---
paths:
  - 'app/Services/SubscriptionService.php,app/Http/Controllers/SubscriptionController.php'
  - 'app/Services/{CartService,CheckoutService,WishlistService}.php,app/Http/Controllers/{Cart,Checkout,Wishlist}Controller.php'
---

# Services Http Controllers

## Subscription purchase activates instantly — no live gateway yet
`SubscriptionService::purchase()` activates the subscription synchronously (status=active immediately) because no payment gateway is wired in yet — that's Phase 14 (Payment System). When Stripe/PayPal integration lands, this becomes: create the subscription as `pending`, redirect to a Checkout Session, and let a webhook flip it to `active` — don't build fake Stripe calls before real credentials exist. `ProductController::publish()` gates on `SubscriptionService::canPublish()` before calling `ProductService::publish()`, then calls `recordPublish()` after — always keep that order (gate → publish → record) since `recordPublish()` is what actually decrements the saler's credit and sets `Product::expires_at` from the credit's expiry.

## Cart/checkout: guest session cart, vendor_id = product owner
Cart works identically for guests (session-keyed via `CartService::sessionId()`, stored in `session('cart_session_id')`) and logged-in users (user-keyed) — same routes, `CartController::currentCart()` picks the right one. `VendorOrder.vendor_id` is always `product.user_id` (the seller of record) even for `payment_route=openbox` items — Openbox holds the money for those but the original seller still owns the vendor_order for fulfillment; don't try to invent a synthetic "Openbox vendor". Orders pulled forward from Phase 15 here since checkout is meaningless without them.

## Checkout payment method: COD + manual bank only, Stripe/PayPal excluded until real creds exist (Phase 14)
`CheckoutService::availablePaymentMethods($cart)` returns the intersection, across every seller group in the cart, of what each accepts — openbox-route items: COD always + manual-bank if admin's `bank_details` setting is filled; seller-route items: whatever that seller's `VendorPaymentSetting` toggles on. `placeOrder()` takes a `PaymentMethod` enum and `abort_unless`s it's in that available set. Stripe/PayPal enum cases exist and have a settings UI (admin Settings→Payments, seller Payment Settings) but are deliberately never offered at checkout — there's no real charge logic yet, only the settings scaffolding to "activate automatically" once real gateway credentials are wired in. Each vendor order gets a `Transaction` (status `pending`) via `PaymentService::recordPendingTransaction()` at order-placement time; manual-bank orders settle via customer-submitted proof (`ManualPaymentSubmission`) reviewed by the seller (or admin for openbox-route) through `PaymentService::verifyManualPayment()/rejectManualPayment()`, COD settles via `confirmCodCollected()`. Refunds go through `PaymentService::requestRefund()/approveRefund()/rejectRefund()` — see `.ai/rules/models.md` for a relation bug that once broke `approveRefund()`.
