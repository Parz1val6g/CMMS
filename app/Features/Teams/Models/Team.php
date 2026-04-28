<?php

namespace App\Features\Teams\Models;

use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;

use App\Features\Sectors\Models\Sector;
use App\Features\Workers\Models\Worker;
use App\Features\Tasks\Models\Task;
use App\Features\MiniTasks\Models\MiniTask;

class Team extends Model
{
    use Base;

    protected $fillable = [
        'sector_id',
        'name',
    ];

    public function sector()
    {
        return $this->belongsTo(Sector::class);
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