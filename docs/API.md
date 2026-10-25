# Customer commerce API v1

Base path `/api/v1`. Send `Accept: application/json`; write requests use JSON. All money is integer minor units. Default rate limit is 60 requests/minute per endpoint key; token login allows 5/minute. Tenant context resolves from the authenticated user's tenant, or the request domain/default marketplace for public endpoints. Suspended tenants return 503.

| Method/path | Access | Behavior |
|---|---|---|
| GET /products | Public | Paginated published catalog and active approved-seller offers |
| POST /tokens | Tenant email/password | Issue a 30-day customer token; device_name required |
| DELETE /tokens/current | Bearer token | Revoke the current token |
| GET /cart | cart:write | Active account cart with item IDs, quantity/current prices |
| POST /cart/items | cart:write | Add offer_id and quantity (1–100) |
| PATCH /cart/items/{item} | cart:write | Set quantity (0 deletes); ownership required |
| POST /checkout | orders:write | Reserve stock and split order; UUID idempotency_key required |
| GET /orders | orders:read | Paginated own order summaries |
| GET /orders/{number} | orders:read | Own order snapshots and item shipment tracking |
| POST /orders/{number}/sandbox-payment | orders:write | Idempotent sandbox capture; no real charge |
| POST /orders/{number}/cancel | orders:write | Cancel unpaid order/release reserved stock |
| POST /order-items/{item}/returns | returns:write | Own delivered item quantity/reason; 14-day policy |
| POST /webhooks/sandbox | Raw-body HMAC | Confirm a configured sandbox payment |

Token request:

```json
{"email":"customer@example.test","password":"your-password","device_name":"My app"}
```

The response contains the raw bearer token once, with `Cache-Control: no-store`, expiry and granted abilities. Store it securely. Sanctum stores its hash. Send `Authorization: Bearer <token>`. Customer API grants cart:write, orders:read, orders:write, returns:write and catalog:read; no administrative abilities. Password reset revokes existing tokens. Token issuance does not authenticate a different tenant's email on the current storefront.

Checkout request:

```json
{"idempotency_key":"123e4567-e89b-42d3-a456-426614174000","email":"customer@example.test","address":{"name":"Example Customer","line1":"100 Example Avenue","city":"Demo City","postal_code":"10000","country":"US"},"coupon":"FIND1"}
```

A new order returns 201; a retry returns the same order with 200 even after a new active cart exists. A key owned by another customer is rejected. The first successful checkout wins; retries do not update shipping or pricing. Unavailable stock, coupon exhaustion, invalid quantities and other business errors return 422. Authentication/ability/ownership errors return 401/403/404. Validation errors use Laravel's message/errors JSON structure.

Cart prices are indicative; final tax, discounts, shipping and availability are confirmed at checkout. Browser guest carts merge once into the account cart on next cart access, combining quantities up to 100 per offer. The API uses the same account cart; it does not accept arbitrary browser guest tokens.

Sandbox webhook requires PAYMENT_WEBHOOK_SECRET. Header `X-Sandbox-Signature` is lowercase hex `hash_hmac('sha256', rawBody, secret)`. JSON includes event_id, reference, amount_minor and currency; amount/currency must match the payment. Missing server secret returns 503. Duplicate events never consume stock or post a second journal. This protocol is an internal sandbox contract, not compatibility with a real gateway.

OpenAPI is in openapi.yaml. Administrative workflows remain in authenticated Filament panels; no unrestricted generic administrative API, GraphQL, outbound webhook subscriptions or SDK is included.
