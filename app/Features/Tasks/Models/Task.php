<?php
namespace App\Features\Tasks\Models;
use App\Core\Enums\Priority;
use App\Core\Enums\TaskStatus;
use App\Core\Traits\Base;
use App\Core\Traits\HasAutoReference;
use Illuminate\Database\Eloquent\Model;

use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Shared\Models\User;
use App\Features\Sectors\Models\Sector;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\Tasks\Models\TaskRejection;

class Task extends Model
{
    use Base, HasAutoReference;

    protected function referenceInitials(): string
    {
        return 'TK';
    }
    protected $fillable = [
        'reference',
        'service_order_id',
        'taskable_id',
        'taskable_type',
        'manager_id',
        'description',
        'status',
        'priority',
        'start_date',
        'end_date',
    ];
    protected $casts = [
        'priority' => Priority::class,
        'status' => TaskStatus::class,
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Polymorphic parent — returns either ServiceOrder or LoanOrder.
     */
    public function taskable()
    {
        return $this->morphTo();
    }

    /**
     * Backward-compatible relationship.
     * Returns null for tasks attached to LoanOrders (taskable_type = LoanOrder).
     * Use $task->taskable instead for polymorphic access.
     */
    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // The sectors responsible for this task
    public function sectors()
    {
        return $this->belongsToMany(Sector::class, 'tasks_sectors', 'task_id', 'sector_id');
    }

    public function miniTasks()
    {
        return $this->hasMany(MiniTask::class);
    }

    public function rejections()
    {
        return $this->hasMany(TaskRejection::class);
    }
}