# ADR-0002: Implement Strategy Adapter Pattern for Payment Gateways

## Status
Accepted

## Context
The application supports multiple payment methods (ABA PayWay, Stripe, PayPal) with distinct API interfaces, signature hashing algorithms, and checkout workflows. Direct coupling of controllers to payment SDKs makes supporting new gateways error-prone.

## Decision
Implement a `PaymentGatewayInterface` and a unified `PaymentManager` factory class to instantiate gateway strategies at runtime:
```php
$gatewayService = PaymentManager::make('abapayway');
```

## Consequences

### Positive
- Controllers depend only on uniform interfaces rather than concrete gateway implementations.
- New payment providers (e.g. KHQR, Razorpay) can be integrated by creating a single service implementation without touching checkout controller logic.
- Mocking payment providers during PHPUnit test suites is simplified.

### Negative
- Gateway-specific custom parameters require normalized configuration maps in `PaymentGatewayConfig`.

## Alternatives Considered
- **Direct Controller Logic**: Rejected due to duplicate code across Storefront and API controllers.
- **Third-Party Payment Aggregator**: Rejected due to increased transaction fees and regional payment gateway availability constraints.
