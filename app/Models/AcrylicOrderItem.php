<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcrylicOrderItem extends Model
{
    protected $appends = ['product_code'];

    protected $fillable = [
        'order_supply_id',
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

    protected $casts = [
    ];

    public function orderSupply()
    {
        return $this->belongsTo(OrderSupply::class, 'order_supply_id');
    }

    public function codes()
    {
        return $this->hasMany(AcrylicOrderItemCode::class, 'acrylic_order_item_id');
    }

    public function getProductCodeAttribute()
    {
        $firstCode = $this->codes()->first();
        if ($firstCode) {
            $pid = $firstCode->product_id;
            $pos = strrpos($pid, '.');
            if ($pos !== false) {
                return substr($pid, 0, $pos);
            }
            return $pid;
        }
        return '—';
    }
}
