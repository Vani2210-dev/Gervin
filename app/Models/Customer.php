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
        'monthly_discount_policy',
        'applied_monthly_discounts',
        'photos',
        'market_group_id',
    ];

    protected $casts = [
        'photos'                    => 'array',
        'monthly_discount_policy'   => 'array',
        'applied_monthly_discounts' => 'array',
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

    /**
     * Tính toán chiết khấu tháng theo doanh số Acrylic (loại trừ min late và cánh kính)
     * @param string $yearMonth Định dạng 'YYYY-MM'
     * @return array
     */
    public function calculateMonthlyDiscount(string $yearMonth): array
    {
        $cDate = \Carbon\Carbon::parse($yearMonth . '-01');
        $startOfMonth = $cDate->copy()->startOfMonth()->toDateTimeString();
        $endOfMonth = $cDate->copy()->endOfMonth()->toDateTimeString();

        // Lấy tất cả đơn hàng Acrylic hợp lệ trong tháng (loại trừ min_late, glass, flywood/service)
        $acrylicOrders = $this->orders()
            ->whereNotNhapKho()
            ->where('type', 'acrylic')
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('order_date', [$startOfMonth, $endOfMonth])
            ->orderBy('order_date', 'asc')
            ->get();

        // Tổng doanh số Acrylic trong tháng
        $acrylicRevenue = (float) $acrylicOrders->sum(function ($order) {
            return round($order->total_amount, -3);
        });

        // Lấy chính sách chiết khấu của khách
        $policy = $this->monthly_discount_policy ?? [];
        $tiers = $policy['tiers'] ?? [];
        if (empty($tiers) && !empty($policy['min_amount'])) {
            $tiers = [
                [
                    'min_amount' => (float) ($policy['min_amount'] ?? 0),
                    'percent'    => (float) ($policy['percent'] ?? 0),
                ]
            ];
        }

        // Sắp xếp các mốc doanh số từ cao xuống thấp để lấy mức chiết khấu cao nhất đạt được
        usort($tiers, function ($a, $b) {
            return ($b['min_amount'] ?? 0) <=> ($a['min_amount'] ?? 0);
        });

        $matchedTier = null;
        $highestPercent = 0;
        foreach ($tiers as $tier) {
            $minAmount = (float) ($tier['min_amount'] ?? 0);
            if ($minAmount > 0 && $acrylicRevenue >= $minAmount) {
                $matchedTier = $tier;
                $highestPercent = (float) ($tier['percent'] ?? 0);
                break;
            }
        }

        // Nếu chưa đạt mốc nào, tìm mốc nhỏ nhất để thông báo còn thiếu bao nhiêu
        $nextTier = null;
        $amountNeeded = 0;
        if (!$matchedTier && !empty($tiers)) {
            $lowestTier = end($tiers);
            $minAmount = (float) ($lowestTier['min_amount'] ?? 0);
            if ($minAmount > $acrylicRevenue) {
                $nextTier = $lowestTier;
                $amountNeeded = $minAmount - $acrylicRevenue;
            }
        }

        $discountAmount = 0;
        if ($highestPercent > 0 && $acrylicRevenue > 0) {
            $discountAmount = round(($acrylicRevenue * $highestPercent) / 100, -3);
        }

        // Kiểm tra xem tháng này đã áp dụng vào công nợ chưa
        $appliedDiscounts = $this->applied_monthly_discounts ?? [];
        $appliedInfo = $appliedDiscounts[$yearMonth] ?? null;
        $isApplied = !empty($appliedInfo);

        return [
            'year_month'        => $yearMonth,
            'month_label'       => $cDate->format('m/Y'),
            'acrylic_orders'    => $acrylicOrders,
            'acrylic_count'     => $acrylicOrders->count(),
            'acrylic_revenue'   => $acrylicRevenue,
            'policy'            => $policy,
            'tiers'             => $tiers,
            'matched_tier'      => $matchedTier,
            'discount_percent'  => $highestPercent,
            'discount_amount'   => $discountAmount,
            'is_eligible'       => $highestPercent > 0 && $discountAmount > 0,
            'next_tier'         => $nextTier,
            'amount_needed'     => $amountNeeded,
            'is_applied'        => $isApplied,
            'applied_info'      => $appliedInfo,
        ];
    }

    /**
     * Áp dụng chiết khấu tháng vào công nợ khách hàng
     */
    public function applyMonthlyDiscount(string $yearMonth, ?string $customNote = null, ?int $userId = null): array
    {
        $calc = $this->calculateMonthlyDiscount($yearMonth);
        if ($calc['is_applied']) {
            throw new \Exception("Tháng {$calc['month_label']} đã được cập nhật chiết khấu vào công nợ trước đó.");
        }
        if (!$calc['is_eligible'] || $calc['discount_amount'] <= 0) {
            throw new \Exception("Tháng {$calc['month_label']} không đủ điều kiện chiết khấu (doanh số Acrylic chưa đạt mốc).");
        }

        $cDate = \Carbon\Carbon::parse($yearMonth . '-01');
        $paymentDate = $cDate->copy()->endOfMonth()->toDateString();
        if ($paymentDate > now()->toDateString()) {
            $paymentDate = now()->toDateString();
        }

        $note = $customNote ?: ("Chiết khấu tháng " . $calc['month_label'] . " doanh số Acrylic " . number_format($calc['acrylic_revenue'], 0, ',', '.') . "đ (-" . $calc['discount_percent'] . "%)");

        // Tạo bản ghi CustomerPayment -> Tự động giảm debt của khách hàng qua boot event
        $payment = \App\Models\CustomerPayment::create([
            'customer_id'    => $this->id,
            'payment_date'   => $paymentDate,
            'amount'         => $calc['discount_amount'],
            'payment_method' => 'discount',
            'note'           => $note,
            'created_by'     => $userId ?: auth()->id(),
        ]);

        $appliedDiscounts = $this->applied_monthly_discounts ?? [];
        $appliedDiscounts[$yearMonth] = [
            'acrylic_revenue'  => $calc['acrylic_revenue'],
            'discount_percent' => $calc['discount_percent'],
            'discount_amount'  => $calc['discount_amount'],
            'payment_id'       => $payment->id,
            'payment_date'     => $paymentDate,
            'note'             => $note,
            'applied_at'       => now()->toDateTimeString(),
            'applied_by'       => $userId ?: auth()->id(),
            'applied_by_name'  => auth()->user()?->name ?? 'Hệ thống',
        ];

        $this->update(['applied_monthly_discounts' => $appliedDiscounts]);

        return [
            'payment'   => $payment,
            'calc'      => $calc,
        ];
    }

    /**
     * Hủy áp dụng chiết khấu tháng và hoàn tác lại công nợ
     */
    public function cancelMonthlyDiscount(string $yearMonth): bool
    {
        $appliedDiscounts = $this->applied_monthly_discounts ?? [];
        if (empty($appliedDiscounts[$yearMonth])) {
            return false;
        }

        $paymentId = $appliedDiscounts[$yearMonth]['payment_id'] ?? null;
        if ($paymentId) {
            $payment = \App\Models\CustomerPayment::find($paymentId);
            if ($payment) {
                // Xóa payment -> Tự động khôi phục debt qua boot deleted event
                $payment->delete();
            }
        }

        unset($appliedDiscounts[$yearMonth]);
        $this->update(['applied_monthly_discounts' => $appliedDiscounts]);

        return true;
    }
}
