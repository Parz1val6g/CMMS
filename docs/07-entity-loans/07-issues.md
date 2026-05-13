# Issues — Sistema de Empréstimos para Entidades

**Parent PRD:** [`07-prd.md`](07-prd.md)
**Grill-Me:** [`07-grill-me.md`](07-grill-me.md)

| Issue | Título | Tipo | Blocked by |
|-------|--------|------|------------|
| #001 | Foundation — Role + Schema + Entity Model | HITL | — |
| #002 | Entity Dashboard (Read-only) | AFK | #001 |
| #003 | Availability Calendar + Service | AFK | #002 |
| #004 | Create Loan Request | AFK | #003 |
| #005 | Manager Approval Flow | AFK | #004 |
| #006 | Tests + Edge Cases | AFK | #005 |

---

## ISSUE-001: Foundation — Role + Schema + Entity Model

**Type:** HITL (schema decisions need human review)
**Blocked by:** None — can start immediately

### Parent

[`07-prd.md`](07-prd.md) — secções "Domain Model Changes" + "Model Changes"

### What to build

Criar a base de dados, enums e modelos para suportar o role `entidade` e os empréstimos com novo ciclo de vida.

**Schema decisions to review:**
- `entities` table: `user_id`, `entity_type` (enum), `nif` (unique), `name`, `phone`, `location_id` (FK)
- `loan_orders`: replace `client_id` with `entity_id` (FK), add `delivery_location_id` (FK, nullable), `approved_by` (FK, nullable), `approved_at` (nullable)
- `equipment_loan_order` pivot: add `start_date` (DATE), `end_date` (DATE), `needs_operator` (BOOLEAN, default false)
- `loan_order_equipment_workers` (nova): `loan_order_id`, `equipment_id`, `worker_id` (para assignment futuro)

**State machine (enums):**
```php
enum LoanOrderStatus: string {
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case CHECKED_OUT = 'checked_out';
    case RETURNED = 'returned';
    case CANCELLED = 'cancelled';
}

enum EntityType: string {
    case MUNICIPAL_COUNCIL = 'municipal_council';
    case PARISH_COUNCIL = 'parish_council';
    case OTHER = 'other';
}
```

### Acceptance criteria

- [ ] `entities` table created with all columns + FKs
- [ ] `loan_orders` migration altered: entity_id replaces client_id, new columns added
- [ ] `equipment_loan_order` pivot has start_date, end_date, needs_operator
- [ ] `LoanOrderStatus` enum with 5 cases + helper methods (isPending, isActive, isTerminal)
- [ ] `EntityType` enum with 3 cases
- [ ] `RoleName::ENTIDADE` constant added
- [ ] `PermissionResource::LOANS` case added
- [ ] `Entity` model with `user()`, `location()`, `loanOrders()` relations
- [ ] `User::entityProfile()` relation added (paralela a clientProfile, workerProfile)
- [ ] `LoanOrder` model updated: `entity()` replaces `client()`, + `deliveryLocation()`, `approvedBy()`
- [ ] Factory + Seeder for Entity
- [ ] `php artisan migrate:fresh --seed` succeeds without errors
- [ ] Rollback works (down methods)

---

## ISSUE-002: Entity Dashboard (Read-only)

**Type:** AFK
**Blocked by:** ISSUE-001

### Parent

[`07-prd.md`](07-prd.md) — secção "Feature Structure" (Entity Dashboard) + User Story #1

### What to build

Um dashboard read-only para entidades autenticadas com role `entidade`. A entidade faz login, vê um resumo dos seus empréstimos (contadores: pendentes, ativos, concluídos, cancelados) e uma lista com os empréstimos existentes. Ainda não pode criar novos — apenas visualizar.

**API:**
- `GET /api/entities/loans` — retorna loan_orders belonging to `auth()->user()->entityProfile`
- Resource inclui: reference, status, equipment names, date range, delivery location

**Frontend:**
- Rota web: `/entidade/dashboard`
- `EntityDashboard.jsx`:
  - KPI cards (pendentes, ativos, concluídos, cancelados)
  - Tabela de empréstimos com colunas: referência, estado, equipamentos, datas, ações (ver detalhe)
  - Botão "Novo Empréstimo" desabilitado com tooltip "Disponível em breve"
- Sidebar/Layout: menu específico para entidade (dashboard, empréstimos)

**Middleware:**
- Verificar que o user logado tem role `entidade`
- Redirecionar se não tiver permissão

### Acceptance criteria

- [ ] Entity logs in and sees `/entidade/dashboard` with KPI cards
- [ ] `GET /api/entities/loans` returns only loans belonging to the authenticated entity
- [ ] Dashboard shows empty state when no loans exist
- [ ] Manager/admin can access all entity dashboards
- [ ] Entity cannot access `/loan-orders` (manager page)
- [ ] Non-entity user gets 403 on `/entidade/dashboard`

---

## ISSUE-003: Availability Calendar + Service

**Type:** AFK
**Blocked by:** ISSUE-002

### Parent

[`07-prd.md`](07-prd.md) — secção "Calendário de Disponibilidade" + User Stories #3, #10

### What to build

Serviço de disponibilidade que verifica se um equipamento está livre num intervalo de datas, e calendário visual para a entidade consultar.

**Backend — `AvailabilityService`:**
- `GET /api/equipment/{id}/availability?from=X&to=Y`
- Cross-reference 3 fontes de ocupação:
  1. **Loan Orders** (APPROVED ou CHECKED_OUT) — via pivot `equipment_loan_order.start_date` / `end_date`
  2. **Service Orders** — via pivot `equipment_service_order` + service order dates
  3. **Mini Tasks** — via pivot `mini_task_equipment` + mini task dates
- Algoritmo de overlap: `(start_date <= $to AND end_date >= $from)`
- Resposta: `{ "occupied_days": ["2026-06-01", "2026-06-02"], "source": "loan_order_ref" }`
- Cache do resultado por 5 minutos (TTL)

**Frontend — `AvailabilityCalendar.jsx`:**
- Componente reutilizável (recebe equipment_id + props)
- Calendário visual tipo Google Calendar
- 3 cores:
  - 🟢 Verde — disponível
  - 🔴 Vermelho — ocupado (tooltip: "Reservado para EMP/2026/0001")
  - 🟠 Laranja — parcialmente ocupado (só alguns dias no intervalo)
- Loading state enquanto busca dados
- Integração com o form (quando seleciona equipamento, mostra calendário)

**Equipment scope:**
- `Equipment::query()->loanable()->where('status', EquipmentStatus::ACTIVE)`
- `scopeLoanable()` — `where('is_loanable', true)`

### Acceptance criteria

- [ ] `GET /api/equipment/{id}/availability` returns occupied days within date range
- [ ] Overlap detection works for loan_orders (APPROVED + CHECKED_OUT)
- [ ] Overlap detection works for service_orders
- [ ] Overlap detection works for mini_tasks
- [ ] Empty response when no overlap exists
- [ ] 404 when equipment not found or not loanable
- [ ] Calendar renders green/red/orange days correctly
- [ ] Tooltip shows reference of conflicting reservation
- [ ] Cache returns stale data within TTL (no extra DB queries)
- [ ] Loading state shown while fetching

---

## ISSUE-004: Create Loan Request

**Type:** AFK
**Blocked by:** ISSUE-003

### Parent

[`07-prd.md`](07-prd.md) — secção "Formulário de Empréstimo" + "API Contracts" + User Stories #2, #4, #5

### What to build

Formulário completo para a entidade criar um pedido de empréstimo. Integra o calendário de disponibilidade (ISSUE-003) para validação visual e server-side.

**API — `POST /api/loan-orders`:**
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

**Validações:**
- `entity_id` = auth()->user()->entityProfile->id (auto)
- Cada equipment_id deve existir, ser loanable e ACTIVE
- `start_date` >= hoje, `end_date` >= `start_date`
- Disponibilidade: re-verificar no servidor com `AvailabilityService`
- `delivery_location_id` opcional — se null, usa `entity.location_id`

**Server-side (`LoanOrderService::create()`):**
```text
1. Begin transaction
2. Validate all equipments exist + are loanable + active
3. Validate dates per equipment (no overlap with existing reservations)
4. Lock equipment rows with lockForUpdate()
5. Create LoanOrder with status PENDING
6. Attach equipments via pivot with dates + needs_operator
7. Generate reference (EMP/2026/XXXX)
8. Commit
9. Return LoanOrderResource
```

**Frontend — `LoanRequestForm.jsx`:**
- Dados da entidade pré-preenchidos (read-only)
- Multi-select de equipamentos (só loanable + ACTIVE)
- Por cada equipamento selecionado:
  - Date picker start + end
  - Toggle needs_operator
  - AvailabilityCalendar integrado
- Select de local de entrega (opcional, com search)
- Textarea de observações
- Botão "Submeter Pedido"
- Success/error toast after submission

**Rota web:** `/entidade/emprestimos/novo`

### Acceptance criteria

- [ ] `POST /api/loan-orders` creates loan with status PENDING (201)
- [ ] Entity data auto-filled from auth profile (read-only)
- [ ] Only loanable + ACTIVE equipments shown in multi-select
- [ ] Date validation: start >= today, end >= start (422 otherwise)
- [ ] Availability re-verified server-side (409 if occupied)
- [ ] Pessimistic locking prevents double-booking
- [ ] `delivery_location_id` defaults to entity's location when null
- [ ] Created loan appears in dashboard list (ISSUE-002)
- [ ] Form shows loading state during submission
- [ ] Validation errors shown inline per field
- [ ] Non-entity user gets 403

---

## ISSUE-005: Manager Approval Flow

**Type:** AFK
**Blocked by:** ISSUE-004

### Parent

[`07-prd.md`](07-prd.md) — secção "Ciclo de Vida" + "Authorization" + User Stories #6, #7, #8, #9

### What to build

Fluxo completo de aprovação para managers: ver pedidos pendentes, aprovar, fazer checkout, registar devolução, cancelar.

**Lifecycle endpoints:**

```
POST /api/loan-orders/{id}/approve    → PENDING → APPROVED
POST /api/loan-orders/{id}/checkout   → APPROVED → CHECKED_OUT
POST /api/loan-orders/{id}/return     → CHECKED_OUT → RETURNED
POST /api/loan-orders/{id}/cancel     → PENDING/APPROVED → CANCELLED
```

**Server-side (`LoanOrderService`):**

`approve()`:
1. Validate status is PENDING
2. Re-verify availability (equipment may have been booked since creation)
3. Set approved_by = auth()->user, approved_at = now()
4. Update status to APPROVED
5. If any equipment has needs_operator=true, flag for worker assignment (future)

`checkout()`:
1. Validate status is APPROVED
2. Check loan order is within date range
3. Update status to CHECKED_OUT, set checked_out_at = now()

`return()`:
1. Validate status is CHECKED_OUT
2. Update status to RETURNED, set returned_at = now()

`cancel()`:
1. Validate canCancel (status PENDING or APPROVED)
2. Entity can only cancel PENDING and only their own loans
3. Manager/admin can cancel PENDING or APPROVED
4. Set cancelled_by = auth()->user, cancelled_at = now()

**Policy (`LoanOrderPolicy`):**
```text
view(entity, loanOrder)    → entity owns loan OR user is manager/admin
create(entity)              → user has entidade role
approve(user, loanOrder)    → user is manager/admin AND status is PENDING
checkout(user, loanOrder)   → user is manager/admin AND status is APPROVED
return(user, loanOrder)     → user is manager/admin AND status is CHECKED_OUT
cancel(user, loanOrder)     → (entity owns AND PENDING) OR (manager AND PENDING/APPROVED)
```

**Frontend — `LoanOrderDrawer.jsx`:**
- Adaptar drawer existente para incluir novo lifecycle
- Estado "PENDING": botões Approve (manager) / Cancel (entity + manager)
- Estado "APPROVED": botão Checkout (manager) / Cancel (manager only)
- Estado "CHECKED_OUT": botão Return (manager)
- Estado "RETURNED": detalhes apenas (read-only)
- Estado "CANCELLED": detalhes + reason (read-only)
- Para equipamentos com needs_operator=true: badge "Requer operador"

**Hook `useLoanOrderStatus.js`:**
- Máquina de estados no frontend
- Controla quais botões são visíveis por role + status
- Confirmação antes de cada ação (modal "Tem a certeza?")

**Página managers:**
- `/loan-orders` com filtro por status (default: PENDING)
- Para managers: coluna "Entidade" visível
- Para entidade: esconde coluna (só vê os seus)

### Acceptance criteria

- [ ] Manager approves PENDING → APPROVED (200)
- [ ] Manager re-verifies availability on approve (409 if now occupied)
- [ ] Manager does checkout APPROVED → CHECKED_OUT (200)
- [ ] Manager does return CHECKED_OUT → RETURNED (200)
- [ ] Manager cancels PENDING or APPROVED (200)
- [ ] Entity cancels own PENDING loan (200)
- [ ] Entity cannot cancel APPROVED loan (403)
- [ ] Entity cannot cancel another entity's loan (403)
- [ ] Entity cannot approve/checkout/return (403)
- [ ] Invalid state transitions return 422
- [ ] Double-click idempotent (second call returns error, doesn't double-transition)
- [ ] Drawer shows correct buttons per status + role
- [ ] `/loan-orders` default filter shows PENDING
- [ ] `needs_operator` equipments flagged in drawer

---

## ISSUE-006: Tests + Edge Cases

**Type:** AFK
**Blocked by:** ISSUE-005

### Parent

[`07-prd.md`](07-prd.md) — secção "Testing Decisions" + User Story #10

### What to build

Suite de testes para cobrir todos os cenários do sistema de empréstimos para entidades.

### Test files

#### 1. API Integration Tests (`tests/Feature/Api/LoanOrderApiTest.php`)

**Create:**
- [ ] Entity creates loan with valid data → 201
- [ ] Entity creates loan with occupied equipment → 409
- [ ] Entity creates loan with invalid equipment_id → 422
- [ ] Entity creates loan with past start_date → 422
- [ ] Entity creates loan with end < start → 422
- [ ] Non-entity creates loan → 403
- [ ] Duplicate submission (same data sent twice) → second fails (idempotency)

**Lifecycle (5 full cycles):**
- [ ] PENDING → APPROVED → CHECKED_OUT → RETURNED
- [ ] PENDING → APPROVED → CHECKED_OUT → CANCELLED
- [ ] PENDING → APPROVED → CANCELLED
- [ ] PENDING → CANCELLED
- [ ] Invalid transitions (e.g., PENDING → RETURNED) → 422

**Availability:**
- [ ] `GET /api/equipment/{id}/availability` returns correct occupied days
- [ ] Overlap with existing loan_order (APPROVED)
- [ ] Overlap with existing loan_order (CHECKED_OUT)
- [ ] Overlap with service_order
- [ ] Overlap with mini_task
- [ ] No overlap → empty array
- [ ] Out of range dates → empty array
- [ ] Non-loanable equipment → 404 (or empty)

#### 2. Policy Tests (`tests/Feature/Authorization/LoanOrderPoliciesTest.php`)

- [ ] Entity can view own loan
- [ ] Entity cannot view another entity's loan
- [ ] Manager can view any loan
- [ ] Entity can cancel own PENDING loan
- [ ] Entity cannot cancel own APPROVED loan
- [ ] Manager can cancel any PENDING loan
- [ ] Manager can cancel any APPROVED loan
- [ ] Entity cannot approve/checkout/return
- [ ] Manager can approve/checkout/return
- [ ] Admin can do everything

#### 3. Service Tests (`tests/Feature/LoanOrders/LoanOrderServiceTest.php`)

- [ ] `create()` with pessimistic locking prevents double-booking
- [ ] `approve()` re-verifies availability
- [ ] `approve()` fails if equipment became occupied since creation
- [ ] Transaction rollback on any failure
- [ ] Reference generation format (EMP/2026/XXXX)
- [ ] Concurrent requests handled correctly

#### 4. Frontend Tests

- [ ] Dashboard renders KPI cards with correct counts
- [ ] Dashboard shows empty state
- [ ] Create form validates dates client-side
- [ ] Calendar shows correct colors
- [ ] LoanOrderDrawer shows correct buttons per status
- [ ] Confirmation modal appears before state transitions

#### 5. Regression Tests

- [ ] ServiceOrders and MiniTasks functionality unchanged
- [ ] `EquipmentStatus::isAvailableForLoan()` still returns correct values
- [ ] Existing Client/Worker features unaffected

### Acceptance criteria

- [ ] `php artisan test` passes with all new tests
- [ ] No flaky tests (deterministic assertions)
- [ ] Coverage includes: 5 lifecycle paths, 3 overlap sources, all policy gates
- [ ] Idempotency tests pass (double-click protection)
- [ ] Regression tests pass (existing features unaffected)
