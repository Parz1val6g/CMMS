<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Step 1: Add polymorphic columns
        Schema::table('tasks', function (Blueprint $table) {
            $table->uuid('taskable_id')->nullable()->after('service_order_id');
            $table->string('taskable_type', 255)->nullable()->after('taskable_id');
            $table->index(['taskable_id', 'taskable_type']);
        });

        // Data backfill: populate taskable_* for existing tasks that have service_order_id
        DB::table('tasks')
            ->whereNotNull('service_order_id')
            ->update([
                'taskable_id'   => DB::raw('service_order_id'),
                'taskable_type' => \App\Features\ServiceOrders\Models\ServiceOrder::class,
            ]);

        // Step 2: Make service_order_id nullable (polymorphic tasks may belong to LoanOrder)
        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->makeServiceOrderIdNullableSqlite();
        } else {
            Schema::table('tasks', function (Blueprint $table) {
                $table->uuid('service_order_id')->nullable()->change();
            });
        }
    }

    private function makeServiceOrderIdNullableSqlite(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        Schema::create('tasks_v2', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_order_id')->nullable()->constrained('service_orders')->cascadeOnDelete();
            $table->string('reference', 20)->nullable()->unique();
            $table->foreignUuid('manager_id')->constrained('users')->cascadeOnDelete();
            $table->text('description');
            $table->string('status', 50);
            $table->uuid('taskable_id')->nullable();
            $table->string('taskable_type', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['service_order_id', 'status']);
            $table->index(['taskable_id', 'taskable_type']);
        });

        DB::statement('INSERT INTO tasks_v2 SELECT * FROM tasks');
        Schema::drop('tasks');
        Schema::rename('tasks_v2', 'tasks');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // Make service_order_id NOT NULL again for down (same driver check)
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');

            Schema::create('tasks_v2', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('service_order_id')->constrained('service_orders')->cascadeOnDelete();
                $table->string('reference', 20)->nullable()->unique();
                $table->foreignUuid('manager_id')->constrained('users')->cascadeOnDelete();
                $table->text('description');
                $table->string('status', 50);
                $table->timestamps();
                $table->softDeletes();

                $table->index('status');
                $table->index(['service_order_id', 'status']);
            });

            DB::statement('INSERT INTO tasks_v2 (id, service_order_id, reference, manager_id, description, status, created_at, updated_at, deleted_at) SELECT id, service_order_id, reference, manager_id, description, status, created_at, updated_at, deleted_at FROM tasks');
            Schema::drop('tasks');
            Schema::rename('tasks_v2', 'tasks');

            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            Schema::table('tasks', function (Blueprint $table) {
                $table->uuid('service_order_id')->nullable(false)->change();
            });
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropIndex(['taskable_id', 'taskable_type']);
                $table->dropColumn(['taskable_id', 'taskable_type']);
            });
        }
    }
};
