<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class WorkLogMaterialSeeder extends Seeder
{
    public function run(): void
    {
        $workLogs  = DB::table('work_logs')->pluck('id');
        $materials = DB::table('materials')->pluck('id');

        if ($workLogs->isEmpty() || $materials->isEmpty()) return;

        $used = [];
        foreach ($workLogs as $wlId) {
            // Pick a random material not yet linked to this work log
            $candidates = $materials->reject(fn($m) => in_array("{$wlId}-{$m}", $used))->shuffle();
            $matId = $candidates->first();
            if (!$matId) continue;

            $used[] = "{$wlId}-{$matId}";

            DB::table('work_logs_materials')->insert([
                'id'               => Str::uuid(),
                'work_log_id'      => $wlId,
                'material_id'      => $matId,
                'unit_price_at_use' => rand(1, 500) / 100,
                'quantity_used'    => rand(1, 100) / 10,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }
}
