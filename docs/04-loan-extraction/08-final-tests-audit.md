# Issue: Final Tests + Audit
**GitHub:** [#35](https://github.com/Parz1val6g/CMMS/issues/35)

**Parent:** [`04-prd.md`](04-prd.md)

## What to Build

Comprehensive test suite for the LoanOrders module plus a final security audit. Following the codebase's existing test patterns (ServiceOrderApiTest, ServiceOrderPoliciesTest, cascade tests).

**`LoanOrderServiceTest`** at `tests/Feature/LoanOrders/LoanOrderServiceTest.php`:
- Full lifecycle: create (PENDING) → initiateReturn (creates return task) → cancel (CANCELLED + equipment released)
- Error scenarios:
  - Equipment unavailable (already IN_USE)
  - Double cancel (idempotent guard)
  - Cancel of CHECKED_OUT (should work)
  - Return on PENDING (should fail)
  - Duplicate return task (should fail)
  - Checkout task not completed before return (should fail)
  - Concurrent pessimistic lock (two simultaneous creates for same equipment)

**`LoanOrderPolicyTest`** at `tests/Feature/LoanOrders/LoanOrderPolicyTest.php`:
- Gates by role: admin full access, manager create/initiateReturn/cancel, worker 403 for all
- Gates by state: cancel only if not terminal, initiateReturn only if CHECKED_OUT
- Uses existing User factory and role seeding patterns

**`LoanOrderApiTest`** at `tests/Feature/LoanOrders/LoanOrderApiTest.php`:
- CRUD endpoints: POST 201, GET 200, POST return 200, POST cancel 200, DELETE 200
- Auth: 401 for unauthenticated, 403 for unauthorized roles
- Validation: 422 for missing/invalid fields
- Soft-delete: 200 then GET returns null, 404 for deleted show
- Soft-delete guard: cannot delete CHECKED_OUT loan (only PENDING/CANCELLED)

**Migration test**:
- Seeds loan SOs with tasks
- Runs `loan-orders:import-existing`
- Verifies correct LoanOrder creation, task reassignment
- Runs rollback, verifies original state

**Frontend test** (if Testing Library is set up):
- Renders `/loans` page
- Create form with validation
- Drawer with tabs rendering

**Security audit** (using [`@cyber-sec`](../path-to-cyber-sec-skill)):
- Input whitelisting: all request validation uses explicit `StoreLoanOrderRequest` rules
- SQL injection: ORM used throughout, no raw queries
- Authorization: every endpoint gated by `LoanOrderPolicy`
- CSRF: API routes use Sanctum, web routes use CSRF token
- Equipment guard: `lockForUpdate()` prevents race conditions
- Data exposure: `LoanOrderResource` only exposes whitelisted fields

## Acceptance Criteria

- [ ] `LoanOrderServiceTest` passes: 10+ test methods covering lifecycle + error scenarios
- [ ] `LoanOrderPolicyTest` passes: role-based and state-based gates verified
- [ ] `LoanOrderApiTest` passes: HTTP status codes for success and error cases
- [ ] Migration test passes: import + rollback idempotent
- [ ] Frontend test passes (if applicable)
- [ ] Security audit checklist signed off: no SQLi, no IDOR, no race conditions, proper auth

## Blocked by

- [`07-cleanup-serviceorders`](./07-cleanup-serviceorders.md)
