# Implementação: Web Interface Access & Role Scoping

## Contexto
Projeto Laravel com autenticação Inertia/React. Atualmente as rotas web têm apenas middleware `auth`. Workers e clients conseguem aceder ao dashboard via browser, o que não é desejado. O sistema de roles está na BD (tabela `roles`) mas o enum `UserRole` está morto (0 referências). As policies usam `BasePolicy::before()` com fast-path hardcoded para admin.

## Objetivos

### 1. Criar `RoleName` — classe de constantes + resolvedor UUID
- Ficheiro: `app/Core/Enums/RoleName.php`
- Constantes para todos os roles existentes: ADMIN, MANAGER, EQUIPMENT_MANAGER, SUPERVISOR, WORKER, CLIENT, TASK_MANAGER, MINI_TASK_MANAGER, WORK_LOG_MANAGER, SECTOR_MANAGER
- Cada constante é o nome em string (ex.: `ADMIN = 'admin'`)
- Método estático `RoleName::id(string $name): ?string` — busca UUID na tabela `roles` com cache por-request (propriedade estática, não Cache do Laravel — apenas memória do request)
- Método estático `RoleName::all(): \Illuminate\Support\Collection` — devolve todos os roles como coleção de modelos, cache por-request
- Método estático `RoleName::exists(string $name): bool` — verifica se o nome existe na BD (cache por-request)
- Namespace: `App\Core\Enums`

### 2. Remover `UserRole` enum (opcional, dead code)
- Ficheiro: `app/Core/Enums/UserRole.php`
- Apagar o ficheiro ou marcar como deprecated

### 3. Remover hardcoded admin bypass em `BasePolicy`
- Ficheiro: `app/Core/Policies/BasePolicy.php`
- `before()`: substituir `if ($this->isAdmin($user)) { return true; }` por chamada a `hasPermission()` — mas `before()` só recebe `$ability`, não o resource.
- **Solução:** Mapear `$ability` para `PermissionAction` equivalente onde possível (ex.: `viewAny` → `view`). Para abilities que não dê para mapear, retornar `null` (deixa a policy method decidir).

Como `before()` não sabe qual o resource, a abordagem mais limpa é:
```php
public function before(User $user, string $ability): ?bool
{
    // Remover o return true hardcoded — o admin é verificado como qualquer role
    return null; // as policy methods (que sabem o resource) tratam da autorização
}
```
Isto é seguro porque:
- O seeder já dá ao admin **todos os resources × todas as actions**
- Se alguém remover uma permissão na BD, a policy method correspondente (que chama `hasPermission()`) vai negar
- O admin perde acesso apenas ao que for removido da BD

- **Manter** `isAdmin()` e `hasPermission()` — continuam a ser usados pelas policy methods
- `isAdmin()` mantém cache por-request (`$permCache`)

### 4. Criar `WebAccessMiddleware`
- Ficheiro: `app/Http/Middleware/WebAccessMiddleware.php`
- Lógica:
  ```php
  public function handle(Request $request, Closure $next): mixed
  {
      $user = $request->user();
      
      if (!$user) {
          return $next($request);
      }
      
      // Roles bloqueadas: worker, client
      $blockedRoles = ['worker', 'client'];
      
      // Verifica se o user tem ALGUMA role que não está na lista de bloqueadas
      $hasWebAccess = $user->roles()
          ->whereNotIn('name', $blockedRoles)
          ->exists();
      
      if (!$hasWebAccess) {
          abort(403, 'Esta conta é para a aplicação móvel e não tem acesso à interface web.');
      }
      
      return $next($request);
  }
  ```
- Registar em `bootstrap/app.php`:
  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->alias([
          'web.access' => \App\Http\Middleware\WebAccessMiddleware::class,
      ]);
  })
  ```
- Aplicar no grupo `auth` em `routes/web.php`:
  ```php
  Route::middleware(['auth', 'web.access'])->group(function () {
      // ... todas as rotas web atuais
  });
  ```

### 5. Scope para sector_manager e supervisor

#### 5.1. Criar `HasSectorScope` trait
- Ficheiro: `app/Core/Traits/HasSectorScope.php`
- Método `scopeForSectorManager(Builder $query, User $user): Builder`
- Aplica `$query->whereIn('sector_id', Sector::where('head_id', $user->id)->pluck('id'))`
- Para modelos que têm `sector_id` diretamente (Sector, Team, Task)

#### 5.2. Criar `HasTeamScope` trait  
- Ficheiro: `app/Core/Traits/HasTeamScope.php`
- Método `scopeForSupervisor(Builder $query, User $user): Builder`
- Aplica `$query->whereIn('team_id', Team::where('supervisor_id', $user->id)->pluck('id'))`
- Para modelos que têm `team_id` (Worker, MiniTask via equipa)

#### 5.3. Query Scopes nos Controllers Web
Nos controllers web (páginas Inertia), adicionar filtro de scope nas queries `viewAny`:

- `SectorPageController::index()`: adicionar `->when(!$user->isAdmin() && $user->roles()->where('name', 'sector_manager')->exists(), fn($q) => $q->where('head_id', $user->id))`
- `TeamPageController::index()`: adicionar scope `whereIn('sector_id', ...)` para sector_manager; `where('supervisor_id', $user->id)` para supervisor
- `WorkerPageController::index()`: adicionar scope por equipas do supervisor / sectores do sector_manager
- `TaskPageController::index()`: adicionar scope por sector para sector_manager
- `MiniTaskPageController::index()`: adicionar scope por supervisor ou por equipa do supervisor
- `ServiceOrderPageController::index()`: adicionar scope por sector para sector_manager (via tabela pivot `service_order_sectors`)
- `WorkLogPageController::index()`: adicionar scope via mini-tasks do supervisor

#### 5.4. Policies com scope (recursos individuais)
- `SectorPolicy::view()`: adicionar verificação `$sector->head_id === $user->id` para sector_manager
- `TeamPolicy::view()`: adicionar `$team->supervisor_id === $user->id` para supervisor OU `$team->sector->head_id === $user->id` para sector_manager
- `WorkerPolicy::view()`: adicionar scope via equipa do supervisor/sector do sector_manager
- Manter `hasPermission()` como guarda primária

**Nota:** As policies `view()` devem primeiro verificar `hasPermission()` (RBAC global) e depois o scope específico da role. Se o user for manager (que tem permissão global), não precisa de scope — só sector_manager e supervisor precisam.

#### 5.5. Otimização: Extrair lógica de scope para `BasePolicy`
Adicionar métodos auxiliares em `BasePolicy`:
- `isSectorManager(User $user): bool` — verifica se tem role 'sector_manager'
- `isSupervisor(User $user): bool` — verifica se tem role 'supervisor'

## Ficheiros a modificar

| Ficheiro | Ação |
|----------|------|
| `app/Core/Enums/RoleName.php` | **CRIAR** — classe de constantes + resolvedor |
| `app/Core/Enums/UserRole.php` | **APAGAR** (opcional, dead code) |
| `app/Core/Policies/BasePolicy.php` | **MODIFICAR** — before() retorna null, adicionar isSectorManager()/isSupervisor() |
| `app/Http/Middleware/WebAccessMiddleware.php` | **CRIAR** — middleware de bloqueio |
| `bootstrap/app.php` | **MODIFICAR** — registar alias 'web.access' |
| `routes/web.php` | **MODIFICAR** — adicionar 'web.access' ao grupo auth |
| `app/Core/Traits/HasSectorScope.php` | **CRIAR** — trait de scope para sector_manager |
| `app/Core/Traits/HasTeamScope.php` | **CRIAR** — trait de scope para supervisor |
| `app/Features/Sectors/Policies/SectorPolicy.php` | **MODIFICAR** — adicionar scope view() |
| `app/Features/Teams/Policies/TeamPolicy.php` | **MODIFICAR** — adicionar scope view() |
| `app/Features/Workers/Policies/WorkerPolicy.php` | **MODIFICAR** — adicionar scope view() |
| `app/Features/Sectors/Controllers/Web/SectorPageController.php` | **MODIFICAR** — query scope em index() |
| `app/Features/Teams/Controllers/Web/TeamPageController.php` | **MODIFICAR** — query scope em index() |
| `app/Features/Workers/Controllers/Web/WorkerPageController.php` | **MODIFICAR** — query scope em index() |
| `app/Features/Tasks/Controllers/Web/TaskPageController.php` | **MODIFICAR** — query scope em index() |
| `app/Features/MiniTasks/Controllers/Web/MiniTaskPageController.php` | **MODIFICAR** — query scope em index() |
| `app/Features/ServiceOrders/Controllers/Web/ServiceOrderPageController.php` | **MODIFICAR** — query scope em index() |
| `app/Features/WorkLogs/Controllers/Web/WorkLogPageController.php` | **MODIFICAR** — query scope em index() |

## Ordem de implementação
1. `RoleName` class + apagar `UserRole` (se aplicável)
2. `BasePolicy` — remover fast-path, adicionar helpers
3. `WebAccessMiddleware` + registo + aplicar nas rotas
4. Traits de scope (`HasSectorScope`, `HasTeamScope`)
5. Atualizar `*Policy::view()` com scope
6. Atualizar `*PageController::index()` com query scopes

## Notas
- As rotas API (`routes/api.php`) **não** devem ter `web.access` — a API pode ser usada pela app móvel (worker) e pelo web.
- O scope nas policies é **defense in depth** — o query scope no controller já filtra, mas a policy garante que um acesso direto a `/sectors/{id}` também é bloqueado.
- O seeder já tem `sector_manager` e `task_manager` etc. — não precisas de alterar seeders.
