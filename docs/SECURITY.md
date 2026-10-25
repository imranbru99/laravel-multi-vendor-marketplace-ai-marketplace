# Security

## Implemented controls

Hashed passwords, tenant-bound single-use password recovery with token revocation, CSRF on web writes, rate-limited login/register/checkout/contact, tenant global scopes, tenant-owned FK checks, vendor resource filtering, branch-restricted views, customer order ownership, signed private downloads, HMAC sandbox webhooks, integer money and idempotent locked finance operations. Append-only event tables are protected at Eloquent and SQLite/PostgreSQL database levels. Demo seeding is local/testing only and explicit. Production boot refuses demo mode.

Filament has four explicit role entry gates. User administration cannot edit the current operator or grant superadmin through a form. Only a superadmin can create owners. Role assignments validate approved vendor and matching branch. Tenant/vendor/branch Access Roles supplement baseline roles with grants/denials, validated assignments and audited revocation. Dashboard aggregates honor entity permissions and scopes.

Raw SQL binding/cookie capture is disabled. Platform diagnostics use hashed SQL fingerprints, duration and memory only, in memory. AI Catalog and Attendance Hub route providers are not discovered. AI Hub studio and global settings database access are disabled. No external credentials are stored by the current marketplace integration UI.

## Release gaps

No independent penetration test, account MFA/email-verification workflow, database row-level security, comprehensive CSP/accessibility audit, malware scan, privacy-retention engine, production provider certification or infrastructure backup drill has been performed. MySQL event triggers are not supplied. The privileged local demo must not be exposed publicly. APP_DEBUG must be false and sessions secure under HTTPS.

The repository contains no committed environment secrets. Seed data uses fictional identities. Protect private asset backups, APP_KEY, database credentials, queue access and logs. Review dependency licenses and advisory results before every release. See known limitations and verification for evidence rather than treating this file as certification.
