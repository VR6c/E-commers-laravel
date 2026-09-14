<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ getSiteLogo() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Admin'))</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Sans+Khmer:wght@100..900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    {{-- Compiled design system --}}
    @if (!App::environment('testing'))
        @vite(['resources/sass/app.scss'])
    @endif

    {{-- Tom Select & Overrides --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.6.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin-select.css') }}?v=4" rel="stylesheet">
    <link href="{{ asset('css/vendor-panel.css') }}?v=3" rel="stylesheet">

    {{-- Core Shell Styles --}}
    <style>
        /* Modern Toast Container */
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }
        .adm-toast {
            pointer-events: all;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            min-width: 320px;
            max-width: 400px;
            padding: 14px 16px;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.06);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-left: 4px solid transparent;
            opacity: 0;
            transform: translateX(24px);
            transition: opacity .22s ease, transform .22s ease;
            position: relative;
            overflow: hidden;
        }
        .adm-toast.show { opacity: 1; transform: translateX(0); }
        .adm-toast.hide { opacity: 0; transform: translateX(24px); }
        .adm-toast.success { border-left-color: #10b981; }
        .adm-toast.error   { border-left-color: #ef4444; }
        .adm-toast.warning { border-left-color: #f59e0b; }
        .adm-toast.info    { border-left-color: #0ea5e9; }

        .adm-toast .t-icon {
            flex-shrink: 0;
            width: 32px; height: 32px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.95rem;
        }
        .adm-toast.success .t-icon { background: #ecfdf5; color: #059669; }
        .adm-toast.error   .t-icon { background: #fef2f2; color: #dc2626; }
        .adm-toast.warning .t-icon { background: #fffbeb; color: #d97706; }
        .adm-toast.info    .t-icon { background: #eff6ff; color: #0284c7; }

        .adm-toast .t-body { flex: 1; min-width: 0; }
        .adm-toast .t-title {
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .adm-toast.success .t-title { color: #059669; }
        .adm-toast.error   .t-title { color: #dc2626; }
        .adm-toast.warning .t-title { color: #d97706; }
        .adm-toast.info    .t-title { color: #0284c7; }

        .adm-toast .t-msg {
            font-size: 0.84rem;
            color: #334155;
            font-weight: 500;
            line-height: 1.4;
        }
        .adm-toast .t-close {
            flex-shrink: 0;
            background: none; border: none; padding: 0;
            color: #94a3b8; font-size: 0.8rem;
            cursor: pointer; line-height: 1;
            transition: color .15s;
        }
        .adm-toast .t-close:hover { color: #334155; }
        .adm-toast .t-progress {
            position: absolute;
            bottom: 0; left: 0;
            height: 3px;
            width: 100%;
            transform-origin: left;
            animation: t-shrink linear forwards;
        }
        .adm-toast.success .t-progress { background: #10b981; }
        .adm-toast.error   .t-progress { background: #ef4444; }
        .adm-toast.warning .t-progress { background: #f59e0b; }
        .adm-toast.info    .t-progress { background: #0ea5e9; }
        @keyframes t-shrink { from { transform: scaleX(1); } to { transform: scaleX(0); } }

        /* Snappy, modern page entry fade */
        @keyframes pageFadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        #main-content {
            animation: pageFadeIn 0.2s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        /* Mobile Sidebar Backdrop */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 1035;
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .sidebar-backdrop.show {
            display: block;
            opacity: 1;
        }

        /* Accessible focus rings */
        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible,
        .btn:focus-visible {
            outline: 2px solid #4f46e5 !important;
            outline-offset: 2px !important;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.16) !important;
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-delay: 0s !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>

    @yield('css')
</head>

<body>

{{-- Toast Container --}}
<div id="toast-container" aria-live="polite" aria-atomic="true"></div>

{{-- Skip to content link --}}
<a href="#main-content" class="skip-link visually-hidden-focusable p-2 bg-primary text-white position-absolute top-0 start-0 z-3">Skip to main content</a>

{{-- Sidebar Navigation --}}
@include('admin.layouts.sidebar')

{{-- Mobile Sidebar Backdrop --}}
<div id="sidebarBackdrop" class="sidebar-backdrop" aria-hidden="true"></div>

{{-- Content Area --}}
<div id="content" class="content-wrapper d-flex flex-column min-vh-100">

    {{-- Topbar Navbar --}}
    <nav class="navbar navbar-expand navbar-light p-0 main-header sticky-top" aria-label="Top navigation">
        <div class="d-flex align-items-center gap-2 ps-3">
            {{-- Sidebar Toggle --}}
            <button class="btn btn-light border-0 p-2 d-flex align-items-center justify-content-center rounded-3 shadow-xs" id="sidebarToggle" aria-label="Toggle sidebar" aria-expanded="true" aria-controls="sidebar" style="width: 36px; height: 36px; color: #475569;">
                <i class="fas fa-bars" style="font-size: 1rem;"></i>
            </button>

            {{-- Command Menu Search Button --}}
            <button type="button" class="btn btn-sm btn-light border d-flex align-items-center gap-2 px-3 py-1-5 text-muted rounded-pill shadow-xs" onclick="window.openCmdk()" style="font-size: 0.835rem; font-weight: 500;">
                <i class="bi bi-search text-primary"></i>
                <span class="d-none d-sm-inline">Quick search or command...</span>
                <kbd class="bg-white border text-dark ms-1 shadow-xs" style="font-size: 0.68rem; padding: 2px 6px; border-radius: 4px;">⌘K</kbd>
            </button>
        </div>

        {{-- Right Side Actions --}}
        <div class="d-flex align-items-center gap-2 ms-auto pe-3 pe-md-4">
            {{-- Visit Storefront Link --}}
            <a href="/" target="_blank" class="btn btn-sm btn-light border d-flex align-items-center gap-1 px-2-5 py-1 text-secondary rounded-pill shadow-xs" title="View Storefront">
                <i class="bi bi-shop text-primary" style="font-size: 0.85rem;"></i>
                <span class="d-none d-md-inline" style="font-size: 0.8rem; font-weight: 600;">Storefront</span>
                <i class="bi bi-arrow-up-right text-muted" style="font-size: 0.65rem;"></i>
            </a>

            {{-- Notifications --}}
            <button class="btn btn-light border-0 position-relative p-2 rounded-3 shadow-xs" aria-label="Notifications" style="width: 36px; height: 36px; color: #475569;">
                <i class="bi bi-bell" style="font-size: 1.05rem;"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="margin-top: 6px; margin-left: -6px;">
                    <span class="visually-hidden">New alerts</span>
                </span>
            </button>

            {{-- Divider --}}
            <div style="width: 1px; height: 20px; background: #e2e8f0; margin: 0 4px;"></div>

            {{-- Profile Dropdown --}}
            <div class="dropdown">
                <button class="btn p-0 d-flex align-items-center gap-2"
                        data-bs-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-label="Account menu"
                        style="background: none; border: none;">
                    <img src="{{ auth()->user()->profile_image
                            ? (\Illuminate\Support\Str::startsWith(auth()->user()->profile_image, ['http://', 'https://'])
                                ? auth()->user()->profile_image
                                : asset('storage/' . auth()->user()->profile_image))
                            : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=4f46e5&color=fff&size=40' }}"
                         class="rounded-circle shadow-xs"
                         alt="{{ auth()->user()->name }}"
                         width="34" height="34"
                         style="object-fit: cover; border: 2px solid #e2e8f0;">
                    <span class="d-none d-md-inline" style="font-size: 0.84rem; font-weight: 600; color: #1e293b;">
                        {{ auth()->user()->name }}
                    </span>
                    <i class="bi bi-chevron-down d-none d-md-inline text-muted" style="font-size: 0.65rem;"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow-xl border-0 rounded-3 p-2 mt-2" style="min-width: 210px; border: 1px solid #e2e8f0 !important;">
                    <li class="px-3 py-2 border-bottom mb-1" style="border-color: #f1f5f9 !important;">
                        <p class="mb-0 fw-bold text-dark" style="font-size: 0.85rem;">{{ auth()->user()->name }}</p>
                        <p class="mb-0 text-muted" style="font-size: 0.74rem;">{{ auth()->user()->email }}</p>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill mt-1" style="font-size: 0.65rem; font-weight: 600;">Administrator</span>
                    </li>
                    <li>
                        <a class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2" href="{{ route('admin.profile.edit') }}" style="font-size: 0.825rem; font-weight: 500;">
                            <i class="bi bi-person-circle text-muted"></i> My Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2" href="{{ route('admin.dashboard') }}" style="font-size: 0.825rem; font-weight: 500;">
                            <i class="bi bi-grid text-muted"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2" href="{{ route('admin.site-settings.index') }}" style="font-size: 0.825rem; font-weight: 500;">
                            <i class="bi bi-gear text-muted"></i> Site Settings
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1" style="border-color: #f1f5f9;"></li>
                    <li>
                        <form id="admin-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                        <a class="dropdown-item text-danger rounded-2 py-2 d-flex align-items-center gap-2"
                           href="#"
                           onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();"
                           style="font-size: 0.825rem; font-weight: 600;">
                            <i class="bi bi-box-arrow-right"></i> {{ __('Logout') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- Main Content Container --}}
    <main class="container-fluid px-3 px-md-4 py-4 flex-grow-1 d-flex flex-column" id="main-content">
        <div class="flex-grow-1">
            @yield('content')
        </div>

        {{-- Minimal Modern Footer --}}
        <footer class="mt-4 pt-3 pb-2 text-center text-md-start d-flex flex-column flex-md-row align-items-center justify-content-between text-muted border-top" style="font-size: 0.78rem; border-color: #e2e8f0 !important;">
            <div>
                &copy; {{ date('Y') }} <a href="{{ route('admin.dashboard') }}" class="text-secondary fw-semibold text-decoration-none">{{ config('app.name', 'E-Commerce') }}</a>. All rights reserved.
            </div>
            <div class="d-none d-md-block text-muted">
                <span>Enterprise Admin &bull; v3.5</span>
            </div>
        </footer>
    </main>

</div>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@if (!App::environment('testing'))
    @vite(['resources/js/app.js'])
@endif

{{-- Ensure modals are appended to <body> so parent container stacking contexts do not trap/block modals with backdrops --}}
<script>
$(document).on('show.bs.modal', '.modal', function () {
    if ($(this).parent().is(':not(body)')) {
        $(this).appendTo('body');
    }
});
</script>

{{-- Sidebar Toggle with LocalStorage Memory & Mobile Drawer --}}
<script>
(function () {
    const sidebar   = document.getElementById('sidebar');
    const toggle    = document.getElementById('sidebarToggle');
    const backdrop  = document.getElementById('sidebarBackdrop');
    const isDesktop = () => window.innerWidth >= 992;

    if (!sidebar || !toggle) return;

    // Restore desktop collapsed state from localStorage
    if (isDesktop() && localStorage.getItem('admin_sidebar_collapsed') === 'true') {
        sidebar.classList.add('collapsed');
        toggle.setAttribute('aria-expanded', 'false');
    }

    function toggleSidebar() {
        if (isDesktop()) {
            sidebar.classList.toggle('collapsed');
            const isCollapsed = sidebar.classList.contains('collapsed');
            toggle.setAttribute('aria-expanded', !isCollapsed);
            localStorage.setItem('admin_sidebar_collapsed', isCollapsed ? 'true' : 'false');
        } else {
            const isOpen = sidebar.classList.contains('mobile-open');
            if (isOpen) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        }
    }

    function openMobileSidebar() {
        sidebar.classList.add('mobile-open');
        backdrop.classList.add('show');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileSidebar() {
        sidebar.classList.remove('mobile-open');
        backdrop.classList.remove('show');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        toggleSidebar();
    });

    if (backdrop) {
        backdrop.addEventListener('click', closeMobileSidebar);
    }

    window.addEventListener('resize', function () {
        if (isDesktop() && sidebar.classList.contains('mobile-open')) {
            closeMobileSidebar();
        }
    });
})();
</script>

{{-- Submenu Treeview --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.nav-sidebar > .nav-item > .nav-link').forEach(function (link) {
        const treeview = link.nextElementSibling;
        if (!treeview || !treeview.classList.contains('nav-treeview')) return;

        link.addEventListener('click', function (e) {
            e.preventDefault();
            const item = link.closest('.nav-item');
            const isOpen = item.classList.contains('menu-open');

            item.closest('ul').querySelectorAll('.nav-item.menu-open').forEach(function (openItem) {
                if (openItem !== item) openItem.classList.remove('menu-open');
            });

            item.classList.toggle('menu-open', !isOpen);
        });
    });
});
</script>

{{-- Sidebar Search Filter --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        const navItems = document.querySelectorAll('.nav-sidebar > .nav-item');

        navItems.forEach(function (item) {
            if (item.classList.contains('nav-header')) {
                item.style.display = (q === '') ? '' : 'none';
                return;
            }

            const texts = item.querySelectorAll('.nav-link p, .nav-treeview .nav-link p');
            let match = false;
            texts.forEach(function (t) {
                if (t.textContent.toLowerCase().includes(q)) match = true;
            });

            item.style.display = (q === '' || match) ? '' : 'none';
            if (q !== '' && match) {
                item.classList.add('menu-open');
            } else if (q === '') {
                if (!item.dataset.defaultOpen) item.classList.remove('menu-open');
            }
        });
    });

    document.querySelectorAll('.nav-sidebar > .nav-item.menu-open').forEach(function (item) {
        item.dataset.defaultOpen = '1';
    });
});
</script>

{{-- Modern Toast Notifications System --}}
<script>
(function () {
    const ICONS = {
        success : 'bi bi-check-circle-fill',
        error   : 'bi bi-x-circle-fill',
        warning : 'bi bi-exclamation-triangle-fill',
        info    : 'bi bi-info-circle-fill',
    };
    const TITLES = {
        success : 'Success',
        error   : 'Error',
        warning : 'Warning',
        info    : 'Notice',
    };
    const DURATION = 4200;

    window.showToast = function (type, message) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'adm-toast ' + (type || 'info');
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="t-icon"><i class="${ICONS[type] || ICONS.info}"></i></div>
            <div class="t-body">
                <div class="t-title">${TITLES[type] || TITLES.info}</div>
                <div class="t-msg">${message}</div>
            </div>
            <button class="t-close" aria-label="Close"><i class="bi bi-x-lg"></i></button>
            <div class="t-progress" style="animation-duration:${DURATION}ms;"></div>
        `;

        container.appendChild(toast);

        requestAnimationFrame(() => {
            requestAnimationFrame(() => toast.classList.add('show'));
        });

        toast.querySelector('.t-close').addEventListener('click', () => dismiss(toast));

        const timer = setTimeout(() => dismiss(toast), DURATION);
        toast.addEventListener('mouseenter', () => {
            clearTimeout(timer);
            const p = toast.querySelector('.t-progress');
            if (p) p.style.animationPlayState = 'paused';
        });
        toast.addEventListener('mouseleave', () => {
            const p = toast.querySelector('.t-progress');
            if (p) p.style.animationPlayState = 'running';
            setTimeout(() => dismiss(toast), 800);
        });
    };

    function dismiss(toast) {
        toast.classList.remove('show');
        toast.classList.add('hide');
        toast.addEventListener('transitionend', () => toast.remove(), { once: true });
    }

    @if (session('success'))
        window.addEventListener('DOMContentLoaded', () => showToast('success', '{{ addslashes(session('success')) }}'));
    @elseif (session('error'))
        window.addEventListener('DOMContentLoaded', () => showToast('error', '{{ addslashes(session('error')) }}'));
    @elseif (session('warning'))
        window.addEventListener('DOMContentLoaded', () => showToast('warning', '{{ addslashes(session('warning')) }}'));
    @elseif (session('info'))
        window.addEventListener('DOMContentLoaded', () => showToast('info', '{{ addslashes(session('info')) }}'));
    @endif

    window.addEventListener('toast', (e) => {
        const { type = 'info', message = '' } = e.detail || {};
        if (message) window.showToast(type, message);
    });

    document.addEventListener('livewire:init', () => {
        Livewire.on('toast', (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            const type = data?.type || 'info';
            const message = data?.message || (typeof data === 'string' ? data : '');
            if (message) window.showToast(type, message);
        });
    });

    window.toast = {
        success: (msg) => window.showToast('success', msg),
        error:   (msg) => window.showToast('error', msg),
        warning: (msg) => window.showToast('warning', msg),
        info:    (msg) => window.showToast('info', msg),
    };
})();
</script>

{{-- Tom Select --}}
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.6.1/dist/js/tom-select.complete.min.js"></script>
<script src="{{ asset('js/admin-select.js') }}?v=5"></script>
<script src="{{ asset('js/admin-combobox.js') }}?v=3"></script>

@include('components.command-menu')

@stack('modals')

{{-- DataTables Hybrid Loading UX Enhancer --}}
<script src="{{ asset('js/admin-datatables.js') }}?v=1"></script>

@yield('js')
@stack('js')
</body>

</html>
