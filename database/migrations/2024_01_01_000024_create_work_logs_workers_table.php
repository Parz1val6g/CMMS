<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_logs_workers', function (Blueprint $table) {
            $table->foreignUuid('work_log_id')->constrained('work_logs')->cascadeOnDelete();
            $table->foreignUuid('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->primary(['work_log_id', 'worker_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_logs_workers');
    }
};
