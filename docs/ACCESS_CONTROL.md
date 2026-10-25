# Scoped access

Protected baseline roles provide platform, marketplace, seller, branch, finance, support and customer defaults. Configurable Access Roles grant or deny entity view/create/update/execute, module usage and named workflow permissions. Scope is tenant, vendor or branch. Role assignments must match the user's tenant and any seller/branch restriction; protected owners/platform users cannot be reassigned through this workflow. Revocation is audited and clears the request permission cache. Explicit denials override non-root defaults.

OperationsAccess combines entity permission with tenant/vendor constraints. BranchAccess limits warehouse stock, movements, allocated order items, purchase receipt, staff and shipment visibility. Branch staff can ship only their allocated line quantities; a shipment containing another branch's items is excluded from their view. Generic branch master-data edits are disabled. Workflow execution checks the named permission again, independently of whether a button is visible.

Module access intersects platform enabled state, active plan entitlements, tenant enabled state, seller/branch restrictions, role denial and dependency state. Scoped controls cannot enable a disabled parent. Deactivation refuses relevant open obligations and never deletes business records. Transaction paths lock the tenant to serialize new obligations against toggles.

Customer Sanctum tokens grant only explicit commerce abilities and still require record ownership. They never confer administrative access. See API.md for issuance, expiry and revocation.
