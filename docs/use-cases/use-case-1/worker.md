# Use Case 1 — Worker (Trabalhador)

**Actor:** António Ferreira  
**Email:** `antonio.ferreira@cm-mangualde.pt`  
**Password:** `password123`  
**Role:** `worker`

---

## Responsabilidades neste use case

Executa o trabalho no terreno e regista as horas e materiais utilizados através de Work Logs (clock-in / clock-out).

---

## Permissões relevantes

| Recurso | Acções |
|---|---|
| Tasks | view, update |
| Mini-Tasks | view, update |
| Work Logs | view, update |
| Equipments | view, update |
| Locations | view, update |

---

## Passo a Passo

### 1. Login

1. Abre `http://localhost:8000`
2. Introduz o email e a password
3. Se aparecer a página **Seleccionar Role**, escolhe **Worker**
4. Redireccionado para o Dashboard — sidebar filtrada para o role de Worker

---

### 2. Consultar as MiniTasks atribuídas

1. Sidebar → **Gestão de Trabalho** → **Mini-Tarefas**
2. Localiza as MiniTasks com status **PENDING** onde estás listado como worker ou membro de equipa
3. Clica numa MiniTask para ver:
   - Descrição do trabalho
   - Datas previstas
   - Materiais planeados
   - Equipamentos reservados para esta tarefa

---

### 3. Registar início de trabalho (Clock-In)

> Quando chegas ao local e começas o trabalho.

1. Sidebar → **Work Logs** → clica **"+ Novo Work Log"**
2. Preenche o formulário:

   | Campo | Descrição | Obrigatório |
   |---|---|---|
   | Mini-Tarefa | Selecciona a MiniTask em que vais trabalhar | Sim |
   | Hora de Início | Hora a que começaste | Sim |
   | Descrição | O que está a ser feito / observações do terreno | Sim |
   | Workers | Confirma os trabalhadores presentes | Não |
   | Materiais | Materiais que estás a usar | Não |
   | Equipamentos | Equipamentos em uso (passam para **IN_USE**) | Não |
   | Hora de Fim | Deixa em branco se ainda estás a trabalhar | Não |

3. Clica **Guardar**
4. Se a hora de fim ficou em branco → Work Log com status **IN_PROGRESS**
5. Se preencheste a hora de fim → Work Log criado directamente como **SUBMITTED**

---

### 4. Registar fim de trabalho (Clock-Out)

> Quando terminares o trabalho no terreno.

1. Sidebar → **Work Logs** → localiza o teu work log com status **IN_PROGRESS**
2. Abre o detalhe → clica **"Concluir"** (Clock-Out)
3. Confirma ou actualiza:
   - Hora de fim
   - Materiais efectivamente utilizados
4. Clica **Guardar**
5. Status passa para **SUBMITTED**
6. Equipamentos usados são libertados da tua posse

---

### 5. Aguardar aprovação

> O Service Order Manager revê o work log submetido.

- **Aprovado** → Work Log passa para **APPROVED** ✓
- **Rejeitado** → Work Log passa para **REJECTED** — deves corrigir e resubmeter

---

### 6. Corrigir um Work Log rejeitado

1. Sidebar → **Work Logs** → filtra por status **REJECTED**
2. Abre o work log rejeitado
3. Corrige o que está incorrecto (horas, materiais, descrição)
4. Submete novamente → volta para **SUBMITTED**
5. Aguarda nova revisão do Service Order Manager

---

## Resumo do Fluxo

```
Recebe MiniTask (atribuída pelo Mini-Task Manager)
  → Cria Work Log — clock-in (IN_PROGRESS)
  → Executa o trabalho no terreno
  → Fecha o Work Log — clock-out (SUBMITTED)
  → [Service Order Manager revê]
      Aprovado (APPROVED) ✓
        ou
      Rejeitado → corrige e resubmete
```
