# ADR-0001: Adopt Modular Monolith Architecture

## Status
Accepted

## Context
The TVR E-Commerce platform serves multiple user personas: Storefront Customers, Vendor Shop Owners, and Platform Administrators. As the application grows in complexity, maintaining clean boundaries without incurring microservice operational overhead is critical.

## Decision
We adopt a **Modular Monolith** pattern within Laravel. Application domains are strictly isolated into dedicated namespace directories:
- `App\Services\Admin` & `App\Repositories\Admin`
- `App\Services\Vendor` & `App\Repositories\Vendor`
- `App\Services\Store`
- `App\Services\PaymentGateway`
- `App\Services\Shared` & `App\Repositories\Shared`

## Consequences

### Positive
- Unified deployment pipeline via standard Laravel deployment tooling.
- Zero inter-service network latency.
- Reusable Eloquent ORM models and database transaction integrity across modules.

### Negative
- Require code review discipline to prevent domain controllers from cross-importing unrelated domain services directly.

### Neutral
- Shared MySQL database schema requires clear database migration naming conventions.

## Alternatives Considered
- **Microservices**: Rejected due to small team size, deployment complexity, and distributed transaction requirements.
- **Single-layer Monolith**: Rejected due to high risk of tight coupling between Admin and Storefront logic.
