<?php

namespace App\Console\Commands;

use App\Core\Enums\EquipmentStatus;
use App\Core\Enums\MiniTaskStatus;
use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\WorkflowType;
use App\Core\Enums\WorkLogStatus;
use App\Features\Equipments\Models\Equipment;
use App\Features\MiniTasks\Services\MiniTaskService;
use Illuminate\Console\Command;

class ReconcileEquipmentStatus extends Command
{
    protected $signature = 'app:reconcile-equipment-status
                            {--dry-run : Show what would change without writing to the database}';

    protected $description = 'Reconcile equipment statuses against active loan orders, work logs, and mini-task plans';

    public function __construct(private MiniTaskService $miniTaskService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be written.');
        }

        $fixed = 0;

        // ── 1. IN_USE with no justification ──────────────────────────────
        // Equipment is IN_USE but has neither an active loan service order
        // nor an active (in_progress / submitted) work log.
        $orphanInUse = Equipment::where('status', EquipmentStatus::IN_USE->value)
            ->whereDoesntHave('serviceOrders', fn ($q) => $q
                ->where('workflow_type', WorkflowType::LOAN->value)
                ->whereNotIn('status', [
                    ServiceOrderStatus::CANCELLED->value,
                    ServiceOrderStatus::COMPLETED->value,
                ])
            )
            ->whereDoesntHave('workLogs', fn ($q) => $q
                ->whereIn('status', [WorkLogStatus::IN_PROGRESS->value, WorkLogStatus::SUBMITTED->value])
            )
            ->get();

        // ── 2. RESERVED with no justification ────────────────────────────
        // Equipment is RESERVED but has no active mini-task planning it.
        $orphanReserved = Equipment::where('status', EquipmentStatus::RESERVED->value)
            ->whereDoesntHave('miniTasks', fn ($q) => $q
                ->whereNotIn('status', [MiniTaskStatus::COMPLETED->value, MiniTaskStatus::CANCELLED->value])
            )
            ->get();

        $orphans = $orphanInUse->merge($orphanReserved);

        if ($orphans->isEmpty()) {
            $this->info('All equipment statuses are consistent — nothing to fix.');
            return self::SUCCESS;
        }

        $this->warn("Found {$orphans->count()} inconsistent equipment item(s):");

        $rows = $orphans->map(fn ($e) => [
            $e->id,
            $e->name,
            "{$e->brand} {$e->model}",
            $e->serial_number,
            $e->status->value,
            '→ active',
        ])->toArray();

        $this->table(['ID', 'Name', 'Brand / Model', 'Serial', 'Current', 'Action'], $rows);

        if ($dryRun) {
            return self::SUCCESS;
        }

        if (!$this->confirm("Recalculate and fix {$orphans->count()} equipment item(s)?", true)) {
            $this->line('Aborted.');
            return self::SUCCESS;
        }

        foreach ($orphans as $equipment) {
            // Use the shared recalculate logic so the correct target state is
            // derived from live DB state rather than hard-coding ACTIVE.
            $this->miniTaskService->recalculateStatus($equipment);
            $fixed++;
        }

        $this->info("Done — {$fixed} equipment item(s) reconciled.");

        return self::SUCCESS;
    }
}
