<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->foreignUuid('equipment_id')
                ->nullable()
                ->constrained('equipments')
                ->nullOnDelete();
            $table->string('attachable_type', 255)->nullable();
            $table->uuid('attachable_id')->nullable();

            $table->index(['attachable_type', 'attachable_id']);
            $table->index('equipment_id');
        });

        // CHECK constraint: both or neither must be set
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE attachments ADD CONSTRAINT attachments_attachable_check CHECK (
                (attachable_type IS NOT NULL AND attachable_id IS NOT NULL) OR
                (attachable_type IS NULL AND attachable_id IS NULL)
            )");
        }
    }

    public function down(): void
    {
        try {
            Schema::table('attachments', function (Blueprint $table) {
                $table->dropForeign(['equipment_id']);
            });
        } catch (\Throwable) {}

        try {
            Schema::table('attachments', function (Blueprint $table) {
                $table->dropIndex(['attachable_type', 'attachable_id']);
            });
        } catch (\Throwable) {}

        try {
            Schema::table('attachments', function (Blueprint $table) {
                $table->dropIndex(['equipment_id']);
            });
        } catch (\Throwable) {}

        if (
            Schema::hasColumn('attachments', 'attachable_type')
            || Schema::hasColumn('attachments', 'attachable_id')
            || Schema::hasColumn('attachments', 'equipment_id')
        ) {
            try {
                Schema::table('attachments', function (Blueprint $table) {
                    $table->dropColumn(['attachable_type', 'attachable_id', 'equipment_id']);
                });
            } catch (\Throwable) {}
        }
    }
};
