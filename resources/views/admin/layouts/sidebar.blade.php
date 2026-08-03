{{-- =====================================================================
     AdminLTE-style Sidebar
     Structure: .main-sidebar > .sidebar > nav.mt-2 > ul.nav.nav-pills.nav-sidebar
     Submenus:  ul.nav.nav-treeview  (Bootstrap collapse toggle)
    ===================================================================== --}}
<aside class="main-sidebar" id="sidebar" aria-label="Main navigation">

    {{-- ── Brand / Logo ──────────────────────────────────────────── --}}
    <a href="{{ route('admin.dashboard') }}" class="brand-link" id="sidebarBrand">
        <div class="brand-image-wrap">
            @php $logoPath = \App\Models\SiteSetting::first()?->logo ?? null; @endphp
            @if($logoPath)
                <img src="{{ \Illuminate\Support\Str::startsWith($logoPath, ['http://', 'https://']) ? $logoPath : asset('storage/' . $logoPath) }}"
                     alt="{{ config('app.name') }}" class="brand-image">
            @else
                <img src="{{ asset('storage/logo_icon/shopping.png') }}"
                     alt="{{ config('app.name') }}" class="brand-image">
            @endif
        </div>
        <span class="brand-text">{{ config('app.name', 'Admin Panel') }}</span>
    </a>

    {{-- ── Sidebar wrapper (scrollable) ───────────────────────────── --}}
    <div class="sidebar" id="sidebarInner">

        {{-- User panel --}}
        <div class="user-panel">
            <div class="user-panel-image">
                <img src="{{ auth()->user()->profile_image
                        ? (\Illuminate\Support\Str::startsWith(auth()->user()->profile_image, ['http://', 'https://'])
                            ? auth()->user()->profile_image
                            : asset('storage/' . auth()->user()->profile_image))
                        : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=6366f1&color=fff&size=40' }}"
                     alt="{{ auth()->user()->name }}">
            </div>
            <div class="user-panel-info">
                <span class="user-panel-name">{{ auth()->user()->name }}</span>
                <span class="user-panel-status"><span class="user-status-dot"></span> Active Session</span>
            </div>
        </div>

        {{-- Search --}}
        <div class="sidebar-search-wrapper" id="sidebarSearch">
            <div class="sidebar-search-inner">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search menu… (⌘K)" autocomplete="off" aria-label="Search sidebar menu">
            </div>
        </div>

        {{-- ── Navigation ────────────────────────────────────────── --}}
        <nav class="mt-2" aria-label="Sidebar navigation">
            <ul class="nav nav-pills nav-sidebar flex-column" id="sidebarMenu"
                data-widget="treeview" role="menu">

                {{-- ─── Dashboard ──────────────────────────────── --}}
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}"
                       class="nav-link {{ Route::currentRouteName() == 'admin.dashboard' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                {{-- ─── CATALOG ─────────────────────────────────── --}}
                <li class="nav-header">CATALOG</li>

                {{-- Products --}}
                @php $productsActive = in_array(Route::currentRouteName(), ['admin.products.create','admin.products.index','admin.products.edit']); @endphp
                <li class="nav-item {{ $productsActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $productsActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-box-open"></i>
                        <p>Products <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.products.create') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.products.create' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add New</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.products.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.products.index' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Products</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Categories --}}
                @php $catsActive = in_array(Route::currentRouteName(), ['admin.categories.create','admin.categories.index']); @endphp
                <li class="nav-item {{ $catsActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $catsActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-layer-group"></i>
                        <p>Categories <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.categories.create') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.categories.create' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add New</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.categories.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.categories.index' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Categories</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Brands --}}
                @php $brandsActive = in_array(Route::currentRouteName(), ['admin.brands.create','admin.brands.index']); @endphp
                <li class="nav-item {{ $brandsActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $brandsActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tags"></i>
                        <p>Brands <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.brands.create') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.brands.create' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add New</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.brands.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.brands.index' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Brands</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Attributes --}}
                @php $attrsActive = in_array(Route::currentRouteName(), ['admin.attributes.create','admin.attributes.index']); @endphp
                <li class="nav-item {{ $attrsActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $attrsActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-sliders"></i>
                        <p>Attributes <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.attributes.create') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.attributes.create' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add New</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.attributes.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.attributes.index' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Attributes</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Coupons --}}
                <li class="nav-item">
                    <a href="{{ route('admin.coupons.index') }}"
                       class="nav-link {{ Route::currentRouteName() == 'admin.coupons.index' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-ticket-alt"></i>
                        <p>Coupons</p>
                    </a>
                </li>

                {{-- ─── USERS ───────────────────────────────────── --}}
                <li class="nav-header">USERS</li>

                {{-- Customers --}}
                @php $custsActive = in_array(Route::currentRouteName(), ['admin.customers.index','admin.customers.create']); @endphp
                <li class="nav-item {{ $custsActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $custsActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Customers <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.customers.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.customers.index' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Customers</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Vendors --}}
                @php $vendsActive = in_array(Route::currentRouteName(), ['admin.vendors.index','admin.vendors.create']); @endphp
                <li class="nav-item {{ $vendsActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $vendsActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-store"></i>
                        <p>Vendors <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.vendors.create') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.vendors.create' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add New</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.vendors.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.vendors.index' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Vendors</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- ─── COMMERCE ────────────────────────────────── --}}
                <li class="nav-header">COMMERCE</li>

                {{-- Orders --}}
                @php $ordersActive = in_array(Route::currentRouteName(), ['admin.orders.index','admin.orders.pending','admin.orders.completed']); @endphp
                <li class="nav-item {{ $ordersActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $ordersActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-shopping-bag"></i>
                        <p>Orders <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.orders.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.orders.index' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Orders</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.orders.pending') }}" class="nav-link {{ Route::currentRouteName() == 'admin.orders.pending' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Pending Orders</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.orders.completed') }}" class="nav-link {{ Route::currentRouteName() == 'admin.orders.completed' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Completed Orders</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Payments --}}
                @php $paymentsActive = in_array(Route::currentRouteName(), ['admin.payments.index','admin.payments.getData']); @endphp
                <li class="nav-item {{ $paymentsActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $paymentsActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-credit-card"></i>
                        <p>Payments <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.payments.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.payments.index' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Payments</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Refunds --}}
                @php $refundsActive = in_array(Route::currentRouteName(), ['admin.refunds.index','admin.refunds.getData']); @endphp
                <li class="nav-item {{ $refundsActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $refundsActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-rotate-left"></i>
                        <p>Refunds <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.refunds.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.refunds.index' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Refunds</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Payment Gateways --}}
                @php $gatewaysActive = in_array(Route::currentRouteName(), ['admin.payment-gateways.index','admin.payment-gateways.getData','admin.payment-gateways.edit']); @endphp
                <li class="nav-item {{ $gatewaysActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $gatewaysActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-wallet"></i>
                        <p>Payment Gateways <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.payment-gateways.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.payment-gateways.index' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Gateways</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Reviews --}}
                @php $reviewsActive = in_array(Route::currentRouteName(), ['admin.reviews.index','admin.reviews.show']); @endphp
                <li class="nav-item {{ $reviewsActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $reviewsActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-star"></i>
                        <p>Reviews <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.reviews.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.reviews.index' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Reviews</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- ─── CONTENT ─────────────────────────────────── --}}
                <li class="nav-header">CONTENT</li>

                {{-- Banners --}}
                @php $bannersActive = in_array(Route::currentRouteName(), ['admin.banners.create','admin.banners.index']); @endphp
                <li class="nav-item {{ $bannersActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $bannersActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-image"></i>
                        <p>Banners <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.banners.create') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.banners.create' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add New</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.banners.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.banners.index' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Banners</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Menus --}}
                @php $menusActive = in_array(Route::currentRouteName(), ['admin.menus.create','admin.menus.index']); @endphp
                <li class="nav-item {{ $menusActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $menusActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-bars-staggered"></i>
                        <p>Menus <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.menus.create') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.menus.create' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add New</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.menus.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.menus.index' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Menus</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Pages --}}
                @php $pagesActive = in_array(Route::currentRouteName(), ['admin.pages.create','admin.pages.index']); @endphp
                <li class="nav-item {{ $pagesActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $pagesActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-lines"></i>
                        <p>Pages <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.pages.create') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.pages.create' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add New</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.pages.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.pages.index' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Pages</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Social Media Links --}}
                @php $socialActive = in_array(Route::currentRouteName(), ['admin.social-media-links.create','admin.social-media-links.index']); @endphp
                <li class="nav-item {{ $socialActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $socialActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-share-nodes"></i>
                        <p>Social Media <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.social-media-links.create') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.social-media-links.create' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add New</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.social-media-links.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.social-media-links.index' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Links</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- ─── SETTINGS ────────────────────────────────── --}}
                <li class="nav-header">SETTINGS</li>

                {{-- Site Settings --}}
                @php $settingsActive = Route::currentRouteName() == 'admin.site-settings.index' || Route::currentRouteName() == 'admin.site-settings.edit'; @endphp
                <li class="nav-item {{ $settingsActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $settingsActive ? 'active' : '' }}">
                        <i class="nav-icon fas fa-gear"></i>
                        <p>Site Settings <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.site-settings.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'admin.site-settings.index' ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Manage Settings</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Profile --}}
                <li class="nav-item">
                    <a href="{{ route('admin.profile.edit') }}"
                       class="nav-link {{ Route::currentRouteName() == 'admin.profile.edit' ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-circle"></i>
                        <p>My Profile</p>
                    </a>
                </li>

            </ul>
        </nav>
        {{-- ── End Navigation ──────────────────────────────────── --}}

    </div>
    {{-- ── End .sidebar ──────────────────────────────────────────── --}}

</aside>
