@extends('layouts.app')

@section('title', 'Data Kunjungan')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Data Kunjungan</h4>
        <p class="text-muted mb-0 small">Kelola data kunjungan UKS (Normalized)</p>
    </div>
    <a href="{{ route('visits.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Tambah Kunjungan
    </a>
</div>

<div data-master-async-container>
    <div class="card mb-3 d-none" id="offlineVisitQueueCard">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div>
                    <h6 class="fw-bold mb-1">Antrean Kunjungan Lokal</h6>
                    <p class="text-muted small mb-0">Data di bawah ini masih tersimpan di perangkat ini dan akan otomatis dikirim ke server saat koneksi tersedia.</p>
                </div>
                <button type="button" class="btn btn-sm btn-primary d-none" id="offlineVisitSyncButton">
                    <i class="bi bi-arrow-repeat me-1"></i>Sinkronkan
                </button>
            </div>
            <div class="table-responsive mt-3">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Waktu Lokal</th>
                            <th>Pasien</th>
                            <th>Kategori</th>
                            <th>Keluhan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="offlineVisitQueueBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('visits.index') }}" class="js-async-search">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Pencarian</label>
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Nama, keluhan, penyakit..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label small fw-semibold">Dari Tanggal</label>
                        <input type="date" name="date_from" class="form-control form-control-sm"
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2 col-6">
                        <label class="form-label small fw-semibold">Sampai Tanggal</label>
                        <input type="date" name="date_to" class="form-control form-control-sm"
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Kategori</label>
                        <select name="patient_category" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach(['SMA', 'GURU', 'KARYAWAN', 'UMUM'] as $cat)
                                <option value="{{ $cat }}" {{ request('patient_category') == $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="is_acc_pulang" value="1" id="filterPulang" {{ request('is_acc_pulang') ? 'checked' : '' }}>
                            <label class="form-check-label small" for="filterPulang">Hanya Acc Pulang</label>
                        </div>
                    </div>
                    <div class="col-md-1 d-grid gap-1">
                        <button type="submit" class="btn btn-primary btn-sm" title="Cari">
                            <i class="bi bi-search"></i>
                        </button>
                        <a href="{{ route('visits.index') }}" class="btn btn-outline-secondary btn-sm js-async-refresh" title="Refresh">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card" data-master-async-table>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>No</th>
                            <th>Waktu</th>
                            <th>Pasien</th>
                            <th>Kategori</th>
                            <th class="d-none d-lg-table-cell">Diseases</th>
                            <th>Rest</th>
                            <th>Pulang</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visits as $index => $visit)
                            <tr>
                                <td class="small">{{ $visits->firstItem() + $index }}</td>
                                <td class="small text-nowrap">
                                    <div class="fw-bold">{{ $visit->visit_date->format('d/m/Y') }}</div>
                                    <div class="text-muted">{{ $visit->visit_time }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">
                                        @if($visit->student)
                                            <a href="{{ route('visitors.students.history', $visit->student) }}" class="text-decoration-none">
                                                {{ $visit->patient_name }}
                                            </a>
                                        @elseif($visit->employee)
                                            <a href="{{ route('visitors.employees.history', $visit->employee) }}" class="text-decoration-none">
                                                {{ $visit->patient_name }}
                                            </a>
                                        @else
                                            {{ $visit->patient_name }}
                                        @endif
                                    </div>
                                    <div class="small text-muted">
                                        @if($visit->student)
                                            NIS: {{ $visit->student->nis }}
                                        @elseif($visit->employee)
                                            NIP: {{ $visit->employee->nip }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-category badge-{{ strtolower($visit->patient_category) }}">
                                        {{ $visit->patient_category }}
                                    </span>
                                    <div class="small text-muted mt-1">{{ $visit->class_at_visit ?? $visit->class_or_department ?? '-' }}</div>
                                </td>
                                <td class="d-none d-lg-table-cell fw-medium text-primary small">
                                    @php
                                        $diseaseNames = $visit->diseases->pluck('name')->filter()->values();
                                    @endphp
                                    {{ $diseaseNames->isNotEmpty() ? $diseaseNames->implode(', ') : '-' }}
                                </td>
                                <td class="small">
                                    @php $canToggleRestToday = $visit->visit_date && $visit->visit_date->isToday(); @endphp
                                    <form action="{{ route('visits.toggle-rest', $visit) }}" method="POST" class="js-visit-toggle d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_rest" value="0">
                                        <input type="hidden" name="rest_action" value="">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" name="is_rest" value="1"
                                                   {{ $visit->is_rest ? 'checked' : '' }}
                                                   {{ ($visit->is_acc_pulang || !$canToggleRestToday) ? 'disabled' : '' }}
                                                   title="{{ $canToggleRestToday ? 'Toggle Rest' : 'Rest hanya aktif di hari kunjungan' }}">
                                        </div>
                                        @if($visit->rest_status === 'completed')
                                            <div class="small text-warning mt-1 fw-semibold">Selesai Rest</div>
                                        @elseif($visit->rest_status === 'cancelled')
                                            <div class="small text-muted mt-1">Ga Jadi Rest</div>
                                        @endif
                                    </form>
                                </td>
                                <td class="small">
                                    <form action="{{ route('visits.toggle-pulang', $visit) }}" method="POST" class="js-visit-toggle d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_acc_pulang" value="0">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" name="is_acc_pulang" value="1" {{ $visit->is_acc_pulang ? 'checked' : '' }}>
                                        </div>
                                    </form>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('visits.show', $visit) }}" class="btn btn-outline-primary" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('visits.edit', $visit) }}" class="btn btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('visits.destroy', $visit) }}" method="POST" class="js-async-delete"
                                              data-confirm="Yakin ingin menghapus data ini?" data-loading-text="Menghapus...">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Belum ada data kunjungan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($visits->hasPages())
            <div class="card-footer bg-transparent">
                {{ $visits->links() }}
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="restToggleConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Akhiri Status Rest</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Pasien akan dilepas dari status rest. Pilih hasil akhirnya:</p>
                <div class="small text-muted">Selesai Rest tetap masuk histori dan laporan. Ga Jadi Rest tidak dihitung sebagai histori rest.</div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-danger" id="cancelRestButton">Ga Jadi Rest</button>
                    <button type="button" class="btn btn-primary" id="completeRestButton">Selesai Rest</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const restModalEl = document.getElementById('restToggleConfirmModal');
        const restModal = restModalEl ? new bootstrap.Modal(restModalEl) : null;
        const completeRestButton = document.getElementById('completeRestButton');
        const cancelRestButton = document.getElementById('cancelRestButton');
        let pendingRestContext = null;

        async function renderOfflineQueue() {
            const queueCard = document.getElementById('offlineVisitQueueCard');
            const queueBody = document.getElementById('offlineVisitQueueBody');
            const syncButton = document.getElementById('offlineVisitSyncButton');

            if (!queueCard || !queueBody || !window.LabsHealthOffline) {
                return;
            }

            const items = await window.LabsHealthOffline.getPendingVisits();
            queueCard.classList.toggle('d-none', items.length === 0);
            syncButton?.classList.toggle('d-none', items.length === 0 || window.LabsHealthOffline.isOffline());

            if (!items.length) {
                queueBody.innerHTML = '';
                return;
            }

            queueBody.innerHTML = items.map(function (item) {
                const payload = item.payload || {};
                const localTime = item.created_at
                    ? new Date(item.created_at).toLocaleString('id-ID')
                    : '-';
                const statusLabel = item.last_error
                    ? `<span class="badge text-bg-danger">Gagal Sync</span><div class="small text-muted mt-1">${item.last_error}</div>`
                    : `<span class="badge text-bg-warning">Menunggu Sync</span>`;

                return `
                    <tr>
                        <td class="small text-nowrap">${localTime}</td>
                        <td class="small fw-semibold">${payload.patient_name || payload.external_patient_name || '-'}</td>
                        <td class="small">${payload.patient_category || '-'}</td>
                        <td class="small">${payload.complaint || '-'}</td>
                        <td class="small">${statusLabel}</td>
                    </tr>
                `;
            }).join('');
        }

        async function submitVisitToggle(form, toggleInput, extraData = {}) {
            const payload = new FormData(form);
            Object.entries(extraData).forEach(([key, value]) => payload.set(key, value));

            toggleInput.disabled = true;
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: payload,
                });

                let body = {};
                try { body = await response.json(); } catch (_) {}

                if (response.status === 419) {
                    window.handleSessionExpired?.(body.message, body.reload_url);
                    return;
                }

                if (!response.ok) {
                    throw new Error(body.message || 'Gagal memperbarui status.');
                }

                if (window.showAsyncAlert) {
                    window.showAsyncAlert('success', body.message || 'Status diperbarui.');
                }

                if (window.refreshMasterAsyncContainer) {
                    await window.refreshMasterAsyncContainer(window.location.href, false);
                } else {
                    window.location.reload();
                }
            } catch (err) {
                if (window.showAsyncAlert) {
                    window.showAsyncAlert('danger', err.message || 'Terjadi kesalahan jaringan.');
                }
                toggleInput.checked = !toggleInput.checked;
                toggleInput.disabled = false;
            }
        }

        completeRestButton?.addEventListener('click', async function () {
            if (!pendingRestContext) return;
            const context = pendingRestContext;
            pendingRestContext = null;
            restModal?.hide();
            await submitVisitToggle(context.form, context.toggleInput, { rest_action: 'completed' });
        });

        cancelRestButton?.addEventListener('click', async function () {
            if (!pendingRestContext) return;
            const context = pendingRestContext;
            pendingRestContext = null;
            restModal?.hide();
            await submitVisitToggle(context.form, context.toggleInput, { rest_action: 'cancelled' });
        });

        restModalEl?.addEventListener('hidden.bs.modal', function () {
            if (pendingRestContext) {
                pendingRestContext.toggleInput.checked = true;
                pendingRestContext.toggleInput.disabled = false;
                pendingRestContext = null;
            }
        });

        document.addEventListener('change', async function (e) {
            const toggleInput = e.target.closest('.js-visit-toggle input.form-check-input');
            if (!toggleInput) return;

            const form = toggleInput.closest('form.js-visit-toggle');
            if (!form) return;

            const isRestToggle = form.action.includes('/toggle-rest');
            if (isRestToggle && !toggleInput.checked) {
                pendingRestContext = { form, toggleInput };
                restModal?.show();
                return;
            }

            await submitVisitToggle(form, toggleInput);
        });

        document.addEventListener('click', function (e) {
            const syncButton = e.target.closest('#offlineVisitSyncButton');
            if (!syncButton) return;

            window.LabsHealthOffline?.syncQueuedVisits({ showNotice: true });
        });

        renderOfflineQueue();
        document.addEventListener('labshealth:offline-queue-changed', renderOfflineQueue);
        document.addEventListener('labshealth:offline-sync-finished', renderOfflineQueue);
        document.addEventListener('labshealth:network-status', renderOfflineQueue);
    });
</script>
@endpush
@endsection
