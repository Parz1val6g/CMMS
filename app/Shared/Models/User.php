<?php

namespace App\Shared\Models;

use App\Core\Traits\Base;
use App\Core\Traits\LogsAuditTrail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Features\Clients\Models\Client;
use App\Features\Workers\Models\Worker;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Tasks\Models\Task;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\Sectors\Models\Sector;

class User extends Authenticatable
{
    use Base, LogsAuditTrail;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'password',
        'status'
    ];

    protected $hidden = [
        'password'
    ];

    public function isAdmin(): bool
    {
        return $this->roles()->where('name', 'admin')->exists();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    /**
     * Returns a query builder over RolePermission scoped to this user's roles.
     * Used by PermissionManager — must remain a Builder so callers can chain
     * additional where() / exists() / get() calls without loading all rows.
     */
    public function rolePermissions(): Builder
    {
        return RolePermission::whereIn(
            'role_id',
            $this->roles()->select('roles.id')
        );
    }

    public function preferences()
    {
        return $this->hasMany(UserPreference::class);
    }

    public function clientProfile()
    {
        return $this->hasOne(Client::class);
    }

    public function worker()
    {
        return $this->hasOne(Worker::class);
    }

    public function workerProfile()
    {
        return $this->worker();
    }

    public function managedServiceOrders()
    {
        return $this->hasMany(ServiceOrder::class, 'manager_id');
    }

    public function managedTasks()
    {
        return $this->hasMany(Task::class, 'manager_id');
    }

    public function managedMiniTasks()
    {
        return $this->hasMany(MiniTask::class, 'supervisor_id');
    }

    public function headedSectors()
    {
        return $this->hasMany(Sector::class, 'head_id');
    }
}
