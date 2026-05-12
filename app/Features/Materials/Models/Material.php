<?php
namespace App\Features\Materials\Models;
use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;

use App\Features\MiniTasks\Models\MiniTask;
use App\Features\WorkLogs\Models\WorkLog;
use App\Shared\Models\Unit;

class Material extends Model
{
    use Base;
    protected $fillable = [
        'name',
        'unit_id',
        'stock_quantity',
    ];
    protected $casts = [
        'stock_quantity' => 'decimal:2',
    ];
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    // Materials planned for a mini-task
    public function plannedForMiniTasks()
    {
        return $this->belongsToMany(MiniTask::class, 'mini_tasks_materials', 'material_id', 'mini_task_id')
            ->withPivot('planned_quantity');
    }
    // Materials actually used in a work log
    public function usedInWorkLogs()
    {
        return $this->belongsToMany(WorkLog::class, 'work_logs_materials', 'material_id', 'work_log_id')
            ->withPivot('quantity_used', 'unit_price_at_use');
    }
}