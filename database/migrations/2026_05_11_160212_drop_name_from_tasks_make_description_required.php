<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fill nulls before making not-null
        DB::table('tasks')->whereNull('description')->update(['description' => '']);

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->text('description')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('name', 150)->after('manager_id')->default('');
            $table->text('description')->nullable()->change();
        });
    }
};
