<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->index(['status', 'priority', 'created_at'], 'so_status_priority_created_idx');
            $table->index(['manager_id', 'status'], 'so_manager_status_idx');
            $table->index(['client_id', 'status'], 'so_client_status_idx');
            $table->index(['workflow_type', 'status'], 'so_workflow_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropIndex('so_status_priority_created_idx');
            $table->dropIndex('so_manager_status_idx');
            $table->dropIndex('so_client_status_idx');
            $table->dropIndex('so_workflow_status_idx');
        });
    }
};
