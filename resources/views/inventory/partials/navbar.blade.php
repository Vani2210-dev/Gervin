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
           class="flex items-center gap-2 px-4 py-3 border-b-2 transition-all {{ (str_starts_with($currentRoute, 'inventory.receipts') || str_starts_with($currentRoute, 'inventory.issues')) ? 'border-primary-600 text-primary-600 bg-white rounded-t-lg shadow-sm' : 'border-transparent text-neutral-600 hover:text-neutral-900 hover:border-neutral-300' }}">
            <iconify-icon icon="solar:clipboard-list-bold" class="text-base"></iconify-icon>
            Nhập, xuất kho
        </a>

        <a href="{{ route('inventory.stocktakes.index') }}" 
           class="flex items-center gap-2 px-4 py-3 border-b-2 transition-all {{ str_starts_with($currentRoute, 'inventory.stocktakes') ? 'border-primary-600 text-primary-600 bg-white rounded-t-lg shadow-sm' : 'border-transparent text-neutral-600 hover:text-neutral-900 hover:border-neutral-300' }}">
            <iconify-icon icon="solar:checklist-bold" class="text-base"></iconify-icon>
            Kiểm kê kho
            <span class="bg-amber-100 text-amber-800 text-[10px] px-2 py-1 rounded-full font-bold">Cân bằng</span>
        </a>

        <a href="{{ route('inventory.warehouses.index') }}" 
           class="flex items-center gap-2 px-4 py-3 border-b-2 transition-all {{ str_starts_with($currentRoute, 'inventory.warehouses') ? 'border-primary-600 text-primary-600 bg-white rounded-t-lg shadow-sm' : 'border-transparent text-neutral-600 hover:text-neutral-900 hover:border-neutral-300' }}">
            <iconify-icon icon="solar:warehouse-outline" class="text-base"></iconify-icon>
            Danh sách Kho
        </a>

        <a href="{{ route('suppliers.index') }}" 
           class="flex items-center gap-2 px-4 py-3 border-b-2 transition-all {{ str_starts_with($currentRoute, 'suppliers') ? 'border-primary-600 text-primary-600 bg-white rounded-t-lg shadow-sm' : 'border-transparent text-neutral-600 hover:text-neutral-900 hover:border-neutral-300' }}">
            <iconify-icon icon="solar:shop-2-bold" class="text-base"></iconify-icon>
            Nhà cung cấp & Công nợ
        </a>
    </div>
</div>


{{-- MODAL IMPORT TỒN KHO TỪ FILE EXCEL (DÙNG CHUNG TOÀN HỆ THỐNG KHO) --}}
@php
    $modalWarehouses = isset($warehouses) && count($warehouses) ? $warehouses : \App\Models\Warehouse::where('status', 'active')->orderBy('id')->get();
    $currentWhId = request('warehouse_id') ?: ($selectedWarehouseId ?? '');
    $serverFiles = \App\Services\InventoryService::getServerExcelFiles();
    $serverFilePath = base_path('mã.xlsx');
    $hasServerMasterFile = file_exists($serverFilePath) || file_exists('C:/Code/Gervin/mã.xlsx');
    $hasServerFiles = !empty($serverFiles) || $hasServerMasterFile;
@endphp
<x-modal name="importStockModal" maxWidth="xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-emerald-50 rounded-t-xl">
        <div class="flex items-center gap-2">
            <iconify-icon icon="solar:file-excel-bold" class="text-xl text-emerald-600"></iconify-icon>
            <h5 class="font-bold text-base text-neutral-800 mb-0">Nhập Danh mục Vật tư từ Excel</h5>
        </div>
        <button type="button" onclick="closeModal('importStockModal')" class="text-neutral-400 hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>

    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
        {{-- Section chọn kho hàng cần nhập tồn --}}
        <div class="p-4 bg-neutral-50 border border-neutral-200 rounded-xl">
            <label for="modal_import_warehouse_select" class="form-label text-xs font-bold text-neutral-800 mb-2 flex items-center gap-2">
                <iconify-icon icon="solar:box-bold" class="text-base text-primary-600"></iconify-icon>
                Kho đích áp dụng:
            </label>
            <select id="modal_import_warehouse_select" onchange="syncImportWarehouse(this.value)" class="form-select rounded-lg w-full border-neutral-300 px-4 py-2 text-xs font-semibold text-neutral-800 focus:border-primary-500 focus:ring-primary-500 bg-white">
                <option value="" data-code="">-- Tất cả kho (Tự động nạp toàn bộ 24 kho từ máy chủ & tạo kho chuẩn) --</option>
                @foreach($modalWarehouses as $wh)
                    <option value="{{ $wh->id }}" data-code="{{ $wh->code }}" {{ (string)$currentWhId === (string)$wh->id ? 'selected' : '' }}>
                        {{ $wh->name }} ({{ $wh->code }})
                    </option>
                @endforeach
            </select>
            <p class="text-[11px] text-neutral-500 mb-0 mt-2 flex items-center gap-1">
                <iconify-icon icon="solar:info-circle-outline" class="text-xs text-primary-600"></iconify-icon>
                Hệ thống chỉ nhập danh mục mã vật tư (không cần số lượng tồn). Chọn "Tất cả kho" để tự động tạo kho chuẩn và nạp toàn bộ 24 file kho từ máy chủ.
            </p>
        </div>

        {{-- Card: File sẵn có trên máy chủ (p_code_excels / mã.xlsx) --}}
        @if($hasServerFiles)
            <div class="p-4 bg-emerald-50 border-2 border-emerald-300 rounded-xl space-y-3 shadow-2xs">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm mt-0.5">
                            <iconify-icon icon="solar:server-square-bold" class="text-2xl"></iconify-icon>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-emerald-950 flex flex-wrap items-center gap-2">
                                <span>Kho file máy chủ (p_code_excels)</span>
                                @if(!empty($serverFiles))
                                    <span class="bg-emerald-200 text-emerald-900 text-[10px] px-2 py-0.5 rounded-full font-bold">{{ count($serverFiles) }} file kho sẵn sàng</span>
                                @endif
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] px-2 py-0.5 rounded-full font-bold border border-emerald-300">Tự tạo kho chuẩn</span>
                            </div>
                            <p class="text-[11px] text-emerald-800 mb-0 mt-1">
                                Tự động chuẩn hóa 24 kho hàng (mã, tên, mô tả, loại hàng) và nạp danh mục mã vật tư.
                            </p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('inventory.import-stock') }}" method="POST" class="m-0 space-y-3">
                    @csrf
                    <input type="hidden" name="use_server_file" value="1">
                    <input type="hidden" name="warehouse_id" class="import-stock-target-wh" value="{{ $currentWhId }}">

                    <div>
                        <label for="server_file_select" class="block text-xs font-bold text-emerald-950 mb-1">
                            Chọn nguồn file máy chủ:
                        </label>
                        <select id="server_file_select" name="server_file" onchange="syncServerFileToWarehouse(this.value)" class="form-select rounded-lg w-full border-emerald-300 bg-white text-xs font-semibold text-neutral-800 focus:border-emerald-600 focus:ring-emerald-600 py-2">
                            <option value="all_p_code_excels" data-wh-code="">[Tất cả kho] Toàn bộ 24 file kho (Tự động nạp 1.529 mã & tạo kho chuẩn)</option>
                            @foreach($serverFiles as $sFile)
                                <option value="{{ $sFile['filename'] }}" data-wh-code="{{ $sFile['warehouse_code'] }}" data-wh-name="{{ $sFile['warehouse_name'] }}">
                                    [{{ $sFile['warehouse_code'] }}] {{ $sFile['warehouse_name'] }} ({{ $sFile['filename'] }}) - {{ $sFile['items_count'] }} mã
                                </option>
                            @endforeach
                            @if($hasServerMasterFile)
                                <option value="ma.xlsx" data-wh-code="">[File tổng] mã.xlsx (File tổng hợp 24 kho gốc)</option>
                            @endif
                        </select>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <span class="text-[11px] text-emerald-700 italic flex items-center gap-1">
                            <iconify-icon icon="solar:check-circle-bold" class="text-emerald-600 text-xs"></iconify-icon>
                            Tự động đồng bộ với kho đích đã chọn ở trên
                        </span>
                        <button type="submit" onclick="return confirm('Xác nhận nạp danh mục vật tư từ máy chủ? Hệ thống sẽ tự động đồng bộ và tạo chuẩn các kho hàng.')" class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm cursor-pointer">
                            <iconify-icon icon="solar:upload-track-bold" class="text-base"></iconify-icon> Nạp từ file máy chủ
                        </button>
                    </div>
                </form>
            </div>

            <div class="flex items-center my-4">
                <div class="flex-grow border-t border-neutral-200"></div>
                <span class="flex-shrink mx-4 text-[11px] font-bold text-neutral-400 uppercase tracking-wider">Hoặc tải file lên từ máy tính</span>
                <div class="flex-grow border-t border-neutral-200"></div>
            </div>
        @endif

        {{-- Form tải file mới --}}
        <form action="{{ route('inventory.import-stock') }}" method="POST" enctype="multipart/form-data" class="space-y-4 m-0">
            @csrf
            <input type="hidden" name="warehouse_id" class="import-stock-target-wh" value="{{ $currentWhId }}">

            <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-900 space-y-1">
                <div class="font-bold flex items-center gap-1.5 mb-1">
                    <iconify-icon icon="solar:info-circle-bold" class="text-base text-blue-600"></iconify-icon>
                    Cấu trúc các cột trong file Excel (mã.xlsx hoặc file từng kho):
                </div>
                <ul class="list-disc pl-5 space-y-0.5 text-[11px]">
                    <li><strong>Cột B:</strong> Tên kho (Vd: <em>152 - KÍNH, 152_ACRYLIC, 153_PK, 152_COT, 155_Thành Phẩm...</em>)</li>
                    <li><strong>Cột C:</strong> Mã hàng (Mã SKU vật tư)</li>
                    <li><strong>Cột D:</strong> Tên hàng hóa / vật tư</li>
                    <li><strong>Cột E:</strong> Đơn vị tính (Tấm, Cái, Mét, Kg, Hộp, Cây...)</li>
                    <li><strong>Cột F:</strong> Nguồn gốc / Xuất xứ NCC (nếu có)</li>
                </ul>
            </div>

            <div>
                <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Chọn file Excel từ máy tính (.xlsx, .xls) <span class="text-rose-500">*</span></label>
                <input type="file" name="excel_file" accept=".xlsx,.xls" required class="form-control rounded-lg w-full border-neutral-300 px-4 py-2 text-xs focus:border-primary-500">
            </div>

            <div class="border-t border-neutral-200 pt-4 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModal('importStockModal')" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-4 py-2 rounded-lg text-xs font-medium">Đóng</button>
                <button type="submit" class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg text-xs font-bold shadow-sm flex items-center gap-2 cursor-pointer">
                    <iconify-icon icon="solar:file-excel-bold" class="text-base"></iconify-icon> Tiến hành Nhập
                </button>
            </div>
        </form>
    </div>
</x-modal>

<script>
    function openImportStockModal(preferredWarehouseId) {
        var whId = (preferredWarehouseId !== undefined && preferredWarehouseId !== null) ? preferredWarehouseId : '{{ $currentWhId }}';
        var sel = document.getElementById('modal_import_warehouse_select');
        if (sel) {
            sel.value = whId;
            syncImportWarehouse(whId);
        }
        openModal('importStockModal');
    }

    function syncImportWarehouse(val) {
        document.querySelectorAll('.import-stock-target-wh').forEach(function(el) {
            el.value = val || '';
        });

        // Tự động đồng bộ dropdown file máy chủ tương ứng
        var whSelect = document.getElementById('modal_import_warehouse_select');
        var serverSelect = document.getElementById('server_file_select');
        if (!whSelect || !serverSelect) return;

        if (!val) {
            serverSelect.value = 'all_p_code_excels';
            return;
        }

        var opt = whSelect.options[whSelect.selectedIndex];
        var whCode = opt ? (opt.getAttribute('data-code') || '').trim() : '';
        if (!whCode) return;

        var found = false;
        for (var i = 0; i < serverSelect.options.length; i++) {
            var sOpt = serverSelect.options[i];
            if (sOpt.getAttribute('data-wh-code') === whCode) {
                serverSelect.selectedIndex = i;
                found = true;
                break;
            }
        }
        if (!found) {
            serverSelect.value = 'all_p_code_excels';
        }
    }

    function syncServerFileToWarehouse(fileName) {
        var whSelect = document.getElementById('modal_import_warehouse_select');
        var serverSelect = document.getElementById('server_file_select');
        if (!whSelect || !serverSelect) return;

        if (fileName === 'all_p_code_excels' || fileName === 'ma.xlsx') {
            whSelect.value = '';
            document.querySelectorAll('.import-stock-target-wh').forEach(function(el) {
                el.value = '';
            });
            return;
        }

        var sOpt = serverSelect.options[serverSelect.selectedIndex];
        var whCode = sOpt ? (sOpt.getAttribute('data-wh-code') || '').trim() : '';
        if (!whCode) return;

        for (var i = 0; i < whSelect.options.length; i++) {
            var opt = whSelect.options[i];
            if (opt.getAttribute('data-code') === whCode) {
                whSelect.selectedIndex = i;
                document.querySelectorAll('.import-stock-target-wh').forEach(function(el) {
                    el.value = opt.value;
                });
                break;
            }
        }
    }
</script>
