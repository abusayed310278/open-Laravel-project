---
paths:
  - 'app/Models/User.php,app/Http/Controllers/**'
---

# Http Controllers

## User::create() silently drops email_verified_at (not fillable)
`User`'s `#[Fillable(...)]` list is `['name', 'email', 'phone', 'password', 'role', 'status']` — it does NOT include `email_verified_at`. Passing it to `User::create([...])` is silently dropped (no exception), so admin-created accounts that should be pre-verified (verifiers, future staff accounts) end up unverified and get bounced by the `verified` middleware. Fix: create the user first, then `$user->forceFill(['email_verified_at' => now()])->save();` as a separate step. See `Admin\VerifierController::store()` for the pattern.
