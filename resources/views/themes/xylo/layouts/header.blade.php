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
                    <i class="fas fa-truck-fast xsf-topbar__icon"></i>
                    <span>{{ 'Free shipping on orders over $50' }}</span>
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
                    <div class="xsf-brand__wrapper">
                        <img src="{{ getSiteLogo() }}"
                             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode(config('app.name', 'Store')) }}&background=6366f1&color=fff&size=100';"
                             alt="{{ config('app.name') }} logo" class="xsf-brand__img" width="38" height="38">
                        <span class="xsf-brand__name">{{ config('app.name', 'Store') }}</span>
                    </div>
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
                               placeholder="{{ 'Search premium products, brands...' }}"
                               aria-label="{{ 'Search products' }}">
                        <div class="xsf-search__addon">
                            <kbd class="xsf-search__kbd" title="Press ⌘K to search">⌘K</kbd>
                            <button type="submit" class="xsf-search__btn" aria-label="{{ 'Search' }}">
                                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            </button>
                        </div>
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

                    {{-- Account dropdown --}}
                    <div class="dropdown xsf-account">
                        <a href="#"
                           class="xsf-action dropdown-toggle"
                           data-bs-toggle="dropdown"
                           aria-expanded="false"
                           aria-label="{{ 'Account' }}">
                            @auth('customer')
                                @php $customer = Auth::guard('customer')->user(); @endphp
                                <img src="{{ $customer->avatar_url }}"
                                     alt="{{ $customer->name }}"
                                     class="xsf-action__avatar">
                            @else
                                <i class="fa-regular fa-user" aria-hidden="true"></i>
                            @endauth
                        </a>
                        <div class="dropdown-menu dropdown-menu-end xsf-account__menu">
                            @guest('customer')
                                <div class="xsf-account__guest-card">
                                    <div class="xsf-account__guest-head">
                                        <div class="xsf-account__guest-avatar">
                                            <i class="fa-regular fa-user"></i>
                                        </div>
                                        <div class="xsf-account__guest-text">
                                            <p class="xsf-account__guest-title">{{ 'My Account' }}</p>
                                            <p class="xsf-account__guest-sub">{{ 'Sign in for orders & wishlist' }}</p>
                                        </div>
                                    </div>
                                    <div class="xsf-account__guest-actions">
                                        <a class="btn btn-primary btn-sm w-100 mb-2" href="{{ route('customer.login') }}">
                                            <i class="bi bi-box-arrow-in-right me-1"></i>
                                            {{ 'Sign In' }}
                                        </a>
                                        <a class="btn btn-outline-secondary btn-sm w-100" href="{{ route('customer.register') }}">
                                            <i class="bi bi-person-plus me-1"></i>
                                            {{ 'Create Account' }}
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="xsf-account__user-header">
                                    <img src="{{ $customer->avatar_url }}"
                                         alt="{{ $customer->name }}"
                                         class="xsf-account__user-avatar">
                                    <div class="xsf-account__user-meta">
                                        <p class="xsf-account__user-name">{{ $customer->name }}</p>
                                        <p class="xsf-account__user-email">{{ $customer->email }}</p>
                                    </div>
                                </div>
                                <hr class="dropdown-divider">
                                <a class="dropdown-item" href="{{ route('customer.profile.edit') }}">
                                    <i class="fa-solid fa-user me-2 text-muted"></i>
                                    {{ 'My Profile' }}
                                </a>
                                <a class="dropdown-item" href="{{ route('customer.orders.index') }}">
                                    <i class="fa-solid fa-box-archive me-2 text-muted"></i>
                                    {{ 'My Orders' }}
                                </a>
                                <a class="dropdown-item" href="{{ route('customer.recipes.index') }}">
                                    <i class="fa-solid fa-utensils me-2 text-muted"></i>
                                    {{ 'My Recipes' }}
                                </a>
                                <a class="dropdown-item" href="{{ route('customer.wishlist.index') }}">
                                    <i class="fa-regular fa-heart me-2 text-muted"></i>
                                    {{ 'Wishlist' }}
                                </a>
                                <hr class="dropdown-divider">
                                <a class="dropdown-item xsf-account__logout text-danger"
                                   href="#"
                                   onclick="event.preventDefault(); document.getElementById('customer-logout-form').submit();">
                                    <i class="fa-solid fa-arrow-right-from-bracket me-2"></i>
                                    {{ 'Logout' }}
                                </a>
                                <form id="customer-logout-form" action="{{ route('customer.logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            @endguest
                        </div>
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
                           placeholder="{{ 'Search premium products...' }}"
                           aria-label="{{ 'Search products' }}">
                    <button type="submit" class="xsf-search__btn" aria-label="{{ 'Search' }}">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
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
                @else
                    <li class="xsf-nav__item"><a class="xsf-nav__link" href="{{ url('/') }}">Home</a></li>
                    <li class="xsf-nav__item"><a class="xsf-nav__link" href="{{ route('shop.index') }}">Shop All</a></li>
                    <li class="xsf-nav__item"><a class="xsf-nav__link" href="{{ route('recipes.index') }}">Recipes</a></li>
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
            @else
                <li><a class="xsf-mobile-nav__link" href="{{ url('/') }}">Home</a></li>
                <li><a class="xsf-mobile-nav__link" href="{{ route('shop.index') }}">Shop All</a></li>
                <li><a class="xsf-mobile-nav__link" href="{{ route('recipes.index') }}">Recipes</a></li>
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
                    <img src="{{ auth('customer')->user()->avatar_url }}"
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
