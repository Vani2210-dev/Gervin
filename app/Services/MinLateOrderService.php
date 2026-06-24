<?php

namespace App\Services;

use App\Models\Order;
use App\Models\MinLateOrderItem;
use App\Models\OrderSupply;
use App\Models\PaymentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MinLateOrderService
{
    /**
     * Get validation rules for storing a Min Late order.
     */
    public function getStoreRules(): array
    {
        return [
            'customer_id'    => 'nullable|exists:customers,id',
            'customer_name'  => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'type'           => 'required|in:min_late',
            'order_date'     => 'nullable|date',
            'delivery_days'  => 'nullable|numeric|min:0',
            'deadline'       => 'nullable|date',
            'notes'          => 'nullable|string',
            'customer_policy'=> 'nullable|string',
            'attachments'    => 'nullable|array',
            'attachments.*'  => 'image|mimes:jpg,jpeg,png|max:2048',
            'supplies'       => 'nullable|array',
            'supplies.*.order_supply_code'   => 'nullable|string|max:255',
            'supplies.*.supply_name'         => 'nullable|string|max:255',
            'supplies.*.quantity'            => 'nullable|numeric|min:0',
            'supplies.*.items'               => 'nullable|array',
            'supplies.*.items.*.id'                   => 'nullable|integer',
            'supplies.*.items.*.product_code'        => 'nullable|string|max:50',
            'supplies.*.items.*.product_name'        => 'nullable|string|max:255',
            'supplies.*.items.*.thickness'           => 'nullable|string|max:100',
            'supplies.*.items.*.height'              => 'nullable|numeric|min:0',
            'supplies.*.items.*.width'               => 'nullable|numeric|min:0',
            'supplies.*.items.*.quantity'            => 'required|integer|min:1',
            'supplies.*.items.*.edge_gluing'         => 'nullable|array',
            'supplies.*.items.*.straight_paste_length' => 'nullable|numeric|min:0',
            'supplies.*.items.*.beveled_length'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.vat_moi_length'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.ban_rong_40_59'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.ban_rong_17_39'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.ban_rong_25_35'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.beveled_handle'        => 'nullable|numeric|min:0',
            'supplies.*.items.*.cnc'                   => 'nullable|integer',
            'supplies.*.items.*.direction'             => 'nullable|string|max:100',
            'supplies.*.items.*.notes'               => 'nullable|string',
            'payment_details'                          => 'nullable|array',
            'payment_details.*.name'                   => 'required|string|max:255',
            'payment_details.*.unit'                   => 'nullable|string|max:255',
            'payment_details.*.quantity'               => 'nullable|numeric|min:0',
            'payment_details.*.price'                  => 'required|numeric|min:0',
            'payment_details.*.price_only'             => 'nullable|numeric|min:0',
            'payment_details.*.total'                  => 'required|numeric|min:0',
        ];
    }

    /**
     * Get validation rules for updating a Min Late order.
     */
    public function getUpdateRules(): array
    {
        $rules = $this->getStoreRules();
        $rules['status'] = 'nullable|in:draft,pending,processing,completed,cancelled';
        $rules['delete_attachments'] = 'nullable|string';
        return $rules;
    }

    /**
     * Update an existing Min Late order.
     */
    public function update(Request $request, Order $order): Order
    {
        // Calculate deadline
        $deadline = $request->deadline;
        if ($request->filled('order_date') && $request->filled('delivery_days')) {
            $orderDate = \Carbon\Carbon::parse($request->order_date);
            $deliveryDays = (float) $request->delivery_days;
            $deadline = $orderDate->addHours($deliveryDays * 24)->format('Y-m-d H:i:s');
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
        $totalAmount = $this->calculateTotalAmount($request->payment_details ?? []);

        // Auto-create or link customer if customer_name filled but no customer_id
        $customerId = $request->customer_id ?: null;
        if (!$customerId && $request->filled('customer_name')) {
            $existing = \App\Models\Customer::where('name', trim($request->customer_name))->first();
            if ($existing) {
                $customerId = $existing->id;
            } else {
                $lastCustomer = \App\Models\Customer::orderBy('id', 'desc')->first();
                $nextNumber   = $lastCustomer ? intval(substr($lastCustomer->customer_code, 2)) + 1 : 1;
                $customerCode = 'KH' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
                $newCustomer = \App\Models\Customer::create([
                    'customer_code' => $customerCode,
                    'name'          => trim($request->customer_name),
                    'phone'         => $request->phone,
                    'address'       => $request->address,
                ]);
                $customerId = $newCustomer->id;
            }
        }

        $order->update([
            'type'          => 'min_late',
            'order_date'    => $request->order_date,
            'delivery_days' => $request->delivery_days,
            'customer_id'   => $customerId,
            'customer_name' => $request->customer_name,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'deadline'      => $deadline,
            'notes'         => $request->notes,
            'customer_policy' => $request->customer_policy,
            'total_amount'  => $totalAmount,
            'status'        => $order->status === 'draft' ? 'pending' : ($request->status ?? $order->status),
            'attachments'   => !empty($allAttachments) ? json_encode($allAttachments) : null,
        ]);

        // Clean up old supplies and items (explicitly delete codes first to avoid duplicate key on unique constraint)
        $existingSupplyIds = $order->supplies()->pluck('id')->toArray();
        if (!empty($existingSupplyIds)) {
            $existingItemIds = MinLateOrderItem::whereIn('order_supply_id', $existingSupplyIds)->pluck('id')->toArray();
            if (!empty($existingItemIds)) {
                \App\Models\MinLateOrderItemCode::whereIn('min_late_order_item_id', $existingItemIds)->delete();
            }
            MinLateOrderItem::whereIn('order_supply_id', $existingSupplyIds)->delete();
        }
        $order->supplies()->delete();

        // Recreate supplies and items
        $this->saveSuppliesAndItems($order, $request->supplies ?? []);

        // Recreate payment details
        $order->paymentDetails()->delete();
        $this->savePaymentDetails($order, $request->payment_details ?? []);

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
     * Calculate total amount helper using payment details.
     */
    protected function calculateTotalAmount(array $paymentDetails): float
    {
        $totalAmount = 0;
        foreach ($paymentDetails as $detail) {
            $totalAmount += round($detail['total'] ?? 0);
        }
        return round($totalAmount, -3);
    }

    /**
     * Save payment details helper.
     */
    protected function savePaymentDetails(Order $order, array $paymentDetailsData): void
    {
        foreach ($paymentDetailsData as $detail) {
            if (empty($detail['name'])) continue;
            
            PaymentDetail::create([
                'order_id'   => $order->id,
                'name'       => $detail['name'],
                'unit'       => $detail['unit'] ?? null,
                'quantity'   => $detail['quantity'] ?? 0,
                'price'      => round($detail['price'] ?? 0),
                'price_only' => round($detail['price_only'] ?? 0),
                'total'      => round($detail['total'] ?? 0),
            ]);
        }
    }

    /**
     * Save supplies and their min late items helper.
     */
    protected function saveSuppliesAndItems(Order $order, array $suppliesData): void
    {
        $globalPieceIndex = 1;
        foreach ($suppliesData as $supplyData) {
            $orderSupply = OrderSupply::create([
                'order_id'          => $order->id,
                'order_supply_code' => $supplyData['order_supply_code'] ?? null,
                'supply_name'       => $supplyData['supply_name'] ?? 'Vật tư',
                'quantity'          => $supplyData['quantity'] ?? 1,
            ]);

            if (!isset($supplyData['items']) || !is_array($supplyData['items'])) continue;
            foreach ($supplyData['items'] as $item) {
                $orderItem = MinLateOrderItem::create([
                    'order_supply_id'       => $orderSupply->id,
                    'name'                  => $item['product_name'],
                    'thickness'             => $item['thickness'] ?? null,
                    'size'                  => [
                        'height' => $item['height'] ?? null,
                        'width'  => $item['width'] ?? null,
                    ],
                    'quantity'              => $item['quantity'] ?? 1,
                    'edge_gluing'           => $item['edge_gluing'] ?? [],
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

                // Always regenerate codes from order_code + supply_code + global sequential index
                // Never parse product_code from the form — supply codes may contain dots
                // causing explode('.') to split incorrectly and produce wrong codes.
                $supplyCodePart = $orderSupply->order_supply_code ?? '';
                $prefix = $order->order_code . ($supplyCodePart !== '' ? '.' . $supplyCodePart : '');

                $qty = intval($item['quantity']) ?: 1;
                for ($i = 0; $i < $qty; $i++) {
                    $orderItem->codes()->create([
                        'product_id' => $prefix . '.' . ($globalPieceIndex + $i),
                        'status'     => [],
                    ]);
                }
                $globalPieceIndex += $qty;
            }
        }
    }
}
