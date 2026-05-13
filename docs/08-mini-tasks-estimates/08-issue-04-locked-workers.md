# S-04: Locked Workers — team→worker auto-select

## Parent

[PRD — Mini-Tarefas: Estimativas, Datas e Atribuição Equipa+Workers](08-prd.md)

## What to build

Implement the locked workers behavior: when a team is selected, its workers are auto-selected and locked in the workers multi-select. This requires a new `lockedValues` prop on the shared MultiSelect component and form logic in Modal/EditPanel to compute locked worker IDs from selected team IDs.

### MultiSelect component
- Add optional prop: `lockedValues = []` (array of values that appear selected and cannot be removed)
- Locked items: render without X (remove) button, apply CSS class `opacity-60 cursor-not-allowed bg-brand-mid/20`
- `toggleItem` ignores locked values — clicking a locked item does nothing
- `removeItem` checks if value is locked before removing
- `selectedLabels` rendering: locked items show without remove button, non-locked items show normally
- Update prop interface: `{ name, options, value, onChange, placeholder, showSearch, lockedValues }`

### Form logic (Modal.jsx & EditPanel.jsx)
- Both components render form fields from `formSchema` generically
- Need to intercept the `team_ids` field to drive locked workers:
  1. Add local state `lockedWorkerIds = []`
  2. Watch `team_ids` value changes: when it changes, fetch workers belonging to those teams
  3. Compute `lockedWorkerIds` = all worker IDs where `worker.team_id` is in selected `team_ids`
  4. Pass `lockedValues={lockedWorkerIds}` to the `worker_ids` MultiSelect
- Worker options: all workers (not filtered) — already provided by formSchema
- On form submit: lockedWorkerIds are included in worker_ids payload (they're already in the value array)

### Workers data flow
- `MiniTaskFormSchema::workerOptions()` already returns all workers with `['id' => $w->id, 'label' => $w->user->name, 'team_id' => $w->team_id]`
- The `team_id` in each option allows the frontend to compute locked workers without an extra API call
- Alternatively, pass a separate `workersByTeam` map from the controller

### Drawer (`MiniTaskDrawer`)
- Team tab: optionally show a visual indicator next to workers that were auto-assigned via team (not critical — the locked behavior is a create/edit concern, not read-only)

## Acceptance criteria

- [ ] MultiSelect renders locked values without remove button
- [ ] Locked items have distinct visual style (opacity-60, no hover remove)
- [ ] Clicking a locked item does not deselect it
- [ ] Selecting a team auto-selects its workers and locks them
- [ ] Selecting multiple teams locks workers from all selected teams
- [ ] Deselecting a team removes the lock on its workers (they may stay selected if manually toggled)
- [ ] Workers not belonging to any selected team remain freely selectable/removable
- [ ] Form submit includes locked workers in the worker_ids payload
- [ ] Jest/Testing Library tests pass: lockedValues rendering, toggle immunity, form integration

## Blocked by

None — can start immediately
