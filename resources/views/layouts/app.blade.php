<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4F46E5">

    <title>@yield('title', 'UKS') — LabsHealth</title>

    <!-- PWA -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 0px;
            --navbar-height: 60px;
            --primary: #4F46E5;
            --primary-dark: #4338CA;
            --primary-light: #818CF8;
            --bg-body: #F1F5F9;
            --bg-sidebar: #1E293B;
            --bg-sidebar-active: #334155;
            --text-sidebar: #CBD5E1;
            --text-sidebar-active: #FFFFFF;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-body);
            overflow-x: hidden;
        }

        /* ── Sidebar ─────────────────────────────── */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--bg-sidebar);
            z-index: 1040;
            transition: transform 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-brand {
            height: var(--navbar-height);
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar-brand h5 {
            color: #fff;
            margin: 0;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .sidebar-brand .brand-icon {
            width: 32px;
            height: 32px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 1rem;
            color: white;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .sidebar-nav .nav-label {
            color: #64748B;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.75rem 1.25rem 0.5rem;
        }

        .sidebar-nav .nav-item {
            padding: 0 0.75rem;
        }

        .sidebar-nav .nav-link {
            color: var(--text-sidebar);
            padding: 0.55rem 0.75rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            font-weight: 400;
            transition: all 0.2s ease;
            text-decoration: none;
            margin-bottom: 2px;
        }

        .sidebar-nav .nav-link:hover {
            background-color: var(--bg-sidebar-active);
            color: var(--text-sidebar-active);
        }

        .sidebar-nav .nav-link.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            font-weight: 500;
        }

        .sidebar-nav .nav-link i {
            width: 20px;
            margin-right: 10px;
            font-size: 1rem;
            text-align: center;
        }

        .sidebar-nav .submenu {
            list-style: none;
            padding-left: 1.25rem;
            margin: 0;
        }

        .sidebar-nav .submenu .nav-link {
            font-size: 0.82rem;
            padding: 0.4rem 0.75rem;
        }

        .nav-link.dropdown-toggle::after {
            display: none;
        }

        .nav-link .toggle-icon {
            transition: transform 0.2s ease;
        }

        .nav-link[aria-expanded="true"] .toggle-icon {
            transform: rotate(180deg);
        }

        /* ── Top Navbar ──────────────────────────── */
        .top-navbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--navbar-height);
            background: #fff;
            border-bottom: 1px solid #E2E8F0;
            z-index: 1030;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            transition: left 0.3s ease;
        }

        .top-navbar .btn-toggle-sidebar {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #475569;
            cursor: pointer;
            padding: 0.25rem;
            display: none;
        }

        .top-navbar .user-dropdown .btn {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            color: #334155;
            font-weight: 500;
        }

        .top-navbar .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8rem;
        }

        /* ── Main Content ────────────────────────── */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
            padding: 1.5rem;
            min-height: calc(100vh - var(--navbar-height));
            transition: margin-left 0.3s ease;
        }

        /* ── Cards ────────────────────────────────── */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #F1F5F9;
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1E293B;
        }

        .stat-card .stat-label {
            font-size: 0.82rem;
            color: #64748B;
            font-weight: 500;
        }

        /* ── Buttons ─────────────────────────────── */
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        /* ── Tables ──────────────────────────────── */
        .table th {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #64748B;
            border-bottom-width: 1px;
        }

        .table td {
            font-size: 0.875rem;
            vertical-align: middle;
        }

        /* ── Badges ──────────────────────────────── */
        .badge-category {
            font-size: 0.72rem;
            font-weight: 600;
            padding: 0.3em 0.65em;
            border-radius: 6px;
        }

        .badge-sma { background: #DBEAFE; color: #1E40AF; }
        .badge-guru { background: #D1FAE5; color: #065F46; }
        .badge-karyawan { background: #FEF3C7; color: #92400E; }
        .badge-umum { background: #E0E7FF; color: #3730A3; }

        /* ── Mobile / Responsive ─────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            z-index: 1035;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar-overlay.show {
                display: block;
            }

            .top-navbar {
                left: 0;
            }

            .top-navbar .btn-toggle-sidebar {
                display: block;
            }

            .main-content {
                margin-left: 0;
            }
        }

        /* ── Print ───────────────────────────────── */
        @media print {
            .sidebar, .top-navbar, .no-print {
                display: none !important;
            }
            .main-content {
                margin: 0;
                padding: 0;
            }
        }

        /* ── Animations ──────────────────────────── */
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        .master-async-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 20;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    @include('layouts.sidebar')

    <!-- Top Navbar -->
    <nav class="top-navbar">
        <button class="btn-toggle-sidebar me-3" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>

        <div class="ms-auto d-flex align-items-center">
            <div class="dropdown user-dropdown">
                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="" class="rounded-circle" width="34" height="34">
                    @else
                        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    @endif
                    <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->email }}</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content fade-in">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }

        function showAsyncAlert(type, message) {
            const mainContent = document.querySelector('.main-content');
            if (!mainContent) return;

            const existing = document.getElementById('async-master-alert');
            if (existing) existing.remove();

            const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
            const alert = document.createElement('div');
            alert.id = 'async-master-alert';
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.role = 'alert';
            alert.innerHTML = `
                <i class="bi bi-${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            mainContent.prepend(alert);
        }

        function getAsyncOverlayTarget(container) {
            return container.querySelector('[data-master-async-table]') || container;
        }

        function toggleAsyncOverlay(container, isLoading) {
            const target = getAsyncOverlayTarget(container);
            if (!target) return;

            const style = window.getComputedStyle(target);
            if (style.position === 'static') {
                target.style.position = 'relative';
            }

            let overlay = target.querySelector(':scope > .master-async-overlay');
            if (!overlay && isLoading) {
                overlay = document.createElement('div');
                overlay.className = 'master-async-overlay';
                overlay.innerHTML = `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-2" role="status"></div>
                        <div class="small text-muted">Memuat data...</div>
                    </div>
                `;
                target.appendChild(overlay);
            }

            if (overlay) {
                overlay.style.display = isLoading ? 'flex' : 'none';
            }
        }

        function setFormSubmitting(form, isSubmitting) {
            const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            submitButtons.forEach((btn) => {
                if (!btn.dataset.originalHtml) {
                    btn.dataset.originalHtml = btn.innerHTML;
                }

                if (isSubmitting) {
                    btn.disabled = true;
                    const loadingText = form.dataset.loadingText || 'Menyimpan...';
                    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>${loadingText}`;
                } else {
                    btn.disabled = false;
                    if (btn.dataset.originalHtml) {
                        btn.innerHTML = btn.dataset.originalHtml;
                    }
                }
            });
        }

        async function refreshMasterAsyncContainer(url = window.location.href, pushState = false) {
            const containerSelector = '[data-master-async-container]';
            const currentContainer = document.querySelector(containerSelector);
            if (!currentContainer) return false;

            toggleAsyncOverlay(currentContainer, true);
            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Gagal memuat data.');
                }

                const html = await response.text();
                const parsed = new DOMParser().parseFromString(html, 'text/html');
                const incomingContainer = parsed.querySelector(containerSelector);

                if (!incomingContainer) {
                    throw new Error('Container data tidak ditemukan.');
                }

                currentContainer.innerHTML = incomingContainer.innerHTML;

                if (pushState) {
                    window.history.pushState({}, '', url);
                }
            } finally {
                const activeContainer = document.querySelector(containerSelector);
                if (activeContainer) {
                    toggleAsyncOverlay(activeContainer, false);
                }
            }

            return true;
        }

        window.refreshMasterAsyncContainer = refreshMasterAsyncContainer;

        function clearFormValidation(form) {
            form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback.async-feedback').forEach((el) => el.remove());
        }

        function applyFormValidation(form, errors) {
            Object.keys(errors || {}).forEach((field) => {
                const input = form.querySelector(`[name="${field}"]`);
                if (!input) return;

                input.classList.add('is-invalid');

                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback async-feedback';
                feedback.textContent = errors[field][0] || 'Input tidak valid';

                if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) {
                    input.nextElementSibling.remove();
                }

                input.insertAdjacentElement('afterend', feedback);
            });
        }

        document.addEventListener('submit', async function (e) {
            const form = e.target;
            const isAsyncMaster = form.classList.contains('js-async-master');
            const isAsyncDelete = form.classList.contains('js-async-delete');

            if (!isAsyncMaster && !isAsyncDelete) return;

            e.preventDefault();

            if (isAsyncDelete) {
                const confirmMessage = form.dataset.confirm || 'Yakin hapus data ini?';
                if (!window.confirm(confirmMessage)) return;
            }

            clearFormValidation(form);
            setFormSubmitting(form, true);

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const payload = new FormData(form);

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

                if (response.status === 422) {
                    applyFormValidation(form, body.errors || {});
                    return;
                }

                if (!response.ok) {
                    throw new Error(body.message || 'Terjadi kesalahan saat memproses data.');
                }

                const modalEl = form.closest('.modal');
                if (modalEl) {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                }

                const successMessage = body.message || form.dataset.successMessage || 'Berhasil diproses.';
                showAsyncAlert('success', successMessage);

                const refreshed = await refreshMasterAsyncContainer(window.location.href, false);
                if (!refreshed) {
                    setTimeout(() => window.location.reload(), 250);
                }
            } catch (err) {
                showAsyncAlert('danger', err.message || 'Terjadi kesalahan jaringan.');
            } finally {
                setFormSubmitting(form, false);
            }
        });

        document.addEventListener('submit', async function (e) {
            const form = e.target;
            if (!form.classList.contains('js-async-search')) return;

            e.preventDefault();
            const action = form.getAttribute('action') || window.location.pathname;
            const params = new URLSearchParams(new FormData(form)).toString();
            const url = params ? `${action}?${params}` : action;

            try {
                await refreshMasterAsyncContainer(url, true);
            } catch (err) {
                showAsyncAlert('danger', err.message || 'Gagal memuat hasil pencarian.');
            }
        });

        document.addEventListener('click', async function (e) {
            const refreshLink = e.target.closest('a.js-async-refresh');
            if (refreshLink) {
                e.preventDefault();
                try {
                    await refreshMasterAsyncContainer(refreshLink.href, true);
                } catch (err) {
                    showAsyncAlert('danger', err.message || 'Gagal memuat ulang data.');
                }
                return;
            }

            const pageLink = e.target.closest('[data-master-async-container] .pagination a');
            if (pageLink) {
                e.preventDefault();
                try {
                    await refreshMasterAsyncContainer(pageLink.href, true);
                } catch (err) {
                    showAsyncAlert('danger', err.message || 'Gagal memuat halaman.');
                }
            }
        });

        // Close sidebar on window resize to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                document.querySelector('.sidebar').classList.remove('show');
                document.getElementById('sidebarOverlay').classList.remove('show');
            }
        });

        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('SW registered:', registration.scope);
                    })
                    .catch(function(err) {
                        console.log('SW registration failed:', err);
                    });
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
