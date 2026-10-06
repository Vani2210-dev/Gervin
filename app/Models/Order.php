<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected static function booted()
    {
        static::created(function ($order) {
            $order->adjustCustomerDebtOnCreate();
        });

        static::updated(function ($order) {
            $order->adjustCustomerDebtOnUpdate();
        });

        static::deleted(function ($order) {
            $order->adjustCustomerDebtOnDelete();
        });
    }

    public function adjustCustomerDebtOnCreate()
    {
        if ($this->customer && !in_array($this->status, ['draft', 'pending', 'cancelled']) && $this->board_return_status !== 'returned') {
            $amount = round($this->total_amount, -3);
            $this->customer->increment('debt', $amount);
        }
    }

    public function adjustCustomerDebtOnUpdate()
    {
        $oldStatus = $this->getOriginal('status');
        $newStatus = $this->status;
        $oldAmount = round($this->getOriginal('total_amount') ?? 0, -3);
        $newAmount = round($this->total_amount, -3);
        $oldCustomerId = $this->getOriginal('customer_id');
        $newCustomerId = $this->customer_id;
        $oldReturnStatus = $this->getOriginal('board_return_status');
        $newReturnStatus = $this->board_return_status;

        $wasValid = !in_array($oldStatus, ['draft', 'pending', 'cancelled']) && $oldReturnStatus !== 'returned';
        $isValid = !in_array($newStatus, ['draft', 'pending', 'cancelled']) && $newReturnStatus !== 'returned';

        // Trường hợp 1: Thay đổi khách hàng
        if ($oldCustomerId != $newCustomerId) {
            if ($wasValid && $oldCustomerId) {
                $oldCustomer = Customer::find($oldCustomerId);
                if ($oldCustomer) {
                    $oldCustomer->decrement('debt', $oldAmount);
                }
            }
            if ($isValid && $newCustomerId) {
                $newCustomer = Customer::find($newCustomerId);
                if ($newCustomer) {
                    $newCustomer->increment('debt', $newAmount);
                }
            }
            return;
        }

        // Trường hợp 2: Cùng một khách hàng
        if ($this->customer) {
            if ($wasValid && $isValid) {
                // Cả hai trạng thái đều hợp lệ, cập nhật phần chênh lệch
                $diff = $newAmount - $oldAmount;
                if ($diff != 0) {
                    $this->customer->increment('debt', $diff);
                }
            } elseif ($wasValid && !$isValid) {
                // Đơn hàng chuyển sang trạng thái không tính nợ (hủy/nháp/đã trả ván...), trừ nợ cũ
                $this->customer->decrement('debt', $oldAmount);
            } elseif (!$wasValid && $isValid) {
                // Đơn hàng chuyển sang trạng thái tính nợ, cộng thêm nợ mới
                $this->customer->increment('debt', $newAmount);
            }
        }
    }

    public function adjustCustomerDebtOnDelete()
    {
        if ($this->customer && !in_array($this->status, ['draft', 'pending', 'cancelled']) && $this->board_return_status !== 'returned') {
            $amount = round($this->total_amount, -3);
            $this->customer->decrement('debt', $amount);
        }
    }

    protected $fillable = [
        'parent_id',
        'relation_type',
        'board_return_status',
        'order_code',
        'type',
        'order_date',
        'delivery_days',
        'customer_id',
        'customer_name',
        'phone',
        'address',
        'deadline',
        'notes',
        'customer_policy',
        'discount_percent',
        'discount_amount',
        'vat_percent',
        'vat_amount',
        'total_amount',
        'status',
        'attachments',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'order_date' => 'datetime',
        'delivery_days' => 'float',
        'total_amount' => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasManyThrough(AcrylicOrderItem::class, OrderSupply::class, 'order_id', 'order_supply_id');
    }

    public function minLateItems()
    {
        return $this->hasManyThrough(MinLateOrderItem::class, OrderSupply::class, 'order_id', 'order_supply_id');
    }

    public function glassItems()
    {
        return $this->hasManyThrough(GlassOrderItem::class, OrderSupply::class, 'order_id', 'order_supply_id');
    }

    public function supplies()
    {
        return $this->hasMany(OrderSupply::class, 'order_id');
    }

    public function paymentDetails()
    {
        return $this->hasMany(PaymentDetail::class, 'order_id');
    }

    public function orderPayments()
    {
        return $this->hasMany(CustomerPayment::class, 'order_id')->orderBy('payment_date');
    }

    // Quan hệ với lệnh sản xuất (nhiều-nhiều)
    public function manufactureOrders()
    {
        return $this->belongsToMany(ManufactureOrder::class, 'manufacture_order_order', 'order_id', 'manufacture_order_id');
    }

    // Liên kết đơn cha (đơn gốc)
    public function parent()
    {
        return $this->belongsTo(Order::class, 'parent_id');
    }

    // Liên kết các đơn sửa con
    public function children()
    {
        return $this->hasMany(Order::class, 'parent_id');
    }

    public function getTotalSheetsAttribute()
    {
        if (isset($this->attributes['total_sheets'])) {
            return (float) $this->attributes['total_sheets'];
        }

        $sheets = 0;
        if ($this->relationLoaded('supplies')) {
            foreach ($this->supplies as $supply) {
                if (in_array($this->type, ['min_late', 'Flywood'])) {
                    if ($supply->relationLoaded('minLateItems')) {
                        $sheets += (float) $supply->minLateItems->sum('quantity');
                    }
                } elseif ($this->type === 'glass') {
                    if ($supply->relationLoaded('glassItems')) {
                        $sheets += (float) $supply->glassItems->sum('wing_quantity');
                    }
                } else {
                    if ($supply->relationLoaded('items')) {
                        $sheets += (float) $supply->items->sum('quantity');
                    }
                }
            }
            if ($sheets <= 0) {
                $sheets = (float) $this->supplies->sum('quantity');
            }
        } else {
            if (in_array($this->type, ['min_late', 'Flywood'])) {
                $sheets = (float) $this->minLateItems()->sum('min_late_order_items.quantity');
            } elseif ($this->type === 'glass') {
                $sheets = (float) $this->glassItems()->sum('glass_order_items.wing_quantity');
            } else {
                $sheets = (float) $this->items()->sum('acrylic_order_items.quantity');
            }
            if ($sheets <= 0) {
                $sheets = (float) $this->supplies()->sum('order_supplies.quantity');
            }
        }

        return $sheets;
    }

    public function getTotalMetersAttribute()
    {
        if (isset($this->attributes['total_meters'])) {
            return (float) $this->attributes['total_meters'];
        }

        $meters = 0;
        if ($this->relationLoaded('supplies')) {
            foreach ($this->supplies as $supply) {
                if (in_array($this->type, ['min_late', 'Flywood']) && $supply->relationLoaded('minLateItems')) {
                    foreach ($supply->minLateItems as $item) {
                        $itemMeters = (float) ($item->straight_paste_length ?? 0)
                                    + (float) ($item->beveled_length ?? 0)
                                    + (float) ($item->vat_moi_length ?? 0)
                                    + (float) ($item->ban_rong_40_59 ?? 0)
                                    + (float) ($item->ban_rong_17_39 ?? 0)
                                    + (float) ($item->ban_rong_25_35 ?? 0)
                                    + (float) ($item->beveled_handle ?? 0);

                        if ($itemMeters <= 0 && !empty($item->size)) {
                            $size = is_string($item->size) ? json_decode($item->size, true) : $item->size;
                            $h = (float) ($size['height'] ?? 0);
                            $w = (float) ($size['width'] ?? 0);
                            $qty = (float) ($item->quantity ?? 1);
                            if ($h > 0 && $w > 0) {
                                $itemMeters = (($h * 2 + $w * 2) / 1000) * $qty;
                            }
                        }
                        $meters += $itemMeters;
                    }
                } elseif ($this->type === 'glass' && $supply->relationLoaded('glassItems')) {
                    foreach ($supply->glassItems as $item) {
                        $h = (float) ($item->height ?? 0);
                        $w = (float) ($item->width ?? 0);
                        $qty = (float) ($item->wing_quantity ?? 1);
                        if ($h > 0 && $w > 0) {
                            $meters += (($h * 2 + $w * 2) / 1000) * $qty;
                        }
                    }
                } else {
                    if ($supply->relationLoaded('items')) {
                        foreach ($supply->items as $item) {
                            if ((float) ($item->molding_length ?? 0) > 0) {
                                $meters += (float) $item->molding_length;
                            } elseif ((float) ($item->height ?? 0) > 0 && (float) ($item->width ?? 0) > 0) {
                                $qty = (float) ($item->quantity ?? 1);
                                $meters += (($item->height * 2 + $item->width * 2) / 1000) * $qty;
                            }
                        }
                    }
                }
            }
        } else {
            if (in_array($this->type, ['min_late', 'Flywood'])) {
                foreach ($this->minLateItems as $item) {
                    $itemMeters = (float) ($item->straight_paste_length ?? 0)
                                + (float) ($item->beveled_length ?? 0)
                                + (float) ($item->vat_moi_length ?? 0)
                                + (float) ($item->ban_rong_40_59 ?? 0)
                                + (float) ($item->ban_rong_17_39 ?? 0)
                                + (float) ($item->ban_rong_25_35 ?? 0)
                                + (float) ($item->beveled_handle ?? 0);

                    if ($itemMeters <= 0 && !empty($item->size)) {
                        $size = is_string($item->size) ? json_decode($item->size, true) : $item->size;
                        $h = (float) ($size['height'] ?? 0);
                        $w = (float) ($size['width'] ?? 0);
                        $qty = (float) ($item->quantity ?? 1);
                        if ($h > 0 && $w > 0) {
                            $itemMeters = (($h * 2 + $w * 2) / 1000) * $qty;
                        }
                    }
                    $meters += $itemMeters;
                }
            } elseif ($this->type === 'glass') {
                foreach ($this->glassItems as $item) {
                    $h = (float) ($item->height ?? 0);
                    $w = (float) ($item->width ?? 0);
                    $qty = (float) ($item->wing_quantity ?? 1);
                    if ($h > 0 && $w > 0) {
                        $meters += (($h * 2 + $w * 2) / 1000) * $qty;
                    }
                }
            } else {
                foreach ($this->items as $item) {
                    if ((float) ($item->molding_length ?? 0) > 0) {
                        $meters += (float) $item->molding_length;
                    } elseif ((float) ($item->height ?? 0) > 0 && (float) ($item->width ?? 0) > 0) {
                        $qty = (float) ($item->quantity ?? 1);
                        $meters += (($item->height * 2 + $item->width * 2) / 1000) * $qty;
                    }
                }
            }
        }

        // Check payment details for meter units (m, md, mét)
        $paymentDetails = $this->relationLoaded('paymentDetails') ? $this->paymentDetails : $this->paymentDetails()->get();
        foreach ($paymentDetails as $detail) {
            $unit = mb_strtolower(trim($detail->unit ?? ''));
            if (in_array($unit, ['m', 'md', 'mét', 'm dài', 'met', 'm.dài'])) {
                $meters += (float) ($detail->quantity ?? 0);
            }
        }

        return round($meters, 2);
    }
}

