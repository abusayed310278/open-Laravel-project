---
paths:
  - 'app/Services/ProductVerificationService.php,app/Http/Controllers/Verifier/**,app/Http/Controllers/ProductVerificationController.php'
---

# Verifier Http Controllers

## Product physical verification: saler-only, self-service booking
Product verification (physical inspection, distinct from KYC) is a **saler-only** feature — business sellers don't get appointment booking (matches the original roadmap's URL structure, which lists `/saler/appointments` but no business equivalent). Routes live at `saler.appointments.*`, not a generic `product-verifications` name. `Product::verification_status` and `ProductVerification::status` share the same `VerificationStatus` enum and are always updated together in `ProductVerificationService` — never update one without the other. There's no separate admin appointment-confirmation step: `ProductVerificationController::store()` auto-confirms the seller's chosen slot immediately (`AppointmentStatus::Confirmed`). Verifiers only see appointments at their own `assigned_location_id` (`Verifier\AppointmentController::index()`) — a verifier with no assigned location sees none, by query design, not a bug.
