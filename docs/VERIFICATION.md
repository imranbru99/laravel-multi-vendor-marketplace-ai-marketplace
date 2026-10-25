# Verification record

Local verification completed **25 October 2026**. This records executed checks, not a production-readiness certification or a hosted CI badge. Application author: [Imran Ahmed](https://imrandev.bd).

| Check | Actual result | Evidence |
|---|---|---|
| SQLite suite | **117 passed, 398 assertions**, 15.98 seconds | [Output](verification/sqlite-tests.txt) |
| PostgreSQL suite | **117 passed, 398 assertions**, 72.51 seconds | [Output](verification/postgresql-tests.txt) |
| Concurrent PostgreSQL processes | Correct last-unit/capture/refund/settlement/coupon/transfer/module invariants | [JSON report](CONCURRENCY_RESULTS.json), tests/Concurrency/run.php |
| Persisted local demo reconciliation | 2 tenants, 251 orders, 293 journals, 305 stock locations; **no errors** | [Integrity report](INTEGRITY_RESULTS.json) |
| PHP formatting | Pint passed | [Output](verification/formatting.json) |
| Composer metadata | `composer validate --strict` passed | Executed locally |
| PHP dependency advisories | No advisories or abandoned packages reported at check time | [Audit](verification/composer-audit.json) |
| Frontend dependency advisories | Zero reported vulnerabilities at check time | [Audit](verification/npm-audit.json) |
| Production frontend bundle | Vite build passed | [Output](verification/frontend-build.txt) |
| Laravel caches | Route cache and compiled Blade views passed; route cache cleared afterward | Executed locally |
| Scheduler registration | Per-minute order expiry and daily sandbox subscription renewal listed | [Output](verification/schedule.txt) |
| Fresh initialization | Secure installer and full demo seed run in both database suites | InstallationTest |
| Browser | Storefront, guest sandbox checkout/capture, owner login, module toggle, resource creation, billing view, proposal create/submit/approve and published storefront search | [Screenshots](screenshots/README.md) |
| Queue | Explicit-tenant jobs processed for two tenants with the database queue worker during development | Workflow/notification tests plus local worker verification |

## Environments and boundaries

PHP 8.5.4, Laravel 12.70.1, Filament 4.15.3, Livewire 3.8.11, Sanctum 4.3.3, Node 22.23.4 and PostgreSQL 18 were used locally. Exact PHP and npm dependency graphs are in composer.lock and package-lock.json. Test timings describe this local run only; they are not throughput comparisons or service-level commitments.

PostgreSQL testing used isolated local test databases. The concurrency harness refuses databases not ending `_concurrency`; eight independent workers compete for scarce stock/coupon quantities or repeated financial operations. The module race admits either valid ordering: a new obligation keeps the module enabled, or the shutdown wins and the new obligation is blocked. The recorded final run had shutdown win, with zero active quotes.

Browser captures include desktop and compact viewport checks. The seller proposal approved in browser QA is explicitly named “Browser QA linen journal (simulated)”; it adds one stock location to the seeded demo. The screenshots contain simulated business records, never real customer endorsement or sales evidence. The local demo also contains a sandbox order created during browser verification, hence 251 orders instead of the fresh seed's 250.

PDF tests verify generated native PDF output and authorization, not comprehensive Bengali/RTL typography. API scope, role, branch, cart merge, password-reset and URL-history assertions verify specific behaviors, not an independent penetration test.

## Not executed or not certified

- Docker image build/runtime: the Podman-backed host CLI could not initialize `/run/user/1000/libpod` under its available permissions. Compose configuration is supplied; YAML was parsed locally, but container behavior remains unverified.
- Hosted GitHub Actions run, external deployment, independent security/accessibility audit, load benchmark and complete backup/restore rehearsal.
- Live payment, payout, carrier, AI, attendance-device, mail/SMS or tax provider certification. Sandbox/fake/local behavior is not proof of external integration.
- MySQL release compatibility or equivalent MySQL event-protection triggers.

Advisory data changes over time; repeat audits before release. Current results do not establish completion of all enterprise brief acceptance criteria. See [feature matrix](FEATURE_MATRIX.md) and [known limitations](KNOWN_LIMITATIONS.md).
