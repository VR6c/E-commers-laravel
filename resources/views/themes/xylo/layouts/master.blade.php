<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ getSiteLogo() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') | @endif{{ config('app.name', 'TVR') }}</title>
    <meta name="description" content="@yield('meta_description', 'Shop quality products from verified vendors with fast delivery and secure checkout.')">
    <meta name="theme-color" content="#6366f1">
    @yield('preload')

    {{-- Preconnect external assets --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

    {{-- Primary Brand Typography --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">

    {{-- Bootstrap & Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" media="print" onload="this.media='all'" />
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" /></noscript>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" media="print" onload="this.media='all'"/>
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"/></noscript>

    {{-- Compiled Vite Storefront Stylesheet --}}
    @if (!App::environment('testing'))
        @vite(['resources/views/themes/xylo/sass/app.scss'])
    @endif

    @yield('css')
</head>
<body>
    <a href="#main-content" class="skip-link">{{ 'Skip to content' }}</a>

    @include('themes.xylo.layouts.header')

    <main id="main-content">
        @yield('content')
    </main>

    @include('themes.xylo.layouts.footer')

    {{-- Mobile bottom tab bar (hidden on desktop via CSS) --}}
    <nav class="xsf-tabbar" aria-label="{{ 'Mobile navigation' }}">
        <a href="{{ route('xylo.home') }}" class="xsf-tabbar__item {{ request()->routeIs('xylo.home') ? 'is-active' : '' }}">
            <i class="fa-solid fa-house" aria-hidden="true"></i><span>Home</span>
        </a>
        <a href="{{ route('shop.index') }}" class="xsf-tabbar__item {{ request()->routeIs('shop.*', 'category.*', 'product.*') ? 'is-active' : '' }}">
            <i class="fa-solid fa-store" aria-hidden="true"></i><span>Shop</span>
        </a>
        <a href="{{ route('customer.wishlist.index') }}" class="xsf-tabbar__item {{ request()->routeIs('customer.wishlist.*') ? 'is-active' : '' }}">
            <i class="fa-regular fa-heart" aria-hidden="true"></i><span>Wishlist</span>
        </a>
        <a href="{{ route('cart.view') }}" class="xsf-tabbar__item {{ request()->routeIs('cart.*', 'checkout.*') ? 'is-active' : '' }}">
            <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i><span>Cart</span>
        </a>
    </nav>

    {{-- Core Scripts: jQuery, Bootstrap, Slick, Toastr --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" defer></script>

    {{-- Compiled Vite Storefront Application JS --}}
    @if (!App::environment('testing'))
        @vite(['resources/views/themes/xylo/js/app.js'])
    @endif

    {{-- Global Toastr Configuration --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if (window.toastr) {
                window.toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    newestOnTop: true,
                    positionClass: "toast-top-right",
                    preventDuplicates: false,
                    timeOut: 2500,
                    extendedTimeOut: 1000,
                    showDuration: 250,
                    hideDuration: 250,
                    showMethod: "fadeIn",
                    hideMethod: "fadeOut",
                };
            }
        });
    </script>

    {{-- Session Flash Notifications --}}
    @if (session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                if (window.toastr) toastr.error("{{ session('error') }}");
            });
        </script>
    @endif
    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                if (window.toastr) toastr.success("{{ session('success') }}");
            });
        </script>
    @endif

    @yield('js')
</body>
</html>