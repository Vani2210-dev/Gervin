<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderSupply extends Model
{
    protected $table = 'order_supplies';

    protected $fillable = [
        'order_id',
        'type',
        'supply_name',
        'quantity',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function items()
    {
        return $this->hasMany(AcrylicOrderItem::class, 'order_supply_id');
    }
}
