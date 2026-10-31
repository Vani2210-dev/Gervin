@extends('layout.layout')

@php
    $title = 'Ép ván';
    $subTitle = 'Quy trình';
@endphp

@section('content')
<style>
    #historyTable th, #historyTable td {
        text-align: left !important;
    }
    #historyTable th.text-right, #historyTable td.text-right {
        text-align: right !important;
    }
</style>

<div class="-mt-4 mb-6">
    <p class="text-sm text-neutral-500 dark:text-neutral-400">Quét mã QR sản phẩm, tem tấm hoặc Lệnh sản xuất (LSX) để ghi nhận hoàn thành công đoạn ép ván.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    {{-- Left column: Control Card (col-span-8) --}}
    <div class="lg:col-span-8 flex flex-col gap-6">
        <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm">
            <div class="card-body p-4 sm:p-6 flex flex-col gap-4 sm:gap-6">

                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800 pb-4">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="lucide:layers" class="text-xl text-primary-600 dark:text-primary-400"></iconify-icon>
                        <span class="font-bold text-neutral-800 dark:text-neutral-100 text-base sm:text-lg">Kiểm soát ép ván dán mặt</span>
                    </div>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        <iconify-icon icon="lucide:zap" class="text-emerald-600 dark:text-emerald-400"></iconify-icon>
                        Quét tự động ghi nhận
                    </span>
                </div>

                {{-- Toggle Buttons (Complete vs Rollback) --}}
                <div class="flex gap-2 sm:gap-4">
                    <button type="button" id="btnTabComplete" onclick="switchActionType('complete')"
                        class="flex-1 py-3 px-4 border border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 rounded-xl flex items-center justify-center gap-2 font-semibold text-xs sm:text-sm transition-all duration-200">
                        <iconify-icon icon="lucide:check-circle" class="text-base sm:text-lg"></iconify-icon>
                        Quét Hoàn thành
                    </button>
                    <button type="button" id="btnTabRollback" onclick="switchActionType('rollback')"
                        class="flex-1 py-3 px-4 border border-neutral-200 dark:border-neutral-700 text-neutral-500 dark:text-neutral-400 rounded-xl flex items-center justify-center gap-2 font-semibold text-xs sm:text-sm transition-all duration-200">
                        <iconify-icon icon="lucide:refresh-cw" class="text-base sm:text-lg"></iconify-icon>
                        Quét Quay lại
                    </button>
                </div>

                {{-- Unified Scan Form --}}
                <div class="flex flex-col gap-4">
                    <div id="actionHintBox" class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/50 rounded-xl text-xs text-emerald-900 dark:text-emerald-200 flex items-start gap-2">
                        <iconify-icon id="actionHintIcon" icon="lucide:info" class="text-base text-emerald-600 dark:text-emerald-400 mt-0.5 flex-shrink-0"></iconify-icon>
                        <div id="actionHintText">
                            <strong>Chế độ HOÀN THÀNH:</strong> Quét mã QR tem tấm ván hoặc mã Lệnh sản xuất (LSX). Hệ thống tự động ghi nhận hoàn thành bước ép ván và cập nhật tiến độ sản xuất.
                        </div>
                    </div>

                    {{-- QR scan input --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="order_qr_input" class="block text-xs sm:text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                                Mã định danh tấm ván / Lệnh sản xuất / Sản phẩm (QR)
                            </label>
                            <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                <iconify-icon icon="lucide:check-circle-2" class="text-xs"></iconify-icon> Quét là lưu ngay
                            </span>
                        </div>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-4">
                            <div class="relative flex-grow">
                                <input type="text" id="order_qr_input" autofocus
                                    class="w-full pl-4 pr-10 py-3 sm:py-4 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-base sm:text-lg font-mono font-bold"
                                    placeholder="Quét mã tấm ván hoặc LSX...">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none">
                                    <iconify-icon icon="lucide:qr-code" class="text-xl"></iconify-icon>
                                </span>
                            </div>
                            <button type="button" onclick="startScanning()"
                                class="w-full sm:w-auto px-6 py-3 sm:py-4 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl flex items-center justify-center gap-2 font-bold text-sm sm:text-base transition-all whitespace-nowrap shadow-md flex-shrink-0">
                                <iconify-icon icon="lucide:camera" class="text-xl"></iconify-icon>
                                <span>Quét Camera</span>
                            </button>
                        </div>

                        {{-- Quick test codes --}}
                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-neutral-500">
                            <span class="text-[11px]">Mã thử:</span>
                            <button type="button" onclick="fillOrderQr('LSX01-001')" class="px-2 py-1 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-001</button>
                            <button type="button" onclick="fillOrderQr('LSX01-002')" class="px-2 py-1 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-002</button>
                            <button type="button" onclick="fillOrderQr('LSX01')" class="px-2 py-1 bg-primary-50 hover:bg-primary-100 dark:bg-primary-950/40 rounded border border-primary-200 dark:border-primary-800 font-mono text-[10px] text-primary-600 dark:text-primary-400">LSX01 (Lô)</button>
                        </div>
                    </div>

                    {{-- Order Notes --}}
                    <div>
                        <label for="order_notes" class="block text-xs sm:text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Ghi chú xác minh (Tùy chọn)</label>
                        <div class="relative flex items-center">
                            <span class="absolute text-neutral-400 flex items-center justify-center pointer-events-none" style="left: 14px;">
                                <iconify-icon icon="lucide:file-text" class="text-lg"></iconify-icon>
                            </span>
                            <input type="text" id="order_notes"
                                class="w-full pr-4 py-2 sm:py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-sm"
                                style="padding-left: 42px;"
                                placeholder="Nhiệt ép đạt chuẩn, keo PUR...">
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button type="button" id="btnSubmitOrder" onclick="submitOrderPress()"
                        class="mt-2 w-full py-3 sm:py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all transform hover:-translate-y-0.5 active:scale-[0.99] text-sm sm:text-base">
                        <iconify-icon id="btnSubmitOrderIcon" icon="lucide:check-circle" class="text-xl"></iconify-icon>
                        <span id="btnSubmitOrderText">Xác nhận Hoàn thành ép ván</span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Right column: Guide Card (col-span-4) --}}
    <div class="lg:col-span-4">
        {{-- Collapsible Guide on mobile --}}
        <details class="lg:hidden bg-primary-600 text-white rounded-2xl p-4 shadow-sm mb-2">
            <summary class="font-bold flex items-center justify-between cursor-pointer list-none">
                <span class="flex items-center gap-2 text-sm"><iconify-icon icon="lucide:help-circle" class="text-lg"></iconify-icon> Xem hướng dẫn thao tác</span>
                <iconify-icon icon="lucide:chevron-down" class="text-lg transition-transform duration-200"></iconify-icon>
            </summary>
            <div class="mt-4 pt-4 border-t border-primary-500/50 text-xs text-primary-100 space-y-2">
                <p>1. <strong>Quét Hoàn thành:</strong> Quét mã QR tem tấm hoặc mã LSX -> Ghi nhận hoàn thành công đoạn ép ván.</p>
                <p>2. <strong>Quét Quay lại:</strong> Chọn chế độ Quay lại để hoàn tác bước ép ván nếu cần chỉnh sửa.</p>
                <p>3. <strong>Quản lý kho:</strong> Tồn kho được quản lý tự động qua Phiếu Nhập / Xuất tại mục Đơn nhập kho.</p>
            </div>
        </details>

        {{-- Desktop Guide Card --}}
        <div class="hidden lg:flex text-white rounded-2xl p-6 shadow-md flex-col justify-between h-full min-h-[380px] bg-primary-600">
            <div>
                <h5 class="text-lg font-bold mb-4 flex items-center gap-2 text-white">
                    <iconify-icon icon="lucide:help-circle"></iconify-icon>
                    Hướng dẫn thao tác
                </h5>
                <ol class="space-y-4 text-sm text-primary-100 list-decimal list-inside pl-1">
                    <li>
                        <strong>Quét Hoàn thành:</strong><br>
                        <span class="text-xs text-primary-200">Dùng súng quét hoặc camera quét mã QR tem tấm / mã LSX -> Hệ thống tự động ghi nhận hoàn thành bước ép ván và chuyển tiến độ đơn hàng.</span>
                    </li>
                    <li>
                        <strong>Quét Quay lại:</strong><br>
                        <span class="text-xs text-primary-200">Bấm chọn Quét Quay lại để hoàn tác trạng thái ép ván của tấm ván nếu phát hiện lỗi cần ép lại.</span>
                    </li>
                    <li>
                        <strong>Tự động nhận diện:</strong><br>
                        <span class="text-xs text-primary-200">Hệ thống tự động liên kết mã tấm của Đơn Acrylic, Đơn Kính, Min Late và Lệnh ép ván xưởng.</span>
                    </li>
                </ol>

                <div class="mt-6 pt-4 border-t border-primary-500/50">
                    <span class="text-xs text-primary-200 block font-bold uppercase tracking-wider mb-2">Quản lý tồn kho:</span>
                    <p class="text-xs text-primary-100 leading-relaxed">
                        Tồn kho được theo dõi và cân đối tự động qua Phiếu Nhập Kho (PNK) và Phiếu Xuất Kho (PXK) tại mục Đơn nhập kho. Việc quét công đoạn chỉ ghi nhận tiến độ sản xuất.
                    </p>
                </div>
            </div>

            {{-- Operator Info --}}
            <div class="mt-6 pt-4 border-t border-primary-500/50 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary-700 border border-primary-400 flex items-center justify-center text-white text-base font-bold">
                    {{ strtoupper(substr(Auth::user()->name ?? 'H', 0, 2)) }}
                </div>
                <div>
                    <span class="text-[10px] text-primary-200 block font-bold leading-none mb-1">NGƯỜI VẬN HÀNH</span>
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
    <div class="card-header border-b border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900/50 py-4 px-6 flex items-center flex-wrap gap-4 justify-between">
        <h5 class="text-lg font-bold text-neutral-800 dark:text-neutral-100 mb-0 flex items-center gap-2">
            Lịch sử ép ván
        </h5>
        <div class="flex items-center flex-wrap gap-4">
            {{-- Bộ lọc ngày tháng chung --}}
            <x-date-filter :action="route('processes.pressing')" />

            <div class="flex items-center gap-2">
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
                    class="w-full pr-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
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
                            <span class="bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-700 px-2 py-1 rounded">
                                {{ $item->product_code }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @if($item->action === 'ép đơn' || $item->action === 'complete')
                            <span class="text-xs px-2 py-1 rounded-full font-semibold inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                                <iconify-icon icon="lucide:check-circle" class="text-xs"></iconify-icon> HOÀN THÀNH ÉP
                            </span>
                            @elseif($item->action === 'quay lại ép' || $item->action === 'rollback')
                            <span class="text-xs px-2 py-1 rounded-full font-semibold inline-flex items-center gap-1 bg-orange-50 text-orange-700 border border-orange-200 dark:bg-orange-950/40 dark:text-orange-300 dark:border-orange-800">
                                <iconify-icon icon="lucide:undo" class="text-xs"></iconify-icon> QUAY LẠI
                            </span>
                            @elseif($item->action === 'làm lệnh ép')
                            <span class="text-xs px-2 py-1 rounded-full font-semibold inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800">
                                <iconify-icon icon="lucide:file-plus" class="text-xs"></iconify-icon> LÀM LỆNH ÉP
                            </span>
                            @elseif($item->action === 'ép dự trữ')
                            <span class="text-xs px-2 py-1 rounded-full font-semibold inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                                <iconify-icon icon="lucide:layers" class="text-xs"></iconify-icon> ÉP VÁN
                            </span>
                            @else
                            <span class="text-xs px-2 py-1 rounded-full font-semibold inline-flex items-center gap-1 bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-700">
                                {{ strtoupper($item->action) }}
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-neutral-800 dark:text-neutral-200 font-medium">{{ $item->product_name }}</td>
                        <td class="py-3 px-4 text-neutral-600 dark:text-neutral-400">{{ $item->notes ?: '—' }}</td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-950/30 text-primary-700 dark:text-primary-400 flex items-center justify-center font-bold text-xs uppercase">
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
                            Chưa có dữ liệu lịch sử ép ván
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($history, 'links'))
        <div class="mt-6">
            {{ $history->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Camera Scanner Modal --}}
<div id="scannerModal" class="fixed inset-0 bg-neutral-900/80 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl w-full max-w-md mx-4 overflow-hidden shadow-2xl flex flex-col" style="max-height: 90vh;">
        <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-800 flex justify-between items-center bg-neutral-50 dark:bg-neutral-900/50 flex-shrink-0">
            <span class="font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                <iconify-icon icon="lucide:camera" class="text-primary-600 text-lg"></iconify-icon>
                Quét mã QR qua Camera
            </span>
            <button type="button" onclick="stopScanning()" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 p-2 rounded-lg">
                <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
            </button>
        </div>
        <div class="p-6 flex flex-col items-center justify-center gap-4 flex-1 overflow-y-auto">
            <div id="reader" class="w-full bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-xl overflow-hidden shadow-inner" style="min-height: 250px;"></div>
            <p class="text-xs text-neutral-400 dark:text-neutral-500 text-center">Di chuyển camera để mã QR lọt vào ô quét.</p>
        </div>
        <div class="px-6 py-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-end bg-neutral-50 dark:bg-neutral-900/50 flex-shrink-0">
            <button type="button" onclick="stopScanning()" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-xl text-xs font-semibold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800">
                Đóng
            </button>
        </div>
    </div>
</div>

{{-- Toast Container --}}
<div id="toastContainer" class="fixed top-4 left-1/2 -translate-x-1/2 sm:left-auto sm:right-6 sm:translate-x-0 sm:bottom-6 sm:top-auto z-50 flex flex-col gap-2 w-[92vw] sm:w-auto max-w-md pointer-events-none"></div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
const PRESSING_ROUTE = '{{ route("processes.pressing.complete") }}';
const CSRF_TOKEN     = '{{ csrf_token() }}';

let currentActionType = 'complete'; // 'complete' | 'rollback'
let html5QrCode       = null;

document.addEventListener('DOMContentLoaded', () => {
    const orderInput = document.getElementById('order_qr_input');
    if (orderInput) {
        orderInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitOrderPress();
            }
        });
    }
});

function switchActionType(type) {
    currentActionType = type;
    const btnComplete = document.getElementById('btnTabComplete');
    const btnRollback = document.getElementById('btnTabRollback');
    const hintBox = document.getElementById('actionHintBox');
    const hintIcon = document.getElementById('actionHintIcon');
    const hintText = document.getElementById('actionHintText');
    const submitBtn = document.getElementById('btnSubmitOrder');
    const submitBtnIcon = document.getElementById('btnSubmitOrderIcon');
    const submitBtnText = document.getElementById('btnSubmitOrderText');

    if (type === 'complete') {
        btnComplete.className = 'flex-1 py-3 px-4 border border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 rounded-xl flex items-center justify-center gap-2 font-semibold text-xs sm:text-sm transition-all duration-200';
        btnRollback.className = 'flex-1 py-3 px-4 border border-neutral-200 dark:border-neutral-700 text-neutral-500 dark:text-neutral-400 rounded-xl flex items-center justify-center gap-2 font-semibold text-xs sm:text-sm transition-all duration-200';
        
        hintBox.className = 'p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/50 rounded-xl text-xs text-emerald-900 dark:text-emerald-200 flex items-start gap-2';
        hintIcon.className = 'text-base text-emerald-600 dark:text-emerald-400 mt-0.5 flex-shrink-0';
        hintIcon.setAttribute('icon', 'lucide:info');
        hintText.innerHTML = '<strong>Chế độ HOÀN THÀNH:</strong> Quét mã QR tem tấm ván hoặc mã Lệnh sản xuất (LSX). Hệ thống tự động ghi nhận hoàn thành bước ép ván và cập nhật tiến độ sản xuất.';

        submitBtn.className = 'mt-2 w-full py-3 sm:py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all transform hover:-translate-y-0.5 active:scale-[0.99] text-sm sm:text-base';
        submitBtnIcon.setAttribute('icon', 'lucide:check-circle');
        submitBtnText.textContent = 'Xác nhận Hoàn thành ép ván';
    } else {
        btnComplete.className = 'flex-1 py-3 px-4 border border-neutral-200 dark:border-neutral-700 text-neutral-500 dark:text-neutral-400 rounded-xl flex items-center justify-center gap-2 font-semibold text-xs sm:text-sm transition-all duration-200';
        btnRollback.className = 'flex-1 py-3 px-4 border border-rose-500 bg-rose-50/50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400 rounded-xl flex items-center justify-center gap-2 font-semibold text-xs sm:text-sm transition-all duration-200';

        hintBox.className = 'p-4 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800/50 rounded-xl text-xs text-rose-900 dark:text-rose-200 flex items-start gap-2';
        hintIcon.className = 'text-base text-rose-600 dark:text-rose-400 mt-0.5 flex-shrink-0';
        hintIcon.setAttribute('icon', 'lucide:alert-circle');
        hintText.innerHTML = '<strong>Chế độ QUAY LẠI:</strong> Quét mã QR tem tấm ván để hoàn tác trạng thái ép ván về bước trước đó (dành cho trường hợp ép lỗi, sửa lại).';

        submitBtn.className = 'mt-2 w-full py-3 sm:py-4 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all transform hover:-translate-y-0.5 active:scale-[0.99] text-sm sm:text-base';
        submitBtnIcon.setAttribute('icon', 'lucide:refresh-cw');
        submitBtnText.textContent = 'Xác nhận Quay lại bước ép ván';
    }

    setTimeout(() => document.getElementById('order_qr_input')?.focus(), 50);
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
                action_type: currentActionType === 'rollback' ? 'rollback' : 'complete'
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

function prependHistoryRow(data) {
    const tbody = document.getElementById('historyTableBody');
    if (!tbody) return;

    const emptyRow = tbody.querySelector('tr td[colspan]');
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }

    const tr = document.createElement('tr');
    tr.className = 'hover:bg-neutral-50/50 dark:hover:bg-neutral-800/10 transition-colors bg-emerald-50/30 dark:bg-emerald-950/20';

    let actionBadge = '';
    if (data.action === 'ép đơn' || data.action === 'complete') {
        actionBadge = `<span class="text-xs px-2 py-1 rounded-full font-semibold inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800"><iconify-icon icon="lucide:check-circle" class="text-xs"></iconify-icon> HOÀN THÀNH ÉP</span>`;
    } else if (data.action === 'quay lại ép' || data.action === 'rollback') {
        actionBadge = `<span class="text-xs px-2 py-1 rounded-full font-semibold inline-flex items-center gap-1 bg-orange-50 text-orange-700 border border-orange-200 dark:bg-orange-950/40 dark:text-orange-300 dark:border-orange-800"><iconify-icon icon="lucide:undo" class="text-xs"></iconify-icon> QUAY LẠI</span>`;
    } else if (data.action === 'làm lệnh ép') {
        actionBadge = `<span class="text-xs px-2 py-1 rounded-full font-semibold inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800"><iconify-icon icon="lucide:file-plus" class="text-xs"></iconify-icon> LÀM LỆNH ÉP</span>`;
    } else {
        actionBadge = `<span class="text-xs px-2 py-1 rounded-full font-semibold inline-flex items-center gap-1 bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-700">${(data.action || '').toUpperCase()}</span>`;
    }

    tr.innerHTML = `
        <td class="py-3 px-4 font-mono text-xs">
            <span class="bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-700 px-2 py-1 rounded">
                ${data.product_code}
            </span>
        </td>
        <td class="py-3 px-4">${actionBadge}</td>
        <td class="py-3 px-4 text-neutral-800 dark:text-neutral-200 font-medium">${data.product_name || '—'}</td>
        <td class="py-3 px-4 text-neutral-600 dark:text-neutral-400">${data.notes || '—'}</td>
        <td class="py-3 px-4">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-950/30 text-primary-700 dark:text-primary-400 flex items-center justify-center font-bold text-xs uppercase">
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

function startScanning() {
    const modal = document.getElementById('scannerModal');
    modal.classList.remove('hidden');

    html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        (decodedText) => {
            const clean = sanitizeCode(decodedText);
            stopScanning();

            const inp = document.getElementById('order_qr_input');
            if (inp) {
                inp.value = clean;
                submitOrderPress();
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
