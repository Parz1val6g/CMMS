# Issue: Cleanup ServiceOrders - Remove Loan Branches
**GitHub:** [#34](https://github.com/Parz1val6g/CMMS/issues/34)

**Parent:** [`05-prd.md`](../03-loans/04-logic-migration/05-prd.md)

## What to Build

After the data migration is complete, strip all loan-related code from the ServiceOrders module. This eliminates the coupled workflow and reduces complexity.

**Backend changes:**

[`ServiceOrderService`](app/Features/ServiceOrders/Services/ServiceOrderService.php):
- `create()`: remove `$isLoan` branch, `Equipment::lockForUpdate()`, `markAsInUse()`, equipment sync, `equipment_ids` handling
- `cancel()`: remove `releaseEquipment()` call (equipment release now in LoanOrderService)
- `delete()`: remove `releaseEquipment()` call
- `complete()`: remove equipment release on completion block
- Remove entire `initiateReturn()` method
- Remove entire `releaseEquipment()` private method
- Remove `cancelCascade()` loan early-return
- Remove `WorkflowType::LOAN` imports and references throughout

[`ServiceOrderFormSchema`](app/Features/ServiceOrders/ServiceOrderFormSchema.php):
- Remove `ToggleInput::make('workflow_type')` from create and update
- Remove `SelectInput::make('equipment_ids')` from create and update
- Remove `editEquipmentOptions()` method
- Remove `equipmentOptions()` method (loanable scope)
- Remove `WorkflowType` import

[`StoreServiceOrderRequest`](app/Features/ServiceOrders/Requests/StoreServiceOrderRequest.php):
- Remove bifurcated validation: unify into a single `$rules` array
- Remove `WorkflowType` import, `$isLoan` branching, `equipment_ids` rules, `prohibited` rules on sectors/service_type
- `client_id` becomes `required`, `sector_ids` becomes `required|array|min:1`

[`ServiceOrder` Model](app/Features/ServiceOrders/Models/ServiceOrder.php):
- Remove `workflow_type` from `$fillable` and `$casts`
- Remove `equipments()` BelongsToMany relation
- Remove `WorkflowType` import

[`CreateLoanTasks` Listener](app/Features/ServiceOrders/Listeners/CreateLoanTasks.php):
- **DELETE entire file** — logic moved to LoanOrderService::create()

[`WorkflowType` Enum](app/Core/Enums/WorkflowType.php):
- **DELETE entire file** — no longer referenced anywhere

**Frontend changes:**

[`ServiceOrders/Pages/Index.jsx`](resources/js/Features/ServiceOrders/Pages/Index.jsx):
- Remove `isLoan` conditional from `soTabs` (equipment tab)
- Remove `SOEquipmentTab` function component
- Remove `EquipmentCard` function component
- Remove `ClientLocationSelector` function component
- Remove unused imports (`useClientLocations` hook)

[`TaskTreeNode.jsx`](resources/js/Features/ServiceOrders/Components/DrawerTabs/TaskTreeNode.jsx):
- Remove `showReturnBtn` logic and `RotateCcw` icon
- Remove `workflowType` and `onInitiateReturn` and `hasReturnTask` props
- Clean up related conditional rendering

**Clear [migration flag](docs/03-loans/04-logic-migration/05-prd.md)**: Add a final migration to drop `workflow_type` column from `service_orders` table and `migrated_to_loan_id` flag (if no longer needed).

## Acceptance Criteria

- [ ] `ServiceOrderService` has zero references to `WorkflowType::LOAN` or `$isLoan`
- [ ] `ServiceOrderFormSchema` has no ToggleInput or equipment fields
- [ ] `StoreServiceOrderRequest` has unified validation without branching
- [ ] `CreateLoanTasks.php` file is deleted
- [ ] `WorkflowType.php` enum file is deleted
- [ ] ServiceOrders frontend has no loan-specific tabs or components
- [ ] `TaskTreeNode` no longer receives loan-related props
- [ ] All existing ServiceOrder tests still pass
- [ ] No regression in standard ServiceOrder creation flow

## Blocked by

- [`06-data-migration-soft-ref`](./06-data-migration-soft-ref.md)
