# Operations

## Daily checks

Review failed jobs, pending payments, unresolved returns, low stock and settlement eligibility. Run marketplace:verify before financial exports/release. Queue workers execute in-app notification jobs with explicit tenant context; scheduler expires unpaid orders each minute. Nothing in the system indicates an actual bank transfer or carrier pickup.

## Backup and recovery

Back up PostgreSQL with a consistent pg_dump or infrastructure snapshot and copy private storage in the same recovery window. Encrypt and restrict backups. Keep APP_KEY recoverable separately; rotate access after incidents. Retain financial events according to approved policy. Restore into an isolated environment and verify migrations, account access, private downloads, journal balance and inventory reconciliation. No backup restoration drill is claimed in this release.

## Upgrade

Review source commits and dependency advisories; test migrations and private storage compatibility. Deploy assets/code, run non-destructive migrations, sync new modules disabled, clear/rebuild caches and restart workers. Never run migrate:fresh on an operational database. Preserve audit and event tables. Investigate discrepancies before posting correction events through a reviewed domain workflow.

## Configuration

The local deployment defaults to SQLite/database sessions/cache/queues. Production Compose uses PostgreSQL/Redis. Horizon, Reverb, Scout and external telemetry are not installed. Configure health monitoring around /up plus actual database/Redis/queue checks; /up alone is framework liveness and does not certify every dependency.
