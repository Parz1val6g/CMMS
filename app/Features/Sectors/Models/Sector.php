<?php

namespace App\Features\Sectors\Models;

use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;

use App\Shared\Models\User;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Features\Teams\Models\Team;
use App\Features\Tasks\Models\Task;

class Sector extends Model
{
    use Base;

    protected $fillable = [
        'name',
        'head_id',
    ];

    public function head(){
        return $this->belongsTo(User::class, 'head_id');
    }

    public function teams(){
        return $this->hasMany(Team::class);
    }

    public function serviceTypes()
    {
        return $this->hasMany(ServiceType::class);
    }

    public function tasks() {
        return $this->belongsToMany(Task::class, 'tasks_sectors', 'sector_id', 'task_id');
    }
}