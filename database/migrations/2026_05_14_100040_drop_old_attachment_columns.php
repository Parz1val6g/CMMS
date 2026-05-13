<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // SQLite cannot drop columns referenced by FK constraints (even with PRAGMA).
        // This migration is for MySQL production cleanup only. Skip on SQLite.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Drop pre-existing CHECK constraints before dropping columns
        $constraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attachments' AND CONSTRAINT_TYPE = 'CHECK'");
        foreach ($constraints as $c) {
            DB::statement("ALTER TABLE attachments DROP CONSTRAINT {$c->CONSTRAINT_NAME}");
        }

        // Only drop foreign keys that actually exist
        $fks = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attachments' AND CONSTRAINT_TYPE = 'FOREIGN KEY'");
        $existingFks = array_map(fn($r) => $r->CONSTRAINT_NAME, $fks);

        if (in_array('attachments_service_order_id_foreign', $existingFks)) {
            Schema::table('attachments', fn(Blueprint $t) => $t->dropForeign(['service_order_id']));
        }
        if (in_array('attachments_mini_task_id_foreign', $existingFks)) {
            Schema::table('attachments', fn(Blueprint $t) => $t->dropForeign(['mini_task_id']));
        }

        // Drop old columns if they still exist
        Schema::table('attachments', function (Blueprint $table) {
            if (Schema::hasColumn('attachments', 'service_order_id')) {
                $table->dropColumn('service_order_id');
            }
            if (Schema::hasColumn('attachments', 'mini_task_id')) {
                $table->dropColumn('mini_task_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            if (!Schema::hasColumn('attachments', 'service_order_id')) {
                $table->foreignUuid('service_order_id')->nullable()->constrained('service_orders')->nullOnDelete();
            }
            if (!Schema::hasColumn('attachments', 'mini_task_id')) {
                $table->foreignUuid('mini_task_id')->nullable()->constrained('mini_tasks')->nullOnDelete();
            }
        });
    }
};
