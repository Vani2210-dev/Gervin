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
</style>
<div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm">
    <div class="flex items-center justify-between border-b border-neutral-100 pb-4 mb-5">
        <div class="flex items-center gap-2">
            <iconify-icon icon="lucide:package-open" class="text-xl text-primary-500"></iconify-icon>
            <h6 class="font-bold text-base text-neutral-800 m-0">Danh sách Vật tư & Sản phẩm (Acrylic)</h6>
        </div>
        <button type="button" onclick="addOrderSupply()" class="btn btn-sm btn-primary rounded-lg flex items-center gap-1">
            <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon> Thêm vật tư
        </button>
    </div>
    <div id="order-supplies-container" class="space-y-6">
        @if(isset($acrylicOrder) && in_array($acrylicOrder->type, ['acrylic', 'glass']) && $acrylicOrder->supplies->count() > 0)
            @foreach($acrylicOrder->supplies as $supplyIndex => $supply)
            <div class="order-supply-row bg-neutral-50/50 border border-neutral-200 rounded-xl p-5 mb-2 relative transition-all hover:border-neutral-300 shadow-sm" data-supply-id="{{ $supply->id }}">
                
                {{-- Items inside this supply --}}
                <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                            <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                        </div>
                        <input type="text" name="supplies[{{ $supplyIndex }}][order_supply_code]" class="order-supply-code-input form-control form-control-sm rounded-lg w-40 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Mã vật tư" value="{{ $supply->order_supply_code ?? '' }}">
                        <input type="text" name="supplies[{{ $supplyIndex }}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)" value="{{ $supply->supply_name }}">
                        <input type="number" name="supplies[{{ $supplyIndex }}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="0.01" value="{{ $supply->quantity ?? 0 }}">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="addOrderItem(this)" class="btn btn-sm btn-outline-primary rounded-lg flex items-center gap-1">
                            <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon> Thêm sản phẩm
                        </button>
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
                                <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->height }}">
                                </td>
                                <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->width }}">
                                </td>
                                <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="1" min="1" required value="{{ $item->quantity }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Cạnh vát" value="{{ $item->edge_bevel }}">
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
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Vát" value="{{ $item->bevel }}">
                                </td>
                                <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][vertical_grain_cnc]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Vân dọc CNC" value="{{ $item->vertical_grain_cnc }}">
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
            </div>
            @endforeach
            @php $supplyIndex = $acrylicOrder->supplies->count() @endphp
        @else
            @php $supplyIndex = 0 @endphp
        @endif
    </div>
</div>

<script>
let supplyIndex = {{ $supplyIndex ?? 0 }};

function addOrderSupply() {
    const container = document.getElementById('order-supplies-container');
    const newSupply = document.createElement('div');
    newSupply.className = 'order-supply-row bg-neutral-50/50 border border-neutral-200 rounded-xl p-5 mb-2 relative transition-all hover:border-neutral-300 shadow-sm';
    newSupply.innerHTML = `
        <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                    <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                </div>
                <input type="text" name="supplies[${supplyIndex}][order_supply_code]" class="order-supply-code-input form-control form-control-sm rounded-lg w-40 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Mã vật tư">
                <input type="text" name="supplies[${supplyIndex}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)">
                <input type="number" name="supplies[${supplyIndex}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="0.01" value="0">
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="addOrderItem(this)" class="btn btn-sm btn-outline-primary rounded-lg flex items-center gap-1">
                    <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon> Thêm sản phẩm
                </button>
                <button type="button" onclick="this.closest('.order-supply-row').remove()" class="text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                    <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto pb-3">
            <table class="table bordered-table sm-table mb-0 min-w-[1700px] border border-neutral-200">
                <thead>
                    <tr class="bg-neutral-50 text-center">
                        <th scope="col" rowspan="2" style="width: 45px; min-width: 45px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Stt</th>
                        <th scope="col" rowspan="2" style="width: 160px; min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã SP</th>
                        <th scope="col" rowspan="2" style="min-width: 220px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên sản phẩm <span class="text-danger-500">*</span></th>
                        <th scope="col" colspan="2" style="white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Kích thước (mm)</th>
                        <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng <span class="text-danger-500">*</span></th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Cạnh Vát</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều vân</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Cánh (m2)</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Phào (m)</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Vát</th>
                        <th scope="col" rowspan="2" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Vân dọc CNC</th>
                        <th scope="col" rowspan="2" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                        <th scope="col" rowspan="2" style="width: 120px; min-width: 120px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                        <th scope="col" rowspan="2" style="min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                        <th scope="col" rowspan="2" style="width: 80px; min-width: 80px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Hành động</th>
                    </tr>
                    <tr class="bg-neutral-50 text-center">
                        <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="border border-neutral-200 bg-yellow-100/70 font-semibold text-xs text-neutral-700 uppercase">Cao (chiều vân)</th>
                        <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
                    </tr>
                </thead>
                <tbody class="supply-items-container" data-supply-index="${supplyIndex}">
                </tbody>
            </table>
        </div>
    `;
    container.appendChild(newSupply);

    // Add one product row by default
    const addProductBtn = newSupply.querySelector('button[onclick^="addOrderItem"]');
    if (addProductBtn) {
        addOrderItem(addProductBtn);
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
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.height : ''}">
        </td>
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.width : ''}">
        </td>
        <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="1" min="1" required value="${lastData ? lastData.quantity : '1'}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][edge_bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Cạnh vát" value="${lastData ? lastData.edge_bevel : ''}">
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
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Vát" value="${lastData ? lastData.bevel : ''}">
        </td>
        <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][vertical_grain_cnc]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Vân dọc CNC" value="${lastData ? lastData.vertical_grain_cnc : ''}">
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
    
    updateOrderSummary();
    updateAcrylicRowIndexes(container);
}

function removeOrderItem(button) {
    const row = button.closest('.order-item-row');
    const tbody = row.closest('.supply-items-container');
    row.remove();
    updateOrderSummary();
    updateAcrylicRowIndexes(tbody);
}

function updateAcrylicRowIndexes(tbody) {
    if (!tbody) return;
    const orderCode = document.getElementById('order-code-input')?.value || '';
    const supplyRow = tbody.closest('.order-supply-row');
    const supplyCode = supplyRow ? (supplyRow.querySelector('.order-supply-code-input')?.value || '') : '';

    tbody.querySelectorAll('.order-item-row').forEach((row, itemIndex) => {
        const indexEl = row.querySelector('.row-index');
        if (indexEl) indexEl.textContent = itemIndex + 1;
        
        const stt = itemIndex + 1;
        const generatedCode = `${orderCode}.${supplyCode}.${stt}`;
        const productCodeInput = row.querySelector('.product-code-input');
        if (productCodeInput) {
            productCodeInput.value = generatedCode;
        }

        row.querySelectorAll('input, select, textarea').forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                const newName = name.replace(/\[items\]\[\d+\]/, `[items][${itemIndex}]`);
                input.setAttribute('name', newName);
            }
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
    
    if (heightInput) heightInput.addEventListener('input', () => calculateTotalPrice(row, 'height'));
    if (widthInput) widthInput.addEventListener('input', () => calculateTotalPrice(row, 'width'));
    if (quantityInput) quantityInput.addEventListener('input', () => calculateTotalPrice(row, 'quantity'));
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
    updateAcrylicRowIndexes(tbody);
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
    const unitPrice = parseFloat(unitPriceInput ? unitPriceInput.value : 0) || 0;
    
    if (sourceEvent !== 'wing_area') {
        let wingArea = 0;
        if (height > 0 && width > 0 && quantity > 0) {
            wingArea = (height * width * quantity) / 1000000;
        }
        wingAreaInput.value = wingArea > 0 ? wingArea.toFixed(2) : '';
    }
    
    const currentWingArea = parseFloat(wingAreaInput.value) || 0;
    const moldingLength = parseFloat(moldingLengthInput.value) || 0;
    
    const totalPrice = Math.round((currentWingArea + moldingLength) * unitPrice);
    const totalPriceInput = row.querySelector('input[name*="[total_price]"]');
    if (totalPriceInput) {
        totalPriceInput.value = totalPrice > 0 ? totalPrice : 0;
    }
    updateOrderSummary();
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

// Initial attachment setup for Acrylic-specific rows if DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('#order-supplies-container .order-item-row').forEach(row => {
        bindAcrylicRowEvents(row);
        calculateTotalPrice(row);
        updateEdgeBevel(row);
    });

    // Recalculate codes on load
    document.querySelectorAll('#order-supplies-container .supply-items-container').forEach(tbody => {
        updateAcrylicRowIndexes(tbody);
    });
});

// Live listener for supply code input changes
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('order-supply-code-input')) {
        const supplyRow = e.target.closest('.order-supply-row');
        if (supplyRow) {
            const tbody = supplyRow.querySelector('.supply-items-container');
            if (tbody) {
                updateAcrylicRowIndexes(tbody);
            }
        }
    }
});
</script>
