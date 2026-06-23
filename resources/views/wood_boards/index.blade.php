@extends('layout.layout')
@php
    $title    = 'Bảng giá tấm';
    $subTitle = 'Danh sách bảng giá tấm';
    
    // Curated dynamic color schemes to cycle through
    $colorSchemes = [
        ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-800', 'bg_sub' => 'bg-emerald-50/50', 'text_sub' => 'text-emerald-700', 'border' => 'text-emerald-600'],
        ['bg' => 'bg-rose-50', 'text' => 'text-rose-800', 'bg_sub' => 'bg-rose-50/50', 'text_sub' => 'text-rose-700', 'border' => 'text-rose-600'],
        ['bg' => 'bg-cyan-50', 'text' => 'text-cyan-800', 'bg_sub' => 'bg-cyan-50/50', 'text_sub' => 'text-cyan-700', 'border' => 'text-cyan-600'],
        ['bg' => 'bg-purple-50', 'text' => 'text-purple-800', 'bg_sub' => 'bg-purple-50/50', 'text_sub' => 'text-purple-700', 'border' => 'text-purple-600'],
        ['bg' => 'bg-amber-50', 'text' => 'text-amber-800', 'bg_sub' => 'bg-amber-50/50', 'text_sub' => 'text-amber-700', 'border' => 'text-amber-600'],
        ['bg' => 'bg-blue-50', 'text' => 'text-blue-800', 'bg_sub' => 'bg-blue-50/50', 'text_sub' => 'text-blue-700', 'border' => 'text-blue-600'],
    ];
@endphp

@section('content')
<style>
    /* Fix sticky column overlap bug in two-row table headers */
    .table thead tr:nth-child(2) th:last-child {
        position: static !important;
        background-color: inherit !important;
        box-shadow: none !important;
    }
    .table thead tr:nth-child(2) th:last-child::before {
        display: none !important;
    }
</style>

<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card h-full p-0 rounded-xl border-0 overflow-hidden shadow-sm bg-white">
            {{-- Header --}}
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                <div class="flex items-center flex-wrap gap-3">
                    {{-- Per page --}}
                    <span class="text-base font-medium text-secondary-light mb-0">Hiển thị</span>
                    <form method="GET" action="{{ route('wood_boards.index') }}" id="perPageForm">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <select name="per_page" class="form-select form-select-sm w-auto border-neutral-200 rounded-lg"
                            onchange="document.getElementById('perPageForm').submit()">
                            @foreach([15, 25, 50, 100] as $option)
                            <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </form>

                    {{-- Search --}}
                    <form method="GET" action="{{ route('wood_boards.index') }}" class="navbar-search">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <input type="text" name="search" class="form-control form-control-sm border-neutral-200 rounded-lg" placeholder="Tìm kiếm mã màu, nhóm..." value="{{ $search }}">
                    </form>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="openModal('filter-modal')"
                        class="btn bg-light-600 text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="solar:filter-outline" class="icon text-xl line-height-1"></iconify-icon>
                        Lọc
                    </button>
                    @if(request()->filled('filter_color_code') || request()->filled('filter_price_group'))
                    <a href="{{ route('wood_boards.index') }}" class="btn text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="solar:close-circle-outline" class="icon text-xl line-height-1"></iconify-icon>
                        Xóa lọc
                    </a>
                    @endif
                    @can('add supply')
                    <button type="button" onclick="openModal('manage-types-modal')"
                        class="btn btn-outline-primary text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="solar:settings-bold" class="icon text-xl line-height-1"></iconify-icon>
                        Cấu hình Loại ván
                    </button>
                    <button type="button" onclick="openModal('manage-price-groups-modal')"
                        class="btn btn-outline-primary text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="solar:dollar-minimalistic-bold-duotone" class="icon text-xl line-height-1"></iconify-icon>
                        Cấu hình Nhóm giá
                    </button>
                    <button type="button" onclick="triggerImportExcel()"
                        class="btn bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200 text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="lucide:file-spreadsheet" class="icon text-xl line-height-1"></iconify-icon>
                        Nhập Excel
                    </button>
                    <form id="importExcelForm" action="{{ route('wood_boards.import') }}" method="POST" enctype="multipart/form-data" style="display: none;">
                        @csrf
                        <input type="file" id="excelImportFileInput" name="file" accept=".xlsx,.xls" onchange="submitImportForm()">
                    </form>
                    <button type="button" onclick="openModal('create-board-modal')"
                        class="btn btn-primary text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                        Thêm dòng bảng giá
                    </button>
                    @endcan
                </div>
            </div>

            {{-- Flash messages --}}
            @if(session('success'))
            <div class="alert alert-success bg-success-50 text-success-600 border border-success-200 rounded-lg p-4 mx-6 mt-4">
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger bg-danger-50 text-danger-600 border border-danger-200 rounded-lg p-4 mx-6 mt-4">
                {{ session('error') }}
            </div>
            @endif

            {{-- Table --}}
            <div class="card-body">
                <div class="table-responsive scroll-sm overflow-x-auto">
                    @php
                        // Estimate table width based on number of columns (5 sub-columns per type now)
                        $minWidth = 350 + (count($boardTypes) * 530);
                    @endphp
                    <table class="table bordered-table sm-table mb-0 border-collapse border border-neutral-200 text-xs" style="min-width: {{ $minWidth }}px;">
                        <thead class="bg-neutral-50 font-semibold text-neutral-700 text-center uppercase tracking-wider">
                            <tr>
                                <th rowspan="2" class="text-center align-middle border border-neutral-200 w-10">STT</th>
                                <th rowspan="2" class="text-center align-middle border border-neutral-200 w-20">Nhóm giá</th>
                                <th rowspan="2" class="text-center align-middle border border-neutral-200 w-24">Mã màu</th>
                                
                                {{-- Dynamic Headers --}}
                                @foreach($boardTypes as $idx => $type)
                                    @php $scheme = $colorSchemes[$idx % count($colorSchemes)]; @endphp
                                    <th colspan="5" class="text-center {{ $scheme['bg'] }} {{ $scheme['text'] }} border border-neutral-200">
                                        {{ $type->name }}
                                    </th>
                                @endforeach
                                
                                <th rowspan="2" class="text-center align-middle border border-neutral-200 w-28">Hành động</th>
                            </tr>
                            <tr>
                                {{-- Repeat Subheadings for each Type --}}
                                @foreach($boardTypes as $idx => $type)
                                    @php $scheme = $colorSchemes[$idx % count($colorSchemes)]; @endphp
                                    <th class="{{ $scheme['bg_sub'] }} {{ $scheme['text_sub'] }} border border-neutral-200 w-28">Mã số</th>
                                    <th class="{{ $scheme['bg_sub'] }} {{ $scheme['text_sub'] }} border border-neutral-200">Tên hàng</th>
                                    <th class="{{ $scheme['bg_sub'] }} {{ $scheme['text_sub'] }} border border-neutral-200 w-20">Độ dày</th>
                                    <th class="{{ $scheme['bg_sub'] }} {{ $scheme['text_sub'] }} text-end border border-neutral-200 w-24">Giá tấm</th>
                                    <th class="{{ $scheme['bg_sub'] }} {{ $scheme['text_sub'] }} text-end border border-neutral-200 w-24">Giá m2</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($woodBoards as $index => $board)
                            @php $stt = $woodBoards->firstItem() + $loop->index; @endphp
                            <tr class="hover:bg-neutral-50/30">
                                <td class="text-center border border-neutral-200">{{ $stt }}</td>
                                <td class="text-center font-medium border border-neutral-200">{{ $board->price_group ?? '—' }}</td>
                                <td class="text-center font-bold text-neutral-800 border border-neutral-200">{{ $board->color_code }}</td>
                                
                                {{-- Render values for each type --}}
                                @foreach($boardTypes as $idx => $type)
                                    @php 
                                        $scheme = $colorSchemes[$idx % count($colorSchemes)];
                                        $price = $board->prices->firstWhere('wood_board_type_id', $type->id);
                                    @endphp
                                    <td class="border border-neutral-200"><span class="text-neutral-500 font-medium">{{ $price->code ?? '—' }}</span></td>
                                    <td class="border border-neutral-200"><span class="text-neutral-600">{{ $price->name ?? '—' }}</span></td>
                                    <td class="border border-neutral-200"><span class="text-neutral-600 font-medium">{{ $price->thickness ?? '—' }}</span></td>
                                    <td class="text-end font-semibold {{ $scheme['border'] }} border border-neutral-200">
                                        {{ $price && $price->price_board > 0 ? number_format($price->price_board, 0, ',', '.') . 'đ' : '—' }}
                                    </td>
                                    <td class="text-end font-semibold {{ $scheme['border'] }} border border-neutral-200">
                                        {{ $price && $price->price_m2 > 0 ? number_format($price->price_m2, 0, ',', '.') . 'đ' : '—' }}
                                    </td>
                                @endforeach

                                <td class="text-center border border-neutral-200">
                                    <div class="flex items-center gap-2 justify-center">
                                        @can('edit supply')
                                        <button type="button"
                                            onclick="openEditModal({{ $board->id }}, '{{ addslashes($board->price_group) }}', '{{ addslashes($board->color_code) }}', {{ json_encode($board->prices) }})"
                                            class="bg-success-100 hover:bg-success-200 text-success-600 font-medium w-8 h-8 flex justify-center items-center rounded-full"
                                            title="Sửa">
                                            <iconify-icon icon="lucide:edit" class="text-base"></iconify-icon>
                                        </button>
                                        @endcan
                                        @can('delete supply')
                                        <form method="POST" action="{{ route('wood_boards.destroy', $board) }}"
                                            onsubmit="return confirm('Xóa dòng bảng giá này?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="bg-danger-100 hover:bg-danger-200 text-danger-600 font-medium w-8 h-8 flex justify-center items-center rounded-full"
                                                title="Xóa">
                                                <iconify-icon icon="fluent:delete-24-regular" class="text-base"></iconify-icon>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ 4 + count($boardTypes) * 5 }}" class="text-center py-8">
                                    <p class="text-neutral-500 text-sm">Chưa có dòng bảng giá nào</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="flex items-center justify-between flex-wrap gap-2 mt-6">
                    <span class="text-secondary-light text-sm">
                        Hiển thị {{ $woodBoards->firstItem() ?? 0 }} đến {{ $woodBoards->lastItem() ?? 0 }}
                        trong tổng {{ $woodBoards->total() }} dòng bảng giá
                    </span>
                    {{ $woodBoards->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Thêm Dòng --}}
@can('add supply')
<x-modal name="create-board-modal" maxWidth="2xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
        <h5 class="font-semibold text-base">Thêm dòng bảng giá mới</h5>
        <button type="button" onclick="closeModal('create-board-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('wood_boards.store') }}" method="POST">
        @csrf
        <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
            {{-- General Section --}}
            <div class="bg-neutral-50 p-4 rounded-xl border border-neutral-100">
                <h6 class="text-sm font-bold text-neutral-800 mb-3 flex items-center gap-1"><iconify-icon icon="solar:info-circle-bold" class="text-primary-500"></iconify-icon> Thông tin chung</h6>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label font-semibold text-xs text-neutral-600">Mã màu <span class="text-danger-500">*</span></label>
                        <input type="text" name="color_code" class="form-control form-control-sm rounded-lg" placeholder="Ví dụ: GV01..." required value="{{ old('color_code') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label font-semibold text-xs text-neutral-600">Nhóm giá</label>
                        <select name="price_group" class="form-select form-select-sm rounded-lg" onchange="onPriceGroupChange(this, 'create')">
                            <option value="">-- Chọn nhóm giá --</option>
                            @foreach($priceGroups as $group)
                                <option value="{{ $group->name }}" {{ old('price_group') == $group->name ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Loop through types --}}
            @foreach($boardTypes as $idx => $type)
                @php $scheme = $colorSchemes[$idx % count($colorSchemes)]; @endphp
                <div class="{{ $scheme['bg'] }}/40 p-4 rounded-xl border {{ $scheme['bg'] === 'bg-neutral-50' ? 'border-neutral-100' : str_replace('bg', 'border', $scheme['bg']) }}-100">
                    <h6 class="text-sm font-bold {{ $scheme['text'] }} mb-3 flex items-center gap-1">
                        <iconify-icon icon="solar:folder-open-bold" class="{{ $scheme['text_sub'] }}"></iconify-icon> 
                        {{ $type->name }}
                    </h6>
                    <div style="display: flex; flex-flow: row wrap; gap: 16px;">
                        <div style="flex: 2; min-width: 150px;">
                            <label class="form-label font-semibold text-xs {{ $scheme['text_sub'] }} mb-1 block">Tên hàng</label>
                            <input type="text" name="prices[{{ $type->id }}][name]" class="form-control form-control-sm rounded-lg" style="width: 100%;" placeholder="Tên chất liệu..." value="{{ $type->name }}">
                        </div>
                        <div style="width: 80px;">
                            <label class="form-label font-semibold text-xs {{ $scheme['text_sub'] }} mb-1 block">Độ dày</label>
                            <input type="text" name="prices[{{ $type->id }}][thickness]" class="form-control form-control-sm rounded-lg" style="width: 100%;" placeholder="Vd: 17mm">
                        </div>
                        <div style="flex: 1; min-width: 100px;">
                            <label class="form-label font-semibold text-xs {{ $scheme['text_sub'] }} mb-1 block">Giá tấm</label>
                            <input type="number" name="prices[{{ $type->id }}][price_board]" class="form-control form-control-sm rounded-lg" style="width: 100%;" placeholder="0" min="0">
                        </div>
                        <div style="flex: 1; min-width: 100px;">
                            <label class="form-label font-semibold text-xs {{ $scheme['text_sub'] }} mb-1 block">Giá m2</label>
                            <input type="number" name="prices[{{ $type->id }}][price_m2]" class="form-control form-control-sm rounded-lg" style="width: 100%;" placeholder="0" min="0">
                        </div>
                        <div style="width: 100%; margin-top: 4px;">
                            <span class="text-xs text-neutral-400 font-medium">Mã số tự động: <strong class="text-neutral-600">[Mã màu]{{ $type->prefix }}</strong></span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Lưu</button>
            <button type="button" onclick="closeModal('create-board-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>
@endcan

{{-- Modal Sửa Dòng --}}
@can('edit supply')
<x-modal name="edit-board-modal" maxWidth="2xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
        <h5 class="font-semibold text-base">Chỉnh sửa dòng bảng giá</h5>
        <button type="button" onclick="closeModal('edit-board-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form id="edit-board-form" action="" method="POST">
        @csrf @method('PUT')
        <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
            {{-- General Section --}}
            <div class="bg-neutral-50 p-4 rounded-xl border border-neutral-100">
                <h6 class="text-sm font-bold text-neutral-800 mb-3 flex items-center gap-1"><iconify-icon icon="solar:info-circle-bold" class="text-primary-500"></iconify-icon> Thông tin chung</h6>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label font-semibold text-xs text-neutral-600">Mã màu <span class="text-danger-500">*</span></label>
                        <input type="text" id="edit_color_code" name="color_code" class="form-control form-control-sm rounded-lg" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label font-semibold text-xs text-neutral-600">Nhóm giá</label>
                        <select id="edit_price_group" name="price_group" class="form-select form-select-sm rounded-lg" onchange="onPriceGroupChange(this, 'edit')">
                            <option value="">-- Chọn nhóm giá --</option>
                            @foreach($priceGroups as $group)
                                <option value="{{ $group->name }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Loop through types for Edit inputs --}}
            @foreach($boardTypes as $idx => $type)
                @php $scheme = $colorSchemes[$idx % count($colorSchemes)]; @endphp
                <div class="{{ $scheme['bg'] }}/40 p-4 rounded-xl border {{ $scheme['bg'] === 'bg-neutral-50' ? 'border-neutral-100' : str_replace('bg', 'border', $scheme['bg']) }}-100">
                    <h6 class="text-sm font-bold {{ $scheme['text'] }} mb-3 flex items-center gap-1">
                        <iconify-icon icon="solar:folder-open-bold" class="{{ $scheme['text_sub'] }}"></iconify-icon> 
                        {{ $type->name }}
                    </h6>
                    <div style="display: flex; flex-flow: row wrap; gap: 16px;">
                        <div style="flex: 2; min-width: 150px;">
                            <label class="form-label font-semibold text-xs {{ $scheme['text_sub'] }} mb-1 block">Tên hàng</label>
                            <input type="text" id="edit_price_name_{{ $type->id }}" name="prices[{{ $type->id }}][name]" class="form-control form-control-sm rounded-lg" style="width: 100%;">
                        </div>
                        <div style="width: 80px;">
                            <label class="form-label font-semibold text-xs {{ $scheme['text_sub'] }} mb-1 block">Độ dày</label>
                            <input type="text" id="edit_price_thickness_{{ $type->id }}" name="prices[{{ $type->id }}][thickness]" class="form-control form-control-sm rounded-lg" style="width: 100%;" placeholder="Vd: 17mm">
                        </div>
                        <div style="flex: 1; min-width: 100px;">
                            <label class="form-label font-semibold text-xs {{ $scheme['text_sub'] }} mb-1 block">Giá tấm</label>
                            <input type="number" id="edit_price_board_{{ $type->id }}" name="prices[{{ $type->id }}][price_board]" class="form-control form-control-sm rounded-lg" style="width: 100%;" min="0">
                        </div>
                        <div style="flex: 1; min-width: 100px;">
                            <label class="form-label font-semibold text-xs {{ $scheme['text_sub'] }} mb-1 block">Giá m2</label>
                            <input type="number" id="edit_price_m2_{{ $type->id }}" name="prices[{{ $type->id }}][price_m2]" class="form-control form-control-sm rounded-lg" style="width: 100%;" min="0">
                        </div>
                        <div style="width: 100%; margin-top: 4px;">
                            <span class="text-xs text-neutral-400 font-medium">Mã số tự động: <strong class="text-neutral-600">[Mã màu]{{ $type->prefix }}</strong></span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Cập nhật</button>
            <button type="button" onclick="closeModal('edit-board-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>

<script>
function openEditModal(id, priceGroup, colorCode, prices) {
    document.getElementById('edit-board-form').action = '/wood-boards/' + id;
    document.getElementById('edit_price_group').value = priceGroup || '';
    document.getElementById('edit_color_code').value = colorCode || '';
    
    // Clear all inputs first
    @foreach($boardTypes as $type)
        if(document.getElementById('edit_price_name_{{ $type->id }}')) document.getElementById('edit_price_name_{{ $type->id }}').value = '';
        if(document.getElementById('edit_price_thickness_{{ $type->id }}')) document.getElementById('edit_price_thickness_{{ $type->id }}').value = '';
        if(document.getElementById('edit_price_board_{{ $type->id }}')) document.getElementById('edit_price_board_{{ $type->id }}').value = '';
        if(document.getElementById('edit_price_m2_{{ $type->id }}')) document.getElementById('edit_price_m2_{{ $type->id }}').value = '';
    @endforeach

    // Populate existing prices
    prices.forEach(p => {
        let nameEl      = document.getElementById('edit_price_name_' + p.wood_board_type_id);
        let thicknessEl = document.getElementById('edit_price_thickness_' + p.wood_board_type_id);
        let boardEl     = document.getElementById('edit_price_board_' + p.wood_board_type_id);
        let m2El        = document.getElementById('edit_price_m2_' + p.wood_board_type_id);

        if (nameEl)      nameEl.value      = p.name || '';
        if (thicknessEl) thicknessEl.value = p.thickness || '';
        if (boardEl)     boardEl.value     = p.price_board ? parseInt(p.price_board) : '';
        if (m2El)        m2El.value        = p.price_m2 ? parseInt(p.price_m2) : '';
    });

    openModal('edit-board-modal');
}
</script>
@endcan

{{-- Modal Lọc --}}
<x-modal name="filter-modal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
        <h5 class="font-semibold text-base">Lọc bảng giá</h5>
        <button type="button" onclick="closeModal('filter-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('wood_boards.index') }}" method="GET">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <input type="hidden" name="search" value="{{ $search }}">
        <div class="p-6 grid grid-cols-1 gap-4">
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Nhóm giá</label>
                <input type="text" name="filter_price_group" class="form-control rounded-lg" placeholder="Nhập nhóm giá..." value="{{ request('filter_price_group') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Mã màu</label>
                <input type="text" name="filter_color_code" class="form-control rounded-lg" placeholder="Nhập mã màu..." value="{{ request('filter_color_code') }}">
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Áp dụng lọc</button>
            <button type="button" onclick="closeModal('filter-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>

{{-- Modal Cấu hình loại ván --}}
@can('add supply')
<x-modal name="manage-types-modal" maxWidth="2xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50 rounded-t-xl">
        <h5 class="font-bold text-base text-neutral-800">Cấu hình Loại ván</h5>
        <button type="button" onclick="closeModal('manage-types-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    
    <form action="{{ route('wood_board_types.batch_update') }}" method="POST">
        @csrf
        <div class="p-6 space-y-4 max-h-[65vh] overflow-y-auto">
            <div>
                <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                    <h6 class="text-sm font-bold text-neutral-800 mb-0">Danh sách loại ván</h6>
                    <button type="button" onclick="addNewTypeRow()" class="btn btn-outline-primary btn-sm px-3 py-1.5 rounded-lg text-xs flex items-center gap-1 font-semibold">
                        <iconify-icon icon="ic:baseline-plus" class="text-sm"></iconify-icon> Thêm dòng loại ván mới
                    </button>
                </div>
                
                <div id="typesContainer" class="space-y-3">
                    @foreach($boardTypes as $type)
                        <div class="p-3 bg-neutral-50 rounded-xl border border-neutral-200" id="type_row_{{ $type->id }}">
                            <div style="display: flex; flex-flow: row wrap; gap: 12px; align-items: center;">
                                <div style="flex: 1; min-width: 150px;">
                                    <label class="text-[10px] text-neutral-500 font-semibold mb-0.5 block">Tên loại ván <span class="text-danger-500">*</span></label>
                                    <input type="text" name="types[{{ $type->id }}][name]" value="{{ $type->name }}" class="form-control form-control-sm rounded-lg" style="width: 100%;" required>
                                </div>
                                <div style="width: 120px;">
                                    <label class="text-[10px] text-neutral-500 font-semibold mb-0.5 block">Tiền tố mã số</label>
                                    <input type="text" name="types[{{ $type->id }}][prefix]" value="{{ $type->prefix }}" class="form-control form-control-sm rounded-lg" style="width: 100%;" placeholder="Vd: .TP.2M">
                                </div>
                                <div style="width: 64px;">
                                    <label class="text-[10px] text-neutral-500 font-semibold mb-0.5 block">Thứ tự</label>
                                    <input type="number" name="types[{{ $type->id }}][display_order]" value="{{ $type->display_order }}" class="form-control form-control-sm rounded-lg" style="width: 100%;" required>
                                </div>
                                @can('delete supply')
                                <div style="margin-top: 14px;">
                                    <button type="button" onclick="deleteType({{ $type->id }}, '{{ addslashes($type->name) }}')" class="btn btn-danger btn-sm p-1.5 rounded-lg text-xs" title="Xóa">
                                        <iconify-icon icon="fluent:delete-24-regular" class="text-sm"></iconify-icon>
                                    </button>
                                </div>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex justify-between gap-3 rounded-b-xl bg-neutral-50">
            <button type="button" onclick="closeModal('manage-types-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg text-xs">Hủy</button>
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg text-xs flex items-center gap-1.5 shadow-sm font-semibold">
                <iconify-icon icon="solar:diskette-outline" class="text-base"></iconify-icon> Lưu tất cả thay đổi
            </button>
        </div>
    </form>
</x-modal>

{{-- Modal Cấu hình Nhóm giá --}}
<x-modal name="manage-price-groups-modal" maxWidth="3xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50 rounded-t-xl">
        <h5 class="font-bold text-base text-neutral-800">Cấu hình Nhóm giá mặc định</h5>
        <button type="button" onclick="closeModal('manage-price-groups-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    
    <form action="{{ route('wood_board_price_groups.batch_update') }}" method="POST">
        @csrf
        <div class="p-6 space-y-4 max-h-[65vh] overflow-y-auto">
            <div>
                <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                    <h6 class="text-sm font-bold text-neutral-800 mb-0">Danh sách nhóm giá</h6>
                    <button type="button" onclick="addNewPriceGroupRow()" class="btn btn-outline-primary btn-sm px-3 py-1.5 rounded-lg text-xs flex items-center gap-1 font-semibold">
                        <iconify-icon icon="ic:baseline-plus" class="text-sm"></iconify-icon> Thêm nhóm giá mới
                    </button>
                </div>
                
                <div id="priceGroupsContainer" class="space-y-4">
                    @foreach($priceGroups as $group)
                        <div class="p-4 bg-neutral-50 rounded-xl border border-neutral-200" id="group_row_{{ $group->id }}">
                            <div class="flex items-center justify-between border-b border-neutral-200 pb-2 mb-3">
                                <div style="display: flex; flex-flow: row wrap; gap: 8px; align-items: center; flex: 1;">
                                    <label class="text-xs font-bold text-neutral-700">Tên Nhóm Giá <span class="text-danger-500">*</span></label>
                                    <input type="text" name="groups[{{ $group->id }}][name]" value="{{ $group->name }}" class="form-control form-control-sm rounded-lg" style="max-width: 200px;" required placeholder="Ví dụ: 1">
                                </div>
                                <button type="button" onclick="deletePriceGroup({{ $group->id }}, '{{ addslashes($group->name) }}')" class="btn btn-danger btn-sm px-2 py-1 rounded-lg text-xs flex items-center gap-1" title="Xóa nhóm này">
                                    <iconify-icon icon="fluent:delete-24-regular" class="text-sm"></iconify-icon> Xóa nhóm
                                </button>
                            </div>
                            
                            <div class="space-y-3">
                                @foreach($boardTypes as $type)
                                    @php 
                                        $groupPrice = $group->prices->firstWhere('wood_board_type_id', $type->id);
                                    @endphp
                                    <div class="p-2.5 bg-white rounded-lg border border-neutral-100">
                                        <span class="text-xs font-bold text-primary-600 block mb-1.5">{{ $type->name }}</span>
                                        <div style="display: flex; flex-flow: row wrap; gap: 12px; align-items: center;">
                                            <div style="flex: 2; min-width: 140px;">
                                                <label class="text-[9px] text-neutral-400 font-semibold mb-0.5 block">Tên hàng</label>
                                                <input type="text" name="groups[{{ $group->id }}][prices][{{ $type->id }}][name]" value="{{ $groupPrice->name ?? $type->name }}" class="form-control form-control-sm rounded-lg" placeholder="Tên hàng">
                                            </div>
                                            <div style="width: 80px;">
                                                <label class="text-[9px] text-neutral-400 font-semibold mb-0.5 block">Độ dày</label>
                                                <input type="text" name="groups[{{ $group->id }}][prices][{{ $type->id }}][thickness]" value="{{ $groupPrice->thickness ?? '' }}" class="form-control form-control-sm rounded-lg" placeholder="Độ dày">
                                            </div>
                                            <div style="flex: 1; min-width: 100px;">
                                                <label class="text-[9px] text-neutral-400 font-semibold mb-0.5 block">Giá tấm</label>
                                                <input type="number" name="groups[{{ $group->id }}][prices][{{ $type->id }}][price_board]" value="{{ $groupPrice ? intval($groupPrice->price_board) : 0 }}" class="form-control form-control-sm rounded-lg" placeholder="Giá tấm" min="0">
                                            </div>
                                            <div style="flex: 1; min-width: 100px;">
                                                <label class="text-[9px] text-neutral-400 font-semibold mb-0.5 block">Giá m2</label>
                                                <input type="number" name="groups[{{ $group->id }}][prices][{{ $type->id }}][price_m2]" value="{{ $groupPrice ? intval($groupPrice->price_m2) : 0 }}" class="form-control form-control-sm rounded-lg" placeholder="Giá m2" min="0">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex justify-between gap-3 rounded-b-xl bg-neutral-50">
            <button type="button" onclick="closeModal('manage-price-groups-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg text-xs">Hủy</button>
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg text-xs flex items-center gap-1.5 shadow-sm font-semibold">
                <iconify-icon icon="solar:diskette-outline" class="text-base"></iconify-icon> Lưu tất cả thay đổi
            </button>
        </div>
    </form>
</x-modal>

<script>
let newRowIndex = 0;
let initialCount = {{ count($boardTypes) }};

const priceGroupsData = @json($priceGroups);

function onPriceGroupChange(selectEl, mode) {
    const selectedGroupName = selectEl.value;
    if (!selectedGroupName) return;
    
    const group = priceGroupsData.find(g => g.name == selectedGroupName);
    if (!group || !group.prices) return;
    
    group.prices.forEach(p => {
        let nameInput, thicknessInput, boardInput, m2Input;
        
        if (mode === 'create') {
            nameInput      = document.querySelector(`input[name="prices[${p.wood_board_type_id}][name]"]`);
            thicknessInput = document.querySelector(`input[name="prices[${p.wood_board_type_id}][thickness]"]`);
            boardInput     = document.querySelector(`input[name="prices[${p.wood_board_type_id}][price_board]"]`);
            m2Input        = document.querySelector(`input[name="prices[${p.wood_board_type_id}][price_m2]"]`);
        } else {
            nameInput      = document.getElementById(`edit_price_name_${p.wood_board_type_id}`);
            thicknessInput = document.getElementById(`edit_price_thickness_${p.wood_board_type_id}`);
            boardInput     = document.getElementById(`edit_price_board_${p.wood_board_type_id}`);
            m2Input        = document.getElementById(`edit_price_m2_${p.wood_board_type_id}`);
        }
        
        if (nameInput)      nameInput.value      = p.name || '';
        if (thicknessInput) thicknessInput.value = p.thickness || '';
        if (boardInput)     boardInput.value     = p.price_board ? parseInt(p.price_board) : 0;
        if (m2Input)        m2Input.value        = p.price_m2 ? parseInt(p.price_m2) : 0;
        
        [nameInput, thicknessInput, boardInput, m2Input].forEach(input => {
            if (input) {
                input.style.transition = 'background-color 0.4s ease';
                input.style.backgroundColor = '#ecfdf5'; // light emerald green
                setTimeout(() => {
                    input.style.backgroundColor = '';
                }, 850);
            }
        });
    });
}

function addNewTypeRow() {
    newRowIndex++;
    const container = document.getElementById('typesContainer');
    const div = document.createElement('div');
    div.className = 'p-3 bg-primary-50/20 rounded-xl border border-primary-200/50';
    div.id = 'new_type_row_' + newRowIndex;
    
    div.innerHTML = `
        <div style="display: flex; flex-flow: row wrap; gap: 12px; align-items: center;">
            <div style="flex: 1; min-width: 150px;">
                <label class="text-[10px] text-primary-600 font-semibold mb-0.5 block">Tên loại ván mới <span class="text-danger-500">*</span></label>
                <input type="text" name="new_types[${newRowIndex}][name]" class="form-control form-control-sm rounded-lg" style="width: 100%;" placeholder="Ví dụ: MDF Veneer..." required>
            </div>
            <div style="width: 120px;">
                <label class="text-[10px] text-primary-600 font-semibold mb-0.5 block">Tiền tố mã số</label>
                <input type="text" name="new_types[${newRowIndex}][prefix]" class="form-control form-control-sm rounded-lg" style="width: 100%;" placeholder="Ví dụ: .VN">
            </div>
            <div style="width: 64px;">
                <label class="text-[10px] text-primary-600 font-semibold mb-0.5 block">Thứ tự</label>
                <input type="number" name="new_types[${newRowIndex}][display_order]" value="${initialCount + newRowIndex}" class="form-control form-control-sm rounded-lg" style="width: 100%;" required>
            </div>
            <div style="margin-top: 14px;">
                <button type="button" onclick="removeNewTypeRow(${newRowIndex})" class="btn btn-neutral btn-sm p-1.5 rounded-lg text-xs" title="Hủy dòng này">
                    <iconify-icon icon="lucide:x" class="text-sm"></iconify-icon>
                </button>
            </div>
        </div>
    `;
    container.appendChild(div);
}

function removeNewTypeRow(index) {
    const el = document.getElementById('new_type_row_' + index);
    if (el) {
        el.remove();
    }
}

function deleteType(id, name) {
    if (confirm('Xóa loại ván "' + name + '" sẽ xóa tất cả giá tương ứng trong hệ thống khi bạn lưu thay đổi. Tiếp tục?')) {
        const row = document.getElementById('type_row_' + id);
        if (row) {
            row.querySelectorAll('input, select, textarea').forEach(el => {
                el.disabled = true;
            });
            row.style.display = 'none';
            
            const form = row.closest('form');
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'deleted_types[]';
            hiddenInput.value = id;
            form.appendChild(hiddenInput);
        }
    }
}

let newGroupIndex = 0;
function addNewPriceGroupRow() {
    newGroupIndex++;
    const container = document.getElementById('priceGroupsContainer');
    const div = document.createElement('div');
    div.className = 'p-4 bg-primary-50/10 rounded-xl border border-primary-200/50';
    div.id = 'new_group_row_' + newGroupIndex;
    
    let typesHtml = '';
    const activeTypes = @json($boardTypes);
    activeTypes.forEach(type => {
        typesHtml += `
            <div class="p-2.5 bg-white rounded-lg border border-neutral-100">
                <span class="text-xs font-bold text-primary-600 block mb-1.5">${type.name}</span>
                <div style="display: flex; flex-flow: row wrap; gap: 12px; align-items: center;">
                    <div style="flex: 2; min-width: 140px;">
                        <label class="text-[9px] text-neutral-400 font-semibold mb-0.5 block">Tên hàng</label>
                        <input type="text" name="new_groups[${newGroupIndex}][prices][${type.id}][name]" value="${type.name || ''}" class="form-control form-control-sm rounded-lg" placeholder="Tên hàng">
                    </div>
                    <div style="width: 80px;">
                        <label class="text-[9px] text-neutral-400 font-semibold mb-0.5 block">Độ dày</label>
                        <input type="text" name="new_groups[${newGroupIndex}][prices][${type.id}][thickness]" class="form-control form-control-sm rounded-lg" placeholder="Độ dày">
                    </div>
                    <div style="flex: 1; min-width: 100px;">
                        <label class="text-[9px] text-neutral-400 font-semibold mb-0.5 block">Giá tấm</label>
                        <input type="number" name="new_groups[${newGroupIndex}][prices][${type.id}][price_board]" value="0" class="form-control form-control-sm rounded-lg" placeholder="Giá tấm" min="0">
                    </div>
                    <div style="flex: 1; min-width: 100px;">
                        <label class="text-[9px] text-neutral-400 font-semibold mb-0.5 block">Giá m2</label>
                        <input type="number" name="new_groups[${newGroupIndex}][prices][${type.id}][price_m2]" value="0" class="form-control form-control-sm rounded-lg" placeholder="Giá m2" min="0">
                    </div>
                </div>
            </div>
        `;
    });

    div.innerHTML = `
        <div class="flex items-center justify-between border-b border-neutral-200 pb-2 mb-3">
            <div style="display: flex; flex-flow: row wrap; gap: 8px; align-items: center; flex: 1;">
                <label class="text-xs font-bold text-primary-700">Tên Nhóm Mới <span class="text-danger-500">*</span></label>
                <input type="text" name="new_groups[${newGroupIndex}][name]" class="form-control form-control-sm rounded-lg" style="max-width: 200px;" required placeholder="Ví dụ: 3">
            </div>
            <button type="button" onclick="removeNewPriceGroupRow(${newGroupIndex})" class="btn btn-neutral btn-sm px-2 py-1 rounded-lg text-xs flex items-center gap-1" title="Hủy nhóm này">
                <iconify-icon icon="lucide:x" class="text-sm"></iconify-icon> Hủy
            </button>
        </div>
        
        <div class="space-y-3">
            ${typesHtml}
        </div>
    `;
    
    container.appendChild(div);
}

function removeNewPriceGroupRow(index) {
    const el = document.getElementById('new_group_row_' + index);
    if (el) {
        el.remove();
    }
}

function deletePriceGroup(id, name) {
    if (confirm('Xóa nhóm giá "' + name + '" sẽ xóa cấu hình mặc định tương ứng khi bạn lưu thay đổi. Tiếp tục?')) {
        const row = document.getElementById('group_row_' + id);
        if (row) {
            row.querySelectorAll('input, select, textarea').forEach(el => {
                el.disabled = true;
            });
            row.style.display = 'none';
            
            const form = row.closest('form');
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'deleted_groups[]';
            hiddenInput.value = id;
            form.appendChild(hiddenInput);
        }
    }
}

function triggerImportExcel() {
    document.getElementById('excelImportFileInput').click();
}
function submitImportForm() {
    const fileInput = document.getElementById('excelImportFileInput');
    if (fileInput.files.length > 0) {
        document.getElementById('importExcelForm').submit();
    }
}
</script>
@endcan

@endsection
