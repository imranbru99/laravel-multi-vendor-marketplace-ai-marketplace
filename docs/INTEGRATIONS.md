# Owner package compatibility decisions

Composer resolution and tests used PHP 8.5 with Laravel 12.70.1, Filament 4.15.3, Livewire 3.8.11 and Sanctum 4.3.3. Composer.lock pins exact distributions and source commits. Five owner packages are dev-main snapshots and must be reviewed before upgrading.

| Package | Locked source/version | Decision |
|---|---|---|
| imrandevbd/drutogoti | 6c5b4c9 | Installed; raw service provider/watchers disabled. QueryWatcher captures raw bindings and CaptureContext can persist cookie/query payloads. A secured superadmin page uses the capture container with only hashed SQL fingerprints, timings and memory; no flush/persistence. |
| imrandevbd/laravel-admin-panel | reviewed 67305195926b7e151f8cb061a1765d23645098a1 | Not installed. Provider creates its own admin panel, registers /admin and /admin/dashboard plus asset/theme routes using a separate dashboard/context and component system. It defaults to web guard/middleware and independent permissions. Competes with the chosen Filament routes/UI. Native Filament resources retained as authorized by the brief. |
| imrandevbd/laravel-ai-catalog | 07b5783 | Installed, auto-discovery disabled. Package API/studio and schema need tenant-specific adapters and provider controls before exposure. No source import is advertised. |
| imrandevbd/laravel-ai-hub | v1.6.0 | Installed; global settings database and studio/navigation disabled. Returned fake object tested. Calling AIHub::assertSentCount after AIHub::fake retained the old resolved facade manager in this version; using the returned fake object works. No vendor files patched. |
| imrandevbd/laravel-attendance-hub | 7bd5a7b | Installed, auto-discovery disabled because its provider enables device/adms routes by default. Manual attendance only; no biometric templates collected. |
| imrandevbd/laravel-unicode-pdf | bc0288d | Native invoice rendering tested for actual %PDF output. Unicode package integration works; Bengali/RTL shaping/layout has not been comprehensively visually certified. |
| imrandevbd/laravel-universal-slug | 9fdcf16 | Used in demo product/store slugs and seller applications. Bengali slug generation tested. Historical product/store/content slugs redirect directly to the current published URL; reserved historical slugs cannot be claimed by another record. |

External prerequisites alone are not the only remaining work: live gateway/carrier adapters, AI tenant credential management/budgets and device synchronization still require application code and verification. Do not merely add credentials and assume they work. The independent admin package source was downloaded into a temporary read-only review folder, never booted.

First-party package references: https://packagist.org/packages/imrandevbd/ . Official developer: https://imrandev.bd . Existing third-party licenses and authorship remain in their distributions.
