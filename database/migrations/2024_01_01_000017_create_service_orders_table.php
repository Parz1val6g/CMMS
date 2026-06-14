<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('process', 250);
            $table->string('title');
            // category_id: FK added later in 2026_06_03_500001 (service_order_categories created after this migration)
            $table->uuid('category_id');
            $table->foreignUuid('client_id')->nullable()->constrained('clients')->cascadeOnDelete();
            // client_location_id: FK added later in 2026_06_03_500001 (client_locations created after this migration)
            $table->uuid('client_location_id')->nullable();
            $table->foreignUuid('manager_id')->constrained('users')->cascadeOnDelete();
            // created_by: users table exists before this migration (000003 < 000017)
            $table->uuid('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            // migrated_to_loan_id: FK added later in 2026_06_03_500001 (loan_orders created after this migration)
            $table->uuid('migrated_to_loan_id')->nullable();
            $table->foreignUuid('location_id')->nullable()->constrained('locations')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamp('start_notified_at')->nullable();
            $table->string('status', 50);
            $table->string('priority', 20)->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->text('description');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('priority');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
            $table->index(['status', 'priority', 'created_at'], 'so_status_priority_created_idx');
            $table->index(['manager_id', 'status'], 'so_manager_status_idx');
            $table->index(['client_id', 'status'], 'so_client_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
