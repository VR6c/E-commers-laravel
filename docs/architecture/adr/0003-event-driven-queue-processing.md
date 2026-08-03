# ADR-0003: Event-Driven Queue Processing with Redis & Horizon

## Status
Accepted

## Context
Tasks such as sending order receipts, processing stock adjustments across multiple vendors, and processing payment webhooks introduce HTTP latency and potential failure points during user checkout.

## Decision
Utilize Laravel's Event and Job queue system backed by Redis and managed via Laravel Horizon for asynchronous task execution.

## Consequences

### Positive
- Sub-150ms HTTP checkout response times.
- Failed background jobs automatically retry without failing user transactions.
- Real-time job metrics and queue monitoring via Horizon dashboard.

### Negative
- Operational requirement to maintain Redis daemon and queue supervisor processes.
- Notifications become evented with slight propagation delay (< 2 seconds).

## Alternatives Considered
- **Synchronous Execution**: Rejected due to slow HTTP response times and webserver timeouts on mail server latency.
- **Database Queues**: Rejected due to higher lock contention and disk I/O under heavy checkout loads.
