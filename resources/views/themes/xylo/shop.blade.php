@extends('themes.xylo.layouts.master')

@section('content')
    @php $currency = activeCurrency(); @endphp

    <section class="xsf-section main-shop">
        <div class="container">

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="xsf-breadcrumb">
                <a href="{{ route('xylo.home') }}">{{ 'Home' }}</a>
                <i class="fa-solid fa-angle-right" aria-hidden="true"></i>
                <span>{{ 'Shop' }}</span>
            </nav>

            {{-- Listing head --}}
            <div class="xsf-listing-head">
                <div class="xsf-listing-head__left">
                    <h1 class="xsf-listing-head__title">{{ 'Shop' }}</h1>
                    <span class="xsf-listing-head__count" id="resultCount">
                        {{ $products->total() }} {{ 'results' }}
                    </span>
                </div>
                <button class="xsf-listing-head__filter-btn btn d-lg-none"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#filterOffcanvas"
                        aria-controls="filterOffcanvas">
                    <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                    {{ 'Filters' }}
                </button>
            </div>

            {{-- Sort / view toolbar --}}
            <div class="xsf-toolbar">
                <div class="xsf-toolbar__left">
                    <i class="fa-solid fa-table-cells-large xsf-toolbar__grid-icon" aria-hidden="true"></i>
                    <span class="xsf-toolbar__count" id="toolbarCount">
                        {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}
                        {{ 'of' }}
                        {{ $products->total() }}
                    </span>
                </div>
                <div class="xsf-toolbar__right">
                    <label class="xsf-toolbar__sort-label" for="sortSelect">
                        <i class="fa-solid fa-arrow-up-wide-short" aria-hidden="true"></i>
                        {{ 'Sort by' }}:
                    </label>
                    <select class="xsf-toolbar__sort-select form-select form-select-sm"
                            id="sortSelect"
                            name="sort"
                            aria-label="{{ 'Sort by' }}">
                        <option value="newest"   {{ request('sort') === 'newest'    ? 'selected' : '' }}>{{ 'Newest'    ?? 'Newest' }}</option>
                        <option value="price_asc"  {{ request('sort') === 'price_asc'  ? 'selected' : '' }}>{{ 'Price: Low → High'  ?? 'Price: Low → High' }}</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>{{ 'Price: High → Low' }}</option>
                        <option value="popular"    {{ request('sort') === 'popular'    ? 'selected' : '' }}>{{ 'Most Popular'    ?? 'Most Popular' }}</option>
                    </select>
                </div>
            </div>

            <div class="row g-4">

                {{-- ── Sidebar filters ─────────────────────────────── --}}
                <aside class="col-lg-3">
                    <div class="offcanvas offcanvas-lg offcanvas-start xsf-filter" tabindex="-1" id="filterOffcanvas">

                        {{-- Mobile offcanvas header --}}
                        <div class="offcanvas-header">
                            <h5 class="offcanvas-title">
                                <i class="fa-solid fa-sliders me-2" aria-hidden="true"></i>
                                {{ 'Filters' }}
                            </h5>
                            <button type="button" class="btn-close"
                                    data-bs-dismiss="offcanvas"
                                    data-bs-target="#filterOffcanvas"
                                    aria-label="Close"></button>
                        </div>

                        {{-- Desktop card header --}}
                        <div class="xsf-filter__card-header d-none d-lg-flex">
                            <span class="xsf-filter__card-title">
                                <i class="fa-solid fa-sliders me-2" aria-hidden="true"></i>
                                {{ 'Filters' }}
                            </span>
                            <a href="{{ route('shop.index') }}"
                               class="xsf-filter__clear"
                               id="clearAllFilters"
                               title="{{ 'Clear all' }}">
                                {{ 'Clear all' }}
                            </a>
                        </div>

                        <div class="offcanvas-body" id="filterSidebar">

                            {{-- Brands --}}
                            <div class="xsf-filter__group">
                                <h6 class="xsf-filter__title">{{ 'Brands' }}</h6>
                                @foreach ($brands as $brand)
                                    <div class="form-check">
                                        <input class="form-check-input filter-input" type="checkbox"
                                               id="brand-{{ $brand->id }}"
                                               name="brand[]"
                                               value="{{ $brand->id }}"
                                               {{ in_array($brand->id, (array) request('brand', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="brand-{{ $brand->id }}">
                                            {{ mb_convert_case($brand->name ?? $brand->slug, MB_CASE_TITLE, 'UTF-8') }}
                                            <span class="xsf-filter__count">({{ $brand->products_count }})</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Categories --}}
                            <div class="xsf-filter__group">
                                <h6 class="xsf-filter__title">{{ 'Categories' }}</h6>
                                @foreach ($categories as $category)
                                    <div class="form-check">
                                        <input class="form-check-input filter-input" type="checkbox"
                                               id="cat-{{ $category->id }}"
                                               name="category[]"
                                               value="{{ $category->id }}"
                                               {{ in_array($category->id, (array) request('category', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="cat-{{ $category->id }}">
                                            {{ mb_convert_case($category->name ?? $category->slug, MB_CASE_TITLE, 'UTF-8') }}
                                            <span class="xsf-filter__count">({{ $category->products_count }})</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Price range --}}
                            <div class="xsf-filter__group">
                                <h6 class="xsf-filter__title">{{ 'Price' }}</h6>

                                {{-- Current range display --}}
                                <div class="rs-values">
                                    <span class="rs-values__badge" id="minBadge">
                                        {{ $currency->symbol ?? '$' }}<span id="minPriceText">{{ request('price_min', 0) }}</span>
                                    </span>
                                    <span class="rs-values__sep">&ndash;</span>
                                    <span class="rs-values__badge" id="maxBadge">
                                        {{ $currency->symbol ?? '$' }}<span id="maxPriceText">{{ request('price_max', 1000) }}</span>
                                    </span>
                                </div>

                                {{-- Custom dual slider --}}
                                <div class="range-slider" id="rangeSlider"
                                     data-min="0" data-max="1000" data-step="10"
                                     data-val-min="{{ request('price_min', 0) }}"
                                     data-val-max="{{ request('price_max', 1000) }}"
                                     role="group" aria-label="Price range">

                                    {{-- Hidden form inputs (submitted with filter) --}}
                                    <input type="hidden" name="price_min" id="minPrice" value="{{ request('price_min', 0) }}">
                                    <input type="hidden" name="price_max" id="maxPrice" value="{{ request('price_max', 1000) }}">

                                    {{-- Track --}}
                                    <div class="rs-track">
                                        <div class="rs-track__fill" id="rsFill"></div>
                                    </div>

                                    {{-- Thumbs --}}
                                    <div class="rs-thumb" id="rsThumbMin"
                                         role="slider"
                                         aria-label="Minimum price"
                                         aria-valuemin="0" aria-valuemax="1000"
                                         aria-valuenow="{{ request('price_min', 0) }}"
                                         tabindex="0">
                                        <div class="rs-thumb__tooltip" id="minTooltip"></div>
                                    </div>
                                    <div class="rs-thumb" id="rsThumbMax"
                                         role="slider"
                                         aria-label="Maximum price"
                                         aria-valuemin="0" aria-valuemax="1000"
                                         aria-valuenow="{{ request('price_max', 1000) }}"
                                         tabindex="0">
                                        <div class="rs-thumb__tooltip" id="maxTooltip"></div>
                                    </div>
                                </div>

                                {{-- Boundary labels --}}
                                <div class="rs-bounds">
                                    <span>{{ $currency->symbol ?? '$' }}0</span>
                                    <span>{{ $currency->symbol ?? '$' }}1,000</span>
                                </div>
                            </div>

                            {{-- Colors --}}
                            <div class="xsf-filter__group">
                                <h6 class="xsf-filter__title">{{ 'Colors' }}</h6>
                                @foreach (['red', 'black'] as $color)
                                    <div class="form-check">
                                        <input class="form-check-input filter-input" type="checkbox"
                                               id="color-{{ $color }}"
                                               name="color[]"
                                               value="{{ strtolower($color) }}"
                                               {{ in_array(strtolower($color), (array) request('color', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="color-{{ $color }}">
                                            <span class="xsf-filter__color-dot color-circle {{ strtolower($color) }}"
                                                  aria-hidden="true"></span>
                                            {{ ucfirst($color) }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Sizes --}}
                            <div class="xsf-filter__group">
                                <h6 class="xsf-filter__title">{{ 'Size' }}</h6>
                                <div class="xsf-filter__size-grid">
                                    @foreach (['M' => 'M', 'L' => 'L'] as $key => $size)
                                        <label class="xsf-filter__size-chip {{ in_array($key, (array) request('size', [])) ? 'is-active' : '' }}"
                                               for="size-{{ $key }}">
                                            <input class="filter-input visually-hidden" type="checkbox"
                                                   id="size-{{ $key }}"
                                                   name="size[]"
                                                   value="{{ $key }}"
                                                   {{ in_array($key, (array) request('size', [])) ? 'checked' : '' }}>
                                            {{ $size }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Mobile clear-all button --}}
                            <div class="d-lg-none mt-2">
                                <a href="{{ route('shop.index') }}" class="btn btn-outline-danger btn-sm w-100">
                                    <i class="fa-solid fa-xmark me-1" aria-hidden="true"></i>
                                    {{ 'Clear all filters' }}
                                </a>
                            </div>

                        </div>{{-- /.offcanvas-body --}}
                    </div>
                </aside>

                {{-- ── Products grid ──────────────────────────────── --}}
                <div class="col-lg-9">
                    <div class="xsf-product-grid" id="productList">
                        @include('themes.xylo.partials.product-list')
                    </div>
                </div>

            </div>{{-- /.row --}}
        </div>{{-- /.container --}}
    </section>
@endsection

@section('js')
<script>
    // ── Price range slider ───────────────────────────────────────────
    (function () {
        const wrap     = document.getElementById('rangeSlider');
        if (!wrap) return;

        // Config from data attributes
        const MIN      = parseInt(wrap.dataset.min,    10);
        const MAX      = parseInt(wrap.dataset.max,    10);
        const STEP     = parseInt(wrap.dataset.step,   10);
        const GAP      = STEP;          // minimum gap between the two values
        const DEBOUNCE = 500;           // ms before AJAX filter fires

        // DOM refs
        const thumbMin  = document.getElementById('rsThumbMin');
        const thumbMax  = document.getElementById('rsThumbMax');
        const fill      = document.getElementById('rsFill');
        const inputMin  = document.getElementById('minPrice');
        const inputMax  = document.getElementById('maxPrice');
        const txtMin    = document.getElementById('minPriceText');
        const txtMax    = document.getElementById('maxPriceText');
        const tipMin    = document.getElementById('minTooltip');
        const tipMax    = document.getElementById('maxTooltip');
        // Badge elements (the rs-values__badge spans around txtMin/txtMax)
        const badgeMin  = document.getElementById('minBadge');
        const badgeMax  = document.getElementById('maxBadge');

        // Extract currency symbol from the badge text (everything before the span)
        const sym = badgeMin
            ? Array.from(badgeMin.childNodes)
                   .filter(n => n.nodeType === Node.TEXT_NODE)
                   .map(n => n.textContent.trim())
                   .join('')
            : '';

        let valMin = parseInt(wrap.dataset.valMin, 10);
        let valMax = parseInt(wrap.dataset.valMax, 10);
        let filterTimer = null;
        let dragging = null;        // 'min' | 'max' | null
        let dragStartX = 0;
        let dragStartVal = 0;

        // ── Helpers ─────────────────────────────────────────────────
        function snap(v) {
            return Math.round(v / STEP) * STEP;
        }
        function clamp(v, lo, hi) {
            return Math.min(Math.max(v, lo), hi);
        }
        function pct(v) {
            return ((v - MIN) / (MAX - MIN)) * 100;
        }
        // Convert a pointer clientX to a slider value
        function xToVal(clientX) {
            const rect  = wrap.getBoundingClientRect();
            const ratio = (clientX - rect.left) / rect.width;
            return snap(clamp(MIN + ratio * (MAX - MIN), MIN, MAX));
        }

        // ── Render ───────────────────────────────────────────────────
        function render() {
            const leftPct  = pct(valMin);
            const rightPct = pct(valMax);

            // Thumb positions (% of wrapper width, thumb centre)
            thumbMin.style.left = leftPct  + '%';
            thumbMax.style.left = rightPct + '%';

            // Fill
            fill.style.left  = leftPct  + '%';
            fill.style.width = (rightPct - leftPct) + '%';

            // Tooltip text
            tipMin.textContent = sym + valMin;
            tipMax.textContent = sym + valMax;

            // Visible badge / display values
            txtMin.textContent = valMin;
            txtMax.textContent = valMax;

            // ARIA
            thumbMin.setAttribute('aria-valuenow', valMin);
            thumbMax.setAttribute('aria-valuenow', valMax);

            // Hidden inputs (consumed by buildFilterUrl)
            inputMin.value = valMin;
            inputMax.value = valMax;
        }

        // ── Drag logic ───────────────────────────────────────────────
        function onPointerDown(e, which) {
            e.preventDefault();
            dragging    = which;
            dragStartX  = e.clientX;
            dragStartVal = which === 'min' ? valMin : valMax;

            const thumb = which === 'min' ? thumbMin : thumbMax;
            thumb.classList.add('is-active');
            thumb.setPointerCapture(e.pointerId);
        }

        function onPointerMove(e) {
            if (!dragging) return;
            const newVal = xToVal(e.clientX);

            if (dragging === 'min') {
                valMin = clamp(newVal, MIN, valMax - GAP);
            } else {
                valMax = clamp(newVal, valMin + GAP, MAX);
            }

            render();
        }

        function onPointerUp(e) {
            if (!dragging) return;
            const thumb = dragging === 'min' ? thumbMin : thumbMax;
            thumb.classList.remove('is-active');
            dragging = null;

            // Debounced filter
            clearTimeout(filterTimer);
            filterTimer = setTimeout(sendFilterRequest, DEBOUNCE);
        }

        // ── Keyboard support ─────────────────────────────────────────
        function onKeyDown(e, which) {
            const delta = { ArrowLeft: -STEP, ArrowDown: -STEP,
                            ArrowRight: STEP, ArrowUp: STEP }[e.key];
            if (delta === undefined) return;
            e.preventDefault();

            if (which === 'min') {
                valMin = clamp(snap(valMin + delta), MIN, valMax - GAP);
            } else {
                valMax = clamp(snap(valMax + delta), valMin + GAP, MAX);
            }
            render();
            clearTimeout(filterTimer);
            filterTimer = setTimeout(sendFilterRequest, DEBOUNCE);
        }

        // ── Event bindings ───────────────────────────────────────────
        thumbMin.addEventListener('pointerdown', e => onPointerDown(e, 'min'));
        thumbMax.addEventListener('pointerdown', e => onPointerDown(e, 'max'));
        thumbMin.addEventListener('pointermove', onPointerMove);
        thumbMax.addEventListener('pointermove', onPointerMove);
        thumbMin.addEventListener('pointerup',   onPointerUp);
        thumbMax.addEventListener('pointerup',   onPointerUp);
        thumbMin.addEventListener('keydown',     e => onKeyDown(e, 'min'));
        thumbMax.addEventListener('keydown',     e => onKeyDown(e, 'max'));

        // Click on the track itself — move nearest thumb
        wrap.addEventListener('pointerdown', function (e) {
            if (e.target === thumbMin || e.target === thumbMax) return;
            if (e.target.closest('.rs-thumb')) return;
            const clicked = xToVal(e.clientX);
            const toMin   = Math.abs(clicked - valMin);
            const toMax   = Math.abs(clicked - valMax);
            if (toMin <= toMax) {
                valMin = clamp(clicked, MIN, valMax - GAP);
            } else {
                valMax = clamp(clicked, valMin + GAP, MAX);
            }
            render();
            clearTimeout(filterTimer);
            filterTimer = setTimeout(sendFilterRequest, DEBOUNCE);
        });

        // ── Init ─────────────────────────────────────────────────────
        render();

        // Expose for buildFilterUrl (reads inputMin/inputMax directly — no change needed)
        window._rsReady = true;
    })();

    // ── AJAX product fetch ───────────────────────────────────────────
    function fetchProducts(url) {
        const list = document.getElementById('productList');
        if (!list) return;
        list.classList.add('is-loading');

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html, */*'
            }
        })
        .then(r => {
            if (!r.ok) {
                throw new Error('HTTP ' + r.status);
            }
            return r.text();
        })
        .then(html => {
            list.innerHTML = html;
            list.classList.remove('is-loading');
            list.scrollIntoView({ behavior: 'smooth', block: 'start' });
            syncSizeChipState();
        })
        .catch(err => {
            console.error('Fetch products error:', err);
            list.classList.remove('is-loading');
            // Graceful fallback to regular page navigation if AJAX fails
            window.location.href = url;
        });
    }

    function buildFilterUrl() {
        const url    = new URL("{{ route('shop.index') }}", window.location.origin);
        const params = new URLSearchParams();

        document.querySelectorAll('.filter-input:checked').forEach(el => {
            params.append(el.name, el.value);
        });

        let minVal = parseInt(document.getElementById('minPrice').value, 10);
        let maxVal = parseInt(document.getElementById('maxPrice').value, 10);
        params.append('price_min', minVal);
        params.append('price_max', maxVal);

        const sort = document.getElementById('sortSelect').value;
        if (sort) { params.append('sort', sort); }

        url.search = params.toString();
        return url.pathname + url.search;
    }

    function sendFilterRequest() {
        const url = buildFilterUrl();
        fetchProducts(url);
        window.history.pushState({}, '', url);
    }

    document.querySelectorAll('.filter-input').forEach(input => {
        input.addEventListener('change', sendFilterRequest);
    });

    // ── Sort select ──────────────────────────────────────────────────
    document.getElementById('sortSelect').addEventListener('change', sendFilterRequest);

    // ── Pagination (delegated, survives AJAX swaps) ──────────────────
    document.getElementById('productList').addEventListener('click', function (e) {
        const pageLink = e.target.closest('.page-link-custom');
        if (pageLink && pageLink.tagName === 'A') {
            e.preventDefault();
            const rawUrl = pageLink.getAttribute('href');
            if (rawUrl && rawUrl !== '#') {
                try {
                    const parsed = new URL(rawUrl, window.location.origin);
                    const safeUrl = parsed.pathname + parsed.search;
                    fetchProducts(safeUrl);
                    window.history.pushState({}, '', safeUrl);
                } catch (err) {
                    window.location.href = rawUrl;
                }
            }
        }
    });

    window.addEventListener('popstate', () => {
        const safeUrl = window.location.pathname + window.location.search;
        fetchProducts(safeUrl);
    });

    // ── Size chip toggle visual state ────────────────────────────────
    function syncSizeChipState() {
        document.querySelectorAll('.xsf-filter__size-chip').forEach(chip => {
            const input = chip.querySelector('input');
            chip.classList.toggle('is-active', input?.checked ?? false);
        });
    }

    document.addEventListener('change', function (e) {
        if (e.target.closest('.xsf-filter__size-chip')) {
            syncSizeChipState();
        }
    });

    // ── Add to cart ──────────────────────────────────────────────────
    function addToCart(productId) {
        fetch("{{ route('cart.add') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: productId, quantity: 1 })
        })
        .then(r => r.json())
        .then(data => {
            toastr.success(data.message || "{{ 'Added to cart' }}");
            updateCartCount(data.cart);
        })
        .catch(err => console.error('Cart error:', err));
    }

    function updateCartCount(cart) {
        const total = Object.values(cart || {}).reduce((sum, item) => sum + item.quantity, 0);
        const el    = document.getElementById('cart-count');
        if (el) {
            el.textContent = total;
            el.classList.toggle('d-none', total === 0);
        }
    }

    // ── Wishlist (delegated, survives AJAX swaps) ────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.wishlist-btn');
        if (!btn) return;
        const productId = btn.getAttribute('data-product-id');
        fetch('{{ route('customer.wishlist.toggle') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(r => {
            if (r.status === 401) { window.location.href = '/customer/login'; return; }
            if (r.ok) return r.json();
            throw new Error('Wishlist error');
        })
        .then(data => {
            if (!data) return;
            const icon = btn.querySelector('i');
            if (data.status === 'added') {
                icon.classList.replace('fa-regular', 'fa-solid');
                btn.classList.add('is-active');
                toastr.success(data.message);
            } else {
                icon.classList.replace('fa-solid', 'fa-regular');
                btn.classList.remove('is-active');
                toastr.info(data.message);
            }
            // Update header badge
            const badge = document.getElementById('wishlist-count');
            if (badge && data.count !== undefined) {
                badge.textContent = data.count;
                badge.classList.toggle('d-none', data.count === 0);
            }
        })
        .catch(err => console.error('Wishlist error:', err));
    });

    // ── Init ─────────────────────────────────────────────────────────
    syncSizeChipState();
</script>
@endsection
