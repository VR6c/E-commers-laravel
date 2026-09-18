@extends('themes.xylo.layouts.master')

/* Place Order button */
#place-order.btn-primary {
    background: linear-gradient(135deg, #5289AD 0%, #243C4C 100%);
    border-color: #5289AD;
    color: #ffffff;
    letter-spacing: 0.02em;
}
#place-order.btn-primary:hover,
#place-order.btn-primary:focus {
    background: linear-gradient(135deg, #3f6f90 0%, #1a2f3d 100%);
    border-color: #3f6f90;
    box-shadow: 0 6px 20px rgba(82, 137, 173, 0.40);
}
#place-order.btn-primary:disabled {
    background: #ACBCBF;
    border-color: #ACBCBF;
}

/* Stripe card element */
#card-element {
    border-color: #ACBCBF !important;
    background: #F4FCFB !important;
}
</style>
@endsection

@section('content')
    @php $currency = activeCurrency(); @endphp

    <section class="xsf-section">
        <div class="container">
            <nav aria-label="breadcrumb" class="xsf-breadcrumb">
                <a href="{{ route('xylo.home') }}">{{ 'Home' }}</a>
                <i class="fa fa-angle-right" aria-hidden="true"></i>
                <a href="{{ route('cart.view') }}">{{ 'Cart' }}</a>
                <i class="fa fa-angle-right" aria-hidden="true"></i>
                <span>{{ 'Checkout' }}</span>
            </nav>

            {{-- Step indicator --}}
            <ol class="xsf-steps">
                <li class="xsf-steps__item is-done"><span class="xsf-steps__num"><i class="fa fa-check"></i></span>{{ 'Cart' }}</li>
                <li class="xsf-steps__item is-active"><span class="xsf-steps__num">2</span>{{ 'Checkout' }}</li>
                <li class="xsf-steps__item"><span class="xsf-steps__num">3</span>{{ 'Complete' }}</li>
            </ol>

            <div class="row g-4">
                <div class="col-lg-7">
                    <form id="checkout-form" method="POST" action="{{ route('checkout.store') }}">
                        @csrf

                        {{-- Shipping --}}
                        <div class="card mb-4 shipping_info">
                            <div class="card-body">
                                <h3 class="cart-heading xsf-summary__title">{{ 'Shipping Information' }}</h3>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="text" name="first_name" class="form-control" placeholder="{{ 'First Name' }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="last_name" class="form-control" placeholder="{{ 'Last Name' }}" required>
                                    </div>
                                    <div class="col-12">
                                        <input type="text" name="address" class="form-control" placeholder="{{ 'Address' }}" required>
                                    </div>

                                    {{-- Suite / Floor (removable) --}}
                                    <div class="col-12" id="suite-row">
                                        <div class="input-group">
                                            <input type="text" name="suite" id="suite-input" class="form-control" placeholder="{{ 'Apartment, suite, etc.' }}">
                                            <button type="button" id="remove-suite-btn" class="btn btn-outline-secondary" title="Remove Suite/Floor">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted">{{ 'Apartment, suite, etc.' }} (optional)</small>
                                    </div>

                                    {{-- Country --}}
                                    <div class="col-md-6">
                                        <input type="hidden" name="country" id="country-value">
                                        <div class="xsf-combobox" id="country-combobox" data-cb-id="country">
                                            <button type="button" class="xsf-combobox__trigger"
                                                    id="country-trigger"
                                                    aria-haspopup="listbox"
                                                    aria-expanded="false"
                                                    aria-controls="country-panel">
                                                <span class="xsf-combobox__label xsf-combobox__label--placeholder"
                                                      id="country-trigger-label">{{ 'Select Country' }}</span>
                                            </button>
                                            <div class="xsf-combobox__panel" id="country-panel" hidden role="listbox">
                                                <div class="xsf-combobox__search-wrap">
                                                    <svg class="xsf-combobox__search-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                                    <input type="text" class="xsf-combobox__search" id="country-search"
                                                           placeholder="{{ 'Search country...' }}"
                                                           autocomplete="off" aria-label="Search country">
                                                </div>
                                                <div class="xsf-combobox__options" id="country-options"></div>
                                                <p class="xsf-combobox__empty" id="country-empty" hidden>No results found</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <input type="text" name="city" class="form-control" placeholder="{{ 'City' }}" required>
                                    </div>

                                    {{-- State (populated dynamically via AJAX) --}}
                                    <div class="col-md-6">
                                        <input type="hidden" name="state" id="state-value">
                                        <div class="xsf-combobox xsf-combobox--disabled" id="state-combobox" data-cb-id="state">
                                            <button type="button" class="xsf-combobox__trigger"
                                                    id="state-trigger"
                                                    aria-haspopup="listbox"
                                                    aria-expanded="false"
                                                    aria-controls="state-panel"
                                                    disabled>
                                                <span class="xsf-combobox__label xsf-combobox__label--placeholder"
                                                      id="state-trigger-label">{{ 'Select State / Province' }}</span>
                                            </button>
                                            <div class="xsf-combobox__panel" id="state-panel" hidden role="listbox">
                                                <div class="xsf-combobox__search-wrap">
                                                    <svg class="xsf-combobox__search-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                                    <input type="text" class="xsf-combobox__search" id="state-search"
                                                           placeholder="{{ 'Search state...' }}"
                                                           autocomplete="off" aria-label="Search state">
                                                </div>
                                                <div class="xsf-combobox__options" id="state-options"></div>
                                                <p class="xsf-combobox__empty" id="state-empty" hidden>No results found</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="use_as_billing" value="1" id="use_as_billing" checked>
                                            <label class="form-check-label" for="use_as_billing">{{ 'Use as billing address' }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Contact --}}
                        <div class="card mb-4 shipping_info">
                            <div class="card-body">
                                <h3 class="cart-heading xsf-summary__title">{{ 'Contact Information' }}</h3>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="email" name="email" class="form-control" placeholder="{{ 'Email Address' }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="phone" class="form-control" placeholder="{{ 'Phone Number' }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Payment --}}
                        <div class="card mb-4 shipping_info">
                            <div class="card-body">
                                <h3 class="cart-heading xsf-summary__title">{{ 'Payment Method' }}</h3>

                                <div class="xsf-gateways">
                                    @foreach ($paymentGateways as $gateway)
                                        <label class="xsf-gateway" for="gateway-{{ $gateway->id }}">
                                            <input type="radio" name="gateway" value="{{ $gateway->code }}" id="gateway-{{ $gateway->id }}" required>
                                            <span class="xsf-gateway__label">{{ $gateway->name }}</span>
                                        </label>

                                        @if ($gateway->code === 'paypal')
                                            <div id="paypal-button-container" class="mt-3" style="display: none;"></div>
                                        @endif
                                        @if ($gateway->code === 'stripe')
                                            <div id="card-element" class="form-control mt-3" style="display: none;"></div>
                                            <div id="card-errors" class="text-danger mt-2"></div>
                                        @endif
                                    @endforeach

                                    <div id="payment-fields"></div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="place-order" class="btn btn-primary btn-lg btn-pill w-100">{{ 'Place Order' }}</button>
                    </form>
                </div>

                <div class="col-lg-5">
                    <div class="card xsf-summary xsf-summary--sticky">
                        <div class="card-body">
                            <h3 class="cart-heading xsf-summary__title">{{ 'Order Summary' }}</h3>

                            <div class="xsf-summary__row">
                                <span>{{ 'Subtotal' }}</span>
                                <span>{{ $currency->symbol }}{{ number_format($subtotal, 2) }}</span>
                            </div>
                            @if ($coupon && $discountAmount > 0)
                                <div class="xsf-summary__row">
                                    <span>{{ 'Discount' }} ({{ $coupon['code'] }})</span>
                                    <span class="text-danger">&minus;{{ $currency->symbol }}{{ number_format($discountAmount, 2) }}</span>
                                </div>
                            @endif
                            <div class="xsf-summary__row">
                                <span>{{ 'Shipping' }}</span>
                                <span class="text-muted"><small>{{ 'Calculated at next step' }}</small></span>
                            </div>
                            <div class="xsf-summary__row xsf-summary__row--total">
                                <span>{{ 'Total' }}</span>
                                <span>{{ $currency->symbol }}{{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ABA PayWay QR Code Payment Modal Component -->
    <x-store.khqr-modal />
@endsection

@section('js')
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency=USD"></script>
<script src="https://js.stripe.com/v3/"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const gatewayRadios = document.querySelectorAll('input[name="gateway"]');
    const paypalContainer = document.getElementById("paypal-button-container");
    const stripeContainer = document.getElementById("card-element");
    const placeOrderBtn = document.getElementById("place-order");

    @if(session('error'))
        toastr.error("{{ session('error') }}");
    @endif
    @if(session('success'))
        toastr.success("{{ session('success') }}");
    @endif

    let stripe = null;
    let card = null;
    const stripePublicKey = "{{ $stripePublicKey ?? '' }}";

    if (stripeContainer && stripePublicKey && typeof Stripe !== "undefined") {
        try {
            stripe = Stripe(stripePublicKey);
            let elements = stripe.elements();
            card = elements.create("card");
            card.mount("#card-element");
        } catch (err) {
            console.warn("Stripe init skipped or failed:", err);
        }
    }

    // Show correct payment fields
    gatewayRadios.forEach(radio => {
        radio.addEventListener("change", function () {
            if (this.value === "paypal") {
                if (paypalContainer) paypalContainer.style.display = "block";
                if (stripeContainer) stripeContainer.style.display = "none";
            } else if (this.value === "stripe") {
                if (stripeContainer) stripeContainer.style.display = "block";
                if (paypalContainer) paypalContainer.style.display = "none";
            } else {
                if (paypalContainer) paypalContainer.style.display = "none";
                if (stripeContainer) stripeContainer.style.display = "none";
            }
        });
    });

    // PayPal integration
    if (typeof paypal !== "undefined") {
        paypal.Buttons({
            createOrder: function (data, actions) {
                return actions.order.create({
                    purchase_units: [{ amount: { value: "{{ number_format($total, 2, '.', '') }}" } }]
                });
            },
            onApprove: function (data, actions) {
                return actions.order.capture().then(function (details) {
                    // Send to backend
                    fetch("{{ route('checkout.store') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            gateway: "paypal",
                            order_id: data.orderID,
                            details: details
                        })
                    }).then(res => res.json()).then(result => {
                        window.location.href = "{{ route('thankyou') }}";
                    });
                });
            }
        }).render("#paypal-button-container");
    }

    // Stripe and general form integration
    const form = document.getElementById("checkout-form");
    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        let selectedGateway = document.querySelector('input[name="gateway"]:checked').value;

        if (selectedGateway === "stripe") {
            if (!stripe || !card) {
                alert("Stripe is not active or initialized.");
                return;
            }
            const {paymentMethod, error} = await stripe.createPaymentMethod({
                type: "card",
                card: card,
            });

            if (error) {
                document.getElementById("card-errors").textContent = error.message;
            } else {
                // Send paymentMethod.id + form data to backend
                let formData = new FormData(form);
                formData.append("payment_method_id", paymentMethod.id);

                fetch("{{ route('checkout.store') }}", {
                    method: "POST",
                    headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"},
                    body: formData
                }).then(res => res.json()).then(result => {
                    window.location.href = "{{ route('thankyou') }}";
                });
            }
        } else if (selectedGateway === "paypal") {
            alert("Please complete payment with PayPal button");
        } else if (selectedGateway === "abapayway") {
            let formData = new FormData(form);
            placeOrderBtn.disabled = true;
            placeOrderBtn.textContent = "Processing...";

            fetch("{{ route('checkout.store') }}", {
                method: "POST",
                headers: {"X-CSRF-TOKEN": "{{ csrf_token() }}"},
                body: formData
            }).then(res => {
                if (!res.ok) {
                    throw new Error("HTTP error " + res.status);
                }
                return res.json();
            }).then(result => {
                if (result.success && result.gateway === 'abapayway') {
                    if (result.response_type === 'html') {
                        // Redirect to hosted page
                        window.location.href = result.redirect_url;
                    } else if (result.response_type === 'json') {
                        // QR Code / Deeplink JSON response
                        const paywayData = result.data;

                        // ── Populate amount & currency ──
                        const amount   = result.amount   ?? paywayData.amount   ?? '0.00';
                        const currency = result.currency ?? paywayData.currency ?? 'USD';
                        const symbol   = currency === 'KHR' ? '៛' : '$';
                        const currencyLabel = currency === 'KHR' ? 'KHQR: Khmer Riel' : 'KHQR: US Dollar';

                        document.getElementById('khqr-amount-display').textContent  = Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        document.getElementById('khqr-currency-symbol').textContent = symbol;
                        document.getElementById('khqr-currency-label').textContent  = currencyLabel;

                        // ── Set QR image (prefer qrImage base64, else build from qrString) ──
                        const qrImg = document.getElementById('payway-qr-image');
                        if (paywayData.qrImage) {
                            qrImg.src = paywayData.qrImage;
                        } else if (paywayData.qrString) {
                            // Render locally via QR API if base64 not provided
                            qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(paywayData.qrString);
                            qrImg.classList.remove('d-none');
                            document.getElementById('khqr-loading-skeleton').classList.add('d-none');
                        }

                        // ── Mobile: show deep-link & store badges ──
                        const isMobile = /Android|iPhone|iPad|iPod|Opera Mini|IEMobile|WPDesktop/i.test(navigator.userAgent);
                        const deepContainer = document.getElementById('payway-deeplink-container');
                        if (isMobile) {
                            if (paywayData.abapay_deeplink) {
                                document.getElementById('payway-deeplink-btn').href = paywayData.abapay_deeplink;
                            }
                            if (paywayData.app_store) {
                                document.getElementById('payway-app-store-btn').href = paywayData.app_store;
                            }
                            if (paywayData.play_store) {
                                document.getElementById('payway-play-store-btn').href = paywayData.play_store;
                            }
                            deepContainer.classList.remove('d-none');
                        } else {
                            deepContainer.classList.add('d-none');
                        }

                        // ── Show modal ──
                        const paywayModal = new bootstrap.Modal(document.getElementById('paywayQRModal'));
                        paywayModal.show();

                        // ── Resolve tran_id from response ──
                        const tranId = result.tran_id ?? paywayData.status?.tran_id ?? paywayData.tran_id ?? '';

                        // ── Poll for payment status every 3 s ──
                        let isPolling = false;
                        let paymentApproved = false;
                        let pollInterval = setInterval(function () {
                            if (!tranId || isPolling || paymentApproved) return;
                            isPolling = true;

                            fetch("/checkout/payway/status/" + encodeURIComponent(tranId))
                                .then(res => res.json())
                                .then(statusResult => {
                                    if (statusResult.success && statusResult.approved && !paymentApproved) {
                                        paymentApproved = true;
                                        clearInterval(pollInterval);
                                        paywayModal.hide();
                                        toastr.success("Payment completed successfully!");
                                        setTimeout(() => {
                                            window.location.href = "{{ route('thankyou') }}";
                                        }, 1200);
                                    }
                                })
                                .catch(err => console.error("Error polling status:", err))
                                .finally(() => {
                                    isPolling = false;
                                });
                        }, 3000);

                        // ── Cancel & Close handlers ──
                        const handleCancelPayway = function () {
                            paymentApproved = true;
                            clearInterval(pollInterval);
                            paywayModal.hide();
                            window.location.href = "{{ route('payway.cancel') }}";
                        };
                        const cancelBtn = document.getElementById('payway-cancel-btn');
                        if (cancelBtn) cancelBtn.addEventListener('click', handleCancelPayway);
                        const closeBtn = document.getElementById('payway-modal-close-btn');
                        if (closeBtn) closeBtn.addEventListener('click', handleCancelPayway);
                    }
                } else {
                    alert(result.message || "An error occurred during checkout processing.");
                    placeOrderBtn.disabled = false;
                    placeOrderBtn.textContent = "Place Order";
                }
            }).catch(err => {
                console.error(err);
                alert("An error occurred. Please check your inputs and try again.");
                placeOrderBtn.disabled = false;
                placeOrderBtn.textContent = "Place Order";
            });
        } else {
            form.submit();
        }
    });
    // ── Suite/Floor remove & restore ──────────────────────────────
    const suiteRow    = document.getElementById('suite-row');
    const suiteInput  = document.getElementById('suite-input');
    const removeSuite = document.getElementById('remove-suite-btn');

    if (removeSuite) {
        removeSuite.addEventListener('click', function () {
            suiteInput.value = '';
            suiteRow.style.display = 'none';
        });
    }

    // ── Country → State dynamic load ──────────────────────────────

    // Country options injected server-side
    const countryOptions = {!! Js::from(collect($countries)->map(fn($c) => ['value' => $c['code'], 'label' => $c['name']])->values()) !!};

    // ── Generic storefront combobox factory ───────────────────────
    function makeCombobox(cfg) {
        // cfg: { wrapperId, triggerId, labelId, searchId, optionsId, emptyId,
        //        valueInputId, options, onSelect }
        const wrap      = document.getElementById(cfg.wrapperId);
        const trigger   = document.getElementById(cfg.triggerId);
        const labelEl   = document.getElementById(cfg.labelId);
        const search    = document.getElementById(cfg.searchId);
        const optionsEl = document.getElementById(cfg.optionsId);
        const emptyEl   = document.getElementById(cfg.emptyId);
        const panel     = document.getElementById(cfg.wrapperId.replace('-combobox', '-panel'));
        const valueInput = document.getElementById(cfg.valueInputId);

        if (!trigger || !panel || !search || !optionsEl) return null;

        // portal
        document.body.appendChild(panel);
        panel.style.position = 'fixed';
        panel.style.zIndex   = '9999';
        panel.hidden         = true;

        let options       = cfg.options || [];
        let selectedValue = valueInput ? valueInput.value : '';
        let isOpen        = false;
        let focusedIndex  = -1;

        function positionPanel() {
            const r = trigger.getBoundingClientRect();
            panel.style.left  = r.left + 'px';
            panel.style.top   = (r.bottom + 4) + 'px';
            panel.style.width = r.width + 'px';
        }

        function getFiltered(q) {
            q = q.trim().toLowerCase();
            return q ? options.filter(o => o.label.toLowerCase().includes(q)) : options;
        }

        function renderOptions(query) {
            const filtered = getFiltered(query || '');
            optionsEl.innerHTML = '';
            focusedIndex = -1;
            filtered.forEach((opt, idx) => {
                const el = document.createElement('div');
                el.className = 'xsf-combobox__option';
                el.setAttribute('role', 'option');
                el.setAttribute('data-value', opt.value);
                if (opt.value === selectedValue) {
                    el.classList.add('xsf-combobox__option--active');
                    focusedIndex = idx;
                }
                el.textContent = opt.label;
                el.addEventListener('mousedown', e => e.preventDefault());
                el.addEventListener('click', () => selectOption(opt));
                optionsEl.appendChild(el);
            });
            if (emptyEl) emptyEl.hidden = filtered.length > 0;
        }

        function selectOption(opt) {
            selectedValue = opt.value;
            if (valueInput) {
                valueInput.value = opt.value;
                valueInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (labelEl) {
                labelEl.textContent = opt.label;
                labelEl.classList.remove('xsf-combobox__label--placeholder');
            }
            trigger.classList.remove('is-invalid');
            closePanel();
            if (cfg.onSelect) cfg.onSelect(opt);
        }

        function openPanel() {
            if (isOpen || trigger.disabled) return;
            isOpen = true;
            positionPanel();
            panel.hidden = false;
            wrap.classList.add('xsf-combobox--open');
            trigger.setAttribute('aria-expanded', 'true');
            search.value = '';
            renderOptions('');
            requestAnimationFrame(() => {
                search.focus();
                const active = optionsEl.querySelector('.xsf-combobox__option--active');
                if (active) active.scrollIntoView({ block: 'nearest' });
            });
            document.addEventListener('click', onOutside, true);
            document.addEventListener('keydown', onKey, true);
            window.addEventListener('scroll', positionPanel, true);
            window.addEventListener('resize', positionPanel);
        }

        function closePanel() {
            if (!isOpen) return;
            isOpen = false;
            panel.hidden = true;
            wrap.classList.remove('xsf-combobox--open');
            trigger.setAttribute('aria-expanded', 'false');
            document.removeEventListener('click', onOutside, true);
            document.removeEventListener('keydown', onKey, true);
            window.removeEventListener('scroll', positionPanel, true);
            window.removeEventListener('resize', positionPanel);
        }

        function onOutside(e) {
            if (!wrap.contains(e.target) && !panel.contains(e.target)) closePanel();
        }

        function onKey(e) {
            const items = optionsEl.querySelectorAll('.xsf-combobox__option');
            const total = items.length;
            switch (e.key) {
                case 'Escape':
                    e.preventDefault(); closePanel(); trigger.focus(); break;
                case 'ArrowDown':
                    e.preventDefault();
                    if (!total) break;
                    focusedIndex = (focusedIndex + 1) % total;
                    highlight(items, focusedIndex); break;
                case 'ArrowUp':
                    e.preventDefault();
                    if (!total) break;
                    focusedIndex = (focusedIndex - 1 + total) % total;
                    highlight(items, focusedIndex); break;
                case 'Enter':
                case ' ':
                    if (document.activeElement === search) break;
                    e.preventDefault();
                    if (focusedIndex >= 0 && items[focusedIndex]) {
                        const val = items[focusedIndex].dataset.value;
                        const found = options.find(o => o.value === val);
                        if (found) selectOption(found);
                    }
                    break;
                case 'Tab': closePanel(); break;
            }
        }

        function highlight(items, idx) {
            items.forEach((el, i) => {
                if (i === idx) { el.classList.add('xsf-combobox__option--active'); el.scrollIntoView({ block: 'nearest' }); }
                else el.classList.remove('xsf-combobox__option--active');
            });
        }

        search.addEventListener('input', () => renderOptions(search.value));
        trigger.addEventListener('click', () => isOpen ? closePanel() : openPanel());
        trigger.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                e.preventDefault(); openPanel();
            }
        });

        // Public API
        return {
            setOptions(newOptions, resetValue) {
                options = newOptions;
                if (resetValue) {
                    selectedValue = '';
                    if (valueInput) valueInput.value = '';
                    if (labelEl) {
                        labelEl.textContent = cfg.placeholder || 'Select…';
                        labelEl.classList.add('xsf-combobox__label--placeholder');
                    }
                }
            },
            setDisabled(val) {
                trigger.disabled = val;
                if (val) { wrap.classList.add('xsf-combobox--disabled'); closePanel(); }
                else     { wrap.classList.remove('xsf-combobox--disabled'); }
            },
            setLoading(val) {
                if (val) {
                    let el = panel.querySelector('.xsf-combobox__loading');
                    if (!el) { el = document.createElement('p'); el.className = 'xsf-combobox__loading'; el.textContent = 'Loading…'; panel.appendChild(el); }
                } else {
                    const el = panel.querySelector('.xsf-combobox__loading');
                    if (el) el.remove();
                }
            }
        };
    }

    // ── Country combobox ───────────────────────────────────────────
    const countryCb = makeCombobox({
        wrapperId:    'country-combobox',
        triggerId:    'country-trigger',
        labelId:      'country-trigger-label',
        searchId:     'country-search',
        optionsId:    'country-options',
        emptyId:      'country-empty',
        valueInputId: 'country-value',
        placeholder:  '{{ __("store.checkout.select_country") }}',
        options:      countryOptions,
        onSelect(opt) {
            // When a country is chosen, fetch and populate states
            stateCb.setDisabled(true);
            stateCb.setOptions([], true);

            fetch('/checkout/states/' + encodeURIComponent(opt.value))
                .then(r => r.json())
                .then(states => {
                    if (!states.length) { stateCb.setDisabled(true); return; }
                    stateCb.setOptions(
                        states.map(s => ({ value: s.code, label: s.name })),
                        true
                    );
                    stateCb.setDisabled(false);
                })
                .catch(err => console.error('Failed to load states:', err));
        }
    });

    // ── State combobox ─────────────────────────────────────────────
    const stateCb = makeCombobox({
        wrapperId:    'state-combobox',
        triggerId:    'state-trigger',
        labelId:      'state-trigger-label',
        searchId:     'state-search',
        optionsId:    'state-options',
        emptyId:      'state-empty',
        valueInputId: 'state-value',
        placeholder:  'Select State/Province',
        options:      [],
    });
});
</script>
@endsection
