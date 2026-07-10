<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view customer',   ['only' => ['index']]);
        $this->middleware('permission:add customer',    ['only' => ['store']]);
        $this->middleware('permission:edit customer',   ['only' => ['update']]);
        $this->middleware('permission:delete customer', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search', '');

        $customers = Customer::when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('customer_code', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            })
            ->when($request->filled('filter_customer_code'), function ($q) use ($request) {
                $q->where('customer_code', 'like', "%{$request->filter_customer_code}%");
            })
            ->when($request->filled('filter_name'), function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->filter_name}%");
            })
            ->when($request->filled('filter_phone'), function ($q) use ($request) {
                $q->where('phone', 'like', "%{$request->filter_phone}%");
            })
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('customers.index', compact('customers', 'perPage', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_code' => 'nullable|string|max:100|unique:customers,customer_code',
            'name'   => 'required|string|max:255',
            'phone'  => 'nullable|string|max:20',
            'address'=> 'nullable|string',
            'initial_debt' => 'nullable|numeric|min:0',
            'policy' => 'nullable|string',
        ]);

        // Use provided code or auto-generate KH00001, KH00002, etc.
        if ($request->filled('customer_code')) {
            $customerCode = trim($request->customer_code);
        } else {
            $lastCustomer = Customer::orderBy('id', 'desc')->first();
            $nextNumber = $lastCustomer ? intval(substr($lastCustomer->customer_code, 2)) + 1 : 1;
            $customerCode = 'KH' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        }

        Customer::create([
            'customer_code' => $customerCode,
            'name'          => $request->name,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'debt'          => $request->initial_debt ?? 0,
            'policy'        => $request->policy,
        ]);

        return redirect()->route('customers.index')->with('success', 'Thêm khách hàng thành công.');
    }

    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'customer_code' => 'nullable|string|max:100|unique:customers,customer_code,' . $customer->id,
            'name'   => 'required|string|max:255',
            'phone'  => 'nullable|string|max:20',
            'address'=> 'nullable|string',
            'initial_debt' => 'nullable|numeric|min:0',
            'policy' => 'nullable|string',
        ]);

        $updateData = [
            'name'    => $request->name,
            'phone'   => $request->phone,
            'address' => $request->address,
            'policy'  => $request->policy,
        ];
        
        if ($request->has('initial_debt')) {
            $updateData['debt'] = $request->initial_debt ?? 0;
        }

        if ($request->filled('customer_code')) {
            $updateData['customer_code'] = trim($request->customer_code);
        }

        $customer->update($updateData);

        return redirect()->route('customers.index')->with('success', 'Cập nhật khách hàng thành công.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Xóa khách hàng thành công.');
    }

    public function quickCreate(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'customer_code' => 'nullable|string|max:100|unique:customers,customer_code',
            'phone'         => 'nullable|string|max:20',
            'address'       => 'nullable|string',
        ]);

        if ($request->filled('customer_code')) {
            $customerCode = trim($request->customer_code);
        } else {
            $lastCustomer = Customer::orderBy('id', 'desc')->first();
            $nextNumber   = $lastCustomer ? intval(substr($lastCustomer->customer_code, 2)) + 1 : 1;
            $customerCode = 'KH' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        }

        $customer = Customer::create([
            'customer_code' => $customerCode,
            'name'          => $request->name,
            'phone'         => $request->phone,
            'address'       => $request->address,
        ]);

        return response()->json([
            'success'  => true,
            'customer' => $customer,
            'label'    => $customer->customer_code . ' - ' . $customer->name,
        ]);
    }

    public function overview(Request $request, Customer $customer)
    {
        $excludeOrderId = $request->query('exclude_order_id');

        $query = \App\Models\Order::where('customer_id', $customer->id);
        
        if ($excludeOrderId) {
            $query->where('id', '!=', $excludeOrderId);
        }

        $orders = $query->with('orderPayments')
            ->orderBy('order_date', 'desc')
            ->get();

        $validOrders = $orders->filter(function($o) {
            return !in_array($o->status, ['draft', 'cancelled', 'pending']);
        });

        $totalOrders = $validOrders->count();
        $totalAmount = $validOrders->sum(function($o) {
            return round($o->total_amount, -3);
        });

        // Tổng đã thu = tổng các đợt thanh toán thực tế của khách hàng (customer_payments)
        $totalPaid = $customer->customerPayments()->sum('amount');

        $statusLabels = [
            'draft'         => 'Nháp',
            'pending'       => 'Chờ xử lý',
            'transferred'   => 'Chuyển sản xuất',
            'in_production' => 'Đang sản xuất',
            'completed'     => 'Hoàn thành',
            'cancelled'     => 'Đã hủy',
        ];

        $statusCounts = $orders->groupBy('status')->map->count();

        $recentOrders = $orders->take(10)->map(function ($o) use ($statusLabels) {
            $paid = $o->orderPayments->sum('amount');
            return [
                'id'           => $o->id,
                'order_code'   => $o->order_code,
                'order_date'   => $o->order_date,
                'status'       => $o->status,
                'status_label' => $statusLabels[$o->status] ?? $o->status,
                'total_amount' => round($o->total_amount, -3),
                'paid'         => $paid,
                'debt'         => in_array($o->status, ['draft', 'cancelled', 'pending']) ? 0 : max(0, round($o->total_amount ?? 0, -3) - $paid),
                'payments_count' => $o->orderPayments->count(),
            ];
        });

        $payments = $customer->customerPayments()->with('creator', 'order')->get()->map(function($pmt) {
            return [
                'id' => $pmt->id,
                'payment_date' => $pmt->payment_date ? $pmt->payment_date->format('Y-m-d') : null,
                'payment_date_formatted' => $pmt->payment_date ? $pmt->payment_date->format('d/m/Y') : '—',
                'amount' => $pmt->amount,
                'payment_method' => $pmt->payment_method,
                'payment_method_label' => \App\Models\CustomerPayment::methodLabel($pmt->payment_method),
                'note' => $pmt->note,
                'order_id' => $pmt->order_id,
                'order_code' => $pmt->order ? $pmt->order->order_code : null,
                'creator_name' => $pmt->creator ? $pmt->creator->name : 'Hệ thống',
            ];
        });

        // Get customer's orders for the select dropdown in the add payment form
        $customerOrders = $customer->orders()->whereNotIn('status', ['draft', 'cancelled'])->orderBy('order_date', 'desc')->get()->map(function($o) {
            return [
                'id' => $o->id,
                'order_code' => $o->order_code,
                'total_amount' => round($o->total_amount, -3),
            ];
        });

        return response()->json([
            'customer'      => $customer,
            'total_orders'  => $totalOrders,
            'total_amount'  => $totalAmount,
            'total_paid'    => $totalPaid,
            'total_debt'    => $customer->total_debt,
            'unpaid_debt_summary' => $customer->getDebtSummaryExcluding($excludeOrderId),
            'status_counts' => $statusCounts,
            'recent_orders' => $recentOrders,
            'payments'      => $payments,
            'customer_orders' => $customerOrders,
        ]);
    }
}
