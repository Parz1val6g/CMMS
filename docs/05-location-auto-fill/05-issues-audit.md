# Architectural Audit — Client Location Auto-Fill Issues

**Audited:** [`docs/05-location-auto-fill/05-issues.md`](docs/05-location-auto-fill/05-issues.md)  
**Codebase Snapshot:** `main` (ahead 2)  
**Date:** 2026-05-13  

---

## Audit Results

### ❌ BLOCKED — ISSUE-002 (Scenario A): Backend validation mismatch

**File:** [`StoreServiceOrderRequest.php`](app/Features/ServiceOrders/Requests/StoreServiceOrderRequest.php:24-29)

**Violation:** The issue's Scenario A logic `formData.delete(f)` for location fields will **always produce a 422 validation error** because [`StoreServiceOrderRequest`](app/Features/ServiceOrders/Requests/StoreServiceOrderRequest.php:24-29) declares `parish_id` and `street` as unconditionally `required`:

```php
'parish_id'  => ['required', 'uuid', 'exists:parishes,id'],
'street'     => ['required', 'string', 'max:255'],
```

**Why this is an architectural concern:** This is a **silent dependency** — ISSUE-002's frontend logic assumes validation will pass when location fields are omitted, but the backend layer was not updated. This violates **boundary integrity**: the frontend submission logic and backend validation rules must agree on the contract. Currently, a `client_location_id` key is already accepted as `nullable`, but the 6 individual fields remain mandatory.

**Remediation:** Add a new backend task (or extend ISSUE-002's scope) to update [`StoreServiceOrderRequest`](app/Features/ServiceOrders/Requests/StoreServiceOrderRequest.php) rules:

| Field | Current | Required |
|-------|---------|----------|
| `parish_id` | `'required'` | `'required_without:client_location_id'` |
| `street` | `'required'` | `'required_without:client_location_id'` |
| `reference_point` | `nullable` (already correct) | — |
| `postal_code` | `nullable` (already correct) | — |
| `latitude` | `nullable` (already correct) | — |
| `longitude` | `nullable` (already correct) | — |
| `client_location_id` | `nullable` (already correct) | — |

---

### ⚠️ WARN — ISSUE-001: Missing data source documentation

**File:** [`ClientLocationController.php`](app/Features/Clients/Controllers/Api/ClientLocationController.php:28-32)

**Concern:** ISSUE-001 describes the `ClientLocationSelector` component's dirty-tracking mechanics in detail but does not document **how the selector fetches client location data**. The API endpoint already exists at `GET /api/clients/{client}/locations` (route: [`clients/{client}/locations`](app/Features/Clients/Routes/api.php:18)), but:

1. The trigger for fetching (on `client_id` field change) is implicit — not documented in the issue.
2. The expected response shape (`ClientLocationResource`) is not referenced — the selector needs to know which fields map to the 6 location form fields.
3. The mechanism to pass the fetched data into the `autofill-location` custom event is implementation-implied but not specified.

**Mitigation:** Add a brief "Data Source" section to ISSUE-001 referencing:
- API route: `GET /api/clients/{client}/locations`
- The selector should watch `client_id` field changes via `modal-field-change` custom events
- Response structure (location → parish_id, street_address → street, landmark → reference_point, etc.)

---

### ⚠️ WARN — ISSUE-002: Scenario logic extraction for testability

**Concern:** The Scenario A/B/C logic is inlined inside [`handleCreate`](resources/js/Features/ServiceOrders/Pages/Index.jsx:59-87), which is a `useCallback` that directly manipulates FormData. ISSUE-003's test plan (#4-6) acknowledges the need to test via mock FormData, but the tight coupling to `handleCreate` makes this fragile.

**Mitigation:** Extract the Scenario logic into a pure helper function `buildCreatePayload(values, clientLocationId, locationsDirty)` in a separate module (e.g., `ServiceOrders/Utils/payload.js`). This:
- Improves **testability** — test the function directly without Mock Modal
- Isolates **volatility** — submission method changes won't affect business logic
- Follows the **aggressive DRY** principle

---

### ✅ PASS — ISSUE-003: Tests

**Status: Ready for implementation**

- Test boundaries are correctly defined (external behavior, not internal refs)
- "What NOT to test" section prevents testing implementation details
- All 7 scenarios map to observable behavior (DOM assertions, payload shape)
- Cross-boundary: frontend tests (ISSUE-003) are correctly separated from backend validation tests (which already exist)

---

### Cross-Item Observations

| Observation | Impact |
|-------------|--------|
| **ISSUE-002 depends on `StoreServiceOrderRequest` changes** (not documented) | Must add backend task or extend ISSUE-002 |
| **ISSUE-001 → ISSUE-002 → ISSUE-003** ordering is correct | Sequential dependency is documented |
| **`ClientLocationSelector` is a new UI component** in ServiceOrders feature | Correct bounded context — no bleed into Clients domain |
| **Custom event pattern** (`autofill-location`, `modal-field-change`) is already supported by [`Modal.jsx`](resources/js/Components/Common/Modal.jsx:72-77) | No new abstraction needed — good |
| **`injectAfterField` prop** is already implemented in [`Modal.jsx`](resources/js/Components/Common/Modal.jsx:99) | No Modal refactoring needed |
| **`client_location_id` field** in [`StoreServiceOrderRequest`](app/Features/ServiceOrders/Requests/StoreServiceOrderRequest.php:15) is already accepted | Schema already anticipates this feature |
| **`ServiceOrderService::create()`** at [`ServiceOrderService.php`](app/Features/ServiceOrders/Services/ServiceOrderService.php:43-46) already handles `client_location_id` | Service layer requires no changes |

---

## Gate Decision

**🟢 PROCEED**

The BLOCKED item was resolved: [`StoreServiceOrderRequest`](app/Features/ServiceOrders/Requests/StoreServiceOrderRequest.php) updated — `parish_id` and `street` changed from `required` to `required_without:client_location_id`.

**Remaining recommendations (non-blocking):**

1. **Document data source:** Add data-fetching details to ISSUE-001 (API endpoint, trigger, response mapping)
2. **(Optional) Extract helper:** Consider extracting Scenario logic into a pure function for testability

The frontend architecture (custom events, `injectAfterField`, `Modal.jsx` autofill support) is well-aligned with the existing codebase. No major refactoring needed.
