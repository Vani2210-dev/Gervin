{{-- Order Supplies & Items Section Card (Glass) --}}
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
    /* Style TomSelect inside table rows to match compact inputs */
    .table .ts-wrapper {
        padding: 0 !important;
        border: none !important;
        background: transparent !important;
        min-height: auto !important;
        height: 32px !important;
    }
    .table .ts-control {
        padding: 0 8px !important;
        height: 32px !important; /* matches h-8 (32px) */
        font-size: 12px !important; /* matches text-xs */
        line-height: 30px !important; /* 32px minus borders */
        border-radius: 8px !important; /* matches rounded-lg */
        border: 1px solid #d1d5db !important; /* matches border-neutral-300 */
        background-color: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        box-shadow: none !important;
    }
    .table .ts-control input {
        font-size: 12px !important;
        height: auto !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .table .ts-control .item {
        font-size: 12px !important;
        line-height: 30px !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .table .ts-wrapper.single .ts-control:after {
        top: 50% !important;
        margin-top: -3px !important;
    }
    .table .ts-wrapper.focus .ts-control {
        border-color: #3b82f6 !important; /* focus border color (primary-500) */
        box-shadow: 0 0 0 1px #3b82f6 !important;
    }
</style>
<div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm">
    <div class="flex items-center justify-between border-b border-neutral-100 pb-4 mb-5">
        <div class="flex items-center gap-2">
            <iconify-icon icon="lucide:package-open" class="text-xl text-primary-500"></iconify-icon>
            <h6 class="font-bold text-base text-neutral-800 m-0">Danh sách Vật tư & Sản phẩm (Kính)</h6>
        </div>
        <button type="button" onclick="addGlassOrderSupply()" class="btn btn-sm btn-primary rounded-lg flex items-center gap-1">
            <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon> Thêm vật tư
        </button>
    </div>
    <div id="glass-supplies-container" class="space-y-6">
        @if(isset($acrylicOrder) && $acrylicOrder->type == 'glass' && $acrylicOrder->supplies->count() > 0)
            @foreach($acrylicOrder->supplies as $supplyIndex => $supply)
            <div class="order-supply-row bg-neutral-50/50 border border-neutral-200 rounded-xl p-5 mb-2 relative transition-all hover:border-neutral-300 shadow-sm" data-supply-id="{{ $supply->id }}">
                
                {{-- Items inside this supply --}}
                <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                            <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                        </div>
                        <input type="text" name="supplies[{{ $supplyIndex }}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)" value="{{ $supply->supply_name }}">
                        <input type="number" name="supplies[{{ $supplyIndex }}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="0.01" value="{{ $supply->quantity ?? 0 }}">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="addGlassOrderItem(this)" class="btn btn-sm btn-outline-primary rounded-lg flex items-center gap-1">
                            <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon> Thêm sản phẩm
                        </button>
                        <button type="button" onclick="this.closest('.order-supply-row').remove()" class="text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                            <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto pb-3">
                    <table class="table bordered-table sm-table mb-0 min-w-[1800px] border border-neutral-200">
                        <thead>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" rowspan="2" class="align-middle w-[45px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Stt</th>
                                <th scope="col" rowspan="2" class="align-middle w-[160px] min-w-[160px] max-w-[160px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã SP</th>
                                <th scope="col" rowspan="2" class="align-middle w-[220px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên sản phẩm <span class="text-danger-500">*</span></th>
                                <th scope="col" rowspan="2" class="align-middle w-[110px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều mở cánh</th>
                                <th scope="col" rowspan="2" class="align-middle w-[100px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Màu nhôm</th>
                                <th scope="col" rowspan="2" class="align-middle w-[100px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Màu kính</th>
                                <th scope="col" colspan="2" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Kích thước cánh (mm)</th>
                                <th scope="col" rowspan="2" class="align-middle w-[80px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn vị</th>
                                <th scope="col" rowspan="2" class="align-middle w-[70px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng cánh <span class="text-danger-500">*</span></th>
                                <th scope="col" rowspan="2" class="align-middle w-[90px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Khối lượng (m2)</th>
                                <th scope="col" rowspan="2" class="align-middle w-[110px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                                <th scope="col" rowspan="2" class="align-middle w-[120px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                                <th scope="col" rowspan="2" class="align-middle w-[160px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                                <th scope="col" rowspan="2" class="align-middle w-[45px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Xóa</th>
                            </tr>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" class="w-[80px] border border-neutral-200 bg-yellow-100/70 font-semibold text-xs text-neutral-700 uppercase">Dài (mm)</th>
                                <th scope="col" class="w-[80px] border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng (mm)</th>
                            </tr>
                        </thead>
                        <tbody class="supply-items-container" data-supply-index="{{ $supplyIndex }}">
                            @foreach($supply->glassItems as $itemIndex => $item)
                            <tr class="order-item-row" data-item-id="{{ $item->id }}">
                                <td class="text-center align-middle border border-neutral-200">
                                    <span class="row-index font-semibold text-neutral-500">{{ $itemIndex + 1 }}</span>
                                </td>
                                <td class="border border-neutral-200 w-[160px] min-w-[160px] max-w-[160px]">
                                    <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_code]" class="tom-select-product" onchange="fillGlassProductInfo(this, {{ $supplyIndex }}, {{ $itemIndex }})">
                                        <option value="">-- Chọn --</option>
                                        @foreach(\App\Models\Supply::pluck('product_code')->filter()->unique() as $code)
                                        <option value="{{ $code }}" {{ $item->product_code == $code ? 'selected' : '' }}>{{ $code }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Tên sản phẩm" required value="{{ $item->product_name }}">
                                </td>
                                <td class="border border-neutral-200 w-[110px] min-w-[110px] max-w-[110px]">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][wing_opening_direction]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Chiều mở cánh" value="{{ $item->wing_opening_direction }}">
                                </td>
                                <td class="border border-neutral-200 w-[100px] min-w-[100px] max-w-[100px]">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][aluminum_color]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Màu nhôm" value="{{ $item->aluminum_color }}">
                                </td>
                                <td class="border border-neutral-200 w-[100px] min-w-[100px] max-w-[100px]">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][glass_color]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Màu kính" value="{{ $item->glass_color }}">
                                </td>
                                <td class="border border-neutral-200 w-[85px] min-w-[85px] max-w-[85px]">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->height }}">
                                </td>
                                <td class="border border-neutral-200 w-[80px] min-w-[80px] max-w-[80px]">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->width }}">
                                </td>
                                <td class="border border-neutral-200 w-[80px] min-w-[80px] max-w-[80px]">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][unit]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs px-1" placeholder="Bộ" value="{{ $item->unit ?? 'Bộ' }}">
                                </td>
                                <td class="border border-neutral-200 w-[70px] min-w-[70px] max-w-[70px]">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][wing_quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="1" min="1" required value="{{ $item->wing_quantity }}">
                                </td>
                                <td class="border border-neutral-200 w-[90px] min-w-[90px] max-w-[90px]">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][area_m2]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->area_m2 }}">
                                </td>
                                <td class="border border-neutral-200 w-[110px] min-w-[110px] max-w-[110px]">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][unit_price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" min="0" required value="{{ $item->unit_price }}">
                                </td>
                                <td class="border border-neutral-200 w-[120px] min-w-[120px] max-w-[120px]">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][total_price]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->total_price }}" readonly>
                                </td>
                                <td class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Ghi chú" value="{{ $item->notes }}">
                                </td>
                                <td class="text-center align-middle border border-neutral-200">
                                    <button type="button" onclick="removeGlassOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
                                        <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                                    </button>
                                </td>
                                <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][id]" value="{{ $item->id }}">
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
            @php $glassSupplyIndex = $acrylicOrder->supplies->count() @endphp
        @else
            @php $glassSupplyIndex = 0 @endphp
        @endif
    </div>
</div>

<script>
let glassSupplyIndex = {{ $glassSupplyIndex ?? 0 }};

function addGlassOrderSupply() {
    const container = document.getElementById('glass-supplies-container');
    const newSupply = document.createElement('div');
    newSupply.className = 'order-supply-row bg-neutral-50/50 border border-neutral-200 rounded-xl p-5 mb-2 relative transition-all hover:border-neutral-300 shadow-sm';
    newSupply.innerHTML = `
        <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                    <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                </div>
                <input type="text" name="supplies[${glassSupplyIndex}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)">
                <input type="number" name="supplies[${glassSupplyIndex}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="0.01" value="0">
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="addGlassOrderItem(this)" class="btn btn-sm btn-outline-primary rounded-lg flex items-center gap-1">
                    <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon> Thêm sản phẩm
                </button>
                <button type="button" onclick="this.closest('.order-supply-row').remove()" class="text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                    <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto pb-3">
            <table class="table bordered-table sm-table mb-0 min-w-[1800px] border border-neutral-200">
                <thead>
                    <tr class="bg-neutral-50 text-center">
                        <th scope="col" rowspan="2" class="align-middle w-[45px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Stt</th>
                        <th scope="col" rowspan="2" class="align-middle w-[160px] min-w-[160px] max-w-[160px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã SP</th>
                        <th scope="col" rowspan="2" class="align-middle w-[220px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên sản phẩm <span class="text-danger-500">*</span></th>
                        <th scope="col" rowspan="2" class="align-middle w-[110px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều mở cánh</th>
                        <th scope="col" rowspan="2" class="align-middle w-[100px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Màu nhôm</th>
                        <th scope="col" rowspan="2" class="align-middle w-[100px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Màu kính</th>
                        <th scope="col" colspan="2" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Kích thước cánh (mm)</th>
                        <th scope="col" rowspan="2" class="align-middle w-[80px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn vị</th>
                        <th scope="col" rowspan="2" class="align-middle w-[70px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng cánh <span class="text-danger-500">*</span></th>
                        <th scope="col" rowspan="2" class="align-middle w-[90px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Khối lượng (m2)</th>
                        <th scope="col" rowspan="2" class="align-middle w-[110px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                        <th scope="col" rowspan="2" class="align-middle w-[120px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                        <th scope="col" rowspan="2" class="align-middle w-[160px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                        <th scope="col" rowspan="2" class="align-middle w-[45px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Xóa</th>
                    </tr>
                    <tr class="bg-neutral-50 text-center">
                        <th scope="col" class="w-[80px] border border-neutral-200 bg-yellow-100/70 font-semibold text-xs text-neutral-700 uppercase">Dài (mm)</th>
                        <th scope="col" class="w-[80px] border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng (mm)</th>
                    </tr>
                </thead>
                <tbody class="supply-items-container" data-supply-index="${glassSupplyIndex}">
                </tbody>
            </table>
        </div>
    `;
    container.appendChild(newSupply);
    glassSupplyIndex++;
}

function addGlassOrderItem(button) {
    const supplyRow = button.closest('.order-supply-row');
    const supplyIndex = supplyRow.querySelector('.supply-items-container').dataset.supplyIndex;
    const container = supplyRow.querySelector('.supply-items-container');
    const itemIndex = container.querySelectorAll('.order-item-row').length;
    
    const newItem = document.createElement('tr');
    newItem.className = 'order-item-row';
    newItem.innerHTML = `
        <td class="text-center align-middle border border-neutral-200">
            <span class="row-index font-semibold text-neutral-500">${itemIndex + 1}</span>
        </td>
        <td class="border border-neutral-200 w-[160px] min-w-[160px] max-w-[160px]">
            <select name="supplies[${supplyIndex}][items][${itemIndex}][product_code]" class="tom-select-product" onchange="fillGlassProductInfo(this, ${supplyIndex}, ${itemIndex})">
                <option value="">-- Chọn --</option>
                @foreach(\App\Models\Supply::pluck('product_code')->filter()->unique() as $code)
                <option value="{{ $code }}">{{ $code }}</option>
                @endforeach
            </select>
        </td>
        <td class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Tên sản phẩm" required>
        </td>
        <td class="border border-neutral-200 w-[110px] min-w-[110px] max-w-[110px]">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][wing_opening_direction]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Chiều mở cánh">
        </td>
        <td class="border border-neutral-200 w-[100px] min-w-[100px] max-w-[100px]">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][aluminum_color]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Màu nhôm">
        </td>
        <td class="border border-neutral-200 w-[100px] min-w-[100px] max-w-[100px]">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][glass_color]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Màu kính">
        </td>
        <td class="border border-neutral-200 w-[85px] min-w-[85px] max-w-[85px]">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01">
        </td>
        <td class="border border-neutral-200 w-[80px] min-w-[80px] max-w-[80px]">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01">
        </td>
        <td class="border border-neutral-200 w-[80px] min-w-[80px] max-w-[80px]">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][unit]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs px-1" placeholder="Bộ" value="Bộ">
        </td>
        <td class="border border-neutral-200 w-[70px] min-w-[70px] max-w-[70px]">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][wing_quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="1" min="1" required value="1">
        </td>
        <td class="border border-neutral-200 w-[90px] min-w-[90px] max-w-[90px]">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][area_m2]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01">
        </td>
        <td class="border border-neutral-200 w-[110px] min-w-[110px] max-w-[110px]">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][unit_price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" min="0" required>
        </td>
        <td class="border border-neutral-200 w-[120px] min-w-[120px] max-w-[120px]">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][total_price]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" readonly>
        </td>
        <td class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Ghi chú">
        </td>
        <td class="text-center align-middle border border-neutral-200">
            <button type="button" onclick="removeGlassOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
                <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
            </button>
        </td>
    `;
    container.appendChild(newItem);
    
    // Initialize tom-select for new product code select
    const newRow = container.lastElementChild;
    const newProductSelect = newRow.querySelector('.tom-select-product');
    if (newProductSelect && typeof TomSelect !== 'undefined') {
        new TomSelect(newProductSelect, {
            allowEmptyOption: true,
            placeholder: '-- Chọn --',
        });
    }
    
    // Attach event listeners to new inputs
    const heightInput = newRow.querySelector('input[name*="[height]"]');
    const widthInput = newRow.querySelector('input[name*="[width]"]');
    const wingQtyInput = newRow.querySelector('input[name*="[wing_quantity]"]');
    const areaInput = newRow.querySelector('input[name*="[area_m2]"]');
    const unitPriceInput = newRow.querySelector('input[name*="[unit_price]"]');
    
    if (heightInput) heightInput.addEventListener('input', () => calculateGlassTotalPrice(newRow, 'height'));
    if (widthInput) widthInput.addEventListener('input', () => calculateGlassTotalPrice(newRow, 'width'));
    if (wingQtyInput) wingQtyInput.addEventListener('input', () => calculateGlassTotalPrice(newRow, 'wing_quantity'));
    if (areaInput) areaInput.addEventListener('input', () => calculateGlassTotalPrice(newRow, 'area'));
    if (unitPriceInput) unitPriceInput.addEventListener('input', () => calculateGlassTotalPrice(newRow, 'unit_price'));
    
    updateOrderSummary();
}

function removeGlassOrderItem(button) {
    const row = button.closest('.order-item-row');
    const tbody = row.closest('.supply-items-container');
    row.remove();
    updateOrderSummary();
    updateGlassRowIndexes(tbody);
}

function updateGlassRowIndexes(tbody) {
    if (!tbody) return;
    tbody.querySelectorAll('.order-item-row').forEach((row, index) => {
        const indexEl = row.querySelector('.row-index');
        if (indexEl) indexEl.textContent = index + 1;
    });
}

function calculateGlassTotalPrice(row, sourceEvent) {
    const heightInput = row.querySelector('input[name*="[height]"]');
    const widthInput = row.querySelector('input[name*="[width]"]');
    const wingQtyInput = row.querySelector('input[name*="[wing_quantity]"]');
    const areaInput = row.querySelector('input[name*="[area_m2]"]');
    const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
    
    if (!heightInput || !widthInput || !wingQtyInput || !areaInput) return;
    
    const height = parseFloat(heightInput.value) || 0;
    const width = parseFloat(widthInput.value) || 0;
    const wingQuantity = parseFloat(wingQtyInput.value) || 0;
    const unitPrice = parseFloat(unitPriceInput ? unitPriceInput.value : 0) || 0;
    
    if (sourceEvent !== 'area') {
        let area = 0;
        if (height > 0 && width > 0 && wingQuantity > 0) {
            area = (height * width * wingQuantity) / 1000000;
        }
        areaInput.value = area > 0 ? area.toFixed(2) : '';
    }
    
    const currentArea = parseFloat(areaInput.value) || 0;
    const totalPrice = currentArea * unitPrice;
    
    const totalPriceInput = row.querySelector('input[name*="[total_price]"]');
    if (totalPriceInput) {
        totalPriceInput.value = totalPrice.toFixed(2);
    }
    updateOrderSummary();
}

function fillGlassProductInfo(selectElement, supplyIndex, itemIndex) {
    const productCode = selectElement.value;
    const row = selectElement.closest('.order-item-row');
    
    @if(auth()->check())
    const supplies = @json(\App\Models\Supply::all());
    const supply = supplies.find(s => s.product_code === productCode);
    if (supply) {
        const productNameInput = row.querySelector(`input[name="supplies[${supplyIndex}][items][${itemIndex}][product_name]"]`);
        if (productNameInput) productNameInput.value = supply.name || '';
        
        const unitPriceInput = row.querySelector(`input[name="supplies[${supplyIndex}][items][${itemIndex}][unit_price]"]`);
        if (unitPriceInput) {
            unitPriceInput.value = supply.unit_price || 0;
            calculateGlassTotalPrice(row);
        }
    }
    @endif
    updateOrderSummary();
}

// Initial setup for Glass-specific rows if DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('#glass-supplies-container .order-item-row').forEach(row => {
        const heightInput = row.querySelector('input[name*="[height]"]');
        const widthInput = row.querySelector('input[name*="[width]"]');
        const wingQtyInput = row.querySelector('input[name*="[wing_quantity]"]');
        const areaInput = row.querySelector('input[name*="[area_m2]"]');
        const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
        
        if (heightInput) heightInput.addEventListener('input', () => calculateGlassTotalPrice(row, 'height'));
        if (widthInput) widthInput.addEventListener('input', () => calculateGlassTotalPrice(row, 'width'));
        if (wingQtyInput) wingQtyInput.addEventListener('input', () => calculateGlassTotalPrice(row, 'wing_quantity'));
        if (areaInput) areaInput.addEventListener('input', () => calculateGlassTotalPrice(row, 'area'));
        if (unitPriceInput) unitPriceInput.addEventListener('input', () => calculateGlassTotalPrice(row, 'unit_price'));
        
        calculateGlassTotalPrice(row);
    });
});
</script>
