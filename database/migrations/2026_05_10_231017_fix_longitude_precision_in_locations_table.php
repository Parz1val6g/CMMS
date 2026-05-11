<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            // DECIMAL(10,8) only supports up to 99.99999999 — longitude goes to ±180
            $table->decimal('longitude', 11, 8)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->decimal('longitude', 10, 8)->nullable()->change();
        });
    }
};
