@extends('layout.layout')
@php
    $title    = 'Quản lý tồn kho';
    $subTitle = 'Báo cáo Xuất - Nhập - Tồn & Ma trận Tiêu thụ';

    $sumStartStock = 0;
    $sumInQty = 0;
    $sumOutQty = 0;
    $sumEndStock = 0;
    foreach($reportData as $row) {
        $sumStartStock += $row['start_stock'];
        $sumInQty += $row['in_qty'];
        $sumOutQty += $row['out_qty'];
        $sumEndStock += $row['end_stock'];
    }
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        @include('inventory.partials.navbar')

        {{-- Main Container Card --}}
        <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
            {{-- Header Toolbar & Tab Switcher --}}
            <div class="p-4 border-b border-neutral-200 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                <div class="flex items-center gap-2 bg-neutral-100 p-1 rounded-xl whitespace-nowrap flex-shrink-0 overflow-x-auto max-w-full">
                    <a href="{{ route('inventory.reports.index', array_filter(['tab' => 'summary', 'start_date' => $startDate, 'end_date' => $endDate, 'warehouse_id' => $warehouseId])) }}" 
                       class="px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap flex-shrink-0 {{ $tab === 'summary' ? 'bg-white text-primary-600 shadow-sm' : 'text-neutral-600 hover:text-neutral-900' }}">
                        <iconify-icon icon="solar:chart-square-bold" class="text-base"></iconify-icon>
                        <span>Báo cáo Xuất - Nhập - Tồn tổng hợp</span>
                    </a>
                    <a href="{{ route('inventory.reports.index', array_filter(['tab' => 'matrix', 'year' => $year, 'warehouse_id' => $warehouseId])) }}" 
                       class="px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap flex-shrink-0 {{ $tab === 'matrix' ? 'bg-white text-primary-600 shadow-sm' : 'text-neutral-600 hover:text-neutral-900' }}">
                        <iconify-icon icon="solar:calendar-date-bold" class="text-base"></iconify-icon>
                        <span>Ma trận Tiêu thụ 12 Tháng Kế toán</span>
                    </a>
                </div>

                {{-- Action / Export Filter --}}
                <div class="flex items-center gap-3 flex-wrap">
                    @if($tab === 'summary')
                        <div class="flex flex-wrap items-center gap-3">
                            <form action="{{ route('inventory.reports.index') }}" method="GET" class="flex items-center gap-2">
                                <input type="hidden" name="tab" value="summary">
                                @if(request('date_preset')) <input type="hidden" name="date_preset" value="{{ request('date_preset') }}"> @endif
                                @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
                                @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
                                <div class="flex items-center gap-1.5 text-xs text-neutral-600 font-medium whitespace-nowrap flex-shrink-0">
                                    <span class="whitespace-nowrap font-semibold">Kho:</span>
                                    <select name="warehouse_id" onchange="this.form.submit()" class="form-select rounded-lg px-2.5 py-2 border-neutral-200 text-xs font-semibold text-neutral-800">
                                        <option value="">-- Tất cả các kho --</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}" {{ (string)$warehouseId === (string)$wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>

                            {{-- Bộ lọc ngày tháng chung --}}
                            <x-date-filter :action="route('inventory.reports.index')" />

                            <a href="{{ route('inventory.reports.export', array_filter(['date_preset' => request('date_preset'), 'start_date' => request('start_date', $startDate), 'end_date' => request('end_date', $endDate), 'warehouse_id' => $warehouseId])) }}" class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm whitespace-nowrap flex-shrink-0">
                                <iconify-icon icon="solar:document-medicine-bold" class="text-base"></iconify-icon>
                                <span>Xuất Excel</span>
                            </a>
                        </div>
                    @else
                        <form action="{{ route('inventory.reports.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                            <input type="hidden" name="tab" value="matrix">
                            <div class="flex items-center gap-1.5 text-xs text-neutral-600 font-medium whitespace-nowrap flex-shrink-0">
                                <span class="whitespace-nowrap font-semibold">Kho:</span>
                                <select name="warehouse_id" onchange="this.form.submit()" class="form-select rounded-lg px-2.5 py-2 border-neutral-200 text-xs font-semibold text-neutral-800">
                                    <option value="">-- Tất cả các kho --</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ (string)$warehouseId === (string)$wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-neutral-600 font-medium whitespace-nowrap flex-shrink-0">
                                <span class="whitespace-nowrap font-semibold">Năm:</span>
                                <select name="year" onchange="this.form.submit()" class="form-select rounded-lg px-3 py-2 border-neutral-200 text-xs font-bold text-neutral-800">
                                    @foreach($availableYears as $y)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Năm {{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <a href="{{ route('inventory.reports.export-matrix', array_filter(['year' => $year, 'warehouse_id' => $warehouseId])) }}" class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm whitespace-nowrap flex-shrink-0">
                                <iconify-icon icon="solar:document-medicine-bold" class="text-base"></iconify-icon>
                                <span>Xuất Ma trận Excel</span>
                            </a>
                        </form>
                    @endif
                </div>
            </div>

            @if($warehouseId && ($currentWarehouse = $warehouses->firstWhere('id', $warehouseId)))
                <div class="px-6 pt-4 pb-0 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-200">
                        <iconify-icon icon="solar:box-minimalistic-bold" class="text-sm"></iconify-icon>
                        Đang lọc theo kho: {{ $currentWarehouse->name }}
                        <a href="{{ route('inventory.reports.index', array_filter(['tab' => $tab, 'start_date' => $startDate, 'end_date' => $endDate, 'year' => $year])) }}" class="ml-1 text-neutral-400 hover:text-rose-600" title="Bỏ lọc kho">
                            <iconify-icon icon="solar:close-circle-bold" class="text-sm"></iconify-icon>
                        </a>
                    </span>
                </div>
            @endif

            {{-- Tab 1 Content: Báo cáo Tổng hợp Xuất - Nhập - Tồn --}}
            @if($tab === 'summary')
                <div class="p-6">
                    {{-- 4 Summary Cards --}}
                    <div class="grid grid-cols-12 gap-4 mb-6">
                        <div class="col-span-12 sm:col-span-6 md:col-span-3">
                            <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                <span class="text-xs text-neutral-500 block mb-1">Tổng Tồn đầu kỳ</span>
                                <span class="text-xl font-black text-neutral-800 block">
                                    {{ fmod($sumStartStock, 1) == 0 ? number_format($sumStartStock, 0, ',', '.') : number_format($sumStartStock, 2, ',', '.') }}
                                </span>
                                <span class="text-[11px] text-neutral-400">Thời điểm trước {{ date('d/m/Y', strtotime($startDate)) }}</span>
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6 md:col-span-3">
                            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                                <span class="text-xs text-emerald-700 block mb-1">Tổng Nhập trong kỳ</span>
                                <span class="text-xl font-black text-emerald-800 block">
                                    +{{ fmod($sumInQty, 1) == 0 ? number_format($sumInQty, 0, ',', '.') : number_format($sumInQty, 2, ',', '.') }}
                                </span>
                                <span class="text-[11px] text-emerald-600">Từ {{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }}</span>
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6 md:col-span-3">
                            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200">
                                <span class="text-xs text-rose-700 block mb-1">Tổng Xuất trong kỳ</span>
                                <span class="text-xl font-black text-rose-800 block">
                                    -{{ fmod($sumOutQty, 1) == 0 ? number_format($sumOutQty, 0, ',', '.') : number_format($sumOutQty, 2, ',', '.') }}
                                </span>
                                <span class="text-[11px] text-rose-600">Tiêu thụ cho sản xuất</span>
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6 md:col-span-3">
                            <div class="p-4 rounded-xl bg-primary-50 border border-primary-200">
                                <span class="text-xs text-primary-700 block mb-1">Tổng Tồn cuối kỳ</span>
                                <span class="text-xl font-black text-primary-800 block">
                                    {{ fmod($sumEndStock, 1) == 0 ? number_format($sumEndStock, 0, ',', '.') : number_format($sumEndStock, 2, ',', '.') }}
                                </span>
                                <span class="text-[11px] text-primary-600">Thời điểm {{ date('d/m/Y', strtotime($endDate)) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Table Xuất Nhập Tồn --}}
                    <div class="border border-neutral-200 rounded-xl overflow-hidden">
                        <div class="overflow-x-auto w-full">
                            <table class="w-full border-collapse text-xs text-left">
                                <thead>
                                    <tr class="bg-neutral-100 text-neutral-700 uppercase font-bold border-b border-neutral-200">
                                        <th class="py-3 px-3 w-12 text-center">STT</th>
                                        <th class="py-3 px-3 w-28">Mã SKU</th>
                                        <th class="py-3 px-3 w-28">Mã gốc TQ</th>
                                        <th class="py-3 px-4 min-w-[200px]">Tên vật tư / Quy cách</th>
                                        <th class="py-3 px-3 w-20 text-center">ĐVT</th>
                                        <th class="py-3 px-3 w-32 text-right">Tồn đầu kỳ</th>
                                        <th class="py-3 px-3 w-32 text-right text-emerald-700">Nhập trong kỳ</th>
                                        <th class="py-3 px-3 w-32 text-right text-rose-700">Xuất trong kỳ</th>
                                        <th class="py-3 px-3 w-32 text-right text-primary-700">Tồn cuối kỳ</th>
                                        <th class="py-3 px-3 w-24 text-center">Thẻ kho</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100 bg-white">
                                    @forelse($reportData as $idx => $r)
                                        <tr class="hover:bg-neutral-50 transition-colors">
                                            <td class="py-3 px-3 text-center text-neutral-500 font-medium">
                                                {{ $idx + 1 }}
                                            </td>
                                            <td class="py-3 px-3 font-bold text-neutral-900">
                                                {{ $r['material']->code }}
                                            </td>
                                            <td class="py-3 px-3 font-medium text-neutral-600">
                                                {{ $r['material']->origin_code ?: '-' }}
                                            </td>
                                            <td class="py-3 px-4 text-neutral-800 font-semibold">
                                                {{ $r['material']->name }}
                                                @if($r['material']->category)
                                                    <span class="block text-[10px] text-neutral-400 font-normal">{{ $r['material']->category }}</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 text-center text-neutral-600 font-medium">
                                                {{ $r['material']->unit }}
                                            </td>
                                            <td class="py-3 px-3 text-right font-medium text-neutral-600">
                                                {{ fmod($r['start_stock'], 1) == 0 ? number_format($r['start_stock'], 0, ',', '.') : number_format($r['start_stock'], 2, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-3 text-right font-bold text-emerald-700">
                                                {{ $r['in_qty'] > 0 ? (fmod($r['in_qty'], 1) == 0 ? number_format($r['in_qty'], 0, ',', '.') : number_format($r['in_qty'], 2, ',', '.')) : '-' }}
                                            </td>
                                            <td class="py-3 px-3 text-right font-bold text-rose-700">
                                                {{ $r['out_qty'] > 0 ? (fmod($r['out_qty'], 1) == 0 ? number_format($r['out_qty'], 0, ',', '.') : number_format($r['out_qty'], 2, ',', '.')) : '-' }}
                                            </td>
                                            <td class="py-3 px-3 text-right font-black text-neutral-900 text-sm">
                                                {{ fmod($r['end_stock'], 1) == 0 ? number_format($r['end_stock'], 0, ',', '.') : number_format($r['end_stock'], 2, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <button type="button" onclick="openStockCardModal({{ $r['material']->id }})" class="p-1 text-primary-600 hover:text-primary-800 hover:bg-primary-50 rounded" title="Xem thẻ kho">
                                                    <iconify-icon icon="solar:history-bold" class="text-base"></iconify-icon>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="py-8 text-center text-neutral-400">
                                                Không có dữ liệu vật tư trong khoảng thời gian này.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="bg-neutral-100 font-black text-neutral-800 border-t-2 border-neutral-300">
                                        <td colspan="5" class="py-3 px-4 text-right uppercase">Tổng cộng:</td>
                                        <td class="py-3 px-3 text-right font-bold text-neutral-700">
                                            {{ fmod($sumStartStock, 1) == 0 ? number_format($sumStartStock, 0, ',', '.') : number_format($sumStartStock, 2, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-3 text-right font-black text-emerald-700">
                                            {{ fmod($sumInQty, 1) == 0 ? number_format($sumInQty, 0, ',', '.') : number_format($sumInQty, 2, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-3 text-right font-black text-rose-700">
                                            {{ fmod($sumOutQty, 1) == 0 ? number_format($sumOutQty, 0, ',', '.') : number_format($sumOutQty, 2, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-3 text-right font-black text-primary-700 text-sm">
                                            {{ fmod($sumEndStock, 1) == 0 ? number_format($sumEndStock, 0, ',', '.') : number_format($sumEndStock, 2, ',', '.') }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                {{-- Tab 2 Content: Ma trận Tiêu thụ 12 Tháng Kế toán (Excel Style) --}}
                <div class="p-6">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h6 class="font-black text-neutral-800 text-base mb-1">Ma trận Nhập - Xuất - Tồn 12 Tháng Năm {{ $year }}</h6>
                            <p class="text-xs text-neutral-500 mb-0">Cấu trúc chuẩn theo sổ sách theo dõi tiêu thụ định kỳ của kế toán.</p>
                        </div>
                        <div class="flex items-center gap-4 text-xs font-semibold">
                            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-100 border border-emerald-400"></span> Nhận (Nhập kho)</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-rose-100 border border-rose-400"></span> Tiêu thụ (Xuất kho)</span>
                            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-100 border border-blue-400"></span> Tồn kho cuối tháng</span>
                        </div>
                    </div>

                    <div class="border border-neutral-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="overflow-x-auto w-full max-h-[680px]">
                            <table class="w-full border-collapse text-[11px] text-left">
                                <thead class="sticky top-0 z-20">
                                    <tr class="bg-neutral-800 text-white uppercase font-bold border-b border-neutral-700">
                                        <th rowspan="2" class="py-2 px-2 w-10 text-center sticky left-0 z-30 bg-neutral-800">STT</th>
                                        <th rowspan="2" class="py-2 px-3 min-w-[90px] sticky left-10 z-30 bg-neutral-800">Mã Gervin</th>
                                        <th rowspan="2" class="py-2 px-3 min-w-[90px]">Mã TQ</th>
                                        <th rowspan="2" class="py-2 px-4 min-w-[170px]">Tên hàng / Màu</th>
                                        <th rowspan="2" class="py-2 px-3 min-w-[90px] text-right bg-neutral-700">Tồn đầu năm</th>

                                        @for($m = 1; $m <= 12; $m++)
                                            <th colspan="3" class="py-2 px-2 text-center border-l border-neutral-700 {{ $m % 2 == 0 ? 'bg-neutral-800' : 'bg-neutral-900' }}">
                                                Tháng {{ $m }}
                                            </th>
                                        @endfor

                                        <th colspan="3" class="py-2 px-3 text-center border-l border-neutral-700 bg-amber-800">CẢ NĂM {{ $year }}</th>
                                    </tr>
                                    <tr class="bg-neutral-700 text-white uppercase text-[10px] font-bold border-b border-neutral-600">
                                        @for($m = 1; $m <= 12; $m++)
                                            <th class="py-1 px-2 text-right border-l border-neutral-600 text-emerald-300">Nhận</th>
                                            <th class="py-1 px-2 text-right text-rose-300">Xuất</th>
                                            <th class="py-1 px-2 text-right text-blue-200">Tồn</th>
                                        @endfor
                                        <th class="py-1 px-2 text-right border-l border-amber-700 text-emerald-300">Tổng nhận</th>
                                        <th class="py-1 px-2 text-right text-rose-300">Tổng xuất</th>
                                        <th class="py-1 px-2 text-right text-amber-200">Tồn cuối</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100 bg-white">
                                    @foreach($matrixRows as $row)
                                        <tr class="hover:bg-neutral-50 transition-colors">
                                            <td class="py-2 px-2 text-center text-neutral-500 font-medium sticky left-0 z-10 bg-white">
                                                {{ $row['stt'] }}
                                            </td>
                                            <td class="py-2 px-3 font-bold text-neutral-900 sticky left-10 z-10 bg-white shadow-sm">
                                                {{ $row['code'] }}
                                            </td>
                                            <td class="py-2 px-3 font-medium text-neutral-600">
                                                {{ $row['origin_code'] ?: '-' }}
                                            </td>
                                            <td class="py-2 px-4 font-semibold text-neutral-800 whitespace-nowrap">
                                                {{ $row['name'] }}
                                            </td>
                                            <td class="py-2 px-3 text-right font-bold text-neutral-700 bg-neutral-50">
                                                {{ fmod($row['start_stock'], 1) == 0 ? number_format($row['start_stock'], 0, ',', '.') : number_format($row['start_stock'], 2, ',', '.') }}
                                            </td>

                                            @for($m = 1; $m <= 12; $m++)
                                                @php
                                                    $mIn = $row['months'][$m]['in'];
                                                    $mOut = $row['months'][$m]['out'];
                                                    $mStock = $row['months'][$m]['stock'];
                                                @endphp
                                                <td class="py-2 px-2 text-right border-l border-neutral-100 text-emerald-700 font-medium {{ $mIn > 0 ? 'bg-emerald-50 bg-opacity-40' : '' }}">
                                                    {{ $mIn > 0 ? (fmod($mIn, 1) == 0 ? number_format($mIn, 0, ',', '.') : number_format($mIn, 2, ',', '.')) : '-' }}
                                                </td>
                                                <td class="py-2 px-2 text-right text-rose-700 font-medium {{ $mOut > 0 ? 'bg-rose-50 bg-opacity-40' : '' }}">
                                                    {{ $mOut > 0 ? (fmod($mOut, 1) == 0 ? number_format($mOut, 0, ',', '.') : number_format($mOut, 2, ',', '.')) : '-' }}
                                                </td>
                                                <td class="py-2 px-2 text-right text-neutral-900 font-bold bg-neutral-50 bg-opacity-60">
                                                    {{ fmod($mStock, 1) == 0 ? number_format($mStock, 0, ',', '.') : number_format($mStock, 2, ',', '.') }}
                                                </td>
                                            @endfor

                                            <td class="py-2 px-2 text-right border-l border-amber-200 text-emerald-700 font-bold bg-amber-50 bg-opacity-30">
                                                {{ $row['total_year_in'] > 0 ? (fmod($row['total_year_in'], 1) == 0 ? number_format($row['total_year_in'], 0, ',', '.') : number_format($row['total_year_in'], 2, ',', '.')) : '-' }}
                                            </td>
                                            <td class="py-2 px-2 text-right text-rose-700 font-bold bg-amber-50 bg-opacity-30">
                                                {{ $row['total_year_out'] > 0 ? (fmod($row['total_year_out'], 1) == 0 ? number_format($row['total_year_out'], 0, ',', '.') : number_format($row['total_year_out'], 2, ',', '.')) : '-' }}
                                            </td>
                                            <td class="py-2 px-2 text-right text-neutral-900 font-black bg-amber-100 bg-opacity-50 text-xs">
                                                {{ fmod($row['final_stock'], 1) == 0 ? number_format($row['final_stock'], 0, ',', '.') : number_format($row['final_stock'], 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="sticky bottom-0 z-20">
                                    <tr class="bg-neutral-800 text-white font-black border-t-2 border-neutral-600 text-xs">
                                        <td colspan="4" class="py-3 px-4 text-right uppercase sticky left-0 z-30 bg-neutral-800">TỔNG CỘNG:</td>
                                        <td class="py-3 px-3 text-right bg-neutral-700 text-neutral-100">
                                            {{ fmod($colTotals['start_stock'], 1) == 0 ? number_format($colTotals['start_stock'], 0, ',', '.') : number_format($colTotals['start_stock'], 2, ',', '.') }}
                                        </td>

                                        @for($m = 1; $m <= 12; $m++)
                                            <td class="py-3 px-2 text-right border-l border-neutral-700 text-emerald-300">
                                                {{ fmod($colTotals['months'][$m]['in'], 1) == 0 ? number_format($colTotals['months'][$m]['in'], 0, ',', '.') : number_format($colTotals['months'][$m]['in'], 2, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-2 text-right text-rose-300">
                                                {{ fmod($colTotals['months'][$m]['out'], 1) == 0 ? number_format($colTotals['months'][$m]['out'], 0, ',', '.') : number_format($colTotals['months'][$m]['out'], 2, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-2 text-right text-blue-200">
                                                {{ fmod($colTotals['months'][$m]['stock'], 1) == 0 ? number_format($colTotals['months'][$m]['stock'], 0, ',', '.') : number_format($colTotals['months'][$m]['stock'], 2, ',', '.') }}
                                            </td>
                                        @endfor

                                        <td class="py-3 px-2 text-right border-l border-amber-700 text-emerald-300 bg-amber-900">
                                            {{ fmod($colTotals['total_in'], 1) == 0 ? number_format($colTotals['total_in'], 0, ',', '.') : number_format($colTotals['total_in'], 2, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-2 text-right text-rose-300 bg-amber-900">
                                            {{ fmod($colTotals['total_out'], 1) == 0 ? number_format($colTotals['total_out'], 0, ',', '.') : number_format($colTotals['total_out'], 2, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-2 text-right text-amber-200 bg-amber-950 font-black">
                                            {{ fmod($colTotals['final_stock'], 1) == 0 ? number_format($colTotals['final_stock'], 0, ',', '.') : number_format($colTotals['final_stock'], 2, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Stock Card Modal Container (if user clicks on stock card) --}}
<div id="stockCardModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl">
        <div class="p-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50">
            <h6 class="font-bold text-neutral-800 text-sm mb-0">Thẻ kho chi tiết</h6>
            <button type="button" onclick="closeStockCardModal()" class="text-neutral-400 hover:text-neutral-800 p-1 rounded-lg">
                <iconify-icon icon="solar:close-circle-bold" class="text-2xl"></iconify-icon>
            </button>
        </div>
        <div id="stockCardModalBody" class="p-6 overflow-y-auto">
            <div class="text-center py-8">
                <span class="text-neutral-400 text-sm">Đang tải thẻ kho...</span>
            </div>
        </div>
    </div>
</div>

<script>
    function openStockCardModal(materialId) {
        const modal = document.getElementById('stockCardModal');
        const body = document.getElementById('stockCardModalBody');
        modal.classList.remove('hidden');
        body.innerHTML = '<div class="text-center py-8"><span class="text-neutral-400 text-sm">Đang tải dữ liệu thẻ kho...</span></div>';

        let cardUrl = `/warehouses/materials/${materialId}/stock-card`;
        @if(!empty($warehouseId))
            cardUrl += `?warehouse_id={{ $warehouseId }}`;
        @endif
        fetch(cardUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(html => {
            body.innerHTML = html;
        })
        .catch(err => {
            body.innerHTML = '<div class="text-center py-8 text-rose-500">Lỗi khi tải thẻ kho!</div>';
        });
    }

    function closeStockCardModal() {
        document.getElementById('stockCardModal').classList.add('hidden');
    }
</script>
@endsection
