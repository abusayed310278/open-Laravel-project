---
paths:
  - routes/web.php
---

# Routes

## Register literal-segment routes before wildcard routes on the same prefix
Laravel matches routes in registration order. A route like `POST /subscription/{plan}` registered before `POST /subscription/cancel` will swallow requests to `/subscription/cancel` first — {plan} matches the literal string "cancel", route model binding then fails to find a SubscriptionPlan with that key and 404s. Always register specific/literal-segment routes (`/cancel`, `/create`, etc.) *before* a sibling wildcard route (`/{id}`) at the same path depth. Check this whenever adding a new literal route next to an existing `{param}` route.
