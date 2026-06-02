<?php
namespace App\Features\ServiceTypes\Models;
use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;

use App\Features\Sectors\Models\Sector;
class ServiceType extends Model
{
    use Base;
    protected $fillable = [
        'name',
        'description',
        'sector_id',
    ];

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }
}