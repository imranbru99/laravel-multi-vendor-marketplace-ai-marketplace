# Multi-tenancy

ResolveTenant runs after session authentication and before application queries. An authenticated account uses its assigned tenant. A platform administrator may select a tenant in session through Platform Tenants. Public requests use a configured tenant domain or MARKETPLACE_TENANT fallback. Suspended tenants return 503.

TenantScoped models require context; an absent context throws instead of returning all records. Create/update hooks enforce tenant ownership and validate referenced rows with ScopeGuard. Cross-vendor warehouse, employee and purchase associations have additional checks. User is a centralized identity table, accessed through explicit tenant filters in administration. Email uniqueness is global.

Resource queries apply vendor ownership in addition to tenant filtering. Branch personnel see only their allocated order lines, stock and authorized shipment records. Scoped roles grant or deny entity and workflow permissions; branch users cannot edit generic master records. APIs authenticate before resolving account tenant context. Jobs carry tenant IDs and restore context in finally. The public tenant fallback is intended for a single storefront/default host; production custom-domain ownership verification and routing management are not implemented.

The selected platform context is shared across the browser session. Switching must be deliberate and is audited. Private digital files use tenant directories and authenticated signed download routes, never public storage symlinks. Test cross-tenant reads, FK writes and Livewire nested context restoration before extending.
