<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_order_id')->nullable()->constrained('service_orders')->cascadeOnDelete();
            $table->uuid('taskable_id')->nullable();
            $table->string('taskable_type', 255)->nullable();
            $table->string('reference', 20)->nullable()->unique();
            $table->foreignUuid('manager_id')->constrained('users')->cascadeOnDelete();
            $table->text('description');
            $table->string('status', 50);
            $table->string('priority')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['service_order_id', 'status']);
            $table->index(['taskable_id', 'taskable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
