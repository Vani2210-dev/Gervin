@extends('layout.layout')
@php
    $title    = 'Quản lý tồn kho';
    $subTitle = 'Danh sách Kho hàng';
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        @include('inventory.partials.navbar')

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-success-100 border border-success-300 text-success-700 rounded-lg px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="mb-6 bg-rose-100 border border-rose-300 text-rose-700 rounded-lg px-4 py-3 text-sm">
                <ul class="list-disc pl-5 mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Main Warehouse Card --}}
        <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
            <div class="p-4 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h5 class="font-bold text-neutral-800 text-base mb-1">Danh sách Kho & Địa điểm lưu trữ</h5>
                    <p class="text-xs text-neutral-500 mb-0">Quản lý mạng lưới kho nguyên vật liệu, phân xưởng sản xuất và chi nhánh.</p>
                </div>
                <button type="button" onclick="openCreateWarehouseModal()" class="btn btn-primary px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm">
                    <iconify-icon icon="ic:baseline-plus" class="text-base"></iconify-icon> Thêm kho mới
                </button>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-12 gap-6">
                    @forelse($warehouses as $w)
                        <div class="col-span-12 md:col-span-6 lg:col-span-4">
                            <div class="p-5 rounded-2xl border border-neutral-200 bg-white hover:border-primary-400 hover:shadow-md transition-all flex flex-col justify-between h-full">
                                <div>
                                    <div class="flex items-start justify-between gap-2 mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center font-bold">
                                                <iconify-icon icon="solar:box-bold" class="text-2xl"></iconify-icon>
                                            </div>
                                            <div>
                                                <h6 class="font-bold text-neutral-900 text-sm mb-0">{{ $w->name }}</h6>
                                                <span class="text-[11px] font-mono font-semibold text-neutral-400">{{ $w->code }}</span>
                                            </div>
                                        </div>

                                        @if(($w->status ?? 'active') === 'active')
                                            <span class="px-2 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md text-[10px] font-bold">
                                                Hoạt động
                                            </span>
                                        @else
                                            <span class="px-2 py-1 bg-neutral-100 text-neutral-600 rounded-md text-[10px] font-bold">
                                                Tạm ngừng
                                            </span>
                                        @endif
                                    </div>

                                    <div class="space-y-2 text-xs text-neutral-600 mb-4">
                                        <div class="flex items-center gap-2">
                                            <iconify-icon icon="solar:user-bold" class="text-neutral-400 text-sm"></iconify-icon>
                                            <span>Thủ kho: <strong>{{ $w->manager ?: 'Chưa phân công' }}</strong></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <iconify-icon icon="solar:map-point-bold" class="text-neutral-400 text-sm"></iconify-icon>
                                            <span class="truncate">Địa chỉ: {{ $w->address ?: 'Tại xưởng sản xuất Gervin' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <iconify-icon icon="solar:layers-minimalistic-bold" class="text-neutral-400 text-sm"></iconify-icon>
                                            <span>Mặt hàng lưu: <strong class="text-primary-600">{{ $w->materials_count }}</strong> loại vật tư</span>
                                        </div>
                                        @if($w->description)
                                            <p class="text-[11px] text-neutral-500 italic mt-2 line-clamp-2">
                                                "{{ $w->description }}"
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-neutral-100 flex items-center justify-between gap-2">
                                    <a href="{{ route('inventory.index', ['warehouse_id' => $w->id]) }}" class="text-xs font-bold text-primary-600 hover:text-primary-800 flex items-center gap-1">
                                        <iconify-icon icon="solar:eye-bold" class="text-sm"></iconify-icon> Xem vật tư
                                    </a>

                                    <div class="flex items-center gap-1">
                                        <button type="button" 
                                                data-warehouse="{{ htmlspecialchars(json_encode($w), ENT_QUOTES, 'UTF-8') }}"
                                                onclick="openEditWarehouseModal(this)" 
                                                class="p-2 text-neutral-400 hover:text-primary-600 hover:bg-neutral-50 rounded-lg transition-colors cursor-pointer" 
                                                title="Chỉnh sửa kho hàng">
                                            <iconify-icon icon="solar:pen-bold" class="text-base"></iconify-icon>
                                        </button>
                                        @if($w->materials_count == 0)
                                            <form action="{{ route('inventory.warehouses.destroy', $w) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa kho này?');" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-neutral-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg" title="Xóa">
                                                    <iconify-icon icon="solar:trash-bin-trash-bold" class="text-base"></iconify-icon>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-12 py-12 text-center text-neutral-400">
                            Chưa có kho hàng nào. Bấm "Thêm kho mới" để tạo kho đầu tiên.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Thêm mới Kho hàng --}}
<div id="createWarehouseModal" class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/60 hidden" onclick="if(event.target === this) closeCreateWarehouseModal()">
    <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl relative" onclick="event.stopPropagation()">
        <form action="{{ route('inventory.warehouses.store') }}" method="POST">
            @csrf
            <div class="p-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50">
                <h6 class="font-bold text-neutral-800 text-sm mb-0">Thêm kho hàng mới</h6>
                <button type="button" onclick="closeCreateWarehouseModal()" class="text-neutral-400 hover:text-neutral-800 p-1 rounded-lg">
                    <iconify-icon icon="solar:close-circle-bold" class="text-2xl"></iconify-icon>
                </button>
            </div>
            <div class="p-6 space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Tên kho hàng <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" class="form-control rounded-lg px-3 py-2 border-neutral-300 text-xs w-full" placeholder="Ví dụ: Kho Acrylic TQ, Kho Phụ kiện..." required>
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Mã kho (tùy chọn)</label>
                    <input type="text" name="code" class="form-control rounded-lg px-3 py-2 border-neutral-300 text-xs w-full" placeholder="Ví dụ: KHO-ACRYLIC">
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Thủ kho / Quản lý</label>
                    <input type="text" name="manager" class="form-control rounded-lg px-3 py-2 border-neutral-300 text-xs w-full" placeholder="Tên nhân sự quản lý kho">
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Địa chỉ kho</label>
                    <input type="text" name="address" class="form-control rounded-lg px-3 py-2 border-neutral-300 text-xs w-full" placeholder="Xưởng sản xuất hoặc chi nhánh...">
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Ghi chú / Mô tả</label>
                    <textarea name="description" rows="2" class="form-control rounded-lg px-3 py-2 border-neutral-300 text-xs w-full" placeholder="Mô tả chức năng kho..."></textarea>
                </div>
            </div>
            <div class="p-4 border-t border-neutral-200 bg-neutral-50 flex items-center justify-end gap-2">
                <button type="button" onclick="closeCreateWarehouseModal()" class="btn btn-secondary px-4 py-2 rounded-lg text-xs font-bold text-neutral-600 border border-neutral-300">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-lg text-xs font-bold shadow-sm">
                    Lưu kho hàng
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Chỉnh sửa Kho hàng --}}
<div id="editWarehouseModal" class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/60 hidden" onclick="if(event.target === this) closeEditWarehouseModal()">
    <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl relative" onclick="event.stopPropagation()">
        <form id="editWarehouseForm" method="POST">
            @csrf
            @method('PUT')
            <div class="p-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50">
                <h6 class="font-bold text-neutral-800 text-sm mb-0">Cập nhật thông tin kho hàng</h6>
                <button type="button" onclick="closeEditWarehouseModal()" class="text-neutral-400 hover:text-neutral-800 p-1 rounded-lg">
                    <iconify-icon icon="solar:close-circle-bold" class="text-2xl"></iconify-icon>
                </button>
            </div>
            <div class="p-6 space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Tên kho hàng <span class="text-rose-500">*</span></label>
                    <input type="text" id="edit_name" name="name" class="form-control rounded-lg px-3 py-2 border-neutral-300 text-xs w-full" required>
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Mã kho</label>
                    <input type="text" id="edit_code" name="code" class="form-control rounded-lg px-3 py-2 border-neutral-300 text-xs w-full">
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Thủ kho / Quản lý</label>
                    <input type="text" id="edit_manager" name="manager" class="form-control rounded-lg px-3 py-2 border-neutral-300 text-xs w-full">
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Địa chỉ kho</label>
                    <input type="text" id="edit_address" name="address" class="form-control rounded-lg px-3 py-2 border-neutral-300 text-xs w-full">
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Trạng thái hoạt động</label>
                    <select id="edit_status" name="status" class="form-select rounded-lg px-3 py-2 border-neutral-300 text-xs w-full">
                        <option value="active">Đang hoạt động</option>
                        <option value="inactive">Tạm ngưng</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-neutral-700 mb-1">Ghi chú / Mô tả</label>
                    <textarea id="edit_description" name="description" rows="2" class="form-control rounded-lg px-3 py-2 border-neutral-300 text-xs w-full"></textarea>
                </div>
            </div>
            <div class="p-4 border-t border-neutral-200 bg-neutral-50 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditWarehouseModal()" class="btn btn-secondary px-4 py-2 rounded-lg text-xs font-bold text-neutral-600 border border-neutral-300">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-lg text-xs font-bold shadow-sm">
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateWarehouseModal() {
        document.getElementById('createWarehouseModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeCreateWarehouseModal() {
        document.getElementById('createWarehouseModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function openEditWarehouseModal(btnOrObj) {
        let warehouse = btnOrObj;
        if (btnOrObj instanceof HTMLElement) {
            try {
                warehouse = JSON.parse(btnOrObj.getAttribute('data-warehouse'));
            } catch (e) {
                console.error("Lỗi parse data-warehouse:", e);
                return;
            }
        }
        if (!warehouse) return;

        document.getElementById('editWarehouseForm').action = `/warehouses/list/${warehouse.id}`;
        document.getElementById('edit_name').value = warehouse.name || '';
        document.getElementById('edit_code').value = warehouse.code || '';
        document.getElementById('edit_manager').value = warehouse.manager || '';
        document.getElementById('edit_address').value = warehouse.address || '';
        document.getElementById('edit_status').value = warehouse.status || 'active';
        document.getElementById('edit_description').value = warehouse.description || '';

        document.getElementById('editWarehouseModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeEditWarehouseModal() {
        document.getElementById('editWarehouseModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateWarehouseModal();
            closeEditWarehouseModal();
        }
    });
</script>
@endsection
