@extends('layout.layout')
@php
    $title    = 'Sắp xếp đơn hàng & Tiến độ';
    $subTitle = 'Theo dõi tiến độ sản xuất chi tiết theo ngày và công đoạn';

    // Tính toán số liệu thống kê tổng thể
    $grandTotalPlates = 0;
    $grandTotalCnc = 0;
    $grandTotalDay1 = 0;
    $grandTotalInProd = 0;
    $grandTotalDay2 = 0;
    $grandTotalRemaining = 0;
    $totalSuppliesCount = 0;

    foreach ($groupedSupplies as $dateGroup) {
        foreach ($dateGroup['supplies'] as $item) {
            $grandTotalPlates += $item['totalCount'];
            $grandTotalCnc += $item['cncCount'];
            $grandTotalDay1 += $item['day1Count'];
            $grandTotalInProd += $item['inProductionCount'];
            $grandTotalDay2 += $item['day2Count'];
            $grandTotalRemaining += $item['remainingCount'];
            $totalSuppliesCount++;
        }
    }

    $grandTotalCompleted = $grandTotalDay1 + $grandTotalDay2;
    $completionRate = $grandTotalPlates > 0 ? round(($grandTotalCompleted / $grandTotalPlates) * 100, 1) : 0;
@endphp

@section('content')

{{-- KHỐI KPI TỔNG QUAN TIẾN ĐỘ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    
    {{-- 1. Tổng tấm --}}
    <div class="card border border-neutral-200 rounded-2xl p-5 bg-white shadow-none hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Tổng tấm theo dõi</span>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <iconify-icon icon="solar:layers-minimalistic-bold-duotone" class="text-2xl"></iconify-icon>
            </div>
        </div>
        <div class="flex items-baseline gap-2">
            <h4 class="text-2xl font-bold text-neutral-900 mb-0">{{ number_format($grandTotalPlates) }}</h4>
            <span class="text-xs text-neutral-500 font-medium">tấm</span>
        </div>
        <div class="mt-3 pt-3 border-t border-neutral-100 flex items-center justify-between text-xs text-neutral-500">
            <span>{{ $totalSuppliesCount }} hạng mục</span>
            <span class="text-neutral-700 font-semibold">{{ count($groupedSupplies) }} ngày</span>
        </div>
    </div>

    {{-- 2. Kính / CNC --}}
    <div class="card border border-neutral-200 rounded-2xl p-5 bg-white shadow-none hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Gia công Kính / CNC</span>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <iconify-icon icon="solar:scissors-bold-duotone" class="text-2xl"></iconify-icon>
            </div>
        </div>
        <div class="flex items-baseline gap-2">
            <h4 class="text-2xl font-bold text-neutral-900 mb-0">{{ number_format($grandTotalCnc) }}</h4>
            <span class="text-xs text-neutral-500 font-medium">tấm CNC</span>
        </div>
        <div class="mt-3 pt-3 border-t border-neutral-100 text-xs text-neutral-500">
            <span>Chiếm: <strong class="text-amber-600 font-semibold">{{ $grandTotalPlates > 0 ? round(($grandTotalCnc / $grandTotalPlates) * 100, 1) : 0 }}%</strong> tổng tấm</span>
        </div>
    </div>

    {{-- 3. Đang sản xuất --}}
    <div class="card border border-neutral-200 rounded-2xl p-5 bg-white shadow-none hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Đang sản xuất</span>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <iconify-icon icon="solar:hourglass-line-bold-duotone" class="text-2xl"></iconify-icon>
            </div>
        </div>
        <div class="flex items-baseline gap-2">
            <h4 class="text-2xl font-bold text-blue-600 mb-0">{{ number_format($grandTotalInProd) }}</h4>
            <span class="text-xs text-neutral-500 font-medium">tấm trên chuyền</span>
        </div>
        <div class="mt-3 pt-3 border-t border-neutral-100 text-xs text-neutral-500">
            <span>Đang quét các công đoạn</span>
        </div>
    </div>

    {{-- 4. Đã hoàn thành --}}
    <div class="card border border-neutral-200 rounded-2xl p-5 bg-white shadow-none hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Đã hoàn thành</span>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <iconify-icon icon="solar:check-circle-bold-duotone" class="text-2xl"></iconify-icon>
            </div>
        </div>
        <div class="flex items-baseline gap-2">
            <h4 class="text-2xl font-bold text-emerald-600 mb-0">{{ number_format($grandTotalCompleted) }}</h4>
            <span class="text-xs font-bold text-emerald-600">({{ $completionRate }}%)</span>
        </div>
        <div class="mt-3">
            <div class="w-full bg-neutral-100 h-2 rounded-full overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-full rounded-full transition-all duration-500" style="width: {{ min(100, $completionRate) }}%"></div>
            </div>
        </div>
    </div>

    {{-- 5. Còn lại --}}
    <div class="card border border-neutral-200 rounded-2xl p-5 bg-white shadow-none hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Tấm còn lại</span>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <iconify-icon icon="solar:danger-triangle-bold-duotone" class="text-2xl"></iconify-icon>
            </div>
        </div>
        <div class="flex items-baseline gap-2">
            <h4 class="text-2xl font-bold {{ $grandTotalRemaining > 0 ? 'text-rose-600' : 'text-emerald-600' }} mb-0">
                {{ number_format($grandTotalRemaining) }}
            </h4>
            <span class="text-xs text-neutral-500 font-medium">chưa xong</span>
        </div>
        <div class="mt-3 pt-3 border-t border-neutral-100 text-xs text-neutral-500">
            <span>{{ $grandTotalRemaining == 0 && $grandTotalPlates > 0 ? 'Đã hoàn tất 100%' : 'Cần đẩy nhanh tiến độ' }}</span>
        </div>
    </div>

</div>

{{-- KHỐI BẢNG TIẾN ĐỘ CHÍNH --}}
<div class="card p-0 rounded-2xl border border-neutral-200 overflow-hidden bg-white shadow-none mb-6">
    
    {{-- Thanh công cụ & Bộ lọc --}}
    <div class="card-header border-b border-neutral-200 bg-white p-5 flex items-center flex-wrap gap-4 justify-between">
        
        {{-- Tìm kiếm & Lọc nhanh ngày --}}
        <div class="flex items-center flex-wrap gap-3">
            <form method="GET" action="{{ route('manufactures.sequence') }}" class="flex items-center flex-wrap gap-3">
                <input type="hidden" name="completion_status" value="{{ $completionStatus }}">
                
                {{-- Ô tìm kiếm --}}
                <div class="relative">
                    <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-base"></iconify-icon>
                    <input type="text" name="search" class="form-control border-neutral-200 rounded-xl pl-9 pr-3 py-2 text-xs w-64 md:w-80 focus:border-primary-500" 
                        placeholder="Tìm khách hàng, mã đơn, mã màu, lệnh..." value="{{ $search }}">
                </div>

                {{-- Chọn ngày trực tiếp --}}
                <div class="flex items-center gap-2">
                    <input type="date" name="date" value="{{ $date }}" class="form-control border-neutral-200 rounded-xl px-3 py-2 text-xs focus:border-primary-500" title="Chọn ngày sản xuất">
                    <button type="submit" class="btn bg-primary-600 hover:bg-primary-700 text-white text-xs px-4 py-2 rounded-xl font-semibold transition">
                        Tìm kiếm
                    </button>
                </div>
            </form>

            {{-- Quick Filter Pills --}}
            <div class="flex items-center gap-1 bg-neutral-100 p-1 rounded-xl">
                <a href="{{ route('manufactures.sequence', ['date' => $date, 'search' => $search]) }}" 
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ empty($completionStatus) ? 'bg-white text-primary-600 shadow-sm' : 'text-neutral-600 hover:text-neutral-900' }}">
                    Tất cả
                </a>
                <a href="{{ route('manufactures.sequence', ['date' => $date, 'search' => $search, 'completion_status' => 'uncompleted']) }}" 
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $completionStatus === 'uncompleted' ? 'bg-white text-warning-700 shadow-sm' : 'text-neutral-600 hover:text-neutral-900' }}">
                    Đang làm
                </a>
                <a href="{{ route('manufactures.sequence', ['date' => $date, 'search' => $search, 'completion_status' => 'completed']) }}" 
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $completionStatus === 'completed' ? 'bg-white text-success-700 shadow-sm' : 'text-neutral-600 hover:text-neutral-900' }}">
                    Đã xong
                </a>
            </div>
        </div>

        {{-- Bên phải: Nút xuất Excel & Xóa lọc --}}
        <div class="flex items-center gap-2">
            @if(!empty($completionStatus) || !empty($date) || !empty($search))
            <a href="{{ route('manufactures.sequence') }}" class="btn bg-danger-50 hover:bg-danger-100 text-danger-600 text-xs px-3 py-2 rounded-xl flex items-center gap-1.5 font-semibold transition" title="Xóa toàn bộ bộ lọc">
                <iconify-icon icon="lucide:x" class="text-base"></iconify-icon>
                Xóa lọc
            </a>
            @endif

            <a href="{{ route('manufactures.sequence.export', ['date' => $date, 'search' => $search, 'completion_status' => $completionStatus]) }}" 
                class="btn bg-emerald-600 hover:bg-emerald-700 text-white text-xs px-4 py-2 rounded-xl flex items-center gap-2 font-semibold shadow-sm transition">
                <iconify-icon icon="solar:file-download-bold-duotone" class="text-lg"></iconify-icon>
                Xuất Excel
            </a>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="alert alert-success bg-success-50 text-success-700 border border-success-200 rounded-xl p-4 m-5 mb-0 flex items-center gap-2">
        <iconify-icon icon="lucide:check-circle" class="text-lg"></iconify-icon>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger bg-danger-50 text-danger-700 border border-danger-200 rounded-xl p-4 m-5 mb-0 flex items-center gap-2">
        <iconify-icon icon="lucide:alert-circle" class="text-lg"></iconify-icon>
        {{ session('error') }}
    </div>
    @endif

    {{-- Table Dữ Liệu --}}
    <div class="card-body p-0">
        <div class="overflow-x-auto scroll-sm">
            <table class="table bordered-table sm-table mb-0 w-full min-w-[1500px] table-auto">
                <thead>
                    <tr class="bg-neutral-50/80 text-neutral-700 border-b border-neutral-200">
                        <th class="w-12 text-center py-3.5 px-3 text-xs font-bold uppercase tracking-wider text-neutral-600">STT</th>
                        <th class="text-left py-3.5 px-4 text-xs font-bold uppercase tracking-wider text-neutral-600">Khách hàng</th>
                        <th class="w-32 text-center py-3.5 px-3 text-xs font-bold uppercase tracking-wider text-neutral-600">Mã đơn</th>
                        <th class="text-left py-3.5 px-4 text-xs font-bold uppercase tracking-wider text-neutral-600">Mã màu / Vật tư</th>
                        <th class="w-24 text-center py-3.5 px-3 text-xs font-bold uppercase tracking-wider text-neutral-600">Tổng tấm</th>
                        <th class="w-24 text-center py-3.5 px-3 text-xs font-bold uppercase tracking-wider text-neutral-600">Kính / CNC</th>
                        <th class="w-32 text-center py-3.5 px-3 text-xs font-bold uppercase tracking-wider text-amber-700 bg-amber-50/40">Đã xong N-1</th>
                        <th class="w-32 text-center py-3.5 px-3 text-xs font-bold uppercase tracking-wider text-blue-700 bg-blue-50/40">Đang sản xuất</th>
                        <th class="w-32 text-center py-3.5 px-3 text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50/40">Đã xong N</th>
                        <th class="w-28 text-center py-3.5 px-3 text-xs font-bold uppercase tracking-wider text-rose-700 bg-rose-50/40">Còn lại</th>
                        <th class="w-36 text-center py-3.5 px-3 text-xs font-bold uppercase tracking-wider text-neutral-600">Thời gian chốt</th>
                        <th class="w-24 text-center py-3.5 px-3 text-xs font-bold uppercase tracking-wider text-neutral-600">Ngày hẹn</th>
                        <th class="w-36 text-center py-3.5 px-3 text-xs font-bold uppercase tracking-wider text-neutral-600">Hạn giao (Deadline)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    
                    @forelse($groupedSupplies as $dateGroup)
                        @php
                            $supplies = $dateGroup['supplies'];
                            $totalPlates = 0;
                            $totalCnc = 0;
                            $totalDay1 = 0;
                            $totalInProd = 0;
                            $totalDay2 = 0;
                            $totalRemaining = 0;
                        @endphp

                        {{-- Tiêu đề Banner theo Ngày --}}
                        <tr style="background: linear-gradient(90deg, #f1f5f9 0%, #f8fafc 100%);">
                            <td colspan="13" class="py-3 px-4 border-y border-neutral-200 text-left">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-primary-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                            <iconify-icon icon="solar:calendar-date-bold"></iconify-icon>
                                        </div>
                                        <div>
                                            <span class="text-sm font-bold text-neutral-900 uppercase tracking-wide">
                                                Lệnh sản xuất & Làm đẹp ngày: {{ date('d/m/Y', strtotime($dateGroup['date'])) }}
                                            </span>
                                            <span class="text-xs text-neutral-500 ml-2 font-medium">({{ $supplies->count() }} đơn hàng & hạng mục)</span>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white border border-neutral-200 text-neutral-700 shadow-sm">
                                        Ngày chốt: {{ date('d/m/Y', strtotime($dateGroup['date'])) }}
                                    </span>
                                </div>
                            </td>
                        </tr>

                        {{-- Các dòng đơn hàng trong ngày --}}
                        @foreach($supplies as $itemIndex => $item)
                            @php
                                $order = $item['order'];
                                $supply = $item['supply'];

                                $totalPlates += $item['totalCount'];
                                $totalCnc += $item['cncCount'];
                                $totalDay1 += $item['day1Count'];
                                $totalInProd += $item['inProductionCount'];
                                $totalDay2 += $item['day2Count'];
                                $totalRemaining += $item['remainingCount'];

                                $isOrderDone = ($item['remainingCount'] == 0 && $item['totalCount'] > 0);
                                $isOverdue = $order->deadline && $order->deadline->isPast() && !$isOrderDone;
                            @endphp
                            <tr class="hover:bg-neutral-50/70 transition-colors {{ $isOrderDone ? 'bg-success-50/10' : '' }}">
                                {{-- STT --}}
                                <td class="text-center py-3 px-3 text-xs font-semibold text-neutral-400">
                                    {{ $itemIndex + 1 }}
                                </td>

                                {{-- Khách hàng --}}
                                <td class="py-3 px-4 text-xs font-semibold text-neutral-800">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-neutral-100 text-neutral-600 flex items-center justify-center text-xs font-bold uppercase shrink-0">
                                            {{ mb_substr($order->customer_name ?? 'K', 0, 1) }}
                                        </div>
                                        <span class="truncate">{{ $order->customer_name }}</span>
                                    </div>
                                </td>

                                {{-- Mã đơn --}}
                                <td class="text-center py-3 px-3 whitespace-nowrap">
                                    <div class="flex flex-col items-center gap-1">
                                        <a href="{{ route('orders.show', $order->id) }}" class="text-xs font-bold text-primary-600 hover:underline">
                                            {{ $order->order_code }}
                                        </a>
                                        @php
                                            $typeBadgeClass = match($order->type) {
                                                'glass' => 'bg-cyan-50 text-cyan-700 border border-cyan-200',
                                                'min_late' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                                default => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
                                            };
                                            $typeLabel = match($order->type) {
                                                'glass' => 'Kính',
                                                'min_late' => 'Min-Late',
                                                default => 'Acrylic',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $typeBadgeClass }}">
                                            {{ $typeLabel }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Mã màu / Vật tư --}}
                                <td class="py-3 px-4 text-xs">
                                    <span class="font-mono font-semibold text-neutral-800 bg-neutral-100 px-2 py-1 rounded-md border border-neutral-200 inline-block">
                                        {{ $supply->order_supply_code ?? '—' }}
                                    </span>
                                    @if(!empty($supply->supply_name))
                                        <span class="text-neutral-500 text-xs block mt-1 truncate">{{ $supply->supply_name }}</span>
                                    @endif
                                </td>

                                {{-- Tổng tấm --}}
                                <td class="text-center py-3 px-3 text-xs font-bold text-neutral-900 bg-neutral-50/30">
                                    {{ $item['totalCount'] }}
                                </td>

                                {{-- Kính / CNC --}}
                                <td class="text-center py-3 px-3 text-xs">
                                    @if($item['cncCount'] > 0)
                                        <span class="font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                            {{ $item['cncCount'] }}
                                        </span>
                                    @else
                                        <span class="text-neutral-300">—</span>
                                    @endif
                                </td>

                                {{-- Đã xong N-1 --}}
                                <td class="text-center py-3 px-3 text-xs font-bold text-amber-700 bg-amber-50/20">
                                    {{ $item['day1Count'] > 0 ? $item['day1Count'] : '—' }}
                                </td>

                                {{-- Đang sản xuất --}}
                                <td class="text-center py-3 px-3 text-xs font-bold text-blue-700 bg-blue-50/20">
                                    {{ $item['inProductionCount'] > 0 ? $item['inProductionCount'] : '—' }}
                                </td>

                                {{-- Đã xong N --}}
                                <td class="text-center py-3 px-3 text-xs font-bold text-emerald-700 bg-emerald-50/20">
                                    {{ $item['day2Count'] > 0 ? $item['day2Count'] : '—' }}
                                </td>

                                {{-- Còn lại --}}
                                <td class="text-center py-3 px-3 text-xs">
                                    @if($isOrderDone)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                            <iconify-icon icon="lucide:check" class="text-sm"></iconify-icon> Xong
                                        </span>
                                    @else
                                        <span class="font-bold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-md border border-rose-200 inline-block">
                                            {{ $item['remainingCount'] }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Thời gian chốt --}}
                                <td class="text-center py-3 px-3 text-xs text-neutral-500 whitespace-nowrap">
                                    {{ $order->order_date ? $order->order_date->format('d/m/Y H:i') : '—' }}
                                </td>

                                {{-- Ngày hẹn --}}
                                <td class="text-center py-3 px-3 text-xs text-neutral-700 font-semibold">
                                    {{ $order->delivery_days ? $order->delivery_days . ' ngày' : '—' }}
                                </td>

                                {{-- Hạn giao (Deadline) --}}
                                <td class="text-center py-3 px-3 text-xs whitespace-nowrap">
                                    @if($order->deadline)
                                        <div class="flex flex-col items-center">
                                            <span class="font-semibold {{ $isOverdue ? 'text-danger-600' : 'text-neutral-700' }}">
                                                {{ $order->deadline->format('H:i d/m/Y') }}
                                            </span>
                                            @if($isOverdue)
                                                <span class="text-[10px] font-bold text-danger-600 bg-danger-50 px-1.5 py-0.2 rounded mt-0.5">Quá hạn</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-neutral-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        {{-- Dòng Tổng Cộng Cho Ngày --}}
                        <tr class="bg-neutral-100/70 font-bold border-b border-neutral-300 text-neutral-900 text-xs">
                            <td colspan="4" class="text-left py-3 px-4 font-bold text-neutral-800">
                                Tổng cộng ngày {{ date('d/m/Y', strtotime($dateGroup['date'])) }} ({{ $supplies->count() }} đơn)
                            </td>
                            <td class="text-center py-3 px-3 font-bold text-neutral-900 bg-neutral-200/40">
                                {{ number_format($totalPlates) }}
                            </td>
                            <td class="text-center py-3 px-3 font-bold text-amber-800">
                                {{ number_format($totalCnc) }}
                            </td>
                            <td class="text-center py-3 px-3 font-bold text-amber-800 bg-amber-100/40">
                                {{ number_format($totalDay1) }}
                            </td>
                            <td class="text-center py-3 px-3 font-bold text-blue-800 bg-blue-100/40">
                                {{ number_format($totalInProd) }}
                            </td>
                            <td class="text-center py-3 px-3 font-bold text-emerald-800 bg-emerald-100/40">
                                {{ number_format($totalDay2) }}
                            </td>
                            <td class="text-center py-3 px-3 font-bold text-rose-800 bg-rose-100/40">
                                {{ number_format($totalRemaining) }}
                            </td>
                            <td colspan="3" class="text-right py-3 px-4 text-xs font-semibold text-neutral-500">
                                Tiến độ ngày: {{ $totalPlates > 0 ? round((($totalDay1 + $totalDay2) / $totalPlates) * 100, 1) : 0 }}%
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="13" class="text-center py-12 text-neutral-400">
                                <div class="w-16 h-16 mx-auto mb-3 bg-neutral-100 rounded-full flex items-center justify-center text-neutral-400 text-3xl">
                                    <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
                                </div>
                                <h6 class="text-sm font-bold text-neutral-700 mb-1">Không tìm thấy dữ liệu phù hợp</h6>
                                <p class="text-xs text-neutral-400 mb-4">Vui lòng thay đổi từ khóa tìm kiếm hoặc bỏ chọn bộ lọc ngày</p>
                                <a href="{{ route('manufactures.sequence') }}" class="btn btn-sm bg-primary-50 text-primary-600 hover:bg-primary-100 font-semibold px-4 py-2 rounded-xl text-xs transition">
                                    Tải lại toàn bộ dữ liệu
                                </a>
                            </td>
                        </tr>
                    @endforelse

                </tbody>

                {{-- Tổng Kết Toàn Bộ (Grand Total Footer) --}}
                @if(count($groupedSupplies) > 0)
                <tfoot>
                    <tr class="bg-neutral-900 text-white font-bold text-xs border-t-2 border-primary-500">
                        <td colspan="4" class="text-left py-4 px-4 uppercase tracking-wider text-yellow-400 font-bold">
                            TỔNG CỘNG TẤT CẢ CÁC NGÀY ({{ count($groupedSupplies) }} ngày / {{ $totalSuppliesCount }} hạng mục)
                        </td>
                        <td class="text-center py-4 px-3 font-bold text-base text-yellow-300">
                            {{ number_format($grandTotalPlates) }}
                        </td>
                        <td class="text-center py-4 px-3 font-bold text-base text-amber-300">
                            {{ number_format($grandTotalCnc) }}
                        </td>
                        <td class="text-center py-4 px-3 font-bold text-base text-amber-200 bg-white/5">
                            {{ number_format($grandTotalDay1) }}
                        </td>
                        <td class="text-center py-4 px-3 font-bold text-base text-blue-200 bg-white/5">
                            {{ number_format($grandTotalInProd) }}
                        </td>
                        <td class="text-center py-4 px-3 font-bold text-base text-emerald-300 bg-white/5">
                            {{ number_format($grandTotalDay2) }}
                        </td>
                        <td class="text-center py-4 px-3 font-bold text-base {{ $grandTotalRemaining > 0 ? 'text-rose-400' : 'text-emerald-400' }} bg-white/5">
                            {{ number_format($grandTotalRemaining) }}
                        </td>
                        <td colspan="3" class="text-right py-4 px-4 text-neutral-300 font-semibold">
                            Tỷ lệ hoàn thành tổng thể: <strong class="text-emerald-400 font-bold text-sm">{{ $completionRate }}%</strong>
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

@endsection
