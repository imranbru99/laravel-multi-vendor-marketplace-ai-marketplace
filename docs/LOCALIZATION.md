# Localization

The storefront can select English or Bengali through a CSRF-protected language form. The session preference is validated and falls back to the tenant locale. Navigation, hero and newsletter labels have translation files under lang/en and lang/bn. Most product/admin/public content remains English; this is partial localization, not complete multilingual commerce.

Use translation keys for new labels, preserve escaped output and test layout in both languages. Tenant currency is independent of language; amount formatting uses integer minor units with a fixed two-decimal representation. No FX conversion or zero/three-decimal currency support exists. Configure timezone per tenant and explicitly format displayed business dates accordingly. Native PDF script shaping must be visually tested with the fonts/languages actually deployed.
