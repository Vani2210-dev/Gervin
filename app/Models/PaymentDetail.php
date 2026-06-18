<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    protected $table = 'payment_details';

    protected $fillable = [
        'order_id',
        'name',
        'unit',
        'quantity',
        'price',
        'price_only',
        'total',
    ];

    protected $casts = [
        'quantity' => 'float',
        'price' => 'float',
        'price_only' => 'float',
        'total' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
