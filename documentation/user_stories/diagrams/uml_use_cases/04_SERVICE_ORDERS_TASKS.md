# UML Use Case — Service Orders & Tasks Workflow

```plantuml
@startuml ServiceOrders_Tasks
skinparam backgroundColor #FEFEFE
title Service Orders & Tasks

:👔 Manager: as Manager
:👷 Supervisor: as Supervisor

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

Manager --> UC_CREATE_SO
Manager --> UC_LIST_SO
Manager --> UC_EDIT_SO
Manager --> UC_STATUS_SO
Manager --> UC_CREATE_TASK
Manager --> UC_STATUS_TASK

Supervisor --> UC_CREATE_MT
Supervisor --> UC_ASSIGN_MT
Supervisor --> UC_STATUS_MT
Supervisor --> UC_APPROVE_MT

UC_CREATE_SO ..|> UC_CREATE_TASK : includes
UC_CREATE_TASK ..|> UC_CREATE_MT : includes
UC_ASSIGN_MT ..|> UC_APPROVE_MT : includes

@enduml
```
