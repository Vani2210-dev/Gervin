@extends('layout.layout')
@php
    $title    = 'Quản lý tồn kho';
    $subTitle = 'Hàng hóa & Vật tư';
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        {{-- Sub-Navigation Navbar --}}
        @include('inventory.partials.navbar')

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-success-100 border border-success-300 text-success-700 rounded-lg px-4 py-3 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="solar:check-circle-bold" class="text-lg"></iconify-icon>
                    <span>{{ session('success') }}</span>
                </div>
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

        {{-- Filter & Action Bar --}}
        <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
            <div class="p-3.5 border-b border-neutral-200 flex flex-col gap-3">
                {{-- Tầng 1: Tìm kiếm, Kho, Trạng thái & Thao tác chính --}}
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        {{-- Quick Search --}}
                        <form action="{{ route('inventory.index') }}" method="GET" class="relative">
                            @if(request('warehouse_id')) <input type="hidden" name="warehouse_id" value="{{ request('warehouse_id') }}"> @endif
                            @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                            @if(request('stock_filter')) <input type="hidden" name="stock_filter" value="{{ request('stock_filter') }}"> @endif
                            @if(request('date_mode')) <input type="hidden" name="date_mode" value="{{ request('date_mode') }}"> @endif
                            @if(request('date_val')) <input type="hidden" name="date_val" value="{{ request('date_val') }}"> @endif

                            <iconify-icon icon="ion:search-outline" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
                            <input type="text" name="search" value="{{ $search }}" class="form-control rounded-lg pl-9 pr-3 py-1.5 border-neutral-300 text-xs w-52 md:w-60 focus:border-primary-500 shadow-2xs" 
                                placeholder="Tìm mã SKU, tên, xuất xứ...">
                        </form>

                        {{-- Warehouse Pills (Tối đa 3 kho trực tiếp + Dropdown các kho còn lại) --}}
                        @php
                            $selectedWhModel = $selectedWarehouseId ? $warehouses->firstWhere('id', $selectedWarehouseId) : null;
                            $maxDirectWh = 3;
                            if ($warehouses->count() <= $maxDirectWh) {
                                $visibleWarehouses = $warehouses;
                                $otherWarehouses = collect();
                            } else {
                                $top3 = $warehouses->take($maxDirectWh);
                                $isInTop3 = $selectedWhModel && $top3->contains('id', $selectedWhModel->id);
                                if ($isInTop3 || !$selectedWhModel) {
                                    $visibleWarehouses = $top3;
                                    $otherWarehouses = $warehouses->slice($maxDirectWh);
                                } else {
                                    $visibleWarehouses = $warehouses->take(2)->push($selectedWhModel);
                                    $otherWarehouses = $warehouses->reject(fn($w) => $visibleWarehouses->contains('id', $w->id));
                                }
                            }
                        @endphp
                        <div class="bg-neutral-100 p-0.5 rounded-lg flex items-center gap-0.5 text-xs font-semibold">
                            <a href="{{ route('inventory.index', request()->except(['page', 'warehouse_id'])) }}"
                               class="px-2.5 py-1 rounded-md transition-all {{ empty($selectedWarehouseId) ? 'bg-white text-neutral-900 shadow-2xs font-bold' : 'text-neutral-500 hover:text-neutral-900' }}">
                                Tất cả kho
                            </a>
                            @foreach($visibleWarehouses as $wh)
                                <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['warehouse_id' => $wh->id])) }}"
                                   class="px-2.5 py-1 rounded-md transition-all {{ (string)$selectedWarehouseId === (string)$wh->id ? ($wh->code === 'KHO-CHI' ? 'bg-amber-600 text-white shadow-2xs font-bold' : 'bg-primary-600 text-white shadow-2xs font-bold') : 'text-neutral-600 hover:text-neutral-900' }}">
                                    {{ $wh->name }}
                                </a>
                            @endforeach

                            @if($otherWarehouses->count() > 0)
                                <div class="relative inline-block" id="other_warehouses_dropdown_wrapper">
                                    <button type="button" 
                                            onclick="toggleOtherWhDropdown(event)" 
                                            class="px-2 py-1 rounded-md transition-all flex items-center gap-1 text-neutral-600 hover:text-neutral-900 hover:bg-neutral-200">
                                        <span>Khác ({{ $otherWarehouses->count() }})</span>
                                        <iconify-icon icon="solar:alt-arrow-down-linear" class="text-xs"></iconify-icon>
                                    </button>
                                    <div id="other_warehouses_dropdown_menu" 
                                         class="hidden absolute left-0 mt-2 w-56 bg-white border border-neutral-200 rounded-xl shadow-lg z-50 py-2 max-h-72 overflow-y-auto">
                                        @foreach($otherWarehouses as $wh)
                                            <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['warehouse_id' => $wh->id])) }}"
                                               class="px-4 py-2 block text-xs hover:bg-primary-50 hover:text-primary-700 transition-colors {{ (string)$selectedWarehouseId === (string)$wh->id ? 'font-bold text-primary-700 bg-primary-50' : 'text-neutral-700' }}">
                                                <div class="font-semibold">{{ $wh->name }}</div>
                                                <div class="text-[10px] text-neutral-400">{{ $wh->code }}</div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Status Pills (All / Low stock / Out of stock) --}}
                        <div class="bg-neutral-100 p-0.5 rounded-lg flex items-center gap-0.5 text-xs font-semibold">
                            <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['stock_filter' => 'all'])) }}"
                               class="px-2.5 py-1 rounded-md transition-all {{ $stockFilter === 'all' ? 'bg-white text-neutral-900 shadow-2xs font-bold' : 'text-neutral-500 hover:text-neutral-900' }}">
                                Tất cả ({{ $totalItems }})
                            </a>
                            <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['stock_filter' => 'low_stock'])) }}"
                               class="px-2.5 py-1 rounded-md transition-all {{ $stockFilter === 'low_stock' ? 'bg-warning-500 text-white shadow-2xs font-bold' : 'text-warning-700 hover:text-warning-800' }}">
                                Tồn thấp
                            </a>
                            <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['stock_filter' => 'out_of_stock'])) }}"
                               class="px-2.5 py-1 rounded-md transition-all {{ $stockFilter === 'out_of_stock' ? 'bg-rose-600 text-white shadow-2xs font-bold' : 'text-rose-600 hover:text-rose-800' }}">
                                Hết hàng
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        {{-- Import Excel --}}
                        <button type="button" onclick="openImportStockModal('{{ $selectedWarehouseId }}')" class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 shadow-2xs">
                            <iconify-icon icon="solar:file-excel-bold" class="text-base"></iconify-icon> Nhập Excel tồn kho
                        </button>

                        {{-- Export Excel --}}
                        <a href="{{ route('inventory.export-stock', request()->query()) }}" class="btn bg-white border border-neutral-300 text-neutral-700 hover:bg-neutral-50 px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 shadow-2xs">
                            <iconify-icon icon="solar:file-excel-outline" class="text-base text-emerald-600"></iconify-icon> Xuất Excel
                        </a>

                        <x-table-column-toggle table-id="inventoryTable" />

                        {{-- Add Material --}}
                        <button type="button" onclick="openModal('createMaterialModal')" class="btn btn-primary px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 shadow-sm">
                            <iconify-icon icon="ic:baseline-plus" class="text-base"></iconify-icon> Thêm vật tư
                        </button>
                    </div>
                </div>

                {{-- Tầng 2: Bộ lọc thời gian & Bộ lọc nâng cao --}}
                <div class="flex items-center justify-between flex-wrap gap-2.5">
                    <div class="flex items-center gap-2 flex-wrap">
                        {{-- Bộ lọc ngày tháng chung --}}
                        <x-date-filter :action="route('inventory.index')" />

                        @if($dateMode !== 'all')
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-1 rounded-md bg-primary-50 text-primary-700 border border-primary-200">
                                <iconify-icon icon="solar:calendar-date-bold" class="text-xs"></iconify-icon>
                                {{ $dateLabel }}
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Nút Bộ lọc Modal --}}
                        <button type="button" onclick="openModal('inventoryFilterModal')" 
                            class="btn bg-white border border-neutral-300 hover:bg-neutral-50 text-neutral-700 text-xs px-2.5 py-1.5 rounded-lg flex items-center gap-1.5 shadow-2xs font-semibold">
                            <iconify-icon icon="solar:filter-outline" class="text-base text-neutral-500"></iconify-icon>
                            <span>Bộ lọc nâng cao</span>
                            @if($activeFilterCount > 0)
                                <span class="w-4 h-4 rounded-full bg-primary-600 text-white text-[9px] font-bold flex items-center justify-center">
                                    {{ $activeFilterCount }}
                                </span>
                            @endif
                        </button>

                        @if($isFiltered)
                            <a href="{{ route('inventory.index') }}" class="btn text-xs px-2 py-1.5 rounded-lg flex items-center gap-1 text-rose-600 hover:bg-rose-50 font-semibold" title="Xóa tất cả bộ lọc">
                                <iconify-icon icon="solar:close-circle-outline" class="text-base"></iconify-icon>
                                Xóa lọc
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Thanh Lưu ý phân loại kho theo Tài khoản kế toán --}}
            <div class="px-4 py-2 bg-neutral-50/80 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-2 text-[11px] text-neutral-600">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="inline-flex items-center gap-1 font-bold text-neutral-800 bg-white border border-neutral-200 px-2 py-0.5 rounded-md shadow-2xs">
                        <iconify-icon icon="solar:info-circle-bold" class="text-primary-600 text-xs"></iconify-icon>
                        Lưu ý mã phân loại kho:
                    </span>
                    <span><strong class="text-neutral-800">152</strong>: Nguyên vật liệu (cốt ván, acrylic, melamin, keo, chỉ...)</span>
                    <span class="text-neutral-300">&bull;</span>
                    <span><strong class="text-neutral-800">153</strong>: Phụ kiện & dụng cụ xưởng (bản lề, ray, ốc vít...)</span>
                    <span class="text-neutral-300">&bull;</span>
                    <span><strong class="text-neutral-800">155</strong>: Thành phẩm gia công hoàn thiện (cánh kính, tấm ép...)</span>
                </div>
                <span class="text-[10px] text-neutral-400 italic">Hệ thống 24 kho chuẩn</span>
            </div>

            {{-- Table Area --}}
            <div class="p-1">
                <div class="overflow-x-auto w-full">
                    <table class="w-full border-collapse text-xs text-left" style="min-width: 1700px;" id="inventoryTable">
                        <thead>
                            <tr class="bg-neutral-100 text-neutral-700 uppercase font-bold border-b border-neutral-200">
                                <th class="py-3 px-3 text-center whitespace-nowrap" style="width: 50px; min-width: 50px;" data-column-name="STT">STT</th>
                                <th class="py-3 px-3 whitespace-nowrap" style="min-width: 140px;" data-column-name="Mã SKU">Mã SKU</th>
                                <th class="py-3 px-3 text-center whitespace-nowrap" style="min-width: 100px;" data-column-name="Mã gốc TQ">Mã gốc TQ</th>
                                <th class="py-3 px-4" style="min-width: 380px; width: 420px;" data-column-name="Tên vật tư / Quy cách">Tên vật tư / Quy cách</th>
                                <th class="py-3 px-3 whitespace-nowrap" style="min-width: 150px;" data-column-name="Kho lưu trữ">Kho lưu trữ</th>
                                <th class="py-3 px-3 whitespace-nowrap" style="min-width: 130px;" data-column-name="Nhóm hàng">Nhóm hàng</th>
                                <th class="py-3 px-3 text-center whitespace-nowrap" style="min-width: 65px;" data-column-name="ĐVT">ĐVT</th>
                                <th class="py-3 px-3 text-right whitespace-nowrap" style="min-width: 100px;" data-column-name="Tồn đầu kỳ">Tồn đầu kỳ</th>
                                <th class="py-3 px-3 text-right text-emerald-700 whitespace-nowrap" style="min-width: 105px;" data-column-name="Nhận trong kỳ">Nhận trong kỳ</th>
                                <th class="py-3 px-3 text-right text-rose-700 whitespace-nowrap" style="min-width: 105px;" data-column-name="Tiêu thụ trong kỳ">Tiêu thụ trong kỳ</th>
                                <th class="py-3 px-3 text-right text-neutral-900 whitespace-nowrap" style="min-width: 105px;" data-column-name="Tồn cuối kỳ">Tồn cuối kỳ</th>
                                <th class="py-3 px-3 text-right whitespace-nowrap" style="min-width: 110px;" data-column-name="Giá mua">Giá mua</th>
                                <th class="py-3 px-3 text-right whitespace-nowrap" style="min-width: 120px;" data-column-name="Giá trị tồn">Giá trị tồn</th>
                                <th class="py-3 px-3 text-center whitespace-nowrap" style="min-width: 95px;" data-column-name="Trạng thái">Trạng thái</th>
                                <th class="py-3 px-3 text-center whitespace-nowrap" style="width: 100px; min-width: 100px;" data-column-name="Thao tác">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 bg-white">
                            @forelse($materials as $index => $m)
                                @php
                                    $costPrice = (float)$m->cost_price;
                                    $startStock = (float)$m->period_start_stock;
                                    $inQty = (float)$m->period_in_qty;
                                    $outQty = (float)$m->period_out_qty;
                                    $endStock = (float)$m->period_end_stock;
                                    $minStock = (float)$m->min_stock;
                                    $lineVal = $endStock * $costPrice;

                                    $editPayload = [
                                        'id'           => $m->id,
                                        'code'         => $m->code,
                                        'name'         => $m->name,
                                        'origin_code'  => $m->origin,
                                        'nature'       => 'Vật tư hàng hóa',
                                        'category'     => $m->category,
                                        'unit'         => $m->unit,
                                        'cost_price'   => $costPrice,
                                        'stock'        => $endStock,
                                        'min_stock'    => $minStock,
                                        'warehouse_id' => $m->warehouse_id,
                                        'status'       => $m->status,
                                        'notes'        => $m->notes,
                                        'foil_name'    => '',
                                        'is_price'     => 0,
                                        'is_material'  => 1,
                                    ];
                                @endphp
                                <tr class="hover:bg-primary-50/40 transition-colors">
                                    <td class="py-3 px-3 text-center text-neutral-500 font-medium whitespace-nowrap">
                                        {{ $materials->firstItem() + $index }}
                                    </td>
                                    <td class="py-3 px-3 font-bold text-neutral-900 whitespace-nowrap">
                                        <a href="{{ route('inventory.materials.stock-card', $m->id) }}" 
                                           onclick="event.preventDefault(); openStockCardModal({{ $m->id }}, 'material')" 
                                           class="hover:text-primary-600 hover:underline inline-flex items-center gap-1.5" 
                                           title="Xem thẻ kho cho {{ $m->code }}">
                                            <span>{{ $m->code }}</span>
                                        </a>
                                    </td>
                                    <td class="py-3 px-3 text-center text-neutral-600 font-medium whitespace-nowrap">
                                        {{ $m->origin ?: '—' }}
                                    </td>
                                    <td class="py-3 px-4" style="min-width: 380px; width: 420px; white-space: normal;">
                                        <div class="font-bold text-neutral-900 text-xs leading-relaxed" style="white-space: normal; word-break: normal;">{{ $m->name }}</div>
                                        @if($m->notes)
                                            <div class="text-[11px] text-neutral-400 truncate max-w-md mt-0.5">{{ $m->notes }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-neutral-100 text-neutral-700">
                                            {{ $m->warehouse?->name ?? 'Kho tổng' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        <span class="text-neutral-600 text-xs">
                                            {{ $m->category ?: '—' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $m->unit === 'm' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-neutral-100 text-neutral-700' }}">
                                            {{ $m->unit ?: 'Cái' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-right font-medium text-neutral-600 whitespace-nowrap">
                                        {{ fmod($startStock, 1) == 0 ? number_format($startStock, 0, ',', '.') : number_format($startStock, 2, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-bold text-emerald-700 whitespace-nowrap">
                                        {{ $inQty > 0 ? '+' . (fmod($inQty, 1) == 0 ? number_format($inQty, 0, ',', '.') : number_format($inQty, 2, ',', '.')) : '—' }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-bold text-rose-700 whitespace-nowrap">
                                        {{ $outQty > 0 ? '-' . (fmod($outQty, 1) == 0 ? number_format($outQty, 0, ',', '.') : number_format($outQty, 2, ',', '.')) : '—' }}
                                    </td>
                                    <td class="py-3 px-3 text-right whitespace-nowrap">
                                        <div class="font-black text-sm {{ $endStock <= 0 ? 'text-rose-600' : ($endStock <= $minStock ? 'text-warning-600' : 'text-neutral-900') }}">
                                            {{ fmod($endStock, 1) == 0 ? number_format($endStock, 0, ',', '.') : number_format($endStock, 2, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-right font-medium text-neutral-700 whitespace-nowrap">
                                        {{ $costPrice > 0 ? number_format($costPrice, 0, ',', '.') . 'đ' : '—' }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-semibold text-neutral-800 whitespace-nowrap">
                                        {{ $lineVal > 0 ? number_format($lineVal, 0, ',', '.') . 'đ' : '—' }}
                                    </td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        @if($endStock <= 0)
                                            <span class="bg-rose-100 text-rose-700 text-[10px] px-2 py-0.5 rounded-full font-bold inline-block">Hết hàng</span>
                                        @elseif($endStock <= $minStock)
                                            <span class="bg-warning-100 text-warning-800 text-[10px] px-2 py-0.5 rounded-full font-bold inline-block">Tồn thấp</span>
                                        @else
                                            <span class="bg-emerald-100 text-emerald-700 text-[10px] px-2 py-0.5 rounded-full font-bold inline-block">Còn hàng</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" 
                                                    onclick="openStockCardModal({{ $m->id }}, 'material')" 
                                                    class="p-1 text-primary-600 hover:text-primary-800 text-base" 
                                                    title="Xem thẻ kho">
                                                <iconify-icon icon="solar:history-bold"></iconify-icon>
                                            </button>
                                            <button type="button" 
                                                    data-material='@json($editPayload)'
                                                    onclick="editMaterial(this)" 
                                                    class="p-1 text-warning-600 hover:text-warning-800 text-base transition-colors cursor-pointer" 
                                                    title="Chỉnh sửa vật tư">
                                                <iconify-icon icon="lucide:edit"></iconify-icon>
                                            </button>
                                            <form action="{{ route('inventory.materials.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa vật tư {{ addslashes($m->code) }}?')" class="inline-block m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 text-danger-600 hover:text-danger-800 text-base" title="Xóa">
                                                    <iconify-icon icon="fluent:delete-24-regular"></iconify-icon>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" class="py-12 text-center text-neutral-500 font-medium bg-neutral-50">
                                        <iconify-icon icon="solar:box-broken-outline" class="text-4xl text-neutral-300 mb-2 block"></iconify-icon>
                                        Chưa tìm thấy vật tư hàng hóa nào. Bấm <strong>"Thêm vật tư"</strong> để bắt đầu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-neutral-100 font-bold text-neutral-800 border-t-2 border-neutral-300 text-xs">
                                <td colspan="7" class="py-3 px-4 text-right uppercase">Tổng trên trang này:</td>
                                <td class="py-3 px-3 text-right font-medium text-neutral-700">
                                    {{ fmod($pageSumStartStock, 1) == 0 ? number_format($pageSumStartStock, 0, ',', '.') : number_format($pageSumStartStock, 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-bold text-emerald-700">
                                    {{ $pageSumInQty > 0 ? '+' . (fmod($pageSumInQty, 1) == 0 ? number_format($pageSumInQty, 0, ',', '.') : number_format($pageSumInQty, 2, ',', '.')) : '0' }}
                                </td>
                                <td class="py-3 px-3 text-right font-bold text-rose-700">
                                    {{ $pageSumOutQty > 0 ? '-' . (fmod($pageSumOutQty, 1) == 0 ? number_format($pageSumOutQty, 0, ',', '.') : number_format($pageSumOutQty, 2, ',', '.')) : '0' }}
                                </td>
                                <td class="py-3 px-3 text-right font-black text-neutral-900">
                                    {{ fmod($pageSumEndStock, 1) == 0 ? number_format($pageSumEndStock, 0, ',', '.') : number_format($pageSumEndStock, 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right text-neutral-400">—</td>
                                <td class="py-3 px-3 text-right font-black text-neutral-900">
                                    {{ $pageSumTotalValue > 0 ? number_format($pageSumTotalValue, 0, ',', '.') . 'đ' : '0đ' }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($materials->hasPages())
                    <div class="p-4 border-t border-neutral-200">
                        {{ $materials->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODAL 1: THÊM VẬT TƯ MỚI --}}
<x-modal name="createMaterialModal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50 rounded-t-xl">
        <div class="flex items-center gap-2">
            <iconify-icon icon="solar:box-minimalistic-bold" class="text-xl text-primary-600"></iconify-icon>
            <h5 class="font-bold text-base text-neutral-800 mb-0">Thêm Vật tư / Hàng hóa Mới</h5>
        </div>
        <button type="button" onclick="closeModal('createMaterialModal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('inventory.materials.store') }}" method="POST">
        @csrf
        <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Mã SKU / Mã vật tư <span class="text-danger-500">*</span></label>
                    <input type="text" name="code" id="create_mat_code" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs font-bold text-primary-700 focus:border-primary-500" placeholder="Vd: KINH-CL-5LY, 153-PK-01..." required>
                </div>
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Mã xuất xứ (NCC)</label>
                    <input type="text" name="origin_code" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="Vd: Austria, Malaysia, TQ...">
                </div>
            </div>

            <div>
                <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Tên hàng hóa / vật tư <span class="text-danger-500">*</span></label>
                <input type="text" name="name" id="create_mat_name" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500 font-medium" placeholder="Vd: Kính cường lực 5ly trà nhạt, Bản lề giảm chấn..." required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Kho lưu trữ <span class="text-danger-500">*</span></label>
                    <select name="warehouse_id" id="create_mat_warehouse_id" class="form-select rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500 font-semibold text-neutral-800" required>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (string)($selectedWarehouseId ?? 1) === (string)$wh->id ? 'selected' : '' }}>
                                {{ $wh->name }} ({{ $wh->code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Đơn vị tính <span class="text-danger-500">*</span></label>
                    <input type="text" name="unit" id="create_mat_unit" value="Cái" list="commonUnits" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500 font-bold" required>
                    <datalist id="commonUnits">
                        <option value="Cái"></option>
                        <option value="Tấm"></option>
                        <option value="Mét"></option>
                        <option value="Kg"></option>
                        <option value="Hộp"></option>
                        <option value="Cây"></option>
                        <option value="Cuộn"></option>
                        <option value="Bộ"></option>
                        <option value="Thùng"></option>
                    </datalist>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Nhóm hàng / Phân loại</label>
                    <input type="text" name="category" id="create_mat_category" list="categoryOptions" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="Vd: 153_PK, Phụ kiện, Kính...">
                    <datalist id="categoryOptions">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Tính chất</label>
                    <select name="nature" id="create_mat_nature" class="form-select rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500 font-semibold text-neutral-800">
                        <option value="Vật tư hàng hóa" selected>Vật tư hàng hóa</option>
                        <option value="Thành phẩm">Thành phẩm</option>
                        <option value="Bán thành phẩm">Bán thành phẩm</option>
                        <option value="Dịch vụ">Dịch vụ</option>
                        <option value="Công cụ dụng cụ">Công cụ dụng cụ</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Giá mua gần nhất (đ)</label>
                    <input type="number" name="cost_price" id="create_mat_cost_price" value="0" min="0" step="100" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Tồn kho ban đầu</label>
                    <input type="number" name="initial_stock" value="0" min="0" step="0.01" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Định mức tồn min</label>
                    <input type="number" name="min_stock" value="5" min="0" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
            </div>

            <div>
                <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Ghi chú</label>
                <textarea name="notes" rows="2" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="Thông số kỹ thuật, vị trí kho, quy cách đóng gói..."></textarea>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex items-center justify-end gap-3 bg-neutral-50 rounded-b-xl">
            <button type="button" onclick="closeModal('createMaterialModal')" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-4 py-2 rounded-lg text-xs font-medium">Hủy</button>
            <button type="submit" class="btn btn-primary px-5 py-2 rounded-lg text-xs font-bold">Lưu vật tư</button>
        </div>
    </form>
</x-modal>

{{-- MODAL 2: CHỈNH SỬA VẬT TƯ --}}
<x-modal name="editMaterialModal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50 rounded-t-xl">
        <h5 class="font-bold text-base text-neutral-800">Chỉnh sửa Vật tư: <span id="edit_code_label" class="text-primary-600"></span></h5>
        <button type="button" onclick="closeModal('editMaterialModal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form id="editMaterialForm" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="is_price" id="edit_is_price" value="0">
        <input type="hidden" name="is_material" id="edit_is_material" value="1">
        <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Mã SKU</label>
                    <input type="text" id="edit_code" class="form-control rounded-lg w-full bg-neutral-100 border-neutral-200 px-3 py-2 text-xs font-bold" readonly>
                </div>
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Mã xuất xứ</label>
                    <input type="text" name="origin_code" id="edit_origin_code" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
            </div>

            <div>
                <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Tên hàng hóa / vật tư <span class="text-danger-500">*</span></label>
                <input type="text" name="name" id="edit_name" class="form-control rounded-lg w-full border-neutral-200 px-4 py-2 text-xs focus:border-primary-500" required>
            </div>

            <div>
                <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Tên Foil thay thế</label>
                <input type="text" name="foil_name" id="edit_foil_name" class="form-control rounded-lg w-full border-neutral-200 px-4 py-2 text-xs focus:border-primary-500" placeholder="Tên hàng Foil thay thế tên cũ">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Tính chất <span class="text-danger-500">*</span></label>
                    <select name="nature" id="edit_nature" class="form-select rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500 font-semibold text-neutral-800">
                        <option value="Vật tư hàng hóa">Vật tư hàng hóa</option>
                        <option value="Thành phẩm">Thành phẩm</option>
                        <option value="Bán thành phẩm">Bán thành phẩm</option>
                        <option value="Dịch vụ">Dịch vụ</option>
                        <option value="Công cụ dụng cụ">Công cụ dụng cụ</option>
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Kho ngầm định</label>
                    <select name="warehouse_id" id="edit_warehouse_id" class="form-select rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500 font-semibold text-neutral-800">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Nhóm hàng</label>
                    <input type="text" name="category" id="edit_category" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Đơn vị tính <span class="text-danger-500">*</span></label>
                    <input type="text" name="unit" id="edit_unit" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" required>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Tồn kho thực tế</label>
                    <input type="number" name="stock" id="edit_stock" step="0.01" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Giá mua gần nhất (đ)</label>
                    <input type="number" name="cost_price" id="edit_cost_price" min="0" step="1000" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Tồn tối thiểu</label>
                    <input type="number" name="min_stock" id="edit_min_stock" min="0" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
            </div>

            <div>
                <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Trạng thái kinh doanh</label>
                <select name="status" id="edit_status" class="form-select rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                    <option value="active">Đang kinh doanh</option>
                    <option value="inactive">Ngừng kinh doanh</option>
                </select>
            </div>

            {{-- Thông số chỉ dán viền đi kèm tại Kho Dán Chỉ --}}
            <div id="editEdgeBandSection" class="p-3 bg-purple-50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-800/40 rounded-xl space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-purple-900 dark:text-purple-300 flex items-center gap-1.5">
                        <iconify-icon icon="solar:ruler-angular-bold" class="text-base text-purple-600"></iconify-icon>
                        Chỉ Dán Viền đi kèm (Kho Dán Chỉ)
                    </span>
                    <span class="text-[11px] text-purple-600 dark:text-purple-400 font-semibold" id="edit_edge_band_code_label">CHI-...</span>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="form-label text-[11px] font-semibold text-neutral-700 dark:text-neutral-300 mb-1 block">Tồn hiện tại (m)</label>
                        <input type="number" name="edge_band_current_stock" id="edit_edge_band_current_stock" min="0" step="0.1" class="form-control rounded-lg w-full border-purple-200 px-3 py-1.5 text-xs focus:border-purple-500">
                    </div>
                    <div>
                        <label class="form-label text-[11px] font-semibold text-neutral-700 dark:text-neutral-300 mb-1 block">Giá mua gần nhất chỉ (đ/m)</label>
                        <input type="number" name="edge_band_cost_price" id="edit_edge_band_cost_price" min="0" step="100" class="form-control rounded-lg w-full border-purple-200 px-3 py-1.5 text-xs focus:border-purple-500">
                    </div>
                    <div>
                        <label class="form-label text-[11px] font-semibold text-neutral-700 dark:text-neutral-300 mb-1 block">Tồn tối thiểu (m)</label>
                        <input type="number" name="edge_band_min_stock" id="edit_edge_band_min_stock" min="0" class="form-control rounded-lg w-full border-purple-200 px-3 py-1.5 text-xs focus:border-purple-500">
                    </div>
                </div>
            </div>

            <div>
                <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Ghi chú</label>
                <textarea name="notes" id="edit_notes" rows="2" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500"></textarea>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex items-center justify-end gap-3 bg-neutral-50 rounded-b-xl">
            <button type="button" onclick="closeModal('editMaterialModal')" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-4 py-2 rounded-lg text-xs font-medium">Hủy</button>
            <button type="submit" class="btn btn-warning text-white px-5 py-2 rounded-lg text-xs font-bold">Cập nhật</button>
        </div>
    </form>
</x-modal>

{{-- MODAL 3: XEM THẺ KHO (STOCK CARD MODAL) --}}
<x-modal name="stockCardModal" maxWidth="3xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-primary-50 rounded-t-xl">
        <div class="flex items-center gap-2">
            <iconify-icon icon="solar:history-bold" class="text-xl text-primary-600"></iconify-icon>
            <h5 class="font-bold text-base text-primary-900" id="stockCardModalTitle">Thẻ kho chi tiết</h5>
        </div>
        <button type="button" onclick="closeModal('stockCardModal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <div class="p-6 overflow-y-auto max-h-[75vh]" id="stockCardModalContent">
        <div class="text-center py-8 text-neutral-400">
            <iconify-icon icon="line-md:loading-loop" class="text-3xl animate-spin mb-2"></iconify-icon>
            <p class="text-xs">Đang tải lịch sử thẻ kho...</p>
        </div>
    </div>
</x-modal>

{{-- MODAL 4: BỘ LỌC VẬT TƯ & TỒN KHO NÂNG CAO --}}
<x-modal name="inventoryFilterModal" maxWidth="md">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50 rounded-t-xl">
        <div class="flex items-center gap-2">
            <iconify-icon icon="solar:filter-bold" class="text-xl text-primary-600"></iconify-icon>
            <h5 class="font-bold text-base text-neutral-800 mb-0">Bộ lọc vật tư & tồn kho</h5>
        </div>
        <button type="button" onclick="closeModal('inventoryFilterModal')" class="text-neutral-400 hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>

    <form action="{{ route('inventory.index') }}" method="GET" id="inventoryFilterForm">
        {{-- Giữ lại từ khóa tìm kiếm và lọc ngày hiện tại --}}
        @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
        @if(request('date_mode')) <input type="hidden" name="date_mode" value="{{ request('date_mode') }}"> @endif
        @if(request('date_val')) <input type="hidden" name="date_val" value="{{ request('date_val') }}"> @endif

        <div class="p-6 space-y-4">
            {{-- Nhóm hàng --}}
            <div>
                <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Nhóm hàng / Phân loại</label>
                <select name="category" class="form-select rounded-lg w-full border-neutral-300 px-3 py-2 text-xs focus:border-primary-500">
                    <option value="">-- Tất cả nhóm hàng --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $selectedCategory == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Kho hàng --}}
            <div>
                <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Kho lưu trữ</label>
                <select name="warehouse_id" class="form-select rounded-lg w-full border-neutral-300 px-3 py-2 text-xs focus:border-primary-500">
                    <option value="">-- Tất cả kho --</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $selectedWarehouseId == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tình trạng tồn --}}
            <div>
                <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Tình trạng tồn kho</label>
                <select name="stock_filter" class="form-select rounded-lg w-full border-neutral-300 px-3 py-2 text-xs focus:border-primary-500">
                    <option value="all" {{ $stockFilter === 'all' ? 'selected' : '' }}>Tất cả</option>
                    <option value="in_stock" {{ $stockFilter === 'in_stock' ? 'selected' : '' }}>Còn hàng trong kho</option>
                    <option value="low_stock" {{ $stockFilter === 'low_stock' ? 'selected' : '' }}>Tồn thấp (Dưới định mức an toàn)</option>
                    <option value="out_of_stock" {{ $stockFilter === 'out_of_stock' ? 'selected' : '' }}>Hết hàng (Tồn = 0)</option>
                </select>
            </div>

            {{-- Khoảng ngày phát sinh tùy chọn --}}
            <div class="border-t border-neutral-200 pt-3">
                <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Hoặc lọc theo khoảng ngày phát sinh</label>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-[11px] text-neutral-500 block mb-1">Từ ngày</span>
                        <input type="date" name="filter_start_date" value="{{ request('filter_start_date') }}" class="form-control rounded-lg w-full border-neutral-300 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <span class="text-[11px] text-neutral-500 block mb-1">Đến ngày</span>
                        <input type="date" name="filter_end_date" value="{{ request('filter_end_date') }}" class="form-control rounded-lg w-full border-neutral-300 px-3 py-2 text-xs">
                    </div>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-neutral-200 flex items-center justify-between bg-neutral-50 rounded-b-xl">
            <a href="{{ route('inventory.index') }}" class="text-xs text-rose-600 hover:underline font-semibold flex items-center gap-1">
                <iconify-icon icon="solar:restart-bold"></iconify-icon> Thiết lập lại
            </a>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeModal('inventoryFilterModal')" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-4 py-2 rounded-lg text-xs font-medium">Đóng</button>
                <button type="submit" class="btn btn-primary px-5 py-2 rounded-lg text-xs font-bold shadow-sm">Áp dụng lọc</button>
            </div>
        </div>
    </form>
</x-modal>




@push('scripts')
<script>
    function toggleOtherWhDropdown(e) {
        e.stopPropagation();
        const menu = document.getElementById('other_warehouses_dropdown_menu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }
    document.addEventListener('click', function(e) {
        const wrap = document.getElementById('other_warehouses_dropdown_wrapper');
        const menu = document.getElementById('other_warehouses_dropdown_menu');
        if (wrap && menu && !wrap.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });

    if (typeof window.openModal !== 'function') {
        window.openModal = function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
        };
    }
    if (typeof window.closeModal !== 'function') {
        window.closeModal = function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.style.display = 'none';
                document.body.style.overflow = '';
            }
        };
    }

    function editMaterial(btnOrObj) {
        let item = btnOrObj;
        if (btnOrObj instanceof HTMLElement) {
            const raw = btnOrObj.getAttribute('data-material');
            try {
                item = JSON.parse(raw);
            } catch (e) {
                try {
                    const txt = document.createElement('textarea');
                    txt.innerHTML = raw;
                    item = JSON.parse(txt.value);
                } catch (e2) {
                    console.error("Lỗi parse data-material:", e, e2);
                    alert("Không thể đọc thông tin vật tư. Vui lòng tải lại trang.");
                    return;
                }
            }
        }
        if (!item) return;

        const editForm = document.getElementById('editMaterialForm');
        if (editForm) editForm.action = `/warehouses/materials/${item.id}`;
        
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val !== null && val !== undefined ? val : '';
        };
        const setText = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val !== null && val !== undefined ? val : '';
        };

        setText('edit_code_label', item.code || item.color_code || '');
        setVal('edit_code', item.code || item.color_code || '');
        setVal('edit_name', item.name || ('Tấm ' + (item.code || item.color_code || '')));
        setVal('edit_foil_name', item.foil_name || '');
        setVal('edit_origin_code', item.origin_code || '');
        setVal('edit_nature', item.nature || 'Vật tư hàng hóa');
        setVal('edit_category', item.category || item.price_group || '');
        setVal('edit_unit', item.unit || 'Tấm');
        setVal('edit_cost_price', item.cost_price || 0);
        setVal('edit_stock', item.stock !== undefined ? item.stock : 0);
        setVal('edit_min_stock', item.min_stock || 5);
        setVal('edit_warehouse_id', item.warehouse_id || '');
        setVal('edit_status', item.status || 'active');
        setVal('edit_notes', item.notes || '');
        setVal('edit_is_price', item.is_price ? '1' : '0');

        const edgeSection = document.getElementById('editEdgeBandSection');
        if (edgeSection) {
            if (item.is_price) {
                edgeSection.classList.add('hidden');
            } else {
                edgeSection.classList.remove('hidden');
                setText('edit_edge_band_code_label', item.edge_band_code || ('CHI-' + (item.code || item.color_code || '')));
                setVal('edit_edge_band_current_stock', item.edge_band_current_stock || 0);
                setVal('edit_edge_band_cost_price', item.edge_band_cost_price || 0);
                setVal('edit_edge_band_min_stock', item.edge_band_min_stock || 100);
            }
        }

        openModal('editMaterialModal');
    }

    function openStockCardModal(materialId, type = 'material') {
        const container = document.getElementById('stockCardModalContent');
        if (container) {
            container.innerHTML = `
                <div class="text-center py-8 text-neutral-400">
                    <iconify-icon icon="line-md:loading-loop" class="text-3xl animate-spin mb-2"></iconify-icon>
                    <p class="text-xs">Đang tải lịch sử thẻ kho...</p>
                </div>
            `;
        }
        openModal('stockCardModal');

        fetch(`/warehouses/materials/${materialId}/stock-card?type=${encodeURIComponent(type)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            return res.text();
        })
        .then(html => {
            if (container) container.innerHTML = html;
        })
        .catch(err => {
            if (container) container.innerHTML = `<div class="p-4 text-danger-600 text-xs">Lỗi khi tải thẻ kho: ${err.message || err}</div>`;
        });
    }

</script>
@endpush

@endsection
