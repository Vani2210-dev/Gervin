{{-- Order Supplies & Items Section Card --}}
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
        @if(isset($acrylicOrder) && $acrylicOrder->type == 'acrylic' && $acrylicOrder->supplies->count() > 0)
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
                            <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon>
                        </button>
                        <button type="button" onclick="this.closest('.order-supply-row').remove()" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa vật tư này">
                            <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto pb-3">
                    <table class="table bordered-table sm-table mb-0 min-w-[1700px]">
                        <thead>
                            <tr>
                                <th scope="col" class="w-32">Mã SP</th>
                                <th scope="col" class="w-64">Tên SP <span class="text-danger-500">*</span></th>
                                <th scope="col" class="w-20">SL <span class="text-danger-500">*</span></th>
                                <th scope="col" class="w-20">Cao</th>
                                <th scope="col" class="w-20">Rộng</th>
                                <th scope="col" class="w-24">Cạnh Vát</th>
                                <th scope="col" class="w-24">Chiều vân</th>
                                <th scope="col" class="w-24">Cánh (m2)</th>
                                <th scope="col" class="w-24">Phào (m)</th>
                                <th scope="col" class="w-24">Vát</th>
                                <th scope="col" class="w-28">Vân dọc CNC</th>
                                <th scope="col" class="w-28">Đơn giá <span class="text-danger-500">*</span></th>
                                <th scope="col" class="w-28">Thành tiền</th>
                                <th scope="col" class="w-44">Ghi chú</th>
                                <th scope="col" class="text-center w-12">Xóa</th>
                            </tr>
                        </thead>
                        <tbody class="supply-items-container" data-supply-index="{{ $supplyIndex }}">
                            @foreach($supply->items as $itemIndex => $item)
                            <tr class="order-item-row" data-item-id="{{ $item->id }}">
                                <td>
                                    <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_code]" class="tom-select-product" onchange="fillProductInfo(this, {{ $supplyIndex }}, {{ $itemIndex }})">
                                        <option value="">-- Chọn --</option>
                                        @foreach(\App\Models\Supply::pluck('product_code')->filter()->unique() as $code)
                                        <option value="{{ $code }}" {{ $item->product_code == $code ? 'selected' : '' }}>{{ $code }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên sản phẩm" required value="{{ $item->product_name }}">
                                </td>
                                <td>
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="1" min="1" required value="{{ $item->quantity }}">
                                </td>
                                <td>
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][height]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" step="0.01" value="{{ $item->height }}">
                                </td>
                                <td>
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" step="0.01" value="{{ $item->width }}">
                                </td>
                                <td>
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Cạnh vát" value="{{ $item->edge_bevel }}">
                                </td>
                                <td>
                                    <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][grain_direction]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500">
                                        <option value="0" {{ $item->grain_direction == 0 ? 'selected' : '' }}>0</option>
                                        <option value="2" {{ $item->grain_direction == 2 ? 'selected' : '' }}>2</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][wing_area]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" step="0.01" value="{{ $item->wing_area }}">
                                </td>
                                <td>
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][molding_length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" step="0.01" value="{{ $item->molding_length }}">
                                </td>
                                <td>
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Vát" value="{{ $item->bevel }}">
                                </td>
                                <td>
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][vertical_grain_cnc]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Vân dọc CNC" value="{{ $item->vertical_grain_cnc }}">
                                </td>
                                <td>
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][unit_price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" min="0" required value="{{ $item->unit_price }}">
                                </td>
                                <td>
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][total_price]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700" placeholder="0" step="0.01" value="{{ $item->total_price }}" readonly>
                                </td>
                                <td>
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Ghi chú" value="{{ $item->notes }}">
                                </td>
                                <td class="text-center">
                                    <button type="button" onclick="this.closest('.order-item-row').remove(); updateOrderSummary();" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
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
                <div class="p-1.5 bg-primary-50 rounded-lg text-primary-600 flex items-center justify-center">
                    <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                </div>
                <input type="text" name="supplies[\${supplyIndex}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)">
                <input type="number" name="supplies[\${supplyIndex}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="0.01" value="0">
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="addOrderItem(this)" class="text-neutral-400 hover:text-primary-600 transition-colors p-1">
                    <iconify-icon icon="lucide:package-plus" class="text-lg"></iconify-icon> Thêm
                </button>
                <button type="button" onclick="this.closest('.order-supply-row').remove()" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa vật tư này">
                    <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon> Xóa
                </button>
            </div>
        </div>
        <div class="overflow-x-auto pb-3">
            <table class="table bordered-table sm-table mb-0 min-w-[1700px]">
                <thead>
                    <tr>
                        <th scope="col" class="w-32">Mã SP</th>
                        <th scope="col" class="w-64">Tên SP <span class="text-danger-500">*</span></th>
                        <th scope="col" class="w-20">SL <span class="text-danger-500">*</span></th>
                        <th scope="col" class="w-20">Cao</th>
                        <th scope="col" class="w-20">Rộng</th>
                        <th scope="col" class="w-24">Cạnh Vát</th>
                        <th scope="col" class="w-24">Chiều vân</th>
                        <th scope="col" class="w-24">Cánh (m2)</th>
                        <th scope="col" class="w-24">Phào (m)</th>
                        <th scope="col" class="w-24">Vát</th>
                        <th scope="col" class="w-28">Vân dọc CNC</th>
                        <th scope="col" class="w-28">Đơn giá <span class="text-danger-500">*</span></th>
                        <th scope="col" class="w-28">Thành tiền</th>
                        <th scope="col" class="w-44">Ghi chú</th>
                        <th scope="col" class="text-center w-12">Xóa</th>
                    </tr>
                </thead>
                <tbody class="supply-items-container" data-supply-index="\${supplyIndex}">
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
        <td>
            <select name="supplies[\${supplyIndex}][items][\${itemIndex}][product_code]" class="tom-select-product" onchange="fillProductInfo(this, \${supplyIndex}, \${itemIndex})">
                <option value="">-- Chọn --</option>
                @foreach(\App\Models\Supply::pluck('product_code')->filter()->unique() as $code)
                <option value="{{ $code }}">{{ $code }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" name="supplies[\${supplyIndex}][items][\${itemIndex}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên sản phẩm" required>
        </td>
        <td>
            <input type="number" name="supplies[\${supplyIndex}][items][\${itemIndex}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="1" min="1" required value="1">
        </td>
        <td>
            <input type="number" name="supplies[\${supplyIndex}][items][\${itemIndex}][height]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" step="0.01">
        </td>
        <td>
            <input type="number" name="supplies[\${supplyIndex}][items][\${itemIndex}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" step="0.01">
        </td>
        <td>
            <input type="text" name="supplies[\${supplyIndex}][items][\${itemIndex}][edge_bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Cạnh vát">
        </td>
        <td>
            <select name="supplies[\${supplyIndex}][items][\${itemIndex}][grain_direction]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500">
                <option value="0">0</option>
                <option value="2">2</option>
            </select>
        </td>
        <td>
            <input type="number" name="supplies[\${supplyIndex}][items][\${itemIndex}][wing_area]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" step="0.01">
        </td>
        <td>
            <input type="number" name="supplies[\${supplyIndex}][items][\${itemIndex}][molding_length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" step="0.01">
        </td>
        <td>
            <input type="text" name="supplies[\${supplyIndex}][items][\${itemIndex}][bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Vát">
        </td>
        <td>
            <input type="text" name="supplies[\${supplyIndex}][items][\${itemIndex}][vertical_grain_cnc]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Vân dọc CNC">
        </td>
        <td>
            <input type="number" name="supplies[\${supplyIndex}][items][\${itemIndex}][unit_price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" min="0" required>
        </td>
        <td>
            <input type="number" name="supplies[\${supplyIndex}][items][\${itemIndex}][total_price]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700" placeholder="0" step="0.01" readonly>
        </td>
        <td>
            <input type="text" name="supplies[\${supplyIndex}][items][\${itemIndex}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Ghi chú">
        </td>
        <td class="text-center">
            <button type="button" onclick="this.closest('.order-item-row').remove(); updateOrderSummary();" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
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
    const unitPriceInput = newRow.querySelector('input[name*="[unit_price]"]');
    const quantityInput = newRow.querySelector('input[name*="[quantity]"]');
    if (unitPriceInput) unitPriceInput.addEventListener('input', () => calculateTotalPrice(newRow));
    if (quantityInput) quantityInput.addEventListener('input', () => calculateTotalPrice(newRow));
    
    updateOrderSummary();
}

function calculateTotalPrice(row) {
    const unitPrice = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
    const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
    const totalPrice = unitPrice * quantity;
    row.querySelector('input[name*="[total_price]"]').value = totalPrice.toFixed(2);
    updateOrderSummary();
}

function fillProductInfo(selectElement, supplyIndex, itemIndex) {
    const productCode = selectElement.value;
    const row = selectElement.closest('.order-item-row');
    
    @if(auth()->check())
    const supplies = @json(\App\Models\Supply::all());
    const supply = supplies.find(s => s.product_code === productCode);
    if (supply) {
        const productNameInput = row.querySelector(`input[name="supplies[\${supplyIndex}][items][\${itemIndex}][product_name]"]`);
        if (productNameInput) productNameInput.value = supply.name || '';
        
        const unitPriceInput = row.querySelector(`input[name="supplies[\${supplyIndex}][items][\${itemIndex}][unit_price]"]`);
        if (unitPriceInput) {
            unitPriceInput.value = supply.unit_price || 0;
            calculateTotalPrice(row);
        }
    }
    @endif
    updateOrderSummary();
}

// Initial attachment setup for Acrylic-specific rows if DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.order-item-row').forEach(row => {
        const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
        const quantityInput = row.querySelector('input[name*="[quantity]"]');
        if (unitPriceInput) unitPriceInput.addEventListener('input', () => calculateTotalPrice(row));
        if (quantityInput) quantityInput.addEventListener('input', () => calculateTotalPrice(row));
    });
});
</script>
