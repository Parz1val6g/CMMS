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
            $table->uuid('equipment_id')->nullable();
            $table->string('file_path', 250);
            $table->string('file_name', 250);
            $table->string('mime_type', 50)->nullable();
            $table->string('attachable_type', 255)->nullable();
            $table->uuid('attachable_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('equipment_id');
            $table->index(['attachable_type', 'attachable_id']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE attachments ADD CONSTRAINT attachments_attachable_check CHECK (
                (attachable_type IS NOT NULL AND attachable_id IS NOT NULL) OR
                (attachable_type IS NULL AND attachable_id IS NULL)
            )");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
