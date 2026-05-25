# UML Use Case — Service Orders & Tasks Workflow

```plantuml
@startuml ServiceOrders_Tasks
skinparam backgroundColor #FEFEFE
title Service Orders & Tasks

:👔 Manager: as Manager
:👷 Supervisor: as Supervisor
:🔧 Technician: as Technician

(Create Service Order) as UC_CREATE_SO
(View Service Orders) as UC_LIST_SO
(Edit Service Order) as UC_EDIT_SO
(Change Status (SO)) as UC_STATUS_SO
(Create Task) as UC_CREATE_TASK
(Create Mini-Task) as UC_CREATE_MT
(Assign Workers/Teams) as UC_ASSIGN_MT
(Change Status (Task)) as UC_STATUS_TASK
(Change Status (MT)) as UC_STATUS_MT
(Approve MT Completion) as UC_APPROVE_MT
(Manage Equipment Loan) as UC_LOAN
(Associate Materials/Equipment to SO) as UC_MATERIALS

Manager --> UC_CREATE_SO
Manager --> UC_LIST_SO
Manager --> UC_EDIT_SO
Manager --> UC_STATUS_SO
Manager --> UC_CREATE_TASK
Manager --> UC_STATUS_TASK
Manager --> UC_LOAN
Manager --> UC_MATERIALS

Supervisor --> UC_CREATE_MT
Supervisor --> UC_ASSIGN_MT
Supervisor --> UC_STATUS_MT
Supervisor --> UC_APPROVE_MT

Technician --> UC_LOAN
Technician --> UC_MATERIALS

UC_CREATE_SO ..|> UC_CREATE_TASK : includes
UC_CREATE_TASK ..|> UC_CREATE_MT : includes
UC_ASSIGN_MT ..|> UC_APPROVE_MT : includes
UC_LOAN ..|> UC_MATERIALS : includes

note right of UC_LOAN
  **Loan Workflow Rules:**
  • Only available when workflow_type='loan'
  • Auto-generates exactly 2 tasks:
    "Empréstimo de Equipamento"
    "Devolução de Equipamento"
  • Tracks equipment_id on the SO
  • Uses work_log_equipment table
    for tracking (not materials)
  • "Devolução" completion
    triggers SO closure
end note

note right of UC_MATERIALS
  For Loan SOs, materials are
  replaced by equipment tracking.
  The Materials tab treats
  inventory as priority.
end note

@enduml
```
