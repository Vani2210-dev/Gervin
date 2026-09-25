@extends('layout.layout')

@php
    $title = 'Báo cáo doanh thu';
    $subTitle = 'Báo cáo doanh số SaleAdmin theo ngày';
@endphp

@section('content')

    {{-- Thanh Điều Hướng & Bộ Lọc --}}
    <div class="card border border-neutral-200 rounded-xl mb-4 shadow-sm bg-white">
        <div class="card-body p-3.5 flex flex-col md:flex-row items-center justify-between gap-3">

            {{-- Phía Trái: Tiêu Đề Báo Cáo Trực Quan Theo Chế Độ Xem --}}
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl {{ $viewMode === 'month' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : ($viewMode === 'range' ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'bg-primary-50 text-primary-600 border border-primary-200') }} flex items-center justify-center shrink-0 shadow-xs">
                    <iconify-icon icon="{{ $viewMode === 'month' ? 'solar:chart-2-bold-duotone' : ($viewMode === 'range' ? 'solar:calendar-minimalistic-bold-duotone' : 'solar:calendar-date-bold-duotone') }}" class="text-xl"></iconify-icon>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-sm md:text-base font-bold text-neutral-900 m-0">
                            @if($viewMode === 'month')
                                Báo cáo doanh thu Tháng {{ $month }}/{{ $year }}
                            @elseif($viewMode === 'range')
                                Báo cáo doanh thu từ ngày {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} đến {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
                            @else
                                Báo cáo doanh thu ngày {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                            @endif
                            @if($selectedStaffUser)
                                <span class="text-primary-600 font-bold"> - {{ $selectedStaffUser->name }}</span>
                            @endif
                        </h4>
                        @if($viewMode === 'month')
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-md bg-emerald-100/90 text-emerald-800 border border-emerald-300">
                                <iconify-icon icon="solar:pie-chart-2-bold-duotone" class="text-xs"></iconify-icon>
                                Tổng Cả Tháng
                            </span>
                        @elseif($viewMode === 'range')
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-md bg-blue-100/90 text-blue-800 border border-blue-300">
                                <iconify-icon icon="solar:calendar-minimalistic-bold-duotone" class="text-xs"></iconify-icon>
                                Khoảng Ngày ({{ $daysInRange }} ngày)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200">
                                <iconify-icon icon="solar:calendar-date-bold-duotone" class="text-xs"></iconify-icon>
                                Theo Ngày
                            </span>
                        @endif
                    </div>
                    <p class="text-[11px] text-neutral-500 m-0 mt-0.5">
                        @if($selectedStaffUser)
                            Báo cáo danh sách khách hàng phụ trách bởi <strong class="text-neutral-700 font-semibold">{{ $selectedStaffUser->name }}</strong>{{ $selectedStaffUser->user_code ? ' (' . $selectedStaffUser->user_code . ')' : '' }}
                        @else
                            @if($viewMode === 'month')
                                Tổng hợp doanh số, tiền về và công nợ lũy kế cả tháng {{ $month }}/{{ $year }}
                            @elseif($viewMode === 'range')
                                Thống kê doanh số, tiền về và công nợ từ ngày {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} đến {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }} ({{ $daysInRange }} ngày)
                            @else
                                Thống kê chi tiết doanh số phát sinh và công nợ trong ngày {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                            @endif
                        @endif
                    </p>
                </div>
            </div>

            {{-- Phía Phải: Bộ lọc ngày/tháng nhanh, Nút Lọc, Xóa Lọc, Cài Đặt, Nút Xuất Excel --}}
            <div class="flex items-center gap-2 flex-wrap">

                {{-- Bộ lọc Ngày / Tháng linh hoạt --}}
                <x-flexible-date-filter :dateMode="$dateMode" :dateVal="$dateVal" :showAll="false" :showYear="false" />

                {{-- Nút Lọc chuẩn theme template --}}
                <button type="button" onclick="openModal('revenue-filter-modal')"
                    class="btn bg-light-600 text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                    <iconify-icon icon="solar:filter-outline" class="icon text-xl line-height-1"></iconify-icon>
                    Lọc
                </button>

                {{-- Nút Xóa lọc --}}
                @if($isFiltered)
                    <a href="{{ route('reports.revenue') }}" class="btn text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="solar:close-circle-outline" class="icon text-xl line-height-1"></iconify-icon>
                        Xóa lọc
                    </a>
                @endif

                {{-- Nút Cài đặt (Chỉ dành cho Admin) --}}
                @if(auth()->user()->hasRole('Admin'))
                    <button type="button" onclick="openModal('set-target-modal')" class="btn bg-light-600 text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2"
                        title="Cài đặt Mục tiêu Doanh thu Tháng {{ $month }}">
                        <iconify-icon icon="solar:settings-outline" class="icon text-xl line-height-1"></iconify-icon>
                    </button>
                @endif

                {{-- Nút Xuất Excel Mở Modal --}}
                <button type="button" onclick="openModal('export-revenue-modal')"
                    class="btn bg-success-600 hover:bg-success-700 text-white text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2"
                    title="Tùy chọn xuất file Excel báo cáo doanh thu">
                    <iconify-icon icon="solar:file-download-outline" class="icon text-xl line-height-1"></iconify-icon>
                    Xuất Excel
                </button>
            </div>

        </div>
    </div>

    {{-- Khối Các Card Thông Tin Lẻ --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3.5 mb-5 kpi-cards-grid">

        {{-- Card 1: Mục tiêu doanh thu tháng --}}
        <div
            class="kpi-card bg-white border border-neutral-200/90 rounded-2xl p-4 shadow-xs relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">Mục tiêu DT tháng</span>
                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <iconify-icon icon="solar:target-bold-duotone" class="text-base"></iconify-icon>
                </div>
            </div>
            <div class="text-base font-black text-neutral-900 leading-tight">
                {{ number_format($targetMonth, 0, ',', '.') }} đ
            </div>
        </div>

        {{-- Card 2: Mục tiêu doanh thu ngày (nếu xem theo ngày) HOẶC DT Thực tế (nếu xem khoảng ngày / tháng) --}}
        <div
            class="kpi-card bg-white border border-neutral-200/90 rounded-2xl p-4 shadow-xs relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                    {{ $viewMode === 'day' ? 'Mục tiêu DT ngày' : 'Doanh thu thực tế' }}
                </span>
                <div class="w-7 h-7 rounded-lg {{ $viewMode === 'day' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center shrink-0">
                    <iconify-icon icon="{{ $viewMode === 'day' ? 'solar:hourglass-line-bold-duotone' : 'solar:chart-square-bold-duotone' }}" class="text-base"></iconify-icon>
                </div>
            </div>
            <div class="text-base font-black {{ $viewMode === 'day' ? 'text-neutral-900' : 'text-emerald-700' }} leading-tight">
                {{ $viewMode === 'day' ? number_format($targetDay, 0, ',', '.') : number_format($dayTotalRevenue, 0, ',', '.') }} đ
            </div>
        </div>

        {{-- Card 3: Vượt (+) / Thiếu (-) --}}
        <div
            class="kpi-card bg-white border border-neutral-200/90 rounded-2xl p-4 shadow-xs relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                    {{ $viewMode === 'month' ? 'Vượt / Thiếu Tháng' : 'Vượt / Thiếu' }}
                </span>
                <div
                    class="w-7 h-7 rounded-lg {{ $dayDiff >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center shrink-0">
                    <iconify-icon
                        icon="{{ $dayDiff >= 0 ? 'solar:graph-up-bold-duotone' : 'solar:graph-down-bold-duotone' }}"
                        class="text-base"></iconify-icon>
                </div>
            </div>
            <div class="text-base font-black {{ $dayDiff >= 0 ? 'text-emerald-600' : 'text-rose-600' }} leading-tight">
                {{ $dayDiff >= 0 ? '+' : '' }}{{ number_format($dayDiff, 0, ',', '.') }} đ
            </div>
        </div>

        {{-- Card 4: % Đạt doanh thu định mức --}}
        <div
            class="kpi-card bg-white border border-neutral-200/90 rounded-2xl p-4 shadow-xs relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                    {{ $viewMode === 'month' ? '% Đạt DT Tháng' : '% Đạt định mức' }}
                </span>
                <div
                    class="w-7 h-7 rounded-lg {{ $dayPercent >= 100 ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }} flex items-center justify-center shrink-0">
                    <iconify-icon icon="solar:pie-chart-2-bold-duotone" class="text-base"></iconify-icon>
                </div>
            </div>
            <div class="text-xl font-black {{ $dayPercent >= 100 ? 'text-emerald-600' : 'text-amber-600' }} leading-none">
                {{ number_format($dayPercent, 0) }}%
            </div>
        </div>

        {{-- Card 5: Số ĐH lên đơn --}}
        <div
            class="kpi-card bg-white border border-neutral-200/90 rounded-2xl p-4 shadow-xs relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                    {{ $viewMode === 'month' ? 'Tổng ĐH trong tháng' : ($viewMode === 'range' ? 'Tổng ĐH' : 'Số ĐH lên đơn') }}
                </span>
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <iconify-icon icon="solar:clipboard-list-bold-duotone" class="text-base"></iconify-icon>
                </div>
            </div>
            <div class="text-xl font-black text-neutral-900 leading-none">
                {{ $dayOrdersCount }} <span class="text-xs font-semibold text-neutral-500">đơn</span>
            </div>
        </div>

        {{-- Card 6: Số KH có đơn --}}
        <div
            class="kpi-card bg-white border border-neutral-200/90 rounded-2xl p-4 shadow-xs relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                    {{ $viewMode === 'month' ? 'Tổng KH có đơn' : 'Số KH có đơn' }}
                </span>
                <div class="w-7 h-7 rounded-lg bg-cyan-50 text-cyan-600 flex items-center justify-center shrink-0">
                    <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="text-base"></iconify-icon>
                </div>
            </div>
            <div class="text-xl font-black text-neutral-900 leading-none">
                {{ $dayCustomersCount }} <span class="text-xs font-semibold text-neutral-500">khách</span>
            </div>
        </div>

        {{-- Card 7: Ngày > 2 đơn --}}
        <div
            class="kpi-card bg-white border border-neutral-200/90 rounded-2xl p-4 shadow-xs relative overflow-hidden flex flex-col justify-between">
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">
                    {{ $viewMode === 'month' ? 'KH có ≥ 2 đơn' : ($viewMode === 'range' ? 'KH có ≥ 2 đơn' : 'Ngày > 2 đơn') }}
                </span>
                <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <iconify-icon icon="solar:clipboard-check-bold-duotone" class="text-base"></iconify-icon>
                </div>
            </div>
            <div class="text-xl font-black text-neutral-900 leading-none">
                {{ $dayOrdersMoreThan2Count }} <span class="text-xs font-semibold text-neutral-500">khách</span>
            </div>
        </div>

        {{-- Card 8: Số KH mở mới --}}
        <a href="{{ route('reports.revenue', array_merge(request()->query(), ['segment' => $segment === 'new' ? 'all' : 'new'])) }}"
            class="kpi-card bg-white border {{ $segment === 'new' ? 'border-emerald-600 ring-2 ring-emerald-500/20 bg-emerald-50/30' : 'border-neutral-200/90 hover:border-emerald-500' }} hover:shadow-sm transition-all cursor-pointer rounded-2xl p-4 shadow-xs relative overflow-hidden flex flex-col justify-between block"
            title="{{ $segment === 'new' ? 'Bấm để bỏ lọc' : 'Bấm để lọc danh sách KH mở mới' }}">
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">Số KH mở mới</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <iconify-icon icon="solar:user-plus-bold-duotone" class="text-base"></iconify-icon>
                </div>
            </div>
            <div class="text-xl font-black text-emerald-700 leading-none">
                {{ $totalNewCust }} <span class="text-xs font-semibold text-neutral-500">khách</span>
            </div>
        </a>

        {{-- Card 9: Số KH quay lại --}}
        <a href="{{ route('reports.revenue', array_merge(request()->query(), ['segment' => $segment === 'returning' ? 'all' : 'returning'])) }}"
            class="kpi-card bg-white border {{ $segment === 'returning' ? 'border-blue-600 ring-2 ring-blue-500/20 bg-blue-50/30' : 'border-neutral-200/90 hover:border-blue-500' }} hover:shadow-sm transition-all cursor-pointer rounded-2xl p-4 shadow-xs relative overflow-hidden flex flex-col justify-between block"
            title="{{ $segment === 'returning' ? 'Bấm để bỏ lọc' : 'Bấm để lọc danh sách KH quay lại' }}">
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">Số KH quay lại</span>
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <iconify-icon icon="solar:user-speak-bold-duotone" class="text-base"></iconify-icon>
                </div>
            </div>
            <div class="text-xl font-black text-blue-700 leading-none">
                {{ $totalReturningCust }} <span class="text-xs font-semibold text-neutral-500">khách</span>
            </div>
        </a>

        {{-- Card 10: Số KH mất đi --}}
        <a href="{{ route('reports.revenue', array_merge(request()->query(), ['segment' => $segment === 'lost' ? 'all' : 'lost'])) }}"
            class="kpi-card bg-white border {{ $segment === 'lost' ? 'border-rose-600 ring-2 ring-rose-500/20 bg-rose-50/30' : 'border-neutral-200/90 hover:border-rose-500' }} hover:shadow-sm transition-all cursor-pointer rounded-2xl p-4 shadow-xs relative overflow-hidden flex flex-col justify-between block"
            title="{{ $segment === 'lost' ? 'Bấm để bỏ lọc' : 'Bấm để lọc danh sách KH mất đi' }}">
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">Số KH mất đi</span>
                <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <iconify-icon icon="solar:user-block-bold-duotone" class="text-base"></iconify-icon>
                </div>
            </div>
            <div class="text-xl font-black text-rose-600 leading-none">
                {{ $totalLostCust }} <span class="text-xs font-semibold text-neutral-500">khách</span>
            </div>
        </a>

    </div>

    {{-- Thanh Lọc Nhanh Phân Loại Khách Hàng (Quick Filter Pills - Server-side Links) --}}
    <div class="flex items-center justify-between gap-3 mb-3 flex-wrap">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold text-neutral-600 uppercase tracking-wider flex items-center gap-1.5 mr-1">
                <iconify-icon icon="solar:users-group-rounded-bold-duotone" class="text-base text-primary-600"></iconify-icon>
                Lọc nhóm KH:
            </span>

            {{-- Nút Tất cả --}}
            <a href="{{ route('reports.revenue', array_merge(request()->query(), ['segment' => 'all'])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $segment === 'all' ? 'border border-primary-600 bg-primary-600 text-white shadow-xs' : 'border border-neutral-200 bg-white text-neutral-700 hover:border-neutral-300 hover:bg-neutral-50' }}">
                <span>Tất cả</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $segment === 'all' ? 'bg-white/20 text-white' : 'bg-neutral-100 text-neutral-700' }}">{{ $allRowsCount }}</span>
            </a>

            {{-- Nút KH Mất đi --}}
            <a href="{{ route('reports.revenue', array_merge(request()->query(), ['segment' => 'lost'])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $segment === 'lost' ? 'border border-rose-600 bg-rose-600 text-white shadow-xs' : 'border border-neutral-200 bg-white text-neutral-700 hover:border-rose-300 hover:bg-rose-50/50' }}">
                <span class="w-2 h-2 rounded-full {{ $segment === 'lost' ? 'bg-white' : 'bg-rose-500' }}"></span>
                <span>KH mất đi</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $segment === 'lost' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-700' }}">{{ $totalLostCust }}</span>
            </a>

            {{-- Nút KH Mở mới --}}
            <a href="{{ route('reports.revenue', array_merge(request()->query(), ['segment' => 'new'])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $segment === 'new' ? 'border border-emerald-600 bg-emerald-600 text-white shadow-xs' : 'border border-neutral-200 bg-white text-neutral-700 hover:border-emerald-300 hover:bg-emerald-50/50' }}">
                <span class="w-2 h-2 rounded-full {{ $segment === 'new' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                <span>KH mở mới</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $segment === 'new' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700' }}">{{ $totalNewCust }}</span>
            </a>

            {{-- Nút KH Quay lại --}}
            <a href="{{ route('reports.revenue', array_merge(request()->query(), ['segment' => 'returning'])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $segment === 'returning' ? 'border border-blue-600 bg-blue-600 text-white shadow-xs' : 'border border-neutral-200 bg-white text-neutral-700 hover:border-blue-300 hover:bg-blue-50/50' }}">
                <span class="w-2 h-2 rounded-full {{ $segment === 'returning' ? 'bg-white' : 'bg-blue-500' }}"></span>
                <span>KH quay lại</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $segment === 'returning' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-700' }}">{{ $totalReturningCust }}</span>
            </a>

            {{-- Nút Có phát sinh trong kỳ --}}
            <a href="{{ route('reports.revenue', array_merge(request()->query(), ['segment' => 'active'])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $segment === 'active' ? 'border border-purple-600 bg-purple-600 text-white shadow-xs' : 'border border-neutral-200 bg-white text-neutral-700 hover:border-purple-300 hover:bg-purple-50/50' }}">
                <span class="w-2 h-2 rounded-full {{ $segment === 'active' ? 'bg-white' : 'bg-purple-500' }}"></span>
                <span>Có phát sinh đơn / tiền</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold {{ $segment === 'active' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-700' }}">{{ $activeCustCount }}</span>
            </a>
        </div>

        {{-- Text thông báo số lượng đang hiển thị --}}
        <div class="text-xs text-neutral-500 font-medium">
            Hiển thị: <strong class="text-neutral-900 font-bold">{{ count($tableRows) }}</strong> / {{ $allRowsCount }} khách hàng
        </div>
    </div>

    {{-- Bảng Báo Cáo Doanh Thu Bám Sát File Excel --}}
    <div class="card border border-neutral-300 rounded-xl shadow-sm bg-white overflow-hidden mb-6">
        <div class="table-responsive">
            <table class="table-excel mb-0">
                <thead>
                    {{-- Dòng Header Cột Bảng (Khaki/Grey Rows) --}}
                    <tr
                        class="header-khaki-row text-center font-bold text-[11px] uppercase text-neutral-900 border-b border-neutral-400 bg-stone-200">
                        <th rowspan="2" class="w-10 border-r border-neutral-400">STT</th>
                        <th rowspan="2" class="w-20 border-r border-neutral-400">Mã KH</th>
                        <th rowspan="2" class="w-48 border-r border-neutral-400 text-left pl-2">
                            <div>Khách Hàng</div>
                            @if($selectedStaffUser)
                                <div class="text-[10px] font-normal italic text-neutral-500 normal-case tracking-normal mt-0.5">
                                    (NV: {{ $selectedStaffUser->user_code ? $selectedStaffUser->user_code . ' - ' : '' }}{{ $selectedStaffUser->name }})
                                </div>
                            @endif
                        </th>
                        <th rowspan="2" class="w-28 border-r border-neutral-400 text-center">Công nợ<br>tháng
                            {{ $month - 1 > 0 ? $month - 1 : 12 }}</th>
                        <th rowspan="2" class="w-28 border-r border-neutral-400 text-center">Định mức<br>công nợ</th>

                        {{-- Gộp cột Acrylic --}}
                        <th colspan="2" class="border-r border-neutral-400 bg-stone-300">Tổng doanh số Acrylic</th>

                        <th rowspan="2" class="w-28 border-r border-neutral-400">Doanh số<br>Cánh Kính</th>
                        <th rowspan="2" class="w-32 border-r border-neutral-400">Doanh số đơn Melamin,<br>Laminate gia công
                        </th>
                        <th rowspan="2" class="w-28 border-r border-neutral-400 font-bold bg-amber-100">
                            {{ $viewMode === 'month' ? 'Tổng tiền về trong tháng' : ($viewMode === 'range' ? 'Tổng tiền về trong kỳ' : 'Tổng tiền về trong ngày') }}
                        </th>
                        <th rowspan="2" class="w-28 border-r border-neutral-400 font-bold">
                            {{ $viewMode === 'month' ? 'Công nợ cuối tháng' : ($viewMode === 'range' ? 'Công nợ cuối kỳ' : 'Công nợ cuối ngày') }}
                        </th>
                        <th class="w-28 border-r border-neutral-400">So sánh với<br>công nợ định mức</th>
                        <th rowspan="2" class="w-24 border-r border-neutral-400">Doanh thu<br>Laminate TM</th>
                        <th rowspan="2" class="w-24">Doanh thu<br>Plywood TM</th>
                    </tr>

                    <tr
                        class="header-khaki-sub-row text-center font-bold text-[10px] uppercase text-neutral-800 border-b border-neutral-300 bg-stone-200">
                        <th class="w-28 border-r border-neutral-400">Đơn chính, foil, chỉ, BS</th>
                        <th class="w-24 border-r border-neutral-400">Đơn bảo hành</th>
                        <th class="w-28 border-r border-neutral-400 font-black text-emerald-900 bg-stone-300">
                            {{ number_format($debtVsPolicyPercent, 0) }}%</th>
                    </tr>

                    {{-- DÒNG MÀU VÀNG NHẠT TỔNG CỘNG (Soft Light Yellow Total Row) --}}
                    <tr class="row-total-day font-bold text-xs">
                        <td class="text-center py-2">—</td>
                        <td class="text-center py-2">—</td>
                        <td class="py-2 pl-2 uppercase font-black">
                            {{ $viewMode === 'month' ? 'TỔNG CỘNG THÁNG' : ($viewMode === 'range' ? 'TỔNG CỘNG KỲ' : 'TỔNG CỘNG NGÀY') }}
                        </td>
                        <td class="py-2 pr-2 text-right font-black">
                            {{ $displayStartMonthDebt > 0 ? number_format($displayStartMonthDebt, 0, ',', '.') : '-' }}
                        <td class="py-2 pr-2 text-right font-black">
                            {{ $displayPolicyDebt > 0 ? number_format($displayPolicyDebt, 0, ',', '.') : '-' }}
                        </td>

                        {{-- Acrylic chính & BH --}}
                        <td class="py-2 pr-2 text-right">
                            {{ $displayAcrylicMain > 0 ? number_format($displayAcrylicMain, 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-2 pr-2 text-right">
                            {{ $displayAcrylicWarranty > 0 ? number_format($displayAcrylicWarranty, 0, ',', '.') : '-' }}
                        </td>

                        {{-- Cánh kính & Min Late --}}
                        <td class="py-2 pr-2 text-right">
                            {{ $displayGlass > 0 ? number_format($displayGlass, 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-2 pr-2 text-right">
                            {{ $displayMinLate > 0 ? number_format($displayMinLate, 0, ',', '.') : '-' }}
                        </td>

                        {{-- Tiền về trong ngày --}}
                        <td class="py-2 pr-2 text-right font-black">
                            {{ $displayPaidInDay > 0 ? number_format($displayPaidInDay, 0, ',', '.') : '-' }}
                        </td>

                        {{-- Công nợ cuối ngày --}}
                        <td class="py-2 pr-2 text-right font-black">
                            {{ $displayEndDayDebt > 0 ? number_format($displayEndDayDebt, 0, ',', '.') : '-' }}
                        </td>

                        {{-- So sánh định mức --}}
                        <td
                            class="py-2 pr-2 text-right {{ $displayDebtVsPolicy < 0 ? 'text-total-danger font-black' : ($displayDebtVsPolicy > 0 ? 'text-total-success font-black' : '') }}">
                            @if($displayPolicyDebt > 0 || $displayEndDayDebt > 0)
                                {{ number_format(abs($displayDebtVsPolicy), 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>

                        {{-- Thương mại --}}
                        <td class="py-2 pr-2 text-right">
                            {{ $displayLaminateCommercial > 0 ? number_format($displayLaminateCommercial, 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-2 pr-2 text-right">
                            {{ $displayPlywoodCommercial > 0 ? number_format($displayPlywoodCommercial, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                </thead>

                {{-- DANH SÁCH KHÁCH HÀNG (Rows) --}}
                <tbody class="divide-y divide-neutral-300 text-[11px] text-neutral-800">
                    @forelse($tableRows as $idx => $row)
                        @php
                            $rowStaffName = !empty($row['users']) && $row['users']->isNotEmpty() ? $row['users']->pluck('name')->implode(', ') : null;
                        @endphp
                        <tr class="bg-white hover:bg-neutral-50 transition-colors">
                            <td class="text-center py-1.5 border-r border-neutral-300 text-neutral-500 font-medium">
                                {{ $idx + 1 }}
                            </td>
                            <td class="text-center py-1.5 border-r border-neutral-300 font-bold text-neutral-900">
                                {{ $row['customer_code'] }}
                            </td>
                            <td class="py-1.5 pl-2 pr-1 border-r border-neutral-300 text-neutral-900">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-neutral-900">{{ $row['customer_name'] }}</span>
                                    @if($row['new_cust'])
                                        <span class="inline-flex items-center px-1.5 py-0.2 text-[9px] font-bold rounded bg-emerald-100 text-emerald-800 border border-emerald-300">Mới</span>
                                    @endif
                                    @if($row['returning_cust'])
                                        <span class="inline-flex items-center px-1.5 py-0.2 text-[9px] font-bold rounded bg-blue-100 text-blue-800 border border-blue-300">Quay lại</span>
                                    @endif
                                    @if($row['lost_cust'])
                                        <span class="inline-flex items-center px-1.5 py-0.2 text-[9px] font-bold rounded bg-rose-100 text-rose-800 border border-rose-300">Mất đi</span>
                                    @endif
                                </div>
                                @if(!empty($row['customer_phone']) || !empty($rowStaffName))
                                    <div class="text-[10px] text-neutral-500 font-normal flex items-center gap-1.5 mt-0.5">
                                        @if(!empty($row['customer_phone']))
                                            <a href="tel:{{ $row['customer_phone'] }}" class="inline-flex items-center gap-0.5 text-primary-600 hover:underline font-semibold" title="Gọi điện cho khách">
                                                <iconify-icon icon="solar:phone-calling-linear" class="text-xs"></iconify-icon>
                                                {{ $row['customer_phone'] }}
                                            </a>
                                        @endif
                                        @if(!empty($row['customer_phone']) && !empty($rowStaffName))
                                            <span>•</span>
                                        @endif
                                        @if(!empty($rowStaffName))
                                            <span class="text-neutral-500 text-[10px] italic">NV: {{ $rowStaffName }}</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="py-1.5 pr-2 text-right border-r border-neutral-300">
                                {{ $row['start_month_debt'] > 0 ? number_format($row['start_month_debt'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-1.5 pr-2 text-right border-r border-neutral-300">
                                {{ $row['policy_debt'] > 0 ? number_format($row['policy_debt'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- Acrylic chính & BH --}}
                            <td
                                class="py-1.5 pr-2 text-right border-r border-neutral-300 {{ $row['acrylic_main'] > 0 ? 'text-indigo-700 font-bold' : 'text-neutral-400' }}">
                                {{ $row['acrylic_main'] > 0 ? number_format($row['acrylic_main'], 0, ',', '.') : '-' }}
                            </td>
                            <td
                                class="py-1.5 pr-2 text-right border-r border-neutral-300 {{ $row['acrylic_warranty'] > 0 ? 'text-purple-700 font-bold' : 'text-neutral-400' }}">
                                {{ $row['acrylic_warranty'] > 0 ? number_format($row['acrylic_warranty'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- Cánh kính & Min Late --}}
                            <td
                                class="py-1.5 pr-2 text-right border-r border-neutral-300 {{ $row['glass'] > 0 ? 'text-cyan-700 font-bold' : 'text-neutral-400' }}">
                                {{ $row['glass'] > 0 ? number_format($row['glass'], 0, ',', '.') : '-' }}
                            </td>
                            <td
                                class="py-1.5 pr-2 text-right border-r border-neutral-300 {{ $row['min_late'] > 0 ? 'text-amber-700 font-bold' : 'text-neutral-400' }}">
                                {{ $row['min_late'] > 0 ? number_format($row['min_late'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- Tiền về trong ngày --}}
                            <td
                                class="py-1.5 pr-2 text-right border-r border-neutral-300 {{ $row['paid_in_day'] > 0 ? 'text-emerald-700 font-bold bg-emerald-50' : 'text-neutral-400' }}">
                                {{ $row['paid_in_day'] > 0 ? number_format($row['paid_in_day'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- Công nợ cuối ngày --}}
                            <td class="py-1.5 pr-2 text-right border-r border-neutral-300 font-semibold text-neutral-800">
                                {{ $row['end_day_debt'] > 0 ? number_format($row['end_day_debt'], 0, ',', '.') : '-' }}
                            </td>

                            {{-- So sánh định mức --}}
                            <td
                                class="py-1.5 pr-2 text-right border-r border-neutral-300 {{ $row['debt_vs_policy'] < 0 ? 'text-danger-600 font-bold' : ($row['debt_vs_policy'] > 0 ? 'text-success-600 font-semibold' : 'text-neutral-600') }}">
                                @if($row['policy_debt'] > 0 || $row['end_day_debt'] > 0)
                                    {{ number_format(abs($row['debt_vs_policy']), 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>

                            {{-- Thương mại --}}
                            <td class="py-1.5 pr-2 text-right border-r border-neutral-300 text-neutral-400">
                                {{ $row['laminate_commercial'] > 0 ? number_format($row['laminate_commercial'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="py-1.5 pr-2 text-right text-neutral-400">
                                {{ $row['plywood_commercial'] > 0 ? number_format($row['plywood_commercial'], 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="py-4 px-4 text-neutral-500 text-xs text-center">
                                Chưa có khách hàng nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Thiết lập Báo cáo Doanh thu (Chỉ dành cho Admin) --}}
    @if(auth()->user()->hasRole('Admin'))
        <x-modal name="set-target-modal" maxWidth="md">
            <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
                <h5 class="font-bold text-base text-neutral-900 flex items-center gap-2">
                    <iconify-icon icon="solar:settings-bold-duotone" class="text-primary-600 text-xl"></iconify-icon>
                    <span id="modal-target-title">Thiết lập Báo cáo Doanh thu Tháng {{ $month }}/{{ $year }}</span>
                </h5>
                <button type="button" onclick="closeModal('set-target-modal')"
                    class="text-neutral-400 hover:text-neutral-700 text-xl leading-none">&times;</button>
            </div>
            <form id="set-target-form" onsubmit="handleSaveTarget(event)">
                @csrf
                <div class="p-6 grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Tháng</label>
                        <select name="month" id="target_month" class="form-select rounded-lg text-sm w-full" required>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>Tháng {{ $m }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Năm</label>
                        <input type="number" name="year" id="target_year" value="{{ $year }}" min="2020" max="2099"
                            class="form-control rounded-lg text-sm w-full" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Mục tiêu DT tháng (VNĐ) <span
                                class="text-danger-500">*</span></label>
                        <input type="number" name="target_amount" id="target_amount" value="{{ $targetMonth }}" min="0"
                            step="1000000" class="form-control rounded-lg text-sm w-full font-bold text-primary-700"
                            placeholder="Ví dụ: 500000000" required>
                        <span class="text-[11px] text-neutral-400 mt-1 block">Ví dụ: 500.000.000 đ</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Mục tiêu DT ngày (VNĐ) <span
                                class="text-danger-500">*</span></label>
                        <input type="number" name="target_day_amount" id="target_day_amount" value="{{ round($targetDay) }}"
                            min="0" step="100000" class="form-control rounded-lg text-sm w-full font-bold text-amber-700"
                            placeholder="Ví dụ: 20000000" required>
                        <span class="text-[11px] text-neutral-400 mt-1 block">Tự tính hoặc nhập trực tiếp.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Số tháng tính KH mất đi <span
                                class="text-danger-500">*</span></label>
                        <input type="number" name="lost_months" id="lost_months" value="{{ $lostMonths ?? 6 }}" min="1" max="60"
                            class="form-control rounded-lg text-sm w-full" placeholder="Ví dụ: 6" required>
                        <span class="text-[11px] text-neutral-400 mt-1 block">Mặc định: 6 tháng không có đơn.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Số tháng tính KH quay lại <span
                                class="text-danger-500">*</span></label>
                        <input type="number" name="returning_months" id="returning_months" value="{{ $returningMonths ?? 6 }}"
                            min="1" max="60" class="form-control rounded-lg text-sm w-full" placeholder="Ví dụ: 6" required>
                        <span class="text-[11px] text-neutral-400 mt-1 block">Mặc định: Sau 6 tháng đặt lại đơn.</span>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-neutral-200 flex justify-end gap-3 bg-neutral-50 rounded-b-xl">
                    <button type="button" onclick="closeModal('set-target-modal')"
                        class="btn btn-neutral px-4 py-2 rounded-lg text-sm">Hủy</button>
                    <button type="submit" id="save-target-btn"
                        class="btn btn-primary px-5 py-2 rounded-lg text-sm font-semibold shadow-sm">
                        Lưu Thiết Lập
                    </button>
                </div>
            </form>
        </x-modal>
    @endif

    {{-- Modal Tùy Chọn Xuất Excel --}}
    <x-modal name="export-revenue-modal" maxWidth="lg">
        <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
            <h5 class="font-bold text-base text-neutral-900 flex items-center gap-2">
                <iconify-icon icon="solar:file-download-bold-duotone" class="text-emerald-600 text-xl"></iconify-icon>
                <span>Tùy Chọn Xuất Excel Báo Cáo Doanh Thu</span>
            </h5>
            <button type="button" onclick="closeModal('export-revenue-modal')"
                class="text-neutral-400 hover:text-neutral-700 text-xl leading-none">&times;</button>
        </div>
        <form method="GET" action="{{ route('reports.revenue.export') }}" target="_blank" onsubmit="return validateExportForm(event)">
            @if($filterUserId)<input type="hidden" name="user_id" value="{{ $filterUserId }}">@endif
            @if($search)<input type="hidden" name="search" value="{{ $search }}">@endif

            <div class="p-6 space-y-4">
                <div class="space-y-3">
                    {{-- Tùy chọn 1: Ngày đang xem --}}
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-neutral-200 hover:border-primary-500 hover:bg-primary-50/30 cursor-pointer transition-all">
                        <input type="radio" name="export_type" value="single" checked class="mt-1 text-primary-600 focus:ring-primary-500 cursor-pointer" onchange="handleExportTypeChange(this.value)">
                        <div class="flex-1">
                            <div class="text-sm font-bold text-neutral-800 flex items-center gap-1.5">
                                <iconify-icon icon="solar:calendar-date-bold-duotone" class="text-primary-600 text-base"></iconify-icon>
                                <span>Chỉ xuất ngày đang xem</span>
                            </div>
                            <p class="text-xs text-neutral-500 mt-0.5">Ngày: <strong class="text-neutral-700 font-semibold">{{ $selectedCarbon->format('d/m/Y') }}</strong></p>
                            <input type="hidden" name="date" value="{{ $selectedDate }}">
                        </div>
                    </label>

                    {{-- Tùy chọn 2: Khoảng ngày tùy chọn trong tháng --}}
                    @php
                        $endOfSelectedMonth = $selectedCarbon->copy()->endOfMonth()->toDateString();
                        $defaultExportFromDate = ($viewMode === 'range' && !empty($fromDate))
                            ? $fromDate
                            : $selectedCarbon->copy()->startOfMonth()->toDateString();

                        $defaultExportToDate = ($viewMode === 'range' && !empty($toDate))
                            ? $toDate
                            : ($viewMode === 'month'
                                ? ($endOfSelectedMonth > $todayDate ? $todayDate : $endOfSelectedMonth)
                                : ($selectedDate > $todayDate ? $todayDate : $selectedDate));

                        // Nếu là ngày 1 đầu tháng thì đặt Đến ngày là ngày hôm nay (nếu cùng tháng) hoặc ngày cuối tháng
                        if ($defaultExportToDate === $defaultExportFromDate && $endOfSelectedMonth > $defaultExportFromDate) {
                            $defaultExportToDate = ($endOfSelectedMonth > $todayDate && $selectedCarbon->isCurrentMonth())
                                ? $todayDate
                                : $endOfSelectedMonth;
                        }
                    @endphp
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-neutral-200 hover:border-primary-500 hover:bg-primary-50/30 cursor-pointer transition-all">
                        <input type="radio" name="export_type" value="range" class="mt-1 text-primary-600 focus:ring-primary-500 cursor-pointer" onchange="handleExportTypeChange(this.value)">
                        <div class="flex-1">
                            <div class="text-sm font-bold text-neutral-800 flex items-center gap-1.5">
                                <iconify-icon icon="solar:calendar-minimalistic-bold-duotone" class="text-amber-600 text-base"></iconify-icon>
                                <span>Khoảng ngày trong tháng</span>
                            </div>
                            <p class="text-xs text-neutral-500 mt-0.5">Xuất các ngày liên tiếp từ ngày bắt đầu đến ngày kết thúc.</p>
                            
                            {{-- Input Chọn Khoảng Ngày trong tháng (Cho phép tự do chọn mọi tháng/năm) --}}
                            <div id="export-range-inputs" class="grid grid-cols-2 gap-2.5 mt-3 hidden">
                                <div>
                                    <label class="text-[11px] font-semibold text-neutral-600 mb-1 block">Từ ngày:</label>
                                    <input type="date" name="from_date" id="export_from_date" 
                                        value="{{ $defaultExportFromDate }}" 
                                        max="{{ $todayDate }}"
                                        onchange="handleFromDateChange()"
                                        class="form-control rounded-lg text-xs py-1.5 px-2.5 w-full border-neutral-300">
                                </div>
                                <div>
                                    <label class="text-[11px] font-semibold text-neutral-600 mb-1 block">Đến ngày:</label>
                                    <input type="date" name="to_date" id="export_to_date" 
                                        value="{{ $defaultExportToDate }}" 
                                        max="{{ $todayDate }}"
                                        onchange="handleToDateChange()"
                                        class="form-control rounded-lg text-xs py-1.5 px-2.5 w-full border-neutral-300">
                                </div>
                            </div>
                        </div>
                    </label>

                    {{-- Tùy chọn 3: Cả tháng --}}
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-neutral-200 hover:border-primary-500 hover:bg-primary-50/30 cursor-pointer transition-all">
                        <input type="radio" name="export_type" value="month" class="mt-1 text-primary-600 focus:ring-primary-500 cursor-pointer" onchange="handleExportTypeChange(this.value)">
                        <div class="flex-1">
                            <div class="text-sm font-bold text-neutral-800 flex items-center gap-1.5">
                                <iconify-icon icon="solar:calendar-bold-duotone" class="text-emerald-600 text-base"></iconify-icon>
                                <span>Xuất trọn vẹn cả tháng</span>
                            </div>
                            <p class="text-xs text-neutral-500 mt-0.5">Xuất tất cả các ngày trong tháng (từ ngày 1 đến ngày cuối tháng / ngày hiện tại).</p>
                            
                            {{-- Input Chọn Tháng/Năm --}}
                            <div id="export-month-inputs" class="grid grid-cols-2 gap-2.5 mt-3 hidden">
                                <div>
                                    <label class="text-[11px] font-semibold text-neutral-600 mb-1 block">Tháng:</label>
                                    <select name="export_month" class="form-select rounded-lg text-xs py-1.5 px-2.5 w-full border-neutral-300">
                                        @for($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[11px] font-semibold text-neutral-600 mb-1 block">Năm:</label>
                                    <input type="number" name="export_year" value="{{ $year }}" min="2020" max="2050"
                                        class="form-control rounded-lg text-xs py-1.5 px-2.5 w-full border-neutral-300">
                                </div>
                            </div>
                        </div>
                    </label>
                </div>

                @if($filterUserId || $search)
                    <div class="p-3 bg-neutral-50 rounded-xl border border-neutral-200 text-xs text-neutral-600 flex items-center gap-2">
                        <iconify-icon icon="solar:info-circle-bold-duotone" class="text-primary-600 text-base shrink-0"></iconify-icon>
                        <span>Đang áp dụng bộ lọc hiện tại: @if($filterUserId)<strong>1 Nhân viên</strong>@endif @if($search)<strong>Từ khóa: "{{ $search }}"</strong>@endif</span>
                    </div>
                @endif
            </div>

            <div class="px-6 py-4 border-t border-neutral-200 flex justify-end gap-3 bg-neutral-50 rounded-b-xl">
                <button type="button" onclick="closeModal('export-revenue-modal')" class="btn btn-neutral px-4 py-2 rounded-lg text-sm">Hủy</button>
                <button type="submit" class="btn btn-primary px-5 py-2 rounded-lg text-sm font-semibold shadow-sm">
                    Tải File Excel
                </button>
            </div>
        </form>
    </x-modal>

    {{-- Modal Bộ Lọc Báo Cáo Doanh Thu --}}
    <x-modal name="revenue-filter-modal" maxWidth="lg">
        <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
            <h5 class="font-bold text-base text-neutral-900 flex items-center gap-2">
                <iconify-icon icon="solar:filter-bold-duotone" class="text-primary-600 text-xl"></iconify-icon>
                <span>Bộ lọc Báo cáo Doanh thu</span>
            </h5>
            <button type="button" onclick="closeModal('revenue-filter-modal')"
                class="text-neutral-400 hover:text-neutral-700 text-xl leading-none">&times;</button>
        </div>
        <form method="GET" action="{{ route('reports.revenue') }}" id="revenue-filter-form" onsubmit="return validateFilterForm(event)">
            <div class="p-6 space-y-4">

                {{-- 1. Chế độ xem: Theo từng ngày, Khoảng ngày HOẶC Tổng của tháng --}}
                <div>
                    <label class="block text-xs font-bold text-neutral-700 uppercase tracking-wider mb-2">
                        Chế độ xem báo cáo <span class="text-danger-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        <label class="filter-mode-card border-2 rounded-xl p-3 cursor-pointer flex flex-col items-center text-center gap-1.5 transition-all {{ $viewMode === 'day' ? 'border-primary-600 bg-primary-50/50 shadow-xs' : 'border-neutral-200 bg-white hover:border-neutral-300' }}"
                            onclick="selectFilterViewMode('day')">
                            <input type="radio" name="view_mode" value="day" class="hidden" {{ $viewMode === 'day' ? 'checked' : '' }} id="filter_mode_day">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                                <iconify-icon icon="solar:calendar-date-bold-duotone" class="text-lg"></iconify-icon>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-neutral-900 leading-tight">Theo từng ngày</div>
                                <div class="text-[10px] text-neutral-500 mt-0.5">Xem số liệu 1 ngày</div>
                            </div>
                        </label>

                        <label class="filter-mode-card border-2 rounded-xl p-3 cursor-pointer flex flex-col items-center text-center gap-1.5 transition-all {{ $viewMode === 'range' ? 'border-primary-600 bg-primary-50/50 shadow-xs' : 'border-neutral-200 bg-white hover:border-neutral-300' }}"
                            onclick="selectFilterViewMode('range')">
                            <input type="radio" name="view_mode" value="range" class="hidden" {{ $viewMode === 'range' ? 'checked' : '' }} id="filter_mode_range">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <iconify-icon icon="solar:calendar-minimalistic-bold-duotone" class="text-lg"></iconify-icon>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-neutral-900 leading-tight">Khoảng ngày</div>
                                <div class="text-[10px] text-neutral-500 mt-0.5">Trong cùng 1 tháng</div>
                            </div>
                        </label>

                        <label class="filter-mode-card border-2 rounded-xl p-3 cursor-pointer flex flex-col items-center text-center gap-1.5 transition-all {{ $viewMode === 'month' ? 'border-primary-600 bg-primary-50/50 shadow-xs' : 'border-neutral-200 bg-white hover:border-neutral-300' }}"
                            onclick="selectFilterViewMode('month')">
                            <input type="radio" name="view_mode" value="month" class="hidden" {{ $viewMode === 'month' ? 'checked' : '' }} id="filter_mode_month">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                <iconify-icon icon="solar:calendar-bold-duotone" class="text-lg"></iconify-icon>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-neutral-900 leading-tight">Tổng của tháng</div>
                                <div class="text-[10px] text-neutral-500 mt-0.5">Tổng hợp cả tháng</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- 2. Khối Chọn Thời Gian Theo Chế Độ --}}
                <div id="filter-date-container" class="{{ $viewMode === 'day' ? 'block' : 'hidden' }}">
                    <label class="block text-xs font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Ngày xem báo cáo
                    </label>
                    <div class="relative">
                        <input type="date" name="date" id="modal_filter_date" value="{{ $selectedDate }}" max="{{ $todayDate }}"
                            class="form-control rounded-xl text-sm font-semibold border-neutral-300 bg-neutral-50 focus:bg-white w-full">
                    </div>
                </div>

                <div id="filter-range-container" class="{{ $viewMode === 'range' ? 'block' : 'hidden' }}">
                    <label class="block text-xs font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Khoảng ngày trong tháng xem báo cáo
                    </label>
                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="text-[11px] font-semibold text-neutral-600 mb-1 block">Từ ngày:</label>
                            <input type="date" name="from_date" id="modal_filter_from_date" 
                                value="{{ $defaultExportFromDate }}" 
                                max="{{ $todayDate }}"
                                onchange="handleFilterFromDateChange()"
                                class="form-control rounded-xl text-xs font-semibold border-neutral-300 bg-neutral-50 focus:bg-white w-full">
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold text-neutral-600 mb-1 block">Đến ngày:</label>
                            <input type="date" name="to_date" id="modal_filter_to_date" 
                                value="{{ $defaultExportToDate }}" 
                                max="{{ $todayDate }}"
                                onchange="handleFilterToDateChange()"
                                class="form-control rounded-xl text-xs font-semibold border-neutral-300 bg-neutral-50 focus:bg-white w-full">
                        </div>
                    </div>
                </div>

                <div id="filter-month-container" class="{{ $viewMode === 'month' ? 'block' : 'hidden' }}">
                    <label class="block text-xs font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Tháng & Năm xem báo cáo
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <select name="month" id="modal_filter_month" class="form-select rounded-xl text-sm font-semibold border-neutral-300 bg-neutral-50 focus:bg-white w-full">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>Tháng {{ $m }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <select name="year" id="modal_filter_year" class="form-select rounded-xl text-sm font-semibold border-neutral-300 bg-neutral-50 focus:bg-white w-full">
                                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Năm {{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 3. Lọc Theo Nhân Viên Phụ Trách (Chỉ dành cho Admin) --}}
                @if(auth()->user()->hasRole('Admin') && !empty($staffUsers) && $staffUsers->isNotEmpty())
                    <div>
                        <label class="block text-xs font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                            Nhân viên phụ trách
                        </label>
                        <select name="user_id" id="modal_filter_user_id" class="form-select rounded-xl text-sm font-medium border-neutral-300 bg-neutral-50 focus:bg-white w-full">
                            <option value="">-- Tất cả nhân viên --</option>
                            @foreach($staffUsers as $u)
                                <option value="{{ $u->id }}" {{ $filterUserId == $u->id ? 'selected' : '' }}>
                                    {{ $u->user_code ? '[' . $u->user_code . '] ' : '' }}{{ $u->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- 4. Tìm kiếm Khách hàng --}}
                <div>
                    <label class="block text-xs font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Tìm kiếm khách hàng
                    </label>
                    <div class="relative">
                        <input type="text" name="search" id="modal_filter_search" value="{{ $search }}" placeholder="Nhập tên, mã khách hàng hoặc số điện thoại..."
                            class="form-control rounded-xl text-sm border-neutral-300 bg-neutral-50 focus:bg-white w-full pl-9">
                        <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-base"></iconify-icon>
                    </div>
                </div>

            </div>

            <div class="px-6 py-4 border-t border-neutral-200 flex justify-end gap-3 bg-neutral-50 rounded-b-xl">
                <button type="button" onclick="closeModal('revenue-filter-modal')" class="btn btn-neutral px-4 py-2 rounded-lg text-sm">Hủy</button>
                <button type="submit" class="btn btn-primary px-5 py-2 rounded-lg text-sm font-semibold shadow-sm">
                    Áp Dụng
                </button>
            </div>
        </form>
    </x-modal>

    <script>
        function selectFilterViewMode(mode) {
            const dayRadio = document.getElementById('filter_mode_day');
            const rangeRadio = document.getElementById('filter_mode_range');
            const monthRadio = document.getElementById('filter_mode_month');
            const dateContainer = document.getElementById('filter-date-container');
            const rangeContainer = document.getElementById('filter-range-container');
            const monthContainer = document.getElementById('filter-month-container');
            const cards = document.querySelectorAll('.filter-mode-card');

            if (dayRadio) dayRadio.checked = (mode === 'day');
            if (rangeRadio) rangeRadio.checked = (mode === 'range');
            if (monthRadio) monthRadio.checked = (mode === 'month');

            if (dateContainer) dateContainer.classList.toggle('hidden', mode !== 'day');
            if (rangeContainer) rangeContainer.classList.toggle('hidden', mode !== 'range');
            if (monthContainer) monthContainer.classList.toggle('hidden', mode !== 'month');

            cards.forEach((card, idx) => {
                const isSelected = (idx === 0 && mode === 'day') || (idx === 1 && mode === 'range') || (idx === 2 && mode === 'month');
                if (isSelected) {
                    card.classList.add('border-primary-600', 'bg-primary-50/50', 'shadow-xs');
                    card.classList.remove('border-neutral-200', 'bg-white');
                } else {
                    card.classList.remove('border-primary-600', 'bg-primary-50/50', 'shadow-xs');
                    card.classList.add('border-neutral-200', 'bg-white');
                }
            });
        }

        // Validate form bộ lọc trước khi submit
        function validateFilterForm(e) {
            const rangeRadio = document.getElementById('filter_mode_range');
            if (rangeRadio && rangeRadio.checked) {
                const fromInput = document.getElementById('modal_filter_from_date');
                const toInput = document.getElementById('modal_filter_to_date');
                if (!fromInput?.value || !toInput?.value) {
                    alert('Vui lòng chọn đầy đủ Từ ngày và Đến ngày.');
                    if (e) e.preventDefault();
                    return false;
                }
                if (fromInput.value > toInput.value) {
                    alert('Lỗi: "Từ ngày" phải nhỏ hơn hoặc bằng "Đến ngày"!');
                    if (e) e.preventDefault();
                    return false;
                }
                const fromMonth = fromInput.value.substring(0, 7);
                const toMonth = toInput.value.substring(0, 7);
                if (fromMonth !== toMonth) {
                    alert('Lỗi: Khoảng ngày xem báo cáo phải nằm trong cùng một tháng!');
                    if (e) e.preventDefault();
                    return false;
                }
            }
            return true;
        }

        // Khi người dùng đổi "Từ ngày" ở bộ lọc: Tự động đồng bộ và giới hạn "Đến ngày" trong cùng tháng
        function handleFilterFromDateChange() {
            const fromInput = document.getElementById('modal_filter_from_date');
            const toInput = document.getElementById('modal_filter_to_date');
            if (!fromInput?.value || !toInput) return;

            const parts = fromInput.value.split('-');
            const year = parseInt(parts[0]);
            const month = parseInt(parts[1]);
            const lastDay = new Date(year, month, 0).getDate();
            const monthStr = month < 10 ? '0' + month : '' + month;
            const endOfMonth = `${year}-${monthStr}-${lastDay < 10 ? '0' + lastDay : lastDay}`;
            const today = '{{ $todayDate }}';
            const maxDate = (year === parseInt(today.split('-')[0]) && month === parseInt(today.split('-')[1]))
                ? (endOfMonth > today ? today : endOfMonth)
                : endOfMonth;

            toInput.min = fromInput.value;
            toInput.max = maxDate;

            if (!toInput.value || toInput.value < fromInput.value || toInput.value.substring(0, 7) !== fromInput.value.substring(0, 7)) {
                toInput.value = maxDate >= fromInput.value ? maxDate : fromInput.value;
            }
        }

        // Khi người dùng đổi "Đến ngày" ở bộ lọc: Tự động đồng bộ và giới hạn "Từ ngày" trong cùng tháng
        function handleFilterToDateChange() {
            const fromInput = document.getElementById('modal_filter_from_date');
            const toInput = document.getElementById('modal_filter_to_date');
            if (!toInput?.value || !fromInput) return;

            const parts = toInput.value.split('-');
            const year = parseInt(parts[0]);
            const month = parseInt(parts[1]);
            const monthStr = month < 10 ? '0' + month : '' + month;
            const startOfMonth = `${year}-${monthStr}-01`;

            fromInput.min = startOfMonth;
            fromInput.max = toInput.value;

            if (!fromInput.value || fromInput.value > toInput.value || fromInput.value.substring(0, 7) !== toInput.value.substring(0, 7)) {
                fromInput.value = startOfMonth;
            }
        }
        function handleExportTypeChange(type) {
            const rangeInputs = document.getElementById('export-range-inputs');
            const monthInputs = document.getElementById('export-month-inputs');
            if (rangeInputs) {
                rangeInputs.classList.toggle('hidden', type !== 'range');
            }
            if (monthInputs) {
                monthInputs.classList.toggle('hidden', type !== 'month');
            }
        }

        // Khi người dùng đổi "Từ ngày": Tự động đồng bộ và giới hạn "Đến ngày" trong cùng tháng
        function handleFromDateChange() {
            const fromInput = document.getElementById('export_from_date');
            const toInput = document.getElementById('export_to_date');
            if (!fromInput?.value || !toInput) return;
            
            const parts = fromInput.value.split('-');
            const year = parseInt(parts[0]);
            const month = parseInt(parts[1]);
            const lastDay = new Date(year, month, 0).getDate();
            const monthStr = month < 10 ? '0' + month : '' + month;
            const endOfMonth = `${year}-${monthStr}-${lastDay < 10 ? '0' + lastDay : lastDay}`;
            const today = '{{ $todayDate }}';
            const maxDate = (year === parseInt(today.split('-')[0]) && month === parseInt(today.split('-')[1]))
                ? (endOfMonth > today ? today : endOfMonth)
                : endOfMonth;

            toInput.min = fromInput.value;
            toInput.max = maxDate;

            // Nếu "Đến ngày" đang ở tháng khác hoặc nhỏ hơn "Từ ngày" -> đồng bộ về ngày cuối tháng hoặc bằng "Từ ngày"
            if (!toInput.value || toInput.value < fromInput.value || toInput.value.substring(0, 7) !== fromInput.value.substring(0, 7)) {
                toInput.value = maxDate >= fromInput.value ? maxDate : fromInput.value;
            }
        }

        // Khi người dùng đổi "Đến ngày": Tự động đồng bộ và giới hạn "Từ ngày" trong cùng tháng
        function handleToDateChange() {
            const fromInput = document.getElementById('export_from_date');
            const toInput = document.getElementById('export_to_date');
            if (!toInput?.value || !fromInput) return;

            const parts = toInput.value.split('-');
            const year = parseInt(parts[0]);
            const month = parseInt(parts[1]);
            const monthStr = month < 10 ? '0' + month : '' + month;
            const startOfMonth = `${year}-${monthStr}-01`;

            fromInput.min = startOfMonth;
            fromInput.max = toInput.value;

            // Nếu "Từ ngày" đang ở tháng khác hoặc lớn hơn "Đến ngày" -> đặt về ngày đầu tháng
            if (!fromInput.value || fromInput.value > toInput.value || fromInput.value.substring(0, 7) !== toInput.value.substring(0, 7)) {
                fromInput.value = startOfMonth;
            }
        }

        function validateExportForm(e) {
            const exportType = document.querySelector('input[name="export_type"]:checked')?.value || 'single';
            if (exportType === 'range') {
                const fromInput = document.getElementById('export_from_date');
                const toInput = document.getElementById('export_to_date');
                if (!fromInput?.value || !toInput?.value) {
                    alert('Vui lòng chọn đầy đủ Từ ngày và Đến ngày.');
                    if (e) e.preventDefault();
                    return false;
                }
                if (fromInput.value > toInput.value) {
                    alert('Lỗi: "Từ ngày" phải nhỏ hơn hoặc bằng "Đến ngày"!');
                    if (e) e.preventDefault();
                    return false;
                }
                const fromMonth = fromInput.value.substring(0, 7);
                const toMonth = toInput.value.substring(0, 7);
                if (fromMonth !== toMonth) {
                    alert('Lỗi: Khoảng ngày xuất phải nằm trong cùng một tháng!');
                    if (e) e.preventDefault();
                    return false;
                }
            }
            closeModal('export-revenue-modal');
            return true;
        }
        document.addEventListener('DOMContentLoaded', function () {
            const monthSelect = document.getElementById('target_month');
            const yearInput = document.getElementById('target_year');
            const titleSpan = document.getElementById('modal-target-title');
            const amountInput = document.getElementById('target_amount');
            const targetDayInput = document.getElementById('target_day_amount');
            const lostInput = document.getElementById('lost_months');
            const returningInput = document.getElementById('returning_months');

            // Tự động tính gợi ý mục tiêu ngày khi gõ mục tiêu tháng
            if (amountInput) {
                amountInput.addEventListener('input', function () {
                    const amt = parseFloat(amountInput.value) || 0;
                    const m = parseInt(monthSelect ? monthSelect.value : 1) || 1;
                    const y = parseInt(yearInput ? yearInput.value : 2026) || 2026;
                    const days = new Date(y, m, 0).getDate();
                    if (targetDayInput && days > 0) {
                        targetDayInput.value = Math.round(amt / days);
                    }
                });
            }

            // Tự động cập nhật tiêu đề và tải lại mục tiêu khi thay đổi tháng / năm
            function updateTargetMonthYear() {
                if (!monthSelect || !yearInput) return;
                const m = monthSelect.value;
                const y = yearInput.value;
                if (titleSpan) {
                    titleSpan.textContent = `Thiết lập Báo cáo Doanh thu Tháng ${m}/${y}`;
                }
                fetch(`{{ route('reports.revenue.get-target') }}?month=${m}&year=${y}`)
                    .then(res => res.json())
                    .then(res => {
                        if (res.success && res.data) {
                            if (amountInput) amountInput.value = res.data.target_amount;
                            if (targetDayInput) targetDayInput.value = res.data.target_day_amount;
                            if (lostInput) lostInput.value = res.data.lost_months ?? 6;
                            if (returningInput) returningInput.value = res.data.returning_months ?? 6;
                        }
                    })
                    .catch(() => { });
            }

            if (monthSelect && yearInput) {
                monthSelect.addEventListener('change', updateTargetMonthYear);
                yearInput.addEventListener('change', updateTargetMonthYear);
            }
        });

        function handleSaveTarget(e) {
            e.preventDefault();
            const btn = document.getElementById('save-target-btn');
            btn.disabled = true;
            btn.innerHTML = '<iconify-icon icon="solar:spinner-linear" class="animate-spin text-lg"></iconify-icon> Đang lưu...';

            const formData = new FormData(document.getElementById('set-target-form'));

            fetch('{{ route("reports.revenue.target") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert(data.message || 'Có lỗi xảy ra khi lưu thiết lập.');
                        btn.disabled = false;
                        btn.innerHTML = '<iconify-icon icon="solar:disk-bold-duotone"></iconify-icon> Lưu Thiết Lập';
                    }
                })
                .catch(err => {
                    alert('Lỗi kết nối: ' + err.message);
                    btn.disabled = false;
                    btn.innerHTML = '<iconify-icon icon="solar:disk-bold-duotone"></iconify-icon> Lưu Thiết Lập';
                });
        }
    </script>

    <style>
        .kpi-cards-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        @media (max-width: 1200px) {
            .kpi-cards-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .kpi-cards-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 500px) {
            .kpi-cards-grid {
                grid-template-columns: 1fr;
            }
        }

        .kpi-card {
            background-color: #ffffff !important;
            border: 1px solid #e5e7eb !important;
            min-height: 80px;
        }

        .table-excel {
            width: 100%;
            border-collapse: collapse;
            font-family: inherit;
        }

        .table-excel th,
        .table-excel td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            white-space: nowrap;
        }

        .table-excel thead th {
            vertical-align: middle;
            white-space: normal !important;
            line-height: 1.3 !important;
        }

        .header-khaki-row th,
        .header-khaki-sub-row th {
            background-color: #e7e5e4;
            color: #1c1917;
        }

        .row-total-day td {
            background-color: #fef9c3 !important;
            border: 1px solid #fde047 !important;
            color: #713f12 !important;
            font-weight: 700;
        }

        .row-total-day td.text-total-danger {
            color: #dc2626 !important;
        }

        .row-total-day td.text-total-success {
            color: #16a34a !important;
        }

        .btn-today-back {
            background-color: #2563eb !important;
            color: #ffffff !important;
            border: 1px solid #1d4ed8 !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            padding: 6px 12px !important;
            border-radius: 8px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            text-decoration: none !important;
            transition: all 0.2s ease !important;
        }

        .btn-today-back:hover {
            background-color: #1d4ed8 !important;
            color: #ffffff !important;
            box-shadow: 0 3px 10px rgba(37, 99, 235, 0.35) !important;
        }

        .btn-today-back iconify-icon {
            color: #ffffff !important;
            font-size: 15px !important;
        }

        .btn-setting-gear {
            background-color: #64748b !important;
            color: #ffffff !important;
            border: 1px solid #475569 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 32px !important;
            height: 32px !important;
            border-radius: 8px !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
        }

        .btn-setting-gear:hover {
            background-color: #475569 !important;
            color: #ffffff !important;
            border-color: #334155 !important;
            box-shadow: 0 2px 8px rgba(100, 116, 139, 0.3) !important;
        }

    </style>

@endsection