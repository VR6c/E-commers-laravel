# Design Specification: DataTables UI Loading Improvement (Hybrid Skeleton Shimmer + Frosted Glass)

**Author:** Antigravity  
**Date:** 2026-09-14  
**Scope:** Global (Vendor Panel & Admin Panel)  
**Status:** In Review  

---

## 1. Executive Summary & Motivation

In data-heavy management dashboards like the Vendor and Admin portals, data tables are the primary surface for user interactions. Currently, during server-side Ajax requests (initial load, sorting, search filtering, and pagination), DataTables renders a plain, barebones white rectangle with a generic spinner and the raw text `"Loading..."` floating in the middle of a completely blank table canvas.

This proposal upgrades the table loading experience into an ultra-modern, high-polish **Hybrid Loading System**:
1. **Initial Page Load**: Renders realistic **Skeleton Shimmer Placeholders** (animated pulsing placeholder rows tailored to column types like badges, text, date chips, status pills, and action icons) so the interface feels instantaneous and structurally stable.
2. **AJAX Filtering, Search & Pagination**: Deploys a **Frosted Glass Floating Loader** with an elegant dual-ring gradient spinner and animated typography, accompanied by a **Top Glowing Progress Bar** and smooth underlying row dimming (`opacity: 0.38; filter: blur(0.4px)`).
3. **Zero Boilerplate**: Designed to work globally across all 30+ tables in the application automatically via jQuery DataTables event hooks, requiring no repetitive markup in individual Blade templates.

---

## 2. Visual & Architectural Design

### 2.1 Component Breakdown

```
+-------------------------------------------------------------------------+
| [Top Toolbar: Show 10 | Search orders...]                                |
+-------------------------------------------------------------------------+
| === Top Glowing Animated Progress Bar (Visible during AJAX requests) == |
| ID        CUSTOMER          ORDER DATE       STATUS       PRICE   ACTION|
+-------------------------------------------------------------------------+
|                                                                         |
|  [ Skeleton Row 1: [Badge] [Avatar + Name] [Date] [Pill] [Price] [Btn] ]|
|  [ Skeleton Row 2: [Badge] [Avatar + Name] [Date] [Pill] [Price] [Btn] ]|  <- Initial Load
|  [ Skeleton Row 3: [Badge] [Avatar + Name] [Date] [Pill] [Price] [Btn] ]|
|  [ Skeleton Row 4: [Badge] [Avatar + Name] [Date] [Pill] [Price] [Btn] ]|
|  [ Skeleton Row 5: [Badge] [Avatar + Name] [Date] [Pill] [Price] [Btn] ]|
|                                                                         |
|                     +-------------------------------+                   |
|                     | (O)  Updating data...         |                   |  <- AJAX Filter/Page
|                     +-------------------------------+                   |     (Frosted Glass Card)
|                                                                         |
+-------------------------------------------------------------------------+
| [Footer: Showing 1-10 of 25 | Pagination: < 1 2 3 >]                    |
+-------------------------------------------------------------------------+
```

### 2.2 Visual Styles & Micro-Animations

#### A. Skeleton Shimmer Rows
- **Background**: Soft gradient wave `linear-gradient(90deg, #f1f5f9 0%, #e2e8f0 40%, #f8fafc 60%, #f1f5f9 100%)`.
- **Animation**: `dtShimmerWave 1.8s ease-in-out infinite`.
- **Column-Adaptive Variations**:
  - `ID`: Narrow rounded badge shape (`50px x 20px`, `border-radius: 6px`).
  - `Customer/Name`: Avatar circle (`28px x 28px`) + multi-width text bar.
  - `Date`: Date badge (`90px x 16px`).
  - `Status`: Rounded pill (`80px x 24px`, `border-radius: 9999px`).
  - `Price`: Currency badge (`65px x 18px`).
  - `Action`: Mini button squares (`30px x 30px`, `border-radius: 8px`).

#### B. Top Glowing Linear Progress Bar
- Mounted on `.dataTables_wrapper::before` / `.dt-container::before`.
- Height: `3px`, with a gradient `linear-gradient(90deg, #6366f1, #a855f7, #3b82f6)`.
- When `.dt-is-processing` is active, it smoothly scales in (`transform: scaleX(1)`) and pulses continuously with `animation: dtProgressPulse 1.6s ease-in-out infinite alternate`.

#### C. Frosted Glass Floating Loader Card
- **Backdrop**: Floating pill with `background: rgba(255, 255, 255, 0.92); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(226, 232, 240, 0.85); box-shadow: 0 12px 32px -4px rgba(15, 23, 42, 0.12); border-radius: 9999px; padding: 10px 22px;`.
- **Dual-Ring Spinner**:
  - Outer track: `#e0e7ff`.
  - Outer gradient active spinner: `#6366f1` to `#a855f7` rotating at `0.75s` ease-in-out.
  - Inner core: Pulsing radial gradient dot.
- **Typography**: Inter / system font, `font-weight: 600; font-size: 0.8125rem; color: #334155;`, followed by 3 staggered animated dots.

#### D. Non-Jarring Row Dimming
- When filtering or paginating existing data, rows do not vanish abruptly; they fade to `opacity: 0.38` and `filter: blur(0.4px)` with a smooth `0.22s` transition while the floating loader and top bar display.

---

## 3. Implementation Details

### 3.1 Global Script: `public/js/admin-datatables.js`
A dedicated lightweight JavaScript module:
1. **Configures DataTables Defaults**: Sets `$.fn.dataTable.defaults.language.processing` to the modern `.dt-loader-card` markup.
2. **Auto-Injects Skeletons**:
   - Listens to `preXhr.dt` and table initialization.
   - If `tbody tr` is empty or only has the default single cell, it counts columns in `thead th` and renders 5 skeleton rows.
3. **Manages Processing State Classes**:
   - Listens to `processing.dt` to toggle `.dt-is-processing` on the table container.
4. **Smooth Entry on Draw**:
   - On `draw.dt`, removes skeleton rows and adds a staggered `fadeIn` animation on actual data rows.

### 3.2 Stylesheets
1. **`public/css/vendor-panel.css`**:
   - Add `.dt-skeleton-row`, `.dt-skeleton-bar`, `.dt-loader-card`, `.dt-spinner-ring`, `.dataTables_wrapper.dt-is-processing`, and keyframes.
2. **`resources/sass/components/_tables.scss`**:
   - Add the identical SCSS rules so the Admin panel inherits the exact same premium treatment.

### 3.3 Layout Master Inclusions
- `resources/views/vendor/layouts/master.blade.php`: Link `admin-datatables.js`.
- `resources/views/admin/layouts/admin.blade.php`: Link `admin-datatables.js`.

---

## 4. Verification & Testing

1. **Vendor Portal Testing**:
   - Visit `/vendor/orders` (the screen in the user's screenshot).
   - Verify initial load displays smooth skeleton shimmer placeholder rows.
   - Verify typing in the search bar triggers the top glowing progress bar, frosted glass badge, and row dimming.
   - Verify clicking pagination buttons smoothly transitions without abrupt layout collapse.
   - Check `/vendor/products` and `/vendor/reviews` to verify universal application.
2. **Admin Portal Testing**:
   - Visit `/admin/orders`, `/admin/products`, `/admin/categories`.
   - Verify matching aesthetic and flawless responsiveness.
3. **Edge Cases**:
   - Empty search results ("No matching orders found") properly clear skeletons and show empty state.
   - Fast connection (< 100ms): Animations do not flicker or create visual artifacts.
