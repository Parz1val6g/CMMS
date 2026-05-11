<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('numerators', function (Blueprint $table) {
            $table->id();
            $table->string('entity_table', 100);
            $table->year('year');
            $table->integer('current_value')->default(0);
            $table->timestamp('last_generated')->nullable();
            $table->timestamps();

            $table->unique(['entity_table', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numerators');
    }
};
