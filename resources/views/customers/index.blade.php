@extends('layout.layout')
@php
    $title    = 'Khách hàng';
    $subTitle = 'Danh sách khách hàng';
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
                    <form method="GET" action="{{ route('customers.index') }}" id="perPageForm">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <select name="per_page" class="form-select form-select-sm w-auto border-neutral-200 rounded-lg"
                            onchange="document.getElementById('perPageForm').submit()">
                            @foreach([10, 25, 50, 100] as $option)
                            <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </form>

                    {{-- Search --}}
                    <form method="GET" action="{{ route('customers.index') }}" class="navbar-search">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <input type="text" name="search" class="form-control form-control-sm border-neutral-200 rounded-lg" placeholder="Tìm kiếm..." value="{{ $search }}">
                    </form>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="openModal('filter-modal')"
                        class="btn bg-light-600 text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="solar:filter-outline" class="icon text-xl line-height-1"></iconify-icon>
                        Lọc
                    </button>
                    @if(request()->filled('filter_customer_code') || request()->filled('filter_name') || request()->filled('filter_phone'))
                    <a href="{{ route('customers.index') }}" class="btn text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="solar:close-circle-outline" class="icon text-xl line-height-1"></iconify-icon>
                        Xóa lọc
                    </a>
                    @endif
                    @can('add customer')
                    <button type="button" onclick="openModal('create-customer-modal')"
                        class="btn btn-primary text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                        Thêm khách hàng
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
                <div class="table-responsive scroll-sm">
                    <table class="table bordered-table sm-table mb-0">
                        <thead>
                            <tr>
                                <th scope="col">STT</th>
                                <th scope="col">Mã khách hàng</th>
                                <th scope="col">Tên khách hàng</th>
                                <th scope="col">Số điện thoại</th>
                                <th scope="col">Địa chỉ</th>
                                <th scope="col" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $index => $c)
                            @php $stt = $customers->firstItem() + $loop->index; @endphp
                            <tr>
                                <td>{{ $stt }}</td>
                                <td>
                                    <span class="text-base font-medium text-secondary-light">{{ $c->customer_code }}</span>
                                </td>
                                <td>
                                    <span class="text-base font-medium text-secondary-light">{{ $c->name }}</span>
                                </td>
                                <td>
                                    <span class="text-base text-secondary-light">{{ $c->phone ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="text-base text-secondary-light">{{ $c->address ?? '—' }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center gap-3 justify-center">
                                        @can('edit customer')
                                        <button type="button"
                                            onclick="openEditModal({{ $c->id }}, '{{ addslashes($c->customer_code) }}', '{{ addslashes($c->name) }}', '{{ addslashes($c->phone) }}', '{{ addslashes($c->address) }}')"
                                            class="bg-success-100 hover:bg-success-200 text-success-600 font-medium w-10 h-10 flex justify-center items-center rounded-full">
                                            <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                        </button>
                                        @endcan
                                        @can('delete customer')
                                        <form method="POST" action="{{ route('customers.destroy', $c) }}"
                                            onsubmit="return confirm('Xóa khách hàng này?')">
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
                                <td colspan="6" class="text-center py-8">
                                    <p class="text-neutral-500">Chưa có khách hàng nào</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="flex items-center justify-between flex-wrap gap-2 mt-6">
                    <span class="text-secondary-light text-sm">
                        Hiển thị {{ $customers->firstItem() ?? 0 }} đến {{ $customers->lastItem() ?? 0 }}
                        trong tổng {{ $customers->total() }} khách hàng
                    </span>
                    @if($customers->hasPages())
                    <ul class="pagination flex flex-wrap items-center gap-2 justify-center">
                        <li class="page-item {{ $customers->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link bg-neutral-300 text-secondary-light font-semibold rounded-lg border-0 flex items-center justify-center h-8 w-8 text-base"
                                href="{{ $customers->previousPageUrl() }}">
                                <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                            </a>
                        </li>
                        @foreach($customers->getUrlRange(1, $customers->lastPage()) as $page => $url)
                        <li class="page-item">
                            <a class="page-link font-semibold rounded-lg border-0 flex items-center justify-center h-8 w-8 text-base {{ $page == $customers->currentPage() ? 'bg-primary-600 text-white' : 'bg-neutral-300 text-secondary-light' }}"
                                href="{{ $url }}">{{ $page }}</a>
                        </li>
                        @endforeach
                        <li class="page-item {{ !$customers->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link bg-neutral-300 text-secondary-light font-semibold rounded-lg border-0 flex items-center justify-center h-8 w-8 text-base"
                                href="{{ $customers->nextPageUrl() }}">
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

{{-- Modal Thêm khách hàng --}}
@can('add customer')
<x-modal name="create-customer-modal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
        <h5 class="font-semibold text-base">Thêm khách hàng mới</h5>
        <button type="button" onclick="closeModal('create-customer-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('customers.store') }}" method="POST">
        @csrf
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Tên khách hàng <span class="text-danger-500">*</span></label>
                <input type="text" name="name" class="form-control rounded-lg" placeholder="Nhập tên khách hàng" required value="{{ old('name') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Số điện thoại</label>
                <input type="text" name="phone" class="form-control rounded-lg" placeholder="Nhập số điện thoại" value="{{ old('phone') }}">
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Địa chỉ</label>
                <textarea name="address" class="form-control rounded-lg" placeholder="Nhập địa chỉ" rows="3">{{ old('address') }}</textarea>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Lưu</button>
            <button type="button" onclick="closeModal('create-customer-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>
@endcan

{{-- Modal Sửa khách hàng --}}
@can('edit customer')
<x-modal name="edit-customer-modal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
        <h5 class="font-semibold text-base">Chỉnh sửa khách hàng</h5>
        <button type="button" onclick="closeModal('edit-customer-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form id="edit-customer-form" action="" method="POST">
        @csrf @method('PUT')
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Mã khách hàng</label>
                <input type="text" id="edit_customer_code" name="customer_code" class="form-control rounded-lg bg-neutral-100" readonly>
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Tên khách hàng <span class="text-danger-500">*</span></label>
                <input type="text" id="edit_name" name="name" class="form-control rounded-lg" placeholder="Nhập tên khách hàng" required>
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Số điện thoại</label>
                <input type="text" id="edit_phone" name="phone" class="form-control rounded-lg" placeholder="Nhập số điện thoại">
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Địa chỉ</label>
                <textarea id="edit_address" name="address" class="form-control rounded-lg" placeholder="Nhập địa chỉ" rows="3"></textarea>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Cập nhật</button>
            <button type="button" onclick="closeModal('edit-customer-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>

<script>
function openEditModal(id, customerCode, name, phone, address) {
    document.getElementById('edit-customer-form').action = '/customers/' + id;
    document.getElementById('edit_customer_code').value = customerCode;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_address').value = address;
    openModal('edit-customer-modal');
}
</script>
@endcan

{{-- Modal Lọc --}}
<x-modal name="filter-modal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
        <h5 class="font-semibold text-base">Lọc khách hàng</h5>
        <button type="button" onclick="closeModal('filter-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('customers.index') }}" method="GET">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <input type="hidden" name="search" value="{{ $search }}">
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Mã khách hàng</label>
                <input type="text" name="filter_customer_code" class="form-control rounded-lg" placeholder="Nhập mã khách hàng..." value="{{ request('filter_customer_code') }}">
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Tên khách hàng</label>
                <input type="text" name="filter_name" class="form-control rounded-lg" placeholder="Nhập tên khách hàng..." value="{{ request('filter_name') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Số điện thoại</label>
                <input type="text" name="filter_phone" class="form-control rounded-lg" placeholder="Nhập số điện thoại..." value="{{ request('filter_phone') }}">
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Áp dụng lọc</button>
            <button type="button" onclick="closeModal('filter-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>

@endsection
