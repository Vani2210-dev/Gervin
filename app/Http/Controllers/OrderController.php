<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\AcrylicOrderService;
use App\Services\MinLateOrderService;
use App\Services\GlassOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
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

        $this->middleware('permission:view acrylic order',   ['only' => ['index', 'show']]);
        $this->middleware('permission:add acrylic order',    ['only' => ['create', 'store']]);
        $this->middleware('permission:edit acrylic order',   ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete acrylic order', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search', '');

        $orders = Order::with('customer')
            ->when($search, function ($q) use ($search) {
                $q->where('order_code', 'like', "%$search%")
                  ->orWhere('customer_name', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
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
        $order->load(['supplies.items', 'supplies.minLateItems', 'supplies.glassItems', 'paymentDetails']);
        $acrylicOrder = $order;
        return view('orders.show', compact('acrylicOrder'));
    }

    public function create()
    {
        $lastOrder = Order::orderBy('id', 'desc')->first();
        $nextNumber = $lastOrder ? intval(substr($lastOrder->order_code, 2)) + 1 : 1;
        $nextOrderCode = 'DA' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        return view('orders.create', compact('nextOrderCode'));
    }

    public function edit(Order $order)
    {
        $order->load(['supplies.items', 'supplies.minLateItems', 'supplies.glassItems', 'paymentDetails']);
        $acrylicOrder = $order;
        return view('orders.edit', compact('acrylicOrder'));
    }

    public function store(Request $request)
    {
        if ($request->type === 'min_late') {
            $request->validate($this->minLateOrderService->getStoreRules());
            $this->minLateOrderService->store($request);
        } elseif ($request->type === 'glass') {
            $request->validate($this->glassOrderService->getStoreRules());
            $this->glassOrderService->store($request);
        } else {
            $request->validate($this->acrylicOrderService->getStoreRules());
            $this->acrylicOrderService->store($request);
        }

        return redirect()->route('orders.index')->with('success', 'Tạo đơn hàng thành công.');
    }

    public function update(Request $request, Order $order)
    {
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

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Xóa đơn hàng thành công.');
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
}
