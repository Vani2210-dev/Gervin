<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected static function booted()
    {
        static::saving(function ($order) {
            if ($order->isNhapKho() && !empty($order->order_code)) {
                // Đơn nhập kho: loại bỏ hậu tố .D nếu có và đảm bảo có hậu tố .NK
                $cleaned = static::stripDraftOrderCode($order->order_code);
                if (!preg_match('/[._-]?NK$/i', trim($cleaned))) {
                    $order->order_code = static::formatNhapKhoOrderCode($cleaned);
                } else {
                    $order->order_code = $cleaned;
                }
            } elseif ($order->status === 'draft' && !empty($order->order_code)) {
                // Đơn nháp: tự động thêm hậu tố .D phía sau
                if (!preg_match('/[._-]?D$/i', trim($order->order_code))) {
                    $order->order_code = static::formatDraftOrderCode($order->order_code);
                }
            } elseif ($order->status !== 'draft' && !empty($order->order_code)) {
                // Khi đơn không còn ở trạng thái nháp: tự động gỡ bỏ hậu tố .D
                if (preg_match('/[._-]?D$/i', trim($order->order_code))) {
                    $order->order_code = static::stripDraftOrderCode($order->order_code);
                }
            }
        });

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
        if ($this->isNhapKho()) {
            return;
        }

        if ($this->customer && !in_array($this->status, ['draft', 'pending', 'cancelled']) && $this->board_return_status !== 'returned') {
            $amount = round($this->total_amount, -3);
            $this->customer->increment('debt', $amount);
        }

        if ($this->customer) {
            \App\Services\CustomerDebtService::syncCustomerDailyDebts($this->customer);
        }
    }

    public function adjustCustomerDebtOnUpdate()
    {
        if ($this->isNhapKho()) {
            return;
        }

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
                    \App\Services\CustomerDebtService::syncCustomerDailyDebts($newCustomer);
                }
            }
            if ($wasValid && $oldCustomerId && isset($oldCustomer)) {
                \App\Services\CustomerDebtService::syncCustomerDailyDebts($oldCustomer);
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

            \App\Services\CustomerDebtService::syncCustomerDailyDebts($this->customer);
        }
    }

    public function adjustCustomerDebtOnDelete()
    {
        if ($this->isNhapKho()) {
            return;
        }

        if ($this->customer && !in_array($this->status, ['draft', 'pending', 'cancelled']) && $this->board_return_status !== 'returned') {
            $amount = round($this->total_amount, -3);
            $this->customer->decrement('debt', $amount);
        }

        if ($this->customer) {
            \App\Services\CustomerDebtService::syncCustomerDailyDebts($this->customer);
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
        'additional_discount_percent',
        'additional_discount_amount',
        'total_amount',
        'status',
        'attachments',
        'edit_reason',
        'accountant_approved_by',
        'accountant_approved_at',
        'technical_approved_by',
        'technical_approved_at',
        'rejected_by',
        'rejected_at',
        'rejected_step',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'order_date' => 'datetime',
        'delivery_days' => 'float',
        'total_amount' => 'float',
        'accountant_approved_at' => 'datetime',
        'technical_approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function accountantApprover()
    {
        return $this->belongsTo(User::class, 'accountant_approved_by');
    }

    public function technicalApprover()
    {
        return $this->belongsTo(User::class, 'technical_approved_by');
    }

    public function rejecter()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function receipts()
    {
        return $this->hasMany(\App\Models\InventoryReceipt::class, 'order_id')->orderBy('date', 'desc');
    }

    public function issues()
    {
        return $this->hasMany(\App\Models\InventoryIssue::class, 'order_id')->orderBy('date', 'desc');
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
                        $sheets += (float) $supply->items->filter(fn($i) => ($i->product_name ?? '') !== 'Công giả dày')->sum('quantity');
                    }
                }
            }
            if ($sheets <= 0) {
                if ($this->type === 'acrylic') {
                    $sheets = (float) $this->items()->where('acrylic_order_items.product_name', '!=', 'Công giả dày')->sum('acrylic_order_items.quantity');
                } else {
                    $sheets = (float) $this->supplies->sum('quantity');
                }
            }
        } else {
            if (in_array($this->type, ['min_late', 'Flywood'])) {
                $sheets = (float) $this->minLateItems()->sum('min_late_order_items.quantity');
            } elseif ($this->type === 'glass') {
                $sheets = (float) $this->glassItems()->sum('glass_order_items.wing_quantity');
            } else {
                $sheets = (float) $this->items()->where('acrylic_order_items.product_name', '!=', 'Công giả dày')->sum('acrylic_order_items.quantity');
            }
            if ($sheets <= 0 && $this->type !== 'acrylic') {
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
                            if (($item->product_name ?? '') === 'Công giả dày') continue;
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
                    if (($item->product_name ?? '') === 'Công giả dày') continue;
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

    public function isNhapKho(): bool
    {
        $code = strtoupper(trim($this->customer?->customer_code ?? ''));
        if (in_array($code, ['NK', 'NHAPKHO', 'LSX', 'SX'], true)) {
            return true;
        }

        $cName = mb_strtolower(trim($this->customer?->name ?? ''));
        if ($cName !== '' && (
            str_contains($cName, 'nhập kho') || str_contains($cName, 'lệnh sản xuất') || 
            str_contains($cName, 'nhap kho') || str_contains($cName, 'lenh san xuat') ||
            str_contains($cName, 'sản xuất nội bộ')
        )) {
            return true;
        }

        $orderCustomerName = mb_strtolower(trim($this->customer_name ?? ''));
        if ($orderCustomerName !== '' && (
            str_contains($orderCustomerName, 'nhập kho') || str_contains($orderCustomerName, 'lệnh sản xuất') || 
            str_contains($orderCustomerName, 'nhap kho') || str_contains($orderCustomerName, 'lenh san xuat') ||
            str_contains($orderCustomerName, 'sản xuất nội bộ')
        )) {
            return true;
        }

        if (!empty($this->order_code) && preg_match('/[._-]?(NK|LSX)$/i', trim($this->order_code))) {
            return true;
        }

        return false;
    }

    /**
     * Kiểm tra đơn hàng có phải là Lệnh sản xuất (LSX) hay không.
     * Đơn hàng thông thường (bán cho khách) và đơn nhập kho (.NK) KHÔNG phải là LSX.
     */
    public function isLsx(): bool
    {
        // 1. Loại đơn min_late là lệnh sản xuất ép tấm
        if ($this->type === 'min_late') {
            return true;
        }

        // 2. Mã khách hàng là LSX hoặc SX
        $code = strtoupper(trim($this->customer?->customer_code ?? ''));
        if (in_array($code, ['LSX', 'SX'], true)) {
            return true;
        }

        // 3. Tên khách hàng chứa cụm "lệnh sản xuất" hoặc "sản xuất nội bộ"
        $cName = mb_strtolower(trim($this->customer?->name ?? ''));
        if ($cName !== '' && (
            str_contains($cName, 'lệnh sản xuất') || 
            str_contains($cName, 'lenh san xuat') || 
            str_contains($cName, 'sản xuất nội bộ')
        )) {
            return true;
        }

        $orderCustomerName = mb_strtolower(trim($this->customer_name ?? ''));
        if ($orderCustomerName !== '' && (
            str_contains($orderCustomerName, 'lệnh sản xuất') || 
            str_contains($orderCustomerName, 'lenh san xuat') || 
            str_contains($orderCustomerName, 'sản xuất nội bộ')
        )) {
            return true;
        }

        // 4. Mã đơn hàng có hậu tố .LSX hoặc LSX (không tính .NK)
        if (!empty($this->order_code) && preg_match('/[._-]?LSX$/i', trim($this->order_code))) {
            return true;
        }

        return false;
    }

    /**
     * Tự động thêm hậu tố .NK vào mã đơn nhập kho nếu chưa có (tránh trùng với đơn thường).
     */
    public static function formatNhapKhoOrderCode(?string $code): string
    {
        $code = trim((string)$code);
        if ($code === '') {
            return '';
        }
        if (preg_match('/[._-]?NK$/i', $code)) {
            return $code;
        }
        return $code . (str_ends_with($code, '.') ? 'NK' : '.NK');
    }

    /**
     * Tự động thêm hậu tố .D vào mã đơn nháp nếu chưa có.
     */
    public static function formatDraftOrderCode(?string $code): string
    {
        $code = trim((string)$code);
        if ($code === '') {
            return '';
        }
        if (preg_match('/[._-]?D$/i', $code)) {
            return $code;
        }
        return $code . (str_ends_with($code, '.') ? 'D' : '.D');
    }

    /**
     * Tự động gỡ bỏ hậu tố .D khỏi mã đơn khi thoát trạng thái nháp.
     */
    public static function stripDraftOrderCode(?string $code): string
    {
        $code = trim((string)$code);
        if ($code === '') {
            return '';
        }
        return preg_replace('/[._-]?D$/i', '', $code);
    }

    /**
     * Tên hiển thị đầy đủ của khách hàng: Tên khách hàng (Mã KH) (Tên công trình).
     * Khớp 100% giữa xem trước đơn hàng (_preview_modal) và xuất file Excel (order-export.js).
     */
    public function getFullCustomerDisplay(): string
    {
        if ($this->isNhapKho()) {
            return 'Nhập kho nội bộ Gervin';
        }

        $customer = $this->customer;
        $projectName = trim($this->customer_name ?? '');

        if ($customer && $projectName !== '') {
            $cName = trim($customer->name ?? '');
            $codeStr = $customer->customer_code ? ' (' . $customer->customer_code . ')' : '';
            if ($cName !== '' && mb_strtolower($cName) === mb_strtolower($projectName)) {
                return $cName . $codeStr;
            }
            return $cName . $codeStr . ' (' . $projectName . ')';
        }

        if ($customer) {
            return $customer->name . ($customer->customer_code ? ' (' . $customer->customer_code . ')' : '');
        }

        return $projectName ?: '—';
    }

    public function getFullCustomerDisplayAttribute(): string
    {
        return $this->getFullCustomerDisplay();
    }

    /**
     * Tên khách hàng rút gọn (chỉ Tên KH + Mã KH nếu có, không kèm Tên công trình).
     * Dùng cho cột Sản phẩm (Product Name) trong file xuất Nesting và Nesting Phào.
     */
    public function getCustomerShortDisplay(): string
    {
        if ($this->isNhapKho()) {
            return 'Nhập kho nội bộ Gervin';
        }

        $customer = $this->customer;
        if ($customer) {
            $cName = trim($customer->name ?? '');
            $codeStr = $customer->customer_code ? ' (' . $customer->customer_code . ')' : '';
            return $cName . $codeStr;
        }

        return trim($this->customer_name ?? '') ?: '—';
    }

    public function getCustomerShortDisplayAttribute(): string
    {
        return $this->getCustomerShortDisplay();
    }

    /**
     * Kiểm tra đơn hàng có ít nhất một tấm (vật tư) có mã GV hay không.
     * Dùng để xét điều kiện chiết khấu tháng.
     */
    public function hasGvSupplies(): bool
    {
        $supplies = $this->relationLoaded('supplies') ? $this->supplies : $this->supplies()->get();

        if ($supplies->isEmpty()) {
            return false;
        }

        foreach ($supplies as $supply) {
            $code = trim($supply->order_supply_code ?? '');
            $name = trim($supply->supply_name ?? '');

            // Nếu mã tấm có chứa 'GV' (ví dụ: GV01, GV28.TP, GV-05, ...)
            if ($code !== '' && stripos($code, 'GV') !== false) {
                return true;
            }

            // Hoặc trường hợp mã để trống nhưng tên vật tư ghi rõ mã GV (ví dụ: Tấm GV01...)
            if ($code === '' && $name !== '' && preg_match('/(?:^|[^a-zA-Z0-9])GV(?:\d+|[^a-zA-Z0-9]|$)/i', $name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Lấy danh sách các mã tấm GV của đơn hàng
     */
    public function getGvSupplyCodes(): array
    {
        $supplies = $this->relationLoaded('supplies') ? $this->supplies : $this->supplies()->get();
        $gvCodes = [];

        foreach ($supplies as $supply) {
            $code = trim($supply->order_supply_code ?? '');
            $name = trim($supply->supply_name ?? '');

            if ($code !== '' && stripos($code, 'GV') !== false) {
                $gvCodes[] = $code;
            } elseif ($code === '' && $name !== '' && preg_match('/(?:^|[^a-zA-Z0-9])(GV\d+[^\s,]*)/i', $name, $matches)) {
                $gvCodes[] = $matches[1];
            }
        }

        return array_values(array_unique($gvCodes));
    }

    public function scopeWhereNhapKho($query)
    {
        return $query->where(function ($q) {
            $q->whereHas('customer', function ($cq) {
                $cq->whereIn('customer_code', ['NK', 'NHAPKHO', 'LSX', 'SX'])
                   ->orWhereRaw('LOWER(TRIM(name)) IN (?, ?, ?, ?)', ['nhập kho', 'nhap kho', 'lệnh sản xuất', 'lenh san xuat'])
                   ->orWhere('name', 'like', '%lệnh sản xuất%')
                   ->orWhere('name', 'like', '%nhập kho%');
            })->orWhereRaw('LOWER(TRIM(customer_name)) IN (?, ?, ?, ?)', ['nhập kho', 'nhap kho', 'lệnh sản xuất', 'lenh san xuat'])
              ->orWhere('customer_name', 'like', '%lệnh sản xuất%')
              ->orWhere('customer_name', 'like', '%nhập kho%')
              ->orWhere('order_code', 'like', '%.NK')
              ->orWhere('order_code', 'like', '%NK')
              ->orWhere('order_code', 'like', '%.LSX')
              ->orWhere('order_code', 'like', '%LSX');
        });
    }

    public function scopeWhereNotNhapKho($query)
    {
        return $query->where(function ($q) {
            $q->whereDoesntHave('customer', function ($cq) {
                $cq->whereIn('customer_code', ['NK', 'NHAPKHO', 'LSX', 'SX'])
                   ->orWhereRaw('LOWER(TRIM(name)) IN (?, ?, ?, ?)', ['nhập kho', 'nhap kho', 'lệnh sản xuất', 'lenh san xuat'])
                   ->orWhere('name', 'like', '%lệnh sản xuất%')
                   ->orWhere('name', 'like', '%nhập kho%');
            })->where(function ($sub) {
                $sub->whereNull('customer_name')
                    ->orWhere(function ($s) {
                        $s->whereRaw('LOWER(TRIM(customer_name)) NOT IN (?, ?, ?, ?)', ['nhập kho', 'nhap kho', 'lệnh sản xuất', 'lenh san xuat'])
                          ->where('customer_name', 'not like', '%lệnh sản xuất%')
                          ->where('customer_name', 'not like', '%nhập kho%');
                    });
            })->where(function ($subCode) {
                $subCode->whereNull('order_code')
                    ->orWhere(function ($cQ) {
                        $cQ->where('order_code', 'not like', '%.NK')
                           ->where('order_code', 'not like', '%NK')
                           ->where('order_code', 'not like', '%.LSX')
                           ->where('order_code', 'not like', '%LSX');
                    });
            });
        });
    }

    /**
     * Kiểm tra đơn hàng có đang bị khóa không cho sửa không.
     * Quy tắc:
     * - Admin luôn có quyền sửa.
     * - Đơn bị hủy (cancelled) hoặc đang sản xuất / hoàn thành: khóa.
     * - Đơn nháp (draft): luôn được sửa.
     * - Khi Kế toán/Kỹ thuật bấm "Yêu cầu sửa" (có edit_reason): MỞ KHÓA cho Sale sửa.
     * - Khi Kế toán đã duyệt (hoặc Kỹ thuật đã duyệt / đã chuyển sản xuất): KHÓA SỬA.
     */
    public function isLockedForEdit(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return true;
        }

        // Admin luôn có quyền can thiệp
        if ($user->hasRole('Admin')) {
            return false;
        }

        if ($this->status === 'cancelled') {
            return true;
        }

        if (in_array($this->status, ['in_production', 'completed'])) {
            return true;
        }

        // Đơn nháp luôn sửa được
        if ($this->status === 'draft') {
            return false;
        }

        // Nếu Kế toán đã yêu cầu sửa -> MỞ KHÓA cho Sale sửa
        if (!empty($this->edit_reason) || !empty($this->rejected_step)) {
            return false;
        }

        // Nếu Kế toán đã duyệt hoặc Kỹ thuật đã duyệt hoặc đã chuyển sản xuất -> KHÓA SỬA
        if ($this->accountant_approved_by || $this->technical_approved_by || $this->status === 'transferred') {
            return true;
        }

        return false;
    }

    /**
     * Kiểm tra đơn hàng có đang trong trạng thái yêu cầu sửa lại không.
     */
        public function isRequestedEdit(): bool
    {
        return !empty($this->edit_reason) || !empty($this->rejected_step);
    }

    /**
     * Tự động kiểm tra và chuyển trạng thái đơn hàng sang "in_production" (Đang sản xuất)
     * nếu đơn hàng đang ở trạng thái transferred/pending và đã có ít nhất 1 tấm được gia công/quét mã.
     */
    public function checkAndMarkInProduction(): bool
    {
        if (!in_array($this->status, ['transferred', 'pending'])) {
            return false;
        }

        $hasProductionLogs = false;
        $this->loadMissing([
            'supplies.items.codes',
            'supplies.minLateItems.codes',
            'supplies.glassItems.codes',
        ]);

        foreach ($this->supplies as $supply) {
            foreach ([$supply->items, $supply->minLateItems, $supply->glassItems] as $items) {
                if (!$items) continue;
                foreach ($items as $item) {
                    foreach ($item->codes as $code) {
                        $logs = $code->status ?? [];
                        if (is_string($logs)) {
                            $logs = json_decode($logs, true) ?? [];
                        }
                        if (!empty($logs)) {
                            $hasProductionLogs = true;
                            break 4;
                        }
                    }
                }
            }
        }

        if ($hasProductionLogs) {
            $this->update(['status' => 'in_production']);
            try {
                \App\Services\NotificationService::notifyOrderStatusChanged($this, 'in_production');
            } catch (\Throwable $e) {}
            return true;
        }

        return false;
    }

    /**
     * Tự động kiểm tra và chuyển trạng thái đơn hàng sang "completed" (Hoàn thành)
     * Điều kiện: Tất cả các tấm của đơn hàng đều đã được đưa vào gói (is_packaged = true)
     * và gói hàng chứa tấm đó đã được khách nhận đơn (delivered_at không rỗng).
     */
    public function checkAndMarkCompleted(): bool
    {
        if ($this->status === 'completed' || in_array($this->status, ['draft', 'cancelled'])) {
            return false;
        }

        $this->loadMissing([
            'supplies.items.codes',
            'supplies.minLateItems.codes',
            'supplies.glassItems.codes',
        ]);

        $allPlateCodes = [];
        foreach ($this->supplies as $supply) {
            foreach ([$supply->items, $supply->minLateItems, $supply->glassItems] as $items) {
                if (!$items) continue;
                foreach ($items as $item) {
                    foreach ($item->codes as $code) {
                        if (!empty($code->product_id)) {
                            $allPlateCodes[] = (string) $code->product_id;
                        }
                    }
                }
            }
        }

        // Nếu đơn hàng chưa có mã tấm nào, không tự động hoàn thành
        if (empty($allPlateCodes)) {
            return false;
        }

        // Lấy danh sách các mã tấm đã được đưa vào gói (is_packaged = true)
        // VÀ kiện hàng chứa tấm đó đã được khách nhận đơn (delivered_at IS NOT NULL)
        $deliveredPlateCodes = \App\Models\PackingPackageItem::whereIn('item_code_id', $allPlateCodes)
            ->where('is_packaged', true)
            ->whereHas('package', function ($q) {
                $q->whereNotNull('delivered_at');
            })
            ->pluck('item_code_id')
            ->map(fn($v) => (string) $v)
            ->unique()
            ->toArray();

        // Kiểm tra xem tất cả các tấm đã được giao hay chưa (100% tấm đều có trong $deliveredPlateCodes)
        $missingPlateCodes = array_diff($allPlateCodes, $deliveredPlateCodes);

        if (empty($missingPlateCodes)) {
            $this->update(['status' => 'completed']);
            try {
                \App\Services\NotificationService::notifyOrderStatusChanged($this, 'completed');
            } catch (\Throwable $e) {}
            return true;
        }

        return false;
    }

    /**
     * Cập nhật trạng thái đơn hàng sang "in_production" trực tiếp từ item đang thao tác.
     */
    public static function markInProductionFromItem($item): void
    {
        if (!$item) return;
        $order = $item->orderSupply?->order ?? $item->order ?? null;
        if ($order && in_array($order->status, ['transferred', 'pending'])) {
            $order->update(['status' => 'in_production']);
            try {
                \App\Services\NotificationService::notifyOrderStatusChanged($order, 'in_production');
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Thống kê tiến độ sản xuất chi tiết của đơn hàng:
     * - Số tấm chưa sản xuất
     * - Số tấm đang sản xuất
     * - Số kiện / tấm đã đóng gói
     * - Số tấm khách đã nhận
     */
    public function getProductionProgressStats(): array
    {
        $this->loadMissing([
            'supplies.items.codes',
            'supplies.minLateItems.codes',
            'supplies.glassItems.codes',
        ]);

        $allPlateCodes = [];
        $totalPlatesExpected = 0;

        foreach ($this->supplies as $supply) {
            if ($this->type === 'glass') {
                $items = $supply->glassItems;
                $totalPlatesExpected += (int) ($items->sum('wing_quantity') ?: $items->sum('quantity'));
            } elseif (in_array($this->type, ['min_late', 'Flywood'])) {
                $items = $supply->minLateItems;
                $totalPlatesExpected += (int) $items->sum('quantity');
            } else {
                $items = $supply->items;
                $totalPlatesExpected += (int) $items->sum('quantity');
            }

            foreach ($items as $item) {
                foreach ($item->codes as $code) {
                    if (!empty($code->product_id)) {
                        $allPlateCodes[(string) $code->product_id] = $code;
                    }
                }
            }
        }

        $totalPlates = count($allPlateCodes) > 0 ? count($allPlateCodes) : $totalPlatesExpected;

        // Tra cứu thông tin đóng gói & giao hàng từ PackingPackageItem
        $packageItems = collect();
        if (!empty($allPlateCodes)) {
            $packageItems = \App\Models\PackingPackageItem::with('package')
                ->whereIn('item_code_id', array_keys($allPlateCodes))
                ->get();
        }

        $packedPlateCodes = [];
        $deliveredPlateCodes = [];
        $relatedPackages = collect();

        foreach ($packageItems as $pkgItem) {
            $codeId = (string) $pkgItem->item_code_id;
            $pkg = $pkgItem->package;

            if ($pkg) {
                $relatedPackages->put($pkg->id, $pkg);
            }

            if ($pkgItem->is_packaged) {
                $packedPlateCodes[$codeId] = true;
                if ($pkg && !empty($pkg->delivered_at)) {
                    $deliveredPlateCodes[$codeId] = true;
                }
            }
        }

        $countDelivered = count($deliveredPlateCodes);
        $countPackedTotal = count($packedPlateCodes);
        $completedPackagesCount = $relatedPackages->where('status', 'completed')->count();
        $totalPackagesCount = $relatedPackages->count();

        $countInProduction = 0;
        $countNotStarted = 0;
        $countCompletedQc = 0;

        foreach ($allPlateCodes as $productId => $code) {
            $statusLogs = $code->status ?? [];
            if (is_string($statusLogs)) {
                $statusLogs = json_decode($statusLogs, true) ?? [];
            }

            $hasStarted = false;
            $hasCompletedQc = false;
            $hasPackedLog = false;
            $hasDeliveredLog = false;

            foreach ($statusLogs as $log) {
                $act = mb_strtolower($log['action'] ?? '');
                if ($act === 'hoàn thành qc' || $act === 'hoàn thành làm đẹp') {
                    $hasCompletedQc = true;
                }
                if (str_contains($act, 'đóng gói')) {
                    $hasPackedLog = true;
                }
                if (str_contains($act, 'giao hàng') || str_contains($act, 'đã nhận') || str_contains($act, 'hoàn thành giao')) {
                    $hasDeliveredLog = true;
                }
                if (
                    str_contains($act, 'ép') ||
                    str_contains($act, 'cnc') ||
                    str_contains($act, 'dán') ||
                    str_contains($act, 'cắt') ||
                    str_contains($act, 'làm đẹp') ||
                    str_contains($act, 'lỗi')
                ) {
                    $hasStarted = true;
                }
            }

            // Đồng bộ nếu trong log quét có đóng gói hoặc giao hàng
            if ($hasDeliveredLog && !isset($deliveredPlateCodes[$productId])) {
                $deliveredPlateCodes[$productId] = true;
                $countDelivered++;
            }
            if (($hasPackedLog || $hasDeliveredLog) && !isset($packedPlateCodes[$productId])) {
                $packedPlateCodes[$productId] = true;
                $countPackedTotal++;
            }

            // Phân loại trạng thái tấm:
            if (isset($packedPlateCodes[$productId])) {
                continue;
            }

            if ($hasStarted) {
                $countInProduction++;
                if ($hasCompletedQc) {
                    $countCompletedQc++;
                }
            } else {
                $countNotStarted++;
            }
        }

        // Trường hợp đơn chưa tạo barcode/codes
        if (empty($allPlateCodes)) {
            $countNotStarted = $totalPlates;
        }

        $countPackedWaitingDelivery = max(0, $countPackedTotal - $countDelivered);

        return [
            'total_plates'                  => $totalPlates,
            'not_started_plates'            => $countNotStarted,
            'in_production_plates'          => $countInProduction,
            'completed_qc_plates'           => $countCompletedQc,
            'packed_plates'                 => $countPackedTotal,
            'packed_waiting_delivery_plates'=> $countPackedWaitingDelivery,
            'delivered_plates'              => $countDelivered,
            'total_packages'                => $totalPackagesCount,
            'completed_packages'            => $completedPackagesCount,
            'package_names'                 => $relatedPackages->pluck('name')->filter()->values()->all(),
        ];
    }

    /**
     * Lấy thông tin các kiện đóng gói của đơn hàng từ mục processes/packing
     */
    public function getPackingInfo(): array
    {
        $allPlateCodeIds = [];
        if ($this->supplies) {
            foreach ($this->supplies as $supply) {
                $items = $this->type === 'min_late'
                    ? $supply->minLateItems
                    : ($this->type === 'glass' ? $supply->glassItems : $supply->items);
                if ($items) {
                    foreach ($items as $item) {
                        if ($item->codes) {
                            foreach ($item->codes as $code) {
                                if (!empty($code->product_id)) {
                                    $allPlateCodeIds[(string) $code->product_id] = true;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (empty($allPlateCodeIds)) {
            return [
                'total_packages' => 0,
                'completed_packages' => 0,
                'package_names' => [],
                'text' => 'Chưa đóng gói',
            ];
        }

        $packageItems = \App\Models\PackingPackageItem::with('package')
            ->whereIn('item_code_id', array_keys($allPlateCodeIds))
            ->where('is_packaged', true)
            ->get();

        $relatedPackages = collect();
        foreach ($packageItems as $pkgItem) {
            if ($pkgItem->package) {
                $relatedPackages->put($pkgItem->package->id, $pkgItem->package);
            }
        }

        $totalPackages = $relatedPackages->count();
        $completedPackages = $relatedPackages->where('status', 'completed')->count();
        $names = $relatedPackages->pluck('name')->filter()->values();

        $text = 'Chưa đóng gói';
        if ($totalPackages > 0) {
            $text = $totalPackages . ' kiện';
            if ($names->isNotEmpty()) {
                $text .= ' (' . $names->implode(', ') . ')';
            }
        }

        return [
            'total_packages' => $totalPackages,
            'completed_packages' => $completedPackages,
            'package_names' => $names->all(),
            'text' => $text,
        ];
    }

    /**
     * Lấy thông tin công nợ lũy kế theo thứ tự thời gian cho đơn hàng này (Phương án 2).
     */
    public function getCustomerDebtInfo(?float $customTotal = null, ?string $customOrderDate = null): ?array
    {
        if (!$this->customer) {
            return null;
        }
        return $this->customer->getDebtInfoForOrder($this, $customTotal, $customOrderDate);
    }
}


