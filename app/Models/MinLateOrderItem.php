<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MinLateOrderItem extends Model
{
    protected $table = 'min_late_order_items';

    protected $fillable = [
        'order_supply_id',
        'product_code',
        'name',
        'size',
        'quantity',
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
        'status',
        'notes',
    ];

    protected $casts = [
        'size' => 'array',
        'edge_gluing' => 'array',
        'status' => 'array',
    ];

    public function orderSupply()
    {
        return $this->belongsTo(OrderSupply::class, 'order_supply_id');
    }
}
