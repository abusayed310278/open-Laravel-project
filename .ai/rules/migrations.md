---
paths:
  - 'database/migrations/**'
---

# Migrations

## Composite unique keys on multiple string columns need explicit lengths
MySQL's max index key length is 3072 bytes. A composite unique index across several `$table->string()` columns (default 255 chars, ×4 bytes for utf8mb4) blows past that fast — e.g. 4 default-length strings = 4080 bytes, fails with "Specified key was too long". When a unique/composite index spans multiple string columns, give each an explicit shorter length (`$table->string('zone', 20)`) sized to what the value actually needs. Hit this on `warehouse_locations` (zone/row/shelf/slot). Check any new composite unique index against multiple varchar columns for this before running the migration.
