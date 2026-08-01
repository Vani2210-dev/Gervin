<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcrylicOrderItem extends Model
{
    protected $appends = ['product_code'];

    protected $fillable = [
        'order_supply_id',
        'product_name',
        'thickness',
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
        
        'offset_left',
        'offset_right',
        'offset_top',
        'offset_bottom',
        
        'mill_left',
        'mill_right',
        'mill_top',
        'mill_bottom',
        'mill_width',
        'mill_depth',
        
        'mill_left_2',
        'mill_right_2',
        'mill_top_2',
        'mill_bottom_2',
        'mill_width_2',
        'mill_depth_2',
        'old_size',
    ];

    protected $casts = [
        'height' => 'float',
        'width' => 'float',
        'wing_area' => 'float',
        'molding_length' => 'float',
        'unit_price' => 'float',
        'total_price' => 'float',
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
