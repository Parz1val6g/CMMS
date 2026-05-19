# Decisions Resolved — LoanOrders Extraction

**Date:** 2026-05-13  
**Gate:** Previously ❌ BLOCKED → now 🟢 Ready to proceed once docs are updated

---

## Q1 — Cancel Guard (Issue 04)

### Decision

**Block cancel from CHECKED_OUT.** If equipment has already been checked out, the loan can only be cancelled after the equipment is returned first.

### Corrected Lifecycle

```
PENDING ──checkout──► CHECKED_OUT ──return──► RETURNED
   │
   └──cancel──► CANCELLED
```

- `PENDING → CANCELLED` ✅ allowed (equipment never left)
- `CHECKED_OUT → CANCELLED` ❌ blocked (must return equipment first)
- `CHECKED_OUT → RETURNED` ✅ via `complete()` (new Issue 09 — see Q3)
- `RETURNED → CANCELLED` ❓ not specified — assumed terminal

### Actions Required

1. Update [`04-loan-lifecycle-return-cancel.md`](04-loan-lifecycle-return-cancel.md:21) — remove the `CHECKED_OUT → CANCELLED` arrow from the diagram
2. Update [`04-prd.md`](04-prd.md:147-151) — correct the lifecycle diagram to match
3. Update `LoanOrderService::cancel()` guard to only allow cancel when status is `PENDING`
4. Ensure `LoanOrderService::checkout()` sets equipment status to `IN_USE`
5. Ensure `LoanOrderService::complete()` releases equipment (see Q3)

---

## Q2 — WorkflowType Enum (Issue 07)

### Decision

**Keep `WorkflowType` as `@deprecated`.** Do NOT delete the file.

### Actions Required

See dedicated doc: [`docs/new-features/deprecate-workflowtype-enum.md`](../new-features/deprecate-workflowtype-enum.md)

---

## Q3 — RETURNED Status + `complete()` Method

### Decision

**Separate issue** — not bundled into Issue 04. GitHub issue must be updated.

### New Issue (09) Scope

1. Add `RETURNED` case to [`LoanOrderStatus`](01-schema-foundation.md:41) enum
2. Add `RETURNED` to [`LoanOrderStatus` state machine](../../app/Core/Enums/LoanOrderStatus.php)
3. Add `complete()` method to `LoanOrderService`:
   - Guard: status must be `CHECKED_OUT`
   - Transition: `CHECKED_OUT → RETURNED`
   - Side effect: release all equipment (`status = 'AVAILABLE'`)
   - Idempotent: no-op if already `RETURNED`
4. Add route `POST /api/loan-orders/{id}/complete`
5. Add tests for the complete flow
6. Update frontend: add "Complete" button in `CHECKED_OUT` state

### Acceptance Criteria (Future Issue 09)

- [ ] `LoanOrderStatus::RETURNED` exists
- [ ] `POST /api/loan-orders/{id}/complete` returns 200
- [ ] All equipment in the loan transitions to `AVAILABLE`
- [ ] Calling `complete()` twice is idempotent (200, not error)
- [ ] Calling `complete()` on non-CHECKED_OUT returns 422
- [ ] Frontend shows "Complete" action only when status is `CHECKED_OUT`

---

## Updated Dependency Graph

```
01 → 02 → 03 → 04 → 05 → 06 → [07 + NEW(deprecate-workflowtype)] → 08 → 09 (RETURNED)
```

Issues 07 and the new deprecation task can run in parallel since they touch different files.
