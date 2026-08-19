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
}

