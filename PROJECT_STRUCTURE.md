# Estrutura do Projeto

## Raiz

```
app/
├── Features/
├── Core/
├── Shared/
├── Console/
├── Exceptions/
├── Http/
│   ├── Middleware/
│   └── Requests/
├── Mail/
├── Observers/
├── Providers/
└── Traits/

bootstrap/
config/
database/
├── factories/
├── migrations/
└── seeders/

public/
resources/
├── css/
├── js/
│   └── Features/
├── lang/
└── views/

routes/
├── api.php
├── console.php
├── web.php
└── api/
    └── Feature routes

storage/
tests/
├── Feature/
└── Unit/
```

---

## app/Features/

Cada feature contém tudo o que precisa. Não há dispersão por layers.

```
Features/
├── Authentication/
│   ├── Controllers/
│   │   └── AuthController.php
│   ├── Services/
│   │   └── AuthService.php
│   ├── Requests/
│   │   ├── LoginRequest.php
│   │   ├── RegisterRequest.php
│   │   ├── ChangePasswordRequest.php
│   │   └── VerifyEmailRequest.php
│   ├── Routes/
│   │   └── routes.php
│   ├── Models/
│   └── Factories/
│       └── AuthFactory.php
│
├── Clients/
│   ├── Controllers/
│   │   └── ClientController.php
│   ├── Services/
│   │   └── ClientService.php
│   ├── Models/
│   │   └── Client.php
│   ├── Policies/
│   │   └── ClientPolicy.php
│   ├── Requests/
│   │   ├── StoreClientRequest.php
│   │   └── UpdateClientRequest.php
│   ├── Resources/
│   │   └── ClientResource.php
│   ├── Routes/
│   │   └── routes.php
│   ├── Factories/
│   │   └── ClientFactory.php
│   └── Tests/
│       ├── Feature/
│       └── Unit/
│
├── ServiceOrders/
│   ├── Controllers/
│   ├── Services/
│   ├── Models/
│   ├── Policies/
│   ├── Requests/
│   ├── Resources/
│   ├── Routes/
│   ├── Factories/
│   └── Tests/
│
├── Tasks/
│   ├── Controllers/
│   ├── Services/
│   ├── Models/
│   ├── Policies/
│   ├── Requests/
│   ├── Resources/
│   ├── Routes/
│   ├── Factories/
│   └── Tests/
│
├── MiniTasks/
│   ├── Controllers/
│   ├── Services/
│   ├── Models/
│   ├── Policies/
│   ├── Requests/
│   ├── Resources/
│   ├── Routes/
│   ├── Factories/
│   └── Tests/
│
├── WorkLogs/
│   ├── Controllers/
│   ├── Services/
│   ├── Models/
│   ├── Policies/
│   ├── Requests/
│   ├── Resources/
│   ├── Routes/
│   ├── Factories/
│   └── Tests/
│
├── Sectors/
│   ├── Controllers/
│   ├── Services/
│   ├── Models/
│   ├── Policies/
│   ├── Requests/
│   ├── Resources/
│   ├── Routes/
│   ├── Factories/
│   └── Tests/
│
├── Teams/
│   ├── Controllers/
│   ├── Services/
│   ├── Models/
│   ├── Policies/
│   ├── Requests/
│   ├── Resources/
│   ├── Routes/
│   ├── Factories/
│   └── Tests/
│
├── Workers/
│   ├── Controllers/
│   ├── Services/
│   ├── Models/
│   ├── Policies/
│   ├── Requests/
│   ├── Resources/
│   ├── Routes/
│   ├── Factories/
│   └── Tests/
│
├── Materials/
│   ├── Controllers/
│   ├── Services/
│   ├── Models/
│   ├── Policies/
│   ├── Requests/
│   ├── Resources/
│   ├── Routes/
│   ├── Factories/
│   └── Tests/
│
├── Locations/
│   ├── Controllers/
│   ├── Services/
│   ├── Models/
│   ├── Policies/
│   ├── Requests/
│   ├── Resources/
│   ├── Routes/
│   ├── Factories/
│   └── Tests/
│
├── ServiceTypes/
│   └── [Same structure]
│
├── Admin/
│   ├── Controllers/
│   │   ├── UserController.php
│   │   ├── RoleController.php
│   │   └── PermissionController.php
│   ├── Services/
│   ├── Policies/
│   ├── Requests/
│   ├── Resources/
│   ├── Routes/
│   └── Tests/
│
├── Export/
│   ├── Controllers/
│   ├── Services/
│   ├── Requests/
│   ├── Routes/
│   └── Tests/
│
├── Settings/
│   ├── Controllers/
│   ├── Services/
│   ├── Requests/
│   ├── Routes/
│   └── Tests/
│
└── Notifications/
    ├── Services/
    └── Mail/
```

---

## app/Core/

Infraestrutura compartilhada. Base para tudo.

```
Core/
├── Services/
│   ├── CacheManager.php          → Invalidação pattern-based
│   ├── TransactionHandler.php    → Wraps mutations
│   ├── PermissionManager.php     → Permission checks
│   ├── FilterService.php         → Unified filtering
│   └── LocationHierarchyService.php → Geographic queries
│
├── Traits/
│   ├── Base.php                  → UUID + soft delete
│   ├── Filterable.php            → Search/filter scope
│   ├── ExportCsv.php             → CSV export capability
│   ├── Timestamped.php           → Created/updated at
│   ├── Publishing.php            → Published/draft status
│   └── Completable.php           → Progress tracking
│
├── Enums/
│   ├── UserRole.php              → admin, manager, pending
│   ├── PermissionAction.php      → view, create, update, delete, etc
│   ├── PermissionResource.php    → users, clients, tasks, etc
│   ├── SystemStatus.php          → active, inactive, suspended
│   ├── TaskStatus.php            → pending, in_progress, completed, blocked, cancelled
│   ├── MiniTaskStatus.php        → pending, in_progress, completed, blocked, cancelled
│   ├── WorkLogStatus.php         → draft, submitted, approved, rejected
│   └── ServicesOrdersPriority.php → urgent, high, normal, low
│
├── Helpers/
│   ├── InputSanitizer.php        → Validation + cleaning
│   ├── FeatureFlags.php          → Feature toggles
│   ├── FormattingHelper.php      → Date/time/currency formatting
│   └── ValidationHelper.php      → Common validations
│
├── Policies/
│   ├── BasePolicy.php            → isAdmin, isOwner, hasPermission
│   └── [Individual policies inherit]
│
└── Middleware/
    ├── AuthenticateApi.php       → Sanctum token verification
    ├── EnsureEmailVerified.php   → Email check
    ├── CheckSoftDeletedUser.php  → Prevent login if soft-deleted
    ├── SetUserLocale.php         → i18n from user preference
    └── RateLimiter.php           → Rate limiting per endpoint
```

---

## app/Shared/

Modelos e serviços verdadeiramente compartilhados.

```
Shared/
├── Models/
│   ├── User.php
│   ├── Role.php
│   ├── UserRole.php
│   ├── RolePermission.php
│   ├── UserPreference.php
│   ├── AppSetting.php
│   ├── District.php
│   ├── Municipality.php
│   ├── Parish.php
│   └── Attachment.php
│
└── Services/
    ├── UserService.php           → User CRUD + role management
    ├── RoleService.php           → Role CRUD + permission seeding
    └── LocationHierarchyService.php → Geographic queries
```

---

## database/

Migrations + seeders + factories.

```
database/
├── factories/
│   ├── UserFactory.php
│   ├── ClientFactory.php
│   ├── SectorFactory.php
│   ├── TeamFactory.php
│   ├── WorkerFactory.php
│   ├── ServiceOrderFactory.php
│   ├── TaskFactory.php
│   ├── MiniTaskFactory.php
│   ├── WorkLogFactory.php
│   ├── LocationFactory.php
│   ├── MaterialFactory.php
│   ├── ServiceTypeFactory.php
│   └── AttachmentFactory.php
│
├── migrations/
│   ├── 2024_01_01_000001_create_users_table.php
│   ├── 2024_01_01_000002_create_roles_table.php
│   ├── 2024_01_01_000003_create_role_permissions_table.php
│   ├── [... 25 total tables ...]
│   └── 2024_01_01_000025_create_attachments_table.php
│
└── seeders/
    ├── DatabaseSeeder.php        → Orchestrator
    ├── UserSeeder.php
    ├── RoleSeeder.php
    ├── SectorSeeder.php
    ├── TeamSeeder.php
    ├── WorkerSeeder.php
    └── ServiceTypeSeeder.php
```

---

## resources/js/

Frontend feature-based.

```
resources/js/
├── Features/
│   ├── Authentication/
│   │   ├── Pages/
│   │   │   ├── LoginPage.vue
│   │   │   ├── RegisterPage.vue
│   │   │   ├── VerifyEmailPage.vue
│   │   │   └── ChangePasswordPage.vue
│   │   ├── Components/
│   │   │   └── [feature-specific components]
│   │   └── composables/
│   │       └── useAuth.js
│   │
│   ├── Clients/
│   │   ├── Pages/
│   │   │   ├── ClientListPage.vue
│   │   │   ├── ClientDetailPage.vue
│   │   │   ├── ClientFormPage.vue
│   │   │   └── ClientEditPage.vue
│   │   ├── Components/
│   │   └── composables/
│   │       └── useClient.js
│   │
│   ├── Tasks/
│   │   ├── Pages/
│   │   ├── Components/
│   │   └── composables/
│   │       └── useTask.js
│   │
│   ├── WorkLogs/
│   │   ├── Pages/
│   │   ├── Components/
│   │   └── composables/
│   │       └── useWorkLog.js
│   │
│   ├── Dashboard/
│   │   └── Pages/
│   │       └── DashboardPage.vue
│   │
│   └── Admin/
│       ├── Pages/
│       └── Components/
│
├── Components/
│   └── Common/
│       ├── Button.vue
│       ├── Card.vue
│       ├── Modal.vue
│       ├── Form.vue
│       ├── Input.vue
│       ├── Select.vue
│       ├── Table.vue
│       ├── Pagination.vue
│       ├── Alert.vue
│       ├── Badge.vue
│       ├── Spinner.vue
│       └── Layout.vue
│
├── composables/
│   ├── useFetch.js               → API calls
│   ├── useForm.js                → Form state + validation
│   ├── useNotification.js        → Toast/alerts
│   ├── useAuth.js                → Auth context
│   └── usePermission.js          → Permission checks
│
├── services/
│   ├── api/
│   │   ├── authService.js
│   │   ├── clientService.js
│   │   ├── taskService.js
│   │   ├── workLogService.js
│   │   └── adminService.js
│   └── localStorage.js           → Persist preferences
│
├── stores/
│   ├── authStore.js              → Pinia: current user + auth state
│   ├── clientStore.js            → Pinia: clients list + filters
│   ├── taskStore.js              → Pinia: tasks list + filters
│   ├── uiStore.js                → Pinia: modals, notifications, sidebar
│   └── settingsStore.js          → Pinia: user preferences
│
└── utils/
    ├── formatters.js             → Date, time, currency formatting
    ├── validators.js             → Common validations
    ├── dateHelpers.js            → Date utilities
    ├── constants.js              → Enums, status values, priority
    └── helpers.js                → Random utilities
```

---

## routes/

API routes by feature.

```
routes/
├── api.php                       → Orchestrator
│   └── Includes all feature routes
│
└── api/
    ├── authentication.php
    ├── clients.php
    ├── service-orders.php
    ├── tasks.php
    ├── mini-tasks.php
    ├── work-logs.php
    ├── sectors.php
    ├── teams.php
    ├── workers.php
    ├── materials.php
    ├── locations.php
    ├── admin.php
    ├── export.php
    └── settings.php
```

---

## Naming Conventions

| Elemento | Padrão | Exemplo |
|----------|--------|---------|
| Model | Singular, PascalCase | `Task`, `WorkLog`, `MiniTask` |
| Controller | Singular + Controller | `TaskController` |
| Service | Singular + Service | `TaskService` |
| Policy | Singular + Policy | `TaskPolicy` |
| Request | Action + Model + Request | `StoreTaskRequest`, `UpdateTaskRequest` |
| Resource | Singular + Resource | `TaskResource` |
| Factory | Singular + Factory | `TaskFactory` |
| Migration | verb + table (snake_case) | `create_tasks_table` |
| Route | lowercase + hyphens | `/api/tasks`, `/api/mini-tasks` |
| Method | camelCase | `updateStatus()`, `assignWorker()` |
| Variable | camelCase | `$taskId`, `$workerName` |
| Constant | UPPERCASE_SNAKE | `MAX_RETRIES`, `CACHE_TTL` |

---

## Code Patterns

### Controller (slim)
```php
class TaskController extends BaseController {
    public function __construct(private TaskService $taskService) {}
    
    public function index(IndexTaskRequest $request) {
        $tasks = $this->taskService->list($request->validated());
        return response()->json(TaskResource::collection($tasks));
    }
    
    public function store(StoreTaskRequest $request) {
        $task = $this->taskService->create($request->validated());
        return response()->json(new TaskResource($task), 201);
    }
}
```

### Service (business logic)
```php
class TaskService {
    public function __construct(
        private TransactionHandler $transactions,
        private CacheManager $cache,
        private PermissionManager $permissions,
    ) {}
    
    public function create(array $data): Task {
        return $this->transactions->execute(function() use ($data) {
            $task = Task::create($data);
            $this->cache->invalidate('tasks', $data['service_order_id']);
            return $task;
        });
    }
    
    public function updateStatus(Task $task, string $status): Task {
        return $this->transactions->execute(function() use ($task, $status) {
            $task->update(['status' => $status]);
            $this->cache->invalidate('tasks', $task->id);
            return $task;
        });
    }
}
```

### Policy (authorization)
```php
class TaskPolicy extends BasePolicy {
    public function view(User $user, Task $task): bool {
        return $this->isAdmin($user) 
            || $this->isOwner($user, $task)
            || $this->hasPermission($user, 'view', 'tasks');
    }
    
    public function update(User $user, Task $task): bool {
        return $this->isAdmin($user) || $this->isOwner($user, $task);
    }
}
```

### Model (relations + scopes)
```php
class Task extends Model {
    use Base, Filterable;
    
    protected $fillable = ['title', 'description', 'status'];
    
    public function serviceOrder() { return $this->belongsTo(ServiceOrder::class); }
    public function miniTasks() { return $this->hasMany(MiniTask::class); }
    public function scopeByStatus($query, $status) { return $query->where('status', $status); }
}
```

---

## Testing Structure

```
tests/
├── Feature/
│   ├── Authentication/
│   │   ├── LoginTest.php
│   │   ├── RegisterTest.php
│   │   └── ChangePasswordTest.php
│   ├── Clients/
│   │   ├── ClientControllerTest.php
│   │   ├── ClientServiceTest.php
│   │   ├── ClientPolicyTest.php
│   │   └── ClientValidationTest.php
│   ├── Tasks/
│   │   ├── TaskControllerTest.php
│   │   ├── TaskServiceTest.php
│   │   ├── TaskPolicyTest.php
│   │   └── TaskStatusTransitionTest.php
│   └── [...other features]
│
└── Unit/
    ├── Services/
    │   ├── TaskServiceTest.php
    │   └── [...other services]
    ├── Policies/
    │   ├── TaskPolicyTest.php
    │   └── [...other policies]
    ├── Helpers/
    │   ├── InputSanitizerTest.php
    │   └── FormattingHelperTest.php
    └── Traits/
        └── FilterableTest.php
```

---

## File Size Targets

- **Controllers**: < 100 linhas
- **Services**: < 200 linhas (split se maior)
- **Models**: < 50 linhas (relações + scopes)
- **Methods**: < 50 linhas
- **Tests**: Cada test < 20 linhas

---

## Import/Require Order

1. Laravel
2. External packages
3. App classes
4. Traits
5. Enums

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use Pest\Testing\TestCase;

use App\Core\Services\CacheManager;
use App\Features\Tasks\Models\Task;

use App\Core\Traits\Base;

use App\Core\Enums\TaskStatus;
```

---

## Principles

✅ **One responsibility per file**  
✅ **Feature contains everything it needs**  
✅ **Core provides infrastructure, not features**  
✅ **Shared contains what multiple features use**  
✅ **Services inject dependencies, no `new` statements**  
✅ **Controllers slim, services fat**  
✅ **Models light, just relations + scopes**  
✅ **Policies handle authorization**  
✅ **Tests mirror source structure**
