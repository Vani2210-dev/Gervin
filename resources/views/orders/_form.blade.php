@php
    $currentOrderType = $orderType ?? ($acrylicOrder->type ?? old('type', 'acrylic'));
    $isDraftCreate = $isDraftCreate ?? false;
    $orderTypeLabels = [
        'acrylic' => 'Acrylic',
        'glass' => 'Glass',
        'min_late' => 'Min Late',
    ];
@endphp
<link rel="stylesheet" href="{{ asset('assets/css/order-form.css') }}?v={{ time() }}">
<div class="card p-0 rounded-xl border-0">
    <div class="card-header border-b border-neutral-200 bg-white py-4 px-6">
        <h5 class="font-semibold text-base">{{ $title ?? 'Tạo đơn hàng' }}</h5>
    </div>
    <form action="{{ $action }}" method="POST" id="order-form" enctype="multipart/form-data">
        @if(isset($acrylicOrder) && !$isDraftCreate)
            @method('PUT')
        @endif
        @csrf
        @if($isDraftCreate && isset($acrylicOrder))
            <input type="hidden" name="draft_order_id" value="{{ $acrylicOrder->id }}">
        @endif
        <div class="p-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Main Form - col-lg-8 --}}
                <div class="lg:col-span-8 space-y-6">
                    {{-- Customer Info Section Card --}}
                    <div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm mb-2 relative pt-8">
                        <div class="absolute -top-3.5 left-6 bg-white px-3 flex items-center gap-2 z-10">
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
                                <select name="customer_id" id="customer-select" class="" onchange="fillCustomerInfo(this.value)">
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
                            <input type="hidden" name="type" value="{{ $currentOrderType }}">
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
                            <div class="form-group md:col-span-2">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Chính sách KH</label>
                                <textarea name="customer_policy" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập chính sách khách hàng" rows="2">{{ old('customer_policy', $acrylicOrder?->customer_policy ?? '') }}</textarea>
                            </div>
                            @if(isset($acrylicOrder) && !$isDraftCreate)
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

                <div class="lg:col-span-4 space-y-6">
                    {{-- Summary Card --}}
                        <div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm mb-2">
                            <div class="flex items-center gap-2 border-b border-neutral-100 pb-4 mb-4">
                                <iconify-icon icon="lucide:receipt-text" class="text-xl text-primary-500"></iconify-icon>
                                <h6 class="font-bold text-base text-neutral-800 m-0">Tóm tắt đơn hàng</h6>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-neutral-500 font-medium">Số sản phẩm:</span>
                                    <span class="font-semibold text-neutral-800" id="total-items">0</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-neutral-500 font-medium">Tổng số tấm:</span>
                                    <span class="font-semibold text-neutral-800" id="total-sheets">0 tấm</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-neutral-500 font-medium">Tổng diện tích:</span>
                                    <span class="font-semibold text-blue-600" id="total-area">0 m²</span>
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
                        <div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm attachments-sticky-card" style="position: -webkit-sticky; position: sticky; top: 96px; z-index: 10;">
                            <div class="flex items-center gap-2 border-b border-neutral-100 pb-4 mb-4">
                                <iconify-icon icon="lucide:paperclip" class="text-xl text-primary-500"></iconify-icon>
                                <h6 class="font-bold text-base text-neutral-800 m-0">Tệp tin đính kèm</h6>
                            </div>
                            <div class="form-group mb-4">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Tải lên hình ảnh</label>
                                <div class="relative flex flex-col items-center justify-center border-2 border-dashed border-neutral-300 rounded-xl py-12 px-4 hover:bg-neutral-50 hover:border-primary-400 transition-colors cursor-pointer group min-h-[220px]">
                                    <input type="file" id="attachments-input" name="attachments[]" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" multiple accept="image/*">
                                    <div class="text-center pointer-events-none">
                                        <iconify-icon icon="lucide:image-plus" class="text-4xl text-neutral-400 group-hover:text-primary-500 transition-colors mb-3"></iconify-icon>
                                        <p class="text-sm font-semibold text-neutral-700">Chọn hoặc thả ảnh tại đây</p>
                                        <p class="text-xs text-neutral-400 mt-1.5">Hỗ trợ JPG, PNG — tối đa 2MB/ảnh</p>
                                        <p class="text-[10px] text-primary-500 font-semibold mt-2 bg-primary-50 px-2 py-0.5 rounded-full inline-block">Hoặc nhấn Ctrl + V để dán ảnh</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Preview Container for newly selected images --}}
                            <div class="mt-4 hidden" id="new-attachments-preview-container">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Hình ảnh mới chọn</label>
                                <div class="flex flex-col gap-2.5" id="new-attachments-preview"></div>
                            </div>

                            @if(isset($acrylicOrder) && $acrylicOrder->attachments)
                            <div class="mt-4">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Hình ảnh đã tải</label>
                                <div class="flex flex-col gap-2.5" id="existing-attachments">
                                    @foreach(json_decode($acrylicOrder->attachments, true) ?? [] as $index => $image)
                                    <div class="flex items-center justify-between p-2 border border-neutral-100 rounded-xl bg-neutral-50/50 hover:bg-neutral-50 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ route('orders.image', ['filename' => basename($image)]) }}" class="w-12 h-12 rounded-lg object-cover border border-neutral-200 shadow-sm cursor-pointer" onclick="openModal('modal-existing-attachment-{{ $index }}')">
                                            <div>
                                                <p class="text-xs font-semibold text-neutral-700 truncate max-w-[120px]">{{ basename($image) }}</p>
                                                <button type="button" onclick="openModal('modal-existing-attachment-{{ $index }}')" class="text-[11px] text-primary-500 hover:text-primary-700 font-bold flex items-center gap-1 mt-0.5">
                                                    <iconify-icon icon="lucide:eye" class="text-sm"></iconify-icon> Xem chi tiết
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" onclick="deleteAttachment('{{ $index }}', '{{ $image }}')" class="text-neutral-400 hover:text-danger-500 transition-colors p-1.5" title="Xóa ảnh">
                                            <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                                        </button>
                                    </div>

                                    {{-- Modal Popup for this image --}}
                                    <x-modal name="modal-existing-attachment-{{ $index }}" maxWidth="2xl" :hasBackdrop="false">
                                        <div class="p-5 relative">
                                            <div class="flex items-center justify-between border-b border-neutral-100 pb-3 mb-4 cursor-move modal-drag-handle">
                                                <h6 class="font-bold text-sm text-neutral-800 m-0 select-none">{{ basename($image) }}</h6>
                                                <button type="button" onclick="closeModal('modal-existing-attachment-{{ $index }}')" class="text-neutral-400 hover:text-neutral-600 text-xl leading-none">&times;</button>
                                            </div>
                                            <div class="relative overflow-hidden rounded-lg attachment-wrapper bg-neutral-50 flex items-center justify-center p-1 border border-neutral-100 shadow-xs" style="cursor: zoom-in;">
                                                <img src="{{ route('orders.image', ['filename' => basename($image)]) }}" class="w-full object-contain max-h-[70vh] rounded-md">
                                            </div>
                                        </div>
                                    </x-modal>
                                    @endforeach
                                </div>
                                <input type="hidden" name="delete_attachments" id="delete-attachments" value="">
                            </div>
                            @endif
                        </div>
                </div>
            </div>

            {{-- Bottom Section: Full Width Bảng danh sách vật tư --}}
            <div class="w-full mt-6">
                <div class="space-y-6">
                    @if($currentOrderType === 'acrylic')
                        @include('orders.acrylic')
                    @elseif($currentOrderType === 'min_late')
                        @include('orders.min_late')
                    @elseif($currentOrderType === 'glass')
                        @include('orders.glass')
                    @endif
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-100 bg-neutral-50/50 flex items-center justify-end gap-3 rounded-b-xl">
            <a href="{{ route('orders.index') }}" class="btn btn-outline-neutral px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">Quay lại</a>
            <button type="submit" class="btn btn-primary px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all">{{ isset($acrylicOrder) && !$isDraftCreate ? 'Cập nhật đơn hàng' : 'Lưu đơn hàng' }}</button>
        </div>
    </form>
</div>

<script>
function switchOrderType(type) {
    if (!type) return;
    closeOrderSuppliesPopup();

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

        // Khi tab đơn hàng vừa hiện ra, áp lại giới hạn số dòng nếu người dùng đã chọn
        const panel = activeSection.closest('[data-order-supplies-zoom-panel]');
        if (panel && typeof applyOrderSuppliesVisibleRows === 'function') {
            applyOrderSuppliesVisibleRows(panel, panel.dataset.orderSuppliesVisibleRows || ORDER_SUPPLIES_VISIBLE_ROWS_DEFAULT);
        }

        if (typeof syncOrderStickyHeaders === 'function') {
            syncOrderStickyHeaders(activeSection);
        }
        
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
    const orderType = @json($currentOrderType);
    
    let totalItems = 0;
    let totalSheets = 0;
    let totalArea = 0;
    let totalAmount = 0;
    
    if (orderType === 'min_late') {
        // Calculate items quantity from supplies.items
        const rows = document.querySelectorAll('.order-item-row');
        rows.forEach(row => {
            const qtyInput = row.querySelector('input[name*="quantity"]');
            if (qtyInput && !qtyInput.disabled) {
                const qty = parseFloat(qtyInput.value) || 0;
                totalItems++;
                totalSheets += qty;
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
            const heightInput = row.querySelector('input[name*="[height]"]');
            const widthInput = row.querySelector('input[name*="[width]"]');
            if (qtyInput && !qtyInput.disabled) {
                const quantity = parseFloat(qtyInput.value) || 0;
                const totalPrice = parseFloat(priceInput ? priceInput.value : 0) || 0;
                const height = parseFloat(heightInput ? heightInput.value : 0) || 0;
                const width = parseFloat(widthInput ? widthInput.value : 0) || 0;
                totalItems++;
                totalSheets += quantity;
                if (height > 0 && width > 0) {
                    totalArea += (height * width * quantity) / 1000000;
                }
                totalAmount += totalPrice;
            }
        });
    }
    
    const totalItemsEl = document.getElementById('total-items');
    const totalSheetsEl = document.getElementById('total-sheets');
    const totalAreaEl = document.getElementById('total-area');
    const totalAmountEl = document.getElementById('total-amount');
    const grandTotalEl = document.getElementById('grand-total');
    
    if (totalItemsEl) totalItemsEl.textContent = totalItems;
    if (totalSheetsEl) totalSheetsEl.textContent = totalSheets + ' tấm';
    if (totalAreaEl) totalAreaEl.textContent = totalArea.toFixed(3) + ' m²';
    if (totalAmountEl) totalAmountEl.textContent = Math.round(totalAmount).toLocaleString('vi-VN') + ' VNĐ';
    if (grandTotalEl) grandTotalEl.textContent = Math.round(totalAmount).toLocaleString('vi-VN') + ' VNĐ';
}

function getOrderSuppliesPanel(element) {
    if (!element) return null;
    return element.closest('[data-order-supplies-zoom-panel]');
}

function updateOrderSuppliesPopupButton(button, isOpen) {
    if (!button) return;

    const iconEl = button.querySelector('[data-order-supplies-popup-icon]');
    const labelEl = button.querySelector('[data-order-supplies-popup-label]');

    button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

    if (iconEl) {
        iconEl.setAttribute('icon', isOpen ? 'lucide:minimize-2' : 'lucide:maximize-2');
    }

    if (labelEl) {
        labelEl.textContent = isOpen ? 'Thu nhỏ' : 'Phóng to';
    }
}

function toggleOrderSuppliesPopup(button) {
    const panel = getOrderSuppliesPanel(button);
    if (!panel) return;

    const isOpen = panel.classList.contains('is-fullscreen');
    const nextState = !isOpen;
    panel.classList.toggle('is-fullscreen', nextState);
    document.body.classList.toggle('order-supplies-popup-open', nextState);
    updateOrderSuppliesPopupButton(button, nextState);

    const scope = getOrderSuppliesStorageScope(panel);
    if (scope) {
        writeOrderSuppliesStorageItem(`${ORDER_SUPPLIES_UI_STORAGE_PREFIX}:${scope}:fullscreen`, nextState ? '1' : '0');
    }

    if (!isOpen) {
        const body = panel.querySelector('.order-supplies-body');
        if (body) {
            body.scrollTop = 0;
        }
    }
}

function closeOrderSuppliesPopup() {
    const panel = document.querySelector('[data-order-supplies-zoom-panel].is-fullscreen');
    if (!panel) {
        document.body.classList.remove('order-supplies-popup-open');
        return;
    }

    panel.classList.remove('is-fullscreen');
    document.body.classList.remove('order-supplies-popup-open');

    const button = panel.querySelector('[data-order-supplies-popup-button]');
    updateOrderSuppliesPopupButton(button, false);

    const scope = getOrderSuppliesStorageScope(panel);
    if (scope) {
        writeOrderSuppliesStorageItem(`${ORDER_SUPPLIES_UI_STORAGE_PREFIX}:${scope}:fullscreen`, '0');
    }
}

function applyOrderSuppliesZoom(panel, zoomValue) {
    if (!panel) return;

    const nextZoom = Math.min(200, Math.max(50, Number(zoomValue) || 100));
    const zoomLabel = panel.querySelector('[data-order-supplies-zoom-label]');
    const zoomRange = panel.querySelector('[data-order-supplies-zoom-range]');
    const zoomWraps = panel.querySelectorAll('[data-order-supplies-table-zoom-wrap]');
    const previousZoom = panel.dataset.orderSuppliesZoom || '';

    panel.dataset.orderSuppliesZoom = String(nextZoom);

    zoomWraps.forEach((wrap) => {
        // Dùng zoom giống file mẫu để sticky header/cột vẫn bám đúng khi phóng to
        wrap.style.zoom = String(nextZoom / 100);
    });

    // Cập nhật lại chiều cao hiển thị của bảng sau khi đổi zoom
    applyOrderSuppliesVisibleRows(panel, panel.dataset.orderSuppliesVisibleRows || ORDER_SUPPLIES_VISIBLE_ROWS_DEFAULT);
    syncOrderStickyHeaders(panel);

    if (previousZoom !== String(nextZoom)) {
        persistOrderSuppliesZoom(panel, nextZoom);
    }

    if (zoomLabel) {
        zoomLabel.textContent = `${nextZoom}%`;
    }

    if (zoomRange && zoomRange.value !== String(nextZoom)) {
        zoomRange.value = String(nextZoom);
    }
}

function changeOrderSuppliesZoom(button, delta) {
    const panel = getOrderSuppliesPanel(button);
    if (!panel) return;

    const currentZoom = Number(panel.dataset.orderSuppliesZoom || 100);
    applyOrderSuppliesZoom(panel, currentZoom + delta);
}

function syncOrderSuppliesZoom(input) {
    const panel = getOrderSuppliesPanel(input);
    if (!panel) return;

    applyOrderSuppliesZoom(panel, input.value);
}

// Lưu cấu hình giao diện của bảng vật tư/sản phẩm theo từng loại đơn.
const ORDER_SUPPLIES_UI_STORAGE_PREFIX = 'gervin:order-supplies-ui';
const ORDER_SUPPLIES_VISIBLE_ROWS_DEFAULT = '5';
const ORDER_SUPPLIES_VISIBLE_ROWS_VALUES = ['5', '10', '25', '50', '100'];

function normalizeOrderSuppliesVisibleRowsValue(value) {
    const nextValue = String(value || ORDER_SUPPLIES_VISIBLE_ROWS_DEFAULT);

    // Dữ liệu cũ nếu không khớp lựa chọn mới sẽ tự quay về 5 dòng.
    return ORDER_SUPPLIES_VISIBLE_ROWS_VALUES.includes(nextValue) ? nextValue : ORDER_SUPPLIES_VISIBLE_ROWS_DEFAULT;
}

function getOrderSuppliesLocalStorage() {
    try {
        return window.localStorage;
    } catch (error) {
        return null;
    }
}

function readOrderSuppliesStorageItem(key) {
    if (!key) return null;

    const storage = getOrderSuppliesLocalStorage();
    if (!storage) return null;

    try {
        return storage.getItem(key);
    } catch (error) {
        return null;
    }
}

function writeOrderSuppliesStorageItem(key, value) {
    if (!key) return;

    const storage = getOrderSuppliesLocalStorage();
    if (!storage) return;

    try {
        storage.setItem(key, value);
    } catch (error) {
        // Bỏ qua khi trình duyệt chặn localStorage hoặc đầy dung lượng.
    }
}

function getOrderSuppliesStorageScope(panel) {
    return panel ? (panel.dataset.orderSuppliesStorageKey || '') : '';
}

function getOrderSuppliesVisibleRowsStorageKey(panel) {
    const scope = getOrderSuppliesStorageScope(panel);
    return scope ? `${ORDER_SUPPLIES_UI_STORAGE_PREFIX}:${scope}:visible-rows` : null;
}

function isOrderSuppliesVisibleRowsDisabled(panel) {
    return panel && panel.dataset.orderSuppliesVisibleRowsDisabled === '1';
}

function getOrderSuppliesZoomStorageKey(panel) {
    const scope = getOrderSuppliesStorageScope(panel);
    return scope ? `${ORDER_SUPPLIES_UI_STORAGE_PREFIX}:${scope}:zoom` : null;
}

function getOrderColumnResizeStorageKey(groupKey) {
    return groupKey ? `${ORDER_SUPPLIES_UI_STORAGE_PREFIX}:column-widths:${groupKey}` : null;
}

function persistOrderSuppliesVisibleRows(panel, visibleRowsValue) {
    const storageKey = getOrderSuppliesVisibleRowsStorageKey(panel);
    if (!storageKey) return;

    writeOrderSuppliesStorageItem(storageKey, normalizeOrderSuppliesVisibleRowsValue(visibleRowsValue));
}

function persistOrderSuppliesZoom(panel, zoomValue) {
    const storageKey = getOrderSuppliesZoomStorageKey(panel);
    if (!storageKey) return;

    writeOrderSuppliesStorageItem(storageKey, String(zoomValue || 100));
}

function applyOrderSuppliesVisibleRows(panel, visibleRowsValue) {
    if (!panel) return;
    if (isOrderSuppliesVisibleRowsDisabled(panel)) return;

    const nextValue = normalizeOrderSuppliesVisibleRowsValue(visibleRowsValue);
    const rowLimit = Math.max(1, Number(nextValue) || Number(ORDER_SUPPLIES_VISIBLE_ROWS_DEFAULT));
    const select = panel.querySelector('[data-order-supplies-visible-rows-select]');
    const scrollWrappers = panel.querySelectorAll('[data-order-supplies-table-scroll]');
    const isVisible = panel.getClientRects().length > 0;
    const previousValue = panel.dataset.orderSuppliesVisibleRows || '';

    panel.dataset.orderSuppliesVisibleRows = nextValue;

    if (select && select.value !== nextValue) {
        select.value = nextValue;
    }

    if (previousValue !== nextValue) {
        persistOrderSuppliesVisibleRows(panel, nextValue);
    }

    if (!isVisible) {
        return;
    }

    scrollWrappers.forEach((scrollWrapper) => {
        const table = scrollWrapper.querySelector('table');
        if (!table) return;

        const headerHeight = table.tHead ? table.tHead.getBoundingClientRect().height : 0;
        const sampleRow = table.tBodies && table.tBodies.length > 0 ? table.tBodies[0].querySelector('tr') : null;
        const rowHeight = sampleRow ? sampleRow.getBoundingClientRect().height : 42;
        const nextMaxHeight = Math.ceil(headerHeight + (rowHeight * rowLimit) + 2);

        // Giới hạn số dòng nhìn thấy, phần dư sẽ cuộn trong khung bảng
        scrollWrapper.style.maxHeight = `${nextMaxHeight}px`;
        scrollWrapper.style.overflowY = 'auto';
    });

    syncOrderStickyHeaders(panel);
}

function syncOrderSuppliesVisibleRows(input) {
    const panel = getOrderSuppliesPanel(input);
    if (!panel) return;

    applyOrderSuppliesVisibleRows(panel, input.value);
}

function initOrderSuppliesVisibleRows() {
    document.querySelectorAll('[data-order-supplies-zoom-panel]').forEach((panel) => {
        if (isOrderSuppliesVisibleRowsDisabled(panel)) {
            return;
        }

        const select = panel.querySelector('[data-order-supplies-visible-rows-select]');
        const storedValue = readOrderSuppliesStorageItem(getOrderSuppliesVisibleRowsStorageKey(panel));
        const initialValue = normalizeOrderSuppliesVisibleRowsValue(storedValue || (select ? select.value : panel.dataset.orderSuppliesVisibleRows));
        applyOrderSuppliesVisibleRows(panel, initialValue);

        if (panel.dataset.orderSuppliesVisibleRowsReady === '1') {
            return;
        }

        panel.dataset.orderSuppliesVisibleRowsReady = '1';

        if (select) {
            select.addEventListener('change', () => {
                syncOrderSuppliesVisibleRows(select);
            });
        }
    });
}

function initOrderSuppliesZoom() {
    document.querySelectorAll('[data-order-supplies-zoom-panel]').forEach((panel) => {
        const storedValue = readOrderSuppliesStorageItem(getOrderSuppliesZoomStorageKey(panel));
        const initialValue = storedValue || panel.dataset.orderSuppliesZoom || 100;
        applyOrderSuppliesZoom(panel, initialValue);

        if (panel.dataset.orderSuppliesZoomReady === '1') {
            return;
        }

        panel.dataset.orderSuppliesZoomReady = '1';

        // Ctrl + lăn chuột chỉ đổi zoom của đúng bảng đang trỏ vào.
        panel.addEventListener('wheel', (event) => {
            if (!event.ctrlKey) return;
            event.preventDefault();

            const direction = event.deltaY < 0 ? 1 : -1;
            const currentZoom = Number(panel.dataset.orderSuppliesZoom || 100);
            applyOrderSuppliesZoom(panel, currentZoom + direction * 10);
        }, { passive: false });
    });
}

function initOrderSuppliesFullscreen() {
    document.querySelectorAll('[data-order-supplies-zoom-panel]').forEach((panel) => {
        const scope = getOrderSuppliesStorageScope(panel);
        if (!scope) return;

        const isFullscreenStored = readOrderSuppliesStorageItem(`${ORDER_SUPPLIES_UI_STORAGE_PREFIX}:${scope}:fullscreen`) === '1';
        if (isFullscreenStored) {
            panel.classList.add('is-fullscreen');
            document.body.classList.add('order-supplies-popup-open');
            const button = panel.querySelector('[data-order-supplies-popup-button]');
            updateOrderSuppliesPopupButton(button, true);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initOrderSuppliesZoom();
    initOrderSuppliesVisibleRows();
    initOrderSuppliesFullscreen();
});

// Co giãn cột bằng data attribute để không đụng vào name/value/event của input.
const ORDER_COLUMN_RESIZE_TABLE_SELECTOR = [
    '#order-supplies-container .order-supply-row table',
    '#glass-supplies-container .order-supply-row table',
    '#min-late-supplies-container .order-supply-row table',
    'table[data-order-resize-group="min_late_payment"]',
].join(', ');
const ORDER_COLUMN_RESIZE_MIN_WIDTH = 48;
let orderColumnResizeTableIndex = 0;
let orderColumnResizeObserver = null;
const orderColumnResizeWidths = new Map();
let orderStickyHeaderTableIndex = 0;
const orderStickyHeaderRules = new Map();

function getOrderStickyHeaderStyleElement() {
    let styleElement = document.getElementById('order-sticky-header-styles');

    if (!styleElement) {
        styleElement = document.createElement('style');
        styleElement.id = 'order-sticky-header-styles';
        document.head.appendChild(styleElement);
    }

    return styleElement;
}

function renderOrderStickyHeaderStyles() {
    getOrderStickyHeaderStyleElement().textContent = Array.from(orderStickyHeaderRules.values()).join('\n');
}

function getOrderStickyHeaderTableId(table) {
    if (!table.dataset.orderStickyHeaderTableId) {
        orderStickyHeaderTableIndex += 1;
        table.dataset.orderStickyHeaderTableId = `order-sticky-header-table-${orderStickyHeaderTableIndex}`;
    }

    return table.dataset.orderStickyHeaderTableId;
}

function getOrderSuppliesTableZoomValue(table) {
    const zoomWrap = table.closest('[data-order-supplies-table-zoom-wrap]');
    const zoomValue = zoomWrap ? parseFloat(zoomWrap.style.zoom || window.getComputedStyle(zoomWrap).zoom || '1') : 1;

    return Number.isFinite(zoomValue) && zoomValue > 0 ? zoomValue : 1;
}

function syncOrderStickyHeaderTable(table) {
    if (!table || !table.tHead) return;

    const tableId = getOrderStickyHeaderTableId(table);
    const rows = Array.from(table.tHead.rows);
    const zoom = getOrderSuppliesTableZoomValue(table);
    const rules = [];
    let topOffset = -1;

    rows.forEach((row, rowIndex) => {
        const measuredHeight = row.getBoundingClientRect().height / zoom;
        const rowHeight = Number.isFinite(measuredHeight) && measuredHeight > 0 ? measuredHeight : row.offsetHeight;
        const roundedTop = Number(topOffset.toFixed(2));
        const zIndex = 70 - rowIndex;

        // Tính top cho từng hàng header để sticky không đè lên nhau trong khung cuộn của bảng.
        rules.push(`[data-order-sticky-header-table-id="${tableId}"] thead tr:nth-child(${rowIndex + 1}) > th { top: ${roundedTop}px !important; z-index: ${zIndex} !important; }`);

        topOffset += rowHeight || 0;
    });

    orderStickyHeaderRules.set(tableId, rules.join('\n'));
    renderOrderStickyHeaderStyles();
}

function syncOrderStickyHeaders(root = document) {
    getOrderColumnResizeTables(root).forEach((table) => {
        syncOrderStickyHeaderTable(table);
    });
}

function getOrderColumnResizeStyleElement() {
    let styleElement = document.getElementById('order-column-resize-styles');

    if (!styleElement) {
        styleElement = document.createElement('style');
        styleElement.id = 'order-column-resize-styles';
        document.head.appendChild(styleElement);
    }

    return styleElement;
}

function getOrderColumnResizeTableId(table) {
    if (!table.dataset.orderResizeTableId) {
        orderColumnResizeTableIndex += 1;
        table.dataset.orderResizeTableId = `order-resize-table-${orderColumnResizeTableIndex}`;
    }

    return table.dataset.orderResizeTableId;
}

function getOrderColumnResizeGroupKey(table) {
    if (!table) return null;

    if (table.dataset.orderResizeGroup) {
        return table.dataset.orderResizeGroup;
    }

    // Gom các bảng cùng loại để co giãn đồng bộ giữa bảng số 1, 2, 3...
    const container = table.closest('#order-supplies-container, #glass-supplies-container, #min-late-supplies-container');
    if (!container) return null;

    let groupKey = null;

    if (container.id === 'order-supplies-container') {
        groupKey = 'acrylic';
    } else if (container.id === 'glass-supplies-container') {
        groupKey = 'glass';
    } else if (container.id === 'min-late-supplies-container') {
        groupKey = 'min_late';
    }

    if (groupKey) {
        table.dataset.orderResizeGroup = groupKey;
    }

    return groupKey;
}

function renderOrderColumnResizeStyles() {
    const rules = [];

    orderColumnResizeWidths.forEach((width, key) => {
        const [groupKey, columnIndex] = key.split(':');
        rules.push(`[data-order-resize-group="${groupKey}"] [data-order-resize-col="${columnIndex}"] { width: ${width}px !important; min-width: ${width}px !important; max-width: ${width}px !important; }`);
    });

    getOrderColumnResizeStyleElement().textContent = rules.join('\n');
}

function persistOrderColumnResizeGroup(groupKey) {
    const storageKey = getOrderColumnResizeStorageKey(groupKey);
    if (!storageKey) return;

    const widths = {};

    orderColumnResizeWidths.forEach((width, key) => {
        const [storedGroupKey, columnIndex] = key.split(':');
        if (storedGroupKey !== groupKey) {
            return;
        }

        widths[columnIndex] = width;
    });

    writeOrderSuppliesStorageItem(storageKey, JSON.stringify(widths));
}

function loadOrderColumnResizeWidthsFromStorage() {
    // Nạp lại độ rộng cột đã lưu để áp cho mọi bảng cùng loại ngay khi mở trang.
    document.querySelectorAll('[data-order-supplies-storage-key]').forEach((panel) => {
        const groupKey = panel.dataset.orderSuppliesStorageKey;
        const storageKey = getOrderColumnResizeStorageKey(groupKey);
        const storedValue = readOrderSuppliesStorageItem(storageKey);

        if (!groupKey || !storedValue) {
            return;
        }

        try {
            const widths = JSON.parse(storedValue);
            if (!widths || typeof widths !== 'object') {
                return;
            }

            Object.entries(widths).forEach(([columnIndex, width]) => {
                const nextWidth = Number(width);
                if (!Number.isFinite(nextWidth) || nextWidth < ORDER_COLUMN_RESIZE_MIN_WIDTH) {
                    return;
                }

                orderColumnResizeWidths.set(`${groupKey}:${columnIndex}`, nextWidth);
            });
        } catch (error) {
            // Dữ liệu cũ hỏng thì bỏ qua và cho bảng quay về mặc định.
        }
    });

    renderOrderColumnResizeStyles();
}

function getOrderColumnResizeTables(root = document) {
    const tables = [];

    if (root.matches && root.matches(ORDER_COLUMN_RESIZE_TABLE_SELECTOR)) {
        tables.push(root);
    }

    if (root.querySelectorAll) {
        root.querySelectorAll(ORDER_COLUMN_RESIZE_TABLE_SELECTOR).forEach((table) => {
            tables.push(table);
        });
    }

    return tables;
}

function mapOrderColumnResizeHeader(table) {
    const thead = table.tHead;
    const resizableHeaders = [];
    const occupiedColumns = [];
    let columnCount = 0;

    if (!thead) {
        return { resizableHeaders, columnCount };
    }

    Array.from(thead.rows).forEach((row, rowIndex) => {
        let columnIndex = 0;

        // Map header có rowspan/colspan về chỉ số cột thật của tbody.
        Array.from(row.cells).forEach((cell) => {
            while (occupiedColumns[columnIndex] > rowIndex) {
                columnIndex += 1;
            }

            const colspan = Math.max(1, cell.colSpan || 1);
            const rowspan = Math.max(1, cell.rowSpan || 1);

            cell.dataset.orderResizeStartCol = String(columnIndex);
            cell.dataset.orderResizeTargetCol = String(columnIndex + colspan - 1);

            if (colspan === 1) {
                cell.dataset.orderResizeCol = String(columnIndex);
            } else {
                delete cell.dataset.orderResizeCol;
            }

            resizableHeaders.push(cell);

            for (let offset = 0; offset < colspan; offset += 1) {
                occupiedColumns[columnIndex + offset] = rowIndex + rowspan;
            }

            columnCount = Math.max(columnCount, columnIndex + colspan);
            columnIndex += colspan;
        });
    });

    return { resizableHeaders, columnCount };
}

function ensureOrderColumnResizeColgroup(table, columnCount) {
    let colgroup = table.querySelector('colgroup[data-order-resize-colgroup]');

    if (!colgroup || colgroup.parentElement !== table) {
        colgroup = document.createElement('colgroup');
        colgroup.dataset.orderResizeColgroup = '1';
        table.insertBefore(colgroup, table.firstElementChild);
    }

    while (colgroup.children.length < columnCount) {
        colgroup.appendChild(document.createElement('col'));
    }

    while (colgroup.children.length > columnCount) {
        colgroup.lastElementChild.remove();
    }

    Array.from(colgroup.children).forEach((col, index) => {
        col.dataset.orderResizeCol = String(index);
    });
}

function tagOrderColumnResizeBody(table) {
    Array.from(table.tBodies).forEach((tbody) => {
        Array.from(tbody.rows).forEach((row) => {
            let columnIndex = 0;

            Array.from(row.cells).forEach((cell) => {
                const colspan = Math.max(1, cell.colSpan || 1);

                if (colspan === 1) {
                    cell.dataset.orderResizeCol = String(columnIndex);
                } else {
                    delete cell.dataset.orderResizeCol;
                }

                columnIndex += colspan;
            });
        });
    });
}

function getOrderColumnCurrentWidth(table, columnIndex) {
    const groupKey = getOrderColumnResizeGroupKey(table);
    if (!groupKey) {
        return null;
    }

    const widthKey = `${groupKey}:${columnIndex}`;

    if (orderColumnResizeWidths.has(widthKey)) {
        return orderColumnResizeWidths.get(widthKey);
    }

    const referenceCell = table.querySelector(`[data-order-resize-col="${columnIndex}"]`);
    if (!referenceCell) {
        return null;
    }

    const zoomWrap = table.closest('[data-order-supplies-table-zoom-wrap]');
    const zoomValue = zoomWrap ? parseFloat(zoomWrap.style.zoom || window.getComputedStyle(zoomWrap).zoom || '1') : 1;
    const zoom = Number.isFinite(zoomValue) && zoomValue > 0 ? zoomValue : 1;

    return referenceCell.getBoundingClientRect().width / zoom;
}

function startOrderColumnResize(event) {
    if (event.button !== undefined && event.button !== 0) return;

    event.preventDefault();
    event.stopPropagation();

    const handle = event.currentTarget;
    const headerCell = handle.closest('th');
    const table = headerCell ? headerCell.closest('table') : null;

    if (!headerCell || !table) return;

    syncOrderColumnResizeTable(table);

    const groupKey = getOrderColumnResizeGroupKey(table);
    if (!groupKey) return;

    const columnIndex = Number(headerCell.dataset.orderResizeTargetCol || headerCell.dataset.orderResizeCol);
    if (!Number.isFinite(columnIndex)) return;

    const widthKey = `${groupKey}:${columnIndex}`;
    const zoomWrap = table.closest('[data-order-supplies-table-zoom-wrap]');
    const zoomValue = zoomWrap ? parseFloat(zoomWrap.style.zoom || window.getComputedStyle(zoomWrap).zoom || '1') : 1;
    const zoom = Number.isFinite(zoomValue) && zoomValue > 0 ? zoomValue : 1;
    const startX = event.clientX;
    const startWidth = getOrderColumnCurrentWidth(table, columnIndex) || headerCell.getBoundingClientRect().width / zoom;

    document.body.classList.add('order-column-resizing');

    const onPointerMove = (moveEvent) => {
        const nextWidth = Math.max(ORDER_COLUMN_RESIZE_MIN_WIDTH, Math.round(startWidth + ((moveEvent.clientX - startX) / zoom)));
        orderColumnResizeWidths.set(widthKey, nextWidth);
        renderOrderColumnResizeStyles();
    };

    const stopResize = () => {
        document.body.classList.remove('order-column-resizing');
        document.removeEventListener('pointermove', onPointerMove);
        document.removeEventListener('pointerup', stopResize);
        document.removeEventListener('pointercancel', stopResize);
        persistOrderColumnResizeGroup(groupKey);
    };

    document.addEventListener('pointermove', onPointerMove);
    document.addEventListener('pointerup', stopResize);
    document.addEventListener('pointercancel', stopResize);
}

function ensureOrderColumnResizeHandle(headerCell) {
    headerCell.classList.add('order-column-resizable-th');

    const existingHandle = Array.from(headerCell.children).find((child) => child.classList.contains('resize-handle-col'));
    if (existingHandle) {
        if (existingHandle.dataset.orderResizeBound !== '1') {
            existingHandle.dataset.orderResizeBound = '1';
            existingHandle.addEventListener('pointerdown', startOrderColumnResize);
        }
        return;
    }

    const handle = document.createElement('div');
    handle.className = 'resize-handle-col';
    handle.setAttribute('aria-hidden', 'true');
    handle.dataset.orderResizeBound = '1';
    handle.addEventListener('pointerdown', startOrderColumnResize);
    headerCell.appendChild(handle);
}

function syncOrderColumnResizeTable(table) {
    const groupKey = getOrderColumnResizeGroupKey(table);
    if (!groupKey) return;

    const { resizableHeaders, columnCount } = mapOrderColumnResizeHeader(table);
    ensureOrderColumnResizeColgroup(table, columnCount);
    tagOrderColumnResizeBody(table);

    resizableHeaders.forEach((headerCell) => {
        ensureOrderColumnResizeHandle(headerCell);
    });

    syncOrderStickyHeaderTable(table);

    table.dataset.orderResizeReady = '1';
}

function initOrderColumnResize(root = document) {
    getOrderColumnResizeTables(root).forEach((table) => {
        syncOrderColumnResizeTable(table);
    });
}

function observeOrderColumnResizeTables() {
    if (orderColumnResizeObserver) return;

    const containers = [
        document.getElementById('order-supplies-container'),
        document.getElementById('glass-supplies-container'),
        document.getElementById('min-late-supplies-container'),
        document.getElementById('payment-details-container'),
    ].filter(Boolean);

    orderColumnResizeObserver = new MutationObserver((mutations) => {
        const hasNewNodes = mutations.some((mutation) => mutation.addedNodes.length > 0);
        if (!hasNewNodes) return;

        // Dòng/vật tư mới sinh bằng JS cũng cần được gắn lại chỉ số cột.
        window.requestAnimationFrame(() => {
            initOrderColumnResize(document);
        });
    });

    containers.forEach((container) => {
        orderColumnResizeObserver.observe(container, {
            childList: true,
            subtree: true,
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    loadOrderColumnResizeWidthsFromStorage();
    initOrderColumnResize(document);
    observeOrderColumnResizeTables();
});

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

// Fallback global openModal and closeModal functions in case no x-modal is rendered by Blade on this page
window.openModal = window.openModal || function(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = 'block';
    if (el.getAttribute('data-has-backdrop') !== 'false') {
        document.body.style.overflow = 'hidden';
    }
};

window.closeModal = window.closeModal || function(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = 'none';
    if (el.getAttribute('data-has-backdrop') !== 'false') {
        document.body.style.overflow = '';
    }
};

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tom-select for existing product code selects
    if (typeof TomSelect !== 'undefined') {
        const customerSelect = document.getElementById('customer-select');
        if (customerSelect && !customerSelect.tomselect) {
            const customerTomSelect = new TomSelect(customerSelect, {
                allowEmptyOption: true,
                placeholder: '-- Chọn khách hàng --',
                maxOptions: null
            });
            customerTomSelect.on('change', function(value) {
                fillCustomerInfo(value);
            });
        }

        document.querySelectorAll('.tom-select-product').forEach(function(element) {
            if (element.tomselect) return;
            new TomSelect(element, {
                allowEmptyOption: true,
                placeholder: '-- Chọn --',
            });
        });
    }

    // Image preview handler
    let newAttachments = [];
    const attachmentsInput = document.getElementById('attachments-input');
    const previewContainer = document.getElementById('new-attachments-preview-container');
    const previewDiv = document.getElementById('new-attachments-preview');

    function renderNewAttachmentsPreview() {
        if (!previewDiv) return;
        previewDiv.innerHTML = ''; // Clear previous previews
        
        // Clean up any dynamic modals in document.body
        document.querySelectorAll('div[id^="modal-new-attachment-"]').forEach(el => el.remove());

        if (newAttachments.length > 0) {
            previewContainer.classList.remove('hidden');
        } else {
            previewContainer.classList.add('hidden');
        }

        newAttachments.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const modalId = `modal-new-attachment-${index}`;
                    const imgWrapper = document.createElement('div');
                    imgWrapper.className = 'flex items-center justify-between p-2 border border-neutral-100 rounded-xl bg-neutral-50/50 hover:bg-neutral-50 transition-colors';
                    imgWrapper.innerHTML = `
                        <div class="flex items-center gap-3">
                            <img src="${e.target.result}" class="w-12 h-12 rounded-lg object-cover border border-neutral-200 shadow-sm cursor-pointer" onclick="openModal('${modalId}')">
                            <div>
                                <p class="text-xs font-semibold text-neutral-700 truncate max-w-[120px]">${file.name}</p>
                                <button type="button" onclick="openModal('${modalId}')" class="text-[11px] text-primary-500 hover:text-primary-700 font-bold flex items-center gap-1 mt-0.5">
                                    <iconify-icon icon="lucide:eye" class="text-sm"></iconify-icon> Xem chi tiết
                                </button>
                            </div>
                        </div>
                        <button type="button" class="text-neutral-400 hover:text-danger-500 transition-colors p-1.5 remove-new-attachment" data-index="${index}" title="Xóa ảnh">
                            <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                        </button>
                    `;

                    // Create modal element separately and append to document.body to avoid stacking context issues
                    const modalDiv = document.createElement('div');
                    modalDiv.id = modalId;
                    modalDiv.setAttribute('data-modal', '');
                    modalDiv.setAttribute('data-has-backdrop', 'false');
                    modalDiv.style.cssText = 'display:none; position:fixed; inset:0; z-index:9999999 !important; pointer-events:none;';
                    modalDiv.innerHTML = `
                        <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; padding:1rem; pointer-events:none;">
                            <div data-modal-content style="position:relative; background:#fff; border-radius:0.75rem; box-shadow:0 20px 60px rgba(0,0,0,0.3); width:100%; max-width:672px; pointer-events:auto;" class="p-5">
                                <div class="flex items-center justify-between border-b border-neutral-100 pb-3 mb-4 cursor-move modal-drag-handle">
                                    <h6 class="font-bold text-sm text-neutral-800 m-0 select-none">${file.name}</h6>
                                    <button type="button" onclick="closeModal('${modalId}')" class="text-neutral-400 hover:text-neutral-600 text-xl leading-none">&times;</button>
                                </div>
                                <div class="relative overflow-hidden rounded-lg attachment-wrapper bg-neutral-50 flex items-center justify-center p-1 border border-neutral-100 shadow-xs" style="cursor: zoom-in;">
                                    <img src="${e.target.result}" class="w-full object-contain max-h-[70vh] rounded-md">
                                </div>
                                <!-- Resize Handle -->
                                <div class="modal-resize-handle" style="position:absolute; right:4px; bottom:4px; width:16px; height:16px; cursor:se-resize; z-index:100; display:flex; align-items:center; justify-content:center; opacity:0.6; hover:opacity:1;">
                                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9 1L1 9M9 5L5 9M9 8L8 9" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modalDiv);

                    // Add delete handler for newly selected/pasted images
                    imgWrapper.querySelector('.remove-new-attachment').addEventListener('click', function(evt) {
                        evt.preventDefault();
                        const idx = parseInt(this.getAttribute('data-index'));
                        closeModal(`modal-new-attachment-${idx}`);
                        newAttachments.splice(idx, 1);

                        // Sync to file input
                        const dataTransfer = new DataTransfer();
                        newAttachments.forEach(f => dataTransfer.items.add(f));
                        attachmentsInput.files = dataTransfer.files;

                        // Re-render
                        renderNewAttachmentsPreview();
                    });

                    previewDiv.appendChild(imgWrapper);
                }
                reader.readAsDataURL(file);
            }
        });
    }

    if (attachmentsInput) {
        attachmentsInput.addEventListener('change', function() {
            newAttachments = Array.from(this.files || []);
            renderNewAttachmentsPreview();
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

    // Pan & zoom event handlers for attachments
    document.addEventListener('wheel', function(e) {
        const wrapper = e.target.closest('.attachment-wrapper');
        if (!wrapper) return;
        
        const img = wrapper.querySelector('img');
        if (!img) return;

        // Prevent browser scroll
        e.preventDefault();

        let scale = parseFloat(wrapper.getAttribute('data-zoom-scale'));
        if (isNaN(scale)) {
            scale = 1.0;
        }

        if (e.deltaY < 0) {
            scale += 0.2;
        } else {
            scale -= 0.2;
        }
        scale = Math.min(Math.max(scale, 1.0), 6.0); // Limit zoom scale to 6x
        wrapper.setAttribute('data-zoom-scale', scale);

        const rect = wrapper.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const xPercent = (x / rect.width) * 100;
        const yPercent = (y / rect.height) * 100;

        img.style.transformOrigin = `${xPercent}% ${yPercent}%`;
        img.style.transform = `scale(${scale})`;
    }, { passive: false });

    document.addEventListener('mousemove', function(e) {
        const wrapper = e.target.closest('.attachment-wrapper');
        if (!wrapper) return;
        
        const img = wrapper.querySelector('img');
        if (!img) return;

        const rect = wrapper.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const xPercent = (x / rect.width) * 100;
        const yPercent = (y / rect.height) * 100;
        
        img.style.transformOrigin = `${xPercent}% ${yPercent}%`;

        let scale = parseFloat(wrapper.getAttribute('data-zoom-scale'));
        if (isNaN(scale)) {
            scale = 1.0;
            wrapper.setAttribute('data-zoom-scale', scale);
        }
        img.style.transform = `scale(${scale})`;
    });

    document.addEventListener('mouseout', function(e) {
        const wrapper = e.target.closest('.attachment-wrapper');
        if (!wrapper) return;
        
        const relatedTarget = e.relatedTarget;
        if (!relatedTarget || !wrapper.contains(relatedTarget)) {
            const img = wrapper.querySelector('img');
            if (img) {
                img.style.transformOrigin = 'center center';
                img.style.transform = 'scale(1)';
                wrapper.removeAttribute('data-zoom-scale');
            }
        }
    });
    
    // Drag-and-drop modal functionality
    document.addEventListener('mousedown', function(e) {
        const handle = e.target.closest('.modal-drag-handle');
        if (!handle) return;

        // Prevent text selection during drag
        e.preventDefault();

        // Find the relative container of the modal
        const modalBody = handle.closest('[data-modal-content]') || handle.parentElement;
        if (!modalBody) return;

        const startX = e.clientX;
        const startY = e.clientY;

        const currentX = parseFloat(modalBody.getAttribute('data-drag-x')) || 0;
        const currentY = parseFloat(modalBody.getAttribute('data-drag-y')) || 0;

        function onMouseMove(moveEvent) {
            const dx = moveEvent.clientX - startX;
            const dy = moveEvent.clientY - startY;
            const newX = currentX + dx;
            const newY = currentY + dy;

            modalBody.style.transform = `translate(${newX}px, ${newY}px)`;
            modalBody.setAttribute('data-drag-x', newX);
            modalBody.setAttribute('data-drag-y', newY);
        }

        function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        }

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    });

    // Drag-resize modal functionality
    document.addEventListener('mousedown', function(e) {
        const handle = e.target.closest('.modal-resize-handle');
        if (!handle) return;

        e.preventDefault();

        const modalBody = handle.closest('[data-modal-content]') || handle.parentElement;
        if (!modalBody) return;

        const startX = e.clientX;
        const startY = e.clientY;

        modalBody.style.maxWidth = 'none';

        const startWidth = modalBody.offsetWidth;
        const startHeight = modalBody.offsetHeight;

        function onMouseMove(moveEvent) {
            const dx = moveEvent.clientX - startX;
            const dy = moveEvent.clientY - startY;

            const newWidth = Math.max(300, startWidth + dx);
            const newHeight = Math.max(200, startHeight + dy);

            modalBody.style.width = newWidth + 'px';
            modalBody.style.height = newHeight + 'px';
        }

        function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        }

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    });

    // Override openModal to reset translate position when opening
    const originalOpenModal = window.openModal;
    window.openModal = function(id) {
        const el = document.getElementById(id);
        if (typeof originalOpenModal === 'function') {
            originalOpenModal(id);
        } else {
            if (el) {
                el.style.display = 'block';
                if (el.getAttribute('data-has-backdrop') !== 'false') {
                    document.body.style.overflow = 'hidden';
                }
            }
        }
        if (el) {
            const modalBody = el.querySelector('[data-modal-content]');
            if (modalBody) {
                modalBody.style.transform = '';
                modalBody.style.width = '';
                modalBody.style.height = '';
                modalBody.style.maxWidth = '';
                modalBody.removeAttribute('data-drag-x');
                modalBody.removeAttribute('data-drag-y');
            }
        }
    };
    // Move all existing modal elements to document.body to escape local stacking contexts
    document.querySelectorAll('[data-modal]').forEach(modal => {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });

    updateOrderSummary();
});
</script>

<script>
// Auto-scroll focused table input to horizontal center of its scroll container
document.addEventListener('focusin', function(e) {
    const el = e.target;
    if (!el.matches('.order-supply-row table input, .order-supply-row table select, .order-supply-row table textarea')) return;

    const scrollContainer = el.closest('[data-order-supplies-table-scroll]');
    if (!scrollContainer) return;

    // Use requestAnimationFrame to wait for browser to finish focus/layout
    requestAnimationFrame(function() {
        const containerRect = scrollContainer.getBoundingClientRect();
        const elRect = el.getBoundingClientRect();

        // Current offset of element relative to scroll container content
        const elOffsetLeft = elRect.left - containerRect.left + scrollContainer.scrollLeft;
        const targetScrollLeft = elOffsetLeft - (containerRect.width / 2) + (elRect.width / 2);

        scrollContainer.scrollTo({
            left: Math.max(0, targetScrollLeft),
            behavior: 'smooth'
        });
    });
});
</script>

@if($isDraftCreate && isset($acrylicOrder))
<script>
document.addEventListener('DOMContentLoaded', function() {
    let formSaved = false;
    let hasDiscarded = false;

    const form = document.getElementById('order-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!e.defaultPrevented) {
                formSaved = true;
            }
        });
    }

    function discardDraft() {
        if (formSaved || hasDiscarded) return;
        hasDiscarded = true;
        
        const formData = new FormData();
        formData.append('draft_order_id', '{{ $acrylicOrder->id }}');
        
        navigator.sendBeacon('{{ route('orders.discard-draft') }}', formData);
    }

    window.addEventListener('pagehide', discardDraft);
    window.addEventListener('beforeunload', discardDraft);
});
</script>
@endif

