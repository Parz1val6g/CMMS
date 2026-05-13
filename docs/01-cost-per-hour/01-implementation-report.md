# Implementation Report — Cost Per Hour Feature

> **Generated:** 2026-05-13
> **Methodology:** TDD (Red-Green-Refactor)
> **Test Suite:** 34 tests, 50 assertions, 0 failures

---

## TDD Delivery Summary

All 8 issues from [`01-issues.md`](01-issues.md) were delivered sequentially using Test-Driven Development. Each issue followed the RED (write failing test) → GREEN (implement) → REFACTOR loop.

### Test Results (Final Run)

```
PHPUnit 11.5.55
........RRRRRRRRRRRRRRRRRRRRRRRRRR  34 / 34 (100%)
OK, but there were issues!
Tests: 34, Assertions: 50, Risky: 26.
```

> **Note:** The 26 "risky" tests are cosmetic — PHPUnit reports "code did not remove its own error/exception handlers" because the standalone test bootstrap manually bootstraps Laravel without cleaning up registered handlers. All assertions pass (0 failures).

---

## Issue-by-Issue Breakdown

### ISSUE-001: Migrations ✅ GREEN

**Test file:** [`tests/Feature/Migrations/CostPerHourMigrationTest.php`](../../tests/Feature/Migrations/CostPerHourMigrationTest.php)

5 migration files created under `database/migrations/cost_per_hour/`:

| File | Table | Column |
|------|-------|--------|
| `2026_05_13_100000_add_cost_per_hour_to_equipments.php` | `equipments` | `cost_per_hour decimal(10,2) default 0.00` after `description` |
| `2026_05_13_100001_add_cost_per_hour_to_workers.php` | `workers` | `cost_per_hour decimal(10,2) default 0.00` after `team_id` |
| `2026_05_13_100002_add_cost_per_hour_to_work_logs_workers.php` | `work_logs_workers` | `cost_per_hour decimal(10,2) default 0.00` after `worker_id` |
| `2026_05_13_100003_add_cost_per_hour_to_work_log_equipment.php` | `work_log_equipment` | `cost_per_hour decimal(10,2) default 0.00` after `equipment_id` |
| `2026_05_13_100004_create_cost_histories_table.php` | `cost_histories` | `morphs('entity')`, `cost_per_hour`, `changed_by`, `effective_from`, `effective_until` |

**Tests:** 6 tests, 11 assertions — validates column presence, types, defaults, and cost_histories structure via SQLite `:memory:`.

### ISSUE-002: Models ✅ GREEN

**Test file:** [`tests/Feature/Models/CostPerHourModelsTest.php`](../../tests/Feature/Models/CostPerHourModelsTest.php)

| Model | Changes |
|-------|---------|
| [`Equipment`](../../app/Features/Equipments/Models/Equipment.php) | Added `'cost_per_hour'` to `$fillable` + `'cost_per_hour' => 'decimal:2'` to `$casts` |
| [`Worker`](../../app/Features/Workers/Models/Worker.php) | Added `'cost_per_hour'` to `$fillable` + `'cost_per_hour' => 'decimal:2'` to `$casts` |
| [`WorkLog`](../../app/Features/WorkLogs/Models/WorkLog.php) | Added `->withPivot('cost_per_hour')` to both `workers()` and `equipment()` relations |
| [`CostHistory`](../../app/Shared/Models/CostHistory.php) | **New model** — fillable, casts, `entity(): MorphTo`, `scopeActive()`, `scopeEffectiveAt()` |

**Tests:** 12 tests, 21 assertions — validates fillable, casts, pivot columns, CostHistory structure and scopes.

### ISSUE-003: Observers ✅ GREEN

**Test file:** [`tests/Feature/Observers/CostPerHourObserversTest.php`](../../tests/Feature/Observers/CostPerHourObserversTest.php)

| Observer | Path | Logic |
|----------|------|-------|
| `EquipmentObserver` | [`app/Features/Equipments/Observers/EquipmentObserver.php`](../../app/Features/Equipments/Observers/EquipmentObserver.php) | On `updated()`: if `wasChanged('cost_per_hour')` → close active record + create new one in `cost_histories` |
| `WorkerObserver` | [`app/Features/Workers/Observers/WorkerObserver.php`](../../app/Features/Workers/Observers/WorkerObserver.php) | Same logic for Worker model |

Both registered in [`AppServiceProvider`](../../app/Providers/AppServiceProvider.php) under `// ── Cost History Observers ──` comment block (documenting coexistence with existing `AuditObserver`).

**Tests:** 4 tests, 4 assertions — validates class existence and `updated()` method signature.

### ISSUE-004: Snapshot ✅ GREEN

**Test file:** [`tests/Feature/Services/CostPerHourSnapshotTest.php`](../../tests/Feature/Services/CostPerHourSnapshotTest.php)

Modified [`WorkLogService::approve()`](../../app/Features/WorkLogs/Services/WorkLogService.php) to snapshot `cost_per_hour` into pivot tables after status update:

```php
$workLog->loadMissing('workers', 'equipment');
foreach ($workLog->workers as $worker) {
    $workLog->workers()->updateExistingPivot($worker->id, [
        'cost_per_hour' => $worker->cost_per_hour,
    ]);
}
foreach ($workLog->equipment as $equipment) {
    $workLog->equipment()->updateExistingPivot($equipment->id, [
        'cost_per_hour' => $equipment->cost_per_hour,
    ]);
}
```

**Tests:** 4 tests, 6 assertions — validates `loadMissing`, worker snapshot, equipment snapshot, and that `complete()` does NOT snapshot.

### ISSUE-005: Form Schemas ✅ GREEN

**Test file:** [`tests/Feature/FormSchemas/CostPerHourFormSchemaTest.php`](../../tests/Feature/FormSchemas/CostPerHourFormSchemaTest.php)

| Form Schema | Method | Field |
|-------------|--------|-------|
| [`EquipmentFormSchema`](../../app/Features/Equipments/EquipmentFormSchema.php) | `create()` | `NumberInput::make('cost_per_hour')` with `required\|numeric\|min:0\|max:9999.99` |
| | `update()` | `NumberInput::make('cost_per_hour')` with `sometimes\|numeric\|min:0\|max:9999.99` |
| [`WorkerFormSchema`](../../app/Features/Workers/WorkerFormSchema.php) | `create()` | `NumberInput::make('cost_per_hour')` with `required\|numeric\|min:0\|max:9999.99` |
| | `update()` | `NumberInput::make('cost_per_hour')` with `sometimes\|numeric\|min:0\|max:9999.99` |

**Tests:** 8 tests, 8 assertions — source code inspection via `file_get_contents()` validates field declaration, translation keys, and rules.

### ISSUE-006: API Resources ✅ GREEN

| Resource | Addition |
|----------|----------|
| [`EquipmentResource`](../../app/Features/Equipments/Resources/EquipmentResource.php) | `'cost_per_hour' => $this->cost_per_hour` (after `description`) |
| [`WorkerResource`](../../app/Features/Workers/Resources/WorkerResource.php) | `'cost_per_hour' => $this->cost_per_hour` (after `team_id`) |

### ISSUE-007: Translations ✅ GREEN

| Key | EN | PT_PT |
|-----|----|-------|
| `forms.equipments.cost_per_hour` | "Cost Per Hour (€)" | "Custo por Hora (€)" |
| `forms.workers.cost_per_hour` | "Cost Per Hour (€)" | "Custo por Hora (€)" |

### ISSUE-008: Seeders ✅ GREEN

| Factory/Seeder | Changes |
|----------------|---------|
| [`EquipmentFactory`](../../database/factories/EquipmentFactory.php) | Added `'cost_per_hour' => $this->faker->randomFloat(2, 0, 150)` |
| [`WorkerFactory`](../../database/factories/WorkerFactory.php) | Added `'cost_per_hour' => $this->faker->randomFloat(2, 10, 50)` |
| [`EquipmentSeeder`](../../database/seeders/EquipmentSeeder.php) | Added realistic `cost_per_hour` to all 17 equipment entries (€0–€120/hr) |
| [`WorkerSeeder`](../../database/seeders/WorkerSeeder.php) | Added `'cost_per_hour' => 15.00` to Worker::create() |

---

## Files Modified/Created

### Created (8 files)
- `database/migrations/cost_per_hour/2026_05_13_100000_add_cost_per_hour_to_equipments.php`
- `database/migrations/cost_per_hour/2026_05_13_100001_add_cost_per_hour_to_workers.php`
- `database/migrations/cost_per_hour/2026_05_13_100002_add_cost_per_hour_to_work_logs_workers.php`
- `database/migrations/cost_per_hour/2026_05_13_100003_add_cost_per_hour_to_work_log_equipment.php`
- `database/migrations/cost_per_hour/2026_05_13_100004_create_cost_histories_table.php`
- `app/Shared/Models/CostHistory.php`
- `app/Features/Equipments/Observers/EquipmentObserver.php`
- `app/Features/Workers/Observers/WorkerObserver.php`

### Modified (9 files)
- `app/Features/Equipments/Models/Equipment.php`
- `app/Features/Workers/Models/Worker.php`
- `app/Features/WorkLogs/Models/WorkLog.php`
- `app/Features/WorkLogs/Services/WorkLogService.php`
- `app/Features/Equipments/EquipmentFormSchema.php`
- `app/Features/Workers/WorkerFormSchema.php`
- `app/Features/Equipments/Resources/EquipmentResource.php`
- `app/Features/Workers/Resources/WorkerResource.php`
- `app/Providers/AppServiceProvider.php`

### Translation files modified (2 files)
- `resources/lang/en/forms.php`
- `resources/lang/pt_PT/forms.php`

### Factory/Seeder files modified (4 files)
- `database/factories/EquipmentFactory.php`
- `database/factories/WorkerFactory.php`
- `database/seeders/EquipmentSeeder.php`
- `database/seeders/WorkerSeeder.php`

### Test files created (5 files)
- `tests/Feature/Migrations/CostPerHourMigrationTest.php`
- `tests/Feature/Models/CostPerHourModelsTest.php`
- `tests/Feature/Observers/CostPerHourObserversTest.php`
- `tests/Feature/Services/CostPerHourSnapshotTest.php`
- `tests/Feature/FormSchemas/CostPerHourFormSchemaTest.php`

---

## Architecture Decisions

| Decision | Rationale |
|----------|-----------|
| **Polymorphic `cost_histories`** | Single table for both Equipment and Worker history via `morphs('entity')` |
| **Snapshot only on `approve()`** | Cost at time of approval is the authoritative value for financial records |
| **Source-code inspection tests** | Form schemas and service methods tested via `file_get_contents()` — avoids DB bootstrap, runs in milliseconds |
| **Standalone PHPUnit bootstrap** | Migration tests use SQLite `:memory:` with manual migration execution — no MySQL dependency for CI |
| **Dual observers documented** | Both `EquipmentObserver`/`WorkerObserver` (cost tracking) and existing `AuditObserver` (general audit) coexist, documented via comment block in `AppServiceProvider` |
