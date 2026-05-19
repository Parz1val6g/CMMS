<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            if (!Schema::hasColumn('equipments', 'internal_reference')) {
                $table->string('internal_reference', 100)->nullable()->after('license_plate');
            }
            if (!Schema::hasColumn('equipments', 'manufacturing_year')) {
                $table->integer('manufacturing_year')->nullable()->after('internal_reference');
            }
            if (!Schema::hasColumn('equipments', 'inspection_date')) {
                $table->date('inspection_date')->nullable()->after('manufacturing_year');
            }
            if (!Schema::hasColumn('equipments', 'counting_type_id')) {
                $table->foreignUuid('counting_type_id')
                    ->nullable()
                    ->after('inspection_date')
                    ->constrained('counting_types')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('equipments', 'revision_interval')) {
                $table->integer('revision_interval')->nullable()->after('counting_type_id');
            }

            // Drop old field if it still exists
            if (Schema::hasColumn('equipments', 'revision_interval_days')) {
                $table->dropColumn('revision_interval_days');
            }
        });

        // Indexes — guard against duplicate index names from partial runs
        $this->addIndexIfNotExists();
    }

    private function addIndexIfNotExists(): void
    {
        $indexes = $this->getExistingIndexes();

        if (!in_array('equipments_equipment_type_id_index', $indexes)) {
            Schema::table('equipments', fn(Blueprint $t) => $t->index('equipment_type_id'));
        }
        if (!in_array('equipments_counting_type_id_index', $indexes)) {
            Schema::table('equipments', fn(Blueprint $t) => $t->index('counting_type_id'));
        }
        if (!in_array('equipments_license_plate_unique', $indexes)) {
            Schema::table('equipments', fn(Blueprint $t) => $t->unique('license_plate'));
        }
        if (!in_array('equipments_internal_reference_unique', $indexes)) {
            Schema::table('equipments', fn(Blueprint $t) => $t->unique('internal_reference'));
        }
    }

    private function getExistingIndexes(): array
    {
        if (DB::getDriverName() === 'sqlite') {
            $rows = DB::select("PRAGMA index_list('equipments')");
            return array_map(fn($r) => $r->name, $rows);
        }
        $rows = DB::select('SHOW INDEX FROM equipments');
        return array_map(fn($r) => $r->Key_name, $rows);
    }

    public function down(): void
    {
        try {
            Schema::table('equipments', function (Blueprint $table) {
                $table->dropForeign(['counting_type_id']);
            });
        } catch (\Throwable) {}

        try {
            Schema::table('equipments', function (Blueprint $table) {
                $table->dropUnique(['internal_reference']);
            });
        } catch (\Throwable) {}

        try {
            Schema::table('equipments', function (Blueprint $table) {
                $table->dropUnique(['license_plate']);
            });
        } catch (\Throwable) {}

        try {
            Schema::table('equipments', function (Blueprint $table) {
                $table->dropIndex(['counting_type_id']);
            });
        } catch (\Throwable) {}

        try {
            Schema::table('equipments', function (Blueprint $table) {
                $table->dropIndex(['equipment_type_id']);
            });
        } catch (\Throwable) {}

        if (!Schema::hasColumn('equipments', 'revision_interval_days')) {
            Schema::table('equipments', function (Blueprint $table) {
                $table->integer('revision_interval_days')->nullable();
            });
        }

        $dropCols = [];
        foreach (['revision_interval', 'counting_type_id', 'inspection_date', 'manufacturing_year', 'internal_reference'] as $col) {
            if (Schema::hasColumn('equipments', $col)) {
                $dropCols[] = $col;
            }
        }
        if (!empty($dropCols)) {
            try {
                Schema::table('equipments', function (Blueprint $table) use ($dropCols) {
                    $table->dropColumn($dropCols);
                });
            } catch (\Throwable) {}
        }
    }
};
