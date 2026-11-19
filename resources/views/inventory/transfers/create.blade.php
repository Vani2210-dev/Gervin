@extends('layout.layout')
@php
    $title    = 'Chuyển kho nội bộ';
    $subTitle = 'Tạo phiếu chuyển kho';
@endphp

@section('style')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .transfer-ts-wrapper {
        width: 100% !important;
        max-width: 320px !important;
    }
    .transfer-ts-wrapper .ts-control {
        border-radius: 0.5rem !important;
        border-color: #e5e7eb !important;
        font-size: 12px !important;
        min-height: 38px !important;
        padding: 6px 10px !important;
        max-width: 100% !important;
        overflow: hidden !important;
        white-space: nowrap !important;
        display: flex !important;
        align-items: center !important;
    }
    .transfer-ts-wrapper .ts-control > .item {
        max-width: calc(100% - 20px) !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
        display: block !important;
    }
    .transfer-ts-wrapper.focus .ts-control {
        border-color: #9333ea !important;
        box-shadow: 0 0 0 1px #9333ea !important;
    }
    .transfer-ts-dropdown {
        font-size: 12px !important;
        z-index: 9999 !important;
        max-height: 320px !important;
        min-width: 480px !important;
    }
    .transfer-ts-dropdown .active {
        background-color: #faf5ff !important;
        color: #7e22ce !important;
    }
    select.tomselected,
    select.material-tom-select.tomselected {
        display: none !important;
    }
</style>
@endsection

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        <form action="{{ route('inventory.transfers.store') }}" method="POST" id="transferForm">
            @csrf

            <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
                {{-- Form Header --}}
                <div class="p-4 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('inventory.transfers.index') }}" class="w-8 h-8 rounded-lg bg-neutral-100 hover:bg-neutral-200 flex items-center justify-center text-neutral-600 transition-colors">
                            <iconify-icon icon="solar:arrow-left-outline" class="text-lg"></iconify-icon>
                        </a>
                        <div>
                            <h5 class="font-bold text-base text-neutral-800 mb-0">Tạo Phiếu Chuyển Kho Nội Bộ (PCK)</h5>
                            <p class="text-xs text-neutral-500 mb-0">Điều chuyển hàng hóa, ván gỗ, chỉ dán, phụ kiện giữa các kho trong nhà máy Gervin.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('inventory.transfers.index') }}" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-4 py-2 rounded-lg text-xs font-semibold">
                            Hủy bỏ
                        </a>
                        <button type="submit" class="btn bg-purple-600 hover:bg-purple-700 text-white px-5 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm transition-all">
                            <iconify-icon icon="solar:disk-bold" class="text-base"></iconify-icon>
                            <span>Hoàn tất & Chuyển kho</span>
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    {{-- Thông tin chung phiếu chuyển --}}
                    <div class="p-4 bg-purple-50/40 rounded-xl border border-purple-100 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            {{-- Mã phiếu --}}
                            <div class="md:col-span-3">
                                <label class="form-label text-xs font-bold text-neutral-700 mb-1">Mã phiếu chuyển <span class="text-danger-500">*</span></label>
                                <input type="text" name="code" value="{{ old('code', $nextCode) }}" class="form-control rounded-lg border-neutral-300 text-xs font-bold text-purple-700 uppercase" required>
                                @error('code') <span class="text-danger-500 text-[11px] font-semibold">{{ $message }}</span> @enderror
                            </div>

                            {{-- Ngày chuyển --}}
                            <div class="md:col-span-3">
                                <label class="form-label text-xs font-bold text-neutral-700 mb-1">Ngày lập phiếu <span class="text-danger-500">*</span></label>
                                <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="form-control rounded-lg border-neutral-300 text-xs" required>
                                @error('date') <span class="text-danger-500 text-[11px] font-semibold">{{ $message }}</span> @enderror
                            </div>

                            {{-- Kho xuất (Nguồn) --}}
                            <div class="md:col-span-3">
                                <label class="form-label text-xs font-bold text-neutral-700 mb-1 flex items-center gap-1 text-rose-700">
                                    <iconify-icon icon="solar:export-bold" class="text-sm"></iconify-icon>
                                    Kho xuất (Nguồn) <span class="text-danger-500">*</span>
                                </label>
                                <select name="from_warehouse_id" id="from_warehouse_id" onchange="onFromWarehouseChanged()" class="form-select rounded-lg border-neutral-300 text-xs font-semibold text-neutral-800" required>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ (string)old('from_warehouse_id', $fromWarehouseId) === (string)$wh->id ? 'selected' : '' }}>
                                            {{ $wh->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('from_warehouse_id') <span class="text-danger-500 text-[11px] font-semibold">{{ $message }}</span> @enderror
                            </div>

                            {{-- Kho nhận (Đích) --}}
                            <div class="md:col-span-3">
                                <label class="form-label text-xs font-bold text-neutral-700 mb-1 flex items-center gap-1 text-emerald-700">
                                    <iconify-icon icon="solar:import-bold" class="text-sm"></iconify-icon>
                                    Kho nhận (Đích) <span class="text-danger-500">*</span>
                                </label>
                                <select name="to_warehouse_id" id="to_warehouse_id" onchange="validateWarehouses()" class="form-select rounded-lg border-neutral-300 text-xs font-semibold text-neutral-800" required>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ (string)old('to_warehouse_id', $toWarehouseId) === (string)$wh->id ? 'selected' : '' }}>
                                            {{ $wh->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="whDiffError" class="text-danger-600 text-[11px] font-bold hidden mt-1">Kho nhận phải khác kho xuất!</div>
                                @error('to_warehouse_id') <span class="text-danger-500 text-[11px] font-semibold">{{ $message }}</span> @enderror
                            </div>

                            {{-- Người giao / vận chuyển --}}
                            <div class="md:col-span-3">
                                <label class="form-label text-xs font-bold text-neutral-700 mb-1">Người lập / giao hàng</label>
                                <input type="text" name="deliverer" value="{{ old('deliverer', auth()->user()->name ?? '') }}" class="form-control rounded-lg border-neutral-300 text-xs" placeholder="Tên người giao / vận chuyển...">
                            </div>

                            {{-- Người nhận kho đích --}}
                            <div class="md:col-span-3">
                                <label class="form-label text-xs font-bold text-neutral-700 mb-1">Người nhận (Kho đích)</label>
                                <input type="text" name="receiver" value="{{ old('receiver') }}" class="form-control rounded-lg border-neutral-300 text-xs" placeholder="Tên thủ kho / người nhận...">
                            </div>

                            {{-- Ghi chú lý do chuyển kho --}}
                            <div class="md:col-span-6">
                                <label class="form-label text-xs font-bold text-neutral-700 mb-1">Lý do điều chuyển / Ghi chú</label>
                                <input type="text" name="notes" value="{{ old('notes') }}" class="form-control rounded-lg border-neutral-300 text-xs" placeholder="Ví dụ: Điều chuyển phôi ván sang xưởng ép tấm theo tiến độ...">
                            </div>
                        </div>
                    </div>

                    {{-- Danh sách mặt hàng chuyển kho --}}
                    <div class="card p-0 rounded-xl border border-neutral-200 overflow-hidden mb-6">
                        <div class="p-3.5 bg-neutral-50 border-b border-neutral-200 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <iconify-icon icon="solar:box-minimalistic-bold" class="text-purple-600 text-base"></iconify-icon>
                                <span class="font-bold text-xs uppercase text-neutral-700">Danh sách vật tư điều chuyển</span>
                                <span class="text-[11px] text-neutral-500 font-normal">(Chọn mặt hàng có trong kho xuất)</span>
                            </div>
                            <button type="button" onclick="addItemRow()" class="btn btn-xs bg-purple-600 hover:bg-purple-700 text-white rounded-lg px-3 py-1.5 text-xs font-bold flex items-center gap-1 shadow-xs transition-all">
                                <iconify-icon icon="ic:baseline-plus" class="text-sm"></iconify-icon>
                                <span>Thêm dòng vật tư</span>
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left border-collapse" id="itemsTable">
                                <thead>
                                    <tr class="bg-neutral-100 text-neutral-700 font-bold border-b border-neutral-200">
                                        <th class="py-2.5 px-3 w-10 text-center">STT</th>
                                        <th class="py-2.5 px-3 w-[300px] max-w-[320px]">Mặt hàng vật tư <span class="text-danger-500">*</span></th>
                                        <th class="py-2.5 px-3 w-16 text-center">ĐVT</th>
                                        <th class="py-2.5 px-3 w-20 text-center">Dài (mm)</th>
                                        <th class="py-2.5 px-3 w-20 text-center">Rộng (mm)</th>
                                        <th class="py-2.5 px-3 w-28 text-right">Tồn kho nguồn</th>
                                        <th class="py-2.5 px-3 w-32 text-right">Số lượng chuyển <span class="text-danger-500">*</span></th>
                                        <th class="py-2.5 px-3 min-w-[140px]">Ghi chú mặt hàng</th>
                                        <th class="py-2.5 px-3 w-10 text-center">Xóa</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody" class="divide-y divide-neutral-100 bg-white">
                                    {{-- Rows inserted dynamically by JS --}}
                                </tbody>
                                <tfoot>
                                    <tr class="bg-purple-50 font-bold text-purple-900 border-t border-purple-200">
                                        <td colspan="6" class="py-3 px-4 text-right uppercase text-xs">
                                            Tổng số lượng điều chuyển:
                                        </td>
                                        <td class="py-3 px-3 text-right text-sm text-purple-700" id="totalQtyDisplay">
                                            0
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Submit buttons --}}
                    <div class="flex items-center justify-between pt-2">
                        <button type="button" onclick="addItemRow()" class="btn border border-dashed border-purple-300 text-purple-700 hover:bg-purple-50 px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-1.5 transition-all">
                            <iconify-icon icon="ic:baseline-plus" class="text-base"></iconify-icon>
                            <span>Thêm dòng vật tư khác</span>
                        </button>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('inventory.transfers.index') }}" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-4 py-2 rounded-lg text-xs font-semibold">
                                Hủy bỏ
                            </a>
                            <button type="submit" class="btn bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm transition-all">
                                <iconify-icon icon="solar:disk-bold" class="text-base"></iconify-icon>
                                <span>Hoàn tất & Chuyển kho</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    const WAREHOUSES = @json($warehouses);
    let ALL_MATERIALS = @json($materials);
    let rowCount = 0;

    function validateWarehouses() {
        const fromWh = document.getElementById('from_warehouse_id').value;
        const toWh = document.getElementById('to_warehouse_id').value;
        const errorEl = document.getElementById('whDiffError');

        if (fromWh && toWh && fromWh === toWh) {
            errorEl.classList.remove('hidden');
            return false;
        } else {
            errorEl.classList.add('hidden');
            return true;
        }
    }

    function onFromWarehouseChanged() {
        validateWarehouses();
        const fromWhId = document.getElementById('from_warehouse_id').value;

        // Tải danh sách vật tư kèm tồn kho thực tế của kho nguồn được chọn
        fetch(`/warehouses/${fromWhId}/materials-json`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.materials) {
                    ALL_MATERIALS = data.materials;
                }
                refreshAllRowsMaterialOptions();
            })
            .catch(() => {
                refreshAllRowsMaterialOptions();
            });
    }

    function getMaterialOptions() {
        const fromWhId = document.getElementById('from_warehouse_id')?.value;
        return ALL_MATERIALS.map(m => {
            const isMatchWh = !fromWhId || String(m.warehouse_id) === String(fromWhId);
            return {
                value: String(m.id),
                code: m.code || '',
                name: m.name || '',
                origin_code: m.origin_code || '',
                category: m.category || '',
                warehouse_name: m.warehouse_name || '',
                warehouse_id: m.warehouse_id,
                unit: m.unit || (m.item_type === 'edge_band' ? 'm' : 'Tấm'),
                stock: isMatchWh ? (m.current_stock || 0) : 0,
                item_type: m.item_type,
                cost_price: m.cost_price || 0
            };
        });
    }

    function refreshAllRowsMaterialOptions() {
        const options = getMaterialOptions();
        const rows = document.querySelectorAll('#itemsTableBody tr');
        rows.forEach(r => {
            const selectEl = r.querySelector('.material-tom-select');
            if (selectEl && selectEl._tomSelect) {
                const ts = selectEl._tomSelect;
                const curVal = ts.getValue();
                ts.clear();
                ts.clearOptions();
                ts.addOptions(options);
                ts.refreshOptions(false);
                if (curVal) {
                    ts.setValue(curVal, true);
                    onMaterialSelected(r.id.replace('row_', ''), curVal);
                }
            }
        });
        calcTotals();
    }

    function addItemRow(selectedMaterialId = '', qty = '', notes = '', height = '', width = '') {
        const tbody = document.getElementById('itemsTableBody');
        const thisRowIdx = rowCount;
        const tr = document.createElement('tr');
        tr.id = `row_${thisRowIdx}`;
        tr.className = 'hover:bg-purple-50/20 transition-colors';

        tr.innerHTML = `
            <td class="py-2 px-3 text-center text-neutral-400 font-medium row-stt">${tbody.children.length + 1}</td>
            <td class="py-2 px-3 w-[300px] max-w-[320px]">
                <select id="select_mat_${thisRowIdx}" name="items[${thisRowIdx}][material_id]" class="material-tom-select w-full" required>
                    <option value="">-- Tìm kiếm mã ván, tên màu, vật tư cần chuyển... --</option>
                </select>
            </td>
            <td class="py-2 px-3 text-center text-neutral-600 unit-cell">Tấm</td>
            <td class="py-2 px-3 text-center">
                <input type="number" name="items[${thisRowIdx}][height]" value="${height !== '' && height !== null ? height : '2440'}" step="any" min="0" class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-center text-xs focus:border-purple-500 height-input" placeholder="2440">
            </td>
            <td class="py-2 px-3 text-center">
                <input type="number" name="items[${thisRowIdx}][width]" value="${width !== '' && width !== null ? width : '1220'}" step="any" min="0" class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-center text-xs focus:border-purple-500 width-input" placeholder="1220">
            </td>
            <td class="py-2 px-3 text-right font-bold text-neutral-700 bg-neutral-50 stock-cell">-</td>
            <td class="py-2 px-3 text-right">
                <input type="number" name="items[${thisRowIdx}][quantity]" value="${qty}" step="any" min="0.001" class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-right text-xs font-bold text-purple-700 focus:border-purple-500 qty-input" required oninput="calcTotals()">
                <div class="text-[10px] text-danger-600 stock-warn hidden mt-1 font-semibold">Vượt số lượng tồn!</div>
            </td>
            <td class="py-2 px-3">
                <input type="text" name="items[${thisRowIdx}][notes]" value="${notes}" class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-xs" placeholder="Ghi chú mặt hàng...">
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

        const options = getMaterialOptions();

        if (typeof TomSelect !== 'undefined') {
            const ts = new TomSelect(selectEl, {
                options: options,
                valueField: 'value',
                labelField: 'code',
                searchField: ['code', 'origin_code', 'name', 'category', 'warehouse_name'],
                create: false,
                maxItems: 1,
                maxOptions: 5000,
                placeholder: '-- Tìm kiếm mã ván, tên màu, vật tư cần chuyển... --',
                allowEmptyOption: true,
                dropdownParent: 'body',
                wrapperClass: 'ts-wrapper transfer-ts-wrapper',
                dropdownClass: 'ts-dropdown transfer-ts-dropdown',
                render: {
                    option: function(data, escape) {
                        const originHtml = data.origin_code 
                            ? `<span class="inline-block bg-amber-50 text-amber-700 border border-amber-200 rounded px-1 text-[10px] font-bold ml-1">${escape(data.origin_code)}</span>`
                            : '';
                        const whHtml = data.warehouse_name 
                            ? `<span class="inline-block bg-neutral-100 text-neutral-600 rounded px-1.5 py-0.5 text-[10px] font-medium ml-1.5">${escape(data.warehouse_name)}</span>`
                            : '';
                        const catHtml = data.category
                            ? `<span class="text-neutral-400 text-[11px]">&bull; ${escape(data.category)}</span>`
                            : '';
                        const stockFormatted = Number(data.stock || 0).toLocaleString('vi-VN');
                        const isOutOfStock = Number(data.stock || 0) <= 0;
                        const stockBadge = isOutOfStock
                            ? `<span class="text-[11px] font-bold text-neutral-500 bg-neutral-100 rounded px-2 py-0.5">Tồn: 0 ${escape(data.unit)}</span>`
                            : `<span class="text-[11px] font-bold text-purple-700 bg-purple-50 border border-purple-200 rounded px-2 py-0.5">Tồn: ${stockFormatted} ${escape(data.unit)}</span>`;

                        return `
                            <div class="py-2 px-3 flex items-center justify-between border-b border-neutral-100 hover:bg-purple-50 transition-colors">
                                <div class="flex-1 min-w-0 pr-2">
                                    <div class="flex items-center flex-wrap gap-1">
                                        <span class="font-bold text-neutral-800 text-xs">${escape(data.code)}</span>
                                        ${originHtml}
                                        ${whHtml}
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
                        const tooltipText = `${escape(data.code)}${originText}${data.name ? ' - ' + escape(data.name) : ''}`;
                        return `
                            <div class="truncate text-xs font-semibold text-neutral-800" title="${tooltipText}">
                                <span class="font-bold text-purple-700">${escape(data.code)}</span>${originText}
                                ${data.name ? `<span class="text-neutral-500 font-normal text-[11px] ml-1">- ${escape(data.name)}</span>` : ''}
                            </div>
                        `;
                    }
                },
                onChange: function(val) {
                    onMaterialSelected(rowIdx, val);
                }
            });

            selectEl._tomSelect = ts;

            if (selectedMaterialId) {
                const strId = String(selectedMaterialId);
                ts.setValue(strId, true);
                onMaterialSelected(rowIdx, strId);
            }
        }
    }

    function onMaterialSelected(rowIdx, materialId) {
        const tr = document.getElementById(`row_${rowIdx}`);
        if (!tr) return;

        const mat = ALL_MATERIALS.find(m => String(m.id) === String(materialId));
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
        const isMatBoard = (mat.item_type === 'board' || mat.unit === 'Tấm') && !String(mat.code || '').startsWith('CHI-');
        if (heightInput) {
            if (isMatBoard) {
                heightInput.disabled = false;
                heightInput.placeholder = '2440';
                if (!heightInput.value) heightInput.value = '2440';
            } else {
                heightInput.disabled = true;
                heightInput.value = '';
                heightInput.placeholder = '—';
            }
        }
        if (widthInput) {
            if (isMatBoard) {
                widthInput.disabled = false;
                widthInput.placeholder = '1220';
                if (!widthInput.value) widthInput.value = '1220';
            } else {
                widthInput.disabled = true;
                widthInput.value = '';
                widthInput.placeholder = '—';
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
        let total = 0;
        const rows = document.querySelectorAll('#itemsTableBody tr');
        rows.forEach(r => {
            const qtyInput = r.querySelector('.qty-input');
            const warnEl = r.querySelector('.stock-warn');
            const maxStock = parseFloat(r.getAttribute('data-max-stock') || 0);

            if (qtyInput) {
                const val = parseFloat(qtyInput.value) || 0;
                total += val;

                if (warnEl) {
                    if (maxStock > 0 && val > maxStock) {
                        warnEl.classList.remove('hidden');
                    } else {
                        warnEl.classList.add('hidden');
                    }
                }
            }
        });

        const displayEl = document.getElementById('totalQtyDisplay');
        if (displayEl) {
            displayEl.textContent = total.toLocaleString('vi-VN', { maximumFractionDigits: 2 });
        }
    }

    // Form submit check
    document.getElementById('transferForm')?.addEventListener('submit', function(e) {
        if (!validateWarehouses()) {
            e.preventDefault();
            alert('Kho nhận (đích) phải khác kho xuất (nguồn)!');
            return false;
        }

        const rows = document.querySelectorAll('#itemsTableBody tr');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Vui lòng thêm ít nhất một mặt hàng cần chuyển kho.');
            return false;
        }

        let hasValidItem = false;
        rows.forEach(r => {
            const selectEl = r.querySelector('.material-tom-select');
            const qtyInput = r.querySelector('.qty-input');
            if (selectEl && selectEl.value && qtyInput && parseFloat(qtyInput.value) > 0) {
                hasValidItem = true;
            }
        });

        if (!hasValidItem) {
            e.preventDefault();
            alert('Vui lòng chọn vật tư và nhập số lượng chuyển hợp lệ (> 0).');
            return false;
        }
    });

    // Init 3 rows by default
    document.addEventListener('DOMContentLoaded', function() {
        validateWarehouses();
        addItemRow();
        addItemRow();
        addItemRow();
    });
</script>
@endpush
