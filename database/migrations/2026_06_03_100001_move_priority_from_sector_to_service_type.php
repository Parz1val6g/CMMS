<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_order_sector_service_type', function (Blueprint $table) {
            $table->string('priority')->nullable()->after('service_type_id');
        });

        Schema::table('service_order_sector', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }

    public function down(): void
    {
        Schema::table('service_order_sector', function (Blueprint $table) {
            $table->string('priority')->nullable()->after('sector_id');
        });

        Schema::table('service_order_sector_service_type', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
