@extends('layout.layout')
@php
    $title    = 'Quản lý tồn kho';
    $subTitle = 'Sổ tổng hợp chứng từ Nhập, Xuất kho';
    $type     = $type ?? request('type', 'all');
    $warehouseId = $warehouseId ?? request('warehouse_id');
    $search   = $search ?? request('search');
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        @include('inventory.partials.navbar')

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-success-100 border border-success-300 text-success-700 rounded-lg px-4 py-3 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="solar:check-circle-bold" class="text-xl text-success-600"></iconify-icon>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-success-700 hover:text-success-900 font-bold">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-danger-100 border border-danger-300 text-danger-700 rounded-lg px-4 py-3 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="solar:danger-triangle-bold" class="text-xl text-danger-600"></iconify-icon>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-danger-700 hover:text-danger-900 font-bold">&times;</button>
            </div>
        @endif

        {{-- Main Container Card --}}
        <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
            {{-- Filter & Action Bar (Đồng bộ chuẩn hệ thống Gervin) --}}
            <div class="p-3.5 border-b border-neutral-200 flex flex-col gap-3 bg-white">
                {{-- Tầng 1: Tìm kiếm, Loại chứng từ, Chọn Kho & Thao tác --}}
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        {{-- 1. Ô tìm kiếm nhanh --}}
                        <form action="{{ route('inventory.receipts.index') }}" method="GET" class="relative">
                            @if(request('date_preset')) <input type="hidden" name="date_preset" value="{{ request('date_preset') }}"> @endif
                            @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
                            @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
                            @if($type !== 'all') <input type="hidden" name="type" value="{{ $type }}"> @endif
                            @if($warehouseId) <input type="hidden" name="warehouse_id" value="{{ $warehouseId }}"> @endif

                            <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-sm"></iconify-icon>
                            <input type="text" name="search" value="{{ $search }}"
                                   class="form-control rounded-lg pl-9 pr-7 py-1.5 border-neutral-300 text-xs w-56 md:w-64 focus:border-primary-500 shadow-2xs"
                                   placeholder="Tìm số phiếu, đối tác, ghi chú...">
                            @if($search)
                                <a href="{{ route('inventory.receipts.index', request()->except(['search', 'page'])) }}"
                                   class="absolute right-2.5 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 text-sm font-bold" title="Xóa tìm kiếm">&times;</a>
                            @endif
                        </form>

                        {{-- 2. Phân loại chứng từ (Segmented Control Pill Tabs) --}}
                        <div class="bg-neutral-100 p-0.5 rounded-lg flex items-center gap-0.5 text-xs font-semibold shadow-2xs">
                            <a href="{{ route('inventory.receipts.index', array_merge(request()->query(), ['type' => 'all', 'page' => 1])) }}"
                               class="px-3 py-1.5 rounded-md transition-all flex items-center gap-1.5 {{ $type === 'all' ? 'bg-white text-neutral-900 shadow-2xs font-bold' : 'text-neutral-600 hover:text-neutral-900' }}">
                                <span>Tất cả</span>
                                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $type === 'all' ? 'bg-neutral-900 text-white font-bold' : 'bg-neutral-200 text-neutral-600' }}">
                                    {{ $stats['total_vouchers'] ?? 0 }}
                                </span>
                            </a>
                            <a href="{{ route('inventory.receipts.index', array_merge(request()->query(), ['type' => 'receipt', 'page' => 1])) }}"
                               class="px-3 py-1.5 rounded-md transition-all flex items-center gap-1.5 {{ $type === 'receipt' ? 'bg-emerald-600 text-white shadow-2xs font-bold' : 'text-emerald-700 hover:bg-emerald-50' }}">
                                <iconify-icon icon="solar:import-bold" class="text-sm {{ $type === 'receipt' ? 'text-white' : 'text-emerald-600' }}"></iconify-icon>
                                <span>Nhập kho</span>
                                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $type === 'receipt' ? 'bg-white/20 text-white font-bold' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $stats['total_receipts'] ?? 0 }}
                                </span>
                            </a>
                            <a href="{{ route('inventory.receipts.index', array_merge(request()->query(), ['type' => 'issue', 'page' => 1])) }}"
                               class="px-3 py-1.5 rounded-md transition-all flex items-center gap-1.5 {{ $type === 'issue' ? 'bg-rose-600 text-white shadow-2xs font-bold' : 'text-rose-700 hover:bg-rose-50' }}">
                                <iconify-icon icon="solar:export-bold" class="text-sm {{ $type === 'issue' ? 'text-white' : 'text-rose-600' }}"></iconify-icon>
                                <span>Xuất kho</span>
                                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $type === 'issue' ? 'bg-white/20 text-white font-bold' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $stats['total_issues'] ?? 0 }}
                                </span>
                            </a>
                        </div>

                        {{-- 3. Chọn Kho --}}
                        <form action="{{ route('inventory.receipts.index') }}" method="GET" class="inline-flex items-center">
                            @if(request('date_preset')) <input type="hidden" name="date_preset" value="{{ request('date_preset') }}"> @endif
                            @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
                            @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
                            @if($type !== 'all') <input type="hidden" name="type" value="{{ $type }}"> @endif
                            @if($search) <input type="hidden" name="search" value="{{ $search }}"> @endif

                            <div class="relative">
                                <select name="warehouse_id" onchange="this.form.submit()"
                                        class="form-select form-select-sm text-xs rounded-lg border-neutral-300 bg-white pl-8 pr-7 py-1.5 font-semibold text-neutral-700 shadow-2xs focus:border-primary-500">
                                    <option value="">-- Tất cả kho --</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ (string)$warehouseId === (string)$wh->id ? 'selected' : '' }}>
                                            {{ $wh->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <iconify-icon icon="solar:warehouse-bold" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-neutral-400 text-sm pointer-events-none"></iconify-icon>
                            </div>
                        </form>
                    </div>

                    {{-- Action Buttons & Đặt lại bộ lọc --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($search || $warehouseId || $type !== 'all' || request('start_date') || request('end_date') || (request('date_preset') && request('date_preset') !== 'all'))
                            <a href="{{ route('inventory.receipts.index') }}"
                               class="btn bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs px-3 py-1.5 rounded-lg font-semibold flex items-center gap-1 shadow-2xs transition-all"
                               title="Xóa tất cả bộ lọc về mặc định">
                                <iconify-icon icon="solar:restart-bold" class="text-sm"></iconify-icon>
                                <span>Đặt lại</span>
                            </a>
                        @endif

                        <a href="{{ route('inventory.receipts.create') }}" class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm transition-all">
                            <iconify-icon icon="solar:import-bold" class="text-base"></iconify-icon>
                            <span>+ Tạo phiếu Nhập (PNK)</span>
                        </a>
                        <a href="{{ route('inventory.issues.create') }}" class="btn btn-sm bg-rose-600 hover:bg-rose-700 text-white px-3.5 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm transition-all">
                            <iconify-icon icon="solar:export-bold" class="text-base"></iconify-icon>
                            <span>+ Tạo phiếu Xuất (PXK)</span>
                        </a>
                    </div>
                </div>

                {{-- Tầng 2: Kỳ thời gian (Bộ lọc ngày tháng) --}}
                <div class="flex items-center justify-between flex-wrap gap-2.5 pt-2.5 border-t border-neutral-100">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-bold text-neutral-700 flex items-center gap-1.5">
                            <iconify-icon icon="solar:calendar-date-bold" class="text-primary-600 text-sm"></iconify-icon>
                            Kỳ báo cáo:
                        </span>
                        <x-date-filter :action="route('inventory.receipts.index')" />
                    </div>

                    <div class="text-[11px] text-neutral-500 font-medium flex items-center gap-2">
                        <span>Hiển thị <strong>{{ $vouchers->count() }}</strong>/{{ $vouchers->total() }} chứng từ</span>
                        @if(isset($stats['total_quantity']))
                            <span class="text-neutral-300">|</span>
                            <span>Tổng SL: <strong class="text-primary-700">{{ fmod($stats['total_quantity'], 1) == 0 ? number_format($stats['total_quantity'], 0, ',', '.') : number_format($stats['total_quantity'], 2, ',', '.') }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- 1. BẢNG MASTER: DANH SÁCH CHỨNG TỪ (BẢNG TRÊN) --}}
            <div class="p-3">
                <div class="overflow-x-auto max-h-[380px] overflow-y-auto border border-neutral-200 rounded-lg shadow-2xs bg-white relative">
                    <table class="w-full border-collapse text-xs text-left" id="masterVouchersTable">
                        <thead class="sticky top-0 bg-neutral-100 z-10 uppercase text-[11px] font-bold text-neutral-700 border-b border-neutral-200 shadow-2xs">
                            <tr>
                                <th class="py-2.5 px-3 w-10 text-center">STT</th>
                                <th class="py-2.5 px-3 w-28">Ngày CT</th>
                                <th class="py-2.5 px-3 w-36">Số chứng từ</th>
                                <th class="py-2.5 px-3 w-28 text-center">Loại phiếu</th>
                                <th class="py-2.5 px-3 w-36">Kho</th>
                                <th class="py-2.5 px-3 min-w-[200px]">Diễn giải / Lý do</th>
                                <th class="py-2.5 px-3 min-w-[200px]">Đối tượng</th>
                                <th class="py-2.5 px-3 w-32">Người giao/nhận</th>
                                <th class="py-2.5 px-3 w-28 text-center">Đơn hàng</th>
                                <th class="py-2.5 px-3 w-20 text-center">Số loại VT</th>
                                <th class="py-2.5 px-3 w-28 text-right">Tổng số lượng</th>
                                <th class="py-2.5 px-3 w-32 text-right">Tổng tiền</th>
                                <th class="py-2.5 px-3 w-28 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 bg-white">
                            @forelse($vouchers as $index => $v)
                                <tr class="voucher-row cursor-pointer transition-colors hover:bg-neutral-50/80"
                                    data-voucher-id="{{ $v->unique_id }}"
                                    onclick="selectVoucher('{{ $v->unique_id }}')">
                                    <td class="py-2.5 px-3 text-center text-neutral-500 font-medium voucher-stt-cell">
                                        {{ $vouchers->firstItem() + $index }}
                                    </td>
                                    <td class="py-2.5 px-3 text-neutral-700 whitespace-nowrap font-medium">
                                        {{ $v->date ? $v->date->format('d/m/Y') : '—' }}
                                    </td>
                                    <td class="py-2.5 px-3 font-mono font-bold text-primary-700 whitespace-nowrap">
                                        <a href="{{ $v->show_url }}" class="hover:underline flex items-center gap-1" onclick="event.stopPropagation()">
                                            <iconify-icon icon="solar:document-text-bold" class="text-sm"></iconify-icon>
                                            <span>{{ $v->code }}</span>
                                        </a>
                                    </td>
                                    <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full {{ $v->badge_class }}">
                                            <iconify-icon icon="{{ $v->icon }}" class="text-xs"></iconify-icon>
                                            <span>{{ $v->type_label }}</span>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-neutral-700 font-medium whitespace-nowrap">
                                        {{ $v->warehouse_name }}
                                    </td>
                                    <td class="py-2.5 px-3 text-neutral-800 truncate max-w-xs" title="{{ $v->reason }}">
                                        {{ $v->reason ?: '—' }}
                                    </td>
                                    <td class="py-2.5 px-3 font-semibold text-neutral-800 truncate max-w-xs" title="{{ $v->partner }}">
                                        {{ $v->partner }}
                                    </td>
                                    <td class="py-2.5 px-3 text-neutral-600 truncate max-w-[120px]" title="{{ $v->deliverer }} / {{ $v->receiver }}">
                                        {{ $v->voucher_type === 'receipt' ? $v->deliverer : $v->receiver }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                        @if($v->order)
                                            <a href="{{ route('orders.show', $v->order->id) }}" class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded px-1.5 py-0.5" title="Đơn hàng liên kết" onclick="event.stopPropagation()">
                                                <iconify-icon icon="solar:document-text-bold" class="text-xs"></iconify-icon>
                                                <span>{{ $v->order->code }}</span>
                                            </a>
                                        @else
                                            <span class="text-neutral-300">—</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-bold text-neutral-700">
                                        {{ $v->items_count }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-black text-emerald-700 whitespace-nowrap">
                                        {{ fmod($v->total_quantity, 1) == 0 ? number_format($v->total_quantity, 0, ',', '.') : number_format($v->total_quantity, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-bold text-neutral-800 whitespace-nowrap">
                                        {{ $v->total_amount > 0 ? number_format($v->total_amount, 0, ',', '.') . 'đ' : '0đ' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center whitespace-nowrap" onclick="event.stopPropagation()">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ $v->show_url }}" class="p-1 text-primary-600 hover:text-primary-800 text-base" title="Xem chi tiết phiếu">
                                                <iconify-icon icon="solar:eye-bold"></iconify-icon>
                                            </a>
                                            <a href="{{ $v->edit_url }}" class="p-1 text-amber-600 hover:text-amber-800 text-base" title="Chỉnh sửa phiếu">
                                                <iconify-icon icon="solar:pen-new-square-bold"></iconify-icon>
                                            </a>
                                            <a href="{{ $v->print_url }}" target="_blank" class="p-1 text-info-600 hover:text-info-800 text-base" title="In phiếu">
                                                <iconify-icon icon="solar:printer-outline"></iconify-icon>
                                            </a>
                                            <form action="{{ $v->destroy_url }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa phiếu {{ $v->code }}? Số lượng hàng hóa tương ứng sẽ được tự động hoàn trả khỏi tồn kho!')" class="inline-block m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 text-danger-600 hover:text-danger-800 text-base leading-none" title="Xóa phiếu">
                                                    <iconify-icon icon="fluent:delete-24-regular"></iconify-icon>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="py-12 text-center text-neutral-400 font-medium bg-neutral-50/50">
                                        <iconify-icon icon="solar:box-broken-outline" class="text-4xl text-neutral-300 mb-2 block mx-auto"></iconify-icon>
                                        Không tìm thấy chứng từ Nhập/Xuất kho nào phù hợp. Hãy điều chỉnh bộ lọc hoặc bấm tạo phiếu mới.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($vouchers->hasPages())
                    <div class="p-2 border-t border-neutral-200 mt-2">
                        {{ $vouchers->links() }}
                    </div>
                @endif

                {{-- 2. BẢNG DETAIL: CHI TIẾT VẬT TƯ CHỨNG TỪ (BẢNG DƯỚI) --}}
                <div class="mt-4 rounded-xl border border-neutral-200 overflow-hidden shadow-sm bg-white">
                    {{-- Detail Header Toolbar --}}
                    <div class="px-4 py-2.5 bg-neutral-100/90 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="w-6 h-6 rounded-md bg-primary-600 text-white flex items-center justify-center text-xs font-bold shadow-2xs">
                                <iconify-icon icon="solar:list-check-bold"></iconify-icon>
                            </span>
                            <span class="font-bold text-xs text-neutral-700 uppercase tracking-wide">Chi tiết chứng từ:</span>
                            <span id="detailVoucherCode" class="font-mono font-bold text-xs text-primary-700 bg-white px-2 py-0.5 rounded border border-primary-300 shadow-2xs">—</span>
                            <span id="detailVoucherBadge" class="text-[11px] font-bold px-2 py-0.5 rounded-full border bg-neutral-100 text-neutral-600 border-neutral-300">—</span>
                            <span id="detailVoucherMeta" class="text-xs text-neutral-500 font-medium">Bấm vào bất kỳ dòng nào ở bảng trên để xem chi tiết vật tư</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs font-semibold">
                            <span id="detailRowCount" class="bg-white px-2.5 py-1 rounded-md border border-neutral-200 shadow-2xs font-bold text-neutral-700">0 dòng</span>
                            <span id="detailTotalQty" class="bg-white px-2.5 py-1 rounded-md border border-neutral-200 shadow-2xs font-bold text-emerald-700">Tổng SL: 0</span>
                            <span id="detailTotalAmount" class="bg-white px-2.5 py-1 rounded-md border border-neutral-200 shadow-2xs font-bold text-neutral-900">Tổng tiền: 0đ</span>
                        </div>
                    </div>

                    {{-- Detail Table Body --}}
                    <div class="overflow-x-auto max-h-[300px] overflow-y-auto">
                        <table class="w-full border-collapse text-xs text-left" id="detailItemsTable">
                            <thead class="sticky top-0 bg-neutral-50 z-10 uppercase text-[11px] font-bold text-neutral-600 border-b border-neutral-200">
                                <tr>
                                    <th class="py-2 px-3 w-10 text-center">STT</th>
                                    <th class="py-2 px-3 w-36">Mã hàng (SKU)</th>
                                    <th class="py-2 px-3 min-w-[240px]">Tên hàng hóa / Vật tư</th>
                                    <th class="py-2 px-3 w-36 text-center">Kích thước (mm)</th>
                                    <th class="py-2 px-3 w-32">Kho</th>
                                    <th class="py-2 px-3 w-20 text-center">ĐVT</th>
                                    <th class="py-2 px-3 w-28 text-right">Số lượng</th>
                                    <th class="py-2 px-3 w-32 text-right">Đơn giá (đ)</th>
                                    <th class="py-2 px-3 w-36 text-right">Thành tiền (đ)</th>
                                    <th class="py-2 px-3 min-w-[160px]">Ghi chú dòng</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 bg-white" id="detailItemsBody">
                                <tr>
                                    <td colspan="10" class="py-8 text-center text-neutral-400">
                                        <iconify-icon icon="solar:hand-stars-broken" class="text-3xl text-neutral-300 mb-1 block mx-auto"></iconify-icon>
                                        Đang tải chi tiết vật tư...
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="sticky bottom-0 bg-neutral-100 z-10 font-bold border-t-2 border-neutral-300 text-xs text-neutral-800">
                                <tr>
                                    <td colspan="6" class="py-2.5 px-3 text-end uppercase text-neutral-600">
                                        Tổng cộng chi tiết:
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-black text-emerald-700 text-sm" id="tfootTotalQty">
                                        0
                                    </td>
                                    <td class="py-2.5 px-3 text-right text-neutral-400">—</td>
                                    <td class="py-2.5 px-3 text-right font-black text-neutral-900 text-sm" id="tfootTotalAmount">
                                        0đ
                                    </td>
                                    <td class="py-2.5 px-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.voucherItemsMap = @json($voucherItemsMap ?? []);
    window.voucherMetaMap = @json($voucherMetaMap ?? []);

    window.escapeHtml = window.escapeHtml || function(text) {
        if (text === undefined || text === null) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    };

    function selectVoucher(uniqueId) {
        // Highlight active row in master table
        document.querySelectorAll('.voucher-row').forEach(row => {
            row.classList.remove('bg-primary-50/80', 'ring-1', 'ring-primary-300', 'border-l-4', 'border-l-primary-600');
        });
        const selectedRow = document.querySelector(`.voucher-row[data-voucher-id="${uniqueId}"]`);
        if (selectedRow) {
            selectedRow.classList.add('bg-primary-50/80', 'ring-1', 'ring-primary-300', 'border-l-4', 'border-l-primary-600');
        }

        // Update detail header meta
        const meta = window.voucherMetaMap ? window.voucherMetaMap[uniqueId] : null;
        const items = window.voucherItemsMap ? window.voucherItemsMap[uniqueId] : [];

        if (meta) {
            document.getElementById('detailVoucherCode').textContent = meta.code;
            const badgeEl = document.getElementById('detailVoucherBadge');
            badgeEl.textContent = meta.type_label;
            badgeEl.className = 'text-[11px] font-bold px-2 py-0.5 rounded-full border ' + meta.badge_class;
            document.getElementById('detailVoucherMeta').textContent = `Ngày: ${meta.date} | Kho: ${meta.warehouse_name} | Đối tác: ${meta.partner}`;
        }

        // Render items in bottom table
        const tbody = document.getElementById('detailItemsBody');
        if (!tbody) return;

        if (!items || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="py-8 text-center text-neutral-400">
                        <iconify-icon icon="solar:box-minimalistic-broken" class="text-3xl text-neutral-300 mb-1 block mx-auto"></iconify-icon>
                        Chứng từ này không có dòng vật tư nào.
                    </td>
                </tr>
            `;
            document.getElementById('detailRowCount').textContent = '0 dòng';
            document.getElementById('detailTotalQty').textContent = 'Tổng SL: 0';
            document.getElementById('detailTotalAmount').textContent = 'Tổng tiền: 0đ';
            document.getElementById('tfootTotalQty').textContent = '0';
            document.getElementById('tfootTotalAmount').textContent = '0đ';
            return;
        }

        let totalQty = 0;
        let totalAmount = 0;
        let html = '';

        items.forEach((it, idx) => {
            const qty = Number(it.quantity) || 0;
            const price = Number(it.unit_price) || 0;
            const total = Number(it.total_price) || 0;

            totalQty += qty;
            totalAmount += total;

            const qtyFormatted = (qty % 1 === 0) 
                ? qty.toLocaleString('vi-VN') 
                : qty.toLocaleString('vi-VN', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
            const priceFormatted = price > 0 ? price.toLocaleString('vi-VN') + 'đ' : '0đ';
            const totalFormatted = total > 0 ? total.toLocaleString('vi-VN') + 'đ' : '0đ';

            html += `
                <tr class="hover:bg-neutral-50/70 transition-colors">
                    <td class="py-2 px-3 text-center text-neutral-500 font-medium">${it.stt || (idx + 1)}</td>
                    <td class="py-2 px-3 font-mono font-bold text-primary-700">${escapeHtml(it.sku || '—')}</td>
                    <td class="py-2 px-3 font-semibold text-neutral-800">${escapeHtml(it.name || '—')}</td>
                    <td class="py-2 px-3 text-center text-neutral-600 font-mono">${escapeHtml(it.dimensions || '—')}</td>
                    <td class="py-2 px-3 text-neutral-600">${escapeHtml(it.warehouse || '—')}</td>
                    <td class="py-2 px-3 text-center text-neutral-600">${escapeHtml(it.unit || '—')}</td>
                    <td class="py-2 px-3 text-right font-black text-emerald-700">${qtyFormatted}</td>
                    <td class="py-2 px-3 text-right text-neutral-600">${priceFormatted}</td>
                    <td class="py-2 px-3 text-right font-bold text-neutral-900">${totalFormatted}</td>
                    <td class="py-2 px-3 text-neutral-500 truncate max-w-xs" title="${escapeHtml(it.notes || '')}">${escapeHtml(it.notes || '—')}</td>
                </tr>
            `;
        });

        tbody.innerHTML = html;

        const totalQtyFmt = (totalQty % 1 === 0)
            ? totalQty.toLocaleString('vi-VN')
            : totalQty.toLocaleString('vi-VN', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
        const totalAmountFmt = totalAmount.toLocaleString('vi-VN') + 'đ';

        document.getElementById('detailRowCount').textContent = `${items.length} dòng`;
        document.getElementById('detailTotalQty').textContent = `Tổng SL: ${totalQtyFmt}`;
        document.getElementById('detailTotalAmount').textContent = `Tổng tiền: ${totalAmountFmt}`;
        document.getElementById('tfootTotalQty').textContent = totalQtyFmt;
        document.getElementById('tfootTotalAmount').textContent = totalAmountFmt;
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if($vouchers->count() > 0)
            selectVoucher('{{ $vouchers->first()->unique_id }}');
        @endif
    });
</script>
@endsection
