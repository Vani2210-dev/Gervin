@extends('layout.layout')

@php
    $title = 'Ép ván';
    $subTitle = 'Quy trình';
@endphp

@section('content')
<style>
    /* ───────── PRESSING PAGE STYLES ───────── */
    .purpose-btn {
        display: flex; align-items: center; justify-content: center;
        gap: 8px; padding: 10px 16px; border-radius: 10px;
        font-weight: 700; font-size: 13px; cursor: pointer;
        border: 2px solid transparent; transition: all .2s;
        flex: 1;
    }
    .purpose-btn.active-order {
        background: #ecfdf5; border-color: #10b981;
        color: #065f46;
    }
    .purpose-btn.active-stock {
        background: #eff6ff; border-color: #3b82f6;
        color: #1e40af;
    }
    .dark .purpose-btn.active-order {
        background: rgba(16,185,129,.12); border-color: #10b981;
        color: #6ee7b7;
    }
    .dark .purpose-btn.active-stock {
        background: rgba(59,130,246,.12); border-color: #3b82f6;
        color: #93c5fd;
    }
    .purpose-btn:not(.active-order):not(.active-stock) {
        background: #f5f5f5; border-color: #e5e7eb;
        color: #9ca3af;
    }
    #confirmScanBtn:disabled {
        opacity: 0.45 !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
        transform: none !important;
        box-shadow: none !important;
    }
    #historyTable th, #historyTable td {
        text-align: left !important;
    }
    /* Progress bar */
    .press-progress-bar {
        height: 5px; border-radius: 9999px;
        background: #e5e7eb; overflow: hidden;
        flex: 1; min-width: 60px;
    }
    .dark .press-progress-bar { background: #404040; }
    .press-progress-fill {
        height: 100%; border-radius: 9999px;
        background: linear-gradient(90deg, #6366f1, #818cf8);
        transition: width .5s ease;
    }
    .status-processing {
        background: rgba(250,204,21,.15); color: #854d0e;
        border: 1px solid rgba(250,204,21,.4);
    }
    .status-done {
        background: rgba(16,185,129,.12); color: #065f46;
        border: 1px solid rgba(16,185,129,.3);
    }
    .dark .status-processing { background: rgba(250,204,21,.1); color: #fde047; border-color: rgba(250,204,21,.3); }
    .dark .status-done       { background: rgba(74,222,128,.1); color: #4ade80; border-color: rgba(74,222,128,.3); }
    #pressOrdersBody tr { transition: background .15s; }
    #material_type {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 16px;
        appearance: none;
        padding-right: 40px;
    }
</style>

<div class="-mt-4 mb-6">
    <p class="text-sm text-neutral-500 dark:text-neutral-400">Kiểm soát quy trình lập lệnh và ép ván dán mặt Acrylic / Laminate theo đơn hàng hoặc dự trữ kho.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    {{-- Left column: Control Card (col-span-8) --}}
    <div class="lg:col-span-8 flex flex-col gap-6">
        <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm">
            <div class="card-body p-6 flex flex-col gap-6">

                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800 pb-4">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="lucide:layers" class="text-xl text-indigo-600"></iconify-icon>
                        <span class="font-bold text-neutral-800 dark:text-neutral-100">Kiểm soát ép ván dán mặt</span>
                    </div>
                </div>

                {{-- Step 1: Tạo lệnh ép --}}
                <div>
                    <p class="text-xs font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded-full bg-indigo-600 text-white flex items-center justify-center font-black text-[9px]">1</span>
                        Thiết lập lệnh ép mới
                    </p>

                    {{-- Toggle purpose --}}
                    <div class="flex gap-4 mb-4">
                        <button type="button" id="btnPurposeOrder" onclick="setPurpose('order')"
                            class="purpose-btn active-order">
                            <iconify-icon icon="lucide:clipboard-check" class="text-lg"></iconify-icon>
                            Ép Theo Đơn Hàng
                        </button>
                        <button type="button" id="btnPurposeStock" onclick="setPurpose('stock')"
                            class="purpose-btn">
                            <iconify-icon icon="lucide:archive" class="text-lg"></iconify-icon>
                            Ép Dự Trữ Kho
                        </button>
                    </div>

                    {{-- Material --}}
                    <div>
                        <label for="material_type" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Loại ván dán mặt Acrylic / Laminate</label>
                        <select id="material_type"
                            class="w-full pl-4 py-3.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 text-base focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Chọn loại ván --</option>
                            @foreach($woodBoardPrices as $typeName => $prices)
                                <optgroup label="{{ $typeName }}">
                                    @foreach($prices as $price)
                                        @php
                                            $val = $price->code . ($price->name ? ' - ' . $price->name : '') . ($price->thickness ? ' (' . $price->thickness . ')' : '');
                                        @endphp
                                        <option value="{{ $val }}">{{ $val }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="qty_needed" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Số lượng tấm cần ép</label>
                            <input type="number" id="qty_needed" min="1" value="1"
                                class="w-full px-4 py-3.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 text-base focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="order_notes" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Ghi chú lệnh</label>
                            <div class="relative flex items-center">
                                <span class="absolute text-neutral-400 flex items-center justify-center pointer-events-none" style="left: 14px;">
                                    <iconify-icon icon="lucide:file-text" class="text-lg"></iconify-icon>
                                </span>
                                <input type="text" id="order_notes"
                                    class="w-full pr-4 py-3.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 text-base focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    style="padding-left: 42px;"
                                    placeholder="Cốt MDF thái xanh, dán keo PUR...">
                            </div>
                        </div>
                    </div>

                    <button type="button" id="createOrderBtn" onclick="createPressOrder()"
                        class="mt-4 w-full py-4 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all transform hover:-translate-y-0.5"
                        style="background-color: rgb(79, 70, 229);">
                        <iconify-icon icon="lucide:plus-circle" class="text-xl"></iconify-icon>
                        <span>Xác Nhận Tạo Lệnh Sản Xuất</span>
                    </button>
                </div>

                {{-- Divider --}}
                <div class="border-t border-neutral-100 dark:border-neutral-800"></div>

                {{-- Step 2: Quét QR --}}
                <div>
                    <p class="text-xs font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded-full bg-indigo-600 text-white flex items-center justify-center font-black text-[9px]">2</span>
                        Quét mã QR tấm ván để ghi nhận
                    </p>

                    {{-- Active order badge --}}
                    <div id="activeOrderBadge" class="hidden mb-4">
                        <div class="flex items-center gap-2 p-3 bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-800/50 rounded-xl">
                            <iconify-icon icon="lucide:link" class="text-indigo-500 text-base flex-shrink-0"></iconify-icon>
                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] text-indigo-400 font-bold block uppercase">Đang xử lý lệnh</span>
                                <span id="activeOrderLabel" class="text-xs font-bold text-indigo-700 dark:text-indigo-300 truncate block"></span>
                            </div>
                            <button type="button" onclick="clearActiveOrder()" class="ml-auto text-indigo-400 hover:text-red-500 flex-shrink-0">
                                <iconify-icon icon="lucide:x" class="text-sm"></iconify-icon>
                            </button>
                        </div>
                    </div>

                    {{-- QR input --}}
                    <div>
                        <label for="qr_input" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Mã định danh tấm ván / Lệnh sản xuất (QR)</label>
                        <div class="flex items-center gap-3">
                            <div class="flex-grow">
                                <input type="text" id="qr_input"
                                    class="w-full pl-4 pr-4 py-3.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base font-mono"
                                    placeholder="Quét mã tấm ván (VD: LLE-202610-01-01)...">
                            </div>
                            <button type="button" onclick="startScanning()"
                                class="px-5 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl flex items-center gap-2 font-semibold text-sm transition-colors whitespace-nowrap shadow-sm">
                                <iconify-icon icon="lucide:camera" class="text-base"></iconify-icon>
                            </button>
                        </div>

                        {{-- Test codes --}}
                        <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs text-neutral-500">
                            <span>Mã nhập thử:</span>
                            <button type="button" onclick="fillQr('LLE-202610-01-01')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LLE-202610-01-01</button>
                            <button type="button" onclick="fillQr('LLE-202610-01-02')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LLE-202610-01-02</button>
                            <button type="button" onclick="fillQr('LLE-202610-01')" class="px-2 py-0.5 bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-950/30 dark:hover:bg-indigo-900/40 rounded border border-indigo-200 dark:border-indigo-800/50 font-mono text-[10px] text-indigo-600 dark:text-indigo-400">LLE-202610-01 (lô)</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="scan_notes" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Ghi chú xác minh</label>
                            <div class="relative flex items-center">
                                <span class="absolute text-neutral-400 flex items-center justify-center pointer-events-none" style="left: 14px;">
                                    <iconify-icon icon="lucide:file-text" class="text-lg"></iconify-icon>
                                </span>
                                <input type="text" id="scan_notes"
                                    class="w-full pr-4 py-3.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base"
                                    style="padding-left: 42px;"
                                    placeholder="Nhiệt ép đạt chuẩn 180°C...">
                            </div>
                        </div>
                    </div>

                    <button type="button" id="confirmScanBtn" onclick="submitScan()" disabled
                        class="mt-4 w-full py-4 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all transform hover:-translate-y-0.5 opacity-50 cursor-not-allowed pointer-events-none"
                        style="background-color: rgb(79, 70, 229);">
                        <iconify-icon icon="lucide:check-circle" class="text-xl" id="confirmBtnIcon"></iconify-icon>
                        <span id="confirmBtnText">Ghi nhận quy trình dán ép hoàn tất</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Right column: Guide Card (col-span-4) --}}
    <div class="lg:col-span-4">
        <div class="text-white rounded-2xl p-6 shadow-md flex flex-col justify-between h-full min-h-[380px]" style="background-color: rgb(79, 70, 229);">
            <div>
                <h5 class="text-lg font-bold mb-4 flex items-center gap-2 text-white">
                    <iconify-icon icon="lucide:help-circle"></iconify-icon>
                    Hướng dẫn thao tác
                </h5>
                <ol class="space-y-3 text-sm text-indigo-100 list-decimal list-inside pl-1">
                    <li>Chọn mục đích ép: <strong>Theo đơn hàng</strong> hoặc <strong>Dự trữ kho</strong>.</li>
                    <li>Chọn loại ván, nhập số lượng và ghi chú lệnh.</li>
                    <li>Bấm <strong>"Xác nhận tạo lệnh"</strong>.</li>
                    <li>Click vào dòng lệnh bên dưới để chọn làm lệnh đang xử lý.</li>
                    <li>Quét mã QR từng tấm để ghi nhận tiến độ ép.</li>
                </ol>

                <div class="mt-6 pt-5 border-t border-indigo-500/50">
                    <span class="text-xs text-indigo-200 block font-bold uppercase tracking-wider mb-2">Quy trình chuẩn:</span>
                    <ul class="space-y-2 text-sm text-indigo-100 list-disc list-inside pl-1">
                        <li>Tạo lệnh ép (chọn loại ván)</li>
                        <li>Quét mã từng tấm sau khi ép xong</li>
                        <li>Lệnh tự hoàn tất khi đủ số lượng</li>
                    </ul>
                </div>
            </div>

            {{-- Operator Info --}}
            <div class="mt-6 pt-4 border-t border-indigo-500/50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-600 border border-indigo-400 flex items-center justify-center text-white text-base font-bold">
                    {{ strtoupper(substr(Auth::user()->name ?? 'H', 0, 2)) }}
                </div>
                <div>
                    <span class="text-[10px] text-indigo-200 block font-bold leading-none mb-1">NGƯỜI VẬN HÀNH</span>
                    <span class="text-sm font-bold block leading-none text-white">{{ Auth::user()->name ?? 'Hệ thống' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════ --}}
{{-- PRESS ORDERS MANAGEMENT TABLE              --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="mt-8 card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900/50 py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
        <h5 class="text-lg font-bold text-neutral-800 dark:text-neutral-100 mb-0 flex items-center gap-2">
            Quản lý lệnh ép &amp; tấm ván
        </h5>
        <span class="text-sm text-neutral-500">Số lệnh thực thi: <strong id="activeOrderCount" class="text-indigo-600 dark:text-indigo-400">0</strong></span>
    </div>

    <div class="card-body p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 whitespace-nowrap">
                        <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">MÃ LỆNH</th>
                        <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">DÒNG VẬT LIỆU</th>
                        <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">MỤC ĐÍCH</th>
                        <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">SỐ LƯỢNG CẦN</th>
                        <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">SẢN LƯỢNG ĐÃ ÉP</th>
                        <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">TRẠNG THÁI</th>
                        <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">GHI CHÚ</th>
                        <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400 text-right">HÀNH ĐỘNG</th>
                    </tr>
                </thead>
                <tbody id="pressOrdersBody" class="divide-y divide-neutral-100 dark:divide-neutral-800">
                    <tr id="emptyOrdersRow">
                        <td colspan="8" class="py-10 text-center text-neutral-400 dark:text-neutral-500">
                            Chưa có lệnh ép nào. Tạo lệnh mới từ form bên trên.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════ --}}
{{-- LỊCH SỬ QUY TRÌNH (server-side)            --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="mt-6 card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900/50 py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
        <h5 class="text-lg font-bold text-neutral-800 dark:text-neutral-100 mb-0 flex items-center gap-2">
            Lịch sử ép ván
        </h5>
        <div class="flex items-center flex-wrap gap-3">
            <span class="text-sm font-medium text-secondary-light mb-0">Hiển thị</span>
            <form method="GET" action="{{ route('processes.pressing') }}" id="perPageForm">
                <input type="hidden" name="search" value="{{ $search }}">
                <select name="per_page" class="form-select form-select-sm w-auto border border-neutral-200 dark:border-neutral-700 rounded-lg py-1 px-2 text-xs bg-transparent dark:text-neutral-300"
                    onchange="document.getElementById('perPageForm').submit()">
                    @foreach([15, 25, 50, 100] as $option)
                    <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                    @endforeach
                </select>
            </form>
            <form method="GET" action="{{ route('processes.pressing') }}" class="relative w-48 sm:w-56">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <span class="absolute top-1/2 -translate-y-1/2 text-neutral-400 flex items-center justify-center pointer-events-none" style="left: 10px;">
                    <iconify-icon icon="lucide:search" class="text-base"></iconify-icon>
                </span>
                <input type="text" name="search"
                    class="w-full pr-3 py-1.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    style="padding-left: 34px;"
                    placeholder="Tìm kiếm lịch sử..." value="{{ $search }}">
            </form>
        </div>
    </div>

    <div class="card-body p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm" id="historyTable">
                <thead>
                    <tr class="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 whitespace-nowrap">
                        <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">MÃ ĐỊNH DANH</th>
                        <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">HÀNH ĐỘNG</th>
                        <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">TÊN SẢN PHẨM</th>
                        <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">GHI CHÚ</th>
                        <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">NGƯỜI THAO TÁC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800" id="historyTableBody">
                    @forelse($history as $item)
                    <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/10 transition-colors">
                        <td class="py-3 px-4 font-mono text-xs">
                            <span class="bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-700 px-2.5 py-1 rounded">
                                {{ $item->product_code }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @if($item->action === 'ép đơn')
                            <span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1" style="background-color: rgba(16,185,129,.1); color: rgb(5,150,105); border: 1px solid rgba(16,185,129,.3);">
                                <iconify-icon icon="lucide:check-circle" class="text-xs"></iconify-icon> ÉP ĐƠN
                            </span>
                            @elseif($item->action === 'ép dự trữ')
                            <span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1" style="background-color: rgba(59,130,246,.1); color: rgb(37,99,235); border: 1px solid rgba(59,130,246,.3);">
                                <iconify-icon icon="lucide:archive" class="text-xs"></iconify-icon> ÉP DỰ TRỮ
                            </span>
                            @elseif($item->action === 'quay lại ép')
                            <span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1" style="background-color: rgba(249,115,22,.1); color: rgb(234,88,12); border: 1px solid rgba(249,115,22,.3);">
                                <iconify-icon icon="lucide:undo" class="text-xs"></iconify-icon> QUAY LẠI
                            </span>
                            @else
                            <span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1 bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-700">
                                {{ strtoupper($item->action) }}
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-neutral-800 dark:text-neutral-200 font-medium">{{ $item->product_name }}</td>
                        <td class="py-3 px-4 text-neutral-600 dark:text-neutral-400">{{ $item->notes ?: '—' }}</td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-400 flex items-center justify-center font-bold text-xs uppercase">
                                    {{ substr($item->operator, 0, 1) }}
                                </div>
                                <div>
                                    <span class="block font-medium text-neutral-800 dark:text-neutral-200 leading-none mb-1">{{ $item->operator }}</span>
                                    <span class="block text-[10px] text-neutral-400 leading-none">{{ $item->time }}</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="noHistoryRow">
                        <td colspan="5" class="py-8 text-center text-neutral-400 dark:text-neutral-500">
                            Chưa có lịch sử ép ván nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($history instanceof \Illuminate\Pagination\LengthAwarePaginator && $history->total() > $perPage)
        <div class="flex items-center justify-between flex-wrap gap-2 mt-6">
            <span class="text-secondary-light text-sm">
                Hiển thị {{ $history->firstItem() ?? 0 }} đến {{ $history->lastItem() ?? 0 }}
                trong tổng {{ $history->total() }} bản ghi lịch sử
            </span>
            {{ $history->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Camera Scanner Modal --}}
<div id="scannerModal" class="fixed inset-0 bg-neutral-900/80 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl w-full max-w-md mx-4 overflow-hidden shadow-2xl">
        <div class="px-5 py-4 border-b border-neutral-100 dark:border-neutral-800 flex justify-between items-center bg-neutral-50 dark:bg-neutral-900/50">
            <span class="font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                <iconify-icon icon="lucide:camera" class="text-indigo-600 text-lg"></iconify-icon>
                Quét mã QR qua Camera
            </span>
            <button type="button" onclick="stopScanning()" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 p-1 rounded-lg">
                <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
            </button>
        </div>
        <div class="p-6 flex flex-col items-center justify-center gap-4">
            <div id="reader" class="w-full bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-xl overflow-hidden shadow-inner" style="min-height: 250px;"></div>
            <p class="text-xs text-neutral-400 dark:text-neutral-500 text-center">Di chuyển camera để mã QR lọt vào ô quét.</p>
        </div>
    </div>
</div>

{{-- Sheet Codes Modal --}}
<div id="sheetCodesModal" class="fixed inset-0 bg-neutral-900/80 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl w-full max-w-lg mx-4 overflow-hidden shadow-2xl">
        <div class="px-5 py-4 border-b border-neutral-100 dark:border-neutral-800 flex justify-between items-center">
            <span class="font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                <iconify-icon icon="lucide:list" class="text-indigo-600 text-lg"></iconify-icon>
                Mã từng tấm trong lệnh
            </span>
            <button type="button" onclick="closeSheetModal()" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 p-1 rounded-lg">
                <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
            </button>
        </div>
        <div class="p-5 flex flex-col gap-3 max-h-[60vh] overflow-y-auto" id="sheetCodesContent">
        </div>
    </div>
</div>

{{-- Toast Container --}}
<div id="toastContainer" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
const PRESSING_ROUTE = '{{ route("processes.pressing.complete") }}';
const CSRF_TOKEN     = '{{ csrf_token() }}';

// ─────────────────────────────────────────
// STATE
// ─────────────────────────────────────────
let currentPurpose = "order";          // "order" | "stock"
let pressOrders    = [];               // in-memory list of pressing orders
let activeOrderId  = null;             // currently selected order id for scanning
let html5QrCode    = null;

const STORAGE_KEY  = "press_orders_v2";

// ─────────────────────────────────────────
// INIT
// ─────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
    loadFromStorage();
    renderOrdersTable();
    bindQrInput();
});

// ─────────────────────────────────────────
// PURPOSE SELECTION
// ─────────────────────────────────────────
function setPurpose(p) {
    currentPurpose = p;
    const btnO = document.getElementById("btnPurposeOrder");
    const btnS = document.getElementById("btnPurposeStock");

    btnO.className = "purpose-btn" + (p === "order" ? " active-order" : "");
    btnS.className = "purpose-btn" + (p === "stock" ? " active-stock" : "");
}

// ─────────────────────────────────────────
// CREATE PRESSING ORDER
// ─────────────────────────────────────────
function createPressOrder() {
    const material = document.getElementById("material_type").value.trim();
    const qty      = parseInt(document.getElementById("qty_needed").value) || 1;
    const notes    = document.getElementById("order_notes").value.trim();

    if (!material) {
        showToast("Vui lòng chọn loại ván dán mặt!", "error");
        return;
    }
    if (qty < 1) {
        showToast("Số lượng tấm phải ít nhất 1.", "error");
        return;
    }

    const now   = new Date();
    const stamp = now.toLocaleDateString("vi-VN", {day:"2-digit", month:"2-digit"}).replace("/","-");
    const seq   = pressOrders.filter(o => o.date === stamp).length + 1;
    const seqPad = String(seq).padStart(2, "0");
    const id    = `LLE-${stamp}-${seqPad}`;

    const order = {
        id,
        purpose:  currentPurpose,          // "order" | "stock"
        material,
        qty_needed: qty,
        qty_done:   0,
        notes,
        status:   "processing",            // "processing" | "done"
        date:     stamp,
        created:  now.toLocaleString("vi-VN"),
        scans:    []                        // individual scan logs
    };

    pressOrders.unshift(order);
    saveToStorage();
    renderOrdersTable();

    // reset form
    document.getElementById("material_type").value   = "";
    document.getElementById("qty_needed").value       = "1";
    document.getElementById("order_notes").value      = "";

    showToast(`Đã tạo lệnh ép <strong>${id}</strong> thành công!`, "success");

    // Auto-select as active order
    selectActiveOrder(id);
}

// ─────────────────────────────────────────
// ACTIVE ORDER (for scanning)
// ─────────────────────────────────────────
function selectActiveOrder(id) {
    activeOrderId = id;
    const order   = pressOrders.find(o => o.id === id);
    if (!order) return;

    const badge = document.getElementById("activeOrderBadge");
    const label = document.getElementById("activeOrderLabel");
    badge.classList.remove("hidden");
    label.textContent = `${order.id} — ${order.material} (${order.purpose === "order" ? "Ép đơn hàng" : "Ép dự trữ kho"})`;
    document.getElementById("qr_input").focus();
}

function clearActiveOrder() {
    activeOrderId = null;
    document.getElementById("activeOrderBadge").classList.add("hidden");
    document.getElementById("activeOrderLabel").textContent = "";
}

// ─────────────────────────────────────────
// QR INPUT BINDING
// ─────────────────────────────────────────
function bindQrInput() {
    const inp = document.getElementById("qr_input");
    inp.addEventListener("input", () => {
        const has = inp.value.trim().length > 0;
        document.getElementById("confirmScanBtn").disabled = !has;
    });
    inp.addEventListener("keydown", (e) => {
        if (e.key === "Enter" && inp.value.trim()) submitScan();
    });
}

function fillQr(code) {
    document.getElementById("qr_input").value = code;
    document.getElementById("confirmScanBtn").disabled = false;
    document.getElementById("qr_input").focus();
}

// ─────────────────────────────────────────
// SUBMIT SCAN → API
// ─────────────────────────────────────────
async function submitScan() {
    const code  = document.getElementById("qr_input").value.trim();
    const notes = document.getElementById("scan_notes").value.trim();

    if (!code) {
        showToast("Vui lòng nhập hoặc quét mã QR!", "error");
        return;
    }

    // Determine action_type
    let actionType;
    if (activeOrderId) {
        const order = pressOrders.find(o => o.id === activeOrderId);
        actionType  = order && order.purpose === "stock" ? "ép dự trữ" : "ép đơn";
    } else {
        // No active order: default to "ép đơn"
        actionType = "ép đơn";
    }

    // UI: loading state
    const btn = document.getElementById("confirmScanBtn");
    const savedHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<iconify-icon icon="lucide:loader-2" class="text-base animate-spin"></iconify-icon><span>Đang xử lý...</span>`;

    try {
        const resp = await fetch(PRESSING_ROUTE, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":  CSRF_TOKEN
            },
            body: JSON.stringify({ product_code: code, notes, action_type: actionType })
        });

        const res = await resp.json();
        btn.innerHTML = savedHTML;

        if (res.success) {
            showToast(res.message, "success");

            // Update active order progress
            if (activeOrderId) {
                const order = pressOrders.find(o => o.id === activeOrderId);
                if (order) {
                    order.qty_done  = Math.min(order.qty_done + 1, order.qty_needed);
                    order.scans.push({ code, notes, time: new Date().toLocaleString("vi-VN"), action: actionType });
                    if (order.qty_done >= order.qty_needed) {
                        order.status = "done";
                    }
                    saveToStorage();
                    renderOrdersTable();
                }
            }

            // Clear inputs
            document.getElementById("qr_input").value   = "";
            document.getElementById("scan_notes").value = "";
            btn.disabled = true;
            document.getElementById("qr_input").focus();

            // Prepend history row
            prependHistoryRow(res.data);

        } else {
            showToast(res.message || "Có lỗi xảy ra!", "error");
            btn.disabled = false;
        }
    } catch (err) {
        btn.innerHTML = savedHTML;
        btn.disabled  = false;
        showToast("Không thể kết nối máy chủ!", "error");
    }
}

// ─────────────────────────────────────────
// RENDER ORDERS TABLE
// ─────────────────────────────────────────
function renderOrdersTable() {
    const tbody = document.getElementById("pressOrdersBody");
    const empty = document.getElementById("emptyOrdersRow");

    // Remove old rows (keep empty row)
    Array.from(tbody.rows).forEach(r => {
        if (r.id !== "emptyOrdersRow") r.remove();
    });

    if (pressOrders.length === 0) {
        empty.style.display = "";
        document.getElementById("activeOrderCount").textContent = "0";
        return;
    }

    empty.style.display = "none";
    const processing = pressOrders.filter(o => o.status === "processing").length;
    document.getElementById("activeOrderCount").textContent = processing;

    pressOrders.forEach(order => {
        const pct     = order.qty_needed > 0 ? Math.round(order.qty_done / order.qty_needed * 100) : 0;
        const isDone  = order.status === "done";
        const isActive = order.id === activeOrderId;

        const purposeBadge = order.purpose === "order"
            ? `<span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                <iconify-icon icon="lucide:clipboard-check"></iconify-icon> ÉP ĐƠN</span>`
            : `<span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/50">
                <iconify-icon icon="lucide:archive"></iconify-icon> DỰ TRỮ</span>`;

        const statusBadge = isDone
            ? `<span class="status-done text-[10px] font-bold px-2 py-0.5 rounded inline-flex items-center gap-1">
                <iconify-icon icon="lucide:check-circle-2"></iconify-icon> HOÀN TẤT</span>`
            : `<span class="status-processing text-[10px] font-bold px-2 py-0.5 rounded inline-flex items-center gap-1">
                <iconify-icon icon="lucide:loader" class="animate-spin" style="animation-duration:2s"></iconify-icon> ĐANG XỬ LÝ</span>`;

        const rowBg = isActive ? "bg-indigo-50/60 dark:bg-indigo-950/10" : "hover:bg-neutral-50/50 dark:hover:bg-neutral-800/10";

        const tr = document.createElement("tr");
        tr.id = `order-row-${order.id}`;
        tr.className = `${rowBg} transition-colors cursor-pointer`;
        tr.onclick   = () => selectActiveOrder(order.id);
        tr.innerHTML = `
            <td class="py-3 px-4">
                <div class="flex items-center gap-1.5">
                    ${isActive ? `<div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse flex-shrink-0"></div>` : `<div class="w-2 h-2 rounded-full bg-transparent flex-shrink-0"></div>`}
                    <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400">${order.id}</span>
                </div>
            </td>
            <td class="py-3 px-4">
                <span class="text-sm text-neutral-700 dark:text-neutral-300">${order.material}</span>
            </td>
            <td class="py-3 px-4">${purposeBadge}</td>
            <td class="py-3 px-4">
                <span class="font-semibold text-neutral-800 dark:text-neutral-200 text-sm">${order.qty_needed} tấm</span>
            </td>
            <td class="py-3 px-4">
                <div class="flex items-center gap-3 min-w-[140px]">
                    <div class="press-progress-bar flex-1">
                        <div class="press-progress-fill" style="width:${pct}%"></div>
                    </div>
                    <span class="text-xs font-semibold text-neutral-600 dark:text-neutral-400 whitespace-nowrap">${order.qty_done} tấm \u00a0 ${pct}%</span>
                </div>
            </td>
            <td class="py-3 px-4">${statusBadge}</td>
            <td class="py-3 px-4 text-xs text-neutral-500 dark:text-neutral-400">${order.notes || "—"}</td>
            <td class="py-3 px-4 text-right" onclick="event.stopPropagation()">
                <div class="flex items-center justify-end gap-2">
                    <button type="button" onclick="openSheetModal('${order.id}')"
                        class="text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/20 dark:hover:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50 whitespace-nowrap transition-colors">
                        Mã từng tấm
                    </button>
                    <button type="button" onclick="deleteOrder('${order.id}')"
                        class="p-1.5 rounded-lg text-neutral-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors">
                        <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// ─────────────────────────────────────────
// DELETE ORDER
// ─────────────────────────────────────────
function deleteOrder(id) {
    if (!confirm("Xóa lệnh ép này khỏi danh sách?")) return;
    pressOrders = pressOrders.filter(o => o.id !== id);
    if (activeOrderId === id) clearActiveOrder();
    saveToStorage();
    renderOrdersTable();
    showToast("Đã xóa lệnh ép.", "success");
}

// ─────────────────────────────────────────
// SHEET CODES MODAL
// ─────────────────────────────────────────
function openSheetModal(id) {
    const order = pressOrders.find(o => o.id === id);
    if (!order) return;

    const content = document.getElementById("sheetCodesContent");

    if (order.scans.length === 0) {
        content.innerHTML = `
            <div class="py-8 text-center text-neutral-400">
                <iconify-icon icon="lucide:inbox" class="text-3xl opacity-40 block mx-auto mb-2"></iconify-icon>
                <span class="text-sm">Chưa có tấm nào được quét cho lệnh này.</span>
            </div>`;
    } else {
        content.innerHTML = order.scans.map((s, i) => `
            <div class="flex items-center gap-3 py-2.5 border-b border-neutral-100 dark:border-neutral-800 last:border-0">
                <span class="text-xs font-bold text-neutral-400 w-5 text-right">${i + 1}.</span>
                <span class="font-mono text-xs bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 px-2.5 py-1 rounded text-neutral-800 dark:text-neutral-200">${s.code}</span>
                <div class="ml-auto text-right">
                    <span class="text-[10px] text-neutral-400 block">${s.time}</span>
                    <span class="text-[10px] font-semibold ${s.action === "ép đơn" ? "text-emerald-600" : "text-blue-600"}">${s.action === "ép đơn" ? "Ép đơn" : "Ép dự trữ"}</span>
                </div>
            </div>
        `).join("");
    }

    document.getElementById("sheetCodesModal").classList.remove("hidden");
}

function closeSheetModal() {
    document.getElementById("sheetCodesModal").classList.add("hidden");
}

// ─────────────────────────────────────────
// PERSIST TO localStorage
// ─────────────────────────────────────────
function saveToStorage() {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(pressOrders));
    } catch(e) {}
}

function loadFromStorage() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (raw) pressOrders = JSON.parse(raw);
    } catch(e) { pressOrders = []; }
}

// ─────────────────────────────────────────
// HISTORY ROW PREPEND
// ─────────────────────────────────────────
function prependHistoryRow(data) {
    if (!data) return;
    const tbody = document.getElementById("historyTableBody");
    const noRow = tbody.querySelector("tr:only-child");

    let badge = "";
    if (data.action === "ép đơn") {
        badge = `<span class="bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1"><iconify-icon icon="lucide:check-circle" class="text-xs"></iconify-icon> ÉP ĐƠN</span>`;
    } else if (data.action === "ép dự trữ") {
        badge = `<span class="bg-blue-100 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/50 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1"><iconify-icon icon="lucide:archive" class="text-xs"></iconify-icon> ÉP DỰ TRỮ</span>`;
    } else if (data.action === "quay lại ép") {
        badge = `<span class="bg-amber-100 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1"><iconify-icon icon="lucide:undo" class="text-xs"></iconify-icon> QUAY LẠI</span>`;
    } else {
        badge = `<span class="bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 text-xs px-2.5 py-1 rounded-full font-semibold">${(data.action || "").toUpperCase()}</span>`;
    }

    const tr = document.createElement("tr");
    tr.className = "hover:bg-neutral-50/50 dark:hover:bg-neutral-800/10 transition-colors";
    tr.innerHTML = `
        <td class="py-3 px-4 font-mono text-xs">
            <span class="bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-700 px-2.5 py-1 rounded">${data.product_code}</span>
        </td>
        <td class="py-3 px-4">${badge}</td>
        <td class="py-3 px-4 text-neutral-800 dark:text-neutral-200 font-medium">${data.product_name}</td>
        <td class="py-3 px-4 text-neutral-500 dark:text-neutral-400 text-xs">${data.notes || "—"}</td>
        <td class="py-3 px-4">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-400 flex items-center justify-center font-bold text-xs uppercase">${(data.operator || "H").substring(0, 1)}</div>
                <div>
                    <span class="block font-medium text-neutral-800 dark:text-neutral-200 leading-none mb-0.5">${data.operator}</span>
                    <span class="block text-[10px] text-neutral-400 leading-none">${data.time}</span>
                </div>
            </div>
        </td>
    `;

    if (noRow && noRow.querySelector("td[colspan]")) {
        tbody.replaceChild(tr, noRow);
    } else {
        tbody.insertBefore(tr, tbody.firstChild);
    }
}

// ─────────────────────────────────────────
// CAMERA SCANNER
// ─────────────────────────────────────────
function startScanning() {
    document.getElementById("scannerModal").classList.remove("hidden");
    html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        (decoded) => {
            document.getElementById("qr_input").value = decoded;
            document.getElementById("confirmScanBtn").disabled = false;
            stopScanning();
            try {
                let ctx = new (window.AudioContext || window.webkitAudioContext)();
                let osc = ctx.createOscillator();
                osc.type = "sine";
                osc.frequency.setValueAtTime(880, ctx.currentTime);
                osc.connect(ctx.destination);
                osc.start(); osc.stop(ctx.currentTime + 0.08);
            } catch(e) {}
        },
        () => {}
    ).catch(err => {
        alert("Không thể khởi động camera: " + err);
        stopScanning();
    });
}

function stopScanning() {
    if (html5QrCode && html5QrCode.isScanning) {
        html5QrCode.stop()
            .then(() => document.getElementById("scannerModal").classList.add("hidden"))
            .catch(() => document.getElementById("scannerModal").classList.add("hidden"));
    } else {
        document.getElementById("scannerModal").classList.add("hidden");
    }
}

// ─────────────────────────────────────────
// TOAST
// ─────────────────────────────────────────
function showToast(message, type = "success") {
    const toast = document.createElement("div");
    toast.className = [
        "flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-semibold",
        "transition-all transform translate-y-2 opacity-0 duration-300",
        type === "success"
            ? "bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-400"
            : "bg-red-50 dark:bg-red-950/20 border-red-200 dark:border-red-800 text-red-800 dark:text-red-400"
    ].join(" ");
    toast.innerHTML = `
        <iconify-icon icon="${type === "success" ? "lucide:check-circle" : "lucide:alert-circle"}" class="text-lg flex-shrink-0"></iconify-icon>
        <span>${message}</span>
    `;
    document.getElementById("toastContainer").appendChild(toast);
    setTimeout(() => toast.classList.remove("translate-y-2", "opacity-0"), 10);
    setTimeout(() => { toast.classList.add("opacity-0", "translate-y-2"); setTimeout(() => toast.remove(), 300); }, 3500);
}
</script>

@endsection
