# S-03: Equipment — multi-select

## Parent

[PRD — Mini-Tarefas: Estimativas, Datas e Atribuição Equipa+Workers](08-prd.md)

## What to build

Expose equipment selection in the mini-task create/edit form. The backend pipeline (`MiniTaskService::syncEquipment`) already exists — this slice covers the UI layer and validation only.

### FormSchema (`MiniTaskFormSchema`)
- Add `equipmentOptions()` helper: queries all equipment, returns `[['id' => $e->id, 'label' => $e->name, ...]]`
- Add `SelectInput::make('equipment_ids')` — multiple, label 'Equipamentos', options from `equipmentOptions()`

### Request (`StoreMiniTaskRequest`)
- `equipment_ids`: nullable|array
- `equipment_ids.*`: exists:equipments,id

### Controller (`MiniTaskPageController`)
- Pass `equipmentOptions` to the Inertia page for the form
- Ensure equipment with pivot data is loaded in the drawer response

### Service (`MiniTaskService`)
- Already handles `$data['equipment_ids']` — verify `create()` and `update()` sync correctly

### Drawer (`MiniTaskDrawer`)
- **Materials tab**: Add equipment section below the materials table showing equipment names

## Acceptance criteria

- [ ] Create form shows equipment multi-select with all equipment as options
- [ ] Submitting with equipment_ids persists pivot rows
- [ ] Submitting without equipment_ids is valid (nullable)
- [ ] Submitting with invalid equipment_id returns 422
- [ ] Edit form loads previously selected equipment
- [ ] Drawer Materials tab shows equipment list
- [ ] PHP tests pass: validation rules, service syncs equipment

## Blocked by

None — can start immediately
