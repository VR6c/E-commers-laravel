@extends('themes.xylo.layouts.master')

@section('content')
    @php $currency = activeCurrency(); @endphp

    <section class="xsf-section xsf-listing-page">
        <div class="container">
            <div class="xsf-listing-head">
                <h1 class="xsf-listing-head__title">
                    {{ 'Search results for' }}
                    <span class="xsf-listing-head__query">"{{ $query }}"</span>
                </h1>
                <span class="xsf-listing-head__count" role="status">{{ $products->total() }} {{ 'results' }}</span>
            </div>

            @if ($products->isEmpty())
                <div class="xsf-empty">
                    <i class="fa-solid fa-magnifying-glass xsf-empty__icon" aria-hidden="true"></i>
                    <h2 class="xsf-empty__title">{{ 'No products found' }}</h2>
                    <p class="xsf-empty__text">{{ 'Check the spelling or try a more general term.' }}</p>
                    <a href="{{ route('shop.index') }}" class="xsf-empty__cta">{{ 'View All' }}</a>
                </div>
            @else
                <div class="xsf-product-grid">
                    @foreach ($products as $product)
                        <div class="xsf-product-grid__item">
                            @include('themes.xylo.partials.product-card', ['product' => $product, 'currency' => $currency])
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($products->hasPages())
                <div class="xsf-pagination mt-4">
                    <p class="xsf-pagination__info">
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
        </div>
    </section>
@endsection

@section('js')
    <script>
        function addToCart(productId) {
            fetch("{{ route('cart.add') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ product_id: productId, quantity: 1 })
            })
            .then(response => response.json())
            .then(data => {
                toastr.success(data.message || "{{ 'Added to cart' }}");
                updateCartCount(data.cart);
            })
            .catch(error => console.error("Error:", error));
        }

        function updateCartCount(cart) {
            let totalCount = Object.values(cart || {}).reduce((sum, item) => sum + item.quantity, 0);
            const el = document.getElementById("cart-count");
            if (el) { el.textContent = totalCount; el.classList.toggle('d-none', totalCount === 0); }
        }

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.wishlist-btn');
            if (!btn) return;
            const productId = btn.getAttribute('data-product-id');
            fetch('/customer/wishlist', {
                method: 'POST',
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json",
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(response => {
                if (response.status === 401) { window.location.href = '/customer/login'; }
                else if (response.ok) { return response.json(); }
                else { throw new Error('Something went wrong'); }
            })
            .then(data => { if (data?.message) { btn.classList.toggle('is-active'); toastr.success(data.message); } })
            .catch(error => console.error('Error:', error));
        });
    </script>
@endsection
