<?php
namespace App\Features\MiniTasks\Models;
use App\Core\Enums\MiniTaskStatus;
use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;

use App\Features\MiniTasks\Models\Pivots\MiniTaskAssignment;
use App\Features\Tasks\Models\Task;
use App\Shared\Models\User;
use App\Features\Workers\Models\Worker;
use App\Features\Teams\Models\Team;
use App\Features\Materials\Models\Material;
use App\Features\WorkLogs\Models\WorkLog;
use App\Shared\Models\Attachment;

class MiniTask extends Model
{
    use Base;
    protected $fillable = [
        'task_id',
        'supervisor_id',
        'description',
        'status',
    ];
    protected $casts = [
        'status' => MiniTaskStatus::class,
    ];
    public function task() { return $this->belongsTo(Task::class); }
    public function supervisor() { return $this->belongsTo(User::class, 'supervisor_id'); }
    
    public function workers() {
        return $this->belongsToMany(Worker::class, 'mini_tasks_workers_teams', 'mini_task_id', 'worker_id')
            ->using(MiniTaskAssignment::class)
            ->withTimestamps();
    }
    public function teams() {
        return $this->belongsToMany(Team::class, 'mini_tasks_workers_teams', 'mini_task_id', 'team_id')
            ->using(MiniTaskAssignment::class)
            ->withTimestamps();
    }
    
    public function materials() {
        return $this->belongsToMany(Material::class, 'mini_tasks_materials', 'mini_task_id', 'material_id')
            ->withPivot('planned_quantity');
    }
    
    public function workLogs() { return $this->hasMany(WorkLog::class); }
    public function attachments() { return $this->hasMany(Attachment::class); }
}