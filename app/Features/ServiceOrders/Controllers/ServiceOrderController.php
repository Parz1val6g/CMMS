<?php
namespace App\Features\ServiceOrders\Controllers;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\ServiceOrders\Requests\StoreServiceOrderRequest;
use App\Features\ServiceOrders\Requests\UpdateServiceOrderRequest;
use App\Features\ServiceOrders\Resources\ServiceOrderResource;
use App\Features\ServiceOrders\Services\ServiceOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Controllers\Controller;

class ServiceOrderController extends Controller
{
    public function __construct(
        private ServiceOrderService $serviceOrderService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', ServiceOrder::class);

        $orders = ServiceOrder::with(['client.user', 'manager', 'location', 'serviceType'])
            ->latest()
            ->paginate(15);
        return ServiceOrderResource::collection($orders);
    }

    public function store(StoreServiceOrderRequest $request): ServiceOrderResource
    {
        $this->authorize('create', ServiceOrder::class);

        $managerId = $request->user()->id;
        $serviceOrder = $this->serviceOrderService->create($request->validated(), $managerId);
        
        $serviceOrder->load(['client.user', 'manager', 'location', 'serviceType']);
        return new ServiceOrderResource($serviceOrder);
    }

    public function show(ServiceOrder $serviceOrder): ServiceOrderResource
    {
        $this->authorize('view', $serviceOrder);

        $serviceOrder->load(['client.user', 'manager', 'location.parish.municipality.district', 'serviceType', 'tasks.sectors', 'attachments']);
        return new ServiceOrderResource($serviceOrder);
    }

    public function update(UpdateServiceOrderRequest $request, ServiceOrder $serviceOrder): ServiceOrderResource
    {
        $this->authorize('update', $serviceOrder);

        $updatedOrder = $this->serviceOrderService->update($serviceOrder, $request->validated());
        $updatedOrder->load(['client.user', 'manager', 'location', 'serviceType']);
        return new ServiceOrderResource($updatedOrder);
    }

    public function cancel(ServiceOrder $serviceOrder): ServiceOrderResource
    {
        $this->authorize('cancel', $serviceOrder);

        $cancelledOrder = $this->serviceOrderService->cancel($serviceOrder);
        $cancelledOrder->load(['client.user', 'manager', 'location', 'serviceType']);
        return new ServiceOrderResource($cancelledOrder);
    }

    public function complete(ServiceOrder $serviceOrder): ServiceOrderResource
    {
        $this->authorize('complete', $serviceOrder);

        $completedOrder = $this->serviceOrderService->complete($serviceOrder);
        $completedOrder->load(['client.user', 'manager', 'location', 'serviceType']);
        return new ServiceOrderResource($completedOrder);
    }
}
