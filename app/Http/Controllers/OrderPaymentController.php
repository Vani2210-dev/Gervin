<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderPaymentController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'payment_date'   => 'required|date',
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,transfer,other',
            'note'           => 'nullable|string|max:500',
        ]);

        $order->orderPayments()->create([
            'payment_date'   => $request->payment_date,
            'amount'         => $request->amount,
            'payment_method' => $request->payment_method,
            'note'           => $request->note,
            'created_by'     => Auth::id(),
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Đã ghi nhận đợt thanh toán.');
    }

    public function update(Request $request, Order $order, OrderPayment $payment)
    {
        abort_if($payment->order_id !== $order->id, 403);

        $request->validate([
            'payment_date'   => 'required|date',
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,transfer,other',
            'note'           => 'nullable|string|max:500',
        ]);

        $payment->update([
            'payment_date'   => $request->payment_date,
            'amount'         => $request->amount,
            'payment_method' => $request->payment_method,
            'note'           => $request->note,
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Đã cập nhật đợt thanh toán.');
    }

    public function destroy(Order $order, OrderPayment $payment)
    {
        abort_if($payment->order_id !== $order->id, 403);

        $payment->delete();

        return redirect()->route('orders.show', $order)
            ->with('success', 'Đã xóa đợt thanh toán.');
    }
}
