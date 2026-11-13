@php
    $isWarranty = $isWarranty ?? (isset($acrylicOrder) && ($acrylicOrder->relation_type ?? null) === 'warranty');
    $paymentDetailsList = collect();
    if (old('payment_details') && is_array(old('payment_details'))) {
        foreach (old('payment_details') as $dIndex => $dData) {
            $d = new \stdClass();
            $d->id = $dData['id'] ?? null;
            $d->name = $dData['name'] ?? '';
            $d->unit = $dData['unit'] ?? '';
            $d->length = $dData['length'] ?? null;
            $d->width = $dData['width'] ?? null;
            $d->quantity = $dData['quantity'] ?? 1;
            $d->price = $dData['price'] ?? 0;
            $d->price_only = $dData['price_only'] ?? 0;
            $rawTotal = $dData['total'] ?? 0;
            if (is_string($rawTotal)) {
                $rawTotal = str_replace(['.', ',', ' ', 'đ', '₫'], '', $rawTotal);
            }
            $d->total = is_numeric($rawTotal) ? (float)$rawTotal : 0;
            $d->formula = $dData['formula'] ?? null;
            $paymentDetailsList->push($d);
        }
    } elseif (isset($acrylicOrder) && isset($acrylicOrder->paymentDetails) && $acrylicOrder->paymentDetails->count() > 0) {
        $paymentDetailsList = $acrylicOrder->paymentDetails;
    }
    $hasPaymentDetails = !$isWarranty && $paymentDetailsList->count() > 0;
    $isMinLateOrder = in_array($currentOrderType ?? '', ['min_late', 'Flywood', 'plywood']);
    $woodBoardPrices = $woodBoardPrices ?? \App\Models\WoodBoardPrice::orderBy('code', 'asc')->get();
    $glassPrices = $glassPrices ?? \App\Models\GlassPrice::orderBy('code', 'asc')->get();
    $minLatePrices = $minLatePrices ?? \App\Models\MinLatePrice::orderBy('category_name', 'asc')->orderBy('stt', 'asc')->get();
    $serviceCombos = \App\Models\OrderServiceCombo::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
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
<div id="payment-details-section" class="order-supplies-popup bg-white border border-neutral-200 rounded-xl p-6 shadow-sm mt-6 relative pt-8" style="{{ ($hasPaymentDetails || ($currentOrderType ?? '') === 'service') ? '' : 'display: none;' }}" data-order-supplies-zoom-panel data-order-supplies-storage-key="min_late_payment" data-order-supplies-zoom="100" data-order-supplies-visible-rows-disabled="1">
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
            @if(isset($serviceCombos) && $serviceCombos->count() > 0)
            <div class="relative inline-block text-left" id="service-combo-dropdown-wrapper">
                <button type="button" onclick="toggleServiceComboMenu(event)" class="btn btn-sm bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-lg flex items-center gap-1.5 font-semibold transition-all shadow-2xs" title="Nạp nhanh tổ hợp chi tiết dịch vụ & vật tư mẫu">
                    <iconify-icon icon="lucide:layers" class="text-base text-purple-600"></iconify-icon>
                    <span class="mobile-hide-text">Nạp tổ hợp mẫu</span>
                    <iconify-icon icon="lucide:chevron-down" class="text-xs text-purple-500"></iconify-icon>
                </button>
                <div id="service-combo-dropdown-menu" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-neutral-200 py-2 z-50 divide-y divide-neutral-100 text-left">
                    @php
                        $curType = $currentOrderType ?? 'min_late';
                        $curTypeCombos = $serviceCombos->filter(fn($c) => $c->order_type === $curType);
                        $otherCombos = $serviceCombos->filter(fn($c) => $c->order_type !== $curType);
                    @endphp
                    @if($curTypeCombos->count() > 0)
                        <div class="px-3 py-1 text-[11px] font-bold text-neutral-400 uppercase tracking-wider">
                            Dành cho {{ $curType }}
                        </div>
                        <div class="py-1">
                            @foreach($curTypeCombos as $combo)
                                <button type="button" onclick="applyServiceCombo({{ $combo->id }})" class="w-full text-left px-3 py-2 hover:bg-purple-50 flex items-start gap-2.5 transition-colors group">
                                    <iconify-icon icon="lucide:sparkles" class="text-base text-purple-500 mt-0.5 shrink-0"></iconify-icon>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-bold text-neutral-800 group-hover:text-purple-700 flex items-center gap-1.5">
                                            <span class="truncate">{{ $combo->name }}</span>
                                            @if($combo->is_default)
                                                <span class="text-[10px] bg-emerald-100 text-emerald-700 font-semibold px-1.5 py-0.2 rounded shrink-0">Mặc định</span>
                                            @endif
                                        </div>
                                        @if($combo->description)
                                            <div class="text-[11px] text-neutral-500 truncate">{{ $combo->description }}</div>
                                        @endif
                                        <div class="text-[10px] text-neutral-400 mt-0.5">{{ count($combo->items ?? []) }} hạng mục</div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @if($otherCombos->count() > 0)
                        <div class="px-3 py-1 text-[11px] font-bold text-neutral-400 uppercase tracking-wider mt-1">
                            Các tổ hợp khác
                        </div>
                        <div class="py-1 max-h-48 overflow-y-auto">
                            @foreach($otherCombos as $combo)
                                <button type="button" onclick="applyServiceCombo({{ $combo->id }})" class="w-full text-left px-3 py-2 hover:bg-neutral-50 flex items-start gap-2.5 transition-colors group">
                                    <iconify-icon icon="lucide:box" class="text-base text-neutral-400 mt-0.5 shrink-0"></iconify-icon>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-semibold text-neutral-700 group-hover:text-primary-700 flex items-center gap-1.5">
                                            <span class="truncate">{{ $combo->name }}</span>
                                            <span class="text-[10px] text-neutral-400">({{ $combo->order_type }})</span>
                                        </div>
                                        <div class="text-[10px] text-neutral-400 mt-0.5">{{ count($combo->items ?? []) }} hạng mục</div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="pt-1.5 px-3">
                        <a href="{{ route('minlate_prices.index', ['tab' => 'combos']) }}" target="_blank" class="text-xs text-primary-600 hover:text-primary-800 font-semibold flex items-center gap-1 py-1">
                            <iconify-icon icon="lucide:settings" class="text-sm"></iconify-icon>
                            <span>Quản lý & Cài đặt tổ hợp</span>
                            <iconify-icon icon="lucide:external-link" class="text-xs ml-auto text-neutral-400"></iconify-icon>
                        </a>
                    </div>
                </div>
            </div>
            @endif
            <button type="button" onclick="addPaymentDetail()" class="btn btn-sm btn-primary rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon> <span class="mobile-hide-text">Thêm nội dung</span>
            </button>
        </div>
    </div>
    <div class="order-supplies-body">
        <div class="overflow-x-auto pb-3" data-order-supplies-table-scroll>
            <div class="order-supplies-table-zoom-wrap" data-order-supplies-table-zoom-wrap>
                <table class="table bordered-table sm-table mb-0 min-w-[1100px] border border-neutral-200" data-order-resize-group="min_late_payment">
                    <thead>
                        <tr class="bg-neutral-50 text-center">
                            <th scope="col" style="width: 50px; min-width: 50px; white-space: nowrap;" class="sticky-stt-th text-center border border-neutral-200 font-bold text-xs text-neutral-600 uppercase"><span class="order-stt-header-label">STT</span></th>
                            <th scope="col" style="width: 140px; min-width: 120px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã</th>
                            <th scope="col" style="white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên nội dung <span class="text-danger-500">*</span></th>
                            <th scope="col" style="width: 90px; min-width: 80px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn vị</th>
                            <th scope="col" style="width: 90px; min-width: 80px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Dài (mm)</th>
                            <th scope="col" style="width: 90px; min-width: 80px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Rộng (mm)</th>
                            <th scope="col" style="width: 90px; min-width: 80px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng</th>
                            <th scope="col" style="width: 130px; min-width: 120px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                            @if($isMinLateOrder)
                            <th scope="col" style="width: 120px; min-width: 110px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá chỉ</th>
                            @endif
                            <th scope="col" style="width: 140px; min-width: 130px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                            <th scope="col" style="width: 70px; min-width: 70px; white-space: nowrap; position: sticky; right: 0; z-index: 2; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase bg-neutral-50">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="payment-details-container">
                        @if(isset($paymentDetailsList) && $paymentDetailsList->count() > 0)
                            @foreach($paymentDetailsList as $detailIndex => $detail)
                            @php
                                $rawDetailF = $detail->formula ?? null;
                                $detailFormulas = is_array($rawDetailF) ? $rawDetailF : (is_string($rawDetailF) ? (json_decode($rawDetailF, true) ?: []) : []);
                            @endphp
                            <tr class="payment-detail-row">
                                <input type="hidden" name="payment_details[{{ $detailIndex }}][formula]" class="row-formula-input" value="{{ !empty($detailFormulas) ? json_encode($detailFormulas) : '' }}">
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
                                        <optgroup label="Tấm ván riêng / Ván mộc">
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
                                <td style="width: 90px; min-width: 80px; padding: 0 !important;" class="border border-neutral-200">
                                    <input type="text" name="payment_details[{{ $detailIndex }}][unit]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs w-full" placeholder="tấm, m², bộ..." value="{{ $detail->unit }}">
                                </td>
                                <td style="width: 90px; min-width: 80px; padding: 0 !important;" class="border border-neutral-200">
                                    <input type="text" name="payment_details[{{ $detailIndex }}][length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs w-full" placeholder="Dài" value="{{ $detail->length ?? '' }}" autocomplete="off" @if(!empty($detailFormulas['length'])) data-formula="{{ $detailFormulas['length'] }}" title="Công thức: {{ $detailFormulas['length'] }}" @endif>
                                </td>
                                <td style="width: 90px; min-width: 80px; padding: 0 !important;" class="border border-neutral-200">
                                    <input type="text" name="payment_details[{{ $detailIndex }}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs w-full" placeholder="Rộng" value="{{ $detail->width ?? '' }}" autocomplete="off" @if(!empty($detailFormulas['width'])) data-formula="{{ $detailFormulas['width'] }}" title="Công thức: {{ $detailFormulas['width'] }}" @endif>
                                </td>
                                <td style="width: 90px; min-width: 80px; padding: 0 !important;" class="border border-neutral-200">
                                    <input type="text" name="payment_details[{{ $detailIndex }}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs payment-quantity-input w-full" placeholder="1" value="{{ $detail->quantity }}" autocomplete="off" @if(!empty($detailFormulas['quantity'])) data-formula="{{ $detailFormulas['quantity'] }}" title="Công thức: {{ $detailFormulas['quantity'] }}" @endif>
                                </td>
                                <td style="width: 130px; min-width: 120px; padding: 0 !important;" class="border border-neutral-200">
                                    <input type="text" name="payment_details[{{ $detailIndex }}][price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs w-full" placeholder="0" required value="{{ $detail->price }}" autocomplete="off" @if(!empty($detailFormulas['price'])) data-formula="{{ $detailFormulas['price'] }}" title="Công thức: {{ $detailFormulas['price'] }}" @endif>
                                </td>
                                @if($isMinLateOrder)
                                <td style="width: 120px; min-width: 120px; padding: 0 !important;" class="border border-neutral-200">
                                    <input type="text" name="payment_details[{{ $detailIndex }}][price_only]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs w-full" placeholder="0" value="{{ $detail->price_only }}" autocomplete="off" @if(!empty($detailFormulas['price_only'])) data-formula="{{ $detailFormulas['price_only'] }}" title="Công thức: {{ $detailFormulas['price_only'] }}" @endif>
                                </td>
                                @else
                                <input type="hidden" name="payment_details[{{ $detailIndex }}][price_only]" value="{{ $detail->price_only ?? 0 }}">
                                @endif
                                <td style="width: 150px; min-width: 150px; padding: 0 !important;" class="border border-neutral-200">
                                    @php
                                        $dtTotal = $detail->total ?? 0;
                                        if (is_string($dtTotal)) {
                                            $dtTotal = str_replace(['.', ',', ' ', 'đ', '₫'], '', $dtTotal);
                                        }
                                        $dtTotal = is_numeric($dtTotal) ? (float)$dtTotal : 0;
                                    @endphp
                                    <input type="text" name="payment_details[{{ $detailIndex }}][total]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center h-8 text-xs w-full" placeholder="0" readonly value="{{ number_format($dtTotal, 0, ',', '.') }}">
                                </td>
                                <td style="width: 80px; min-width: 80px; padding: 0 !important; position: sticky; right: 0; z-index: 1; background-color: #fff; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="text-center align-middle border border-neutral-200">
                                    <button type="button" onclick="removePaymentDetail(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa nội dung này">
                                        <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                                    </button>
                                    <input type="hidden" name="payment_details[{{ $detailIndex }}][id]" value="{{ $detail->id ?? '' }}">
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
window.orderServiceCombosData = @json($serviceCombos ?? []);
var paymentDetailIndex = typeof window.paymentDetailIndex !== 'undefined' ? window.paymentDetailIndex : {{ isset($acrylicOrder) && isset($acrylicOrder->paymentDetails) ? $acrylicOrder->paymentDetails->count() : 0 }};
window.paymentDetailIndex = paymentDetailIndex;
var isMinLateOrderType = {{ $isMinLateOrder ? 'true' : 'false' }};

function getPaymentCodeOptionsHtml() {
    let optionsHtml = '<option value="">-- Chọn mã --</option>';
    
    // 1. Tấm ván riêng / Ván mộc
    const boardPrices = window.woodBoardPricesData || [];
    if (boardPrices.length > 0) {
        optionsHtml += '<optgroup label="Tấm ván riêng / Ván mộc">';
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
        <input type="hidden" name="payment_details[${paymentDetailIndex}][formula]" class="row-formula-input" value="">
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
        <td style="width: 90px; min-width: 80px; padding: 0 !important;" class="border border-neutral-200">
            <input type="text" name="payment_details[${paymentDetailIndex}][unit]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs w-full" placeholder="tấm, m², bộ...">
        </td>
        <td style="width: 90px; min-width: 80px; padding: 0 !important;" class="border border-neutral-200">
            <input type="text" name="payment_details[${paymentDetailIndex}][length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs w-full" placeholder="Dài" autocomplete="off">
        </td>
        <td style="width: 90px; min-width: 80px; padding: 0 !important;" class="border border-neutral-200">
            <input type="text" name="payment_details[${paymentDetailIndex}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs w-full" placeholder="Rộng" autocomplete="off">
        </td>
        <td style="width: 90px; min-width: 80px; padding: 0 !important;" class="border border-neutral-200">
            <input type="text" name="payment_details[${paymentDetailIndex}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs payment-quantity-input w-full" placeholder="1" value="1" autocomplete="off">
        </td>
        <td style="width: 130px; min-width: 120px; padding: 0 !important;" class="border border-neutral-200">
            <input type="text" name="payment_details[${paymentDetailIndex}][price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs w-full" placeholder="0" required value="0" autocomplete="off">
        </td>
        ${isMinLateOrderType ? `
        <td style="width: 120px; min-width: 110px; padding: 0 !important;" class="border border-neutral-200">
            <input type="text" name="payment_details[${paymentDetailIndex}][price_only]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs w-full" placeholder="0" value="0" autocomplete="off">
        </td>` : `
        <input type="hidden" name="payment_details[${paymentDetailIndex}][price_only]" value="0">
        `}
        <td style="width: 140px; min-width: 130px; padding: 0 !important;" class="border border-neutral-200">
            <input type="text" name="payment_details[${paymentDetailIndex}][total]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center h-8 text-xs w-full" placeholder="0" readonly value="0">
        </td>
        <td style="width: 70px; min-width: 70px; padding: 0 !important; position: sticky; right: 0; z-index: 1; background-color: #fff; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="text-center align-middle border border-neutral-200">
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
    
    const parseFn = typeof window.parseInputValue === 'function' ? window.parseInputValue : (el => parseFloat(el?.value) || 0);
    const quantity = parseFn(qtyInput);
    const price = parseFn(priceInput);
    const priceOnly = priceOnlyInput ? parseFn(priceOnlyInput) : 0;
    const total = Math.round(quantity * (price + priceOnly));
    
    totalInput.value = total > 0 ? total.toLocaleString('vi-VN') : 0;
    totalInput.setAttribute('data-exact-value', total > 0 ? total : 0);
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
        ['input', 'change', 'blur'].forEach(evtName => {
            input.addEventListener(evtName, () => {
                calculatePaymentDetailRowTotal(row);
            });
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
        const lengthInput = row.querySelector('[name*="[length]"]');
        const widthInput = row.querySelector('[name*="[width]"]');
        if (lengthInput && !lengthInput.value) {
            lengthInput.value = 2440;
            applyFlashEffect(lengthInput);
            lengthInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (widthInput && !widthInput.value) {
            widthInput.value = 1220;
            applyFlashEffect(widthInput);
            widthInput.dispatchEvent(new Event('input', { bubbles: true }));
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
        if (minLatePrice.default_formula) {
            const qtyInput = row.querySelector('input[name*="[quantity]"]');
            if (qtyInput) {
                const defF = String(minLatePrice.default_formula).trim();
                const formulaVal = defF.startsWith('=') ? defF : ('=' + defF);
                qtyInput.value = formulaVal;
                qtyInput.dataset.formula = formulaVal;
                if (typeof processFormulaForInput === 'function') {
                    processFormulaForInput(qtyInput, true);
                }
                applyFlashEffect(qtyInput);
            }
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

function toggleServiceComboMenu(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const menu = document.getElementById('service-combo-dropdown-menu');
    if (menu) menu.classList.toggle('hidden');
}

document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('service-combo-dropdown-wrapper');
    const menu = document.getElementById('service-combo-dropdown-menu');
    if (menu && !menu.classList.contains('hidden')) {
        if (!wrapper || !wrapper.contains(e.target)) {
            menu.classList.add('hidden');
        }
    }
});

window.applyServiceCombo = function(comboId, replace = true) {
    const menu = document.getElementById('service-combo-dropdown-menu');
    if (menu) menu.classList.add('hidden');

    const combos = window.orderServiceCombosData || [];
    const combo = combos.find(c => c.id == comboId);
    if (!combo) return;

    const container = document.getElementById('payment-details-container');
    if (!container) return;

    if (replace) {
        const existingRows = container.querySelectorAll('tr.payment-detail-row');
        let hasData = false;
        existingRows.forEach(r => {
            const name = r.querySelector('textarea[name*="[name]"]')?.value?.trim();
            if (name) hasData = true;
        });

        if (hasData) {
            if (!confirm(`Nạp tổ hợp "${combo.name}" sẽ thay thế các chi tiết hóa đơn hiện có. Bạn có muốn tiếp tục?`)) {
                return;
            }
        }
        container.innerHTML = '';
        window.paymentDetailIndex = 0;
    }

    if (!combo.items || !Array.isArray(combo.items) || combo.items.length === 0) {
        alert('Tổ hợp mẫu này chưa có hạng mục nào!');
        return;
    }

    combo.items.forEach(item => {
        addPaymentDetail();
        const rows = container.querySelectorAll('tr.payment-detail-row');
        const newRow = rows[rows.length - 1];
        if (!newRow) return;

        const nameInput = newRow.querySelector('textarea[name*="[name]"]');
        const unitInput = newRow.querySelector('input[name*="[unit]"]');
        const lengthInput = newRow.querySelector('input[name*="[length]"]');
        const widthInput = newRow.querySelector('input[name*="[width]"]');
        const qtyInput = newRow.querySelector('input[name*="[quantity]"]');
        const priceInput = newRow.querySelector('input[name*="[price]"]');
        const priceOnlyInput = newRow.querySelector('input[name*="[price_only]"]');

        if (nameInput) nameInput.value = item.name || '';
        if (unitInput) unitInput.value = item.unit || '';
        if (lengthInput && item.length) lengthInput.value = item.length;
        if (widthInput && item.width) widthInput.value = item.width;
        if (priceInput) priceInput.value = item.price || 0;
        if (priceOnlyInput) priceOnlyInput.value = item.price_only || 0;

        // Xử lý công thức nếu có
        const formula = item.formula ? String(item.formula).trim() : '';
        if (formula) {
            const formulaVal = formula.startsWith('=') ? formula : ('=' + formula);
            if (qtyInput) {
                qtyInput.value = formulaVal;
                qtyInput.dataset.formula = formulaVal;
                if (typeof processFormulaForInput === 'function') {
                    processFormulaForInput(qtyInput, false);
                }
            }
        } else {
            if (qtyInput) {
                qtyInput.value = (item.quantity !== undefined && item.quantity !== null && item.quantity !== '') ? item.quantity : 1;
            }
        }

        // Chọn mã nếu có
        const select = newRow.querySelector('select.order-payment-code-select');
        if (select && item.code) {
            select.value = item.code;
            if (select.tomselect) {
                select.tomselect.setValue(item.code, true);
            }
        }

        if (typeof syncRowFormulasToHiddenInput === 'function') {
            syncRowFormulasToHiddenInput(newRow);
        }
        calculatePaymentDetailRowTotal(newRow);
    });

    if (typeof updateOrderSummary === 'function') {
        updateOrderSummary();
    }
    if (typeof refreshPaymentDetailsTableLayout === 'function') {
        refreshPaymentDetailsTableLayout();
    }
};

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
            const curType = @json($currentOrderType ?? 'min_late');
            const defaultCombo = (window.orderServiceCombosData || []).find(c => c.order_type === curType && c.is_default);
            if (defaultCombo && typeof applyServiceCombo === 'function') {
                applyServiceCombo(defaultCombo.id, false);
            } else {
                addPaymentDetail();
            }
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
