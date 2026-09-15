---
paths:
  - 'tests/**,database/factories/*.php'
---

# Factories

## Model factories + integration-style feature tests, not unit mocks
Only `UserFactory` shipped with the project; `CategoryFactory`, `BrandFactory`, `ProductFactory` (with a `->live()` state for published/approved/publication_status=published), and `AddressFactory` were added this session — remember to add `use HasFactory;` to the model itself, Laravel doesn't infer it. `ProductFactory` needs `user_id => User::factory()->role(UserRole::Business)` since `products.user_id` is required and has no sensible default.

Feature tests in this app call the real service classes (`CheckoutService::placeOrder()`, `PaymentService::confirmCodCollected()`, `OrderFulfillmentService::updateStatus()`, `WalletService`, `ReviewService`) end-to-end rather than mocking them — that's deliberate, matching how this session's manual curl-based verification worked, and it's what actually catches cross-service bugs (e.g. a broken hook between `PaymentService` and `CommissionService`). `abort_if`/`abort_unless` failures surface as `Symfony\Component\HttpKernel\Exception\HttpException` in tests (`expect(fn () => ...)->toThrow(HttpException::class)`), not a Laravel-specific exception class. Always `Queue::fake()` in a `beforeEach()` for any flow that fires a queued `Notification` — otherwise the queue driver (`database` in `.env`, not overridden for tests) tries to write to a `jobs` table during the test.
