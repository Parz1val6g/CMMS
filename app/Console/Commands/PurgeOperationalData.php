<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeOperationalData extends Command
{
    protected $signature = 'app:purge-operational-data
                            {--force : Skip confirmation prompt}';

    protected $description = 'Remove all service orders, tasks, mini-tasks, work logs and reset numerators';

    public function handle(): int
    {
        if (! $this->option('force')) {
            $this->warn('This will permanently delete ALL service orders, tasks, mini-tasks and work logs.');
            if (! $this->confirm('Are you sure you want to continue?')) {
                $this->info('Aborted.');
                return self::SUCCESS;
            }
        }

        DB::transaction(function () {
            // work_logs pivot tables
            DB::table('work_log_equipment')->delete();
            DB::table('work_logs_materials')->delete();
            DB::table('work_logs_workers')->delete();
            DB::table('work_logs')->delete();

            // mini_tasks pivot tables
            DB::table('mini_task_equipment')->delete();
            DB::table('mini_tasks_materials')->delete();
            DB::table('mini_tasks_workers_teams')->delete();

            // mini_tasks
            DB::table('mini_tasks')->delete();

            // tasks pivot tables
            DB::table('task_rejections')->delete();
            DB::table('tasks_sectors')->delete();
            DB::table('tasks')->delete();

            // service_orders pivot + dependents
            DB::table('service_order_sector')->delete();
            DB::table('tickets')->whereNotNull('service_order_id')->delete();
            DB::table('service_orders')->delete();

            // polymorphic: attachments
            DB::table('attachments')->whereIn('attachable_type', [
                'App\\Features\\MiniTasks\\Models\\MiniTask',
                'App\\Features\\ServiceOrders\\Models\\ServiceOrder',
                'App\\Features\\Tasks\\Models\\Task',
                'App\\Features\\WorkLogs\\Models\\WorkLog',
            ])->delete();

            // polymorphic: audit_logs
            DB::table('audit_logs')->whereIn('auditable_type', [
                'App\\Features\\MiniTasks\\Models\\MiniTask',
                'App\\Features\\ServiceOrders\\Models\\ServiceOrder',
                'App\\Features\\Tasks\\Models\\Task',
                'App\\Features\\WorkLogs\\Models\\WorkLog',
            ])->delete();

            // all notifications (operational noise)
            DB::table('notifications')->delete();

            // reset numerators
            DB::table('numerators')
                ->whereIn('entity_table', ['service_orders', 'tasks', 'mini_tasks', 'work_logs'])
                ->update(['current_value' => 0, 'last_generated' => null]);
        });

        $this->info('Purge complete. Numerators reset to 0.');

        return self::SUCCESS;
    }
}
