@php
    $isWarranty = isset($acrylicOrder) && $acrylicOrder->relation_type === 'warranty';
    $hasPaymentDetails = !$isWarranty && isset($acrylicOrder) && isset($acrylicOrder->paymentDetails) && $acrylicOrder->paymentDetails->count() > 0;
    $isRework = isset($acrylicOrder) && $acrylicOrder->relation_type === 'rework';
@endphp
{{-- Order Supplies & Items Section Card (Min Late) --}}
<style>
    /* Hide dropdown arrow in very narrow select boxes for a clean, centered look */
    .hide-arrow {
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background-image: none !important;
        padding-right: 4px !important;
        padding-left: 4px !important;
        text-align: center !important;
        text-align-last: center !important;
        cursor: pointer;
    }
    .hide-arrow option {
        text-align: center;
    }
    /* === COMPACT TABLE: 75% font scale === */
    #min-late-supplies-container .order-supply-row table {
        font-size: 75% !important;
    }
    #min-late-supplies-container .order-supply-row table input,
    #min-late-supplies-container .order-supply-row table select,
    #min-late-supplies-container .order-supply-row table textarea {
        font-size: 75% !important;
        height: 24px !important;
        min-height: 24px !important;
        padding-top: 2px !important;
        padding-bottom: 2px !important;
        line-height: 1.2 !important;
    }
    #min-late-supplies-container .order-supply-row table th,
    table[data-order-resize-group="min_late_payment"] th {
        padding: 8px 4px !important;
    }
    #min-late-supplies-container .order-supply-row table td,
    table[data-order-resize-group="min_late_payment"] td {
        padding: 3px 4px !important;
    }
    /* Override td widths to ~65% of original */
    #min-late-supplies-container .order-supply-row table td[style*="width: 45px"],
    #min-late-supplies-container .order-supply-row table td[style*="width:45px"] { width: 30px !important; min-width: 30px !important; max-width: 30px !important; }
    #min-late-supplies-container .order-supply-row table td[style*="width: 160px"],
    #min-late-supplies-container .order-supply-row table td[style*="width:160px"] { width: 110px !important; min-width: 110px !important; max-width: 110px !important; }
    #min-late-supplies-container .order-supply-row table td[style*="width: 200px"],
    #min-late-supplies-container .order-supply-row table td[style*="width:200px"] { width: 130px !important; min-width: 130px !important; max-width: 130px !important; }
    #min-late-supplies-container .order-supply-row table td[style*="width: 70px"],
    #min-late-supplies-container .order-supply-row table td[style*="width:70px"] { width: 50px !important; min-width: 50px !important; max-width: 50px !important; }
    #min-late-supplies-container .order-supply-row table td[style*="width: 100px"],
    #min-late-supplies-container .order-supply-row table td[style*="width:100px"] { width: 65px !important; min-width: 65px !important; max-width: 65px !important; }
    #min-late-supplies-container .order-supply-row table td[style*="width: 105px"],
    #min-late-supplies-container .order-supply-row table td[style*="width:105px"] { width: 70px !important; min-width: 70px !important; max-width: 70px !important; }
    #min-late-supplies-container .order-supply-row table td[style*="width: 110px"],
    #min-late-supplies-container .order-supply-row table td[style*="width:110px"] { width: 75px !important; min-width: 75px !important; max-width: 75px !important; }
    #min-late-supplies-container .order-supply-row table td[style*="width: 80px"],
    #min-late-supplies-container .order-supply-row table td[style*="width:80px"] { width: 55px !important; min-width: 55px !important; max-width: 55px !important; }
    #min-late-supplies-container .order-supply-row table td[style*="min-width: 140px"],
    #min-late-supplies-container .order-supply-row table td[style*="min-width:140px"] { min-width: 90px !important; }

    body > .ts-dropdown {
        z-index: 99999 !important;
    }
    
    html body div#order-supplies-container .order-supply-row table,
    html body div#glass-supplies-container .order-supply-row table,
    html body div#min-late-supplies-container .order-supply-row table,
    html body table[data-order-resize-group="min_late_payment"] {
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }
    
    /* Sticky STT column */
    html body div#order-supplies-container .order-supply-row table thead tr th.sticky-stt-th,
    html body div#glass-supplies-container .order-supply-row table thead tr th.sticky-stt-th,
    html body div#min-late-supplies-container .order-supply-row table thead tr th.sticky-stt-th,
    html body table[data-order-resize-group="min_late_payment"] thead tr th.sticky-stt-th,
    html body .sticky-stt-th {
        position: -webkit-sticky !important;
        position: sticky !important;
        left: 0 !important;
        z-index: 15 !important;
        background-color: #f1f5f9 !important; /* Match other headers */
        background-clip: padding-box !important;
        box-shadow: inset -1px 0 0 #e5e7eb, 2px 0 4px rgba(0,0,0,0.06) !important;
    }
    html body div#order-supplies-container .order-supply-row table tbody tr td.sticky-stt-td,
    html body div#glass-supplies-container .order-supply-row table tbody tr td.sticky-stt-td,
    html body div#min-late-supplies-container .order-supply-row table tbody tr td.sticky-stt-td,
    html body table[data-order-resize-group="min_late_payment"] tbody tr td.sticky-stt-td,
    html body .sticky-stt-td.sticky-stt-td {
        position: -webkit-sticky !important;
        position: sticky !important;
        left: 0 !important;
        z-index: 5 !important;
        background-color: #ffffff !important;
        background-clip: padding-box !important;
        box-shadow: 2px 0 4px rgba(0,0,0,0.06) !important;
    }

    html body .sticky-stt-th::after,
    html body .sticky-stt-td::after {
        content: "" !important;
        position: absolute !important;
        top: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 1px !important;
        background-color: #e5e7eb !important;
        z-index: 20 !important;
    }
    
    /* Action column z-index below navbar (20) */
    html body div#order-supplies-container .order-supply-row table thead tr th:last-child,
    html body div#glass-supplies-container .order-supply-row table thead tr th:last-child,
    html body div#min-late-supplies-container .order-supply-row table thead tr th:last-child,
    html body table[data-order-resize-group="min_late_payment"] thead tr th:last-child {
        z-index: 15 !important;
    }

    html body div#order-supplies-container .order-supply-row table tbody tr td:last-child,
    html body div#glass-supplies-container .order-supply-row table tbody tr td:last-child,
    html body div#min-late-supplies-container .order-supply-row table tbody tr td:last-child,
    html body table[data-order-resize-group="min_late_payment"] tbody tr td:last-child {
        z-index: 5 !important;
    }
</style>
<div class="order-supplies-popup bg-white border border-neutral-200 rounded-xl p-6 shadow-sm relative pt-8" data-order-supplies-zoom-panel data-order-supplies-storage-key="min_late" data-order-supplies-zoom="100" data-order-supplies-visible-rows="5">
    <div class="order-supplies-header flex items-center justify-between border-b border-neutral-100 pb-4 mb-5">
        <div class="absolute -top-3.5 left-6 bg-white px-3 flex items-center gap-2 z-10">
            <iconify-icon icon="lucide:package-open" class="text-xl text-primary-500"></iconify-icon>
            <h6 class="font-bold text-base text-neutral-800 m-0">Danh sách Vật tư & Sản phẩm (Min Late)</h6>
        </div>
        <div class="flex flex-wrap items-center gap-2 ml-auto">
            <div class="flex items-center gap-2 hide-on-mobile">
                <span class="text-xs font-semibold uppercase tracking-wider text-neutral-500" style="white-space: nowrap !important; flex-shrink: 0 !important;">Hiển thị</span>
                <select data-order-supplies-visible-rows-select class="form-select form-select-sm w-28 rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500">
                    <option value="5" selected>5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="zoom-panel flex items-center gap-2 rounded-lg border border-neutral-200 bg-neutral-50 px-2 py-1 shadow-sm" style="display: none !important;">
                <button type="button" onclick="changeOrderSuppliesZoom(this, -10)" class="btn btn-icon btn-sm bg-white hover:bg-neutral-100 text-neutral-700 rounded-md p-1 h-7 w-7 flex items-center justify-center border border-neutral-200 shadow-sm transition-all" title="Thu nhỏ">
                    <iconify-icon icon="lucide:minus" class="text-xs"></iconify-icon>
                </button>
                <div class="min-w-[3rem] text-center text-xs font-semibold text-neutral-700">
                    <span data-order-supplies-zoom-label>100%</span>
                </div>
                <button type="button" onclick="changeOrderSuppliesZoom(this, 10)" class="btn btn-icon btn-sm bg-white hover:bg-neutral-100 text-neutral-700 rounded-md p-1 h-7 w-7 flex items-center justify-center border border-neutral-200 shadow-sm transition-all" title="Phóng to">
                    <iconify-icon icon="lucide:plus" class="text-xs"></iconify-icon>
                </button>
                <input data-order-supplies-zoom-range type="range" min="50" max="200" value="100" oninput="syncOrderSuppliesZoom(this)" class="w-24 accent-primary-500 h-1 cursor-pointer bg-neutral-200 rounded-lg appearance-none" />
            </div>
            <button type="button" onclick="previewOrder()" class="fullscreen-only-btn btn btn-sm bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200 rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:eye" class="text-lg"></iconify-icon>
                <span>Xem trước</span>
            </button>
            <button type="button" onclick="toggleOrderSuppliesPopup(this)" class="btn btn-sm bg-light-100 hover:bg-neutral-200 text-dark rounded-lg flex items-center gap-1 hide-on-mobile" data-order-supplies-popup-button aria-expanded="false">
                <iconify-icon icon="lucide:maximize-2" class="text-lg" data-order-supplies-popup-icon></iconify-icon>
                <span data-order-supplies-popup-label>Phóng to</span>
            </button>
            <button type="button" onclick="triggerMinLateExcelUpload()" class="btn btn-sm bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200 rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:file-spreadsheet" class="text-lg"></iconify-icon> <span class="mobile-hide-text">Nhập từ Excel</span>
            </button>
            <input type="file" id="minLateExcelFileInput" accept=".xlsx, .xls" style="display: none;">
            @if(!$isWarranty)
                @if($hasPaymentDetails)
                    <button type="button" id="toggle-payment-details-btn" onclick="togglePaymentDetailsSection()" class="btn btn-sm bg-danger-50 text-danger-600 hover:bg-danger-100 border border-danger-200 rounded-lg flex items-center gap-1">
                        <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon> <span>Xóa chi tiết hóa đơn</span>
                    </button>
                @else
                    <button type="button" id="toggle-payment-details-btn" onclick="togglePaymentDetailsSection()" class="btn btn-sm bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 rounded-lg flex items-center gap-1">
                        <iconify-icon icon="lucide:receipt" class="text-lg"></iconify-icon> <span>Thêm chi tiết hóa đơn</span>
                    </button>
                @endif
            @endif
            <button type="button" onclick="addMinLateOrderSupply()" class="btn btn-sm btn-primary rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon> <span class="mobile-hide-text">Thêm vật tư</span>
            </button>
        </div>
    </div>
    <div class="order-supplies-body">
        <div id="min-late-supplies-container" class="space-y-6">
        @php
            $supplies = collect();
            if (old('supplies')) {
                foreach (old('supplies') as $sIndex => $sData) {
                    $supply = new \stdClass();
                    $supply->id = $sData['id'] ?? null;
                    $supply->order_supply_code = $sData['order_supply_code'] ?? null;
                    $supply->supply_name = $sData['supply_name'] ?? '';
                    $supply->quantity = $sData['quantity'] ?? 0;
                    
                    $items = collect();
                    if (isset($sData['items']) && is_array($sData['items'])) {
                        foreach ($sData['items'] as $iIndex => $iData) {
                            $item = new \stdClass();
                            $item->id = $iData['id'] ?? null;
                            $item->product_code = $iData['product_code'] ?? null;
                            $item->product_name = $iData['product_name'] ?? '';
                            $item->thickness = $iData['thickness'] ?? '';
                            $item->quantity = $iData['quantity'] ?? 1;
                            $item->straight_paste_length = $iData['straight_paste_length'] ?? 0;
                            $item->beveled_length = $iData['beveled_length'] ?? 0;
                            $item->vat_moi_length = $iData['vat_moi_length'] ?? 0;
                            $item->ban_rong_40_59 = $iData['ban_rong_40_59'] ?? 0;
                            $item->ban_rong_17_39 = $iData['ban_rong_17_39'] ?? 0;
                            $item->ban_rong_25_35 = $iData['ban_rong_25_35'] ?? 0;
                            $item->beveled_handle = $iData['beveled_handle'] ?? 0;
                            $item->cnc = $iData['cnc'] ?? 0;
                            $item->direction = $iData['direction'] ?? '';
                            $item->notes = $iData['notes'] ?? '';
                            
                            $item->size = [
                                'height' => $iData['height'] ?? '',
                                'width' => $iData['width'] ?? ''
                            ];
                            
                            $item->edge_gluing = [
                                'height_1' => $iData['edge_gluing']['height_1'] ?? '',
                                'height_2' => $iData['edge_gluing']['height_2'] ?? '',
                                'width_1' => $iData['edge_gluing']['width_1'] ?? '',
                                'width_2' => $iData['edge_gluing']['width_2'] ?? ''
                            ];
                            
                            $items->push($item);
                        }
                    }
                    $supply->minLateItems = $items;
                    $supplies->push($supply);
                }
            } elseif (isset($acrylicOrder) && $acrylicOrder->type == 'min_late' && $acrylicOrder->supplies->count() > 0) {
                $supplies = $acrylicOrder->supplies;
            }
        @endphp
        @if($supplies->count() > 0)
            @foreach($supplies as $supplyIndex => $supply)
            <div class="order-supply-row bg-neutral-50/50 border border-primary-600 rounded-xl p-5 mb-2 relative shadow-sm" data-supply-id="{{ $supply->id }}">
                
                {{-- Items inside this supply --}}
                <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                            <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                        </div>
                        <input type="text" name="supplies[{{ $supplyIndex }}][order_supply_code]" class="order-supply-code-input form-control form-control-sm rounded-lg w-40 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Mã vật tư" value="{{ $supply->order_supply_code ?? '' }}">
                        <input type="text" name="supplies[{{ $supplyIndex }}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)" value="{{ $supply->supply_name }}">
                        <input type="number" name="supplies[{{ $supplyIndex }}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="any" value="{{ $supply->quantity ?? 0 }}">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="this.closest('.order-supply-row').remove(); updateOrderSummary(); if(typeof updateMinLateRowIndexes === 'function') updateMinLateRowIndexes();" class="order-table-form-action text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                            <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto pb-3" data-order-supplies-table-scroll>
                    <div class="order-supplies-table-zoom-wrap" data-order-supplies-table-zoom-wrap>
                    <table class="table bordered-table sm-table mb-0 min-w-[2000px] border border-neutral-200">
                        <thead>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" style="width: 45px; min-width: 45px; white-space: nowrap;" class="sticky-stt-th align-middle text-center border border-neutral-200 font-bold text-xs text-neutral-600 uppercase"><span class="order-stt-header-label">STT</span></th>
                                <th scope="col" style="width: 160px; min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã hàng</th>
                                <th scope="col" style="min-width: 180px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên hàng hóa, dịch vụ</th>
                                <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Độ dày</th>
                                @if($isRework)
                                    <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase text-center bg-yellow-50/50">Cao (cũ)</th>
                                    <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase text-center bg-yellow-50/50">Rộng (cũ)</th>
                                @endif
                                <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="align-middle border border-neutral-200 bg-yellow-100/70 font-bold text-xs text-neutral-600 uppercase text-center">Cao (vân)</th>
                                <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase text-center">Rộng</th>
                                <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng</th>
                                <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Vát</th>
                                <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Nẹp Cao 1</th>
                                <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Nẹp Cao 2</th>
                                <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Nẹp Rộng 1</th>
                                <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Nẹp Rộng 2</th>
                                <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán thẳng</th>
                                <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán vát</th>
                                <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán vát mòi</th>
                                <th scope="col" style="width: 105px; min-width: 105px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 40-59mm</th>
                                <th scope="col" style="width: 105px; min-width: 105px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 17-39mm</th>
                                <th scope="col" style="width: 105px; min-width: 105px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 25-35mm</th>
                                <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số vát tay nắm âm</th>
                                <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số tấm CNC</th>
                                <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều vân</th>
                                <th scope="col" style="min-width: 140px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                                <th scope="col" style="width: 80px; min-width: 80px; white-space: nowrap; position: sticky; right: 0; z-index: 3; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase bg-neutral-50">Hành động</th>
                            </tr>
                            <tr class="table-summary-row bg-neutral-100 font-bold text-neutral-800 text-center">
                                <td class="border border-neutral-200 text-center sticky-stt-td" style="font-size: 80% !important; background-color: #f1f5f9;">TỔNG</td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                @if($isRework)
                                    <td class="border border-neutral-200"></td>
                                    <td class="border border-neutral-200"></td>
                                @endif
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200 text-center" data-summary-field="quantity" style="font-size: 80% !important;">0</td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200 text-center" data-summary-field="straight_paste_length" style="font-size: 80% !important;">0</td>
                                <td class="border border-neutral-200 text-center" data-summary-field="beveled_length" style="font-size: 80% !important;">0</td>
                                <td class="border border-neutral-200 text-center" data-summary-field="vat_moi_length" style="font-size: 80% !important;">0</td>
                                <td class="border border-neutral-200 text-center" data-summary-field="ban_rong_40_59" style="font-size: 80% !important;">0</td>
                                <td class="border border-neutral-200 text-center" data-summary-field="ban_rong_17_39" style="font-size: 80% !important;">0</td>
                                <td class="border border-neutral-200 text-center" data-summary-field="ban_rong_25_35" style="font-size: 80% !important;">0</td>
                                <td class="border border-neutral-200 text-center" data-summary-field="beveled_handle" style="font-size: 80% !important;">0</td>
                                <td class="border border-neutral-200 text-center" data-summary-field="cnc" style="font-size: 80% !important;">0</td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200" style="position: sticky; right: 0; z-index: 3; background-color: #f1f5f9;"></td>
                        </thead>
                        <tbody class="supply-items-container" data-supply-index="{{ $supplyIndex }}">
                            @foreach($supply->minLateItems as $itemIndex => $item)
                            @php
                                $size = $item->size ?? [];
                                if (is_string($size)) {
                                    $size = json_decode($size, true) ?? [];
                                }
                                $gluing = $item->edge_gluing ?? [];
                                if (is_string($gluing)) {
                                    $gluing = json_decode($gluing, true) ?? [];
                                }
                                $oldSizeParts = explode(' x ', $item->old_size ?? '');
                                $oldHeight = isset($oldSizeParts[0]) ? trim($oldSizeParts[0]) : '';
                                $oldWidth = isset($oldSizeParts[1]) ? trim($oldSizeParts[1]) : '';
                            @endphp
                            <tr class="order-item-row" data-item-id="{{ $item->id }}">
                                <td style="width: 45px; min-width: 45px; " class="sticky-stt-td text-center align-middle border border-neutral-200">
                                    <span class="row-index font-semibold text-neutral-500">{{ $itemIndex + 1 }}</span>
                                </td>
                                <td style="width: 160px; min-width: 160px; " class="border border-neutral-200">
                                     <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_code]" class="product-code-input form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs font-semibold text-neutral-600" readonly value="{{ $item->product_code ?? '' }}">
                                 </td>
                                 <td style="min-width: 180px;" class="border border-neutral-200">
                                     <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Tên sản phẩm" value="{{ $item->product_name ?? $item->name ?? '' }}">
                                 </td>
                                <td style="width: 100px; min-width: 100px;" class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][thickness]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Độ dày" value="{{ $item->thickness }}">
                                </td>
                                @if($isRework)
                                    <td style="width: 100px; min-width: 100px;" class="border border-neutral-200">
                                        <input type="text" class="old-height-input form-control form-control-sm rounded-lg {{ empty($acrylicOrder->parent_id) ? '' : 'bg-neutral-100 border-neutral-200 cursor-not-allowed' }} text-center px-1 py-1 h-8 text-xs font-semibold text-neutral-600" {{ empty($acrylicOrder->parent_id) ? '' : 'readonly' }} value="{{ $oldHeight }}" oninput="updateOldSize(this)">
                                    </td>
                                    <td style="width: 100px; min-width: 100px;" class="border border-neutral-200">
                                        <input type="text" class="old-width-input form-control form-control-sm rounded-lg {{ empty($acrylicOrder->parent_id) ? '' : 'bg-neutral-100 border-neutral-200 cursor-not-allowed' }} text-center px-1 py-1 h-8 text-xs font-semibold text-neutral-600" {{ empty($acrylicOrder->parent_id) ? '' : 'readonly' }} value="{{ $oldWidth }}" oninput="updateOldSize(this)">
                                    </td>
                                @endif
                                <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="Cao (vân)" step="any" value="{{ $size['height'] ?? '' }}">
                                </td>
                                <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Rộng" step="any" value="{{ $size['width'] ?? '' }}">
                                </td>
                                <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Số lượng" min="1" required value="{{ $item->quantity }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <div class="w-full h-full" style="position: relative;">
                                        <input type="text" 
                                               name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][bevel]" 
                                               class="product-bevel-input form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center pr-6 pl-1 py-1 h-8 text-xs w-full" 
                                               placeholder="Vát" 
                                               value="{{ $item->bevel }}"
                                               data-auto-sync="{{ (empty($item->bevel) && $item->bevel !== '0') ? 'width' : (($item->bevel == ($size['width'] ?? '')) ? 'width' : (($item->bevel == ($size['height'] ?? '')) ? 'height' : 'none')) }}">
                                        <div class="pointer-events-auto cursor-pointer" style="position: absolute; right: 4px; top: 0; bottom: 0; width: 24px; display: flex; align-items: center; justify-content: center; z-index: 4;" title="Chọn kiểu vát">
                                            <iconify-icon icon="lucide:chevron-down" class="text-neutral-500 text-base pointer-events-none"></iconify-icon>
                                            <select class="product-bevel-select absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                                                <option value="">-- Không vát --&nbsp;&nbsp;</option>
                                                <option value="width">▪ Bám theo Rộng&nbsp;&nbsp;</option>
                                                <option value="height">▪ Bám theo Dài&nbsp;&nbsp;</option>
                                                <option value="custom">▪ Tự nhập tay&nbsp;&nbsp;</option>
                                            </select>
                                        </div>
                                    </div>
                                </td>
                                {{-- Dán cạnh (4 text inputs) --}}
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_gluing][height_1]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-0.5 h-8 text-xs hide-arrow">
                                        <option value=""></option>
                                        <option value="V" {{ ($gluing['height_1'] ?? '') == 'V' ? 'selected' : '' }}>V</option>
                                        <option value="T" {{ ($gluing['height_1'] ?? '') == 'T' ? 'selected' : '' }}>T</option>
                                        <option value="VAT MOI" {{ ($gluing['height_1'] ?? '') == 'VAT MOI' ? 'selected' : '' }}>VAT MOI</option>
                                        <option value="DS" {{ ($gluing['height_1'] ?? '') == 'DS' ? 'selected' : '' }}>DS</option>
                                        <option value="VAT TNA" {{ ($gluing['height_1'] ?? '') == 'VAT TNA' ? 'selected' : '' }}>VAT TNA</option>
                                        <option value="XEM BAN VE CT" {{ ($gluing['height_1'] ?? '') == 'XEM BAN VE CT' ? 'selected' : '' }}>XEM BAN VE CT</option>
                                    </select>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_gluing][height_2]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-0.5 h-8 text-xs hide-arrow">
                                        <option value=""></option>
                                        <option value="V" {{ ($gluing['height_2'] ?? '') == 'V' ? 'selected' : '' }}>V</option>
                                        <option value="T" {{ ($gluing['height_2'] ?? '') == 'T' ? 'selected' : '' }}>T</option>
                                        <option value="VAT MOI" {{ ($gluing['height_2'] ?? '') == 'VAT MOI' ? 'selected' : '' }}>VAT MOI</option>
                                        <option value="DS" {{ ($gluing['height_2'] ?? '') == 'DS' ? 'selected' : '' }}>DS</option>
                                        <option value="VAT TNA" {{ ($gluing['height_2'] ?? '') == 'VAT TNA' ? 'selected' : '' }}>VAT TNA</option>
                                        <option value="XEM BAN VE CT" {{ ($gluing['height_2'] ?? '') == 'XEM BAN VE CT' ? 'selected' : '' }}>XEM BAN VE CT</option>
                                    </select>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_gluing][width_1]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-0.5 h-8 text-xs hide-arrow">
                                        <option value=""></option>
                                        <option value="V" {{ ($gluing['width_1'] ?? '') == 'V' ? 'selected' : '' }}>V</option>
                                        <option value="T" {{ ($gluing['width_1'] ?? '') == 'T' ? 'selected' : '' }}>T</option>
                                        <option value="VAT MOI" {{ ($gluing['width_1'] ?? '') == 'VAT MOI' ? 'selected' : '' }}>VAT MOI</option>
                                        <option value="DS" {{ ($gluing['width_1'] ?? '') == 'DS' ? 'selected' : '' }}>DS</option>
                                        <option value="VAT TNA" {{ ($gluing['width_1'] ?? '') == 'VAT TNA' ? 'selected' : '' }}>VAT TNA</option>
                                        <option value="XEM BAN VE CT" {{ ($gluing['width_1'] ?? '') == 'XEM BAN VE CT' ? 'selected' : '' }}>XEM BAN VE CT</option>
                                    </select>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_gluing][width_2]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-0.5 h-8 text-xs hide-arrow">
                                        <option value=""></option>
                                        <option value="V" {{ ($gluing['width_2'] ?? '') == 'V' ? 'selected' : '' }}>V</option>
                                        <option value="T" {{ ($gluing['width_2'] ?? '') == 'T' ? 'selected' : '' }}>T</option>
                                        <option value="VAT MOI" {{ ($gluing['width_2'] ?? '') == 'VAT MOI' ? 'selected' : '' }}>VAT MOI</option>
                                        <option value="DS" {{ ($gluing['width_2'] ?? '') == 'DS' ? 'selected' : '' }}>DS</option>
                                        <option value="VAT TNA" {{ ($gluing['width_2'] ?? '') == 'VAT TNA' ? 'selected' : '' }}>VAT TNA</option>
                                        <option value="XEM BAN VE CT" {{ ($gluing['width_2'] ?? '') == 'XEM BAN VE CT' ? 'selected' : '' }}>XEM BAN VE CT</option>
                                    </select>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][straight_paste_length]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Số mét dán thẳng" step="any" value="{{ $item->straight_paste_length ?? 0 }}" readonly>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][beveled_length]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Số mét dán vát" step="any" value="{{ $item->beveled_length ?? 0 }}" readonly>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][vat_moi_length]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Số mét dán vát mòi" step="any" value="{{ $item->vat_moi_length ?? 0 }}" readonly>
                                </td>
                                <td style="width: 105px; min-width: 105px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][ban_rong_40_59]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Số mét dán bản rộng 40-59mm" step="any" value="{{ $item->ban_rong_40_59 ?? 0 }}" readonly>
                                </td>
                                <td style="width: 105px; min-width: 105px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][ban_rong_17_39]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Số mét dán bản rộng 17-39mm" step="any" value="{{ $item->ban_rong_17_39 ?? 0 }}" readonly>
                                </td>
                                <td style="width: 105px; min-width: 105px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][ban_rong_25_35]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Số mét dán bản rộng 25-35mm" step="any" value="{{ $item->ban_rong_25_35 ?? 0 }}" readonly>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][beveled_handle]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Số vát tay nắm âm" step="any" value="{{ $item->beveled_handle ?? 0 }}" readonly>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][cnc]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Số tấm CNC" value="{{ $item->cnc ?? 0 }}" readonly>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][direction]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8 text-xs" placeholder="Chiều vân" value="{{ $item->direction ?? '' }}">
                                </td>

                                <td style="min-width: 140px;" class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Ghi chú" value="{{ $item->notes ?? '' }}">
                                </td>
                                <td style="width: 80px; min-width: 80px; position: sticky; right: 0; z-index: 1; background-color: #fff; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="text-center align-middle border border-neutral-200">
                                    <div class="flex items-center gap-1 justify-center">
                                        <button type="button" onclick="insertMinLateRow(this)" class="text-neutral-400 hover:text-success-500 transition-colors p-1" title="Chèn dòng mới ở dưới">
                                            <iconify-icon icon="lucide:list-plus" class="text-base"></iconify-icon>
                                        </button>
                                        <button type="button" onclick="duplicateMinLateRow(this)" class="text-neutral-400 hover:text-primary-500 transition-colors p-1" title="Nhân bản sản phẩm">
                                            <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                                        </button>
                                        <button type="button" onclick="removeMinLateOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
                                            <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                                        </button>
                                    </div>
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][id]" value="{{ $item->id }}">
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][old_size]" value="{{ $item->old_size }}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>
                {{-- Nút thêm sản phẩm mới --}}
                <button type="button" onclick="addMinLateOrderItem(this)" class="order-table-form-action w-full mt-4 py-3 border-2 border-dashed border-primary-300 hover:border-primary-500 rounded-xl bg-primary-50/50 hover:bg-primary-50 text-primary-600 font-semibold text-sm flex items-center justify-center gap-1.5 transition-all duration-200">
                    <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon>
                    Thêm sản phẩm mới
                </button>
            </div>
            @endforeach
            @php $minLateSupplyIndex = $acrylicOrder->supplies->count() @endphp
        @else
            @php $minLateSupplyIndex = 0 @endphp
        @endif
        </div>
    </div>
</div>

@if(!$isWarranty)
{{-- Bảng chi tiết hóa đơn dùng chung các tính năng hiển thị như bảng vật tư --}}
<div id="payment-details-section" class="order-supplies-popup bg-white border border-neutral-200 rounded-xl p-6 shadow-sm mt-6 relative pt-8" style="{{ $hasPaymentDetails ? '' : 'display: none;' }}" data-order-supplies-zoom-panel data-order-supplies-storage-key="min_late_payment" data-order-supplies-zoom="100" data-order-supplies-visible-rows-disabled="1">
    <div class="order-supplies-header flex items-center justify-between border-b border-neutral-100 pb-4 mb-5">
        <div class="absolute -top-3.5 left-6 bg-white px-3 flex items-center gap-2 z-10">
            <iconify-icon icon="lucide:receipt" class="text-xl text-primary-500"></iconify-icon>
            <h6 class="font-bold text-base text-neutral-800 m-0">Chi tiết hóa đơn</h6>
        </div>
        <div class="flex flex-wrap items-center gap-2 ml-auto">
            <div class="zoom-panel flex items-center gap-2 rounded-lg border border-neutral-200 bg-neutral-50 px-2 py-1 shadow-sm" style="display: none !important;">
                <button type="button" onclick="changeOrderSuppliesZoom(this, -10)" class="btn btn-icon btn-sm bg-white hover:bg-neutral-100 text-neutral-700 rounded-md p-1 h-7 w-7 flex items-center justify-center border border-neutral-200 shadow-sm transition-all" title="Thu nhỏ">
                    <iconify-icon icon="lucide:minus" class="text-xs"></iconify-icon>
                </button>
                <div class="min-w-[3rem] text-center text-xs font-semibold text-neutral-700">
                    <span data-order-supplies-zoom-label>100%</span>
                </div>
                <button type="button" onclick="changeOrderSuppliesZoom(this, 10)" class="btn btn-icon btn-sm bg-white hover:bg-neutral-100 text-neutral-700 rounded-md p-1 h-7 w-7 flex items-center justify-center border border-neutral-200 shadow-sm transition-all" title="Phóng to">
                    <iconify-icon icon="lucide:plus" class="text-xs"></iconify-icon>
                </button>
                <input data-order-supplies-zoom-range type="range" min="50" max="200" value="100" oninput="syncOrderSuppliesZoom(this)" class="w-24 accent-primary-500 h-1 cursor-pointer bg-neutral-200 rounded-lg appearance-none" />
            </div>
            <button type="button" onclick="previewOrder()" class="fullscreen-only-btn btn btn-sm bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200 rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:eye" class="text-lg"></iconify-icon>
                <span>Xem trước</span>
            </button>
            <button type="button" onclick="toggleOrderSuppliesPopup(this)" class="btn btn-sm bg-light-100 hover:bg-neutral-200 text-dark rounded-lg flex items-center gap-1 hide-on-mobile" data-order-supplies-popup-button aria-expanded="false">
                <iconify-icon icon="lucide:maximize-2" class="text-lg" data-order-supplies-popup-icon></iconify-icon>
                <span data-order-supplies-popup-label>Phóng to</span>
            </button>
            <button type="button" onclick="addPaymentDetail()" class="btn btn-sm btn-primary rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon> <span class="mobile-hide-text">Thêm nội dung</span>
            </button>
        </div>
    </div>
    <div class="order-supplies-body">
        <div class="overflow-x-auto pb-3" data-order-supplies-table-scroll>
            <div class="order-supplies-table-zoom-wrap" data-order-supplies-table-zoom-wrap>
                <table class="table bordered-table sm-table mb-0 min-w-[900px] border border-neutral-200" data-order-resize-group="min_late_payment">
                    <thead>
                        <tr class="bg-neutral-50 text-center">
                            <th scope="col" style="width: 50px; min-width: 50px; white-space: nowrap;" class="sticky-stt-th text-center border border-neutral-200 font-bold text-xs text-neutral-600 uppercase"><span class="order-stt-header-label">STT</span></th>
                            <th scope="col" style="white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên nội dung <span class="text-danger-500">*</span></th>
                            <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn vị</th>
                            <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng</th>
                            <th scope="col" style="width: 150px; min-width: 150px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                            <th scope="col" style="width: 150px; min-width: 150px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá chỉ</th>
                            <th scope="col" style="width: 160px; min-width: 160px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                            <th scope="col" style="width: 80px; min-width: 80px; white-space: nowrap; position: sticky; right: 0; z-index: 2; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase bg-neutral-50">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="payment-details-container">
                        @if(isset($acrylicOrder) && $acrylicOrder->type == 'min_late' && isset($acrylicOrder->paymentDetails) && $acrylicOrder->paymentDetails->count() > 0)
                            @foreach($acrylicOrder->paymentDetails as $detailIndex => $detail)
                            <tr class="payment-detail-row">
                                <td style="width: 50px; min-width: 50px; " class="sticky-stt-td text-center align-middle border border-neutral-200">
                                    <span class="detail-index font-semibold text-neutral-500">{{ $detailIndex + 1 }}</span>
                                </td>
                                <td class="border border-neutral-200" style="padding: 0 !important; position: relative;">
                                    <textarea name="payment_details[{{ $detailIndex }}][name]" class="form-control form-control-sm rounded-0 border-0 focus:border-primary-500 focus:ring-primary-500 text-xs w-full payment-name-textarea resize-none" rows="2" style="resize: none; padding: 4px 8px; font-weight: 600;" placeholder="Chọn dịch vụ / Nhập nội dung..." required>{{ $detail->name }}</textarea>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="text" name="payment_details[{{ $detailIndex }}][unit]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="m, tấm..." value="{{ $detail->unit }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="payment_details[{{ $detailIndex }}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs payment-quantity-input" placeholder="1" step="any" value="{{ $detail->quantity }}">
                                </td>
                                <td style="width: 150px; min-width: 150px; " class="border border-neutral-200">
                                    <input type="number" name="payment_details[{{ $detailIndex }}][price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="0" min="0" step="any" required value="{{ $detail->price }}">
                                </td>
                                <td style="width: 150px; min-width: 150px; " class="border border-neutral-200">
                                    <input type="number" name="payment_details[{{ $detailIndex }}][price_only]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="0" min="0" step="any" value="{{ $detail->price_only }}">
                                </td>
                                <td style="width: 160px; min-width: 160px; " class="border border-neutral-200">
                                    <input type="text" name="payment_details[{{ $detailIndex }}][total]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center h-8 text-xs" placeholder="0" readonly value="{{ number_format($detail->total, 0, ',', '.') }}">
                                </td>
                                <td style="width: 80px; min-width: 80px; position: sticky; right: 0; z-index: 1; background-color: #fff; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="text-center align-middle border border-neutral-200">
                                    <button type="button" onclick="removePaymentDetail(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa nội dung này">
                                        <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                                    </button>
                                    <input type="hidden" name="payment_details[{{ $detailIndex }}][id]" value="{{ $detail->id }}">
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

<script>
window.isReworkOrder = @json(isset($acrylicOrder) && $acrylicOrder->relation_type === 'rework');
window.isIndependentRework = @json(isset($acrylicOrder) && $acrylicOrder->relation_type === 'rework' && empty($acrylicOrder->parent_id));
window.minLatePricesData = @json($minLatePrices ?? []);
let minLateSupplyIndex = {{ $minLateSupplyIndex ?? 0 }};

function updateOldSize(element) {
    const row = element.closest('.order-item-row');
    if (!row) return;
    const oldHeightInput = row.querySelector('.old-height-input');
    const oldWidthInput = row.querySelector('.old-width-input');
    const hiddenOldSize = row.querySelector('input[name*="[old_size]"]');
    if (oldHeightInput && oldWidthInput && hiddenOldSize) {
        const height = oldHeightInput.value.trim();
        const width = oldWidthInput.value.trim();
        if (height || width) {
            hiddenOldSize.value = height + ' x ' + width;
        } else {
            hiddenOldSize.value = '';
        }
    }
}

function addMinLateOrderSupply() {
    const container = document.getElementById('min-late-supplies-container');
    const newSupply = document.createElement('div');
    newSupply.className = 'order-supply-row bg-neutral-50/50 border border-primary-600 rounded-xl p-5 mb-2 relative shadow-sm';
    newSupply.innerHTML = `
        <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                    <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                </div>
                <input type="text" name="supplies[${minLateSupplyIndex}][order_supply_code]" class="order-supply-code-input form-control form-control-sm rounded-lg w-40 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Mã vật tư">
                <input type="text" name="supplies[${minLateSupplyIndex}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)">
                <input type="number" name="supplies[${minLateSupplyIndex}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="any" value="0">
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="this.closest('.order-supply-row').remove(); updateOrderSummary(); if(typeof updateMinLateRowIndexes === 'function') updateMinLateRowIndexes();" class="order-table-form-action text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                    <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto pb-3" data-order-supplies-table-scroll>
            <div class="order-supplies-table-zoom-wrap" data-order-supplies-table-zoom-wrap>
            <table class="table bordered-table sm-table mb-0 min-w-[2000px] border border-neutral-200">
                <thead>
                    <tr class="bg-neutral-50 text-center">
                        <th scope="col" style="width: 45px; min-width: 45px; white-space: nowrap;" class="sticky-stt-th align-middle text-center border border-neutral-200 font-bold text-xs text-neutral-600 uppercase"><span class="order-stt-header-label">STT</span></th>
                        <th scope="col" style="width: 160px; min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã hàng</th>
                        <th scope="col" style="min-width: 180px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên hàng hóa, dịch vụ</th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Độ dày</th>
                        ${window.isReworkOrder ? `
                            <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase text-center bg-yellow-50/50">Cao (cũ)</th>
                            <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase text-center bg-yellow-50/50">Rộng (cũ)</th>
                        ` : ''}
                        <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="align-middle border border-neutral-200 bg-yellow-100/70 font-bold text-xs text-neutral-600 uppercase text-center">Cao (vân)</th>
                        <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase text-center">Rộng</th>
                        <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng</th>
                        <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Vát</th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Nẹp Cao 1</th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Nẹp Cao 2</th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Nẹp Rộng 1</th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Nẹp Rộng 2</th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán thẳng</th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán vát</th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán vát mòi</th>
                        <th scope="col" style="width: 105px; min-width: 105px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 40-59mm</th>
                        <th scope="col" style="width: 105px; min-width: 105px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 17-39mm</th>
                        <th scope="col" style="width: 105px; min-width: 105px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 25-35mm</th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số vát tay nắm âm</th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số tấm CNC</th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều vân</th>
                        <th scope="col" style="min-width: 140px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                        <th scope="col" style="width: 80px; min-width: 80px; white-space: nowrap; position: sticky; right: 0; z-index: 3; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase bg-neutral-50">Hành động</th>
                    </tr>
                    <tr class="table-summary-row bg-neutral-100 font-bold text-neutral-800 text-center">
                        <td class="border border-neutral-200 text-center sticky-stt-td" style="font-size: 80% !important; background-color: #f1f5f9;">TỔNG</td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200"></td>
                        ${window.isReworkOrder ? `
                            <td class="border border-neutral-200"></td>
                            <td class="border border-neutral-200"></td>
                        ` : ''}
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200 text-center" data-summary-field="quantity" style="font-size: 80% !important;">0</td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200 text-center" data-summary-field="straight_paste_length" style="font-size: 80% !important;">0</td>
                        <td class="border border-neutral-200 text-center" data-summary-field="beveled_length" style="font-size: 80% !important;">0</td>
                        <td class="border border-neutral-200 text-center" data-summary-field="vat_moi_length" style="font-size: 80% !important;">0</td>
                        <td class="border border-neutral-200 text-center" data-summary-field="ban_rong_40_59" style="font-size: 80% !important;">0</td>
                        <td class="border border-neutral-200 text-center" data-summary-field="ban_rong_17_39" style="font-size: 80% !important;">0</td>
                        <td class="border border-neutral-200 text-center" data-summary-field="ban_rong_25_35" style="font-size: 80% !important;">0</td>
                        <td class="border border-neutral-200 text-center" data-summary-field="beveled_handle" style="font-size: 80% !important;">0</td>
                        <td class="border border-neutral-200 text-center" data-summary-field="cnc" style="font-size: 80% !important;">0</td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200" style="position: sticky; right: 0; z-index: 3; background-color: #f1f5f9;"></td>
                </thead>
                <tbody class="supply-items-container" data-supply-index="${minLateSupplyIndex}">
                </tbody>
            </table>
            </div>
        </div>
        <button type="button" onclick="addMinLateOrderItem(this)" class="w-full mt-4 py-3 border-2 border-dashed border-primary-300 hover:border-primary-500 rounded-xl bg-primary-50/50 hover:bg-primary-50 text-primary-600 font-semibold text-sm flex items-center justify-center gap-1.5 transition-all duration-200">
            <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon>
            Thêm sản phẩm mới
        </button>
    `;
    container.appendChild(newSupply);

    // Add one product row by default
    const addProductBtn = newSupply.querySelector('button[onclick^="addMinLateOrderItem"]');
    if (addProductBtn) {
        addMinLateOrderItem(addProductBtn, true);
    }

    const panel = newSupply.closest('[data-order-supplies-zoom-panel]');
    if (panel) {
        applyOrderSuppliesZoom(panel, panel.dataset.orderSuppliesZoom || 100);
    }

    minLateSupplyIndex++;
    if (typeof window.initOrderSuppliesHeightResize === 'function') {
        window.initOrderSuppliesHeightResize();
    }
}

function addMinLateOrderItem(button, isInitial = false, insertAfterRow = null) {
    const supplyRow = button.closest('.order-supply-row');
    const supplyIndex = supplyRow.querySelector('.supply-items-container').dataset.supplyIndex;
    const container = supplyRow.querySelector('.supply-items-container');
    const itemIndex = container.querySelectorAll('.order-item-row').length;
    
    // Không sao chép từ sản phẩm cuối
    let lastData = null;
    
    const newItem = document.createElement('tr');
    newItem.className = 'order-item-row';
    newItem.innerHTML = `
        <td style="width: 45px; min-width: 45px; " class="sticky-stt-td text-center align-middle border border-neutral-200">
            <span class="row-index font-semibold text-neutral-500">${itemIndex + 1}</span>
        </td>
        <td style="width: 160px; min-width: 160px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][product_code]" class="product-code-input form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs font-semibold text-neutral-600" readonly>
        </td>
        <td style="min-width: 180px;" class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Tên sản phẩm" value="${lastData ? lastData.product_name : ''}">
        </td>
        <td style="width: 100px; min-width: 100px;" class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][thickness]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Độ dày" value="${lastData ? lastData.thickness : ''}">
        </td>
        ${window.isReworkOrder ? `
        <td style="width: 100px; min-width: 100px;" class="border border-neutral-200">
            <input type="text" class="old-height-input form-control form-control-sm rounded-lg ${window.isIndependentRework ? '' : 'bg-neutral-100 border-neutral-200 cursor-not-allowed'} text-center px-1 py-1 h-8 text-xs font-semibold text-neutral-600" ${window.isIndependentRework ? '' : 'readonly'} value="" oninput="updateOldSize(this)">
        </td>
        <td style="width: 100px; min-width: 100px;" class="border border-neutral-200">
            <input type="text" class="old-width-input form-control form-control-sm rounded-lg ${window.isIndependentRework ? '' : 'bg-neutral-100 border-neutral-200 cursor-not-allowed'} text-center px-1 py-1 h-8 text-xs font-semibold text-neutral-600" ${window.isIndependentRework ? '' : 'readonly'} value="" oninput="updateOldSize(this)">
        </td>
        ` : ''}
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="Cao (vân)" step="any" value="${lastData ? lastData.height : ''}">
        </td>
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Rộng" step="any" value="${lastData ? lastData.width : ''}">
        </td>
        <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Số lượng" min="1" required value="${lastData ? lastData.quantity : '1'}">
        </td>
        <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
            <div class="w-full h-full" style="position: relative;">
                <input type="text" 
                       name="supplies[${supplyIndex}][items][${itemIndex}][bevel]" 
                       class="product-bevel-input form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center pr-6 pl-1 py-1 h-8 text-xs w-full" 
                       placeholder="Vát" 
                       value="${lastData ? lastData.bevel : ''}"
                       data-auto-sync="${(!lastData || (!lastData.bevel && lastData.bevel !== '0')) ? 'width' : ((lastData.bevel == lastData.width) ? 'width' : ((lastData.bevel == lastData.height) ? 'height' : 'none'))}">
                <div class="pointer-events-auto cursor-pointer" style="position: absolute; right: 4px; top: 0; bottom: 0; width: 24px; display: flex; align-items: center; justify-content: center; z-index: 4;" title="Chọn kiểu vát">
                    <iconify-icon icon="lucide:chevron-down" class="text-neutral-500 text-base pointer-events-none"></iconify-icon>
                    <select class="product-bevel-select absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                        <option value="">-- Không vát --&nbsp;&nbsp;</option>
                        <option value="width">▪ Bám theo Rộng&nbsp;&nbsp;</option>
                        <option value="height">▪ Bám theo Dài&nbsp;&nbsp;</option>
                        <option value="custom">▪ Tự nhập tay&nbsp;&nbsp;</option>
                    </select>
                </div>
            </div>
        </td>
        {{-- Dán cạnh (4 select options) --}}
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <select name="supplies[${supplyIndex}][items][${itemIndex}][edge_gluing][height_1]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-0.5 h-8 text-xs hide-arrow">
                <option value=""></option>
                <option value="V" ${lastData && lastData.edge_gluing_height_1 === 'V' ? 'selected' : ''}>V</option>
                <option value="T" ${lastData && lastData.edge_gluing_height_1 === 'T' ? 'selected' : ''}>T</option>
                <option value="VAT MOI" ${lastData && lastData.edge_gluing_height_1 === 'VAT MOI' ? 'selected' : ''}>VAT MOI</option>
                <option value="DS" ${lastData && lastData.edge_gluing_height_1 === 'DS' ? 'selected' : ''}>DS</option>
                <option value="VAT TNA" ${lastData && lastData.edge_gluing_height_1 === 'VAT TNA' ? 'selected' : ''}>VAT TNA</option>
                <option value="XEM BAN VE CT" ${lastData && lastData.edge_gluing_height_1 === 'XEM BAN VE CT' ? 'selected' : ''}>XEM BAN VE CT</option>
            </select>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <select name="supplies[${supplyIndex}][items][${itemIndex}][edge_gluing][height_2]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-0.5 h-8 text-xs hide-arrow">
                <option value=""></option>
                <option value="V" ${lastData && lastData.edge_gluing_height_2 === 'V' ? 'selected' : ''}>V</option>
                <option value="T" ${lastData && lastData.edge_gluing_height_2 === 'T' ? 'selected' : ''}>T</option>
                <option value="VAT MOI" ${lastData && lastData.edge_gluing_height_2 === 'VAT MOI' ? 'selected' : ''}>VAT MOI</option>
                <option value="DS" ${lastData && lastData.edge_gluing_height_2 === 'DS' ? 'selected' : ''}>DS</option>
                <option value="VAT TNA" ${lastData && lastData.edge_gluing_height_2 === 'VAT TNA' ? 'selected' : ''}>VAT TNA</option>
                <option value="XEM BAN VE CT" ${lastData && lastData.edge_gluing_height_2 === 'XEM BAN VE CT' ? 'selected' : ''}>XEM BAN VE CT</option>
            </select>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <select name="supplies[${supplyIndex}][items][${itemIndex}][edge_gluing][width_1]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-0.5 h-8 text-xs hide-arrow">
                <option value=""></option>
                <option value="V" ${lastData && lastData.edge_gluing_width_1 === 'V' ? 'selected' : ''}>V</option>
                <option value="T" ${lastData && lastData.edge_gluing_width_1 === 'T' ? 'selected' : ''}>T</option>
                <option value="VAT MOI" ${lastData && lastData.edge_gluing_width_1 === 'VAT MOI' ? 'selected' : ''}>VAT MOI</option>
                <option value="DS" ${lastData && lastData.edge_gluing_width_1 === 'DS' ? 'selected' : ''}>DS</option>
                <option value="VAT TNA" ${lastData && lastData.edge_gluing_width_1 === 'VAT TNA' ? 'selected' : ''}>VAT TNA</option>
                <option value="XEM BAN VE CT" ${lastData && lastData.edge_gluing_width_1 === 'XEM BAN VE CT' ? 'selected' : ''}>XEM BAN VE CT</option>
            </select>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <select name="supplies[${supplyIndex}][items][${itemIndex}][edge_gluing][width_2]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-0.5 h-8 text-xs hide-arrow">
                <option value=""></option>
                <option value="V" ${lastData && lastData.edge_gluing_width_2 === 'V' ? 'selected' : ''}>V</option>
                <option value="T" ${lastData && lastData.edge_gluing_width_2 === 'T' ? 'selected' : ''}>T</option>
                <option value="VAT MOI" ${lastData && lastData.edge_gluing_width_2 === 'VAT MOI' ? 'selected' : ''}>VAT MOI</option>
                <option value="DS" ${lastData && lastData.edge_gluing_width_2 === 'DS' ? 'selected' : ''}>DS</option>
                <option value="VAT TNA" ${lastData && lastData.edge_gluing_width_2 === 'VAT TNA' ? 'selected' : ''}>VAT TNA</option>
                <option value="XEM BAN VE CT" ${lastData && lastData.edge_gluing_width_2 === 'XEM BAN VE CT' ? 'selected' : ''}>XEM BAN VE CT</option>
            </select>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][straight_paste_length]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Số mét dán thẳng" step="any" value="${lastData ? lastData.straight_paste_length : '0'}" readonly>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][beveled_length]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Số mét dán vát" step="any" value="${lastData ? lastData.beveled_length : '0'}" readonly>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][vat_moi_length]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Số mét dán vát mòi" step="any" value="${lastData ? lastData.vat_moi_length : '0'}" readonly>
        </td>
        <td style="width: 105px; min-width: 105px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][ban_rong_40_59]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Số mét dán bản rộng 40-59mm" step="any" value="${lastData ? lastData.ban_rong_40_59 : '0'}" readonly>
        </td>
        <td style="width: 105px; min-width: 105px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][ban_rong_17_39]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Số mét dán bản rộng 17-39mm" step="any" value="${lastData ? lastData.ban_rong_17_39 : '0'}" readonly>
        </td>
        <td style="width: 105px; min-width: 105px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][ban_rong_25_35]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Số mét dán bản rộng 25-35mm" step="any" value="${lastData ? lastData.ban_rong_25_35 : '0'}" readonly>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][beveled_handle]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Số vát tay nắm âm" step="any" value="${lastData ? lastData.beveled_handle : '0'}" readonly>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][cnc]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Số tấm CNC" value="${lastData ? lastData.cnc : '0'}" readonly>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][direction]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8 text-xs" placeholder="Chiều vân" value="${lastData ? lastData.direction : ''}">
        </td>
        <td style="min-width: 140px;" class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Ghi chú" value="${lastData ? lastData.notes : ''}">
        </td>
        <td style="width: 80px; min-width: 80px; position: sticky; right: 0; z-index: 1; background-color: #fff; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="text-center align-middle border border-neutral-200">
            <div class="flex items-center gap-1 justify-center">
                <button type="button" onclick="insertMinLateRow(this)" class="text-neutral-400 hover:text-success-500 transition-colors p-1" title="Chèn dòng mới ở dưới">
                    <iconify-icon icon="lucide:list-plus" class="text-base"></iconify-icon>
                </button>
                <button type="button" onclick="duplicateMinLateRow(this)" class="text-neutral-400 hover:text-primary-500 transition-colors p-1" title="Nhân bản sản phẩm">
                    <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                </button>
                <button type="button" onclick="removeMinLateOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
                    <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                </button>
            </div>
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][old_size]" value="">
        </td>
    `;
    
    if (insertAfterRow) {
        insertAfterRow.parentNode.insertBefore(newItem, insertAfterRow.nextSibling);
    } else {
        container.appendChild(newItem);
    }
    
    const newRow = insertAfterRow ? insertAfterRow.nextElementSibling : container.lastElementChild;
    
    // Force table reflow to fix Chrome sticky cell border-collapse rendering bug
    const table = newRow.closest('table');

    
    bindMinLateRowEvents(newRow);
    calculateMinLateRowStats(newRow);
    if (typeof calculateMinLateTotalPrice === 'function') calculateMinLateTotalPrice(newRow);
    initMinLateBevelField(newRow);
    updateOrderSummary();
    updateMinLateRowIndexes();

    // Tự động cuộn xuống dòng mới thêm và focus vào ô Độ dày (chỉ khi được thêm thủ công)
    if (!isInitial) {
        setTimeout(() => {
            newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            const focusInput = newRow.querySelector('input[name*="[thickness]"]');
            if (focusInput) {
                focusInput.focus();
            }
        }, 50);
    }
}

function removeMinLateOrderItem(button) {
    const row = button.closest('.order-item-row');
    const table = row.closest('table');
    row.remove();
    updateOrderSummary();
    updateMinLateRowIndexes();

}

function updateMinLateRowIndexes() {
    const orderCode = document.getElementById('order-code-input')?.value || '';
    let globalPieceIndex = 1;
    let globalItemIndex = 1;

    document.querySelectorAll('#min-late-supplies-container .order-supply-row').forEach((supplyRow, supplyIndex) => {
        const supplyCode = supplyRow.querySelector('.order-supply-code-input')?.value || '';
        const tbody = supplyRow.querySelector('.supply-items-container');
        if (!tbody) return;

        tbody.querySelectorAll('.order-item-row').forEach((row, itemIndex) => {
            // Update row STT
            const indexEl = row.querySelector('.row-index');
            if (indexEl) indexEl.textContent = globalItemIndex;

            // Get quantity
            const quantityInput = row.querySelector('input[name*="[quantity]"]');
            const qty = parseInt(quantityInput?.value) || 1;

            // Base code for the item (first piece index)
            const baseCode = `${orderCode}.${supplyCode}.${globalPieceIndex}`;
            const productCodeInput = row.querySelector('.product-code-input');
            if (productCodeInput) {
                productCodeInput.value = baseCode;
            }

            // Remove product_ids container if it exists
            const idsContainer = row.querySelector('.product-ids-container');
            if (idsContainer) {
                idsContainer.remove();
            }

            // Update inputs name indexes
            row.querySelectorAll('input, select, textarea').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    let newName = name.replace(/supplies\[\d+\]/, `supplies[${supplyIndex}]`);
                    newName = newName.replace(/\[items\]\[\d+\]/, `[items][${itemIndex}]`);
                    input.setAttribute('name', newName);
                }
            });

            // Update mobile data-labels based on thead headers
            const headers = Array.from(supplyRow.querySelectorAll('thead th')).map(th => th.textContent.trim().replace(/\s*\*$/, ''));
            row.querySelectorAll('td').forEach((td, colIndex) => {
                if (headers[colIndex] && headers[colIndex] !== 'STT' && headers[colIndex] !== 'Hành động' && headers[colIndex] !== 'Xóa') {
                    td.setAttribute('data-label', headers[colIndex]);
                }
            });

            globalPieceIndex += qty;
            globalItemIndex++;
        });
    });
}

function insertMinLateRow(button) {
    const currentRow = button.closest('.order-item-row');
    addMinLateOrderItem(button, false, currentRow);
}

function duplicateMinLateRow(button) {
    const row = button.closest('.order-item-row');

    // Collect input/select/textarea values BEFORE cloning
    const valuesToCopy = [];
    row.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(el => {
        valuesToCopy.push({ name: el.name, value: el.value });
    });
    
    const newRow = row.cloneNode(true);
    
    // Remove database ID so it creates a new entry
    const idInput = newRow.querySelector('input[name*="[id]"]');
    if (idInput) idInput.remove();
    
    // Restore other field values by name
    valuesToCopy.forEach(({ name, value }) => {
        if (!name || name.indexOf('[id]') !== -1) return;
        const el = newRow.querySelector(`[name="${name}"]`);
        if (el) el.value = value;
    });

    // Insert after current row
    row.parentNode.insertBefore(newRow, row.nextSibling);

    // Force table reflow to fix Chrome sticky cell border-collapse rendering bug
    const table = newRow.closest('table');

    
    bindMinLateRowEvents(newRow);
    calculateMinLateRowStats(newRow);
    updateMinLateRowIndexes();
    updateOrderSummary();
}

function calculateMinLateRowStats(row) {
    if (!row) return;
    
    const heightInput = row.querySelector('input[name*="[height]"]');
    const widthInput = row.querySelector('input[name*="[width]"]');
    const quantityInput = row.querySelector('input[name*="[quantity]"]');
    
    if (!heightInput || !widthInput || !quantityInput) return;
    
    const height = parseFloat(heightInput.value) || 0;
    const width = parseFloat(widthInput.value) || 0;
    const quantity = parseFloat(quantityInput.value) || 0;
    
    const height1Select = row.querySelector('select[name*="[edge_gluing][height_1]"]');
    const height2Select = row.querySelector('select[name*="[edge_gluing][height_2]"]');
    const width1Select = row.querySelector('select[name*="[edge_gluing][width_1]"]');
    const width2Select = row.querySelector('select[name*="[edge_gluing][width_2]"]');
    
    const h1 = height1Select ? height1Select.value : '';
    const h2 = height2Select ? height2Select.value : '';
    const w1 = width1Select ? width1Select.value : '';
    const w2 = width2Select ? width2Select.value : '';
    
    // 1. Straight paste length ("T")
    let sumT = 0;
    if (h1 === 'T') sumT += height;
    if (h2 === 'T') sumT += height;
    if (w1 === 'T') sumT += width;
    if (w2 === 'T') sumT += width;
    const straightLength = (sumT * quantity) / 1000;
    
    // 2. Beveled length ("V")
    let sumV = 0;
    if (h1 === 'V') sumV += height;
    if (h2 === 'V') sumV += height;
    if (w1 === 'V') sumV += width;
    if (w2 === 'V') sumV += width;
    const beveledLength = (sumV * quantity) / 1000;
    
    // 3. Vát mòi length ("VAT MOI")
    let sumVatMoi = 0;
    if (h1 === 'VAT MOI') sumVatMoi += height;
    if (h2 === 'VAT MOI') sumVatMoi += height;
    if (w1 === 'VAT MOI') sumVatMoi += width;
    if (w2 === 'VAT MOI') sumVatMoi += width;
    const vatMoiLength = (sumVatMoi * quantity) / 1000;
    
    // 3.5. Dán bản rộng 25-35mm ("DS")
    let sumDS = 0;
    if (h1 === 'DS') sumDS += height;
    if (h2 === 'DS') sumDS += height;
    if (w1 === 'DS') sumDS += width;
    if (w2 === 'DS') sumDS += width;
    const banRong2535Length = (sumDS * quantity) / 1000;
    
    // Set outputs
    const straightInput = row.querySelector('input[name*="[straight_paste_length]"]');
    if (straightInput) {
        straightInput.value = straightLength.toFixed(2);
        straightInput.setAttribute('data-exact-value', straightLength);
    }
    
    const beveledInput = row.querySelector('input[name*="[beveled_length]"]');
    if (beveledInput) {
        beveledInput.value = beveledLength.toFixed(2);
        beveledInput.setAttribute('data-exact-value', beveledLength);
    }
    
    const vatMoiInput = row.querySelector('input[name*="[vat_moi_length]"]');
    if (vatMoiInput) {
        vatMoiInput.value = vatMoiLength.toFixed(2);
        vatMoiInput.setAttribute('data-exact-value', vatMoiLength);
    }
    
    const banRong2535Input = row.querySelector('input[name*="[ban_rong_25_35]"]');
    if (banRong2535Input) {
        banRong2535Input.value = banRong2535Length.toFixed(2);
        banRong2535Input.setAttribute('data-exact-value', banRong2535Length);
    }
    
    // Dán bản rộng 40-59mm and 17-39mm logic based on dimension checks
    let banRong40_59Length = 0;
    let banRong17_39Length = 0;
    
    const hasDim40_59 = (height >= 40 && height <= 59) || (width >= 40 && width <= 59);
    const hasDimUnder39 = (height > 0 && height <= 39) || (width > 0 && width <= 39);
    
    if (hasDim40_59) {
        banRong40_59Length = straightLength;
    }
    if (hasDimUnder39) {
        banRong17_39Length = straightLength;
    }
    
    const banRong40_59Input = row.querySelector('input[name*="[ban_rong_40_59]"]');
    if (banRong40_59Input) {
        banRong40_59Input.value = banRong40_59Length.toFixed(2);
        banRong40_59Input.setAttribute('data-exact-value', banRong40_59Length);
    }
    
    const banRong17_39Input = row.querySelector('input[name*="[ban_rong_17_39]"]');
    if (banRong17_39Input) {
        banRong17_39Input.value = banRong17_39Length.toFixed(2);
        banRong17_39Input.setAttribute('data-exact-value', banRong17_39Length);
    }
    
    // 4. CNC count: if any of the 4 contains "BAN VE CT" or "XEM BAN VE CT"
    const hasBanVe = [h1, h2, w1, w2].some(val => val === 'XEM BAN VE CT' || val === 'BAN VE CT');
    const cncInput = row.querySelector('input[name*="[cnc]"]');
    if (cncInput) {
        cncInput.value = hasBanVe ? quantity : 0;
    }
    
    // 5. Số vát tay nắm âm: if any of the 4 contains "VAT TNA"
    const hasVatTna = [h1, h2, w1, w2].some(val => val === 'VAT TNA');
    const beveledHandleInput = row.querySelector('input[name*="[beveled_handle]"]');
    if (beveledHandleInput) {
        beveledHandleInput.value = hasVatTna ? quantity : 0;
    }
    
    // 6. Bevel sync
    const bevelInput = row.querySelector('.product-bevel-input');
    if (bevelInput) {
        const syncMode = bevelInput.getAttribute('data-auto-sync') || 'none';
        if (syncMode === 'width') {
            bevelInput.value = widthInput ? widthInput.value.trim() : '';
        } else if (syncMode === 'height') {
            bevelInput.value = heightInput ? heightInput.value.trim() : '';
        }
    }
}

function bindMinLateRowEvents(row) {
    if (!row) return;
    
    const inputs = row.querySelectorAll('input[name*="[height]"], input[name*="[width]"], input[name*="[quantity]"]');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            calculateMinLateRowStats(row);
            if (typeof calculateMinLateTotalPrice === 'function') calculateMinLateTotalPrice(row);
            if (input.name.indexOf('[quantity]') !== -1) {
                updateMinLateRowIndexes();
            }
            updateOrderSummary();
        });
    });
    
    initMinLateBevelEvents(row);
    
    const selects = row.querySelectorAll('select[name*="[edge_gluing]"]');
    selects.forEach(select => {
        select.addEventListener('change', () => {
            calculateMinLateRowStats(row);
            updateOrderSummary();
        });
    });
}

function calculateMinLateTotalPrice(row) {
    const unitPriceEl = row.querySelector('input[name*="[unit_price]"]');
    const totalPriceEl = row.querySelector('input[name*="[total_price]"]');
    if (!unitPriceEl || !totalPriceEl) return;
    const unitPrice = parseFloat(unitPriceEl.value) || 0;
    const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
    const totalPrice = unitPrice * quantity;
    totalPriceEl.value = Math.round(totalPrice);
    updateOrderSummary();
}

function initMinLateBevelEvents(row) {
    const bevelInput = row.querySelector('.product-bevel-input');
    const bevelSelect = row.querySelector('.product-bevel-select');
    const widthInput = row.querySelector('input[name*="[width]"]');
    const heightInput = row.querySelector('input[name*="[height]"]');
    
    if (!bevelInput || !bevelSelect) return;

    bevelInput.addEventListener('input', () => {
        bevelInput.setAttribute('data-auto-sync', 'none');
        updateMinLateBevelFieldStyle(row);
    });

    bevelSelect.addEventListener('change', () => {
        const mode = bevelSelect.value;
        if (mode === 'width') {
            bevelInput.setAttribute('data-auto-sync', 'width');
            bevelInput.value = widthInput ? widthInput.value.trim() : '';
        } else if (mode === 'height') {
            bevelInput.setAttribute('data-auto-sync', 'height');
            bevelInput.value = heightInput ? heightInput.value.trim() : '';
        } else if (mode === '') {
            bevelInput.setAttribute('data-auto-sync', 'none');
            bevelInput.value = '';
        } else if (mode === 'custom') {
            bevelInput.setAttribute('data-auto-sync', 'none');
            updateMinLateBevelFieldStyle(row);
            bevelInput.focus();
            return;
        }
        updateMinLateBevelFieldStyle(row);
        calculateMinLateRowStats(row);
    });
}

function updateMinLateBevelFieldStyle(row) {
    const bevelInput = row.querySelector('.product-bevel-input');
    const bevelSelect = row.querySelector('.product-bevel-select');
    if (!bevelInput || !bevelSelect) return;

    const syncMode = bevelInput.getAttribute('data-auto-sync') || 'none';
    
    // Reset background and border classes
    bevelInput.classList.remove(
        'bg-indigo-50', 'text-indigo-900', 'border-indigo-300', 'font-medium',
        'bg-yellow-50', 'text-yellow-900', 'border-yellow-300',
        'bg-white', 'text-neutral-800', 'border-neutral-300',
        'bg-neutral-50', 'bg-sky-50', 'bg-emerald-50', 'bg-amber-50', 'font-semibold', 'text-sky-700', 'text-emerald-700', 'text-amber-700'
    );

    if (syncMode === 'width') {
        bevelInput.classList.add('bg-indigo-50', 'text-indigo-900', 'border-indigo-300', 'font-medium');
        bevelSelect.value = 'width';
    } else if (syncMode === 'height') {
        bevelInput.classList.add('bg-yellow-50', 'text-yellow-900', 'border-yellow-300', 'font-medium');
        bevelSelect.value = 'height';
    } else {
        bevelInput.classList.add('bg-white', 'text-neutral-800', 'border-neutral-300');
        if (bevelInput.value.trim() === '') {
            bevelSelect.value = '';
        } else {
            bevelSelect.value = 'custom';
        }
    }
}

function initMinLateBevelField(row) {
    const heightInput = row.querySelector('input[name*="[height]"]');
    const widthInput = row.querySelector('input[name*="[width]"]');
    const bevelInput = row.querySelector('input[name*="[bevel]"]');
    const bevelSelect = row.querySelector('.product-bevel-select');
    
    if (!heightInput || !widthInput || !bevelInput || !bevelSelect) return;
    
    const height = heightInput.value.trim();
    const width = widthInput.value.trim();
    let val = bevelInput.value.trim();
    
    const currentSync = bevelInput.getAttribute('data-auto-sync') || 'width';

    if (val === '') {
        if (currentSync === 'none') {
            bevelInput.setAttribute('data-auto-sync', 'none');
        } else {
            // Default to width sync
            bevelInput.setAttribute('data-auto-sync', 'width');
            if (width !== '') {
                bevelInput.value = width;
                val = width;
            }
        }
    } else {
        // Check if value matches width or height
        if (width !== '' && val === width) {
            bevelInput.setAttribute('data-auto-sync', 'width');
        } else if (height !== '' && val === height) {
            bevelInput.setAttribute('data-auto-sync', 'height');
        } else {
            bevelInput.setAttribute('data-auto-sync', 'none');
        }
    }

    updateMinLateBevelFieldStyle(row);
}




// Initial setup for Min Late-specific rows if DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('min-late-supplies-container');
    if (container && container.querySelectorAll('.order-supply-row').length === 0) {
        addMinLateOrderSupply();
    }
    document.querySelectorAll('.order-item-row').forEach(row => {
        bindMinLateRowEvents(row);
        calculateMinLateRowStats(row);
        initMinLateBevelField(row);
    });

    // Recalculate codes on load
    updateMinLateRowIndexes();

    const orderCodeInput = document.getElementById('order-code-input');
    if (orderCodeInput) {
        orderCodeInput.addEventListener('input', () => updateMinLateRowIndexes());
    }
});

// Live listener for supply code input changes
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('order-supply-code-input')) {
        updateMinLateRowIndexes();
    }
});

let paymentDetailIndex = {{ (isset($acrylicOrder) && $acrylicOrder->type == 'min_late' && isset($acrylicOrder->paymentDetails)) ? $acrylicOrder->paymentDetails->count() : 0 }};

// Đồng bộ lại chiều cao hiển thị và co giãn cột sau khi thêm/xóa dòng chi tiết.
function refreshPaymentDetailsTableLayout() {
    const panel = document.querySelector('[data-order-supplies-storage-key="min_late_payment"]');
    if (panel && typeof applyOrderSuppliesVisibleRows === 'function') {
        applyOrderSuppliesVisibleRows(panel, panel.dataset.orderSuppliesVisibleRows || '5');
    }

    if (typeof initOrderColumnResize === 'function') {
        initOrderColumnResize(document);
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function addPaymentDetail() {
    const container = document.getElementById('payment-details-container');
    if (!container) return;
    
    const newRow = document.createElement('tr');
    newRow.className = 'payment-detail-row';
    newRow.innerHTML = `
        <td style="width: 50px; min-width: 50px; " class="sticky-stt-td text-center align-middle border border-neutral-200">
            <span class="detail-index font-semibold text-neutral-500">${paymentDetailIndex + 1}</span>
        </td>
        <td class="border border-neutral-200" style="padding: 0 !important; position: relative;">
            <textarea name="payment_details[${paymentDetailIndex}][name]" class="form-control form-control-sm rounded-0 border-0 focus:border-primary-500 focus:ring-primary-500 text-xs w-full payment-name-textarea resize-none" rows="2" style="resize: none; padding: 4px 8px; font-weight: 600;" placeholder="Chọn dịch vụ / Nhập nội dung..." required></textarea>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="payment_details[${paymentDetailIndex}][unit]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="m, tấm...">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="payment_details[${paymentDetailIndex}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs payment-quantity-input" placeholder="1" step="any" value="1">
        </td>
        <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
            <input type="number" name="payment_details[${paymentDetailIndex}][price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="0" min="0" step="any" required value="0">
        </td>
        <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
            <input type="number" name="payment_details[${paymentDetailIndex}][price_only]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="0" min="0" step="any" value="0">
        </td>
        <td style="width: 120px; min-width: 120px; " class="border border-neutral-200">
            <input type="text" name="payment_details[${paymentDetailIndex}][total]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center h-8 text-xs" placeholder="0" readonly value="0">
        </td>
        <td style="width: 80px; min-width: 80px; position: sticky; right: 0; z-index: 1; background-color: #fff; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="text-center align-middle border border-neutral-200">
            <button type="button" onclick="removePaymentDetail(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa nội dung này">
                <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
            </button>
        </td>
    `;
    container.appendChild(newRow);
    
    // Initialize Autocomplete for the new row textarea
    const textareaEl = newRow.querySelector('.payment-name-textarea');
    if (textareaEl) {
        initPaymentNameAutocomplete(textareaEl);
    }

    // Initialize Suggestions for the new row quantity
    const qtyInputEl = newRow.querySelector('.payment-quantity-input');
    if (qtyInputEl) {
        initPaymentQuantitySuggestions(qtyInputEl);
    }

    // Bind change/input events for auto-calculating row total
    bindPaymentDetailEvents(newRow);
    
    paymentDetailIndex++;
    updatePaymentDetailIndexes();
    updateOrderSummary();
    refreshPaymentDetailsTableLayout();
}

function removePaymentDetail(button) {
    const row = button.closest('.payment-detail-row');
    row.remove();
    updatePaymentDetailIndexes();
    updateOrderSummary();
    refreshPaymentDetailsTableLayout();
}

function calculatePaymentDetailRowTotal(row) {
    const qtyInput = row.querySelector('input[name*="[quantity]"]');
    const priceInput = row.querySelector('input[name*="[price]"]');
    const priceOnlyInput = row.querySelector('input[name*="[price_only]"]');
    const totalInput = row.querySelector('input[name*="[total]"]');
    
    if (!qtyInput || !priceInput || !totalInput) return;
    
    const quantity = parseFloat(qtyInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    const priceOnly = priceOnlyInput ? (parseFloat(priceOnlyInput.value) || 0) : 0;
    const total = quantity * (price + priceOnly);
    
    totalInput.value = total > 0 ? Math.round(total).toLocaleString('vi-VN') : 0;
    updateOrderSummary();
}

function updatePaymentDetailIndexes() {
    const table = document.querySelector('table[data-order-resize-group="min_late_payment"]');
    const headers = table ? Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim().replace(/\s*\*$/, '')) : [];

    const rows = document.querySelectorAll('.payment-detail-row');
    rows.forEach((row, index) => {
        const indexSpan = row.querySelector('.detail-index');
        if (indexSpan) {
            indexSpan.textContent = index + 1;
        }

        row.querySelectorAll('td').forEach((td, colIndex) => {
            if (headers[colIndex] && headers[colIndex] !== 'STT' && headers[colIndex] !== 'Xóa') {
                td.setAttribute('data-label', headers[colIndex]);
            }
        });
    });
}

function bindPaymentDetailEvents(row) {
    const inputs = row.querySelectorAll('input[name*="[quantity]"], input[name*="[price]"], input[name*="[price_only]"]');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            calculatePaymentDetailRowTotal(row);
        });
    });
}

// ---- Autocomplete Tên dịch vụ & Gợi ý số lượng chi tiết hóa đơn Min Late ----
let globalPaymentNameDropdown = null;
let activePaymentNameTextarea = null;

function getGlobalPaymentNameDropdown() {
    if (!globalPaymentNameDropdown) {
        globalPaymentNameDropdown = document.createElement('div');
        globalPaymentNameDropdown.id = 'payment-name-autocomplete-global-dropdown';
        globalPaymentNameDropdown.className = 'dropdown-menu p-0 shadow-lg border border-neutral-200';
        globalPaymentNameDropdown.style.cssText = 'display: none; position: absolute; z-index: 999999; max-height: 220px; overflow-y: auto; background-color: #ffffff !important;';
        document.body.appendChild(globalPaymentNameDropdown);

        // Click outside listener (chỉ đăng ký 1 lần duy nhất)
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.payment-name-textarea') && !globalPaymentNameDropdown.contains(e.target)) {
                globalPaymentNameDropdown.style.display = 'none';
            }
        });

        // Scroll listener (chỉ đăng ký 1 lần duy nhất)
        window.addEventListener('scroll', function() {
            if (globalPaymentNameDropdown.style.display === 'block' && activePaymentNameTextarea) {
                const rect = activePaymentNameTextarea.getBoundingClientRect();
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
                globalPaymentNameDropdown.style.top = (rect.bottom + scrollTop) + 'px';
                globalPaymentNameDropdown.style.left = (rect.left + scrollLeft) + 'px';
            }
        }, true);

        // Resize listener (chỉ đăng ký 1 lần duy nhất)
        window.addEventListener('resize', function() {
            if (globalPaymentNameDropdown.style.display === 'block' && activePaymentNameTextarea) {
                const rect = activePaymentNameTextarea.getBoundingClientRect();
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
                globalPaymentNameDropdown.style.top = (rect.bottom + scrollTop) + 'px';
                globalPaymentNameDropdown.style.left = (rect.left + scrollLeft) + 'px';
                globalPaymentNameDropdown.style.width = rect.width + 'px';
            }
        });
    }
    return globalPaymentNameDropdown;
}

function initPaymentNameAutocomplete(textarea) {
    const globalDropdown = getGlobalPaymentNameDropdown();

    function renderDropdown(filterText = '') {
        globalDropdown.innerHTML = '';
        const search = (filterText || '').toLowerCase().trim();
        const prices = window.minLatePricesData || [];
        
        // Filter prices based on name or category
        const filtered = prices.filter(p => {
            const fullName = `${p.category_name || ''} - ${p.product_name || ''}`.toLowerCase();
            return fullName.includes(search);
        });
        
        if (filtered.length === 0) {
            globalDropdown.style.display = 'none';
            return;
        }
        
        filtered.forEach(p => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'dropdown-item text-xs py-2 px-3 text-start w-full border-b border-neutral-100';
            item.style.whiteSpace = 'normal';
            item.style.backgroundColor = '#ffffff';
            item.style.color = '#1f2937';
            item.innerHTML = `<span class="font-bold text-primary">${escapeHtml(p.category_name || '')}</span> - ${escapeHtml(p.product_name || '')}`;
            
            item.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Set textarea value
                textarea.value = p.product_name || '';
                
                // Fill unit and price
                const row = textarea.closest('.payment-detail-row');
                if (row) {
                    const unitInput = row.querySelector('input[name*="[unit]"]');
                    const priceInput = row.querySelector('input[name*="[price]"]');
                    
                    if (unitInput) {
                        unitInput.value = p.unit || '';
                        applyFlashEffect(unitInput);
                    }
                    
                    if (priceInput) {
                        priceInput.value = p.price || 0;
                        applyFlashEffect(priceInput);
                    }
                    
                    calculatePaymentDetailRowTotal(row);
                }
                
                // Trigger input event
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
                updateOrderSummary();
                
                globalDropdown.style.display = 'none';
            });
            
            item.addEventListener('mouseenter', () => {
                item.style.backgroundColor = '#f3f4f6';
            });
            item.addEventListener('mouseleave', () => {
                item.style.backgroundColor = '#ffffff';
            });
            
            globalDropdown.appendChild(item);
        });
        
        // Position the dropdown directly under the textarea
        const rect = textarea.getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        
        globalDropdown.style.top = (rect.bottom + scrollTop) + 'px';
        globalDropdown.style.left = (rect.left + scrollLeft) + 'px';
        globalDropdown.style.width = rect.width + 'px';
        globalDropdown.style.display = 'block';
        activePaymentNameTextarea = textarea;
    }
    
    // Show dropdown on focus / click
    textarea.addEventListener('focus', function() {
        renderDropdown(textarea.value);
    });
    textarea.addEventListener('click', function() {
        renderDropdown(textarea.value);
    });
    
    // Filter on typing
    textarea.addEventListener('input', function() {
        renderDropdown(textarea.value);
    });
}

function applyFlashEffect(el) {
    if (!el) return;
    el.style.transition = 'background-color 0.4s ease';
    el.style.backgroundColor = '#ecfdf5';
    setTimeout(() => {
        el.style.backgroundColor = '';
    }, 850);
}

function getMinLateQuantitySuggestions() {
    let totalStraight = 0;
    let total40_59 = 0;
    let total17_39 = 0;
    let totalBeveled = 0;
    
    document.querySelectorAll('.order-supply-row table tbody tr:not(.hidden)').forEach(row => {
        const straightInput = row.querySelector('input[name*="[straight_paste_length]"]');
        const r40_59Input = row.querySelector('input[name*="[ban_rong_40_59]"]');
        const r17_39Input = row.querySelector('input[name*="[ban_rong_17_39]"]');
        const beveledInput = row.querySelector('input[name*="[beveled_length]"]');
        
        if (straightInput && !straightInput.disabled) {
            totalStraight += parseFloat(straightInput.getAttribute('data-exact-value') || straightInput.value) || 0;
        }
        if (r40_59Input && !r40_59Input.disabled) {
            total40_59 += parseFloat(r40_59Input.getAttribute('data-exact-value') || r40_59Input.value) || 0;
        }
        if (r17_39Input && !r17_39Input.disabled) {
            total17_39 += parseFloat(r17_39Input.getAttribute('data-exact-value') || r17_39Input.value) || 0;
        }
        if (beveledInput && !beveledInput.disabled) {
            totalBeveled += parseFloat(beveledInput.getAttribute('data-exact-value') || beveledInput.value) || 0;
        }
    });
    
    const opt1 = (totalStraight - total40_59 - total17_39) * 1.05;
    const opt2 = totalBeveled * 1.05;
    
    return {
        opt1: Math.round(opt1 * 100) / 100,
        opt2: Math.round(opt2 * 100) / 100
    };
}

let globalPaymentQtyDropdown = null;
let activePaymentQtyInput = null;

function getGlobalPaymentQtyDropdown() {
    if (!globalPaymentQtyDropdown) {
        globalPaymentQtyDropdown = document.createElement('div');
        globalPaymentQtyDropdown.id = 'payment-qty-suggestions-global-dropdown';
        globalPaymentQtyDropdown.className = 'dropdown-menu p-0 shadow-lg border border-neutral-200';
        globalPaymentQtyDropdown.style.cssText = 'display: none; position: absolute; z-index: 999999; max-height: 200px; overflow-y: auto; background-color: #ffffff !important; min-width: 220px;';
        document.body.appendChild(globalPaymentQtyDropdown);

        // Click outside listener (chỉ đăng ký 1 lần duy nhất)
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.payment-quantity-input') && !globalPaymentQtyDropdown.contains(e.target)) {
                globalPaymentQtyDropdown.style.display = 'none';
            }
        });

        // Scroll listener (chỉ đăng ký 1 lần duy nhất)
        window.addEventListener('scroll', function() {
            if (globalPaymentQtyDropdown.style.display === 'block' && activePaymentQtyInput) {
                const rect = activePaymentQtyInput.getBoundingClientRect();
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
                globalPaymentQtyDropdown.style.top = (rect.bottom + scrollTop) + 'px';
                globalPaymentQtyDropdown.style.left = (rect.left + scrollLeft) + 'px';
            }
        }, true);

        // Resize listener (chỉ đăng ký 1 lần duy nhất)
        window.addEventListener('resize', function() {
            if (globalPaymentQtyDropdown.style.display === 'block' && activePaymentQtyInput) {
                const rect = activePaymentQtyInput.getBoundingClientRect();
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
                globalPaymentQtyDropdown.style.top = (rect.bottom + scrollTop) + 'px';
                globalPaymentQtyDropdown.style.left = (rect.left + scrollLeft) + 'px';
                globalPaymentQtyDropdown.style.width = rect.width + 'px';
            }
        });
    }
    return globalPaymentQtyDropdown;
}

function initPaymentQuantitySuggestions(input) {
    const globalQtyDropdown = getGlobalPaymentQtyDropdown();
    
    function renderSuggestions() {
        globalQtyDropdown.innerHTML = '';
        const suggestions = getMinLateQuantitySuggestions();
        
        const opts = [
            { label: 'Option 1: (Thẳng - Bản rộng)*1.05', value: suggestions.opt1 },
            { label: 'Option 2: (Vát * 1.05)', value: suggestions.opt2 }
        ];
        
        opts.forEach(opt => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'dropdown-item text-xs py-2 px-3 text-start w-full border-b border-neutral-100';
            item.style.whiteSpace = 'normal';
            item.style.backgroundColor = '#ffffff';
            item.style.color = '#1f2937';
            item.innerHTML = `<span class="font-bold text-primary">${opt.value.toLocaleString('vi-VN')}</span> <span class="text-neutral-500 font-normal">(${opt.label})</span>`;
            
            item.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                input.value = opt.value;
                
                // Trigger change event
                input.dispatchEvent(new Event('input', { bubbles: true }));
                
                const row = input.closest('.payment-detail-row');
                if (row) {
                    calculatePaymentDetailRowTotal(row);
                }
                
                globalQtyDropdown.style.display = 'none';
            });
            
            item.addEventListener('mouseenter', () => {
                item.style.backgroundColor = '#f3f4f6';
            });
            item.addEventListener('mouseleave', () => {
                item.style.backgroundColor = '#ffffff';
            });
            
            globalQtyDropdown.appendChild(item);
        });
        
        const rect = input.getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        
        globalQtyDropdown.style.top = (rect.bottom + scrollTop) + 'px';
        globalQtyDropdown.style.left = (rect.left + scrollLeft) + 'px';
        globalQtyDropdown.style.display = 'block';
        activePaymentQtyInput = input;
    }
    
    input.addEventListener('focus', renderSuggestions);
    input.addEventListener('click', renderSuggestions);
}

// Initial setup for Min Late-specific rows and payment details
document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('.order-item-row').forEach(row => {
        bindMinLateRowEvents(row);
        calculateMinLateRowStats(row);
    });

    // Bind events for existing payment details
    document.querySelectorAll('.payment-detail-row').forEach(row => {
        bindPaymentDetailEvents(row);
        calculatePaymentDetailRowTotal(row);
    });

    // Initialize Autocomplete for existing payment details
    document.querySelectorAll('.payment-name-textarea').forEach(function(textarea) {
        initPaymentNameAutocomplete(textarea);
    });

    // Initialize suggestions for existing payment quantity inputs
    document.querySelectorAll('.payment-quantity-input').forEach(function(input) {
        initPaymentQuantitySuggestions(input);
    });

    updatePaymentDetailIndexes();

    const minLateBackdrop = document.getElementById('min-late-excel-import-backdrop');
    const minLateModal = document.getElementById('min-late-excel-import-modal');
    if (minLateBackdrop) document.body.appendChild(minLateBackdrop);
    if (minLateModal) document.body.appendChild(minLateModal);
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
let _minLateExcelSupplyGroups = [];
let _minLateExcelServices = [];

function triggerMinLateExcelUpload() {
    const fileInput = document.getElementById('minLateExcelFileInput');
    if (!fileInput) return;
    fileInput.value = '';
    fileInput.onchange = function(e) { handleMinLateExcelFile(e.target.files[0]); };
    fileInput.click();
}

function handleMinLateExcelFile(file) {
    if (!file) return;
    document.getElementById('min-late-excel-import-filename').textContent = file.name;
    _minLateExcelSupplyGroups = [];
    _minLateExcelServices = [];

    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const sheetName = workbook.SheetNames[0];
            const ws = workbook.Sheets[sheetName];
            const rawRows = XLSX.utils.sheet_to_json(ws, { header: 1, defval: '', raw: false });

            if (!rawRows || rawRows.length === 0) {
                showMinLateExcelModal([], []);
                return;
            }

            const clean = v => (v === null || v === undefined) ? '' : String(v).trim();
            const num = v => { let c = clean(v); if (c === '') return ''; let n = parseFloat(c.replace(/,/g, '')); return isNaN(n) ? '' : n; };
            const cleanEdge = v => { let s = clean(v).toUpperCase(); return s === 'V' ? 'V' : (s === 'T' ? 'T' : ''); };

            let hIdx = { straight: 12, vat: 13, ban25: 14, ban40: 15, ban17: 16, notes: 17, vatMoi: -1, beveledHandle: -1, cnc: -1 };
            // Try to find header row to dynamically map columns
            for(let i = 0; i < 5 && i < rawRows.length; i++) {
                const rStr = rawRows[i].map(c => clean(c).toLowerCase()).join(' ');
                if(rStr.includes('thẳng') || rStr.includes('vát')) {
                    rawRows[i].forEach((c, idx) => {
                        const cell = clean(c).toLowerCase();
                        if (cell.includes('thẳng')) hIdx.straight = idx;
                        else if (cell.includes('vát mòi')) hIdx.vatMoi = idx;
                        else if (cell.includes('vát')) hIdx.vat = idx;
                        else if (cell.includes('25-35') || cell.includes('25 - 35')) hIdx.ban25 = idx;
                        else if (cell.includes('40-59') || cell.includes('40 - 59')) hIdx.ban40 = idx;
                        else if (cell.includes('17-39') || cell.includes('17 - 39')) hIdx.ban17 = idx;
                        else if (cell.includes('tay nắm âm')) hIdx.beveledHandle = idx;
                        else if (cell.includes('cnc')) hIdx.cnc = idx;
                        else if (cell.includes('ghi chú')) hIdx.notes = idx;
                    });
                    break;
                }
            }

            let groups = [];
            let currentGroup = null;
            let services = [];
            let parsingServices = false;
            let sNameIdx = -1, sUnitIdx = -1, sQtyIdx = -1, sPriceIdx = -1, sPriceOnlyIdx = -1, sTotalPriceIdx = -1;

            rawRows.forEach((row, idx) => {
                if (!parsingServices) {
                    const rowStr = row.map(c => clean(c).toLowerCase().replace(/\s+/g, ' ')).join(' ');
                    if (rowStr.includes('tính giá bán sản phẩm, dịch vụ')) {
                        parsingServices = true;
                        row.forEach((cell, i) => {
                            const txt = clean(cell).toLowerCase().replace(/\s+/g, ' ');
                            if (txt.includes('tính giá bán') || txt.includes('sản phẩm') || txt.includes('dịch vụ') || txt.includes('nội dung')) sNameIdx = i;
                            else if (txt === 'đơn vị') sUnitIdx = i;
                            else if (txt === 'số lượng') sQtyIdx = i;
                            else if (txt === 'đơn giá') sPriceIdx = i;
                            else if (txt === 'đơn giá chỉ') sPriceOnlyIdx = i;
                            else if (txt === 'thành tiền' || txt === 'tổng tiền') sTotalPriceIdx = i;
                        });
                        if (sNameIdx === -1) sNameIdx = 2; // Default fallback to column C
                        return;
                    }
                }

                if (parsingServices) {
                    const rowStr = row.map(c => clean(c).toLowerCase()).join(' ');
                    
                    // End of services table
                    if (rowStr.includes('tổng tiền') || rowStr.includes('tổng cộng') || rowStr.includes('công nợ') || rowStr.includes('đã ck') || rowStr.includes('còn lại')) {
                        parsingServices = false;
                        return;
                    }

                    const name = sNameIdx !== -1 ? clean(row[sNameIdx]) : clean(row[2]);

                    if (name && name.length > 2) {
                        const unit = sUnitIdx !== -1 ? clean(row[sUnitIdx]) : '';
                        const qtyStr = sQtyIdx !== -1 ? clean(row[sQtyIdx]) : '';
                        const priceStr = sPriceIdx !== -1 ? clean(row[sPriceIdx]) : '';
                        
                        const stt = clean(row[1]);
                        const isSttNum = /^\d+$/.test(stt);
                        
                        if (isSttNum || num(qtyStr) > 0) {
                            services.push({ 
                                name: name, 
                                unit: unit, 
                                qty: num(qtyStr) || 1, 
                                price: num(priceStr) || 0, 
                                priceOnly: sPriceOnlyIdx !== -1 ? num(row[sPriceOnlyIdx]) : 0,
                                totalPrice: sTotalPriceIdx !== -1 ? num(row[sTotalPriceIdx]) : ''
                            });
                        }
                    }
                    return;
                }

                const col0 = clean(row[0]);
                const stt = clean(row[1]);
                if (col0 !== 'CT' && col0 !== '0' && col0 !== '1') return;

                // Identify if this is a group header (e.g. STT is A, B, C, I, II)
                const isGroupHeader = /^[A-ZIVX]+$/.test(stt) && !/^\d+$/.test(stt);

                if (isGroupHeader || (!stt && clean(row[3]) && !clean(row[4]))) {
                    const groupName = clean(row[3]);
                    if (groupName && groupName.toUpperCase() !== 'VẬT TƯ') {
                        currentGroup = {
                            supply_name: groupName,
                            items: []
                        };
                        groups.push(currentGroup);
                    }
                } else if (/^\d+$/.test(stt)) {
                    if (!currentGroup) {
                        currentGroup = { supply_name: 'Vật tư', items: [] };
                        groups.push(currentGroup);
                    }

                    const height = clean(row[4]);
                    const width = clean(row[5]);
                    const qty = parseInt(clean(row[6])) || 1;
                    const beveled = clean(row[7]);
                    
                    const edge_h1 = cleanEdge(row[8]);
                    const edge_h2 = cleanEdge(row[9]);
                    const edge_w1 = cleanEdge(row[10]);
                    const edge_w2 = cleanEdge(row[11]);
                    
                    const straight = num(row[hIdx.straight]);
                    const vat = num(row[hIdx.vat]);
                    const ban25 = num(row[hIdx.ban25]);
                    const ban40 = num(row[hIdx.ban40]);
                    const ban17 = num(row[hIdx.ban17]);
                    
                    const vatMoi = hIdx.vatMoi !== -1 ? num(row[hIdx.vatMoi]) : '';
                    const beveledHandle = hIdx.beveledHandle !== -1 ? num(row[hIdx.beveledHandle]) : '';
                    const cnc = hIdx.cnc !== -1 ? num(row[hIdx.cnc]) : '';
                    
                    const notes = hIdx.notes !== -1 ? clean(row[hIdx.notes]) : clean(row[17]);
                    
                    currentGroup.items.push({
                        height: height,
                        width: width,
                        quantity: qty,
                        beveled: beveled,
                        edge_h1: edge_h1,
                        edge_h2: edge_h2,
                        edge_w1: edge_w1,
                        edge_w2: edge_w2,
                        straight: straight,
                        vat: vat,
                        vatMoi: vatMoi,
                        ban25: ban25,
                        ban40: ban40,
                        ban17: ban17,
                        beveledHandle: beveledHandle,
                        cnc: cnc,
                        notes: notes
                    });
                }
            });

            _minLateExcelSupplyGroups = groups.filter(g => g.items.length > 0);
            _minLateExcelServices = services;
            showMinLateExcelModal(_minLateExcelSupplyGroups, _minLateExcelServices);
        } catch(err) {
            console.error(err);
            alert("Lỗi khi đọc file Excel. Vui lòng kiểm tra lại định dạng.");
        }
    };
    reader.readAsArrayBuffer(file);
}

function showMinLateExcelModal(groups, services = []) {
    const thead = document.getElementById('min-late-excel-preview-thead');
    const tbody = document.getElementById('min-late-excel-preview-tbody');
    const empty = document.getElementById('min-late-excel-preview-empty');
    const countEl = document.getElementById('min-late-excel-import-count');
    const confirmBtn = document.getElementById('min-late-excel-import-confirm-btn');

    thead.innerHTML = `<tr>
        <th style="padding:8px 10px;text-align:left;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">Vật tư / Tên SP</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;">Cao</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;">Rộng</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;">SL</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">Cạnh Vát</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">Nẹp Cao 1</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">Nẹp Cao 2</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">Nẹp Rộng 1</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">Nẹp Rộng 2</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;min-width:70px;">Số mét dán thẳng</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;min-width:70px;">Số mét dán vát</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;min-width:80px;">Số mét dán bản rộng 25-35mm</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;min-width:80px;">Số mét dán bản rộng 40-59mm</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;min-width:80px;">Số mét dán bản rộng 17-39mm</th>
        <th style="padding:8px 10px;text-align:left;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">Ghi chú</th>
    </tr>`;

    if ((!groups || groups.length === 0) && (!services || services.length === 0)) {
        tbody.innerHTML = '';
        empty.style.display = 'block';
        confirmBtn.style.opacity = '0.5';
        confirmBtn.style.pointerEvents = 'none';
        document.getElementById('min-late-excel-import-backdrop').style.display = 'block';
        document.getElementById('min-late-excel-import-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        return;
    }

    empty.style.display = 'none';
    confirmBtn.style.opacity = '1';
    confirmBtn.style.pointerEvents = 'auto';

    let html = '';
    let totalItems = 0;

    const escHtml = (str) => {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    };

    groups.forEach(group => {
        totalItems += group.items.length;
        let itemsHtml = '';
        
        group.items.forEach((item, i) => {
            const bgClass = i % 2 === 0 ? '' : 'background:#fafafa;';
            itemsHtml += `
                <tr style="border-bottom:1px solid #f1f5f9;${bgClass}">
                    <td style="padding:6px 12px;color:#0f172a;padding-left:24px;font-style:italic;font-size:11px;color:#9ca3af;"></td>
                    <td style="padding:6px 10px;text-align:center;color:#374151;">${item.height !== '' ? item.height : '-'}</td>
                    <td style="padding:6px 10px;text-align:center;color:#374151;">${item.width !== '' ? item.width : '-'}</td>
                    <td style="padding:6px 10px;text-align:center;font-weight:600;color:#1d4ed8;">${item.quantity}</td>
                    <td style="padding:6px 10px;text-align:center;color:#374151;">${escHtml(item.beveled) || '-'}</td>
                    <td style="padding:6px 10px;text-align:center;color:#374151;">${escHtml(item.edge_h1) || '-'}</td>
                    <td style="padding:6px 10px;text-align:center;color:#374151;">${escHtml(item.edge_h2) || '-'}</td>
                    <td style="padding:6px 10px;text-align:center;color:#374151;">${escHtml(item.edge_w1) || '-'}</td>
                    <td style="padding:6px 10px;text-align:center;color:#374151;">${escHtml(item.edge_w2) || '-'}</td>
                    <td style="padding:6px 10px;text-align:center;color:#374151;">${item.straight > 0 ? item.straight : '-'}</td>
                    <td style="padding:6px 10px;text-align:center;color:#374151;">${item.vat > 0 ? item.vat : '-'}</td>
                    <td style="padding:6px 10px;text-align:center;color:#374151;">${item.ban_rong_25_35 > 0 ? item.ban_rong_25_35 : '-'}</td>
                    <td style="padding:6px 10px;text-align:center;color:#374151;">${item.ban_rong_40_59 > 0 ? item.ban_rong_40_59 : '-'}</td>
                    <td style="padding:6px 10px;text-align:center;color:#374151;">${item.ban_rong_17_39 > 0 ? item.ban_rong_17_39 : '-'}</td>
                    <td style="padding:6px 10px;color:#6b7280;font-style:italic;max-width:150px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${escHtml(item.notes)}">${escHtml(item.notes)}</td>
                </tr>
            `;
        });
        
        html += `
            <tbody style="border-bottom:2px solid #e2e8f0;">
                <!-- Group Header -->
                <tr style="background:#ede9fe;">
                    <td colspan="15" style="padding:7px 12px;font-weight:700;color:#6d28d9;font-size:12px;border:1px solid #e2e8f0;border-bottom:none;">
                        <iconify-icon icon="lucide:package" style="margin-right:6px;font-size:13px;vertical-align:middle;"></iconify-icon>
                        Vật tư: <span style="background:#fff;border:1px solid #c4b5fd;border-radius:6px;padding:1px 8px;margin-left:4px;vertical-align:middle;">${escHtml(group.supply_name || 'Vật tư')}</span>
                        <span style="color:#94a3b8;font-weight:400;margin-left:8px;vertical-align:middle;">(${group.items.length} sản phẩm)</span>
                    </td>
                </tr>
                <!-- Items -->
                ${itemsHtml}
            </tbody>
        `;
    });

    tbody.innerHTML = html;

    const srvContainer = document.getElementById('min-late-excel-services-container');
    const srvTbody = document.getElementById('min-late-excel-services-tbody');
    
    if (services && services.length > 0) {
        if(srvContainer) srvContainer.style.display = 'block';
        let srvHtml = '';
        services.forEach(srv => {
            const priceFmt = new Intl.NumberFormat('vi-VN').format(srv.price);
            const priceOnlyFmt = srv.priceOnly > 0 ? new Intl.NumberFormat('vi-VN').format(srv.priceOnly) : '';
            const totalPriceFmt = srv.totalPrice !== '' ? new Intl.NumberFormat('vi-VN').format(srv.totalPrice) : '';
            srvHtml += `
                <tr>
                    <td style="padding:8px 10px;border-bottom:1px solid #e2e8f0;font-weight:500;color:#1e293b;white-space:pre-wrap;">${escHtml(srv.name)}</td>
                    <td style="padding:8px 10px;border-bottom:1px solid #e2e8f0;text-align:center;">${escHtml(srv.unit)}</td>
                    <td style="padding:8px 10px;border-bottom:1px solid #e2e8f0;text-align:center;font-weight:600;color:#0369a1;">${srv.qty}</td>
                    <td style="padding:8px 10px;border-bottom:1px solid #e2e8f0;text-align:right;color:#ef4444;font-weight:600;">${priceFmt}</td>
                    <td style="padding:8px 10px;border-bottom:1px solid #e2e8f0;text-align:right;color:#f97316;font-weight:600;">${priceOnlyFmt}</td>
                    <td style="padding:8px 10px;border-bottom:1px solid #e2e8f0;text-align:right;color:#059669;font-weight:700;">${totalPriceFmt}</td>
                </tr>
            `;
        });
        if(srvTbody) srvTbody.innerHTML = srvHtml;
    } else {
        if(srvContainer) srvContainer.style.display = 'none';
        if(srvTbody) srvTbody.innerHTML = '';
    }

    countEl.innerHTML = `<b style="color:#6d28d9;">${groups.length} nhóm</b>&nbsp;·&nbsp;<b style="color:#1d4ed8;">${totalItems} sản phẩm</b>${services && services.length > 0 ? `&nbsp;·&nbsp;<b style="color:#059669;">${services.length} dịch vụ</b>` : ''} sẽ được nhập`;

    document.getElementById('min-late-excel-import-backdrop').style.display = 'block';
    document.getElementById('min-late-excel-import-modal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeMinLateExcelImport() {
    document.getElementById('min-late-excel-import-backdrop').style.display = 'none';
    document.getElementById('min-late-excel-import-modal').style.display = 'none';
    document.body.style.overflow = '';
    _minLateExcelSupplyGroups = [];
}

function confirmMinLateExcelImport() {
    if (!_minLateExcelSupplyGroups || _minLateExcelSupplyGroups.length === 0) return;

    const suppliesContainer = document.getElementById('min-late-supplies-container');
    if (!suppliesContainer) return;

    _minLateExcelSupplyGroups.forEach(group => {
        addMinLateOrderSupply();
        const newSupplyRow = suppliesContainer.querySelector('.order-supply-row:last-child');
        if (!newSupplyRow) return;

        const supplyNameInput = newSupplyRow.querySelector('input[name*="[supply_name]"]');
        if (supplyNameInput) {
            supplyNameInput.value = group.supply_name;
        }

        const itemsContainer = newSupplyRow.querySelector('.supply-items-container');
        if (!itemsContainer) return;

        // Clear existing empty default items
        itemsContainer.innerHTML = '';
        const addItemBtn = newSupplyRow.querySelector('button[onclick^="addMinLateOrderItem"]');

        group.items.forEach(item => {
            if (addItemBtn) {
                addMinLateOrderItem(addItemBtn, true);
                const newRow = itemsContainer.lastElementChild;
                if (!newRow) return;

                const setVal = (sel, val) => {
                    if (val === '' || val === null || val === undefined) return;
                    const el = newRow.querySelector(sel);
                    if (el) {
                        if (el.tagName.toLowerCase() === 'select' && el.tomselect) {
                            el.tomselect.setValue(val);
                        } else {
                            el.value = val;
                            el.setAttribute('data-exact-value', val);
                        }
                    }
                };

                setVal('input[name*="[height]"]', item.height);
                setVal('input[name*="[width]"]', item.width);
                setVal('input[name*="[quantity]"]', item.quantity);
                setVal('input[name*="[beveled_edges]"]', item.beveled);
                setVal('select[name*="[edge_gluing][height_1]"]', item.edge_h1);
                setVal('select[name*="[edge_gluing][height_2]"]', item.edge_h2);
                setVal('select[name*="[edge_gluing][width_1]"]', item.edge_w1);
                setVal('select[name*="[edge_gluing][width_2]"]', item.edge_w2);
                setVal('input[name*="[notes]"]', item.notes);

                // OVERRIDE auto-calculated values with explicit calculated values from Excel (if provided)
                setVal('input[name*="[straight_paste_length]"]', item.straight);
                setVal('input[name*="[beveled_length]"]', item.vat);
                setVal('input[name*="[vat_moi_length]"]', item.vatMoi);
                setVal('input[name*="[ban_rong_25_35]"]', item.ban25);
                setVal('input[name*="[ban_rong_40_59]"]', item.ban40);
                setVal('input[name*="[ban_rong_17_39]"]', item.ban17);
                setVal('input[name*="[beveled_handle]"]', item.beveledHandle);
                setVal('input[name*="[cnc]"]', item.cnc);

                if(typeof bindMinLateRowEvents === 'function') bindMinLateRowEvents(newRow);
                if(typeof initMinLateBevelField === 'function') initMinLateBevelField(newRow);
            }
        });
    });

    if (typeof updateMinLateRowIndexes === 'function') updateMinLateRowIndexes();
    if (typeof updateOrderSummary === 'function') updateOrderSummary();

    // Import Services
    if (typeof _minLateExcelServices !== 'undefined' && _minLateExcelServices.length > 0) {
        const section = document.getElementById('payment-details-section');
        const btn = document.getElementById('toggle-payment-details-btn');
        if (section && btn) {
            section.style.display = 'block';
            btn.className = 'btn btn-sm bg-danger-50 text-danger-600 hover:bg-danger-100 border border-danger-200 rounded-lg flex items-center gap-1';
            btn.innerHTML = '<iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon> <span>Xóa chi tiết hóa đơn</span>';
        }
        _minLateExcelServices.forEach(srv => {
            if(typeof addPaymentDetail === 'function') {
                addPaymentDetail();
                const paymentContainer = document.getElementById('payment-details-container');
                if(paymentContainer) {
                    const newRow = paymentContainer.querySelector('.payment-detail-row:last-child');
                    if(newRow) {
                        const idxMatch = newRow.innerHTML.match(/payment_details\[(\d+)\]/);
                        if(idxMatch) {
                            const idx = idxMatch[1];
                            const setVal = (name, val) => {
                                const el = newRow.querySelector(`[name="payment_details[${idx}][${name}]"]`);
                                if(el) el.value = val;
                            };
                            setVal('name', srv.name);
                            setVal('unit', srv.unit);
                            setVal('quantity', srv.qty);
                            setVal('price', srv.price);
                            setVal('price_only', srv.priceOnly);
                            
                            // trigger calculation
                            if (typeof bindPaymentDetailEvents === 'function') {
                                const qtyInput = newRow.querySelector('.payment-quantity-input');
                                if(qtyInput) qtyInput.dispatchEvent(new Event('input', {bubbles: true}));
                            }

                            // OVERRIDE total_price if explicitly available from Excel
                            if (srv.totalPrice !== undefined && srv.totalPrice !== '') {
                                const totalEl = newRow.querySelector(`[name="payment_details[${idx}][total]"]`);
                                if (totalEl) {
                                    totalEl.value = Number(srv.totalPrice).toLocaleString('vi-VN');
                                    totalEl.setAttribute('data-exact-value', srv.totalPrice);
                                }
                            }
                        }
                    }
                }
            }
        });
        
        // Highlight payment details container to show they were added
        const paymentContainer = document.getElementById('payment-details-container');
        if (paymentContainer) {
            paymentContainer.style.transition = 'box-shadow 0.3s';
            paymentContainer.style.boxShadow = '0 0 0 3px #3b82f6';
            setTimeout(() => { paymentContainer.style.boxShadow = ''; }, 1500);
        }
    }

    if (suppliesContainer) {
        suppliesContainer.style.transition = 'box-shadow 0.3s';
        suppliesContainer.style.boxShadow = '0 0 0 3px #10b981';
        setTimeout(() => { suppliesContainer.style.boxShadow = ''; }, 1500);
    }

    closeMinLateExcelImport();
}

function togglePaymentDetailsSection() {
    const section = document.getElementById('payment-details-section');
    const btn = document.getElementById('toggle-payment-details-btn');
    const container = document.getElementById('payment-details-container');
    if (!section || !btn) return;

    if (section.style.display === 'none') {
        section.style.display = 'block';
        btn.className = 'btn btn-sm bg-danger-50 text-danger-600 hover:bg-danger-100 border border-danger-200 rounded-lg flex items-center gap-1';
        btn.innerHTML = '<iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon> <span>Xóa chi tiết hóa đơn</span>';
        
        if (container && container.querySelectorAll('.payment-detail-row').length === 0) {
            addPaymentDetail();
        }
    } else {
        const rows = container ? container.querySelectorAll('.payment-detail-row') : [];
        if (rows.length > 0) {
            if (!confirm('Bạn có chắc chắn muốn xóa toàn bộ chi tiết hóa đơn?')) {
                return;
            }
        }
        section.style.display = 'none';
        btn.className = 'btn btn-sm bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 rounded-lg flex items-center gap-1';
        btn.innerHTML = '<iconify-icon icon="lucide:receipt" class="text-lg"></iconify-icon> <span>Thêm chi tiết hóa đơn</span>';
        
        if (container) {
            container.innerHTML = '';
        }
        updateOrderSummary();
    }
}
</script>

<div id="min-late-excel-import-backdrop" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(15,23,42,0.4);backdrop-filter:blur(4px);z-index:9998;transition:all 0.3s ease;"></div>
<div id="min-late-excel-import-modal" style="display:none;position:fixed;inset:0;z-index:99999;align-items:center;justify-content:center;padding:20px;">
    <div style="width:min(1450px,98vw);height:90vh;background:#fff;border-radius:16px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1);display:flex;flex-direction:column;overflow:hidden;animation:excelModalIn 0.3s cubic-bezier(0.16,1,0.3,1);">
        <!-- Header -->
        <div style="padding:16px 24px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;background:#f8fafc;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;border-radius:10px;background:#d1fae5;display:flex;align-items:center;justify-content:center;">
                    <iconify-icon icon="lucide:file-spreadsheet" style="font-size:22px;color:#10b981;"></iconify-icon>
                </div>
                <div>
                    <div style="font-size:16px;font-weight:700;color:#1f2937;">Nhập từ Excel</div>
                    <div id="min-late-excel-import-filename" style="font-size:12px;color:#6b7280;margin-top:2px;">-</div>
                </div>
            </div>
            <button type="button" onclick="closeMinLateExcelImport()" class="w-8 h-8 rounded-lg hover:bg-neutral-100 flex items-center justify-center text-neutral-400 hover:text-danger-500 transition-colors" style="border:none;background:transparent;">
                <iconify-icon icon="lucide:x" style="font-size:18px;"></iconify-icon>
            </button>
        </div>
        <!-- Body -->
        <div style="flex:1;overflow:hidden;display:flex;flex-direction:column;background:#fff;">
            <div style="padding:14px 24px 0;flex-shrink:0;">
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;">
                    <div style="font-size:12px;font-weight:700;color:#065f46;margin-bottom:6px;display:flex;align-items:center;gap:6px;">
                        <iconify-icon icon="lucide:info" style="font-size:14px;"></iconify-icon>
                        Định dạng cột Excel (Gia công Late) — hàng 13 là tiêu đề, từ hàng 14 là dữ liệu
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;font-size:11px;">
                        <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:999px;font-weight:600;">Tên vật tư</span>
                        <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:999px;font-weight:600;">Cao</span>
                        <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:999px;font-weight:600;">Rộng</span>
                        <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:999px;font-weight:600;">Số lượng</span>
                        <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:999px;font-weight:600;">Cạnh Vát</span>
                        <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:999px;font-weight:600;">Nẹp Cao 1</span>
                        <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:999px;font-weight:600;">Nẹp Cao 2</span>
                        <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:999px;font-weight:600;">Nẹp Rộng 1</span>
                        <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:999px;font-weight:600;">Nẹp Rộng 2</span>
                        <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:999px;font-weight:600;">Số mét dán Thẳng/Vát/Bản Rộng</span>
                        <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:999px;font-weight:600;">Ghi chú</span>
                    </div>
                    <div style="font-size:11px;color:#6b7280;margin-top:6px;">
                        Các nhóm vật tư được ngăn cách bằng STT chữ cái hoặc số La Mã (A, B, C, I, II...). Hệ thống tự động nhận diện theo vị trí cột.
                    </div>
                </div>
            </div>

            <div style="flex:1;overflow-y:auto;padding:14px 24px;">
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:12px;min-width:800px;text-align:left;">
                        <thead style="background:#f8fafc;position:sticky;top:0;z-index:2;" id="min-late-excel-preview-thead">
                            {{-- Injected via JS --}}
                        </thead>
                        <tbody id="min-late-excel-preview-tbody">
                            {{-- Injected via JS --}}
                        </tbody>
                    </table>
                    <div id="min-late-excel-preview-empty" style="display:none;text-align:center;padding:40px;color:#94a3b8;">
                        <iconify-icon icon="lucide:file-x-2" style="font-size:36px;"></iconify-icon>
                        <div style="margin-top:8px;">Không tìm thấy dữ liệu hợp lệ</div>
                    </div>
                </div>
                
                <div id="min-late-excel-services-container" style="display:none;margin-top:24px;border-top:1px dashed #cbd5e1;padding-top:16px;">
                    <div style="font-size:14px;font-weight:700;color:#1e293b;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                        <iconify-icon icon="lucide:receipt" style="color:#3b82f6;font-size:16px;"></iconify-icon>
                        Dịch vụ / Phụ phí đính kèm
                    </div>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:12px;min-width:600px;text-align:left;">
                            <thead style="background:#f8fafc;">
                                <tr>
                                    <th style="padding:8px 10px;text-align:left;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;">Nội dung</th>
                                    <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;width:80px;">Đơn vị</th>
                                    <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;width:80px;">Số lượng</th>
                                    <th style="padding:8px 10px;text-align:right;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;width:110px;">Đơn giá</th>
                                    <th style="padding:8px 10px;text-align:right;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;width:110px;">Đơn giá chỉ</th>
                                    <th style="padding:8px 10px;text-align:right;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;width:120px;">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody id="min-late-excel-services-tbody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer -->
        <div style="padding:14px 24px;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;background:#f8fafc;">
            <div id="min-late-excel-import-count" style="font-size:13px;color:#64748b;">
                0 nhóm · 0 sản phẩm
            </div>
            <div style="display:flex;gap:10px;">
                <button type="button" onclick="closeMinLateExcelImport()" style="padding:9px 20px;border:1.5px solid #d1d5db;border-radius:8px;background:#fff;color:#374151;font-size:13px;font-weight:600;cursor:pointer;">
                    Hủy bỏ
                </button>
                <button type="button" id="min-late-excel-import-confirm-btn" onclick="confirmMinLateExcelImport()" style="padding:9px 20px;border:none;border-radius:8px;background:linear-gradient(135deg,#059669,#10b981);color:#fff;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;box-shadow:0 4px 6px -1px rgba(16, 185, 129, 0.2);">
                    <iconify-icon icon="lucide:check" style="font-size:15px;"></iconify-icon>
                    Tiến hành nhập
                </button>
            </div>
        </div>
    </div>
</div>

