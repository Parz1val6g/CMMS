<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parishes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('municipality_id')->constrained('municipalities')->cascadeOnDelete();
            $table->string('name', 50);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parishes');
    }
};
