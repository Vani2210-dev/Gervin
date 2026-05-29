@extends('layout.layout')
@php
    $title='Quản lý vai trò';
    $subTitle = 'Danh sách vai trò';
@endphp

@section('content')

    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="card h-full p-0 rounded-xl border-0 overflow-hidden">
                <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                    <div class="flex items-center flex-wrap gap-3">
                        <span class="text-base font-medium text-secondary-light mb-0">Hiển thị</span>
                        <form method="GET" action="{{ route('roles.index') }}" id="perPageForm">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <select name="per_page" class="form-select form-select-sm w-auto border-neutral-200 rounded-lg"
                                onchange="document.getElementById('perPageForm').submit()">
                                @foreach([10, 25, 50, 100] as $n)
                                <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                                @endforeach
                            </select>
                        </form>
                        <form method="GET" action="{{ route('roles.index') }}" class="navbar-search">
                            <input type="hidden" name="per_page" value="{{ $perPage }}">
                            <input type="text" class="bg-white h-10 w-auto" name="search"
                                value="{{ request('search') }}" placeholder="Tìm kiếm">
                            <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                        </form>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="openModal('filter-modal')"
                            class="btn bg-light-600 text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                            <iconify-icon icon="solar:filter-outline" class="icon text-xl line-height-1"></iconify-icon>
                            Lọc
                        </button>
                        @if(request()->filled('filter_name'))
                        <a href="{{ route('roles.index') }}" class="btn text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                            <iconify-icon icon="solar:close-circle-outline" class="icon text-xl line-height-1"></iconify-icon>
                            Xóa lọc
                        </a>
                        @endif
                        @can('add role')
                        <a href="{{ route('roles.create') }}" class="btn btn-primary text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                            <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                            Thêm vai trò mới
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive scroll-sm">
                        <table class="table bordered-table sm-table mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">STT</th>
                                    <th scope="col">Tên vai trò</th>
                                    <th scope="col">Quyền hạn</th>
                                    <th scope="col" class="text-center">Số người dùng</th>
                                    <th scope="col" class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $index => $role)
                                @php $stt = $roles->firstItem() + $loop->index; @endphp
                                <tr>
                                    <td>{{ $stt }}</td>
                                    <td><span class="text-base mb-0 font-normal text-secondary-light">{{ $role->name }}</span></td>
                                    <td>
                                        @php $perms = $role->permissions; $extra = $perms->count() - 3; @endphp
                                        @foreach($perms->take(3) as $permission)
                                            <span class="bg-primary-100 text-primary-600 border border-primary-600 px-3 py-1 rounded font-medium text-xs me-1 mb-1 inline-block">{{ $permission->name }}</span>
                                        @endforeach
                                        @if($extra > 0)
                                            <span class="bg-neutral-200 text-neutral-600 px-2 py-1 rounded font-medium text-xs mb-1 inline-block">+{{ $extra }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="text-base mb-0 font-normal text-secondary-light">{{ $role->users->count() }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="flex items-center gap-3 justify-center">
                                            @can('edit role')
                                            <a href="{{ route('roles.edit', $role) }}" class="bg-success-100 hover:bg-success-200 text-success-600 font-medium w-10 h-10 flex justify-center items-center rounded-full">
                                                <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                            </a>
                                            @endcan
                                            @can('delete role')
                                            @if($role->name !== 'admin')
                                            <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vai trò này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-danger-100 hover:bg-danger-200 text-danger-600 font-medium w-10 h-10 flex justify-center items-center rounded-full">
                                                    <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                                                </button>
                                            </form>
                                            @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8">
                                        <p class="text-neutral-500">Chưa có dữ liệu</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between flex-wrap gap-2 mt-6">
                        <span class="text-secondary-light text-sm">
                            Hiển thị {{ $roles->firstItem() ?? 0 }} đến {{ $roles->lastItem() ?? 0 }}
                            trong tổng {{ $roles->total() }} vai trò
                        </span>
                        @if($roles->hasPages())
                        <ul class="pagination flex flex-wrap items-center gap-2 justify-center">
                            <li class="page-item {{ $roles->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link bg-neutral-300 text-secondary-light font-semibold rounded-lg border-0 flex items-center justify-center h-8 w-8 text-base"
                                    href="{{ $roles->previousPageUrl() }}"><iconify-icon icon="ep:d-arrow-left"></iconify-icon></a>
                            </li>
                            @foreach($roles->getUrlRange(1, $roles->lastPage()) as $page => $url)
                            <li class="page-item">
                                <a class="page-link font-semibold rounded-lg border-0 flex items-center justify-center h-8 w-8 text-base {{ $page == $roles->currentPage() ? 'bg-primary-600 text-white' : 'bg-neutral-300 text-secondary-light' }}"
                                    href="{{ $url }}">{{ $page }}</a>
                            </li>
                            @endforeach
                            <li class="page-item {{ !$roles->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link bg-neutral-300 text-secondary-light font-semibold rounded-lg border-0 flex items-center justify-center h-8 w-8 text-base"
                                    href="{{ $roles->nextPageUrl() }}"><iconify-icon icon="ep:d-arrow-right"></iconify-icon></a>
                            </li>
                        </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- Modal Lọc --}}
<x-modal name="filter-modal" maxWidth="md">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
        <h5 class="font-semibold text-base">Lọc vai trò</h5>
        <button type="button" onclick="closeModal('filter-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('roles.index') }}" method="GET">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <div class="p-6">
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Tên vai trò</label>
                <input type="text" name="filter_name" class="form-control rounded-lg" placeholder="Nhập tên vai trò..." value="{{ request('filter_name') }}">
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Áp dụng lọc</button>
            <button type="button" onclick="closeModal('filter-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>

@endsection
