# Adaptation Guide — COMPLETED

**Purpose**: Map splnet/backend implementation to current project and document how working logic was extracted and adapted to current project's superior modular architecture while respecting the database schema.

**Status**: All features fully implemented. This document serves as a reference of how the adaptation was executed.

---

## 📍 Feature-by-Feature Mapping

### 1. Authentication Feature

**Current Project**: `app/Features/Authentication/`  
**Splnet Reference**: `app/Http/Controllers/AuthController.php`

#### Splnet Implementation
- `login()` — Auth with constant-time password check, creates token + session + login history
- `register()` — New user creation (checks registration enabled flag)
- `logout()` — Single session logout
- `logoutAll()` — Logout all sessions
- `me()` — Current user + role + permissions
- `refreshToken()` — New token generation
- `forgotPassword()` — ⚠️ Security issue (no null check, token exposed)
- `resetPassword()` — Email-based reset

#### Current Project Implementation
- **AuthController**: `login()`, `logout()`, `me()` — using Laravel Sanctum tokens
- **Decision (Tech Lead)**: NO public registration or password recovery. Admin-only user creation.
- **Models needed**: User.php already existed; Role, RolePermission, UserRole, UserPreference were all implemented
- **Services**: PermissionManager (existing, enhanced) handles RBAC
- **Security Improvements**:
  - ✅ InputSanitizer for XSS prevention
  - ✅ PermissionManager for centralized permission checks
  - ✅ ForgotPassword NOT implemented (security risk avoided per design decision)
- **Routes**:
  ```
  POST /auth/login
  POST /auth/logout       (auth:sanctum)
  GET  /auth/me            (auth:sanctum)
  ```

---

### 2. Clients Feature

**Current Project**: `app/Features/Clients/`  
**Splnet Reference**: `app/Http/Controllers/ClientController.php`

#### Splnet Implementation
- `index()` — List with pagination, authorization checks
- `store()` — Create client with service
- `show()` — Get client with relations
- `update()` — Modify client
- `destroy()` — Soft-delete
- `restore()` — Restore deleted
- `locations()` — Get locations
- `serviceOrders()` — Get client's service orders

#### Current Project Implementation
- **Controller**: [`ClientController`](app/Features/Clients/Controllers/ClientController.php) — CRUD with eager loading
- **Models**: [`Client`](app/Features/Clients/Models/Client.php) with user(), serviceOrders() relations
- **Services**: [`ClientService`](app/Features/Clients/Services/ClientService.php) — CRUD logic
- **Policy**: [`ClientPolicy`](app/Features/Clients/Policies/ClientPolicy.php) — Permission-based
- **Adaptation**: Used Filterable trait, PermissionManager, TransactionHandler
- **Routes**:
  ```
  GET    /clients
  POST   /clients
  GET    /clients/{client}
  PUT    /clients/{client}
  DELETE /clients/{client}
  ```

---

### 3. Service Orders Feature

**Current Project**: `app/Features/ServiceOrders/`  
**Splnet Reference**: `app/Http/Controllers/ServiceOrderController.php`

#### Splnet Implementation
- `index()` — List with complex filtering (priority, service type)
- `store()` — Create with inline location creation + photo upload
- `show()` — Get with relations
- `update()` — Modify order
- `destroy()` — Soft-delete
- `restore()` — Restore deleted
- `changeStatus()` — Change order status

#### Current Project Implementation
- **Controller**: [`ServiceOrderController`](app/Features/ServiceOrders/Controllers/ServiceOrderController.php) — CRUD + cancel + complete + destroy
- **Models**: [`ServiceOrder`](app/Features/ServiceOrders/Models/ServiceOrder.php) with all relations (client, manager, location, serviceType, tasks, attachments)
- **Services**: [`ServiceOrderService`](app/Features/ServiceOrders/Services/ServiceOrderService.php) — create, update, cancel, complete with TransactionHandler
- **Policy**: [`ServiceOrderPolicy`](app/Features/ServiceOrders/Policies/ServiceOrderPolicy.php) — Permission + manager scope
- **Events**: `ServiceOrderCreatedEvent` → `SendServiceOrderCreatedNotification`
- **Adaptation**: Used Filterable trait, TransactionHandler for atomic operations, PermissionManager for authorization
- **Routes**:
  ```
  GET    /service-orders
  POST   /service-orders
  GET    /service-orders/{serviceOrder}
  PUT    /service-orders/{serviceOrder}
  POST   /service-orders/{serviceOrder}/cancel
  POST   /service-orders/{serviceOrder}/complete
  DELETE /service-orders/{serviceOrder}
  ```

---

### 4. Tasks & MiniTasks Features

**Current Project**: `app/Features/Tasks/`, `app/Features/MiniTasks/`  
**Splnet Reference**: (Not separate controllers; logic in ServiceOrderController)

#### Current Project Implementation

**Tasks:**
- **Controller**: [`TaskController`](app/Features/Tasks/Controllers/TaskController.php) — index, store, show, update, cancel, destroy
- **Services**: [`TaskService`](app/Features/Tasks/Services/TaskService.php) — create, update, cancel, complete
- **Policy**: [`TaskPolicy`](app/Features/Tasks/Policies/TaskPolicy.php) — Permission + manager scope
- **Cascade**: `TaskCompletedEvent` → `CheckTaskCompletion` → `ServiceOrderService::complete()`

**MiniTasks:**
- **Controller**: [`MiniTaskController`](app/Features/MiniTasks/Controllers/MiniTaskController.php) — index, store, show, update, complete
- **Services**: [`MiniTaskService`](app/Features/MiniTasks/Services/MiniTaskService.php) — create, complete
- **Policy**: [`MiniTaskPolicy`](app/Features/MiniTasks/Policies/MiniTaskPolicy.php) — Permission + supervisor scope
- **Cascade**: `MiniTaskCompletedEvent` → `CheckMiniTasksCompletion` → `TaskService::complete()`

**Adaptation**: Both use Completable trait, Filterable trait, TransactionHandler. The cascade chain is event-driven:
```
WorkLog → CheckWorkLogsCompletion → MiniTaskService::complete()
  → MiniTaskCompletedEvent → CheckMiniTasksCompletion → TaskService::complete()
    → TaskCompletedEvent → CheckTaskCompletion → ServiceOrderService::complete()
```

---

### 5. Work Logs Feature

**Current Project**: `app/Features/WorkLogs/`  
**Splnet Reference**: (Logic intertwined with MiniTasks)

#### Current Project Implementation
- **Controller**: [`WorkLogController`](app/Features/WorkLogs/Controllers/WorkLogController.php)
  - index, store, show, update, complete (submit), approve, reject
- **Services**: [`WorkLogService`](app/Features/WorkLogs/Services/WorkLogService.php)
  - create(), complete() with status transition, approve(), reject() with state validation
- **Policy**: [`WorkLogPolicy`](app/Features/WorkLogs/Policies/WorkLogPolicy.php) — Permission + supervisor approve/reject
- **State Machine** ([`WorkLogStatus`](app/Core/Enums/WorkLogStatus.php)):
  ```
  in_progress → submitted → approved
                          → rejected
  ```
- **Key Features**:
  - Auto-calculated duration from start/end times
  - Status transitions validated via `canTransitionTo()`
  - Reviewer tracking (reviewed_by, reviewed_at)
  - Event-driven cascade: WorkLogCompletedEvent → CheckWorkLogsCompletion
- **Routes**:
  ```
  GET    /work-logs
  POST   /work-logs
  GET    /work-logs/{workLog}
  PUT    /work-logs/{workLog}
  POST   /work-logs/{workLog}/complete
  POST   /work-logs/{workLog}/approve
  POST   /work-logs/{workLog}/reject
  ```

---

### 6. Workers, Teams & Sectors Features

**Current Project**: `app/Features/Workers/`, `app/Features/Teams/`, `app/Features/Sectors/`

#### Current Project Implementation

**Workers:**
- [`WorkerController`](app/Features/Workers/Controllers/WorkerController.php) — CRUD
- [`WorkerPolicy`](app/Features/Workers/Policies/WorkerPolicy.php)

**Teams:**
- [`TeamController`](app/Features/Teams/Controllers/TeamController.php) — CRUD
- [`TeamService`](app/Features/Teams/Services/TeamService.php)
- [`TeamPolicy`](app/Features/Teams/Policies/TeamPolicy.php)

**Sectors:**
- [`SectorController`](app/Features/Sectors/Controllers/SectorController.php) — CRUD
- [`SectorService`](app/Features/Sectors/Services/SectorService.php)
- [`SectorPolicy`](app/Features/Sectors/Policies/SectorPolicy.php)

**Relations**: Worker→User (1:1), Worker→Team (M:1), Team→Sector (M:1), Sector→Head(User) (M:1)

---

### 7. Geographic Locations (Read-Only)

**Current Project**: `app/Shared/Controllers/DistrictController.php`, `MunicipalityController.php`, `ParishController.php` + `app/Features/Locations/`

#### Current Project Implementation
- **Read-Only Geographic Endpoints** (per Tech Lead decision):
  - [`DistrictController`](app/Shared/Controllers/DistrictController.php) — index, show (with municipalities)
  - [`MunicipalityController`](app/Shared/Controllers/MunicipalityController.php) — index, show (with district + parishes)
  - [`ParishController`](app/Shared/Controllers/ParishController.php) — index, show (with municipality + locations)
- **Full CRUD** for user-created locations:
  - [`LocationController`](app/Features/Locations/Controllers/LocationController.php) — CRUD
  - [`LocationService`](app/Features/Locations/Services/LocationService.php)
  - [`LocationPolicy`](app/Features/Locations/Policies/LocationPolicy.php)
- **Seeder**: [`GeographicDataSeeder`](database/seeders/GeographicDataSeeder.php) — Viseu district, 5 municipalities, ~80 parishes
- **Resources**: DistrictResource, MunicipalityResource, ParishResource

---

### 8. Materials Feature

**Current Project**: `app/Features/Materials/` + Shared Units

#### Current Project Implementation
- **Materials**: [`MaterialController`](app/Features/Materials/Controllers/MaterialController.php) — CRUD
  - [`MaterialService`](app/Features/Materials/Services/MaterialService.php)
  - [`MaterialPolicy`](app/Features/Materials/Policies/MaterialPolicy.php)
- **Units**: [`UnitController`](app/Shared/Controllers/UnitController.php) — Full CRUD (added in Phase 4)
  - [`UnitPolicy`](app/Shared/Policies/UnitPolicy.php)
  - [`StoreUnitRequest`](app/Shared/Requests/StoreUnitRequest.php), [`UpdateUnitRequest`](app/Shared/Requests/UpdateUnitRequest.php)

---

### 9. Additional Features

**ServiceTypes:**
- [`ServiceTypeController`](app/Features/ServiceTypes/Controllers/ServiceTypeController.php) — CRUD
- [`ServiceTypePolicy`](app/Features/ServiceTypes/Policies/ServiceTypePolicy.php)

**Notifications:**
- [`NotificationController`](app/Features/Notifications/Controllers/NotificationController.php) — index, markAsRead
- [`NotificationService`](app/Features/Notifications/Services/NotificationService.php)
- [`NotificationPolicy`](app/Features/Notifications/Policies/NotificationPolicy.php) — owner-scoped

**Settings:**
- [`AppSettingController`](app/Shared/Controllers/AppSettingController.php) — Admin-only CRUD
- [`AppSettingPolicy`](app/Features/Settings/Policies/AppSettingPolicy.php) — Admin-only via `isAdmin()`
- [`UserPreferenceController`](app/Shared/Controllers/UserPreferenceController.php) — Owner-scoped
- [`UserPreferencePolicy`](app/Shared/Policies/UserPreferencePolicy.php) — via `isOwner()`

**Export (CSV):**
- [`ExportController`](app/Features/Export/Controllers/ExportController.php) — serviceOrders(), workLogs()
- [`CsvExportService`](app/Features/Export/Services/CsvExportService.php) — Memory-efficient streaming (LazyCollection + StreamedResponse)

**Admin:**
- [`UserController`](app/Features/Admin/Controllers/UserController.php) — Admin-only user CRUD
- [`RoleController`](app/Features/Admin/Controllers/RoleController.php) — Full CRUD (Phase 4)
- [`RolePolicy`](app/Features/Admin/Policies/RolePolicy.php) — Permission-based

---

## 🔄 Reusable Code Patterns Applied

### 1. Query Filtering Pattern
```
✅ Filterable trait on all list endpoints
✅ FilterService for complex queries
✅ Authorization checks before filtering
```

### 2. Resource Response Pattern
```
✅ Consistent JSON structure (data, meta)
✅ Include relations in show() responses
✅ Pagination on list endpoints
✅ API Resources for transformation
```

### 3. Authorization Pattern
```
✅ PermissionManager for RBAC
✅ BasePolicy with before() admin bypass, isAdmin(), isOwner(), hasPermission()
✅ FormRequest authorize() for input validation + permission check
✅ Controller $this->authorize() for explicit gates
```

### 4. Soft Delete Pattern
```
✅ All models use soft deletes
✅ destroy() methods on all CRUD controllers
```

### 5. Error Handling Pattern
```
✅ InputSanitizer for centralized sanitization
✅ Validation in FormRequests (not controllers)
✅ TransactionHandler for atomic DB operations
```

### 6. Event-Driven Cascade
```
✅ WorkLogCompleted → CheckWorkLogsCompletion → MiniTask::complete()
  → MiniTaskCompleted → CheckMiniTasksCompletion → Task::complete()
    → TaskCompleted → CheckTaskCompletion → ServiceOrder::complete()
```

---

## ⚠️ Security Issues Found in splnet/backend (Resolved in Current Project)

| Issue | splnet/backend | Current Project |
|-------|---------------|-----------------|
| **forgotPassword** | No null check on token, exposes in response | ❌ Not implemented (closed system — admin-only user creation) |
| **Export size** | No export size validation | ✅ LazyCollection streaming avoids memory issues |
| **Export authz** | Manager can export all clients | ✅ Policy-based authorization on all exports |
| **User update** | No ownership check | ✅ UserPolicy enforces admin-only |
| **N+1 Queries** | Missing eager loading | ✅ Eager loading in all show() and index() methods |
| **WorkLog authz** | No permission check on create | ✅ StoreWorkLogRequest.authorize() checks Policy |
| **AppSetting** | No admin enforcement | ✅ AppSettingPolicy.isAdmin() on all methods |

---

## 📝 Code Philosophy Integration

**Minimalismo**: Early returns, max 2 levels of nesting, guard clauses throughout
**DRY**: Extracted to BasePolicy (admin bypass, isOwner, hasPermission), TransactionHandler, Filterable trait
**Centralized Error Handling**: Validation in FormRequests, sanitization in InputSanitizer
**Smart Naming**: `verb + noun` for functions, `is/has` for booleans, short variables for internals
**Security First**: Prepared Statements (ORM), explicit input whitelisting, policy-based authorization
**Architecture**: Route → Controller → Service → Helper/Model (clean separation)
**Database**: Transactions for multi-step writes, UUID PKs, soft deletes, strategic indexes

---

## 🤖 AI Development Guidelines

The following rules MUST be followed by any AI assistant working on this codebase:

### Loan Workflow Constraint

When dealing with **'loan'** type Service Orders ([`workflow_type`](database/migrations/2026_05_04_090300_add_equipment_id_and_workflow_type_to_service_orders_table.php:13) = `'loan'`):

1. **Binary Task Rule**: Never allow more than two specific tasks:
   - `"Empréstimo de Equipamento"` — Tracks the equipment loan-out.
   - `"Devolução de Equipamento"` — Tracks the equipment return.
2. **No additional tasks** may be created on a Loan SO beyond these two.
3. **Materials Tab Priority**: The Materials tab is treated as priority for inventory tracking, but uses [`work_log_equipment`](database/seeders/DevelopmentTestSeeder.php:247) instead of `work_logs_materials` for tracking.
4. **Closure Trigger**: Completion of the "Devolução de Equipamento" task is the sole trigger for closing the SO (see [State Machine](documentation/user_stories/diagrams/state_machines/01_SERVICE_ORDER_LIFECYCLE.md)).
5. **Database**: The [`equipment_id`](database/migrations/2026_05_04_090300_add_equipment_id_and_workflow_type_to_service_orders_table.php:18) column on `service_orders` references the loaned equipment.
6. **Enforcement Directive**: Always enforce the 2-task rule (`"Empréstimo de Equipamento"` / `"Devolução de Equipamento"`) when managing or generating `'loan'` type Service Orders. No additional tasks may be created beyond these two.

### Form Schema Import Pattern

When creating or modifying Form Schema files in `app/Features/*/Schemas/`:

1. **`FormSchema`** is imported from the root Forms namespace:
   ```php
   use App\Core\Forms\FormSchema;
   ```
2. **All field/input classes** (`TextInput`, `SelectInput`, `EmailInput`, etc.) are imported from the `Fields` sub-namespace:
   ```php
   use App\Core\Forms\Fields\{TextInput, SelectInput, EmailInput};
   ```
3. **Never** import field classes from the root `App\Core\Forms\` namespace — they will not resolve and cause runtime errors.