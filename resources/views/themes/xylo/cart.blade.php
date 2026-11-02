@extends('themes.xylo.layouts.master')

@section('content')
    @php $currency = activeCurrency(); @endphp

    <section class="xsf-section">
        <div class="container">
            <nav aria-label="breadcrumb" class="xsf-breadcrumb">
                <a href="{{ url('/') }}">{{ 'Home' }}</a>
                <i class="fa fa-angle-right" aria-hidden="true"></i>
                <span aria-current="page">{{ 'Cart' }}</span>
            </nav>

            <div class="xsf-listing-head">
                <h1 class="xsf-listing-head__title">{{ 'Cart' }}</h1>
            </div>

            @php $total = 0; @endphp
            @if (empty($cart))
                <div class="xsf-empty cart-page">
                    <i class="fa fa-shopping-bag xsf-empty__icon" aria-hidden="true"></i>
                    <h2>{{ 'Your cart is empty.' }}</h2>
                    <p>{{ 'Browse the shop and add something you like.' }}</p>
                    <a href="{{ route('shop.index') }}" class="btn btn-primary mt-3">{{ 'Shop now' }}</a>
                </div>
            @else
                <div class="xsf-cart-layout cart-page">
                    <div class="xsf-cart-layout__main">
                        @php $cartItems = $hydratedCart ?? []; @endphp
                        <ul class="xsf-cart-list" aria-label="{{ 'Cart items' }}">
                            @foreach ($cartItems as $key => $item)
                                <li class="xsf-cart-item">
                                    <div class="xsf-cart-item__img">
                                        <img src="{{ $item['image_url'] }}" alt="" class="cart-product-img" loading="lazy" width="88" height="88">
                                    </div>
                                    <div class="xsf-cart-item__main">
                                        <p class="cart-product-name">{{ $item['display_name'] }}</p>
                                        <div class="cart-product-attributes">
                                            @if (!empty($item['sizes']))
                                                <div class="cart-attribute-sizes">
                                                    @foreach ($item['sizes'] as $size)<span class="size-box">{{ $size }}</span>@endforeach
                                                </div>
                                            @endif
                                            @if (!empty($item['colors']))
                                                <div class="cart-attribute-colors">
                                                    @foreach ($item['colors'] as $color)<span class="color-circle {{ strtolower($color) }}" style="background-color: {{ strtolower($color) }};" title="{{ $color }}"></span>@endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="xsf-cart-item__price">
                                        <span class="visually-hidden">{{ 'Price' }}: </span>{{ $currency->symbol }}{{ number_format($item['price'], 2) }}
                                    </p>
                                    <div class="xsf-cart-item__qty">
                                        <div class="xsf-qty" role="group" aria-label="{{ 'Quantity' }}: {{ $item['display_name'] }}">
                                            <button type="button" class="xsf-qty__btn" data-qty-step="-1" aria-label="{{ 'Decrease quantity' }}"><i class="fa fa-minus" aria-hidden="true"></i></button>
                                            <input type="number" inputmode="numeric" aria-label="{{ 'Quantity' }}: {{ $item['display_name'] }}" class="form-control form-control-sm xsf-cart-qty" value="{{ $item['quantity'] }}" min="1" data-id="{{ $key }}">
                                            <button type="button" class="xsf-qty__btn" data-qty-step="1" aria-label="{{ 'Increase quantity' }}"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                        </div>
                                    </div>
                                    <p class="xsf-cart-item__total">
                                        <span class="visually-hidden">{{ 'Subtotal' }}: </span>{{ $currency->symbol }}{{ number_format($item['subtotal'], 2) }}
                                    </p>
                                    <div class="xsf-cart-item__remove">
                                        <button type="button" class="remove-from-cart bnlink" data-id="{{ $key }}"
                                            aria-label="{{ 'Remove' }} {{ $item['display_name'] }}">
                                            <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </li>
                                @php $total += $item['subtotal']; @endphp
                            @endforeach
                        </ul>

                        <div class="xsf-cart-toolbar">
                            <a href="{{ route('xylo.home') }}" class="btn btn-light">{{ 'Continue Shopping' }}</a>
                            <a href="#" role="button" class="btn btn-dark update-cart">{{ 'Update Cart' }}</a>
                        </div>
                    </div>

                    <aside class="xsf-cart-layout__aside" aria-labelledby="cart-totals-title">
                        @php
                            $coupon = $coupon ?? session('cart_coupon');
                            $subtotal = $subtotal ?? $total;
                            $discountAmount = $discountAmount ?? 0;
                            if (!isset($finalTotal)) {
                                if (isset($total) && $total !== $subtotal) {
                                    $finalTotal = $total;
                                } else {
                                    $finalTotal = max(0, $subtotal - $discountAmount);
                                }
                            }
                        @endphp

                        <div class="xsf-summary">
                            <h2 class="xsf-summary__title" id="cart-totals-title">{{ 'Cart Totals' }}</h2>

                            <div class="xsf-summary__row">
                                <span>{{ 'Subtotal' }}</span>
                                <span>{{ $currency->symbol }}{{ number_format($total, 2) }}</span>
                            </div>

                            @if ($coupon)
                                <div class="xsf-summary__row xsf-summary__row--discount">
                                    <span>{{ 'Discount' }} ({{ $coupon['code'] }})</span>
                                    <span class="d-flex align-items-center gap-2">
                                        &minus;{{ $currency->symbol }}{{ number_format($discountAmount, 2) }}
                                        <form id="removeCouponForm" class="m-0">
                                            @csrf
                                            <button type="submit" class="remove-coupon xsf-summary__remove" aria-label="{{ 'Remove coupon' }}">&times;</button>
                                        </form>
                                    </span>
                                </div>
                            @endif

                            <div class="xsf-summary__row xsf-summary__row--total">
                                <span>{{ 'Total' }}</span>
                                <span>{{ $currency->symbol }}{{ number_format($finalTotal, 2) }}</span>
                            </div>

                            <details class="xsf-coupon">
                                <summary>{{ 'Have a Coupon?' }}</summary>
                                <form id="applyCouponForm" class="coupon-box xsf-coupon__form">
                                    @csrf
                                    <label for="coupon_code" class="visually-hidden">{{ 'Enter coupon code' }}</label>
                                    <input type="text" name="code" id="coupon_code" placeholder="{{ 'Enter coupon code' }}" class="form-control" autocomplete="off">
                                    <button type="submit" class="btn btn-dark">{{ 'Apply Coupon' }}</button>
                                </form>
                            </details>

                            <a href="{{ route('checkout.index') }}" class="btn btn-primary xsf-summary__cta proceed-to-checkout">{{ 'Proceed to Checkout' }}</a>
                        </div>
                    </aside>
                </div>
            @endif
        </div>
    </section>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('.update-cart').click(function (e) {
                e.preventDefault();
                let cartData = [];
                $('.xsf-cart-qty').each(function () {
                    let productId = $(this).data('id');
                    let quantity = $(this).val();
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
            document.querySelectorAll('.xsf-qty__btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const input = this.parentElement.querySelector('.xsf-cart-qty');
                    const next = Math.max(1, (parseInt(input.value, 10) || 1) + parseInt(this.dataset.qtyStep, 10));
                    input.value = next;
                });
            });
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
