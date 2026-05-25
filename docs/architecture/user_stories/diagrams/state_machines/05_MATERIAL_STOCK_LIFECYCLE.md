# State Machine — Material Stock Lifecycle

```plantuml
@startuml Material_Stock_Lifecycle
skinparam backgroundColor #FEFEFE
state in_stock {
  note right : Available for use\nCan create Work Logs
}

state pending_deduction {
  note right : Stock "reserved"\nIf WL rejected: stock released
}

state deducted {
  note right : Final deduction\nStock confirmed used
}

state low_stock {
  note right : Waiting for reorder
}

state archived {
  note right : Obsolete material
}

[*] --> in_stock: Added to inventory

in_stock --> in_stock: Quantity updated (adjustment)
in_stock --> pending_deduction: Work Log DRAFT created
in_stock --> low_stock: Stock falls below threshold
in_stock --> archived: Admin archives
in_stock --> [*]

pending_deduction --> deducted: Work Log APPROVED
pending_deduction --> in_stock: Work Log REJECTED

deducted --> low_stock: Or directly if threshold reached
deducted --> [*]

low_stock --> in_stock: Reorder received
low_stock --> [*]

archived --> [*]

@enduml
```
