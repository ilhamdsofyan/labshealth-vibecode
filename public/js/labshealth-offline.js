(function () {
    const DB_NAME = 'labshealth-offline';
    const DB_VERSION = 1;
    const VISIT_QUEUE_STORE = 'visit_queue';
    const PATIENT_CACHE_STORE = 'patient_cache';

    const config = window.LabsHealthOfflineConfig || {};
    let dbPromise = null;
    let syncInProgress = false;

    function dispatch(name, detail) {
        document.dispatchEvent(new CustomEvent(name, { detail }));
    }

    function openDb() {
        if (dbPromise) {
            return dbPromise;
        }

        dbPromise = new Promise((resolve, reject) => {
            const request = window.indexedDB.open(DB_NAME, DB_VERSION);

            request.onupgradeneeded = function (event) {
                const db = event.target.result;

                if (!db.objectStoreNames.contains(VISIT_QUEUE_STORE)) {
                    const visitStore = db.createObjectStore(VISIT_QUEUE_STORE, { keyPath: 'client_uuid' });
                    visitStore.createIndex('created_at', 'created_at', { unique: false });
                }

                if (!db.objectStoreNames.contains(PATIENT_CACHE_STORE)) {
                    const patientStore = db.createObjectStore(PATIENT_CACHE_STORE, { keyPath: 'cache_key' });
                    patientStore.createIndex('type', 'type', { unique: false });
                    patientStore.createIndex('updated_at', 'updated_at', { unique: false });
                }
            };

            request.onsuccess = function () {
                resolve(request.result);
            };

            request.onerror = function () {
                reject(request.error);
            };
        });

        return dbPromise;
    }

    async function withStore(storeName, mode, callback) {
        const db = await openDb();

        return new Promise((resolve, reject) => {
            const transaction = db.transaction(storeName, mode);
            const store = transaction.objectStore(storeName);

            transaction.oncomplete = function () {
                resolve();
            };

            transaction.onerror = function () {
                reject(transaction.error);
            };

            transaction.onabort = function () {
                reject(transaction.error);
            };

            callback(store, transaction, resolve, reject);
        });
    }

    function isOffline() {
        return !window.navigator.onLine;
    }

    function generateUuid() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }

        return 'offline-' + Date.now() + '-' + Math.random().toString(16).slice(2);
    }

    async function cachePatientResults(type, items) {
        if (!Array.isArray(items) || !items.length) {
            return;
        }

        const normalizedItems = items
            .map((item) => {
                if (!item || !item.id) {
                    return null;
                }

                const searchBlob = [
                    item.text,
                    item.class,
                    item.gender,
                    item.name,
                    item.role_type,
                    item.department,
                ].filter(Boolean).join(' ').toLowerCase();

                return {
                    cache_key: type + ':' + item.id,
                    type: type,
                    id: item.id,
                    payload: item,
                    search_blob: searchBlob,
                    updated_at: new Date().toISOString(),
                };
            })
            .filter(Boolean);

        if (!normalizedItems.length) {
            return;
        }

        await withStore(PATIENT_CACHE_STORE, 'readwrite', function (store) {
            normalizedItems.forEach((item) => store.put(item));
        });
    }

    async function searchPatientCache(type, query) {
        const keyword = String(query || '').trim().toLowerCase();
        const matches = [];

        await withStore(PATIENT_CACHE_STORE, 'readonly', function (store, transaction, resolve, reject) {
            const request = store.getAll();
            request.onsuccess = function () {
                const result = (request.result || [])
                    .filter((item) => item.type === type)
                    .filter((item) => !keyword || item.search_blob.includes(keyword))
                    .sort((a, b) => String(b.updated_at).localeCompare(String(a.updated_at)))
                    .slice(0, 10)
                    .map((item) => item.payload);

                matches.push(...result);
            };
            request.onerror = function () {
                reject(request.error);
            };
        });

        return matches;
    }

    async function queueVisit(payload) {
        const entry = {
            client_uuid: payload.client_uuid || generateUuid(),
            payload: payload,
            created_at: new Date().toISOString(),
            last_error: null,
        };

        await withStore(VISIT_QUEUE_STORE, 'readwrite', function (store) {
            store.put(entry);
        });

        dispatch('labshealth:offline-queue-changed', { count: null });
        return entry;
    }

    async function getPendingVisits() {
        const items = [];

        await withStore(VISIT_QUEUE_STORE, 'readonly', function (store, transaction, resolve, reject) {
            const request = store.getAll();
            request.onsuccess = function () {
                items.push(...(request.result || []).sort((a, b) => String(a.created_at).localeCompare(String(b.created_at))));
            };
            request.onerror = function () {
                reject(request.error);
            };
        });

        return items;
    }

    async function countPendingVisits() {
        const items = await getPendingVisits();
        return items.length;
    }

    async function removeQueuedVisit(clientUuid) {
        await withStore(VISIT_QUEUE_STORE, 'readwrite', function (store) {
            store.delete(clientUuid);
        });
    }

    async function updateQueueError(clientUuid, message) {
        await withStore(VISIT_QUEUE_STORE, 'readwrite', function (store, transaction, resolve, reject) {
            const request = store.get(clientUuid);
            request.onsuccess = function () {
                const record = request.result;
                if (!record) {
                    return;
                }

                record.last_error = message || null;
                record.updated_at = new Date().toISOString();
                store.put(record);
            };
            request.onerror = function () {
                reject(request.error);
            };
        });
    }

    async function syncQueuedVisits(options) {
        options = options || {};

        if (syncInProgress || isOffline() || !config.syncUrl) {
            return { synced: [], failed: [] };
        }

        const pending = await getPendingVisits();
        if (!pending.length) {
            dispatch('labshealth:offline-sync-finished', { synced: [], failed: [] });
            return { synced: [], failed: [] };
        }

        syncInProgress = true;
        dispatch('labshealth:offline-sync-started', { count: pending.length });

        try {
            const response = await window.fetch(config.syncUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': config.csrfToken || '',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    items: pending.map((entry) => ({
                        client_uuid: entry.client_uuid,
                        payload: entry.payload,
                    })),
                }),
            });

            const body = await response.json().catch(() => ({}));
            if (!response.ok && response.status !== 207) {
                throw new Error(body.message || 'Sinkronisasi kunjungan lokal gagal.');
            }
            const synced = Array.isArray(body.synced) ? body.synced : [];
            const failed = Array.isArray(body.failed) ? body.failed : [];

            for (const item of synced) {
                if (item && item.client_uuid) {
                    await removeQueuedVisit(item.client_uuid);
                }
            }

            for (const item of failed) {
                if (item && item.client_uuid) {
                    await updateQueueError(item.client_uuid, item.message || 'Sinkronisasi gagal.');
                }
            }

            dispatch('labshealth:offline-queue-changed', { count: null });
            dispatch('labshealth:offline-sync-finished', {
                synced: synced,
                failed: failed,
                message: body.message || null,
                showNotice: Boolean(options.showNotice),
            });

            return { synced, failed };
        } finally {
            syncInProgress = false;
        }
    }

    async function renderStatusBanner() {
        const banner = document.getElementById('offlineStatusBanner');
        if (!banner) {
            return;
        }

        const count = await countPendingVisits();
        const offline = isOffline();
        const hasPending = count > 0;

        banner.classList.toggle('d-none', !offline && !hasPending);

        if (offline) {
            banner.className = 'alert alert-warning d-flex flex-wrap align-items-start justify-content-between gap-3';
            banner.innerHTML = `
                <div>
                    <div class="fw-bold mb-1"><i class="bi bi-wifi-off me-2"></i>Mode Offline Aktif</div>
                    <div class="small mb-1">Anda masih bisa input kunjungan. Data akan disimpan di perangkat ini dan disinkronkan otomatis saat koneksi kembali.</div>
                    <div class="small text-muted">Batasan: pencarian siswa/pegawai offline hanya memakai data yang sudah pernah tercache di perangkat ini.</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge text-bg-light border">${count} antrean</span>
                    <a href="${config.createVisitUrl || '#'}" class="btn btn-sm btn-outline-dark ${config.createVisitUrl ? '' : 'disabled'}">Input Kunjungan</a>
                </div>
            `;
            return;
        }

        if (hasPending) {
            banner.className = 'alert alert-info d-flex flex-wrap align-items-center justify-content-between gap-3';
            banner.innerHTML = `
                <div>
                    <div class="fw-bold mb-1"><i class="bi bi-arrow-repeat me-2"></i>${count} kunjungan menunggu sinkronisasi</div>
                    <div class="small text-muted">Server sudah terhubung lagi. Data lokal akan dicoba kirim otomatis, atau Anda bisa memicu sinkronisasi manual.</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="${config.visitsIndexUrl || '#'}" class="btn btn-sm btn-outline-primary ${config.visitsIndexUrl ? '' : 'disabled'}">Lihat Kunjungan</a>
                    <button type="button" class="btn btn-sm btn-primary" id="manualOfflineSyncButton">Sinkronkan Sekarang</button>
                </div>
            `;

            const button = document.getElementById('manualOfflineSyncButton');
            button?.addEventListener('click', function () {
                syncQueuedVisits({ showNotice: true });
            }, { once: true });
        }
    }

    function initGlobalListeners() {
        window.addEventListener('online', function () {
            renderStatusBanner();
            syncQueuedVisits({ showNotice: true });
            dispatch('labshealth:network-status', { offline: false });
        });

        window.addEventListener('offline', function () {
            renderStatusBanner();
            dispatch('labshealth:network-status', { offline: true });
        });

        document.addEventListener('labshealth:offline-queue-changed', function () {
            renderStatusBanner();
        });

        document.addEventListener('labshealth:offline-sync-finished', function (event) {
            renderStatusBanner();

            const detail = event.detail || {};
            const syncedCount = Array.isArray(detail.synced) ? detail.synced.length : 0;
            const failedCount = Array.isArray(detail.failed) ? detail.failed.length : 0;

            if (!detail.showNotice) {
                return;
            }

            if (window.showAsyncAlert) {
                if (syncedCount > 0) {
                    window.showAsyncAlert('success', `${syncedCount} kunjungan lokal berhasil disinkronkan.`);
                }

                if (failedCount > 0) {
                    window.showAsyncAlert('danger', `${failedCount} kunjungan masih gagal sinkron. Cek lagi saat koneksi stabil.`);
                }
            }
        });
    }

    async function init() {
        if (!window.indexedDB) {
            return;
        }

        try {
            await openDb();
            initGlobalListeners();
            await renderStatusBanner();

            if (!isOffline()) {
                [config.visitsIndexUrl, config.createVisitUrl].filter(Boolean).forEach(function (url) {
                    window.fetch(url, {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }).catch(() => null);
                });

                syncQueuedVisits({ showNotice: false });
            }
        } catch (error) {
            console.warn('Offline mode initialization failed:', error);
        }
    }

    window.LabsHealthOffline = {
        init,
        isOffline,
        queueVisit,
        getPendingVisits,
        countPendingVisits,
        syncQueuedVisits,
        cachePatientResults,
        searchPatientCache,
        renderStatusBanner,
    };
})();



