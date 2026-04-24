# State Machine — Work Log Lifecycle

```plantuml
@startuml WorkLog_Lifecycle
skinparam backgroundColor #FEFEFE
state draft {
  note right : Materials can be\nadded/removed\nStock not yet deducted
}

state submitted {
  note right : Pending supervisor review\nMaterials still in stock\n(deducted on draft)
}

state approved {
  note right : Final state\nStock confirmed deducted
}

state rejected {
  note right : Stock returned to inventory\nCan be modified & resubmitted
}

[*] --> draft: Created

draft --> draft: Edit materials & description
draft --> submitted: Worker submits
draft --> cancelled: Worker cancels
draft --> [*]

submitted --> approved: Supervisor approves
submitted --> rejected: Supervisor rejects
submitted --> submitted: Waiting for review

rejected --> draft: Modify & resubmit

approved --> [*]
cancelled --> [*]

@enduml
```
