<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add start_date (initially nullable)
        Schema::table('service_orders', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('execution_date');
        });

        // 2. Fill start_date with created_at for historical records
        DB::statement('UPDATE service_orders SET start_date = created_at WHERE start_date IS NULL');

        // 3. Make start_date NOT NULL
        Schema::table('service_orders', function (Blueprint $table) {
            $table->date('start_date')->nullable(false)->change();
        });

        // 4. Rename execution_date to end_date
        Schema::table('service_orders', function (Blueprint $table) {
            $table->renameColumn('execution_date', 'end_date');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->renameColumn('end_date', 'execution_date');
            $table->dropColumn('start_date');
        });
    }
};
