<div class="product-search-container container my-4">
    @php
        $currencySymbol = function_exists('activeCurrency') && activeCurrency() ? activeCurrency()->symbol : '$';
    @endphp

    {{-- Search Bar Header --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden">
                        <span class="input-group-text bg-white border-0 ps-3 text-muted">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input
                            type="text"
                            class="form-control border-0 ps-2"
                            placeholder="Search products by name, SKU, tags or description..."
                            wire:model.live.debounce.300ms="search"
                            aria-label="Search products"
                        >
                        @if(!empty($search))
                            <button
                                class="btn btn-white border-0 text-muted pe-3"
                                type="button"
                                wire:click="removeFilter('search')"
                                title="Clear search"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end d-flex align-items-center justify-content-lg-end justify-content-between">
                    <div wire:loading wire:target="search, category_id, brand_id, min_price, max_price, sort_by, per_page" class="text-primary me-3">
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        <small class="fw-semibold">Updating results...</small>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-2 fs-6">
                        <x-number-flow :value="$products->total()" :decimals="0" suffix=" Found" />
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Filters Sidebar --}}
        <aside class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 100px; z-index: 10;">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0 fw-bold fs-6">
                        <i class="fa-solid fa-sliders text-primary me-2"></i>Filters
                    </h5>
                    @if(!empty($search) || $category_id || $brand_id || $min_price || $max_price || $sort_by !== 'newest')
                        <button
                            type="button"
                            wire:click="clearFilters"
                            class="btn btn-sm btn-link text-decoration-none text-danger p-0 fw-semibold"
                        >
                            Reset All
                        </button>
                    @endif
                </div>
                <div class="card-body p-3">
                    {{-- Category Filter --}}
                    <div class="mb-4">
                        <label for="category-select" class="form-label fw-semibold text-secondary small text-uppercase mb-2">Category</label>
                        <select id="category-select" class="form-select shadow-none" wire:model.live="category_id">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Brand Filter --}}
                    <div class="mb-4">
                        <label for="brand-select" class="form-label fw-semibold text-secondary small text-uppercase mb-2">Brand</label>
                        <select id="brand-select" class="form-select shadow-none" wire:model.live="brand_id">
                            <option value="">All Brands</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Price Range Filter --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary small text-uppercase mb-2">Price Range</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">{{ $currencySymbol }}</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-control"
                                        placeholder="Min"
                                        wire:model.live.debounce.500ms="min_price"
                                    >
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">{{ $currencySymbol }}</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-control"
                                        placeholder="Max"
                                        wire:model.live.debounce.500ms="max_price"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Items Per Page --}}
                    <div>
                        <label for="per-page-select" class="form-label fw-semibold text-secondary small text-uppercase mb-2">Items Per Page</label>
                        <select id="per-page-select" class="form-select shadow-none form-select-sm" wire:model.live="per_page">
                            <option value="12">12 Items</option>
                            <option value="24">24 Items</option>
                            <option value="48">48 Items</option>
                        </select>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main Results Area --}}
        <main class="col-lg-9">
            {{-- Toolbar: Active Filters & Sorting --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom g-2">
                {{-- Active Filter Pills --}}
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2 mb-md-0">
                    @if(!empty($search))
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                            Search: "{{ $search }}"
                            <button type="button" class="btn-close ms-2" style="font-size: 0.65rem;" wire:click="removeFilter('search')"></button>
                        </span>
                    @endif

                    @if($category_id)
                        @php $cat = $categories->firstWhere('id', $category_id); @endphp
                        @if($cat)
                            <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                Category: {{ $cat->name }}
                                <button type="button" class="btn-close ms-2" style="font-size: 0.65rem;" wire:click="removeFilter('category_id')"></button>
                            </span>
                        @endif
                    @endif

                    @if($brand_id)
                        @php $bnd = $brands->firstWhere('id', $brand_id); @endphp
                        @if($bnd)
                            <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                                Brand: {{ $bnd->name }}
                                <button type="button" class="btn-close ms-2" style="font-size: 0.65rem;" wire:click="removeFilter('brand_id')"></button>
                            </span>
                        @endif
                    @endif

                    @if($min_price !== null && $min_price > 0)
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                            Min Price: {{ $currencySymbol }}{{ number_format($min_price, 2) }}
                            <button type="button" class="btn-close ms-2" style="font-size: 0.65rem;" wire:click="removeFilter('min_price')"></button>
                        </span>
                    @endif

                    @if($max_price !== null && $max_price > 0)
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                            Max Price: {{ $currencySymbol }}{{ number_format($max_price, 2) }}
                            <button type="button" class="btn-close ms-2" style="font-size: 0.65rem;" wire:click="removeFilter('max_price')"></button>
                        </span>
                    @endif
                </div>

                {{-- Sort Dropdown --}}
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <label for="sort-by-select" class="text-nowrap small fw-semibold text-muted mb-0">Sort By:</label>
                    <select id="sort-by-select" class="form-select form-select-sm shadow-none w-auto" wire:model.live="sort_by">
                        <option value="newest">Newest Arrivals</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                        <option value="name_asc">Name: A to Z</option>
                    </select>
                </div>
            </div>

            {{-- Products Grid --}}
            <div class="row g-3">
                @forelse($products as $product)
                    <div class="col-6 col-md-4 col-xl-4" wire:key="product-{{ $product->id }}">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden product-card hover-shadow transition">
                            {{-- Image Container --}}
                            <div class="position-relative bg-light text-center p-3">
                                @php
                                    $imgUrl = $product->image_url
                                        ?: ($product->thumbnail?->image_path ? asset('storage/' . $product->thumbnail->image_path) : asset('assets/images/placeholder.jpg'));
                                @endphp
                                <img
                                    src="{{ $imgUrl }}"
                                    alt="{{ $product->name }}"
                                    class="img-fluid object-fit-contain"
                                    style="height: 180px; width: 100%;"
                                    onerror="this.src='https://via.placeholder.com/300x300?text=No+Image';"
                                >
                                @if($product->category)
                                    <span class="badge bg-secondary position-absolute top-0 start-0 m-2 rounded-pill small">
                                        {{ $product->category->name }}
                                    </span>
                                @endif
                            </div>

                            {{-- Card Body --}}
                            <div class="card-body d-flex flex-column p-3">
                                @if($product->brand)
                                    <small class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.75rem;">
                                        {{ $product->brand->name }}
                                    </small>
                                @endif

                                <h6 class="card-title fw-bold text-truncate mb-2" title="{{ $product->name }}">
                                    <a href="{{ Route::has('product.show') ? route('product.show', $product->slug ?? $product->id) : '#' }}" class="text-dark text-decoration-none">
                                        {{ $product->name }}
                                    </a>
                                </h6>

                                @if($product->short_description)
                                    <p class="text-muted small text-truncate-2 mb-3" style="font-size: 0.85rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $product->short_description }}
                                    </p>
                                @endif

                                <div class="mt-auto d-flex align-items-center justify-content-between pt-2 border-top">
                                    <div class="price-container">
                                        <x-number-flow :value="(float)$product->price" :prefix="$currencySymbol" :decimals="2" class="fw-bold text-primary fs-5" />
                                    </div>
                                    <a
                                        href="{{ Route::has('product.show') ? route('product.show', $product->slug ?? $product->id) : '#' }}"
                                        class="btn btn-outline-primary btn-sm rounded-pill px-3"
                                    >
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 py-5">
                        <div class="text-center bg-light p-5 rounded-4 border border-dashed">
                            <div class="mb-3 text-muted">
                                <i class="fa-solid fa-magnifying-glass fa-3x"></i>
                            </div>
                            <h5 class="fw-bold">No Products Found</h5>
                            <p class="text-muted mb-4">We couldn't find any products matching your search criteria.</p>
                            <button
                                type="button"
                                wire:click="clearFilters"
                                class="btn btn-primary rounded-pill px-4"
                            >
                                Clear All Filters
                            </button>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Livewire Pagination Links --}}
            @if($products->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $products->links() }}
                </div>
            @endif
        </main>
    </div>
</div>
