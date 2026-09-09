@extends('themes.xylo.layouts.master')
@section('content')
    @php $currency = activeCurrency(); @endphp

    {{-- Hero / Banner --}}
    <section class="xsf-hero">
        <div class="container">
            <div class="banner-slider xsf-hero__slider">
                @foreach ($banners as $banner)
                    <div>
                        <div class="row align-items-center xsf-hero__slide">
                            <div class="col-lg-6">
                                <span class="xsf-hero__eyebrow">{{ 'New Arrivals' }}</span>
                                <h1 class="xsf-hero__title">
                                    {{ $banner->title }}
                                </h1>
                                <p class="xsf-hero__text">{{ 'Discover our latest collection of premium products.' }}</p>
                                <a href="{{ route('shop.index') }}" class="btn btn-primary btn-pill btn-lg">
                                    {{ 'Shop Now' }}
                                </a>
                            </div>
                            <div class="col-lg-6">
                                <div class="xsf-hero__media">
                                    <img src="{{ Storage::url($banner->image_url ?? 'default.jpg') }}"
                                        onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1526738549149-8e07eca6c147?w=800&auto=format&fit=crop&q=80';"
                                        class="img-fluid" alt="{{ $banner->title }}">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Categories --}}
    <section class="xsf-section">
        <div class="container">
            <div class="xsf-section__head">
                <h2 class="xsf-section__title sec-heading">{{ 'Explore Popular Categories' }}</h2>
            </div>
            <div class="category-slider xsf-category-slider">
                @foreach ($categories as $category)
                    <div>
                        <a href="{{ route('category.show', $category->slug) }}" class="xsf-category-card">
                            <span class="xsf-category-card__img">
                                <img src="{{ Storage::url($category->image_url ?? 'default.jpg') }}"
                                    onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&auto=format&fit=crop&q=80';"
                                    loading="lazy" alt="{{ $category->name ?? 'Category' }}">
                            </span>
                            <span class="xsf-category-card__name">{{ $category->name ?? 'Category' }}</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Trending (slider) --}}
    <section class="xsf-section">
        <div class="container position-relative">
            <div class="xsf-section__head">
                <h2 class="xsf-section__title sec-heading">{{ 'Trending Products' }}</h2>
                <div class="custom-arrows xsf-slider-arrows">
                    <button class="prev" aria-label="{{ 'Previous' }}"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="next" aria-label="{{ 'Next' }}"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
            <div class="product-slider xsf-product-slider">
                @foreach ($products as $product)
                    <div>
                        @include('themes.xylo.partials.product-card', ['product' => $product, 'currency' => $currency])
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Featured (grid) --}}
    <section class="xsf-section">
        <div class="container">
            <div class="xsf-section__head">
                <h2 class="xsf-section__title sec-heading">{{ 'Featured Products' }}</h2>
            </div>
            <div class="row g-4">
                @foreach ($products as $product)
                    <div class="col-6 col-lg-3">
                        @include('themes.xylo.partials.product-card', ['product' => $product, 'currency' => $currency])
                    </div>
                @endforeach
            </div>
            <div class="text-center mt-5">
                <a href="{{ route('shop.index') }}" class="btn btn-outline-primary btn-pill">{{ 'View All' }}</a>
            </div>
        </div>
    </section>

    {{-- Why choose us --}}
    <section class="xsf-section xsf-features">
        <div class="container">
            <div class="xsf-section__head">
                <h2 class="xsf-section__title sec-heading">{{ 'Why Choose Us' }}</h2>
            </div>
            <div class="row g-4">
                @php
                    $features = [
                        ['img' => 'https://i.ibb.co/WNQXhLnP/choose-icon1.png', 'title' => 'Fast Delivery', 'text' => 'Get your orders delivered quickly and reliably.'],
                        ['img' => 'https://i.ibb.co/FkmgGPrr/choose-icon2.png', 'title' => '24/7 Support', 'text' => 'Our team is always here to help you.'],
                        ['img' => 'https://i.ibb.co/CffNqX9/choose-icon3.png', 'title' => 'Trusted Worldwide', 'text' => 'Thousands of happy customers around the globe.'],
                        ['img' => 'https://i.ibb.co/XPvjQGG/choose-icon4.png', 'title' => '10+ Years of Service', 'text' => 'A decade of delivering quality and excellence.'],
                    ];
                @endphp
                @foreach ($features as $feature)
                    <div class="col-6 col-lg-3">
                        <div class="xsf-feature">
                            <div class="xsf-feature__icon">
                                <img src="{{ $feature['img'] }}" alt="" aria-hidden="true">
                            </div>
                            <h3 class="xsf-feature__title">{{ $feature['title'] }}</h3>
                            <p class="xsf-feature__text">{{ $feature['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script>
        function addToCart(productId) {
            fetch("{{ route('cart.add') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ product_id: productId, quantity: 1 })
            })
            .then(response => response.json())
            .then(data => {
                toastr.success(data.message || "{{ 'Added to cart' }}");
                updateCartCount(data.cart);
            })
            .catch(error => console.error("Error:", error));
        }

        function updateCartCount(cart) {
            let totalCount = Object.values(cart || {}).reduce((sum, item) => sum + item.quantity, 0);
            const el = document.getElementById("cart-count");
            if (el) {
                el.textContent = totalCount;
                el.classList.toggle('d-none', totalCount === 0);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.wishlist-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const btn = this;
                    const productId = btn.getAttribute('data-product-id');
                    fetch('{{ route('customer.wishlist.toggle') }}', {
                        method: 'POST',
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json",
                        },
                        body: JSON.stringify({ product_id: productId })
                    })
                    .then(response => {
                        if (response.status === 401) {
                            window.location.href = '/customer/login';
                            return;
                        } else if (response.ok) {
                            return response.json();
                        } else {
                            throw new Error('Something went wrong');
                        }
                    })
                    .then(data => {
                        if (!data) return;
                        const icon = btn.querySelector('i');
                        if (data.status === 'added') {
                            icon.classList.replace('fa-regular', 'fa-solid');
                            btn.classList.add('is-active');
                            toastr.success(data.message);
                        } else {
                            icon.classList.replace('fa-solid', 'fa-regular');
                            btn.classList.remove('is-active');
                            toastr.info(data.message);
                        }
                        // Update header badge
                        const badge = document.getElementById('wishlist-count');
                        if (badge && data.count !== undefined) {
                            badge.textContent = data.count;
                            badge.classList.toggle('d-none', data.count === 0);
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            });
        });
    </script>
@endsection
