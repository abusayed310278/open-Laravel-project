---
paths:
  - 'resources/views/**'
---

# Views

## Branding is runtime-driven, never hardcoded
Logo, favicon, accent color, and font all come from the admin Settings module (Phase 3), not hardcoded markup. Use `<x-brand-logo />` for the logo+wordmark pairing (falls back to the "OB" mark when no logo is uploaded). Use `brand-*` Tailwind classes (bg-brand-500, text-brand-600, etc.) instead of `amber-*` anywhere a component should follow the site's configurable accent color — these map to CSS custom properties (`--color-brand-50..900`) defined in resources/css/app.css and overridden at runtime by `layouts/partials/branding-style.blade.php` (generates a full tonal scale from one admin-picked hex via `App\Support\ColorScale`). Never reference `config('app.name')` as a literal "Openbox" string in new views — same idea, it's user-configurable.
