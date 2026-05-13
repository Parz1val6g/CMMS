<?php

namespace App\Features\Workers\Observers;

use App\Features\Workers\Models\Worker;
use App\Shared\Models\CostHistory;
use Illuminate\Support\Facades\Auth;

class WorkerObserver
{
    public function updated(Worker $worker): void
    {
        if (!$worker->wasChanged('cost_per_hour')) {
            return;
        }

        $oldValue = $worker->getOriginal('cost_per_hour');

        // Close the previous active record
        CostHistory::where('entity_type', Worker::class)
            ->where('entity_id', $worker->getKey())
            ->whereNull('effective_until')
            ->update(['effective_until' => now()]);

        // Create the new record
        CostHistory::create([
            'entity_type' => Worker::class,
            'entity_id' => $worker->getKey(),
            'cost_per_hour' => $worker->cost_per_hour,
            'changed_by' => Auth::id(),
            'effective_from' => now(),
            'effective_until' => null,
        ]);
    }
}