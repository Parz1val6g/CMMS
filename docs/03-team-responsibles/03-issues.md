# Issues — Team Responsible Feature

> Gerado a partir de: [`docs/03-team-responsibles/03-prd.md`](03-prd.md)
> Publicado em: [github.com/Parz1val6g/CMMS/issues](https://github.com/Parz1val6g/CMMS/issues)

---

## ISSUE-001: Foundation — Schema + Role + Model

🔗 [#1](https://github.com/Parz1val6g/CMMS/issues/1)

**Labels:** `backend`, `database`, `migration`, `role`
**Milestone:** M1
**Estimativa:** 1h
**Dependências:** Nenhuma

### Descrição

Criar a base de dados, role e modelo para suportar o responsável de equipa. Esta issue crie a infraestrutura necessária para as issues seguintes.

### Tasks

- [ ] **Migration:** Adicionar `responsible_id` (UUID, FK→`users`, NOT NULL, `cascadeOnDelete`) à tabela `teams`
- [ ] **Model [`Team`](../../app/Features/Teams/Models/Team.php):** Adicionar relação `responsible()` (belongsTo User)
- [ ] **RoleSeeder:** Adicionar `'team_manager'` à lista de roles
- [ ] **UserSeeder:** Adicionar user de teste `'team_manager'` (nome: "Rita, Responsável")
- [ ] **RolePermissionSeeder:** Adicionar permissões `view` + `update` sobre `TEAMS` para role `team_manager`
- [ ] **BasePolicy:** Adicionar helper `isTeamManager()` (mesmo padrão de `isSectorManager()`)

### Critérios de Aceitação

- [ ] `php artisan migrate` executa sem erros
- [ ] `php artisan migrate:rollback` reverte a migration
- [ ] `php artisan db:seed` cria role `team_manager` + user de teste + permissions
- [ ] `Team::with('responsible')->first()` carrega relação
- [ ] `BasePolicy::isTeamManager()` retorna true para user com role `team_manager`

---

## ISSUE-002: Create Team with Responsible — Full CRUD Path

🔗 [#2](https://github.com/Parz1val6g/CMMS/issues/2)

**Labels:** `backend`, `api`, `form`, `service`, `frontend`
**Milestone:** M2
**Estimativa:** 2h
**Dependências:** ISSUE-001

### Descrição

Implementar o fluxo completo de criação de equipa com responsável, desde o formulário até à base de dados, passando pela lógica de negócio que cria automaticamente o Worker record e atribui roles.

### Tasks

- [ ] **TeamFormSchema:** Adicionar campo `SelectInput::make('responsible_id')`
  - Label: "Responsável"
  - Required
  - Options: Users com role `worker` que **não são Worker de outra equipa**
- [ ] **TeamService::create():**
  1. Validar que User não é Worker de outra equipa
  2. Criar Team com `responsible_id`
  3. Atribuir role `team_manager` ao User (se não tiver)
  4. Atribuir role `worker` ao User (se não tiver)
  5. Criar Worker record (`user_id` + `team_id`)
- [ ] **TeamResource:** Incluir `responsible` quando carregado:
  ```php
  'responsible' => $this->whenLoaded('responsible', fn() => [
      'id' => $this->responsible->id,
      'name' => $this->responsible->first_name . ' ' . $this->responsible->last_name,
  ]),
  ```
- [ ] **TeamController (API):** Adicionar eager load `responsible` no show/index
- [ ] **TeamPageController:** Adicionar coluna "Responsável" + eager load `responsible`

### Critérios de Aceitação

- [ ] `POST /api/teams` com `responsible_id` válido → 201, Worker criado, roles atribuídas
- [ ] `POST /api/teams` sem `responsible_id` → 422
- [ ] `POST /api/teams` com User já Worker de outra equipa → 422
- [ ] `GET /api/teams` devolve `responsible {id, name}` no JSON
- [ ] Página web de equipas mostra coluna "Responsável"
- [ ] Formulário de criação tem campo "Responsável" com SearchableSelect

---

## ISSUE-003: Update Team + Policy Scoping

🔗 [#3](https://github.com/Parz1val6g/CMMS/issues/3)

**Labels:** `backend`, `policy`, `authorization`, `seeder`
**Milestone:** M3
**Estimativa:** 1.5h
**Dependências:** ISSUE-002

### Descrição

Implementar a edição de equipas com a lógica de mudança de responsável, as políticas de autorização scoped para `team_manager`, e atualizar os seeders para preencher `responsible_id` nas equipas existentes.

### Tasks

- [ ] **TeamService::update():**
  - Se `responsible_id` mudou:
    - Novo User já é Worker desta equipa → só atualiza `responsible_id`
    - Não é Worker → criar Worker record + roles `worker`/`team_manager`
    - Responsável anterior: só limpa `responsible_id`, Worker mantém-se
- [ ] **TeamPolicy:** Adicionar scoped check para `team_manager`:
  ```php
  if ($this->isTeamManager($user)) {
      return $team->responsible_id === $user->id;
  }
  ```
- [ ] **TeamSeeder:** Atribuir `responsible_id` às equipas existentes
- [ ] **WorkerSeeder:** Garantir que user `team_manager` tem roles `worker` + `team_manager`

### Critérios de Aceitação

- [ ] `PUT /api/teams/{id}` com novo `responsible_id` → 200, Worker criado
- [ ] `team_manager` pode editar a sua própria equipa → 200
- [ ] `team_manager` não pode editar outra equipa → 403
- [ ] `sector_manager` pode editar equipas do seu setor (já existente, não quebrar)
- [ ] `worker` não pode editar equipas → 403
- [ ] `php artisan db:seed` preenche `responsible_id` nas equipas existentes

---

## ISSUE-004: Tests — Integração

🔗 [#4](https://github.com/Parz1val6g/CMMS/issues/4)

**Labels:** `testing`
**Milestone:** M4
**Estimativa:** 1.5h
**Dependências:** ISSUE-002, ISSUE-003

### Descrição

Implementar testes de integração para todo o fluxo de responsável de equipa, cobrindo API, políticas e lógica de serviço.

### Tasks

- [ ] Criar [`tests/Feature/Api/TeamApiTest.php`](../../tests/Feature/Api/TeamApiTest.php):
  - Unauthenticated → 401
  - Criar team sem `responsible_id` → 422
  - Criar team com dados válidos → 201 + Worker criado
  - Criar team com User já Worker de outra equipa → 422
  - Atualizar `responsible_id` → cria Worker para novo user
  - Listar teams → inclui campo `responsible`
  - Non-admin não pode apagar teams
  - Prior art: [`ServiceOrderApiTest`](../../tests/Feature/Api/ServiceOrderApiTest.php)
- [ ] Criar [`tests/Feature/Authorization/TeamPoliciesTest.php`](../../tests/Feature/Authorization/TeamPoliciesTest.php):
  - Admin pode ver/editar/apagar qualquer team
  - `team_manager` pode ver e editar própria equipa
  - `team_manager` não pode editar outra equipa
  - `sector_manager` pode ver/editar equipas do seu setor
  - `worker` não pode editar equipas
  - Prior art: [`ServiceOrderPoliciesTest`](../../tests/Feature/Authorization/ServiceOrderPoliciesTest.php)
- [ ] Criar [`tests/Feature/Teams/TeamServiceTest.php`](../../tests/Feature/Teams/TeamServiceTest.php):
  - Criar team com `responsible_id` cria Worker
  - Criar team atribui roles `worker` + `team_manager`
  - Criar team com User já Worker noutra equipa → ValidationException
  - Atualizar `responsible_id` cria Worker para novo user
  - Remover `responsible_id` limpa-o mas Worker mantém-se
  - Prior art: [`CascadeCompletionTest`](../../tests/Feature/Cascade/CascadeCompletionTest.php)

### Critérios de Aceitação

- [ ] `php artisan test --filter=TeamApiTest` — todos passam
- [ ] `php artisan test --filter=TeamPoliciesTest` — todos passam
- [ ] `php artisan test --filter=TeamServiceTest` — todos passam
- [ ] `php artisan test` — nenhum teste existente quebrado
