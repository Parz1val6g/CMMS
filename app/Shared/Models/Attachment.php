<?php
namespace App\Shared\Models;
use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;

use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\MiniTasks\Models\MiniTask;

class Attachment extends Model
{
    use Base;
    protected $fillable = [
        'service_order_id',
        'mini_task_id',
        'file_path',
        'file_name',
        'mime_type',
    ];
    public function serviceOrder()
    {
        return $this->belongsTo(ServiceOrder::class);
    }
    public function miniTask()
    {
        return $this->belongsTo(MiniTask::class);
    }
}