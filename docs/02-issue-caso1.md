# Caso 1 — Granular RBAC + Permission Middleware + Context Switching

**Status:** @to-issue complete — @tdd ready  
**Parent PRD:** https://github.com/Parz1val6g/CMMS/issues/117  
**Date:** 2026-05-22

---

## Architectural Decisions (grill-me locked)

| # | Decision | Choice |
|---|----------|--------|
| D1 | Como o middleware sabe que permissao verificar? | Parametro explicito na rota: `permission:resource,action` |
| D2 | O que o middleware verifica? | `PermissionManager::hasPermission()` — preserva admin bypass e cache |
| D3 | Scope do middleware? | **Hibrido**: middleware faz fail-fast resource-level; policies fazem scoping instance-level |
| D4 | Formato do parametro? | Dois args separados por virgula: `permission:service_orders,view`. Validado contra enums (500 se invalido) |
| D5 | Que rotas recebem middleware? | Todas as rotas `auth:sanctum` declaram individualmente via `->middleware('permission:...')` |
| D6 | Remove `Gate::authorize()` redundantes? | Remove resource-level (`viewAny`/`create`). Mantem instance-level (`view`/`update`/`delete`/customs) |
| D7 | Resposta em nao autorizado? | `abort(403)` — JSON padrao do Laravel |
| D8 | Frontend `$page.props.can`? | Usa PermissionManager diretamente (desacopla de Gate/policies para a camada Inertia) |
| D9 | Localizacao do middleware? | `app/Core/Middleware/CheckPermission.php`, alias `'permission'` em `bootstrap/app.php` |
| D10 | Valores de PermissionAction nas rotas? | Usa os valores do enum. `view` cobre index + show. |
| D11 | Custom abilities? | Todas adicionadas ao PermissionAction enum (11 novas cases). Verificacao hibrida. |
| D12 | Restore/forceDelete? | Mantidos no enum/policies. Rotas deferred (nao neste sprint). |
| D13 | Export/import? | Usam permissao explicita `export`/`import` no middleware, separando semanticamente de `view`. |

---

## Issue Breakdown (6 issues)

### Dependency Graph

```
[1] Enum + Seeder Foundation
 ├── [2] CheckPermission Middleware + ServiceOrders
 │    └── [3] All Remaining Routes + Controller Cleanup
 ├── [4] Policy + FormRequest Cleanup
 ├── [5] Context Switching Backend + Inertia Can Refactor
 │    └── [6] RoleSwitcher UI + /select-role + Sidebar Gating
```

---

### Issue #1: Enum + Seeder Foundation
**GitHub:** https://github.com/Parz1val6g/CMMS/issues/111  
**Type:** AFK | **Blocked by:** None

#### Scope
- Expand `PermissionAction` enum from 9 to 20 cases
- Add `attendant` role to `RoleName` enum and `RoleSeeder`
- Add `ticket_manager` and `team_manager` constants to `RoleName` (already seeded, missing from enum)
- Update `RolePermissionSeeder` with new actions for existing roles + attendant

#### Files
- `app/Core/Enums/PermissionAction.php`
- `app/Core/Enums/RoleName.php`
- `database/seeders/RolePermissionSeeder.php`
- `database/seeders/RoleSeeder.php`

---

### Issue #2: CheckPermission Middleware + ServiceOrders Routes
**GitHub:** https://github.com/Parz1val6g/CMMS/issues/112  
**Type:** AFK | **Blocked by:** #111

#### Scope
- Create `app/Core/Middleware/CheckPermission.php`
- Register `permission` alias in `bootstrap/app.php`
- Apply middleware to ServiceOrders routes
- Remove `Gate::authorize('viewAny')` and `Gate::authorize('create')` from ServiceOrderController
- Write tests verifying 403/500/200/admin-bypass

#### Files
- `app/Core/Middleware/CheckPermission.php` (NEW)
- `bootstrap/app.php`
- `app/Features/ServiceOrders/Routes/api.php`
- `app/Features/ServiceOrders/Controllers/Api/ServiceOrderController.php`

---

### Issue #3: Permission Middleware — All Remaining Routes + Controller Cleanup
**GitHub:** https://github.com/Parz1val6g/CMMS/issues/113  
**Type:** AFK | **Blocked by:** #112

#### Scope
Mechanical application of `permission:resource,action` middleware to all route files, removing `Gate::authorize('viewAny'/'create')` from all controllers.

#### Route files (20)
`app/Features/{Feature}/Routes/api.php`:
- Tasks, LoanOrders, MiniTasks, WorkLogs, Tickets
- Clients, Locations, Sectors, Teams, Workers, Materials, Equipments, Entities, ServiceTypes
- Admin (users + roles), Export, Authentication (auth-only routes)

`routes/api/`:
- units, attachments

---

### Issue #4: Policy + FormRequest Cleanup
**GitHub:** https://github.com/Parz1val6g/CMMS/issues/114  
**Type:** AFK | **Blocked by:** #111

#### Scope
Migrate 6 policy custom ability methods to use `PermissionAction` enum values. Clean duplicate authorization from `RejectTaskRequest` and `ConvertTicketRequest`.

#### Files
- `app/Features/ServiceOrders/Policies/ServiceOrderPolicy.php`
- `app/Features/Tasks/Policies/TaskPolicy.php`
- `app/Features/LoanOrders/Policies/LoanOrderPolicy.php`
- `app/Features/MiniTasks/Policies/MiniTaskPolicy.php`
- `app/Features/WorkLogs/Policies/WorkLogPolicy.php`
- `app/Features/Tickets/Policies/TicketPolicy.php`
- `app/Features/Tasks/Requests/RejectTaskRequest.php`
- `app/Features/Tickets/Requests/ConvertTicketRequest.php`

---

### Issue #5: Context Switching Backend + Inertia Can Refactor
**GitHub:** https://github.com/Parz1val6g/CMMS/issues/115  
**Type:** AFK | **Blocked by:** #111

#### Scope
- `POST /api/auth/switch-role` — stores `active_role` in session
- Refactor `HandleInertiaRequests::share()` to use `PermissionManager::userPermissions()`
- Expand `CAN_CHECKS` with custom ability keys and missing sidebar resource keys

#### Files
- `app/Features/Authentication/Routes/api.php`
- `app/Features/Authentication/Controllers/AuthController.php`
- `app/Http/Middleware/HandleInertiaRequests.php`

---

### Issue #6: RoleSwitcher UI + /select-role Page + Sidebar Gating
**GitHub:** https://github.com/Parz1val6g/CMMS/issues/116  
**Type:** AFK | **Blocked by:** #115

#### Scope
- `/select-role` Inertia page (multi-role users)
- `RoleSwitcher` header dropdown component
- Sidebar navigation gating via `can` prop
- Auto-redirect single-role users past select-role

#### Files
- `resources/js/Features/Authentication/Pages/SelectRole.jsx` (NEW)
- `resources/js/Components/RoleSwitcher.jsx` (NEW)
- `resources/js/Components/Sidebar.jsx`

---

## Test Matrix

### M1 (Issue #1)
- `PermissionAction::cases()` returns 20 cases
- Seeder creates all expected role_permissions
- `attendant` has `service_orders:view` + `service_orders:create` + `profile:view`
- `admin` has all 20 actions across all resources

### M2 (Issues #2 + #3)
- Middleware returns 403 when user lacks permission
- Middleware returns 500 when resource/action param is invalid
- Admin bypass works (admin never gets 403)
- Authorized user gets 200

### M3 (Issue #4)
- `ServiceOrderPolicy::activate()` requires `service_orders:activate`
- `TaskPolicy::complete()` requires `tasks:complete`
- FormRequests no longer duplicate authorization checks

### M4 (Issues #5 + #6)
- `can` prop includes `activateServiceOrder` when active role is `manager`
- `can` prop excludes `activateServiceOrder` when active role is `worker`
- Sidebar renders only items whose `can` is truthy
- `POST /auth/switch-role` returns 200 with valid role, 403 with unassigned role

---

## M5 (Deferred): Restore/ForceDelete Routes

Rotas a criar no futuro, usando `->withTrashed()`:

```php
Route::post('/service-orders/{service_order}/restore', [ServiceOrderController::class, 'restore'])
    ->withTrashed()
    ->middleware('permission:service_orders,restore');

Route::delete('/service-orders/{service_order}/force-delete', [ServiceOrderController::class, 'forceDelete'])
    ->withTrashed()
    ->middleware('permission:service_orders,force_delete');
```

Enum values `RESTORE` and `FORCE_DELETE` ja existem no PermissionAction (Issue #1). Rotas serao criadas em sprint futuro.

---

## Execution Order

```
1. Issue #111 → Enum + Seeder Foundation
2. Issue #112 → CheckPermission Middleware + ServiceOrders  (after #111)
3. Issue #113 → All Remaining Routes                        (after #112)
4. Issue #114 → Policy + FormRequest Cleanup                (after #111, parallel with #112/#113)
5. Issue #115 → Context Switching + Inertia Refactor        (after #111, parallel with #112-#114)
6. Issue #116 → RoleSwitcher + Sidebar Gating               (after #115)
```

Parallel tracks: [#112→#113], [#114], and [#115→#116] can all start once #111 is complete.
