---
paths:
  - 'app/Models/*.php'
  - 'app/Models/{Page,Post,BlogCategory,Tag}.php'
---

# Models

## Undefined Eloquent relation accessors fail silently, not loudly
Calling `$model->someRelation` when `someRelation()` isn't defined on the model does NOT throw — Eloquent's `__get` just returns null (there's no matching relationship method or attribute). This let `PaymentService::approveRefund()` silently skip marking a Transaction as Refunded for weeks because `Refund` had no `transaction()` relation — the `if ($refund->transaction)` check was always false, no error anywhere.

When wiring a service method that reads `$model->someRelation`, verify the relation method actually exists on the model (grep it) before trusting the code path — don't assume a null check catches a missing relation the same way it catches "no related row".

## Always override slugSourceColumn() when the model has no 'name' column
`HasSlug::slugSourceColumn()` defaults to `'name'`. `Page` has `title`, not `name` — forgetting to override it produced an empty-string slug (silently, no error) exactly like the earlier `Product` bug (`.ai/rules/concerns.md`). `Post` correctly overrides it to `'title'`; `BlogCategory` and `Tag` both genuinely have a `name` column so need no override.

Whenever `use HasSlug;` is added to a new model, immediately check the model's column list and add `public function slugSourceColumn(): string { return '...'; }` unless the source column really is named `name` — don't rely on remembering this per-model, grep the migration for the actual title/name column before assuming the default is fine. If a bad empty-slug row is ever created, just delete it and re-save — `generateUniqueSlug()` only runs on `creating`, not on update, so fixing the method alone won't repair existing rows.
