@extends('themes.xylo.layouts.master')

@section('content')
    @php $currency = activeCurrency(); @endphp

    <section class="xsf-section">
        <div class="container">
            <nav aria-label="breadcrumb" class="xsf-breadcrumb">
                <a href="{{ url('/') }}">{{ 'Home' }}</a>
                <i class="fa fa-angle-right" aria-hidden="true"></i>
                <span>{{ 'Cart' }}</span>
            </nav>

            <div class="xsf-listing-head">
                <h1 class="xsf-listing-head__title">{{ 'Cart' }}</h1>
            </div>

            @php $total = 0; @endphp
            <div class="row g-4 cart-page">
                <div class="col-lg-8">
                    @if (empty($cart))
                        <div class="xsf-empty">
                            <i class="fa fa-shopping-bag xsf-empty__icon" aria-hidden="true"></i>
                            <p class="xsf-empty__text">{{ 'Your cart is empty.' }}</p>
                            <a href="{{ route('shop.index') }}" class="btn btn-primary btn-pill mt-2">{{ 'Continue Shopping' }}</a>
                        </div>
                    @else
                        <div class="card">
                            <div class="table-responsive">
                                <table class="w-100 table align-middle mb-0 xsf-cart-table">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>{{ 'Product' }}</th>
                                            <th>{{ 'Price' }}</th>
                                            <th>{{ 'Quantity' }}</th>
                                            <th class="text-end">{{ 'Subtotal' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cart as $key => $item)
                                            @php
                                                $product = \App\Models\Product::with(['thumbnail'])->find($item['product_id']);
                                                $variant = isset($item['variant_id'])
                                                    ? \App\Models\ProductVariant::with('images')->find($item['variant_id'])
                                                    : \App\Models\ProductVariant::where('product_id', $item['product_id'])->whereRaw('is_primary is true')->first();
                                                $subtotal = $item['price'] * $item['quantity'];
                                            @endphp
                                            <tr>
                                                <td>
                                                    <button class="btn btn-link p-0 bnlink remove-from-cart" data-id="{{ $key }}"
                                                        aria-label="{{ 'Remove' }}">
                                                        <i class="fa-regular fa-circle-xmark"></i>
                                                    </button>
                                                </td>
                                                <td>
                                                    <div class="cart-product-detail">
                                                        <img src="{{ product_image_url(($variant->images->first() ?? $product->thumbnail)?->image_url) }}"
                                                            alt="{{ $variant->name ?? $product->name }}" class="cart-product-img">
                                                        <div class="cart-product-info">
                                                            <p class="cart-product-name">{{ $variant->name ?? $product->name }}</p>
                                                            <div class="cart-product-attributes">
                                                                @php $sizes = []; $colors = []; @endphp
                                                                @if (!empty($item['attributes']))
                                                                    @foreach ($item['attributes'] as $attributeValueId)
                                                                        @php $attributeValue = \App\Models\AttributeValue::with('attribute')->find($attributeValueId); @endphp
                                                                        @if ($attributeValue && $attributeValue->attribute)
                                                                            @php
                                                                                $attributeName = strtolower($attributeValue->attribute->name);
                                                                                if ($attributeName === 'size') { $sizes[] = $attributeValue->translated_value; }
                                                                                elseif ($attributeName === 'color') { $colors[] = $attributeValue->translated_value; }
                                                                            @endphp
                                                                        @endif
                                                                    @endforeach
                                                                @endif
                                                                @if (!empty($sizes))
                                                                    <div class="cart-attribute-sizes">
                                                                        @foreach ($sizes as $size)<span class="size-box">{{ $size }}</span>@endforeach
                                                                    </div>
                                                                @endif
                                                                @if (!empty($colors))
                                                                    <div class="cart-attribute-colors">
                                                                        @foreach ($colors as $color)<span class="color-circle {{ strtolower($color) }}" style="background-color: {{ strtolower($color) }};"></span>@endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><strong>{{ $currency->symbol }}{{ number_format($item['price'], 2) }}</strong></td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm xsf-cart-qty" value="{{ $item['quantity'] }}" min="1" data-id="{{ $key }}">
                                                </td>
                                                <td class="text-end"><strong>{{ $currency->symbol }}{{ number_format($subtotal, 2) }}</strong></td>
                                            </tr>
                                            @php $total += $subtotal; @endphp
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="{{ route('xylo.home') }}" class="btn btn-light btn-pill">{{ 'Continue Shopping' }}</a>
                        <a href="#" class="btn btn-dark btn-pill update-cart">{{ 'Update Cart' }}</a>
                    </div>
                </div>

                <div class="col-lg-4">
                    @php
                        $coupon = session('cart_coupon');
                        $discountAmount = 0;
                        if ($coupon) {
                            $discountAmount = $coupon['type'] === 'percentage'
                                ? $total * ($coupon['discount'] / 100)
                                : $coupon['discount'];
                        }
                        $finalTotal = max(0, $total - $discountAmount);
                    @endphp

                    <div class="card xsf-summary">
                        <div class="card-body">
                            <h2 class="xsf-summary__title">{{ 'Cart Totals' }}</h2>

                            <div class="xsf-summary__row">
                                <span>{{ 'Subtotal' }}</span>
                                <span>{{ $currency->symbol }}{{ number_format($total, 2) }}</span>
                            </div>

                            @if ($coupon)
                                <div class="xsf-summary__row">
                                    <span>{{ 'Discount' }} ({{ $coupon['code'] }})</span>
                                    <span class="d-flex align-items-center gap-2">
                                        &minus;{{ $currency->symbol }}{{ number_format($discountAmount, 2) }}
                                        <form id="removeCouponForm" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm remove-coupon xsf-summary__remove" aria-label="Remove coupon">&times;</button>
                                        </form>
                                    </span>
                                </div>
                            @endif

                            <div class="xsf-summary__row xsf-summary__row--total">
                                <span>{{ 'Total' }}</span>
                                <span>{{ $currency->symbol }}{{ number_format($finalTotal, 2) }}</span>
                            </div>

                            @if (empty($cart))
                                <button type="button" class="btn btn-primary btn-pill w-100 mt-3 proceed-to-checkout" disabled
                                    title="{{ 'Your cart is empty.' }}">
                                    {{ 'Proceed to Checkout' }}
                                </button>
                            @else
                                <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-pill w-100 mt-3 proceed-to-checkout">{{ 'Proceed to Checkout' }}</a>
                            @endif
                        </div>
                    </div>

                    <div class="card xsf-summary mt-4">
                        <div class="card-body">
                            <h2 class="xsf-summary__title">{{ 'Have a Coupon?' }}</h2>
                            <form id="applyCouponForm" class="coupon-box">
                                @csrf
                                <div class="mb-3">
                                    <input type="text" name="code" id="coupon_code" placeholder="{{ 'Enter coupon code' }}" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-dark btn-pill w-100">{{ 'Apply Coupon' }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('.update-cart').click(function (e) {
                e.preventDefault();
                let cartData = [];
                $('tbody tr').each(function () {
                    let productId = $(this).find('input[type="number"]').data('id');
                    let quantity = $(this).find('input[type="number"]').val();
                    if (productId !== undefined) {
                        cartData.push({ product_id: productId, quantity: quantity });
                    }
                });
                $.ajax({
                    url: "{{ route('cart.update') }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}", cart: cartData },
                    success: function (response) { if (response.success) { location.reload(); } }
                });
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll('.remove-from-cart').forEach(button => {
                button.addEventListener('click', function () {
                    let productId = this.dataset.id;
                    fetch("{{ route('cart.remove') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                        body: JSON.stringify({ product_id: productId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        toastr.success(data.message);
                        location.reload();
                    });
                });
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("applyCouponForm")?.addEventListener("submit", function (e) {
                e.preventDefault();
                let code = document.getElementById("coupon_code").value;
                fetch("{{ route('cart.applyCoupon') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value },
                    body: JSON.stringify({ code: code })
                })
                .then(response => response.json())
                .then(data => {
                    toastr.success(data.message, "Applied");
                    setTimeout(() => { if (data.success) location.reload(); }, 1000);
                });
            });

            document.getElementById("removeCouponForm")?.addEventListener("submit", function (e) {
                e.preventDefault();
                fetch("{{ route('cart.removeCoupon') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value }
                })
                .then(response => response.json())
                .then(data => {
                    toastr.success(data.message, "Removed");
                    setTimeout(() => { if (data.success) location.reload(); }, 1000);
                });
            });
        });
    </script>
@endsection
