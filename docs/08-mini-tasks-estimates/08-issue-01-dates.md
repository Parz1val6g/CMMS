# S-01: Dates — start_date / end_date

## Parent

[PRD — Mini-Tarefas: Estimativas, Datas e Atribuição Equipa+Workers](08-prd.md)

## What to build

Add `start_date` and `end_date` columns to the `mini_tasks` table and expose them through all layers: migration, model, form, validation, service, controller response, and drawer UI.

### Migration
- Add `start_date DATE NOT NULL` and `end_date DATE NOT NULL` after `description` column
- Rollback drops both columns

### Model (`MiniTask`)
- Add `'start_date', 'end_date'` to `$fillable`
- Add `'start_date' => 'date:Y-m-d', 'end_date' => 'date:Y-m-d'` to `$casts`

### FormSchema (`MiniTaskFormSchema`)
- Add `DateInput::make('start_date')` — required, label 'Data de Início'
- Add `DateInput::make('end_date')` — required, label 'Data de Fim'

### Request (`StoreMiniTaskRequest`)
- `start_date`: required|date|after_or_equal:today
- `end_date`: required|date|after_or_equal:start_date
- Update `UpdateMiniTaskRequest` (or `rules()` for update context): `sometimes|date` for both

### Service (`MiniTaskService`)
- Pass `$data['start_date']` and `$data['end_date']` to `MiniTask::create()` in `create()` method
- Pass to `update()` if an update method exists

### Controller (`MiniTaskPageController`)
- Ensure `start_date` and `end_date` are loaded in the index/list response
- Ensure they're included in the single-item response for the drawer

### Drawer (`MiniTaskDrawer`)
- **General tab**: Add `start_date` and `end_date` fields formatted as `DD/MM/YYYY`

## Acceptance criteria

- [ ] Migration runs cleanly (`php artisan migrate`), rollback works
- [ ] MiniTask model has `start_date` and `end_date` in `$fillable` and `$casts`
- [ ] Create form renders two date inputs for start_date and end_date
- [ ] Creating a mini-task without dates returns 422
- [ ] Creating a mini-task with end_date before start_date returns 422
- [ ] Creating a mini-task with valid dates succeeds (201)
- [ ] Editing a mini-task allows changing dates (200)
- [ ] Drawer General tab shows formatted dates
- [ ] PHP tests pass: validation rules, service creates with dates, drawer renders

## Blocked by

None — can start immediately
