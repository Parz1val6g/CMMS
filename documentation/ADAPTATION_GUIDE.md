# Adaptation Guide

**Purpose**: Map splnet/backend implementation to current project, identify reusable patterns, and guide feature development.

**Key Principle**: Extract working logic from splnet/backend and adapt it to current project's superior modular architecture while respecting the database schema defined in `db_tables.sql`.

---

## 📍 Feature-by-Feature Mapping

### 1. Authentication Feature

**Current Project**: `app/Features/Authentication/`  
**Splnet Reference**: `app/Http/Controllers/AuthController.php` (16 controllers total reference as well)

#### Splnet Implementation
- **AuthController methods**:
  - `login()` — Auth with constant-time password check, creates token + session + login history
  - `register()` — New user creation (checks registration enabled flag)
  - `logout()` — Single session logout
  - `logoutAll()` — Logout all sessions
  - `me()` — Current user + role + permissions
  - `refreshToken()` — New token generation
  - `forgotPassword()` — ⚠️ Security issue (no null check, token exposed)

**Current Project Database Schema Context**
- `users` table: id, first_name, last_name, phone, email, password, status, timestamps, soft delete
- `user_roles` junction: user_id, role_id (M:M relationship)
- `roles` table: id, name, columns (for visibility), timestamps, soft delete
- `role_permissions` table: role_id, resource, action (RBAC)
- `user_preferences` table: user_id, key, value (user settings)

#### Adaptation Strategy
1. **Extract AuthController logic** → `app/Features/Authentication/Controllers/AuthController.php`
2. **Implement Models needed**:
   - ✅ User.php already exists
   - → Role.php (with relationships to permissions)
   - → RolePermission.php (RBAC model)
   - → UserRole.php (pivot)
   - → UserPreference.php
3. **Create Services**:
   - `AuthenticationService` — Centralized auth logic
   - `TokenService` — Token generation/validation (use Laravel Sanctum)
   - `PermissionService` — Role/permission checking (enhance existing PermissionManager)
4. **Security Improvements from Current Project**:
   - Use ValidationHelper for input validation
   - Use InputSanitizer for data sanitization
   - Use PermissionManager for centralized permission checks
   - Fix forgotPassword security issue (null checks, secure token delivery)
5. **Routes** — `app/Features/Authentication/Routes/`
   - POST /auth/register
   - POST /auth/login
   - POST /auth/logout
   - POST /auth/logout-all
   - GET /auth/me
   - POST /auth/refresh-token
   - POST /auth/forgot-password
   - POST /auth/reset-password

---

### 2. Clients Feature

**Current Project**: `app/Features/Clients/`  
**Splnet Reference**: `app/Http/Controllers/ClientController.php`

#### Splnet Implementation
- **ClientController methods**:
  - `index()` — List with pagination, authorization checks, configurable routes
  - `store()` — Create client with service
  - `show()` — Get client with relations
  - `update()` — Modify client
  - `destroy()` — Soft-delete
  - `restore()` — Restore deleted
  - `locations()` — Get locations associated with client
  - `serviceOrders()` — Get client's service orders

**Current Project Database Schema Context**
- `clients` table: id, user_id (manager), nif (tax ID), timestamps, soft delete
- `users` table: client manager reference
- `service_orders` table: client_id foreign key (M:1 relationship)
- `locations` table: physical addresses (clients have locations via service orders)

#### Adaptation Strategy
1. **Extract ClientController logic** → `app/Features/Clients/Controllers/ClientController.php`
2. **Implement Models**:
   - → Client.php (with user manager relation, soft deletes)
   - → ServiceOrder.php (relation to client)
3. **Create Services**:
   - `ClientService` — Client CRUD + filtering
   - `ClientLocationService` — Client-location associations
4. **Apply Current Project Philosophy**:
   - Use Filterable trait for dynamic filtering
   - Use PermissionManager to restrict access (manager sees own clients)
   - Use ValidationHelper for NIF validation (Portuguese format)
   - Use TransactionHandler for atomic creates
5. **Routes**:
   - GET /clients — List (paginated)
   - POST /clients — Create
   - GET /clients/{id} — Show
   - PUT /clients/{id} — Update
   - DELETE /clients/{id} — Delete (soft)
   - POST /clients/{id}/restore — Restore
   - GET /clients/{id}/locations — Client locations
   - GET /clients/{id}/service-orders — Client service orders

---

### 3. Service Orders Feature

**Current Project**: `app/Features/ServiceOrders/`  
**Splnet Reference**: `app/Http/Controllers/ServiceOrderController.php`

#### Splnet Implementation
- **ServiceOrderController methods**:
  - `index()` — List with complex filtering (priority, service type)
  - `store()` — Create with inline location creation + photo upload
  - `show()` — Get with relations
  - `update()` — Modify order
  - `destroy()` — Soft-delete
  - `restore()` — Restore deleted
  - `changeStatus()` — Change order status

**Current Project Database Schema Context**
- `service_orders` table: id, process, client_id, manager_id, location_id, service_type_id, priority, execution_date, status, timestamps, soft delete
- Foreign keys: clients, users (manager), locations, service_types
- Relationships: Tasks (1:M), Attachments (1:M)
- Priority values: urgent, high, normal, low (ServicesOrdersPriority enum)

#### Adaptation Strategy
1. **Extract ServiceOrderController logic** → `app/Features/ServiceOrders/Controllers/ServiceOrderController.php`
2. **Implement Models**:
   - → ServiceOrder.php (with all relations)
   - → ServiceType.php (service definitions)
   - → Attachment.php (polymorphic for service orders & mini tasks)
   - → Location.php (reference from Locations feature)
3. **Create Services**:
   - `ServiceOrderService` — CRUD + status transitions
   - `ServiceOrderFilterService` — Complex filtering (priority, type, date ranges)
   - `FileUploadService` — Photo/document handling (refine current approach)
4. **Apply Philosophy**:
   - Use FilterService for complex queries
   - Use Filterable trait on ServiceOrder
   - Use TransactionHandler for atomic status changes
   - Use PermissionManager to authorize (manager/admin only)
   - Centralize file upload logic in helper
5. **Routes**:
   - GET /service-orders — List (paginated, filterable)
   - POST /service-orders — Create
   - GET /service-orders/{id} — Show
   - PUT /service-orders/{id} — Update
   - DELETE /service-orders/{id} — Delete
   - POST /service-orders/{id}/restore — Restore
   - POST /service-orders/{id}/change-status — Change status
   - GET /service-orders/{id}/tasks — Associated tasks
   - GET /service-orders/{id}/attachments — Associated attachments

---

### 4. Tasks & MiniTasks Features

**Current Project**: `app/Features/Tasks/`, `app/Features/MiniTasks/`  
**Splnet Reference**: (Controllers not separate; logic in ServiceOrderController)

#### Current Project Database Schema Context
- `tasks` table: id, service_order_id, manager_id, name, status, timestamps, soft delete
- `mini_tasks` table: id, task_id, supervisor_id, description, status, timestamps, soft delete
- `tasks_sectors` junction: task_id, sector_id (M:M)
- `mini_tasks_workers_teams` junction: mini_task_id, worker_id OR team_id (XOR constraint)
- `mini_tasks_materials` junction: mini_task_id, material_id, planned_quantity
- `work_logs` table: mini_task_id, started_at, completed_at, duration_minutes (auto), timestamps

#### Adaptation Strategy
1. **Task Feature** → `app/Features/Tasks/Controllers/TaskController.php`
   - Methods: index(), store(), show(), update(), destroy(), changeStatus()
   - Services: TaskService, TaskSectorService (assign sectors)
   - Models: Task, TaskSector
2. **MiniTask Feature** → `app/Features/MiniTasks/Controllers/MiniTaskController.php`
   - Methods: index(), store(), show(), update(), destroy(), changeStatus()
   - Services: MiniTaskService, MiniTaskAssignmentService (assign workers/teams)
   - Models: MiniTask, MiniTaskMaterial
3. **Apply Philosophy**:
   - Use FilterService for filtering by task status, sector, supervisor
   - Use TransactionHandler for atomic assignment operations
   - Use PermissionManager to restrict (supervisors see their tasks, managers see all)
   - Use Completable trait on both Task & MiniTask
4. **Routes**:
   - GET /tasks, POST /tasks, GET /tasks/{id}, PUT /tasks/{id}, DELETE /tasks/{id}
   - POST /tasks/{id}/sectors — Assign sectors
   - GET /mini-tasks, POST /mini-tasks, GET /mini-tasks/{id}, PUT /mini-tasks/{id}, DELETE /mini-tasks/{id}
   - POST /mini-tasks/{id}/assign-worker — Assign worker
   - POST /mini-tasks/{id}/assign-team — Assign team
   - POST /mini-tasks/{id}/materials — Add materials

---

### 5. Work Logs Feature

**Current Project**: `app/Features/WorkLogs/`  
**Splnet Reference**: (Logic intertwined with MiniTasks)

#### Current Project Database Schema Context
- `work_logs` table: id, mini_task_id, started_at, completed_at, duration_minutes (generated), description, timestamps, soft delete
- `work_logs_materials` junction: work_log_id, material_id, quantity_used, unit_price_at_use
- `work_logs_workers` junction: work_log_id, worker_id (M:M)
- `work_logs_materials` tracks actual usage vs planned (mini_tasks_materials)

#### Adaptation Strategy
1. **WorkLog Feature** → `app/Features/WorkLogs/Controllers/WorkLogController.php`
   - Methods: index(), store(), show(), update(), destroy(), approve(), reject()
   - Services: WorkLogService (time tracking), MaterialUsageService (track usage vs plan)
   - Models: WorkLog, WorkLogMaterial, WorkLogWorker (pivot)
2. **Key Features**:
   - Auto-calculated duration from start/end times
   - Immutable completed logs (soft delete only)
   - Material usage tracking (compare vs planned)
   - Worker assignment (multiple workers can contribute)
   - Status workflow: draft → submitted → approved/rejected
3. **Apply Philosophy**:
   - Use FilterService for date-range filtering
   - Use WorkLogStatus enum for state transitions
   - Use TransactionHandler for atomic material deductions
   - Use PermissionManager (only supervisors/managers can approve)
4. **Routes**:
   - GET /work-logs, POST /work-logs, GET /work-logs/{id}, PUT /work-logs/{id}, DELETE /work-logs/{id}
   - POST /work-logs/{id}/submit — Submit for approval
   - POST /work-logs/{id}/approve — Approve (manager only)
   - POST /work-logs/{id}/reject — Reject (manager only)
   - POST /work-logs/{id}/materials — Add material usage
   - GET /work-logs/{id}/materials — Material usage details

---

### 6. Workers & Teams Features

**Current Project**: `app/Features/Workers/`, `app/Features/Teams/`  
**Splnet Reference**: (Not separate controllers; referenced in task assignment)

#### Current Project Database Schema Context
- `workers` table: id, user_id (unique), team_id (nullable), timestamps, soft delete
- `teams` table: id, sector_id, name, timestamps, soft delete
- `sectors` table: id, name, head_id (user), timestamps, soft delete
- `mini_tasks_workers_teams` junction: mini_task_id, worker_id XOR team_id

#### Adaptation Strategy
1. **Workers Feature** → `app/Features/Workers/Controllers/WorkerController.php`
   - Methods: index(), store() (assign user to worker), show(), update(), destroy()
   - Manage team assignments for workers
2. **Teams Feature** → `app/Features/Teams/Controllers/TeamController.php`
   - Methods: index(), store(), show(), update(), destroy()
   - Add/remove workers from team
3. **Sectors Feature** → `app/Features/Sectors/Controllers/SectorController.php`
   - Methods: index(), store(), show(), update(), destroy()
   - Assign sector head (manager user)
4. **Models**: Worker, Team, Sector
5. **Routes**:
   - GET /workers, POST /workers, GET /workers/{id}, PUT /workers/{id}, DELETE /workers/{id}
   - GET /teams, POST /teams, GET /teams/{id}, PUT /teams/{id}, DELETE /teams/{id}
   - POST /teams/{id}/workers/{workerId} — Add worker to team
   - DELETE /teams/{id}/workers/{workerId} — Remove from team
   - GET /sectors, POST /sectors, GET /sectors/{id}, PUT /sectors/{id}, DELETE /sectors/{id}

---

### 7. Locations Feature

**Current Project**: `app/Features/Locations/`  
**Splnet Reference**: `app/Http/Controllers/LocationController.php`

#### Splnet Implementation
- `index()` — List with city filter
- `store()` — Create location
- `show()` — Get with relations
- `update()` — Modify location
- `destroy()` — Soft-delete
- `restore()` — Restore deleted
- `serviceOrders()` — Orders at location
- `clients()` — Clients at location

**Current Project Database Schema Context**
- Geographic hierarchy: `districts` → `municipalities` → `parishes` → `locations`
- `locations` table: id, parish_id, postal_code, street_address, landmark, latitude, longitude, timestamps, soft delete
- Relationships: service_orders (1:M), clients (through service orders)

#### Adaptation Strategy
1. **LocationController** → `app/Features/Locations/Controllers/LocationController.php`
2. **Models**: Location, Parish, Municipality, District
3. **Services**: GeographicService (location hierarchy helpers)
4. **Routes**:
   - GET /locations, POST /locations, GET /locations/{id}, PUT /locations/{id}, DELETE /locations/{id}
   - GET /districts, POST /districts
   - GET /municipalities?district_id=, POST /municipalities
   - GET /parishes?municipality_id=, POST /parishes
   - GET /locations/{id}/service-orders, GET /locations/{id}/clients

---

### 8. Materials Feature

**Current Project**: `app/Features/Materials/`  
**Splnet Reference**: (Minimal coverage; not separate controller)

#### Current Project Database Schema Context
- `units` table: id, name, abbreviation (unique), timestamps, soft delete
- `materials` table: id, name, unit_id, stock_quantity (decimal with >= 0 check), timestamps, soft delete
- `mini_tasks_materials` junction: mini_task_id, material_id, planned_quantity
- `work_logs_materials` junction: work_log_id, material_id, quantity_used, unit_price_at_use

#### Adaptation Strategy
1. **MaterialController** → `app/Features/Materials/Controllers/MaterialController.php`
   - CRUD operations for materials and units
2. **Models**: Material, Unit
3. **Services**: InventoryService (track stock, usage, pricing)
4. **Routes**:
   - GET /materials, POST /materials, GET /materials/{id}, PUT /materials/{id}, DELETE /materials/{id}
   - GET /units, POST /units
   - GET /materials/{id}/usage-history — Track usage over time

---

### 9. Additional Features

**ServiceTypes, Notifications, Settings, Export, Admin**

These have minimal implementation in splnet/backend or specific context in current schema:

1. **ServiceTypes** — Simple CRUD (`ServiceTypeController`)
2. **Notifications** — Event-driven (create when events occur)
3. **Settings** — User preferences + app settings
4. **Export** — CSV export of all entities (use ExportCsv trait)
5. **Admin** — Administrative operations, user management, role assignment

---

## 🔄 Reusable Code Patterns from splnet/backend

### 1. Query Filtering Pattern
```
✓ Use Filterable trait (already implemented)
✓ Apply FilterService for complex queries
✓ Authorization checks before filtering
```

### 2. Resource Response Pattern
```
✓ Consistent JSON structure (data, meta, errors)
✓ Include relations in show() responses
✓ Pagination on list endpoints
```

### 3. Authorization Pattern
```
✓ Use PermissionManager for RBAC
✓ Use Policies for complex authorization
✓ Check ownership before self-updates
```

### 4. Soft Delete Pattern
```
✓ All models use soft deletes
✓ Provide restore endpoints
✓ Include deleted_at in queries appropriately
```

### 5. Error Handling Pattern
```
✓ Centralize in ValidationHelper & handlers
✓ Consistent error responses
✓ Validation before processing
```

---

## ⚠️ Security Issues Found in splnet/backend (To Avoid)

1. **forgotPassword** — No null check on token, exposes in response
   - **Fix**: Validate email exists, use secure token delivery (email link)
   
2. **ExportController** — No export size validation
   - **Fix**: Implement size limits, pagination for exports
   
3. **ExportController** — Manager can export all clients
   - **Fix**: Enforce ownership (manager sees only own clients) via PermissionManager
   
4. **UserController update()** — No ownership check
   - **Fix**: Verify self-update or admin privilege
   
5. **N+1 Queries** — Missing eager loading
   - **Fix**: Use eager loading in ServiceOrderController, ExportController

---

## 🎯 Implementation Priority Order

Based on dependencies and complexity:

1. **Phase 1 (Core Infrastructure)**
   - ✅ Already done: Enums, Traits, Services, Helpers
   - → Implement: All 20+ Models (start with User relations)

2. **Phase 2 (Authentication & Authorization)**
   - → Authentication feature (register, login, token generation)
   - → User feature (profiles, roles, permissions)
   - → Settings feature (preferences)

3. **Phase 3 (Master Data)**
   - → ServiceTypes feature
   - → Locations feature (geographic hierarchy)
   - → Materials feature (units, stock)

4. **Phase 4 (Clients & Service Orders)**
   - → Clients feature
   - → ServiceOrders feature (main aggregate)
   - → Attachments feature (documents)

5. **Phase 5 (Work Execution)**
   - → Workers, Teams, Sectors features
   - → Tasks feature
   - → MiniTasks feature
   - → WorkLogs feature (time tracking)

6. **Phase 6 (Supporting Features)**
   - → Notifications feature
   - → Export feature
   - → Admin feature

---

## 📝 Code Philosophy Integration

**Current Project Philosophy** (from memory):
- **Minimalismo**: Avoid deeply nested code, use early exits
- **DRY**: Never duplicate logic, extract to reusable functions
- **Centralized Error Handling**: Validations in helpers/middleware, not scattered in controllers
- **Smart Naming**: `Verbo + Substantivo` for functions, clear state names

**Apply to Adaptations**:
1. Controllers focus on orchestration, delegate to Services
2. Services contain business logic, use helpers for validation/formatting
3. Models define relationships and basic queries
4. Traits provide reusable behaviors (Filterable, ExportCsv, Completable)
5. Middleware handles cross-cutting concerns (auth, logging)
6. Enums centralize domain concepts

---

