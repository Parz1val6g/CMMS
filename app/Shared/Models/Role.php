<?php
namespace App\Shared\Models;
use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;
class Role extends Model
{
    use Base;
    protected $fillable = ['name', 'columns'];
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id');
    }
    public function permissions()
    {
        return $this->hasMany(RolePermission::class);
    }
}