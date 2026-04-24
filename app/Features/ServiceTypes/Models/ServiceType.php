<?php
namespace App\Features\ServiceTypes\Models;
use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;

use App\Features\ServiceOrders\Models\ServiceOrder;
class ServiceType extends Model
{
    use Base;
    protected $fillable = [
        'name',
        'description',
    ];
    public function serviceOrders()
    {
        return $this->hasMany(ServiceOrder::class);
    }
}