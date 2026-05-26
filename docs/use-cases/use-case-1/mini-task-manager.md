# Use Case 1 — Mini-Task Manager

**Actor:** Hugo Ribeiro  
**Email:** `hugo.ribeiro@cm-mangualde.pt`  
**Password:** `password123`  
**Role:** `mini_task_manager`

---

## Responsabilidades neste use case

Decompõe as Tasks em MiniTasks concretas e atribui os recursos necessários: workers, equipas, materiais e equipamentos. Quando o trabalho está feito e os Work Logs aprovados, conclui as MiniTasks.

---

## Permissões relevantes

| Recurso | Acções |
|---|---|
| Tasks | view, create, update |
| Mini-Tasks | view, create, update, assign_workers, assign_materials, assign_equipment, **complete** |
| Attachments | view, create, update |

---

## Passo a Passo

### 1. Login

1. Abre `http://localhost:8000`
2. Introduz o email e a password
3. Se aparecer a página **Seleccionar Role**, escolhe **Mini-Task Manager**
4. Redireccionado para o Dashboard

---

### 2. Consultar as Tasks disponíveis

1. Sidebar → **Gestão de Trabalho** → **Tarefas**
2. Localiza as Tasks com status **PENDING** do teu sector
3. Clica numa Task para ver o contexto: OS de origem, descrição, sector

---

### 3. Criar MiniTasks

> Para cada Task, cria uma ou mais MiniTasks com os recursos necessários.

1. Sidebar → **Mini-Tarefas** → clica **"+ Nova Mini-Tarefa"**
2. Preenche o formulário:

   | Campo | Descrição | Obrigatório |
   |---|---|---|
   | Task | Task a que esta mini-tarefa pertence | Sim |
   | Descrição | O que vai ser feito concretamente | Sim |
   | Data de Início | Data prevista para começar | Não |
   | Data de Fim | Data prevista para terminar | Não |

   **Atribuição de recursos:**

   | Campo | Descrição |
   |---|---|
   | Workers | Trabalhadores atribuídos a esta mini-tarefa |
   | Equipas | Equipas atribuídas |
   | Materiais | Materiais necessários + quantidade planeada |
   | Equipamentos | Equipamentos a usar (ficam automaticamente como **RESERVED**) |

3. Clica **Guardar**
4. MiniTask criada com status **PENDING**
5. Equipamentos associados passam para **RESERVED** automaticamente

---

### 4. Acompanhar o progresso

1. Sidebar → **Mini-Tarefas**
2. Filtra pelas mini-tarefas da OS em questão
3. Verifica o status:
   - **PENDING** — aguarda que os Workers iniciem
   - **IN_PROGRESS** — Workers com work logs activos
   - **AWAITING_APPROVAL** / work logs **SUBMITTED** — aguarda aprovação do Service Order Manager

---

### 5. Concluir a MiniTask

> Após todos os Work Logs da MiniTask estarem **APPROVED** pelo Service Order Manager.

1. Sidebar → **Mini-Tarefas** → abre o detalhe da MiniTask
2. Verifica que não existem work logs por fechar (o sistema bloqueia se existirem)
3. Clica **"Concluir Mini-Tarefa"**
4. Status passa para **COMPLETED**
5. Equipamentos associados são libertados: **RESERVED** → **ACTIVE**
6. Quando **todas** as MiniTasks de uma Task estiverem COMPLETED → a Task passa automaticamente para **AWAITING_APPROVAL**

---

## Resumo do Fluxo

```
Recebe Tasks (PENDING)
  → Cria MiniTasks → atribui Workers, Equipas, Materiais, Equipamentos
  → [Workers fazem clock-in / clock-out → Work Logs SUBMITTED]
  → [Service Order Manager aprova Work Logs]
  → Conclui MiniTasks (COMPLETED)
    → Equipamentos libertados
    → Task passa para AWAITING_APPROVAL
  → [Task Manager aprova a Task]
```
