<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tasks_sectors', function (Blueprint $table) {
            $table->foreignUuid('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignUuid('sector_id')->constrained('sectors')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->primary(['task_id', 'sector_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks_sectors');
    }
};
