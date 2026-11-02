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

{{-- PAGE HEADER: the single h1 --}}
<header class="xsp-hero">
    <div class="container">
        <nav aria-label="Breadcrumb">
            <ol class="xsp-hero__breadcrumb">
                <li class="xsp-hero__breadcrumb-item"><a href="{{ route('xylo.home') }}">Home</a></li>
                <li class="xsp-hero__breadcrumb-item active" aria-current="page">{{ $title }}</li>
            </ol>
        </nav>
        <p class="xsp-hero__eyebrow"><i class="{{ $icon }}" aria-hidden="true"></i> {{ $eyebrow }}</p>
        <h1 class="xsp-hero__title">{{ $title }}</h1>
        @if ($isAbout)
            <p class="xsp-hero__subtitle">Learn about our mission, the people behind TVR, and what drives us to deliver the best shopping experience.</p>
        @elseif ($isBlog)
            <p class="xsp-hero__subtitle">Stay up to date with our latest news, product highlights, and ecommerce insights.</p>
        @elseif ($isContact)
            <p class="xsp-hero__subtitle">Have a question or feedback? We'd love to hear from you. Reach out any time.</p>
        @elseif ($isServices)
            <p class="xsp-hero__subtitle">Explore the full range of services we provide to customers and vendors on our platform.</p>
        @endif
    </div>
</header>

@if ($isContact)
{{-- ================================================================ CONTACT --}}
<div class="xsf-static-page">
    <div class="container">
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

        <div class="xsp-contact">
            <section class="xsp-contact__details" aria-labelledby="contact-details-title">
                <h2 class="xsp-section-title" id="contact-details-title">Contact details</h2>
                @if ($email || $phone || $address)
                    <ul class="xsp-contact-tiles">
                        @if ($email)
                        <li class="xsp-contact-tile">
                            <span class="xsp-contact-tile__icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
                            <span>
                                <span class="xsp-contact-tile__label">Email</span>
                                <a class="xsp-contact-tile__value" href="mailto:{{ $email }}">{{ $email }}</a>
                            </span>
                        </li>
                        @endif
                        @if ($phone)
                        <li class="xsp-contact-tile">
                            <span class="xsp-contact-tile__icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                            <span>
                                <span class="xsp-contact-tile__label">Phone</span>
                                <span class="xsp-contact-tile__value">{{ $phone }}</span>
                            </span>
                        </li>
                        @endif
                        @if ($address)
                        <li class="xsp-contact-tile">
                            <span class="xsp-contact-tile__icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                            <span>
                                <span class="xsp-contact-tile__label">Address</span>
                                <span class="xsp-contact-tile__value">{{ $address }}</span>
                            </span>
                        </li>
                        @endif
                    </ul>
                @else
                    <p class="xsp-muted">Use the form and we'll get back to you by email.</p>
                @endif

                @if ($page->image_url || !empty(strip_tags($page->content ?? '')))
                    <div class="xsf-static-page__content xsp-contact__more">
                        @if ($page->image_url)
                            <img src="{{ optimized_image_url($page->image_url) }}" alt="{{ $title }}" loading="lazy" decoding="async">
                        @endif
                        {!! $page->content ?? '' !!}
                    </div>
                @endif
            </section>

            <section class="xsp-contact__form-wrap" aria-labelledby="contact-form-title">
                <form method="POST" action="{{ route('contact.submit') }}" class="xsp-contact-form" novalidate>
                    @csrf
                    <h2 class="xsp-section-title" id="contact-form-title">Send us a message</h2>
                    <div class="xsp-form-grid">
                        <div class="xsp-field">
                            <label for="cf-name" class="form-label">Your name</label>
                            <input type="text" id="cf-name" name="name" value="{{ old('name') }}" autocomplete="name"
                                   class="form-control @error('name') is-invalid @enderror" required maxlength="100"
                                   @error('name') aria-invalid="true" aria-describedby="cf-name-error" @enderror>
                            @error('name')<p class="xsp-field__error" id="cf-name-error" role="alert">{{ $message }}</p>@enderror
                        </div>
                        <div class="xsp-field">
                            <label for="cf-email" class="form-label">Email</label>
                            <input type="email" id="cf-email" name="email" value="{{ old('email') }}" autocomplete="email"
                                   class="form-control @error('email') is-invalid @enderror" required maxlength="150"
                                   @error('email') aria-invalid="true" aria-describedby="cf-email-error" @enderror>
                            @error('email')<p class="xsp-field__error" id="cf-email-error" role="alert">{{ $message }}</p>@enderror
                        </div>
                        <div class="xsp-field xsp-field--full">
                            <label for="cf-subject" class="form-label">Subject <span class="xsp-muted">(optional)</span></label>
                            <input type="text" id="cf-subject" name="subject" value="{{ old('subject') }}"
                                   class="form-control @error('subject') is-invalid @enderror" maxlength="150"
                                   @error('subject') aria-invalid="true" aria-describedby="cf-subject-error" @enderror>
                            @error('subject')<p class="xsp-field__error" id="cf-subject-error" role="alert">{{ $message }}</p>@enderror
                        </div>
                        <div class="xsp-field xsp-field--full">
                            <label for="cf-message" class="form-label">Message</label>
                            <textarea id="cf-message" name="message" rows="5" required minlength="10" maxlength="3000"
                                      class="form-control @error('message') is-invalid @enderror"
                                      @error('message') aria-invalid="true" aria-describedby="cf-message-error" @enderror>{{ old('message') }}</textarea>
                            @error('message')<p class="xsp-field__error" id="cf-message-error" role="alert">{{ $message }}</p>@enderror
                        </div>
                        {{-- Honeypot --}}
                        <div class="xsp-contact-form__hp" aria-hidden="true">
                            <label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary xsp-contact-form__submit">Send message</button>
                </form>
            </section>
        </div>
    </div>
</div>

@elseif ($isServices)
{{-- ================================================================ SERVICES --}}
<div class="xsf-static-page">
    <div class="container">
        <section class="xsp-section" aria-labelledby="services-title">
            <h2 class="xsp-section-title" id="services-title">Everything you need to shop and sell with confidence</h2>
            <p class="xsp-lead">From the first click to the doorstep, we take care of the details so you don't have to.</p>

            @php
                $services = [
                    ['icon' => 'fa-solid fa-truck-fast',   'title' => 'Fast Shipping',   'desc' => 'Delivery to your doorstep with real-time tracking on every order.',           'theme' => 'indigo'],
                    ['icon' => 'fa-solid fa-store',        'title' => 'Vendor Support',  'desc' => 'A robust multi-vendor panel with detailed analytics and sales metrics.',      'theme' => 'violet'],
                    ['icon' => 'fa-solid fa-shield-halved','title' => 'Secure Payments', 'desc' => 'Industry-standard encryption keeps every transaction safe and reliable.',     'theme' => 'emerald'],
                    ['icon' => 'fa-solid fa-headset',      'title' => '24 / 7 Support',  'desc' => 'Friendly agents are ready to assist at any hour, any day of the week.',       'theme' => 'amber'],
                ];
            @endphp
            <ul class="xsp-grid xsp-services-grid">
                @foreach ($services as $svc)
                    <li class="xsp-card xsp-service-card">
                        <span class="xsp-icon" aria-hidden="true"><i class="{{ $svc['icon'] }}"></i></span>
                        <h3 class="xsp-card__title">{{ $svc['title'] }}</h3>
                        <p class="xsp-card__desc">{{ $svc['desc'] }}</p>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="xsp-section" aria-labelledby="steps-title">
            <h2 class="xsp-section-title" id="steps-title">How it works</h2>
            <ol class="xsp-grid xsp-steps">
                @foreach ([
                    ['fa-solid fa-magnifying-glass', 'Browse',   'Explore products from verified vendors and filter to find the right fit.'],
                    ['fa-solid fa-bag-shopping',     'Order',    'Add to your bag and check out securely with the payment method you prefer.'],
                    ['fa-solid fa-box-open',         'Receive',  'Track your delivery in real time and get help from our team if you need it.'],
                ] as $i => [$icon, $name, $desc])
                    <li class="xsp-card xsp-step">
                        <span class="xsp-icon" aria-hidden="true"><i class="{{ $icon }}"></i></span>
                        <h3 class="xsp-card__title"><span class="xsp-step__num">{{ $i + 1 }}.</span> {{ $name }}</h3>
                        <p class="xsp-card__desc">{{ $desc }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        @if (!empty(strip_tags($page->content ?? '')))
            <details class="xsp-details">
                <summary>More about our services</summary>
                <div class="xsf-static-page__content">
                    {!! $page->content ?? '' !!}
                </div>
            </details>
        @endif

        <section class="xsp-cta-strip" aria-labelledby="cta-title">
            <div>
                <h2 class="xsp-cta-strip__title" id="cta-title">Ready to start shopping?</h2>
                <p class="xsp-cta-strip__subtitle">Thousands of products from verified vendors, delivered fast.</p>
            </div>
            <div class="xsp-cta-strip__actions">
                <a href="{{ route('shop.index') }}" class="btn btn-primary">Shop Now</a>
                <a href="{{ route('store.page', 'contact-us') }}" class="btn btn-outline-secondary">Contact Us</a>
            </div>
        </section>
    </div>
</div>

@elseif ($isBlog)
{{-- ================================================================ BLOG --}}
<div class="xsf-static-page">
    <div class="container">
        @php $hasContent = !empty(strip_tags($page->content ?? '')); @endphp

        @if ($hasContent)
            <article class="xsp-prose">
                @if ($page->image_url)
                    <img class="xsp-prose__media" src="{{ optimized_image_url($page->image_url) }}" alt="{{ $title }}">
                @endif
                <div class="xsf-static-page__content">
                    {!! $page->content ?? '' !!}
                </div>
            </article>
        @endif

        @if ($posts->isNotEmpty())
            <section class="xsp-section" aria-labelledby="posts-title">
                <h2 class="xsp-section-title" id="posts-title">Latest articles</h2>
                <div class="xsp-grid xsp-blog-grid">
                    @foreach ($posts as $post)
                        <a href="{{ route('recipes.show', $post->slug) }}" class="xsp-blog-card">
                            <div class="xsp-blog-card__media">
                                <img src="{{ optimized_image_url($post->image_url ?? 'default.jpg') }}"
                                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80';"
                                     alt="" loading="lazy" decoding="async" width="400" height="240">
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
            </section>
        @elseif (!$hasContent)
            <section class="xsp-empty-state" aria-labelledby="empty-title">
                <h2 class="xsp-empty-state__title" id="empty-title">Articles coming soon</h2>
                <p class="xsp-empty-state__body">We're working on fresh content: styling tips, product highlights, and ecommerce best practices. Check back soon.</p>
                <a href="{{ route('xylo.home') }}" class="btn btn-primary">Back to Home</a>
            </section>
        @endif
    </div>
</div>

@elseif ($isAbout)
{{-- ================================================================ ABOUT --}}
<div class="xsf-static-page">
    <div class="container">
        <section class="xsp-about" aria-labelledby="story-title">
            <div class="xsp-about__copy">
                <h2 class="xsp-section-title" id="story-title">Built for people who love great products</h2>
                <div class="xsf-static-page__content">
                    {!! $page->content ?? '' !!}
                </div>
                <div class="xsp-about__actions">
                    <a href="{{ route('shop.index') }}" class="btn btn-primary">Start shopping</a>
                    <a href="{{ route('store.page', 'contact-us') }}" class="btn btn-outline-secondary">Get in touch</a>
                </div>
            </div>
            @if ($page->image_url)
                <div class="xsp-about__visual">
                    <img src="{{ optimized_image_url($page->image_url) }}" alt="{{ $title }}"
                         loading="lazy" decoding="async" width="640" height="520">
                </div>
            @endif
        </section>

        <dl class="xsp-stat-strip">
            <div class="xsp-stat"><dt class="xsp-stat__label">Happy Customers</dt><dd class="xsp-stat__value">10K+</dd></div>
            <div class="xsp-stat"><dt class="xsp-stat__label">Verified Vendors</dt><dd class="xsp-stat__value">500+</dd></div>
            <div class="xsp-stat"><dt class="xsp-stat__label">Products Listed</dt><dd class="xsp-stat__value">50K+</dd></div>
            <div class="xsp-stat"><dt class="xsp-stat__label">Satisfaction Rate</dt><dd class="xsp-stat__value">99%</dd></div>
        </dl>

        <section class="xsp-section" aria-labelledby="values-title">
            <h2 class="xsp-section-title" id="values-title">Our values</h2>
            <ul class="xsp-grid xsp-about__values">
                @foreach ([
                    ['fa-solid fa-gem',          'Quality first',    'We curate products from verified vendors so what you receive matches what you saw.'],
                    ['fa-solid fa-handshake',    'Trust & honesty',  'Clear prices, secure payments and straightforward returns, with no surprises.'],
                    ['fa-solid fa-heart',        'Customer care',    'Real people ready to help before, during and after every order.'],
                ] as [$icon, $name, $desc])
                    <li class="xsp-card xsp-value">
                        <span class="xsp-icon" aria-hidden="true"><i class="{{ $icon }}"></i></span>
                        <h3 class="xsp-card__title">{{ $name }}</h3>
                        <p class="xsp-card__desc">{{ $desc }}</p>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="xsp-cta-strip" aria-labelledby="cta-title">
            <div>
                <h2 class="xsp-cta-strip__title" id="cta-title">Join our growing community</h2>
                <p class="xsp-cta-strip__subtitle">Shop premium products or become a vendor on {{ config('app.name', 'TVR') }} today.</p>
            </div>
            <div class="xsp-cta-strip__actions">
                <a href="{{ route('shop.index') }}" class="btn btn-primary">Shop Now</a>
                <a href="{{ route('vendor.register') }}" class="btn btn-outline-secondary">Become a Vendor</a>
            </div>
        </section>
    </div>
</div>

@else
{{-- ================================================================ GENERIC / LEGAL / FAQ --}}
<div class="xsf-static-page">
    <div class="container">
        <article class="xsp-prose">
            @if ($page->image_url)
                <img class="xsp-prose__media" src="{{ optimized_image_url($page->image_url) }}" alt="{{ $title }}">
            @endif
            <div class="xsf-static-page__content">
                {!! $page->content ?? '' !!}
            </div>
        </article>
    </div>
</div>
@endif

@endsection
