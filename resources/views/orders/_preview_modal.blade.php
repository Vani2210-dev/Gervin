@php
    $typeLabels = [
        'acrylic' => 'Acrylic',
        'min_late' => 'Lệnh sản xuất',
        'glass' => 'Glass',
        'service' => 'Dịch vụ & Vật tư',
        'Flywood' => 'Flywood',
    ];
    $typeLabel = $acrylicOrder->isNhapKho() ? 'Lệnh sản xuất' : ($typeLabels[$acrylicOrder->type] ?? ucfirst($acrylicOrder->type));

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
    if ($acrylicOrder->isNhapKho()) {
        $customerName = 'Sản xuất nội bộ Gervin';
    }

    $previewPaymentDetails = collect();
    if (isset($acrylicOrder->paymentDetails) && $acrylicOrder->paymentDetails->count() > 0) {
        $previewPaymentDetails = $acrylicOrder->paymentDetails;
    } elseif (!empty($exportData['payment_details'])) {
        $previewPaymentDetails = collect($exportData['payment_details'])->map(fn($d) => (object)$d);
    }

    $totalItems = $acrylicOrder->supplies->flatMap(function ($s) use ($acrylicOrder) {
        if ($acrylicOrder->type === 'min_late') return $s->minLateItems;
        if ($acrylicOrder->type === 'glass') return $s->glassItems;
        return $s->items->filter(fn($i) => ($i->product_name ?? '') !== 'Công giả dày');
    })->sum(fn($i) => $i->quantity ?? $i->wing_quantity ?? 0);

    $pdTotalQty = $previewPaymentDetails->sum(fn($d) => floatval(is_object($d) ? ($d->quantity ?? 0) : ($d['quantity'] ?? 0)));

    if ($totalItems == 0 && $pdTotalQty > 0) {
        $totalItems = $pdTotalQty;
    }

    $totalSheets = $acrylicOrder->supplies->sum(function ($s) use ($acrylicOrder) {
        if ($acrylicOrder->type === 'acrylic') {
            return $s->items->filter(fn($i) => ($i->product_name ?? '') !== 'Công giả dày')->sum('quantity');
        }
        return $s->quantity ?? 0;
    });

    if ($totalSheets == 0 && $pdTotalQty > 0) {
        $sheetCount = $previewPaymentDetails->filter(function($p) {
            $unit = trim(mb_strtolower(is_object($p) ? ($p->unit ?? '') : ($p['unit'] ?? '')));
            return $unit === 'tấm' || empty($unit);
        })->sum(fn($d) => floatval(is_object($d) ? ($d->quantity ?? 0) : ($d['quantity'] ?? 0)));
        $totalSheets = $sheetCount > 0 ? $sheetCount : $pdTotalQty;
    }

    $totalArea = $acrylicOrder->supplies->flatMap(function ($s) use ($acrylicOrder) {
        if ($acrylicOrder->type === 'glass') return $s->glassItems;
        return $s->items->filter(fn($i) => ($i->product_name ?? '') !== 'Công giả dày');
    })->sum(fn($i) => $i->wing_area ?? $i->area_m2 ?? 0);

    $discountAmt = $acrylicOrder->discount_amount ?? 0;
    $vatAmt = $acrylicOrder->vat_amount ?? 0;
    $additionalDiscountAmt = $acrylicOrder->additional_discount_amount ?? 0;
    $subTotal = $acrylicOrder->total_amount + $discountAmt - $vatAmt + $additionalDiscountAmt;

    // Customer debt calculation matching OrderController::getExportData exactly (Phương án 2)
    $debtInfo = $exportData['customer_debt_info'] ?? ($acrylicOrder->customer ? $acrylicOrder->customer->getDebtInfoForOrder($acrylicOrder) : null);

    // Order specific payment
    $orderPaidAmt = $acrylicOrder->orderPayments ? $acrylicOrder->orderPayments->sum('amount') : 0;
    if ($orderPaidAmt == 0 && !empty($debtInfo['this_order_paid'])) {
        $orderPaidAmt = floatval($debtInfo['this_order_paid']);
    }
    $orderRemainingAmt = max(0, $acrylicOrder->total_amount - $orderPaidAmt);

    // Tra cứu thông tin đóng gói từ mục processes/packing
    $packingInfo = $acrylicOrder->getPackingInfo();

    // Danh sách hình ảnh đã tải (tệp đính kèm)
    $orderAttachments = [];
    if (!empty($acrylicOrder->attachments)) {
        $orderAttachments = is_array($acrylicOrder->attachments)
            ? $acrylicOrder->attachments
            : (json_decode($acrylicOrder->attachments, true) ?? []);
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
                    <h5 class="font-bold text-base text-neutral-800 m-0">{{ ($acrylicOrder->isNhapKho() || $acrylicOrder->type === 'min_late' || $acrylicOrder->type === 'Flywood') ? 'Xem trước Lệnh sản xuất' : 'Xem trước đơn hàng' }}</h5>
                    <p class="text-xs text-neutral-400 m-0 mt-1">{{ $acrylicOrder->order_code }} • {{ $typeLabel }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" id="preview-header-attachments-btn" onclick="document.getElementById('preview-order-attachments-section')?.scrollIntoView({behavior:'smooth'})" class="btn bg-primary-50 hover:bg-primary-100 text-primary-700 border border-primary-200 px-3 py-2 rounded-lg text-xs font-bold shadow-xs transition-all flex items-center gap-1.5" style="{{ count($orderAttachments) == 0 ? 'display:none;' : '' }}" title="Cuộn xuống xem hình ảnh đã tải">
                    <iconify-icon icon="lucide:paperclip" class="text-sm text-primary-600"></iconify-icon>
                    <span>Ảnh đã tải (<span id="preview-header-attachments-count">{{ count($orderAttachments) }}</span>)</span>
                </button>
                <button type="button" onclick="printPreviewOrder()" class="btn text-white px-3 py-2 rounded-lg text-xs font-bold shadow-sm transition-all flex items-center gap-2 hover:opacity-95" style="background:#059669; border:none;" title="In đơn hàng ra giấy hoặc xuất PDF (khổ ngang A4)">
                    <iconify-icon icon="lucide:printer" class="text-base"></iconify-icon>
                    <span>In đơn (khổ ngang)</span>
                </button>
                <button type="button" id="btn-copy-preview-header" onclick="copyPreviewOrderImage()" class="btn text-white px-3 py-2 rounded-lg text-xs font-bold shadow-sm transition-all flex items-center gap-2 hover:opacity-95" style="background:#0068FF; border:none;" title="Chụp ảnh đơn hàng và lưu vào bộ nhớ tạm (sang Zalo chỉ cần Ctrl + V)">
                    <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                    <span>Copy ảnh gửi Zalo</span>
                </button>
                <button type="button" onclick="downloadPreviewOrderImage()" class="btn bg-white hover:bg-neutral-50 text-neutral-600 px-3 py-2 rounded-lg text-xs font-semibold border border-neutral-300 shadow-xs transition-all flex items-center gap-2" title="Tải ảnh PNG đơn hàng về máy">
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
        <div class="flex-1 overflow-y-auto p-3 md:p-4 bg-neutral-100/50" style="min-height:0;" id="preview-order-scroll-container">
            <div id="preview-order-capture-area" class="bg-white p-3 space-y-2 rounded-xl border border-neutral-300 shadow-sm w-full mx-auto" style="min-width: 1050px; max-width: 1240px;">
                <!-- Header trên ảnh khi xuất -->
                <div class="border-b-2 border-primary-500 pb-2 flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('logo.png') }}" alt="Gervin Wood" class="h-12 w-auto object-contain flex-shrink-0" style="max-height: 48px;" crossorigin="anonymous">
                        <div>
                            <h3 class="font-black text-base text-neutral-900 m-0 tracking-tight leading-tight uppercase">CÔNG TY TNHH GỖ GERVIN</h3>
                            <p class="text-[11px] text-neutral-600 m-0 mt-1 font-medium leading-tight">
                                Xóm 3, Hưng Thịnh, Hưng Nguyên, Nghệ An • Hotline: <strong class="text-neutral-800 font-bold">0967.181.786</strong> • Web: <strong class="text-primary-600">www.gervinwood.com</strong>
                            </p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        @if($acrylicOrder->isNhapKho() || $acrylicOrder->type === 'min_late' || $acrylicOrder->type === 'Flywood')
                            <div class="inline-block px-2 py-1 bg-primary-50 text-primary-700 border border-primary-200 rounded font-bold text-[11px] uppercase tracking-wider mb-1">
                                LỆNH SẢN XUẤT
                            </div>
                        @else
                            <div class="inline-block px-2 py-1 bg-primary-50 text-primary-700 border border-primary-200 rounded font-bold text-[11px] uppercase tracking-wider mb-1">
                                BÁO GIÁ KIÊM ĐƠN ĐẶT HÀNG
                            </div>
                        @endif
                        <div class="text-lg font-black text-primary-600 font-mono tracking-tight leading-none">{{ $acrylicOrder->order_code }}</div>
                        <div class="text-[11px] text-neutral-500 mt-1 font-medium">Ngày chốt: {{ $orderDateFormatted }}</div>
                    </div>
                </div>

                <!-- Thông tin khách hàng & Đơn hàng (Gọn gàng, tối ưu diện tích) -->
                @php
                    $fullCustomerDisplay = $acrylicOrder->full_customer_display;
                @endphp
                <!-- Thông tin chung (Gọn gàng, tối ưu diện tích in ấn) -->
                <div class="border border-neutral-300 rounded-lg overflow-hidden bg-white shadow-2xs">
                    <div class="bg-neutral-100/80 px-3 py-1 border-b border-neutral-200 flex items-center justify-between">
                        <div class="flex items-center gap-1.5 font-bold text-xs text-neutral-800 uppercase tracking-wide">
                            <iconify-icon icon="lucide:info" class="text-primary-600 text-sm"></iconify-icon>
                            <span>Thông tin chung</span>
                        </div>
                    </div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 11.5px;">
                        <tbody>
                            <tr style="border-bottom: 1px solid #cbd5e1;">
                                <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; background: #f8fafc; font-weight: 700; color: #475569; width: 9%; white-space: nowrap;">Khách hàng:</td>
                                <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; font-weight: 700; color: #0f172a; width: 38%;">{{ $fullCustomerDisplay }}</td>
                                <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; background: #f8fafc; font-weight: 700; color: #475569; width: 11%; white-space: nowrap;">Mã đơn / Loại:</td>
                                <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; font-weight: 700; color: #0f172a; width: 22%;"><span class="font-mono text-primary-700 font-black">{{ $acrylicOrder->order_code }}</span> • {{ $typeLabel }}</td>
                                <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; background: #f8fafc; font-weight: 700; color: #475569; width: 9%; white-space: nowrap;">Ngày chốt:</td>
                                <td style="padding: 3.5px 6px; font-weight: 600; color: #0f172a; width: 11%; white-space: nowrap;">{{ $orderDateFormatted }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #cbd5e1;">
                                <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; background: #f8fafc; font-weight: 700; color: #475569; white-space: nowrap;">Điện thoại:</td>
                                <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; font-weight: 600; color: #0f172a;">{{ $acrylicOrder->phone ?: ($acrylicOrder->customer?->phone ?: '—') }}</td>
                                <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; background: #f8fafc; font-weight: 700; color: #475569; white-space: nowrap;">Hạn giao hàng:</td>
                                <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; font-weight: 700; color: #dc2626;">{{ $deadlineFormatted }} {{ $acrylicOrder->delivery_days ? "({$acrylicOrder->delivery_days} ngày)" : '' }}</td>
                                <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; background: #f8fafc; font-weight: 700; color: #475569; white-space: nowrap;">Trạng thái:</td>
                                <td style="padding: 3.5px 6px; font-weight: 600; color: #0f172a; white-space: nowrap;">{{ $statusText }}</td>
                            </tr>
                            <tr style="{{ ($acrylicOrder->notes || $acrylicOrder->customer_policy) ? 'border-bottom: 1px solid #cbd5e1;' : '' }}">
                                <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; background: #f8fafc; font-weight: 700; color: #475569; white-space: nowrap;">Địa chỉ:</td>
                                <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; color: #0f172a;">{{ $acrylicOrder->address ?: '—' }}</td>
                                <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; background: #f8fafc; font-weight: 700; color: #475569; white-space: nowrap;">Đóng gói:</td>
                                <td colspan="3" style="padding: 3.5px 6px; color: #0f172a;">
                                    {{ $packingInfo['total_packages'] > 0 ? $packingInfo['total_packages'] . ' kiện' : 'Chưa đóng gói' }}
                                </td>
                            </tr>
                            @if($acrylicOrder->notes || $acrylicOrder->customer_policy)
                                @if($acrylicOrder->notes && $acrylicOrder->customer_policy)
                                    <tr>
                                        <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; background: #fffbeb; font-weight: 700; color: #92400e; white-space: nowrap;">Ghi chú:</td>
                                        <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; color: #78350f; font-weight: 600;">{{ $acrylicOrder->notes }}</td>
                                        <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; background: #eff6ff; font-weight: 700; color: #1e40af; white-space: nowrap;">Chính sách:</td>
                                        <td colspan="3" style="padding: 3.5px 6px; color: #1e3a8a; font-weight: 600;">{{ $acrylicOrder->customer_policy }}</td>
                                    </tr>
                                @elseif($acrylicOrder->notes)
                                    <tr>
                                        <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; background: #fffbeb; font-weight: 700; color: #92400e; white-space: nowrap;">Ghi chú:</td>
                                        <td colspan="5" style="padding: 3.5px 6px; color: #78350f; font-weight: 600;">{{ $acrylicOrder->notes }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td style="padding: 3.5px 6px; border-right: 1px solid #cbd5e1; background: #eff6ff; font-weight: 700; color: #1e40af; white-space: nowrap;">Chính sách:</td>
                                        <td colspan="5" style="padding: 3.5px 6px; color: #1e3a8a; font-weight: 600;">{{ $acrylicOrder->customer_policy }}</td>
                                    </tr>
                                @endif
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Danh sách Vật tư & Sản phẩm -->
                @if($acrylicOrder->supplies && $acrylicOrder->supplies->count() > 0)
                    <div class="bg-white border border-neutral-300 rounded-xl p-3 shadow-sm">
                        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-neutral-200">
                            <iconify-icon icon="lucide:package-open" class="text-lg text-primary-600"></iconify-icon>
                            <h6 class="font-black text-sm text-neutral-900 m-0 uppercase tracking-tight">Danh sách Vật tư & Sản phẩm</h6>
                        </div>

                        @foreach($acrylicOrder->supplies as $sIdx => $supply)
                            @php
                                $hasPvc = (stripos($supply->order_supply_code ?? '', 'PVC') !== false) 
                                       || (stripos($supply->supply_name ?? '', 'PVC') !== false);
                                if (!$hasPvc && isset($supply->items)) {
                                    foreach ($supply->items as $it) {
                                        if (stripos($it->product_code ?? '', 'PVC') !== false || stripos($it->product_name ?? '', 'PVC') !== false) {
                                            $hasPvc = true;
                                            break;
                                        }
                                    }
                                }
                                if (!$hasPvc && isset($supply->minLateItems)) {
                                    foreach ($supply->minLateItems as $it) {
                                        if (stripos($it->product_code ?? '', 'PVC') !== false || stripos($it->name ?? '', 'PVC') !== false) {
                                            $hasPvc = true;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            <div class="mb-4">
                                <div class="flex items-center gap-3 mb-2 pb-2 border-b border-neutral-200">
                                    <div class="p-2 {{ $hasPvc ? 'bg-emerald-100 text-emerald-800' : 'bg-primary-100 text-primary-700' }} rounded-lg flex items-center justify-center font-bold">
                                        <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                                    </div>
                                    <span class="font-extrabold text-sm {{ $hasPvc ? 'text-emerald-800' : 'text-neutral-800' }}" {!! $hasPvc ? 'style="color: #166534 !important;"' : '' !!}>{{ $supply->order_supply_code ? '[' . $supply->order_supply_code . '] ' : '' }}{{ $supply->supply_name ?: 'Vật tư ' . ($sIdx + 1) }}</span>
                                    @if($supply->quantity)
                                        <span class="text-xs {{ $hasPvc ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-primary-50 text-primary-700 border-primary-200' }} border px-2 py-1 rounded-full font-bold">SL tấm: {{ floatval($supply->quantity) == intval($supply->quantity) ? number_format($supply->quantity, 0, ',', '.') : number_format($supply->quantity, 2, ',', '.') }}</span>
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
                                                        $isLabor = ($item->product_name ?? '') === 'Công giả dày';
                                                        if (!$isLabor) {
                                                            $sumQty += (float)($item->quantity ?? 0);
                                                            $sumWingArea += (float)($item->wing_area ?? 0);
                                                        }
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

                                                        $grainVal = trim((string)($item->grain_direction ?? ''));
                                                        if ($grainVal === '' || $grainVal === '0' || $grainVal === '0.00' || $grainVal === '0,00' || $grainVal === 'Không vân') {
                                                            $grainText = 'Không vân';
                                                        } elseif ($grainVal === '1' || strcasecmp($grainVal, 'dọc') === 0 || stripos($grainVal, 'vân dọc') !== false) {
                                                            $grainText = 'Vân dọc';
                                                        } elseif ($grainVal === '2' || strcasecmp($grainVal, 'ngang') === 0 || stripos($grainVal, 'vân ngang') !== false) {
                                                            $grainText = 'Vân ngang';
                                                        } else {
                                                            $grainText = $grainVal;
                                                        }
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
    $isNarrow = ($wVal > 0 && $wVal <= 55 && $prodName !== 'Công giả dày');
    $hasCnc = !empty($item->vertical_grain_cnc) || (!empty($item->notes) && (stripos($item->notes, 'kính') !== false || stripos($item->notes, 'cnc') !== false));
@endphp
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; {{ $isNarrow ? 'color: #ef4444; background-color: #fef2f2; font-weight: 700;' : 'color: #0f172a;' }} font-size: 13px; white-space: nowrap;">{{ $item->width ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $item->quantity }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $bevelVal }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $grainText }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #2563eb; font-size: 13px; font-weight: 600; white-space: nowrap;">{{ ((float)($item->wing_area ?? 0) > 0) ? number_format($item->wing_area, 2, ',', '.') : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ ((float)($item->molding_length ?? 0) > 0) ? number_format($item->molding_length, 2, ',', '.') : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: right; color: #0f172a; font-size: 13px; font-weight: 600; white-space: nowrap;">{{ $item->unit_price ? number_format($item->unit_price, 0, ',', '.') . ' đ' : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: right; color: #059669; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $item->total_price ? number_format($item->total_price, 0, ',', '.') . ' đ' : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: left; {{ $hasCnc ? 'color: #b45309; background-color: #fef9c3; font-weight: 700;' : 'color: #64748b;' }} font-size: 13px; white-space: nowrap;">{{ $item->notes ?: ($item->vertical_grain_cnc ?: '—') }}</td>
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
    $isNarrow = ($wVal > 0 && $wVal <= 55 && $prodName !== 'Công giả dày');
    $hasCnc = ((float)($item->cnc ?? 0) > 0) || (!empty($item->notes) && (stripos($item->notes, 'kính') !== false || stripos($item->notes, 'cnc') !== false));
@endphp
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; {{ $isNarrow ? 'color: #ef4444; background-color: #fef2f2; font-weight: 700;' : 'color: #0f172a;' }} font-size: 13px; white-space: nowrap;">{{ $sizes['width'] ?? '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $item->quantity }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $edgeTxt }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->bevel ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ ((float)($item->straight_paste_length ?? 0) > 0) ? $item->straight_paste_length : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ ((float)($item->beveled_length ?? 0) > 0) ? $item->beveled_length : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ ((float)($item->vat_moi_length ?? 0) > 0) ? $item->vat_moi_length : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ ((float)($item->ban_rong_40_59 ?? 0) > 0) ? $item->ban_rong_40_59 : (((float)($item->ban_rong_17_39 ?? 0) > 0) ? $item->ban_rong_17_39 : (((float)($item->ban_rong_25_35 ?? 0) > 0) ? $item->ban_rong_25_35 : '—')) }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ ((float)($item->beveled_handle ?? 0) > 0) ? $item->beveled_handle : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; {{ ((float)($item->cnc ?? 0) > 0) ? 'color: #b45309; background-color: #fef9c3; font-weight: 700;' : 'color: #0f172a;' }} font-size: 13px; white-space: nowrap;">{{ ((float)($item->cnc ?? 0) > 0) ? $item->cnc : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->direction ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: left; {{ $hasCnc ? 'color: #b45309; background-color: #fef9c3; font-weight: 700;' : 'color: #64748b;' }} font-size: 13px; white-space: nowrap;">{{ $item->notes ?: '—' }}</td>
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
    $isNarrow = ($wVal > 0 && $wVal <= 55 && $prodName !== 'Công giả dày');
    $hasCnc = (!empty($item->notes) && (stripos($item->notes, 'kính') !== false || stripos($item->notes, 'cnc') !== false));
@endphp
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; {{ $isNarrow ? 'color: #ef4444; background-color: #fef2f2; font-weight: 700;' : 'color: #0f172a;' }} font-size: 13px; white-space: nowrap;">{{ $item->width ?: '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $item->unit ?: 'cánh' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $item->wing_quantity }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; color: #2563eb; font-size: 13px; font-weight: 600; white-space: nowrap;">{{ ((float)($item->area_m2 ?? 0) > 0) ? number_format($item->area_m2, 2, ',', '.') : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: right; color: #0f172a; font-size: 13px; font-weight: 600; white-space: nowrap;">{{ $item->unit_price ? number_format($item->unit_price, 0, ',', '.') . ' đ' : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: right; color: #059669; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ $item->total_price ? number_format($item->total_price, 0, ',', '.') . ' đ' : '—' }}</td>
                                                        <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: left; {{ $hasCnc ? 'color: #b45309; background-color: #fef9c3; font-weight: 700;' : 'color: #64748b;' }} font-size: 13px; white-space: nowrap;">{{ $item->notes ?: '—' }}</td>
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
                @if($previewPaymentDetails && $previewPaymentDetails->count() > 0)
                    <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-neutral-100">
                            <iconify-icon icon="lucide:receipt" class="text-lg text-primary-500"></iconify-icon>
                            <h6 class="font-bold text-sm text-neutral-800 m-0">Chi tiết hóa đơn dịch vụ & Vật tư bổ sung</h6>
                        </div>
                        <div class="w-full rounded-lg border border-neutral-200 overflow-hidden">
                            <table class="w-full" style="border-collapse: collapse; width: 100%; table-layout: fixed;">
                                <thead>
                                    <tr style="background-color: #f1f5f9;">
                                        <th style="border: 1.5px solid #cbd5e1; padding: 9px 4px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12px; text-transform: uppercase; width: 42px; white-space: nowrap;">STT</th>
                                        <th style="border: 1.5px solid #cbd5e1; padding: 9px 10px; text-align: left; color: #0f172a; font-weight: 700; font-size: 12px; text-transform: uppercase;">Tên nội dung / Dịch vụ / Vật tư</th>
                                        <th style="border: 1.5px solid #cbd5e1; padding: 9px 4px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12px; text-transform: uppercase; width: 60px; white-space: nowrap;">Đơn vị</th>
                                        <th style="border: 1.5px solid #cbd5e1; padding: 9px 4px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12px; text-transform: uppercase; width: 90px; white-space: nowrap;">Quy cách</th>
                                        <th style="border: 1.5px solid #cbd5e1; padding: 9px 4px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12px; text-transform: uppercase; width: 70px; white-space: nowrap;">Số lượng</th>
                                        <th style="border: 1.5px solid #cbd5e1; padding: 9px 6px; text-align: right; color: #0f172a; font-weight: 700; font-size: 12px; text-transform: uppercase; width: 105px; white-space: nowrap;">Đơn giá</th>
                                        <th style="border: 1.5px solid #cbd5e1; padding: 9px 6px; text-align: right; color: #0f172a; font-weight: 700; font-size: 12px; text-transform: uppercase; width: 120px; white-space: nowrap;">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sumPayTotal = 0; @endphp
                                    @foreach($previewPaymentDetails as $pIdx => $pDetail)
                                        @php
                                            $pName = is_object($pDetail) ? ($pDetail->name ?? '') : ($pDetail['name'] ?? '');
                                            $pUnit = is_object($pDetail) ? ($pDetail->unit ?? '') : ($pDetail['unit'] ?? '');
                                            $pLen = is_object($pDetail) ? ($pDetail->length ?? null) : ($pDetail['length'] ?? null);
                                            $pWid = is_object($pDetail) ? ($pDetail->width ?? null) : ($pDetail['width'] ?? null);
                                            $dimStr = ($pLen || $pWid) ? (($pLen ? number_format(floatval($pLen), 0, ',', '.') : '—') . ' x ' . ($pWid ? number_format(floatval($pWid), 0, ',', '.') : '—')) : '—';
                                            $pQty = floatval(is_object($pDetail) ? ($pDetail->quantity ?? 0) : ($pDetail['quantity'] ?? 0));
                                            $pPrice = floatval(is_object($pDetail) ? ($pDetail->price ?? 0) : ($pDetail['price'] ?? 0));
                                            $pTotal = floatval(is_object($pDetail) ? ($pDetail->total ?? 0) : ($pDetail['total'] ?? 0));
                                            $sumPayTotal += $pTotal;
                                        @endphp
                                        <tr style="border-bottom: 1px solid #cbd5e1;">
                                            <td style="border: 1px solid #cbd5e1; padding: 7px 4px; text-align: center; color: #64748b; font-size: 13px; white-space: nowrap;">{{ $pIdx + 1 }}</td>
                                            <td style="border: 1px solid #cbd5e1; padding: 7px 10px; text-align: left; color: #0f172a; font-size: 13px; font-weight: 600; line-height: 1.45; word-break: break-word; overflow-wrap: anywhere; white-space: normal;">{{ $pName }}</td>
                                            <td style="border: 1px solid #cbd5e1; padding: 7px 4px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $pUnit ?: '—' }}</td>
                                            <td style="border: 1px solid #cbd5e1; padding: 7px 4px; text-align: center; color: #0f172a; font-size: 13px; white-space: nowrap;">{{ $dimStr }}</td>
                                            <td style="border: 1px solid #cbd5e1; padding: 7px 4px; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ floatval($pQty) == intval($pQty) ? number_format($pQty, 0, ',', '.') : number_format($pQty, 2, ',', '.') }}</td>
                                            <td style="border: 1px solid #cbd5e1; padding: 7px 6px; text-align: right; color: #0f172a; font-size: 13px; font-weight: 600; white-space: nowrap;">{{ number_format($pPrice, 0, ',', '.') }} đ</td>
                                            <td style="border: 1px solid #cbd5e1; padding: 7px 6px; text-align: right; color: #059669; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ number_format($pTotal, 0, ',', '.') }} đ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background-color: #f8fafc; font-weight: 700; border-top: 2px solid #94a3b8;">
                                        <td style="border: 1px solid #cbd5e1; padding: 8px 4px; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700; white-space: nowrap;">TỔNG</td>
                                        <td colspan="5" style="border: 1px solid #cbd5e1;"></td>
                                        <td style="border: 1px solid #cbd5e1; padding: 8px 6px; text-align: right; color: #059669; font-size: 13px; font-weight: 700; white-space: nowrap;">{{ number_format(round($sumPayTotal), 0, ',', '.') }} đ</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Tóm tắt đơn hàng (Gọn gàng, hẹp) -->
                <div class="bg-neutral-50 border border-neutral-300 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <iconify-icon icon="lucide:receipt-text" class="text-base text-primary-600"></iconify-icon>
                            <span class="font-bold text-xs text-neutral-800 uppercase tracking-wide">Tóm tắt đơn hàng</span>
                        </div>
                        @if($acrylicOrder->isNhapKho())
                            <span class="text-xs font-bold text-primary-700 bg-primary-100 px-2 py-1 rounded border border-primary-200">
                                Lệnh sản xuất nội bộ (Không tính tiền)
                            </span>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mt-2">
                        <div class="text-center py-1 px-2 bg-white rounded border border-neutral-200">
                            <div class="text-[11px] text-neutral-500 font-medium">Số sản phẩm</div>
                            <div class="text-base font-bold text-neutral-800">{{ $totalItems }}</div>
                        </div>
                        <div class="text-center py-1 px-2 bg-white rounded border border-neutral-200">
                            <div class="text-[11px] text-neutral-500 font-medium">Tổng số tấm</div>
                            <div class="text-base font-bold text-neutral-800">{{ $totalSheets }}</div>
                        </div>
                        <div class="text-center py-1 px-2 bg-white rounded border border-neutral-200">
                            <div class="text-[11px] text-neutral-500 font-medium">Tổng diện tích</div>
                            <div class="text-base font-bold text-blue-600">{{ number_format($totalArea, 2, ',', '.') }} m²</div>
                        </div>
                        <div class="text-center py-1 px-2 bg-white rounded border border-neutral-200">
                            <div class="text-[11px] text-neutral-500 font-medium">Tổng tiền hàng</div>
                            <div class="text-base font-bold text-neutral-800">{{ $acrylicOrder->isNhapKho() ? '0 đ' : number_format(round($subTotal, -3), 0, ',', '.') . ' đ' }}</div>
                        </div>
                    </div>
                    @if(!$acrylicOrder->isNhapKho())
                        @if($acrylicOrder->discount_percent > 0)
                            <div class="mt-1 text-xs text-right font-medium text-danger-600">
                                Chiết khấu ({{ (float)$acrylicOrder->discount_percent }}%): -{{ number_format(round($discountAmt, -3), 0, ',', '.') }} đ
                            </div>
                        @endif
                        @if($acrylicOrder->vat_percent > 0)
                            <div class="mt-1 text-xs text-right font-medium text-neutral-700">
                                VAT ({{ (float)$acrylicOrder->vat_percent }}%): +{{ number_format(round($vatAmt, -3), 0, ',', '.') }} đ
                            </div>
                        @endif
                        @if(($acrylicOrder->additional_discount_percent ?? 0) > 0)
                            <div class="mt-1 text-xs text-right font-medium text-danger-600">
                                Chiết khấu thêm ({{ (float)$acrylicOrder->additional_discount_percent }}%): -{{ number_format(round($additionalDiscountAmt, -3), 0, ',', '.') }} đ
                            </div>
                        @endif
                        <div class="mt-2 pt-2 border-t border-neutral-200 flex items-center justify-between">
                            <span class="font-bold text-xs text-neutral-800">Tổng thanh toán:</span>
                            <span class="text-lg font-extrabold text-primary-700">{{ number_format(round($acrylicOrder->total_amount, -3), 0, ',', '.') }} VNĐ</span>
                        </div>

                        @if($orderPaidAmt > 0)
                            <div class="mt-2 pt-2 border-t border-dashed border-neutral-200 flex items-center justify-between text-xs">
                                <span class="text-neutral-600 font-medium">Đã thanh toán:</span>
                                <span class="font-bold text-emerald-600 font-mono">{{ number_format(round($orderPaidAmt, -3), 0, ',', '.') }} đ</span>
                            </div>
                            <div class="mt-1 flex items-center justify-between text-xs">
                                <span class="text-neutral-600 font-medium">Còn lại:</span>
                                <span class="font-bold text-rose-600 font-mono">{{ number_format(round($orderRemainingAmt, -3), 0, ',', '.') }} đ</span>
                            </div>
                        @endif

                        {{-- Khối thông tin công nợ khách hàng --}}
                        @if(!empty($debtInfo))
                            <div class="mt-2 pt-2 border-t border-neutral-200">
                                <div class="text-[11px] font-bold text-neutral-700 mb-1 flex items-center justify-between">
                                    <span class="flex items-center gap-2 text-primary-700">
                                        <iconify-icon icon="lucide:wallet" class="text-sm text-primary-600"></iconify-icon>
                                        <span>Công nợ khách hàng</span>
                                    </span>
                                    <span class="text-[10px] font-normal text-neutral-400 italic">(đồng bộ với file excel)</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <div class="bg-white py-1 px-2 rounded border border-neutral-200 flex items-center justify-between text-xs">
                                        <span class="text-neutral-500 text-[11px]">Công nợ cũ:</span>
                                        <span class="font-bold text-neutral-800 font-mono">
                                            {{ number_format(round($debtInfo['old_debt'], -3), 0, ',', '.') }} đ
                                        </span>
                                    </div>
                                    <div class="bg-white py-1 px-2 rounded border border-emerald-200 bg-emerald-50/40 flex items-center justify-between text-xs">
                                        <span class="text-emerald-700 text-[11px]">Đã thanh toán:</span>
                                        <span class="font-bold text-emerald-600 font-mono">
                                            {{ number_format(round($debtInfo['this_order_paid'], -3), 0, ',', '.') }} đ
                                        </span>
                                    </div>
                                    <div class="bg-white py-1 px-2 rounded border border-rose-200 bg-rose-50/50 flex items-center justify-between text-xs">
                                        <span class="text-rose-700 text-[11px] font-bold">Tổng nợ:</span>
                                        <span class="font-bold text-rose-600 font-mono">
                                            {{ number_format(round($debtInfo['total_combined_debt'], -3), 0, ',', '.') }} đ
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Khối Lưu ý, Xác nhận chữ ký 3 bên & Điều khoản báo giá (Gọn gàng) -->
                <div class="mt-3 pt-3 border-t-2 border-neutral-300 space-y-2 text-neutral-800">
                    <!-- 1. Lưu ý nhận hàng -->
                    <div class="p-2 rounded-lg bg-amber-50/80 border border-amber-200 text-xs text-amber-950 leading-relaxed">
                        <div class="font-bold text-xs mb-1 text-amber-900 flex items-center gap-1">
                            <iconify-icon icon="lucide:alert-circle" class="text-sm text-amber-600"></iconify-icon>
                            <span>Lưu ý nhận hàng:</span>
                        </div>
                        <p class="m-0 leading-normal">+ Khách hàng vui lòng kiểm tra và xác nhận đã nhận đầy đủ số lượng, tình trạng bên ngoài không bị trầy xước hư hỏng trước khi nhận hàng. Sau thời điểm giao xong hàng bên bán không chịu trách nhiệm về thiếu sót hoặc hư hỏng ngoại quan.</p>
                    </div>

                    <!-- 2. Khu vực chữ ký & Xét duyệt điện tử -->
                    <div class="grid grid-cols-3 gap-3 text-center pt-1">
                        <!-- Cột 1: Bên mua -->
                        <div class="p-2 rounded-lg border border-neutral-300 bg-neutral-50/60 flex flex-col justify-between" style="min-height: 105px;">
                            <div>
                                <div class="font-bold text-xs uppercase tracking-wide text-neutral-900">Bên mua hàng</div>
                                <div class="text-[11px] text-neutral-500 mt-1 italic leading-tight">
                                    Đã nhận đủ số lượng & nguyên vẹn
                                </div>
                            </div>
                            <div class="text-[11px] text-neutral-400 italic pt-3 pb-1">(Ký, ghi rõ họ tên)</div>
                        </div>

                        <!-- Cột 2: Kỹ thuật duyệt -->
                        <div class="p-2 rounded-lg border border-neutral-300 bg-neutral-50/60 flex flex-col justify-between" style="min-height: 105px;">
                            <div>
                                <div class="font-bold text-xs uppercase tracking-wide text-neutral-900">Kỹ thuật duyệt</div>
                            </div>
                            @if($acrylicOrder->technical_approved_by)
                                <div class="my-auto py-1 flex flex-col items-center justify-center">
                                    <div class="px-3 py-1 rounded-lg text-center inline-block" style="border: 1.5px solid #2563eb; background-color: #eff6ff;">
                                        <div class="text-[10px] font-black uppercase tracking-wider text-blue-700 flex items-center justify-center gap-1">
                                            <iconify-icon icon="lucide:check-circle-2" class="text-xs text-blue-600"></iconify-icon>
                                            <span>ĐÃ DUYỆT SX</span>
                                        </div>
                                        <div class="text-xs font-bold text-neutral-900 mt-1 uppercase">
                                            {{ $acrylicOrder->technicalApprover?->name ?? 'Kỹ thuật' }}
                                        </div>
                                        @if($acrylicOrder->technical_approved_at)
                                            <div class="text-[9px] text-neutral-500 font-mono">
                                                {{ $acrylicOrder->technical_approved_at->format('H:i d/m/Y') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="my-auto py-2 flex flex-col items-center justify-center">
                                    <span class="text-xs text-neutral-400 italic">(Chờ duyệt)</span>
                                </div>
                            @endif
                            <div class="text-[10px] text-neutral-400 pb-1">Xác nhận duyệt hệ thống</div>
                        </div>

                        <!-- Cột 3: Kế toán duyệt -->
                        <div class="p-2 rounded-lg border border-neutral-300 bg-neutral-50/60 flex flex-col justify-between" style="min-height: 105px;">
                            <div>
                                <div class="font-bold text-xs uppercase tracking-wide text-neutral-900">Kế toán duyệt</div>
                            </div>
                            @if($acrylicOrder->accountant_approved_by)
                                <div class="my-auto py-1 flex flex-col items-center justify-center">
                                    <div class="px-3 py-1 rounded-lg text-center inline-block" style="border: 1.5px solid #059669; background-color: #ecfdf5;">
                                        <div class="text-[10px] font-black uppercase tracking-wider text-emerald-700 flex items-center justify-center gap-1">
                                            <iconify-icon icon="lucide:check-circle-2" class="text-xs text-emerald-600"></iconify-icon>
                                            <span>KẾ TOÁN ĐÃ DUYỆT</span>
                                        </div>
                                        <div class="text-xs font-bold text-neutral-900 mt-1 uppercase">
                                            {{ $acrylicOrder->accountantApprover?->name ?? 'Kế toán' }}
                                        </div>
                                        @if($acrylicOrder->accountant_approved_at)
                                            <div class="text-[9px] text-neutral-500 font-mono">
                                                {{ $acrylicOrder->accountant_approved_at->format('H:i d/m/Y') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="my-auto py-2 flex flex-col items-center justify-center">
                                    <span class="text-xs text-neutral-400 italic">(Chờ duyệt)</span>
                                </div>
                            @endif
                            <div class="text-[10px] text-neutral-400 pb-1">Xác nhận duyệt hệ thống</div>
                        </div>
                    </div>

                    <!-- 3. Các điều khoản thanh toán & bảo hành (5 điều ngắn gọn) -->
                    <div class="pt-2 text-[11px] space-y-1 text-neutral-700 leading-normal border-t border-neutral-200">
                        <div class="flex items-start gap-2">
                            <span class="font-bold text-neutral-900 w-3 shrink-0 text-center">1.</span>
                            <span>Đơn giá trên chưa bao gồm VAT. Đúng sai kích thước sản xuất ±1 mm.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="font-bold text-neutral-900 w-3 shrink-0 text-center">2.</span>
                            <span>Ứng trước 50% tiền đơn hàng gia công, thanh toán 50% còn lại trước khi lấy hàng tại kho.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="font-bold text-neutral-900 w-3 shrink-0 text-center">3.</span>
                            <span>Khi mua tấm Quý khách vui lòng thanh toán 100% tiền hàng trước khi xuất kho bên bán.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="font-bold text-neutral-900 w-3 shrink-0 text-center">4.</span>
                            <span>Thanh toán tiền mặt hoặc chuyển khoản theo thông tin ngân hàng của Công ty Gỗ Gervin.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="font-bold text-neutral-900 w-3 shrink-0 text-center">5.</span>
                            <span>Bảo hành cong vênh, bong tróc 12 tháng, bảo trì vĩnh viễn (không bảo hành ngập nước và mối mọt).</span>
                        </div>
                    </div>
                </div>

                <!-- Footer trên ảnh khi xuất -->
                <div class="pt-4 border-t border-neutral-200 flex items-center justify-between text-xs text-neutral-400">
                    <span>Xưởng Nội Thất Gervin</span>
                    <span>Phiếu xuất lúc: {{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <!-- Mục Hình ảnh đã tải (Đặt ngoài #preview-order-capture-area để KHÔNG chụp vào ảnh gửi Zalo) -->
            <div id="preview-order-attachments-section" class="bg-white p-4 mt-3 rounded-xl border border-neutral-300 shadow-sm w-full mx-auto" style="min-width: 1050px; max-width: 1240px; {{ count($orderAttachments) == 0 ? 'display:none;' : '' }}">
                <div class="flex items-center justify-between border-b border-neutral-200 pb-3 mb-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
                            <iconify-icon icon="lucide:image" class="text-lg"></iconify-icon>
                        </span>
                        <div>
                            <h6 class="font-bold text-sm text-neutral-800 m-0">Hình ảnh đã tải (<span id="preview-attachments-count-text">{{ count($orderAttachments) }}</span> ảnh)</h6>
                            <p class="text-[11px] text-neutral-400 m-0">Bấm ảnh để mở · Kéo chuột di chuyển tự do · <strong>Ctrl + Lăn chuột</strong> phóng to/thu nhỏ · <strong>Nhấp đúp</strong> về gốc · <strong>Ctrl + Alt</strong> ẩn/hiện nhanh</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-semibold text-neutral-500 bg-neutral-100 px-2.5 py-1 rounded-full border border-neutral-200">
                        Không chụp vào ảnh gửi Zalo
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3" id="preview-attachments-grid">
                    @foreach($orderAttachments as $idx => $imgFile)
                        @php
                            $fname = basename($imgFile);
                            $imgUrl = route('orders.image', ['filename' => $fname]);
                        @endphp
                        <div class="group relative border border-neutral-200 hover:border-primary-400 rounded-xl p-2 bg-neutral-50/50 hover:bg-neutral-50 transition-all cursor-pointer shadow-xs hover:shadow-md existing-preview-attachment"
                             data-filename="{{ $fname }}"
                             onclick="openPreviewImageModal('preview-img-modal-{{ $idx }}')">
                            <div class="w-full aspect-square rounded-lg overflow-hidden bg-white border border-neutral-200 flex items-center justify-center relative">
                                <img src="{{ $imgUrl }}" alt="{{ $fname }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200" loading="lazy">
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/25 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100">
                                    <span class="w-8 h-8 rounded-full bg-white text-primary-600 flex items-center justify-center shadow-md">
                                        <iconify-icon icon="lucide:maximize-2" class="text-base"></iconify-icon>
                                    </span>
                                </div>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-1">
                                <p class="text-[11px] font-semibold text-neutral-700 truncate m-0 flex-1" title="{{ $fname }}">{{ $fname }}</p>
                                <span class="text-[11px] text-primary-600 font-bold shrink-0 hover:underline">Xem</span>
                            </div>
                        </div>

                        {{-- Modal Popup xem ảnh trần có thể kéo thả tự do & phóng to bằng Ctrl + lăn chuột --}}
                        <div id="preview-img-modal-{{ $idx }}"
                             class="preview-image-draggable-modal"
                             style="display:none; position:fixed; inset:0; z-index:100000005 !important; pointer-events:none;">
                            <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; padding:1rem; pointer-events:none;">
                                <div data-modal-content style="position:relative; width:fit-content; pointer-events:auto;">
                                    <div class="relative preview-drag-handle cursor-grab select-none" style="filter: drop-shadow(0 25px 40px rgba(0,0,0,0.5));">
                                        {{-- Nút X đóng ở góc phải trên --}}
                                        <button type="button"
                                                onclick="closePreviewImageModal('preview-img-modal-{{ $idx }}')"
                                                class="absolute -top-3 -right-3 z-30 w-8 h-8 bg-white rounded-full shadow-xl flex items-center justify-center text-neutral-600 hover:text-red-600 hover:bg-red-50 transition-colors border border-neutral-300 cursor-pointer"
                                                title="Đóng ảnh (hoặc phím Esc)">
                                            <iconify-icon icon="lucide:x" class="text-base"></iconify-icon>
                                        </button>

                                        {{-- Tên file ảnh góc trái trên --}}
                                        <div class="absolute top-2 left-2 z-20 bg-black/65 backdrop-blur-xs text-white text-[11px] font-medium px-2.5 py-1 rounded-md truncate max-w-[320px] pointer-events-none">
                                            {{ $fname }}
                                        </div>

                                        {{-- Ảnh trần - có thể kéo di chuyển tự do bằng chuột & Ctrl + lăn chuột phóng to --}}
                                        <img src="{{ $imgUrl }}"
                                             class="modal-image-viewer rounded-xl block border-2 border-white bg-white"
                                             style="max-width:85vw; max-height:82vh; object-fit:contain; pointer-events:auto; user-select:none; -webkit-user-drag:none;"
                                             draggable="false"
                                             title="Kéo chuột để di chuyển • Ctrl + Lăn chuột để phóng to/thu nhỏ • Nhấp đúp để reset về ban đầu"
                                             alt="{{ $fname }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
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
                <button type="button" onclick="printPreviewOrder()" class="btn text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center gap-2 hover:opacity-95" style="background:#059669; border:none;" title="In đơn hàng ra giấy hoặc xuất PDF (khổ ngang A4)">
                    <iconify-icon icon="lucide:printer" class="text-base"></iconify-icon>
                    <span>In đơn (khổ ngang)</span>
                </button>
                <button type="button" onclick="copyPreviewOrderImage()" class="btn text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center gap-2 hover:opacity-95" style="background:#0068FF; border:none;" title="Copy ảnh để dán Ctrl + V vào Zalo">
                    <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                    <span>Copy ảnh gửi Zalo</span>
                </button>
                @if(!empty($isFromForm))
                    @if(!isset($acrylicOrder) || (isset($isDraftCreate) && $isDraftCreate) || $acrylicOrder->status === 'draft')
                        <button type="button" onclick="closePreviewOrder(); if (typeof closeOrderSuppliesPopup === 'function') closeOrderSuppliesPopup(); document.getElementById('order-form-action').value = 'draft'; document.getElementById('order-form').requestSubmit();" class="btn bg-amber-500 hover:bg-amber-600 text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center gap-2">
                            <iconify-icon icon="lucide:file-text" class="text-base"></iconify-icon>
                            <span>Lưu nháp</span>
                        </button>
                    @endif
                    <button type="button" onclick="closePreviewOrder(); if (typeof closeOrderSuppliesPopup === 'function') closeOrderSuppliesPopup(); document.getElementById('order-form-action').value = 'save'; document.getElementById('order-form').requestSubmit();" class="btn btn-primary px-6 py-2 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center gap-2">
                        <iconify-icon icon="lucide:save" class="text-base"></iconify-icon>
                        <span>{{ isset($acrylicOrder) && empty($isDraftCreate) && $acrylicOrder->status !== 'draft' ? 'Cập nhật đơn hàng' : 'Lưu đơn hàng' }}</span>
                    </button>
                @else
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

    let previewImgTopZIndex = 100000010;

    function openPreviewModal() {
        let modal = document.getElementById('preview-order-modal');
        if (modal) {
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            syncFormAttachmentsToPreview();
        }
    }

    if (typeof window.previewOrder !== 'function' || !window.isOrderFormPage) {
        window.previewOrder = openPreviewModal;
    }

    function openPreviewImageModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        previewImgTopZIndex++;
        el.style.zIndex = previewImgTopZIndex;
        el.style.display = 'block';
        const modalBody = el.querySelector('[data-modal-content]');
        if (modalBody) {
            modalBody.style.transform = '';
            modalBody.removeAttribute('data-drag-x');
            modalBody.removeAttribute('data-drag-y');
        }
        // Reset kích thước ảnh về mặc định khi mở lại
        el.querySelectorAll('.modal-image-viewer').forEach(function(img) {
            img.style.width = '';
            img.style.maxWidth = '85vw';
            img.style.maxHeight = '82vh';
        });
    }

    function closePreviewImageModal(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    }

    function closePreviewOrder() {
        const modal = document.getElementById('preview-order-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
        document.querySelectorAll('.preview-image-draggable-modal').forEach(function(el) {
            el.style.display = 'none';
        });
    }

    // Đồng bộ ảnh đã tải & ảnh mới từ form chỉnh sửa sang bản xem trước (nếu đang ở trang form /orders/{id}/edit)
    function syncFormAttachmentsToPreview() {
        if (!window.isOrderFormPage) return;

        // 1. Ẩn các ảnh cũ đã bị xóa trên form
        const deleteInput = document.getElementById('delete-attachments');
        let deletedFiles = [];
        if (deleteInput && deleteInput.value) {
            try {
                deletedFiles = JSON.parse(deleteInput.value);
                if (!Array.isArray(deletedFiles)) deletedFiles = [];
            } catch (e) {
                deletedFiles = [];
            }
        }
        if (deletedFiles.length > 0) {
            document.querySelectorAll('#preview-attachments-grid .existing-preview-attachment').forEach(card => {
                const fname = card.getAttribute('data-filename');
                if (fname && deletedFiles.some(d => d.includes(fname) || fname.includes(d))) {
                    card.style.display = 'none';
                }
            });
        }

        // 2. Bổ sung các ảnh mới chọn (chưa bấm lưu vào DB) vào mục xem trước
        const newPreviewItems = document.querySelectorAll('#new-attachments-preview > div');
        const previewGrid = document.getElementById('preview-attachments-grid');
        if (previewGrid && newPreviewItems.length > 0) {
            newPreviewItems.forEach((item, idx) => {
                const img = item.querySelector('img');
                const nameEl = item.querySelector('p');
                if (!img || !img.src) return;
                const imgSrc = img.src;
                const fileName = nameEl ? nameEl.textContent.trim() : `Ảnh mới ${idx + 1}`;
                const newModalId = `preview-new-img-modal-${idx}`;

                if (!document.getElementById(newModalId)) {
                    // Card thumbnail ảnh mới
                    const card = document.createElement('div');
                    card.className = 'group relative border border-emerald-300 hover:border-emerald-500 rounded-xl p-2 bg-emerald-50/30 hover:bg-emerald-50 transition-all cursor-pointer shadow-xs hover:shadow-md new-preview-attachment';
                    card.onclick = () => openPreviewImageModal(newModalId);
                    card.innerHTML = `
                        <div class="w-full aspect-square rounded-lg overflow-hidden bg-white border border-emerald-200 flex items-center justify-center relative">
                            <img src="${imgSrc}" alt="${fileName}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200" loading="lazy">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/25 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100">
                                <span class="w-8 h-8 rounded-full bg-white text-emerald-600 flex items-center justify-center shadow-md">
                                    <iconify-icon icon="lucide:maximize-2" class="text-base"></iconify-icon>
                                </span>
                            </div>
                            <span class="absolute top-1 left-1 bg-emerald-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow-xs">Mới</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between gap-1">
                            <p class="text-[11px] font-semibold text-neutral-700 truncate m-0 flex-1" title="${fileName}">${fileName}</p>
                            <span class="text-[11px] text-emerald-600 font-bold shrink-0 hover:underline">Xem</span>
                        </div>
                    `;
                    previewGrid.appendChild(card);

                    // Modal popup trần có thể kéo thả cho ảnh mới
                    const modalDiv = document.createElement('div');
                    modalDiv.id = newModalId;
                    modalDiv.className = 'preview-image-draggable-modal';
                    modalDiv.style.cssText = 'display:none; position:fixed; inset:0; z-index:100000005 !important; pointer-events:none;';
                    modalDiv.innerHTML = `
                        <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; padding:1rem; pointer-events:none;">
                            <div data-modal-content style="position:relative; width:fit-content; pointer-events:auto;">
                                <div class="relative preview-drag-handle cursor-grab select-none" style="filter: drop-shadow(0 25px 40px rgba(0,0,0,0.5));">
                                    <button type="button"
                                            onclick="closePreviewImageModal('${newModalId}')"
                                            class="absolute -top-3 -right-3 z-30 w-8 h-8 bg-white rounded-full shadow-xl flex items-center justify-center text-neutral-600 hover:text-red-600 hover:bg-red-50 transition-colors border border-neutral-300 cursor-pointer"
                                            title="Đóng ảnh (hoặc phím Esc)">
                                        <iconify-icon icon="lucide:x" class="text-base"></iconify-icon>
                                    </button>
                                    <div class="absolute top-2 left-2 z-20 bg-black/65 backdrop-blur-xs text-white text-[11px] font-medium px-2.5 py-1 rounded-md truncate max-w-[320px] pointer-events-none flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        <span>${fileName}</span>
                                    </div>
                                    <img src="${imgSrc}"
                                         class="modal-image-viewer rounded-xl block border-2 border-white bg-white"
                                         style="max-width:85vw; max-height:82vh; object-fit:contain; pointer-events:auto; user-select:none; -webkit-user-drag:none;"
                                         draggable="false"
                                         title="Kéo chuột để di chuyển • Ctrl + Lăn chuột để phóng to/thu nhỏ • Nhấp đúp để reset về ban đầu"
                                         alt="${fileName}">
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modalDiv);
                }
            });
        }

        // 3. Cập nhật lại số lượng ảnh thực tế hiển thị
        const visibleCards = Array.from(document.querySelectorAll('#preview-attachments-grid > div')).filter(el => el.style.display !== 'none');
        const totalCount = visibleCards.length;

        const section = document.getElementById('preview-order-attachments-section');
        const headerBtn = document.getElementById('preview-header-attachments-btn');
        const countBadge = document.getElementById('preview-attachments-count-text');
        const headerCount = document.getElementById('preview-header-attachments-count');

        if (countBadge) countBadge.textContent = totalCount;
        if (headerCount) headerCount.textContent = totalCount;

        if (totalCount > 0) {
            if (section) section.style.display = 'block';
            if (headerBtn) headerBtn.style.display = 'inline-flex';
        } else {
            if (section) section.style.display = 'none';
            if (headerBtn) headerBtn.style.display = 'none';
        }
    }

    // Đóng bằng phím Escape (ưu tiên đóng ảnh trước, sau đó đóng modal)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openImgModals = Array.from(document.querySelectorAll('.preview-image-draggable-modal')).filter(el => el.style.display !== 'none');
            if (openImgModals.length > 0) {
                openImgModals.forEach(el => el.style.display = 'none');
                return;
            }
            closePreviewOrder();
        }
    });

    // Kéo thả di chuyển tự do ảnh trần xem trước
    document.addEventListener('mousedown', function(e) {
        // Nâng z-index lên trên cùng khi người dùng bấm vào bất kỳ cửa sổ ảnh nào
        const draggableModal = e.target.closest('.preview-image-draggable-modal');
        if (draggableModal) {
            previewImgTopZIndex++;
            draggableModal.style.zIndex = previewImgTopZIndex;
        }

        const handle = e.target.closest('.preview-drag-handle');
        if (!handle) return;
        if (e.target.closest('button')) return; // Bỏ qua nếu bấm nút X

        e.preventDefault();
        const modalBody = handle.closest('[data-modal-content]') || handle.parentElement;
        if (!modalBody) return;

        handle.style.cursor = 'grabbing';
        const startX = e.clientX;
        const startY = e.clientY;
        const currentX = parseFloat(modalBody.getAttribute('data-drag-x')) || 0;
        const currentY = parseFloat(modalBody.getAttribute('data-drag-y')) || 0;

        function onMouseMove(moveEvent) {
            const dx = moveEvent.clientX - startX;
            const dy = moveEvent.clientY - startY;
            const newX = currentX + dx;
            const newY = currentY + dy;
            modalBody.style.transform = `translate(${newX}px, ${newY}px)`;
            modalBody.setAttribute('data-drag-x', newX);
            modalBody.setAttribute('data-drag-y', newY);
        }

        function onMouseUp() {
            handle.style.cursor = 'grab';
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        }

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    });

    // ========================================================
    // TÍNH NĂNG ZOOM VÀ CO GIÃN ẢNH (GIỐNG TRANG EDIT ĐƠN HÀNG)
    // ========================================================
    if (!window.previewImageInteractionHandlersAttached) {
        window.previewImageInteractionHandlersAttached = true;

        // 1. Ctrl + lăn chuột để co giãn kích thước ảnh (thay đổi width trực tiếp)
        document.addEventListener('wheel', function(e) {
            // Kiểm tra xem chuột có nằm trên popup ảnh xem trước không
            const draggableModal = e.target.closest('.preview-image-draggable-modal');
            if (!draggableModal || !e.ctrlKey) return;

            const img = draggableModal.querySelector('.modal-image-viewer');
            if (!img) return;

            // Ngăn trình duyệt zoom toàn bộ trang
            e.preventDefault();
            e.stopImmediatePropagation();

            // Lấy chiều rộng hiện tại (width đang set hoặc kích thước thật trên màn hình)
            let currentWidth = parseInt(img.style.width) || img.offsetWidth;

            // Tăng/giảm 60px mỗi lần lăn chuột
            if (e.deltaY < 0) {
                currentWidth += 60;
            } else {
                currentWidth -= 60;
            }

            // Giới hạn: nhỏ nhất 80px, lớn nhất 95% viewport width
            currentWidth = Math.max(80, Math.min(currentWidth, window.innerWidth * 0.95));

            img.style.width = currentWidth + 'px';
            img.style.maxWidth = 'none';
            img.style.maxHeight = 'none';
        }, { passive: false, capture: true });

        // 2. Double-click để reset kích thước ảnh về mặc định
        document.addEventListener('dblclick', function(e) {
            const draggableModal = e.target.closest('.preview-image-draggable-modal');
            if (!draggableModal) return;
            if (e.target.closest('button')) return; // Bỏ qua nếu bấm nút X

            const img = draggableModal.querySelector('.modal-image-viewer');
            if (!img) return;

            e.stopImmediatePropagation();
            img.style.width = '';
            img.style.maxWidth = '85vw';
            img.style.maxHeight = '82vh';
        }, { capture: true });

        // 3. Phím tắt Ctrl+Alt: ẩn/hiện nhanh tất cả popup ảnh đang mở để đối chiếu thông tin đơn hàng
        let previewHiddenModals = [];
        document.addEventListener('keydown', function(e) {
            const isToggleShortcut = (e.key === 'Alt' && e.ctrlKey) || (e.key === 'Control' && e.altKey);
            if (!isToggleShortcut) return;

            if (previewHiddenModals.length > 0) {
                previewHiddenModals.forEach(function(modal) {
                    modal.style.display = 'block';
                });
                previewHiddenModals = [];
                return;
            }

            const openModals = [];
            document.querySelectorAll('.preview-image-draggable-modal').forEach(function(modal) {
                if (modal.style.display !== 'none') {
                    openModals.push(modal);
                }
            });

            if (openModals.length === 0) return;

            openModals.forEach(function(modal) {
                modal.style.display = 'none';
                previewHiddenModals.push(modal);
            });
        });
    }

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
        if (document.fonts && document.fonts.ready) {
            await document.fonts.ready;
        }

        // Nhân bản chính xác khối xem trước người dùng đang nhìn thấy
        const clone = captureEl.cloneNode(true);
        clone.id = 'zalo-export-clone-node';

        // Thay thế các thẻ iconify-icon bằng SVG chuẩn để html2canvas vẽ sắc nét
        const origIcons = captureEl.querySelectorAll('iconify-icon');
        const cloneIcons = clone.querySelectorAll('iconify-icon');
        origIcons.forEach((orig, idx) => {
            const cloneIcon = cloneIcons[idx];
            if (!cloneIcon) return;
            const svg = orig.shadowRoot ? orig.shadowRoot.querySelector('svg') : orig.querySelector('svg');
            if (svg) {
                const svgClone = svg.cloneNode(true);
                const computed = window.getComputedStyle(orig);
                svgClone.style.width = computed.fontSize || '1em';
                svgClone.style.height = computed.fontSize || '1em';
                svgClone.style.display = 'inline-block';
                svgClone.style.verticalAlign = '-0.125em';
                cloneIcon.replaceWith(svgClone);
            }
        });

        // Mở rộng các khối cuộn bảng để không bị cắt bớt cột
        clone.querySelectorAll('.overflow-x-auto, .overflow-y-auto').forEach(el => {
            el.style.overflow = 'visible';
            el.style.width = '100%';
            el.style.maxWidth = 'none';
        });

        // Chiều rộng tự nhiên khớp với khung xem trước (tối thiểu 1150px)
        const targetWidth = Math.max(captureEl.scrollWidth, captureEl.offsetWidth, 1150);

        // Wrapper đặt tại (0, 0) dưới lớp modal (z-index: -9999).
        // Tuyệt đối không đặt -99999px vì Chromium sẽ tắt DirectWrite subpixel hinting gây mờ chữ!
        const wrapper = document.createElement('div');
        wrapper.id = 'zalo-export-wrapper';
        wrapper.style.position = 'fixed';
        wrapper.style.top = '0';
        wrapper.style.left = '0';
        wrapper.style.width = targetWidth + 'px';
        wrapper.style.zIndex = '-9999';
        wrapper.style.pointerEvents = 'none';
        wrapper.style.background = '#ffffff';
        wrapper.style.overflow = 'visible';

        clone.style.width = targetWidth + 'px';
        clone.style.minWidth = targetWidth + 'px';
        clone.style.maxWidth = 'none';
        clone.style.margin = '0';
        // Giữ nguyên 100% padding, borders, backgrounds, font sizes như trên màn hình xem trước

        wrapper.appendChild(clone);
        document.body.appendChild(wrapper);

        // Chờ 120ms để trình duyệt tính toán reflow và render font glyphs chuẩn xác
        await new Promise(r => setTimeout(r, 120));

        try {
            const canvas = await html2canvas(clone, {
                scale: 2, // Tỷ lệ số nguyên 2x (Retina) siêu sắc nét, không bị nhòe pixel do số lẻ
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                logging: false,
                scrollX: 0,
                scrollY: 0,
                x: 0,
                y: 0,
            });

            wrapper.remove();

            return new Promise((resolve, reject) => {
                canvas.toBlob((blob) => {
                    if (blob) resolve(blob);
                    else reject(new Error('Không thể xuất ảnh từ canvas'));
                }, 'image/png');
            });
        } catch (err) {
            if (wrapper && wrapper.parentNode) wrapper.remove();
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

    function printPreviewOrder() {
        const captureEl = document.getElementById('preview-order-capture-area');
        if (!captureEl) {
            alert('Không tìm thấy vùng nội dung đơn hàng để in');
            return;
        }

        // Tạo hoặc lấy container in trực tiếp dưới body
        let printContainer = document.getElementById('preview-order-print-container');
        if (!printContainer) {
            printContainer = document.createElement('div');
            printContainer.id = 'preview-order-print-container';
            document.body.appendChild(printContainer);
        }

        // Clone nội dung xem trước
        printContainer.innerHTML = '';
        const clone = captureEl.cloneNode(true);
        clone.id = 'preview-order-print-area';
        clone.style.minWidth = '100%';
        clone.style.maxWidth = '100%';
        clone.style.width = '100%';
        clone.style.border = 'none';
        clone.style.boxShadow = 'none';
        clone.style.padding = '0';
        clone.style.margin = '0';

        clone.querySelectorAll('.overflow-x-auto, .overflow-y-auto, .overflow-hidden').forEach(el => {
            el.style.overflow = 'visible';
            el.style.width = '100%';
            el.style.maxWidth = 'none';
        });

        clone.querySelectorAll('.truncate').forEach(el => {
            el.classList.remove('truncate');
        });

        clone.querySelectorAll('table').forEach(tbl => {
            tbl.style.width = '100%';
            tbl.style.minWidth = '100%';
            tbl.style.borderCollapse = 'collapse';
        });

        printContainer.appendChild(clone);

        // Đánh dấu body đang in đơn hàng
        document.body.classList.add('is-printing-order-preview');

        // Đợi 50ms để DOM cập nhật rồi gọi lệnh in bản địa
        setTimeout(() => {
            window.print();
        }, 50);

        // Fallback dọn dẹp sau khi in
        setTimeout(() => {
            document.body.classList.remove('is-printing-order-preview');
        }, 2000);
    }

    // Tự động dọn dẹp sau khi in hoặc hủy in
    window.addEventListener('afterprint', function() {
        document.body.classList.remove('is-printing-order-preview');
        const printContainer = document.getElementById('preview-order-print-container');
        if (printContainer) {
            printContainer.innerHTML = '';
        }
    });

    // Bắt sự kiện phím tắt Ctrl + P khi đang mở modal xem trước
    if (!window.previewOrderPrintKeyHandlerAttached) {
        window.previewOrderPrintKeyHandlerAttached = true;
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'p' || e.key === 'P')) {
                const modal = document.getElementById('preview-order-modal');
                if (modal && modal.style.display !== 'none' && getComputedStyle(modal).display !== 'none') {
                    e.preventDefault();
                    printPreviewOrder();
                }
            }
        });
    }
</script>

<style>
#preview-order-print-container {
    display: none;
}

@media print {
    @page {
        size: A4 landscape;
        margin: 6mm 8mm;
    }
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    body.is-printing-order-preview {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
        height: auto !important;
        overflow: visible !important;
    }
    body.is-printing-order-preview > *:not(#preview-order-print-container) {
        display: none !important;
    }
    body.is-printing-order-preview #preview-order-print-container {
        display: block !important;
        position: static !important;
        width: 100% !important;
        min-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
    }
    body.is-printing-order-preview #preview-order-print-container #preview-order-print-area {
        width: 100% !important;
        min-width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        box-shadow: none !important;
    }
    body.is-printing-order-preview #preview-order-print-container table {
        width: 100% !important;
        border-collapse: collapse !important;
        page-break-inside: auto;
    }
    body.is-printing-order-preview #preview-order-print-container thead {
        display: table-header-group !important;
    }
    body.is-printing-order-preview #preview-order-print-container tfoot {
        display: table-footer-group !important;
    }
    body.is-printing-order-preview #preview-order-print-container tr {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    body.is-printing-order-preview #preview-order-print-container th,
    body.is-printing-order-preview #preview-order-print-container td {
        font-size: 11px !important;
        padding: 4px 6px !important;
        border: 1px solid #cbd5e1 !important;
    }
    body.is-printing-order-preview #preview-order-print-container .space-y-2 > :not([hidden]) ~ :not([hidden]) {
        margin-top: 6px !important;
    }
    body.is-printing-order-preview #preview-order-print-container th {
        background-color: #f1f5f9 !important;
        font-weight: 700 !important;
        color: #0f172a !important;
    }
    body.is-printing-order-preview #preview-order-print-container .avoid-break {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
}
</style>
