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
                    <a href="{{ route('inventory.reports.index', array_filter(['tab' => 'detail', 'start_date' => $startDate, 'end_date' => $endDate, 'warehouse_id' => $warehouseId])) }}" 
                       class="px-4 py-2 rounded-lg text-xs font-bold transition-all flex items-center gap-2 whitespace-nowrap flex-shrink-0 {{ $tab === 'detail' ? 'bg-white text-primary-600 shadow-sm' : 'text-neutral-600 hover:text-neutral-900' }}">
                        <iconify-icon icon="solar:document-text-bold" class="text-base"></iconify-icon>
                        <span>Báo cáo Chi tiết phát sinh (Sổ chi tiết)</span>
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
                            <form action="{{ route('inventory.reports.index') }}" method="GET" class="flex items-center gap-2 flex-wrap">
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
                                <div class="relative">
                                    <input type="text" name="search" value="{{ $search }}" placeholder="Tìm mã hoặc tên..." class="form-input rounded-lg pl-8 pr-3 py-2 border-neutral-200 text-xs font-medium text-neutral-800 w-44">
                                    <iconify-icon icon="solar:magnifer-linear" class="absolute left-2.5 top-2.5 text-neutral-400 text-sm"></iconify-icon>
                                </div>
                            </form>

                            {{-- Bộ lọc ngày tháng chung --}}
                            <x-date-filter :action="route('inventory.reports.index')" />

                            <a href="{{ route('inventory.reports.export', array_filter(['date_preset' => request('date_preset'), 'start_date' => request('start_date', $startDate), 'end_date' => request('end_date', $endDate), 'warehouse_id' => $warehouseId])) }}" class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm whitespace-nowrap flex-shrink-0">
                                <iconify-icon icon="solar:document-medicine-bold" class="text-base"></iconify-icon>
                                <span>Xuất Excel</span>
                            </a>
                        </div>
                    @elseif($tab === 'detail')
                        <div class="flex flex-wrap items-center gap-3">
                            <form action="{{ route('inventory.reports.index') }}" method="GET" class="flex items-center gap-2 flex-wrap">
                                <input type="hidden" name="tab" value="detail">
                                @if(request('date_preset')) <input type="hidden" name="date_preset" value="{{ request('date_preset') }}"> @endif
                                @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
                                @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
                                @if(request('material_id')) <input type="hidden" name="material_id" value="{{ request('material_id') }}"> @endif
                                <div class="flex items-center gap-1.5 text-xs text-neutral-600 font-medium whitespace-nowrap flex-shrink-0">
                                    <span class="whitespace-nowrap font-semibold">Kho:</span>
                                    <select name="warehouse_id" onchange="this.form.submit()" class="form-select rounded-lg px-2.5 py-2 border-neutral-200 text-xs font-semibold text-neutral-800">
                                        <option value="">-- Tất cả các kho --</option>
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}" {{ (string)$warehouseId === (string)$wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="relative">
                                    <input type="text" name="search" value="{{ $search }}" placeholder="Tìm số phiếu, mã..." class="form-input rounded-lg pl-8 pr-3 py-2 border-neutral-200 text-xs font-medium text-neutral-800 w-44">
                                    <iconify-icon icon="solar:magnifer-linear" class="absolute left-2.5 top-2.5 text-neutral-400 text-sm"></iconify-icon>
                                </div>
                            </form>

                            {{-- Bộ lọc ngày tháng chung --}}
                            <x-date-filter :action="route('inventory.reports.index')" />
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

            {{-- Thanh chuyển nhanh kho hàng (Kho Tấm Ván vs Kho Dán Chỉ) --}}
            <div class="px-6 py-2.5 bg-neutral-50/80 border-b border-neutral-200/80 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-xs font-bold text-neutral-500 mr-1 flex items-center gap-1">
                        <iconify-icon icon="solar:box-minimalistic-bold" class="text-neutral-400"></iconify-icon> Kho hàng:
                    </span>
                    <a href="{{ route('inventory.reports.index', array_filter(['tab' => $tab, 'date_preset' => request('date_preset'), 'start_date' => $startDate, 'end_date' => $endDate, 'year' => $year])) }}" 
                       class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all {{ empty($warehouseId) ? 'bg-neutral-800 text-white shadow-xs' : 'bg-white text-neutral-600 border border-neutral-200 hover:bg-neutral-100' }}">
                        Tất cả kho
                    </a>
                    @foreach($warehouses as $wh)
                        @php
                            $isEdge = in_array(strtoupper($wh->code), ['KHO-CHI', 'EDGE_BAND', 'CHI']);
                            $isActive = (string)$warehouseId === (string)$wh->id;
                        @endphp
                        <a href="{{ route('inventory.reports.index', array_filter(['tab' => $tab, 'warehouse_id' => $wh->id, 'date_preset' => request('date_preset'), 'start_date' => $startDate, 'end_date' => $endDate, 'year' => $year])) }}"
                           class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5 {{ $isActive ? ($isEdge ? 'bg-purple-600 text-white shadow-xs' : 'bg-primary-600 text-white shadow-xs') : ($isEdge ? 'bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100' : 'bg-white text-neutral-700 border border-neutral-200 hover:bg-neutral-100') }}">
                            <iconify-icon icon="{{ $isEdge ? 'solar:ruler-angular-bold' : 'solar:box-minimalistic-bold' }}" class="text-sm"></iconify-icon>
                            <span>{{ $wh->name }}</span>
                            @if($isEdge)
                                <span class="text-[10px] px-1 py-0.2 rounded {{ $isActive ? 'bg-purple-500 text-white' : 'bg-purple-200 text-purple-800' }} font-bold">m</span>
                            @else
                                <span class="text-[10px] px-1 py-0.2 rounded {{ $isActive ? 'bg-primary-500 text-white' : 'bg-neutral-200 text-neutral-700' }} font-bold">Tấm</span>
                            @endif
                        </a>
                    @endforeach
                </div>

                @if($isEdgeBand)
                    <span class="text-xs font-semibold text-purple-700 bg-purple-100/90 px-2.5 py-1 rounded-md border border-purple-200 flex items-center gap-1">
                        <iconify-icon icon="solar:info-circle-bold"></iconify-icon>
                        Đang xem số liệu Nhận, Tiêu thụ & Tồn theo Mét (m) Kho Dán Chỉ
                    </span>
                @endif
            </div>

            {{-- Tab 1 Content: Báo cáo Tổng hợp Xuất - Nhập - Tồn --}}
            @if($tab === 'summary')
                <div class="p-6">
                    {{-- 4 Summary Cards --}}
                    <div class="grid grid-cols-12 gap-4 mb-6">
                        <div class="col-span-12 sm:col-span-6 md:col-span-3">
                            <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                <span class="text-xs text-neutral-500 block mb-1">Tổng Tồn đầu kỳ</span>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-xl font-black text-neutral-800">
                                        {{ fmod($sumStartStock, 1) == 0 ? number_format($sumStartStock, 0, ',', '.') : number_format($sumStartStock, 2, ',', '.') }}
                                    </span>
                                    <span class="text-xs font-bold text-neutral-500">{{ $isEdgeBand ? 'm' : ($warehouseId ? 'Tấm' : '') }}</span>
                                </div>
                                <span class="text-[11px] text-neutral-400">Thời điểm trước {{ date('d/m/Y', strtotime($startDate)) }}</span>
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6 md:col-span-3">
                            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                                <span class="text-xs text-emerald-700 block mb-1">Tổng Nhận trong kỳ (Nhập)</span>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-xl font-black text-emerald-800">
                                        +{{ fmod($sumInQty, 1) == 0 ? number_format($sumInQty, 0, ',', '.') : number_format($sumInQty, 2, ',', '.') }}
                                    </span>
                                    <span class="text-xs font-bold text-emerald-600">{{ $isEdgeBand ? 'm' : ($warehouseId ? 'Tấm' : '') }}</span>
                                </div>
                                <span class="text-[11px] text-emerald-600">Từ {{ date('d/m/Y', strtotime($startDate)) }} - {{ date('d/m/Y', strtotime($endDate)) }}</span>
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6 md:col-span-3">
                            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200">
                                <span class="text-xs text-rose-700 block mb-1">Tổng Tiêu thụ trong kỳ (Xuất)</span>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-xl font-black text-rose-800">
                                        -{{ fmod($sumOutQty, 1) == 0 ? number_format($sumOutQty, 0, ',', '.') : number_format($sumOutQty, 2, ',', '.') }}
                                    </span>
                                    <span class="text-xs font-bold text-rose-600">{{ $isEdgeBand ? 'm' : ($warehouseId ? 'Tấm' : '') }}</span>
                                </div>
                                <span class="text-[11px] text-rose-600">Tiêu thụ cho sản xuất</span>
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-6 md:col-span-3">
                            <div class="p-4 rounded-xl bg-primary-50 border border-primary-200">
                                <span class="text-xs text-primary-700 block mb-1">Tổng Tồn cuối kỳ</span>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-xl font-black text-primary-800">
                                        {{ fmod($sumEndStock, 1) == 0 ? number_format($sumEndStock, 0, ',', '.') : number_format($sumEndStock, 2, ',', '.') }}
                                    </span>
                                    <span class="text-xs font-bold text-primary-600">{{ $isEdgeBand ? 'm' : ($warehouseId ? 'Tấm' : '') }}</span>
                                </div>
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
                                        <th class="py-3 px-3 w-32 text-right text-emerald-700">Nhận trong kỳ</th>
                                        <th class="py-3 px-3 w-32 text-right text-rose-700">Tiêu thụ trong kỳ</th>
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
                                                {{ $r['sku'] ?? $r['material']->code }}
                                            </td>
                                            <td class="py-3 px-3 font-medium text-neutral-600">
                                                {{ $r['material']->origin_code ?: '-' }}
                                            </td>
                                            <td class="py-3 px-4 text-neutral-800 font-semibold">
                                                {{ $r['name'] ?? $r['material']->name }}
                                                @if($r['material']->category)
                                                    <span class="block text-[10px] text-neutral-400 font-normal">{{ $r['material']->category }}</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 text-center text-neutral-600 font-medium">
                                                <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ ($r['unit'] ?? $r['material']->unit) === 'm' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-neutral-100 text-neutral-700' }}">
                                                    {{ $r['unit'] ?? $r['material']->unit }}
                                                </span>
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
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <button type="button" onclick="openStockCardModal({{ $r['material']->id }})" class="p-1 text-primary-600 hover:text-primary-800 hover:bg-primary-50 rounded" title="Xem nhanh thẻ kho">
                                                        <iconify-icon icon="solar:history-bold" class="text-base"></iconify-icon>
                                                    </button>
                                                    <a href="{{ route('inventory.reports.index', array_filter(['tab' => 'detail', 'material_id' => $r['material']->id, 'warehouse_id' => $warehouseId, 'start_date' => $startDate, 'end_date' => $endDate, 'date_preset' => request('date_preset')])) }}" class="p-1 text-neutral-500 hover:text-primary-700 hover:bg-neutral-100 rounded text-xs font-semibold" title="Xem sổ chi tiết phát sinh (Báo cáo chi tiết)">
                                                        <iconify-icon icon="solar:document-text-linear" class="text-base"></iconify-icon>
                                                    </a>
                                                </div>
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
            @elseif($tab === 'detail')
                {{-- Tab 2 Content: Báo cáo Chi tiết phát sinh (Sổ chi tiết / Thẻ kho) --}}
                <div class="p-6">
                    @if($selectedDetailMaterial)
                        <div class="mb-4 bg-primary-50/70 border border-primary-200 rounded-xl p-4 flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-primary-600 text-white flex items-center justify-center font-bold">
                                    <iconify-icon icon="solar:box-minimalistic-bold" class="text-xl"></iconify-icon>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-black text-neutral-900">{{ $selectedDetailMaterial->code }}</span>
                                        <span class="text-xs text-neutral-500 font-medium">({{ $selectedDetailMaterial->origin ?: 'Không có mã NCC' }})</span>
                                        <span class="bg-primary-100 text-primary-800 text-[10px] px-2 py-0.5 rounded font-bold">{{ $selectedDetailMaterial->category }}</span>
                                    </div>
                                    <div class="text-xs text-neutral-600 font-medium">{{ $selectedDetailMaterial->name }} - Kho: {{ $selectedDetailMaterial->warehouse?->name ?? 'Tất cả' }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <span class="text-[10px] uppercase font-bold text-neutral-400 block">Tồn hiện tại</span>
                                    <span class="text-lg font-black text-primary-700">
                                        {{ fmod($selectedDetailMaterial->current_stock, 1) == 0 ? number_format($selectedDetailMaterial->current_stock, 0, ',', '.') : number_format($selectedDetailMaterial->current_stock, 2, ',', '.') }}
                                        <span class="text-xs font-normal text-neutral-500">{{ $selectedDetailMaterial->unit }}</span>
                                    </span>
                                </div>
                                <a href="{{ route('inventory.reports.index', array_filter(['tab' => 'detail', 'warehouse_id' => $warehouseId, 'start_date' => $startDate, 'end_date' => $endDate, 'date_preset' => request('date_preset')])) }}" class="btn btn-sm bg-white border border-neutral-300 hover:bg-neutral-100 text-neutral-700 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1">
                                    <iconify-icon icon="solar:list-cross-linear" class="text-sm"></iconify-icon> Xem tất cả vật tư
                                </a>
                                <a href="{{ route('inventory.reports.index', array_filter(['tab' => 'summary', 'warehouse_id' => $warehouseId, 'start_date' => $startDate, 'end_date' => $endDate, 'date_preset' => request('date_preset')])) }}" class="btn btn-sm btn-primary px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1">
                                    <iconify-icon icon="solar:arrow-left-linear" class="text-sm"></iconify-icon> Về Báo cáo tổng hợp
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <h6 class="font-black text-neutral-800 text-base mb-1">Sổ Chi tiết Giao dịch Phát sinh Kho</h6>
                                <p class="text-xs text-neutral-500 mb-0">Theo dõi toàn bộ lịch sử các phiếu Nhập (PNK), Xuất (PXK) và Cân bằng kiểm kê theo trình tự thời gian.</p>
                            </div>
                            <a href="{{ route('inventory.reports.index', array_filter(['tab' => 'summary', 'warehouse_id' => $warehouseId, 'start_date' => $startDate, 'end_date' => $endDate, 'date_preset' => request('date_preset')])) }}" class="btn btn-sm bg-neutral-100 hover:bg-neutral-200 text-neutral-800 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1">
                                <iconify-icon icon="solar:chart-square-bold" class="text-sm"></iconify-icon> Xem Báo cáo tổng hợp
                            </a>
                        </div>
                    @endif

                    <div class="border border-neutral-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="overflow-x-auto w-full">
                            <table class="w-full border-collapse text-xs text-left">
                                <thead>
                                    <tr class="bg-neutral-100 text-neutral-700 uppercase font-bold border-b border-neutral-200">
                                        <th class="py-3 px-3 w-24">Ngày ghi sổ</th>
                                        <th class="py-3 px-3 w-32">Số chứng từ</th>
                                        <th class="py-3 px-3 w-28">Loại phát sinh</th>
                                        <th class="py-3 px-3 min-w-[140px]">Mã vật tư</th>
                                        <th class="py-3 px-4 min-w-[200px]">Diễn giải</th>
                                        <th class="py-3 px-3 w-28 text-center">Kho</th>
                                        <th class="py-3 px-3 w-24 text-right text-emerald-800 bg-emerald-50/50">Nhập</th>
                                        <th class="py-3 px-3 w-24 text-right text-rose-800 bg-rose-50/50">Xuất</th>
                                        <th class="py-3 px-3 w-28 text-right text-primary-900 bg-primary-50/50">Tồn sau GD</th>
                                        <th class="py-3 px-3 w-28 text-neutral-600">Người lập</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-100 bg-white">
                                    @forelse($detailTransactions as $t)
                                        <tr class="hover:bg-neutral-50 transition-colors">
                                            <td class="py-2.5 px-3 text-neutral-600 font-medium">{{ $t->date ? $t->date->format('d/m/Y') : '-' }}</td>
                                            <td class="py-2.5 px-3 font-bold">
                                                @if($t->edit_url)
                                                    <a href="{{ $t->edit_url }}" target="_blank" class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-800 hover:underline group" title="Mở chứng từ {{ $t->voucher_code }}">
                                                        <span>{{ $t->voucher_code }}</span>
                                                        <iconify-icon icon="solar:arrow-right-up-linear" class="text-xs text-neutral-400 group-hover:text-primary-600 transition-colors"></iconify-icon>
                                                    </a>
                                                @else
                                                    <span class="text-neutral-800">{{ $t->voucher_code }}</span>
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-3">
                                                @if($t->type === 'receipt')
                                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] px-2 py-0.5 rounded font-bold">Nhập kho</span>
                                                @elseif($t->type === 'issue')
                                                    <span class="bg-rose-100 text-rose-800 text-[10px] px-2 py-0.5 rounded font-bold">Xuất kho</span>
                                                @elseif($t->type === 'stocktake')
                                                    <span class="bg-amber-100 text-amber-900 text-[10px] px-2 py-0.5 rounded font-bold">Cân bằng KK</span>
                                                @else
                                                    <span class="bg-neutral-100 text-neutral-700 text-[10px] px-2 py-0.5 rounded font-bold">Đầu kỳ</span>
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-3">
                                                <span class="font-bold text-neutral-900 block">{{ $t->material?->code ?? $t->item_sku }}</span>
                                                <span class="text-[10px] text-neutral-400">{{ $t->material?->name ?? $t->item_name }}</span>
                                            </td>
                                            <td class="py-2.5 px-4 text-neutral-700">{{ $t->notes ?: '-' }}</td>
                                            <td class="py-2.5 px-3 text-center text-neutral-600 font-medium">{{ $t->warehouse?->name ?: '-' }}</td>
                                            <td class="py-2.5 px-3 text-right font-bold text-emerald-700 bg-emerald-50/20">
                                                {{ $t->in_qty > 0 ? (fmod($t->in_qty, 1) == 0 ? number_format($t->in_qty, 0, ',', '.') : number_format($t->in_qty, 2, ',', '.')) : '-' }}
                                            </td>
                                            <td class="py-2.5 px-3 text-right font-bold text-rose-700 bg-rose-50/20">
                                                {{ $t->out_qty > 0 ? (fmod($t->out_qty, 1) == 0 ? number_format($t->out_qty, 0, ',', '.') : number_format($t->out_qty, 2, ',', '.')) : '-' }}
                                            </td>
                                            <td class="py-2.5 px-3 text-right font-black text-neutral-900 bg-primary-50/20">
                                                {{ fmod($t->stock_after, 1) == 0 ? number_format($t->stock_after, 0, ',', '.') : number_format($t->stock_after, 2, ',', '.') }}
                                            </td>
                                            <td class="py-2.5 px-3 text-neutral-500 text-[11px]">{{ $t->creator?->name ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="py-8 text-center text-neutral-400 font-medium">
                                                Không có phát sinh giao dịch nào trong khoảng thời gian này.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($detailTransactions->hasPages())
                            <div class="p-3 border-t border-neutral-200 bg-neutral-50 flex justify-end">
                                {{ $detailTransactions->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            @else
                {{-- Tab 3 Content: Ma trận Tiêu thụ 12 Tháng Kế toán (Excel Style) --}}
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
