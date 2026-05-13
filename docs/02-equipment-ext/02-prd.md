# PRD — Campos Estendidos de Equipamentos

**Product Requirements Document**

| Campo | Valor |
|-------|-------|
| **Data** | 2026-05-12 |
| **Versao** | 1.0 |
| **Status** | Aprovado |
| **Prioridade** | Alta |

---

## 1. Objetivo

Adicionar campos especializados a entidade **Equipment** para suportar a gestao completa de frota de veiculos e equipamentos de espetaculos: tipo de equipamento, matricula, inspecao, revisoes, anexos, e referencias internas.

---

## 2. Problema

A tabela `equipments` atual e generica demais para o negocio:

1. **Sem distincao** entre tipos de equipamento (veiculo pesado, palco, gerador, etc.)
2. **Sem suporte** a veiculos — falta matricula, data de inspecao, contagem por KM
3. **Serial number obrigatorio** para todos, mas veiculos usam matricula
4. **Revisao fixa em dias** — nao suporta contagem por KM, horas, ou eventos
5. **Sem anexos** — nao e possivel anexar livrete, manuais, certificados
6. **Sem referencia interna** — a empresa nao pode usar o seu proprio codigo de identificacao

---

## 3. Escopo

### Incluido

- ✅ Tabela `equipment_types` (dinamica, gerivel pelo utilizador) com `category`
- ✅ Tabela `counting_types` (dinamica, gerivel pelo utilizador)
- ✅ Colunas novas na tabela `equipments` (ver secao 4)
- ✅ Refatoracao da tabela `attachments` para polimorfica
- ✅ Validacao condicional (vehicle vs general) no backend
- ✅ Formularios de criacao/edicao atualizados
- ✅ APIs e Resources atualizados
- ✅ Seeders para dados iniciais
- ✅ Traducoes EN e PT_PT

### Excluido

- ❌ Alteracao no workflow de revisoes (equipment_revisions) — mantem-se como esta
- ❌ Notificacoes de revisao vencida (sera fase posterior)
- ❌ Relatorios ou dashboards

---

## 4. Schema da Base de Dados

### 4.1 Nova Tabela: `equipment_types`

```sql
CREATE TABLE equipment_types (
    id         UUID PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    category   VARCHAR(20)  NOT NULL CHECK (category IN ('vehicle', 'general')),
    active     BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

**Seed data inicial:**

| name | category |
|------|----------|
| Veiculo Pesado | vehicle |
| Veiculo Leve | vehicle |
| Palco | general |
| Gerador | general |
| Sistema de Som | general |
| Iluminacao | general |
| Ferramenta | general |
| Outro | general |

### 4.2 Nova Tabela: `counting_types`

```sql
CREATE TABLE counting_types (
    id         UUID PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,  -- "Dias", "Quilometros"
    value      VARCHAR(50)  NOT NULL,  -- "days", "km"
    active     BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);
```

**Seed data inicial:**

| name | value |
|------|-------|
| Dias | days |
| Meses | months |
| Anos | years |
| Quilometros | km |
| Horas de uso | hours |

### 4.3 Tabela Alterada: `equipments`

| Coluna | Estado | Tipo | Notas |
|--------|--------|------|-------|
| id | existente | uuid PK | |
| **equipment_type_id** | **novo** | **uuid FK** | **NOT NULL** -> equipment_types |
| name | existente | varchar(200) | |
| brand | existente | varchar(150) nullable | |
| model | existente | varchar(150) nullable | |
| serial_number | **alterado** | varchar(250) | **passa a nullable** (required so se category != vehicle) |
| **license_plate** | **novo** | **varchar(20)** | **nullable, unique** (required so se category = vehicle) |
| **internal_reference** | **novo** | **varchar(100)** | **nullable, unique** |
| **manufacturing_year** | **novo** | **integer** | **nullable** (4 digitos) |
| **inspection_date** | **novo** | **date** | **nullable** (required so se category = vehicle) |
| **counting_type_id** | **novo** | **uuid FK** | **nullable** -> counting_types |
| revision_interval_days | **removido** | — | Substituido pelos campos abaixo |
| **revision_interval** | **novo** | **integer** | **nullable** — valor numerico do intervalo |
| last_revision_date | existente | datetime nullable | |
| next_revision_date | existente | datetime nullable | |
| manager_id | existente | uuid FK | -> users |
| status | existente | varchar(50) | |
| is_loanable | existente | boolean | |
| description | existente | varchar(250) nullable | |
| timestamps | existente | | |
| softDeletes | existente | | |

**Indices:**
- `INDEX(equipment_type_id)`
- `UNIQUE(license_plate)` — apenas quando NOT NULL (unique index with filter)
- `UNIQUE(internal_reference)` — apenas quando NOT NULL
- `INDEX(counting_type_id)`

### 4.4 Tabela Refatorada: `attachments`

**Antes:**

```sql
attachments (
    id               UUID PK,
    service_order_id UUID FK nullable,
    mini_task_id     UUID FK nullable,
    file_path        VARCHAR(250),
    file_name        VARCHAR(250),
    mime_type        VARCHAR(50) nullable,
    -- CONSTRAINT CHECK: um dos dois deve ser preenchido
);
```

**Depois (polimorfico):**

```sql
attachments (
    id               UUID PK,
    attachable_type  VARCHAR(100) NOT NULL,  -- "App\Models\Equipment"
    attachable_id    UUID NOT NULL,           -- ID do equipamento/SO/mini-task
    file_path        VARCHAR(250) NOT NULL,
    file_name        VARCHAR(250) NOT NULL,
    mime_type        VARCHAR(50) nullable,
    timestamps,
    softDeletes,

    INDEX(attachable_type, attachable_id)
);
```

**Migracao:** Os registos existentes de service_orders e mini_tasks serao convertidos:
- `service_order_id` X -> `attachable_type` = "App\Models\ServiceOrder", `attachable_id` = X
- `mini_task_id` Y -> `attachable_type` = "App\Models\MiniTask", `attachable_id` = Y

---

## 5. Logica de Validacao

### 5.1 Regras Condicionais (Backend)

```
SE equipment_type.category == 'vehicle':
    license_plate     = required|unique
    inspection_date   = required|date
    serial_number     = nullable (opcional)

SE equipment_type.category != 'vehicle':
    serial_number     = required|unique
    license_plate     = nullable (sempre null)
    inspection_date   = nullable (sempre null)
```

### 5.2 Counting Type

```
SE counting_type.value IN ('days', 'months', 'years'):
    revision_interval = numero de dias/meses/anos entre revisoes
    next_revision_date = last_revision_date + revision_interval

SE counting_type.value == 'km':
    next_revision_date = NULL (baseado em KM, nao em tempo)
    (fase futura: alerta quando KM atingido)

SE counting_type.value == 'hours':
    next_revision_date = NULL (baseado em horas de uso)
    (fase futura: alerta quando horas atingidas)
```

---

## 6. Alteracoes no Codigo

### 6.1 Novos Modelos

| Ficheiro | Descricao |
|----------|-----------|
| `app/Features/Equipments/Models/EquipmentType.php` | Modelo para equipment_types |
| `app/Features/Equipments/Models/CountingType.php` | Modelo para counting_types |

### 6.2 Modelo Alterado: `Equipment.php`

**Novos fillable:**
```php
'equipment_type_id',
'license_plate',
'internal_reference',
'manufacturing_year',
'inspection_date',
'counting_type_id',
'revision_interval',
```

**Novos casts:**
```php
'manufacturing_year' => 'integer',
'inspection_date' => 'date',
'revision_interval' => 'integer',
```

**Novas relacoes:**
```php
public function equipmentType(): BelongsTo
{
    return $this->belongsTo(EquipmentType::class);
}

public function countingType(): BelongsTo
{
    return $this->belongsTo(CountingType::class);
}

public function attachments(): MorphMany
{
    return $this->morphMany(Attachment::class, 'attachable');
}
```

### 6.3 FormSchema Alterado: `EquipmentFormSchema.php`

- Campo `equipment_type_id` -> SelectInput, required, carregado de equipment_types
- Campo `license_plate` -> TextInput, required se category=vehicle
- Campo `internal_reference` -> TextInput, nullable
- Campo `manufacturing_year` -> NumberInput, min=1900, max=current_year
- Campo `inspection_date` -> DateInput, required se category=vehicle
- Campo `counting_type_id` -> SelectInput, carregado de counting_types
- Campo `revision_interval` -> NumberInput, substitui revision_interval_days

### 6.4 Controller Alterado: `EquipmentPageController.php`

- Adicionar `equipment_type_id` e `counting_type_id` ao eager loading
- Adicionar filtros por tipo de equipamento

### 6.5 Resource Alterado: `EquipmentResource.php`

- Expor `equipment_type`, `counting_type`, `attachments`

### 6.6 Nova Migracao

```php
// 2026_05_12_xxxxxx_create_equipment_types_table.php
// 2026_05_12_xxxxxx_create_counting_types_table.php
// 2026_05_12_xxxxxx_add_equipment_extended_fields.php
// 2026_05_12_xxxxxx_refactor_attachments_to_polymorphic.php
```

---

## 7. Seeders

### `EquipmentTypeSeeder.php`

```php
$types = [
    ['name' => 'Veiculo Pesado',  'category' => 'vehicle'],
    ['name' => 'Veiculo Leve',    'category' => 'vehicle'],
    ['name' => 'Palco',           'category' => 'general'],
    ['name' => 'Gerador',         'category' => 'general'],
    ['name' => 'Sistema de Som',  'category' => 'general'],
    ['name' => 'Iluminacao',      'category' => 'general'],
    ['name' => 'Ferramenta',      'category' => 'general'],
    ['name' => 'Outro',           'category' => 'general'],
];
```

### `CountingTypeSeeder.php`

```php
$types = [
    ['name' => 'Dias',          'value' => 'days'],
    ['name' => 'Meses',         'value' => 'months'],
    ['name' => 'Anos',          'value' => 'years'],
    ['name' => 'Quilometros',   'value' => 'km'],
    ['name' => 'Horas de uso',  'value' => 'hours'],
];
```

---

## 8. Traducoes

### `lang/pt_PT/forms.php`

```php
'equipments' => [
    'equipment_type'      => 'Tipo de Equipamento',
    'license_plate'       => 'Matricula',
    'internal_reference'  => 'Referencia Interna',
    'manufacturing_year'  => 'Ano de Fabrico',
    'inspection_date'     => 'Data de Inspecao',
    'counting_type'       => 'Tipo de Contagem',
    'revision_interval'   => 'Periodicidade de Revisao',
],
```

### `lang/en/forms.php`

```php
'equipments' => [
    'equipment_type'      => 'Equipment Type',
    'license_plate'       => 'License Plate',
    'internal_reference'  => 'Internal Reference',
    'manufacturing_year'  => 'Manufacturing Year',
    'inspection_date'     => 'Inspection Date',
    'counting_type'       => 'Counting Type',
    'revision_interval'   => 'Revision Interval',
],
```

---

## 9. Checklist de Implementacao

### Fase 1 — Base de Dados
- [ ] Criar migracao `create_equipment_types_table`
- [ ] Criar migracao `create_counting_types_table`
- [ ] Criar migracao `add_equipment_extended_fields` (ALTER TABLE equipments)
- [ ] Criar migracao `refactor_attachments_to_polymorphic`

### Fase 2 — Modelos
- [ ] Criar `EquipmentType.php` (Model)
- [ ] Criar `CountingType.php` (Model)
- [ ] Alterar `Equipment.php` (fillable, casts, relacoes)
- [ ] Alterar `Attachment.php` (morphTo em vez de belongsTo)

### Fase 3 — Regras/Formularios
- [ ] Alterar `EquipmentFormSchema.php` (create + update)
- [ ] Criar `StoreEquipmentTypeRequest.php`
- [ ] Criar `StoreCountingTypeRequest.php`
- [ ] Implementar validacao condicional no servico

### Fase 4 — APIs
- [ ] Alterar `EquipmentResource.php`
- [ ] Alterar `EquipmentPageController.php` (filtros)
- [ ] Adicionar rotas para equipment_types e counting_types (CRUD basico)

### Fase 5 — Seeders
- [ ] Criar `EquipmentTypeSeeder.php`
- [ ] Criar `CountingTypeSeeder.php`

### Fase 6 — Frontend (Inertia/React)
- [ ] Atualizar formulario de criacao de equipamento
- [ ] Atualizar formulario de edicao de equipamento
- [ ] Atualizar tabela de listagem (colunas novas)
- [ ] Adicionar gestao de tipos de equipamento (CRUD page)
- [ ] Adicionar gestao de tipos de contagem (CRUD page)

### Fase 7 — Traducoes
- [ ] Adicionar chaves PT_PT
- [ ] Adicionar chaves EN

---

## 10. Decisoes Arquiteturais

| Decisao | Opcao Escolhida | Alternativa |
|---------|-----------------|-------------|
| Tipo de equipamento | Tabela `equipment_types` (dinamica) | Enum fixo (rejeitado) |
| Categoria para validacao | Coluna `category` (vehicle/general) | Sem validacao (rejeitado) |
| Tipo de contagem | Tabela `counting_types` (dinamica) | Enum fixo (rejeitado) |
| Serial number | Nullable para veiculos | Required sempre (rejeitado) |
| Matricula | Unique (indice filtrado) | Nao unica (rejeitado) |
| Anexos | Polimorfico (attachable_type+id) | Colunas fixas (rejeitado) |
| Referencia interna | Unique (indice filtrado) | Nao unica (rejeitado) |
| Ano fabrico | Integer (4 digitos) | Data completa (rejeitado) |
| Inspecao | So para veiculos | Para todos (rejeitado) |

---

## 11. Riscos

| Risco | Impacto | Mitigacao |
|-------|---------|-----------|
| Quebrar attachments existentes na migracao | Alto | Script de migracao com backup dos dados |
| Validacao condicional complexa | Medio | Centralizar num metodo `EquipmentService::validate()` |
| Indices unicos com nulls | Medio | Usar partial unique index (WHERE col IS NOT NULL) |
| Performance com morphs | Baixo | Index em (attachable_type, attachable_id) |
