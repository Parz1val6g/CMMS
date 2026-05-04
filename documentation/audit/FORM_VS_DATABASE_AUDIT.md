# Full-Stack Form vs. Database Schema Audit

> **Date:** 2026-05-04  
> **Scope:** All feature modules with CRUD forms  
> **Status:** STEP 1 — Report (no changes applied)

---

## 1. CLIENTS

### Database (`clients`)
| Column | Type | Constraints |
|--------|------|-------------|
| `id` | uuid | PK |
| `user_id` | uuid | FK → users, NOT NULL |
| `nif` | string(20) | UNIQUE, NOT NULL |
| `created_at` | timestamp | auto |
| `updated_at` | timestamp | auto |
| `deleted_at` | timestamp | soft-delete |

### Backend FormSchema (Create/Update)
`nif`, `first_name`, `last_name`, `email`, `phone`

### Backend Requests
- [`StoreClientRequest`](../../app/Features/Clients/Requests/StoreClientRequest.php:19): schema rules + `user_id` required|exists  
- [`UpdateClientRequest`](../../app/Features/Clients/Requests/UpdateClientRequest.php:20): schema rules + `user_id` sometimes + nif unique ignore

### Frontend (`resources/js/Features/Clients/Pages/Index.jsx`)
Receives `createFormSchema` + `formSchema` (update) from controller, rendered via Modal + DataManager.

### Discrepancies
| Issue | Severity | Detail |
|-------|----------|--------|
| `user_id` missing from form | ⚠️ Warning | Non-nullable FK. Request requires it, but no form input renders it. Assumed set programmatically. |
| `first_name`, `last_name`, `email`, `phone` are user fields, not client columns | ℹ️ Info | These create a User record alongside the Client — design choice. |

**Verdict: ⚠️ MINOR (user_id not in form, but expected to be set internally)**

---

## 2. LOCATIONS

### Database (`locations`)
| Column | Type | Constraints |
|--------|------|-------------|
| `id` | uuid | PK |
| `parish_id` | uuid | FK → parishes, NOT NULL |
| `postal_code` | string(8) | NOT NULL |
| `street_address` | string(100) | NOT NULL |
| `landmark` | string(100) | NOT NULL |
| `latitude` | decimal(10,8) | NULLABLE |
| `longitude` | decimal(10,8) | NULLABLE |

### Backend FormSchema (Create/Update)
`street_address`, `postal_code`, `parish_id` (select), `landmark`, `location` (map → lat/lng)

### Backend Requests
- [`StoreLocationRequest`](../../app/Features/Locations/Requests/StoreLocationRequest.php:19): from schema  
- [`UpdateLocationRequest`](../../app/Features/Locations/Requests/UpdateLocationRequest.php:19): from schema

### Discrepancies
**None.** All migration columns are represented:

| Migration column | Form field | Type match |
|---|---|---|
| `parish_id` | `parish_id` | Select (FK) ✅ |
| `postal_code` | `postal_code` | TextInput ✅ |
| `street_address` | `street_address` | TextInput ✅ |
| `landmark` | `landmark` | TextInput ✅ |
| `latitude` | MapInput → `latitude` (hidden) ✅ |
| `longitude` | MapInput → `longitude` (hidden) ✅ |

**Verdict: ✅ CLEAN**

---

## 3. MATERIALS

### Database (`materials`)
| Column | Type | Constraints |
|--------|------|-------------|
| `id` | uuid | PK |
| `name` | string(100) | NOT NULL |
| `unit_id` | uuid | FK → units, NOT NULL |
| `stock_quantity` | decimal(10,2) | default 0 |

### Backend FormSchema (Create/Update)
`name`, `unit_id` (select), `stock_quantity` (number)

### Backend Requests
- [`StoreMaterialRequest`](../../app/Features/Materials/Requests/StoreMaterialRequest.php:19): from schema  
- [`UpdateMaterialRequest`](../../app/Features/Materials/Requests/UpdateMaterialRequest.php:19): from schema

### Discrepancies
**None.** All columns present with correct types.

**Verdict: ✅ CLEAN**

---

## 4. SERVICE TYPES

### Database (`service_types`)
| Column | Type | Constraints |
|--------|------|-------------|
| `id` | uuid | PK |
| `name` | string(100) | NOT NULL |
| `description` | string(250) | NOT NULL |

### Backend FormSchema (Create/Update)
`name`, `description` (textarea)

### Backend Requests
- [`StoreServiceTypeRequest`](../../app/Features/ServiceTypes/Requests/StoreServiceTypeRequest.php:19): from schema  
- [`UpdateServiceTypeRequest`](../../app/Features/ServiceTypes/Requests/UpdateServiceTypeRequest.php:19): from schema

### Discrepancies
| Issue | Severity | Detail |
|-------|----------|--------|
| `description` nullable in form but NOT NULL in migration | 🔴 Error | Migration: `string('description', 250)` (NOT NULL). Schema rules: `nullable\|string\|max:250` |

**Verdict: 🔴 DISCREPANCY — `description` should be `required` instead of `nullable`**

---

## 5. SECTORS

### Database (`sectors`)
| Column | Type | Constraints |
|--------|------|-------------|
| `id` | uuid | PK |
| `name` | string(100) | NOT NULL |
| `head_id` | uuid | FK → users, NOT NULL |

### Backend FormSchema (Create/Update)
`name` (required), `head_id` (select, nullable)

### Backend Requests
- [`StoreSectorRequest`](../../app/Features/Sectors/Requests/StoreSectorRequest.php:19): from schema  
- [`UpdateSectorRequest`](../../app/Features/Sectors/Requests/UpdateSectorRequest.php:19): from schema

### Discrepancies
| Issue | Severity | Detail |
|-------|----------|--------|
| `head_id` nullable in form but NOT NULL in migration | 🔴 Error | Migration: `foreignUuid('head_id')->constrained('users')` (NOT NULL). Schema rules: `nullable\|exists:users,id` |

**Verdict: 🔴 DISCREPANCY — `head_id` should be `required` instead of `nullable`**

---

## 6. TEAMS

### Database (`teams`)
| Column | Type | Constraints |
|--------|------|-------------|
| `id` | uuid | PK |
| `sector_id` | uuid | FK → sectors, NOT NULL |
| `name` | string(100) | NOT NULL |

### Backend FormSchema (Create/Update)
`name`, `sector_id` (select, required)

### Backend Requests
- [`StoreTeamRequest`](../../app/Features/Teams/Requests/StoreTeamRequest.php:19): from schema  
- [`UpdateTeamRequest`](../../app/Features/Teams/Requests/UpdateTeamRequest.php:19): from schema

### Discrepancies
**None.** All columns present with correct types.

**Verdict: ✅ CLEAN**

---

## 7. WORKERS

### Database (`workers`)
| Column | Type | Constraints |
|--------|------|-------------|
| `id` | uuid | PK |
| `user_id` | uuid | FK → users, UNIQUE, NOT NULL |
| `team_id` | uuid | FK → teams, NULLABLE |

### Backend FormSchema (Create/Update)
`first_name`, `last_name`, `email`, `phone`, `team_id` (select)

### Backend Requests
- [`StoreWorkerRequest`](../../app/Features/Workers/Requests/StoreWorkerRequest.php:19): schema rules + `user_id` required|exists  
- [`UpdateWorkerRequest`](../../app/Features/Workers/Requests/UpdateWorkerRequest.php:19): schema rules + `user_id` sometimes|exists

### Discrepancies
| Issue | Severity | Detail |
|-------|----------|--------|
| `user_id` missing from form | ⚠️ Warning | Non-nullable unique FK. Request requires it internally. |
| `first_name`, `last_name`, `email`, `phone` are user fields | ℹ️ Info | Design choice — form creates User + Worker. |

**Verdict: ⚠️ MINOR (same pattern as Clients — user_id handled internally)**

---

## 8. SERVICE ORDERS

### Database (`service_orders`)
| Column | Type | Constraints |
|--------|------|-------------|
| `id` | uuid | PK |
| `process` | string(250) | NOT NULL |
| `client_id` | uuid | FK → clients, NULLABLE |
| `manager_id` | uuid | FK → users, NOT NULL |
| `location_id` | uuid | FK → locations, NOT NULL |
| `service_type_id` | uuid | FK → service_types, NULLABLE |
| `workflow_type` | string(50) | default 'regular' |
| `equipment_id` | uuid | FK → equipments, NULLABLE |
| `priority` | string(20) | NOT NULL |
| `execution_date` | date | NULLABLE |
| `status` | string(50) | NOT NULL |
| `description` | text | NULLABLE |
| `photo_path` | string(255) | NULLABLE |

### Backend FormSchema (Create)
`process`, `description`, `client_id`, `service_type_id`, `priority`, `photo`, `parish_id`, `street`, `reference_point`, `postal_code`, `location` (map)

### Backend FormSchema (Update)
Same as Create + `status` (select), `execution_date`

### Backend Requests
- [`StoreServiceOrderRequest`](../../app/Features/ServiceOrders/Requests/StoreServiceOrderRequest.php:19): schema rules + priority enum + photo image + `workflow_type`, `equipment_id`  
- [`UpdateServiceOrderRequest`](../../app/Features/ServiceOrders/Requests/UpdateServiceOrderRequest.php:21): schema rules + `location_id`, priority enum

### Discrepancies
| Issue | Severity | Detail |
|-------|----------|--------|
| `manager_id` missing from form | 🔴 Error | Non-nullable FK. Not rendered in any form. |
| `location_id` missing from CREATE form | 🔴 Error | Non-nullable FK. Create form has inline location fields instead (parish_id, street, etc.) — these are not DB columns on `service_orders` |
| `workflow_type` missing from Create form | 🔴 Error | Column exists with default 'regular'. Request validates it. Not in form. |
| `equipment_id` missing from Create form | ⚠️ Warning | Column nullable. Request validates conditional. Not in form. |
| `status` missing from Create form | ⚠️ Warning | Non-nullable column. Only in Update form. |
| `photo` field in form maps to `photo_path` in DB | ℹ️ Info | Acceptable — backend stores file path |
| Inline location fields not in migration | ℹ️ Info | `parish_id`, `street`, `reference_point`, `postal_code` are not SO columns — form creates a Location record |

**Verdict: 🔴 MULTIPLE DISCREPANCIES — manager_id, location_id (create), workflow_type, equipment_id, status missing**

---

## 9. TASKS

### Database (`tasks`)
| Column | Type | Constraints |
|--------|------|-------------|
| `id` | uuid | PK |
| `service_order_id` | uuid | FK → service_orders, NOT NULL |
| `manager_id` | uuid | FK → users, NOT NULL |
| `name` | string(150) | NOT NULL |
| `description` | text | NULLABLE |
| `status` | string(50) | NOT NULL |

### Backend FormSchema (Create/Update)
`name`, `description`, `service_order_id`, `sector_ids` (multi-select), `status` (select)

### Backend Requests
- [`StoreTaskRequest`](../../app/Features/Tasks/Requests/StoreTaskRequest.php:19): schema rules + `sector_ids.*` exists  
- [`UpdateTaskRequest`](../../app/Features/Tasks/Requests/UpdateTaskRequest.php:19): schema rules + sector_ids array validation

### Discrepancies
| Issue | Severity | Detail |
|-------|----------|--------|
| `manager_id` missing from form | 🔴 Error | Non-nullable FK. Not rendered. |
| `status` `sometimes` in Create but DB NOT NULL | ⚠️ Warning | Schema says `sometimes\|string` — no required. DB expects non-null. |
| `sector_ids` not a DB column | ℹ️ Info | Stored in pivot `tasks_sectors`. Expected. |

**Verdict: 🔴 DISCREPANCIES — manager_id not in form, status not required in create**

---

## 10. MINI TASKS

### Database (`mini_tasks`)
| Column | Type | Constraints |
|--------|------|-------------|
| `id` | uuid | PK |
| `task_id` | uuid | FK → tasks, NOT NULL |
| `supervisor_id` | uuid | FK → users, NOT NULL |
| `description` | string(250) | NOT NULL |
| `status` | string(50) | NOT NULL |

### Backend FormSchema (Create/Update)
`description`, `task_id`, `worker_ids` (multi), `team_ids` (multi)

### Backend Requests
- [`StoreMiniTaskRequest`](../../app/Features/MiniTasks/Requests/StoreMiniTaskRequest.php:19): schema + worker_ids, team_ids, materials validation

### Discrepancies
| Issue | Severity | Detail |
|-------|----------|--------|
| `supervisor_id` missing from form | 🔴 Error | Non-nullable FK. Not rendered. |
| `status` missing from form entirely | 🔴 Error | Non-nullable column. Not in create or update schema. |
| `worker_ids`, `team_ids` not DB columns | ℹ️ Info | Stored in pivot `mini_tasks_workers_teams` |

**Verdict: 🔴 DISCREPANCIES — supervisor_id and status missing from form**

---

## 11. WORK LOGS

### Database (`work_logs`)
| Column | Type | Constraints |
|--------|------|-------------|
| `id` | uuid | PK |
| `mini_task_id` | uuid | FK → mini_tasks, NOT NULL |
| `started_at` | timestamp | NOT NULL |
| `completed_at` | timestamp | NULLABLE |
| `description` | string(250) | NOT NULL |
| `duration_minutes` | unsignedInteger | NULLABLE |
| `status` | string(20) | default 'in_progress' |
| `reviewed_by` | uuid | FK → users, NULLABLE |
| `reviewed_at` | timestamp | NULLABLE |

### Backend FormSchema (Create)
`description`, `mini_task_id`, `started_at`, `completed_at`

### Backend FormSchema (Update)
Same as Create + `status` (select)

### Backend Requests
- [`StoreWorkLogRequest`](../../app/Features/WorkLogs/Requests/StoreWorkLogRequest.php:19): schema + worker_ids, materials validation

### Discrepancies
| Issue | Severity | Detail |
|-------|----------|--------|
| `duration_minutes` missing from both forms | ⚠️ Warning | Column exists (nullable). Could be calculated, but no form input. |
| `status` missing from Create form | ⚠️ Warning | Column has default, but should be settable. Only in Update form. |
| `reviewed_by` missing from form | ℹ️ Info | Set by backend on review action. |
| `reviewed_at` missing from form | ℹ️ Info | Set by backend on review action. |

**Verdict: ⚠️ MINOR — duration_minutes and status not in create form**

---

## 12. EQUIPMENTS

### Database (`equipments`)
| Column | Type | Constraints |
|--------|------|-------------|
| `id` | uuid | PK |
| `name` | string(200) | NOT NULL |
| `brand` | string(150) | NULLABLE |
| `model` | string(150) | NULLABLE |
| `serial_number` | string(250) | UNIQUE, NOT NULL |
| `manager_id` | uuid | FK → users, NOT NULL (restrictOnDelete) |
| `status` | string(50) | NOT NULL |
| `is_loanable` | boolean | default true |
| `revision_interval_days` | integer | NOT NULL |
| `last_revision_date` | dateTime | NULLABLE |
| `next_revision_date` | dateTime | NULLABLE |
| `description` | string(250) | NULLABLE |

### Backend FormSchema (Create/Update)
`name`, `brand`, `model`, `serial_number`, `status` (select), `is_loanable` (checkbox), `description`

### Backend Requests
- [`StoreEquipmentRequest`](../../app/Features/Equipments/Requests/StoreEquipmentRequest.php:19): from schema  
- [`UpdateEquipmentRequest`](../../app/Features/Equipments/Requests/UpdateEquipmentRequest.php:19): from schema

### Discrepancies
| Issue | Severity | Detail |
|-------|----------|--------|
| `manager_id` missing from form | 🔴 Error | Non-nullable FK (restrictOnDelete). Not in form. |
| `revision_interval_days` missing from form | 🔴 Error | Non-nullable integer. Not in form. Required column with no default. |
| `last_revision_date` missing from form | ⚠️ Warning | Nullable. Could be set by system. |
| `next_revision_date` missing from form | ⚠️ Warning | Nullable. Could be auto-calculated. |

**Verdict: 🔴 DISCREPANCIES — manager_id and revision_interval_days missing (both required)**

---

## 13. UNITS (Admin/Series)

### Database (`units`)
| Column | Type | Constraints |
|--------|------|-------------|
| `id` | uuid | PK |
| `name` | string(50) | NOT NULL |
| `abbreviation` | string(10) | UNIQUE, NOT NULL |

### Backend Requests
- [`StoreUnitRequest`](../../app/Shared/Requests/StoreUnitRequest.php:16): inline rules — name required|max:50, abbreviation required|max:10|unique  
- [`UpdateUnitRequest`](../../app/Shared/Requests/UpdateUnitRequest.php:17): inline rules — name sometimes|max:50, abbreviation sometimes|max:10|unique

### Frontend
Uses DataManager via [`Series.jsx`](../../resources/js/Features/Admin/Pages/Series.jsx) with `formSchema` from controller.

### Discrepancies
| Issue | Severity | Detail |
|-------|----------|--------|
| No FormSchema class exists | ℹ️ Info | Rules are inline in Request classes |
| `columns` in migration nullable, not in form | ℹ️ Info | Not used in current implementation |

**Verdict: ⚠️ MINOR — no FormSchema class but inline rules match columns**

---

## 14. USERS (Admin)

### Database (`users`)
| Column | Type | Constraints |
|--------|------|-------------|
| `id` | uuid | PK |
| `first_name` | string(250) | NOT NULL |
| `last_name` | string(250) | NOT NULL |
| `phone` | string(14) | UNIQUE, NOT NULL |
| `email` | string(250) | UNIQUE, NOT NULL |
| `password` | string(250) | NULLABLE |
| `status` | string(50) | NOT NULL |
| `locale` | string(10) | default 'pt' |

### Backend Requests
- [`StoreUserRequest`](../../app/Features/Admin/Requests/StoreUserRequest.php:18): inline rules — first_name, last_name required|max:250; email required|email|unique; phone required|max:14|unique; status required|enum; role_ids required|array

### Discrepancies
| Issue | Severity | Detail |
|-------|----------|--------|
| `password` not in Request | ⚠️ Warning | Nullable in DB, but no validation rules |
| `locale` not in Request | ⚠️ Warning | Has default 'pt', not settable via form |
| No FormSchema class | ℹ️ Info | Rules are inline |

**Verdict: ⚠️ MINOR — password and locale not exposed**

---

## 15. ROLES (Admin)

### Database (`roles`)
| Column | Type | Constraints |
|--------|------|-------------|
| `id` | uuid | PK |
| `name` | string(50) | NOT NULL |
| `columns` | string(250) | NULLABLE |

### Backend Requests
- [`StoreRoleRequest`](../../app/Features/Admin/Requests/StoreRoleRequest.php:16): inline — name required|max:50|unique  
- [`UpdateRoleRequest`](../../app/Features/Admin/Requests/UpdateRoleRequest.php:17): inline — name sometimes|max:50|unique

### Discrepancies
| Issue | Severity | Detail |
|-------|----------|--------|
| `columns` not in any request | ℹ️ Info | Nullable — not currently used |

**Verdict: ✅ CLEAN (columns is nullable and unused)**

---

## 16. PIVOT / SYSTEM TABLES (No Forms)

| Table | Has Forms? | Notes |
|-------|-----------|-------|
| `role_permissions` | ❌ | Managed via backend logic |
| `user_roles` | ❌ | Managed via user form (role_ids) |
| `user_preferences` | ❌ | Managed via settings |
| `app_settings` | ❌ | Managed via Settings page |
| `districts` | ❌ | Seeded reference data |
| `municipalities` | ❌ | Seeded reference data |
| `parishes` | ❌ | Seeded reference data |
| `tasks_sectors` | ❌ | Pivot — managed via Task form (sector_ids) |
| `mini_tasks_workers_teams` | ❌ | Pivot — managed via MiniTask form |
| `mini_tasks_materials` | ❌ | Pivot — managed via request validation |
| `work_logs_materials` | ❌ | Pivot — managed via request validation |
| `work_logs_workers` | ❌ | Pivot — managed via request validation |
| `work_log_equipment` | ❌ | Pivot — managed via request validation |
| `equipment_revisions` | ❌ | No CRUD form yet |
| `attachments` | ❌ | Managed via file upload |
| `notifications` | ❌ | System-generated |

---

## SUMMARY

### 🔴 Critical Discrepancies (Missing Required Columns from Form)

| # | Feature | Missing Field | DB Constraint |
|---|---------|--------------|---------------|
| 1 | **Service Orders** | `manager_id` | NON-NULL FK |
| 2 | **Service Orders** | `location_id` (create) | NON-NULL FK |
| 3 | **Service Orders** | `workflow_type` | default 'regular' |
| 4 | **Tasks** | `manager_id` | NON-NULL FK |
| 5 | **Mini Tasks** | `supervisor_id` | NON-NULL FK |
| 6 | **Mini Tasks** | `status` | NON-NULL string(50) |
| 7 | **Equipments** | `manager_id` | NON-NULL FK (restrictOnDelete) |
| 8 | **Equipments** | `revision_interval_days` | NON-NULL integer |

### 🔴 Validation Mismatch (Form nullable vs DB NOT NULL)

| # | Feature | Field | Schema Rule | DB Constraint |
|---|---------|-------|-------------|---------------|
| 1 | **Service Types** | `description` | `nullable` | NOT NULL |
| 2 | **Sectors** | `head_id` | `nullable` | NOT NULL (FK) |
| 3 | **Tasks** | `status` (create) | `sometimes` (not required) | NOT NULL |

### ⚠️ Minor Issues

| # | Feature | Issue |
|---|---------|-------|
| 1 | **Clients** | `user_id` not in form (set internally) |
| 2 | **Workers** | `user_id` not in form (set internally) |
| 3 | **Service Orders** | `equipment_id` not in create form |
| 4 | **Service Orders** | `status` not in create form |
| 5 | **Work Logs** | `duration_minutes` not in any form |
| 6 | **Work Logs** | `status` not in create form |
| 7 | **Users (Admin)** | `password` and `locale` not in form |
| 8 | **Equipments** | `last_revision_date`, `next_revision_date` not in form |

### ✅ Clean Modules
- **Locations** ✅
- **Materials** ✅
- **Teams** ✅
- **Service Types** ✅ (except description nullable mismatch)
- **Units (Series)** ✅
- **Roles** ✅

---

*End of STEP 1 — Report. Awaiting approval to proceed with STEP 2 (Fixes).*
