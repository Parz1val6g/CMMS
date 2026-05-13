<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipment_types', function (Blueprint $table) {
            $table->boolean('active')->default(true);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE equipment_types ADD CONSTRAINT equipment_types_category_check CHECK (category IN ('vehicle', 'general'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE equipment_types DROP CONSTRAINT equipment_types_category_check');
        }

        Schema::table('equipment_types', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
