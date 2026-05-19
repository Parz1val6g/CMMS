<?php

namespace App\Features\MiniTasks\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MiniTaskMaterial extends Pivot
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'mini_tasks_materials';

    protected $fillable = [
        'id',
        'mini_task_id',
        'material_id',
        'planned_quantity',
    ];
}
