<div class="card p-0 rounded-xl border-0 overflow-hidden">
    <div class="card-header border-b border-neutral-200 bg-white py-4 px-6">
        <h5 class="font-semibold text-base">{{ $title ?? 'Đơn hàng Acrylic' }}</h5>
    </div>
    <form action="{{ $action }}" method="POST" id="order-form" enctype="multipart/form-data">
        @if(isset($acrylicOrder))
            @method('PUT')
        @endif
        @csrf
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Main Form - col-lg-8 --}}
                <div class="lg:col-span-8">
                    {{-- Customer Info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="form-group">
                            <label class="form-label font-semibold text-sm text-neutral-600">Khách hàng</label>
                            <select name="customer_id" class="form-select rounded-lg" onchange="fillCustomerInfo(this.value)">
                                <option value="">-- Chọn khách hàng --</option>
                                @foreach(\App\Models\Customer::all() as $customer)
                                <option value="{{ $customer->id }}" {{ isset($acrylicOrder) && $acrylicOrder?->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->customer_code }} - {{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group md:col-span-2">
                            <label class="form-label font-semibold text-sm text-neutral-600">Tên khách hàng <span class="text-danger-500">*</span></label>
                            <input type="text" name="customer_name" class="form-control rounded-lg" placeholder="Nhập tên khách hàng" required value="{{ old('customer_name', $acrylicOrder?->customer_name ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label font-semibold text-sm text-neutral-600">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control rounded-lg" placeholder="Nhập số điện thoại" value="{{ old('phone', $acrylicOrder?->phone ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label font-semibold text-sm text-neutral-600">Hạn đơn</label>
                            <input type="date" name="deadline" class="form-control rounded-lg" value="{{ old('deadline', (isset($acrylicOrder) && $acrylicOrder->deadline) ? ($acrylicOrder->deadline instanceof \Carbon\Carbon ? $acrylicOrder->deadline->format('Y-m-d') : date('Y-m-d', strtotime($acrylicOrder->deadline))) : '') }}">
                        </div>
                        <div class="form-group md:col-span-2">
                            <label class="form-label font-semibold text-sm text-neutral-600">Địa chỉ</label>
                            <textarea name="address" class="form-control rounded-lg" placeholder="Nhập địa chỉ" rows="2">{{ old('address', $acrylicOrder?->address ?? '') }}</textarea>
                        </div>
                        <div class="form-group md:col-span-2">
                            <label class="form-label font-semibold text-sm text-neutral-600">Ghi chú đơn hàng</label>
                            <textarea name="notes" class="form-control rounded-lg" placeholder="Nhập ghi chú" rows="2">{{ old('notes', $acrylicOrder?->notes ?? '') }}</textarea>
                        </div>
                        @if(isset($acrylicOrder))
                        <div class="form-group">
                            <label class="form-label font-semibold text-sm text-neutral-600">Trạng thái</label>
                            <select name="status" class="form-select rounded-lg">
                                <option value="pending" {{ $acrylicOrder->status === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                                <option value="processing" {{ $acrylicOrder->status === 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                                <option value="completed" {{ $acrylicOrder->status === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                <option value="cancelled" {{ $acrylicOrder->status === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                            </select>
                        </div>
                        @endif
                    </div>

                    {{-- Order Items --}}
                    <div class="border-t border-neutral-200 pt-4">
                        <div class="flex items-center justify-between mb-4">
                            <h6 class="font-semibold text-base">Danh sách Sản phẩm</h6>
                            <button type="button" onclick="addOrderItem()" class="btn btn-sm btn-primary rounded-lg">
                                Thêm sản phẩm
                            </button>
                        </div>
                        <div id="order-items-container">
                            @if(isset($acrylicOrder) && $acrylicOrder->items->count() > 0)
                                @foreach($acrylicOrder->items as $index => $item)
                                <div class="order-item-row bg-neutral-50 p-4 rounded-lg mb-3" data-item-id="{{ $item->id }}">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-medium text-neutral-600">Sản phẩm {{ $index + 1 }}</span>
                                        <button type="button" onclick="this.closest('.order-item-row').remove()" class="text-danger-500 hover:text-danger-700">
                                            <iconify-icon icon="lucide:x" class="icon"></iconify-icon>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-4 gap-3 mb-3">
                                        <div class="form-group">
                                            <label class="form-label text-xs text-neutral-600">Mã SP</label>
                                            <select name="items[{{ $index }}][product_code]" class="tom-select-product" onchange="fillProductInfo(this, {{ $index }})">
                                                <option value="">-- Chọn --</option>
                                                @foreach(\App\Models\Supply::pluck('product_code')->filter()->unique() as $code)
                                                <option value="{{ $code }}" {{ $item->product_code == $code ? 'selected' : '' }}>{{ $code }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-span-2">
                                            <label class="form-label text-xs text-neutral-600">Tên SP <span class="text-danger-500">*</span></label>
                                            <input type="text" name="items[{{ $index }}][product_name]" class="form-control form-control-sm rounded-lg" placeholder="Tên sản phẩm" required value="{{ $item->product_name }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label text-xs text-neutral-600">Số lượng <span class="text-danger-500">*</span></label>
                                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm rounded-lg" placeholder="1" min="1" required value="{{ $item->quantity }}">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-4 gap-3 mb-3">
                                        <div class="form-group">
                                            <label class="form-label text-xs text-neutral-600">Cao</label>
                                            <input type="number" name="items[{{ $index }}][height]" class="form-control form-control-sm rounded-lg" placeholder="0" step="0.01" value="{{ $item->height }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label text-xs text-neutral-600">Rộng</label>
                                            <input type="number" name="items[{{ $index }}][width]" class="form-control form-control-sm rounded-lg" placeholder="0" step="0.01" value="{{ $item->width }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label text-xs text-neutral-600">Cạnh Vát</label>
                                            <input type="text" name="items[{{ $index }}][edge_bevel]" class="form-control form-control-sm rounded-lg" placeholder="Cạnh vát" value="{{ $item->edge_bevel }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label text-xs text-neutral-600">Chiều vân</label>
                                            <select name="items[{{ $index }}][grain_direction]" class="form-select form-select-sm rounded-lg">
                                                <option value="0" {{ $item->grain_direction == 0 ? 'selected' : '' }}>0</option>
                                                <option value="2" {{ $item->grain_direction == 2 ? 'selected' : '' }}>2</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-4 gap-3 mb-3">
                                        <div class="form-group">
                                            <label class="form-label text-xs text-neutral-600">Cánh (m2)</label>
                                            <input type="number" name="items[{{ $index }}][wing_area]" class="form-control form-control-sm rounded-lg" placeholder="0" step="0.01" value="{{ $item->wing_area }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label text-xs text-neutral-600">Thanh Phào (m)</label>
                                            <input type="number" name="items[{{ $index }}][molding_length]" class="form-control form-control-sm rounded-lg" placeholder="0" step="0.01" value="{{ $item->molding_length }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label text-xs text-neutral-600">Vát</label>
                                            <input type="text" name="items[{{ $index }}][bevel]" class="form-control form-control-sm rounded-lg" placeholder="Vát" value="{{ $item->bevel }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label text-xs text-neutral-600">Vân dọc CNC</label>
                                            <input type="text" name="items[{{ $index }}][vertical_grain_cnc]" class="form-control form-control-sm rounded-lg" placeholder="Vân dọc CNC" value="{{ $item->vertical_grain_cnc }}">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                        <div class="form-group">
                                            <label class="form-label text-xs text-neutral-600">Đơn giá <span class="text-danger-500">*</span></label>
                                            <input type="number" name="items[{{ $index }}][unit_price]" class="form-control form-control-sm rounded-lg" placeholder="0" min="0" required value="{{ $item->unit_price }}">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label text-xs text-neutral-600">Thành tiền</label>
                                            <input type="number" name="items[{{ $index }}][total_price]" class="form-control form-control-sm rounded-lg" placeholder="0" step="0.01" value="{{ $item->total_price }}" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label text-xs text-neutral-600">Ghi chú</label>
                                            <input type="text" name="items[{{ $index }}][notes]" class="form-control form-control-sm rounded-lg" placeholder="Ghi chú" value="{{ $item->notes }}">
                                        </div>
                                    </div>
                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                </div>
                                @endforeach
                                @php $itemIndex = $acrylicOrder->items->count() @endphp
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Order Summary & Attachments - col-lg-4 --}}
                <div class="lg:col-span-4">
                    <div class="bg-neutral-50 rounded-lg p-4 sticky top-4 space-y-4">
                        <div>
                            <h6 class="font-semibold text-base mb-4">Tóm tắt đơn hàng</h6>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-neutral-600">Số lượng sản phẩm:</span>
                                    <span class="text-sm font-medium" id="total-items">0</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-neutral-600">Tổng tiền:</span>
                                    <span class="text-sm font-medium text-primary-600" id="total-amount">0 VNĐ</span>
                                </div>
                                <hr class="border-neutral-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-semibold">Tổng thanh toán:</span>
                                    <span class="text-lg font-bold text-primary-600" id="grand-total">0 VNĐ</span>
                                </div>
                            </div>
                        </div>

                        <hr class="border-neutral-200">

                        <div>
                            <h6 class="font-semibold text-base mb-4">Hình ảnh đính kèm</h6>
                            <div class="form-group">
                                <label class="form-label font-semibold text-sm text-neutral-600">Tải lên hình ảnh</label>
                                <input type="file" id="attachments-input" name="attachments[]" class="form-control rounded-lg" multiple accept="image/*">
                                <p class="text-xs text-secondary-light mt-1">JPG, PNG — có thể chọn nhiều hình ảnh</p>
                            </div>
                            
                            {{-- Preview Container for newly selected images --}}
                            <div class="mt-4 hidden" id="new-attachments-preview-container">
                                <label class="form-label font-semibold text-sm text-neutral-600">Hình ảnh mới chọn</label>
                                <div class="flex flex-wrap gap-2" id="new-attachments-preview"></div>
                            </div>

                            @if(isset($acrylicOrder) && $acrylicOrder->attachments)
                            <div class="mt-4">
                                <label class="form-label font-semibold text-sm text-neutral-600">Hình ảnh đã tải</label>
                                <div class="flex flex-wrap gap-2" id="existing-attachments">
                                    @foreach(json_decode($acrylicOrder->attachments, true) ?? [] as $index => $image)
                                    <div class="relative">
                                        <img src="{{ route('acrylic_orders.image', ['filename' => basename($image)]) }}" class="w-20 h-20 object-cover rounded-lg border border-neutral-200">
                                        <button type="button" onclick="deleteAttachment('{{ $index }}', '{{ $image }}')" class="absolute top-0 right-0 m-1 bg-danger-100 hover:bg-danger-200 text-danger-600 font-medium w-7 h-7 m-2 p-2 flex justify-center items-center rounded-full">
                                            <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="delete_attachments" id="delete-attachments" value="">
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">{{ isset($acrylicOrder) ? 'Cập nhật' : 'Lưu đơn hàng' }}</button>
            <a href="{{ route('acrylic_orders.index') }}" class="btn btn-neutral px-5 py-2.5 rounded-lg">Quay lại</a>
        </div>
    </form>
</div>

<script>
let itemIndex = {{ $itemIndex ?? 1 }};

function addOrderItem() {
    const container = document.getElementById('order-items-container');
    const newItem = document.createElement('div');
    newItem.className = 'order-item-row bg-neutral-50 p-4 rounded-lg mb-3';
    newItem.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-neutral-600">Sản phẩm mới</span>
            <button type="button" onclick="this.closest('.order-item-row').remove()" class="text-danger-500 hover:text-danger-700">
                <iconify-icon icon="lucide:x" class="icon"></iconify-icon>
            </button>
        </div>
        <div class="grid grid-cols-4 gap-3 mb-3">
            <div class="form-group">
                <label class="form-label text-xs text-neutral-600">Mã SP</label>
                <select name="items[${itemIndex}][product_code]" class="tom-select-product" onchange="fillProductInfo(this, ${itemIndex})">
                    <option value="">-- Chọn --</option>
                    @foreach(\App\Models\Supply::pluck('product_code')->filter()->unique() as $code)
                    <option value="{{ $code }}">{{ $code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-span-2">
                <label class="form-label text-xs text-neutral-600">Tên SP <span class="text-danger-500">*</span></label>
                <input type="text" name="items[${itemIndex}][product_name]" class="form-control form-control-sm rounded-lg" placeholder="Tên sản phẩm" required>
            </div>
            <div class="form-group">
                <label class="form-label text-xs text-neutral-600">Số lượng <span class="text-danger-500">*</span></label>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm rounded-lg" placeholder="1" min="1" required value="1">
            </div>
        </div>
        <div class="grid grid-cols-4 gap-3 mb-3">
            <div class="form-group">
                <label class="form-label text-xs text-neutral-600">Cao</label>
                <input type="number" name="items[${itemIndex}][height]" class="form-control form-control-sm rounded-lg" placeholder="0" step="0.01">
            </div>
            <div class="form-group">
                <label class="form-label text-xs text-neutral-600">Rộng</label>
                <input type="number" name="items[${itemIndex}][width]" class="form-control form-control-sm rounded-lg" placeholder="0" step="0.01">
            </div>
            <div class="form-group">
                <label class="form-label text-xs text-neutral-600">Cạnh Vát</label>
                <input type="text" name="items[${itemIndex}][edge_bevel]" class="form-control form-control-sm rounded-lg" placeholder="Cạnh vát">
            </div>
            <div class="form-group">
                <label class="form-label text-xs text-neutral-600">Chiều vân</label>
                <select name="items[${itemIndex}][grain_direction]" class="form-select form-select-sm rounded-lg">
                    <option value="0">0</option>
                    <option value="2">2</option>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-4 gap-3 mb-3">
            <div class="form-group">
                <label class="form-label text-xs text-neutral-600">Cánh (m2)</label>
                <input type="number" name="items[${itemIndex}][wing_area]" class="form-control form-control-sm rounded-lg" placeholder="0" step="0.01">
            </div>
            <div class="form-group">
                <label class="form-label text-xs text-neutral-600">Thanh Phào (m)</label>
                <input type="number" name="items[${itemIndex}][molding_length]" class="form-control form-control-sm rounded-lg" placeholder="0" step="0.01">
            </div>
            <div class="form-group">
                <label class="form-label text-xs text-neutral-600">Vát</label>
                <input type="text" name="items[${itemIndex}][bevel]" class="form-control form-control-sm rounded-lg" placeholder="Vát">
            </div>
            <div class="form-group">
                <label class="form-label text-xs text-neutral-600">Vân dọc CNC</label>
                <input type="text" name="items[${itemIndex}][vertical_grain_cnc]" class="form-control form-control-sm rounded-lg" placeholder="Vân dọc CNC">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
            <div class="form-group">
                <label class="form-label text-xs text-neutral-600">Đơn giá <span class="text-danger-500">*</span></label>
                <input type="number" name="items[${itemIndex}][unit_price]" class="form-control form-control-sm rounded-lg" placeholder="0" min="0" required>
            </div>
            <div class="form-group">
                <label class="form-label text-xs text-neutral-600">Thành tiền</label>
                <input type="number" name="items[${itemIndex}][total_price]" class="form-control form-control-sm rounded-lg" placeholder="0" step="0.01" readonly>
            </div>
            <div class="form-group">
                <label class="form-label text-xs text-neutral-600">Ghi chú</label>
                <input type="text" name="items[${itemIndex}][notes]" class="form-control form-control-sm rounded-lg" placeholder="Ghi chú">
            </div>
        </div>
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
    
    itemIndex++;
}

function fillCustomerInfo(customerId) {
    @if(auth()->check())
    const customers = @json(\App\Models\Customer::all());
    const customer = customers.find(c => c.id == customerId);
    if (customer) {
        document.querySelector('input[name="customer_name"]').value = customer.name;
        document.querySelector('input[name="phone"]').value = customer.phone || '';
        document.querySelector('textarea[name="address"]').value = customer.address || '';
    }
    @endif
}

function calculateTotalPrice(row) {
    const unitPrice = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
    const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
    const totalPrice = unitPrice * quantity;
    row.querySelector('input[name*="[total_price]"]').value = totalPrice.toFixed(2);
    updateOrderSummary();
}

// Attach event listeners to existing inputs
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tom-select for existing product code selects
    if (typeof TomSelect !== 'undefined') {
        document.querySelectorAll('.tom-select-product').forEach(function(element) {
            new TomSelect(element, {
                allowEmptyOption: true,
                placeholder: '-- Chọn --',
            });
        });
    }
    
    document.querySelectorAll('.order-item-row').forEach(row => {
        const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
        const quantityInput = row.querySelector('input[name*="[quantity]"]');
        if (unitPriceInput) unitPriceInput.addEventListener('input', () => calculateTotalPrice(row));
        if (quantityInput) quantityInput.addEventListener('input', () => calculateTotalPrice(row));
    });
    
    // Initialize order summary
    updateOrderSummary();

    // Image preview handler
    const attachmentsInput = document.getElementById('attachments-input');
    if (attachmentsInput) {
        attachmentsInput.addEventListener('change', function() {
            const previewContainer = document.getElementById('new-attachments-preview-container');
            const previewDiv = document.getElementById('new-attachments-preview');
            previewDiv.innerHTML = ''; // Clear previous previews

            if (this.files && this.files.length > 0) {
                previewContainer.classList.remove('hidden');
                Array.from(this.files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imgWrapper = document.createElement('div');
                            imgWrapper.className = 'relative';
                            imgWrapper.innerHTML = `
                                <img src="${e.target.result}" class="w-20 h-20 object-cover rounded-lg border border-neutral-200">
                            `;
                            previewDiv.appendChild(imgWrapper);
                        }
                        reader.readAsDataURL(file);
                    }
                });
            } else {
                previewContainer.classList.add('hidden');
            }
        });
    }
});

function deleteAttachment(index, imagePath) {
    if (confirm('Bạn có chắc muốn xóa hình ảnh này?')) {
        // Remove from DOM
        const container = document.getElementById('existing-attachments');
        const imageDivs = container.querySelectorAll('.relative.group');
        if (imageDivs[index]) {
            imageDivs[index].remove();
        }
        
        // Track deleted attachments
        const deleteInput = document.getElementById('delete-attachments');
        let deleted = deleteInput.value ? JSON.parse(deleteInput.value) : [];
        deleted.push(imagePath);
        deleteInput.value = JSON.stringify(deleted);
    }
}

function fillProductInfo(selectElement, index) {
    const productCode = selectElement.value;
    const row = selectElement.closest('.order-item-row');
    
    @if(auth()->check())
    const supplies = @json(\App\Models\Supply::all());
    const supply = supplies.find(s => s.product_code === productCode);
    if (supply) {
        const productNameInput = row.querySelector(`input[name="items[${index}][product_name]"]`);
        if (productNameInput) productNameInput.value = supply.name || '';
        
        const unitPriceInput = row.querySelector(`input[name="items[${index}][unit_price]"]`);
        if (unitPriceInput) {
            unitPriceInput.value = supply.unit_price || 0;
            calculateTotalPrice(row);
        }
    }
    @endif
    updateOrderSummary();
}

function updateOrderSummary() {
    const rows = document.querySelectorAll('.order-item-row');
    let totalItems = 0;
    let totalAmount = 0;
    
    rows.forEach(row => {
        const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
        const totalPrice = parseFloat(row.querySelector('input[name*="[total_price]"]').value) || 0;
        totalItems += quantity;
        totalAmount += totalPrice;
    });
    
    document.getElementById('total-items').textContent = totalItems;
    document.getElementById('total-amount').textContent = totalAmount.toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('grand-total').textContent = totalAmount.toLocaleString('vi-VN') + ' VNĐ';
}
</script>
