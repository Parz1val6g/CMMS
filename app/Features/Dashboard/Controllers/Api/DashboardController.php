<?php

namespace App\Features\Dashboard\Controllers\Api;

use App\Core\Enums\LoanOrderStatus;
use App\Core\Enums\Priority;
use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\TaskStatus;
use App\Core\Enums\TicketStatus;
use App\Features\LoanOrders\Models\LoanOrder;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Tasks\Models\Task;
use App\Features\Tickets\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewDashboard');

        ['current' => $cur, 'previous' => $prev] = $this->periodBounds($request->query('period', 'week'));
        $deltaLabel = $this->deltaLabel($request->query('period', 'week'));

        // ── KPIs (current snapshot + delta vs previous period) ────────────
        $kpis = [
            'active_orders' => [
                'value' => ServiceOrder::whereIn('status', [
                    ServiceOrderStatus::PENDING->value,
                    ServiceOrderStatus::IN_PROGRESS->value,
                ])->count(),
                'delta' => $this->delta(
                    ServiceOrder::whereBetween('created_at', [$cur['start'], $cur['end']])->count(),
                    ServiceOrder::whereBetween('created_at', [$prev['start'], $prev['end']])->count()
                ),
                'delta_label' => $deltaLabel,
            ],

            'open_tickets' => [
                'value' => Ticket::where('status', TicketStatus::OPEN->value)->count(),
                'delta' => $this->delta(
                    Ticket::whereBetween('created_at', [$cur['start'], $cur['end']])->count(),
                    Ticket::whereBetween('created_at', [$prev['start'], $prev['end']])->count()
                ),
                'delta_label' => $deltaLabel,
            ],

            'overdue_tasks' => [
                'value' => Task::where('status', TaskStatus::PENDING->value)
                    ->where('created_at', '<', now()->subDays(3))
                    ->count(),
                'delta' => null,
                'delta_label' => null,
            ],

            'pending_approvals' => [
                'value' => LoanOrder::where('status', LoanOrderStatus::PENDING->value)->count(),
                'delta' => $this->delta(
                    LoanOrder::whereBetween('created_at', [$cur['start'], $cur['end']])->count(),
                    LoanOrder::whereBetween('created_at', [$prev['start'], $prev['end']])->count()
                ),
                'delta_label' => $deltaLabel,
            ],
        ];

        // ── Needs Attention (max 8, sorted by urgency) ────────────────────
        $attention = $this->needsAttention();

        // ── Intervention map ──────────────────────────────────────────────
        $mapOrders = ServiceOrder::with(['location.parish'])
            ->whereHas('location', fn($q) => $q->whereNotNull('latitude')->whereNotNull('longitude'))
            ->whereNotIn('status', [ServiceOrderStatus::COMPLETED->value])
            ->latest()
            ->take(50)
            ->get()
            ->map(fn($o) => [
                'id'          => $o->id,
                'process'     => $o->process,
                'priority'    => $o->priority,
                'description' => $o->location?->street_address
                    ? $o->location->street_address . ($o->location->parish ? ', ' . $o->location->parish->name : '')
                    : ($o->location?->parish?->name ?? ''),
                'latitude'    => (float) $o->location?->latitude,
                'longitude'   => (float) $o->location?->longitude,
            ]);

        return Inertia::render('Dashboard/Pages/Dashboard', [
            'kpis'      => $kpis,
            'attention' => $attention,
            'mapOrders' => $mapOrders,
            'period'    => $request->query('period', 'week'),
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function needsAttention(): array
    {
        $items = collect();

        // High-priority or urgent orders not completed
        ServiceOrder::with(['location.parish'])
            ->where(fn($q) => $q
                ->where('priority', Priority::HIGH->value)
                ->orWhere('priority', Priority::URGENT->value)
            )
            ->whereNotIn('status', [ServiceOrderStatus::COMPLETED->value])
            ->latest()
            ->take(5)
            ->get()
            ->each(fn($o) => $items->push([
                'type'      => 'order',
                'id'        => $o->id,
                'reference' => $o->process,
                'status'    => $o->status->value,
                'priority'  => $o->priority->value,
                'reason'    => 'high_priority',
                'days_open' => $o->created_at->diffInDays(now()),
                'location'  => $o->location?->parish?->name,
            ]));

        // Orders stale > 7 days
        ServiceOrder::with(['location.parish'])
            ->whereIn('status', [ServiceOrderStatus::PENDING->value, ServiceOrderStatus::IN_PROGRESS->value])
            ->where('created_at', '<', now()->subDays(7))
            ->latest()
            ->take(5)
            ->get()
            ->each(fn($o) => $items->push([
                'type'      => 'order',
                'id'        => $o->id,
                'reference' => $o->process,
                'status'    => $o->status->value,
                'priority'  => $o->priority->value,
                'reason'    => 'stale_order',
                'days_open' => $o->created_at->diffInDays(now()),
                'location'  => $o->location?->parish?->name,
            ]));

        // Tasks stale > 3 days
        Task::where('status', TaskStatus::PENDING->value)
            ->where('created_at', '<', now()->subDays(3))
            ->latest()
            ->take(5)
            ->get()
            ->each(fn($t) => $items->push([
                'type'      => 'task',
                'id'        => $t->id,
                'reference' => $t->reference,
                'status'    => $t->status->value,
                'priority'  => null,
                'reason'    => 'stale_task',
                'days_open' => $t->created_at->diffInDays(now()),
                'location'  => null,
            ]));

        return $items
            ->unique('id')
            ->sortByDesc('days_open')
            ->take(8)
            ->values()
            ->toArray();
    }

    private function periodBounds(string $period): array
    {
        return match ($period) {
            'today' => [
                'current'  => ['start' => today(),              'end' => now()],
                'previous' => ['start' => today()->subDay(),    'end' => today()->subSecond()],
            ],
            'month' => [
                'current'  => ['start' => now()->startOfMonth(), 'end' => now()],
                'previous' => ['start' => now()->subMonth()->startOfMonth(), 'end' => now()->subMonth()->endOfMonth()],
            ],
            default => [ // week
                'current'  => ['start' => now()->startOfWeek(), 'end' => now()],
                'previous' => ['start' => now()->subWeek()->startOfWeek(), 'end' => now()->subWeek()->endOfWeek()],
            ],
        };
    }

    private function deltaLabel(string $period): string
    {
        return match ($period) {
            'today' => 'vs ontem',
            'month' => 'vs mês passado',
            default => 'vs semana passada',
        };
    }

    private function delta(int $current, int $previous): int
    {
        return $current - $previous;
    }
}
