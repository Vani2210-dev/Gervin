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

        {{-- 4 Summary KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Total Items --}}
            <div class="card p-4 rounded-xl border border-neutral-200 bg-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center shrink-0">
                    <iconify-icon icon="solar:box-bold" class="text-2xl"></iconify-icon>
                </div>
                <div>
                    <div class="text-xs font-semibold text-neutral-500 uppercase">Tổng mặt hàng</div>
                    <div class="text-2xl font-black text-neutral-800">{{ number_format($totalItems) }} <span class="text-xs font-normal text-neutral-500">mã SKU</span></div>
                </div>
            </div>

            {{-- Total Stock Qty --}}
            <div class="card p-4 rounded-xl border border-neutral-200 bg-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <iconify-icon icon="solar:warehouse-bold" class="text-2xl"></iconify-icon>
                </div>
                <div>
                    <div class="text-xs font-semibold text-emerald-600 uppercase">Tổng số lượng tồn</div>
                    <div class="text-2xl font-black text-emerald-700">{{ number_format($totalStockQty, 2, ',', '.') }} <span class="text-xs font-normal text-neutral-500">tấm</span></div>
                </div>
            </div>

            {{-- Total Stock Value --}}
            <div class="card p-4 rounded-xl border border-neutral-200 bg-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <iconify-icon icon="solar:wallet-money-bold" class="text-2xl"></iconify-icon>
                </div>
                <div>
                    <div class="text-xs font-semibold text-blue-600 uppercase">Tổng giá trị tồn kho</div>
                    <div class="text-2xl font-black text-blue-700">{{ number_format($totalStockValue, 0, ',', '.') }} <span class="text-xs font-normal text-neutral-500">đ</span></div>
                </div>
            </div>

            {{-- Low Stock Alert --}}
            <div class="card p-4 rounded-xl border border-neutral-200 bg-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <iconify-icon icon="solar:danger-triangle-bold" class="text-2xl"></iconify-icon>
                </div>
                <div>
                    <div class="text-xs font-semibold text-rose-600 uppercase">Cần nhập thêm</div>
                    <div class="text-2xl font-black text-rose-700">{{ number_format($lowStockCount) }} <span class="text-xs font-normal text-neutral-500">mã cảnh báo</span></div>
                </div>
            </div>
        </div>

        {{-- Filter & Action Bar --}}
        {{-- Filter & Action Bar --}}
        <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
            <div class="p-4 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-3">
                {{-- Left: Search & Smart Date Filter --}}
                <div class="flex items-center gap-3 flex-wrap">
                    {{-- Quick Search --}}
                    <form action="{{ route('inventory.index') }}" method="GET" class="relative">
                        @if(request('warehouse_id')) <input type="hidden" name="warehouse_id" value="{{ request('warehouse_id') }}"> @endif
                        @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                        @if(request('stock_filter')) <input type="hidden" name="stock_filter" value="{{ request('stock_filter') }}"> @endif
                        @if(request('date_mode')) <input type="hidden" name="date_mode" value="{{ request('date_mode') }}"> @endif
                        @if(request('date_val')) <input type="hidden" name="date_val" value="{{ request('date_val') }}"> @endif

                        <iconify-icon icon="ion:search-outline" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
                        <input type="text" name="search" value="{{ $search }}" class="form-control rounded-xl pl-9 pr-3 py-2 border-neutral-300 text-xs w-60 focus:border-primary-500 shadow-2xs" 
                            placeholder="Tìm mã SKU, tên, xuất xứ...">
                    </form>

                    {{-- Bộ lọc ngày tháng chung --}}
                    <x-date-filter :action="route('inventory.index')" />

                    @if($dateMode !== 'all')
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-1 rounded-md bg-primary-50 text-primary-700 border border-primary-200">
                            <iconify-icon icon="solar:calendar-date-bold" class="text-xs"></iconify-icon>
                            {{ $dateLabel }}
                        </span>
                    @endif
                </div>

                {{-- Right: Filter Button (modal), Reset filter, Status Pills, Export, Add --}}
                <div class="flex items-center gap-2 flex-wrap">
                    {{-- Nút Bộ lọc Modal --}}
                    <button type="button" onclick="openModal('inventoryFilterModal')" 
                        class="btn bg-white border border-neutral-300 hover:bg-neutral-50 text-neutral-700 text-xs px-3 py-2 rounded-xl flex items-center gap-2 shadow-2xs font-semibold">
                        <iconify-icon icon="solar:filter-outline" class="text-base text-neutral-500"></iconify-icon>
                        <span>Bộ lọc</span>
                        @if($activeFilterCount > 0)
                            <span class="w-5 h-5 rounded-full bg-primary-600 text-white text-[10px] font-bold flex items-center justify-center">
                                {{ $activeFilterCount }}
                            </span>
                        @endif
                    </button>

                    @if($isFiltered)
                        <a href="{{ route('inventory.index') }}" class="btn text-xs px-2 py-2 rounded-xl flex items-center gap-1 text-rose-600 hover:bg-rose-50 font-semibold" title="Xóa tất cả bộ lọc">
                            <iconify-icon icon="solar:close-circle-outline" class="text-base"></iconify-icon>
                            Xóa lọc
                        </a>
                    @endif

                    {{-- Warehouse Pills --}}
                    <div class="bg-neutral-100 p-1 rounded-xl flex items-center gap-1 text-xs font-semibold">
                        <a href="{{ route('inventory.index', array_merge(request()->query(), ['warehouse_id' => ''])) }}"
                           class="px-3 py-1 rounded-lg transition-all {{ empty($selectedWarehouseId) ? 'bg-white text-neutral-900 shadow-2xs font-bold' : 'text-neutral-500 hover:text-neutral-900' }}">
                            Tất cả kho
                        </a>
                        @foreach($warehouses as $wh)
                            <a href="{{ route('inventory.index', array_merge(request()->query(), ['warehouse_id' => $wh->id])) }}"
                               class="px-3 py-1 rounded-lg transition-all {{ (string)$selectedWarehouseId === (string)$wh->id ? ($wh->code === 'KHO-CHI' ? 'bg-amber-600 text-white shadow-2xs font-bold' : 'bg-primary-600 text-white shadow-2xs font-bold') : 'text-neutral-600 hover:text-neutral-900' }}">
                                {{ $wh->name }}
                            </a>
                        @endforeach
                    </div>

                    {{-- Status Pills (All / Low stock / Out of stock) --}}
                    <div class="bg-neutral-100 p-1 rounded-xl flex items-center gap-1 text-xs font-semibold">
                        <a href="{{ route('inventory.index', array_merge(request()->query(), ['stock_filter' => 'all'])) }}"
                           class="px-3 py-1 rounded-lg transition-all {{ $stockFilter === 'all' ? 'bg-white text-neutral-900 shadow-2xs font-bold' : 'text-neutral-500 hover:text-neutral-900' }}">
                            Tất cả ({{ $totalItems }})
                        </a>
                        <a href="{{ route('inventory.index', array_merge(request()->query(), ['stock_filter' => 'low_stock'])) }}"
                           class="px-3 py-1 rounded-lg transition-all {{ $stockFilter === 'low_stock' ? 'bg-warning-500 text-white shadow-2xs font-bold' : 'text-warning-700 hover:text-warning-800' }}">
                            Tồn thấp
                        </a>
                        <a href="{{ route('inventory.index', array_merge(request()->query(), ['stock_filter' => 'out_of_stock'])) }}"
                           class="px-3 py-1 rounded-lg transition-all {{ $stockFilter === 'out_of_stock' ? 'bg-rose-600 text-white shadow-2xs font-bold' : 'text-rose-600 hover:text-rose-800' }}">
                            Hết hàng
                        </a>
                    </div>

                    {{-- Export Excel --}}
                                        {{-- Import Excel --}}
                    <button type="button" onclick="openModal('importStockModal')" class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm">
                        <iconify-icon icon="solar:file-excel-bold" class="text-base"></iconify-icon> Nhập Excel tồn kho
                    </button>

                    <a href="{{ route('inventory.export-stock', request()->query()) }}" class="btn bg-white border border-neutral-300 text-neutral-700 hover:bg-neutral-50 px-3 py-2 rounded-xl text-xs font-bold flex items-center gap-2 shadow-2xs">
                        <iconify-icon icon="solar:file-excel-outline" class="text-base text-emerald-600"></iconify-icon> Xuất Excel
                    </a>

                    {{-- Add Material --}}
                    <button type="button" onclick="openModal('createMaterialModal')" class="btn btn-primary px-3 py-2 rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm">
                        <iconify-icon icon="ic:baseline-plus" class="text-base"></iconify-icon> Thêm vật tư mới
                    </button>
                </div>
            </div>

            {{-- Table Area --}}
            <div class="card-body p-0">
                <div class="overflow-x-auto w-full">
                    <table class="w-full border-collapse text-xs text-left">
                        <thead>
                            <tr class="bg-neutral-50 text-neutral-700 uppercase font-bold border-b border-neutral-200">
                                <th class="py-3 px-4 w-12 text-center">STT</th>
                                <th class="py-3 px-4 w-36">Mã SKU</th>
                                <th class="py-3 px-4 min-w-[260px]">Tên hàng hóa / Vật tư</th>
                                <th class="py-3 px-4 min-w-[240px]">Tên Foil thay thế</th>
                                <th class="py-3 px-4 w-28 text-center">Mã xuất xứ</th>
                                <th class="py-3 px-4 w-44">Nhóm hàng</th>
                                <th class="py-3 px-4 w-20 text-center">ĐVT</th>
                                <th class="py-3 px-4 w-32 text-right">Giá vốn</th>
                                <th class="py-3 px-4 w-36 text-right">Tồn kho thực tế</th>
                                <th class="py-3 px-4 w-36 text-right">Giá trị tồn</th>
                                <th class="py-3 px-4 w-28 text-center">Trạng thái</th>
                                <th class="py-3 px-4 w-28 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 bg-white">
                            @forelse($materials as $index => $m)
                                @php
                                    if ($isEdgeBandWarehouse) {
                                        $displaySku = $m->edge_band_code;
                                        $displayName = $m->edge_band_name;
                                        $colorTag = $m->color_code;
                                        $thickness = '';
                                        $originCode = $m->origin_code ?: '—';
                                        $categoryName = 'Chỉ dán viền';
                                        $displayUnit = $m->edge_band_unit ?: 'm';
                                        $displayCostPrice = (float)$m->edge_band_cost_price;
                                        $displayStock = (float)$m->edge_band_current_stock;
                                        $displayMinStock = (float)$m->edge_band_min_stock;
                                        $lineValue = $displayStock * $displayCostPrice;
                                        $stockCardType = 'edge_band';
                                        $colorLink = $m->color_code;
                                        $editPayload = [
                                            'id'                      => $m->id,
                                            'code'                    => $m->edge_band_code,
                                            'name'                    => $m->edge_band_name,
                                            'origin_code'             => $m->origin_code,
                                            'category'                => 'Chỉ dán viền',
                                            'unit'                    => $m->edge_band_unit ?: 'm',
                                            'cost_price'              => (float)$m->edge_band_cost_price,
                                            'stock'                   => (float)$m->edge_band_current_stock,
                                            'min_stock'               => (float)$m->edge_band_min_stock,
                                            'warehouse_id'            => $m->warehouse_id,
                                            'status'                  => $m->status,
                                            'notes'                   => $m->notes,
                                            'foil_name'                => $m->foil_name ?? '',
                                            'is_price'                => 0,
                                            'edge_band_unit'          => $m->edge_band_unit ?: 'm',
                                            'edge_band_current_stock' => (float)$m->edge_band_current_stock,
                                            'edge_band_cost_price'    => (float)$m->edge_band_cost_price,
                                            'edge_band_min_stock'     => (float)$m->edge_band_min_stock,
                                        ];
                                    } else {
                                        $isPriceModel = ($m instanceof \App\Models\WoodBoardPrice);
                                        $board = $isPriceModel ? $m->board : $m;
                                        $color = $board?->color_code ?? '';

                                        $displaySku = $isPriceModel 
                                            ? ($m->code ?: ($color . ($m->type?->prefix ?? ''))) 
                                            : $m->color_code;

                                        $typeName = $isPriceModel ? ($m->name ?: ($m->type?->name ?? 'Tấm Acrylic')) : $m->name;
                                        $displayName = 'Tấm ' . $color . ($typeName ? ' - ' . $typeName : '');
                                        $colorTag = $color;
                                        $thickness = $isPriceModel ? $m->thickness : '';
                                        $originCode = $board?->origin_code ?: '—';
                                        $categoryName = $isPriceModel ? ($m->type?->name ?? $board?->price_group ?? 'Tấm cốt gỗ') : ($m->price_group ?: 'Tấm cốt gỗ');
                                        $displayUnit = $board?->unit ?: 'Tấm';
                                        $displayCostPrice = (float)($isPriceModel ? $m->price_board : $m->cost_price);
                                        $displayStock = (float)($isPriceModel ? $m->stock : $m->total_stock);
                                        $displayMinStock = (float)($isPriceModel ? 5 : $m->min_stock);
                                        $lineValue = $displayStock * $displayCostPrice;
                                        $stockCardType = $isPriceModel ? 'price' : 'board';
                                        $colorLink = $color;
                                        $editPayload = [
                                            'id'           => $m->id,
                                            'code'         => $displaySku,
                                            'name'         => $displayName,
                                            'origin_code'  => $board?->origin_code,
                                            'category'     => $categoryName,
                                            'unit'         => $displayUnit,
                                            'cost_price'   => $displayCostPrice,
                                            'stock'        => $displayStock,
                                            'min_stock'    => $displayMinStock,
                                            'warehouse_id' => $board?->warehouse_id,
                                            'status'       => $board?->status ?? 'active',
                                            'notes'        => $board?->notes,
                                            'foil_name'     => $isPriceModel ? ($m->foil_name ?: ($board?->foil_name ?: '')) : ($m->foil_name ?: ''),
                                            'is_price'     => $isPriceModel ? 1 : 0,
                                        ];
                                    }
                                @endphp
                                <tr class="hover:bg-primary-50 hover:bg-opacity-40 transition-colors">
                                    <td class="py-3 px-4 text-center text-neutral-500 font-medium">
                                        {{ $materials->firstItem() + $index }}
                                    </td>
                                    <td class="py-3 px-4 font-bold text-neutral-900 whitespace-nowrap">
                                        <a href="{{ route('inventory.materials.stock-card', [$m->id, 'type' => $stockCardType]) }}" 
                                           onclick="event.preventDefault(); openStockCardModal({{ $m->id }}, '{{ $stockCardType }}')" 
                                           class="hover:text-primary-600 hover:underline flex items-center gap-2" 
                                           title="Xem thẻ kho cho {{ $displaySku }}">
                                            <span>{{ $displaySku }}</span>
                                        </a>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-neutral-900 text-xs">{{ $displayName }}</div>
                                        <div class="flex items-center gap-2 mt-0.5 text-[11px] text-neutral-500 flex-wrap">
                                            @if($colorTag)
                                                <span class="inline-flex items-center gap-1 font-semibold text-primary-700 bg-primary-50 px-2 py-0.5 rounded border border-primary-200">
                                                    Mã màu: <strong>{{ $colorTag }}</strong>
                                                </span>
                                            @endif
                                            @if(!empty($thickness))
                                                <span class="text-neutral-300">•</span>
                                                <span class="text-neutral-500 font-medium">Độ dày: <strong>{{ $thickness }}</strong></span>
                                            @endif
                                        </div>
                                        @if(($m->notes ?? false) && !$isEdgeBandWarehouse)
                                            <div class="text-[11px] text-neutral-400 truncate max-w-xs mt-0.5">{{ $m->notes }}</div>
                                        @endif
                                    </td>
                                                                        <td class="py-3 px-4 min-w-[240px]">
                                        @php
                                            $foilNameDisplay = $isPriceModel 
                                                ? ($m->foil_name ?: ($board?->foil_name ?: '')) 
                                                : ($m->foil_name ?: '');
                                        @endphp
                                        @if($foilNameDisplay)
                                            <span class="text-neutral-800 font-medium text-xs block leading-relaxed">{{ $foilNameDisplay }}</span>
                                        @else
                                            <span class="text-neutral-300">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center text-neutral-600">
                                        {{ $originCode }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="bg-neutral-100 text-neutral-700 px-2 py-1 rounded text-[11px] font-semibold inline-block">
                                            {{ $categoryName }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center font-medium text-neutral-600">
                                        {{ $displayUnit }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-medium text-neutral-700 whitespace-nowrap">
                                        {{ $displayCostPrice > 0 ? number_format($displayCostPrice, 0, ',', '.') . 'đ' : '—' }}
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="font-black text-sm {{ $displayStock <= 0 ? 'text-rose-600' : ($displayStock <= $displayMinStock ? 'text-warning-600' : 'text-emerald-700') }}">
                                            {{ fmod($displayStock, 1) == 0 ? number_format($displayStock, 0, ',', '.') : number_format($displayStock, 2, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-right font-semibold text-neutral-800 whitespace-nowrap">
                                        {{ $lineValue > 0 ? number_format($lineValue, 0, ',', '.') . 'đ' : '—' }}
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        @if($displayStock <= 0)
                                            <span class="bg-rose-100 text-rose-700 text-[10px] px-2.5 py-1 rounded-full font-bold inline-block">Hết hàng</span>
                                        @elseif($displayStock <= $displayMinStock)
                                            <span class="bg-warning-100 text-warning-800 text-[10px] px-2.5 py-1 rounded-full font-bold inline-block">Tồn thấp</span>
                                        @else
                                            <span class="bg-success-100 text-success-700 text-[10px] px-2.5 py-1 rounded-full font-bold inline-block">Còn hàng</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            @if($colorLink)
                                                <a href="{{ route('wood_boards.index', ['search' => $colorLink]) }}" 
                                                   class="p-1 text-info-600 hover:text-info-800 text-base" 
                                                   title="Xem trên Bảng giá tấm">
                                                    <iconify-icon icon="solar:grid-bold"></iconify-icon>
                                                </a>
                                            @endif
                                            <a href="{{ route('inventory.materials.stock-card', [$m->id, 'type' => $stockCardType]) }}" 
                                               onclick="event.preventDefault(); openStockCardModal({{ $m->id }}, '{{ $stockCardType }}')" 
                                               class="p-1 text-primary-600 hover:text-primary-800 text-base" 
                                               title="Xem thẻ kho">
                                                <iconify-icon icon="solar:history-bold"></iconify-icon>
                                            </a>
                                            <button type="button" 
                                                    data-material='@json($editPayload)'
                                                    onclick="editMaterial(this)" 
                                                    class="p-1 text-warning-600 hover:text-warning-800 text-base transition-colors cursor-pointer" 
                                                    title="Chỉnh sửa hàng hóa">
                                                <iconify-icon icon="lucide:edit"></iconify-icon>
                                            </button>
                                            <form action="{{ route('inventory.materials.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa mặt hàng {{ addslashes($displaySku) }}?')" class="inline-block m-0">
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
                                    <td colspan="12" class="py-12 text-center text-neutral-500 font-medium bg-neutral-50">
                                        <iconify-icon icon="solar:box-broken-outline" class="text-4xl text-neutral-300 mb-2 block"></iconify-icon>
                                        Chưa tìm thấy vật tư hàng hóa nào. Bấm <strong>"Thêm vật tư mới"</strong> để bắt đầu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
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
        <h5 class="font-bold text-base text-neutral-800">Thêm Vật tư / Hàng hóa Mới</h5>
        <button type="button" onclick="closeModal('createMaterialModal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('inventory.materials.store') }}" method="POST">
        @csrf
        <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
            {{-- CHỌN NHANH TỪ BẢNG GIÁ TẤM --}}
            <div class="p-3.5 bg-orange-50/70 dark:bg-orange-950/20 border border-orange-200 dark:border-orange-800/40 rounded-xl space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-orange-900 dark:text-orange-200 flex items-center gap-1.5">
                        <iconify-icon icon="solar:magic-stick-3-bold" class="text-orange-600 text-sm"></iconify-icon>
                        Lấy data từ Bảng giá tấm (Nhập mã màu & loại ván)
                    </span>
                    <span id="invBoardMatchPrice" class="text-xs font-bold text-emerald-700 dark:text-emerald-400"></span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label text-[11px] font-bold text-neutral-600 dark:text-neutral-300 mb-1 block">Mã màu ván</label>
                        <input type="text" id="inv_lookup_color" list="invColorList" oninput="onInvBoardLookup()"
                               class="form-control rounded-lg w-full border-neutral-300 px-3 py-1.5 text-xs font-mono font-bold uppercase focus:border-orange-500"
                               placeholder="Vd: GV01, PARC 01...">
                        <datalist id="invColorList">
                            @foreach($colorCodes as $code)
                                <option value="{{ $code }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="form-label text-[11px] font-bold text-neutral-600 dark:text-neutral-300 block mb-0">Loại ván (Cốt ván)</label>
                            <span class="text-[10px] text-neutral-400 font-semibold">Chọn nhanh:</span>
                        </div>
                        <div class="grid grid-cols-3 gap-1 mb-1.5">
                            <button type="button" onclick="selectInvTypeShortcut(1)" id="btnInvType_1"
                                    class="inv-type-btn px-1.5 py-1 rounded text-[11px] font-bold border transition-all bg-emerald-600 text-white border-emerald-600 shadow-xs">
                                MDF 1M
                            </button>
                            <button type="button" onclick="selectInvTypeShortcut(2)" id="btnInvType_2"
                                    class="inv-type-btn px-1.5 py-1 rounded text-[11px] font-bold border transition-all bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border-neutral-300 dark:border-neutral-700 hover:bg-neutral-50">
                                MDF 2M
                            </button>
                            <button type="button" onclick="selectInvTypeShortcut(3)" id="btnInvType_3"
                                    class="inv-type-btn px-1.5 py-1 rounded text-[11px] font-bold border transition-all bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border-neutral-300 dark:border-neutral-700 hover:bg-neutral-50">
                                Nhựa PVC
                            </button>
                        </div>
                        <select id="inv_lookup_type" onchange="onInvTypeSelectChange()"
                                class="form-select rounded-lg w-full border-neutral-300 px-3 py-1.5 text-xs focus:border-orange-500 font-semibold">
                            <option value="">-- Chọn loại ván --</option>
                            @foreach($boardTypes as $bt)
                                <option value="{{ $bt->id }}" data-prefix="{{ $bt->prefix }}" data-name="{{ $bt->name }}" {{ $bt->id == 1 ? 'selected' : '' }}>
                                    {{ $bt->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="invBoardMatchNote" class="text-[11px] text-emerald-800 dark:text-emerald-300 font-medium hidden"></div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Mã SKU / Mã vật tư <span class="text-danger-500">*</span></label>
                    <input type="text" name="code" id="create_mat_code" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs font-bold focus:border-primary-500" placeholder="Vd: GV120" required>
                </div>
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Mã xuất xứ (NCC)</label>
                    <input type="text" name="origin_code" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="Vd: 2201">
                </div>
            </div>

                        <div>
                <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Tên Foil thay thế</label>
                <input type="text" name="foil_name" id="create_mat_foil_name" class="form-control rounded-lg w-full border-neutral-200 px-4 py-2 text-xs focus:border-primary-500" placeholder="Tên hàng Foil thay thế tên cũ (nếu có)">
            </div>

            <div>
                <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Tên vật tư / Quy cách <span class="text-danger-500">*</span></label>
                <input type="text" name="name" id="create_mat_name" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="Vd: Acrylic GV120 Nhũ chống xước" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Nhóm hàng / Phân loại</label>
                    <input type="text" name="category" id="create_mat_category" list="categoryOptions" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="Vd: Acrylic TQ">
                    <datalist id="categoryOptions">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Đơn vị tính <span class="text-danger-500">*</span></label>
                    <input type="text" name="unit" id="create_mat_unit" value="Tấm" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" required>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Giá vốn ván (đ)</label>
                    <input type="number" name="cost_price" id="create_mat_cost_price" value="0" min="0" step="1000" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Định mức tồn min ván</label>
                    <input type="number" name="min_stock" value="50" min="0" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Tồn ván ban đầu (Tấm)</label>
                    <input type="number" name="initial_stock" value="0" min="0" step="0.01" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
            </div>

            {{-- Thông số chỉ dán viền đi kèm --}}
            <div class="p-3 bg-purple-50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-800/40 rounded-xl space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-purple-900 dark:text-purple-300 flex items-center gap-1.5">
                        <iconify-icon icon="solar:ruler-angular-bold" class="text-base text-purple-600"></iconify-icon>
                        Chỉ dán viền tương ứng (Tự động quản lý tại Kho Dán Chỉ)
                    </span>
                    <span class="text-[11px] text-purple-600 dark:text-purple-400 font-semibold">SKU: CHI-[Mã tấm]</span>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="form-label text-[11px] font-semibold text-neutral-700 dark:text-neutral-300 mb-1 block">Tồn chỉ ban đầu (m)</label>
                        <input type="number" name="edge_band_initial_stock" value="0" min="0" step="0.1" class="form-control rounded-lg w-full border-purple-200 px-3 py-1.5 text-xs focus:border-purple-500">
                    </div>
                    <div>
                        <label class="form-label text-[11px] font-semibold text-neutral-700 dark:text-neutral-300 mb-1 block">Giá vốn chỉ (đ/m)</label>
                        <input type="number" name="edge_band_cost_price" value="0" min="0" step="100" class="form-control rounded-lg w-full border-purple-200 px-3 py-1.5 text-xs focus:border-purple-500">
                    </div>
                    <div>
                        <label class="form-label text-[11px] font-semibold text-neutral-700 dark:text-neutral-300 mb-1 block">Tồn tối thiểu chỉ (m)</label>
                        <input type="number" name="edge_band_min_stock" value="100" min="0" class="form-control rounded-lg w-full border-purple-200 px-3 py-1.5 text-xs focus:border-purple-500">
                    </div>
                </div>
            </div>

            <div>
                <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Kho tấm ván mặc định</label>
                <select name="warehouse_id" class="form-select rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Ghi chú</label>
                <textarea name="notes" rows="2" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="Thông số kỹ thuật, ghi chú..."></textarea>
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
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Giá vốn (đ)</label>
                    <input type="number" name="cost_price" id="edit_cost_price" min="0" step="1000" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Tồn tối thiểu</label>
                    <input type="number" name="min_stock" id="edit_min_stock" min="0" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Kho hàng ván</label>
                    <select name="warehouse_id" id="edit_warehouse_id" class="form-select rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Trạng thái</label>
                    <select name="status" id="edit_status" class="form-select rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                        <option value="active">Đang kinh doanh</option>
                        <option value="inactive">Ngừng kinh doanh</option>
                    </select>
                </div>
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
                        <label class="form-label text-[11px] font-semibold text-neutral-700 dark:text-neutral-300 mb-1 block">Giá vốn chỉ (đ/m)</label>
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

    function openStockCardModal(materialId, type = 'price') {
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

    let invLookupTimeout = null;
    function onInvBoardLookup() {
        clearTimeout(invLookupTimeout);
        invLookupTimeout = setTimeout(async () => {
            const color = (document.getElementById("inv_lookup_color")?.value || "").trim().toUpperCase();
            const typeSelect = document.getElementById("inv_lookup_type");
            const typeId = typeSelect ? typeSelect.value : "";
            const selectedOpt = typeSelect ? typeSelect.selectedOptions[0] : null;
            const prefix = selectedOpt ? (selectedOpt.getAttribute("data-prefix") || "") : "";
            const typeName = selectedOpt ? (selectedOpt.getAttribute("data-name") || "") : "";

            if (!color && !typeId) return;

            const codeInput = document.getElementById("create_mat_code");
            const nameInput = document.getElementById("create_mat_name");
            const catInput = document.getElementById("create_mat_category");
            const priceInput = document.getElementById("create_mat_cost_price");

            if (color && codeInput) {
                codeInput.value = color + prefix;
                if (nameInput) {
                    nameInput.value = typeName ? `Tấm ${typeName} ${color}` : `Tấm Acrylic ${color}`;
                }
            }

            try {
                const res = await fetch(`/wood-boards/board-info?color_code=${encodeURIComponent(color)}&wood_board_type_id=${typeId || ""}`);
                const data = await res.json();
                if (data.success) {
                    if (data.computed_code && codeInput) codeInput.value = data.computed_code;
                    if (data.computed_name && nameInput) nameInput.value = data.computed_name;
                    if (data.price_group && catInput) catInput.value = data.price_group;
                    if (data.cost_price > 0 && priceInput) {
                        priceInput.value = data.cost_price;
                        const matchPriceEl = document.getElementById("invBoardMatchPrice");
                        if (matchPriceEl) {
                            matchPriceEl.textContent = "Giá bảng: " + new Intl.NumberFormat("vi-VN").format(data.cost_price) + "đ";
                        }
                    }
                    const noteEl = document.getElementById("invBoardMatchNote");
                    if (noteEl) {
                        if (data.board) {
                            noteEl.textContent = `✓ Đã khớp Bảng giá tấm: Nhóm ${data.price_group || 'chuẩn'}, độ dày ${data.computed_thickness || '17mm'}`;
                            noteEl.classList.remove("hidden");
                        } else {
                            noteEl.classList.add("hidden");
                        }
                    }
                }
            } catch (e) {
                console.error("Lookup board info error:", e);
            }
        }, 200);
    }

    function selectInvTypeShortcut(id) {
        const sel = document.getElementById("inv_lookup_type");
        if (sel) sel.value = id;
        updateInvTypeButtonsUI(id);
        onInvBoardLookup();
    }

    function onInvTypeSelectChange() {
        const sel = document.getElementById("inv_lookup_type");
        if (sel) updateInvTypeButtonsUI(sel.value);
        onInvBoardLookup();
    }

    function updateInvTypeButtonsUI(activeId) {
        document.querySelectorAll(".inv-type-btn").forEach(btn => {
            btn.className = "inv-type-btn px-1.5 py-1 rounded text-[11px] font-bold border transition-all bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border-neutral-300 dark:border-neutral-700 hover:bg-neutral-50";
        });
        const btn = document.getElementById("btnInvType_" + activeId);
        if (btn) {
            if (activeId == 1) btn.className = "inv-type-btn px-1.5 py-1 rounded text-[11px] font-bold border transition-all bg-emerald-600 text-white border-emerald-600 shadow-xs";
            else if (activeId == 2) btn.className = "inv-type-btn px-1.5 py-1 rounded text-[11px] font-bold border transition-all bg-purple-600 text-white border-purple-600 shadow-xs";
            else btn.className = "inv-type-btn px-1.5 py-1 rounded text-[11px] font-bold border transition-all bg-amber-600 text-white border-amber-600 shadow-xs";
        }
    }
</script>
@endpush

@endsection
