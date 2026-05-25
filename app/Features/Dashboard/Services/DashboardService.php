<?php

namespace App\Features\Dashboard\Services;

use App\Core\Enums\MiniTaskStatus;
use App\Core\Enums\Priority;
use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\TaskStatus;
use App\Core\Enums\TicketStatus;
use App\Core\Enums\WorkLogStatus;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Tasks\Models\Task;
use App\Features\Teams\Models\Team;
use App\Features\Tickets\Models\Ticket;
use App\Features\Workers\Models\Worker;
use App\Features\WorkLogs\Models\WorkLog;
use App\Shared\Models\Role;
use App\Shared\Models\User;
use Illuminate\Support\Collection;
use Inertia\Response;
use Inertia\Inertia;

class DashboardService
{
    public function admin(User $user): Response
    {
        $kpis = [
            'total_users'    => ['value' => User::count()],
            'active_roles'   => ['value' => Role::whereHas('users')->count()],
            'new_users_week' => ['value' => User::where('created_at', '>=', now()->startOfWeek())->count()],
        ];

        $recentUsers = User::with('roles')
            ->latest()
            ->take(8)
            ->get()
            ->map(fn($u) => [
                'id'         => $u->id,
                'name'       => $u->first_name . ' ' . $u->last_name,
                'email'      => $u->email,
                'roles'      => $u->roles->pluck('name')->join(', '),
                'created_at' => $u->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Dashboard/Pages/Dashboard', [
            'role'        => 'admin',
            'kpis'        => $kpis,
            'recentUsers' => $recentUsers,
        ]);
    }

    public function manager(User $user, string $period = 'week'): Response
    {
        ['current' => $cur, 'previous' => $prev] = $this->periodBounds($period);
        $deltaLabel = $this->deltaLabel($period);

        $kpis = [
            'active_orders' => [
                'value' => ServiceOrder::whereIn('status', [
                    ServiceOrderStatus::PENDING->value,
                    ServiceOrderStatus::IN_PROGRESS->value,
                ])->count(),
                'delta'       => $this->delta(
                    ServiceOrder::whereBetween('created_at', [$cur['start'], $cur['end']])->count(),
                    ServiceOrder::whereBetween('created_at', [$prev['start'], $prev['end']])->count()
                ),
                'delta_label' => $deltaLabel,
            ],
            'open_tickets' => [
                'value' => Ticket::where('status', TicketStatus::OPEN->value)->count(),
                'delta'       => $this->delta(
                    Ticket::whereBetween('created_at', [$cur['start'], $cur['end']])->count(),
                    Ticket::whereBetween('created_at', [$prev['start'], $prev['end']])->count()
                ),
                'delta_label' => $deltaLabel,
            ],
            'overdue_tasks' => [
                'value'       => Task::where('status', TaskStatus::PENDING->value)
                    ->where('created_at', '<', now()->subDays(3))
                    ->count(),
                'delta'       => null,
                'delta_label' => null,
            ],
            'awaiting_review' => [
                'value'       => ServiceOrder::where('status', ServiceOrderStatus::AWAITING_APPROVAL->value)->count(),
                'delta'       => null,
                'delta_label' => null,
            ],
        ];

        return Inertia::render('Dashboard/Pages/Dashboard', [
            'role'      => 'manager',
            'kpis'      => $kpis,
            'attention' => $this->managerAttention(),
            'mapOrders' => $this->interventionMap(),
            'period'    => $period,
        ]);
    }

    public function attendant(User $user, string $period = 'week'): Response
    {
        ['current' => $cur, 'previous' => $prev] = $this->periodBounds($period);
        $deltaLabel = $this->deltaLabel($period);

        $kpis = [
            'pending_orders' => [
                'value'       => ServiceOrder::where('status', ServiceOrderStatus::PENDING->value)->count(),
                'delta'       => null,
                'delta_label' => null,
            ],
            'open_tickets' => [
                'value'       => Ticket::where('status', TicketStatus::OPEN->value)->count(),
                'delta'       => $this->delta(
                    Ticket::whereBetween('created_at', [$cur['start'], $cur['end']])->count(),
                    Ticket::whereBetween('created_at', [$prev['start'], $prev['end']])->count()
                ),
                'delta_label' => $deltaLabel,
            ],
            'new_orders' => [
                'value'       => ServiceOrder::whereBetween('created_at', [$cur['start'], $cur['end']])->count(),
                'delta'       => $this->delta(
                    ServiceOrder::whereBetween('created_at', [$cur['start'], $cur['end']])->count(),
                    ServiceOrder::whereBetween('created_at', [$prev['start'], $prev['end']])->count()
                ),
                'delta_label' => $deltaLabel,
            ],
        ];

        $recentOrders = ServiceOrder::with(['serviceType', 'location.parish'])
            ->latest()
            ->take(8)
            ->get()
            ->map(fn($o) => [
                'id'           => $o->id,
                'process'      => $o->process,
                'description'  => $o->description,
                'status'       => $o->status->value,
                'priority'     => $o->priority->value,
                'service_type' => $o->serviceType?->name,
                'location'     => $o->location?->parish?->name,
                'created_at'   => $o->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Dashboard/Pages/Dashboard', [
            'role'         => 'attendant',
            'kpis'         => $kpis,
            'recentOrders' => $recentOrders,
            'period'       => $period,
        ]);
    }

    public function taskManager(User $user): Response
    {
        $kpis = [
            'active_tasks' => [
                'value' => Task::where('manager_id', $user->id)
                    ->whereIn('status', [TaskStatus::PENDING->value, TaskStatus::IN_PROGRESS->value])
                    ->count(),
            ],
            'awaiting_approval' => [
                'value' => Task::where('manager_id', $user->id)
                    ->where('status', TaskStatus::AWAITING_APPROVAL->value)
                    ->count(),
            ],
            'pending_mini_tasks' => [
                'value' => MiniTask::where('supervisor_id', $user->id)
                    ->whereIn('status', [MiniTaskStatus::PENDING->value, MiniTaskStatus::IN_PROGRESS->value])
                    ->count(),
            ],
        ];

        $attention = Task::where('manager_id', $user->id)
            ->where('status', TaskStatus::AWAITING_APPROVAL->value)
            ->where('updated_at', '<', now()->subDays(2))
            ->latest('updated_at')
            ->take(8)
            ->get()
            ->map(fn($t) => [
                'type'      => 'task',
                'id'        => $t->id,
                'reference' => $t->reference,
                'reason'    => 'awaiting_approval',
                'age_label' => $t->updated_at->diffInDays(now()) . 'd',
            ])
            ->toArray();

        return Inertia::render('Dashboard/Pages/Dashboard', [
            'role'      => 'task_manager',
            'kpis'      => $kpis,
            'attention' => $attention,
        ]);
    }

    public function sectorManager(User $user): Response
    {
        $sectorIds = $user->headedSectors()->pluck('id');

        $kpis = [
            'active_tasks' => [
                'value' => Task::whereHas('sectors', fn($q) => $q->whereIn('sectors.id', $sectorIds))
                    ->whereIn('status', [TaskStatus::PENDING->value, TaskStatus::IN_PROGRESS->value])
                    ->count(),
            ],
            'teams' => [
                'value' => Team::whereIn('sector_id', $sectorIds)->count(),
            ],
            'workers' => [
                'value' => Worker::whereIn('team_id', function ($sub) use ($sectorIds) {
                    $sub->select('id')->from('teams')->whereIn('sector_id', $sectorIds);
                })->count(),
            ],
        ];

        $attention = Task::whereHas('sectors', fn($q) => $q->whereIn('sectors.id', $sectorIds))
            ->whereIn('status', [TaskStatus::PENDING->value, TaskStatus::IN_PROGRESS->value])
            ->where('created_at', '<', now()->subDays(3))
            ->latest()
            ->take(8)
            ->get()
            ->map(fn($t) => [
                'type'      => 'task',
                'id'        => $t->id,
                'reference' => $t->reference,
                'reason'    => 'stale_task',
                'age_label' => $t->created_at->diffInDays(now()) . 'd',
            ])
            ->toArray();

        $mapOrders = ServiceOrder::with(['location.parish'])
            ->whereHas('tasks.sectors', fn($q) => $q->whereIn('sectors.id', $sectorIds))
            ->whereHas('location', fn($q) => $q->whereNotNull('latitude')->whereNotNull('longitude'))
            ->whereNotIn('status', [ServiceOrderStatus::COMPLETED->value, ServiceOrderStatus::CANCELLED->value])
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
            'role'      => 'sector_manager',
            'kpis'      => $kpis,
            'attention' => $attention,
            'mapOrders' => $mapOrders,
        ]);
    }

    public function teamManager(User $user): Response
    {
        $teamIds = Team::where('responsible_id', $user->id)->pluck('id');

        $kpis = [
            'workers' => [
                'value' => Worker::whereIn('team_id', $teamIds)->count(),
            ],
            'pending_mini_tasks' => [
                'value' => MiniTask::whereHas('teams', fn($q) => $q->whereIn('teams.id', $teamIds))
                    ->whereIn('status', [MiniTaskStatus::PENDING->value, MiniTaskStatus::IN_PROGRESS->value])
                    ->count(),
            ],
            'completed_today' => [
                'value' => MiniTask::whereHas('teams', fn($q) => $q->whereIn('teams.id', $teamIds))
                    ->where('status', MiniTaskStatus::COMPLETED->value)
                    ->whereDate('updated_at', today())
                    ->count(),
            ],
        ];

        $teamWorkers = Worker::with(['user', 'team'])
            ->whereIn('team_id', $teamIds)
            ->get()
            ->map(fn($w) => [
                'id'   => $w->id,
                'name' => $w->user?->first_name . ' ' . $w->user?->last_name,
                'team' => $w->team?->name,
            ]);

        return Inertia::render('Dashboard/Pages/Dashboard', [
            'role'        => 'team_manager',
            'kpis'        => $kpis,
            'teamWorkers' => $teamWorkers,
        ]);
    }

    public function worker(User $user): Response
    {
        $worker = $user->worker;

        $kpis = [
            'pending_mini_tasks' => [
                'value' => $worker
                    ? MiniTask::whereHas('workers', fn($q) => $q->where('workers.id', $worker->id))
                        ->whereIn('status', [MiniTaskStatus::PENDING->value, MiniTaskStatus::IN_PROGRESS->value])
                        ->count()
                    : 0,
            ],
            'open_work_logs' => [
                'value' => $worker
                    ? WorkLog::where('status', WorkLogStatus::IN_PROGRESS->value)
                        ->whereHas('workers', fn($q) => $q->where('workers.id', $worker->id))
                        ->count()
                    : 0,
            ],
            'completed_today' => [
                'value' => $worker
                    ? MiniTask::whereHas('workers', fn($q) => $q->where('workers.id', $worker->id))
                        ->where('status', MiniTaskStatus::COMPLETED->value)
                        ->whereDate('updated_at', today())
                        ->count()
                    : 0,
            ],
        ];

        $attention = $worker
            ? WorkLog::where('status', WorkLogStatus::IN_PROGRESS->value)
                ->whereHas('workers', fn($q) => $q->where('workers.id', $worker->id))
                ->where('started_at', '<', now()->subHours(8))
                ->latest('started_at')
                ->take(8)
                ->get()
                ->map(fn($wl) => [
                    'type'      => 'work_log',
                    'id'        => $wl->id,
                    'reference' => $wl->reference,
                    'reason'    => 'open_work_log',
                    'age_label' => $wl->started_at->diffInHours(now()) . 'h',
                ])
                ->toArray()
            : [];

        return Inertia::render('Dashboard/Pages/Dashboard', [
            'role'      => 'worker',
            'kpis'      => $kpis,
            'attention' => $attention,
        ]);
    }

    public function managerAttention(): array
    {
        $items = collect();

        ServiceOrder::with(['location.parish'])
            ->where(fn($q) => $q
                ->where('priority', Priority::HIGH->value)
                ->orWhere('priority', Priority::URGENT->value)
            )
            ->whereNotIn('status', [ServiceOrderStatus::COMPLETED->value, ServiceOrderStatus::CANCELLED->value])
            ->latest()
            ->take(5)
            ->get()
            ->each(fn($o) => $items->push([
                'type'      => 'order',
                'id'        => $o->id,
                'reference' => $o->process,
                'reason'    => 'high_priority',
                'age_label' => $o->created_at->diffInDays(now()) . 'd',
                'location'  => $o->location?->parish?->name,
            ]));

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
                'reason'    => 'stale_order',
                'age_label' => $o->created_at->diffInDays(now()) . 'd',
                'location'  => $o->location?->parish?->name,
            ]));

        Task::where('status', TaskStatus::PENDING->value)
            ->where('created_at', '<', now()->subDays(3))
            ->latest()
            ->take(5)
            ->get()
            ->each(fn($t) => $items->push([
                'type'      => 'task',
                'id'        => $t->id,
                'reference' => $t->reference,
                'reason'    => 'stale_task',
                'age_label' => $t->created_at->diffInDays(now()) . 'd',
                'location'  => null,
            ]));

        return $items->unique('id')->sortByDesc('age_label')->take(8)->values()->toArray();
    }

    public function interventionMap(): Collection
    {
        return ServiceOrder::with(['location.parish'])
            ->whereHas('location', fn($q) => $q->whereNotNull('latitude')->whereNotNull('longitude'))
            ->whereNotIn('status', [ServiceOrderStatus::COMPLETED->value, ServiceOrderStatus::CANCELLED->value])
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
    }

    public function periodBounds(string $period): array
    {
        return match ($period) {
            'today' => [
                'current'  => ['start' => today(),            'end' => now()],
                'previous' => ['start' => today()->subDay(),  'end' => today()->subSecond()],
            ],
            'month' => [
                'current'  => ['start' => now()->startOfMonth(), 'end' => now()],
                'previous' => ['start' => now()->subMonth()->startOfMonth(), 'end' => now()->subMonth()->endOfMonth()],
            ],
            default => [
                'current'  => ['start' => now()->startOfWeek(), 'end' => now()],
                'previous' => ['start' => now()->subWeek()->startOfWeek(), 'end' => now()->subWeek()->endOfWeek()],
            ],
        };
    }

    public function deltaLabel(string $period): string
    {
        return match ($period) {
            'today' => 'vs ontem',
            'month' => 'vs mês passado',
            default => 'vs semana passada',
        };
    }

    public function delta(int $current, int $previous): int
    {
        return $current - $previous;
    }
}
