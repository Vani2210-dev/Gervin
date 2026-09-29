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
                            <select name="warehouse_id" class="form-select rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-rose-500" required>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Người / Bộ phận nhận <span class="text-danger-500">*</span></label>
                            <input type="text" name="recipient" value="Xưởng sản xuất Gervin" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-rose-500" required>
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
                        <div class="md:col-span-2">
                            <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Ghi chú phiếu xuất</label>
                            <input type="text" name="notes" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-rose-500" placeholder="Xuất theo lệnh sản xuất số, đơn hàng...">
                        </div>
                    </div>

                    {{-- Items Details Table --}}
                    <div class="border border-neutral-200 rounded-xl overflow-hidden mb-6">
                        <div class="p-3 bg-neutral-100 border-b border-neutral-200 flex items-center justify-between">
                            <h6 class="font-bold text-neutral-800 text-xs uppercase mb-0">Danh sách vật tư xuất kho</h6>
                            <button type="button" onclick="addItemRow()" class="btn btn-sm bg-rose-600 hover:bg-rose-700 text-white px-3 py-1 rounded-lg text-xs font-bold flex items-center gap-1 shadow-sm">
                                <iconify-icon icon="ic:baseline-plus" class="text-sm"></iconify-icon> Thêm dòng
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left border-collapse" id="itemsTable">
                                <thead class="bg-neutral-50 text-neutral-700 font-bold border-b border-neutral-200">
                                    <tr>
                                        <th class="py-2 px-3 w-12 text-center">STT</th>
                                        <th class="py-2 px-3 min-w-[240px]">Mặt hàng vật tư <span class="text-danger-500">*</span></th>
                                        <th class="py-2 px-3 w-20 text-center">ĐVT</th>
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
                                        <td colspan="4" class="py-3 px-3 text-right text-neutral-700 uppercase">TỔNG CỘNG XUẤT:</td>
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
                        <button type="submit" class="btn bg-rose-600 hover:bg-rose-700 text-white px-6 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm">
                            <iconify-icon icon="solar:diskette-bold" class="text-base"></iconify-icon> Xác nhận Xuất kho
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const MATERIALS = @json($materials);
    let rowCount = 0;

    function addItemRow(selectedMaterialId = '', qty = '') {
        const tbody = document.getElementById('itemsTableBody');
        const tr = document.createElement('tr');
        tr.id = `row_${rowCount}`;
        tr.className = 'hover:bg-neutral-50';

        let optionsHtml = '<option value="">-- Chọn mặt hàng vật tư xuất --</option>';
        MATERIALS.forEach(m => {
            const isSel = (m.id == selectedMaterialId) ? 'selected' : '';
            optionsHtml += `<option value="${m.id}" data-unit="${m.unit}" data-stock="${m.current_stock}" ${isSel}>${m.code} - ${m.name} (Tồn: ${m.current_stock})</option>`;
        });

        tr.innerHTML = `
            <td class="py-2 px-3 text-center text-neutral-400 font-medium row-stt">${tbody.children.length + 1}</td>
            <td class="py-2 px-3">
                <select name="items[${rowCount}][material_id]" class="form-select rounded-lg w-full border-neutral-200 px-2 py-2 text-xs material-select focus:border-rose-500" required onchange="onMaterialChange(this, ${rowCount})">
                    ${optionsHtml}
                </select>
            </td>
            <td class="py-2 px-3 text-center text-neutral-600 unit-cell">Tấm</td>
            <td class="py-2 px-3 text-right font-bold text-amber-900 bg-amber-50 bg-opacity-40 stock-cell">-</td>
            <td class="py-2 px-3 text-right">
                <input type="number" name="items[${rowCount}][quantity]" value="${qty}" step="0.01" min="0.01" class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-right text-xs font-bold text-rose-700 focus:border-rose-500 qty-input" required oninput="calcTotals()">
                <div class="text-[10px] text-danger-600 stock-warn hidden mt-1 font-semibold">Vượt số lượng tồn!</div>
            </td>
            <td class="py-2 px-3">
                <input type="text" name="items[${rowCount}][notes]" class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-xs" placeholder="Ghi chú...">
            </td>
            <td class="py-2 px-3 text-center">
                <button type="button" onclick="removeRow(${rowCount})" class="text-danger-500 hover:text-danger-700 p-1 text-base leading-none" title="Xóa dòng">&times;</button>
            </td>
        `;

        tbody.appendChild(tr);
        rowCount++;
        updateStt();
        calcTotals();
    }

    function onMaterialChange(selectElem, rowIdx) {
        const tr = document.getElementById(`row_${rowIdx}`);
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            tr.querySelector('.unit-cell').textContent = 'Tấm';
            tr.querySelector('.stock-cell').textContent = '-';
            return;
        }

        const unit = selectedOption.getAttribute('data-unit') || 'Tấm';
        const stock = parseFloat(selectedOption.getAttribute('data-stock') || 0);

        tr.querySelector('.unit-cell').textContent = unit;
        tr.querySelector('.stock-cell').textContent = stock.toLocaleString('vi-VN');
        tr.setAttribute('data-max-stock', stock);

        calcTotals();
    }

    function removeRow(rowIdx) {
        const tr = document.getElementById(`row_${rowIdx}`);
        if (tr) tr.remove();
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

    document.addEventListener('DOMContentLoaded', () => {
        addItemRow();
        addItemRow();
        addItemRow();
    });
</script>
@endsection
