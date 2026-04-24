<?php
namespace App\Features\Materials\Models;
use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;
class Unit extends Model
{
    use Base;
    protected $fillable = [
        'name',
        'abbreviation',
    ];
    public function materials()
    {
        return $this->hasMany(Material::class);
    }
}