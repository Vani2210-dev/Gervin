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

                    {{-- Bộ lọc Ngày / Tháng / Năm / Tất cả thông minh --}}
                    <x-flexible-date-filter :dateMode="$dateMode" :dateVal="$dateVal" />

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
                                <th class="py-3 px-4 w-32">Mã SKU</th>
                                <th class="py-3 px-4 min-w-[200px]">Tên hàng hóa / Vật tư</th>
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
                                    $lineValue = $m->current_stock * $m->cost_price;
                                @endphp
                                <tr class="hover:bg-primary-50 hover:bg-opacity-40 transition-colors">
                                    <td class="py-3 px-4 text-center text-neutral-500 font-medium">
                                        {{ $materials->firstItem() + $index }}
                                    </td>
                                    <td class="py-3 px-4 font-bold text-neutral-900">
                                        <a href="javascript:void(0)" onclick="openStockCardModal({{ $m->id }})" class="hover:text-primary-600 hover:underline">
                                            {{ $m->code }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-neutral-800">{{ $m->name }}</div>
                                        @if($m->notes)
                                            <div class="text-[11px] text-neutral-400 truncate max-w-xs">{{ $m->notes }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center text-neutral-600">
                                        {{ $m->origin_code ?: '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-neutral-700">
                                        <span class="bg-neutral-100 text-neutral-700 px-2 py-1 rounded text-[11px] font-medium">
                                            {{ $m->category ?: 'Vật tư chung' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center font-medium text-neutral-600">
                                        {{ $m->unit }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-medium text-neutral-700">
                                        {{ $m->cost_price > 0 ? number_format($m->cost_price, 0, ',', '.') . 'đ' : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="font-black text-sm {{ $m->current_stock <= 0 ? 'text-danger-600' : ($m->current_stock <= $m->min_stock ? 'text-warning-600' : 'text-neutral-900') }}">
                                            {{ fmod($m->current_stock, 1) == 0 ? number_format($m->current_stock, 0, ',', '.') : number_format($m->current_stock, 2, ',', '.') }}
                                        </div>
                                        <div class="text-[10px] text-neutral-400">Định mức: {{ number_format($m->min_stock, 0) }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-right font-semibold text-neutral-800">
                                        {{ $lineValue > 0 ? number_format($lineValue, 0, ',', '.') . 'đ' : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($m->current_stock <= 0)
                                            <span class="bg-danger-100 text-danger-700 text-[10px] px-2 py-1 rounded-full font-bold inline-block">Hết hàng</span>
                                        @elseif($m->current_stock <= $m->min_stock)
                                            <span class="bg-warning-100 text-warning-800 text-[10px] px-2 py-1 rounded-full font-bold inline-block">Dưới định mức</span>
                                        @else
                                            <span class="bg-success-100 text-success-700 text-[10px] px-2 py-1 rounded-full font-bold inline-block">Còn hàng</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button" onclick="openStockCardModal({{ $m->id }})" class="p-1 text-primary-600 hover:text-primary-800 text-base" title="Xem thẻ kho">
                                                <iconify-icon icon="solar:history-bold"></iconify-icon>
                                            </button>
                                            <button type="button" onclick="editMaterial(@json($m))" class="p-1 text-warning-600 hover:text-warning-800 text-base" title="Chỉnh sửa">
                                                <iconify-icon icon="lucide:edit"></iconify-icon>
                                            </button>
                                            <form action="{{ route('inventory.materials.destroy', $m) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa vật tư {{ $m->code }}? Dữ liệu giao dịch liên quan sẽ bị xóa.')">
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
                                    <td colspan="11" class="py-12 text-center text-neutral-500 font-medium bg-neutral-50">
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
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Mã SKU / Mã vật tư <span class="text-danger-500">*</span></label>
                    <input type="text" name="code" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs font-bold focus:border-primary-500" placeholder="Vd: GV120" required>
                </div>
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Mã xuất xứ (NCC)</label>
                    <input type="text" name="origin_code" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="Vd: 2201">
                </div>
            </div>

            <div>
                <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Tên vật tư / Quy cách <span class="text-danger-500">*</span></label>
                <input type="text" name="name" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="Vd: Acrylic GV120 Nhũ chống xước" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Nhóm hàng / Phân loại</label>
                    <input type="text" name="category" list="categoryOptions" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="Vd: Acrylic TQ">
                    <datalist id="categoryOptions">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Đơn vị tính <span class="text-danger-500">*</span></label>
                    <input type="text" name="unit" value="Tấm" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" required>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Giá vốn (đ)</label>
                    <input type="number" name="cost_price" value="0" min="0" step="1000" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Định mức tồn min</label>
                    <input type="number" name="min_stock" value="50" min="0" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Tồn kho ban đầu</label>
                    <input type="number" name="initial_stock" value="0" min="0" step="0.01" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
            </div>

            <div>
                <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Kho mặc định</label>
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
                <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Tên vật tư <span class="text-danger-500">*</span></label>
                <input type="text" name="name" id="edit_name" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" required>
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

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Giá vốn (đ)</label>
                    <input type="number" name="cost_price" id="edit_cost_price" min="0" step="1000" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Định mức tồn tối thiểu</label>
                    <input type="number" name="min_stock" id="edit_min_stock" min="0" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Kho hàng</label>
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

<script>
    function editMaterial(item) {
        document.getElementById('editMaterialForm').action = `/warehouses/materials/${item.id}`;
        document.getElementById('edit_code_label').textContent = item.code;
        document.getElementById('edit_code').value = item.code;
        document.getElementById('edit_name').value = item.name || '';
        document.getElementById('edit_origin_code').value = item.origin_code || '';
        document.getElementById('edit_category').value = item.category || '';
        document.getElementById('edit_unit').value = item.unit || 'Tấm';
        document.getElementById('edit_cost_price').value = item.cost_price || 0;
        document.getElementById('edit_min_stock').value = item.min_stock || 50;
        document.getElementById('edit_warehouse_id').value = item.warehouse_id || '';
        document.getElementById('edit_status').value = item.status || 'active';
        document.getElementById('edit_notes').value = item.notes || '';

        openModal('editMaterialModal');
    }

    function openStockCardModal(materialId) {
        const container = document.getElementById('stockCardModalContent');
        container.innerHTML = `
            <div class="text-center py-8 text-neutral-400">
                <iconify-icon icon="line-md:loading-loop" class="text-3xl animate-spin mb-2"></iconify-icon>
                <p class="text-xs">Đang tải lịch sử thẻ kho...</p>
            </div>
        `;
        openModal('stockCardModal');

        fetch(`/warehouses/materials/${materialId}/stock-card`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(err => {
            container.innerHTML = `<div class="p-4 text-danger-600 text-xs">Lỗi khi tải thẻ kho: ${err}</div>`;
        });
    }
</script>

@endsection
