{{-- Order Supplies & Items Section Card --}}
<style>
    .input-narrow-warning,
    input.input-narrow-warning[type='number'],
    #order-supplies-container .order-supply-row table input.input-narrow-warning {
        background-color: #fef2f2 !important;
        color: #ef4444 !important;
        font-weight: bold !important;
        border: 0 !important;
    }
    .input-narrow-warning:focus,
    input.input-narrow-warning[type='number']:focus,
    #order-supplies-container .order-supply-row table input.input-narrow-warning:focus {
        background-color: #fef2f2 !important;
        color: #ef4444 !important;
        font-weight: bold !important;
        outline: 2px solid #ef4444 !important;
        outline-offset: -2px !important;
        box-shadow: none !important;
    }

    /* === COMPACT TABLE: 75% font scale === */
    #order-supplies-container .order-supply-row table {
        font-size: 75% !important;
    }
    #order-supplies-container .order-supply-row table input,
    #order-supplies-container .order-supply-row table select,
    #order-supplies-container .order-supply-row table textarea {
        font-size: 75% !important;
        height: 24px !important;
        min-height: 24px !important;
        padding-top: 2px !important;
        padding-bottom: 2px !important;
        line-height: 1.2 !important;
    }
    #order-supplies-container .order-supply-row table th {
        padding: 8px 4px !important;
    }
    #order-supplies-container .order-supply-row table td {
        padding: 3px 4px !important;
    }
    /* Override all td max-widths to 65% of original */
    #order-supplies-container .order-supply-row table td[style*="width: 45px"],
    #order-supplies-container .order-supply-row table td[style*="width:45px"] { width: 30px !important; min-width: 30px !important; max-width: 30px !important; }
    #order-supplies-container .order-supply-row table td[style*="width: 160px"],
    #order-supplies-container .order-supply-row table td[style*="width:160px"] { width: 110px !important; min-width: 110px !important; max-width: 110px !important; }
    #order-supplies-container .order-supply-row table td[style*="min-width: 220px"],
    #order-supplies-container .order-supply-row table td[style*="min-width:220px"] { min-width: 150px !important; }
    #order-supplies-container .order-supply-row table td[style*="width: 200px"],
    #order-supplies-container .order-supply-row table td[style*="width:200px"] { width: 130px !important; min-width: 130px !important; max-width: 130px !important; }
    #order-supplies-container .order-supply-row table td[style*="width: 70px"],
    #order-supplies-container .order-supply-row table td[style*="width:70px"] { width: 50px !important; min-width: 50px !important; max-width: 50px !important; }
    #order-supplies-container .order-supply-row table td[style*="width: 100px"],
    #order-supplies-container .order-supply-row table td[style*="width:100px"] { width: 70px !important; min-width: 70px !important; max-width: 70px !important; }
    #order-supplies-container .order-supply-row table td[style*="width: 110px"],
    #order-supplies-container .order-supply-row table td[style*="width:110px"] { width: 80px !important; min-width: 80px !important; max-width: 80px !important; }
    #order-supplies-container .order-supply-row table td[style*="width: 120px"],
    #order-supplies-container .order-supply-row table td[style*="width:120px"] { width: 90px !important; min-width: 90px !important; max-width: 90px !important; }
    #order-supplies-container .order-supply-row table td[style*="min-width: 160px"],
    #order-supplies-container .order-supply-row table td[style*="min-width:160px"],
    #order-supplies-container .order-supply-row table td[style*="width: 160px"],
    #order-supplies-container .order-supply-row table td[style*="width:160px"] { min-width: 120px !important; }
    #order-supplies-container .order-supply-row table td[style*="width: 80px"],
    #order-supplies-container .order-supply-row table td[style*="width:80px"] { width: 60px !important; min-width: 60px !important; max-width: 60px !important; }
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

    /* Style TomSelect for Mã vật tư dropdown in the header row */
    .order-supply-row .ts-wrapper.tom-select-supply-code {
        width: 190px !important;
        display: block;
        flex-shrink: 0;
    }
    /* Hide dropdown arrow - make it look like a plain text input */
    .order-supply-row .ts-wrapper.tom-select-supply-code.single .ts-control:after {
        display: none !important;
    }
    .order-supply-row .ts-wrapper.tom-select-supply-code .ts-control {
        padding: 0 12px !important;
        font-size: 13px !important;
        border-radius: 8px !important;
        min-height: 45px !important;
        height: 45px !important;
        line-height: 1.4 !important;
        box-sizing: border-box !important;
        background-color: #ffffff !important;
        border: 1px solid #d1d5db !important;
        box-shadow: none !important;
        display: flex !important;
        align-items: center !important;
        flex-wrap: nowrap !important;
        overflow: hidden !important;
    }
    .order-supply-row .ts-wrapper.tom-select-supply-code .ts-control input {
        font-size: 13px !important;
        height: auto !important;
        padding: 0 !important;
        margin: 0 !important;
        min-width: 0 !important;
        flex: 1 !important;
    }
    .order-supply-row .ts-wrapper.tom-select-supply-code .ts-control .item {
        font-size: 13px !important;
        line-height: 20px !important;
        margin: 0 !important;
        padding: 0 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }
        padding: 8px 12px !important;
        font-size: 13px !important;
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
<div class="order-supplies-popup bg-white border border-neutral-200 rounded-xl p-6 shadow-sm relative pt-8" data-order-supplies-zoom-panel data-order-supplies-storage-key="acrylic" data-order-supplies-zoom="100" data-order-supplies-visible-rows="5">
    <div class="order-supplies-header flex items-center justify-between border-b border-neutral-100 pb-4 mb-5">
        <div class="absolute -top-3.5 left-6 bg-white px-3 flex items-center gap-2 z-10">
            <iconify-icon icon="lucide:package-open" class="text-xl text-primary-500"></iconify-icon>
            <h6 class="font-bold text-base text-neutral-800 m-0">Danh sách Vật tư & Sản phẩm (Acrylic)</h6>
        </div>
        <div class="flex flex-wrap items-center gap-2 ml-auto">
            <div class="flex items-center gap-2">
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
            <button type="button" onclick="toggleOrderSuppliesPopup(this)" class="btn btn-sm bg-light-100 hover:bg-neutral-200 text-dark rounded-lg flex items-center gap-1" data-order-supplies-popup-button aria-expanded="false">
                <iconify-icon icon="lucide:maximize-2" class="text-lg" data-order-supplies-popup-icon></iconify-icon>
                <span data-order-supplies-popup-label>Phóng to</span>
            </button>
            <button type="button" onclick="triggerGlobalExcelImport()" class="btn btn-sm bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200 rounded-lg flex items-center gap-1" title="Nhập danh sách từ file Excel">
                <iconify-icon icon="lucide:file-spreadsheet" class="text-lg"></iconify-icon>
                <span class="mobile-hide-text">Nhập Excel</span>
            </button>
            <input type="file" id="excel-global-file-input" accept=".xlsx,.xls,.csv" style="display:none;">
            <button type="button" onclick="addOrderSupply()" class="btn btn-sm btn-primary rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon> <span class="mobile-hide-text">Thêm vật tư</span>
            </button>
        </div>
    </div>
    <div class="order-supplies-body">
        <div id="order-supplies-container" class="space-y-6">
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
                            $item->height = $iData['height'] ?? '';
                            $item->width = $iData['width'] ?? '';
                            $item->quantity = $iData['quantity'] ?? 1;
                            $item->bevel = $iData['bevel'] ?? '';
                            $item->grain_direction = $iData['grain_direction'] ?? 0;
                            $item->wing_area = $iData['wing_area'] ?? '';
                            $item->molding_length = $iData['molding_length'] ?? '';
                            $item->edge_bevel = $iData['edge_bevel'] ?? '';
                            $item->vertical_grain_cnc = $iData['vertical_grain_cnc'] ?? null;
                            $item->offset_left = $iData['offset_left'] ?? '';
                            $item->offset_right = $iData['offset_right'] ?? '';
                            $item->offset_top = $iData['offset_top'] ?? '';
                            $item->offset_bottom = $iData['offset_bottom'] ?? '';
                            $item->mill_left = $iData['mill_left'] ?? '';
                            $item->mill_right = $iData['mill_right'] ?? '';
                            $item->mill_top = $iData['mill_top'] ?? '';
                            $item->mill_bottom = $iData['mill_bottom'] ?? '';
                            $item->mill_width = $iData['mill_width'] ?? '';
                            $item->mill_depth = $iData['mill_depth'] ?? '';
                            $item->mill_left_2 = $iData['mill_left_2'] ?? '';
                            $item->mill_right_2 = $iData['mill_right_2'] ?? '';
                            $item->mill_top_2 = $iData['mill_top_2'] ?? '';
                            $item->mill_bottom_2 = $iData['mill_bottom_2'] ?? '';
                            $item->mill_width_2 = $iData['mill_width_2'] ?? '';
                            $item->mill_depth_2 = $iData['mill_depth_2'] ?? '';
                            $item->unit_price = $iData['unit_price'] ?? 0;
                            $item->total_price = $iData['total_price'] ?? 0;
                            $item->notes = $iData['notes'] ?? '';
                            $item->is_labor = $iData['is_labor'] ?? 0;
                            $items->push($item);
                        }
                    }
                    $supply->items = $items;
                    $supplies->push($supply);
                }
            } elseif (isset($acrylicOrder) && in_array($acrylicOrder->type, ['acrylic', 'glass']) && $acrylicOrder->supplies->count() > 0) {
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
                        <select name="supplies[{{ $supplyIndex }}][order_supply_code]" class="order-supply-code-select tom-select-supply-code w-48">
                            <option value="">-- Mã vật tư --</option>
                            @foreach($woodBoardPrices as $price)
                                <option value="{{ $price->code }}" {{ (isset($supply->order_supply_code) && $supply->order_supply_code == $price->code) ? 'selected' : '' }}>{{ $price->code }}</option>
                            @endforeach
                            @if(isset($supply->order_supply_code) && !$woodBoardPrices->contains('code', $supply->order_supply_code))
                                <option value="{{ $supply->order_supply_code }}" selected>{{ $supply->order_supply_code }}</option>
                            @endif
                        </select>
                        <input type="text" name="supplies[{{ $supplyIndex }}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)" value="{{ $supply->supply_name }}">
                        <input type="number" name="supplies[{{ $supplyIndex }}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="any" value="{{ $supply->quantity ?? 0 }}">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="this.closest('.order-supply-row').remove()" class="text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                            <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto pb-3" data-order-supplies-table-scroll>
                    <div class="order-supplies-table-zoom-wrap" data-order-supplies-table-zoom-wrap>
                    <table class="table bordered-table sm-table mb-0 min-w-[1100px] border border-neutral-200">
                        <thead>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" style="width: 30px; min-width: 30px; white-space: nowrap;" class="sticky-stt-th align-middle text-center border border-neutral-200 font-bold text-xs text-neutral-600 uppercase"><span class="order-stt-header-label">STT</span></th>
                                <th scope="col" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã SP</th>
                                <th scope="col" style="width: 150px; min-width: 150px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên sản phẩm <span class="text-danger-500">*</span></th>
                                <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Độ dày</th>
                                <th scope="col" style="width: 130px; min-width: 130px; white-space: nowrap;" class="align-middle border border-neutral-200 bg-yellow-100/70 font-bold text-xs text-neutral-600 uppercase text-center">Cao (vân)</th>
                                <th scope="col" style="width: 130px; min-width: 130px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase text-center">Rộng</th>
                                <th scope="col" style="width: 50px; min-width: 50px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng <span class="text-danger-500">*</span></th>
                                <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Vát</th>
                                <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều vân</th>
                                <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Cánh (m2)</th>
                                <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Phào (m)</th>
                                <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Cạnh Vát</th>
                                <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Khoét kính</th>
                                <th scope="col" style="width: 80px; min-width: 80px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                                <th scope="col" style="width: 90px; min-width: 90px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                                <th scope="col" style="min-width: 120px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                                <th scope="col" style="width: 90px; min-width: 90px; position: sticky; right: 0; z-index: 3; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase bg-neutral-50">Hành động</th>
                            </tr>
                        </thead>
                        <tbody class="supply-items-container" data-supply-index="{{ $supplyIndex }}">
                            @foreach($supply->items as $itemIndex => $item)
                            @php $isLaborRow = ($item->product_name === 'Công giả dày'); @endphp
                            <tr class="order-item-row {{ $isLaborRow ? 'bg-amber-50/60' : '' }}" data-item-id="{{ $item->id }}" {{ $isLaborRow ? 'data-is-labor=1' : '' }}>
                                <td style="width: 45px; min-width: 45px; " class="sticky-stt-td text-center align-middle border border-neutral-200">
                                    <span class="row-index font-semibold text-neutral-500">{{ $itemIndex + 1 }}</span>
                                </td>
                                <td style="width: 160px; min-width: 160px; " class="border border-neutral-200">
                                    @if($isLaborRow)
                                        <div class="h-8 flex items-center justify-center text-xs text-amber-600 font-semibold bg-amber-50 px-1">
                                            <iconify-icon icon="lucide:hammer" style="margin-right:4px;"></iconify-icon> Công
                                        </div>
                                        <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_code]" value="">
                                    @else
                                        <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_code]" class="product-code-input form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs font-semibold text-neutral-600" readonly value="{{ $item->product_code ?? '' }}">
                                    @endif
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][is_labor]" value="{{ $isLaborRow ? '1' : '0' }}">
                                </td>
                                <td style="min-width: 220px;" class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_name]" class="form-control form-control-sm rounded-lg {{ $isLaborRow ? 'border-amber-300 focus:border-amber-500 focus:ring-amber-400 font-semibold text-amber-700' : 'border-neutral-300 focus:border-primary-500 focus:ring-primary-500' }} h-8 text-xs" placeholder="Tên sản phẩm" value="{{ $item->product_name }}">
                                </td>
                                <td style="width: 100px; min-width: 100px;" class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][thickness]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Độ dày" value="{{ $item->thickness }}">
                                </td>
                                <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="Cao (vân)" step="any" value="{{ $item->height }}">
                                </td>
                                <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Rộng" step="any" value="{{ $item->width }}">
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
                                               data-auto-sync="{{ ($item->bevel === '' || $item->bevel === null) ? 'none' : (($item->bevel == $item->width) ? 'width' : (($item->bevel == $item->height) ? 'height' : 'none')) }}">
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
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][grain_direction]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8 text-xs">
                                        <option value="0" {{ $item->grain_direction == 0 ? 'selected' : '' }}>0</option>
                                        <option value="2" {{ $item->grain_direction == 2 ? 'selected' : '' }}>2</option>
                                    </select>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][wing_area]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Cánh (m2)" step="any" value="{{ $item->wing_area }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][molding_length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Phào (m)" step="any" value="{{ $item->molding_length }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200 cursor-not-allowed">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_bevel]" class="product-edge-bevel-input form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Cạnh vát" value="{{ $item->edge_bevel }}" readonly tabindex="-1" style="pointer-events: none;">
                                </td>
                                <td style="width: 130px; min-width: 130px; " class="border border-neutral-200">
                                    <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][vertical_grain_cnc]" class="cnc-template-select form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8 text-xs" onchange="onCncTemplateChange(this)">
                                        <option value="">-- CNC --</option>
                                        @foreach($cncTemplates ?? [] as $cncTpl)
                                        <option value="{{ $cncTpl->name }}" data-params='{{ json_encode($cncTpl) }}' {{ ($item->vertical_grain_cnc === $cncTpl->name) ? 'selected' : '' }}>{{ $cncTpl->name }}</option>
                                        @endforeach
                                        @if($item->vertical_grain_cnc && !($cncTemplates ?? collect())->where('name', $item->vertical_grain_cnc)->count())
                                        <option value="{{ $item->vertical_grain_cnc }}" selected>{{ $item->vertical_grain_cnc }}</option>
                                        @endif
                                    </select>
                                    {{-- Hidden parameter fields --}}
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][offset_left]"   class="cnc-offset-left"   value="{{ $item->offset_left }}">
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][offset_right]"  class="cnc-offset-right"  value="{{ $item->offset_right }}">
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][offset_top]"    class="cnc-offset-top"    value="{{ $item->offset_top }}">
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][offset_bottom]" class="cnc-offset-bottom" value="{{ $item->offset_bottom }}">
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][mill_left]"     class="cnc-mill-left"     value="{{ $item->mill_left }}">
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][mill_right]"    class="cnc-mill-right"    value="{{ $item->mill_right }}">
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][mill_top]"      class="cnc-mill-top"      value="{{ $item->mill_top }}">
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][mill_bottom]"   class="cnc-mill-bottom"   value="{{ $item->mill_bottom }}">
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][mill_width]"    class="cnc-mill-width"    value="{{ $item->mill_width }}">
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][mill_depth]"    class="cnc-mill-depth"    value="{{ $item->mill_depth }}">
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][mill_left_2]"   class="cnc-mill-left-2"   value="{{ $item->mill_left_2 }}">
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][mill_right_2]"  class="cnc-mill-right-2"  value="{{ $item->mill_right_2 }}">
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][mill_top_2]"    class="cnc-mill-top-2"    value="{{ $item->mill_top_2 }}">
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][mill_bottom_2]" class="cnc-mill-bottom-2" value="{{ $item->mill_bottom_2 }}">
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][mill_width_2]"  class="cnc-mill-width-2"  value="{{ $item->mill_width_2 }}">
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][mill_depth_2]"  class="cnc-mill-depth-2"  value="{{ $item->mill_depth_2 }}">
                                </td>
                                <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][unit_price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Đơn giá" min="0" step="any" required value="{{ $item->unit_price }}">
                                </td>
                                <td style="width: 120px; min-width: 120px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][total_price]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center px-1 py-1 h-8 text-xs" placeholder="Thành tiền" step="any" value="{{ $item->total_price }}" readonly>
                                </td>
                                <td style="min-width: 160px;" class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Ghi chú" value="{{ $item->notes }}">
                                </td>
                                <td style="width: 90px; min-width: 90px; " class="text-center align-middle border border-neutral-200">
                                    <div class="flex items-center gap-1 justify-center">
                                        <button type="button" onclick="addFakeThicknessRow(this)" class="text-neutral-400 hover:text-warning-500 transition-colors p-1" title="Tạo công giả dày">
                                            <iconify-icon icon="lucide:layers" class="text-base"></iconify-icon>
                                        </button>
                                        <button type="button" onclick="duplicateAcrylicRow(this)" class="text-neutral-400 hover:text-primary-500 transition-colors p-1" title="Nhân bản sản phẩm">
                                            <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                                        </button>
                                        <button type="button" onclick="removeOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
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
                <div class="flex gap-2 mt-4">
                    <button type="button" onclick="addOrderItem(this)" class="flex-1 py-3 border-2 border-dashed border-primary-300 hover:border-primary-500 rounded-xl bg-primary-50/50 hover:bg-primary-50 text-primary-600 font-semibold text-sm flex items-center justify-center gap-1.5 transition-all duration-200">
                        <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon>
                        Thêm sản phẩm mới
                    </button>
                </div>
            </div>
            @endforeach
            @php $supplyIndex = $supplies->count() @endphp
        @else
            @php $supplyIndex = 0 @endphp
        @endif
        </div>
    </div>
</div>

<script>
const woodBoardPricesData = @json($woodBoardPrices);

function handleSupplyCodeChange(selectEl, value) {
    if (!value) return;
    
    const price = woodBoardPricesData.find(p => p.code === value);
    if (!price) return;
    
    const supplyRow = selectEl.closest('.order-supply-row');
    if (!supplyRow) return;
    
    const supplyNameInput = supplyRow.querySelector('input[name*="[supply_name]"]');
    if (supplyNameInput) {
        supplyNameInput.value = price.name || '';
        applyFlashEffect(supplyNameInput);
    }
    
    const itemRows = supplyRow.querySelectorAll('.order-item-row');
    itemRows.forEach(itemRow => {
        const thicknessInput = itemRow.querySelector('input[name*="[thickness]"]');
        const unitPriceInput = itemRow.querySelector('input[name*="[unit_price]"]');
        
        if (thicknessInput) {
            thicknessInput.value = price.thickness || '';
            applyFlashEffect(thicknessInput);
        }
        if (unitPriceInput) {
            unitPriceInput.value = price.price_m2 ? parseInt(price.price_m2) : 0;
            applyFlashEffect(unitPriceInput);
            calculateTotalPrice(itemRow, 'unit_price');
        }
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

let supplyIndex = {{ $supplyIndex ?? 0 }};

function addOrderSupply() {
    const container = document.getElementById('order-supplies-container');
    const newSupply = document.createElement('div');
    newSupply.className = 'order-supply-row bg-neutral-50/50 border border-primary-600 rounded-xl p-5 mb-2 relative shadow-sm';
    newSupply.innerHTML = `
        <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                    <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                </div>
                <select name="supplies[${supplyIndex}][order_supply_code]" class="order-supply-code-select tom-select-supply-code w-48">
                    <option value="">-- Mã vật tư --</option>
                    ${woodBoardPricesData.map(p => `<option value="${p.code}">${p.code}</option>`).join('')}
                </select>
                <input type="text" name="supplies[${supplyIndex}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)">
                <input type="number" name="supplies[${supplyIndex}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="0.01" value="0">
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="this.closest('.order-supply-row').remove()" class="text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                    <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto pb-3" data-order-supplies-table-scroll>
            <div class="order-supplies-table-zoom-wrap" data-order-supplies-table-zoom-wrap>
            <table class="table bordered-table sm-table mb-0 min-w-[1100px] border border-neutral-200">
                <thead>
                    <tr class="bg-neutral-50 text-center">
                        <th scope="col" style="width: 30px; min-width: 30px; white-space: nowrap;" class="sticky-stt-th align-middle text-center border border-neutral-200 font-bold text-xs text-neutral-600 uppercase"><span class="order-stt-header-label">STT</span></th>
                        <th scope="col" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã SP</th>
                        <th scope="col" style="width: 150px; min-width: 150px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên sản phẩm <span class="text-danger-500">*</span></th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Độ dày</th>
                        <th scope="col" style="width: 130px; min-width: 130px; white-space: nowrap;" class="align-middle border border-neutral-200 bg-yellow-100/70 font-bold text-xs text-neutral-600 uppercase text-center">Cao (vân)</th>
                        <th scope="col" style="width: 130px; min-width: 130px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase text-center">Rộng</th>
                        <th scope="col" style="width: 50px; min-width: 50px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng <span class="text-danger-500">*</span></th>
                        <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Vát</th>
                        <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều vân</th>
                        <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Cánh (m2)</th>
                        <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Phào (m)</th>
                        <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Cạnh Vát</th>
                        <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Khoét kính</th>
                        <th scope="col" style="width: 80px; min-width: 80px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                        <th scope="col" style="width: 90px; min-width: 90px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                        <th scope="col" style="min-width: 120px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                        <th scope="col" style="width: 60px; min-width: 60px; white-space: nowrap; position: sticky; right: 0; z-index: 3; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase bg-neutral-50">Hành động</th>
                    </tr>
                </thead>
                <tbody class="supply-items-container" data-supply-index="${supplyIndex}">
                </tbody>
            </table>
            </div>
        </div>
        <button type="button" onclick="addOrderItem(this)" class="w-full mt-4 py-3 border-2 border-dashed border-primary-300 hover:border-primary-500 rounded-xl bg-primary-50/50 hover:bg-primary-50 text-primary-600 font-semibold text-sm flex items-center justify-center gap-1.5 transition-all duration-200">
            <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon>
            Thêm sản phẩm mới
        </button>
    `;
    container.appendChild(newSupply);

    // Add one product row by default
    const addProductBtn = newSupply.querySelector('button[onclick^="addOrderItem"]');
    if (addProductBtn) {
        addOrderItem(addProductBtn, true);
    }

    const selectEl = newSupply.querySelector('.tom-select-supply-code');
    if (selectEl && typeof TomSelect !== 'undefined' && !selectEl.tomselect) {
        const ts = new TomSelect(selectEl, {
            create: true,
            placeholder: '-- Mã vật tư --',
            allowEmptyOption: true,
            maxOptions: null
        });
        ts.on('change', function(value) {
            handleSupplyCodeChange(selectEl, value);
            updateAcrylicRowIndexes();
        });
    }

    const panel = newSupply.closest('[data-order-supplies-zoom-panel]');
    if (panel) {
        applyOrderSuppliesZoom(panel, panel.dataset.orderSuppliesZoom || 100);
    }

    supplyIndex++;
}

function addOrderItem(button, isInitial = false) {
    const supplyRow = button.closest('.order-supply-row');
    const supplyIndex = supplyRow.querySelector('.supply-items-container').dataset.supplyIndex;
    const container = supplyRow.querySelector('.supply-items-container');
    const itemIndex = container.querySelectorAll('.order-item-row').length;
    
    // Lấy thông tin mã vật tư đã chọn để tự điền độ dày và đơn giá (nếu có)
    const selectEl = supplyRow.querySelector('.order-supply-code-select');
    let selectedCode = '';
    if (selectEl) {
        selectedCode = selectEl.tomselect ? selectEl.tomselect.getValue() : selectEl.value;
    }
    const price = woodBoardPricesData.find(p => p.code === selectedCode);
    const defaultThickness = price ? (price.thickness || '') : '';
    const defaultUnitPrice = price ? (price.price_m2 ? parseInt(price.price_m2) : 0) : '';
    
    // Sao chép đơn giá từ dòng cuối nếu có
    const lastRow = container.querySelector('.order-item-row:last-of-type');
    let copiedUnitPrice = '';
    if (lastRow) {
        const lastUnitPriceInput = lastRow.querySelector('input[name*="[unit_price]"]');
        if (lastUnitPriceInput && lastUnitPriceInput.value !== '') {
            copiedUnitPrice = lastUnitPriceInput.value;
        }
    }
    const unitPriceToUse = copiedUnitPrice !== '' ? copiedUnitPrice : defaultUnitPrice;
    
    const newItem = document.createElement('tr');
    newItem.className = 'order-item-row';
    newItem.innerHTML = `
        <td style="width: 45px; min-width: 45px; " class="sticky-stt-td text-center align-middle border border-neutral-200">
            <span class="row-index font-semibold text-neutral-500">${itemIndex + 1}</span>
        </td>
        <td style="width: 160px; min-width: 160px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][product_code]" class="product-code-input form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs font-semibold text-neutral-600" readonly>
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][is_labor]" value="0">
        </td>
        <td style="min-width: 220px;" class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Tên sản phẩm" value="">
        </td>
        <td style="width: 100px; min-width: 100px;" class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][thickness]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Độ dày" value="${defaultThickness}">
        </td>
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="Cao (vân)" step="any" value="">
        </td>
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Rộng" step="any" value="">
        </td>
        <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Số lượng" min="1" required value="1">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <div class="w-full h-full" style="position: relative;">
                <input type="text" 
                       name="supplies[${supplyIndex}][items][${itemIndex}][bevel]" 
                       class="product-bevel-input form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center pr-6 pl-1 py-1 h-8 text-xs w-full" 
                       placeholder="Vát" 
                       value=""
                       data-auto-sync="width">
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
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <select name="supplies[${supplyIndex}][items][${itemIndex}][grain_direction]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8 text-xs">
                <option value="0">0</option>
                <option value="2">2</option>
            </select>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][wing_area]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Cánh (m2)" step="any" value="">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][molding_length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Phào (m)" step="any" value="">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200 cursor-not-allowed">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][edge_bevel]" class="product-edge-bevel-input form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Cạnh vát" value="" readonly tabindex="-1" style="pointer-events: none;">
        </td>
        <td style="width: 130px; min-width: 130px; " class="border border-neutral-200">
            <select name="supplies[${supplyIndex}][items][${itemIndex}][vertical_grain_cnc]" class="cnc-template-select form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8 text-xs" onchange="onCncTemplateChange(this)">
                <option value="">-- CNC --</option>
                ${window.cncTemplatesData ? window.cncTemplatesData.map(t => `<option value="${t.name}" data-params='${JSON.stringify(t)}'>${t.name}</option>`).join('') : ''}
            </select>
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][offset_left]"   class="cnc-offset-left"   value="">
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][offset_right]"  class="cnc-offset-right"  value="">
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][offset_top]"    class="cnc-offset-top"    value="">
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][offset_bottom]" class="cnc-offset-bottom" value="">
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][mill_left]"     class="cnc-mill-left"     value="">
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][mill_right]"    class="cnc-mill-right"    value="">
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][mill_top]"      class="cnc-mill-top"      value="">
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][mill_bottom]"   class="cnc-mill-bottom"   value="">
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][mill_width]"    class="cnc-mill-width"    value="">
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][mill_depth]"    class="cnc-mill-depth"    value="">
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][mill_left_2]"   class="cnc-mill-left-2"   value="">
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][mill_right_2]"  class="cnc-mill-right-2"  value="">
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][mill_top_2]"    class="cnc-mill-top-2"    value="">
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][mill_bottom_2]" class="cnc-mill-bottom-2" value="">
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][mill_width_2]"  class="cnc-mill-width-2"  value="">
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][mill_depth_2]"  class="cnc-mill-depth-2"  value="">
        </td>
        <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][unit_price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Đơn giá" min="0" step="any" required value="${unitPriceToUse}">
        </td>
        <td style="width: 120px; min-width: 120px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][total_price]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center px-1 py-1 h-8 text-xs" placeholder="Thành tiền" step="any" readonly value="">
        </td>
        <td style="min-width: 160px;" class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Ghi chú" value="">
        </td>
        <td style="width: 90px; min-width: 90px; " class="text-center align-middle border border-neutral-200">
            <div class="flex items-center gap-1 justify-center">
                <button type="button" onclick="addFakeThicknessRow(this)" class="text-neutral-400 hover:text-warning-500 transition-colors p-1" title="Tạo công giả dày">
                    <iconify-icon icon="lucide:layers" class="text-base"></iconify-icon>
                </button>
                <button type="button" onclick="duplicateAcrylicRow(this)" class="text-neutral-400 hover:text-primary-500 transition-colors p-1" title="Nhân bản sản phẩm">
                    <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                </button>
                <button type="button" onclick="removeOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
                    <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                </button>
            </div>
        </td>
    `;
    container.appendChild(newItem);
    
    const newRow = container.lastElementChild;
    
    // Force table reflow to fix Chrome sticky cell border-collapse rendering bug
    const table = newRow.closest('table');

    
    bindAcrylicRowEvents(newRow);
    initBevelField(newRow);
    
    updateOrderSummary();
    updateAcrylicRowIndexes();

    // Tự động cuộn xuống dòng mới thêm và focus vào ô Cao (vân) (chỉ khi được thêm thủ công)
    if (!isInitial) {
        setTimeout(() => {
            newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            const focusInput = newRow.querySelector('input[name*="[height]"]');
            if (focusInput) {
                focusInput.focus();
            }
        }, 50);
    }
}

function removeOrderItem(button) {
    const row = button.closest('.order-item-row');
    const tbody = row.closest('.supply-items-container');
    const table = row.closest('table');
    row.remove();
    updateOrderSummary();
    updateAcrylicRowIndexes(tbody);

}

function updateAcrylicRowIndexes() {
    const orderCode = document.getElementById('order-code-input')?.value || '';
    let globalPieceIndex = 1;
    let globalItemIndex = 1;

    document.querySelectorAll('#order-supplies-container .order-supply-row').forEach((supplyRow, supplyIndex) => {
        const supplyCode = (supplyRow.querySelector('.order-supply-code-select')?.value || supplyRow.querySelector('.order-supply-code-input')?.value || '');
        const tbody = supplyRow.querySelector('.supply-items-container');
        if (!tbody) return;

        tbody.querySelectorAll('.order-item-row').forEach((row, itemIndex) => {
            // Detect labor rows (Công giả dày)
            const isLaborRow = row.dataset.isLabor === '1' ||
                (row.querySelector('input[name*="[is_labor]"]')?.value === '1') ||
                (row.querySelector('input[name*="[product_name]"]')?.value?.trim() === 'Công giả dày');

            // Update row STT
            const indexEl = row.querySelector('.row-index');
            if (indexEl) indexEl.textContent = globalItemIndex;

            // Get quantity
            const quantityInput = row.querySelector('input[name*="[quantity]"]');
            const qty = parseInt(quantityInput?.value) || 1;

            if (isLaborRow) {
                // Labor rows: clear the product-code-input (no mã tấm), do NOT advance globalPieceIndex
                const productCodeInput = row.querySelector('.product-code-input');
                if (productCodeInput) productCodeInput.value = '';
                // Apply amber styling as visual cue
                row.classList.add('bg-amber-50/60');
                row.dataset.isLabor = '1';
            } else {
                // Normal rows: assign mã tấm
                const baseCode = `${orderCode}.${supplyCode}.${globalPieceIndex}`;
                const productCodeInput = row.querySelector('.product-code-input');
                if (productCodeInput) productCodeInput.value = baseCode;
                globalPieceIndex += qty;
            }

            // Remove product_ids container if it exists
            const idsContainer = row.querySelector('.product-ids-container');
            if (idsContainer) idsContainer.remove();

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

            globalItemIndex++;
        });
    });
}

function bindAcrylicRowEvents(row) {
    const productNameInput = row.querySelector('input[name*="[product_name]"]');
    const heightInput = row.querySelector('input[name*="[height]"]');
    const widthInput = row.querySelector('input[name*="[width]"]');
    const quantityInput = row.querySelector('input[name*="[quantity]"]');
    const wingAreaInput = row.querySelector('input[name*="[wing_area]"]');
    const moldingLengthInput = row.querySelector('input[name*="[molding_length]"]');
    const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
    const bevelInput = row.querySelector('input[name*="[bevel]"]');
    const bevelSelect = row.querySelector('.product-bevel-select');
    const btnSwitch = row.querySelector('.btn-bevel-switch');
    
    if (productNameInput) {
        productNameInput.addEventListener('input', () => {
            calculateTotalPrice(row);
        });
    }
    
    if (heightInput) {
        heightInput.addEventListener('input', () => {
            applyNarrowWidthRule(row, false);
            calculateTotalPrice(row, 'height');
            syncBevelOnSizeChange(row);
        });
    }
    if (widthInput) {
        widthInput.addEventListener('input', () => {
            calculateTotalPrice(row, 'width');
            syncBevelOnSizeChange(row);
            
            // Dùng debounce để tránh đổi đơn giá liên tục khi người dùng đang nhập số (ví dụ: gõ 120 không bị nhảy xuống 100k ở số 1 và 12)
            clearTimeout(widthInput.narrowTimeout);
            widthInput.narrowTimeout = setTimeout(() => {
                applyNarrowWidthRule(row, true);
            }, 500);
        });

        widthInput.addEventListener('blur', () => {
            if (widthInput.narrowTimeout) {
                clearTimeout(widthInput.narrowTimeout);
                widthInput.narrowTimeout = null;
                applyNarrowWidthRule(row, true);
            }
        });
    }
    if (quantityInput) {
        quantityInput.addEventListener('input', () => {
            applyNarrowWidthRule(row, false);
            calculateTotalPrice(row, 'quantity');
            updateAcrylicRowIndexes();
        });
    }
    if (wingAreaInput) wingAreaInput.addEventListener('input', () => calculateTotalPrice(row, 'wing_area'));
    if (moldingLengthInput) moldingLengthInput.addEventListener('input', () => calculateTotalPrice(row, 'molding_length'));
    if (unitPriceInput) unitPriceInput.addEventListener('input', () => calculateTotalPrice(row, 'unit_price'));
    if (bevelInput) {
        bevelInput.addEventListener('input', () => {
            bevelInput.setAttribute('data-auto-sync', 'none');
            updateBevelFieldStyle(row);
            updateEdgeBevel(row);
        });
    }
    if (bevelSelect) {
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
                updateBevelFieldStyle(row);
                bevelInput.focus();
                return;
            }
            updateBevelFieldStyle(row);
            updateEdgeBevel(row);
        });
    }
}

function duplicateAcrylicRow(button) {
    const row = button.closest('.order-item-row');
    const tbody = row.closest('.supply-items-container');

    // Collect all input/select/textarea values BEFORE cloning
    const valuesToCopy = [];
    row.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(el => {
        valuesToCopy.push({ name: el.name, value: el.value });
    });

    const newRow = row.cloneNode(true);

    // Remove database ID so it creates a new entry
    const idInput = newRow.querySelector('input[name*="[id]"]');
    if (idInput) idInput.remove();

    // Restore field values by name
    valuesToCopy.forEach(({ name, value }) => {
        if (!name || name.indexOf('[id]') !== -1) return;
        const el = newRow.querySelector(`[name="${name}"]`);
        if (el) el.value = value;
    });

    // Insert after current row
    row.parentNode.insertBefore(newRow, row.nextSibling);

    // Force table reflow to fix Chrome sticky cell border-collapse rendering bug
    const table = newRow.closest('table');


    bindAcrylicRowEvents(newRow);
    initBevelField(newRow);
    updateAcrylicRowIndexes();
    updateOrderSummary();
}

function addFakeThicknessRow(button) {
    const row = button.closest('.order-item-row');
    const tbody = row.closest('.supply-items-container');
    const supplyIndex = tbody.dataset.supplyIndex;
    
    // Get height and quantity from current row
    const heightInput = row.querySelector('input[name*="[height]"]');
    const qtyInput = row.querySelector('input[name*="[quantity]"]');
    
    const parentHeight = heightInput ? parseFloat(heightInput.value) || 0 : 0;
    const parentQty = qtyInput ? parseFloat(qtyInput.value) || 1 : 1;
    
    // Calculate wing area: height / 1000 * qty
    const wingArea = (parentHeight * parentQty) / 1000;
    const unitPrice = 35000;
    const totalPrice = Math.round(wingArea * unitPrice);
    
    // Create new row
    const newRow = row.cloneNode(true);
    
    // Clean up database ID
    const idInput = newRow.querySelector('input[name*="[id]"]');
    if (idInput) idInput.remove();
    
    // Set values for the new row
    const nameInput = newRow.querySelector('input[name*="[product_name]"]');
    if (nameInput) nameInput.value = "Công giả dày";
    
    const thicknessInput = newRow.querySelector('input[name*="[thickness]"]');
    if (thicknessInput) thicknessInput.value = "";
    
    const newHeightInput = newRow.querySelector('input[name*="[height]"]');
    if (newHeightInput) newHeightInput.value = parentHeight || "";
    
    const newWidthInput = newRow.querySelector('input[name*="[width]"]');
    if (newWidthInput) newWidthInput.value = "";
    
    const newQtyInput = newRow.querySelector('input[name*="[quantity]"]');
    if (newQtyInput) newQtyInput.value = parentQty;
    
    const bevelInput = newRow.querySelector('input[name*="[bevel]"]');
    if (bevelInput) {
        bevelInput.value = "";
        bevelInput.setAttribute('data-auto-sync', 'none');
    }
    
    const wingAreaInput = newRow.querySelector('input[name*="[wing_area]"]');
    if (wingAreaInput) wingAreaInput.value = wingArea > 0 ? wingArea : "";
    
    const moldingLengthInput = newRow.querySelector('input[name*="[molding_length]"]');
    if (moldingLengthInput) moldingLengthInput.value = "";
    
    const edgeBevelInput = newRow.querySelector('input[name*="[edge_bevel]"]');
    if (edgeBevelInput) edgeBevelInput.value = "";
    
    const cncSelect = newRow.querySelector('.cnc-template-select');
    if (cncSelect) cncSelect.value = "";
    
    // Clear cnc hidden parameters
    newRow.querySelectorAll('input[type="hidden"]').forEach(el => {
        if (!el.name.includes('[id]')) {
            el.value = "";
        }
    });
    
    const newUnitPriceInput = newRow.querySelector('input[name*="[unit_price]"]');
    if (newUnitPriceInput) newUnitPriceInput.value = unitPrice;
    
    const newTotalPriceInput = newRow.querySelector('input[name*="[total_price]"]');
    if (newTotalPriceInput) newTotalPriceInput.value = totalPrice > 0 ? totalPrice : 0;
    
    const notesInput = newRow.querySelector('input[name*="[notes]"]');
    if (notesInput) notesInput.value = "";
    
    // Mark new row as labor
    newRow.dataset.isLabor = '1';
    newRow.classList.add('bg-amber-50/60');

    // Set is_labor hidden input to 1
    const isLaborInput = newRow.querySelector('input[name*="[is_labor]"]');
    if (isLaborInput) {
        isLaborInput.value = '1';
    } else {
        // Create if not present (cloned from JS-created row which may not have it)
        const hiddenLaborInput = document.createElement('input');
        hiddenLaborInput.type = 'hidden';
        hiddenLaborInput.name = 'labor_placeholder'; // will be renamed by updateAcrylicRowIndexes
        hiddenLaborInput.value = '1';
        hiddenLaborInput.setAttribute('data-is-labor-flag', '1');
        newRow.querySelector('td')?.appendChild(hiddenLaborInput);
    }

    // Replace the product-code-input td content with labor badge
    const pcInput = newRow.querySelector('.product-code-input');
    if (pcInput) {
        const td = pcInput.closest('td');
        pcInput.value = '';
        pcInput.style.display = 'none';
        if (!td.querySelector('.labor-badge')) {
            const badge = document.createElement('div');
            badge.className = 'labor-badge h-8 flex items-center justify-center text-xs text-amber-600 font-semibold bg-amber-50 px-1';
            badge.innerHTML = '<iconify-icon icon="lucide:hammer" style="margin-right:4px;"></iconify-icon> Công';
            td.insertBefore(badge, pcInput);
        }
    }

    // Style the product name input
    const nameInputNew = newRow.querySelector('input[name*="[product_name]"]');
    if (nameInputNew) {
        nameInputNew.classList.add('font-semibold', 'text-amber-700');
        nameInputNew.classList.remove('border-neutral-300', 'focus:border-primary-500', 'focus:ring-primary-500');
        nameInputNew.classList.add('border-amber-300', 'focus:border-amber-500', 'focus:ring-amber-400');
    }

    // Insert after current row
    row.parentNode.insertBefore(newRow, row.nextSibling);

    // Force table reflow to fix Chrome sticky cell border-collapse rendering bug
    const table = newRow.closest('table');


    bindAcrylicRowEvents(newRow);
    initBevelField(newRow);
    updateAcrylicRowIndexes();
    updateOrderSummary();
}

function calculateTotalPrice(row, sourceEvent) {
    const heightInput = row.querySelector('input[name*="[height]"]');
    const widthInput = row.querySelector('input[name*="[width]"]');
    const quantityInput = row.querySelector('input[name*="[quantity]"]');
    const wingAreaInput = row.querySelector('input[name*="[wing_area]"]');
    const moldingLengthInput = row.querySelector('input[name*="[molding_length]"]');
    const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
    
    if (!heightInput || !widthInput || !quantityInput || !wingAreaInput || !moldingLengthInput) return;

    const height = parseFloat(heightInput.value) || 0;
    const width = parseFloat(widthInput.value) || 0;
    const quantity = parseFloat(quantityInput.value) || 0;
    
    if (sourceEvent !== 'wing_area') {
        const productNameInput = row.querySelector('input[name*="[product_name]"]');
        const isFakeThickness = productNameInput && productNameInput.value.trim() === "Công giả dày";
        
        if (isFakeThickness) {
            let wingArea = 0;
            if (height > 0 && quantity > 0) {
                wingArea = (height * quantity) / 1000;
            }
            wingAreaInput.value = wingArea > 0 ? wingArea : '';
        } else {
            if (!isNaN(width) && width > 0 && width < 55) {
                wingAreaInput.value = 0;
            } else {
                let wingArea = 0;
                if (height > 0 && width > 0 && quantity > 0) {
                    wingArea = (height * width * quantity) / 1000000;
                }
                wingAreaInput.value = wingArea > 0 ? wingArea : '';
            }
        }
    }
    
    const unitPrice = parseFloat(unitPriceInput ? unitPriceInput.value : 0) || 0;
    const currentWingArea = parseFloat(wingAreaInput.value) || 0;
    const moldingLength = parseFloat(moldingLengthInput.value) || 0;
    
    const totalPrice = Math.round((currentWingArea + moldingLength) * unitPrice);
    const totalPriceInput = row.querySelector('input[name*="[total_price]"]');
    if (totalPriceInput) {
        totalPriceInput.value = totalPrice > 0 ? totalPrice : 0;
    }
    updateOrderSummary();
}

/**
 * Narrow-width rule: width < 55mm → Phào = (cao × SL) / 1000, Đơn giá = 100.000, Cánh = 0.
 * Width input gets red border as visual warning.
 */
function applyNarrowWidthRule(row, triggerCalculation = true) {
    const heightInput       = row.querySelector('input[name*="[height]"]');
    const widthInput        = row.querySelector('input[name*="[width]"]');
    const quantityInput     = row.querySelector('input[name*="[quantity]"]');
    const wingAreaInput     = row.querySelector('input[name*="[wing_area]"]');
    const moldingLengthInput= row.querySelector('input[name*="[molding_length]"]');
    const unitPriceInput    = row.querySelector('input[name*="[unit_price]"]');

    if (!widthInput) return;

    const productNameInput = row.querySelector('input[name*="[product_name]"]');
    if (productNameInput && productNameInput.value.trim() === "Công giả dày") {
        widthInput.classList.remove('input-narrow-warning');
        return;
    }

    const width    = parseFloat(widthInput.value);
    const height   = parseFloat(heightInput ? heightInput.value : 0) || 0;
    const quantity = parseFloat(quantityInput ? quantityInput.value : 1) || 1;

    const lastWidth = parseFloat(widthInput.dataset.lastWidth);
    const isPreviousNarrow = !isNaN(lastWidth) && lastWidth > 0 && lastWidth < 55;
    const isCurrentNarrow = !isNaN(width) && width > 0 && width < 55;

    if (isCurrentNarrow) {
        // Red border warning
        widthInput.classList.add('input-narrow-warning');

        // Phào (m) = (cao × SL) / 1000
        if (moldingLengthInput) {
            const phao = height > 0 ? ((height * quantity) / 1000) : 0;
            moldingLengthInput.value = phao > 0 ? phao : '';
        }
        // Đơn giá = 100.000
        if (unitPriceInput) {
            unitPriceInput.value = 100000;
        }
        // Cánh (m2) = 0
        if (wingAreaInput) {
            wingAreaInput.value = 0;
        }
    } else {
        // Restore normal border
        widthInput.classList.remove('input-narrow-warning');

        // Phào (m) = 0 khi rộng >= 55
        if (moldingLengthInput) {
            moldingLengthInput.value = 0;
        }

        // If it transitioned from narrow to wide, restore the original price
        if (isPreviousNarrow && unitPriceInput && unitPriceInput.value == 100000) {
            const supplyRow = row.closest('.order-supply-row');
            if (supplyRow) {
                const selectEl = supplyRow.querySelector('.order-supply-code-select') || supplyRow.querySelector('.order-supply-code-input');
                const supplyCode = selectEl ? selectEl.value : '';
                if (supplyCode && typeof woodBoardPricesData !== 'undefined') {
                    const price = woodBoardPricesData.find(p => p.code === supplyCode);
                    if (price) {
                        unitPriceInput.value = price.price_m2 ? parseInt(price.price_m2) : '';
                    }
                }
            }
        }
    }

    if (!isNaN(width)) {
        widthInput.dataset.lastWidth = width;
    } else {
        delete widthInput.dataset.lastWidth;
    }

    // Tính toán lại tổng tiền và tóm tắt đơn sau khi áp dụng/hủy quy tắc chiều rộng nhỏ
    if (triggerCalculation) {
        calculateTotalPrice(row);
        updateOrderSummary();
    }
}



function updateEdgeBevel(row) {
    const bevelInput = row.querySelector('input[name*="[bevel]"]');
    const edgeBevelInput = row.querySelector('input[name*="[edge_bevel]"]');
    if (!bevelInput || !edgeBevelInput) return;
    
    const bevelValue = bevelInput.value.trim();
    if (bevelValue) {
        if (/^vát/i.test(bevelValue)) {
            edgeBevelInput.value = bevelValue;
        } else {
            edgeBevelInput.value = 'Vát ' + bevelValue;
        }
    } else {
        edgeBevelInput.value = '';
    }
}

function updateBevelFieldStyle(row) {
    const bevelInput = row.querySelector('.product-bevel-input');
    const bevelSelect = row.querySelector('.product-bevel-select');
    if (!bevelInput || !bevelSelect) return;

    const syncMode = bevelInput.getAttribute('data-auto-sync') || 'none';
    
    // Reset background and border classes
    bevelInput.classList.remove(
        'bg-indigo-50', 'text-indigo-900', 'border-indigo-300', 'font-medium',
        'bg-yellow-50', 'text-yellow-900', 'border-yellow-300',
        'bg-white', 'text-neutral-800', 'border-neutral-300'
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

function initBevelField(row) {
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

    updateBevelFieldStyle(row);
    updateEdgeBevel(row);
}

function syncBevelOnSizeChange(row) {
    const heightInput = row.querySelector('input[name*="[height]"]');
    const widthInput = row.querySelector('input[name*="[width]"]');
    const bevelInput = row.querySelector('input[name*="[bevel]"]');
    
    if (!heightInput || !widthInput || !bevelInput) return;
    
    const height = heightInput.value.trim();
    const width = widthInput.value.trim();
    const syncMode = bevelInput.getAttribute('data-auto-sync') || 'none';

    if (syncMode === 'width') {
        bevelInput.value = width;
        updateEdgeBevel(row);
    } else if (syncMode === 'height') {
        bevelInput.value = height;
        updateEdgeBevel(row);
    }
}

// Initial attachment setup for Acrylic-specific rows if DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('order-supplies-container');
    if (container && container.querySelectorAll('.order-supply-row').length === 0) {
        addOrderSupply();
    }

    document.querySelectorAll('#order-supplies-container .order-item-row').forEach(row => {
        bindAcrylicRowEvents(row);
        applyNarrowWidthRule(row);
        calculateTotalPrice(row);
        initBevelField(row);
        updateEdgeBevel(row);
    });

    if (typeof TomSelect !== 'undefined') {
        document.querySelectorAll('.tom-select-supply-code').forEach(function(element) {
            if (element.tomselect) return;
            const ts = new TomSelect(element, {
                create: true,
                placeholder: '-- Mã vật tư --',
                allowEmptyOption: true,
                maxOptions: null
            });
            ts.on('change', function(value) {
                handleSupplyCodeChange(element, value);
                updateAcrylicRowIndexes();
            });
        });
    }

    // Recalculate codes on load
    updateAcrylicRowIndexes();

    const orderCodeInput = document.getElementById('order-code-input');
    if (orderCodeInput) {
        orderCodeInput.addEventListener('input', () => updateAcrylicRowIndexes());
    }
});

// Live listener for supply code input changes
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('order-supply-code-input')) {
        updateAcrylicRowIndexes();
    }
});

// ---- CNC Template Selection Logic ----
window.cncTemplatesData = @json($cncTemplates ?? []);

function onCncTemplateChange(selectEl) {
    const td = selectEl.closest('td');
    if (!selectEl.value) {
        // Clear all hidden fields
        td.querySelectorAll('input[type="hidden"]').forEach(el => el.value = '');
        return;
    }
    const selectedOption = selectEl.options[selectEl.selectedIndex];
    let params = null;
    try {
        params = JSON.parse(selectedOption.getAttribute('data-params') || 'null');
    } catch(e) { params = null; }
    if (!params) return;

    const fieldMap = {
        'cnc-offset-left':   params.offset_left,
        'cnc-offset-right':  params.offset_right,
        'cnc-offset-top':    params.offset_top,
        'cnc-offset-bottom': params.offset_bottom,
        'cnc-mill-left':     params.mill_left,
        'cnc-mill-right':    params.mill_right,
        'cnc-mill-top':      params.mill_top,
        'cnc-mill-bottom':   params.mill_bottom,
        'cnc-mill-width':    params.mill_width,
        'cnc-mill-depth':    params.mill_depth,
        'cnc-mill-left-2':   params.mill_left_2,
        'cnc-mill-right-2':  params.mill_right_2,
        'cnc-mill-top-2':    params.mill_top_2,
        'cnc-mill-bottom-2': params.mill_bottom_2,
        'cnc-mill-width-2':  params.mill_width_2,
        'cnc-mill-depth-2':  params.mill_depth_2,
    };
    Object.entries(fieldMap).forEach(([cls, val]) => {
        const input = td.querySelector('.' + cls);
        if (input) input.value = (val !== null && val !== undefined) ? val : '';
    });
}
</script>

{{-- =================== EXCEL IMPORT =================== --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

{{-- Modal preview Excel --}}
<div id="excel-import-backdrop"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:3000;"
    onclick="closeExcelImport()"></div>
<div id="excel-import-modal"
    style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);
           width:min(1100px,96vw);height:90vh;background:#fff;border-radius:16px;
           box-shadow:0 25px 60px rgba(0,0,0,0.3);z-index:3001;overflow:hidden;flex-direction:column;">

    {{-- Header --}}
    <div style="background:#fff;border-bottom:1px solid #e5e7eb;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="background:#ecfdf5;border-radius:10px;padding:10px;display:flex;">
                <iconify-icon icon="lucide:file-spreadsheet" style="font-size:22px;color:#10b981;"></iconify-icon>
            </div>
            <div>
                <div style="font-size:16px;font-weight:700;color:#1f2937;">Nhập từ Excel</div>
                <div id="excel-import-filename" style="font-size:12px;color:#6b7280;margin-top:2px;">—</div>
            </div>
        </div>
        <button type="button" onclick="closeExcelImport()" class="w-8 h-8 rounded-lg hover:bg-neutral-100 flex items-center justify-center text-neutral-400 hover:text-danger-500 transition-colors" style="border:none;background:transparent;">
            <iconify-icon icon="lucide:x" style="font-size:18px;"></iconify-icon>
        </button>
    </div>

    {{-- Column mapping guide --}}
    <div style="padding:14px 24px 0;flex-shrink:0;">
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;">
            <div style="font-size:12px;font-weight:700;color:#065f46;margin-bottom:6px;display:flex;align-items:center;gap:6px;">
                <iconify-icon icon="lucide:info" style="font-size:14px;"></iconify-icon>
                Định dạng cột Excel — hàng đầu là tiêu đề, từ hàng 2 là dữ liệu
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:6px;font-size:11px;">
                @foreach(['Tên SP','Độ dày','Cao','Rộng','SL','Vát','Chiều vân','Cánh m2','Phào m','Đơn giá','Ghi chú'] as $col)
                <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:999px;font-weight:600;">{{ $col }}</span>
                @endforeach
            </div>
            <div style="font-size:11px;color:#6b7280;margin-top:6px;">
                Cột tiêu đề không cần đúng tên — hệ thống nhận diện theo <strong>vị trí</strong> (cột A, B, C...). Thứ tự như trên.
            </div>
        </div>
    </div>

    {{-- Preview table --}}
    <div style="flex:1;overflow-y:auto;padding:14px 24px;">
        <div id="excel-preview-container" style="overflow-x:auto;">
            <table id="excel-preview-table" style="width:100%;border-collapse:collapse;font-size:12px;min-width:800px;">
                <thead id="excel-preview-thead" style="background:#f8fafc;position:sticky;top:0;z-index:2;"></thead>
                <tbody id="excel-preview-tbody"></tbody>
            </table>
            <div id="excel-preview-empty" style="display:none;text-align:center;padding:40px;color:#94a3b8;">
                <iconify-icon icon="lucide:file-x-2" style="font-size:36px;"></iconify-icon>
                <div style="margin-top:8px;">Không tìm thấy dữ liệu trong file</div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div style="padding:14px 24px;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;background:#f8fafc;">
        <div id="excel-import-count" style="font-size:13px;color:#64748b;"></div>
        <div style="display:flex;gap:10px;">
            <button type="button" onclick="closeExcelImport()"
                style="padding:9px 20px;border:1.5px solid #d1d5db;border-radius:8px;background:#fff;color:#374151;font-size:13px;font-weight:600;cursor:pointer;">
                Hủy
            </button>
            <button type="button" id="excel-import-confirm-btn" onclick="confirmExcelImport()"
                style="padding:9px 20px;border:none;border-radius:8px;background:linear-gradient(135deg,#059669,#10b981);color:#fff;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;">
                <iconify-icon icon="lucide:check" style="font-size:15px;"></iconify-icon>
                Nhập vào bảng
            </button>
        </div>
    </div>
</div>

<script>
// Di chuyển modal ra ngoài body để tránh bị ảnh hưởng bởi relative/transform của các thẻ cha (gây hở viền trên/dưới)
document.addEventListener('DOMContentLoaded', function() {
    const backdrop = document.getElementById('excel-import-backdrop');
    const modal = document.getElementById('excel-import-modal');
    if (backdrop) document.body.appendChild(backdrop);
    if (modal) document.body.appendChild(modal);
});

// ======== EXCEL IMPORT LOGIC (v2 — grouped by supply code in col B) ========
// _excelSupplyGroups = [{supply_code, supply_name, items:[{...}]}]
let _excelSupplyGroups = [];

function triggerGlobalExcelImport() {
    const fileInput = document.getElementById('excel-global-file-input');
    if (!fileInput) return;
    fileInput.value = '';
    fileInput.onchange = function(e) { handleExcelFile(e.target.files[0]); };
    fileInput.click();
}

function handleExcelFile(file) {
    if (!file) return;
    document.getElementById('excel-import-filename').textContent = file.name;
    _excelSupplyGroups = [];

    const reader = new FileReader();
    reader.onload = function(e) {
        try {
            const wb = XLSX.read(e.target.result, { type: 'array' });
            const ws = wb.Sheets[wb.SheetNames[0]];
            // Read as array-of-arrays; include formula values via { raw: false } fallback
            const rawRows = XLSX.utils.sheet_to_json(ws, { header: 1, defval: '', raw: false });

            if (!rawRows || rawRows.length === 0) {
                showExcelModal([]);
                return;
            }

            /*
             * Column mapping (0-indexed):
             *  A=0  STT                   (số / La-mã section header)
             *  B=1  Mã vật tư             ← KEY: repeated = same supply group
             *  C=2  Tên sản phẩm          (only first row of group usually)
             *  D=3  Cao (height)
             *  E=4  Rộng (width)
             *  F=5  Số lượng
             *  G=6  Chiều vân
             *  H=7  (unused)
             *  I=8  Cánh m2
             *  J=9  Phào m
             *  K=10 Đơn giá
             *  L=11 Thành tiền            (computed — skip)
             *  M=12 Ghi chú / loại đặc biệt ("Tấm giả dày", "Công giả dày"…)
             *  N=13 Bevel/width sync
             */

            const clean = v => (v === null || v === undefined) ? '' : String(v).trim();
            const num   = v => { const n = parseFloat(String(v).replace(/[^0-9.\-]/g,'')); return isNaN(n) ? '' : n; };

            // Roman numeral / section header detector
            const isHeader = stt => /^[IVX]+$/.test(clean(stt));

            // Group by consecutive col B value (mã vật tư)
            const groups = {};    // supply_code → {supply_code, supply_name, items[]}
            const groupOrder = [];
            let prevSupplyCode = null;
            for (const row of rawRows) {
                const stt        = clean(row[0]);
                const supplyCode = clean(row[1]);
                const supplyName = clean(row[2]);
                const height     = num(row[3]);
                const width      = num(row[4]);
                const qty        = parseInt(clean(row[5])) || 1;
                const edgeBevel  = clean(row[6]);
                const grain      = clean(row[7]);
                const wingArea   = num(row[8]);
                const molding    = num(row[9]);
                const unitPrice  = num(row[10]);
                const notes      = clean(row[12]);
                const bevel      = clean(row[13]);

                // Skip header rows (col A = La-mã I, II…) and summary/footer rows
                if (isHeader(stt)) {
                    continue;
                }

                // Must have a supply code in col B to be a data row
                if (!supplyCode) continue;

                // Must have at least height or width to be a real item row
                if (height === '' && width === '') continue;

                // Detect "Công giả dày" / "Tấm giả dày" via notes col M
                const isLaborNote = /gi[aả]\s*d[aày]y/i.test(notes) || /c[oô]ng/i.test(notes);
                const productName = isLaborNote ? 'Công giả dày' : '';

                const item = {
                    product_name : productName,
                    thickness    : '',
                    height,
                    width,
                    quantity     : qty,
                    edge_bevel   : edgeBevel,
                    grain        : grain === '2' ? '2' : '0',
                    wing_area    : wingArea,
                    molding,
                    unit_price   : unitPrice,
                    notes,
                    bevel,
                    is_labor     : isLaborNote ? 1 : 0,
                };

                if (!groups[supplyCode]) {
                    groups[supplyCode] = { supply_code: supplyCode, supply_name: supplyName, items: [] };
                    groupOrder.push(supplyCode);
                } else if (supplyName && !groups[supplyCode].supply_name) {
                    groups[supplyCode].supply_name = supplyName;
                }
                groups[supplyCode].items.push(item);
            }

            _excelSupplyGroups = groupOrder.map(sc => groups[sc]).filter(g => g.items.length > 0);

            showExcelModal(_excelSupplyGroups);
        } catch(err) {
            console.error(err);
            alert('Không thể đọc file Excel: ' + err.message);
        }
    };
    reader.readAsArrayBuffer(file);
}

function showExcelModal(groups) {
    const thead = document.getElementById('excel-preview-thead');
    const tbody = document.getElementById('excel-preview-tbody');
    const empty = document.getElementById('excel-preview-empty');
    const countEl = document.getElementById('excel-import-count');
    const confirmBtn = document.getElementById('excel-import-confirm-btn');

    thead.innerHTML = `<tr>
        <th style="padding:8px 10px;text-align:left;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">Vật tư / Tên SP</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">Cao</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">Rộng</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">SL</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">Vát</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">Chiều vân</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">Cạnh vát</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">Cánh m²</th>
        <th style="padding:8px 10px;text-align:center;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">Phào m</th>
        <th style="padding:8px 10px;text-align:right;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">Đơn giá</th>
        <th style="padding:8px 10px;text-align:left;color:#64748b;font-weight:600;border-bottom:2px solid #e2e8f0;white-space:nowrap;">Ghi chú</th>
    </tr>`;

    if (!groups || groups.length === 0) {
        tbody.innerHTML = '';
        empty.style.display = 'block';
        countEl.textContent = 'Không có dữ liệu';
        confirmBtn.style.opacity = '0.5';
        confirmBtn.style.pointerEvents = 'none';
        document.getElementById('excel-import-backdrop').style.display = 'block';
        document.getElementById('excel-import-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        return;
    }

    empty.style.display = 'none';
    confirmBtn.style.opacity = '1';
    confirmBtn.style.pointerEvents = 'auto';

    let totalItems = 0;
    let html = '';
    groups.forEach(g => {
        // Supply header row
        html += `<tr style="background:#ede9fe;">
            <td colspan="11" style="padding:7px 12px;font-weight:700;color:#6d28d9;font-size:12px;">
                <iconify-icon icon="lucide:package" style="margin-right:6px;font-size:13px;"></iconify-icon>
                Vật tư: <span style="background:#fff;border:1px solid #c4b5fd;border-radius:6px;padding:1px 8px;margin-left:4px;">${escHtml(g.supply_code)}</span>
                <span style="color:#94a3b8;font-weight:400;margin-left:8px;">(${g.items.length} sản phẩm)</span>
            </td>
        </tr>`;
        g.items.forEach((item, i) => {
            totalItems++;
            const bgClass = item.is_labor ? 'background:#fef9c3;' : (i % 2 === 0 ? '' : 'background:#fafafa;');
            const laborBadge = item.is_labor ? `<span style="background:#fde68a;color:#92400e;padding:1px 6px;border-radius:999px;font-size:10px;margin-left:6px;">Công</span>` : '';
            html += `<tr style="border-bottom:1px solid #f1f5f9;${bgClass}">
                <td style="padding:6px 12px;color:#0f172a;padding-left:24px;">${escHtml(item.product_name)}${laborBadge}</td>
                <td style="padding:6px 10px;text-align:center;color:#374151;">${item.height !== '' ? item.height : '—'}</td>
                <td style="padding:6px 10px;text-align:center;color:#374151;">${item.width !== '' ? item.width : '—'}</td>
                <td style="padding:6px 10px;text-align:center;font-weight:600;color:#1d4ed8;">${item.quantity}</td>
                <td style="padding:6px 10px;text-align:center;color:#374151;">${item.bevel !== '' ? item.bevel : '—'}</td>
                <td style="padding:6px 10px;text-align:center;color:#374151;">${item.grain !== '' ? item.grain : '—'}</td>
                <td style="padding:6px 10px;text-align:center;color:#374151;">${item.edge_bevel !== '' ? escHtml(item.edge_bevel) : '—'}</td>
                <td style="padding:6px 10px;text-align:center;color:#374151;">${item.wing_area !== '' ? item.wing_area : '—'}</td>
                <td style="padding:6px 10px;text-align:center;color:#374151;">${item.molding !== '' ? item.molding : '—'}</td>
                <td style="padding:6px 10px;text-align:right;font-weight:600;color:#15803d;">${item.unit_price !== '' ? Number(item.unit_price).toLocaleString('vi-VN') : '—'}</td>
                <td style="padding:6px 10px;color:#6b7280;font-style:italic;">${escHtml(item.notes)}</td>
            </tr>`;
        });
    });
    tbody.innerHTML = html;

    countEl.innerHTML = `<b style="color:#6d28d9;">${groups.length} vật tư</b>&nbsp;·&nbsp;<b style="color:#1d4ed8;">${totalItems} sản phẩm</b> sẽ được nhập`;

    document.getElementById('excel-import-backdrop').style.display = 'block';
    document.getElementById('excel-import-modal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeExcelImport() {
    document.getElementById('excel-import-backdrop').style.display = 'none';
    document.getElementById('excel-import-modal').style.display = 'none';
    document.body.style.overflow = '';
    _excelSupplyGroups = [];
}

function confirmExcelImport() {
    if (!_excelSupplyGroups || _excelSupplyGroups.length === 0) return;

    const suppliesContainer = document.getElementById('order-supplies-container');
    if (!suppliesContainer) return;

    _excelSupplyGroups.forEach(group => {
        // 1. Create a new supply section
        addOrderSupply();

        // Get the newly created supply row (last child)
        const newSupplyRow = suppliesContainer.querySelector('.order-supply-row:last-child');
        if (!newSupplyRow) return;

        // 2. Set supply code via TomSelect or plain input
        const supplyCodeSelect = newSupplyRow.querySelector('.order-supply-code-select');
        if (supplyCodeSelect && supplyCodeSelect.tomselect) {
            supplyCodeSelect.tomselect.createItem(group.supply_code, true);
            supplyCodeSelect.tomselect.setValue(group.supply_code);
        } else if (supplyCodeSelect) {
            // Add option if not present
            let opt = Array.from(supplyCodeSelect.options).find(o => o.value === group.supply_code);
            if (!opt) {
                opt = new Option(group.supply_code, group.supply_code, true, true);
                supplyCodeSelect.add(opt);
            }
            supplyCodeSelect.value = group.supply_code;
        }

        // Set supply name from Excel group
        const supplyNameInput = newSupplyRow.querySelector('input[name*="[supply_name]"]');
        if (supplyNameInput && group.supply_name) {
            supplyNameInput.value = group.supply_name;
        }

        // 3. Add items into the supply
        const itemsContainer = newSupplyRow.querySelector('.supply-items-container');
        const addItemBtn = newSupplyRow.querySelector('[onclick*="addOrderItem"]');
        if (!itemsContainer || !addItemBtn) return;

        // Clear the default empty row added by addOrderSupply()
        itemsContainer.innerHTML = '';

        group.items.forEach(item => {
            addOrderItem(addItemBtn, true);
            const newRow = itemsContainer.lastElementChild;
            if (!newRow) return;

            const setVal = (sel, val) => {
                if (val === '' || val === null || val === undefined) return;
                const el = newRow.querySelector(sel);
                if (el) el.value = val;
            };

            setVal('input[name*="[product_name]"]', item.product_name);
            setVal('input[name*="[height]"]',        item.height);
            setVal('input[name*="[width]"]',         item.width);
            setVal('input[name*="[quantity]"]',      item.quantity);
            setVal('input[name*="[wing_area]"]',     item.wing_area);
            setVal('input[name*="[molding_length]"]',item.molding);
            setVal('input[name*="[unit_price]"]',    item.unit_price);
            setVal('input[name*="[notes]"]',         item.notes);
            if (item.bevel === '' || item.bevel === null || item.bevel === undefined) {
                const bevelIn = newRow.querySelector('input[name*="[bevel]"]');
                if (bevelIn) bevelIn.setAttribute('data-auto-sync', 'none');
            } else {
                setVal('input[name*="[bevel]"]', item.bevel);
            }
            setVal('input[name*="[edge_bevel]"]',    item.edge_bevel);

            const grainSel = newRow.querySelector('select[name*="[grain_direction]"]');
            if (grainSel) grainSel.value = item.grain || '0';

            // Handle labor rows
            if (item.is_labor) {
                newRow.dataset.isLabor = '1';
                newRow.classList.add('bg-amber-50/60');
                const isLaborInput = newRow.querySelector('input[name*="[is_labor]"]');
                if (isLaborInput) isLaborInput.value = '1';

                // Show labor badge instead of product code
                const pcInput = newRow.querySelector('.product-code-input');
                if (pcInput) {
                    const td = pcInput.closest('td');
                    pcInput.value = '';
                    pcInput.style.display = 'none';
                    if (!td.querySelector('.labor-badge')) {
                        const badge = document.createElement('div');
                        badge.className = 'labor-badge h-8 flex items-center justify-center text-xs text-amber-600 font-semibold bg-amber-50 px-1';
                        badge.innerHTML = '<iconify-icon icon="lucide:hammer" style="margin-right:4px;"></iconify-icon> Công';
                        td.insertBefore(badge, pcInput);
                    }
                }
            }

            bindAcrylicRowEvents(newRow);
            calculateTotalPrice(newRow);
            initBevelField(newRow);
        });
    });

    updateAcrylicRowIndexes();
    updateOrderSummary();

    // Flash the container
    if (suppliesContainer) {
        suppliesContainer.style.transition = 'box-shadow 0.3s';
        suppliesContainer.style.boxShadow = '0 0 0 3px #10b981';
        setTimeout(() => { suppliesContainer.style.boxShadow = ''; }, 1500);
    }



    closeExcelImport();
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
