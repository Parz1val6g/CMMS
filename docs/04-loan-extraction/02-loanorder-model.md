# Issue: LoanOrder Model + Relationships
**GitHub:** [#29](https://github.com/Parz1val6g/CMMS/issues/29)

**Parent:** [`05-prd.md`](../03-loans/04-logic-migration/05-prd.md)

## What to Build

Create the Eloquent model layer for LoanOrders, and update the Task and Equipment models to support the new polymorphic relationship.

**`LoanOrder` model** at `app/Features/LoanOrders/Models/LoanOrder.php`:
- Extends base Model with `Base` trait + `HasAutoReference` trait
- `referenceInitials()` returns `'EMP'`
- `$fillable`: all schema fields
- `$casts`: `status` → `LoanOrderStatus`, timestamps
- Relationships:
  - `equipments()` — BelongsToMany via `equipment_loan_order`
  - `tasks()` — MorphMany (morphName: `taskable`)
  - `client()` — BelongsTo `Client`
  - `location()` — BelongsTo `Location`
  - `manager()` — BelongsTo `User`
  - `cancelledBy()` — BelongsTo `User`

**`Task` model** updates at `app/Features/Tasks/Models/Task.php`:
- Add `taskable()` MorphTo relationship
- Keep `serviceOrder()` BelongsTo for BC
- `$fillable` gets `taskable_id` + `taskable_type`

**`Equipment` model** updates at `app/Features/Equipments/Models/Equipment.php`:
- Add `loanOrders()` BelongsToMany via `equipment_loan_order`

## Acceptance Criteria

- [ ] `LoanOrder::factory()` generates valid model instances
- [ ] `LoanOrder::equipments` returns associated Equipment collection
- [ ] `LoanOrder::tasks` returns associated Task collection (MorphMany)
- [ ] `Task::taskable` returns the parent model (ServiceOrder or LoanOrder)
- [ ] `Task::serviceOrder` still works for backward compatibility
- [ ] `Equipment::loanOrders` returns associated LoanOrder collection
- [ ] Existing ServiceOrder tests still pass (no regression)

## Blocked by

- [`01-schema-foundation`](./01-schema-foundation.md)
