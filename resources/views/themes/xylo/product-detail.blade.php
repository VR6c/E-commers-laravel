@extends('themes.xylo.layouts.master')

@section('preload')
    @php $mainImg = optional($product->images->first())->image_url ?: optional($product->thumbnail)->image_url; @endphp
    @if ($mainImg)
        <link rel="preload" as="image" href="{{ product_image_url($mainImg) }}" fetchpriority="high">
    @endif
@endsection

@section('content')
    @php
        $currency = activeCurrency();
        $averageRating = round($product->reviews_avg_rating, 1);
        $galleryImages = $product->images->isNotEmpty() ? $product->images : collect();
        $approvedReviews = $product->reviews->filter(fn ($r) => $r->is_approved);
        $ratingBuckets = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($approvedReviews as $r) {
            $k = (int) max(1, min(5, round($r->rating)));
            $ratingBuckets[$k]++;
        }
        $approvedCount = $approvedReviews->count();
        $relatedProducts = \App\Models\Product::with(['thumbnail', 'primaryVariant', 'variants'])
            ->withCount('reviews')->withAvg('reviews', 'rating')
            ->where('status', 1)->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->latest()->limit(4)->get();
    @endphp

    <section class="xsf-section pt-3 pb-0">
        <div class="container">
            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="xsf-breadcrumb xsf-pd__crumbs">
                <a href="{{ url('/') }}">{{ 'Home' }}</a>
                @foreach ($breadcrumbs as $category)
                    <i class="fa fa-angle-right" aria-hidden="true"></i>
                    <a href="{{ url('category/' . $category->slug) }}">{{ $category->name ?? $category->slug }}</a>
                @endforeach
                <i class="fa fa-angle-right" aria-hidden="true"></i>
                <span aria-current="page">{{ $product->name }}</span>
            </nav>
        </div>
    </section>

    <div class="xsf-section pt-3">
        <div class="container">
            <div class="xsf-pd">
                {{-- Gallery --}}
                <div class="xsf-pd__gallery">
                    <div class="xsf-gallery" id="pdGallery" role="region" aria-label="Product images" tabindex="0">
                        @forelse ($galleryImages as $image)
                            <div class="xsf-gallery__slide" id="pdSlide{{ $loop->index }}">
                                <img src="{{ product_image_url($image['image_url']) }}"
                                     alt="{{ $image['name'] ?? $product->name }}"
                                     width="600" height="600"
                                     @if ($loop->first)
                                         fetchpriority="high"
                                         loading="eager"
                                     @else
                                         loading="lazy"
                                         decoding="async"
                                     @endif
                                />
                            </div>
                        @empty
                            <div class="xsf-gallery__slide" id="pdSlide0">
                                <img src="{{ product_image_url(optional($product->thumbnail)->image_url) }}"
                                     alt="{{ $product->name }}"
                                     width="600" height="600"
                                     fetchpriority="high"
                                     loading="eager"
                                />
                            </div>
                        @endforelse
                    </div>

                    @if ($galleryImages->count() > 1)
                        <div class="xsf-gallery__thumbs" id="pdThumbs" role="group" aria-label="Choose product image">
                            @foreach ($galleryImages as $image)
                                <button type="button" class="xsf-gallery__thumb{{ $loop->first ? ' is-active' : '' }}"
                                        data-index="{{ $loop->index }}"
                                        aria-label="Show image {{ $loop->iteration }} of {{ $galleryImages->count() }}"
                                        @if ($loop->first) aria-current="true" @endif>
                                    <img src="{{ product_image_url($image['image_url']) }}" alt="" width="72" height="72" loading="lazy" decoding="async">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Info / buy box --}}
                <div class="xsf-pd__info">
                    <div class="xsf-pd__title-row">
                        <h1 class="xsf-pd__title">{{ $product->name }}</h1>

                        @auth('customer')
                            @php
                                $isFavorite = auth('customer')->user()->wishlistProducts()
                                    ->where('product_id', $product->id)->exists();
                            @endphp
                        @else
                            @php $isFavorite = false; @endphp
                        @endauth

                        <button id="wishlist-heart" type="button" class="xsf-pd__wish" aria-label="{{ 'Wishlist' }}"
                                data-product-id="{{ $product->id }}">
                            <i class="{{ $isFavorite ? 'fa-solid fa-heart text-danger' : 'fa-regular fa-heart text-secondary' }}" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="xsf-pd__rating stars">
                        <x-store.rating :rating="$averageRating" />
                        @if ($product->reviews_count > 0)
                            <a href="#reviews" class="spanstar xsf-pd__rating-link js-open-reviews">{{ $product->reviews_count }} {{ 'customer reviews' }}</a>
                        @else
                            <span class="spanstar">{{ 'No reviews yet' }}</span>
                        @endif
                    </div>

                    <div class="xsf-pd__price-row">
                        <div class="xsf-pd__price">
                            <span id="currency-symbol">{{ $currency->symbol ?? '' }}</span><span id="variant-price">{{ $product->primaryVariant->converted_price ?? 'N/A' }}</span>
                        </div>
                        <span id="product-stock" class="xsf-pd__stock {{ $inStock ? 'is-in' : 'is-out text-danger' }}" role="status">
                            {{ $inStock ? 'IN STOCK' : 'OUT OF STOCK' }}
                        </span>
                    </div>

                    @if ($product->short_description)
                        <p class="xsf-pd__short">{{ $product->short_description }}</p>
                    @endif

                    <div id="product-attributes" class="product-options xsf-pd__attributes">
                        @php $groupedAttributes = $product->attributeValues->groupBy(fn ($item) => $item->attribute->id); @endphp

                        @foreach ($groupedAttributes as $attributeId => $values)
                            @php $attrName = strtolower($values->first()->attribute->name); @endphp
                            <fieldset class="attribute-options xsf-pd__attribute">
                                <legend class="xsf-pd__attribute-label">
                                    {{ ucfirst($attrName) }}
                                    <span class="xsf-pd__attribute-selected js-attr-selected" data-attr="{{ $attributeId }}"></span>
                                </legend>
                                <div class="{{ $attrName }}-wrapper xsf-pd__attribute-options">
                                    @foreach ($values as $index => $value)
                                        @php $inputId = $attrName . '-' . $index; @endphp
                                        <input type="radio" name="attribute_{{ $attributeId }}" id="{{ $inputId }}"
                                            value="{{ $value->id }}" data-label="{{ $value->translated_value ?? $value->value }}" {{ $index === 0 ? 'checked' : '' }}>
                                        <label for="{{ $inputId }}"
                                            class="{{ $attrName === 'color' ? 'color-circle' : 'size-box' }}"
                                            style="{{ $attrName === 'color' ? 'background-color:' . strtolower($value->value) . ';' : '' }}">
                                            @if ($attrName === 'size'){{ $value->translated_value }}@else<span class="visually-hidden">{{ $value->translated_value ?? $value->value }}</span>@endif
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endforeach
                    </div>

                    <div class="cart-actions xsf-pd__actions">
                        <div class="quantity xsf-pd__qty" role="group" aria-label="Quantity">
                            <button type="button" onclick="changeQty(-1)" aria-label="Decrease quantity">&minus;</button>
                            <input type="text" id="qty" value="1" inputmode="numeric" pattern="[0-9]*" aria-label="Quantity">
                            <button type="button" onclick="changeQty(1)" aria-label="Increase quantity">+</button>
                        </div>
                        <button type="button" class="add-to-cart btn btn-primary"
                            onclick="addToCart({{ $product->id }}, '{{ $product->product_type }}')">
                            <i class="fa fa-shopping-bag me-2" aria-hidden="true"></i>{{ 'Add to Cart' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs: description + reviews --}}
    <div class="xsf-section reviewbox">
        <div class="container">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description"
                        type="button" role="tab" aria-controls="description" aria-selected="true">{{ 'Description' }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews"
                        type="button" role="tab" aria-controls="reviews" aria-selected="false">{{ 'Reviews' }} ({{ $product->reviews_count }})</button>
                </li>
            </ul>

            <div class="tab-content pt-4" id="myTabContent">
                <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab" tabindex="0">
                    <h2 class="visually-hidden">{{ 'Description' }}</h2>
                    <div class="xsf-pd__description">{!! $product->description !!}</div>
                </div>

                <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab" tabindex="0">
                    <h2 class="visually-hidden">{{ 'Customer reviews' }}</h2>
                    <div class="product-detail-customer-review">
                        @if ($approvedCount > 0)
                            <div class="average-rating xsf-pd__summary">
                                <div class="xsf-pd__summary-score">
                                    <span class="rating-score">{{ number_format($product->reviews_avg_rating, 1) }}</span>
                                    <x-store.rating :rating="$product->reviews_avg_rating" />
                                    <span class="xsf-pd__summary-count">{{ $approvedCount }} {{ $approvedCount == 1 ? 'review' : 'reviews' }} &middot; {{ 'average rating' }}</span>
                                </div>
                                <ul class="xsf-pd__bars" aria-label="Rating breakdown">
                                    @foreach ($ratingBuckets as $stars => $n)
                                        <li>
                                            <span class="xsf-pd__bars-label">{{ $stars }} star{{ $stars > 1 ? 's' : '' }}</span>
                                            <span class="xsf-pd__bars-track" aria-hidden="true"><span style="width: {{ round($n / $approvedCount * 100) }}%"></span></span>
                                            <span class="xsf-pd__bars-n">{{ $n }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @auth('customer')
                            @if ($alreadyReviewed)
                                {{-- Customer already left a review --}}
                                <div class="xsf-review-notice xsf-review-notice--done mt-3" role="status">
                                    <i class="fa-solid fa-circle-check me-2" style="color:var(--success-500);" aria-hidden="true"></i>
                                    {{ 'You have already submitted a review for this product.' }}
                                </div>
                            @elseif ($hasPurchased)
                                {{-- Customer bought the product — show the review form --}}
                                <div class="xsf-review-form">
                                    <h3>{{ 'Write a Review' }}</h3>
                                    <form action="{{ route('review.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="rating" id="rating-value" required>

                                        <div id="starWrapper" class="xsf-review-form__stars" role="group" aria-label="Your rating">
                                            @for ($s = 1; $s <= 5; $s++)
                                                <button type="button" class="star" data-value="{{ $s }}" aria-label="{{ $s }} star{{ $s > 1 ? 's' : '' }}" style="color:var(--neutral-300);">&#9733;</button>
                                            @endfor
                                        </div>
                                        <div id="star-error" class="text-danger small mt-1 d-none" role="alert">Please select a rating before submitting.</div>

                                        <div class="mb-3 mt-3">
                                            <label class="form-label" for="review-text">{{ 'Your Review (Optional)' }}</label>
                                            <textarea name="review" id="review-text" class="form-control" rows="3"></textarea>
                                        </div>

                                        <button class="btn btn-primary">{{ 'Submit Review' }}</button>
                                    </form>
                                </div>
                            @else
                                {{-- Logged in but has not purchased this product --}}
                                <div class="xsf-review-notice xsf-review-notice--locked mt-3">
                                    <i class="fa-solid fa-lock me-2" style="color:var(--warning-500);" aria-hidden="true"></i>
                                    {{ 'You must purchase this product before reviewing it.' }}
                                </div>
                            @endif
                        @else
                            {{-- Guest: prompt to log in --}}
                            <p class="mt-3">{{ 'Please' }} <a href="{{ route('customer.login') }}">{{ 'log in' }}</a> {{ 'to submit a review.' }}</p>
                        @endauth

                        @if ($product->reviews->isEmpty())
                            <div class="xsf-pd__empty">
                                <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
                                <p>{{ 'No reviews yet. Be the first to review this product!' }}</p>
                            </div>
                        @else
                            <ul class="xsf-review-list">
                                @foreach ($product->reviews as $review)
                                    @if ($review->is_approved)
                                        <li class="xsf-review">
                                            <div class="review-customer-info">
                                                <img src="{{ $review->customer->profile_image ? (\Illuminate\Support\Str::startsWith($review->customer->profile_image, ['http://', 'https://']) ? $review->customer->profile_image : asset('storage/' . $review->customer->profile_image)) : 'https://ui-avatars.com/api/?name=' . urlencode($review->customer->name) . '&background=0D8ABC&color=fff&size=70' }}"
                                                    alt="" width="40" height="40" loading="lazy" class="review-customer-avatar" />
                                                <strong class="review-name">{{ ucwords($review->customer->name) }}</strong>
                                            </div>
                                            <div class="review-rating">
                                                <x-store.rating :rating="$review->rating" />
                                                <span class="review-time">
                                                    @php
                                                        $created_at = \Carbon\Carbon::parse($review->created_at);
                                                        $diffInDays = (int) $created_at->diffInDays(\Carbon\Carbon::now());
                                                    @endphp
                                                    {{ $diffInDays }} {{ $diffInDays == 1 ? 'day' : 'days' }} {{ 'ago' }}
                                                </span>
                                            </div>
                                            @if ($review->review)
                                                <p>{{ $review->review }}</p>
                                            @else
                                                <p>{{ 'No written review.' }}</p>
                                            @endif
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Related products --}}
    @if ($relatedProducts->isNotEmpty())
        <section class="xsf-section xsf-pd-related" aria-labelledby="related-title">
            <div class="container">
                <h2 id="related-title" class="xsf-pd-related__title">{{ 'You may also like' }}</h2>
                <div class="xsf-pd-related__grid">
                    @foreach ($relatedProducts as $related)
                        <div class="xsf-pd-related__item">
                            <x-store.product-card :product="$related" :currency="$currency" :wishlist-ids="[]" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Mobile buy bar: sits above the bottom tab bar, proxies clicks to the main button --}}
    <div class="xsf-sticky-cta is-visible" id="stickyCta" role="region" aria-label="Quick add to bag">
        <div class="xsf-sticky-cta__info">
            <span class="xsf-sticky-cta__name">{{ $product->name }}</span>
            <span class="xsf-sticky-cta__price">{{ $currency->symbol ?? '' }}<span id="stickyCtaPrice">{{ $product->primaryVariant->converted_price ?? 'N/A' }}</span></span>
        </div>
        <button type="button" class="btn btn-primary xsf-sticky-cta__btn" id="stickyCtaBtn" @unless ($inStock) disabled @endunless>
            <i class="fa fa-shopping-bag me-2" aria-hidden="true"></i>{{ 'Add to Cart' }}
        </button>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('#wishlist-heart').on('click', function () {
                var button = $(this);
                var icon = button.find('i');
                var productId = {{ $product->id }};
                $.ajax({
                    url: '{{ route('customer.wishlist.toggle') }}',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', product_id: productId },
                    success: function (response) {
                        if (response.status === 'added') {
                            icon.removeClass('fa-regular text-secondary').addClass('fa-solid text-danger');
                            toastr.success(response.message || 'Added to favorites');
                        } else if (response.status === 'removed') {
                            icon.removeClass('fa-solid text-danger').addClass('fa-regular text-secondary');
                            toastr.info(response.message || 'Removed from favorites');
                        }
                        // Update header badge
                        var badge = $('#wishlist-count');
                        if (badge.length && response.count !== undefined) {
                            badge.text(response.count);
                            badge.toggleClass('d-none', response.count === 0);
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 401) {
                            window.location.href = '/customer/login';
                        } else {
                            toastr.error('Something went wrong.');
                        }
                    }
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const stars = document.querySelectorAll('#starWrapper .star');
            const ratingInput = document.getElementById('rating-value');
            const starError = document.getElementById('star-error');
            const reviewForm = ratingInput ? ratingInput.closest('form') : null;
            if (!ratingInput) return;

            function paintStars(upTo) {
                stars.forEach(s => {
                    s.style.color = parseInt(s.dataset.value) <= upTo ? 'var(--warning-500)' : 'var(--neutral-300)';
                });
            }

            stars.forEach(star => {
                // Hover: highlight up to hovered star
                star.addEventListener('mouseover', function () {
                    paintStars(parseInt(this.dataset.value));
                });

                // Mouse out: restore to currently selected rating
                star.addEventListener('mouseout', function () {
                    paintStars(parseInt(ratingInput.value) || 0);
                });

                // Click: lock the selection
                star.addEventListener('click', function () {
                    const val = parseInt(this.dataset.value);
                    ratingInput.value = val;
                    paintStars(val);
                    if (starError) starError.classList.add('d-none');
                });
            });

            // Validate: rating must be selected before submit
            if (reviewForm) {
                reviewForm.addEventListener('submit', function (e) {
                    if (!ratingInput.value) {
                        e.preventDefault();
                        if (starError) starError.classList.remove('d-none');
                        document.getElementById('starWrapper').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            }
        });
    </script>

    <script>
        @if (Session::has('success'))
            toastr.success("{{ session('success') }}");
        @endif
        @if (Session::has('error'))
            toastr.error("{{ session('error') }}");
        @endif

        // Gallery: native scroll-snap track + thumbnail buttons
        (function () {
            const track = document.getElementById('pdGallery');
            const thumbs = document.querySelectorAll('#pdThumbs .xsf-gallery__thumb');
            if (!track || !thumbs.length) return;
            const slides = track.querySelectorAll('.xsf-gallery__slide');
            function setActive(i) {
                thumbs.forEach((t, k) => {
                    t.classList.toggle('is-active', k === i);
                    if (k === i) t.setAttribute('aria-current', 'true'); else t.removeAttribute('aria-current');
                });
                const strip = document.getElementById('pdThumbs');
                const t = thumbs[i];
                if (strip && t) {
                    const vertical = strip.scrollHeight > strip.clientHeight + 1 && strip.scrollWidth <= strip.clientWidth + 1;
                    if (vertical) strip.scrollTo({ top: t.offsetTop - strip.clientHeight / 2 + t.offsetHeight / 2, behavior: 'smooth' });
                    else strip.scrollTo({ left: t.offsetLeft - strip.clientWidth / 2 + t.offsetWidth / 2, behavior: 'smooth' });
                }
            }
            thumbs.forEach((t, i) => t.addEventListener('click', () => {
                const slide = slides[i];
                if (slide) track.scrollTo({ left: slide.offsetLeft - track.offsetLeft, behavior: 'smooth' });
                setActive(i);
            }));
            let raf = null;
            track.addEventListener('scroll', () => {
                if (raf) return;
                raf = requestAnimationFrame(() => {
                    raf = null;
                    const w = track.clientWidth || 1;
                    setActive(Math.min(slides.length - 1, Math.round(track.scrollLeft / w)));
                });
            }, { passive: true });
            track.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowRight') { track.scrollBy({ left: track.clientWidth, behavior: 'smooth' }); e.preventDefault(); }
                if (e.key === 'ArrowLeft') { track.scrollBy({ left: -track.clientWidth, behavior: 'smooth' }); e.preventDefault(); }
            });
        })();

        // "N customer reviews" link opens the Reviews tab
        document.addEventListener('click', function (e) {
            const a = e.target.closest('.js-open-reviews');
            if (!a) return;
            e.preventDefault();
            const tab = document.getElementById('reviews-tab');
            if (tab) { tab.click(); tab.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        });
    </script>

    <script>
        const variantMap = @json($variantMap);

        $(document).ready(function () {
            const productId = {{ $product->id }};

            function getSelectedAttributeValueIds() {
                let selected = [];
                $('#product-attributes input[type="radio"]:checked').each(function () {
                    selected.push(parseInt($(this).val()));
                });
                return selected.sort((a, b) => a - b);
            }

            function findMatchingVariantId(selectedAttrIds) {
                for (const variant of variantMap) {
                    const variantAttrIds = variant.attributes.slice().sort((a, b) => a - b);
                    if (JSON.stringify(variantAttrIds) === JSON.stringify(selectedAttrIds)) {
                        return variant.id;
                    }
                }
                return null;
            }

            function showSelectedLabels() {
                $('#product-attributes input[type="radio"]:checked').each(function () {
                    const name = this.name.replace('attribute_', '');
                    $('.js-attr-selected[data-attr="' + name + '"]').text(this.dataset.label || '');
                });
            }

            $('#product-attributes input[type="radio"]').on('change', function () {
                showSelectedLabels();
                const selectedAttrIds = getSelectedAttributeValueIds();
                const variantId = findMatchingVariantId(selectedAttrIds);
                $('.xsf-pd__actions .add-to-cart, #stickyCtaBtn').prop('disabled', !variantId);
                if (!variantId) { toastr.warning('Selected variant not available.'); return; }

                $.ajax({
                    url: '/get-variant-price',
                    type: 'GET',
                    data: { variant_id: variantId, product_id: productId },
                    success: function (response) {
                        if (response.success) {
                            $('#variant-price').text(response.price);
                            $('#product-stock').text(response.stock);
                            $('#currency-symbol').text(response.currency_symbol);
                            $('#product-stock').toggleClass('text-danger is-out', !!response.is_out_of_stock);
                            $('#product-stock').toggleClass('is-in', !response.is_out_of_stock);
                        } else {
                            console.log('Unable to fetch variant price.');
                        }
                    },
                    error: function () { toastr.error('Something went wrong. Please try again.'); }
                });
            });

            // Pre-select a combination that has a variant, so the default choice can be added to cart.
            if (variantMap.length && !findMatchingVariantId(getSelectedAttributeValueIds())) {
                variantMap[0].attributes.forEach(function (id) {
                    $('#product-attributes input[type="radio"][value="' + id + '"]').prop('checked', true);
                });
            }

            $('#product-attributes input[type="radio"]:checked').trigger('change');
        });
    </script>

    <script>
        function changeQty(amount) {
            let qtyInput = document.getElementById("qty");
            let currentQty = parseInt(qtyInput.value);
            let newQty = currentQty + amount;
            if (newQty < 1 || isNaN(newQty)) newQty = 1;
            qtyInput.value = newQty;
        }

        function addToCart(productId, product_type) {
            const quantity = parseInt(document.getElementById("qty").value);
            const attributeInputs = document.querySelectorAll('#product-attributes input[type="radio"]:checked');
            let selectedAttributes = [];
            attributeInputs.forEach(input => { selectedAttributes.push(parseInt(input.value)); });

            fetch("{{ route('cart.add') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity,
                    attribute_value_ids: selectedAttributes,
                    product_type: product_type
                })
            })
            .then(response => response.json())
            .then(data => {
                toastr.success(data.message);
                updateCartCount(data.cart);
            })
            .catch(error => console.error("Error:", error));
        }

        function updateCartCount(cart) {
            let totalCount = Object.values(cart || {}).reduce((sum, item) => sum + item.quantity, 0);
            const el = document.getElementById("cart-count");
            if (el) { el.textContent = totalCount; el.classList.toggle('d-none', totalCount === 0); }
        }

        // Mobile buy bar: mirrors price, proxies clicks to the main button.
        (function () {
            const mainBtn = document.querySelector('.xsf-pd__actions .add-to-cart');
            const barBtn = document.getElementById('stickyCtaBtn');
            if (!mainBtn || !barBtn) return;
            barBtn.addEventListener('click', () => mainBtn.click());

            const price = document.getElementById('variant-price');
            const barPrice = document.getElementById('stickyCtaPrice');
            if (price && barPrice) {
                new MutationObserver(() => { barPrice.textContent = price.textContent; })
                    .observe(price, { childList: true, characterData: true, subtree: true });
            }
        })();
    </script>
@endsection
