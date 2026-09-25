<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\AcrylicOrderService;
use App\Services\MinLateOrderService;
use App\Services\GlassOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    private const ORDER_TYPES = ['acrylic', 'glass', 'min_late'];

    protected $acrylicOrderService;
    protected $minLateOrderService;
    protected $glassOrderService;

    public function __construct(
        AcrylicOrderService $acrylicOrderService,
        MinLateOrderService $minLateOrderService,
        GlassOrderService $glassOrderService
    ) {
        $this->acrylicOrderService = $acrylicOrderService;
        $this->minLateOrderService = $minLateOrderService;
        $this->glassOrderService = $glassOrderService;

        $this->middleware('permission:view order',   ['only' => ['index', 'show']]);
        $this->middleware('permission:add order',    ['only' => ['create', 'createByType', 'store']]);
        $this->middleware('permission:edit order',   ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete order', ['only' => ['destroy', 'bulkDestroy']]);
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search', '');

        $user = auth()->user();
        $query = Order::with('customer')
            ->withCount(['manufactureOrders' => function ($q) {
                $q->whereIn('status', ['stamps_received', 'in_production', 'completed']);
            }])
            ->when($user && !$user->hasRole('Admin'), function ($q) use ($user) {
                $userMarketGroupIds = $user->marketGroups()->pluck('market_groups.id');
                $q->whereHas('customer', function ($cq) use ($userMarketGroupIds) {
                    $cq->whereIn('market_group_id', $userMarketGroupIds);
                });
            })
            ->when($request->input('filter_status') !== 'draft', function ($q) {
                $q->where('status', '!=', 'draft');
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('order_code', 'like', "%$search%")
                        ->orWhere('customer_name', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%")
                        ->orWhereHas('customer', function ($cq) use ($search) {
                            $cq->where('name', 'like', "%$search%")
                               ->orWhere('customer_code', 'like', "%$search%");
                        });
                });
            })

            ->when($request->filled('filter_customer_id'), function ($q) use ($request) {
                $q->where('customer_id', $request->filter_customer_id);
            })
            ->when($request->filled('filter_status'), function ($q) use ($request) {
                $q->where('status', $request->filter_status);
            })
            ->when($request->filled('filter_type'), function ($q) use ($request) {
                $q->where('type', $request->filter_type);
            });

        // Filter Date Mode: day | month | year | all | custom
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

        if ($startDate) {
            $query->whereDate('order_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('order_date', '<=', $endDate);
        }

        // Compute totals before pagination
        $totalsQuery = clone $query;
        $totalOrdersCount = $totalsQuery->count();
        $totalAmountSum = $totalsQuery->sum('total_amount');
        
        $matchingOrderIds = $totalsQuery->pluck('id');
        $totalPaidSum = DB::table('customer_payments')
            ->whereIn('order_id', $matchingOrderIds)
            ->sum('amount');
        $totalDebtSum = max(0, $totalAmountSum - $totalPaidSum);

        $deadlineThreshold = (int) \Illuminate\Support\Facades\Cache::get('deadline_warning_days', 0);
        
        if ($deadlineThreshold > 0) {
            // Đưa các đơn bị cảnh báo lên đầu (true = 1, false = 0 -> xếp DESC để 1 lên trước)
            $query->orderByRaw("
                CASE 
                    WHEN status NOT IN ('completed', 'cancelled', 'draft') 
                     AND deadline IS NOT NULL 
                     AND order_date <= NOW()
                     AND deadline <= DATE_ADD(NOW(), INTERVAL ? HOUR)
                    THEN 1
                    ELSE 0
                END DESC
            ", [$deadlineThreshold * 24])
            // Trong nhóm các đơn cảnh báo, đơn nào hạn gần nhất (hoặc quá hạn nhiều nhất) sẽ lên trước
            ->orderByRaw("
                CASE 
                    WHEN status NOT IN ('completed', 'cancelled', 'draft') 
                     AND deadline IS NOT NULL 
                     AND order_date <= NOW()
                     AND deadline <= DATE_ADD(NOW(), INTERVAL ? HOUR)
                    THEN deadline
                    ELSE NULL
                END ASC
            ", [$deadlineThreshold * 24]);
        }

        $orders = $query->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        if ($user && !$user->hasRole('Admin')) {
            $userMarketGroupIds = $user->marketGroups()->pluck('market_groups.id');
            $customers = \App\Models\Customer::whereIn('market_group_id', $userMarketGroupIds)->orderBy('name')->get();
        } else {
            $customers = \App\Models\Customer::orderBy('name')->get();
        }

        $draftOrdersCount = Order::where('status', 'draft')
            ->when($user && !$user->hasRole('Admin'), function ($q) use ($user) {
                $userMarketGroupIds = $user->marketGroups()->pluck('market_groups.id');
                $q->whereHas('customer', function ($cq) use ($userMarketGroupIds) {
                    $cq->whereIn('market_group_id', $userMarketGroupIds);
                });
            })
            ->count();

        return view('orders.index', compact(
            'orders', 
            'perPage', 
            'search',
            'totalOrdersCount',
            'totalAmountSum',
            'totalPaidSum',
            'totalDebtSum',
            'customers',
            'draftOrdersCount',
            'dateMode',
            'dateVal',
            'dateLabel',
            'startDate',
            'endDate'
        ));
    }

    public function show(Order $order)
    {
        $user = auth()->user();
        if ($user && !$user->hasRole('Admin')) {
            if (!$order->customer || !$order->customer->isAccessibleBy($user)) {
                abort(403, 'Bạn không có quyền truy cập đơn hàng này.');
            }
        }

        $order->load(['supplies.items.codes', 'supplies.minLateItems.codes', 'supplies.glassItems.codes', 'paymentDetails', 'orderPayments.creator']);
        $acrylicOrder = $order;
        $exportData = $this->getExportData($order);
        return view('orders.show', compact('acrylicOrder', 'exportData'));
    }

    public function bulkExportData(\Illuminate\Http\Request $request)
    {
        $orderIds = $request->input('order_ids', []);
        $orders = Order::whereIn('id', $orderIds)->with(['supplies.items.codes', 'supplies.minLateItems.codes', 'supplies.glassItems.codes', 'paymentDetails', 'orderPayments.creator', 'customer'])->get();
        
        $exportDataArray = [];
        foreach ($orders as $order) {
            $exportDataArray[] = $this->getExportData($order);
        }
        
        return response()->json($exportDataArray);
    }

    public function getExportData(Order $acrylicOrder)
    {
        return [
            'order_code' => $acrylicOrder->order_code,
            'created_at' => $acrylicOrder->created_at->format('d/m/Y H:i'),
            'type' => $acrylicOrder->type,
            'customer_name' => $acrylicOrder->customer_name,
            'phone' => $acrylicOrder->phone,
            'order_date' => $acrylicOrder->order_date ? \Carbon\Carbon::parse($acrylicOrder->order_date)->format('Y-m-d H:i:s') : null,
            'deadline' => $acrylicOrder->deadline ? \Carbon\Carbon::parse($acrylicOrder->deadline)->format('Y-m-d H:i:s') : null,
            'address' => $acrylicOrder->address,
            'notes' => $acrylicOrder->notes,
            'relation_type' => $acrylicOrder->relation_type,
            'parent_id' => $acrylicOrder->parent_id,
            'customer_policy' => $acrylicOrder->customer_policy,
            'discount_percent' => $acrylicOrder->discount_percent ?? 0,
            'discount_amount' => $acrylicOrder->discount_amount ?? 0,
            'vat_percent' => $acrylicOrder->vat_percent ?? 0,
            'vat_amount' => $acrylicOrder->vat_amount ?? 0,
            'total_amount' => round($acrylicOrder->total_amount, -3),
            'delivery_days' => $acrylicOrder->delivery_days ?? ($acrylicOrder->type === 'glass' ? 5 : 2),
            'customer_debt_info' => call_user_func(function() use ($acrylicOrder) {
                if (!$acrylicOrder->customer) return null;
                $thisOrderTotal = round($acrylicOrder->total_amount, -3);
                $thisOrderPaid = $acrylicOrder->orderPayments->sum('amount');
                $thisOrderUnpaid = 0;
                if (!in_array($acrylicOrder->status, ['draft', 'cancelled', 'pending'])) {
                    $thisOrderUnpaid = max(0, $thisOrderTotal - $thisOrderPaid);
                }
                $currentTotalDebt = $acrylicOrder->customer->total_debt;
                $todayPaid = $acrylicOrder->customer->customerPayments()->whereDate('payment_date', \Carbon\Carbon::today())->sum('amount');
                $oldDebt = max(0, $currentTotalDebt - $thisOrderUnpaid + $todayPaid);
                $totalCombinedDebt = $oldDebt + $thisOrderTotal - $todayPaid;
                
                if ($oldDebt == 0 && $todayPaid == 0 && $totalCombinedDebt == 0) return null;
                
                return [
                    'old_debt' => $oldDebt,
                    'this_order_paid' => $todayPaid, // still named this_order_paid in JS, but sends today's paid
                    'total_combined_debt' => max(0, $totalCombinedDebt),
                ];
            }),
            'supplies' => $acrylicOrder->supplies->map(function ($supply) use ($acrylicOrder) {
                $items = [];
                if ($acrylicOrder->type === 'min_late') {
                    $items = $supply->minLateItems->map(function ($item) {
                        $sizes = $item->size ?? [];
                        if (is_string($sizes)) {
                            $sizes = json_decode($sizes, true) ?? [];
                        }
                        $edgeGluing = $item->edge_gluing ?? [];
                        if (is_string($edgeGluing)) {
                            $edgeGluing = json_decode($edgeGluing, true) ?? [];
                        }
                        return [
                            'product_code' => $item->product_code,
                            'name' => $item->product_name ?? $item->name,
                            'thickness' => $item->thickness,
                            'quantity' => $item->quantity,
                            'height' => $sizes['height'] ?? null,
                            'width' => $sizes['width'] ?? null,
                            'edge_gluing' => $edgeGluing,
                            'bevel' => $item->bevel,
                            'straight_paste_length' => $item->straight_paste_length,
                            'beveled_length' => $item->beveled_length,
                            'vat_moi_length' => $item->vat_moi_length,
                            'ban_rong_40_59' => $item->ban_rong_40_59,
                            'ban_rong_17_39' => $item->call_rong_17_39 ?? $item->ban_rong_17_39,
                            'ban_rong_25_35' => $item->ban_rong_25_35,
                            'beveled_handle' => $item->beveled_handle,
                            'cnc' => $item->cnc,
                            'direction' => $item->direction,
                            'notes' => $item->notes,
                        ];
                    });
                } elseif ($acrylicOrder->type === 'glass') {
                    $items = $supply->glassItems->map(function ($item) {
                        return [
                            'product_code' => $item->product_code,
                            'product_name' => $item->product_name,
                            'thickness' => $item->thickness,
                            'wing_opening_direction' => $item->wing_opening_direction,
                            'aluminum_color' => $item->aluminum_color,
                            'glass_color' => $item->glass_color,
                            'height' => $item->height,
                            'width' => $item->width,
                            'unit' => $item->unit ?? 'cánh',
                            'wing_quantity' => $item->wing_quantity,
                            'area_m2' => $item->area_m2,
                            'unit_price' => $item->unit_price,
                            'total_price' => $item->total_price,
                            'notes' => $item->notes,
                        ];
                    });
                } else {
                    $items = $supply->items->map(function ($item) {
                        return [
                            'product_code' => $item->product_code,
                            'product_codes' => $item->codes->pluck('product_id')->toArray(),
                            'product_name' => $item->product_name,
                            'thickness' => $item->thickness,
                            'quantity' => $item->quantity,
                            'height' => $item->height,
                            'width' => $item->width,
                            'edge_bevel' => $item->edge_bevel,
                            'grain_direction' => $item->grain_direction,
                            'wing_area' => $item->wing_area,
                            'molding_length' => $item->molding_length,
                            'bevel' => $item->bevel,
                            'vertical_grain_cnc' => $item->vertical_grain_cnc,
                            'offset_left' => $item->offset_left,
                            'offset_right' => $item->offset_right,
                            'offset_top' => $item->offset_top,
                            'offset_bottom' => $item->offset_bottom,
                            'mill_left' => $item->mill_left,
                            'mill_right' => $item->mill_right,
                            'mill_top' => $item->mill_top,
                            'mill_bottom' => $item->mill_bottom,
                            'mill_width' => $item->mill_width,
                            'mill_depth' => $item->mill_depth,
                            'mill_left_2' => $item->mill_left_2,
                            'mill_right_2' => $item->mill_right_2,
                            'mill_top_2' => $item->mill_top_2,
                            'mill_bottom_2' => $item->mill_bottom_2,
                            'mill_width_2' => $item->mill_width_2,
                            'mill_depth_2' => $item->mill_depth_2,
                            'unit_price' => $item->unit_price,
                            'total_price' => $item->total_price,
                            'notes' => $item->notes,
                        ];
                    });
                }
                return [
                    'order_supply_code' => $supply->order_supply_code,
                    'supply_name' => $supply->supply_name,
                    'quantity' => $supply->quantity,
                    'items' => $items
                ];
            }),
            'payment_details' => isset($acrylicOrder->paymentDetails) ? $acrylicOrder->paymentDetails->map(function ($detail) {
                return [
                    'name' => $detail->name,
                    'unit' => $detail->unit,
                    'quantity' => $detail->quantity,
                    'price' => $detail->price,
                    'price_only' => $detail->price_only,
                    'total' => $detail->total,
                ];
            }) : []
        ];
    }

    public function create()
    {
        return view('orders.create');
    }

    public function createByType(Request $request, string $type)
    {
        $relationType = $request->query('relation_type'); // ví dụ: rework
        if ($relationType === 'rework') {
            $type = 'min_late';
        }
        abort_unless(in_array($type, self::ORDER_TYPES, true), 404);

        $orderType = $type;
        
        $oldDraftId = old('draft_order_id');
        if ($oldDraftId) {
            $acrylicOrder = Order::where('status', 'draft')
                ->when($relationType, function ($q) use ($relationType) {
                    $q->where('relation_type', $relationType);
                })
                ->find($oldDraftId);
        }
        
        if (!isset($acrylicOrder) || !$acrylicOrder) {
            $acrylicOrder = $this->createDraftOrder($type, $relationType);
        }

        $isDraftCreate = true;
        $woodBoardPrices = \App\Models\WoodBoardPrice::orderBy('code', 'asc')->get();
        $cncTemplates = \App\Models\CncTemplate::orderBy('id', 'asc')->get();
        $glassPrices = \App\Models\GlassPrice::orderBy('code', 'asc')->get();
        $minLatePrices = \App\Models\MinLatePrice::orderBy('category_name', 'asc')->orderBy('stt', 'asc')->get();

        return view('orders.create', compact('orderType', 'acrylicOrder', 'isDraftCreate', 'woodBoardPrices', 'cncTemplates', 'glassPrices', 'minLatePrices'));
    }

    public function edit(Order $order)
    {
        $user = auth()->user();
        if ($user && !$user->hasRole('Admin')) {
            if (!$order->customer || !$order->customer->isAccessibleBy($user)) {
                abort(403, 'Bạn không có quyền truy cập đơn hàng này.');
            }
        }

        if ($order->status === 'in_production') {
            return redirect()->route('orders.index')->with('error', 'Đơn hàng đang trong quá trình sản xuất, không thể chỉnh sửa.');
        }
        if ($order->status === 'cancelled') {
            return redirect()->route('orders.index')->with('error', 'Đơn hàng đã bị hủy, không thể chỉnh sửa.');
        }
        $order->load(['supplies.items', 'supplies.minLateItems', 'supplies.glassItems', 'paymentDetails']);
        $acrylicOrder = $order;
        $isDraftCreate = false;
        $woodBoardPrices = \App\Models\WoodBoardPrice::orderBy('code', 'asc')->get();
        $cncTemplates = \App\Models\CncTemplate::orderBy('id', 'asc')->get();
        $glassPrices = \App\Models\GlassPrice::orderBy('code', 'asc')->get();
        $minLatePrices = \App\Models\MinLatePrice::orderBy('category_name', 'asc')->orderBy('stt', 'asc')->get();
        return view('orders.edit', compact('acrylicOrder', 'woodBoardPrices', 'cncTemplates', 'glassPrices', 'minLatePrices', 'isDraftCreate'));
    }

    public function store(Request $request)
    {
        if ($request->filled('supplies_json')) {
            $supplies = json_decode($request->input('supplies_json'), true);
            if (is_array($supplies)) {
                $supplies = $this->cleanEmptyStrings($supplies);
                $request->merge(['supplies' => $supplies]);
            }
        }

        $request->validate([
            'draft_order_id' => 'required|integer',
            'type' => 'required|in:acrylic,glass,min_late',
        ]);

        $draftOrder = Order::where('status', 'draft')->findOrFail($request->draft_order_id);

        $user = auth()->user();
        if ($user && !$user->hasRole('Admin') && $request->filled('customer_id')) {
            $cust = \App\Models\Customer::find($request->customer_id);
            if ($cust && !$cust->isAccessibleBy($user)) {
                abort(403, 'Bạn chỉ có thể tạo đơn cho khách hàng thuộc nhóm thị trường của mình.');
            }
        }

        if ($draftOrder->type !== $request->type) {
            abort(422, 'Loại đơn không khớp với đơn nháp.');
        }

        $service = $this->serviceFor($request->type);

        $isDraftSave = $request->input('action') === 'draft';
        if ($isDraftSave) {
            $request->merge(['status' => 'draft']);
        }

        $rules = $service->getStoreRules();
        if ($isDraftSave) {
            foreach ($rules as $k => $v) {
                if (is_string($v) && str_contains($v, 'required')) {
                    $rules[$k] = str_replace(['required|', '|required', 'required'], ['nullable|', '', 'nullable'], $v);
                }
            }
            if (!$request->filled('customer_name')) {
                $request->merge(['customer_name' => 'Đơn nháp ' . $draftOrder->order_code]);
            }
        }

        $request->validate($rules);
        $service->update($request, $draftOrder);

        if ($isDraftSave) {
            return redirect()->route('orders.index', ['filter_status' => 'draft'])->with('success', 'Đã lưu đơn nháp thành công.');
        }

        return redirect()->route('orders.index')->with('success', 'Tạo đơn hàng thành công.');
    }

    public function update(Request $request, Order $order)
    {
        $user = auth()->user();
        if ($user && !$user->hasRole('Admin')) {
            if (!$order->customer || !$order->customer->isAccessibleBy($user)) {
                abort(403, 'Bạn không có quyền truy cập đơn hàng này.');
            }
            if ($request->filled('customer_id')) {
                $targetCust = \App\Models\Customer::find($request->customer_id);
                if ($targetCust && !$targetCust->isAccessibleBy($user)) {
                    abort(403, 'Bạn chỉ có thể chọn khách hàng thuộc nhóm thị trường của mình.');
                }
            }
        }

        if ($order->status === 'in_production') {
            return redirect()->route('orders.index')->with('error', 'Đơn hàng đang trong quá trình sản xuất, không thể chỉnh sửa.');
        }
        if ($order->status === 'cancelled') {
            return redirect()->route('orders.index')->with('error', 'Đơn hàng đã bị hủy, không thể chỉnh sửa.');
        }

        if ($request->filled('supplies_json')) {
            $supplies = json_decode($request->input('supplies_json'), true);
            if (is_array($supplies)) {
                $supplies = $this->cleanEmptyStrings($supplies);
                $request->merge(['supplies' => $supplies]);
            }
        }

        $isDraftSave = $request->input('action') === 'draft' || ($request->status === 'draft' && $order->status === 'draft');
        if ($isDraftSave) {
            $request->merge(['status' => 'draft']);
        }

        $service = $this->serviceFor($request->type);
        $rules = $service->getUpdateRules();

        if ($isDraftSave) {
            foreach ($rules as $k => $v) {
                if (is_string($v) && str_contains($v, 'required')) {
                    $rules[$k] = str_replace(['required|', '|required', 'required'], ['nullable|', '', 'nullable'], $v);
                }
            }
            if (!$request->filled('customer_name')) {
                $request->merge(['customer_name' => 'Đơn nháp ' . $order->order_code]);
            }
        }

        $request->validate($rules);
        $service->update($request, $order);

        if ($isDraftSave) {
            return redirect()->route('orders.index', ['filter_status' => 'draft'])->with('success', 'Đã lưu đơn nháp thành công.');
        }

        return redirect()->route('orders.index')->with('success', 'Cập nhật đơn hàng thành công.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:transferred,cancelled',
        ]);

        $status = $request->status;

        if ($status === 'transferred') {
            if (!in_array($order->status, ['pending', 'draft'])) {
                return redirect()->back()->with('error', 'Trạng thái đơn hàng không hợp lệ để chuyển sản xuất.');
            }
            $order->status = 'transferred';
            $order->save();
            return redirect()->back()->with('success', 'Chuyển sản xuất thành công đơn hàng ' . $order->order_code);
        }

        if ($status === 'cancelled') {
            if ($order->status === 'cancelled') {
                return redirect()->back()->with('error', 'Đơn hàng đã bị hủy trước đó.');
            }
            $order->status = 'cancelled';
            $order->save();
            return redirect()->back()->with('success', 'Hủy thành công đơn hàng ' . $order->order_code);
        }

        return redirect()->back()->with('error', 'Thao tác không hợp lệ.');
    }

    public function destroy(Request $request, Order $order)
    {
        $user = auth()->user();
        if ($user && !$user->hasRole('Admin')) {
            if (!$order->customer || !$order->customer->isAccessibleBy($user)) {
                abort(403, 'Bạn không có quyền truy cập đơn hàng này.');
            }
        }

        $order->delete();
        return redirect()
            ->route('orders.index', $request->only([
                'search',
                'per_page',
                'filter_order_code',
                'filter_customer_name',
                'filter_status',
            ]))
            ->with('success', 'Xóa đơn hàng thành công.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer|distinct|exists:orders,id',
        ]);

        $orderIds = array_values(array_unique($validated['order_ids']));

        $deletedCount = DB::transaction(function () use ($orderIds) {
            $orders = Order::whereIn('id', $orderIds)->get();
            $deletedCount = 0;

            foreach ($orders as $order) {
                $order->delete();
                $deletedCount++;
            }

            return $deletedCount;
        });

        return redirect()
            ->route('orders.index', $request->only([
                'search',
                'per_page',
                'filter_order_code',
                'filter_customer_name',
                'filter_status',
            ]))
            ->with('success', "Xóa {$deletedCount} đơn hàng thành công.");
    }

    public function productionStats(Order $order)
    {
        // Chỉ cho phép xem khi đơn đã có Lệnh sản xuất được duyệt hoàn tất quy trình phê duyệt
        $hasApprovedMO = $order->manufactureOrders()
            ->whereIn('status', ['stamps_received', 'in_production', 'completed'])
            ->exists();

        if (!$hasApprovedMO) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng chưa có Lệnh sản xuất được duyệt.',
                'data' => []
            ]);
        }

        $order->load(['supplies.items.codes', 'supplies.minLateItems.codes', 'supplies.glassItems.codes']);
        $stats = [];

        $today = now()->format('Y-m-d');
        $day2Date = $today; // Hôm nay là ngày 2
        $day1Date = now()->subDay()->format('Y-m-d'); // Hôm qua là ngày 1

        foreach ($order->supplies as $supply) {
            $day1Count = 0;
            $day2Count = 0;
            $inProductionCount = 0;
            $totalCount = 0;
            $cncCount = 0;
            $completedTotalCount = 0;

            if ($order->type === 'glass') {
                $items = $supply->glassItems;
                $totalCount = $items->sum('wing_quantity') ?: $items->sum('quantity');
                $cncCount = $totalCount;
            } elseif ($order->type === 'min_late') {
                $items = $supply->minLateItems;
                $totalCount = $items->sum('quantity');
                $cncCount = $items->where('cnc', 1)->sum('quantity');
            } else {
                $items = $supply->items;
                $totalCount = $items->sum('quantity');
                $cncCount = $items->filter(fn($i) => !empty($i->vertical_grain_cnc))->sum('quantity');
            }

            foreach ($items as $item) {
                foreach ($item->codes as $code) {
                    $statusLogs = $code->status ?? [];
                    if (is_string($statusLogs)) {
                        $statusLogs = json_decode($statusLogs, true) ?? [];
                    }

                    $completedTime = null;
                    $hasStarted = false;
                    foreach ($statusLogs as $log) {
                        $action = mb_strtolower($log['action'] ?? '');
                        if ($action === 'hoàn thành qc' || $action === 'hoàn thành làm đẹp') {
                            $completedTime = $log['time'] ?? null;
                        }
                        // Chỉ tính là đang sản xuất khi đã thực sự quét vào các công đoạn gia công
                        if (
                            str_contains($action, 'ép') ||
                            str_contains($action, 'cnc') ||
                            str_contains($action, 'dán cạnh') ||
                            str_contains($action, 'làm đẹp') ||
                            str_contains($action, 'lỗi')
                        ) {
                            $hasStarted = true;
                        }
                    }

                    if ($completedTime) {
                        $completedTotalCount++;
                        $completedDate = date('Y-m-d', strtotime($completedTime));
                        if ($completedDate === $day1Date) {
                            $day1Count++;
                        } elseif ($completedDate === $day2Date) {
                            $day2Count++;
                        }
                    } elseif ($hasStarted) {
                        $inProductionCount++;
                    }
                }
            }

            $remainingCount = max(0, $totalCount - $completedTotalCount);

            $stats[] = [
                'order_supply_code' => $supply->order_supply_code,
                'supply_name' => $supply->supply_name,
                'total_count' => $totalCount,
                'cnc_count' => $cncCount,
                'day1_count' => $day1Count,
                'in_production_count' => $inProductionCount,
                'day2_count' => $day2Count,
                'remaining_count' => $remainingCount,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
            'order_info' => [
                'order_date' => $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('Y-m-d H:i:s') : '—',
                'delivery_days' => $order->delivery_days ?? '—',
                'deadline' => $order->deadline ? \Carbon\Carbon::parse($order->deadline)->format('Y-m-d H:i:s') : '—',
            ]
        ]);
    }

    public function serveImage($filename)
    {
        if (str_contains($filename, '..')) abort(403);

        $filePath = 'anh-don-hang/' . $filename;
        
        if (!Storage::disk('private')->exists($filePath)) {
            abort(404);
        }

        $path     = storage_path('app/private/' . $filePath);
        $mimeType = Storage::disk('private')->mimeType($filePath);

        return response()->file($path, [
            'Content-Type'        => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($filename) . '"',
        ]);
    }

    public function discardDraft(Request $request)
    {
        $id = $request->input('draft_order_id');
        if (!$id) return response()->json(['ok' => false], 400);

        $draft = Order::where('status', 'draft')->find($id);
        if ($draft) {
            $draft->delete();
        }

        return response()->json(['ok' => true]);
    }

    private function generateOrderCode(): string
    {
        $dateStr = now()->format('dmy');
        $prefix = $dateStr . '_';

        $orders = Order::where('order_code', 'like', $prefix . '%')
            ->lockForUpdate()
            ->pluck('order_code');

        $maxNumber = 0;
        foreach ($orders as $code) {
            $parts = explode('_', $code);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $num = intval($parts[1]);
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }

        return $prefix . ($maxNumber + 1);
    }

    private function createDraftOrder(string $type, ?string $relationType = null): Order
    {
        return DB::transaction(function () use ($type, $relationType) {
            $orderCode = $this->generateOrderCode();

            return Order::create([
                'order_code' => $orderCode,
                'type' => $type,
                'relation_type' => $relationType,
                'customer_name' => '',
                'total_amount' => 0,
                'status' => 'draft',
            ]);
        });
    }

    private function serviceFor(?string $type)
    {
        return match ($type) {
            'min_late' => $this->minLateOrderService,
            'glass' => $this->glassOrderService,
            default => $this->acrylicOrderService,
            'acrylic' => $this->acrylicOrderService,
        };
    }

    /**
     * Recursively convert empty string elements to null in request arrays.
     */
    private function cleanEmptyStrings(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->cleanEmptyStrings($value);
            } elseif ($value === '') {
                $array[$key] = null;
            }
        }
        return $array;
    }

    public function saveDeadlineSetting(Request $request)
    {
        $days = max(0, min(30, (int) $request->input('deadline_warning_days', 0)));
        \Illuminate\Support\Facades\Cache::forever('deadline_warning_days', $days);
        return redirect()->route('orders.index')->with('success', 'Đã lưu cài đặt cảnh báo: ' . $days . ' ngày');
    }

    /**
     * Tạo đơn hàng sửa tấm từ đơn hàng gốc.
     */
    /**
     * Tạo đơn hàng sửa tấm từ đơn hàng gốc (xử lý POST từ modal).
     */
    public function createReworkOrder(Request $request, Order $order)
    {
        $request->validate([
            'item_ids' => 'required|array|min:1',
        ]);

        $selectedPieces = $request->input('selected_pieces', []);
        if (empty($selectedPieces)) {
            foreach ($request->input('item_ids') as $id) {
                $selectedPieces[] = ['item_id' => (int)$id, 'piece_index' => 0];
            }
        }

        session([
            'rework_selected_pieces_' . $order->id => $selectedPieces,
            'rework_item_ids_' . $order->id => $request->input('item_ids')
        ]);

        return response()->json([
            'ok' => true,
            'redirect_url' => route('orders.rework-create', $order->id)
        ]);
    }

    /**
     * Tạo đơn hàng bảo hành từ đơn hàng gốc (xử lý POST từ modal).
     */
    public function createWarrantyOrder(Request $request, Order $order)
    {
        $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'required|integer',
        ]);

        session(['warranty_item_ids_' . $order->id => $request->input('item_ids')]);

        return response()->json([
            'ok' => true,
            'redirect_url' => route('orders.warranty-create', $order->id)
        ]);
    }

    /**
     * Hiển thị trang tạo đơn nháp tận dụng tấm (GET).
     */
    public function reuseCreateForm(Order $order)
    {
        $acrylicOrder = Order::where('status', 'draft')
            ->where('parent_id', $order->id)
            ->where('relation_type', 'reuse')
            ->first();

        if (!$acrylicOrder) {
            $acrylicOrder = $this->buildReuseDraftOrder($order);
        }

        $orderType = $order->type;
        $isDraftCreate = true;
        $woodBoardPrices = \App\Models\WoodBoardPrice::orderBy('code', 'asc')->get();
        $cncTemplates = \App\Models\CncTemplate::orderBy('id', 'asc')->get();
        $glassPrices = \App\Models\GlassPrice::orderBy('code', 'asc')->get();
        $minLatePrices = \App\Models\MinLatePrice::orderBy('category_name', 'asc')->orderBy('stt', 'asc')->get();

        return view('orders.create', compact('orderType', 'acrylicOrder', 'isDraftCreate', 'woodBoardPrices', 'cncTemplates', 'glassPrices', 'minLatePrices'));
    }

    /**
     * Tạo bản ghi đơn nháp tận dụng tấm trong DB.
     */
    private function buildReuseDraftOrder(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $orderCode = $this->generateOrderCode();

            return Order::create([
                'parent_id' => $order->id,
                'relation_type' => 'reuse',
                'order_code' => $orderCode,
                'type' => $order->type,
                'customer_id' => $order->customer_id,
                'customer_name' => $order->customer_name,
                'phone' => $order->phone,
                'address' => $order->address,
                'discount_percent' => $order->discount_percent ?? 0,
                'discount_amount' => 0,
                'vat_percent' => $order->vat_percent ?? 0,
                'vat_amount' => 0,
                'total_amount' => 0,
                'customer_policy' => $order->customer_policy,
                'order_date' => now(),
                'delivery_days' => null,
                'deadline' => null,
                'status' => 'draft',
                'notes' => '[Tận dụng tấm] Đơn liên kết của ' . $order->order_code,
            ]);
        });
    }

    /**
     * Hiển thị trang tạo đơn nháp sửa tấm (GET).
     * Luôn sử dụng form đơn Min-late!
     */
    public function reworkCreateForm(Order $order)
    {
        $selectedPieces = session('rework_selected_pieces_' . $order->id, []);
        $itemIds = session('rework_item_ids_' . $order->id, []);

        if (empty($selectedPieces) && empty($itemIds)) {
            return redirect()->route('orders.show', $order->id)->with('error', 'Chưa chọn tấm gỗ cần sửa.');
        }

        $oldDraftId = old('draft_order_id');
        if ($oldDraftId) {
            $acrylicOrder = Order::where('status', 'draft')->where('relation_type', 'rework')->find($oldDraftId);
        }

        if (!isset($acrylicOrder) || !$acrylicOrder) {
            $acrylicOrder = $this->buildReworkDraftOrder($order, !empty($selectedPieces) ? $selectedPieces : $itemIds);
        }

        // Đơn sửa tấm PHẢI LUÔN DÙNG FORM ĐƠN MINLATE
        $orderType = 'min_late';
        $isDraftCreate = true;
        $woodBoardPrices = \App\Models\WoodBoardPrice::orderBy('code', 'asc')->get();
        $cncTemplates = \App\Models\CncTemplate::orderBy('id', 'asc')->get();
        $glassPrices = \App\Models\GlassPrice::orderBy('code', 'asc')->get();
        $minLatePrices = \App\Models\MinLatePrice::orderBy('category_name', 'asc')->orderBy('stt', 'asc')->get();

        return view('orders.create', compact('orderType', 'acrylicOrder', 'isDraftCreate', 'woodBoardPrices', 'cncTemplates', 'glassPrices', 'minLatePrices'));
    }

    /**
     * Tạo bản ghi đơn nháp sửa tấm trong DB.
     * Đơn sửa tấm luôn là type = 'min_late'.
     * Tách riêng từng tấm (mỗi tấm số lượng = 1).
     * Ghi chú format: KTC: {cao} x {rộng}\nYêu cầu: Giữ nguyên cạnh vát.
     * Tự động sinh 3 dòng dịch vụ: LIC1, ML48, ML49.
     */
    private function buildReworkDraftOrder(Order $order, array $selectedPieces): Order
    {
        return DB::transaction(function () use ($order, $selectedPieces) {
            $orderCode = $this->generateOrderCode();

            $reworkOrder = Order::create([
                'parent_id'        => $order->id,
                'relation_type'    => 'rework',
                'order_code'       => $orderCode,
                'type'             => 'min_late', // LUÔN LUÔN DÙNG MIN_LATE
                'customer_id'      => $order->customer_id,
                'customer_name'    => $order->customer_name,
                'phone'            => $order->phone,
                'address'          => $order->address,
                'discount_percent' => $order->discount_percent ?? 0,
                'discount_amount'  => 0,
                'vat_percent'      => $order->vat_percent ?? 0,
                'vat_amount'       => 0,
                'total_amount'     => 0,
                'order_date'       => now(),
                'delivery_days'    => null,
                'deadline'         => null,
                'status'           => 'draft',
                'notes'            => '[Sửa tấm] Đơn liên kết của ' . $order->order_code,
            ]);

            $supplyMapping = [];
            $totalPlates = 0;
            $totalStraightLength = 0;
            $totalBeveledLength = 0;

            foreach ($selectedPieces as $pieceInfo) {
                $itemId = is_array($pieceInfo) ? ($pieceInfo['item_id'] ?? null) : $pieceInfo;
                if (!$itemId) continue;

                $originalItem = null;
                $originalSupply = null;

                if ($order->type === 'min_late') {
                    $originalItem = \App\Models\MinLateOrderItem::find($itemId);
                } elseif ($order->type === 'glass') {
                    $originalItem = \App\Models\GlassOrderItem::find($itemId);
                } else {
                    $originalItem = \App\Models\AcrylicOrderItem::find($itemId);
                }

                if (!$originalItem) continue;

                $originalSupply = $originalItem->orderSupply;
                if (!$originalSupply) continue;

                $parentSupplyId = $originalSupply->id;
                if (!isset($supplyMapping[$parentSupplyId])) {
                    $newSupply = \App\Models\OrderSupply::create([
                        'order_id'          => $reworkOrder->id,
                        'order_supply_code' => $originalSupply->order_supply_code,
                        'supply_name'       => $originalSupply->supply_name ?? 'Vật tư',
                        'quantity'          => 1,
                    ]);
                    $supplyMapping[$parentSupplyId] = $newSupply;
                }
                $newSupply = $supplyMapping[$parentSupplyId];

                // Xác định kích thước gốc, độ dày và thông số dán cạnh
                $origHeight = null;
                $origWidth = null;
                $origThickness = $originalItem->thickness ?? null;
                $origName = $originalItem->name ?? ($originalItem->product_name ?? 'Tấm');
                $edgeGluing = [];
                $straightLength = 0;
                $beveledLength = 0;
                $vatMoiLength = 0;
                $banRong40_59 = 0;
                $banRong17_39 = 0;
                $banRong25_35 = 0;
                $beveledHandle = 0;
                $cnc = 0;
                $direction = null;

                if ($order->type === 'min_late') {
                    $size = $originalItem->size ?? [];
                    if (is_string($size)) {
                        $size = json_decode($size, true) ?? [];
                    }
                    $origHeight = $size['height'] ?? null;
                    $origWidth = $size['width'] ?? null;
                    $edgeGluing = $originalItem->edge_gluing ?? [];
                    if (is_string($edgeGluing)) {
                        $edgeGluing = json_decode($edgeGluing, true) ?? [];
                    }
                    $straightLength = (float)($originalItem->straight_paste_length ?? 0);
                    $beveledLength = (float)($originalItem->beveled_length ?? 0);
                    $vatMoiLength = (float)($originalItem->vat_moi_length ?? 0);
                    $banRong40_59 = (float)($originalItem->ban_rong_40_59 ?? 0);
                    $banRong17_39 = (float)($originalItem->ban_rong_17_39 ?? 0);
                    $banRong25_35 = (float)($originalItem->ban_rong_25_35 ?? 0);
                    $beveledHandle = (float)($originalItem->beveled_handle ?? 0);
                    $cnc = (int)($originalItem->cnc ?? 0);
                    $direction = $originalItem->direction ?? null;

                    // Nếu bản ghi gốc có SL > 1 thì chia đều số mét cho 1 tấm
                    $origQty = max(1, (float)($originalItem->quantity ?? 1));
                    if ($origQty > 1) {
                        $straightLength = round($straightLength / $origQty, 2);
                        $beveledLength = round($beveledLength / $origQty, 2);
                        $vatMoiLength = round($vatMoiLength / $origQty, 2);
                        $banRong40_59 = round($banRong40_59 / $origQty, 2);
                        $banRong17_39 = round($banRong17_39 / $origQty, 2);
                        $banRong25_35 = round($banRong25_35 / $origQty, 2);
                    }
                } elseif ($order->type === 'glass') {
                    $origHeight = $originalItem->height ?? null;
                    $origWidth = $originalItem->width ?? null;
                    $h = (float)($origHeight ?? 0);
                    $w = (float)($origWidth ?? 0);
                    $straightLength = round(($h * 2 + $w * 2) / 1000, 2);
                    $beveledLength = 0;
                    $edgeGluing = ['height_1' => 'T', 'height_2' => 'T', 'width_1' => 'T', 'width_2' => 'T'];
                } else {
                    // Acrylic
                    $origHeight = $originalItem->height ?? null;
                    $origWidth = $originalItem->width ?? null;
                    $h = (float)($origHeight ?? 0);
                    $w = (float)($origWidth ?? 0);
                    $hasBevel = !empty($originalItem->bevel) && !in_array(strtolower(trim($originalItem->bevel)), ['k', 'không', '0', 'none']);
                    $hasEdgeBevel = !empty($originalItem->edge_bevel) && !in_array(strtolower(trim($originalItem->edge_bevel)), ['k', 'không', '0', 'none']);

                    if ($hasBevel || $hasEdgeBevel) {
                        // 1 cạnh vát, 3 cạnh thẳng
                        $beveledLength = round($w / 1000, 2);
                        $straightLength = round(($h * 2 + $w) / 1000, 2);
                        $edgeGluing = ['height_1' => 'T', 'height_2' => 'T', 'width_1' => 'V', 'width_2' => 'T'];
                    } else {
                        $beveledLength = 0;
                        $straightLength = round(($h * 2 + $w * 2) / 1000, 2);
                        $edgeGluing = ['height_1' => 'T', 'height_2' => 'T', 'width_1' => 'T', 'width_2' => 'T'];
                    }
                    $direction = $originalItem->grain_direction ?? null;
                }

                // Định dạng kích thước cũ (KTC) chuẩn xác theo yêu cầu
                $hFormatted = (is_numeric($origHeight) && floatval($origHeight) == intval($origHeight)) ? number_format($origHeight, 0, '.', '') : (is_numeric($origHeight) ? rtrim(rtrim(number_format($origHeight, 2, '.', ''), '0'), '.') : ($origHeight ?? '—'));
                $wFormatted = (is_numeric($origWidth) && floatval($origWidth) == intval($origWidth)) ? number_format($origWidth, 0, '.', '') : (is_numeric($origWidth) ? rtrim(rtrim(number_format($origWidth, 2, '.', ''), '0'), '.') : ($origWidth ?? '—'));

                $formattedOldSize = "{$hFormatted} x {$wFormatted}";
                $itemNotes = "KTC: {$formattedOldSize}\nYêu cầu: Giữ nguyên cạnh vát";

                \App\Models\MinLateOrderItem::create([
                    'order_supply_id'       => $newSupply->id,
                    'name'                  => $origName,
                    'thickness'             => $origThickness,
                    'size'                  => [
                        'height' => $origHeight,
                        'width'  => $origWidth,
                    ],
                    'quantity'              => 1, // Từng tấm riêng biệt!
                    'bevel'                 => $originalItem->bevel ?? null,
                    'edge_gluing'           => $edgeGluing,
                    'straight_paste_length' => $straightLength,
                    'beveled_length'        => $beveledLength,
                    'vat_moi_length'        => $vatMoiLength,
                    'ban_rong_40_59'        => $banRong40_59,
                    'ban_rong_17_39'        => $banRong17_39,
                    'ban_rong_25_35'        => $banRong25_35,
                    'beveled_handle'        => $beveledHandle,
                    'cnc'                   => $cnc,
                    'direction'             => $direction,
                    'notes'                 => $itemNotes,
                    'old_size'              => $formattedOldSize,
                ]);

                $totalPlates++;
                $totalStraightLength += $straightLength;
                $totalBeveledLength += $beveledLength;
            }

            // Lấy trực tiếp thông tin tên nội dung, đơn vị, đơn giá từ database (bảng minlate_prices)
            $lic1Db = \App\Models\MinLatePrice::where('code', 'LIC1')->first();
            $ml48Db = \App\Models\MinLatePrice::where('code', 'ML48')->first();
            $ml49Db = \App\Models\MinLatePrice::where('code', 'ML49')->first();

            $ml48Qty = round($totalStraightLength * 1.05, 2);
            $ml49Qty = round($totalBeveledLength * 1.05, 2);

            $lic1Name  = $lic1Db ? $lic1Db->product_name : 'Gia công cắt dán sửa tấm';
            $lic1Unit  = $lic1Db ? ($lic1Db->unit ?: 'Tấm') : 'Tấm';
            $lic1Price = $lic1Db ? (float)$lic1Db->price : 10000;
            $lic1Total = round($totalPlates * $lic1Price);

            $ml48Name  = $ml48Db ? $ml48Db->product_name : 'Dán chỉ thẳng';
            $ml48Unit  = $ml48Db ? ($ml48Db->unit ?: 'm') : 'm';
            $ml48Price = $ml48Db ? (float)$ml48Db->price : 18000;
            $ml48Total = round($ml48Qty * $ml48Price);

            $ml49Name  = $ml49Db ? $ml49Db->product_name : 'Dán chỉ vát';
            $ml49Unit  = $ml49Db ? ($ml49Db->unit ?: 'm') : 'm';
            $ml49Price = $ml49Db ? (float)$ml49Db->price : 25000;
            $ml49Total = round($ml49Qty * $ml49Price);

            \App\Models\PaymentDetail::create([
                'order_id'   => $reworkOrder->id,
                'name'       => $lic1Name,
                'unit'       => $lic1Unit,
                'quantity'   => $totalPlates,
                'price'      => $lic1Price,
                'price_only' => 0,
                'total'      => $lic1Total,
            ]);

            \App\Models\PaymentDetail::create([
                'order_id'   => $reworkOrder->id,
                'name'       => $ml48Name,
                'unit'       => $ml48Unit,
                'quantity'   => $ml48Qty,
                'price'      => $ml48Price,
                'price_only' => 0,
                'total'      => $ml48Total,
            ]);

            \App\Models\PaymentDetail::create([
                'order_id'   => $reworkOrder->id,
                'name'       => $ml49Name,
                'unit'       => $ml49Unit,
                'quantity'   => $ml49Qty,
                'price'      => $ml49Price,
                'price_only' => 0,
                'total'      => $ml49Total,
            ]);

            $reworkOrder->total_amount = $lic1Total + $ml48Total + $ml49Total;
            $reworkOrder->save();

            return $reworkOrder;
        });
    }

    /**
     * Hiển thị trang tạo đơn nháp bảo hành (GET).
     */
    public function warrantyCreateForm(Order $order)
    {
        $itemIds = session('warranty_item_ids_' . $order->id, []);
        if (empty($itemIds)) {
            return redirect()->route('orders.show', $order->id)->with('error', 'Chưa chọn tấm gỗ cần bảo hành.');
        }

        $oldDraftId = old('draft_order_id');
        if ($oldDraftId) {
            $acrylicOrder = Order::where('status', 'draft')->where('relation_type', 'warranty')->find($oldDraftId);
        }

        if (!isset($acrylicOrder) || !$acrylicOrder) {
            $acrylicOrder = $this->buildWarrantyDraftOrder($order, $itemIds);
        }

        $orderType = $order->type;
        $isDraftCreate = true;
        $woodBoardPrices = \App\Models\WoodBoardPrice::orderBy('code', 'asc')->get();
        $cncTemplates = \App\Models\CncTemplate::orderBy('id', 'asc')->get();
        $glassPrices = \App\Models\GlassPrice::orderBy('code', 'asc')->get();
        $minLatePrices = \App\Models\MinLatePrice::orderBy('category_name', 'asc')->orderBy('stt', 'asc')->get();

        return view('orders.create', compact('orderType', 'acrylicOrder', 'isDraftCreate', 'woodBoardPrices', 'cncTemplates', 'glassPrices', 'minLatePrices'));
    }

    /**
     * Tạo bản ghi đơn nháp bảo hành trong DB.
     */
    private function buildWarrantyDraftOrder(Order $order, array $selectedItemIds): Order
    {
        return DB::transaction(function () use ($order, $selectedItemIds) {
            $orderCode = $this->generateOrderCode();

            $warrantyOrder = Order::create([
                'parent_id' => $order->id,
                'relation_type' => 'warranty',
                'order_code' => $orderCode,
                'type' => $order->type,
                'customer_id' => $order->customer_id,
                'customer_name' => $order->customer_name,
                'phone' => $order->phone,
                'address' => $order->address,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'vat_percent' => 0,
                'vat_amount' => 0,
                'total_amount' => 0,
                'order_date' => now(),
                'delivery_days' => null,
                'deadline' => null,
                'status' => 'draft',
                'notes' => '[Bảo hành] Đơn liên kết của ' . $order->order_code,
            ]);

            $supplyMapping = [];

            foreach ($selectedItemIds as $itemId) {
                $originalItem = null;
                $originalSupply = null;

                if ($order->type === 'min_late') {
                    $originalItem = \App\Models\MinLateOrderItem::find($itemId);
                } elseif ($order->type === 'glass') {
                    $originalItem = \App\Models\GlassOrderItem::find($itemId);
                } else {
                    $originalItem = \App\Models\AcrylicOrderItem::find($itemId);
                }

                if (!$originalItem) continue;

                $originalSupply = $originalItem->orderSupply;
                if (!$originalSupply) continue;

                $parentSupplyId = $originalSupply->id;
                if (!isset($supplyMapping[$parentSupplyId])) {
                    $newSupply = \App\Models\OrderSupply::create([
                        'order_id'          => $warrantyOrder->id,
                        'order_supply_code' => $originalSupply->order_supply_code,
                        'supply_name'       => $originalSupply->supply_name ?? 'Vật tư',
                        'quantity'          => $originalSupply->quantity ?? 1,
                    ]);
                    $supplyMapping[$parentSupplyId] = $newSupply->id;
                }
                $newSupplyId = $supplyMapping[$parentSupplyId];

                $itemData = $originalItem->toArray();
                unset($itemData['id']);
                $itemData['order_supply_id'] = $newSupplyId;

                $origHeight = null;
                $origWidth = null;

                if ($order->type === 'min_late') {
                    $size = $originalItem->size ?? [];
                    if (is_string($size)) {
                        $size = json_decode($size, true) ?? [];
                    }
                    $origHeight = $size['height'] ?? null;
                    $origWidth = $size['width'] ?? null;
                } else {
                    $origHeight = $originalItem->height;
                    $origWidth = $originalItem->width;
                }

                $itemData['notes'] = $originalItem->notes ?? '';
                $itemData['old_size'] = ($origHeight ?? '?') . ' x ' . ($origWidth ?? '?');

                if ($order->type === 'min_late') {
                    \App\Models\MinLateOrderItem::create($itemData);
                } elseif ($order->type === 'glass') {
                    \App\Models\GlassOrderItem::create($itemData);
                } else {
                    \App\Models\AcrylicOrderItem::create($itemData);
                }
            }

            return $warrantyOrder;
        });
    }

    /**
     * In lệnh viết tay cho đơn sửa tấm.
     */
    public function printHandwritten(Order $order)
    {
        $order->load(['supplies.items', 'supplies.minLateItems', 'supplies.glassItems', 'parent']);
        $customer = $order->customer;
        return view('orders.print_handwritten', compact('order', 'customer'));
    }

    /**
     * Tạo đơn bổ sung từ danh sách tấm được chọn (POST JSON).
     */
    public function createAdditionalOrder(Request $request, Order $order)
    {
        $selectedItemIds = $request->input('item_ids', []);
        $boardReturnStatus = $request->input('board_return_status'); // 'pending' hoặc null

        if (empty($selectedItemIds)) {
            return response()->json(['error' => 'Chưa chọn tấm gỗ cần bổ sung.'], 422);
        }

        $additionalOrder = $this->buildAdditionalDraftOrderWithItems($order, $selectedItemIds, $boardReturnStatus);

        return response()->json([
            'ok' => true,
            'redirect_url' => route('orders.additional-create', $order->id) . '?draft_id=' . $additionalOrder->id
        ]);
    }

    /**
     * Tạo bản ghi đơn nháp bổ sung cùng các tấm được chọn trong DB.
     */
    private function buildAdditionalDraftOrderWithItems(Order $order, array $selectedItemIds, ?string $boardReturnStatus = null): Order
    {
        return DB::transaction(function () use ($order, $selectedItemIds, $boardReturnStatus) {
            // Xóa bản nháp bổ sung cũ nếu có
            $oldDraft = Order::where('status', 'draft')
                ->where('parent_id', $order->id)
                ->where('relation_type', 'additional')
                ->first();
            if ($oldDraft) {
                foreach ($oldDraft->supplies as $s) {
                    $s->items()->delete();
                    $s->minLateItems()->delete();
                    $s->glassItems()->delete();
                    $s->delete();
                }
                $oldDraft->delete();
            }

            $orderCode = $this->generateOrderCode();

            $additionalOrder = Order::create([
                'parent_id' => $order->id,
                'relation_type' => 'additional',
                'board_return_status' => $boardReturnStatus,
                'order_code' => $orderCode,
                'type' => $order->type,
                'customer_id' => $order->customer_id,
                'customer_name' => $order->customer_name,
                'phone' => $order->phone,
                'address' => $order->address,
                'discount_percent' => $order->discount_percent ?? 0,
                'discount_amount' => 0,
                'vat_percent' => $order->vat_percent ?? 0,
                'vat_amount' => 0,
                'total_amount' => 0,
                'customer_policy' => $order->customer_policy,
                'order_date' => now(),
                'delivery_days' => null,
                'status' => 'draft',
                'notes' => '[Bổ sung] Đơn liên kết của ' . $order->order_code,
            ]);

            $supplyMapping = [];

            foreach ($selectedItemIds as $itemId) {
                $originalItem = null;
                $originalSupply = null;

                if ($order->type === 'min_late') {
                    $originalItem = \App\Models\MinLateOrderItem::find($itemId);
                } elseif ($order->type === 'glass') {
                    $originalItem = \App\Models\GlassOrderItem::find($itemId);
                } else {
                    $originalItem = \App\Models\AcrylicOrderItem::find($itemId);
                }

                if (!$originalItem) continue;

                $originalSupply = $originalItem->orderSupply;
                if (!$originalSupply) continue;

                $parentSupplyId = $originalSupply->id;
                if (!isset($supplyMapping[$parentSupplyId])) {
                    $newSupply = \App\Models\OrderSupply::create([
                        'order_id'          => $additionalOrder->id,
                        'order_supply_code' => $originalSupply->order_supply_code,
                        'supply_name'       => $originalSupply->supply_name ?? 'Vật tư',
                        'quantity'          => $originalSupply->quantity ?? 1,
                    ]);
                    $supplyMapping[$parentSupplyId] = $newSupply->id;
                }
                $newSupplyId = $supplyMapping[$parentSupplyId];

                $itemData = $originalItem->toArray();
                unset($itemData['id']);
                $itemData['order_supply_id'] = $newSupplyId;

                if ($order->type === 'min_late') {
                    \App\Models\MinLateOrderItem::create($itemData);
                } elseif ($order->type === 'glass') {
                    \App\Models\GlassOrderItem::create($itemData);
                } else {
                    \App\Models\AcrylicOrderItem::create($itemData);
                }
            }

            return $additionalOrder;
        });
    }

    /**
     * Hiển thị trang tạo đơn nháp bổ sung (GET).
     */
    public function additionalCreateForm(Order $order)
    {
        $draftId = request('draft_id');
        if ($draftId) {
            $acrylicOrder = Order::where('status', 'draft')
                ->where('parent_id', $order->id)
                ->where('relation_type', 'additional')
                ->find($draftId);
        } else {
            $acrylicOrder = Order::where('status', 'draft')
                ->where('parent_id', $order->id)
                ->where('relation_type', 'additional')
                ->first();
        }

        if (!$acrylicOrder) {
            $acrylicOrder = $this->buildAdditionalDraftOrder($order);
        }

        $orderType = $order->type;
        $isDraftCreate = true;
        $woodBoardPrices = \App\Models\WoodBoardPrice::orderBy('code', 'asc')->get();
        $cncTemplates = \App\Models\CncTemplate::orderBy('id', 'asc')->get();
        $glassPrices = \App\Models\GlassPrice::orderBy('code', 'asc')->get();
        $minLatePrices = \App\Models\MinLatePrice::orderBy('category_name', 'asc')->orderBy('stt', 'asc')->get();

        return view('orders.create', compact('orderType', 'acrylicOrder', 'isDraftCreate', 'woodBoardPrices', 'cncTemplates', 'glassPrices', 'minLatePrices'));
    }

    /**
     * Tạo bản ghi đơn nháp bổ sung trống trong DB.
     */
    private function buildAdditionalDraftOrder(Order $order): Order
    {
        return \DB::transaction(function () use ($order) {
            $orderCode = $this->generateOrderCode();

            return Order::create([
                'parent_id' => $order->id,
                'relation_type' => 'additional',
                'board_return_status' => null,
                'order_code' => $orderCode,
                'type' => $order->type,
                'customer_id' => $order->customer_id,
                'customer_name' => $order->customer_name,
                'phone' => $order->phone,
                'address' => $order->address,
                'discount_percent' => $order->discount_percent ?? 0,
                'discount_amount' => 0,
                'vat_percent' => $order->vat_percent ?? 0,
                'vat_amount' => 0,
                'total_amount' => 0,
                'customer_policy' => $order->customer_policy,
                'order_date' => now(),
                'delivery_days' => null,
                'status' => 'draft',
                'notes' => '[Bổ sung] Đơn liên kết của ' . $order->order_code,
            ]);
        });
    }

    /**
     * Xác nhận khách đã trả ván cũ cho đơn bổ sung.
     */
    public function confirmBoardReturn(Order $order)
    {
        if ($order->relation_type !== 'additional') {
            return redirect()->back()->with('error', 'Chức năng chỉ áp dụng cho đơn bổ sung.');
        }

        $order->update([
            'board_return_status' => 'returned',
        ]);

        return redirect()->route('orders.show', $order)->with('success', 'Đã xác nhận trả ván và cấn trừ công nợ thành công.');
    }
}
