# Changelog

## 0.1.0 — 2026-10-25 (Asia/Dhaka)

Initial working Laravel 12/Filament 4 implementation: scoped tenancy, registry/dependency controls, catalog and seller offers, warehouse stock history, checkout and vendor splits, sandbox finance, manual fulfillment, quantity returns, private digital downloads, basic extensions, customer/operations interfaces, local demonstration data and deployment documentation.

Automated tests, isolated PostgreSQL competing-process checks and browser walkthroughs are recorded in docs/VERIFICATION.md. This is an incomplete implementation of the much larger owner brief and is not a production release.

## 2026-10-25 — End-to-end workflow expansion

- Added scoped configurable roles and platform/vendor/branch/plan module constraints.
- Added sandbox SaaS billing, immutable invoices, quota enforcement, renewal/cancellation and snapshotted terms.
- Added partial item shipping, branch allocation actions, delivery events/packing slips and return decisions.
- Added effective commission/tax/shipping rules with immutable order snapshots and a commission simulator.
- Added variants, galleries, reviewed seller catalog proposals and validated CSV staging.
- Added merged customer carts, ability-protected customer commerce APIs, password recovery and slug history redirects.
- Extended SQLite/PostgreSQL tests and repeatable concurrent-process verification.
