<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected static function booted()
    {
        static::created(function ($order) {
            $order->adjustCustomerDebtOnCreate();
        });

        static::updated(function ($order) {
            $order->adjustCustomerDebtOnUpdate();
        });

        static::deleted(function ($order) {
            $order->adjustCustomerDebtOnDelete();
        });
    }

    public function adjustCustomerDebtOnCreate()
    {
        if ($this->customer && !in_array($this->status, ['draft', 'pending', 'cancelled'])) {
            $amount = round($this->total_amount, -3);
            $this->customer->increment('debt', $amount);
        }
    }

    public function adjustCustomerDebtOnUpdate()
    {
        $oldStatus = $this->getOriginal('status');
        $newStatus = $this->status;
        $oldAmount = round($this->getOriginal('total_amount') ?? 0, -3);
        $newAmount = round($this->total_amount, -3);
        $oldCustomerId = $this->getOriginal('customer_id');
        $newCustomerId = $this->customer_id;

        $wasValid = !in_array($oldStatus, ['draft', 'pending', 'cancelled']);
        $isValid = !in_array($newStatus, ['draft', 'pending', 'cancelled']);

        // Case 1: Customer changed
        if ($oldCustomerId != $newCustomerId) {
            if ($wasValid && $oldCustomerId) {
                $oldCustomer = Customer::find($oldCustomerId);
                if ($oldCustomer) {
                    $oldCustomer->decrement('debt', $oldAmount);
                }
            }
            if ($isValid && $newCustomerId) {
                $newCustomer = Customer::find($newCustomerId);
                if ($newCustomer) {
                    $newCustomer->increment('debt', $newAmount);
                }
            }
            return;
        }

        // Case 2: Same customer
        if ($this->customer) {
            if ($wasValid && $isValid) {
                // Both valid, adjust difference
                $diff = $newAmount - $oldAmount;
                if ($diff != 0) {
                    $this->customer->increment('debt', $diff);
                }
            } elseif ($wasValid && !$isValid) {
                // Became invalid, subtract old amount
                $this->customer->decrement('debt', $oldAmount);
            } elseif (!$wasValid && $isValid) {
                // Became valid, add new amount
                $this->customer->increment('debt', $newAmount);
            }
        }
    }

    public function adjustCustomerDebtOnDelete()
    {
        if ($this->customer && !in_array($this->status, ['draft', 'pending', 'cancelled'])) {
            $amount = round($this->total_amount, -3);
            $this->customer->decrement('debt', $amount);
        }
    }

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
        return $this->hasMany(CustomerPayment::class, 'order_id')->orderBy('payment_date');
    }
}

