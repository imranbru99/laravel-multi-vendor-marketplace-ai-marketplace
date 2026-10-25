# Architecture

Laravel 12 modular monolith with Filament 4 operational panels, Blade storefront and customer forms, Livewire/Alpine in administration, and Tailwind 4/custom design styles. The owner brief is PROJECT_BRIEF.md; README.md records actual scope.

## Boundaries

- Platform: Tenant model, provisioning, manual entitlements and authorized tenant switching.
- Tenancy: request/job/CLI context plus TenantScoped global query/write hooks and ScopeGuard FK checks.
- Catalog: master Product and seller Offer are distinct; categories/brands belong to a marketplace.
- Inventory: balances belong to an offer and warehouse, with append-only movements.
- Commerce: cart → checkout → master order → vendor suborders → lines; historical prices/rates are snapshots.
- Payments: PaymentGateway contract with SandboxGateway only. Signed events and order locks protect capture.
- Finance: Ledger balanced journals; OrderWorkflow posts refunds and settlements.
- Extensions: basic quotations, service appointments, receipt into stock, manual workforce, private digital fulfillment.
- Interfaces: controllers and Filament resources call domain services. Custom operations recheck record authorization.

## Transaction ordering

Checkout takes a tenant row lock before cart/coupon/stock locks. This conservatively serializes checkout per tenant and simplifies module shutdown integrity; it is a documented throughput limitation. Money processing locks the master order before payment/vendor order/refund state. Stock transfers lock balances in ascending ID order. Transactions retry deadlocks. Unique constraints protect idempotency and slot ownership.

HTTP context is restored as a stack because Livewire persistent middleware runs nested pipelines. Queued confirmation jobs carry integer tenant/order identifiers, establish context explicitly and restore it afterward. Never put an already-scoped Eloquent model into an unscoped custom worker.

This release has fixed role checks, not a configurable tenant RBAC engine. It has one currency per tenant, not exchange-rate settlement. No independently deployed microservices are present.
