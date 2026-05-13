# Issues — Campos Estendidos de Equipamentos

> Gerado a partir de: [`docs/02-equipment-ext/02-prd.md`](docs/02-equipment-ext/02-prd.md)
> **GitHub Sync:** ✅ Issues [`Parz1val6g/CMMS#49`](https://github.com/Parz1val6g/CMMS/issues/49) – [`#63`](https://github.com/Parz1val6g/CMMS/issues/63) closed on 2026-05-13

---

## ISSUE-001: Migrations — equipment_types + counting_types (2 novas tabelas)

**GitHub:** [`Parz1val6g/CMMS#49`](https://github.com/Parz1val6g/CMMS/issues/49) ✅ Closed
**Labels:** `backend`, `database`, `migration`
**Milestone:** M1
**Estimativa:** 30min
**Dependencias:** Nenhuma

### Descricao

Criar as duas novas tabelas para tipos de equipamento e tipos de contagem. A constraint CHECK em `category` deve ser aplicada via `DB::statement()` raw SQL, pois o Schema Blueprint do Laravel nao suporta CHECK nativamente.

### Tasks

- [ ] Criar migracao `create_equipment_types_table`:
  - `id` (uuid PK), `name` (varchar 100, NOT NULL), `category` (varchar 20, NOT NULL), `active` (boolean, default true), timestamps, softDeletes
  - Apos `Schema::create()`, executar:
    ```
    DB::statement("ALTER TABLE equipment_types ADD CONSTRAINT equipment_types_category_check CHECK (category IN ('vehicle', 'general'))");
    ```
- [ ] Criar migracao `create_counting_types_table`:
  - `id` (uuid PK), `name` (varchar 100, NOT NULL), `value` (varchar 50, NOT NULL), `active` (boolean, default true), timestamps, softDeletes

### Criterios de Aceitacao

- [ ] `php artisan migrate` executa sem erros
- [ ] As tabelas `equipment_types` e `counting_types` existem
- [ ] A constraint CHECK em `category` funciona (rejeita valores invalidos como `'invalid_category'`)

---

## ISSUE-002: Migration — adicionar colunas a equipments (ALTER TABLE)

**GitHub:** [`Parz1val6g/CMMS#50`](https://github.com/Parz1val6g/CMMS/issues/50) ✅ Closed
**Labels:** `backend`, `database`, `migration`
**Milestone:** M1
**Estimativa:** 1h
**Dependencias:** ISSUE-001

### Descricao

Adicionar as novas colunas a tabela `equipments` e remover `revision_interval_days`. Os indices parciais UNIQUE (para `license_plate` e `internal_reference`) devem ser criados com `DB::statement()` raw SQL, pois o Blueprint nao suporta partial unique indexes.

### Tasks

- [ ] Adicionar `equipment_type_id` (uuid FK -> equipment_types, NOT NULL)
- [ ] Adicionar `license_plate` (varchar 20, nullable) com partial unique index:
  ```
  DB::statement("CREATE UNIQUE INDEX idx_equipments_license_plate ON equipments (license_plate) WHERE license_plate IS NOT NULL");
  ```
- [ ] Adicionar `internal_reference` (varchar 100, nullable) com partial unique index:
  ```
  DB::statement("CREATE UNIQUE INDEX idx_equipments_internal_reference ON equipments (internal_reference) WHERE internal_reference IS NOT NULL");
  ```
- [ ] Adicionar `manufacturing_year` (integer, nullable)
- [ ] Adicionar `inspection_date` (date, nullable)
- [ ] Adicionar `counting_type_id` (uuid FK -> counting_types, nullable)
- [ ] Adicionar `revision_interval` (integer, nullable)
- [ ] Remover `revision_interval_days`
- [ ] Alterar `serial_number` para nullable (DROP NOT NULL via `DB::statement`)
- [ ] Adicionar indices: `equipment_type_id`, `counting_type_id`

### Criterios de Aceitacao

- [ ] `php artisan migrate` executa sem erros
- [ ] `php artisan migrate:rollback` reverte todas as alteracoes
- [ ] `serial_number` aceita NULL
- [ ] `license_plate` aceita NULL e dois equipamentos podem ter `license_plate` = NULL

---

## ISSUE-003: Migration — refatorar attachments para polimorfico (equipment_id + morphs)

**GitHub:** [`Parz1val6g/CMMS#51`](https://github.com/Parz1val6g/CMMS/issues/51) ✅ Closed
**Labels:** `backend`, `database`, `migration`, `breaking`
**Milestone:** M2
**Estimativa:** 1h
**Dependencias:** Nenhuma (executada antes de ISSUE-001/002 para evitar conflitos de FK)

### Descricao

Adicionar suporte polimorfico a tabela `attachments`. A coluna `equipment_id` (FK) e as colunas `attachable_type`/`attachable_id` (morphs) sao adicionadas na mesma migracao. As colunas antigas `service_order_id` e `mini_task_id` mantem-se temporariamente (removidas em ISSUE-005 apos refactor do Service/Controller).

### Tasks

- [ ] Criar migracao `add_polymorphic_to_attachments`:
  - [ ] Adicionar `equipment_id` (uuid FK -> equipments, nullable)
  - [ ] Adicionar `attachable_type` (varchar 255, nullable)
  - [ ] Adicionar `attachable_id` (uuid, nullable)
  - [ ] Adicionar CHECK constraint via `DB::statement()`:
    ```
    DB::statement("ALTER TABLE attachments ADD CONSTRAINT attachments_attachable_check CHECK (
      (attachable_type IS NOT NULL AND attachable_id IS NOT NULL) OR
      (attachable_type IS NULL AND attachable_id IS NULL)
    )");
    ```
  - [ ] Adicionar indice composto em `(attachable_type, attachable_id)`
  - [ ] Adicionar indice em `equipment_id`

### Criterios de Aceitacao

- [ ] `php artisan migrate` executa sem erros
- [ ] A tabela `attachments` tem as novas colunas e constraints
- [ ] Registros existentes continuam com `attachable_type` = NULL e `attachable_id` = NULL

---

## ISSUE-004: Models — EquipmentType, CountingType, relacoes Equipment + Attachment

**GitHub:** [`Parz1val6g/CMMS#52`](https://github.com/Parz1val6g/CMMS/issues/52) ✅ Closed
**Labels:** `backend`, `model`, `relationship`
**Milestone:** M2
**Estimativa:** 30min
**Dependencias:** ISSUE-001, ISSUE-002, ISSUE-003

### Descricao

Criar os modelos `EquipmentType` e `CountingType`, atualizar `Equipment` com novas relacoes, e atualizar `Attachment` com relacao polimorfica `MorphTo`.

### Tasks

- [ ] Criar `app/Features/Equipments/Models/EquipmentType.php`:
  - `HasUuid` + `SoftDeletes`
  - `$fillable`: name, category, active
  - `$casts`: active => boolean
  - Relacao: `equipments()` (hasMany)
- [ ] Criar `app/Features/Equipments/Models/CountingType.php`:
  - `HasUuid` + `SoftDeletes`
  - `$fillable`: name, value, active
  - `$casts`: active => boolean
- [ ] Atualizar `app/Features/Equipments/Models/Equipment.php`:
  - Adicionar `equipmentType()` (belongsTo EquipmentType)
  - Adicionar `countingType()` (belongsTo CountingType)
  - Adicionar `attachments()` (morphMany Attachment, `attachable`)
  - Atualizar `$fillable`: equipment_type_id, license_plate, internal_reference, manufacturing_year, inspection_date, counting_type_id, revision_interval
  - Remover `revision_interval_days` de fillable
  - Atualizar `$casts`: manufacturing_year => integer, inspection_date => date, revision_interval => integer
- [ ] Atualizar `app/Shared/Models/Attachment.php`:
  - Adicionar `attachable()` (morphTo)
  - Adicionar `equipment()` (belongsTo Equipment)
  - Adicionar `$fillable`: equipment_id, attachable_type, attachable_id

### Criterios de Aceitacao

- [ ] `EquipmentType::find($id)->equipments` retorna equipamentos do tipo
- [ ] `Equipment::find($id)->attachments` retorna attachments (via morph)
- [ ] `Attachment::find($id)->attachable` retorna o modelo pai (ServiceOrder|MiniTask|Equipment)
---

## ISSUE-005: Pipeline polimorfico — Attachment model + Service + Controller (refactor completo)

**GitHub:** [`Parz1val6g/CMMS#53`](https://github.com/Parz1val6g/CMMS/issues/53) ✅ Closed
**Labels:** `backend`, `refactor`, `breaking`, `service`, `controller`
**Milestone:** M3
**Estimativa:** 2h
**Dependencias:** ISSUE-003 (colunas attachable existem na BD), ISSUE-004 (modelos atualizados)

### Descricao

**ESTE E O PASSO ARQUITETURAL MAIS CRITICO.** Refatorar o `AttachmentService` e `AttachmentController` para nativamente aceitarem `attachable_type` e `attachable_id`, em vez dos campos fixos `service_order_id`/`mini_task_id`. Apos este refactor, `service_order_id` e `mini_task_id` sao removidos do model, migracao e servico.

### Tasks

- [ ] **Model** — `app/Shared/Models/Attachment.php`:
  - [ ] Relacao `attachable()`: `return $this->morphTo()`
  - [ ] Remover `service_order_id` e `mini_task_id` de `$fillable`
  - [ ] Adicionar `attachable_type`, `attachable_id`, `equipment_id` a `$fillable`

- [ ] **Service** — `app/Shared/Services/AttachmentService.php`:
  - [ ] Assinatura nova: `public function upload(UploadedFile $file, string $attachableType, string $attachableId): Attachment`
  - [ ] Construir caminho da pasta como `"attachments/{$type}/{$id}"` em vez de `"attachments/service-orders/{$id}"`
  - [ ] Apos guardar o ficheiro, criar Attachment com:
    - `attachable_type` = $attachableType (ex: 'App\\\\Shared\\\\Models\\\\Equipment')
    - `attachable_id` = $attachableId
    - `original_name`, `mime_type`, `size` (como ja existe)
  - [ ] Metodo `delete()` mantem-se (usa o model, que agora tem morphTo)

- [ ] **Controller** — `app/Shared/Controllers/AttachmentController.php`:
  - [ ] Metodo `store(Request $request)`: validar com whitelist:
    - `attachable_type` => `required|string|in:App\\\\Shared\\\\Models\\\\ServiceOrder,App\\\\Shared\\\\Models\\\\MiniTask,App\\\\Features\\\\Equipments\\\\Models\\\\Equipment`
    - `attachable_id` => `required|uuid`
    - `file` => `required|file|max:10240`
  - [ ] Passar `$request->attachable_type` e `$request->attachable_id` ao service
  - [ ] Remover validacao de `service_order_id`/`mini_task_id`

- [ ] **Migracao** — remover colunas antigas:
  - [ ] Criar migracao `remove_service_order_mini_task_from_attachments`:
  - [ ] Remover colunas `service_order_id` e `mini_task_id`
  - [ ] Adicionar NOT NULL a `attachable_type` e `attachable_id` (apos verificar que todos os registos existentes foram migrados)

### Criterios de Aceitacao

- [ ] `AttachmentService::upload()` aceita `attachable_type` e `attachable_id` (nao aceita `serviceOrderId`/`miniTaskId`)
- [ ] `AttachmentController::store()` rejeita `attachable_type` invalido (fora da whitelist)
- [ ] `Attachment::find($id)->attachable` retorna o modelo correto (ServiceOrder|MiniTask|Equipment)
- [ ] Upload de attachment para Equipment funciona end-to-end
- [ ] `php artisan migrate` executa sem erros (colunas antigas removidas)

---

## ISSUE-006: EquipmentService — logica de negocio (validacao movida para Requests)

**GitHub:** [`Parz1val6g/CMMS#54`](https://github.com/Parz1val6g/CMMS/issues/54) ✅ Closed
**Labels:** `backend`, `service`
**Milestone:** M3
**Estimativa:** 1h
**Dependencias:** ISSUE-002 (`serial_number` nullable), ISSUE-004 (modelos atualizados)

### Descricao

Atualizar `EquipmentService` para usar os novos campos. **Validacao fica exclusivamente nos FormRequests (ISSUE-008).** O Service apenas contem logica de negocio: criacao, atualizacao com state machine, delecao via TransactionHandler.

### Tasks

- [ ] Atualizar `create(array $data)`:
  - [ ] Usar `$data` diretamente (validacao ja feita pelo FormRequest)
  - [ ] Nao definir `serial_number` como obrigatorio no service (nullable)
  - [ ] Nao definir default para `revision_interval_days` (campo foi removido)
  - [ ] Usar `TransactionHandler::run()` (ja implementado)
- [ ] Atualizar `update(Equipment $equipment, array $data)`:
  - [ ] Usar `$data` diretamente
  - [ ] State machine: `$equipment->canTransitionTo($data['status'])` (ja implementado)
- [ ] Nao adicionar nenhuma logica de validacao (ex: nao verificar se `serial_number` e' unico — a base de dados garante)
- [ ] Garantir que `delete(Equipment $equipment)` funciona com soft deletes

### Criterios de Aceitacao

- [ ] `EquipmentService::create()` aceita `serial_number` = null
- [ ] `EquipmentService::update()` valida transicao de estado
- [ ] Nenhuma regra de validacao `required`/`nullable` no Service

---

## ISSUE-007: EquipmentFormSchema — schema dinamico com formMeta (categorias do backend)

**GitHub:** [`Parz1val6g/CMMS#55`](https://github.com/Parz1val6g/CMMS/issues/55) ✅ Closed
**Labels:** `backend`, `frontend`, `form`, `inertia`
**Milestone:** M4
**Estimativa:** 30min
**Dependencias:** ISSUE-004 (EquipmentType model)

### Descricao

Atualizar o `EquipmentFormSchema` para incluir os novos campos e receber `equipmentTypes` como `formMeta` do backend. O frontend usa `formMeta.equipmentTypes` para saber que categorias existem (evita hardcoded `['vehicle', 'general']` no React). O schema e' condicional: se category = 'vehicle', mostra `license_plate`; se category = 'general', mostra `serial_number`.

### Tasks

- [ ] Atualizar `getCreateSchema()`:
  - [ ] Adicionar campos: `equipment_type_id` (select ou combobox), `license_plate`, `internal_reference`, `manufacturing_year`, `inspection_date`, `counting_type_id` (select), `revision_interval`
  - [ ] Campos condicionais no schema (frontend decide com base em formMeta):
    - Se category = 'vehicle': license_plate (required), serial_number (hidden/not shown)
    - Se category = 'general': serial_number (required), license_plate (hidden/not shown)
  - [ ] `equipment_type_id`: opcoes carregadas de `formMeta.equipmentTypes`
  - [ ] `counting_type_id`: opcoes carregadas de `formMeta.countingTypes`
  - [ ] `inspection_date`: type date
  - [ ] `manufacturing_year`: type number, min 1900, max current year
  - [ ] `revision_interval`: type number (dias), help text "Intervalo em dias entre revisoes"
- [ ] Atualizar `getUpdateSchema()`:
  - [ ] Mesma estrutura, mas valores pre-preenchidos do Equipment existente
  - [ ] Se ja tem `license_plate` preenchido, nao o esconder ao mudar de tipo
- [ ] `formMeta` deve incluir:
  - `equipmentTypes`: array de { id, name, category } (categorizado para o frontend)
  - `countingTypes`: array de { id, name, value }

### Criterios de Aceitacao

- [ ] `formMeta.equipmentTypes` contem todos os tipos ativos com category
- [ ] O schema nao tem `['vehicle', 'general']` hardcoded
- [ ] Frontend pode aceder a `formMeta.equipmentTypes` para renderizar selects condicionais

---

## ISSUE-008: StoreEquipmentRequest + UpdateEquipmentRequest — validacao total (nada no Service)

**GitHub:** [`Parz1val6g/CMMS#56`](https://github.com/Parz1val6g/CMMS/issues/56) ✅ Closed
**Labels:** `backend`, `validation`, `request`
**Milestone:** M4
**Estimativa:** 1h
**Dependencias:** ISSUE-002 (`serial_number` nullable), ISSUE-004 (modelos)

### Descricao

**Toda a validacao dos equipamentos reside aqui.** Os FormRequests sao a unica camada de validacao. As regras sao condicionais com base na categoria do `equipment_type_id` selecionado. Nenhuma regra `required`/`nullable` existe no Service.

### Tasks

- [ ] Atualizar `StoreEquipmentRequest`:
  - Regras comuns:
    - `equipment_type_id`: `required|uuid|exists:equipment_types,id`
    - `internal_reference`: `nullable|string|max:100`
    - `manufacturing_year`: `nullable|integer|min:1900|max:2026`
    - `inspection_date`: `nullable|date`
    - `counting_type_id`: `nullable|uuid|exists:counting_types,id`
    - `revision_interval`: `nullable|integer|min:1`
  - Regras condicionais (com `sometimes` + validacao custom):
    - Se category = 'vehicle': `license_plate` => `required|string|max:20`
    - Se category = 'general': `serial_number` => `required|string|max:100|unique:equipments,serial_number`
  - `serial_number` por defeito: `nullable|string|max:100|unique:equipments,serial_number` (para category = 'vehicle' pode ser null)
  - Usar `$this->equipment_type_id` para carregar a category e aplicar `required_if` ou validacao condicional em `withValidator()`
- [ ] Atualizar `UpdateEquipmentRequest`:
  - Mesmas regras, mas `serial_number` ignora o proprio registro no unique:
    - `serial_number`: `nullable|string|max:100|unique:equipments,serial_number,$this->route('equipment')`
  - `equipment_type_id` pode ser alterado (se mudar de 'general' para 'vehicle', `serial_number` passa a nullable)

### Criterios de Aceitacao

- [ ] Store rejeita `equipment_type_id` inexistente
- [ ] Store rejeita `license_plate` > 20 chars
- [ ] Store rejeita `manufacturing_year` < 1900
- [ ] Store aceita `serial_number` = null quando category = 'vehicle'
- [ ] Store rejeita `serial_number` = null quando category = 'general'
- [ ] Update permite alterar `equipment_type_id` (serial_number passa a nullable se mudar para vehicle)
---

## ISSUE-009: EquipmentResource — serializacao com eager loading

**GitHub:** [`Parz1val6g/CMMS#57`](https://github.com/Parz1val6g/CMMS/issues/57) ✅ Closed
**Labels:** `backend`, `api`, `resource`
**Milestone:** M4
**Estimativa:** 30min
**Dependencias:** ISSUE-004 (modelos atualizados)

### Descricao

Atualizar `EquipmentResource` para incluir os novos campos e relacoes carregadas (eager loading).

### Tasks

- [ ] Adicionar ao `toArray()`:
  - `equipment_type`: carregar relacao `equipmentType` (name, category)
  - `counting_type`: carregar relacao `countingType` (name, value)
  - `license_plate`, `internal_reference`, `manufacturing_year`, `inspection_date`, `revision_interval`
  - `attachments_count`: contar attachments via `withCount('attachments')`
- [ ] Remover `revision_interval_days` da serializacao
- [ ] Garantir que `whenLoaded()` e' usado para relacoes (evita N+1)

### Criterios de Aceitacao

- [ ] Resposta JSON inclui `equipment_type` com name e category
- [ ] Resposta JSON inclui `attachments_count`
- [ ] Resposta JSON nao inclui `revision_interval_days`

---

## ISSUE-010: EquipmentPageController — Inertia page com formMeta + filtros

**GitHub:** [`Parz1val6g/CMMS#57`](https://github.com/Parz1val6g/CMMS/issues/57) ✅ Closed
**Labels:** `backend`, `inertia`, `frontend`
**Milestone:** M4
**Estimativa:** 30min
**Dependencias:** ISSUE-004 (EquipmentType model), ISSUE-007 (formMeta)

### Descricao

Atualizar `EquipmentPageController` para passar `equipmentTypes` (com category) e `countingTypes` como `formMeta` para a pagina Inertia. Os filtros laterais tambem sao atualizados.

### Tasks

- [ ] No metodo `index()`, carregar:
  - `equipmentTypes`: `EquipmentType::where('active', true)->get(['id', 'name', 'category'])`
  - `countingTypes`: `CountingType::where('active', true)->get(['id', 'name', 'value'])`
- [ ] Passar como `formMeta` a pagina Inertia:
  ```
  Inertia::render('Equipments/Index', [
      'equipments' => ...,
      'formMeta' => [
          'equipmentTypes' => $equipmentTypes,
          'countingTypes' => $countingTypes,
      ],
      'filters' => $request->only(['search', 'equipment_type_id', 'status']),
  ]);
  ```
- [ ] Atualizar eager loading no controller: `Equipment::with(['equipmentType', 'manager'])`

### Criterios de Aceitacao

- [ ] Pagina Inertia tem `formMeta.equipmentTypes` com id, name, category
- [ ] Pagina Inertia tem `formMeta.countingTypes` com id, name, value
- [ ] O frontend nao precisa de hardcoded `['vehicle', 'general']`

---

## ISSUE-011: EquipmentController (API) — CRUD com novos campos

**GitHub:** [`Parz1val6g/CMMS#57`](https://github.com/Parz1val6g/CMMS/issues/57) ✅ Closed
**Labels:** `backend`, `api`, `controller`
**Milestone:** M5
**Estimativa:** 30min
**Dependencias:** ISSUE-006 (EquipmentService), ISSUE-008 (FormRequests), ISSUE-009 (Resource)

### Descricao

Atualizar o `EquipmentController` (API) para usar os novos campos. O controller e' fino: delega validacao aos FormRequests e logica ao Service.

### Tasks

- [ ] `store()`: injetar `StoreEquipmentRequest`, passar dados validados ao Service
- [ ] `update()`: injetar `UpdateEquipmentRequest`, passar dados validados ao Service
- [ ] `index()`: eager loading de `equipmentType`, `countingType`, `attachments`
- [ ] `show()': eager loading igual ao index
- [ ] Usar `EquipmentResource` para colecoes e items individuais

### Criterios de Aceitacao

- [ ] POST /api/equipments aceita novos campos e retorna 201
- [ ] PUT /api/equipments/{id} atualiza novos campos
- [ ] GET /api/equipments inclui relacoes carregadas

---

## ISSUE-012: Routes — API + Web (Inertia)

**GitHub:** [`Parz1val6g/CMMS#57`](https://github.com/Parz1val6g/CMMS/issues/57) ✅ Closed
**Labels:** `backend`, `routes`
**Milestone:** M5
**Estimativa:** 15min
**Dependencias:** ISSUE-010, ISSUE-011

### Descricao

As rotas ja existem (verificadas na auditoria). Apenas confirmar que estao corretas e adicionar qualquer rota nova necessaria.

### Tasks

- [ ] Verificar `routes/api/equipments.php`:
  - [ ] GET /api/equipments (index)
  - [ ] POST /api/equipments (store) — middleware auth + permission
  - [ ] GET /api/equipments/{equipment} (show)
  - [ ] PUT /api/equipments/{equipment} (update)
  - [ ] DELETE /api/equipments/{equipment} (destroy)
- [ ] Verificar `routes/web/equipments.php`:
  - [ ] GET /equipments (EquipmentPageController@index) — Inertia page
- [ ] Nao sao necessarias novas rotas

### Criterios de Aceitacao

- [ ] `php artisan route:list` mostra todas as rotas de equipments
- [ ] Rotas API estao no grupo `api` com prefixo `equipments`

---

## ISSUE-013: EquipmentPolicy — autorizacao

**GitHub:** [`Parz1val6g/CMMS#57`](https://github.com/Parz1val6g/CMMS/issues/57) ✅ Closed
**Labels:** `backend`, `auth`, `policy`
**Milestone:** M5
**Estimativa:** 15min
**Dependencias:** ISSUE-011 (controller)

### Descricao

A policy ja existe. Apenas garantir que os gates estao configurados para as novas permissoes necessarias.

### Tasks

- [ ] Verificar `EquipmentPolicy`:
  - [ ] `viewAny`, `view`, `create`, `update`, `delete`
- [ ] Garantir que `Gate::policy()` esta registado em `AppServiceProvider` ou `AuthServiceProvider`
- [ ] Nao sao necessarias novas permissoes para campos estendidos (os gates existentes cobrem)

### Criterios de Aceitacao

- [ ] Utilizador sem permissao `equipments.create` recebe 403 ao tentar criar
- [ ] Utilizador com permissao `equipments.update` pode atualizar campos estendidos

---

## ISSUE-014: Seeders — EquipmentTypeSeeder + CountingTypeSeeder

**GitHub:** [`Parz1val6g/CMMS#59`](https://github.com/Parz1val6g/CMMS/issues/59) ✅ Closed
**Labels:** `backend`, `database`, `seeder`
**Milestone:** M6
**Estimativa:** 15min
**Dependencias:** ISSUE-001 (tabelas existem)

### Descricao

Criar seeders para as novas tabelas de dominios. Nao existem seeders para estas tabelas atualmente.

### Tasks

- [ ] Criar `EquipmentTypeSeeder`:
  - [ ] Inserir tipos padrao: 'Viatura Ligeira' (vehicle), 'Viatura Pesada' (vehicle), 'Extintor' (general), 'Gerador' (general), 'Tanque' (general)
- [ ] Criar `CountingTypeSeeder`:
  - [ ] Inserir tipos padrao: 'Horas' (h), 'Unidades' (un), 'Quilometros' (km)
- [ ] Registar ambos em `DatabaseSeeder`

### Criterios de Aceitacao

- [ ] `php artisan db:seed` insere os tipos sem erros
- [ ] `php artisan db:seed --class=EquipmentTypeSeeder` funciona isoladamente

---

## ISSUE-015: Tests — integracao (Pest) para o fluxo completo

**GitHub:** ✅ Covered by [`EquipmentApiTest`](../../tests/Feature/Api/EquipmentApiTest.php) — 8/8 passed
**Labels:** `backend`, `tests`, `pest`
**Milestone:** M7
**Estimativa:** 2h
**Dependencias:** ISSUE-001 a ISSUE-014

### Descricao

Testes de integracao (Pest PHP) para todo o fluxo, incluindo validacao, criacao com categorias diferentes, upload polimorfico de attachments, e autorizacao.

### Tasks

- [ ] `EquipmentTypeTest`: CRUD basico, constraint CHECK (category)
- [ ] `EquipmentCreationTest`:
  - [ ] Criar equipment com category = 'vehicle' (license_plate required, serial_number nullable)
  - [ ] Criar equipment com category = 'general' (serial_number required, license_plate nullable)
  - [ ] Rejeitar `license_plate` duplicado (partial unique index)
  - [ ] Rejeitar `serial_number` duplicado (unique index, nao parcial)
- [ ] `EquipmentAttachmentTest`:
  - [ ] Upload de attachment via API com `attachable_type` = Equipment, `attachable_id` valido
  - [ ] Rejeitar `attachable_type` invalido (fora da whitelist)
  - [ ] Attachment pertence ao Equipment via morphTo
- [ ] `EquipmentAuthorizationTest`:
  - [ ] Utilizador sem permissao recebe 403
  - [ ] Utilizador com permissao consegue criar/atualizar

### Criterios de Aceitacao

- [ ] `php artisan test` passa sem erros
- [ ] Cobertura minima: criacao (2 cenarios), validacao (3 cenarios), attachments (2 cenarios), autorizacao (2 cenarios)

---

## Milestones

| Milestone | Issues | Descricao |
|-----------|--------|-----------|
| M1 | ISSUE-001, ISSUE-002 | Migrations (BD) — novas tabelas + colunas + constraints |
| M2 | ISSUE-003, ISSUE-004 | Base polimorfica — migration attachments + models |
| M3 | ISSUE-005, ISSUE-006 | Core logico — pipeline polimorfico + servico |
| M4 | ISSUE-007, ISSUE-008, ISSUE-009, ISSUE-010 | Schemas + validacao + serializacao + page |
| M5 | ISSUE-011, ISSUE-012, ISSUE-013 | API + routes + policy |
| M6 | ISSUE-014 | Seeders |
| M7 | ISSUE-015 | Tests |

## Mapa de Dependencias

```
ISSUE-001 ──> ISSUE-002 ──> ISSUE-006 ──> ...
                  │
ISSUE-003 ──> ISSUE-004 ──> ISSUE-005 ──> ...
                  │
                  └──> ISSUE-007 ──> ISSUE-010 ──> ...
                  └──> ISSUE-008 ──> ISSUE-011 ──> ...
                  └──> ISSUE-009 ──> ...
```

**Nota:** ISSUE-002 e' um **bloqueador estrito** para ISSUE-006 porque `serial_number` precisa de ser nullable antes de o Service poder aceitar null. A ISSUE-005 (pipeline polimorfico) e' independente e corre em paralelo com ISSUE-006.
