@extends('themes.xylo.layouts.master')

@php
    $slug  = $pageKey ?? ($page->slug ?? '');
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

@section('title', $title)

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
     CONTACT PAGE — info tiles + form + content card
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
                        <div class="xsp-contact-tile__icon xsp-contact-tile__icon--success">
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                        </div>
                        <p class="xsp-contact-tile__label">Phone</p>
                        <p class="xsp-contact-tile__value">{{ $phone }}</p>
                    </div>
                    @endif

                    @if ($address)
                    <div class="xsp-contact-tile">
                        <div class="xsp-contact-tile__icon xsp-contact-tile__icon--warning">
                            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        </div>
                        <p class="xsp-contact-tile__label">Address</p>
                        <p class="xsp-contact-tile__value">{{ $address }}</p>
                    </div>
                    @endif
                </div>

                {{-- Contact form --}}
                <form method="POST" action="{{ route('contact.submit') }}" class="xsp-contact-form" novalidate>
                    @csrf
                    <h2 class="xsp-contact-form__title">Send us a message</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="cf-name" class="form-label">Your name</label>
                            <input type="text" id="cf-name" name="name" value="{{ old('name') }}" autocomplete="name"
                                   class="form-control @error('name') is-invalid @enderror" required maxlength="100">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="cf-email" class="form-label">Email</label>
                            <input type="email" id="cf-email" name="email" value="{{ old('email') }}" autocomplete="email"
                                   class="form-control @error('email') is-invalid @enderror" required maxlength="150">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="cf-subject" class="form-label">Subject <span class="text-muted">(optional)</span></label>
                            <input type="text" id="cf-subject" name="subject" value="{{ old('subject') }}"
                                   class="form-control @error('subject') is-invalid @enderror" maxlength="150">
                            @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="cf-message" class="form-label">Message</label>
                            <textarea id="cf-message" name="message" rows="5" required minlength="10" maxlength="3000"
                                      class="form-control @error('message') is-invalid @enderror">{{ old('message') }}</textarea>
                            @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        {{-- Honeypot --}}
                        <div class="xsp-contact-form__hp" aria-hidden="true">
                            <label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-pill px-5">
                                <i class="fa-solid fa-paper-plane me-2" aria-hidden="true"></i>Send message
                            </button>
                        </div>
                    </div>
                </form>

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
     SERVICES PAGE — feature cards, steps, details
     ================================================================ --}}
@elseif ($isServices)
<section class="xsf-static-page xsp-services">
    <div class="container">

        <div class="xsp-services__head">
            <span class="xsp-about__kicker">What we offer</span>
            <h2 class="xsp-about__heading">Everything you need to shop and sell with confidence</h2>
            <p class="xsp-services__lead">From the first click to the doorstep, we take care of the details so you don't have to.</p>
        </div>

        {{-- Services --}}
        <div class="xsp-services-grid">
            @php
                $services = [
                    ['icon' => 'fa-solid fa-truck-fast',   'title' => 'Fast Shipping',   'desc' => 'Delivery to your doorstep with real-time tracking on every order.',           'theme' => 'indigo'],
                    ['icon' => 'fa-solid fa-store',        'title' => 'Vendor Support',  'desc' => 'A robust multi-vendor panel with detailed analytics and sales metrics.',      'theme' => 'violet'],
                    ['icon' => 'fa-solid fa-shield-halved','title' => 'Secure Payments', 'desc' => 'Industry-standard encryption keeps every transaction safe and reliable.',     'theme' => 'emerald'],
                    ['icon' => 'fa-solid fa-headset',      'title' => '24 / 7 Support',  'desc' => 'Friendly agents are ready to assist at any hour, any day of the week.',       'theme' => 'amber'],
                ];
            @endphp

            @foreach ($services as $svc)
                <article class="xsp-service-card xsp-service-card--{{ $svc['theme'] }}">
                    <div class="xsp-service-card__icon">
                        <i class="{{ $svc['icon'] }}" aria-hidden="true"></i>
                    </div>
                    <div class="xsp-service-card__body">
                        <h3 class="xsp-service-card__title">{{ $svc['title'] }}</h3>
                        <p class="xsp-service-card__desc">{{ $svc['desc'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- How it works --}}
        <div class="xsp-services__head xsp-services__head--steps">
            <span class="xsp-about__kicker">How it works</span>
            <h2 class="xsp-about__heading">Three simple steps</h2>
        </div>
        <ol class="xsp-steps">
            @foreach ([
                ['fa-solid fa-magnifying-glass', 'Browse',   'Explore products from verified vendors and filter to find the right fit.'],
                ['fa-solid fa-bag-shopping',     'Order',    'Add to your bag and check out securely with the payment method you prefer.'],
                ['fa-solid fa-box-open',         'Receive',  'Track your delivery in real time and get help from our team if you need it.'],
            ] as $i => [$icon, $name, $desc])
                <li class="xsp-step">
                    <span class="xsp-step__num" aria-hidden="true">{{ $i + 1 }}</span>
                    <div class="xsp-step__icon"><i class="{{ $icon }}" aria-hidden="true"></i></div>
                    <h3 class="xsp-step__title">{{ $name }}</h3>
                    <p class="xsp-step__desc">{{ $desc }}</p>
                </li>
            @endforeach
        </ol>

        {{-- CMS content (kept, but tucked away so it doesn't repeat the cards above) --}}
        @if (!empty(strip_tags($page->content ?? '')))
            <details class="xsp-details">
                <summary>
                    <span>More about our services</span>
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </summary>
                <div class="xsf-static-page__content">
                    {!! $page->content ?? '' !!}
                </div>
            </details>
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
</section>

{{-- ================================================================
     BLOG PAGE — empty / coming-soon state (posts live in CMS content)
     ================================================================ --}}
@elseif ($isBlog)
<section class="xsf-static-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">

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

                @if ($posts->isNotEmpty())
                    <div class="xsp-blog-grid">
                        @foreach ($posts as $post)
                            <a href="{{ route('recipes.show', $post->slug) }}" class="xsp-blog-card">
                                <div class="xsp-blog-card__media">
                                    <img src="{{ optimized_image_url($post->image_url ?? 'default.jpg') }}"
                                         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80';"
                                         alt="{{ $post->title }}" loading="lazy" decoding="async" width="400" height="240">
                                </div>
                                <div class="xsp-blog-card__body">
                                    <time class="xsp-blog-card__date" datetime="{{ $post->created_at->toDateString() }}">
                                        {{ $post->created_at->format('M d, Y') }}
                                    </time>
                                    <h3 class="xsp-blog-card__title">{{ $post->title }}</h3>
                                    @if ($post->summary)
                                        <p class="xsp-blog-card__excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($post->summary), 110) }}</p>
                                    @endif
                                    <span class="xsp-blog-card__more">Read more <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                                </div>
                            </a>
                        @endforeach
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
<section class="xsf-static-page xsp-about">
    <div class="container">

        {{-- Story: CMS copy beside a visual --}}
        <div class="row g-5 align-items-center xsp-about__story">
            <div class="col-lg-6">
                <span class="xsp-about__kicker">Our story</span>
                <h2 class="xsp-about__heading">Built for people who love great products</h2>
                <div class="xsf-static-page__content xsp-about__copy">
                    {!! $page->content ?? '' !!}
                </div>
                <div class="xsp-about__actions">
                    <a href="{{ route('shop.index') }}" class="btn btn-primary btn-pill px-4">
                        <i class="fa-solid fa-bag-shopping me-2" aria-hidden="true"></i>Start shopping
                    </a>
                    <a href="{{ route('store.page', 'contact-us') }}" class="btn btn-outline-primary btn-pill px-4">Get in touch</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="xsp-about__visual">
                    @if ($page->image_url)
                        <img src="{{ optimized_image_url($page->image_url) }}" alt="{{ $title }}"
                             loading="lazy" decoding="async" width="640" height="520">
                    @else
                        <div class="xsp-about__visual-art" aria-hidden="true">
                            <i class="fa-solid fa-store"></i>
                        </div>
                    @endif
                    <div class="xsp-about__badge">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                        <div>
                            <strong>Verified vendors</strong>
                            <span>Every seller is reviewed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats --}}
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

        {{-- Values --}}
        <div class="xsp-about__values-head">
            <span class="xsp-about__kicker">What we stand for</span>
            <h2 class="xsp-about__heading">Our values</h2>
        </div>
        <div class="xsp-about__values">
            @foreach ([
                ['fa-solid fa-gem',          'Quality first',    'We curate products from verified vendors so what you receive matches what you saw.'],
                ['fa-solid fa-handshake',    'Trust & honesty',  'Clear prices, secure payments and straightforward returns, with no surprises.'],
                ['fa-solid fa-heart',        'Customer care',    'Real people ready to help before, during and after every order.'],
            ] as [$icon, $name, $desc])
                <div class="xsp-value">
                    <div class="xsp-value__icon"><i class="{{ $icon }}" aria-hidden="true"></i></div>
                    <h3 class="xsp-value__title">{{ $name }}</h3>
                    <p class="xsp-value__desc">{{ $desc }}</p>
                </div>
            @endforeach
        </div>

        {{-- CTA strip --}}
        <div class="xsp-cta-strip">
            <div class="xsp-cta-strip__text">
                <p class="xsp-cta-strip__title">Join our growing community</p>
                <p class="xsp-cta-strip__subtitle">Shop premium products or become a vendor on {{ config('app.name', 'TVR') }} today.</p>
            </div>
            <div class="xsp-cta-strip__actions">
                <a href="{{ route('shop.index') }}" class="xsp-cta-strip__btn xsp-cta-strip__btn--primary">
                    <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> Shop Now
                </a>
                <a href="{{ route('vendor.register') }}" class="xsp-cta-strip__btn xsp-cta-strip__btn--ghost">
                    Become a Vendor
                </a>
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
