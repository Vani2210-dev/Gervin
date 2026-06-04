<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlassOrderItem extends Model
{
    protected $table = 'glass_order_items';

    protected $fillable = [
        'order_supply_id',
        'product_name',
        'product_code',
        'wing_opening_direction',
        'aluminum_color',
        'glass_color',
        'height',
        'width',
        'unit',
        'wing_quantity',
        'area_m2',
        'unit_price',
        'total_price',
        'notes',
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
