<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('reference', 20)->nullable()->unique()->after('service_order_id');
        });

        Schema::table('mini_tasks', function (Blueprint $table) {
            $table->string('reference', 20)->nullable()->unique()->after('task_id');
        });

        Schema::table('work_logs', function (Blueprint $table) {
            $table->string('reference', 20)->nullable()->unique()->after('mini_task_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('reference');
        });

        Schema::table('mini_tasks', function (Blueprint $table) {
            $table->dropColumn('reference');
        });

        Schema::table('work_logs', function (Blueprint $table) {
            $table->dropColumn('reference');
        });
    }
};
