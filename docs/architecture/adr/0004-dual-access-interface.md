# ADR-0004: Dual Access Layer Architecture (Livewire v3 + Sanctum REST API)

## Status
Accepted

## Context
The platform requires interactive server-rendered web UI for customers and admins, as well as token-authenticated RESTful API endpoints for mobile applications and third-party integrations.

## Decision
Maintain Livewire v3 for web UI components and Laravel Sanctum for RESTful API controllers, with both consuming identical underlying `Services` and `Repositories`.

```
                  ┌───────────────────────────────┐
                  │    Domain Service Layer       │
                  └───────────────┬───────────────┘
                                  │
                  ┌───────────────┴───────────────┐
                  ▼                               ▼
       ┌────────────────────┐          ┌────────────────────┐
       │ Livewire v3 (Web)  │          │ Sanctum REST API   │
       └────────────────────┘          └────────────────────┘
```

## Consequences

### Positive
- Business logic is completely decoupled from presentation logic.
- REST API and Web UI maintain consistent data validation and domain behavior.
- Frontend web app avoids complex JavaScript SPA build steps while providing real-time reactivity.

### Negative
- Schema updates require maintaining both Livewire component views and API Resource transformers.

## Alternatives Considered
- **Full SPA (InertiaJS / Vue / React)**: Rejected to leverage server-driven rendering speed and Livewire's lower setup complexity for multi-vendor backoffices.
