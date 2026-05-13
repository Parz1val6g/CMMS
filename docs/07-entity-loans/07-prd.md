# PRD — Sistema de Empréstimos para Entidades

| Campo | Valor |
|-------|-------|
| **Data** | 2026-05-12 |
| **Versão** | 1.1 |
| **Status** | Aprovado |
| **Prioridade** | Alta |
| **Grill-Me** | [`07-grill-me.md`](07-grill-me.md) |

---

## Problem Statement

Atualmente, o sistema não suporta que entidades externas (Câmaras Municipais, Juntas de Freguesia, etc.) possam requisitar equipamentos de forma autónoma. Os empréstimos de equipamentos eram geridos internamente por admins/managers, sem um portal de self-service para entidades.

Isto causa:
- Processo manual para registar pedidos de empréstimo de entidades
- Sem rastreio de disponibilidade de equipamentos por datas
- Sem separação entre o papel de requisição (Entidade) e o papel de aprovação (Manager)
- Sem calendário visual para a Entidade perceber quando pode levantar/devolver equipamentos

---

## Solução

Criar um **novo role `entidade`** com um **dashboard próprio** onde pode:
1. Ver a lista dos seus empréstimos (passados e atuais)
2. Criar novos pedidos de empréstimo
3. Escolher equipamentos com calendário visual de disponibilidade

Os **Managers** aprovam os pedidos na página normal de Loan Orders ([`/loan-orders`](../../resources/js/Features/LoanOrders/Pages/Index.jsx)) e gerem o ciclo de vida.

---

## User Stories

1. Como **entidade**, quero fazer login e ver um dashboard com os meus empréstimos, para acompanhar o estado dos meus pedidos.
2. Como **entidade**, quero criar um pedido de empréstimo selecionando equipamentos + datas + necessidade de operador, para requisitar o que preciso.
3. Como **entidade**, quero ver um calendário visual de disponibilidade ao escolher um equipamento, para saber se as datas estão livres.
4. Como **entidade**, quero que os meus dados apareçam preenchidos automaticamente no formulário.
5. Como **entidade**, quero escolher um local de entrega diferente da minha morada (opcional).
6. Como **manager**, quero ver todos os pedidos pendentes em [`/loan-orders`](../../resources/js/Features/LoanOrders/Pages/Index.jsx), para aprovar ou recusar.
7. Como **manager**, quero aprovar um pedido (PENDING → APPROVED) e depois fazer o checkout (APPROVED → CHECKED_OUT).
8. Como **manager**, quero registar a devolução (CHECKED_OUT → RETURNED).
9. Como **manager**, quero atribuir Workers aos equipamentos que precisam de operador.
10. Como **sistema**, quero validar que um equipamento não é emprestado se já estiver ocupado (noutro empréstimo, service order ou mini-task) nas mesmas datas.

---

## Decisões de Implementação

### Domain Model Changes

#### 1. Nova Role: `entidade`

Adicionar constante `ENTIDADE` em [`app/Core/Enums/RoleName.php`](../../app/Core/Enums/RoleName.php):
```php
public const ENTIDADE = 'entidade';
```

#### 2. Novo PermissionResource: `LOANS`

Adicionar `LOANS` em [`app/Core/Enums/PermissionResource.php`](../../app/Core/Enums/PermissionResource.php) com ações `create`, `view`, `approve`, `checkout`, `return`, `cancel`:
```php
case LOANS = 'loans';
```

#### 3. Nova Migração: `create_entities_table`

```sql
CREATE TABLE entities (
    id UUID PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES users(id),
    entity_type VARCHAR(50) NOT NULL, -- municipal_council, parish_council, other
    nif VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    location_id UUID REFERENCES locations(id),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

#### 4. Alterar Migração: `loan_orders`

- Remover `client_id`
- Adicionar `entity_id` UUID FK → entities
- Adicionar `delivery_location_id` UUID FK → locations (nullable — ver Q13)
- Adicionar `approved_by` UUID FK → users (nullable)
- Adicionar `approved_at` TIMESTAMP (nullable)
- `status` passa a suportar: `pending`, `approved`, `checked_out`, `returned`, `cancelled`

#### 5. Alterar Pivot: `equipment_loan_order`

Adicionar colunas:
- `start_date` DATE NOT NULL
- `end_date` DATE NOT NULL
- `needs_operator` BOOLEAN DEFAULT false

**Nota:** Quando o Manager aprova, se `needs_operator = true`, cria-se registos na tabela `loan_order_equipment_workers` (ver secção "Operator Assignment" abaixo).

#### 6. Novo Enum: `LoanOrderStatus` em [`app/Core/Enums/LoanOrderStatus.php`](../../app/Core/Enums/LoanOrderStatus.php)

```php
enum LoanOrderStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case CHECKED_OUT = 'checked_out';
    case RETURNED = 'returned';
    case CANCELLED = 'cancelled';

    public function isPending(): bool { return $this === self::PENDING; }
    public function isActive(): bool { return in_array($this, [self::APPROVED, self::CHECKED_OUT]); }
    public function isTerminal(): bool { return in_array($this, [self::RETURNED, self::CANCELLED]); }
}
```

#### 7. Novo Enum: `EntityType` em [`app/Core/Enums/EntityType.php`](../../app/Core/Enums/EntityType.php)

```php
enum EntityType: string
{
    case MUNICIPAL_COUNCIL = 'municipal_council';
    case PARISH_COUNCIL = 'parish_council';
    case OTHER = 'other';
}
```

---

### Model Changes

#### Entity Model ([`app/Features/Entities/Models/Entity.php`](../../app/Features/Entities/Models/Entity.php))

Segue o padrão de [`app/Features/Clients/Models/Client.php`](../../app/Features/Clients/Models/Client.php):

```php
class Entity extends Model
{
    use Base;

    protected $fillable = [
        'user_id', 'entity_type', 'nif', 'name', 'phone', 'location_id',
    ];

    protected $casts = [
        'entity_type' => EntityType::class,
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function location() { return $this->belongsTo(Location::class); }
    public function loanOrders() { return $this->hasMany(LoanOrder::class); }
}
```

#### User Model ([`app/Shared/Models/User.php`](../../app/Shared/Models/User.php))

Adicionar relação (paralela a `clientProfile()` e `workerProfile()`):

```php
public function entityProfile()
{
    return $this->hasOne(Entity::class);
}
```

#### LoanOrder Model ([`app/Features/LoanOrders/Models/LoanOrder.php`](../../app/Features/LoanOrders/Models/LoanOrder.php))

- Substituir `client()` por `entity()` — BelongsTo `Entity`
- Adicionar `deliveryLocation()` — BelongsTo `Location` (nullable)
- Adicionar `approvedBy()` — BelongsTo `User` (nullable)
- `status` cast → `LoanOrderStatus` (com 5 estados)

#### Equipment Model ([`app/Features/Equipments/Models/Equipment.php`](../../app/Features/Equipments/Models/Equipment.php))

Já tem os métodos necessários:
- [`is_loanable`](../../app/Features/Equipments/Models/Equipment.php:28) — boolean cast
- [`isAvailableForLoan()`](../../app/Core/Enums/EquipmentStatus.php:39) — retorna true só se `ACTIVE`
- [`scopeLoanable()`](../../app/Features/Equipments/Models/Equipment.php) — scope a adicionar se não existir
- [`loanOrders()`](../../app/Features/Equipments/Models/Equipment.php) — BelongsToMany a adicionar

---

### Ciclo de Vida do Empréstimo

```
PENDING ───→ APPROVED ───→ CHECKED_OUT ───→ RETURNED
  │              │
  └── CANCELLED  └── CANCELLED
```

| Transição | Quem | Condições |
|-----------|------|-----------|
| Entidade cria pedido | Entidade | Equipamentos disponíveis nas datas |
| Manager aprova | Manager (ou Admin) | Pedido em PENDING |
| Manager faz checkout | Manager (ou Admin) | Pedido em APPROVED |
| Manager regista devolução | Manager (ou Admin) | Pedido em CHECKED_OUT |
| Cancelar (PENDING) | Entidade (só seus) ou Manager | Pedido em PENDING |
| Cancelar (APPROVED) | Manager ou Admin | Pedido em APPROVED |

---

### Feature Structure

```
app/Features/Entities/                          (NOVO)
├── Models/
│   └── Entity.php                              (segue padrão de Client.php)
├── Controllers/
│   ├── Api/EntityController.php                (CRUD entities - admin only)
│   └── Web/EntityPageController.php            (dashboard Inertia)
├── Requests/
│   ├── StoreEntityRequest.php
│   └── UpdateEntityRequest.php
├── Resources/
│   └── EntityResource.php
├── Policies/
│   └── EntityPolicy.php
├── Services/
│   └── EntityService.php
├── EntityFormSchema.php
└── Routes/
    ├── api.php
    └── web.php

app/Features/LoanOrders/                        (MODIFICADO - ver docs_old/issues/01..05)
├── Models/
│   └── LoanOrder.php                           (entity_id em vez de client_id)
├── Services/
│   ├── LoanOrderService.php                    (NOVO lifecycle com 5 estados)
│   └── AvailabilityService.php                 (NOVO - validação de disponibilidade)
├── Controllers/
│   ├── Api/LoanOrderController.php
│   └── Web/LoanOrderPageController.php
├── Requests/
│   └── StoreLoanOrderRequest.php               (com dates por equipamento)
├── Resources/
│   └── LoanOrderResource.php
├── Policies/
│   └── LoanOrderPolicy.php                     (autorização complexa)
├── LoanOrderFormSchema.php
└── Routes/
    ├── api.php
    └── web.php

resources/js/Features/Entities/                 (NOVO)
├── Pages/
│   ├── EntityDashboard.jsx                     (dashboard da entidade)
│   └── LoanRequestForm.jsx                     (formulário de empréstimo)
└── Components/
    └── AvailabilityCalendar.jsx                (calendário visual)

resources/js/Features/LoanOrders/               (MODIFICADO)
├── Pages/
│   └── Index.jsx                               (também para managers verem todos)
├── Components/
│   └── LoanOrderDrawer.jsx                     (com novo lifecycle + approve/checkout/return)
└── Hooks/
    └── useLoanOrderStatus.js                   (máquina de estados no frontend)
```

---

### Formulário de Empréstimo (Entidade)

| Campo | Tipo | Origem | Notas |
|-------|------|--------|-------|
| Entidade | Texto (auto) | `auth()->user()->entityProfile` | Read-only, preenchido automaticamente |
| Equipamentos | Multi-select + Calendário | `Equipment::loanable()->available()` | Só mostra `is_loanable=true` + `status=ACTIVE` (Q16) |
| Data levantamento | Date picker (por equip.) | Preenchido pela entidade | Campo separado por equipamento |
| Data entrega | Date picker (por equip.) | Preenchido pela entidade | Campo separado por equipamento |
| Precisa operador? | Toggle (por equip.) | Preenchido pela entidade | Boolean — manager atribui workers depois (Q10) |
| Local entrega | Location selector | Opcional | Se vazio, usa `entity.location_id` (Q13) |
| Observações | Textarea | Opcional | — |

---

### Calendário de Disponibilidade

**Backend:** `GET /api/equipment/{id}/availability?from=X&to=Y`

Implementado em [`app/Features/LoanOrders/Services/AvailabilityService.php`](../../app/Features/LoanOrders/Services/AvailabilityService.php).

Verifica sobreposições de datas com 3 fontes:

1. **Loan Orders** (APPROVED ou CHECKED_OUT) — via pivot `equipment_loan_order.start_date` / `end_date`
2. **Service Orders** — via pivot `equipment_service_order` + dates da service order
3. **Mini Tasks** — via pivot `mini_task_equipment` + dates da mini task

**Algoritmo:** Dado um equipamento `$id` e um intervalo `[$from, $to]`, retorna array de dias ocupados:

```
occupado se: (start_date <= $to AND end_date >= $from)
            AND status IN (APPROVED, CHECKED_OUT) -- para loan_orders
            AND status IN (IN_PROGRESS, PLANNED)  -- para service_orders / mini_tasks
```

**Frontend:** [`AvailabilityCalendar.jsx`](../../resources/js/Features/Entities/Components/AvailabilityCalendar.jsx) — calendário visual (tipo Google Calendar) onde:
- Dias disponíveis → verde
- Dias ocupados → vermelho (com tooltip: "Reservado para [referência]")
- Dias parcialmente ocupados → laranja

---

### Operator Assignment Flow (Q10)

1. **Entidade** marca `needs_operator = true` no toggle do equipamento
2. **Manager** ao aprovar (PENDING → APPROVED) vê lista de equipamentos com `needs_operator = true`
3. **Manager** atribui Workers (quantidade e pessoas) — interface no LoanOrderDrawer
4. Sistema cria registos na tabela `loan_order_equipment_workers`:

```sql
CREATE TABLE loan_order_equipment_workers (
    id UUID PRIMARY KEY,
    loan_order_id UUID NOT NULL REFERENCES loan_orders(id),
    equipment_id UUID NOT NULL REFERENCES equipments(id),
    worker_id UUID NOT NULL REFERENCES workers(id),
    created_at TIMESTAMP
);
```

5. **Nota:** Atribuição de workers é para implementação futura (Q14 — notificações depois). Para já, o Manager pode ver os equipamentos com `needs_operator = true` mas a atribuição é manual/offline.

---

### API Contracts

```
GET    /api/entities/loans              → Lista de empréstimos da entidade logada
POST   /api/loan-orders                 → Criar pedido (StoreLoanOrderRequest)
GET    /api/loan-orders                 → Lista geral (manager vê todos)
GET    /api/loan-orders/{id}            → Detalhe do empréstimo
POST   /api/loan-orders/{id}/approve    → Aprovar (PENDING → APPROVED)
POST   /api/loan-orders/{id}/checkout   → Checkout (APPROVED → CHECKED_OUT)
POST   /api/loan-orders/{id}/return     → Devolução (CHECKED_OUT → RETURNED)
POST   /api/loan-orders/{id}/cancel     → Cancelar
GET    /api/equipment/{id}/availability  → Disponibilidade para calendário
```

#### StoreLoanOrderRequest (payload)

```json
{
    "equipments": [
        {
            "equipment_id": "uuid",
            "start_date": "2026-06-01",
            "end_date": "2026-06-05",
            "needs_operator": false
        }
    ],
    "delivery_location_id": "uuid | null",
    "description": "string (opcional)"
}
```

---

### Authorization

| Ação | Entidade | Manager | Admin |
|------|----------|---------|-------|
| Ver dashboard | ✅ (só seus) | ✅ (todos) | ✅ (todos) |
| Criar pedido | ✅ | ❌ | ❌ |
| Aprovar | ❌ | ✅ | ✅ |
| Checkout | ❌ | ✅ | ✅ |
| Devolução | ❌ | ✅ | ✅ |
| Cancelar (PENDING) | ✅ (só seus) | ✅ | ✅ |
| Cancelar (APPROVED) | ❌ | ✅ | ✅ |

Implementado em [`LoanOrderPolicy`](../../app/Features/LoanOrders/Policies/LoanOrderPolicy.php) com gates `create`, `view`, `approve`, `checkout`, `return`, `cancel`.

---

### Existing Files to Modify

| Ficheiro | O quê muda |
|----------|------------|
| [`app/Core/Enums/RoleName.php`](../../app/Core/Enums/RoleName.php:12) | + `ENTIDADE` constant |
| [`app/Core/Enums/PermissionResource.php`](../../app/Core/Enums/PermissionResource.php:19) | + `LOANS` case |
| [`app/Core/Enums/LoanOrderStatus.php`](../../app/Core/Enums/LoanOrderStatus.php) | + `APPROVED`, `RETURNED` cases |
| [`app/Shared/Models/User.php`](../../app/Shared/Models/User.php:52) | + `entityProfile()` relation |
| [`app/Features/Equipments/Models/Equipment.php`](../../app/Features/Equipments/Models/Equipment.php:68) | + `loanOrders()` BelongsToMany |
| [`app/Features/Locations/Models/Location.php`](../../app/Features/Locations/Models/Location.php) | + `entities()` HasMany (opcional) |
| [`docs_old/issues/01-schema-foundation.md`](../../docs_old/issues/01-schema-foundation.md) | Adaptar para entity_id + novo lifecycle |
| [`docs_old/issues/02-loanorder-model.md`](../../docs_old/issues/02-loanorder-model.md) | Adaptar relações |
| [`docs_old/issues/03-create-loan-backend.md`](../../docs_old/issues/03-create-loan-backend.md) | Adaptar service + request |
| [`docs_old/issues/04-loan-lifecycle-return-cancel.md`](../../docs_old/issues/04-loan-lifecycle-return-cancel.md) | Expandir lifecycle para 5 estados |
| [`docs_old/issues/05-loan-frontend-page.md`](../../docs_old/issues/05-loan-frontend-page.md) | Adaptar para managers + entidades |

---

### Deep Modules (Complexidade Alta)

| Módulo | Complexidade | Razão |
|--------|-------------|-------|
| **LoanOrderService** ([`app/Features/LoanOrders/Services/LoanOrderService.php`](../../app/Features/LoanOrders/Services/LoanOrderService.php)) | Alta | State machine com 5 transições, validação de disponibilidade no approve, pessimistic locking |
| **AvailabilityService** ([`app/Features/LoanOrders/Services/AvailabilityService.php`](../../app/Features/LoanOrders/Services/AvailabilityService.php)) | Alta | Cross-reference 3 tabelas (loan_orders, service_orders, mini_tasks) com deteção de overlap |
| **LoanOrderPolicy** ([`app/Features/LoanOrders/Policies/LoanOrderPolicy.php`](../../app/Features/LoanOrders/Policies/LoanOrderPolicy.php)) | Média | 3 roles × 6 ações = matriz de autorização |
| **AvailabilityCalendar** ([`resources/js/Features/Entities/Components/AvailabilityCalendar.jsx`](../../resources/js/Features/Entities/Components/AvailabilityCalendar.jsx)) | Média | Calendário visual com fetching async, 3 estados (verde/vermelho/laranja) |

---

## Fora de Scope (para já)

- ❌ Notificações na plataforma e email
- ❌ Atribuição automática de Workers a operadores
- ❌ Pagamentos/faturas associadas a empréstimos
- ❌ Multas por atraso na devolução
- ❌ Upload de documentos no pedido
- ❌ SLA ou prazos máximos de aprovação
- ❌ Histórico de alterações (audit trail) — já existe no sistema via `LogsAuditTrail`
- ❌ Upload de fotos do equipamento no checkout/return

---

## Testing Decisions

### Stack
- Backend: Pest PHP (integration tests) — ver [`tests/`](../../tests/)
- Frontend: React Testing Library + Jest

### O que testar

| Camada | O quê | Prioridade |
|--------|-------|------------|
| API | Cada transição de estado (5 ciclos completos) | Crítica |
| API | Validação de disponibilidade (sobreposição com loan_orders, service_orders, mini_tasks) | Crítica |
| API | Policy: entidade não aprova, manager não cria, etc. | Alta |
| API | Idempotência: duplo clique no approve/checkout/return | Alta |
| API | Cancelamento: entidade só cancela PENDING, manager cancela PENDING+APPROVED | Média |
| Frontend | Formulário: submissão com equipamentos + datas | Alta |
| Frontend | Calendário: dias vermelhos/verdes/laranja | Média |
| Frontend | Dashboard: lista de empréstimos correta por entidade | Média |

### Testes de Regressão
- Garantir que ServiceOrders e MiniTasks continuam a funcionar (não partilham código com LoanOrders)
- Garantir que o scope `isAvailableForLoan()` no EquipmentStatus não quebra

---

## Risks and Mitigations

| Risco | Mitigação |
|-------|-----------|
| Concorrência: duas entidades tentam reservar o mesmo equipamento nas mesmas datas | Usar pessimistic locking (`lockForUpdate()`) no `LoanOrderService::create()` |
| Estado inconsistente se approve falha a meio | Envolver approve + atribuição de workers numa transação |
| Calendário lento com muitos equipamentos | Cache do resultado de `/api/equipment/{id}/availability` por 5 minutos |
| Esquecer `scopeLoanable()` no formulário da entidade | Teste: entidade não vê equipamentos não-loanable |

---

## Issues (Vertical Slices)

### S-01: Foundation — Role + Enum + Entity Profile + Schema
**Backend, Database, Migration, Role**

- Adicionar [`entidade`](../../app/Core/Enums/RoleName.php) ao RoleName
- Adicionar [`EntityType`](../../app/Core/Enums/EntityType.php) enum
- Adicionar [`LOANS`](../../app/Core/Enums/PermissionResource.php) ao PermissionResource
- Atualizar [`LoanOrderStatus`](../../app/Core/Enums/LoanOrderStatus.php) para novo lifecycle (5 estados)
- Migração `create_entities_table`
- Model [`Entity`](../../app/Features/Entities/Models/Entity.php) com relações
- Adicionar [`entityProfile()`](../../app/Shared/Models/User.php) ao User
- Model factory + seeder para Entity

### S-02: Loan Order — Schema + Lifecycle + Service
**Backend, API**

- Alterar migration `loan_orders` (entity_id, delivery_location_id, approved_by/at, novo status)
- Alterar pivot `equipment_loan_order` (start_date, end_date, needs_operator)
- Criar [`StoreLoanOrderRequest`](../../app/Features/LoanOrders/Requests/StoreLoanOrderRequest.php) com validação de equipamentos + dates
- Criar [`LoanOrderService`](../../app/Features/LoanOrders/Services/LoanOrderService.php) com lifecycle (create, approve, checkout, return, cancel)
- Criar [`AvailabilityService`](../../app/Features/LoanOrders/Services/AvailabilityService.php) com `GET /api/equipment/{id}/availability`
- Atualizar [`LoanOrderPolicy`](../../app/Features/LoanOrders/Policies/LoanOrderPolicy.php)
- Atualizar [`LoanOrder`](../../app/Features/LoanOrders/Models/LoanOrder.php) model

### S-03: Entity Dashboard + Loan Form
**Frontend, Backend (Web)**

- [`EntityPageController`](../../app/Features/Entities/Controllers/Web/EntityPageController.php) com dashboard Inertia
- [`EntityDashboard.jsx`](../../resources/js/Features/Entities/Pages/EntityDashboard.jsx) com lista de empréstimos + botão "Novo Empréstimo"
- [`LoanRequestForm.jsx`](../../resources/js/Features/Entities/Pages/LoanRequestForm.jsx) com equipamentos multi-select + calendário + datas por equipamento
- [`AvailabilityCalendar.jsx`](../../resources/js/Features/Entities/Components/AvailabilityCalendar.jsx) (calendário visual)
- Rotas web para `/entidade/dashboard` + `/entidade/emprestimos/novo`

### S-04: Manager Approval Flow
**Frontend, Backend, API**

- Página [`/loan-orders`](../../resources/js/Features/LoanOrders/Pages/Index.jsx) para managers (adaptar da planeada)
- Botões de ação no [`LoanOrderDrawer`](../../resources/js/Features/LoanOrders/Components/LoanOrderDrawer.jsx) (approve, checkout, return, cancel)
- Validação de disponibilidade no approve (re-verifica se equipamento ainda livre)
- Hook [`useLoanOrderStatus.js`](../../resources/js/Features/LoanOrders/Hooks/useLoanOrderStatus.js) para máquina de estados no frontend
- Testes de lifecycle completo

### S-05: Tests + Edge Cases
**Testing**

- API tests para cada transição de estado (5 caminhos)
- Policy tests para cada role (entidade, manager, admin)
- Testes de disponibilidade (sobreposição de datas com loan_orders, service_orders, mini_tasks)
- Testes de duplo clique (idempotência — garantir que approve duas vezes não quebra)
- Testes de concorrência (duas entidades a reservar ao mesmo tempo)
- Testes de regressão (ServiceOrders e MiniTasks não afetados)

---

## Referências

- Grill-Me Session: [`docs/07-entity-loans/07-grill-me.md`](07-grill-me.md) (18 decisões)
- Planeamento Original LoanOrders: [`docs_old/issues/`](../../docs_old/issues/) (issues #28-#35)
- Modelo Cliente (padrão a seguir): [`app/Features/Clients/Models/Client.php`](../../app/Features/Clients/Models/Client.php)
- Modelo Location (reutilizado): [`app/Features/Locations/Models/Location.php`](../../app/Features/Locations/Models/Location.php)
- Equipment Status (isAvailableForLoan): [`app/Core/Enums/EquipmentStatus.php`](../../app/Core/Enums/EquipmentStatus.php)
- Permission System: [`app/Core/Services/PermissionManager.php`](../../app/Core/Services/PermissionManager.php)
