@extends('layout.layout')
@php
    $title    = 'Xuất kho';
    $subTitle = 'Tạo phiếu xuất mới';
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
            <div class="p-4 border-b border-neutral-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('inventory.issues.index') }}" class="w-8 h-8 rounded-lg bg-neutral-100 hover:bg-neutral-200 flex items-center justify-center text-neutral-600 transition-colors">
                        <iconify-icon icon="solar:arrow-left-outline" class="text-lg"></iconify-icon>
                    </a>
                    <div>
                        <h5 class="font-bold text-base text-neutral-800 mb-0">Tạo Phiếu Xuất kho (PXK)</h5>
                        <p class="text-xs text-neutral-500 mb-0">Xuất nguyên vật liệu cho xưởng sản xuất hoặc giao hàng.</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('inventory.issues.store') }}" method="POST">
                @csrf
                <div class="p-6">
                    {{-- Master Info Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 bg-neutral-50 p-4 rounded-xl border border-neutral-200">
                        <div>
                            <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Mã phiếu xuất <span class="text-danger-500">*</span></label>
                            <input type="text" name="code" value="{{ $nextCode }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs font-bold text-rose-700 focus:border-rose-500" required>
                        </div>
                        <div>
                            <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Ngày xuất <span class="text-danger-500">*</span></label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-rose-500" required>
                        </div>
                        <div>
                            <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Kho xuất <span class="text-danger-500">*</span></label>
                            <select name="warehouse_id" id="select_warehouse" class="form-select rounded-lg w-full border border-neutral-300 px-3 py-2 text-xs font-semibold text-neutral-800 bg-white focus:border-rose-500" required>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ (string)old('warehouse_id', request('warehouse_id', $selectedWarehouseId ?? '')) === (string)$wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Người / Bộ phận nhận <span class="text-danger-500">*</span></label>
                            <input type="text" name="recipient" value="Xưởng sản xuất Gervin" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-rose-500" required>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="form-label text-xs font-bold text-neutral-700 block mb-0">Đơn hàng liên quan <span class="text-neutral-400 font-normal">(Tùy chọn)</span></label>
                                <button type="button" id="btnLoadOrderItems" onclick="loadSelectedOrderSupplies()" class="text-[11px] text-rose-600 hover:text-rose-800 font-bold flex items-center gap-0.5 {{ empty($selectedOrder) ? 'hidden' : '' }}" title="Nạp danh sách vật tư từ đơn hàng này vào bảng bên dưới">
                                    <iconify-icon icon="solar:download-square-bold"></iconify-icon> Nạp vật tư đơn
                                </button>
                            </div>
                            <select name="order_id" id="select_order" class="form-select rounded-lg w-full border border-neutral-300 px-3 py-2 text-xs font-semibold text-neutral-800 bg-white focus:border-rose-500" onchange="onOrderChanged(this)">
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
                            <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Lý do xuất</label>
                            <select name="reason" class="form-select rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-rose-500">
                                <option value="Xuất sản xuất">Xuất sản xuất xưởng</option>
                                <option value="Xuất bán hàng">Xuất bán vật tư</option>
                                <option value="Xuất chuyển kho">Xuất chuyển kho</option>
                                <option value="Xuất bảo hành">Xuất bảo hành / sửa chữa</option>
                                <option value="Xuất hủy">Xuất hủy hỏng</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Người xuất (Thủ kho)</label>
                            <input type="text" name="deliverer" value="{{ auth()->user()->name ?? 'Thủ kho' }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-rose-500">
                        </div>
                        <div>
                            <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Ghi chú phiếu xuất</label>
                            <input type="text" name="notes" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-rose-500" placeholder="Xuất theo lệnh sản xuất số, đơn hàng..." value="{{ !empty($selectedOrder) ? 'Xuất kho cho đơn hàng #' . $selectedOrder->order_code : '' }}">
                        </div>
                    </div>

                    {{-- Items Details Table --}}
                    <div class="border border-neutral-200 rounded-xl overflow-hidden mb-6">
                        <div class="p-3 bg-neutral-100 border-b border-neutral-200 flex items-center justify-between">
                            <h6 class="font-bold text-neutral-800 text-xs uppercase mb-0">Danh sách vật tư xuất kho</h6>
                            <button type="button" onclick="addItemRow()" class="btn btn-sm btn-primary px-3 py-1 rounded-lg text-xs font-bold flex items-center gap-1 shadow-sm">
                                <iconify-icon icon="ic:baseline-plus" class="text-sm"></iconify-icon> Thêm dòng
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left border-collapse" id="itemsTable">
                                <thead class="bg-neutral-50 text-neutral-700 font-bold border-b border-neutral-200">
                                    <tr>
                                        <th class="py-2 px-3 w-12 text-center">STT</th>
                                        <th class="py-2 px-3 min-w-[280px]" id="thMaterialCol">Mặt hàng vật tư (Mã ván / Chỉ dán) <span class="text-danger-500">*</span></th>
                                        <th class="py-2 px-3 w-20 text-center">ĐVT</th>
                                        <th class="py-2 px-3 w-24 text-center">Cao (mm)</th>
                                        <th class="py-2 px-3 w-24 text-center">Rộng (mm)</th>
                                        <th class="py-2 px-3 w-28 text-right bg-amber-50 text-amber-900">Tồn hiện tại</th>
                                        <th class="py-2 px-3 w-32 text-right">Số lượng xuất <span class="text-danger-500">*</span></th>
                                        <th class="py-2 px-3 min-w-[160px]">Ghi chú dòng</th>
                                        <th class="py-2 px-3 w-12 text-center"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody" class="divide-y divide-neutral-100 bg-white">
                                    {{-- Row template populated by JS --}}
                                </tbody>
                                <tfoot class="bg-neutral-50 font-bold border-t-2 border-neutral-300 text-xs">
                                    <tr>
                                        <td colspan="6" class="py-3 px-3 text-right text-neutral-700 uppercase">TỔNG CỘNG XUẤT:</td>
                                        <td class="py-3 px-3 text-right text-rose-700 font-black text-sm" id="lblTotalQty">0</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Actions Bar --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200">
                        <a href="{{ route('inventory.issues.index') }}" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-5 py-2 rounded-lg text-xs font-semibold">
                            Hủy bỏ
                        </a>
                        <button type="submit" class="btn btn-primary px-6 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm">
                            <iconify-icon icon="solar:diskette-bold" class="text-base"></iconify-icon> Xác nhận Xuất kho
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .issue-ts-wrapper {
        width: 100% !important;
    }
    .issue-ts-wrapper .ts-control {
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
    .issue-ts-wrapper.focus .ts-control {
        border-color: #e11d48 !important;
        box-shadow: 0 0 0 2px rgba(225, 29, 72, 0.15) !important;
    }
    .issue-ts-dropdown {
        font-size: 12px !important;
        border-radius: 0.5rem !important;
        border: 1px solid #e5e7eb !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08) !important;
        z-index: 99999 !important;
        max-height: 320px !important;
        overflow-y: auto !important;
        background-color: #ffffff !important;
    }
    .issue-ts-dropdown .ts-dropdown-content {
        max-height: 320px !important;
    }
    .issue-ts-dropdown .active {
        background-color: #fff1f2 !important;
        color: #be123c !important;
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

        const orderSelect = document.getElementById('select_order');
        if (orderSelect && orderSelect.value) {
            loadSelectedOrderSupplies(true);
            return;
        }

        const filtered = getFilteredMaterialOptions();
        const rows = document.querySelectorAll('#itemsTableBody tr');
        rows.forEach(r => {
            const selectEl = r.querySelector('.material-tom-select');
            if (selectEl && selectEl._tomSelect) {
                const ts = selectEl._tomSelect;
                const curVal = ts.getValue();
                const curOpt = curVal && ts.options ? ts.options[curVal] : null;
                ts.clear();
                ts.clearOptions();
                ts.addOptions(filtered);
                if (curVal && curOpt && !filtered.some(opt => opt.value === curVal)) {
                    ts.addOption(curOpt);
                }
                ts.refreshOptions(false);
                if (curVal) {
                    ts.setValue(curVal, true);
                    onMaterialSelected(r.id.replace('row_', ''), curVal);
                } else {
                    r.querySelector('.unit-cell').textContent = isEdge ? 'm' : 'Tấm';
                    r.querySelector('.stock-cell').textContent = '-';
                    r.removeAttribute('data-max-stock');
                }
            }
        });
        calcTotals();
    }

    let rowCount = 0;

    function addItemRow(selectedMaterialId = '', qty = '', notes = '', height = '', width = '') {
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
                    <option value="">-- Chọn / tìm ${isEdge ? 'mã chỉ dán xuất kho...' : 'mã ván xuất kho...'} --</option>
                </select>
            </td>
            <td class="py-2 px-3 text-center text-neutral-600 unit-cell">${isEdge ? 'm' : 'Tấm'}</td>
            <td class="py-2 px-3 text-center">
                <input type="number" name="items[${thisRowIdx}][height]" value="${height !== '' ? height : (isEdge ? '' : '2440')}" step="1" min="0" class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-center text-xs focus:border-rose-500 height-input" placeholder="${isEdge ? '—' : '2440'}" ${isEdge ? 'disabled' : ''}>
            </td>
            <td class="py-2 px-3 text-center">
                <input type="number" name="items[${thisRowIdx}][width]" value="${width !== '' ? width : (isEdge ? '' : '1220')}" step="1" min="0" class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-center text-xs focus:border-rose-500 width-input" placeholder="${isEdge ? '—' : '1220'}" ${isEdge ? 'disabled' : ''}>
            </td>
            <td class="py-2 px-3 text-right font-bold text-amber-900 bg-amber-50 bg-opacity-40 stock-cell">-</td>
            <td class="py-2 px-3 text-right">
                <input type="number" name="items[${thisRowIdx}][quantity]" value="${qty}" step="0.01" min="0.01" class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-right text-xs font-bold text-rose-700 focus:border-rose-500 qty-input" required oninput="calcTotals()">
                <div class="text-[10px] text-danger-600 stock-warn hidden mt-1 font-semibold">Vượt số lượng tồn!</div>
            </td>
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
                placeholder: isEdge ? '-- Tìm kiếm mã chỉ dán CHI-... --' : '-- Tìm kiếm mã ván, tên màu... --',
                allowEmptyOption: true,
                dropdownParent: 'body',
                wrapperClass: 'ts-wrapper issue-ts-wrapper',
                dropdownClass: 'ts-dropdown issue-ts-dropdown',
                render: {
                    option: function(data, escape) {
                        const originHtml = data.origin_code 
                            ? `<span class="inline-block bg-amber-50 text-amber-700 border border-amber-200 rounded px-1 text-[10px] font-bold ml-1">${escape(data.origin_code)}</span>`
                            : '';
                        const catHtml = data.category
                            ? `<span class="text-neutral-400 text-[11px]">&bull; ${escape(data.category)}</span>`
                            : '';
                        const stockFormatted = Number(data.stock || 0).toLocaleString('vi-VN');
                        const isOutOfStock = Number(data.stock || 0) <= 0;
                        const stockBadge = isOutOfStock
                            ? `<span class="text-[11px] font-bold text-rose-700 bg-rose-50 border border-rose-200 rounded px-2 py-1">Hết hàng (0 ${escape(data.unit)})</span>`
                            : `<span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded px-2 py-1">Tồn: ${stockFormatted} ${escape(data.unit)}</span>`;

                        return `
                            <div class="py-2 px-3 flex items-center justify-between border-b border-neutral-100 hover:bg-rose-50 transition-colors">
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
                                    ${stockBadge}
                                </div>
                            </div>
                        `;
                    },
                    item: function(data, escape) {
                        const originText = data.origin_code ? ` [${escape(data.origin_code)}]` : '';
                        return `<div class="truncate text-xs font-semibold text-neutral-800">
                            <span class="font-bold text-rose-700">${escape(data.code)}</span>${originText} - ${escape(data.name)}
                        </div>`;
                    }
                },
                onChange: function(val) {
                    onMaterialSelected(rowIdx, val);
                }
            });

            selectEl._tomSelect = ts;

            if (selectedMaterialId) {
                const strId = String(selectedMaterialId);
                if (!ts.options[strId]) {
                    const foundMat = MATERIALS.find(m => String(m.id) === strId);
                    if (foundMat) {
                        ts.addOption({
                            value: String(foundMat.id),
                            code: foundMat.code || '',
                            name: foundMat.name || '',
                            origin_code: foundMat.origin_code || '',
                            category: foundMat.category || '',
                            unit: foundMat.unit || (foundMat.item_type === 'edge_band' ? 'm' : 'Tấm'),
                            stock: foundMat.current_stock || 0,
                            item_type: foundMat.item_type
                        });
                    }
                }
                ts.setValue(strId, true);
                onMaterialSelected(rowIdx, strId);
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
            tr.removeAttribute('data-max-stock');
            return;
        }

        const unit = mat.unit || 'Tấm';
        const stock = parseFloat(mat.current_stock || 0);

        tr.querySelector('.unit-cell').textContent = unit;
        tr.querySelector('.stock-cell').textContent = stock.toLocaleString('vi-VN');
        tr.setAttribute('data-max-stock', stock);

        const heightInput = tr.querySelector('.height-input');
        const widthInput = tr.querySelector('.width-input');
        const isMatEdge = mat.item_type === 'edge_band' || (mat.code && String(mat.code).startsWith('CHI-'));
        if (isMatEdge) {
            if (heightInput) { heightInput.value = ''; heightInput.disabled = true; heightInput.placeholder = '—'; }
            if (widthInput) { widthInput.value = ''; widthInput.disabled = true; widthInput.placeholder = '—'; }
        } else {
            if (heightInput) {
                heightInput.disabled = false;
                heightInput.placeholder = '2440';
                if (!heightInput.value) heightInput.value = '2440';
            }
            if (widthInput) {
                widthInput.disabled = false;
                widthInput.placeholder = '1220';
                if (!widthInput.value) widthInput.value = '1220';
            }
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
        const rows = document.querySelectorAll('#itemsTableBody tr');

        rows.forEach(r => {
            const qtyInput = r.querySelector('.qty-input');
            const qty = parseFloat(qtyInput?.value || 0);
            const maxStock = parseFloat(r.getAttribute('data-max-stock') || 999999);
            const warnElem = r.querySelector('.stock-warn');

            if (warnElem) {
                if (qty > maxStock && maxStock >= 0) {
                    warnElem.classList.remove('hidden');
                } else {
                    warnElem.classList.add('hidden');
                }
            }

            totalQty += qty;
        });

        document.getElementById('lblTotalQty').textContent = totalQty.toLocaleString('vi-VN');
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
                addItemRow(it.material_id, it.quantity, it.notes || '', it.height || '', it.width || '');
            });
        } else {
            addItemRow();
            addItemRow();
            addItemRow();
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

    function loadSelectedOrderSupplies(forceClean = false) {
        const orderId = document.getElementById('select_order')?.value;
        if (!orderId) {
            if (!forceClean) alert('Vui lòng chọn đơn hàng trước.');
            return;
        }

        const btn = document.getElementById('btnLoadOrderItems');
        if (btn) btn.innerHTML = 'Đang tải...';

        const whId = document.getElementById('select_warehouse')?.value || '';
        fetch(`/warehouses/orders/${orderId}/supplies?type=issue&warehouse_id=${whId}`)
            .then(res => res.json())
            .then(data => {
                if (btn) btn.innerHTML = '<iconify-icon icon="solar:download-square-bold"></iconify-icon> Nạp vật tư đơn';
                if (data.success && data.items && data.items.length > 0) {
                    const tbody = document.getElementById('itemsTableBody');
                    if (!forceClean && tbody && tbody.children.length > 0) {
                        const hasFilled = Array.from(tbody.querySelectorAll('.qty-input')).some(i => parseFloat(i.value) > 0);
                        if (hasFilled && !confirm('Bạn có muốn xóa các dòng hiện tại để nạp lại vật tư từ đơn hàng ' + data.order_code + '?')) {
                            data.items.forEach(it => {
                                addItemRow(it.material_id, it.quantity, it.notes || '', it.height || '', it.width || '');
                            });
                            return;
                        }
                    }

                    if (tbody) tbody.innerHTML = '';
                    data.items.forEach(it => {
                        addItemRow(it.material_id, it.quantity, it.notes || '', it.height || '', it.width || '');
                    });
                } else {
                    if (!forceClean) alert('Đơn hàng ' + (data.order_code || '') + ' không có vật tư phù hợp để xuất kho.');
                }
            })
            .catch(err => {
                if (btn) btn.innerHTML = '<iconify-icon icon="solar:download-square-bold"></iconify-icon> Nạp vật tư đơn';
                if (!forceClean) alert('Không thể tải vật tư từ đơn hàng.');
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDefaultRows);
    } else {
        initDefaultRows();
    }
</script>
@endpush
