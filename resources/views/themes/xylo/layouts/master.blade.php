<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('storage/logo_icon/shopping.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TVR') }}</title>
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

    {{-- Core Scripts: jQuery, Bootstrap, Slick, Toastr --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" defer></script>

    {{-- Compiled Vite Storefront Application JS --}}
    @if (!App::environment('testing'))
        @vite(['resources/views/themes/xylo/js/app.js'])
    @endif

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