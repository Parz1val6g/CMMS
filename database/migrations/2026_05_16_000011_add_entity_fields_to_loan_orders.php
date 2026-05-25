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
        // Remove entity-linked loans — they have no client_id and cannot exist after rollback
        DB::table('loan_orders')->whereNull('client_id')->delete();

        // Drop FK constraints only if they exist (MySQL doesn't support DROP FOREIGN KEY IF EXISTS)
        $dbName = DB::connection()->getDatabaseName();
        $existingFks = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', $dbName)
            ->where('TABLE_NAME', 'loan_orders')
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->whereIn('CONSTRAINT_NAME', [
                'loan_orders_entity_id_foreign',
                'loan_orders_delivery_location_id_foreign',
                'loan_orders_approved_by_foreign',
            ])
            ->pluck('CONSTRAINT_NAME');

        foreach ($existingFks as $fk) {
            DB::statement("ALTER TABLE loan_orders DROP FOREIGN KEY `{$fk}`");
        }

        Schema::table('loan_orders', function (Blueprint $table) {
            $existing = Schema::getColumnListing('loan_orders');

            $drops = array_filter(
                ['entity_id', 'delivery_location_id', 'approved_by', 'approved_at', 'notes_cancel'],
                fn($col) => in_array($col, $existing)
            );

            if ($drops) {
                $table->dropColumn(array_values($drops));
            }

            $table->foreignUuid('client_id')->nullable(false)->change();
        });
    }
};
