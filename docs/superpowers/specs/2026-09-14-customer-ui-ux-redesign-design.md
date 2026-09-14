# Design Specification: Customer Storefront UI/UX Redesign & Architectural Refactoring

- **Date:** 2026-09-14
- **Status:** Approved by User
- **Scope:** Phase 1 — Core Storefront Architecture, Layouts, Homepage, Reusable Blade Components, Unified Frontend JavaScript, and Cart Query Refactoring.

---

## 1. Executive Summary & Goals

The goal of this initiative is to redesign the customer storefront of the e-commerce platform into a **Modern Minimalist & Premium Tech** shopping experience while eliminating technical debt, inline script/style pollution, code duplication, and database N+1 performance bottlenecks.

### Key Objectives
1. **Elevate Storefront UI/UX**: Deliver a crisp, modern aesthetic featuring soft slate surfaces, vibrant indigo accents, refined typography, tactile cards with smooth hover elevation, and responsive glassmorphic navigation.
2. **Encapsulate UI into Blade Components**: Replace loose `@include` partials with reusable Laravel Blade components under `resources/views/components/store/` (`<x-store.product-card>`, `<x-store.category-card>`, `<x-store.feature-card>`, `<x-store.rating>`, `<x-store.badge>`).
3. **Eliminate All Inline Scripts & Styles**: Consolidate scattered JavaScript (add to cart, wishlist toggle, search autocomplete, sticky header, button particles/ripples) into a centralized, modular `storefront.js` bundled via Vite, using document-level event delegation.
4. **Fix Database N+1 Performance Issues**: Pre-hydrate cart items, variant details, and attributes in `CartController::viewCart()` rather than querying models directly in the `cart.blade.php` view.
5. **Optimize Fonts & Assets**: Standardize typography exclusively on **Plus Jakarta Sans**, dropping unnecessary font families (`Exo 2`, `Noto Sans Khmer`, `Poppins`) to eliminate render-blocking stylesheet bloat.

---

## 2. Design System & Visual Specification

### A. Color Palette & Surface Tokens
| Token | Hex Value | Role / Usage |
| :--- | :--- | :--- |
| `primary` | `#6366f1` | Brand accent, primary buttons, active highlights |
| `primary-hover` | `#4f46e5` | Button hover state, interactive links |
| `primary-subtle` | `#eef2ff` | Badge backgrounds, icon pill highlights |
| `surface-bg` | `#f8fafc` | Clean neutral page canvas background |
| `surface-card` | `#ffffff` | Elevated card surfaces, search panels, header |
| `border-subtle` | `#f1f5f9` | Inner dividers, soft outlines |
| `border-default` | `#e2e8f0` | Card borders, input outlines |
| `text-primary` | `#0f172a` | Main headings, product titles, bold prices |
| `text-secondary`| `#475569` | Body text, descriptions, navigation links |
| `text-muted` | `#94a3b8` | Placeholders, review counts, timestamps |
| `accent-sale` | `#f43f5e` | Sale badges, wishlist active hearts, discounts |
| `accent-stock` | `#10b981` | In-stock indicators, positive toast messages |
| `accent-rating` | `#f59e0b` | Star reviews, highlighted notifications |

### B. Typography
- **Primary Typeface**: `Plus Jakarta Sans`, sans-serif.
- Weights loaded: `400` (Regular), `500` (Medium), `600` (Semi-bold), `700` (Bold), `800` (Extra-bold).
- Headings: `font-weight: 700` or `800`, letter-spacing `-0.02em`.
- Product Titles: `font-weight: 600`, line-clamp `2` for consistent card heights.

### C. Shadows & Micro-interactions
- **Card Ambient Shadow**: `box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.04);`
- **Card Hover Elevation**: `transform: translateY(-4px); box-shadow: 0 12px 24px -4px rgba(15, 23, 42, 0.08), 0 4px 8px -2px rgba(15, 23, 42, 0.03);`
- **Sticky Header Glassmorphism**: `backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); background: rgba(255, 255, 255, 0.92); border-bottom: 1px solid rgba(226, 232, 240, 0.8);`
- **Image Hover Scale**: `transform: scale(1.05); transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);`

---

## 3. Blade Component Architecture (`resources/views/components/store/`)

### 1. `<x-store.product-card :product="$product" :currency="$currency" :wishlist-ids="$wishlistIds" />`
- **Location**: `resources/views/components/store/product-card.blade.php`
- **Features**:
  - Image container with fixed aspect ratio (`1/1`), lazy loading, and fallback placeholder.
  - Interactive top bar with badge pill (Sale / New / % Off) and quick-action buttons (Wishlist heart toggle).
  - Star rating using `<x-store.rating>` with customer review counts.
  - Title with link to `route('product.show', $product->slug)`.
  - Price display: crossed-out original price (when discounted) + bold active price.
  - Quick "Add to Bag" button with `.js-add-to-cart` trigger class and loading state.

### 2. `<x-store.category-card :category="$category" />`
- **Location**: `resources/views/components/store/category-card.blade.php`
- **Features**:
  - Tactile rounded card with subtle hover scale and glowing border.
  - Optimized category thumbnail with fallback.
  - Clean title and subtle item count or explore chevron.

### 3. `<x-store.feature-card :icon="$icon" :title="$title" :description="$description" />`
- **Location**: `resources/views/components/store/feature-card.blade.php`
- **Features**:
  - Replaces hardcoded ImgBB images in "Why Choose Us" with modern SVG/FontAwesome icons in gradient pill wrappers.
  - Responsive flex card with bold title and supporting description.

### 4. `<x-store.rating :rating="$rating" :count="$count" />`
- **Location**: `resources/views/components/store/rating.blade.php`
- **Features**:
  - Standardized calculation for full, half, and empty stars.
  - Accessible `aria-label` with formatted review count.

### 5. `<x-store.badge :type="$type" :text="$text" />`
- **Location**: `resources/views/components/store/badge.blade.php`
- **Features**:
  - Standardized micro-badges for `sale`, `new`, `trending`, and `discount`.

---

## 4. Frontend JavaScript Architecture (`resources/views/themes/xylo/js/`)

Create a centralized module: `resources/views/themes/xylo/js/storefront.js`, imported by `app.js`.

### Core Capabilities:
1. **Document-Level Event Delegation for Cart**:
   - Selector: `.js-add-to-cart`
   - Disables button, renders inline spinner, sends CSRF `POST` to `/cart/add`.
   - On success: fires Toastr notification, updates `#cart-count` badge, removes spinner.
   - On error: displays Toastr error.
2. **Document-Level Event Delegation for Wishlist**:
   - Selector: `.js-wishlist-toggle`
   - Sends CSRF `POST` to `/customer/wishlist/toggle`.
   - On 401 unauthenticated: redirects cleanly to `/customer/login`.
   - On success: toggles heart icon (`fa-regular` <-> `fa-solid`), updates `#wishlist-count` badge, shows toast message.
3. **Live Search Autocomplete**:
   - Selector: `#search-input`
   - 300ms debounce before fetching `/search-suggestions?q=...`.
   - Renders cleanly styled dropdown with product thumbnail, title, price, and category.
   - Handles keyboard navigation (Arrow Down/Up/Enter) and click-outside dismissal.
4. **Header Scroll Elevation**:
   - Passive scroll listener on window, toggling `.is-scrolled` class on `#site-header` past 20px threshold.
5. **Clean Slider Initializations**:
   - `initHeroSlider()`: banner slider with fade transitions and custom progress dots.
   - `initCategorySlider()`: smooth category carousel with responsive breakpoints.
   - `initProductSlider()`: trending products carousel with custom arrow buttons.

---

## 5. Performance & Database Query Refactoring

### A. Fix N+1 in `CartController::viewCart()`
- **File**: `app/Http/Controllers/Store/CartController.php`
- **Change**: Hydrate cart session data in the controller:
  - Extract all `product_id`, `variant_id`, and `attribute_value_ids`.
  - Batch query `Product::with('thumbnail')->whereIn('id', $productIds)`.
  - Batch query `ProductVariant::with('images')->whereIn('id', $variantIds)`.
  - Batch query `AttributeValue::with('attribute')->whereIn('id', $allAttributeIds)`.
  - Pass an enriched `$cartItems` collection to `themes.xylo.cart`.
  - Remove all inline `\App\Models\...::find()` calls from `cart.blade.php`.

### B. Add `avatar_url` Accessor to Customer Model
- **File**: `app/Models/Customer.php`
- **Change**: Add accessor `getAvatarUrlAttribute()` to cleanly resolve uploaded avatar or fallback to UI-Avatars without duplicating ternary logic in Blade templates.

---

## 6. Target File Modifications & Additions

| Action | File Path | Purpose |
| :--- | :--- | :--- |
| **NEW** | `resources/views/components/store/product-card.blade.php` | Reusable product card Blade component |
| **NEW** | `resources/views/components/store/category-card.blade.php` | Reusable category card Blade component |
| **NEW** | `resources/views/components/store/feature-card.blade.php` | Reusable why choose us feature card |
| **NEW** | `resources/views/components/store/rating.blade.php` | Star rating component |
| **NEW** | `resources/views/components/store/badge.blade.php` | Status badge component |
| **NEW** | `resources/views/themes/xylo/js/storefront.js` | Centralized storefront JavaScript module |
| **MODIFY** | `resources/views/themes/xylo/layouts/master.blade.php` | Clean `<head>`, remove inline styles and scripts |
| **MODIFY** | `resources/views/themes/xylo/layouts/header.blade.php` | Modern glassmorphic header, remove 128 lines of JS |
| **MODIFY** | `resources/views/themes/xylo/layouts/footer.blade.php` | Clean modern footer, improved newsletter form |
| **MODIFY** | `resources/views/themes/xylo/home.blade.php` | Clean markup using `<x-store...>` components, zero scripts |
| **MODIFY** | `resources/views/themes/xylo/sass/app.scss` | Import refactored styling modules |
| **MODIFY** | `resources/views/themes/xylo/sass/_shell.scss` | Header, topbar, navigation, mobile drawer styles |
| **MODIFY** | `resources/views/themes/xylo/sass/_home.scss` | Hero, categories, features section styles |
| **MODIFY** | `resources/views/themes/xylo/sass/_products.scss` | Product card, badges, rating styles |
| **MODIFY** | `resources/views/themes/xylo/js/app.js` | Import `storefront.js` into Vite bundle |
| **MODIFY** | `app/Http/Controllers/Store/CartController.php` | Hydrate cart items and eliminate N+1 queries |
| **MODIFY** | `resources/views/themes/xylo/cart.blade.php` | Consume hydrated cart items, clean template |
| **MODIFY** | `app/Models/Customer.php` | Add `avatar_url` accessor |

---

## 7. Verification & Testing Plan

1. **Automated Unit & Feature Tests**:
   - Run `php artisan test --filter=Storefront` / existing test suite to ensure checkout, store pages, and cart endpoints remain fully functional.
2. **Asset Build Verification**:
   - Run `npm run build` to verify Vite builds CSS and JS without errors.
3. **Interactive Verification**:
   - Verify homepage loads with modern typography and styling.
   - Verify hero slider, category carousel, and trending products slider operate cleanly.
   - Test "Add to Bag" on a product card: button shows loading state, Toastr fires, header cart badge increments.
   - Test Wishlist toggle: updates heart icon, header wishlist badge increments, handles unauthorized redirect.
   - Test live search autocomplete: typing yields suggestions cleanly with images and prices.
   - Verify cart page displays pre-hydrated items without executing database queries in Blade.
