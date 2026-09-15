---
paths:
  - 'bootstrap/app.php,app/Http/Middleware/SecurityHeaders.php,app/Providers/AppServiceProvider.php,routes/web.php'
---

# Providers

## Security hardening baseline: headers, password policy, throttled write endpoints
`SecurityHeaders` middleware is appended globally in `bootstrap/app.php` (X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy, plus HSTS when the request is already HTTPS). Deliberately NO Content-Security-Policy — the app has many inline `<script>` blocks (chat polling, dialog toggles, etc.) and a CSP without nonces would break them; don't add one without also nonce-ing every inline script first.

`Password::defaults()` is set in `AppServiceProvider::boot()` to `min(8)->mixedCase()->numbers()` (no `->symbols()`/`->uncompromised()` — deliberately not requiring symbols to avoid annoying casual seller signups, and skipping the HaveIBeenPwned network check since it can fail/timeout without external network access). Applies automatically everywhere `Password::defaults()` is used (register, reset, change-password).

User-generated-content POST endpoints are individually throttled in `routes/web.php` (not a route-group blanket rule, since they're scattered across different groups): `checkout.store` (10/min), `chat.store` (30/min), `support.store` (10/min), `account.reviews.store` (10/min), `refund-requests.store` (10/min), `reviews.report` (20/min). When adding a new user-facing write endpoint that a script could hammer (review, message, ticket, report, order-placing), add `->middleware('throttle:N,1')` to it directly rather than assuming the auth-route throttles cover it — they don't extend past `routes/auth.php`.
