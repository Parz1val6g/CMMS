<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            // 1 = integer only, 0.5 = half-unit increments, 0.01 = free decimal
            $table->decimal('step', 5, 2)->default(1)->after('abbreviation');
        });

        $rules = [
            ['abbr' => 'ml',  'new_abbr' => 'mlin', 'step' => 0.01],
            ['abbr' => 'rol', 'new_abbr' => null,    'step' => 0.5 ],
            ['abbr' => 'm³',  'new_abbr' => null,    'step' => 0.01],
            ['abbr' => 'kg',  'new_abbr' => null,    'step' => 0.01],
            ['abbr' => 'm',   'new_abbr' => null,    'step' => 0.01],
            ['abbr' => 'm²',  'new_abbr' => null,    'step' => 0.01],
            ['abbr' => 'l',   'new_abbr' => null,    'step' => 0.01],
            // plc, bld, sco, cx, h, d, un keep default step=1
        ];

        foreach ($rules as $rule) {
            $update = ['step' => $rule['step']];
            if ($rule['new_abbr']) {
                $update['abbreviation'] = $rule['new_abbr'];
            }
            DB::table('units')->where('abbreviation', $rule['abbr'])->update($update);
        }
    }

    public function down(): void
    {
        DB::table('units')->where('abbreviation', 'mlin')->update(['abbreviation' => 'ml']);

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('step');
        });
    }
};
