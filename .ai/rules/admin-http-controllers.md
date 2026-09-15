---
paths:
  - 'app/Services/WarehouseService.php,app/Http/Controllers/Admin/Warehouse*.php,app/Http/Controllers/WarehouseDepositController.php'
---

# Admin Http Controllers

## Warehouse deposit: saler-only, flips payment_route to openbox on receipt
Like product verification, warehouse deposit is a **saler-only** feature (`saler.warehouse.*` routes) — no business equivalent, matching the roadmap's URL structure. Only `verification_status=Verified` products can be deposited (enforced in `WarehouseDepositController::index()`'s eligible-products query). The critical business rule lives in `WarehouseService::receive()`: the moment a warehouse-staff member assigns a storage slot, `Product::payment_route` flips from `seller` to `openbox` — this is the one place that happens outside `ProductService::paymentRouteFor()`. `WarehouseProduct::storage_status` and `Product::warehouse_status` are two separate enums (`StorageStatus` vs `WarehouseStatus`) that are updated together but never merged — don't try to unify them.
