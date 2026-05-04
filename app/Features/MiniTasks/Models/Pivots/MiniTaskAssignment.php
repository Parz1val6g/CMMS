<?php

namespace App\Features\MiniTasks\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MiniTaskAssignment extends Pivot
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'mini_tasks_workers_teams';

    protected $fillable = [
        'id',
        'mini_task_id',
        'worker_id',
        'team_id',
    ];
}
