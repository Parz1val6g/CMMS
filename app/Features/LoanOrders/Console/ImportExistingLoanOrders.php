<?php

namespace App\Features\LoanOrders\Console;

use App\Core\Enums\LoanOrderStatus;
use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\WorkflowType;
use App\Features\LoanOrders\Models\LoanOrder;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Tasks\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportExistingLoanOrders extends Command
{
    protected $signature = 'loan-orders:import-existing
                            {--dry-run : Show what would be imported without writing}';

    protected $description = 'Migrate existing loan ServiceOrders into the new LoanOrders table';

    private const STATUS_MAP = [
        ServiceOrderStatus::PENDING->value    => LoanOrderStatus::PENDING->value,
        ServiceOrderStatus::IN_PROGRESS->value => LoanOrderStatus::CHECKED_OUT->value,
        ServiceOrderStatus::COMPLETED->value  => LoanOrderStatus::CHECKED_OUT->value,
        ServiceOrderStatus::CANCELLED->value  => LoanOrderStatus::CANCELLED->value,
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $loanSOs = ServiceOrder::where('workflow_type', WorkflowType::LOAN->value)
            ->whereNull('migrated_to_loan_id')
            ->get();

        if ($loanSOs->isEmpty()) {
            $this->info('No loan ServiceOrders to migrate.');
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn("DRY RUN — {$loanSOs->count()} loan SO(s) would be migrated:");
            foreach ($loanSOs as $so) {
                $targetStatus = self::STATUS_MAP[$so->status->value] ?? LoanOrderStatus::PENDING->value;
                $this->line("  {$so->process} ({$so->status->value} → {$targetStatus})");
            }
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($loanSOs->count());
        $bar->start();

        $imported = 0;
        $errors = 0;

        foreach ($loanSOs as $so) {
            try {
                \DB::transaction(function () use ($so, &$imported) {
                    $targetStatus = self::STATUS_MAP[$so->status->value] ?? LoanOrderStatus::PENDING->value;

                    $loanOrder = LoanOrder::create([
                        'manager_id'         => $so->manager_id,
                        'location_id'        => $so->location_id,
                        'migrated_from_so_id'=> $so->id,
                        'status'             => $targetStatus,
                        'description'        => $so->description,
                        'created_at'         => $so->created_at,
                        'updated_at'         => $so->updated_at,
                    ]);

                    // Sync equipment from legacy pivot (direct DB — relation was removed)
                    $equipmentIds = DB::table('equipment_service_order')
                        ->where('service_order_id', $so->id)
                        ->pluck('equipment_id')
                        ->toArray();
                    if (!empty($equipmentIds)) {
                        $loanOrder->equipments()->sync($equipmentIds);
                    }

                    // Update existing tasks to use polymorphic relationship
                    Task::where('service_order_id', $so->id)
                        ->update([
                            'taskable_id'   => $loanOrder->id,
                            'taskable_type' => LoanOrder::class,
                            'service_order_id' => null,
                        ]);

                    // Set bidirectional reference
                    $so->update(['migrated_to_loan_id' => $loanOrder->id]);

                    $imported++;
                });
            } catch (\Throwable $e) {
                $this->error("Failed to migrate SO {$so->process}: {$e->getMessage()}");
                $errors++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done — {$imported} loan order(s) imported, {$errors} error(s).");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
