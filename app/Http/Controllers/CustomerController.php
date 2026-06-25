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
        ]);

        $updateData = [
            'name'    => $request->name,
            'phone'   => $request->phone,
            'address' => $request->address,
        ];

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
            return !in_array($o->status, ['draft', 'cancelled']);
        });

        $totalOrders = $validOrders->count();
        $totalAmount = $validOrders->sum(function($o) {
            return round($o->total_amount, -3);
        });

        // Tổng đã thu = tổng các đợt thanh toán thực tế (order_payments)
        $totalPaid = $validOrders->flatMap->orderPayments->sum('amount');

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

        return response()->json([
            'customer'      => $customer,
            'total_orders'  => $totalOrders,
            'total_amount'  => $totalAmount,
            'total_paid'    => $totalPaid,
            'total_debt'    => $customer->total_debt,
            'unpaid_debt_summary' => $customer->getDebtSummaryExcluding($excludeOrderId),
            'status_counts' => $statusCounts,
            'recent_orders' => $recentOrders,
        ]);
    }
}
