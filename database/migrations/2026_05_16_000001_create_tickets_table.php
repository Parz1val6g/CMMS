<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('description');
            $table->foreignUuid('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignUuid('service_type_id')->nullable()->constrained('service_types')->nullOnDelete();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['open', 'in_progress', 'converted', 'cancelled'])->default('open');
            $table->foreignUuid('ticket_manager_id')->constrained('users')->cascadeOnDelete();
            $table->char('location_id', 36)->nullable();
            $table->foreign('location_id')->references('id')->on('locations')->nullOnDelete();
            $table->foreignUuid('service_order_id')->nullable()->constrained('service_orders')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
