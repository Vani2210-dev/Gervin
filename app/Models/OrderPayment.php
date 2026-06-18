<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    protected $table = 'order_payments';

    protected $fillable = [
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
