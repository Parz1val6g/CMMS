# Issue: Create Loan Order - Full Backend + API
**GitHub:** [#30](https://github.com/Parz1val6g/CMMS/issues/30)

**Parent:** [`04-prd.md`](04-prd.md)

## What to Build

The core create operation for LoanOrders. This is the deep module — encapsulates availability validation, pessimistic locking, equipment state transitions, and auto-creation of the checkout Task — all within a single transaction.

**`LoanOrderService`** at `app/Features/LoanOrders/Services/LoanOrderService.php`:

`create(array $data, string $managerId): LoanOrder` — inside `TransactionHandler::execute()`:
1. Validate equipment IDs are provided
2. `Equipment::whereIn('id', $equipmentIds)->lockForUpdate()` — pessimistic lock
3. Verify every equipment `isAvailableForLoan()` — throw `EquipmentUnavailableException` if not
4. Create Location (if parish_id provided) — same pattern as ServiceOrderService
5. Create `LoanOrder` with status PENDING, manager_id, client_id, location_id
6. Call `$equipment->markAsInUse()` for each equipment
7. Sync equipments via `equipment_loan_order` pivot
8. Auto-create checkout Task: `Task::create(['taskable_id' => $loanOrder->id, 'taskable_type' => LoanOrder::class, 'manager_id' => $managerId, 'description' => __('messages.task_names.equipment_loan'), 'status' => TaskStatus::PENDING])`
9. Return LoanOrder with loaded relations

**`StoreLoanOrderRequest`** at `app/Features/LoanOrders/Requests/StoreLoanOrderRequest.php`:
- `authorize()`: `$this->user()->can('create', LoanOrder::class)`
- `rules()`: explicit whitelist — client_id (required|uuid), manager_id (required|uuid), equipment_ids (required|array|min:1), equipment_ids.* (uuid|exists:equipments,id), description (nullable|string), parish_id/street/postal_code/latitude/longitude (location fields), reference_point (nullable)

**`LoanOrderController`** (API) at `app/Features/LoanOrders/Controllers/Api/LoanOrderController.php`:
- `store(StoreLoanOrderRequest)` — calls service.create() → returns `LoanOrderResource` with 201
- `show($id)` — returns `LoanOrderResource` with equipments + tasks loaded

**`LoanOrderResource`** at `app/Features/LoanOrders/Resources/LoanOrderResource.php`:
- Exposed fields: id, reference, status, client, manager, location, equipments, tasks, description, notes_checkout, notes_return, checked_out_at, returned_at, cancelled_at, timestamps

**API routes** in `app/Features/LoanOrders/Routes/api.php`:
- `POST /api/loan-orders` → `store`
- `GET /api/loan-orders/{id}` → `show`

**Test** — `LoanOrderApiTest::test_can_create_loan_order()`:
- Creates via API with valid equipment
- Asserts 201, status PENDING, equipment marked IN_USE, checkout Task created

## Acceptance Criteria

- [ ] `POST /api/loan-orders` with valid data returns 201 + LoanOrderResource
- [ ] Equipment availability validated before creation (throws 422 if unavailable)
- [ ] Pessimistic lock prevents concurrent double-booking of same equipment
- [ ] Equipment status transitions to IN_USE on successful creation
- [ ] Checkout Task auto-created with taskable_type = LoanOrder
- [ ] `POST /api/loan-orders` with invalid data returns 422 with validation errors
- [ ] `POST /api/loan-orders` without auth returns 401
- [ ] `POST /api/loan-orders` without permission returns 403
- [ ] `GET /api/loan-orders/{id}` returns full detail with equipments + tasks

## Blocked by

- [`02-loanorder-model`](./02-loanorder-model.md)
