<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            $table->string('status', 20)->default('in_progress')->after('description');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('work_logs', function (Blueprint $table) {
            $table->dropColumn(['status', 'reviewed_by', 'reviewed_at']);
        });
    }
};
