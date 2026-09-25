@extends('layout.layout')
@php
    $title    = 'Chi tiết: ' . $marketGroup->name;
    $subTitle = 'Danh sách toàn bộ khách hàng và chỉ số của nhóm thị trường ' . $marketGroup->name;
    $codeUpper = strtoupper($marketGroup->code ?: '');
    $groupColors = [
        'A' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'badge' => 'bg-blue-600'],
        'B' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'badge' => 'bg-emerald-600'],
        'C' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'badge' => 'bg-amber-600'],
        'D' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'badge' => 'bg-purple-600'],
        'E' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'badge' => 'bg-rose-600'],
    ];
    $color = $groupColors[$codeUpper] ?? ['bg' => 'bg-primary-50', 'text' => 'text-primary-700', 'border' => 'border-primary-200', 'badge' => 'bg-primary-600'];
@endphp

{{-- Leaflet CSS & JS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@section('content')

{{-- Breadcrumb & Back button & Action buttons --}}
<div class="mb-5 flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('market-groups.index') }}" class="btn btn-neutral btn-sm rounded-xl px-3.5 py-2 flex items-center gap-2 text-xs font-semibold shadow-xs hover:bg-neutral-100 transition-colors">
            <iconify-icon icon="lucide:arrow-left" class="text-sm"></iconify-icon>
            Quay lại danh sách nhóm
        </a>
        <div class="h-5 w-px bg-neutral-200"></div>
        <div class="flex items-center gap-2.5">
            <span class="w-9 h-9 rounded-xl {{ $color['badge'] }} text-white font-bold text-sm flex items-center justify-center shadow-xs">
                {{ $marketGroup->code ?: substr($marketGroup->name, -1) }}
            </span>
            <div>
                <h4 class="font-bold text-xl text-neutral-800 m-0 leading-tight">{{ $marketGroup->name }}</h4>
                <div class="text-xs text-neutral-500 mt-0.5 flex items-center gap-2">
                    @if($marketGroup->code)
                        <span class="font-mono uppercase font-bold text-neutral-600 bg-neutral-100 px-1.5 py-0.5 rounded">Mã: {{ $marketGroup->code }}</span>
                    @endif
                    <span>{{ $marketGroup->description ?: 'Chưa có mô tả khu vực' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 flex-wrap">
        {{-- Nhân viên phụ trách nhóm --}}
        <div class="flex items-center gap-2">
            <span class="text-xs text-neutral-500 font-medium">Nhân viên:</span>
            @forelse($marketGroup->users as $u)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-neutral-200 text-neutral-700 shadow-2xs">
                    <iconify-icon icon="solar:user-bold" class="text-primary-600 text-xs"></iconify-icon>
                    {{ $u->user_code ? '[' . $u->user_code . '] ' : '' }}{{ $u->name }}
                </span>
            @empty
                <span class="text-xs text-neutral-400 italic">Chưa phân bổ NV</span>
            @endforelse
        </div>

        {{-- Nút Thêm khách hàng mới --}}
        <button type="button" onclick="openCreateCustomerModal()" class="btn btn-primary btn-sm rounded-xl px-4 py-2 text-xs font-semibold flex items-center gap-2 shadow-sm">
            <iconify-icon icon="solar:user-plus-bold" class="text-base"></iconify-icon>
            Thêm khách hàng mới
        </button>
    </div>
</div>

@if(session('success'))
<div class="mb-5 p-4 rounded-xl border border-success-200 bg-success-50 text-success-700 flex items-center justify-between">
    <div class="flex items-center gap-2">
        <iconify-icon icon="lucide:check-circle" class="text-xl text-success-600"></iconify-icon>
        <span class="font-medium text-sm">{{ session('success') }}</span>
    </div>
    <button type="button" onclick="this.parentElement.remove()" class="text-neutral-400 hover:text-neutral-600">&times;</button>
</div>
@endif

{{-- Cards Thống kê nhóm --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-2xl shrink-0">
            <iconify-icon icon="solar:users-group-two-rounded-bold-duotone"></iconify-icon>
        </div>
        <div>
            <div class="text-xs text-neutral-500 font-medium">Tổng khách hàng</div>
            <div class="text-2xl font-bold text-neutral-800 mt-0.5">{{ $totalCustomers }} <span class="text-xs font-normal text-neutral-500">khách</span></div>
        </div>
    </div>

    <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-danger-50 text-danger-600 flex items-center justify-center text-2xl shrink-0">
            <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
        </div>
        <div>
            <div class="text-xs text-neutral-500 font-medium">Tổng công nợ nhóm</div>
            <div class="text-2xl font-bold text-danger-600 mt-0.5">{{ number_format($totalDebt, 0, ',', '.') }} <span class="text-xs font-normal text-neutral-500">₫</span></div>
        </div>
    </div>

    <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl shrink-0">
            <iconify-icon icon="solar:card-recive-bold-duotone"></iconify-icon>
        </div>
        <div>
            <div class="text-xs text-neutral-500 font-medium">Đã thanh toán ({{ $dateLabel }})</div>
            <div class="text-2xl font-bold text-emerald-600 mt-0.5">{{ number_format($totalPaid, 0, ',', '.') }} <span class="text-xs font-normal text-neutral-500">₫</span></div>
        </div>
    </div>

    <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl shrink-0">
            <iconify-icon icon="solar:user-id-bold-duotone"></iconify-icon>
        </div>
        <div>
            <div class="text-xs text-neutral-500 font-medium">Nhân viên phụ trách</div>
            <div class="text-2xl font-bold text-neutral-800 mt-0.5">{{ $marketGroup->users->count() }} <span class="text-xs font-normal text-neutral-500">nhân sự</span></div>
        </div>
    </div>
</div>

{{-- Main Container Card --}}
<div class="bg-white border border-neutral-200 rounded-2xl shadow-sm overflow-hidden mb-8">
    {{-- Thanh công cụ: Gán thêm khách hàng & Bộ lọc thời gian linh hoạt & Tìm kiếm --}}
    <div class="p-5 border-b border-neutral-200 bg-neutral-50/60 flex flex-wrap items-center justify-between gap-4">
        {{-- Form gán khách hàng nhanh --}}
        <form action="{{ route('market-groups.assign-customers', $marketGroup->id) }}" method="POST" class="flex flex-wrap items-center gap-2.5 min-w-[280px]">
            @csrf
            <div class="text-xs font-bold text-neutral-700 flex items-center gap-1.5 shrink-0">
                <iconify-icon icon="solar:user-plus-bold" class="text-primary-600 text-base"></iconify-icon>
                Gán vào nhóm:
            </div>
            <div class="w-64">
                <select name="customer_ids[]" id="select-customers-to-add" multiple placeholder="Chọn khách hàng...">
                    @foreach($allCustomers as $c)
                        <option value="{{ $c->id }}" {{ $c->market_group_id == $marketGroup->id ? 'disabled' : '' }}>
                            {{ $c->customer_code ? '[' . $c->customer_code . '] ' : '' }}{{ $c->name }}
                            {{ $c->market_group_id == $marketGroup->id ? '(Đã thuộc nhóm này)' : ($c->market_group_id ? '(Đã có nhóm khác)' : '(Chưa có nhóm)') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-sm btn-primary rounded-xl px-3.5 py-2 text-xs font-semibold shrink-0 flex items-center gap-1.5 shadow-sm">
                <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon> Thêm
            </button>
        </form>

        {{-- Bộ lọc Ngày / Tháng / Năm linh hoạt (1 ô date duy nhất) --}}
        <form method="GET" action="{{ route('market-groups.show', $marketGroup->id) }}" id="dateFilterForm" class="flex items-center gap-2 flex-wrap">
            @if($search)
                <input type="hidden" name="search" value="{{ $search }}">
            @endif
            <div class="inline-flex items-center bg-white border border-neutral-300 rounded-xl p-1 shadow-2xs">
                {{-- Selector chọn chế độ --}}
                <div class="flex items-center bg-neutral-100 rounded-lg p-0.5 text-xs font-semibold mr-1.5">
                    <button type="button" onclick="setDateMode('day')" id="mode-btn-day" 
                        class="px-2.5 py-1 rounded-md transition-all {{ $dateMode === 'day' ? 'bg-white text-primary-700 shadow-2xs font-bold' : 'text-neutral-500 hover:text-neutral-800' }}">
                        Ngày
                    </button>
                    <button type="button" onclick="setDateMode('month')" id="mode-btn-month" 
                        class="px-2.5 py-1 rounded-md transition-all {{ $dateMode === 'month' ? 'bg-white text-primary-700 shadow-2xs font-bold' : 'text-neutral-500 hover:text-neutral-800' }}">
                        Tháng
                    </button>
                    <button type="button" onclick="setDateMode('year')" id="mode-btn-year" 
                        class="px-2.5 py-1 rounded-md transition-all {{ $dateMode === 'year' ? 'bg-white text-primary-700 shadow-2xs font-bold' : 'text-neutral-500 hover:text-neutral-800' }}">
                        Năm
                    </button>
                    <button type="button" onclick="setDateMode('all')" id="mode-btn-all" 
                        class="px-2.5 py-1 rounded-md transition-all {{ $dateMode === 'all' ? 'bg-white text-primary-700 shadow-2xs font-bold' : 'text-neutral-500 hover:text-neutral-800' }}">
                        Tất cả
                    </button>
                </div>
                <input type="hidden" name="date_mode" id="date_mode" value="{{ $dateMode }}">

                {{-- Ô nhập date duy nhất linh hoạt --}}
                <div id="date-input-container" class="flex items-center">
                    @if($dateMode === 'all')
                        <input type="text" readonly value="Toàn thời gian" class="border-0 bg-transparent text-xs py-1 px-2 font-semibold text-neutral-500 w-28 cursor-default outline-none">
                    @elseif($dateMode === 'day')
                        <input type="date" name="date_val" id="date_val" value="{{ $dateVal }}" 
                            class="border-0 bg-transparent text-xs py-1 px-1.5 font-medium text-neutral-800 outline-none cursor-pointer" 
                            onchange="this.form.submit()">
                    @elseif($dateMode === 'month')
                        <input type="month" name="date_val" id="date_val" value="{{ $dateVal }}" 
                            class="border-0 bg-transparent text-xs py-1 px-1.5 font-medium text-neutral-800 outline-none cursor-pointer" 
                            onchange="this.form.submit()">
                    @elseif($dateMode === 'year')
                        <input type="number" min="2000" max="2099" name="date_val" id="date_val" value="{{ $dateVal }}" 
                            class="border-0 bg-transparent text-xs py-1 px-1.5 font-medium text-neutral-800 outline-none w-20" 
                            placeholder="Năm..." onchange="this.form.submit()">
                    @endif
                </div>

                <button type="submit" class="btn btn-xs btn-primary rounded-lg px-2.5 py-1.5 text-xs font-semibold flex items-center gap-1 ml-1" title="Áp dụng lọc">
                    <iconify-icon icon="lucide:arrow-right" class="text-xs"></iconify-icon>
                </button>
            </div>
        </form>

        {{-- Ô tìm kiếm lọc bảng --}}
        <div class="flex items-center gap-3">
            <div class="relative">
                <input type="text" id="page-customer-search" onkeyup="filterPageCustomerTable()" placeholder="Tìm nhanh khách hàng..." class="form-control rounded-xl pl-9 pr-3 py-2 text-xs w-60 md:w-64 border-neutral-300 shadow-2xs">
                <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-sm"></iconify-icon>
            </div>
            <span id="page-search-count-badge" class="px-2.5 py-1 rounded-full text-xs font-semibold bg-neutral-100 text-neutral-600 border border-neutral-200 shrink-0">
                {{ count($customers) }} khách hàng
            </span>
        </div>
    </div>

    {{-- Bảng chi tiết toàn bộ các cột --}}
    <div class="overflow-x-auto">
        <table class="table bordered-table w-full mb-0 text-xs text-left" style="min-width: 2050px;" id="page-customers-table">
            <thead>
                <tr class="bg-neutral-100 text-neutral-700 font-bold border-b border-neutral-200 text-xs">
                    <th style="width: 50px;" class="text-center py-3.5 px-2 sticky left-0 bg-neutral-100 z-10">STT</th>
                    <th style="width: 100px;" class="py-3.5 px-3 sticky left-[50px] bg-neutral-100 z-10">Mã KH</th>
                    <th style="min-width: 180px;" class="py-3.5 px-3 sticky left-[150px] bg-neutral-100 z-10 border-r border-neutral-200">Khách hàng</th>
                    <th style="min-width: 150px;" class="py-3.5 px-3 text-center">Trạng thái</th>
                    <th style="min-width: 120px;" class="py-3.5 px-3">Số điện thoại</th>
                    <th style="min-width: 260px;" class="py-3.5 px-3">Địa chỉ & Bản đồ GPS</th>
                    <th style="min-width: 140px;" class="py-3.5 px-3 text-center">Ảnh hiện trường</th>
                    <th style="min-width: 160px;" class="py-3.5 px-3">Đối tác hợp tác</th>
                    <th style="min-width: 130px;" class="py-3.5 px-3">Quy mô xưởng</th>
                    <th style="min-width: 140px;" class="py-3.5 px-3">Tính cách KH</th>
                    <th style="min-width: 220px;" class="py-3.5 px-3">Phản ánh về Gervin</th>
                    <th style="min-width: 220px;" class="py-3.5 px-3">Đề xuất KH</th>
                    <th style="min-width: 220px;" class="py-3.5 px-3">Đề xuất Sale</th>
                    <th style="min-width: 150px;" class="py-3.5 px-3">Chính sách</th>
                    <th style="min-width: 120px;" class="py-3.5 px-3 text-right">Định mức nợ</th>
                    <th style="min-width: 130px;" class="py-3.5 px-3 text-right">Công nợ</th>
                    <th style="min-width: 140px;" class="py-3.5 px-3 text-right">
                        Đã thanh toán
                        <div class="text-[10px] font-normal text-neutral-400 capitalize">({{ $dateLabel }})</div>
                    </th>
                    <th style="width: 90px;" class="py-3.5 px-3 text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody id="page-customers-tbody" class="divide-y divide-neutral-200">
                @forelse($customers as $index => $c)
                @php
                    $st = $c->status ?: 'Đang đặt hàng';
                    $stClass = match($st) {
                        'Đang đặt hàng' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'Không đặt GERVIN' => 'bg-rose-50 text-rose-700 border-rose-200',
                        'Khách hàng mới tiềm năng' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'Tạm dừng hợp tác' => 'bg-amber-50 text-amber-700 border-amber-200',
                        default => 'bg-neutral-100 text-neutral-600 border-neutral-200'
                    };
                    $photos = is_array($c->photos) ? $c->photos : (is_string($c->photos) ? (json_decode($c->photos, true) ?: []) : []);
                    $rowSearchText = strtolower(implode(' ', array_filter([
                        $c->customer_code,
                        $c->name,
                        $c->phone,
                        $c->full_address,
                        $c->partner_competitors,
                        $c->workshop_scale,
                        $c->personality,
                        $c->feedback,
                        $c->customer_proposal,
                        $c->sale_proposal,
                        $c->policy
                    ])));
                @endphp
                <tr class="hover:bg-neutral-50/80 transition-colors customer-row" data-search="{{ $rowSearchText }}">
                    {{-- 1. STT --}}
                    <td class="text-center font-medium text-neutral-500 py-3 px-2 sticky left-0 bg-white row-stt">{{ $index + 1 }}</td>

                    {{-- 2. Mã KH --}}
                    <td class="font-semibold text-neutral-800 font-mono py-3 px-3 sticky left-[50px] bg-white">{{ $c->customer_code ?: '—' }}</td>

                    {{-- 3. Khách hàng --}}
                    <td class="py-3 px-3 sticky left-[150px] bg-white border-r border-neutral-200">
                        <a href="{{ route('customers.index', ['overview_id' => $c->id]) }}" target="_blank" class="font-bold text-neutral-900 hover:text-primary-600 inline-flex items-center gap-1.5 leading-snug" title="Mở chi tiết khách hàng">
                            <span>{{ $c->name }}</span>
                            <iconify-icon icon="lucide:external-link" class="text-[11px] opacity-40 shrink-0"></iconify-icon>
                        </a>
                    </td>

                    {{-- 4. Trạng thái --}}
                    <td class="py-3 px-3 text-center">
                        <span class="px-2.5 py-1 rounded text-[11px] font-bold border inline-block {{ $stClass }}">{{ $st }}</span>
                    </td>

                    {{-- 4. SĐT --}}
                    <td class="text-neutral-700 font-medium py-3 px-3">
                        @if($c->phone)
                            <a href="tel:{{ $c->phone }}" class="hover:text-primary-600 hover:underline">{{ $c->phone }}</a>
                        @else
                            <span class="text-neutral-300">—</span>
                        @endif
                    </td>

                    {{-- 5. Địa chỉ & GPS --}}
                    <td class="text-neutral-700 py-3 px-3 max-w-[280px]">
                        <div class="line-clamp-2 leading-relaxed" title="{{ $c->full_address }}">{{ $c->full_address }}</div>
                        @if($c->latitude && $c->longitude)
                            <div class="mt-1">
                                <a href="https://www.google.com/maps?q={{ $c->latitude }},{{ $c->longitude }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-primary-600 hover:text-primary-800 font-semibold bg-primary-50 px-1.5 py-0.5 rounded border border-primary-200 hover:bg-primary-100 transition-colors">
                                    <iconify-icon icon="solar:map-point-wave-bold" class="text-rose-500"></iconify-icon>
                                    Maps ({{ number_format($c->latitude, 3) }}, {{ number_format($c->longitude, 3) }})
                                </a>
                            </div>
                        @endif
                    </td>

                    {{-- 6. Ảnh hiện trường --}}
                    <td class="py-3 px-3 text-center">
                        @if(!empty($photos) && count($photos) > 0)
                            <div class="flex items-center gap-1 justify-center">
                                @foreach(array_slice($photos, 0, 3) as $pIdx => $p)
                                    @php $fullSrc = (str_starts_with($p, 'http') || str_starts_with($p, '/')) ? $p : '/' . $p; @endphp
                                    <img src="{{ $fullSrc }}" class="w-8 h-8 rounded border border-neutral-200 object-cover cursor-pointer hover:scale-110 transition-transform shadow-xs shrink-0" onclick="openImageLightbox('{{ $fullSrc }}', '{{ addslashes($c->name) }} - Ảnh {{ $pIdx + 1 }}')" title="Xem ảnh timestamp">
                                @endforeach
                                @if(count($photos) > 3)
                                    <span class="w-8 h-8 rounded bg-neutral-100 border border-neutral-300 text-[10px] font-bold text-neutral-600 flex items-center justify-center cursor-pointer hover:bg-neutral-200" onclick="previewCustomerPhotos({{ json_encode($photos) }}, '{{ addslashes($c->name) }}')">
                                        +{{ count($photos) - 3 }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="text-neutral-300 italic text-[11px]">Chưa có</span>
                        @endif
                    </td>

                    {{-- 7. Đối tác hợp tác --}}
                    <td class="text-neutral-700 font-medium py-3 px-3">
                        {{ $c->partner_competitors ?: '—' }}
                    </td>

                    {{-- 8. Quy mô xưởng --}}
                    <td class="text-neutral-700 py-3 px-3">
                        {{ $c->workshop_scale ?: '—' }}
                    </td>

                    {{-- 9. Tính cách KH --}}
                    <td class="text-neutral-700 py-3 px-3">
                        {{ $c->personality ?: '—' }}
                    </td>

                    {{-- 10. Phản ánh về Gervin --}}
                    <td class="text-neutral-700 py-3 px-3 max-w-[220px]">
                        @if($c->feedback)
                            <div class="line-clamp-2 leading-relaxed" title="{{ $c->feedback }}">{{ $c->feedback }}</div>
                        @else
                            <span class="text-neutral-300 italic text-[11px]">Chưa có</span>
                        @endif
                    </td>

                    {{-- 11. Đề xuất KH --}}
                    <td class="text-neutral-700 py-3 px-3 max-w-[220px]">
                        @if($c->customer_proposal)
                            <div class="line-clamp-2 leading-relaxed" title="{{ $c->customer_proposal }}">{{ $c->customer_proposal }}</div>
                        @else
                            <span class="text-neutral-300 italic text-[11px]">Chưa có</span>
                        @endif
                    </td>

                    {{-- 12. Đề xuất Sale --}}
                    <td class="text-neutral-700 py-3 px-3 max-w-[220px]">
                        @if($c->sale_proposal)
                            <div class="line-clamp-2 leading-relaxed" title="{{ $c->sale_proposal }}">{{ $c->sale_proposal }}</div>
                        @else
                            <span class="text-neutral-300 italic text-[11px]">Chưa có</span>
                        @endif
                    </td>

                    {{-- 13. Chính sách --}}
                    <td class="text-neutral-700 py-3 px-3 max-w-[150px]">
                        @if($c->policy)
                            <div class="line-clamp-2 leading-relaxed" title="{{ $c->policy }}">{{ $c->policy }}</div>
                        @else
                            <span class="text-neutral-300">—</span>
                        @endif
                    </td>

                    {{-- 14. Định mức nợ --}}
                    <td class="text-right text-neutral-700 py-3 px-3 font-medium">
                        {{ $c->debt_limit ? number_format($c->debt_limit, 0, ',', '.') . ' ₫' : '—' }}
                    </td>

                    {{-- 15. Công nợ --}}
                    <td class="text-right py-3 px-3 {{ $c->debt > 0 ? 'text-danger-600 font-bold' : 'text-neutral-600 font-medium' }}">
                        {{ number_format($c->debt ?? 0, 0, ',', '.') }} ₫
                    </td>

                    {{-- 16. Đã thanh toán --}}
                    <td class="text-right py-3 px-3 {{ $c->period_paid > 0 ? 'text-emerald-600 font-bold' : 'text-neutral-600 font-medium' }}">
                        {{ number_format($c->period_paid ?? 0, 0, ',', '.') }} ₫
                    </td>

                    {{-- 17. Thao tác (Sửa + Bỏ nhóm) --}}
                    <td class="py-3 px-3 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            {{-- Nút Sửa khách hàng --}}
                            <button type="button"
                                class="text-emerald-600 hover:text-emerald-800 p-1.5 rounded hover:bg-emerald-50 transition-colors"
                                title="Chỉnh sửa khách hàng"
                                data-id="{{ $c->id }}"
                                data-code="{{ $c->customer_code ?? '' }}"
                                data-name="{{ $c->name }}"
                                data-phone="{{ $c->phone ?? '' }}"
                                data-address="{{ $c->address ?? '' }}"
                                data-latitude="{{ $c->latitude ?? '' }}"
                                data-longitude="{{ $c->longitude ?? '' }}"
                                data-province="{{ $c->province ?? '' }}"
                                data-ward="{{ $c->ward ?? '' }}"
                                data-status="{{ $c->status ?? 'Đang đặt hàng' }}"
                                data-partner-competitors="{{ $c->partner_competitors ?? '' }}"
                                data-feedback="{{ $c->feedback ?? '' }}"
                                data-personality="{{ $c->personality ?? '' }}"
                                data-workshop-scale="{{ $c->workshop_scale ?? '' }}"
                                data-customer-proposal="{{ $c->customer_proposal ?? '' }}"
                                data-sale-proposal="{{ $c->sale_proposal ?? '' }}"
                                data-debt="{{ $c->debt ?? 0 }}"
                                data-debt-limit="{{ $c->debt_limit ?? 0 }}"
                                data-policy="{{ $c->policy ?? '' }}"
                                data-photos="{{ json_encode($photos) }}"
                                data-market-group-id="{{ $c->market_group_id ?? $marketGroup->id }}"
                                onclick="openEditCustomerModalFromBtn(this)">
                                <iconify-icon icon="lucide:edit-3" class="text-lg"></iconify-icon>
                            </button>

                            {{-- Nút Bỏ nhóm --}}
                            <form action="{{ route('market-groups.remove-customer', [$marketGroup->id, $c->id]) }}" method="POST" onsubmit="return confirm('Bỏ khách hàng {{ addslashes($c->name) }} khỏi nhóm {{ addslashes($marketGroup->name) }}?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-danger-500 hover:text-danger-700 p-1.5 rounded hover:bg-danger-50 transition-colors" title="Bỏ khỏi nhóm">
                                    <iconify-icon icon="lucide:x-circle" class="text-lg"></iconify-icon>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr id="page-customers-empty-row">
                    <td colspan="18" class="text-center py-20 text-neutral-400">
                        <iconify-icon icon="solar:users-group-two-rounded-line-duotone" class="text-4xl text-neutral-300"></iconify-icon>
                        <div class="text-sm mt-2 font-bold text-neutral-700">Nhóm này chưa có khách hàng nào</div>
                        <div class="text-xs text-neutral-400 mt-1">Sử dụng ô chọn phía trên để gán khách hàng hoặc thêm khách hàng mới.</div>
                    </td>
                </tr>
                @endforelse
                <tr id="page-customers-no-search-row" class="hidden">
                    <td colspan="18" class="text-center py-16 text-neutral-400">
                        <iconify-icon icon="lucide:search-x" class="text-3xl text-neutral-300"></iconify-icon>
                        <div class="text-xs mt-2 font-medium">Không tìm thấy khách hàng nào khớp với từ khóa tìm kiếm.</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Footer Card --}}
    <div class="p-4 bg-neutral-50 border-t border-neutral-200 flex items-center justify-between text-xs text-neutral-500">
        <div>
            Hiển thị <strong>{{ count($customers) }}</strong> khách hàng trong nhóm <strong>{{ $marketGroup->name }}</strong>
        </div>
        <div>
            Cập nhật dữ liệu thời gian thực
        </div>
    </div>
</div>

{{-- ===== MODAL 1: THÊM MỚI KHÁCH HÀNG ===== --}}
<x-modal name="create-customer-modal" maxWidth="7xl">
    <div class="px-8 py-5 border-b border-neutral-200 flex items-center justify-between shrink-0 bg-white">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-xl shadow-xs">
                <iconify-icon icon="solar:user-plus-bold-duotone"></iconify-icon>
            </div>
            <div>
                <h5 class="font-bold text-lg text-neutral-800 m-0">Thêm mới khách hàng (Nhóm: {{ $marketGroup->name }})</h5>
                <p class="text-xs text-neutral-400 m-0 mt-0.5">Nhập đầy đủ thông tin, định vị bản đồ và hồ sơ thị trường của khách hàng</p>
            </div>
        </div>
        <button type="button" onclick="closeModal('create-customer-modal')" class="text-neutral-400 hover:text-neutral-700 text-2xl leading-none w-8 h-8 flex items-center justify-center rounded-lg hover:bg-neutral-100 transition-colors">&times;</button>
    </div>
    <form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col overflow-hidden max-h-[calc(94vh-80px)]">
        @csrf
        <div class="p-8 overflow-y-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
                {{-- Cột 1: Thông tin cơ bản, Địa chỉ & Bản đồ Leaflet --}}
                <div class="lg:col-span-6 space-y-4">
                    <div class="border-b border-neutral-200 pb-2 flex items-center gap-2.5">
                        <iconify-icon icon="solar:user-id-bold-duotone" class="text-primary-600 text-xl"></iconify-icon>
                        <h6 class="font-bold text-sm uppercase tracking-wider text-neutral-800 m-0">Thông tin cơ bản & Vị trí</h6>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Mã khách hàng</label>
                            <input type="text" name="customer_code" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Tự sinh nếu để trống">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Nhóm thị trường</label>
                            <select name="market_group_id" class="form-select rounded-xl text-sm py-2.5 px-3.5 w-full">
                                @foreach($marketGroups as $mg)
                                    <option value="{{ $mg->id }}" {{ $mg->id == $marketGroup->id ? 'selected' : '' }}>
                                        {{ $mg->name }} ({{ $mg->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Tên khách hàng <span class="text-danger-500">*</span></label>
                            <input type="text" name="name" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Tên khách hàng / Xưởng" required>
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Nhập số điện thoại">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Tỉnh / Thành phố</label>
                            <input type="text" name="province" id="create_province" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Ví dụ: Hà Nội, Bắc Ninh...">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Quận / Huyện / Xã</label>
                            <input type="text" name="ward" id="create_ward" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Ví dụ: Thạch Thất, Hữu Bằng...">
                        </div>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Địa chỉ chi tiết</label>
                        <input type="text" name="address" id="create_address" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Ví dụ: Số 12 Đội 5, Làng nghề...">
                    </div>

                    {{-- Leaflet Map & GPS Location Picker --}}
                    <div class="bg-neutral-50/80 rounded-2xl p-4 border border-neutral-200 space-y-3 mt-2">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <iconify-icon icon="solar:map-point-wave-bold" class="text-primary-600 text-lg"></iconify-icon>
                                <span class="font-bold text-xs uppercase tracking-wide text-neutral-800">Định vị bản đồ (Leaflet)</span>
                            </div>
                            <button type="button" onclick="getCreateCurrentLocation()" id="btn-create-gps" class="btn btn-sm bg-white text-primary-700 hover:bg-primary-50 border border-primary-200 rounded-xl px-3 py-1.5 text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-all">
                                <iconify-icon icon="lucide:crosshair" class="text-sm text-primary-600"></iconify-icon> Lấy vị trí hiện tại
                            </button>
                        </div>
                        
                        <input type="hidden" name="latitude" id="create_latitude">
                        <input type="hidden" name="longitude" id="create_longitude">

                        <div id="create-customer-map" style="height: 240px; width: 100%; border-radius: 12px;" class="border border-neutral-300 shadow-inner"></div>

                        <div class="flex items-center justify-between text-xs text-neutral-500 pt-0.5">
                            <span>Toạ độ: <strong id="create_coords_display" class="text-neutral-800 font-mono">Chưa ghim vị trí</strong></span>
                            <span class="text-neutral-400 italic">Click hoặc kéo ghim đỏ để chọn vị trí chính xác</span>
                        </div>
                    </div>

                    {{-- Khối Chụp ảnh & Đính kèm ảnh có Timestamp --}}
                    <div class="bg-neutral-50/80 rounded-2xl p-4 border border-neutral-200 space-y-3 mt-3">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <iconify-icon icon="solar:camera-bold-duotone" class="text-primary-600 text-xl"></iconify-icon>
                                <div>
                                    <span class="font-bold text-xs uppercase tracking-wide text-neutral-800 block">Ảnh xưởng, bảng biểu & chụp ảnh (Có Timestamp)</span>
                                    <span class="text-[11px] text-neutral-400">Tự động đóng dấu Ngày giờ, Tên KH, Toạ độ GPS lên ảnh</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="document.getElementById('create-camera-input').click()" class="btn btn-sm bg-primary-600 text-white hover:bg-primary-700 rounded-xl px-3 py-1.5 text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-all">
                                    <iconify-icon icon="solar:camera-bold" class="text-sm"></iconify-icon> Chụp ảnh
                                </button>
                                <button type="button" onclick="document.getElementById('create-file-input').click()" class="btn btn-sm bg-white text-neutral-700 hover:bg-neutral-100 border border-neutral-200 rounded-xl px-3 py-1.5 text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-all">
                                    <iconify-icon icon="solar:gallery-bold" class="text-sm text-neutral-500"></iconify-icon> Chọn từ máy
                                </button>
                            </div>
                        </div>

                        <!-- Hidden file inputs -->
                        <input type="file" id="create-camera-input" accept="image/*" capture="environment" class="hidden" onchange="handleCustomerPhotoUpload(this, 'create')">
                        <input type="file" id="create-file-input" accept="image/*" multiple class="hidden" onchange="handleCustomerPhotoUpload(this, 'create')">

                        <!-- Dynamic photo inputs container -->
                        <div id="create-photo-inputs-container"></div>

                        <!-- Previews -->
                        <div id="create-photo-previews" class="flex flex-wrap gap-2.5 min-h-[50px] p-2.5 bg-white rounded-xl border border-dashed border-neutral-300">
                            <div id="create-photo-empty-hint" class="text-center w-full py-3 text-neutral-400 text-xs flex items-center justify-center gap-1.5">
                                <iconify-icon icon="solar:camera-add-linear" class="text-base"></iconify-icon> Chưa có ảnh nào được chụp hoặc tải lên
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cột 2: Hồ sơ thị trường, đối tác & CSKH --}}
                <div class="lg:col-span-6 space-y-4">
                    <div class="border-b border-neutral-200 pb-2 flex items-center gap-2.5">
                        <iconify-icon icon="solar:chart-square-bold-duotone" class="text-primary-600 text-xl"></iconify-icon>
                        <h6 class="font-bold text-sm uppercase tracking-wider text-neutral-800 m-0">Hồ sơ thị trường & CSKH</h6>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Tình trạng hợp tác</label>
                        <select name="status" class="form-select rounded-xl text-sm py-2.5 px-3.5 w-full">
                            <option value="Đang đặt hàng">Đang đặt hàng</option>
                            <option value="Không đặt GERVIN">Không đặt GERVIN</option>
                            <option value="Khách hàng mới tiềm năng">Khách hàng mới tiềm năng</option>
                            <option value="Tạm dừng hợp tác">Tạm dừng hợp tác</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-0">Đối tác KH đang hợp tác</label>
                            <span class="text-[11px] text-neutral-400">Bấm chọn nhanh hoặc gõ trực tiếp:</span>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap mb-2" id="create-partner-pills">
                            <button type="button" onclick="togglePartnerTag('create-partner-pills', 'create_partner_competitors', 'ALD')" data-partner="ALD" class="px-3 py-1 rounded-lg text-xs font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200 hover:border-primary-400 hover:bg-neutral-200 transition-all">+ ALD</button>
                            <button type="button" onclick="togglePartnerTag('create-partner-pills', 'create_partner_competitors', 'LIVAS')" data-partner="LIVAS" class="px-3 py-1 rounded-lg text-xs font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200 hover:border-primary-400 hover:bg-neutral-200 transition-all">+ LIVAS</button>
                            <button type="button" onclick="togglePartnerTag('create-partner-pills', 'create_partner_competitors', 'GERVIN')" data-partner="GERVIN" class="px-3 py-1 rounded-lg text-xs font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200 hover:border-primary-400 hover:bg-neutral-200 transition-all">+ GERVIN</button>
                            <button type="button" onclick="togglePartnerTag('create-partner-pills', 'create_partner_competitors', 'ĐỖ THÀNH ĐẠT')" data-partner="ĐỖ THÀNH ĐẠT" class="px-3 py-1 rounded-lg text-xs font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200 hover:border-primary-400 hover:bg-neutral-200 transition-all">+ ĐỖ THÀNH ĐẠT</button>
                        </div>
                        <input type="text" name="partner_competitors" id="create_partner_competitors" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Ví dụ: ALD, LIVAS, GERVIN...">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Quy mô xưởng</label>
                            <input type="text" name="workshop_scale" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Ví dụ: 300m2, 5 thợ, 2 máy dán...">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Tính cách khách hàng</label>
                            <input type="text" name="personality" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Ví dụ: Kỹ tính, cẩn thận đường keo...">
                        </div>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Phản ánh khách hàng về Gervin</label>
                        <textarea name="feedback" rows="2" class="form-control rounded-xl text-sm py-2 px-3.5" placeholder="Ghi nhận phản hồi về chất lượng nẹp, keo PUR, tiến độ giao hàng..."></textarea>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Đề xuất Khách hàng (nếu có)</label>
                        <textarea name="customer_proposal" rows="2" class="form-control rounded-xl text-sm py-2 px-3.5" placeholder="Mong muốn, yêu cầu hoặc đề xuất từ phía khách hàng..."></textarea>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Đề xuất Sale (CSKH / kéo khách / mở mới)</label>
                        <textarea name="sale_proposal" rows="2" class="form-control rounded-xl text-sm py-2 px-3.5" placeholder="Kế hoạch chăm sóc, kéo khách quay lại hoặc mở mới..."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-1">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Công nợ ban đầu (₫)</label>
                            <input type="number" name="initial_debt" min="0" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Ví dụ: 10000000">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Định mức công nợ (₫)</label>
                            <input type="number" name="debt_limit" min="0" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Ví dụ: 50000000">
                        </div>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Chính sách khách hàng</label>
                        <input type="text" name="policy" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Chính sách giá, chiết khấu, điều khoản thanh toán...">
                    </div>
                </div>
            </div>
        </div>
        <div class="px-8 py-4 border-t border-neutral-200 flex justify-end gap-3.5 shrink-0 bg-white">
            <button type="button" onclick="closeModal('create-customer-modal')" class="btn btn-neutral px-6 py-2.5 text-xs rounded-xl font-semibold">Hủy</button>
            <button type="submit" class="btn btn-primary px-7 py-2.5 text-xs rounded-xl font-semibold flex items-center gap-2 shadow-sm">
                <iconify-icon icon="lucide:check" class="text-base"></iconify-icon> Lưu khách hàng
            </button>
        </div>
    </form>
</x-modal>

{{-- ===== MODAL 2: CHỈNH SỬA KHÁCH HÀNG ===== --}}
<x-modal name="edit-customer-modal" maxWidth="7xl">
    <div class="px-8 py-5 border-b border-neutral-200 flex items-center justify-between shrink-0 bg-white">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-xs">
                <iconify-icon icon="solar:pen-new-square-bold-duotone"></iconify-icon>
            </div>
            <div>
                <h5 class="font-bold text-lg text-neutral-800 m-0">Chỉnh sửa thông tin khách hàng</h5>
                <p class="text-xs text-neutral-400 m-0 mt-0.5">Cập nhật thông tin chi tiết, bản đồ toạ độ và hồ sơ thị trường</p>
            </div>
        </div>
        <button type="button" onclick="closeModal('edit-customer-modal')" class="text-neutral-400 hover:text-neutral-700 text-2xl leading-none w-8 h-8 flex items-center justify-center rounded-lg hover:bg-neutral-100 transition-colors">&times;</button>
    </div>
    <form id="edit-customer-form" action="" method="POST" enctype="multipart/form-data" class="flex flex-col overflow-hidden max-h-[calc(94vh-80px)]">
        @csrf @method('PUT')
        <div class="p-8 overflow-y-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
                {{-- Cột 1: Thông tin cơ bản, Địa chỉ & Bản đồ Leaflet --}}
                <div class="lg:col-span-6 space-y-4">
                    <div class="border-b border-neutral-200 pb-2 flex items-center gap-2.5">
                        <iconify-icon icon="solar:user-id-bold-duotone" class="text-primary-600 text-xl"></iconify-icon>
                        <h6 class="font-bold text-sm uppercase tracking-wider text-neutral-800 m-0">Thông tin cơ bản & Vị trí</h6>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Mã khách hàng</label>
                            <input type="text" id="edit_customer_code" name="customer_code" class="form-control rounded-xl text-sm py-2.5 px-3.5">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Nhóm thị trường</label>
                            <select id="edit_market_group_id" name="market_group_id" class="form-select rounded-xl text-sm py-2.5 px-3.5 w-full">
                                <option value="">-- Chưa chọn nhóm --</option>
                                @foreach($marketGroups as $mg)
                                    <option value="{{ $mg->id }}">
                                        {{ $mg->name }} ({{ $mg->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Tên khách hàng <span class="text-danger-500">*</span></label>
                            <input type="text" id="edit_name" name="name" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Nhập tên khách hàng" required>
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Số điện thoại</label>
                            <input type="text" id="edit_phone" name="phone" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Nhập số điện thoại">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Tỉnh / Thành phố</label>
                            <input type="text" id="edit_province" name="province" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Ví dụ: Hà Nội, Bắc Ninh...">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Quận / Huyện / Xã</label>
                            <input type="text" id="edit_ward" name="ward" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Ví dụ: Thạch Thất, Hữu Bằng...">
                        </div>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Địa chỉ chi tiết</label>
                        <input type="text" id="edit_address" name="address" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Nhập địa chỉ chi tiết">
                    </div>

                    {{-- Leaflet Map & GPS Location Picker --}}
                    <div class="bg-neutral-50/80 rounded-2xl p-4 border border-neutral-200 space-y-3 mt-2">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <iconify-icon icon="solar:map-point-wave-bold" class="text-primary-600 text-lg"></iconify-icon>
                                <span class="font-bold text-xs uppercase tracking-wide text-neutral-800">Định vị bản đồ (Leaflet)</span>
                            </div>
                            <button type="button" onclick="getEditCurrentLocation()" id="btn-edit-gps" class="btn btn-sm bg-white text-primary-700 hover:bg-primary-50 border border-primary-200 rounded-xl px-3 py-1.5 text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-all">
                                <iconify-icon icon="lucide:crosshair" class="text-sm text-primary-600"></iconify-icon> Lấy vị trí hiện tại
                            </button>
                        </div>
                        
                        <input type="hidden" name="latitude" id="edit_latitude">
                        <input type="hidden" name="longitude" id="edit_longitude">

                        <div id="edit-customer-map" style="height: 240px; width: 100%; border-radius: 12px;" class="border border-neutral-300 shadow-inner"></div>

                        <div class="flex items-center justify-between text-xs text-neutral-500 pt-0.5">
                            <span>Toạ độ: <strong id="edit_coords_display" class="text-neutral-800 font-mono">Chưa ghim vị trí</strong></span>
                            <span class="text-neutral-400 italic">Click hoặc kéo ghim đỏ để chọn vị trí chính xác</span>
                        </div>
                    </div>

                    {{-- Khối Chụp ảnh & Đính kèm ảnh có Timestamp --}}
                    <div class="bg-neutral-50/80 rounded-2xl p-4 border border-neutral-200 space-y-3 mt-3">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <iconify-icon icon="solar:camera-bold-duotone" class="text-primary-600 text-xl"></iconify-icon>
                                <div>
                                    <span class="font-bold text-xs uppercase tracking-wide text-neutral-800 block">Ảnh xưởng, bảng biểu & chụp ảnh (Có Timestamp)</span>
                                    <span class="text-[11px] text-neutral-400">Đóng dấu ngày giờ, toạ độ GPS và tên khách hàng vào ảnh</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="document.getElementById('edit-camera-input').click()" class="btn btn-sm bg-primary-600 text-white hover:bg-primary-700 rounded-xl px-3 py-1.5 text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-all">
                                    <iconify-icon icon="solar:camera-bold" class="text-sm"></iconify-icon> Chụp ảnh
                                </button>
                                <button type="button" onclick="document.getElementById('edit-file-input').click()" class="btn btn-sm bg-white text-neutral-700 hover:bg-neutral-100 border border-neutral-200 rounded-xl px-3 py-1.5 text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-all">
                                    <iconify-icon icon="solar:gallery-bold" class="text-sm text-neutral-500"></iconify-icon> Chọn từ máy
                                </button>
                            </div>
                        </div>

                        <!-- Hidden file inputs -->
                        <input type="file" id="edit-camera-input" accept="image/*" capture="environment" class="hidden" onchange="handleCustomerPhotoUpload(this, 'edit')">
                        <input type="file" id="edit-file-input" accept="image/*" multiple class="hidden" onchange="handleCustomerPhotoUpload(this, 'edit')">

                        <!-- Dynamic photo inputs & keep photos container -->
                        <div id="edit-photo-inputs-container"></div>

                        <!-- Previews -->
                        <div id="edit-photo-previews" class="flex flex-wrap gap-2.5 min-h-[50px] p-2.5 bg-white rounded-xl border border-dashed border-neutral-300">
                            <div id="edit-photo-empty-hint" class="text-center w-full py-3 text-neutral-400 text-xs flex items-center justify-center gap-1.5">
                                <iconify-icon icon="solar:camera-add-linear" class="text-base"></iconify-icon> Chưa có ảnh nào được chụp hoặc tải lên
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cột 2: Hồ sơ thị trường, đối tác & CSKH --}}
                <div class="lg:col-span-6 space-y-4">
                    <div class="border-b border-neutral-200 pb-2 flex items-center gap-2.5">
                        <iconify-icon icon="solar:chart-square-bold-duotone" class="text-primary-600 text-xl"></iconify-icon>
                        <h6 class="font-bold text-sm uppercase tracking-wider text-neutral-800 m-0">Hồ sơ thị trường & CSKH</h6>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Tình trạng hợp tác</label>
                        <select id="edit_status" name="status" class="form-select rounded-xl text-sm py-2.5 px-3.5 w-full">
                            <option value="Đang đặt hàng">Đang đặt hàng</option>
                            <option value="Không đặt GERVIN">Không đặt GERVIN</option>
                            <option value="Khách hàng mới tiềm năng">Khách hàng mới tiềm năng</option>
                            <option value="Tạm dừng hợp tác">Tạm dừng hợp tác</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-0">Đối tác KH đang hợp tác</label>
                            <span class="text-[11px] text-neutral-400">Bấm chọn nhanh hoặc gõ trực tiếp:</span>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap mb-2" id="edit-partner-pills">
                            <button type="button" onclick="togglePartnerTag('edit-partner-pills', 'edit_partner_competitors', 'ALD')" data-partner="ALD" class="px-3 py-1 rounded-lg text-xs font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200 hover:border-primary-400 hover:bg-neutral-200 transition-all">+ ALD</button>
                            <button type="button" onclick="togglePartnerTag('edit-partner-pills', 'edit_partner_competitors', 'LIVAS')" data-partner="LIVAS" class="px-3 py-1 rounded-lg text-xs font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200 hover:border-primary-400 hover:bg-neutral-200 transition-all">+ LIVAS</button>
                            <button type="button" onclick="togglePartnerTag('edit-partner-pills', 'edit_partner_competitors', 'GERVIN')" data-partner="GERVIN" class="px-3 py-1 rounded-lg text-xs font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200 hover:border-primary-400 hover:bg-neutral-200 transition-all">+ GERVIN</button>
                            <button type="button" onclick="togglePartnerTag('edit-partner-pills', 'edit_partner_competitors', 'ĐỖ THÀNH ĐẠT')" data-partner="ĐỖ THÀNH ĐẠT" class="px-3 py-1 rounded-lg text-xs font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200 hover:border-primary-400 hover:bg-neutral-200 transition-all">+ ĐỖ THÀNH ĐẠT</button>
                        </div>
                        <input type="text" id="edit_partner_competitors" name="partner_competitors" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Ví dụ: ALD, LIVAS, GERVIN...">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Quy mô xưởng</label>
                            <input type="text" id="edit_workshop_scale" name="workshop_scale" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Ví dụ: 300m2, 5 thợ, máy dán...">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Tính cách khách hàng</label>
                            <input type="text" id="edit_personality" name="personality" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Ví dụ: Kỹ tính, cẩn thận đường keo...">
                        </div>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Phản ánh khách hàng về Gervin</label>
                        <textarea id="edit_feedback" name="feedback" rows="2" class="form-control rounded-xl text-sm py-2 px-3.5" placeholder="Ghi nhận phản hồi về chất lượng nẹp, keo PUR, tiến độ giao hàng..."></textarea>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Đề xuất Khách hàng (nếu có)</label>
                        <textarea id="edit_customer_proposal" name="customer_proposal" rows="2" class="form-control rounded-xl text-sm py-2 px-3.5" placeholder="Mong muốn, yêu cầu hoặc đề xuất từ phía khách hàng..."></textarea>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Đề xuất Sale (CSKH / kéo khách / mở mới)</label>
                        <textarea id="edit_sale_proposal" name="sale_proposal" rows="2" class="form-control rounded-xl text-sm py-2 px-3.5" placeholder="Kế hoạch chăm sóc, kéo khách quay lại hoặc mở mới..."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-1">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Công nợ ban đầu (₫)</label>
                            <input type="number" id="edit_initial_debt" name="initial_debt" min="0" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Ví dụ: 10000000">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Định mức công nợ (₫)</label>
                            <input type="number" id="edit_debt_limit" name="debt_limit" min="0" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Ví dụ: 50000000">
                        </div>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Chính sách khách hàng</label>
                        <input type="text" id="edit_policy" name="policy" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Chính sách giá, chiết khấu, điều khoản thanh toán...">
                    </div>
                </div>
            </div>
        </div>
        <div class="px-8 py-4 border-t border-neutral-200 flex justify-end gap-3.5 shrink-0 bg-white">
            <button type="button" onclick="closeModal('edit-customer-modal')" class="btn btn-neutral px-6 py-2.5 text-xs rounded-xl font-semibold">Hủy</button>
            <button type="submit" class="btn btn-primary px-7 py-2.5 text-xs rounded-xl font-semibold flex items-center gap-2 shadow-sm">
                <iconify-icon icon="lucide:check" class="text-base"></iconify-icon> Lưu thay đổi
            </button>
        </div>
    </form>
</x-modal>

{{-- ===== IMAGE LIGHTBOX MODAL (XEM ẢNH TIMESTAMP PHÓNG TO) ===== --}}
<div id="image-lightbox-modal" class="hidden fixed inset-0 z-[3000] bg-black/85 backdrop-blur-sm flex flex-col items-center justify-center p-4" onclick="closeImageLightbox()">
    <div class="relative max-w-4xl max-h-[92vh] flex flex-col items-center" onclick="event.stopPropagation()">
        <button type="button" onclick="closeImageLightbox()"
                class="absolute -top-12 right-0 text-white/80 hover:text-white bg-neutral-900/70 rounded-full p-2 hover:bg-neutral-900 transition flex items-center justify-center shadow-lg">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
        </button>
        <img id="image-lightbox-img" src="" alt="Customer Photo" class="max-h-[82vh] max-w-full rounded-xl shadow-2xl border border-white/20 object-contain bg-black">
        <div id="image-lightbox-caption" class="mt-3 text-white/90 text-xs md:text-sm font-medium bg-neutral-900/80 px-4 py-1.5 rounded-full border border-white/10 max-w-xl text-center truncate"></div>
    </div>
</div>

<script>
let createMap = null;
let createMarker = null;
let editMap = null;
let editMarker = null;

document.addEventListener('DOMContentLoaded', function () {
    if (typeof TomSelect !== 'undefined' && document.getElementById('select-customers-to-add')) {
        new TomSelect('#select-customers-to-add', {
            plugins: ['remove_button'],
            placeholder: 'Chọn một hoặc nhiều khách hàng để thêm vào nhóm...',
            maxItems: null,
        });
    }
});

function filterPageCustomerTable() {
    const input = document.getElementById('page-customer-search');
    const term = (input ? input.value : '').toLowerCase().trim();
    const rows = document.querySelectorAll('.customer-row');
    const noSearchRow = document.getElementById('page-customers-no-search-row');
    const badge = document.getElementById('page-search-count-badge');

    let visibleCount = 0;
    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        if (!term || text.includes(term)) {
            row.style.display = '';
            visibleCount++;
            const sttCell = row.querySelector('.row-stt');
            if (sttCell) sttCell.textContent = visibleCount;
        } else {
            row.style.display = 'none';
        }
    });

    if (badge) {
        badge.textContent = `${visibleCount} khách hàng`;
    }

    if (noSearchRow) {
        if (visibleCount === 0 && rows.length > 0) {
            noSearchRow.classList.remove('hidden');
        } else {
            noSearchRow.classList.add('hidden');
        }
    }
}

// ===== DATE FILTER FLEXIBLE SWITCHER =====
function setDateMode(mode) {
    const hiddenMode = document.getElementById('date_mode');
    const container = document.getElementById('date-input-container');
    const oldInput = document.getElementById('date_val');
    const oldVal = oldInput ? oldInput.value : '';
    
    hiddenMode.value = mode;
    updateModeButtonStyles(mode);

    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    const todayStr = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}`;
    const thisMonthStr = `${now.getFullYear()}-${pad(now.getMonth()+1)}`;
    const thisYearStr = `${now.getFullYear()}`;

    if (mode === 'day') {
        const val = (oldVal && oldVal.length === 10) ? oldVal : todayStr;
        container.innerHTML = `<input type="date" name="date_val" id="date_val" value="${val}" class="border-0 bg-transparent text-xs py-1 px-1.5 font-medium text-neutral-800 outline-none cursor-pointer" onchange="this.form.submit()">`;
    } else if (mode === 'month') {
        const val = (oldVal && oldVal.length >= 7) ? oldVal.substring(0, 7) : thisMonthStr;
        container.innerHTML = `<input type="month" name="date_val" id="date_val" value="${val}" class="border-0 bg-transparent text-xs py-1 px-1.5 font-medium text-neutral-800 outline-none cursor-pointer" onchange="this.form.submit()">`;
    } else if (mode === 'year') {
        const val = (oldVal && oldVal.length >= 4) ? oldVal.substring(0, 4) : thisYearStr;
        container.innerHTML = `<input type="number" min="2000" max="2099" name="date_val" id="date_val" value="${val}" class="border-0 bg-transparent text-xs py-1 px-1.5 font-medium text-neutral-800 outline-none w-20" placeholder="Năm..." onchange="this.form.submit()">`;
    } else {
        container.innerHTML = `<input type="text" readonly value="Toàn thời gian" class="border-0 bg-transparent text-xs py-1 px-2 font-semibold text-neutral-500 w-28 cursor-default outline-none">`;
    }

    // Auto submit form on mode change
    document.getElementById('dateFilterForm').submit();
}

function updateModeButtonStyles(activeMode) {
    ['day', 'month', 'year', 'all'].forEach(m => {
        const btn = document.getElementById('mode-btn-' + m);
        if (!btn) return;
        if (m === activeMode) {
            btn.className = 'px-2.5 py-1 rounded-md transition-all bg-white text-primary-700 shadow-2xs font-bold';
        } else {
            btn.className = 'px-2.5 py-1 rounded-md transition-all text-neutral-500 hover:text-neutral-800';
        }
    });
}

// ===== LEAFLET MAP FUNCTIONS =====
function setCreateCoords(lat, lng) {
    const latFixed = Number(lat).toFixed(7);
    const lngFixed = Number(lng).toFixed(7);
    document.getElementById('create_latitude').value = latFixed;
    document.getElementById('create_longitude').value = lngFixed;
    const textEl = document.getElementById('create_coords_display');
    if (textEl) {
        textEl.textContent = `${latFixed}, ${lngFixed}`;
    }
}

function initCreateMap(defaultLat, defaultLng) {
    const lat = Number(defaultLat) || 20.9800;
    const lng = Number(defaultLng) || 105.7800;
    const hasExistingCoords = !!(defaultLat && defaultLng);

    setTimeout(() => {
        if (!createMap) {
            createMap = L.map('create-customer-map').setView([lat, lng], 14);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(createMap);

            createMarker = L.marker([lat, lng], { draggable: true }).addTo(createMap);

            createMap.on('click', function(e) {
                createMarker.setLatLng(e.latlng);
                setCreateCoords(e.latlng.lat, e.latlng.lng);
            });

            createMarker.on('dragend', function(e) {
                const pos = createMarker.getLatLng();
                setCreateCoords(pos.lat, pos.lng);
            });
        } else {
            createMap.setView([lat, lng], 14);
            createMarker.setLatLng([lat, lng]);
            createMap.invalidateSize();
        }

        if (hasExistingCoords) {
            setCreateCoords(lat, lng);
        } else {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    const myLat = pos.coords.latitude;
                    const myLng = pos.coords.longitude;
                    if (createMap) {
                        createMap.setView([myLat, myLng], 15);
                        createMarker.setLatLng([myLat, myLng]);
                    }
                    setCreateCoords(myLat, myLng);
                }, function() {}, { enableHighAccuracy: true, timeout: 5000 });
            }
        }
    }, 200);
}

function getCreateCurrentLocation() {
    if (!navigator.geolocation) {
        alert('Trình duyệt không hỗ trợ Geolocation.');
        return;
    }
    const btn = document.getElementById('btn-create-gps');
    if (btn) btn.innerHTML = '<iconify-icon icon="lucide:loader-2" class="animate-spin"></iconify-icon> Đang tìm...';
    navigator.geolocation.getCurrentPosition(function(pos) {
        if (btn) btn.innerHTML = '<iconify-icon icon="lucide:crosshair"></iconify-icon> Lấy vị trí hiện tại';
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        if (createMap) {
            createMap.setView([lat, lng], 16);
            createMarker.setLatLng([lat, lng]);
        }
        setCreateCoords(lat, lng);
    }, function(err) {
        if (btn) btn.innerHTML = '<iconify-icon icon="lucide:crosshair"></iconify-icon> Lấy vị trí hiện tại';
        alert('Không thể xác định vị trí: ' + err.message);
    }, { enableHighAccuracy: true, timeout: 8000 });
}

function setEditCoords(lat, lng) {
    const latFixed = Number(lat).toFixed(7);
    const lngFixed = Number(lng).toFixed(7);
    document.getElementById('edit_latitude').value = latFixed;
    document.getElementById('edit_longitude').value = lngFixed;
    const textEl = document.getElementById('edit_coords_display');
    if (textEl) {
        textEl.textContent = `${latFixed}, ${lngFixed}`;
    }
}

function initEditMap(defaultLat, defaultLng) {
    const lat = Number(defaultLat) || 20.9800;
    const lng = Number(defaultLng) || 105.7800;
    const hasExistingCoords = !!(defaultLat && defaultLng);

    setTimeout(() => {
        if (!editMap) {
            editMap = L.map('edit-customer-map').setView([lat, lng], 14);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(editMap);

            editMarker = L.marker([lat, lng], { draggable: true }).addTo(editMap);

            editMap.on('click', function(e) {
                editMarker.setLatLng(e.latlng);
                setEditCoords(e.latlng.lat, e.latlng.lng);
            });

            editMarker.on('dragend', function(e) {
                const pos = editMarker.getLatLng();
                setEditCoords(pos.lat, pos.lng);
            });
        } else {
            editMap.setView([lat, lng], 14);
            editMarker.setLatLng([lat, lng]);
            editMap.invalidateSize();
        }

        if (hasExistingCoords) {
            setEditCoords(lat, lng);
        } else {
            document.getElementById('edit_latitude').value = '';
            document.getElementById('edit_longitude').value = '';
            const textEl = document.getElementById('edit_coords_display');
            if (textEl) textEl.textContent = 'Chưa ghim vị trí';
        }
    }, 200);
}

function getEditCurrentLocation() {
    if (!navigator.geolocation) {
        alert('Trình duyệt không hỗ trợ Geolocation.');
        return;
    }
    const btn = document.getElementById('btn-edit-gps');
    if (btn) btn.innerHTML = '<iconify-icon icon="lucide:loader-2" class="animate-spin"></iconify-icon> Đang tìm...';
    navigator.geolocation.getCurrentPosition(function(pos) {
        if (btn) btn.innerHTML = '<iconify-icon icon="lucide:crosshair"></iconify-icon> Lấy vị trí hiện tại';
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        if (editMap) {
            editMap.setView([lat, lng], 16);
            editMarker.setLatLng([lat, lng]);
        }
        setEditCoords(lat, lng);
    }, function(err) {
        if (btn) btn.innerHTML = '<iconify-icon icon="lucide:crosshair"></iconify-icon> Lấy vị trí hiện tại';
        alert('Không thể xác định vị trí: ' + err.message);
    }, { enableHighAccuracy: true, timeout: 8000 });
}

function togglePartnerTag(containerId, inputId, partnerName) {
    const input = document.getElementById(inputId);
    if (!input) return;
    let list = input.value.split(',').map(s => s.trim()).filter(Boolean);
    const idx = list.indexOf(partnerName);
    if (idx > -1) {
        list.splice(idx, 1);
    } else {
        list.push(partnerName);
    }
    input.value = list.join(', ');
    updatePartnerTagButtons(containerId, inputId);
}

function updatePartnerTagButtons(containerId, inputId) {
    const input = document.getElementById(inputId);
    const container = document.getElementById(containerId);
    if (!input || !container) return;
    const list = input.value.split(',').map(s => s.trim().toUpperCase()).filter(Boolean);
    container.querySelectorAll('[data-partner]').forEach(btn => {
        const p = btn.getAttribute('data-partner').toUpperCase();
        if (list.includes(p)) {
            btn.classList.add('bg-primary-600', 'text-white', 'border-primary-600');
            btn.classList.remove('bg-neutral-100', 'text-neutral-700', 'border-neutral-200');
        } else {
            btn.classList.remove('bg-primary-600', 'text-white', 'border-primary-600');
            btn.classList.add('bg-neutral-100', 'text-neutral-700', 'border-neutral-200');
        }
    });
}

function openCreateCustomerModal() {
    updatePartnerTagButtons('create-partner-pills', 'create_partner_competitors');
    
    // Reset photo previews in create modal
    const cPreviews = document.getElementById('create-photo-previews');
    if (cPreviews) {
        cPreviews.querySelectorAll('.relative').forEach(el => el.remove());
        const hint = document.getElementById('create-photo-empty-hint');
        if (hint) hint.classList.remove('hidden');
    }
    const cInputs = document.getElementById('create-photo-inputs-container');
    if (cInputs) cInputs.innerHTML = '';

    openModal('create-customer-modal');
    initCreateMap(
        document.getElementById('create_latitude').value,
        document.getElementById('create_longitude').value
    );
}

function openEditCustomerModalFromBtn(btn) {
    const id = btn.getAttribute('data-id');
    const code = btn.getAttribute('data-code') || '';
    const name = btn.getAttribute('data-name') || '';
    const phone = btn.getAttribute('data-phone') || '';
    const province = btn.getAttribute('data-province') || '';
    const ward = btn.getAttribute('data-ward') || '';
    const address = btn.getAttribute('data-address') || '';
    const lat = btn.getAttribute('data-latitude') || '';
    const lng = btn.getAttribute('data-longitude') || '';
    const status = btn.getAttribute('data-status') || 'Đang đặt hàng';
    const partner = btn.getAttribute('data-partner-competitors') || '';
    const scale = btn.getAttribute('data-workshop-scale') || '';
    const personality = btn.getAttribute('data-personality') || '';
    const feedback = btn.getAttribute('data-feedback') || '';
    const custProposal = btn.getAttribute('data-customer-proposal') || '';
    const saleProposal = btn.getAttribute('data-sale-proposal') || '';
    const debt = btn.getAttribute('data-debt') || 0;
    const debtLimit = btn.getAttribute('data-debt-limit') || 0;
    const policy = btn.getAttribute('data-policy') || '';
    const marketGroupId = btn.getAttribute('data-market-group-id') || '';

    document.getElementById('edit-customer-form').action = '/customers/' + id;
    document.getElementById('edit_customer_code').value = code;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_province').value = province;
    document.getElementById('edit_ward').value = ward;
    document.getElementById('edit_address').value = address;
    document.getElementById('edit_latitude').value = lat;
    document.getElementById('edit_longitude').value = lng;
    document.getElementById('edit_status').value = status;
    document.getElementById('edit_partner_competitors').value = partner;
    document.getElementById('edit_workshop_scale').value = scale;
    document.getElementById('edit_personality').value = personality;
    document.getElementById('edit_feedback').value = feedback;
    document.getElementById('edit_customer_proposal').value = custProposal;
    document.getElementById('edit_sale_proposal').value = saleProposal;
    document.getElementById('edit_initial_debt').value = debt;
    document.getElementById('edit_debt_limit').value = debtLimit;
    document.getElementById('edit_policy').value = policy;
    
    const mgSelect = document.getElementById('edit_market_group_id');
    if (mgSelect) {
        mgSelect.value = marketGroupId;
    }

    // Reset and populate existing photos in edit modal
    const ePreviews = document.getElementById('edit-photo-previews');
    if (ePreviews) {
        ePreviews.querySelectorAll('.relative').forEach(el => el.remove());
        const hint = document.getElementById('edit-photo-empty-hint');
        if (hint) hint.classList.remove('hidden');
    }
    const eInputs = document.getElementById('edit-photo-inputs-container');
    if (eInputs) eInputs.innerHTML = '';

    let photos = [];
    try {
        photos = JSON.parse(btn.getAttribute('data-photos') || '[]');
    } catch(e) {}
    if (Array.isArray(photos) && photos.length > 0) {
        photos.forEach(p => {
            addCustomerPhotoPreview(p, 'edit', true);
        });
    }

    updatePartnerTagButtons('edit-partner-pills', 'edit_partner_competitors');

    openModal('edit-customer-modal');
    initEditMap(lat, lng);
}

// ===== XỬ LÝ CHỤP ẢNH & ĐÓNG DẤU TIMESTAMP BẰNG HTML5 CANVAS =====
function handleCustomerPhotoUpload(input, mode) {
    if (!input.files || input.files.length === 0) return;

    const modalId = mode === 'create' ? 'create-customer-modal' : 'edit-customer-modal';
    const nameEl  = document.querySelector(`#${modalId} input[name="name"]`);
    const codeEl  = document.querySelector(`#${modalId} input[name="customer_code"]`);
    const latEl   = document.getElementById(mode + '_latitude');
    const lngEl   = document.getElementById(mode + '_longitude');
    const addrEl  = mode === 'create' ? document.getElementById('create_address') : document.getElementById('edit_address');
    const wardEl  = mode === 'create' ? document.getElementById('create_ward') : document.getElementById('edit_ward');
    const provEl  = mode === 'create' ? document.getElementById('create_province') : document.getElementById('edit_province');

    const custName = (nameEl ? nameEl.value.trim() : '') || 'Khách hàng';
    const custCode = codeEl ? codeEl.value.trim() : '';
    const coordsStr = (latEl && latEl.value && lngEl && lngEl.value) 
        ? `${Number(latEl.value).toFixed(5)}, ${Number(lngEl.value).toFixed(5)}` 
        : '';
    const fullAddrParts = [addrEl ? addrEl.value.trim() : '', wardEl ? wardEl.value.trim() : '', provEl ? provEl.value.trim() : ''].filter(Boolean);
    const addrStr = fullAddrParts.join(', ');

    const files = Array.from(input.files);
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const stampedBase64 = applyTimestampWatermark(img, {
                    name: custName,
                    code: custCode,
                    coords: coordsStr,
                    address: addrStr,
                });
                addCustomerPhotoPreview(stampedBase64, mode, false);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });

    input.value = '';
}

function applyTimestampWatermark(img, info) {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    let w = img.width;
    let h = img.height;
    const maxDim = 1400;
    if (w > maxDim || h > maxDim) {
        if (w > h) {
            h = Math.round((h * maxDim) / w);
            w = maxDim;
        } else {
            w = Math.round((w * maxDim) / h);
            h = maxDim;
        }
    }
    canvas.width = w;
    canvas.height = h;

    // Vẽ ảnh gốc
    ctx.drawImage(img, 0, 0, w, h);

    // Format thời gian ngày giờ hiện tại
    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    const timeStr = `${pad(now.getDate())}/${pad(now.getMonth()+1)}/${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;

    // Chiều cao khung watermark
    const bannerHeight = Math.max(76, Math.round(h * 0.12));
    const bannerY = h - bannerHeight;

    // Dải nền đen bán trong suốt
    const grad = ctx.createLinearGradient(0, bannerY, 0, h);
    grad.addColorStop(0, 'rgba(15, 23, 42, 0.82)');
    grad.addColorStop(1, 'rgba(2, 6, 23, 0.94)');
    ctx.fillStyle = grad;
    ctx.fillRect(0, bannerY, w, bannerHeight);

    // Dải viền cam nổi bật
    ctx.fillStyle = '#f59e0b';
    ctx.fillRect(0, bannerY, w, Math.max(3, Math.round(bannerHeight * 0.04)));

    // Tính kích thước chữ theo kích thước ảnh
    const fontSizeMain = Math.max(14, Math.round(bannerHeight * 0.28));
    const fontSizeSub  = Math.max(11, Math.round(bannerHeight * 0.22));
    const paddingX     = Math.max(16, Math.round(w * 0.025));
    const lineSpacing  = Math.round(bannerHeight * 0.29);

    ctx.textBaseline = 'top';

    // Dòng 1: Thời gian & Tên thương hiệu
    ctx.font = `bold ${fontSizeMain}px sans-serif`;
    ctx.fillStyle = '#ffffff';
    ctx.fillText(`🕒 ${timeStr}`, paddingX, bannerY + Math.round(bannerHeight * 0.14));

    const brandText = 'GERVIN WOOD';
    const brandWidth = ctx.measureText(brandText).width;
    ctx.fillStyle = '#fbbf24';
    ctx.fillText(brandText, w - brandWidth - paddingX, bannerY + Math.round(bannerHeight * 0.14));

    // Dòng 2: Tên & Mã khách hàng
    ctx.font = `bold ${fontSizeSub}px sans-serif`;
    ctx.fillStyle = '#38bdf8';
    const custIdentity = `👤 ${info.code ? '[' + info.code + '] ' : ''}${info.name}`;
    ctx.fillText(custIdentity, paddingX, bannerY + Math.round(bannerHeight * 0.14) + lineSpacing);

    // Dòng 3: Toạ độ GPS / Địa chỉ
    let locStr = '';
    if (info.coords) locStr += `📍 ${info.coords}`;
    if (info.address) locStr += (locStr ? '  |  ' : '📍 ') + info.address;
    if (!locStr) locStr = '📍 Đã chụp tại hiện trường';

    ctx.font = `${Math.max(10, fontSizeSub - 2)}px sans-serif`;
    ctx.fillStyle = '#cbd5e1';
    ctx.fillText(locStr, paddingX, bannerY + Math.round(bannerHeight * 0.14) + lineSpacing * 1.88);

    return canvas.toDataURL('image/jpeg', 0.85);
}

function addCustomerPhotoPreview(dataUrl, mode, isExisting) {
    const previewsContainer = document.getElementById(mode + '-photo-previews');
    const emptyHint         = document.getElementById(mode + '-photo-empty-hint');

    if (emptyHint) emptyHint.classList.add('hidden');

    const itemDiv = document.createElement('div');
    itemDiv.className = 'relative group w-20 h-20 rounded-xl overflow-hidden border border-neutral-300 shadow-sm shrink-0 bg-neutral-100';

    const fullSrc = isExisting ? (dataUrl.startsWith('http') || dataUrl.startsWith('/') ? dataUrl : '/' + dataUrl) : dataUrl;

    itemDiv.innerHTML = `
        <img src="${fullSrc}" class="w-full h-full object-cover cursor-pointer hover:scale-105 transition-transform" onclick="openImageLightbox('${fullSrc}', 'Ảnh chụp khách hàng')">
        <button type="button" onclick="removeCustomerPhoto(this, '${mode}')" class="absolute top-1 right-1 w-5 h-5 bg-rose-600 hover:bg-rose-700 text-white rounded-full flex items-center justify-center text-xs leading-none shadow-md transition-transform hover:scale-110" title="Xóa ảnh">&times;</button>
        <div class="absolute bottom-0 inset-x-0 bg-neutral-900/60 text-[9px] text-white text-center py-0.5 truncate px-1">
            ${isExisting ? 'Đã lưu' : 'Mới'}
        </div>
    `;

    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    if (isExisting) {
        hiddenInput.name = 'keep_photos[]';
        hiddenInput.value = dataUrl;
    } else {
        hiddenInput.name = 'photo_data[]';
        hiddenInput.value = dataUrl;
    }

    itemDiv.appendChild(hiddenInput);
    previewsContainer.appendChild(itemDiv);
}

function removeCustomerPhoto(btn, mode) {
    const item = btn.closest('.relative');
    if (item) item.remove();

    const previewsContainer = document.getElementById(mode + '-photo-previews');
    const emptyHint         = document.getElementById(mode + '-photo-empty-hint');
    if (previewsContainer && previewsContainer.querySelectorAll('.relative').length === 0) {
        if (emptyHint) emptyHint.classList.remove('hidden');
    }
}

// Lightbox modal handlers
function openImageLightbox(src, caption) {
    const modal = document.getElementById('image-lightbox-modal');
    const img   = document.getElementById('image-lightbox-img');
    const cap   = document.getElementById('image-lightbox-caption');
    if (img) img.src = src;
    if (cap) cap.textContent = caption || '';
    if (modal) modal.classList.remove('hidden');
}

function closeImageLightbox() {
    const modal = document.getElementById('image-lightbox-modal');
    if (modal) modal.classList.add('hidden');
}

function previewCustomerPhotos(photos, custName) {
    if (!photos || photos.length === 0) return;
    const first = photos[0];
    const full = first.startsWith('http') || first.startsWith('/') ? first : '/' + first;
    openImageLightbox(full, `Ảnh của khách: ${custName || ''} (${photos.length} ảnh)`);
}
</script>

<style>
.ts-dropdown {
    z-index: 999999 !important;
}
#edit-customer-modal [data-modal-content],
#create-customer-modal [data-modal-content] {
    max-height: 95vh !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
}
</style>

@endsection
