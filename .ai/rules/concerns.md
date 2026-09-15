---
paths:
  - 'app/Models/{Category,Brand,Attribute,AttributeValue}.php,app/Support/Concerns/HasSlug.php'
---

# Concerns

## Catalog models auto-slug via HasSlug; category tree via parent_id
Category, Brand, Attribute, and AttributeValue all `use HasSlug` — slugs are generated automatically on create from the `name` column (or `value` for AttributeValue, via `slugSourceColumn()` override) and are unique; don't set slug manually unless intentionally overriding. Category hierarchy is a simple self-referencing `parent_id` (no nested-set/materialized-path), one level deep in practice so far (parent → child) but the schema supports arbitrary depth — `Category::isDescendantOf()` guards against circular re-parenting, enforced in `Admin\CategoryController::update()`. Category↔Attribute is a `belongsToMany` pivot (`category_attributes`) carrying `is_required`/`sort_order` — read via `$category->attributes` (pivot data on `->pivot`), write via `$category->attributes()->sync(...)`, never query the pivot table directly.
