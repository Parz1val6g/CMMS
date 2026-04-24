<?php

namespace App\Features\Workers\Models;

use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;

use App\Shared\Models\User;
use App\Features\Teams\Models\Team;
use App\Features\WorkLogs\Models\WorkLog;
use App\Features\MiniTasks\Models\MiniTask;

class Worker extends Model
{
    use Base;

    protected $fillable = [
        'user_id',
        'team_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function workLogs()
    {
        return $this->belongsToMany(WorkLog::class, 'work_logs_workers', 'worker_id', 'work_log_id');
    }

    public function miniTasks()
    {
        return $this->belongsToMany(MiniTask::class, 'mini_tasks_workers_teams', 'worker_id', 'mini_task_id');
    }
}