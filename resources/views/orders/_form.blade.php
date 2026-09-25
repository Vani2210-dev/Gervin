@php
    $currentOrderType = $orderType ?? ($acrylicOrder->type ?? old('type', 'acrylic'));
    $isDraftCreate = $isDraftCreate ?? false;
    $orderTypeLabels = [
        'acrylic' => 'Acrylic',
        'glass' => 'Glass',
        'min_late' => 'Min Late',
    ];
    $isEdit = isset($acrylicOrder) && !$isDraftCreate;
    $thisOrderId = $isEdit ? $acrylicOrder->id : null;
    $thisOrderPaid = $isEdit && $acrylicOrder->orderPayments ? $acrylicOrder->orderPayments->sum('amount') : 0;
    $thisOrderOldAmount = $isEdit ? round($acrylicOrder->total_amount, -3) : 0;
    $thisOrderOldStatusWasValid = $isEdit ? !in_array($acrylicOrder->status, ['draft', 'pending', 'cancelled']) : false;
@endphp
<script>
    window.currentOrderId = @json($thisOrderId);
    window.thisOrderPaid = @json($thisOrderPaid);
    window.thisOrderOldAmount = @json($thisOrderOldAmount);
    window.thisOrderOldStatusWasValid = @json($thisOrderOldStatusWasValid);
    window.customerOldDebt = 0;
</script>
<link rel="stylesheet" href="{{ asset('assets/css/order-form.css') }}?v={{ time() }}">
<div class="card p-0 rounded-xl border-0">
    <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex justify-between items-center">
        <h5 class="font-semibold text-base m-0">{{ $title ?? 'Tạo đơn hàng' }}</h5>
        <button type="button" onclick="openModal('modal-shortcuts')" class="btn bg-primary-50 text-primary-600 hover:bg-primary-100 border border-primary-200 px-3 py-1.5 rounded-lg text-sm font-semibold flex items-center gap-1.5 transition-colors hide-on-mobile">
            <iconify-icon icon="lucide:lightbulb" class="text-base"></iconify-icon> Hướng dẫn & Mẹo
        </button>
    </div>
    <form action="{{ $action }}" method="POST" id="order-form" enctype="multipart/form-data">
        @if(isset($acrylicOrder) && !$isDraftCreate)
            @method('PUT')
        @endif
        @csrf
        @if($isDraftCreate && isset($acrylicOrder))
            <input type="hidden" name="draft_order_id" value="{{ $acrylicOrder->id }}">
        @endif
        <input type="hidden" name="supplies_json" id="supplies-json-input">
        <input type="hidden" name="action" id="order-form-action" value="save">
        <div class="p-4">
            @if ($errors->any())
                <div class="mb-4 p-4 rounded-xl border border-danger-200 bg-danger-50 text-danger-600 shadow-sm">
                    <div class="flex items-center gap-2 mb-2 font-bold text-danger-800">
                        <iconify-icon icon="solar:danger-triangle-bold" class="text-xl"></iconify-icon>
                        <span>Lỗi nhập liệu! Vui lòng kiểm tra và sửa lại các trường sau:</span>
                    </div>
                    <ul class="list-disc pl-5 space-y-1 text-xs font-semibold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-3">
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
                                @php
                                    $currentUser = auth()->user();
                                    if ($currentUser && !$currentUser->hasRole('Admin')) {
                                        $userMarketGroupIds = $currentUser->marketGroups()->pluck('market_groups.id');
                                        $allowedCustomers = \App\Models\Customer::whereIn('market_group_id', $userMarketGroupIds)->orderBy('name')->get();
                                    } else {
                                        $allowedCustomers = \App\Models\Customer::orderBy('name')->get();
                                    }

                                    if (isset($acrylicOrder) && $acrylicOrder->customer && !$allowedCustomers->contains('id', $acrylicOrder->customer_id)) {
                                        if ($currentUser && ($currentUser->hasRole('Admin') || $acrylicOrder->customer->isAccessibleBy($currentUser))) {
                                            $allowedCustomers->push($acrylicOrder->customer);
                                        }
                                    }
                                @endphp
                                <select name="customer_id" id="customer-select" class="" onchange="fillCustomerInfo(this.value)">
                                    <option value="">-- Chọn khách hàng --</option>
                                    @foreach($allowedCustomers as $customer)
                                    <option value="{{ $customer->id }}" {{ isset($acrylicOrder) && $acrylicOrder?->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->customer_code }} - {{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group md:col-span-2">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Tên công trình <span class="text-danger-500">*</span></label>
                                <input type="text" name="customer_name" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập tên công trình" required value="{{ old('customer_name', $acrylicOrder?->customer_name ?? '') }}">
                            </div>
                            <input type="hidden" name="type" value="{{ $currentOrderType }}">
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập số điện thoại" value="{{ old('phone', $acrylicOrder?->phone ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Ngày giờ chốt đơn</label>
                                <input type="datetime-local" id="order_date" name="order_date" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" value="{{ old('order_date', (isset($acrylicOrder) && $acrylicOrder->order_date) ? \Carbon\Carbon::parse($acrylicOrder->order_date)->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i') : \Carbon\Carbon::now('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Số ngày phải giao</label>
                                <input type="number" id="delivery_days" name="delivery_days" step="any" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập số ngày" min="0" value="{{ old('delivery_days', $acrylicOrder?->delivery_days ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Hạn đơn (tự động tính)</label>
                                <input type="datetime-local" id="deadline" name="deadline" class="form-control rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-medium text-neutral-700" readonly value="{{ old('deadline', (isset($acrylicOrder) && $acrylicOrder->deadline) ? ($acrylicOrder->deadline instanceof \Carbon\Carbon ? $acrylicOrder->deadline->format('Y-m-d\TH:i') : date('Y-m-d\TH:i', strtotime($acrylicOrder->deadline))) : '') }}">
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
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-6">
                    {{-- Customer Debt Card --}}
                    <div id="customer-debt-card" hidden class="bg-white border border-danger-200 rounded-xl p-6 shadow-sm mb-2 hidden">
                        <div class="flex items-center gap-2 border-b border-danger-100 pb-4 mb-4">
                            <iconify-icon icon="lucide:alert-circle" class="text-xl text-danger-500"></iconify-icon>
                            <h6 class="font-bold text-base text-neutral-800 m-0">Công nợ khách hàng</h6>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm mb-2">
                                <span class="text-neutral-500 font-medium">Công nợ:</span>
                                <input type="number" name="customer_initial_debt" id="customer_initial_debt" class="form-control form-control-sm w-48 text-right rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" value="0">
                            </div>
                        </div>
                    </div>

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
                                <div class="flex justify-between items-center text-sm mt-2">
                                    <span class="text-neutral-500 font-medium flex items-center">Chiết khấu (%):</span>
                                    <input type="number" name="discount_percent" id="discount_percent" value="{{ old('discount_percent', $acrylicOrder->discount_percent ?? 0) }}" class="form-control text-right w-20 h-7 text-sm px-2 py-1" min="0" max="100" step="0.01" oninput="if(typeof updateOrderSummary === 'function') updateOrderSummary();">
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-neutral-500 font-medium">Tiền chiết khấu:</span>
                                    <span class="font-semibold text-danger-600" id="discount-amount">-0 VNĐ</span>
                                </div>
                                <div class="flex justify-between items-center text-sm mt-2">
                                    <span class="text-neutral-500 font-medium flex items-center">VAT (%):</span>
                                    <input type="number" name="vat_percent" id="vat_percent" value="{{ old('vat_percent', $acrylicOrder->vat_percent ?? 0) }}" class="form-control text-right w-20 h-7 text-sm px-2 py-1" min="0" max="100" step="0.01" oninput="if(typeof updateOrderSummary === 'function') updateOrderSummary();">
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-neutral-500 font-medium">Tiền VAT:</span>
                                    <span class="font-semibold text-neutral-800" id="vat-amount">+0 VNĐ</span>
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
        <div class="px-6 py-4 border-t border-neutral-100 bg-neutral-50/50 flex items-center justify-end gap-3 rounded-b-xl flex-wrap">
            <a href="{{ route('orders.index') }}" class="btn btn-outline-neutral px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">Quay lại</a>
            <button type="button" onclick="previewOrder()" class="btn bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all flex items-center gap-1.5">
                <iconify-icon icon="lucide:eye" class="text-base"></iconify-icon> Xem trước
            </button>
            
            {{-- NÚT LƯU NHÁP --}}
            @if(!isset($acrylicOrder) || $isDraftCreate || $acrylicOrder->status === 'draft')
            <button type="submit" name="action" value="draft" class="btn bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center gap-1.5" onclick="document.getElementById('order-form-action').value = 'draft'">
                <iconify-icon icon="lucide:file-text" class="text-base"></iconify-icon> Lưu nháp
            </button>
            @endif

            @if(isset($acrylicOrder) && !$isDraftCreate)
                @if($acrylicOrder->status !== 'cancelled')
                <button type="submit" name="status" value="cancelled" class="btn bg-danger-50 text-danger-600 hover:bg-danger-100 border border-danger-200 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all" onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')">Hủy đơn</button>
                @endif
                @if($acrylicOrder->status === 'pending')
                <button type="submit" name="status" value="transferred" class="btn bg-violet-50 text-violet-600 hover:bg-violet-100 border border-violet-200 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all" onclick="return confirm('Xác nhận chuyển đơn hàng sang sản xuất?')">Chuyển Sản xuất</button>
                @endif
            @endif
            <button type="submit" name="action" value="save" class="btn btn-primary px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center gap-1.5" onclick="document.getElementById('order-form-action').value = 'save'">
                <iconify-icon icon="lucide:save" class="text-base"></iconify-icon>
                {{ isset($acrylicOrder) && !$isDraftCreate && $acrylicOrder->status !== 'draft' ? 'Cập nhật đơn hàng' : 'Lưu đơn hàng' }}
            </button>
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

function fetchCustomerDebt(customerId) {
    if (!customerId || !/^\d+$/.test(customerId)) {
        const debtCard = document.getElementById('customer-debt-card');
        if (debtCard) {
            debtCard.hidden = false;
            debtCard.classList.remove('hidden');
        }
        window.customerOldDebt = 0;
        window.customerOldPaid = 0;
        window.customerOldAmount = 0;
        const initDebtInput = document.getElementById('customer_initial_debt');
        if (initDebtInput) initDebtInput.value = 0;
        
        if (typeof updateOrderSummary === 'function') {
            updateOrderSummary();
        }
        return;
    }
    const excludeParam = window.currentOrderId ? `?exclude_order_id=${window.currentOrderId}` : '';
    fetch(`/customers/${customerId}/overview${excludeParam}`)
        .then(r => r.json())
        .then(data => {
            const debtCard = document.getElementById('customer-debt-card');
            if (data.unpaid_debt_summary) {
                window.customerOldDebt = data.unpaid_debt_summary.total_debt || 0;
                window.customerOldPaid = data.unpaid_debt_summary.total_paid || 0;
                window.customerOldAmount = data.unpaid_debt_summary.total_amount || 0;
                const initDebtInput = document.getElementById('customer_initial_debt');
                if (initDebtInput && data.customer) {
                    initDebtInput.value = data.customer.debt || 0;
                }
                
                if (typeof updateOrderSummary === 'function') {
                    updateOrderSummary();
                }

                if (debtCard) {
                    debtCard.hidden = false;
                    debtCard.classList.remove('hidden');
                }
            } else {
                if (debtCard) {
                    debtCard.hidden = true;
                    debtCard.classList.add('hidden');
                }
            }
        })
        .catch(e => console.error(e));
}

function fillCustomerInfo(customerId) {
    @if(auth()->check())
    const customers = @json($allowedCustomers ?? \App\Models\Customer::all());
    const customer = customers.find(c => c.id == customerId);
    if (customer) {
        document.querySelector('input[name="customer_name"]').value = customer.name;
        document.querySelector('input[name="phone"]').value = customer.phone || '';
        document.querySelector('textarea[name="address"]').value = customer.address || '';
        const policyEl = document.querySelector('textarea[name="customer_policy"]');
        if (policyEl) {
            policyEl.value = customer.policy || '';
        }
    } else if (customerId && !/^\d+$/.test(customerId)) {
        document.querySelector('input[name="customer_name"]').value = customerId;
        document.querySelector('input[name="phone"]').value = '';
        document.querySelector('textarea[name="address"]').value = '';
        const policyEl = document.querySelector('textarea[name="customer_policy"]');
        if (policyEl) {
            policyEl.value = '';
        }
    } else {
        // Clear fields if select is cleared
        document.querySelector('input[name="customer_name"]').value = '';
        document.querySelector('input[name="phone"]').value = '';
        document.querySelector('textarea[name="address"]').value = '';
        const policyEl = document.querySelector('textarea[name="customer_policy"]');
        if (policyEl) {
            policyEl.value = '';
        }
    }
    
    // Luôn gọi hàm này để cập nhật hoặc ẩn thẻ công nợ tùy theo customerId
    fetchCustomerDebt(customerId);
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
    const customerEl = form.querySelector('[name="customer_id"]');
    let customerText = '';
    if (customerEl) {
        if (customerEl.tomselect) {
            customerText = customerEl.tomselect.getItem(customerEl.tomselect.getValue())?.textContent || '';
        } else if (customerEl.selectedIndex >= 0 && customerEl.options[customerEl.selectedIndex]) {
            const t = customerEl.options[customerEl.selectedIndex].text.trim();
            customerText = t.startsWith('--') ? '' : t;
        }
    }
    const phone = getVal('[name="phone"]');
    const orderDate = getVal('[name="order_date"]');
    const deliveryDays = getVal('[name="delivery_days"]');
    const deadline = getVal('[name="deadline"]');
    const address = form.querySelector('[name="address"]')?.value || '';
    const notes = form.querySelector('[name="notes"]')?.value || '';
    const customerPolicy = form.querySelector('[name="customer_policy"]')?.value || '';
    const statusEl = form.querySelector('[name="status"]');
    let statusText = '';
    if (statusEl) {
        if (statusEl.tagName === 'SELECT') {
            statusText = statusEl.options[statusEl.selectedIndex]?.text || '';
        } else {
            statusText = '{{ isset($acrylicOrder) ? ($acrylicOrder->status === "pending" ? "Chờ xử lý" : ($acrylicOrder->status === "transferred" ? "Chuyển sản xuất" : ($acrylicOrder->status === "cancelled" ? "Đã hủy" : ($acrylicOrder->status === "in_production" ? "Đang sản xuất" : ($acrylicOrder->status === "completed" ? "Hoàn thành" : $acrylicOrder->status))))) : "Chờ xử lý" }}';
        }
    } else {
        // Fallback for when there's no status input (e.g. edit mode with buttons)
        statusText = '{{ isset($acrylicOrder) ? ($acrylicOrder->status === "pending" ? "Chờ xử lý" : ($acrylicOrder->status === "transferred" ? "Chuyển sản xuất" : ($acrylicOrder->status === "cancelled" ? "Đã hủy" : ($acrylicOrder->status === "in_production" ? "Đang sản xuất" : ($acrylicOrder->status === "completed" ? "Hoàn thành" : $acrylicOrder->status))))) : "Chờ xử lý" }}';
    }

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

    // Thu thập vật tư & sản phẩm (Đầy đủ 100% tất cả các cột)
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

        // Lấy headers từ hàng đầu tiên của thead
        const table = supplyRow.querySelector('table');
        if (!table) return;
        const theadFirstRow = table.querySelector('thead tr:first-child');
        if (!theadFirstRow) return;

        const thList = Array.from(theadFirstRow.querySelectorAll('th'));
        const colDefs = [];
        thList.forEach((th, idx) => {
            const text = th.textContent.trim().replace(/\s*\*\s*/g, '');
            // Bỏ cột Hành động ở cuối
            if (text === 'Hành động' || th.querySelector('iconify-icon[icon*="trash"]') || idx === thList.length - 1) {
                return;
            }
            colDefs.push({
                index: idx,
                title: text
            });
        });

        // Duyệt từng item row của vật tư
        let itemsHTML = '';
        let sumQty = 0;
        let sumWingArea = 0;
        let sumMolding = 0;
        let sumTotalPrice = 0;

        const itemRows = supplyRow.querySelectorAll('.order-item-row');
        itemRows.forEach((row, iIdx) => {
            const tds = row.querySelectorAll('td');
            if (tds.length === 0) return;

            let rowHTML = '<tr style="border-bottom: 1px solid #cbd5e1;">';
            colDefs.forEach(col => {
                const td = tds[col.index];
                let cellVal = '';
                let align = 'center';
                let isBold = false;
                let textColor = '#0f172a';

                if (col.index === 0) {
                    // STT
                    cellVal = (iIdx + 1).toString();
                } else if (td) {
                    const titleLower = col.title.toLowerCase();

                    // 1. Cột Vát: Luôn lấy giá trị SỐ từ input thay vì text "▪ Bám theo Rộng" của select
                    const bevelInput = td.querySelector('.product-bevel-input') || td.querySelector('input[name*="[bevel]"]:not([name*="[beveled_"])');
                    if (titleLower === 'vát' || bevelInput) {
                        if (bevelInput) {
                            let bVal = bevelInput.value.trim();
                            // Fallback nếu đang ở chế độ bám theo Rộng / Dài mà ô vát chưa cập nhật số
                            if (!bVal || bVal === '0') {
                                const syncMode = bevelInput.getAttribute('data-auto-sync');
                                if (syncMode === 'width') {
                                    bVal = row.querySelector('input[name*="[width]"]')?.value?.trim() || '';
                                } else if (syncMode === 'height') {
                                    bVal = row.querySelector('input[name*="[height]"]')?.value?.trim() || '';
                                }
                            }
                            const bNum = parseFloat(bVal);
                            cellVal = (!isNaN(bNum) && bNum > 0) ? bVal : '';
                        }
                    }
                    // 2. Các ô khác: Trích xuất bình thường
                    else {
                        const select = td.querySelector('select:not(.product-bevel-select)');
                        const input = td.querySelector('input:not([type="hidden"])');
                        const textarea = td.querySelector('textarea');

                        if (input) {
                            cellVal = input.value.trim();
                        } else if (select) {
                            if (select.tomselect) {
                                cellVal = select.tomselect.getItem(select.tomselect.getValue())?.textContent?.trim() || '';
                            } else if (select.selectedIndex >= 0 && select.options[select.selectedIndex]) {
                                const optText = select.options[select.selectedIndex].text.trim();
                                cellVal = (optText.startsWith('--') && optText.endsWith('--')) ? '' : optText;
                            }
                        } else if (textarea) {
                            cellVal = textarea.value.trim();
                        } else {
                            cellVal = td.textContent.trim();
                        }
                    }

                    // Xử lý các cột đặc thù theo tiêu đề cột
                    // Tên sản phẩm
                    if (titleLower.includes('tên')) {
                        align = 'left';
                        isBold = true;
                        if (row.hasAttribute('data-is-labor') || row.classList.contains('bg-amber-50/60')) {
                            cellVal = '[Công] ' + (cellVal || 'Công giả dày');
                            textColor = '#d97706';
                        }
                    }
                    // Đơn giá & Thành tiền (Xử lý định dạng VNĐ có dấu chấm phân cách hàng nghìn)
                    else if (titleLower.includes('đơn giá') || titleLower.includes('thành tiền')) {
                        align = 'right';
                        isBold = true;
                        let cleanVal = cellVal.replace(/[^\d.,-]/g, '');
                        if (cleanVal.includes('.') && cleanVal.includes(',')) {
                            if (cleanVal.lastIndexOf(',') > cleanVal.lastIndexOf('.')) {
                                cleanVal = cleanVal.substring(0, cleanVal.lastIndexOf(',')).replace(/\./g, '');
                            } else {
                                cleanVal = cleanVal.substring(0, cleanVal.lastIndexOf('.')).replace(/,/g, '');
                            }
                        } else if (cleanVal.includes('.')) {
                            // Số tiền VNĐ: dấu chấm luôn là dấu phân cách hàng nghìn (ví dụ 45.501 -> 45501)
                            cleanVal = cleanVal.replace(/\./g, '');
                        } else if (cleanVal.includes(',')) {
                            const parts = cleanVal.split(',');
                            if (parts.length > 1 && parts[parts.length - 1].length === 3) {
                                cleanVal = cleanVal.replace(/,/g, '');
                            } else {
                                cleanVal = cleanVal.replace(',', '.');
                            }
                        }
                        const num = parseFloat(cleanVal);
                        if (!isNaN(num) && num > 0) {
                            cellVal = Math.round(num).toLocaleString('vi-VN') + ' đ';
                            if (titleLower.includes('thành tiền')) {
                                textColor = '#059669';
                                sumTotalPrice += num;
                            }
                        } else {
                            cellVal = '';
                        }
                    }
                    // Số lượng
                    else if (titleLower.includes('số lượng') || titleLower === 'sl' || titleLower === 'sl cánh') {
                        const num = parseFloat(cellVal);
                        if (!isNaN(num)) sumQty += num;
                    }
                    // Cánh (m2) hoặc Khối lượng
                    else if (titleLower.includes('cánh') || titleLower.includes('m2') || titleLower.includes('khối lượng')) {
                        const num = parseFloat(cellVal);
                        if (!isNaN(num)) {
                            sumWingArea += num;
                            cellVal = num.toFixed(2);
                        }
                    }
                    // Phào (m)
                    else if (titleLower.includes('phào')) {
                        const num = parseFloat(cellVal);
                        if (!isNaN(num)) {
                            sumMolding += num;
                            cellVal = num.toFixed(2);
                        }
                    }
                    // Chiều vân
                    else if (titleLower.includes('chiều vân')) {
                        if (cellVal === '0') cellVal = 'Không vân';
                        else if (cellVal === '1') cellVal = 'Vân dọc';
                        else if (cellVal === '2') cellVal = 'Vân ngang';
                    }
                    // Ghi chú
                    else if (titleLower.includes('ghi chú')) {
                        align = 'left';
                    }
                }

                rowHTML += `<td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: ${align}; color: ${textColor}; font-weight: ${isBold ? '600' : 'normal'}; font-size: 13px; white-space: nowrap;">${cellVal || '—'}</td>`;
            });
            rowHTML += '</tr>';
            itemsHTML += rowHTML;
        });

        // Tạo headers
        let headerHTML = '<tr style="background-color: #f1f5f9;">';
        colDefs.forEach(col => {
            headerHTML += `<th style="border: 1.5px solid #cbd5e1; padding: 9px 8px; text-align: center; color: #0f172a; font-weight: 700; font-size: 12.5px; text-transform: uppercase; white-space: nowrap;">${col.title}</th>`;
        });
        headerHTML += '</tr>';

        // Tạo dòng tổng cộng cho bảng này
        let summaryRowHTML = '<tr style="background-color: #f8fafc; font-weight: 700; border-top: 2px solid #94a3b8;">';
        colDefs.forEach((col, cIdx) => {
            const titleLower = col.title.toLowerCase();
            let val = '';
            let align = 'center';
            let color = '#0f172a';

            if (cIdx === 0) {
                val = 'TỔNG';
            } else if (titleLower.includes('số lượng') || titleLower === 'sl' || titleLower === 'sl cánh') {
                val = sumQty.toString();
            } else if (titleLower.includes('cánh') || titleLower.includes('m2') || titleLower.includes('khối lượng')) {
                val = sumWingArea > 0 ? sumWingArea.toFixed(2) + ' m²' : '—';
                color = '#2563eb';
            } else if (titleLower.includes('phào')) {
                val = sumMolding > 0 ? sumMolding.toFixed(2) + ' m' : '—';
            } else if (titleLower.includes('thành tiền')) {
                val = sumTotalPrice > 0 ? Math.round(sumTotalPrice).toLocaleString('vi-VN') + ' đ' : '—';
                align = 'right';
                color = '#059669';
            }

            summaryRowHTML += `<td style="border: 1px solid #cbd5e1; padding: 8px 8px; text-align: ${align}; color: ${color}; font-size: 13px; font-weight: 700; white-space: nowrap;">${val}</td>`;
        });
        summaryRowHTML += '</tr>';

        suppliesHTML += `
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-2.5 pb-2 border-b border-neutral-200">
                    <div class="p-1.5 bg-primary-100 rounded-lg text-primary-700 flex items-center justify-center font-bold">
                        <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                    </div>
                    <span class="font-extrabold text-sm text-neutral-800">${supplyCode || 'Vật tư ' + (sIdx + 1)}</span>
                    ${supplyName ? `<span class="text-xs font-semibold text-neutral-600">— ${supplyName}</span>` : ''}
                    ${supplyQty ? `<span class="text-xs bg-primary-50 text-primary-700 border border-primary-200 px-2.5 py-0.5 rounded-full font-bold">SL tấm: ${supplyQty}</span>` : ''}
                </div>
                <div class="overflow-x-auto rounded-lg border border-neutral-300">
                    <table class="w-full" style="border-collapse: collapse; min-width: 100%;">
                        <thead>${headerHTML}</thead>
                        <tbody>${itemsHTML || '<tr><td colspan="' + colDefs.length + '" class="text-center text-xs text-neutral-400 py-4">Chưa có sản phẩm</td></tr>'}</tbody>
                        <tfoot>${summaryRowHTML}</tfoot>
                    </table>
                </div>
            </div>
        `;
    });

    // Thu thập chi tiết hóa đơn dịch vụ
    let paymentDetailsHTML = '';
    let sumPaymentTotal = 0;
    const paymentRows = document.querySelectorAll('.payment-detail-row');
    if (paymentRows.length > 0) {
        paymentRows.forEach((row, rIdx) => {
            const getPDVal = (selector) => {
                const input = row.querySelector(selector);
                if (!input) return '';
                if (!input.value) return '';
                if (input.tagName === 'SELECT') {
                    if (input.tomselect) return input.tomselect.getItem(input.tomselect.getValue())?.textContent || '';
                    return input.options[input.selectedIndex]?.text || '';
                }
                return input.value || '';
            };
            
            const code = getPDVal('.order-payment-code-select');
            const name = getPDVal('[name*="[name]"]');
            const unit = getPDVal('[name*="[unit]"]');
            let qty = getPDVal('[name*="[quantity]"]');
            let price = getPDVal('[name*="[price]"]');
            let priceOnly = getPDVal('[name*="[price_only]"]');
            let total = getPDVal('[name*="[total]"]');

            // Hàm phân tích tiền tệ VNĐ (loại bỏ triệt để dấu chấm/phẩy phân cách hàng nghìn)
            const parseVNMoney = (val) => {
                if (val === undefined || val === null || val === '') return 0;
                if (typeof val === 'number') return val;
                let s = String(val).trim().replace(/[^\d.,-]/g, '');
                if (!s) return 0;
                s = s.replace(/\./g, '').replace(/,/g, '');
                return parseFloat(s) || 0;
            };

            // Hàm phân tích số lượng (có thể có phần thập phân như 5.23 hoặc 0.75)
            const parseVNQty = (val) => {
                if (val === undefined || val === null || val === '') return 0;
                if (typeof val === 'number') return val;
                let s = String(val).trim().replace(/[^\d.,-]/g, '');
                if (!s) return 0;
                if (s.includes(',')) s = s.replace(',', '.');
                return parseFloat(s) || 0;
            };

            const qNum = parseVNQty(qty);
            const pNum = parseVNMoney(price);
            const poNum = parseVNMoney(priceOnly);
            const effectiveUnitPrice = (pNum + poNum) > 0 ? (pNum + poNum) : pNum;

            let tNum = 0;
            if (qNum > 0 && effectiveUnitPrice > 0) {
                tNum = Math.round(qNum * effectiveUnitPrice);
            } else {
                tNum = parseVNMoney(total);
            }

            if (!name && !qNum && !effectiveUnitPrice && !code) return;

            let displayQty = '—';
            if (qNum > 0) {
                displayQty = Number.isInteger(qNum) ? qNum.toLocaleString('vi-VN') : qNum.toLocaleString('vi-VN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            let displayPrice = '—';
            if (effectiveUnitPrice > 0) {
                displayPrice = Math.round(effectiveUnitPrice).toLocaleString('vi-VN') + ' đ';
            } else if (pNum > 0) {
                displayPrice = Math.round(pNum).toLocaleString('vi-VN') + ' đ';
            }

            let displayTotal = '—';
            if (tNum > 0) {
                sumPaymentTotal += tNum;
                displayTotal = Math.round(tNum).toLocaleString('vi-VN') + ' đ';
            } else if (tNum === 0 && (qNum > 0 || effectiveUnitPrice > 0)) {
                displayTotal = '0 đ';
            }

            paymentDetailsHTML += `<tr style="border-bottom: 1px solid #cbd5e1;">
                <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; font-size: 13px;">${rIdx + 1}</td>
                <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; font-weight: 600; font-size: 13px;">${code || '—'}</td>
                <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: left; font-weight: 600; font-size: 13px;">${name || '—'}</td>
                <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; font-size: 13px;">${unit || '—'}</td>
                <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: center; font-size: 13px;">${displayQty}</td>
                <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: right; font-size: 13px;">${displayPrice}</td>
                <td style="border: 1px solid #cbd5e1; padding: 7px 8px; text-align: right; font-weight: 700; color: #059669; font-size: 13px;">${displayTotal}</td>
            </tr>`;
        });
    }

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

    // Lưu thông tin đơn hiện tại cho các hàm chụp/copy ảnh
    window.currentPreviewOrder = {
        orderCode: orderCode || 'DON_HANG',
        customerName: customerName || 'Khách hàng',
        phone: phone || ''
    };

    // Tạo modal HTML
    const modalHTML = `
        <div id="preview-order-modal" style="position:fixed; inset:0; z-index:99999999; display:flex; align-items:center; justify-content:center;">
            <div style="position:absolute; inset:0; background:rgba(0,0,0,0.5);" onclick="closePreviewOrder()"></div>
            <div class="bg-white flex flex-col" style="position:relative; width:100%; height:100%; z-index:1;">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 flex-shrink-0 flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-50 rounded-xl">
                            <iconify-icon icon="lucide:eye" class="text-xl text-emerald-500"></iconify-icon>
                        </div>
                        <div>
                            <h5 class="font-bold text-base text-neutral-800 m-0">Xem trước đơn hàng</h5>
                            <p class="text-xs text-neutral-400 m-0 mt-0.5">${orderCode} • ${typeLabel}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" id="btn-copy-preview-header" onclick="copyPreviewOrderImage()" class="btn text-white px-3.5 py-2 rounded-lg text-xs font-bold shadow-sm transition-all flex items-center gap-1.5 hover:opacity-95" style="background:#0068FF; border:none;" title="Chụp ảnh đơn hàng và lưu vào bộ nhớ tạm (sang Zalo chỉ cần Ctrl + V)">
                            <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                            <span>Copy ảnh gửi Zalo</span>
                        </button>
                        <button type="button" onclick="downloadPreviewOrderImage()" class="btn bg-white hover:bg-neutral-50 text-neutral-600 px-3 py-2 rounded-lg text-xs font-semibold border border-neutral-300 shadow-xs transition-all flex items-center gap-1.5" title="Tải ảnh PNG đơn hàng về máy">
                            <iconify-icon icon="lucide:download" class="text-sm"></iconify-icon>
                            <span>Tải ảnh</span>
                        </button>
                        <div class="h-6 w-px bg-neutral-200 mx-1"></div>
                        <button type="button" onclick="closePreviewOrder()" class="w-8 h-8 rounded-lg hover:bg-neutral-100 flex items-center justify-center text-neutral-400 hover:text-neutral-600 transition-colors">
                            <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                        </button>
                    </div>
                </div>

                <!-- Body (scrollable) -->
                <div class="flex-1 overflow-y-auto p-4 md:p-6 bg-neutral-100/50" style="min-height:0;" id="preview-order-scroll-container">
                    <div id="preview-order-capture-area" class="bg-white p-6 md:p-8 space-y-6 rounded-2xl border border-neutral-200 shadow-sm w-full mx-auto" style="min-width: 1100px;">
                        <!-- Header trên ảnh khi xuất -->
                        <div class="border-b-2 border-primary-500 pb-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary-600 to-primary-700 text-white flex items-center justify-center font-black text-xl shadow-sm">G</div>
                                <div>
                                    <h4 class="font-bold text-lg text-neutral-800 m-0 leading-tight">PHIẾU THÔNG TIN ĐƠN HÀNG</h4>
                                    <p class="text-xs text-neutral-500 m-0 mt-0.5 font-medium">Hệ thống sản xuất & gia công Gervin</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Mã đơn hàng</div>
                                <div class="text-xl font-extrabold text-primary-600 font-mono tracking-tight">${orderCode}</div>
                                <div class="text-xs text-neutral-500 mt-0.5">${formatDate(orderDate)}</div>
                            </div>
                        </div>

                        <!-- Thông tin khách hàng & đơn hàng -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
                                <div class="flex items-center gap-2 mb-3 pb-3 border-b border-neutral-100">
                                    <iconify-icon icon="lucide:user" class="text-lg text-primary-500"></iconify-icon>
                                    <h6 class="font-bold text-sm text-neutral-800 m-0">Thông tin khách hàng & công trình</h6>
                                </div>
                                ${customerText ? infoRow('Khách hàng', customerText, 'lucide:user') : ''}
                                ${infoRow('Tên công trình', customerName, 'lucide:building')}
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

                        <!-- Hóa đơn dịch vụ -->
                        ${paymentDetailsHTML ? `
                        <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
                            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-neutral-100">
                                <iconify-icon icon="lucide:receipt" class="text-lg text-primary-500"></iconify-icon>
                                <h6 class="font-bold text-sm text-neutral-800 m-0">Chi tiết hóa đơn dịch vụ</h6>
                            </div>
                            <div class="overflow-x-auto rounded-lg border border-neutral-200">
                                <table class="w-full">
                                    <thead>
                                        <tr class="bg-primary-50/50">
                                            <th class="px-3 py-2.5 text-xs font-bold text-neutral-600 uppercase border border-neutral-100 text-center whitespace-nowrap">STT</th>
                                            <th class="px-3 py-2.5 text-xs font-bold text-neutral-600 uppercase border border-neutral-100 text-center whitespace-nowrap">Mã</th>
                                            <th class="px-3 py-2.5 text-xs font-bold text-neutral-600 uppercase border border-neutral-100 text-left whitespace-nowrap">Tên nội dung</th>
                                            <th class="px-3 py-2.5 text-xs font-bold text-neutral-600 uppercase border border-neutral-100 text-center whitespace-nowrap">Đơn vị</th>
                                            <th class="px-3 py-2.5 text-xs font-bold text-neutral-600 uppercase border border-neutral-100 text-center whitespace-nowrap">Số lượng</th>
                                            <th class="px-3 py-2.5 text-xs font-bold text-neutral-600 uppercase border border-neutral-100 text-center whitespace-nowrap">Đơn giá</th>
                                            <th class="px-3 py-2.5 text-xs font-bold text-neutral-600 uppercase border border-neutral-100 text-center whitespace-nowrap">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>${paymentDetailsHTML}</tbody>
                                </table>
                            </div>
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

                        <!-- Footer trên ảnh khi xuất -->
                        <div class="pt-4 border-t border-neutral-200 flex items-center justify-between text-xs text-neutral-400">
                            <span>Xưởng Nội Thất Gervin</span>
                            <span>Phiếu xuất lúc: ${new Date().toLocaleString('vi-VN')}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between gap-3 px-6 py-4 border-t border-neutral-200 flex-shrink-0 bg-neutral-50/50 flex-wrap">
                    <div class="flex items-center gap-2 text-xs text-neutral-600 mr-auto">
                        <iconify-icon icon="lucide:sparkles" class="text-base text-amber-500 shrink-0"></iconify-icon>
                        <span>Bấm <strong>Copy ảnh gửi Zalo</strong>, sau đó mở chat với khách và ấn <strong>Ctrl + V</strong> để gửi ảnh ngay.</span>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" onclick="closePreviewOrder()" class="btn btn-outline-neutral px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">Đóng</button>
                        <button type="button" onclick="copyPreviewOrderImage()" class="btn text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center gap-1.5 hover:opacity-95" style="background:#0068FF; border:none;" title="Copy ảnh để dán Ctrl + V vào Zalo">
                            <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                            Copy ảnh gửi Zalo
                        </button>
                        @if(!isset($acrylicOrder) || $isDraftCreate || $acrylicOrder->status === 'draft')
                        <button type="button" onclick="closePreviewOrder(); if (typeof closeOrderSuppliesPopup === 'function') closeOrderSuppliesPopup(); document.getElementById('order-form-action').value = 'draft'; document.getElementById('order-form').requestSubmit();" class="btn bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center gap-1.5">
                            <iconify-icon icon="lucide:file-text" class="text-base"></iconify-icon>
                            Lưu nháp
                        </button>
                        @endif
                        <button type="button" onclick="closePreviewOrder(); if (typeof closeOrderSuppliesPopup === 'function') closeOrderSuppliesPopup(); document.getElementById('order-form-action').value = 'save'; document.getElementById('order-form').requestSubmit();" class="btn btn-primary px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center gap-1.5">
                            <iconify-icon icon="lucide:save" class="text-base"></iconify-icon>
                            {{ isset($acrylicOrder) && !$isDraftCreate && $acrylicOrder->status !== 'draft' ? 'Cập nhật đơn hàng' : 'Lưu đơn hàng' }}
                        </button>
                    </div>
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

// === CÁC HÀM XỬ LÝ CHỤP & COPY ẢNH GỬI ZALO ===
function showPreviewToast(message, type = 'success') {
    let container = document.getElementById('preview-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'preview-toast-container';
        container.style.cssText = 'position:fixed; top:24px; left:50%; transform:translateX(-50%); z-index:9999999999; display:flex; flex-direction:column; align-items:center; gap:10px; max-width:560px; width:max-content; pointer-events:none;';
        document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    
    let bgStyle = 'background: linear-gradient(135deg, #0f172a 0%, #064e3b 100%); border: 1.5px solid #10b981; box-shadow: 0 20px 35px -5px rgba(0, 0, 0, 0.55), 0 0 24px rgba(16, 185, 129, 0.35);';
    let icon = 'lucide:check-circle-2';
    let iconColor = '#34d399';
    let strongColor = '#6ee7b7';

    if (type === 'error') {
        bgStyle = 'background: linear-gradient(135deg, #0f172a 0%, #7f1d1d 100%); border: 1.5px solid #ef4444; box-shadow: 0 20px 35px -5px rgba(0, 0, 0, 0.55), 0 0 24px rgba(239, 68, 68, 0.35);';
        icon = 'lucide:alert-circle';
        iconColor = '#f87171';
        strongColor = '#fca5a5';
    } else if (type === 'warning') {
        bgStyle = 'background: linear-gradient(135deg, #0f172a 0%, #78350f 100%); border: 1.5px solid #f59e0b; box-shadow: 0 20px 35px -5px rgba(0, 0, 0, 0.55), 0 0 24px rgba(245, 158, 11, 0.35);';
        icon = 'lucide:alert-triangle';
        iconColor = '#fbbf24';
        strongColor = '#fde68a';
    }

    toast.style.cssText = `
        pointer-events: auto;
        ${bgStyle}
        color: #ffffff;
        border-radius: 14px;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        font-family: inherit;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        transform: translateY(-20px) scale(0.96);
        opacity: 0;
    `;

    toast.innerHTML = `
        <div style="flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
            <iconify-icon icon="${icon}" style="font-size: 26px; color: ${iconColor};"></iconify-icon>
        </div>
        <div style="font-size: 13.5px; line-height: 1.55; color: #f8fafc; font-weight: 500; word-break: break-word;">
            ${message.replace(/<strong>/g, `<strong style="color:${strongColor}; font-weight:700;">`)}
        </div>
        <button type="button" onclick="this.parentElement.remove()" style="margin-left: 8px; background: rgba(255,255,255,0.1); border: none; width: 26px; height: 26px; border-radius: 50%; cursor: pointer; color: #cbd5e1; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'" title="Đóng thông báo">
            <iconify-icon icon="lucide:x" style="font-size: 15px;"></iconify-icon>
        </button>
    `;

    container.appendChild(toast);
    requestAnimationFrame(() => {
        toast.style.transform = 'translateY(0) scale(1)';
        toast.style.opacity = '1';
    });
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px) scale(0.96)';
        setTimeout(() => toast.remove(), 350);
    }, 4500);
}

async function ensureHtml2CanvasLoaded() {
    if (window.html2canvas) return window.html2canvas;
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
        script.onload = () => resolve(window.html2canvas);
        script.onerror = () => reject(new Error('Không thể tải thư viện html2canvas từ CDN'));
        document.head.appendChild(script);
    });
}

async function getPreviewOrderImageBlob() {
    const captureEl = document.getElementById('preview-order-capture-area');
    if (!captureEl) throw new Error('Không tìm thấy vùng nội dung đơn hàng để chụp');

    await ensureHtml2CanvasLoaded();

    // 1. Tạo một bản sao (clone) ẩn ngoài màn hình để bung toàn bộ chiều rộng 1750px (đầy đủ 100% cột, không bị cắt và cực nét)
    const clone = captureEl.cloneNode(true);
    clone.id = 'zalo-export-clone-node';
    
    // Đặt chiều rộng cố định 1750px để TẤT CẢ các cột bung ra thoải mái
    clone.style.position = 'fixed';
    clone.style.top = '0';
    clone.style.left = '-99999px';
    clone.style.width = '1750px';
    clone.style.minWidth = '1750px';
    clone.style.maxWidth = '1750px';
    clone.style.height = 'auto';
    clone.style.zIndex = '-999999';
    clone.style.background = '#ffffff';
    clone.style.padding = '35px 40px';
    clone.style.boxSizing = 'border-box';
    clone.style.boxShadow = 'none';
    clone.style.borderRadius = '0';

    // Bỏ tất cả các giới hạn cuộn và overflow trong clone để không bị cắt cột
    clone.querySelectorAll('.overflow-x-auto, .overflow-y-auto, .overflow-hidden').forEach(el => {
        el.style.overflow = 'visible';
        el.style.width = '100%';
        el.style.maxWidth = 'none';
    });

    // Tinh chỉnh bảng trong clone để hiển thị sắc nét từng đường viền và chữ
    clone.querySelectorAll('table').forEach(tbl => {
        tbl.style.width = '100%';
        tbl.style.minWidth = '100%';
        tbl.style.tableLayout = 'auto';
        tbl.style.borderCollapse = 'collapse';
        tbl.style.fontSize = '13.5px';
    });
    clone.querySelectorAll('th').forEach(th => {
        th.style.padding = '10px 8px';
        th.style.fontSize = '13px';
        th.style.fontWeight = '700';
        th.style.border = '1.5px solid #cbd5e1';
        th.style.backgroundColor = '#f1f5f9';
        th.style.color = '#0f172a';
        th.style.whiteSpace = 'nowrap';
    });
    clone.querySelectorAll('td').forEach(td => {
        td.style.padding = '8px 8px';
        td.style.fontSize = '13px';
        td.style.border = '1px solid #cbd5e1';
        td.style.whiteSpace = 'nowrap';
    });

    document.body.appendChild(clone);

    // Chờ 80ms để trình duyệt render các font và layout hoàn tất
    await new Promise(r => setTimeout(r, 80));

    try {
        // Chụp với scale 2.5 trên bề rộng 1750px -> ảnh rộng ~4375px cực nét (Retina 300DPI)
        const canvas = await html2canvas(clone, {
            scale: 2.5,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            logging: false,
            width: clone.scrollWidth,
            height: clone.scrollHeight,
            windowWidth: 1750,
        });

        clone.remove();

        return new Promise((resolve, reject) => {
            canvas.toBlob((blob) => {
                if (blob) resolve(blob);
                else reject(new Error('Không thể xuất ảnh từ canvas'));
            }, 'image/png', 0.98);
        });
    } catch (err) {
        if (clone && clone.parentNode) clone.remove();
        throw err;
    }
}

async function copyPreviewOrderImage() {
    const orderCode = window.currentPreviewOrder?.orderCode || 'DON_HANG';
    const btn = document.getElementById('btn-copy-preview-header');
    const originalHTML = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<iconify-icon icon="lucide:loader-2" class="text-base animate-spin"></iconify-icon> <span>Đang chụp ảnh...</span>`;
    }

    try {
        const blob = await getPreviewOrderImageBlob();
        
        if (navigator.clipboard && window.ClipboardItem) {
            await navigator.clipboard.write([
                new ClipboardItem({ 'image/png': blob })
            ]);
            showPreviewToast(`Đã sao chép ảnh đơn hàng <strong>${orderCode}</strong> thành công! Bạn chỉ cần vào khung chat Zalo của khách và bấm <strong>Ctrl + V</strong> để gửi ảnh ngay.`);
        } else {
            downloadBlob(blob, `Don_Hang_${orderCode}.png`);
            showPreviewToast(`Trình duyệt không hỗ trợ sao chép ảnh trực tiếp, đã tự động tải ảnh về máy cho bạn.`, 'warning');
        }
    } catch (err) {
        console.error('Lỗi sao chép ảnh:', err);
        try {
            const blob = await getPreviewOrderImageBlob();
            downloadBlob(blob, `Don_Hang_${orderCode}.png`);
            showPreviewToast(`Không thể copy trực tiếp vào bộ nhớ tạm do chính sách bảo mật trình duyệt. Đã tự động tải file ảnh về máy cho bạn.`, 'warning');
        } catch (downloadErr) {
            showPreviewToast('Không thể tạo ảnh đơn hàng: ' + (err.message || 'Lỗi không xác định'), 'error');
        }
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        }
    }
}

async function downloadPreviewOrderImage() {
    const orderCode = window.currentPreviewOrder?.orderCode || 'DON_HANG';
    try {
        const blob = await getPreviewOrderImageBlob();
        downloadBlob(blob, `Don_Hang_${orderCode}.png`);
        showPreviewToast(`Đã tải ảnh đơn hàng <strong>${orderCode}</strong> thành công!`);
    } catch (err) {
        console.error('Lỗi tải ảnh:', err);
        showPreviewToast('Không thể tải ảnh: ' + (err.message || 'Lỗi không xác định'), 'error');
    }
}

function downloadBlob(blob, fileName) {
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = fileName;
    document.body.appendChild(a);
    a.click();
    setTimeout(() => {
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }, 100);
}

function updateOrderSummary() {
    // Auto-calculate total quantity for each supply block
    document.querySelectorAll('.order-supply-row').forEach(supplyRow => {
        const supplyQtyInput = supplyRow.querySelector('input[name^="supplies["][name$="][quantity]"]:not([name*="][items]["])');
        
        if (supplyQtyInput) {
            const itemQtyInputs = supplyRow.querySelectorAll('input[name*="][items]["][name$="][quantity]"], input[name*="][items]["][name$="][wing_quantity]"]');
            
            if (itemQtyInputs.length > 0) {
                let total = 0;
                itemQtyInputs.forEach(input => {
                    // Ignore rows that might be visually hidden or disabled (e.g. templates)
                    if (!input.disabled && !input.closest('tr.hidden')) {
                        const val = parseFloat(input.value);
                        if (!isNaN(val)) {
                            total += val;
                        }
                    }
                });
                
                // Only update if it's different and a valid number, avoiding infinite loops or overriding empty inputs when total is 0?
                // Actually, if total is 0, we still want to show 0.
                if (!isNaN(total) && parseFloat(supplyQtyInput.value) !== total) {
                    supplyQtyInput.value = total;
                }
            }
        }
    });
    // Auto-calculate column sums for supplies tables
    document.querySelectorAll('.order-supply-row').forEach(supplyRow => {
        const table = supplyRow.querySelector('table');
        if (table) {
            const summaryRow = table.querySelector('.table-summary-row');
            if (summaryRow) {
                summaryRow.querySelectorAll('td[data-summary-field]').forEach(td => {
                    const field = td.getAttribute('data-summary-field');
                    let sum = 0;
                    
                    // Fields that should display as float (decimals)
                    const isFloat = field === 'wing_area' || field === 'weight' || field.includes('length') || field.includes('ban_rong') || field === 'area_m2';
                    
                    table.querySelectorAll(`tbody tr:not(.hidden) input[name*="[${field}]"]`).forEach(input => {
                        if (!input.disabled && input.type !== 'hidden') {
                            let valStr = input.hasAttribute('data-exact-value') ? input.getAttribute('data-exact-value') : input.value;
                            if (typeof valStr === 'string' && !isFloat) {
                                valStr = valStr.replace(/\./g, '');
                            }
                            const val = parseFloat(valStr);
                            if (!isNaN(val)) {
                                sum += val;
                            }
                        }
                    });
                    
                    // Format sum
                    if (field === 'total_price') {
                        const roundedSum = Math.round(sum / 1000) * 1000;
                        td.textContent = roundedSum.toLocaleString('vi-VN') + 'đ';
                    } else if (field === 'unit_price' || field === 'price') {
                        td.textContent = sum.toLocaleString('vi-VN') + 'đ';
                    } else {
                        td.textContent = isFloat ? (Math.round(sum * 100) / 100).toFixed(2).replace(/\.00$/, '') : Math.round(sum);
                    }
                });
            }
        }
    });

    window.isReworkOrder = @json(isset($acrylicOrder) && $acrylicOrder->relation_type === 'rework');
    window.isWarrantyOrder = @json(isset($acrylicOrder) && $acrylicOrder->relation_type === 'warranty');
    const orderType = @json($currentOrderType);
    
    let totalItems = 0;
    let totalSheets = 0;
    let totalArea = 0;
    let totalAmount = 0;
    
    if (orderType === 'min_late') {
        // Tự động đồng bộ 3 dòng dịch vụ sửa tấm (LIC1, ML48, ML49) nếu là đơn sửa tấm
        if (window.isReworkOrder) {
            let reworkPlates = 0;
            let reworkStraight = 0;
            let reworkBeveled = 0;

            document.querySelectorAll('.order-supply-row table tbody tr:not(.hidden)').forEach(r => {
                const qInput = r.querySelector('input[name*="[quantity]"]');
                const sInput = r.querySelector('input[name*="[straight_paste_length]"]');
                const bInput = r.querySelector('input[name*="[beveled_length]"]');

                const q = parseFloat(qInput ? qInput.value : 0) || 0;
                if (q > 0) reworkPlates += q;

                if (sInput && !sInput.disabled) {
                    reworkStraight += parseFloat(sInput.getAttribute('data-exact-value') || sInput.value) || 0;
                }
                if (bInput && !bInput.disabled) {
                    reworkBeveled += parseFloat(bInput.getAttribute('data-exact-value') || bInput.value) || 0;
                }
            });

            const ml48Q = Math.round(reworkStraight * 1.05 * 100) / 100;
            const ml49Q = Math.round(reworkBeveled * 1.05 * 100) / 100;

            const pSection = document.getElementById('payment-details-section');
            if (pSection && pSection.style.display === 'none') {
                pSection.style.display = '';
            }

            const pRows = document.querySelectorAll('#payment-details-container tr.payment-detail-row');
            pRows.forEach(pRow => {
                const nameIn = pRow.querySelector('textarea[name*="[name]"]');
                const qIn = pRow.querySelector('input[name*="[quantity]"]');
                const selIn = pRow.querySelector('.order-payment-code-select');
                const codeVal = (selIn ? (selIn.tomselect ? selIn.tomselect.getValue() : selIn.value) : '').toUpperCase();
                const nVal = (nameIn ? nameIn.value : '').toUpperCase();

                const isLic1 = codeVal === 'LIC1' || nVal.includes('LIC1') || nVal.includes('SỬA TẤM');
                const isMl48 = codeVal === 'ML48' || nVal.includes('ML48') || nVal.includes('DÁN CHỈ THẲNG');
                const isMl49 = codeVal === 'ML49' || nVal.includes('ML49') || nVal.includes('DÁN CHỈ VÁT');

                if (isLic1) {
                    if (qIn && parseFloat(qIn.value) !== reworkPlates) {
                        qIn.value = reworkPlates;
                        if (typeof calculatePaymentDetailRowTotal === 'function') calculatePaymentDetailRowTotal(pRow);
                    }
                } else if (isMl48) {
                    if (qIn && parseFloat(qIn.value) !== ml48Q) {
                        qIn.value = ml48Q;
                        if (typeof calculatePaymentDetailRowTotal === 'function') calculatePaymentDetailRowTotal(pRow);
                    }
                } else if (isMl49) {
                    if (qIn && parseFloat(qIn.value) !== ml49Q) {
                        qIn.value = ml49Q;
                        if (typeof calculatePaymentDetailRowTotal === 'function') calculatePaymentDetailRowTotal(pRow);
                    }
                }
            });
        }

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
        
        // Calculate total amount from payment details (nếu không phải đơn bảo hành)
        if (!window.isWarrantyOrder) {
            const detailRows = document.querySelectorAll('.payment-detail-row');
            detailRows.forEach(row => {
                const totalInput = row.querySelector('input[name*="total"]');
                if (totalInput && !totalInput.disabled) {
                    const rawTotal = totalInput.value ? totalInput.value.replace(/\./g, '') : 0;
                    totalAmount += parseFloat(rawTotal) || 0;
                }
            });
        }
    } else {
        const rows = document.querySelectorAll('.order-item-row');
        rows.forEach(row => {
            const qtyInput = row.querySelector('input[name*="quantity"]');
            const priceInput = row.querySelector('input[name*="total_price"]');
            const heightInput = row.querySelector('input[name*="[height]"]');
            const widthInput = row.querySelector('input[name*="[width]"]');
            if (qtyInput && !qtyInput.disabled) {
                const quantity = parseFloat(qtyInput.value) || 0;
                const rawPrice = priceInput ? priceInput.value.replace(/\./g, '') : 0;
                const totalPrice = parseFloat(rawPrice) || 0;
                const height = parseFloat(heightInput ? heightInput.value : 0) || 0;
                const width = parseFloat(widthInput ? widthInput.value : 0) || 0;
                totalItems++;
                totalSheets += quantity;
                if (height > 0 && width > 0) {
                    totalArea += (height * width * quantity) / 1000000;
                }
                if (!window.isReworkOrder && !window.isWarrantyOrder) {
                    totalAmount += totalPrice;
                }
            }
        });

        // Also add total amount from payment details (for acrylic orders, nếu không phải đơn bảo hành)
        if (!window.isWarrantyOrder) {
            const detailRows = document.querySelectorAll('.payment-detail-row');
            detailRows.forEach(row => {
                const totalInput = row.querySelector('input[name*="total"]');
                if (totalInput && !totalInput.disabled) {
                    const rawTotal = totalInput.value ? totalInput.value.replace(/\./g, '') : 0;
                    totalAmount += parseFloat(rawTotal) || 0;
                }
            });
        }
    }
    
    if (window.isWarrantyOrder) {
        totalAmount = 0;
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
    
    const discountPercentEl = document.getElementById('discount_percent');
    const vatPercentEl = document.getElementById('vat_percent');
    const discountAmountEl = document.getElementById('discount-amount');
    const vatAmountEl = document.getElementById('vat-amount');

    const discountPercent = discountPercentEl ? (parseFloat(discountPercentEl.value) || 0) : 0;
    const vatPercent = vatPercentEl ? (parseFloat(vatPercentEl.value) || 0) : 0;

    const rawDiscountAmount = roundedTotalAmount * (discountPercent / 100);
    const discountAmount = Math.round(rawDiscountAmount / 1000) * 1000;
    
    const rawVatAmount = (roundedTotalAmount - discountAmount) * (vatPercent / 100);
    const vatAmount = Math.round(rawVatAmount / 1000) * 1000;
    
    const finalTotalAmount = roundedTotalAmount - discountAmount + vatAmount;

    if (discountAmountEl) discountAmountEl.textContent = '-' + discountAmount.toLocaleString('vi-VN') + ' VNĐ';
    if (vatAmountEl) vatAmountEl.textContent = '+' + vatAmount.toLocaleString('vi-VN') + ' VNĐ';

    if (grandTotalEl) grandTotalEl.textContent = finalTotalAmount.toLocaleString('vi-VN') + ' VNĐ';


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
                const scope = getOrderSuppliesStorageScope(panel);
                if (scope) {
                    writeOrderSuppliesStorageItem(`order_supplies_height:${scope}`, '');
                }
                panel.querySelectorAll('[data-order-supplies-table-scroll]').forEach(w => {
                    w.style.height = '';
                });
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

function applyRowStyleHeight(row, height) {
    const heightStr = `${height}px`;
    row.style.setProperty('height', heightStr, 'important');
    row.style.setProperty('min-height', heightStr, 'important');
    row.style.setProperty('max-height', heightStr, 'important');

    Array.from(row.cells).forEach(cell => {
        cell.style.setProperty('height', heightStr, 'important');
        cell.style.setProperty('min-height', heightStr, 'important');
        cell.style.setProperty('max-height', heightStr, 'important');
        
        const controls = cell.querySelectorAll('.form-control, .form-select, .row-index, .detail-index, td > .flex');
        controls.forEach(ctrl => {
            ctrl.style.setProperty('height', heightStr, 'important');
            ctrl.style.setProperty('min-height', heightStr, 'important');
            if (!ctrl.matches('textarea')) {
                ctrl.style.setProperty('max-height', heightStr, 'important');
            }
        });
    });
}

function initOrderSuppliesRowResize(root = document) {
    const tables = root.querySelectorAll([
        '#order-supplies-container .order-supply-row table',
        '#glass-supplies-container .order-supply-row table',
        '#min-late-supplies-container .order-supply-row table',
        'table[data-order-resize-group="min_late_payment"]'
    ].join(', '));

    tables.forEach((table) => {
        const panel = getOrderSuppliesPanel(table);
        if (!panel) return;
        const scope = getOrderSuppliesStorageScope(panel);
        
        const rows = table.querySelectorAll('tbody tr.order-item-row, tbody tr.payment-detail-row');
        rows.forEach((row, rowIndex) => {
            const firstCell = row.cells[0];
            if (!firstCell) return;

            if (window.getComputedStyle(firstCell).position === 'static') {
                firstCell.style.position = 'relative';
            }

            if (firstCell.querySelector('.order-row-height-resize-handle')) {
                const storedHeight = readOrderSuppliesStorageItem(`order_row_height:${scope}_row_${rowIndex}`);
                if (storedHeight) {
                    applyRowStyleHeight(row, storedHeight);
                }
                return;
            }

            const handle = document.createElement('div');
            handle.className = 'order-row-height-resize-handle';
            handle.setAttribute('title', 'Kéo cạnh dưới để thay đổi chiều cao hàng');

            firstCell.appendChild(handle);

            const storedHeight = readOrderSuppliesStorageItem(`order_row_height:${scope}_row_${rowIndex}`);
            if (storedHeight) {
                applyRowStyleHeight(row, storedHeight);
            }

            handle.addEventListener('pointerdown', function(e) {
                if (e.button !== undefined && e.button !== 0) return;
                e.preventDefault();
                e.stopPropagation();

                const startY = e.clientY;
                const startHeight = row.offsetHeight;

                document.body.classList.add('order-row-resizing');
                document.body.style.cursor = 'row-resize';

                const onPointerMove = (moveEvent) => {
                    const deltaY = moveEvent.clientY - startY;
                    const nextHeight = Math.max(30, startHeight + deltaY);
                    applyRowStyleHeight(row, nextHeight);
                };

                const stopResize = () => {
                    document.body.classList.remove('order-row-resizing');
                    document.body.style.cursor = '';
                    document.removeEventListener('pointermove', onPointerMove);
                    document.removeEventListener('pointerup', stopResize);
                    document.removeEventListener('pointercancel', stopResize);

                    writeOrderSuppliesStorageItem(`order_row_height:${scope}_row_${rowIndex}`, String(row.offsetHeight));
                };

                document.addEventListener('pointermove', onPointerMove);
                document.addEventListener('pointerup', stopResize);
                document.addEventListener('pointercancel', stopResize);
            });
        });
    });
}
window.initOrderSuppliesRowResize = initOrderSuppliesRowResize;

document.addEventListener('DOMContentLoaded', () => {
    initOrderSuppliesZoom();
    initOrderSuppliesVisibleRows();
    initOrderSuppliesFullscreen();
    initOrderSuppliesRowResize();

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

    const supplyRow = table.closest('.order-supply-row');
    const isAccessory = supplyRow && (supplyRow.dataset.isAccessory === '1' || supplyRow.querySelector('input[name*="[supply_name]"]')?.value === 'Phụ kiện');

    // Gom các bảng cùng loại để co giãn đồng bộ giữa bảng số 1, 2, 3...
    const container = table.closest('#order-supplies-container, #glass-supplies-container, #min-late-supplies-container');
    if (!container) return null;

    let groupKey = null;

    if (container.id === 'order-supplies-container') {
        groupKey = isAccessory ? 'acrylic_accessory' : 'acrylic';
    } else if (container.id === 'glass-supplies-container') {
        groupKey = isAccessory ? 'glass_accessory' : 'glass';
    } else if (container.id === 'min-late-supplies-container') {
        groupKey = isAccessory ? 'min_late_accessory' : 'min_late';
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

        // Chỉ map các cell hiển thị (bỏ qua cell bị ẩn display: none hoặc class hidden)
        const visibleCells = Array.from(row.cells).filter(cell => {
            return !cell.classList.contains('hidden') && cell.style.display !== 'none';
        });

        visibleCells.forEach((cell) => {
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

            const visibleCells = Array.from(row.cells).filter(cell => {
                return !cell.classList.contains('hidden') && cell.style.display !== 'none';
            });

            visibleCells.forEach((cell) => {
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

        // Dòng/vật tư mới sinh bằng JS cũng cần được gắn lại chỉ số cột và hàng.
        window.requestAnimationFrame(() => {
            initOrderColumnResize(document);
            initOrderSuppliesRowResize(document);
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
    initOrderSuppliesRowResize(document);
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
            
            // Check debt on page load for existing selected customer
            if (customerSelect.value) {
                fetchCustomerDebt(customerSelect.value);
            }
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
            const deliveryDays = parseFloat(deliveryDaysInput.value) || 0;
            const deadline = new Date(orderDate.getTime() + deliveryDays * 24 * 60 * 60 * 1000);
            
            // Format for datetime-local: YYYY-MM-DDTHH:mm
            const offset = deadline.getTimezoneOffset() * 60000;
            const localISOTime = (new Date(deadline - offset)).toISOString().slice(0, 16);
            deadlineInput.value = localISOTime;
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
    if (!el.matches('.order-supply-row table input, .order-supply-row table select, .order-supply-row table textarea, .payment-detail-row input, .payment-detail-row select, .payment-detail-row textarea')) return;

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

<script>
// Global helper to fix Chromium border-collapse bug on sticky cells
window.fixChromeTableBorders = function(table) {
    // if (!table || table.dataset.borderFixPending) return;
    // table.dataset.borderFixPending = '1';
    // requestAnimationFrame(() => {
    //     table.style.setProperty('border-collapse', 'separate', 'important');
    //     table.offsetHeight; // force repaint without looping entire table
    //     table.style.removeProperty('border-collapse');
    //     delete table.dataset.borderFixPending;
    // });
};
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
        formData.append('_token', '{{ csrf_token() }}');
        
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

// JSON Serializer for bypassing PHP max_input_vars limit
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('order-form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        if (e.defaultPrevented) return;

        try {
            // Serialize supplies & items into JSON
            const supplies = serializeSupplies();
            if (supplies) {
                document.getElementById('supplies-json-input').value = JSON.stringify(supplies);
                
                // Disable native inputs to bypass PHP max_input_vars limit
                const container = document.getElementById('order-supplies-container') 
                               || document.getElementById('glass-supplies-container')
                               || document.getElementById('min-late-supplies-container');
                if (container) {
                    const inputs = container.querySelectorAll('input[name^="supplies["], select[name^="supplies["], textarea[name^="supplies["]');
                    inputs.forEach(input => {
                        input.disabled = true;
                    });
                    
                    // Re-enable in the next cycle in case submission is aborted (e.g. invalid HTML5 validation or other handlers)
                    setTimeout(() => {
                        inputs.forEach(input => {
                            input.disabled = false;
                        });
                    }, 500);
                }
            }
        } catch (err) {
            console.error('Lỗi mã hóa dữ liệu vật tư:', err);
        }
    });

    function serializeSupplies() {
        const container = document.getElementById('order-supplies-container') 
                       || document.getElementById('glass-supplies-container')
                       || document.getElementById('min-late-supplies-container');
        if (!container) return null;

        const supplies = [];
        const supplyRows = container.querySelectorAll('.order-supply-row');
        
        supplyRows.forEach((supplyRow) => {
            // Check if this supply section is disabled (e.g. from hidden tabs)
            const firstInput = supplyRow.querySelector('input:not([type="hidden"]), select');
            if (firstInput && firstInput.disabled) return;

            const supply = {
                id: supplyRow.getAttribute('data-supply-id') || null,
                order_supply_code: supplyRow.querySelector('[name*="[order_supply_code]"]')?.value || '',
                supply_name: supplyRow.querySelector('[name*="[supply_name]"]')?.value || '',
                quantity: parseFloat(supplyRow.querySelector('[name*="[quantity]"]:not([name*="items"])')?.value) || 0,
                items: []
            };

            // If we are using TomSelect, get value from it or fallback to DOM value
            const codeSelect = supplyRow.querySelector('.order-supply-code-select');
            if (codeSelect && codeSelect.tomselect) {
                supply.order_supply_code = codeSelect.tomselect.getValue();
            }

            const itemRows = supplyRow.querySelectorAll('.order-item-row');
            itemRows.forEach((itemRow) => {
                const item = {
                    id: itemRow.getAttribute('data-item-id') || null
                };

                // Gather all inputs inside the item row
                itemRow.querySelectorAll('input, select, textarea').forEach(input => {
                    const name = input.getAttribute('name');
                    if (!name) return;

                    // Extract the field key, e.g. "product_name" from "supplies[0][items][1][product_name]"
                    const match = name.match(/\[items\]\[\d+\]\[([^\]]+)\]/);
                    if (match) {
                        const key = match[1];
                        let val = input.value;
                        
                        // Parse values based on input type
                        if (input.type === 'number') {
                            val = val === '' ? '' : parseFloat(val);
                        } else if (input.type === 'hidden' && (key === 'is_labor' || key.startsWith('offset_') || key.startsWith('mill_'))) {
                            val = val === '' ? null : parseInt(val);
                        }
                        item[key] = val;
                    }
                });
                
                // Add default is_labor if not present
                if (itemRow.dataset.isLabor === '1') {
                    item.is_labor = 1;
                }
                
                // For min_late edge gluing, it is nested: edge_gluing[height_1], etc.
                // We need to group them into a nested object if name contains "edge_gluing"
                const gluingInputs = itemRow.querySelectorAll('[name*="[edge_gluing]"]');
                if (gluingInputs.length > 0) {
                    item.edge_gluing = {};
                    gluingInputs.forEach(input => {
                        const name = input.getAttribute('name');
                        const keyMatch = name.match(/\[edge_gluing\]\[([^\]]+)\]/);
                        if (keyMatch) {
                            item.edge_gluing[keyMatch[1]] = input.value || '';
                        }
                    });
                }

                // For min_late size, it is height and width
                if (item.height !== undefined && item.width !== undefined) {
                    item.size = {
                        height: item.height,
                        width: item.width
                    };
                }

                supply.items.push(item);
            });

            supplies.push(supply);
        });

        return supplies;
    }
});

document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('focusin', function(e) {
        if (e.target && e.target.tagName === 'INPUT' && e.target.type === 'number' && e.target.closest('.order-item-row, .payment-detail-row')) {
            e.target.type = 'text';
            e.target.inputMode = 'numeric';
            e.target.dataset.wasNumber = 'true';
        }
    });

    // Table arrow key navigation and Enter handling
    document.addEventListener('keydown', function(e) {
        const key = e.key;
        const currentEl = e.target;
        
        // Handle Enter key on product-bevel-input to open the select options
        if (key === 'Enter' && currentEl.classList.contains('product-bevel-input')) {
            e.preventDefault();
            const td = currentEl.closest('td');
            if (td) {
                const selectEl = td.querySelector('.product-bevel-select');
                if (selectEl && typeof selectEl.showPicker === 'function') {
                    try {
                        selectEl.showPicker();
                    } catch (err) {
                        console.error("Browser doesn't support showPicker on this element", err);
                    }
                }
            }
            return;
        }

        if (!['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(key)) return;

        if (!currentEl.matches('.order-item-row input:not([type="hidden"]), .order-item-row select, .order-item-row textarea, .payment-detail-row input:not([type="hidden"]), .payment-detail-row select, .payment-detail-row textarea')) return;

        // Allow default Left/Right behavior inside text inputs and textareas unless at boundaries
        if ((key === 'ArrowLeft' || key === 'ArrowRight') && (currentEl.tagName === 'INPUT' || currentEl.tagName === 'TEXTAREA')) {
            if (currentEl.tagName === 'TEXTAREA' || currentEl.type === 'text') {
                if (key === 'ArrowLeft' && currentEl.selectionStart > 0) return;
                if (key === 'ArrowRight' && currentEl.selectionEnd < currentEl.value.length) return;
            }
        }

        // Allow default Up/Down behavior inside textareas unless at boundaries (first/last line)
        if ((key === 'ArrowUp' || key === 'ArrowDown') && currentEl.tagName === 'TEXTAREA') {
            const text = currentEl.value;
            const cursor = currentEl.selectionStart;
            if (key === 'ArrowUp') {
                const isOnFirstLine = text.lastIndexOf('\n', cursor - 1) === -1;
                if (!isOnFirstLine) return;
            } else if (key === 'ArrowDown') {
                const isOnLastLine = text.indexOf('\n', cursor) === -1;
                if (!isOnLastLine) return;
            }
        }

        const td = currentEl.closest('td');
        const tr = currentEl.closest('tr.order-item-row, tr.payment-detail-row');
        if (!td || !tr) return;

        const tbody = tr.closest('tbody');
        if (!tbody) return;
        
        const allTrs = Array.from(tbody.querySelectorAll('tr.order-item-row, tr.payment-detail-row'));
        const trIndex = allTrs.indexOf(tr);
        
        const allTds = Array.from(tr.children);
        const tdIndex = allTds.indexOf(td);

        let targetEl = null;

        if (key === 'ArrowUp' || key === 'ArrowDown') {
            e.preventDefault(); // prevent scrolling or number incrementing
            let targetTrIndex = key === 'ArrowUp' ? trIndex - 1 : trIndex + 1;
            
            while (targetTrIndex >= 0 && targetTrIndex < allTrs.length) {
                const targetTr = allTrs[targetTrIndex];
                const targetTd = targetTr.children[tdIndex];
                if (targetTd) {
                    targetEl = targetTd.querySelector('input:not([type="hidden"]):not([disabled]):not([readonly]), select:not([disabled]), textarea:not([disabled]):not([readonly])');
                    if (targetEl) break;
                }
                targetTrIndex = key === 'ArrowUp' ? targetTrIndex - 1 : targetTrIndex + 1;
            }
        } else if (key === 'ArrowLeft' || key === 'ArrowRight') {
            let nextTdIndex = key === 'ArrowLeft' ? tdIndex - 1 : tdIndex + 1;
            while(nextTdIndex >= 0 && nextTdIndex < allTds.length) {
                const targetTd = allTds[nextTdIndex];
                targetEl = targetTd.querySelector('input:not([type="hidden"]):not([disabled]):not([readonly]), select:not([disabled]), textarea:not([disabled]):not([readonly])');
                if (targetEl) break;
                nextTdIndex = key === 'ArrowLeft' ? nextTdIndex - 1 : nextTdIndex + 1;
            }
        }

        if (targetEl) {
            e.preventDefault();
            if (targetEl.tomselect) {
                targetEl.tomselect.focus();
            } else {
                targetEl.focus();
                if (targetEl.tagName === 'INPUT' || targetEl.tagName === 'TEXTAREA') {
                    targetEl.select();
                }
            }
        }
    });

    // Run order summary update once on page load to initialize auto-calculated fields like supply quantities
    setTimeout(() => {
        if (typeof updateOrderSummary === 'function') {
            updateOrderSummary();
        }
    }, 100);
});
</script>

<x-modal name="modal-shortcuts" maxWidth="2xl" :hasBackdrop="true" :transparent="false">
    <div class="p-6 flex flex-col" style="max-height: 85vh;">
        <div class="flex items-center justify-between border-b border-neutral-100 pb-4 shrink-0">
            <h5 class="font-bold text-lg text-neutral-800 flex items-center gap-2 m-0">
                <iconify-icon icon="lucide:lightbulb" class="text-primary-500"></iconify-icon>
                Hướng dẫn & Mẹo sử dụng
            </h5>
            <button type="button" onclick="closeModal('modal-shortcuts')" class="w-8 h-8 rounded-lg hover:bg-neutral-100 flex items-center justify-center text-neutral-400 hover:text-danger-500 transition-colors">
                <iconify-icon icon="lucide:x" class="text-lg"></iconify-icon>
            </button>
        </div>
        
        <div class="space-y-4 overflow-y-auto pr-2 flex-1 mt-4">
            <div class="flex items-start gap-3 p-3 rounded-lg bg-neutral-50 border border-neutral-100">
                <div class="px-2.5 py-1.5 bg-white border border-neutral-200 rounded text-xs font-mono font-bold text-neutral-700 shadow-sm flex items-center gap-1 shrink-0">Ctrl + <iconify-icon icon="lucide:mouse"></iconify-icon> Lăn chuột</div>
                <div>
                    <p class="font-semibold text-sm text-neutral-800 m-0">(Khi trỏ vào bảng vật tư)</p>
                    <p class="text-sm text-neutral-500 m-0">Thu nhỏ / Phóng to bảng vật tư để nhìn được nhiều cột hơn hoặc nhìn rõ chữ hơn.</p>
                </div>
            </div>

            <div class="flex items-start gap-3 p-3 rounded-lg bg-neutral-50 border border-neutral-100">
                <div class="px-2.5 py-1.5 bg-white border border-neutral-200 rounded text-xs font-mono font-bold text-neutral-700 shadow-sm flex items-center gap-1 shrink-0">Ctrl + <iconify-icon icon="lucide:mouse"></iconify-icon> Lăn chuột</div>
                <div>
                    <p class="font-semibold text-sm text-neutral-800 m-0">(Khi mở popup xem ảnh to)</p>
                    <p class="text-sm text-neutral-500 m-0">Thay đổi kích thước thực của ảnh đang xem để soi rõ chi tiết.</p>
                </div>
            </div>

            <div class="flex items-start gap-3 p-3 rounded-lg bg-neutral-50 border border-neutral-100">
                <div class="px-2.5 py-1.5 bg-white border border-neutral-200 rounded text-xs font-mono font-bold text-neutral-700 shadow-sm shrink-0">Ctrl + Alt</div>
                <div>
                    <p class="font-semibold text-sm text-neutral-800 m-0">Ẩn / Hiện tạm thời các popup ảnh</p>
                    <p class="text-sm text-neutral-500 m-0">Nhấn giữ để tạm thời ẩn tất cả các ảnh đang mở (để nhìn bảng dữ liệu phía sau). Buông phím ra ảnh sẽ hiện lại.</p>
                </div>
            </div>

            <div class="flex items-start gap-3 p-3 rounded-lg bg-neutral-50 border border-neutral-100">
                <div class="px-2.5 py-1.5 bg-white border border-neutral-200 rounded text-xs font-mono font-bold text-neutral-700 shadow-sm shrink-0">Ctrl + V</div>
                <div>
                    <p class="font-semibold text-sm text-neutral-800 m-0">Dán ảnh trực tiếp</p>
                    <p class="text-sm text-neutral-500 m-0">Copy ảnh từ Zalo hoặc phần mềm khác, bấm vào ô khu vực tải ảnh và ấn Ctrl+V để dán ảnh vào đơn hàng thật nhanh.</p>
                </div>
            </div>

            <div class="flex items-start gap-3 p-3 rounded-lg bg-neutral-50 border border-neutral-100">
                <div class="px-2.5 py-1.5 bg-white border border-neutral-200 rounded text-xs font-mono font-bold text-neutral-700 shadow-sm flex items-center gap-1 shrink-0"><iconify-icon icon="lucide:columns"></iconify-icon> Kéo dãn cột</div>
                <div>
                    <p class="font-semibold text-sm text-neutral-800 m-0">Tự động lưu độ rộng cột</p>
                    <p class="text-sm text-neutral-500 m-0">Bạn có thể dùng chuột rê vào vách ngăn giữa các tiêu đề cột để kéo dãn độ rộng cho vừa mắt. Hệ thống sẽ tự động lưu lại cấu hình này cho các lần nhập sau.</p>
                </div>
            </div>
            
            <div class="flex items-start gap-3 p-3 rounded-lg bg-neutral-50 border border-neutral-100">
                <div class="px-2.5 py-1.5 bg-white border border-neutral-200 rounded text-xs font-mono font-bold text-neutral-700 shadow-sm flex items-center gap-1 shrink-0"><iconify-icon icon="lucide:move"></iconify-icon> Kéo thả chuột</div>
                <div>
                    <p class="font-semibold text-sm text-neutral-800 m-0">Kéo thả ảnh đang xem</p>
                    <p class="text-sm text-neutral-500 m-0">Khi mở popup xem ảnh to, bạn có thể click giữ chuột để kéo bức ảnh sang vị trí khác trên màn hình.</p>
                </div>
            </div>

            <div class="flex items-start gap-3 p-3 rounded-lg bg-neutral-50 border border-neutral-100">
                <div class="px-2.5 py-1.5 bg-white border border-neutral-200 rounded text-xs font-mono font-bold text-neutral-700 shadow-sm flex items-center gap-1 shrink-0"><iconify-icon icon="lucide:mouse-pointer-click"></iconify-icon> Click đúp (vào ảnh)</div>
                <div>
                    <p class="font-semibold text-sm text-neutral-800 m-0">Thu / Phóng kích thước ảnh</p>
                    <p class="text-sm text-neutral-500 m-0">Click đúp (nhấp chuột 2 lần) vào ảnh để ảnh tự động quay trở về kích thước ban đầu.</p>
                </div>
            </div>

            <div class="flex items-start gap-3 p-3 rounded-lg bg-neutral-50 border border-neutral-100">
                <div class="px-2.5 py-1.5 bg-white border border-neutral-200 rounded text-xs font-mono font-bold text-neutral-700 shadow-sm flex items-center gap-1 shrink-0"><iconify-icon icon="lucide:list-ordered"></iconify-icon> Hiển thị dòng</div>
                <div>
                    <p class="font-semibold text-sm text-neutral-800 m-0">Tùy chỉnh số lượng dòng (5, 10, 25...)</p>
                    <p class="text-sm text-neutral-500 m-0">Chọn hiển thị 5, 10, 25... dòng để phân trang bảng dữ liệu. Việc này giúp giao diện gọn gàng hơn, hỗ trợ bạn dễ dàng theo dõi, kiểm tra và quản lý khi đơn hàng có số lượng sản phẩm lớn.</p>
                </div>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-neutral-100 flex justify-end shrink-0">
            <button type="button" onclick="closeModal('modal-shortcuts')" class="btn btn-primary px-5 py-2 rounded-lg text-sm font-semibold transition-all">
                Đã hiểu
            </button>
        </div>
    </div>
</x-modal>

<style>
    .order-row-height-resize-handle:hover {
        background-color: transparent !important;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>


