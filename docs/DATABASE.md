# Database

Migrations create tenant identity/operations, catalog/offer/inventory, commerce, finance, engagement, CMS and extension tables. A machine-readable initial field inventory is in schema.json; migrations are authoritative and include later digital/notification additions.

## Principal relationships

Tenant → Vendor → Branch → Warehouse. Product → Offer belongs to a seller. InventoryBalance links Offer to Warehouse. Cart has CartItems. Order has VendorOrders and OrderItems. Each order line snapshots its offer/SKU/name, unit price, discounts, tax, commission basis points and commission amount, warehouse allocation and product type. Digital order lines snapshot a private asset version.

PaymentEvents have unique event IDs. Orders have unique checkout idempotency keys. Journals, refunds, settlements and stock movements use unique references. A vendor suborder is unique per order/vendor. An offer/warehouse balance is unique. Service bookings are unique by offer/start time.

## Integrity and history

Inventory quantities and reservations reconcile to movement deltas. Ledger entries sum to zero per transaction. Refund allocations use cumulative integer division to return rounding remainders on the final unit. Shipping stays on the master order and is not refunded by the basic policy.

SQLite and PostgreSQL triggers prohibit UPDATE/DELETE on inventory movements, financial journals/entries, payment events, audit logs, refunds and settlements. Eloquent also rejects mutation of those event models. MySQL trigger enforcement is not implemented/tested. Direct database INSERT privileges still require trusted services and least-privilege production credentials.

Foreign keys and global scopes are defense in depth; tenant ownership is not an SQL row-level-security policy. Backup/restore must include private asset files. No destructive reset workflow is supplied.

## Added workflow data

AccessRole/RoleAssignment enforce tenant/vendor/branch grants and denials. PlatformModule and ModuleScopeOverride restrict effective module availability. SubscriptionPlan is global configuration; TenantSubscription snapshots price, period length, limits and modules; SubscriptionInvoice is immutable and journaled separately from customer payments.

CommissionRule, TaxRule and ShippingRule supply pricing inputs. Order lines snapshot selected rule IDs/results and variant details; orders snapshot shipping quotes. ShipmentItem records immutable partial quantities and ShipmentEvent preserves manual tracking transitions. ProductVariant, ProductProposal and CatalogImport cover catalog enrichment/review. SlugRedirect reserves historical tenant/entity URLs. `schema.json` is an introspection of the migrated SQLite schema, not sample customer data.
