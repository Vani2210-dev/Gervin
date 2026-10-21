@extends('layout.layout')

@php
    $title = 'Ép ván';
    $subTitle = 'Quy trình';
@endphp

@section('content')
<style>
    /* ───────── PRESSING PAGE STYLES ───────── */
    .mode-tab-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 14px 18px;
        border-radius: 14px;
        font-weight: 700;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s ease;
        flex: 1;
        text-align: center;
    }
    .mode-tab-btn.active-order {
        background: #eef2ff;
        border-color: #6366f1;
        color: #3730a3;
        box-shadow: 0 4px 12px -2px rgba(99, 102, 241, 0.15);
    }
    .mode-tab-btn.active-stock {
        background: #ecfdf5;
        border-color: #10b981;
        color: #065f46;
        box-shadow: 0 4px 12px -2px rgba(16, 185, 129, 0.15);
    }
    .dark .mode-tab-btn.active-order {
        background: rgba(99, 102, 241, 0.15);
        border-color: #818cf8;
        color: #c7d2fe;
    }
    .dark .mode-tab-btn.active-stock {
        background: rgba(16, 185, 129, 0.15);
        border-color: #34d399;
        color: #a7f3d0;
    }
    .mode-tab-btn:not(.active-order):not(.active-stock) {
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #64748b;
    }
    .dark .mode-tab-btn:not(.active-order):not(.active-stock) {
        background: #1e293b;
        border-color: #334155;
        color: #94a3b8;
    }
    .mode-tab-btn:hover:not(.active-order):not(.active-stock) {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .dark .mode-tab-btn:hover:not(.active-order):not(.active-stock) {
        background: #243044;
        border-color: #475569;
    }

    #historyTable th, #historyTable td {
        text-align: left !important;
    }
</style>

<div class="-mt-4 mb-6">
    <p class="text-sm text-neutral-500 dark:text-neutral-400">Kiểm soát quy trình dán ép ván Acrylic / Laminate theo đơn hàng (trừ kho) hoặc ép dự trữ kho (+1 kho).</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    {{-- Left column: Control Card (col-span-8) --}}
    <div class="lg:col-span-8 flex flex-col gap-6">
        <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm">
            <div class="card-body p-4 sm:p-6 flex flex-col gap-5 sm:gap-6">

                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800 pb-3 sm:pb-4">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="lucide:layers" class="text-xl text-indigo-600 dark:text-indigo-400"></iconify-icon>
                        <span class="font-bold text-neutral-800 dark:text-neutral-100 text-base sm:text-lg">Kiểm soát ép ván dán mặt</span>
                    </div>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        <iconify-icon icon="lucide:zap" class="text-emerald-600 dark:text-emerald-400"></iconify-icon>
                        Quét tự động ghi nhận
                    </span>
                </div>

                {{-- Mode Selection Toggle --}}
                <div>
                    <label class="block text-xs font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider mb-2.5">
                        Chọn chế độ ép ván
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- Mode 1: Ép theo đơn hàng --}}
                        <button type="button" id="btnModeOrder" onclick="switchMode('order')"
                            class="mode-tab-btn active-order">
                            <div class="flex items-center gap-2 text-sm sm:text-base">
                                <iconify-icon icon="lucide:clipboard-check" class="text-xl"></iconify-icon>
                                <span>Ép theo đơn hàng</span>
                            </div>
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300">
                                Trừ 1 tấm trong kho
                            </span>
                        </button>

                        {{-- Mode 2: Ép dự trữ kho --}}
                        <button type="button" id="btnModeStock" onclick="switchMode('stock')"
                            class="mode-tab-btn">
                            <div class="flex items-center gap-2 text-sm sm:text-base">
                                <iconify-icon icon="lucide:archive" class="text-xl"></iconify-icon>
                                <span>Ép dự trữ kho</span>
                            </div>
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300">
                                +1 tấm vào kho
                            </span>
                        </button>
                    </div>
                </div>

                {{-- ═══════════════════════════════════════════ --}}
                {{-- SECTION 1: ÉP THEO ĐƠN HÀNG (TRỪ KHO)       --}}
                {{-- ═══════════════════════════════════════════ --}}
                <div id="sectionOrderMode" class="flex flex-col gap-4">
                    <div class="p-3.5 bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-800/50 rounded-xl text-xs text-indigo-900 dark:text-indigo-200 flex items-start gap-2.5">
                        <iconify-icon icon="lucide:info" class="text-base text-indigo-600 dark:text-indigo-400 mt-0.5 flex-shrink-0"></iconify-icon>
                        <div>
                            <strong>Chế độ ÉP THEO ĐƠN HÀNG:</strong> Quét mã QR tem tấm ván hoặc mã Lệnh sản xuất (LSX). Hệ thống tự động ghi nhận hoàn thành bước ép và <strong>trừ 1 tấm ván tương ứng trong kho</strong>.
                        </div>
                    </div>

                    {{-- QR scan input --}}
                    <div>
                        <label for="order_qr_input" class="block text-xs sm:text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">
                            Mã định danh tấm ván / Lệnh sản xuất (QR)
                        </label>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">
                            <div class="relative flex-grow">
                                <input type="text" id="order_qr_input" autofocus
                                    class="w-full pl-4 pr-10 py-3.5 sm:py-4 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base sm:text-lg font-mono font-bold"
                                    placeholder="Quét mã tấm ván hoặc LSX...">
                                <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none">
                                    <iconify-icon icon="lucide:qr-code" class="text-xl"></iconify-icon>
                                </span>
                            </div>
                            <button type="button" onclick="startScanning('order')"
                                class="w-full sm:w-auto px-5 py-3.5 sm:py-4 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-xl flex items-center justify-center gap-2 font-bold text-sm sm:text-base transition-all whitespace-nowrap shadow-md flex-shrink-0">
                                <iconify-icon icon="lucide:camera" class="text-xl"></iconify-icon>
                                <span>Quét Camera</span>
                            </button>
                        </div>

                        {{-- Test codes --}}
                        <div class="mt-2.5 flex flex-wrap items-center gap-1.5 text-xs text-neutral-500">
                            <span class="text-[11px]">Mã thử:</span>
                            <button type="button" onclick="fillOrderQr('LSX01-001')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-001</button>
                            <button type="button" onclick="fillOrderQr('LSX01-002')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-002</button>
                            <button type="button" onclick="fillOrderQr('LSX01')" class="px-2 py-0.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 rounded border border-indigo-200 dark:border-indigo-800 font-mono text-[10px] text-indigo-600 dark:text-indigo-400">LSX01 (Lô)</button>
                        </div>
                    </div>

                    {{-- Order Notes --}}
                    <div>
                        <label for="order_notes" class="block text-xs sm:text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Ghi chú xác minh (Tùy chọn)</label>
                        <div class="relative flex items-center">
                            <span class="absolute text-neutral-400 flex items-center justify-center pointer-events-none" style="left: 14px;">
                                <iconify-icon icon="lucide:file-text" class="text-lg"></iconify-icon>
                            </span>
                            <input type="text" id="order_notes"
                                class="w-full pr-4 py-2.5 sm:py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                                style="padding-left: 42px;"
                                placeholder="Nhiệt ép đạt chuẩn, keo PUR...">
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button type="button" id="btnSubmitOrder" onclick="submitOrderPress()"
                        class="mt-1 w-full py-3.5 sm:py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all transform hover:-translate-y-0.5 active:scale-[0.99] text-sm sm:text-base">
                        <iconify-icon icon="lucide:check-circle" class="text-xl"></iconify-icon>
                        <span>Xác nhận Ép theo đơn hàng (Trừ 1 tấm kho)</span>
                    </button>
                </div>

                {{-- ═══════════════════════════════════════════ --}}
                {{-- SECTION 2: ÉP DỰ TRỮ KHO (+1 TẤM VÀO KHO)   --}}
                {{-- ═══════════════════════════════════════════ --}}
                <div id="sectionStockMode" class="flex flex-col gap-4 hidden">
                    <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/50 rounded-xl text-xs text-emerald-900 dark:text-emerald-200 flex items-start gap-2.5">
                        <iconify-icon icon="lucide:info" class="text-base text-emerald-600 dark:text-emerald-400 mt-0.5 flex-shrink-0"></iconify-icon>
                        <div>
                            <strong>Chế độ ÉP DỰ TRỮ KHO:</strong> Quét hoặc nhập mã ván cần ép. Khi xác nhận, hệ thống sẽ ghi nhận hoàn thành ép và <strong>cộng 1 tấm ván vào kho</strong>.
                        </div>
                    </div>

                    {{-- Scan board QR / code --}}
                    <div>
                        <label for="stock_qr_input" class="block text-xs sm:text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">
                            Mã ván dán mặt ép dự trữ (QR / Barcode)
                        </label>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">
                            <div class="relative flex-grow">
                                <input type="text" id="stock_qr_input"
                                    class="w-full pl-4 pr-10 py-3 sm:py-3.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm sm:text-base font-mono font-bold"
                                    placeholder="Quét mã ván (VD: GV103.BA, GV01)...">
                                <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none">
                                    <iconify-icon icon="lucide:qr-code" class="text-xl"></iconify-icon>
                                </span>
                            </div>
                            <button type="button" onclick="startScanning('stock')"
                                class="w-full sm:w-auto px-5 py-3 sm:py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl flex items-center justify-center gap-2 font-bold text-sm sm:text-base transition-all whitespace-nowrap shadow-md flex-shrink-0">
                                <iconify-icon icon="lucide:camera" class="text-xl"></iconify-icon>
                                <span>Quét Camera</span>
                            </button>
                        </div>
                    </div>

                    {{-- Stock Notes --}}
                    <div>
                        <label for="stock_notes" class="block text-xs sm:text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">Ghi chú ép dự trữ (Tùy chọn)</label>
                        <div class="relative flex items-center">
                            <span class="absolute text-neutral-400 flex items-center justify-center pointer-events-none" style="left: 14px;">
                                <iconify-icon icon="lucide:file-text" class="text-lg"></iconify-icon>
                            </span>
                            <input type="text" id="stock_notes"
                                class="w-full pr-4 py-2.5 sm:py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm"
                                style="padding-left: 42px;"
                                placeholder="Ép ván dự trữ kho, keo PUR...">
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button type="button" id="btnSubmitStock" onclick="submitStockPress()"
                        class="mt-1 w-full py-3.5 sm:py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all transform hover:-translate-y-0.5 active:scale-[0.99] text-sm sm:text-base">
                        <iconify-icon icon="lucide:plus-circle" class="text-xl"></iconify-icon>
                        <span>+1 Tấm Vào Kho (Xác Nhận Ép Dự Trữ)</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Right column: Guide Card (col-span-4) --}}
    <div class="lg:col-span-4">
        {{-- Collapsible Guide on mobile --}}
        <details class="lg:hidden bg-indigo-600 text-white rounded-2xl p-4 shadow-sm mb-2">
            <summary class="font-bold flex items-center justify-between cursor-pointer list-none">
                <span class="flex items-center gap-2 text-sm"><iconify-icon icon="lucide:help-circle" class="text-lg"></iconify-icon> Xem hướng dẫn thao tác</span>
                <iconify-icon icon="lucide:chevron-down" class="text-lg transition-transform duration-200"></iconify-icon>
            </summary>
            <div class="mt-3 pt-3 border-t border-indigo-500/50 text-xs text-indigo-100 space-y-2">
                <p>1. <strong>Ép theo đơn hàng:</strong> Quét mã QR tấm ván -> Hoàn thành bước ép và <strong>-1 tấm trong kho</strong>.</p>
                <p>2. <strong>Ép dự trữ kho:</strong> Quét hoặc nhập mã ván -> Bấm xác nhận để <strong>+1 tấm vào kho</strong>.</p>
                <p>3. Xem toàn bộ lịch sử thao tác và biến động kho bên dưới.</p>
            </div>
        </details>

        {{-- Desktop Guide Card --}}
        <div class="hidden lg:flex text-white rounded-2xl p-6 shadow-md flex-col justify-between h-full min-h-[380px]" style="background-color: rgb(79, 70, 229);">
            <div>
                <h5 class="text-lg font-bold mb-4 flex items-center gap-2 text-white">
                    <iconify-icon icon="lucide:help-circle"></iconify-icon>
                    Hướng dẫn thao tác
                </h5>
                <ol class="space-y-4 text-sm text-indigo-100 list-decimal list-inside pl-1">
                    <li>
                        <strong>Ép theo đơn hàng:</strong><br>
                        <span class="text-xs text-indigo-200">Dùng súng quét hoặc camera quét mã QR tem tấm -> Hệ thống tự động ghi nhận và <strong>trừ 1 tấm trong kho</strong>.</span>
                    </li>
                    <li>
                        <strong>Ép dự trữ kho:</strong><br>
                        <span class="text-xs text-indigo-200">Quét hoặc nhập mã ván -> Bấm xác nhận để <strong>cộng 1 tấm vào kho</strong>.</span>
                    </li>
                    <li>
                        <strong>Tự động nhận diện:</strong><br>
                        <span class="text-xs text-indigo-200">Hệ thống tự động liên kết vật tư tấm ván trong kho và lưu vào thẻ kho (Sổ kho).</span>
                    </li>
                </ol>

                <div class="mt-6 pt-5 border-t border-indigo-500/50">
                    <span class="text-xs text-indigo-200 block font-bold uppercase tracking-wider mb-2">Quy tắc kho:</span>
                    <ul class="space-y-2 text-sm text-indigo-100 list-disc list-inside pl-1 text-xs">
                        <li><strong class="text-white">Ép theo đơn hàng</strong>: Trừ 1 tấm (-1)</li>
                        <li><strong class="text-white">Ép dự trữ kho</strong>: Cộng 1 tấm (+1)</li>
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
{{-- LỊCH SỬ ÉP VÁN                             --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="mt-8 card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">
    <div class="card-header border-b border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900/50 py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
        <h5 class="text-lg font-bold text-neutral-800 dark:text-neutral-100 mb-0 flex items-center gap-2">
            Lịch sử ép ván
        </h5>
        <div class="flex items-center flex-wrap gap-3">
            {{-- Bộ lọc ngày tháng chung --}}
            <x-date-filter :action="route('processes.pressing')" />

            <div class="flex items-center gap-1.5">
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
            </div>
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
                        <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">TÊN SẢN PHẨM / VÁN</th>
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
                            <span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1" style="background-color: rgba(99,102,241,.1); color: rgb(79,70,229); border: 1px solid rgba(99,102,241,.3);">
                                <iconify-icon icon="lucide:clipboard-check" class="text-xs"></iconify-icon> ÉP ĐƠN (-1 KHO)
                            </span>
                            @elseif($item->action === 'ép dự trữ')
                            <span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1" style="background-color: rgba(16,185,129,.1); color: rgb(5,150,105); border: 1px solid rgba(16,185,129,.3);">
                                <iconify-icon icon="lucide:archive" class="text-xs"></iconify-icon> ÉP DỰ TRỮ (+1 KHO)
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
                    <tr>
                        <td colspan="5" class="py-12 text-center text-neutral-400 dark:text-neutral-500">
                            <iconify-icon icon="lucide:inbox" class="text-4xl mb-2"></iconify-icon>
                            <p class="mb-0">Chưa có lịch sử ép ván nào được ghi nhận.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($history->hasPages())
        <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-end">
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

{{-- Toast Container --}}
<div id="toastContainer" class="fixed top-4 left-1/2 -translate-x-1/2 sm:left-auto sm:right-5 sm:translate-x-0 sm:bottom-5 sm:top-auto z-50 flex flex-col gap-2 w-[92vw] sm:w-auto max-w-md pointer-events-none"></div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
const PRESSING_ROUTE = '{{ route("processes.pressing.complete") }}';
const CSRF_TOKEN     = '{{ csrf_token() }}';

let currentMode       = 'order'; // 'order' | 'stock'
let html5QrCode       = null;
let scanTargetMode    = 'order';

// ─────────────────────────────────────────
// INIT
// ─────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Bind Enter on inputs
    const orderInput = document.getElementById('order_qr_input');
    if (orderInput) {
        orderInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitOrderPress();
            }
        });
    }

    const stockInput = document.getElementById('stock_qr_input');
    if (stockInput) {
        stockInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitStockPress();
            }
        });
    }
});

// ─────────────────────────────────────────
// MODE SWITCHING
// ─────────────────────────────────────────
function switchMode(mode) {
    currentMode = mode;
    const btnOrder = document.getElementById('btnModeOrder');
    const btnStock = document.getElementById('btnModeStock');
    const secOrder = document.getElementById('sectionOrderMode');
    const secStock = document.getElementById('sectionStockMode');

    if (mode === 'order') {
        btnOrder.className = 'mode-tab-btn active-order';
        btnStock.className = 'mode-tab-btn';
        secOrder.classList.remove('hidden');
        secStock.classList.add('hidden');
        setTimeout(() => document.getElementById('order_qr_input')?.focus(), 50);
    } else {
        btnOrder.className = 'mode-tab-btn';
        btnStock.className = 'mode-tab-btn active-stock';
        secOrder.classList.add('hidden');
        secStock.classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('stock_qr_input')?.focus();
        }, 50);
    }
}

function fillOrderQr(code) {
    const inp = document.getElementById('order_qr_input');
    if (inp) {
        inp.value = code;
        submitOrderPress();
    }
}

function sanitizeCode(code) {
    if (!code) return '';
    code = code.trim();
    if (code.includes('_')) {
        code = code.split('_')[0].trim();
    }
    return code;
}

// ─────────────────────────────────────────
// AUDIO FEEDBACK
// ─────────────────────────────────────────
function playBeep(isSuccess = true) {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        if (isSuccess) {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = "sine";
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.12);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.12);
        } else {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = "sawtooth";
            osc.frequency.setValueAtTime(300, ctx.currentTime);
            osc.frequency.setValueAtTime(200, ctx.currentTime + 0.1);
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.25);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.25);
        }
    } catch(e) {}
}

// ─────────────────────────────────────────
// TOAST NOTIFICATION
// ─────────────────────────────────────────
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    const isSuccess = type === 'success';

    toast.className = `p-4 rounded-xl shadow-lg border flex items-center gap-3 transition-all transform duration-300 translate-y-2 opacity-0 text-sm font-medium pointer-events-auto ${
        isSuccess
            ? 'bg-emerald-600 text-white border-emerald-500 shadow-emerald-900/20'
            : 'bg-rose-600 text-white border-rose-500 shadow-rose-900/20'
    }`;

    toast.innerHTML = `
        <iconify-icon icon="${isSuccess ? 'lucide:check-circle' : 'lucide:alert-circle'}" class="text-xl flex-shrink-0"></iconify-icon>
        <div class="flex-1">${message}</div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
    }, 10);

    setTimeout(() => {
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ─────────────────────────────────────────
// SUBMIT: ÉP THEO ĐƠN HÀNG (TRỪ 1 TẤM KHO)
// ─────────────────────────────────────────
async function submitOrderPress() {
    const input = document.getElementById('order_qr_input');
    const code = sanitizeCode(input.value);
    input.value = code;
    const notes = document.getElementById('order_notes').value.trim();

    if (!code) {
        playBeep(false);
        showToast('Vui lòng quét hoặc nhập mã QR tấm ván / Lệnh sản xuất!', 'error');
        input.focus();
        return;
    }

    const btn = document.getElementById('btnSubmitOrder');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<iconify-icon icon="lucide:loader-2" class="text-xl animate-spin"></iconify-icon><span>Đang ghi nhận...</span>`;

    try {
        const resp = await fetch(PRESSING_ROUTE, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                product_code: code,
                notes: notes,
                action_type: 'ép đơn'
            })
        });

        const res = await resp.json();
        btn.disabled = false;
        btn.innerHTML = originalHtml;

        if (res.success) {
            playBeep(true);
            showToast(res.message, 'success');
            input.value = '';
            document.getElementById('order_notes').value = '';
            input.focus();

            // Prepend row to history table
            if (res.data) {
                prependHistoryRow(res.data);
            }
        } else {
            playBeep(false);
            showToast(res.message || 'Có lỗi xảy ra, vui lòng thử lại.', 'error');
        }
    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        playBeep(false);
        showToast('Lỗi kết nối máy chủ!', 'error');
    }
}

// ─────────────────────────────────────────
// SUBMIT: ÉP DỰ TRỮ KHO (+1 TẤM VÀO KHO)
// ─────────────────────────────────────────
async function submitStockPress() {
    const qrInput = document.getElementById('stock_qr_input');
    let code = sanitizeCode(qrInput.value);
    qrInput.value = code;
    const notes = document.getElementById('stock_notes').value.trim();

    if (!code) {
        playBeep(false);
        showToast('Vui lòng quét hoặc nhập mã ván cần ép dự trữ!', 'error');
        qrInput.focus();
        return;
    }

    const btn = document.getElementById('btnSubmitStock');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<iconify-icon icon="lucide:loader-2" class="text-xl animate-spin"></iconify-icon><span>Đang cộng kho...</span>`;

    try {
        const resp = await fetch(PRESSING_ROUTE, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                product_code: code,
                notes: notes,
                action_type: 'ép dự trữ'
            })
        });

        const res = await resp.json();
        btn.disabled = false;
        btn.innerHTML = originalHtml;

        if (res.success) {
            playBeep(true);
            showToast(res.message, 'success');
            qrInput.value = '';
            document.getElementById('stock_notes').value = '';
            qrInput.focus();

            // Prepend row to history table
            if (res.data) {
                prependHistoryRow(res.data);
            }
        } else {
            playBeep(false);
            showToast(res.message || 'Có lỗi xảy ra, vui lòng thử lại.', 'error');
        }
    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
        playBeep(false);
        showToast('Lỗi kết nối máy chủ!', 'error');
    }
}

// ─────────────────────────────────────────
// DYNAMIC PREPEND TO HISTORY TABLE
// ─────────────────────────────────────────
function prependHistoryRow(data) {
    const tbody = document.getElementById('historyTableBody');
    if (!tbody) return;

    // Remove empty row if present
    const emptyRow = tbody.querySelector('tr td[colspan]');
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }

    const tr = document.createElement('tr');
    tr.className = 'hover:bg-neutral-50/50 dark:hover:bg-neutral-800/10 transition-colors bg-indigo-50/30 dark:bg-indigo-950/20';

    let actionBadge = '';
    if (data.action === 'ép đơn') {
        actionBadge = `<span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1" style="background-color: rgba(99,102,241,.1); color: rgb(79,70,229); border: 1px solid rgba(99,102,241,.3);"><iconify-icon icon="lucide:clipboard-check" class="text-xs"></iconify-icon> ÉP ĐƠN (-1 KHO)</span>`;
    } else if (data.action === 'ép dự trữ') {
        actionBadge = `<span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1" style="background-color: rgba(16,185,129,.1); color: rgb(5,150,105); border: 1px solid rgba(16,185,129,.3);"><iconify-icon icon="lucide:archive" class="text-xs"></iconify-icon> ÉP DỰ TRỮ (+1 KHO)</span>`;
    } else {
        actionBadge = `<span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1 bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-700">${data.action.toUpperCase()}</span>`;
    }

    tr.innerHTML = `
        <td class="py-3 px-4 font-mono text-xs">
            <span class="bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-700 px-2.5 py-1 rounded">
                ${data.product_code}
            </span>
        </td>
        <td class="py-3 px-4">${actionBadge}</td>
        <td class="py-3 px-4 text-neutral-800 dark:text-neutral-200 font-medium">${data.product_name || '—'}</td>
        <td class="py-3 px-4 text-neutral-600 dark:text-neutral-400">${data.notes || '—'}</td>
        <td class="py-3 px-4">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-400 flex items-center justify-center font-bold text-xs uppercase">
                    ${(data.operator || 'H').substring(0, 1)}
                </div>
                <div>
                    <span class="block font-medium text-neutral-800 dark:text-neutral-200 leading-none mb-1">${data.operator || 'Hệ thống'}</span>
                    <span class="block text-[10px] text-neutral-400 leading-none">${data.time || 'Vừa xong'}</span>
                </div>
            </div>
        </td>
    `;

    tbody.insertBefore(tr, tbody.firstChild);
}

// ─────────────────────────────────────────
// CAMERA SCANNER
// ─────────────────────────────────────────
function startScanning(mode = 'order') {
    scanTargetMode = mode;
    const modal = document.getElementById('scannerModal');
    modal.classList.remove('hidden');

    html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        (decodedText) => {
            const clean = sanitizeCode(decodedText);
            stopScanning();

            if (scanTargetMode === 'order') {
                const inp = document.getElementById('order_qr_input');
                if (inp) {
                    inp.value = clean;
                    submitOrderPress();
                }
            } else {
                const inp = document.getElementById('stock_qr_input');
                if (inp) {
                    inp.value = clean;
                    submitStockPress();
                }
            }
        },
        () => {}
    ).catch(err => {
        showToast("Không thể mở camera: " + err, "error");
        stopScanning();
    });
}

function stopScanning() {
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            html5QrCode.clear();
            document.getElementById('scannerModal').classList.add('hidden');
        }).catch(() => {
            document.getElementById('scannerModal').classList.add('hidden');
        });
    } else {
        document.getElementById('scannerModal').classList.add('hidden');
    }
}
</script>
@endsection
