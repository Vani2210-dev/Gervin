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
    #min-late-supplies-container .order-supply-row table th {
        padding: 8px 4px !important;
    }
    #min-late-supplies-container .order-supply-row table td {
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
    /* Style TomSelect inside table rows to match compact inputs */
    .table .ts-wrapper {
        padding: 0 !important;
        border: none !important;
        background: transparent !important;
        min-height: auto !important;
        height: 24px !important;
    }
    .table .ts-control {
        padding: 0 5px !important;
        height: 24px !important;
        font-size: 9px !important;
        line-height: 22px !important;
        border-radius: 6px !important;
        border: 1px solid #d1d5db !important;
        background-color: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        box-shadow: none !important;
    }
    .table .ts-control input {
        font-size: 9px !important;
        height: auto !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .table .ts-control .item {
        font-size: 9px !important;
        line-height: 22px !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .table .ts-wrapper.single .ts-control:after {
        top: 50% !important;
        margin-top: -3px !important;
    }
    .table .ts-wrapper.focus .ts-control {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 1px #3b82f6 !important;
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
                        <button type="button" onclick="this.closest('.order-supply-row').remove(); updateOrderSummary();" class="order-table-form-action text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
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
                                <th scope="col" style="min-width: 180px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên hàng hóa, dịch vụ <span class="text-danger-500">*</span></th>
                                <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Độ dày</th>
                                <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="align-middle border border-neutral-200 bg-yellow-100/70 font-bold text-xs text-neutral-600 uppercase text-center">Cao (vân)</th>
                                <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase text-center">Rộng</th>
                                <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng</th>
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
                                <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="Cao (vân)" step="any" value="{{ $size['height'] ?? '' }}">
                                </td>
                                <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Rộng" step="any" value="{{ $size['width'] ?? '' }}">
                                </td>
                                <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Số lượng" min="1" required value="{{ $item->quantity }}">
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

{{-- Bảng chi tiết hóa đơn dùng chung các tính năng hiển thị như bảng vật tư --}}
<div class="order-supplies-popup bg-white border border-neutral-200 rounded-xl p-6 shadow-sm mt-6 relative pt-8" data-order-supplies-zoom-panel data-order-supplies-storage-key="min_late_payment" data-order-supplies-zoom="100" data-order-supplies-visible-rows-disabled="1">
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
                            <th scope="col" style="width: 150px; min-width: 150px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá chỉ gỗ</th>
                            <th scope="col" style="width: 160px; min-width: 160px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                            <th scope="col" style="width: 50px; min-width: 50px; white-space: nowrap; position: sticky; right: 0; z-index: 2; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase bg-neutral-50">Xóa</th>
                        </tr>
                    </thead>
                    <tbody id="payment-details-container">
                        @if(isset($acrylicOrder) && $acrylicOrder->type == 'min_late' && isset($acrylicOrder->paymentDetails) && $acrylicOrder->paymentDetails->count() > 0)
                            @foreach($acrylicOrder->paymentDetails as $detailIndex => $detail)
                            <tr class="payment-detail-row">
                                <td style="width: 50px; min-width: 50px; " class="sticky-stt-td text-center align-middle border border-neutral-200">
                                    <span class="detail-index font-semibold text-neutral-500">{{ $detailIndex + 1 }}</span>
                                </td>
                                <td class="border border-neutral-200">
                                    <input type="text" name="payment_details[{{ $detailIndex }}][name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Nhập tên chi phí/dịch vụ" required value="{{ $detail->name }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="text" name="payment_details[{{ $detailIndex }}][unit]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="m, tấm..." value="{{ $detail->unit }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="payment_details[{{ $detailIndex }}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="1" step="any" value="{{ $detail->quantity }}">
                                </td>
                                <td style="width: 150px; min-width: 150px; " class="border border-neutral-200">
                                    <input type="number" name="payment_details[{{ $detailIndex }}][price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="0" min="0" step="any" required value="{{ $detail->price }}">
                                </td>
                                <td style="width: 150px; min-width: 150px; " class="border border-neutral-200">
                                    <input type="number" name="payment_details[{{ $detailIndex }}][price_only]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="0" min="0" step="any" value="{{ $detail->price_only }}">
                                </td>
                                <td style="width: 160px; min-width: 160px; " class="border border-neutral-200">
                                    <input type="number" name="payment_details[{{ $detailIndex }}][total]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center h-8 text-xs" placeholder="0" step="any" readonly value="{{ $detail->total }}">
                                </td>
                                <td style="width: 50px; min-width: 50px; position: sticky; right: 0; z-index: 1; background-color: #fff; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="text-center align-middle border border-neutral-200">
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

<script>
let minLateSupplyIndex = {{ $minLateSupplyIndex ?? 0 }};

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
                <button type="button" onclick="this.closest('.order-supply-row').remove(); updateOrderSummary();" class="order-table-form-action text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
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
                        <th scope="col" style="min-width: 180px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên hàng hóa, dịch vụ <span class="text-danger-500">*</span></th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Độ dày</th>
                        <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="align-middle border border-neutral-200 bg-yellow-100/70 font-bold text-xs text-neutral-600 uppercase text-center">Cao (vân)</th>
                        <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase text-center">Rộng</th>
                        <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng</th>
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
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="Cao (vân)" step="any" value="${lastData ? lastData.height : ''}">
        </td>
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Rộng" step="any" value="${lastData ? lastData.width : ''}">
        </td>
        <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Số lượng" min="1" required value="${lastData ? lastData.quantity : '1'}">
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
    const straightLength = Math.ceil(((sumT * quantity) / 1000) * 100) / 100;
    
    // 2. Beveled length ("V")
    let sumV = 0;
    if (h1 === 'V') sumV += height;
    if (h2 === 'V') sumV += height;
    if (w1 === 'V') sumV += width;
    if (w2 === 'V') sumV += width;
    const beveledLength = Math.ceil(((sumV * quantity) / 1000) * 100) / 100;
    
    // 3. Vát mòi length ("VAT MOI")
    let sumVatMoi = 0;
    if (h1 === 'VAT MOI') sumVatMoi += height;
    if (h2 === 'VAT MOI') sumVatMoi += height;
    if (w1 === 'VAT MOI') sumVatMoi += width;
    if (w2 === 'VAT MOI') sumVatMoi += width;
    const vatMoiLength = Math.ceil(((sumVatMoi * quantity) / 1000) * 100) / 100;
    
    // 3.5. Dán bản rộng 25-35mm ("DS")
    let sumDS = 0;
    if (h1 === 'DS') sumDS += height;
    if (h2 === 'DS') sumDS += height;
    if (w1 === 'DS') sumDS += width;
    if (w2 === 'DS') sumDS += width;
    const banRong2535Length = Math.ceil(((sumDS * quantity) / 1000) * 100) / 100;
    
    // Set outputs
    const straightInput = row.querySelector('input[name*="[straight_paste_length]"]');
    if (straightInput) {
        straightInput.value = straightLength.toFixed(2);
    }
    
    const beveledInput = row.querySelector('input[name*="[beveled_length]"]');
    if (beveledInput) {
        beveledInput.value = beveledLength.toFixed(2);
    }
    
    const vatMoiInput = row.querySelector('input[name*="[vat_moi_length]"]');
    if (vatMoiInput) {
        vatMoiInput.value = vatMoiLength.toFixed(2);
    }
    
    const banRong2535Input = row.querySelector('input[name*="[ban_rong_25_35]"]');
    if (banRong2535Input) {
        banRong2535Input.value = banRong2535Length.toFixed(2);
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
    }
    
    const banRong17_39Input = row.querySelector('input[name*="[ban_rong_17_39]"]');
    if (banRong17_39Input) {
        banRong17_39Input.value = banRong17_39Length.toFixed(2);
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




// Initial setup for Min Late-specific rows if DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('min-late-supplies-container');
    if (container && container.querySelectorAll('.order-supply-row').length === 0) {
        addMinLateOrderSupply();
    }

    ensureAllMinLateFormLayouts();

    document.querySelectorAll('.order-item-row').forEach(row => {
        bindMinLateRowEvents(row);
        calculateMinLateRowStats(row);
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

function addPaymentDetail() {
    const container = document.getElementById('payment-details-container');
    if (!container) return;
    
    const newRow = document.createElement('tr');
    newRow.className = 'payment-detail-row';
    newRow.innerHTML = `
        <td style="width: 50px; min-width: 50px; " class="sticky-stt-td text-center align-middle border border-neutral-200">
            <span class="detail-index font-semibold text-neutral-500">${paymentDetailIndex + 1}</span>
        </td>
        <td class="border border-neutral-200">
            <input type="text" name="payment_details[${paymentDetailIndex}][name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Nhập tên chi phí/dịch vụ" required>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="payment_details[${paymentDetailIndex}][unit]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="m, tấm...">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="payment_details[${paymentDetailIndex}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="1" step="any" value="1">
        </td>
        <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
            <input type="number" name="payment_details[${paymentDetailIndex}][price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="0" min="0" step="any" required value="0">
        </td>
        <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
            <input type="number" name="payment_details[${paymentDetailIndex}][price_only]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="0" min="0" step="any" value="0">
        </td>
        <td style="width: 120px; min-width: 120px; " class="border border-neutral-200">
            <input type="number" name="payment_details[${paymentDetailIndex}][total]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center h-8 text-xs" placeholder="0" step="any" readonly value="0">
        </td>
        <td style="width: 50px; min-width: 50px; position: sticky; right: 0; z-index: 1; background-color: #fff; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="text-center align-middle border border-neutral-200">
            <button type="button" onclick="removePaymentDetail(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa nội dung này">
                <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
            </button>
        </td>
    `;
    container.appendChild(newRow);
    
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
    const totalInput = row.querySelector('input[name*="[total]"]');
    
    if (!qtyInput || !priceInput || !totalInput) return;
    
    const quantity = parseFloat(qtyInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    const total = quantity * price;
    
    totalInput.value = Math.round(total);
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
    const inputs = row.querySelectorAll('input[name*="[quantity]"], input[name*="[price]"]');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            calculatePaymentDetailRowTotal(row);
        });
    });
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

    // Ensure at least one payment detail row exists on page load if none loaded
    const container = document.getElementById('payment-details-container');
    if (container && container.querySelectorAll('.payment-detail-row').length === 0) {
        addPaymentDetail();
    } else {
        updatePaymentDetailIndexes();
    }
});
</script>
