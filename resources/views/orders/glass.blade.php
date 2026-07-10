{{-- Order Supplies & Items Section Card (Glass) --}}
<style>
    /* Wing Direction Label styling */
    .wing-direction-label {
        margin-left: 8px !important; /* Lề trái 8px để không sát viền */
        flex-shrink: 0 !important;
    }

    /* === COMPACT TABLE: 75% font scale === */
    #glass-supplies-container .order-supply-row table {
        font-size: 75% !important;
    }
    #glass-supplies-container .order-supply-row table input,
    #glass-supplies-container .order-supply-row table select {
        font-size: 75% !important;
        height: 24px !important;
        min-height: 24px !important;
        padding-top: 2px !important;
        padding-bottom: 2px !important;
        line-height: 1.2 !important;
    }
    #glass-supplies-container .order-supply-row table textarea {
        font-size: 75% !important;
        height: auto !important;
        min-height: 60px !important;
        padding-top: 4px !important;
        padding-bottom: 4px !important;
        line-height: 1.2 !important;
    }
    #glass-supplies-container .order-supply-row table th {
        padding: 8px 4px !important;
    }
    #glass-supplies-container .order-supply-row table td {
        padding: 3px 4px !important;
    }
    /* Override td widths to ~65% of original */
    #glass-supplies-container .order-supply-row table td[style*="width: 45px"],
    #glass-supplies-container .order-supply-row table td[style*="width:45px"] { width: 30px !important; min-width: 30px !important; max-width: 30px !important; }
    #glass-supplies-container .order-supply-row table td[style*="width: 160px"],
    #glass-supplies-container .order-supply-row table td[style*="width:160px"] { width: 110px !important; min-width: 110px !important; max-width: 110px !important; }
    #glass-supplies-container .order-supply-row table td[style*="width: 200px"],
    #glass-supplies-container .order-supply-row table td[style*="width:200px"] { width: 130px !important; min-width: 130px !important; max-width: 130px !important; }
    #glass-supplies-container .order-supply-row table td[style*="width: 110px"],
    #glass-supplies-container .order-supply-row table td[style*="width:110px"] { width: 80px !important; min-width: 80px !important; max-width: 80px !important; }
    #glass-supplies-container .order-supply-row table td[style*="width: 100px"],
    #glass-supplies-container .order-supply-row table td[style*="width:100px"] { width: 70px !important; min-width: 70px !important; max-width: 70px !important; }
    #glass-supplies-container .order-supply-row table td[style*="width: 120px"],
    #glass-supplies-container .order-supply-row table td[style*="width:120px"] { width: 90px !important; min-width: 90px !important; max-width: 90px !important; }
    #glass-supplies-container .order-supply-row table td[style*="width: 70px"],
    #glass-supplies-container .order-supply-row table td[style*="width:70px"] { width: 50px !important; min-width: 50px !important; max-width: 50px !important; }
    #glass-supplies-container .order-supply-row table td[style*="width: 80px"],
    #glass-supplies-container .order-supply-row table td[style*="width:80px"] { width: 60px !important; min-width: 60px !important; max-width: 60px !important; }
    #glass-supplies-container .order-supply-row table td[style*="min-width: 160px"],
    #glass-supplies-container .order-supply-row table td[style*="min-width:160px"] { min-width: 120px !important; }
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
    
    /* TomSelect for table row accessories */
    .table .ts-wrapper.tom-select-accessory-product {
        width: 100% !important;
        min-width: 150px !important;
        margin: 0 !important;
        padding: 0 !important;
        display: block !important;
    }
    .table .ts-wrapper.tom-select-accessory-product .ts-control {
        min-height: 32px !important;
        height: 32px !important;
        padding: 2px 8px !important;
        font-size: 12px !important;
        border-radius: 8px !important;
        border: 1px solid #cbd5e1 !important;
        background-color: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        box-sizing: border-box !important;
    }
    .table .ts-wrapper.tom-select-accessory-product .ts-control input {
        font-size: 12px !important;
        height: auto !important;
        padding: 0 !important;
        margin: 0 !important;
        min-width: 0 !important;
    }
    .table .ts-wrapper.tom-select-accessory-product .ts-control .item {
        font-size: 12px !important;
        margin: 0 !important;
        padding: 0 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }
    
    /* Force high z-index for body-appended tom-select dropdowns */
    body > .ts-dropdown {
        z-index: 99999 !important;
    }
    
    .order-supply-row .ts-wrapper.tom-select-supply-code {
        width: 190px !important;
        display: block;
        flex-shrink: 0;
    }
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
        line-height: 22px !important;
        margin: 0 !important;
        padding: 0 !important;
        white-space: nowrap !important;
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
<div class="order-supplies-popup bg-white border border-neutral-200 rounded-xl p-6 shadow-sm relative pt-8" data-order-supplies-zoom-panel data-order-supplies-storage-key="glass" data-order-supplies-zoom="100" data-order-supplies-visible-rows="5">
    <div class="order-supplies-header flex items-center justify-between border-b border-neutral-100 pb-4 mb-5">
        <div class="absolute -top-3.5 left-6 bg-white px-3 flex items-center gap-2 z-10">
            <iconify-icon icon="lucide:package-open" class="text-xl text-primary-500"></iconify-icon>
            <h6 class="font-bold text-base text-neutral-800 m-0">Danh sách Vật tư & Sản phẩm (Kính)</h6>
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
            <button type="button" onclick="addGlassOrderSupply(false)" class="btn btn-sm btn-primary rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon> <span class="mobile-hide-text">Thêm vật tư</span>
            </button>
            <button type="button" onclick="addGlassOrderSupply(true)" class="btn btn-sm bg-neutral-100 text-neutral-700 hover:bg-neutral-200 border border-neutral-300 rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon> <span class="mobile-hide-text">Thêm phụ kiện</span>
            </button>
        </div>
    </div>
    <div class="order-supplies-body">
        <div id="glass-supplies-container" class="space-y-6">
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
                            $item->wing_opening_direction = $iData['wing_opening_direction'] ?? '';
                            $item->aluminum_color = $iData['aluminum_color'] ?? '';
                            $item->glass_color = $iData['glass_color'] ?? '';
                            $item->height = $iData['height'] ?? '';
                            $item->width = $iData['width'] ?? '';
                            $item->unit = $iData['unit'] ?? 'Bộ';
                            $item->wing_quantity = $iData['wing_quantity'] ?? 1;
                            $item->area_m2 = $iData['area_m2'] ?? '';
                            $item->unit_price = $iData['unit_price'] ?? 0;
                            $item->total_price = $iData['total_price'] ?? 0;
                            $item->notes = $iData['notes'] ?? '';
                            
                            $items->push($item);
                        }
                    }
                    $supply->glassItems = $items;
                    $supplies->push($supply);
                }
            } elseif (isset($acrylicOrder) && $acrylicOrder->type == 'glass' && $acrylicOrder->supplies->count() > 0) {
                $supplies = $acrylicOrder->supplies;
            }
        @endphp
        @if($supplies->count() > 0)
            @foreach($supplies as $supplyIndex => $supply)
            @php
                $isAccessory = ($supply->supply_name === 'Phụ kiện');
            @endphp
            <div class="order-supply-row bg-neutral-50/50 border border-primary-600 rounded-xl p-5 mb-2 relative shadow-sm" data-supply-id="{{ $supply->id }}" data-is-accessory="{{ $isAccessory ? '1' : '0' }}">
                
                {{-- Items inside this supply --}}
                <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                            <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                        </div>
                        @if($isAccessory)
                            <span class="badge bg-neutral-200 text-neutral-800 font-bold px-3 py-1.5 rounded-lg text-xs uppercase tracking-wider">Phụ kiện</span>
                            <input type="hidden" name="supplies[{{ $supplyIndex }}][order_supply_code]" value="">
                        @else
                            <select name="supplies[{{ $supplyIndex }}][order_supply_code]" class="order-supply-code-select tom-select-supply-code w-48">
                                <option value="">-- Mã vật tư --</option>
                                @foreach($glassPrices as $price)
                                    @if($price->code)
                                        <option value="{{ $price->code }}" {{ (isset($supply->order_supply_code) && $supply->order_supply_code == $price->code) ? 'selected' : '' }}>{{ $price->code }}</option>
                                    @endif
                                @endforeach
                                @if(isset($supply->order_supply_code) && $supply->order_supply_code !== '' && !$glassPrices->contains('code', $supply->order_supply_code))
                                    <option value="{{ $supply->order_supply_code }}" selected>{{ $supply->order_supply_code }}</option>
                                @endif
                            </select>
                        @endif
                        <input type="text" name="supplies[{{ $supplyIndex }}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)" value="{{ $supply->supply_name }}" {{ $isAccessory ? 'readonly' : '' }}>
                        <input type="number" name="supplies[{{ $supplyIndex }}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="any" value="{{ $supply->quantity ?? 0 }}">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="this.closest('.order-supply-row').remove(); updateOrderSummary(); if(typeof updateGlassRowIndexes === 'function') updateGlassRowIndexes();" class="text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                            <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto pb-3" data-order-supplies-table-scroll>
                    <div class="order-supplies-table-zoom-wrap" data-order-supplies-table-zoom-wrap>
                    <table class="table bordered-table sm-table mb-0 min-w-[1200px] border border-neutral-200">
                        <thead>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" style="width: 30px; min-width: 30px; white-space: nowrap;" class="sticky-stt-th align-middle text-center border border-neutral-200 font-bold text-xs text-neutral-600 uppercase"><span class="order-stt-header-label">STT</span></th>
                                <th scope="col" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã SP</th>
                                <th scope="col" style="width: 150px; min-width: 150px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên sản phẩm <span class="text-danger-500">*</span></th>
                                <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Độ dày</th>
                                <th scope="col" style="width: 150px; min-width: 150px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều mở cánh</th>
                                <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Màu nhôm</th>
                                <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Màu kính</th>
                                <th scope="col" style="width: 130px; min-width: 130px; white-space: nowrap;" class="align-middle border border-neutral-200 bg-yellow-100/70 font-bold text-xs text-neutral-600 uppercase text-center">Dài cánh (mm)</th>
                                <th scope="col" style="width: 130px; min-width: 130px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase text-center">Rộng cánh (mm)</th>
                                <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn vị</th>
                                <th scope="col" style="width: 50px; min-width: 50px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng cánh <span class="text-danger-500">*</span></th>
                                <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Khối lượng (m2)</th>
                                <th scope="col" style="width: 80px; min-width: 80px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                                <th scope="col" style="width: 90px; min-width: 90px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                                <th scope="col" style="min-width: 120px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                                <th scope="col" style="width: 60px; min-width: 60px; white-space: nowrap; position: sticky; right: 0; z-index: 3; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase bg-neutral-50">Hành động</th>
                            </tr>
                            <tr class="table-summary-row bg-neutral-100 font-bold text-neutral-800 text-center">
                                <td class="border border-neutral-200 text-center sticky-stt-td" style="font-size: 80% !important; background-color: #f1f5f9;">TỔNG</td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200 text-center" data-summary-field="quantity" style="font-size: 80% !important;">0</td>
                                <td class="border border-neutral-200 text-center" data-summary-field="weight" style="font-size: 80% !important;">0</td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200 text-center" data-summary-field="total_price" style="font-size: 80% !important;">0</td>
                                <td class="border border-neutral-200"></td>
                                <td class="border border-neutral-200" style="position: sticky; right: 0; z-index: 3; background-color: #f1f5f9;"></td>
                        </thead>
                        <tbody class="supply-items-container" data-supply-index="{{ $supplyIndex }}">
                            @if(isset($supply->glassItems) && $supply->glassItems->count() > 0)
                                @foreach($supply->glassItems as $itemIndex => $item)
                                <tr class="order-item-row" data-item-id="{{ $item->id }}">
                                    <td style="width: 45px; min-width: 45px; " class="sticky-stt-td text-center align-middle border border-neutral-200">
                                        <span class="row-index font-semibold text-neutral-500">{{ $itemIndex + 1 }}</span>
                                    </td>
                                    <td style="width: 160px; min-width: 160px; " class="border border-neutral-200">
                                        <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][id]" value="{{ $item->id }}">
                                        @if($isAccessory)
                                            <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_code]" class="accessory-product-code-select tom-select-accessory-product w-full text-xs font-semibold">
                                                <option value="">-- Mã SP --</option>
                                                @foreach($glassPrices as $price)
                                                    @if($price->category_name === 'PHỤ KIỆN & PHỤ PHÍ')
                                                        <option value="{{ $price->code }}" {{ $item->product_code == $price->code ? 'selected' : '' }} data-name="{{ $price->product_name }}" data-price="{{ $price->price }}" data-unit="{{ $price->unit ?? 'Cái' }}">{{ $price->code }} - {{ $price->product_name }}</option>
                                                    @endif
                                                @endforeach
                                                @if($item->product_code !== '' && !$glassPrices->contains('code', $item->product_code))
                                                    <option value="{{ $item->product_code }}" selected>{{ $item->product_code }}</option>
                                                @endif
                                            </select>
                                        @else
                                            <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_code]" class="product-code-input form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs font-semibold text-neutral-600" value="{{ $item->product_code }}" readonly>
                                        @endif
                                    </td>
                                    <td style="min-width: 220px;" class="border border-neutral-200">
                                        <textarea name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-xs w-full" rows="3" placeholder="Tên sản phẩm">{{ $item->product_name }}</textarea>
                                    </td>
                                    <td style="width: 100px; min-width: 100px;" class="border border-neutral-200">
                                        <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][thickness]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Độ dày" value="{{ $item->thickness }}">
                                    </td>
                                    <td style="width: 150px; min-width: 150px; " class="border border-neutral-200 p-1">
                                        <div class="wing-direction-container flex items-center gap-1 w-full">
                                            <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][wing_opening_direction]" class="actual-wing-direction" value="{{ $item->wing_opening_direction }}">
                                            <div class="flex items-center gap-1 w-1/2" title="Số lượng cánh mở Trái">
                                                <span class="text-[11px] text-neutral-500 font-semibold wing-direction-label">Trái:</span>
                                                <input type="number" class="qty-left form-control form-control-sm rounded-md border-neutral-300 focus:border-primary-500 text-xs px-1 w-full h-7 text-center" placeholder="SL" min="0" oninput="updateWingDirection(this)">
                                            </div>
                                            <div class="flex items-center gap-1 w-1/2" title="Số lượng cánh mở Phải">
                                                <span class="text-[11px] text-neutral-500 font-semibold wing-direction-label">Phải:</span>
                                                <input type="number" class="qty-right form-control form-control-sm rounded-md border-neutral-300 focus:border-primary-500 text-xs px-1 w-full h-7 text-center" placeholder="SL" min="0" oninput="updateWingDirection(this)">
                                            </div>
                                        </div>
                                    </td>
                                    <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                        <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][aluminum_color]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Màu nhôm" value="{{ $item->aluminum_color }}">
                                    </td>
                                    <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                        <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][glass_color]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Màu kính" value="{{ $item->glass_color }}">
                                    </td>
                                    <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                        <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="Dài cánh (mm)" step="any" value="{{ $item->height }}">
                                    </td>
                                    <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                        <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Rộng cánh (mm)" step="any" value="{{ $item->width }}">
                                    </td>
                                    <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                        <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][unit]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs px-1" placeholder="Đơn vị" value="{{ $item->unit ?? 'Bộ' }}">
                                    </td>
                                    <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
                                        <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][wing_quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Số lượng cánh" min="1" required value="{{ $item->wing_quantity ?? 1 }}">
                                    </td>
                                    <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                        <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][area_m2]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Khối lượng (m2)" step="any" value="{{ $item->area_m2 }}">
                                    </td>
                                    <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
                                        <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][unit_price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Đơn giá" min="0" step="any" required value="{{ $item->unit_price }}">
                                    </td>
                                    <td style="width: 120px; min-width: 120px; " class="border border-neutral-200">
                                        <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][total_price]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center px-1 py-1 h-8 text-xs" placeholder="Thành tiền" readonly value="{{ number_format($item->total_price, 0, ',', '.') }}">
                                    </td>
                                    <td style="min-width: 160px;" class="border border-neutral-200">
                                        <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Ghi chú" value="{{ $item->notes }}">
                                    </td>
                                    <td style="width: 80px; min-width: 80px; " class="text-center align-middle border border-neutral-200">
                                        <div class="flex items-center gap-1 justify-center">
                                            <button type="button" onclick="insertGlassRow(this)" class="text-neutral-400 hover:text-success-500 transition-colors p-1" title="Chèn dòng mới ở dưới">
                                                <iconify-icon icon="lucide:list-plus" class="text-base"></iconify-icon>
                                            </button>
                                            <button type="button" onclick="duplicateGlassRow(this)" class="text-neutral-400 hover:text-primary-500 transition-colors p-1" title="Nhân bản sản phẩm">
                                                <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                                            </button>
                                            <button type="button" onclick="removeGlassOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
                                                <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                    </div>
                </div>
                <button type="button" onclick="addGlassOrderItem(this)" class="w-full mt-4 py-3 border-2 border-dashed border-primary-300 hover:border-primary-500 rounded-xl bg-primary-50/50 hover:bg-primary-50 text-primary-600 font-semibold text-sm flex items-center justify-center gap-1.5 transition-all duration-200">
                    <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon>
                    Thêm sản phẩm mới
                </button>
            </div>
            @endforeach
            @php $glassSupplyIndex = $acrylicOrder->supplies->count() @endphp
        @else
            @php $glassSupplyIndex = 0 @endphp
        @endif
        </div>
    </div>
</div>

<script>
window.glassPricesData = @json($glassPrices ?? []);
let glassSupplyIndex = {{ $glassSupplyIndex ?? 0 }};

function addGlassOrderSupply(isAccessory = false) {
    const container = document.getElementById('glass-supplies-container');
    const newSupply = document.createElement('div');
    newSupply.className = 'order-supply-row bg-neutral-50/50 border border-primary-600 rounded-xl p-5 mb-2 relative shadow-sm';
    if (isAccessory) {
        newSupply.dataset.isAccessory = '1';
    }

    let headerHtml = '';
    if (isAccessory) {
        headerHtml = `
            <span class="badge bg-neutral-200 text-neutral-800 font-bold px-3 py-1.5 rounded-lg text-xs uppercase tracking-wider">Phụ kiện</span>
            <input type="hidden" name="supplies[${glassSupplyIndex}][order_supply_code]" value="">
            <input type="text" name="supplies[${glassSupplyIndex}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư" value="Phụ kiện" readonly>
            <input type="number" name="supplies[${glassSupplyIndex}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="0.01" value="0">
        `;
    } else {
        headerHtml = `
            <select name="supplies[${glassSupplyIndex}][order_supply_code]" class="order-supply-code-select tom-select-supply-code w-48">
                <option value="">-- Mã vật tư --</option>
                ${(window.glassPricesData || []).filter(p => p.code).map(p => `<option value="${p.code}">${p.code}</option>`).join('')}
            </select>
            <input type="text" name="supplies[${glassSupplyIndex}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)">
            <input type="number" name="supplies[${glassSupplyIndex}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="0.01" value="0">
        `;
    }

    newSupply.innerHTML = `
        <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                    <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                </div>
                ${headerHtml}
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="this.closest('.order-supply-row').remove(); updateOrderSummary(); if(typeof updateGlassRowIndexes === 'function') updateGlassRowIndexes();" class="order-table-form-action text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                    <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto pb-3" data-order-supplies-table-scroll>
            <div class="order-supplies-table-zoom-wrap" data-order-supplies-table-zoom-wrap>
            <table class="table bordered-table sm-table mb-0 min-w-[1800px] border border-neutral-200">
                <thead>
                    <tr class="bg-neutral-50 text-center">
                        <th scope="col" style="width: 45px; min-width: 45px; white-space: nowrap;" class="sticky-stt-th align-middle text-center border border-neutral-200 font-bold text-xs text-neutral-600 uppercase"><span class="order-stt-header-label">STT</span></th>
                        <th scope="col" style="width: 160px; min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã SP</th>
                        <th scope="col" style="min-width: 220px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên sản phẩm <span class="text-danger-500">*</span></th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Độ dày</th>
                        <th scope="col" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều mở cánh</th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Màu nhôm</th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Màu kính</th>
                        <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="align-middle border border-neutral-200 bg-yellow-100/70 font-bold text-xs text-neutral-600 uppercase text-center">Dài cánh (mm)</th>
                        <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase text-center">Rộng cánh (mm)</th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn vị</th>
                        <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng cánh <span class="text-danger-500">*</span></th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Khối lượng (m2)</th>
                        <th scope="col" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                        <th scope="col" style="width: 120px; min-width: 120px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                        <th scope="col" style="min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                        <th scope="col" style="width: 80px; min-width: 80px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Hành động</th>
                    </tr>
                    <tr class="table-summary-row bg-neutral-100 font-bold text-neutral-800 text-center">
                        <td class="border border-neutral-200 text-center sticky-stt-td" style="font-size: 80% !important; background-color: #f1f5f9;">TỔNG</td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200 text-center" data-summary-field="quantity" style="font-size: 80% !important;">0</td>
                        <td class="border border-neutral-200 text-center" data-summary-field="weight" style="font-size: 80% !important;">0</td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200 text-center" data-summary-field="total_price" style="font-size: 80% !important;">0</td>
                        <td class="border border-neutral-200"></td>
                        <td class="border border-neutral-200" style="position: sticky; right: 0; z-index: 3; background-color: #f1f5f9;"></td>
                </thead>
                <tbody class="supply-items-container" data-supply-index="${glassSupplyIndex}">
                </tbody>
            </table>
            </div>
        </div>
        <button type="button" onclick="addGlassOrderItem(this)" class="order-table-form-action w-full mt-4 py-3 border-2 border-dashed border-primary-300 hover:border-primary-500 rounded-xl bg-primary-50/50 hover:bg-primary-50 text-primary-600 font-semibold text-sm flex items-center justify-center gap-1.5 transition-all duration-200">
            <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon>
            Thêm sản phẩm mới
        </button>
    `;

    const selectEl = newSupply.querySelector('.tom-select-supply-code');
    if (selectEl && typeof TomSelect !== 'undefined' && !selectEl.tomselect) {
        const ts = new TomSelect(selectEl, {
            create: true,
            placeholder: '-- Mã vật tư --',
            allowEmptyOption: true,
            maxOptions: null
        });
        ts.on('change', function(value) {
            handleGlassSupplyCodeChange(selectEl, value);
            updateGlassRowIndexes();
        });
    }
    container.appendChild(newSupply);

    // Add one product row by default
    const addProductBtn = newSupply.querySelector('button[onclick^="addGlassOrderItem"]');
    if (addProductBtn) {
        addGlassOrderItem(addProductBtn, true);
    }

    const panel = newSupply.closest('[data-order-supplies-zoom-panel]');
    if (panel) {
        applyOrderSuppliesZoom(panel, panel.dataset.orderSuppliesZoom || 100);
    }

    glassSupplyIndex++;
    if (typeof window.initOrderSuppliesHeightResize === 'function') {
        window.initOrderSuppliesHeightResize();
    }
}

function addGlassOrderItem(button, isInitial = false, insertAfterRow = null) {
    const supplyRow = button.closest('.order-supply-row');
    const supplyIndex = supplyRow.querySelector('.supply-items-container').dataset.supplyIndex;
    const container = supplyRow.querySelector('.supply-items-container');
    const itemIndex = container.querySelectorAll('.order-item-row').length;
    const isAccessory = supplyRow.dataset.isAccessory === '1' || 
                        (supplyRow.querySelector('input[name*="[supply_name]"]')?.value === 'Phụ kiện');
    
    // Sao chép thông tin từ mã vật tư được chọn nếu có
    let lastData = null;
    const supplyCodeSelect = supplyRow.querySelector('.tom-select-supply-code');
    const selectedCode = supplyCodeSelect ? supplyCodeSelect.value : '';
    if (selectedCode && window.glassPricesData) {
        const price = window.glassPricesData.find(p => p.code === selectedCode);
        if (price) {
            let thickness = '';
            if (price.product_name) {
                const match = price.product_name.match(/(\d+\s*mm)/i);
                if (match) {
                    thickness = match[1];
                }
            }
            lastData = {
                product_name: price.product_name || '',
                thickness: thickness,
                glass_color: price.glass_color || '',
                unit: price.unit || 'Bộ',
                unit_price: price.price || 0
            };
        }
    }

    // Sao chép đơn giá từ dòng cuối nếu có
    const lastRow = container.querySelector('.order-item-row:last-of-type');
    let copiedUnitPrice = lastData ? lastData.unit_price : '';
    if (!copiedUnitPrice && lastRow) {
        const lastUnitPriceInput = lastRow.querySelector('input[name*="[unit_price]"]');
        if (lastUnitPriceInput && lastUnitPriceInput.value !== '') {
            copiedUnitPrice = lastUnitPriceInput.value;
        }
    }

    let productCodeCellHtml = '';
    if (isAccessory) {
        productCodeCellHtml = `
            <select name="supplies[${supplyIndex}][items][${itemIndex}][product_code]" class="accessory-product-code-select tom-select-accessory-product w-full text-xs font-semibold">
                <option value="">-- Mã SP --</option>
                ${(window.glassPricesData || []).filter(p => p.category_name === 'PHỤ KIỆN & PHỤ PHÍ').map(p => `
                    <option value="${p.code}" data-name="${p.product_name || ''}" data-price="${p.price || 0}" data-unit="${p.unit || 'Cái'}">${p.code} - ${p.product_name}</option>
                `).join('')}
            </select>
        `;
    } else {
        productCodeCellHtml = `
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][product_code]" class="product-code-input form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs font-semibold text-neutral-600" readonly>
        `;
    }

    const newItem = document.createElement('tr');
    newItem.className = 'order-item-row';
    newItem.innerHTML = `
        <td style="width: 45px; min-width: 45px; " class="sticky-stt-td text-center align-middle border border-neutral-200">
            <span class="row-index font-semibold text-neutral-500">${itemIndex + 1}</span>
        </td>
        <td style="width: 160px; min-width: 160px; " class="border border-neutral-200">
            <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][id]" value="">
            ${productCodeCellHtml}
        </td>
        <td style="min-width: 220px;" class="border border-neutral-200">
            <textarea name="supplies[${supplyIndex}][items][${itemIndex}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-xs w-full" rows="3" placeholder="Tên sản phẩm">${lastData ? lastData.product_name : ''}</textarea>
        </td>
        <td style="width: 100px; min-width: 100px;" class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][thickness]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Độ dày" value="${lastData ? lastData.thickness : ''}">
        </td>
        <td style="width: 150px; min-width: 150px; " class="border border-neutral-200 p-1">
            <div class="wing-direction-container flex items-center gap-1 w-full">
                <input type="hidden" name="supplies[${supplyIndex}][items][${itemIndex}][wing_opening_direction]" class="actual-wing-direction" value="${lastData ? lastData.wing_opening_direction : ''}">
                <div class="flex items-center gap-1 w-1/2" title="Số lượng cánh mở Trái">
                    <span class="text-[11px] text-neutral-500 font-semibold wing-direction-label">Trái:</span>
                    <input type="number" class="qty-left form-control form-control-sm rounded-md border-neutral-300 focus:border-primary-500 text-xs px-1 w-full h-7 text-center" placeholder="SL" min="0" oninput="updateWingDirection(this)">
                </div>
                <div class="flex items-center gap-1 w-1/2" title="Số lượng cánh mở Phải">
                    <span class="text-[11px] text-neutral-500 font-semibold wing-direction-label">Phải:</span>
                    <input type="number" class="qty-right form-control form-control-sm rounded-md border-neutral-300 focus:border-primary-500 text-xs px-1 w-full h-7 text-center" placeholder="SL" min="0" oninput="updateWingDirection(this)">
                </div>
            </div>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][aluminum_color]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Màu nhôm" value="${lastData ? lastData.aluminum_color : ''}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][glass_color]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Màu kính" value="${lastData ? lastData.glass_color : ''}">
        </td>
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="Dài cánh (mm)" step="any" value="${lastData ? lastData.height : ''}">
        </td>
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Rộng cánh (mm)" step="any" value="${lastData ? lastData.width : ''}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][unit]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs px-1" placeholder="Đơn vị" value="${lastData ? lastData.unit : (isAccessory ? 'Cái' : 'Bộ')}">
        </td>
        <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][wing_quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="${isAccessory ? 'Số lượng' : 'Số lượng cánh'}" min="1" required value="${lastData ? lastData.wing_quantity : '1'}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][area_m2]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Khối lượng (m2)" step="any" value="${lastData ? lastData.area_m2 : ''}" ${isAccessory ? 'readonly disabled bg-neutral-100' : ''}>
        </td>
        <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][unit_price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Đơn giá" min="0" step="any" required value="${copiedUnitPrice}">
        </td>
        <td style="width: 120px; min-width: 120px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][total_price]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center px-1 py-1 h-8 text-xs" placeholder="Thành tiền" readonly value="${lastData && lastData.total_price > 0 ? parseFloat(lastData.total_price).toLocaleString('vi-VN') : ''}">
        </td>
        <td style="min-width: 160px;" class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Ghi chú" value="${lastData ? lastData.notes : ''}">
        </td>
        <td style="width: 80px; min-width: 80px; " class="text-center align-middle border border-neutral-200">
            <div class="flex items-center gap-1 justify-center">
                <button type="button" onclick="insertGlassRow(this)" class="text-neutral-400 hover:text-success-500 transition-colors p-1" title="Chèn dòng mới ở dưới">
                    <iconify-icon icon="lucide:list-plus" class="text-base"></iconify-icon>
                </button>
                <button type="button" onclick="duplicateGlassRow(this)" class="text-neutral-400 hover:text-primary-500 transition-colors p-1" title="Nhân bản sản phẩm">
                    <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                </button>
                <button type="button" onclick="removeGlassOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
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

    // Khởi tạo TomSelect cho Mã SP phụ kiện nếu là dòng phụ kiện
    if (isAccessory) {
        const selectEl = newItem.querySelector('.tom-select-accessory-product');
        if (selectEl && typeof TomSelect !== 'undefined' && !selectEl.tomselect) {
            const ts = new TomSelect(selectEl, {
                create: false,
                placeholder: '-- Mã SP --',
                allowEmptyOption: true,
                maxOptions: null,
                dropdownParent: 'body'
            });
            ts.on('change', function(value) {
                handleAccessoryProductCodeChange(selectEl, value);
            });
        }
    }

    const newRow = insertAfterRow ? insertAfterRow.nextElementSibling : container.lastElementChild;
    
    // Force table reflow to fix Chrome sticky cell border-collapse rendering bug
    const table = newRow.closest('table');

    
    bindGlassRowEvents(newRow);
    
    // Initialize the wing direction inputs
    const wingContainer = newRow.querySelector('.wing-direction-container');
    if (wingContainer) initWingDirection(wingContainer);

    updateOrderSummary();
    updateGlassRowIndexes();

    // Tự động cuộn xuống dòng mới thêm và focus vào ô Chiều mở cánh (chỉ khi được thêm thủ công)
    if (!isInitial) {
        setTimeout(() => {
            newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            const focusInput = newRow.querySelector('input[name*="[wing_opening_direction]"]');
            if (focusInput) {
                focusInput.focus();
            }
        }, 50);
    }
}

function removeGlassOrderItem(button) {
    const row = button.closest('.order-item-row');
    const tbody = row.closest('.supply-items-container');
    const table = row.closest('table');
    row.remove();
    updateOrderSummary();
    updateGlassRowIndexes();

}

function updateGlassRowIndexes() {
    const orderCode = document.getElementById('order-code-input')?.value || '';
    let globalPieceIndex = 1;
    let globalItemIndex = 1;

    document.querySelectorAll('#glass-supplies-container .order-supply-row').forEach((supplyRow, supplyIndex) => {
        const supplyCode = (supplyRow.querySelector('.order-supply-code-select')?.value || supplyRow.querySelector('.order-supply-code-input')?.value || '');
        const tbody = supplyRow.querySelector('.supply-items-container');
        if (!tbody) return;

        const isAccessory = supplyRow.dataset.isAccessory === '1' || 
                            (supplyRow.querySelector('input[name*="[supply_name]"]')?.value === 'Phụ kiện');

        tbody.querySelectorAll('.order-item-row').forEach((row, itemIndex) => {
            // Update row STT
            const indexEl = row.querySelector('.row-index');
            if (indexEl) indexEl.textContent = globalItemIndex;

            // Get quantity (Glass uses wing_quantity)
            const quantityInput = row.querySelector('input[name*="[wing_quantity]"]');
            const qty = parseInt(quantityInput?.value) || 1;

            if (!isAccessory) {
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

                globalPieceIndex += qty;
            } else {
                const idsContainer = row.querySelector('.product-ids-container');
                if (idsContainer) {
                    idsContainer.remove();
                }
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
            const headers = Array.from(supplyRow.querySelectorAll('thead th')).map(th => th.textContent.trim().replace(/\s\*$/, ''));
            row.querySelectorAll('td').forEach((td, colIndex) => {
                if (headers[colIndex] && headers[colIndex] !== 'STT' && headers[colIndex] !== 'Hành động' && headers[colIndex] !== 'Xóa') {
                    td.setAttribute('data-label', headers[colIndex]);
                }
            });

            globalItemIndex++;
        });
    });
}

function bindGlassRowEvents(row) {
    const heightInput = row.querySelector('input[name*="[height]"]');
    const widthInput = row.querySelector('input[name*="[width]"]');
    const wingQtyInput = row.querySelector('input[name*="[wing_quantity]"]');
    const areaInput = row.querySelector('input[name*="[area_m2]"]');
    const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
    
    if (heightInput) heightInput.addEventListener('input', () => calculateGlassTotalPrice(row, 'height'));
    if (widthInput) widthInput.addEventListener('input', () => calculateGlassTotalPrice(row, 'width'));
    if (wingQtyInput) {
        wingQtyInput.addEventListener('input', () => {
            calculateGlassTotalPrice(row, 'wing_quantity');
            updateGlassRowIndexes();
        });
    }
    if (areaInput) areaInput.addEventListener('input', () => calculateGlassTotalPrice(row, 'area'));
    if (unitPriceInput) unitPriceInput.addEventListener('input', () => calculateGlassTotalPrice(row, 'unit_price'));
}

function handleGlassSupplyCodeChange(selectEl, value) {
    if (!value) return;
    
    const glassPrice = (window.glassPricesData || []).find(p => p.code === value);
    if (!glassPrice) return;
    
    const supplyRow = selectEl.closest('.order-supply-row');
    if (!supplyRow) return;
    
    // 1. Tên vật tư (supply_name)
    const supplyNameInput = supplyRow.querySelector('input[name*="[supply_name]"]');
    if (supplyNameInput && glassPrice.category_name) {
        supplyNameInput.value = glassPrice.category_name;
        applyFlashEffect(supplyNameInput);
    }
    
    // 2. Cập nhật tất cả các dòng sản phẩm hiện có dưới card vật tư này
    const itemRows = supplyRow.querySelectorAll('.order-item-row');
    itemRows.forEach(itemRow => {
        // Tên sản phẩm
        const productNameInput = itemRow.querySelector('[name*="[product_name]"]');
        if (productNameInput) {
            productNameInput.value = glassPrice.product_name || '';
            applyFlashEffect(productNameInput);
        }
        
        // Độ dày (thickness) - Trích xuất từ product_name dùng regex (\d+\s*mm)
        const thicknessInput = itemRow.querySelector('input[name*="[thickness]"]');
        if (thicknessInput) {
            let thickness = '';
            if (glassPrice.product_name) {
                const match = glassPrice.product_name.match(/(\d+\s*mm)/i);
                if (match) {
                    thickness = match[1];
                }
            }
            thicknessInput.value = thickness;
            applyFlashEffect(thicknessInput);
        }
        
        // Màu kính (glass_color)
        const glassColorInput = itemRow.querySelector('input[name*="[glass_color]"]');
        if (glassColorInput) {
            glassColorInput.value = glassPrice.glass_color || '';
            applyFlashEffect(glassColorInput);
        }
        
        // Đơn vị (unit)
        const unitInput = itemRow.querySelector('input[name*="[unit]"]');
        if (unitInput) {
            unitInput.value = glassPrice.unit || 'Bộ';
            applyFlashEffect(unitInput);
        }
        
        // Đơn giá (unit_price)
        const unitPriceInput = itemRow.querySelector('input[name*="[unit_price]"]');
        if (unitPriceInput) {
            unitPriceInput.value = glassPrice.price || 0;
            applyFlashEffect(unitPriceInput);
        }
        
        // Tính lại thành tiền
        calculateGlassTotalPrice(itemRow);
    });
}

function handleAccessoryProductCodeChange(selectEl, value) {
    if (!value) return;
    
    const glassPrice = (window.glassPricesData || []).find(p => p.code === value);
    if (!glassPrice) return;
    
    const row = selectEl.closest('.order-item-row');
    if (!row) return;
    
    // 1. Tên sản phẩm (product_name)
    const productNameInput = row.querySelector('[name*="[product_name]"]');
    if (productNameInput) {
        productNameInput.value = glassPrice.product_name || '';
        applyFlashEffect(productNameInput);
    }
    
    // 2. Độ dày (thickness)
    const thicknessInput = row.querySelector('input[name*="[thickness]"]');
    if (thicknessInput) {
        let thickness = '';
        if (glassPrice.product_name) {
            const match = glassPrice.product_name.match(/(\d+\s*mm)/i);
            if (match) {
                thickness = match[1];
            }
        }
        thicknessInput.value = thickness;
        applyFlashEffect(thicknessInput);
    }
    
    // 3. Màu kính (glass_color)
    const glassColorInput = row.querySelector('input[name*="[glass_color]"]');
    if (glassColorInput) {
        glassColorInput.value = glassPrice.glass_color || '';
        applyFlashEffect(glassColorInput);
    }
    
    // 4. Đơn vị (unit)
    const unitInput = row.querySelector('input[name*="[unit]"]');
    if (unitInput) {
        unitInput.value = glassPrice.unit || 'Cái';
        applyFlashEffect(unitInput);
    }
    
    // 5. Đơn giá (unit_price)
    const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
    if (unitPriceInput) {
        unitPriceInput.value = glassPrice.price || 0;
        applyFlashEffect(unitPriceInput);
    }
    
    // Tính lại thành tiền
    calculateGlassTotalPrice(row);
}

function applyFlashEffect(el) {
    if (!el) return;
    el.style.transition = 'background-color 0.4s ease';
    el.style.backgroundColor = '#ecfdf5';
    setTimeout(() => {
        el.style.backgroundColor = '';
    }, 850);
}

function insertGlassRow(button) {
    const currentRow = button.closest('.order-item-row');
    addGlassOrderItem(button, false, currentRow);
}

function duplicateGlassRow(button) {
    const row = button.closest('.order-item-row');
    const tbody = row.closest('.supply-items-container');
    const supplyRow = row.closest('.order-supply-row');
    const isAccessory = supplyRow && (supplyRow.dataset.isAccessory === '1' || supplyRow.querySelector('input[name*="[supply_name]"]')?.value === 'Phụ kiện');

    // Collect all input/select/textarea values BEFORE cloning
    const valuesToCopy = [];
    row.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(el => {
        valuesToCopy.push({ name: el.name, value: el.value });
    });

    const newRow = row.cloneNode(true);

    // Remove database ID so it creates a new entry
    const idInput = newRow.querySelector('input[name*="[id]"]');
    if (idInput) idInput.remove();

    // Clean up cloned TomSelect wrapper if any
    const tsWrappers = newRow.querySelectorAll('.ts-wrapper');
    tsWrappers.forEach(w => w.remove());
    // Show the select element again
    newRow.querySelectorAll('.tom-select-accessory-product').forEach(sel => {
        sel.style.display = '';
        sel.tomselect = null;
        sel.className = 'accessory-product-code-select tom-select-accessory-product w-full text-xs font-semibold';
    });

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

    // Re-initialize TomSelect for any .tom-select-accessory-product in newRow
    newRow.querySelectorAll('.tom-select-accessory-product').forEach(selectEl => {
        if (typeof TomSelect !== 'undefined') {
            const ts = new TomSelect(selectEl, {
                create: false,
                placeholder: '-- Mã SP --',
                allowEmptyOption: true,
                maxOptions: null,
                dropdownParent: 'body'
            });
            ts.on('change', function(value) {
                handleAccessoryProductCodeChange(selectEl, value);
            });
            // Restore value
            const matchingVal = valuesToCopy.find(v => v.name === selectEl.name);
            if (matchingVal) {
                ts.setValue(matchingVal.value, true);
            }
        }
    });

    bindGlassRowEvents(newRow);
    updateGlassRowIndexes();
    updateOrderSummary();
}

function calculateGlassTotalPrice(row, sourceEvent) {
    const supplyRow = row.closest('.order-supply-row');
    const isAccessory = supplyRow && (supplyRow.dataset.isAccessory === '1' || supplyRow.querySelector('input[name*="[supply_name]"]')?.value === 'Phụ kiện');

    const heightInput = row.querySelector('input[name*="[height]"]');
    const widthInput = row.querySelector('input[name*="[width]"]');
    const wingQtyInput = row.querySelector('input[name*="[wing_quantity]"]');
    const areaInput = row.querySelector('input[name*="[area_m2]"]');
    const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
    
    if (!wingQtyInput || !unitPriceInput) return;

    const wingQty = parseFloat(wingQtyInput.value) || 0;
    const unitPrice = parseFloat(unitPriceInput.value) || 0;
    
    if (isAccessory) {
        if (areaInput) {
            areaInput.value = '';
        }
        const totalPrice = wingQty * unitPrice;
        const totalPriceInput = row.querySelector('input[name*="[total_price]"]');
        if (totalPriceInput) {
            totalPriceInput.value = Math.round(totalPrice);
        }
        updateOrderSummary();
        return;
    }

    if (!heightInput || !widthInput || !areaInput) return;

    const height = parseFloat(heightInput.value) || 0;
    const width = parseFloat(widthInput.value) || 0;
    
    if (sourceEvent !== 'area') {
        let area = 0;
        if (height > 0 && width > 0 && wingQty > 0) {
            area = (height * width * wingQty) / 1000000;
        }
        areaInput.value = area > 0 ? area : '';
    }
    
    const currentArea = parseFloat(areaInput.value) || 0;
    const totalPrice = currentArea * unitPrice;
    
    const totalPriceInput = row.querySelector('input[name*="[total_price]"]');
    if (totalPriceInput) {
        totalPriceInput.value = totalPrice > 0 ? totalPrice.toLocaleString('vi-VN') : '';
    }
    updateOrderSummary();
}



// Initial setup for Glass-specific rows if DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('glass-supplies-container');
    if (container && container.querySelectorAll('.order-supply-row').length === 0) {
        addGlassOrderSupply();
    }

    document.querySelectorAll('#glass-supplies-container .order-item-row').forEach(row => {
        bindGlassRowEvents(row);
        calculateGlassTotalPrice(row);
        
        // Initialize wing direction for existing rows
        const wingContainer = row.querySelector('.wing-direction-container');
        if (wingContainer) initWingDirection(wingContainer);
    });

    // Recalculate codes on load
    updateGlassRowIndexes();

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
                handleGlassSupplyCodeChange(element, value);
                updateGlassRowIndexes();
            });
        });

        // Khởi tạo TomSelect cho Mã SP của các dòng phụ kiện hiện có
        document.querySelectorAll('.tom-select-accessory-product').forEach(function(element) {
            if (element.tomselect) return;
            const ts = new TomSelect(element, {
                create: false,
                placeholder: '-- Mã SP --',
                allowEmptyOption: true,
                maxOptions: null,
                dropdownParent: 'body'
            });
            ts.on('change', function(value) {
                handleAccessoryProductCodeChange(element, value);
            });
        });
    }

    const orderCodeInput = document.getElementById('order-code-input');
    if (orderCodeInput) {
        orderCodeInput.addEventListener('input', () => updateGlassRowIndexes());
    }
});

// Live listener for supply code input changes
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('order-supply-code-select')) {
        updateGlassRowIndexes();
    }
});

// Wing Direction Logic
function updateWingDirection(el) {
    const container = el.closest('.wing-direction-container');
    const hiddenInput = container.querySelector('.actual-wing-direction');
    const leftQty = container.querySelector('.qty-left').value;
    const rightQty = container.querySelector('.qty-right').value;
    
    let parts = [];
    if (leftQty && leftQty > 0) {
        parts.push(`${leftQty} mở trái`);
    }
    if (rightQty && rightQty > 0) {
        parts.push(`${rightQty} mở phải`);
    }
    
    hiddenInput.value = parts.join(', ');
}

function initWingDirection(container) {
    const hiddenInput = container.querySelector('.actual-wing-direction');
    const leftInput = container.querySelector('.qty-left');
    const rightInput = container.querySelector('.qty-right');
    
    let val = (hiddenInput.value || '').trim().toLowerCase();
    
    leftInput.value = '';
    rightInput.value = '';
    
    if (val) {
        // Look for explicit quantities
        const leftMatch = val.match(/(\d+)\s*(?:mở\s*)?trái/i);
        const rightMatch = val.match(/(\d+)\s*(?:mở\s*)?phải/i);
        
        if (leftMatch) {
            leftInput.value = leftMatch[1];
        } else if (val === 'trái' || val === 'mở trái') {
            leftInput.value = 1; // Default back to 1 if just "Trái"
        }
        
        if (rightMatch) {
            rightInput.value = rightMatch[1];
        } else if (val === 'phải' || val === 'mở phải') {
            rightInput.value = 1; // Default back to 1 if just "Phải"
        }
    }
}
</script>
