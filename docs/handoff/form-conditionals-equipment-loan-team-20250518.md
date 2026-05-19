# Handoff: Form Field Conditionals & Equipment/Loan/Team Forms

**Session Focus:** Form refinements — field conditionals, equipment form logic (vehicle vs. serial number), loan form completeness, team form responsible select.

**Status:** 70% complete. Main work done:
- ✅ NumberInput step/min/max attributes fixed for cost_per_hour (0.01 step, 0 min)
- ✅ FieldCondition.php enhanced with `in` and `not_in` operators
- ✅ Frontend condition evaluation added to Modal.jsx and EditPanel.jsx
- ✅ EquipmentFormSchema.php conditionality prepared (serial_number vs. license_plate)
- ⏳ **2 background agents running** (may be completed by now):
  - Fix loan form missing inputs (entity, equipment, dates per equipment, operators flag, location inputs, loan manager, description)
  - Fix team form responsible select input with contextualized rules

**What's Remaining:**

1. **Equipment Form Conditionals** (HIGH PRIORITY)
   - Apply `.when('equipment_type_id', 'in', vehicleTypeIds())` to license_plate field (create + update methods)
   - Apply `.when('equipment_type_id', 'not_in', vehicleTypeIds())` to serial_number field (create + update methods)
   - Verify FieldCondition logic evaluates correctly in Modal/EditPanel
   - Update translation labels to reflect vehicle vs. non-vehicle context

2. **Equipment CountingType Seeder** (MEDIUM PRIORITY)
   - Current seeder has: Unitário, Peso, Comprimento, Área, Volume
   - Required: km, horas, metros, dias, semanas, meses, anos
   - File: `database/seeders/CountingTypeSeeder.php`
   - Update seeder then re-seed (do NOT hard-delete; use migrate:refresh for dev)

3. **Review Background Agent Outputs** (BLOCKING)
   - Check if loan form and team form agents completed their tasks
   - Loan form: Verify all inputs present in correct dependency order
   - Team form: Verify responsible input is a SelectInput with proper actor rules

4. **Integration Testing** (FINAL STEP)
   - Test equipment form: create vehicle type → license_plate visible, serial_number hidden
   - Test equipment form: create non-vehicle type → serial_number visible, license_plate hidden
   - Test loan form: All inputs render correctly with proper validation
   - Test team form: Responsible select filters by role/permissions
   - Hard refresh browser (Ctrl+Shift+R) after backend changes

---

## Architecture Context

**Key Files Modified This Session:**
- `app/Core/Forms/FieldCondition.php` — condition operators + evaluation logic
- `resources/js/Components/Common/Modal.jsx` — frontend condition evaluation (evalCondition function)
- `resources/js/Components/DataManager/EditPanel.jsx` — frontend condition evaluation
- `resources/js/Components/Common/FormInput.jsx` — step/min/max attributes for numbers
- `app/Features/Equipments/EquipmentFormSchema.php` — conditional fields + vehicleTypeIds() helper
- `app/Features/Workers/WorkerFormSchema.php` — cost_per_hour step(0.01)->min(0)

**Patterns to Follow:**
- **Conditionals:** Use `.when(fieldName, operator, value)` in FormSchema. Operators: `==`, `!=`, `in`, `not_in`, `>`, `<`, `>=`, `<=`
- **Field Visibility:** Modal/EditPanel filter by field.condition, then call evalCondition() to determine render
- **Enum-Based Lookups:** Use `EquipmentType::where('category', 'vehicle')->pluck('id')` for dynamic vehicle type IDs (do NOT hardcode)
- **Form Flow:** Request → Controller (thin, ~100 lines) → Service (owns business logic) → Model → Resource
- **Translations:** All labels in `resources/lang/{en|pt_PT}/forms.php` under feature key (e.g., `forms.equipments.*`, `forms.workers.*`, `forms.loans.*`)

**Backend Conventions:**
- FormSchema static methods: `create()`, `update($model)`, `destroy()`
- FormField fluent setters: `setLabel(__('key'))`, `setRequired()`, `setRules('...')`
- Conditions serialized via FormField::toArray() → sent to frontend as `field.condition = {field, operator, value}`
- All mutations use `TransactionHandler::execute(fn() => ...)` (see `app/Core/TransactionHandler.php`)

**Frontend Conventions:**
- Modal/EditPanel read `fields` array from FormSchema resource
- visibleFields computed with workflow type filter + condition evaluation
- Form submission → POST to ServiceOrder endpoint → backend validation
- Rebuild via `npm run build` (or `docker exec project-node-1 ... vite build`) after JS changes

---

## Next Steps for Deepseek

1. **Check background agents** → If loan/team form work is complete, verify quality; if incomplete, finish it
2. **Equipment form conditionals** → Apply `.when()` conditions and rebuild
3. **CountingType seeder** → Update to correct units and re-seed
4. **Test conditionals** → Create vehicle/non-vehicle equipment, verify serial/license plate toggle
5. **End-to-end test** → Create loan + team entities, verify all inputs render + validate correctly

Use **graphify** skill for codebase questions. Refer to [CLAUDE.md](CLAUDE.md) for architecture overview and `/memories/` for persistent patterns. 

---

## Suggested Skills
- `graphify` — if need to explore relationships between Loan, Equipment, Seeder, or FieldCondition
- `audit-arch` — final validation that form architecture follows conventions
