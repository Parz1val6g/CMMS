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
        $user = $request->user();
        $activeRole = $request->input('active_role');

        $base = MiniTask::with(['task.serviceOrder', 'supervisor', 'workers.user', 'teams', 'materials.unit', 'equipment'])
            ->when($activeRole === 'manager', fn($q) => $q->whereHas('task.serviceOrder', fn($sq) => $sq->where('manager_id', $user->id)))
            ->when($activeRole === 'task_manager', fn($q) => $q->where('supervisor_id', $user->id))
            ->when($activeRole === 'sector_manager', fn($q) => $q->whereHas('task.sectors', fn($sq) => $sq->whereIn('sectors.id', $user->headedSectors()->pluck('id'))))
            ->when($activeRole === 'worker', fn($q) => $q->whereHas('workers', fn($wq) => $wq->where('workers.user_id', $user->id)));

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
        $supervisorId = $request->user()->id;
        $miniTask = $this->miniTaskService->create($request->validated(), $supervisorId);
        $miniTask->load(['supervisor', 'workers.user', 'teams', 'materials.unit', 'equipment']);
        return new MiniTaskResource($miniTask);
    }

    public function show(MiniTask $miniTask): MiniTaskResource
    {
        Gate::authorize('view', $miniTask);

        $miniTask->load(['supervisor', 'workers.user', 'teams', 'materials.unit', 'equipment', 'task', 'workLogs']);
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
            'start_date'  => ['sometimes', 'nullable', 'date'],
            'end_date'    => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $miniTask->update($data);
        $miniTask->load(['supervisor', 'workers.user', 'teams', 'materials.unit', 'equipment']);

        return new MiniTaskResource($miniTask);
    }

    public function complete(MiniTask $miniTask): MiniTaskResource
    {
        Gate::authorize('complete', $miniTask);

        $completedMiniTask = $this->miniTaskService->complete($miniTask);
        $completedMiniTask->load(['supervisor', 'workers.user', 'teams', 'materials.unit', 'equipment']);
        return new MiniTaskResource($completedMiniTask);
    }
}
