<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mini_tasks_workers_teams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mini_task_id')->constrained('mini_tasks')->cascadeOnDelete();
            $table->foreignUuid('worker_id')->nullable()->constrained('workers')->cascadeOnDelete();
            $table->foreignUuid('team_id')->nullable()->constrained('teams')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mini_tasks_workers_teams');
    }
};
