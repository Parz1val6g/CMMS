# User Stories: Service Orders, Tasks, MiniTasks & Work Logs

---

## 🔄 Workflow Types

The Service Order module supports two distinct workflow types, determined by the [`workflow_type`](database/migrations/2026_05_04_090300_add_equipment_id_and_workflow_type_to_service_orders_table.php:13) column (`VARCHAR(50)`, default `'regular'`) on the `service_orders` table.

### Regular Workflow

- Standard SO for general maintenance, repairs, and construction work.
- No restriction on the number or naming of tasks — any task name and count is allowed.
- Materials are tracked via `work_logs_materials` with quantity-based deductions from stock.
- The [`equipment_id`](database/migrations/2026_05_04_090300_add_equipment_id_and_workflow_type_to_service_orders_table.php:18) column is `NULL`.

### Loan (Empréstimo) Workflow

- Specialized SO for equipment loan/commodato agreements.
- **Binary Task Rule**: A Loan SO MUST have exactly two tasks:
  1. **"Empréstimo de Equipamento"** — Tracks the loan-out of equipment.
  2. **"Devolução de Equipamento"** — Tracks the return of equipment.
- No additional tasks may be created on a Loan SO.
- Materials tab is treated as **priority** — equipment is tracked via `work_log_equipment` instead of `work_logs_materials`.
- The [`equipment_id`](database/migrations/2026_05_04_090300_add_equipment_id_and_workflow_type_to_service_orders_table.php:18) references the loaned equipment.
- The completion of the **"Devolução de Equipamento"** task is the trigger for closing the SO (see [State Machine — Loan Flow](documentation/user_stories/diagrams/state_machines/01_SERVICE_ORDER_LIFECYCLE.md)).

---

## 📋 US-081: Criar Ordem de Serviço (Service Order)

**Como** manager,  
**Eu quero** registar uma nova ordem de serviço,  
**Para que** eu possa organizar trabalho a ser realizado.

### Critérios de Aceitação
- ✅ POST /service-orders com:
  - process (description, max 250 chars)
  - client_id (optional, null se não registado)
  - manager_id (current user ou manager_id if admin)
  - location_id (required)
  - service_type_id (optional)
  - priority (required: urgent, high, normal, low)
  - execution_date (optional, default today)
- ✅ Status: pending (default)
- ✅ Client pode não estar registado (anónimo)
- ✅ Se location não existe: criar inline (opcional)
- ✅ Auditoria: criador, timestamp
- ✅ Notificação: enviar a manager e clients (se registado)

### Exemplo
```
POST /service-orders
{
  "process": "Reparação do buraco na Rua Principal",
  "client_id": null,
  "manager_id": "mgr-001",
  "location_id": "loc-001",
  "service_type_id": "st-trolhas",
  "priority": "urgent",
  "execution_date": "2026-04-24"
}
```

---

## 📋 US-082: Listar Ordens de Serviço

**Como** manager,  
**Eu quero** ver minhas ordens de serviço,  
**Para que** eu possa acompanhar trabalho.

### Critérios de Aceitação
- ✅ GET /service-orders com paginação
- ✅ Filtros:
  - status (pending, active, completed, cancelled)
  - priority (urgent, high, normal, low)
  - date_range (execution_date)
  - manager_id (se admin)
  - location_id, client_id
  - search (process description)
- ✅ Campos: id, process, client, location, priority, status, created_at, progress (%)
- ✅ Authorization: manager vê suas ordens, admin vê todas
- ✅ Ordenação: default por priority desc + execution_date asc

---

## 📋 US-083: Visualizar Detalhes de Ordem de Serviço

**Como** manager,  
**Eu quero** ver informações completas de uma ordem,  
**Para que** eu possa revisar todos detalhes.

### Critérios de Aceitação
- ✅ GET /service-orders/{id} retorna:
  - Dados básicos: processo, cliente, localização, tipo
  - Prioridade, status, datas (criação, execução)
  - Manager responsável
  - Todas tasks associadas (com status)
  - Progress: % concluído (based on work_logs)
  - Materiais utilizados (sum from work_logs)
  - Horas totais (sum duration_minutes)
  - Attachments (fotos, documentos)
- ✅ Timeline: histórico de mudanças de status
- ✅ Cache: 5 min (invalida ao atualizar)

---

## 📋 US-084: Editar Ordem de Serviço

**Como** manager ou admin,  
**Eu quero** atualizar informações de uma ordem,  
**Para que** dados fiquem corretos.

### Critérios de Aceitação
- ✅ PUT /service-orders/{id} com: process, priority, execution_date, status
- ✅ Editável apenas se status = pending ou active
- ✅ Se status concluído: não editável (apenas admin pode force)
- ✅ Auditoria: mudanças registadas
- ✅ Authorization: owner ou admin

---

## 📋 US-085: Mudar Status de Ordem de Serviço

**Como** manager,  
**Eu quero** atualizar status de uma ordem,  
**Para que** eu possa acompanhar progresso.

### Critérios de Aceitação
- ✅ POST /service-orders/{id}/change-status com: status
- ✅ Transições permitidas:
  - pending → active
  - active → completed (apenas se ALL tasks completed)
  - active → cancelled
  - completed → active (re-open, admin only)
- ✅ Validação: se status=completed, TODAS tasks devem estar completed
- ✅ Auditoria: quem mudou, de/para status, timestamp
- ✅ Notificações: manager + client (se registado)

---

## 📋 US-086: Deletar Ordem de Serviço (Soft Delete)

**Como** manager ou admin,  
**Eu quero** remover uma ordem de serviço,  
**Para que** eu possa limpar dados.

### Critérios de Aceitação
- ✅ DELETE /service-orders/{id} (soft delete)
- ✅ Validação: se status=active COM tasks open → erro (não permitir)
- ✅ Se status=completed: permitir (dados preservados)
- ✅ Auditoria: quem deletou, quando
- ✅ Authorization: owner ou admin

---

## 📋 US-087: Anexar Ficheiro a Ordem de Serviço

**Como** manager,  
**Eu quero** carregar fotos/documentos da ordem,  
**Para que** eu possa documentar trabalho.

### Critérios de Aceitação
- ✅ POST /service-orders/{id}/attachments (multipart form, file upload)
- ✅ Formatos aceites: PDF, JPEG, PNG, DOCX (customizável)
- ✅ Max file size: 10 MB
- ✅ Armazenar em: storage/service-orders/{order-id}/
- ✅ Auditoria: file_name, file_path, uploaded_by, timestamp

---

## 🎯 US-088: Criar Task (Divisão de Ordem em Setores)

**Como** manager,  
**Eu quero** criar tasks para diferentes setores,  
**Para que** eu possa atribuir trabalho especializado.

### Critérios de Aceitação
- ✅ POST /tasks com:
  - service_order_id (required)
  - manager_id (current user)
  - name (required, max 150)
  - sectors (M:M assignment via tasks_sectors)
- ✅ Status: pending (default)
- ✅ Auditoria: criador, timestamp
- ✅ Notificações: sector heads

### Exemplo (ordem com 3 tarefas)
```
Task 1: "Preparação do local" → [Trolhas, Esgotos]
Task 2: "Trabalho eletricidade" → [Eletricistas]
Task 3: "Acabamentos" → [Trolhas]
```

---

## 🎯 US-089: Listar Tasks

**Como** manager ou supervisor,  
**Eu quero** ver tasks de uma ordem,  
**Para que** eu possa acompanhar divisão do trabalho.

### Critérios de Aceitação
- ✅ GET /tasks com filtros:
  - service_order_id (required context)
  - status
  - manager_id (se admin)
- ✅ Campos: id, name, sectors, status, mini_tasks_count, progress (%)
- ✅ Paginação: 20 por página

---

## 🎯 US-090: Visualizar Detalhes de Task

**Como** manager,  
**Eu quero** ver informações completas de uma task,  
**Para que** eu possa revisar execução.

### Critérios de Aceitação
- ✅ GET /tasks/{id} retorna:
  - Nome, ordem, manager, setores
  - Status, data criação
  - Todas mini-tasks (com status e assignments)
  - Progress: % concluído
  - Materiais utilizados (sum)
  - Horas (sum)
  - Histórico de mudanças

---

## 🎯 US-091: Mudar Status de Task

**Como** manager,  
**Eu quero** atualizar status de uma task,  
**Para que** eu possa acompanhar.

### Critérios de Aceitação
- ✅ POST /tasks/{id}/change-status com: status
- ✅ Transições:
  - pending → in_progress
  - in_progress → completed (se TODAS mini-tasks completed)
  - in_progress → blocked (com motivo)
  - blocked → in_progress
  - * → cancelled
- ✅ Validação: se completed, TODAS mini-tasks devem estar completed
- ✅ Auditoria: mudanças registadas

---

## 🎯 US-092: Deletar Task

**Como** admin,  
**Eu quero** remover uma task,  
**Para que** eu possa corrigir erros.

### Critérios de Aceitação
- ✅ DELETE /tasks/{id} (soft delete)
- ✅ Validação: se tem mini-tasks open → erro
- ✅ Se todas mini-tasks closed: permitir
- ✅ Auditoria: quem deletou

---

## 🔧 US-093: Criar MiniTask (Atribuição a Grupos/Trabalhadores)

**Como** sector head (supervisor),  
**Eu quero** criar mini-tasks e atribuir a grupos/trabalhadores,  
**Para que** trabalho seja distribuído.

### Critérios de Aceitação
- ✅ POST /mini-tasks com:
  - task_id (required)
  - supervisor_id (current user, sector head)
  - description (required, max 250)
  - assignments: [ { worker_id OR team_id } ] (M:M, multiple)
  - planned_materials: [ { material_id, planned_quantity } ] (optional)
- ✅ Status: pending (default)
- ✅ MÚLTIPLOS workers/teams podem ser atribuídos (Grupo1 + António)
- ✅ Auditoria: criador, assignments
- ✅ Notificações: workers/team heads

### Exemplo (MT1 com Grupo1 + António)
```
POST /tasks/tsk-001/mini-tasks
{
  "description": "Preparar alcatrão",
  "supervisor_id": "sup-001",
  "assignments": [
    { "team_id": "team-group1" },
    { "worker_id": "wrk-antonio" }
  ],
  "planned_materials": [
    { "material_id": "mat-asphalt", "planned_quantity": 500 }
  ]
}
```

---

## 🔧 US-094: Listar MiniTasks

**Como** worker ou supervisor,  
**Eu quero** ver minhas mini-tasks,  
**Para que** eu possa começar trabalho.

### Critérios de Aceitação
- ✅ GET /mini-tasks com filtros:
  - task_id (context)
  - worker_id ou team_id (current user/team)
  - status (pending, in_progress, completed)
- ✅ Campos: id, description, status, assignments (workers/teams), deadline
- ✅ Paginação: 20 por página
- ✅ Workers veem suas; supervisors veem todas do sector

---

## 🔧 US-095: Visualizar Detalhes de MiniTask

**Como** worker,  
**Eu quero** ver detalhes completos da minha mini-task,  
**Para que** eu possa executar corretamente.

### Critérios de Aceitação
- ✅ GET /mini-tasks/{id} retorna:
  - Descrição, status, supervisor
  - Ordem/Task/Setor context
  - Assignees (workers/teams)
  - Materiais planejados (qty, unit)
  - Work logs criados (com status)
  - Progress: % concluído (based on work_logs approved)
  - Histórico

---

## 🔧 US-096: Editar MiniTask

**Como** supervisor,  
**Eu quero** atualizar mini-task antes de começar,  
**Para que** eu possa corrigir informações.

### Critérios de Aceitação
- ✅ PUT /mini-tasks/{id} com: description, planned_materials, assignments
- ✅ Editável apenas se status = pending
- ✅ Se status in_progress: apenas supervisor pode force
- ✅ Auditoria: mudanças registadas

---

## 🔧 US-097: Atribuir Worker/Team a MiniTask

**Como** supervisor,  
**Eu quero** atribuir (mais) worker(s) ou team(s) a uma mini-task,  
**Para que** mais pessoas possam trabalhar.

### Critérios de Aceitação
- ✅ POST /mini-tasks/{id}/assign com: worker_id OR team_id
- ✅ Múltiplas atribuições permitidas (Grupo1 + António)
- ✅ Validação: worker/team válido, não já atribuído
- ✅ Auditoria: quem atribuiu, quando
- ✅ Notificação: worker/team head

---

## 🔧 US-098: Remover Atribuição Worker/Team de MiniTask

**Como** supervisor,  
**Eu quero** remover worker/team de uma mini-task,  
**Para que** eu possa reatribuir.

### Critérios de Aceitação
- ✅ DELETE /mini-tasks/{id}/assignments/{assignmentId}
- ✅ Validação: se tem work_logs submitted → warning (não permitir até concluir)
- ✅ Auditoria: remoção registada

---

## 🔧 US-099: Adicionar Material Planejado a MiniTask

**Como** supervisor,  
**Eu quero** especificar materiais que serão usados,  
**Para que** eu possa planejar e comparar com uso real.

### Critérios de Aceitação
- ✅ POST /mini-tasks/{id}/materials com: material_id, planned_quantity
- ✅ Material_id: válido, em stock
- ✅ Planned_quantity: decimal, > 0
- ✅ Auditoria: adição registada
- ✅ Notificação: se material está low stock

---

## 🔧 US-100: Remover Material Planejado de MiniTask

**Como** supervisor,  
**Eu quero** remover material planejado,  
**Para que** eu possa corrigir planejamento.

### Critérios de Aceitação
- ✅ DELETE /mini-tasks/{id}/materials/{materialId}
- ✅ Auditoria: remoção registada

---

## 🔧 US-101: Mudar Status de MiniTask

**Como** supervisor,  
**Eu quero** atualizar status de uma mini-task,  
**Para que** eu possa acompanhar progresso.

### Critérios de Aceitação
- ✅ POST /mini-tasks/{id}/change-status com: status
- ✅ Transições:
  - pending → in_progress (quando worker começa)
  - in_progress → completed_pending_approval (quando TODOS work_logs approved)
  - completed_pending_approval → completed (supervisor final "yes")
  - in_progress → blocked (com motivo)
  - * → cancelled
- ✅ Validação automática: se all work_logs approved → sugerir "pending approval"
- ✅ Auditoria: mudanças registadas
- ✅ Notificações: supervisor quando pronto para aprovação

---

## 🔧 US-102: Aprovar Conclusão de MiniTask (Supervisor)

**Como** supervisor,  
**Eu quero** dar aprovação final de uma mini-task,  
**Para que** ela fique concluída.

### Critérios de Aceitação
- ✅ POST /mini-tasks/{id}/approve-completion
- ✅ Validação: TODOS work_logs devem estar approved (status=approved)
- ✅ Status muda: completed_pending_approval → completed
- ✅ Auditoria: quem aprovou, quando
- ✅ Notificação: workers, task manager
- ✅ Sistema valida cascata: se TODAS mini-tasks completed → task pode ser marcada completed

---

## 📝 US-103: Criar Work Log (Registar Trabalho Realizado)

**Como** worker,  
**Eu quero** registar trabalho realizado numa mini-task,  
**Para que** eu possa documentar horas e materiais.

### Critérios de Aceitação
- ✅ POST /work-logs com:
  - mini_task_id (required)
  - started_at (required, timestamp)
  - completed_at (required, timestamp, > started_at)
  - description (required, max 250)
  - materials_used: [ { material_id, quantity_used, unit_price_at_use (optional) } ]
  - workers: [ { worker_id } ] (who else participated)
- ✅ Status: draft (default)
- ✅ Duration_minutes: auto-calculated (completed_at - started_at)
- ✅ Stock deduzido IMEDIATAMENTE (transação atómica)
- ✅ Se stock não suficiente: erro (rollback)
- ✅ Auditoria: criador, timestamp
- ✅ Notificação: supervisor

### Exemplo
```
POST /mini-tasks/mt-001/work-logs
{
  "started_at": "2026-04-24 15:00:00",
  "completed_at": "2026-04-24 16:00:00",
  "description": "Preparação alcatrão grupo1",
  "materials_used": [
    { "material_id": "mat-asphalt", "quantity_used": 100, "unit_price_at_use": 50.00 }
  ],
  "workers": [
    { "worker_id": "wrk-001" },
    { "worker_id": "wrk-002" }
  ]
}
```

---

## 📝 US-104: Listar Work Logs

**Como** supervisor ou worker,  
**Eu quero** ver work logs de uma mini-task,  
**Para que** eu possa revisar execução.

### Critérios de Aceitação
- ✅ GET /work-logs com filtros:
  - mini_task_id (context)
  - status (draft, submitted, approved, rejected)
  - date_range
  - worker_id (se supervisor)
- ✅ Campos: id, mini_task, started_at, completed_at, duration, status, author
- ✅ Paginação: 50 por página

---

## 📝 US-105: Visualizar Detalhes de Work Log

**Como** supervisor ou worker,  
**Eu quero** ver detalhes completos de um work log,  
**Para que** eu possa auditar.

### Critérios de Aceitação
- ✅ GET /work-logs/{id} retorna:
  - Datas, duração, descrição
  - Materiais utilizados (qty, preço, subtotal)
  - Workers que participaram
  - Status, criador, timestamps
  - Comparação planned vs actual (materiais)
  - Histórico de mudanças (approved/rejected)

---

## 📝 US-106: Editar Work Log (Draft Status Apenas)

**Como** worker,  
**Eu quero** editar um work log não enviado,  
**Para que** eu possa corrigir erros.

### Critérios de Aceitação
- ✅ PUT /work-logs/{id} com: started_at, completed_at, description, materials_used, workers
- ✅ Editável apenas se status = draft
- ✅ Se materiais mudaram: ajustar stock (devolver antigos, deduzir novos)
- ✅ Auditoria: mudanças registadas

### Notas
- Stock adjustments: transação atómica (tudo ou nada)

---

## 📝 US-107: Submeter Work Log para Aprovação

**Como** worker,  
**Eu quero** submeter um work log,  
**Para que** supervisor possa revisar.

### Critérios de Aceitação
- ✅ POST /work-logs/{id}/submit
- ✅ Status muda: draft → submitted
- ✅ Work log não mais editável (autor)
- ✅ Auditoria: submissão registada
- ✅ Notificação: supervisor (mini_task.supervisor_id)

---

## ✅ US-108: Aprovar Work Log (Supervisor)

**Como** supervisor,  
**Eu quero** aprovar um work log submetido,  
**Para que** trabalho seja oficializado.

### Critérios de Aceitação
- ✅ POST /work-logs/{id}/approve (ou /approve-all para múltiplos)
- ✅ Status muda: submitted → approved
- ✅ Auditoria: quem aprovou, quando
- ✅ Validação: se TODOS work_logs de uma MT estão approved → notificar supervisor de MT approval pendente
- ✅ Notificação: worker (aprovado)
- ✅ Material cost é finalizado (unit_price_at_use locked)

---

## ❌ US-109: Rejeitar Work Log (Supervisor)

**Como** supervisor,  
**Eu quero** rejeitar um work log,  
**Para que** worker possa corrigir.

### Critérios de Aceitação
- ✅ POST /work-logs/{id}/reject com: reason (required)
- ✅ Status muda: submitted → draft
- ✅ Materiais deduzidos são DEVOLVIDOS ao stock (transação atómica)
- ✅ Auditoria: rejeição registada com motivo
- ✅ Notificação: worker (rejected, com motivo)
- ✅ Worker pode re-editar e re-submeter

### Exemplo
```
POST /work-logs/wl-001/reject
{
  "reason": "Quantidade de material inconsistente com estimativa"
}
```

---

## 📊 US-110: Comparar Materiais: Planejado vs Usado

**Como** supervisor,  
**Eu quero** comparar materiais planejados vs efetivamente usados,  
**Para que** eu possa auditar eficiência.

### Critérios de Aceitação
- ✅ GET /mini-tasks/{id}/materials-comparison retorna:
  - Tabela: material | planned_qty | actual_qty (sum from work_logs) | variance (%)
  - Total custo: planned vs actual
  - Alertas: se actual > planned (ex: over 120%)
- ✅ Análise: por mini-task ou agregada por task/ordem

---

## 📊 US-111: Visualizar Progresso de Ordem (Dashboard)

**Como** manager,  
**Eu quero** ver progresso visual de uma ordem,  
**Para que** eu possa acompanhar de um relance.

### Critérios de Aceitação
- ✅ GET /service-orders/{id}/progress retorna:
  - Barras de progresso: tasks (n% complete), mini-tasks (n%), work-logs (n%)
  - Horas trabalhadas vs estimadas (se disponível)
  - Materiais utilizados vs planejado
  - Timeline: datas importantes (criação, execução, conclusão)
  - Status atual, próximas ações

---

## 🗑️ US-112: Deletar Work Log

**Como** supervisor,  
**Eu quero** remover um work log,  
**Para que** eu possa corrigir erros.

### Critérios de Aceitação
- ✅ DELETE /work-logs/{id} (soft delete)
- ✅ Validação: apenas se status = draft ou approved (submitted não permite)
- ✅ Se approved: stock deve ser devolvido (transação atómica)
- ✅ Auditoria: quem deletou, quando, stock adjustments
- ✅ Authorization: criador ou supervisor ou admin

---

## 📎 US-113: Anexar Ficheiro a MiniTask

**Como** worker,  
**Eu quero** carregar fotos/documentos de uma mini-task,  
**Para que** eu possa documentar progresso.

### Critérios de Aceitação
- ✅ POST /mini-tasks/{id}/attachments (multipart form)
- ✅ Mesmas especificações de US-087 (ServiceOrder attachments)
- ✅ Armazenar em: storage/mini-tasks/{mini-task-id}/
- ✅ Auditoria: file info, uploaded_by, timestamp

---

## 📊 US-114: Relatório de Trabalho Realizado

**Como** manager,  
**Eu quero** gerar relatório de trabalho realizado,  
**Para que** eu possa documentar e analisar.

### Critérios de Aceitação
- ✅ GET /reports/work-completed?date_from=&date_to= retorna:
  - Por ordem/task/sector: hours, materials, cost
  - Timeline: quando cada etapa foi concluída
  - Workers envolvidos, horas por worker
  - Materiais utilizados (total qty, custo)
  - Variância planned vs actual
- ✅ Export: PDF, CSV
- ✅ Filtros: period, sector, manager, client

---

## 🔔 US-115: Notificações de Status de Work Log

**Como** worker,  
**Eu quero** receber notificações sobre status de meus work logs,  
**Para que** eu possa acompanhar.

### Critérios de Aceitação
- ✅ Notificação ao submeter: "Work log submetido para aprovação"
- ✅ Notificação ao aprovar: "Work log aprovado ✅"
- ✅ Notificação ao rejeitar: "Work log rejeitado com motivo: ..."
- ✅ Notificação push (app) + email (se subscrito)
- ✅ In-app: centro de notificações

---
