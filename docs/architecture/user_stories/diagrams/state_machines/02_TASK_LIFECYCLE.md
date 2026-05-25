# State Machine — Task Lifecycle

```plantuml
@startuml Task_Lifecycle
skinparam backgroundColor #FEFEFE
state pending {
  note right : Sectors assigned\nno Mini-Tasks yet
}

state in_progress {
  note right : Mini-Tasks created\nwork in progress
}

state pending_approval {
  note right : All Mini-Tasks done\nawaiting final approval
}

state completed {
  note right : Task complete
}

[*] --> pending: Created in SO

pending --> in_progress: First Mini-Task created
pending --> cancelled: Manager cancels
pending --> [*]

in_progress --> in_progress: More Mini-Tasks created
in_progress --> pending_approval: All Mini-Tasks completed
in_progress --> cancelled: Manager cancels

pending_approval --> completed: Supervisor approves
pending_approval --> in_progress: Changes requested

completed --> [*]
cancelled --> [*]

@enduml
```
