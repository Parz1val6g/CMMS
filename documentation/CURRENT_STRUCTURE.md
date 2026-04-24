# Current Project Structure

**Project**: Service Management Backend (Laravel 12)  
**Status**: Skeleton/Template Phase  
**Database**: 27 tables with comprehensive relationships  
**Architecture**: Modular feature-based design (16 features)

---

## 📊 Project Overview

### Database Schema (from `db_tables.sql`)

#### Core Entity Tables

**Users & Authentication**
- `users` — Base user records (first_name, last_name, phone, email, password, status)
  - Unique constraints: phone, email
  - Indexes: email, phone, status
  - Soft deletes enabled

**Roles & Permissions**
- `roles` — Role definitions (name, columns for display)
  - Soft deletes enabled
  - Tracks role-specific visible columns
- `role_permissions` — Permission definitions (resource, action, description)
  - Unique constraint: role_id + resource + action
  - Foreign key to roles
  - Indexes: role_id
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
  - Indexes: parish_id

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
  - Indexes: manager, client, location, service_type, status, priority, created_at
  - Composite index: status + created_at
  - Soft deletes enabled

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
  - Indexes: service_order_id, manager, status
  - Composite index: service_order_id + status
  - Soft deletes enabled
- `tasks_sectors` — Task-to-sector assignments (junction table)
  - Foreign keys: tasks, sectors
  - Indexes: task_id, sector_id

**Mini-Tasks**
- `mini_tasks` — Subtasks under tasks (description, status)
  - Foreign keys: tasks, supervisor (users)
  - Indexes: task_id, supervisor, status
  - Composite index: task_id + status
  - Soft deletes enabled
- `mini_tasks_workers_teams` — Assign mini-tasks to workers or teams (mutually exclusive)
  - Unique assignment per mini_task
  - Check constraint: worker_id XOR team_id (one must be null)
  - Foreign keys: mini_tasks, workers, teams
  - Indexes: mini_task_id, worker_id, team_id

**Work Logs** (Time tracking)
- `work_logs` — Completed work records (started_at, completed_at, description)
  - Foreign key: mini_tasks
  - Generated column: `duration_minutes` (auto-calculated from time diff)
  - Check constraint: completed_at > started_at
  - Indexes: mini_task_id, created_at
  - Soft deletes enabled
- `work_logs_workers` — Workers assigned to work logs (junction table)
  - Foreign keys: work_logs, workers
  - Indexes: work_log_id, worker_id

#### Materials Management

**Materials & Units**
- `units` — Measurement units (name, abbreviation)
  - Unique constraint: abbreviation
- `materials` — Materials (name, unit_id, stock_quantity)
  - Foreign key to units
  - Decimal field: stock_quantity with check >= 0
  - Indexes: unit_id
- `work_logs_materials` — Materials used in work logs (quantity_used, unit_price_at_use)
  - Unique constraint: work_log_id + material_id
  - Foreign keys: work_logs, materials
  - Check constraint: quantity_used > 0
  - Indexes: work_log_id, material_id
- `mini_tasks_materials` — Planned materials for mini-tasks (planned_quantity)
  - Unique constraint: mini_task_id + material_id
  - Foreign keys: mini_tasks, materials

**Attachments**
- `attachments` — File uploads (file_path, file_name, mime_type)
  - Polymorphic: belongs to either service_orders OR mini_tasks (mutually exclusive)
  - Check constraint: service_order_id XOR mini_task_id
  - Indexes: service_order_id, mini_task_id
  - Soft deletes enabled

---

## 🏗️ Current Infrastructure (Implemented)

### Enums (`app/Core/Enums/`)

1. **UserRole.php** — User role types
   - Values: admin, manager, pending, supervisor, worker
   - Methods: label()

2. **TaskStatus.php** — Task workflow states
   - Values: pending, in_progress, completed, blocked, cancelled
   - Methods: label(), isOpen(), isClosed()

3. **WorkLogStatus.php** — Work log states
   - Values: draft, submitted, approved, rejected
   - Methods: label()

4. **MiniTaskStatus.php** — Mini-task states
   - Values: pending, in_progress, completed, blocked, cancelled
   - Methods: label(), isOpen(), isClosed()

5. **ServicesOrdersPriority.php** — Priority levels
   - Values: urgent, high, normal, low
   - Methods: label(), weight(), isHighPriority()

6. **PermissionAction.php** — RBAC actions
   - Values: view, create, update, delete, change_role, export, import, restore, force_delete

7. **PermissionResource.php** — RBAC resources
   - Values: users, clients, locations, service_orders, service_types, sessions, login_histories, tasks, mini_tasks, work_logs, sectors, teams, workers, materials, role_permissions, profile, settings

8. **SystemStatus.php** — System entity status
   - Values: active, inactive, suspended, archived
   - Methods: label(), isActive()

### Traits (`app/Core/Traits/`)

1. **Base.php** — Foundation trait (UUIDs, timestamps)
   - UUID primary keys
   - Timestamps (created_at, updated_at)

2. **Timestamped.php** — Additional timestamp fields
   - Extends timestamp behavior

3. **Publishing.php** — Publish/unpublish logic
   - Published_at field
   - Scope: published(), unpublished()

4. **Filterable.php** — Dynamic filtering
   - Scope: filter()
   - Supports column-based filtering

5. **ExportCsv.php** — CSV export capability
   - Method: toCSV()
   - Handles model serialization

6. **Completable.php** — Completion tracking
   - Completed_at field
   - Methods: markComplete(), isComplete()

### Services (`app/Core/Services/`)

1. **PermissionManager.php** — RBAC engine
   - Manages permissions per role
   - Checks: hasPermission(), canPerform()

2. **CacheManager.php** — Caching orchestration
   - Cache invalidation on changes
   - Methods: cache(), invalidate(), remember()

3. **FilterService.php** — Query filtering
   - Dynamic filter application
   - Scope builders

4. **TransactionHandler.php** — Database transactions
   - Atomic operations
   - Rollback on error

### Helpers (`app/Core/Helpers/`)

1. **ValidationHelper.php** — Validation utilities
   - Common validation rules
   - Custom validators

2. **InputSanitizer.php** — Input sanitization
   - XSS prevention
   - Trim, filter functions

3. **FormattingHelper.php** — Data formatting
   - Date formatting
   - Number formatting
   - String manipulation

4. **FeatureFlags.php** — Feature toggles
   - Enable/disable features
   - A/B testing support

### Middleware (`app/Core/Middleware/`)

1. **AuthenticateApi.php** — API authentication
   - Bearer token validation
   - Request authentication

2. **CheckSoftDeletedUser.php** — User state check
   - Verify user not soft-deleted
   - Account status validation

3. **EnsureEmailVerified.php** — Email verification requirement
   - Redirect/reject unverified users

4. **SetUserLocale.php** — Localization setup
   - Set language per user preference
   - Fallback to app default

### Policies (`app/Core/Policies/`)

1. **BasePolicy.php** — Authorization base class
   - Common authorization logic
   - Action authorization patterns

---

## 📦 Models (Status)

### Implemented
- ✅ **User.php** — Full implementation with relations (roles, preferences, sessions, login histories)

### Defined in Migrations (Not Yet Implemented)
- ⏳ Role
- ⏳ RolePermission
- ⏳ UserRole (pivot)
- ⏳ UserPreference
- ⏳ AppSetting
- ⏳ District
- ⏳ Municipality
- ⏳ Parish
- ⏳ Location
- ⏳ Client
- ⏳ ServiceType
- ⏳ ServiceOrder
- ⏳ Sector
- ⏳ Team
- ⏳ Worker
- ⏳ Task
- ⏳ MiniTask
- ⏳ WorkLog
- ⏳ Material
- ⏳ Unit
- ⏳ Attachment

---

## 🎯 Features (16 Modular Features - All Skeleton)

Each feature folder in `app/Features/{FeatureName}/` contains:
- `Controllers/` — Feature controllers (empty)
- `Services/` — Feature business logic (empty)
- `Models/` — Feature models (empty)
- `Routes/` — Feature-specific routes (skeleton)
- `Factories/` — Seeding factories (empty)
- `Tests/` — Feature tests (empty)

**Features:**
1. ✅ **Admin** — Administrative operations
2. ✅ **Authentication** — Auth flows (login, register, password reset)
3. ✅ **Clients** — Client management
4. ✅ **Export** — Data export (CSV, PDF)
5. ✅ **Locations** — Geographic locations (districts, municipalities, parishes)
6. ✅ **Materials** — Material/inventory management
7. ✅ **MiniTasks** — Subtask management
8. ✅ **Notifications** — User notifications
9. ✅ **Sectors** — Organizational sectors
10. ✅ **ServiceOrders** — Main service order management
11. ✅ **ServiceTypes** — Service type definitions
12. ✅ **Settings** — User & app settings
13. ✅ **Tasks** — Task management
14. ✅ **Teams** — Team management
15. ✅ **Workers** — Worker/employee management
16. ✅ **WorkLogs** — Time tracking & work logging

---

## 🗂️ Project File Statistics

| Category | Count | Status |
|----------|-------|--------|
| PHP Infrastructure Files | ~50 | ✅ Implemented |
| Enums | 8 | ✅ Implemented |
| Traits | 6 | ✅ Implemented |
| Services | 4 | ✅ Implemented |
| Helpers | 4 | ✅ Implemented |
| Middleware | 4 | ✅ Implemented |
| Policies | 1 | ✅ Implemented |
| Models (Implemented) | 1 | ✅ Implemented |
| Models (Needed) | 19+ | ⏳ Pending |
| Migrations | 25 | ✅ Defined |
| Controllers | 16 | ⏳ Empty stubs |
| Routes | 16 | ⏳ Skeleton |
| Tests | 0 | ⏳ Pending |
| **Total** | **~150+** | **Mixed** |

---

## 🔗 Key Relationships Overview

```
Users
├── Roles (M:M via user_roles)
├── UserPreferences (1:M)
├── UserRoles (1:M)
├── Clients (1:M as manager)
├── ServiceOrders (1:M as manager)
├── Sectors (1:M as head)
├── Tasks (1:M as manager)
├── MiniTasks (1:M as supervisor)
├── Workers (1:1 via workers.user_id)
└── Sessions (1:M)

ServiceOrder (Central aggregate)
├── Client (M:1)
├── Location (M:1)
├── ServiceType (M:1)
├── Manager/User (M:1)
├── Tasks (1:M)
├── Attachments (1:M)
└── WorkLogs (through Tasks→MiniTasks→WorkLogs)

Task
├── ServiceOrder (M:1)
├── Manager/User (M:1)
├── MiniTasks (1:M)
├── TaskSectors (M:M via tasks_sectors)
└── WorkLogs (through MiniTasks)

MiniTask
├── Task (M:1)
├── Supervisor/User (M:1)
├── WorkLogs (1:M)
├── Materials (M:M via mini_tasks_materials)
├── WorkersTeams (M:M via mini_tasks_workers_teams)
└── Attachments (1:M)

WorkLog (Time tracking)
├── MiniTask (M:1)
├── Materials (M:M via work_logs_materials)
├── Workers (M:M via work_logs_workers)
├── Attachments (through MiniTask)
└── Timestamps (auto-calculated duration)

Materials
├── Unit (M:1)
├── WorkLogsMaterials (1:M)
└── MiniTasksMaterials (1:M)

Geographic Hierarchy
├── District (1:M)
│   └── Municipality (1:M)
│       └── Parish (1:M)
│           └── Location (1:M)
```

---

## 📋 Database Configuration Details

- **Primary Key Type**: UUID (VARCHAR 36)
- **Timestamps**: TIMESTAMP with CURRENT_TIMESTAMP and ON UPDATE
- **Soft Deletes**: All main entities have `deleted_at` column
- **Constraints**: Foreign keys, unique constraints, check constraints
- **Indexes**: Strategic indexes on foreign keys, status fields, created_at, and common filters
- **Collation**: Default (likely utf8mb4_unicode_ci for MySQL)

---

## 📝 Notes

- **Philosophy**: Modular feature-based architecture with centralized infrastructure
- **Database-First**: Schema defined via migrations, models to follow
- **RBAC**: Role-based access control with resource-action pairs
- **Soft Deletes**: Non-destructive deletion pattern
- **Timestamps**: All entities tracked for audit purposes
- **Relationships**: Complex M:M relationships for flexibility (workers/teams assigned to tasks)

