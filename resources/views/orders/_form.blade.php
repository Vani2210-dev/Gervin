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
                                    @foreach(\App\Models\Customer::orderBy('name')->get() as $customer)
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
                            <div class="form-group">
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

                                    {{-- Modal Popup xem ảnh - chỉ hiện ảnh trần + nút X --}}
                                    <x-modal name="modal-existing-attachment-{{ $index }}" maxWidth="2xl" :hasBackdrop="false" :transparent="true">
                                        <div class="relative modal-drag-handle cursor-grab">
                                            {{-- Nút X đóng ở góc phải trên --}}
                                            <button type="button" onclick="closeModal('modal-existing-attachment-{{ $index }}')" class="absolute -top-3 -right-3 z-10 w-7 h-7 bg-white rounded-full shadow-lg flex items-center justify-center text-neutral-500 hover:text-red-500 hover:bg-red-50 transition-colors border border-neutral-200">
                                                <iconify-icon icon="lucide:x" class="text-sm"></iconify-icon>
                                            </button>
                                            {{-- Ảnh trần - không wrapper --}}
                                            <img src="{{ route('orders.image', ['filename' => basename($image)]) }}" class="modal-image-viewer rounded-lg shadow-2xl block" style="max-width:672px; max-height:80vh; object-fit:contain;">
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
            <button type="button" onclick="previewOrder()" class="btn bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all flex items-center gap-1.5">
                <iconify-icon icon="lucide:eye" class="text-base"></iconify-icon> Xem trước
            </button>
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
    } else if (customerId && !/^\d+$/.test(customerId)) {
        document.querySelector('input[name="customer_name"]').value = customerId;
        document.querySelector('input[name="phone"]').value = '';
        document.querySelector('textarea[name="address"]').value = '';
    }
    @endif
}

// === XEM TRƯỚC ĐƠN HÀNG ===
function closePreviewOrder() {
    const modal = document.getElementById('preview-order-modal');
    if (modal) modal.remove();
    document.body.style.overflow = '';
}

function previewOrder() {
    const form = document.getElementById('order-form');
    if (!form) return;

    // Thu thập thông tin từ form
    const getVal = (selector) => {
        const el = form.querySelector(selector);
        if (!el) return '';
        if (el.tagName === 'SELECT') {
            return el.options[el.selectedIndex]?.text || el.value || '';
        }
        return el.value || '';
    };

    const orderCode = document.getElementById('order-code-input')?.value || '';
    const orderType = getVal('[name="type"]');
    const typeLabels = { acrylic: 'Acrylic', glass: 'Glass', min_late: 'Min Late' };
    const typeLabel = typeLabels[orderType] || orderType;
    const customerName = getVal('[name="customer_name"]');
    const phone = getVal('[name="phone"]');
    const orderDate = getVal('[name="order_date"]');
    const deliveryDays = getVal('[name="delivery_days"]');
    const deadline = getVal('[name="deadline"]');
    const address = form.querySelector('[name="address"]')?.value || '';
    const notes = form.querySelector('[name="notes"]')?.value || '';
    const customerPolicy = form.querySelector('[name="customer_policy"]')?.value || '';
    const statusEl = form.querySelector('[name="status"]');
    const statusText = statusEl ? statusEl.options[statusEl.selectedIndex]?.text : '';

    // Định dạng ngày giờ
    const formatDate = (val) => {
        if (!val) return '—';
        try {
            const d = new Date(val);
            return d.toLocaleDateString('vi-VN', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
        } catch { return val; }
    };
    const formatDateOnly = (val) => {
        if (!val) return '—';
        try {
            const d = new Date(val);
            return d.toLocaleDateString('vi-VN', { day:'2-digit', month:'2-digit', year:'numeric' });
        } catch { return val; }
    };
    const formatMoney = (val) => {
        const n = parseFloat(val) || 0;
        return n.toLocaleString('vi-VN') + ' đ';
    };

    // Thu thập tóm tắt từ sidebar
    const totalItems = document.getElementById('total-items')?.textContent || '0';
    const totalSheets = document.getElementById('total-sheets')?.textContent || '0';
    const totalArea = document.getElementById('total-area')?.textContent || '0 m²';
    const totalAmount = document.getElementById('total-amount')?.textContent || '0 VNĐ';
    const grandTotal = document.getElementById('grand-total')?.textContent || '0 VNĐ';

    // Thu thập vật tư & sản phẩm
    let suppliesHTML = '';
    const supplyRows = document.querySelectorAll('.order-supply-row');
    supplyRows.forEach((supplyRow, sIdx) => {
        // Kiểm tra supply có bị disabled không
        const firstInput = supplyRow.querySelector('input:not([type="hidden"])');
        if (firstInput && firstInput.disabled) return;

        const supplyCodeEl = supplyRow.querySelector('[name*="order_supply_code"]');
        const supplyCode = supplyCodeEl ? (supplyCodeEl.tomselect ? supplyCodeEl.tomselect.getItem(supplyCodeEl.tomselect.getValue())?.textContent : supplyCodeEl.options?.[supplyCodeEl.selectedIndex]?.text) : '';
        const supplyName = supplyRow.querySelector('[name*="supply_name"]')?.value || '';
        const supplyQty = supplyRow.querySelector('[name*="[quantity]"]:not([name*="items"])')?.value || '';

        // Lấy headers từ bảng
        const table = supplyRow.querySelector('table');
        if (!table) return;
        const headers = [];
        table.querySelectorAll('thead th').forEach(th => {
            headers.push(th.textContent.trim().replace(/\s*\*\s*/g, ''));
        });

        // Lấy dữ liệu từng item row
        let itemsHTML = '';
        const itemRows = supplyRow.querySelectorAll('.order-item-row');
        itemRows.forEach((row, iIdx) => {
            itemsHTML += '<tr class="border-b border-neutral-100 hover:bg-neutral-50/50">';
            // STT
            itemsHTML += `<td class="px-3 py-2 text-center text-xs text-neutral-500 border border-neutral-100">${iIdx + 1}</td>`;
            // Đọc tất cả td (bỏ STT và Hành động)
            const tds = row.querySelectorAll('td');
            tds.forEach((td, tdIdx) => {
                if (tdIdx === 0) return; // Bỏ STT (đã render ở trên)
                if (tdIdx === tds.length - 1) return; // Bỏ cột Hành động

                const input = td.querySelector('input, select, textarea');
                let val = '';
                if (input) {
                    if (input.tagName === 'SELECT') {
                        val = input.options[input.selectedIndex]?.text || '';
                    } else {
                        val = input.value || '';
                    }
                } else {
                    val = td.textContent.trim();
                }

                // Format tiền cho cột đơn giá và thành tiền
                const name = input?.name || '';
                if (name.includes('unit_price') || name.includes('total_price') || name.includes('[total]')) {
                    const num = parseFloat(val);
                    val = !isNaN(num) && num > 0 ? num.toLocaleString('vi-VN') : val;
                }

                itemsHTML += `<td class="px-3 py-2 text-xs text-neutral-700 border border-neutral-100 text-center">${val || '—'}</td>`;
            });
            itemsHTML += '</tr>';
        });

        // Tạo headers (bỏ cột Hành động cuối)
        let headerHTML = '<tr class="bg-primary-50/50">';
        headers.forEach((h, hIdx) => {
            if (hIdx === headers.length - 1) return; // Bỏ cột Hành động
            headerHTML += `<th class="px-3 py-2.5 text-xs font-bold text-neutral-600 uppercase border border-neutral-100 text-center whitespace-nowrap">${h}</th>`;
        });
        headerHTML += '</tr>';

        suppliesHTML += `
            <div class="mb-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                        <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                    </div>
                    <span class="font-bold text-sm text-neutral-800">${supplyCode || 'Vật tư ' + (sIdx + 1)}</span>
                    ${supplyName ? `<span class="text-xs text-neutral-500">— ${supplyName}</span>` : ''}
                    ${supplyQty ? `<span class="text-xs bg-primary-50 text-primary-600 px-2 py-0.5 rounded-full font-semibold">SL: ${supplyQty}</span>` : ''}
                </div>
                <div class="overflow-x-auto rounded-lg border border-neutral-200">
                    <table class="w-full">
                        <thead>${headerHTML}</thead>
                        <tbody>${itemsHTML || '<tr><td colspan="20" class="text-center text-xs text-neutral-400 py-4">Chưa có sản phẩm</td></tr>'}</tbody>
                    </table>
                </div>
            </div>
        `;
    });

    // Tạo info row helper
    const infoRow = (label, value, icon) => {
        if (!value || value === '—') return '';
        return `
            <div class="flex items-start gap-3 py-2">
                <iconify-icon icon="${icon}" class="text-base text-neutral-400 mt-0.5 flex-shrink-0"></iconify-icon>
                <div>
                    <div class="text-[11px] font-semibold text-neutral-400 uppercase tracking-wider">${label}</div>
                    <div class="text-sm font-medium text-neutral-800 mt-0.5">${value}</div>
                </div>
            </div>
        `;
    };

    // Tạo modal HTML
    const modalHTML = `
        <div id="preview-order-modal" style="position:fixed; inset:0; z-index:99999999; display:flex; align-items:center; justify-content:center;">
            <div style="position:absolute; inset:0; background:rgba(0,0,0,0.5);" onclick="closePreviewOrder()"></div>
            <div class="bg-white flex flex-col" style="position:relative; width:100%; height:100%; z-index:1;">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-50 rounded-xl">
                            <iconify-icon icon="lucide:eye" class="text-xl text-emerald-500"></iconify-icon>
                        </div>
                        <div>
                            <h5 class="font-bold text-base text-neutral-800 m-0">Xem trước đơn hàng</h5>
                            <p class="text-xs text-neutral-400 m-0 mt-0.5">${orderCode} • ${typeLabel}</p>
                        </div>
                    </div>
                    <button type="button" onclick="closePreviewOrder()" class="w-8 h-8 rounded-lg hover:bg-neutral-100 flex items-center justify-center text-neutral-400 hover:text-neutral-600 transition-colors">
                        <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                    </button>
                </div>

                <!-- Body (scrollable) -->
                <div class="flex-1 overflow-y-auto p-6 space-y-5" style="min-height:0;">
                    <!-- Thông tin khách hàng & đơn hàng -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
                            <div class="flex items-center gap-2 mb-3 pb-3 border-b border-neutral-100">
                                <iconify-icon icon="lucide:user" class="text-lg text-primary-500"></iconify-icon>
                                <h6 class="font-bold text-sm text-neutral-800 m-0">Thông tin khách hàng</h6>
                            </div>
                            ${infoRow('Tên khách hàng', customerName, 'lucide:user')}
                            ${infoRow('Số điện thoại', phone, 'lucide:phone')}
                            ${infoRow('Địa chỉ', address, 'lucide:map-pin')}
                        </div>
                        <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
                            <div class="flex items-center gap-2 mb-3 pb-3 border-b border-neutral-100">
                                <iconify-icon icon="lucide:file-text" class="text-lg text-primary-500"></iconify-icon>
                                <h6 class="font-bold text-sm text-neutral-800 m-0">Thông tin đơn hàng</h6>
                            </div>
                            ${infoRow('Mã đơn hàng', orderCode, 'lucide:hash')}
                            ${infoRow('Loại đơn', typeLabel, 'lucide:tag')}
                            ${infoRow('Ngày chốt đơn', formatDate(orderDate), 'lucide:calendar')}
                            ${infoRow('Số ngày giao', deliveryDays ? deliveryDays + ' ngày' : '', 'lucide:truck')}
                            ${infoRow('Hạn đơn', formatDateOnly(deadline), 'lucide:clock')}
                            ${statusText ? infoRow('Trạng thái', statusText, 'lucide:activity') : ''}
                        </div>
                    </div>

                    <!-- Ghi chú -->
                    ${(notes || customerPolicy) ? `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        ${notes ? `
                        <div class="bg-amber-50/50 border border-amber-200 rounded-xl p-5">
                            <div class="flex items-center gap-2 mb-2">
                                <iconify-icon icon="lucide:sticky-note" class="text-base text-amber-500"></iconify-icon>
                                <span class="font-bold text-xs text-amber-700 uppercase">Ghi chú đơn hàng</span>
                            </div>
                            <p class="text-sm text-neutral-700 whitespace-pre-wrap m-0">${notes}</p>
                        </div>` : ''}
                        ${customerPolicy ? `
                        <div class="bg-blue-50/50 border border-blue-200 rounded-xl p-5">
                            <div class="flex items-center gap-2 mb-2">
                                <iconify-icon icon="lucide:shield" class="text-base text-blue-500"></iconify-icon>
                                <span class="font-bold text-xs text-blue-700 uppercase">Chính sách KH</span>
                            </div>
                            <p class="text-sm text-neutral-700 whitespace-pre-wrap m-0">${customerPolicy}</p>
                        </div>` : ''}
                    </div>` : ''}

                    <!-- Vật tư & Sản phẩm -->
                    ${suppliesHTML ? `
                    <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-neutral-100">
                            <iconify-icon icon="lucide:package-open" class="text-lg text-primary-500"></iconify-icon>
                            <h6 class="font-bold text-sm text-neutral-800 m-0">Danh sách Vật tư & Sản phẩm</h6>
                        </div>
                        ${suppliesHTML}
                    </div>` : ''}

                    <!-- Tóm tắt -->
                    <div class="bg-gradient-to-r from-primary-50 to-emerald-50 border border-primary-200 rounded-xl p-5">
                        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-primary-100">
                            <iconify-icon icon="lucide:receipt-text" class="text-lg text-primary-500"></iconify-icon>
                            <h6 class="font-bold text-sm text-neutral-800 m-0">Tóm tắt đơn hàng</h6>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center p-3 bg-white/70 rounded-lg">
                                <div class="text-xs text-neutral-500 font-medium">Số sản phẩm</div>
                                <div class="text-lg font-bold text-neutral-800 mt-1">${totalItems}</div>
                            </div>
                            <div class="text-center p-3 bg-white/70 rounded-lg">
                                <div class="text-xs text-neutral-500 font-medium">Tổng số tấm</div>
                                <div class="text-lg font-bold text-neutral-800 mt-1">${totalSheets}</div>
                            </div>
                            <div class="text-center p-3 bg-white/70 rounded-lg">
                                <div class="text-xs text-neutral-500 font-medium">Tổng diện tích</div>
                                <div class="text-lg font-bold text-blue-600 mt-1">${totalArea}</div>
                            </div>
                            <div class="text-center p-3 bg-white/70 rounded-lg">
                                <div class="text-xs text-neutral-500 font-medium">Tổng tiền hàng</div>
                                <div class="text-lg font-bold text-neutral-800 mt-1">${totalAmount}</div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-primary-100 flex items-center justify-between">
                            <span class="font-bold text-sm text-neutral-800">Tổng thanh toán:</span>
                            <span class="text-2xl font-extrabold text-primary-600">${grandTotal}</span>
                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-neutral-200 flex-shrink-0 bg-neutral-50/50">
                    <button type="button" onclick="closePreviewOrder()" class="btn btn-outline-neutral px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">Đóng</button>
                    <button type="button" onclick="closePreviewOrder(); if (typeof closeOrderSuppliesPopup === 'function') closeOrderSuppliesPopup(); document.getElementById('order-form').requestSubmit();" class="btn btn-primary px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center gap-1.5">
                        <iconify-icon icon="lucide:save" class="text-base"></iconify-icon>
                        {{ isset($acrylicOrder) && !$isDraftCreate ? 'Cập nhật đơn hàng' : 'Lưu đơn hàng' }}
                    </button>
                </div>
            </div>
        </div>
    `;

    // Xóa modal cũ nếu có, thêm modal mới
    const oldModal = document.getElementById('preview-order-modal');
    if (oldModal) oldModal.remove();
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    document.body.style.overflow = 'hidden';

    // Đóng bằng Esc
    const escHandler = function(e) {
        if (e.key === 'Escape') {
            closePreviewOrder();
            document.removeEventListener('keydown', escHandler);
        }
    };
    document.addEventListener('keydown', escHandler);
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
    const roundedTotalAmount = Math.round(totalAmount / 1000) * 1000;
    if (totalAmountEl) totalAmountEl.textContent = roundedTotalAmount.toLocaleString('vi-VN') + ' VNĐ';
    if (grandTotalEl) grandTotalEl.textContent = roundedTotalAmount.toLocaleString('vi-VN') + ' VNĐ';
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

// Đồng bộ tệp đính kèm vào cuối panel khi fullscreen
function syncAttachmentsToFullscreen(panel, show) {
    // Xóa clone cũ nếu có
    const existingClone = panel.querySelector('.fullscreen-attachments-clone');
    if (existingClone) existingClone.remove();

    if (!show) return;

    const body = panel.querySelector('.order-supplies-body');
    if (!body) return;

    // Tìm phần tệp đính kèm gốc từ sidebar
    const originalCard = document.querySelector('.attachments-sticky-card');
    if (!originalCard) return;

    // Lấy danh sách ảnh đã tải (existing)
    const existingSection = originalCard.querySelector('#existing-attachments');
    // Lấy danh sách ảnh mới chọn (new)
    const newSection = originalCard.querySelector('#new-attachments-preview');

    // Kiểm tra có ảnh nào không
    const hasExisting = existingSection && existingSection.children.length > 0;
    const hasNew = newSection && newSection.children.length > 0;
    if (!hasExisting && !hasNew) return;

    // Tạo container clone
    const cloneWrapper = document.createElement('div');
    cloneWrapper.className = 'fullscreen-attachments-clone border-t border-neutral-200 mt-6 pt-5 px-2 pb-4';
    cloneWrapper.innerHTML = `
        <div class="flex items-center gap-2 mb-4">
            <iconify-icon icon="lucide:paperclip" class="text-xl text-primary-500"></iconify-icon>
            <h6 class="font-bold text-base text-neutral-800 m-0">Tệp tin đính kèm</h6>
        </div>
        <div class="flex flex-wrap gap-3" id="fullscreen-attachments-grid"></div>
    `;
    const grid = cloneWrapper.querySelector('#fullscreen-attachments-grid');

    // Thêm ảnh đã tải - hiển thị dạng thumbnail có thể click mở popup
    if (hasExisting) {
        existingSection.querySelectorAll(':scope > div').forEach(function(item) {
            const img = item.querySelector('img');
            const btn = item.querySelector('button[onclick*="openModal"]') || item.querySelector('[onclick*="openModal"]');
            if (!img) return;

            const thumb = document.createElement('div');
            thumb.className = 'relative group cursor-pointer';
            const onclickAttr = btn ? btn.getAttribute('onclick') : (img.getAttribute('onclick') || '');
            
            const originalDeleteBtn = item.querySelector('button[onclick*="deleteAttachment"]') || item.querySelector('button.remove-new-attachment');
            
            thumb.innerHTML = `
                <img src="${img.src}" class="w-20 h-20 rounded-lg object-cover border border-neutral-200 shadow-sm hover:shadow-md hover:border-primary-400 transition-all" onclick="${onclickAttr}">
            `;
            if (originalDeleteBtn) {
                const delClone = document.createElement('button');
                delClone.type = 'button';
                delClone.className = 'bg-white rounded-full text-danger-500 shadow-sm border border-neutral-200 transition-colors hover:bg-danger-50';
                delClone.style.cssText = 'position: absolute; top: -8px; right: -8px; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; z-index: 10;';
                delClone.title = 'Xóa ảnh';
                delClone.innerHTML = '<iconify-icon icon="lucide:trash-2" class="text-xs"></iconify-icon>';
                delClone.onclick = function(e) {
                    e.stopPropagation();
                    originalDeleteBtn.click();
                };
                thumb.appendChild(delClone);
            }
            grid.appendChild(thumb);
        });
    }

    // Thêm ảnh mới chọn
    if (hasNew) {
        newSection.querySelectorAll(':scope > div').forEach(function(item) {
            const img = item.querySelector('img');
            const btn = item.querySelector('button[onclick*="openModal"]') || item.querySelector('[onclick*="openModal"]');
            if (!img) return;

            const thumb = document.createElement('div');
            thumb.className = 'relative group cursor-pointer';
            const onclickAttr = btn ? btn.getAttribute('onclick') : (img.getAttribute('onclick') || '');
            
            const originalDeleteBtn = item.querySelector('button[onclick*="deleteAttachment"]') || item.querySelector('button.remove-new-attachment');
            
            thumb.innerHTML = `
                <img src="${img.src}" class="w-20 h-20 rounded-lg object-cover border-2 border-primary-300 shadow-sm hover:shadow-md hover:border-primary-500 transition-all" onclick="${onclickAttr}">
            `;
            if (originalDeleteBtn) {
                const delClone = document.createElement('button');
                delClone.type = 'button';
                delClone.className = 'bg-white rounded-full text-danger-500 shadow-sm border border-neutral-200 transition-colors hover:bg-danger-50';
                delClone.style.cssText = 'position: absolute; top: -8px; right: -8px; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; z-index: 10;';
                delClone.title = 'Xóa ảnh';
                delClone.innerHTML = '<iconify-icon icon="lucide:trash-2" class="text-xs"></iconify-icon>';
                delClone.onclick = function(e) {
                    e.stopPropagation();
                    originalDeleteBtn.click();
                };
                thumb.appendChild(delClone);
            }
            grid.appendChild(thumb);
        });
    }

    body.appendChild(cloneWrapper);
}

// Hàm cập nhật lại tệp đính kèm trong tất cả panel fullscreen đang mở
function refreshFullscreenAttachments() {
    document.querySelectorAll('[data-order-supplies-zoom-panel].is-fullscreen').forEach(function(panel) {
        syncAttachmentsToFullscreen(panel, true);
    });
}

// MutationObserver theo dõi thay đổi ảnh gốc → tự động re-sync vào fullscreen
document.addEventListener('DOMContentLoaded', function() {
    const observerConfig = { childList: true, subtree: true };
    const observer = new MutationObserver(function() {
        // Debounce: chờ 200ms tránh re-sync quá nhiều lần liên tục
        clearTimeout(window._attachmentSyncTimer);
        window._attachmentSyncTimer = setTimeout(refreshFullscreenAttachments, 200);
    });

    // Theo dõi phần ảnh đã tải
    const existing = document.getElementById('existing-attachments');
    if (existing) observer.observe(existing, observerConfig);

    // Theo dõi phần ảnh mới chọn
    const newPreview = document.getElementById('new-attachments-preview');
    if (newPreview) observer.observe(newPreview, observerConfig);

    // Theo dõi container cha (để bắt cả khi container bị ẩn/hiện)
    const newPreviewContainer = document.getElementById('new-attachments-preview-container');
    if (newPreviewContainer) observer.observe(newPreviewContainer, observerConfig);
});

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
        // writeOrderSuppliesStorageItem(`${ORDER_SUPPLIES_UI_STORAGE_PREFIX}:${scope}:fullscreen`, nextState ? '1' : '0');
    }

    if (!isOpen) {
        const body = panel.querySelector('.order-supplies-body');
        if (body) {
            body.scrollTop = 0;
        }
    }

    // Đồng bộ tệp đính kèm vào panel fullscreen
    syncAttachmentsToFullscreen(panel, nextState);
}

function closeOrderSuppliesPopup() {
    const panel = document.querySelector('[data-order-supplies-zoom-panel].is-fullscreen');
    if (!panel) {
        document.body.classList.remove('order-supplies-popup-open');
        return;
    }

    panel.classList.remove('is-fullscreen');
    document.body.classList.remove('order-supplies-popup-open');

    // Xóa clone tệp đính kèm khi thu nhỏ
    syncAttachmentsToFullscreen(panel, false);

    const button = panel.querySelector('[data-order-supplies-popup-button]');
    updateOrderSuppliesPopupButton(button, false);

    const scope = getOrderSuppliesStorageScope(panel);
    if (scope) {
        // writeOrderSuppliesStorageItem(`${ORDER_SUPPLIES_UI_STORAGE_PREFIX}:${scope}:fullscreen`, '0');
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
    // Disabled restoring fullscreen state from localStorage per user request
    // document.querySelectorAll('[data-order-supplies-zoom-panel]').forEach((panel) => { ... });
}

document.addEventListener('DOMContentLoaded', () => {
    initOrderSuppliesZoom();
    initOrderSuppliesVisibleRows();
    initOrderSuppliesFullscreen();

    // Tự động đóng chế độ toàn màn hình khi lưu/cập nhật đơn hàng
    const form = document.getElementById('order-form');
    if (form) {
        form.addEventListener('submit', () => {
            if (typeof closeOrderSuppliesPopup === 'function') {
                closeOrderSuppliesPopup();
            }
        });
    }
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
        const zIndex = 12 - rowIndex;

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
                maxOptions: null,
                create: true,
                createFilter: function(input) {
                    return input.length > 0;
                }
            });
            customerTomSelect.on('change', function(value) {
                fillCustomerInfo(value);
            });
        }

        // Intercept form submission to clear customer_id if it is not numeric
        const orderForm = document.getElementById('order-form');
        if (orderForm) {
            orderForm.addEventListener('submit', function(e) {
                const custSelect = document.getElementById('customer-select');
                if (custSelect && custSelect.value && !/^\d+$/.test(custSelect.value)) {
                    // It's a typed string, remove the name attribute so it doesn't submit
                    // but the backend uses customer_name which is already filled by fillCustomerInfo
                    custSelect.name = ''; 
                }
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
                            <div data-modal-content style="position:relative; width:fit-content; pointer-events:auto;">
                                <div class="relative modal-drag-handle cursor-grab">
                                    <!-- Nút X đóng ở góc phải trên -->
                                    <button type="button" onclick="closeModal('${modalId}')" class="absolute -top-3 -right-3 z-10 w-7 h-7 bg-white rounded-full shadow-lg flex items-center justify-center text-neutral-500 hover:text-red-500 hover:bg-red-50 transition-colors border border-neutral-200">
                                        <iconify-icon icon="lucide:x" class="text-sm"></iconify-icon>
                                    </button>
                                    <!-- Ảnh trần - không wrapper -->
                                    <img src="${e.target.result}" class="modal-image-viewer rounded-lg shadow-2xl block" style="max-width:672px; max-height:80vh; object-fit:contain;">
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

    // Phím tắt Ctrl+Alt: ẩn/hiện tất cả popup ảnh đang mở
    let hiddenImageModals = []; // Lưu popup đã ẩn để hiện lại
    document.addEventListener('keydown', function(e) {
        // Bắt khi nhấn Ctrl+Alt (không kèm key khác)
        const isToggleShortcut = (e.key === 'Alt' && e.ctrlKey) || (e.key === 'Control' && e.altKey);
        if (!isToggleShortcut) return;

        // Nếu đang có popup bị ẩn → hiện lại
        if (hiddenImageModals.length > 0) {
            hiddenImageModals.forEach(function(modal) {
                modal.style.display = 'block';
            });
            hiddenImageModals = [];
            return;
        }

        // Tìm tất cả popup ảnh đang hiển thị (modal có backdrop=false, chứa .modal-image-viewer)
        const openModals = [];
        document.querySelectorAll('[data-modal]').forEach(function(modal) {
            if (modal.style.display !== 'none' && modal.querySelector('.modal-image-viewer')) {
                openModals.push(modal);
            }
        });

        if (openModals.length === 0) return;

        // Ẩn tất cả popup ảnh
        openModals.forEach(function(modal) {
            modal.style.display = 'none';
            hiddenImageModals.push(modal);
        });
    });

    // Ctrl + lăn chuột để co giãn kích thước ảnh thật sự (thay đổi width)
    document.addEventListener('wheel', function(e) {
        const img = e.target.closest('.modal-image-viewer');
        if (!img || !e.ctrlKey) return;

        // Ngăn trình duyệt zoom trang
        e.preventDefault();

        // Lấy chiều rộng hiện tại (width đang set hoặc kích thước thật trên màn hình)
        let currentWidth = parseInt(img.style.width) || img.offsetWidth;

        // Tăng/giảm 60px mỗi lần lăn
        if (e.deltaY < 0) {
            currentWidth += 60;
        } else {
            currentWidth -= 60;
        }
        // Giới hạn: nhỏ nhất 80px, lớn nhất 90% viewport
        currentWidth = Math.max(80, Math.min(currentWidth, window.innerWidth * 0.9));

        img.style.width = currentWidth + 'px';
        img.style.maxWidth = 'none';
        img.style.maxHeight = 'none';
    }, { passive: false });

    // Double-click để reset kích thước ảnh về mặc định
    document.addEventListener('dblclick', function(e) {
        const img = e.target.closest('.modal-image-viewer');
        if (!img) return;

        img.style.width = '';
        img.style.maxWidth = '672px';
        img.style.maxHeight = '80vh';
    });
    // Khi click vào popup ảnh nào thì đẩy z-index lên cao nhất
    let modalTopZIndex = 10000000;
    document.addEventListener('mousedown', function(e) {
        const modal = e.target.closest('[data-modal]');
        if (!modal) return;
        modalTopZIndex++;
        modal.style.zIndex = modalTopZIndex;
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
                modalBody.removeAttribute('data-drag-x');
                modalBody.removeAttribute('data-drag-y');
            }
            // Reset kích thước ảnh về mặc định khi mở lại modal
            el.querySelectorAll('.modal-image-viewer').forEach(function(img) {
                img.style.width = '';
                img.style.maxWidth = '672px';
                img.style.maxHeight = '80vh';
            });
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


<script>
// Prevent mouse wheel from changing number input values
document.addEventListener('wheel', function(event) {
    if (document.activeElement.type === 'number') {
        document.activeElement.blur();
    }
});
</script>


