<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')->on('service_order_categories')->restrictOnDelete();
            $table->foreign('client_location_id')
                ->references('id')->on('client_locations')->nullOnDelete();
            $table->foreign('migrated_to_loan_id')
                ->references('id')->on('loan_orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['client_location_id']);
            $table->dropForeign(['migrated_to_loan_id']);
        });
    }
};
