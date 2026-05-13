# S-02: Materials — multi-select + planned quantity

## Parent

[PRD — Mini-Tarefas: Estimativas, Datas e Atribuição Equipa+Workers](08-prd.md)

## What to build

Expose material selection in the mini-task create/edit form with planned quantity per material. The backend pipeline (`MiniTaskService::syncMaterials`) already exists — this slice covers the UI layer and validation only.

### FormSchema (`MiniTaskFormSchema`)
- Add `materialOptions()` helper: queries all materials, returns `[['id' => $m->id, 'label' => $m->name, ...]]`
- Add `SelectInput::make('material_ids')` — multiple, label 'Materiais', options from `materialOptions()`
- Planned quantities: each selected material gets an inline `<input type="number">` for `planned_quantity`
- Quantity inputs are rendered dynamically as materials are selected (paired array: `planned_quantities[material_id] = value`)

### Request (`StoreMiniTaskRequest`)
- `material_ids`: nullable|array
- `material_ids.*`: exists:materials,id
- `planned_quantities`: nullable|array (keyed by material_id)
- `planned_quantities.*`: integer|min:1

### Controller (`MiniTaskPageController`)
- Pass `materialOptions` to the Inertia page for the form
- Ensure materials with pivot data are loaded in the drawer response

### Service (`MiniTaskService`)
- Already handles `$data['materials']` — verify `create()` accepts `[['material_id' => X, 'planned_quantity' => Y], ...]`
- Verify `update()` also syncs materials correctly

### Drawer (`MiniTaskDrawer`)
- **Materials tab**: Display materials table with columns: Material Name, Planned Quantity
- Add an equipment section placeholder or label (actual equipment data depends on S-03)

## Acceptance criteria

- [ ] Create form shows material multi-select with all materials as options
- [ ] Selecting a material shows an inline quantity input for that material
- [ ] Submitting with materials persists pivot rows with planned_quantity
- [ ] Submitting without materials is valid (nullable)
- [ ] Submitting with invalid material_id returns 422
- [ ] Edit form loads previously selected materials with their quantities
- [ ] Drawer Materials tab shows materials table with name + quantity
- [ ] PHP tests pass: validation rules, service syncs materials

## Blocked by

None — can start immediately
