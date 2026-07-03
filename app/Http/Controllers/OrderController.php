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

        $orders = Order::with('customer')
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
            ->when($request->filled('filter_order_code'), function ($q) use ($request) {
                $q->where('order_code', 'like', "%{$request->filter_order_code}%");
            })
            ->when($request->filled('filter_customer_name'), function ($q) use ($request) {
                $q->where('customer_name', 'like', "%{$request->filter_customer_name}%");
            })
            ->when($request->filled('filter_status'), function ($q) use ($request) {
                $q->where('status', $request->filter_status);
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('orders.index', compact('orders', 'perPage', 'search'));
    }

    public function show(Order $order)
    {
        $order->load(['supplies.items.codes', 'supplies.minLateItems', 'supplies.glassItems', 'paymentDetails', 'orderPayments.creator']);
        $acrylicOrder = $order;
        return view('orders.show', compact('acrylicOrder'));
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
        if ($order->status === 'in_production') {
            return redirect()->route('orders.index')->with('error', 'Đơn hàng đang trong quá trình sản xuất, không thể chỉnh sửa.');
        }
        if ($order->status === 'cancelled') {
            return redirect()->route('orders.index')->with('error', 'Đơn hàng đã bị hủy, không thể chỉnh sửa.');
        }
        $order->load(['supplies.items', 'supplies.minLateItems', 'supplies.glassItems', 'paymentDetails']);
        $acrylicOrder = $order;
        $woodBoardPrices = \App\Models\WoodBoardPrice::orderBy('code', 'asc')->get();
        $cncTemplates = \App\Models\CncTemplate::orderBy('id', 'asc')->get();
        $glassPrices = \App\Models\GlassPrice::orderBy('code', 'asc')->get();
        $minLatePrices = \App\Models\MinLatePrice::orderBy('category_name', 'asc')->orderBy('stt', 'asc')->get();
        return view('orders.edit', compact('acrylicOrder', 'woodBoardPrices', 'cncTemplates', 'glassPrices', 'minLatePrices'));
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

    public function destroy(Request $request, Order $order)
    {
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
}
