<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mini_task_id')->constrained('mini_tasks')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('completed_at');
            $table->string('description', 250);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_logs');
    }
};
