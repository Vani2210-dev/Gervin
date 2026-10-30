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
     * Tính toán công nợ lũy kế theo thứ tự thời gian cho một đơn hàng (Phương án 2).
     * Khi trong cùng 1 ngày (hoặc nhiều ngày) có nhiều đơn hàng:
     * - Đơn đặt trước sẽ tính nợ trước.
     * - Đơn đặt sau sẽ cộng dồn các đơn đặt trước đó.
     * - Các đơn đặt sau sẽ KHÔNG bị tính ngược vào đơn đặt trước.
     *
     * @param Order|null $order Đơn hàng đang xem/sửa (null nếu là đơn mới đang tạo)
     * @param float|null $customOrderTotal Tổng tiền của đơn hiện tại (dùng khi xem trước từ form)
     * @param string|null $customOrderDate Ngày chốt đơn (dùng khi xem trước từ form)
     * @return array|null [old_debt, this_order_paid, total_combined_debt]
     */
    public function getDebtInfoForOrder(?Order $order = null, ?float $customOrderTotal = null, ?string $customOrderDate = null): ?array
    {
        // 1. Không tính công nợ cho đơn nhập kho nội bộ hoặc khi không có thông tin khách hàng
        if ($order && method_exists($order, 'isNhapKho') && $order->isNhapKho()) {
            return null;
        }

        // 2. Xác định ngày giờ và tổng tiền của đơn hàng hiện tại
        $orderTotal = $customOrderTotal !== null 
            ? round($customOrderTotal, -3) 
            : ($order ? round($order->total_amount ?? 0, -3) : 0);

        $orderDate = null;
        if ($customOrderDate) {
            try { $orderDate = \Carbon\Carbon::parse($customOrderDate); } catch (\Throwable $e) {}
        }
        if (!$orderDate && $order && $order->order_date) {
            $orderDate = \Carbon\Carbon::parse($order->order_date);
        }
        if (!$orderDate && $order && $order->created_at) {
            $orderDate = \Carbon\Carbon::parse($order->created_at);
        }
        if (!$orderDate) {
            $orderDate = \Carbon\Carbon::now();
        }

        $orderId = $order?->id;

        // 3. Lấy tất cả đơn hàng hợp lệ của khách hàng (loại bỏ đơn hủy, đơn nhập kho)
        $allOrders = $this->orders()
            ->whereNotNhapKho()
            ->where('status', '!=', 'cancelled')
            ->get();

        // 4. Phân loại các đơn hàng TRƯỚC và SAU đơn hàng hiện tại
        $ordersAfter = collect();
        $ordersBefore = collect();

        foreach ($allOrders as $o) {
            if ($orderId && $o->id == $orderId) {
                continue; // Chính là đơn hiện tại
            }

            $oDate = $o->order_date ? \Carbon\Carbon::parse($o->order_date) : ($o->created_at ? \Carbon\Carbon::parse($o->created_at) : null);
            
            $isAfter = false;
            if ($orderId) {
                if ($oDate && $orderDate) {
                    if ($oDate->gt($orderDate)) {
                        $isAfter = true;
                    } elseif ($oDate->eq($orderDate)) {
                        $isAfter = $o->id > $orderId;
                    } else {
                        $isAfter = false;
                    }
                } else {
                    $isAfter = $o->id > $orderId;
                }
            } else {
                // Đơn mới đang tạo: nếu $oDate muộn hơn $orderDate thì coi là after, còn lại là before
                if ($oDate && $orderDate && $oDate->gt($orderDate)) {
                    $isAfter = true;
                } else {
                    $isAfter = false;
                }
            }

            if ($isAfter) {
                $ordersAfter->push($o);
            } else {
                $ordersBefore->push($o);
            }
        }

        // 5. Tính toán Công nợ cũ tính đến trước đơn này ($oldDebt)
        $currentDebt = floatval($this->debt ?? 0);
        $oldDebt = $currentDebt;

        // A. Nếu đơn hiện tại $order đang ở trạng thái đã được cộng vào $this->debt (transferred, in_production, completed):
        // Ta phải TRỪ tiền đơn này ra khỏi $oldDebt (vì $oldDebt là nợ TRƯỚC đơn này)
        if ($order && !in_array($order->status, ['draft', 'pending', 'cancelled']) && $order->board_return_status !== 'returned') {
            $oldDebt -= round($order->total_amount ?? 0, -3);
        }

        // B. Với các đơn SAU đơn này ($ordersAfter):
        // Nếu bất kỳ đơn nào sau đơn này ĐÃ ĐƯỢC CỘNG vào $this->debt, ta phải TRỪ nó ra khỏi $oldDebt
        foreach ($ordersAfter as $afterOrder) {
            if (!in_array($afterOrder->status, ['draft', 'pending', 'cancelled']) && $afterOrder->board_return_status !== 'returned') {
                $oldDebt -= round($afterOrder->total_amount ?? 0, -3);
            }
        }

        // C. Với các đơn TRƯỚC đơn này ($ordersBefore):
        // Nếu bất kỳ đơn nào trước đơn này mà CHƯA ĐƯỢC CỘNG vào $this->debt (ví dụ đơn đó đang pending):
        // Theo nguyên tắc lũy kế, vì đơn đó đã đặt trước đơn này, ta CỘNG nó vào $oldDebt
        foreach ($ordersBefore as $beforeOrder) {
            if (in_array($beforeOrder->status, ['pending']) && $beforeOrder->board_return_status !== 'returned') {
                $oldDebt += round($beforeOrder->total_amount ?? 0, -3);
            }
        }

        // D. Xử lý các khoản thanh toán (payments)
        $payments = $this->customerPayments()->get();

        // Khoản thanh toán cho riêng đơn hiện tại này
        $thisOrderPaid = 0;
        if ($orderId) {
            $thisOrderPaid = $payments->where('order_id', $orderId)->sum('amount');
        }

        // Các khoản thanh toán diễn ra SAU đơn này: cộng lại vào $oldDebt
        foreach ($payments as $pmt) {
            if ($pmt->order_id && $ordersAfter->contains('id', $pmt->order_id)) {
                $oldDebt += floatval($pmt->amount);
            } elseif (!$pmt->order_id && $pmt->payment_date) {
                $pDate = \Carbon\Carbon::parse($pmt->payment_date)->startOfDay();
                if ($pDate->gt($orderDate->copy()->startOfDay())) {
                    $oldDebt += floatval($pmt->amount);
                }
            }
        }

        // Nếu đơn hiện tại chưa có phiếu thanh toán riêng gắn với order_id:
        // Phân bổ khoản thanh toán chung trong ngày cho các đơn theo thứ tự
        if ($thisOrderPaid == 0) {
            $orderDateStr = $orderDate->format('Y-m-d');
            $generalPaymentsToday = $payments->filter(function($p) use ($orderDateStr) {
                return empty($p->order_id) && $p->payment_date && \Carbon\Carbon::parse($p->payment_date)->format('Y-m-d') === $orderDateStr;
            })->sum('amount');

            if ($generalPaymentsToday > 0) {
                $sameDayOrdersBefore = $ordersBefore->filter(function($o) use ($orderDateStr) {
                    $d = $o->order_date ? \Carbon\Carbon::parse($o->order_date)->format('Y-m-d') : null;
                    return $d === $orderDateStr;
                });

                $usedByPreviousOrders = 0;
                foreach ($sameDayOrdersBefore as $prevOrder) {
                    $prevSpecific = $payments->where('order_id', $prevOrder->id)->sum('amount');
                    if ($prevSpecific == 0) {
                        $prevAmount = round($prevOrder->total_amount ?? 0, -3);
                        $usedByPreviousOrders += $prevAmount;
                    }
                }

                $remainingGeneralPayment = max(0, $generalPaymentsToday - $usedByPreviousOrders);
                $thisOrderPaid = min($orderTotal, $remainingGeneralPayment);
            }
        }

        $oldDebt = max(0, $oldDebt);
        $totalCombinedDebt = max(0, $oldDebt + $orderTotal - $thisOrderPaid);

        if ($oldDebt == 0 && $thisOrderPaid == 0 && $totalCombinedDebt == 0 && $orderTotal == 0) {
            return null;
        }

        return [
            'old_debt'            => round($oldDebt, -3),
            'this_order_paid'     => round($thisOrderPaid, -3),
            'total_combined_debt' => round($totalCombinedDebt, -3),
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
