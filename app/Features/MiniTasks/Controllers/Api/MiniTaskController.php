<?php
namespace App\Features\MiniTasks\Controllers\Api;
use App\Core\Services\FilterService;
use App\Core\Traits\FiltersAdvancedRules;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\MiniTasks\Requests\StoreMiniTaskRequest;
use App\Features\MiniTasks\Resources\MiniTaskResource;
use App\Features\MiniTasks\Services\MiniTaskService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;

class MiniTaskController extends Controller
{
    use FiltersAdvancedRules;

    public function __construct(
        private MiniTaskService $miniTaskService,
        private FilterService $filterService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', MiniTask::class);

        $base = MiniTask::with(['task.serviceOrder', 'supervisor', 'workers.user', 'teams', 'materials.unit']);

        if ($request->filled('task_id')) {
            $request->validate(['task_id' => 'exists:tasks,id']);
            $base->where('task_id', $request->task_id);
        }

        $query = $this->filterService->apply(
            $base,
            $request->only(['search', 'status', 'sort']),
            ['description', 'status']
        );

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['description', 'status', 'created_at']
        );

        $miniTasks = $query->when(!$request->filled('sort'), fn($q) => $q->latest())->paginate(15);
        return MiniTaskResource::collection($miniTasks);
    }

    public function store(StoreMiniTaskRequest $request): MiniTaskResource
    {
        Gate::authorize('create', MiniTask::class);

        $supervisorId = $request->user()->id;
        $miniTask = $this->miniTaskService->create($request->validated(), $supervisorId);
        $miniTask->load(['supervisor', 'workers.user', 'teams', 'materials.unit']);
        return new MiniTaskResource($miniTask);
    }

    public function show(MiniTask $miniTask): MiniTaskResource
    {
        Gate::authorize('view', $miniTask);

        $miniTask->load(['supervisor', 'workers.user', 'teams', 'materials.unit', 'task', 'workLogs']);
        return new MiniTaskResource($miniTask);
    }

    public function update(Request $request, MiniTask $miniTask): MiniTaskResource
    {
        Gate::authorize('update', $miniTask);

        if ($miniTask->status === \App\Core\Enums\MiniTaskStatus::COMPLETED->value) {
            throw new \InvalidArgumentException('Cannot update a completed mini-task.');
        }

        $data = $request->validate([
            'description' => ['sometimes', 'string', 'max:250'],
        ]);

        $miniTask->update($data);
        $miniTask->load(['supervisor', 'workers.user', 'teams', 'materials.unit']);

        return new MiniTaskResource($miniTask);
    }

    public function complete(MiniTask $miniTask): MiniTaskResource
    {
        Gate::authorize('complete', $miniTask);

        $completedMiniTask = $this->miniTaskService->complete($miniTask);
        $completedMiniTask->load(['supervisor', 'workers.user', 'teams', 'materials.unit']);
        return new MiniTaskResource($completedMiniTask);
    }
}
