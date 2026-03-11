<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#006060">

    <title>@yield('title', 'UKS') - LabsHealth</title>

    <script>
        (function () {
            const stored = localStorage.getItem('labshealth_theme');
            const initial = stored === 'dark' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', initial);
        })();
    </script>

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/Logo.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/Logo.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 288px;
            --navbar-height: 80px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --primary: #006060;
            --accent: #f0d000;
            --bg-body: #f8f6f6;
            --bg-surface: #ffffff;
            --bg-surface-soft: #f3f4f6;
            --bg-sidebar: #ffffff;
            --border: #e5e7eb;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --text-sidebar: #475569;
            --shadow-card: 0 1px 3px rgba(15, 23, 42, 0.08), 0 1px 2px rgba(15, 23, 42, 0.06);
        }

        html[data-theme='dark'] {
            --primary: #0070c0;
            --accent: #f0d000;
            --bg-body: #121212;
            --bg-surface: #1e1e1e;
            --bg-surface-soft: #252525;
            --bg-sidebar: #1a1a1a;
            --border: #2a2a2a;
            --text-main: #edf2ff;
            --text-muted: #b7c2d8;
            --text-sidebar: #c4cee1;
            --shadow-card: 0 1px 3px rgba(0, 0, 0, 0.28), 0 1px 2px rgba(0, 0, 0, 0.22);
            --bs-body-color: #edf2ff;
            --bs-secondary-color: #b7c2d8;
            --bs-tertiary-color: #9aa8c4;
            --bs-border-color: #2a2a2a;
            --bs-emphasis-color: #f8fbff;
        }

        * { font-family: 'Public Sans', sans-serif; }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            color: var(--text-main);
        }

        a { color: inherit; }

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
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            min-height: 90px;
            display: flex;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-brand .brand-logo-long {
            max-width: 190px;
            width: 100%;
            height: auto;
            object-fit: contain;
        }

        .sidebar-brand .brand-logo-square {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            object-fit: cover;
            display: none;
        }

        .sidebar-nav { padding: 1rem; }

        .sidebar-nav .nav-item { padding: 0; }

        .sidebar-nav .nav-link {
            color: var(--text-sidebar);
            padding: 0.76rem 0.95rem;
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.22s ease;
            text-decoration: none;
            margin-bottom: 0.25rem;
        }

        .sidebar-nav .nav-link:hover {
            background-color: var(--bg-surface-soft);
            color: var(--text-main);
            transform: translateX(1px);
        }

        .sidebar-nav .nav-link.active {
            background: var(--primary);
            color: #fff;
            font-weight: 700;
            box-shadow: 0 8px 20px color-mix(in srgb, var(--primary) 22%, transparent);
        }

        .sidebar-nav .nav-link i {
            width: 20px;
            margin-right: 0;
            font-size: 1.05rem;
            text-align: center;
        }

        .sidebar-nav .submenu {
            list-style: none;
            padding-left: 1.5rem;
            margin: 0;
        }

        .sidebar-nav .submenu .nav-link {
            font-size: 0.84rem;
            padding: 0.55rem 0.75rem;
            border-radius: 10px;
        }

        .nav-link.dropdown-toggle::after { display: none; }

        .nav-link .toggle-icon { transition: transform 0.2s ease; }
        .nav-link[aria-expanded="true"] .toggle-icon { transform: rotate(180deg); }

        .sidebar-footer {
            margin-top: auto;
            padding: 1rem;
            border-top: 1px solid var(--border);
        }

        .sidebar-footer .nav-link {
            color: var(--text-sidebar);
            padding: 0.76rem 0.95rem;
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.22s ease;
            text-decoration: none;
            margin-bottom: 0.25rem;
        }

        .sidebar-footer .nav-link:hover {
            background-color: var(--bg-surface-soft);
            color: var(--text-main);
        }

        .sidebar-footer .nav-link.logout-link {
            color: #ef4444;
        }

        .sidebar-footer .nav-link.logout-link:hover {
            background: rgba(239, 68, 68, 0.1);
        }

        .top-navbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--navbar-height);
            background: color-mix(in srgb, var(--bg-surface) 88%, transparent);
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(10px);
            z-index: 1030;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            transition: left 0.3s ease;
        }

        html[data-theme='dark'] .top-navbar {
            background: rgba(18, 18, 18, 0.8);
            border-bottom-color: #2a2a2a;
        }

        .top-navbar .btn-toggle-sidebar {
            background: none;
            border: none;
            font-size: 1.35rem;
            color: var(--text-main);
            cursor: pointer;
            padding: 0.25rem;
            display: none;
        }

        .top-navbar .page-heading {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .header-tools {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .quick-search {
            width: 260px;
            position: relative;
        }

        .quick-search i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .quick-search input {
            width: 100%;
            height: 40px;
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 0.5rem 0.75rem 0.5rem 2rem;
            background: var(--bg-surface-soft);
            color: var(--text-main);
            font-size: 0.85rem;
        }

        html[data-theme='dark'] .quick-search input {
            background: #1e1e1e;
            border-color: #2a2a2a;
            color: #f5f5f7;
        }

        .quick-search input:focus {
            outline: none;
            border-color: color-mix(in srgb, var(--primary) 50%, var(--border));
            box-shadow: 0 0 0 0.25rem color-mix(in srgb, var(--primary) 18%, transparent);
        }

        .btn-theme {
            border: 1px solid var(--border);
            background: var(--bg-surface-soft);
            color: var(--text-main);
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .btn-theme:hover {
            background: color-mix(in srgb, var(--bg-surface-soft) 70%, var(--primary) 30%);
        }

        .top-navbar .user-dropdown .btn {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.875rem;
            color: var(--text-main);
            font-weight: 600;
            border: 1px solid var(--border);
            background: var(--bg-surface);
            border-radius: 14px;
            padding: 0.35rem 0.6rem;
        }

        .top-navbar .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--accent), var(--primary));
            color: #111827;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.8rem;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
            padding: 2rem;
            min-height: calc(100vh - var(--navbar-height));
            transition: margin-left 0.3s ease;
        }

        .card {
            border: 1px solid var(--border);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-card);
            background: var(--bg-surface);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.25rem;
            font-weight: 700;
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
            color: var(--text-main);
        }

        .stat-card .stat-label {
            font-size: 0.82rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            border-radius: 12px;
            font-weight: 700;
        }

        .btn-primary:hover {
            background: color-mix(in srgb, var(--primary) 88%, #000 12%);
            border-color: color-mix(in srgb, var(--primary) 88%, #000 12%);
        }

        .btn-outline-secondary,
        .btn-outline-primary,
        .btn-outline-warning,
        .btn-outline-danger {
            border-radius: 12px;
        }

        .form-control,
        .form-select,
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 12px !important;
            border-color: var(--border) !important;
            background-color: var(--bg-surface) !important;
            color: var(--text-main) !important;
        }

        .table { color: var(--text-main); }

        .table th {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: var(--text-muted);
            border-bottom-width: 1px;
            border-color: var(--border);
            background: color-mix(in srgb, var(--bg-surface) 84%, var(--bg-surface-soft) 16%);
        }

        .table td {
            font-size: 0.875rem;
            vertical-align: middle;
            border-color: var(--border);
            background: var(--bg-surface);
        }

        html[data-theme='dark'] .table th {
            color: #c5d0e6;
        }

        html[data-theme='dark'] .table td {
            color: #e6edff;
        }

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

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1035;
        }

        .dropdown-menu {
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-card);
            background: var(--bg-surface);
            color: var(--text-main);
        }

        .dropdown-item { color: var(--text-main); }
        .dropdown-item:hover { background: var(--bg-surface-soft); }
        .text-muted { color: var(--text-muted) !important; }
        .alert { border-radius: 12px; }

        html[data-theme='dark'] .card,
        html[data-theme='dark'] .card-header,
        html[data-theme='dark'] .card-body,
        html[data-theme='dark'] .form-label,
        html[data-theme='dark'] .small,
        html[data-theme='dark'] .fw-medium,
        html[data-theme='dark'] .fw-semibold,
        html[data-theme='dark'] .fw-bold {
            color: var(--text-main);
        }

        html[data-theme='dark'] .btn-outline-secondary {
            color: #d5def0;
            border-color: #445070;
        }

        html[data-theme='dark'] .btn-outline-secondary:hover {
            color: #f8fbff;
            border-color: #5a6790;
            background: #2a3146;
        }

        .master-async-overlay {
            position: absolute;
            inset: 0;
            background: color-mix(in srgb, var(--bg-surface) 72%, transparent);
            backdrop-filter: blur(1px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 20;
            border-radius: inherit;
        }

        html[data-theme='dark'] .master-async-overlay {
            background: rgba(26, 26, 26, 0.78);
        }

        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .sidebar-overlay.show { display: block; }
            .top-navbar { left: 0; padding: 0 1rem; }
            .top-navbar .btn-toggle-sidebar { display: block; }
            .quick-search { display: none; }
            .main-content { margin-left: 0; padding: 1rem; }
            .header-tools { gap: 0.5rem; }
            .top-navbar .page-heading { font-size: 1rem; }

            .sidebar-brand {
                justify-content: center;
                padding: 1rem;
            }

            .sidebar-brand .brand-logo-long {
                display: none;
            }

            .sidebar-brand .brand-logo-square {
                display: block;
            }
        }

        @media print {
            .sidebar, .top-navbar, .no-print { display: none !important; }
            .main-content { margin: 0; padding: 0; }
        }

        .fade-in { animation: fadeIn 0.3s ease-in; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    @include('layouts.sidebar')

    <nav class="top-navbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn-toggle-sidebar" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <h2 class="page-heading mb-0">@yield('title', 'Dashboard')</h2>
        </div>

        <div class="header-tools">
            <div class="quick-search">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Search patients..." aria-label="Quick search">
            </div>

            <button type="button" class="btn-theme" id="themeToggle" title="Toggle Theme">
                <i class="bi bi-moon-stars-fill"></i>
            </button>

            <div class="dropdown user-dropdown">
                <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="" class="rounded-circle" width="36" height="36">
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

    <main class="main-content fade-in">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }

        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('labshealth_theme', theme);

            const themeMeta = document.querySelector('meta[name="theme-color"]');
            if (themeMeta) {
                themeMeta.setAttribute('content', theme === 'dark' ? '#121212' : '#006060');
            }

            const themeIcon = document.querySelector('#themeToggle i');
            if (themeIcon) {
                themeIcon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
            }
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

        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                document.querySelector('.sidebar').classList.remove('show');
                document.getElementById('sidebarOverlay').classList.remove('show');
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            applyTheme(currentTheme);

            const toggle = document.getElementById('themeToggle');
            if (toggle) {
                toggle.addEventListener('click', function () {
                    const active = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                    applyTheme(active === 'dark' ? 'light' : 'dark');
                });
            }
        });

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
