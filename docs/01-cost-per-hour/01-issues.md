# Issues — Sistema de Custo por Hora

> Gerado a partir de: [`docs/PRD_SISTEMA_CUSTO_POR_HORA.md`](docs/PRD_SISTEMA_CUSTO_POR_HORA.md)

---

## ISSUE-001: Migrations — adicionar colunas cost_per_hour (5 migrations)

**Labels:** `backend`, `database`, `migration`
**Milestone:** M1
**Estimativa:** 1h
**Dependências:** Nenhuma

### Descrição

Criar 5 migrations para adicionar suporte ao campo `cost_per_hour` (decimal 10,2, default 0.00) nas tabelas existentes e criar a tabela de histórico.

### Tasks

- [ ] Migration 1: Adicionar `cost_per_hour` à tabela `equipments` (após `description`)
- [ ] Migration 2: Adicionar `cost_per_hour` à tabela `workers` (após `team_id`)
- [ ] Migration 3: Adicionar `cost_per_hour` à tabela pivot `work_logs_workers` (após `worker_id`)
- [ ] Migration 4: Adicionar `cost_per_hour` à tabela pivot `work_log_equipment` (após `equipment_id`)
- [ ] Migration 5: Criar tabela `cost_histories` com `morphs('entity')`, `cost_per_hour`, `changed_by`, `effective_from`, `effective_until`, timestamps

### Critérios de Aceitação

- [ ] `php artisan migrate` executa sem erros
- [ ] `php artisan migrate:rollback` reverte todas as 5 migrations
- [ ] As colunas nas tabelas existentes têm default 0.00
- [ ] A tabela `cost_histories` tem índices em `entity_type+entity_id` e `effective_until`

---

## ISSUE-002: Modelos — cast e fillable para cost_per_hour + WorkLog pivot

**Labels:** `backend`, `model`
**Milestone:** M1
**Estimativa:** 30min
**Dependências:** ISSUE-001

### Descrição

Atualizar os modelos para reconhecerem o novo campo e criar o modelo `CostHistory`.

### Tasks

- [ ] [`Equipment`](app/Features/Equipments/Models/Equipment.php): adicionar `'cost_per_hour'` ao `$fillable` + cast `'decimal:2'`
- [ ] [`Worker`](app/Features/Workers/Models/Worker.php): adicionar `'cost_per_hour'` ao `$fillable` + cast `'decimal:2'`
- [ ] [`WorkLog`](app/Features/WorkLogs/Models/WorkLog.php): adicionar `->withPivot('cost_per_hour')` nas relações `workers()` e `equipment()`
- [ ] Criar [`CostHistory`](app/Shared/Models/CostHistory.php) com:
  - `$fillable`: `entity_type`, `entity_id`, `cost_per_hour`, `changed_by`, `effective_from`, `effective_until`
  - Casts: `decimal:2` para `cost_per_hour`, `datetime` para dates
  - Relação polimórfica: `entity(): MorphTo`
  - Scope `active()`: `whereNull('effective_until')`
  - Scope `effectiveAt($date)`: `effective_from <= $date AND (effective_until IS NULL OR effective_until > $date)`

### Critérios de Aceitação

- [ ] `Equipment::create(['cost_per_hour' => 15.50])` funciona
- [ ] `Worker::create(['cost_per_hour' => 12.00])` funciona
- [ ] `CostHistory::active()` retorna só registos sem `effective_until`
- [ ] `CostHistory::effectiveAt($date)` retorna registos vigentes na data

---

## ISSUE-003: Observers — EquipmentObserver e WorkerObserver

**Labels:** `backend`, `observer`
**Milestone:** M2
**Estimativa:** 30min
**Dependências:** ISSUE-002

### Descrição

Criar observers que registam automaticamente as alterações de `cost_per_hour` na tabela `cost_histories`.

### Tasks

- [ ] Criar [`EquipmentObserver`](app/Features/Equipments/Observers/EquipmentObserver.php) com método `updated()`:
  - Verificar `$equipment->wasChanged('cost_per_hour')`
  - Fechar registo ativo anterior: `effective_until = now()`
  - Criar novo registo com `effective_from = now()`, `effective_until = null`
  - Guardar `changed_by = auth()->id()`
- [ ] Criar [`WorkerObserver`](app/Features/Workers/Observers/WorkerObserver.php) com mesma lógica
- [ ] Registar ambos no [`AppServiceProvider`](app/Providers/AppServiceProvider.php) sob comentário `// ── Cost History Observers ──` (documentando dual-observer com `AuditObserver` existente): `Equipment::observe(EquipmentObserver::class)` e `Worker::observe(WorkerObserver::class)`

### Critérios de Aceitação

- [ ] Ao atualizar `cost_per_hour` de um Equipment, é criado um registo na `cost_histories`
- [ ] O registo anterior fica com `effective_until` preenchido
- [ ] O novo registo tem `effective_until = null`
- [ ] O mesmo funciona para Worker
- [ ] Alterações a outros campos (ex: `name`) não geram registos no histórico

---

## ISSUE-004: Snapshot — WorkLogService::approve()

**Labels:** `backend`, `service`, `business-logic`
**Milestone:** M3
**Estimativa:** 30min
**Dependências:** ISSUE-001, ISSUE-002

### Descrição

No momento da aprovação de um work log, copiar o `cost_per_hour` atual de cada worker e equipment para as respetivas tabelas pivot.

### Tasks

- [ ] No método [`WorkLogService::approve()`](app/Features/WorkLogs/Services/WorkLogService.php:86), após o update do status, adicionar:
  - `$workLog->loadMissing('workers', 'equipment')`
  - Loop por `$workLog->workers`: `updateExistingPivot($worker->id, ['cost_per_hour' => $worker->cost_per_hour])`
  - Loop por `$workLog->equipment`: `updateExistingPivot($equipment->id, ['cost_per_hour' => $equipment->cost_per_hour])`

### Critérios de Aceitação

- [ ] Ao aprovar um work log, as pivots `work_logs_workers` e `work_log_equipment` ficam com `cost_per_hour` preenchido
- [ ] O valor corresponde ao `cost_per_hour` atual do worker/equipment no momento da aprovação
- [ ] O `complete()` **não** altera as pivots

---

## ISSUE-005: Formulários — EquipmentFormSchema e WorkerFormSchema

**Labels:** `frontend`, `form`, `react`, `inertia`
**Milestone:** M4
**Estimativa:** 30min
**Dependências:** ISSUE-002

### Descrição

Adicionar campo `cost_per_hour` (NumberInput) aos formulários de criação e edição de Equipment e Worker.

### Tasks

- [ ] [`EquipmentFormSchema::create()`](app/Features/Equipments/EquipmentFormSchema.php:11): adicionar `NumberInput::make('cost_per_hour')` com label `forms.equipments.cost_per_hour` e rules `required|numeric|min:0|max:9999.99`
- [ ] [`EquipmentFormSchema::update()`](app/Features/Equipments/EquipmentFormSchema.php:64): adicionar o mesmo campo
- [ ] [`WorkerFormSchema::create()`](app/Features/Workers/WorkerFormSchema.php:11): adicionar `NumberInput::make('cost_per_hour')` com label `forms.workers.cost_per_hour` e rules `required|numeric|min:0|max:9999.99`
- [ ] [`WorkerFormSchema::update()`](app/Features/Workers/WorkerFormSchema.php:51): adicionar o mesmo campo

### Critérios de Aceitação

- [ ] O formulário "Novo Equipment" tem campo "Custo por Hora (€)"
- [ ] O formulário "Editar Equipment" tem campo "Custo por Hora (€)"
- [ ] O formulário "Novo Trabalhador" tem campo "Taxa Horária (€)"
- [ ] O formulário "Editar Trabalhador" tem campo "Taxa Horária (€)"
- [ ] Valores negativos são rejeitados
- [ ] Valores > 9999.99 são rejeitados

---

## ISSUE-006: API Resources — EquipmentResource e WorkerResource

**Labels:** `backend`, `api`
**Milestone:** M5
**Estimativa:** 15min
**Dependências:** ISSUE-002

### Descrição

Expor `cost_per_hour` nas respostas da API.

### Tasks

- [ ] [`EquipmentResource`](app/Features/Equipments/Resources/EquipmentResource.php): adicionar `'cost_per_hour' => $this->cost_per_hour`
- [ ] [`WorkerResource`](app/Features/Workers/Resources/WorkerResource.php): adicionar `'cost_per_hour' => $this->cost_per_hour`

### Critérios de Aceitação

- [ ] `GET /api/equipments` devolve `cost_per_hour` no JSON
- [ ] `GET /api/workers` devolve `cost_per_hour` no JSON

---

## ISSUE-007: Traduções — EN e PT_PT

**Labels:** `i18n`, `translations`
**Milestone:** M6
**Estimativa:** 15min
**Dependências:** ISSUE-005

### Descrição

Adicionar as chaves de tradução para os novos campos de formulário.

### Tasks

- [ ] [`resources/lang/en/forms.php`](resources/lang/en/forms.php):
  - `forms.equipments.cost_per_hour` = "Cost per Hour (€)"
  - `forms.equipments.cost_per_hour_helper` = "Hourly cost rate for this equipment"
  - `forms.workers.cost_per_hour` = "Hourly Rate (€)"
  - `forms.workers.cost_per_hour_helper` = "Worker's hourly pay rate"
- [ ] [`resources/lang/pt_PT/forms.php`](resources/lang/pt_PT/forms.php):
  - `forms.equipments.cost_per_hour` = "Custo por Hora (€)"
  - `forms.equipments.cost_per_hour_helper` = "Custo horário deste equipamento"
  - `forms.workers.cost_per_hour` = "Taxa Horária (€)"
  - `forms.workers.cost_per_hour_helper` = "Taxa horária do trabalhador"

### Critérios de Aceitação

- [ ] As chaves existem em ambos os ficheiros de lingua
- [ ] Os formulários exibem os labels corretos conforme o locale

---

## ISSUE-008: Seeders — cost_per_hour nos factories existentes

**Labels:** `backend`, `testing`, `seeder`
**Milestone:** M6
**Estimativa:** 15min
**Dependências:** ISSUE-002

### Descrição

Atualizar factories/seeders para incluir `cost_per_hour` nos dados de exemplo.

### Tasks

- [ ] Verificar se existem `EquipmentFactory` e `WorkerFactory` e adicionar `cost_per_hour` como `fake()->randomFloat(2, 5, 50)`

### Critérios de Aceitação

- [ ] `php artisan db:seed` gera equipments e workers com `cost_per_hour` preenchido


**Publicadas em GitHub:**
- [#5 RC-001](https://github.com/Parz1val6g/CMMS/issues/5) . [#6 RC-002](https://github.com/Parz1val6g/CMMS/issues/6) . [#7 RC-003](https://github.com/Parz1val6g/CMMS/issues/7) . [#8 RC-004](https://github.com/Parz1val6g/CMMS/issues/8)
- [#9 RC-005](https://github.com/Parz1val6g/CMMS/issues/9) . [#10 RC-006](https://github.com/Parz1val6g/CMMS/issues/10) . [#11 RC-007](https://github.com/Parz1val6g/CMMS/issues/11) . [#12 RC-008](https://github.com/Parz1val6g/CMMS/issues/12)
