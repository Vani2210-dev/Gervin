<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'order_code',
        'type',
        'order_date',
        'delivery_days',
        'customer_id',
        'customer_name',
        'phone',
        'address',
        'deadline',
        'notes',
        'customer_policy',
        'discount_percent',
        'discount_amount',
        'vat_percent',
        'vat_amount',
        'total_amount',
        'status',
        'attachments',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'order_date' => 'datetime',
        'delivery_days' => 'float',
        'total_amount' => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasManyThrough(AcrylicOrderItem::class, OrderSupply::class, 'order_id', 'order_supply_id');
    }

    public function minLateItems()
    {
        return $this->hasManyThrough(MinLateOrderItem::class, OrderSupply::class, 'order_id', 'order_supply_id');
    }

    public function glassItems()
    {
        return $this->hasManyThrough(GlassOrderItem::class, OrderSupply::class, 'order_id', 'order_supply_id');
    }

    public function supplies()
    {
        return $this->hasMany(OrderSupply::class, 'order_id');
    }

    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetail::class, 'order_id');
    }

    public function orderPayments()
    {
        return $this->hasMany(OrderPayment::class, 'order_id')->orderBy('payment_date');
    }
}

