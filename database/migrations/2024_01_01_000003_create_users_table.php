<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name', 250);
            $table->string('last_name', 250);
            $table->string('phone', 14)->unique();
            $table->string('email', 250)->unique();
            $table->string('password', 250)->nullable();
            $table->string('status', 50);
            $table->string('locale', 10)->default('pt');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
