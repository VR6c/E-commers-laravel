# ADR-0005: Mobile Product Suggestion & Instant Search API

## Status
Accepted

## Context
Mobile application interfaces require fast, low-latency (<100ms) instant feedback for two core catalog experiences:
1. **Live Search Autocomplete / Typeahead**: As users type keywords into the search box, the client displays matching product suggestions, related keywords, and direct category filters.
2. **Contextual & Personalized Suggestions**: Dynamic product suggestion carousels on the mobile Home Screen ("Suggested For You") and Product Detail Screen ("You May Also Like" / Related Products).

The existing `/api/products` endpoint returns full variant trees, attribute mappings, and full pagination metadata, resulting in large response payloads (~15KB–30KB) that cause stutter and excess memory consumption during continuous keystroke queries.

## Decision
Implement specialized, lightweight suggestion endpoints in `App\Http\Controllers\Api\ProductController`:
- `GET /api/products/suggestions`: Supports live search autocomplete (`?q=...`), category-scoped suggestions (`?category_id=...`), and feed recommendations (`?type=recommended` / `?type=trending`).
- `GET /api/products/{slug}/suggestions`: Contextual related suggestions based on product category, brand affinity, and rating score.

```
┌─────────────────────────────────────────────────────────────┐
│                       Mobile Client                         │
│  (Search Autocomplete / Home Carousels / Product Detail)    │
└──────────────────────────────┬──────────────────────────────┘
                               │
            ┌──────────────────┴──────────────────┐
            │ GET /api/products/suggestions?q=... │
            │ GET /api/products/{slug}/suggestion │
            ▼                                     ▼
┌─────────────────────────────────────────────────────────────┐
│             ProductController::suggestions / related        │
│    - Active Scope (`status = 1`)                            │
│    - Eager load `thumbnail`, `primaryVariant`, `ratings`    │
│    - Pluck matching completion keywords & categories        │
│    - Trim payload: id, slug, name, price, thumb, rating     │
└─────────────────────────────────────────────────────────────┘
```

## Consequences

### Positive
- **Drastically Reduced Payload**: Reduces payload size by ~75% compared to the standard catalog list.
- **Fast Execution**: Average response time under 40ms with eager loading and selective column queries, avoiding N+1 bottlenecks.
- **Rich Mobile UX**: Single-request delivery of product cards, keyword pills, and category shortcuts.
- **Non-Breaking**: Extends catalog capabilities without changing existing `/api/products` contracts.

### Negative
- Requires maintaining the suggestion card transformer alongside full product resource definitions.

## Alternatives Considered
- **Client-Side Full Catalog Caching**: Rejected due to high mobile memory footprint, battery usage, and stale catalog issues in multi-vendor stores.
- **Reusing Web Store Endpoint (`/search-suggestions`)**: Rejected because web store search returned blade view fragments and minimal data without prices, ratings, or category pills.
