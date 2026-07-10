<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    protected $table = 'customer_payments';

    protected static function booted()
    {
        static::created(function ($payment) {
            if ($payment->customer) {
                $payment->customer->decrement('debt', $payment->amount);
            }
        });

        static::updating(function ($payment) {
            $oldAmount = $payment->getOriginal('amount') ?? 0;
            $newAmount = $payment->amount;
            $oldCustomerId = $payment->getOriginal('customer_id');
            $newCustomerId = $payment->customer_id;

            if ($oldCustomerId != $newCustomerId) {
                if ($oldCustomerId) {
                    $oldCustomer = Customer::find($oldCustomerId);
                    if ($oldCustomer) {
                        $oldCustomer->increment('debt', $oldAmount);
                    }
                }
                if ($newCustomerId) {
                    $newCustomer = Customer::find($newCustomerId);
                    if ($newCustomer) {
                        $newCustomer->decrement('debt', $newAmount);
                    }
                }
            } else {
                if ($payment->customer) {
                    $diff = $newAmount - $oldAmount;
                    if ($diff != 0) {
                        $payment->customer->decrement('debt', $diff);
                    }
                }
            }
        });

        static::deleted(function ($payment) {
            if ($payment->customer) {
                $payment->customer->increment('debt', $payment->amount);
            }
        });
    }

    protected $fillable = [
        'customer_id',
        'order_id',
        'payment_date',
        'amount',
        'payment_method',
        'note',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Labels cho hình thức thanh toán
    public static function methodLabel(string $method): string
    {
        return match($method) {
            'cash'     => 'Tiền mặt',
            'transfer' => 'Chuyển khoản',
            'other'    => 'Khác',
            default    => $method,
        };
    }
}
