# Client Location Auto-Fill on Service Order Creation

## Context
Grill-me session to design the behavior of the client location selector on the Service Order creation form.

## Files identified
- `resources/js/Features/ServiceOrders/Pages/Index.jsx`
- `resources/js/Components/Common/Modal.jsx`
- `resources/js/Hooks/useClientLocations.js`
- `app/Features/ServiceOrders/ServiceOrderFormSchema.php`
- `app/Features/ServiceOrders/Requests/StoreServiceOrderRequest.php`
- `app/Features/ServiceOrders/Services/ServiceOrderService.php`

## Design decisions

### Q1 - Position
Select goes right below `client_id`, before `equipment_ids`.

### Q2 - Edit detection
If user edits ANY of the 6 location fields after autofill, select is cleared immediately. Fields stay visible.

### Q3 - Scope of location fields
All 6: `parish_id`, `street`, `reference_point`, `postal_code`, `latitude`, `longitude`.

### Q4 - Submission logic
- **Scenario A:** Location selected + no edits -> send only `client_location_id`, omit 6 fields
- **Scenario B:** Location selected + user edited -> select cleared, send edited fields, no `client_location_id`
- **Scenario C:** No location selected -> current behavior

## Implementation plan
1. `Modal.jsx`: Add `injectAfterField` prop to inject children after a specific schema field
2. `Index.jsx`:
   - Refactor `ClientLocationSelector` with dirty tracking (snapshot ref, isAutoFilling flag)
   - Listen for `modal-field-change` to detect manual edits on location fields
   - Update `handleCreate` to conditionally strip/keep location fields in FormData
3. Backend: No changes needed (already supports `client_location_id`)