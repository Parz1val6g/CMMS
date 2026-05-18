<?php

namespace App\Features\LoanOrders\Controllers\Api;

use App\Features\LoanOrders\Models\LoanOrder;
use App\Features\LoanOrders\Requests\CancelLoanOrderRequest;
use App\Features\LoanOrders\Requests\StoreLoanOrderRequest;
use App\Features\LoanOrders\Requests\UpdateLoanOrderRequest;
use App\Features\LoanOrders\Resources\LoanOrderResource;
use App\Features\LoanOrders\Services\AvailabilityService;
use App\Features\LoanOrders\Services\LoanOrderService;
use App\Features\Tasks\Resources\TaskResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;

class LoanOrderController extends Controller
{
    public function __construct(
        private LoanOrderService $loanOrderService,
        private AvailabilityService $availabilityService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', LoanOrder::class);

        $user = $request->user();

        $orders = LoanOrder::with(['client.user', 'entity', 'manager', 'location', 'equipments', 'tasks'])
            ->when(!$user->isAdmin(), fn($q) => $q->where('manager_id', $user->id))
            ->latest()
            ->paginate(15);

        return LoanOrderResource::collection($orders);
    }

    public function store(StoreLoanOrderRequest $request): JsonResponse
    {
        Gate::authorize('create', LoanOrder::class);

        $managerId = $request->validated('manager_id');
        $loanOrder = $this->loanOrderService->create($request->validated(), $managerId);

        $loanOrder->load(['client.user', 'entity', 'manager', 'location', 'equipments', 'tasks']);

        return (new LoanOrderResource($loanOrder))->response()->setStatusCode(201);
    }

    public function show(string $id): LoanOrderResource
    {
        $loanOrder = LoanOrder::with([
            'client.user',
            'entity',
            'manager',
            'location.parish.municipality.district',
            'equipments',
            'tasks',
        ])->findOrFail($id);

        Gate::authorize('view', $loanOrder);

        return new LoanOrderResource($loanOrder);
    }

    public function update(UpdateLoanOrderRequest $request, string $id): LoanOrderResource
    {
        $loanOrder = LoanOrder::findOrFail($id);

        Gate::authorize('update', $loanOrder);

        $updated = $this->loanOrderService->update($loanOrder, $request->validated());
        $updated->load(['client.user', 'entity', 'manager', 'location', 'equipments', 'tasks']);

        return new LoanOrderResource($updated);
    }

    public function initiateReturn(string $id): TaskResource
    {
        $loanOrder = LoanOrder::findOrFail($id);

        Gate::authorize('initiateReturn', $loanOrder);

        $task = $this->loanOrderService->initiateReturn($loanOrder);

        return new TaskResource($task);
    }

    public function cancel(CancelLoanOrderRequest $request, string $id): LoanOrderResource
    {
        $loanOrder = LoanOrder::findOrFail($id);

        Gate::authorize('cancel', $loanOrder);

        $cancelled = $this->loanOrderService->cancel($loanOrder, $request->user()->id);
        $cancelled->load(['client.user', 'manager', 'location', 'equipments', 'tasks']);

        return new LoanOrderResource($cancelled);
    }

    public function approve(string $id): JsonResponse
    {
        $loanOrder = LoanOrder::findOrFail($id);

        Gate::authorize('approve', $loanOrder);

        $approved = $this->loanOrderService->approve($loanOrder, auth()->id());
        $approved->load(['client.user', 'entity.user', 'manager', 'location', 'equipments', 'tasks']);

        return (new LoanOrderResource($approved))->response();
    }

    public function checkout(string $id): JsonResponse
    {
        $loanOrder = LoanOrder::findOrFail($id);

        Gate::authorize('checkout', $loanOrder);

        $checkedOut = $this->loanOrderService->checkout($loanOrder);
        $checkedOut->load(['client.user', 'entity.user', 'manager', 'location', 'equipments', 'tasks']);

        return (new LoanOrderResource($checkedOut))->response();
    }

    public function complete(string $id): JsonResponse
    {
        $loanOrder = LoanOrder::findOrFail($id);

        Gate::authorize('complete', $loanOrder);

        $completed = $this->loanOrderService->complete($loanOrder);
        $completed->load(['client.user', 'entity.user', 'manager', 'location', 'equipments', 'tasks']);

        return (new LoanOrderResource($completed))->response();
    }

    public function availability(Request $request, string $equipmentId): JsonResponse
    {
        $from = $request->query('from', now()->toDateString());
        $to   = $request->query('to', now()->addMonths(3)->toDateString());

        $ranges = $this->availabilityService->getOccupiedRanges($equipmentId, $from, $to);

        return response()->json(['data' => $ranges]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $loanOrder = LoanOrder::findOrFail($id);

        Gate::authorize('delete', $loanOrder);

        $this->loanOrderService->delete($loanOrder);

        return response()->json(['message' => 'Loan order deleted successfully.']);
    }
}
