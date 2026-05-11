<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_order_sector', function (Blueprint $table) {
            $table->foreignUuid('service_order_id')->constrained('service_orders')->cascadeOnDelete();
            $table->foreignUuid('sector_id')->constrained('sectors')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['service_order_id', 'sector_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_sector');
    }
};
