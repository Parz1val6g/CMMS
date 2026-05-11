<?php
namespace App\Features\Tasks\Models;
use App\Core\Enums\TaskStatus;
use App\Core\Traits\Base;
use App\Core\Traits\HasAutoReference;
use Illuminate\Database\Eloquent\Model;

use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Shared\Models\User;
use App\Features\Sectors\Models\Sector;
use App\Features\MiniTasks\Models\MiniTask;

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
        'manager_id',
        'name',
        'description',
        'status',
    ];
    protected $casts = [
        'status' => TaskStatus::class,
    ];
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
}