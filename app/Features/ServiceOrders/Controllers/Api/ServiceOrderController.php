<?php
namespace App\Features\ServiceOrders\Controllers\Api;

use App\Core\Services\FilterService;
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
use App\Core\Traits\FiltersAdvancedRules;
use App\Http\Controllers\Controller;

class ServiceOrderController extends Controller
{
    use FiltersAdvancedRules;
    public function __construct(
        private ServiceOrderService $serviceOrderService,
        private FilterService $filterService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = $this->filterService->apply(
            ServiceOrder::with(['client.user', 'manager', 'location', 'serviceType', 'sectors']),
            $request->only(['search', 'status', 'priority', 'from_date', 'to_date', 'sort']),
            ['process', 'description', 'priority', 'status']
        );

        // Search across relationship columns
        if ($request->filled('search')) {
            $term = $request->search;
            $query->orWhereHas('client.user', fn($q) => $q
                ->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"])
            );
        }

        $this->applyAdvancedFilters(
            $request, $query, $this->filterService,
            ['process', 'description', 'priority', 'status', 'created_at']
        );

        $user = $request->user();
        $activeRole = $request->input('active_role');

        $orders = $query
            ->when($activeRole === 'attendant', fn($q) => $q->where('created_by', $user->id))
            ->when($activeRole === 'sector_manager', fn($q) => $q->whereHas('sectors', fn($sq) => $sq->whereIn('sectors.id', $user->headedSectors()->pluck('id'))))
            ->when($activeRole === 'manager', fn($q) => $q->where('manager_id', $user->id))
            ->when(!$request->filled('sort'), fn($q) => $q->latest())
            ->paginate(15);

        return ServiceOrderResource::collection($orders);
    }

    public function store(StoreServiceOrderRequest $request): ServiceOrderResource
    {
        $managerId = $request->validated('manager_id');
        $serviceOrder = $this->serviceOrderService->create($request->validated(), $managerId, $request->user()->id);

        $serviceOrder->load(['client.user', 'manager', 'location', 'serviceType']);

        session()->flash('success', 'Service order created successfully.');
        return new ServiceOrderResource($serviceOrder);
    }

    public function show(ServiceOrder $serviceOrder): ServiceOrderResource
    {
        Gate::authorize('view', $serviceOrder);

        $serviceOrder->load([
            'client.user',
            'clientLocation.location.parish',
            'manager',
            'location.parish.municipality.district',
            'serviceType',
            'sectors',
            'tasks' => fn($q) => $q->with([
                'sectors',
                'miniTasks' => fn($mt) => $mt->with([
                    'workLogs' => fn($wl) => $wl->with(['workers', 'materials', 'equipment', 'reviewer']),
                ]),
            ]),
            'attachments',
        ]);
        return new ServiceOrderResource($serviceOrder);
    }

    public function update(UpdateServiceOrderRequest $request, ServiceOrder $serviceOrder): ServiceOrderResource
    {
        Gate::authorize('update', $serviceOrder);

        $updatedOrder = $this->serviceOrderService->update($serviceOrder, $request->validated());
        $updatedOrder->load(['client.user', 'manager', 'location', 'serviceType', 'sectors']);

        session()->flash('success', 'Service order updated successfully.');
        return new ServiceOrderResource($updatedOrder);
    }

    public function cancel(ServiceOrder $serviceOrder): ServiceOrderResource
    {
        Gate::authorize('cancel', $serviceOrder);

        $cancelledOrder = $this->serviceOrderService->cancel($serviceOrder);
        $cancelledOrder->load(['client.user', 'manager', 'location', 'serviceType', 'sectors']);
        return new ServiceOrderResource($cancelledOrder);
    }

    public function activate(ServiceOrder $serviceOrder): ServiceOrderResource
    {
        Gate::authorize('activate', $serviceOrder);

        $activatedOrder = $this->serviceOrderService->activate($serviceOrder);
        $activatedOrder->load(['client.user', 'manager', 'location', 'serviceType', 'sectors']);
        return new ServiceOrderResource($activatedOrder);
    }

    public function complete(ServiceOrder $serviceOrder): ServiceOrderResource
    {
        Gate::authorize('complete', $serviceOrder);

        $completedOrder = $this->serviceOrderService->complete($serviceOrder);
        $completedOrder->load(['client.user', 'manager', 'location', 'serviceType', 'sectors']);
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

        $this->serviceOrderService->delete($serviceOrder);

        session()->flash('success', 'Service order deleted successfully.');
        return response()->json(['message' => 'Service order deleted successfully']);
    }
}
