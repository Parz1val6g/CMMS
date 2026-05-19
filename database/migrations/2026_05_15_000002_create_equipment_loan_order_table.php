<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipment_loan_order', function (Blueprint $table) {
            $table->foreignUuid('equipment_id')->constrained('equipments')->cascadeOnDelete();
            $table->foreignUuid('loan_order_id')->constrained('loan_orders')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['equipment_id', 'loan_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_loan_order');
    }
};
