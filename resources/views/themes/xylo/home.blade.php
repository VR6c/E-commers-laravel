@extends('themes.xylo.layouts.master')

@section('preload')
    @if ($banners->isNotEmpty())
        <link rel="preload" as="image" href="{{ optimized_image_url($banners->first()->image_url) }}" fetchpriority="high">
    @endif
@endsection

@section('content')
    @php $currency = activeCurrency(); @endphp

    {{-- Hero / Banner Slider: copy left, image right; stacks image-first on phones --}}
    <section class="xsf-hero" aria-label="{{ 'Featured collections' }}">
        <div class="container">
            <div class="xsf-hero__card">
                <div class="banner-slider xsf-hero__slider">
                    @foreach ($banners as $index => $banner)
                        <div class="xsf-hero__slide-wrapper">
                            <div class="row align-items-center xsf-hero__slide">
                                <div class="col-lg-6 xsf-hero__content">
                                    <span class="xsf-hero__eyebrow">{{ 'New Collection' }}</span>
                                    {{-- one h1 per page: only the first slide is the page title --}}
                                    <{{ $index === 0 ? 'h1' : 'h2' }} class="xsf-hero__title">{{ $banner->title }}</{{ $index === 0 ? 'h1' : 'h2' }}>
                                    <p class="xsf-hero__text">{{ $banner->description ?: 'Discover our latest collection of premium products, designed for your lifestyle.' }}</p>
                                    <div class="xsf-hero__cta-row">
                                        <a href="{{ route('shop.index') }}" class="btn btn-primary btn-lg">
                                            {{ 'Shop Now' }}<i class="fa-solid fa-arrow-right ms-2" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-lg-6 xsf-hero__visual">
                                    <div class="xsf-hero__media">
                                        <img src="{{ optimized_image_url($banner->image_url ?? 'default.jpg') }}"
                                            onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1526738549149-8e07eca6c147?w=800&auto=format&fit=crop&q=80';"
                                            width="600"
                                            height="420"
                                            @if ($index === 0) fetchpriority="high" loading="eager" @else loading="lazy" decoding="async" @endif
                                            alt="{{ $banner->title }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Service strip: replaces the old trust badges + "Why choose us" block --}}
    <section class="xsf-strip" aria-label="{{ 'Our promises' }}">
        <div class="container">
            <ul class="xsf-strip__list">
                <li><i class="fa-solid fa-truck-fast" aria-hidden="true"></i><span><strong>Free delivery</strong> over $50</span></li>
                <li><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><span><strong>Secure</strong> checkout</span></li>
                <li><i class="fa-solid fa-rotate-left" aria-hidden="true"></i><span><strong>Easy</strong> returns</span></li>
                <li><i class="fa-solid fa-headset" aria-hidden="true"></i><span><strong>24/7</strong> support</span></li>
            </ul>
        </div>
    </section>

    {{-- Categories: compact, scannable rail --}}
    @if ($categories->isNotEmpty())
        <section class="xsf-section xsf-section--tight">
            <div class="container">
                <div class="xsf-section__head">
                    <h2 class="xsf-section__title sec-heading">{{ 'Shop by category' }}</h2>
                    <a href="{{ route('shop.index') }}" class="xsf-section-link">
                        <span>{{ 'All products' }}</span>
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
                <ul class="xsf-cat-rail">
                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ route('category.show', $category->slug ?? '#') }}" class="xsf-cat-chip">
                                <img src="{{ optimized_image_url($category->image_url ?? 'default.jpg') }}"
                                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=200&auto=format&fit=crop&q=80';"
                                     loading="lazy" decoding="async" width="64" height="64" alt="">
                                <span>{{ $category->name }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- New arrivals --}}
    <section class="xsf-section">
        <div class="container">
            <div class="xsf-section__head">
                <h2 class="xsf-section__title sec-heading">{{ 'New arrivals' }}</h2>
                <a href="{{ route('shop.index') }}" class="xsf-section-link">
                    <span>{{ 'See all' }}</span>
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
            @if ($products->isEmpty())
                <div class="xsf-empty" role="status">
                    <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                    <h3>{{ 'No products yet' }}</h3>
                    <p>{{ 'New products will appear here soon.' }}</p>
                </div>
            @else
                <div class="xsf-product-grid">
                    @foreach ($products->take(8) as $product)
                        <x-store.product-card :product="$product" :currency="$currency" :wishlist-ids="$wishlistIds" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
