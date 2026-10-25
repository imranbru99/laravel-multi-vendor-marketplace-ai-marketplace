# Branch and warehouse behavior

A vendor may own multiple branches; warehouses belong to a matching vendor/branch pair. Offers have separate balances per warehouse. Checkout can allocate one cart line across several warehouse balances, creating separate immutable price/allocation snapshots. Stock movement records explain quantity/reservation changes.

Branch creation through the operational action calls BusinessRules::branch, locking the tenant and enforcing its total branch entitlement. Starter defaults to two; the Billing page activates configurable sandbox plans, snapshots limits and posts a simulated subscription invoice. Growth defaults to 25. No real subscription charge is collected.

Direct inventory transfers are atomic and restricted to the same offer/vendor. The current operation transfers immediately; no in-transit state, approval or receiving workflow is advertised. Branch panels expose assigned stock and allocated item shipping. Configurable scoped staff permissions are enforced. Working hours, delivery zones, cash management and branch expenses remain planned.
