# Owner brief acceptance matrix

Status applies to the bounded behavior described here. **No area is certified Production Ready; the complete enterprise brief remains open.** Source scope is PROJECT_BRIEF.md.

| Area | Verified implementation | Remaining |
|---|---|---|
| Laravel/Filament | Locked dependencies, four panels, operational resources | Production rollout/upgrade rehearsal |
| Identity/access | Login/register/reset, scoped configurable role grants/denials, protected roots, token abilities | MFA, invitations/teams, external SSO |
| Tenancy | Required context, FK guards, leakage tests, platform creation/switching | Verified domains, export/archive/lifecycle |
| Module engine | Registry/cycle checks, platform/tenant/vendor/branch/plan/permission hierarchy, obligation guards | Arbitrary module schema forms, dynamic job registrations |
| SaaS billing | Sandbox activation/renewal/cancellation, period/price/entitlement snapshots, invoices, quotas | Live provider, proration, dunning, metering |
| Public marketing | Editable pages, platform directory, local contact records, sitemap/meta | Editorial/legal approval, publishing history |
| Storefront | Discovery/filter/sort, sellers/details, bag, checkout, accounts, dark mode | Comparison, Q&A, personalized ranking, loyalty |
| Catalog | Master products, variants/attributes, gallery, offers, seller proposals, CSV staging/review, URL history | Advanced versioning, feeds, translations, export pipeline |
| Vendors | Application, approval, owned offers/orders/imports, assigned account | KYC/bank integration, appeals, richer analytics |
| Branch/warehouse | Quotas, scoped stock/allocated order lines, branch shipping, adjustments/transfers | Bins/lots/serials/expiry, picking/counts, transit workflow |
| Cart/checkout | Guest/account persistence/merging, atomic allocation, coupons, splitting, idempotency | Recovery campaigns, credits/gift cards, delivery scheduling |
| Orders | Immutable pricing/variant/allocation snapshots, controlled states, partial item shipments | Item cancellation, disputes, exchange/replacement |
| Payments | Sandbox capture/HMAC callbacks, exact amount/currency, duplicate protection | All live providers, auth/capture separation, reconciliation |
| Commission | Effective dates, vendor/product/category/plan/subtotal tiers, fixed/hybrid/caps, simulator | Commercial policy review, complex promotional stacking |
| Tax/shipping pricing | Country/category/product exclusive tax; seller/branch flat/per-item/weight/free-threshold shipping | Jurisdiction determination, inclusive tax, live rates/zones |
| Ledger/settlements | Balanced journals, immutable events, return-aware 14-day hold, sandbox settlement | Live bank payout, reserves/recoveries/reconciliation UX |
| Shipping | Partial quantities, branch allocation, tracking/events/delivery, packing slip PDF, optional retry key | Real carrier labels/callbacks, proof, reverse logistics |
| Returns | Quantity request, authorization/receipt/rejection/refund, exact reversal; rights survive plan changes | Exchanges/disputes, post-settlement recovery |
| B2B | RFQ/request/offer/accept | Quote-to-order, negotiations, credit, organization procurement |
| Digital | Private asset versions, signed owned downloads, counters/expiry/revocation | S3, licenses, memberships/recurring customer products |
| Bookings | Serialized future hourly slots and completion | Staff calendars, deposits/rescheduling/reminders |
| CRM/affiliates/loyalty | Planned | Entire advanced workflows |
| Marketing | Tested coupon limits/expiry; draft campaigns, local signups/contact | Sending, segmentation, attribution, abandoned cart |
| Workforce | Manual employee/attendance records | Devices, shifts, leave/payroll, retention automation |
| Procurement | Single-line receipt into immutable stock movement | Multi-line documents, approval, supplier accounting |
| AI | Reviewed deterministic drafts, isolated AI Hub fake test | Live providers, budgets, imports/recommendations |
| Owner packages | Unicode PDF/slugs used, sanitized Drutogoti container; unsafe global providers disabled | Tenant-safe AI Catalog/Attendance adapters |
| Notifications | Explicit-tenant queued in-app confirmations, read action; reset mail notification | Configured external delivery, SMS/push, workflow templates |
| API | Tenant-bound scoped tokens, cart, checkout, own-order tracking/pay/cancel/return, sandbox callback | Administrative APIs/SDK/outbound subscriptions |
| Localization | EN/BN labels; theme preference | Complete translation, RTL, localized URLs/hreflang |
| Demo | Two tenants, commerce scenarios, five subscription records, role accounts | Every specialized advanced workflow |
| DevOps | Installer, additions/sync/verify/expiry/renewal commands, assets, worker/scheduler, Docker/CI source | Docker runtime, hosted CI, restore/load/security/accessibility audit |

Scenarios A–F have core coverage in their sandbox forms: multi-vendor checkout and refunds; branch-allocated shipping; sandbox plan quotas/upgrade; basic quotation acceptance. Live payment/payout, comprehensive B2B procurement and the full specialized operating procedures remain incomplete. Tests prove the named invariants, not every sub-bullet of the brief.
