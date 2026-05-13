# Issue: Frontend - Loan Orders Page + Drawer
**GitHub:** [#32](https://github.com/Parz1val6g/CMMS/issues/32)

**Parent:** [`04-prd.md`](04-prd.md)

## What to Build

Full frontend for the LoanOrders module — list page with DataManager, create modal with form schema, and a WorkspaceDrawer with detail tabs.

**`LoanOrderFormSchema`** at `app/Features/LoanOrders/LoanOrderFormSchema.php`:
- `create()`: SectionHeader(core) → SelectInput(client_id), SelectInput(manager_id), TextAreaInput(description), SelectInput(equipment_ids, multiple, loanable scope), SectionHeader(location) → SelectInput(parish_id), TextInput(street), TextInput(reference_point), TextInput(postal_code), MapInput(coordinates)
- `update()`: similar + SelectInput(status) for state changes

**`LoanOrderPresenter`** at `app/Features/LoanOrders/Presenters/LoanOrderPresenter.php`:
- Presenter methods matching the ServiceOrderPresenter pattern
- `statusLabel()`, `priorityLabel()`, `equipmentList()`, formatted timestamps
- Follows existing presenter contract used by DataManager

**`LoanOrderPageController`** at `app/Features/LoanOrders/Controllers/Web/LoanOrderPageController.php`:
- `index()`: paginated list with filters, returns Inertia page
- `create()`/`edit()`: optional (could use API + modal pattern like ServiceOrders)

**Web routes** in `app/Features/LoanOrders/Routes/web.php`:
- `GET /loan-orders` → LoanOrderPageController@index
- Sidebar entry in `routes/web.php` (or shared sidebar config)

**Frontend — `LoanOrders/Pages/Index.jsx`** at `resources/js/Features/LoanOrders/Pages/Index.jsx`:
- Uses `AppLayout` with breadcrumbs: Dashboard → Loans
- Uses `DataManager` for list with columns: reference, client, manager, status, created_at
- Create button opens Modal with `LoanOrderFormSchema::create()`
- Card click opens WorkspaceDrawer

**Frontend — `LoanOrders/Components/LoanOrderDrawer.jsx`** at `resources/js/Features/LoanOrders/Components/LoanOrderDrawer.jsx`:
- Drawer title: reference + status badge
- Tabs:
  - **Details tab**: client, manager, description, location, timestamps
  - **Equipment tab**: list of EquipmentCards (reuse pattern, or embed directly)
  - **Tasks tab**: tasks tree (reuse SOTasksTree or simplified version)
  - **History tab**: audit trail (created_at, checked_out_at, returned_at, cancelled_at)
- Action buttons (based on status + permissions):
  - PENDING: Cancel button
  - CHECKED_OUT: Initiate Return button

**Sidebar entry**: Add "Loans" link to sidebar navigation pointing to `/loan-orders`

## Acceptance Criteria

- [ ] `/loan-orders` page renders with DataManager listing all loans
- [ ] Create modal with form schema renders all fields
- [ ] Form validation errors display correctly
- [ ] Drawer opens on row click with 3+ tabs
- [ ] Equipment tab shows assigned equipment with status badges
- [ ] Tasks tab shows checkout/return tasks
- [ ] Cancel button visible on PENDING loans (with permission gate)
- [ ] Initiate Return button visible on CHECKED_OUT loans (with permission gate)
- [ ] Sidebar has "Loans" link pointing to `/loan-orders`
- [ ] Mobile-responsive layout (Bootstrap 5 utility classes)

## Blocked by

- [`04-loan-lifecycle-return-cancel`](./04-loan-lifecycle-return-cancel.md)
