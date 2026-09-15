---
paths:
  - 'app/Services/SettingsService.php,app/Http/Controllers/Admin/SettingsController.php,resources/views/admin/settings/**'
---

# Settings

## Admin Settings module: key-value store, cached, encrypted secrets
All admin-configurable site settings (branding, SMTP, R2, etc.) live in the `settings` table (key/value/group) behind `SettingsService`, cached forever under `settings:all` and flushed on every write. Read via the global `setting($key, $default)` helper (app/Support/helpers.php) — never query the `Setting` model directly. Secret values (SMTP password, R2 secret key — see `SettingsService::ENCRYPTED_KEYS`) are stored `Crypt::encryptString()`'d and must never be re-rendered into an edit form; forms only show a "leave blank to keep current" placeholder. Runtime config (mail.*, filesystems.disks.r2) is overridden from saved settings in `AppServiceProvider::applyRuntimeSettings()`, guarded to skip during Artisan console commands and when the `settings` table doesn't exist yet. Adding a new setting: add it to the relevant FormRequest + controller tab, read/write it through `SettingsService`, and if it needs to affect runtime behavior wire it into `AppServiceProvider::boot()` the same way mail/R2 are.
