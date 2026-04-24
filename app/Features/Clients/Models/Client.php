<?php
namespace App\Features\Clients\Models;
use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;

use App\Shared\Models\User;
use App\Features\ServiceOrders\Models\ServiceOrder;

class Client extends Model
{
    use Base;
    protected $fillable = [
        'user_id',
        'nif',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function serviceOrders()
    {
        return $this->hasMany(ServiceOrder::class);
    }
}