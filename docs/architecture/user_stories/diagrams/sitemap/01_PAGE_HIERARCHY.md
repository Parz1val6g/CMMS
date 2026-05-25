# Site Map — Application Page Hierarchy & Navigation

## 🎯 Main Site Map (Page Hierarchy)

```
📱 Application Root (/)
│
├── 🔐 Authentication (No Login Required)
│   ├── /auth/register
│   ├── /auth/login
│   ├── /auth/forgot-password
│   ├── /auth/reset-password/{token}
│   ├── /auth/verify-email/{code}
│   └── /auth/logout
│
├── 📊 Dashboard (All Authenticated Users)
│   ├── / (home)
│   └── /dashboard
│
├── 👤 User Management (Admin Only)
│   ├── /admin/users
│   │   ├── /admin/users (List)
│   │   ├── /admin/users/create
│   │   └── /admin/users/{id}/edit
│   ├── /admin/profile (Own profile)
│   └── /admin/account-settings
│
├── 🔑 Roles & Permissions (Admin Only)
│   ├── /admin/roles
│   │   ├── /admin/roles (List)
│   │   ├── /admin/roles/create
│   │   ├── /admin/roles/{id}/edit
│   │   ├── /admin/roles/{id}/permissions (Manage permissions)
│   │   └── /admin/roles/{id}/delete
│   └── /admin/permissions (View all available permissions)
│
├── 👥 Clients (Manager, Admin)
│   ├── /clients
│   │   ├── /clients (List/Search)
│   │   ├── /clients/create
│   │   ├── /clients/{id}
│   │   │   ├── /clients/{id}/edit
│   │   │   ├── /clients/{id}/service-orders (Related SO)
│   │   │   ├── /clients/{id}/locations (Registered locations)
│   │   │   └── /clients/{id}/export
│   │   └── /clients/{id}/delete
│   └── /clients/export
│
├── 📋 Service Orders (Manager, Supervisor, Admin)
│   ├── /service-orders
│   │   ├── /service-orders (List/Filter by status)
│   │   ├── /service-orders/create
│   │   ├── /service-orders/{id}
│   │   │   ├── /service-orders/{id}/edit
│   │   │   ├── /service-orders/{id}/tasks (View tasks)
│   │   │   ├── /service-orders/{id}/create-task
│   │   │   ├── /service-orders/{id}/attachments
│   │   │   ├── /service-orders/{id}/timeline (History)
│   │   │   ├── /service-orders/{id}/change-status
│   │   │   ├── /service-orders/{id}/assign-sectors
│   │   │   └── /service-orders/{id}/delete
│   └── /service-orders/export
│
├── ✅ Tasks (Manager, Supervisor, Admin)
│   ├── /tasks
│   │   ├── /tasks (List)
│   │   ├── /tasks/{id}
│   │   │   ├── /tasks/{id}/edit
│   │   │   ├── /tasks/{id}/mini-tasks (View mini-tasks)
│   │   │   ├── /tasks/{id}/create-mini-task
│   │   │   ├── /tasks/{id}/sectors (Assigned sectors)
│   │   │   ├── /tasks/{id}/change-status
│   │   │   ├── /tasks/{id}/attachments
│   │   │   └── /tasks/{id}/timeline
│   └── /tasks/{id}/delete
│
├── 🎯 Mini-Tasks (Supervisor, Worker, Admin)
│   ├── /mini-tasks
│   │   ├── /mini-tasks (List - My assigned)
│   │   ├── /mini-tasks/{id}
│   │   │   ├── /mini-tasks/{id}/edit
│   │   │   ├── /mini-tasks/{id}/assign-workers
│   │   │   ├── /mini-tasks/{id}/materials (Planned materials)
│   │   │   ├── /mini-tasks/{id}/work-logs (Created work logs)
│   │   │   ├── /mini-tasks/{id}/create-work-log
│   │   │   ├── /mini-tasks/{id}/change-status
│   │   │   ├── /mini-tasks/{id}/attachments
│   │   │   ├── /mini-tasks/{id}/approve-completion
│   │   │   └── /mini-tasks/{id}/timeline
│   └── /mini-tasks/{id}/delete
│
├── 🔧 Work Logs (Worker, Supervisor, Admin)
│   ├── /work-logs
│   │   ├── /work-logs (List - created by user OR assigned mini-tasks)
│   │   ├── /work-logs/{id}
│   │   │   ├── /work-logs/{id}/edit (draft only)
│   │   │   ├── /work-logs/{id}/materials (Used materials, stock deduction)
│   │   │   ├── /work-logs/{id}/timeline
│   │   │   ├── /work-logs/{id}/submit (Draft → Submitted)
│   │   │   ├── /work-logs/{id}/approve (Supervisor)
│   │   │   ├── /work-logs/{id}/reject (Supervisor)
│   │   │   ├── /work-logs/{id}/attachments
│   │   │   └── /work-logs/{id}/compare-materials (Planned vs Actual)
│   └── /work-logs/export
│
├── 🏢 Organization (Admin, Supervisor)
│   ├── /sectors
│   │   ├── /sectors (List)
│   │   ├── /sectors/create
│   │   ├── /sectors/{id}
│   │   │   ├── /sectors/{id}/edit
│   │   │   ├── /sectors/{id}/teams (List teams in sector)
│   │   │   ├── /sectors/{id}/workers (List workers in sector)
│   │   │   ├── /sectors/{id}/performance (Metrics)
│   │   │   └── /sectors/{id}/timeline
│   │   └── /sectors/{id}/delete
│   │
│   ├── /teams
│   │   ├── /teams (List)
│   │   ├── /teams/create
│   │   ├── /teams/{id}
│   │   │   ├── /teams/{id}/edit
│   │   │   ├── /teams/{id}/workers (Add/remove members)
│   │   │   ├── /teams/{id}/mini-tasks (Assigned mini-tasks)
│   │   │   ├── /teams/{id}/performance
│   │   │   └── /teams/{id}/timeline
│   │   └── /teams/{id}/delete
│   │
│   └── /workers
│       ├── /workers (List)
│       ├── /workers/create
│       ├── /workers/{id}
│       │   ├── /workers/{id}/edit
│       │   ├── /workers/{id}/profile
│       │   ├── /workers/{id}/mini-tasks (Assigned)
│       │   ├── /workers/{id}/work-logs (Created)
│       │   ├── /workers/{id}/availability
│       │   ├── /workers/{id}/performance
│       │   └── /workers/{id}/timeline
│       └── /workers/{id}/delete
│
├── 📦 Master Data (Admin, Manager)
│   ├── /service-types
│   │   ├── /service-types (List)
│   │   ├── /service-types/create
│   │   ├── /service-types/{id}/edit
│   │   └── /service-types/{id}/delete
│   │
│   ├── /locations
│   │   ├── /locations (List)
│   │   ├── /locations/create
│   │   ├── /locations/{id}/edit
│   │   └── /locations/{id}/delete
│   │
│   ├── /geographic
│   │   ├── /districts (List)
│   │   ├── /municipalities (List)
│   │   ├── /parishes (List)
│   │   └── /districts/{id}/municipalities/{mid}/parishes
│   │
│   ├── /materials
│   │   ├── /materials (List)
│   │   ├── /materials/create
│   │   ├── /materials/{id}
│   │   │   ├── /materials/{id}/edit
│   │   │   ├── /materials/{id}/stock (Current stock)
│   │   │   ├── /materials/{id}/stock-adjustments
│   │   │   ├── /materials/{id}/usage-history
│   │   │   ├── /materials/{id}/analytics
│   │   │   └── /materials/{id}/delete
│   │   ├── /materials/export
│   │   └── /materials/stock-report
│   │
│   └── /units
│       ├── /units (List)
│       ├── /units/create
│       ├── /units/{id}/edit
│       └── /units/{id}/delete
│
├── 📊 Reports & Analytics (Manager, Admin)
│   ├── /analytics
│   │   ├── /analytics/dashboard
│   │   ├── /analytics/service-orders (SO completion rates, timeline)
│   │   ├── /analytics/tasks (Task efficiency)
│   │   ├── /analytics/mini-tasks (MT status distribution)
│   │   ├── /analytics/materials (Planned vs Actual usage)
│   │   ├── /analytics/workers (Performance, productivity)
│   │   ├── /analytics/teams (Team performance)
│   │   ├── /analytics/sectors (Sector metrics)
│   │   ├── /analytics/costs (Cost analysis by SO, task, sector)
│   │   └── /analytics/timeline (Historical trends)
│   │
│   └── /reports
│       ├── /reports/work-logs
│       ├── /reports/materials-usage
│       ├── /reports/financial (Costs breakdown)
│       ├── /reports/hr (Worker productivity, hours)
│       ├── /reports/custom-builder
│       └── /reports/scheduled-reports
│
├── 📁 Exports & Downloads (All Authenticated)
│   ├── /exports
│   │   ├── /exports (History of exports)
│   │   ├── /exports/clients
│   │   ├── /exports/service-orders
│   │   ├── /exports/work-logs
│   │   ├── /exports/materials
│   │   ├── /exports/workers
│   │   └── /exports/{id}/download
│   └── /exports/create-custom
│
├── ⚙️ Settings & Configuration
│   ├── /settings/profile (User's own profile)
│   │   ├── /settings/profile/edit
│   │   ├── /settings/profile/change-password
│   │   ├── /settings/profile/2fa (Two-factor authentication)
│   │   └── /settings/profile/sessions (Active sessions)
│   │
│   ├── /settings/preferences (User preferences)
│   │   ├── /settings/preferences/notifications
│   │   ├── /settings/preferences/language
│   │   ├── /settings/preferences/theme
│   │   └── /settings/preferences/defaults
│   │
│   └── /admin/settings (Admin only - System configuration)
│       ├── /admin/settings/system (App name, timezone, etc)
│       ├── /admin/settings/email (Email config)
│       ├── /admin/settings/backup
│       ├── /admin/settings/audit-log
│       ├── /admin/settings/feature-flags
│       └── /admin/settings/integrations
│
├── 📩 Notifications (All Authenticated)
│   ├── /notifications
│   ├── /notifications/read/{id}
│   ├── /notifications/delete/{id}
│   └── /notifications/clear-all
│
├── 📋 Audit & Administration (Admin Only)
│   ├── /admin/audit-log
│   ├── /admin/backups
│   │   ├── /admin/backups (List)
│   │   ├── /admin/backups/create
│   │   ├── /admin/backups/{id}/restore
│   │   └── /admin/backups/{id}/delete
│   ├── /admin/activity-log
│   ├── /admin/error-log
│   └── /admin/system-health
│
└── ❌ Error Pages
    ├── /404 (Not Found)
    ├── /403 (Forbidden)
    ├── /401 (Unauthorized)
    └── /500 (Server Error)
```
