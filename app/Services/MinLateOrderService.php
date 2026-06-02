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
            'supplies.*.items.*.product_name'        => 'required|string|max:255',
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
        $rules['status'] = 'nullable|in:pending,processing,completed,cancelled';
        $rules['delete_attachments'] = 'nullable|string';
        return $rules;
    }

    /**
     * Store a new Min Late order.
     */
    public function store(Request $request): Order
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

        // Generate order code
        $lastOrder = Order::orderBy('id', 'desc')->first();
        $nextNumber = $lastOrder ? intval(substr($lastOrder->order_code, 2)) + 1 : 1;
        $orderCode = 'DA' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        // Calculate total amount from payment details
        $totalAmount = $this->calculateTotalAmount($request->payment_details ?? []);

        $order = Order::create([
            'order_code'    => $orderCode,
            'type'          => 'min_late',
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

        $this->saveSuppliesAndItems($order, $request->supplies);
        $this->savePaymentDetails($order, $request->payment_details ?? []);

        return $order;
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
        $totalAmount = $this->calculateTotalAmount($request->payment_details ?? []);

        $order->update([
            'type'          => 'min_late',
            'order_date'    => $request->order_date,
            'delivery_days' => $request->delivery_days,
            'customer_id'   => $request->customer_id,
            'customer_name' => $request->customer_name,
            'phone'         => $request->phone,
            'address'       => $request->address,
            'deadline'      => $deadline,
            'notes'         => $request->notes,
            'total_amount'  => $totalAmount,
            'status'        => $request->status ?? $order->status,
            'attachments'   => !empty($allAttachments) ? json_encode($allAttachments) : null,
        ]);

        // Clean up old supplies and items
        $existingSupplyIds = $order->supplies()->pluck('id')->toArray();
        MinLateOrderItem::whereIn('order_supply_id', $existingSupplyIds)->delete();
        $order->supplies()->delete();

        // Recreate supplies and items
        $this->saveSuppliesAndItems($order, $request->supplies);

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
            $totalAmount += ($detail['total'] ?? 0);
        }
        return $totalAmount;
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
                'price'      => $detail['price'] ?? 0,
                'price_only' => $detail['price_only'] ?? 0,
                'total'      => $detail['total'] ?? 0,
            ]);
        }
    }

    /**
     * Save supplies and their min late items helper.
     */
    protected function saveSuppliesAndItems(Order $order, array $suppliesData): void
    {
        foreach ($suppliesData as $supplyData) {
            $orderSupply = OrderSupply::create([
                'order_id'    => $order->id,
                'supply_name' => $supplyData['supply_name'] ?? 'Vật tư',
                'quantity'    => $supplyData['quantity'] ?? 1,
            ]);

            foreach ($supplyData['items'] as $item) {
                MinLateOrderItem::create([
                    'order_supply_id'       => $orderSupply->id,
                    'name'                  => $item['product_name'],
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
            }
        }
    }
}
