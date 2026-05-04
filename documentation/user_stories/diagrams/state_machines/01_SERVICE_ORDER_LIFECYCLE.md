# State Machine — Service Order Lifecycle

```plantuml
@startuml SO_Lifecycle
skinparam backgroundColor #FEFEFE
state pending {
  note right : Status: PENDING\nActions: Edit, Activate, Delete
}

state active {
  note right : Status: ACTIVE\nActions: Create Task, Pause, Cancel
}

state in_progress {
  note right : Status: IN_PROGRESS\nActions: View Tasks, Suspend
}

state suspended {
  note right : Status: SUSPENDED\nActions: Resume, Cancel
}

state pending_approval {
  note right : Status: PENDING_APPROVAL\nActions: Approve, Reject
}

state completed {
  note right : Status: COMPLETED\nAll tasks done\n(Loan: Devolução task must be completed)
}

state archived {
  note right : Auto-archived after 90 days
}

[*] --> pending: Create SO

pending --> active: Manager activates
pending --> cancelled: Manager cancels
pending --> [*]

active --> in_progress: First Task created
active --> suspended: Manager suspends
active --> cancelled: Manager cancels

in_progress --> in_progress: More Tasks created\n(Regular only; Loan is locked to 2 tasks)
in_progress --> pending_approval: All Tasks completed
in_progress --> suspended: Manager suspends

suspended --> active: Manager resumes
suspended --> cancelled: Manager cancels

pending_approval --> completed: Admin approves
pending_approval --> in_progress: Changes requested

completed --> archived: Auto-archive
archived --> [*]

cancelled --> archived: Mark as archived
cancelled --> [*]

note top of completed
  **Loan Flow (workflow_type='loan')**
  A Loan SO uses the same state machine with these constraints:
  • Exactly 2 tasks: "Empréstimo de Equipamento" and "Devolução de Equipamento"
  • No additional tasks may be created
  • Completion of "Devolução de Equipamento" triggers SO → completed
  • Equipment is tracked via `work_log_equipment` instead of materials
end note

@enduml
```
