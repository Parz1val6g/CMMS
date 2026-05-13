<?php

namespace App\Shared\Models;

use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

use App\Features\Equipments\Models\Equipment;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\ServiceOrders\Models\ServiceOrder;

class Attachment extends Model
{
    use Base;

    protected $fillable = [
        'equipment_id',
        'attachable_type',
        'attachable_id',
        'file_path',
        'file_name',
        'mime_type',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'attachable_id');
    }

    public function miniTask(): BelongsTo
    {
        return $this->belongsTo(MiniTask::class, 'attachable_id');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}