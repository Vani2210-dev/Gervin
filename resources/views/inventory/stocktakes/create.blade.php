@extends('layout.layout')
@php
    $title    = 'Quản lý tồn kho';
    $subTitle = 'Tạo Phiếu Kiểm kê';
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        @include('inventory.partials.navbar')

        @if(isset($errors) && $errors->any())
            <div class="mb-6 bg-rose-100 border border-rose-300 text-rose-700 rounded-lg px-4 py-3 text-sm">
                <ul class="list-disc pl-5 mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('inventory.stocktakes.store') }}" method="POST" id="stocktakeForm">
            @csrf

            {{-- Thông tin chung phiếu kiểm kê --}}
            <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
                <div class="p-4 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                            <iconify-icon icon="solar:checklist-bold" class="text-2xl"></iconify-icon>
                        </div>
                        <div>
                            <h5 class="font-bold text-neutral-800 text-base mb-1">Phiếu Kiểm kê & Cân bằng kho</h5>
                            <p class="text-xs text-neutral-500 mb-0">Nhập số lượng đếm thực tế tại kho. Hệ thống tự động so khớp và tính chênh lệch tồn.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('inventory.stocktakes.index') }}" class="btn btn-secondary px-4 py-2 rounded-lg text-xs font-bold text-neutral-600 border border-neutral-300">
                            Quay lại
                        </a>
                        <button type="submit" class="btn bg-amber-500 hover:bg-amber-600 text-white px-5 py-2 rounded-lg text-xs font-bold shadow-sm flex items-center gap-2">
                            <iconify-icon icon="solar:disk-bold" class="text-base"></iconify-icon> Lưu phiếu kiểm kê
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 md:col-span-3">
                            <label class="block text-xs font-bold text-neutral-700 mb-1">Mã phiếu kiểm kê</label>
                            <input type="text" name="code" value="{{ old('code', $nextCode) }}" class="form-control rounded-lg px-3 py-2 border-neutral-200 text-xs w-full font-bold text-amber-600" required>
                        </div>

                        <div class="col-span-12 md:col-span-3">
                            <label class="block text-xs font-bold text-neutral-700 mb-1">Kho kiểm kê <span class="text-rose-500">*</span></label>
                            <input type="hidden" name="warehouse_id" value="{{ $warehouse->id }}">
                            <input type="text" value="{{ $warehouse->name }} ({{ $warehouse->code }})" class="form-control rounded-lg px-3 py-2 border-neutral-200 text-xs w-full bg-neutral-100 text-neutral-600 cursor-not-allowed font-medium" readonly>
                        </div>

                        <div class="col-span-12 md:col-span-3">
                            <label class="block text-xs font-bold text-neutral-700 mb-1">Ngày kiểm kê <span class="text-rose-500">*</span></label>
                            <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="form-control rounded-lg px-3 py-2 border-neutral-200 text-xs w-full" required>
                        </div>

                        <div class="col-span-12 md:col-span-3">
                            <label class="block text-xs font-bold text-neutral-700 mb-1">Người kiểm kê</label>
                            <input type="text" name="creator_name" value="{{ old('creator_name', Auth::user()->name ?? 'Kế toán kho') }}" class="form-control rounded-lg px-3 py-2 border-neutral-200 text-xs w-full">
                        </div>

                        <div class="col-span-12 md:col-span-8">
                            <label class="block text-xs font-bold text-neutral-700 mb-1">Ghi chú / Mục đích kiểm kê</label>
                            <input type="text" name="notes" value="{{ old('notes') }}" class="form-control rounded-lg px-3 py-2 border-neutral-200 text-xs w-full" placeholder="Ví dụ: Kiểm kê định kỳ cuối tháng, kiểm kê đột xuất...">
                        </div>

                        <div class="col-span-12 md:col-span-4 flex items-end">
                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg w-full flex items-center gap-3">
                                <input type="checkbox" name="auto_balance" id="auto_balance" value="1" class="w-4 h-4 text-amber-600 border-neutral-300 rounded focus:ring-amber-500">
                                <label for="auto_balance" class="text-xs font-bold text-amber-900 cursor-pointer select-none">
                                    Cân bằng kho ngay sau khi lưu
                                    <span class="block text-[11px] font-normal text-amber-700">Tự động cập nhật tồn thực tế vào kho và tạo bút toán điều chỉnh</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bảng danh sách vật tư kiểm kê --}}
            <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
                <div class="p-4 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-4 bg-neutral-50">
                    <div class="flex items-center gap-3">
                        <h6 class="font-bold text-neutral-800 text-sm mb-0">Danh sách vật tư kiểm kê ({{ count($materials) }} mã)</h6>
                        <span class="text-xs text-neutral-500">Hỗ trợ lọc nhanh hoặc đồng bộ số lượng tồn</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <iconify-icon icon="ion:search-outline" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
                            <input type="text" id="filterInput" onkeyup="filterTable()" class="form-control rounded-lg pl-9 pr-3 py-2 border-neutral-200 text-xs w-60" placeholder="Lọc mã SKU hoặc tên...">
                        </div>
                        <button type="button" onclick="setActualEqualsBook()" class="btn btn-sm bg-neutral-200 hover:bg-neutral-300 text-neutral-800 px-3 py-2 rounded-lg text-xs font-bold flex items-center gap-1">
                            <iconify-icon icon="solar:copy-bold" class="text-sm"></iconify-icon> Gán thực tế = Sổ sách
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto w-full">
                    <table class="w-full border-collapse text-xs text-left" id="stocktakeTable">
                        <thead>
                            <tr class="bg-neutral-100 text-neutral-700 uppercase font-bold border-b border-neutral-200">
                                <th class="py-3 px-3 w-12 text-center">STT</th>
                                <th class="py-3 px-3 w-28">Mã SKU</th>
                                <th class="py-3 px-3 w-28">Mã gốc TQ</th>
                                <th class="py-3 px-4 min-w-[200px]">Tên vật tư / Quy cách</th>
                                <th class="py-3 px-3 w-20 text-center">ĐVT</th>
                                <th class="py-3 px-3 w-32 text-right">Tồn sổ sách</th>
                                <th class="py-3 px-3 w-40 text-center">Tồn thực tế đếm được</th>
                                <th class="py-3 px-3 w-32 text-right">Chênh lệch</th>
                                <th class="py-3 px-4 min-w-[180px]">Ghi chú chênh lệch / Lý do</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 bg-white">
                            @foreach($materials as $index => $m)
                                <tr class="item-row hover:bg-neutral-50 transition-colors" data-code="{{ strtolower($m->code) }}" data-name="{{ strtolower($m->name) }}" data-origin="{{ strtolower($m->origin_code) }}">
                                    <td class="py-3 px-3 text-center text-neutral-500 font-medium">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="py-3 px-3 font-bold text-neutral-900">
                                        {{ $m->code }}
                                        <input type="hidden" name="items[{{ $index }}][material_id]" value="{{ $m->id }}">
                                    </td>
                                    <td class="py-3 px-3 font-medium text-neutral-600">
                                        {{ $m->origin_code ?: '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-neutral-800 font-semibold">
                                        {{ $m->name }}
                                        @if($m->category)
                                            <span class="block text-[10px] text-neutral-400 font-normal">{{ $m->category }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center text-neutral-600 font-medium">
                                        {{ $m->unit }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-bold text-neutral-700 book-qty" data-book="{{ (float)$m->current_stock }}">
                                        {{ fmod($m->current_stock, 1) == 0 ? number_format($m->current_stock, 0, ',', '.') : number_format($m->current_stock, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2 px-3 text-center">
                                        <input type="number" 
                                               name="items[{{ $index }}][actual_quantity]" 
                                               value="{{ (float)$m->current_stock }}" 
                                               step="any" 
                                               min="0" 
                                               class="actual-qty form-control rounded-lg px-3 py-2 border-neutral-300 text-xs w-32 text-right font-bold text-neutral-900 focus:border-amber-500" 
                                               oninput="calcRowDiff(this)" 
                                               required>
                                    </td>
                                    <td class="py-3 px-3 text-right font-bold row-diff text-neutral-400">
                                        0
                                    </td>
                                    <td class="py-2 px-4">
                                        <input type="text" name="items[{{ $index }}][notes]" class="form-control rounded-lg px-3 py-2 border-neutral-200 text-xs w-full" placeholder="Nguyên nhân nếu có sai lệch...">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Tổng kết chân bảng --}}
                <div class="p-4 border-t border-neutral-200 bg-neutral-50 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-6 text-xs">
                        <div>
                            <span class="text-neutral-500">Tổng mã vật tư:</span>
                            <span class="font-bold text-neutral-900 ml-1" id="totalItemsCount">{{ count($materials) }}</span>
                        </div>
                        <div>
                            <span class="text-neutral-500">Tổng tồn sổ sách:</span>
                            <span class="font-bold text-neutral-900 ml-1" id="totalBookSum">0</span>
                        </div>
                        <div>
                            <span class="text-neutral-500">Tổng tồn thực tế:</span>
                            <span class="font-bold text-amber-600 ml-1" id="totalActualSum">0</span>
                        </div>
                        <div>
                            <span class="text-neutral-500">Tổng chênh lệch:</span>
                            <span class="font-black ml-1" id="totalDiffSum">0</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="btn bg-amber-500 hover:bg-amber-600 text-white px-6 py-2 rounded-lg text-xs font-bold shadow-sm flex items-center gap-2">
                            <iconify-icon icon="solar:check-read-bold" class="text-base"></iconify-icon> Hoàn tất kiểm kê & Lưu
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function filterTable() {
        const query = document.getElementById('filterInput').value.toLowerCase();
        const rows = document.querySelectorAll('#stocktakeTable tbody tr.item-row');
        rows.forEach(r => {
            const code = r.getAttribute('data-code') || '';
            const name = r.getAttribute('data-name') || '';
            const origin = r.getAttribute('data-origin') || '';
            if (code.includes(query) || name.includes(query) || origin.includes(query)) {
                r.style.display = '';
            } else {
                r.style.display = 'none';
            }
        });
    }

    function calcRowDiff(input) {
        const row = input.closest('tr');
        const bookQty = parseFloat(row.querySelector('.book-qty').getAttribute('data-book')) || 0;
        const actualQty = parseFloat(input.value) || 0;
        const diff = actualQty - bookQty;
        const diffEl = row.querySelector('.row-diff');

        if (diff > 0) {
            diffEl.innerHTML = `<span class="text-emerald-600">+${diff.toFixed(2).replace(/\.00$/, '')} (Thừa)</span>`;
        } else if (diff < 0) {
            diffEl.innerHTML = `<span class="text-rose-600">${diff.toFixed(2).replace(/\.00$/, '')} (Thiếu)</span>`;
        } else {
            diffEl.innerHTML = `<span class="text-neutral-400">0</span>`;
        }

        calcTotals();
    }

    function setActualEqualsBook() {
        document.querySelectorAll('#stocktakeTable tbody tr.item-row').forEach(row => {
            const bookQty = parseFloat(row.querySelector('.book-qty').getAttribute('data-book')) || 0;
            const actualInput = row.querySelector('.actual-qty');
            actualInput.value = bookQty;
            calcRowDiff(actualInput);
        });
    }

    function calcTotals() {
        let totalBook = 0;
        let totalActual = 0;

        document.querySelectorAll('#stocktakeTable tbody tr.item-row').forEach(row => {
            const b = parseFloat(row.querySelector('.book-qty').getAttribute('data-book')) || 0;
            const a = parseFloat(row.querySelector('.actual-qty').value) || 0;
            totalBook += b;
            totalActual += a;
        });

        const totalDiff = totalActual - totalBook;

        document.getElementById('totalBookSum').innerText = totalBook.toLocaleString('vi-VN', { maximumFractionDigits: 2 });
        document.getElementById('totalActualSum').innerText = totalActual.toLocaleString('vi-VN', { maximumFractionDigits: 2 });

        const diffEl = document.getElementById('totalDiffSum');
        if (totalDiff > 0) {
            diffEl.className = 'font-black ml-1 text-emerald-600';
            diffEl.innerText = `+${totalDiff.toLocaleString('vi-VN', { maximumFractionDigits: 2 })} (Thừa)`;
        } else if (totalDiff < 0) {
            diffEl.className = 'font-black ml-1 text-rose-600';
            diffEl.innerText = `${totalDiff.toLocaleString('vi-VN', { maximumFractionDigits: 2 })} (Thiếu)`;
        } else {
            diffEl.className = 'font-black ml-1 text-neutral-500';
            diffEl.innerText = '0 (Khớp hoàn toàn)';
        }
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.actual-qty').forEach(input => {
            calcRowDiff(input);
        });
        calcTotals();
    });
</script>
@endsection
