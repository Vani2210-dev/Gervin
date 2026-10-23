<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_code',
        'name',
        'phone',
        'address',
        'latitude',
        'longitude',
        'province',
        'ward',
        'status',
        'partner_competitors',
        'feedback',
        'personality',
        'workshop_scale',
        'customer_proposal',
        'sale_proposal',
        'debt',
        'debt_limit',
        'policy',
        'photos',
        'market_group_id',
    ];

    protected $casts = [
        'photos' => 'array',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function marketGroup()
    {
        return $this->belongsTo(MarketGroup::class, 'market_group_id');
    }

    public function isAccessibleBy($user)
    {
        if (!$user) {
            return false;
        }
        if ($user->canViewAllMarketGroups()) {
            return true;
        }
        if (in_array(strtoupper($this->customer_code ?? ''), ['NK', 'NHAPKHO'], true) || in_array(mb_strtolower(trim($this->name ?? '')), ['nhập kho', 'nhap kho'], true)) {
            return true;
        }
        if (!$this->market_group_id) {
            return false;
        }
        return $user->marketGroups()->where('market_groups.id', $this->market_group_id)->exists();
    }

    public static function getNhapKhoCustomer(): ?self
    {
        try {
            $customer = static::whereIn('customer_code', ['NK', 'NHAPKHO'])
                ->orWhereRaw('LOWER(TRIM(name)) = ?', ['nhập kho'])
                ->orWhereRaw('LOWER(TRIM(name)) = ?', ['nhap kho'])
                ->first();

            if (!$customer) {
                $code = 'NK';
                if (static::where('customer_code', $code)->exists()) {
                    $code = 'NHAPKHO';
                }
                if (static::where('customer_code', $code)->exists()) {
                    $code = 'NK' . date('ymd');
                }
                $customer = static::create([
                    'customer_code'   => $code,
                    'name'            => 'Nhập kho',
                    'phone'           => '',
                    'address'         => 'Kho Gervin',
                    'status'          => 'active',
                ]);
            }

            return $customer;
        } catch (\Throwable $e) {
            \Log::warning('Customer::getNhapKhoCustomer error: ' . $e->getMessage());
            return null;
        }
    }

    public function customerPayments()
    {
        return $this->hasMany(CustomerPayment::class, 'customer_id')->orderBy('payment_date');
    }

    public function careLogs()
    {
        return $this->hasMany(CustomerCareLog::class, 'customer_id')->orderBy('visit_date', 'desc')->orderBy('id', 'desc');
    }

    public function histories()
    {
        return $this->hasMany(CustomerHistory::class, 'customer_id')->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    public function getTotalDebtAttribute()
    {
        return $this->debt;
    }

    public function getDebtSummaryAttribute()
    {
        return $this->getDebtSummaryExcluding();
    }

    public function getDebtSummaryExcluding($excludeOrderId = null)
    {
        $query = $this->orders()
            ->whereNotNhapKho()
            ->whereNotIn('status', ['draft', 'cancelled', 'pending']);
            
        if ($excludeOrderId) {
            $query->where('id', '!=', $excludeOrderId);
        }

        $orders = $query->get();

        $totalAmount = $orders->sum(function($order) {
            return round($order->total_amount, -3);
        });
        
        $totalPaid = $this->customerPayments()->sum('amount');

        $totalDebt = $this->debt;
        if ($excludeOrderId) {
            $excludedOrder = Order::find($excludeOrderId);
            if ($excludedOrder && !in_array($excludedOrder->status, ['draft', 'cancelled', 'pending'])) {
                $totalDebt -= round($excludedOrder->total_amount, -3);
            }
        }

        return [
            'total_amount' => $totalAmount,
            'total_paid' => $totalPaid,
            'total_debt' => max(0, $totalDebt)
        ];
    }
}
