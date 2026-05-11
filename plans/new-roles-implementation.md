# New Roles Implementation Plan

## Summary
Add 4 new roles following the existing `equipment_manager` pattern: `task_manager`, `mini_task_manager`, `work_log_manager`, `sector_manager`.

## Audit Result
**None of the 4 roles exist.** Current DB roles: `admin`, `manager`, `equipment_manager`, `supervisor`, `worker`, `client`.

## Files to Modify

### 1. `database/seeders/RoleSeeder.php`
Add 4 new entries to the `$roles` array:
```php
'task_manager',
'mini_task_manager',
'work_log_manager',
'sector_manager',
```

### 2. `database/seeders/RolePermissionSeeder.php`
Add 4 new entries in the `$roleResourceActions` array:

#### `task_manager`
| Resource | Actions |
|----------|---------|
| SERVICE_ORDERS | VIEW |
| TASKS | VIEW, CREATE, UPDATE |
| ATTACHMENTS | VIEW, CREATE |
| PROFILE | VIEW, UPDATE |

#### `mini_task_manager`
| Resource | Actions |
|----------|---------|
| TASKS | VIEW |
| MINI_TASKS | VIEW, CREATE, UPDATE |
| ATTACHMENTS | VIEW, CREATE |
| PROFILE | VIEW, UPDATE |

#### `work_log_manager`
| Resource | Actions |
|----------|---------|
| MINI_TASKS | VIEW |
| WORK_LOGS | VIEW, CREATE, UPDATE |
| PROFILE | VIEW, UPDATE |

#### `sector_manager`
| Resource | Actions |
|----------|---------|
| SECTORS | VIEW, CREATE, UPDATE |
| TEAMS | VIEW, CREATE, UPDATE |
| WORKERS | VIEW, CREATE, UPDATE |
| PROFILE | VIEW, UPDATE |

### 3. `database/seeders/UserSeeder.php`
Add 4 new test users:
```php
'task_manager'      => ['first_name' => 'Task',     'last_name' => 'Manager',   'phone' => '+351912345701', 'email' => 'task.manager@cm.pt'],
'mini_task_manager' => ['first_name' => 'MiniTask', 'last_name' => 'Manager',   'phone' => '+351912345702', 'email' => 'minitask.manager@cm.pt'],
'work_log_manager'  => ['first_name' => 'WorkLog',  'last_name' => 'Manager',   'phone' => '+351912345703', 'email' => 'worklog.manager@cm.pt'],
'sector_manager'    => ['first_name' => 'Sector',   'last_name' => 'Manager',   'phone' => '+351912345704', 'email' => 'sector.manager@cm.pt'],
```

### No Changes Needed
- `app/Core/Enums/UserRole.php` — left as-is (separate concept, unused in app code)
- No migration changes — `roles` table already supports arbitrary `name` values
