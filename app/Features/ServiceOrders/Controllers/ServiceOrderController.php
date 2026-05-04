<?php
namespace App\Features\ServiceOrders\Controllers;
use App\Core\Enums\TaskStatus;
use App\Core\Enums\WorkflowType;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\ServiceOrders\Requests\StoreServiceOrderRequest;
use App\Features\ServiceOrders\Requests\UpdateServiceOrderRequest;
use App\Features\ServiceOrders\Resources\ServiceOrderResource;
use App\Features\ServiceOrders\Services\ServiceOrderService;
use App\Features\Tasks\Models\Task;
use App\Features\Tasks\Resources\TaskResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;

class ServiceOrderController extends Controller
{
    public function __construct(
        private ServiceOrderService $serviceOrderService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', ServiceOrder::class);

        $user = $request->user();

        $orders = ServiceOrder::with(['client.user', 'manager', 'location', 'serviceType'])
            ->when(!$user->isAdmin(), fn($q) => $q->where('manager_id', $user->id))
            ->latest()
            ->paginate(15);
        return ServiceOrderResource::collection($orders);
    }

    public function store(StoreServiceOrderRequest $request): ServiceOrderResource
    {
        Gate::authorize('create', ServiceOrder::class);

        $managerId = $request->user()->id;
        $serviceOrder = $this->serviceOrderService->create($request->validated(), $managerId);
        
        $serviceOrder->load(['client.user', 'manager', 'location', 'serviceType']);

        session()->flash('success', 'Service order created successfully.');
        return new ServiceOrderResource($serviceOrder);
    }

    public function show(ServiceOrder $serviceOrder): ServiceOrderResource
    {
        Gate::authorize('view', $serviceOrder);

        $serviceOrder->load([
            'client.user',
            'manager',
            'location.parish.municipality.district',
            'serviceType',
            'tasks.sectors',
            'attachments',
            'equipment',
        ]);
        return new ServiceOrderResource($serviceOrder);
    }

    public function update(UpdateServiceOrderRequest $request, ServiceOrder $serviceOrder): ServiceOrderResource
    {
        Gate::authorize('update', $serviceOrder);

        $updatedOrder = $this->serviceOrderService->update($serviceOrder, $request->validated());
        $updatedOrder->load(['client.user', 'manager', 'location', 'serviceType']);

        session()->flash('success', 'Service order updated successfully.');
        return new ServiceOrderResource($updatedOrder);
    }

    public function cancel(ServiceOrder $serviceOrder): ServiceOrderResource
    {
        Gate::authorize('cancel', $serviceOrder);

        $cancelledOrder = $this->serviceOrderService->cancel($serviceOrder);
        $cancelledOrder->load(['client.user', 'manager', 'location', 'serviceType']);
        return new ServiceOrderResource($cancelledOrder);
    }

    public function complete(ServiceOrder $serviceOrder): ServiceOrderResource
    {
        Gate::authorize('complete', $serviceOrder);

        $completedOrder = $this->serviceOrderService->complete($serviceOrder);
        $completedOrder->load(['client.user', 'manager', 'location', 'serviceType']);
        return new ServiceOrderResource($completedOrder);
    }

    public function initiateReturn(ServiceOrder $serviceOrder): TaskResource
    {
        Gate::authorize('update', $serviceOrder);

        abort_if($serviceOrder->workflow_type !== WorkflowType::LOAN->value, 400, 'Initiate return is only valid for loan workflows.');

        abort_if($serviceOrder->tasks()->where('name', 'Devolução de Equipamento')->exists(), 409, 'Return task already exists.');

        $task1 = $serviceOrder->tasks()->where('name', 'Empréstimo de Equipamento')->first();
        abort_if(!$task1 || $task1->status !== TaskStatus::COMPLETED->value, 400, 'Equipment checkout task must be completed before initiating return.');

        $task2 = Task::create([
            'service_order_id' => $serviceOrder->id,
            'manager_id'       => $serviceOrder->manager_id,
            'name'             => 'Devolução de Equipamento',
            'status'           => TaskStatus::PENDING->value,
        ]);

        $task2->load(['sectors', 'manager']);
        return new TaskResource($task2);
    }

    public function destroy(ServiceOrder $serviceOrder): JsonResponse
    {
        Gate::authorize('delete', $serviceOrder);

        $serviceOrder->delete();

        session()->flash('success', 'Service order deleted successfully.');
        return response()->json(['message' => 'Service order deleted successfully']);
    }
}
