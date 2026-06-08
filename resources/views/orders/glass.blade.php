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
    /* === COMPACT TABLE: 75% font scale === */
    #glass-supplies-container .order-supply-row table {
        font-size: 75% !important;
    }
    #glass-supplies-container .order-supply-row table input,
    #glass-supplies-container .order-supply-row table select,
    #glass-supplies-container .order-supply-row table textarea {
        font-size: 75% !important;
        height: 24px !important;
        min-height: 24px !important;
        padding-top: 2px !important;
        padding-bottom: 2px !important;
        line-height: 1.2 !important;
    }
    #glass-supplies-container .order-supply-row table th,
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
</style>
<div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm">
    <div class="flex items-center justify-between border-b border-neutral-100 pb-4 mb-5">
        <div class="flex items-center gap-2">
            <iconify-icon icon="lucide:package-open" class="text-xl text-primary-500"></iconify-icon>
            <h6 class="font-bold text-base text-neutral-800 m-0">Danh sách Vật tư & Sản phẩm (Kính)</h6>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="addGlassOrderSupply()" class="btn btn-sm btn-primary rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon> Thêm vật tư
            </button>
        </div>
    </div>
    <div id="glass-supplies-container" class="space-y-6">
        @if(isset($acrylicOrder) && $acrylicOrder->type == 'glass' && $acrylicOrder->supplies->count() > 0)
            @foreach($acrylicOrder->supplies as $supplyIndex => $supply)
            <div class="order-supply-row bg-neutral-50/50 border border-primary-600 rounded-xl p-5 mb-2 relative shadow-sm" data-supply-id="{{ $supply->id }}">
                
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
                        <button type="button" onclick="this.closest('.order-supply-row').remove()" class="text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                            <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto pb-3">
                    <table class="table bordered-table sm-table mb-0 min-w-[1200px] border border-neutral-200">
                        <thead>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" rowspan="2" style="width: 30px; min-width: 30px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Stt</th>
                                <th scope="col" rowspan="2" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã SP</th>
                                <th scope="col" rowspan="2" style="width: 150px; min-width: 150px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên sản phẩm <span class="text-danger-500">*</span></th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Độ dày</th>
                                <th scope="col" rowspan="2" style="width: 80px; min-width: 80px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều mở cánh</th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Màu nhôm</th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Màu kính</th>
                                <th scope="col" colspan="2" style="white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Kích thước cánh (mm)</th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn vị</th>
                                <th scope="col" rowspan="2" style="width: 50px; min-width: 50px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng cánh <span class="text-danger-500">*</span></th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Khối lượng (m2)</th>
                                <th scope="col" rowspan="2" style="width: 80px; min-width: 80px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                                <th scope="col" rowspan="2" style="width: 90px; min-width: 90px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                                <th scope="col" rowspan="2" style="min-width: 120px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                                <th scope="col" rowspan="2" style="width: 60px; min-width: 60px; white-space: nowrap; position: sticky; right: 0; z-index: 3; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase bg-neutral-50">Hành động</th>
                            </tr>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" style="width: 130px; min-width: 130px; white-space: nowrap;" class="border border-neutral-200 bg-yellow-100/70 font-semibold text-xs text-neutral-700 uppercase">Dài (mm)</th>
                                <th scope="col" style="width: 130px; min-width: 130px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng (mm)</th>
                            </tr>
                        </thead>
                        <tbody class="supply-items-container" data-supply-index="${glassSupplyIndex}">
                        </tbody>
                    </table>
                </div>
                <button type="button" onclick="addGlassOrderItem(this)" class="w-full mt-4 py-3 border-2 border-dashed border-primary-300 hover:border-primary-500 rounded-xl bg-primary-50/50 hover:bg-primary-50 text-primary-600 font-semibold text-sm flex items-center justify-center gap-1.5 transition-all duration-200">
                    <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon>
                    Thêm sản phẩm mới (Sao chép từ sản phẩm cuối)
                </button>
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
    newSupply.className = 'order-supply-row bg-neutral-50/50 border border-primary-600 rounded-xl p-5 mb-2 relative shadow-sm';
    newSupply.innerHTML = `
        <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                    <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                </div>
                <input type="text" name="supplies[${glassSupplyIndex}][order_supply_code]" class="order-supply-code-input form-control form-control-sm rounded-lg w-40 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Mã vật tư">
                <input type="text" name="supplies[${glassSupplyIndex}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)">
                <input type="number" name="supplies[${glassSupplyIndex}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="0.01" value="0">
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="this.closest('.order-supply-row').remove()" class="order-table-form-action text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                    <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto pb-3">
            <table class="table bordered-table sm-table mb-0 min-w-[1800px] border border-neutral-200">
                <thead>
                    <tr class="bg-neutral-50 text-center">
                        <th scope="col" rowspan="2" style="width: 45px; min-width: 45px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Stt</th>
                        <th scope="col" rowspan="2" style="width: 160px; min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã SP</th>
                        <th scope="col" rowspan="2" style="min-width: 220px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên sản phẩm <span class="text-danger-500">*</span></th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Độ dày</th>
                        <th scope="col" rowspan="2" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều mở cánh</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Màu nhôm</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Màu kính</th>
                        <th scope="col" colspan="2" style="white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Kích thước cánh (mm)</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn vị</th>
                        <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng cánh <span class="text-danger-500">*</span></th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Khối lượng (m2)</th>
                        <th scope="col" rowspan="2" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                        <th scope="col" rowspan="2" style="width: 120px; min-width: 120px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                        <th scope="col" rowspan="2" style="min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                        <th scope="col" rowspan="2" style="width: 80px; min-width: 80px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Hành động</th>
                    </tr>
                    <tr class="bg-neutral-50 text-center">
                        <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="border border-neutral-200 bg-yellow-100/70 font-semibold text-xs text-neutral-700 uppercase">Dài (mm)</th>
                        <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng (mm)</th>
                    </tr>
                </thead>
                <tbody class="supply-items-container" data-supply-index="${glassSupplyIndex}">
                </tbody>
            </table>
        </div>
        <button type="button" onclick="addGlassOrderItem(this)" class="order-table-form-action w-full mt-4 py-3 border-2 border-dashed border-primary-300 hover:border-primary-500 rounded-xl bg-primary-50/50 hover:bg-primary-50 text-primary-600 font-semibold text-sm flex items-center justify-center gap-1.5 transition-all duration-200">
            <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon>
            Thêm sản phẩm mới (Sao chép từ sản phẩm cuối)
        </button>
    `;
    newSupply.querySelector('.order-supply-code-input').addEventListener('input', function() {
        updateGlassRowIndexes(newSupply.querySelector('.supply-items-container'));
    });
    container.appendChild(newSupply);

    // Add one product row by default
    const addProductBtn = newSupply.querySelector('button[onclick^="addGlassOrderItem"]');
    if (addProductBtn) {
        addGlassOrderItem(addProductBtn);
    }

    glassSupplyIndex++;
}

function addGlassOrderItem(button) {
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
            wing_opening_direction: lastRow.querySelector('input[name*="[wing_opening_direction]"]') ? lastRow.querySelector('input[name*="[wing_opening_direction]"]').value : '',
            aluminum_color: lastRow.querySelector('input[name*="[aluminum_color]"]') ? lastRow.querySelector('input[name*="[aluminum_color]"]').value : '',
            glass_color: lastRow.querySelector('input[name*="[glass_color]"]') ? lastRow.querySelector('input[name*="[glass_color]"]').value : '',
            height: lastRow.querySelector('input[name*="[height]"]') ? lastRow.querySelector('input[name*="[height]"]').value : '',
            width: lastRow.querySelector('input[name*="[width]"]') ? lastRow.querySelector('input[name*="[width]"]').value : '',
            unit: lastRow.querySelector('input[name*="[unit]"]') ? lastRow.querySelector('input[name*="[unit]"]').value : 'Bộ',
            wing_quantity: lastRow.querySelector('input[name*="[wing_quantity]"]') ? lastRow.querySelector('input[name*="[wing_quantity]"]').value : '1',
            area_m2: lastRow.querySelector('input[name*="[area_m2]"]') ? lastRow.querySelector('input[name*="[area_m2]"]').value : '',
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
        <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][wing_opening_direction]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Chiều mở cánh" value="${lastData ? lastData.wing_opening_direction : ''}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][aluminum_color]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Màu nhôm" value="${lastData ? lastData.aluminum_color : ''}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][glass_color]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Màu kính" value="${lastData ? lastData.glass_color : ''}">
        </td>
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.height : ''}">
        </td>
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.width : ''}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][unit]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs px-1" placeholder="Bộ" value="${lastData ? lastData.unit : 'Bộ'}">
        </td>
        <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][wing_quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="1" min="1" required value="${lastData ? lastData.wing_quantity : '1'}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][area_m2]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.area_m2 : ''}">
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
                <button type="button" onclick="duplicateGlassRow(this)" class="text-neutral-400 hover:text-primary-500 transition-colors p-1" title="Nhân bản sản phẩm">
                    <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                </button>
                <button type="button" onclick="removeGlassOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
                    <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                </button>
            </div>
        </td>
    `;
    container.appendChild(newItem);
    
    const newRow = container.lastElementChild;
    bindGlassRowEvents(newRow);
    updateOrderSummary();
    updateGlassRowIndexes();
}

function removeGlassOrderItem(button) {
    const row = button.closest('.order-item-row');
    const tbody = row.closest('.supply-items-container');
    row.remove();
    updateOrderSummary();
    updateGlassRowIndexes();
}

function updateGlassRowIndexes() {
    const orderCode = document.getElementById('order-code-input')?.value || '';
    let globalPieceIndex = 1;
    let globalItemIndex = 1;

    document.querySelectorAll('#glass-supplies-container .order-supply-row').forEach((supplyRow, supplyIndex) => {
        const supplyCode = supplyRow.querySelector('.order-supply-code-input')?.value || '';
        const tbody = supplyRow.querySelector('.supply-items-container');
        if (!tbody) return;

        tbody.querySelectorAll('.order-item-row').forEach((row, itemIndex) => {
            // Update row STT
            const indexEl = row.querySelector('.row-index');
            if (indexEl) indexEl.textContent = globalItemIndex;

            // Get quantity (Glass uses wing_quantity)
            const quantityInput = row.querySelector('input[name*="[wing_quantity]"]');
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

function duplicateGlassRow(button) {
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

    bindGlassRowEvents(newRow);
    updateGlassRowIndexes();
    updateOrderSummary();
}

function calculateGlassTotalPrice(row, sourceEvent) {
    const heightInput = row.querySelector('input[name*="[height]"]');
    const widthInput = row.querySelector('input[name*="[width]"]');
    const wingQtyInput = row.querySelector('input[name*="[wing_quantity]"]');
    const areaInput = row.querySelector('input[name*="[area_m2]"]');
    const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
    
    if (!heightInput || !widthInput || !wingQtyInput || !areaInput) return;
    
    if (!heightInput || !widthInput || !wingQtyInput || !areaInput || !unitPriceInput) return;

    const height = parseFloat(heightInput.value) || 0;
    const width = parseFloat(widthInput.value) || 0;
    const wingQty = parseFloat(wingQtyInput.value) || 0;
    const unitPrice = parseFloat(unitPriceInput.value) || 0;
    
    if (sourceEvent !== 'area') {
        let area = 0;
        if (height > 0 && width > 0 && wingQty > 0) {
            area = (height * width * wingQty) / 1000000;
        }
        areaInput.value = area > 0 ? area.toFixed(4) : '';
    }
    
    const currentArea = parseFloat(areaInput.value) || 0;
    const totalPrice = currentArea * unitPrice;
    
    const totalPriceInput = row.querySelector('input[name*="[total_price]"]');
    if (totalPriceInput) {
        totalPriceInput.value = Math.round(totalPrice);
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
    });

    // Recalculate codes on load
    updateGlassRowIndexes();

    const orderCodeInput = document.getElementById('order-code-input');
    if (orderCodeInput) {
        orderCodeInput.addEventListener('input', () => updateGlassRowIndexes());
    }
});

// Live listener for supply code input changes
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('order-supply-code-input')) {
        updateGlassRowIndexes();
    }
});
</script>
