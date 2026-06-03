<div class="card p-0 rounded-xl border-0 overflow-hidden">
    <div class="card-header border-b border-neutral-200 bg-white py-4 px-6">
        <h5 class="font-semibold text-base">{{ $title ?? 'Tạo đơn hàng' }}</h5>
    </div>
    <form action="{{ $action }}" method="POST" id="order-form" enctype="multipart/form-data">
        @if(isset($acrylicOrder))
            @method('PUT')
        @endif
        @csrf
        <div class="p-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Main Form - col-lg-8 --}}
                <div class="lg:col-span-8 space-y-6">
                    {{-- Customer Info Section Card --}}
                    <div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm mb-2">
                        <div class="flex items-center gap-2 border-b border-neutral-100 pb-4 mb-5">
                            <iconify-icon icon="lucide:user" class="text-xl text-primary-500"></iconify-icon>
                            <h6 class="font-bold text-base text-neutral-800 m-0">Thông tin khách hàng & Đơn hàng</h6>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Mã đơn hàng</label>
                                <input type="text" id="order-code-input" class="form-control rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-600" readonly value="{{ isset($acrylicOrder) ? $acrylicOrder->order_code : ($nextOrderCode ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Khách hàng</label>
                                <select name="customer_id" class="form-select rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" onchange="fillCustomerInfo(this.value)">
                                    <option value="">-- Chọn khách hàng --</option>
                                    @foreach(\App\Models\Customer::all() as $customer)
                                    <option value="{{ $customer->id }}" {{ isset($acrylicOrder) && $acrylicOrder?->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->customer_code }} - {{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group md:col-span-2">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Tên khách hàng <span class="text-danger-500">*</span></label>
                                <input type="text" name="customer_name" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập tên khách hàng" required value="{{ old('customer_name', $acrylicOrder?->customer_name ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Loại đơn</label>
                                @if(isset($acrylicOrder))
                                    <select id="order-type-select" class="form-select rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-neutral-500 font-medium" disabled>
                                        <option value="acrylic" {{ $acrylicOrder->type == 'acrylic' ? 'selected' : '' }}>Acrylic</option>
                                        <option value="min_late" {{ $acrylicOrder->type == 'min_late' ? 'selected' : '' }}>Min Late</option>
                                        <option value="glass" {{ $acrylicOrder->type == 'glass' ? 'selected' : '' }}>Glass</option>
                                    </select>
                                    <input type="hidden" name="type" value="{{ $acrylicOrder->type }}">
                                @else
                                    <select name="type" id="order-type-select" class="form-select rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" onchange="switchOrderType(this.value)">
                                        <option value="">-- Chọn --</option>
                                        <option value="acrylic" {{ old('type', 'acrylic') == 'acrylic' ? 'selected' : '' }}>Acrylic</option>
                                        <option value="min_late" {{ old('type') == 'min_late' ? 'selected' : '' }}>Min Late</option>
                                        <option value="glass" {{ old('type') == 'glass' ? 'selected' : '' }}>Glass</option>
                                    </select>
                                @endif
                            </div>
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập số điện thoại" value="{{ old('phone', $acrylicOrder?->phone ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Ngày giờ chốt đơn</label>
                                <input type="datetime-local" id="order_date" name="order_date" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" value="{{ old('order_date', (isset($acrylicOrder) && $acrylicOrder->order_date) ? ($acrylicOrder->order_date instanceof \Carbon\Carbon ? $acrylicOrder->order_date->format('Y-m-d\TH:i') : date('Y-m-d\TH:i', strtotime($acrylicOrder->order_date))) : '') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Số ngày phải giao</label>
                                <input type="number" id="delivery_days" name="delivery_days" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập số ngày" min="0" value="{{ old('delivery_days', $acrylicOrder?->delivery_days ?? '') }}">
                            </div>
                            <div class="form-group md:col-span-2">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Hạn đơn (tự động tính)</label>
                                <input type="date" id="deadline" name="deadline" class="form-control rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-medium text-neutral-700" readonly value="{{ old('deadline', (isset($acrylicOrder) && $acrylicOrder->deadline) ? ($acrylicOrder->deadline instanceof \Carbon\Carbon ? $acrylicOrder->deadline->format('Y-m-d') : date('Y-m-d', strtotime($acrylicOrder->deadline))) : '') }}">
                            </div>
                            <div class="form-group md:col-span-2">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Địa chỉ</label>
                                <textarea name="address" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập địa chỉ" rows="2">{{ old('address', $acrylicOrder?->address ?? '') }}</textarea>
                            </div>
                            <div class="form-group md:col-span-2">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Ghi chú đơn hàng</label>
                                <textarea name="notes" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập ghi chú" rows="2">{{ old('notes', $acrylicOrder?->notes ?? '') }}</textarea>
                            </div>
                            @if(isset($acrylicOrder))
                            <div class="form-group md:col-span-2">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Trạng thái</label>
                                <select name="status" class="form-select rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500">
                                    <option value="pending" {{ $acrylicOrder->status === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                                    <option value="processing" {{ $acrylicOrder->status === 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                                    <option value="completed" {{ $acrylicOrder->status === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                    <option value="cancelled" {{ $acrylicOrder->status === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                                </select>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Order Summary & Attachments - col-lg-4 --}}
                <div class="lg:col-span-4">
                    <div class="sticky top-6 space-y-6">
                        {{-- Summary Card --}}
                        <div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm mb-2">
                            <div class="flex items-center gap-2 border-b border-neutral-100 pb-4 mb-4">
                                <iconify-icon icon="lucide:receipt-text" class="text-xl text-primary-500"></iconify-icon>
                                <h6 class="font-bold text-base text-neutral-800 m-0">Tóm tắt đơn hàng</h6>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-neutral-500 font-medium">Số lượng sản phẩm:</span>
                                    <span class="font-semibold text-neutral-800" id="total-items">0</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-neutral-500 font-medium">Tổng tiền hàng:</span>
                                    <span class="font-semibold text-neutral-800" id="total-amount">0 VNĐ</span>
                                </div>
                                <hr class="border-neutral-100">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-bold text-neutral-800">Tổng thanh toán:</span>
                                    <span class="text-lg font-extrabold text-primary-600" id="grand-total">0 VNĐ</span>
                                </div>
                            </div>
                        </div>

                        {{-- Attachments Card --}}
                        <div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm">
                            <div class="flex items-center gap-2 border-b border-neutral-100 pb-4 mb-4">
                                <iconify-icon icon="lucide:paperclip" class="text-xl text-primary-500"></iconify-icon>
                                <h6 class="font-bold text-base text-neutral-800 m-0">Tệp tin đính kèm</h6>
                            </div>
                            <div class="form-group mb-4">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Tải lên hình ảnh</label>
                                <div class="relative flex items-center justify-center border-2 border-dashed border-neutral-300 rounded-xl p-4 hover:bg-neutral-50 hover:border-primary-400 transition-colors cursor-pointer group">
                                    <input type="file" id="attachments-input" name="attachments[]" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" multiple accept="image/*">
                                    <div class="text-center pointer-events-none">
                                        <iconify-icon icon="lucide:image-plus" class="text-3xl text-neutral-400 group-hover:text-primary-500 transition-colors mb-2"></iconify-icon>
                                        <p class="text-xs font-semibold text-neutral-600">Chọn hoặc thả ảnh tại đây</p>
                                        <p class="text-[10px] text-neutral-400 mt-1">Hỗ trợ JPG, PNG — tối đa 2MB/ảnh</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Preview Container for newly selected images --}}
                            <div class="mt-4 hidden" id="new-attachments-preview-container">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Hình ảnh mới chọn</label>
                                <div class="flex flex-wrap gap-2" id="new-attachments-preview"></div>
                            </div>

                            @if(isset($acrylicOrder) && $acrylicOrder->attachments)
                            <div class="mt-4">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Hình ảnh đã tải</label>
                                <div class="flex flex-wrap gap-2" id="existing-attachments">
                                    @foreach(json_decode($acrylicOrder->attachments, true) ?? [] as $index => $image)
                                    <div class="relative group w-16 h-16">
                                        <img src="{{ route('orders.image', ['filename' => basename($image)]) }}" class="w-16 h-16 object-cover rounded-lg border border-neutral-200 shadow-sm transition-transform group-hover:scale-105">
                                        <button type="button" onclick="deleteAttachment('{{ $index }}', '{{ $image }}')" class="absolute bg-danger-100 hover:bg-danger-200 text-danger-600 transition-colors w-6 h-6 flex justify-center items-center rounded-full shadow-sm z-10" style="top: -6px; right: -6px;" title="Xóa ảnh">
                                            <iconify-icon icon="lucide:trash-2" class="text-xs"></iconify-icon>
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

            {{-- Dynamic Items Container - Full width (col-12) --}}
            <div class="mt-6 space-y-6">
                <div id="items-section-acrylic" class="type-items-section">
                    @include('orders.acrylic')
                </div>
                <div id="items-section-min_late" class="type-items-section hidden">
                    @include('orders.min_late')
                </div>
                <div id="items-section-glass" class="type-items-section hidden">
                    @include('orders.glass')
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-100 bg-neutral-50/50 flex items-center justify-end gap-3 rounded-b-xl">
            <a href="{{ route('orders.index') }}" class="btn btn-outline-neutral px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">Quay lại</a>
            <button type="submit" class="btn btn-primary px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all">{{ isset($acrylicOrder) ? 'Cập nhật đơn hàng' : 'Lưu đơn hàng' }}</button>
        </div>
    </form>
</div>

<script>
function switchOrderType(type) {
    if (!type) return;
    
    const targetSection = type;
    
    // Hide all sections and disable inputs inside them
    document.querySelectorAll('.type-items-section').forEach(section => {
        section.classList.add('hidden');
        section.querySelectorAll('input, select, textarea').forEach(input => {
            input.disabled = true;
        });
    });

    // Show selected section and enable inputs inside it
    const activeSection = document.getElementById(`items-section-${targetSection}`);
    if (activeSection) {
        activeSection.classList.remove('hidden');
        activeSection.querySelectorAll('input, select, textarea').forEach(input => {
            input.disabled = false;
        });
        
        // If there are no rows in the active section, add one by default
        const rows = activeSection.querySelectorAll('.order-item-row');
        if (rows.length === 0) {
            if (targetSection === 'acrylic' && typeof addOrderSupply === 'function') {
                addOrderSupply();
            } else if (targetSection === 'min_late' && typeof addMinLateOrderSupply === 'function') {
                addMinLateOrderSupply();
            } else if (targetSection === 'glass' && typeof addGlassOrderSupply === 'function') {
                addGlassOrderSupply();
            }
        }
    }
    
    updateOrderSummary();
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

function updateOrderSummary() {
    const typeSelect = document.getElementById('order-type-select');
    const orderType = typeSelect ? typeSelect.value : 'acrylic';
    
    let totalItems = 0;
    let totalAmount = 0;
    
    if (orderType === 'min_late') {
        // Calculate items quantity from supplies.items
        const rows = document.querySelectorAll('.order-item-row');
        rows.forEach(row => {
            const qtyInput = row.querySelector('input[name*="quantity"]');
            if (qtyInput && !qtyInput.disabled) {
                totalItems += parseFloat(qtyInput.value) || 0;
            }
        });
        
        // Calculate total amount from payment details
        const detailRows = document.querySelectorAll('.payment-detail-row');
        detailRows.forEach(row => {
            const totalInput = row.querySelector('input[name*="total"]');
            if (totalInput && !totalInput.disabled) {
                totalAmount += parseFloat(totalInput.value) || 0;
            }
        });
    } else {
        const rows = document.querySelectorAll('.order-item-row');
        rows.forEach(row => {
            const qtyInput = row.querySelector('input[name*="quantity"]');
            const priceInput = row.querySelector('input[name*="total_price"]');
            if (qtyInput && !qtyInput.disabled) {
                const quantity = parseFloat(qtyInput.value) || 0;
                const totalPrice = parseFloat(priceInput ? priceInput.value : 0) || 0;
                totalItems += quantity;
                totalAmount += totalPrice;
            }
        });
    }
    
    const totalItemsEl = document.getElementById('total-items');
    const totalAmountEl = document.getElementById('total-amount');
    const grandTotalEl = document.getElementById('grand-total');
    
    if (totalItemsEl) totalItemsEl.textContent = totalItems;
    if (totalAmountEl) totalAmountEl.textContent = Math.round(totalAmount).toLocaleString('vi-VN') + ' VNĐ';
    if (grandTotalEl) grandTotalEl.textContent = Math.round(totalAmount).toLocaleString('vi-VN') + ' VNĐ';
}

function deleteAttachment(index, imagePath) {
    if (confirm('Bạn có chắc muốn xóa hình ảnh này?')) {
        // Remove from DOM
        const container = document.getElementById('existing-attachments');
        const imageDivs = container.querySelectorAll('.relative');
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

document.addEventListener('DOMContentLoaded', function() {
    // Initial active section check based on selected value
    const typeSelect = document.getElementById('order-type-select');
    if (typeSelect) {
        const initialType = typeSelect.value || 'acrylic';
        switchOrderType(initialType);
    }

    // Initialize tom-select for existing product code selects
    if (typeof TomSelect !== 'undefined') {
        document.querySelectorAll('.tom-select-product').forEach(function(element) {
            new TomSelect(element, {
                allowEmptyOption: true,
                placeholder: '-- Chọn --',
            });
        });
    }

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
                            imgWrapper.className = 'relative w-20 h-20';
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

    // Auto-calculate deadline from order_date + delivery_days
    const orderDateInput = document.getElementById('order_date');
    const deliveryDaysInput = document.getElementById('delivery_days');
    const deadlineInput = document.getElementById('deadline');

    function calculateDeadline() {
        if (orderDateInput.value && deliveryDaysInput.value) {
            const orderDate = new Date(orderDateInput.value);
            const deliveryDays = parseInt(deliveryDaysInput.value) || 0;
            const deadline = new Date(orderDate);
            deadline.setDate(deadline.getDate() + deliveryDays);
            deadlineInput.value = deadline.toISOString().split('T')[0];
        }
    }

    if (orderDateInput && deliveryDaysInput && deadlineInput) {
        orderDateInput.addEventListener('change', calculateDeadline);
        deliveryDaysInput.addEventListener('input', calculateDeadline);
    }
    
    // Clipboard paste handler (Ctrl+V) for images
    document.addEventListener('paste', function(event) {
        const clipboardItems = (event.clipboardData || window.clipboardData).items;
        let imagePasted = false;
        const newFiles = [];

        for (let i = 0; i < clipboardItems.length; i++) {
            const item = clipboardItems[i];
            if (item.type.indexOf('image') !== -1) {
                const file = item.getAsFile();
                if (file) {
                    newFiles.push(file);
                    imagePasted = true;
                }
            }
        }

        if (imagePasted) {
            event.preventDefault(); // Stop default pasting behavior
            
            const attachmentsInput = document.getElementById('attachments-input');
            if (attachmentsInput) {
                const dataTransfer = new DataTransfer();
                
                // Keep pre-existing files in the file input
                if (attachmentsInput.files) {
                    Array.from(attachmentsInput.files).forEach(file => {
                        dataTransfer.items.add(file);
                    });
                }
                
                // Add new pasted files
                newFiles.forEach(file => {
                    const filename = `pasted_image_${Date.now()}_${Math.random().toString(36).substring(2, 7)}.png`;
                    const renamedFile = new File([file], filename, { type: file.type });
                    dataTransfer.items.add(renamedFile);
                });
                
                attachmentsInput.files = dataTransfer.files;
                attachmentsInput.dispatchEvent(new Event('change'));
            }
        }
    });
    
    updateOrderSummary();
});
</script>
