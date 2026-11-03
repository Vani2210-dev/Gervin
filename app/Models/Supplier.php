<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'address',
        'tax_code',
        'contact_person',
        'initial_debt',
        'debt',
        'notes',
        'status',
    ];

    protected $casts = [
        'initial_debt' => 'double',
        'debt'         => 'double',
    ];

    public function receipts(): HasMany
    {
        return $this->hasMany(InventoryReceipt::class, 'supplier_id')->orderBy('date', 'desc');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class, 'supplier_id')->orderBy('payment_date', 'desc');
    }

    /**
     * Tự động sinh mã NCC tiếp theo (ví dụ: NCC00001, NCC00002)
     */
    public static function generateNextCode(): string
    {
        $last = self::where('code', 'like', 'NCC%')
            ->orderByRaw('LENGTH(code) DESC')
            ->orderBy('code', 'desc')
            ->value('code');

        if (!$last) {
            return 'NCC00001';
        }

        if (preg_match('/^NCC(\d+)$/i', $last, $matches)) {
            $num = intval($matches[1]) + 1;
            return 'NCC' . str_pad($num, max(5, strlen($matches[1])), '0', STR_PAD_LEFT);
        }

        return 'NCC' . str_pad(self::count() + 1, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Tính toán và đồng bộ lại công nợ chính xác của NCC:
     * Công nợ = Nợ ban đầu + Tổng tiền các phiếu nhập kho (trừ cancelled) - Tổng tiền các phiếu chi đã thanh toán
     */
    public function recalculateDebt(): float
    {
        $totalReceipts = (float) $this->receipts()
            ->whereNotIn('status', ['cancelled'])
            ->sum('total_amount');

        $totalPayments = (float) $this->payments()->sum('amount');

        $this->debt = (float) ($this->initial_debt ?? 0) + $totalReceipts - $totalPayments;
        $this->saveQuietly();

        return $this->debt;
    }
}

