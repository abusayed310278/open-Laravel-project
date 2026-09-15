---
paths:
  - 'resources/views/components/{input,select,textarea,file-upload}.blade.php'
---

# Components

## Form field components convert bracket names to dot notation for error lookup
A field named `documents[nid]` (bracket HTML notation, used for nested/array inputs) produces a validation error keyed `documents.nid` (dot notation) in Laravel's error bag — `$errors->first('documents[nid]')` or `@error('documents[nid]')` silently finds nothing. These four components convert via `str_replace([']', '['], ['', '.'], $name)` before doing the error lookup. Keep this conversion if you touch their error-display logic, and apply the same pattern in any new form component.
