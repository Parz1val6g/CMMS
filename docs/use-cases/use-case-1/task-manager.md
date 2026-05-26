# Use Case 1 — Task Manager

**Actor:** Sofia Marques  
**Email:** `sofia.marques@cm-mangualde.pt`  
**Password:** `password123`  
**Role:** `task_manager`

---

## Responsabilidades neste use case

Recebe as Tasks criadas automaticamente pela activação da OS. Gere o ciclo de vida das Tasks — pode criar tasks adicionais, acompanhar o progresso, e no final aprovar ou rejeitar cada Task. Também pode gerir MiniTasks (atribuir recursos e concluir).

---

## Permissões relevantes

| Recurso | Acções |
|---|---|
| Service Orders | view |
| Tasks | view, create, update, **complete**, **cancel**, **reject** |
| Mini-Tasks | view, create, update, assign_workers, assign_materials, assign_equipment, **complete** |
| Attachments | view, create, update |

---

## Passo a Passo

### 1. Login

1. Abre `http://localhost:8000`
2. Introduz o email e a password
3. Se aparecer a página **Seleccionar Role**, escolhe **Task Manager**
4. Redireccionado para o Dashboard

---

### 2. Consultar as Tasks da OS

1. Sidebar → **Gestão de Trabalho** → **Tarefas**
2. Localiza as Tasks com status **PENDING** criadas pela activação da OS
3. Clica numa Task para ver o detalhe: OS de origem, sector associado, descrição

---

### 3. Criar Tasks adicionais (se necessário)

> Normalmente as tasks são criadas automaticamente pela activação da OS. Se for necessária uma task extra:

1. Na lista de Tarefas → clica **"+ Nova Tarefa"**
2. Preenche:

   | Campo | Descrição | Obrigatório |
   |---|---|---|
   | Ordem de Serviço | OS a que pertence esta task | Sim |
   | Sector | Sector responsável | Sim |
   | Descrição | O que deve ser feito | Sim |

3. Clica **Guardar** → Task criada com status **PENDING**

---

### 4. Acompanhar o progresso

1. Sidebar → **Tarefas**
2. Filtra pela OS em questão
3. Verifica o status de cada Task:
   - **PENDING** — aguarda que o Mini-Task Manager crie as MiniTasks
   - **IN_PROGRESS** — MiniTasks em execução
   - **AWAITING_APPROVAL** — todas as MiniTasks concluídas, aguarda aprovação
   - **COMPLETED** — Task aprovada

---

### 5. Aprovar ou Rejeitar uma Task

> Quando todas as MiniTasks de uma Task estão COMPLETED, a Task passa automaticamente para AWAITING_APPROVAL.

1. Sidebar → **Tarefas** → filtra por **AWAITING_APPROVAL**
2. Abre o detalhe da Task
3. **Aprovar** → clica **"Concluir Task"** → status passa para **COMPLETED**
4. **Rejeitar** → clica **"Rejeitar"** → introduz o motivo (obrigatório) → Task volta para **IN_PROGRESS** e notificação é enviada

---

## Resumo do Fluxo

```
Recebe Tasks (PENDING) — criadas pela activação da OS
  → Acompanha progresso
  → [Mini-Task Manager cria MiniTasks → Worker executa]
  → Task passa para AWAITING_APPROVAL
  → Aprova Task (COMPLETED)
    ou
  → Rejeita Task (IN_PROGRESS) → Mini-Task Manager retoma
```
