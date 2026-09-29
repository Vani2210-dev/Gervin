@extends('layout.layout')
@php
    $title    = 'Chi tiết kho';
    $subTitle = $warehouse->name;

    // Formatting closure: 0 or empty -> '-', else formatted number
    $fmt = function($val) {
        if ($val === null || $val === '' || (float)$val == 0) return '-';
        $num = (float)$val;
        return fmod($num, 1) == 0 ? number_format($num, 0, ',', '.') : number_format($num, 2, ',', '.');
    };

    $fmtStock = function($val) {
        $num = (float)$val;
        return fmod($num, 1) == 0 ? number_format($num, 0, ',', '.') : number_format($num, 2, ',', '.');
    };
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        {{-- Header Card --}}
        <div class="card p-0 rounded-xl border-0 mb-6 bg-white shadow-sm">
            <div class="card-body p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="text-xl font-bold text-neutral-800">{{ $warehouse->name }}</h3>
                            <span class="bg-primary-50 text-primary-600 text-xs font-semibold px-3 py-1 rounded-full">
                                {{ $warehouse->item_name ?: 'Chưa cấu hình hàng hóa' }}
                            </span>
                        </div>
                        <p class="text-secondary-light text-sm">Theo dõi chi tiết lịch sử giao dịch nhập, xuất và tổng hợp báo cáo tiêu thụ tồn kho định kỳ.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" onsubmit="return confirm('Xóa kho này sẽ mất vĩnh viễn toàn bộ lịch sử nhập xuất? Bạn có chắc chắn?')">
                            @csrf 
                            @method('DELETE')
                            <button type="submit" class="btn border border-danger-300 text-danger-600 hover:bg-danger-600 hover:text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200">
                                <iconify-icon icon="fluent:delete-24-regular" class="inline-block align-middle me-1 text-lg"></iconify-icon> Xóa kho
                            </button>
                        </form>
                        <button type="button" onclick="openModal('configWarehouseModal')" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            <iconify-icon icon="solar:settings-outline" class="inline-block align-middle me-1 text-lg"></iconify-icon> Cấu hình hàng hóa
                        </button>
                        @if($warehouse->item_name)
                            <button type="button" onclick="openModal('addRecordModal')" class="btn btn-primary px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-all hover:opacity-90">
                                <iconify-icon icon="ic:baseline-plus" class="inline-block align-middle me-1 text-lg"></iconify-icon> Thêm phiếu mới
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="mb-6 bg-success-100 border border-success-300 text-success-700 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
        @endif
        @if(isset($errors) && $errors->any())
        <div class="mb-6 bg-danger-100 border border-danger-300 text-danger-700 rounded-lg px-4 py-3 text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(!$warehouse->item_name)
            <div class="bg-white rounded-2xl border border-neutral-100 shadow-sm p-12 text-center">
                <iconify-icon icon="solar:info-circle-outline" class="text-5xl text-primary-500 mb-4"></iconify-icon>
                <h5 class="text-neutral-800 font-bold mb-2 text-base">Kho chưa được cấu hình!</h5>
                <p class="text-secondary-light text-sm mb-6 max-w-md mx-auto">Vui lòng nhấn vào nút <strong>"Cấu hình hàng hóa"</strong> ở trên để thiết lập tên sản phẩm và các kích thước hoặc nhập trực tiếp từ file Excel ma trận.</p>
                <div class="flex items-center justify-center gap-3">
                    <button type="button" onclick="openModal('configWarehouseModal')" class="btn btn-primary px-5 py-2 rounded-lg text-sm font-medium">
                        Cấu hình thủ công &rarr;
                    </button>
                    <button type="button" onclick="openModal('importMatrixModal')" class="btn border border-primary-500 text-primary-600 hover:bg-primary-50 px-5 py-2 rounded-lg text-sm font-medium">
                        <iconify-icon icon="solar:file-excel-bold" class="inline-block align-middle me-1 text-base"></iconify-icon> Nhập từ Excel Ma trận
                    </button>
                </div>
            </div>
        @else
            {{-- Tabs Navigation --}}
            <div class="flex flex-wrap items-center justify-between border-b border-neutral-200 mb-6 gap-3">
                <div class="flex items-center gap-2">
                    <a href="{{ route('warehouses.show', [$warehouse, 'tab' => 'matrix', 'year' => $selectedYear]) }}" 
                       class="flex items-center gap-2 px-5 py-3 text-sm font-bold border-b-2 transition-all {{ $currentTab === 'matrix' ? 'border-primary-600 text-primary-600 bg-white rounded-t-lg shadow-sm' : 'border-transparent text-neutral-600 hover:text-neutral-900 hover:border-neutral-300' }}">
                        <iconify-icon icon="solar:chart-square-bold" class="text-lg"></iconify-icon>
                        Báo cáo Tiêu thụ theo Tháng (Ma trận)
                        <span class="bg-primary-100 text-primary-700 text-xs px-2 py-1 rounded-full font-semibold">Chuẩn KT</span>
                    </a>
                    <a href="{{ route('warehouses.show', [$warehouse, 'tab' => 'ledger']) }}" 
                       class="flex items-center gap-2 px-5 py-3 text-sm font-bold border-b-2 transition-all {{ $currentTab === 'ledger' ? 'border-primary-600 text-primary-600 bg-white rounded-t-lg shadow-sm' : 'border-transparent text-neutral-600 hover:text-neutral-900 hover:border-neutral-300' }}">
                        <iconify-icon icon="solar:document-text-outline" class="text-lg"></iconify-icon>
                        Sổ chi tiết phiếu kho ({{ $records->count() }})
                    </a>
                </div>

                {{-- Tab quick buttons --}}
                <div class="flex items-center gap-2 pb-2">
                    @if($currentTab === 'matrix')
                        <button type="button" onclick="openModal('importMatrixModal')" class="bg-neutral-100 text-neutral-700 hover:bg-neutral-200 text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-2 transition-all">
                            <iconify-icon icon="solar:cloud-upload-outline" class="text-base text-primary-600"></iconify-icon> Nhập Excel Ma trận
                        </button>
                        <a href="{{ route('warehouses.export-matrix', [$warehouse, 'year' => $selectedYear]) }}" class="bg-success-50 text-success-700 hover:bg-success-100 border border-success-200 text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-2 transition-all">
                            <iconify-icon icon="solar:file-excel-outline" class="text-base text-success-600"></iconify-icon> Xuất Excel Năm {{ $selectedYear }}
                        </a>
                    @else
                        <button class="bg-neutral-100 text-neutral-700 hover:bg-neutral-200 text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-2 transition-all" type="button" onclick="openModal('importWarehouseModal')">
                            <iconify-icon icon="solar:import-outline" class="text-base"></iconify-icon> Nhập Excel Phiếu
                        </button>
                        <div class="relative inline-block text-left" x-data="{ open: false }">
                            <button @click="open = !open" @click.away="open = false" class="bg-neutral-100 text-neutral-700 hover:bg-neutral-200 text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-2 transition-all" type="button">
                                <iconify-icon icon="solar:download-outline" class="text-base"></iconify-icon> Xuất dữ liệu
                            </button>
                            <div x-show="open" class="origin-top-right absolute right-0 mt-2 w-48 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20" style="display: none;">
                                <div class="py-1">
                                    <a class="flex items-center gap-2 px-4 py-2 text-xs text-neutral-700 hover:bg-neutral-100" href="{{ route('warehouses.export-template', $warehouse) }}">
                                        <iconify-icon icon="solar:file-text-outline" class="text-base text-info-500"></iconify-icon> Tải file mẫu
                                    </a>
                                    <hr class="border-neutral-100 my-1">
                                    <a class="flex items-center gap-2 px-4 py-2 text-xs text-neutral-700 hover:bg-neutral-100" href="{{ route('warehouses.export-data', $warehouse) }}">
                                        <iconify-icon icon="solar:file-excel-outline" class="text-base text-success-500"></iconify-icon> Export Excel
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- TAB 1: BÁO CÁO TIÊU THỤ & TỒN KHO THEO THÁNG (MATRIX) --}}
            @if($currentTab === 'matrix')
                {{-- Year Filter Pills & Search --}}
                <div class="bg-white rounded-xl border border-neutral-200 shadow-sm p-4 mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-neutral-600 uppercase tracking-wide flex items-center gap-1">
                            <iconify-icon icon="solar:calendar-bold" class="text-base text-primary-600"></iconify-icon> Năm báo cáo:
                        </span>
                        @if(empty($availableYears))
                            <span class="px-3 py-1 bg-primary-600 text-white text-xs font-bold rounded-lg shadow-sm">{{ $selectedYear }}</span>
                        @else
                            @foreach($availableYears as $yr)
                                <a href="{{ route('warehouses.show', [$warehouse, 'tab' => 'matrix', 'year' => $yr]) }}"
                                   class="px-4 py-2 rounded-lg text-xs font-bold transition-all {{ (int)$selectedYear === (int)$yr ? 'bg-primary-600 text-white shadow-sm' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200' }}">
                                    Năm {{ $yr }}
                                </a>
                            @endforeach
                        @endif
                    </div>

                    {{-- Search in matrix --}}
                    <div class="relative">
                        <iconify-icon icon="ion:search-outline" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
                        <input type="text" id="matrixSearch" class="form-control form-control-sm rounded-lg pl-9 pr-4 py-2 border-neutral-200 text-xs w-72 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" 
                            placeholder="Lọc mã Gervin, xuất xứ, nhóm màu..." onkeyup="filterMatrixRows()">
                    </div>
                </div>

                {{-- KPI Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    {{-- Total Items --}}
                    <div class="card p-4 rounded-xl border border-neutral-200 bg-white shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center shrink-0">
                            <iconify-icon icon="solar:box-bold" class="text-2xl"></iconify-icon>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-neutral-500 uppercase">Tổng mã mặt hàng</div>
                            <div class="text-2xl font-black text-neutral-800">{{ $matrixData['kpis']['total_items'] }} <span class="text-xs font-normal text-neutral-500">mã màu</span></div>
                        </div>
                    </div>

                    {{-- Total In --}}
                    <div class="card p-4 rounded-xl border border-neutral-200 bg-white shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <iconify-icon icon="solar:import-bold" class="text-2xl"></iconify-icon>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-emerald-600 uppercase">Tổng Nhận (Năm {{ $selectedYear }})</div>
                            <div class="text-2xl font-black text-emerald-700">{{ number_format($matrixData['kpis']['total_in'], 0, ',', '.') }} <span class="text-xs font-normal text-neutral-500">tấm</span></div>
                        </div>
                    </div>

                    {{-- Total Out --}}
                    <div class="card p-4 rounded-xl border border-neutral-200 bg-white shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                            <iconify-icon icon="solar:export-bold" class="text-2xl"></iconify-icon>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-rose-600 uppercase">Tổng Tiêu thụ (Năm {{ $selectedYear }})</div>
                            <div class="text-2xl font-black text-rose-700">{{ number_format($matrixData['kpis']['total_out'], 0, ',', '.') }} <span class="text-xs font-normal text-neutral-500">tấm</span></div>
                        </div>
                    </div>

                    {{-- Final Stock --}}
                    <div class="card p-4 rounded-xl border border-neutral-200 bg-white shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <iconify-icon icon="solar:warehouse-bold" class="text-2xl"></iconify-icon>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-amber-700 uppercase">Tồn kho hiện tại</div>
                            <div class="text-2xl font-black text-amber-900 flex items-center gap-2">
                                {{ number_format($matrixData['kpis']['final_stock'], 2, ',', '.') }}
                                <span class="text-xs font-normal text-neutral-500">tấm</span>
                            </div>
                            @if($matrixData['kpis']['low_stock_count'] > 0)
                                <div class="text-[10px] text-danger-600 font-semibold mt-1">
                                    <iconify-icon icon="solar:danger-triangle-bold" class="inline-block align-middle"></iconify-icon>
                                    {{ $matrixData['kpis']['low_stock_count'] }} mã dưới mức an toàn (&le;150 tấm)
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- The Monthly Matrix Table Card --}}
                <div class="card p-0 rounded-xl border-0 bg-white shadow-sm overflow-hidden mb-6">
                    <div class="border-b border-neutral-200 py-4 px-6 flex flex-wrap items-center justify-between gap-4 bg-neutral-50">
                        <div>
                            <h6 class="font-bold text-neutral-800 text-base mb-1">
                                Bảng Tổng hợp Tồn kho & Tiêu thụ - Năm {{ $selectedYear }}
                            </h6>
                            <p class="text-xs text-neutral-500 mb-0">
                                Báo cáo đối soát 12 tháng theo định dạng kế toán. Số lượng hiển thị theo đơn vị tấm Acrylic.
                            </p>
                        </div>
                        <div class="flex items-center gap-4 text-xs">
                            <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> Nhận</span>
                            <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span> Tiêu thụ</span>
                            <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span> Tồn cuối kỳ</span>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="overflow-x-auto overflow-y-auto w-full max-h-[72vh] relative">
                            <table class="w-full border-collapse border border-neutral-200 text-xs text-center" style="min-width: 3200px;">
                                <thead class="sticky top-0 z-30 shadow-sm">
                                    {{-- Level 1: Main Headers --}}
                                    <tr class="bg-primary-50 text-neutral-800 font-bold border-b border-neutral-300">
                                        <th rowspan="2" class="border border-neutral-300 px-2 py-3 sticky left-0 z-40 bg-primary-50" style="width: 48px; min-width: 48px;">STT</th>
                                        <th rowspan="2" class="border border-neutral-300 px-3 py-3 sticky z-40 bg-primary-50 text-left" style="left: 48px; width: 90px; min-width: 90px;">Mã Gervin</th>
                                        <th rowspan="2" class="border border-neutral-300 px-2 py-3 sticky z-40 bg-primary-50 text-center" style="left: 138px; width: 80px; min-width: 80px;">Mã xuất xứ</th>
                                        <th rowspan="2" class="border border-neutral-300 px-3 py-3 sticky z-40 bg-primary-50 text-left" style="left: 218px; width: 160px; min-width: 160px;">Nhóm màu</th>
                                        <th rowspan="2" class="border border-neutral-300 px-3 py-3 bg-primary-100 text-primary-900" style="width: 95px; min-width: 95px;">Tồn đầu năm</th>

                                        {{-- 12 Months --}}
                                        @for($m = 1; $m <= 12; $m++)
                                            <th colspan="3" class="border border-neutral-300 py-2 px-1 text-center font-bold text-neutral-800 bg-neutral-100">
                                                THÁNG {{ sprintf('%02d', $m) }}.{{ $selectedYear }}
                                            </th>
                                        @endfor

                                        {{-- Full Year --}}
                                        <th colspan="3" class="border border-neutral-300 py-2 px-1 text-center font-bold text-neutral-900 bg-primary-100">
                                            CẢ NĂM {{ $selectedYear }}
                                        </th>
                                    </tr>

                                    {{-- Level 2: Sub-headers for Nhận / Tiêu thụ / Tồn --}}
                                    <tr class="border-b border-neutral-300 text-[10px] font-bold">
                                        {{-- 12 Months subheaders --}}
                                        @for($m = 1; $m <= 12; $m++)
                                            <th class="border border-neutral-200 py-1 px-1 bg-emerald-50 text-emerald-800" style="width: 70px; min-width: 70px;">NHẬN</th>
                                            <th class="border border-neutral-200 py-1 px-1 bg-rose-50 text-rose-800" style="width: 70px; min-width: 70px;">TIÊU THỤ</th>
                                            <th class="border border-neutral-200 py-1 px-1 bg-amber-50 text-amber-900" style="width: 75px; min-width: 75px;">TỒN</th>
                                        @endfor

                                        {{-- Full Year subheaders --}}
                                        <th class="border border-neutral-200 py-1 px-1 bg-emerald-100 text-emerald-900 font-extrabold" style="width: 85px; min-width: 85px;">TỔNG NHẬN</th>
                                        <th class="border border-neutral-200 py-1 px-1 bg-rose-100 text-rose-900 font-extrabold" style="width: 85px; min-width: 85px;">TỔNG TIÊU THỤ</th>
                                        <th class="border border-neutral-200 py-1 px-1 bg-amber-100 text-amber-900 font-extrabold" style="width: 95px; min-width: 95px;">TỒN CUỐI NĂM</th>
                                    </tr>

                                    {{-- Summary Row: TỔNG CỘNG at TOP --}}
                                    <tr class="bg-neutral-100 font-bold border-b-2 border-primary-600 text-neutral-900 text-xs">
                                        <td colspan="4" class="border border-neutral-300 py-2 px-3 text-right font-extrabold text-primary-700 sticky left-0 z-30 bg-neutral-100">
                                            TỔNG CỘNG:
                                        </td>
                                        <td class="border border-neutral-300 py-2 px-2 bg-primary-100 text-primary-950 font-black text-right">
                                            {{ $fmtStock($matrixData['col_totals']['start_stock']) }}
                                        </td>

                                        {{-- 12 Months Totals --}}
                                        @for($m = 1; $m <= 12; $m++)
                                            <td class="border border-neutral-200 py-2 px-1 bg-emerald-100 bg-opacity-70 text-emerald-900 font-bold text-right">
                                                {{ $fmt($matrixData['col_totals']['months'][$m]['in']) }}
                                            </td>
                                            <td class="border border-neutral-200 py-2 px-1 bg-rose-100 bg-opacity-70 text-rose-900 font-bold text-right">
                                                {{ $fmt($matrixData['col_totals']['months'][$m]['out']) }}
                                            </td>
                                            <td class="border border-neutral-200 py-2 px-1 bg-amber-100 bg-opacity-70 text-amber-950 font-bold text-right">
                                                {{ $fmtStock($matrixData['col_totals']['months'][$m]['stock']) }}
                                            </td>
                                        @endfor

                                        {{-- Full Year Totals --}}
                                        <td class="border border-neutral-300 py-2 px-1 bg-emerald-200 text-emerald-950 font-black text-right">
                                            {{ $fmtStock($matrixData['col_totals']['total_in']) }}
                                        </td>
                                        <td class="border border-neutral-300 py-2 px-1 bg-rose-200 text-rose-950 font-black text-right">
                                            {{ $fmtStock($matrixData['col_totals']['total_out']) }}
                                        </td>
                                        <td class="border border-neutral-300 py-2 px-1 bg-amber-200 text-amber-950 font-black text-right">
                                            {{ $fmtStock($matrixData['col_totals']['final_stock']) }}
                                        </td>
                                    </tr>
                                </thead>

                                <tbody id="matrixTableBody" class="divide-y divide-neutral-200 bg-white">
                                    @forelse($matrixData['rows'] as $r)
                                        <tr class="hover:bg-primary-50 hover:bg-opacity-50 transition-colors matrix-row">
                                            {{-- Sticky left info --}}
                                            <td class="border border-neutral-200 py-2 px-2 text-neutral-500 font-medium sticky left-0 z-20 bg-white" style="width: 48px; min-width: 48px;">
                                                {{ $r['stt'] }}
                                            </td>
                                            <td class="border border-neutral-200 py-2 px-3 text-left font-bold text-neutral-900 sticky z-20 bg-white code-gv" style="left: 48px; width: 90px; min-width: 90px;">
                                                {{ $r['code'] }}
                                            </td>
                                            <td class="border border-neutral-200 py-2 px-2 text-center text-neutral-600 sticky z-20 bg-white code-origin" style="left: 138px; width: 80px; min-width: 80px;">
                                                {{ $r['origin_code'] ?: '-' }}
                                            </td>
                                            <td class="border border-neutral-200 py-2 px-3 text-left text-neutral-700 sticky z-20 bg-white group-name truncate" style="left: 218px; width: 160px; min-width: 160px;" title="{{ $r['group'] }}">
                                                {{ $r['group'] }}
                                            </td>

                                            {{-- Start Stock --}}
                                            <td class="border border-neutral-200 py-2 px-2 text-right font-medium text-neutral-700 bg-neutral-50">
                                                {{ $fmtStock($r['start_stock']) }}
                                            </td>

                                            {{-- 12 Months --}}
                                            @for($m = 1; $m <= 12; $m++)
                                                <td class="border border-neutral-200 py-2 px-1 text-right text-emerald-800 {{ $r['months'][$m]['in'] > 0 ? 'bg-emerald-50 bg-opacity-40 font-semibold' : '' }}">
                                                    {{ $fmt($r['months'][$m]['in']) }}
                                                </td>
                                                <td class="border border-neutral-200 py-2 px-1 text-right text-rose-800 {{ $r['months'][$m]['out'] > 0 ? 'bg-rose-50 bg-opacity-40 font-semibold' : '' }}">
                                                    {{ $fmt($r['months'][$m]['out']) }}
                                                </td>
                                                <td class="border border-neutral-200 py-2 px-1 text-right text-neutral-800 {{ $r['months'][$m]['stock'] <= 50 ? 'bg-amber-50 font-bold text-amber-900' : '' }}">
                                                    {{ $fmtStock($r['months'][$m]['stock']) }}
                                                </td>
                                            @endfor

                                            {{-- Full Year --}}
                                            <td class="border border-neutral-200 py-2 px-1 text-right font-bold text-emerald-900 bg-emerald-50">
                                                {{ $fmtStock($r['total_year_in']) }}
                                            </td>
                                            <td class="border border-neutral-200 py-2 px-1 text-right font-bold text-rose-900 bg-rose-50">
                                                {{ $fmtStock($r['total_year_out']) }}
                                            </td>
                                            <td class="border border-neutral-200 py-2 px-1 text-right font-black bg-amber-50 text-amber-950">
                                                <div class="flex items-center justify-end gap-1">
                                                    <span>{{ $fmtStock($r['final_stock']) }}</span>
                                                    @if($r['stock_status'] === 'out_of_stock')
                                                        <span class="inline-block bg-danger-100 text-danger-700 text-[9px] px-2 py-1 rounded font-bold" title="Hết hàng">Hết</span>
                                                    @elseif($r['stock_status'] === 'danger_low')
                                                        <span class="inline-block bg-warning-100 text-warning-800 text-[9px] px-2 py-1 rounded font-bold" title="Tồn kho thấp &le;50">&le;50</span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="44" class="py-12 text-neutral-500 text-center font-medium bg-neutral-50">
                                                Chưa có dữ liệu ma trận cho năm {{ $selectedYear }}. Vui lòng nhập dữ liệu từ file Excel hoặc thêm các phiếu kho trong năm.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- TAB 2: SỔ CHI TIẾT PHIẾU KHO (LEDGER) --}}
            @if($currentTab === 'ledger')
                <div class="card p-0 rounded-xl border-0 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-neutral-200 py-4 px-6 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h6 class="font-bold text-neutral-800 text-base mb-1">Sổ chi tiết phiếu nhập xuất kho</h6>
                            <p class="text-xs text-neutral-500 mb-0">Tra cứu từng phiếu xuất/nhập, in phiếu kho và điều chỉnh thông tin.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            {{-- Search Input --}}
                            <div class="relative">
                                <iconify-icon icon="ion:search-outline" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
                                <input type="text" id="warehouseSearch" class="form-control form-control-sm rounded-lg pl-9 pr-4 py-2 border-neutral-200 text-xs w-64 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" 
                                    placeholder="Tìm kiếm phiếu, nội dung..." onkeyup="filterWarehouseRecords()">
                            </div>
                        </div>
                    </div>

                    {{-- Table Area --}}
                    <div class="card-body p-0">
                        <div class="overflow-x-auto w-full max-h-[70vh]">
                            <table class="w-full border-collapse border border-neutral-200 text-xs text-center min-w-[1600px]">
                                <thead>
                                    {{-- Row 1: Headers --}}
                                    <tr class="bg-primary-50 text-neutral-700 uppercase font-bold border-b border-neutral-300">
                                        <th rowspan="3" class="border border-neutral-200 px-2 py-3 w-10">STT</th>
                                        <th rowspan="3" class="border border-neutral-200 px-2 py-3 w-20">Số phiếu</th>
                                        <th rowspan="3" class="border border-neutral-200 px-2 py-3 w-24">Ngày</th>
                                        <th rowspan="3" class="border border-neutral-200 px-3 py-3 w-48">Nội dung</th>
                                        
                                        @php $sizeCount = count($allSizes); @endphp
                                        <th colspan="{{ $sizeCount ?: 1 }}" class="border border-neutral-200 bg-success-50 text-success-700 px-2 py-2">NHẬP {{ $warehouse->item_name }}</th>
                                        <th colspan="{{ $sizeCount ?: 1 }}" class="border border-neutral-200 bg-danger-50 text-danger-700 px-2 py-2">XUẤT {{ $warehouse->item_name }}</th>
                                        <th colspan="{{ $sizeCount ?: 1 }}" class="border border-neutral-200 bg-warning-50 text-warning-700 px-2 py-2">TỒN {{ $warehouse->item_name }}</th>

                                        <th rowspan="3" class="border border-neutral-200 px-3 py-3 w-32">Người xuất/nhập</th>
                                        <th rowspan="3" class="border border-neutral-200 px-3 py-3 w-32">Người nhận</th>
                                        <th rowspan="3" class="border border-neutral-200 px-2 py-3 w-20"><iconify-icon icon="solar:menu-dots-outline" class="text-base"></iconify-icon></th>
                                    </tr>
                                    
                                    {{-- Row 2: Size Groups --}}
                                    <tr class="bg-white border-b border-neutral-200">
                                        @foreach(['in', 'out', 'stock'] as $type)
                                            @php
                                                $groupThClass = 'bg-success-50 text-success-800';
                                                if ($type === 'out') $groupThClass = 'bg-danger-50 text-danger-800';
                                                if ($type === 'stock') $groupThClass = 'bg-warning-50 text-warning-800';
                                            @endphp
                                            @foreach($warehouse->sizes_config ?? [] as $group)
                                                <th colspan="{{ count($group['sizes']) ?: 1 }}" 
                                                    rowspan="{{ count($group['sizes']) ? 1 : 2 }}"
                                                    class="border border-neutral-200 py-1 font-bold text-[10px] {{ $groupThClass }}">
                                                    <div>{{ $group['name'] }}{{ !empty($group['code']) ? ' (' . $group['code'] . ')' : '' }}</div>
                                                    @if(!empty($group['price']) && $type === 'stock')
                                                        <div class="text-[9px] text-primary-600 font-semibold mt-1">({{ number_format($group['price'], 0, ',', '.') }}đ)</div>
                                                    @endif
                                                </th>
                                            @endforeach
                                        @endforeach
                                    </tr>
                                    
                                    {{-- Row 3: Sizes --}}
                                    <tr class="border-b border-neutral-300">
                                        @foreach(['in', 'out', 'stock'] as $type)
                                             @php
                                                 $sizeThClass = 'bg-success-50 bg-opacity-60 text-success-700';
                                                 if ($type === 'out') $sizeThClass = 'bg-danger-50 bg-opacity-60 text-danger-700';
                                                 if ($type === 'stock') $sizeThClass = 'bg-warning-50 bg-opacity-60 text-warning-700';
                                             @endphp
                                            @foreach($warehouse->sizes_config ?? [] as $group)
                                                @foreach($group['sizes'] as $size)
                                                    @php
                                                        $sizePrice = !empty($size['price']) ? $size['price'] : (!empty($group['price']) ? $group['price'] : null);
                                                        $sizeName = is_array($size) ? ($size['name'] ?? '') : $size;
                                                        $sizeCode = is_array($size) ? ($size['code'] ?? '') : '';
                                                    @endphp
                                                    <th class="border border-neutral-200 py-1 font-semibold text-[9px] w-12 {{ $sizeThClass }}">
                                                        <div>{{ $sizeName }}{{ $sizeCode ? ' [' . $sizeCode . ']' : '' }}</div>
                                                        @if(!empty($sizePrice) && $type === 'stock')
                                                            <div class="text-[8px] text-primary-500 font-normal mt-1">({{ number_format($sizePrice, 0, ',', '.') }}đ)</div>
                                                        @endif
                                                    </th>
                                                @endforeach
                                            @endforeach
                                        @endforeach
                                    </tr>
                                    
                                    {{-- Summary row (TỔNG CỘNG) at the TOP --}}
                                    @if($records->count() > 0)
                                        <tr class="bg-neutral-100 font-bold border-b-2 border-primary-600 text-neutral-800">
                                            <td colspan="4" class="border border-neutral-200 py-2 px-3 text-right text-primary-700">TỔNG CỘNG:</td>
                                            
                                            {{-- Total In --}}
                                            @foreach($allSizes as $s)
                                                <td class="border border-neutral-200 bg-success-100 bg-opacity-60 text-success-800 py-2 font-bold">
                                                    {{ number_format($records->sum(fn($r) => $r->in_data[$s['key']] ?? 0)) }}
                                                </td>
                                            @endforeach

                                            {{-- Total Out --}}
                                            @foreach($allSizes as $s)
                                                <td class="border border-neutral-200 bg-danger-100 bg-opacity-60 text-danger-800 py-2 font-bold">
                                                    {{ number_format($records->sum(fn($r) => $r->out_data[$s['key']] ?? 0)) }}
                                                </td>
                                            @endforeach

                                            {{-- Current Stock (Latest record) --}}
                                            @php
                                                $latest = $records->first();
                                            @endphp
                                            @foreach($allSizes as $s)
                                                <td class="border border-neutral-200 text-warning-900 bg-warning-100 py-2 font-bold">
                                                    {{ number_format($latest->stock_data[$s['key']] ?? 0) }}
                                                </td>
                                            @endforeach

                                            <td colspan="3" class="border border-neutral-200 bg-neutral-50"></td>
                                        </tr>
                                    @endif
                                </thead>
                                <tbody id="warehouseTableBody" class="divide-y divide-neutral-100 bg-white">
                                    @forelse($records as $index => $record)
                                        <tr class="hover:bg-primary-50 transition-colors">
                                            <td class="border border-neutral-200 font-bold text-neutral-500 py-2">{{ $records->count() - $index }}</td>
                                            <td class="border border-neutral-200 font-semibold text-neutral-800">{{ $record->voucher_no }}</td>
                                            <td class="border border-neutral-200 text-neutral-600">{{ $record->date ? $record->date->format('d/m/Y') : '-' }}</td>
                                            <td class="border border-neutral-200 text-left px-3 text-neutral-700 max-w-xs truncate" title="{{ $record->content }}">{{ $record->content }}</td>
                                            
                                            {{-- In Data --}}
                                            @foreach($allSizes as $s)
                                                <td class="border border-neutral-200 bg-success-50 bg-opacity-35 text-success-800 font-semibold">
                                                    {{ $record->in_data[$s['key']] ?? '' }}
                                                </td>
                                            @endforeach
                                            
                                            {{-- Out Data --}}
                                            @foreach($allSizes as $s)
                                                <td class="border border-neutral-200 bg-danger-50 bg-opacity-35 text-danger-800 font-semibold">
                                                    {{ $record->out_data[$s['key']] ?? '' }}
                                                </td>
                                            @endforeach
                                            
                                            {{-- Stock Data --}}
                                            @foreach($allSizes as $s)
                                                <td class="border border-neutral-200 bg-warning-50 bg-opacity-35 text-warning-900 font-bold">
                                                    {{ $record->stock_data[$s['key']] ?? 0 }}
                                                </td>
                                            @endforeach

                                            <td class="border border-neutral-200 text-neutral-600">{{ $record->exporter }}</td>
                                            <td class="border border-neutral-200 text-neutral-600">{{ $record->receiver }}</td>
                                            <td class="border border-neutral-200 text-center">
                                                <div class="flex items-center gap-2 justify-center">
                                                    <button type="button" class="text-warning-600 hover:text-warning-800 text-lg transition-colors p-1" title="Chỉnh sửa"
                                                        onclick='editRecord(@json($record))'>
                                                        <iconify-icon icon="lucide:edit"></iconify-icon>
                                                    </button>
                                                    @if((!empty($record->out_data) && collect($record->out_data)->filter()->sum() > 0) || (!empty($record->in_data) && collect($record->in_data)->filter()->sum() > 0))
                                                        <a href="{{ route('warehouses.records.print', [$warehouse, $record]) }}" target="_blank" class="text-info-600 hover:text-info-800 text-lg transition-colors p-1" title="In phiếu">
                                                            <iconify-icon icon="solar:printer-outline"></iconify-icon>
                                                        </a>
                                                    @endif
                                                    <form action="{{ route('warehouses.records.destroy', [$warehouse, $record]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phiếu này? Hệ thống sẽ tự động tính lại tồn kho cho các phiếu sau.')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="text-danger-600 hover:text-danger-800 text-lg transition-colors p-1" title="Xóa">
                                                            <iconify-icon icon="fluent:delete-24-regular"></iconify-icon>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 7 + (count($allSizes) * 3) }}" class="py-12 text-neutral-500 text-center font-medium bg-neutral-50">
                                                Chưa có dữ liệu giao dịch hóa đơn.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

{{-- Modals configuration and add/edit records --}}
@include('warehouses.partials.modals')
@if($warehouse->item_name)
    @include('warehouses.partials.import_modal')
    @include('warehouses.partials.import_matrix_modal')
@endif

<script>
    function filterMatrixRows() {
        const input = document.getElementById('matrixSearch');
        if (!input) return;
        const filter = input.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
        const rows = document.querySelectorAll('#matrixTableBody tr.matrix-row');

        rows.forEach(row => {
            const codeGV = row.querySelector('.code-gv')?.textContent || '';
            const codeOrigin = row.querySelector('.code-origin')?.textContent || '';
            const groupName = row.querySelector('.group-name')?.textContent || '';
            const text = (codeGV + ' ' + codeOrigin + ' ' + groupName).toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");

            row.style.display = text.indexOf(filter) > -1 ? "" : "none";
        });
    }

    function filterWarehouseRecords() {
        const input = document.getElementById('warehouseSearch');
        if (!input) return;
        const filter = input.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        const tbody = document.getElementById('warehouseTableBody');
        if(!tbody) return;
        const rows = tbody.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            if(cells.length < 2) continue; // Skip empty rows or loaders
            
            let found = false;
            // Track columns to search: Số phiếu (1), Nội dung (3)
            const searchCols = [1, 3];
            
            for (let j of searchCols) {
                if (cells[j]) {
                    const txtValue = cells[j].textContent || cells[j].innerText;
                    const normalizedValue = txtValue.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                    if (normalizedValue.indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            
            rows[i].style.display = found ? "" : "none";
        }
    }

    function editRecord(record) {
        document.getElementById('editRecordForm').action = `/warehouses/{{ $warehouse->id }}/records/${record.id}`;

        // Fill meta data
        document.getElementById('edit_voucher_no').value = record.voucher_no || '';
        document.getElementById('edit_date').value = record.date ? record.date.split('T')[0] : '';
        document.getElementById('edit_content').value = record.content || '';
        document.getElementById('edit_exporter').value = record.exporter || '';
        document.getElementById('edit_receiver').value = record.receiver || '';

        // Set In/Out data
        const inData = record.in_data || {};
        const outData = record.out_data || {};

        // Clear values first
        const allInputs = document.querySelectorAll('#editRecordModal input[type="number"]');
        allInputs.forEach(input => {
            input.value = '';
        });

        for (const key in inData) {
            const input = document.getElementById(`edit_in_${key}`);
            if (input) input.value = inData[key] || '';
        }
        for (const key in outData) {
            const input = document.getElementById(`edit_out_${key}`);
            if (input) input.value = outData[key] || '';
        }

        // Show Modal
        openModal('editRecordModal');
    }
</script>

@endsection
