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
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $color['bg'] }} {{ $color['text'] }} hover:shadow-sm border {{ $color['border'] }} transition-all" 
                                    title="Xem danh sách & thêm khách hàng vào nhóm">
                                    <iconify-icon icon="lucide:users" class="text-sm"></iconify-icon>
                                    <span>{{ $group->customers_count }} khách hàng</span>
                                    <iconify-icon icon="lucide:external-link" class="text-xs opacity-60"></iconify-icon>
                                </button>
                            </td>
                            <td class="py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
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

{{-- MODAL 4: XEM & THÊM KHÁCH HÀNG VÀO NHÓM THỊ TRƯỜNG --}}
<div id="modal-group-customers" class="fixed inset-0 bg-neutral-900/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-3xl w-full shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between shrink-0 bg-white">
            <div class="flex items-center gap-2">
                <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="text-2xl text-primary-600"></iconify-icon>
                <div>
                    <h5 class="font-bold text-base text-neutral-800 m-0">Khách hàng thuộc <span id="group-customers-title" class="text-primary-600"></span></h5>
                    <p class="text-xs text-neutral-500 m-0">Quản lý và thêm khách hàng trực tiếp vào nhóm</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('modal-group-customers')" class="text-neutral-400 hover:text-neutral-600 text-xl leading-none">&times;</button>
        </div>

        {{-- Form thêm nhanh khách hàng vào nhóm --}}
        <div class="p-4 bg-primary-50/50 border-b border-neutral-200 shrink-0">
            <form id="form-add-customers-to-group" method="POST" class="flex flex-wrap items-center gap-3">
                @csrf
                <div class="flex-1 min-w-[280px]">
                    <select name="customer_ids[]" id="select-customers-to-add" multiple placeholder="Chọn khách hàng để thêm vào nhóm này...">
                        @foreach($allCustomers as $c)
                            <option value="{{ $c->id }}">{{ $c->customer_code ? '[' . $c->customer_code . '] ' : '' }}{{ $c->name }} {{ $c->market_group_id ? '(Đã có nhóm)' : '(Chưa có nhóm)' }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary rounded-lg px-4 py-2 text-xs font-semibold shrink-0 flex items-center gap-1.5">
                    <iconify-icon icon="lucide:user-plus" class="text-sm"></iconify-icon> Thêm vào nhóm
                </button>
            </form>
        </div>

        {{-- Bảng danh sách khách hàng trong nhóm --}}
        <div class="p-6 overflow-y-auto flex-1">
            <div id="group-customers-loading" class="text-center py-10 text-neutral-400">
                <iconify-icon icon="lucide:loader-2" class="text-2xl animate-spin"></iconify-icon>
                <div class="text-xs mt-2">Đang tải danh sách khách hàng...</div>
            </div>

            <div id="group-customers-table-container" class="hidden">
                <table class="table bordered-table w-full mb-0 text-xs">
                    <thead>
                        <tr class="bg-neutral-50 text-neutral-600 font-semibold border-b border-neutral-200">
                            <th style="width: 50px;" class="text-center py-2.5">STT</th>
                            <th style="width: 110px;" class="py-2.5">Mã KH</th>
                            <th class="py-2.5">Tên khách hàng</th>
                            <th style="width: 120px;" class="py-2.5">Số điện thoại</th>
                            <th style="width: 140px;" class="text-right py-2.5">Công nợ</th>
                            <th style="width: 80px;" class="text-center py-2.5">Bỏ nhóm</th>
                        </tr>
                    </thead>
                    <tbody id="group-customers-tbody" class="divide-y divide-neutral-200">
                    </tbody>
                </table>
            </div>

            <div id="group-customers-empty" class="hidden text-center py-12 text-neutral-400">
                <iconify-icon icon="lucide:users" class="text-3xl text-neutral-300"></iconify-icon>
                <div class="text-xs mt-2 font-medium">Nhóm này chưa có khách hàng nào</div>
                <div class="text-[11px] text-neutral-400 mt-0.5">Sử dụng ô chọn phía trên để gán khách hàng vào nhóm này.</div>
            </div>
        </div>

        <div class="px-6 py-3 bg-neutral-50 border-t border-neutral-200 flex justify-end shrink-0">
            <button type="button" onclick="closeModal('modal-group-customers')" class="btn btn-sm btn-neutral rounded-lg px-5 py-2 text-xs font-semibold">Đóng</button>
        </div>
    </div>
</div>

<script>
let createGroupTs = null;
let editGroupTs = null;
let assignUsersTs = null;
let selectCustomersTs = null;
let currentActiveGroupId = null;

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
    const tbody = document.getElementById('group-customers-tbody');

    loading.classList.remove('hidden');
    tableContainer.classList.add('hidden');
    empty.classList.add('hidden');

    fetch('/market-groups/' + groupId + '/customers-data')
        .then(res => res.json())
        .then(data => {
            loading.classList.add('hidden');
            const customers = data.customers || [];
            if (customers.length === 0) {
                empty.classList.remove('hidden');
            } else {
                tableContainer.classList.remove('hidden');
                tbody.innerHTML = customers.map((c, idx) => `
                    <tr class="hover:bg-neutral-50 transition-colors">
                        <td class="text-center font-medium text-neutral-500 py-2.5">${idx + 1}</td>
                        <td class="font-semibold text-neutral-800 py-2.5">${c.customer_code || '—'}</td>
                        <td class="font-bold text-neutral-800 py-2.5">${c.name}</td>
                        <td class="text-neutral-600 py-2.5">${c.phone || '—'}</td>
                        <td class="text-right font-bold text-danger-600 py-2.5">${Number(c.debt || 0).toLocaleString('vi-VN')} ₫</td>
                        <td class="text-center py-2.5">
                            <form action="/market-groups/${groupId}/customers/${c.id}" method="POST" onsubmit="return confirm('Bỏ khách hàng ${c.name} khỏi nhóm này?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-danger-500 hover:text-danger-700 p-1 rounded hover:bg-danger-50" title="Bỏ khỏi nhóm">
                                    <iconify-icon icon="lucide:x-circle" class="text-base"></iconify-icon>
                                </button>
                            </form>
                        </td>
                    </tr>
                `).join('');
            }
        })
        .catch(err => {
            loading.classList.add('hidden');
            empty.classList.remove('hidden');
        });
}
</script>

<style>
.ts-dropdown {
    z-index: 999999 !important;
}
</style>

@endsection
