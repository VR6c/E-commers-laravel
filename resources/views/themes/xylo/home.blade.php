@extends('themes.xylo.layouts.master')

@section('preload')
    @if ($banners->isNotEmpty())
        <link rel="preload" as="image" href="{{ optimized_image_url($banners->first()->image_url) }}" fetchpriority="high">
    @endif
@endsection

@section('content')
    @php $currency = activeCurrency(); @endphp

    {{-- Hero / Banner Slider --}}
    <section class="xsf-hero">
        <div class="container">
            <div class="banner-slider xsf-hero__slider">
                @foreach ($banners as $index => $banner)
                    <div>
                        <div class="row align-items-center xsf-hero__slide">
                            <div class="col-lg-6">
                                <span class="xsf-hero__eyebrow">
                                    <i class="fa-solid fa-sparkles me-1"></i> {{ 'New Collection' }}
                                </span>
                                <h1 class="xsf-hero__title">
                                    {{ $banner->title }}
                                </h1>
                                <p class="xsf-hero__text">{{ 'Discover our latest collection of premium products, designed for your lifestyle.' }}</p>
                                <div class="xsf-hero__cta-row">
                                    <a href="{{ route('shop.index') }}" class="btn btn-primary btn-pill btn-lg">
                                        <span>{{ 'Shop Now' }}</span>
                                        <i class="fa-solid fa-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="xsf-hero__media">
                                    <img src="{{ optimized_image_url($banner->image_url ?? 'default.jpg') }}"
                                        onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1526738549149-8e07eca6c147?w=800&auto=format&fit=crop&q=80';"
                                        class="img-fluid"
                                        width="600"
                                        height="420"
                                        style="aspect-ratio: 10/7; object-fit: cover;"
                                        @if ($index === 0)
                                            fetchpriority="high"
                                            loading="eager"
                                        @else
                                            loading="lazy"
                                            decoding="async"
                                        @endif
                                        alt="{{ $banner->title }}">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Popular Categories --}}
    <section class="xsf-section">
        <div class="container">
            <div class="xsf-section__head">
                <div>
                    <span class="xsf-section__eyebrow">{{ 'Browse By' }}</span>
                    <h2 class="xsf-section__title sec-heading">{{ 'Explore Popular Categories' }}</h2>
                </div>
                <a href="{{ route('shop.index') }}" class="xsf-section-link">
                    <span>{{ 'View All Categories' }}</span>
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="category-slider xsf-category-slider">
                @foreach ($categories as $category)
                    <div>
                        <x-store.category-card :category="$category" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Trending Products (Slider) --}}
    <section class="xsf-section xsf-section--bg-soft">
        <div class="container position-relative">
            <div class="xsf-section__head">
                <div>
                    <span class="xsf-section__eyebrow">{{ 'Top Picks' }}</span>
                    <h2 class="xsf-section__title sec-heading">{{ 'Trending Products' }}</h2>
                </div>
                <div class="custom-arrows xsf-slider-arrows">
                    <button class="prev" aria-label="{{ 'Previous' }}"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="next" aria-label="{{ 'Next' }}"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
            <div class="product-slider xsf-product-slider">
                @foreach ($products as $product)
                    <div>
                        <x-store.product-card :product="$product" :currency="$currency" :wishlist-ids="$wishlistIds" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Featured Products (Grid) --}}
    <section class="xsf-section">
        <div class="container">
            <div class="xsf-section__head">
                <div>
                    <span class="xsf-section__eyebrow">{{ 'Curated' }}</span>
                    <h2 class="xsf-section__title sec-heading">{{ 'Featured Products' }}</h2>
                </div>
                <a href="{{ route('shop.index') }}" class="xsf-section-link">
                    <span>{{ 'See All Products' }}</span>
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
            <div class="row g-4">
                @foreach ($products as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <x-store.product-card :product="$product" :currency="$currency" :wishlist-ids="$wishlistIds" />
                    </div>
                @endforeach
            </div>
            <div class="text-center mt-5">
                <a href="{{ route('shop.index') }}" class="btn btn-outline-primary btn-pill px-5 py-3">
                    <span>{{ 'Explore All Products' }}</span>
                    <i class="fa-solid fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    {{-- Why Choose Us (Features) --}}
    <section class="xsf-section xsf-features">
        <div class="container">
            <div class="xsf-section__head text-center d-block">
                <span class="xsf-section__eyebrow">{{ 'Our Commitment' }}</span>
                <h2 class="xsf-section__title sec-heading d-block">{{ 'Why Choose Us' }}</h2>
            </div>
            <div class="row g-4 mt-2">
                <div class="col-6 col-lg-3">
                    <x-store.feature-card
                        icon="fa-solid fa-truck-fast"
                        title="Fast & Free Delivery"
                        description="Free shipping on orders over $50 with real-time tracking."
                    />
                </div>
                <div class="col-6 col-lg-3">
                    <x-store.feature-card
                        icon="fa-solid fa-headset"
                        title="24/7 Expert Support"
                        description="Our friendly support team is always here to help you."
                    />
                </div>
                <div class="col-6 col-lg-3">
                    <x-store.feature-card
                        icon="fa-solid fa-shield-halved"
                        title="Secure Payments"
                        description="Encrypted and safe checkout across cards & PayPal."
                    />
                </div>
                <div class="col-6 col-lg-3">
                    <x-store.feature-card
                        icon="fa-solid fa-award"
                        title="Quality Guarantee"
                        description="Over a decade of providing verified, authentic products."
                    />
                </div>
            </div>
        </div>
    </section>
@endsection
