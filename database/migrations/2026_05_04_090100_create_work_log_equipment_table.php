<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_log_equipment', function (Blueprint $table) {
            $table->foreignUuid('work_log_id')->constrained('work_logs')->cascadeOnDelete();
            $table->foreignUuid('equipment_id')->constrained('equipments')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->primary(['work_log_id', 'equipment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_log_equipment');
    }
};
