<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cost_histories', function (Blueprint $table) {
            $table->id();
            $table->morphs('entity');
            $table->decimal('cost_per_hour', 10, 2);
            $table->string('changed_by')->nullable();
            $table->timestamp('effective_from')->useCurrent();
            $table->timestamp('effective_until')->nullable();

            // morphs() already creates index on (entity_type, entity_id)
            $table->index('effective_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_histories');
    }
};
