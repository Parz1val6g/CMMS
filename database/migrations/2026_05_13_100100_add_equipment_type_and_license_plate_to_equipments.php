<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            $table->foreignUuid('equipment_type_id')
                ->nullable()
                ->constrained('equipment_types')
                ->nullOnDelete();
            $table->string('license_plate', 20)->nullable()->after('serial_number');
        });
    }

    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            $table->dropForeign(['equipment_type_id']);
            $table->dropColumn(['equipment_type_id', 'license_plate']);
        });
    }
};
