<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->foreignUuid('unit_id')->constrained('units')->cascadeOnDelete();
            $table->decimal('stock_quantity', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('ALTER TABLE materials ADD CONSTRAINT check_stock_qty CHECK (stock_quantity >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
