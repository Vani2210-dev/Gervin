@php
    $role = $role ?? null;
    $action = $action ?? route('roles.store');
    $method = $method ?? 'POST';
    $selectedPermissions = $selectedPermissions ?? old('permissions', []);

    $moduleLabels = [
        'user'          => 'Người dùng',
        'role'          => 'Vai trò',
        'media'         => 'Tệp tin',
        'supply'        => 'Vật tư',
        'customer'      => 'Khách hàng',
        'acrylic order' => 'Đơn acrylic',
        'glass order'   => 'Đơn kính',
        'min late order'=> 'Đơn min-late',
        'manufacture'   => 'Lệnh sản xuất',
        'warehouse'     => 'Kho',
        'pressing'      => 'Ép ván',
        'cnc'           => 'Cắt CNC',
        'edge banding'  => 'Dán cạnh',
        'finishing'     => 'Làm đẹp',
        'qc'            => 'Kiểm soát (QC)',
        'packing'       => 'Đóng gói',
        'shipped'       => 'Xuất xưởng',
        'ui'            => 'Giao diện UI',
    ];
    $actionLabels = [
        'view'     => 'Xem',
        'add'      => 'Thêm',
        'edit'     => 'Sửa',
        'delete'   => 'Xóa',
        'approve'  => 'Duyệt',
        'complete' => 'Hoàn thành',
    ];

    $grouped = [];
    foreach ($permissions as $permission) {
        $parts = explode(' ', $permission->name, 2);
        $action_ = $parts[0] ?? '';
        $module  = $parts[1] ?? 'other';
        $grouped[$module][] = $permission;
    }
@endphp

<form action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif

    <div class="grid grid-cols-12 gap-6">

        {{-- Tên vai trò --}}
        <div class="col-span-12 md:col-span-6">
            <div class="form-group">
                <label class="form-label">Tên vai trò <span class="text-danger-500">*</span></label>
                <input type="text" class="form-control" name="name"
                    value="{{ old('name', $role?->name) }}"
                    placeholder="Nhập tên vai trò" required>
                @error('name')
                    <div class="text-danger-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Quyền hạn theo nhóm --}}
        <div class="col-span-12">
            <label class="form-label mb-3">Quyền hạn</label>
            @error('permissions')
                <div class="text-danger-500 text-sm mb-2">{{ $message }}</div>
            @enderror
            <div class="grid grid-cols-2 xl:grid-cols-6 gap-4">
                @foreach($grouped as $module => $perms)
                @php $moduleId = 'module-' . Str::slug($module); @endphp
                <div class="col-span-1">
                    <div class="border border-neutral-200 dark:border-neutral-600 rounded-xl p-4 h-full">
                        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-neutral-200 dark:border-neutral-600">
                            <input class="form-check-input rounded border input-form-dark select-all-checkbox"
                                type="checkbox"
                                id="all-{{ $moduleId }}"
                                data-module="{{ $moduleId }}">
                            <label class="font-semibold text-sm text-secondary-light cursor-pointer mb-0" for="all-{{ $moduleId }}">
                                {{ $moduleLabels[$module] ?? ucfirst($module) }}
                            </label>
                        </div>
                        <div class="flex flex-col gap-2" id="{{ $moduleId }}">
                            @foreach($perms as $permission)
                            @php $actionKey = explode(' ', $permission->name)[0]; @endphp
                            <div class="form-check style-check">
                                <input class="form-check-input rounded border input-form-dark module-checkbox"
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission->name }}"
                                    id="perm-{{ $permission->id }}"
                                    data-module="{{ $moduleId }}"
                                    {{ in_array($permission->name, $selectedPermissions) ? 'checked' : '' }}>
                                <label class="form-check-label" for="perm-{{ $permission->id }}">
                                    {{ $actionLabels[$actionKey] ?? ucfirst($actionKey) }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Nút hành động --}}
        <div class="col-span-12">
            <div class="flex items-center gap-3">
                <button type="submit" class="btn btn-primary px-6 py-2.5 rounded-lg">
                    {{ $method === 'PUT' ? 'Cập nhật' : 'Lưu' }}
                </button>
                <a href="{{ route('roles.index') }}" class="btn btn-neutral px-6 py-2.5 rounded-lg">
                    Hủy
                </a>
            </div>
        </div>

    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Chọn tất cả trong module
    document.querySelectorAll('.select-all-checkbox').forEach(function (selectAll) {
        const moduleId = selectAll.getAttribute('data-module');
        const checkboxes = document.querySelectorAll(`.module-checkbox[data-module="${moduleId}"]`);

        // Khởi tạo trạng thái ban đầu
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        const someChecked = Array.from(checkboxes).some(cb => cb.checked);
        selectAll.checked = allChecked;
        selectAll.indeterminate = !allChecked && someChecked;

        // Khi bấm "chọn tất cả"
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        // Khi thay đổi checkbox con → cập nhật trạng thái "chọn tất cả"
        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', function () {
                const allChecked = Array.from(checkboxes).every(c => c.checked);
                const someChecked = Array.from(checkboxes).some(c => c.checked);
                selectAll.checked = allChecked;
                selectAll.indeterminate = !allChecked && someChecked;
            });
        });
    });
});
</script>
