# Audit Report — Team Responsible Feature

**Audited:** [`docs/03-team-responsibles/03-issues.md`](03-issues.md) (ISSUE-001 through ISSUE-004)
**Date:** 2026-05-13
**Scope:** Architectural review against Boundary Integrity, Dependency Direction, Volatility Isolation, Slice Cohesion, and Glossary & Consistency.

---

## ✅ PASS (ready for implementation)

### ISSUE-001 — Foundation (Schema + Role + Model)

| Dimension | Verdict | Notes |
|-----------|---------|-------|
| **Boundary Integrity** | ✅ PASS | All changes within Teams bounded context. `responsible_id` FK→`users` follows same pattern as `sector_id`→`sectors`. `team_manager` role name follows convention (`sector_manager`, `equipment_manager`, `supervisor`). |
| **Dependency Direction** | ✅ PASS | `isTeamManager()` added to [`BasePolicy`](app/Core/Policies/BasePolicy.php:88) follows existing `isSectorManager()` pattern. `TeamPolicy` extends `BasePolicy`. Dependencies flow Core ← Feature, not reverse. |
| **Volatility Isolation** | ✅ PASS | Schema changes are migration-isolated. Role/permission changes are seeder-isolated. Model relation is a simple `belongsTo`. No infrastructure leakage. |
| **Slice Cohesion** | ✅ PASS | Single end-to-end slice: migration → model → role → seeder → permission → policy helper. One implementation cycle (~1h). |
| **Glossary & Consistency** | ✅ PASS | `responsible_id` consistent with sector's `head_id` concept. `team_manager` consistent with existing role naming. `cascadeOnDelete` consistent with [`sector_id` FK on teams](database/migrations/2024_01_01_000016_create_teams_table.php:12). |

### ISSUE-002 — Create Team with Responsible (Full CRUD Path)

| Dimension | Verdict | Notes |
|-----------|---------|-------|
| **Boundary Integrity** | ✅ PASS | Form schema, service logic, API resource, and controllers all within `app/Features/Teams/`. Cross-context calls (User role assignment, Worker creation) are mediated through the Service layer. |
| **Dependency Direction** | ✅ PASS | [`TeamService::create()`](app/Features/Teams/Services/TeamService.php:14) depends on [`Team`](app/Features/Teams/Models/Team.php) model (same context). Role/Worker creation is delegated — no direct DB queries. |
| **Volatility Isolation** | ✅ PASS | All mutations wrapped in [`TransactionHandler`](app/Core/Services/TransactionHandler.php). Business logic in Service, not Controller. |
| **Slice Cohesion** | ✅ PASS | End-to-end: form → validation → service → resource → controller. One implementation cycle (~2h). |
| **Glossary & Consistency** | ✅ PASS | `whenLoaded('responsible')` pattern consistent with existing `whenLoaded('sector')` in [`TeamResource`](app/Features/Teams/Resources/TeamResource.php:16). SearchableSelect consistent with other form schemas. |

### ISSUE-003 — Update Team + Policy Scoping

| Dimension | Verdict | Notes |
|-----------|---------|-------|
| **Boundary Integrity** | ✅ PASS | Update logic stays within Teams context. Policy scoping is a natural extension of existing pattern. |
| **Dependency Direction** | ✅ PASS | [`TeamPolicy`](app/Features/Teams/Policies/TeamPolicy.php) extends [`BasePolicy`](app/Core/Policies/BasePolicy.php) — correct direction. Policy checks use existing `isTeamManager()` helper. |
| **Volatility Isolation** | ✅ PASS | Update logic wrapped in [`TransactionHandler`](app/Features/Teams/Services/TeamService.php:24). Worker creation/role assignment encapsulated in Service. |
| **Slice Cohesion** | ✅ PASS | Single vertical: update logic → policy scoping → seeders. One implementation cycle (~1.5h). |
| **Glossary & Consistency** | ✅ PASS | Seeder updates consistent with existing seeder patterns. Policy scoping mirrors `sector_manager` + `supervisor` patterns in [`view()`](app/Features/Teams/Policies/TeamPolicy.php:16). |

### ISSUE-004 — Tests (Integração)

| Dimension | Verdict | Notes |
|-----------|---------|-------|
| **Boundary Integrity** | ✅ PASS | Tests scoped to Teams feature. Prior art references (`ServiceOrderApiTest`, `ServiceOrderPoliciesTest`, `CascadeCompletionTest`) are well-chosen. |
| **Slice Cohesion** | ✅ PASS | Each test file covers one aspect: API (HTTP), Policies (authorization), Service (business logic). No overlap. One implementation cycle (~1.5h). |
| **Glossary & Consistency** | ✅ PASS | Test naming follows existing convention. Coverage maps 1:1 to acceptance criteria. |

---

## ⚠️ WARN (minor concerns — document and proceed)

### WARN-001 — `cascadeOnDelete` on `responsible_id` (ISSUE-001)

**Concern:** Deleting a User who is `responsible_id` of a team will cascade-delete the entire team (and all its workers, mini-tasks, etc.). This is consistent with the existing `sector_id` cascade pattern, but is a high-risk operation.

**Mitigation:** Ensure the admin UI displays a confirmation warning about cascade effects when deleting a User who is a team responsible. Alternatively, consider `restrictOnDelete` or a nullable `responsible_id` with a pre-delete reassignment flow. This is acceptable for now as it follows established codebase convention.

---

### WARN-002 — `responsible_id` SelectInput performance (ISSUE-002)

**Concern:** The form schema options for `responsible_id` need to filter users by `worker` role AND exclude users already assigned as Workers. If the user base grows large, loading all options upfront will be expensive.

**Mitigation:** Use [`SearchableSelect`] with server-side search (AJAX endpoint) rather than preloading all user options. The existing [`SelectInput`](app/Features/Teams/TeamFormSchema.php:21) pattern loads all options upfront, which works for sectors (small dataset) but may not scale for users.

---

### WARN-003 — `team_manager` scope missing from `view()` method (ISSUE-003)

**Concern:** The [`TeamPolicy::view()`](app/Features/Teams/Policies/TeamPolicy.php:16) method has scoped checks for `sector_manager` and `supervisor`, but no scoped check for `team_manager` is proposed. Since `team_manager` has `view` permission on `TEAMS`, they can currently view ALL teams, not just their own. This contradicts PRD user story #3 ("As a team_manager, I want to view my team's details").

**Mitigation:** Add a `team_manager` scope to [`view()`](app/Features/Teams/Policies/TeamPolicy.php:16) mirroring the pattern used for `sector_manager` and the proposed `update()` scope:

```php
if ($this->isTeamManager($user)) {
    return $team->responsible_id === $user->id;
}
```

Also ensure the [`update()`](app/Features/Teams/Policies/TeamPolicy.php:42) method has correct priority ordering: `admin` (via `hasPermission`) → `sector_manager` → `team_manager` → `supervisor` → deny.

---

## ❌ BLOCKED (none)

No items are blocked.

---

## Cross-Item Observations

| Observation | Details |
|-------------|---------|
| **Dependency chain clean** | ISSUE-001→002→003→004 forms a linear dependency chain with no cross-cutting concerns. Milestones M1→M4 map cleanly. |
| **No feature coupling** | The git diff shows parallel work on Equipment extensions, cost_per_hour, and attachment refactoring — none of these touch the Teams context. Zero coupling risk. |
| **Worker.user_id UNIQUE** | The proposed logic correctly leverages `Worker.user_id` UNIQUE constraint as a domain invariant. Service-level validation (ISSUE-002 Task 2.1) provides a clean error message before the DB constraint fires. |
| **responsible_id NOT NULL** | The PRD correctly notes that removing a responsible means reassigning, since the column is NOT NULL. This is a deliberate domain decision with no hidden edge cases. |
| **Transactional consistency** | All mutations go through `TransactionHandler`, ensuring atomicity across Team creation + Worker creation + role assignment in ISSUE-002/003. |

---

## Gate Decision

**✅ PROCEED — All PASS + 3 WARN**

No architectural violations. The 3 WARN items are minor and have documented mitigations.

| WARN | Action Required |
|------|----------------|
| WARN-001 | Add cascade-delete warning to admin UX (implementation note) |
| WARN-002 | Use SearchableSelect with server-side search (implementation note) |
| WARN-003 | Add `team_manager` scope to `view()` + ensure priority ordering in `update()` (fix during ISSUE-003 implementation) |

The proposal is architecturally sound, follows existing patterns, respects bounded contexts, and is ready for implementation.
