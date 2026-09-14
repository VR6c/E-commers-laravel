# Design Specification: Modern DataTables UI Loading Redesign

**Date:** 2026-09-14  
**Scope:** Admin Panel & Vendor Panel (Global DataTables & Table Loading UX)  
**Status:** In Review  

---

## 1. Problem Statement
In the existing implementation, server-side DataTables requests (initial page load, search filtering, column sorting, pagination) present an awkward UX:
- The table canvas collapses to an empty white rectangle because `loadingRecords` was set to `&nbsp;` and `.dataTables_empty` was set to `font-size: 0`.
- A solitary floating pill badge stating `"Updating data . . ."` floats in the dead center of this empty void.
- On slow requests or errors, this empty void with the floating pill gives the appearance of a broken application.

---

## 2. Target Design & Architecture

### 2.1 Component Anatomy

```
+-----------------------------------------------------------------------------------------+
| [Show 10  v] entries per page                              Search: [ Search...       ] |
+-----------------------------------------------------------------------------------------+
| === Top Gradient Laser Beam (Indigo -> Violet -> Cyan glow, sweeps during AJAX) ======= |
| ID       IMAGE        NAME                  CATEGORY     PRICE    STOCK   STATUS  ACTION|
+-----------------------------------------------------------------------------------------+
| [#101]   [ Avatar ]   ==================    [ Badge ]    $49.99   [ 24 ]  ( O )   [E] [D]
|                       ==========                                                        |
| [#102]   [ Avatar ]   ==============        [ Badge ]    $120.00  [ 10 ]  ( O )   [E] [D]
|                       =================                                                 |
| [#103]   [ Avatar ]   ====================  [ Badge ]    $15.50   [Out]   (   )   [E] [D]
|                       ========                                                          |
| [#104]   [ Avatar ]   ============          [ Badge ]    $85.00   [ 82 ]  ( O )   [E] [D]
|                       ===============                                                   |
| [#105]   [ Avatar ]   ===================   [ Badge ]    $64.20   [ 15 ]  ( O )   [E] [D]
|                                                                                         |
|                               +------------------------+                                |
|                               | (o) Syncing data...    |  <- (Bottom-center micro-chip) |
|                               +------------------------+                                |
+-----------------------------------------------------------------------------------------+
| Showing 1 to 10 of 25 entries                                Pagination: [ <  1  2  > ] |
+-----------------------------------------------------------------------------------------+
```

### 2.2 Visual Elements & Interactions

1. **Realistic Shimmer Skeletons**:
   - Renders 5 rows of column-adaptive placeholders (ID badge, 40px thumbnail, dual text lines for name, category badge, price, stock counter, toggle switch, action buttons).
   - Animated shimmer wave: `linear-gradient(90deg, #f8fafc 0%, #edf2f7 50%, #f8fafc 100%)`.

2. **Top Glowing Laser Beam**:
   - `height: 2.5px`, positioned seamlessly at the top edge of the table header.
   - Gradient: `#6366f1` (indigo) -> `#8b5cf6` (violet) -> `#06b6d4` (cyan).
   - Subtle glow: `box-shadow: 0 0 10px rgba(99, 102, 241, 0.45)`.

3. **Subsequent AJAX Updates (Search, Filter, Paginate)**:
   - Existing rows stay in place and gently dim to `opacity: 0.48; filter: blur(0.4px)`.
   - Top Laser Beam pulses and animates.
   - Sleek micro-pill (`Syncing data...`) floats gracefully near the bottom-center, non-intrusively.

4. **Error & Timeout Handling**:
   - If an AJAX error occurs (500 or network timeout), the loading state automatically clears and an inline error notice appears with a quick "Retry" button.
