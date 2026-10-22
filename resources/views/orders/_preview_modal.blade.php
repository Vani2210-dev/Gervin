@php
    $typeLabels = [
        'acrylic' => 'Acrylic',
        'min_late' => 'Min Late',
        'glass' => 'Glass',
        'service' => 'Dịch vụ & Vật tư',
        'Flywood' => 'Flywood',
    ];
    $typeLabel = $typeLabels[$acrylicOrder->type] ?? ucfirst($acrylicOrder->type);

    $statusLabels = [
        'draft' => 'Nháp',
        'pending' => 'Chờ xử lý',
        'transferred' => 'Chuyển sản xuất',
        'in_production' => 'Đang sản xuất',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];
    $statusText = $statusLabels[$acrylicOrder->status] ?? $acrylicOrder->status;

    $orderDateFormatted = $acrylicOrder->order_date ? \Carbon\Carbon::parse($acrylicOrder->order_date)->format('d/m/Y H:i') : $acrylicOrder->created_at->format('d/m/Y H:i');
    $deadlineFormatted = $acrylicOrder->deadline ? \Carbon\Carbon::parse($acrylicOrder->deadline)->format('d/m/Y') : '—';
    $customerName = $acrylicOrder->customer ? ($acrylicOrder->customer->name . ' (' . $acrylicOrder->customer->customer_code . ')') : '';

    $totalItems = $acrylicOrder->supplies->flatMap(function ($s) use ($acrylicOrder) {
        if ($acrylicOrder->type === 'min_late') return $s->minLateItems;
        if ($acrylicOrder->type === 'glass') return $s->glassItems;
        return $s->items;
    })->sum(fn($i) => $i->quantity ?? $i->wing_quantity ?? 0);

    if ($acrylicOrder->type === 'service' && $totalItems == 0) {
        $totalItems = $acrylicOrder->paymentDetails ? $acrylicOrder->paymentDetails->sum('quantity') : 0;
    }

    $totalSheets = $acrylicOrder->supplies->sum(fn($s) => $s->quantity ?? 0);

    $totalArea = $acrylicOrder->supplies->flatMap(function ($s) use ($acrylicOrder) {
        if ($acrylicOrder->type === 'glass') return $s->glassItems;
        return $s->items;
    })->sum(fn($i) => $i->wing_area ?? $i->area_m2 ?? 0);

    $discountAmt = $acrylicOrder->discount_amount ?? 0;
    $vatAmt = $acrylicOrder->vat_amount ?? 0;
    $subTotal = $acrylicOrder->total_amount + $discountAmt - $vatAmt;

    // Order specific payment
    $orderPaidAmt = $acrylicOrder->orderPayments ? $acrylicOrder->orderPayments->sum('amount') : 0;
    $orderRemainingAmt = max(0, $acrylicOrder->total_amount - $orderPaidAmt);

    // Customer debt calculation matching OrderController::getExportData exactly
    $debtInfo = $exportData['customer_debt_info'] ?? null;
    if ($debtInfo === null && $acrylicOrder->customer) {
        $thisOrderTotal = round($acrylicOrder->total_amount, -3);
        $thisOrderPaid = $acrylicOrder->orderPayments ? $acrylicOrder->orderPayments->sum('amount') : 0;
        $thisOrderUnpaid = 0;
        if (!in_array($acrylicOrder->status, ['draft', 'cancelled', 'pending'])) {
            $thisOrderUnpaid = max(0, $thisOrderTotal - $thisOrderPaid);
        }
        $currentTotalDebt = $acrylicOrder->customer->total_debt ?? 0;
        $todayPaid = $acrylicOrder->customer->customerPayments()
            ->whereDate('payment_date', \Carbon\Carbon::today())
            ->sum('amount');
        $oldDebt = max(0, $currentTotalDebt - $thisOrderUnpaid + $todayPaid);
        $totalCombinedDebt = $oldDebt + $thisOrderTotal - $todayPaid;

        if ($oldDebt > 0 || $todayPaid > 0 || $totalCombinedDebt > 0) {
            $debtInfo = [
                'old_debt' => $oldDebt,
                'this_order_paid' => $todayPaid,
                'total_combined_debt' => max(0, $totalCombinedDebt),
            ];
        }
    }
@endphp

<!-- Preview Order Modal -->
<div id="preview-order-modal" style="position:fixed; inset:0; z-index:99999999; display:none; align-items:center; justify-content:center;">
    <div style="position:absolute; inset:0; background:rgba(0,0,0,0.5);" onclick="closePreviewOrder()"></div>
    <div class="bg-white flex flex-col" style="position:relative; width:100%; height:100%; z-index:1;">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 flex-shrink-0 flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-50 rounded-xl">
                    <iconify-icon icon="lucide:eye" class="text-xl text-emerald-500"></iconify-icon>
                </div>
                <div>
                    <h5 class="font-bold text-base text-neutral-800 m-0">Xem trước đơn hàng</h5>
                    <p class="text-xs text-neutral-400 m-0 mt-0.5">{{ $acrylicOrder->order_code }} • {{ $typeLabel }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" id="btn-copy-preview-header" onclick="copyPreviewOrderImage()" class="btn text-white px-3.5 py-2 rounded-lg text-xs font-bold shadow-sm transition-all flex items-center gap-1.5 hover:opacity-95" style="background:#0068FF; border:none;" title="Chụp ảnh đơn hàng và lưu vào bộ nhớ tạm (sang Zalo chỉ cần Ctrl + V)">
                    <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                    <span>Copy ảnh gửi Zalo</span>
                </button>
                <button type="button" onclick="downloadPreviewOrderImage()" class="btn bg-white hover:bg-neutral-50 text-neutral-600 px-3 py-2 rounded-lg text-xs font-semibold border border-neutral-300 shadow-xs transition-all flex items-center gap-1.5" title="Tải ảnh PNG đơn hàng về máy">
                    <iconify-icon icon="lucide:download" class="text-sm"></iconify-icon>
                    <span>Tải ảnh</span>
                </button>
                <div class="h-6 w-px bg-neutral-200 mx-1"></div>
                <button type="button" onclick="closePreviewOrder()" class="w-8 h-8 rounded-lg hover:bg-neutral-100 flex items-center justify-center text-neutral-400 hover:text-neutral-600 transition-colors">
                    <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                </button>
            </div>
        </div>

        <!-- Body (scrollable) -->
        <div class="flex-1 overflow-y-auto p-4 md:p-6 bg-neutral-100/50" style="min-height:0;" id="preview-order-scroll-container">
            <div id="preview-order-capture-area" class="bg-white p-6 md:p-8 space-y-6 rounded-2xl border border-neutral-200 shadow-sm w-full mx-auto" style="min-width: 1100px;">
                <!-- Header trên ảnh khi xuất -->
                <div class="border-b-2 border-primary-500 pb-5 flex items-start justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <img src="{{ asset('logo.png') }}" alt="Gervin Wood" class="h-16 w-auto object-contain flex-shrink-0" style="max-height: 64px;" crossorigin="anonymous">
                        <div>
                            <h3 class="font-black text-lg text-neutral-900 m-0 tracking-tight leading-tight uppercase">CÔNG TY TNHH GỖ GERVIN</h3>
                            <p class="text-xs text-neutral-600 m-0 mt-1 font-medium leading-relaxed">
                                Địa chỉ: Xóm 3, Hưng Thịnh, Hưng Nguyên, Nghệ An<br>
                                Hotline: <strong class="text-neutral-800 font-semibold">0967.181.786</strong> • Email: contact@gervinwood.com • Website: <strong class="text-primary-600">www.gervinwood.com</strong>
                            </p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <div class="inline-block px-3 py-1 bg-primary-50 text-primary-700 border border-primary-200 rounded-lg font-bold text-xs uppercase tracking-wider mb-1.5">
                            BÁO GIÁ KIÊM ĐƠN ĐẶT HÀNG
                        </div>
                        <div class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Mã đơn hàng</div>
                        <div class="text-2xl font-black text-primary-600 font-mono tracking-tight leading-none">{{ $acrylicOrder->order_code }}</div>
                        <div class="text-xs text-neutral-500 mt-1 font-medium">Ngày chốt: {{ $orderDateFormatted }}</div>
                    </div>
                </div>

                <!-- Thông tin khách hàng & đơn hàng -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
                        <div class="flex items-center gap-2 mb-3 pb-3 border-b border-neutral-100">
                            <iconify-icon icon="lucide:user" class="text-lg text-primary-500"></iconify-icon>
                            <h6 class="font-bold text-sm text-neutral-800 m-0">Thông tin khách hàng & công trình</h6>
                        </div>
                        @if($customerName)
                            <div class="flex items-start justify-between py-1.5 border-b border-neutral-100 text-xs">
                                <span class="text-neutral-500 flex items-center gap-1.5 font-medium"><iconify-icon icon="lucide:user" class="text-sm text-neutral-400"></iconify-icon>Khách hàng:</span>
                                <span class="font-bold text-neutral-800 text-right">{{ $customerName }}</span>
                            </div>
                        @endif
                        <div class="flex items-start justify-between py-1.5 border-b border-neutral-100 text-xs">
                            <span class="text-neutral-500 flex items-center gap-1.5 font-medium"><iconify-icon icon="lucide:building" class="text-sm text-neutral-400"></iconify-icon>Tên công trình:</span>
                            <span class="font-bold text-neutral-800 text-right">{{ $acrylicOrder->customer_name ?: '—' }}</span>
                        </div>
                        <div class="flex items-start justify-between py-1.5 border-b border-neutral-100 text-xs">
                            <span class="text-neutral-500 flex items-center gap-1.5 font-medium"><iconify-icon icon="lucide:phone" class="text-sm text-neutral-400"></iconify-icon>Số điện thoại:</span>
                            <span class="font-bold text-neutral-800 text-right">{{ $acrylicOrder->phone ?: '—' }}</span>
                        </div>
                        <div class="flex items-start justify-between py-1.5 text-xs">
                            <span class="text-neutral-500 flex items-center gap-1.5 font-medium"><iconify-icon icon="lucide:map-pin" class="text-sm text-neutral-400"></iconify-icon>Địa chỉ:</span>
                            <span class="font-bold text-neutral-800 text-right">{{ $acrylicOrder->address ?: '—' }}</span>
                        </div>
                    </div>

                    <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
                        <div class="flex items-center gap-2 mb-3 pb-3 border-b border-neutral-100">
                            <iconify-icon icon="lucide:file-text" class="text-lg text-primary-500"></iconify-icon>
                            <h6 class="font-bold text-sm text-neutral-800 m-0">Thông tin đơn hàng</h6>
                        </div>
                        <div class="flex items-start justify-between py-1.5 border-b border-neutral-100 text-xs">
                            <span class="text-neutral-500 flex items-center gap-1.5 font-medium"><iconify-icon icon="lucide:hash" class="text-sm text-neutral-400"></iconify-icon>Mã đơn hàng:</span>
                            <span class="font-bold text-primary-600 font-mono text-right">{{ $acrylicOrder->order_code }}</span>
                        </div>
                        <div class="flex items-start justify-between py-1.5 border-b border-neutral-100 text-xs">
                            <span class="text-neutral-500 flex items-center gap-1.5 font-medium"><iconify-icon icon="lucide:tag" class="text-sm text-neutral-400"></iconify-icon>Loại đơn:</span>
                            <span class="font-bold text-neutral-800 text-right">{{ $typeLabel }}</span>
                        </div>
                        <div class="flex items-start justify-between py-1.5 border-b border-neutral-100 text-xs">
                            <span class="text-neutral-500 flex items-center gap-1.5 font-medium"><iconify-icon icon="lucide:calendar" class="text-sm text-neutral-400"></iconify-icon>Ngày chốt đơn:</span>
                            <span class="font-bold text-neutral-800 text-right">{{ $orderDateFormatted }}</span>
                        </div>
                        <div class="flex items-start justify-between py-1.5 border-b border-neutral-100 text-xs">
                            <span class="text-neutral-500 flex items-center gap-1.5 font-medium"><iconify-icon icon="lucide:truck" class="text-sm text-neutral-400"></iconify-icon>Số ngày giao:</span>
                            <span class="font-bold text-neutral-800 text-right">{{ $acrylicOrder->delivery_days ? $acrylicOrder->delivery_days . ' ngày' : '—' }}</span>
                        </div>
                        <div class="flex items-start justify-between py-1.5 border-b border-neutral-100 text-xs">
                            <span class="text-neutral-500 flex items-center gap-1.5 font-medium"><iconify-icon icon="lucide:clock" class="text-sm text-neutral-400"></iconify-icon>Hạn đơn:</span>
                            <span class="font-bold text-danger-600 text-right">{{ $deadlineFormatted }}</span>
                        </div>
                        <div class="flex items-start justify-between py-1.5 text-xs">
                            <span class="text-neutral-500 flex items-center gap-1.5 font-medium"><iconify-icon icon="lucide:activity" class="text-sm text-neutral-400"></iconify-icon>Trạng thái:</span>
                            <span class="font-bold text-neutral-800 text-right">{{ $statusText }}</span>
                        </div>
                    </div>
                </div>

                <!-- Ghi chú & Chính sách KH -->
                @if($acrylicOrder->notes || $acrylicOrder->customer_policy)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        @if($acrylicOrder->notes)
                            <div class="bg-amber-50/50 border border-amber-200 rounded-xl p-5">
                                <div class="flex items-center gap-2 mb-2">
                                    <iconify-icon icon="lucide:sticky-note" class="text-base text-amber-500"></iconify-icon>
                                    <span class="font-bold text-xs text-amber-700 uppercase">Ghi chú đơn hàng</span>
                                </div>
                                <p class="text-sm text-neutral-700 whitespace-pre-wrap m-0">{{ $acrylicOrder->notes }}</p>
                            </div>
                        @endif
                        @if($acrylicOrder->customer_policy)
                            <div class="bg-blue-50/50 border border-blue-200 rounded-xl p-5">
                                <div class="flex items-center gap-2 mb-2">
                                    <iconify-icon icon="lucide:shield" class="text-base text-blue-500"></iconify-icon>
                                    <span class="font-bold text-xs text-blue-700 uppercase">Chính sách KH</span>
                                </div>
                                <p class="text-sm text-neutral-700 whitespace-pre-wrap m-0">{{ $acrylicOrder->customer_policy }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Danh sách Vật tư & Sản phẩm -->
                @if($acrylicOrder->supplies && $acrylicOrder->supplies->count() > 0)
                    <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-neutral-100">
                            <iconify-icon icon="lucide:package-open" class="text-lg text-primary-500"></iconify-icon>
                            <h6 class="font-bold text-sm text-neutral-800 m-0">Danh sách Vật tư & Sản phẩm</h6>
                        </div>

                        @foreach($acrylicOrder->supplies as $sIdx => $supply)
                            <div class="mb-6">
                                <div class="flex items-center gap-3 mb-2.5 pb-2 border-b border-neutral-200">
                                    <div class="p-1.5 bg-primary-100 rounded-lg text-primary-700 flex items-center justify-center font-bold">
                                        <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                                    </div>
                                    <span class="font-extrabold text-sm text-neutral-800">{{ $supply->order_supply_code ? '[' . $supply->order_supply_code . '] ' : '' }}{{ $supply->supply_name ?: 'Vật tư ' . ($sIdx + 1) }}</span>
                                    @if($supply->quantity)
                                        <span class="text-xs bg-primary-50 text-primary-700 border border-primary-200 px-2.5 py-0.5 rounded-full font-bold">SL tấm: {{ floatval($supply->quantity) == intval($supply->quantity) ? number_format($supply->quantity, 0, ',', '.') : number_format($supply->quantity, 2, ',', '.') }}</span>
                                    @endif
                                </div>

                                <div class="overflow-x-auto rounded-lg border border-neutral-300">
                                    @if($acrylicOrder->type === 'acrylic')
                                        {{-- Bảng Acrylic --}}
                                        @php
                                            $sumQty = 0; $sumWingArea = 0; $sumMolding = 0; $sumTotal = 0;
                                        @endphp
                                        <table class="w-full" style="border-collapse: collapse; min-width: 100%;">
                                            <thead>
                                                <tr style="background-color: #f1f5f9;">
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">STT</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Mã SP</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: left; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Tên sản phẩm</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Độ dày</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Cao (vân)</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Rộng</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Số lượng</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Vát</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Chiều vân</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Cánh (m²)</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Phào (m)</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: right; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Đơn giá</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: right; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Thành tiền</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: left; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Ghi chú</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($supply->items as $iIdx => $item)
                                                    @php
                                                        $sumQty += (float)($item->quantity ?? 0);
                                                        $sumWingArea += (float)($item->wing_area ?? 0);
                                                        $sumMolding += (float)($item->molding_length ?? 0);
                                                        $sumTotal += (float)($item->total_price ?? 0);

                                                        $bevelVal = '—';
                                                        if ($item->bevel) {
                                                            if ($item->bevel === 'width') $bevelVal = $item->width ?: 'Vát';
                                                            elseif ($item->bevel === 'height') $bevelVal = $item->height ?: 'Vát';
                                                            else $bevelVal = $item->bevel;
                                                        } elseif ($item->edge_bevel) {
                                                            $bevelVal = $item->edge_bevel;
                                                        }

                                                        $grainText = 'Không vân';
                                                        if ($item->grain_direction == 1) $grainText = 'Vân dọc';
                                                        elseif ($item->grain_direction == 2) $grainText = 'Vân ngang';
                                                    @endphp
                                                    <tr style="border-bottom: 1px solid #cbd5e1;">
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #64748b; font-size: 13px; white-space: nowrap;">{{ $iIdx + 1 }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #64748b; font-size: 13px; font-weight: 600; font-family: monospace; white-space: nowrap;">{{ $item->product_code ?? '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: left; color: #0f172a; font-size: 13px; font-weight: 600; white-space: nowrap;">{{ $item->product_name }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->thickness ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->height ?: '—' }}</td>
@php
    $wVal = floatval($item->width ?? 0);
    $prodName = trim($item->product_name ?? ($item->name ?? ''));
    $isNarrow = ($wVal > 0 && $wVal < 55 && $prodName !== 'Công giả dày');
@endphp
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; {{ $isNarrow ? 'color: #ef4444; background-color: #fef2f2; font-weight: 700;' : 'color: #0f172a;' }} font-size: 13px; white-space: nowrap;">{{ $item->width ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $item->quantity }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $bevelVal }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $grainText }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #2563eb; font-size: 13px; font-weight: 600; white-space: nowrap;">{{ $item->wing_area ? number_format($item->wing_area, 2, ',', '.') : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->molding_length ? number_format($item->molding_length, 2, ',', '.') : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: right; color: #0f172a; font-size: 13px; font-weight: 600; white-space: nowrap;">{{ $item->unit_price ? number_format($item->unit_price, 0, ',', '.') . ' đ' : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: right; color: #059669; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $item->total_price ? number_format($item->total_price, 0, ',', '.') . ' đ' : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: left; color: #64748b; font-size: 13px; white-space: nowrap;">{{ $item->notes ?: '—' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="14" class="text-center text-xs text-neutral-400 py-4">Chưa có sản phẩm</td></tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr style="background-color: #f8fafc; font-weight: 700; border-top: 2px solid #94a3b8;">
                                                    <td style="border: 1px solid #cbd5e1; padding: 8px 8px; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700; white-space: nowrap;">TỔNG</td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1; padding: 8px 8px; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $sumQty }}</td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1; padding: 8px 8px; text-align: center; color: #2563eb; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $sumWingArea > 0 ? number_format($sumWingArea, 2, ',', '.') . ' m²' : '—' }}</td>
                                                    <td style="border: 1px solid #cbd5e1; padding: 8px 8px; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $sumMolding > 0 ? number_format($sumMolding, 2, ',', '.') . ' m' : '—' }}</td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1; padding: 8px 8px; text-align: right; color: #059669; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $sumTotal > 0 ? number_format(round($sumTotal), 0, ',', '.') . ' đ' : '—' }}</td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                </tr>
                                            </tfoot>
                                        </table>

                                    @elseif(in_array($acrylicOrder->type, ['min_late', 'Flywood']))
                                        {{-- Bảng Min Late / Flywood --}}
                                        @php $sumQty = 0; @endphp
                                        <table class="w-full" style="border-collapse: collapse; min-width: 100%;">
                                            <thead>
                                                <tr style="background-color: #f1f5f9;">
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">STT</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Mã SP</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: left; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Tên sản phẩm</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Độ dày</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Cao</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Rộng</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Số lượng</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Dán cạnh</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Vát</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Dán thẳng</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Dán vát</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Vát mòi</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Bản rộng</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Tay nắm vát</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">CNC</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Hướng vân</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: left; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Ghi chú</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($supply->minLateItems as $iIdx => $item)
                                                    @php
                                                        $sumQty += (float)($item->quantity ?? 0);
                                                        $sizes = is_array($item->size) ? $item->size : (json_decode($item->size, true) ?? []);
                                                        $edges = is_array($item->edge_gluing) ? $item->edge_gluing : (json_decode($item->edge_gluing, true) ?? []);
                                                        $edgeTxt = !empty($edges) ? implode(', ', array_filter($edges)) : '—';
                                                    @endphp
                                                    <tr style="border-bottom: 1px solid #cbd5e1;">
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #64748b; font-size: 13px; white-space: nowrap;">{{ $iIdx + 1 }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #64748b; font-size: 13px; font-weight: 600; font-family: monospace; white-space: nowrap;">{{ $item->product_code ?? '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: left; color: #0f172a; font-size: 13px; font-weight: 600; white-space: nowrap;">{{ $item->name }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->thickness ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $sizes['height'] ?? '—' }}</td>
@php
    $wVal = floatval($sizes['width'] ?? ($item->width ?? 0));
    $prodName = trim($item->name ?? ($item->product_name ?? ''));
    $isNarrow = ($wVal > 0 && $wVal < 55 && $prodName !== 'Công giả dày');
@endphp
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; {{ $isNarrow ? 'color: #ef4444; background-color: #fef2f2; font-weight: 700;' : 'color: #0f172a;' }} font-size: 13px; white-space: nowrap;">{{ $sizes['width'] ?? '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $item->quantity }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $edgeTxt }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->bevel ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->straight_paste_length ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->beveled_length ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->vat_moi_length ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->ban_rong_40_59 ?: ($item->ban_rong_17_39 ?: ($item->ban_rong_25_35 ?: '—')) }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->beveled_handle ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->cnc ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->direction ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: left; color: #64748b; font-size: 13px; white-space: nowrap;">{{ $item->notes ?: '—' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="17" class="text-center text-xs text-neutral-400 py-4">Chưa có sản phẩm</td></tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr style="background-color: #f8fafc; font-weight: 700; border-top: 2px solid #94a3b8;">
                                                    <td style="border: 1px solid #cbd5e1; padding: 8px 8px; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700; white-space: nowrap;">TỔNG</td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1; padding: 8px 8px; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $sumQty }}</td>
                                                    <td colspan="10" style="border: 1px solid #cbd5e1;"></td>
                                                </tr>
                                            </tfoot>
                                        </table>

                                    @elseif($acrylicOrder->type === 'glass')
                                        {{-- Bảng Glass --}}
                                        @php $sumQty = 0; $sumArea = 0; $sumTotal = 0; @endphp
                                        <table class="w-full" style="border-collapse: collapse; min-width: 100%;">
                                            <thead>
                                                <tr style="background-color: #f1f5f9;">
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">STT</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Mã SP</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: left; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Tên sản phẩm</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Độ dày</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Mở cánh</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Màu nhôm</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Màu kính</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Cao</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Rộng</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Đơn vị</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">SL cánh</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Diện tích (m²)</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: right; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Đơn giá</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: right; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Thành tiền</th>
                                                    <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: left; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Ghi chú</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($supply->glassItems as $iIdx => $item)
                                                    @php
                                                        $sumQty += (float)($item->wing_quantity ?? 0);
                                                        $sumArea += (float)($item->area_m2 ?? 0);
                                                        $sumTotal += (float)($item->total_price ?? 0);
                                                    @endphp
                                                    <tr style="border-bottom: 1px solid #cbd5e1;">
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #64748b; font-size: 13px; white-space: nowrap;">{{ $iIdx + 1 }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #64748b; font-size: 13px; font-weight: 600; font-family: monospace; white-space: nowrap;">{{ $item->product_code ?? '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: left; color: #0f172a; font-size: 13px; font-weight: 600; white-space: nowrap;">{{ $item->product_name }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->thickness ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->wing_opening_direction ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->aluminum_color ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->glass_color ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->height ?: '—' }}</td>
@php
    $wVal = floatval($item->width ?? 0);
    $prodName = trim($item->product_name ?? ($item->name ?? ''));
    $isNarrow = ($wVal > 0 && $wVal < 55 && $prodName !== 'Công giả dày');
@endphp
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; {{ $isNarrow ? 'color: #ef4444; background-color: #fef2f2; font-weight: 700;' : 'color: #0f172a;' }} font-size: 13px; white-space: nowrap;">{{ $item->width ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->unit ?: 'cánh' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $item->wing_quantity }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #2563eb; font-size: 13px; font-weight: 600; white-space: nowrap;">{{ $item->area_m2 ? number_format($item->area_m2, 2, ',', '.') : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: right; color: #0f172a; font-size: 13px; font-weight: 600; white-space: nowrap;">{{ $item->unit_price ? number_format($item->unit_price, 0, ',', '.') . ' đ' : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: right; color: #059669; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $item->total_price ? number_format($item->total_price, 0, ',', '.') . ' đ' : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: left; color: #64748b; font-size: 13px; white-space: nowrap;">{{ $item->notes ?: '—' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="15" class="text-center text-xs text-neutral-400 py-4">Chưa có sản phẩm</td></tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr style="background-color: #f8fafc; font-weight: 700; border-top: 2px solid #94a3b8;">
                                                    <td style="border: 1px solid #cbd5e1; padding: 8px 8px; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700; white-space: nowrap;">TỔNG</td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1; padding: 8px 8px; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $sumQty }}</td>
                                                    <td style="border: 1px solid #cbd5e1; padding: 8px 8px; text-align: center; color: #2563eb; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $sumArea > 0 ? number_format($sumArea, 2, ',', '.') . ' m²' : '—' }}</td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                    <td style="border: 1px solid #cbd5e1; padding: 8px 8px; text-align: right; color: #059669; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $sumTotal > 0 ? number_format(round($sumTotal), 0, ',', '.') . ' đ' : '—' }}</td>
                                                    <td style="border: 1px solid #cbd5e1;"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Chi tiết hóa đơn dịch vụ & Vật tư bổ sung -->
                @if($acrylicOrder->paymentDetails && $acrylicOrder->paymentDetails->count() > 0)
                    <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-neutral-100">
                            <iconify-icon icon="lucide:receipt" class="text-lg text-primary-500"></iconify-icon>
                            <h6 class="font-bold text-sm text-neutral-800 m-0">Chi tiết hóa đơn dịch vụ & Vật tư bổ sung</h6>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-neutral-200">
                            <table class="w-full" style="border-collapse: collapse; min-width: 100%;">
                                <thead>
                                    <tr style="background-color: #f1f5f9;">
                                        <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">STT</th>
                                        <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: left; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Tên nội dung / Dịch vụ / Vật tư</th>
                                        <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Đơn vị</th>
                                        <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Số lượng</th>
                                        <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: right; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Đơn giá</th>
                                        <th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: right; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sumPayTotal = 0; @endphp
                                    @foreach($acrylicOrder->paymentDetails as $pIdx => $pDetail)
                                        @php $sumPayTotal += (float)($pDetail->total ?? 0); @endphp
                                        <tr style="border-bottom: 1px solid #cbd5e1;">
                                            <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #64748b; font-size: 13px; white-space: nowrap;">{{ $pIdx + 1 }}</td>
                                            <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: left; color: #0f172a; font-size: 13px; font-weight: 600; white-space: nowrap;">{{ $pDetail->name }}</td>
                                            <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $pDetail->unit ?: '—' }}</td>
                                            <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ floatval($pDetail->quantity) == intval($pDetail->quantity) ? number_format($pDetail->quantity, 0, ',', '.') : number_format($pDetail->quantity, 2, ',', '.') }}</td>
                                            <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: right; color: #0f172a; font-size: 13px; font-weight: 600; white-space: nowrap;">{{ number_format($pDetail->price, 0, ',', '.') }} đ</td>
                                            <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: right; color: #059669; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ number_format($pDetail->total, 0, ',', '.') }} đ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background-color: #f8fafc; font-weight: 700; border-top: 2px solid #94a3b8;">
                                        <td style="border: 1px solid #cbd5e1; padding: 8px 8px; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700; white-space: nowrap;">TỔNG</td>
                                        <td colspan="4" style="border: 1px solid #cbd5e1;"></td>
                                        <td style="border: 1px solid #cbd5e1; padding: 8px 8px; text-align: right; color: #059669; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ number_format(round($sumPayTotal), 0, ',', '.') }} đ</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Tóm tắt đơn hàng -->
                <div class="bg-gradient-to-r from-primary-50 to-emerald-50 border border-primary-200 rounded-xl p-5">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-primary-100">
                        <iconify-icon icon="lucide:receipt-text" class="text-lg text-primary-500"></iconify-icon>
                        <h6 class="font-bold text-sm text-neutral-800 m-0">Tóm tắt đơn hàng</h6>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-3 bg-white/70 rounded-lg">
                            <div class="text-xs text-neutral-500 font-medium">Số sản phẩm</div>
                            <div class="text-lg font-bold text-neutral-800 mt-1">{{ $totalItems }}</div>
                        </div>
                        <div class="text-center p-3 bg-white/70 rounded-lg">
                            <div class="text-xs text-neutral-500 font-medium">Tổng số tấm</div>
                            <div class="text-lg font-bold text-neutral-800 mt-1">{{ $totalSheets }}</div>
                        </div>
                        <div class="text-center p-3 bg-white/70 rounded-lg">
                            <div class="text-xs text-neutral-500 font-medium">Tổng diện tích</div>
                            <div class="text-lg font-bold text-blue-600 mt-1">{{ number_format($totalArea, 2, ',', '.') }} m²</div>
                        </div>
                        <div class="text-center p-3 bg-white/70 rounded-lg">
                            <div class="text-xs text-neutral-500 font-medium">Tổng tiền hàng</div>
                            <div class="text-lg font-bold text-neutral-800 mt-1">{{ number_format(round($subTotal, -3), 0, ',', '.') }} đ</div>
                        </div>
                    </div>
                    @if($acrylicOrder->discount_percent > 0)
                        <div class="mt-2 text-xs text-right font-medium text-danger-600">
                            Chiết khấu ({{ (float)$acrylicOrder->discount_percent }}%): -{{ number_format(round($discountAmt, -3), 0, ',', '.') }} đ
                        </div>
                    @endif
                    @if($acrylicOrder->vat_percent > 0)
                        <div class="mt-1 text-xs text-right font-medium text-neutral-700">
                            VAT ({{ (float)$acrylicOrder->vat_percent }}%): +{{ number_format(round($vatAmt, -3), 0, ',', '.') }} đ
                        </div>
                    @endif
                    <div class="mt-4 pt-4 border-t border-primary-100 flex items-center justify-between">
                        <span class="font-bold text-sm text-neutral-800">Tổng thanh toán đơn này:</span>
                        <span class="text-2xl font-extrabold text-primary-600">{{ number_format(round($acrylicOrder->total_amount, -3), 0, ',', '.') }} VNĐ</span>
                    </div>

                    @if($orderPaidAmt > 0)
                        <div class="mt-2.5 pt-2.5 border-t border-dashed border-primary-200/80 flex items-center justify-between text-xs">
                            <span class="text-neutral-600 font-medium">Đã thanh toán đơn này:</span>
                            <span class="font-bold text-emerald-600 font-mono text-sm">{{ number_format(round($orderPaidAmt, -3), 0, ',', '.') }} đ</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-xs">
                            <span class="text-neutral-600 font-medium">Còn lại của đơn:</span>
                            <span class="font-bold text-rose-600 font-mono text-sm">{{ number_format(round($orderRemainingAmt, -3), 0, ',', '.') }} đ</span>
                        </div>
                    @endif

                    {{-- Khối thông tin công nợ khách hàng (đồng bộ với khi xuất Excel) --}}
                    @if(!empty($debtInfo))
                        <div class="mt-5 pt-4 border-t-2 border-dashed border-primary-200">
                            <div class="text-xs font-bold uppercase tracking-wider text-neutral-600 mb-2.5 flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-primary-700">
                                    <iconify-icon icon="lucide:wallet" class="text-base text-primary-600"></iconify-icon>
                                    <span>Thông tin công nợ khách hàng</span>
                                </span>
                                <span class="text-[11px] font-normal text-neutral-400 lowercase italic">(đồng bộ với bảng công nợ khi xuất excel)</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="bg-white p-3.5 rounded-xl border border-neutral-200 shadow-xs flex flex-col justify-between">
                                    <span class="text-xs font-semibold text-neutral-500">Công nợ:</span>
                                    <span class="text-base font-bold text-neutral-800 mt-1 font-mono">
                                        {{ number_format(round($debtInfo['old_debt'], -3), 0, ',', '.') }} đ
                                    </span>
                                </div>
                                <div class="bg-white p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/40 shadow-xs flex flex-col justify-between">
                                    <span class="text-xs font-semibold text-emerald-700">Đã TT (hôm nay):</span>
                                    <span class="text-base font-bold text-emerald-600 mt-1 font-mono">
                                        {{ number_format(round($debtInfo['this_order_paid'], -3), 0, ',', '.') }} đ
                                    </span>
                                </div>
                                <div class="bg-white p-3.5 rounded-xl border border-rose-200 bg-rose-50/50 shadow-xs flex flex-col justify-between">
                                    <span class="text-xs font-bold text-rose-700">Tổng công nợ:</span>
                                    <span class="text-lg font-black text-rose-600 mt-1 font-mono">
                                        {{ number_format(round($debtInfo['total_combined_debt'], -3), 0, ',', '.') }} đ
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Footer trên ảnh khi xuất -->
                <div class="pt-4 border-t border-neutral-200 flex items-center justify-between text-xs text-neutral-400">
                    <span>Xưởng Nội Thất Gervin</span>
                    <span>Phiếu xuất lúc: {{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between gap-4 px-6 py-4 border-t border-neutral-200 flex-shrink-0 bg-neutral-50/50 flex-wrap">
            <div class="flex items-center gap-2 text-xs text-neutral-600 mr-auto">
                <iconify-icon icon="lucide:sparkles" class="text-base text-amber-500 shrink-0"></iconify-icon>
                <span>Bấm <strong>Copy ảnh gửi Zalo</strong>, sau đó mở chat với khách và ấn <strong>Ctrl + V</strong> để gửi ảnh ngay.</span>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" onclick="closePreviewOrder()" class="btn btn-outline-neutral px-4 py-2 rounded-lg text-sm font-semibold transition-all">Đóng</button>
                <button type="button" onclick="copyPreviewOrderImage()" class="btn text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center gap-2 hover:opacity-95" style="background:#0068FF; border:none;" title="Copy ảnh để dán Ctrl + V vào Zalo">
                    <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                    <span>Copy ảnh gửi Zalo</span>
                </button>
                @can('edit order')
                    @if(!in_array($acrylicOrder->status, ['in_production', 'cancelled']))
                        <a href="{{ route('orders.edit', $acrylicOrder) }}" class="btn btn-primary px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center gap-2">
                            <iconify-icon icon="lucide:edit-3" class="text-base"></iconify-icon>
                            <span>Chỉnh sửa đơn hàng</span>
                        </a>
                    @endif
                @endcan

                @if(auth()->user()->can('approve order') || auth()->user()->can('edit order') || auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Kế toán') || auth()->user()->hasRole('Kỹ thuật'))
                    {{-- 1. Nút Kế toán duyệt --}}
                    @if(!$acrylicOrder->accountant_approved_by && in_array($acrylicOrder->status, ['draft', 'pending']))
                        <form method="POST" action="{{ route('orders.update-status', $acrylicOrder) }}" onsubmit="return confirm('Kế toán xác nhận duyệt đơn hàng {{ $acrylicOrder->order_code }}?')" class="m-0 inline-block">
                            @csrf
                            <input type="hidden" name="status" value="accountant_approve">
                            <button type="submit" class="btn text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition-all flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 cursor-pointer" title="Kế toán xác nhận duyệt đơn hàng">
                                <iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon>
                                <span>Kế toán duyệt</span>
                            </button>
                        </form>
                    @elseif($acrylicOrder->accountant_approved_by)
                        <span class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-300" title="Kế toán duyệt lúc {{ $acrylicOrder->accountant_approved_at?->format('H:i d/m/Y') }}">
                            <iconify-icon icon="lucide:check-check" class="text-sm text-emerald-600"></iconify-icon>
                            <span>KT đã duyệt ({{ $acrylicOrder->accountantApprover?->name ?? 'Xong' }})</span>
                        </span>
                    @endif

                    {{-- 2. Nút Kỹ thuật duyệt (Chuyển SX) --}}
                    @if(!$acrylicOrder->technical_approved_by && in_array($acrylicOrder->status, ['draft', 'pending']))
                        @if($acrylicOrder->accountant_approved_by)
                            <form method="POST" action="{{ route('orders.update-status', $acrylicOrder) }}" onsubmit="return confirm('Kỹ thuật xác nhận duyệt & Chuyển sản xuất đơn hàng {{ $acrylicOrder->order_code }}?')" class="m-0 inline-block">
                                @csrf
                                <input type="hidden" name="status" value="transferred">
                                <button type="submit" class="btn text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition-all flex items-center gap-2 bg-violet-600 hover:bg-violet-700 cursor-pointer" title="Kỹ thuật duyệt & Chuyển đơn sang sản xuất">
                                    <iconify-icon icon="lucide:factory" class="text-base"></iconify-icon>
                                    <span>{{ !empty($acrylicOrder->edit_reason) ? 'Chuyển SX lại' : 'Kỹ thuật duyệt & Chuyển SX' }}</span>
                                </button>
                            </form>
                        @else
                            <button type="button" onclick="alert('Đơn hàng cần được Kế toán duyệt trước khi Kỹ thuật duyệt chuyển sản xuất!')" class="btn bg-neutral-100 text-neutral-400 border border-neutral-300 px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 cursor-not-allowed opacity-75" title="Cần Kế toán duyệt trước khi Chuyển SX">
                                <iconify-icon icon="lucide:factory" class="text-base"></iconify-icon>
                                <span>Kỹ thuật duyệt</span>
                            </button>
                        @endif
                    @elseif($acrylicOrder->technical_approved_by)
                        <span class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-bold bg-violet-50 text-violet-800 border border-violet-300" title="Kỹ thuật duyệt lúc {{ $acrylicOrder->technical_approved_at?->format('H:i d/m/Y') }}">
                            <iconify-icon icon="lucide:check-check" class="text-sm text-violet-600"></iconify-icon>
                            <span>SX đã duyệt ({{ $acrylicOrder->technicalApprover?->name ?? 'Đã chuyển SX' }})</span>
                        </span>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    window.currentPreviewOrder = {
        orderCode: {!! json_encode($acrylicOrder->order_code ?? '') !!},
        customerName: {!! json_encode($acrylicOrder->customer_name ?: ($acrylicOrder->customer?->name ?? '')) !!},
        phone: {!! json_encode($acrylicOrder->phone ?: ($acrylicOrder->customer?->phone ?? '')) !!}
    };

    function previewOrder() {
        let modal = document.getElementById('preview-order-modal');
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closePreviewOrder() {
        const modal = document.getElementById('preview-order-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    // Đóng bằng phím Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePreviewOrder();
        }
    });

    function showPreviewToast(message, type = 'success') {
        let container = document.getElementById('preview-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'preview-toast-container';
            container.style.cssText = 'position:fixed; top:24px; left:50%; transform:translateX(-50%); z-index:9999999999; display:flex; flex-direction:column; align-items:center; gap:10px; max-width:560px; width:max-content; pointer-events:none;';
            document.body.appendChild(container);
        }
        const toast = document.createElement('div');
        
        let bgStyle = 'background: linear-gradient(135deg, #0f172a 0%, #064e3b 100%); border: 1.5px solid #10b981; box-shadow: 0 20px 35px -5px rgba(0, 0, 0, 0.55), 0 0 24px rgba(16, 185, 129, 0.35);';
        let icon = 'lucide:check-circle-2';
        let iconColor = '#34d399';
        let strongColor = '#6ee7b7';

        if (type === 'error') {
            bgStyle = 'background: linear-gradient(135deg, #0f172a 0%, #7f1d1d 100%); border: 1.5px solid #ef4444; box-shadow: 0 20px 35px -5px rgba(0, 0, 0, 0.55), 0 0 24px rgba(239, 68, 68, 0.35);';
            icon = 'lucide:alert-circle';
            iconColor = '#f87171';
            strongColor = '#fca5a5';
        } else if (type === 'warning') {
            bgStyle = 'background: linear-gradient(135deg, #0f172a 0%, #78350f 100%); border: 1.5px solid #f59e0b; box-shadow: 0 20px 35px -5px rgba(0, 0, 0, 0.55), 0 0 24px rgba(245, 158, 11, 0.35);';
            icon = 'lucide:alert-triangle';
            iconColor = '#fbbf24';
            strongColor = '#fde68a';
        }

        toast.style.cssText = `
            pointer-events: auto;
            ${bgStyle}
            color: #ffffff;
            border-radius: 14px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            font-family: inherit;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            transform: translateY(-20px) scale(0.96);
            opacity: 0;
        `;

        toast.innerHTML = `
            <div style="flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                <iconify-icon icon="${icon}" style="font-size: 26px; color: ${iconColor};"></iconify-icon>
            </div>
            <div style="font-size: 13.5px; line-height: 1.55; color: #f8fafc; font-weight: 500; word-break: break-word;">
                ${message.replace(/<strong>/g, `<strong style="color:${strongColor}; font-weight:700;">`)}
            </div>
            <button type="button" onclick="this.parentElement.remove()" style="margin-left: 8px; background: rgba(255,255,255,0.1); border: none; width: 26px; height: 26px; border-radius: 50%; cursor: pointer; color: #cbd5e1; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'" title="Đóng thông báo">
                <iconify-icon icon="lucide:x" style="font-size: 15px;"></iconify-icon>
            </button>
        `;

        container.appendChild(toast);
        requestAnimationFrame(() => {
            toast.style.transform = 'translateY(0) scale(1)';
            toast.style.opacity = '1';
        });
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-20px) scale(0.96)';
            setTimeout(() => toast.remove(), 350);
        }, 4500);
    }

    async function ensureHtml2CanvasLoaded() {
        if (window.html2canvas) return window.html2canvas;
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
            script.onload = () => resolve(window.html2canvas);
            script.onerror = () => reject(new Error('Không thể tải thư viện html2canvas từ CDN'));
            document.head.appendChild(script);
        });
    }

    async function getPreviewOrderImageBlob() {
        const captureEl = document.getElementById('preview-order-capture-area');
        if (!captureEl) throw new Error('Không tìm thấy vùng nội dung đơn hàng để chụp');

        await ensureHtml2CanvasLoaded();

        const clone = captureEl.cloneNode(true);
        clone.id = 'zalo-export-clone-node';
        clone.style.position = 'fixed';
        clone.style.top = '0';
        clone.style.left = '-99999px';
        clone.style.width = '1750px';
        clone.style.minWidth = '1750px';
        clone.style.maxWidth = '1750px';
        clone.style.height = 'auto';
        clone.style.zIndex = '-999999';
        clone.style.background = '#ffffff';
        clone.style.padding = '35px 40px';
        clone.style.boxSizing = 'border-box';
        clone.style.boxShadow = 'none';
        clone.style.borderRadius = '0';

        clone.querySelectorAll('.overflow-x-auto, .overflow-y-auto, .overflow-hidden').forEach(el => {
            el.style.overflow = 'visible';
            el.style.width = '100%';
            el.style.maxWidth = 'none';
        });

        clone.querySelectorAll('table').forEach(tbl => {
            tbl.style.width = '100%';
            tbl.style.minWidth = '100%';
            tbl.style.tableLayout = 'auto';
            tbl.style.borderCollapse = 'collapse';
            tbl.style.fontSize = '13.5px';
        });
        clone.querySelectorAll('th').forEach(th => {
            th.style.padding = '10px 8px';
            th.style.fontSize = '13px';
            th.style.fontWeight = '700';
            th.style.border = '1.5px solid #cbd5e1';
            th.style.backgroundColor = '#f1f5f9';
            th.style.color = '#0f172a';
            th.style.whiteSpace = 'nowrap';
        });
        clone.querySelectorAll('td').forEach(td => {
            td.style.padding = '8px 8px';
            td.style.fontSize = '13px';
            td.style.border = '1px solid #cbd5e1';
            td.style.whiteSpace = 'nowrap';
        });

        document.body.appendChild(clone);
        await new Promise(r => setTimeout(r, 80));

        try {
            const canvas = await html2canvas(clone, {
                scale: 2.5,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                logging: false,
                width: clone.scrollWidth,
                height: clone.scrollHeight,
                windowWidth: 1750,
            });

            clone.remove();

            return new Promise((resolve, reject) => {
                canvas.toBlob((blob) => {
                    if (blob) resolve(blob);
                    else reject(new Error('Không thể xuất ảnh từ canvas'));
                }, 'image/png', 0.98);
            });
        } catch (err) {
            if (clone && clone.parentNode) clone.remove();
            throw err;
        }
    }

    async function copyPreviewOrderImage() {
        const orderCode = window.currentPreviewOrder?.orderCode || 'DON_HANG';
        const btn = document.getElementById('btn-copy-preview-header');
        const originalHTML = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<iconify-icon icon="lucide:loader-2" class="text-base animate-spin"></iconify-icon> <span>Đang chụp ảnh...</span>`;
        }

        try {
            const blob = await getPreviewOrderImageBlob();
            
            if (navigator.clipboard && window.ClipboardItem) {
                await navigator.clipboard.write([
                    new ClipboardItem({ 'image/png': blob })
                ]);
                showPreviewToast(`Đã sao chép ảnh đơn hàng <strong>${orderCode}</strong> thành công! Bạn chỉ cần vào khung chat Zalo của khách và bấm <strong>Ctrl + V</strong> để gửi ảnh ngay.`);
            } else {
                downloadBlob(blob, `Don_Hang_${orderCode}.png`);
                showPreviewToast(`Trình duyệt không hỗ trợ sao chép ảnh trực tiếp, đã tự động tải ảnh về máy cho bạn.`, 'warning');
            }
        } catch (err) {
            console.error('Lỗi sao chép ảnh:', err);
            try {
                const blob = await getPreviewOrderImageBlob();
                downloadBlob(blob, `Don_Hang_${orderCode}.png`);
                showPreviewToast(`Không thể copy trực tiếp vào bộ nhớ tạm do chính sách bảo mật trình duyệt. Đã tự động tải file ảnh về máy cho bạn.`, 'warning');
            } catch (downloadErr) {
                showPreviewToast('Không thể tạo ảnh đơn hàng: ' + (err.message || 'Lỗi không xác định'), 'error');
            }
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        }
    }

    async function downloadPreviewOrderImage() {
        const orderCode = window.currentPreviewOrder?.orderCode || 'DON_HANG';
        try {
            const blob = await getPreviewOrderImageBlob();
            downloadBlob(blob, `Don_Hang_${orderCode}.png`);
            showPreviewToast(`Đã tải ảnh đơn hàng <strong>${orderCode}</strong> thành công!`);
        } catch (err) {
            console.error('Lỗi tải ảnh:', err);
            showPreviewToast('Không thể tải ảnh: ' + (err.message || 'Lỗi không xác định'), 'error');
        }
    }

    function downloadBlob(blob, fileName) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        setTimeout(() => {
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }, 100);
    }
</script>
