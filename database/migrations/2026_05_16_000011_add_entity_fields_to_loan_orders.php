<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loan_orders', function (Blueprint $table) {
            // Make client_id nullable for entity-linked loans
            $table->foreignUuid('client_id')->nullable()->change();

            $table->foreignUuid('entity_id')
                ->nullable()
                ->constrained('entities')
                ->nullOnDelete()
                ->after('client_id');

            $table->foreignUuid('delivery_location_id')
                ->nullable()
                ->constrained('parishes')
                ->nullOnDelete()
                ->after('location_id');

            $table->foreignUuid('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('manager_id');

            $table->timestamp('approved_at')->nullable()->after('checked_out_at');

            $table->text('notes_cancel')->nullable()->after('notes_return');
        });
    }

    public function down(): void
    {
        Schema::table('loan_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('entity_id');
            $table->dropConstrainedForeignId('delivery_location_id');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['approved_at', 'notes_cancel']);
            $table->foreignUuid('client_id')->nullable(false)->change();
        });
    }
};
