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
        
        $startDate = $request->input('filter_start_date');
        $endDate   = $request->input('filter_end_date');

        $user = auth()->user();
        $query = Customer::query();

        if ($user && !$user->hasRole('Admin')) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        if ($startDate || $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereHas('orders', function ($o) use ($startDate, $endDate) {
                    if ($startDate) $o->where('order_date', '>=', $startDate . ' 00:00:00');
                    if ($endDate) $o->where('order_date', '<=', $endDate . ' 23:59:59');
                })->orWhereHas('customerPayments', function ($p) use ($startDate, $endDate) {
                    if ($startDate) $p->where('payment_date', '>=', $startDate);
                    if ($endDate) $p->where('payment_date', '<=', $endDate);
                });
            });
        }

        if ($request->filled('filter_customer_id')) {
            $query->where('id', $request->filter_customer_id);
        }

        $debtLevel = $request->input('filter_debt_level');
        if ($debtLevel) {
            switch ($debtLevel) {
                case '0-10':
                    $query->whereBetween('debt', [0, 10000000]);
                    break;
                case '10-30':
                    $query->whereBetween('debt', [10000000, 30000000]);
                    break;
                case '30-50':
                    $query->whereBetween('debt', [30000000, 50000000]);
                    break;
                case '50-100':
                    $query->whereBetween('debt', [50000000, 100000000]);
                    break;
                case '100-150':
                    $query->whereBetween('debt', [100000000, 150000000]);
                    break;
                case '150+':
                    $query->where('debt', '>', 150000000);
                    break;
            }
        }

        $customersQuery = $query->when($search, function ($q) use ($search) {
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
            });

        $matchingQuery = clone $customersQuery;
        $matchingCustomerIds = $matchingQuery->pluck('id');
        $totalCustomersCount = $matchingCustomerIds->count();
        $totalDebtSum = $matchingQuery->sum('debt');

        $customers = $customersQuery->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        $paidStartDate = $startDate ?: now()->startOfMonth()->toDateString();
        $paidEndDate   = $endDate ?: now()->endOfMonth()->toDateString();

        foreach ($customers as $c) {
            $c->period_paid = $c->customerPayments()
                ->where('payment_date', '>=', $paidStartDate)
                ->where('payment_date', '<=', $paidEndDate)
                ->sum('amount');
        }

        $totalPeriodPaidSum = \App\Models\CustomerPayment::whereIn('customer_id', $matchingCustomerIds)
            ->where('payment_date', '>=', $paidStartDate)
            ->where('payment_date', '<=', $paidEndDate)
            ->sum('amount');

        $baseScopedQuery = Customer::query();
        if ($user && !$user->hasRole('Admin')) {
            $baseScopedQuery->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        $lostCustomersCount = (clone $baseScopedQuery)->whereHas('orders')
            ->whereDoesntHave('orders', function ($q) {
                $q->where('order_date', '>=', now()->subMonths(2)->toDateString());
            })->count();

        $newCustomersCount = (clone $baseScopedQuery)
            ->where('created_at', '>=', $paidStartDate . ' 00:00:00')
            ->where('created_at', '<=', $paidEndDate . ' 23:59:59')
            ->count();

        $users = [];
        if ($user && $user->hasRole('Admin')) {
            $users = \App\Models\User::all();
        }

        // Get allowed customers for select dropdown filter
        $filterQuery = Customer::query();
        if ($user && !$user->hasRole('Admin')) {
            $filterQuery->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }
        $filterCustomers = $filterQuery->orderBy('name')->get();

        return view('customers.index', compact(
            'customers', 'perPage', 'search', 'users', 'filterCustomers',
            'startDate', 'endDate', 'paidStartDate', 'paidEndDate',
            'totalCustomersCount', 'totalDebtSum', 'totalPeriodPaidSum',
            'lostCustomersCount', 'newCustomersCount'
        ));
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

        $customer = Customer::create([
            'customer_code' => $customerCode,
            'name'          => $request->name,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'debt'          => $request->initial_debt ?? 0,
            'policy'        => $request->policy,
        ]);

        $user = auth()->user();
        if ($user) {
            if ($user->hasRole('Admin')) {
                if ($request->has('user_ids')) {
                    $customer->users()->sync($request->user_ids);
                }
            } else {
                $customer->users()->sync([$user->id]);
            }
        }

        return redirect()->route('customers.index')->with('success', 'Thêm khách hàng thành công.');
    }

    public function update(Request $request, Customer $customer)
    {
        abort_unless($customer->isAccessibleBy(auth()->user()), 403, 'Bạn không có quyền cập nhật khách hàng này.');

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

        $user = auth()->user();
        if ($user && $user->hasRole('Admin')) {
            $customer->users()->sync($request->input('user_ids', []));
        }

        return redirect()->route('customers.index')->with('success', 'Cập nhật khách hàng thành công.');
    }

    public function destroy(Customer $customer)
    {
        abort_unless($customer->isAccessibleBy(auth()->user()), 403, 'Bạn không có quyền xóa khách hàng này.');

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

        $user = auth()->user();
        if ($user) {
            if (!$user->hasRole('Admin')) {
                $customer->users()->sync([$user->id]);
            }
        }

        return response()->json([
            'success'  => true,
            'customer' => $customer,
            'label'    => $customer->customer_code . ' - ' . $customer->name,
        ]);
    }

    public function overview(Request $request, Customer $customer)
    {
        abort_unless($customer->isAccessibleBy(auth()->user()), 403, 'Bạn không có quyền xem thông tin khách hàng này.');

        $excludeOrderId = $request->query('exclude_order_id');
        $paymentDate = $request->query('payment_date');

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

        // Query payments with date filter and pagination (5 per page, newest first)
        $paymentsQuery = $customer->customerPayments()->reorder()->with('creator', 'order');

        if ($paymentDate) {
            $paymentsQuery->whereDate('payment_date', $paymentDate);
        }

        $paymentsPaginator = $paymentsQuery->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(5, ['*'], 'payment_page');

        $payments = collect($paymentsPaginator->items())->map(function($pmt) {
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
            'payments_pagination' => [
                'current_page' => $paymentsPaginator->currentPage(),
                'last_page' => $paymentsPaginator->lastPage(),
                'total' => $paymentsPaginator->total(),
                'per_page' => $paymentsPaginator->perPage(),
            ],
            'customer_orders' => $customerOrders,
        ]);
    }
}
