<?php

namespace App\Services;

use App\Models\Order;
use App\Models\GlassOrderItem;
use App\Models\OrderSupply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GlassOrderService
{
    /**
     * Get validation rules for storing a Glass order.
     */
    public function getStoreRules(): array
    {
        return [
            'customer_id'    => 'nullable|exists:customers,id',
            'customer_name'  => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'type'           => 'required|in:glass',
            'order_date'     => 'nullable|date',
            'delivery_days'  => 'nullable|integer|min:0',
            'deadline'       => 'nullable|date',
            'notes'          => 'nullable|string',
            'attachments'    => 'nullable|array',
            'attachments.*'  => 'image|mimes:jpg,jpeg,png|max:2048',
            'supplies'       => 'nullable|array',
            'supplies.*.order_supply_code'   => 'nullable|string|max:255',
            'supplies.*.supply_name'         => 'nullable|string|max:255',
            'supplies.*.quantity'            => 'nullable|numeric|min:0',
            'supplies.*.items'               => 'nullable|array',
            'supplies.*.items.*.id'                   => 'nullable|integer',
            'supplies.*.items.*.product_name'        => 'nullable|string|max:255',
            'supplies.*.items.*.product_code'        => 'nullable|string|max:50',
            'supplies.*.items.*.wing_opening_direction' => 'nullable|string|max:100',
            'supplies.*.items.*.aluminum_color'      => 'nullable|string|max:100',
            'supplies.*.items.*.glass_color'         => 'nullable|string|max:100',
            'supplies.*.items.*.height'              => 'nullable|numeric|min:0',
            'supplies.*.items.*.width'               => 'nullable|numeric|min:0',
            'supplies.*.items.*.unit'                => 'nullable|string|max:50',
            'supplies.*.items.*.wing_quantity'       => 'required|integer|min:1',
            'supplies.*.items.*.area_m2'             => 'nullable|numeric|min:0',
            'supplies.*.items.*.unit_price'          => 'required|numeric|min:0',
            'supplies.*.items.*.notes'               => 'nullable|string',
        ];
    }

    /**
     * Get validation rules for updating a Glass order.
     */
    public function getUpdateRules(): array
    {
        $rules = $this->getStoreRules();
        $rules['status'] = 'nullable|in:draft,pending,processing,completed,cancelled';
        $rules['delete_attachments'] = 'nullable|string';
        return $rules;
    }

    /**
     * Update an existing Glass order.
     */
    public function update(Request $request, Order $order): Order
    {
        // Calculate deadline
        $deadline = $request->deadline;
        if ($request->filled('order_date') && $request->filled('delivery_days')) {
            $orderDate = \Carbon\Carbon::parse($request->order_date);
            $deliveryDays = (int) $request->delivery_days;
            $deadline = $orderDate->addDays($deliveryDays)->format('Y-m-d');
        }

        // Handle file uploads
        $attachmentPaths = $this->uploadAttachments($request);

        // Handle deleted attachments
        $existingAttachments = json_decode($order->attachments, true) ?? [];
        if ($request->filled('delete_attachments')) {
            $deletedAttachments = json_decode($request->delete_attachments, true) ?? [];
            foreach ($deletedAttachments as $deletedPath) {
                $existingAttachments = array_filter($existingAttachments, function($path) use ($deletedPath) {
                    return $path !== $deletedPath;
                });
                if (Storage::disk('private')->exists($deletedPath)) {
                    Storage::disk('private')->delete($deletedPath);
                }
            }
        }

        $allAttachments = array_merge($existingAttachments, $attachmentPaths);
        $totalAmount = $this->calculateTotalAmount($request->supplies ?? []);

        $order->update([
            'type'          => 'glass',
            'order_date'    => $request->order_date,
            'delivery_days' => $request->delivery_days,
            'customer_id'   => $request->customer_id,
            'customer_name' => $request->customer_name,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'deadline'      => $deadline,
            'notes'         => $request->notes,
            'total_amount'  => $totalAmount,
            'status'        => $order->status === 'draft' ? 'pending' : ($request->status ?? $order->status),
            'attachments'   => !empty($allAttachments) ? json_encode($allAttachments) : null,
        ]);

        // Clean up old supplies and items
        $existingSupplyIds = $order->supplies()->pluck('id')->toArray();
        GlassOrderItem::whereIn('order_supply_id', $existingSupplyIds)->delete();
        $order->supplies()->delete();

        // Recreate supplies and items
        $this->saveSuppliesAndItems($order, $request->supplies ?? []);

        return $order;
    }

    /**
     * Upload attachments helper.
     */
    protected function uploadAttachments(Request $request): array
    {
        $paths = [];
        if ($request->hasFile('attachments')) {
            $count = 0;
            foreach ($request->file('attachments') as $file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension    = strtolower($file->getClientOriginalExtension());
                $cleanName    = Str::slug($originalName) . '_' . time() . '_' . $count . '.' . $extension;
                $filePath     = $file->storeAs('anh-don-hang', $cleanName, 'private');
                $paths[]      = $filePath;
                $count++;
            }
        }
        return $paths;
    }

    /**
     * Calculate total amount helper.
     */
    protected function calculateTotalAmount(array $supplies): float
    {
        $totalAmount = 0;
        foreach ($supplies as $supply) {
            foreach ($supply['items'] as $item) {
                $itemTotal = isset($item['total_price']) ? floatval($item['total_price']) : ($item['unit_price'] * ($item['area_m2'] ?: $item['wing_quantity']));
                $totalAmount += $itemTotal;
            }
        }
        return round($totalAmount);
    }

    /**
     * Save supplies and their glass items helper.
     */
    protected function saveSuppliesAndItems(Order $order, array $suppliesData): void
    {
        foreach ($suppliesData as $supplyData) {
            $orderSupply = OrderSupply::create([
                'order_id'          => $order->id,
                'order_supply_code' => $supplyData['order_supply_code'] ?? null,
                'supply_name'       => $supplyData['supply_name'] ?? 'Vật tư',
                'quantity'          => $supplyData['quantity'] ?? 1,
            ]);

            foreach ($supplyData['items'] as $item) {
                $height = $item['height'] ?? 0;
                $width = $item['width'] ?? 0;
                $wingQuantity = $item['wing_quantity'] ?? 1;
                $areaM2 = $item['area_m2'] ?? (($height * $width * $wingQuantity) / 1000000);
                
                $totalPrice = isset($item['total_price']) ? floatval($item['total_price']) : ($areaM2 > 0 ? ($areaM2 * $item['unit_price']) : ($wingQuantity * $item['unit_price']));
                $totalPrice = round($totalPrice);

                GlassOrderItem::create([
                    'order_supply_id'        => $orderSupply->id,
                    'product_name'           => $item['product_name'],
                    'product_code'           => $item['product_code'] ?? null,
                    'wing_opening_direction' => $item['wing_opening_direction'] ?? null,
                    'aluminum_color'         => $item['aluminum_color'] ?? null,
                    'glass_color'            => $item['glass_color'] ?? null,
                    'height'                 => $item['height'] ?? null,
                    'width'                  => $item['width'] ?? null,
                    'unit'                   => $item['unit'] ?? 'Bộ',
                    'wing_quantity'          => $item['wing_quantity'] ?? 1,
                    'area_m2'                => $areaM2,
                    'unit_price'             => round($item['unit_price']),
                    'total_price'            => $totalPrice,
                    'notes'                  => $item['notes'] ?? null,
                ]);
            }
        }
    }
}
