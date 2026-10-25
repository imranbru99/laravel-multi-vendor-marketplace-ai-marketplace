# Payments

The only adapter is SandboxGateway implementing PaymentGateway. No Stripe, PayPal, SSLCOMMERZ, bKash, Nagad or bank transfer integration is implemented. No raw card details are accepted, and no actual money is transferred. Disable sandbox to block capture/refund/settlement; doing so does not add a live provider.

Checkout creates a pending payment for the exact order total and currency, with 30-minute stock reservation expiry. Capture locks order/payment state, validates amount/currency, records a unique event, consumes stock, credits vendor liabilities/commission/tax/shipping and debits sandbox cash. Duplicate capture returns without another stock or journal event.

The customer order page is authorized by the account or guest browser session. The webhook route has no browser CSRF requirement but requires HMAC SHA-256 of the exact request bytes with PAYMENT_WEBHOOK_SECRET in X-Sandbox-Signature. Empty secret returns 503. Never use a guessable production secret.

Refunds are local confirmed simulations after receipt approval. Shipping is not refunded. Settlements are local simulations after completed delivery and the 14-day hold with no pending return. Full digital order settlements use captured-payment age for the hold. Live provider error/retry/reconciliation/chargeback behavior is pending, so this implementation cannot be used to collect real money.
