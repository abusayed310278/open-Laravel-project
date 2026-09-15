---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Base Controller now includes AuthorizesRequests
Laravel 13's default `app/Http/Controllers/Controller.php` ships bare (no traits) — `$this->authorize()` throws "Call to undefined method" unless a trait provides it. Fixed by adding `use AuthorizesRequests` (Illuminate\Foundation\Auth\Access\AuthorizesRequests) to the base Controller so every controller inherits `$this->authorize()`/`$this->authorizeForUser()`. Don't re-add the trait to individual controllers — it's already on the base class.
