# Issue: Loan Order Lifecycle - Return + Cancel
**GitHub:** [#31](https://github.com/Parz1val6g/CMMS/issues/31)

**Parent:** [`04-prd.md`](04-prd.md)

## What to Build

Add the remaining lifecycle methods to LoanOrderService — initiateReturn and cancel — along with their API endpoints, request validation, and authorization policy.

**`LoanOrderService`** additions at `app/Features/LoanOrders/Services/LoanOrderService.php`:

`initiateReturn(LoanOrder $loanOrder): Task` — inside transaction:
1. Guard: status must be CHECKED_OUT
2. Guard: no existing return task (prevent duplicates)
3. Guard: checkout task must be COMPLETED
4. Create return Task: `taskable_id` = loanOrder.id, `taskable_type` = LoanOrder::class, description = equipment_return
5. Return the Task resource

`cancel(LoanOrder $loanOrder): LoanOrder` — inside transaction:
1. Guard: status is not already CANCELLED (idempotent)
2. Guard: status is PENDING (cannot cancel in-flight CHECKED_OUT — must return first)
3. Set status to CANCELLED, set cancelled_at, set cancelled_by
4. Release equipments: loop through equipments, `markAsActive()` if IN_USE
5. Return updated LoanOrder

**`CancelLoanOrderRequest`** at `app/Features/LoanOrders/Requests/CancelLoanOrderRequest.php`:
- Minimal — just authorization check + optional notes_cancel field

**`LoanOrderPolicy`** at `app/Features/LoanOrders/Policies/LoanOrderPolicy.php`:
- Extends `BasePolicy`
- `viewAny`: admin/manager
- `view`: admin/manager
- `create`: admin/manager
- `initiateReturn`: admin/manager (only if PENDING)
- `cancel`: admin/manager (only if not terminal)

**API routes** additions in `app/Features/LoanOrders/Routes/api.php`:
- `POST /api/loan-orders/{id}/return` → `initiateReturn`
- `POST /api/loan-orders/{id}/cancel` → `cancel`
- `DELETE /api/loan-orders/{id}` → `destroy` (soft delete, only if PENDING or CANCELLED)

**`LoanOrderController`** additions:
- `initiateReturn($id)` — calls service.initiateReturn() → 200 + TaskResource
- `cancel($id)` — calls service.cancel() → 200 + LoanOrderResource
- `destroy($id)` — validates state, soft deletes

**Test** — full lifecycle test:
- Create loan → status PENDING
- Attempt return on PENDING → 422 (must be CHECKED_OUT first)
- Cancel PENDING → status CANCELLED, equipment ACTIVE
- Create another loan → checkout task created
- Complete checkout task → then initiateReturn → return task created
- Attempt duplicate return → 422
- Attempt cancel on CHECKED_OUT → 422 (must return first)
- Soft delete loan → 200 + soft-deleted

## Acceptance Criteria

- [ ] `POST /api/loan-orders/{id}/return` creates return Task, returns 200
- [ ] `POST /api/loan-orders/{id}/cancel` sets CANCELLED, releases equipment, returns 200
- [ ] `DELETE /api/loan-orders/{id}` soft-deletes if PENDING or CANCELLED, 422 otherwise
- [ ] `LoanOrderPolicy` gates all endpoints by role
- [ ] Idempotent guards: double cancel, duplicate return
- [ ] State guards: cannot cancel CHECKED_OUT, cannot return PENDING
- [ ] Full lifecycle test passes

## Blocked by

- [`03-create-loan-backend`](./03-create-loan-backend.md)
