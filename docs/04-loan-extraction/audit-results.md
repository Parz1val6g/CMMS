# Architectural Audit: LoanOrders Extraction

**Audit Date:** 2026-05-13  
**Scope:** `docs/04-loan-extraction/` (Issues #01–#08)  
**Method:** [`audit-arch`](../../.roo/skills/audit-arch/SKILL.md) — boundary integrity, dependency direction, volatility isolation, slice cohesion, glossary consistency

---

## Executive Summary

**Gate Decision: ❌ BLOCKED — remediation required before proceeding**

The plan is structurally sound (clean DDD extraction, proper bounded context separation, correct use of existing patterns). However, **3 architectural violations** and **4 significant concerns** must be resolved:

| Severity | Count |
|----------|-------|
| ❌ BLOCKED | 2 |
| ⚠️ WARN | 4 |
| ✅ PASS | 2 |

---

## ❌ BLOCKED (requires remediation)

### Issue 04 — Cancel guard contradicts PRD lifecycle diagram

**Violation:** Internal inconsistency between the issue spec and the PRD lifecycle diagram.

- [`04-loan-lifecycle-return-cancel.md`](04-loan-lifecycle-return-cancel.md:21) states: *"Guard: status is PENDING (cannot cancel in-flight CHECKED_OUT — must return first)"*
- [`04-prd.md`](04-prd.md:147-151) lifecycle diagram shows:

```
PENDING ──checkout──► CHECKED_OUT ──return──► RETURNED
   │                      │
   └──cancel──────────────┴──cancel──► CANCELLED
```

The PRD explicitly allows cancel from CHECKED_OUT. The issue explicitly forbids it. These cannot both be correct.

**Remediation:** Choose one design and make both documents consistent. If cancel from CHECKED_OUT is allowed, the guard must change and the service must release equipment. If not, the PRD diagram must be corrected.

---

### Issue 07 — Deleting `WorkflowType` enum breaks existing records

**Violation:** Destructive change to a shared enum that legacy records depend on.

- [`07-cleanup-serviceorders.md`](07-cleanup-serviceorders.md:42) says: **DELETE entire file** `WorkflowType.php`
- [`ServiceOrder` Model](../../app/Features/ServiceOrders/Models/ServiceOrder.php:41) casts `workflow_type` to `WorkflowType::class`
- After data migration (Issue 06), old `service_orders` records still have `workflow_type = 'loan'` in the database
- Any code path that reads a migrated service order (API index/show, exports, background jobs, audit trails) will trigger:
  ```
  Class "App\Core\Enums\WorkflowType" not found
  ```
  when Laravel attempts to cast the `'loan'` value

This is a **runtime crash** waiting to happen, not just a compile-time error.

**Remediation:**
1. Keep `WorkflowType` enum but mark it `@deprecated` — do not delete it
2. Remove the `$casts` entry from `ServiceOrder` model, letting the raw string pass through
3. Alternatively, keep the enum but remove only the `LOAN` case, leaving `STANDARD` for BC
4. Plan for a **future** cleanup migration to drop the column entirely from `service_orders` after data verification

---

## ⚠️ WARN (minor concerns, document and proceed)

### Issue 01 — Missing data backfill in schema migration

**Concern:** Migration 3 adds nullable `taskable_id`/`taskable_type` to `tasks`, but no data backfill is included in the migration itself. Existing tasks remain with `NULL` in both columns.

Between Issue 01 (schema) and Issue 06 (data migration command), the application runs in an inconsistent state:
- New loan order tasks will have `taskable_*` populated
- All existing tasks will have `taskable_*` = NULL
- Any code expecting `$task->taskable` to return a parent will get `null` for existing records

**Mitigation:** Include a data backfill in Migration 3 itself (or as a separate migration immediately after):
```php
// In the migration's up():
DB::table('tasks')
    ->whereNotNull('service_order_id')
    ->update([
        'taskable_id' => DB::raw('service_order_id'),
        'taskable_type' => \App\Features\ServiceOrders\Models\ServiceOrder::class,
    ]);
```

---

### Issue 04 — `RETURNED` status never set (incomplete lifecycle)

**Concern:** The lifecycle diagram shows `RETURNED` as a terminal state, but:
- [`LoanOrderStatus`](../../docs/04-loan-extraction/01-schema-foundation.md:41) only defines `PENDING`, `CHECKED_OUT`, `CANCELLED`
- [`initiateReturn()`](../../docs/04-loan-extraction/04-loan-lifecycle-return-cancel.md:12-18) creates a return Task but does **not** set the loan order to RETURNED
- The PRD mentions a `complete()` method (*"automático quando return task completa → status RETURNED, release equipments"*), but Issue 04 does not implement it

This means: equipment returns Task is created → Task is completed → loan order stays permanently in `CHECKED_OUT` with equipment still marked `IN_USE`. The equipment is never released.

**Mitigation:** Either:
1. Add a `complete()` method to `LoanOrderService` that transitions `CHECKED_OUT → RETURNED` and releases equipment, OR
2. Add an event listener on `TaskCompleted` that auto-completes the loan order when the return task finishes
3. Add `RETURNED` to `LoanOrderStatus` enum

---

### Issue 01 — `reference` vs `process` column naming ambiguity

**Concern:** [`01-schema-foundation.md`](01-schema-foundation.md:11) defines the column as `reference VARCHAR(20) UNIQUE`. The [`HasAutoReference`](../../app/Core/Traits/HasAutoReference.php:18) trait defaults `referenceColumn()` to `'reference'`. But:
- [`ServiceOrder`](../../app/Features/ServiceOrders/Models/ServiceOrder.php:17-20) overrides `referenceColumn()` to return `'process'`
- [`04-grill-me.md`](04-grill-me.md:119) shows the column as `process` not `reference`
- The Issue 01 doc and Issue 02 doc use `reference` inconsistently

It's unclear whether `LoanOrder` should use `reference` or `process` as the auto-reference column.

**Mitigation:** Explicitly state in `LoanOrder` model spec: `referenceColumn()` returns `'reference'` (not overriding) so the column name in the schema is `reference`. Update all docs to align.

---

### Issue 02 — Task model backward compatibility risk

**Concern:** [`02-loanorder-model.md`](02-loanorder-model.md:24) says *"Keep `serviceOrder()` BelongsTo for BC"*. After migration:
- Tasks attached to ServiceOrders → `$task->serviceOrder` returns the ServiceOrder
- Tasks attached to LoanOrders → `$task->serviceOrder` returns **null** (because `service_order_id` is null)

Any code in the codebase that calls `$task->serviceOrder` without null-checking will silently fail for loan tasks. This affects:
- Existing API resources
- Existing notification/listener code
- Any view/computed attribute that assumes `$task->serviceOrder` is always non-null

**Mitigation:**
1. Search the codebase for all `$task->serviceOrder` usages and add null guards
2. Consider adding a helper method `$task->getParentOrder()` that checks `taskable_type` and returns the correct model
3. Document the BC break in the PRD

---

## ✅ PASS (ready for implementation)

### Issue 03 — Create Loan Order Backend

**Verification:**
- ✅ Deep module pattern — encapsulates validation, locking, state transitions in single `TransactionHandler::execute()` call
- ✅ [`lockForUpdate()`](../../docs/04-loan-extraction/03-create-loan-backend.md:14) — correct pessimistic locking prevents double-booking
- ✅ `StoreLoanOrderRequest` with explicit field whitelisting — no mass-assignment risk
- ✅ `EquipmentUnavailableException` for invalid states
- ✅ Auto-creates checkout Task — consistent with existing behavior
- ✅ Returns `LoanOrderResource` with 201 — RESTful
- ✅ Tests cover auth (401), permission (403), validation (422), success (201/200)

**No architectural concerns.** Ready for implementation.

---

### Issue 05 — Loan Frontend Page

**Verification:**
- ✅ Clean separation — new `resources/js/Features/LoanOrders/` folder
- ✅ Uses existing UI patterns: `DataManager`, `WorkspaceDrawer`, `AppLayout`
- ✅ Form schema matches backend `StoreLoanOrderRequest` whitelist
- ✅ Action buttons gated by permission + state (Cancel for PENDING, Return for CHECKED_OUT)
- ✅ Sidebar entry follows existing pattern

**No architectural concerns.** Ready for implementation (blocked on Issues 01-04).

---

### Issue 06 — Data Migration

**Verification:**
- ✅ Idempotent design — checks `migrated_to_loan_id` before processing
- ✅ Summary reporting for audit trail
- ✅ Handles edge cases: tasks, equipment pivot, soft-reference FK
- ✅ Rollback scenario documented

**No architectural concerns despite ⚠️ on timing.** The migration logic itself is correct.

---

### Issue 08 — Final Tests + Audit

**Verification:**
- ✅ Comprehensive coverage: service lifecycle, policy gates, API HTTP codes, migration
- ✅ Error scenarios: unavailable equipment, double cancel, state guards, concurrent lock
- ✅ Security audit: input whitelisting, ORM, authorization, CSRF, race conditions
- Follows existing test patterns (`ServiceOrderApiTest` precedent)

**No architectural concerns.** Ready for implementation (blocked on Issues 01-07).

---

## Cross-Item Observations

### 1. Strict ordering dependency (non-negotiable)

Issues must be implemented **in order**:
```
01 → 02 → 03 → 04 → 05 → 06 → 07 → 08
```

Any deviation will create an inconsistent system state:
| If you implement... | Without... | Problem |
|---|---|---|
| 03 (LoanOrderService) | 02 (Model) | Class not found |
| 04 (return/cancel) | 03 (create) | No loan orders to act on |
| 05 (frontend) | 03+04 (backend) | No API endpoints |
| 07 (cleanup) | 06 (migration) | Existing loan data orphaned |

### 2. Co-existence window risk (Issues 03–06 active simultaneously)

During the co-existence period, **both** `ServiceOrderService::create($isLoan=true)` and `LoanOrderService::create()` can create equipment loans. The old service's `lockForUpdate()` and availability guards still protect against double-booking, but:
- Equipment status transitions happen in two different code paths
- The `equipment_service_order` pivot table accumulates loan data that must be cleaned up later
- Two different task creation mechanisms run (old listener vs new service)

**Recommendation:** Add a `FeatureFlag` or `rate_limit` check in `ServiceOrderService::create()` that blocks new loan SO creation once the migration has begun. This ensures no new loan data enters the legacy path during migration.

### 3. Dead columns after cleanup

After Issue 07 (cleanup), these database artifacts remain:

| Column/Table | Location | Status |
|---|---|---|
| `tasks.service_order_id` | Existing column | Dead — always null for new records, populated for legacy |
| `service_orders.workflow_type` | Existing column | Dead — always `'regular'` for new records |
| `equipment_service_order` pivot | Existing table | Contains orphaned loan data from migrated records |
| `equipments.serviceOrders()` relationship | Equipment model | Still references pivot with legacy loan data |

These are not blockers but should be documented in the PRD as **future cleanup** items.

### 4. Equipment model accumulating relationships

After extraction, `Equipment` has these order-related relationship methods:
- `serviceOrders()` → BelongsToMany via `equipment_service_order` (legacy loan data + standard SOs)
- `loanOrders()` → BelongsToMany via `equipment_loan_order` (new loan data)

This is acceptable for now but creates a **naming inconsistency**: `serviceOrders()` returns both standard SOs and legacy loan data, while `loanOrders()` returns only new loans. Consider scoping `serviceOrders()` with a `where('workflow_type', 'regular')` filter once cleanup is complete.

---

## Recommended Remediation Action Items

| Priority | Action | Source |
|----------|--------|--------|
| 🔴 P0 | Resolve cancel-from-CHECKED_OUT inconsistency between Issue 04 and PRD | Issue 04 |
| 🔴 P0 | Keep `WorkflowType` enum — deprecate instead of delete | Issue 07 |
| 🟡 P1 | Add `RETURNED` case to `LoanOrderStatus` + add `complete()` to `LoanOrderService` | Issue 04 |
| 🟡 P1 | Add data backfill to migration 3 for existing tasks | Issue 01 |
| 🟡 P1 | Add co-existence guard in `ServiceOrderService::create()` to block new loans post-migration | Cross-item |
| 🟢 P2 | Align `reference`/`process` column naming across all docs | Issue 01 |
| 🟢 P2 | Add null guards to all `$task->serviceOrder` call sites | Issue 02 |
| 🔵 P3 | Document dead columns as future cleanup | Cross-item |

---

*Audit performed using `audit-arch` skill. Full analysis against Boundary Integrity, Dependency Direction, Volatility Isolation, Slice Cohesion, and Glossary Consistency criteria.*