<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->foreignUuid('sector_id')->nullable()->after('id')->constrained('sectors')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropColumn('sector_id');
        });
    }
};
