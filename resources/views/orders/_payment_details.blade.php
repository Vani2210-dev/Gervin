@php
    $hasPaymentDetails = isset($acrylicOrder) && isset($acrylicOrder->paymentDetails) && $acrylicOrder->paymentDetails->count() > 0;
    $isMinLateOrder = ($currentOrderType ?? '') === 'min_late';
    $woodBoardPrices = $woodBoardPrices ?? \App\Models\WoodBoardPrice::orderBy('code', 'asc')->get();
    $glassPrices = $glassPrices ?? \App\Models\GlassPrice::orderBy('code', 'asc')->get();
    $minLatePrices = $minLatePrices ?? \App\Models\MinLatePrice::orderBy('category_name', 'asc')->orderBy('stt', 'asc')->get();
@endphp

<style>
    /* Ẩn triệt để thẻ select HTML gốc để chỉ hiển thị hộp chọn tìm kiếm TomSelect */
    #payment-details-container select.order-payment-code-select,
    #payment-details-container select.tom-select-payment-code,
    #payment-details-container select.tomselected,
    .payment-detail-row select {
        display: none !important;
        visibility: hidden !important;
        position: absolute !important;
        opacity: 0 !important;
        pointer-events: none !important;
        width: 0 !important;
        height: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        border: 0 !important;
    }

    /* Style TomSelect cho dropdown Mã trong bảng chi tiết hóa đơn */
    #payment-details-container tr.payment-detail-row td .ts-wrapper {
        width: 100% !important;
        height: 100% !important;
        min-height: 42px !important;
        padding: 0 !important;
        margin: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        display: flex !important;
        align-items: center !important;
    }
    #payment-details-container tr.payment-detail-row td .ts-wrapper .ts-control,
    #payment-details-container tr.payment-detail-row td .ts-wrapper .ts-control * {
        font-size: 12px !important;
    }
    #payment-details-container tr.payment-detail-row td .ts-wrapper .ts-control {
        width: 100% !important;
        height: 100% !important;
        min-height: 42px !important;
        padding: 0 10px !important;
        margin: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: #ffffff !important;
        box-shadow: none !important;
        color: #0f172a !important;
        display: flex !important;
        align-items: center !important;
        cursor: pointer !important;
    }
    #payment-details-container tr.payment-detail-row td .ts-wrapper .ts-control input {
        border: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        height: auto !important;
        min-height: 0 !important;
        box-shadow: none !important;
    }
    #payment-details-container tr.payment-detail-row td .ts-wrapper.focus .ts-control {
        background: #eff6ff !important;
        outline: 2px solid #3b82f6 !important;
        outline-offset: -2px !important;
        box-shadow: inset 0 0 0 1px #3b82f6 !important;
    }
    #payment-details-container tr.payment-detail-row td .ts-wrapper .ts-control:after {
        display: none !important;
    }
    .ts-dropdown,
    body > .ts-dropdown {
        font-size: 12px !important;
        z-index: 999999 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
    }
    .ts-dropdown .option,
    body > .ts-dropdown .option {
        padding: 6px 12px !important;
        font-size: 12px !important;
    }
    .ts-dropdown .optgroup-header,
    body > .ts-dropdown .optgroup-header {
        font-weight: 700 !important;
        background: #f8fafc !important;
        color: #475569 !important;
        padding: 6px 10px !important;
    }
</style>

@if(!$isWarranty)
{{-- Bảng chi tiết hóa đơn dịch vụ / mua tấm riêng / cắt kính --}}
<div id="payment-details-section" class="order-supplies-popup bg-white border border-neutral-200 rounded-xl p-6 shadow-sm mt-6 relative pt-8" style="{{ $hasPaymentDetails ? '' : 'display: none;' }}" data-order-supplies-zoom-panel data-order-supplies-storage-key="min_late_payment" data-order-supplies-zoom="100" data-order-supplies-visible-rows-disabled="1">
    <div class="order-supplies-header flex items-center justify-between border-b border-neutral-100 pb-4 mb-5">
        <div class="absolute -top-3.5 left-6 bg-white px-3 flex items-center gap-2 z-10">
            <iconify-icon icon="lucide:receipt" class="text-xl text-primary-500"></iconify-icon>
            <h6 class="font-bold text-base text-neutral-800 m-0">Chi tiết hóa đơn dịch vụ & Vật tư bổ sung</h6>
        </div>
        <div class="flex flex-wrap items-center gap-2 ml-auto">
            <button type="button" onclick="previewOrder()" class="fullscreen-only-btn btn btn-sm bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200 rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:eye" class="text-lg"></iconify-icon>
                <span>Xem trước</span>
            </button>
            <button type="button" onclick="toggleOrderSuppliesPopup(this)" class="btn btn-sm bg-light-100 hover:bg-neutral-200 text-dark rounded-lg flex items-center gap-1 hide-on-mobile" data-order-supplies-popup-button aria-expanded="false">
                <iconify-icon icon="lucide:maximize-2" class="text-lg" data-order-supplies-popup-icon></iconify-icon>
                <span data-order-supplies-popup-label>Phóng to</span>
            </button>
            <button type="button" onclick="addPaymentDetail()" class="btn btn-sm btn-primary rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon> <span class="mobile-hide-text">Thêm nội dung</span>
            </button>
        </div>
    </div>
    <div class="order-supplies-body">
        <div class="overflow-x-auto pb-3" data-order-supplies-table-scroll>
            <div class="order-supplies-table-zoom-wrap" data-order-supplies-table-zoom-wrap>
                <table class="table bordered-table sm-table mb-0 min-w-[950px] border border-neutral-200" data-order-resize-group="min_late_payment">
                    <thead>
                        <tr class="bg-neutral-50 text-center">
                            <th scope="col" style="width: 50px; min-width: 50px; white-space: nowrap;" class="sticky-stt-th text-center border border-neutral-200 font-bold text-xs text-neutral-600 uppercase"><span class="order-stt-header-label">STT</span></th>
                            <th scope="col" style="width: 140px; min-width: 120px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã</th>
                            <th scope="col" style="white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên nội dung <span class="text-danger-500">*</span></th>
                            <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn vị</th>
                            <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng</th>
                            <th scope="col" style="width: 140px; min-width: 140px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                            @if($isMinLateOrder)
                            <th scope="col" style="width: 120px; min-width: 120px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá chỉ</th>
                            @endif
                            <th scope="col" style="width: 150px; min-width: 150px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                            <th scope="col" style="width: 80px; min-width: 80px; white-space: nowrap; position: sticky; right: 0; z-index: 2; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase bg-neutral-50">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="payment-details-container">
                        @if(isset($acrylicOrder) && isset($acrylicOrder->paymentDetails) && $acrylicOrder->paymentDetails->count() > 0)
                            @foreach($acrylicOrder->paymentDetails as $detailIndex => $detail)
                            <tr class="payment-detail-row">
                                <td style="width: 50px; min-width: 50px; padding: 0 !important;" class="sticky-stt-td text-center align-middle border border-neutral-200">
                                    <span class="detail-index font-semibold text-neutral-500">{{ $detailIndex + 1 }}</span>
                                </td>
                                <td class="border border-neutral-200" style="padding: 0 !important; position: relative; width: 140px; min-width: 120px; height: 42px;">
                                    @php
                                        $selectedCode = '';
                                        // 1. Kiểm tra WoodBoardPrice (Mua tấm ván riêng / Ván mộc)
                                        foreach($woodBoardPrices ?? [] as $p) {
                                            if ($p->code && (str_starts_with($detail->name, $p->code . ' - ') || $detail->name === $p->name || $detail->name === ($p->code . ' - ' . $p->name))) {
                                                $selectedCode = $p->code;
                                                break;
                                            }
                                        }
                                        // 2. Kiểm tra GlassPrice (Cắt kính)
                                        if (!$selectedCode) {
                                            foreach($glassPrices ?? [] as $p) {
                                                if ($p->code && (str_starts_with($detail->name, $p->code . ' - ') || $detail->name === $p->product_name || $detail->name === ($p->code . ' - ' . $p->product_name))) {
                                                    $selectedCode = $p->code;
                                                    break;
                                                }
                                            }
                                        }
                                        // 3. Kiểm tra MinLatePrice (Dịch vụ min-late)
                                        if (!$selectedCode) {
                                            foreach($minLatePrices ?? [] as $p) {
                                                $mCode = $p->code ?: ($p->product_code ?: 'ML'.$p->id);
                                                if ($mCode && (str_starts_with($detail->name, $mCode . ' - ') || $detail->name === $p->product_name || str_contains($detail->name, $p->product_name))) {
                                                    $selectedCode = $mCode;
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp
                                    <select class="order-payment-code-select tom-select-payment-code" style="display: none !important;">
                                        <option value="">-- Chọn mã --</option>
                                        <optgroup label="🪵 Tấm ván riêng / Ván mộc">
                                            @foreach($woodBoardPrices ?? [] as $price)
                                                @if($price->code)
                                                    <option value="{{ $price->code }}" {{ $selectedCode == $price->code ? 'selected' : '' }}>{{ $price->code }} - {{ $price->name }}</option>
                                                @endif
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="🪟 Cắt cánh kính & Phụ kiện">
                                            @foreach($glassPrices ?? [] as $price)
                                                @if($price->code)
                                                    <option value="{{ $price->code }}" {{ $selectedCode == $price->code ? 'selected' : '' }}>{{ $price->code }} - {{ $price->product_name }}</option>
                                                @endif
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="🪚 Dịch vụ Min-late / Gia công">
                                            @foreach($minLatePrices ?? [] as $price)
                                                @php $mCode = $price->code ?: ($price->product_code ?: 'ML'.$price->id); @endphp
                                                <option value="{{ $mCode }}" {{ $selectedCode == $mCode ? 'selected' : '' }}>{{ $mCode }} - {{ $price->category_name }} - {{ $price->product_name }}</option>
                                            @endforeach
                                        </optgroup>
                                        @if($selectedCode && !($woodBoardPrices->pluck('code')->contains($selectedCode) || $glassPrices->pluck('code')->contains($selectedCode)))
                                            <option value="{{ $selectedCode }}" selected>{{ $selectedCode }}</option>
                                        @endif
                                    </select>
                                </td>
                                <td class="border border-neutral-200" style="padding: 0 !important; position: relative;">
                                    <textarea name="payment_details[{{ $detailIndex }}][name]" class="form-control form-control-sm rounded-lg w-full border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-xs font-semibold resize-none" rows="2" style="resize: none; padding: 4px 8px;" placeholder="Tên nội dung..." required>{{ $detail->name }}</textarea>
                                </td>
                                <td style="width: 100px; min-width: 100px; padding: 0 !important;" class="border border-neutral-200">
                                    <input type="text" name="payment_details[{{ $detailIndex }}][unit]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs w-full" placeholder="tấm, m², bộ..." value="{{ $detail->unit }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; padding: 0 !important;" class="border border-neutral-200">
                                    <input type="number" name="payment_details[{{ $detailIndex }}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs payment-quantity-input w-full" placeholder="1" step="any" value="{{ $detail->quantity }}">
                                </td>
                                <td style="width: 140px; min-width: 140px; padding: 0 !important;" class="border border-neutral-200">
                                    <input type="number" name="payment_details[{{ $detailIndex }}][price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs w-full" placeholder="0" min="0" step="any" required value="{{ $detail->price }}">
                                </td>
                                @if($isMinLateOrder)
                                <td style="width: 120px; min-width: 120px; padding: 0 !important;" class="border border-neutral-200">
                                    <input type="number" name="payment_details[{{ $detailIndex }}][price_only]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs w-full" placeholder="0" min="0" step="any" value="{{ $detail->price_only }}">
                                </td>
                                @else
                                <input type="hidden" name="payment_details[{{ $detailIndex }}][price_only]" value="{{ $detail->price_only ?? 0 }}">
                                @endif
                                <td style="width: 150px; min-width: 150px; padding: 0 !important;" class="border border-neutral-200">
                                    <input type="text" name="payment_details[{{ $detailIndex }}][total]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center h-8 text-xs w-full" placeholder="0" readonly value="{{ number_format($detail->total, 0, ',', '.') }}">
                                </td>
                                <td style="width: 80px; min-width: 80px; padding: 0 !important; position: sticky; right: 0; z-index: 1; background-color: #fff; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="text-center align-middle border border-neutral-200">
                                    <button type="button" onclick="removePaymentDetail(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa nội dung này">
                                        <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                                    </button>
                                    <input type="hidden" name="payment_details[{{ $detailIndex }}][id]" value="{{ $detail->id }}">
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

<script>
window.escapeHtml = window.escapeHtml || function(text) {
    if (text === undefined || text === null) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
};
var escapeHtml = window.escapeHtml;

window.refreshPaymentDetailsTableLayout = window.refreshPaymentDetailsTableLayout || function() {
    const panel = document.querySelector('[data-order-supplies-storage-key="min_late_payment"]');
    if (panel && typeof applyOrderSuppliesVisibleRows === 'function') {
        applyOrderSuppliesVisibleRows(panel, panel.dataset.orderSuppliesVisibleRows || '5');
    }

    if (typeof initOrderColumnResize === 'function') {
        initOrderColumnResize(document);
    }
};
var refreshPaymentDetailsTableLayout = window.refreshPaymentDetailsTableLayout;

window.applyFlashEffect = window.applyFlashEffect || function(el) {
    if (!el) return;
    el.classList.add('bg-amber-100', 'transition-colors', 'duration-500');
    setTimeout(() => {
        el.classList.remove('bg-amber-100');
    }, 1000);
};
var applyFlashEffect = window.applyFlashEffect;

window.woodBoardPricesData = @json($woodBoardPrices ?? []);
window.glassPricesData = @json($glassPrices ?? []);
window.minLatePricesData = @json($minLatePrices ?? []);
var paymentDetailIndex = typeof window.paymentDetailIndex !== 'undefined' ? window.paymentDetailIndex : {{ isset($acrylicOrder) && isset($acrylicOrder->paymentDetails) ? $acrylicOrder->paymentDetails->count() : 0 }};
window.paymentDetailIndex = paymentDetailIndex;
var isMinLateOrderType = {{ $isMinLateOrder ? 'true' : 'false' }};

function getPaymentCodeOptionsHtml() {
    let optionsHtml = '<option value="">-- Chọn mã --</option>';
    
    // 1. Tấm ván riêng / Ván mộc
    const boardPrices = window.woodBoardPricesData || [];
    if (boardPrices.length > 0) {
        optionsHtml += '<optgroup label="🪵 Tấm ván riêng / Ván mộc">';
        boardPrices.forEach(p => {
            if (p.code) {
                optionsHtml += `<option value="${escapeHtml(p.code)}">${escapeHtml(p.code)} - ${escapeHtml(p.name || '')}</option>`;
            }
        });
        optionsHtml += '</optgroup>';
    }

    // 2. Kính & Phụ kiện
    const glassPrices = window.glassPricesData || [];
    if (glassPrices.length > 0) {
        optionsHtml += '<optgroup label="🪟 Cắt cánh kính & Phụ kiện">';
        glassPrices.forEach(p => {
            if (p.code) {
                optionsHtml += `<option value="${escapeHtml(p.code)}">${escapeHtml(p.code)} - ${escapeHtml(p.product_name || '')}</option>`;
            }
        });
        optionsHtml += '</optgroup>';
    }

    // 3. Dịch vụ Min-late
    const minLatePrices = window.minLatePricesData || [];
    if (minLatePrices.length > 0) {
        optionsHtml += '<optgroup label="🪚 Dịch vụ Min-late / Gia công">';
        minLatePrices.forEach(p => {
            const mCode = p.code || p.product_code || ('ML' + p.id);
            optionsHtml += `<option value="${escapeHtml(mCode)}">${escapeHtml(mCode)} - ${escapeHtml(p.category_name || '')} - ${escapeHtml(p.product_name || '')}</option>`;
        });
        optionsHtml += '</optgroup>';
    }

    return optionsHtml;
}

function addPaymentDetail() {
    const container = document.getElementById('payment-details-container');
    if (!container) return;
    
    const optionsHtml = getPaymentCodeOptionsHtml();

    const newRow = document.createElement('tr');
    newRow.className = 'payment-detail-row';
    newRow.innerHTML = `
        <td style="width: 50px; min-width: 50px; padding: 0 !important;" class="sticky-stt-td text-center align-middle border border-neutral-200">
            <span class="detail-index font-semibold text-neutral-500">${paymentDetailIndex + 1}</span>
        </td>
        <td class="border border-neutral-200" style="padding: 0 !important; position: relative; width: 140px; min-width: 120px; height: 42px;">
            <select class="order-payment-code-select tom-select-payment-code" style="display: none !important;">
                ${optionsHtml}
            </select>
        </td>
        <td class="border border-neutral-200" style="padding: 0 !important; position: relative;">
            <textarea name="payment_details[${paymentDetailIndex}][name]" class="form-control form-control-sm rounded-lg w-full border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-xs font-semibold resize-none" rows="2" style="resize: none; padding: 4px 8px;" placeholder="Tên nội dung..." required></textarea>
        </td>
        <td style="width: 100px; min-width: 100px; padding: 0 !important;" class="border border-neutral-200">
            <input type="text" name="payment_details[${paymentDetailIndex}][unit]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs w-full" placeholder="tấm, m², bộ...">
        </td>
        <td style="width: 100px; min-width: 100px; padding: 0 !important;" class="border border-neutral-200">
            <input type="number" name="payment_details[${paymentDetailIndex}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs payment-quantity-input w-full" placeholder="1" step="any" value="1">
        </td>
        <td style="width: 140px; min-width: 140px; padding: 0 !important;" class="border border-neutral-200">
            <input type="number" name="payment_details[${paymentDetailIndex}][price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs w-full" placeholder="0" min="0" step="any" required value="0">
        </td>
        ${isMinLateOrderType ? `
        <td style="width: 120px; min-width: 120px; padding: 0 !important;" class="border border-neutral-200">
            <input type="number" name="payment_details[${paymentDetailIndex}][price_only]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs w-full" placeholder="0" min="0" step="any" value="0">
        </td>` : `
        <input type="hidden" name="payment_details[${paymentDetailIndex}][price_only]" value="0">
        `}
        <td style="width: 150px; min-width: 150px; padding: 0 !important;" class="border border-neutral-200">
            <input type="text" name="payment_details[${paymentDetailIndex}][total]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center h-8 text-xs w-full" placeholder="0" readonly value="0">
        </td>
        <td style="width: 80px; min-width: 80px; padding: 0 !important; position: sticky; right: 0; z-index: 1; background-color: #fff; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="text-center align-middle border border-neutral-200">
            <button type="button" onclick="removePaymentDetail(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa nội dung này">
                <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
            </button>
        </td>
    `;
    container.appendChild(newRow);
    
    // Initialize TomSelect for the new row select
    const selectEl = newRow.querySelector('.tom-select-payment-code');
    if (selectEl && typeof TomSelect !== 'undefined') {
        initPaymentCodeTomSelect(selectEl);
    }

    // Initialize Suggestions for quantity if function exists (e.g. Min Late)
    const qtyInputEl = newRow.querySelector('.payment-quantity-input');
    if (qtyInputEl && typeof initPaymentQuantitySuggestions === 'function') {
        initPaymentQuantitySuggestions(qtyInputEl);
    }

    // Bind change/input events for auto-calculating row total
    bindPaymentDetailEvents(newRow);
    
    paymentDetailIndex++;
    updatePaymentDetailIndexes();
    if (typeof updateOrderSummary === 'function') updateOrderSummary();
    if (typeof refreshPaymentDetailsTableLayout === 'function') refreshPaymentDetailsTableLayout();
}

function removePaymentDetail(button) {
    const row = button.closest('.payment-detail-row');
    if (row) {
        row.remove();
        updatePaymentDetailIndexes();
        if (typeof updateOrderSummary === 'function') updateOrderSummary();
        if (typeof refreshPaymentDetailsTableLayout === 'function') refreshPaymentDetailsTableLayout();
    }
}

function calculatePaymentDetailRowTotal(row) {
    const qtyInput = row.querySelector('input[name*="[quantity]"]');
    const priceInput = row.querySelector('input[name*="[price]"]');
    const priceOnlyInput = row.querySelector('input[name*="[price_only]"]');
    const totalInput = row.querySelector('input[name*="[total]"]');
    
    if (!qtyInput || !priceInput || !totalInput) return;
    
    const quantity = parseFloat(qtyInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    const priceOnly = priceOnlyInput ? (parseFloat(priceOnlyInput.value) || 0) : 0;
    const total = quantity * (price + priceOnly);
    
    totalInput.value = total > 0 ? Math.round(total).toLocaleString('vi-VN') : 0;
    if (typeof updateOrderSummary === 'function') updateOrderSummary();
}

function updatePaymentDetailIndexes() {
    const table = document.querySelector('table[data-order-resize-group="min_late_payment"]');
    const headers = table ? Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim().replace(/\s*\*$/, '')) : [];

    const rows = document.querySelectorAll('.payment-detail-row');
    rows.forEach((row, index) => {
        const indexSpan = row.querySelector('.detail-index');
        if (indexSpan) {
            indexSpan.textContent = index + 1;
        }

        row.querySelectorAll('td').forEach((td, colIndex) => {
            if (headers[colIndex] && headers[colIndex] !== 'STT' && headers[colIndex] !== 'Xóa' && headers[colIndex] !== 'Hành động') {
                td.setAttribute('data-label', headers[colIndex]);
            }
        });
    });
}

function bindPaymentDetailEvents(row) {
    const inputs = row.querySelectorAll('input[name*="[quantity]"], input[name*="[price]"], input[name*="[price_only]"]');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            calculatePaymentDetailRowTotal(row);
        });
    });
}

function handlePaymentDetailCodeSelected(row, rawCode) {
    if (!row || rawCode === undefined || rawCode === null) return;
    const selectedCode = String(rawCode).trim();
    if (!selectedCode) return;

    const codeLower = selectedCode.toLowerCase();

    // Lấy dữ liệu nguồn cho cả 3 loại (kiểm tra cả window và biến global)
    const woodList = window.woodBoardPricesData || (typeof woodBoardPricesData !== 'undefined' ? woodBoardPricesData : []);
    const glassList = window.glassPricesData || (typeof glassPricesData !== 'undefined' ? glassPricesData : []);
    const minLateList = window.minLatePricesData || (typeof minLatePricesData !== 'undefined' ? minLatePricesData : []);

    // 1. Tìm trong WoodBoardPrice (Tấm ván riêng / Ván mộc)
    const woodPrice = woodList.find(p => p && p.code && String(p.code).trim().toLowerCase() === codeLower);

    // 2. Tìm trong GlassPrice (Kính & nhôm)
    const glassPrice = glassList.find(p => {
        if (!p) return false;
        const c = p.code ? String(p.code).trim().toLowerCase() : '';
        const n = p.product_name ? String(p.product_name).trim().toLowerCase() : '';
        return c === codeLower || n === codeLower;
    });

    // 3. Tìm trong MinLatePrice (Dịch vụ min-late)
    const minLatePrice = minLateList.find(p => {
        if (!p) return false;
        const c = p.code ? String(p.code).trim().toLowerCase() : '';
        const pc = p.product_code ? String(p.product_code).trim().toLowerCase() : '';
        const ml = ('ML' + p.id).toLowerCase();
        return c === codeLower || pc === codeLower || ml === codeLower;
    });

    const nameInput = row.querySelector('[name*="[name]"]');
    const unitInput = row.querySelector('[name*="[unit]"]');
    const priceInput = row.querySelector('input[name*="[price]"]:not([name*="[price_only]"])');
    const priceOnlyInput = row.querySelector('[name*="[price_only]"]');

    if (woodPrice) {
        if (nameInput) {
            nameInput.value = woodPrice.name ? `${woodPrice.code} - ${woodPrice.name}` : (woodPrice.code || '');
            applyFlashEffect(nameInput);
            nameInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (unitInput) {
            unitInput.value = woodPrice.unit || 'tấm';
            applyFlashEffect(unitInput);
            unitInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (priceInput) {
            const rawPr = woodPrice.price_board !== undefined ? woodPrice.price_board : (woodPrice.price !== undefined ? woodPrice.price : 0);
            priceInput.value = Math.round(parseFloat(rawPr) || 0);
            applyFlashEffect(priceInput);
            priceInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (priceOnlyInput) {
            priceOnlyInput.value = 0;
            priceOnlyInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    } else if (glassPrice) {
        if (nameInput) {
            nameInput.value = glassPrice.product_name ? `${glassPrice.code} - ${glassPrice.product_name}` : (glassPrice.code || '');
            applyFlashEffect(nameInput);
            nameInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (unitInput) {
            unitInput.value = glassPrice.unit || 'm²';
            applyFlashEffect(unitInput);
            unitInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (priceInput) {
            const rawPr = glassPrice.price !== undefined ? glassPrice.price : (glassPrice.unit_price !== undefined ? glassPrice.unit_price : 0);
            priceInput.value = Math.round(parseFloat(rawPr) || 0);
            applyFlashEffect(priceInput);
            priceInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (priceOnlyInput) {
            priceOnlyInput.value = 0;
            priceOnlyInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    } else if (minLatePrice) {
        if (nameInput) {
            nameInput.value = minLatePrice.product_name || (minLatePrice.category_name || '');
            applyFlashEffect(nameInput);
            nameInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (unitInput) {
            unitInput.value = minLatePrice.unit || 'm';
            applyFlashEffect(unitInput);
            unitInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (priceInput) {
            const rawPr = minLatePrice.price !== undefined ? minLatePrice.price : (minLatePrice.unit_price !== undefined ? minLatePrice.unit_price : 0);
            priceInput.value = Math.round(parseFloat(rawPr) || 0);
            applyFlashEffect(priceInput);
            priceInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (priceOnlyInput) {
            priceOnlyInput.value = 0;
            applyFlashEffect(priceOnlyInput);
            priceOnlyInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    calculatePaymentDetailRowTotal(row);
    if (typeof updateOrderSummary === 'function') updateOrderSummary();
}

window.addPaymentDetailWithData = function(code, name, unit, qty, price, priceOnly) {
    if (typeof addPaymentDetail !== 'function') return;
    addPaymentDetail();
    const rows = document.querySelectorAll('#payment-details-container tr.payment-detail-row');
    const newRow = rows[rows.length - 1];
    if (!newRow) return;

    const nameInput = newRow.querySelector('textarea[name*="[name]"]');
    const unitInput = newRow.querySelector('input[name*="[unit]"]');
    const qtyInput = newRow.querySelector('input[name*="[quantity]"]');
    const priceInput = newRow.querySelector('input[name*="[price]"]');
    const priceOnlyInput = newRow.querySelector('input[name*="[price_only]"]');

    if (nameInput) nameInput.value = name;
    if (unitInput) unitInput.value = unit;
    if (qtyInput) qtyInput.value = qty;
    if (priceInput) priceInput.value = price;
    if (priceOnlyInput) priceOnlyInput.value = priceOnly;

    const select = newRow.querySelector('select.order-payment-code-select');
    if (select) {
        select.value = code;
        if (select.tomselect) {
            select.tomselect.setValue(code, true);
        }
    }

    calculatePaymentDetailRowTotal(newRow);
    if (typeof updateOrderSummary === 'function') updateOrderSummary();
};

function initPaymentCodeTomSelect(selectEl) {
    if (!selectEl || selectEl.tomselect) return;
    const row = selectEl.closest('.payment-detail-row');
    selectEl.style.setProperty('display', 'none', 'important');

    const ts = new TomSelect(selectEl, {
        wrapperClass: 'ts-wrapper tom-select-payment-code',
        create: true,
        placeholder: '-- Chọn mã --',
        allowEmptyOption: true,
        maxOptions: null,
        dropdownParent: 'body',
        searchField: ['text', 'value'],
        render: {
            item: function(data, escape) {
                const label = data.value || data.text || '';
                return '<div class="font-bold text-neutral-800">' + escape(label) + '</div>';
            },
            option: function(data, escape) {
                return '<div>' + escape(data.text || data.value || '') + '</div>';
            }
        }
    });

    selectEl.style.setProperty('display', 'none', 'important');

    let lastFilledCode = null;
    const triggerFill = (val) => {
        const targetRow = row || selectEl.closest('.payment-detail-row');
        const code = (val !== undefined && val !== null && val !== '') ? val : (ts ? ts.getValue() : selectEl.value);
        if (!code || code === lastFilledCode) return;
        lastFilledCode = code;
        handlePaymentDetailCodeSelected(targetRow, code);
    };

    ts.on('change', function(value) {
        triggerFill(value);
    });

    ts.on('item_add', function(value) {
        triggerFill(value);
    });

    selectEl.addEventListener('change', function() {
        triggerFill(this.value);
    });
}

function togglePaymentDetailsSection() {
    const section = document.getElementById('payment-details-section');
    const btn = document.getElementById('toggle-payment-details-btn');
    const container = document.getElementById('payment-details-container');
    if (!section) return;

    if (section.style.display === 'none' || section.style.display === '') {
        section.style.display = 'block';
        if (btn) {
            btn.classList.remove('bg-blue-50', 'text-blue-600', 'hover:bg-blue-100', 'border-blue-200');
            btn.classList.add('bg-danger-50', 'text-danger-600', 'hover:bg-danger-100', 'border-danger-200');
            btn.innerHTML = '<iconify-icon icon="lucide:eye-off" class="text-lg"></iconify-icon> <span class="mobile-hide-text">Ẩn chi tiết hóa đơn</span>';
        }
        if (container && container.querySelectorAll('tr.payment-detail-row').length === 0) {
            addPaymentDetail();
        }
    } else {
        section.style.display = 'none';
        if (btn) {
            btn.classList.remove('bg-danger-50', 'text-danger-600', 'hover:bg-danger-100', 'border-danger-200');
            btn.classList.add('bg-blue-50', 'text-blue-600', 'hover:bg-blue-100', 'border-blue-200');
            btn.innerHTML = '<iconify-icon icon="lucide:receipt" class="text-lg"></iconify-icon> <span class="mobile-hide-text">Chi tiết hóa đơn dịch vụ</span>';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Khởi tạo TomSelect cho tất cả dòng chi tiết hóa đơn hiện có
    document.querySelectorAll('#payment-details-container .tom-select-payment-code').forEach(el => {
        initPaymentCodeTomSelect(el);
    });
    // Gán sự kiện tính toán cho các dòng
    document.querySelectorAll('#payment-details-container .payment-detail-row').forEach(row => {
        bindPaymentDetailEvents(row);
    });
});
</script>
