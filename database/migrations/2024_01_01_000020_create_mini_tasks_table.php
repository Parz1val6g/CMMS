<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mini_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignUuid('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->string('reference', 20)->nullable()->unique();
            $table->string('description', 250);
            $table->string('status', 50);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['task_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mini_tasks');
    }
};
