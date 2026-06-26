<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlassPrice extends Model
{
    protected $table = 'glass_prices';

    protected $fillable = [
        'category_name',
        'stt',
        'product_name',
        'aluminum_color',
        'glass_color',
        'unit',
        'price',
        'code',
        'notes',
    ];

    protected $casts = [
        'price' => 'float',
    ];
}
