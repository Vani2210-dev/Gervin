<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerDailyDebt extends Model
{
    protected $table = 'customer_daily_debts';

    protected $fillable = [
        'customer_id',
        'date',
        'opening_debt',
        'order_amount',
        'paid_amount',
        'closing_debt',
        'orders_count',
        'payments_count',
        'notes',
    ];

    protected $casts = [
        'date'           => 'date:Y-m-d',
        'opening_debt'   => 'float',
        'order_amount'   => 'float',
        'paid_amount'    => 'float',
        'closing_debt'   => 'float',
        'orders_count'   => 'integer',
        'payments_count' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}

