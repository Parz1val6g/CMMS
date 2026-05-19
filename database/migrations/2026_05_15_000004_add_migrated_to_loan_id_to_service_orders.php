<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->foreignUuid('migrated_to_loan_id')
                ->nullable()
                ->after('workflow_type')
                ->constrained('loan_orders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropForeign(['migrated_to_loan_id']);
            $table->dropColumn('migrated_to_loan_id');
        });
    }
};
