<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcrylicOrderItem extends Model
{
    protected $fillable = [
        'order_supply_id',
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
        'status',
    ];

    protected $casts = [
        'status' => 'array',
    ];

    public function orderSupply()
    {
        return $this->belongsTo(OrderSupply::class, 'order_supply_id');
    }
}
