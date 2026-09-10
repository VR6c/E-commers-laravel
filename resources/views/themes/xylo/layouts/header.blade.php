@php
    $wishlistCount = 0;
    if (auth('customer')->check()) {
        $wishlistCount = auth('customer')->user()->wishlistProducts()->count();
    }
    $cartCount = session('cart') ? collect(session('cart'))->sum('quantity') : 0;
@endphp

<header class="xsf-header" id="site-header">

    {{-- ── Announcement bar ────────────────────────────────── --}}
    <div class="xsf-topbar">
        <div class="container">
            <div class="xsf-topbar__inner">
                <p class="xsf-topbar__msg mb-0">
                    <i class="fas fa-truck xsf-topbar__icon"></i>
                    {{ 'Free shipping on orders over $50' }}
                </p>

                {{-- Register CTA buttons (guests only) --}}
                @guest('customer')
                <div class="xsf-topbar__register d-none d-md-flex">
                    <a href="{{ route('customer.register') }}" class="xsf-topbar-reg xsf-topbar-reg--customer">
                        <i class="fa-regular fa-user"></i>
                        <span>Register Customer</span>
                    </a>
                    <span class="xsf-topbar-reg__sep"></span>
                    <a href="{{ route('vendor.register') }}" class="xsf-topbar-reg xsf-topbar-reg--vendor">
                        <i class="fas fa-store"></i>
                        <span>Register Vendor</span>
                        <span class="xsf-topbar-reg__badge">New</span>
                    </a>
                </div>
                @endguest
            </div>
        </div>
    </div>

    {{-- ── Primary bar: logo / search / actions ─────────────── --}}
    <div class="xsf-header__main">
        <div class="container">
            <div class="xsf-header__row">

                {{-- Mobile nav toggle --}}
                <button class="xsf-header__burger d-lg-none"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#xsfMobileNav"
                        aria-controls="xsfMobileNav"
                        aria-label="{{ 'Open menu' }}">
                    <i class="fa fa-bars" aria-hidden="true"></i>
                </button>

                {{-- Logo --}}
                <a href="{{ route('xylo.home') }}" class="xsf-header__brand" aria-label="{{ config('app.name') }}">
                    <img src="{{ getSiteLogo() }}"
                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode(config('app.name', 'Store')) }}&background=6366f1&color=fff&size=100';"
                         alt="{{ config('app.name') }} logo" class="xsf-brand__img" width="160" height="40" style="aspect-ratio: 4/1; object-fit: contain;">
                </a>

                {{-- Search (desktop) --}}
                <form class="xsf-search d-none d-md-flex" action="{{ url('/search') }}" method="GET" role="search">
                    <div class="xsf-search__group">
                        <i class="fa fa-search xsf-search__icon" aria-hidden="true"></i>
                        <input type="text"
                               class="xsf-search__input"
                               id="search-input"
                               name="q"
                               autocomplete="off"
                               placeholder="{{ 'Search products...' }}"
                               aria-label="{{ 'Search products...' }}">
                        <button type="submit" class="xsf-search__btn">
                            {{ 'Search' }}
                        </button>
                        <div id="search-suggestions" class="xsf-search__suggestions d-none"></div>
                    </div>
                </form>

                {{-- Action icons --}}
                <div class="xsf-actions">

                    {{-- Wishlist --}}
                    <a href="{{ auth('customer')->check() ? route('customer.wishlist.index') : route('customer.login') }}"
                       class="xsf-action"
                       aria-label="{{ 'Wishlist' }}"
                       title="{{ 'Wishlist' }}">
                        <i class="fa-regular fa-heart" aria-hidden="true"></i>
                        <span id="wishlist-count" class="xsf-action__badge {{ $wishlistCount > 0 ? '' : 'd-none' }}">{{ $wishlistCount }}</span>
                    </a>

                    {{-- Account --}}
                    <div class="dropdown xsf-account">
                        <a href="#"
                           class="xsf-action dropdown-toggle"
                           data-bs-toggle="dropdown"
                           aria-expanded="false"
                           aria-label="{{ 'Account' }}">
                            @auth('customer')
                                @php $customer = Auth::guard('customer')->user(); @endphp
                                <img src="{{ $customer->profile_image ? asset('storage/' . $customer->profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode($customer->name) . '&background=6366f1&color=fff&size=40' }}"
                                     alt="{{ $customer->name }}"
                                     class="xsf-action__avatar">
                            @else
                                <i class="fa-regular fa-user" aria-hidden="true"></i>
                            @endauth
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end xsf-account__menu">
                            @guest('customer')
                                <li>
                                    <div class="xsf-account__guest-header">
                                        <i class="fa-regular fa-user-circle xsf-account__guest-icon"></i>
                                        <span>{{ 'My Account' }}</span>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('customer.login') }}">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>
                                        {{ 'Sign In' }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('customer.register') }}">
                                        <i class="bi bi-person-plus me-2"></i>
                                        {{ 'Sign Up' }}
                                    </a>
                                </li>
                            @else
                                <li>
                                    <div class="xsf-account__user-header">
                                        <img src="{{ $customer->profile_image ? asset('storage/' . $customer->profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode($customer->name) . '&background=6366f1&color=fff&size=64' }}"
                                             alt="{{ $customer->name }}"
                                             class="xsf-account__user-avatar">
                                        <div>
                                            <p class="xsf-account__user-name">{{ $customer->name }}</p>
                                            <p class="xsf-account__user-email">{{ $customer->email }}</p>
                                        </div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('customer.profile.edit') }}">
                                        <i class="bi bi-person-circle me-2"></i>
                                        {{ 'My Profile' }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('customer.wishlist.index') }}">
                                        <i class="bi bi-heart me-2"></i>
                                        {{ 'Wishlist' }}
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item xsf-account__logout"
                                       href="#"
                                       onclick="event.preventDefault(); document.getElementById('customer-logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-2"></i>
                                        {{ 'Logout' }}
                                    </a>
                                    <form id="customer-logout-form" action="{{ route('customer.logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </li>
                            @endguest
                        </ul>
                    </div>

                    {{-- Cart --}}
                    <a href="{{ route('cart.view') }}"
                       class="xsf-action xsf-action--cart"
                       aria-label="{{ 'Cart' }}"
                       title="{{ 'Cart' }}">
                        <i class="fa fa-shopping-bag" aria-hidden="true"></i>
                        <span id="cart-count" class="xsf-action__badge {{ $cartCount > 0 ? '' : 'd-none' }}">{{ $cartCount }}</span>
                    </a>
                </div>

            </div>{{-- /.xsf-header__row --}}

            {{-- Search (mobile) --}}
            <form class="xsf-search xsf-search--mobile d-flex d-md-none" action="{{ url('/search') }}" method="GET" role="search">
                <div class="xsf-search__group">
                    <i class="fa fa-search xsf-search__icon" aria-hidden="true"></i>
                    <input type="text"
                           class="xsf-search__input"
                           name="q"
                           autocomplete="off"
                           placeholder="{{ 'Search products...' }}"
                           aria-label="{{ 'Search products...' }}">
                    <button type="submit" class="xsf-search__btn">
                        {{ 'Search' }}
                    </button>
                </div>
            </form>

        </div>
    </div>

    {{-- ── Primary navigation (desktop) ──────────────────────── --}}
    <nav class="xsf-nav d-none d-lg-block" aria-label="{{ 'Primary navigation' }}">
        <div class="container">
            <ul class="xsf-nav__list">
                @if (!empty($headerMenu) && $headerMenu->menuItems->count())
                    @foreach ($headerMenu->menuItems as $menuItem)
                        <li class="xsf-nav__item">
                            <a class="xsf-nav__link menu-text-color" href="{{ url($menuItem->slug) }}">
                                {{ $menuItem->title ?? 'Menu Item' }}
                            </a>
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>
    </nav>

</header>

{{-- ── Mobile navigation offcanvas ──────────────────────────── --}}
<div class="offcanvas offcanvas-start xsf-mobile-nav" tabindex="-1" id="xsfMobileNav" aria-labelledby="xsfMobileNavLabel">
    <div class="offcanvas-header xsf-mobile-nav__header">
        <img src="{{ getSiteLogo() }}"
             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode(config('app.name', 'Store')) }}&background=6366f1&color=fff&size=100';"
             alt="{{ config('app.name') }}" id="xsfMobileNavLabel" width="140" height="38" style="max-height:40px; aspect-ratio: 4/1; object-fit: contain;">
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <ul class="xsf-mobile-nav__list">
            @if (!empty($headerMenu) && $headerMenu->menuItems->count())
                @foreach ($headerMenu->menuItems as $menuItem)
                    <li>
                        <a class="xsf-mobile-nav__link" href="{{ url($menuItem->slug) }}">
                            {{ $menuItem->title ?? 'Menu Item' }}
                            <i class="fas fa-chevron-right xsf-mobile-nav__arrow"></i>
                        </a>
                    </li>
                @endforeach
            @endif
        </ul>

        {{-- Mobile auth links --}}
        <div class="xsf-mobile-nav__auth">
            @guest('customer')
                <a href="{{ route('customer.login') }}" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </a>
                <a href="{{ route('customer.register') }}" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-person-plus me-2"></i>Create Account
                </a>
                <div class="xsf-mobile-nav__divider"></div>
                <p class="xsf-mobile-nav__label">Join as a seller</p>
                <a href="{{ route('vendor.register') }}" class="xsf-mobile-reg-vendor w-100">
                    <i class="fas fa-store me-2"></i>Register as Vendor
                </a>
            @else
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ auth('customer')->user()->profile_image ? asset('storage/' . auth('customer')->user()->profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode(auth('customer')->user()->name) . '&background=6366f1&color=fff&size=40' }}"
                         alt="{{ auth('customer')->user()->name }}"
                         class="rounded-circle" width="42" height="42" style="object-fit:cover;">
                    <div>
                        <p class="mb-0 fw-semibold" style="font-size:.9rem;">{{ auth('customer')->user()->name }}</p>
                        <p class="mb-0 text-muted" style="font-size:.78rem;">{{ auth('customer')->user()->email }}</p>
                    </div>
                </div>
                <a href="{{ route('customer.profile.edit') }}" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-person-circle me-2"></i>My Profile
                </a>
            @endguest
        </div>
    </div>
</div>

{{-- Scroll-aware header elevation --}}
<script>
(function () {
    const header = document.getElementById('site-header');
    if (!header) return;
    const onScroll = function () {
        header.classList.toggle('is-scrolled', window.scrollY > 20);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
})();
</script>

{{-- Register button click animations --}}
<script>
(function () {
    /* ── Ripple factory ───────────────────────────────────────────── */
    function spawnRipple(btn, e) {
        const existing = btn.querySelector('.xsf-reg-ripple');
        if (existing) existing.remove();

        const rect   = btn.getBoundingClientRect();
        const size   = Math.max(rect.width, rect.height) * 2.2;
        const x      = (e ? e.clientX - rect.left : rect.width  / 2) - size / 2;
        const y      = (e ? e.clientY - rect.top  : rect.height / 2) - size / 2;

        const ripple = document.createElement('span');
        ripple.className = 'xsf-reg-ripple';
        ripple.style.cssText =
            'width:'  + size + 'px;' +
            'height:' + size + 'px;' +
            'left:'   + x    + 'px;' +
            'top:'    + y    + 'px;';
        btn.appendChild(ripple);

        ripple.addEventListener('animationend', () => ripple.remove(), { once: true });
    }

    /* ── Particle burst factory ───────────────────────────────────── */
    function spawnParticles(btn, isVendor) {
        const colors = isVendor
            ? ['#fbbf24','#f59e0b','#fcd34d','#fff','#d97706']
            : ['#a5b4fc','#6366f1','#c7d2fe','#fff','#818cf8'];

        const count = 10;
        for (let i = 0; i < count; i++) {
            const p    = document.createElement('span');
            const angle  = (360 / count) * i + (Math.random() * 20 - 10);
            const dist   = 28 + Math.random() * 22;
            const dx     = Math.cos((angle * Math.PI) / 180) * dist;
            const dy     = Math.sin((angle * Math.PI) / 180) * dist;
            const size   = 3 + Math.random() * 3;
            const color  = colors[Math.floor(Math.random() * colors.length)];
            const delay  = Math.random() * 60;

            p.className = 'xsf-reg-particle';
            p.style.cssText =
                'width:'            + size    + 'px;' +
                'height:'           + size    + 'px;' +
                'background:'       + color   + ';'   +
                '--dx:'             + dx      + 'px;' +
                '--dy:'             + dy      + 'px;' +
                'animation-delay:'  + delay   + 'ms;';
            btn.appendChild(p);
            p.addEventListener('animationend', () => p.remove(), { once: true });
        }
    }

    /* ── Icon swap helper ─────────────────────────────────────────── */
    function swapIcon(btn, newIconClass) {
        const icon = btn.querySelector('i');
        if (!icon) return null;
        const original = icon.className;
        icon.style.transition = 'transform .15s ease, opacity .15s ease';
        icon.style.opacity = '0';
        icon.style.transform = 'scale(0.4) rotate(-20deg)';
        setTimeout(function () {
            icon.className = newIconClass;
            icon.style.opacity = '1';
            icon.style.transform = 'scale(1) rotate(0deg)';
        }, 150);
        return original;
    }

    /* ── Scale-press ──────────────────────────────────────────────── */
    function scalePress(btn) {
        btn.style.transition = 'transform .10s cubic-bezier(.36,.07,.19,.97)';
        btn.style.transform  = 'scale(0.88)';
        setTimeout(function () {
            btn.style.transform = 'scale(1.06)';
            setTimeout(function () {
                btn.style.transform = '';
                btn.style.transition = '';
            }, 160);
        }, 100);
    }

    /* ── Main handler ─────────────────────────────────────────────── */
    document.querySelectorAll('.xsf-topbar-reg, .xsf-mobile-reg-vendor, [href="{{ route('customer.register') }}"].btn-outline-primary').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const isVendor = btn.classList.contains('xsf-topbar-reg--vendor') ||
                             btn.classList.contains('xsf-mobile-reg-vendor');

            /* 1. Ripple */
            spawnRipple(btn, e);

            /* 2. Particle burst */
            spawnParticles(btn, isVendor);

            /* 3. Scale press */
            scalePress(btn);

            /* 4. Icon swap to spinner / arrow */
            const origIcon = swapIcon(btn, 'fas fa-circle-notch fa-spin');

            /* 5. After a short beat, swap icon to arrow-right then navigate */
            const href = btn.getAttribute('href');
            if (href && href !== '#') {
                e.preventDefault();
                setTimeout(function () {
                    swapIcon(btn, 'fas fa-arrow-right');
                    setTimeout(function () {
                        window.location.href = href;
                    }, 280);
                }, 380);
            }
        });
    });
})();
</script>
