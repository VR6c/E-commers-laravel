# TVR E-Commerce: System Architecture Overview

## Executive Summary
This document outlines the high-level architecture, module organization, data flow, scaling strategy, and non-functional specifications for the **TVR Multi-Vendor & Multi-Lingual Laravel E-Commerce Platform**.

---

## High-Level Architecture Diagram

```mermaid
graph TD
    subgraph Clients ["Client Access Layer"]
        WebClient["Blade Templates + Livewire v3\n(Server-Rendered Reactive UI)"]
        MobileClient["Mobile App / SPA Clients\n(Sanctum Authenticated REST API)"]
    end

    subgraph Infrastructure ["Edge & Web Server Layer"]
        Nginx["Nginx Web Server\n(TLS 1.3, Static Asset Caching, Rate Limiting)"]
    end

    subgraph Monolith ["Laravel Modular Monolith"]
        Router["Routing & Security Middleware\n(Sanctum, CSRF, RBAC)"]

        subgraph CoreDomains ["Domain Isolation Modules"]
            StoreDomain["Storefront Domain\n(Cart, Catalog, Wishlist, Reviews)"]
            VendorDomain["Vendor Domain\n(Shop, Products, Inventory, Payouts)"]
            AdminDomain["Admin Domain\n(Users, Settings, Moderation, Analytics)"]
            PaymentDomain["Payment Adapter Strategy\n(ABA PayWay, Stripe, PayPal)"]
            SharedDomain["Shared Domain\n(Currency, SiteSettings, Banners)"]
        end

        subgraph DataAccess ["Repository Layer"]
            Repositories["Eloquent Models & Repositories"]
        end
    end

    subgraph Persistence ["Data & Cache Layer"]
        PrimaryDB[("Primary Database\n(MySQL 8 / PostgreSQL 15)")]
        RedisCache[("Redis Session & Page Cache")]
        RedisQueue[("Redis Queue Manager\n(Laravel Horizon)")]
    end

    subgraph ExternalServices ["External Integrations"]
        ABAPayWay["ABA PayWay Gateway"]
        Stripe["Stripe Gateway"]
        PayPal["PayPal Gateway"]
        Mailgun["SMTP / Mailgun"]
    end

    WebClient --> Nginx
    MobileClient --> Nginx
    Nginx --> Router
    Router --> CoreDomains
    CoreDomains --> DataAccess
    DataAccess --> PrimaryDB
    CoreDomains --> RedisCache
    CoreDomains --> RedisQueue
    PaymentDomain --> ABAPayWay
    PaymentDomain --> Stripe
    PaymentDomain --> PayPal
    RedisQueue --> Mailgun
```

---

## Domain Architecture Breakdown

The codebase enforces a **Modular Monolith** pattern inside `app/`:

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/         # Admin REST / Resource Controllers
│   │   ├── Api/           # Public & Sanctum API Controllers
│   │   ├── Store/         # Storefront Blade Controllers
│   │   └── Vendor/        # Vendor Management Controllers
│   └── Middleware/        # Auth, Role, Security Middleware
├── Livewire/             # Reactive UI Components
├── Models/               # Eloquent Data Models
├── Repositories/         # Data Access Layer
│   ├── Admin/
│   ├── Shared/
│   └── Vendor/
└── Services/             # Domain Business Logic Layer
    ├── Admin/
    ├── PaymentGateway/    # Strategy Adapters (ABA, Stripe, PayPal)
    ├── Shared/
    ├── Store/
    └── Vendor/
```

---

## Non-Functional Requirements (NFRs)

### Performance
- **API Response Time**: `< 150ms` (p95) for cached catalog endpoints.
- **Livewire Search**: `< 100ms` real-time search filtering.
- **Database Execution**: `< 30ms` indexed queries.

### Availability & Reliability
- **Uptime Target**: `99.95%` availability.
- **RPO (Recovery Point Objective)**: `< 15 minutes` via transaction log backups.
- **RTO (Recovery Time Objective)**: `< 1 hour` via automated database failover.

### Security
- **Authentication**: Laravel Sanctum with token expiration & refresh strategies.
- **PCI DSS Compliance**: Offloaded payment capture via host checkout redirects & tokenized intent APIs (Stripe PaymentIntents, PayWay HMAC signatures).
- **Authorization**: Granular Role-Based Access Control (RBAC) separating Customers, Vendors, and Admin users.

---

## Failure Modes & Mitigations

| Failure Scenario | Business Impact | Mitigation & Recovery |
|------------------|-----------------|------------------------|
| Payment Gateway Outage | Customer unable to checkout with specific gateway | PaymentManager dynamically surfaces alternative operational gateways |
| Database Read Spike | Slow response on catalog pages | Offload query results to Redis cache & read-replicas |
| Mail Server Down | Delayed order confirmation emails | Async queue retry policies via Laravel Horizon without blocking user checkout |
