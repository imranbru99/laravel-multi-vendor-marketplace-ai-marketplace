<?php

namespace Database\Seeders;

use App\Models\ContentPage;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['about', 'About Common Ground', 'Good things. Independent people. A marketplace with a point of view.', 'Common Ground is the demonstration storefront for Multi-vendor Marketplace, built by Imran Ahmed. It brings independent seller offers into one searchable catalog.

All businesses, products and customer activity in the demo dataset are simulated. Product illustrations are original vector artwork created for this application. There are no invented client endorsements or live commercial claims.

Developer: Imran Ahmed
Official website: https://imrandev.bd', 'company'],
            ['features', 'Platform features', 'A connected foundation for independent marketplace businesses.', 'Working capabilities include scoped marketplace data, seller offers, stock movements, customer carts, multi-vendor checkout, sandbox payments, manual shipping, item returns and balanced financial journals.

Four Filament panels give platform, marketplace, seller and branch teams distinct entry points. Modules can be enabled and disabled subject to dependency and transaction checks. Advanced capabilities and external provider integrations are tracked in the project feature matrix.', 'company'],
            ['modules', 'Module directory', 'Choose the capabilities your marketplace needs.', 'The registry below shows the modules implemented in this release. Administrators can manage enabled modules from the Filament Modules page. Dependencies must be enabled first. Existing order history and accounting remain accessible.

Activation does not imply that a production external service is configured. Payments and AI content in this installation use explicitly labeled sandbox behavior.', 'company'],
            ['how-it-works', 'How it works', 'One discovery. One checkout. Independent fulfillment.', '1. Browse products, compare seller offers and add available items to your bag.
2. Enter a delivery address and optionally apply a coupon.
3. Checkout validates current prices and reserves available warehouse stock.
4. A master order and vendor suborders preserve pricing and commission snapshots.
5. Confirm the sandbox payment. Stock is consumed and a balanced journal is posted.
6. Each seller records its own shipment. Follow delivery from your order page.
7. Eligible delivered items can be requested for return within 14 days.

The demonstration does not collect real payments or arrange real deliveries.', 'company'],
            ['solutions', 'Marketplace solutions', 'One configurable platform, different business contexts.', 'The core architecture separates marketplaces, vendors, branches, warehouses, master products and seller offers. This allows a business to configure a catalog and operating model without duplicating its application.

The implemented foundation suits retail product discovery and manual seller fulfillment. Private digital downloads and sandbox SaaS recurring billing are implemented. Specialized grocery delivery, recurring customer memberships and advanced wholesale credit remain future extensions.', 'company'],
            ['pricing', 'Plans & entitlements', 'Transparent configuration before commercial commitments.', 'The local installation includes editable Starter and Growth sandbox example plans. Starter is USD 29 per 30 days with 2 branches; Growth is USD 99 per 30 days with 25 branches. These are demonstration values, not a commercial offer.

Owners can activate a sandbox plan, opt into simulated renewal, cancel renewal and download invoices. Vendor, warehouse and product quotas also apply. No real payment is collected. Contact the operator for actual commercial terms.', 'company'],
            ['pricing-comparison', 'Plan comparison', 'Understand the entitlements currently enforced.', 'Starter: two total branches per marketplace.
Growth: 25 total branches per marketplace.

Starter additionally permits 5 vendors, 4 warehouses and 250 products. Growth permits 100 vendors, 50 warehouses and 10000 products. Plans control module entitlements; scoped administrators can further restrict availability. Sandbox invoices and optional renewal are implemented. Real commercial billing, proration and storage metering remain planned.', 'company'],
            ['security', 'Security & access', 'Explicit tenant context, scoped queries and auditable operations.', 'Tenant-owned models require an active tenant context. Their default query scope filters by tenant and their write hooks validate referenced tenant-owned records. Seller resources additionally filter vendor records. Branch users can act on their allocated stock and shipment lines. Configurable role grants/denials further constrain entity and workflow permissions.

Web forms use CSRF protection. Login and registration are rate limited. Passwords are hashed. No raw card data is collected. Sandbox webhooks require an HMAC signature. Financial and inventory event models reject ordinary updates and deletes.

Production security still requires infrastructure controls, dependency auditing, backups, monitoring, a penetration test and production load validation. Isolated PostgreSQL transaction race tests are included. Do not expose privileged demo accounts to the public.', 'company'],
            ['integrations', 'Integration status', 'An honest view of what is connected.', 'Laravel 12, Filament 4, Sanctum and six owner-supplied packages are locked in Composer. Unicode PDF is used for invoices, and Universal Slug generates demo product and seller slugs.

Payments: internal sandbox adapter only. No money moves.
Shipping: manual carrier and tracking entry only. No live carrier rates or labels.
AI: human-reviewed local sandbox drafts. No external inference requests.
Attendance: manually recorded demonstration events. Device ingestion is not enabled.
Mail: log transport by default. Newsletter signups and contact messages persist locally; no delivery is claimed.

Production provider adapters and credentials must be implemented and verified before activation.', 'company'],
            ['documentation', 'Documentation', 'Understand the workflows and their boundaries.', 'Installation, architecture, data model, security, API and operational documentation live in the docs directory of the source project. README.md is the implementation inventory.

For local demonstrations, run migrations, explicitly enable MARKETPLACE_DEMO, and run php artisan marketplace:demo-seed against an empty isolated database. Open /demo for local credentials.

For production, use marketplace:install to create an administrator securely. Do not seed known demo passwords. Configure APP_KEY, database, mail, storage, queue workers and scheduler before use.', 'resources'],
            ['api', 'Developer API', 'Versioned JSON endpoints with explicit authentication.', 'GET /api/v1/products returns paginated published product data and eligible seller prices.
POST /api/v1/tokens issues a tenant-bound 30-day customer token. Ability-protected routes cover account cart changes, idempotent checkout, own-order tracking, sandbox payment, cancellation and eligible return requests. GET /api/v1/orders returns only the authenticated customer’s order summaries.
POST /api/v1/webhooks/sandbox validates an HMAC SHA-256 signature in X-Sandbox-Signature against PAYMENT_WEBHOOK_SECRET before confirming a sandbox payment.

API authentication tokens can be provisioned by an operator through Sanctum. OpenAPI documentation is included in docs/openapi.yaml. This release does not expose unrestricted administrative CRUD APIs.', 'resources'],
            ['contact', 'Get in touch', 'A conversation starts here.', 'Use the form below to leave a message for the marketplace team. Messages are stored in the dashboard for authorized administrators. Submission does not imply an email was delivered.

For software authorship and first-party developer information, visit https://imrandev.bd.', 'company'],
            ['support', 'Customer support', 'Your questions deserve a clear answer.', 'Signed-in customers can create support tickets in their account. The marketplace team can read the message, reply, and mark it resolved from the Support Tickets resource. Responses are visible in your account.

For general questions before signing in, use the contact form below. This demo does not send external email or SMS notifications.', 'company'],
            ['help-center', 'Help center', 'A useful starting point for your marketplace journey.', 'Finding products: use Shop all and filter by category, brand, seller, or price.
Shopping bag: add an offer, change quantity, or set quantity to zero to remove it.
Checkout: supply an email and delivery address. Prices are verified before the order is placed.
Payment: confirm the sandbox payment from the order screen. No card or real money is used.
Tracking: each vendor has a separate shipment, recorded by its team.
Returns: sign in, open your completed order, and request an eligible return within 14 days of delivery.
Support: open a ticket from your account.

If a module is disabled, the corresponding new operation will be blocked by the application.', 'resources'],
            ['faq', 'Frequently asked questions', 'Clear answers, without the fine-print surprises.', 'Is the storefront real? It is a working software demonstration with simulated inventory and sellers.
Can I pay? You can confirm a sandbox payment. No money is charged.
Can several stores share one cart? Yes. Checkout creates vendor-specific suborders.
Are stock reservations real database records? Yes. Reservations and movements are persisted and reversible on unpaid cancellation.
Can sellers see each other’s private orders? Seller resources filter by the assigned vendor.
Are all requested enterprise modules complete? No. The feature matrix lists implemented and pending capabilities honestly.
Can I use the demo publicly? Do not expose privileged shared demo credentials. Use an isolated, secured environment.', 'resources'],
            ['privacy-policy', 'Privacy policy', 'Demo data handling and deployment considerations.', 'This development installation stores account names, email addresses, hashed passwords, delivery addresses, shopping activity, support requests and operational audit events in its configured database. It does not collect payment card numbers or biometric templates.

Data is used to operate the workflows the user requests. Contact and newsletter submissions are stored locally. Mail uses the configured transport; the default is a local log.

This is a development policy draft. Before commercial launch, the operator must identify its legal entity, jurisdiction, retention periods, processors and contact channels, and publish an approved policy. Do not enter sensitive real personal data in a shared demo.', 'legal'],
            ['cookies', 'Cookie policy', 'Session cookies keep your visit connected.', 'The application uses a session cookie for authentication, CSRF protection, your shopping bag token and guest order access. These are functional cookies for the requested service.

No advertising or analytics tracking scripts are included by default. Configure session security, HTTPS and cookie domains for your deployment. Additional integrations may require a revised policy and consent controls.', 'legal'],
            ['cookie-policy', 'Cookie details', 'Essential session storage for your marketplace visit.', 'Authentication and guest carts use Laravel sessions. CSRF tokens protect state-changing web forms. The default application includes no third-party advertising cookies.

The operator must review cookie settings and any future analytics integrations before commercial launch.', 'legal'],
            ['vendor-agreement', 'Vendor agreement', 'A draft framework for independent seller participation.', 'Sellers submit a business name and description for marketplace review. Administrators approve stores and assign authorized seller accounts. Seller offers must reference approved catalog products, accurate integer prices and available stock.

Configured commission rates are snapshotted when orders are placed. Refunds reverse eligible commission and vendor liability. Sandbox settlements never represent bank transfers.

This development draft is not an executed commercial contract. The operating entity must set approved seller terms, lawful verification requirements, tax obligations, dispute processes and payout arrangements before accepting real business.', 'legal'],
            ['refund-policy', 'Returns & refunds', 'A clear workflow for when something is not quite right.', 'Customers can request a quantity-specific return within 14 days after a recorded delivery. The system prevents over-returning quantities, overlapping unresolved requests and refunds after settlement.

The marketplace team records receipt and approves the sandbox refund. The refund reverses the item’s allocated merchandise, tax and commission amounts. Original order-level shipping is not refunded by the implemented policy. Returned units are restocked.

Settlements require completed delivery, expiry of the 14-day return window and no unresolved returns. All monetary events in this demonstration are simulated. A production operator must publish approved legal and commercial terms.', 'legal'],
            ['become-a-seller', 'A home for your independent store', 'Bring your perspective. We will bring the marketplace.', 'Create a customer account, then submit your store name and a description of your business from the account page. Your application begins in pending review.

A marketplace administrator reviews and approves the store and assigns a seller account. Sellers can then manage offers, locations, stock and eligible vendor orders within their authorized scope.

The demo is not a commercial onboarding service. Identity verification, bank onboarding and production payout integrations remain prerequisites for a live marketplace.', 'company'],
            ['request-demo', 'Request a demonstration', 'Explore your business workflow with the marketplace team.', 'You can explore the working local demo from /demo. Use the form below to record a demonstration request for the marketplace operator.

Messages persist in the Contact Messages resource. External email delivery is not implied. For a real deployment, arrange a secured environment and use separate identities for each reviewer.', 'company'],
            ['customer-stories', 'Customer stories', 'Evidence comes before endorsements.', 'There are no verified customer stories available for this new application. We do not publish invented customer names, business results, statistics or testimonials.

The local demonstration shows simulated seller fulfillment, multi-vendor checkout, returns and ledger behavior. Those scenarios demonstrate software workflows rather than commercial outcomes.', 'company'],
            ['case-studies', 'Workflow case studies', 'Explore scenarios from the simulated dataset.', 'Multi-vendor discovery: a shopper adds items from separate sellers, checks out once, and receives seller-specific fulfillment records.

A returned unit: a customer requests one unit from a delivered order. An administrator records receipt and confirms the sandbox refund. Stock is restored and a balanced reversal journal is created.

These examples are simulated test scenarios, not reports of real customer performance.', 'resources'],
            ['blog', 'From the marketplace', 'Notes on building thoughtful commerce.', 'Design note: separate a master catalog from seller offers. One product may be sold by several vendors at different prices.

Engineering note: preserve the price and commission applied at checkout, so future changes do not alter historical orders.

Operations note: stock movements explain why a balance changed. Financial journals explain where a liability came from.

These editorial notes describe the implemented foundation. A full article publishing workflow is planned.', 'resources'],
            ['changelog', 'Changelog', 'A record of actual changes.', 'Initial implementation: Laravel and Filament foundation, tenant-scoped marketplace tables, module dependency controls, seller offers, inventory movements, customer shopping flows, sandbox payments, shipping operations, returns, balanced ledger and settlements.

See CHANGELOG.md and docs/VERIFICATION.md for the verified release state. No production-readiness claim is made without the required evidence.', 'resources'],
            ['roadmap', 'Implementation roadmap', 'Build the foundation. Verify the behavior. Extend deliberately.', 'Current scope: catalog, inventory, checkout, sandbox finance, operational panels and local demonstration data.

Next validation: PostgreSQL parallel transaction tests, accessibility audit, dependency security review and hardened production deployment.

Planned extensions: live payment and carrier providers, digital fulfillment, recurring subscriptions, branch-level order allocation controls, configurable tax jurisdictions, granular tenant-scoped RBAC, search indexing, notification delivery, advanced procurement and workforce devices.

The complete owner brief is preserved with an implementation status matrix in the source project.', 'resources'],
            ['careers', 'Build considered commerce', 'Thoughtful systems are built by thoughtful people.', 'No open positions are currently advertised by this application. The form below records a general expression of interest for the marketplace operator.

Submitting a message does not create an employment relationship or guarantee an external email response.', 'company'],
            ['become-a-partner', 'Explore a partnership', 'Bring complementary expertise to the marketplace.', 'Implementation, independent seller communities and operational support are potential areas for collaboration. No partner certification or commercial program is currently active.

Use the form below to record an inquiry. The marketplace operator can review it in the dashboard.', 'company'],
            ['terms', 'Terms of service', 'Development demonstration terms.', 'This installation is a software demonstration. All seeded businesses and transactions are simulated. Sandbox confirmation does not purchase physical goods, arrange delivery or transfer money.

Use only data you have permission to provide. Do not upload secrets or sensitive personal information to a shared demonstration. Do not expose shared privileged accounts publicly.

Before commercial use, the operating entity must publish jurisdiction-appropriate terms, identify the contracting parties and configure approved payment, tax, fulfillment and consumer-rights processes. This draft is not a substitute for those terms.', 'legal'],
            ['terms-of-service', 'Terms of service', 'Development demonstration terms.', 'This installation is a software demonstration. All seeded businesses and transactions are simulated. Sandbox confirmation does not purchase physical goods, arrange delivery or transfer money.

Use only data you have permission to provide. Do not upload secrets or sensitive personal information to a shared demonstration. Do not expose shared privileged accounts publicly.

Before commercial use, the operating entity must publish jurisdiction-appropriate terms, identify the contracting parties and configure approved payment, tax, fulfillment and consumer-rights processes. This draft is not a substitute for those terms.', 'legal'],
            ['marketplace-for-retail', 'Marketplace for Retail', 'A configurable starting point for retail product catalogs and independent sellers.', 'The catalog separates master products and seller offers, while branches and warehouses hold inventory. Checkout records stock reservations and creates seller-specific order histories.

Specialized live operations, recurring billing, secure digital downloads and regulated workflows require additional implementation and validation.

All example products and stores in this installation are simulated.', 'solutions'],
            ['marketplace-for-fashion', 'Marketplace for Fashion', 'A configurable starting point for style, accessories and everyday collections.', 'The catalog separates master products and seller offers, while branches and warehouses hold inventory. Checkout records stock reservations and creates seller-specific order histories.

Specialized live operations, recurring billing, secure digital downloads and regulated workflows require additional implementation and validation.

All example products and stores in this installation are simulated.', 'solutions'],
            ['marketplace-for-electronics', 'Marketplace for Electronics', 'A configurable starting point for electronic products and seller-specific offers.', 'The catalog separates master products and seller offers, while branches and warehouses hold inventory. Checkout records stock reservations and creates seller-specific order histories.

Specialized live operations, recurring billing, secure digital downloads and regulated workflows require additional implementation and validation.

All example products and stores in this installation are simulated.', 'solutions'],
            ['marketplace-for-grocery', 'Marketplace for Grocery', 'A configurable starting point for grocery product catalogs.', 'The catalog separates master products and seller offers, while branches and warehouses hold inventory. Checkout records stock reservations and creates seller-specific order histories.

Product discovery and warehouse stock apply to grocery catalogs. Expiry batches, delivery slots, distance-based rates and cold-chain operations are planned.

All example products and stores in this installation are simulated.', 'solutions'],
            ['marketplace-for-furniture', 'Marketplace for Furniture', 'A configurable starting point for home and furniture catalogs.', 'The catalog separates master products and seller offers, while branches and warehouses hold inventory. Checkout records stock reservations and creates seller-specific order histories.

Specialized live operations, recurring billing, secure digital downloads and regulated workflows require additional implementation and validation.

All example products and stores in this installation are simulated.', 'solutions'],
            ['marketplace-for-wholesale', 'Marketplace for Wholesale', 'A configurable starting point for business quotation requests.', 'The catalog separates master products and seller offers, while branches and warehouses hold inventory. Checkout records stock reservations and creates seller-specific order histories.

Customers can request quantity-based quotations. Sellers can enter a quoted amount and customers can accept it. Automated procurement credit and invoice payment terms are not implemented.

All example products and stores in this installation are simulated.', 'solutions'],
            ['marketplace-for-digital-products', 'Marketplace for Digital Products', 'A configurable starting point for digital product catalogs.', 'The catalog separates master products and seller offers, while branches and warehouses hold inventory. Checkout records stock reservations and creates seller-specific order histories.

Specialized live operations, recurring billing, secure digital downloads and regulated workflows require additional implementation and validation.

All example products and stores in this installation are simulated.', 'solutions'],
            ['marketplace-for-local-businesses', 'Marketplace for Local Businesses', 'A configurable starting point for independent local sellers.', 'The catalog separates master products and seller offers, while branches and warehouses hold inventory. Checkout records stock reservations and creates seller-specific order histories.

Specialized live operations, recurring billing, secure digital downloads and regulated workflows require additional implementation and validation.

All example products and stores in this installation are simulated.', 'solutions'],
            ['catalog', 'Product catalog', 'Master products, categories, brands and independent seller offers.', 'Administrators manage catalog records. Sellers manage their own offers against existing products. Published products appear in discovery and product pages. Prices are integer minor units and SKU uniqueness is scoped to the marketplace.

Bulk import/export, full variant PIM, media uploads and approval version history remain planned.', 'features'],
            ['inventory', 'Inventory management', 'Warehouse balances explained by auditable movements.', 'Stock is held by offer and warehouse. Adjustments and direct transfers generate immutable movement records. Checkout reserves units atomically; capture consumes reserved stock; unpaid cancellation releases it.

Cycle counts, in-transit transfer approval, serial/lot/expiry tracking and valuation layers remain planned.', 'features'],
            ['checkout', 'Cart & checkout', 'One customer journey across independent sellers.', 'A persisted cart holds seller offers and quantities. Checkout locks stock, validates published offers and approved vendors, applies an eligible percentage coupon, snapshots prices and commissions and creates vendor suborders. A unique idempotency key prevents repeated checkout.

Guest order access is tied to the browser session. Authenticated customers can revisit their orders. Gift cards, credits, loyalty, cart recovery and sophisticated shipping selections remain planned.', 'features'],
            ['payments', 'Payment management', 'A verified internal sandbox boundary.', 'The sandbox adapter confirms pending orders, validates the expected amount and currency, records an idempotent payment event, consumes reservations and posts a balanced journal. Signed sandbox webhooks are supported when a secret is configured.

No real card, wallet, bank transfer, split-payment gateway or production refund adapter is configured. Sandbox payments never move money.', 'features'],
            ['shipping', 'Shipping & delivery', 'Seller fulfillment with a clear operational history.', 'Authorized operators record the carrier and tracking reference for a vendor order and later mark delivery. The customer sees separate vendor shipments on the master order page. Shipping cost is a configurable order-level flat amount.

Live carrier labels, rate shopping, item-level partial shipments, proof of delivery and reverse logistics remain planned.', 'features'],
            ['returns', 'Returns & refunds', 'Item-specific quantities and balanced reversals.', 'Customers request eligible delivered quantities from their account within 14 days. Staff record receipt and approve sandbox refunds. Cumulative allocation preserves exact rounding on the final refunded unit. Restocking creates a movement. Commission, tax and vendor liabilities reverse through a balanced journal.

Automatic chargebacks, exchanges, store credits and production provider refunds remain planned.', 'features'],
            ['reviews', 'Verified product reviews', 'Feedback follows a completed purchase.', 'A customer may submit one review per product after a completed vendor order containing that product. Ratings must be from one to five. Reviews are displayed with verified-purchase context. Demo reviews explicitly identify themselves as simulated.

Helpful voting, seller reviews and public question-and-answer workflows remain planned.', 'features'],
            ['marketing', 'Coupons & campaigns', 'Persisted promotions with honest delivery status.', 'Administrators configure percentage coupons with expiration and usage limits. Checkout serializes coupon redemption and snapshots discounts. Newsletter signups persist locally. Campaign records are draft editorial content.

Automated campaign delivery, segmentation, attribution, affiliate commissions and abandoned-cart messaging remain planned.', 'features'],
            ['support-module', 'Customer conversations', 'Support messages visible to the right team.', 'Customers open support tickets. Authorized marketplace support agents reply and resolve them. The reply is visible in the customer account. Contact inquiries are separately stored for administrators.

Seller-to-buyer messaging, attachments, realtime chat and escalation service levels remain planned.', 'features'],
            ['b2b', 'Wholesale quotations', 'Start the business conversation with an explicit quantity.', 'Signed-in customers request wholesale quotations for at least ten units. The seller sees only its own quotations and may enter an offered total. Customers can accept a quoted amount in their account. Active quotations prevent module deactivation.

Acceptance does not automatically charge or reserve stock. Credit limits, negotiation history, contracts and quote-to-order conversion remain planned.', 'features'],
            ['bookings', 'Service appointments', 'Serialized hourly slots for service offers.', 'Service-type offers can accept future hourly appointments. The offer row is locked before checking availability, and a unique constraint protects each offer and start time. Administrators can mark appointments complete.

Staff availability, recurring calendars, deposits, reminders and rescheduling remain planned.', 'features'],
            ['procurement', 'Purchase orders', 'Receive configured quantities into auditable inventory.', 'Authorized operators create a purchase record for an offer and an inventory balance. Receiving the purchase order adds the configured units and records an immutable movement. Repeated receipt does not duplicate the stock.

Supplier invoicing, payment approvals, multi-line purchases and procurement accounting remain planned.', 'features'],
            ['workforce', 'Staff & attendance', 'Scoped personnel records and manual attendance.', 'Seller teams can maintain employee records and manual clock-in/clock-out entries. The application does not collect biometric templates. The owner attendance package is installed for future adapter work, but device ingestion is disabled.

Shift planning, leave, payroll, device synchronization and retention automation remain planned.', 'features'],
            ['ai', 'AI review workspace', 'Sandbox suggestions, with a human in control.', 'The demo includes a clearly labeled local suggestion awaiting review. Authorized administrators can approve the draft and apply its text to the product description. No external inference request or automated financial action occurs.

Provider management, encrypted credentials, usage budgets, content generation requests, catalog imports and recommendations remain planned.', 'features'],
            ['multi-vendor-management', 'Multi Vendor Management', 'A connected foundation for independent marketplace businesses.', 'Working capabilities include scoped marketplace data, seller offers, stock movements, customer carts, multi-vendor checkout, sandbox payments, manual shipping, item returns and balanced financial journals.

Four Filament panels give platform, marketplace, seller and branch teams distinct entry points. Modules can be enabled and disabled subject to dependency and transaction checks. Advanced capabilities and external provider integrations are tracked in the project feature matrix.', 'features'],
            ['multi-branch-management', 'Multi Branch Management', 'Warehouse balances explained by auditable movements.', 'Stock is held by offer and warehouse. Adjustments and direct transfers generate immutable movement records. Checkout reserves units atomically; capture consumes reserved stock; unpaid cancellation releases it.

Cycle counts, in-transit transfer approval, serial/lot/expiry tracking and valuation layers remain planned.', 'features'],
            ['warehouse-management', 'Warehouse Management', 'Warehouse balances explained by auditable movements.', 'Stock is held by offer and warehouse. Adjustments and direct transfers generate immutable movement records. Checkout reserves units atomically; capture consumes reserved stock; unpaid cancellation releases it.

Cycle counts, in-transit transfer approval, serial/lot/expiry tracking and valuation layers remain planned.', 'features'],
            ['order-management', 'Order Management', 'One customer journey across independent sellers.', 'A persisted cart holds seller offers and quantities. Checkout locks stock, validates published offers and approved vendors, applies an eligible percentage coupon, snapshots prices and commissions and creates vendor suborders. A unique idempotency key prevents repeated checkout.

Guest order access is tied to the browser session. Authenticated customers can revisit their orders. Gift cards, credits, loyalty, cart recovery and sophisticated shipping selections remain planned.', 'features'],
            ['commission-management', 'Commission Management', 'A verified internal sandbox boundary.', 'The sandbox adapter confirms pending orders, validates the expected amount and currency, records an idempotent payment event, consumes reservations and posts a balanced journal. Signed sandbox webhooks are supported when a secret is configured.

No real card, wallet, bank transfer, split-payment gateway or production refund adapter is configured. Sandbox payments never move money.', 'features'],
            ['vendor-management', 'Vendor Management', 'Bring your perspective. We will bring the marketplace.', 'Create a customer account, then submit your store name and a description of your business from the account page. Your application begins in pending review.

A marketplace administrator reviews and approves the store and assigns a seller account. Sellers can then manage offers, locations, stock and eligible vendor orders within their authorized scope.

The demo is not a commercial onboarding service. Identity verification, bank onboarding and production payout integrations remain prerequisites for a live marketplace.', 'features'],
            ['marketing-automation', 'Marketing Automation', 'Persisted promotions with honest delivery status.', 'Administrators configure percentage coupons with expiration and usage limits. Checkout serializes coupon redemption and snapshots discounts. Newsletter signups persist locally. Campaign records are draft editorial content.

Automated campaign delivery, segmentation, attribution, affiliate commissions and abandoned-cart messaging remain planned.', 'features'],
            ['analytics', 'Operational analytics', 'Metrics from the actual marketplace database.', 'Filament dashboard cards show scoped vendor-order counts, captured merchandise value, available units and low-stock locations. Branch panels show warehouse and branch stock totals.

Metrics are based on persisted data. No invented growth statistics or customer logos are displayed. Forecasting, cohort analysis, attribution and advanced financial reporting remain planned.', 'features'],
            ['ai-features', 'Ai Features', 'Sandbox suggestions, with a human in control.', 'The demo includes a clearly labeled local suggestion awaiting review. Authorized administrators can approve the draft and apply its text to the product description. No external inference request or automated financial action occurs.

Provider management, encrypted credentials, usage budgets, content generation requests, catalog imports and recommendations remain planned.', 'features'],
        ];
        $pages[] = ['digital', 'Private digital downloads', 'Purchased files, protected by ownership and time-limited access.', 'Administrators upload original or licensed files into private tenant storage. Authenticated checkout snapshots the asset version. Sandbox capture grants download access for 30 days with a three-download limit. Each link expires after ten minutes and requires the purchasing account. Full refunds revoke access.

Recurring billing, license key pools, guest digital checkout and streaming remain planned.', 'features'];
        foreach ($pages as [$slug,$title,$description,$body,$group]) {
            ContentPage::firstOrCreate(compact('slug'), compact('title', 'description', 'body', 'group'));
        }
    }
}
