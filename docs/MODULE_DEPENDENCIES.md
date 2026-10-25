# Module dependency matrix

| Key | Requires | Core | Shutdown guard |
|---|---|---|---|
| catalog | — | Yes | Always protected |
| inventory | catalog | No | Dependents and active orders |
| checkout | inventory | No | Dependents and active orders |
| payments | checkout | No | Dependents and active orders |
| shipping | payments | No | Active orders |
| returns | payments | No | Requested returns |
| digital | payments | No | Dependencies checked; purchased downloads retained |
| reviews | catalog | No | No destructive operation |
| marketing | checkout | No | Stops new coupon redemption |
| support | — | No | Stops new tickets; history remains |
| b2b | catalog | No | Requested/quoted/accepted quotations |
| bookings | catalog | No | Confirmed future appointments |
| procurement | inventory | No | Draft/ordered purchases |
| workforce | — | No | History retained |
| ai | catalog | No | History retained |

Effective availability combines platform, tenant, plan, vendor, branch and role constraints. Vendor/branch overrides only restrict enabled parents. SaaS quotas cover branches/vendors/warehouses/products; arbitrary module configuration forms remain planned. Deactivation never deletes records. Current domain locks serialize critical module operations against tenant toggles. Whole-module behavior beyond the implemented bounded workflows is not implied.
