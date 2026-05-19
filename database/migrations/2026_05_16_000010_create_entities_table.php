<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('entities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type', 30);
            $table->string('nif', 20)->unique()->nullable();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->foreignUuid('location_id')->nullable()->constrained('parishes')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('entity_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};
