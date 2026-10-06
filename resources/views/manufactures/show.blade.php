@extends('layout.layout')
@php
    $title    = 'Chi tiết Lệnh sản xuất';
    $subTitle = $manufacture->code;
    
    // Status mapping for active states
    $statusSteps = [
        'initialized' => 1,
        'tech_approved' => 2,
        'manager_approved' => 3,
        'stamps_received' => 4,
        'in_production' => 5,
        'completed' => 6
    ];
    
    $currentStepNum = $statusSteps[$manufacture->status] ?? 1;
@endphp

@section('content')

<style>
    /* Default (Mobile) */
    .manufacture-desktop-line {
        display: none !important;
    }
    .manufacture-mobile-line {
        display: block !important;
    }

    /* Desktop (>= 768px) */
    @media (min-width: 768px) {
        .manufacture-stepper {
            flex-direction: row !important;
        }
        .manufacture-stepper .manufacture-step-item {
            flex-direction: column !important;
            text-align: center !important;
            width: 15% !important;
        }
        .manufacture-stepper .manufacture-step-text {
            margin-left: 0 !important;
            margin-top: 12px !important;
            text-align: center !important;
        }
        .manufacture-desktop-line {
            display: block !important;
        }
        .manufacture-mobile-line {
            display: none !important;
        }
    }
</style>

{{-- Production Status Banner --}}
<div class="bg-white border border-neutral-200 rounded-xl mb-6 shadow-sm p-6">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl font-bold shadow-sm flex-shrink-0
                {{ $manufacture->status === 'completed' ? 'bg-success-50 text-success-600 border border-success-200' : 'bg-indigo-50 text-indigo-600 border border-indigo-200' }}">
                @if($manufacture->status === 'completed')
                    <iconify-icon icon="solar:check-circle-bold" class="text-3xl text-success-600"></iconify-icon>
                @else
                    <iconify-icon icon="solar:cog-bold-duotone" class="text-3xl text-indigo-600 animate-spin" style="animation-duration: 6s;"></iconify-icon>
                @endif
            </div>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h4 class="font-black text-xl text-neutral-800 m-0">{{ $manufacture->code }}</h4>
                    @if($manufacture->status === 'completed')
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-success-100 text-success-700 border border-success-200 flex items-center gap-1">
                            <iconify-icon icon="lucide:check-circle"></iconify-icon> Đã hoàn thành
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 border border-indigo-200 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-indigo-600 animate-ping"></span> Đang sản xuất
                        </span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-neutral-500">
                    <span><strong class="text-neutral-700">Người tạo:</strong> {{ $manufacture->creator->name ?? 'Hệ thống' }}</span>
                    <span>•</span>
                    <span><strong class="text-neutral-700">Ngày tạo:</strong> {{ $manufacture->created_at->format('d/m/Y H:i') }}</span>
                    <span>•</span>
                    <span><strong class="text-neutral-700">Số đơn ghép:</strong> {{ $manufacture->orders->count() }} đơn</span>
                    <span>•</span>
                    <span><strong class="text-neutral-700">Tổng số tấm:</strong> {{ $totalItemsCount }} tấm</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 self-stretch md:self-auto justify-end flex-wrap">
            @if(count($acrylicOrdersExportData ?? []) > 0)
                <button type="button" onclick="exportManufactureNestingFiles('{{ $manufacture->code }}', window.moOrdersExportData)"
                    class="px-4 py-2.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition-colors flex items-center gap-2 cursor-pointer"
                    title="Tải file xả tem Nesting cho tất cả các tấm Acrylic trong lệnh này">
                    <iconify-icon icon="lucide:table-2" class="text-base"></iconify-icon> Tải file xả tem Nesting
                </button>
            @endif
            <a href="{{ route('manufactures.print-stamps', $manufacture) }}" target="_blank"
                class="px-4 py-2.5 rounded-lg text-xs font-bold bg-neutral-100 hover:bg-neutral-200 text-neutral-800 transition-colors flex items-center gap-2">
                <iconify-icon icon="lucide:printer" class="text-base text-neutral-600"></iconify-icon> In phiếu dán tem
            </a>
            @if($manufacture->status === 'in_production')
                <form action="{{ route('manufactures.approve', [$manufacture, 'complete']) }}" method="POST" class="m-0" onsubmit="return confirm('Bạn có chắc muốn hoàn thành lệnh sản xuất này?')">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 rounded-lg text-xs font-bold bg-success-600 hover:bg-success-700 text-white shadow-sm transition-colors flex items-center gap-2 cursor-pointer">
                        <iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon> Hoàn thành lệnh SX
                    </button>
                </form>
            @elseif($manufacture->status !== 'completed')
                <form action="{{ route('manufactures.approve', [$manufacture, 'start_production']) }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 rounded-lg text-xs font-bold bg-primary-600 hover:bg-primary-700 text-white shadow-sm transition-colors flex items-center gap-2 cursor-pointer">
                        <iconify-icon icon="lucide:play-circle" class="text-base"></iconify-icon> Kích hoạt sản xuất ngay
                    </button>
                </form>
            @endif
            <a href="{{ route('manufactures.index') }}" class="px-3.5 py-2.5 rounded-lg text-xs font-bold border border-neutral-300 hover:bg-neutral-50 text-neutral-600 transition-colors flex items-center gap-1.5">
                <iconify-icon icon="lucide:arrow-left" class="text-base"></iconify-icon> Danh sách
            </a>
        </div>
    </div>
</div>

{{-- Real-time Production Progress Card --}}
<div class="bg-white border border-neutral-200 rounded-xl mb-6 shadow-sm p-5">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-4">
        <div>
            <h6 class="font-bold text-base text-neutral-800 m-0 flex items-center gap-2">
                <iconify-icon icon="solar:chart-square-bold" class="text-xl text-primary-600"></iconify-icon>
                Tiến độ gia công thực tế
            </h6>
            <p class="text-xs text-neutral-500 mb-0 mt-0.5">Cập nhật trực tiếp theo từng lượt quét mã QR các tấm tại xưởng</p>
        </div>
        <div class="flex items-center gap-4 text-xs font-semibold flex-wrap">
            <span class="flex items-center gap-1.5 text-neutral-600">
                <span class="w-3 h-3 rounded-full bg-neutral-300"></span> Chưa bắt đầu: <strong class="text-neutral-800">{{ max(0, ($progress['total'] ?? 0) - ($progress['completed'] ?? 0) - ($progress['in_progress'] ?? 0)) }}</strong>
            </span>
            <span class="flex items-center gap-1.5 text-indigo-600">
                <span class="w-3 h-3 rounded-full bg-indigo-500"></span> Đang gia công: <strong class="text-indigo-800">{{ $progress['in_progress'] ?? 0 }}</strong>
            </span>
            <span class="flex items-center gap-1.5 text-success-600">
                <span class="w-3 h-3 rounded-full bg-success-500"></span> Đã hoàn thành: <strong class="text-success-800">{{ $progress['completed'] ?? 0 }}/{{ $progress['total'] ?? 0 }}</strong>
            </span>
        </div>
    </div>

    {{-- Main Progress Bar --}}
    <div class="w-full bg-neutral-100 rounded-full h-3.5 overflow-hidden mb-3 border border-neutral-200 p-0.5 flex">
        <div class="h-full rounded-full transition-all duration-500 bg-success-500" style="width: {{ $progress['percentage'] ?? 0 }}%;"></div>
        @php
            $inProgPct = ($progress['total'] ?? 0) > 0 ? round((($progress['in_progress'] ?? 0) / $progress['total']) * 100) : 0;
        @endphp
        <div class="h-full rounded-full transition-all duration-500 bg-indigo-500" style="width: {{ $inProgPct }}%;"></div>
    </div>

    {{-- Breakdown by order --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 pt-3 border-t border-neutral-100">
        @foreach($manufacture->orders as $o)
        @php
            $orderTotal = 0;
            $orderDone = 0;
            foreach($o->supplies as $sup) {
                $subItems = $o->type === 'glass' ? $sup->glassItems : ($o->type === 'min_late' ? $sup->minLateItems : $sup->items);
                foreach($subItems as $subI) {
                    foreach($subI->codes as $subC) {
                        $orderTotal++;
                        $logs = is_string($subC->status) ? json_decode($subC->status, true) : ($subC->status ?? []);
                        foreach($logs as $l) {
                            $act = mb_strtolower($l['action'] ?? '');
                            if ($act === 'hoàn thành qc' || $act === 'hoàn thành làm đẹp') {
                                $orderDone++;
                                break;
                            }
                        }
                    }
                }
            }
            $orderPct = $orderTotal > 0 ? round(($orderDone / $orderTotal) * 100) : 0;
        @endphp
        <div class="p-2.5 rounded-lg border border-neutral-200 bg-neutral-50/50 flex flex-col justify-between">
            <div class="flex items-center justify-between gap-1 mb-1.5">
                <a href="{{ route('orders.show', $o) }}" class="text-xs font-bold text-neutral-800 hover:text-primary-600 transition-colors">
                    {{ $o->order_code }}
                </a>
                <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold {{ $orderDone == $orderTotal && $orderTotal > 0 ? 'bg-success-100 text-success-700' : 'bg-neutral-200 text-neutral-700' }}">
                    {{ ucfirst($o->type) }}
                </span>
            </div>
            <div class="w-full bg-neutral-200 rounded-full h-1.5 overflow-hidden mb-1">
                <div class="h-1.5 rounded-full {{ $orderDone == $orderTotal && $orderTotal > 0 ? 'bg-success-500' : 'bg-indigo-600' }}" style="width: {{ $orderPct }}%;"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-neutral-500 font-medium">
                <span>{{ $orderDone }}/{{ $orderTotal }} tấm</span>
                <span class="font-bold {{ $orderDone == $orderTotal && $orderTotal > 0 ? 'text-success-600' : 'text-neutral-700' }}">{{ $orderPct }}%</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Main Grid --}}
<div class="grid grid-cols-12 gap-6">
    {{-- Left column: Danh sách các tấm --}}
    <div class="col-span-12 md:col-span-8 space-y-6">
        <div class="bg-white border border-neutral-200 rounded-xl overflow-hidden shadow-sm">
            <div class="border-b border-neutral-100 bg-white py-4 px-6 flex items-center justify-between">
                <h6 class="font-bold text-base text-neutral-800 m-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:layers" class="text-xl text-neutral-600"></iconify-icon>
                    Danh sách các tấm ({{ $totalItemsCount }})
                </h6>
                <div class="flex items-center gap-3">
                    {{-- Per page --}}
                    <div class="flex items-center gap-1.5 text-xs text-neutral-500">
                        <span class="font-medium text-secondary-light">Hiển thị</span>
                        <select onchange="window.location.href = this.value" class="form-select rounded-lg border-neutral-300 py-1 px-2.5 text-xs focus:border-primary-500 focus:ring-primary-500 bg-white cursor-pointer font-medium text-neutral-700 w-auto">
                            @foreach([15, 25, 50, 100] as $size)
                                <option value="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}" {{ $items->perPage() == $size ? 'selected' : '' }}>
                                    {{ $size }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <a href="{{ route('manufactures.print-stamps', $manufacture) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-neutral-700 hover:text-neutral-800 hover:bg-neutral-50 border border-neutral-200 hover:border-neutral-300 rounded-lg transition-colors duration-200">
                        <iconify-icon icon="lucide:printer" class="text-lg"></iconify-icon> In phiếu dán tem
                    </a>
                </div>
            </div>
            
            <div class="p-1">
                <div class="table-responsive border-0 overflow-x-auto scroll-sm">
                    <table class="table bordered-table sm-table mb-0">
                        <thead class="bg-neutral-50">
                            <tr>
                                <th scope="col" style="width: 50px;" class="text-center">Stt</th>
                                <th scope="col">Mã SP</th>
                                <th scope="col">Sản phẩm</th>
                                <th scope="col">Loại gỗ / Vật tư</th>
                                <th scope="col" class="text-center">Kích thước (mm)</th>
                                <th scope="col" class="text-center">SL</th>
                                <th scope="col">Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $index => $item)
                            <tr>
                                <td class="text-center align-middle">{{ ($items->currentPage() - 1) * $items->perPage() + $index + 1 }}</td>
                                <td>
                                    <span class="font-semibold text-secondary-light">{{ $item->product_code ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="text-base text-secondary-light font-medium">{{ $item->product_name }}</span>
                                    <span class="block text-xs text-neutral-400">Đơn hàng: {{ $item->order_code }}</span>
                                </td>
                                <td>
                                    <span class="text-base text-secondary-light">{{ $item->supply_name ?? '—' }}</span>
                                </td>
                                <td class="text-center align-middle font-medium text-secondary-light">
                                    {{ $item->dimensions ?: '—' }}
                                </td>
                                <td class="text-center align-middle font-semibold text-secondary-light">
                                    {{ $item->quantity }}
                                </td>
                                <td>
                                    <span class="text-xs text-neutral-500 block max-w-xs truncate" title="{{ $item->notes }}">{{ $item->notes ?? '—' }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-8">
                                    <p class="text-neutral-500 mb-0">Không có tấm gỗ/sản phẩm nào trong lệnh sản xuất này.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($items->hasPages() || $totalItemsCount > 15)
            <div class="px-6 py-4 border-t border-neutral-100 bg-neutral-50/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                <span class="text-neutral-500 text-xs font-medium">
                    Hiển thị từ {{ $items->firstItem() ?? 0 }} đến {{ $items->lastItem() ?? 0 }} trong tổng số {{ $items->total() }} tấm
                </span>
                {{ $items->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Right column: Thông tin lệnh & Lịch sử duyệt --}}
    <div class="col-span-12 md:col-span-4 space-y-6">
        
        {{-- Flash message inside right col --}}
        @if(session('success') || session('error'))
        <div class="bg-white border border-neutral-200 rounded-xl overflow-hidden shadow-sm">
            <div class="p-4">
                @if(session('success'))
                <div class="bg-success-50 text-success-600 border border-success-200 rounded-lg p-3 m-0 text-sm">
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="bg-danger-50 text-danger-600 border border-danger-200 rounded-lg p-3 m-0 text-sm">
                    {{ session('error') }}
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Action Card based on current step & permissions --}}
        @can('approve manufacture')
        @if($manufacture->status !== 'completed')
        <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
            <h6 class="font-bold text-sm text-neutral-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                <iconify-icon icon="lucide:check-circle" class="text-lg text-primary-600"></iconify-icon>
                Thao tác lệnh
            </h6>
            
            @if($manufacture->status === 'in_production')
                <form action="{{ route('manufactures.approve', [$manufacture, 'complete']) }}" method="POST" class="m-0" onsubmit="return confirm('Bạn có chắc muốn hoàn thành lệnh sản xuất này?')">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 bg-success-600 hover:bg-success-700 text-white shadow-sm border border-transparent transition-colors duration-200 cursor-pointer">
                        <iconify-icon icon="lucide:check-circle" class="text-lg"></iconify-icon>
                        Hoàn thành lệnh sản xuất
                    </button>
                </form>
            @else
                <form action="{{ route('manufactures.approve', [$manufacture, 'start_production']) }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 bg-primary-600 hover:bg-primary-700 text-white shadow-sm border border-transparent transition-colors duration-200 cursor-pointer">
                        <iconify-icon icon="lucide:play-circle" class="text-lg"></iconify-icon>
                        Kích hoạt Đang sản xuất ngay (1-Chạm)
                    </button>
                </form>
            @endif
        </div>
        @endif
        @endcan

        {{-- Phân phát tem nhanh --}}
        @if(in_array($manufacture->status, ['manager_approved', 'stamps_received', 'in_production']))
        <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
            <h6 class="font-bold text-sm text-neutral-800 uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-neutral-100 pb-3">
                <iconify-icon icon="solar:tag-bold-duotone" class="text-lg text-primary-600"></iconify-icon>
                Phân phát tem nhanh
            </h6>
            
            <div class="mb-4 text-xs text-neutral-500">
                @php
                    $totalStampsCount = $totalItemsCount;
                    $assignedStampsCount = $manufacture->stampDistributions->sum('quantity');
                    $unassignedStampsCount = max(0, $totalStampsCount - $assignedStampsCount);
                @endphp
                <div class="flex justify-between mb-1.5">
                    <span>Đã phân phát:</span>
                    <span class="font-semibold text-neutral-800">{{ $assignedStampsCount }} / {{ $totalStampsCount }} tem</span>
                </div>
                <div class="flex justify-between">
                    <span>Chưa phân phát:</span>
                    <span class="font-bold text-primary-600">{{ $unassignedStampsCount }} tem</span>
                </div>
            </div>

            <form action="{{ route('manufactures.assign-stamps', $manufacture) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="action" value="quick_distribute">
                
                <div class="form-group">
                    <label class="form-label text-xs font-semibold text-neutral-500 mb-1.5 block">Nhân viên nhận tem</label>
                    <select name="worker_id" id="worker_id_select" class="form-select rounded-lg border-neutral-300 text-sm py-2 px-3 focus:border-primary-500 focus:ring-primary-500 w-full" required>
                        <option value="">-- Chọn nhân viên --</option>
                        @foreach($workers as $worker)
                            <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label text-xs font-semibold text-neutral-500 mb-1.5 block">Số lượng tem cần phát</label>
                    <input type="number" name="quantity" min="1" max="{{ $unassignedStampsCount }}" class="form-control rounded-lg border-neutral-300 text-sm focus:border-primary-500 focus:ring-primary-500 w-full" placeholder="Ví dụ: 100" required>
                </div>

                <button type="submit" {{ $unassignedStampsCount == 0 ? 'disabled' : '' }} class="w-full py-2 px-4 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 bg-neutral-900 hover:bg-black text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 cursor-pointer">
                    <iconify-icon icon="lucide:check-circle" class="text-sm"></iconify-icon>
                    Phân phát tem
                </button>
            </form>

            @if($assignedStampsCount > 0)
            <div class="mt-3">
                <button type="button" onclick="toggleDistributionList()" class="w-full py-2 px-4 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 transition-colors duration-200 cursor-pointer">
                    <iconify-icon icon="lucide:users" class="text-sm"></iconify-icon>
                    Xem người đã nhận & số lượng
                </button>
                <div id="distribution-list-details" class="hidden mt-3 p-3 bg-neutral-50 rounded-lg border border-neutral-100 space-y-2 text-xs">
                    @foreach($manufacture->stampDistributions as $dist)
                    <div class="flex justify-between items-center py-1.5 border-b border-neutral-200 last:border-b-0">
                        <span class="font-medium text-neutral-700">{{ $dist->worker->name ?? 'Không rõ' }}</span>
                        <span class="font-bold text-neutral-900 bg-neutral-200 px-2 py-0.5 rounded-full">{{ $dist->quantity }} tem</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($assignedStampsCount > 0)
            <div class="mt-4 pt-4 border-t border-neutral-100">
                <form action="{{ route('manufactures.assign-stamps', $manufacture) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn thu hồi toàn bộ tem đã phân phát cho nhân viên?')">
                    @csrf
                    <input type="hidden" name="action" value="reset_all">
                    <button type="submit" class="w-full py-2 px-4 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 border border-danger-200 text-danger-600 hover:bg-danger-50 transition-colors duration-200 cursor-pointer">
                        <iconify-icon icon="lucide:rotate-ccw" class="text-sm"></iconify-icon>
                        Thu hồi toàn bộ tem đã phát
                    </button>
                </form>
            </div>
            @endif
        </div>
        @endif

        {{-- Thông tin lệnh --}}
        <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
            <h6 class="font-bold text-sm text-neutral-800 uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-neutral-100 pb-3">
                <iconify-icon icon="lucide:info" class="text-lg text-primary-600"></iconify-icon>
                Thông tin lệnh
            </h6>
            
            <div class="space-y-4 text-sm">
                <div class="flex justify-between items-center py-2 border-b border-neutral-50">
                    <span class="text-neutral-500 font-medium">Đơn hàng ghép:</span>
                    <span class="font-semibold text-neutral-800 flex flex-wrap justify-end gap-1 max-w-[200px]">
                        @foreach($manufacture->orders as $o)
                        <a href="{{ route('orders.show', $o) }}" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-neutral-100 hover:bg-neutral-200 text-neutral-700 transition-colors">
                            {{ $o->order_code }}
                        </a>
                        @endforeach
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-neutral-50">
                    <span class="text-neutral-500 font-medium">Tổng số tem:</span>
                    <span class="font-bold text-neutral-800 text-base">{{ $totalItemsCount }}</span>
                </div>
                <div class="py-2">
                    <span class="text-neutral-500 font-medium block mb-1">Ghi chú lệnh:</span>
                    <p class="text-neutral-700 bg-neutral-50 rounded-lg p-3 text-xs leading-relaxed italic m-0 border border-neutral-100">
                        {{ $manufacture->notes ?: 'Không có ghi chú' }}
                    </p>
                </div>
                
                @can('edit manufacture')
                <div class="pt-2">
                    <a href="{{ route('manufactures.edit', $manufacture) }}" class="w-full py-2 px-4 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 border border-neutral-200 text-neutral-700 hover:bg-neutral-50 transition-colors">
                        <iconify-icon icon="lucide:edit" class="text-sm"></iconify-icon>
                        Chỉnh sửa lệnh ghép
                    </a>
                </div>
                @endcan
            </div>
        </div>

        {{-- Lịch sử duyệt --}}
        <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
            <h6 class="font-bold text-sm text-neutral-800 uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-neutral-100 pb-3">
                <iconify-icon icon="lucide:history" class="text-lg text-primary-600"></iconify-icon>
                Lịch sử duyệt
            </h6>
            
            <div class="space-y-4">
                {{-- 1. Kỹ thuật duyệt --}}
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $manufacture->techApprover ? 'bg-success-50 text-success-600' : 'bg-neutral-100 text-neutral-400' }}">
                        <iconify-icon icon="lucide:shield" class="text-base"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-sm font-semibold block text-neutral-800">Kỹ thuật duyệt</span>
                        @if($manufacture->techApprover)
                            <span class="text-xs text-neutral-600 block">{{ $manufacture->techApprover->name }}</span>
                            <span class="text-[10px] text-neutral-400 block">{{ $manufacture->tech_approved_at->format('d/m/Y H:i') }}</span>
                        @else
                            <span class="text-xs text-neutral-400 block italic">Chưa duyệt</span>
                        @endif
                    </div>
                </div>

                {{-- 2. Quản đốc duyệt --}}
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $manufacture->managerApprover ? 'bg-success-50 text-success-600' : 'bg-neutral-100 text-neutral-400' }}">
                        <iconify-icon icon="lucide:user" class="text-base"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-sm font-semibold block text-neutral-800">Quản đốc duyệt</span>
                        @if($manufacture->managerApprover)
                            <span class="text-xs text-neutral-600 block">{{ $manufacture->managerApprover->name }}</span>
                            <span class="text-[10px] text-neutral-400 block">{{ $manufacture->manager_approved_at->format('d/m/Y H:i') }}</span>
                        @else
                            <span class="text-xs text-neutral-400 block italic">Chưa duyệt</span>
                        @endif
                    </div>
                </div>

                {{-- 3. Nhận tem sản xuất --}}
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $manufacture->stampsReceiver ? 'bg-success-50 text-success-600' : 'bg-neutral-100 text-neutral-400' }}">
                        <iconify-icon icon="lucide:tag" class="text-base"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-sm font-semibold block text-neutral-800">Nhận tem sản xuất</span>
                        @if($manufacture->stampsReceiver)
                            <span class="text-xs text-neutral-600 block">{{ $manufacture->stampsReceiver->name }}</span>
                            <span class="text-[10px] text-neutral-400 block">{{ $manufacture->stamps_received_at->format('d/m/Y H:i') }}</span>
                        @else
                            <span class="text-xs text-neutral-400 block italic">Chưa nhận</span>
                        @endif
                    </div>
                </div>

                {{-- 4. Bắt đầu sản xuất --}}
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $manufacture->productionStarter ? 'bg-success-50 text-success-600' : 'bg-neutral-100 text-neutral-400' }}">
                        <iconify-icon icon="lucide:cog" class="text-base"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-sm font-semibold block text-neutral-800">Bắt đầu sản xuất</span>
                        @if($manufacture->productionStarter)
                            <span class="text-xs text-neutral-600 block">{{ $manufacture->productionStarter->name }}</span>
                            <span class="text-[10px] text-neutral-400 block">{{ $manufacture->production_started_at->format('d/m/Y H:i') }}</span>
                        @else
                            <span class="text-xs text-neutral-400 block italic">Chưa bắt đầu</span>
                        @endif
                    </div>
                </div>

                {{-- 5. Hoàn thành --}}
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $manufacture->completer ? 'bg-success-50 text-success-600' : 'bg-neutral-100 text-neutral-400' }}">
                        <iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-sm font-semibold block text-neutral-800">Hoàn thành lệnh</span>
                        @if($manufacture->completer)
                            <span class="text-xs text-neutral-600 block">{{ $manufacture->completer->name }}</span>
                            <span class="text-[10px] text-neutral-400 block">{{ $manufacture->completed_at->format('d/m/Y H:i') }}</span>
                        @else
                            <span class="text-xs text-neutral-400 block italic">Chưa hoàn thành</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script src="{{ asset('assets/js/nesting-export.js') }}"></script>
<script>
window.moOrdersExportData = @json($acrylicOrdersExportData ?? []);

function toggleDistributionList() {
    const list = document.getElementById('distribution-list-details');
    if (list) {
        list.classList.toggle('hidden');
    }
}

document.addEventListener("DOMContentLoaded", function() {
    if (typeof TomSelect !== "undefined") {
        const workerSelect = document.getElementById("worker_id_select");
        if (workerSelect && !workerSelect.tomselect) {
            new TomSelect(workerSelect, {
                placeholder: "-- Chọn nhân viên --",
                allowEmptyOption: true,
                dropdownParent: "body"
            });
        }
    }
});
</script>
@endsection
