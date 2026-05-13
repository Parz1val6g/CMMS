# Issues — Client Location Auto-Fill on Service Order Creation

> Generated from: [`docs/05-location-auto-fill/05-prd.md`](docs/05-location-auto-fill/05-prd.md)
> Based on grill-me session: [`docs/05-location-auto-fill/05-grill-me.md`](docs/05-location-auto-fill/05-grill-me.md)

---

## ISSUE-001: ClientLocationSelector — dirty tracking + edit detection

**Labels:** `frontend`, `react`, `enhancement`
**Milestone:** M1
**Estimated:** 1.5h
**Dependencies:** None (Modal.jsx already has injectAfterField and autofill-location support)

### Description

Refactor the `ClientLocationSelector` component in [`Index.jsx`](resources/js/Features/ServiceOrders/Pages/Index.jsx) to implement dirty tracking that detects when the user manually edits any of the 6 autofilled location fields, and clears the selector accordingly.

### Why

The grill-me session (Q2) decided: if user edits ANY of the 6 location fields after autofill, the select must be cleared immediately (fields stay visible). This requires the selector to know whether autofilled values have been dirtied.

### Implementation

**Changes needed in `ClientLocationSelector`:**

1. **Add `snapshotRef`** (useRef) — stores the initial autofilled values when a location is selected
2. **Add `isAutoFillingRef`** (useRef boolean) — set to `true` during autofill, `false` after, prevents dirty-detection from firing during the autofill itself
3. **Add `onDirtyChange` prop** — callback `(dirty: boolean) => void` that communicates dirtiness to the parent (`Index.jsx`). The parent uses this to decide Scenario A vs B in `handleCreate`.
4. **Listen for `modal-field-change`** custom events:
   - If the changed field is one of the 6 location fields (`parish_id`, `street`, `reference_point`, `postal_code`, `latitude`, `longitude`)
   - And `isAutoFillingRef.current` is `false`
   - And a snapshot exists
   - And the new value differs from the snapshot → clear `selectedId`, notify parent via `onClientLocationChange('')` and call `onDirtyChange(true)`
5. **On location selection (handleChange):**
   - Set `isAutoFillingRef.current = true`
   - Call `onDirtyChange(false)` to reset dirtiness on fresh selection
   - Dispatch `autofill-location` event
   - Capture snapshot of autofilled values
   - After a microtask (setTimeout 0), set `isAutoFillingRef.current = false`
6. **On modal close (when `isOpen` transitions from `true` to `false`):**
   - Reset `snapshotRef.current` to `null`
   - Call `onDirtyChange(false)` to clear dirty state for next open

### Location fields (Q3)
All 6: `parish_id`, `street`, `reference_point`, `postal_code`, `latitude`, `longitude`.

### Acceptance Criteria
- [ ] Selecting a client location autofills all 6 fields
- [ ] Editing ANY of the 6 fields after autofill clears the selector immediately
- [ ] Editing a non-location field (e.g. `description`) does NOT clear the selector
- [ ] Fields remain visible after selector is cleared (no field reset)
- [ ] Selector resets cleanly when modal is closed/reopened (snapshotRef + dirty state)
- [ ] `isAutoFilling` flag prevents spurious clearing during autofill
- [ ] `onDirtyChange(true)` fires when an autofilled field is manually edited
- [ ] `onDirtyChange(false)` fires on fresh location selection or modal close

---

## ISSUE-002: Modal wiring + handleCreate — Scenario A/B/C submission logic

**Labels:** `frontend`, `react`, `enhancement`
**Milestone:** M1
**Estimated:** 1h
**Dependencies:** ISSUE-001

### Description

Wire the `ClientLocationSelector` into the create Modal with proper positioning, and update `handleCreate` to implement the three submission scenarios defined in the grill-me session.

### Why

The Modal invocation currently renders `ClientLocationSelector` at the top of the form. Q1 decided it must go right below `client_id`, before `equipment_ids`. Q4 defined three submission scenarios that `handleCreate` must implement.

### Implementation

**A. Modal positioning in [`Index.jsx`](resources/js/Features/ServiceOrders/Pages/Index.jsx):**
```jsx
<Modal
  formSchema={createFormSchema}
  routes={routes}
  size="lg"
  open={showModal}
  onClose={() => setShowModal(false)}
  onSubmit={handleCreate}
  injectAfterField="client_id"   // ← ADD THIS
>
  <ClientLocationSelector
    isOpen={showModal}
    onClientLocationChange={setClientLocationId}
    onDirtyChange={setLocationsDirty}
  />
</Modal>
```

**B. `handleCreate` update — Scenario A/B/C:**
```js
// Scenario A: Location selected + no edits → send only client_location_id, omit 6 fields
// Scenario B: Location selected + user edited → select cleared, send edited fields
// Scenario C: No location selected → current behavior

const LOCATION_FIELDS = ['parish_id', 'street', 'reference_point', 'postal_code', 'latitude', 'longitude'];

// Before appending to FormData:
if (clientLocationId && !locationsDirty) {
  // Scenario A
  formData.append('client_location_id', clientLocationId);
  LOCATION_FIELDS.forEach(f => formData.delete(f));
} else if (clientLocationId && locationsDirty) {
  // Scenario B — don't append client_location_id, keep fields as-is
  // (selector already cleared + dirty signalled by ClientLocationSelector)
}
// Scenario C: no clientLocationId — default behavior, all fields sent
```

**C. New state in `Index.jsx`:**
- Add `const [locationsDirty, setLocationsDirty] = useState(false);` alongside existing state declarations
- Wire it as `onDirtyChange={setLocationsDirty}` on `<ClientLocationSelector>`
- Reset in `openCreate`: set `setLocationsDirty(false)` alongside existing resets

### Acceptance Criteria
- [ ] Modal renders the location selector between `client_id` and `equipment_ids`
- [ ] **Scenario A:** Location selected + no edits → FormData contains `client_location_id`, omits 6 fields
- [ ] **Scenario B:** Location selected + user edited → FormData contains 6 fields, no `client_location_id`
- [ ] **Scenario C:** No location selected → FormData contains all fields as current behavior
- [ ] Backend receives correct payload in all 3 scenarios (no 422 errors)
- [ ] Existing non-location fields (description, priority, photo, etc.) are unaffected

---

## ISSUE-003: Tests for autofill behavior (ClientLocationSelector + handleCreate scenarios)

**Labels:** `frontend`, `react`, `testing`, `qa`
**Milestone:** M1
**Estimated:** 1h
**Dependencies:** ISSUE-002

### Description

Write integration and unit tests covering all 7 scenarios defined in the PRD's Testing Decisions section. Tests must verify external behavior (DOM assertions, submission payloads), not internal hook state.

### Why

The PRD dedicates a full testing section with 7 specific tests and a "What NOT to test" list. Without dedicated test coverage, the autofill behavior is fragile against regressions.

### Implementation

**A. Component integration tests (`ClientLocationSelector`):**

| # | Test | Approach |
|---|------|----------|
| 1 | Selector appears only when client selected + client has locations | Render with/without `clientId` and `locations`; assert DOM presence |
| 2 | Location selection dispatches `autofill-location` with correct payload | Mock `document.dispatchEvent`; assert event detail shape |
| 3 | Manual edit on any of the 6 fields clears the selector | Simulate `modal-field-change` after autofill; assert selector value reset |
| 7 | Modal renders selector between `client_id` and `equipment_ids` | Render Modal with `injectAfterField="client_id"`; assert DOM order |

Follow prior art from existing form component tests in the codebase.

**B. Unit tests for `handleCreate` submission logic:**

Extract the Scenario A/B/C logic into a pure helper function (or test via mock FormData).

| # | Test | Assertion |
|---|------|-----------|
| 4 | Scenario A — `clientLocationId` set, not dirty | FormData contains `client_location_id`, omits 6 location fields |
| 5 | Scenario B — `clientLocationId` was set, now dirty (locationsDirty=true) | FormData contains 6 fields, no `client_location_id` |
| 6 | Scenario C — no location selected | FormData contains all fields as current behavior (no change) |

### What NOT to test (from PRD)
- Backend validation of `client_location_id` (already covered by existing tests)
- Internal refs or `isAutoFilling` flag values (implementation detail)
- FormSchema field ordering (tested by schema unit tests)

### Acceptance Criteria
- [ ] All 7 PRD tests pass (selector visibility, autofill dispatch, dirty clear, 3 submission scenarios, modal positioning)
- [ ] Tests use DOM assertions and event simulation (not internals inspection)
- [ ] Tests cover boundary: non-location field edit does NOT clear selector
- [ ] Tests cover boundary: selector + fields reset on modal close/reopen
