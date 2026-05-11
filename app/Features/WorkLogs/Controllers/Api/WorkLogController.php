<?php
namespace App\Features\WorkLogs\Controllers\Api;
use App\Core\Services\FilterService;
use App\Core\Traits\FiltersAdvancedRules;
use App\Features\WorkLogs\Models\WorkLog;
use App\Features\WorkLogs\Requests\StoreWorkLogRequest;
use App\Features\WorkLogs\Resources\WorkLogResource;
use App\Features\WorkLogs\Services\WorkLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;

class WorkLogController extends Controller
{
    use FiltersAdvancedRules;

    public function __construct(
        private WorkLogService $workLogService,
        private FilterService $filterService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', WorkLog::class);

        $base = WorkLog::with(['miniTask.task', 'workers.user', 'materials', 'reviewer']);

        if ($request->filled('mini_task_id')) {
            $request->validate(['mini_task_id' => 'exists:mini_tasks,id']);
            $base->where('mini_task_id', $request->mini_task_id);
        }

        $query = $this->filterService->apply(
            $base,
            $request->only(['search', 'status', 'sort']),
            ['description', 'status']
        );

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['description', 'status', 'completed_at']
        );

        $workLogs = $query->when(!$request->filled('sort'), fn($q) => $q->latest())->paginate(15);
        return WorkLogResource::collection($workLogs);
    }

    public function store(StoreWorkLogRequest $request): WorkLogResource
    {
        Gate::authorize('create', WorkLog::class);

        $data = $request->validated();

        if (!empty($data['materials'])) {
            $formattedMaterials = [];
            foreach ($data['materials'] as $mat) {
                $formattedMaterials[$mat['material_id']] = ['quantity_used' => $mat['quantity_used']];
            }
            $data['materials'] = $formattedMaterials;
        }
        $workLog = $this->workLogService->create($data);
        $workLog->load(['workers.user', 'materials']);
        return new WorkLogResource($workLog);
    }

    public function show(WorkLog $workLog): WorkLogResource
    {
        Gate::authorize('view', $workLog);

        $workLog->load(['workers.user', 'materials']);
        return new WorkLogResource($workLog);
    }

    public function update(Request $request, WorkLog $workLog): WorkLogResource
    {
        Gate::authorize('update', $workLog);

        if ($workLog->completed_at !== null) {
            throw new \InvalidArgumentException('Cannot update an already completed work log.');
        }

        $data = $request->validate([
            'description' => ['sometimes', 'string', 'max:250'],
        ]);

        $workLog->update($data);
        $workLog->load(['workers.user', 'materials']);

        return new WorkLogResource($workLog);
    }

    public function complete(Request $request, WorkLog $workLog): WorkLogResource
    {
        Gate::authorize('complete', $workLog);

        $request->validate([
            'completed_at' => ['required', 'date', 'after:' . $workLog->started_at],
            'materials' => ['nullable', 'array'],
            'materials.*.material_id' => ['required', 'exists:materials,id'],
            'materials.*.quantity_used' => ['required', 'numeric', 'min:0.01'],
        ]);

        $materials = [];
        if ($request->has('materials')) {
            foreach ($request->materials as $mat) {
                $materials[$mat['material_id']] = ['quantity_used' => $mat['quantity_used']];
            }
        }

        $completedWorkLog = $this->workLogService->complete($workLog, $request->completed_at, $materials);
        $completedWorkLog->load(['workers.user', 'materials']);
        return new WorkLogResource($completedWorkLog);
    }

    public function approve(Request $request, WorkLog $workLog): WorkLogResource
    {
        Gate::authorize('approve', $workLog);

        $approved = $this->workLogService->approve($workLog, $request->user()->id);
        $approved->load(['workers.user', 'materials', 'reviewer']);
        return new WorkLogResource($approved);
    }

    public function reject(Request $request, WorkLog $workLog): WorkLogResource
    {
        Gate::authorize('reject', $workLog);

        $rejected = $this->workLogService->reject($workLog, $request->user()->id);
        $rejected->load(['workers.user', 'materials', 'reviewer']);
        return new WorkLogResource($rejected);
    }
}
