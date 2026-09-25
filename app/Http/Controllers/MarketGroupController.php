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

    public function show(Request $request, MarketGroup $marketGroup)
    {
        $marketGroup->load(['users']);

        $search = $request->input('search');

        // Date Filter Mode: day | month | year | all
        $dateMode = $request->input('date_mode', 'month');
        $dateVal = $request->input('date_val');

        if (!$dateVal && $dateMode !== 'all') {
            if ($dateMode === 'day') {
                $dateVal = now()->toDateString();
            } elseif ($dateMode === 'year') {
                $dateVal = now()->format('Y');
            } else { // month
                $dateMode = 'month';
                $dateVal = now()->format('Y-m');
            }
        }

        $startDate = null;
        $endDate = null;
        $dateLabel = 'Toàn thời gian';
        $inputType = 'month';

        if ($dateMode === 'day' && $dateVal) {
            $startDate = $dateVal;
            $endDate = $dateVal;
            $dateLabel = 'Ngày ' . \Carbon\Carbon::parse($dateVal)->format('d/m/Y');
            $inputType = 'date';
        } elseif ($dateMode === 'month' && $dateVal) {
            $cDate = \Carbon\Carbon::parse($dateVal . '-01');
            $startDate = $cDate->copy()->startOfMonth()->toDateString();
            $endDate = $cDate->copy()->endOfMonth()->toDateString();
            $dateLabel = 'Tháng ' . $cDate->format('m/Y');
            $inputType = 'month';
        } elseif ($dateMode === 'year' && $dateVal) {
            $startDate = $dateVal . '-01-01';
            $endDate = $dateVal . '-12-31';
            $dateLabel = 'Năm ' . $dateVal;
            $inputType = 'number';
        } else {
            $dateMode = 'all';
            $dateVal = '';
            $dateLabel = 'Toàn bộ thời gian';
            $inputType = 'text';
        }

        $query = $marketGroup->customers()
            ->with(['customerPayments' => function ($pq) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $pq->whereBetween('payment_date', [$startDate, $endDate]);
                }
            }])
            ->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('province', 'like', "%{$search}%")
                  ->orWhere('ward', 'like', "%{$search}%")
                  ->orWhere('partner_competitors', 'like', "%{$search}%")
                  ->orWhere('workshop_scale', 'like', "%{$search}%")
                  ->orWhere('personality', 'like', "%{$search}%")
                  ->orWhere('feedback', 'like', "%{$search}%")
                  ->orWhere('customer_proposal', 'like', "%{$search}%")
                  ->orWhere('sale_proposal', 'like', "%{$search}%");
            });
        }

        $customers = $query->get()->map(function ($c) {
            $c->period_paid = $c->customerPayments->sum('amount');
            $fullAddr = implode(', ', array_filter([$c->address, $c->ward, $c->province]));
            $c->full_address = $fullAddr ?: ($c->address ?: '—');
            return $c;
        });

        $totalCustomers = $marketGroup->customers()->count();
        $totalDebt = $marketGroup->customers()->sum('debt');
        $groupCustomerIds = $marketGroup->customers()->pluck('id');

        $paidQuery = \App\Models\CustomerPayment::whereIn('customer_id', $groupCustomerIds);
        if ($startDate && $endDate) {
            $paidQuery->whereBetween('payment_date', [$startDate, $endDate]);
        }
        $totalPaid = $paidQuery->sum('amount');

        $allCustomers = Customer::orderBy('name')->get();
        $marketGroups = MarketGroup::orderBy('name')->get();

        return view('market_groups.show', compact(
            'marketGroup',
            'marketGroups',
            'customers',
            'totalCustomers',
            'totalDebt',
            'totalPaid',
            'allCustomers',
            'search',
            'dateMode',
            'dateVal',
            'dateLabel',
            'inputType'
        ));
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
            ->with('customerPayments')
            ->orderBy('name')
            ->get()
            ->map(function ($c) {
                $paid = $c->customerPayments->sum('amount');
                $fullAddr = implode(', ', array_filter([$c->address, $c->ward, $c->province]));
                return [
                    'id'                  => $c->id,
                    'customer_code'       => $c->customer_code,
                    'name'                => $c->name,
                    'phone'               => $c->phone,
                    'province'            => $c->province,
                    'ward'                => $c->ward,
                    'address'             => $c->address,
                    'full_address'        => $fullAddr ?: ($c->address ?: '—'),
                    'latitude'            => $c->latitude,
                    'longitude'           => $c->longitude,
                    'status'              => $c->status ?: 'Đang đặt hàng',
                    'partner_competitors' => $c->partner_competitors,
                    'workshop_scale'      => $c->workshop_scale,
                    'personality'         => $c->personality,
                    'feedback'            => $c->feedback,
                    'customer_proposal'   => $c->customer_proposal,
                    'sale_proposal'       => $c->sale_proposal,
                    'policy'              => $c->policy,
                    'photos'              => $c->photos ?: [],
                    'debt'                => $c->debt ?? 0,
                    'debt_limit'          => $c->debt_limit ?? 0,
                    'period_paid'         => $paid,
                ];
            });

        return response()->json([
            'group'       => $marketGroup,
            'total_count' => $customers->count(),
            'customers'   => $customers,
        ]);
    }
}
