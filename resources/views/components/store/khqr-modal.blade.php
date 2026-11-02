@props([
    'title' => 'Scan to Pay with KHQR',
])

<!-- ABA PayWay KHQR Modal Component -->
<div class="modal fade" id="paywayQRModal" tabindex="-1" aria-labelledby="paywayQRModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content khqr-modal-content">
            <!-- KHQR card header -->
            <div class="khqr-header">
                <span class="khqr-badge">KHQR</span>
                <h2 class="khqr-title" id="paywayQRModalLabel">ABA PayWay</h2>
                <button type="button" class="btn-close btn-close-white khqr-close" id="payway-modal-close-btn" aria-label="{{ __('Close') }}" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body khqr-body">
                <!-- Amount -->
                <div class="khqr-amount">
                    <span class="khqr-amount__label">{{ __('Amount to Pay') }}</span>
                    <div class="khqr-amount__value">
                        <span id="khqr-currency-symbol" class="khqr-amount__symbol">$</span>
                        <span id="khqr-amount-display">0.00</span>
                    </div>
                    <span class="khqr-amount__currency" id="khqr-currency-label">{{ __('KHQR: US Dollar') }}</span>
                </div>

                <!-- Ticket divider -->
                <div class="khqr-divider" aria-hidden="true"></div>

                <!-- QR Code -->
                <div class="khqr-qr-box">
                    <span class="khqr-qr-box__corner khqr-qr-box__corner--tl" aria-hidden="true"></span>
                    <span class="khqr-qr-box__corner khqr-qr-box__corner--tr" aria-hidden="true"></span>
                    <span class="khqr-qr-box__corner khqr-qr-box__corner--bl" aria-hidden="true"></span>
                    <span class="khqr-qr-box__corner khqr-qr-box__corner--br" aria-hidden="true"></span>
                    <div id="khqr-loading-skeleton" class="spinner-border khqr-spinner" role="status">
                        <span class="visually-hidden">{{ __('Loading QR...') }}</span>
                    </div>
                    <img id="payway-qr-image" src="" alt="{{ __('KHQR payment code') }}" class="img-fluid d-none" width="220" height="220" />
                </div>

                <p class="khqr-hint">
                    <i class="fa-solid fa-camera-retro" aria-hidden="true"></i>
                    {{ __('Scan with any mobile banking app supporting KHQR (Bakong, ABA, Wing, etc.)') }}
                </p>

                <!-- Mobile deep link (shown on mobile only) -->
                <div id="payway-deeplink-container" class="khqr-deeplink d-none">
                    <a id="payway-deeplink-btn" href="#" class="btn btn-primary w-100 fw-semibold d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i>
                        {{ __('Open in ABA Mobile') }}
                    </a>
                    <div class="khqr-deeplink__stores">
                        <a id="payway-app-store-btn" href="#" target="_blank" rel="noopener">App Store</a>
                        <a id="payway-play-store-btn" href="#" target="_blank" rel="noopener">Google Play</a>
                    </div>
                </div>

                <!-- Polling status -->
                <div class="khqr-status" role="status" aria-live="polite">
                    <span class="khqr-status__dot" aria-hidden="true"></span>
                    <span>{{ __('Waiting for payment confirmation...') }}</span>
                </div>
            </div>

            <div class="khqr-footer">
                <button type="button" class="btn btn-link khqr-cancel" id="payway-cancel-btn">
                    {{ __('Cancel Payment') }}
                </button>
            </div>
        </div>
    </div>
</div>
