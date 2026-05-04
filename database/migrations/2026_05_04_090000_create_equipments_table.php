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
            $table->string('serial_number', 250)->unique();
            $table->foreignUuid('manager_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 50);
            $table->boolean('is_loanable')->default(true);
            $table->integer('revision_interval_days');
            $table->dateTime('last_revision_date')->nullable();
            $table->dateTime('next_revision_date')->nullable();
            $table->string('description', 250)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('next_revision_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipments');
    }
};
