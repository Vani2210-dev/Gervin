<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MinLateOrderItem extends Model
{
    protected $table = 'min_late_order_items';

    protected $appends = ['product_code'];

    protected $fillable = [
        'order_supply_id',
        'name',
        'thickness',
        'size',
        'quantity',
        'bevel',
        'edge_gluing',
        'straight_paste_length',
        'beveled_length',
        'vat_moi_length',
        'ban_rong_40_59',
        'ban_rong_17_39',
        'ban_rong_25_35',
        'beveled_handle',
        'cnc',
        'direction',
        'notes',
    ];

    protected $casts = [
        'size' => 'array',
        'edge_gluing' => 'array',
        'straight_paste_length' => 'float',
        'beveled_length' => 'float',
        'vat_moi_length' => 'float',
        'ban_rong_40_59' => 'float',
        'ban_rong_17_39' => 'float',
        'ban_rong_25_35' => 'float',
        'beveled_handle' => 'float',
    ];

    public function orderSupply()
    {
        return $this->belongsTo(OrderSupply::class, 'order_supply_id');
    }

    public function codes()
    {
        return $this->hasMany(MinLateOrderItemCode::class, 'min_late_order_item_id');
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
