<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('equipments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 200);
            $table->string('brand', 150)->nullable();
            $table->string('model', 150)->nullable();
            $table->string('serial_number', 250)->nullable()->unique();
            $table->foreignUuid('manager_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 50);
            $table->uuid('equipment_type_id')->nullable();
            $table->string('license_plate', 20)->nullable()->unique();
            $table->string('internal_reference', 100)->nullable()->unique();
            $table->integer('manufacturing_year')->nullable();
            $table->date('inspection_date')->nullable();
            $table->uuid('counting_type_id')->nullable();
            $table->boolean('is_loanable')->default(true);
            $table->integer('revision_interval')->nullable();
            $table->dateTime('last_revision_date')->nullable();
            $table->dateTime('next_revision_date')->nullable();
            $table->string('description', 250)->nullable();
            $table->decimal('cost_per_hour', 8, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('next_revision_date');
            $table->index('equipment_type_id');
            $table->index('counting_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipments');
    }
};
