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
            'name'   => 'required|string|max:255',
            'phone'  => 'nullable|string|max:20',
            'address'=> 'nullable|string',
        ]);

        // Generate customer code: KH00001, KH00002, etc.
        $lastCustomer = Customer::orderBy('id', 'desc')->first();
        $nextNumber = $lastCustomer ? intval(substr($lastCustomer->customer_code, 2)) + 1 : 1;
        $customerCode = 'KH' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

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
            'name'   => 'required|string|max:255',
            'phone'  => 'nullable|string|max:20',
            'address'=> 'nullable|string',
        ]);

        $customer->update([
            'name'          => $request->name,
            'phone'         => $request->phone,
            'address'       => $request->address,
        ]);

        return redirect()->route('customers.index')->with('success', 'Cập nhật khách hàng thành công.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Xóa khách hàng thành công.');
    }
}
