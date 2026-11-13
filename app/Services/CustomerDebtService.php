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
        DB::transaction(function () use ($customer) {
            // 1. Lấy tất cả đơn hàng hợp lệ của khách (loại trừ đơn hủy, đơn nhập kho nội bộ)
            $orders = $customer->orders()
                ->whereNotNhapKho()
                ->where('status', '!=', 'cancelled')
                ->get(['id', 'order_code', 'total_amount', 'order_date', 'created_at', 'status']);

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
                $customer->updateQuietly(['debt' => 0]);
                return;
            }

            // Xóa các ngày thừa không còn giao dịch
            CustomerDailyDebt::where('customer_id', $customer->id)
                ->whereNotIn('date', $uniqueDates)
                ->delete();

            $runningDebt = 0;

            foreach ($uniqueDates as $dateStr) {
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
                $closingDebt = round($openingDebt + $orderAmount - $paidAmount, -3);

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

            // Đồng bộ trực tiếp số dư cuối cùng vào customers.debt
            $customer->updateQuietly(['debt' => max(0, $runningDebt)]);
        });
    }

    /**
     * Lấy thông tin công nợ nhanh theo ngày cho đơn hàng hiện tại (Xem trước, In đơn, Form).
     *
     * Công thức chuẩn:
     * - Công nợ cũ: Tiền nợ tích lũy trước đơn này (Nợ đầu ngày + các đơn trước trong ngày).
     * - Đã thanh toán: Toàn bộ số tiền đã thanh toán sau đơn này (trong ngày).
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

        // 1. Xác định ngày của đơn hàng
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

        // 2. Xác định tổng tiền của đơn hàng hiện tại
        $orderTotal = $customTotal !== null
            ? round($customTotal, -3)
            : ($order ? round($order->total_amount ?? 0, -3) : 0);

        // 3. Lấy Nợ đầu ngày của ngày $orderDateStr:
        // Đọc từ bản ghi customer_daily_debts của ngày hôm đó (hoặc ngày gần nhất trước ngày hôm đó)
        $todayLedger = CustomerDailyDebt::where('customer_id', $customer->id)
            ->where('date', $orderDateStr)
            ->first();

        if ($todayLedger) {
            $openingDebt = floatval($todayLedger->opening_debt);
        } else {
            // Lấy closing_debt của ngày gần nhất trước ngày này
            $prevLedger = CustomerDailyDebt::where('customer_id', $customer->id)
                ->where('date', '<', $orderDateStr)
                ->orderBy('date', 'desc')
                ->first();
            $openingDebt = $prevLedger ? floatval($prevLedger->closing_debt) : floatval($customer->debt ?? 0);
        }

        // 4. Các đơn hàng TRƯỚC đơn hiện tại trong cùng ngày $orderDateStr
        $orderId = $order?->id;
        $ordersBeforeToday = $customer->orders()
            ->whereNotNhapKho()
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($orderDateStr) {
                $q->whereDate('order_date', $orderDateStr)
                  ->orWhere(function ($sub) use ($orderDateStr) {
                      $sub->whereNull('order_date')->whereDate('created_at', $orderDateStr);
                  });
            })
            ->when($orderId, function ($q) use ($orderId) {
                $q->where('id', '<', $orderId);
            })
            ->get();

        $sameDayBeforeOrdersAmount = round($ordersBeforeToday->sum('total_amount'), -3);

        // Công nợ cũ = Nợ đầu ngày + Các đơn trước đó trong cùng ngày
        $oldDebt = round($openingDebt + $sameDayBeforeOrdersAmount, -3);

        // 5. Đã thanh toán:
        // Lấy tất cả các khoản thanh toán trong ngày đơn hàng HOẶC trong ngày hôm nay (khi vừa ghi nhận thu tiền)
        $payments = $customer->customerPayments()
            ->where(function ($q) use ($orderDateStr, $todayStr) {
                $q->whereDate('payment_date', $orderDateStr)
                  ->orWhereDate('payment_date', $todayStr);
            })
            ->get();

        $thisOrderPaid = round($payments->sum('amount'), -3);

        // 6. Tổng nợ = Công nợ cũ + Tiền đơn hàng này - Đã thanh toán
        $totalCombinedDebt = round($oldDebt + $orderTotal - $thisOrderPaid, -3);

        if ($oldDebt == 0 && $thisOrderPaid == 0 && $totalCombinedDebt == 0 && $orderTotal == 0) {
            return null;
        }

        return [
            'old_debt'            => max(0, $oldDebt),
            'this_order_paid'     => max(0, $thisOrderPaid),
            'total_combined_debt' => max(0, $totalCombinedDebt),
        ];
    }
}

