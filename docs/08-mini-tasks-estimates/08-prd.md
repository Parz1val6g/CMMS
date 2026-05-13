# PRD — Mini-Tarefas: Estimativas, Datas e Atribuição Equipa+Workers

## Problem Statement

Atualmente, as mini-tasks registam apenas uma descrição, supervisor, task de origem (ticket/SO) e status. Quando uma mini-task é criada, não é possível definir:

- Estimativa de **materiais** a usar nos worklogs da mini-task
- Estimativa de **equipamentos** necessários
- **Datas de início e fim** previstas
- Comportamento integrado entre seleção de **equipa** e **trabalhadores** (workers)

Os gestores de obra precisam de planear os recursos (materiais, equipamentos, mão-de-obra) ao nível da mini-task, e não apenas ao nível da task/SO. Além disso, a relação entre equipas e trabalhadores deve ser prática: ao selecionar uma equipa, os seus membros devem aparecer automaticamente como selecionados (e bloqueados) no multi-select de workers.

## Solution

Adicionar à mini-task:

1. **Datas**: colunas `start_date` e `end_date` (DATE, NOT NULL) na tabela `mini_tasks` — obrigatórias na criação, editáveis depois
2. **Materiais**: multi-select de materiais com campo de `planned_quantity` por material — sincronizado via pivot `mini_tasks_materials` (já existe)
3. **Equipamentos**: multi-select de equipamentos — sincronizado via pivot `mini_task_equipment` (já existe)
4. **Workers bloqueados por equipa**: quando uma ou mais equipas são selecionadas, os workers pertencentes a essas equipas aparecem automaticamente selecionados e **bloqueados** (não podem ser removidos individualmente). Apenas ficam desbloqueados quando a equipa é desselecionada.

O CHECK constraint (`worker_id XOR team_id`) na pivot `mini_tasks_workers_teams` é mantido — linhas separadas para team e worker permitem ambas as atribuições em simultâneo.

## User Stories

1. As a **gestor de obra**, I want to define **start_date and end_date** when creating a mini-task, so that I can plan the execution timeline
2. As a **gestor de obra**, I want to **edit start_date and end_date** after creation, so that I can adjust the plan as the work evolves
3. As a **gestor de obra**, I want to **select materials** from a multi-select and define a **planned quantity per material**, so that I can estimate material needs for the mini-task
4. As a **gestor de obra**, I want to **select equipment** from a multi-select, so that I can reserve equipment for the mini-task
5. As a **gestor de obra**, I want to **assign teams and workers simultaneously**, so that I can flexibly compose the workforce
6. As a **gestor de obra**, when I select a **team**, I want its **workers to be auto-selected and locked** in the workers multi-select, so that I don't need to manually add each team member
7. As a **gestor de obra**, I want to **remove the lock** on workers only by deselecting the team, so that I can control individual assignments
8. As a **gestor de obra**, I want to see **all workers** in the workers multi-select (not filtered by team), so that I can assign workers without a team or from other teams
9. As a **gestor de obra**, I want to see the **start_date and end_date** in the mini-task detail drawer, so that I can quickly verify the timeline
10. As a **gestor de obra**, I want to see **planned materials with quantities** in the drawer, so that I can review the material plan
11. As a **gestor de obra**, I want to see **planned equipment** in the drawer, so that I can review the equipment plan
12. As a **gestor de obra**, I want the **team assignment** to show locked workers with a visual indicator (different style, no remove button), so that I understand which selections are automatic

## Implementation Decisions

### Schema Changes

- **Migration**: Add `start_date DATE NOT NULL` and `end_date DATE NOT NULL` to `mini_tasks` table
- **Model casts**: `'date:Y-m-d'` for both fields
- **$fillable**: Both fields added to MiniTask model fillable array
- **CHECK constraint**: Kept unchanged — multi-row pivot allows team + worker simultaneously

### Materials

- Backend pipeline already exists: `MiniTaskService::create()` accepts `$data['materials']` with `material_id` + `planned_quantity`
- Pivot table `mini_tasks_materials` already has `planned_quantity` column
- **New**: Expose `material_ids` (multi-select) + `planned_quantities` (dynamic quantity input per material) in the form
- Form field type: multi-select for materials + inline number input for quantity

### Equipment

- Backend pipeline already exists: `MiniTaskService::create()` accepts `$data['equipment_ids']`
- Pivot table `mini_task_equipment` exists (equipment_id FK + mini_task_id FK)
- **New**: Expose `equipment_ids` (multi-select) in the form

### Form Changes (MiniTaskFormSchema)

New fields in create form:
- `start_date` (DateInput, required, `after_or_equal:today`)
- `end_date` (DateInput, required, `after_or_equal:start_date`)
- `material_ids` (SelectInput, multiple)
- `equipment_ids` (SelectInput, multiple)

New helper methods:
- `materialOptions()` — returns formatted material list
- `equipmentOptions()` — returns formatted equipment list

### Validation (StoreMiniTaskRequest)

New rules:
- `start_date`: required, date, after_or_equal:today
- `end_date`: required, date, after_or_equal:start_date
- `material_ids`: nullable, array
- `material_ids.*`: exists:materials,id
- `equipment_ids`: nullable, array
- `equipment_ids.*`: exists:equipments,id

### Team+Worker Locked Behavior

- **MultiSelect component**: New optional prop `lockedValues = []` (array of values that are selected and cannot be removed)
- Locked items: no X (remove) button rendered, CSS class `opacity-60 cursor-not-allowed bg-brand-mid/20`
- `toggleItem` ignores locked values — cannot toggle them off
- **MiniTaskFormSchema**: Workers options include ALL workers (not filtered by team)
- **Frontend logic** in form component: When `team_ids` changes, compute `lockedWorkerIds` = all workers belonging to selected teams; pass as `lockedValues` to the workers MultiSelect
- Existing workers that are NOT in selected teams remain freely selectable

### Files to Modify (10 total, implementation order)

1. Migration — add `start_date` / `end_date` to `mini_tasks` table
2. MiniTask model — add columns to `$fillable` + `$casts`
3. MultiSelect component — `lockedValues` prop
4. MiniTaskFormSchema — new fields + helper methods
5. StoreMiniTaskRequest — validation rules for new fields
6. MiniTaskService — pass `start_date`, `end_date` to `create()`
7. MiniTaskDrawer — display new fields in tabs
8. MiniTaskController — ensure new fields are loaded for drawer response
9. Form frontend — implement locked workers logic (where formSchema is consumed: Modal + EditPanel)
10. Tests — validate new behavior

### Drawing Tab (MiniTaskDrawer)

- **General tab**: Add `start_date` and `end_date` fields
- **Team tab**: No visual changes — the locked behavior is enforced in the MultiSelect during create/edit, not in the drawer read view
- **Materials tab**: Add equipment section alongside existing materials table

## Testing Decisions

### Testing Philosophy

- **Backend**: Integration tests via Pest PHP — test the Service layer and Request validation. Do NOT test the model casts in isolation (tested by framework). Test the form schema returns valid options. Test validation rules reject invalid dates and accept valid ones. Test that worker_ids and team_ids are correctly saved with the XOR constraint maintained.
- **Frontend**: Component tests via Testing Library — test MultiSelect lockedValues prop renders locked items without remove button. Test that locked items cannot be toggled. Test form submission with locked values.
- **What makes a good test**: Tests should validate behavior from the outside (input → output), not implementation details. A create test should assert the database has the correct rows, not that a specific method was called.

### Modules to Test

- **MiniTaskService::create()** — integration test with full payload (dates, materials, equipment, workers, teams)
- **StoreMiniTaskRequest** — validation unit test for date rules and existence checks
- **MultiSelect component** — unit test for lockedValues prop behavior
- **MiniTaskFormSchema** — unit test that options are correctly formatted

## Out of Scope

- **Material/Equipment actual usage tracking**: This PRD covers only **planned quantities**. Recording actual consumption against the plan is separate scope.
- **Calendar/Gantt view**: Dates are just stored as columns — no timeline visualization.
- **Material/Equipment availability validation**: No check if materials/equipment are in stock or already reserved.
- **Worker workload validation**: No check if workers are already assigned to overlapping mini-tasks.
- **Notifications**: No email/in-app notification when dates approach or pass.
- **Material planned_quantity on equipment pivot**: Equipment pivot has no quantity — just presence/absence of equipment. Adding quantity to equipment is out of scope.
- **Removal of CHECK constraint**: The XOR constraint remains. Multiple rows per mini-task allow both team and worker assignment.

## Further Notes

- The existing `mini_tasks_materials` pivot already supports `planned_quantity` — no pivot migration needed for materials
- The existing `mini_task_equipment` pivot has no `planned_quantity` column — equipment is currently assigned/unassigned only
- The `lockedValues` pattern on MultiSelect is reusable across the app for any future "auto-selected based on parent selection" scenarios
- The form currently uses `Modal.jsx` for creation and `EditPanel.jsx` for editing — both render fields generically from `formSchema`. The locked workers logic needs to be implemented in both components (or extracted into a shared hook)
- Worker already has `team_id` FK — the `Team::workers()` relation is `hasMany(Worker)`, making it trivial to compute `lockedWorkerIds` from `team_ids`
