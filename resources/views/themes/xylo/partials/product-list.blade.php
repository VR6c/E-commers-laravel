@php $currency = activeCurrency(); @endphp

@forelse ($products as $product)
    <div class="xsf-product-grid__item">
        @include('themes.xylo.partials.product-card', ['product' => $product, 'currency' => $currency])
    </div>
@empty
    <div class="xsf-product-grid__empty">
        <div class="xsf-empty">
            <i class="fa-regular fa-bag-shopping xsf-empty__icon" aria-hidden="true"></i>
            <h2 class="xsf-empty__title">{{ 'No products found' }}</h2>
            <p class="xsf-empty__text">{{ 'Try adjusting your filters or browse all products.' }}</p>
            <a href="{{ route('shop.index') }}" class="xsf-empty__cta">
                <i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i>
                {{ 'Clear filters' }}
            </a>
        </div>
    </div>
@endforelse

@if ($products->hasPages())
    <div class="xsf-product-grid__pagination xsf-pagination">
        <p class="xsf-pagination__info mb-0">
            {{ 'Showing' }}
            <strong>{{ $products->firstItem() }}–{{ $products->lastItem() }}</strong>
            {{ 'of' }}
            <strong>{{ $products->total() }}</strong>
            {{ 'results' }}
        </p>
        <div class="paginations">
            {{ $products->links('vendor.pagination.custom') }}
        </div>
    </div>
@endif
