@props([
    'title' => 'Scan to Pay with KHQR',
])

<!-- ABA PayWay KHQR Modal Component -->
<div class="modal fade" id="paywayQRModal" tabindex="-1" aria-labelledby="paywayQRModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden khqr-modal-content">
            <!-- Modal Header / Brand Banner -->
            <div class="khqr-header text-white p-3 text-center position-relative">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                    <span class="khqr-badge">KHQR</span>
                    <span class="fw-bold fs-6">ABA PayWay</span>
                </div>
                <p class="small mb-0 opacity-75" id="khqr-currency-label">{{ __('KHQR: US Dollar') }}</p>
                <button type="button" class="btn-close btn-close-white position-absolute top-50 end-0 translate-middle-y me-3" id="payway-modal-close-btn" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 text-center">
                <!-- Amount Display -->
                <div class="mb-3">
                    <div class="text-muted small text-uppercase fw-semibold tracking-wide">{{ __('Amount to Pay') }}</div>
                    <div class="display-6 fw-bold text-dark d-flex align-items-center justify-content-center gap-1">
                        <span id="khqr-currency-symbol" class="fs-4 text-secondary">$</span>
                        <span id="khqr-amount-display">0.00</span>
                    </div>
                </div>

                <!-- QR Code Card -->
                <div class="khqr-qr-box p-3 mx-auto mb-3 rounded-3 position-relative d-flex align-items-center justify-content-center">
                    <div id="khqr-loading-skeleton" class="spinner-border text-primary my-4" role="status">
                        <span class="visually-hidden">{{ __('Loading QR...') }}</span>
                    </div>
                    <img id="payway-qr-image" src="" alt="KHQR Payment Code" class="img-fluid rounded d-none" style="max-width: 220px; max-height: 220px;" />
                </div>

                <p class="small text-secondary mb-3">
                    <i class="fa-solid fa-camera-retro me-1"></i>
                    {{ __('Scan with any mobile banking app supporting KHQR (Bakong, ABA, Wing, etc.)') }}
                </p>

                <!-- Mobile Deep Link (hidden by default, shown on mobile) -->
                <div id="payway-deeplink-container" class="d-none mb-3">
                    <a id="payway-deeplink-btn" href="#" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                        {{ __('Open in ABA Mobile') }}
                    </a>
                    <div class="d-flex justify-content-center gap-2 mt-2">
                        <a id="payway-app-store-btn" href="#" class="small text-muted text-decoration-none" target="_blank" rel="noopener">App Store</a>
                        <span class="text-muted small">&bull;</span>
                        <a id="payway-play-store-btn" href="#" class="small text-muted text-decoration-none" target="_blank" rel="noopener">Google Play</a>
                    </div>
                </div>

                <!-- Polling Indicator -->
                <div class="d-flex align-items-center justify-content-center gap-2 text-muted small py-1">
                    <div class="spinner-grow spinner-grow-sm text-success" role="status"></div>
                    <span>{{ __('Waiting for payment confirmation...') }}</span>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 pt-0 pb-3 justify-content-center">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4" id="payway-cancel-btn">
                    {{ __('Cancel Payment') }}
                </button>
            </div>
        </div>
    </div>
</div>
