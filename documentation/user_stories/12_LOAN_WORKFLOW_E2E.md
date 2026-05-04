# Loan (Empréstimo) Workflow — End-to-End Specification

**Last Updated**: 2026-05-04
**Domain**: Service Orders — Loan Equipment
**Version**: 2.0 — Reactive Task Generation

---

## Table of Contents

1. [Overview](#1-overview)
2. [Workflow Initiation](#2-workflow-initiation)
3. [The Sequential Task Rule (CRITICAL)](#3-the-sequential-task-rule-critical)
4. [Execution & Life-cycle](#4-execution--life-cycle)
5. [UI Behavior](#5-ui-behavior)
6. [Database Schema](#6-database-schema)
7. [Seeder Reference](#7-seeder-reference)
8. [Event-Driven Cascade](#8-event-driven-cascade)
9. [State Machine](#9-state-machine)
10. [Frontend Components](#10-frontend-components)
11. [Edge Cases & Validation](#11-edge-cases--validation)
12. [Cross-Reference Index](#12-cross-reference-index)

---

## 1. Overview

The **Loan (Empréstimo)** workflow extends the standard Service Order system to handle equipment lending. It uses a **reactive sequential task model** — Task 1 is auto-generated on SO creation; Task 2 is created on-demand via an "Initiate Return" trigger — and replaces the generic materials tracking with equipment-specific tracking via `work_log_equipment`.

### Key Distinctions

| Aspect | Regular Workflow | Loan Workflow |
|--------|-----------------|---------------|
| `workflow_type` | `'regular'` | `'loan'` |
| `equipment_id` | `null` | FK → `equipments.id` |
| Task count | Unlimited | 1 (initially), 2nd created on trigger |
| Task names | Free-form | Fixed: "Empréstimo de Equipamento" / "Devolução de Equipamento" (Task 2 created later) |
| Materials tab | Hidden | Visible (equipment tracking) |
| Pivot table | `work_logs_materials` | `work_log_equipment` |
| Closure trigger | All tasks complete | Task 2 ("Devolução") created AND complete |

---

## 2. Workflow Initiation

### 2.1 Trigger

A Service Order is classified as a **Loan** when:

```php
$serviceOrder->workflow_type === 'loan';
```

### 2.2 Mandatory Equipment Link

A Loan SO **MUST** be associated with an [`equipment_id`](app/Features/ServiceOrders/Models/ServiceOrder.php:24) from the inventory. This is enforced at the database level:

- Column: [`equipment_id`](database/migrations/2026_05_04_090300_add_equipment_id_and_workflow_type_to_service_orders_table.php:18) — UUID, FK → `equipments.id`, nullable
- For `workflow_type='loan'`: **required** (non-null)
- For `workflow_type='regular'`: **null**

### 2.3 Model Definition

The [`ServiceOrder`](app/Features/ServiceOrders/Models/ServiceOrder.php:15) model includes:

```php
protected $fillable = [
    // ...
    'equipment_id',   // line 24
    'workflow_type',  // line 25
];

public function equipment()  // line 61-64
{
    return $this->belongsTo(Equipment::class);
}
```

### 2.4 API Response

The [`ServiceOrderResource`](app/Features/ServiceOrders/Resources/ServiceOrderResource.php:7) exposes both fields:

```php
'workflow_type' => $this->workflow_type,  // line 16
'equipment_id'  => $this->equipment_id,   // line 17
```

When the `equipment` relation is eager-loaded, the full equipment payload is included (line 42-53):

```php
'equipment' => $this->whenLoaded('equipment', function () {
    return [
        'id'                => $this->equipment->id,
        'name'              => $this->equipment->name,
        'serial_number'     => $this->equipment->serial_number,
        'status'            => $this->equipment->status,
        'is_loanable'       => $this->equipment->is_loanable,
        'description'       => $this->equipment->description,
        'last_revision_date'=> $this->equipment->last_revision_date?->format('Y-m-d'),
        'next_revision_date'=> $this->equipment->next_revision_date?->format('Y-m-d'),
    ];
}),
```

---

## 3. The Sequential Task Rule (CRITICAL)

### 3.1 Rule Statement

Loan Service Orders use a **reactive sequential model**: only Task 1 is auto-generated upon creation; Task 2 is created when the user triggers "Initiate Return".

| # | Task Name (PT) | Task Name (EN) | Purpose | Created |
|---|----------------|----------------|---------|---------|
| 1 | **Empréstimo de Equipamento** | Equipment Checkout | Documents the delivery of equipment to the client | **Auto-generated** on SO creation |
| 2 | **Devolução de Equipamento** | Equipment Return | Documents the return/check-in of equipment | **On-demand** via `POST /api/service-orders/{so}/initiate-return` |

### 3.2 Enforcement

- **No additional tasks** may be manually added to a Loan SO.
- The system **MUST** auto-generate **only Task 1** upon SO creation when `workflow_type='loan'`.
- Task 2 is created **exclusively** via the [`initiateReturn()`](app/Features/ServiceOrders/Controllers/ServiceOrderController.php:93) controller method.
- Any task-creation endpoint or service method MUST reject attempts to create more than 1 task on an incomplete Loan SO (Task 2 not yet created) or more than 2 total tasks on a Loan SO.
- The task names are fixed and MUST match exactly (`"Empréstimo de Equipamento"`, `"Devolução de Equipamento"`).

### 3.3 Seeder Reference

The [`DevelopmentTestSeeder`](database/seeders/DevelopmentTestSeeder.php:26-35) defines task names per workflow type:

```php
private const SO_TASK_NAMES = [
    'regular' => [
        'Inspeção e levantamento de necessidades',
        'Preparação do local de intervenção',
    ],
    'loan' => [
        'Empréstimo de Equipamento',  // Only 1 task — Task 2 created via initiateReturn()
    ],
];
```

> **Note**: The seeder uses production-matching task names. The **production auto-generation logic** (see [`CreateLoanTasks`](app/Features/ServiceOrders/Listeners/CreateLoanTasks.php)) must use the fixed names from §3.1.

---

## 4. Execution & Life-cycle

### 4.1 Task Life-cycle

```
Task 1: Empréstimo de Equipamento (auto-created on SO creation)
  ├── Status: pending → in_progress → completed
  ├── Work Logs: Document equipment condition, accessories delivered
  ├── Effect: Equipment status → 'in_use' (loaned out)
  └── Trigger: Completing Task 1 enables the "Iniciar Devolução" button in the UI

Task 2: Devolução de Equipamento (created via "Initiate Return" trigger)
  ├── Created by: POST /api/service-orders/{so}/initiate-return
  ├── Prerequisites: Task 1 must be completed; Task 2 must not already exist
  ├── Status: pending → in_progress → completed
  ├── Work Logs: Document equipment condition upon return, accessories returned
  └── Effect: Equipment status → 'active' (available); SO eligible for closure
```

### 4.2 Completion Rules

| Condition | Behavior |
|-----------|----------|
| Task 1 completed | Equipment marked as `in_use` (loaned); "Iniciar Devolução" button becomes available |
| Task 2 created | Second task appears in the task tree as `pending` |
| Task 2 completed | Equipment marked as `active` (available); SO eligible for closure |
| Both tasks completed | SO can be set to `completed` status |
| Task 2 not yet created | SO completion **BLOCKED** by [`CheckTaskCompletion`](app/Features/ServiceOrders/Listeners/CheckTaskCompletion.php:23) loan guard |
| Task 2 created but not completed | SO completion **BLOCKED** |

### 4.3 Work Logs (Equipment Tracking)

Work Logs in Loan workflows use the [`work_log_equipment`](database/seeders/DevelopmentTestSeeder.php:247) pivot table instead of `work_logs_materials`:

- **Regular**: [`work_logs_materials`](database/seeders/DevelopmentTestSeeder.php:234) — tracks quantity_used, unit_price_at_use
- **Loan**: [`work_log_equipment`](database/seeders/DevelopmentTestSeeder.php:247) — tracks which equipment was involved per work log

The seeder logic (lines 230-254):

```php
if ($so->workflow_type === 'regular') {
    // Attach materials via work_logs_materials
} else {
    // Attach equipments via work_log_equipment
}
```

### 4.4 Inventory Status Transition

```
┌──────────────────────────────────────────────────┐
│                   Equipment                       │
│                                                    │
│  [active] ───Task 1──→ [in_use] ───Task 2──→ [active] │
│              checkout            return             │
└──────────────────────────────────────────────────┘
```

- **Task 1 completion** → Equipment status changes to `in_use`
- **Task 2 completion** → Equipment status reverts to `active`
- Status enum: `active`, `in_use`, `maintenance`, `retired`

---

## 5. UI Behavior

### 5.1 Tab Visibility

The **"Materials"** tab header and content are **visible only for Loan workflows**.

Implemented in [`SOWorkspaceDrawer.jsx`](resources/js/Features/ServiceOrders/Components/SOWorkspaceDrawer.jsx:41-43):

```jsx
const showEquipments = serviceOrder?.workflow_type === 'loan';
const visibleTabs = TABS.filter((t) => t.key !== 'materials' || showEquipments);
```

Tab content rendering (line 195):

```jsx
{showEquipments && activeTab === 'materials' && (
    <SOMaterialsList serviceOrder={so} />
)}
```

### 5.2 Materials Tab Content

Rendered by [`SOMaterialsList`](resources/js/Features/ServiceOrders/Components/Tabs/SOMaterialsList.jsx:55):

| Column | Description |
|--------|-------------|
| Item Name | Equipment name + description |
| Serial Number | Equipment serial number (monospace) |
| Type | Badge: **Loanable** (indigo) or **Fixed** (slate) |
| Status | Badge: Active (green), In Use (blue), Maintenance (yellow), Retired (red) |
| Next Revision | Date + overdue indicator (red if past due) |

Features:
- Client-side search by name or serial number
- Empty state when no equipment linked
- "No service order selected" state

### 5.3 Tasks Tree

The [`SOTasksTree`](resources/js/Features/ServiceOrders/Components/DrawerTabs/TasksTree.jsx:26) renders the hierarchical task tree for **any** workflow type. For Loan SOs, it renders 1 root task initially (Task 2 appears only after "Initiate Return" is triggered).

**Workflow-aware props** (passed from parent drawer):
- `workflowType` — `'loan'` or `'regular'`; controls whether the "Initiate Return" button is shown
- `onInitiateReturn` — callback to `POST /api/service-orders/{so}/initiate-return`

Rendering chain:
1. [`SOTasksTree`](resources/js/Features/ServiceOrders/Components/DrawerTabs/TasksTree.jsx) — fetches tasks + mini-tasks, builds tree; passes `workflowType` and `onInitiateReturn` to each node
2. [`buildTaskTree`](resources/js/Features/ServiceOrders/Utils/buildTaskTree.js:14) — generic flat→nested utility
3. [`TaskTreeNode`](resources/js/Features/ServiceOrders/Components/DrawerTabs/TaskTreeNode.jsx:20) — recursive render with expand/collapse, status badges, type badges (T/MT); conditionally renders **"Iniciar Devolução"** button

**"Iniciar Devolução" button logic** ([`TaskTreeNode.jsx`](resources/js/Features/ServiceOrders/Components/DrawerTabs/TaskTreeNode.jsx:55-58)):
```jsx
const showReturnBtn = workflowType === 'loan'
  && item._type === 'task'
  && item.name === 'Empréstimo de Equipamento'
  && item.status === 'completed';
```

Button behavior:
- Visible only when Task 1 ("Empréstimo de Equipamento") is **completed**
- Calls `onInitiateReturn(serviceOrderId)` which triggers the API endpoint
- Uses `e.stopPropagation()` to prevent accidental tree expand/collapse toggling
- After success, the task tree re-fetches and Task 2 appears as `pending`

---

## 6. Database Schema

### 6.1 Migration

File: [`database/migrations/2026_05_04_090300_add_equipment_id_and_workflow_type_to_service_orders_table.php`](database/migrations/2026_05_04_090300_add_equipment_id_and_workflow_type_to_service_orders_table.php)

| Column | Type | Default | Constraints |
|--------|------|---------|-------------|
| `workflow_type` | VARCHAR(50) | `'regular'` | — |
| `equipment_id` | UUID (FK) | `null` | FK → `equipments.id`, nullable |

### 6.2 Pivot Tables

| Table | Used By | Key Columns |
|-------|---------|-------------|
| `work_logs_materials` | Regular workflows | `work_log_id`, `material_id`, `quantity_used`, `unit_price_at_use` |
| `work_log_equipment` | Loan workflows | `work_log_id`, `equipment_id` |

---

## 7. Seeder Reference

The [`DevelopmentTestSeeder`](database/seeders/DevelopmentTestSeeder.php:57) creates a 1-2-2-2-2 hierarchy for regular SO and a 1-1-2-2-2 hierarchy for loan SO (reflecting the sequential model):

```
ServiceOrder (regular)          ServiceOrder (loan)
  └── Task 1                      └── Task 1 (Empréstimo de Equipamento)
  │     └── MiniTask 1                  └── MiniTask 1
  │     │     └── WorkLog 1                  └── WorkLog 1
  │     │     └── WorkLog 2                  └── WorkLog 2
  │     └── MiniTask 2                  └── MiniTask 2
  └── Task 2
        └── MiniTask 1
        └── MiniTask 2
```

Key details:
- **2 SOs**: 1 regular, 1 loan
- **3 Tasks**: 2 for regular, 1 for loan (Task 2 created later via `initiateReturn()`)
- **6 Mini-tasks**: 2 per task
- **Work logs**: Different statuses per mini-task
- **Materials**: Regular SO uses `work_logs_materials`; Loan SO uses `work_log_equipment`
- **Seeder reflects production reality**: Loan SO starts with only 1 task (Empréstimo de Equipamento)

---

## 8. Event-Driven Cascade

The Loan workflow introduces a **new event listener** and a **manual trigger endpoint** on top of the existing cascade:

### 8.1 Creation Cascade (NEW)

```
ServiceOrderCreatedEvent
  └── CreateLoanTasks (listener)  ← NEW
        ├── Transaction::create (Task 1: "Empréstimo de Equipamento")
        └── Equipment::update (status → 'reserved')
```

On SO creation with `workflow_type='loan'`, the [`CreateLoanTasks`](app/Features/ServiceOrders/Listeners/CreateLoanTasks.php) listener:
1. Opens a DB transaction (via [`TransactionHandler`](app/Shared/Services/TransactionHandler.php))
2. Creates Task 1: "Empréstimo de Equipamento" with `status: pending`
3. Updates the linked equipment status to `reserved` (pending checkout)
4. Commits the transaction — if either step fails, both roll back

Registered in [`EventServiceProvider`](app/Providers/EventServiceProvider.php:20-38):
```php
ServiceOrderCreatedEvent::class => [
    TriggerServiceOrderCreationNotifications::class,
    CreateLoanTasks::class,  // 2nd listener
],
```

### 8.2 Initiate Return Trigger (NEW)

```
User Click → POST /api/service-orders/{so}/initiate-return
  └── ServiceOrderController::initiateReturn()
        ├── Gate::authorize('update', $serviceOrder)
        ├── abort_if workflow_type !== 'loan'
        ├── abort_if Task 2 already exists
        ├── abort_if Task 1 not completed
        ├── Task::create (Task 2: "Devolução de Equipamento")
        └── return TaskResource
```

The [`initiateReturn()`](app/Features/ServiceOrders/Controllers/ServiceOrderController.php:93) method:
- Validates the SO is a loan workflow
- Checks Task 2 doesn't already exist (409 Conflict)
- Checks Task 1 is in `completed` status (400 Bad Request)
- Creates Task 2 with `status: pending`
- Returns the new task as a [`TaskResource`](app/Features/Tasks/Resources/TaskResource.php)

Route definition ([`routes/api/service-orders.php`](routes/api/service-orders.php)):
```php
Route::post('/{serviceOrder}/initiate-return', [ServiceOrderController::class, 'initiateReturn']);
```

### 8.3 Task Completion Cascade

```
WorkLog → CheckWorkLogsCompletion → MiniTaskService::complete()
  → MiniTaskCompletedEvent → CheckMiniTasksCompletion → TaskService::complete()
    → TaskCompletedEvent → CheckTaskCompletion → ServiceOrderService::complete()
```

For Loan SOs, the [`CheckTaskCompletion`](app/Features/ServiceOrders/Listeners/CheckTaskCompletion.php:15) listener includes a **loan guard**:

```php
if ($serviceOrder->workflow_type === 'loan') {
    $hasTask2 = $serviceOrder->tasks()
        ->where('name', 'Devolução de Equipamento')
        ->exists();
    if (!$hasTask2) return;  // Don't close SO — Task 2 not yet created
}
```

This prevents premature SO closure when Task 1 completes but Task 2 hasn't been created yet.

**Cascade summary:**

| Trigger | Action | Guard |
|---------|--------|-------|
| SO created (`loan`) | Task 1 created, equipment → `reserved` | — |
| Task 1 completed | Equipment → `in_use`; "Iniciar Devolução" enabled | — |
| User clicks "Iniciar Devolução" | Task 2 created as `pending` | Task 1 must be completed; Task 2 must not exist |
| Task 2 completed | Equipment → `active`; SO eligible for closure | Loan guard: Task 2 must exist |

---

## 9. State Machine

### 9.1 Service Order States

```
                    ┌──────────────┐
                    │   PENDING    │
                    └──────┬───────┘
                           │
                    ┌──────▼───────┐
              ┌─────│ IN_PROGRESS  │◄──────────────────┐
              │     └──────┬───────┘                    │
              │            │                            │
              │     ┌──────▼───────┐                    │
              │     │  COMPLETED   │                    │
              │     └──────────────┘                    │
              │                                         │
              │  Loan-specific:                         │
              │  IN_PROGRESS (Task 1 done)              │
              │  IN_PROGRESS (Task 2 done → COMPLETED)  │
              └─────────────────────────────────────────┘
                       (blocked if Task 2 incomplete)
```

### 9.2 Equipment States

```
                    ┌──────────────┐
                    │    ACTIVE    │
                    └──────┬───────┘
                           │
                    ┌──────▼───────┐
                    │   IN_USE     │  ← Task 1 complete (checkout)
                    └──────┬───────┘
                           │
                    ┌──────▼───────┐
                    │    ACTIVE    │  ← Task 2 complete (return)
                    └──────────────┘
```

---

## 10. Frontend Components

| Component | File | Role |
|-----------|------|------|
| [`SOWorkspaceDrawer`](resources/js/Features/ServiceOrders/Components/SOWorkspaceDrawer.jsx:37) | Main drawer with tab navigation; controls Materials tab visibility based on `workflow_type` |
| [`SOTasksTree`](resources/js/Features/ServiceOrders/Components/Tabs/SOTasksTree.jsx:26) | Hierarchical task tree (generic, workflow-agnostic) |
| [`TaskTreeNode`](resources/js/Features/ServiceOrders/Components/Tabs/TaskTreeNode.jsx:20) | Recursive tree node with expand/collapse |
| [`SOMaterialsList`](resources/js/Features/ServiceOrders/Components/Tabs/SOMaterialsList.jsx:55) | Equipment display for Loan workflows |
| [`buildTaskTree`](resources/js/Features/ServiceOrders/Utils/buildTaskTree.js:14) | Generic flat-to-nested utility |

---

## 11. Edge Cases & Validation

| Scenario | Expected Behavior |
|----------|-------------------|
| Create Loan SO without `equipment_id` | ❌ **Rejected** — equipment is mandatory for loan workflows |
| Add 3rd task to Loan SO | ❌ **Rejected** — binary task rule enforcement |
| Delete Task 1 from Loan SO | ❌ **Rejected** — would violate binary rule; only allowed if re-creating both |
| Attempt to complete SO with Task 2 pending | ❌ **Blocked** — event cascade prevents closure |
| Change `workflow_type` from `loan` to `regular` | Must validate: if tasks exist, reject or cascade appropriately |
| Equipment already `in_use` (loaned to another SO) | ❌ **Rejected** — equipment must be `active` to be loaned |
| Work Log on Task 2 with missing return condition notes | ⚠️ **Warning** — recommend documenting condition upon return |

---

## 12. Cross-Reference Index

| Document | Section | File |
|----------|---------|------|
| ServiceOrder Model | §2.3 | [`app/Features/ServiceOrders/Models/ServiceOrder.php`](app/Features/ServiceOrders/Models/ServiceOrder.php) |
| ServiceOrder Resource | §2.4 | [`app/Features/ServiceOrders/Resources/ServiceOrderResource.php`](app/Features/ServiceOrders/Resources/ServiceOrderResource.php) |
| StoreServiceOrderRequest | §2.2 | [`app/Features/ServiceOrders/Requests/StoreServiceOrderRequest.php`](app/Features/ServiceOrders/Requests/StoreServiceOrderRequest.php) |
| ServiceOrderService::create() | §2.1 | [`app/Features/ServiceOrders/Services/ServiceOrderService.php`](app/Features/ServiceOrders/Services/ServiceOrderService.php) |
| Migration | §6.1 | [`database/migrations/2026_05_04_090300_add_equipment_id_and_workflow_type_to_service_orders_table.php`](database/migrations/2026_05_04_090300_add_equipment_id_and_workflow_type_to_service_orders_table.php) |
| Seeder | §7 | [`database/seeders/DevelopmentTestSeeder.php`](database/seeders/DevelopmentTestSeeder.php) |
| Drawer + Tab visibility | §5.1-5.2 | [`resources/js/Features/ServiceOrders/Components/SOWorkspaceDrawer.jsx`](resources/js/Features/ServiceOrders/Components/SOWorkspaceDrawer.jsx) |
| Materials List | §5.2 | [`resources/js/Features/ServiceOrders/Components/Tabs/SOMaterialsList.jsx`](resources/js/Features/ServiceOrders/Components/Tabs/SOMaterialsList.jsx) |
| Tasks Tree | §5.3 | [`resources/js/Features/ServiceOrders/Components/DrawerTabs/TasksTree.jsx`](resources/js/Features/ServiceOrders/Components/DrawerTabs/TasksTree.jsx) |
| Task Tree Node | §5.3 | [`resources/js/Features/ServiceOrders/Components/DrawerTabs/TaskTreeNode.jsx`](resources/js/Features/ServiceOrders/Components/DrawerTabs/TaskTreeNode.jsx) |
| Tree Builder | §5.3 | [`resources/js/Features/ServiceOrders/Utils/buildTaskTree.js`](resources/js/Features/ServiceOrders/Utils/buildTaskTree.js) |
| CreateLoanTasks Listener | §8.1 | [`app/Features/ServiceOrders/Listeners/CreateLoanTasks.php`](app/Features/ServiceOrders/Listeners/CreateLoanTasks.php) |
| EventServiceProvider | §8.1 | [`app/Providers/EventServiceProvider.php`](app/Providers/EventServiceProvider.php) |
| initiateReturn Controller | §8.2 | [`app/Features/ServiceOrders/Controllers/ServiceOrderController.php`](app/Features/ServiceOrders/Controllers/ServiceOrderController.php) |
| Initiate Return Route | §8.2 | [`routes/api/service-orders.php`](routes/api/service-orders.php) |
| CheckTaskCompletion Listener | §8.3 | [`app/Features/ServiceOrders/Listeners/CheckTaskCompletion.php`](app/Features/ServiceOrders/Listeners/CheckTaskCompletion.php) |
| Event Cascade | §8.3 | Cascade chain in [`documentation/ADAPTATION_GUIDE.md`](documentation/ADAPTATION_GUIDE.md:294-299) |
| Implementation Tracker | — | [`documentation/IMPLEMENTATION_TRACKER.md`](documentation/IMPLEMENTATION_TRACKER.md) |
| Adaptation Guide | — | [`documentation/ADAPTATION_GUIDE.md`](documentation/ADAPTATION_GUIDE.md) |

---

> **Maintainers**: This document is the **Source of Truth** for the Loan Workflow. Any changes to the implementation MUST be reflected here. The Sequential Task Rule (§3) is the most critical invariant — always enforce it.
