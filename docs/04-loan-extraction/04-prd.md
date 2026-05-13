# PRD: Extração de LoanOrders para CRUD Independente

**Task:** 04-logic-migration  
**Domínio:** 03-loans  
**Base:** [`04-grill-me.md`](04-grill-me.md)

---

## Problem Statement

A lógica de empréstimos ("loans") está acoplada ao módulo ServiceOrders via toggle `workflow_type`, causando:

- [`ServiceOrderService`](app/Features/ServiceOrders/Services/ServiceOrderService.php) com branches `$isLoan` em 6 métodos
- [`ServiceOrderFormSchema`](app/Features/ServiceOrders/ServiceOrderFormSchema.php) com campos condicionais (ToggleInput, equipment_ids)
- [`StoreServiceOrderRequest`](app/Features/ServiceOrders/Requests/StoreServiceOrderRequest.php) com validação bifurcada
- [`CreateLoanTasks`](app/Features/ServiceOrders/Listeners/CreateLoanTasks.php) listener fora do domínio
- Frontend [`Index.jsx`](resources/js/Features/ServiceOrders/Pages/Index.jsx) com componentes loan-only (SOEquipmentTab, EquipmentCard, ClientLocationSelector)
- [`WorkflowType`](app/Core/Enums/WorkflowType.php) enum a acoplar dois domínios distintos

Cada nova funcionalidade de loan requer alterações em ficheiros SO, criando risco de regressão.

## Solução

Extrair loans para módulo `LoanOrders` independente com tabela própria, modelo, serviço, controller, policy, form schema, rotas e páginas frontend. Tasks passam a usar MorphTo (`taskable_id` + `taskable_type`) para servir ambos os domínios.

## User Stories

1. Como gestor, quero criar uma loan order com equipamentos, cliente e localização, para registar o empréstimo de equipamento a uma entidade externa.
2. Como gestor, quero que o sistema valide a disponibilidade do equipamento antes de criar a loan, para evitar conflitos de agendamento.
3. Como gestor, quero fazer checkout de uma loan order (mudar status para CHECKED_OUT), para registar que o equipamento saiu.
4. Como gestor, quero iniciar o processo de return de uma loan order (criar task de devolução), para rastrear a devolução.
5. Como gestor, quero cancelar uma loan order e libertar os equipamentos, para anular empréstimos não concretizados.
6. Como gestor, quero ver o histórico completo de uma loan order (tasks, equipamentos, timestamps), para auditoria.
7. Como gestor, quero uma página `/loans` com listagem, filtros e CRUD independente, para gerir loans sem sair do contexto.
8. Como gestor, quero que as tasks de checkout/return sejam criadas automaticamente no ciclo de vida da loan, para reduzir trabalho manual.
9. Como gestor de equipamentos, quero que o estado do equipamento transite para IN_USE durante o loan e volte a ACTIVE após return, para manter o inventory accuracy.
10. Como gestor, quero que loans pendentes bloqueiem novos loans do mesmo equipamento (pessimistic lock), para prevenir double-booking.
11. Como administrador, quero uma política de autorização própria para loans, para definir gates independentes das service orders.
12. Como developer, quero que o módulo ServiceOrders perca toda a lógica de loans, para reduzir complexidade e risco de regressão.
13. Como developer, quero que as tasks existentes de loans sejam migradas via soft-reference, para manter o histórico sem quebrar dados.
## Implementation Decisions

## Implementation Decisions

### Deep Modules

**LoanOrderService** � encapsula todo o ciclo de vida: create (valida disponibilidade, lock pessimista, cria task checkout), initiateReturn (cria task devolucao), cancel (release equipments para ACTIVE). Interface reduzida com 3 metodos publicos.

**LoanOrderPolicy** � gates independentes: viewAny, view, create, initiateReturn, cancel. Herda BasePolicy.

### Schema Changes

Nova tabela loan_orders: id UUID PK, reference VARCHAR(20) UNIQUE (EMP/2026/0001), client_id FK, manager_id FK, location_id FK, status VARCHAR(20) DEFAULT pending, description TEXT, notes_checkout TEXT, notes_return TEXT, checked_out_at, returned_at, cancelled_at, cancelled_by FK, timestamps + soft delete.

Nova pivot equipment_loan_order: equipment_id UUID FK, loan_order_id UUID FK, PK composta, created_at.

MorphTo nas tasks: ALTER TABLE tasks ADD COLUMN taskable_id UUID, ADD COLUMN taskable_type VARCHAR(255). Dados existentes: taskable_type = ServiceOrder class. Novas loans: taskable_type = LoanOrder class.

### Ciclo de Vida

PENDING --checkout-- --return-- --cancel-- --cancel-- valida equipamentos disponiveis, lockForUpdate(), markAsInUse(), cria Task checkout (taskable_type=LoanOrder)

initiateReturn(): valida CHECKED_OUT, cria Task devolucao

cancel(): release equipments (markAsActive()), status CANCELLED

### API Contracts

POST /api/loan-orders � StoreLoanOrderRequest, retorna LoanOrderResource
GET /api/loan-orders � listagem paginada com filters
GET /api/loan-orders/{id} � detalhe com equipments + tasks
POST /api/loan-orders/{id}/return � initiateReturn
POST /api/loan-orders/{id}/cancel � cancel
DELETE /api/loan-orders/{id} � soft delete (apenas PENDING ou CANCELLED)

### Modules to Create

Backend: LoanOrder Model, LoanOrderService, LoanOrderPageController, LoanOrderController (API), StoreLoanOrderRequest, CancelLoanOrderRequest, LoanOrderPolicy, LoanOrderFormSchema, LoanOrderPresenter, LoanOrderResource, LoanOrderStatus enum, migrations (loan_orders, pivot, morph columns, soft-ref migration), routes (web + api).

Frontend: LoanOrders/Pages/Index.jsx, LoanOrders/Components/LoanOrderDrawer.jsx, LoanOrders/Components/EquipmentCard.jsx, sidebar entry.

### Modules to Modify

ServiceOrderService: remover branches , initiateReturn(), releaseEquipment().
ServiceOrderFormSchema: remover ToggleInput, SelectInput equipment_ids.
StoreServiceOrderRequest: simplificar para standard-only.
ServiceOrder Model: remover workflow_type, equipments(), tasks muda para MorphMany.
Equipment Model: adicionar loanOrders() BelongsToMany.
CreateLoanTasks: ELIMINAR (logica movida para LoanOrderService).
WorkflowType enum: DEPRECAR.
Task Model: adicionar taskable() MorphTo.
Frontend ServiceOrders/Index.jsx: remover isLoan tabs, SOEquipmentTab, EquipmentCard.
Frontend TaskTreeNode.jsx: remover showReturnBtn.

### Migration Strategy: Soft-Reference

1. Migracao cria tabela loan_orders + pivot + morph columns
2. Script copia loans existentes (workflow_type=loan) para loan_orders
3. Tasks existentes recebem taskable_id = service_order_id, taskable_type = ServiceOrder class
4. ServiceOrders originais recebem flag migrated_to_loan_id
5. Periodo de co-existence: ambas as UIs funcionam
6. Cleanup: remover toggle UI, remover workflow_type da tabela SOs

## Testing Decisions

All modules will be tested. Prior art in codebase: ServiceOrderApiTest, ServiceOrderPoliciesTest, cascade tests.

**LoanOrderServiceTest** � ciclo de vida completo: create (PENDING), initiateReturn (cria Task), cancel (CANCELLED + equipment released). Cenarios de erro: equipamento indisponivel, duplo cancel, cancel de CHECKED_OUT, lock concorrente.

**LoanOrderPolicyTest** � gates por role: admin full access, manager com create/initiateReturn, worker sem acesso.

**LoanOrderApiTest** � CRUD endpoints: 200/201/401/403/422 responses.

**Frontend test** � renderizacao da pagina /loans, formulario create com validacao, workflow tabs.

**Migration test** � soft-ref copia dados corretamente, rollback funcional.

## Out of Scope

- Loan portal para entidades externas (Task 7 � 07-loan-portal)
- Sistema de multas por atraso na devolucao
- Notificacoes email/SMS para loans
- Relatorios/analytics especificos de loans
- Integracao com sistemas externos de inventory

## Further Notes

- Tasks ja existentes com service_order_id serao preenchidas com taskable_id = service_order_id e taskable_type = ServiceOrder class, garantindo retrocompatibilidade.
- EquipmentStatus transitions (ACTIVE-, IN_USE- mantem-se no model Equipment.
- O prefixo EMP distingue visualmente loans de service orders (OS).
- Todo o codigo segue DDD: app/Features/LoanOrders/ agrupa modelo, servico, controller, policy, requests, resources.
- A pagina frontend segue o padrao WorkspaceDrawer com tabs de detalhe (equipment tab, tasks tab, history tab).
