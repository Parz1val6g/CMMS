<?php

namespace App\Console\Commands;

use App\Core\Services\NumeratorService;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\Tasks\Models\Task;
use App\Features\WorkLogs\Models\WorkLog;
use Illuminate\Console\Command;

class BackfillReferences extends Command
{
    protected $signature = 'app:backfill-references
                            {--dry-run : Show what would be updated without writing to the database}';

    protected $description = 'Backfill NULL reference codes on Tasks, MiniTasks, and WorkLogs using the NumeratorService';

    private NumeratorService $numerator;

    public function __construct(NumeratorService $numerator)
    {
        parent::__construct();
        $this->numerator = $numerator;
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be written.');
        }

        $this->backfill(Task::class,     'TK', 'tasks');
        $this->backfill(MiniTask::class, 'MT', 'mini_tasks');
        $this->backfill(WorkLog::class,  'WL', 'work_logs');

        $this->newLine();
        $this->info('Backfill complete.');

        return self::SUCCESS;
    }

    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     */
    private function backfill(string $modelClass, string $initials, string $table): void
    {
        $dryRun = $this->option('dry-run');

        // Use withTrashed() if the model uses SoftDeletes, so we fix soft-deleted rows too
        $query = method_exists($modelClass, 'withTrashed')
            ? $modelClass::withTrashed()->whereNull('reference')->oldest()
            : $modelClass::whereNull('reference')->oldest();

        $count = $query->count();

        if ($count === 0) {
            $this->line("  <fg=green>✓</> {$table}: no NULL references found.");
            return;
        }

        $this->line("  <fg=yellow>→</> {$table}: {$count} row(s) to backfill…");

        if ($dryRun) {
            return;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $query->each(function ($model) use ($initials, $table, $bar) {
            // Use the year from created_at so old records get the correct year in their code
            $year = (int) $model->created_at->format('Y');

            $reference = $this->numerator->format($initials, $table, $year);

            // Bypass model events to avoid triggering HasAutoReference again
            $model->timestamps = false;
            $model->withoutEvents(fn () => $model->update(['reference' => $reference]));
            $model->timestamps = true;

            $bar->advance();
        });

        $bar->finish();
        $this->newLine();
        $this->line("  <fg=green>✓</> {$table}: {$count} reference(s) generated.");
    }
}
