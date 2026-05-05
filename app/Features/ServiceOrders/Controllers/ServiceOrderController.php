<?php
namespace App\Features\ServiceOrders\Controllers;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\ServiceOrders\Requests\StoreServiceOrderRequest;
use App\Features\ServiceOrders\Requests\UpdateServiceOrderRequest;
use App\Features\ServiceOrders\Resources\ServiceOrderResource;
use App\Features\ServiceOrders\Services\ServiceOrderService;
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

        $task = $this->serviceOrderService->initiateReturn($serviceOrder);
        return new TaskResource($task);
    }

    public function destroy(ServiceOrder $serviceOrder): JsonResponse
    {
        Gate::authorize('delete', $serviceOrder);

        $serviceOrder->delete();

        session()->flash('success', 'Service order deleted successfully.');
        return response()->json(['message' => 'Service order deleted successfully']);
    }
}
