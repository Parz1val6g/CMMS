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
  note right : Status: COMPLETED\nAll tasks done
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

in_progress --> in_progress: More Tasks created
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

@enduml
```
