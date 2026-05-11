<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Create the many-to-many pivot table
        Schema::create('equipment_service_order', function (Blueprint $table) {
            $table->uuid('equipment_id');
            $table->uuid('service_order_id');
            $table->timestamps();

            $table->foreign('equipment_id')->references('id')->on('equipments')->cascadeOnDelete();
            $table->foreign('service_order_id')->references('id')->on('service_orders')->cascadeOnDelete();
            $table->primary(['equipment_id', 'service_order_id']);
        });

        // 2. Migrate existing data: each SO with equipment_id gets a pivot row
        $serviceOrders = DB::table('service_orders')
            ->whereNotNull('equipment_id')
            ->get(['id', 'equipment_id']);

        foreach ($serviceOrders as $so) {
            DB::table('equipment_service_order')->insert([
                'equipment_id'     => $so->equipment_id,
                'service_order_id' => $so->id,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        // 3. Drop the old FK constraint and column
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropForeign(['equipment_id']);
            $table->dropColumn('equipment_id');
        });
    }

    public function down(): void
    {
        // 1. Re-add equipment_id column
        Schema::table('service_orders', function (Blueprint $table) {
            $table->uuid('equipment_id')->nullable()->index()->after('service_type_id');
            $table->foreign('equipment_id')->references('id')->on('equipments')->cascadeOnDelete();
        });

        // 2. Restore data from pivot (take first equipment if multiple)
        $pivotRows = DB::table('equipment_service_order')
            ->orderBy('created_at')
            ->get()
            ->groupBy('service_order_id');

        foreach ($pivotRows as $soId => $rows) {
            $first = $rows->first();
            DB::table('service_orders')
                ->where('id', $soId)
                ->update(['equipment_id' => $first->equipment_id]);
        }

        // 3. Drop pivot table
        Schema::dropIfExists('equipment_service_order');
    }
};
