<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop pivot table — loan equipment is now tracked via equipment_loan_order
        Schema::dropIfExists('equipment_service_order');

        // Drop workflow_type column — loans are now a separate feature.
        // Must remove the composite index that references it first (SQLite constraint).
        if (Schema::hasColumn('service_orders', 'workflow_type')) {
            Schema::table('service_orders', function (Blueprint $table) {
                $table->dropIndex('so_workflow_status_idx');
                $table->dropColumn('workflow_type');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('service_orders', 'workflow_type')) {
            Schema::table('service_orders', function (Blueprint $table) {
                $table->string('workflow_type', 50)->nullable()->after('service_type_id');
                $table->index(['workflow_type', 'status'], 'so_workflow_status_idx');
            });
        }

        Schema::create('equipment_service_order', function (Blueprint $table) {
            $table->uuid('equipment_id');
            $table->uuid('service_order_id');
            $table->timestamps();

            $table->foreign('equipment_id')->references('id')->on('equipments')->cascadeOnDelete();
            $table->foreign('service_order_id')->references('id')->on('service_orders')->cascadeOnDelete();
            $table->primary(['equipment_id', 'service_order_id']);
        });
    }
};
