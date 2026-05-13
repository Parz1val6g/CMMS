<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->foreignUuid('responsible_id')->nullable()->after('sector_id');
        });

        $firstUser = DB::table('users')->first();
        if ($firstUser) {
            DB::table('teams')->whereNull('responsible_id')->update([
                'responsible_id' => $firstUser->id,
            ]);
        }

        Schema::table('teams', function (Blueprint $table) {
            $table->foreignUuid('responsible_id')->nullable(false)->change();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->foreign('responsible_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['responsible_id']);
            $table->dropColumn('responsible_id');
        });
    }
};
