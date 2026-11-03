<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
    protected $table = 'supplier_payments';

    protected $fillable = [
        'supplier_id',
        'receipt_id',
        'payment_code',
        'payment_date',
        'amount',
        'payment_method',
        'note',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'double',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(InventoryReceipt::class, 'receipt_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Tự động sinh mã phiếu chi thanh toán NCC (ví dụ: PCNCC00001)
     */
    public static function generateNextCode(): string
    {
        $last = self::where('payment_code', 'like', 'PC%')
            ->orderByRaw('LENGTH(payment_code) DESC')
            ->orderBy('payment_code', 'desc')
            ->value('payment_code');

        if (!$last) {
            return 'PC00001';
        }

        if (preg_match('/^PC(\d+)$/i', $last, $matches)) {
            $num = intval($matches[1]) + 1;
            return 'PC' . str_pad($num, max(5, strlen($matches[1])), '0', STR_PAD_LEFT);
        }

        return 'PC' . str_pad(self::count() + 1, 5, '0', STR_PAD_LEFT);
    }

    protected static function booted()
    {
        static::saved(function ($payment) {
            $payment->supplier?->recalculateDebt();
        });

        static::deleted(function ($payment) {
            $payment->supplier?->recalculateDebt();
        });
    }
}

