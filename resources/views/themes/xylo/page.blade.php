@extends('themes.xylo.layouts.master')

@php
    $slug  = $page->slug ?? '';
    $title = $page->title ?? 'Page';

    // Map slugs to hero icon + eyebrow label + accent theme
    $pageConfig = [
        'about-us'     => ['icon' => 'fa-solid fa-building-user',    'eyebrow' => 'Who We Are',      'theme' => 'indigo'],
        'blog'         => ['icon' => 'fa-solid fa-newspaper',         'eyebrow' => 'Latest Updates',  'theme' => 'violet'],
        'contact-us'   => ['icon' => 'fa-solid fa-envelope-open-text','eyebrow' => 'Get In Touch',    'theme' => 'emerald'],
        'our-services' => ['icon' => 'fa-solid fa-layer-group',       'eyebrow' => 'What We Offer',   'theme' => 'amber'],
        'privacy-policy'   => ['icon' => 'fa-solid fa-shield-halved', 'eyebrow' => 'Privacy',         'theme' => 'indigo'],
        'terms-of-service' => ['icon' => 'fa-solid fa-file-contract', 'eyebrow' => 'Legal',           'theme' => 'indigo'],
    ];

    $cfg     = $pageConfig[$slug] ?? ['icon' => 'fa-solid fa-file-lines', 'eyebrow' => 'Page', 'theme' => 'indigo'];
    $icon    = $cfg['icon'];
    $eyebrow = $cfg['eyebrow'];

    $isContact  = $slug === 'contact-us';
    $isServices = $slug === 'our-services';
    $isBlog     = $slug === 'blog';
    $isAbout    = $slug === 'about-us';
@endphp

@section('content')

{{-- ================================================================
     PAGE HERO
     ================================================================ --}}
<section class="xsp-hero">
    <div class="xsp-hero__deco" aria-hidden="true"></div>
    <div class="container">
        <div class="xsp-hero__inner">

            {{-- Eyebrow badge --}}
            <span class="xsp-hero__eyebrow">
                <i class="{{ $icon }}" aria-hidden="true"></i>
                {{ $eyebrow }}
            </span>

            {{-- Page title --}}
            <h1 class="xsp-hero__title">{{ $title }}</h1>

            {{-- Short description per page type --}}
            @if ($isAbout)
                <p class="xsp-hero__subtitle">
                    Learn about our mission, the people behind TVR, and what drives us to deliver the best shopping experience.
                </p>
            @elseif ($isBlog)
                <p class="xsp-hero__subtitle">
                    Stay up to date with our latest news, product highlights, and ecommerce insights.
                </p>
            @elseif ($isContact)
                <p class="xsp-hero__subtitle">
                    Have a question or feedback? We'd love to hear from you — reach out any time.
                </p>
            @elseif ($isServices)
                <p class="xsp-hero__subtitle">
                    Explore the full range of services we provide to customers and vendors on our platform.
                </p>
            @endif

            {{-- Breadcrumb --}}
            <ol class="xsp-hero__breadcrumb" aria-label="Breadcrumb">
                <li class="xsp-hero__breadcrumb-item">
                    <a href="{{ route('xylo.home') }}"><i class="fa-solid fa-house" aria-hidden="true"></i> Home</a>
                </li>
                <li class="xsp-hero__breadcrumb-item active" aria-current="page">{{ $title }}</li>
            </ol>

        </div>
    </div>
</section>

{{-- ================================================================
     CONTACT PAGE — info tiles + content card
     ================================================================ --}}
@if ($isContact)
<section class="xsf-static-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">

                {{-- Contact info tiles --}}
                <div class="xsp-contact-tiles">
                    @php
                        $email   = null;
                        $phone   = null;
                        $address = null;
                        $content = $page->content ?? '';
                        // Extract email
                        if (preg_match('/[\w.+-]+@[\w-]+\.[a-z]{2,}/i', $content, $m)) $email = $m[0];
                        // Extract phone (digits, spaces, +, -, parentheses)
                        if (preg_match('/(?:Phone|Tel)[^\d]*([+\d][\d\s\-().]{6,20})/i', $content, $m)) $phone = trim($m[1]);
                        // Extract address (after "Address:")
                        if (preg_match('/Address[:\s]+([^\n<]+)/i', $content, $m)) $address = trim(strip_tags($m[1]));
                    @endphp

                    @if ($email)
                    <div class="xsp-contact-tile">
                        <div class="xsp-contact-tile__icon">
                            <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                        </div>
                        <p class="xsp-contact-tile__label">Email</p>
                        <p class="xsp-contact-tile__value">
                            <a href="mailto:{{ $email }}" style="color:inherit;text-decoration:none;">{{ $email }}</a>
                        </p>
                    </div>
                    @endif

                    @if ($phone)
                    <div class="xsp-contact-tile">
                        <div class="xsp-contact-tile__icon" style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);box-shadow:0 6px 20px -4px rgba(16,185,129,.45);">
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                        </div>
                        <p class="xsp-contact-tile__label">Phone</p>
                        <p class="xsp-contact-tile__value">{{ $phone }}</p>
                    </div>
                    @endif

                    @if ($address)
                    <div class="xsp-contact-tile">
                        <div class="xsp-contact-tile__icon" style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);box-shadow:0 6px 20px -4px rgba(245,158,11,.45);">
                            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        </div>
                        <p class="xsp-contact-tile__label">Address</p>
                        <p class="xsp-contact-tile__value">{{ $address }}</p>
                    </div>
                    @endif
                </div>

                {{-- Full content card --}}
                <article class="xsf-static-page__card">
                    @if ($page->image_url)
                        <div class="xsf-static-page__media">
                            <img src="{{ optimized_image_url($page->image_url) }}"
                                 alt="{{ $title }}" class="img-fluid">
                        </div>
                    @endif
                    <div class="xsf-static-page__content">
                        {!! $page->content ?? '' !!}
                    </div>
                </article>

            </div>
        </div>
    </div>
</section>

{{-- ================================================================
     SERVICES PAGE — feature cards + content card
     ================================================================ --}}
@elseif ($isServices)
<section class="xsf-static-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                {{-- Feature cards (static highlights) --}}
                <div class="xsp-services-grid">
                    @php
                        $services = [
                            ['icon' => 'fa-solid fa-truck-fast',        'title' => 'Fast Shipping',       'desc' => 'Delivery to your doorstep with real-time tracking on every order.',              'theme' => 'indigo', 'n' => '01'],
                            ['icon' => 'fa-solid fa-store',              'title' => 'Vendor Support',      'desc' => 'Access a robust multi-vendor panel with detailed analytics and metrics.',        'theme' => 'violet', 'n' => '02'],
                            ['icon' => 'fa-solid fa-shield-check',       'title' => 'Secure Payments',     'desc' => 'Industry-standard encryption keeps every transaction safe and reliable.',        'theme' => 'emerald','n' => '03'],
                            ['icon' => 'fa-solid fa-headset',            'title' => '24 / 7 Support',      'desc' => 'Friendly agents are ready to assist you at any hour, any day of the week.',     'theme' => 'amber',  'n' => '04'],
                        ];
                    @endphp

                    @foreach ($services as $svc)
                    <div class="xsp-service-card xsp-service-card--{{ $svc['theme'] }}">
                        <span class="xsp-service-card__number" aria-hidden="true">{{ $svc['n'] }}</span>
                        <div class="xsp-service-card__icon">
                            <i class="{{ $svc['icon'] }}" aria-hidden="true"></i>
                        </div>
                        <h3 class="xsp-service-card__title">{{ $svc['title'] }}</h3>
                        <p class="xsp-service-card__desc">{{ $svc['desc'] }}</p>
                    </div>
                    @endforeach
                </div>

                {{-- Full CMS content (if any extra content) --}}
                @if (!empty(strip_tags($page->content ?? '')))
                <article class="xsf-static-page__card" style="margin-top:2.5rem;">
                    @if ($page->image_url)
                        <div class="xsf-static-page__media">
                            <img src="{{ optimized_image_url($page->image_url) }}"
                                 alt="{{ $title }}" class="img-fluid">
                        </div>
                    @endif
                    <div class="xsf-static-page__content">
                        {!! $page->content ?? '' !!}
                    </div>
                </article>
                @endif

                {{-- CTA strip --}}
                <div class="xsp-cta-strip">
                    <div class="xsp-cta-strip__text">
                        <p class="xsp-cta-strip__title">Ready to start shopping?</p>
                        <p class="xsp-cta-strip__subtitle">Thousands of products from verified vendors, delivered fast.</p>
                    </div>
                    <div class="xsp-cta-strip__actions">
                        <a href="{{ route('shop.index') }}" class="xsp-cta-strip__btn xsp-cta-strip__btn--primary">
                            <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> Shop Now
                        </a>
                        <a href="{{ route('store.page', 'contact-us') }}" class="xsp-cta-strip__btn xsp-cta-strip__btn--ghost">
                            Contact Us
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

{{-- ================================================================
     BLOG PAGE — empty / coming-soon state (posts live in CMS content)
     ================================================================ --}}
@elseif ($isBlog)
<section class="xsf-static-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">

                @if (!empty(strip_tags($page->content ?? '')))
                    {{-- CMS content exists — render it in the card --}}
                    <article class="xsf-static-page__card">
                        @if ($page->image_url)
                            <div class="xsf-static-page__media">
                                <img src="{{ optimized_image_url($page->image_url) }}"
                                     alt="{{ $title }}" class="img-fluid">
                            </div>
                        @endif
                        <div class="xsf-static-page__content">
                            {!! $page->content ?? '' !!}
                        </div>
                    </article>
                @else
                    {{-- No posts yet — friendly empty state --}}
                    <div class="xsp-empty-state">
                        <div class="xsp-empty-state__icon" aria-hidden="true">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </div>
                        <h2 class="xsp-empty-state__title">Articles coming soon</h2>
                        <p class="xsp-empty-state__body">
                            We're working on fresh content — styling tips, product highlights, and ecommerce best practices. Check back soon!
                        </p>
                        <a href="{{ route('xylo.home') }}" class="btn btn-primary rounded-pill px-5">
                            Back to Home
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>
</section>

{{-- ================================================================
     ABOUT PAGE — stats strip + content card
     ================================================================ --}}
@elseif ($isAbout)
<section class="xsf-static-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">

                {{-- Stats strip --}}
                <div class="xsp-stat-strip">
                    <div class="xsp-stat">
                        <p class="xsp-stat__value">10K+</p>
                        <p class="xsp-stat__label">Happy Customers</p>
                    </div>
                    <div class="xsp-stat">
                        <p class="xsp-stat__value">500+</p>
                        <p class="xsp-stat__label">Verified Vendors</p>
                    </div>
                    <div class="xsp-stat">
                        <p class="xsp-stat__value">50K+</p>
                        <p class="xsp-stat__label">Products Listed</p>
                    </div>
                    <div class="xsp-stat">
                        <p class="xsp-stat__value">99%</p>
                        <p class="xsp-stat__label">Satisfaction Rate</p>
                    </div>
                </div>

                {{-- CMS content card --}}
                <article class="xsf-static-page__card">
                    @if ($page->image_url)
                        <div class="xsf-static-page__media">
                            <img src="{{ optimized_image_url($page->image_url) }}"
                                 alt="{{ $title }}" class="img-fluid">
                        </div>
                    @endif
                    <div class="xsf-static-page__content">
                        {!! $page->content ?? '' !!}
                    </div>
                </article>

                {{-- CTA strip --}}
                <div class="xsp-cta-strip">
                    <div class="xsp-cta-strip__text">
                        <p class="xsp-cta-strip__title">Join our growing community</p>
                        <p class="xsp-cta-strip__subtitle">Shop premium products or become a vendor on TVR today.</p>
                    </div>
                    <div class="xsp-cta-strip__actions">
                        <a href="{{ route('shop.index') }}" class="xsp-cta-strip__btn xsp-cta-strip__btn--primary">
                            <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> Shop Now
                        </a>
                        <a href="{{ route('store.page', 'contact-us') }}" class="xsp-cta-strip__btn xsp-cta-strip__btn--ghost">
                            Contact Us
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

{{-- ================================================================
     ALL OTHER PAGES — generic card layout
     ================================================================ --}}
@else
<section class="xsf-static-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <article class="xsf-static-page__card">
                    @if ($page->image_url)
                        <div class="xsf-static-page__media">
                            <img src="{{ optimized_image_url($page->image_url) }}"
                                 alt="{{ $title }}" class="img-fluid">
                        </div>
                    @endif
                    <div class="xsf-static-page__content">
                        {!! $page->content ?? '' !!}
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>
@endif

@endsection
