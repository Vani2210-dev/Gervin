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
            <button type="button" onclick="openImportStockModal('{{ request('warehouse_id') ?: ($selectedWarehouseId ?? '') }}')" class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm">
                <iconify-icon icon="solar:file-excel-bold" class="text-base"></iconify-icon> Nhập Excel tồn kho
            </button>
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


{{-- MODAL IMPORT TỒN KHO TỪ FILE EXCEL (DÙNG CHUNG TOÀN HỆ THỐNG KHO) --}}
@php
    $modalWarehouses = isset($warehouses) && count($warehouses) ? $warehouses : \App\Models\Warehouse::where('status', 'active')->orderBy('id')->get();
    $currentWhId = request('warehouse_id') ?: ($selectedWarehouseId ?? ($modalWarehouses->first()?->id ?? 1));
@endphp
<x-modal name="importStockModal" maxWidth="xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-primary-50 rounded-t-xl">
        <div class="flex items-center gap-2">
            <iconify-icon icon="solar:file-excel-bold" class="text-xl text-emerald-600"></iconify-icon>
            <h5 class="font-bold text-base text-primary-900 mb-0">Nhập tồn kho từ Excel</h5>
        </div>
        <button type="button" onclick="closeModal('importStockModal')" class="text-neutral-400 hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>

    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
        {{-- Section chọn kho hàng cần nhập tồn --}}
        <div class="p-4 bg-primary-50 border border-primary-200 rounded-xl">
            <label for="modal_import_warehouse_select" class="form-label text-xs font-bold text-neutral-800 mb-2 flex items-center gap-2">
                <iconify-icon icon="solar:box-bold" class="text-base text-primary-600"></iconify-icon>
                Chọn kho hàng cần nhập tồn: <span class="text-rose-500">*</span>
            </label>
            <select id="modal_import_warehouse_select" onchange="syncImportWarehouse(this.value)" class="form-select rounded-lg w-full border-neutral-300 px-4 py-2 text-xs font-semibold text-neutral-800 focus:border-primary-500 focus:ring-primary-500 bg-white">
                @foreach($modalWarehouses as $wh)
                    <option value="{{ $wh->id }}" {{ (string)$currentWhId === (string)$wh->id ? 'selected' : '' }}>
                        {{ $wh->name }} ({{ $wh->code }})
                    </option>
                @endforeach
            </select>
            <p class="text-[11px] text-neutral-500 mb-0 mt-2 flex items-center gap-1">
                <iconify-icon icon="solar:info-circle-outline" class="text-xs"></iconify-icon>
                Toàn bộ số lượng tồn kho trong file Excel sẽ được cập nhật trực tiếp vào kho đã chọn này.
            </p>
        </div>

        {{-- Card: File sẵn có Ton_Kho_Tam_Van_.xlsx --}}
        @php
            $serverFilePath = base_path('Ton_Kho_Tam_Van_.xlsx');
            $hasServerFile = file_exists($serverFilePath) || file_exists('C:/Code/Gervin/Ton_Kho_Tam_Van_.xlsx');
        @endphp
        @if($hasServerFile)
            <div class="p-4 bg-emerald-50 border-2 border-emerald-300 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm mt-1">
                        <iconify-icon icon="solar:file-excel-bold" class="text-2xl"></iconify-icon>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-emerald-950 flex items-center gap-2">
                            <span>Ton_Kho_Tam_Van_.xlsx</span>
                            <span class="bg-emerald-200 text-emerald-800 text-[10px] px-2 py-1 rounded-full font-bold">Sẵn có trên máy</span>
                        </div>
                        <p class="text-[11px] text-emerald-800 mb-0 mt-1">
                            File kiểm kê tồn kho tấm ván thực tế (654 SKU - 37.242 tấm).
                        </p>
                    </div>
                </div>
                <form action="{{ route('inventory.import-stock') }}" method="POST" class="m-0 shrink-0">
                    @csrf
                    <input type="hidden" name="use_server_file" value="1">
                    <input type="hidden" name="warehouse_id" class="import-stock-target-wh" value="{{ $currentWhId }}">
                    <button type="submit" class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm w-full sm:w-auto justify-center">
                        <iconify-icon icon="solar:play-circle-bold" class="text-base"></iconify-icon> Nhập file này ngay
                    </button>
                </form>
            </div>

            <div class="flex items-center my-4">
                <div class="flex-grow border-t border-neutral-200"></div>
                <span class="flex-shrink mx-4 text-[11px] font-bold text-neutral-400 uppercase tracking-wider">Hoặc tải lên file Excel mới</span>
                <div class="flex-grow border-t border-neutral-200"></div>
            </div>
        @endif

        {{-- Form tải file mới --}}
        <form action="{{ route('inventory.import-stock') }}" method="POST" enctype="multipart/form-data" class="space-y-4 m-0">
            @csrf
            <input type="hidden" name="warehouse_id" class="import-stock-target-wh" value="{{ $currentWhId }}">

            <div class="p-4 bg-neutral-50 border border-neutral-200 rounded-xl text-xs text-neutral-700 space-y-2">
                <div class="font-bold text-neutral-900 flex items-center gap-2">
                    <iconify-icon icon="solar:info-circle-bold" class="text-base text-primary-600"></iconify-icon>
                    Quy chuẩn các cột trong file Excel:
                </div>
                <ul class="list-disc pl-6 space-y-2 text-neutral-600 text-[11px]">
                    <li>Cột B: <strong>Mã SKU</strong></li>
                    <li>Cột C: <strong>Tên hàng hóa / vật tư</strong></li>
                    <li>Cột D: <strong>Tên hàng Foil thay thế</strong></li>
                    <li>Cột E: <strong>Mã xuất xứ</strong> (NCC)</li>
                    <li>Cột F: <strong>Nhóm hàng</strong></li>
                    <li>Cột G: <strong>Đơn vị tính</strong> (Tấm)</li>
                    <li>Cột H: <strong>Tồn kho kiểm kê</strong></li>
                </ul>
            </div>

            <div>
                <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Chọn file Excel từ máy tính (.xlsx, .xls) <span class="text-rose-500">*</span></label>
                <input type="file" name="excel_file" accept=".xlsx,.xls" required class="form-control rounded-lg w-full border-neutral-300 px-4 py-2 text-xs focus:border-primary-500">
            </div>

            <div class="border-t border-neutral-200 pt-4 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('importStockModal')" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-4 py-2 rounded-lg text-xs font-medium">Đóng</button>
                <button type="submit" class="btn btn-primary px-6 py-2 rounded-lg text-xs font-bold shadow-sm flex items-center gap-2">
                    <iconify-icon icon="solar:upload-track-linear" class="text-base"></iconify-icon> Tải lên & Nhập tồn kho
                </button>
            </div>
        </form>
    </div>
</x-modal>

<script>
    function openImportStockModal(preferredWarehouseId) {
        var whId = preferredWarehouseId || '{{ $currentWhId }}';
        var sel = document.getElementById('modal_import_warehouse_select');
        if (sel && whId) {
            sel.value = whId;
            syncImportWarehouse(whId);
        }
        openModal('importStockModal');
    }

    function syncImportWarehouse(val) {
        document.querySelectorAll('.import-stock-target-wh').forEach(function(el) {
            el.value = val;
        });
    }
</script>
