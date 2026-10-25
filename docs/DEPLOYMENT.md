# Deployment

This source includes deployment configuration, not a verified live deployment. Docker build execution and hosted GitHub Actions execution require a working daemon/runner. The current commercial scope remains sandbox-only.

## Standard hosting

Use PHP 8.5/FPM, a web server rooted at public/, PostgreSQL, writable storage/bootstrap/cache and HTTPS. Install Composer production dependencies from composer.lock, run npm ci/build in a build stage, generate a stable APP_KEY, set production environment and secure cookies, migrate, then run marketplace:install with an interactive administrator password. Never enable the demo seeder or copy a shared demo database into production.

Run dedicated queue workers and a per-minute scheduler. Configure Redis or database drivers. Deploy Filament assets and run `storage:link` for approved public catalog media. Cache config/routes/views only after environment variables are final. Restart workers after every deployment. Application uploads live on the private local disk and require persistent storage; this release does not integrate S3 digital delivery.

## Docker

Copy .env.example, generate APP_KEY, set DB_PASSWORD and HTTPS APP_URL, disable demo/debug and set production cookie security. `docker compose build` creates the FPM and Nginx images. `docker compose up -d` starts PostgreSQL, Redis, FPM, web, worker and scheduler. Then execute `docker compose exec app php artisan marketplace:install --email=...`. Migrations and initialization are explicit, not destructive entrypoint resets.

Nginx binds localhost:8080 for a TLS reverse proxy. Configure trusted proxies/hosts for the actual infrastructure. Database/Redis have no public port bindings. Named volumes retain database, private storage and Redis data. Nginx mounts the shared storage volume read-only; only storage/app/public is linked below the public web root. Avoid serving local /demo privileged credentials. Container and reverse-proxy behavior must be validated on the target host.

## Release gate

Run tests on SQLite and PostgreSQL, concurrent harness, static formatting, lock validation/advisory audit, fresh install/demo seed, production asset build and integrity checks. Verify queue/scheduler operation and backup restore. Implement/certify real payment and carrier adapters before commercial transactions. No config switch converts sandbox results into real payments.

Local verification could parse Compose YAML, but the Podman-backed Docker CLI failed while initializing /run/user/1000/libpod. Image build and container execution remain unverified on this host.
