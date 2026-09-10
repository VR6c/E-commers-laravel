<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('storage/logo_icon/shopping.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TVR') }}</title>
    @yield('preload')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

    @if (!App::environment('testing'))
        @vite(['resources/views/themes/xylo/sass/app.scss'])
    @endif
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@0,700;1,700&family=Noto+Sans+Khmer:wght@100..900&family=Plus+Jakarta+Sans:wght@600;700;800&family=Poppins:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap">
    <style>
        .exo-2-bold {
            font-family: "Exo 2", sans-serif;
            font-optical-sizing: auto;
            font-weight: 700;
            font-style: normal;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Non-critical styles loaded asynchronously to eliminate render-blocking in Lighthouse/PSI -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" media="print" onload="this.media='all'" />
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" /></noscript>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" media="print" onload="this.media='all'"/>
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"/></noscript>
    @yield('css')

    {{-- Global Fade-In Animations --}}
    <style>
        .animate__animated {
            animation-duration: 0.6s;
            animation-fill-mode: both;
        }
        .animate__fadeIn {
            animation-name: fadeIn;
        }
        /* ── Keyframes ─────────────────────────────────────────── */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-24px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(24px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Main content area ──────────────────────────────────── */
        #main-content {
            animation: fadeIn 0.5s ease both;
        }

        /* ── Navigation / Header ────────────────────────────────── */
        header,
        nav,
        .navbar {
            animation: fadeInDown 0.45s ease both;
        }

        /* ── Sections & product cards ───────────────────────────── */
        section,
        .section {
            animation: fadeIn 0.5s ease both;
        }

        /* ── Product cards with stagger ─────────────────────────── */
        .product-card,
        .card,
        .item-card {
            animation: fadeIn 0.45s ease both;
        }

        /* Stagger grid items (works for up to 12 items) */
        .row > [class*="col"]:nth-child(1)  { animation: fadeIn 0.4s 0.00s ease both; }
        .row > [class*="col"]:nth-child(2)  { animation: fadeIn 0.4s 0.06s ease both; }
        .row > [class*="col"]:nth-child(3)  { animation: fadeIn 0.4s 0.12s ease both; }
        .row > [class*="col"]:nth-child(4)  { animation: fadeIn 0.4s 0.18s ease both; }
        .row > [class*="col"]:nth-child(5)  { animation: fadeIn 0.4s 0.24s ease both; }
        .row > [class*="col"]:nth-child(6)  { animation: fadeIn 0.4s 0.30s ease both; }
        .row > [class*="col"]:nth-child(7)  { animation: fadeIn 0.4s 0.36s ease both; }
        .row > [class*="col"]:nth-child(8)  { animation: fadeIn 0.4s 0.42s ease both; }
        .row > [class*="col"]:nth-child(9)  { animation: fadeIn 0.4s 0.48s ease both; }
        .row > [class*="col"]:nth-child(10) { animation: fadeIn 0.4s 0.54s ease both; }
        .row > [class*="col"]:nth-child(11) { animation: fadeIn 0.4s 0.60s ease both; }
        .row > [class*="col"]:nth-child(12) { animation: fadeIn 0.4s 0.66s ease both; }

        /* ── Hero / Banner ──────────────────────────────────────── */
        .banner,
        .hero,
        .slider,
        .carousel {
            animation: fadeIn 0.6s ease both;
        }

        /* ── Headings ───────────────────────────────────────────── */
        h1, h2, h3, h4, h5, h6 {
            animation: fadeIn 0.45s ease both;
            animation-delay: 0.05s;
        }

        /* ── Sidebar / Category list ────────────────────────────── */
        aside,
        .sidebar,
        .category-list {
            animation: fadeInLeft 0.45s ease both;
        }

        /* ── Footer ─────────────────────────────────────────────── */
        footer {
            animation: fadeIn 0.5s ease both;
        }

        /* ── Breadcrumb ─────────────────────────────────────────── */
        .breadcrumb-wrap,
        .breadcrumb {
            animation: fadeIn 0.4s 0.1s ease both;
        }

        /* ── Buttons ─────────────────────────────────────────────── */
        .btn {
            animation: fadeIn 0.4s ease both;
        }

        /* ── Respect user motion preference ─────────────────────── */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-delay: 0s !important;
            }
        }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">{{ 'Skip to content' }}</a>
    @include('themes.xylo.layouts.header')
    <main id="main-content">
        @yield('content')
    </main>
    @include('themes.xylo.layouts.footer')
    @if (!App::environment('testing'))
        @vite(['resources/views/themes/xylo/js/app.js'])
    @endif
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" defer></script>
    <script>
        // Global Toastr defaults — applied to every toast across the storefront
        toastr.options = {
            closeButton:       true,
            progressBar:       true,
            newestOnTop:       true,
            positionClass:     'toast-top-right',
            preventDuplicates: false,
            timeOut:           5000,
            extendedTimeOut:   2000,
            showDuration:      300,
            hideDuration:      400,
            showEasing:        'swing',
            hideEasing:        'linear',
            showMethod:        'fadeIn',
            hideMethod:        'fadeOut',
        };
    </script>
    @yield('js')
    @if (session('error'))
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            toastr.error("{{ session('error') }}");
        });
    </script>
    @endif
    <script>
        $(document).ready(function () {
            $('.category-slider').slick({
                slidesToShow: 4,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 2000,
                dots: false,
                arrows: true,
                prevArrow: '<button class="slick-prev"><i class="fa fa-angle-left"></i></button>',
                nextArrow: '<button class="slick-next"><i class="fa fa-angle-right"></i></button>',
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 3,
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 1,
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1,
                        }
                    }
                ]
            });
            $('.banner-slider').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplay: true,
                fade: true,
                speed: 500,
                cssEase: 'linear',
                autoplaySpeed: 5000,
                dots: true,
                arrows: false,
            });
            $('.product-slider').slick({
                slidesToShow: 4,
                slidesToScroll: 1,
                infinite: true,
                autoplay: true,
                autoplaySpeed: 3000,
                arrows: true,
                prevArrow: '.custom-arrows .prev',
                nextArrow: '.custom-arrows .next',
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 3,
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 2,
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1,
                        }
                    }
                ]
            });
        });
    </script>
    <script>
        /* header script */
        document.addEventListener('DOMContentLoaded', function() {
            const accountToggle = document.getElementById('accountDropdown');
            const accountMenu = document.querySelector('.account-menu');

            if (accountToggle && accountMenu) {
                document.addEventListener('click', function(event) {
                    if (!accountToggle.contains(event.target) && !accountMenu.contains(event.target)) {
                        accountMenu.classList.remove('show');
                    }
                });
            }
        });
    </script>
    <script>
        /* product seach input */
        $(document).ready(function () {
            $('#search-input').on('keyup', function () {
                let query = $(this).val();
                if (query.length > 2) {
                    $.ajax({
                        url: '{{ url('/search-suggestions') }}',
                        type: 'GET',
                        data: { q: query },
                        success: function (data) {
                            let suggestions = $('#search-suggestions');
                            suggestions.html('');
                            if (data.length > 0) {
                                data.forEach(product => {
                                    suggestions.append(`
                                        <a href="/product/${product.slug}" class="dropdown-item d-flex align-items-center">
                                            <img src="${product.thumbnail}" alt="${product.name}" class="me-2" width="40" height="40" style="object-fit: cover; border-radius: 5px;">
                                            <span class="search-product-title">${product.name}</span>
                                        </a>
                                    `);
                                });
                                suggestions.removeClass('d-none');
                            } else {
                                suggestions.addClass('d-none');
                            }
                        }
                    });
                } else {
                    $('#search-suggestions').addClass('d-none');
                }
            });

            $(document).on('click', function (event) {
                if (!$(event.target).closest('#search-input, #search-suggestions').length) {
                    $('#search-suggestions').addClass('d-none');
                }
            });
        });


    </script>
</body>
</html>