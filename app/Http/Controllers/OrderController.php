<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\AcrylicOrderItem;
use App\Models\MinLateOrderItem;
use App\Models\OrderSupply;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
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

        return view('orders.index', compact('orders', 'perPage', 'search'));
    }

    public function show(Order $acrylicOrder)
    {
        $acrylicOrder->load(['supplies.items', 'supplies.minLateItems']);
        return view('orders.show', compact('acrylicOrder'));
    }

    public function create()
    {
        return view('orders.create');
    }

    public function edit(Order $acrylicOrder)
    {
        $acrylicOrder->load(['supplies.items', 'supplies.minLateItems']);
        return view('orders.edit', compact('acrylicOrder'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'    => 'nullable|exists:customers,id',
            'customer_name'  => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'type'           => 'nullable|in:acrylic,min_late,glass',
            'order_date'     => 'nullable|date',
            'delivery_days'  => 'nullable|integer|min:0',
            'deadline'       => 'nullable|date',
            'notes'          => 'nullable|string',
            'attachments'    => 'nullable|array',
            'attachments.*'  => 'image|mimes:jpg,jpeg,png|max:2048',
            'supplies'       => 'required|array|min:1',
            'supplies.*.supply_name'         => 'nullable|string|max:255',
            'supplies.*.quantity'            => 'nullable|numeric|min:0',
            'supplies.*.items'               => 'required|array|min:1',
            'supplies.*.items.*.id'                   => 'nullable|integer',
            'supplies.*.items.*.product_code'        => 'nullable|string|max:50',
            'supplies.*.items.*.product_name'        => 'required|string|max:255',
            'supplies.*.items.*.height'              => 'nullable|numeric|min:0',
            'supplies.*.items.*.width'               => 'nullable|numeric|min:0',
            'supplies.*.items.*.grain_direction'     => 'nullable|in:0,2',
            'supplies.*.items.*.edge_bevel'          => 'nullable|string|max:100',
            'supplies.*.items.*.wing_area'           => 'nullable|numeric|min:0',
            'supplies.*.items.*.molding_length'      => 'nullable|numeric|min:0',
            'supplies.*.items.*.quantity'            => 'required|integer|min:1',
            'supplies.*.items.*.unit_price'          => 'required|numeric|min:0',
            'supplies.*.items.*.notes'               => 'nullable|string',
            'supplies.*.items.*.bevel'               => 'nullable|string|max:100',
            'supplies.*.items.*.vertical_grain_cnc'  => 'nullable|string|max:100',
            'supplies.*.items.*.straight_paste_length' => 'nullable|numeric|min:0',
            'supplies.*.items.*.beveled_length'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.vat_moi_length'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.ban_rong_40_59'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.ban_rong_17_39'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.ban_rong_25_35'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.beveled_handle'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.cnc'                   => 'nullable|integer',
            'supplies.*.items.*.direction'             => 'nullable|string|max:100',
        ]);

        // Calculate deadline from order_date + delivery_days if both are provided
        $deadline = $request->deadline;
        if ($request->filled('order_date') && $request->filled('delivery_days')) {
            $orderDate = \Carbon\Carbon::parse($request->order_date);
            $deliveryDays = (int) $request->delivery_days;
            $deadline = $orderDate->addDays($deliveryDays)->format('Y-m-d');
        }

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
        foreach ($request->supplies as $supply) {
            foreach ($supply['items'] as $item) {
                $totalAmount += ($item['unit_price'] * $item['quantity']);
            }
        }

        $order = Order::create([
            'order_code'    => $orderCode,
            'type'          => $request->type ?? 'acrylic',
            'order_date'    => $request->order_date,
            'delivery_days' => $request->delivery_days,
            'customer_id'   => $request->customer_id,
            'customer_name' => $request->customer_name,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'deadline'      => $deadline,
            'notes'         => $request->notes,
            'total_amount'  => $totalAmount,
            'status'        => 'pending',
            'attachments'   => !empty($attachmentPaths) ? json_encode($attachmentPaths) : null,
        ]);

        // Create OrderSupplies and their items
        foreach ($request->supplies as $supplyData) {
            $orderSupply = OrderSupply::create([
                'order_id'    => $order->id,
                'type'        => $order->type,
                'supply_name' => $supplyData['supply_name'] ?? 'Vật tư',
                'quantity'    => $supplyData['quantity'] ?? 1,
            ]);

            // Create items for this supply
            foreach ($supplyData['items'] as $item) {
                $totalPrice = $item['unit_price'] * $item['quantity'];
                
                if ($order->type === 'min_late') {
                    MinLateOrderItem::create([
                        'order_supply_id'       => $orderSupply->id,
                        'name'                  => $item['product_name'],
                        'size'                  => json_encode([
                            'height' => $item['height'] ?? null,
                            'width'  => $item['width'] ?? null,
                        ]),
                        'quantity'              => $item['quantity'] ?? 1,
                        'edge_gluing'           => json_encode($item['edge_gluing'] ?? []),
                        'straight_paste_length' => $item['straight_paste_length'] ?? 0,
                        'beveled_length'        => $item['beveled_length'] ?? 0,
                        'vat_moi_length'        => $item['vat_moi_length'] ?? 0,
                        'ban_rong_40_59'        => $item['ban_rong_40_59'] ?? 0,
                        'ban_rong_17_39'        => $item['ban_rong_17_39'] ?? 0,
                        'ban_rong_25_35'        => $item['ban_rong_25_35'] ?? 0,
                        'beveled_handle'        => $item['beveled_handle'] ?? 0,
                        'cnc'                   => $item['cnc'] ?? 0,
                        'direction'             => $item['direction'] ?? null,
                    ]);
                } else {
                    AcrylicOrderItem::create([
                        'order_supply_id'    => $orderSupply->id,
                        'product_code'        => $item['product_code'] ?? null,
                        'product_name'        => $item['product_name'],
                        'height'              => $item['height'] ?? null,
                        'width'               => $item['width'] ?? null,
                        'grain_direction'     => $item['grain_direction'] ?? 0,
                        'edge_bevel'          => $item['edge_bevel'] ?? null,
                        'wing_area'           => $item['wing_area'] ?? null,
                        'molding_length'      => $item['molding_length'] ?? null,
                        'quantity'            => $item['quantity'],
                        'unit_price'          => $item['unit_price'],
                        'total_price'         => $totalPrice,
                        'notes'               => $item['notes'] ?? null,
                        'bevel'               => $item['bevel'] ?? null,
                        'vertical_grain_cnc'  => $item['vertical_grain_cnc'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('orders.index')->with('success', 'Tạo đơn hàng thành công.');
    }

    public function update(Request $request, Order $acrylicOrder)
    {
        $request->validate([
            'customer_id'    => 'nullable|exists:customers,id',
            'customer_name'  => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'type'           => 'nullable|in:acrylic,min_late,glass',
            'order_date'     => 'nullable|date',
            'delivery_days'  => 'nullable|integer|min:0',
            'deadline'       => 'nullable|date',
            'notes'          => 'nullable|string',
            'status'         => 'nullable|in:pending,processing,completed,cancelled',
            'attachments'    => 'nullable|array',
            'attachments.*'  => 'image|mimes:jpg,jpeg,png|max:2048',
            'delete_attachments' => 'nullable|string',
            'supplies'       => 'required|array|min:1',
            'supplies.*.supply_name'         => 'nullable|string|max:255',
            'supplies.*.quantity'            => 'nullable|numeric|min:0',
            'supplies.*.items'               => 'required|array|min:1',
            'supplies.*.items.*.id'                   => 'nullable|integer',
            'supplies.*.items.*.product_code'        => 'nullable|string|max:50',
            'supplies.*.items.*.product_name'        => 'required|string|max:255',
            'supplies.*.items.*.height'              => 'nullable|numeric|min:0',
            'supplies.*.items.*.width'               => 'nullable|numeric|min:0',
            'supplies.*.items.*.grain_direction'     => 'nullable|in:0,2',
            'supplies.*.items.*.edge_bevel'          => 'nullable|string|max:100',
            'supplies.*.items.*.wing_area'           => 'nullable|numeric|min:0',
            'supplies.*.items.*.molding_length'      => 'nullable|numeric|min:0',
            'supplies.*.items.*.quantity'            => 'required|integer|min:1',
            'supplies.*.items.*.unit_price'          => 'required|numeric|min:0',
            'supplies.*.items.*.notes'               => 'nullable|string',
            'supplies.*.items.*.bevel'               => 'nullable|string|max:100',
            'supplies.*.items.*.vertical_grain_cnc'  => 'nullable|string|max:100',
            'supplies.*.items.*.straight_paste_length' => 'nullable|numeric|min:0',
            'supplies.*.items.*.beveled_length'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.vat_moi_length'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.ban_rong_40_59'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.ban_rong_17_39'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.ban_rong_25_35'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.beveled_handle'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.cnc'                   => 'nullable|integer',
            'supplies.*.items.*.direction'             => 'nullable|string|max:100',
        ]);

        // Calculate deadline from order_date + delivery_days if both are provided
        $deadline = $request->deadline;
        if ($request->filled('order_date') && $request->filled('delivery_days')) {
            $orderDate = \Carbon\Carbon::parse($request->order_date);
            $deliveryDays = (int) $request->delivery_days;
            $deadline = $orderDate->addDays($deliveryDays)->format('Y-m-d');
        }

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
        foreach ($request->supplies as $supply) {
            foreach ($supply['items'] as $item) {
                $totalAmount += ($item['unit_price'] * $item['quantity']);
            }
        }

        $acrylicOrder->update([
            'type'          => $request->type ?? 'acrylic',
            'order_date'    => $request->order_date,
            'delivery_days' => $request->delivery_days,
            'customer_id'   => $request->customer_id,
            'customer_name' => $request->customer_name,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'deadline'      => $deadline,
            'notes'         => $request->notes,
            'total_amount'  => $totalAmount,
            'status'        => $request->status ?? $acrylicOrder->status,
            'attachments'   => !empty($allAttachments) ? json_encode($allAttachments) : null,
        ]);

        // Delete all existing supplies and their items for this order, then recreate
        $existingSupplyIds = $acrylicOrder->supplies()->pluck('id')->toArray();
        AcrylicOrderItem::whereIn('order_supply_id', $existingSupplyIds)->delete();
        MinLateOrderItem::whereIn('order_supply_id', $existingSupplyIds)->delete();
        $acrylicOrder->supplies()->delete();

        // Create new supplies and their items
        foreach ($request->supplies as $supplyData) {
            $orderSupply = OrderSupply::create([
                'order_id'    => $acrylicOrder->id,
                'type'        => $acrylicOrder->type,
                'supply_name' => $supplyData['supply_name'] ?? 'Vật tư',
                'quantity'    => $supplyData['quantity'] ?? 1,
            ]);

            // Create items for this supply
            foreach ($supplyData['items'] as $item) {
                $totalPrice = $item['unit_price'] * $item['quantity'];
                
                if ($acrylicOrder->type === 'min_late') {
                    MinLateOrderItem::create([
                        'order_supply_id'       => $orderSupply->id,
                        'name'                  => $item['product_name'],
                        'size'                  => json_encode([
                            'height' => $item['height'] ?? null,
                            'width'  => $item['width'] ?? null,
                        ]),
                        'quantity'              => $item['quantity'] ?? 1,
                        'edge_gluing'           => json_encode($item['edge_gluing'] ?? []),
                        'straight_paste_length' => $item['straight_paste_length'] ?? 0,
                        'beveled_length'        => $item['beveled_length'] ?? 0,
                        'vat_moi_length'        => $item['vat_moi_length'] ?? 0,
                        'ban_rong_40_59'        => $item['ban_rong_40_59'] ?? 0,
                        'ban_rong_17_39'        => $item['ban_rong_17_39'] ?? 0,
                        'ban_rong_25_35'        => $item['ban_rong_25_35'] ?? 0,
                        'beveled_handle'        => $item['beveled_handle'] ?? 0,
                        'cnc'                   => $item['cnc'] ?? 0,
                        'direction'             => $item['direction'] ?? null,
                    ]);
                } else {
                    AcrylicOrderItem::create([
                        'order_supply_id'    => $orderSupply->id,
                        'product_code'        => $item['product_code'] ?? null,
                        'product_name'        => $item['product_name'],
                        'height'              => $item['height'] ?? null,
                        'width'               => $item['width'] ?? null,
                        'grain_direction'     => $item['grain_direction'] ?? 0,
                        'edge_bevel'          => $item['edge_bevel'] ?? null,
                        'wing_area'           => $item['wing_area'] ?? null,
                        'molding_length'      => $item['molding_length'] ?? null,
                        'quantity'            => $item['quantity'],
                        'unit_price'          => $item['unit_price'],
                        'total_price'         => $totalPrice,
                        'notes'               => $item['notes'] ?? null,
                        'bevel'               => $item['bevel'] ?? null,
                        'vertical_grain_cnc'  => $item['vertical_grain_cnc'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('orders.index')->with('success', 'Cập nhật đơn hàng thành công.');
    }

    public function destroy(Order $acrylicOrder)
    {
        $acrylicOrder->delete();
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
