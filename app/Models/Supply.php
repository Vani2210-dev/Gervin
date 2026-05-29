<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    protected $table = 'supplies';

    protected $fillable = [
        'name',
        'category',
        'unit',
        'stock_quantity',
        'min_stock',
        'unit_price',
    ];
}
