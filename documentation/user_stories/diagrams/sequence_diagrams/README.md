# Sequence Diagrams — Index

All diagrams use **PlantUML** syntax. Show **Frontend → API → Database** interactions with transaction boundaries and error handling.

## 📂 Diagrams

| # | File | Description |
|---|------|-------------|
| 01 | [01_REGISTRATION_FLOW.md](./01_REGISTRATION_FLOW.md) | User registration flow |
| 02 | [02_LOGIN_TOKEN_REFRESH.md](./02_LOGIN_TOKEN_REFRESH.md) | Login & JWT token refresh |
| 03 | [03_CREATE_SERVICE_ORDER.md](./03_CREATE_SERVICE_ORDER.md) | Create service order with optional inline location |
| 04 | [04_CREATE_TASK_ASSIGN_SECTORS.md](./04_CREATE_TASK_ASSIGN_SECTORS.md) | Create task & assign to sectors (M:M) |
| 05 | [05_CREATE_MINITASK_ASSIGN_WORKERS.md](./05_CREATE_MINITASK_ASSIGN_WORKERS.md) | Create mini-task — polymorphic worker/team assignment |
| 06 | [06_CREATE_WORKLOG_MATERIAL_DEDUCTION.md](./06_CREATE_WORKLOG_MATERIAL_DEDUCTION.md) | Create work log with transactional stock deduction |
| 07 | [07_APPROVE_REJECT_WORKLOG.md](./07_APPROVE_REJECT_WORKLOG.md) | Approve/reject work log with stock rollback |
| 08 | [08_CASCADE_COMPLETION.md](./08_CASCADE_COMPLETION.md) | Cascade completion: WorkLog → MiniTask → Task → SO |
| 09 | [09_EXPORT_REPORT.md](./09_EXPORT_REPORT.md) | Export report generation (CSV/PDF) |
| 10 | [10_MATERIAL_ANALYTICS.md](./10_MATERIAL_ANALYTICS.md) | Material usage analytics — planned vs actual |
| 11 | [11_PERMISSION_CHECK_RBAC.md](./11_PERMISSION_CHECK_RBAC.md) | RBAC permission check with cache |
| 12 | [12_END_TO_END_COMPLETE.md](./12_END_TO_END_COMPLETE.md) | Complete end-to-end workflow overview |
