# MASTER AI CODING AGENT PROMPT
# MULTI-VENDOR MARKETPLACE — THE AMAZON-STYLE MODEL
## Full-Stack Laravel + Filament Enterprise Business Platform

### YOUR ROLE

You are an elite team of senior software architects, Laravel engineers, Filament specialists, database engineers, e-commerce experts, payment engineers, security architects, UI/UX designers, QA engineers, DevOps engineers, and technical documentation writers.

Your responsibility is to **design, implement, test, document, and deliver a complete, production-ready, enterprise-grade Multi-vendor Marketplace application**.

This is NOT a prototype, static website, CRUD demonstration, dashboard-only project, or collection of unfinished modules.

The software must operate as a complete commercial business solution.

**Application name:** Multi-vendor Marketplace  
**Business concept:** The Amazon-style marketplace model  
**Core platform:** PHP Laravel + Filament  
**Architecture:** Modular, multi-tenant, multi-vendor, multi-branch, multi-warehouse, API-first, localization-ready, SaaS-capable

The system must support several business models, from a small local marketplace to a large international marketplace, without requiring separate application codebases.

Your objective is to create a world-class marketplace product with comprehensive functionality, high performance, excellent usability, strict security, and an extensible architecture.

---

# 1. NON-NEGOTIABLE EXECUTION RULES

1. Implement real, working application functionality.
2. Do not create placeholder buttons, dummy routes, empty dashboards, or unfinished CRUD pages.
3. Do not mark unfinished functionality as completed.
4. Every implemented feature must have the necessary database structures, business logic, authorization, validation, interface, and testing.
5. Complete each feature vertically: database → domain services → authorization → UI/API → tests.
6. Maintain working code throughout development.
7. Do not rewrite functional code unnecessarily.
8. Inspect the existing repository before modifying anything.
9. Treat README.md as the central project feature inventory and documentation.
10. Inspect the README continuously and update it when functionality changes.
11. Create detailed architecture and implementation documentation.
12. No hardcoded business data, pricing, commissions, permissions, or payment credentials.
13. All tenant-scoped data must be securely isolated.
14. All money calculations must use reliable decimal or integer-minor-unit arithmetic, never floating-point arithmetic.
15. Handle concurrent stock reservations, payment webhooks, refunds, and vendor payouts safely.
16. Use database transactions, unique constraints, idempotency, and state machines where appropriate.
17. Ensure all modules can be enabled or disabled through the platform's module management system, subject to dependency and data-integrity restrictions.
18. Create genuinely functional demo accounts and realistic sample business data.
19. Create comprehensive public marketing and documentation pages.
20. Every public feature advertised on the website must correspond to actual implemented functionality.
21. Never fabricate successful API calls, payment settlements, carrier labels, emails, SMS deliveries, or tests.
22. External services must have real integrations where credentials are available, and clearly labeled sandbox/mock implementations for local demonstrations.
23. Run migrations, seeders, tests, code formatting, and production build verification.
24. Never expose secrets, personal information, payment tokens, or sensitive data in logs.
25. Use only compatible, maintained packages and lock dependency versions.
26. Do not leave broken links or nonfunctional menu items.
27. Continue implementation systematically through the documented roadmap instead of stopping after generating the initial scaffolding.
28. Preserve existing data and avoid destructive migrations.
29. Document every unavailable external-service prerequisite.
30. A feature is complete only when it satisfies its acceptance criteria.

---

# 2. TECHNICAL STACK

Use the following technology stack:

| Layer | Technology |
|---|---|
| Programming language | PHP 8.3+ |
| Framework | Laravel 12 preferred |
| Administration | Compatible Filament release |
| Frontend | Laravel Blade + Livewire + Alpine.js |
| Styling | Tailwind CSS |
| Database | PostgreSQL preferred, MySQL supported |
| Caching | Redis |
| Queue management | Laravel queues + Horizon where compatible |
| Authentication | Laravel authentication + Sanctum |
| Authorization | Policies, Gates, tenant-scoped RBAC |
| API | Versioned REST API + OpenAPI |
| Search | Laravel Scout with configurable engines |
| Realtime | Laravel Reverb or compatible provider |
| File storage | Local, S3-compatible, configurable |
| Payment processing | Provider adapter architecture |
| Email | Laravel Mail |
| PDF | Unicode PDF integration |
| Testing | Pest/PHPUnit + browser tests |
| Monitoring | Application logs, health checks, telemetry |
| Deployment | Docker + standard Linux hosting |
| CI/CD | Automated GitHub Actions pipeline |

Choose supported versions after running Composer compatibility checks.

Do not assume a package's stated compatibility guarantees actual interoperability.

Use a modular monolith as the primary architecture.

Structure modules so that selected services can eventually be extracted into independent services if scaling requirements justify it.

Do not prematurely create hundreds of microservices.

---

# 3. REQUIRED THIRD-PARTY PACKAGES

Prioritize the following official packages supplied by the project owner.

## 3.1 Performance optimization

Package:

https://packagist.org/packages/imrandevbd/drutogoti

Composer:

`composer require imrandevbd/drutogoti`

Use it where compatible for:

- Application performance profiling
- Query performance diagnostics
- N+1 query detection
- Cache monitoring
- Slow query analysis
- Performance recommendations
- Background diagnostics
- Production-safe request sampling

Create a secured performance monitoring section for platform administrators.

Do not expose profiler diagnostics to public users.

## 3.2 Administration package

Package:

https://packagist.org/packages/imrandevbd/laravel-admin-panel

Preferred installation:

`composer require imrandevbd/laravel-admin-panel`

IMPORTANT:

The application must remain Filament-first.

The package advertises itself as an independent administration framework.

Before installing, inspect its routing, authentication, permissions, asset loading, service providers, UI components, and dashboard features.

Avoid duplicate admin authentication, conflicting authorization systems, duplicate dashboards, and competing route registrations.

Only integrate useful compatible features, or write isolated adapters when necessary.

If the package conflicts with Filament, retain Filament as the primary administration framework, record the incompatibility, and continue using equivalent native Filament functionality.

## 3.3 AI product catalog

Package:

https://packagist.org/packages/imrandevbd/laravel-ai-catalog

Composer:

`composer require imrandevbd/laravel-ai-catalog`

Integrate its documented capabilities where compatible:

- AI product listing generation
- Product descriptions
- SEO optimization
- Product categorization
- Product data extraction
- Supported online data imports
- Product image analysis
- Product metadata
- Multilingual catalogs
- Bulk catalog enrichment
- Duplicate product detection
- Product quality review

External data collection must respect source permissions, applicable terms, robots policies where relevant, and intellectual property rights.

Do not publish copied or unlicensed product content automatically.

All imported products must pass validation and review before publication.

## 3.4 Multi-provider AI

Package:

https://packagist.org/packages/imrandevbd/laravel-ai-hub

Composer:

`composer require imrandevbd/laravel-ai-hub`

Create an AI provider management module with supported provider adapters.

Support:

- AI content generation
- Product recommendations
- Customer-support assistance
- Vendor onboarding assistance
- Search assistance
- Product categorization
- Fraud-risk signals
- Inventory forecasting
- Sales forecasting
- Translation assistance
- Analytics summaries
- Marketing copy generation
- AI usage tracking
- Cost monitoring
- Provider fallback
- Monthly AI budgets
- Permission-controlled AI access

External AI credentials must be encrypted.

AI-generated recommendations must not autonomously perform financial actions, approve refunds, or alter vendor balances.

All high-impact AI actions must require explicit authorization and human review.

## 3.5 Attendance machine integration

Package:

https://packagist.org/packages/imrandevbd/laravel-attendance-hub

Composer:

`composer require imrandevbd/laravel-attendance-hub`

Use this in the optional staff, branch, warehouse, and workforce modules.

Support compatible device integrations for:

- Employee attendance
- Shift management
- Work-hour tracking
- Branch attendance
- Warehouse attendance
- Device management
- Synchronization history
- Attendance reports
- Manual corrections
- Approved leave adjustments

Implement informed permission, data minimization, retention policies, and appropriate controls for biometric-related data.

Never expose biometric templates through ordinary staff interfaces.

## 3.6 Multilingual PDF

Package:

https://packagist.org/packages/imrandevbd/laravel-unicode-pdf

Composer:

`composer require imrandevbd/laravel-unicode-pdf`

Use for:

- Invoices
- Purchase orders
- Credit notes
- Vendor statements
- Settlement reports
- Packing slips
- Shipping documents
- Quotations
- Tax documents
- Financial reports
- Payroll reports
- Return documents
- Branch reports

Support Unicode languages and RTL text according to the selected rendering engine's tested capabilities.

PDF documents must have printable layouts and configurable branding.

## 3.7 Multilingual slugs

Package:

https://packagist.org/packages/imrandevbd/laravel-universal-slug

Composer:

`composer require imrandevbd/laravel-universal-slug`

Use for:

- Product URLs
- Vendor store URLs
- Category URLs
- Brand URLs
- CMS pages
- Blog articles
- Landing pages
- Campaigns

Support multilingual slugs, collision handling, canonical URLs, URL history, and permanent redirects.

---

# 4. SYSTEM ARCHITECTURE

Create a modular multi-tenant marketplace platform.

The application must accommodate:

1. Platform owner
2. Platform administration team
3. Independent marketplace businesses
4. Marketplace business administrators
5. Vendors/sellers
6. Vendor organizations
7. Vendor branches
8. Vendor warehouses
9. Vendor staff
10. Fulfillment operators
11. Delivery partners
12. Marketplace customers
13. Wholesale customers
14. Corporate buyers
15. Affiliate partners
16. Customer-support agents
17. Accountants
18. Marketing teams
19. Warehouse managers
20. Auditors

## Required hierarchy

Platform  
→ Marketplace organization/tenant  
→ Vendors  
→ Vendor branches  
→ Warehouses  
→ Inventory locations  
→ Products/Offers  
→ Orders  
→ Fulfillment  
→ Settlements

Customers can interact with multiple vendors through one storefront.

Different marketplace tenants must never see each other's private business data.

Vendor employees must only access permitted vendor and branch information.

A platform super-admin may access authorized administration functions across tenants, with audited administrative access.

Do not assume that every tenant is a seller.

Model marketplace ownership and seller ownership as distinct concepts.

---

# 5. MULTI-TENANCY

Implement proper multi-tenant architecture.

Required features:

- Tenant creation
- Tenant onboarding
- Tenant subscription plans
- Tenant domains
- Custom domains
- Tenant branding
- Tenant-specific settings
- Tenant currencies
- Tenant languages
- Tenant time zones
- Tenant permissions
- Tenant modules
- Tenant quotas
- Tenant billing
- Tenant storage isolation
- Tenant media isolation
- Tenant notification configurations
- Tenant payment configurations
- Tenant email templates
- Tenant data exports
- Tenant suspension
- Tenant reactivation
- Tenant archival
- Tenant deletion workflows
- Tenant audit logs
- Tenant usage analytics

Use centralized platform identity only where appropriate.

Ensure every query, job, export, upload, authorization check, and event handler uses the correct tenant context.

Add automated cross-tenant data leakage tests.

Provide a development tenant switcher that is available only to authorized platform administrators.

---

# 6. DYNAMIC MODULE MANAGEMENT

This is one of the most important requirements.

Create a complete **Module Management Engine**.

Every non-core business capability must be independently configurable.

## Module levels

1. Platform-wide
2. Tenant-wide
3. Vendor-wide
4. Branch-wide
5. Subscription-plan-based
6. Role-based
7. Permission-based

## Every module must support

- Unique identifier
- Name
- Description
- Category
- Version
- Installation status
- Activation status
- Scope
- Dependencies
- Conflicts
- Permission requirements
- Configuration schema
- Feature limits
- Subscription entitlement
- Navigation registration
- Route registration
- Background jobs
- Scheduled jobs
- Events and listeners
- Health checks
- Audit events

## Module activation/deactivation

Admins must be able to switch modules on or off from the dashboard.

Example:

Marketplace A enables:

- Multi-vendor selling
- Product catalog
- Shopping cart
- Payments
- Delivery
- Reviews

Marketplace B enables:

- B2B wholesale
- Quotations
- Purchase orders
- Credit limits
- Multiple warehouses

Marketplace C enables:

- Digital products
- Subscriptions
- Affiliate marketing
- AI recommendations

Marketplace D enables:

- Grocery
- Local delivery
- Branch-based inventory
- Pickup scheduling
- Delivery slots

All use the same application.

## Important behavior

Disabling a module must:

1. Check dependencies.
2. Check active transactions.
3. Prevent data corruption.
4. Preserve historical financial records.
5. Prevent new operations that require the module.
6. Handle queued jobs safely.
7. Remove inaccessible navigation.
8. Block unauthorized module routes and APIs.
9. Respect existing obligations, including orders, refunds, subscriptions, and settlements.
10. Support reactivation without destroying data.

Core authentication, authorization, accounting integrity, audit logging, tenant isolation, and existing-order history cannot be disabled in a way that breaks security or legal obligations.

Create a Module Registry and Module Manager.

Use interfaces/contracts for modules.

Implement dependency graphs and configuration validation.

Provide a Filament Module Management Resource with searchable tables, module cards, status indicators, dependency information, and activation controls.

---

# 7. COMPLETE PUBLIC WEBSITE

Build a premium responsive public marketing website.

Do not create only a basic homepage.

Required marketing pages:

1. Home
2. Features
3. All Modules
4. How It Works
5. Solutions
6. Marketplace for Retail
7. Marketplace for Fashion
8. Marketplace for Electronics
9. Marketplace for Grocery
10. Marketplace for Furniture
11. Marketplace for Wholesale
12. Marketplace for Digital Products
13. Marketplace for Local Businesses
14. Multi-Vendor Management
15. Multi-Branch Management
16. Inventory Management
17. Warehouse Management
18. Order Management
19. Payment Management
20. Shipping Management
21. Commission Management
22. AI Features
23. Vendor Management
24. Marketing Automation
25. Analytics
26. Security
27. Integrations
28. Pricing
29. Pricing Comparison
30. Free Demo
31. Customer Stories
32. Case Studies
33. FAQ
34. About Us
35. Contact
36. Support
37. Help Center
38. Documentation
39. Developer API
40. Blog
41. Changelog
42. Roadmap
43. Privacy Policy
44. Terms of Service
45. Cookie Policy
46. Vendor Agreement
47. Refund Policy
48. Careers
49. Become a Partner
50. Request a Demo

The public website must be driven by configurable content where practical.

Each major module must have its own feature page.

## Homepage design

Create an elegant, international-grade SaaS homepage with:

- Announcement bar
- Premium header
- Mega menu
- Hero section
- Interactive application previews
- Feature highlights
- Marketplace workflow illustrations
- Business solution categories
- Module showcase
- Dashboard preview
- Multi-vendor overview
- Multi-branch overview
- AI capabilities
- Integration showcase
- Pricing preview
- Demo section
- Testimonials
- FAQ
- Call-to-action sections
- Footer
- Newsletter subscription
- Responsive mobile navigation

Avoid fake statistics, fabricated client logos, and invented testimonials.

Use clearly labeled demonstration content until real business evidence is available.

## Public SEO

Implement:

- Editable SEO titles
- Meta descriptions
- OpenGraph metadata
- Canonical URLs
- XML sitemaps
- Robots configuration
- Structured data
- Breadcrumbs
- Hreflang where needed
- Semantic HTML
- Responsive images
- Optimized page loading
- Search engine friendly URLs
- Automatic redirects
- Blog SEO
- Product SEO
- Category SEO
- Vendor storefront SEO

---

# 8. PUBLIC MARKETPLACE STOREFRONT

Create a complete customer-facing marketplace similar in business functionality to major multi-vendor commerce platforms.

Do not copy Amazon's protected branding, images, or proprietary design.

Required pages:

- Marketplace homepage
- Product discovery
- Category listing
- Subcategory listing
- Brand pages
- Seller stores
- Seller profiles
- Product detail pages
- Product comparison
- Search results
- Advanced filters
- Trending products
- Recently viewed products
- Recommended products
- New arrivals
- Deals
- Flash sales
- Seasonal promotions
- Gift cards
- Wishlist
- Shopping cart
- Checkout
- Guest checkout
- Order confirmation
- Customer dashboard
- Order history
- Order tracking
- Returns
- Refund status
- Seller messaging
- Support tickets
- Saved addresses
- Saved payment method tokens
- Notifications
- Loyalty dashboard
- Coupons
- Product reviews
- Store reviews

## Customer experience

Implement:

- Responsive layout
- Mobile-first navigation
- Optimized product image galleries
- Product variants
- Pricing and discounts
- Availability indicators
- Estimated delivery information
- Product specifications
- Seller details
- Fulfillment method
- Shipping rules
- Warranty information
- Return eligibility
- Related products
- Verified-purchase reviews
- Helpful review voting
- Product questions and answers
- Multiple seller offers
- Product recommendations

Customer actions must update real application state.

---

# 9. PRODUCT INFORMATION MANAGEMENT

Create a comprehensive Product Information Management (PIM) module.

Required capabilities:

- Master product catalog
- Seller-specific offers
- Product drafts
- Product approvals
- Product categories
- Nested category hierarchy
- Brand management
- Product types
- Physical products
- Digital products
- Services where applicable
- Product variants
- Variant combinations
- Attributes
- Attribute groups
- Specifications
- Product identifiers
- SKU
- Barcode
- GTIN
- MPN
- Unit of measure
- Dimensions
- Weight
- Product condition
- Warranty
- Manufacturer
- Country of origin
- Product images
- Videos
- Downloadable files
- Product bundles
- Product kits
- Related products
- Cross-selling
- Upselling
- Product tagging
- Product collections
- Search keywords
- Product visibility
- Product publication schedules
- Product availability
- Product version history
- Bulk editing
- Bulk imports
- Bulk exports
- Duplicate detection
- Inventory-linked product status
- Multiple currencies
- Localization
- Product moderation
- Content approval

## Master catalog versus vendor offers

A master catalog item must not automatically be treated as a vendor-owned product.

Support multiple sellers offering the same master catalog item with different:

- Prices
- Inventory
- Shipping methods
- Fulfillment locations
- Delivery promises
- Return policies
- Seller-specific warranties
- Promotions

Where appropriate, build an offer-selection mechanism based on configurable eligibility, customer experience, pricing, stock, and fulfillment rules.

Do not create misleading product rankings or fabricated ratings.

---

# 10. VENDOR AND SELLER MANAGEMENT

Create an entire vendor business administration system.

## Vendor onboarding

- Seller registration
- Business profile
- Contact details
- Business type
- Organization information
- Tax details
- Identity verification integration points
- Document submissions
- Store branding
- Store slug
- Bank/payout onboarding
- Marketplace agreement acceptance
- Approval workflow
- Risk review
- Seller activation
- Seller suspension
- Seller appeal workflow

Implement lawful and proportionate verification processes.

Sensitive identity documents must have restricted access and retention controls.

## Vendor management

- Vendor dashboard
- Store settings
- Storefront customization
- Staff management
- Role management
- Branch management
- Product management
- Inventory management
- Order management
- Returns management
- Customer communication
- Shipping management
- Financial statements
- Commission statements
- Payout status
- Sales analytics
- Marketing
- Coupons
- Store performance
- Ratings and reviews
- Seller notifications
- Subscription management
- Integration management

## Seller performance metrics

Track:

- Total sales
- Net sales
- Orders
- Cancellations
- Return rate
- Shipment timeliness
- Seller response time
- Customer satisfaction
- Payment holds
- Disputes
- Average order value
- Product performance
- Fulfillment performance

Metrics must be calculated from real data.

---

# 11. MULTI-BRANCH MANAGEMENT

Implement full multi-branch functionality.

Each vendor may operate:

- Headquarters
- Retail branches
- Showrooms
- Warehouses
- Fulfillment centers
- Pickup points
- Service locations

## Branch features

- Branch creation
- Branch settings
- Branch status
- Branch staff
- Branch manager
- Branch permissions
- Branch working hours
- Branch inventory
- Branch pricing
- Branch-specific promotions
- Branch delivery zones
- Branch pickup
- Branch order assignment
- Branch-level reports
- Branch expenses
- Branch cash management
- Branch performance
- Branch stock transfers
- Branch purchase requests
- Branch operational alerts

## Multi-branch inventory

Support:

- Location-specific stock
- Branch-level stock reservations
- Stock transfers
- Transfer approvals
- In-transit inventory
- Receiving
- Damaged stock
- Stock adjustments
- Stock reconciliation
- Inter-branch replenishment
- Warehouse-to-branch delivery
- Configurable allocation logic

Stock totals must remain accurate under simultaneous transactions.

---

# 12. WAREHOUSE MANAGEMENT

Build a working warehouse management system.

Features:

- Multiple warehouses
- Warehouse zones
- Aisles
- Racks
- Bins
- Storage locations
- Inventory receiving
- Putaway
- Picking
- Packing
- Dispatch
- Barcode operations
- Inventory counts
- Cycle counting
- Batch tracking
- Serial number tracking
- Lot tracking
- Expiry tracking
- Damaged goods
- Returned goods
- Transfer orders
- Reorder points
- Inventory alerts
- Replenishment
- Inventory aging
- Stock valuation
- Inventory movement history
- Warehouse performance
- Packing slips
- Pick lists
- Shipping labels

Inventory operations must generate immutable movement records.

Never rely only on a mutable stock quantity without a corresponding auditable inventory ledger.

---

# 13. SHOPPING CART AND CHECKOUT

Implement a fully functional shopping cart and checkout engine.

Features:

- Guest carts
- Authenticated carts
- Persistent carts
- Cart merging
- Multi-vendor carts
- Multi-warehouse fulfillment
- Coupons
- Store credits
- Gift cards
- Loyalty points
- Shipping selections
- Tax calculation
- Address validation
- Delivery scheduling
- Pickup scheduling
- Checkout validation
- Inventory reservations
- Payment authorization
- Payment confirmation
- Checkout expiration
- Abandoned carts
- Cart recovery
- Duplicate submission protection

One customer cart may contain products from multiple sellers.

The platform must correctly split checkout fulfillment and financial obligations across vendors.

Pricing totals must be reproducible and auditable.

---

# 14. ORDER MANAGEMENT

Create an advanced order management system.

## Order lifecycle

Draft → Pending Payment → Confirmed → Processing → Partially Fulfilled → Fulfilled → Completed

Include controlled alternate transitions such as:

- Failed
- Cancelled
- Partially Cancelled
- Returned
- Partially Returned
- Refunded
- Partially Refunded
- Disputed

Do not implement uncontrolled status strings.

Use explicit order state machines.

## Required order features

- Master orders
- Vendor suborders
- Order line items
- Item-level fulfillment
- Order splitting
- Partial shipping
- Partial cancellation
- Partial returns
- Order notes
- Order attachments
- Internal comments
- Order history
- Order audit trails
- Invoice creation
- Packing slips
- Shipment tracking
- Customer notifications
- Vendor notifications
- Admin intervention
- Payment reconciliation
- Tax snapshots
- Discount allocations
- Shipping allocations
- Commission snapshots
- Refund allocations

All historical monetary calculations must remain stable after product prices, commissions, or taxes change.

---

# 15. PAYMENT MANAGEMENT

Build a provider-independent payment system.

## Payment methods

Support through verified provider integrations:

- Debit and credit cards
- Digital wallets
- Bank transfers
- Cash on delivery
- Local payment gateways
- International gateways
- Stored payment method tokens
- Store credits
- Gift cards
- Partial payments where business rules permit

Potential provider adapters:

- Stripe
- PayPal
- SSLCOMMERZ
- bKash
- Nagad
- Approved local banking/payment integrations
- Additional gateway adapters

Availability must depend on the provider's actual supported countries, merchant eligibility, and capabilities.

Never assume all listed gateways support marketplace split payments.

## Marketplace payment logic

Implement:

- Payment intent records
- Authorization
- Capture
- Payment status
- Payment retry
- Webhook signature validation
- Idempotent webhook processing
- Duplicate payment prevention
- Multi-vendor allocation
- Marketplace commission
- Vendor payable amount
- Refund processing
- Partial refunds
- Chargebacks
- Disputes
- Reconciliation
- Payment audit trails

Create a payment provider interface so gateways can be enabled or disabled without rewriting checkout.

Do not store raw card details.

Use provider-approved tokenization and hosted payment methods.

---

# 16. MARKETPLACE COMMISSION ENGINE

Support flexible commission models:

- Global default commission
- Tenant-level commission
- Vendor-level commission
- Category-based commission
- Product-based commission
- Subscription-based commission
- Percentage commission
- Fixed commission
- Hybrid commission
- Tiered commission
- Promotional commission
- Commission exemptions
- Minimum/maximum commissions

Create effective-date versioned commission rules.

Store applied commission snapshots on financial transactions.

Example:

Product sale: $100  
Marketplace commission: $10  
Vendor payable before other adjustments: $90

The calculation must account for eligible discounts, taxes, shipping, refunds, and fees according to configured business rules.

Provide a commission simulator.

---

# 17. VENDOR WALLET AND SETTLEMENTS

Create a marketplace financial ledger.

Features:

- Vendor balances
- Available balance
- Pending balance
- Reserved balance
- Payout eligible balance
- Refund liability
- Commission deductions
- Settlement schedules
- Settlement batches
- Payout requests
- Approved payout methods
- Payout processing
- Failed payout handling
- Payment reconciliation
- Vendor financial statements
- Transaction exports

Use a double-entry ledger or similarly rigorous balanced accounting design.

Every financial event must be auditable.

Never calculate vendor balances solely by summing mutable order status fields.

Provide payout holds for eligible unresolved disputes and refunds.

Never display a sandbox payout as a real transferred payment.

---

# 18. SHIPPING AND DELIVERY

Implement a comprehensive logistics module.

Features:

- Shipping zones
- Shipping methods
- Shipping rates
- Weight-based shipping
- Dimension-based shipping
- Distance-based shipping
- Free shipping thresholds
- Delivery promises
- Shipping templates
- Vendor shipping policies
- Carrier integrations
- Delivery partner management
- Shipment generation
- Shipment tracking
- Delivery events
- Proof of delivery
- Shipping labels
- Packing slips
- Partial shipments
- Shipment consolidation where feasible
- Failed deliveries
- Return shipments
- Reverse logistics
- Local delivery
- Same-day delivery configuration
- Scheduled delivery
- Pickup points
- Click and collect
- Cash on delivery reconciliation

Carrier rates and delivery ETAs must be retrieved from legitimate integrations or calculated from clearly labeled configurable rules.

Never fabricate live carrier quotations.

---

# 19. RETURNS, REFUNDS, AND DISPUTES

Create an end-to-end return and refund module.

Include:

- Return requests
- Return eligibility
- Return reasons
- Return evidence
- Return authorization
- Return shipment
- Return reception
- Item inspection
- Refund calculation
- Partial refunds
- Replacement orders
- Exchange workflows
- Store-credit refunds
- Refund status tracking
- Dispute escalation
- Vendor responses
- Admin decisions
- Audit logs

Refunds must correctly reverse eligible commissions, vendor liabilities, taxes, and other financial amounts based on documented marketplace policies.

Ensure that external payment refund requests are idempotent.

---

# 20. B2B AND WHOLESALE MARKETPLACE

Create an optional B2B commerce module.

Features:

- Business customer registration
- Company profiles
- Buyer organizations
- Multi-user buyer accounts
- Procurement roles
- Bulk pricing
- Wholesale price tiers
- Minimum order quantities
- Volume discounts
- RFQ (Request for Quotation)
- Vendor quotations
- Quotation negotiation
- Purchase orders
- Purchase order approvals
- Payment terms
- Credit-limit integration
- Credit approval workflows
- Repeat orders
- Contract pricing
- Customer-specific price lists
- Tax document support
- Wholesale invoices
- B2B analytics

Credit terms must depend on proper authorization and applicable legal requirements.

---

# 21. DIGITAL PRODUCTS AND SUBSCRIPTIONS

Create optional digital commerce functionality.

Features:

- Digital files
- Secure downloads
- License keys
- Download limits
- Download expiration
- Digital order fulfillment
- Subscription products
- Recurring billing
- Memberships
- Renewal notifications
- Cancellation management
- Subscription upgrades
- Subscription downgrades
- Access entitlements
- Customer license history

Enforce rights to distribute uploaded digital content.

Use private storage and authorized time-limited download access.

---

# 22. BOOKING AND SERVICE MARKETPLACE

Create an optional service marketplace module.

Features:

- Service listings
- Service providers
- Availability schedules
- Appointments
- Booking calendars
- Service packages
- Booking deposits where supported
- Cancellation rules
- Rescheduling
- Staff assignment
- Branch assignment
- Appointment reminders
- Service completion
- Service reviews
- Service reports

Service bookings must have concurrency-safe availability checks.

---

# 23. PRICING, PROMOTIONS, AND DISCOUNTS

Implement an advanced pricing engine.

Features:

- Base price
- Sale price
- Scheduled prices
- Vendor-specific prices
- Branch-specific prices
- Customer group pricing
- B2B pricing
- Currency pricing
- Coupon codes
- Percentage discounts
- Fixed discounts
- First-order coupons
- Vendor coupons
- Category coupons
- Product coupons
- Minimum spend
- Maximum discounts
- Coupon usage limits
- Expiration
- Flash sales
- Bundle discounts
- Buy X Get Y
- Loyalty rewards
- Gift cards
- Referral credits
- Free shipping offers
- Promotion stacking rules

Promotions must have deterministic stacking and allocation behavior.

Test conflicting promotions and concurrency-related usage-limit enforcement.

---

# 24. CUSTOMER RELATIONSHIP MANAGEMENT

Build a customer management module.

Features:

- Customer profiles
- Customer groups
- Customer purchase history
- Customer lifetime value
- Customer segmentation
- Customer notes
- Customer communication history
- Customer service cases
- Customer tagging
- Loyalty status
- Marketing preferences
- Email subscriptions
- Consent management
- Customer support history
- Customer exports
- Data access requests
- Data deletion workflows

Respect tenant boundaries and privacy requirements.

---

# 25. CUSTOMER SUPPORT AND COMMUNICATION

Create a unified support system.

Features:

- Support tickets
- Ticket categories
- Ticket priorities
- Ticket statuses
- Customer-to-vendor messaging
- Customer-to-platform messaging
- Internal agent notes
- Attachments
- Agent assignment
- SLA tracking
- Ticket escalation
- Customer satisfaction surveys
- Knowledge base
- Canned responses
- AI-assisted replies
- Email notifications
- In-app notifications
- Chat integrations
- Support analytics

Support messages must not leak information between different vendors or tenants.

Provide moderation and reporting tools.

---

# 26. AI MARKETPLACE FEATURES

Implement an optional AI functionality suite.

Use the AI Hub package through a centralized AI service abstraction.

## AI shopping assistant

- Natural-language product search
- Product discovery
- Product comparison summaries
- Shopping assistance
- Product question answering
- Contextual recommendations

## AI vendor assistant

- Listing generation
- SEO descriptions
- Product categorization
- Attribute extraction
- Translation
- Marketing copy
- Product quality checks
- Listing improvement suggestions

## AI business assistant

- Inventory forecasting
- Demand forecasting
- Sales insights
- Low-stock recommendations
- Customer service response drafting
- Order anomaly detection
- Operational summaries
- Vendor performance insights

## AI controls

- Provider management
- Model selection
- API key management
- Prompt templates
- Usage monitoring
- Token/cost reporting
- Budget enforcement
- Permission management
- Failure handling
- AI response caching where safe
- Audit logs
- Human approval for consequential actions

Never represent probabilistic AI output as guaranteed fact.

AI suggestions must not bypass core business rules or authorization.

---

# 27. SEARCH AND PRODUCT DISCOVERY

Implement advanced marketplace search.

Features:

- Full-text search
- Typo tolerance
- Product suggestions
- Autocomplete
- Category filters
- Brand filters
- Price filters
- Rating filters
- Availability filters
- Vendor filters
- Branch filters
- Location filters
- Attribute filters
- Sorting
- Pagination
- Search history where consented
- Trending searches
- Search analytics
- Search synonyms
- Multilingual search
- Search result relevance tuning
- Search indexing jobs

Search results must respect unpublished products, tenant visibility, stock rules, and seller restrictions.

---

# 28. RATINGS, REVIEWS, AND MARKETPLACE TRUST

Implement:

- Product reviews
- Seller reviews
- Verified-purchase badges
- Review moderation
- Rating summaries
- Review photos
- Helpful votes
- Seller responses
- Review reporting
- Abuse prevention
- Fraudulent review detection signals
- Review appeals
- Moderation history

Never generate fake customer reviews or testimonials.

Do not allow vendors to alter authentic customer ratings.

---

# 29. MARKETING AUTOMATION

Create a marketing management module.

Features:

- Campaign manager
- Email campaigns
- Notification campaigns
- Customer segments
- Coupon campaigns
- Seasonal promotions
- Automated abandoned-cart reminders
- Welcome sequences
- Post-purchase messages
- Review request campaigns
- Customer win-back campaigns
- Campaign scheduling
- Marketing templates
- Performance analytics
- Conversion tracking
- Attribution tracking
- Consent and unsubscribe controls

Include rate limits and anti-spam safeguards.

---

# 30. AFFILIATE AND REFERRAL MANAGEMENT

Optional affiliate module:

- Affiliate registration
- Affiliate approval
- Referral links
- Tracking codes
- Campaign tracking
- Conversion tracking
- Referral attribution
- Affiliate commissions
- Commission approval
- Affiliate payouts
- Refund-related reversals
- Fraud detection
- Affiliate dashboard
- Affiliate statements
- Program settings

Disclose affiliate relationships appropriately.

---

# 31. HUMAN RESOURCES AND STAFF

Create an optional workforce management system.

Features:

- Employee directory
- Staff onboarding
- Employment records
- Department management
- Designations
- Branch assignment
- Shift schedules
- Attendance
- Leave management
- Overtime requests
- Holiday calendars
- Staff performance records
- Permission management
- Payroll integration points
- Employee reports

Integrate the attendance package through an adapter.

Employee information must be protected by HR-specific permissions.

---

# 32. PROCUREMENT AND SUPPLIER MANAGEMENT

Create an optional purchasing system.

Features:

- Supplier records
- Supplier categories
- Supplier quotations
- Purchase requisitions
- Purchase orders
- Approval workflows
- Goods receiving
- Supplier invoices
- Purchasing expenses
- Stock replenishment
- Purchase returns
- Supplier balances
- Procurement analytics

Keep procurement vendors distinct from marketplace selling vendors where business semantics differ.

---

# 33. ACCOUNTING AND FINANCIAL REPORTS

Implement a configurable accounting module.

Core financial records must remain valid independently of optional accounting UI features.

Support:

- Chart of accounts
- General ledger
- Journal entries
- Accounts receivable
- Accounts payable
- Marketplace clearing accounts
- Commission revenue
- Vendor liabilities
- Payment fees
- Refund liabilities
- Tax liabilities
- Bank accounts
- Bank reconciliation
- Expense categories
- Income statements
- Balance sheets
- Cash flow reports
- Trial balances
- Tax reports
- Financial exports

Accounting rules must use balanced entries.

Period locks and correction entries must preserve auditability.

Never silently edit posted ledger transactions.

---

# 34. TAX AND MULTI-CURRENCY

Create an extensible tax engine.

Support:

- Tax classes
- Tax rules
- Tax regions
- Tax-inclusive pricing
- Tax-exclusive pricing
- Vendor tax configuration
- Marketplace tax configuration
- Tax exemptions
- Tax registration details
- Tax invoices
- Tax adjustments
- Refund tax reversals
- Tax exports

Support international currencies, including BDT, USD, EUR, and GBP as initial examples.

Currency settings must support:

- Currency codes
- Currency symbols
- Decimal precision
- Exchange rates
- Historical exchange-rate snapshots
- Conversion rules
- Rounding rules
- Price display preferences

Do not assume one global tax rule applies to every jurisdiction.

Leave tax-specific legal decisions configurable and reviewable.

---

# 35. SAAS SUBSCRIPTIONS AND PLATFORM BILLING

The platform must support selling marketplace software access to multiple businesses.

## Subscription plans

Examples:

- Starter
- Growth
- Professional
- Enterprise
- Custom

Plan features:

- Vendor limits
- Branch limits
- Warehouse limits
- Product limits
- Staff limits
- Order limits
- Storage limits
- AI usage allowances
- Feature entitlements
- API limits
- Support levels
- Billing cycles

## Billing features

- Trial periods
- Monthly billing
- Annual billing
- Upgrade
- Downgrade
- Renewal
- Cancellation
- Failed payment recovery
- Grace periods
- Proration
- Billing invoices
- Usage reporting
- Subscription webhooks
- Subscription suspension
- Entitlement enforcement

Never delete business records automatically when a plan expires.

Separate platform SaaS subscription billing from customer marketplace checkout payments.

---

# 36. SUPER ADMIN PANEL

Create a dedicated Filament Super Admin panel.

Suggested route:

`/super-admin`

Modules:

- Platform overview
- Tenant management
- Vendor management
- Customer management
- Subscription management
- Module management
- Plans and billing
- Global categories
- Global product moderation
- Seller applications
- Orders overview
- Payments
- Refunds
- Commissions
- Settlements
- Financial ledger
- Shipping integrations
- Marketing
- CMS
- AI settings
- Performance analytics
- Support
- Notifications
- System logs
- Audit logs
- Permissions
- Security
- Integrations
- System settings
- Backup monitoring
- Job monitoring
- Health dashboard

Create real dashboard widgets with working drill-down links.

---

# 37. TENANT ADMIN PANEL

Suggested route:

`/admin`

The tenant administrator must be able to configure:

- Business profile
- Marketplace theme
- Vendors
- Branches
- Warehouses
- Products
- Orders
- Customers
- Payments
- Commissions
- Shipping
- Marketing
- Analytics
- Modules
- Permissions
- Subscription
- Staff
- Notifications
- Website content
- Integrations

All resources must be tenant-scoped.

---

# 38. VENDOR PANEL

Suggested route:

`/vendor`

Include:

- Vendor dashboard
- Storefront settings
- Product management
- Inventory
- Warehouses
- Branches
- Orders
- Shipments
- Returns
- Refund status
- Finance
- Payouts
- Statements
- Coupons
- Analytics
- Staff
- Support
- AI assistant
- Notifications

Vendors must not access platform-only controls.

---

# 39. BRANCH OPERATIONS PANEL

Suggested route:

`/branch`

Include:

- Branch dashboard
- Branch stock
- Assigned orders
- Picking
- Packing
- Transfers
- Receiving
- Dispatch
- Local delivery
- Staff schedules
- Attendance
- Reports

Enforce branch permissions on every read and write.

---

# 40. CUSTOMER ACCOUNT AREA

Suggested route:

`/account`

Include:

- Profile
- Addresses
- Orders
- Tracking
- Wishlist
- Returns
- Refunds
- Support
- Reviews
- Coupons
- Loyalty
- Subscriptions
- Security settings
- Communication preferences

A customer must never access another customer's order information.

---

# 41. ROLE-BASED ACCESS CONTROL

Create centralized permission management.

Example roles:

- Platform Super Admin
- Platform Admin
- Platform Support
- Tenant Owner
- Tenant Admin
- Tenant Finance Manager
- Vendor Owner
- Vendor Manager
- Vendor Accountant
- Vendor Product Manager
- Branch Manager
- Warehouse Manager
- Warehouse Operator
- Delivery Operator
- Marketing Manager
- Customer Support Agent
- Auditor
- Customer
- Corporate Buyer

Support custom roles.

Every Filament resource, API endpoint, export, dashboard widget, action, and background operation must apply appropriate authorization.

Do not rely on menu visibility alone for security.

---

# 42. CMS AND WEBSITE BUILDER

Create an admin-configurable CMS.

Features:

- Pages
- Content blocks
- Hero banners
- Navigation menus
- Footers
- FAQ
- Testimonials
- Team pages
- Contact forms
- Blog
- Categories
- Tags
- Landing pages
- SEO fields
- Scheduled publication
- Draft previews
- Media library
- Reusable sections
- Legal policy pages
- Template management
- Revision history

Provide a safe component-based page builder where feasible.

Do not allow unrestricted executable HTML, JavaScript, or unsafe file uploads through the CMS.

---

# 43. NOTIFICATION SYSTEM

Create a unified event-driven notification center.

Channels:

- Email
- In-app
- SMS through configured gateways
- Push notifications where supported
- Webhook notifications
- Messaging-provider adapters

Events:

- Registration
- Vendor approval
- New order
- Payment confirmation
- Order cancellation
- Shipment
- Delivery
- Refund
- Dispute
- Low stock
- Payout
- Subscription renewal
- Failed payment
- Support messages
- Security alerts

Features:

- Template editor
- Localization
- Notification preferences
- Scheduled notifications
- Delivery retries
- Delivery failures
- Suppression rules
- Rate limits
- Notification history

---

# 44. REPORTS AND ANALYTICS

Build comprehensive dashboards.

## Platform analytics

- Gross merchandise value
- Net sales
- Marketplace revenue
- Commission revenue
- Active vendors
- Active customers
- Order trends
- Conversion rates
- Refund rates
- Category performance
- Subscription revenue
- Payment performance
- Vendor settlement liability

## Vendor analytics

- Sales
- Profit indicators
- Order counts
- Top products
- Inventory
- Returns
- Promotions
- Customer metrics
- Settlement reports
- Branch comparisons

## Warehouse analytics

- Stock movement
- Order fulfillment
- Picking performance
- Inventory aging
- Stock discrepancies
- Replenishment

## Reports

Support:

- Date filters
- Vendor filters
- Branch filters
- Warehouse filters
- Currency selection
- CSV exports
- Excel exports
- PDF exports
- Scheduled reports
- Permission-controlled reports

Create efficient aggregate queries or materialized summaries where necessary.

---

# 45. INTEGRATION MANAGEMENT

Create a centralized integration manager.

Potential integrations:

- Payment gateways
- Shipping providers
- Email services
- SMS providers
- AI providers
- ERP systems
- Accounting software
- CRM systems
- Analytics services
- Cloud storage
- Maps and geocoding
- Attendance devices
- Webhook consumers
- Marketplace feeds

Every integration should have:

- Name
- Provider
- Configuration
- Credentials
- Encrypted secrets
- Enable/disable control
- Connection test
- Health state
- Retry policy
- Audit events
- Error handling
- Sandbox/live mode
- Documentation

Never assume a successful configuration means an external service is operational without testing its actual connection.

---

# 46. REST API AND DEVELOPER PLATFORM

Build an API-first architecture.

Use versioned endpoints:

`/api/v1/`

Core APIs:

- Authentication
- Tenants
- Vendors
- Branches
- Products
- Product offers
- Categories
- Inventory
- Carts
- Checkout
- Orders
- Payments
- Shipments
- Returns
- Customers
- Reviews
- Commissions
- Settlements
- Reports
- Webhooks
- Integrations

Create:

- OpenAPI documentation
- Authentication examples
- Pagination standards
- Error response standards
- API rate limiting
- API token scopes
- Idempotency support
- Webhook signatures
- Webhook retries
- Webhook delivery history
- API audit logs

Provide a developer documentation page on the public website.

---

# 47. MULTILINGUAL AND INTERNATIONAL SUPPORT

Support:

- English
- Bangla
- Arabic
- Hindi
- Additional languages through translation files

Create language-switching functionality.

Support:

- Unicode content
- RTL layouts
- Translation management
- Localized emails
- Localized PDFs
- Localized storefronts
- Localized product information
- Localized URLs
- Date formatting
- Currency formatting
- Time zones
- International addresses
- Country-specific configurations

Never hardcode English-only validation messages or labels.

---

# 48. THEMING AND WHITE-LABEL CAPABILITIES

Implement:

- Platform branding
- Tenant branding
- Vendor store branding
- Custom logos
- Favicons
- Color palettes
- Typography settings
- Dark mode
- Light mode
- Header layouts
- Footer layouts
- Storefront themes
- Banner configuration
- Homepage sections
- Custom domains
- Email branding
- PDF branding

Themes must not allow unsafe code execution.

---

# 49. SECURITY REQUIREMENTS

Implement modern application security throughout the system.

Required controls:

- Password hashing
- Email verification
- Optional MFA
- Passkeys if safely supported
- Session management
- Login rate limiting
- Account lockout safeguards
- CSRF protection
- XSS prevention
- SQL injection prevention
- Secure file uploads
- Signed download URLs
- Policy-based authorization
- Tenant isolation
- Branch isolation
- Encrypted credentials
- Secure webhook handling
- Payment idempotency
- Audit logging
- Sensitive data masking
- Secure password resets
- API token rotation
- Security headers
- Dependency scanning
- Environment secret management
- Least-privilege authorization

Create security tests for:

- Unauthorized panel access
- Cross-tenant data access
- Cross-vendor data access
- IDOR vulnerabilities
- Payment webhook replay
- Duplicate checkout requests
- Unauthorized refunds
- Unauthorized payouts
- Unauthorized file downloads
- Privilege escalation
- Module bypasses

---

# 50. PERFORMANCE AND SCALABILITY

Build for future high traffic.

Implement:

- Database indexes
- Eager loading
- Query optimization
- Redis caching
- Cached storefront data
- Cache invalidation
- Queue-based processing
- Background image processing
- Search indexing queues
- Pagination
- Chunked imports
- Chunked exports
- Efficient bulk operations
- Asynchronous reporting
- CDN compatibility
- Image optimization
- Responsive image sizes
- Query monitoring
- Application health checks
- Horizontal scaling compatibility
- Stateless application workers where possible

Use Drutogoti for supported profiling and diagnostics.

Create reproducible performance benchmarks.

Do not claim specific request throughput without measurement.

---

# 51. DATABASE ARCHITECTURE

Design normalized, indexed, scalable database tables.

Minimum domain groups:

## Platform

- users
- tenants
- tenant_domains
- tenant_settings
- roles
- permissions
- user_role_assignments
- modules
- module_dependencies
- module_configurations
- subscription_plans
- subscriptions
- subscription_invoices
- feature_entitlements

## Vendors and branches

- vendors
- vendor_users
- vendor_documents
- vendor_settings
- branches
- branch_staff
- warehouses
- warehouse_locations

## Catalog

- master_products
- vendor_offers
- categories
- brands
- product_variants
- product_attributes
- product_attribute_values
- product_media
- product_translations
- product_approval_requests
- product_revision_history

## Inventory

- inventory_balances
- inventory_movements
- inventory_reservations
- stock_transfers
- stock_adjustments
- inventory_counts
- purchase_orders
- goods_receipts

## Commerce

- customers
- customer_addresses
- carts
- cart_items
- orders
- vendor_orders
- order_items
- order_status_history
- payments
- payment_attempts
- payment_allocations
- refunds
- return_requests
- return_items
- shipments
- shipment_items
- shipment_events

## Financial

- commission_rules
- commission_records
- ledger_accounts
- ledger_transactions
- ledger_entries
- vendor_settlements
- payout_batches
- payout_items
- payout_attempts
- tax_rules
- invoices
- credit_notes

## Engagement

- reviews
- wishlists
- coupons
- promotions
- notifications
- support_tickets
- support_messages
- marketing_campaigns
- affiliates
- referrals

## Operations

- employees
- attendance_records
- shifts
- suppliers
- procurement_requests
- audit_logs
- integration_connections
- webhook_endpoints
- webhook_deliveries
- AI_usage_records

These are suggested logical tables, not an instruction to create unnecessary duplicate records.

Design the final schema based on correct domain boundaries.

Use appropriate foreign keys, composite indexes, unique constraints, and data-retention rules.

Use immutable financial and inventory event histories.

---

# 52. FILAMENT IMPLEMENTATION

Use Filament as the primary dashboard framework.

Create separate panel providers for:

1. Super Admin
2. Tenant Admin
3. Vendor
4. Branch Operations

Use shared resource classes/services where safe, but never weaken authorization.

Every major module should provide:

- Filament resources
- Forms
- Tables
- Relation managers
- Filters
- Bulk actions
- Detail views
- Navigation
- Dashboard widgets
- Authorization policies
- Validation
- Export/import capabilities where appropriate

Implement responsive dashboards that work on desktop, tablet, and mobile.

Use charts and financial metrics based on real application data.

---

# 53. DEMO ACCOUNTS AND SAMPLE BUSINESS DATA

Create a fully functional demo mode.

## Required demo roles

| Role | Demo email |
|---|---|
| Super Admin | superadmin@demo.test |
| Tenant Owner | owner@demo.test |
| Marketplace Admin | admin@demo.test |
| Vendor Owner A | vendor1@demo.test |
| Vendor Owner B | vendor2@demo.test |
| Vendor Manager | manager@demo.test |
| Branch Manager | branch@demo.test |
| Warehouse Manager | warehouse@demo.test |
| Accountant | finance@demo.test |
| Support Agent | support@demo.test |
| Customer A | customer1@demo.test |
| Customer B | customer2@demo.test |
| Corporate Buyer | business@demo.test |

## Demo password

Use a single documented development-only password:

`Demo@12345`

Never seed known demo passwords in production.

Demo seeders must run only when explicitly requested in a local, test, or separately secured demo environment.

Production deployment must reject the demo account seeder.

## Demo dataset

Create at least:

- 2 marketplace tenants
- 5 vendors
- 12 branches
- 8 warehouses
- 20 categories
- 30 brands
- 200 products
- 300 vendor offers
- 100 customers
- 250 orders
- 50 shipments
- 20 returns
- 30 refunds or partial refunds
- 100 product reviews
- 25 coupons
- 10 campaigns
- 5 subscriptions
- Historical accounting transactions
- Inventory movement records
- Vendor settlement records
- Support tickets
- Notifications
- Staff records

Use realistic sample data clearly labeled as simulated.

Populate coherent relationships, amounts, statuses, timestamps, and transactions.

Do not use actual individuals' private information.

## Demo scenarios

The seeded data must demonstrate:

1. Successful multi-vendor checkout
2. Multi-vendor order splitting
3. Inventory reservations
4. Branch-specific stock
5. Low inventory
6. Vendor approval
7. Commission calculation
8. Payment confirmation
9. Partial shipment
10. Refund
11. Vendor settlement
12. B2B quotation
13. Digital product delivery
14. Customer support ticket
15. Module enabling/disabling
16. Subscription plan restrictions
17. AI sandbox mode
18. Multi-language functionality

## Public demo page

Create:

`/demo`

Show:

- Available demo roles
- Demo capabilities
- Panel entry links
- Demo login instructions
- Important demo restrictions
- Sample business workflows

For a publicly accessible live demo, use isolated disposable demo environments or session-specific demo identities rather than sharing unrestricted privileged accounts.

Disable real payouts, production credentials, destructive administration, external messaging, and other consequential operations in the public demonstration.

---

# 54. DATABASE SEEDERS AND INSTALLATION

Create clear seeders:

- CoreSettingsSeeder
- RolesAndPermissionsSeeder
- ModuleRegistrySeeder
- SubscriptionPlanSeeder
- DemoTenantSeeder
- DemoVendorSeeder
- DemoBranchSeeder
- DemoWarehouseSeeder
- DemoCatalogSeeder
- DemoCustomerSeeder
- DemoInventorySeeder
- DemoOrderSeeder
- DemoFinancialSeeder
- DemoMarketingSeeder

Create an installation command:

`php artisan marketplace:install`

The command should validate the environment, install required structures, create an initial administrator securely, and initialize platform settings.

Create a separate explicit demo command:

`php artisan marketplace:demo-seed`

This command must enforce environment restrictions and allow safe reset of isolated demo data.

---

# 55. REQUIRED README.MD

Create a comprehensive README.md.

It must include:

1. Project introduction
2. Screenshots or verified interface previews
3. Feature overview
4. Complete module directory
5. Module dependency matrix
6. Technology stack
7. Architecture
8. Requirements
9. Installation
10. Configuration
11. Required PHP extensions
12. Composer setup
13. Database setup
14. Redis setup
15. Queue setup
16. Search configuration
17. Mail configuration
18. Storage configuration
19. Payment configuration
20. AI configuration
21. Package integration notes
22. Multi-tenant configuration
23. Multi-branch configuration
24. Demo users
25. Demo environment instructions
26. API documentation
27. Testing
28. Security guidance
29. Deployment
30. Troubleshooting
31. Upgrade instructions
32. Backup and recovery
33. Module development guide
34. Localization guide
35. Contribution guide
36. License
37. Implementation status
38. Known limitations

Every module must be marked with its actual status:

- Planned
- In Progress
- Implemented
- Tested
- Production Ready

Do not claim a feature is production ready without real verification.

---

# 56. ADDITIONAL DOCUMENTATION

Create the following files:

- `README.md`
- `docs/ARCHITECTURE.md`
- `docs/DATABASE.md`
- `docs/MODULES.md`
- `docs/MODULE_DEPENDENCIES.md`
- `docs/API.md`
- `docs/SECURITY.md`
- `docs/DEPLOYMENT.md`
- `docs/DEMO.md`
- `docs/INTEGRATIONS.md`
- `docs/MULTI_TENANCY.md`
- `docs/MULTI_BRANCH.md`
- `docs/PAYMENTS.md`
- `docs/FINANCIAL_LEDGER.md`
- `docs/TESTING.md`
- `docs/OPERATIONS.md`
- `docs/FEATURE_MATRIX.md`
- `docs/IMPLEMENTATION_PLAN.md`
- `docs/KNOWN_LIMITATIONS.md`
- `CHANGELOG.md`
- `.env.example`

Documentation must describe actual implemented behavior.

---

# 57. TESTING REQUIREMENTS

Implement automated testing throughout development.

## Unit tests

- Commission calculations
- Tax calculations
- Currency rounding
- Pricing
- Promotions
- Module dependencies
- Subscription entitlements
- Inventory reservations
- Financial ledger balancing
- Settlement calculations

## Feature tests

- Registration
- Login
- Vendor onboarding
- Tenant onboarding
- Product creation
- Product approval
- Checkout
- Payment confirmation
- Multi-vendor order splitting
- Shipment handling
- Refunds
- Returns
- Payout calculations
- Branch permissions
- Module toggles
- API permissions

## Integration tests

- Payment gateway sandbox
- Shipping provider sandbox
- AI provider fake adapters
- Attendance simulator
- Email sandbox
- PDF generation
- Search indexing
- Webhook processing

## Browser tests

- Public homepage
- Registration
- Customer login
- Seller dashboard
- Product browsing
- Cart
- Checkout
- Customer orders
- Tenant admin
- Super-admin module manager

## High-risk concurrency tests

- Simultaneous checkout of last available stock
- Duplicate payment webhooks
- Multiple refund requests
- Concurrent payout processing
- Concurrent coupon redemption
- Stock transfer conflicts
- Module shutdown during active operations

Tests must verify actual outcomes and database state.

---

# 58. DEVELOPMENT ROADMAP

Implement the system in structured phases.

## Phase 1 — Discovery and architecture

- Inspect repository
- Inspect README
- Validate Composer dependencies
- Choose compatible versions
- Document architecture
- Define domain models
- Define module dependencies
- Establish implementation checklist

## Phase 2 — Foundation

- Laravel setup
- Filament setup
- Authentication
- Authorization
- Multi-tenancy
- Module manager
- Database foundation
- Queue foundation
- Logging
- Storage
- Test infrastructure

## Phase 3 — Core marketplace

- Vendors
- Branches
- Warehouses
- Categories
- Products
- Vendor offers
- Inventory
- Search
- Public storefront

## Phase 4 — Commerce engine

- Cart
- Checkout
- Pricing
- Promotions
- Orders
- Payment adapters
- Shipping
- Tax calculations
- Notifications

## Phase 5 — Marketplace finance

- Commission engine
- Financial ledger
- Refunds
- Settlements
- Payouts
- Invoices
- Reconciliation

## Phase 6 — Complete administration

- Super Admin
- Tenant Admin
- Vendor Panel
- Branch Panel
- Customer Account
- Reporting

## Phase 7 — Advanced modules

- B2B wholesale
- Digital products
- Subscriptions
- Service bookings
- CRM
- Marketing
- Affiliates
- HR
- Procurement
- Accounting

## Phase 8 — AI and integrations

- AI Hub
- AI Catalog
- Attendance Hub
- Unicode PDFs
- Universal Slugs
- Performance monitoring
- Third-party integration manager

## Phase 9 — Public marketing website

- Homepage
- Feature pages
- Module pages
- Industry pages
- Pricing
- Documentation
- Blog
- CMS
- Demo landing page

## Phase 10 — Demonstration

- Demo seeders
- Demo roles
- Demo orders
- Demo payments
- Demo settlements
- Demo product catalogs
- Realistic workflows

## Phase 11 — Quality and security

- Automated tests
- Cross-tenant security tests
- Payment concurrency tests
- Accessibility testing
- Performance benchmarks
- Dependency scanning
- Data integrity checks

## Phase 12 — Deployment and release

- Production configuration
- Docker configuration
- Queue workers
- Scheduler
- Storage
- Domain configuration
- Database backups
- Disaster recovery
- Deployment documentation
- Final acceptance report

Do not skip foundational correctness to produce attractive but nonfunctional screens.

---

# 59. USER EXPERIENCE REQUIREMENTS

The application must feel like a polished commercial product.

UI requirements:

- Modern professional design
- Consistent spacing
- Excellent typography
- Responsive dashboards
- Dark and light modes
- Accessible color contrast
- Keyboard-friendly controls
- Clear page headings
- Empty states
- Loading states
- Validation feedback
- Confirmation dialogs
- Helpful errors
- Searchable tables
- Advanced filters
- Bulk actions
- Export actions
- Contextual help
- Onboarding walkthroughs
- Role-specific navigation

Avoid excessive animations.

Prioritize reliability, clarity, accessibility, and perceived speed.

---

# 60. CONFIGURATION-FIRST BUSINESS LOGIC

Business-specific behavior should be configurable whenever practical.

Configurable examples:

- Marketplace commission
- Vendor approval
- Product approval
- Inventory reservations
- Shipping methods
- Tax settings
- Return windows
- Refund policies
- Seller payouts
- Currency
- Language
- Enabled modules
- Subscription limits
- Vendor limits
- Branch limits
- Minimum order values
- Payment methods
- Checkout policies
- Catalog policies
- AI providers
- Notification templates
- Reporting schedules

Do not create a separate codebase for each marketplace business type.

---

# 61. REQUIRED WORKING BUSINESS SCENARIOS

The system is only considered functionally complete when the following end-to-end scenarios work.

## Scenario A: New marketplace business

1. Platform owner creates a tenant.
2. Tenant chooses a plan.
3. Tenant configures a marketplace.
4. Tenant activates selected modules.
5. Tenant configures branding.
6. Tenant configures payment and shipping.
7. Tenant invites vendors.
8. Vendors apply and receive approval.
9. Vendors publish products.
10. Customers place orders.
11. Orders are fulfilled.
12. Marketplace commissions are recorded.
13. Vendors receive eligible settlements.
14. Reports show correct figures.

## Scenario B: Multi-vendor order

1. Customer adds products from three vendors.
2. Customer checks out once.
3. System validates inventory.
4. System calculates taxes, discounts, shipping, and totals.
5. Payment is authorized/confirmed.
6. System creates vendor suborders.
7. Each vendor processes its items.
8. Each vendor ships independently.
9. Customer tracks shipments.
10. Marketplace allocates financial amounts.
11. Eligible settlements are generated.

## Scenario C: Multi-branch inventory

1. Vendor has three branches.
2. Each branch has different stock.
3. Customer places an order.
4. System selects an eligible stock location.
5. Inventory is reserved.
6. Order is assigned to that location.
7. Stock is picked and shipped.
8. Inventory ledger updates.
9. Branch report reflects the transaction.

## Scenario D: Partial refund

1. Customer orders several items.
2. One item is returned.
3. Vendor/admin approves the return.
4. Refund amount is calculated.
5. Payment provider refund is initiated.
6. Refund confirmation is received.
7. Vendor liability changes.
8. Commission adjustments are recorded.
9. Financial ledger stays balanced.
10. Customer receives a refund notification.

## Scenario E: Module deactivation

1. Tenant enables B2B.
2. Tenant creates quotations.
3. Tenant creates purchase orders.
4. Tenant requests module deactivation.
5. System checks dependencies and active transactions.
6. Existing records are preserved.
7. New B2B operations are blocked when appropriate.
8. UI navigation is updated.
9. Tenant later reactivates the module.
10. Historical records remain intact.

## Scenario F: SaaS subscription restriction

1. Tenant subscribes to Starter.
2. Starter permits two branches.
3. Tenant creates two branches.
4. Tenant attempts to create a third.
5. Application enforces the entitlement.
6. Tenant upgrades the plan.
7. New entitlement becomes effective.
8. Tenant creates the third branch successfully.

All scenarios require automated tests.

---

# 62. DEFINITION OF DONE

A module is done only when:

- Database migrations exist.
- Domain logic is implemented.
- Authorization is implemented.
- Validation is implemented.
- Admin interface is functional.
- APIs work where applicable.
- Business workflows are complete.
- Notifications work where applicable.
- Module toggling is respected.
- Tenant isolation is enforced.
- Feature tests pass.
- Relevant integration tests pass.
- Documentation is updated.
- Demo data demonstrates the feature.
- Error handling is complete.
- No critical security issue remains.

A successful-looking screen is not enough.

---

# 63. FINAL ACCEPTANCE CHECKLIST

Before claiming completion:

- [ ] Laravel application installs from scratch
- [ ] Composer dependencies resolve
- [ ] Filament panels load correctly
- [ ] Database migrations run
- [ ] Seeders run
- [ ] Demo logins work
- [ ] Public pages work
- [ ] Vendor registration works
- [ ] Tenant onboarding works
- [ ] Module toggles work
- [ ] Branch management works
- [ ] Warehouse management works
- [ ] Product management works
- [ ] Customer storefront works
- [ ] Cart works
- [ ] Checkout works
- [ ] Order splitting works
- [ ] Payment sandbox works
- [ ] Inventory reservations work
- [ ] Commission calculation works
- [ ] Refund calculation works
- [ ] Settlement calculation works
- [ ] Financial ledger balances
- [ ] API authorization works
- [ ] Cross-tenant isolation tests pass
- [ ] PDF generation works
- [ ] Localization works
- [ ] Queue workers process jobs
- [ ] Scheduler operates correctly
- [ ] Production assets compile
- [ ] Critical end-to-end tests pass
- [ ] README matches actual functionality
- [ ] Security requirements are verified
- [ ] Known limitations are documented

Each item must be supported by actual implementation evidence.

---

# 64. FINAL DELIVERY REQUIREMENTS

The final output must include:

1. Complete Laravel source code.
2. Fully configured Filament panels.
3. Functional public marketing website.
4. Functional marketplace storefront.
5. Complete module management system.
6. Multi-vendor support.
7. Multi-tenant support.
8. Multi-branch support.
9. Multi-warehouse support.
10. Working payment sandbox.
11. Financial ledger and settlement engine.
12. Demo users and seeded sample records.
13. Complete README.md.
14. Environment example.
15. API documentation.
16. Automated tests.
17. Deployment instructions.
18. Performance configuration.
19. Security documentation.
20. Implementation verification report.

The verification report must contain:

- Implemented modules
- Test results
- Failed tests
- Pending work
- Unsupported integrations
- Package compatibility decisions
- Security review findings
- Deployment prerequisites
- Production-readiness status

Never claim 100% completion if anything is unfinished.

---

# 65. INSTRUCTIONS FOR THE CODING AGENT

Start by inspecting the existing codebase and README.md.

If there is no existing application, create the project from scratch.

Before coding:

1. Review the seven supplied packages and their documentation.
2. Check Laravel/Filament/Composer version compatibility.
3. Identify actual functionality already available in the repository.
4. Design the multi-tenant database structure.
5. Design the module dependency registry.
6. Create the implementation checklist.
7. Establish testing and code quality standards.

Then implement the application phase by phase.

After each phase:

1. Run the relevant tests.
2. Fix errors.
3. Check data integrity.
4. Check authorization.
5. Verify the UI.
6. Update documentation.
7. Record completed versus unfinished work.

Continue toward the entire defined application rather than ending after scaffolding, migrations, or admin resources.

If an external provider requires credentials, implement and verify the sandbox adapter, document the missing production credentials, and continue with other independent modules.

If a package is incompatible, document the exact issue and use the agreed framework-native alternative without compromising security or functionality.

**Ultimate objective:**

Build a configurable, secure, scalable, multilingual, multi-tenant, multi-vendor, multi-branch, multi-warehouse marketplace ecosystem that businesses can install, customize, demonstrate, operate, and extend commercially.

The result must be a real working product, not simply code that appears complete.

**BEGIN IMPLEMENTATION.**