---
paths:
  - 'app/Http/Controllers/Auth/*.php'
---

# Auth

## Guest-session merge hooks must run on both login and register
Both `AuthenticatedSessionController::store()` (login) and `RegisteredUserController::store()` (registration) call `Auth::login()` and are equally valid entry points into an authenticated session — a brand-new user goes through register, not login. Any guest-session state that needs merging into the account (cart via `CartService::mergeGuestCartIntoUser()`, and anything similar added later — e.g. a future wishlist session merge) must be wired into *both* controllers, not just login. Missed this the first time for cart merging; caught via live testing, not review.

## Never hardcode route('dashboard') — this app has no such route
Breeze scaffolds every auth controller with `route('dashboard', ...)`, but this app uses per-role dashboards (`account.dashboard`, `business.dashboard`, `saler.dashboard`, `verifier.dashboard`, `admin.dashboard` — see `UserRole::dashboardRoute()`). `RegisteredUserController` and `AuthenticatedSessionController` were fixed for this early on, but `ConfirmablePasswordController`, `EmailVerificationNotificationController`, `EmailVerificationPromptController`, and `VerifyEmailController` still had the literal `route('dashboard', ...)` call — a real 500 (`RouteNotFoundException`) in production the first time any user confirmed their password or verified their email, caught only when the default Breeze test suite was finally run and fixed against real routes. Always use `route($user->role->dashboardRoute(), ...)`, never the literal string `'dashboard'`.
