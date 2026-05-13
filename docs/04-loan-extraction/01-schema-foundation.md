# Issue: Schema Foundation
**GitHub:** [#28](https://github.com/Parz1val6g/CMMS/issues/28)

**Parent:** [`04-prd.md`](04-prd.md)

## What to Build

Create the database schema foundation for the LoanOrders module — three migrations plus a new enum.

**Migration 1 — `create_loan_orders_table`:**
- `id` UUID PK
- `reference` VARCHAR(20) UNIQUE (format: `EMP/2026/0001`)
- `client_id` UUID FK → clients.id
- `manager_id` UUID FK → users.id
- `location_id` UUID FK → locations.id (nullable)
- `migrated_from_so_id` UUID FK → service_orders.id (nullable, for soft-ref)
- `status` VARCHAR(20) DEFAULT 'pending'
- `description` TEXT (nullable)
- `notes_checkout` TEXT (nullable)
- `notes_return` TEXT (nullable)
- `checked_out_at` TIMESTAMP (nullable)
- `returned_at` TIMESTAMP (nullable)
- `cancelled_at` TIMESTAMP (nullable)
- `cancelled_by` UUID FK → users.id (nullable)
- `deleted_at` TIMESTAMP (nullable, soft-delete)
- Standard Laravel timestamps

**Migration 2 — `create_equipment_loan_order_table`:**
- `equipment_id` UUID FK → equipments.id
- `loan_order_id` UUID FK → loan_orders.id
- Composite PK (equipment_id, loan_order_id)
- `created_at` TIMESTAMP (pivot timestamps)

**Migration 3 — `add_taskable_morph_to_tasks`:**
- Add `taskable_id` UUID NULL
- Add `taskable_type` VARCHAR(255) NULL
- Add `migrated_to_loan_id` UUID NULL to `service_orders` (for soft-ref tracking)
- Index on (taskable_id, taskable_type)

**`LoanOrderStatus` enum** at `app/Core/Enums/LoanOrderStatus.php`:
- Cases: `PENDING`, `CHECKED_OUT`, `CANCELLED`
- Methods: `label()`, `options()`, `isTerminal()`, `isOperational()`

## Acceptance Criteria

- [ ] `loan_orders` table exists with all columns and FKs
- [ ] `equipment_loan_order` pivot table exists with composite PK
- [ ] `tasks` table has `taskable_id` + `taskable_type` columns (nullable)
- [ ] `service_orders` table has `migrated_to_loan_id` column (nullable)
- [ ] `LoanOrderStatus` enum defined with PENDING, CHECKED_OUT, CANCELLED
- [ ] All migrations are reversible (down method)
- [ ] `php artisan migrate:fresh --seed` succeeds without errors

## Blocked by

None — can start immediately.
