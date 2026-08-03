<footer class="xsf-footer">
    <div class="container">
        <div class="row gy-5 xsf-footer__top">

            {{-- Brand + blurb + social --}}
            <div class="col-12 col-lg-4">
                @php $siteLogo = \App\Models\SiteSetting::first()?->logo ?? null; @endphp
                @if($siteLogo)
                    <img src="{{ \Illuminate\Support\Str::startsWith($siteLogo, ['http://','https://']) ? $siteLogo : asset('storage/' . $siteLogo) }}"
                         alt="{{ config('app.name') }} logo"
                         class="xsf-footer__logo">
                @else
                    <span class="xsf-footer__wordmark">{{ config('app.name') }}</span>
                @endif
                <p class="xsf-footer__blurb">{{ 'Quality products, delivered with care.' }}</p>

                {{-- Social --}}
                <div class="xsf-footer__social">
                    <a href="#" aria-label="Facebook" title="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                    <a href="#" aria-label="X / Twitter" title="X / Twitter"><i class="fab fa-x-twitter" aria-hidden="true"></i></a>
                    <a href="#" aria-label="Instagram" title="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                    <a href="#" aria-label="TikTok" title="TikTok"><i class="fab fa-tiktok" aria-hidden="true"></i></a>
                </div>
            </div>

            {{-- Account links --}}
            <div class="col-6 col-lg-2">
                <h2 class="xsf-footer__heading">{{ 'My Account' }}</h2>
                <ul class="xsf-footer__links">
                    <li><a href="{{ route('customer.profile.edit') }}">{{ 'My Account' }}</a></li>
                    <li><a href="{{ route('customer.wishlist.index') }}">{{ 'Wishlist' }}</a></li>
                    <li><a href="{{ route('shop.index') }}">{{ 'Shop' }}</a></li>
                </ul>
            </div>

            {{-- Pages links --}}
            <div class="col-6 col-lg-2">
                <h2 class="xsf-footer__heading">{{ 'Pages' }}</h2>
                <ul class="xsf-footer__links">
                    <li><a href="#">{{ 'Privacy Policy' }}</a></li>
                    <li><a href="#">{{ 'Terms of Service' }}</a></li>
                    <li><a href="#">{{ 'About Us' }}</a></li>
                </ul>
            </div>

            {{-- Newsletter --}}
            <div class="col-12 col-lg-4">
                <h2 class="xsf-footer__heading">{{ 'Follow Us' }}</h2>
                <p class="xsf-footer__blurb mb-4">
                    {{ 'Stay in the loop with our latest drops and exclusive offers.' }}
                </p>
                <div class="xsf-footer__newsletter">
                    <div class="xsf-footer__newsletter-form">
                        <input type="email"
                               class="xsf-footer__newsletter-input"
                               placeholder="your@email.com"
                               aria-label="Newsletter email">
                        <button type="button" class="xsf-footer__newsletter-btn" aria-label="Subscribe to newsletter">
                            <i class="fas fa-paper-plane" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom bar --}}
    <div class="xsf-footer__bottom">
        <div class="container">
            <div class="xsf-footer__bottom-row">
                <span>{{ '© ' . date('Y') . ' All rights reserved.' }}</span>
                <div class="xsf-footer__payment-icons">
                    <i class="fab fa-cc-visa" title="Visa"></i>
                    <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                    <i class="fab fa-cc-paypal" title="PayPal"></i>
                </div>
                <span>{{ 'Powered by Laravel' }}</span>
            </div>
        </div>
    </div>
</footer>
