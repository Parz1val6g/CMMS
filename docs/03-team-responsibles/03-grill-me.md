# Grill Me — Adicionar Responsável por Equipa

**Data:** 2026-05-12
**Contexto:** Roadmap item #3 — "adicionar um responsável por cada equipa"
**Stack:** Laravel 12 + React 19 + Inertia

---

## Decisões Tomadas

### 1. 🗃️ Migration — `teams` table

| Campo | Tipo | Constraints |
|-------|------|-------------|
| `responsible_id` | UUID (FK → `users`) | **NOT NULL**, `cascadeOnDelete` |

```php
$table->foreignUuid('responsible_id')->constrained('users')->cascadeOnDelete();
```

O campo é **obrigatório** — toda equipa precisa de um responsável desde a criação.

### 2. 🏷️ Role — `team_manager`

- Nome mantém-se `team_manager` (consistente com `sector_manager`, `equipment_manager`)
- Adicionar à lista no [`RoleSeeder`](../../database/seeders/RoleSeeder.php)
- Adicionar user de teste no [`UserSeeder`](../../database/seeders/UserSeeder.php) (ex: "Rita, Responsável")

### 3. 🔐 Permissions

| Role | Resource | Actions | Scope |
|------|----------|---------|-------|
| `team_manager` | `TEAMS` | `view`, `update` | Apenas a equipa onde é responsável |
| `team_manager` | `WORKERS` | `view`, `update` | Apenas workers da sua equipa |

- **BasePolicy:** adicionar helper `isTeamManager()` (mesmo padrão de [`isSectorManager()`](../../app/Core/Policies/BasePolicy.php))
- **TeamPolicy:** `update()` verifica `$team->responsible_id === $user->id` se for `team_manager`

### 4. 👤 User elegível para responsável

- Deve ter roles: **`worker`** + **`team_manager`**
- Deve ser **Worker da mesma equipa** (Worker record criado automaticamente)
- `Worker.user_id` é **UNIQUE** → um User só pode ser Worker de 1 equipa
- Se o User já é Worker de outra equipa → rejeitar (`ValidationException`)

### 5. 📝 TeamService — create / update flow

**Create** (com `responsible_id`):
1. Validar que User não é Worker de outra equipa
2. Criar Team com `responsible_id`
3. Atribuir role `team_manager` ao User (se não tiver)
4. Atribuir role `worker` ao User (se não tiver)
5. Criar Worker record (`user_id` + `team_id`)

**Update** (ao mudar `responsible_id`):
1. Se novo User já é Worker desta equipa → só atualiza `responsible_id`
2. Se não é Worker → criar Worker record + roles necessárias
3. Responsável anterior: só limpa `responsible_id`, Worker record **mantém-se**

**Remove responsável** (Opção A): só limpa `responsible_id`, Worker record mantém-se.

### 6. 📋 FormSchema — [`TeamFormSchema`](../../app/Features/Teams/TeamFormSchema.php)

- Novo campo: `SelectInput::make('responsible_id')`
  - **Label:** "Responsável"
  - **Required**
  - **Options:** Users com role `worker` que **não são Worker de outra equipa**
  - **UI:** [`SearchableSelect`](../../resources/js/Components/Common/SearchableSelect.jsx)

### 7. 📡 API — [`TeamResource`](../../app/Features/Teams/Resources/TeamResource.php)

```php
'responsible' => $this->whenLoaded('responsible', fn() => [
    'id' => $this->responsible->id,
    'name' => $this->responsible->first_name . ' ' . $this->responsible->last_name,
]),
```

### 8. 🖥️ UI — [`TeamPageController`](../../app/Features/Teams/Controllers/Web/TeamPageController.php)

- Adicionar coluna `'responsible'` às `columns` do index
- Eager load: `Team::with(['sector', 'responsible'])`

### 9. 🌱 Seeders — dados de teste

| Seeder | Alteração |
|--------|-----------|
| [`RoleSeeder`](../../database/seeders/RoleSeeder.php) | Adicionar `'team_manager'` à lista |
| [`UserSeeder`](../../database/seeders/UserSeeder.php) | Adicionar user `'team_manager'` |
| [`RolePermissionSeeder`](../../database/seeders/RolePermissionSeeder.php) | Adicionar `view` + `update` para `team_manager` em `TEAMS` |
| [`TeamSeeder`](../../database/seeders/TeamSeeder.php) | Atribuir `responsible_id` à primeira equipa |
| [`WorkerSeeder`](../../database/seeders/WorkerSeeder.php) | User `team_manager` recebe roles `worker` + `team_manager` |

---

### 📐 Modelo Relacional Final

```
Sector
├── head_id (User)            ← responsável pelo setor
└── teams → Team
             ├── responsible_id (User)    ← NOVO
             ├── workers → Worker
             │               └── user_id (User, UNIQUE)
             └── sector_id (Sector)
```

---

### 📁 Ficheiros a Alterar

| Ficheiro | Alteração |
|----------|-----------|
| `database/migrations/XXXX_create_teams_table.php` | Nova migration para adicionar `responsible_id` |
| `app/Features/Teams/Models/Team.php` | Adicionar relação `responsible()` |
| `app/Features/Teams/TeamFormSchema.php` | Adicionar campo `responsible_id` |
| `app/Features/Teams/Services/TeamService.php` | Lógica de criar Worker + atribuir roles |
| `app/Features/Teams/Policies/TeamPolicy.php` | Scoped check para `team_manager` |
| `app/Features/Teams/Resources/TeamResource.php` | Incluir `responsible` |
| `app/Features/Teams/Controllers/Api/TeamController.php` | Se necessário, eager load |
| `app/Features/Teams/Controllers/Web/TeamPageController.php` | Coluna + eager load |
| `app/Core/Policies/BasePolicy.php` | Adicionar `isTeamManager()` |
| `database/seeders/RoleSeeder.php` | Adicionar `'team_manager'` |
| `database/seeders/UserSeeder.php` | Adicionar user `'team_manager'` |
| `database/seeders/RolePermissionSeeder.php` | Permissions para `team_manager` |
| `database/seeders/TeamSeeder.php` | Atribuir `responsible_id` |
| `database/seeders/WorkerSeeder.php` | Atribuir roles ao team_manager |
