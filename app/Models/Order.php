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
        if ($this->isNhapKho()) {
            return;
        }

        if ($this->customer && !in_array($this->status, ['draft', 'pending', 'cancelled']) && $this->board_return_status !== 'returned') {
            $amount = round($this->total_amount, -3);
            $this->customer->increment('debt', $amount);
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
        if ($this->isNhapKho()) {
            return;
        }

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

    public function isNhapKho(): bool
    {
        if ($this->customer && (
            in_array(strtoupper($this->customer->customer_code ?? ''), ['NK', 'NHAPKHO'], true) ||
            in_array(mb_strtolower(trim($this->customer->name ?? '')), ['nhập kho', 'nhap kho'], true)
        )) {
            return true;
        }
        $cName = mb_strtolower(trim($this->customer_name ?? ''));
        return in_array($cName, ['nhập kho', 'nhap kho'], true);
    }

    public function scopeWhereNhapKho($query)
    {
        return $query->where(function ($q) {
            $q->whereHas('customer', function ($cq) {
                $cq->whereIn('customer_code', ['NK', 'NHAPKHO'])
                   ->orWhereRaw('LOWER(TRIM(name)) IN (?, ?)', ['nhập kho', 'nhap kho']);
            })->orWhereRaw('LOWER(TRIM(customer_name)) IN (?, ?)', ['nhập kho', 'nhap kho']);
        });
    }

    public function scopeWhereNotNhapKho($query)
    {
        return $query->where(function ($q) {
            $q->whereDoesntHave('customer', function ($cq) {
                $cq->whereIn('customer_code', ['NK', 'NHAPKHO'])
                   ->orWhereRaw('LOWER(TRIM(name)) IN (?, ?)', ['nhập kho', 'nhap kho']);
            })->where(function ($sub) {
                $sub->whereNull('customer_name')
                    ->orWhereRaw('LOWER(TRIM(customer_name)) NOT IN (?, ?)', ['nhập kho', 'nhap kho']);
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
     * Lấy thông tin công nợ lũy kế theo thứ tự thời gian tạo đơn (Phương án 2)
     * - Đơn tạo trước tính nợ tồn trước đó.
     * - Đơn tạo sau tính nợ cũ = Nợ tồn + Đơn trước.
     * - Tiền khách thanh toán trong ngày cấn trừ theo thứ tự đơn nào trước trả trước, không bị trừ đúp.
     */
    public function getCustomerDebtInfo(): ?array
    {
        if ($this->isNhapKho() || !$this->customer_id) {
            return null;
        }

        $customer = $this->customer ?? \App\Models\Customer::find($this->customer_id);
        if (!$customer) {
            return null;
        }

        $tz = 'Asia/Ho_Chi_Minh';
        $orderDateTime = $this->order_date ?? $this->created_at ?? \Carbon\Carbon::now($tz);
        $orderDate = \Carbon\Carbon::parse($orderDateTime)->timezone($tz);
        $dayStr = $orderDate->format('Y-m-d');

        // 1. Tính công nợ tồn đọng của khách tại thời điểm đầu ngày (trước 00:00 ngày $dayStr)
        // Debt_start_of_day = Tổng nợ hiện tại - Các đơn hợp lệ từ đầu ngày $dayStr đến nay + Các khoản thanh toán từ đầu ngày $dayStr đến nay
        $currentTotalDebt = (float) ($customer->total_debt ?? 0);

        $ordersSinceStartOfDay = (float) $customer->orders()
            ->whereNotNhapKho()
            ->whereNotIn('status', ['draft', 'pending', 'cancelled'])
            ->where('board_return_status', '!=', 'returned')
            ->where(function ($q) use ($dayStr, $tz) {
                $q->whereDate('order_date', '>=', $dayStr)
                  ->orWhere(function ($sq) use ($dayStr) {
                      $sq->whereNull('order_date')->whereDate('created_at', '>=', $dayStr);
                  });
            })
            ->get()
            ->sum(fn($o) => round($o->total_amount ?? 0, -3));

        $paymentsSinceStartOfDay = (float) $customer->customerPayments()
            ->whereDate('payment_date', '>=', $dayStr)
            ->sum('amount');

        $debtStartOfDay = max(0, $currentTotalDebt - $ordersSinceStartOfDay + $paymentsSinceStartOfDay);

        // 2. Lấy các khoản thanh toán trong ngày $dayStr của khách hàng
        $dayPayments = $customer->customerPayments()
            ->whereDate('payment_date', $dayStr)
            ->orderBy('id', 'asc')
            ->get();

        $directPaymentsByOrder = $dayPayments->whereNotNull('order_id')->groupBy('order_id');
        $generalPaymentsTotal = (float) $dayPayments->whereNull('order_id')->sum('amount');

        // 3. Lấy tất cả các đơn hàng trong ngày $dayStr (sắp xếp theo thứ tự thời gian)
        $dayOrders = $customer->orders()
            ->whereNotNhapKho()
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->where('board_return_status', '!=', 'returned')
            ->where(function ($q) use ($dayStr) {
                $q->whereDate('order_date', $dayStr)
                  ->orWhere(function ($sq) use ($dayStr) {
                      $sq->whereNull('order_date')->whereDate('created_at', $dayStr);
                  });
            })
            ->get();

        // Đảm bảo đơn hàng hiện tại ($this) nằm trong danh sách xét duyệt
        $foundCurrent = false;
        if ($this->id) {
            foreach ($dayOrders as $idx => $dOrd) {
                if ($dOrd->id == $this->id) {
                    $dayOrders[$idx] = $this;
                    $foundCurrent = true;
                    break;
                }
            }
        }
        if (!$foundCurrent) {
            $dayOrders->push($this);
        }

        // Sắp xếp các đơn trong ngày theo thứ tự thời gian
        $sortedDayOrders = $dayOrders->sort(function ($a, $b) use ($tz) {
            $timeA = $a->order_date ? \Carbon\Carbon::parse($a->order_date)->timezone($tz)->timestamp : ($a->created_at ? \Carbon\Carbon::parse($a->created_at)->timezone($tz)->timestamp : PHP_INT_MAX);
            $timeB = $b->order_date ? \Carbon\Carbon::parse($b->order_date)->timezone($tz)->timestamp : ($b->created_at ? \Carbon\Carbon::parse($b->created_at)->timezone($tz)->timestamp : PHP_INT_MAX);
            if ($timeA === $timeB) {
                return ($a->id ?? PHP_INT_MAX) <=> ($b->id ?? PHP_INT_MAX);
            }
            return $timeA <=> $timeB;
        })->values();

        // 4. Lũy kế công nợ và cấn trừ thanh toán tuần tự (FIFO)
        $runningDebt = $debtStartOfDay;
        $remainingGeneralPayment = $generalPaymentsTotal;
        $orderDebtInfo = null;

        $dayOrdersCount = $sortedDayOrders->count();
        foreach ($sortedDayOrders as $idx => $ord) {
            $isTargetOrder = ($this->id && $ord->id == $this->id) || (!$this->id && $ord === $this);
            $isLastOrderOfDay = ($idx === $dayOrdersCount - 1);

            $ordTotal = round($ord->total_amount ?? 0, -3);
            $directPaid = (isset($ord->id) && isset($directPaymentsByOrder[$ord->id]))
                ? (float) $directPaymentsByOrder[$ord->id]->sum('amount')
                : 0;

            // Phân bổ thanh toán chung cho đơn này
            $allocatedGeneral = 0;
            if ($remainingGeneralPayment > 0) {
                if ($isLastOrderOfDay) {
                    // Đơn cuối cùng trong ngày nhận phần thanh toán còn lại
                    $allocatedGeneral = $remainingGeneralPayment;
                } else {
                    // Đơn trước nhận tối đa bằng số tiền của đơn đó
                    $need = max(0, $ordTotal - $directPaid);
                    $allocatedGeneral = min($remainingGeneralPayment, $need);
                }
                $remainingGeneralPayment -= $allocatedGeneral;
            }

            $orderPaid = $directPaid + $allocatedGeneral;
            $oldDebtForThis = $runningDebt;
            $endDebtForThis = max(0, $oldDebtForThis + $ordTotal - $orderPaid);

            if ($isTargetOrder) {
                if ($oldDebtForThis > 0 || $orderPaid > 0 || $endDebtForThis > 0) {
                    $orderDebtInfo = [
                        'old_debt' => $oldDebtForThis,
                        'this_order_paid' => $orderPaid,
                        'total_combined_debt' => $endDebtForThis,
                    ];
                }
                break;
            }

            // Nợ sau đơn này sẽ trở thành Nợ cũ (Công nợ) của đơn tiếp theo trong ngày
            $runningDebt = $endDebtForThis;
        }

        return $orderDebtInfo;
    }
}


