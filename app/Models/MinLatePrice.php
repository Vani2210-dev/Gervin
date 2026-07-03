<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MinLatePrice extends Model
{
    protected $table = 'minlate_prices';

    protected $fillable = [
        'category_name',
        'stt',
        'product_name',
        'unit',
        'price',
        'code',
        'notes',
    ];

    protected $casts = [
        'price' => 'float',
    ];
}
