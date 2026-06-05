@extends('layout.layout')
@php
    $title='Danh sách người dùng';
    $subTitle = 'Danh sách người dùng';
    $script ='<script>
                        $(".remove-item-btn").on("click", function() {
                            if(confirm("Bạn có chắc chắn muốn xóa người dùng này?")) {
                                $(this).closest("form").submit();
                            }
                        });
            </script>';
@endphp

@section('content')

    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="card h-full p-0 rounded-xl border-0 overflow-hidden">
                <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                    <div class="flex items-center flex-wrap gap-3">
                        {{-- Per page --}}
                        <span class="text-base font-medium text-secondary-light mb-0">Hiển thị</span>
                        <form method="GET" action="{{ route('users.index') }}" id="perPageForm">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <input type="hidden" name="role_id" value="{{ request('role_id') }}">
                            <select name="per_page" class="form-select form-select-sm w-auto border-neutral-200 rounded-lg"
                                onchange="document.getElementById('perPageForm').submit()">
                                @foreach([10, 25, 50, 100] as $n)
                                <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                                @endforeach
                            </select>
                        </form>

                        {{-- Tìm kiếm --}}
                        <form method="GET" action="{{ route('users.index') }}" class="navbar-search">
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
                        @if(request()->filled('filter_name') || request()->filled('filter_email') || request()->filled('filter_phone') || request()->filled('filter_role_id'))
                        <a href="{{ route('users.index') }}" class="btn text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                            <iconify-icon icon="solar:close-circle-outline" class="icon text-xl line-height-1"></iconify-icon>
                            Xóa lọc
                        </a>
                        @endif
                        @can('add user')
                        <a href="{{ route('users.create') }}" class="btn btn-primary text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                            <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                            Thêm người dùng mới
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive scroll-sm">
                        <table class="table bordered-table sm-table mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">
                                        <div class="flex items-center gap-10">
                                            <div class="form-check style-check flex items-center">
                                                <input class="form-check-input rounded border input-form-dark" type="checkbox" name="checkbox"
                                                        id="selectAll">
                                            </div>
                                            STT
                                        </div>
                                    </th>
                                    <th scope="col">Tên</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Vai trò</th>
                                    <th scope="col">Ngày tham gia</th>
                                    <th scope="col" class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $index => $user)
                                @php $stt = $users->firstItem() + $loop->index; @endphp
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-10">
                                            <div class="form-check style-check flex items-center">
                                                <input class="form-check-input rounded border border-neutral-400" type="checkbox" name="checkbox"
                                                        id="SL-{{ $index + 1 }}">
                                            </div>
                                            {{ $stt }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex items-center">
                                            @if($user->avatar)
                                                <img src="{{ route('users.avatar', $user) }}?t={{ $user->updated_at?->timestamp }}"
                                                     class="rounded-full object-cover me-2 flex-shrink-0"
                                                     style="width:40px;height:40px;" alt="{{ $user->name }}">
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center me-2 flex-shrink-0">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="grow">
                                                <span class="text-base mb-0 font-normal text-secondary-light">{{ $user->name }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="text-base mb-0 font-normal text-secondary-light">{{ $user->email }}</span></td>
                                    <td>
                                        @if($user->role)
                                            <span class="bg-primary-100 text-primary-600 border border-primary-600 px-3 py-1 rounded font-medium text-xs">{{ $user->role->name }}</span>
                                        @else
                                            <span class="text-neutral-500 text-sm">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}</td>
                                    <td class="text-center">
                                        <div class="flex items-center gap-3 justify-center">
                                            @can('view user')
                                            <a href="{{ route('users.show', $user) }}" class="bg-info-100 hover:bg-info-200 text-info-600 font-medium w-10 h-10 flex justify-center items-center rounded-full">
                                                <iconify-icon icon="majesticons:eye-line" class="icon text-xl"></iconify-icon>
                                            </a>
                                            @endcan
                                            @can('edit user')
                                            <a href="{{ route('users.edit', $user) }}" class="bg-success-100 hover:bg-success-200 text-success-600 font-medium w-10 h-10 flex justify-center items-center rounded-full">
                                                <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                            </a>
                                            @endcan
                                            @can('delete user')
                                            @if($user->id !== auth()->id())
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="remove-item-btn">
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
                                    <td colspan="6" class="text-center py-8">
                                        <p class="text-neutral-500">Chưa có dữ liệu</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between flex-wrap gap-2 mt-6">
                        <span class="text-secondary-light text-sm">
                            Hiển thị {{ $users->firstItem() ?? 0 }} đến {{ $users->lastItem() ?? 0 }}
                            trong tổng {{ $users->total() }} người dùng
                        </span>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- Modal Lọc --}}
<x-modal name="filter-modal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
        <h5 class="font-semibold text-base">Lọc người dùng</h5>
        <button type="button" onclick="closeModal('filter-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('users.index') }}" method="GET">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Tên</label>
                <input type="text" name="filter_name" class="form-control rounded-lg" placeholder="Nhập tên..." value="{{ request('filter_name') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Email</label>
                <input type="text" name="filter_email" class="form-control rounded-lg" placeholder="Nhập email..." value="{{ request('filter_email') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Số điện thoại</label>
                <input type="text" name="filter_phone" class="form-control rounded-lg" placeholder="Nhập số điện thoại..." value="{{ request('filter_phone') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Vai trò</label>
                <select name="filter_role_id" class="form-select rounded-lg">
                    <option value="">Tất cả vai trò</option>
                    @foreach(\Spatie\Permission\Models\Role::all() as $role)
                    <option value="{{ $role->id }}" {{ request('filter_role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Áp dụng lọc</button>
            <button type="button" onclick="closeModal('filter-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>

@endsection
