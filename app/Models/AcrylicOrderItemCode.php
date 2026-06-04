<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcrylicOrderItemCode extends Model
{
    protected $table = 'acrylic_order_item_codes';

    protected $fillable = [
        'acrylic_order_item_id',
        'product_id',
        'status',
    ];

    protected $casts = [
        'status' => 'array',
    ];

    public function acrylicOrderItem()
    {
        return $this->belongsTo(AcrylicOrderItem::class, 'acrylic_order_item_id');
    }
}
