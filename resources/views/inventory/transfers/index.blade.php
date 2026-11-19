@extends('layout.layout')
@php
    $title    = 'Quản lý tồn kho';
    $subTitle = 'Phiếu Chuyển kho';
    $fromWarehouseId = $fromWarehouseId ?? request('from_warehouse_id');
    $toWarehouseId = $toWarehouseId ?? request('to_warehouse_id');
    $search = $search ?? request('search');
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        @include('inventory.partials.navbar')

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-success-100 border border-success-300 text-success-700 rounded-lg px-4 py-3 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="solar:check-circle-bold" class="text-xl text-success-600"></iconify-icon>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-success-700 hover:text-success-900 font-bold">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-danger-100 border border-danger-300 text-danger-700 rounded-lg px-4 py-3 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="solar:danger-triangle-bold" class="text-xl text-danger-600"></iconify-icon>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-danger-700 hover:text-danger-900 font-bold">&times;</button>
            </div>
        @endif

        {{-- Main Container Card --}}
        <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
            {{-- Filter & Action Bar --}}
            <div class="p-3.5 border-b border-neutral-200 flex flex-col gap-3 bg-white">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        {{-- 1. Tìm kiếm nhanh --}}
                        <form action="{{ route('inventory.transfers.index') }}" method="GET" class="relative">
                            @if(request('date_preset')) <input type="hidden" name="date_preset" value="{{ request('date_preset') }}"> @endif
                            @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
                            @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
                            @if($fromWarehouseId) <input type="hidden" name="from_warehouse_id" value="{{ $fromWarehouseId }}"> @endif
                            @if($toWarehouseId) <input type="hidden" name="to_warehouse_id" value="{{ $toWarehouseId }}"> @endif

                            <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-sm"></iconify-icon>
                            <input type="text" name="search" value="{{ $search }}"
                                   class="form-control rounded-lg pl-9 pr-7 py-1.5 border-neutral-300 text-xs w-56 md:w-64 focus:border-primary-500 shadow-2xs"
                                   placeholder="Tìm số phiếu, người giao, ghi chú...">
                            @if($search)
                                <a href="{{ route('inventory.transfers.index', request()->except(['search', 'page'])) }}"
                                   class="absolute right-2.5 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 text-sm font-bold" title="Xóa tìm kiếm">&times;</a>
                            @endif
                        </form>

                        {{-- 2. Chọn Kho xuất --}}
                        <form action="{{ route('inventory.transfers.index') }}" method="GET" class="inline-flex items-center">
                            @if(request('date_preset')) <input type="hidden" name="date_preset" value="{{ request('date_preset') }}"> @endif
                            @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
                            @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
                            @if($toWarehouseId) <input type="hidden" name="to_warehouse_id" value="{{ $toWarehouseId }}"> @endif
                            @if($search) <input type="hidden" name="search" value="{{ $search }}"> @endif

                            <div class="relative">
                                <select name="from_warehouse_id" onchange="this.form.submit()"
                                        class="form-select form-select-sm text-xs rounded-lg border-neutral-300 bg-white pl-8 pr-7 py-1.5 font-semibold text-neutral-700 shadow-2xs focus:border-purple-500">
                                    <option value="">-- Kho xuất: Tất cả --</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ (string)$fromWarehouseId === (string)$wh->id ? 'selected' : '' }}>
                                            Xuất từ: {{ $wh->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <iconify-icon icon="solar:export-bold" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-rose-500 text-sm pointer-events-none"></iconify-icon>
                            </div>
                        </form>

                        {{-- 3. Chọn Kho nhận --}}
                        <form action="{{ route('inventory.transfers.index') }}" method="GET" class="inline-flex items-center">
                            @if(request('date_preset')) <input type="hidden" name="date_preset" value="{{ request('date_preset') }}"> @endif
                            @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
                            @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
                            @if($fromWarehouseId) <input type="hidden" name="from_warehouse_id" value="{{ $fromWarehouseId }}"> @endif
                            @if($search) <input type="hidden" name="search" value="{{ $search }}"> @endif

                            <div class="relative">
                                <select name="to_warehouse_id" onchange="this.form.submit()"
                                        class="form-select form-select-sm text-xs rounded-lg border-neutral-300 bg-white pl-8 pr-7 py-1.5 font-semibold text-neutral-700 shadow-2xs focus:border-purple-500">
                                    <option value="">-- Kho nhập: Tất cả --</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ (string)$toWarehouseId === (string)$wh->id ? 'selected' : '' }}>
                                            Nhập về: {{ $wh->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <iconify-icon icon="solar:import-bold" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-emerald-500 text-sm pointer-events-none"></iconify-icon>
                            </div>
                        </form>
                    </div>

                    {{-- Action Buttons & Reset Filter --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($search || $fromWarehouseId || $toWarehouseId || request('start_date') || request('end_date') || (request('date_preset') && request('date_preset') !== 'all'))
                            <a href="{{ route('inventory.transfers.index') }}"
                               class="btn bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs px-3 py-1.5 rounded-lg font-semibold flex items-center gap-1 shadow-2xs transition-all"
                               title="Xóa tất cả bộ lọc về mặc định">
                                <iconify-icon icon="solar:restart-bold" class="text-sm"></iconify-icon>
                                <span>Đặt lại</span>
                            </a>
                        @endif

                        <x-date-filter :action="route('inventory.transfers.index')" />

                        <a href="{{ route('inventory.transfers.create') }}" class="btn bg-purple-600 hover:bg-purple-700 text-white text-xs px-3.5 py-1.5 rounded-lg font-bold flex items-center gap-1.5 shadow-xs transition-all">
                            <iconify-icon icon="solar:transfer-horizontal-bold" class="text-base"></iconify-icon>
                            <span>Tạo phiếu chuyển kho</span>
                        </a>
                    </div>
                </div>

                {{-- Tầng 2: Stats banner --}}
                <div class="flex items-center gap-4 text-xs pt-2 border-t border-neutral-100 text-neutral-600">
                    <div>
                        Tổng số phiếu: <strong class="text-neutral-900 font-bold">{{ number_format($stats['total_transfers'] ?? 0) }}</strong> phiếu
                    </div>
                    <div>&bull;</div>
                    <div>
                        Tổng số lượng điều chuyển: <strong class="text-purple-700 font-bold">{{ number_format($stats['total_quantity'] ?? 0, 1, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="p-1">
                <div class="overflow-x-auto w-full">
                    <table class="w-full border-collapse text-xs text-left" id="transfersTable">
                        <thead>
                            <tr class="bg-neutral-50 text-neutral-700 uppercase font-bold border-b border-neutral-200">
                                <th class="py-3 px-4 w-12 text-center">STT</th>
                                <th class="py-3 px-4 w-36">Mã phiếu</th>
                                <th class="py-3 px-4 w-28">Ngày chuyển</th>
                                <th class="py-3 px-4 w-48">Kho xuất (Nguồn)</th>
                                <th class="py-3 px-4 w-10 text-center"><iconify-icon icon="solar:arrow-right-bold" class="text-base text-neutral-400"></iconify-icon></th>
                                <th class="py-3 px-4 w-48">Kho nhập (Đích)</th>
                                <th class="py-3 px-4 w-24 text-center">Số mặt hàng</th>
                                <th class="py-3 px-4 w-32 text-right">Tổng số lượng</th>
                                <th class="py-3 px-4 w-36">Người lập / giao</th>
                                <th class="py-3 px-4">Ghi chú</th>
                                <th class="py-3 px-4 w-28 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 bg-white">
                            @forelse($transfers as $index => $t)
                                <tr class="hover:bg-purple-50/40 transition-colors">
                                    <td class="py-3 px-4 text-center text-neutral-500 font-medium">
                                        {{ $transfers->firstItem() + $index }}
                                    </td>
                                    <td class="py-3 px-4 font-bold text-purple-700">
                                        <a href="{{ route('inventory.transfers.show', $t) }}" class="hover:underline flex items-center gap-1">
                                            <iconify-icon icon="solar:transfer-horizontal-bold" class="text-sm"></iconify-icon>
                                            {{ $t->code }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-4 text-neutral-700 whitespace-nowrap">
                                        {{ $t->date ? $t->date->format('d/m/Y') : '—' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <iconify-icon icon="solar:export-bold" class="text-xs"></iconify-icon>
                                            {{ $t->fromWarehouse->name ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <iconify-icon icon="solar:arrow-right-linear" class="text-neutral-400 text-sm"></iconify-icon>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <iconify-icon icon="solar:import-bold" class="text-xs"></iconify-icon>
                                            {{ $t->toWarehouse->name ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center font-semibold text-neutral-700">
                                        {{ $t->items_count }} loại
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-neutral-900">
                                        {{ floatval($t->total_quantity) == intval($t->total_quantity) ? number_format($t->total_quantity, 0, ',', '.') : number_format($t->total_quantity, 2, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-neutral-700">
                                        {{ $t->deliverer ?: ($t->creator->name ?? '—') }}
                                    </td>
                                    <td class="py-3 px-4 text-neutral-600 truncate max-w-xs" title="{{ $t->notes }}">
                                        {{ $t->notes ?: '—' }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('inventory.transfers.show', $t) }}"
                                               class="p-1.5 text-neutral-500 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                                               title="Xem chi tiết phiếu chuyển">
                                                <iconify-icon icon="solar:eye-bold" class="text-base"></iconify-icon>
                                            </a>
                                            <a href="{{ route('inventory.transfers.print', $t) }}" target="_blank"
                                               class="p-1.5 text-neutral-500 hover:text-info-600 hover:bg-info-50 rounded-lg transition-colors"
                                               title="In phiếu A4">
                                                <iconify-icon icon="solar:printer-outline" class="text-base"></iconify-icon>
                                            </a>
                                            <form action="{{ route('inventory.transfers.destroy', $t) }}" method="POST"
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa phiếu chuyển kho {{ $t->code }}? Số lượng hàng hóa sẽ được hoàn trả lại tồn kho ban đầu!')"
                                                  class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-neutral-400 hover:text-danger-600 hover:bg-danger-50 rounded-lg transition-colors" title="Xóa phiếu chuyển kho">
                                                    <iconify-icon icon="solar:trash-bin-trash-bold" class="text-base"></iconify-icon>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="py-12 text-center text-neutral-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <iconify-icon icon="solar:transfer-horizontal-outline" class="text-4xl text-neutral-300"></iconify-icon>
                                            <span class="text-sm font-semibold text-neutral-500">Chưa có phiếu chuyển kho nào.</span>
                                            <a href="{{ route('inventory.transfers.create') }}" class="btn btn-sm bg-purple-600 hover:bg-purple-700 text-white rounded-lg px-3 py-1.5 text-xs font-bold mt-1">
                                                Tạo phiếu chuyển kho đầu tiên
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($transfers->hasPages())
                    <div class="p-4 border-t border-neutral-100 flex items-center justify-between">
                        <div class="text-xs text-neutral-500">
                            Hiển thị {{ $transfers->firstItem() }} đến {{ $transfers->lastItem() }} trên tổng số {{ $transfers->total() }} phiếu
                        </div>
                        <div>
                            {{ $transfers->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
