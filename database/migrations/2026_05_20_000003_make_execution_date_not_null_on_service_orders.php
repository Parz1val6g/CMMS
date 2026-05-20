<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE service_orders SET execution_date = COALESCE(updated_at, CURRENT_TIMESTAMP) WHERE execution_date IS NULL");

        Schema::table('service_orders', function (Blueprint $table) {
            $table->date('execution_date')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->date('execution_date')->nullable()->change();
        });
    }
};
