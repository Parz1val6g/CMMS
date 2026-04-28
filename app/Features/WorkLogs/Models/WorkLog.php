<?php
namespace App\Features\WorkLogs\Models;
use App\Core\Enums\WorkLogStatus;
use App\Core\Traits\Base;
use App\Shared\Models\User;
use Illuminate\Database\Eloquent\Model;

use App\Features\MiniTasks\Models\MiniTask;
use App\Features\Workers\Models\Worker;
use App\Features\Materials\Models\Material;

class WorkLog extends Model
{
    use Base;
    protected $fillable = [
        'mini_task_id',
        'started_at',
        'completed_at',
        'description',
        'status',
        'reviewed_by',
        'reviewed_at',
        // 'duration_minutes' is intentionally excluded because MySQL generates it!
    ];
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];
    public function miniTask() { return $this->belongsTo(MiniTask::class); }
    
    public function workers() {
        return $this->belongsToMany(Worker::class, 'work_logs_workers', 'work_log_id', 'worker_id');
    }
    
    public function materials() {
        return $this->belongsToMany(Material::class, 'work_logs_materials', 'work_log_id', 'material_id')
            ->withPivot('quantity_used', 'unit_price_at_use');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}