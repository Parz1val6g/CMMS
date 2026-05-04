<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_order_id')->nullable()->constrained('service_orders')->cascadeOnDelete();
            $table->foreignUuid('mini_task_id')->nullable()->constrained('mini_tasks')->cascadeOnDelete();
            $table->string('file_path', 250);
            $table->string('file_name', 250);
            $table->string('mime_type', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('service_order_id');
            $table->index('mini_task_id');
        });

        DB::statement('ALTER TABLE attachments ADD CONSTRAINT check_attachment_entity CHECK ((service_order_id IS NOT NULL AND mini_task_id IS NULL) OR (service_order_id IS NULL AND mini_task_id IS NOT NULL))');
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
