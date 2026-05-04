<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            // Drop FK before modifying column
            $table->dropForeign(['location_id']);
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->foreignUuid('location_id')->nullable()->change();
            $table->string('priority', 20)->nullable()->change();
        });

        Schema::table('service_orders', function (Blueprint $table) {
            // Re-add FK — allows NULL (only validates when value is provided)
            $table->foreign('location_id')->references('id')->on('locations')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->foreignUuid('location_id')->nullable(false)->change();
            $table->string('priority', 20)->nullable(false)->change();
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->foreign('location_id')->references('id')->on('locations')->cascadeOnDelete();
        });
    }
};
