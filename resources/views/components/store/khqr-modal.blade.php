@props([
    'title' => 'Scan to Pay with KHQR',
])

<!-- ABA PayWay KHQR Modal Component -->
<div class="modal fade" id="paywayQRModal" tabindex="-1" aria-labelledby="paywayQRModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content khqr-modal-content">
            <!-- Modal Header -->
            <div class="khqr-header">
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <span class="khqr-badge">KHQR</span>
                    <h2 class="fs-6 fw-bold mb-0" id="paywayQRModalLabel">ABA PayWay</h2>
                </div>
                <p class="small text-muted mb-0" id="khqr-currency-label">{{ __('KHQR: US Dollar') }}</p>
                <button type="button" class="btn-close khqr-close" id="payway-modal-close-btn" aria-label="{{ __('Close') }}" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-3 text-center">
                <!-- Amount Display -->
                <div class="mb-3">
                    <div class="text-muted small fw-semibold">{{ __('Amount to Pay') }}</div>
                    <div class="fs-2 fw-bold text-dark d-flex align-items-center justify-content-center gap-1">
                        <span id="khqr-currency-symbol" class="fs-5 text-secondary">$</span>
                        <span id="khqr-amount-display">0.00</span>
                    </div>
                </div>

                <!-- QR Code -->
                <div class="khqr-qr-box p-2 mx-auto mb-3 d-flex align-items-center justify-content-center">
                    <div id="khqr-loading-skeleton" class="spinner-border text-primary my-4" role="status">
                        <span class="visually-hidden">{{ __('Loading QR...') }}</span>
                    </div>
                    <img id="payway-qr-image" src="" alt="{{ __('KHQR payment code') }}" class="img-fluid d-none" width="220" height="220" />
                </div>

                <p class="small text-secondary mb-3">
                    <i class="fa-solid fa-camera-retro me-1" aria-hidden="true"></i>
                    {{ __('Scan with any mobile banking app supporting KHQR (Bakong, ABA, Wing, etc.)') }}
                </p>

                <!-- Mobile Deep Link (hidden by default, shown on mobile) -->
                <div id="payway-deeplink-container" class="d-none mb-3">
                    <a id="payway-deeplink-btn" href="#" class="btn btn-primary w-100 fw-semibold d-flex align-items-center justify-content-center gap-2" style="min-height:44px;">
                        <i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i>
                        {{ __('Open in ABA Mobile') }}
                    </a>
                    <div class="d-flex justify-content-center gap-3 mt-2">
                        <a id="payway-app-store-btn" href="#" class="small text-secondary py-2" target="_blank" rel="noopener">App Store</a>
                        <a id="payway-play-store-btn" href="#" class="small text-secondary py-2" target="_blank" rel="noopener">Google Play</a>
                    </div>
                </div>

                <!-- Polling Indicator -->
                <div class="d-flex align-items-center justify-content-center gap-2 text-muted small py-1" role="status" aria-live="polite">
                    <div class="spinner-grow spinner-grow-sm text-success" aria-hidden="true"></div>
                    <span>{{ __('Waiting for payment confirmation...') }}</span>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-outline-secondary w-100" style="min-height:44px;" id="payway-cancel-btn">
                    {{ __('Cancel Payment') }}
                </button>
            </div>
        </div>
    </div>
</div>
