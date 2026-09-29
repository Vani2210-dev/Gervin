{{-- Inventory Sub-Navigation Bar (KiotViet / MISA style) --}}
@php
    $currentRoute = request()->route() ? request()->route()->getName() : '';
@endphp
<div class="card p-0 rounded-xl border-0 mb-6 bg-white shadow-sm overflow-hidden">
    <div class="p-4 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center">
                <iconify-icon icon="solar:box-minimalistic-bold" class="text-2xl"></iconify-icon>
            </div>
            <div>
                <h4 class="text-lg font-bold text-neutral-800 mb-0">Hệ thống Quản lý Tồn kho & Vật tư</h4>
                <p class="text-xs text-neutral-500 mb-0">Quản lý định mức, theo dõi xuất nhập tồn thời gian thực và kiểm kê cân bằng kho chuẩn kế toán.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('inventory.receipts.create') }}" class="btn btn-sm btn-primary px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm">
                <iconify-icon icon="solar:import-bold" class="text-base"></iconify-icon> Nhập kho (PNK)
            </a>
            <a href="{{ route('inventory.issues.create') }}" class="btn btn-sm bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm">
                <iconify-icon icon="solar:export-bold" class="text-base"></iconify-icon> Xuất kho (PXK)
            </a>
            <a href="{{ route('inventory.stocktakes.create') }}" class="btn btn-sm bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm">
                <iconify-icon icon="solar:checklist-minimalistic-bold" class="text-base"></iconify-icon> Kiểm kê kho (PKK)
            </a>
        </div>
    </div>

    {{-- Horizontal Tabs Bar --}}
    <div class="flex flex-wrap items-center gap-1 px-4 pt-2 bg-neutral-50 border-t border-neutral-100 overflow-x-auto text-xs font-bold">
        <a href="{{ route('inventory.index') }}" 
           class="flex items-center gap-2 px-4 py-3 border-b-2 transition-all {{ $currentRoute === 'inventory.index' ? 'border-primary-600 text-primary-600 bg-white rounded-t-lg shadow-sm' : 'border-transparent text-neutral-600 hover:text-neutral-900 hover:border-neutral-300' }}">
            <iconify-icon icon="solar:widget-2-bold" class="text-base"></iconify-icon>
            Tồn kho & Hàng hóa
        </a>

        <a href="{{ route('inventory.receipts.index') }}" 
           class="flex items-center gap-2 px-4 py-3 border-b-2 transition-all {{ str_starts_with($currentRoute, 'inventory.receipts') ? 'border-primary-600 text-primary-600 bg-white rounded-t-lg shadow-sm' : 'border-transparent text-neutral-600 hover:text-neutral-900 hover:border-neutral-300' }}">
            <iconify-icon icon="solar:import-outline" class="text-base"></iconify-icon>
            Phiếu Nhập kho
        </a>

        <a href="{{ route('inventory.issues.index') }}" 
           class="flex items-center gap-2 px-4 py-3 border-b-2 transition-all {{ str_starts_with($currentRoute, 'inventory.issues') ? 'border-primary-600 text-primary-600 bg-white rounded-t-lg shadow-sm' : 'border-transparent text-neutral-600 hover:text-neutral-900 hover:border-neutral-300' }}">
            <iconify-icon icon="solar:export-outline" class="text-base"></iconify-icon>
            Phiếu Xuất kho
        </a>

        <a href="{{ route('inventory.stocktakes.index') }}" 
           class="flex items-center gap-2 px-4 py-3 border-b-2 transition-all {{ str_starts_with($currentRoute, 'inventory.stocktakes') ? 'border-primary-600 text-primary-600 bg-white rounded-t-lg shadow-sm' : 'border-transparent text-neutral-600 hover:text-neutral-900 hover:border-neutral-300' }}">
            <iconify-icon icon="solar:checklist-bold" class="text-base"></iconify-icon>
            Kiểm kê kho
            <span class="bg-amber-100 text-amber-800 text-[10px] px-2 py-1 rounded-full font-bold">Cân bằng</span>
        </a>

        <a href="{{ route('inventory.reports.index') }}" 
           class="flex items-center gap-2 px-4 py-3 border-b-2 transition-all {{ str_starts_with($currentRoute, 'inventory.reports') ? 'border-primary-600 text-primary-600 bg-white rounded-t-lg shadow-sm' : 'border-transparent text-neutral-600 hover:text-neutral-900 hover:border-neutral-300' }}">
            <iconify-icon icon="solar:chart-square-bold" class="text-base"></iconify-icon>
            Báo cáo Xuất-Nhập-Tồn & Ma trận
        </a>

        <a href="{{ route('inventory.warehouses.index') }}" 
           class="flex items-center gap-2 px-4 py-3 border-b-2 transition-all {{ str_starts_with($currentRoute, 'inventory.warehouses') ? 'border-primary-600 text-primary-600 bg-white rounded-t-lg shadow-sm' : 'border-transparent text-neutral-600 hover:text-neutral-900 hover:border-neutral-300' }}">
            <iconify-icon icon="solar:warehouse-outline" class="text-base"></iconify-icon>
            Danh sách Kho
        </a>
    </div>
</div>
