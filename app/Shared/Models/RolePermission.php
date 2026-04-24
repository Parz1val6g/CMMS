<?php
namespace App\Shared\Models;

use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use Base;

    protected $fillable = [
        'role_id',
        'resource',
        'action',
        'description'
    ];
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}