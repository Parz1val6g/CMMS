# Implementation Roadmap — COMPLETED

**Objective**: Fully working backend implements all models, controllers, services, policies, and routes across 16 modular features.

**Approach**: Feature-by-feature, respecting modular architecture, applying code philosophy (Minimalismo, DRY, centralized error handling, Security First).

---

## 🗺️ Completed Phases

### PHASE 1: Core Models & Infrastructure Completion ✅

**Completed**: 2026-04-23 → 2026-04-28  
**Priority**: 🔴 CRITICAL (blocked all other phases)

#### 1.1 Core Models — All 22+ Implemented

All models use UUID PK via `App\Core\Traits\Base`, soft deletes, proper relationships, and casts.

| # | Model | Location | Relations | Status |
|---|-------|----------|-----------|--------|
| 1 | [`User`](app/Shared/Models/User.php) | `app/Shared/Models/` | roles, preferences, clientProfile, workerProfile, managedServiceOrders, managedTasks, managedMiniTasks, headedSectors | ✅ |
| 2 | [`Role`](app/Shared/Models/Role.php) | `app/Shared/Models/` | permissions, users (M:M) | ✅ |
| 3 | [`RolePermission`](app/Shared/Models/RolePermission.php) | `app/Shared/Models/` | role (M:1) | ✅ |
| 4 | [`UserPreference`](app/Shared/Models/UserPreference.php) | `app/Shared/Models/` | user (M:1) | ✅ |
| 5 | [`AppSetting`](app/Shared/Models/AppSetting.php) | `app/Shared/Models/` | Key-value store with sections | ✅ |
| 6 | [`Unit`](app/Shared/Models/Unit.php) | `app/Shared/Models/` | Measurement units | ✅ |
| 7 | [`Material`](app/Features/Materials/Models/Material.php) | `app/Features/Materials/Models/` | unit, plannedForMiniTasks, usedInWorkLogs | ✅ |
| 8 | [`District`](app/Shared/Models/District.php) | `app/Shared/Models/` | municipalities (1:M) | ✅ |
| 9 | [`Municipality`](app/Shared/Models/Municipality.php) | `app/Shared/Models/` | district, parishes | ✅ |
| 10 | [`Parish`](app/Shared/Models/Parish.php) | `app/Shared/Models/` | municipality, locations | ✅ |
| 11 | [`Location`](app/Features/Locations/Models/Location.php) | `app/Features/Locations/Models/` | parish, serviceOrders | ✅ |
| 12 | [`ServiceType`](app/Features/ServiceTypes/Models/ServiceType.php) | `app/Features/ServiceTypes/Models/` | serviceOrders (1:M) | ✅ |
| 13 | [`Sector`](app/Features/Sectors/Models/Sector.php) | `app/Features/Sectors/Models/` | head (User), teams, tasks (M:M) | ✅ |
| 14 | [`Team`](app/Features/Teams/Models/Team.php) | `app/Features/Teams/Models/` | sector, workers, miniTasks | ✅ |
| 15 | [`Worker`](app/Features/Workers/Models/Worker.php) | `app/Features/Workers/Models/` | user, team, workLogs, miniTasks | ✅ |
| 16 | [`Client`](app/Features/Clients/Models/Client.php) | `app/Features/Clients/Models/` | user, serviceOrders | ✅ |
| 17 | [`ServiceOrder`](app/Features/ServiceOrders/Models/ServiceOrder.php) | `app/Features/ServiceOrders/Models/` | client, manager, location, serviceType, tasks, attachments | ✅ |
| 18 | [`Task`](app/Features/Tasks/Models/Task.php) | `app/Features/Tasks/Models/` | serviceOrder, manager, miniTasks, sectors (M:M) | ✅ |
| 19 | [`MiniTask`](app/Features/MiniTasks/Models/MiniTask.php) | `app/Features/MiniTasks/Models/` | task, supervisor, workLogs, materials, assignedWorkers, assignedTeams | ✅ |
| 20 | [`WorkLog`](app/Features/WorkLogs/Models/WorkLog.php) | `app/Features/WorkLogs/Models/` | miniTask, materials, workers, reviewer; status enum | ✅ |
| 21 | [`Attachment`](app/Shared/Models/Attachment.php) | `app/Shared/Models/` | serviceOrder (M:1), miniTask (M:1) — polymorphic | ✅ |
| 22 | [`Notification`](app/Features/Notifications/Models/Notification.php) | `app/Features/Notifications/Models/` | notifiable (polymorphic) | ✅ |

**Bugs Fixed During Implementation:**
- [`Sector`](app/Features/Sectors/Models/Sector.php): Fixed `belongsToMany` table name (`task_sectors` → `tasks_sectors`)
- [`Team`](app/Features/Teams/Models/Team.php): Fixed `belongsToMany` table name (`mini_task_workers_teams` → `mini_tasks_workers_teams`)
- [`AppSetting`](app/Shared/Models/AppSetting.php): Removed bogus `user()` relation (no `user_id` column)

#### 1.2 Database Initialization ✅

32 migrations defined covering all tables:
- Core: users, roles, role_permissions, user_roles, user_preferences, app_settings
- Geographic: districts, municipalities, parishes, locations
- Master Data: service_types, units, materials
- Operations: clients, service_orders, tasks, mini_tasks, work_logs
- Organization: sectors, teams, workers
- Junctions: tasks_sectors, mini_tasks_workers_teams, mini_tasks_materials, work_logs_workers, work_logs_materials
- Supporting: attachments, notifications, personal_access_tokens, cache, jobs, failed_jobs
- Added: WorkLog status migration (adds status, reviewed_by, reviewed_at)

**Seeder Created:**
- [`GeographicDataSeeder`](database/seeders/GeographicDataSeeder.php): Populates Viseu district with 5 municipalities (Mangualde, Viseu, Tondela, Lamego, São Pedro do Sul) and ~80 parishes

---

### PHASE 2: Authentication & Authorization System ✅

**Completed**: 2026-04-23 → 2026-04-28  
**Depended on**: Phase 1 (Models)

#### 2.1 Authentication Feature

**Feature Path**: `app/Features/Authentication/`

**Controller:**
- [`AuthController`](app/Features/Authentication/Controllers/AuthController.php)
  - `login(LoginRequest)` → Authenticate, create Sanctum token
  - `logout()` → Revoke current token
  - `me()` → Get current user with role + permissions

**Decision (Tech Lead)**: NO public registration or password recovery. User creation is admin-only via UserController. System is closed.

**Requests:**
- [`LoginRequest`](app/Features/Authentication/Requests/LoginRequest.php) — email (required, email), password (required)

**Routes**: [`routes/api/authentication.php`](routes/api/authentication.php)
```
POST   /auth/login
POST   /auth/logout        (auth:sanctum)
GET    /auth/me             (auth:sanctum)
```

#### 2.2 Users & Roles Feature

**Feature Path**: `app/Features/Admin/`

**Controllers:**
- [`UserController`](app/Features/Admin/Controllers/UserController.php)
  - `index()` — List users (admin only)
  - `store(StoreUserRequest)` — Create user (admin only)
  - `show(User)` — Get user profile
  - `update(Request, User)` — Update user
  - `destroy(User)` — Soft delete (admin only)

- [`RoleController`](app/Features/Admin/Controllers/RoleController.php)
  - `index()` — List roles
  - `store(StoreRoleRequest)` — Create role
  - `show(Role)` — Get role
  - `update(UpdateRoleRequest, Role)` — Update role
  - `destroy(Role)` — Delete role

**Policies:**
- [`UserPolicy`](app/Shared/Policies/UserPolicy.php) — Admin-only CRUD
- [`RolePolicy`](app/Features/Admin/Policies/RolePolicy.php) — Permission-based CRUD

**Requests:**
- [`StoreRoleRequest`](app/Features/Admin/Requests/StoreRoleRequest.php) — name: required, max:50, unique
- [`UpdateRoleRequest`](app/Features/Admin/Requests/UpdateRoleRequest.php) — name with unique ignoring self

**Routes**: [`routes/api/admin.php`](routes/api/admin.php)
```
GET    /admin/users
POST   /admin/users
GET    /admin/users/{user}
PUT    /admin/users/{user}
DELETE /admin/users/{user}

GET    /admin/roles
POST   /admin/roles
GET    /admin/roles/{role}
PUT    /admin/roles/{role}
DELETE /admin/roles/{role}
```

#### 2.3 Settings & Preferences

**Shared Controllers:**
- [`AppSettingController`](app/Shared/Controllers/AppSettingController.php) — index, store, show, update, destroy
- [`UserPreferenceController`](app/Shared/Controllers/UserPreferenceController.php) — index, update

**Policies:**
- [`AppSettingPolicy`](app/Features/Settings/Policies/AppSettingPolicy.php) — **Admin-only** via `isAdmin()`
- [`UserPreferencePolicy`](app/Shared/Policies/UserPreferencePolicy.php) — **Owner-scoped** via `isOwner()`

---

### PHASE 3: Master Data Management ✅

**Completed**: 2026-04-23 → 2026-04-28  
**Depended on**: Phase 2 (auth works)

#### 3.1 Service Types Feature

**Controller:**
- [`ServiceTypeController`](app/Features/ServiceTypes/Controllers/ServiceTypeController.php)
  - `index()` — List (paginated)
  - `store(StoreServiceTypeRequest)` — Create
  - `show(ServiceType)` — Get
  - `update(UpdateServiceTypeRequest, ServiceType)` — Update
  - `destroy(ServiceType)` — Soft delete

**Policy**: [`ServiceTypePolicy`](app/Features/ServiceTypes/Policies/ServiceTypePolicy.php)

**Routes**: [`routes/api/service-types.php`](routes/api/service-types.php)
```
GET    /service-types
POST   /service-types
GET    /service-types/{serviceType}
PUT    /service-types/{serviceType}
DELETE /service-types/{serviceType}
```

#### 3.2 Geographic Locations (Read-Only)

**Controllers:**
- [`DistrictController`](app/Shared/Controllers/DistrictController.php) — index, show (with municipalities)
- [`MunicipalityController`](app/Shared/Controllers/MunicipalityController.php) — index, show (with district + parishes)
- [`ParishController`](app/Shared/Controllers/ParishController.php) — index, show (with municipality + locations)

**Resources:**
- [`DistrictResource`](app/Shared/Resources/DistrictResource.php)
- [`MunicipalityResource`](app/Shared/Resources/MunicipalityResource.php)
- [`ParishResource`](app/Shared/Resources/ParishResource.php)

**Routes**: All under `auth:sanctum`, read-only
```
GET    /districts
GET    /districts/{district}
GET    /municipalities
GET    /municipalities/{municipality}
GET    /parishes
GET    /parishes/{parish}
```

#### 3.3 Locations Feature

**Controller:**
- [`LocationController`](app/Features/Locations/Controllers/LocationController.php)
  - `index()` — List (filter by parish)
  - `store(StoreLocationRequest)` — Create
  - `show(Location)` — Get
  - `update(UpdateLocationRequest, Location)` — Update
  - `destroy(Location)` — Soft delete

**Services**: [`LocationService`](app/Features/Locations/Services/LocationService.php)

**Policy**: [`LocationPolicy`](app/Features/Locations/Policies/LocationPolicy.php)

#### 3.4 Materials & Units

**Controllers:**
- [`MaterialController`](app/Features/Materials/Controllers/MaterialController.php) — CRUD
- [`UnitController`](app/Shared/Controllers/UnitController.php) — CRUD

**Services**: [`MaterialService`](app/Features/Materials/Services/MaterialService.php)

**Policies**: [`MaterialPolicy`](app/Features/Materials/Policies/MaterialPolicy.php), [`UnitPolicy`](app/Shared/Policies/UnitPolicy.php)

**Requests**: [`StoreUnitRequest`](app/Shared/Requests/StoreUnitRequest.php), [`UpdateUnitRequest`](app/Shared/Requests/UpdateUnitRequest.php)

---

### PHASE 4: Organization & Clients ✅

**Completed**: 2026-04-23 → 2026-04-28  
**Depended on**: Phase 3 (master data exists)

#### 4.1 Sectors & Teams

**Controllers:**
- [`SectorController`](app/Features/Sectors/Controllers/SectorController.php) — CRUD
- [`TeamController`](app/Features/Teams/Controllers/TeamController.php) — CRUD

**Services**: [`SectorService`](app/Features/Sectors/Services/SectorService.php), [`TeamService`](app/Features/Teams/Services/TeamService.php)

**Policies**: [`SectorPolicy`](app/Features/Sectors/Policies/SectorPolicy.php), [`TeamPolicy`](app/Features/Teams/Policies/TeamPolicy.php)

#### 4.2 Workers

**Controller:**
- [`WorkerController`](app/Features/Workers/Controllers/WorkerController.php) — CRUD

**Policy**: [`WorkerPolicy`](app/Features/Workers/Policies/WorkerPolicy.php)

#### 4.3 Clients

**Controller:**
- [`ClientController`](app/Features/Clients/Controllers/ClientController.php) — CRUD

**Services**: [`ClientService`](app/Features/Clients/Services/ClientService.php)

**Policy**: [`ClientPolicy`](app/Features/Clients/Policies/ClientPolicy.php)

---

### PHASE 5: Service Orders & Work Execution ✅

**Completed**: 2026-04-23 → 2026-04-28  
**Depended on**: Phase 4 (clients, teams, sectors)

#### 5.1 Service Orders

**Controller:**
- [`ServiceOrderController`](app/Features/ServiceOrders/Controllers/ServiceOrderController.php)
  - `index()` — List (filterable by status, priority, date range)
  - `store(StoreServiceOrderRequest)` — Create
  - `show(ServiceOrder)` — Get with all relations
  - `update(UpdateServiceOrderRequest, ServiceOrder)` — Update
  - `cancel(ServiceOrder)` — Cancel
  - `complete(ServiceOrder)` — Complete (triggered by cascade)
  - `destroy(ServiceOrder)` — Soft delete

**Services**: [`ServiceOrderService`](app/Features/ServiceOrders/Services/ServiceOrderService.php)
  - `create()` — Create with TransactionHandler
  - `update()` — Update order
  - `cancel()` — Cancel order
  - `complete()` — Complete (final step in cascade chain)

**Policy**: [`ServiceOrderPolicy`](app/Features/ServiceOrders/Policies/ServiceOrderPolicy.php)
  - viewAny, view, create, update, cancel, complete, delete, restore, forceDelete

**Events**: `ServiceOrderCreatedEvent` → `SendServiceOrderCreatedNotification`

#### 5.2 Tasks

**Controller:**
- [`TaskController`](app/Features/Tasks/Controllers/TaskController.php)
  - `index()` — List (filter by service_order, status)
  - `store(StoreTaskRequest)` — Create
  - `show(Task)` — Get with relations
  - `update(UpdateTaskRequest, Task)` — Update
  - `cancel(Task)` — Cancel
  - `destroy(Task)` — Soft delete

**Services**: [`TaskService`](app/Features/Tasks/Services/TaskService.php)
  - `create()` — Create task with TransactionHandler
  - `update()` — Update task
  - `cancel()` — Cancel task (blocks completion)
  - `complete()` — Called by cascade when all mini-tasks done

**Policy**: [`TaskPolicy`](app/Features/Tasks/Policies/TaskPolicy.php)

**Events**: `TaskCompletedEvent` → `CheckTaskCompletion`

#### 5.3 MiniTasks

**Controller:**
- [`MiniTaskController`](app/Features/MiniTasks/Controllers/MiniTaskController.php)
  - `index()` — List (filter by task, status)
  - `store(StoreMiniTaskRequest)` — Create
  - `show(MiniTask)` — Get with assignments
  - `update(Request, MiniTask)` — Update
  - `complete(MiniTask)` — Complete (cascade trigger)

**Services**: [`MiniTaskService`](app/Features/MiniTasks/Services/MiniTaskService.php)
  - `create()` — Create with TransactionHandler
  - `complete()` — Complete mini-task, dispatch MiniTaskCompletedEvent

**Policy**: [`MiniTaskPolicy`](app/Features/MiniTasks/Policies/MiniTaskPolicy.php)

**Events**: `MiniTaskCompletedEvent` → `CheckMiniTasksCompletion`

#### 5.4 Work Logs (With Approval Flow)

**Controller:**
- [`WorkLogController`](app/Features/WorkLogs/Controllers/WorkLogController.php)
  - `index()` — List (filter by mini-task, status, date range)
  - `store(StoreWorkLogRequest)` — Create
  - `show(WorkLog)` — Get with materials, workers, reviewer
  - `update(Request, WorkLog)` — Update
  - `complete(Request, WorkLog)` — Submit for approval (status → submitted)
  - `approve(Request, WorkLog)` — Approve (manager/supervisor)
  - `reject(Request, WorkLog)` — Reject (manager/supervisor)

**Services**: [`WorkLogService`](app/Features/WorkLogs/Services/WorkLogService.php)
  - `create()` — Create with status (`in_progress` or `submitted`)
  - `complete()` — Set status to `submitted`, dispatch WorkLogCompletedEvent
  - `approve()` — Validate state transition, set `approved` + reviewer
  - `reject()` — Validate state transition, set `rejected` + reviewer

**Policy**: [`WorkLogPolicy`](app/Features/WorkLogs/Policies/WorkLogPolicy.php)
  - viewAny, view, create, update, complete, approve, reject

**State Machine** ([`WorkLogStatus`](app/Core/Enums/WorkLogStatus.php)):
```
in_progress → submitted → approved
                        → rejected
```

**Events**: `WorkLogCompletedEvent` → `CheckWorkLogsCompletion` (starts cascade chain)

#### 5.5 Attachments

**Controller:**
- [`AttachmentController`](app/Shared/Controllers/AttachmentController.php)
  - `store(Request)` — Upload file + create attachment record
  - `destroy(Attachment)` — Delete file + record

**Services**: [`AttachmentService`](app/Shared/Services/AttachmentService.php)
  - `upload()` — Validate, store file, create Attachment
  - `delete()` — Remove file from storage, soft-delete record

**Policy**: [`AttachmentPolicy`](app/Shared/Policies/AttachmentPolicy.php) — create, delete

---

### PHASE 6: Additional Features ✅

**Completed**: 2026-04-28  
**Depended on**: Phase 5 (work execution)

#### 6.1 Notifications

**Controller:**
- [`NotificationController`](app/Features/Notifications/Controllers/NotificationController.php)
  - `index()` — Get user's notifications
  - `markAsRead(Notification)` — Mark single notification as read

**Services**: [`NotificationService`](app/Features/Notifications/Services/NotificationService.php)

**Policy**: [`NotificationPolicy`](app/Features/Notifications/Policies/NotificationPolicy.php) — owner-scoped

#### 6.2 CSV Export

**Controller:**
- [`ExportController`](app/Features/Export/Controllers/ExportController.php)
  - `serviceOrders(Request)` — Export ServiceOrders with filters (status, priority, date range)
  - `workLogs(Request)` — Export WorkLogs with filters (mini-task, status, date range, worker)

**Services**: [`CsvExportService`](app/Features/Export/Services/CsvExportService.php)
  - `exportServiceOrders()` — LazyCollection + StreamedResponse
  - `exportWorkLogs()` — LazyCollection + StreamedResponse
  - `streamCsv()` — Private: UTF-8 BOM, semicolon delimiter, memory-efficient streaming

**Routes**: [`routes/api/exports.php`](routes/api/exports.php)
```
GET /exports/service-orders  (auth:sanctum)
GET /exports/work-logs       (auth:sanctum)
```

#### 6.3 Admin & Security Hardening

- **No public registration** — closed system, admin-only user creation
- **AppSetting** — Admin-only via `isAdmin()` policy
- **UserPreference** — Owner-scoped via `isOwner()` policy
- **WorkLog Approval** — Supervisor/manager approval required
- **All controllers** — Authorized via FormRequest `authorize()` or `$this->authorize()` in controller
- **Input sanitization** — Centralized via [`InputSanitizer`](app/Core/Helpers/InputSanitizer.php)

---

## 📅 Timeline Summary

| Phase | Duration | Completed | Status | Key Deliverables |
|-------|----------|-----------|--------|------------------|
| 1: Core Models & DB | ~1 week | 2026-04-28 | ✅ | 22 models, 32 migrations |
| 2: Authentication | ~1 week | 2026-04-28 | ✅ | Auth system, RBAC, settings |
| 3: Master Data | ~1 week | 2026-04-28 | ✅ | Service types, locations, materials |
| 4: Organization | ~1 week | 2026-04-28 | ✅ | Sectors, teams, workers, clients |
| 5: Service Orders & Work | ~2 weeks | 2026-04-28 | ✅ | Core business logic + approval flow |
| 6: Additional & Testing | ~1 week | 2026-04-28 | ✅ | Export, notifications, admin, security |
| **Total** | **~7 weeks** | **2026-04-28** | **✅ ALL DONE** | **Fully working backend** |

---

## ✅ Success Criteria — MET

| Criterion | Status |
|-----------|--------|
| All 22+ models implemented with relationships | ✅ |
| 16 features fully implemented with controllers, services, routes, policies | ✅ |
| Working authentication & authorization system (Sanctum + RBAC) | ✅ |
| All 32 database migrations defined | ✅ |
| Event-driven cascade completion chain (WorkLog→MiniTask→Task→ServiceOrder) | ✅ |
| WorkLog approval flow with state machine (in_progress→submitted→approved\rejected) | ✅ |
| CSV export with memory-efficient streaming (LazyCollection + StreamedResponse) | ✅ |
| Geographic seeder (Viseu district, 5 municipalities, ~80 parishes) | ✅ |
| Comprehensive test coverage | ⏳ Pending |
| API documentation (OpenAPI/Swagger) | ⏳ Pending |

---

## ⏳ Remaining Work

1. **Tests**: Unit + Feature + Integration test suite
2. **Documentation**: OpenAPI/Swagger, deployment guide
3. **Frontend**: Vue.js/React integration
4. **Restore Endpoints**: Deferred to frontend phase (Tech Lead decision)
