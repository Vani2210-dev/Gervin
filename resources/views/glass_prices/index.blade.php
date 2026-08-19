@extends('layout.layout')
@php
    $title    = 'Bảng giá kính';
    $subTitle = 'Danh sách bảng giá gia công cánh kính & phụ kiện';
@endphp

@section('content')
<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card h-full p-0 rounded-xl border-0 overflow-hidden shadow-sm bg-white">
            {{-- Header --}}
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                <div class="flex items-center flex-wrap gap-3">
                    {{-- Per page --}}
                    {{-- Search (Bên trái ngoài cùng) --}}
                    <form method="GET" action="{{ route('glass_prices.index') }}" class="navbar-search">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <input type="hidden" name="filter_category" value="{{ $filterCategory }}">
                        <div class="relative">
                            <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-base"></iconify-icon>
                            <input type="text" name="search" class="form-control form-control-sm border-neutral-200 rounded-lg pl-9 pr-3 w-64 md:w-80" placeholder="Tìm tên, màu nhôm, màu kính, mã..." value="{{ $search }}">
                        </div>
                    </form>

                    {{-- Per page --}}
                    <div class="flex items-center gap-2">
                        <span class="text-base font-medium text-secondary-light mb-0">Hiển thị</span>
                        <form method="GET" action="{{ route('glass_prices.index') }}" id="perPageForm">
                            <input type="hidden" name="search" value="{{ $search }}">
                            <input type="hidden" name="filter_category" value="{{ $filterCategory }}">
                            <select name="per_page" class="form-select form-select-sm w-auto border-neutral-200 rounded-lg"
                                onchange="document.getElementById('perPageForm').submit()">
                                @foreach([15, 25, 50, 100] as $option)
                                <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    {{-- Category Filter --}}
                    <form method="GET" action="{{ route('glass_prices.index') }}" id="categoryFilterForm">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <select name="filter_category" class="form-select form-select-sm border-neutral-200 rounded-lg max-w-xs"
                            onchange="document.getElementById('categoryFilterForm').submit()">
                            <option value="">-- Lọc theo nhóm sản phẩm --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ $filterCategory == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </form>
                    
                    @if($search || $filterCategory)
                    <a href="{{ route('glass_prices.index') }}" class="btn bg-light-100 hover:bg-neutral-200 text-sm btn-sm px-3 py-2 rounded-lg flex items-center gap-1">
                        <iconify-icon icon="solar:close-circle-outline" class="text-lg"></iconify-icon>
                        Xóa bộ lọc
                    </a>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    @can('add supply')
                    <button type="button" onclick="triggerImportExcel()"
                        class="btn bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200 text-sm btn-sm px-3 py-2 rounded-lg flex items-center gap-1.5 transition-all">
                        <iconify-icon icon="lucide:file-spreadsheet" class="text-lg"></iconify-icon>
                        Nhập Excel
                    </button>
                    <form id="importExcelForm" action="{{ route('glass_prices.import') }}" method="POST" enctype="multipart/form-data" style="display: none;">
                        @csrf
                        <input type="file" id="excelImportFileInput" name="file" accept=".xlsx,.xls" onchange="submitImportForm()">
                    </form>
                    <button type="button" onclick="openModal('create-price-modal')"
                        class="btn btn-primary text-sm btn-sm px-3 py-2 rounded-lg flex items-center gap-1.5 transition-all">
                        <iconify-icon icon="ic:baseline-plus" class="text-lg"></iconify-icon>
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
                    <table class="table bordered-table sm-table mb-0 border-collapse border border-neutral-200 text-xs min-w-[1000px]">
                        <thead class="bg-neutral-50 font-semibold text-neutral-700 text-center uppercase tracking-wider">
                            <tr>
                                <th class="border border-neutral-200 w-12 text-center">STT</th>
                                <th class="border border-neutral-200 w-24 text-center">Mã TP</th>
                                <th class="border border-neutral-200 text-left">Tên sản phẩm / Nhóm quy cách</th>
                                <th class="border border-neutral-200 w-44 text-left">Màu nhôm</th>
                                <th class="border border-neutral-200 w-44 text-left">Màu kính</th>
                                <th class="border border-neutral-200 w-16 text-center">Đơn vị</th>
                                <th class="border border-neutral-200 w-32 text-end">Đơn giá</th>
                                <th class="border border-neutral-200 text-left">Ghi chú</th>
                                <th class="border border-neutral-200 w-28 text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $currentCategoryName = null; @endphp
                            @forelse($glassPrices as $index => $item)
                                {{-- Display Category Header Row if it changes --}}
                                @if($item->category_name !== $currentCategoryName)
                                    @php $currentCategoryName = $item->category_name; @endphp
                                    <tr class="bg-emerald-50/60 font-bold">
                                        <td colspan="9" class="p-3 text-emerald-800 text-sm border border-neutral-200">
                                            <div class="flex items-center gap-1.5">
                                                <iconify-icon icon="solar:folder-open-bold" class="text-emerald-600 text-base"></iconify-icon>
                                                {{ $currentCategoryName ?: 'KHÁC / PHỤ KIỆN' }}
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                                
                                <tr class="hover:bg-neutral-50/50 transition-colors">
                                    <td class="text-center border border-neutral-200 text-neutral-500 font-medium">
                                        {{ $item->stt ?: ($glassPrices->firstItem() + $index) }}
                                    </td>
                                    <td class="text-center border border-neutral-200 font-bold text-neutral-800">
                                        {{ $item->code ?: '—' }}
                                    </td>
                                    <td class="border border-neutral-200 font-medium text-neutral-800 max-w-[300px] whitespace-normal">
                                        {!! nl2br(e($item->product_name)) !!}
                                    </td>
                                    <td class="border border-neutral-200 text-neutral-600 max-w-[150px] whitespace-normal">
                                        {!! nl2br(e($item->aluminum_color ?: '—')) !!}
                                    </td>
                                    <td class="border border-neutral-200 text-neutral-600 max-w-[150px] whitespace-normal">
                                        {!! nl2br(e($item->glass_color ?: '—')) !!}
                                    </td>
                                    <td class="text-center border border-neutral-200 font-medium text-neutral-600">
                                        {{ $item->unit ?: '—' }}
                                    </td>
                                    <td class="text-end border border-neutral-200 font-bold text-emerald-700">
                                        {{ number_format($item->price, 0, ',', '.') }}đ
                                    </td>
                                    <td class="border border-neutral-200 text-neutral-500 max-w-[200px] whitespace-normal">
                                        {!! nl2br(e($item->notes ?: '—')) !!}
                                    </td>
                                    <td class="text-center border border-neutral-200">
                                        <div class="flex items-center gap-2 justify-center">
                                            @can('edit supply')
                                            <button type="button"
                                                onclick="openEditModal({{ json_encode($item) }})"
                                                class="bg-success-100 hover:bg-success-200 text-success-600 font-medium w-8 h-8 flex justify-center items-center rounded-full transition-colors"
                                                title="Sửa">
                                                <iconify-icon icon="lucide:edit" class="text-base"></iconify-icon>
                                            </button>
                                            @endcan
                                            @can('delete supply')
                                            <form method="POST" action="{{ route('glass_prices.destroy', $item) }}"
                                                onsubmit="return confirm('Xóa dòng bảng giá kính này?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="bg-danger-100 hover:bg-danger-200 text-danger-600 font-medium w-8 h-8 flex justify-center items-center rounded-full transition-colors"
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
                                    <td colspan="9" class="text-center py-8">
                                        <p class="text-neutral-500 text-sm">Chưa có dòng bảng giá kính nào</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="flex items-center justify-between flex-wrap gap-2 mt-6">
                    <span class="text-secondary-light text-sm">
                        Hiển thị {{ $glassPrices->firstItem() ?? 0 }} đến {{ $glassPrices->lastItem() ?? 0 }}
                        trong tổng {{ $glassPrices->total() }} dòng bảng giá kính
                    </span>
                    {{ $glassPrices->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Thêm Dòng --}}
@can('add supply')
<x-modal name="create-price-modal" maxWidth="2xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50 rounded-t-xl">
        <h5 class="font-bold text-base text-neutral-800">Thêm dòng bảng giá kính mới</h5>
        <button type="button" onclick="closeModal('create-price-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('glass_prices.store') }}" method="POST">
        @csrf
        <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Nhóm sản phẩm (Category)</label>
                <input type="text" name="category_name" class="form-control rounded-lg" placeholder="Ví dụ: CÁNH KÍNH TỦ ÁO..." value="{{ old('category_name') }}">
            </div>
            
            <div class="grid grid-cols-12 gap-4">
                <div class="form-group col-span-4">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Số thứ tự (STT)</label>
                    <input type="text" name="stt" class="form-control rounded-lg text-center" placeholder="Vd: 1, 2..." value="{{ old('stt') }}">
                </div>
                <div class="form-group col-span-8">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Mã TP (Mã thành phẩm)</label>
                    <input type="text" name="code" class="form-control rounded-lg font-bold" placeholder="Ví dụ: GK01..." value="{{ old('code') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Tên sản phẩm / Mô tả quy cách <span class="text-danger-500">*</span></label>
                <textarea name="product_name" rows="3" class="form-control rounded-lg" placeholder="Nhập tên sản phẩm và quy cách kính..." required>{{ old('product_name') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Màu nhôm</label>
                    <input type="text" name="aluminum_color" class="form-control rounded-lg" placeholder="Nhập màu nhôm..." value="{{ old('aluminum_color') }}">
                </div>
                <div class="form-group">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Màu kính</label>
                    <input type="text" name="glass_color" class="form-control rounded-lg" placeholder="Nhập màu kính..." value="{{ old('glass_color') }}">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Đơn vị tính</label>
                    <input type="text" name="unit" class="form-control rounded-lg text-center" placeholder="Ví dụ: m2, cái, bộ..." value="{{ old('unit', 'm2') }}">
                </div>
                <div class="form-group">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Đơn giá (VNĐ) <span class="text-danger-500">*</span></label>
                    <input type="number" name="price" class="form-control rounded-lg text-end font-semibold text-emerald-700" placeholder="0" min="0" required value="{{ old('price') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Ghi chú</label>
                <textarea name="notes" rows="2" class="form-control rounded-lg" placeholder="Nhập ghi chú...">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Lưu</button>
            <button type="button" onclick="closeModal('create-price-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>
@endcan

{{-- Modal Sửa Dòng --}}
@can('edit supply')
<x-modal name="edit-price-modal" maxWidth="2xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50 rounded-t-xl">
        <h5 class="font-bold text-base text-neutral-800">Chỉnh sửa dòng bảng giá kính</h5>
        <button type="button" onclick="closeModal('edit-price-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form id="edit-price-form" action="" method="POST">
        @csrf @method('PUT')
        <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Nhóm sản phẩm (Category)</label>
                <input type="text" id="edit_category_name" name="category_name" class="form-control rounded-lg">
            </div>
            
            <div class="grid grid-cols-12 gap-4">
                <div class="form-group col-span-4">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Số thứ tự (STT)</label>
                    <input type="text" id="edit_stt" name="stt" class="form-control rounded-lg text-center">
                </div>
                <div class="form-group col-span-8">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Mã TP (Mã thành phẩm)</label>
                    <input type="text" id="edit_code" name="code" class="form-control rounded-lg font-bold">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Tên sản phẩm / Mô tả quy cách <span class="text-danger-500">*</span></label>
                <textarea id="edit_product_name" name="product_name" rows="3" class="form-control rounded-lg" required></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Màu nhôm</label>
                    <input type="text" id="edit_aluminum_color" name="aluminum_color" class="form-control rounded-lg">
                </div>
                <div class="form-group">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Màu kính</label>
                    <input type="text" id="edit_glass_color" name="glass_color" class="form-control rounded-lg">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Đơn vị tính</label>
                    <input type="text" id="edit_unit" name="unit" class="form-control rounded-lg text-center">
                </div>
                <div class="form-group">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Đơn giá (VNĐ) <span class="text-danger-500">*</span></label>
                    <input type="number" id="edit_price" name="price" class="form-control rounded-lg text-end font-semibold text-emerald-700" min="0" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Ghi chú</label>
                <textarea id="edit_notes" name="notes" rows="2" class="form-control rounded-lg"></textarea>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Cập nhật</button>
            <button type="button" onclick="closeModal('edit-price-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>
@endcan

<script>
    function triggerImportExcel() {
        document.getElementById('excelImportFileInput').click();
    }
    
    function submitImportForm() {
        const fileInput = document.getElementById('excelImportFileInput');
        if (fileInput.files.length > 0) {
            if (confirm('Nhập bảng giá kính từ Excel sẽ thay thế toàn bộ dữ liệu bảng giá kính hiện tại. Tiếp tục?')) {
                document.getElementById('importExcelForm').submit();
            } else {
                fileInput.value = '';
            }
        }
    }

    function openEditModal(item) {
        document.getElementById('edit-price-form').action = '/glass-prices/' + item.id;
        document.getElementById('edit_category_name').value = item.category_name || '';
        document.getElementById('edit_stt').value = item.stt || '';
        document.getElementById('edit_code').value = item.code || '';
        document.getElementById('edit_product_name').value = item.product_name || '';
        document.getElementById('edit_aluminum_color').value = item.aluminum_color || '';
        document.getElementById('edit_glass_color').value = item.glass_color || '';
        document.getElementById('edit_unit').value = item.unit || '';
        document.getElementById('edit_price').value = item.price || 0;
        document.getElementById('edit_notes').value = item.notes || '';
        
        openModal('edit-price-modal');
    }
</script>
@endsection
