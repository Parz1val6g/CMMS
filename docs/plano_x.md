# Plano — Estimativas, Datas e Atribuição Equipa+Workers nas Mini-Tarefas

> **Base:** [`docs/plano_x.md`](docs/plano_x.md)
> **Arquitecto:** Grill-me session (2026-05-12)

---

## 1. Migração: Novas colunas na `mini_tasks`

Ficheiro: `database/migrations/2026_05_13_000000_add_estimates_dates_to_mini_tasks.php`

```php
Schema::table('mini_tasks', function (Blueprint $table) {
    $table->date('start_date')->nullable(false);  // obrigatório
    $table->date('end_date')->nullable(false);    // obrigatório
});
```

**Regras:**
- `start_date` e `end_date` são **obrigatórias** na criação, **editáveis** depois
- Validação: `start_date <= end_date`

---

## 2. Backend: Modelo [`MiniTask`](app/Features/MiniTasks/Models/MiniTask.php)

### `$fillable` — adicionar:
```php
'start_date',
'end_date',
```

### `$casts` — adicionar:
```php
'start_date' => 'date:Y-m-d',
'end_date'   => 'date:Y-m-d',
```

---

## 3. Backend: Form Schema [`MiniTaskFormSchema`](app/Features/MiniTasks/MiniTaskFormSchema.php)

### Criar (`create()`):
Adicionar campos:

| Campo | Tipo | Regras |
|-------|------|--------|
| `start_date` | `DateInput` | `required\|date\|after_or_equal:today` |
| `end_date` | `DateInput` | `required\|date\|after_or_equal:start_date` |
| `material_ids` | `SelectInput(multiple)` + `planned_quantities` | `nullable\|array` |
| `equipment_ids` | `SelectInput(multiple)` | `nullable\|array` |

### Editar (`update()`):
Mesmos campos, com regras `sometimes|date`.

### Providers auxiliares:
- `materialOptions()` — retorna `['value' => id, 'label' => nome]`
- `equipmentOptions()` — retorna `['value' => id, 'label' => nome + ref]`

---

## 4. Backend: Request [`StoreMiniTaskRequest`](app/Features/MiniTasks/Requests/StoreMiniTaskRequest.php)

Adicionar validações:
```php
$rules['start_date'] = ['required', 'date', 'after_or_equal:today'];
$rules['end_date']   = ['required', 'date', 'after_or_equal:start_date'];
$rules['material_ids']            = ['nullable', 'array'];
$rules['material_ids.*']          = ['exists:materials,id'];
$rules['planned_quantities']      = ['nullable', 'array'];
$rules['planned_quantities.*']    = ['required_with:material_ids', 'numeric', 'min:0.01'];
$rules['equipment_ids']           = ['nullable', 'array'];
$rules['equipment_ids.*']         = ['exists:equipments,id'];
```

---

## 5. Backend: Service [`MiniTaskService`](app/Features/MiniTasks/Services/MiniTaskService.php)

### `create()` — alterar:
```php
$miniTask = MiniTask::create([
    ...
    'start_date'   => $data['start_date'],
    'end_date'     => $data['end_date'],
    'status'       => MiniTaskStatus::PENDING->value,
]);

// Sync materiais com planned_quantity
if (!empty($data['material_ids'])) {
    $materialsSync = [];
    foreach ($data['material_ids'] as $i => $materialId) {
        $qty = $data['planned_quantities'][$i] ?? 1;
        $materialsSync[$materialId] = ['planned_quantity' => $qty];
    }
    $miniTask->materials()->sync($materialsSync);
}

// Sync equipamentos
$miniTask->equipment()->sync($data['equipment_ids'] ?? []);
```

---

## 6. Backend: Resource [`MiniTaskResource`](app/Features/MiniTasks/Resources/MiniTaskResource.php)

Adicionar à resposta:
```php
'start_date' => $this->start_date?->format('Y-m-d'),
'end_date'   => $this->end_date?->format('Y-m-d'),
'materials'  => MaterialResource::collection($this->whenLoaded('materials')),
'equipment'  => EquipmentResource::collection($this->whenLoaded('equipment')),
```

---

## 7. Frontend: Formulário de Criação (Modal)

**Ficheiro:** `resources/js/Features/MiniTasks/createFormSchema.js` (ou inline no PageController)

### Novos campos no schema:
```js
{ key: 'start_date', type: 'date', label: 'Data de Início', required: true },
{ key: 'end_date',   type: 'date', label: 'Data de Fim',    required: true },
{ key: 'material_ids', type: 'multiselect', label: 'Materiais', multiple: true, options: [...] },
{ key: 'planned_quantities', type: 'hidden' }, // gerido dinamicamente
{ key: 'equipment_ids', type: 'multiselect', label: 'Equipamentos', multiple: true, options: [...] },
```

### Comportamento Materiais (custom component):
- Select múltiplo de materiais
- Ao selecionar um material, aparece input de `planned_quantity` ao lado
- Enviar como arrays paralelos: `material_ids: [uuid1, uuid2]`, `planned_quantities: [5, 3]`

---

## 8. Frontend: Formulário de Edição (EditPanel)

O [`EditPanel`](resources/js/Components/DataManager/EditPanel.jsx:76) já renderiza dinamicamente os campos do `formSchema`.  
Basta adicionar os novos campos no schema retornado pelo [`MiniTaskPageController`](app/Features/MiniTasks/Controllers/Web/MiniTaskPageController.php).

**Importante:** Resolver valores iniciais para materiais/equipamentos:
- `material_ids` → extrair `item.materials[].id`
- `planned_quantities` → extrair `item.materials[].pivot.planned_quantity`
- `equipment_ids` → extrair `item.equipment[].id`

---

## 9. Frontend: Drawer de Visualização ([`MiniTaskDrawer`](resources/js/Features/MiniTasks/Components/MiniTaskDrawer.jsx))

### Separador "Geral" — adicionar:
```jsx
<Field label="Data de Início">{item.start_date}</Field>
<Field label="Data de Fim">{item.end_date}</Field>
```

### Separador "Equipa + Workers" — novo comportamento:

**Regra de UI:**
1. Select **Equipas** (multiselect) — livres
2. Select **Trabalhadores** (multiselect) — **mostra todos os workers**
   - Workers ligados a equipas selecionadas → **selecionados e bloqueados** (X removido / css `opacity-50 cursor-not-allowed`)
   - Workers sem equipa → selecionáveis livremente
   - Desselecionar equipa → workers dessa equipa somem/desbloqueiam

### Separador "Materiais" — já existe, apenas atualizar para mostrar os dados corretos

### Separador "Equipamentos" — criar novo ou adicionar ao separador Materiais

---

## 10. Modificação no [`MultiSelect`](resources/js/Components/Common/MultiSelect.jsx)

**Nova prop:** `lockedValues = []`

Comportamento:
- Valores em `lockedValues` aparecem selecionados e com X removido (bloqueado)
- `toggleItem` ignora lockedValues (não permite remover)
- Visual: `opacity-60 cursor-not-allowed bg-brand-mid/20`

---

## 11. Backend: Pivot — Remover CHECK constraint (opcional)

Na verdade, a CHECK constraint atual (`worker_id XOR team_id`) **não precisa de ser removida**.  
Cada linha continua a ter apenas um dos dois.  
A mini-tarefa pode ter **N linhas** no pivot:

```
mini_task_id | worker_id | team_id
MT-001      | null      | TEAM_A
MT-001      | WORKER_X  | null      ← worker avulso
```

A lógica de workers "implícitos" via equipa é **apenas visual/frontend**.  
No backend, o [`MiniTaskService`](app/Features/MiniTasks/Services/MiniTaskService.php) trata `worker_ids` e `team_ids` separadamente.

---

## 12. Ficheiros a alterar (lista final)

| # | Ficheiro | Ação |
|---|----------|------|
| 1 | `database/migrations/2026_05_13_000000_add_estimates_dates_to_mini_tasks.php` | **Criar** |
| 2 | `app/Features/MiniTasks/Models/MiniTask.php` | **Editar** — add fillable + casts |
| 3 | `app/Features/MiniTasks/MiniTaskFormSchema.php` | **Editar** — add campos |
| 4 | `app/Features/MiniTasks/Requests/StoreMiniTaskRequest.php` | **Editar** — add validações |
| 5 | `app/Features/MiniTasks/Services/MiniTaskService.php` | **Editar** — sync materiais/equipamentos |
| 6 | `app/Features/MiniTasks/Resources/MiniTaskResource.php` | **Editar** — add campos à resposta |
| 7 | `app/Features/MiniTasks/Controllers/Web/MiniTaskPageController.php` | **Editar** — passar equipamentos + materiais options |
| 8 | `app/Features/MiniTasks/Controllers/Api/MiniTaskController.php` | **Editar** — se necessário |
| 9 | `resources/js/Features/MiniTasks/Components/MiniTaskDrawer.jsx` | **Editar** — datas + equipa/workers locked |
| 10 | `resources/js/Components/Common/MultiSelect.jsx` | **Editar** — prop `lockedValues` |
| 11 | `resources/js/Features/MiniTasks/Pages/Index.jsx` | **Editar** — passar formSchema atualizado |

---

## 13. Ordem de implementação

```
1. Migration (nova tabela)
2. Model MiniTask (fillable + casts)
3. Backend: FormSchema
4. Backend: Request (validações)
5. Backend: Service (lógica create/update)
6. Backend: Resource (response)
7. Backend: PageController (options)
8. Frontend: MultiSelect (lockedValues)
9. Frontend: MiniTaskDrawer (UI)
10. Frontend: Index (formSchema)
```
