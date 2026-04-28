<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkLogWorkerSeeder extends Seeder
{
    public function run(): void
    {
        $workLogs = DB::table('work_logs')->pluck('id');
        $workers  = DB::table('workers')->pluck('id');

        if ($workLogs->isEmpty() || $workers->isEmpty()) return;

        foreach ($workLogs as $wlId) {
            // Assign a random worker to each work log
            $workerId = $workers->random();

            $exists = DB::table('work_logs_workers')
                ->where('work_log_id', $wlId)
                ->where('worker_id', $workerId)
                ->exists();

            if (!$exists) {
                DB::table('work_logs_workers')->insert([
                    'work_log_id' => $wlId,
                    'worker_id'   => $workerId,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
