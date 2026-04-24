<?php
namespace App\Features\MiniTasks\Controllers;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\MiniTasks\Requests\StoreMiniTaskRequest;
use App\Features\MiniTasks\Resources\MiniTaskResource;
use App\Features\MiniTasks\Services\MiniTaskService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Controllers\Controller;

class MiniTaskController extends Controller
{
    public function __construct(
        private MiniTaskService $miniTaskService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', MiniTask::class);

        $request->validate(['task_id' => 'required|exists:tasks,id']);
        $miniTasks = MiniTask::with(['supervisor', 'workers.user', 'teams', 'materials.unit'])
            ->where('task_id', $request->task_id)
            ->latest()
            ->paginate(15);
        return MiniTaskResource::collection($miniTasks);
    }

    public function store(StoreMiniTaskRequest $request): MiniTaskResource
    {
        $this->authorize('create', MiniTask::class);

        $supervisorId = $request->user()->id;
        $miniTask = $this->miniTaskService->create($request->validated(), $supervisorId);
        $miniTask->load(['supervisor', 'workers.user', 'teams', 'materials.unit']);
        return new MiniTaskResource($miniTask);
    }

    public function show(MiniTask $miniTask): MiniTaskResource
    {
        $this->authorize('view', $miniTask);

        $miniTask->load(['supervisor', 'workers.user', 'teams', 'materials.unit', 'task', 'workLogs']);
        return new MiniTaskResource($miniTask);
    }

    public function update(Request $request, MiniTask $miniTask): MiniTaskResource
    {
        $this->authorize('update', $miniTask);

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
        $this->authorize('complete', $miniTask);

        $completedMiniTask = $this->miniTaskService->complete($miniTask);
        $completedMiniTask->load(['supervisor', 'workers.user', 'teams', 'materials.unit']);
        return new MiniTaskResource($completedMiniTask);
    }
}
