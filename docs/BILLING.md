# Sandbox SaaS billing

Platform subscriptions are separate from customer checkout. Platform administrators edit `SubscriptionPlan` example prices, periods, limits and module entitlements. Owners activate plans from Billing; the service locks the tenant, validates current usage/dependencies and outstanding obligations, snapshots plan terms, expires the previous active subscription and creates an immutable sandbox-paid invoice plus balanced subscription expense/cash entries. There is no external charge.

The default example Starter plan is USD 29 / 30 days with 2 branches, 5 vendors, 4 warehouses and 250 products; Growth is USD 99 / 30 days with 25/100/50/10000. These are editable demonstration prices, not a commercial offer. A downgrade cannot put current usage over its limits. Creating a vendor/branch/warehouse/product checks the appropriate quota under a tenant lock.

Automatic renewal is opt-in. `marketplace:billing-renewals` runs daily; it locks each tenant, uses period-specific invoice references and extends snapshots without duplicating invoices. Cancellation disables renewal, preserving the current period and history. Expiry restricts new optional-module work; customer history and purchased return rights remain accessible. Core catalog access remains available. Existing historical financial terms never change when the plan definition changes.

There is no live subscription provider, proration, dunning or refund/credit-note system. Native PDF invoices are authorized to tenant owners/admins/platform users. Five demo subscriptions comprise three expired records and two active sandbox examples.

Reactivating the currently active plan is idempotent within its paid period: it changes renewal preference without a second invoice. Price and period length are snapshotted for renewal; editing the plan does not silently alter an existing subscription.
