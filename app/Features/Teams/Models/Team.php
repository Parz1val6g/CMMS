<?php

namespace App\Features\Teams\Models;

use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Features\Sectors\Models\Sector;
use App\Features\Workers\Models\Worker;
use App\Features\Tasks\Models\Task;
use App\Features\MiniTasks\Models\MiniTask;
use App\Shared\Models\User;

class Team extends Model
{
    use Base;

    protected $fillable = [
        'sector_id',
        'name',
        'responsible_id',
    ];

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function workers()
    {
        return $this->hasMany(Worker::class);
    }

    public function miniTasks()
    {
        return $this->belongsToMany(MiniTask::class, 'mini_tasks_workers_teams', 'team_id', 'mini_task_id');
    }
}