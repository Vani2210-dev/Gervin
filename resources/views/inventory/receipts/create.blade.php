@extends('layout.layout')
@php
    $title    = 'Nhập kho';
    $subTitle = 'Tạo phiếu nhập mới';
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
            <div class="p-4 border-b border-neutral-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('inventory.receipts.index') }}" class="w-8 h-8 rounded-lg bg-neutral-100 hover:bg-neutral-200 flex items-center justify-center text-neutral-600 transition-colors">
                        <iconify-icon icon="solar:arrow-left-outline" class="text-lg"></iconify-icon>
                    </a>
                    <div>
                        <h5 class="font-bold text-base text-neutral-800 mb-0">Tạo Phiếu Nhập kho (PNK)</h5>
                        <p class="text-xs text-neutral-500 mb-0">Nhập nhiều mặt hàng vật tư cùng lúc vào kho.</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('inventory.receipts.store') }}" method="POST">
                @csrf
                <div class="p-6">
                    {{-- Master Info Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 bg-neutral-50 p-4 rounded-xl border border-neutral-200">
                        <div>
                            <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Mã phiếu <span class="text-danger-500">*</span></label>
                            <input type="text" name="code" value="{{ $nextCode }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs font-bold text-primary-700 focus:border-primary-500" required>
                        </div>
                        <div>
                            <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Ngày nhập <span class="text-danger-500">*</span></label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" required>
                        </div>
                        <div>
                            <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Kho nhận <span class="text-danger-500">*</span></label>
                            <select name="warehouse_id" id="select_warehouse" class="form-select rounded-lg w-full border border-neutral-300 px-3 py-2 text-xs font-semibold text-neutral-800 bg-white focus:border-primary-500 focus:ring-primary-500" required>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="form-label text-xs font-bold text-neutral-700 block mb-0">Nhà cung cấp (NCC)</label>
                                <button type="button" onclick="openQuickSupplierModal()" class="text-[11px] text-primary-600 hover:text-primary-800 font-bold flex items-center gap-0.5">
                                    <iconify-icon icon="ic:baseline-plus"></iconify-icon> Thêm mới NCC
                                </button>
                            </div>
                            <select name="supplier_id" id="select_supplier" class="form-select rounded-lg w-full border border-neutral-300 px-3 py-2 text-xs font-semibold text-neutral-800 bg-white focus:border-primary-500 focus:ring-primary-500" onchange="onSupplierChanged(this)">
                                <option value="">-- Chọn NCC (hoặc để trống) --</option>
                                @foreach($suppliers ?? [] as $sup)
                                    <option value="{{ $sup->id }}" data-name="{{ $sup->name }}" data-debt="{{ $sup->debt }}" {{ (string)old('supplier_id', request('supplier_id')) === (string)$sup->id ? 'selected' : '' }}>
                                        [{{ $sup->code }}] {{ $sup->name }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="supplier_name" id="input_supplier_name" value="{{ old('supplier_name') }}">
                            <div id="supplier_debt_badge" class="mt-1 text-[11px] text-rose-600 font-bold hidden">
                                Công nợ NCC hiện tại: <span id="supplier_debt_val">0</span>đ
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="form-label text-xs font-bold text-neutral-700 block mb-0">Đơn hàng liên quan <span class="text-neutral-400 font-normal">(Tùy chọn)</span></label>
                                <button type="button" id="btnLoadOrderItems" onclick="loadSelectedOrderSupplies()" class="text-[11px] text-primary-600 hover:text-primary-800 font-bold flex items-center gap-0.5 {{ empty($selectedOrder) ? 'hidden' : '' }}" title="Nạp danh sách vật tư từ đơn hàng này vào bảng bên dưới">
                                    <iconify-icon icon="solar:download-square-bold"></iconify-icon> Nạp vật tư đơn
                                </button>
                            </div>
                            <select name="order_id" id="select_order" class="form-select rounded-lg w-full border border-neutral-300 px-3 py-2 text-xs font-semibold text-neutral-800 bg-white focus:border-primary-500 focus:ring-primary-500" onchange="onOrderChanged(this)">
                                <option value="">-- Không gắn đơn hàng --</option>
                                @if(!empty($selectedOrder) && !$orders->contains('id', $selectedOrder->id))
                                    <option value="{{ $selectedOrder->id }}" selected>
                                        [{{ $selectedOrder->order_code }}] {{ $selectedOrder->customer_name ?: 'Khách hàng' }}
                                    </option>
                                @endif
                                @foreach($orders ?? [] as $ord)
                                    <option value="{{ $ord->id }}" {{ (string)old('order_id', request('order_id')) === (string)$ord->id ? 'selected' : '' }}>
                                        [{{ $ord->order_code }}] {{ $ord->customer_name ?: 'Khách hàng' }} ({{ date('d/m/Y', strtotime($ord->order_date)) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Người giao hàng</label>
                            <input type="text" name="deliverer" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="Tên tài xế / người giao">
                        </div>
                        <div>
                            <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Người nhận (Thủ kho)</label>
                            <input type="text" name="receiver" value="{{ auth()->user()->name ?? 'Thủ kho' }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                        </div>
                        <div>
                            <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Ghi chú phiếu nhập</label>
                            <input type="text" name="notes" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="Nhập theo hóa đơn, container số..." value="{{ !empty($selectedOrder) ? 'Nhập kho cho đơn hàng #' . $selectedOrder->order_code : '' }}">
                        </div>
                    </div>

                    {{-- Items Details Table --}}
                    <div class="border border-neutral-200 rounded-xl overflow-hidden mb-6">
                        <div class="p-3 bg-neutral-100 border-b border-neutral-200 flex items-center justify-between">
                            <h6 class="font-bold text-neutral-800 text-xs uppercase mb-0">Danh sách vật tư nhập kho</h6>
                            <button type="button" onclick="addItemRow()" class="btn btn-sm btn-primary px-3 py-1 rounded-lg text-xs font-bold flex items-center gap-1 shadow-sm">
                                <iconify-icon icon="ic:baseline-plus" class="text-sm"></iconify-icon> Thêm dòng
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left border-collapse" id="itemsTable">
                                <thead class="bg-neutral-50 text-neutral-700 font-bold border-b border-neutral-200">
                                    <tr>
                                        <th class="py-2 px-3 w-12 text-center">STT</th>
                                        <th class="py-2 px-3 min-w-[340px]" id="thMaterialCol">Mặt hàng vật tư (Mã ván / Chỉ dán) <span class="text-danger-500">*</span></th>
                                        <th class="py-2 px-3 w-20 text-center">ĐVT</th>
                                        <th class="py-2 px-3 w-24 text-right">Tồn hiện tại</th>
                                        <th class="py-2 px-3 w-32 text-right">Số lượng nhập <span class="text-danger-500">*</span></th>
                                        <th class="py-2 px-3 w-36 text-right">Đơn giá nhập (đ)</th>
                                        <th class="py-2 px-3 w-36 text-right">Thành tiền (đ)</th>
                                        <th class="py-2 px-3 min-w-[140px]">Ghi chú dòng</th>
                                        <th class="py-2 px-3 w-12 text-center"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody" class="divide-y divide-neutral-100 bg-white">
                                    {{-- Row 0 by default --}}
                                </tbody>
                                <tfoot class="bg-neutral-50 font-bold border-t-2 border-neutral-300 text-xs">
                                    <tr>
                                        <td colspan="4" class="py-3 px-3 text-right text-neutral-700 uppercase">TỔNG CỘNG:</td>
                                        <td class="py-3 px-3 text-right text-emerald-700 font-black text-sm" id="lblTotalQty">0</td>
                                        <td></td>
                                        <td class="py-3 px-3 text-right text-primary-700 font-black text-sm" id="lblTotalAmount">0đ</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Actions Bar --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200">
                        <a href="{{ route('inventory.receipts.index') }}" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-5 py-2 rounded-lg text-xs font-semibold">
                            Hủy bỏ
                        </a>
                        <button type="submit" class="btn btn-primary px-6 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm">
                            <iconify-icon icon="solar:diskette-bold" class="text-base"></iconify-icon> Hoàn thành Nhập kho
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL THÊM NHANH NHÀ CUNG CẤP --}}
<div id="quickSupplierModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-neutral-900 bg-opacity-50 transition-opacity" onclick="closeQuickSupplierModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <div class="p-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="solar:shop-2-bold" class="text-xl text-primary-600"></iconify-icon>
                    <h5 class="font-bold text-neutral-800 text-sm mb-0">Thêm nhanh Nhà cung cấp</h5>
                </div>
                <button type="button" onclick="closeQuickSupplierModal()" class="text-neutral-400 hover:text-neutral-700 text-xl font-bold leading-none">&times;</button>
            </div>

            <form id="quickSupplierForm" onsubmit="handleQuickSupplierSubmit(event)" class="p-5 space-y-3">
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Tên Nhà cung cấp <span class="text-danger-500">*</span></label>
                    <input type="text" id="quick_sup_name" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500 font-semibold" placeholder="VD: Gỗ An Cường" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Mã NCC (tùy chọn)</label>
                        <input type="text" id="quick_sup_code" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="Tự sinh nếu trống">
                    </div>
                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Số điện thoại</label>
                        <input type="text" id="quick_sup_phone" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="09...">
                    </div>
                </div>
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Địa chỉ</label>
                    <input type="text" id="quick_sup_address" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="Địa chỉ kho / trụ sở">
                </div>
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Công nợ ban đầu (đ)</label>
                    <input type="number" step="1000" id="quick_sup_initial_debt" value="0" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500 font-bold text-rose-600">
                </div>
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-neutral-200">
                    <button type="button" onclick="closeQuickSupplierModal()" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-4 py-1.5 rounded-lg text-xs font-semibold">
                        Hủy
                    </button>
                    <button type="submit" id="btnSubmitQuickSupplier" class="btn btn-primary px-4 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1 shadow-sm">
                        <iconify-icon icon="solar:check-circle-bold" class="text-sm"></iconify-icon> Lưu NCC
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .material-ts-wrapper {
        width: 100% !important;
    }
    .material-ts-wrapper .ts-control {
        border-radius: 0.5rem !important;
        border: 1px solid #e5e7eb !important;
        padding: 5px 10px !important;
        font-size: 12px !important;
        min-height: 36px !important;
        line-height: 1.25rem !important;
        background-color: #ffffff !important;
        box-shadow: none !important;
        display: flex !important;
        align-items: center !important;
        color: #1f2937 !important;
    }
    .material-ts-wrapper.focus .ts-control {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15) !important;
    }
    .material-ts-dropdown {
        font-size: 12px !important;
        border-radius: 0.5rem !important;
        border: 1px solid #e5e7eb !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08) !important;
        z-index: 99999 !important;
        max-height: 320px !important;
        overflow-y: auto !important;
        background-color: #ffffff !important;
    }
    .material-ts-dropdown .ts-dropdown-content {
        max-height: 320px !important;
    }
    .material-ts-dropdown .active {
        background-color: #eff6ff !important;
        color: #1d4ed8 !important;
    }
    select.tomselected,
    select.material-tom-select.tomselected {
        display: none !important;
    }
</style>
@endsection

@push('scripts')
<script>
    const WAREHOUSES = @json($warehouses);
    const MATERIALS = @json($materials);
    const PREFILLED_ORDER_ITEMS = @json($orderItems ?? []);

    function getCurrentWarehouse() {
        const whSelect = document.getElementById('select_warehouse');
        const whId = whSelect ? whSelect.value : '';
        return WAREHOUSES.find(w => String(w.id) === String(whId)) || null;
    }

    function isEdgeBandWarehouse() {
        const wh = getCurrentWarehouse();
        if (!wh) return false;
        const code = (wh.code || '').toUpperCase();
        return code === 'KHO-CHI' || code.includes('CHI') || wh.name.toLowerCase().includes('chỉ');
    }

    function getFilteredMaterialOptions() {
        const isEdge = isEdgeBandWarehouse();
        return MATERIALS.filter(m => {
            if (isEdge) return m.item_type === 'edge_band';
            return m.item_type !== 'edge_band';
        }).map(m => ({
            value: String(m.id),
            code: m.code || '',
            name: m.name || '',
            origin_code: m.origin_code || '',
            category: m.category || '',
            unit: m.unit || (m.item_type === 'edge_band' ? 'm' : 'Tấm'),
            price: m.cost_price || 0,
            stock: m.current_stock || 0,
            item_type: m.item_type
        }));
    }

    function onWarehouseChange() {
        const isEdge = isEdgeBandWarehouse();
        const thCol = document.getElementById('thMaterialCol');
        if (thCol) {
            thCol.innerHTML = isEdge 
                ? 'Mặt hàng Chỉ Dán Viền (Mã CHI-...) <span class="text-danger-500">*</span>' 
                : 'Mặt hàng Tấm Ván (Mã SKU) <span class="text-danger-500">*</span>';
        }

        const filtered = getFilteredMaterialOptions();
        const rows = document.querySelectorAll('#itemsTableBody tr');
        rows.forEach(r => {
            const selectEl = r.querySelector('.material-tom-select');
            if (selectEl && selectEl._tomSelect) {
                const ts = selectEl._tomSelect;
                const curVal = ts.getValue();
                ts.clear();
                ts.clearOptions();
                ts.addOptions(filtered);
                ts.refreshOptions(false);
                if (filtered.some(opt => opt.value === curVal)) {
                    ts.setValue(curVal, true);
                } else {
                    r.querySelector('.unit-cell').textContent = isEdge ? 'm' : 'Tấm';
                    r.querySelector('.stock-cell').textContent = '-';
                }
            }
        });
        calcTotals();
    }

    let rowCount = 0;

    function addItemRow(selectedMaterialId = '', qty = '', price = '', notes = '') {
        const tbody = document.getElementById('itemsTableBody');
        const tr = document.createElement('tr');
        const thisRowIdx = rowCount;
        const isEdge = isEdgeBandWarehouse();
        tr.id = `row_${thisRowIdx}`;
        tr.className = 'hover:bg-neutral-50 transition-colors';

        tr.innerHTML = `
            <td class="py-2 px-3 text-center text-neutral-400 font-medium row-stt">${tbody.children.length + 1}</td>
            <td class="py-2 px-3">
                <select id="select_mat_${thisRowIdx}" name="items[${thisRowIdx}][material_id]" class="material-tom-select w-full" required>
                    <option value="">-- Chọn / tìm ${isEdge ? 'mã chỉ dán CHI-...' : 'mã ván, tên màu...'} --</option>
                </select>
            </td>
            <td class="py-2 px-3 text-center text-neutral-600 unit-cell">${isEdge ? 'm' : 'Tấm'}</td>
            <td class="py-2 px-3 text-right text-neutral-500 stock-cell">-</td>
            <td class="py-2 px-3 text-right">
                <input type="number" name="items[${thisRowIdx}][quantity]" value="${qty}" step="0.01" min="0.01" class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-right text-xs font-bold text-emerald-700 focus:border-primary-500 qty-input" required oninput="calcTotals()">
            </td>
            <td class="py-2 px-3 text-right">
                <input type="number" name="items[${thisRowIdx}][unit_price]" value="${price}" step="1000" min="0" class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-right text-xs focus:border-primary-500 price-input" placeholder="0" oninput="calcTotals()">
            </td>
            <td class="py-2 px-3 text-right font-bold text-neutral-800 line-total-cell">0đ</td>
            <td class="py-2 px-3">
                <input type="text" name="items[${thisRowIdx}][notes]" value="${notes}" class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-xs" placeholder="Ghi chú...">
            </td>
            <td class="py-2 px-3 text-center">
                <button type="button" onclick="removeRow(${thisRowIdx})" class="text-danger-500 hover:text-danger-700 p-1 text-base leading-none" title="Xóa dòng">&times;</button>
            </td>
        `;

        tbody.appendChild(tr);
        rowCount++;

        initTomSelectForRow(thisRowIdx, selectedMaterialId);
        updateStt();
        calcTotals();
    }

    function initTomSelectForRow(rowIdx, selectedMaterialId) {
        const selectEl = document.getElementById(`select_mat_${rowIdx}`);
        if (!selectEl) return;

        const isEdge = isEdgeBandWarehouse();
        const options = getFilteredMaterialOptions();

        if (typeof TomSelect !== 'undefined') {
            const ts = new TomSelect(selectEl, {
                options: options,
                valueField: 'value',
                labelField: 'code',
                searchField: ['code', 'origin_code', 'name', 'category'],
                create: false,
                maxItems: 1,
                placeholder: isEdge ? '-- Tìm mã chỉ dán CHI-... --' : '-- Tìm kiếm mã ván, tên màu... --',
                allowEmptyOption: true,
                dropdownParent: 'body',
                wrapperClass: 'ts-wrapper material-ts-wrapper',
                dropdownClass: 'ts-dropdown material-ts-dropdown',
                render: {
                    option: function(data, escape) {
                        const originHtml = data.origin_code 
                            ? `<span class="inline-block bg-amber-50 text-amber-700 border border-amber-200 rounded px-1 text-[10px] font-bold ml-1">${escape(data.origin_code)}</span>`
                            : '';
                        const catHtml = data.category
                            ? `<span class="text-neutral-400 text-[11px]">&bull; ${escape(data.category)}</span>`
                            : '';
                        const stockFormatted = Number(data.stock || 0).toLocaleString('vi-VN');
                        return `
                            <div class="py-2 px-3 flex items-center justify-between border-b border-neutral-100 hover:bg-primary-50 transition-colors">
                                <div class="flex-1 min-w-0 pr-2">
                                    <div class="flex items-center">
                                        <span class="font-bold text-neutral-800 text-xs">${escape(data.code)}</span>
                                        ${originHtml}
                                    </div>
                                    <div class="text-[11px] text-neutral-600 truncate mt-1">
                                        ${escape(data.name)} ${catHtml}
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded px-2 py-1">
                                        Tồn: ${stockFormatted} ${escape(data.unit)}
                                    </span>
                                </div>
                            </div>
                        `;
                    },
                    item: function(data, escape) {
                        const originText = data.origin_code ? ` [${escape(data.origin_code)}]` : '';
                        return `<div class="truncate text-xs font-semibold text-neutral-800">
                            <span class="font-bold text-primary-700">${escape(data.code)}</span>${originText} - ${escape(data.name)}
                        </div>`;
                    }
                },
                onChange: function(val) {
                    onMaterialSelected(rowIdx, val);
                }
            });

            selectEl._tomSelect = ts;

            if (selectedMaterialId) {
                ts.setValue(String(selectedMaterialId), true);
                onMaterialSelected(rowIdx, selectedMaterialId);
            }
        }
    }

    function onMaterialSelected(rowIdx, materialId) {
        const tr = document.getElementById(`row_${rowIdx}`);
        if (!tr) return;

        const mat = MATERIALS.find(m => String(m.id) === String(materialId));
        if (!mat) {
            tr.querySelector('.unit-cell').textContent = 'Tấm';
            tr.querySelector('.stock-cell').textContent = '-';
            return;
        }

        const unit = mat.unit || 'Tấm';
        const price = mat.cost_price || 0;
        const stock = mat.current_stock || 0;

        tr.querySelector('.unit-cell').textContent = unit;
        tr.querySelector('.stock-cell').textContent = Number(stock).toLocaleString('vi-VN');

        const priceInput = tr.querySelector('.price-input');
        if (priceInput && (!priceInput.value || parseFloat(priceInput.value) === 0)) {
            priceInput.value = price;
        }
        calcTotals();
    }

    function removeRow(rowIdx) {
        const tr = document.getElementById(`row_${rowIdx}`);
        if (tr) {
            const selectEl = document.getElementById(`select_mat_${rowIdx}`);
            if (selectEl && selectEl._tomSelect) {
                selectEl._tomSelect.destroy();
            }
            tr.remove();
        }
        updateStt();
        calcTotals();
    }

    function updateStt() {
        const rows = document.querySelectorAll('#itemsTableBody tr');
        rows.forEach((r, idx) => {
            const sttCell = r.querySelector('.row-stt');
            if (sttCell) sttCell.textContent = idx + 1;
        });
    }

    function calcTotals() {
        let totalQty = 0;
        let totalAmount = 0;
        const rows = document.querySelectorAll('#itemsTableBody tr');

        rows.forEach(r => {
            const qty = parseFloat(r.querySelector('.qty-input')?.value || 0);
            const price = parseFloat(r.querySelector('.price-input')?.value || 0);
            const lineTotal = qty * price;

            const lineCell = r.querySelector('.line-total-cell');
            if (lineCell) {
                lineCell.textContent = lineTotal > 0 ? lineTotal.toLocaleString('vi-VN') + 'đ' : '0đ';
            }

            totalQty += qty;
            totalAmount += lineTotal;
        });

        document.getElementById('lblTotalQty').textContent = totalQty.toLocaleString('vi-VN');
        document.getElementById('lblTotalAmount').textContent = totalAmount.toLocaleString('vi-VN') + 'đ';
    }

    // Initialize rows
    function initDefaultRows() {
        const whEl = document.getElementById("select_warehouse");
        if (whEl) {
            whEl.addEventListener('change', onWarehouseChange);
        }
        onWarehouseChange();

        if (PREFILLED_ORDER_ITEMS && PREFILLED_ORDER_ITEMS.length > 0) {
            PREFILLED_ORDER_ITEMS.forEach(it => {
                addItemRow(it.material_id, it.quantity, it.unit_price, it.notes || '');
            });
        } else {
            addItemRow();
            addItemRow();
            addItemRow();
        }

        const supSelect = document.getElementById('select_supplier');
        if (supSelect && supSelect.value) {
            onSupplierChanged(supSelect);
        }
    }

    // Order items loader
    function onOrderChanged(selectEl) {
        const btn = document.getElementById('btnLoadOrderItems');
        if (btn) {
            if (selectEl.value) {
                btn.classList.remove('hidden');
            } else {
                btn.classList.add('hidden');
            }
        }
    }

    function loadSelectedOrderSupplies() {
        const orderId = document.getElementById('select_order')?.value;
        if (!orderId) {
            alert('Vui lòng chọn đơn hàng trước.');
            return;
        }

        const btn = document.getElementById('btnLoadOrderItems');
        if (btn) btn.innerHTML = 'Đang tải...';

        fetch(`/warehouses/orders/${orderId}/supplies`)
            .then(res => res.json())
            .then(data => {
                if (btn) btn.innerHTML = '<iconify-icon icon="solar:download-square-bold"></iconify-icon> Nạp vật tư đơn';
                if (data.success && data.items && data.items.length > 0) {
                    const tbody = document.getElementById('itemsTableBody');
                    if (tbody && tbody.children.length > 0) {
                        const hasFilled = Array.from(tbody.querySelectorAll('.qty-input')).some(i => parseFloat(i.value) > 0);
                        if (hasFilled && !confirm('Bạn có muốn xóa các dòng hiện tại để nạp lại vật tư từ đơn hàng ' + data.order_code + '?')) {
                            data.items.forEach(it => {
                                addItemRow(it.material_id, it.quantity, it.unit_price, it.notes || '');
                            });
                            return;
                        }
                    }

                    if (tbody) tbody.innerHTML = '';
                    data.items.forEach(it => {
                        addItemRow(it.material_id, it.quantity, it.unit_price, it.notes || '');
                    });
                } else {
                    alert('Đơn hàng ' + (data.order_code || '') + ' không có vật tư phù hợp để nạp.');
                }
            })
            .catch(err => {
                if (btn) btn.innerHTML = '<iconify-icon icon="solar:download-square-bold"></iconify-icon> Nạp vật tư đơn';
                alert('Không thể tải vật tư từ đơn hàng.');
            });
    }

    // Supplier functions
    function onSupplierChanged(selectEl) {
        const selectedOpt = selectEl.options[selectEl.selectedIndex];
        const inputName = document.getElementById('input_supplier_name');
        const badge = document.getElementById('supplier_debt_badge');
        const debtVal = document.getElementById('supplier_debt_val');

        if (!selectedOpt || !selectedOpt.value) {
            if (badge) badge.classList.add('hidden');
            return;
        }

        if (inputName) {
            inputName.value = selectedOpt.getAttribute('data-name') || '';
        }

        const debt = parseFloat(selectedOpt.getAttribute('data-debt') || 0);
        if (badge && debtVal) {
            debtVal.textContent = Number(debt).toLocaleString('vi-VN');
            if (debt > 0) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    }

    function openQuickSupplierModal() {
        document.getElementById('quickSupplierModal').classList.remove('hidden');
        document.getElementById('quick_sup_name').focus();
    }

    function closeQuickSupplierModal() {
        document.getElementById('quickSupplierModal').classList.add('hidden');
    }

    function handleQuickSupplierSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitQuickSupplier');
        btn.disabled = true;
        btn.innerHTML = 'Đang lưu...';

        const payload = {
            name: document.getElementById('quick_sup_name').value,
            code: document.getElementById('quick_sup_code').value,
            phone: document.getElementById('quick_sup_phone').value,
            address: document.getElementById('quick_sup_address').value,
            initial_debt: document.getElementById('quick_sup_initial_debt').value,
            _token: '{{ csrf_token() }}'
        };

        fetch('{{ route("suppliers.quick-store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="solar:check-circle-bold" class="text-sm"></iconify-icon> Lưu NCC';

            if (data.success && data.supplier) {
                const s = data.supplier;
                const selectEl = document.getElementById('select_supplier');
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = `[${s.code}] ${s.name}`;
                opt.setAttribute('data-name', s.name);
                opt.setAttribute('data-debt', s.debt);
                selectEl.appendChild(opt);
                selectEl.value = s.id;
                onSupplierChanged(selectEl);
                closeQuickSupplierModal();
                document.getElementById('quickSupplierForm').reset();
            } else {
                alert(data.message || 'Có lỗi xảy ra khi tạo nhà cung cấp.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="solar:check-circle-bold" class="text-sm"></iconify-icon> Lưu NCC';
            alert('Không thể kết nối đến máy chủ.');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDefaultRows);
    } else {
        initDefaultRows();
    }
</script>
@endpush
