# Financial ledger

Signed integer minor units use positive debits and negative credits. Ledger::post requires at least two entries and an exact zero sum. References are unique. Transactions/entries are append-only through model hooks and SQLite/PostgreSQL triggers.

Capture journal: debit sandbox_cash for the order total; credit vendor_payable per seller, commission_revenue, tax_payable and shipping_revenue. Vendor payable is merchandise after coupon discount minus commission. Tax is retained as a platform liability in this simplified policy. It is not automatically remitted.

Refund journal: credit sandbox_cash; debit vendor_payable, commission_revenue and tax_payable for the returned allocation. Cumulative quantity allocations make the last returned unit consume rounding remainders. Original shipping remains retained. Settled vendor orders cannot be refunded by this basic workflow; production recoveries/reserves are planned.

Settlement journal: debit vendor_payable and credit sandbox_cash. A locked vendor suborder and unique reference prevent duplicate payouts. “sandbox_paid” is not a bank confirmation. Commission is a tenant default percentage with an optional vendor override, not the full effective-dated tier/category/hybrid engine in the owner brief.

`marketplace:verify` checks journal balance, stock reconciliation and master order arithmetic across tenants. No automated repair mutates historical journals. Chart-of-accounts configuration, legal tax accounting, fee accrual, FX, reconciliation feeds and production bank payout controls are unfinished.
