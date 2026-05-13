# Plano: Custo por Hora (cost_per_hour)

## Decisões de Design

| Decisão | Escolha |
|---------|---------|
| Tipo de coluna | `decimal(10,2)` |
| Moeda | EUR (apenas) |
| Valor padrão | `0.00` |
| Nullable | Não (`required`) |
| Validação | `required\|numeric\|min:0\|max:9999.99` |
| Tabelas com `cost_per_hour` | `equipments`, `workers` |
| Tabelas pivot com `cost_per_hour` | `work_logs_workers`, `work_log_equipment` |
| Teams | ❌ Sem `cost_per_hour` |
| Tabela de histórico | `cost_histories` (polimórfica nativa Laravel) |
| Snapshot | Apenas no `WorkLogService::approve()` |
| Registo de alterações | Automático via Observer (`EquipmentObserver`, `WorkerObserver`) |
| Formulários | Adicionar ao create e edit de Equipment e Worker |
| API | Expor nos Resources temporariamente |
| Histórico | `effective_from` / `effective_until` (sem soft-delete) |

---

## Alterações Necessárias

### 1. Migrations (5 novas)

#### `database/migrations/XXXX_XX_XX_000001_add_cost_per_hour_to_equipments.php`
```php
Schema::table('equipments', function (Blueprint $table) {
    $table->decimal('cost_per_hour', 10, 2)->default(0.00)->after('description');
});
```

#### `database/migrations/XXXX_XX_XX_000002_add_cost_per_hour_to_workers.php`
```php
Schema::table('workers', function (Blueprint $table) {
    $table->decimal('cost_per_hour', 10, 2)->default(0.00)->after('team_id');
});
```

#### `database/migrations/XXXX_XX_XX_000003_add_cost_per_hour_to_work_logs_workers.php`
```php
Schema::table('work_logs_workers', function (Blueprint $table) {
    $table->decimal('cost_per_hour', 10, 2)->default(0.00)->after('worker_id');
});
```

#### `database/migrations/XXXX_XX_XX_000004_add_cost_per_hour_to_work_log_equipment.php`
```php
Schema::table('work_log_equipment', function (Blueprint $table) {
    $table->decimal('cost_per_hour', 10, 2)->default(0.00)->after('equipment_id');
});
```

#### `database/migrations/XXXX_XX_XX_000005_create_cost_histories_table.php`
```php
Schema::create('cost_histories', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->morphs('entity');                 // entity_type + entity_id (polimorfismo nativo)
    $table->decimal('cost_per_hour', 10, 2);
    $table->foreignUuid('changed_by')->nullable()->constrained('users')->nullOnDelete();
    $table->dateTime('effective_from');
    $table->dateTime('effective_until')->nullable(); // null = ativo
    $table->timestamps();

    $table->index('effective_until');
});
```

---

### 2. Modelos

#### [`Equipment`](app/Features/Equipments/Models/Equipment.php)
- Adicionar `'cost_per_hour'` ao `$fillable`
- Adicionar cast: `'cost_per_hour' => 'decimal:2'`

#### [`Worker`](app/Features/Workers/Models/Worker.php)
- Adicionar `'cost_per_hour'` ao `$fillable`
- Adicionar cast: `'cost_per_hour' => 'decimal:2'`

#### [`CostHistory`](app/Features/Shared/Models/CostHistory.php) (novo modelo)
```php
class CostHistory extends Model
{
    use Base;

    protected $fillable = [
        'entity_type', 'entity_id', 'cost_per_hour', 'changed_by',
        'effective_from', 'effective_until',
    ];

    protected $casts = [
        'cost_per_hour'  => 'decimal:2',
        'effective_from'  => 'datetime',
        'effective_until' => 'datetime',
    ];

    /**
     * Relação polimórfica nativa Laravel.
     * entity_type guarda o FQCN (ex: App\Features\Equipments\Models\Equipment)
     */
    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope: registo ativo (sem effective_until)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('effective_until');
    }

    /**
     * Scope: registo vigente numa determinada data
     */
    public function scopeEffectiveAt(Builder $query, Carbon $date): Builder
    {
        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_until')
                  ->orWhere('effective_until', '>', $date);
            });
    }
}
```

#### [`WorkLog`](app/Features/WorkLogs/Models/WorkLog.php)
- Atualizar relação `workers()`: adicionar `->withPivot('cost_per_hour')`
- Atualizar relação `equipment()`: adicionar `->withPivot('cost_per_hour')`

---

### 3. Observers

#### `App\Features\Equipments\Observers\EquipmentObserver` (novo)
```php
public function updated(Equipment $equipment): void
{
    if ($equipment->wasChanged('cost_per_hour')) {
        $now = now();

        // Fechar registo ativo anterior
        CostHistory::where('entity_type', get_class($equipment))
            ->where('entity_id', $equipment->id)
            ->whereNull('effective_until')
            ->update(['effective_until' => $now]);

        // Inserir novo registo
        CostHistory::create([
            'entity_type'    => get_class($equipment),
            'entity_id'      => $equipment->id,
            'cost_per_hour'  => $equipment->cost_per_hour,
            'changed_by'     => auth()->id(),
            'effective_from' => $now,
            'effective_until' => null,
        ]);
    }
}
```

#### `App\Features\Workers\Observers\WorkerObserver` (novo)
```php
public function updated(Worker $worker): void
{
    if ($worker->wasChanged('cost_per_hour')) {
        $now = now();

        CostHistory::where('entity_type', get_class($worker))
            ->where('entity_id', $worker->id)
            ->whereNull('effective_until')
            ->update(['effective_until' => $now]);

        CostHistory::create([
            'entity_type'    => get_class($worker),
            'entity_id'      => $worker->id,
            'cost_per_hour'  => $worker->cost_per_hour,
            'changed_by'     => auth()->id(),
            'effective_from' => $now,
            'effective_until' => null,
        ]);
    }
}
```

#### Registar observers no `AppServiceProvider`
```php
Equipment::observe(EquipmentObserver::class);
Worker::observe(WorkerObserver::class);
```

---

### 4. Lógica de Snapshot no WorkLogService

O snapshot do `cost_per_hour` é populado **apenas no approve()**, não no complete().

#### [`WorkLogService::approve()`](app/Features/WorkLogs/Services/WorkLogService.php:86)
Após o `$workLog->update(...)`, adicionar:
```php
// Snapshot do cost_per_hour atual de cada worker
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

> Nota: O `loadMissing` garante que as relações estão carregadas sem duplicar queries se já foram carregadas antes.

---

### 5. Formulários

#### [`EquipmentFormSchema`](app/Features/Equipments/EquipmentFormSchema.php)
Adicionar campo no `create()`:
```php
->field(
    NumberInput::make('cost_per_hour')
        ->setLabel(__('forms.equipments.cost_per_hour'))
        ->helperText(__('forms.equipments.cost_per_hour_helper'))
        ->setRules('required|numeric|min:0|max:9999.99')
)
```
Adicionar o mesmo no `update()`.

#### [`WorkerFormSchema`](app/Features/Workers/WorkerFormSchema.php)
Adicionar campo no `create()`:
```php
->field(
    NumberInput::make('cost_per_hour')
        ->setLabel(__('forms.workers.cost_per_hour'))
        ->helperText(__('forms.workers.cost_per_hour_helper'))
        ->setRules('required|numeric|min:0|max:9999.99')
)
```
Adicionar o mesmo no `update()`.

---

### 6. Language Files

#### [`resources/lang/en/forms.php`](resources/lang/en/forms.php)
```php
'equipments' => [
    // ... existing
    'cost_per_hour' => 'Cost per Hour (€)',
    'cost_per_hour_helper' => 'Hourly cost rate for this equipment',
],
'workers' => [
    // ... existing
    'cost_per_hour' => 'Hourly Rate (€)',
    'cost_per_hour_helper' => "Worker's hourly pay rate",
],
```

#### [`resources/lang/pt_PT/forms.php`](resources/lang/pt_PT/forms.php)
```php
'equipments' => [
    // ... existing
    'cost_per_hour' => 'Custo por Hora (€)',
    'cost_per_hour_helper' => 'Custo horário deste equipamento',
],
'workers' => [
    // ... existing
    'cost_per_hour' => 'Taxa Horária (€)',
    'cost_per_hour_helper' => 'Taxa horária do trabalhador',
],
```

---

### 7. API Resources

#### [`EquipmentResource`](app/Features/Equipments/Resources/EquipmentResource.php)
Adicionar: `'cost_per_hour' => $this->cost_per_hour,`

#### [`WorkerResource`](app/Features/Workers/Resources/WorkerResource.php)
Adicionar: `'cost_per_hour' => $this->cost_per_hour,`

---

## Resumo dos Ficheiros a Criar/Modificar

| Ficheiro | Ação |
|----------|------|
| `database/migrations/*_add_cost_per_hour_to_equipments.php` | **Criar** |
| `database/migrations/*_add_cost_per_hour_to_workers.php` | **Criar** |
| `database/migrations/*_add_cost_per_hour_to_work_logs_workers.php` | **Criar** |
| `database/migrations/*_add_cost_per_hour_to_work_log_equipment.php` | **Criar** |
| `database/migrations/*_create_cost_histories_table.php` | **Criar** |
| `app/Features/Shared/Models/CostHistory.php` | **Criar** |
| `app/Features/Equipments/Observers/EquipmentObserver.php` | **Criar** |
| `app/Features/Workers/Observers/WorkerObserver.php` | **Criar** |
| `app/Features/Equipments/Models/Equipment.php` | Modificar |
| `app/Features/Workers/Models/Worker.php` | Modificar |
| `app/Features/WorkLogs/Models/WorkLog.php` | Modificar |
| `app/Features/WorkLogs/Services/WorkLogService.php` | Modificar |
| `app/Features/Equipments/EquipmentFormSchema.php` | Modificar |
| `app/Features/Workers/WorkerFormSchema.php` | Modificar |
| `app/Features/Equipments/Resources/EquipmentResource.php` | Modificar |
| `app/Features/Workers/Resources/WorkerResource.php` | Modificar |
| `resources/lang/en/forms.php` | Modificar |
| `resources/lang/pt_PT/forms.php` | Modificar |
| `app/Providers/AppServiceProvider.php` | Modificar |
