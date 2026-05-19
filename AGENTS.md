# AGENTS.md

OpenCode agent guidance for this repository. See [CLAUDE.md](CLAUDE.md) for full architecture docs.

## Environment

This is a Laravel 12 API + React 19 + Inertia.js + Tailwind CSS v4 app. No TypeScript — all frontend is JS/JSX.

**There is no `docker-compose.yml`.** The CLAUDE.md Docker instructions are stale. Commands run directly on the host. The `.env` points to `DB_HOST=127.0.0.1:3306` (local MySQL).

## Commands

```bash
# PHP tests (SQLite in-memory, configured in phpunit.xml)
composer test                    # = php artisan config:clear && php artisan test

# Focused PHP test
php artisan test --filter=MyTest
docker exec project-app-1 php artisan test --filter=MyTest

# JS tests (Vitest + jsdom, globals enabled)
npx vitest                       # all tests
npx vitest --run                 # single run (no watch)

# JS lint
npm run lint                     # eslint resources/js

# Run React dev server (Vite HMR on port 5173)
npm run dev

# PHP dev server (if not using Docker)
php artisan serve

# DB migrations
php artisan migrate --force
php artisan migrate:refresh --seed --force
```

## Architecture

### Feature-based structure

```
app/Features/{Feature}/          resources/js/Features/{Feature}/
  Controllers/                     Pages/
  Services/                        Components/
  Models/                          composables/
  Policies/
  Requests/
  Resources/
  Routes/
  Tests/
```

Cross-cutting: `app/Core/` (BasePolicy, TransactionHandler, PermissionManager, Enums, Traits, Middleware, Forms DSL).
Shared models: `app/Shared/` (User, Role, Attachment, Location hierarchy).

Routes register per-feature via `routes/api.php`.

### Key patterns (easy to miss)

- **All DB mutations** go through `TransactionHandler::execute(fn() => ...)`. Use `executeSilent()` when null-on-failure is acceptable.
- **Statuses are enums**, never raw strings. Found in `app/Core/Enums/`. Each has `label()`, `options()`, and domain helpers.
- **All models use the `Base` trait** (`app/Core/Traits/Base.php`) — UUID primary keys, SoftDeletes, HasFactory.
- **Auth**: Sanctum stateless Bearer tokens. Tests use `$this->actingAs($user, 'sanctum')`.
- **Authorization**: `BasePolicy` with `isAdmin()`, `isOwner()`, `hasPermission()`, `hasRole()`, `isManagerScoped()`. Permission cache per-request via `PermissionManager`.
- **Completion cascades**: WorkLogs done → MiniTask done → Task done → ServiceOrder done.
- **Forms**: DSL system at `app/Core/Forms/` — god nodes (FormSchema 45 edges, FormField 33 edges). Touch carefully.

### Frontend specifics

- `@` alias → `resources/js/`
- Inertia resolves pages from `resources/js/Features/**/*.jsx`
- Pinia stores: `resources/js/stores/` (authStore, clientStore, taskStore, uiStore, settingsStore)
- API layer: composables (`useFetch`, `useForm`) and services in `resources/js/services/api/`
- i18n: `t()` from `resources/js/utils/i18n.js`
- ESLint: `no-console` is `warn` (allow `console.warn`/`console.error` only)

## Knowledge Graph

`graphify-out/` — read `GRAPH_REPORT.md` before exploring source. Run `/graphify . --update` after significant modifications.
