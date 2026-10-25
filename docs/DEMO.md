# Demonstration

Use an empty local/testing database with MARKETPLACE_DEMO=true and PAYMENT_SANDBOX=true. `marketplace:demo-seed` migrates and explicitly seeds fictional data. It refuses production and existing atlas/harbor tenants, preserving data. Password Demo@12345 is development-only; /demo discloses it only when both local/testing and demo mode apply.

The required 13 role emails are in README.md. `owner2@demo.test` belongs to the second tenant. Platform tenant switching is session-based and audited. Public pages describe simulated content without invented real customer endorsements.

Dataset: two tenants, five stores, twelve branches, eight warehouses, twenty categories, thirty brands, at least two hundred catalog products/three hundred offers, one hundred customer identities and two hundred fifty orders. It includes captures, vendor splitting, warehouse stock, low stock, deliveries, requested returns, thirty refunds, sandbox settlements, one hundred sample verified reviews, twenty-five coupons, ten draft campaigns, support, staff/manual attendance and procurement. Each marketplace gets a private text handbook and service appointment listing.

Five subscription records are seeded: three expired historical plans and two active sandbox plans. Production gateways and real carrier labels are not seeded. Some delivery timestamps are backdated to demonstrate settlement eligibility; this is a workflow dataset rather than a statistically representative sales history. Browser QA may add records; integrity results record their actual counts.

Do not publish shared unrestricted privileged credentials. A public demonstration needs isolated disposable identities, restricted administration and environment-level access controls beyond this local mode.
