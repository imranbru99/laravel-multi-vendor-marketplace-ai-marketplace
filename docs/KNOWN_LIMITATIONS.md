# Known limitations and production status

**The complete enterprise brief is not finished. The application is not production ready.** The working application includes end-to-end sandbox commerce, catalog review/import, scoped permissions, module hierarchies, pricing rules and SaaS sandbox billing. These boundaries remain:

- No verified custom-domain ownership workflow, invitations/teams, MFA, self-service tenant export/archive/deletion or operational retention automation. Authentication includes password recovery; mail delivery depends on the configured transport.
- Configurable roles supplement protected baseline roles. There is no external identity provider or organization membership system.
- Sandbox SaaS plans support periodic renewal, quotas, invoices and cancellation. No real recurring payment provider, proration, dunning, credit notes or metered storage billing.
- Catalog supports variants, attributes, galleries, reviewed seller proposals and CSV validation/staging. No full translation/version comparison, automated supplier feeds, export pipeline or image moderation service. Imports stage for review rather than automatically publishing.
- Inventory supports balances, reservations, allocation, adjustments and direct atomic transfers. No bins/lots/serials/expiry, count sessions, putaway/picking or in-transit transfer approvals.
- Effective-dated commission rules support percentage/fixed/hybrid amounts, subtotal tiers and min/max. Country/product/category tax is simplified exclusive merchandise tax, with no jurisdictional determination, tax-inclusive pricing or external tax filing. Shipping supports flat/per-item/weight rules, not live carrier rates/zones.
- Payment/refund/settlement providers are sandbox-only. No live card checkout, bank payout, disputes/chargebacks or external reconciliation. Adding credentials does not implement an adapter.
- Shipments support partial item quantities, branch allocation, tracking, delivery events and packing slips. No real labels, carrier callbacks, proof of delivery or reverse logistics provider.
- Returns support request/authorization/receipt/rejection/refund within the physical return policy. No exchange/replacement/dispute service or post-settlement recovery; original shipping is retained. Existing return rights survive module/plan changes.
- Private digital downloads use local disk, purchase asset snapshots, signed URLs, limits and revocation. No S3 adapter, license key pools, memberships or recurring customer products. Guest digital checkout is blocked.
- B2B is request/offer/acceptance, not quote-to-checkout, credit limits or purchase contracts. Booking uses unique hourly slots, not staff calendars/deposits/rescheduling. Procurement is single-line receipt, not supplier accounting. Workforce is manual records, not devices/payroll.
- Marketing campaigns remain drafts; newsletter/contact records are local. No campaign delivery automation, referrals, affiliates, loyalty, gift cards or CRM pipeline.
- AI uses deterministic local suggestions and package fake tests. No external inference, provider budgets or automated catalog importing. Package installation is not an integration claim.
- Customer API supports commerce with scoped tokens. No generic administrative API, GraphQL, external webhook subscriptions or SDK.
- EN/BN translation is partial; no complete RTL/localized content system. Unicode PDF bytes are tested; complex-script visual shaping is not certified.
- PostgreSQL race checks establish specific transaction invariants, not production throughput. Tenant-wide serialization is conservative. No load benchmark, independent security/accessibility audit, backup restore drill, hosted CI run or production rollout is claimed.
- SQLite/PostgreSQL protect financial/event histories with triggers. MySQL and Docker image runtime are not verified. The local host's Podman-backed Docker CLI cannot initialize its runtime directory under the available permissions.

See FEATURE_MATRIX.md and VERIFICATION.md for actual coverage. Informational industry pages describe applications of the architecture; they do not certify all specialized workflows.
