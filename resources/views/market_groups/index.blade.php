@extends('layout.layout')
@php
    $title    = 'Nhóm thị trường';
    $subTitle = 'Quản lý nhóm thị trường, phân bổ nhân viên và khách hàng';
    $groupColors = [
        'A' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'badge' => 'bg-blue-600'],
        'B' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'badge' => 'bg-emerald-600'],
        'C' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'badge' => 'bg-amber-600'],
        'D' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'badge' => 'bg-purple-600'],
        'E' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'badge' => 'bg-rose-600'],
    ];
@endphp

@section('content')

@if(session('success'))
<div class="mb-4 p-4 rounded-xl border border-success-200 bg-success-50 text-success-700 flex items-center justify-between">
    <div class="flex items-center gap-2">
        <iconify-icon icon="lucide:check-circle" class="text-xl text-success-600"></iconify-icon>
        <span class="font-medium text-sm">{{ session('success') }}</span>
    </div>
    <button type="button" onclick="this.parentElement.remove()" class="text-neutral-400 hover:text-neutral-600">&times;</button>
</div>
@endif

<div class="grid grid-cols-12 gap-6">
    {{-- Card Thống kê --}}
    <div class="col-span-12">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-2xl flex-shrink-0">
                    <iconify-icon icon="solar:users-group-two-rounded-bold-duotone"></iconify-icon>
                </div>
                <div>
                    <div class="text-xs text-neutral-500 font-medium">Tổng số nhóm thị trường</div>
                    <div class="text-xl font-bold text-neutral-800">{{ $marketGroups->count() }} nhóm</div>
                </div>
            </div>

            <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl flex-shrink-0">
                    <iconify-icon icon="solar:user-id-bold-duotone"></iconify-icon>
                </div>
                <div>
                    <div class="text-xs text-neutral-500 font-medium">Tổng nhân viên trong nhóm</div>
                    <div class="text-xl font-bold text-neutral-800">{{ $marketGroups->sum('users_count') }} lượt NV</div>
                </div>
            </div>

            <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl flex-shrink-0">
                    <iconify-icon icon="solar:user-hand-up-bold-duotone"></iconify-icon>
                </div>
                <div>
                    <div class="text-xs text-neutral-500 font-medium">Tổng khách hàng đã gán nhóm</div>
                    <div class="text-xl font-bold text-neutral-800">{{ $marketGroups->sum('customers_count') }} KH</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="col-span-12">
        <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h5 class="font-bold text-lg text-neutral-800 m-0">Danh sách Nhóm thị trường</h5>
                    <p class="text-xs text-neutral-500 mt-1">Phân quyền chăm sóc khách hàng và doanh số theo từng nhóm thị trường</p>
                </div>
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('market-groups.index') }}" class="flex items-center">
                        <div class="relative">
                            <input type="text" name="search" value="{{ $search }}" placeholder="Tìm kiếm nhóm..." class="form-control rounded-lg pl-9 pr-3 py-1.5 text-xs w-48 border-neutral-300">
                            <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-sm"></iconify-icon>
                        </div>
                    </form>
                    <button type="button" onclick="openCreateGroupModal()" class="btn btn-sm btn-primary rounded-lg flex items-center gap-1.5 px-3 py-2 text-xs font-semibold">
                        <iconify-icon icon="lucide:plus" class="text-base"></iconify-icon> Thêm nhóm mới
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table bordered-table mb-0 w-full">
                    <thead>
                        <tr class="bg-neutral-50 text-neutral-600 font-semibold text-xs uppercase border-b border-neutral-200">
                            <th style="width: 60px;" class="text-center py-3">STT</th>
                            <th style="width: 180px;" class="py-3">Tên nhóm</th>
                            <th class="py-3">Mô tả</th>
                            <th style="width: 320px;" class="py-3">Nhân viên trong nhóm</th>
                            <th style="width: 180px;" class="text-center py-3">Khách hàng</th>
                            <th style="width: 120px;" class="text-center py-3">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 text-xs">
                        @forelse($marketGroups as $index => $group)
                        @php
                            $codeUpper = strtoupper($group->code ?: '');
                            $color = $groupColors[$codeUpper] ?? ['bg' => 'bg-neutral-50', 'text' => 'text-neutral-700', 'border' => 'border-neutral-200', 'badge' => 'bg-neutral-600'];
                        @endphp
                        <tr class="hover:bg-neutral-50/70 transition-colors">
                            <td class="text-center font-semibold text-neutral-500 py-3">{{ $index + 1 }}</td>
                            <td class="py-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-8 h-8 rounded-lg {{ $color['badge'] }} text-white font-bold text-sm flex items-center justify-center flex-shrink-0">
                                        {{ $group->code ?: substr($group->name, -1) }}
                                    </span>
                                    <div>
                                        <div class="font-bold text-sm text-neutral-800">{{ $group->name }}</div>
                                        @if($group->code)
                                            <span class="text-[10px] text-neutral-400 uppercase font-mono">Mã: {{ $group->code }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 text-neutral-600">
                                {{ $group->description ?: '—' }}
                            </td>
                            <td class="py-3">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @forelse($group->users as $u)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-neutral-100 text-neutral-700 border border-neutral-200">
                                            <iconify-icon icon="lucide:user" class="text-xs text-primary-500"></iconify-icon>
                                            {{ $u->user_code ? '[' . $u->user_code . '] ' : '' }}{{ $u->name }}
                                        </span>
                                    @empty
                                        <span class="text-neutral-400 italic">Chưa có nhân viên</span>
                                    @endforelse
                                    <button type="button" 
                                        onclick="openAssignUsersModalFromBtn(this)" 
                                        data-id="{{ $group->id }}" 
                                        data-name="{{ $group->name }}" 
                                        data-user-ids="{{ json_encode($group->users->pluck('id')) }}" 
                                        class="text-primary-600 hover:text-primary-800 text-xs font-semibold p-1 hover:bg-primary-50 rounded" 
                                        title="Thêm/sửa nhân viên">
                                        <iconify-icon icon="lucide:user-plus" class="text-sm"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                            <td class="py-3 text-center">
                                <button type="button" 
                                    onclick="openGroupCustomersModalFromBtn(this)" 
                                    data-id="{{ $group->id }}" 
                                    data-name="{{ $group->name }}" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold {{ $color['bg'] }} {{ $color['text'] }} hover:shadow border {{ $color['border'] }} transition-all" 
                                    title="Xem bảng chi tiết danh sách khách hàng nhóm">
                                    <iconify-icon icon="solar:users-group-two-rounded-bold" class="text-sm"></iconify-icon>
                                    <span>{{ $group->customers_count }} khách hàng</span>
                                    <iconify-icon icon="solar:eye-bold" class="text-xs"></iconify-icon>
                                </button>
                            </td>
                            <td class="py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                        onclick="openGroupCustomersModalFromBtn(this)" 
                                        data-id="{{ $group->id }}" 
                                        data-name="{{ $group->name }}" 
                                        class="w-8 h-8 rounded-full bg-primary-50 hover:bg-primary-100 text-primary-600 flex items-center justify-center transition-colors" 
                                        title="Xem chi tiết tất cả khách hàng của nhóm">
                                        <iconify-icon icon="solar:eye-bold" class="text-sm"></iconify-icon>
                                    </button>
                                    <button type="button" 
                                        onclick="openEditGroupModalFromBtn(this)" 
                                        data-id="{{ $group->id }}" 
                                        data-name="{{ $group->name }}" 
                                        data-code="{{ $group->code ?? '' }}" 
                                        data-description="{{ $group->description ?? '' }}" 
                                        data-user-ids="{{ json_encode($group->users->pluck('id')) }}" 
                                        class="w-8 h-8 rounded-full bg-neutral-100 hover:bg-neutral-200 text-neutral-600 flex items-center justify-center transition-colors" 
                                        title="Sửa nhóm">
                                        <iconify-icon icon="lucide:edit-3" class="text-sm"></iconify-icon>
                                    </button>
                                    <form action="{{ route('market-groups.destroy', $group->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa nhóm thị trường này? Các khách hàng thuộc nhóm này sẽ chuyển về chưa phân nhóm.')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 rounded-full bg-danger-50 hover:bg-danger-100 text-danger-600 flex items-center justify-center transition-colors" title="Xóa nhóm">
                                            <iconify-icon icon="lucide:trash-2" class="text-sm"></iconify-icon>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-neutral-400">
                                Chưa có nhóm thị trường nào.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL 1: THÊM NHÓM THỊ TRƯỜNG MỚI --}}
<div id="modal-create-group" class="fixed inset-0 bg-neutral-900/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-150">
        <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
            <h5 class="font-bold text-base text-neutral-800 m-0">Tạo Nhóm thị trường mới</h5>
            <button type="button" onclick="closeModal('modal-create-group')" class="text-neutral-400 hover:text-neutral-600 text-xl leading-none">&times;</button>
        </div>
        <form action="{{ route('market-groups.store') }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="form-label font-semibold text-xs text-neutral-700">Tên nhóm <span class="text-danger-500">*</span></label>
                    <input type="text" name="name" required placeholder="Ví dụ: Nhóm F, Nhóm Miền Bắc..." class="form-control rounded-lg text-xs w-full">
                </div>
                <div>
                    <label class="form-label font-semibold text-xs text-neutral-700">Mã nhóm (Ký hiệu)</label>
                    <input type="text" name="code" placeholder="Ví dụ: F, MB..." class="form-control rounded-lg text-xs w-full uppercase">
                </div>
                <div>
                    <label class="form-label font-semibold text-xs text-neutral-700">Mô tả / Khu vực</label>
                    <textarea name="description" rows="2" placeholder="Ghi chú về nhóm thị trường này..." class="form-control rounded-lg text-xs w-full"></textarea>
                </div>
                <div>
                    <label class="form-label font-semibold text-xs text-neutral-700">Thành viên (Nhân viên trong nhóm)</label>
                    <select name="user_ids[]" id="create-group-user-ids" multiple class="w-full text-xs">
                        @foreach($allUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->user_code ? '[' . $u->user_code . '] ' : '' }}{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 bg-neutral-50 border-t border-neutral-200 flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-create-group')" class="btn btn-sm btn-neutral rounded-lg px-4 py-2 text-xs font-semibold">Hủy</button>
                <button type="submit" class="btn btn-sm btn-primary rounded-lg px-5 py-2 text-xs font-semibold">Tạo nhóm</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: SỬA NHÓM THỊ TRƯỜNG --}}
<div id="modal-edit-group" class="fixed inset-0 bg-neutral-900/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
            <h5 class="font-bold text-base text-neutral-800 m-0">Chỉnh sửa Nhóm thị trường</h5>
            <button type="button" onclick="closeModal('modal-edit-group')" class="text-neutral-400 hover:text-neutral-600 text-xl leading-none">&times;</button>
        </div>
        <form id="form-edit-group" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-4">
                <div>
                    <label class="form-label font-semibold text-xs text-neutral-700">Tên nhóm <span class="text-danger-500">*</span></label>
                    <input type="text" name="name" id="edit-group-name" required class="form-control rounded-lg text-xs w-full">
                </div>
                <div>
                    <label class="form-label font-semibold text-xs text-neutral-700">Mã nhóm</label>
                    <input type="text" name="code" id="edit-group-code" class="form-control rounded-lg text-xs w-full uppercase">
                </div>
                <div>
                    <label class="form-label font-semibold text-xs text-neutral-700">Mô tả</label>
                    <textarea name="description" id="edit-group-description" rows="2" class="form-control rounded-lg text-xs w-full"></textarea>
                </div>
                <div>
                    <label class="form-label font-semibold text-xs text-neutral-700">Thành viên (Nhân viên trong nhóm)</label>
                    <select name="user_ids[]" id="edit-group-user-ids" multiple class="w-full text-xs">
                        @foreach($allUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->user_code ? '[' . $u->user_code . '] ' : '' }}{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 bg-neutral-50 border-t border-neutral-200 flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-edit-group')" class="btn btn-sm btn-neutral rounded-lg px-4 py-2 text-xs font-semibold">Hủy</button>
                <button type="submit" class="btn btn-sm btn-primary rounded-lg px-5 py-2 text-xs font-semibold">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 3: GÁN / CẬP NHẬT NHÂN VIÊN TRONG NHÓM --}}
<div id="modal-assign-users" class="fixed inset-0 bg-neutral-900/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
            <h5 class="font-bold text-base text-neutral-800 m-0">Nhân viên: <span id="assign-users-group-title" class="text-primary-600"></span></h5>
            <button type="button" onclick="closeModal('modal-assign-users')" class="text-neutral-400 hover:text-neutral-600 text-xl leading-none">&times;</button>
        </div>
        <form id="form-assign-users" method="POST">
            @csrf
            <div class="p-6">
                <p class="text-xs text-neutral-500 mb-3">Chọn hoặc tìm kiếm các nhân viên thuộc nhóm thị trường này:</p>
                <select name="user_ids[]" id="assign-user-ids-select" multiple class="w-full text-xs">
                    @foreach($allUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->user_code ? '[' . $u->user_code . '] ' : '' }}{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="px-6 py-4 bg-neutral-50 border-t border-neutral-200 flex justify-end gap-3">
                <button type="button" onclick="closeModal('modal-assign-users')" class="btn btn-sm btn-neutral rounded-lg px-4 py-2 text-xs font-semibold">Hủy</button>
                <button type="submit" class="btn btn-sm btn-primary rounded-lg px-5 py-2 text-xs font-semibold">Cập nhật thành viên</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 4: BẢNG CHI TIẾT KHÁCH HÀNG THUỘC NHÓM THỊ TRƯỜNG (FULL TẤT CẢ CÁC CỘT) --}}
<div id="modal-group-customers" class="fixed inset-0 bg-neutral-900/60 z-50 hidden flex items-center justify-center p-2 sm:p-4">
    <div class="bg-white rounded-2xl w-[98vw] max-w-[1700px] shadow-2xl overflow-hidden flex flex-col h-[94vh] max-h-[94vh]">
        {{-- Header Modal --}}
        <div class="px-6 py-4 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-3 shrink-0 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-2xl shrink-0">
                    <iconify-icon icon="solar:users-group-two-rounded-bold-duotone"></iconify-icon>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h5 class="font-bold text-lg text-neutral-800 m-0">Chi tiết khách hàng: <span id="group-customers-title" class="text-primary-600"></span></h5>
                        <span id="group-customers-total-count-badge" class="px-3 py-0.5 rounded-full text-xs font-bold bg-primary-100 text-primary-800 border border-primary-200 flex items-center gap-1">
                            <iconify-icon icon="solar:users-group-two-rounded-bold"></iconify-icon>
                            <span>0 khách hàng</span>
                        </span>
                    </div>
                    <p class="text-xs text-neutral-500 m-0 mt-0.5">Bảng chi tiết toàn bộ thông tin khách hàng, phản ánh, ảnh chụp và công nợ thuộc nhóm thị trường</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <input type="text" id="group-customers-search-input" onkeyup="filterGroupCustomersTable()" placeholder="Tìm nhanh khách hàng (tên, SĐT, địa chỉ, đối tác...)" class="form-control rounded-lg pl-9 pr-3 py-1.5 text-xs w-72 border-neutral-300">
                    <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-sm"></iconify-icon>
                </div>
                <button type="button" onclick="closeModal('modal-group-customers')" class="w-8 h-8 rounded-full bg-neutral-100 hover:bg-neutral-200 text-neutral-500 flex items-center justify-center text-lg leading-none">&times;</button>
            </div>
        </div>

        {{-- Form thêm nhanh khách hàng vào nhóm --}}
        <div class="px-6 py-3 bg-neutral-50 border-b border-neutral-200 shrink-0">
            <form id="form-add-customers-to-group" method="POST" class="flex flex-wrap items-center gap-3">
                @csrf
                <div class="text-xs font-semibold text-neutral-700 flex items-center gap-1.5 shrink-0">
                    <iconify-icon icon="solar:user-plus-bold" class="text-primary-600 text-sm"></iconify-icon>
                    Gán khách hàng vào nhóm:
                </div>
                <div class="flex-1 min-w-[300px]">
                    <select name="customer_ids[]" id="select-customers-to-add" multiple placeholder="Chọn khách hàng để gán vào nhóm này...">
                        @foreach($allCustomers as $c)
                            <option value="{{ $c->id }}">{{ $c->customer_code ? '[' . $c->customer_code . '] ' : '' }}{{ $c->name }} {{ $c->market_group_id ? '(Đã có nhóm)' : '(Chưa có nhóm)' }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary rounded-lg px-4 py-2 text-xs font-semibold shrink-0 flex items-center gap-1.5 shadow-sm">
                    <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon> Thêm vào nhóm
                </button>
            </form>
        </div>

        {{-- Bảng danh sách chi tiết tất cả các cột --}}
        <div class="overflow-auto flex-1 p-4 bg-neutral-50/50">
            <div id="group-customers-loading" class="text-center py-20 text-neutral-400">
                <iconify-icon icon="lucide:loader-2" class="text-3xl animate-spin text-primary-500"></iconify-icon>
                <div class="text-xs mt-2 font-medium">Đang tải toàn bộ dữ liệu chi tiết khách hàng...</div>
            </div>

            <div id="group-customers-table-container" class="hidden bg-white rounded-xl border border-neutral-200 shadow-sm overflow-hidden">
                <table class="table bordered-table w-full mb-0 text-xs text-left" style="min-width: 2000px;">
                    <thead>
                        <tr class="bg-neutral-100 text-neutral-700 font-bold border-b border-neutral-200 text-xs">
                            <th style="width: 50px;" class="text-center py-3 px-2 sticky left-0 bg-neutral-100 z-10">STT</th>
                            <th style="width: 100px;" class="py-3 px-3 sticky left-[50px] bg-neutral-100 z-10">Mã KH</th>
                            <th style="min-width: 220px;" class="py-3 px-3 sticky left-[150px] bg-neutral-100 z-10 border-r border-neutral-200">Khách hàng & Trạng thái</th>
                            <th style="min-width: 120px;" class="py-3 px-3">Số điện thoại</th>
                            <th style="min-width: 260px;" class="py-3 px-3">Địa chỉ & Bản đồ GPS</th>
                            <th style="min-width: 140px;" class="py-3 px-3 text-center">Ảnh hiện trường</th>
                            <th style="min-width: 160px;" class="py-3 px-3">Đối tác hợp tác</th>
                            <th style="min-width: 130px;" class="py-3 px-3">Quy mô xưởng</th>
                            <th style="min-width: 140px;" class="py-3 px-3">Tính cách KH</th>
                            <th style="min-width: 220px;" class="py-3 px-3">Phản ánh về Gervin</th>
                            <th style="min-width: 220px;" class="py-3 px-3">Đề xuất KH</th>
                            <th style="min-width: 220px;" class="py-3 px-3">Đề xuất Sale</th>
                            <th style="min-width: 150px;" class="py-3 px-3">Chính sách</th>
                            <th style="min-width: 120px;" class="py-3 px-3 text-right">Định mức nợ</th>
                            <th style="min-width: 130px;" class="py-3 px-3 text-right">Công nợ</th>
                            <th style="min-width: 130px;" class="py-3 px-3 text-right">Đã thanh toán</th>
                            <th style="width: 80px;" class="py-3 px-3 text-center">Bỏ nhóm</th>
                        </tr>
                    </thead>
                    <tbody id="group-customers-tbody" class="divide-y divide-neutral-200">
                    </tbody>
                </table>
            </div>

            <div id="group-customers-empty" class="hidden text-center py-20 text-neutral-400 bg-white rounded-xl border border-neutral-200">
                <iconify-icon icon="solar:users-group-two-rounded-line-duotone" class="text-4xl text-neutral-300"></iconify-icon>
                <div class="text-sm mt-2 font-bold text-neutral-700">Nhóm này chưa có khách hàng nào</div>
                <div class="text-xs text-neutral-400 mt-1">Sử dụng ô chọn phía trên để gán khách hàng vào nhóm thị trường này.</div>
            </div>

            <div id="group-customers-no-search" class="hidden text-center py-16 text-neutral-400 bg-white rounded-xl border border-neutral-200">
                <iconify-icon icon="lucide:search-x" class="text-3xl text-neutral-300"></iconify-icon>
                <div class="text-xs mt-2 font-medium">Không tìm thấy khách hàng nào khớp với từ khóa tìm kiếm.</div>
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="px-6 py-3 bg-white border-t border-neutral-200 flex items-center justify-between shrink-0">
            <div class="text-xs text-neutral-500 font-medium" id="group-customers-footer-info">
                Hiển thị danh sách khách hàng
            </div>
            <button type="button" onclick="closeModal('modal-group-customers')" class="btn btn-sm btn-neutral rounded-lg px-5 py-2 text-xs font-semibold">Đóng</button>
        </div>
    </div>
</div>

{{-- ===== IMAGE LIGHTBOX MODAL (XEM ẢNH TIMESTAMP PHÓNG TO) ===== --}}
<div id="image-lightbox-modal" class="hidden fixed inset-0 z-[3000] bg-black/85 backdrop-blur-sm flex flex-col items-center justify-center p-4" onclick="closeImageLightbox()">
    <div class="relative max-w-4xl max-h-[92vh] flex flex-col items-center" onclick="event.stopPropagation()">
        <button type="button" onclick="closeImageLightbox()"
                class="absolute -top-12 right-0 text-white/80 hover:text-white bg-neutral-900/70 rounded-full p-2 hover:bg-neutral-900 transition flex items-center justify-center shadow-lg">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
        </button>
        <img id="image-lightbox-img" src="" alt="Customer Photo" class="max-h-[82vh] max-w-full rounded-xl shadow-2xl border border-white/20 object-contain bg-black">
        <div id="image-lightbox-caption" class="mt-3 text-white/90 text-xs md:text-sm font-medium bg-neutral-900/80 px-4 py-1.5 rounded-full border border-white/10 max-w-xl text-center truncate"></div>
    </div>
</div>

<script>
let createGroupTs = null;
let editGroupTs = null;
let assignUsersTs = null;
let selectCustomersTs = null;
let currentActiveGroupId = null;
let currentGroupCustomersData = [];

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('hidden');
}

function openCreateGroupModal() {
    document.getElementById('modal-create-group').classList.remove('hidden');
    if (typeof TomSelect !== 'undefined' && !createGroupTs && document.getElementById('create-group-user-ids')) {
        createGroupTs = new TomSelect('#create-group-user-ids', {
            plugins: ['remove_button'],
            placeholder: 'Chọn nhân viên...',
        });
    }
}

function openEditGroupModalFromBtn(btn) {
    const id = btn.getAttribute('data-id');
    const name = btn.getAttribute('data-name') || '';
    const code = btn.getAttribute('data-code') || '';
    const description = btn.getAttribute('data-description') || '';
    let userIds = [];
    try {
        userIds = JSON.parse(btn.getAttribute('data-user-ids') || '[]');
    } catch(e) {}

    document.getElementById('form-edit-group').action = '/market-groups/' + id;
    document.getElementById('edit-group-name').value = name;
    document.getElementById('edit-group-code').value = code;
    document.getElementById('edit-group-description').value = description;

    document.getElementById('modal-edit-group').classList.remove('hidden');

    if (typeof TomSelect !== 'undefined') {
        if (!editGroupTs && document.getElementById('edit-group-user-ids')) {
            editGroupTs = new TomSelect('#edit-group-user-ids', {
                plugins: ['remove_button'],
                placeholder: 'Chọn nhân viên...',
            });
        }
        if (editGroupTs) {
            editGroupTs.clear();
            if (userIds && Array.isArray(userIds)) {
                editGroupTs.setValue(userIds.map(String));
            }
        }
    }
}

function openAssignUsersModalFromBtn(btn) {
    const id = btn.getAttribute('data-id');
    const groupName = btn.getAttribute('data-name') || '';
    let userIds = [];
    try {
        userIds = JSON.parse(btn.getAttribute('data-user-ids') || '[]');
    } catch(e) {}

    document.getElementById('assign-users-group-title').textContent = groupName;
    document.getElementById('form-assign-users').action = '/market-groups/' + id + '/users';

    document.getElementById('modal-assign-users').classList.remove('hidden');

    if (typeof TomSelect !== 'undefined') {
        if (!assignUsersTs && document.getElementById('assign-user-ids-select')) {
            assignUsersTs = new TomSelect('#assign-user-ids-select', {
                plugins: ['remove_button'],
                placeholder: 'Chọn nhân viên...',
            });
        }
        if (assignUsersTs) {
            assignUsersTs.clear();
            if (userIds && Array.isArray(userIds)) {
                assignUsersTs.setValue(userIds.map(String));
            }
        }
    }
}

function openGroupCustomersModalFromBtn(btn) {
    const groupId = btn.getAttribute('data-id');
    const groupName = btn.getAttribute('data-name') || '';
    currentActiveGroupId = groupId;
    document.getElementById('group-customers-title').textContent = groupName;
    document.getElementById('form-add-customers-to-group').action = '/market-groups/' + groupId + '/customers';

    // Reset ô tìm kiếm
    const searchInput = document.getElementById('group-customers-search-input');
    if (searchInput) searchInput.value = '';

    document.getElementById('modal-group-customers').classList.remove('hidden');

    // Khởi tạo TomSelect cho chọn khách hàng thêm vào nhóm
    if (typeof TomSelect !== 'undefined') {
        if (!selectCustomersTs && document.getElementById('select-customers-to-add')) {
            selectCustomersTs = new TomSelect('#select-customers-to-add', {
                plugins: ['remove_button'],
                placeholder: 'Chọn một hoặc nhiều khách hàng để thêm vào nhóm...',
                maxItems: null,
            });
        }
        if (selectCustomersTs) {
            selectCustomersTs.clear();
        }
    }

    // Tải danh sách khách hàng trong nhóm qua AJAX
    loadGroupCustomers(groupId);
}

function loadGroupCustomers(groupId) {
    const loading = document.getElementById('group-customers-loading');
    const tableContainer = document.getElementById('group-customers-table-container');
    const empty = document.getElementById('group-customers-empty');
    const noSearch = document.getElementById('group-customers-no-search');
    const badge = document.getElementById('group-customers-total-count-badge');
    const footerInfo = document.getElementById('group-customers-footer-info');

    loading.classList.remove('hidden');
    tableContainer.classList.add('hidden');
    empty.classList.add('hidden');
    noSearch.classList.add('hidden');

    fetch('/market-groups/' + groupId + '/customers-data')
        .then(res => res.json())
        .then(data => {
            loading.classList.add('hidden');
            currentGroupCustomersData = data.customers || [];
            const total = data.total_count ?? currentGroupCustomersData.length;

            if (badge) {
                badge.innerHTML = `<iconify-icon icon="solar:users-group-two-rounded-bold"></iconify-icon> <span>${total} khách hàng</span>`;
            }

            if (footerInfo) {
                footerInfo.textContent = `Tổng cộng: ${total} khách hàng thuộc nhóm này`;
            }

            if (currentGroupCustomersData.length === 0) {
                empty.classList.remove('hidden');
            } else {
                tableContainer.classList.remove('hidden');
                renderGroupCustomersRows(currentGroupCustomersData);
            }
        })
        .catch(err => {
            loading.classList.add('hidden');
            empty.classList.remove('hidden');
        });
}

function filterGroupCustomersTable() {
    const searchInput = document.getElementById('group-customers-search-input');
    const term = (searchInput ? searchInput.value : '').toLowerCase().trim();
    const tableContainer = document.getElementById('group-customers-table-container');
    const noSearch = document.getElementById('group-customers-no-search');
    const empty = document.getElementById('group-customers-empty');
    const footerInfo = document.getElementById('group-customers-footer-info');

    if (currentGroupCustomersData.length === 0) {
        return;
    }

    if (!term) {
        tableContainer.classList.remove('hidden');
        noSearch.classList.add('hidden');
        renderGroupCustomersRows(currentGroupCustomersData);
        if (footerInfo) footerInfo.textContent = `Hiển thị toàn bộ ${currentGroupCustomersData.length} khách hàng`;
        return;
    }

    const filtered = currentGroupCustomersData.filter(c => {
        const text = [
            c.customer_code,
            c.name,
            c.phone,
            c.full_address,
            c.partner_competitors,
            c.workshop_scale,
            c.personality,
            c.feedback,
            c.customer_proposal,
            c.sale_proposal,
            c.policy
        ].filter(Boolean).join(' ').toLowerCase();
        return text.includes(term);
    });

    if (filtered.length === 0) {
        tableContainer.classList.add('hidden');
        noSearch.classList.remove('hidden');
        if (footerInfo) footerInfo.textContent = 'Không có kết quả khớp với tìm kiếm';
    } else {
        tableContainer.classList.remove('hidden');
        noSearch.classList.add('hidden');
        renderGroupCustomersRows(filtered);
        if (footerInfo) footerInfo.textContent = `Đang hiển thị ${filtered.length} / ${currentGroupCustomersData.length} khách hàng`;
    }
}

function renderGroupCustomersRows(customers) {
    const tbody = document.getElementById('group-customers-tbody');
    if (!tbody) return;

    tbody.innerHTML = customers.map((c, idx) => {
        // Status formatting
        const st = c.status || 'Đang đặt hàng';
        let stClass = 'bg-neutral-100 text-neutral-600 border-neutral-200';
        if (st === 'Đang đặt hàng') stClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
        else if (st === 'Không đặt GERVIN') stClass = 'bg-rose-50 text-rose-700 border-rose-200';
        else if (st === 'Khách hàng mới tiềm năng') stClass = 'bg-blue-50 text-blue-700 border-blue-200';
        else if (st === 'Tạm dừng hợp tác') stClass = 'bg-amber-50 text-amber-700 border-amber-200';

        // GPS Link
        let gpsHtml = '';
        if (c.latitude && c.longitude) {
            gpsHtml = `<div class="mt-1"><a href="https://www.google.com/maps?q=${c.latitude},${c.longitude}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-primary-600 hover:text-primary-800 font-semibold bg-primary-50 px-1.5 py-0.5 rounded border border-primary-200 hover:bg-primary-100 transition-colors"><iconify-icon icon="solar:map-point-wave-bold" class="text-rose-500"></iconify-icon> Maps (${Number(c.latitude).toFixed(3)}, ${Number(c.longitude).toFixed(3)})</a></div>`;
        }

        // Photos thumbnails with lightbox
        let photos = c.photos || [];
        if (typeof photos === 'string') {
            try { photos = JSON.parse(photos); } catch(e) { photos = []; }
        }
        let photosHtml = '<span class="text-neutral-300 italic text-[11px]">Chưa có</span>';
        if (Array.isArray(photos) && photos.length > 0) {
            const thumbs = photos.slice(0, 3).map((p, pIdx) => {
                const src = p.startsWith('http') || p.startsWith('/') ? p : '/' + p;
                const safeName = (c.name || '').replace(/'/g, "\\'");
                return `<img src="${src}" class="w-8 h-8 rounded border border-neutral-200 object-cover cursor-pointer hover:scale-110 transition-transform shadow-xs shrink-0" onclick="openImageLightbox('${src}', '${safeName} - Ảnh ${pIdx+1}')" title="Xem ảnh timestamp">`;
            }).join('');
            const moreBadge = photos.length > 3 
                ? `<span class="w-8 h-8 rounded bg-neutral-100 border border-neutral-300 text-[10px] font-bold text-neutral-600 flex items-center justify-center cursor-pointer hover:bg-neutral-200" onclick="previewCustomerPhotos(${JSON.stringify(photos).replace(/"/g, '&quot;')}, '${(c.name||'').replace(/'/g, "\\'")}')">+${photos.length - 3}</span>` 
                : '';
            photosHtml = `<div class="flex items-center gap-1 justify-center">${thumbs}${moreBadge}</div>`;
        }

        // Financial formatting
        const debtFormatted = Number(c.debt || 0).toLocaleString('vi-VN') + ' ₫';
        const debtClass = (Number(c.debt || 0) > 0) ? 'text-danger-600 font-bold' : 'text-neutral-600';

        const paidFormatted = Number(c.period_paid || 0).toLocaleString('vi-VN') + ' ₫';
        const paidClass = (Number(c.period_paid || 0) > 0) ? 'text-emerald-600 font-bold' : 'text-neutral-600';

        const limitFormatted = c.debt_limit ? Number(c.debt_limit).toLocaleString('vi-VN') + ' ₫' : '—';

        return `
            <tr class="hover:bg-neutral-50/80 transition-colors">
                {{-- 1. STT --}}
                <td class="text-center font-medium text-neutral-500 py-3 px-2 sticky left-0 bg-white">${idx + 1}</td>

                {{-- 2. Mã KH --}}
                <td class="font-semibold text-neutral-800 font-mono py-3 px-3 sticky left-[50px] bg-white">${c.customer_code || '—'}</td>

                {{-- 3. Khách hàng & Trạng thái --}}
                <td class="py-3 px-3 sticky left-[150px] bg-white border-r border-neutral-200">
                    <a href="/customers?overview_id=${c.id}" target="_blank" class="font-bold text-neutral-900 hover:text-primary-600 flex items-center gap-1 leading-snug" title="Mở chi tiết khách hàng">
                        <span>${c.name}</span>
                        <iconify-icon icon="lucide:external-link" class="text-[11px] opacity-40 shrink-0"></iconify-icon>
                    </a>
                    <div class="mt-1">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border inline-block ${stClass}">${st}</span>
                    </div>
                </td>

                {{-- 4. SĐT --}}
                <td class="text-neutral-700 font-medium py-3 px-3">
                    ${c.phone ? `<a href="tel:${c.phone}" class="hover:text-primary-600">${c.phone}</a>` : '<span class="text-neutral-300">—</span>'}
                </td>

                {{-- 5. Địa chỉ & GPS --}}
                <td class="text-neutral-700 py-3 px-3 max-w-[280px]">
                    <div class="line-clamp-2 leading-relaxed" title="${c.full_address || c.address || ''}">${c.full_address || c.address || '—'}</div>
                    ${gpsHtml}
                </td>

                {{-- 6. Ảnh hiện trường --}}
                <td class="py-3 px-3 text-center">
                    ${photosHtml}
                </td>

                {{-- 7. Đối tác hợp tác --}}
                <td class="text-neutral-700 font-medium py-3 px-3">
                    ${c.partner_competitors || '<span class="text-neutral-300">—</span>'}
                </td>

                {{-- 8. Quy mô xưởng --}}
                <td class="text-neutral-700 py-3 px-3">
                    ${c.workshop_scale || '<span class="text-neutral-300">—</span>'}
                </td>

                {{-- 9. Tính cách KH --}}
                <td class="text-neutral-700 py-3 px-3">
                    ${c.personality || '<span class="text-neutral-300">—</span>'}
                </td>

                {{-- 10. Phản ánh về Gervin --}}
                <td class="text-neutral-700 py-3 px-3 max-w-[220px]">
                    <div class="line-clamp-2 leading-relaxed" title="${c.feedback || ''}">${c.feedback || '<span class="text-neutral-300 italic text-[11px]">Chưa có</span>'}</div>
                </td>

                {{-- 11. Đề xuất KH --}}
                <td class="text-neutral-700 py-3 px-3 max-w-[220px]">
                    <div class="line-clamp-2 leading-relaxed" title="${c.customer_proposal || ''}">${c.customer_proposal || '<span class="text-neutral-300 italic text-[11px]">Chưa có</span>'}</div>
                </td>

                {{-- 12. Đề xuất Sale --}}
                <td class="text-neutral-700 py-3 px-3 max-w-[220px]">
                    <div class="line-clamp-2 leading-relaxed" title="${c.sale_proposal || ''}">${c.sale_proposal || '<span class="text-neutral-300 italic text-[11px]">Chưa có</span>'}</div>
                </td>

                {{-- 13. Chính sách --}}
                <td class="text-neutral-700 py-3 px-3 max-w-[150px]">
                    <div class="line-clamp-2 leading-relaxed" title="${c.policy || ''}">${c.policy || '<span class="text-neutral-300">—</span>'}</div>
                </td>

                {{-- 14. Định mức nợ --}}
                <td class="text-right text-neutral-700 py-3 px-3">
                    ${limitFormatted}
                </td>

                {{-- 15. Công nợ --}}
                <td class="text-right py-3 px-3 ${debtClass}">
                    ${debtFormatted}
                </td>

                {{-- 16. Đã thanh toán --}}
                <td class="text-right py-3 px-3 ${paidClass}">
                    ${paidFormatted}
                </td>

                {{-- 17. Thao tác (Bỏ nhóm) --}}
                <td class="text-center py-3 px-3">
                    <form action="/market-groups/${currentActiveGroupId}/customers/${c.id}" method="POST" onsubmit="return confirm('Bỏ khách hàng ${c.name} khỏi nhóm này?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-danger-500 hover:text-danger-700 p-1.5 rounded hover:bg-danger-50 transition-colors" title="Bỏ khỏi nhóm">
                            <iconify-icon icon="lucide:x-circle" class="text-lg"></iconify-icon>
                        </button>
                    </form>
                </td>
            </tr>
        `;
    }).join('');
}

// Lightbox modal handlers
function openImageLightbox(src, caption) {
    const modal = document.getElementById('image-lightbox-modal');
    const img   = document.getElementById('image-lightbox-img');
    const cap   = document.getElementById('image-lightbox-caption');
    if (img) img.src = src;
    if (cap) cap.textContent = caption || '';
    if (modal) modal.classList.remove('hidden');
}

function closeImageLightbox() {
    const modal = document.getElementById('image-lightbox-modal');
    if (modal) modal.classList.add('hidden');
}

function previewCustomerPhotos(photos, custName) {
    if (!photos || photos.length === 0) return;
    const first = photos[0];
    const full = first.startsWith('http') || first.startsWith('/') ? first : '/' + first;
    openImageLightbox(full, `Ảnh của khách: ${custName || ''} (${photos.length} ảnh)`);
}
</script>

<style>
.ts-dropdown {
    z-index: 999999 !important;
}
</style>

@endsection
