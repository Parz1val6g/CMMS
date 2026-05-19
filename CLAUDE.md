# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Environment

**The project runs inside Docker containers.** All commands must be prefixed with `docker exec` or run inside the container shell.

```bash
# Access the app container shell
docker exec -it project-app-1 sh

# Or run commands directly from host
docker exec project-app-1 php artisan migrate
```

**Services (defined in `docker-compose.yml`):**
| Service | Image | Port |
|---|---|---|
| `app` | Custom (PHP 8.2 + nginx/php-fpm) | 80 |
| `node` | node:22-alpine | 5173 (Vite HMR) |
| `mysql` | mysql:8.0 | 3307→3306 |
| `redis` | redis:7-alpine | 6379 |

Database credentials: `splnet_db` / `splnet` / `splnet` (root: `root`).

The `app` entrypoint (`docker/entrypoint.sh`) runs `php artisan migrate --force` and conditionally seeds the DB on every container start.

## Commands

```bash
# Build and start all containers
docker compose up -d --build

# Stop all containers (data persists via named volumes)
docker compose down

# Stop and delete volumes (full reset)
docker compose down -v

# Rebuild only the app container (e.g. after Dockerfile or entrypoint changes)
docker compose up -d --build app

# Full dev stack (inside app container)
docker exec project-app-1 composer dev

# Run migrations (inside app container)
docker exec project-app-1 php artisan migrate --force

# Run seeders (inside app container)
docker exec project-app-1 php artisan db:seed --force

# Full DB reset + seed (inside app container)
docker exec project-app-1 php artisan migrate:refresh --seed --force

# Tests (inside app container)
docker exec project-app-1 composer test

# Frontend build (inside node container)
docker exec project-node-1 npm run build

# Run a one-off command via tinker
docker exec project-app-1 php artisan tinker --execute="echo User::count();"
```

Tests run against an in-memory SQLite database (configured in `phpunit.xml`). The app database is MySQL via the `mysql` Docker container.

## Architecture

This is a **service order management system for municipal services** — citizens report issues, managers create service orders, sector heads assign tasks to teams, workers log progress.

**Stack:** Laravel 12 API + React 19 + Inertia.js + Tailwind CSS v4 + Sanctum (Bearer token auth) + Vite

### Feature-Based Directory Structure

Each domain feature is fully self-contained under `app/Features/{Feature}/` and `resources/js/Features/{Feature}/`:

```
app/Features/ServiceOrders/
  Controllers/   # Slim — delegates to Service
  Services/      # Business logic, owns transactions
  Models/
  Policies/
  Requests/      # Form validation
  Resources/     # JSON transformation
  Routes/
  Tests/
```

Cross-cutting infrastructure lives in:
- `app/Core/` — BasePolicy, TransactionHandler, PermissionManager, enums, traits, middleware
- `app/Shared/` — User, Role, Attachment, Location hierarchy (District → Municipality → Parish)

Routes are registered per-feature and pulled into `routes/api.php`.

### Request → Response Flow

```
Route → Controller (Gate::authorize) → Service (TransactionHandler) → Model → Resource
```

Controllers are kept under ~100 lines; services under ~200 lines.

### Domain Workflow

ServiceOrders cascade down:

```
ServiceOrder → Tasks (by sector) → MiniTasks (assigned to workers/teams) → WorkLogs
```

Completion propagates upward: all WorkLogs done → MiniTask done → Task done → ServiceOrder done. Equipment loans are modeled as a special ServiceOrder subtype (`workflow_type = equipment_loan`).

### Key Patterns

**Transactions** — All mutations use `TransactionHandler::execute(fn() => ...)`. Use `executeSilent()` when a null return on failure is acceptable.

**Enums** — Statuses are never raw strings. Enums in `app/Core/Enums/`:
- `ServiceOrderStatus`, `TaskStatus`, `MiniTaskStatus`, `WorkLogStatus`
- `UserRole`, `Priority`, `WorkflowType`, `PermissionAction`, `PermissionResource`
- Each enum has `label()`, `options()`, and domain-specific helpers like `weight()` or `isHighPriority()`.

**Authorization** — `BasePolicy` in `app/Core/Policies/` provides `isAdmin()`, `isOwner()`, `hasPermission()`, `hasRole()`, `isManagerScoped()`. Permissions are cached per-request in `PermissionManager` to avoid N+1.

**Models** — All use the `Base` trait (`app/Core/Traits/Base.php`) which adds UUID primary keys, SoftDeletes, and HasFactory.

**Frontend state** — Pinia stores in `resources/js/stores/` (authStore, clientStore, taskStore, uiStore, settingsStore). API calls go through composables (`useFetch`, `useForm`) and services in `resources/js/services/api/`.

**Frontend routing** — Inertia resolves pages dynamically from `resources/js/Features/**/*.jsx`. The `@` path alias points to `resources/js/`.

### Authentication

Sanctum stateless API tokens (Bearer). Public routes (login, password reset) are throttled (5/min login, 3/hour reset). All other routes require `auth:sanctum` middleware. Tests use `$this->actingAs($user, 'sanctum')`.

### File Storage

ServiceOrder photos are stored on the `public` disk (`Storage::disk('public')`), exposed via an appended `photo_url` attribute on the model.

## Knowledge Graph (RAG)

A pre-built knowledge graph of this codebase lives at `graphify-out/`. Use it as your primary lookup system before touching source files.

**Files:**
- `graphify-out/graph.json` — full graph (2,038 nodes, 2,091 edges, 331 communities)
- `graphify-out/GRAPH_REPORT.md` — audit report with god nodes, surprising connections, suggested questions
- `graphify-out/graph.html` — interactive browser visualization

**Rules:**
- Before reading source files, running grep/glob, or answering any codebase question, read `graphify-out/GRAPH_REPORT.md` first — it is your map.
- For "how does X relate to Y" questions use `/graphify path "X" "Y"` or `/graphify query "<question>"` to traverse the graph instead of grepping files.
- For "what is X" questions use `/graphify explain "X"` to get all edges and source locations for a concept.

**God nodes** (highest connectivity — touch these carefully, they affect many things):
1. `FormSchema` (45 edges) — `app/Core/Forms/FormSchema.php`
2. `FormField` (33 edges) — `app/Core/Forms/FormField.php`
3. `SelectInput` (30 edges) — `app/Core/Forms/Fields/SelectInput.php`
4. `TextInput` (29 edges) — `app/Core/Forms/Fields/TextInput.php`
5. `FormValidator` (29 edges) — `app/Core/Forms/`
6. `t()` (28 edges) — `resources/js/utils/i18n.js` (i18n, called across all frontend components)
7. `Equipment` (23 edges) — `app/Features/Equipments/Models/Equipment.php`

**Community map** (major clusters):
- `Domain Actors & Workflows` — UML use cases, actor roles, equipment loan flow
- `Admin & Cross-Cutting Concerns` — admin role, cascade completion, export, JWT auth
- `Architecture & Documentation` — arch docs, audit reports, loan tasks listener
- `FormField Core` / `FormSchema Builder` / `Form Field Components` — the form DSL system
- `Form Schema Definitions` — per-feature schemas (Client, Worker, Equipment, etc.)
- `Service Order Forms` / `MiniTask Management` / `Task Management` — domain CRUD layers
- `Frontend UI Shell` — CRUDPage, AppLayout, TopBar, Dashboard
- `Notification System` — events, listeners, notification model/resource
- `Equipment Model & State` — state machine, loan/return lifecycle
- `Material Management` / `Sector Management` / `Team Management` / `Location Management` / `Service Type Management` / `WorkLog Forms & Input`

**Keep graph current:** after modifying code run `/graphify . --update` (AST-only, no token cost).
