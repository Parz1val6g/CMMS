# Deprecate `WorkflowType` Enum

**Origin:** Audit blocker — [`docs/04-loan-extraction/audit-results.md`](../04-loan-extraction/audit-results.md:44)  
**Decision date:** 2026-05-13  
**Priority:** 🔴 P0 (must be done before 04-loan-extraction Issue 07)

---

## Problem

[`07-cleanup-serviceorders.md`](../04-loan-extraction/07-cleanup-serviceorders.md:42) originally proposed **deleting** [`WorkflowType.php`](../../app/Core/Enums/WorkflowType.php). However, legacy `service_orders` records still have `workflow_type = 'loan'` in the database. Deleting the enum causes a runtime crash:

```
Class "App\Core\Enums\WorkflowType" not found
```

This crash occurs whenever Laravel tries to cast the `'loan'` value via the `$casts` property on [`ServiceOrder`](../../app/Features/ServiceOrders/Models/ServiceOrder.php:41).

---

## Decision

Keep the enum file. Do NOT delete it. Apply these changes:

### 1. Annotate `WorkflowType.php` as `@deprecated`

```php
/**
 * @deprecated Since loan extraction (v2.0). Kept for legacy data BC.
 *             Will be removed in a future version after data migration.
 */
enum WorkflowType: string
{
    case STANDARD = 'regular';
    case LOAN = 'loan';
}
```

### 2. Remove `$casts` from `ServiceOrder` model

In [`ServiceOrder.php`](../../app/Features/ServiceOrders/Models/ServiceOrder.php), remove the cast entry:

```php
// Remove this line:
'workflow_type' => WorkflowType::class,
```

Let the raw string (`'regular'` | `'loan'`) pass through as a plain string attribute instead.

### 3. Update `ServiceOrderResource` (if needed)

Check if [`ServiceOrderResource`](../../app/Features/ServiceOrders/Resources/ServiceOrderResource.php) serializes `workflow_type` as an enum label. If so, keep it as a plain string:

```php
'workflow_type' => $this->workflow_type, // plain string, no cast
```

### 4. Plan future cleanup

After data verification confirms no legacy loan records remain, a future migration should:

- Drop column `workflow_type` from `service_orders` table
- Delete `WorkflowType.php` entirely

This is **not** part of this issue — document as future tech debt.

---

## Files to Modify

| File | Change |
|------|--------|
| [`app/Core/Enums/WorkflowType.php`](../../app/Core/Enums/WorkflowType.php) | Add `@deprecated` docblock, keep both cases |
| [`app/Features/ServiceOrders/Models/ServiceOrder.php`](../../app/Features/ServiceOrders/Models/ServiceOrder.php) | Remove `'workflow_type' => WorkflowType::class` from `$casts` |
| [`app/Features/ServiceOrders/Resources/ServiceOrderResource.php`](../../app/Features/ServiceOrders/Resources/ServiceOrderResource.php) | Verify `workflow_type` serializes as plain string |

## Acceptance Criteria

- [ ] `WorkflowType` enum file is NOT deleted
- [ ] `WorkflowType` enum has `@deprecated` annotation
- [ ] `ServiceOrder` model no longer casts `workflow_type` to `WorkflowType`
- [ ] API endpoints return `workflow_type` as plain string
- [ ] No runtime crash when reading legacy `service_orders` with `workflow_type = 'loan'`
