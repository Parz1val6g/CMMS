# Implementation Roadmap

**Objective**: Complete backend to fully working state by implementing all models, controllers, routes, and features based on database schema and splnet/backend reference.

**Approach**: Feature-by-feature, respecting modular architecture, applying code philosophy (Minimalismo, DRY, centralized error handling).

---

## 🗺️ Phased Implementation Plan

### PHASE 1: Core Models & Infrastructure Completion

**Duration**: ~1 week  
**Priority**: 🔴 CRITICAL (blocks all other phases)

#### 1.1 Implement Core Models

**Models to create** (in order of dependency):

```
1. ✅ User.php — Already exists
   Ensure all relationships are defined:
   ├── roles() → M:M via user_roles
   ├── preferences() → 1:M
   ├── sessions() → 1:M
   └── loginHistories() → 1:M

2. → Role.php
   Relationships:
   ├── permissions() → 1:M (role_permissions)
   └── users() → M:M via user_roles

3. → RolePermission.php
   ├── role() → M:1
   └── Composite unique: (role_id, resource, action)

4. → UserRole.php (Pivot)
   Just a pivot model linking users and roles

5. → UserPreference.php
   ├── user() → M:1
   └── Unique: (user_id, key)

6. → AppSetting.php
   Key-value store with sections
   └── Unique: (key, section)

7. → Unit.php
   Measurement units (kg, liters, boxes, etc.)
   └── Unique: abbreviation

8. → Material.php
   ├── unit() → M:1
   ├── workLogUsages() → 1:M (work_logs_materials)
   └── miniTaskPlannings() → 1:M (mini_tasks_materials)

9. → District.php
   ├── municipalities() → 1:M

10. → Municipality.php
    ├── district() → M:1
    └── parishes() → 1:M

11. → Parish.php
    ├── municipality() → M:1
    └── locations() → 1:M

12. → Location.php
    ├── parish() → M:1
    ├── serviceOrders() → 1:M
    └── clients() → M:M through service_orders

13. → ServiceType.php
    └── serviceOrders() → 1:M

14. → Sector.php
    ├── head() → User M:1
    └── teams() → 1:M

15. → Team.php
    ├── sector() → M:1
    ├── workers() → 1:M
    └── miniTaskAssignments() → 1:M

16. → Worker.php
    ├── user() → M:1 unique
    ├── team() → M:1 nullable
    └── miniTaskAssignments() → 1:M

17. → Client.php
    ├── manager() → User M:1
    ├── serviceOrders() → 1:M
    └── locations() → M:M through service_orders

18. → ServiceOrder.php
    ├── client() → M:1 nullable
    ├── manager() → User M:1
    ├── location() → M:1
    ├── serviceType() → M:1 nullable
    ├── tasks() → 1:M
    └── attachments() → 1:M

19. → Task.php
    ├── serviceOrder() → M:1
    ├── manager() → User M:1
    ├── miniTasks() → 1:M
    ├── sectors() → M:M via tasks_sectors
    └── workLogs() → 1:M through miniTasks

20. → MiniTask.php
    ├── task() → M:1
    ├── supervisor() → User M:1
    ├── workLogs() → 1:M
    ├── materials() → M:M via mini_tasks_materials
    ├── assignedWorkers() → M:M via mini_tasks_workers_teams
    └── assignedTeams() → M:M via mini_tasks_workers_teams

21. → WorkLog.php (implements Completable)
    ├── miniTask() → M:1
    ├── materials() → M:M via work_logs_materials
    ├── workers() → M:M via work_logs_workers
    ├── durationMinutes() → Generated column
    └── status → enum: draft, submitted, approved, rejected

22. → Attachment.php (Polymorphic)
    Polymorphic belongs-to:
    ├── attachable (service_orders OR mini_tasks)
    └── Check constraint: one must be NOT NULL
```

**Deliverables**:
- ✅ 22 model files with all relationships, casts, scopes
- ✅ Each model uses appropriate Traits (Base, Timestamped, Publishing, Filterable, ExportCsv, Completable)
- ✅ Each model has methods for common operations
- ✅ Pivot models for M:M relationships

**Testing**:
- Run `php artisan tinker` to verify relationships load correctly
- No SQL errors on model instantiation

---

#### 1.2 Database Initialization

```
✅ php artisan migrate
   → Initializes all 25 migrations
   → Verifies schema matches db_tables.sql
   → Creates all 27 tables with proper indexes
```

**Verification**:
- All 27 tables created in database
- All foreign keys in place
- All indexes created
- No migration errors

---

### PHASE 2: Authentication & Authorization System

**Duration**: ~1 week  
**Depends on**: Phase 1 (Models)

#### 2.1 Implement Authentication Feature

**Feature Path**: `app/Features/Authentication/`

**Models**: Already in Phase 1 (User, Role, RolePermission, UserRole, UserPreference)

**Controllers**:
```
✅ AuthController
   - register(RegisterRequest) → Create user with worker/client profile
   - login(LoginRequest) → Authenticate, create token + session + login history
   - logout() → Destroy current session
   - logoutAll() → Destroy all user sessions
   - me() → Get current user + roles + permissions
   - refreshToken() → Generate new token
   - forgotPassword(ForgotPasswordRequest) → Secure password reset
   - resetPassword(ResetPasswordRequest) → Complete password reset via email link
```

**Services**:
```
✅ AuthenticationService
   ├── register($data): User
   ├── login($credentials): Token
   ├── logout(User): void
   ├── logoutAll(User): void
   ├── refreshToken(User): Token
   └── validateCredentials($email, $password): bool

✅ TokenService
   ├── generate(User): string
   ├── validate(string): User
   ├── revoke(User): void
   └── refreshExpired(User): string

✅ PermissionService (enhance PermissionManager)
   ├── hasPermission(User, resource, action): bool
   ├── can(User, action, resource): bool
   └── permissions(User): Collection
```

**Requests (Form Validation)**:
```
✅ RegisterRequest
   - first_name: required, string, max:250
   - last_name: required, string, max:250
   - email: required, email, unique:users
   - password: required, string, min:8, confirmed
   - phone: required, phone format

✅ LoginRequest
   - email: required, email
   - password: required, string

✅ ForgotPasswordRequest
   - email: required, email, exists:users

✅ ResetPasswordRequest
   - token: required
   - email: required, email, exists:users
   - password: required, string, min:8, confirmed
```

**Routes** (`routes/auth.php` or `api.php`):
```
POST   /auth/register
POST   /auth/login
POST   /auth/logout
POST   /auth/logout-all
GET    /auth/me (protected)
POST   /auth/refresh-token
POST   /auth/forgot-password
POST   /auth/reset-password
```

**Security Implementations**:
- ✅ Constant-time password comparison
- ✅ Secure token generation
- ✅ Password reset via email with signed URL
- ✅ Null checks on email before token generation
- ✅ Token expiration handling

**Tests**:
- Unit: AuthenticationService, TokenService
- Feature: All auth endpoints (register, login, logout, etc.)

---

#### 2.2 Implement Users & Roles Feature

**Feature Path**: `app/Features/Users/` (or extend Authentication)

**Controllers**:
```
✅ UserController
   - index() → List users (admin only)
   - show($id) → Get user profile
   - store(StoreUserRequest) → Create user (admin only)
   - update(UpdateUserRequest) → Update profile (self or admin)
   - destroy($id) → Soft delete (admin)
   - restore($id) → Restore (admin)
   - changeRole(ChangeRoleRequest) → Assign role (admin only)

✅ RoleController
   - index() → List roles
   - store(StoreRoleRequest) → Create role (admin only)
   - show($id) → Get role + permissions
   - update(UpdateRoleRequest) → Update role
   - destroy($id) → Delete role
   - addPermission(AddPermissionRequest) → Grant permission to role
   - removePermission($roleId, $permissionId) → Revoke permission
```

**Services**:
```
✅ UserService
   ├── create($data): User
   ├── update(User, $data): User
   ├── changeRole(User, Role): void
   └── delete(User): void

✅ RoleService
   ├── create($data): Role
   ├── update(Role, $data): Role
   ├── addPermission(Role, resource, action): void
   └── removePermission(Role, resource, action): void
```

**Routes**:
```
GET    /users (admin only)
POST   /users (admin only)
GET    /users/{id}
PUT    /users/{id}
DELETE /users/{id} (admin only)
POST   /users/{id}/restore (admin only)
POST   /users/{id}/change-role (admin only)

GET    /roles (admin only)
POST   /roles (admin only)
GET    /roles/{id}
PUT    /roles/{id}
DELETE /roles/{id}
POST   /roles/{id}/permissions (admin only)
DELETE /roles/{id}/permissions/{permissionId} (admin only)
```

---

#### 2.3 Implement Settings Feature

**Feature Path**: `app/Features/Settings/`

**Controllers**:
```
✅ SettingsController
   - getUserSettings() → Get user preferences
   - updateUserSettings(UpdateSettingsRequest) → Update preferences
   - getAppSettings() → Get system settings (admin only)
   - updateAppSettings(UpdateAppSettingsRequest) → Update system settings (admin only)
```

**Routes**:
```
GET    /settings/user
PUT    /settings/user
GET    /settings/app (admin only)
PUT    /settings/app (admin only)
```

---

### PHASE 3: Master Data Management

**Duration**: ~1 week  
**Depends on**: Phase 2 (auth works)

#### 3.1 Service Types Feature

**Controllers**:
```
✅ ServiceTypeController
   - index() → List (paginated)
   - store(StoreServiceTypeRequest) → Create
   - show($id) → Get
   - update(UpdateServiceTypeRequest) → Update
   - destroy($id) → Soft delete
   - restore($id) → Restore
```

**Routes**:
```
GET    /service-types
POST   /service-types (admin/manager)
GET    /service-types/{id}
PUT    /service-types/{id} (admin/manager)
DELETE /service-types/{id} (admin/manager)
POST   /service-types/{id}/restore (admin/manager)
```

---

#### 3.2 Locations Feature

**Controllers**:
```
✅ DistrictController
   - index() → List districts
   - store() → Create (admin)
   - etc.

✅ MunicipalityController
   - index(?district_id) → Filter by district
   - store() → Create (admin)
   - etc.

✅ ParishController
   - index(?municipality_id) → Filter
   - store() → Create (admin)
   - etc.

✅ LocationController
   - index(?parish_id, ?postal_code) → List, filter
   - store(StoreLocationRequest) → Create
   - show($id) → Get with relations
   - update(UpdateLocationRequest) → Update
   - destroy($id) → Soft delete
   - restore($id) → Restore
   - serviceOrders() → Orders at this location
   - clients() → Clients with locations here
```

**Routes**:
```
GET    /districts
POST   /districts (admin)

GET    /municipalities?district_id=
POST   /municipalities (admin)

GET    /parishes?municipality_id=
POST   /parishes (admin)

GET    /locations
POST   /locations
GET    /locations/{id}
PUT    /locations/{id}
DELETE /locations/{id}
POST   /locations/{id}/restore
GET    /locations/{id}/service-orders
GET    /locations/{id}/clients
```

---

#### 3.3 Materials Feature

**Controllers**:
```
✅ UnitController
   - index() → List units
   - store() → Create (admin)
   - show() → Get
   - update() → Update (admin)
   - destroy() → Delete (admin)

✅ MaterialController
   - index() → List (with stock)
   - store() → Create (admin)
   - show() → Get with stock history
   - update() → Update (admin)
   - destroy() → Soft delete (admin)
   - restore() → Restore
   - adjustStock(AdjustStockRequest) → Add/remove stock
```

**Routes**:
```
GET    /units
POST   /units (admin)
GET    /units/{id}
PUT    /units/{id} (admin)
DELETE /units/{id} (admin)

GET    /materials
POST   /materials (admin)
GET    /materials/{id}
PUT    /materials/{id} (admin)
DELETE /materials/{id} (admin)
POST   /materials/{id}/restore
POST   /materials/{id}/adjust-stock (admin)
```

---

### PHASE 4: Organization & Clients

**Duration**: ~1 week  
**Depends on**: Phase 3 (master data exists)

#### 4.1 Sectors & Teams Features

**Controllers**:
```
✅ SectorController
   - index() → List sectors
   - store(StoreSectorRequest) → Create (admin)
   - show($id) → Get with teams
   - update(UpdateSectorRequest) → Update (admin)
   - destroy($id) → Soft delete (admin)
   - restore($id) → Restore

✅ TeamController
   - index(?sector_id) → List, filter by sector
   - store(StoreTeamRequest) → Create
   - show($id) → Get with workers
   - update(UpdateTeamRequest) → Update
   - destroy($id) → Soft delete
   - restore($id) → Restore
   - addWorker(AddWorkerRequest) → Assign worker to team
   - removeWorker($workerId) → Remove worker from team
   - workers() → List team workers
```

**Routes**:
```
GET    /sectors
POST   /sectors (admin)
GET    /sectors/{id}
PUT    /sectors/{id} (admin)
DELETE /sectors/{id} (admin)
POST   /sectors/{id}/restore (admin)

GET    /teams?sector_id=
POST   /teams
GET    /teams/{id}
PUT    /teams/{id}
DELETE /teams/{id}
POST   /teams/{id}/restore
POST   /teams/{id}/workers (admin)
DELETE /teams/{id}/workers/{workerId} (admin)
GET    /teams/{id}/workers
```

---

#### 4.2 Workers Feature

**Controllers**:
```
✅ WorkerController
   - index() → List workers
   - store(StoreWorkerRequest) → Assign user as worker (admin)
   - show($id) → Get worker with team
   - update(UpdateWorkerRequest) → Update team assignment
   - destroy($id) → Unassign worker (admin)
   - restore($id) → Restore
```

**Routes**:
```
GET    /workers
POST   /workers (admin)
GET    /workers/{id}
PUT    /workers/{id}
DELETE /workers/{id} (admin)
POST   /workers/{id}/restore (admin)
```

---

#### 4.3 Clients Feature

**Controllers**:
```
✅ ClientController
   - index() → List (paginated, filterable)
   - store(StoreClientRequest) → Create client
   - show($id) → Get with relations
   - update(UpdateClientRequest) → Update
   - destroy($id) → Soft delete
   - restore($id) → Restore
   - serviceOrders($id) → Get client's service orders
   - locations($id) → Get locations associated with client
```

**Services**:
```
✅ ClientService
   ├── create($data): Client
   ├── update(Client, $data): Client
   ├── delete(Client): void
   └── serviceOrders(Client): Collection

✅ ClientFilterService
   ├── byName(), byNIF(), byStatus()
   └── Compose with FilterService
```

**Requests**:
```
✅ StoreClientRequest
   - nif: required, regex:/^\d{9}$/, unique:clients
   - user_id: required, exists:users
   - (other fields as needed)

✅ UpdateClientRequest
   - nif: sometimes, regex, unique except current
   - user_id: sometimes, exists:users
```

**Routes**:
```
GET    /clients
POST   /clients
GET    /clients/{id}
PUT    /clients/{id}
DELETE /clients/{id}
POST   /clients/{id}/restore
GET    /clients/{id}/service-orders
GET    /clients/{id}/locations
```

**Authorization**:
- Managers see only own clients (via PermissionManager)
- Admins see all clients

---

### PHASE 5: Service Orders & Work Execution

**Duration**: ~2 weeks  
**Depends on**: Phase 4 (clients, teams)

#### 5.1 Service Orders Feature

**Controllers**:
```
✅ ServiceOrderController
   - index() → List (complex filtering: priority, status, date range, service type)
   - store(StoreServiceOrderRequest) → Create order + auto create/link location
   - show($id) → Get with all relations
   - update(UpdateServiceOrderRequest) → Update order details
   - destroy($id) → Soft delete
   - restore($id) → Restore
   - changeStatus(ChangeStatusRequest) → Status transition (pending→active→completed)
   - tasks($id) → Get associated tasks
   - attachments($id) → Get associated attachments
```

**Services**:
```
✅ ServiceOrderService
   ├── create($data): ServiceOrder
   ├── update(ServiceOrder, $data): ServiceOrder
   ├── changeStatus(ServiceOrder, newStatus): void
   ├── delete(ServiceOrder): void
   └── getTasks(ServiceOrder): Collection

✅ ServiceOrderFilterService (extends FilterService)
   ├── byPriority(priority)
   ├── byStatus(status)
   ├── byServiceType(service_type_id)
   ├── byManager(manager_id)
   ├── byClient(client_id)
   ├── byDateRange(start_date, end_date)
   └── compose(): Builder
```

**Models**:
```
✅ ServiceOrder.php
   - Use Filterable trait
   - Status field with enum
   - Priority field with enum
   - Relationships: client, manager, location, serviceType, tasks, attachments
   - Scopes: active(), completed(), pending(), overdue()
```

**Requests**:
```
✅ StoreServiceOrderRequest
   - process: required, string, max:250
   - client_id: required, exists:clients
   - manager_id: required, exists:users (manager role check)
   - location_id: exists:locations OR create new location inline
   - service_type_id: exists:service_types
   - priority: required, in:urgent,high,normal,low
   - execution_date: required, date, >= today

✅ UpdateServiceOrderRequest
   - (similar fields, some optional)

✅ ChangeStatusRequest
   - status: required, in:pending,active,completed,cancelled
   - (validation: only allowed transitions)
```

**Routes**:
```
GET    /service-orders?priority=urgent&status=pending&manager_id=
POST   /service-orders
GET    /service-orders/{id}
PUT    /service-orders/{id}
DELETE /service-orders/{id}
POST   /service-orders/{id}/restore
POST   /service-orders/{id}/change-status
GET    /service-orders/{id}/tasks
GET    /service-orders/{id}/attachments
```

**Authorization**:
- Managers can create/update own service orders
- Managers can view own service orders (& team's)
- Admins see all service orders
- Use PermissionManager + BasePolicy

---

#### 5.2 Tasks Feature

**Controllers**:
```
✅ TaskController
   - index() → List (filter by service_order, status, manager)
   - store(StoreTaskRequest) → Create task
   - show($id) → Get with mini-tasks, sectors
   - update(UpdateTaskRequest) → Update task
   - destroy($id) → Soft delete
   - restore($id) → Restore
   - changeStatus(ChangeStatusRequest) → Status transition
   - addSector(AddSectorRequest) → Assign sector to task
   - removeSector($sectorId) → Remove sector assignment
   - miniTasks($id) → Get sub-tasks
```

**Services**:
```
✅ TaskService
   ├── create($data): Task
   ├── update(Task, $data): Task
   ├── changeStatus(Task, status): void
   ├── addSector(Task, Sector): void
   └── removeSector(Task, Sector): void
```

**Models**:
```
✅ Task.php
   - Relationships: serviceOrder, manager, miniTasks, sectors (M:M)
   - Scopes: byServiceOrder(), byStatus(), byManager()
   - Use Completable trait
   - Status enum (pending, in_progress, completed, blocked, cancelled)
```

**Routes**:
```
GET    /tasks?service_order_id=&status=&manager_id=
POST   /tasks
GET    /tasks/{id}
PUT    /tasks/{id}
DELETE /tasks/{id}
POST   /tasks/{id}/restore
POST   /tasks/{id}/change-status
POST   /tasks/{id}/sectors (assign sector)
DELETE /tasks/{id}/sectors/{sectorId}
GET    /tasks/{id}/mini-tasks
```

---

#### 5.3 MiniTasks Feature

**Controllers**:
```
✅ MiniTaskController
   - index() → List (filter by task, status, supervisor)
   - store(StoreMiniTaskRequest) → Create mini-task
   - show($id) → Get with assignments, materials, work logs
   - update(UpdateMiniTaskRequest) → Update
   - destroy($id) → Soft delete
   - restore($id) → Restore
   - changeStatus(ChangeStatusRequest) → Status transition
   - assignWorker(AssignWorkerRequest) → Assign worker
   - assignTeam(AssignTeamRequest) → Assign team
   - removeAssignment($assignmentId) → Remove assignment
   - addMaterial(AddMaterialRequest) → Add planned material
   - removeMaterial($materialId) → Remove planned material
   - workLogs($id) → Get work logs
```

**Services**:
```
✅ MiniTaskService
   ├── create($data): MiniTask
   ├── update(MiniTask, $data): MiniTask
   ├── changeStatus(MiniTask, status): void
   └── delete(MiniTask): void

✅ MiniTaskAssignmentService
   ├── assignWorker(MiniTask, Worker): void
   ├── assignTeam(MiniTask, Team): void
   ├── removeAssignment(MiniTask, $assignmentId): void
   └── getAssignments(MiniTask): Collection

✅ MiniTaskMaterialService
   ├── addPlannedMaterial(MiniTask, Material, quantity): void
   ├── removePlannedMaterial(MiniTask, Material): void
   └── getMaterials(MiniTask): Collection
```

**Models**:
```
✅ MiniTask.php
   - Relationships: task, supervisor, workLogs, materials (M:M), assignedWorkers (M:M), assignedTeams (M:M)
   - Status enum with methods: isOpen(), isClosed()
   - Scopes: byTask(), byStatus(), bySupervisor()
   - Use Completable trait

✅ MiniTaskMaterial.php (Pivot with extra columns)
   - Planned quantity tracking
   - Compare vs actual usage in work logs

✅ MiniTaskWorkerTeam.php (Polymorphic pivot)
   - Check constraint: worker XOR team
   - Cannot have both assigned
```

**Routes**:
```
GET    /mini-tasks?task_id=&status=&supervisor_id=
POST   /mini-tasks
GET    /mini-tasks/{id}
PUT    /mini-tasks/{id}
DELETE /mini-tasks/{id}
POST   /mini-tasks/{id}/restore
POST   /mini-tasks/{id}/change-status
POST   /mini-tasks/{id}/assign-worker
POST   /mini-tasks/{id}/assign-team
DELETE /mini-tasks/{id}/assignments/{assignmentId}
POST   /mini-tasks/{id}/materials
DELETE /mini-tasks/{id}/materials/{materialId}
GET    /mini-tasks/{id}/work-logs
```

---

#### 5.4 Work Logs Feature

**Controllers**:
```
✅ WorkLogController
   - index() → List (filter by mini-task, status, date range, worker)
   - store(StoreWorkLogRequest) → Create work log (draft)
   - show($id) → Get with materials, workers
   - update(UpdateWorkLogRequest) → Update (only draft status)
   - destroy($id) → Soft delete
   - restore($id) → Restore
   - submit(SubmitWorkLogRequest) → Move to submitted status
   - approve(ApproveWorkLogRequest) → Manager approval → submitted to approved
   - reject(RejectWorkLogRequest) → Rejection → submitted to draft
   - addMaterial(AddMaterialRequest) → Record material usage
   - removeMaterial($materialId) → Remove material entry
   - addWorker(AddWorkerRequest) → Assign worker to work log
   - removeWorker($workerId) → Remove worker
```

**Services**:
```
✅ WorkLogService
   ├── create($data): WorkLog (draft)
   ├── update(WorkLog, $data): WorkLog
   ├── submit(WorkLog): void
   ├── approve(WorkLog): void
   ├── reject(WorkLog, reason): void
   ├── delete(WorkLog): void
   ├── getDurationMinutes(WorkLog): int (auto-calculated)
   └── getStatus(WorkLog): string

✅ WorkLogMaterialService
   ├── addMaterialUsage(WorkLog, Material, quantity, unitPrice): void
   ├── removeMaterialUsage(WorkLog, Material): void
   ├── getMaterials(WorkLog): Collection
   ├── compareToPlanned(WorkLog): array (planned vs actual)
   └── calculateCost(WorkLog): decimal

✅ WorkLogWorkerService
   ├── assignWorker(WorkLog, Worker): void
   ├── removeWorker(WorkLog, Worker): void
   └── getWorkers(WorkLog): Collection

✅ InventoryService (new or extend MaterialService)
   ├── deductUsage(Material, quantity): void
   ├── adjustStock(Material, quantity, reason): void
   └── getStockHistory(Material): Collection
```

**Models**:
```
✅ WorkLog.php
   - Status enum: draft, submitted, approved, rejected (WorkLogStatus)
   - Relationships: miniTask, materials (M:M via work_logs_materials), workers (M:M via work_logs_workers)
   - Generated column: duration_minutes (TIMESTAMPDIFF between completed_at - started_at)
   - Scopes: byStatus(), byDateRange(), byWorker()
   - Methods: submit(), approve(), reject(), isApproved(), isDraft()
   - Timestamps: started_at, completed_at (with validation: completed_at > started_at)

✅ WorkLogMaterial.php (Pivot with extra columns)
   - quantity_used: decimal (required, > 0)
   - unit_price_at_use: decimal (nullable, for cost calculation)

✅ WorkLogWorker.php (Simple pivot)
   - Links workers who participated in work log
```

**Requests**:
```
✅ StoreWorkLogRequest
   - mini_task_id: required, exists:mini_tasks
   - started_at: required, datetime, <= now
   - completed_at: required, datetime, > started_at
   - description: required, string, max:250

✅ SubmitWorkLogRequest
   - (work log must be in draft status)

✅ ApproveWorkLogRequest
   - (manager only, work log must be submitted)
   - notes: optional, string

✅ RejectWorkLogRequest
   - (manager only, work log must be submitted)
   - reason: required, string

✅ AddMaterialRequest
   - material_id: required, exists:materials
   - quantity_used: required, decimal, > 0
   - unit_price_at_use: decimal, nullable

✅ AddWorkerRequest
   - worker_id: required, exists:workers
```

**Routes**:
```
GET    /work-logs?mini_task_id=&status=&date_from=&date_to=&worker_id=
POST   /work-logs
GET    /work-logs/{id}
PUT    /work-logs/{id} (only draft status)
DELETE /work-logs/{id}
POST   /work-logs/{id}/restore
POST   /work-logs/{id}/submit
POST   /work-logs/{id}/approve (manager only)
POST   /work-logs/{id}/reject (manager only)
POST   /work-logs/{id}/materials
DELETE /work-logs/{id}/materials/{materialId}
POST   /work-logs/{id}/workers
DELETE /work-logs/{id}/workers/{workerId}
```

**Authorization**:
- Workers can create/update own work logs (draft)
- Supervisors/managers can approve/reject
- Managers can view all work logs in their service orders
- Use PermissionManager + BasePolicy

---

#### 5.5 Attachments Feature

**Controllers**:
```
✅ AttachmentController
   - index() → List (filter by attachable: service_order or mini_task)
   - store(StoreAttachmentRequest) → Upload file
   - show($id) → Get attachment metadata
   - destroy($id) → Delete attachment file
   - download($id) → Download file
```

**Services**:
```
✅ FileUploadService (or FileStorageService)
   ├── upload(UploadedFile, $attachable_type, $attachable_id): Attachment
   ├── delete(Attachment): void
   ├── getUrl(Attachment): string
   └── validateFile(UploadedFile): bool

✅ AttachmentService
   ├── create(UploadedFile, ServiceOrder|MiniTask): Attachment
   ├── delete(Attachment): void
   └── getByAttachable(attachable): Collection
```

**Models**:
```
✅ Attachment.php
   - Polymorphic: belongs_to_morphable (service_orders OR mini_tasks)
   - Fields: file_path, file_name, mime_type
   - Methods: getDownloadUrl(), getPublicUrl()
   - Check constraint: one of service_order_id OR mini_task_id must be NOT NULL
```

**Routes**:
```
GET    /attachments?attachable_type=service_order&attachable_id={id}
POST   /attachments (multipart form)
GET    /attachments/{id}
GET    /attachments/{id}/download
DELETE /attachments/{id}
```

---

### PHASE 6: Additional Features & Completion

**Duration**: ~1 week  
**Depends on**: Phase 5 (work execution working)

#### 6.1 Notifications Feature

**Controllers**:
```
✅ NotificationController
   - index() → Get user's notifications
   - mark() → Mark as read
   - delete() → Delete notification
   - broadcast() → Admin: send notification to users (if real-time)
```

**Services**:
```
✅ NotificationService
   ├── notify(User, title, message, data): Notification
   ├── notifyMany(Collection users, ...): void
   ├── sendOnEvent(event, data): void
   └── markAsRead(Notification): void
```

**Events to trigger notifications**:
- ServiceOrder status changes
- Task assigned
- MiniTask completed
- WorkLog approved/rejected
- Material stock low

---

#### 6.2 Export Feature

**Controllers**:
```
✅ ExportController
   - clients() → Export clients to CSV
   - users() → Export users (admin only)
   - locations() → Export locations to CSV
   - serviceOrders() → Export service orders (with filters)
   - serviceTypes() → Export service types to CSV
   - materials() → Export materials with stock
   - workLogs() → Export work logs (date range)
```

**Services**:
```
✅ ExportService (or use ExportCsv trait)
   ├── export(Model, format='csv'): string
   ├── exportFiltered(Model, filters, format): string
   └── streamDownload(Model, format): StreamedResponse
```

**Implementation**:
- Use ExportCsv trait on models
- Implement dynamic export with FilterService
- Add size limits (max 10k records per export)
- Return CSV with proper headers
- Eager load relations to avoid N+1

---

#### 6.3 Admin Feature

**Controllers**:
```
✅ AdminDashboardController
   - stats() → Overall statistics
   - recentActivity() → Recent changes/events
   - systemHealth() → Database, cache, queue status

✅ AdminUserManagementController
   - (extends UserController with admin-only operations)

✅ AdminSettingsController
   - (extends SettingsController)
```

---

#### 6.4 Feature Tests & Cleanup

```
✅ Create comprehensive tests:
   - Unit tests for all Services
   - Feature tests for all Controllers
   - Integration tests for workflows

✅ Code quality:
   - Run phpstan for static analysis
   - Run php-cs-fixer for code style
   - Ensure all tests pass

✅ Documentation:
   - API documentation (OpenAPI/Swagger)
   - Database ER diagram
   - Deployment guide
```

---

## 📅 Timeline Summary

| Phase | Duration | Status | Key Deliverables |
|-------|----------|--------|------------------|
| 1: Core Models & DB | 1 week | ⏳ TODO | 22 models, migrations |
| 2: Authentication | 1 week | ⏳ TODO | Auth system, RBAC |
| 3: Master Data | 1 week | ⏳ TODO | Service types, locations, materials |
| 4: Organization | 1 week | ⏳ TODO | Sectors, teams, workers, clients |
| 5: Service Orders & Work | 2 weeks | ⏳ TODO | Core business logic |
| 6: Additional & Testing | 1 week | ⏳ TODO | Export, admin, notifications, tests |
| **Total** | **7 weeks** | | **Fully working backend** |

---

## ✅ Success Criteria

**When complete, the backend will have:**
- ✅ All 22 models implemented with relationships
- ✅ 16 features fully implemented with controllers, services, routes
- ✅ Working authentication & authorization system
- ✅ All 27 database tables initialized
- ✅ Comprehensive test coverage (70%+)
- ✅ API documentation
- ✅ Security: No known vulnerabilities (from splnet/backend reference fixed)
- ✅ Performance: Eager loading, proper indexing, optimized queries
- ✅ Code quality: PSR-12, Laravel conventions, philosophy applied

---

## 🎯 Implementation Notes

1. **Follow User's Philosophy**:
   - Minimalismo: Early exits, no deep nesting
   - DRY: Extract common logic to services/helpers
   - Centralized error handling: Validations in helpers/middleware
   - Smart naming: Verb + Noun for functions

2. **Use Modular Architecture**:
   - Each feature has Controllers, Services, Models, Routes, Tests
   - Share infrastructure (Traits, Helpers, Services)
   - No cross-feature dependencies (loosely coupled)

3. **Security First**:
   - Fix issues from splnet/backend
   - Validate all inputs
   - Check permissions before operations
   - Use soft deletes
   - Hash passwords
   - Secure token generation

4. **Database Integrity**:
   - Use transactions for multi-step operations
   - Respect foreign keys
   - Use check constraints
   - Maintain data consistency

5. **Testing**:
   - Unit tests for services
   - Feature tests for endpoints
   - Integration tests for workflows
   - Run tests frequently (CI/CD)

---

