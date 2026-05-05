# AUDIT RESOLUTION REPORT — Business Logic Audit

**Date**: 2026-05-05  
**Source**: `ESPECIFICACAO_PROJETO.md` + 47 business questions answered by Product Owner  
**Status**: All decisions resolved

---

## Table of Contents

1. [Roles & Permissions](#1-roles--permissoes-rbac)
2. [Inventory & Equipment](#2-gestao-de-inventario-e-equipamentos)
3. [Service Orders](#3-ordens-de-servico)
4. [Tasks, MiniTasks & WorkLogs](#4-tarefas-mini-tarefas-e-work-logs)
5. [Organization](#5-organizacao)
6. [Notifications](#6-notificacoes)
7. [Geography](#7-geografia)
8. [Cross-Cutting](#8-cross-cutting)
9. [Critical Action Items](#9-critical-action-items)

---

## 1. Roles & Permissões (RBAC)

### Decision Summary

| # | Question | Decision |
|---|----------|----------|
| 1 | Receptionist role? | **Removed** — does not exist. Current roles: `client`, `equipment_manager`, `supervisor`, `manager`, `admin`, `worker`. |
| 2 | Client access to system? | Clients have **no system access**. Future: email-based communication with citizens. |
| 3 | Public registration? | **No public registration.** Contact form only for prospective clients — manual onboarding by admin. |
| 4 | Multiple roles per user? | **Yes** — a user can have multiple roles. RBAC must support role accumulation. |
| 5 | Delegation of authority? | **Not needed** — any manager can approve any work log, so absence of one manager is covered by others. |
| 6 | Worker deactivation impact? | Mini-task **status preserved**; supervisor is **notified** to reassign. |
| 7 | `pending_approval` state? | Exists only for **MiniTasks** and **Service Orders** (not work logs). Approval done by **Manager**. Work logs are just submitted/completed by workers — no approval needed. |
| 47 | `pending` role approval? | **Admin approves** the transition. Future: delegation of this function. |

### Required Specification Changes

1. **Update** [Section 2](ESPECIFICACAO_PROJETO.md:2) roles table: remove Receptionist, add `equipment_manager`, clarify client has no access.
2. **Update** [Feature Access Matrix](documentation/user_stories/diagrams/sitemap/02_FEATURE_ACCESS_MATRIX.md): remove Citizen and Receptionist columns; add `equipment_manager`.
3. **Correct** [Section 3.2](ESPECIFICACAO_PROJETO.md:3.2): Work logs do NOT require approval — workers finalize them directly. Approval exists at MiniTask level (by admin/supervisor).
4. **Correct** [Section 3.1](ESPECIFICACAO_PROJETO.md:3.1) cascade rule: Mini-task completes when **all work logs are finalized** (not approved). SO completion requires **Manager approval**.
5. **Correct** [US-014](documentation/user_stories/02_ROLES_AND_PERMISSIONS.md): change roles to: `admin`, `manager`, `supervisor`, `worker`, `equipment_manager`, `pending`.
6. **Design** multi-role assignment: RBAC system must support `user_roles` as a many-to-many relationship.

---

## 2. Gestão de Inventário e Equipamentos

### Decision Summary

| # | Question | Decision |
|---|----------|----------|
| 8 | Equipment states | Full state machine: `active` → `in_use` (loaned), `maintenance`, `retired/broken`, `out_of_service`. Equipment used in work logs like materials. |
| 9 | Concurrent loans prevention | **Must implement** — atomic validation preventing loan if equipment is not `active`. |
| 10 | Damaged equipment | New states needed: `broken` and `under_repair`. Full lifecycle logic required (broken → under_repair → active). |
| 11 | Periodic revisions | Equipment has `next_revision_date`. When due: **must block availability** → marked as `maintenance_pending` → sent to maintenance → returned to `active`. All revisions logged. |
| 12 | Low stock notification | Notify `equipment_manager` (new role). If role doesn't exist, create it and adapt system. |
| 13 | Material reservation | **Reserved at MiniTask creation** — when mini-task is created, materials are pre-allocated based on estimate. |
| 14 | Equipment categories | Not yet implemented. Future feature. |
| 15 | `unit_price_at_use` | Price is **snapshot at MiniTask creation** — frozen value from DB at that moment. |

### Required Specification Changes

1. **Create** Equipment state machine documentation with all states: `active`, `in_use`, `maintenance_pending`, `maintenance`, `broken`, `under_repair`, `retired`.
2. **Define** `equipment_manager` role permissions (view stock, manage revisions, receive low-stock alerts).
3. **Update** [Section 3.5](ESPECIFICACAO_PROJETO.md:3.5): add material reservation rule at MiniTask creation.
4. **Define** revision lifecycle: equipment → `maintenance_pending` when revision_date passes → manager triggers maintenance → `maintenance` → manager confirms return → `active`.
5. **Document** equipment damage flow: damage report → equipment → `broken` → repair order → `under_repair` → return → `active`.

---

## 3. Ordens de Serviço

### Decision Summary

| # | Question | Decision |
|---|----------|----------|
| 16 | Human-readable reference | **Yes** — format like `OS-2026-00042` for citizen/field communication. |
| 17 | Loan equipment mandatory | **Yes** — frontend must enforce. Loan SO must also require a **location** (like regular SOs). |
| 18 | Return reminder | **Not yet implemented** — must be built (periodic reminder for pending returns). |
| 19 | Recurring SOs | **Should exist** — system must support periodic/recurring service orders. Not yet implemented. |
| 20 | Cancel with equipment `in_use` | Equipment must be **in our possession** if SO is cancelled. Either force return or block cancellation. |
| 21 | Priority impact | Priority **categorizes** SOs. High-priority must be executed first — impacts **scheduling/ordering**, not SLA. |
| 22 | Scheduling | SOs **can be scheduled** — `scheduled_at` field needed. |
| 23 | workflow_type change | **Not allowed** — loan cannot become regular or vice-versa. Immutable after creation. |

### Required Specification Changes

1. **Add** `reference_number` field (auto-generated sequential) to service_orders.
2. **Add** `scheduled_at` field to service_orders.
3. **Add** location requirement for loan SOs (currently only regular).
4. **Document** priority impact: affects execution order, not SLAs.
5. **Define** cancellation rules for loan: equipment must be returned first, or cancellation forces return Task 2 creation.

---

## 4. Tarefas, Mini-Tarefas e Work Logs

### Decision Summary

| # | Question | Decision |
|---|----------|----------|
| 24 | Worker task visibility | Workers only see **mini-tasks assigned to them or their teams**, plus related work logs. Matrix needs correction. |
| 25 | Work log approval | **Work logs do NOT need approval.** Workers finalize them. MiniTask completion is approved by the person who administered it (supervisor/admin). |
| 26 | Resubmission limit | **Yes** — implement a limit (3 rejections → auto-escalation to manager). |
| 27 | Time overlap | **Yes** — system must validate no overlapping work logs for the same worker. |
| 28 | Hours estimation | **Yes** — system must flag when actual hours significantly exceed estimate. |
| 29 | Cross-sector assignment | **No** — only workers/teams from the same sector can be assigned. |
| 30 | Edit after submission | **Yes** — worker can edit work log until MiniTask is "closed" by the responsible person. |
| 31 | Multiple workers on work log | **One person submits** on behalf of the group. Any worker in that work log can finalize it. |

### Required Specification Changes

1. **Correct** [Section 3.2](ESPECIFICACAO_PROJETO.md:3.2): work log state machine should be `in_progress → submitted → completed` (no approval/rejection). Approval exists at MiniTask level.
2. **Update** [Matriz](documentation/user_stories/diagrams/sitemap/02_FEATURE_ACCESS_MATRIX.md): remove Approve/Reject Work Log from Supervisor; add Approve MiniTask Completion.
3. **Update** [WorkLog lifecycle](documentation/user_stories/diagrams/state_machines/04_WORKLOG_LIFECYCLE.md): remove approved/rejected states.
4. **Add** hours estimation to MiniTask model + alerting rule.
5. **Add** cross-sector validation rule.
6. **Define** "closing" a MiniTask (approval by admin/supervisor).

---

## 5. Organização

### Decision Summary

| # | Question | Decision |
|---|----------|----------|
| 32 | Multiple supervisors per sector | **Yes** — a sector can have multiple responsible people. |
| 33 | Worker in multiple teams/sectors | **Yes** — workers can belong to multiple teams and sectors. |
| 34 | Workload visibility | **Not yet defined** — pending design. |

### Required Specification Changes

1. **Update** Sector model to support multiple "chiefs" (many-to-many with users).
2. **Update** Worker model to support multiple teams and sectors.
3. **Defer** workload visibility — mark as future feature.

---

## 6. Notificações

### Decision Summary

| # | Question | Decision |
|---|----------|----------|
| 35 | Notification matrix | **Not yet implemented.** Will be part of mobile app version. |
| 36 | Notification channels | **In-app only for now.** Mobile version will add push. |

### Required Specification Changes

1. **Mark** notification system as "planned for mobile app (long-term)".
2. **Define** future notification matrix when mobile version is started.

---

## 7. Geografia

### Decision Summary

| # | Question | Decision |
|---|----------|----------|
| 37 | Geography maintenance | **Admins only** — admin CRUD for districts, municipalities, parishes. |
| 38 | Location without coordinates | **Allowed** — lat/lng is optional. |

### Required Specification Changes

1. **Update** [Section 4.1#20](ESPECIFICACAO_PROJETO.md:4.1): geography is NOT read-only — admins can maintain it. Change "só leitura" to "admin-maintained".
2. **Document** that lat/lng is optional.

---

## 8. Cross-Cutting

### Decision Summary

| # | Question | Decision |
|---|----------|----------|
| 39 | Soft delete client with active SOs | **Not allowed** — client cannot be deleted while having active SOs. |
| 40 | Audit logs | Audit logs **migration exists** but purpose unclear. Need clarification on what audit_logs table tracks vs. login_history. |
| 41 | Multi-tenancy | **Single-tenant for now.** Future: add `tenant_id` to all tables, scope queries, separate DB per tenant or shared with tenant column. |
| 42 | Bulk operations | **Should exist** — export work logs, mini-tasks, equipment, materials. Bulk approve/reassign. |
| 43 | Holidays/non-working days | **Deferred** — all days allowed for now. Future: block Sundays. |
| 44 | Export scope | Users can only export data **they created or are involved in** (sector-scoped). |
| 45 | Data retention | Data retained **5-10 years** after last access/deletion. Purge flow needed. |
| 46 | Equipment state machine | **Must be created** — full state machine documentation for equipment lifecycle. |

### Required Specification Changes

1. **Define** audit_logs purpose clearly — is it for tracking `unit_price_at_use` changes or full audit trail?
2. **Add** multi-tenancy considerations to architecture section (future).
3. **Create** Equipment state machine document (parallel to material stock lifecycle).
4. **Define** data retention policy in specification (5-10 years + purge).

---

## 9. Critical Action Items

Items requiring immediate implementation (ordered by priority):

| Priority | Item | Domain | Description |
|----------|------|--------|-------------|
| 🔴 P1 | Equipment state machine | Equipment | Create state machine + validation for all states (active, in_use, maintenance_pending, maintenance, broken, under_repair, retired) |
| 🔴 P1 | Concurrent loan guard | Equipment | Atomic check preventing loan of non-active equipment |
| 🔴 P1 | Work log → MiniTask approval correction | Workflow | Remove approval from work logs; add approval at MiniTask level |
| 🔴 P1 | Material reservation at MiniTask creation | Materials | Reserve materials when MiniTask is created (pre-allocation) |
| 🟡 P2 | Multi-role RBAC | Permissions | Refactor `user_roles` to many-to-many |
| 🟡 P2 | Human-readable reference number | Service Orders | Auto-generated sequential reference (OS-YYYY-NNNNN) |
| 🟡 P2 | Cross-sector assignment guard | Tasks | Validate worker/team belongs to same sector as task |
| 🟡 P2 | Work log time overlap validation | Work Logs | Block overlapping hours for same worker |
| 🟡 P2 | Hours estimation vs. actual alert | Work Logs | Flag significant discrepancies |
| 🟡 P2 | Resubmission limit (3x) | Work Logs | Escalate to manager after 3 rejections |
| 🟡 P2 | Loan SO cancellation → force return | Service Orders | Auto-create Task 2 or block cancellation |
| 🟡 P2 | Loan SO requires location | Service Orders | Add location_id validation for loan SOs |
| 🟡 P2 | Low stock → notify equipment_manager | Materials | Implement notification trigger |
| 🟢 P3 | Recurring SO generation | Service Orders | Calendar-based recurring SO auto-creation |
| 🟢 P3 | Equipment revision blocking | Equipment | Block loan when next_revision_date is past due |
| 🟢 P3 | Export scoping by sector | Export | Filter exportable data by user's sector involvement |
| 🟢 P3 | Admin geography CRUD | Geography | Add admin endpoints for district/municipality/parish |
| 🟢 P3 | Scheduled_at field | Service Orders | Add scheduling date to SO model |
| 🟢 P4 | Notification matrix | Notifications | Define event → role mapping for future mobile version |
| 🟢 P4 | Data retention/purge | Admin | Implement 5-10 year retention + purge flow |
| 🟢 P4 | Multi-tenancy prep | Architecture | Document required changes for future multi-tenant |
| 🟢 P4 | Bulk operations | Cross-cutting | Export in bulk, bulk approve, bulk assign |

---

> **Maintainers**: This document supersedes the assumptions in `ESPECIFICACAO_PROJETO.md` where conflicts exist. All specification documents should be updated to reflect these resolutions.
