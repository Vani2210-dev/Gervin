@extends('layout.layout')

@php
    $title = 'Thiết bị quét QR';
    $subTitle = 'Cấu hình & Nhật ký';

    $configCommands = [
        [
            'step'    => 1,
            'title'   => 'Khôi phục cài đặt gốc',
            'desc'    => 'Đặt lại toàn bộ thông số máy quét về mặc định ban đầu',
            'cmd'     => '<cmd>rk_reset',
            'badge'   => 'Bước 1: Reset',
        ],
        [
            'step'    => 2,
            'title'   => 'Kết nối Wi-Fi xưởng',
            'desc'    => 'SSID: "CTY GERVIN - XUONG" | Pass: "68686868"',
            'cmd'     => '<cmd>wifi -ssid "CTY GERVIN - XUONG" -pass "68686868"',
            'badge'   => 'Bước 2: Wi-Fi',
        ],
        [
            'step'    => 3,
            'title'   => 'Cấu hình Máy chủ Server',
            'desc'    => 'URL: https://gervinwood.vn/scan -dup 1 -queue 20',
            'cmd'     => '<cmd>server -url "https://gervinwood.vn/scan" -dup 1 -queue 20',
            'badge'   => 'Bước 3: Server',
        ],
        [
            'step'    => 4,
            'title'   => 'Thời gian nghỉ giữa các lần quét',
            'desc'    => 'Tạm dừng 1500ms (1.5 giây) giữa 2 lần quét',
            'cmd'     => '<cmd>rk -pause 1500',
            'badge'   => 'Bước 4: Pause 1.5s',
        ],
        [
            'step'    => 5,
            'title'   => 'Màn hình luôn sáng',
            'desc'    => 'Timeout = 0: Không bao giờ tắt màn hình máy quét',
            'cmd'     => '<cmd>screen -timeout 0',
            'badge'   => 'Bước 5: Screen Timeout',
        ],
    ];
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

    <div class="-mt-4 mb-6">
        <p class="text-sm text-neutral-500 dark:text-neutral-400">Xem nhật ký quét thời gian thực và quản lý liên kết cấu hình cho thiết bị Rakinda RK80ER.</p>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left: Devices Configuration (4 Columns) -->
        <div class="lg:col-span-4 flex flex-col gap-6">

            <!-- Card: Cấu hình Máy quét QR -->
            <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">
                <div class="card-header border-b border-neutral-200 dark:border-neutral-800 py-3.5 px-5 flex justify-between items-center bg-white dark:bg-neutral-900/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shrink-0">
                            <iconify-icon icon="solar:qr-code-bold-duotone"></iconify-icon>
                        </div>
                        <div>
                            <h5 class="text-sm font-bold text-neutral-800 dark:text-neutral-100 m-0">Cấu hình Máy quét</h5>
                            <span class="text-[11px] text-neutral-400">Rakinda RK80ER & Thiết bị QR</span>
                        </div>
                    </div>
                    <button type="button" onclick="openModal('printQrConfigModal')"
                            class="btn bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950 dark:hover:bg-indigo-900 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-xs font-semibold px-2.5 py-1.5 rounded-lg flex items-center gap-1.5 transition">
                        <iconify-icon icon="solar:printer-bold" class="text-sm"></iconify-icon>
                        In mã A4
                    </button>
                </div>

                {{-- Alert instruction --}}
                <div class="p-3.5 bg-amber-50 dark:bg-amber-950/20 border-b border-amber-200 dark:border-amber-800/40 flex items-start gap-2.5">
                    <iconify-icon icon="solar:danger-triangle-bold" class="text-amber-600 text-lg shrink-0 mt-0.5"></iconify-icon>
                    <div class="text-xs text-amber-900 dark:text-amber-200">
                        <span class="font-bold uppercase tracking-wide block mb-0.5">Quét từ trên xuống để cấu hình Máy quét</span>
                        <span class="text-neutral-600 dark:text-neutral-300 leading-relaxed">Đưa đầu đọc máy quét lần lượt qua từng mã QR từ <strong>Bước 1</strong> đến <strong>Bước 5</strong> từ trên xuống dưới để thiết lập máy.</span>
                    </div>
                </div>

                {{-- QR Sequence List --}}
                <div class="card-body p-4 space-y-4 max-h-[820px] overflow-y-auto">
                    @foreach($configCommands as $idx => $item)
                        @php
                            $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                                ->size(140)
                                ->margin(1)
                                ->generate($item['cmd']);
                            $qrSvg = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $qrSvg);
                        @endphp
                        <div class="relative bg-neutral-50/80 dark:bg-neutral-800/40 p-3.5 rounded-xl border border-neutral-200/80 dark:border-neutral-700 flex flex-col items-center text-center">
                            {{-- Step indicator header --}}
                            <div class="w-full flex items-center justify-between mb-2">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                    <iconify-icon icon="solar:round-alt-arrow-down-bold"></iconify-icon>
                                    {{ $item['badge'] }}
                                </span>
                                <span class="text-[10px] font-mono text-neutral-400 font-semibold">
                                    Mã #{{ $item['step'] }}/{{ count($configCommands) }}
                                </span>
                            </div>

                            <h6 class="font-bold text-xs text-neutral-800 dark:text-neutral-100 mb-0.5">
                                {{ $item['title'] }}
                            </h6>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mb-2">
                                {{ $item['desc'] }}
                            </p>

                            {{-- High-contrast white QR frame for reliable optical scanning --}}
                            <div class="bg-white p-2.5 rounded-xl border border-neutral-200 shadow-2xs flex items-center justify-center my-1 hover:scale-105 transition-transform">
                                <div class="w-[140px] h-[140px] flex items-center justify-center">
                                    {!! $qrSvg !!}
                                </div>
                            </div>

                            {{-- Command box with copy --}}
                            <div class="mt-2 w-full flex items-center justify-between bg-white dark:bg-neutral-900 px-2.5 py-1.5 rounded-lg border border-neutral-200 dark:border-neutral-700 text-left">
                                <code class="text-[11px] font-mono text-indigo-600 dark:text-indigo-400 truncate select-all flex-1" title="{{ $item['cmd'] }}">
                                    {{ $item['cmd'] }}
                                </code>
                                <button type="button" onclick="copyQrCommand('{{ addslashes($item['cmd']) }}', this)"
                                        class="text-neutral-400 hover:text-indigo-600 ml-1.5 p-1 rounded transition-colors shrink-0"
                                        title="Sao chép lệnh">
                                    <iconify-icon icon="solar:copy-bold" class="text-sm"></iconify-icon>
                                </button>
                            </div>

                            @if(!$loop->last)
                                <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 z-10 w-6 h-6 rounded-full bg-white dark:bg-neutral-900 border border-neutral-300 dark:border-neutral-700 flex items-center justify-center text-neutral-400 text-xs shadow-xs">
                                    <iconify-icon icon="lucide:arrow-down"></iconify-icon>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Existing Card: Danh sách Thiết bị -->
            <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm">
                <div class="card-header border-b border-neutral-200 dark:border-neutral-800 py-4 px-6 flex justify-between items-center bg-white dark:bg-neutral-900/50">
                    <h5 class="text-lg font-bold text-neutral-800 dark:text-neutral-100 mb-0 flex items-center gap-2">
                        <iconify-icon icon="lucide:cpu" class="text-indigo-600 text-xl"></iconify-icon>
                        Danh sách Thiết bị
                    </h5>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 rounded-full">
                        {{ $devices->count() }} thiết bị
                    </span>
                </div>
                <div class="card-body p-6 flex flex-col gap-3">
                    @forelse($devices as $device)
                        @php
                            $stepName = 'Chưa cấu hình';
                            $stepIcon = 'lucide:help-circle';
                            $stepColor = 'text-neutral-400';
                            
                            switch($device->process_step) {
                                case 'cnc':
                                    $stepName = 'Cắt CNC';
                                    $stepIcon = 'lucide:scissors';
                                    $stepColor = 'text-indigo-500';
                                    break;
                                case 'pressing':
                                    $stepName = 'Ép ván dán mặt';
                                    $stepIcon = 'lucide:layers';
                                    $stepColor = 'text-emerald-500';
                                    break;
                                case 'edge_banding':
                                    $stepName = 'Dán cạnh';
                                    $stepIcon = 'lucide:brush';
                                    $stepColor = 'text-sky-500';
                                    break;
                                case 'finishing':
                                    $stepName = 'Làm đẹp';
                                    $stepIcon = 'lucide:sparkles';
                                    $stepColor = 'text-amber-500';
                                    break;
                                case 'qc':
                                    $stepName = 'QC (Kiểm soát)';
                                    $stepIcon = 'lucide:check-circle';
                                    $stepColor = 'text-red-500';
                                    break;
                            }
                        @endphp
                        
                        <div class="p-4 border rounded-xl flex flex-col gap-3 transition-colors {{ $device->is_active ? 'border-neutral-200 dark:border-neutral-800 hover:border-indigo-500/50' : 'border-dashed border-red-300 bg-red-50/10' }}">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center {{ $stepColor }}">
                                        <iconify-icon icon="{{ $stepIcon }}" class="text-xl"></iconify-icon>
                                    </div>
                                    <div>
                                        <span class="block font-bold text-neutral-800 dark:text-neutral-200">{{ $device->name }}</span>
                                        <span class="block text-xs font-mono text-neutral-400">ID: {{ $device->id }}</span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <button type="button" 
                                            onclick="openEditModal({{ json_encode($device) }})"
                                            class="p-1.5 text-neutral-400 hover:text-indigo-600 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                                        <iconify-icon icon="lucide:edit" class="text-base"></iconify-icon>
                                    </button>
                                    <form action="{{ route('processes.qr-scans.delete-device', $device->id) }}" method="POST" onsubmit="return confirm('Xóa thiết bị quét này khỏi hệ thống?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-neutral-400 hover:text-red-600 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                                            <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-2 text-xs border-t border-neutral-100 dark:border-neutral-800 pt-3">
                                <div>
                                    <span class="block text-neutral-400 font-medium">Công đoạn</span>
                                    <span class="block font-bold text-neutral-700 dark:text-neutral-300 flex items-center gap-1 mt-0.5">
                                        {{ $stepName }}
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-neutral-400 font-medium">Hành động</span>
                                    <span class="block font-bold text-neutral-700 dark:text-neutral-300 mt-0.5 uppercase">
                                        {{ $device->action_type ?: 'complete' }}
                                    </span>
                                </div>
                                <div class="col-span-2 mt-1">
                                    <span class="block text-neutral-400 font-medium">Nhân viên phụ trách</span>
                                    <span class="block font-bold text-neutral-700 dark:text-neutral-300 mt-0.5">
                                        {{ $device->operator->name ?? 'Mặc định (Hệ thống)' }}
                                    </span>
                                </div>
                            </div>
                            
                            @if($device->notes)
                                <div class="text-[11px] text-neutral-500 bg-neutral-50 dark:bg-neutral-800/40 p-2 rounded-lg border border-neutral-100 dark:border-neutral-800/50">
                                    <strong>Ghi chú:</strong> {{ $device->notes }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8 text-neutral-400 dark:text-neutral-500 border border-dashed border-neutral-200 dark:border-neutral-800 rounded-xl">
                            <iconify-icon icon="lucide:cpu" class="text-3xl mb-2"></iconify-icon>
                            <p class="text-sm">Chưa có thiết bị nào được kết nối.</p>
                            <p class="text-xs text-neutral-400 mt-1">Thiết bị sẽ tự động xuất hiện ở đây khi quét mã đầu tiên.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Scan Logs (8 Columns) -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">
                <div class="card-header border-b border-neutral-200 dark:border-neutral-800 py-4 px-6 flex flex-wrap gap-3 items-center justify-between bg-white dark:bg-neutral-900/50">
                    <h5 class="text-lg font-bold text-neutral-800 dark:text-neutral-100 mb-0 flex items-center gap-2">
                        <iconify-icon icon="lucide:activity" class="text-indigo-600 text-xl"></iconify-icon>
                        Nhật ký Quét QR
                    </h5>
                    
                    <div class="flex items-center flex-wrap gap-2">
                        <!-- Filter Forms -->
                        <form method="GET" action="{{ route('processes.qr-scans') }}" class="flex items-center flex-wrap gap-2">
                            <input type="hidden" name="per_page" value="{{ $perPage }}">

                            <!-- Search -->
                            <div class="relative w-44 sm:w-52">
                                <span class="absolute top-1/2 -translate-y-1/2 text-neutral-400 flex items-center justify-center pointer-events-none" style="left: 10px;">
                                    <iconify-icon icon="lucide:search" class="text-base"></iconify-icon>
                                </span>
                                <input type="text" name="search"
                                    class="w-full pr-3 py-1.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 text-xs focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    style="padding-left: 34px;"
                                    placeholder="Tìm mã sản phẩm..." value="{{ $search }}">
                            </div>

                            <!-- Status Filter -->
                            <select name="status" onchange="this.form.submit()"
                                    class="form-select form-select-sm w-auto border border-neutral-200 dark:border-neutral-700 rounded-lg py-1 px-2 text-xs bg-transparent dark:text-neutral-300">
                                <option value="">-- Tất cả trạng thái --</option>
                                <option value="success" {{ $statusFilter === 'success' ? 'selected' : '' }}>Thành công</option>
                                <option value="failed" {{ $statusFilter === 'failed' ? 'selected' : '' }}>Thất bại</option>
                                <option value="duplicate" {{ $statusFilter === 'duplicate' ? 'selected' : '' }}>Trùng lặp</option>
                                <option value="unmapped" {{ $statusFilter === 'unmapped' ? 'selected' : '' }}>Chưa cấu hình</option>
                            </select>

                            <!-- Device Filter -->
                            <select name="device_id" onchange="this.form.submit()"
                                    class="form-select form-select-sm w-auto border border-neutral-200 dark:border-neutral-700 rounded-lg py-1 px-2 text-xs bg-transparent dark:text-neutral-300">
                                <option value="">-- Tất cả máy quét --</option>
                                @foreach($devices as $dev)
                                    <option value="{{ $dev->id }}" {{ $deviceFilter == $dev->id ? 'selected' : '' }}>{{ $dev->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
                
                <div class="card-body p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm" id="logsTable">
                            <thead>
                                <tr class="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 whitespace-nowrap">
                                    <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">THỜI GIAN</th>
                                    <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">MÁY QUÉT</th>
                                    <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">MÃ SẢN PHẨM (QR)</th>
                                    <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">TRẠNG THÁI</th>
                                    <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">MÔ TẢ KẾT QUẢ</th>
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
                        <div class="flex items-center justify-between flex-wrap gap-2 mt-6">
                            <span class="text-secondary-light text-sm">
                                Hiển thị {{ $logs->firstItem() ?? 0 }} đến {{ $logs->lastItem() ?? 0 }}
                                trong tổng {{ $logs->total() }} bản ghi nhật ký
                            </span>
                            {{ $logs->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- Modal: Bản in Cấu hình Máy quét QR -->
    <x-modal name="printQrConfigModal" maxWidth="3xl">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between bg-white dark:bg-neutral-900 print:hidden">
            <h5 class="font-bold text-base text-neutral-800 dark:text-neutral-100 m-0 flex items-center gap-2">
                <iconify-icon icon="solar:printer-bold" class="text-indigo-600 text-xl"></iconify-icon>
                Bản in Mã QR Cấu hình Máy quét
            </h5>
            <div class="flex items-center gap-2">
                <button type="button" onclick="printQrSheet()"
                        class="btn bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-xs">
                    <iconify-icon icon="solar:printer-bold"></iconify-icon> In ngay (Print)
                </button>
                <button type="button" onclick="closeModal('printQrConfigModal')" class="text-neutral-400 hover:text-neutral-700 text-xl leading-none">&times;</button>
            </div>
        </div>
        <div class="p-6 bg-white text-neutral-900" id="printable-qr-content">
            <div class="text-center border-b-2 border-neutral-900 pb-3 mb-4">
                <h2 class="text-xl font-extrabold uppercase tracking-wide text-neutral-900 m-0">CÔNG TY GERVIN - HƯỚNG DẪN CẤU HÌNH MÁY QUÉT QR</h2>
                <div class="mt-2 inline-block bg-neutral-900 text-white text-xs font-bold uppercase tracking-wider px-4 py-1 rounded">
                    Quét từ trên xuống để cấu hình Máy quét
                </div>
                <p class="text-xs text-neutral-500 mt-1 mb-0">Thiết bị: Rakinda RK80ER | Hệ thống: https://gervinwood.vn/scan</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($configCommands as $item)
                    @php
                        $printSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                            ->size(150)
                            ->margin(1)
                            ->generate($item['cmd']);
                        $printSvg = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $printSvg);
                    @endphp
                    <div class="border-2 border-neutral-300 rounded-xl p-3.5 flex flex-col items-center text-center {{ $loop->last ? 'sm:col-span-2 sm:max-w-md sm:mx-auto w-full' : '' }}">
                        <span class="text-xs font-extrabold uppercase bg-neutral-100 text-neutral-800 px-3 py-0.5 rounded-full border border-neutral-300 mb-1">
                            {{ $item['badge'] }}
                        </span>
                        <h6 class="font-bold text-xs text-neutral-900 mb-0.5">{{ $item['title'] }}</h6>
                        <p class="text-[11px] text-neutral-500 mb-2">{{ $item['desc'] }}</p>

                        <div class="bg-white p-2 border border-neutral-300 rounded-lg shadow-xs">
                            <div class="w-[140px] h-[140px] flex items-center justify-center">
                                {!! $printSvg !!}
                            </div>
                        </div>

                        <code class="text-[11px] font-mono font-bold text-neutral-900 bg-neutral-100 px-2 py-1 rounded mt-2 border border-neutral-200">
                            {{ $item['cmd'] }}
                        </code>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-4 pt-3 border-t border-neutral-200 flex justify-between items-center text-[11px] text-neutral-500">
                <span>Hệ thống ERP GervinWood.vn</span>
                <span>Ngày xuất bản: {{ date('d/m/Y H:i') }}</span>
            </div>
        </div>
    </x-modal>

    <!-- Edit Device Modal -->
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
                btn.innerHTML = '<iconify-icon icon="solar:check-circle-bold" class="text-emerald-600 text-sm"></iconify-icon>';
                setTimeout(() => { btn.innerHTML = oldHtml; }, 2000);
            });
        }
    }

    function printQrSheet() {
        const printContent = document.getElementById("printable-qr-content").innerHTML;
        const printWin = window.open("", "", "width=850,height=900");
        printWin.document.write("<!DOCTYPE html><html><head><title>Cau_Hinh_May_Quet_QR_Gervin</title><style>body { font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif; margin: 20px; color: #111; } .text-center { text-align: center; } .grid { display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; } .qr-card { border: 2px solid #333; border-radius: 8px; padding: 12px; margin: 6px; width: 44%; box-sizing: border-box; text-align: center; } code { font-family: monospace; font-size: 11px; background: #eee; padding: 3px 6px; border-radius: 4px; display: inline-block; word-break: break-all; } h2 { font-size: 16px; margin: 0 0 6px 0; } h6 { font-size: 13px; margin: 4px 0; } p { font-size: 11px; color: #555; margin: 2px 0 6px 0; } .badge { font-size: 11px; font-weight: bold; background: #222; color: #fff; padding: 2px 8px; border-radius: 4px; display: inline-block; } svg { width: 140px; height: 140px; display: block; margin: 0 auto; } @media print { @page { margin: 10mm; } }</style></head><body>" + printContent + "</body></html>");
        printWin.document.close();
        printWin.focus();
        setTimeout(() => {
            printWin.print();
            printWin.close();
        }, 400);
    }
</script>
@endpush
