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
                    <table class="table bordered-table sm-table mb-0 min-w-[1700px] border border-neutral-200">
                        <thead>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" rowspan="2" style="width: 45px; min-width: 45px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Stt</th>
                                <th scope="col" rowspan="2" style="width: 160px; min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã SP</th>
                                <th scope="col" rowspan="2" style="width: 220px; min-width: 220px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên sản phẩm <span class="text-danger-500">*</span></th>
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
                                <th scope="col" rowspan="2" style="width: 160px; min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                                <th scope="col" rowspan="2" style="width: 45px; min-width: 45px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Xóa</th>
                            </tr>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="border border-neutral-200 bg-yellow-100/70 font-semibold text-xs text-neutral-700 uppercase">Cao (chiều vân)</th>
                                <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
                            </tr>
                        </thead>
                        <tbody class="supply-items-container" data-supply-index="{{ $supplyIndex }}">
                            @foreach($supply->items as $itemIndex => $item)
                            <tr class="order-item-row" data-item-id="{{ $item->id }}">
                                <td style="width: 45px; min-width: 45px; " class="text-center align-middle border border-neutral-200">
                                    <span class="row-index font-semibold text-neutral-500">{{ $itemIndex + 1 }}</span>
                                </td>
                                <td style="width: 160px; min-width: 160px; " class="border border-neutral-200">
                                    <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_code]" class="tom-select-product" onchange="fillProductInfo(this, {{ $supplyIndex }}, {{ $itemIndex }})">
                                        <option value="">-- Chọn --</option>
                                        @foreach(\App\Models\Supply::pluck('product_code')->filter()->unique() as $code)
                                        <option value="{{ $code }}" {{ $item->product_code == $code ? 'selected' : '' }}>{{ $code }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="min-width: 220px;" class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Tên sản phẩm" required value="{{ $item->product_name }}">
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
                                <td style="width: 45px; min-width: 45px; " class="text-center align-middle border border-neutral-200">
                                    <button type="button" onclick="removeOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
                                        <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                                    </button>
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
                        <th scope="col" rowspan="2" style="width: 45px; min-width: 45px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Xóa</th>
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
    supplyIndex++;
}

function addOrderItem(button) {
    const supplyRow = button.closest('.order-supply-row');
    const supplyIndex = supplyRow.querySelector('.supply-items-container').dataset.supplyIndex;
    const container = supplyRow.querySelector('.supply-items-container');
    const itemIndex = container.querySelectorAll('.order-item-row').length;
    
    const newItem = document.createElement('tr');
    newItem.className = 'order-item-row';
    newItem.innerHTML = `
        <td style="width: 45px; min-width: 45px; " class="text-center align-middle border border-neutral-200">
            <span class="row-index font-semibold text-neutral-500">${itemIndex + 1}</span>
        </td>
        <td style="width: 160px; min-width: 160px; " class="border border-neutral-200">
            <select name="supplies[${supplyIndex}][items][${itemIndex}][product_code]" class="tom-select-product" onchange="fillProductInfo(this, ${supplyIndex}, ${itemIndex})">
                <option value="">-- Chọn --</option>
                @foreach(\App\Models\Supply::pluck('product_code')->filter()->unique() as $code)
                <option value="{{ $code }}">{{ $code }}</option>
                @endforeach
            </select>
        </td>
        <td style="min-width: 220px;" class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Tên sản phẩm" required>
        </td>
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01">
        </td>
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01">
        </td>
        <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="1" min="1" required value="1">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][edge_bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Cạnh vát">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <select name="supplies[${supplyIndex}][items][${itemIndex}][grain_direction]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8 text-xs">
                <option value="0">0</option>
                <option value="2">2</option>
            </select>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][wing_area]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][molding_length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Vát">
        </td>
        <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][vertical_grain_cnc]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Vân dọc CNC">
        </td>
        <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][unit_price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" min="0" required>
        </td>
        <td style="width: 120px; min-width: 120px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][total_price]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center px-1 py-1 h-8 text-xs" placeholder="0" readonly>
        </td>
        <td style="min-width: 160px;" class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Ghi chú">
        </td>
        <td style="width: 45px; min-width: 45px; " class="text-center align-middle border border-neutral-200">
            <button type="button" onclick="removeOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
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
    const quantityInput = newRow.querySelector('input[name*="[quantity]"]');
    const wingAreaInput = newRow.querySelector('input[name*="[wing_area]"]');
    const moldingLengthInput = newRow.querySelector('input[name*="[molding_length]"]');
    const unitPriceInput = newRow.querySelector('input[name*="[unit_price]"]');
    const bevelInput = newRow.querySelector('input[name*="[bevel]"]');
    
    if (heightInput) heightInput.addEventListener('input', () => calculateTotalPrice(newRow, 'height'));
    if (widthInput) widthInput.addEventListener('input', () => calculateTotalPrice(newRow, 'width'));
    if (quantityInput) quantityInput.addEventListener('input', () => calculateTotalPrice(newRow, 'quantity'));
    if (wingAreaInput) wingAreaInput.addEventListener('input', () => calculateTotalPrice(newRow, 'wing_area'));
    if (moldingLengthInput) moldingLengthInput.addEventListener('input', () => calculateTotalPrice(newRow, 'molding_length'));
    if (unitPriceInput) unitPriceInput.addEventListener('input', () => calculateTotalPrice(newRow, 'unit_price'));
    if (bevelInput) bevelInput.addEventListener('input', () => updateEdgeBevel(newRow));
    
    updateOrderSummary();
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
    tbody.querySelectorAll('.order-item-row').forEach((row, index) => {
        const indexEl = row.querySelector('.row-index');
        if (indexEl) indexEl.textContent = index + 1;
    });
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

function fillProductInfo(selectElement, supplyIndex, itemIndex) {
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
            unitPriceInput.value = supply.unit_price ? Math.round(supply.unit_price) : 0;
            calculateTotalPrice(row);
        }
    }
    @endif
    updateOrderSummary();
}

// Initial attachment setup for Acrylic-specific rows if DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('#order-supplies-container .order-item-row').forEach(row => {
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
        
        calculateTotalPrice(row);
        updateEdgeBevel(row);
    });
});
</script>
