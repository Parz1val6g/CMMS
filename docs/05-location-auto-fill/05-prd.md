# PRD — Client Location Auto-Fill on Service Order Creation

**Product Requirements Document**

| Campo | Valor |
|-------|-------|
| **Data** | 2026-05-12 |
| **Versão** | 1.0 |
| **Status** | Aprovado |
| **Prioridade** | Média |

---

## Problem Statement

When creating a Service Order, operators must manually fill in 6 location fields (`parish_id`, `street`, `reference_point`, `postal_code`, `latitude`, `longitude`) even when the client already has a registered location with all this data. This is repetitive, error-prone, and slows down the creation flow. The system already stores client locations via `ClientLocation` entities, but there is no mechanism to auto-fill the service order form from a selected client location.

---

## Solution

Add a **Client Location selector** to the Service Order creation modal, positioned directly below the `client_id` field and above `equipment_ids`. When the operator selects a client location, the 6 location fields (`parish_id`, `street`, `reference_point`, `postal_code`, `latitude`, `longitude`) are auto-filled from the location data.

**Autofill + edit detection** follows these rules:
- If the operator **does not edit** any of the 6 fields after autofill → send only `client_location_id`, omit the 6 fields on submission (Scenario A).
- If the operator **edits** any of the 6 fields after autofill → clear the selector, send the edited fields, omit `client_location_id` (Scenario B).
- If no location is selected → current behavior unchanged (Scenario C).

---

## User Stories

1. As a service order operator, I want to select a client location from a dropdown so that the 6 address fields are auto-filled automatically.
2. As a service order operator, I want the location selector to appear only after a client is selected and only when that client has registered locations.
3. As a service order operator, I want the location selector to be positioned right below the client field so the visual flow is logical.
4. As a service order operator, I want to be able to manually edit any auto-filled field so that I can adjust the address for a specific service order.
5. As a service order operator, I want the selector to be immediately cleared if I edit any auto-filled field so that I know the form is no longer bound to a stored location.
6. As a service order operator, I want the 6 manually-edited fields to remain visible (not cleared) after the selector is cleared so I don't lose my changes.
7. As an API consumer, I want the backend to accept `client_location_id` so that the service order can reference the stored location without duplicating field data.
8. As an API consumer, I want the backend to receive the individual 6 fields when the operator has manually edited them, so that the custom address is stored instead of the client location reference.
9. As a system administrator, I want the submission logic to be deterministic (Scenario A/B/C) so that there is no ambiguity about which address takes precedence.

---

## Implementation Decisions

### Modules to modify

#### 1. Modal.jsx (Common component)
The Modal component already supports the `injectAfterField` prop and the `autofill-location` custom event listener. No changes needed — the prop is already implemented and working.

#### 2. ServiceOrders/Pages/Index.jsx
This is the primary module for the feature. Three modifications are required:

**a) ClientLocationSelector refactor — dirty tracking**
Add internal state management using:
- A `snapshotRef` (useRef) that captures the initial autofilled values when a location is selected
- An `isAutoFilling` flag that prevents the dirty-detection logic from firing during the autofill itself
- When `isAutoFilling` is false and a manual edit is detected on any of the 6 location fields, the selector is cleared and `clientLocationId` is reset

**b) Modal field wiring — position + injectAfterField**
The Modal invocation in Index.jsx must pass `injectAfterField="client_id"` so that the `ClientLocationSelector` children render between `client_id` and `equipment_ids` in the form.

**c) handleCreate — Scenario A/B/C submission logic**
The `handleCreate` function currently appends `client_location_id` unconditionally. It must be updated to:

- **Scenario A:** If `clientLocationId` is set and no fields were dirtied → append only `client_location_id`, strip `parish_id`, `street`, `reference_point`, `postal_code`, `latitude`, `longitude` from FormData
- **Scenario B:** If `clientLocationId` was set but fields were dirtied → clear `clientLocationId`, do NOT append `client_location_id`, keep the 6 fields as-is in FormData
- **Scenario C:** No location selected → current behavior (all fields sent as-is)

**Dirty detection mechanism:**
- Store a snapshot of autofilled values when a location is selected
- On each `modal-field-change` event for a location field, compare current value vs snapshot
- If any mismatch found (and not currently auto-filling), clear the selector

#### 3. Backend — No changes required
The `StoreServiceOrderRequest` already accepts `client_location_id` as nullable. The `ServiceOrderService::create()` already handles `client_location_id`. No backend changes needed.

### Data flow

```
User selects client → ClientLocationSelector appears
  → User selects location → autofill-location event dispatched
    → Modal.updateValue fills 6 fields + takes snapshot
  → User edits field → modal-field-change fires
    → ClientLocationSelector detects dirtied field
    → Clears selection + resets clientLocationId

On submit:
  Scenario A: clientLocationId set, not dirty → send client_location_id only
  Scenario B: clientLocationId was set, now dirty → send 6 fields, no client_location_id
  Scenario C: never selected → send all fields as normal
```

### API Contract (unchanged)
```
POST /service-orders
  - client_location_id: uuid | null
  - parish_id: uuid | null (stripped in Scenario A)
  - street: string | null (stripped in Scenario A)
  - reference_point: string | null (stripped in Scenario A)
  - postal_code: string | null (stripped in Scenario A)
  - latitude: number | null (stripped in Scenario A)
  - longitude: number | null (stripped in Scenario A)
```

---

## Testing Decisions

### What makes a good test
- Tests should verify external behavior (submission payload, field visibility, selector clearing), not internal state or implementation details of hooks.
- Frontend tests should use DOM assertions and simulate real events (dispatchEvent for custom events).

### Which modules to test

| Module | Test type | Prior art |
|--------|-----------|-----------|
| `ClientLocationSelector` | Component integration | Existing form component tests in codebase |
| `handleCreate` submission logic | Unit (extractable pure function) | Similar submission tests in other features |
| Modal rendering with `injectAfterField` | Component integration | Modal tests in common components |

### What to test
1. ClientLocationSelector appears only when client is selected and has locations
2. Location selection dispatches `autofill-location` with correct payload
3. Manual edit on any of the 6 fields clears the selector
4. handleCreate strips 6 fields when Scenario A (location selected, no edits)
5. handleCreate keeps 6 fields when Scenario B (location was selected, then edited)
6. handleCreate behaves as current when Scenario C (no location selected)
7. Modal renders selector between client_id and equipment_ids when injectAfterField="client_id"

### What NOT to test
- Backend validation of `client_location_id` (already covered by existing tests)
- Internal refs or isAutoFilling flag values (implementation detail)
- FormSchema field ordering (tested by schema unit tests)

---

## Out of Scope

- Backend changes to `ServiceOrderService` or `StoreServiceOrderRequest` (already supported)
- Client Location CRUD management (exists separately)
- Auto-fill on the **edit** form (create only)
- Map/coordinates auto-correction or reverse geocoding
- UI for bulk-importing client locations

---

## Further Notes

- The grill-me session that produced these decisions is documented in [`docs/05-location-auto-fill/05-grill-me.md`](docs/05-location-auto-fill/05-grill-me.md).
- The Modal component's `injectAfterField` prop and `autofill-location` event listener were pre-built during the grill-me validation phase and are already merged.
- No database migration is required — the `client_location_id` column already exists on `service_orders`.
- The dirty-detection strategy (snapshot ref + isAutoFilling flag) was chosen over a more complex state machine to keep the solution minimal and linear, in line with project architecture principles.
