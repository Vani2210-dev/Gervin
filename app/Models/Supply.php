<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    protected $table = 'supplies';

    protected $fillable = [
        'product_code',
        'name',
        'category',
        'unit',
        'grain_direction',
        'width_mm',
        'height_mm',
        'unit_price',
    ];
}
