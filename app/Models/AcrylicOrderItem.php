<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcrylicOrderItem extends Model
{
    protected $fillable = [
        'acrylic_order_id',
        'product_code',
        'product_name',
        'height',
        'width',
        'grain_direction',
        'edge_bevel',
        'wing_area',
        'molding_length',
        'quantity',
        'unit_price',
        'total_price',
        'notes',
        'bevel',
        'vertical_grain_cnc',
    ];

    public function acrylicOrder()
    {
        return $this->belongsTo(AcrylicOrder::class);
    }
}
