<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_logs_materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('work_log_id')->constrained('work_logs')->cascadeOnDelete();
            $table->foreignUuid('material_id')->constrained('materials')->cascadeOnDelete();
            $table->decimal('unit_price_at_use', 10, 2)->nullable();
            $table->decimal('quantity_used', 10, 2);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['work_log_id', 'material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_logs_materials');
    }
};
