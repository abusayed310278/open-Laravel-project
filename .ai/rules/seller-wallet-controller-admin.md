---
paths:
  - 'app/Services/{CommissionService,WalletService}.php,app/Http/Controllers/{SellerWalletController,Admin/{CommissionRuleController,PayoutController}}.php'
---

# Seller Wallet Controller Admin

## Wallet lifecycle: sale credits pending, delivery releases, refund reverses, payout reserves on request
Money flows through `SellerWallet` in one direction only, driven by hooks in other services — never write to `available_balance`/`pending_balance` directly from a controller:
1. `PaymentService::verifyManualPayment()`/`confirmCodCollected()` → `CommissionService::recordSaleForVendorOrder()` → one `CommissionRecord` (status `pending`) per order item + `WalletService::creditPending()` (adds to both `pending_balance` and `total_earned`).
2. `OrderFulfillmentService::updateStatus()` reaching `Delivered` → `CommissionService::releaseForVendorOrder()` moves each `pending` record to `available` and shifts the matching amount from `pending_balance` to `available_balance` (no change to `total_earned` — already counted at step 1).
3. `PaymentService::approveRefund()` → `CommissionService::reverseForVendorOrder()` marks records `refunded` and calls `WalletService::reverseForRefund()`, which pulls from whichever balance the funds are currently sitting in (`available` or `pending`) and always decrements `total_earned`. If the seller already withdrew the money before the refund lands, `available_balance` simply can't go negative (clamped via `min()`) — there's no receivable/clawback tracking, that's a deliberate simplification, not a bug.
4. `WalletService::requestPayout()` immediately decrements `available_balance` (reserves the funds at request time, not at completion) and creates a `SellerPayout` (`requested`). `rejectPayout()` credits it back. `completePayout()` only then increments `total_withdrawn` and logs the debit `SellerTransaction` — no wallet-balance change at completion since it was already reserved at request time.

`CommissionService::resolveRule()` picks the single most-specific active `CommissionRule` in the fixed order product → seller → category → business (seller-type, `reference_id` null) → global — see `CommissionRuleType::specificityOrder()`. No commission rule matching means 0% commission (seller keeps 100%), not an error.
