<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mini_task_equipment', function (Blueprint $table) {
            $table->uuid('mini_task_id');
            $table->uuid('equipment_id');
            $table->timestamps();

            $table->foreign('mini_task_id')->references('id')->on('mini_tasks')->cascadeOnDelete();
            $table->foreign('equipment_id')->references('id')->on('equipments')->cascadeOnDelete();
            $table->primary(['mini_task_id', 'equipment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mini_task_equipment');
    }
};
