<?php

namespace App\Features\ServiceOrderCategories\Models;

use App\Core\Traits\Base;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceOrderCategory extends Model
{
    use Base, SoftDeletes;

    protected $fillable = ['name', 'description'];
}
