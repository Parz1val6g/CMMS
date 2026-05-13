# Grill-me: Mini-Tarefas — Estimativas, Datas e Atribuição Equipa+Workers

**Data:** 2026-05-12
**Contexto:** Adicionar campo de estimativa de materiais/equipamentos, data de início/fim, e definir comportamento equipa+workers no formulário de criação/edição de mini-tarefas.

---

## Perguntas e Decisões

### P1 — Materiais (estimativa)

**Pergunta:** No formulário da mini-tarefa, queres poder selecionar materiais (select múltiplo) com campo de quantidade planeada?

**Resposta:** Sim, select múltiplo de materiais + campo de quantidade para cada um.

**Decisão:** Adicionar `material_ids` (multiselect) + `planned_quantities` (array paralelo) ao formulário.

---

### P2 — Equipamentos (estimativa)

**Pergunta:** Também queres selecionar equipamentos no formulário?

**Resposta:** Sim, select múltiplo de equipamentos.

**Decisão:** Adicionar `equipment_ids` (multiselect) ao formulário.

---

### P3 — Datas de início e fim

**Pergunta:** Devem ser obrigatórias ou opcionais na criação? Editáveis depois?

**Resposta:** Obrigatórias na criação, editáveis depois.

**Decisão:** Colunas `start_date` e `end_date` (date) na tabela `mini_tasks`. Validação: `required|date` na criação, `sometimes|date` na edição, `start_date <= end_date`.

---

### P4 — Comportamento Equipas + Workers

**Pergunta:** Quando seleciono uma equipa, os trabalhadores dessa equipa devem ser também associados automaticamente? E no select de workers, mostram-se todos ou só os sem equipa?

**Resposta:** Deve ser possível atribuir equipas E workers ao mesmo tempo (mesmo que não pertençam às equipas selecionadas). Workers de equipas selecionadas devem ficar auto-selecionados e bloqueados (não removíveis) no select de workers. O select de workers mostra todos os workers.

**Decisão final (confirmada):**

| Regra | Comportamento |
|-------|---------------|
| Select Equipas | Multiselect normal — equipas selecionadas livremente |
| Select Workers | Mostra **todos** os workers |
| Workers de equipas selecionadas | Auto-selecionados e **bloqueados** (locked — não podem ser removidos individualmente) |
| Workers sem equipa / de outras equipas | Livremente selecionáveis/removíveis |
| Desselecionar equipa | Workers dessa equipa são removidos/desbloqueados |
| CHECK constraint (BD) | Mantém-se — `worker_id` XOR `team_id` por linha; mini-tarefa pode ter múltiplas linhas |

---

## Ficheiros a alterar (11)

| # | Ficheiro | Ação |
|---|----------|------|
| 1 | `database/migrations/2026_05_13_000000_add_estimates_dates_to_mini_tasks.php` | **Criar** |
| 2 | `app/Features/MiniTasks/Models/MiniTask.php` | **Editar** — add `start_date`, `end_date` a `$fillable` e `$casts` |
| 3 | `app/Features/MiniTasks/MiniTaskFormSchema.php` | **Editar** — add campos date, material_ids, equipment_ids |
| 4 | `app/Features/MiniTasks/Requests/StoreMiniTaskRequest.php` | **Editar** — add validações |
| 5 | `app/Features/MiniTasks/Services/MiniTaskService.php` | **Editar** — sync materiais com quantity + equipamentos |
| 6 | `app/Features/MiniTasks/Resources/MiniTaskResource.php` | **Editar** — add campos à resposta |
| 7 | `app/Features/MiniTasks/Controllers/Web/MiniTaskPageController.php` | **Editar** — passar options de materiais/equipamentos |
| 8 | `resources/js/Components/Common/MultiSelect.jsx` | **Editar** — nova prop `lockedValues` |
| 9 | `resources/js/Features/MiniTasks/Components/MiniTaskDrawer.jsx` | **Editar** — mostrar datas, workers locked |
| 10 | `resources/js/Features/MiniTasks/Pages/Index.jsx` | **Editar** — formSchema atualizado |

---

## Ordem de implementação

```
 1. Migration (nova tabela)
 2. Model MiniTask (fillable + casts)
 3. Backend: FormSchema
 4. Backend: Request (validações)
 5. Backend: Service (lógica create/update)
 6. Backend: Resource (response)
 7. Backend: PageController (options)
 8. Frontend: MultiSelect (lockedValues)
 9. Frontend: MiniTaskDrawer (UI)
10. Frontend: Index (formSchema)
```
