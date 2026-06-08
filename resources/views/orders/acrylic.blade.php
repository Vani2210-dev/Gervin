{{-- Order Supplies & Items Section Card --}}
<style>
    /* Fix sticky column overlap bug in two-row table headers */
    .table thead tr:nth-child(2) th:last-child {
        position: static !important;
        background-color: inherit !important;
        box-shadow: none !important;
    }
    .table thead tr:nth-child(2) th:last-child::before {
        display: none !important;
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
    #order-supplies-container .order-supply-row table th,
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
        padding: 8px 12px !important;
        font-size: 13px !important;
        border-radius: 8px !important;
        min-height: 38px !important;
        height: 38px !important;
        line-height: 20px !important;
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
    .order-supply-row .ts-wrapper.tom-select-supply-code .ts-dropdown .option {
        padding: 8px 12px !important;
        font-size: 13px !important;
    }
</style>
<div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm">
    <div class="flex items-center justify-between border-b border-neutral-100 pb-4 mb-5">
        <div class="flex items-center gap-2">
            <iconify-icon icon="lucide:package-open" class="text-xl text-primary-500"></iconify-icon>
            <h6 class="font-bold text-base text-neutral-800 m-0">Danh sách Vật tư & Sản phẩm (Acrylic)</h6>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="addOrderSupply()" class="btn btn-sm btn-primary rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon> Thêm vật tư
            </button>
        </div>
    </div>
    <div id="order-supplies-container" class="space-y-6">
        @if(isset($acrylicOrder) && in_array($acrylicOrder->type, ['acrylic', 'glass']) && $acrylicOrder->supplies->count() > 0)
            @foreach($acrylicOrder->supplies as $supplyIndex => $supply)
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
                        <input type="number" name="supplies[{{ $supplyIndex }}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="0.01" value="{{ $supply->quantity ?? 0 }}">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="this.closest('.order-supply-row').remove()" class="text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                            <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto pb-3">
                    <table class="table bordered-table sm-table mb-0 min-w-[1100px] border border-neutral-200">
                        <thead>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" rowspan="2" style="width: 30px; min-width: 30px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Stt</th>
                                <th scope="col" rowspan="2" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã SP</th>
                                <th scope="col" rowspan="2" style="width: 150px; min-width: 150px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên sản phẩm <span class="text-danger-500">*</span></th>
                                <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Độ dày</th>
                                <th scope="col" colspan="2" style="white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Kích thước (mm)</th>
                                <th scope="col" rowspan="2" style="width: 50px; min-width: 50px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng <span class="text-danger-500">*</span></th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Cạnh Vát</th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều vân</th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Cánh (m2)</th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Phào (m)</th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Vát</th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Vân dọc CNC</th>
                                <th scope="col" rowspan="2" style="width: 80px; min-width: 80px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                                <th scope="col" rowspan="2" style="width: 90px; min-width: 90px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                                <th scope="col" rowspan="2" style="min-width: 120px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                                <th scope="col" rowspan="2" style="width: 60px; min-width: 60px; white-space: nowrap; position: sticky; right: 0; z-index: 3; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase bg-neutral-50">Hành động</th>
                            </tr>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" style="width: 130px; min-width: 130px; white-space: nowrap;" class="border border-neutral-200 bg-yellow-100/70 font-semibold text-xs text-neutral-700 uppercase">Cao (chiều vân)</th>
                                <th scope="col" style="width: 130px; min-width: 130px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
                            </tr>
                        </thead>
                        <tbody class="supply-items-container" data-supply-index="{{ $supplyIndex }}">
                            @foreach($supply->items as $itemIndex => $item)
                            <tr class="order-item-row" data-item-id="{{ $item->id }}">
                                <td style="width: 45px; min-width: 45px; " class="text-center align-middle border border-neutral-200">
                                    <span class="row-index font-semibold text-neutral-500">{{ $itemIndex + 1 }}</span>
                                </td>
                                <td style="width: 160px; min-width: 160px; " class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_code]" class="product-code-input form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs font-semibold text-neutral-600" readonly value="{{ $item->product_code ?? '' }}">
                                </td>
                                <td style="min-width: 220px;" class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Tên sản phẩm" value="{{ $item->product_name }}">
                                </td>
                                <td style="width: 100px; min-width: 100px;" class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][thickness]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Độ dày" value="{{ $item->thickness }}">
                                </td>
                                <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->height }}">
                                </td>
                                <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->width }}">
                                </td>
                                <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="1" min="1" required value="{{ $item->quantity }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200 cursor-not-allowed">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_bevel]" class="product-edge-bevel-input form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Cạnh vát" value="{{ $item->edge_bevel }}" readonly tabindex="-1" style="pointer-events: none;">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][grain_direction]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8 text-xs">
                                        <option value="0" {{ $item->grain_direction == 0 ? 'selected' : '' }}>0</option>
                                        <option value="2" {{ $item->grain_direction == 2 ? 'selected' : '' }}>2</option>
                                    </select>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][wing_area]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->wing_area }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][molding_length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->molding_length }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][bevel]" class="product-bevel-input form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Vát" value="{{ $item->bevel }}" list="bevel-list-{{ $supplyIndex }}-{{ $itemIndex }}">
                                    <datalist class="bevel-datalist" id="bevel-list-{{ $supplyIndex }}-{{ $itemIndex }}">
                                        @if($item->width)
                                            <option value="{{ $item->width }}">Rộng ({{ $item->width }})</option>
                                        @endif
                                        @if($item->height)
                                            <option value="{{ $item->height }}">Cao ({{ $item->height }})</option>
                                        @endif
                                    </datalist>
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
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][unit_price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" min="0" required value="{{ $item->unit_price ? round($item->unit_price) : '' }}">
                                </td>
                                <td style="width: 120px; min-width: 120px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][total_price]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center px-1 py-1 h-8 text-xs" placeholder="0" value="{{ $item->total_price ? round($item->total_price) : '' }}" readonly>
                                </td>
                                <td style="min-width: 160px;" class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Ghi chú" value="{{ $item->notes }}">
                                </td>
                                <td style="width: 80px; min-width: 80px; " class="text-center align-middle border border-neutral-200">
                                    <div class="flex items-center gap-1 justify-center">
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
                
                {{-- Nút thêm sản phẩm mới (Sao chép từ sản phẩm cuối) --}}
                <button type="button" onclick="addOrderItem(this)" class="w-full mt-4 py-3 border-2 border-dashed border-primary-300 hover:border-primary-500 rounded-xl bg-primary-50/50 hover:bg-primary-50 text-primary-600 font-semibold text-sm flex items-center justify-center gap-1.5 transition-all duration-200">
                    <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon>
                    Thêm sản phẩm mới (Sao chép từ sản phẩm cuối)
                </button>
            </div>
            @endforeach
            @php $supplyIndex = $acrylicOrder->supplies->count() @endphp
        @else
            @php $supplyIndex = 0 @endphp
        @endif
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
        <div class="overflow-x-auto pb-3">
            <table class="table bordered-table sm-table mb-0 min-w-[1100px] border border-neutral-200">
                <thead>
                    <tr class="bg-neutral-50 text-center">
                        <th scope="col" rowspan="2" style="width: 30px; min-width: 30px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Stt</th>
                        <th scope="col" rowspan="2" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã SP</th>
                        <th scope="col" rowspan="2" style="width: 150px; min-width: 150px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên sản phẩm <span class="text-danger-500">*</span></th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Độ dày</th>
                        <th scope="col" colspan="2" style="white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Kích thước (mm)</th>
                        <th scope="col" rowspan="2" style="width: 50px; min-width: 50px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng <span class="text-danger-500">*</span></th>
                        <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Cạnh Vát</th>
                        <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều vân</th>
                        <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Cánh (m2)</th>
                        <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Phào (m)</th>
                        <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Vát</th>
                        <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Vân dọc CNC</th>
                        <th scope="col" rowspan="2" style="width: 80px; min-width: 80px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                        <th scope="col" rowspan="2" style="width: 90px; min-width: 90px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                        <th scope="col" rowspan="2" style="min-width: 120px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                        <th scope="col" rowspan="2" style="width: 60px; min-width: 60px; white-space: nowrap; position: sticky; right: 0; z-index: 3; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase bg-neutral-50">Hành động</th>
                    </tr>
                    <tr class="bg-neutral-50 text-center">
                        <th scope="col" style="width: 130px; min-width: 130px; white-space: nowrap;" class="border border-neutral-200 bg-yellow-100/70 font-semibold text-xs text-neutral-700 uppercase">Cao (chiều vân)</th>
                        <th scope="col" style="width: 130px; min-width: 130px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
                    </tr>
                </thead>
                <tbody class="supply-items-container" data-supply-index="${supplyIndex}">
                </tbody>
            </table>
        </div>
        <button type="button" onclick="addOrderItem(this)" class="w-full mt-4 py-3 border-2 border-dashed border-primary-300 hover:border-primary-500 rounded-xl bg-primary-50/50 hover:bg-primary-50 text-primary-600 font-semibold text-sm flex items-center justify-center gap-1.5 transition-all duration-200">
            <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon>
            Thêm sản phẩm mới (Sao chép từ sản phẩm cuối)
        </button>
    `;
    container.appendChild(newSupply);

    // Add one product row by default
    const addProductBtn = newSupply.querySelector('button[onclick^="addOrderItem"]');
    if (addProductBtn) {
        addOrderItem(addProductBtn);
    }

    const selectEl = newSupply.querySelector('.tom-select-supply-code');
    if (selectEl && typeof TomSelect !== 'undefined') {
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

    supplyIndex++;
}

function addOrderItem(button) {
    const supplyRow = button.closest('.order-supply-row');
    const supplyIndex = supplyRow.querySelector('.supply-items-container').dataset.supplyIndex;
    const container = supplyRow.querySelector('.supply-items-container');
    const itemIndex = container.querySelectorAll('.order-item-row').length;
    
    // Check if there is an existing last row in the container to copy from
    const lastRow = container.querySelector('.order-item-row:last-of-type');
    let lastData = null;
    if (lastRow) {
        lastData = {
            product_name: lastRow.querySelector('input[name*="[product_name]"]') ? lastRow.querySelector('input[name*="[product_name]"]').value : '',
            thickness: lastRow.querySelector('input[name*="[thickness]"]') ? lastRow.querySelector('input[name*="[thickness]"]').value : '',
            height: lastRow.querySelector('input[name*="[height]"]') ? lastRow.querySelector('input[name*="[height]"]').value : '',
            width: lastRow.querySelector('input[name*="[width]"]') ? lastRow.querySelector('input[name*="[width]"]').value : '',
            quantity: lastRow.querySelector('input[name*="[quantity]"]') ? lastRow.querySelector('input[name*="[quantity]"]').value : '1',
            edge_bevel: lastRow.querySelector('input[name*="[edge_bevel]"]') ? lastRow.querySelector('input[name*="[edge_bevel]"]').value : '',
            grain_direction: lastRow.querySelector('select[name*="[grain_direction]"]') ? lastRow.querySelector('select[name*="[grain_direction]"]').value : '0',
            wing_area: lastRow.querySelector('input[name*="[wing_area]"]') ? lastRow.querySelector('input[name*="[wing_area]"]').value : '',
            molding_length: lastRow.querySelector('input[name*="[molding_length]"]') ? lastRow.querySelector('input[name*="[molding_length]"]').value : '',
            bevel: lastRow.querySelector('input[name*="[bevel]"]') ? lastRow.querySelector('input[name*="[bevel]"]').value : '',
            vertical_grain_cnc: lastRow.querySelector('input[name*="[vertical_grain_cnc]"]') ? lastRow.querySelector('input[name*="[vertical_grain_cnc]"]').value : '',
            unit_price: lastRow.querySelector('input[name*="[unit_price]"]') ? lastRow.querySelector('input[name*="[unit_price]"]').value : '',
            total_price: lastRow.querySelector('input[name*="[total_price]"]') ? lastRow.querySelector('input[name*="[total_price]"]').value : '',
            notes: lastRow.querySelector('input[name*="[notes]"]') ? lastRow.querySelector('input[name*="[notes]"]').value : '',
        };
    }
    
    const newItem = document.createElement('tr');
    newItem.className = 'order-item-row';
    newItem.innerHTML = `
        <td style="width: 45px; min-width: 45px; " class="text-center align-middle border border-neutral-200">
            <span class="row-index font-semibold text-neutral-500">${itemIndex + 1}</span>
        </td>
        <td style="width: 160px; min-width: 160px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][product_code]" class="product-code-input form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs font-semibold text-neutral-600" readonly>
        </td>
        <td style="min-width: 220px;" class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Tên sản phẩm" value="${lastData ? lastData.product_name : ''}">
        </td>
        <td style="width: 100px; min-width: 100px;" class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][thickness]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Độ dày" value="${lastData ? lastData.thickness : ''}">
        </td>
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.height : ''}">
        </td>
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.width : ''}">
        </td>
        <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="1" min="1" required value="${lastData ? lastData.quantity : '1'}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200 cursor-not-allowed">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][edge_bevel]" class="product-edge-bevel-input form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="Cạnh vát" value="${lastData ? lastData.edge_bevel : ''}" readonly tabindex="-1" style="pointer-events: none;">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <select name="supplies[${supplyIndex}][items][${itemIndex}][grain_direction]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8 text-xs">
                <option value="0" ${lastData && lastData.grain_direction === '0' ? 'selected' : ''}>0</option>
                <option value="2" ${lastData && lastData.grain_direction === '2' ? 'selected' : ''}>2</option>
            </select>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][wing_area]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.wing_area : ''}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][molding_length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.molding_length : ''}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][bevel]" class="product-bevel-input form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Vát" value="${lastData ? lastData.bevel : ''}" list="bevel-list-${supplyIndex}-${itemIndex}">
            <datalist class="bevel-datalist" id="bevel-list-${supplyIndex}-${itemIndex}">
                ${lastData && lastData.width ? `<option value="${lastData.width}">Rộng (${lastData.width})</option>` : ''}
                ${lastData && lastData.height ? `<option value="${lastData.height}">Cao (${lastData.height})</option>` : ''}
            </datalist>
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
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][unit_price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" min="0" required value="${lastData ? lastData.unit_price : ''}">
        </td>
        <td style="width: 120px; min-width: 120px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][total_price]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center px-1 py-1 h-8 text-xs" placeholder="0" readonly value="${lastData ? lastData.total_price : ''}">
        </td>
        <td style="min-width: 160px;" class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Ghi chú" value="${lastData ? lastData.notes : ''}">
        </td>
        <td style="width: 80px; min-width: 80px; " class="text-center align-middle border border-neutral-200">
            <div class="flex items-center gap-1 justify-center">
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
    bindAcrylicRowEvents(newRow);
    updateBevelSuggestions(newRow);
    
    updateOrderSummary();
    updateAcrylicRowIndexes();
}

function removeOrderItem(button) {
    const row = button.closest('.order-item-row');
    const tbody = row.closest('.supply-items-container');
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

            globalPieceIndex += qty;
            globalItemIndex++;
        });
    });
}

function bindAcrylicRowEvents(row) {
    const heightInput = row.querySelector('input[name*="[height]"]');
    const widthInput = row.querySelector('input[name*="[width]"]');
    const quantityInput = row.querySelector('input[name*="[quantity]"]');
    const wingAreaInput = row.querySelector('input[name*="[wing_area]"]');
    const moldingLengthInput = row.querySelector('input[name*="[molding_length]"]');
    const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
    const bevelInput = row.querySelector('input[name*="[bevel]"]');
    
    if (heightInput) {
        heightInput.addEventListener('input', () => {
            applyNarrowWidthRule(row);
            calculateTotalPrice(row, 'height');
            updateBevelSuggestions(row);
        });
    }
    if (widthInput) {
        widthInput.addEventListener('input', () => {
            applyNarrowWidthRule(row);
            calculateTotalPrice(row, 'width');
            updateBevelSuggestions(row);
        });
    }
    if (quantityInput) {
        quantityInput.addEventListener('input', () => {
            applyNarrowWidthRule(row);
            calculateTotalPrice(row, 'quantity');
            updateAcrylicRowIndexes();
        });
    }
    if (wingAreaInput) wingAreaInput.addEventListener('input', () => calculateTotalPrice(row, 'wing_area'));
    if (moldingLengthInput) moldingLengthInput.addEventListener('input', () => calculateTotalPrice(row, 'molding_length'));
    if (unitPriceInput) unitPriceInput.addEventListener('input', () => calculateTotalPrice(row, 'unit_price'));
    if (bevelInput) bevelInput.addEventListener('input', () => updateEdgeBevel(row));
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

    bindAcrylicRowEvents(newRow);
    updateBevelSuggestions(newRow);
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
        if (!isNaN(width) && width > 0 && width < 55) {
            wingAreaInput.value = 0;
        } else {
            let wingArea = 0;
            if (height > 0 && width > 0 && quantity > 0) {
                wingArea = (height * width * quantity) / 1000000;
            }
            wingAreaInput.value = wingArea > 0 ? wingArea.toFixed(2) : '';
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
function applyNarrowWidthRule(row) {
    const heightInput       = row.querySelector('input[name*="[height]"]');
    const widthInput        = row.querySelector('input[name*="[width]"]');
    const quantityInput     = row.querySelector('input[name*="[quantity]"]');
    const wingAreaInput     = row.querySelector('input[name*="[wing_area]"]');
    const moldingLengthInput= row.querySelector('input[name*="[molding_length]"]');
    const unitPriceInput    = row.querySelector('input[name*="[unit_price]"]');

    if (!widthInput) return;

    const width    = parseFloat(widthInput.value);
    const height   = parseFloat(heightInput ? heightInput.value : 0) || 0;
    const quantity = parseFloat(quantityInput ? quantityInput.value : 1) || 1;

    const lastWidth = parseFloat(widthInput.dataset.lastWidth);
    const isPreviousNarrow = !isNaN(lastWidth) && lastWidth > 0 && lastWidth < 55;
    const isCurrentNarrow = !isNaN(width) && width > 0 && width < 55;

    if (isCurrentNarrow) {
        // Red border warning
        widthInput.style.borderColor = '#ef4444';
        widthInput.style.boxShadow   = '0 0 0 1px #ef4444';

        // Phào (m) = (cao × SL) / 1000
        if (moldingLengthInput) {
            const phao = height > 0 ? ((height * quantity) / 1000) : 0;
            moldingLengthInput.value = phao > 0 ? phao.toFixed(3) : '';
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
        widthInput.style.borderColor = '';
        widthInput.style.boxShadow   = '';

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

function updateBevelSuggestions(row) {
    const heightInput = row.querySelector('input[name*="[height]"]');
    const widthInput = row.querySelector('input[name*="[width]"]');
    const bevelInput = row.querySelector('input[name*="[bevel]"]');
    const datalist = row.querySelector('.bevel-datalist');
    
    if (!heightInput || !widthInput || !bevelInput || !datalist) return;
    
    const height = heightInput.value.trim();
    const width = widthInput.value.trim();
    
    const lastWidth = bevelInput.dataset.lastWidth || '';
    const lastHeight = bevelInput.dataset.lastHeight || '';
    const currentBevel = bevelInput.value.trim();
    
    // Update datalist options dynamically
    let optionsHtml = '';
    if (width) {
        optionsHtml += `<option value="${width}">Rộng (${width})</option>`;
    }
    if (height) {
        optionsHtml += `<option value="${height}">Cao (${height})</option>`;
    }
    datalist.innerHTML = optionsHtml;
    
    // Auto-update logic based on defaults/manual inputs
    if (currentBevel === '') {
        if (width) {
            bevelInput.value = width;
        } else if (height) {
            bevelInput.value = height;
        }
    } else if (currentBevel === lastWidth || lastWidth === '') {
        if (width) {
            bevelInput.value = width;
        } else if (height) {
            bevelInput.value = height;
        }
    } else if (currentBevel === lastHeight) {
        if (height) {
            bevelInput.value = height;
        } else if (width) {
            bevelInput.value = width;
        }
    }
    
    updateEdgeBevel(row);
    
    bevelInput.dataset.lastWidth = width;
    bevelInput.dataset.lastHeight = height;
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
        updateBevelSuggestions(row);
        updateEdgeBevel(row);
    });

    if (typeof TomSelect !== 'undefined') {
        document.querySelectorAll('.tom-select-supply-code').forEach(function(element) {
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
