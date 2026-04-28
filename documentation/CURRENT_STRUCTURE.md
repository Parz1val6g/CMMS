# Current Project Structure

**Project**: Service Management Backend (Laravel 12)  
**Status**: Fully Implemented — Production-Ready  
**Database**: 32 tables with comprehensive relationships  
**Architecture**: Modular feature-based design (16 features)  
**Authentication**: Sanctum API tokens with RBAC  
**Events**: 5 event types with cascade completion chain (WorkLog→MiniTask→Task→ServiceOrder)

---

## 📊 Project Overview

### Database Schema (from migrations)

#### Core Entity Tables

**Users & Authentication**
- `users` — Base user records (first_name, last_name, phone, email, password, status)
  - Unique constraints: phone, email
  - Indexes: email, phone, status
  - Soft deletes enabled
  - UUID primary key via `App\Core\Traits\Base`

**Roles & Permissions**
- `roles` — Role definitions (name)
  - Soft deletes enabled
- `role_permissions` — Permission definitions (resource, action, description)
  - Unique constraint: role_id + resource + action
  - Foreign key to roles
- `user_roles` — User-to-role associations (junction table)
  - Primary key: user_id + role_id
  - Foreign keys: users, roles
  - Soft deletes enabled
- `user_preferences` — User settings & preferences (key, value)
  - Unique constraint: user_id + key
  - Foreign key to users

**App Settings**
- `app_settings` — System-wide configuration (key, value, section)
  - Unique constraint: key + section
  - Soft deletes enabled

#### Geographic Hierarchy

**Districts → Municipalities → Parishes → Locations**
- `districts` — Administrative districts (name)
- `municipalities` — Cities/municipalities (name, district_id)
- `parishes` — Parishes/neighborhoods (name, municipality_id)
- `locations` — Physical addresses (street_address, postal_code, landmark, latitude/longitude)
  - Foreign key to parishes
  - Coordinates stored as DECIMAL(10, 8)

#### Client Management

- `clients` — Client profiles (nif [tax ID], user_id for manager)
  - Unique constraint: nif
  - Foreign key to users
  - Soft deletes enabled

#### Service Operations

**Service Types**
- `service_types` — Service definitions (name, description)
  - Soft deletes enabled

**Service Orders** (Main aggregate)
- `service_orders` — Service requests (process, priority, execution_date, status)
  - Foreign keys: clients, manager (users), locations, service_types
  - Fields: process (VARCHAR 250), priority (VARCHAR 20), status (VARCHAR 50)
  - Composite index: status + created_at
  - Soft deletes enabled
  - Event: `ServiceOrderCreatedEvent` → `SendServiceOrderCreatedNotification`

#### Work Hierarchy

**Sectors & Teams**
- `sectors` — Organizational sectors (name, head_id)
  - Foreign key to users (head_id)
- `teams` — Work teams (name, sector_id)
  - Foreign key to sectors
  - Soft deletes enabled

**Workers**
- `workers` — Worker profiles (user_id, team_id)
  - Unique constraint: user_id
  - Foreign keys: users, teams
  - Soft deletes enabled

**Tasks**
- `tasks` — Service order tasks (name, status)
  - Foreign keys: service_orders, manager (users)
  - Composite index: service_order_id + status
  - Soft deletes enabled
- `tasks_sectors` — Task-to-sector assignments (junction table)

**Mini-Tasks**
- `mini_tasks` — Subtasks under tasks (description, status)
  - Foreign keys: tasks, supervisor (users)
  - Composite index: task_id + status
  - Soft deletes enabled
- `mini_tasks_workers_teams` — Assign mini-tasks to workers or teams (mutually exclusive)
  - Check constraint: worker_id XOR team_id
- `mini_tasks_materials` — Planned materials for mini-tasks (planned_quantity)

**Work Logs** (Time tracking with approval flow)
- `work_logs` — Completed work records (started_at, completed_at, description, status)
  - Foreign key: mini_tasks
  - Generated column: `duration_minutes` (auto-calculated from time diff)
  - Status: in_progress → submitted → approved | rejected
  - Fields: status (VARCHAR 20), reviewed_by (FK→users), reviewed_at (timestamp)
  - Soft deletes enabled
- `work_logs_workers` — Workers assigned to work logs (junction table)
- `work_logs_materials` — Materials used in work logs (quantity_used, unit_price_at_use)

#### Materials Management

- `units` — Measurement units (name, abbreviation)
  - Unique constraint: abbreviation
- `materials` — Materials (name, unit_id, stock_quantity)
  - Foreign key to units
  - Decimal field: stock_quantity with check >= 0

**Attachments**
- `attachments` — File uploads (file_path, file_name, mime_type)
  - Polymorphic: belongs to either service_orders OR mini_tasks (mutually exclusive)
  - Check constraint: service_order_id XOR mini_task_id
  - Soft deletes enabled

---

## 🏗️ Infrastructure (Implementado)

### Enums (`app/Core/Enums/`) — 8 files

| Enum | Values | Key Methods |
|------|--------|-------------|
| [`UserRole`](app/Core/Enums/UserRole.php) | admin, manager, pending, supervisor, worker | `label()`, `isAdmin()`, `isManager()` |
| [`TaskStatus`](app/Core/Enums/TaskStatus.php) | pending, in_progress, completed, blocked, cancelled | `label()`, `isOpen()`, `isClosed()` |
| [`WorkLogStatus`](app/Core/Enums/WorkLogStatus.php) | in_progress, submitted, approved, rejected | `canTransitionTo()` — state machine |
| [`MiniTaskStatus`](app/Core/Enums/MiniTaskStatus.php) | pending, in_progress, completed, blocked, cancelled | `label()`, `isOpen()`, `isClosed()` |
| [`ServicesOrdersPriority`](app/Core/Enums/ServicesOrdersPriority.php) | urgent, high, normal, low | `label()`, `weight()`, `isHighPriority()` |
| [`PermissionAction`](app/Core/Enums/PermissionAction.php) | view, create, update, delete, export, etc. | values only |
| [`PermissionResource`](app/Core/Enums/PermissionResource.php) | users, clients, service_orders, tasks, etc. | values only |
| [`SystemStatus`](app/Core/Enums/SystemStatus.php) | active, inactive, suspended, archived | `label()`, `isActive()` |

### Traits (`app/Core/Traits/`) — 6 files

| Trait | Purpose |
|-------|---------|
| [`Base`](app/Core/Traits/Base.php) | UUID primary keys, non-incrementing |
| [`Timestamped`](app/Core/Traits/Timestamped.php) | Additional timestamp fields |
| [`Publishing`](app/Core/Traits/Publishing.php) | Publish/unpublish logic with `published_at` |
| [`Filterable`](app/Core/Traits/Filterable.php) | Dynamic query filtering with `scopeFilter()` |
| [`ExportCsv`](app/Core/Traits/ExportCsv.php) | Model-to-CSV serialization |
| [`Completable`](app/Core/Traits/Completable.php) | Completion tracking (`markComplete()`, `isComplete()`) |

### Services (`app/Core/Services/`) — 4 files

| Service | Purpose |
|---------|---------|
| [`PermissionManager`](app/Core/Services/PermissionManager.php) | RBAC engine with `hasPermission()`, cache invalidation |
| [`CacheManager`](app/Core/Services/CacheManager.php) | Caching orchestration with `cache()`, `invalidate()` |
| [`FilterService`](app/Core/Services/FilterService.php) | Dynamic query filter application |
| [`TransactionHandler`](app/Core/Services/TransactionHandler.php) | Atomic DB operations with rollback |

### Helpers (`app/Core/Helpers/`) — 4 files

| Helper | Purpose |
|--------|---------|
| [`ValidationHelper`](app/Core/Helpers/ValidationHelper.php) | Common validation rules and custom validators |
| [`InputSanitizer`](app/Core/Helpers/InputSanitizer.php) | XSS prevention, sanitize HTML/email/phone/URL |
| [`FormattingHelper`](app/Core/Helpers/FormattingHelper.php) | Date/number formatting, string manipulation |
| [`FeatureFlags`](app/Core/Helpers/FeatureFlags.php) | Feature toggles and A/B testing |

### Middleware (`app/Core/Middleware/`) — 4 files

| Middleware | Purpose |
|------------|---------|
| [`AuthenticateApi`](app/Core/Middleware/AuthenticateApi.php) | Bearer token validation via Sanctum |
| [`CheckSoftDeletedUser`](app/Core/Middleware/CheckSoftDeletedUser.php) | Reject requests from soft-deleted users |
| [`EnsureEmailVerified`](app/Core/Middleware/EnsureEmailVerified.php) | Email verification requirement |
| [`SetUserLocale`](app/Core/Middleware/SetUserLocale.php) | Localization per user preference |

### Policies (`app/Core/Policies/`) — 1 Base + 14 Feature Policies

| Policy | Location | Authz Logic |
|--------|----------|-------------|
| [`BasePolicy`](app/Core/Policies/BasePolicy.php) | `app/Core/Policies/` | `before()` (admin bypass), `isAdmin()`, `isOwner()`, `hasPermission()`, `isManagerScoped()` |
| [`UserPolicy`](app/Shared/Policies/UserPolicy.php) | `app/Shared/Policies/` | Admin-only CRUD |
| [`AttachmentPolicy`](app/Shared/Policies/AttachmentPolicy.php) | `app/Shared/Policies/` | Permission-based create/delete |
| [`UnitPolicy`](app/Shared/Policies/UnitPolicy.php) | `app/Shared/Policies/` | Public view, admin create/update/delete |
| [`UserPreferencePolicy`](app/Shared/Policies/UserPreferencePolicy.php) | `app/Shared/Policies/` | Owner-scoped (users manage own preferences) |
| [`RolePolicy`](app/Features/Admin/Policies/RolePolicy.php) | `app/Features/Admin/Policies/` | Permission-based CRUD |
| [`ServiceOrderPolicy`](app/Features/ServiceOrders/Policies/ServiceOrderPolicy.php) | `app/Features/ServiceOrders/Policies/` | Permission + manager scope + complete |
| [`TaskPolicy`](app/Features/Tasks/Policies/TaskPolicy.php) | `app/Features/Tasks/Policies/` | Permission + manager scope + cancel |
| [`MiniTaskPolicy`](app/Features/MiniTasks/Policies/MiniTaskPolicy.php) | `app/Features/MiniTasks/Policies/` | Permission + supervisor scope + complete |
| [`WorkLogPolicy`](app/Features/WorkLogs/Policies/WorkLogPolicy.php) | `app/Features/WorkLogs/Policies/` | Permission + approve/reject for supervisors |
| [`SectorPolicy`](app/Features/Sectors/Policies/SectorPolicy.php) | `app/Features/Sectors/Policies/` | Permission-based CRUD |
| [`TeamPolicy`](app/Features/Teams/Policies/TeamPolicy.php) | `app/Features/Teams/Policies/` | Permission-based CRUD |
| [`WorkerPolicy`](app/Features/Workers/Policies/WorkerPolicy.php) | `app/Features/Workers/Policies/` | Permission-based CRUD |
| [`AppSettingPolicy`](app/Features/Settings/Policies/AppSettingPolicy.php) | `app/Features/Settings/Policies/` | **Admin-only** (via `isAdmin()`) |
| [`NotificationPolicy`](app/Features/Notifications/Policies/NotificationPolicy.php) | `app/Features/Notifications/Policies/` | Owner-scoped |
| [`ClientPolicy`](app/Features/Clients/Policies/ClientPolicy.php) | `app/Features/Clients/Policies/` | Permission-based CRUD |
| [`ServiceTypePolicy`](app/Features/ServiceTypes/Policies/ServiceTypePolicy.php) | `app/Features/ServiceTypes/Policies/` | Permission-based CRUD |
| [`MaterialPolicy`](app/Features/Materials/Policies/MaterialPolicy.php) | `app/Features/Materials/Policies/` | Permission-based CRUD |
| [`LocationPolicy`](app/Features/Locations/Policies/LocationPolicy.php) | `app/Features/Locations/Policies/` | Permission-based CRUD |

### Event System (`app/Providers/EventServiceProvider.php`)

**5 Event-Listener pairs registered:**

| Event | Listener | Purpose |
|-------|----------|---------|
| [`ServiceOrderCreatedEvent`](app/Features/ServiceOrders/Events/ServiceOrderCreatedEvent.php) | [`SendServiceOrderCreatedNotification`](app/Features/Notifications/Listeners/SendServiceOrderCreatedNotification.php) | Notify on new service order |
| [`UserCreatedEvent`](app/Features/Admin/Events/UserCreatedEvent.php) | [`CreateClientProfile`](app/Features/Clients/Listeners/CreateClientProfile.php), [`CreateWorkerProfile`](app/Features/Workers/Listeners/CreateWorkerProfile.php) | Auto-create profiles |
| [`WorkLogCompletedEvent`](app/Features/WorkLogs/Events/WorkLogCompletedEvent.php) | [`CheckWorkLogsCompletion`](app/Features/MiniTasks/Listeners/CheckWorkLogsCompletion.php) | Trigger cascade completion |
| [`MiniTaskCompletedEvent`](app/Features/MiniTasks/Events/MiniTaskCompletedEvent.php) | [`CheckMiniTasksCompletion`](app/Features/Tasks/Listeners/CheckMiniTasksCompletion.php) | Trigger cascade completion |
| [`TaskCompletedEvent`](app/Features/Tasks/Events/TaskCompletedEvent.php) | [`CheckTaskCompletion`](app/Features/Tasks/Listeners/CheckTasksCompletion.php) | Trigger ServiceOrder completion |

**Cascade Completion Chain:**
```
WorkLog Completed
  → CheckWorkLogsCompletion
    → MiniTaskService::complete() (if all work logs approved)
      → MiniTaskCompletedEvent
        → CheckMiniTasksCompletion
          → TaskService::complete() (if all mini-tasks done)
            → TaskCompletedEvent
              → CheckTaskCompletion
                → ServiceOrderService::complete() (if all tasks done)
                  → ServiceOrderCompletedEvent
```

---

## 📦 Models — 30+ Fully Implemented

All models use UUID PK via `Base` trait, soft deletes, proper relationships.

**Shared Models** (`app/Shared/Models/`):
- [`User`](app/Shared/Models/User.php) — roles(), preferences(), clientProfile(), workerProfile(), managedServiceOrders(), managedTasks(), managedMiniTasks(), headedSectors()
- [`Role`](app/Shared/Models/Role.php) — users(), permissions()
- [`RolePermission`](app/Shared/Models/RolePermission.php) — role()
- [`UserPreference`](app/Shared/Models/UserPreference.php) — user()
- [`AppSetting`](app/Shared/Models/AppSetting.php) — Key-value store
- [`District`](app/Shared/Models/District.php) — municipalities()
- [`Municipality`](app/Shared/Models/Municipality.php) — district(), parishes()
- [`Parish`](app/Shared/Models/Parish.php) — municipality(), locations()
- [`Unit`](app/Shared/Models/Unit.php) — Measurement units
- [`Attachment`](app/Shared/Models/Attachment.php) — serviceOrder(), miniTask() (polymorphic)

**Feature Models** (`app/Features/{Feature}/Models/`):
- [`Client`](app/Features/Clients/Models/Client.php) — user(), serviceOrders()
- [`ServiceType`](app/Features/ServiceTypes/Models/ServiceType.php) — serviceOrders()
- [`ServiceOrder`](app/Features/ServiceOrders/Models/ServiceOrder.php) — client(), manager(), location(), serviceType(), tasks(), attachments()
- [`Sector`](app/Features/Sectors/Models/Sector.php) — head(), teams(), tasks()
- [`Team`](app/Features/Teams/Models/Team.php) — sector(), workers(), miniTasks()
- [`Worker`](app/Features/Workers/Models/Worker.php) — user(), team(), workLogs(), miniTasks()
- [`Task`](app/Features/Tasks/Models/Task.php) — serviceOrder(), manager(), sectors(), miniTasks()
- [`MiniTask`](app/Features/MiniTasks/Models/MiniTask.php) — task(), supervisor(), workLogs(), materials(), assignedWorkers(), assignedTeams()
- [`WorkLog`](app/Features/WorkLogs/Models/WorkLog.php) — miniTask(), materials(), workers(), reviewer()
  - Status: in_progress → submitted → approved | rejected
- [`Material`](app/Features/Materials/Models/Material.php) — unit(), plannedForMiniTasks(), usedInWorkLogs()
- [`Location`](app/Features/Locations/Models/Location.php) — parish(), serviceOrders()
- [`Notification`](app/Features/Notifications/Models/Notification.php) — notifiable()

---

## 🎯 Features (16 Fully Implemented)

Each feature in `app/Features/{FeatureName}/` contains:
- `Controllers/` — Feature controllers (implemented)
- `Services/` — Business logic (implemented)
- `Models/` — Feature models (implemented)
- `Policies/` — Authorization policies (implemented)
- `Requests/` — Form validation requests (implemented)
- `Listeners/` — Event listeners (implemented where needed)

| # | Feature | Controllers | Routes | Status |
|---|---------|-------------|--------|--------|
| 1 | **Admin** | [`UserController`](app/Features/Admin/Controllers/UserController.php), [`RoleController`](app/Features/Admin/Controllers/RoleController.php) | [`admin.php`](routes/api/admin.php) | ✅ Full CRUD (users, roles) |
| 2 | **Authentication** | [`AuthController`](app/Features/Authentication/Controllers/AuthController.php) | [`authentication.php`](routes/api/authentication.php) | ✅ Login, logout, me |
| 3 | **Clients** | [`ClientController`](app/Features/Clients/Controllers/ClientController.php) | [`clients.php`](routes/api/clients.php) | ✅ Full CRUD |
| 4 | **Export** | [`ExportController`](app/Features/Export/Controllers/ExportController.php) | [`exports.php`](routes/api/exports.php) | ✅ CSV (ServiceOrders, WorkLogs) |
| 5 | **Locations** | [`LocationController`](app/Features/Locations/Controllers/LocationController.php) | [`locations.php`](routes/api/locations.php) | ✅ Full CRUD |
| 6 | **Materials** | [`MaterialController`](app/Features/Materials/Controllers/MaterialController.php) | [`materials.php`](routes/api/materials.php) | ✅ Full CRUD |
| 7 | **MiniTasks** | [`MiniTaskController`](app/Features/MiniTasks/Controllers/MiniTaskController.php) | [`mini-tasks.php`](routes/api/mini-tasks.php) | ✅ CRUD + complete |
| 8 | **Notifications** | [`NotificationController`](app/Features/Notifications/Controllers/NotificationController.php) | [`notifications.php`](routes/api/notifications.php) | ✅ List, markAsRead |
| 9 | **Sectors** | [`SectorController`](app/Features/Sectors/Controllers/SectorController.php) | [`sectors.php`](routes/api/sectors.php) | ✅ Full CRUD |
| 10 | **ServiceOrders** | [`ServiceOrderController`](app/Features/ServiceOrders/Controllers/ServiceOrderController.php) | [`service-orders.php`](routes/api/service-orders.php) | ✅ CRUD + cancel + complete |
| 11 | **ServiceTypes** | [`ServiceTypeController`](app/Features/ServiceTypes/Controllers/ServiceTypeController.php) | [`service-types.php`](routes/api/service-types.php) | ✅ Full CRUD |
| 12 | **Settings** | [`AppSettingController`](app/Shared/Controllers/AppSettingController.php) | (via admin routes) | ✅ Admin-only CRUD |
| 13 | **Tasks** | [`TaskController`](app/Features/Tasks/Controllers/TaskController.php) | [`tasks.php`](routes/api/tasks.php) | ✅ CRUD + cancel + destroy |
| 14 | **Teams** | [`TeamController`](app/Features/Teams/Controllers/TeamController.php) | [`teams.php`](routes/api/teams.php) | ✅ Full CRUD |
| 15 | **Workers** | [`WorkerController`](app/Features/Workers/Controllers/WorkerController.php) | [`workers.php`](routes/api/workers.php) | ✅ Full CRUD |
| 16 | **WorkLogs** | [`WorkLogController`](app/Features/WorkLogs/Controllers/WorkLogController.php) | [`work-logs.php`](routes/api/work-logs.php) | ✅ CRUD + approve + reject + complete |

**Shared Controllers:**
- [`AppSettingController`](app/Shared/Controllers/AppSettingController.php) — system settings CRUD
- [`AttachmentController`](app/Shared/Controllers/AttachmentController.php) — file upload/delete
- [`DistrictController`](app/Shared/Controllers/DistrictController.php) — read-only (index, show)
- [`MunicipalityController`](app/Shared/Controllers/MunicipalityController.php) — read-only (index, show)
- [`ParishController`](app/Shared/Controllers/ParishController.php) — read-only (index, show)
- [`UnitController`](app/Shared/Controllers/UnitController.php) — full CRUD
- [`UserPreferenceController`](app/Shared/Controllers/UserPreferenceController.php) — owner-scoped preferences

---

## 🗂️ Route Structure (20 route files)

All routes grouped by feature in `routes/api/`:
- [`api.php`](routes/api.php) — Central aggregator (includes all feature routes)
- [`admin.php`](routes/api/admin.php) — Admin operations
- [`authentication.php`](routes/api/authentication.php) — Auth endpoints
- [`attachments.php`](routes/api/attachments.php) — File operations
- [`clients.php`](routes/api/clients.php) — Client CRUD
- [`districts.php`](routes/api/districts.php) — Geographic read-only
- [`exports.php`](routes/api/exports.php) — CSV export endpoints
- [`locations.php`](routes/api/locations.php) — Location CRUD
- [`materials.php`](routes/api/materials.php) — Material CRUD
- [`mini-tasks.php`](routes/api/mini-tasks.php) — Mini-task management
- [`municipalities.php`](routes/api/municipalities.php) — Geographic read-only
- [`notifications.php`](routes/api/notifications.php) — Notification endpoints
- [`parishes.php`](routes/api/parishes.php) — Geographic read-only
- [`sectors.php`](routes/api/sectors.php) — Sector CRUD
- [`service-orders.php`](routes/api/service-orders.php) — Service order management
- [`service-types.php`](routes/api/service-types.php) — Service type CRUD
- [`tasks.php`](routes/api/tasks.php) — Task management
- [`teams.php`](routes/api/teams.php) — Team CRUD
- [`units.php`](routes/api/units.php) — Unit CRUD
- [`work-logs.php`](routes/api/work-logs.php) — Work log + approve/reject
- [`workers.php`](routes/api/workers.php) — Worker CRUD

---

## 📊 Project File Statistics

| Category | Count | Status |
|----------|-------|--------|
| PHP Infrastructure Files | ~50 | ✅ Implemented |
| Enums | 8 | ✅ Implemented |
| Traits | 6 | ✅ Implemented |
| Core Services | 4 | ✅ Implemented |
| Helpers | 4 | ✅ Implemented |
| Middleware | 4 | ✅ Implemented |
| Policies | 18+ (1 Base + 17 feature/shared) | ✅ Implemented |
| Models | 30+ | ✅ Implemented |
| Migrations | 32 | ✅ Defined |
| Controllers | 16 feature + 7 shared = 23 | ✅ Implemented |
| Route files | 20 | ✅ Defined |
| Event-Listener pairs | 5 | ✅ Registered |
| Seeders | 1 (GeographicData: Viseu district) | ✅ Created |
| Tests | 0 | ⏳ Pending |

---

## 🔗 Key Relationships Overview

```
Users
├── Roles (M:M via user_roles)
├── UserPreferences (1:M)
├── Clients (1:M as manager)
├── ServiceOrders (1:M as manager)
├── Sectors (1:M as head)
├── Tasks (1:M as manager)
├── MiniTasks (1:M as supervisor)
├── Workers (1:1 via workers.user_id)
└── Notifications (polymorphic)

ServiceOrder (Central aggregate)
├── Client (M:1)
├── Location (M:1)
├── ServiceType (M:1)
├── Manager/User (M:1)
├── Tasks (1:M)
├── Attachments (1:M polymorphic)
└── WorkLogs (through Tasks→MiniTasks→WorkLogs)

Task
├── ServiceOrder (M:1)
├── Manager/User (M:1)
├── MiniTasks (1:M)
├── Sectors (M:M via tasks_sectors)
└── WorkLogs (through MiniTasks)

MiniTask
├── Task (M:1)
├── Supervisor/User (M:1)
├── WorkLogs (1:M)
├── Materials (M:M via mini_tasks_materials)
├── Workers (M:M via mini_tasks_workers_teams)
├── Teams (M:M via mini_tasks_workers_teams)
└── Attachments (1:M polymorphic)

WorkLog (Time tracking with approval)
├── MiniTask (M:1)
├── Materials (M:M via work_logs_materials)
├── Workers (M:M via work_logs_workers)
├── Reviewer/User (M:1 via reviewed_by)
└── Status: in_progress → submitted → approved|rejected

Materials
├── Unit (M:1)
├── WorkLogsMaterials (1:M) — actual usage
└── MiniTasksMaterials (1:M) — planned usage

Geographic Hierarchy
├── District (1:M)
│   └── Municipality (1:M)
│       └── Parish (1:M)
│           └── Location (1:M)
```

---

## 🔄 Cascade Completion Chain

```
WorkLog.complete() → status=submitted
  → WorkLogCompletedEvent
    → CheckWorkLogsCompletion (listener)
      → MiniTaskService.complete() (if all work logs approved)
        → MiniTaskCompletedEvent
          → CheckMiniTasksCompletion (listener)
            → TaskService.complete() (if all mini-tasks completed)
              → TaskCompletedEvent
                → CheckTaskCompletion (listener)
                  → ServiceOrderService.complete() (if all tasks completed)
                    → ServiceOrderCompletedEvent
```

---

## 📋 Database Configuration Details

- **Primary Key Type**: UUID (VARCHAR 36)
- **Timestamps**: TIMESTAMP with CURRENT_TIMESTAMP and ON UPDATE
- **Soft Deletes**: All main entities have `deleted_at` column
- **Constraints**: Foreign keys, unique constraints, check constraints
- **Indexes**: Strategic indexes on foreign keys, status fields, created_at, and common filters
- **Collation**: utf8mb4_unicode_ci (MySQL)

---

## 📝 Notes

- **Philosophy**: Modular feature-based architecture with centralized infrastructure
- **Security**: RBAC via `PermissionManager` + `BasePolicy` (admin bypass, permission checks, ownership scoping)
- **Auth**: Sanctum tokens, no public registration (admin-only user creation)
- **Architecture**: Route → Controller → Service → Repository/Helper → Model
- **Migrations**: 32 files defining the complete schema
- **Performance**: `LazyCollection` + `StreamedResponse` for CSV exports
- **Geographic Data**: Seeder populates Viseu district with 5 municipalities and ~80 parishes
