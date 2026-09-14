{{-- =====================================================================
     Vendor Panel — AdminLTE-style Modern Sidebar
     100% consistent with Admin panel navigation and visual tokens
    ===================================================================== --}}
@php
    $vendor   = Auth::guard('vendor')->user();
    $logoPath = \App\Models\SiteSetting::first()?->logo ?? null;
@endphp

<aside class="main-sidebar" id="sidebar" aria-label="Vendor navigation">

    {{-- ── Brand / Logo ──────────────────────────────────────────── --}}
    <a href="{{ route('vendor.dashboard') }}" class="brand-link" id="sidebarBrand">
        <div class="brand-image-wrap">
            <img src="{{ getSiteLogo() }}"
                 alt="{{ config('app.name') }}" class="brand-image">
        </div>
        <span class="brand-text">{{ config('app.name', 'Vendor Portal') }}</span>
    </a>

    {{-- ── Scrollable wrapper ────────────────────────────────────── --}}
    <div class="sidebar" id="sidebarInner">

        {{-- User panel --}}
        <div class="user-panel">
            <div class="user-panel-image">
                <img src="{{ $vendor && $vendor->profile_image
                        ? (\Illuminate\Support\Str::startsWith($vendor->profile_image, ['http://', 'https://'])
                            ? $vendor->profile_image
                            : asset('storage/' . $vendor->profile_image))
                        : 'https://ui-avatars.com/api/?name=' . urlencode($vendor ? $vendor->name : 'V') . '&background=6366f1&color=fff&size=40' }}"
                     alt="{{ $vendor ? $vendor->name : 'Vendor' }}">
            </div>
            <div class="user-panel-info">
                <span class="user-panel-name">{{ $vendor ? $vendor->name : 'Vendor' }}</span>
                <span class="user-panel-status"><span class="user-status-dot"></span> Active Vendor</span>
            </div>
        </div>

        {{-- Search --}}
        <div class="sidebar-search-wrapper" id="sidebarSearch">
            <div class="sidebar-search-inner">
                <i class="bi bi-search"></i>
                <input type="text" id="searchInput" placeholder="Search menu… (⌘K)" autocomplete="off" aria-label="Search sidebar menu">
            </div>
        </div>

        {{-- ── Navigation ────────────────────────────────────────── --}}
        <nav class="mt-2" aria-label="Vendor sidebar navigation">
            <ul class="nav nav-pills nav-sidebar flex-column" id="sidebarMenu"
                data-widget="treeview" role="menu">

                {{-- Dashboard --}}
                <li class="nav-item">
                    <a href="{{ route('vendor.dashboard') }}"
                       class="nav-link {{ Route::currentRouteName() == 'vendor.dashboard' ? 'active' : '' }}">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                {{-- ─── CATALOG ─────────────────────────────────── --}}
                <li class="nav-header">CATALOG</li>

                {{-- Products --}}
                @php $productsActive = in_array(Route::currentRouteName(), ['vendor.products.create','vendor.products.index','vendor.products.edit']); @endphp
                <li class="nav-item {{ $productsActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $productsActive ? 'active' : '' }}">
                        <i class="nav-icon bi bi-box-seam"></i>
                        <p>Products <i class="right bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('vendor.products.create') }}"
                               class="nav-link {{ Route::currentRouteName() == 'vendor.products.create' ? 'active' : '' }}">
                                <i class="bi bi-circle nav-icon"></i>
                                <p>Add New</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('vendor.products.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'vendor.products.index' ? 'active' : '' }}">
                                <i class="bi bi-circle nav-icon"></i>
                                <p>All Products</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- ─── COMMERCE ────────────────────────────────── --}}
                <li class="nav-header">COMMERCE</li>

                {{-- Orders --}}
                @php $ordersActive = Route::is('vendor.orders.*'); @endphp
                <li class="nav-item {{ $ordersActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $ordersActive ? 'active' : '' }}">
                        <i class="nav-icon bi bi-bag-check"></i>
                        <p>Orders <i class="right bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('vendor.orders.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'vendor.orders.index' ? 'active' : '' }}">
                                <i class="bi bi-circle nav-icon"></i>
                                <p>All Orders</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Reviews --}}
                @php $reviewsActive = Route::is('vendor.reviews.*'); @endphp
                <li class="nav-item {{ $reviewsActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $reviewsActive ? 'active' : '' }}">
                        <i class="nav-icon bi bi-star"></i>
                        <p>Reviews <i class="right bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('vendor.reviews.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'vendor.reviews.index' ? 'active' : '' }}">
                                <i class="bi bi-circle nav-icon"></i>
                                <p>All Reviews</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- ─── CONTENT ─────────────────────────────────── --}}
                <li class="nav-header">CONTENT</li>

                {{-- Social Media Links --}}
                @php $socialActive = in_array(Route::currentRouteName(), ['vendor.social-media-links.create','vendor.social-media-links.index','vendor.social-media-links.edit']); @endphp
                <li class="nav-item {{ $socialActive ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ $socialActive ? 'active' : '' }}">
                        <i class="nav-icon bi bi-share"></i>
                        <p>Social Media <i class="right bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('vendor.social-media-links.create') }}"
                               class="nav-link {{ Route::currentRouteName() == 'vendor.social-media-links.create' ? 'active' : '' }}">
                                <i class="bi bi-circle nav-icon"></i>
                                <p>Add New</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('vendor.social-media-links.index') }}"
                               class="nav-link {{ Route::currentRouteName() == 'vendor.social-media-links.index' ? 'active' : '' }}">
                                <i class="bi bi-circle nav-icon"></i>
                                <p>All Links</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- ─── ACCOUNT ─────────────────────────────────── --}}
                <li class="nav-header">ACCOUNT</li>

                {{-- Profile --}}
                <li class="nav-item">
                    <a href="{{ route('vendor.profile.edit') }}"
                       class="nav-link {{ Route::currentRouteName() == 'vendor.profile.edit' ? 'active' : '' }}">
                        <i class="nav-icon bi bi-person-circle"></i>
                        <p>My Profile</p>
                    </a>
                </li>

            </ul>
        </nav>

    </div>
    {{-- ── End .sidebar ──────────────────────────────────────────── --}}

</aside>
