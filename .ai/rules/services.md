---
paths:
  - 'app/Services/{InventoryService,CheckoutService,ProductService,OrderFulfillmentService,PaymentService}.php'
  - app/Services/ReportService.php
---

# Services

## Stock is deducted at checkout, inside the transaction, row-locked
`products.quantity` is the authoritative stock count — there is no separate `inventories` table (deliberately simplified from the doc's schema: no per-location/reserved-quantity split, no cart-hold reservation system). `InventoryMovement` is a pure audit ledger.

`InventoryService::deduct()` is called from inside `CheckoutService::placeOrder()`'s `DB::transaction()`, one call per order item, AFTER that `OrderItem` is created (so the movement can reference it) but the whole thing must stay inside the same transaction: it does `Product::lockForUpdate()` then `abort_if($locked->quantity < $quantity, 422, ...)` — the `abort_if` throws, which makes `DB::transaction()` roll back everything (the `Order`, `VendorOrder`, and any already-created `OrderItem`s) so a failed stock check never leaves a partial order behind. Never call `deduct()` outside an active transaction — `lockForUpdate()` is a no-op without one.

Stock is restored (`InventoryService::restore()`, type `Return`) from two places: `PaymentService::approveRefund()` (per vendor-order item) and `OrderFulfillmentService::updateStatus()` when a vendor order reaches `Cancelled`. A manual quantity edit through `ProductService::update()` doesn't call deduct/restore — it diffs old vs new `quantity` and logs an `Adjustment` movement via `InventoryService::logAdjustment()` instead, only when an `$actor` is passed (both `ProductController::update()` and `Admin\ProductController::update()` pass `$request->user()`).

## Report queries: qualify columns after joins, use ->label() not string helpers on cast enums
Two recurring bugs in this file, both now fixed — watch for them in any new report method:

1. Any `Model::query()->select('status')->groupBy('status')->get()` still returns full model instances with casts applied, so `$row->status` is an enum object, not a string. Never call `ucfirst($row->status)` or `str_replace(..., $row->status)` — use `$row->status->label()` instead (see `.ai/rules/controllers-admin.md` for the twin bug in the admin overview report).
2. Once a query built with an unqualified column in `whereBetween`/`where` (e.g. `whereBetween('created_at', ...)`) later gets a `->join(...)` added (even via a cloned copy for a different aggregate), MySQL sees two `created_at` columns and throws "Column 'created_at' is ambiguous" — qualify the column with the table name from the start (`vendor_orders.created_at`) on any query that might get joined later in the same method, not just at the point of the join.
