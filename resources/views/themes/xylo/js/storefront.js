/**
 * Storefront Core JavaScript
 * Unified event delegation, cart, wishlist, live search, and slider initialization.
 */

const getCsrfToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
};

export const updateCartCount = (cart) => {
    let count = 0;
    if (typeof cart === 'number') {
        count = cart;
    } else if (cart && typeof cart === 'object') {
        count = Object.values(cart).reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
    }
    const badge = document.getElementById('cart-count');
    if (badge) {
        badge.textContent = count;
        badge.classList.toggle('d-none', count <= 0);
    }
};

export const updateWishlistCount = (count) => {
    const badge = document.getElementById('wishlist-count');
    if (badge && count !== undefined) {
        badge.textContent = count;
        badge.classList.toggle('d-none', count <= 0);
    }
};

/**
 * Initialize document-level event delegation for Add to Cart
 */
function initCartEvents() {
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-add-to-cart');
        if (!btn || btn.disabled) return;

        e.preventDefault();
        const productId = btn.getAttribute('data-product-id');
        if (!productId) return;

        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>';

        try {
            const response = await fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ product_id: productId, quantity: 1 }),
            });

            const data = await response.json();

            if (response.ok) {
                if (window.toastr) {
                    window.toastr.success(data.message || 'Item added to bag!');
                }
                updateCartCount(data.cart);
                
                // Quick success state
                btn.innerHTML = '<i class="fa-solid fa-check text-success" aria-hidden="true"></i>';
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }, 1200);
            } else {
                throw new Error(data.message || 'Failed to add item to bag');
            }
        } catch (err) {
            console.error('Cart Error:', err);
            if (window.toastr) {
                window.toastr.error(err.message || 'Something went wrong. Please try again.');
            }
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    });
}

/**
 * Initialize document-level event delegation for Wishlist toggle
 */
function initWishlistEvents() {
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-wishlist-toggle');
        if (!btn || btn.disabled) return;

        e.preventDefault();
        const productId = btn.getAttribute('data-product-id');
        if (!productId) return;

        btn.disabled = true;
        const icon = btn.querySelector('i');

        try {
            const response = await fetch('/customer/wishlist/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ product_id: productId }),
            });

            if (response.status === 401) {
                window.location.href = '/customer/login';
                return;
            }

            const data = await response.json();

            if (response.ok) {
                const isAdded = data.status === 'added';
                btn.classList.toggle('is-active', isAdded);

                if (icon) {
                    icon.className = isAdded ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
                    icon.style.transform = 'scale(1.35)';
                    setTimeout(() => { icon.style.transform = ''; }, 200);
                }

                if (window.toastr) {
                    if (isAdded) {
                        window.toastr.success(data.message || 'Saved to your wishlist');
                    } else {
                        window.toastr.info(data.message || 'Removed from wishlist');
                    }
                }

                updateWishlistCount(data.count);

                // Handle wishlist view removal
                const wishlistItem = btn.closest('.xsf-wishlist-item');
                if (wishlistItem && !isAdded) {
                    wishlistItem.style.opacity = '0';
                    wishlistItem.style.transform = 'scale(0.95)';
                    setTimeout(() => wishlistItem.remove(), 250);
                }
            } else {
                throw new Error(data.message || 'Error updating wishlist');
            }
        } catch (err) {
            console.error('Wishlist Error:', err);
            if (window.toastr) {
                window.toastr.error('Could not update wishlist.');
            }
        } finally {
            btn.disabled = false;
        }
    });
}

/**
 * Initialize live search autocomplete
 */
function initLiveSearch() {
    const searchInput = document.getElementById('search-input');
    const suggestionsBox = document.getElementById('search-suggestions');
    if (!searchInput || !suggestionsBox) return;

    let debounceTimer;

    searchInput.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        const query = e.target.value.trim();

        if (query.length < 2) {
            suggestionsBox.innerHTML = '';
            suggestionsBox.classList.add('d-none');
            return;
        }

        debounceTimer = setTimeout(async () => {
            try {
                const res = await fetch(`/search-suggestions?q=${encodeURIComponent(query)}`);
                const products = await res.json();

                suggestionsBox.innerHTML = '';
                if (Array.isArray(products) && products.length > 0) {
                    products.forEach(product => {
                        const item = document.createElement('a');
                        item.href = `/product/${product.slug}`;
                        item.className = 'xsf-search-suggestion';
                        item.innerHTML = `
                            <img src="${product.thumbnail || 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=80'}"
                                 alt="${product.name}"
                                 class="xsf-search-suggestion__img"
                                 width="40" height="40">
                            <div class="xsf-search-suggestion__info">
                                <span class="xsf-search-suggestion__title">${product.name}</span>
                            </div>
                        `;
                        suggestionsBox.appendChild(item);
                    });
                    suggestionsBox.classList.remove('d-none');
                } else {
                    suggestionsBox.innerHTML = '<div class="xsf-search-suggestion--empty">No products found</div>';
                    suggestionsBox.classList.remove('d-none');
                }
            } catch (err) {
                console.error('Search error:', err);
                suggestionsBox.classList.add('d-none');
            }
        }, 250);
    });

    // Close on click outside
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.classList.add('d-none');
        }
    });

    // Global Cmd+K / Ctrl+K shortcut to focus search
    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            suggestionsBox.classList.add('d-none');
            searchInput.blur();
        }
    });
}

/**
 * Sticky header scroll state
 */
function initHeaderScroll() {
    const header = document.getElementById('site-header');
    if (!header) return;

    const handleScroll = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 15);
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
}

/**
 * Initialize sliders safely
 */
function initSliders() {
    if (typeof window.$ === 'undefined' || !window.$.fn?.slick) {
        return;
    }

    const $ = window.$;

    // Hero banner slider
    const $heroSlider = $('.banner-slider');
    if ($heroSlider.length > 0 && !$heroSlider.hasClass('slick-initialized')) {
        $heroSlider.slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 5000,
            fade: true,
            speed: 600,
            cssEase: 'cubic-bezier(0.25, 1, 0.5, 1)',
            dots: true,
            arrows: false,
        });
        $heroSlider.addClass('is-initialized');
    }

    // Categories slider
    const $categorySlider = $('.category-slider');
    if ($categorySlider.length > 0 && !$categorySlider.hasClass('slick-initialized')) {
        $categorySlider.slick({
            slidesToShow: 6,
            slidesToScroll: 2,
            autoplay: true,
            autoplaySpeed: 4000,
            dots: false,
            arrows: true,
            prevArrow: '<button class="slick-prev" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>',
            nextArrow: '<button class="slick-next" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>',
            responsive: [
                { breakpoint: 1200, settings: { slidesToShow: 5 } },
                { breakpoint: 992, settings: { slidesToShow: 4 } },
                { breakpoint: 768, settings: { slidesToShow: 3 } },
                { breakpoint: 480, settings: { slidesToShow: 2 } },
            ]
        });
        $categorySlider.addClass('is-initialized');
    }

    // Trending products slider
    const $productSlider = $('.product-slider');
    if ($productSlider.length > 0 && !$productSlider.hasClass('slick-initialized')) {
        $productSlider.slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            infinite: true,
            autoplay: true,
            autoplaySpeed: 3500,
            arrows: true,
            prevArrow: '.custom-arrows .prev',
            nextArrow: '.custom-arrows .next',
            responsive: [
                { breakpoint: 1200, settings: { slidesToShow: 3 } },
                { breakpoint: 992, settings: { slidesToShow: 2 } },
                { breakpoint: 576, settings: { slidesToShow: 1 } },
            ]
        });
        $productSlider.addClass('is-initialized');
    }
}

// Global Toastr defaults
export function configureToastr() {
    if (typeof window.toastr !== 'undefined') {
        window.toastr.options = {
            closeButton: true,
            progressBar: true,
            newestOnTop: true,
            positionClass: 'toast-top-right',
            preventDuplicates: false,
            timeOut: 2500,
            extendedTimeOut: 1000,
            showDuration: 250,
            hideDuration: 250,
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut',
        };
    }
}
configureToastr();

// Bootstrap initialization on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    configureToastr();
    initCartEvents();
    initWishlistEvents();
    initLiveSearch();
    initHeaderScroll();

    // Check sliders immediately and retry if jQuery/Slick loaded deferred
    initSliders();
    setTimeout(initSliders, 300);
    setTimeout(initSliders, 800);
});

// Also make helpers globally accessible if needed by legacy templates
window.addToCart = (productId) => {
    const fakeBtn = document.querySelector(`.js-add-to-cart[data-product-id="${productId}"]`);
    if (fakeBtn) {
        fakeBtn.click();
    }
};
window.updateCartCount = updateCartCount;
