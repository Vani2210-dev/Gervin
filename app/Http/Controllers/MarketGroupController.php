<?php

namespace App\Http\Controllers;

use App\Models\MarketGroup;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;

class MarketGroupController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $marketGroups = MarketGroup::with(['users' => function ($q) {
            $q->orderBy('name');
        }])
        ->withCount(['users', 'customers'])
        ->when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%$search%")
              ->orWhere('code', 'like', "%$search%")
              ->orWhere('description', 'like', "%$search%");
        })
        ->orderBy('name')
        ->get();

        $allUsers = User::whereDoesntHave('roles', function ($q) {
            $q->where('name', 'Admin');
        })->where(function ($q) {
            $q->whereNull('role_id')
              ->orWhereHas('role', function ($rq) {
                  $rq->where('name', '!=', 'Admin');
              });
        })->where('email', '!=', 'admin@kbtech.com')
          ->where('id', '!=', 1)
          ->orderBy('name')
          ->get();

        $allCustomers = Customer::orderBy('name')->select('id', 'customer_code', 'name', 'phone', 'market_group_id')->get();

        return view('market_groups.index', compact('marketGroups', 'allUsers', 'allCustomers', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'user_ids'    => 'nullable|array',
            'user_ids.*'  => 'exists:users,id',
        ]);

        $group = MarketGroup::create([
            'name'        => $request->name,
            'code'        => $request->code,
            'description' => $request->description,
        ]);

        if ($request->has('user_ids')) {
            $group->users()->sync($request->user_ids);
        }

        return redirect()->route('market-groups.index')->with('success', 'Tạo nhóm thị trường thành công.');
    }

    public function update(Request $request, MarketGroup $marketGroup)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'code'        => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'user_ids'    => 'nullable|array',
            'user_ids.*'  => 'exists:users,id',
        ]);

        $marketGroup->update([
            'name'        => $request->name,
            'code'        => $request->code,
            'description' => $request->description,
        ]);

        if ($request->has('user_ids')) {
            $marketGroup->users()->sync($request->input('user_ids', []));
        }

        return redirect()->route('market-groups.index')->with('success', 'Cập nhật nhóm thị trường thành công.');
    }

    public function destroy(MarketGroup $marketGroup)
    {
        // Gỡ nhóm khỏi các khách hàng thuộc nhóm này
        Customer::where('market_group_id', $marketGroup->id)->update(['market_group_id' => null]);
        
        $marketGroup->delete();

        return redirect()->route('market-groups.index')->with('success', 'Xóa nhóm thị trường thành công.');
    }

    public function assignUsers(Request $request, MarketGroup $marketGroup)
    {
        $request->validate([
            'user_ids'   => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $marketGroup->users()->sync($request->input('user_ids', []));

        return redirect()->route('market-groups.index')->with('success', 'Cập nhật danh sách thành viên nhóm thành công.');
    }

    public function assignCustomers(Request $request, MarketGroup $marketGroup)
    {
        $request->validate([
            'customer_ids'   => 'required|array',
            'customer_ids.*' => 'exists:customers,id',
        ]);

        Customer::whereIn('id', $request->customer_ids)->update(['market_group_id' => $marketGroup->id]);

        return redirect()->back()->with('success', 'Đã thêm khách hàng vào nhóm ' . $marketGroup->name . ' thành công.');
    }

    public function removeCustomer(MarketGroup $marketGroup, Customer $customer)
    {
        if ($customer->market_group_id == $marketGroup->id) {
            $customer->update(['market_group_id' => null]);
        }

        return redirect()->back()->with('success', 'Đã xóa khách hàng khỏi nhóm thị trường.');
    }

    public function customersData(MarketGroup $marketGroup)
    {
        $customers = $marketGroup->customers()
            ->select('id', 'customer_code', 'name', 'phone', 'address', 'debt')
            ->orderBy('name')
            ->get();

        return response()->json([
            'group'     => $marketGroup,
            'customers' => $customers,
        ]);
    }
}
