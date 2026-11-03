@php
    $currentOrderType = $orderType ?? ($acrylicOrder->type ?? old('type', 'acrylic'));
    $isDraftCreate = $isDraftCreate ?? false;
    $orderTypeLabels = [
        'acrylic' => 'Acrylic',
        'glass' => 'Glass',
        'min_late' => 'Min Late',
        'Flywood' => 'Flywood',
        'service' => 'Dịch vụ & Vật tư',
    ];
    $isEdit = isset($acrylicOrder) && !$isDraftCreate;
    $thisOrderId = $isEdit ? $acrylicOrder->id : null;
    $thisOrderPaid = $isEdit && $acrylicOrder->orderPayments ? $acrylicOrder->orderPayments->sum('amount') : 0;
    $thisOrderOldAmount = $isEdit ? round($acrylicOrder->total_amount, -3) : 0;
    $thisOrderOldStatusWasValid = $isEdit ? !in_array($acrylicOrder->status, ['draft', 'pending', 'cancelled']) : false;
    // Tính toán công nợ khách hàng theo thứ tự thời gian khớp 100% với _preview_modal.blade.php và OrderController::getExportData (Phương án 2)
    $initialDebtInfo = ($isEdit && $acrylicOrder->customer) 
        ? $acrylicOrder->customer->getDebtInfoForOrder($acrylicOrder) 
        : null;
@endphp
<script>
    window.currentOrderId = @json($thisOrderId);
    window.thisOrderPaid = @json($thisOrderPaid);
    window.thisOrderOldAmount = @json($thisOrderOldAmount);
    window.thisOrderOldStatusWasValid = @json($thisOrderOldStatusWasValid);
    window.initialDebtInfo = @json($initialDebtInfo);
    window.customerOldDebt = {{ $initialDebtInfo ? $initialDebtInfo['old_debt'] : 0 }};
    window.customerTodayPaid = {{ $initialDebtInfo ? $initialDebtInfo['this_order_paid'] : 0 }};
    window.orderPaidAmt = @json($thisOrderPaid);
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
        @if(isset($acrylicOrder))
            <input type="hidden" name="order_id" value="{{ $acrylicOrder->id }}">
        @endif
        <input type="hidden" name="supplies_json" id="supplies-json-input">
        <input type="hidden" name="action" id="order-form-action" value="save">
        <div class="p-4">
            @if(isset($acrylicOrder) && !empty($acrylicOrder->edit_reason))
                <div class="mb-4 p-4 rounded-xl border border-amber-300 bg-amber-50 text-amber-900 shadow-sm flex items-start gap-3">
                    <iconify-icon icon="lucide:alert-triangle" class="text-2xl text-amber-600 mt-0.5 flex-shrink-0"></iconify-icon>
                    <div class="flex-1">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <span class="font-bold text-base text-amber-950">Kế toán yêu cầu sửa đơn hàng (Đã mở khóa sửa)</span>
                            <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-amber-200 text-amber-800">Cần điều chỉnh</span>
                        </div>
                        <div class="text-sm mt-1.5 whitespace-pre-line text-neutral-800 font-medium bg-white p-3 rounded-lg border border-amber-200 shadow-2xs">
                            <strong class="text-amber-900">Lý do yêu cầu sửa:</strong> {{ $acrylicOrder->edit_reason }}
                        </div>
                        <div class="text-xs text-amber-700 font-medium italic mt-2">
                            * Sau khi bạn điều chỉnh và bấm "Lưu đơn hàng", đơn hàng sẽ tự động chuyển về trạng thái Chờ duyệt để Kế toán duyệt lại.
                        </div>
                    </div>
                </div>
            @endif
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
                                <div class="flex items-center justify-between mb-2">
                                    <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider block m-0">Mã đơn hàng <span class="text-danger-500">*</span></label>
                                    <span class="text-[11px] text-neutral-400">Tự tăng hoặc nhập mã tùy chỉnh (VD: 26.5178)</span>
                                </div>
                                <input type="text" name="order_code" id="order-code-input" 
                                    class="form-control rounded-lg border-neutral-300 font-bold text-neutral-800 focus:border-primary-500 focus:ring-primary-500 text-sm" 
                                    placeholder="VD: 26.5178" 
                                    value="{{ old('order_code', isset($acrylicOrder) ? $acrylicOrder->order_code : ($nextOrderCode ?? '')) }}" required>
                                @error('order_code')
                                    <div class="text-danger-500 text-xs mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider block m-0">Khách hàng <span class="text-danger-500">*</span></label>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="selectNhapKhoCustomer()" class="text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-300 px-2.5 py-1 rounded-lg text-xs font-semibold inline-flex items-center gap-1 transition shadow-sm" title="Chọn khách hàng là Nhập kho">
                                            <iconify-icon icon="lucide:warehouse" class="text-sm text-amber-600"></iconify-icon>
                                            Nhập kho
                                        </button>
                                        <button type="button" onclick="openModal('quickCreateCustomerModal')" class="text-primary-600 hover:text-primary-700 text-xs font-semibold inline-flex items-center gap-1 transition">
                                            <iconify-icon icon="lucide:user-plus" class="text-sm"></iconify-icon>
                                            + Thêm khách
                                        </button>
                                    </div>
                                </div>
                                @php
                                    $currentUser = auth()->user();
                                    if ($currentUser && !$currentUser->canViewAllMarketGroups()) {
                                        $userMarketGroupIds = $currentUser->marketGroups()->pluck('market_groups.id');
                                        $allowedCustomers = \App\Models\Customer::with('marketGroup')->whereIn('market_group_id', $userMarketGroupIds)->orderBy('name')->get();
                                        $availableMarketGroups = $currentUser->marketGroups()->orderBy('name')->get();
                                    } else {
                                        $allowedCustomers = \App\Models\Customer::with('marketGroup')->orderBy('name')->get();
                                        $availableMarketGroups = \App\Models\MarketGroup::orderBy('name')->get();
                                    }

                                    $nhapKhoCustomer = \App\Models\Customer::getNhapKhoCustomer();
                                    if ($nhapKhoCustomer) {
                                        $allowedCustomers = $allowedCustomers->reject(fn($c) => $c->id === $nhapKhoCustomer->id);
                                        $allowedCustomers->prepend($nhapKhoCustomer);
                                    }

                                    if (isset($acrylicOrder) && $acrylicOrder->customer && !$allowedCustomers->contains('id', $acrylicOrder->customer_id)) {
                                        if ($currentUser && ($currentUser->canViewAllMarketGroups() || $acrylicOrder->customer->isAccessibleBy($currentUser))) {
                                            $allowedCustomers->push($acrylicOrder->customer);
                                        }
                                    }
                                @endphp
                                <select name="customer_id" id="customer-select" class="" onchange="fillCustomerInfo(this.value)">
                                    <option value="">-- Chọn khách hàng --</option>
                                    @foreach($allowedCustomers as $customer)
                                    <option value="{{ $customer->id }}" data-market-group-id="{{ $customer->market_group_id }}" {{ isset($acrylicOrder) && $acrylicOrder?->customer_id == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->customer_code }} - {{ $customer->name }} {{ $customer->marketGroup ? '('.$customer->marketGroup->name.')' : '' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Nhóm thị trường</label>
                                <select name="market_group_id" id="market_group_select" class="form-select rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 w-full text-sm">
                                    <option value="">-- Chưa phân nhóm --</option>
                                    @foreach($availableMarketGroups as $mg)
                                        <option value="{{ $mg->id }}" {{ (isset($acrylicOrder) && $acrylicOrder->customer?->market_group_id == $mg->id) ? 'selected' : '' }}>
                                            {{ $mg->name }} ({{ $mg->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group md:col-span-1">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Tên công trình <span class="text-danger-500">*</span></label>
                                <input type="text" name="customer_name" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập tên công trình / dự án" required value="{{ old('customer_name', $acrylicOrder?->customer_name ?? '') }}">
                            </div>
                            <input type="hidden" name="type" value="{{ $currentOrderType }}">
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập số điện thoại" value="{{ old('phone', $acrylicOrder?->phone ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Ngày giờ chốt đơn</label>
                                <input type="datetime-local" id="order_date" name="order_date" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" value="{{ old('order_date', (isset($acrylicOrder) && $acrylicOrder->status !== 'draft' && $acrylicOrder->order_date) ? \Carbon\Carbon::parse($acrylicOrder->order_date)->timezone('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i') : \Carbon\Carbon::now('Asia/Ho_Chi_Minh')->format('Y-m-d\TH:i')) }}">
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
                                    <div id="existing-attachment-item-{{ $index }}" class="existing-attachment-item flex items-center justify-between p-2 border border-neutral-100 rounded-xl bg-neutral-50/50 hover:bg-neutral-50 transition-colors">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ route('orders.image', ['filename' => basename($image)]) }}" class="w-12 h-12 rounded-lg object-cover border border-neutral-200 shadow-sm cursor-pointer" onclick="openModal('modal-existing-attachment-{{ $index }}')">
                                            <div>
                                                <p class="text-xs font-semibold text-neutral-700 truncate max-w-[120px]">{{ basename($image) }}</p>
                                                <button type="button" onclick="openModal('modal-existing-attachment-{{ $index }}')" class="text-[11px] text-primary-500 hover:text-primary-700 font-bold flex items-center gap-1 mt-0.5">
                                                    <iconify-icon icon="lucide:eye" class="text-sm"></iconify-icon> Xem chi tiết
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" onclick="deleteAttachment('{{ $index }}', '{{ $image }}', this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1.5" title="Xóa ảnh">
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
                    @elseif(in_array($currentOrderType, ['min_late', 'Flywood']))
                        @include('orders.min_late')
                    @elseif($currentOrderType === 'glass')
                        @include('orders.glass')
                    @elseif($currentOrderType === 'service')
                        @include('orders.service')
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
                    @if($acrylicOrder->accountant_approved_by)
                    <button type="submit" name="status" value="transferred" class="btn bg-violet-50 text-violet-600 hover:bg-violet-100 border border-violet-200 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all" onclick="return confirm('Kỹ thuật xác nhận duyệt & chuyển đơn hàng sang sản xuất?')">{{ !empty($acrylicOrder->edit_reason) ? 'Chuyển SX lại' : 'Kỹ thuật duyệt & Chuyển SX' }}</button>
                    @else
                    <button type="button" onclick="alert('Đơn hàng cần được Kế toán duyệt trước khi Kỹ thuật duyệt chuyển sản xuất!')" class="btn bg-neutral-100 text-neutral-400 border border-neutral-300 px-5 py-2.5 rounded-lg text-sm font-semibold cursor-not-allowed opacity-75" title="Đơn hàng cần được Kế toán duyệt trước khi Kỹ thuật duyệt chuyển sản xuất">{{ !empty($acrylicOrder->edit_reason) ? 'Chuyển SX lại' : 'Kỹ thuật duyệt & Chuyển SX' }}</button>
                    @endif
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
    const nhapKhoId = {{ $nhapKhoCustomer ? $nhapKhoCustomer->id : 'null' }};
    if (!customerId || !/^\d+$/.test(customerId) || (nhapKhoId && customerId == nhapKhoId)) {
        const debtCard = document.getElementById('customer-debt-card');
        if (debtCard) {
            debtCard.hidden = true;
            debtCard.classList.add('hidden');
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
            if (data.debt_info_for_order) {
                window.initialDebtInfo = data.debt_info_for_order;
                window.customerOldDebt = data.debt_info_for_order.old_debt || 0;
                window.customerTodayPaid = data.debt_info_for_order.this_order_paid || 0;
            }
            if (data.unpaid_debt_summary) {
                if (!data.debt_info_for_order) {
                    window.customerOldDebt = data.unpaid_debt_summary.total_debt || 0;
                    window.customerOldPaid = data.unpaid_debt_summary.total_paid || 0;
                    window.customerOldAmount = data.unpaid_debt_summary.total_amount || 0;
                }
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

window.nhapKhoCustomerId = {{ $nhapKhoCustomer ? $nhapKhoCustomer->id : 'null' }};

window.updateSupplyInventoryActionStyle = function(selectEl) {
    if (!selectEl) return;
    if (selectEl.value === 'subtract') {
        selectEl.classList.remove('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
        selectEl.classList.add('bg-rose-50', 'text-rose-700', 'border-rose-300');
    } else {
        selectEl.classList.remove('bg-rose-50', 'text-rose-700', 'border-rose-300');
        selectEl.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-300');
    }
};

window.toggleSupplyInventoryActionVisibility = function() {
    const isNK = typeof window.isNhapKhoSelected === 'function' && window.isNhapKhoSelected();
    document.querySelectorAll('.supply-inventory-action-wrap').forEach(function(el) {
        el.style.display = isNK ? 'flex' : 'none';
    });
};

window.isNhapKhoSelected = function() {
    const custSelect = document.getElementById('customer-select');
    if (!custSelect) return false;
    const val = custSelect.value;
    const nhapKhoId = window.nhapKhoCustomerId ? String(window.nhapKhoCustomerId) : null;
    if (nhapKhoId && String(val) === nhapKhoId) {
        return true;
    }
    if (custSelect.tomselect) {
        const item = custSelect.tomselect.getItem(val);
        const text = item ? (item.textContent || '') : '';
        if (text.toLowerCase().includes('nhập kho') || text.toLowerCase().includes('nhap kho')) {
            return true;
        }
    } else if (custSelect.selectedIndex >= 0) {
        const text = custSelect.options[custSelect.selectedIndex]?.text || '';
        if (text.toLowerCase().includes('nhập kho') || text.toLowerCase().includes('nhap kho')) {
            return true;
        }
    }
    const cName = document.querySelector('input[name="customer_name"]')?.value || '';
    if (cName.trim().toLowerCase() === 'nhập kho' || cName.trim().toLowerCase() === 'nhap kho') {
        return true;
    }
    return false;
};

window.applyNhapKhoToAllItems = function() {
    const itemRows = document.querySelectorAll('.order-item-row');
    itemRows.forEach(row => {
        const heightInput = row.querySelector('input[name*="[height]"]');
        const widthInput = row.querySelector('input[name*="[width]"]');
        const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
        
        if (heightInput) heightInput.value = 1220;
        if (widthInput) widthInput.value = 2440;
        if (unitPriceInput) unitPriceInput.value = 0;

        if (typeof calculateTotalPrice === 'function') {
            calculateTotalPrice(row, 'unit_price');
        } else if (typeof calculateMinLateTotalPrice === 'function') {
            if (typeof calculateMinLateRowStats === 'function') {
                calculateMinLateRowStats(row);
            }
            calculateMinLateTotalPrice(row);
        } else if (typeof calculateGlassTotalPrice === 'function') {
            calculateGlassTotalPrice(row, 'unit_price');
        }
    });

    if (typeof updateOrderSummary === 'function') {
        updateOrderSummary();
    }
};

function selectNhapKhoCustomer() {
    const custSelect = document.getElementById('customer-select');
    const nhapKhoId = {{ $nhapKhoCustomer ? $nhapKhoCustomer->id : 'null' }};
    if (custSelect && nhapKhoId) {
        if (custSelect.tomselect) {
            custSelect.tomselect.setValue(nhapKhoId);
        } else {
            custSelect.value = nhapKhoId;
            fillCustomerInfo(nhapKhoId);
        }
        const cNameInput = document.querySelector('input[name="customer_name"]');
        if (cNameInput) {
            cNameInput.value = 'Nhập kho';
        }
        if (typeof window.applyNhapKhoToAllItems === 'function') {
            window.applyNhapKhoToAllItems();
        }
        if (typeof window.toggleSupplyInventoryActionVisibility === 'function') {
            window.toggleSupplyInventoryActionVisibility();
        }
    }
}

window.allAllowedCustomers = @json($allowedCustomers ?? \App\Models\Customer::all());

function fillCustomerInfo(customerId) {
    @if(auth()->check())
    const customers = window.allAllowedCustomers || @json($allowedCustomers ?? \App\Models\Customer::all());
    const customer = customers.find(c => c.id == customerId);
    const cNameInput = document.querySelector('input[name="customer_name"]');
    const mgSelect = document.getElementById('market_group_select');

    if (customer) {
        const isNK = (customer.customer_code === 'NK' || customer.customer_code === 'NHAPKHO' || (customer.name && customer.name.toLowerCase().includes('nhập kho')));
        if (cNameInput && (!cNameInput.value.trim() || cNameInput.value.startsWith('Đơn nháp') || isNK)) {
            cNameInput.value = customer.name;
        }
        if (isNK && typeof window.applyNhapKhoToAllItems === 'function') {
            window.applyNhapKhoToAllItems();
        }
        if (typeof window.toggleSupplyInventoryActionVisibility === 'function') {
            window.toggleSupplyInventoryActionVisibility();
        }
        if (mgSelect) {
            mgSelect.value = customer.market_group_id || '';
        }
        document.querySelector('input[name="phone"]').value = customer.phone || '';
        document.querySelector('textarea[name="address"]').value = customer.address || '';
        const policyEl = document.querySelector('textarea[name="customer_policy"]');
        if (policyEl) {
            policyEl.value = customer.policy || '';
        }
    } else if (customerId && !/^\d+$/.test(customerId)) {
        if (cNameInput && !cNameInput.value.trim()) {
            cNameInput.value = customerId;
        }
        if (mgSelect) {
            mgSelect.value = '';
        }
        document.querySelector('input[name="phone"]').value = '';
        document.querySelector('textarea[name="address"]').value = '';
        const policyEl = document.querySelector('textarea[name="customer_policy"]');
        if (policyEl) {
            policyEl.value = '';
        }
    } else {
        // Clear fields if select is cleared
        if (mgSelect) {
            mgSelect.value = '';
        }
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

// === XEM TRƯỚC ĐƠN HÀNG (DÙNG CHUNG 1 MẪU _preview_modal.blade.php) ===
window.isOrderFormPage = true;

function closePreviewOrder() {
    const modal = document.getElementById('preview-order-modal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

async function previewOrder() {
    const form = document.getElementById('order-form');
    if (!form) return;

    if (typeof refreshOrderDateIfPromotingDraft === 'function') {
        refreshOrderDateIfPromotingDraft();
    }

    // Hiển thị loading overlay
    let loadingEl = document.getElementById('preview-order-loading-indicator');
    if (!loadingEl) {
        loadingEl = document.createElement('div');
        loadingEl.id = 'preview-order-loading-indicator';
        loadingEl.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs transition-opacity';
        loadingEl.innerHTML = `
            <div class="bg-white rounded-2xl p-6 shadow-2xl flex flex-col items-center gap-3">
                <div class="w-8 h-8 border-3 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
                <div class="text-xs font-bold text-neutral-700">Đang chuẩn bị bản xem trước đơn hàng...</div>
            </div>
        `;
        document.body.appendChild(loadingEl);
    }

    try {
        const formData = new FormData(form);

        // Đảm bảo có order_id
        if (!formData.get('order_id')) {
            formData.append('order_id', '{{ $acrylicOrder->id }}');
        }

        // Đóng gói danh sách vật tư vào supplies_json
        if (typeof window.serializeSupplies === 'function') {
            const supplies = window.serializeSupplies();
            if (supplies) {
                formData.set('supplies_json', JSON.stringify(supplies));
            }
        }

        const res = await fetch('{{ route("orders.preview-live") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });

        if (!res.ok) {
            const errData = await res.json().catch(() => ({}));
            throw new Error(errData.message || 'Không thể tải bản xem trước đơn hàng');
        }

        const html = await res.text();

        // Xóa modal xem trước cũ nếu có
        const oldModal = document.getElementById('preview-order-modal');
        if (oldModal) oldModal.remove();

        // Container chèn modal
        let container = document.getElementById('order-preview-modal-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'order-preview-modal-container';
            document.body.appendChild(container);
        }
        container.innerHTML = html;

        // Thực thi các thẻ script trong HTML vừa load
        const scripts = container.querySelectorAll('script');
        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            newScript.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });

        // Mở modal
        if (typeof openPreviewModal === 'function') {
            openPreviewModal();
        } else {
            const modal = document.getElementById('preview-order-modal');
            if (modal) {
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
    } catch (err) {
        console.error('Lỗi xem trước đơn hàng:', err);
        alert(err.message || 'Có lỗi xảy ra khi tạo bản xem trước');
    } finally {
        if (loadingEl) loadingEl.remove();
    }
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
                    const row = input.closest('tr');
                    // Ignore rows that might be visually hidden or disabled (e.g. templates)
                    if (!input.disabled && (!row || !row.classList.contains('hidden'))) {
                        const isLabor = row && (
                            row.dataset.isLabor === '1' ||
                            row.classList.contains('bg-amber-50/60') ||
                            row.querySelector('.labor-badge') != null ||
                            (row.querySelector('input[name*="[product_name]"]')?.value?.trim() === 'Công giả dày') ||
                            (row.querySelector('input[name*="[is_labor]"]')?.value === '1')
                        );
                        if (isLabor) return; // BỎ RA: Không cộng số lượng công giả dày vào tổng số tấm của vật tư!

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
                    
                    table.querySelectorAll(`tbody tr:not(.hidden)`).forEach(row => {
                        const isLabor = row.dataset.isLabor === '1' ||
                            row.classList.contains('bg-amber-50/60') ||
                            row.querySelector('.labor-badge') != null ||
                            (row.querySelector('input[name*="[product_name]"]')?.value?.trim() === 'Công giả dày') ||
                            (row.querySelector('input[name*="[is_labor]"]')?.value === '1');

                        // BỎ RA: Dòng công giả dày KHÔNG cộng số lượng và KHÔNG cộng m2/chiều dài vào tổng bảng!
                        if (isLabor && (field === 'quantity' || field === 'wing_area' || field === 'area_m2' || field === 'molding_length')) {
                            return;
                        }

                        const input = row.querySelector(`input[name*="[${field}]"]`);
                        if (input && !input.disabled && input.type !== 'hidden') {
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

        // Calculate items quantity and area from supplies.items
        const rows = document.querySelectorAll('.order-item-row');
        rows.forEach(row => {
            const qtyInput = row.querySelector('input[name*="quantity"]');
            const heightInput = row.querySelector('input[name*="[size][height]"]') || row.querySelector('input[name*="[height]"]');
            const widthInput = row.querySelector('input[name*="[size][width]"]') || row.querySelector('input[name*="[width]"]');
            if (qtyInput && !qtyInput.disabled) {
                const qty = parseFloat(qtyInput.value) || 0;
                totalItems++;
                totalSheets += qty;
                const h = parseFloat(heightInput ? heightInput.value : 0) || 0;
                const w = parseFloat(widthInput ? widthInput.value : 0) || 0;
                if (h > 0 && w > 0) {
                    totalArea += (h * w * qty) / 1000000;
                }
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
            const isLabor = row.dataset.isLabor === '1' ||
                row.classList.contains('bg-amber-50/60') ||
                row.querySelector('.labor-badge') != null ||
                (row.querySelector('input[name*="[product_name]"]')?.value?.trim() === 'Công giả dày') ||
                (row.querySelector('input[name*="[is_labor]"]')?.value === '1');

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

                // Tiền công vẫn được cộng vào tổng tiền đơn hàng!
                if (!window.isReworkOrder && !window.isWarrantyOrder) {
                    totalAmount += totalPrice;
                }

                // BỎ RA: Không cộng số tấm, số sản phẩm, diện tích m2 của công giả dày vào tổng sản lượng của đơn!
                if (isLabor) {
                    return;
                }

                totalItems++;
                totalSheets += quantity;
                if (height > 0 && width > 0) {
                    totalArea += (height * width * quantity) / 1000000;
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

function deleteAttachment(index, imagePath, btn = null) {
    if (confirm('Bạn có chắc muốn xóa hình ảnh này?')) {
        // 1. Xóa phần tử khỏi giao diện DOM ngay lập tức
        const itemById = document.getElementById(`existing-attachment-item-${index}`);
        if (itemById) {
            itemById.remove();
        } else if (btn) {
            const row = btn.closest('.existing-attachment-item') || btn.closest('.flex.items-center.justify-between') || btn.closest('div');
            if (row) row.remove();
        } else {
            const container = document.getElementById('existing-attachments');
            if (container) {
                const rows = container.querySelectorAll(':scope > div');
                if (rows[index]) rows[index].remove();
            }
        }

        // Đóng và xóa modal popup xem ảnh nếu có
        if (typeof closeModal === 'function') {
            try { closeModal(`modal-existing-attachment-${index}`); } catch (e) {}
        }
        const modalEl = document.getElementById(`modal-existing-attachment-${index}`);
        if (modalEl) modalEl.remove();

        // Đồng bộ xóa thumbnail trong chế độ toàn màn hình (fullscreen clone) nếu đang mở
        const fsClone = document.querySelector('.fullscreen-attachments-clone');
        if (fsClone) {
            const activePanel = document.querySelector('.order-panel-fullscreen') || document.querySelector('.fullscreen-mode');
            if (activePanel && typeof syncAttachmentsToFullscreen === 'function') {
                syncAttachmentsToFullscreen(activePanel, true);
            }
        }
        
        // 2. Ghi nhận đường dẫn ảnh đã xóa vào input hidden để gửi lên server khi cập nhật
        const deleteInput = document.getElementById('delete-attachments');
        if (deleteInput) {
            let deleted = [];
            try {
                deleted = deleteInput.value ? JSON.parse(deleteInput.value) : [];
                if (!Array.isArray(deleted)) deleted = [];
            } catch (e) {
                deleted = [];
            }
            if (!deleted.includes(imagePath)) {
                deleted.push(imagePath);
            }
            deleteInput.value = JSON.stringify(deleted);
        }
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
        calculateDeadline();
    }

    // Tự động làm mới ngày giờ hiện tại khi xác nhận đơn từ đơn nháp sang đơn thường
    const isOrderDraftState = {{ ($isDraftCreate || (isset($acrylicOrder) && $acrylicOrder->status === 'draft')) ? 'true' : 'false' }};
    function refreshOrderDateIfPromotingDraft() {
        if (!isOrderDraftState) return;
        const actionEl = document.getElementById('order-form-action');
        if (actionEl && actionEl.value === 'draft') return;
        const now = new Date();
        const offset = now.getTimezoneOffset() * 60000;
        const localISOTime = (new Date(now.getTime() - offset)).toISOString().slice(0, 16);
        if (orderDateInput) {
            orderDateInput.value = localISOTime;
            calculateDeadline();
        }
    }

    const orderFormElement = document.getElementById('order-form');
    if (orderFormElement) {
        orderFormElement.addEventListener('submit', function() {
            refreshOrderDateIfPromotingDraft();
        });
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

    // =========================================================================
    // HỖ TRỢ COPY - DÁN (CTRL+V) TỪ EXCEL VÀO CÁC BẢNG VẬT TƯ & SẢN PHẨM
    // Cho phép copy 1 cột (hoặc nhiều cột) hàng trăm dòng từ Excel và dán trực tiếp.
    // Tự động thêm dòng mới nếu bảng chưa đủ dòng.
    // =========================================================================
    function cleanExcelNumber(input, val) {
        if (input.type !== 'number') return val;
        let clean = (val || '').trim();
        if (clean === '') return '';
        // Loại bỏ ký tự tiền tệ, khoảng trắng thừa
        clean = clean.replace(/[^\d.,\-]/g, '');
        if (clean === '' || clean === '-') return '';
        
        // Xử lý dấu phân cách hàng nghìn vs thập phân
        if (clean.includes('.') && clean.includes(',')) {
            if (clean.indexOf('.') < clean.indexOf(',')) {
                // Định dạng 1.250.000,50 -> 1250000.50
                clean = clean.replace(/\./g, '').replace(',', '.');
            } else {
                // Định dạng 1,250,000.50 -> 1250000.50
                clean = clean.replace(/,/g, '');
            }
        } else if (clean.includes(',')) {
            // e.g. 1,200 hoặc 150,000
            if (/^\d{1,3}(,\d{3})+$/.test(clean)) {
                clean = clean.replace(/,/g, '');
            } else {
                // Số thập phân kiểu Việt: 12,5 -> 12.5
                clean = clean.replace(',', '.');
            }
        } else if (clean.includes('.')) {
            // e.g. 1.200 hoặc 150.000 trong Excel tiếng Việt
            if (/^\d{1,3}(\.\d{3})+$/.test(clean)) {
                clean = clean.replace(/\./g, '');
            }
        }
        return clean;
    }

    function getRowEditableElements(row) {
        if (!row) return [];
        const all = Array.from(row.querySelectorAll('input:not([type="hidden"]), textarea, select'));
        return all.filter(el => {
            if (el.readOnly || el.disabled) return false;
            if (el.offsetParent === null && el.style.display === 'none') return false;
            if (el.closest('.hidden, [style*="display: none"], [style*="display:none"]')) return false;
            if (el.classList.contains('product-bevel-select')) return false;
            return true;
        });
    }

    function getFieldNameSuffix(el) {
        if (!el) return null;
        const name = el.getAttribute('name');
        if (!name) return null;
        const match = name.match(/\[([a-zA-Z0-9_]+)\](\[[a-zA-Z0-9_]+\])?$/);
        return match ? match[0] : null;
    }

    function getSpecialClassName(el) {
        if (!el) return null;
        if (el.classList.contains('old-height-input')) return 'old-height-input';
        if (el.classList.contains('old-width-input')) return 'old-width-input';
        if (el.classList.contains('display-area-input')) return 'display-area-input';
        if (el.classList.contains('product-bevel-input')) return 'product-bevel-input';
        return null;
    }

    function findTargetElementInRow(targetRow, matcher) {
        if (!targetRow || !matcher) return null;
        if (matcher.className) {
            const found = targetRow.querySelector(`.${matcher.className}`);
            if (found && !found.readOnly && !found.disabled) return found;
        }
        if (matcher.nameSuffix) {
            const found = targetRow.querySelector(`[name*="${matcher.nameSuffix}"]`);
            if (found && !found.readOnly && !found.disabled) return found;
        }
        const targetRowElements = getRowEditableElements(targetRow);
        if (targetRowElements[matcher.fallbackIndex]) {
            return targetRowElements[matcher.fallbackIndex];
        }
        return null;
    }

    function setInputValue(targetEl, rawVal) {
        if (!targetEl) return;
        let val = rawVal !== undefined && rawVal !== null ? String(rawVal).trim() : '';
        if (val.startsWith('"') && val.endsWith('"')) {
            val = val.slice(1, -1).replace(/""/g, '"').trim();
        }

        if (targetEl.tagName === 'SELECT') {
            let matched = false;
            const lower = val.toLowerCase();
            for (let i = 0; i < targetEl.options.length; i++) {
                const opt = targetEl.options[i];
                if (opt.value.trim().toLowerCase() === lower || opt.text.trim().toLowerCase() === lower) {
                    targetEl.selectedIndex = i;
                    matched = true;
                    break;
                }
            }
            if (!matched && val === '') {
                targetEl.value = '';
            }
            if (targetEl.tomselect) {
                targetEl.tomselect.setValue(targetEl.value);
            }
        } else if (targetEl.type === 'number') {
            targetEl.value = cleanExcelNumber(targetEl, val);
        } else {
            targetEl.value = val;
        }

        if (targetEl.classList.contains('product-bevel-input')) {
            targetEl.setAttribute('data-auto-sync', 'none');
            const row = targetEl.closest('.order-item-row');
            if (row) {
                if (typeof updateBevelFieldStyle === 'function') updateBevelFieldStyle(row);
                if (typeof updateMinLateBevelFieldStyle === 'function') updateMinLateBevelFieldStyle(row);
            }
        }

        targetEl.dispatchEvent(new Event('input', { bubbles: true }));
        targetEl.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function createNewRowForSupply(supplyRow, container) {
        if (!supplyRow) return;
        const glassBtn = supplyRow.querySelector('button[onclick*="addGlassOrderItem"]');
        if (glassBtn && typeof addGlassOrderItem === 'function') {
            addGlassOrderItem(glassBtn, true);
            return;
        }
        const minLateBtn = supplyRow.querySelector('button[onclick*="addMinLateOrderItem"]');
        if (minLateBtn && typeof addMinLateOrderItem === 'function') {
            addMinLateOrderItem(minLateBtn, true);
            return;
        }
        const acrylicBtn = supplyRow.querySelector('button[onclick*="addOrderItem"]');
        if (acrylicBtn && typeof addOrderItem === 'function') {
            addOrderItem(acrylicBtn, true);
            return;
        }
        const anyAddBtn = supplyRow.querySelector('button[onclick*="OrderItem"], button[onclick*="add"]');
        if (anyAddBtn) {
            anyAddBtn.click();
        }
    }

    // Khi người dùng bấm vào bất kỳ khoảng trống nào trong ô <td> của bảng, tự động focus vào input của ô đó
    document.addEventListener('click', function(e) {
        const td = e.target.closest('.order-item-row td, .payment-detail-row td');
        if (!td) return;
        if (e.target.closest('button, a, select, input, textarea, .ts-wrapper, .dropdown')) return;
        const input = td.querySelector('input:not([type="hidden"]), textarea, select');
        if (input && !input.readOnly && !input.disabled) {
            input.focus();
        }
    });

    // Lắng nghe sự kiện Paste (Ctrl+V) vào bảng vật tư
    document.addEventListener('paste', function(event) {
        const target = event.target;
        let currentInput = target.closest ? target.closest('input:not([type="hidden"]), textarea, select') : null;
        const currentRow = target.closest ? target.closest('.order-item-row, .payment-detail-row') : null;
        if (!currentRow) return;

        if (!currentInput && target.closest('td')) {
            currentInput = target.closest('td').querySelector('input:not([type="hidden"]), textarea, select');
        }
        if (!currentInput) return;

        const clipboardData = event.clipboardData || window.clipboardData;
        if (!clipboardData) return;

        // Bỏ qua nếu đang paste hình ảnh
        if (clipboardData.types && Array.from(clipboardData.types).includes('Files')) {
            const items = clipboardData.items;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type && items[i].type.indexOf('image') !== -1) {
                    return;
                }
            }
        }

        const pastedText = clipboardData.getData('text');
        if (!pastedText) return;

        // Chuẩn hóa dòng từ clipboard
        let rawLines = pastedText.replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n');
        // Excel luôn kèm ký tự xuống dòng ở cuối vùng copy, xóa dòng rỗng cuối này
        if (rawLines.length > 0 && rawLines[rawLines.length - 1].trim() === '') {
            rawLines.pop();
        }

        // Nếu chỉ là 1 giá trị đơn lẻ không có tab, cho phép browser paste bình thường
        if (rawLines.length <= 1 && (!rawLines[0] || !rawLines[0].includes('\t'))) {
            return;
        }

        // Chặn hành vi dán mặc định để xử lý dán theo bảng Excel
        event.preventDefault();

        const isPayment = currentRow.classList.contains('payment-detail-row');
        const container = currentRow.parentElement;
        const rowSelector = isPayment ? '.payment-detail-row' : '.order-item-row';
        const allRows = Array.from(container.querySelectorAll(rowSelector));
        const startRowIndex = allRows.indexOf(currentRow);
        if (startRowIndex === -1) return;

        const supplyRow = currentRow.closest('.order-supply-row');
        const totalRowsNeeded = startRowIndex + rawLines.length;
        const rowsToAdd = totalRowsNeeded - allRows.length;

        // Tự động tạo thêm dòng mới nếu số dòng copy nhiều hơn số dòng hiện có
        if (rowsToAdd > 0) {
            const originalUpdateSummary = window.updateOrderSummary;
            try {
                if (rowsToAdd > 5) {
                    window.updateOrderSummary = function() {};
                }
                for (let i = 0; i < rowsToAdd; i++) {
                    if (isPayment) {
                        if (typeof addPaymentDetail === 'function') addPaymentDetail();
                    } else if (supplyRow) {
                        createNewRowForSupply(supplyRow, container);
                    }
                }
            } finally {
                window.updateOrderSummary = originalUpdateSummary;
            }
        }

        const updatedRows = Array.from(container.querySelectorAll(rowSelector));
        const currentRowElements = getRowEditableElements(currentRow);
        let startColIndex = currentRowElements.indexOf(currentInput);
        if (startColIndex === -1) {
            startColIndex = currentRowElements.findIndex(el => el.closest('td') === currentInput.closest('td'));
            if (startColIndex === -1) startColIndex = 0;
        }

        // Xác định matcher cho các cột được copy (hỗ trợ cả copy 1 cột lẫn nhiều cột)
        const numColsInPaste = rawLines[0].split('\t').length;
        const colMatchers = [];
        for (let c = 0; c < numColsInPaste; c++) {
            const refEl = currentRowElements[startColIndex + c];
            if (refEl) {
                colMatchers.push({
                    nameSuffix: getFieldNameSuffix(refEl),
                    className: getSpecialClassName(refEl),
                    fallbackIndex: startColIndex + c
                });
            } else {
                colMatchers.push({
                    nameSuffix: null,
                    className: null,
                    fallbackIndex: startColIndex + c
                });
            }
        }

        const modifiedRows = [];
        for (let r = 0; r < rawLines.length; r++) {
            const targetRow = updatedRows[startRowIndex + r];
            if (!targetRow) continue;
            modifiedRows.push(targetRow);
            const rowCells = rawLines[r].split('\t');
            for (let c = 0; c < rowCells.length; c++) {
                const matcher = colMatchers[c];
                if (matcher) {
                    const targetEl = findTargetElementInRow(targetRow, matcher);
                    if (targetEl) {
                        setInputValue(targetEl, rowCells[c]);
                    }
                }
            }
        }

        // Kích hoạt tính toán công thức cho toàn bộ các dòng vừa dán
        modifiedRows.forEach(row => {
            if (typeof calculateTotalPrice === 'function') {
                try { calculateTotalPrice(row); } catch(err) {}
            }
            if (typeof calculateGlassTotalPrice === 'function') {
                try { calculateGlassTotalPrice(row); } catch(err) {}
            }
            if (typeof calculateMinLateRowStats === 'function') {
                try { calculateMinLateRowStats(row); } catch(err) {}
            }
            if (typeof calculateMinLateTotalPrice === 'function') {
                try { calculateMinLateTotalPrice(row); } catch(err) {}
            }
            if (typeof calculatePaymentDetailRowTotal === 'function' && row.classList.contains('payment-detail-row')) {
                try { calculatePaymentDetailRowTotal(row); } catch(err) {}
            }
        });

        // Cập nhật lại số thứ tự dòng và tổng hợp đơn hàng
        if (typeof updateAcrylicRowIndexes === 'function') updateAcrylicRowIndexes();
        if (typeof updateGlassRowIndexes === 'function') updateGlassRowIndexes();
        if (typeof updateMinLateRowIndexes === 'function') updateMinLateRowIndexes();
        if (typeof updateOrderSummary === 'function') updateOrderSummary();

        // Hiển thị thông báo toast thành công
        const toastMsg = `Đã dán thành công <strong>${modifiedRows.length}</strong> dòng từ Excel vào bảng!`;
        if (typeof showPreviewToast === 'function') {
            showPreviewToast(toastMsg, 'success');
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

    window.serializeSupplies = function serializeSupplies() {
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

            const actionSelect = supplyRow.querySelector('.supply-inventory-action-select') || supplyRow.querySelector('[name*="[inventory_action]"]');
            const warehouseSelect = supplyRow.querySelector('[name*="[warehouse_id]"]');

            const supply = {
                id: supplyRow.getAttribute('data-supply-id') || null,
                order_supply_code: supplyRow.querySelector('[name*="[order_supply_code]"]')?.value || '',
                supply_name: supplyRow.querySelector('[name*="[supply_name]"]')?.value || '',
                quantity: parseFloat(supplyRow.querySelector('[name*="[quantity]"]:not([name*="items"])')?.value) || 0,
                inventory_action: actionSelect ? actionSelect.value : 'add',
                warehouse_id: warehouseSelect ? warehouseSelect.value : null,
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
            <div class="flex items-start gap-3 p-3 rounded-lg bg-sky-50/70 border border-sky-200">
                <div class="px-2.5 py-1.5 bg-white border border-sky-300 rounded text-xs font-mono font-bold text-sky-800 shadow-sm flex items-center gap-1 shrink-0"><iconify-icon icon="lucide:table" class="text-sky-600"></iconify-icon> Bôi đen giống Excel</div>
                <div>
                    <p class="font-semibold text-sm text-sky-950 m-0">Chọn nhiều dòng & Xóa cả loạt (hoặc Copy)</p>
                    <p class="text-sm text-sky-800 m-0">Bấm và kéo chuột ở cột <strong>STT</strong> để bôi đen nhiều dòng (hoặc bấm dòng đầu rồi giữ <strong>Shift</strong> bấm dòng cuối). Nhấn phím <strong>Delete</strong> để xóa cả loạt, hoặc <strong>Ctrl+C</strong> để sao chép.</p>
                </div>
            </div>

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

{{-- Modal Thêm Nhanh Khách Hàng --}}
<x-modal name="quickCreateCustomerModal" maxWidth="lg" :hasBackdrop="true" :transparent="false">
    <div class="p-6">
        <div class="flex items-center justify-between border-b border-neutral-100 pb-3 mb-4">
            <h5 class="font-bold text-base text-neutral-800 flex items-center gap-2 m-0">
                <iconify-icon icon="lucide:user-plus" class="text-primary-600 text-lg"></iconify-icon>
                Thêm khách hàng mới
            </h5>
            <button type="button" onclick="closeModal('quickCreateCustomerModal'); resetQcCodeState();" class="w-8 h-8 rounded-lg hover:bg-neutral-100 flex items-center justify-center text-neutral-400 hover:text-danger-500 transition">
                <iconify-icon icon="lucide:x" class="text-lg"></iconify-icon>
            </button>
        </div>

        <form id="quickCreateCustomerForm" onsubmit="submitQuickCreateCustomer(event)" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-1">
                    <label class="block text-xs font-bold text-neutral-700 uppercase mb-1">Mã khách hàng</label>
                    <div class="relative">
                        <input type="text" name="qc_customer_code" id="qc_customer_code" 
                            class="form-control rounded-lg w-full text-sm font-mono font-bold uppercase transition-colors" 
                            placeholder="Tự sinh nếu trống" 
                            autocomplete="off"
                            oninput="handleCustomerCodeInput(this.value)">
                        <span id="qc_code_status_icon" class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center pointer-events-none hidden"></span>
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-neutral-700 uppercase mb-1">Tên khách hàng <span class="text-danger-500">*</span></label>
                    <input type="text" name="qc_name" id="qc_name" required class="form-control rounded-lg w-full text-sm" placeholder="VD: Anh Nam, Cty Nội Thất ABC...">
                </div>
            </div>
            <div id="qc_code_feedback" class="text-xs font-medium -mt-2 hidden"></div>

            <div>
                <label class="block text-xs font-bold text-neutral-700 uppercase mb-1">Nhóm thị trường</label>
                <select name="qc_market_group_id" id="qc_market_group_id" class="form-select rounded-lg w-full text-sm border-neutral-300">
                    <option value="">-- Chọn nhóm thị trường --</option>
                    @foreach($availableMarketGroups as $mg)
                        <option value="{{ $mg->id }}">{{ $mg->name }} ({{ $mg->code }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-neutral-700 uppercase mb-1">Số điện thoại</label>
                    <input type="text" name="qc_phone" id="qc_phone" class="form-control rounded-lg w-full text-sm" placeholder="09xxxxxxxxx">
                </div>
                <div>
                    <label class="block text-xs font-bold text-neutral-700 uppercase mb-1">Nợ ban đầu (nếu có)</label>
                    <input type="number" name="qc_debt" id="qc_debt" class="form-control rounded-lg w-full text-sm" placeholder="0" min="0">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-neutral-700 uppercase mb-1">Địa chỉ</label>
                <textarea name="qc_address" id="qc_address" rows="2" class="form-control rounded-lg w-full text-sm" placeholder="Địa chỉ xưởng / công ty khách..."></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-neutral-700 uppercase mb-1">Chính sách khách hàng</label>
                <textarea name="qc_policy" id="qc_policy" rows="2" class="form-control rounded-lg w-full text-sm" placeholder="Ghi chú chiết khấu, chính sách riêng..."></textarea>
            </div>

            <div id="qc_error_msg" class="text-xs text-danger-600 hidden font-semibold"></div>

            <div class="flex justify-end gap-2 pt-3 border-t border-neutral-100">
                <button type="button" onclick="closeModal('quickCreateCustomerModal'); resetQcCodeState();" class="btn btn-neutral px-4 py-2 text-sm rounded-lg">
                    Hủy
                </button>
                <button type="submit" id="qc_submit_btn" class="btn btn-primary px-5 py-2 text-sm rounded-lg flex items-center gap-1.5 font-semibold">
                    <iconify-icon icon="lucide:check" class="text-base"></iconify-icon>
                    Lưu & Chọn khách
                </button>
            </div>
        </form>
    </div>
</x-modal>

<script>
let qcCodeCheckTimer = null;
let isQcCodeDuplicated = false;

function resetQcCodeState() {
    isQcCodeDuplicated = false;
    clearTimeout(qcCodeCheckTimer);
    const input = document.getElementById('qc_customer_code');
    const icon = document.getElementById('qc_code_status_icon');
    const feedback = document.getElementById('qc_code_feedback');
    if (input) {
        input.classList.remove('border-danger-500', 'bg-danger-50', 'text-danger-900', 'border-success-500', 'bg-success-50/20');
    }
    if (icon) {
        icon.classList.add('hidden');
        icon.innerHTML = '';
    }
    if (feedback) {
        feedback.classList.add('hidden');
        feedback.innerHTML = '';
    }
}

function setQcCodeError(msg) {
    const input = document.getElementById('qc_customer_code');
    const icon = document.getElementById('qc_code_status_icon');
    const feedback = document.getElementById('qc_code_feedback');
    if (input) {
        input.classList.remove('border-success-500', 'bg-success-50/20');
        input.classList.add('border-danger-500', 'bg-danger-50', 'text-danger-900');
    }
    if (icon) {
        icon.classList.remove('hidden');
        icon.innerHTML = '<iconify-icon icon="lucide:alert-circle" class="text-danger-500 text-base"></iconify-icon>';
    }
    if (feedback) {
        feedback.classList.remove('hidden');
        feedback.innerHTML = '<span class="text-danger-600 flex items-center gap-1 font-bold"><iconify-icon icon="lucide:alert-triangle" class="text-sm flex-shrink-0"></iconify-icon> ' + msg + '</span>';
    }
}

function setQcCodeSuccess(msg) {
    const input = document.getElementById('qc_customer_code');
    const icon = document.getElementById('qc_code_status_icon');
    const feedback = document.getElementById('qc_code_feedback');
    if (input) {
        input.classList.remove('border-danger-500', 'bg-danger-50', 'text-danger-900');
        input.classList.add('border-success-500', 'bg-success-50/20');
    }
    if (icon) {
        icon.classList.remove('hidden');
        icon.innerHTML = '<iconify-icon icon="lucide:check-circle-2" class="text-success-500 text-base"></iconify-icon>';
    }
    if (feedback) {
        feedback.classList.remove('hidden');
        feedback.innerHTML = '<span class="text-success-600 flex items-center gap-1 font-semibold"><iconify-icon icon="lucide:check" class="text-sm flex-shrink-0"></iconify-icon> ' + msg + '</span>';
    }
}

function handleCustomerCodeInput(val) {
    clearTimeout(qcCodeCheckTimer);
    const code = val.trim();

    if (!code) {
        resetQcCodeState();
        return;
    }

    // Fast check client-side
    if (typeof window.allAllowedCustomers !== 'undefined' && Array.isArray(window.allAllowedCustomers)) {
        const found = window.allAllowedCustomers.some(c => c.customer_code && c.customer_code.trim().toUpperCase() === code.toUpperCase());
        if (found) {
            isQcCodeDuplicated = true;
            setQcCodeError('Mã khách hàng "' + code + '" đã bị trùng (đã tồn tại trong hệ thống)!');
            return;
        }
    }

    const icon = document.getElementById('qc_code_status_icon');
    if (icon) {
        icon.classList.remove('hidden');
        icon.innerHTML = '<iconify-icon icon="lucide:loader-2" class="text-neutral-400 text-sm animate-spin"></iconify-icon>';
    }

    qcCodeCheckTimer = setTimeout(() => {
        fetch('{{ route("customers.check-code") }}?code=' + encodeURIComponent(code))
            .then(res => res.json())
            .then(data => {
                const currentVal = document.getElementById('qc_customer_code')?.value.trim();
                if (currentVal !== code) return;

                if (data.exists) {
                    isQcCodeDuplicated = true;
                    setQcCodeError(data.message || 'Mã khách hàng "' + code + '" đã bị trùng!');
                } else {
                    isQcCodeDuplicated = false;
                    setQcCodeSuccess('Mã hợp lệ, có thể sử dụng');
                }
            })
            .catch(() => {
                if (icon) icon.classList.add('hidden');
            });
    }, 250);
}

function submitQuickCreateCustomer(e) {
    e.preventDefault();
    const btn = document.getElementById('qc_submit_btn');
    const errEl = document.getElementById('qc_error_msg');
    errEl.classList.add('hidden');
    errEl.textContent = '';

    const customerCode = document.getElementById('qc_customer_code').value.trim();
    const name = document.getElementById('qc_name').value.trim();
    const marketGroupId = document.getElementById('qc_market_group_id').value;
    const phone = document.getElementById('qc_phone').value.trim();
    const address = document.getElementById('qc_address').value.trim();
    const debt = document.getElementById('qc_debt').value;
    const policy = document.getElementById('qc_policy').value.trim();

    if (!name) {
        errEl.textContent = 'Vui lòng nhập tên khách hàng';
        errEl.classList.remove('hidden');
        return;
    }

    if (isQcCodeDuplicated) {
        setQcCodeError('Mã khách hàng "' + customerCode + '" đã bị trùng, vui lòng nhập mã khác!');
        document.getElementById('qc_customer_code').focus();
        errEl.textContent = 'Mã khách hàng đã bị trùng! Vui lòng chọn mã khác.';
        errEl.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<iconify-icon icon="lucide:loader-2" class="animate-spin"></iconify-icon> Đang lưu...';

    fetch('{{ route("customers.quick-create") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            name: name,
            customer_code: customerCode || null,
            market_group_id: marketGroupId || null,
            phone: phone,
            address: address,
            debt: debt || 0,
            policy: policy,
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="lucide:check" class="text-base"></iconify-icon> Lưu & Chọn khách';

        if (data.success && data.customer) {
            const cust = data.customer;
            if (typeof window.allAllowedCustomers !== 'undefined') {
                window.allAllowedCustomers.push(cust);
            }

            const custSelect = document.getElementById('customer-select');
            const mgName = cust.market_group ? ' (' + cust.market_group.name + ')' : '';
            const labelText = cust.customer_code + ' - ' + cust.name + mgName;

            if (custSelect && custSelect.tomselect) {
                const ts = custSelect.tomselect;
                ts.addOption({
                    value: cust.id,
                    text: labelText
                });
                ts.setValue(cust.id);
            } else if (custSelect) {
                const opt = new Option(labelText, cust.id, true, true);
                custSelect.appendChild(opt);
            }

            fillCustomerInfo(cust.id);

            // Cập nhật market_group_select
            const mgSelect = document.getElementById('market_group_select');
            if (mgSelect && cust.market_group_id) {
                mgSelect.value = cust.market_group_id;
            }

            document.getElementById('quickCreateCustomerForm').reset();
            resetQcCodeState();
            closeModal('quickCreateCustomerModal');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Đã thêm khách hàng: ' + cust.name,
                    showConfirmButton: false,
                    timer: 2500
                });
            }
        } else {
            if (data.errors && data.errors.customer_code) {
                isQcCodeDuplicated = true;
                setQcCodeError(data.errors.customer_code[0]);
                document.getElementById('qc_customer_code').focus();
            }
            errEl.textContent = data.message || 'Có lỗi xảy ra khi lưu khách hàng.';
            errEl.classList.remove('hidden');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="lucide:check" class="text-base"></iconify-icon> Lưu & Chọn khách';
        errEl.textContent = 'Lỗi kết nối máy chủ.';
    });
}

// ==============================================================
// EXCEL-LIKE MULTI-ROW SELECTION & BULK ACTIONS (XÓA / COPY)
// ==============================================================
(function() {
    let excelSelectionAnchorRow = null;
    let isExcelDragging = false;
    let currentTbody = null;

    function getSelectedRows() {
        return Array.from(document.querySelectorAll('tr.order-item-row.row-selected, tr.payment-detail-row.row-selected'));
    }

    function clearExcelRowSelection() {
        document.querySelectorAll('tr.row-selected').forEach(r => r.classList.remove('row-selected'));
        updateExcelSelectionBar();
    }

    function updateExcelSelectionBar() {
        const bar = document.getElementById('excel-selection-bar');
        const countEl = document.getElementById('excel-selected-count');
        if (!bar || !countEl) return;

        const count = getSelectedRows().length;
        if (count > 0) {
            countEl.textContent = count;
            bar.style.display = 'flex';
        } else {
            bar.style.display = 'none';
        }
    }

    function getTableRows(tbody) {
        return Array.from(tbody.querySelectorAll('tr.order-item-row, tr.payment-detail-row'));
    }

    function selectRowRange(tbody, startRow, endRow) {
        const allRows = getTableRows(tbody);
        const startIdx = allRows.indexOf(startRow);
        const endIdx = allRows.indexOf(endRow);
        if (startIdx === -1 || endIdx === -1) return;

        const min = Math.min(startIdx, endIdx);
        const max = Math.max(startIdx, endIdx);

        allRows.forEach((r, idx) => {
            if (idx >= min && idx <= max) {
                r.classList.add('row-selected');
            } else {
                r.classList.remove('row-selected');
            }
        });
        updateExcelSelectionBar();
    }

    // Xử lý sự kiện mousedown trên cột STT (bắt đầu kéo chọn giống Excel)
    document.addEventListener('mousedown', function(e) {
        const sttCell = e.target.closest('td.sticky-stt-td');
        if (!sttCell) {
            // Nếu click bên ngoài bảng và bên ngoài thanh floating bar -> bỏ chọn
            if (!e.target.closest('#excel-selection-bar') && !e.target.closest('th.sticky-stt-th')) {
                if (getSelectedRows().length > 0 && !e.shiftKey && !e.ctrlKey && !e.metaKey) {
                    clearExcelRowSelection();
                }
            }
            return;
        }

        const row = sttCell.closest('tr.order-item-row, tr.payment-detail-row');
        if (!row) return;

        const tbody = row.closest('tbody');
        if (!tbody) return;

        currentTbody = tbody;
        isExcelDragging = true;

        if (e.shiftKey && excelSelectionAnchorRow && excelSelectionAnchorRow.closest('tbody') === tbody) {
            // Shift + click: Chọn dải dòng từ dòng trước đến dòng này
            selectRowRange(tbody, excelSelectionAnchorRow, row);
        } else if (e.ctrlKey || e.metaKey) {
            // Ctrl + click: Chọn/bỏ chọn từng dòng
            row.classList.toggle('row-selected');
            excelSelectionAnchorRow = row;
            updateExcelSelectionBar();
        } else {
            // Click thường: Chọn dòng này và chuẩn bị kéo
            clearExcelRowSelection();
            row.classList.add('row-selected');
            excelSelectionAnchorRow = row;
            updateExcelSelectionBar();
        }
    });

    // Kéo chuột qua các dòng STT khác để bôi đen dải dòng
    document.addEventListener('mouseover', function(e) {
        if (!isExcelDragging || !excelSelectionAnchorRow || !currentTbody) return;

        const sttCell = e.target.closest('td.sticky-stt-td');
        if (!sttCell) return;

        const row = sttCell.closest('tr.order-item-row, tr.payment-detail-row');
        if (!row || row.closest('tbody') !== currentTbody) return;

        selectRowRange(currentTbody, excelSelectionAnchorRow, row);
    });

    // Kết thúc kéo chuột
    document.addEventListener('mouseup', function() {
        isExcelDragging = false;
    });

    // Click vào tiêu đề cột STT trên header -> Chọn / Bỏ chọn toàn bộ dòng trong bảng đó
    document.addEventListener('click', function(e) {
        const sttTh = e.target.closest('th.sticky-stt-th');
        if (!sttTh) return;

        const table = sttTh.closest('table');
        if (!table) return;

        const rows = Array.from(table.querySelectorAll('tr.order-item-row, tr.payment-detail-row'));
        if (rows.length === 0) return;

        const allSelected = rows.every(r => r.classList.contains('row-selected'));
        clearExcelRowSelection();
        if (!allSelected) {
            rows.forEach(r => r.classList.add('row-selected'));
            excelSelectionAnchorRow = rows[0];
        }
        updateExcelSelectionBar();
    });

    // Khi người dùng bấm vào ô input/textarea để gõ dữ liệu -> tự động hủy bôi đen dòng
    document.addEventListener('focusin', function(e) {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
            if (getSelectedRows().length > 0) {
                clearExcelRowSelection();
            }
        }
    });

    // Xóa các dòng đang được bôi đen
    function deleteSelectedRows() {
        const selectedRows = getSelectedRows();
        if (selectedRows.length === 0) return;

        const parentTbodies = new Set();
        selectedRows.forEach(row => {
            const tbody = row.closest('.supply-items-container, tbody');
            if (tbody) parentTbodies.add(tbody);

            // Khôi phục nút nếu dòng là công giả dày
            const isLaborRow = row.dataset.isLabor === '1' || row.querySelector('.labor-badge') != null || (row.querySelector('input[name*="[product_name]"]') && row.querySelector('input[name*="[product_name]"]').value.trim() === 'Công giả dày');
            if (isLaborRow) {
                const prevRow = row.previousElementSibling;
                if (prevRow && prevRow.classList.contains('order-item-row')) {
                    prevRow.querySelectorAll('button[onclick*="insertAcrylicRow"]').forEach(b => b.style.display = '');
                    const p1 = prevRow.querySelector('button[onclick*="addFakeThicknessRow"]');
                    if (p1) p1.style.display = '';
                    const p2 = prevRow.querySelector('button[onclick*="duplicateAcrylicRow"]');
                    if (p2) p2.style.display = '';
                }
            }
            row.remove();
        });

        // Nếu bảng bị xóa sạch, tự động thêm 1 dòng trắng mặc định
        parentTbodies.forEach(tbody => {
            if (tbody.querySelectorAll('.order-item-row').length === 0) {
                const supplyRow = tbody.closest('.order-supply-row');
                const addAcrylicBtn = supplyRow?.querySelector('button[onclick*="addOrderItem"]');
                const addGlassBtn = supplyRow?.querySelector('button[onclick*="addGlassOrderItem"]');
                const addMinLateBtn = supplyRow?.querySelector('button[onclick*="addMinLateOrderItem"]');
                if (addAcrylicBtn && typeof addOrderItem === 'function') addOrderItem(addAcrylicBtn, true);
                else if (addGlassBtn && typeof addGlassOrderItem === 'function') addGlassOrderItem(addGlassBtn, true);
                else if (addMinLateBtn && typeof addMinLateOrderItem === 'function') addMinLateOrderItem(addMinLateBtn, true);
            }
        });

        clearExcelRowSelection();

        // Cập nhật lại số thứ tự và tổng tiền
        if (typeof updateOrderSummary === 'function') updateOrderSummary();
        if (typeof updateAcrylicRowIndexes === 'function') updateAcrylicRowIndexes();
        if (typeof updateGlassRowIndexes === 'function') updateGlassRowIndexes();
        if (typeof updateMinLateRowIndexes === 'function') updateMinLateRowIndexes();

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: `Đã xóa ${selectedRows.length} dòng được chọn!`,
                showConfirmButton: false,
                timer: 2000
            });
        }
    }

    // Sao chép các dòng đang chọn thành dạng bảng TSV để dán vào Excel / Ctrl+V
    function copySelectedRows() {
        const selectedRows = getSelectedRows();
        if (selectedRows.length === 0) return;

        const lines = [];
        selectedRows.forEach(row => {
            const inputs = Array.from(row.querySelectorAll('input:not([type="hidden"]), select, textarea'));
            const rowData = inputs.map(input => {
                if (input.tagName === 'SELECT') {
                    return input.options[input.selectedIndex]?.text || input.value || '';
                }
                return input.value || '';
            });
            lines.push(rowData.join('\t'));
        });

        const tsvText = lines.join('\n');
        navigator.clipboard.writeText(tsvText).then(() => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `Đã copy ${selectedRows.length} dòng (sẵn sàng dán Excel hoặc Ctrl+V)!`,
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        }).catch(err => {
            console.error('Copy failed:', err);
        });
    }

    // Bắt phím tắt bàn phím
    document.addEventListener('keydown', function(e) {
        const selectedCount = getSelectedRows().length;

        // Escape: Bỏ chọn dòng
        if (e.key === 'Escape' && selectedCount > 0) {
            e.preventDefault();
            clearExcelRowSelection();
            return;
        }

        // Delete hoặc Backspace khi có dòng đang bôi đen (và không đang focus gõ chữ trong ô input)
        if ((e.key === 'Delete' || e.key === 'Backspace') && selectedCount > 0) {
            const isTyping = ['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName);
            if (!isTyping) {
                e.preventDefault();
                deleteSelectedRows();
                return;
            }
        }

        // Ctrl + C: Sao chép các dòng đang chọn
        if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'C') && selectedCount > 0) {
            const textSelected = window.getSelection().toString();
            if (!textSelected) {
                e.preventDefault();
                copySelectedRows();
                return;
            }
        }

        // Ctrl + A: Bôi đen tất cả các dòng trong bảng hiện tại
        if ((e.ctrlKey || e.metaKey) && (e.key === 'a' || e.key === 'A')) {
            const activeTable = document.activeElement ? document.activeElement.closest('table') : null;
            if (activeTable && activeTable.querySelector('tr.order-item-row, tr.payment-detail-row')) {
                const isInsideTextInput = ['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName);
                if (!isInsideTextInput) {
                    e.preventDefault();
                    clearExcelRowSelection();
                    activeTable.querySelectorAll('tr.order-item-row, tr.payment-detail-row').forEach(r => r.classList.add('row-selected'));
                    updateExcelSelectionBar();
                }
            }
        }
    });

    // Gán sự kiện click cho các nút trên floating selection bar
    function initButtons() {
        const delBtn = document.getElementById('excel-delete-selected-btn');
        if (delBtn) delBtn.onclick = deleteSelectedRows;

        const copyBtn = document.getElementById('excel-copy-selected-btn');
        if (copyBtn) copyBtn.onclick = copySelectedRows;

        const clearBtn = document.getElementById('excel-clear-selection-btn');
        if (clearBtn) clearBtn.onclick = clearExcelRowSelection;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initButtons);
    } else {
        initButtons();
    }

    // ========================================================
    // EXCEL-LIKE CELL SELECTION, AUTO-FILL HANDLE & RANGE COPY/PASTE
    // ========================================================
    let selectedCells = [];
    let cellAnchor = null;
    let cellActive = null;
    let isCellDragSelecting = false;
    let isHandleDragging = false;
    let fillHandleEl = null;
    let fillSourceCell = null;
    let fillTargetCells = [];
    let mouseStartPos = { x: 0, y: 0 };
    let isPossibleDrag = false;
    let currentCellTbody = null;
    let internalCopiedText = null;

    // === HỆ THỐNG HOÀN TÁC UNDO / REDO ===
    const undoStack = [];
    const redoStack = [];

    function pushUndoAction(action) {
        undoStack.push(action);
        if (undoStack.length > 50) undoStack.shift();
        redoStack.length = 0; // Khi có thao tác mới, xóa lịch sử redo
    }

    function flashCells(cells, className = 'excel-undo-pulse') {
        cells.forEach(c => {
            c.classList.add(className);
            setTimeout(() => c.classList.remove(className), 800);
        });
    }

    function performUndo() {
        if (undoStack.length === 0) return false;
        const action = undoStack.pop();
        const affected = [];

        action.changes.forEach(ch => {
            if (ch.input && document.body.contains(ch.input)) {
                ch.input.value = ch.oldValue;
                ch.input.dispatchEvent(new Event('input', { bubbles: true }));
                ch.input.dispatchEvent(new Event('change', { bubbles: true }));
                affected.push(ch.input);
            }
        });

        redoStack.push(action);
        if (typeof updateOrderSummary === 'function') updateOrderSummary();
        flashCells(affected);
        updateFillHandlePosition();

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: `Đã hoàn tác: ${action.description || 'Undo'} (Ctrl + Z)`,
                showConfirmButton: false,
                timer: 1500
            });
        }
        return true;
    }

    function performRedo() {
        if (redoStack.length === 0) return false;
        const action = redoStack.pop();
        const affected = [];

        action.changes.forEach(ch => {
            if (ch.input && document.body.contains(ch.input)) {
                ch.input.value = ch.newValue;
                ch.input.dispatchEvent(new Event('input', { bubbles: true }));
                ch.input.dispatchEvent(new Event('change', { bubbles: true }));
                affected.push(ch.input);
            }
        });

        undoStack.push(action);
        if (typeof updateOrderSummary === 'function') updateOrderSummary();
        flashCells(affected);
        updateFillHandlePosition();

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: `Đã làm lại: ${action.description || 'Redo'} (Ctrl + Shift + Z)`,
                showConfirmButton: false,
                timer: 1500
            });
        }
        return true;
    }

    // === HỆ THỐNG CẮT (CTRL + X) ===
    function performCut(e) {
        const activeEl = document.activeElement;
        const isTableInput = activeEl && activeEl.closest('table.bordered-table tbody tr.order-item-row, table.bordered-table tbody tr.payment-detail-row');

        let targetCells = [];
        if (selectedCells.length > 0) {
            targetCells = [...selectedCells];
        } else if (isTableInput && !activeEl.readOnly && !activeEl.disabled) {
            targetCells = [activeEl];
        }

        if (targetCells.length === 0) return false;

        if (e) e.preventDefault();

        // 1. Nhóm dữ liệu TSV để copy vào clipboard
        const rowMap = new Map();
        targetCells.forEach(cell => {
            const tr = cell.closest('tr');
            if (!rowMap.has(tr)) rowMap.set(tr, []);
            rowMap.get(tr).push(cell.value || '');
        });

        const tsvLines = [];
        rowMap.forEach(cols => tsvLines.push(cols.join('\t')));
        const tsv = tsvLines.join('\n');
        internalCopiedText = tsv;

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(tsv).catch(() => {});
        }
        if (e && e.clipboardData) {
            e.clipboardData.setData('text/plain', tsv);
        }

        // 2. Lưu vào Undo stack
        const changes = targetCells.map(c => ({
            input: c,
            oldValue: c.value,
            newValue: ''
        }));
        pushUndoAction({
            type: 'cut',
            description: `Cắt ${targetCells.length} ô`,
            changes: changes
        });

        // 3. Xóa giá trị các ô
        targetCells.forEach(c => {
            c.value = '';
            c.dispatchEvent(new Event('input', { bubbles: true }));
            c.dispatchEvent(new Event('change', { bubbles: true }));
        });

        if (typeof updateOrderSummary === 'function') updateOrderSummary();
        updateFillHandlePosition();

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: `Đã cắt ${targetCells.length} ô (Ctrl + X)!`,
                showConfirmButton: false,
                timer: 1500
            });
        }
        return true;
    }

    // Khởi tạo phần tử Fill Handle (chấm vuông màu xanh góc dưới phải ô giống Excel)
    function ensureFillHandle() {
        if (!fillHandleEl) {
            fillHandleEl = document.getElementById('excel-fill-handle');
            if (!fillHandleEl) {
                fillHandleEl = document.createElement('div');
                fillHandleEl.id = 'excel-fill-handle';
                fillHandleEl.title = 'Kéo xuống để tự động điền giá trị giống Excel';
                document.body.appendChild(fillHandleEl);
            }

            fillHandleEl.onmousedown = function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (!cellActive && selectedCells.length === 0) return;
                isHandleDragging = true;
                fillSourceCell = cellActive || selectedCells[0];
                fillTargetCells = [];
                currentCellTbody = fillSourceCell.closest('tbody');
            };
        }
    }

    // Đặt vị trí của Fill Handle tại góc dưới bên phải của ô đang active hoặc vùng ô đang chọn
    function updateFillHandlePosition() {
        ensureFillHandle();
        if (!fillHandleEl) return;
        const target = selectedCells.length > 0 ? selectedCells[selectedCells.length - 1] : cellActive;
        if (!target || !document.body.contains(target)) {
            fillHandleEl.style.display = 'none';
            return;
        }

        const rect = target.getBoundingClientRect();
        if (rect.width === 0 || rect.height === 0 || rect.bottom < 0 || rect.top > window.innerHeight) {
            fillHandleEl.style.display = 'none';
            return;
        }

        fillHandleEl.style.display = 'block';
        fillHandleEl.style.left = (rect.right - 4 + window.scrollX) + 'px';
        fillHandleEl.style.top = (rect.bottom - 4 + window.scrollY) + 'px';
    }

    // Xóa toàn bộ ô đang bôi đen
    function clearCellSelection() {
        selectedCells.forEach(cell => {
            cell.classList.remove('excel-cell-selected', 'excel-copied-marching', 'excel-fill-preview');
        });
        selectedCells = [];
        if (fillHandleEl) fillHandleEl.style.display = 'none';
    }

    // Xác định tọa độ dòng & cột của ô input trong bảng
    function getCellPos(input) {
        if (!input) return null;
        const td = input.closest('td');
        const tr = input.closest('tr.order-item-row, tr.payment-detail-row');
        if (!td || !tr) return null;
        const tbody = tr.closest('tbody');
        if (!tbody) return null;

        const rows = Array.from(tbody.querySelectorAll('tr.order-item-row, tr.payment-detail-row'));
        const rowIndex = rows.indexOf(tr);
        const tds = Array.from(tr.querySelectorAll('td'));
        const colIndex = tds.indexOf(td);

        return { rowIndex, colIndex, tr, td, tbody, rows };
    }

    // Lấy danh sách ô trong hình chữ nhật từ startPos đến endPos
    function getCellsInRect(startPos, endPos) {
        if (!startPos || !endPos || startPos.tbody !== endPos.tbody) return [];
        const minR = Math.min(startPos.rowIndex, endPos.rowIndex);
        const maxR = Math.max(startPos.rowIndex, endPos.rowIndex);
        const minC = Math.min(startPos.colIndex, endPos.colIndex);
        const maxC = Math.max(startPos.colIndex, endPos.colIndex);

        const cells = [];
        for (let r = minR; r <= maxR; r++) {
            const row = startPos.rows[r];
            if (!row) continue;
            const rowTds = Array.from(row.querySelectorAll('td'));
            for (let c = minC; c <= maxC; c++) {
                const cellTd = rowTds[c];
                if (!cellTd) continue;
                const inp = cellTd.querySelector('input:not([type="hidden"]), select, textarea');
                if (inp && !inp.readOnly && !inp.disabled) {
                    cells.push(inp);
                }
            }
        }
        return cells;
    }

    // Chọn vùng ô từ anchor đến target
    function selectCellRange(startInput, endInput) {
        const startPos = getCellPos(startInput);
        const endPos = getCellPos(endInput);
        if (!startPos || !endPos) return;

        clearCellSelection();
        const cells = getCellsInRect(startPos, endPos);
        cells.forEach(c => c.classList.add('excel-cell-selected'));
        selectedCells = cells;
        cellActive = endInput;
        updateFillHandlePosition();
    }

    // 1. Khi nhấn chuột xuống một ô input (mousedown)
    document.addEventListener('mousedown', function(e) {
        // Bỏ qua nếu bấm vào fill handle
        if (e.target.closest('#excel-fill-handle')) return;

        const input = e.target.closest('table.bordered-table tbody tr.order-item-row td input:not([type="hidden"]), table.bordered-table tbody tr.payment-detail-row td input:not([type="hidden"])');
        if (!input || input.readOnly || input.disabled) {
            if (!e.target.closest('.excel-cell-selected')) {
                clearCellSelection();
            }
            return;
        }

        ensureFillHandle();

        if (e.shiftKey && cellAnchor && cellAnchor.closest('tbody') === input.closest('tbody')) {
            // Shift + click: Chọn dải ô từ anchor đến ô này
            e.preventDefault();
            selectCellRange(cellAnchor, input);
            return;
        }

        cellActive = input;
        cellAnchor = input;
        mouseStartPos = { x: e.clientX, y: e.clientY };
        isPossibleDrag = true;
        currentCellTbody = input.closest('tbody');

        // Bôi sáng ô hiện tại
        clearCellSelection();
        selectedCells = [input];
        input.classList.add('excel-cell-selected');
        updateFillHandlePosition();
    });

    // 2. Khi rê chuột (mousemove)
    document.addEventListener('mousemove', function(e) {
        // Trường hợp A: Kéo góc Fill Handle (chấm vuông xanh) để điền tự động giống Excel
        if (isHandleDragging && fillSourceCell && currentCellTbody) {
            e.preventDefault();
            const el = document.elementFromPoint(e.clientX, e.clientY);
            const targetInput = el?.closest('td')?.querySelector('input:not([type="hidden"]), select, textarea');
            const sourcePos = getCellPos(fillSourceCell);
            const targetPos = getCellPos(targetInput);

            // Bỏ preview cũ
            document.querySelectorAll('.excel-fill-preview').forEach(el => el.classList.remove('excel-fill-preview'));
            fillTargetCells = [];

            if (sourcePos && targetPos && sourcePos.tbody === targetPos.tbody) {
                // Điền dọc theo cột của ô nguồn
                const col = sourcePos.colIndex;
                const minR = Math.min(sourcePos.rowIndex, targetPos.rowIndex);
                const maxR = Math.max(sourcePos.rowIndex, targetPos.rowIndex);

                for (let r = minR; r <= maxR; r++) {
                    const row = sourcePos.rows[r];
                    if (!row) continue;
                    const inp = row.querySelectorAll('td')[col]?.querySelector('input:not([type="hidden"]), select, textarea');
                    if (inp && inp !== fillSourceCell && !inp.readOnly && !inp.disabled) {
                        inp.classList.add('excel-fill-preview');
                        fillTargetCells.push(inp);
                    }
                }
            }
            return;
        }

        // Trường hợp B: Kéo chuột từ ô này qua các ô khác để bôi đen dải ô (kéo 1 đường từ trên xuống)
        if (isPossibleDrag && cellAnchor) {
            const dx = Math.abs(e.clientX - mouseStartPos.x);
            const dy = Math.abs(e.clientY - mouseStartPos.y);

            // Chỉ kích hoạt chế độ kéo chọn khi di chuyển chuột ra khỏi vị trí ban đầu
            if (dx > 4 || dy > 4) {
                isCellDragSelecting = true;
                window.getSelection()?.removeAllRanges(); // ngăn bôi đen text mặc định của trình duyệt

                const el = document.elementFromPoint(e.clientX, e.clientY);
                const targetInput = el?.closest('td')?.querySelector('input:not([type="hidden"]), select, textarea');
                if (targetInput && targetInput.closest('tbody') === currentCellTbody) {
                    selectCellRange(cellAnchor, targetInput);
                }
            }
        }
    });

    // 3. Khi thả chuột (mouseup)
    document.addEventListener('mouseup', function(e) {
        // Hoàn tất kéo Fill Handle
        if (isHandleDragging) {
            isHandleDragging = false;
            document.querySelectorAll('.excel-fill-preview').forEach(el => el.classList.remove('excel-fill-preview'));

            if (fillSourceCell && fillTargetCells.length > 0) {
                const fillVal = fillSourceCell.value;

                // Ghi vào Undo Stack
                const changes = fillTargetCells.map(inp => ({
                    input: inp,
                    oldValue: inp.value,
                    newValue: fillVal
                }));
                pushUndoAction({
                    type: 'fill',
                    description: `Điền tự động ${fillTargetCells.length} ô`,
                    changes: changes
                });

                fillTargetCells.forEach(inp => {
                    inp.value = fillVal;
                    inp.dispatchEvent(new Event('input', { bubbles: true }));
                    inp.dispatchEvent(new Event('change', { bubbles: true }));
                });

                if (typeof updateOrderSummary === 'function') updateOrderSummary();

                // Chọn lại vùng vừa điền
                clearCellSelection();
                selectedCells = [fillSourceCell, ...fillTargetCells];
                selectedCells.forEach(c => c.classList.add('excel-cell-selected'));
                cellActive = fillTargetCells[fillTargetCells.length - 1];
                updateFillHandlePosition();

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: `Đã tự động điền "${fillVal}" vào ${fillTargetCells.length} ô!`,
                        showConfirmButton: false,
                        timer: 1800
                    });
                }
            }
            fillSourceCell = null;
            fillTargetCells = [];
            return;
        }

        isPossibleDrag = false;
        isCellDragSelecting = false;
        updateFillHandlePosition();
    });

    // 4. Sự kiện Copy (Ctrl + C)
    document.addEventListener('copy', function(e) {
        if (selectedCells.length > 1) {
            e.preventDefault();

            // Nhóm các ô theo dòng
            const rowMap = new Map();
            selectedCells.forEach(cell => {
                const tr = cell.closest('tr');
                if (!rowMap.has(tr)) rowMap.set(tr, []);
                rowMap.get(tr).push(cell.value || '');
            });

            const tsvLines = [];
            rowMap.forEach(cols => {
                tsvLines.push(cols.join('\t'));
            });

            const tsv = tsvLines.join('\n');
            internalCopiedText = tsv;
            if (e.clipboardData) {
                e.clipboardData.setData('text/plain', tsv);
            }

            // Hiệu ứng viền marching ants giống Excel
            selectedCells.forEach(c => c.classList.add('excel-copied-marching'));
            setTimeout(() => {
                selectedCells.forEach(c => c.classList.remove('excel-copied-marching'));
            }, 1500);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `Đã copy ${selectedCells.length} ô vào clipboard!`,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        } else if (cellActive) {
            internalCopiedText = cellActive.value;
        }
    });

    // Sự kiện Cắt (Ctrl + X) dạng native event
    document.addEventListener('cut', function(e) {
        performCut(e);
    });

    // 5. Sự kiện Paste (Ctrl + V)
    document.addEventListener('paste', function(e) {
        const activeEl = document.activeElement;
        const isTableInput = activeEl && activeEl.closest('table.bordered-table tbody tr.order-item-row, table.bordered-table tbody tr.payment-detail-row');

        // Chỉ can thiệp khi đang focus ô trong bảng hoặc có ô đang được bôi đen
        if (!isTableInput && selectedCells.length === 0) return;

        let clipboardText = '';
        if (e.clipboardData) {
            clipboardText = e.clipboardData.getData('text/plain');
        }
        if (!clipboardText && internalCopiedText) {
            clipboardText = internalCopiedText;
        }
        if (!clipboardText) return;

        const rawLines = clipboardText.replace(/\r\n/g, '\n').replace(/\r/g, '\n').replace(/\n$/, '').split('\n').map(l => l.split('\t'));
        const isSingleValue = rawLines.length === 1 && rawLines[0].length === 1;

        // TRƯỜNG HỢP 1: Copy 1 giá trị (ví dụ: 1200) và dán vào nhiều ô đang chọn
        if (isSingleValue && selectedCells.length > 1) {
            e.preventDefault();
            const singleVal = rawLines[0][0].trim();

            const changes = selectedCells.map(cell => ({
                input: cell,
                oldValue: cell.value,
                newValue: singleVal
            }));
            pushUndoAction({
                type: 'paste',
                description: `Dán ${selectedCells.length} ô`,
                changes: changes
            });

            selectedCells.forEach(cell => {
                cell.value = singleVal;
                cell.dispatchEvent(new Event('input', { bubbles: true }));
                cell.dispatchEvent(new Event('change', { bubbles: true }));
            });

            if (typeof updateOrderSummary === 'function') updateOrderSummary();
            updateFillHandlePosition();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `Đã dán "${singleVal}" vào ${selectedCells.length} ô!`,
                    showConfirmButton: false,
                    timer: 1800
                });
            }
            return;
        }

        // TRƯỜNG HỢP 2: Dán bảng dữ liệu nhiều dòng/nhiều cột từ Excel hoặc sao chép nhiều ô
        const startInput = (selectedCells.length > 0 ? selectedCells[0] : activeEl);
        const startPos = getCellPos(startInput);
        if (!startPos) return;

        if (rawLines.length > 1 || rawLines[0].length > 1) {
            e.preventDefault();
            const pastedCells = [];
            const changes = [];

            rawLines.forEach((rowVals, rOffset) => {
                const targetRowIndex = startPos.rowIndex + rOffset;
                const targetRow = startPos.rows[targetRowIndex];
                if (!targetRow) return;

                const rowTds = Array.from(targetRow.querySelectorAll('td'));
                rowVals.forEach((val, cOffset) => {
                    const targetColIndex = startPos.colIndex + cOffset;
                    const targetTd = rowTds[targetColIndex];
                    if (!targetTd) return;

                    const inp = targetTd.querySelector('input:not([type="hidden"]), select, textarea');
                    if (inp && !inp.readOnly && !inp.disabled) {
                        changes.push({
                            input: inp,
                            oldValue: inp.value,
                            newValue: val.trim()
                        });
                        inp.value = val.trim();
                        inp.dispatchEvent(new Event('input', { bubbles: true }));
                        inp.dispatchEvent(new Event('change', { bubbles: true }));
                        pastedCells.push(inp);
                    }
                });
            });

            if (changes.length > 0) {
                pushUndoAction({
                    type: 'paste_grid',
                    description: `Dán ${changes.length} ô`,
                    changes: changes
                });
            }

            if (typeof updateOrderSummary === 'function') updateOrderSummary();

            if (pastedCells.length > 0) {
                clearCellSelection();
                selectedCells = pastedCells;
                selectedCells.forEach(c => c.classList.add('excel-cell-selected'));
                cellActive = pastedCells[pastedCells.length - 1];
                updateFillHandlePosition();

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: `Đã dán ${pastedCells.length} ô dữ liệu!`,
                        showConfirmButton: false,
                        timer: 1800
                    });
                }
            }
            return;
        }
    });

    // Theo dõi giá trị ban đầu khi focus ô để hỗ trợ Undo chỉnh sửa đơn lẻ
    document.addEventListener('focusin', function(e) {
        const inp = e.target.closest('table.bordered-table tbody tr.order-item-row td input:not([type="hidden"]), table.bordered-table tbody tr.payment-detail-row td input:not([type="hidden"])');
        if (inp) {
            inp.dataset.undoVal = inp.value;
        }
    });

    document.addEventListener('change', function(e) {
        const inp = e.target.closest('table.bordered-table tbody tr.order-item-row td input:not([type="hidden"]), table.bordered-table tbody tr.payment-detail-row td input:not([type="hidden"])');
        if (inp && inp.dataset.undoVal !== undefined && inp.value !== inp.dataset.undoVal) {
            pushUndoAction({
                type: 'edit',
                description: 'Sửa ô',
                changes: [{ input: inp, oldValue: inp.dataset.undoVal, newValue: inp.value }]
            });
            inp.dataset.undoVal = inp.value;
        }

        // Đồng bộ: Cột khoét kính ghi cái gì thì replace ghi chú thành cái đó + tô màu vàng
        if (e.target && (e.target.classList.contains('cnc-template-select') || (e.target.name && e.target.name.includes('[vertical_grain_cnc]')))) {
            const tr = e.target.closest('tr');
            const noteInput = tr ? tr.querySelector('input[name*="[notes]"]') : null;
            const hasVal = !!e.target.value;
            if (hasVal) {
                e.target.classList.add('input-cnc-highlight');
                if (noteInput) noteInput.classList.add('input-cnc-highlight');
            } else {
                e.target.classList.remove('input-cnc-highlight');
                if (noteInput) noteInput.classList.remove('input-cnc-highlight');
            }
            if (noteInput) {
                const selectedText = e.target.value ? (e.target.options ? (e.target.options[e.target.selectedIndex]?.text || e.target.value) : e.target.value).trim() : '';
                noteInput.value = selectedText;
                noteInput.dispatchEvent(new Event('input', { bubbles: true }));
                noteInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    });

    // 6. Phím tắt mở rộng vùng chọn, Delete, Cut (Ctrl+X), Undo (Ctrl+Z), Redo (Ctrl+Shift+Z / Ctrl+Y)
    document.addEventListener('keydown', function(e) {
        // Ctrl + Z: Hoàn tác (Undo)
        if ((e.ctrlKey || e.metaKey) && (e.key === 'z' || e.key === 'Z') && !e.shiftKey) {
            const isTableInput = document.activeElement && document.activeElement.closest('table.bordered-table tbody tr.order-item-row, table.bordered-table tbody tr.payment-detail-row');
            if (isTableInput || selectedCells.length > 0 || undoStack.length > 0) {
                if (performUndo()) {
                    e.preventDefault();
                    return;
                }
            }
        }

        // Ctrl + Shift + Z hoặc Ctrl + Y: Làm lại (Redo)
        if (((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 'z' || e.key === 'Z')) || 
            ((e.ctrlKey || e.metaKey) && (e.key === 'y' || e.key === 'Y'))) {
            const isTableInput = document.activeElement && document.activeElement.closest('table.bordered-table tbody tr.order-item-row, table.bordered-table tbody tr.payment-detail-row');
            if (isTableInput || selectedCells.length > 0 || redoStack.length > 0) {
                if (performRedo()) {
                    e.preventDefault();
                    return;
                }
            }
        }

        // Ctrl + X: Cắt dữ liệu (Cut)
        if ((e.ctrlKey || e.metaKey) && (e.key === 'x' || e.key === 'X')) {
            if (performCut(e)) {
                return;
            }
        }

        // Phím Delete hoặc Backspace khi có nhiều ô được chọn
        if ((e.key === 'Delete' || e.key === 'Backspace') && selectedCells.length > 1) {
            const isTyping = ['INPUT', 'TEXTAREA'].includes(document.activeElement?.tagName) && selectedCells.length <= 1;
            if (!isTyping) {
                e.preventDefault();
                const changes = selectedCells.map(cell => ({
                    input: cell,
                    oldValue: cell.value,
                    newValue: ''
                }));
                pushUndoAction({
                    type: 'delete',
                    description: `Xóa ${selectedCells.length} ô`,
                    changes: changes
                });

                selectedCells.forEach(cell => {
                    cell.value = '';
                    cell.dispatchEvent(new Event('input', { bubbles: true }));
                    cell.dispatchEvent(new Event('change', { bubbles: true }));
                });
                if (typeof updateOrderSummary === 'function') updateOrderSummary();
                return;
            }
        }

        // Shift + ArrowDown / ArrowUp để mở rộng dải ô giống Excel
        if (e.shiftKey && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
            const current = cellActive || (selectedCells.length > 0 ? selectedCells[selectedCells.length - 1] : null);
            const currentPos = getCellPos(current);
            if (!currentPos) return;

            const nextRowIndex = e.key === 'ArrowDown' ? currentPos.rowIndex + 1 : currentPos.rowIndex - 1;
            const nextRow = currentPos.rows[nextRowIndex];
            if (!nextRow) return;

            const nextInput = nextRow.querySelectorAll('td')[currentPos.colIndex]?.querySelector('input:not([type="hidden"]), select, textarea');
            if (nextInput) {
                e.preventDefault();
                cellAnchor = cellAnchor || current;
                selectCellRange(cellAnchor, nextInput);
                nextInput.focus();
            }
        }

        // Escape: Bỏ chọn ô
        if (e.key === 'Escape' && selectedCells.length > 0) {
            clearCellSelection();
        }
    });

    // Cập nhật lại vị trí handle khi cuộn bảng hoặc màn hình
    window.addEventListener('scroll', updateFillHandlePosition, true);
    window.addEventListener('resize', updateFillHandlePosition);

    window.clearExcelRowSelection = clearExcelRowSelection;
    window.deleteExcelSelectedRows = deleteSelectedRows;
    window.copyExcelSelectedRows = copySelectedRows;
    window.clearCellSelection = clearCellSelection;
    window.performExcelUndo = performUndo;
    window.performExcelRedo = performRedo;
    window.performExcelCut = performCut;
})();
</script>

{{-- Floating Action Bar cho Thao tác Bôi đen Nhiều Dòng (Excel-style) --}}
<div id="excel-selection-bar" style="display:none;" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[9999] bg-slate-900/95 text-white backdrop-blur-md px-5 py-2.5 rounded-2xl shadow-2xl border border-slate-700/80 items-center gap-3 transition-all duration-200 animate-in fade-in slide-in-from-bottom-4">
    <div class="flex items-center gap-2 pr-3 border-r border-slate-700 text-xs font-semibold text-slate-200">
        <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 font-bold text-xs">
            <iconify-icon icon="lucide:check-square" class="text-sm"></iconify-icon>
        </span>
        <span>Đã chọn: <strong id="excel-selected-count" class="text-white text-sm font-extrabold">0</strong> dòng</span>
    </div>
    <div class="flex items-center gap-2">
        <button type="button" id="excel-delete-selected-btn" class="px-3.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold flex items-center gap-1.5 shadow-md transition-all hover:scale-105 active:scale-95 cursor-pointer" title="Xóa các dòng đang chọn (Phím Delete)">
            <iconify-icon icon="lucide:trash-2" class="text-sm"></iconify-icon>
            <span>Xóa dòng đã chọn (Del)</span>
        </button>
        <button type="button" id="excel-copy-selected-btn" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold flex items-center gap-1.5 border border-slate-700 transition-all hover:scale-105 active:scale-95 cursor-pointer" title="Sao chép các dòng đang chọn (Ctrl + C)">
            <iconify-icon icon="lucide:copy" class="text-sm"></iconify-icon>
            <span>Copy (Ctrl+C)</span>
        </button>
    </div>
    <button type="button" id="excel-clear-selection-btn" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition-colors cursor-pointer" title="Bỏ chọn (Phím Esc)">
        <iconify-icon icon="lucide:x" class="text-base"></iconify-icon>
    </button>
</div>

<style>
    .order-row-height-resize-handle:hover {
        background-color: transparent !important;
    }

    /* === EXCEL-LIKE MULTI-ROW SELECTION STYLES === */
    .sticky-stt-td {
        cursor: pointer !important;
        user-select: none !important;
        -webkit-user-select: none !important;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .sticky-stt-td:hover {
        background-color: #dbeafe !important;
        color: #1d4ed8 !important;
        font-weight: 700 !important;
    }
    .sticky-stt-th {
        cursor: pointer !important;
        user-select: none !important;
        -webkit-user-select: none !important;
        transition: background-color 0.15s ease;
    }
    .sticky-stt-th:hover {
        background-color: #dbeafe !important;
        color: #1d4ed8 !important;
    }
    tr.order-item-row.row-selected,
    tr.payment-detail-row.row-selected {
        background-color: #e0f2fe !important;
    }
    tr.order-item-row.row-selected > td,
    tr.payment-detail-row.row-selected > td {
        background-color: #e0f2fe !important;
        border-top: 1px solid #38bdf8 !important;
        border-bottom: 1px solid #38bdf8 !important;
    }
    tr.order-item-row.row-selected > td.sticky-stt-td,
    tr.payment-detail-row.row-selected > td.sticky-stt-td {
        background-color: #0284c7 !important;
        color: #ffffff !important;
        font-weight: 800 !important;
        box-shadow: inset -2px 0 0 #0369a1 !important;
    }
    tr.order-item-row.row-selected input,
    tr.order-item-row.row-selected select,
    tr.order-item-row.row-selected textarea,
    tr.payment-detail-row.row-selected input,
    tr.payment-detail-row.row-selected select,
    tr.payment-detail-row.row-selected textarea {
        background-color: #f0f9ff !important;
        border-color: #93c5fd !important;
    }

    /* === EXCEL-LIKE CELL SELECTION & AUTO-FILL STYLES === */
    .excel-cell-selected,
    table.bordered-table tbody tr.order-item-row td input.excel-cell-selected,
    table.bordered-table tbody tr.payment-detail-row td input.excel-cell-selected {
        background-color: #dbeafe !important; /* Tailwind blue-100 */
        color: #1e3a8a !important;
        outline: 2px solid #2563eb !important;
        outline-offset: -2px !important;
        position: relative;
        z-index: 5;
    }

    .excel-fill-preview,
    table.bordered-table tbody tr.order-item-row td input.excel-fill-preview,
    table.bordered-table tbody tr.payment-detail-row td input.excel-fill-preview {
        background-color: rgba(59, 130, 246, 0.18) !important;
        outline: 2px dashed #2563eb !important;
        outline-offset: -2px !important;
    }

    .excel-copied-marching,
    table.bordered-table tbody tr.order-item-row td input.excel-copied-marching {
        outline: 2px dashed #16a34a !important;
        outline-offset: -2px !important;
        animation: excelMarchingAnts 0.8s linear infinite;
    }

    @keyframes excelMarchingAnts {
        0% { outline-offset: -2px; }
        50% { outline-offset: -1px; }
        100% { outline-offset: -2px; }
    }

    #excel-fill-handle {
        position: absolute;
        width: 8px;
        height: 8px;
        background-color: #2563eb;
        border: 1.5px solid #ffffff;
        cursor: crosshair;
        z-index: 99999;
        display: none;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.35);
        border-radius: 1px;
        pointer-events: auto;
        transition: transform 0.1s ease;
    }
    #excel-fill-handle:hover {
        transform: scale(1.3);
        background-color: #1d4ed8;
    }

    .excel-undo-pulse {
        animation: excelUndoFlash 0.8s ease-out;
    }
    @keyframes excelUndoFlash {
        0% { background-color: #fef08a !important; outline: 2px solid #eab308 !important; }
        100% { background-color: inherit; outline: none; }
    }

    .input-narrow-warning,
    input.input-narrow-warning,
    select.input-narrow-warning,
    table.bordered-table tbody tr.order-item-row td input.input-narrow-warning,
    table.bordered-table tbody tr.order-item-row td select.input-narrow-warning {
        background-color: #fef2f2 !important;
        color: #ef4444 !important;
        font-weight: bold !important;
        border-color: #fca5a5 !important;
    }
    .input-narrow-warning:focus,
    input.input-narrow-warning:focus,
    select.input-narrow-warning:focus,
    table.bordered-table tbody tr.order-item-row td input.input-narrow-warning:focus,
    table.bordered-table tbody tr.order-item-row td select.input-narrow-warning:focus {
        background-color: #fef2f2 !important;
        color: #ef4444 !important;
        font-weight: bold !important;
        outline: 2px solid #ef4444 !important;
        outline-offset: -2px !important;
        box-shadow: none !important;
    }

    /* Kính và CNC màu vàng */
    .input-cnc-highlight,
    input.input-cnc-highlight,
    select.input-cnc-highlight,
    table.bordered-table tbody tr.order-item-row td input.input-cnc-highlight,
    table.bordered-table tbody tr.order-item-row td select.input-cnc-highlight {
        background-color: #fef9c3 !important;
        color: #b45309 !important;
        font-weight: bold !important;
        border-color: #fde047 !important;
    }
    .input-cnc-highlight:focus,
    input.input-cnc-highlight:focus,
    select.input-cnc-highlight:focus,
    table.bordered-table tbody tr.order-item-row td input.input-cnc-highlight:focus,
    table.bordered-table tbody tr.order-item-row td select.input-cnc-highlight:focus {
        background-color: #fef9c3 !important;
        color: #b45309 !important;
        font-weight: bold !important;
        outline: 2px solid #eab308 !important;
        outline-offset: -2px !important;
        box-shadow: none !important;
    }
    #preview-order-print-container {
        display: none;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 6mm 8mm;
        }
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body.is-printing-order-preview {
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
            height: auto !important;
            overflow: visible !important;
        }
        body.is-printing-order-preview > *:not(#preview-order-print-container) {
            display: none !important;
        }
        body.is-printing-order-preview #preview-order-print-container {
            display: block !important;
            position: static !important;
            width: 100% !important;
            min-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
        }
        body.is-printing-order-preview #preview-order-print-container #preview-order-print-area {
            width: 100% !important;
            min-width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
        }
        body.is-printing-order-preview #preview-order-print-container table {
            width: 100% !important;
            border-collapse: collapse !important;
            page-break-inside: auto;
        }
        body.is-printing-order-preview #preview-order-print-container thead {
            display: table-header-group !important;
        }
        body.is-printing-order-preview #preview-order-print-container tfoot {
            display: table-footer-group !important;
        }
        body.is-printing-order-preview #preview-order-print-container tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        body.is-printing-order-preview #preview-order-print-container th,
        body.is-printing-order-preview #preview-order-print-container td {
            font-size: 11px !important;
            padding: 5px 6px !important;
            border: 1px solid #cbd5e1 !important;
        }
        body.is-printing-order-preview #preview-order-print-container th {
            background-color: #f1f5f9 !important;
            font-weight: 700 !important;
            color: #0f172a !important;
        }
        body.is-printing-order-preview #preview-order-print-container .avoid-break {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>


