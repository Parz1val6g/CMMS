<?php

namespace App\Features\Equipments\Observers;

use App\Features\Equipments\Models\Equipment;
use App\Shared\Models\CostHistory;
use Illuminate\Support\Facades\Auth;

class EquipmentObserver
{
    public function updated(Equipment $equipment): void
    {
        if (!$equipment->wasChanged('cost_per_hour')) {
            return;
        }

        $oldValue = $equipment->getOriginal('cost_per_hour');

        // Close the previous active record
        CostHistory::where('entity_type', Equipment::class)
            ->where('entity_id', $equipment->getKey())
            ->whereNull('effective_until')
            ->update(['effective_until' => now()]);

        // Create the new record
        CostHistory::create([
            'entity_type' => Equipment::class,
            'entity_id' => $equipment->getKey(),
            'cost_per_hour' => $equipment->cost_per_hour,
            'changed_by' => Auth::id(),
            'effective_from' => now(),
            'effective_until' => null,
        ]);
    }
}