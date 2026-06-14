<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mini_task_id')->constrained('mini_tasks')->cascadeOnDelete();
            $table->string('reference', 20)->nullable()->unique();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            // SQLite does not support TIMESTAMPDIFF in generated columns
            if (DB::getDriverName() !== 'sqlite') {
                $table->integer('duration_minutes')->storedAs('TIMESTAMPDIFF(MINUTE, started_at, completed_at)');
            } else {
                $table->integer('duration_minutes')->nullable();
            }
            $table->string('description', 250);
            $table->string('status', 20)->default('in_progress');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('created_at');
        });

        // SQLite does not support ALTER TABLE ADD CONSTRAINT
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE work_logs ADD CONSTRAINT check_time_order CHECK (completed_at > started_at)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('work_logs');
    }
};
