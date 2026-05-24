# PRD: Granular RBAC + Context Switching — Case 1 (Citizen Problem Report)

## Problem Statement

The current system has three authorization problems that prevent the correct implementation of Case 1 (Citizen Problem Report):

1. **Undifferentiated roles**: The `manager` is simultaneously "attendant" (creates SO) and "manager" (activates/completes SO) — a Separation of Duties (SoD) violation. In the real world, the person who answers the phone and registers the problem is not necessarily the person who manages its execution.

2. **Permissions without domain granularity**: Critical state transitions (activate, complete) lack dedicated actions in the `PermissionAction` enum. They are handled as generic `UPDATE` or ad-hoc ownership checks. Actions such as assigning workers, materials, and equipment to Mini-Tasks also lack their own permissions.

3. **Frontend without an authorization contract**: Drawers use `authUser?.roles?.some()` — but `roles` never reaches the frontend via Inertia (dead code). The `can` flat map covers only 26 generic permissions and does not distinguish active role context. Users with multiple roles see a UI that improperly merges all permissions.

## Solution

Implement three architectural pillars:

1. **Separation of Attendant/Manager roles** — new `attendant` role with exclusive SO creation permission. The `manager` retains activation and completion. The creation form allows selecting the `manager_id` (explicit delegation).

2. **PermissionAction expansion** — add granular actions (`ACTIVATE`, `COMPLETE`, `ASSIGN_WORKERS`, `ASSIGN_MATERIALS`, `ASSIGN_EQUIPMENT`) to the enum. Policies shall require dual verification: granular permission + ownership (`isManagerScoped()`).

3. **Context Switching via Active Session Role** — users with multiple roles select an active context after login (`/select-role` screen). The Inertia middleware injects `can` based exclusively on that role. The Sidebar filters exhaustively by `can`. Drawers use global `can` + local ownership (Model B). Role switch available via dropdown in the header.

## User Stories

### Occurrence Registration

1. As an **atendente**, I want to create a new Service Order from a citizen report, so that the problem is formally registered in the system.
2. As an **atendente**, I want to select the **manager** responsible for the service order during creation, so that the order is delegated to the correct person.
3. As an **atendente**, I want to see only the "Service Orders" section and creation form in my sidebar, so that I am not distracted by administrative menus irrelevant to my role.

### Activation and Planning

4. As a **manager**, I want to activate a pending service order assigned to me, so that tasks are automatically created for each sector.
5. As a **manager**, I want to see only service orders where I am the `manager_id` in my workspace, so that I focus on my responsibilities.
6. As a **manager**, I want the "Activate" button to appear only on service orders I own, so that I don't get 403 errors on orders belonging to other managers.

### Mini-Task Breakdown

7. As a **task_manager**, I want to divide my task into Mini-Tasks, so that I can assign specific work units to workers and teams.
8. As a **task_manager**, I want to assign workers and/or teams to each Mini-Task, so that responsibilities are clear.
9. As a **task_manager**, I want to assign required materials with quantities to each Mini-Task, so that workers know what resources to use.
10. As a **task_manager**, I want to assign required equipment to each Mini-Task, so that equipment availability is planned.

### Execution

11. As a **worker**, I want to create Work Logs on Mini-Tasks assigned to me, so that I record my execution progress.
12. As a **worker**, I want to register materials and equipment actually used in each Work Log, so that consumption is tracked.
13. As a **worker**, I want to close a Work Log by setting an end timestamp, so that the Mini-Task cascade checks completion.

### Completion and Approval

14. As a **task_manager**, I want to review and mark a task as completed when all its Mini-Tasks are done, so that it moves to the Service Order completion queue.
15. As a **manager**, I want to review and mark a service order as completed when all its tasks are done, so that the citizen report is formally closed.

### Context Switching

16. As a **user with multiple roles**, I want to select which role context to operate in after login, so that I see only the permissions and navigation relevant to that role.
17. As a **user with multiple roles**, I want to switch my active role context at any time via a dropdown, so that I can perform different duties without logging out.
18. As a **user**, I want the sidebar to reflect only the navigation items my active role has permission to access, so that the interface is clean and role-appropriate.

### Security

19. As a **system**, I want all status transitions (activate, complete) to require both the granular permission AND ownership verification, so that even if the frontend is bypassed, the backend enforces correct authorization.

## Implementation Decisions

### Decision 1: Split "Atendente" and "Gestor" into separate roles

A new `attendant` role is created. It has only `service_orders:create` permission. The `manager` role retains `service_orders:activate` and `service_orders:complete` (via dual-check policies), plus existing VIEW/UPDATE permissions. The `StoreServiceOrderRequest` already has a `manager_id` field — the attendant selects the responsible manager during creation. This implements Separation of Duties without changing the existing data model.

### Decision 2: Expand PermissionAction enum

Five new actions are added to the `PermissionAction` enum:

| New Action | Resource | Used By | Purpose |
|---|---|---|---|
| `ACTIVATE` | `service_orders` | manager, admin | Activate a pending OS (creates Tasks) |
| `COMPLETE` | `service_orders` | manager, admin | Mark OS as completed after all tasks done |
| `COMPLETE` | `tasks` | task_manager, admin | Review and mark task as completed |
| `ASSIGN_WORKERS` | `mini_tasks` | task_manager, admin | Assign workers/teams to a Mini-Task |
| `ASSIGN_MATERIALS` | `mini_tasks` | task_manager, admin | Assign materials with quantities |
| `ASSIGN_EQUIPMENT` | `mini_tasks` | task_manager, admin | Assign equipment to a Mini-Task |

These are stored as rows in `role_permissions` alongside existing VIEW/CREATE/UPDATE entries. The `RolePermissionSeeder` is updated accordingly.

### Decision 3: Dual-check authorization in Policies (Permission + Ownership)

Status transition policies (activate, complete) require BOTH conditions:

1. User has the granular permission (e.g., `service_orders:activate`) — checked via `hasPermission()`
2. User is the owner of the resource (`isManagerScoped()` or direct `manager_id` comparison)

Admin bypass applies: if `isAdmin()` returns true, both conditions pass. This means the `can` prop controls button visibility, and the backend policy is the Single Source of Truth for enforcement.

**Before (current):**
- `ServiceOrderPolicy::activate()`: `$user->id === $serviceOrder->manager_id` (pure ownership, no permission check)
- `TaskPolicy::complete()`: `isAdmin() || isOwner()` (no permission check for non-admin owners)

**After (target):**
- `ServiceOrderPolicy::activate()`: `isAdmin()` OR (`hasPermission(ACTIVATE)` AND `isOwner()`)
- `TaskPolicy::complete()`: `isAdmin()` OR (`hasPermission(COMPLETE)` AND `isOwner()`)

### Decision 4: Frontend rendering — Model B (can + ownership)

Drawer components use a two-step check for ownership-scoped actions:

```js
// Pattern for every ownership-scoped button in drawers:
const canActivate = can?.activateServiceOrder && authUser.id === order.manager_id;
const canComplete = can?.completeServiceOrder && authUser.id === order.manager_id;
```

- `can.activateServiceOrder` comes from Inertia shared props (global permission — "does this role allow activating OS?")
- `authUser.id === order.manager_id` is a local data comparison (ownership — "is this user the manager of THIS OS?")
- No `roles` array is used anywhere in the frontend — the `can` prop is the exclusive contract

### Decision 5: Exhaustive sidebar gating via can

Every sidebar navigation item receives a `can` key. The Sidebar component filters items strictly by the `can` prop from the active session context. Items without a `can` key are considered always-visible only for the `dev` flag (feature toggles).

The `getSections()` data structure gains `can` keys for all currently-ungated items: `viewClients`, `viewEntities`, `viewLocations`, `viewEquipments`, `viewLoanOrders`, `viewSectors`, `viewTeams`, `viewWorkers`, `viewServiceTypes`, `viewEquipmentTypes`, `viewCountingTypes`, `viewMaterials`, `viewSettings`, `viewUsers` (admin).

Bottom items (Notifications, Settings, Admin) also receive `can` keys.

### Decision 6: Full-page role selection via Inertia

**Flow:**

1. User logs in successfully via `/api/auth/login` or web login
2. Backend checks if user has more than one role (after WebAccessMiddleware filtering)
3. If yes: redirect to `/select-role` (dedicated Inertia page)
4. The page renders cards/buttons, one per role, showing role name and description
5. User selects a role → `POST /api/auth/switch-role` with `{ role: "manager" }`
6. Backend stores `active_role` in session, regenerates the `can` prop map based on that role's permissions
7. Redirect to `/dashboard` with the correct `can` context

**Role switching mid-session:**
- A dropdown in the header/sidebar lists the user's available roles
- Selecting a different role triggers `POST /api/auth/switch-role` + `Inertia.reload()`
- The full-page reload ensures the `can` prop and all Inertia page props are re-computed with the new context
- Unsaved form state is lost on switch (acceptable — context switch implies intent to abandon current workflow)

**Users with a single role:**
- Skip the selection screen entirely; the role is auto-activated
- The role switch dropdown is hidden

### Decision 7: can prop computation per active session

`HandleInertiaRequests::share()` is refactored:

1. Reads `active_role` from the session
2. Loads the role's permissions from `role_permissions` (cached via `PermissionManager`)
3. Computes `can` keys based ONLY on the active role's permissions
4. The `CAN_CHECKS` constant is expanded to include new keys: `activateServiceOrder`, `completeServiceOrder`, `completeTask`, `assignWorkers`, `assignMaterials`, `assignEquipment`, and the newly-gated sidebar items

The computation uses the same `$user->can($ability, $model)` mechanism as today — the difference is that `$user->can()` now respects the active session role context.

### Decision 8: WebAccessMiddleware update

The middleware currently blocks `worker` and `client` from web access. The new `attendant` role is allowed (not in the blocked list). The middleware also gains awareness of the active session role to assist with context initialization on login. No routing changes — the `/select-role` page is a web route accessible only to authenticated users with >1 role.

### Decision 9: Sidebar data refactoring

The `getSections()` function in `sidebar.js` is updated to include `can` keys on every navigation item. The existing pattern of `{ label, icon, href, dev, can }` is extended to cover all items.

The current `dev` flag controls feature toggles (hidden until ready). The `can` flag controls permission-based visibility. An item is rendered only if `(!item.dev || isDev) && (!item.can || can[item.can])`. Items with neither `dev` nor `can` are treated as always-visible (currently only the Dashboard link).

### Decision 10: RolePermission seeder update

**New role: `attendant`**

| Resource | Actions |
|---|---|
| `service_orders` | VIEW, CREATE |
| `profile` | VIEW |

**Existing roles — new actions added:**

| Role | Additional actions |
|---|---|
| `manager` | `service_orders:ACTIVATE`, `service_orders:COMPLETE`, `mini_tasks:ASSIGN_WORKERS`, `mini_tasks:ASSIGN_MATERIALS`, `mini_tasks:ASSIGN_EQUIPMENT` |
| `supervisor` | `service_orders:COMPLETE`, `tasks:COMPLETE` |
| `task_manager` | `tasks:COMPLETE`, `mini_tasks:ASSIGN_WORKERS`, `mini_tasks:ASSIGN_MATERIALS`, `mini_tasks:ASSIGN_EQUIPMENT` |
| `mini_task_manager` | `mini_tasks:ASSIGN_WORKERS`, `mini_tasks:ASSIGN_MATERIALS`, `mini_tasks:ASSIGN_EQUIPMENT` |
| `admin` | All new actions on all resources (automatic via existing admin-all pattern) |

## Testing Decisions

### What makes a good test

Tests verify external behavior (API responses, HTTP status codes, Inertia prop shapes, page access) — not implementation details (internal method calls, cache keys).

### Backend tests (PHPUnit)

**PermissionManager tests:**
- `hasPermission()` returns true/false correctly for each new action
- Admin bypass works for all new actions
- Permission cache invalidation works after role switch

**Policy tests:**
- `ServiceOrderPolicy::activate()`: non-owner with `ACTIVATE` permission → false; owner without `ACTIVATE` permission → false; owner with `ACTIVATE` permission → true; admin → true
- `ServiceOrderPolicy::complete()`: same dual-check pattern
- `TaskPolicy::complete()`: same dual-check pattern

**API endpoint tests:**
- `POST /api/service-orders/{id}/activate`: 403 for non-owner, 403 for owner without permission, 200 for owner with permission
- `POST /api/service-orders/{id}/complete`: same pattern
- `POST /api/auth/switch-role`: 200 with valid role, 403 with role not assigned to user

**Middleware tests:**
- `HandleInertiaRequests`: `can` prop includes new keys when active role has them; excludes them when role lacks them
- `WebAccessMiddleware`: `attendant` role passes through; `worker`/`client` blocked

**Seeder tests:**
- `RolePermissionSeeder`: `attendant` role exists with correct permissions; existing roles have new actions

**Prior art for backend tests:** Existing tests in `tests/Feature/` use `$this->actingAs($user, 'sanctum')` pattern, SQLite in-memory, and `composer test` runner.

### Frontend tests (Vitest + jsdom)

- Sidebar component: renders only items whose `can` key is truthy in the provided prop
- ServiceOrderDrawer: "Activate" button visible only when `can.activateServiceOrder && isOwner`; hidden otherwise
- SelectRole page: renders correct number of role cards based on prop data
- RoleSwitcher component: renders correct number of options, triggers POST on selection

**Prior art for frontend tests:** Vitest configured with jsdom, globals enabled. Run via `npx vitest --run`.

## Out of Scope

- **Casos 2, 3, 4**: This PRD covers only Caso 1 (Reporte de Problema pelo Cidadão). The RBAC infrastructure built here is reusable for future cases, but their specific permissions and UI are not addressed.
- **Mobile SPA**: The SPA uses Sanctum tokens and `/api/auth/me` for role data — a different authorization path. Context switching for mobile is deferred.
- **Permission management UI**: The admin interface for assigning permissions to roles already exists at `/admin/roles`. The new actions appear automatically when added to the enum — no UI changes needed.
- **Notification delivery mechanism**: The alert system for OS start dates exists. This PRD only ensures the `manager` can view notifications via `can.viewNotifications`.
- **Audit logging**: Tracking who activated/completed what is valuable but not in scope for this authorization PRD.

## Further Notes

### Why not pass `roles` to frontend

The `roles` array on `auth.user` was considered but rejected because:
- It encourages frontend `roles.some()` checks that duplicate backend policies
- It leaks role names into JS bundles (minor security concern)
- The `can` flat boolean map is simpler, faster to evaluate, and mirrors Laravel's own `@can` Blade directive pattern
- Context switching makes roles dynamic — the `can` map changes atomically with the session role

### Migration strategy

No database migrations are needed:
- `role_permissions` uses string columns for `resource` and `action` — new enum values are just new strings
- The `attendant` role is a new row in the `roles` table (via seeder)
- The `active_role` session key is ephemeral (stored in Laravel session, not DB)

All changes are:
1. Enum expansion (PHP code)
2. Policy refactoring (PHP code)
3. Seeder updates (PHP code)
4. Middleware refactoring (PHP code)
5. New web route + controller for `/select-role`
6. New API endpoint `POST /api/auth/switch-role`
7. Frontend component updates (Sidebar, drawers)
8. New frontend page (SelectRole)
9. New frontend component (RoleSwitcher dropdown)

### Completion cascade

The existing cascade (WorkLogs done → MiniTask done → Task done → ServiceOrder done) is NOT modified. The new `COMPLETE` action only gates the manual approval step at Task and ServiceOrder level — the automatic cascade based on child completion is unchanged.
