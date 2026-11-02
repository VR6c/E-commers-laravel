@extends('themes.xylo.layouts.master')

@section('title', $category->name)

@section('content')
    @php $currency = activeCurrency(); @endphp

    <section class="xsf-section xsf-listing-section">
        <div class="container">

            {{-- ── Breadcrumb ────────────────────────────────────────── --}}
            <nav aria-label="breadcrumb" class="xsf-breadcrumb">
                <a href="{{ route('xylo.home') }}">
                    <i class="fa-solid fa-house" aria-hidden="true"></i>
                    {{ 'Home' }}
                </a>
                @foreach ($breadcrumbs as $crumb)
                    <i class="fa-solid fa-angle-right" aria-hidden="true"></i>
                    <a href="{{ route('category.show', $crumb->slug) }}" @if ($loop->last) aria-current="page" @endif>{{ $crumb->name }}</a>
                @endforeach
            </nav>

            {{-- ── Listing header ─────────────────────────────────────── --}}
            <div class="xsf-listing-head">
                <div class="xsf-listing-head__left">
                    <h1 class="xsf-listing-head__title">{{ $category->name }}</h1>
                    @if ($products->total() > 0)
                        <span class="xsf-listing-head__count" role="status">
                            {{ $products->total() }} {{ 'products' }}
                        </span>
                    @endif
                </div>

                {{-- Mobile: toggle filter sidebar --}}
                <button class="xsf-listing-head__filter-btn d-lg-none"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#filterSidebar"
                        aria-controls="filterSidebar"
                        aria-expanded="false">
                    <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                    {{ 'Filters' }}
                </button>
            </div>

            {{-- ── Two-column layout: sidebar + main ────────────────── --}}
            <div class="xsf-listing-layout">

                {{-- ═══ FILTER SIDEBAR (offcanvas on mobile, inline on ≥lg) ═══ --}}
                <aside class="xsf-filter offcanvas-lg offcanvas-bottom" tabindex="-1" id="filterSidebar" aria-labelledby="filterSidebarTitle">

                    {{-- Mobile offcanvas header --}}
                    <div class="offcanvas-header">
                        <h2 class="offcanvas-title" id="filterSidebarTitle">
                            <i class="fa-solid fa-sliders me-2" aria-hidden="true"></i>
                            {{ 'Filters' }}
                        </h2>
                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="offcanvas"
                                data-bs-target="#filterSidebar"
                                aria-label="{{ 'Close' }}"></button>
                    </div>

                    {{-- Desktop header --}}
                    <div class="xsf-filter__card-header d-none d-lg-flex">
                        <h2 class="xsf-filter__card-title">
                            <i class="fa-solid fa-sliders me-2" aria-hidden="true"></i>
                            {{ 'Filters' }}
                        </h2>
                        @if (request()->hasAny(['min_price', 'max_price']))
                            <a href="{{ route('category.show', $category->slug) }}" class="xsf-filter__clear">
                                {{ 'Clear all' }}
                            </a>
                        @endif
                    </div>

                    <div class="offcanvas-body">
                        <form method="GET" id="filter-form" action="{{ route('category.show', $category->slug) }}">
                            {{-- Preserve sort when filtering --}}
                            @if (request('sort'))
                                <input type="hidden" name="sort" value="{{ request('sort') }}">
                            @endif

                            {{-- Price range --}}
                            <div class="xsf-filter__group">
                                <p class="xsf-filter__title">{{ 'Price Range' }}</p>

                                <div class="xsf-filter-price-inputs">
                                    <div class="xsf-filter-price-inputs__field">
                                        <label class="xsf-filter-price-inputs__label" for="min_price">{{ 'Min' }}</label>
                                        <div class="xsf-filter-price-inputs__wrap">
                                            <span class="xsf-filter-price-inputs__symbol">{{ $currency->symbol ?? '$' }}</span>
                                            <input type="number"
                                                   id="min_price"
                                                   name="min_price"
                                                   class="form-control form-control-sm xsf-filter-price-inputs__input"
                                                   placeholder="0"
                                                   min="0"
                                                   value="{{ request('min_price') }}">
                                        </div>
                                    </div>
                                    <span class="xsf-filter-price-inputs__sep">&ndash;</span>
                                    <div class="xsf-filter-price-inputs__field">
                                        <label class="xsf-filter-price-inputs__label" for="max_price">{{ 'Max' }}</label>
                                        <div class="xsf-filter-price-inputs__wrap">
                                            <span class="xsf-filter-price-inputs__symbol">{{ $currency->symbol ?? '$' }}</span>
                                            <input type="number"
                                                   id="max_price"
                                                   name="max_price"
                                                   class="form-control form-control-sm xsf-filter-price-inputs__input"
                                                   placeholder="{{ 'Any' }}"
                                                   min="0"
                                                   value="{{ request('max_price') }}">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm w-100 mt-3">
                                    <i class="fa-solid fa-filter me-1" aria-hidden="true"></i>
                                    {{ 'Apply Filter' }}
                                </button>

                                @if (request()->hasAny(['min_price', 'max_price']))
                                    <a href="{{ route('category.show', $category->slug) }}"
                                       class="xsf-filter__clear d-flex justify-content-center mt-2">
                                        <i class="fa-solid fa-xmark me-1" aria-hidden="true"></i>
                                        {{ 'Clear price filter' }}
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </aside>
                {{-- END SIDEBAR --}}

                {{-- ═══ MAIN CONTENT ═══ --}}
                <div class="xsf-listing-main">

                    {{-- Toolbar --}}
                    <div class="xsf-toolbar" role="region" aria-label="{{ 'Sort and results' }}">
                        <div class="xsf-toolbar__left">
                            @if ($products->total() > 0)
                                <span class="xsf-toolbar__count">
                                    {{ 'Showing' }}
                                    <strong>{{ $products->firstItem() }}–{{ $products->lastItem() }}</strong>
                                    {{ 'of' }}
                                    <strong>{{ $products->total() }}</strong>
                                </span>
                            @else
                                <span class="xsf-toolbar__count">{{ 'No products found' }}</span>
                            @endif
                        </div>

                        <div class="xsf-toolbar__right">
                            <label class="xsf-toolbar__sort-label" for="sort-select">
                                <i class="fa-solid fa-arrow-up-wide-short" aria-hidden="true"></i>
                                {{ 'Sort' }}
                            </label>
                            <select id="sort-select"
                                    name="sort"
                                    class="form-select form-select-sm xsf-toolbar__sort-select"
                                    onchange="applySortFilter(this.value)">
                                <option value="">{{ 'Default' }}</option>
                                <option value="newest"     {{ request('sort') === 'newest'     ? 'selected' : '' }}>{{ 'Newest' }}</option>
                                <option value="price_asc"  {{ request('sort') === 'price_asc'  ? 'selected' : '' }}>{{ 'Price: Low → High' }}</option>
                                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>{{ 'Price: High → Low' }}</option>
                                <option value="rating"     {{ request('sort') === 'rating'     ? 'selected' : '' }}>{{ 'Top Rated' }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Active filter chips --}}
                    @if (request()->hasAny(['min_price', 'max_price']))
                        <div class="xsf-filter__active-chips" role="group" aria-label="{{ 'Active filters' }}">
                            @if (request('min_price'))
                                <span class="xsf-filter__chip">
                                    {{ 'Min' }}: {{ $currency->symbol ?? '$' }}{{ request('min_price') }}
                                    <a href="{{ route('category.show', $category->slug) . '?' . http_build_query(array_merge(request()->except('min_price'))) }}" aria-label="{{ 'Remove min price filter' }}">
                                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                    </a>
                                </span>
                            @endif
                            @if (request('max_price'))
                                <span class="xsf-filter__chip">
                                    {{ 'Max' }}: {{ $currency->symbol ?? '$' }}{{ request('max_price') }}
                                    <a href="{{ route('category.show', $category->slug) . '?' . http_build_query(array_merge(request()->except('max_price'))) }}" aria-label="{{ 'Remove max price filter' }}">
                                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                    </a>
                                </span>
                            @endif
                        </div>
                    @endif

                    {{-- Product grid --}}
                    <div class="xsf-product-grid" id="product-grid">
                        @forelse ($products as $product)
                            <div class="xsf-product-grid__item">
                                @include('themes.xylo.partials.product-card', [
                                    'product'     => $product,
                                    'currency'    => $currency,
                                    'wishlistIds' => $wishlistIds ?? [],
                                ])
                            </div>
                        @empty
                            <div class="xsf-product-grid__empty">
                                <div class="xsf-empty">
                                    <div class="xsf-empty__icon-wrap">
                                        <i class="fa-regular fa-bag-shopping xsf-empty__icon" aria-hidden="true"></i>
                                    </div>
                                    <h2 class="xsf-empty__title">{{ 'No products found' }}</h2>
                                    <p class="xsf-empty__text">
                                        {{ 'Try adjusting your price range, or browse the full catalog.' }}
                                    </p>
                                    @if (request()->hasAny(['min_price', 'max_price']))
                                        <a href="{{ route('category.show', $category->slug) }}" class="xsf-empty__cta">
                                            <i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i>
                                            {{ 'Clear filters' }}
                                        </a>
                                    @else
                                        <a href="{{ route('shop.index') }}" class="xsf-empty__cta">
                                            <i class="fa-solid fa-store" aria-hidden="true"></i>
                                            {{ 'Browse all products' }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforelse
                    </div>

                    {{-- Pagination --}}
                    @if ($products->hasPages())
                        <div class="xsf-pagination mt-4">
                            <p class="xsf-pagination__info mb-0">
                                {{ 'Showing' }}
                                <strong>{{ $products->firstItem() }}–{{ $products->lastItem() }}</strong>
                                {{ 'of' }}
                                <strong>{{ $products->total() }}</strong>
                                {{ 'results' }}
                            </p>
                            <div class="paginations">
                                {{ $products->appends(request()->query())->links('vendor.pagination.custom') }}
                            </div>
                        </div>
                    @endif

                </div>{{-- END MAIN --}}

            </div>{{-- END LAYOUT --}}

        </div>
    </section>
@endsection

@section('js')
<script>
    // ── Sort select: preserves active price filter params ─────────────
    function applySortFilter(sortValue) {
        const url = new URL(window.location.href);
        if (sortValue) {
            url.searchParams.set('sort', sortValue);
        } else {
            url.searchParams.delete('sort');
        }
        window.location.href = url.toString();
    }

    // ── Filter sheet: sync trigger aria-expanded (Esc handled by Bootstrap) ──
    (function () {
        const sheet = document.getElementById('filterSidebar');
        const btn   = document.querySelector('[aria-controls="filterSidebar"]');
        if (!sheet || !btn) return;
        sheet.addEventListener('shown.bs.offcanvas',  () => btn.setAttribute('aria-expanded', 'true'));
        sheet.addEventListener('hidden.bs.offcanvas', () => btn.setAttribute('aria-expanded', 'false'));
    })();

    // ── Add to cart ───────────────────────────────────────────────────
    function addToCart(productId) {
        fetch("{{ route('cart.add') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ product_id: productId, quantity: 1 }),
        })
        .then(r => r.json())
        .then(data => {
            toastr.success(data.message || '{{ 'Added to cart' }}');
            updateCartCount(data.cart);
        })
        .catch(err => console.error('Cart error:', err));
    }

    function updateCartCount(cart) {
        const total = Object.values(cart || {}).reduce((sum, item) => sum + item.quantity, 0);
        const el = document.getElementById('cart-count');
        if (el) {
            el.textContent = total;
            el.classList.toggle('d-none', total === 0);
        }
    }

    // ── Wishlist toggle ───────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.wishlist-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const productId = this.dataset.productId;
                fetch('/customer/wishlist', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ product_id: productId }),
                })
                .then(r => {
                    if (r.status === 401) { window.location.href = '/customer/login'; return; }
                    if (r.ok) return r.json();
                    throw new Error('Wishlist request failed');
                })
                .then(data => {
                    if (data?.message) {
                        this.classList.toggle('is-active');
                        const icon = this.querySelector('i');
                        if (icon) {
                            icon.classList.toggle('fa-solid');
                            icon.classList.toggle('fa-regular');
                        }
                        toastr.success(data.message);
                    }
                })
                .catch(err => console.error('Wishlist error:', err));
            });
        });
    });
</script>
@endsection
