<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'order_code',
        'customer_id',
        'customer_name',
        'phone',
        'address',
        'deadline',
        'notes',
        'total_amount',
        'status',
        'attachments',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(AcrylicOrderItem::class, 'order_id');
    }

    public function supplies()
    {
        return $this->hasMany(OrderSupply::class, 'order_id');
    }
}
