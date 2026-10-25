# Pricing snapshots

All amounts are integer minor units; 100 minor units equal one major currency unit. Basis points use 10000 = 100%. The application supports one settlement currency per marketplace and prevents changing it once financial history exists.

Commission rules are effective-dated and scoped by seller, master product, category, tenant plan and subtotal interval. Priority and specificity select the rule; the service applies percentage plus fixed-per-unit, min/max caps and an absolute cap at net merchandise. Without a match, seller/tenant percentage defaults apply. The commission simulator uses the same service without moving money. Versioned rule terms are immutable; disable a version and create a successor.

Country/product/category tax rules calculate exclusive merchandise tax after discount. This is a configurable policy, not a jurisdictional tax service. Seller/branch/country shipping rules support flat, per-item or rounded-up kilogram weight, plus a free-shipping threshold. Digital products do not add shipping. Unmatched physical groups share one configured fallback order charge.

Checkout stores tax/commission rule IDs, applied inputs/results, variant/weight information and shipping quote snapshots. Later product/rule changes cannot rewrite historical line amounts. Quantity refunds allocate cumulatively from the original snapshots so the last unit absorbs rounding remainders. Original shipping is retained.
