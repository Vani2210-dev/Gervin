@extends('layout.layout')

@php
    $title = 'Thiết bị quét QR';
    $subTitle = 'Cấu hình, Thiết bị & Nhật ký';

    $configCommands = $configCommands ?? \App\Models\QrConfigCommand::where('is_active', true)->orderBy('sort_order', 'asc')->get();
    if ($configCommands->isEmpty()) {
        $configCommands = \App\Models\QrConfigCommand::defaultCommands();
    }

    $currTab = $currTab ?? 'config';
@endphp

@section('content')
    <style>
        .status-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .status-success {
            background-color: rgba(16, 185, 129, 0.12);
            color: #065f46;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .dark .status-success {
            background-color: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border-color: rgba(16, 185, 129, 0.4);
        }
        .status-failed {
            background-color: rgba(239, 68, 68, 0.12);
            color: #991b1b;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .dark .status-failed {
            background-color: rgba(239, 68, 68, 0.2);
            color: #f87171;
            border-color: rgba(239, 68, 68, 0.4);
        }
        .status-duplicate {
            background-color: rgba(245, 158, 11, 0.12);
            color: #92400e;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .dark .status-duplicate {
            background-color: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
            border-color: rgba(245, 158, 11, 0.4);
        }
        .status-unmapped {
            background-color: rgba(107, 114, 128, 0.12);
            color: #374151;
            border: 1px solid rgba(107, 114, 128, 0.3);
        }
        .dark .status-unmapped {
            background-color: rgba(156, 163, 175, 0.2);
            color: #d1d5db;
            border-color: rgba(156, 163, 175, 0.4);
        }
    </style>

    <div class="-mt-4 mb-4">
        <p class="text-sm text-neutral-500 dark:text-neutral-400">Hệ thống quản lý máy quét mã QR Rakinda RK80ER: Cấu hình nhanh thiết bị, danh sách máy quét tại xưởng và nhật ký quét thời gian thực.</p>
    </div>

    {{-- 3 Tab Navigation Header --}}
    <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-neutral-200/80 dark:border-neutral-800 shadow-xs p-2 mb-6">
        <div class="flex items-center gap-2 overflow-x-auto">
            {{-- Tab 1: Cấu hình --}}
            <button type="button" onclick="switchQrTab('config')" id="tab-btn-config"
                class="flex items-center gap-2.5 px-5 py-3 rounded-xl font-bold text-xs sm:text-sm transition-all {{ $currTab === 'config' ? 'bg-primary-600 text-white shadow-sm shadow-primary-200' : 'bg-neutral-50 dark:bg-neutral-800 hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-600 dark:text-neutral-300' }}">
                <iconify-icon icon="solar:qr-code-bold-duotone" class="text-lg"></iconify-icon>
                <span>Cấu hình máy quét</span>
                <span id="tab-badge-config" class="px-2 py-0.5 rounded-full text-xs font-extrabold {{ $currTab === 'config' ? 'bg-white/20 text-white' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-200' }}">
                    {{ count($configCommands) }} mã QR
                </span>
            </button>

            {{-- Tab 2: Danh sách máy --}}
            <button type="button" onclick="switchQrTab('devices')" id="tab-btn-devices"
                class="flex items-center gap-2.5 px-5 py-3 rounded-xl font-bold text-xs sm:text-sm transition-all {{ $currTab === 'devices' ? 'bg-primary-600 text-white shadow-sm shadow-primary-200' : 'bg-neutral-50 dark:bg-neutral-800 hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-600 dark:text-neutral-300' }}">
                <iconify-icon icon="solar:devices-bold-duotone" class="text-lg"></iconify-icon>
                <span>Danh sách máy</span>
                <span id="tab-badge-devices" class="px-2 py-0.5 rounded-full text-xs font-extrabold {{ $currTab === 'devices' ? 'bg-white/20 text-white' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-200' }}">
                    {{ $devices->count() }} máy
                </span>
            </button>

            {{-- Tab 3: Nhật ký quét --}}
            <button type="button" onclick="switchQrTab('logs')" id="tab-btn-logs"
                class="flex items-center gap-2.5 px-5 py-3 rounded-xl font-bold text-xs sm:text-sm transition-all {{ $currTab === 'logs' ? 'bg-primary-600 text-white shadow-sm shadow-primary-200' : 'bg-neutral-50 dark:bg-neutral-800 hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-600 dark:text-neutral-300' }}">
                <iconify-icon icon="solar:history-bold-duotone" class="text-lg"></iconify-icon>
                <span>Nhật ký quét</span>
                <span id="tab-badge-logs" class="px-2 py-0.5 rounded-full text-xs font-extrabold {{ $currTab === 'logs' ? 'bg-white/20 text-white' : 'bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-200' }}">
                    {{ $logs->total() }} bản ghi
                </span>
            </button>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- TAB 1: CẤU HÌNH MÁY QUÉT QR                              --}}
    {{-- ======================================================== --}}
    <div id="tab-pane-config" class="{{ $currTab === 'config' ? '' : 'hidden' }} space-y-6">
        <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-2xl shadow-sm overflow-hidden">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-800 py-4 px-6 flex flex-wrap gap-3 justify-between items-center bg-white dark:bg-neutral-900/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl shrink-0">
                        <iconify-icon icon="solar:qr-code-bold-duotone"></iconify-icon>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-neutral-800 dark:text-neutral-100 m-0">Bộ mã QR Cấu hình Thiết bị quét</h5>
                        <span class="text-xs text-neutral-400">Dành cho Rakinda RK80ER & các đầu đọc QR kết nối Wi-Fi</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="openModal('editQrConfigModal')"
                            class="btn bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3.5 py-2 rounded-xl flex items-center gap-1.5 transition shadow-xs">
                        <iconify-icon icon="solar:pen-new-square-bold" class="text-base"></iconify-icon>
                        Sửa cấu hình mã QR
                    </button>
                    <a href="{{ route('processes.qr-scans.export-pdf') }}" target="_blank"
                       class="btn bg-rose-50 hover:bg-rose-100 dark:bg-rose-950 dark:hover:bg-rose-900 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-xs font-bold px-3.5 py-2 rounded-xl flex items-center gap-1.5 transition shadow-2xs">
                        <iconify-icon icon="solar:file-download-bold" class="text-base text-rose-600 dark:text-rose-400"></iconify-icon>
                        Xuất ra PDF
                    </a>
                </div>
            </div>

            {{-- Alert instruction banner --}}
            <div class="p-4 bg-amber-50 dark:bg-amber-950/20 border-b border-amber-200 dark:border-amber-800/40 flex items-start gap-3">
                <iconify-icon icon="solar:danger-triangle-bold" class="text-amber-600 text-2xl shrink-0 mt-0.5"></iconify-icon>
                <div class="text-xs text-amber-900 dark:text-amber-200">
                    <span class="font-extrabold uppercase tracking-wide block mb-1 text-sm text-amber-800 dark:text-amber-300">
                        Quét từ trên xuống để cấu hình Máy quét
                    </span>
                    <span class="text-neutral-600 dark:text-neutral-300 leading-relaxed block text-xs">
                        Đưa mắt quét qua lần lượt từng mã QR theo đúng trình tự từ <strong>Bước 1</strong> đến <strong>Bước {{ count($configCommands) }}</strong> từ trên xuống dưới. Sau khi quét xong, máy quét sẽ tự động kết nối Wi-Fi xưởng và gửi dữ liệu về máy chủ ERP.
                    </span>
                </div>
            </div>

            {{-- Dynamic Step QR Sequence List (Top-to-Bottom) --}}
            <div class="p-6 md:p-8 max-w-3xl mx-auto space-y-6">
                @foreach($configCommands as $idx => $item)
                    @php
                        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                            ->size(150)
                            ->margin(1)
                            ->generate($item['cmd']);
                        $qrSvg = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $qrSvg);
                    @endphp

                    <div class="relative bg-neutral-50/90 dark:bg-neutral-800/50 p-5 rounded-2xl border border-neutral-200/90 dark:border-neutral-700 shadow-xs flex flex-col md:flex-row items-center gap-6">
                        {{-- Step Counter Box --}}
                        <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white font-extrabold text-lg flex items-center justify-center shrink-0 shadow-sm shadow-indigo-300">
                            #{{ $item['step'] ?? ($idx + 1) }}
                        </div>

                        {{-- QR Code Display (High Contrast Frame) --}}
                        <div class="bg-white p-3 rounded-2xl border border-neutral-200 shadow-xs flex items-center justify-center shrink-0 hover:scale-105 transition-transform">
                            <div class="w-[140px] h-[140px] flex items-center justify-center">
                                {!! $qrSvg !!}
                            </div>
                        </div>

                        {{-- Step Description & Command --}}
                        <div class="flex-1 w-full text-center md:text-left">
                            <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 mb-1.5">
                                <span class="inline-flex items-center gap-1 text-xs font-extrabold px-3 py-1 rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                    <iconify-icon icon="solar:round-alt-arrow-down-bold"></iconify-icon>
                                    {{ $item['badge'] ?? ('Bước ' . ($idx + 1)) }}
                                </span>
                                <span class="text-xs font-mono text-neutral-400 font-semibold">
                                    Thứ tự: {{ $idx + 1 }}/{{ count($configCommands) }}
                                </span>
                            </div>

                            <h6 class="font-bold text-sm text-neutral-900 dark:text-neutral-100 mb-1">
                                {{ $item['title'] }}
                            </h6>
                            @if(!empty($item['desc']))
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-3">
                                    {{ $item['desc'] }}
                                </p>
                            @else
                                <div class="mb-2"></div>
                            @endif

                            {{-- Command box with quick copy and edit shortcut --}}
                            <div class="flex items-center justify-between bg-white dark:bg-neutral-900 px-3 py-2 rounded-xl border border-neutral-200 dark:border-neutral-700 text-left">
                                <code class="text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 truncate select-all flex-1" title="{{ $item['cmd'] }}">
                                    {{ $item['cmd'] }}
                                </code>
                                <div class="flex items-center gap-1.5 shrink-0 ml-2">
                                    <button type="button" onclick="copyQrCommand('{{ addslashes($item['cmd']) }}', this)"
                                            class="text-neutral-400 hover:text-indigo-600 p-1.5 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                                            title="Sao chép nội dung lệnh">
                                        <iconify-icon icon="solar:copy-bold" class="text-base"></iconify-icon>
                                    </button>
                                    <button type="button" onclick="openModal('editQrConfigModal')"
                                            class="text-neutral-400 hover:text-indigo-600 p-1.5 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                                            title="Chỉnh sửa cấu hình">
                                        <iconify-icon icon="solar:pen-new-square-bold" class="text-base"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Connector Arrow between steps --}}
                        @if(!$loop->last)
                            <div class="absolute -bottom-4 left-1/2 -translate-x-1/2 z-10 w-8 h-8 rounded-full bg-white dark:bg-neutral-900 border-2 border-indigo-200 dark:border-neutral-700 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-sm shadow-xs">
                                <iconify-icon icon="solar:arrow-down-bold"></iconify-icon>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- TAB 2: DANH SÁCH MÁY (KÈM CỘT MÃ QUÉT GẦN NHẤT)          --}}
    {{-- ======================================================== --}}
    <div id="tab-pane-devices" class="{{ $currTab === 'devices' ? '' : 'hidden' }} space-y-6">
        <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-2xl shadow-sm overflow-hidden">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-800 py-4 px-6 flex flex-wrap gap-3 justify-between items-center bg-white dark:bg-neutral-900/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl shrink-0">
                        <iconify-icon icon="solar:devices-bold-duotone"></iconify-icon>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-neutral-800 dark:text-neutral-100 m-0">Danh sách Thiết bị Quét QR</h5>
                        <span class="text-xs text-neutral-400">Quản lý định danh thiết bị, công đoạn sản xuất và mã quét gần nhất</span>
                    </div>
                </div>
                <span class="text-xs font-bold px-3 py-1.5 bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 rounded-xl">
                    Tổng số: {{ $devices->count() }} thiết bị
                </span>
            </div>

            <div class="p-4 bg-sky-50/60 dark:bg-sky-950/20 border-b border-sky-100 dark:border-sky-900/30 flex items-center gap-2.5 text-xs text-sky-800 dark:text-sky-300">
                <iconify-icon icon="solar:info-circle-bold" class="text-sky-600 text-lg shrink-0"></iconify-icon>
                <span>Hệ thống tự động đăng ký máy quét mới vào danh sách khi thiết bị gửi dữ liệu quét lần đầu tiên.</span>
            </div>

            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 whitespace-nowrap text-xs">
                                <th class="py-3.5 px-4 font-bold text-neutral-600 dark:text-neutral-400">MÃ THIẾT BỊ</th>
                                <th class="py-3.5 px-4 font-bold text-neutral-600 dark:text-neutral-400">TÊN THIẾT BỊ</th>
                                <th class="py-3.5 px-4 font-bold text-neutral-600 dark:text-neutral-400">CÔNG ĐOẠN</th>
                                <th class="py-3.5 px-4 font-bold text-neutral-600 dark:text-neutral-400">HÀNH ĐỘNG</th>
                                <th class="py-3.5 px-4 font-bold text-neutral-600 dark:text-neutral-400">NHÂN VIÊN PHỤ TRÁCH</th>
                                <th class="py-3.5 px-4 font-bold text-indigo-800 dark:text-indigo-300 bg-indigo-50/50 dark:bg-indigo-950/20">MÃ QUÉT GẦN NHẤT</th>
                                <th class="py-3.5 px-4 font-bold text-neutral-600 dark:text-neutral-400">TRẠNG THÁI</th>
                                <th class="py-3.5 px-4 font-bold text-neutral-600 dark:text-neutral-400 text-right">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                            @forelse($devices as $device)
                                @php
                                    $stepName = 'Chưa cấu hình';
                                    $stepIcon = 'lucide:help-circle';
                                    $stepBadge = 'bg-neutral-100 text-neutral-600 border-neutral-200';
                                    
                                    switch($device->process_step) {
                                        case 'cnc':
                                            $stepName = 'Cắt CNC';
                                            $stepIcon = 'lucide:scissors';
                                            $stepBadge = 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/50 dark:text-indigo-300';
                                            break;
                                        case 'pressing':
                                            $stepName = 'Ép ván dán mặt';
                                            $stepIcon = 'lucide:layers';
                                            $stepBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300';
                                            break;
                                        case 'edge_banding':
                                            $stepName = 'Dán cạnh';
                                            $stepIcon = 'lucide:brush';
                                            $stepBadge = 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/50 dark:text-sky-300';
                                            break;
                                        case 'finishing':
                                            $stepName = 'Làm đẹp';
                                            $stepIcon = 'lucide:sparkles';
                                            $stepBadge = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300';
                                            break;
                                        case 'qc':
                                            $stepName = 'QC (Kiểm soát)';
                                            $stepIcon = 'lucide:check-circle';
                                            $stepBadge = 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300';
                                            break;
                                    }

                                    $lastLog = $device->latestLog;
                                @endphp
                                <tr class="hover:bg-neutral-50/60 dark:hover:bg-neutral-800/20 transition-colors">
                                    {{-- ID --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap font-mono text-xs text-neutral-500 font-bold">
                                        #{{ $device->id }}
                                    </td>

                                    {{-- Tên thiết bị --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                                            <iconify-icon icon="solar:scanner-bold-duotone" class="text-indigo-600 text-lg"></iconify-icon>
                                            <span>{{ $device->name }}</span>
                                        </div>
                                        @if($device->notes)
                                            <span class="block text-[11px] text-neutral-400 mt-0.5 truncate max-w-xs" title="{{ $device->notes }}">
                                                {{ $device->notes }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Công đoạn --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $stepBadge }}">
                                            <iconify-icon icon="{{ $stepIcon }}" class="text-sm"></iconify-icon>
                                            {{ $stepName }}
                                        </span>
                                    </td>

                                    {{-- Hành động --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="font-mono text-xs font-bold text-neutral-700 dark:text-neutral-300 uppercase px-2 py-0.5 bg-neutral-100 dark:bg-neutral-800 rounded border border-neutral-200 dark:border-neutral-700">
                                            {{ $device->action_type ?: 'complete' }}
                                        </span>
                                    </td>

                                    {{-- Nhân viên phụ trách --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap text-xs text-neutral-700 dark:text-neutral-300 font-medium">
                                        <div class="flex items-center gap-1.5">
                                            <iconify-icon icon="solar:user-bold" class="text-neutral-400"></iconify-icon>
                                            <span>{{ $device->operator->name ?? 'Mặc định (Hệ thống)' }}</span>
                                        </div>
                                    </td>

                                    {{-- Cột mới: MÃ QUÉT GẦN NHẤT --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap bg-indigo-50/30 dark:bg-indigo-950/10">
                                        @if($lastLog)
                                            <div class="flex flex-col gap-1">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-mono text-xs font-bold text-neutral-800 dark:text-neutral-100 bg-white dark:bg-neutral-800 px-2 py-0.5 rounded border border-neutral-200 dark:border-neutral-700 shadow-2xs">
                                                        {{ $lastLog->barcode }}
                                                    </span>
                                                    @if($lastLog->status === 'success')
                                                        <span class="status-badge status-success">Thành công</span>
                                                    @elseif($lastLog->status === 'failed')
                                                        <span class="status-badge status-failed">Thất bại</span>
                                                    @elseif($lastLog->status === 'duplicate')
                                                        <span class="status-badge status-duplicate">Trùng lặp</span>
                                                    @else
                                                        <span class="status-badge status-unmapped">Chưa map</span>
                                                    @endif
                                                </div>
                                                <span class="text-[11px] text-neutral-400 flex items-center gap-1" title="{{ $lastLog->scanned_at ? $lastLog->scanned_at->format('d/m/Y H:i:s') : $lastLog->scanned_at_raw }}">
                                                    <iconify-icon icon="solar:clock-circle-bold" class="text-xs"></iconify-icon>
                                                    {{ $lastLog->scanned_at ? $lastLog->scanned_at->format('H:i d/m/Y') : $lastLog->scanned_at_raw }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-xs text-neutral-400 italic">Chưa có lượt quét</span>
                                        @endif
                                    </td>

                                    {{-- Trạng thái --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if($device->is_active)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 px-2.5 py-0.5 rounded-full">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Hoạt động
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-neutral-500 bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 px-2.5 py-0.5 rounded-full">
                                                Tạm dừng
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Thao tác --}}
                                    <td class="py-3.5 px-4 whitespace-nowrap text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button" 
                                                    onclick="openEditModal({{ json_encode($device) }})"
                                                    class="p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 dark:hover:bg-indigo-950 rounded-lg transition-colors"
                                                    title="Sửa cấu hình thiết bị">
                                                <iconify-icon icon="lucide:edit-3" class="text-base"></iconify-icon>
                                            </button>
                                            <form action="{{ route('processes.qr-scans.delete-device', $device->id) }}" method="POST" onsubmit="return confirm('Xóa thiết bị quét này khỏi hệ thống?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950 rounded-lg transition-colors" title="Xóa máy quét">
                                                    <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-neutral-400 dark:text-neutral-500">
                                        <iconify-icon icon="solar:devices-bold-duotone" class="text-4xl mb-2 block mx-auto text-neutral-300"></iconify-icon>
                                        <p class="text-sm font-semibold m-0">Chưa có thiết bị nào trong danh sách.</p>
                                        <p class="text-xs text-neutral-400 mt-1">Thiết bị sẽ tự động xuất hiện ở đây ngay khi quét mã QR đầu tiên.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- TAB 3: NHẬT KÝ QUÉT                                      --}}
    {{-- ======================================================== --}}
    <div id="tab-pane-logs" class="{{ $currTab === 'logs' ? '' : 'hidden' }} space-y-6">
        <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-2xl shadow-sm overflow-hidden">
            <div class="card-header border-b border-neutral-200 dark:border-neutral-800 py-4 px-6 flex flex-wrap gap-3 items-center justify-between bg-white dark:bg-neutral-900/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl shrink-0">
                        <iconify-icon icon="solar:history-bold-duotone"></iconify-icon>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-neutral-800 dark:text-neutral-100 m-0">Nhật ký Quét QR Thời gian thực</h5>
                        <span class="text-xs text-neutral-400">Theo dõi toàn bộ lịch sử quét mã của các thiết bị tại xưởng</span>
                    </div>
                </div>
                
                {{-- Filter Forms --}}
                <form method="GET" action="{{ route('processes.qr-scans') }}" class="flex items-center flex-wrap gap-2">
                    <input type="hidden" name="tab" value="logs">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">

                    {{-- Search --}}
                    <div class="relative w-44 sm:w-56">
                        <span class="absolute top-1/2 -translate-y-1/2 text-neutral-400 flex items-center justify-center pointer-events-none" style="left: 10px;">
                            <iconify-icon icon="lucide:search" class="text-base"></iconify-icon>
                        </span>
                        <input type="text" name="search"
                            class="w-full pr-3 py-1.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 text-xs focus:outline-none focus:ring-2 focus:ring-primary-500"
                            style="padding-left: 34px;"
                            placeholder="Tìm mã sản phẩm..." value="{{ $search }}">
                    </div>

                    {{-- Status Filter --}}
                    <select name="status" onchange="this.form.submit()"
                            class="form-select form-select-sm w-auto border border-neutral-200 dark:border-neutral-700 rounded-lg py-1 px-2.5 text-xs bg-transparent dark:text-neutral-300">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="success" {{ $statusFilter === 'success' ? 'selected' : '' }}>Thành công</option>
                        <option value="failed" {{ $statusFilter === 'failed' ? 'selected' : '' }}>Thất bại</option>
                        <option value="duplicate" {{ $statusFilter === 'duplicate' ? 'selected' : '' }}>Trùng lặp</option>
                        <option value="unmapped" {{ $statusFilter === 'unmapped' ? 'selected' : '' }}>Chưa cấu hình</option>
                    </select>

                    {{-- Device Filter --}}
                    <select name="device_id" onchange="this.form.submit()"
                            class="form-select form-select-sm w-auto border border-neutral-200 dark:border-neutral-700 rounded-lg py-1 px-2.5 text-xs bg-transparent dark:text-neutral-300">
                        <option value="">-- Tất cả máy quét --</option>
                        @foreach($devices as $dev)
                            <option value="{{ $dev->id }}" {{ $deviceFilter == $dev->id ? 'selected' : '' }}>{{ $dev->name }}</option>
                        @endforeach
                    </select>

                    @if($search || $statusFilter || $deviceFilter)
                        <a href="{{ route('processes.qr-scans', ['tab' => 'logs']) }}" class="text-xs text-neutral-500 hover:text-red-600 px-2 py-1 rounded">
                            Xóa lọc
                        </a>
                    @endif
                </form>
            </div>
            
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm" id="logsTable">
                        <thead>
                            <tr class="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 whitespace-nowrap text-xs">
                                <th class="py-3.5 px-4 font-bold text-neutral-600 dark:text-neutral-400">THỜI GIAN</th>
                                <th class="py-3.5 px-4 font-bold text-neutral-600 dark:text-neutral-400">MÁY QUÉT</th>
                                <th class="py-3.5 px-4 font-bold text-neutral-600 dark:text-neutral-400">MÃ SẢN PHẨM (QR)</th>
                                <th class="py-3.5 px-4 font-bold text-neutral-600 dark:text-neutral-400">TRẠNG THÁI</th>
                                <th class="py-3.5 px-4 font-bold text-neutral-600 dark:text-neutral-400">MÔ TẢ KẾT QUẢ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                            @forelse($logs as $log)
                                <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/10 transition-colors">
                                    <td class="py-3.5 px-4 whitespace-nowrap text-xs text-neutral-500">
                                        {{ $log->scanned_at ? $log->scanned_at->format('d-m-Y H:i:s') : $log->scanned_at_raw }}
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-neutral-800 dark:text-neutral-200">
                                        {{ $log->device->name ?? 'Máy quét #' . $log->device_id }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-xs">
                                        <span class="bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-700 px-2 py-0.5 rounded">
                                            {{ $log->barcode }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($log->status === 'success')
                                            <span class="status-badge status-success">
                                                <iconify-icon icon="lucide:check-circle"></iconify-icon> Thành công
                                            </span>
                                        @elseif($log->status === 'failed')
                                            <span class="status-badge status-failed">
                                                <iconify-icon icon="lucide:alert-circle"></iconify-icon> Thất bại
                                            </span>
                                        @elseif($log->status === 'duplicate')
                                            <span class="status-badge status-duplicate">
                                                <iconify-icon icon="lucide:copy"></iconify-icon> Trùng lặp
                                            </span>
                                        @else
                                            <span class="status-badge status-unmapped">
                                                <iconify-icon icon="lucide:help-circle"></iconify-icon> Chưa cấu hình
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-neutral-600 dark:text-neutral-400">
                                        {{ $log->message ?: '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-neutral-400 dark:text-neutral-500">
                                        Không có nhật ký quét nào phù hợp với bộ lọc.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($logs->hasPages())
                    <div class="p-4 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-between flex-wrap gap-2">
                        <span class="text-secondary-light text-sm">
                            Hiển thị {{ $logs->firstItem() ?? 0 }} đến {{ $logs->lastItem() ?? 0 }}
                            trong tổng {{ $logs->total() }} bản ghi nhật ký
                        </span>
                        {{ $logs->appends(array_merge(request()->query(), ['tab' => 'logs']))->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- MODAL: CHỈNH SỬA BỘ MÃ QR CẤU HÌNH                       --}}
    {{-- ======================================================== --}}
    <x-modal name="editQrConfigModal" maxWidth="3xl">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between bg-neutral-50 dark:bg-neutral-900/50 rounded-t-xl">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shrink-0">
                    <iconify-icon icon="solar:pen-new-square-bold-duotone"></iconify-icon>
                </div>
                <div>
                    <h5 class="text-base font-bold text-neutral-800 dark:text-neutral-100 m-0">Chỉnh sửa Bộ mã QR Cấu hình</h5>
                    <span class="text-xs text-neutral-400">Sửa đổi các câu lệnh cấu hình máy quét và lưu lại vào hệ thống</span>
                </div>
            </div>
            <button type="button" onclick="closeModal('editQrConfigModal')" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 text-xl leading-none">&times;</button>
        </div>

        <form method="POST" action="{{ route('processes.qr-scans.update-config-commands') }}" id="editConfigCommandsForm">
            @csrf
            <div class="p-6 space-y-4 max-h-[68vh] overflow-y-auto" id="config-commands-container">
                @foreach($configCommands as $idx => $cmd)
                    <div class="command-row bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-200 dark:border-neutral-700 p-4 rounded-xl relative">
                        <div class="flex items-center justify-between mb-3">
                            <span class="step-label font-extrabold text-xs text-indigo-600 dark:text-indigo-400 uppercase flex items-center gap-1">
                                <iconify-icon icon="solar:round-alt-arrow-down-bold"></iconify-icon>
                                Bước <span class="step-num">{{ $idx + 1 }}</span>
                            </span>
                            <button type="button" onclick="removeCommandRow(this)" class="text-neutral-400 hover:text-rose-600 text-xs font-semibold flex items-center gap-1 transition">
                                <iconify-icon icon="lucide:trash-2" class="text-sm"></iconify-icon> Xóa bước này
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                            <div class="md:col-span-4">
                                <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 mb-1">Nhãn / Huy hiệu</label>
                                <input type="text" name="commands[{{ $idx }}][badge]" value="{{ $cmd['badge'] ?? ('Bước ' . ($idx + 1)) }}"
                                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg text-xs bg-white dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100"
                                       placeholder="Ví dụ: Bước 1: Reset">
                            </div>

                            <div class="md:col-span-8">
                                <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 mb-1">Tiêu đề bước <span class="text-rose-500">*</span></label>
                                <input type="text" name="commands[{{ $idx }}][title]" value="{{ $cmd['title'] }}" required
                                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg text-xs bg-white dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 font-semibold"
                                       placeholder="Ví dụ: Khôi phục cài đặt gốc">
                            </div>

                            <div class="md:col-span-12">
                                <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 mb-1">Nội dung mã lệnh QR (&lt;cmd&gt;...) <span class="text-rose-500">*</span></label>
                                <input type="text" name="commands[{{ $idx }}][cmd]" value="{{ $cmd['cmd'] }}" required
                                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg text-xs bg-white dark:bg-neutral-800 text-indigo-600 dark:text-indigo-400 font-mono font-bold"
                                       placeholder="Ví dụ: <cmd>rk_reset">
                            </div>

                            <div class="md:col-span-12">
                                <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 mb-1">Mô tả / Hướng dẫn thêm</label>
                                <input type="text" name="commands[{{ $idx }}][desc]" value="{{ $cmd['desc'] ?? '' }}"
                                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg text-xs bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300"
                                       placeholder="Ví dụ: Đặt lại toàn bộ thông số máy quét về mặc định ban đầu">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-4 border-t border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 flex flex-wrap items-center justify-between gap-3">
                <button type="button" onclick="addNewCommandRow()"
                        class="btn bg-white hover:bg-neutral-100 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-200 border border-neutral-300 dark:border-neutral-700 text-xs font-bold px-3 py-2 rounded-xl flex items-center gap-1.5 shadow-2xs">
                    <iconify-icon icon="solar:add-circle-bold" class="text-base text-indigo-600"></iconify-icon> Thêm bước cấu hình mới
                </button>

                <div class="flex items-center gap-2.5">
                    <button type="button" onclick="confirmResetDefaults()"
                            class="px-3 py-2 text-rose-600 hover:text-rose-800 dark:hover:text-rose-400 text-xs font-semibold rounded-xl hover:bg-rose-50 dark:hover:bg-rose-950/30 transition">
                        Khôi phục mặc định gốc
                    </button>
                    <button type="button" onclick="closeModal('editQrConfigModal')"
                            class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 rounded-xl font-bold text-xs hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                        Hủy
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-sm transition">
                        Lưu cấu hình
                    </button>
                </div>
            </div>
        </form>

        <form id="resetConfigForm" method="POST" action="{{ route('processes.qr-scans.reset-config-commands') }}" class="hidden">
            @csrf
        </form>
    </x-modal>

    {{-- Edit Device Modal --}}
    <x-modal name="editDeviceModal" maxWidth="lg">
        <div class="px-5 py-4 border-b border-neutral-100 dark:border-neutral-800 flex justify-between items-center bg-neutral-50 dark:bg-neutral-900/50 rounded-t-xl">
            <span class="font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                <iconify-icon icon="lucide:edit-3" class="text-indigo-600 text-lg"></iconify-icon>
                Cấu hình Thiết bị quét
            </span>
            <button type="button" onclick="closeModal('editDeviceModal')" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 p-1 rounded-lg transition-colors">
                <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="editDeviceForm" method="POST" action="">
            @csrf
            <div class="p-6 flex flex-col gap-4">
                <!-- Read-only Device ID -->
                <div>
                    <label class="block text-xs font-bold text-neutral-400 dark:text-neutral-500 uppercase mb-1">Mã thiết bị (Device ID)</label>
                    <input type="text" id="modal_device_id" readonly
                           class="w-full px-4 py-2 border border-neutral-200 dark:border-neutral-800 rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-500 font-mono text-sm cursor-not-allowed">
                </div>

                <!-- Name -->
                <div>
                    <label for="modal_name" class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase mb-1">Tên máy quét</label>
                    <input type="text" name="name" id="modal_name" required
                           class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Process Step Mapping -->
                    <div>
                        <label for="modal_process_step" class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase mb-1">Công đoạn sản xuất</label>
                        <select name="process_step" id="modal_process_step" onchange="updateActionPlaceholder()"
                                class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Chọn công đoạn --</option>
                            <option value="cnc">Cắt CNC</option>
                            <option value="pressing">Ép ván dán mặt</option>
                            <option value="edge_banding">Dán cạnh</option>
                            <option value="finishing">Làm đẹp</option>
                            <option value="qc">Kiểm soát (QC)</option>
                        </select>
                    </div>

                    <!-- Action Type -->
                    <div>
                        <label for="modal_action_type" class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase mb-1">Hành động ghi nhận</label>
                        <input type="text" name="action_type" id="modal_action_type"
                               class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <span id="action_help_text" class="block text-[10px] text-neutral-400 mt-1">Hành động ghi nhận trạng thái của công đoạn</span>
                    </div>
                </div>

                <!-- Operator -->
                <div>
                    <label for="modal_operator" class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase mb-1">Nhân viên phụ trách</label>
                    <select name="operator_user_id" id="modal_operator"
                            class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Mặc định (Hệ thống) --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Active Toggle -->
                <div class="flex items-center gap-3 py-2">
                    <input type="checkbox" name="is_active" id="modal_is_active" value="1"
                           class="w-5 h-5 text-indigo-600 border-neutral-300 rounded focus:ring-indigo-500 focus:ring-2">
                    <label for="modal_is_active" class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 select-none">
                        Kích hoạt thiết bị hoạt động
                    </label>
                </div>

                <!-- Notes -->
                <div>
                    <label for="modal_notes" class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase mb-1">Ghi chú thiết bị</label>
                    <textarea name="notes" id="modal_notes" rows="2"
                              class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                              placeholder="Ví dụ: Đặt tại bàn ép ván số 1..."></textarea>
                </div>
            </div>

            <div class="px-6 py-4 bg-neutral-50 dark:bg-neutral-900/50 border-t border-neutral-100 dark:border-neutral-800 flex justify-end gap-3 rounded-b-xl">
                <button type="button" onclick="closeModal('editDeviceModal')"
                        class="px-5 py-2.5 border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 rounded-xl font-bold text-sm hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                    Hủy
                </button>
                <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm shadow-sm transition-colors">
                    Lưu cấu hình
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Notification Toasts -->
    @if(session('success'))
        <div id="successToast" class="fixed bottom-5 right-5 z-50 flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-300 text-sm font-semibold transition-all duration-300">
            <iconify-icon icon="lucide:check-circle" class="text-lg text-emerald-600"></iconify-icon>
            <span>{{ session('success') }}</span>
        </div>
        <script>
            setTimeout(() => {
                const t = document.getElementById('successToast');
                if(t) {
                    t.style.opacity = '0';
                    setTimeout(() => t.remove(), 300);
                }
            }, 3500);
        </script>
    @endif
@endsection

@push('scripts')
<script>
    function switchQrTab(tabName) {
        const tabs = ['config', 'devices', 'logs'];
        tabs.forEach(t => {
            const pane = document.getElementById('tab-pane-' + t);
            const btn = document.getElementById('tab-btn-' + t);
            const badge = document.getElementById('tab-badge-' + t);

            if (t === tabName) {
                if (pane) pane.classList.remove('hidden');
                if (btn) {
                    btn.className = 'flex items-center gap-2.5 px-5 py-3 rounded-xl font-bold text-xs sm:text-sm transition-all bg-primary-600 text-white shadow-sm shadow-primary-200';
                }
                if (badge) {
                    badge.className = 'px-2 py-0.5 rounded-full text-xs font-extrabold bg-white/20 text-white';
                }
            } else {
                if (pane) pane.classList.add('hidden');
                if (btn) {
                    btn.className = 'flex items-center gap-2.5 px-5 py-3 rounded-xl font-bold text-xs sm:text-sm transition-all bg-neutral-50 dark:bg-neutral-800 hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-600 dark:text-neutral-300';
                }
                if (badge) {
                    badge.className = 'px-2 py-0.5 rounded-full text-xs font-extrabold bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-200';
                }
            }
        });

        // Update URL query parameter without full reload
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url.toString());
    }

    function addNewCommandRow() {
        const container = document.getElementById('config-commands-container');
        const rows = container.querySelectorAll('.command-row');
        const newIdx = rows.length;
        const stepNum = newIdx + 1;

        const div = document.createElement('div');
        div.className = 'command-row bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-200 dark:border-neutral-700 p-4 rounded-xl relative';
        div.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <span class="step-label font-extrabold text-xs text-indigo-600 dark:text-indigo-400 uppercase flex items-center gap-1">
                    <iconify-icon icon="solar:round-alt-arrow-down-bold"></iconify-icon>
                    Bước <span class="step-num">${stepNum}</span>
                </span>
                <button type="button" onclick="removeCommandRow(this)" class="text-neutral-400 hover:text-rose-600 text-xs font-semibold flex items-center gap-1 transition">
                    <iconify-icon icon="lucide:trash-2" class="text-sm"></iconify-icon> Xóa bước này
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 mb-1">Nhãn / Huy hiệu</label>
                    <input type="text" name="commands[${newIdx}][badge]" value="Bước ${stepNum}"
                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg text-xs bg-white dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100"
                           placeholder="Ví dụ: Bước ${stepNum}">
                </div>
                <div class="md:col-span-8">
                    <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 mb-1">Tiêu đề bước <span class="text-rose-500">*</span></label>
                    <input type="text" name="commands[${newIdx}][title]" value="" required
                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg text-xs bg-white dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 font-semibold"
                           placeholder="Nhập tiêu đề bước cấu hình">
                </div>
                <div class="md:col-span-12">
                    <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 mb-1">Nội dung mã lệnh QR (&lt;cmd&gt;...) <span class="text-rose-500">*</span></label>
                    <input type="text" name="commands[${newIdx}][cmd]" value="" required
                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg text-xs bg-white dark:bg-neutral-800 text-indigo-600 dark:text-indigo-400 font-mono font-bold"
                           placeholder="Ví dụ: <cmd>...">
                </div>
                <div class="md:col-span-12">
                    <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 mb-1">Mô tả / Hướng dẫn thêm</label>
                    <input type="text" name="commands[${newIdx}][desc]" value=""
                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-700 rounded-lg text-xs bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300"
                           placeholder="Mô tả tác dụng của lệnh">
                </div>
            </div>
        `;
        container.appendChild(div);
    }

    function removeCommandRow(btn) {
        const container = document.getElementById('config-commands-container');
        const rows = container.querySelectorAll('.command-row');
        if (rows.length <= 1) {
            alert('Phải giữ lại ít nhất 1 bước cấu hình!');
            return;
        }
        btn.closest('.command-row').remove();
        // Re-index step numbers
        const updatedRows = container.querySelectorAll('.command-row');
        updatedRows.forEach((row, i) => {
            const stepNum = i + 1;
            row.querySelector('.step-num').textContent = stepNum;
            row.querySelectorAll('input').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/commands\[\d+\]/, `commands[${i}]`));
                }
            });
        });
    }

    function confirmResetDefaults() {
        if (confirm('Bạn có chắc chắn muốn khôi phục lại các mã QR cấu hình mặc định gốc ban đầu?')) {
            document.getElementById('resetConfigForm').submit();
        }
    }

    function openEditModal(device) {
        document.getElementById("modal_device_id").value = device.id;
        document.getElementById("modal_name").value = device.name;
        document.getElementById("modal_process_step").value = device.process_step || "";
        document.getElementById("modal_action_type").value = device.action_type || "";
        document.getElementById("modal_operator").value = device.operator_user_id || "";
        document.getElementById("modal_is_active").checked = !!device.is_active;
        document.getElementById("modal_notes").value = device.notes || "";

        // Set form action dynamic route
        const form = document.getElementById("editDeviceForm");
        form.action = "/processes/qr-scans/devices/" + device.id;

        updateActionPlaceholder();

        // Dùng openModal() từ x-modal component
        openModal("editDeviceModal");
    }
    
    function updateActionPlaceholder() {
        const step = document.getElementById("modal_process_step").value;
        const actionInp = document.getElementById("modal_action_type");
        const helpTxt = document.getElementById("action_help_text");
        
        if (!actionInp || !helpTxt) return;
        
        switch(step) {
            case "cnc":
                actionInp.placeholder = "complete";
                helpTxt.textContent = "Gợi ý hành động: complete (hoàn thành) hoặc rollback (quay lại).";
                break;
            case "pressing":
                actionInp.placeholder = "ép đơn";
                helpTxt.textContent = "Gợi ý hành động: làm lệnh ép, xuất kho ván, ép đơn, ép dự trữ, rollback.";
                break;
            case "edge_banding":
                actionInp.placeholder = "complete";
                helpTxt.textContent = "Gợi ý hành động: complete hoặc rollback.";
                break;
            case "finishing":
                actionInp.placeholder = "complete";
                helpTxt.textContent = "Gợi ý hành động: complete hoặc rollback.";
                break;
            case "qc":
                actionInp.placeholder = "complete";
                helpTxt.textContent = "Gợi ý hành động: complete, lỗi ép ván, lỗi cắt cnc, lỗi dán cạnh, lỗi làm đẹp.";
                break;
            default:
                actionInp.placeholder = "";
                helpTxt.textContent = "Nhập hành động của công đoạn.";
        }
    }

    function copyQrCommand(text, btn) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                const oldHtml = btn.innerHTML;
                btn.innerHTML = '<iconify-icon icon="solar:check-circle-bold" class="text-emerald-600 text-base"></iconify-icon>';
                setTimeout(() => { btn.innerHTML = oldHtml; }, 2000);
            });
        }
    }
</script>
@endpush
