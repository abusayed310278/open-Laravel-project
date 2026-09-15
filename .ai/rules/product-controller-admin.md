---
paths:
  - 'app/Services/ProductService.php,app/Http/Controllers/{ProductController,Admin/ProductController}.php'
---

# Product Controller Admin

## Product lifecycle: draft → pending_approval → approved → published
Three separate status fields on `products`, all driven through `ProductService`, never set directly on the model: `status` (overall lifecycle enum, `ProductStatus`), `approval_status` (admin review outcome, `ProductApprovalStatus`), `publication_status` (storefront visibility, `ProductPublicationStatus`). Flow: create() → draft; submitForApproval() → pending_approval; approve()/reject() → approved/rejected; publish() (requires approval_status=Approved) → published + publication_status=Published. `payment_route` is computed from the seller's role (`ProductService::paymentRouteFor()`), never set manually — admin-owned products always route to `openbox`. Admin-created products auto-approve via `ProductService::approve()` right after creation (no self-review). Listing-credit/subscription gating on `publish()` is deferred to Phase 12 — don't add ad-hoc checks for it before then. `ProductPolicy::update()` locks the record once `Product::isLocked()` (verification_status=Verified, Phase 10) — this doesn't trigger yet since nothing sets that status until Phase 10 lands.
