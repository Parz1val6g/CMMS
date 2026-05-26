<?php

namespace App\Features\Tickets\Controllers\Web;

use App\Core\Enums\TicketPriority;
use App\Core\Enums\TicketStatus;
use App\Features\Tickets\Models\Ticket;
use App\Features\Tickets\TicketFormSchema;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TicketPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Ticket::class);

        $user = $request->user();
        $activeRole = $request->session()->get('active_role');

        $tickets = Ticket::with(['client.user', 'serviceType', 'ticketManager'])
            ->when($activeRole === 'ticket_manager', fn($q) => $q->where('ticket_manager_id', $user->id))
            ->latest()
            ->paginate(15)
            ->through(fn($t) => [
                'id'              => $t->id,
                'description'     => $t->description,
                // Use raw enum values so Row.jsx badgeStyle/labelFor work correctly
                'priority'        => $t->priority->value,
                'status'          => $t->status->value,
                'created_at'      => $t->created_at->toIso8601String(),
                'client'          => $t->client ? [
                    'id'   => $t->client->id,
                    'name' => $t->client->user->first_name . ' ' . $t->client->user->last_name,
                ] : null,
                'service_type'    => $t->serviceType ? [
                    'id'   => $t->serviceType->id,
                    'name' => $t->serviceType->name,
                ] : null,
                'ticket_manager'  => $t->ticketManager ? [
                    'id'   => $t->ticketManager->id,
                    'name' => $t->ticketManager->first_name . ' ' . $t->ticketManager->last_name,
                ] : null,
            ]);

        $createSchema = TicketFormSchema::create();
        $updateSchema = TicketFormSchema::update();

        return Inertia::render('Tickets/Pages/Index', [
            'tickets'          => $tickets,
            'columns'          => [
                ['key' => 'description',    'label' => __('messages.controllers.tickets.col_description')],
                ['key' => 'client.name',    'label' => __('messages.controllers.tickets.col_client')],
                ['key' => 'priority',       'label' => __('messages.controllers.tickets.col_priority'), 'sortable' => true],
                ['key' => 'status',         'label' => __('messages.controllers.tickets.col_status'),   'sortable' => true],
                ['key' => 'created_at',     'label' => __('messages.controllers.tickets.col_created'),  'sortable' => true],
            ],
            'formSchema'       => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes'           => [
                'index'   => url('/api/tickets'),
                'store'   => url('/api/tickets'),
                'update'  => url('/api/tickets/:id'),
                'destroy' => url('/api/tickets/:id'),
                'show'    => url('/api/tickets/:id'),
                'convert' => url('/api/tickets/:id/convert'),
            ],
            'filterSchema'     => [
                ['key' => 'status',   'label' => __('messages.controllers.tickets.filter_status'),   'type' => 'select', 'options' => TicketStatus::options()],
                ['key' => 'priority', 'label' => __('messages.controllers.tickets.filter_priority'), 'type' => 'select', 'options' => TicketPriority::options()],
            ],
            'formMeta' => [
                'priorityOptions' => TicketPriority::options(),
                'statusOptions'   => TicketStatus::options(),
            ],
        ]);
    }
}
