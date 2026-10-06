<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentDetail;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceOrderService
{
    /**
     * Get validation rules for storing a Service & Supply order.
     */
    public function getStoreRules(): array
    {
        return [
            'customer_id'       => 'nullable|exists:customers,id',
            'customer_name'     => 'required|string|max:255',
            'phone'             => 'nullable|string|max:20',
            'address'           => 'nullable|string',
            'type'              => 'required|in:service',
            'order_date'        => 'nullable|date',
            'delivery_days'     => 'nullable|numeric|min:0',
            'deadline'          => 'nullable|date',
            'notes'             => 'nullable|string',
            'customer_policy'   => 'nullable|string',
            'attachments'       => 'nullable|array',
            'attachments.*'     => 'image|mimes:jpg,jpeg,png|max:2048',
            'payment_details'   => 'nullable|array',
            'payment_details.*.name'     => 'required|string|max:255',
            'payment_details.*.unit'     => 'nullable|string|max:255',
            'payment_details.*.quantity' => 'nullable|numeric|min:0',
            'payment_details.*.price'    => 'nullable|numeric|min:0',
            'payment_details.*.price_only'=> 'nullable|numeric|min:0',
            'payment_details.*.total'    => 'nullable|string',
        ];
    }

    /**
     * Update an existing Service & Supply order.
     */
    public function update(Request $request, Order $order): Order
    {
        $allAttachments = [];
        if ($order->attachments) {
            $allAttachments = json_decode($order->attachments, true) ?? [];
        }

        if ($request->filled('delete_attachments')) {
            $toDelete = explode(',', $request->delete_attachments);
            $allAttachments = array_values(array_filter($allAttachments, function ($path) use ($toDelete) {
                return !in_array($path, $toDelete);
            }));
        }

        $newAttachments = $this->uploadAttachments($request);
        $allAttachments = array_merge($allAttachments, $newAttachments);

        // Deadline calculation
        $deadline = $request->deadline;
        if (!$deadline && $request->filled('order_date') && $request->filled('delivery_days')) {
            $days = floatval($request->delivery_days);
            $orderDate = Carbon::parse($request->order_date);
            $deadline = (floor($days) == $days)
                ? $orderDate->copy()->addDays(intval($days))
                : $orderDate->copy()->addHours(round($days * 24));
        }

        // Tính tiền từ bảng chi tiết hóa đơn
        $subTotal = $this->calculateTotalAmount($request->payment_details ?? []);
        $discountPercent = floatval($request->discount_percent ?? 0);
        $vatPercent = floatval($request->vat_percent ?? 0);
        $discountAmount = round($subTotal * ($discountPercent / 100), -3);
        $vatAmount = round(($subTotal - $discountAmount) * ($vatPercent / 100), -3);
        $totalAmount = $subTotal - $discountAmount + $vatAmount;

        // Auto-create or link customer
        $customerId = $request->customer_id ?: null;
        if (!$customerId && $request->filled('customer_name')) {
            $existing = Customer::where('name', trim($request->customer_name))->first();
            if ($existing) {
                $customerId = $existing->id;
                if ($request->has('customer_initial_debt')) {
                    $existing->update(['debt' => $request->input('customer_initial_debt', 0)]);
                }
            } else {
                $lastCustomer = Customer::orderBy('id', 'desc')->first();
                $nextNumber   = $lastCustomer ? intval(substr($lastCustomer->customer_code, 2)) + 1 : 1;
                $customerCode = 'KH' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
                $newCustomer = Customer::create([
                    'customer_code' => $customerCode,
                    'name'          => trim($request->customer_name),
                    'phone'         => $request->phone,
                    'address'       => $request->address,
                    'debt'          => $request->input('customer_initial_debt', 0),
                ]);
                if ($authUser = auth()->user()) {
                    if (!$authUser->hasRole('Admin')) {
                        $newCustomer->users()->sync([$authUser->id]);
                    }
                }
                $customerId = $newCustomer->id;
            }
        } elseif ($customerId && $request->has('customer_initial_debt')) {
            Customer::where('id', $customerId)->update(['debt' => $request->input('customer_initial_debt', 0)]);
        }

        $updateData = [
            'type'            => 'service',
            'order_date'      => $request->order_date,
            'delivery_days'   => $request->delivery_days,
            'customer_id'     => $customerId,
            'customer_name'   => $request->customer_name,
            'phone'           => $request->phone,
            'address'         => $request->address,
            'deadline'        => $deadline,
            'notes'           => $request->notes,
            'customer_policy' => $request->customer_policy,
            'discount_percent'=> $discountPercent,
            'discount_amount' => $discountAmount,
            'vat_percent'     => $vatPercent,
            'vat_amount'      => $vatAmount,
            'total_amount'    => $totalAmount,
            'status'          => ($request->input('action') === 'draft' || $request->status === 'draft')
                ? 'draft'
                : ($order->status === 'draft' ? 'pending' : ($request->status ?? $order->status)),
            'attachments'     => !empty($allAttachments) ? json_encode($allAttachments) : null,
        ];

        $order->update($updateData);

        // Xóa supplies/items nếu có (đơn dịch vụ không có tấm sản phẩm)
        $order->supplies()->delete();

        // Lưu bảng Chi tiết hóa đơn dịch vụ & vật tư
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
            $rawTotal = str_replace('.', '', $detail['total'] ?? 0);
            $totalAmount += round($rawTotal);
        }
        return round($totalAmount, -3);
    }

    /**
     * Save payment details helper.
     */
    protected function savePaymentDetails(Order $order, array $paymentDetails): void
    {
        foreach ($paymentDetails as $detail) {
            if (empty($detail['name']) && empty($detail['price'])) {
                continue;
            }

            $rawPrice = str_replace('.', '', $detail['price'] ?? 0);
            $rawPriceOnly = str_replace('.', '', $detail['price_only'] ?? 0);
            $rawTotal = str_replace('.', '', $detail['total'] ?? 0);

            PaymentDetail::create([
                'order_id'   => $order->id,
                'name'       => $detail['name'] ?? '',
                'unit'       => $detail['unit'] ?? '',
                'quantity'   => floatval($detail['quantity'] ?? 1),
                'price'      => floatval($rawPrice),
                'price_only' => floatval($rawPriceOnly),
                'total'      => floatval($rawTotal),
            ]);
        }
    }
}
