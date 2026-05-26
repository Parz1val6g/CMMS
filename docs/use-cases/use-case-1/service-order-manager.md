# Use Case 1 — Service Order Manager

**Actor:** Maria Pereira  
**Email:** `maria.pereira@cm-mangualde.pt`  
**Password:** `password123`  
**Role:** `manager`

---

## Responsabilidades neste use case

Recebe as SOs criadas pelo Atendente, completa os dados de gestão (sectores, prioridade, data de fim), activa-a criando Tarefas por sector, acompanha a execução e no final conclui a OS.

---

## Permissões relevantes

| Recurso | Acções |
|---|---|
| Service Orders | view, create, update, **activate**, **complete**, cancel, delete |
| Tasks | view, cancel, complete, reject |
| Work Logs | view (só leitura) |

---

## Passo a Passo

### 1. Login

1. Abre `http://localhost:8000`
2. Introduz o email e a password
3. Se aparecer a página **Seleccionar Role**, escolhe **Manager**
4. Redireccionado para o Dashboard

---

### 2. Rever a SO criada pelo Atendente

1. Sidebar → **Gestão de Trabalho** → **Ordens de Serviço**
2. A listagem mostra as SOs onde o Gestor está designado como responsável
3. Clica na SO com status **PENDING** criada pelo Atendente
4. Revê os dados introduzidos durante a chamada:
   - Descrição do problema
   - Localização
   - Cliente
   - Tipo de serviço
   - Prioridade sugerida pelo Atendente
5. Completa ou ajusta os campos de gestão:

   | Campo | Descrição |
   |---|---|
   | Sectores | Seleccionar os sectores responsáveis (obrigatório para activar) |
   | Data de fim prevista | Prazo estimado para conclusão |
   | Prioridade | Ajustar se a sugestão do Atendente estiver incorrecta |
   | Notas / Anexos | Adicionar contexto interno ou documentos |

---

### 3. Activar a Ordem de Serviço

> A activação cria automaticamente uma Task por cada sector associado à OS.

1. No detalhe da OS, clica no botão **"Activar"** (apenas visível em status PENDING)
2. Confirma a acção
3. Status passa para **IN_PROGRESS**
4. Tasks criadas automaticamente — uma por sector

> **Nota:** A SO não pode ser reatribuída a outro Gestor depois de activada.

---

### 4. Aprovar / Rejeitar Tasks

> Quando o Mini-Task Manager conclui todas as MiniTasks de uma Task, ela passa automaticamente para AWAITING_APPROVAL.

1. Sidebar → **Tarefas**
2. Filtra por status **AWAITING_APPROVAL**
3. Para cada Task:
   - Abre o detalhe e verifica o trabalho realizado
   - **Aprovar** → status passa para **COMPLETED**
   - **Rejeitar** (com motivo obrigatório) → Task volta para **IN_PROGRESS**, notificação enviada ao Task Manager

---

### 5. Fechar a Ordem de Serviço

> Quando todas as Tasks estão COMPLETED, a OS passa automaticamente para AWAITING_APPROVAL.

1. Sidebar → **Ordens de Serviço**
2. Filtra por status **AWAITING_APPROVAL**
3. Abre o detalhe → clica **"Concluir"**
4. Status passa para **COMPLETED** ✓

---

## Resumo do Fluxo

```
Atendente cria OS (PENDING)
  → Gestor revê dados + selecciona sectores
  → Activa OS (IN_PROGRESS) — Tasks criadas por sector
  → [Task Manager + Mini-Task Manager + Worker executam]
  → Aprova Tasks (AWAITING_APPROVAL → COMPLETED)
  → Conclui OS (AWAITING_APPROVAL → COMPLETED) ✓
```
