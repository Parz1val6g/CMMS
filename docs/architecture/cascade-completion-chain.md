# Cascade Completion Chain

The cascade completion chain ensures that when a child entity is marked complete, the system automatically checks whether the parent entity can also be marked complete. This propagates upward through four hops.

## Chain Overview

```
WorkLog approved  →  MiniTask complete  →  Task awaiting approval  →  ServiceOrder awaiting approval
```

| Hop | Trigger Event | Listener | Condition | Action |
|-----|--------------|----------|-----------|--------|
| 1 | `WorkLogCompletedEvent` | `CheckWorkLogsCompletion` | All WorkLogs on the MiniTask are `APPROVED` | Calls `MiniTaskService::complete()` |
| 2 | `MiniTaskCompletedEvent` | `CheckMiniTasksCompletion` | All MiniTasks on the Task are `COMPLETED` | Sets Task to `AWAITING_APPROVAL`, notifies task manager |
| 3 | `TaskCompletedEvent` | `CheckTaskCompletion` | All Tasks on the ServiceOrder are `COMPLETED` | Sets ServiceOrder to `AWAITING_APPROVAL`, notifies SO manager |

## Detailed Flow

### Hop 1: WorkLog → MiniTask

**Location:** `app/Features/MiniTasks/Listeners/CheckWorkLogsCompletion.php`

When a `WorkLogCompletedEvent` fires (a worker submits a WorkLog for approval), this listener checks all WorkLogs belonging to the same MiniTask. If every WorkLog has status `APPROVED`, it calls `MiniTaskService::complete()` which transitions the MiniTask to `COMPLETED`.

> **Note:** WorkLogs are only considered "done" when approved, not merely submitted. This means a MiniTask stays open until every assigned WorkLog receives approval.

### Hop 2: MiniTask → Task

**Location:** `app/Features/Tasks/Listeners/CheckMiniTasksCompletion.php`

When a `MiniTaskCompletedEvent` fires (a supervisor completes a MiniTask), this listener checks all MiniTasks belonging to the same Task. If every MiniTask has status `COMPLETED`, the Task is transitioned to `AWAITING_APPROVAL` and the task manager receives a notification.

### Hop 3: Task → ServiceOrder

**Location:** `app/Features/ServiceOrders/Listeners/CheckTaskCompletion.php`

When a `TaskCompletedEvent` fires (a task manager completes a Task), this listener checks all Tasks belonging to the same ServiceOrder. If every Task has status `COMPLETED`, the ServiceOrder is transitioned to `AWAITING_APPROVAL` and the service order manager receives a notification.

**Special case — Loan workflow:** For ServiceOrders with `workflow_type === 'loan'`, the listener checks whether a second task named "Devolução de Equipamento" exists. If it does, both tasks must be completed before the ServiceOrder transitions. If the second task does not exist, the listener returns early (no cascade).

## Event Registration

All events and listeners are registered in:

**`app/Providers/EventServiceProvider.php`**

```php
WorkLogCompletedEvent::class => [CheckWorkLogsCompletion::class],
MiniTaskCompletedEvent::class => [CheckMiniTasksCompletion::class],
TaskCompletedEvent::class     => [CheckTaskCompletion::class],
```

## Database Columns

- `work_logs.status` — values: `pending`, `in_progress`, `completed`, `approved`, `rejected`
- `mini_tasks.status` — values: `pending`, `in_progress`, `completed`, `blocked`, `cancelled`
- `tasks.status` — values: `pending`, `in_progress`, `awaiting_approval`, `completed`, `blocked`, `cancelled`
- `service_orders.status` — values: `pending`, `in_progress`, `awaiting_approval`, `completed`, `cancelled`
- `service_orders.workflow_type` — values: `regular`, `loan`

## Debugging a Cascade Failure

1. Check the event fired correctly (look for the dispatch call in the service)
2. Verify the listener is registered in `EventServiceProvider`
3. Check the condition in the listener — are there incomplete children?
4. For the loan workflow special case, verify the second task exists with the exact name
