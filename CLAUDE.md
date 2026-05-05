# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Full dev stack (Laravel server + queue + logs + Vite HMR — all concurrently)
composer dev

# First-time setup (install deps, .env, app key, migrate, npm install, build)
composer setup

# Tests (clears config cache first, then PHPUnit)
composer test

# Individual commands
php artisan serve
php artisan migrate
php artisan db:seed
npm run dev
npm run build
```

Tests run against an in-memory SQLite database (configured in `phpunit.xml`). The default app database is MySQL via `.env`.

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
