<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MarketGroup;
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
        
        // Date Filter Mode: day | month | year | all | custom
        $dateMode = $request->input('date_mode');
        $dateVal = $request->input('date_val');
        $filterStartDate = $request->input('filter_start_date');
        $filterEndDate = $request->input('filter_end_date');

        $startDate = null;
        $endDate = null;
        $dateLabel = 'Toàn thời gian';

        if ($dateMode) {
            if ($dateMode === 'day' && $dateVal) {
                $startDate = $dateVal;
                $endDate = $dateVal;
                $dateLabel = 'Ngày ' . \Carbon\Carbon::parse($dateVal)->format('d/m/Y');
            } elseif ($dateMode === 'month' && $dateVal) {
                $cDate = \Carbon\Carbon::parse($dateVal . '-01');
                $startDate = $cDate->copy()->startOfMonth()->toDateString();
                $endDate = $cDate->copy()->endOfMonth()->toDateString();
                $dateLabel = 'Tháng ' . $cDate->format('m/Y');
            } elseif ($dateMode === 'year' && $dateVal) {
                $startDate = $dateVal . '-01-01';
                $endDate = $dateVal . '-12-31';
                $dateLabel = 'Năm ' . $dateVal;
            } elseif ($dateMode === 'all') {
                $startDate = null;
                $endDate = null;
                $dateLabel = 'Toàn thời gian';
            }
        } elseif ($filterStartDate || $filterEndDate) {
            $startDate = $filterStartDate;
            $endDate = $filterEndDate;
            $dateMode = 'custom';
            $dateLabel = ($startDate ? 'Từ ' . \Carbon\Carbon::parse($startDate)->format('d/m/Y') : '') . ($endDate ? ' đến ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y') : '');
        } else {
            $dateMode = 'all';
            $dateVal = '';
            $dateLabel = 'Toàn thời gian';
        }

        $user = auth()->user();
        $query = Customer::query();

        if ($user && !$user->hasRole('Admin')) {
            $userMarketGroupIds = $user->marketGroups()->pluck('market_groups.id');
            $query->whereIn('market_group_id', $userMarketGroupIds);
        }

        if ($request->filled('filter_market_group_id')) {
            $query->where('market_group_id', $request->filter_market_group_id);
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

        $customers = $customersQuery->with(['marketGroup', 'users'])
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        $paidStartDate = $startDate;
        $paidEndDate   = $endDate;

        foreach ($customers as $c) {
            $cPaymentQuery = $c->customerPayments();
            if ($paidStartDate) $cPaymentQuery->where('payment_date', '>=', $paidStartDate);
            if ($paidEndDate)   $cPaymentQuery->where('payment_date', '<=', $paidEndDate);
            $c->period_paid = $cPaymentQuery->sum('amount');
        }

        $allPaidQuery = \App\Models\CustomerPayment::whereIn('customer_id', $matchingCustomerIds);
        if ($paidStartDate) $allPaidQuery->where('payment_date', '>=', $paidStartDate);
        if ($paidEndDate)   $allPaidQuery->where('payment_date', '<=', $paidEndDate);
        $totalPeriodPaidSum = $allPaidQuery->sum('amount');

        $baseScopedQuery = Customer::query();
        if ($user && !$user->hasRole('Admin')) {
            $userMarketGroupIds = $user->marketGroups()->pluck('market_groups.id');
            $baseScopedQuery->whereIn('market_group_id', $userMarketGroupIds);
        }

        $lostCustomersCount = (clone $baseScopedQuery)->whereHas('orders')
            ->whereDoesntHave('orders', function ($q) {
                $q->where('order_date', '>=', now()->subMonths(2)->toDateString());
            })->count();

        $newCustQuery = (clone $baseScopedQuery);
        if ($paidStartDate) $newCustQuery->where('created_at', '>=', $paidStartDate . ' 00:00:00');
        if ($paidEndDate)   $newCustQuery->where('created_at', '<=', $paidEndDate . ' 23:59:59');
        $newCustomersCount = $newCustQuery->count();

        $users = [];
        if ($user && ($user->can('assign customer') || $user->hasRole('Admin'))) {
            $users = \App\Models\User::whereDoesntHave('roles', function ($q) {
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
        }

        // Get allowed customers for select dropdown filter
        $filterQuery = Customer::query();
        if ($user && !$user->hasRole('Admin')) {
            $userMarketGroupIds = $user->marketGroups()->pluck('market_groups.id');
            $filterQuery->whereIn('market_group_id', $userMarketGroupIds);
            $marketGroups = \App\Models\MarketGroup::whereIn('id', $userMarketGroupIds)->orderBy('name')->get();
        } else {
            $marketGroups = \App\Models\MarketGroup::orderBy('name')->get();
        }
        $filterCustomers = $filterQuery->orderBy('name')->get();

        return view('customers.index', compact(
            'customers', 'perPage', 'search', 'users', 'filterCustomers', 'marketGroups',
            'startDate', 'endDate', 'paidStartDate', 'paidEndDate',
            'totalCustomersCount', 'totalDebtSum', 'totalPeriodPaidSum',
            'lostCustomersCount', 'newCustomersCount',
            'dateMode', 'dateVal', 'dateLabel'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_code'       => 'nullable|string|max:100|unique:customers,customer_code',
            'name'                => 'required|string|max:255',
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string',
            'latitude'            => 'nullable|numeric',
            'longitude'           => 'nullable|numeric',
            'province'            => 'nullable|string|max:100',
            'ward'                => 'nullable|string|max:150',
            'status'              => 'nullable|string|max:100',
            'partner_competitors' => 'nullable',
            'feedback'            => 'nullable|string',
            'personality'         => 'nullable|string',
            'workshop_scale'      => 'nullable|string|max:255',
            'customer_proposal'   => 'nullable|string',
            'sale_proposal'       => 'nullable|string',
            'initial_debt'        => 'nullable|numeric|min:0',
            'debt_limit'          => 'nullable|numeric|min:0',
            'policy'              => 'nullable|string',
            'market_group_id'     => 'nullable|exists:market_groups,id',
        ]);

        // Use provided code or auto-generate KH00001, KH00002, etc.
        if ($request->filled('customer_code')) {
            $customerCode = trim($request->customer_code);
        } else {
            $lastCustomer = Customer::orderBy('id', 'desc')->first();
            $nextNumber = $lastCustomer ? intval(substr($lastCustomer->customer_code, 2)) + 1 : 1;
            $customerCode = 'KH' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        }

        $partnerCompetitors = $request->partner_competitors;
        if (is_array($partnerCompetitors)) {
            $partnerCompetitors = implode(', ', array_filter($partnerCompetitors));
        }

        $photos = $this->processCustomerPhotos($request);

        $user = auth()->user();
        $userMarketGroupIds = ($user && !$user->hasRole('Admin')) ? $user->marketGroups()->pluck('market_groups.id') : null;

        $targetMarketGroupId = $request->market_group_id;
        if ($userMarketGroupIds !== null) {
            if ($targetMarketGroupId && !$userMarketGroupIds->contains($targetMarketGroupId)) {
                abort(403, 'Bạn chỉ có thể thêm khách hàng vào nhóm thị trường của mình.');
            }
            if (!$targetMarketGroupId && $userMarketGroupIds->isNotEmpty()) {
                $targetMarketGroupId = $userMarketGroupIds->first();
            }
        }

        $customer = Customer::create([
            'customer_code'       => $customerCode,
            'name'                => $request->name,
            'phone'               => $request->phone,
            'address'             => $request->address,
            'latitude'            => $request->latitude,
            'longitude'           => $request->longitude,
            'province'            => $request->province,
            'ward'                => $request->ward,
            'status'              => $request->status ?: 'Đang đặt hàng',
            'partner_competitors' => $partnerCompetitors,
            'feedback'            => $request->feedback,
            'personality'         => $request->personality,
            'workshop_scale'      => $request->workshop_scale,
            'customer_proposal'   => $request->customer_proposal,
            'sale_proposal'       => $request->sale_proposal,
            'debt'                => $request->initial_debt ?? 0,
            'debt_limit'          => $request->debt_limit ?? 0,
            'policy'              => $request->policy,
            'photos'              => $photos,
            'market_group_id'     => $targetMarketGroupId,
        ]);

        if ($user) {
            if ($user->can('assign customer') || $user->hasRole('Admin')) {
                if ($request->has('user_ids')) {
                    $customer->users()->sync($request->user_ids);
                }
            } else {
                $customer->users()->sync([$user->id]);
            }
        }

        // Log initial creation in CustomerHistory
        \App\Models\CustomerHistory::create([
            'customer_id'     => $customer->id,
            'user_id'         => $user?->id,
            'market_group_id' => $customer->market_group_id,
            'action'          => 'created',
            'summary'         => 'Khởi tạo thông tin khách hàng mới',
            'changes'         => null,
            'photos'          => $photos,
            'latitude'        => $request->latitude,
            'longitude'       => $request->longitude,
            'note'            => $request->input('edit_note') ?: 'Tạo mới hồ sơ khách hàng',
        ]);

        return redirect()->back()->with('success', 'Thêm khách hàng thành công.');
    }

    public function update(Request $request, Customer $customer)
    {
        abort_unless($customer->isAccessibleBy(auth()->user()), 403, 'Bạn không có quyền cập nhật khách hàng này.');

        $request->validate([
            'customer_code'       => 'nullable|string|max:100|unique:customers,customer_code,' . $customer->id,
            'name'                => 'required|string|max:255',
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string',
            'latitude'            => 'nullable|numeric',
            'longitude'           => 'nullable|numeric',
            'province'            => 'nullable|string|max:100',
            'ward'                => 'nullable|string|max:150',
            'status'              => 'nullable|string|max:100',
            'partner_competitors' => 'nullable',
            'feedback'            => 'nullable|string',
            'personality'         => 'nullable|string',
            'workshop_scale'      => 'nullable|string|max:255',
            'customer_proposal'   => 'nullable|string',
            'sale_proposal'       => 'nullable|string',
            'initial_debt'        => 'nullable|numeric|min:0',
            'debt_limit'          => 'nullable|numeric|min:0',
            'policy'              => 'nullable|string',
            'market_group_id'     => 'nullable|exists:market_groups,id',
        ]);

        $partnerCompetitors = $request->partner_competitors;
        if (is_array($partnerCompetitors)) {
            $partnerCompetitors = implode(', ', array_filter($partnerCompetitors));
        }

        $user = auth()->user();
        abort_unless($customer->isAccessibleBy($user), 403, 'Bạn không có quyền cập nhật khách hàng này.');

        if ($user && !$user->hasRole('Admin') && $request->filled('market_group_id')) {
            $userMarketGroupIds = $user->marketGroups()->pluck('market_groups.id');
            if (!$userMarketGroupIds->contains($request->market_group_id)) {
                abort(403, 'Bạn không thể chuyển khách sang nhóm thị trường khác nhóm của mình.');
            }
        }

        $oldAttributes = [
            'name'                => $customer->name,
            'phone'               => $customer->phone,
            'address'             => $customer->address,
            'province'            => $customer->province,
            'ward'                => $customer->ward,
            'status'              => $customer->status,
            'partner_competitors' => $customer->partner_competitors,
            'feedback'            => $customer->feedback,
            'personality'         => $customer->personality,
            'workshop_scale'      => $customer->workshop_scale,
            'customer_proposal'   => $customer->customer_proposal,
            'sale_proposal'       => $customer->sale_proposal,
            'debt_limit'          => $customer->debt_limit,
            'policy'              => $customer->policy,
            'market_group_id'     => $customer->market_group_id,
        ];
        $oldPhotos = is_array($customer->photos) ? $customer->photos : (is_string($customer->photos) ? (json_decode($customer->photos, true) ?: []) : []);

        $photos = $this->processCustomerPhotos($request, $oldPhotos);

        $updateData = [
            'name'                => $request->name,
            'phone'               => $request->phone,
            'address'             => $request->address,
            'latitude'            => $request->latitude,
            'longitude'           => $request->longitude,
            'province'            => $request->province,
            'ward'                => $request->ward,
            'status'              => $request->status,
            'partner_competitors' => $partnerCompetitors,
            'feedback'            => $request->feedback,
            'personality'         => $request->personality,
            'workshop_scale'      => $request->workshop_scale,
            'customer_proposal'   => $request->customer_proposal,
            'sale_proposal'       => $request->sale_proposal,
            'debt_limit'          => $request->debt_limit ?? 0,
            'policy'              => $request->policy,
            'photos'              => $photos,
            'market_group_id'     => $request->market_group_id ?? $customer->market_group_id,
        ];
        
        if ($request->has('initial_debt')) {
            $updateData['debt'] = $request->initial_debt ?? 0;
        }

        if ($request->filled('customer_code')) {
            $updateData['customer_code'] = trim($request->customer_code);
        }

        $customer->update($updateData);

        if ($user && ($user->can('assign customer') || $user->hasRole('Admin'))) {
            if ($request->has('user_ids')) {
                $customer->users()->sync($request->input('user_ids', []));
            }
        }

        // Track and log changes into CustomerHistory
        $fieldLabels = [
            'name'                => 'Tên khách hàng',
            'phone'               => 'Số điện thoại',
            'address'             => 'Địa chỉ',
            'province'            => 'Tỉnh / TP',
            'ward'                => 'Quận / Huyện / Xã',
            'status'              => 'Trạng thái',
            'partner_competitors' => 'Đối tác đối thủ',
            'feedback'            => 'Phản ánh về Gervin',
            'personality'         => 'Tính cách KH',
            'workshop_scale'      => 'Quy mô xưởng',
            'customer_proposal'   => 'Đề xuất KH',
            'sale_proposal'       => 'Đề xuất Sale',
            'debt_limit'          => 'Định mức nợ',
            'policy'              => 'Chính sách',
            'market_group_id'     => 'Nhóm thị trường',
        ];

        $changes = [];
        $changedLabels = [];
        foreach ($fieldLabels as $field => $label) {
            $oldVal = $oldAttributes[$field] ?? null;
            $newVal = $customer->$field;
            $oldStr = trim((string)$oldVal);
            $newStr = trim((string)$newVal);
            if ($oldStr !== $newStr) {
                $changes[] = [
                    'field' => $field,
                    'label' => $label,
                    'old'   => $oldVal,
                    'new'   => $newVal,
                ];
                $changedLabels[] = $label;
            }
        }

        $newPhotos = array_values(array_diff($photos, $oldPhotos));
        if (!empty($newPhotos)) {
            $changedLabels[] = 'Thêm ' . count($newPhotos) . ' ảnh mới';
        }

        $editNote = trim((string)$request->input('edit_note'));
        if (!empty($changes) || !empty($newPhotos) || !empty($editNote)) {
            $summary = !empty($changedLabels)
                ? 'Cập nhật ' . implode(', ', array_slice($changedLabels, 0, 3)) . (count($changedLabels) > 3 ? '...' : '')
                : 'Cập nhật ghi chú thông tin';

            \App\Models\CustomerHistory::create([
                'customer_id'     => $customer->id,
                'user_id'         => $user?->id,
                'market_group_id' => $customer->market_group_id,
                'action'          => 'updated',
                'summary'         => $summary,
                'changes'         => $changes,
                'photos'          => $newPhotos,
                'latitude'        => $request->latitude,
                'longitude'       => $request->longitude,
                'note'            => $editNote ?: null,
            ]);
        }

        return redirect()->back()->with('success', 'Cập nhật khách hàng thành công.');
    }

    private function processCustomerPhotos(Request $request, $existingPhotos = [])
    {
        $photos = is_array($existingPhotos) ? $existingPhotos : [];

        // Nếu form gửi danh sách ảnh cũ cần giữ lại
        if ($request->has('keep_photos')) {
            $keep = (array) $request->keep_photos;
            $photos = array_values(array_intersect($photos, $keep));
        }

        $uploadDir = public_path('uploads/customers');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // 1. Ảnh base64 đã qua canvas đóng dấu watermark timestamp
        if ($request->filled('photo_data')) {
            $base64List = (array) $request->photo_data;
            foreach ($base64List as $b64) {
                if (empty($b64)) continue;
                if (preg_match('/^data:image\/(\w+);base64,/', $b64, $type)) {
                    $raw = substr($b64, strpos($b64, ',') + 1);
                    $decoded = base64_decode($raw);
                    if ($decoded !== false) {
                        $ext = strtolower($type[1]);
                        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) $ext = 'jpg';
                        $filename = 'kh_' . uniqid() . '_' . time() . '.' . $ext;
                        file_put_contents($uploadDir . '/' . $filename, $decoded);
                        $photos[] = 'uploads/customers/' . $filename;
                    }
                }
            }
        }

        // 2. File upload trực tiếp
        if ($request->hasFile('photos_files')) {
            foreach ((array)$request->file('photos_files') as $file) {
                if ($file && $file->isValid()) {
                    $ext = $file->getClientOriginalExtension() ?: 'jpg';
                    $filename = 'kh_' . uniqid() . '_' . time() . '.' . $ext;
                    $file->move($uploadDir, $filename);
                    $photos[] = 'uploads/customers/' . $filename;
                }
            }
        }

        return array_values(array_unique($photos));
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
        $customer->load('marketGroup');

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
                'id'            => $o->id,
                'order_code'    => $o->order_code,
                'customer_name' => $o->customer_name,
                'order_date'    => $o->order_date,
                'status'        => $o->status,
                'status_label'  => $statusLabels[$o->status] ?? $o->status,
                'total_amount'  => round($o->total_amount, -3),
                'paid'          => $paid,
                'debt'          => in_array($o->status, ['draft', 'cancelled', 'pending']) ? 0 : max(0, round($o->total_amount ?? 0, -3) - $paid),
                'payments_count'=> $o->orderPayments->count(),
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

    public function getHistories(Customer $customer)
    {
        $user = auth()->user();
        abort_unless($customer->isAccessibleBy($user), 403, 'Bạn không có quyền truy cập khách hàng này.');

        $histories = $customer->histories()
            ->with('user')
            ->get()
            ->map(function ($h) {
                return [
                    'id'         => $h->id,
                    'action'     => $h->action,
                    'summary'    => $h->summary,
                    'changes'    => $h->changes,
                    'photos'     => $h->photos,
                    'latitude'   => $h->latitude,
                    'longitude'  => $h->longitude,
                    'note'       => $h->note,
                    'user_name'  => $h->user?->name ?? 'Hệ thống',
                    'created_at' => $h->created_at->format('H:i d/m/Y'),
                    'diff'       => $h->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'customer' => [
                'id'            => $customer->id,
                'customer_code' => $customer->customer_code,
                'name'          => $customer->name,
                'phone'         => $customer->phone,
                'status'        => $customer->status,
            ],
            'histories' => $histories,
        ]);
    }
}

