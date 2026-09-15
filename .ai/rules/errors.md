---
paths:
  - 'resources/views/errors/**'
---

# Errors

## Error pages use <x-error-page>, extend the public layout
All six error views (403/404/419/429/500/503) `@extend layouts.app` and render `<x-error-page code="..." title="..." message="..." icon="...">` — don't build one-off markup per error page. 503 doubles as the `php artisan down` maintenance page, so it and every partial it depends on (header/footer via layouts.app, `SettingsService::all()`) must degrade gracefully on a DB outage — `SettingsService::all()` already catches `Throwable` and returns `[]` rather than throwing, so `setting()` calls anywhere in the layout are always safe even mid-outage. Don't remove that guard.
