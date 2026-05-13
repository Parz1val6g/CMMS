# Issue: Data Migration - Soft-Reference
**GitHub:** [#33](https://github.com/Parz1val6g/CMMS/issues/33)

**Parent:** [`05-prd.md`](../03-loans/04-logic-migration/05-prd.md)

## What to Build

Create the Artisan command to migrate existing loan ServiceOrders (workflow_type=loan) into the new LoanOrders table, preserving history via soft-reference.

**Artisan command `loan-orders:import-existing`** at `app/Features/LoanOrders/Console/ImportExistingLoanOrders.php`:
- Queries all ServiceOrders where `workflow_type = 'loan'`
- For each SO:
  1. Creates a new `LoanOrder` record copying: client_id, manager_id, location_id, description, timestamps
  2. Sets status based on SO status (PENDING → PENDING, COMPLETED → CHECKED_OUT, CANCELLED → CANCELLED)
  3. Syncs equipment via `equipment_loan_order` pivot from the existing `equipment_service_order` pivot
  4. Sets `migrated_from_so_id` on the LoanOrder
  5. Sets `migrated_to_loan_id` on the original ServiceOrder (new nullable column added in migration)
  6. Updates existing Tasks: sets `taskable_id = service_order_id`, `taskable_type = ServiceOrder::class`
- Reports summary: count of loans migrated, tasks updated, errors if any
- Idempotent: safe to re-run (skips already-migrated records by checking `migrated_to_loan_id`)

**Registered in** `app/Providers/ConsoleServiceProvider.php` or `Kernel.php`

**Test** — `LoanOrderMigrationTest`:
- Seed: create 3 loan SOs with tasks + equipment
- Run command
- Assert: 3 LoanOrders created with correct data
- Assert: Tasks have correct taskable_id + taskable_type
- Assert: Equipment pivot copied correctly
- Assert: Original SOs have migrated_to_loan_id set
- Assert: Rollback scenario (delete LoanOrders, restore tasks) works

## Acceptance Criteria

- [ ] `php artisan loan-orders:import-existing` runs without errors
- [ ] All existing loan SOs are copied to loan_orders table
- [ ] Existing tasks are updated with taskable_id + taskable_type
- [ ] Equipment pivot data is preserved
- [ ] Original SOs reference the new LoanOrder via migrated_to_loan_id
- [ ] Command is idempotent (safe to re-run)
- [ ] Rollback procedure is documented
- [ ] Co-existence period: both ServiceOrders UI and LoanOrders UI work simultaneously

## Blocked by

- [`05-loan-frontend-page`](./05-loan-frontend-page.md)
