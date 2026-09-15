---
paths:
  - 'app/Services/ReportService.php,app/Http/Controllers/Admin/ReportController.php'
---

# Controllers Admin

## Eloquent ->select('status') still casts — don't re-call Enum::from() on it
`Order::query()->select('status')->get()` returns `Order` model instances (not raw stdClass rows) — Eloquent still applies the model's `casts()` to whatever columns were selected, so `$row->status` is already an `OrderStatus` enum instance, not a string. Calling `OrderStatus::from($row->status)` on it throws `TypeError: must be of type string|int, App\Enums\OrderStatus given`. This bit `ReportService::summary()`'s status-breakdown query. When mapping a raw grouped/aggregated Eloquent query result that includes a cast enum column, check `instanceof` before re-`::from()`-ing it (`$row->status instanceof OrderStatus ? $row->status : OrderStatus::from($row->status)`), or just skip the enum method entirely if it's already cast.
