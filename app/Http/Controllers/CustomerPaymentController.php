<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerPaymentController extends Controller
{
    public function store(Request $request, Customer $customer)
    {
        abort_unless($customer->isAccessibleBy(auth()->user()), 403, 'Bạn không có quyền thực hiện giao dịch này.');

        $request->validate([
            'payment_date'   => 'required|date',
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,transfer,other',
            'note'           => 'nullable|string|max:500',
            'order_id'       => 'nullable|exists:orders,id',
        ]);

        $customer->customerPayments()->create([
            'payment_date'   => $request->payment_date,
            'amount'         => $request->amount,
            'payment_method' => $request->payment_method,
            'note'           => $request->note,
            'order_id'       => $request->order_id,
            'created_by'     => Auth::id(),
        ]);

        return redirect()->route('customers.index', ['overview_id' => $customer->id])
            ->with('success', 'Đã ghi nhận đợt thanh toán cho khách hàng.');
    }

    public function update(Request $request, Customer $customer, CustomerPayment $payment)
    {
        abort_unless($customer->isAccessibleBy(auth()->user()), 403, 'Bạn không có quyền thực hiện giao dịch này.');

        abort_if($payment->customer_id !== $customer->id, 403);

        $request->validate([
            'payment_date'   => 'required|date',
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'required|in:cash,transfer,other',
            'note'           => 'nullable|string|max:500',
            'order_id'       => 'nullable|exists:orders,id',
        ]);

        $payment->update([
            'payment_date'   => $request->payment_date,
            'amount'         => $request->amount,
            'payment_method' => $request->payment_method,
            'note'           => $request->note,
            'order_id'       => $request->order_id,
        ]);

        return redirect()->route('customers.index', ['overview_id' => $customer->id])
            ->with('success', 'Đã cập nhật đợt thanh toán.');
    }

    public function destroy(Customer $customer, CustomerPayment $payment)
    {
        abort_unless($customer->isAccessibleBy(auth()->user()), 403, 'Bạn không có quyền thực hiện giao dịch này.');

        abort_if($payment->customer_id !== $customer->id, 403);

        $payment->delete();

        return redirect()->route('customers.index', ['overview_id' => $customer->id])
            ->with('success', 'Đã xóa đợt thanh toán.');
    }
}
