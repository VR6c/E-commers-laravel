<div class="product-search-container container xsf-listing-page">
    @php
        $currencySymbol = function_exists('activeCurrency') && activeCurrency() ? activeCurrency()->symbol : '$';
    @endphp

    {{-- Search bar --}}
    <h1 class="visually-hidden">Search products</h1>
    <div class="xsf-lw-search__bar">
        <div class="xsf-lw-search__field">
            <i class="fa-solid fa-magnifying-glass xsf-lw-search__icon" aria-hidden="true"></i>
            <input
                type="search"
                class="form-control"
                placeholder="Search products by name, SKU, tags or description..."
                wire:model.live.debounce.300ms="search"
                aria-label="Search products"
            >
            @if(!empty($search))
                <button
                    class="xsf-lw-search__clear"
                    type="button"
                    wire:click="removeFilter('search')"
                    aria-label="Clear search"
                >
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            @endif
        </div>
        <button type="button"
                class="xsf-lw-search__toggle d-lg-none"
                data-bs-toggle="collapse"
                data-bs-target="#lwFilters"
                aria-controls="lwFilters"
                aria-expanded="false">
            <i class="fa-solid fa-sliders" aria-hidden="true"></i> Filters
        </button>
        <div class="xsf-lw-search__status" role="status" aria-live="polite">
            <span wire:loading wire:target="search, category_id, brand_id, min_price, max_price, sort_by, per_page">
                <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Updating results...
            </span>
            <span wire:loading.remove wire:target="search, category_id, brand_id, min_price, max_price, sort_by, per_page">
                <x-number-flow :value="$products->total()" :decimals="0" suffix=" found" />
            </span>
        </div>
    </div>

    <div class="row g-3 g-lg-4">
        {{-- Filters: collapsible panel below lg, sticky sidebar from lg --}}
        <aside class="col-lg-3" aria-label="Filters">
            <div id="lwFilters" class="collapse d-lg-block" wire:ignore.self>
                <div class="xsf-lw-search__panel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="m-0 fs-6 fw-bold">Filters</h2>
                    @if(!empty($search) || $category_id || $brand_id || $min_price || $max_price || $sort_by !== 'newest')
                        <button
                            type="button"
                            wire:click="clearFilters"
                            class="btn btn-link text-decoration-none p-0 fw-semibold"
                        >
                            Reset all
                        </button>
                    @endif
                </div>
                <div class="xsf-lw-search__panel-body">
                    {{-- Category Filter --}}
                    <div>
                        <label for="category-select" class="form-label">Category</label>
                        <select id="category-select" class="form-select" wire:model.live="category_id">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Brand Filter --}}
                    <div>
                        <label for="brand-select" class="form-label">Brand</label>
                        <select id="brand-select" class="form-select" wire:model.live="brand_id">
                            <option value="">All Brands</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Price Range Filter --}}
                    <div>
                        <label class="form-label" id="lw-price-label">Price range</label>
                        <div class="row g-2" role="group" aria-labelledby="lw-price-label">
                            <div class="col-6">
                                <div class="input-group">
                                    <span class="input-group-text">{{ $currencySymbol }}</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-control"
                                        placeholder="Min"
                                        aria-label="Minimum price"
                                        wire:model.live.debounce.500ms="min_price"
                                    >
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="input-group">
                                    <span class="input-group-text">{{ $currencySymbol }}</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-control"
                                        placeholder="Max"
                                        aria-label="Maximum price"
                                        wire:model.live.debounce.500ms="max_price"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Items Per Page --}}
                    <div>
                        <label for="per-page-select" class="form-label">Items Per Page</label>
                        <select id="per-page-select" class="form-select" wire:model.live="per_page">
                            <option value="12">12 Items</option>
                            <option value="24">24 Items</option>
                            <option value="48">48 Items</option>
                        </select>
                    </div>
                </div>
                </div>
            </div>
        </aside>

        {{-- Main Results Area --}}
        <main class="col-lg-9" style="min-width:0">
            {{-- Toolbar: Active Filters & Sorting --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                {{-- Active Filter Pills --}}
                <div class="xsf-lw-search__chips">
                    @if(!empty($search))
                        <span class="xsf-lw-search__chip">
                            Search: "{{ $search }}"
                            <button type="button" wire:click="removeFilter(\'search\')" aria-label="Remove filter"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                        </span>
                    @endif

                    @if($category_id)
                        @php $cat = $categories->firstWhere('id', $category_id); @endphp
                        @if($cat)
                            <span class="xsf-lw-search__chip">
                                Category: {{ $cat->name }}
                                <button type="button" wire:click="removeFilter(\'category_id\')" aria-label="Remove filter"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                            </span>
                        @endif
                    @endif

                    @if($brand_id)
                        @php $bnd = $brands->firstWhere('id', $brand_id); @endphp
                        @if($bnd)
                            <span class="xsf-lw-search__chip">
                                Brand: {{ $bnd->name }}
                                <button type="button" wire:click="removeFilter(\'brand_id\')" aria-label="Remove filter"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                            </span>
                        @endif
                    @endif

                    @if($min_price !== null && $min_price > 0)
                        <span class="xsf-lw-search__chip">
                            Min Price: {{ $currencySymbol }}{{ number_format($min_price, 2) }}
                            <button type="button" wire:click="removeFilter(\'min_price\')" aria-label="Remove filter"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                        </span>
                    @endif

                    @if($max_price !== null && $max_price > 0)
                        <span class="xsf-lw-search__chip">
                            Max Price: {{ $currencySymbol }}{{ number_format($max_price, 2) }}
                            <button type="button" wire:click="removeFilter(\'max_price\')" aria-label="Remove filter"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                        </span>
                    @endif
                </div>

                {{-- Sort Dropdown --}}
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <label for="sort-by-select" class="text-nowrap small text-muted mb-0">Sort by</label>
                    <select id="sort-by-select" class="form-select form-select-sm shadow-none w-auto" wire:model.live="sort_by">
                        <option value="newest">Newest Arrivals</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                        <option value="name_asc">Name: A to Z</option>
                    </select>
                </div>
            </div>

            {{-- Products Grid --}}
            <div class="xsf-product-grid">
                @forelse($products as $product)
                    @php
                        $imgUrl = product_image_url($product->image_url ?: optional($product->thumbnail)->image_url);
                        $productUrl = Route::has('product.show') ? route('product.show', $product->slug ?? $product->id) : '#';
                    @endphp
                    <div class="xsf-product-grid__item" wire:key="product-{{ $product->id }}">
                        <article class="xsf-lw-search__card">
                            <a href="{{ $productUrl }}" class="xsf-lw-search__media" tabindex="-1" aria-hidden="true">
                                <img
                                    src="{{ $imgUrl }}"
                                    alt=""
                                    loading="lazy"
                                    onerror="this.src='https://via.placeholder.com/300x300?text=No+Image';"
                                >
                            </a>
                            <div class="xsf-lw-search__body">
                                @if($product->brand)
                                    <span class="xsf-lw-search__brand">{{ $product->brand->name }}</span>
                                @endif
                                <h2 class="xsf-lw-search__name" title="{{ $product->name }}">
                                    <a href="{{ $productUrl }}">{{ $product->name }}</a>
                                </h2>
                                <div class="xsf-lw-search__foot">
                                    <x-number-flow :value="(float)$product->price" :prefix="$currencySymbol" :decimals="2" />
                                    <a href="{{ $productUrl }}" class="xsf-lw-search__view" aria-label="View {{ $product->name }}">View</a>
                                </div>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="xsf-product-grid__empty">
                        <div class="xsf-empty">
                            <i class="fa-solid fa-magnifying-glass xsf-empty__icon" aria-hidden="true"></i>
                            <h2 class="xsf-empty__title">No products found</h2>
                            <p class="xsf-empty__text">We couldn't find any products matching your search. Try different keywords or clear your filters.</p>
                            <button type="button" wire:click="clearFilters" class="xsf-empty__cta">Clear all filters</button>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Livewire Pagination Links --}}
            @if($products->hasPages())
                <div class="d-flex justify-content-center mt-4 xsf-pagination border-0">
                    {{ $products->links() }}
                </div>
            @endif
        </main>
    </div>
</div>
