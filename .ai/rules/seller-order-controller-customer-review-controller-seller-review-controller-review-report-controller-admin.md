---
paths:
  - 'app/Services/{OrderFulfillmentService,ReviewService}.php,app/Http/Controllers/{SellerOrderController,CustomerReviewController,SellerReviewController,ReviewReportController,Admin/{OrderController,ReviewController,ReviewReportController}}.php'
---

# Seller Order Controller Customer Review Controller Seller Review Controller Review Report Controller Admin

## Order fulfillment gates review eligibility; reviewable_type isn't a real morph map
Sellers move a `VendorOrder` forward via `OrderFulfillmentService::updateStatus()` (confirmed→processing→packed→shipped→out_for_delivery→delivered, or cancelled from any pre-shipment state) — `nextStatuses()` is the single source of truth for legal transitions, whitelist-checked with `abort_unless` in `updateStatus()`. Never let a controller set `VendorOrder::status` directly. `Order.status` is a best-effort aggregate recomputed by `syncOrderStatus()` on every vendor-order update (least-advanced-state heuristic across all vendor orders on multi-vendor orders — there's no single "true" status when vendors fulfill independently).

`ReviewService::eligibleReviewables()` requires a vendor order to be `Delivered` before its product/seller can be reviewed, so Reviews is hard-blocked on fulfillment actually reaching that state — don't build review flows assuming `Confirmed` is enough.

`reviews.reviewable_type` is a plain enum string (`product`/`seller`), NOT a real Eloquent morph map — `Relation::enforceMorphMap()` was deliberately NOT used because `ActivityLog.subject_type` already stores full class names across every other model in the app, and forcing a global morph map would break every existing `ActivityLog::record()` call. `Review::reviewable()` resolves the target manually (`Product::find()`/`User::find()`); `Product::reviews()`/`User::reviewsAsSeller()` are plain `hasMany` with an extra `->where('reviewable_type', ...)` constraint, not `morphMany`.
