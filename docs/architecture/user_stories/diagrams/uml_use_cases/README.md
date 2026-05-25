# UML Use Case Diagrams — Index

All diagrams use **PlantUML** syntax. Renderable with the [PlantUML VS Code extension](https://marketplace.visualstudio.com/items?itemName=jebbs.plantuml).

## 📂 Diagrams

| # | File | Description |
|---|------|-------------|
| 01 | [01_SYSTEM_OVERVIEW.md](./01_SYSTEM_OVERVIEW.md) | All actors & use cases — system-level overview |
| 02 | [02_AUTHENTICATION_USER_MANAGEMENT.md](./02_AUTHENTICATION_USER_MANAGEMENT.md) | Auth flows & user management (Admin) |
| 03 | [03_ROLES_PERMISSIONS.md](./03_ROLES_PERMISSIONS.md) | RBAC — Roles & Permissions management |
| 04 | [04_SERVICE_ORDERS_TASKS.md](./04_SERVICE_ORDERS_TASKS.md) | Service Orders & Tasks workflow |
| 05 | [05_WORK_LOGS_MATERIALS.md](./05_WORK_LOGS_MATERIALS.md) | Work Logs & Materials management workflow |
| 06 | [06_ORGANIZATION_MANAGEMENT.md](./06_ORGANIZATION_MANAGEMENT.md) | Sectors, Teams & Workers management |
| 07 | [07_REPORTS_ANALYTICS.md](./07_REPORTS_ANALYTICS.md) | Reports & Analytics use cases |
| 08 | [08_SETTINGS_CONFIGURATION.md](./08_SETTINGS_CONFIGURATION.md) | System configuration & settings |
| 09 | [09_NOTIFICATION_SYSTEM.md](./09_NOTIFICATION_SYSTEM.md) | Notification system actors & use cases |
| 10 | [10_ATTACHMENTS_FILES.md](./10_ATTACHMENTS_FILES.md) | Attachments & files management |
| 11 | [11_MASTER_DATA.md](./11_MASTER_DATA.md) | Master data management (Admin & Manager) |

## 👥 Actors Summary

| Actor | Role |
|-------|------|
| 🔐 Admin | System administrator — full access |
| 👔 Manager | Creates service orders, manages clients & tasks |
| 👷 Supervisor | Manages mini-tasks, approves work logs |
| 🔧 Worker | Creates work logs, executes mini-tasks |
| 👥 Client | Views own service orders |
| ⚙️ System | Automated processes (notifications, stock deduction) |
