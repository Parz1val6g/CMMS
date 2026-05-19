<?php

namespace App\Features\Tickets\Controllers\Api;

use App\Features\Tickets\Models\Ticket;
use App\Features\Tickets\Requests\ConvertTicketRequest;
use App\Features\Tickets\Requests\StoreTicketRequest;
use App\Features\Tickets\Requests\UpdateTicketRequest;
use App\Features\Tickets\Resources\TicketResource;
use App\Features\Tickets\Services\TicketService;
use App\Features\ServiceOrders\Resources\ServiceOrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;

class TicketController extends Controller
{
    public function __construct(
        private TicketService $ticketService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Ticket::class);

        $user = $request->user();

        $query = Ticket::with(['client.user', 'serviceType', 'ticketManager']);

        // ticket_manager role sees only own tickets
        if (!$user->isAdmin() && !$user->roles()->whereIn('name', ['manager'])->exists()) {
            $query->where('ticket_manager_id', $user->id);
        }

        $tickets = $query->latest()->paginate(15);

        return TicketResource::collection($tickets);
    }

    public function store(StoreTicketRequest $request): TicketResource
    {
        Gate::authorize('create', Ticket::class);

        $managerId = $request->user()->id;
        $ticket = $this->ticketService->create($request->validated(), $managerId);

        $ticket->load(['client.user', 'serviceType', 'ticketManager']);

        return new TicketResource($ticket);
    }

    public function show(Ticket $ticket): TicketResource
    {
        Gate::authorize('view', $ticket);

        $ticket->load(['client.user', 'serviceType', 'ticketManager', 'serviceOrder']);

        return new TicketResource($ticket);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): TicketResource
    {
        Gate::authorize('update', $ticket);

        $updatedTicket = $this->ticketService->update($ticket, $request->validated());
        $updatedTicket->load(['client.user', 'serviceType', 'ticketManager']);

        return new TicketResource($updatedTicket);
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        Gate::authorize('delete', $ticket);

        $this->ticketService->cancel($ticket);

        return response()->json(['message' => 'Ticket cancelled successfully.']);
    }

    public function convert(ConvertTicketRequest $request, Ticket $ticket): ServiceOrderResource
    {
        Gate::authorize('convert', $ticket);

        $serviceOrder = $this->ticketService->convertToServiceOrder($ticket, $request->validated());

        $serviceOrder->load(['client.user', 'manager', 'location', 'serviceType']);

        return new ServiceOrderResource($serviceOrder);
    }
}
