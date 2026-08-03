<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('storage/logo_icon/shopping.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Admin'))</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@0,700;1,700&family=Noto+Sans+Khmer:wght@100..900&family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .exo-2-bold {
            font-family: "Exo 2", sans-serif;
            font-optical-sizing: auto;
            font-weight: 700;
            font-style: normal;
        }
    </style>

    {{-- Bootstrap 5 (CDN — loaded FIRST so our Vite overrides always win) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    {{-- Compiled design system (tokens → components → admin shell) — AFTER Bootstrap so we win every tie --}}
    @if (!App::environment('testing'))
        @vite(['resources/sass/app.scss'])
    @endif

    {{-- Custom Toast --}}
    <style>
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
            min-width: 300px;
            max-width: 380px;
            padding: 14px 16px;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(0,0,0,.10), 0 2px 6px rgba(0,0,0,.06);
            border-left: 4px solid transparent;
            opacity: 0;
            transform: translateX(32px);
            transition: opacity .28s ease, transform .28s ease;
            position: relative;
            overflow: hidden;
        }
        .adm-toast.show {
            opacity: 1;
            transform: translateX(0);
        }
        .adm-toast.hide {
            opacity: 0;
            transform: translateX(32px);
        }
        /* type colours */
        .adm-toast.success { border-left-color: #22c55e; }
        .adm-toast.error   { border-left-color: #ef4444; }
        .adm-toast.warning { border-left-color: #f59e0b; }
        .adm-toast.info    { border-left-color: #3b82f6; }

        .adm-toast .t-icon {
            flex-shrink: 0;
            width: 34px; height: 34px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem;
        }
        .adm-toast.success .t-icon { background: #f0fdf4; color: #16a34a; }
        .adm-toast.error   .t-icon { background: #fef2f2; color: #dc2626; }
        .adm-toast.warning .t-icon { background: #fffbeb; color: #d97706; }
        .adm-toast.info    .t-icon { background: #eff6ff; color: #2563eb; }

        .adm-toast .t-body { flex: 1; min-width: 0; }
        .adm-toast .t-title {
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .adm-toast.success .t-title { color: #16a34a; }
        .adm-toast.error   .t-title { color: #dc2626; }
        .adm-toast.warning .t-title { color: #d97706; }
        .adm-toast.info    .t-title { color: #2563eb; }

        .adm-toast .t-msg {
            font-size: .85rem;
            color: #374151;
            font-weight: 500;
            line-height: 1.4;
        }
        .adm-toast .t-close {
            flex-shrink: 0;
            background: none; border: none; padding: 0;
            color: #9ca3af; font-size: .75rem;
            cursor: pointer; line-height: 1;
            margin-top: 1px;
            transition: color .15s;
        }
        .adm-toast .t-close:hover { color: #374151; }
        /* progress bar */
        .adm-toast .t-progress {
            position: absolute;
            bottom: 0; left: 0;
            height: 3px;
            border-radius: 0 0 12px 12px;
            width: 100%;
            transform-origin: left;
            animation: t-shrink linear forwards;
        }
        .adm-toast.success .t-progress { background: #22c55e; }
        .adm-toast.error   .t-progress { background: #ef4444; }
        .adm-toast.warning .t-progress { background: #f59e0b; }
        .adm-toast.info    .t-progress { background: #3b82f6; }
        @keyframes t-shrink {
            from { transform: scaleX(1); }
            to   { transform: scaleX(0); }
        }
    </style>

    {{-- Tom Select --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.6.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin-select.css') }}?v=3" rel="stylesheet">
    <link href="{{ asset('css/vendor-panel.css') }}?v=2" rel="stylesheet">

    {{-- Global Fade-In Animations --}}
    <style>
        /* ── Keyframes ─────────────────────────────────────────── */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-20px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(20px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* ── Page-level wrapper ─────────────────────────────────── */
        #content {
            animation: fadeIn 0.45s ease both;
        }

        /* ── Cards & panels ─────────────────────────────────────── */
        .card,
        .table-responsive,
        .dashboard-stat {
            animation: fadeIn 0.45s ease both;
        }

        /* ── Stagger children inside card bodies ─────────────────── */
        .card-body > *,
        .table-responsive > * {
            animation: fadeIn 0.4s ease both;
        }
        .card-body > *:nth-child(1) { animation-delay: 0.05s; }
        .card-body > *:nth-child(2) { animation-delay: 0.10s; }
        .card-body > *:nth-child(3) { animation-delay: 0.15s; }
        .card-body > *:nth-child(4) { animation-delay: 0.20s; }
        .card-body > *:nth-child(5) { animation-delay: 0.25s; }

        /* ── Table rows ──────────────────────────────────────────── */
        tbody tr {
            animation: fadeIn 0.35s ease both;
        }
        tbody tr:nth-child(1)  { animation-delay: 0.04s; }
        tbody tr:nth-child(2)  { animation-delay: 0.08s; }
        tbody tr:nth-child(3)  { animation-delay: 0.12s; }
        tbody tr:nth-child(4)  { animation-delay: 0.16s; }
        tbody tr:nth-child(5)  { animation-delay: 0.20s; }
        tbody tr:nth-child(6)  { animation-delay: 0.24s; }
        tbody tr:nth-child(7)  { animation-delay: 0.28s; }
        tbody tr:nth-child(8)  { animation-delay: 0.32s; }
        tbody tr:nth-child(9)  { animation-delay: 0.36s; }
        tbody tr:nth-child(10) { animation-delay: 0.40s; }

        /* ── Form elements ───────────────────────────────────────── */
        form .mb-3,
        form .form-group {
            animation: fadeIn 0.4s ease both;
        }
        form .mb-3:nth-child(1),
        form .form-group:nth-child(1) { animation-delay: 0.05s; }
        form .mb-3:nth-child(2),
        form .form-group:nth-child(2) { animation-delay: 0.10s; }
        form .mb-3:nth-child(3),
        form .form-group:nth-child(3) { animation-delay: 0.15s; }
        form .mb-3:nth-child(4),
        form .form-group:nth-child(4) { animation-delay: 0.20s; }
        form .mb-3:nth-child(5),
        form .form-group:nth-child(5) { animation-delay: 0.25s; }

        /* ── Page headings ───────────────────────────────────────── */
        h1, h2, h3, h4, h5, h6,
        .page-title {
            animation: fadeIn 0.4s ease both;
            animation-delay: 0.05s;
        }

        /* ── Accessible Focus Rings (a11y) ─────────────────────── */
        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible,
        .btn:focus-visible {
            outline: 2px solid #6366f1 !important;
            outline-offset: 2px !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15) !important;
        }

        /* ── Respect user motion preference ─────────────────────── */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-delay: 0s !important;
            }
        }
    </style>

    @yield('css')
</head>

<body>

{{-- ─────────────────────────────────────────────────────────────────
     TOAST CONTAINER
──────────────────────────────────────────────────────────────────── --}}
<div id="toast-container" aria-live="polite" aria-atomic="true"></div>

{{-- ─────────────────────────────────────────────────────────────────
     SKIP LINK  (a11y)
──────────────────────────────────────────────────────────────────── --}}
<a href="#main-content" class="skip-link">{{ 'Skip to content' }}</a>

{{-- ─────────────────────────────────────────────────────────────────
     SIDEBAR
──────────────────────────────────────────────────────────────────── --}}
@include('admin.layouts.sidebar')

{{-- ─────────────────────────────────────────────────────────────────
     CONTENT AREA
──────────────────────────────────────────────────────────────────── --}}
<div id="content" class="content-wrapper d-flex flex-column min-vh-100">


    {{-- ── TOPBAR ────────────────────────────────────────────────── --}}
    <nav class="navbar navbar-expand navbar-light p-0 main-header" aria-label="Top navigation">

        {{-- Left: sidebar toggle + Command Menu search trigger --}}
        <div class="d-flex align-items-center gap-3 ps-3">
            <button class="btn btn-outline-secondary border-0 p-2" id="sidebarToggle" aria-label="Toggle sidebar" aria-expanded="true" aria-controls="sidebar">
                <i class="fas fa-bars" style="font-size:1.1rem;"></i>
            </button>
            <button type="button" class="btn btn-sm btn-light border d-flex align-items-center gap-2 px-3 py-1-5 text-muted rounded-pill shadow-xs" onclick="window.openCmdk()" style="font-size:0.85rem; font-weight:500;">
                <i class="bi bi-search text-primary"></i>
                <span class="d-none d-sm-inline">Search or command...</span>
                <kbd class="bg-white border text-dark ms-2 shadow-xs" style="font-size:0.68rem; padding: 2px 6px; border-radius: 4px;">⌘K</kbd>
            </button>
        </div>

        {{-- Right: notifications + profile --}}
        <div class="d-flex align-items-center gap-2 ms-auto pe-4">

            {{-- Notifications --}}
            <button class="btn btn-light position-relative p-2" aria-label="Notifications">
                <i class="bi bi-bell" style="font-size: 1.1rem;"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                    <span class="visually-hidden">{{ 'New alerts' }}</span>
                </span>
            </button>

            {{-- Divider --}}
            <div style="width:1px; height:22px; background:var(--border-color);"></div>

            {{-- Profile Dropdown --}}
            <div class="dropdown">
                <button class="btn p-0 d-flex align-items-center gap-2"
                        data-bs-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-label="Account menu"
                        style="background:none; border:none;">
                    <img src="{{ auth()->user()->profile_image
                            ? (\Illuminate\Support\Str::startsWith(auth()->user()->profile_image, ['http://', 'https://'])
                                ? auth()->user()->profile_image
                                : asset('storage/' . auth()->user()->profile_image))
                            : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=6366f1&color=fff&size=40' }}"
                         class="rounded-circle"
                         alt="{{ auth()->user()->name }}"
                         width="34" height="34"
                         style="object-fit:cover; border:2px solid var(--border-color); border-radius:50%; transition:all .2s;">
                    <span class="d-none d-md-inline" style="font-size:.82rem; font-weight:600; color:var(--text-secondary);">
                        {{ auth()->user()->name }}
                    </span>
                    <i class="bi bi-chevron-down d-none d-md-inline" style="font-size:.65rem; opacity:.5;"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end" style="min-width:200px;">
                    <li>
                        <div class="px-3 py-2 border-bottom" style="border-color:var(--border-subtle) !important;">
                            <p class="mb-0" style="font-size:.8rem; font-weight:600; color:var(--text-primary);">
                                {{ auth()->user()->name }}
                            </p>
                            <p class="mb-0" style="font-size:.72rem; color:var(--text-muted);">
                                {{ auth()->user()->email }}
                            </p>
                        </div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.profile.edit') }}">
                            <i class="bi bi-person-circle"></i>
                            {{ 'My Profile' }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                            <i class="bi bi-grid"></i>
                            {{ 'Dashboard' }}
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form id="admin-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                        <a class="dropdown-item text-danger"
                           href="#"
                           onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                            <i class="bi bi-box-arrow-right"></i>
                            {{ __('Logout') }}
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </nav>
    {{-- ── END TOPBAR ───────────────────────────────────────────── --}}

    {{-- ── MAIN CONTENT ─────────────────────────────────────────── --}}
    <div class="container-fluid px-4 mt-4 flex-grow-1 d-flex flex-column" id="main-content">
        <div class="flex-grow-1">
            @yield('content')
        </div>

        {{-- ── MAIN FOOTER ────────────────────────────────────────────── --}}
        <footer class="main-footer bg-white border py-3 px-4 mt-4 mb-4 rounded-3 d-flex align-items-center justify-content-between text-muted shadow-sm" style="font-size:0.875rem;">
            <div>
                <strong>Copyright &copy; {{ date('Y') }} <a href="{{ route('admin.dashboard') }}" class="text-primary text-decoration-none">{{ config('app.name', 'E-Commerce') }}</a>.</strong> All rights reserved.
            </div>
            <div class="d-none d-sm-block">
                <b>Version</b> 3.2.0
            </div>
        </footer>
    </div>

</div>
{{-- ── END CONTENT AREA ─────────────────────────────────────────── --}}



{{-- ─────────────────────────────────────────────────────────────────
     SCRIPTS
──────────────────────────────────────────────────────────────────── --}}
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

{{-- Sidebar toggle --}}
<script>
(function () {
    const sidebar  = document.querySelector('.main-sidebar');
    const toggle   = document.getElementById('sidebarToggle');
    const isDesktop = () => window.innerWidth >= 992;

    if (!sidebar || !toggle) return;

    toggle.addEventListener('click', function () {
        sidebar.classList.toggle('collapsed');
        toggle.setAttribute('aria-expanded', !sidebar.classList.contains('collapsed'));
    });

    // Close sidebar overlay when clicking outside on mobile
    document.addEventListener('click', function (e) {
        if (!isDesktop() && sidebar.classList.contains('collapsed') &&
            !sidebar.contains(e.target) && !toggle.contains(e.target)) {
            sidebar.classList.remove('collapsed');
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
})();
</script>

{{-- AdminLTE-style treeview: toggle submenus on parent nav-link click --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Each nav-link that has a sibling .nav-treeview is a tree toggle
    document.querySelectorAll('.nav-sidebar > .nav-item > .nav-link').forEach(function (link) {
        const treeview = link.nextElementSibling;
        if (!treeview || !treeview.classList.contains('nav-treeview')) return;

        link.addEventListener('click', function (e) {
            e.preventDefault();
            const item = link.closest('.nav-item');
            const isOpen = item.classList.contains('menu-open');

            // Close all siblings
            item.closest('ul').querySelectorAll('.nav-item.menu-open').forEach(function (openItem) {
                if (openItem !== item) openItem.classList.remove('menu-open');
            });

            item.classList.toggle('menu-open', !isOpen);
        });
    });
});
</script>

{{-- Sidebar search filter --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        const navItems = document.querySelectorAll('.nav-sidebar > .nav-item');

        navItems.forEach(function (item) {
            if (item.classList.contains('nav-header')) {
                item.style.display = '';
                return;
            }

            const texts = item.querySelectorAll('.nav-link p, .nav-treeview .nav-link p');
            let match = false;
            texts.forEach(function (t) {
                if (t.textContent.toLowerCase().includes(q)) match = true;
            });

            item.style.display = (q === '' || match) ? '' : 'none';

            // Auto-expand matching items during search
            if (q !== '' && match) {
                item.classList.add('menu-open');
            } else if (q === '') {
                // Restore: keep open only if it was server-rendered open
                if (!item.dataset.defaultOpen) item.classList.remove('menu-open');
            }
        });
    });

    // Remember which items were rendered open (active from server)
    document.querySelectorAll('.nav-sidebar > .nav-item.menu-open').forEach(function (item) {
        item.dataset.defaultOpen = '1';
    });
});
</script>

{{-- Notifications --}}
<script>
(function () {
    const ICONS = {
        success : 'bi bi-check-circle-fill',
        error   : 'bi bi-x-circle-fill',
        warning : 'bi bi-exclamation-triangle-fill',
        info    : 'bi bi-info-circle-fill',
    };
    const TITLES = {
        success : '{{ 'Success' }}',
        error   : '{{ 'Error' }}',
        warning : '{{ 'Warning' }}',
        info    : '{{ 'Info' }}',
    };
    const DURATION = 4500; // ms

    window.showToast = function (type, message) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = 'adm-toast ' + type;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="t-icon"><i class="${ICONS[type]}"></i></div>
            <div class="t-body">
                <div class="t-title">${TITLES[type]}</div>
                <div class="t-msg">${message}</div>
            </div>
            <button class="t-close" aria-label="Close"><i class="bi bi-x-lg"></i></button>
            <div class="t-progress" style="animation-duration:${DURATION}ms;"></div>
        `;

        container.appendChild(toast);

        // trigger enter animation
        requestAnimationFrame(() => {
            requestAnimationFrame(() => toast.classList.add('show'));
        });

        // close button
        toast.querySelector('.t-close').addEventListener('click', () => dismiss(toast));

        // auto-dismiss
        const timer = setTimeout(() => dismiss(toast), DURATION);
        toast.addEventListener('mouseenter', () => {
            clearTimeout(timer);
            toast.querySelector('.t-progress').style.animationPlayState = 'paused';
        });
        toast.addEventListener('mouseleave', () => {
            toast.querySelector('.t-progress').style.animationPlayState = 'running';
            setTimeout(() => dismiss(toast), 800);
        });
    };

    function dismiss(toast) {
        toast.classList.remove('show');
        toast.classList.add('hide');
        toast.addEventListener('transitionend', () => toast.remove(), { once: true });
    }

    // fire on page load from Laravel session
    @if (session('success'))
        window.addEventListener('DOMContentLoaded', () => showToast('success', '{{ addslashes(session('success')) }}'));
    @elseif (session('error'))
        window.addEventListener('DOMContentLoaded', () => showToast('error', '{{ addslashes(session('error')) }}'));
    @elseif (session('warning'))
        window.addEventListener('DOMContentLoaded', () => showToast('warning', '{{ addslashes(session('warning')) }}'));
    @elseif (session('info'))
        window.addEventListener('DOMContentLoaded', () => showToast('info', '{{ addslashes(session('info')) }}'));
    @endif

    // Listen for custom JS events: window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: '...' } }))
    window.addEventListener('toast', (e) => {
        const { type = 'info', message = '' } = e.detail || {};
        if (message) window.showToast(type, message);
    });

    // Listen for Livewire 3 events
    document.addEventListener('livewire:init', () => {
        Livewire.on('toast', (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            const type = data?.type || 'info';
            const message = data?.message || (typeof data === 'string' ? data : '');
            if (message) window.showToast(type, message);
        });
    });

    // Alias for Sonner-style API calls: toast.success('...'), toast.error('...'), etc.
    window.sonner = window.toast = {
        success: (msg) => window.showToast('success', msg),
        error:   (msg) => window.showToast('error', msg),
        warning: (msg) => window.showToast('warning', msg),
        info:    (msg) => window.showToast('info', msg),
    };
})();
</script>

{{-- Tom Select --}}
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.6.1/dist/js/tom-select.complete.min.js"></script>
<script src="{{ asset('js/admin-select.js') }}?v=3"></script>
<script src="{{ asset('js/admin-combobox.js') }}?v=3"></script>

@include('components.command-menu')

@stack('modals')

@yield('js')
</body>

</html>
