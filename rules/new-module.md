---
trigger: manual
---
Database chỉ dùng tiếng anh.

Khi tạo module mới thì hãy tạo database đầu tiên, sau đó tạo các quyền xem, thêm, sửa, xóa, (duyệt nếu cần) cho module đó và đưa nó vào:

database\seeders\PermissionSeeder.php

Sau đó tạo controller, model, migration, request, resource cho module đó
Phần phân quyền các chức năng trong controller dùng theo ví dụ như sau:
    public function __construct()
    {
        $this->middleware('permission:view supply',   ['only' => ['index']]);
        $this->middleware('permission:add supply',    ['only' => ['store']]);
        $this->middleware('permission:edit supply',   ['only' => ['update']]);
        $this->middleware('permission:delete supply', ['only' => ['destroy']]);
    }

Sử dụng giao diện UI cho giống: resources\views\users\index.blade.php.

UI nút lọc và xóa lọc (sử dụng cho các module có bảng):
```blade
<button type="button" onclick="openModal('filter-modal')"
    class="btn bg-light-600 text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
    <iconify-icon icon="solar:filter-outline" class="icon text-xl line-height-1"></iconify-icon>
    Lọc
</button>
@if(request()->filled('filter_name') || request()->filled('filter_category') || request()->filled('filter_unit') || request()->filled('filter_min_stock') || request()->filled('filter_max_stock') || request()->filled('filter_min_price') || request()->filled('filter_max_price'))
<a href="{{ route('module.index') }}" class="btn text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
    <iconify-icon icon="solar:close-circle-outline" class="icon text-xl line-height-1"></iconify-icon>
    Xóa lọc
</a>
@endif
```


