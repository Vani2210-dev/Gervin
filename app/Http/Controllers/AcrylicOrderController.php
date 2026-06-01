<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\AcrylicOrderItem;
use App\Models\OrderSupply;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AcrylicOrderController extends Controller
{
    public function __construct()
    {
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

        return view('acrylic_orders.index', compact('orders', 'perPage', 'search'));
    }

    public function show(Order $acrylicOrder)
    {
        $acrylicOrder->load('items');
        return view('acrylic_orders.show', compact('acrylicOrder'));
    }

    public function create()
    {
        return view('acrylic_orders.create');
    }

    public function edit(Order $acrylicOrder)
    {
        $acrylicOrder->load('items');
        return view('acrylic_orders.edit', compact('acrylicOrder'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'    => 'nullable|exists:customers,id',
            'customer_name'  => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'deadline'       => 'nullable|date',
            'notes'          => 'nullable|string',
            'attachments'    => 'nullable|array',
            'attachments.*'  => 'image|mimes:jpg,jpeg,png|max:2048',
            'items'          => 'required|array|min:1',
            'items.*.product_code'        => 'nullable|string|max:50',
            'items.*.product_name'        => 'required|string|max:255',
            'items.*.height'              => 'nullable|numeric|min:0',
            'items.*.width'               => 'nullable|numeric|min:0',
            'items.*.grain_direction'     => 'nullable|in:0,2',
            'items.*.edge_bevel'          => 'nullable|string|max:100',
            'items.*.wing_area'           => 'nullable|numeric|min:0',
            'items.*.molding_length'      => 'nullable|numeric|min:0',
            'items.*.quantity'            => 'required|integer|min:1',
            'items.*.unit_price'          => 'required|numeric|min:0',
            'items.*.notes'               => 'nullable|string',
            'items.*.bevel'               => 'nullable|string|max:100',
            'items.*.vertical_grain_cnc'  => 'nullable|string|max:100',
        ]);

        // Handle file uploads
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            $count = 0;
            foreach ($request->file('attachments') as $file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension    = strtolower($file->getClientOriginalExtension());
                $cleanName    = \Illuminate\Support\Str::slug($originalName) . '_' . time() . '_' . $count . '.' . $extension;
                $filePath     = $file->storeAs('anh-don-hang', $cleanName, 'private');
                $attachmentPaths[] = $filePath;
                $count++;
            }
        }

        // Generate order code: DA00001, DA00002, etc.
        $lastOrder = Order::orderBy('id', 'desc')->first();
        $nextNumber = $lastOrder ? intval(substr($lastOrder->order_code, 2)) + 1 : 1;
        $orderCode = 'DA' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        // Calculate total amount
        $totalAmount = 0;
        foreach ($request->items as $item) {
            $totalAmount += ($item['unit_price'] * $item['quantity']);
        }

        $order = Order::create([
            'order_code'    => $orderCode,
            'customer_id'   => $request->customer_id,
            'customer_name' => $request->customer_name,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'deadline'      => $request->deadline,
            'notes'         => $request->notes,
            'total_amount'  => $totalAmount,
            'status'        => 'pending',
            'attachments'   => !empty($attachmentPaths) ? json_encode($attachmentPaths) : null,
        ]);

        // Create a default OrderSupply for the order
        $orderSupply = OrderSupply::create([
            'order_id'    => $order->id,
            'supply_name' => 'Vật tư Acrylic mặc định',
            'quantity'    => 1,
            'type'        => 'acrylic',
        ]);

        // Create order items
        foreach ($request->items as $item) {
            $totalPrice = $item['unit_price'] * $item['quantity'];
            AcrylicOrderItem::create([
                'order_supply_id'    => $orderSupply->id,
                'product_code'        => $item['product_code'],
                'product_name'        => $item['product_name'],
                'height'              => $item['height'],
                'width'               => $item['width'],
                'grain_direction'     => $item['grain_direction'] ?? 0,
                'edge_bevel'          => $item['edge_bevel'],
                'wing_area'           => $item['wing_area'],
                'molding_length'      => $item['molding_length'],
                'quantity'            => $item['quantity'],
                'unit_price'          => $item['unit_price'],
                'total_price'         => $totalPrice,
                'notes'               => $item['notes'],
                'bevel'               => $item['bevel'],
                'vertical_grain_cnc'  => $item['vertical_grain_cnc'],
            ]);
        }

        return redirect()->route('acrylic_orders.index')->with('success', 'Tạo đơn hàng Acrylic thành công.');
    }

    public function update(Request $request, Order $acrylicOrder)
    {
        $request->validate([
            'customer_id'    => 'nullable|exists:customers,id',
            'customer_name'  => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'deadline'       => 'nullable|date',
            'notes'          => 'nullable|string',
            'status'         => 'nullable|in:pending,processing,completed,cancelled',
            'attachments'    => 'nullable|array',
            'attachments.*'  => 'image|mimes:jpg,jpeg,png|max:2048',
            'delete_attachments' => 'nullable|string',
            'items'          => 'required|array|min:1',
            'items.*.id'                   => 'nullable|exists:acrylic_order_items,id',
            'items.*.product_code'        => 'nullable|string|max:50',
            'items.*.product_name'        => 'required|string|max:255',
            'items.*.height'              => 'nullable|numeric|min:0',
            'items.*.width'               => 'nullable|numeric|min:0',
            'items.*.grain_direction'     => 'nullable|in:0,2',
            'items.*.edge_bevel'          => 'nullable|string|max:100',
            'items.*.wing_area'           => 'nullable|numeric|min:0',
            'items.*.molding_length'      => 'nullable|numeric|min:0',
            'items.*.quantity'            => 'required|integer|min:1',
            'items.*.unit_price'          => 'required|numeric|min:0',
            'items.*.notes'               => 'nullable|string',
            'items.*.bevel'               => 'nullable|string|max:100',
            'items.*.vertical_grain_cnc'  => 'nullable|string|max:100',
        ]);

        // Handle file uploads
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            $count = 0;
            foreach ($request->file('attachments') as $file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension    = strtolower($file->getClientOriginalExtension());
                $cleanName    = \Illuminate\Support\Str::slug($originalName) . '_' . time() . '_' . $count . '.' . $extension;
                $filePath     = $file->storeAs('anh-don-hang', $cleanName, 'private');
                $attachmentPaths[] = $filePath;
                $count++;
            }
        }

        // Handle deleted attachments
        $existingAttachments = json_decode($acrylicOrder->attachments, true) ?? [];
        if ($request->filled('delete_attachments')) {
            $deletedAttachments = json_decode($request->delete_attachments, true) ?? [];
            foreach ($deletedAttachments as $deletedPath) {
                // Remove from array
                $existingAttachments = array_filter($existingAttachments, function($path) use ($deletedPath) {
                    return $path !== $deletedPath;
                });
                // Delete file from storage
                if (Storage::disk('private')->exists($deletedPath)) {
                    Storage::disk('private')->delete($deletedPath);
                }
            }
        }

        // Merge with existing attachments
        $allAttachments = array_merge($existingAttachments, $attachmentPaths);

        // Calculate total amount
        $totalAmount = 0;
        foreach ($request->items as $item) {
            $totalAmount += ($item['unit_price'] * $item['quantity']);
        }

        $acrylicOrder->update([
            'customer_id'   => $request->customer_id,
            'customer_name' => $request->customer_name,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'deadline'      => $request->deadline,
            'notes'         => $request->notes,
            'total_amount'  => $totalAmount,
            'status'        => $request->status ?? $acrylicOrder->status,
            'attachments'   => !empty($allAttachments) ? json_encode($allAttachments) : null,
        ]);

        // Find or create a default OrderSupply for the order
        $orderSupply = $acrylicOrder->supplies()->first();
        if (!$orderSupply) {
            $orderSupply = OrderSupply::create([
                'order_id'    => $acrylicOrder->id,
                'supply_name' => 'Vật tư Acrylic mặc định',
                'quantity'    => 1,
                'type'        => 'acrylic',
            ]);
        }

        // Update or create order items
        $existingItemIds = [];
        foreach ($request->items as $item) {
            $totalPrice = $item['unit_price'] * $item['quantity'];
            
            if (isset($item['id'])) {
                // Update existing item
                $orderItem = AcrylicOrderItem::find($item['id']);
                if ($orderItem && $orderItem->orderSupply && $orderItem->orderSupply->order_id == $acrylicOrder->id) {
                    $orderItem->update([
                        'product_code'        => $item['product_code'],
                        'product_name'        => $item['product_name'],
                        'height'              => $item['height'],
                        'width'               => $item['width'],
                        'grain_direction'     => $item['grain_direction'] ?? 0,
                        'edge_bevel'          => $item['edge_bevel'],
                        'wing_area'           => $item['wing_area'],
                        'molding_length'      => $item['molding_length'],
                        'quantity'            => $item['quantity'],
                        'unit_price'          => $item['unit_price'],
                        'total_price'         => $totalPrice,
                        'notes'               => $item['notes'],
                        'bevel'               => $item['bevel'],
                        'vertical_grain_cnc'  => $item['vertical_grain_cnc'],
                    ]);
                    $existingItemIds[] = $orderItem->id;
                }
            } else {
                // Create new item
                AcrylicOrderItem::create([
                    'order_supply_id'    => $orderSupply->id,
                    'product_code'        => $item['product_code'],
                    'product_name'        => $item['product_name'],
                    'height'              => $item['height'],
                    'width'               => $item['width'],
                    'grain_direction'     => $item['grain_direction'] ?? 0,
                    'edge_bevel'          => $item['edge_bevel'],
                    'wing_area'           => $item['wing_area'],
                    'molding_length'      => $item['molding_length'],
                    'quantity'            => $item['quantity'],
                    'unit_price'          => $item['unit_price'],
                    'total_price'         => $totalPrice,
                    'notes'               => $item['notes'],
                    'bevel'               => $item['bevel'],
                    'vertical_grain_cnc'  => $item['vertical_grain_cnc'],
                ]);
            }
        }

        // Delete items not in the request
        $supplyIds = $acrylicOrder->supplies()->pluck('id');
        AcrylicOrderItem::whereIn('order_supply_id', $supplyIds)->whereNotIn('id', $existingItemIds)->delete();

        return redirect()->route('acrylic_orders.index')->with('success', 'Cập nhật đơn hàng Acrylic thành công.');
    }

    public function destroy(Order $acrylicOrder)
    {
        $acrylicOrder->delete();
        return redirect()->route('acrylic_orders.index')->with('success', 'Xóa đơn hàng Acrylic thành công.');
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
