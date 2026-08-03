/**
 * Storefront Main Interactive Logic
 * Modernized using ES2023+ standards, arrow functions, optional chaining, 
 * IntersectionObserver API, and safe DOM manipulation.
 *
 * @module storefront-main
 */

/**
 * Initializes hero banner mouse movement parallax effect.
 *
 * @returns {void}
 */
export function initBannerParallax() {
    const bannerArea = document.querySelector('.banner-area');
    if (!bannerArea) return;

    let targetX = 0;
    let targetY = 0;
    let currentX = 0;
    let currentY = 0;
    const ease = 0.1;
    const moveFactor = 0.05;

    bannerArea.addEventListener('mousemove', (e) => {
        const activeSlide = bannerArea.querySelector('.slick-active');
        if (!activeSlide) return;

        const container = activeSlide.querySelector('.rightimg-banner1 img, .rightimg-banner2 img');
        if (!container) return;

        const rect = container.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        targetX = (x - centerX) * moveFactor;
        targetY = (y - centerY) * moveFactor;
    });

    const animate = () => {
        currentX += (targetX - currentX) * ease;
        currentY += (targetY - currentY) * ease;

        const activeSlide = bannerArea.querySelector('.slick-active');
        const shoeImage = activeSlide?.querySelector('.rightimg-banner img');
        if (shoeImage) {
            shoeImage.style.transform = `translate(${currentX}px, ${currentY}px)`;
        }
        requestAnimationFrame(animate);
    };

    animate();
}

/**
 * Initializes custom blog slider controls.
 *
 * @returns {void}
 */
export function initBlogSlider() {
    if (typeof window.$ === 'undefined') return;

    const $ = window.$;
    const slides = $('.blog-img');
    const titles = $('.blog-title');
    const totalSlides = slides.length;
    if (totalSlides === 0) return;

    let currentIndex = 0;

    const updateSlide = () => {
        slides.stop(true, true).fadeOut(300);
        slides.removeClass('active').addClass('deactive');
        slides.eq(currentIndex).stop(true, true).fadeIn(300).removeClass('deactive').addClass('active');

        titles.removeClass('active').addClass('deactive');
        titles.eq(currentIndex).removeClass('deactive').addClass('active');
    };

    $('.nnext').on('click', () => {
        currentIndex = (currentIndex + 1) % totalSlides;
        updateSlide();
    });

    $('.pprev').on('click', () => {
        currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
        updateSlide();
    });

    titles.on('click', function () {
        const clickedIndex = titles.index($(this));
        if (clickedIndex !== -1) {
            currentIndex = clickedIndex;
            updateSlide();
        }
    });

    updateSlide();
}

/**
 * Initializes client logo carousel with Slick Slider.
 *
 * @returns {void}
 */
export function initClientSlider() {
    if (typeof window.$ === 'undefined') return;

    const $ = window.$;
    const $clientSlider = $('.client-slider');
    if ($clientSlider.length === 0 || !$.fn?.slick) return;

    $clientSlider.slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        infinite: true,
        autoplay: true,
        autoplaySpeed: 3000,
        dots: false,
        arrows: true,
        prevArrow: $('.prev'),
        nextArrow: $('.next'),
        responsive: [
            {
                breakpoint: 992,
                settings: { slidesToShow: 1 }
            },
            {
                breakpoint: 576,
                settings: { slidesToShow: 1 }
            }
        ]
    });
}

/**
 * Initializes IntersectionObserver scroll animations.
 *
 * @returns {void}
 */
export function initScrollAnimations() {
    const elements = document.querySelectorAll('.animate-on-scroll');
    if (elements.length === 0) return;

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated', 'animate__fadeIn');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.3 }
    );

    elements.forEach((el) => observer.observe(el));
}

// Auto-run when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initBannerParallax();
        initBlogSlider();
        initClientSlider();
        initScrollAnimations();
    });
} else {
    initBannerParallax();
    initBlogSlider();
    initClientSlider();
    initScrollAnimations();
}
