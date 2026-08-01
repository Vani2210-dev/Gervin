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
            ->withCount('manufactureOrders')
            ->when($user && !$user->hasRole('Admin'), function ($q) use ($user) {
                $q->whereHas('customer.users', function ($uq) use ($user) {
                    $uq->where('users.id', $user->id);
                });
            })
            ->when($request->input('filter_status') !== 'draft', function ($q) {
                $q->where('status', '!=', 'draft');
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('order_code', 'like', "%$search%")
                        ->orWhere('customer_name', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%");
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
            })
            ->when($request->filled('filter_start_date'), function ($q) use ($request) {
                $q->whereDate('order_date', '>=', $request->filter_start_date);
            })
            ->when($request->filled('filter_end_date'), function ($q) use ($request) {
                $q->whereDate('order_date', '<=', $request->filter_end_date);
            });

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
            $customers = \App\Models\Customer::whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })->orderBy('name')->get();
        } else {
            $customers = \App\Models\Customer::orderBy('name')->get();
        }

        return view('orders.index', compact(
            'orders', 
            'perPage', 
            'search',
            'totalOrdersCount',
            'totalAmountSum',
            'totalPaidSum',
            'totalDebtSum',
            'customers'
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

        $order->load(['supplies.items.codes', 'supplies.minLateItems', 'supplies.glassItems', 'paymentDetails', 'orderPayments.creator']);
        $acrylicOrder = $order;
        $exportData = $this->getExportData($order);
        return view('orders.show', compact('acrylicOrder', 'exportData'));
    }

    public function bulkExportData(\Illuminate\Http\Request $request)
    {
        $orderIds = $request->input('order_ids', []);
        $orders = Order::whereIn('id', $orderIds)->with(['supplies.items.codes', 'supplies.minLateItems', 'supplies.glassItems', 'paymentDetails', 'orderPayments.creator', 'customer'])->get();
        
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

    public function createByType(string $type)
    {
        abort_unless(in_array($type, self::ORDER_TYPES, true), 404);

        $orderType = $type;
        
        $oldDraftId = old('draft_order_id');
        if ($oldDraftId) {
            $acrylicOrder = Order::where('status', 'draft')->find($oldDraftId);
        }
        
        if (!isset($acrylicOrder) || !$acrylicOrder) {
            $acrylicOrder = $this->createDraftOrder($type);
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

        if ($draftOrder->type !== $request->type) {
            abort(422, 'Loại đơn không khớp với đơn nháp.');
        }

        $service = $this->serviceFor($request->type);

        $request->validate($service->getStoreRules());
        $service->update($request, $draftOrder);

        return redirect()->route('orders.index')->with('success', 'Tạo đơn hàng thành công.');
    }

    public function update(Request $request, Order $order)
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

        if ($request->filled('supplies_json')) {
            $supplies = json_decode($request->input('supplies_json'), true);
            if (is_array($supplies)) {
                $supplies = $this->cleanEmptyStrings($supplies);
                $request->merge(['supplies' => $supplies]);
            }
        }

        if ($request->type === 'min_late') {
            $request->validate($this->minLateOrderService->getUpdateRules());
            $this->minLateOrderService->update($request, $order);
        } elseif ($request->type === 'glass') {
            $request->validate($this->glassOrderService->getUpdateRules());
            $this->glassOrderService->update($request, $order);
        } else {
            $request->validate($this->acrylicOrderService->getUpdateRules());
            $this->acrylicOrderService->update($request, $order);
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
            if ($order->status !== 'pending') {
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
        $order->load(['supplies.items.codes', 'supplies.minLateItems.codes', 'supplies.glassItems.codes']);
        $stats = [];

        $date = now()->format('Y-m-d'); // Today is day 1
        $day1Date = $date;
        $day2Date = date('Y-m-d', strtotime($date . ' +1 day'));

        foreach ($order->supplies as $supply) {
            $day1Count = 0;
            $day2Count = 0;
            $inProductionCount = 0;
            $totalCount = 0;
            $cncCount = 0;

            if ($order->type === 'glass') {
                $items = $supply->glassItems;
                $totalCount = $items->sum('quantity');
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
                        $action = strtolower($log['action'] ?? '');
                        if (in_array($action, ['hoàn thành làm đẹp', 'hoàn thành qc'])) {
                            $completedTime = $log['time'] ?? null;
                        }
                        if (in_array($action, ['đã nhận tem', 'hoàn thành cnc', 'ép ván', 'hoàn thành ép', 'hoàn thành dán cạnh'])) {
                            $hasStarted = true;
                        }
                    }

                    if ($completedTime) {
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

            $remainingCount = max(0, $totalCount - $day1Count - $day2Count);

            $stats[] = [
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

    private function createDraftOrder(string $type): Order
    {
        return DB::transaction(function () use ($type) {
            $dateStr = now()->format('ymd');
            $prefix = 'DH' . $dateStr;

            $lastOrder = Order::where('order_code', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('order_code', 'desc')
                ->first();

            if ($lastOrder) {
                $lastNumber = intval(substr($lastOrder->order_code, 8));
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $orderCode = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            return Order::create([
                'order_code' => $orderCode,
                'type' => $type,
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
            'item_ids.*' => 'required|integer',
        ]);

        session(['rework_item_ids_' . $order->id => $request->input('item_ids')]);

        return response()->json([
            'ok' => true,
            'redirect_url' => route('orders.rework-create', $order->id)
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
            $dateStr = now()->format('ymd');
            $prefix = 'DH' . $dateStr;

            $lastOrder = Order::where('order_code', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('order_code', 'desc')
                ->first();

            if ($lastOrder) {
                $lastNumber = intval(substr($lastOrder->order_code, 8));
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $orderCode = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

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
     */
    public function reworkCreateForm(Order $order)
    {
        $itemIds = session('rework_item_ids_' . $order->id, []);
        if (empty($itemIds)) {
            return redirect()->route('orders.show', $order->id)->with('error', 'Chưa chọn tấm gỗ cần sửa.');
        }

        $oldDraftId = old('draft_order_id');
        if ($oldDraftId) {
            $acrylicOrder = Order::where('status', 'draft')->where('relation_type', 'rework')->find($oldDraftId);
        }

        if (!isset($acrylicOrder) || !$acrylicOrder) {
            $acrylicOrder = $this->buildReworkDraftOrder($order, $itemIds);
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
     * Tạo bản ghi đơn nháp sửa tấm trong DB.
     */
    private function buildReworkDraftOrder(Order $order, array $selectedItemIds): Order
    {
        return DB::transaction(function () use ($order, $selectedItemIds) {
            $dateStr = now()->format('ymd');
            $prefix = 'DH' . $dateStr;

            $lastOrder = Order::where('order_code', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('order_code', 'desc')
                ->first();

            if ($lastOrder) {
                $lastNumber = intval(substr($lastOrder->order_code, 8));
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $orderCode = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $reworkOrder = Order::create([
                'parent_id' => $order->id,
                'relation_type' => 'rework',
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
                'notes' => '[Sửa tấm] Đơn liên kết của ' . $order->order_code,
            ]);

            $supplyMapping = [];

            foreach ($selectedItemIds as $itemId) {
                $originalItem = null;
                $originalSupply = null;
                $originalStt = 1;

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

                if ($order->type === 'min_late') {
                    $siblings = $originalSupply->minLateItems()->orderBy('id')->get();
                } elseif ($order->type === 'glass') {
                    $siblings = $originalSupply->glassItems()->orderBy('id')->get();
                } else {
                    $siblings = $originalSupply->items()->orderBy('id')->get();
                }

                foreach ($siblings as $idx => $sib) {
                    if ($sib->id == $originalItem->id) {
                        $originalStt = $idx + 1;
                        break;
                    }
                }

                $parentSupplyId = $originalSupply->id;
                if (!isset($supplyMapping[$parentSupplyId])) {
                    $newSupply = \App\Models\OrderSupply::create([
                        'order_id'          => $reworkOrder->id,
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

            return $reworkOrder;
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
     * Hiển thị trang tạo đơn nháp bổ sung (GET).
     */
    public function additionalCreateForm(Order $order)
    {
        $acrylicOrder = Order::where('status', 'draft')
            ->where('parent_id', $order->id)
            ->where('relation_type', 'additional')
            ->first();

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
     * Tạo bản ghi đơn nháp bổ sung trong DB.
     */
    private function buildAdditionalDraftOrder(Order $order): Order
    {
        return \DB::transaction(function () use ($order) {
            $dateStr = now()->format('ymd');
            $prefix = 'DH' . $dateStr;

            $lastOrder = Order::where('order_code', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('order_code', 'desc')
                ->first();

            if ($lastOrder) {
                $lastNumber = intval(substr($lastOrder->order_code, 8));
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $orderCode = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            return Order::create([
                'parent_id' => $order->id,
                'relation_type' => 'additional',
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
                'notes' => '[Bổ sung] Đơn liên kết của ' . $order->order_code,
            ]);
        });
    }
}
