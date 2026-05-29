@extends('layout.layout')
@php
    $title    = 'Vật tư';
    $subTitle = 'Danh sách vật tư';
@endphp

@section('content')

<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card h-full p-0 rounded-xl border-0 overflow-hidden">

            {{-- Header --}}
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                <div class="flex items-center flex-wrap gap-3">
                    {{-- Per page --}}
                    <span class="text-base font-medium text-secondary-light mb-0">Hiển thị</span>
                    <form method="GET" action="{{ route('supplies.index') }}" id="perPageForm">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <select name="per_page" class="form-select form-select-sm w-auto border-neutral-200 rounded-lg"
                            onchange="document.getElementById('perPageForm').submit()">
                            @foreach([10, 25, 50, 100] as $n)
                            <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                            @endforeach
                        </select>
                    </form>

                    {{-- Tìm kiếm --}}
                    <form method="GET" action="{{ route('supplies.index') }}" class="navbar-search">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <input type="text" class="bg-white h-10 w-auto" name="search"
                            value="{{ $search }}" placeholder="Tìm tên, phân loại...">
                        <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                    </form>
                </div>

                <div class="flex items-center gap-2">
                    @can('add supply')
                    <button type="button" onclick="openModal('create-supply-modal')"
                        class="btn btn-primary text-sm btn-sm px-3 py-3 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                        Thêm vật tư
                    </button>
                    @endcan
                </div>
            </div>

            {{-- Flash --}}
            @if(session('success'))
            <div class="mx-6 mt-4 bg-success-100 border border-success-300 text-success-700 rounded-lg px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
            @endif
            @if($errors->any())
            <div class="mx-6 mt-4 bg-danger-100 border border-danger-300 text-danger-700 rounded-lg px-4 py-3 text-sm">
                @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
            @endif

            {{-- Table --}}
            <div class="card-body p-6">
                <div class="table-responsive scroll-sm">
                    <table class="table bordered-table sm-table mb-0">
                        <thead>
                            <tr>
                                <th scope="col">STT</th>
                                <th scope="col">Tên vật tư</th>
                                <th scope="col">Phân loại</th>
                                <th scope="col">Đơn vị tính</th>
                                <th scope="col" class="text-end">Số lượng tồn</th>
                                <th scope="col" class="text-end">Tồn tối thiểu</th>
                                <th scope="col" class="text-end">Đơn giá (VNĐ)</th>
                                <th scope="col" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supplies as $index => $s)
                            @php $stt = $supplies->firstItem() + $loop->index; @endphp
                            <tr>
                                <td>{{ $stt }}</td>
                                <td>
                                    <span class="text-base font-medium text-secondary-light">{{ $s->name }}</span>
                                </td>
                                <td>
                                    @if($s->category)
                                        <span class="bg-primary-100 text-primary-600 border border-primary-600 px-3 py-1 rounded font-medium text-xs">{{ $s->category }}</span>
                                    @else
                                        <span class="text-neutral-400">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-base text-secondary-light">{{ $s->unit ?? '—' }}</span>
                                </td>
                                <td class="text-end">
                                    @php $low = $s->stock_quantity <= $s->min_stock && $s->min_stock > 0; @endphp
                                    <span class="font-medium {{ $low ? 'text-danger-600' : 'text-secondary-light' }}">
                                        {{ number_format($s->stock_quantity, 2, ',', '.') }}
                                    </span>
                                    @if($low)
                                        <iconify-icon icon="mdi:alert-circle-outline" class="text-danger-500 text-base align-middle" title="Dưới mức tồn tối thiểu"></iconify-icon>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="text-base text-secondary-light">{{ number_format($s->min_stock, 2, ',', '.') }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="text-base font-medium text-secondary-light">{{ number_format($s->unit_price, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center gap-3 justify-center">
                                        @can('edit supply')
                                        <button type="button"
                                            onclick="openEditModal({{ $s->id }}, '{{ addslashes($s->name) }}', '{{ addslashes($s->category) }}', '{{ addslashes($s->unit) }}', {{ $s->stock_quantity }}, {{ $s->min_stock }}, {{ $s->unit_price }})"
                                            class="bg-success-100 hover:bg-success-200 text-success-600 font-medium w-10 h-10 flex justify-center items-center rounded-full">
                                            <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                        </button>
                                        @endcan
                                        @can('delete supply')
                                        <form method="POST" action="{{ route('supplies.destroy', $s) }}"
                                            onsubmit="return confirm('Xóa vật tư này?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="bg-danger-100 hover:bg-danger-200 text-danger-600 font-medium w-10 h-10 flex justify-center items-center rounded-full">
                                                <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-8">
                                    <p class="text-neutral-500">Chưa có vật tư nào</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="flex items-center justify-between flex-wrap gap-2 mt-6">
                    <span class="text-secondary-light text-sm">
                        Hiển thị {{ $supplies->firstItem() ?? 0 }} đến {{ $supplies->lastItem() ?? 0 }}
                        trong tổng {{ $supplies->total() }} vật tư
                    </span>
                    @if($supplies->hasPages())
                    <ul class="pagination flex flex-wrap items-center gap-2 justify-center">
                        <li class="page-item {{ $supplies->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link bg-neutral-300 text-secondary-light font-semibold rounded-lg border-0 flex items-center justify-center h-8 w-8 text-base"
                                href="{{ $supplies->previousPageUrl() }}">
                                <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                            </a>
                        </li>
                        @foreach($supplies->getUrlRange(1, $supplies->lastPage()) as $page => $url)
                        <li class="page-item">
                            <a class="page-link font-semibold rounded-lg border-0 flex items-center justify-center h-8 w-8 text-base {{ $page == $supplies->currentPage() ? 'bg-primary-600 text-white' : 'bg-neutral-300 text-secondary-light' }}"
                                href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$supplies->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link bg-neutral-300 text-secondary-light font-semibold rounded-lg border-0 flex items-center justify-center h-8 w-8 text-base"
                                href="{{ $supplies->nextPageUrl() }}">
                                <iconify-icon icon="ep:d-arrow-right"></iconify-icon>
                            </a>
                        </li>
                    </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Thêm vật tư --}}
@can('add supply')
<x-modal name="create-supply-modal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
        <h5 class="font-semibold text-base">Thêm vật tư mới</h5>
        <button type="button" onclick="closeModal('create-supply-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('supplies.store') }}" method="POST">
        @csrf
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Tên vật tư <span class="text-danger-500">*</span></label>
                <input type="text" name="name" class="form-control rounded-lg" placeholder="Nhập tên vật tư" required value="{{ old('name') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Phân loại</label>
                <input type="text" name="category" class="form-control rounded-lg" placeholder="VD: Điện, Cơ khí..." value="{{ old('category') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Đơn vị tính</label>
                <input type="text" name="unit" class="form-control rounded-lg" placeholder="VD: cái, kg, m..." value="{{ old('unit') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Số lượng tồn</label>
                <input type="number" name="stock_quantity" class="form-control rounded-lg" placeholder="0" min="0" step="0.01" value="{{ old('stock_quantity', 0) }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Tồn tối thiểu</label>
                <input type="number" name="min_stock" class="form-control rounded-lg" placeholder="0" min="0" step="0.01" value="{{ old('min_stock', 0) }}">
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Đơn giá (VNĐ)</label>
                <input type="number" name="unit_price" class="form-control rounded-lg" placeholder="0" min="0" step="1000" value="{{ old('unit_price', 0) }}">
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Lưu</button>
            <button type="button" onclick="closeModal('create-supply-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>
@endcan

{{-- Modal Sửa vật tư --}}
@can('edit supply')
<x-modal name="edit-supply-modal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
        <h5 class="font-semibold text-base">Chỉnh sửa vật tư</h5>
        <button type="button" onclick="closeModal('edit-supply-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form id="edit-supply-form" action="" method="POST">
        @csrf @method('PUT')
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Tên vật tư <span class="text-danger-500">*</span></label>
                <input type="text" id="edit_name" name="name" class="form-control rounded-lg" placeholder="Nhập tên vật tư" required>
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Phân loại</label>
                <input type="text" id="edit_category" name="category" class="form-control rounded-lg" placeholder="VD: Điện, Cơ khí...">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Đơn vị tính</label>
                <input type="text" id="edit_unit" name="unit" class="form-control rounded-lg" placeholder="VD: cái, kg, m...">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Số lượng tồn</label>
                <input type="number" id="edit_stock_quantity" name="stock_quantity" class="form-control rounded-lg" min="0" step="0.01">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Tồn tối thiểu</label>
                <input type="number" id="edit_min_stock" name="min_stock" class="form-control rounded-lg" min="0" step="0.01">
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Đơn giá (VNĐ)</label>
                <input type="number" id="edit_unit_price" name="unit_price" class="form-control rounded-lg" min="0" step="1000">
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Cập nhật</button>
            <button type="button" onclick="closeModal('edit-supply-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>

<script>
function openEditModal(id, name, category, unit, stock, minStock, price) {
    document.getElementById('edit-supply-form').action = '/supplies/' + id;
    document.getElementById('edit_name').value           = name;
    document.getElementById('edit_category').value       = category;
    document.getElementById('edit_unit').value           = unit;
    document.getElementById('edit_stock_quantity').value = stock;
    document.getElementById('edit_min_stock').value      = minStock;
    document.getElementById('edit_unit_price').value     = price;
    openModal('edit-supply-modal');
}
</script>
@endcan

@endsection
