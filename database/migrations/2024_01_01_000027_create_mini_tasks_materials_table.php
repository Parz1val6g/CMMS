<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mini_tasks_materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mini_task_id')->constrained('mini_tasks')->cascadeOnDelete();
            $table->foreignUuid('material_id')->constrained('materials')->cascadeOnDelete();
            $table->decimal('planned_quantity', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['mini_task_id', 'material_id']);

            $table->index('mini_task_id');
            $table->index('material_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mini_tasks_materials');
    }
};
