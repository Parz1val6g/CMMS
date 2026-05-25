# Sequence Diagrams — Frontend, Backend & Database Interactions

> ⚠️ **Este ficheiro foi reorganizado.** Cada diagrama está agora no seu próprio ficheiro dedicado.

## 📂 Diagramas Individuais

Ver pasta: [`diagrams/sequence_diagrams/`](./diagrams/sequence_diagrams/README.md)

| # | Diagrama | Ficheiro |
|---|----------|---------|
| 01 | User Registration Flow | [01_REGISTRATION_FLOW.md](./diagrams/sequence_diagrams/01_REGISTRATION_FLOW.md) |
| 02 | Login & Token Refresh | [02_LOGIN_TOKEN_REFRESH.md](./diagrams/sequence_diagrams/02_LOGIN_TOKEN_REFRESH.md) |
| 03 | Create Service Order (with Location) | [03_CREATE_SERVICE_ORDER.md](./diagrams/sequence_diagrams/03_CREATE_SERVICE_ORDER.md) |
| 04 | Create Task & Assign to Sectors (M:M) | [04_CREATE_TASK_ASSIGN_SECTORS.md](./diagrams/sequence_diagrams/04_CREATE_TASK_ASSIGN_SECTORS.md) |
| 05 | Create Mini-Task — Polymorphic Assignment | [05_CREATE_MINITASK_ASSIGN_WORKERS.md](./diagrams/sequence_diagrams/05_CREATE_MINITASK_ASSIGN_WORKERS.md) |
| 06 | Create Work Log with Material Deduction (Transactional) | [06_CREATE_WORKLOG_MATERIAL_DEDUCTION.md](./diagrams/sequence_diagrams/06_CREATE_WORKLOG_MATERIAL_DEDUCTION.md) |
| 07 | Approve / Reject Work Log (with Stock Rollback) | [07_APPROVE_REJECT_WORKLOG.md](./diagrams/sequence_diagrams/07_APPROVE_REJECT_WORKLOG.md) |
| 08 | Cascade Completion: WorkLog → MiniTask → Task → ServiceOrder | [08_CASCADE_COMPLETION.md](./diagrams/sequence_diagrams/08_CASCADE_COMPLETION.md) |
| 09 | Export Report (CSV/PDF) | [09_EXPORT_REPORT.md](./diagrams/sequence_diagrams/09_EXPORT_REPORT.md) |
| 10 | Material Usage Report (Planned vs Actual) | [10_MATERIAL_ANALYTICS.md](./diagrams/sequence_diagrams/10_MATERIAL_ANALYTICS.md) |
| 11 | Permission Check (RBAC Authorization Gate) | [11_PERMISSION_CHECK_RBAC.md](./diagrams/sequence_diagrams/11_PERMISSION_CHECK_RBAC.md) |
| 12 | Complete End-to-End Cycle | [12_END_TO_END_COMPLETE.md](./diagrams/sequence_diagrams/12_END_TO_END_COMPLETE.md) |
