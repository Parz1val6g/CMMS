# Plano de Extração: LoanOrders

> Gerado a partir da sessão `grill-me` em 2026-05-12.

## Decisões Tomadas

| Decisão | Escolha |
|---|---|
| **Tabela** | `loan_orders` separada |
| **Prefixo ref.** | `EMP/2026/0001` |
| **Status próprio** | `LoanOrderStatus` (PENDING, CHECKED_OUT, RETURNED, CANCELLED) |
| **Tasks** | Polymorphic MorphTo — `taskable_id` + `taskable_type` na `tasks` |
| **Location** | Reuso `Location` model com `location_id` FK |
| **Frontend** | Página separada `/loans` |
| **Sidebar** | Secção Operacional, após Equipamentos |
| **Policy** | `LoanOrderPolicy` própria |
| **Migração** | Soft-reference (copia existentes, flag, limpa depois) |
| **Acções** | Separação explícita: create, return, cancel |

## Backend — Ficheiros a Criar

```
app/Core/Enums/LoanOrderStatus.php                       ← novo enum
app/Features/LoanOrders/
├── Models/LoanOrder.php                                 ← novo modelo
├── Policies/LoanOrderPolicy.php                         ← nova policy
├── Services/LoanOrderService.php                        ← novo serviço
├── Requests/
│   ├── StoreLoanOrderRequest.php                        ← validação create
│   └── CancelLoanOrderRequest.php                       ← validação cancel
├── Controllers/
│   ├── Web/LoanOrderPageController.php                  ← render Inertia
│   └── Api/LoanOrderController.php                      ← endpoints REST
├── Presenters/LoanOrderPresenter.php                    ← formatação
├── Resources/LoanOrderResource.php                      ← API resource
├── LoanOrderFormSchema.php                              ← schema formulário
├── Routes/
│   ├── web.php
│   └── api.php
database/migrations/
└── xxxx_xx_xx_create_loan_orders_table.php              ← nova tabela
└── xxxx_xx_xx_add_taskable_to_tasks_table.php           ← morph columns
└── xxxx_xx_xx_migrate_existing_loans.php                ← soft-ref migration
```

## Backend — Ficheiros a Modificar

### Task Model (`app/Features/Tasks/Models/Task.php`)
- Adicionar `taskable_id` (UUID) + `taskable_type` (string)
- Adicionar MorphTo `taskable()` relationship
- Remover `service_order_id` FK e relação `belongsTo ServiceOrder`

### ServiceOrderFormSchema (`app/Features/ServiceOrders/ServiceOrderFormSchema.php`)
- Remover `ToggleInput::make('workflow_type')`
- Remover `SelectInput::make('equipment_ids')`
- Ajustar regras para remover condicional loan

### ServiceOrderService (`app/Features/ServiceOrders/Services/ServiceOrderService.php`)
- Remover todos os branches `$isLoan`
- Remover lógica de equipment lock/status
- Remover `initiateReturn()`, `releaseEquipment()`
- Simplificar `cancelCascade()` (já não precisa de guard loan)
- Simplificar `complete()` (já não precisa de liberta equipment)

### StoreServiceOrderRequest (`app/Features/ServiceOrders/Requests/StoreServiceOrderRequest.php`)
- Remover validação condicional de loan
- `equipment_ids` passa a `prohibited`
- `sector_ids` mantém-se obrigatório

### CreateLoanTasks Listener (`app/Features/ServiceOrders/Listeners/CreateLoanTasks.php`)
- **Remover** — a criação da task de checkout passa para `LoanOrderService::create()`

### Equipment Model (`app/Features/Equipments/Models/Equipment.php`)
- Nenhuma alteração estrutural
- O `serviceOrders()` BelongsToMany mantém-se para os SOs standard
- Adicionar `loanOrders()` BelongsToMany para a nova pivot `equipment_loan_order`

### ServiceOrder Model (`app/Features/ServiceOrders/Models/ServiceOrder.php`)
- Remover `workflow_type` do `$fillable` e `$casts`
- Remover relação `equipments()` (loan passa a ser noutro modelo)
- Relação `tasks()` muda para MorphMany via `taskable`

### Routes actuais (`routes/web.php` e `routes/api.php`)
- Nada a remover directamente (as rotas SO mantêm-se)
- As novas rotas loan são carregadas de `routes/api/loan-orders.php`

## Frontend — Ficheiros a Criar

```
resources/js/Features/LoanOrders/
├── Pages/Index.jsx                                     ← CRUD página principal
├── Components/LoanOrderDrawer.jsx                      ← drawer de detalhe
├── Components/EquipmentCard.jsx                         ← cartão de equipamento (similar ao SOEquipmentTab)
```

## Frontend — Ficheiros a Modificar

### Sidebar (`resources/js/Layouts/data/sidebar.js`)
- Adicionar item `Empréstimos` com ícone, href `/loans`, na secção `section_operational` após `equipments`

### ServiceOrders Index (`resources/js/Features/ServiceOrders/Pages/Index.jsx`)
- Remover `SOEquipmentTab` component
- Remover lógica `isLoan` na construção de tabs do drawer
- Remover importações loan-specific

### TasksTree (`resources/js/Features/ServiceOrders/Components/DrawerTabs/TasksTree.jsx`)
- O `workflowType` prop pode deixar de existir

### TaskTreeNode (`resources/js/Features/ServiceOrders/Components/DrawerTabs/TaskTreeNode.jsx`)
- Remover `showReturnBtn` e lógica `workflowType === 'loan'`

### Translation files
- Adicionar chaves `pages.sidebar.loans`, `pages.loan_orders.*`

## LoanOrder — Schema da Tabela

```sql
CREATE TABLE loan_orders (
    id              UUID PRIMARY KEY,
    process         VARCHAR(20) NOT NULL UNIQUE,       -- EMP/2026/0001
    client_id       UUID NOT NULL REFERENCES clients(id),
    manager_id      UUID NOT NULL REFERENCES users(id),
    location_id     UUID REFERENCES locations(id),
    description     TEXT,
    photo_path      VARCHAR(255),
    status          VARCHAR(20) NOT NULL DEFAULT 'pending',
    -- timestamps
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,
    deleted_at      TIMESTAMP                           -- soft delete
);
```

### Tabela Pivot

```sql
CREATE TABLE equipment_loan_order (
    equipment_id    UUID NOT NULL REFERENCES equipments(id),
    loan_order_id   UUID NOT NULL REFERENCES loan_orders(id),
    created_at      TIMESTAMP,
    PRIMARY KEY (equipment_id, loan_order_id)
);
```

## LoanOrder — Ciclo de Vida

```
PENDING ──checkout──► CHECKED_OUT ──return──► RETURNED
   │                      │
   └──cancel──────────────┴──cancel──► CANCELLED
```

- **create()** → status PENDING, lock equipments, mark IN_USE, cria task checkout
- **initiateReturn()** → valida checkout completo, cria task devolução
- **cancel()** → release equipments back to ACTIVE, status CANCELLED
- **complete()** (automático quando return task completa) → status RETURNED, release equipments

## LoanOrderService — Métodos

```php
class LoanOrderService {
    public function create(array $data, string $managerId): LoanOrder;
    public function initiateReturn(LoanOrder $loanOrder): Task;
    public function cancel(LoanOrder $loanOrder): LoanOrder;
    public function delete(LoanOrder $loanOrder): void;
}
```

## Rotas API

```php
// routes/api/loan-orders.php
Route::apiResource('loan-orders', LoanOrderController::class);
Route::post('loan-orders/{loanOrder}/return', [LoanOrderController::class, 'initiateReturn']);
Route::post('loan-orders/{loanOrder}/cancel', [LoanOrderController::class, 'cancel']);

// routes/web/loan-orders.php
Route::get('/loans', [LoanOrderPageController::class, 'index'])->name('loan-orders.index');
Route::get('/loans/{id}', [LoanOrderPageController::class, 'show'])->name('loan-orders.show');
```
