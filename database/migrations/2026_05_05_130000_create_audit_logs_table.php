<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('auditable');        // polymorphic: model type + id
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');                     // created, updated, deleted, status_changed, role_assigned, login
            $table->json('old_values')->nullable();      // snapshot before change
            $table->json('new_values')->nullable();      // snapshot after change
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id']);
            $table->index(['event']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
