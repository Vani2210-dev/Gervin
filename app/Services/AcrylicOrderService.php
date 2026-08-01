<?php

namespace App\Services;

use App\Models\Order;
use App\Models\AcrylicOrderItem;
use App\Models\OrderSupply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AcrylicOrderService
{
    /**
     * Get validation rules for storing an Acrylic/Glass order.
     */
    public function getStoreRules(): array
    {
        return [
            'customer_id'    => 'nullable|exists:customers,id',
            'customer_name'  => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'type'           => 'nullable|in:acrylic,glass',
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
            'supplies.*.items.*.grain_direction'     => 'nullable|string|max:100',
            'supplies.*.items.*.edge_bevel'          => 'nullable|string|max:100',
            'supplies.*.items.*.wing_area'           => 'nullable|numeric|min:0',
            'supplies.*.items.*.molding_length'      => 'nullable|numeric|min:0',
            'supplies.*.items.*.quantity'            => 'required|integer|min:1',
            'supplies.*.items.*.unit_price'          => 'required|numeric|min:0',
            'supplies.*.items.*.notes'               => 'nullable|string',
            'supplies.*.items.*.bevel'               => 'nullable|string|max:100',
            'supplies.*.items.*.vertical_grain_cnc'  => 'nullable|string|max:100',
            'supplies.*.items.*.offset_left'          => 'nullable|integer',
            'supplies.*.items.*.offset_right'         => 'nullable|integer',
            'supplies.*.items.*.offset_top'           => 'nullable|integer',
            'supplies.*.items.*.offset_bottom'        => 'nullable|integer',
            'supplies.*.items.*.mill_left'            => 'nullable|integer',
            'supplies.*.items.*.mill_right'           => 'nullable|integer',
            'supplies.*.items.*.mill_top'             => 'nullable|integer',
            'supplies.*.items.*.mill_bottom'          => 'nullable|integer',
            'supplies.*.items.*.mill_width'           => 'nullable|integer',
            'supplies.*.items.*.mill_depth'           => 'nullable|integer',
            'supplies.*.items.*.mill_left_2'          => 'nullable|integer',
            'supplies.*.items.*.mill_right_2'         => 'nullable|integer',
            'supplies.*.items.*.mill_top_2'           => 'nullable|integer',
            'supplies.*.items.*.mill_bottom_2'        => 'nullable|integer',
            'supplies.*.items.*.mill_width_2'         => 'nullable|integer',
            'supplies.*.items.*.mill_depth_2'         => 'nullable|integer',
            'supplies.*.items.*.is_labor'              => 'nullable|in:0,1',
            'payment_details'                          => 'nullable|array',
            'payment_details.*.name'                   => 'required|string|max:255',
            'payment_details.*.unit'                   => 'nullable|string|max:100',
            'payment_details.*.quantity'               => 'nullable|numeric|min:0',
            'payment_details.*.price'                  => 'required|numeric|min:0',
            'payment_details.*.total'                  => 'required|string',
        ];
    }

    /**
     * Get validation rules for updating an Acrylic/Glass order.
     */
    public function getUpdateRules(): array
    {
        $rules = $this->getStoreRules();
        $rules['status'] = 'nullable|in:draft,pending,transferred,in_production,completed,cancelled';
        $rules['delete_attachments'] = 'nullable|string';
        return $rules;
    }

    /**
     * Update an existing Acrylic/Glass order.
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
        $isRework = $order->relation_type === 'rework';
        $subTotal = $this->calculateTotalAmount($request->supplies ?? [], $request->payment_details ?? [], $isRework);
        $discountPercent = (float) $request->input('discount_percent', 0);
        $vatPercent = (float) $request->input('vat_percent', 0);
        
        $rawDiscountAmount = $subTotal * ($discountPercent / 100);
        $discountAmount = round($rawDiscountAmount, -3);

        $rawVatAmount = ($subTotal - $discountAmount) * ($vatPercent / 100);
        $vatAmount = round($rawVatAmount, -3);

        $totalAmount = $subTotal - $discountAmount + $vatAmount;

        // Auto-create or link customer if customer_name filled but no customer_id
        $customerId = $request->customer_id ?: null;
        if (!$customerId && $request->filled('customer_name')) {
            $existing = \App\Models\Customer::where('name', trim($request->customer_name))->first();
            if ($existing) {
                $customerId = $existing->id;
                if ($request->has('customer_initial_debt')) {
                    $existing->update(['debt' => $request->input('customer_initial_debt', 0)]);
                }
            } else {
                $lastCustomer = \App\Models\Customer::orderBy('id', 'desc')->first();
                $nextNumber   = $lastCustomer ? intval(substr($lastCustomer->customer_code, 2)) + 1 : 1;
                $customerCode = 'KH' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
                $newCustomer = \App\Models\Customer::create([
                    'customer_code' => $customerCode,
                    'name'          => trim($request->customer_name),
                    'phone'         => $request->phone,
                    'address'       => $request->address,
                    'debt'          => $request->input('customer_initial_debt', 0),
                ]);
                $customerId = $newCustomer->id;
            }
        } elseif ($customerId && $request->has('customer_initial_debt')) {
            \App\Models\Customer::where('id', $customerId)->update(['debt' => $request->input('customer_initial_debt', 0)]);
        }

        $order->update([
            'type'          => $request->type ?? 'acrylic',
            'order_date'    => $request->order_date,
            'delivery_days' => $request->delivery_days,
            'customer_id'   => $customerId,
            'customer_name' => $request->customer_name,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'deadline'      => $deadline,
            'notes'         => $request->notes,
            'customer_policy' => $request->customer_policy,
            'discount_percent'=> $discountPercent,
            'discount_amount' => $discountAmount,
            'vat_percent'     => $vatPercent,
            'vat_amount'      => $vatAmount,
            'total_amount'    => $totalAmount,
            'status'          => $order->status === 'draft' ? 'pending' : ($request->status ?? $order->status),
            'attachments'   => !empty($allAttachments) ? json_encode($allAttachments) : null,
        ]);

        // Clean up old supplies and items (explicitly delete codes first to avoid duplicate key on unique constraint)
        $existingSupplyIds = $order->supplies()->pluck('id')->toArray();
        if (!empty($existingSupplyIds)) {
            $existingItemIds = AcrylicOrderItem::whereIn('order_supply_id', $existingSupplyIds)->pluck('id')->toArray();
            if (!empty($existingItemIds)) {
                \App\Models\AcrylicOrderItemCode::whereIn('acrylic_order_item_id', $existingItemIds)->delete();
            }
            AcrylicOrderItem::whereIn('order_supply_id', $existingSupplyIds)->delete();
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
     * Calculate total amount helper.
     */
    protected function calculateTotalAmount(array $supplies, array $paymentDetails, bool $isRework = false): float
    {
        $totalAmount = 0;
        if (!$isRework) {
            foreach ($supplies as $supply) {
                if (!isset($supply['items']) || !is_array($supply['items'])) continue;
                foreach ($supply['items'] as $item) {
                    $itemTotal = isset($item['total_price']) ? floatval(str_replace('.', '', $item['total_price'])) : ($item['unit_price'] * $item['quantity']);
                    $totalAmount += $itemTotal;
                }
            }
        }
        foreach ($paymentDetails as $detail) {
            if (empty($detail['name'])) continue;
            $detailTotal = isset($detail['total']) ? floatval(str_replace('.', '', $detail['total'])) : (($detail['price'] ?? 0) * ($detail['quantity'] ?? 0));
            $totalAmount += $detailTotal;
        }
        return round($totalAmount, -3);
    }

    /**
     * Save supplies and their acrylic items helper.
     */
    protected function saveSuppliesAndItems(Order $order, array $suppliesData): void
    {
        $globalPieceIndex = 1;
        $isRework = $order->relation_type === 'rework';
        foreach ($suppliesData as $supplyData) {
            $orderSupply = OrderSupply::create([
                'order_id'          => $order->id,
                'order_supply_code' => $supplyData['order_supply_code'] ?? null,
                'supply_name'       => $supplyData['supply_name'] ?? 'Vật tư',
                'quantity'          => $supplyData['quantity'] ?? 1,
            ]);

            if (!isset($supplyData['items']) || !is_array($supplyData['items'])) continue;
            foreach ($supplyData['items'] as $item) {
                $unitPrice = round($item['unit_price'] ?? 0);
                $totalPrice = isset($item['total_price']) ? floatval(str_replace('.', '', $item['total_price'])) : (($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0));
                $totalPrice = round($totalPrice);
                
                $orderItem = AcrylicOrderItem::create([
                    'order_supply_id'    => $orderSupply->id,
                    'product_name'        => $item['product_name'],
                    'thickness'           => $item['thickness'] ?? null,
                    'height'              => $item['height'] ?? null,
                    'width'               => $item['width'] ?? null,
                    'grain_direction'     => isset($item['grain_direction']) ? (string)$item['grain_direction'] : '0',
                    'edge_bevel'          => $item['edge_bevel'] ?? null,
                    'wing_area'           => $item['wing_area'] ?? null,
                    'molding_length'      => $item['molding_length'] ?? null,
                    'quantity'            => $item['quantity'],
                    'unit_price'          => $unitPrice,
                    'total_price'         => $totalPrice,
                    'notes'               => $item['notes'] ?? null,
                    'bevel'               => $item['bevel'] ?? null,
                    'vertical_grain_cnc'  => $item['vertical_grain_cnc'] ?? null,
                    'offset_left'         => isset($item['offset_left'])   && $item['offset_left']   !== '' ? intval($item['offset_left'])   : null,
                    'offset_right'        => isset($item['offset_right'])  && $item['offset_right']  !== '' ? intval($item['offset_right'])  : null,
                    'offset_top'          => isset($item['offset_top'])    && $item['offset_top']    !== '' ? intval($item['offset_top'])    : null,
                    'offset_bottom'       => isset($item['offset_bottom']) && $item['offset_bottom'] !== '' ? intval($item['offset_bottom']) : null,
                    'mill_left'           => isset($item['mill_left'])     && $item['mill_left']     !== '' ? intval($item['mill_left'])     : null,
                    'mill_right'          => isset($item['mill_right'])    && $item['mill_right']    !== '' ? intval($item['mill_right'])    : null,
                    'mill_top'            => isset($item['mill_top'])      && $item['mill_top']      !== '' ? intval($item['mill_top'])      : null,
                    'mill_bottom'         => isset($item['mill_bottom'])   && $item['mill_bottom']   !== '' ? intval($item['mill_bottom'])   : null,
                    'mill_width'          => isset($item['mill_width'])    && $item['mill_width']    !== '' ? intval($item['mill_width'])    : null,
                    'mill_depth'          => isset($item['mill_depth'])    && $item['mill_depth']    !== '' ? intval($item['mill_depth'])    : null,
                    'mill_left_2'         => isset($item['mill_left_2'])   && $item['mill_left_2']   !== '' ? intval($item['mill_left_2'])   : null,
                    'mill_right_2'        => isset($item['mill_right_2'])  && $item['mill_right_2']  !== '' ? intval($item['mill_right_2'])  : null,
                    'mill_top_2'          => isset($item['mill_top_2'])    && $item['mill_top_2']    !== '' ? intval($item['mill_top_2'])    : null,
                    'mill_bottom_2'       => isset($item['mill_bottom_2']) && $item['mill_bottom_2'] !== '' ? intval($item['mill_bottom_2']) : null,
                    'mill_width_2'        => isset($item['mill_width_2'])  && $item['mill_width_2']  !== '' ? intval($item['mill_width_2'])  : null,
                    'mill_depth_2'        => isset($item['mill_depth_2'])  && $item['mill_depth_2']  !== '' ? intval($item['mill_depth_2'])  : null,
                    'old_size'            => $item['old_size'] ?? null,
                ]);

                // Labor rows (Công giả dày) do NOT get item codes — they are billing-only rows
                $isLabor = !empty($item['is_labor']) && $item['is_labor'] == '1';
                $isLabor = $isLabor || (($item['product_name'] ?? '') === 'Công giả dày');

                if (!$isLabor) {
                    // Always regenerate codes from order_code + supply_code + global sequential index
                    // Never parse product_code from the form — supply codes may contain dots (e.g. 'GV05.TP')
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
                // else: labor row — no codes, globalPieceIndex unchanged
            }
        }
    }

    /**
     * Save payment details helper.
     */
    protected function savePaymentDetails(Order $order, array $paymentDetailsData): void
    {
        foreach ($paymentDetailsData as $detail) {
            if (empty($detail['name'])) continue;
            
            \App\Models\PaymentDetail::create([
                'order_id'   => $order->id,
                'name'       => $detail['name'],
                'unit'       => $detail['unit'] ?? null,
                'quantity'   => $detail['quantity'] ?? 0,
                'price'      => round($detail['price'] ?? 0),
                'price_only' => 0,
                'total'      => round(str_replace('.', '', $detail['total'] ?? 0)),
            ]);
        }
    }
}
