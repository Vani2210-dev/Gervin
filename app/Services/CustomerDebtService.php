<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerDailyDebt;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerDebtService
{
    /**
     * Đồng bộ lại toàn bộ sổ cái công nợ theo ngày cho 1 khách hàng.
     * Tự động tính: Nợ đầu ngày, Tổng đơn trong ngày, Đã thanh toán trong ngày, Nợ cuối ngày.
     * Cập nhật đồng bộ trực tiếp vào $customer->debt.
     */
    public static function syncCustomerDailyDebts(Customer $customer): void
    {
        try {
            DB::transaction(function () use ($customer) {
                // 1. Chỉ lấy các đơn hàng ĐÃ ĐƯỢC XÁC NHẬN cộng vào công nợ (loại trừ đơn nháp, chờ duyệt, đơn hủy, trả ván, nhập kho)
                $orders = $customer->orders()
                    ->whereNotNhapKho()
                    ->whereNotIn('status', ['draft', 'pending', 'cancelled'])
                    ->where(function ($q) {
                        $q->whereNull('board_return_status')->orWhere('board_return_status', '!=', 'returned');
                    })
                    ->get(['id', 'order_code', 'total_amount', 'order_date', 'created_at', 'status', 'board_return_status']);

                // 2. Lấy tất cả các khoản thanh toán của khách
                $payments = $customer->customerPayments()
                    ->get(['id', 'order_id', 'amount', 'payment_date', 'payment_method']);

                // 3. Gom danh sách các ngày phát sinh giao dịch
                $dates = collect();

                foreach ($orders as $o) {
                    $d = $o->order_date ? Carbon::parse($o->order_date)->format('Y-m-d') : Carbon::parse($o->created_at)->format('Y-m-d');
                    $dates->push($d);
                }

                foreach ($payments as $p) {
                    if ($p->payment_date) {
                        $dates->push(Carbon::parse($p->payment_date)->format('Y-m-d'));
                    }
                }

                $uniqueDates = $dates->unique()->sort()->values();

                // Nếu khách hàng chưa có giao dịch nào
                if ($uniqueDates->isEmpty()) {
                    CustomerDailyDebt::where('customer_id', $customer->id)->delete();
                    return;
                }

                // Xóa các ngày thừa không còn giao dịch
                CustomerDailyDebt::where('customer_id', $customer->id)
                    ->whereNotIn('date', $uniqueDates)
                    ->delete();

                // 4. Còn nợ hiện tại của khách hàng (Chuẩn mực trong hệ thống)
                $currentDebt = floatval($customer->debt ?? 0);

                // Tổng tiền các đơn hàng hợp lệ đã cộng nợ và tổng tiền đã thanh toán
                $totalOrderAmount = round($orders->sum('total_amount'), -3);
                $totalPaidAmount = round($payments->sum('amount'), -3);

                // Nợ ban đầu (số dư trước tất cả các đơn hàng / thanh toán ghi nhận trong hệ thống)
                // Công thức: Nợ cuối = Nợ ban đầu + Tổng đơn - Tổng trả => Nợ ban đầu = Nợ cuối - Tổng đơn + Tổng trả
                $initialDebt = round($currentDebt - $totalOrderAmount + $totalPaidAmount, -3);

                $runningDebt = $initialDebt;
                $lastIndex = $uniqueDates->count() - 1;

                foreach ($uniqueDates as $index => $dateStr) {
                    // Đơn hàng trong ngày này
                    $dayOrders = $orders->filter(function ($o) use ($dateStr) {
                        $d = $o->order_date ? Carbon::parse($o->order_date)->format('Y-m-d') : Carbon::parse($o->created_at)->format('Y-m-d');
                        return $d === $dateStr;
                    });

                    // Thanh toán trong ngày này
                    $dayPayments = $payments->filter(function ($p) use ($dateStr) {
                        return $p->payment_date && Carbon::parse($p->payment_date)->format('Y-m-d') === $dateStr;
                    });

                    $openingDebt = round($runningDebt, -3);
                    $orderAmount = round($dayOrders->sum('total_amount'), -3);
                    $paidAmount = round($dayPayments->sum('amount'), -3);

                    // Tại ngày cuối cùng, chốt số dư cuối ngày khớp tuyệt đối 100% với Còn nợ hiện tại ($currentDebt)
                    if ($index === $lastIndex) {
                        $closingDebt = $currentDebt;
                    } else {
                        $closingDebt = round($openingDebt + $orderAmount - $paidAmount, -3);
                    }

                    CustomerDailyDebt::updateOrCreate(
                        [
                            'customer_id' => $customer->id,
                            'date'        => $dateStr,
                        ],
                        [
                            'opening_debt'   => $openingDebt,
                            'order_amount'   => $orderAmount,
                            'paid_amount'    => $paidAmount,
                            'closing_debt'   => $closingDebt,
                            'orders_count'   => $dayOrders->count(),
                            'payments_count' => $dayPayments->count(),
                        ]
                    );

                    $runningDebt = $closingDebt;
                }

                // Ghi chú: KHÔNG ghi đè $customer->debt ở đây để đảm bảo Còn nợ hiện tại (customers.debt)
                // là nguồn dữ liệu chuẩn mực nhất theo số dư ban đầu và các giao dịch thực tế.
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('syncCustomerDailyDebts error: ' . $e->getMessage());
        }
    }

    /**
     * Lấy thông tin công nợ cho đơn hàng (Xem trước, In đơn, Form), ăn theo Còn nợ hiện tại ($customer->debt).
     *
     * Công thức chuẩn:
     * - Công nợ cũ: Tiền nợ tích lũy trước đơn này (xuất phát từ Còn nợ hiện tại $customer->debt).
     * - Đã thanh toán: Toàn bộ số tiền đã thanh toán trong ngày/cho đơn này.
     * - Tổng nợ = Công nợ cũ + Tiền đơn hàng này - Đã thanh toán.
     */
    public static function getDebtInfoForOrder(
        ?Order $order = null,
        ?Customer $customer = null,
        ?float $customTotal = null,
        ?string $customOrderDate = null
    ): ?array {
        $customer = $customer ?: $order?->customer;
        if (!$customer) {
            return null;
        }

        if ($order && method_exists($order, 'isNhapKho') && $order->isNhapKho()) {
            return null;
        }

        // 1. ĂN THEO CÒN NỢ HIỆN TẠI (customers.debt)
        $currentDebt = floatval($customer->debt ?? 0);

        // 2. Xác định ngày giờ và tổng tiền của đơn hàng hiện tại
        $orderTotal = $customTotal !== null
            ? round($customTotal, -3)
            : ($order ? round($order->total_amount ?? 0, -3) : 0);

        $orderDate = null;
        if ($customOrderDate) {
            try { $orderDate = Carbon::parse($customOrderDate); } catch (\Throwable $e) {}
        }
        if (!$orderDate && $order && $order->order_date) {
            $orderDate = Carbon::parse($order->order_date);
        }
        if (!$orderDate && $order && $order->created_at) {
            $orderDate = Carbon::parse($order->created_at);
        }
        if (!$orderDate) {
            $orderDate = Carbon::now();
        }

        $orderDateStr = $orderDate->format('Y-m-d');
        $todayStr = Carbon::now()->format('Y-m-d');
        $orderId = $order?->id;

        // 3. Lấy tất cả các khoản thanh toán của khách hàng
        $payments = $customer->customerPayments()->get(['id', 'order_id', 'amount', 'payment_date']);

        // A. Khoản thanh toán GẮN ĐÍCH DANH VỚI ĐƠN HÀNG NÀY (order_id == $orderId)
        // Lưu ý quan trọng: Chỉ có khoản thanh toán gắn mã đơn hàng này mới là tiền khách thanh toán cho đơn này.
        // Tuyệt đối KHÔNG phân bổ khoản thu nợ chung của khách (order_id null, ví dụ đợt thanh toán nợ cũ) vào đơn hàng mới!
        $specificPaid = $orderId ? floatval($payments->where('order_id', $orderId)->sum('amount')) : 0.0;
        $thisOrderPaid = $specificPaid;

        // B. Nếu là khách lẻ (is_retail == true):
        // Khách lẻ thanh toán công nợ dứt điểm theo từng đơn riêng biệt, không có công nợ cũ lũy kế từ các đơn khác!
        if ($customer->is_retail) {
            $totalCombinedDebt = max(0, $orderTotal - $thisOrderPaid);
            return [
                'is_retail'           => true,
                'old_debt'            => 0.0,
                'this_order_paid'     => round($thisOrderPaid, -3),
                'total_combined_debt' => round($totalCombinedDebt, -3),
            ];
        }

        // 4. Lấy tất cả đơn hàng hợp lệ của khách hàng (loại bỏ đơn hủy, đơn nhập kho, đơn nháp draft)
        $allOrders = $customer->orders()
            ->whereNotNhapKho()
            ->whereNotIn('status', ['cancelled', 'draft'])
            ->get(['id', 'order_code', 'total_amount', 'order_date', 'created_at', 'status', 'board_return_status']);

        // 5. Phân loại các đơn hàng TRƯỚC và SAU đơn hàng hiện tại theo thời gian
        $ordersAfter = collect();
        $ordersBefore = collect();

        foreach ($allOrders as $o) {
            if ($orderId && $o->id == $orderId) {
                continue;
            }

            $oDate = $o->order_date ? Carbon::parse($o->order_date) : ($o->created_at ? Carbon::parse($o->created_at) : null);

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

        // 6. Tính toán Công nợ cũ tính đến trước đơn này ($oldDebt) từ Còn nợ hiện tại ($currentDebt)
        $oldDebt = $currentDebt;

        // A. Nếu đơn hiện tại $order đang ở trạng thái ĐÃ ĐƯỢC CỘNG vào currentDebt (transferred, in_production, completed...):
        // Ta trừ tiền đơn này ra khỏi oldDebt (vì oldDebt là nợ TRƯỚC đơn này)
        $isOrderInDebt = $order && !in_array($order->status, ['draft', 'pending', 'cancelled']) && $order->board_return_status !== 'returned';
        if ($isOrderInDebt) {
            $oldDebt -= round($order->total_amount ?? 0, -3);
        }

        // B. Với các đơn SAU đơn này ($ordersAfter):
        // Nếu đã được cộng vào currentDebt, ta trừ ra khỏi oldDebt (vì khi đơn này phát sinh thì các đơn sau chưa có)
        foreach ($ordersAfter as $afterOrder) {
            if (!in_array($afterOrder->status, ['draft', 'pending', 'cancelled']) && $afterOrder->board_return_status !== 'returned') {
                $oldDebt -= round($afterOrder->total_amount ?? 0, -3);
            }
        }

        // C. Với các đơn TRƯỚC đơn này ($ordersBefore):
        // Nếu đơn nào trước đơn này mà CHƯA ĐƯỢC CỘNG vào currentDebt (ở trạng thái pending - Chờ duyệt):
        // Ta cộng tiền đơn đó vào oldDebt để nợ nối tiếp đúng thời gian
        // (Lưu ý: Đơn nháp draft KHÔNG tính vào công nợ; mọi khoản đã trả cho đơn trước đó đã được trừ vào currentDebt rồi)
        foreach ($ordersBefore as $beforeOrder) {
            if ($beforeOrder->status === 'pending' && $beforeOrder->board_return_status !== 'returned') {
                $oldDebt += round($beforeOrder->total_amount ?? 0, -3);
            }
        }

        // D. Khoản thanh toán gắn đích danh với đơn này ($thisOrderPaid):
        // Vì khoản $thisOrderPaid này đã được trừ vào $currentDebt khi tạo phiếu thu,
        // để $oldDebt thể hiện đúng số nợ TRƯỚC đơn này và TRƯỚC khi thanh toán đơn này, ta cộng lại $thisOrderPaid vào $oldDebt:
        if ($thisOrderPaid > 0) {
            $oldDebt += $thisOrderPaid;
        }

        // E. Các khoản thanh toán diễn ra SAU đơn này:
        // - Thanh toán gắn với đơn sau: cộng lại vào oldDebt
        // - Thanh toán chung (không gắn đơn) có ngày sau ngày đơn này: cộng lại vào oldDebt
        foreach ($payments as $pmt) {
            if ($pmt->order_id && $ordersAfter->contains('id', $pmt->order_id)) {
                $oldDebt += floatval($pmt->amount);
            } elseif (!$pmt->order_id && $pmt->payment_date) {
                $pDate = Carbon::parse($pmt->payment_date)->startOfDay();
                if ($pDate->gt($orderDate->copy()->startOfDay())) {
                    $oldDebt += floatval($pmt->amount);
                }
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
}

