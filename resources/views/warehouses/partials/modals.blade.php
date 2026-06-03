{{-- Configuration Modal --}}
<x-modal name="configWarehouseModal" maxWidth="2xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50 rounded-t-xl">
        <h5 class="font-bold text-base text-neutral-800">Cấu hình Hàng hóa & Kích cỡ (Sizes)</h5>
        <button type="button" onclick="closeModal('configWarehouseModal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('warehouses.config.update', $warehouse) }}" method="POST">
        @csrf
        <div class="p-6 overflow-y-auto max-h-[70vh]">
            <div class="mb-5">
                <label class="form-label font-bold text-sm text-neutral-700 mb-1.5 block">Tên loại hàng hóa (Vd: "Tấm Gỗ Acrylic", "Kính Cường Lực")</label>
                <input type="text" name="item_name" value="{{ $warehouse->item_name }}"
                    class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500" required>
            </div>

            <h6 class="font-bold text-sm text-neutral-800 mb-3 border-b border-neutral-100 pb-2">Nhóm Kích thước & Giá cấu hình</h6>
            <div id="sizeGroupsContainer" class="space-y-4">
                @php
                    $configs = $warehouse->sizes_config ?? [];
                @endphp
                @foreach ($configs as $gIdx => $group)
                    <div class="size-group-row p-4 bg-neutral-50 rounded-xl relative border border-neutral-200 mb-4" id="groupRow_{{ $gIdx }}">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-3">
                            <div class="md:col-span-4">
                                <label class="form-label text-xs font-semibold text-neutral-600 mb-1 block">Tên nhóm</label>
                                <input type="text" name="groups[{{ $gIdx }}][name]"
                                    value="{{ $group['name'] }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-1.5 text-xs focus:ring-primary-500"
                                    placeholder="Vd: Chiều dày 18mm" required>
                            </div>
                            <div class="md:col-span-4">
                                <label class="form-label text-xs font-semibold text-neutral-600 mb-1 block">Mã nhóm</label>
                                <input type="text" name="groups[{{ $gIdx }}][code]"
                                    value="{{ $group['code'] ?? '' }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-1.5 text-xs focus:ring-primary-500"
                                    placeholder="Vd: TH18">
                            </div>
                            <div class="md:col-span-4">
                                <label class="form-label text-xs font-semibold text-neutral-600 mb-1 block">Đơn giá nhóm (nếu có)</label>
                                <input type="number" name="groups[{{ $gIdx }}][price]"
                                    value="{{ $group['price'] ?? '' }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-1.5 text-xs focus:ring-primary-500"
                                    placeholder="Vd: 1200000" min="0" step="1000">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label text-xs font-bold text-neutral-700 mb-2 block">Danh sách kích cỡ & Mã / Giá riêng (nếu có)</label>
                            <div id="sizesContainer_{{ $gIdx }}" class="space-y-2">
                                @if(!empty($group['sizes']))
                                    @foreach($group['sizes'] as $sIdx => $sizeObj)
                                        <div class="flex items-center gap-3 size-row">
                                            <input type="text" name="groups[{{ $gIdx }}][sizes][{{ $sIdx }}][name]" 
                                                value="{{ $sizeObj['name'] ?? $sizeObj }}" class="form-control rounded-lg flex-grow border-neutral-200 px-3 py-1 text-xs" 
                                                placeholder="Tên kích cỡ (Vd: 1220x2440)" required>
                                            <input type="text" name="groups[{{ $gIdx }}][sizes][{{ $sIdx }}][code]" 
                                                value="{{ $sizeObj['code'] ?? '' }}" class="form-control rounded-lg w-32 border-neutral-200 px-3 py-1 text-xs" 
                                                placeholder="Mã size">
                                            <input type="number" name="groups[{{ $gIdx }}][sizes][{{ $sIdx }}][price]" 
                                                value="{{ $sizeObj['price'] ?? '' }}" class="form-control rounded-lg w-32 border-neutral-200 px-3 py-1 text-xs" 
                                                placeholder="Đơn giá (Vd: 1500000)" min="0" step="1000">
                                            <button type="button" class="text-danger-500 hover:text-danger-700 p-1 text-base leading-none" onclick="this.parentElement.remove()">&times;</button>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-2.5 py-1 rounded-md text-[11px] font-semibold mt-2 inline-flex items-center gap-1"
                                onclick="addSizeRow({{ $gIdx }})">
                                <iconify-icon icon="ic:baseline-plus" class="text-sm"></iconify-icon> Thêm kích cỡ
                            </button>
                        </div>
                        
                        <button type="button" class="text-danger-500 hover:text-danger-700 absolute top-0 end-0 mt-2 me-2 p-1" onclick="this.parentElement.remove()">
                            <iconify-icon icon="solar:close-circle-outline" class="text-lg"></iconify-icon>
                        </button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn border border-primary-500 text-primary-600 hover:bg-primary-50 px-3 py-1.5 rounded-lg text-xs font-semibold mt-4 flex items-center gap-1.5"
                onclick="addSizeGroup()">
                <iconify-icon icon="ic:baseline-plus" class="text-base"></iconify-icon> Thêm nhóm mới
            </button>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex justify-end gap-3 rounded-b-xl">
            <button type="button" onclick="closeModal('configWarehouseModal')" class="btn bg-neutral-200 text-neutral-700 px-4 py-2 rounded-lg text-sm font-medium">Hủy</button>
            <button type="submit" class="btn btn-primary px-5 py-2 rounded-lg text-sm font-medium shadow-sm">Lưu cấu hình</button>
        </div>
    </form>
</x-modal>

{{-- Add Record Modal --}}
<x-modal name="addRecordModal" maxWidth="2xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-success-50 rounded-t-xl">
        <h5 class="font-bold text-base text-success-800"><iconify-icon icon="solar:add-circle-outline" class="inline-block align-middle me-1 text-lg"></iconify-icon> Tạo phiếu nhập/xuất kho</h5>
        <button type="button" onclick="closeModal('addRecordModal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('warehouses.records.store', $warehouse) }}" method="POST">
        @csrf
        <div class="p-6 overflow-y-auto max-h-[70vh] space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-600 mb-1 block">Số phiếu</label>
                    <input type="text" name="voucher_no" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs"
                        placeholder="Vd: P001" value="{{ $nextVoucher }}">
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-600 mb-1 block">Ngày</label>
                    <input type="date" name="date" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs"
                        value="{{ date('Y-m-d') }}">
                </div>
            </div>
            
            <div>
                <label class="form-label text-xs font-semibold text-neutral-600 mb-1 block">Nội dung phiếu</label>
                <input type="text" name="content" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs"
                    placeholder="Nhập nội dung nhập/xuất kho...">
            </div>

            {{-- Sizes Quantities input section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-neutral-100">
                {{-- Nhập Column --}}
                <div class="border-r border-neutral-100 pr-2">
                    <h6 class="text-xs font-bold text-success-700 mb-3 px-3 py-2 bg-success-50 rounded-lg flex items-center gap-1.5"><iconify-icon icon="solar:arrow-down-outline" class="text-base"></iconify-icon> SỐ LƯỢNG NHẬP</h6>
                    <div class="grid grid-cols-2 gap-3 max-h-[250px] overflow-y-auto pr-1">
                        @foreach ($allSizes as $s)
                            <div class="p-2.5 border border-neutral-200 rounded-xl bg-white flex flex-col justify-between">
                                <div class="text-[10px] font-bold text-neutral-500 mb-0.5 truncate" title="{{ $s['group'] }}">{{ $s['group'] }}</div>
                                <div class="text-xs font-bold text-neutral-800 mb-1 flex items-center justify-between">
                                    <span>{{ $s['size'] ?: 'Mặc định' }}</span>
                                    @if(!empty($s['price']))
                                        <span class="text-[10px] text-primary-600 font-semibold" title="Đơn giá cấu hình: {{ number_format($s['price']) }} VNĐ">{{ number_format($s['price']/1000) }}k</span>
                                    @endif
                                </div>
                                <input type="number" name="in_data[{{ $s['key'] }}]"
                                    class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-center text-xs font-semibold text-success-600 focus:border-success-500 focus:ring-1 focus:ring-success-500"
                                    min="0">
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Xuất Column --}}
                <div>
                    <h6 class="text-xs font-bold text-danger-700 mb-3 px-3 py-2 bg-danger-50 rounded-lg flex items-center gap-1.5"><iconify-icon icon="solar:arrow-up-outline" class="text-base"></iconify-icon> SỐ LƯỢNG XUẤT</h6>
                    <div class="grid grid-cols-2 gap-3 max-h-[250px] overflow-y-auto pr-1">
                        @foreach ($allSizes as $s)
                            <div class="p-2.5 border border-neutral-200 rounded-xl bg-white flex flex-col justify-between">
                                <div class="text-[10px] font-bold text-neutral-500 mb-0.5 truncate" title="{{ $s['group'] }}">{{ $s['group'] }}</div>
                                <div class="text-xs font-bold text-neutral-800 mb-1 flex items-center justify-between">
                                    <span>{{ $s['size'] ?: 'Mặc định' }}</span>
                                    @if(!empty($s['price']))
                                        <span class="text-[10px] text-primary-600 font-semibold" title="Đơn giá cấu hình: {{ number_format($s['price']) }} VNĐ">{{ number_format($s['price']/1000) }}k</span>
                                    @endif
                                </div>
                                <input type="number" name="out_data[{{ $s['key'] }}]"
                                    class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-center text-xs font-semibold text-danger-600 focus:border-danger-500 focus:ring-1 focus:ring-danger-500"
                                    min="0">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-neutral-100">
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-600 mb-1 block">Người xuất / Người giao</label>
                    <input type="text" name="exporter" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs" placeholder="Tên cán bộ xuất kho">
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-600 mb-1 block">Người nhận</label>
                    <input type="text" name="receiver" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs" placeholder="Tên người nhận hàng">
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex justify-end gap-3 rounded-b-xl">
            <button type="button" onclick="closeModal('addRecordModal')" class="btn bg-neutral-200 text-neutral-700 px-4 py-2 rounded-lg text-sm font-medium">Hủy</button>
            <button type="submit" class="btn btn-primary px-5 py-2 rounded-lg text-sm font-medium shadow-sm flex items-center gap-1.5"><iconify-icon icon="solar:diskette-outline" class="text-base"></iconify-icon> Lưu phiếu</button>
        </div>
    </form>
</x-modal>

{{-- Edit Record Modal --}}
<x-modal name="editRecordModal" maxWidth="2xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-warning-50 rounded-t-xl">
        <h5 class="font-bold text-base text-warning-800"><iconify-icon icon="solar:pen-new-square-outline" class="inline-block align-middle me-1 text-lg"></iconify-icon> Chỉnh sửa phiếu nhập/xuất</h5>
        <button type="button" onclick="closeModal('editRecordModal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form id="editRecordForm" method="POST">
        @csrf
        @method('PUT')
        <div class="p-6 overflow-y-auto max-h-[70vh] space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-600 mb-1 block">Số phiếu</label>
                    <input type="text" name="voucher_no" id="edit_voucher_no" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-600 mb-1 block">Ngày</label>
                    <input type="date" name="date" id="edit_date" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs">
                </div>
            </div>
            
            <div>
                <label class="form-label text-xs font-semibold text-neutral-600 mb-1 block">Nội dung phiếu</label>
                <input type="text" name="content" id="edit_content" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-neutral-100">
                {{-- Nhập Column --}}
                <div class="border-r border-neutral-100 pr-2">
                    <h6 class="text-xs font-bold text-success-700 mb-3 px-3 py-2 bg-success-50 rounded-lg flex items-center gap-1.5"><iconify-icon icon="solar:arrow-down-outline" class="text-base"></iconify-icon> SỐ LƯỢNG NHẬP</h6>
                    <div class="grid grid-cols-2 gap-3 max-h-[250px] overflow-y-auto pr-1">
                        @foreach ($allSizes as $s)
                            <div class="p-2.5 border border-neutral-200 rounded-xl bg-white flex flex-col justify-between">
                                <div class="text-[10px] font-bold text-neutral-500 mb-0.5 truncate" title="{{ $s['group'] }}">{{ $s['group'] }}</div>
                                <div class="text-xs font-bold text-neutral-800 mb-1 flex items-center justify-between">
                                    <span>{{ $s['size'] ?: 'Mặc định' }}</span>
                                    @if(!empty($s['price']))
                                        <span class="text-[10px] text-primary-600 font-semibold" title="Đơn giá cấu hình: {{ number_format($s['price']) }} VNĐ">{{ number_format($s['price']/1000) }}k</span>
                                    @endif
                                </div>
                                <input type="number" name="in_data[{{ $s['key'] }}]" id="edit_in_{{ $s['key'] }}"
                                    class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-center text-xs font-semibold text-success-600 focus:border-success-500 focus:ring-1 focus:ring-success-500"
                                    min="0">
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Xuất Column --}}
                <div>
                    <h6 class="text-xs font-bold text-danger-700 mb-3 px-3 py-2 bg-danger-50 rounded-lg flex items-center gap-1.5"><iconify-icon icon="solar:arrow-up-outline" class="text-base"></iconify-icon> SỐ LƯỢNG XUẤT</h6>
                    <div class="grid grid-cols-2 gap-3 max-h-[250px] overflow-y-auto pr-1">
                        @foreach ($allSizes as $s)
                            <div class="p-2.5 border border-neutral-200 rounded-xl bg-white flex flex-col justify-between">
                                <div class="text-[10px] font-bold text-neutral-500 mb-0.5 truncate" title="{{ $s['group'] }}">{{ $s['group'] }}</div>
                                <div class="text-xs font-bold text-neutral-800 mb-1 flex items-center justify-between">
                                    <span>{{ $s['size'] ?: 'Mặc định' }}</span>
                                    @if(!empty($s['price']))
                                        <span class="text-[10px] text-primary-600 font-semibold" title="Đơn giá cấu hình: {{ number_format($s['price']) }} VNĐ">{{ number_format($s['price']/1000) }}k</span>
                                    @endif
                                </div>
                                <input type="number" name="out_data[{{ $s['key'] }}]" id="edit_out_{{ $s['key'] }}"
                                    class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-center text-xs font-semibold text-danger-600 focus:border-danger-500 focus:ring-1 focus:ring-danger-500"
                                    min="0">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-neutral-100">
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-600 mb-1 block">Người xuất / Người giao</label>
                    <input type="text" name="exporter" id="edit_exporter" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="form-label text-xs font-semibold text-neutral-600 mb-1 block">Người nhận</label>
                    <input type="text" name="receiver" id="edit_receiver" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs">
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex justify-end gap-3 rounded-b-xl">
            <button type="button" onclick="closeModal('editRecordModal')" class="btn bg-neutral-200 text-neutral-700 px-4 py-2 rounded-lg text-sm font-medium">Hủy</button>
            <button type="submit" class="btn btn-warning text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm flex items-center gap-1.5"><iconify-icon icon="solar:diskette-outline" class="text-base"></iconify-icon> Cập nhật phiếu</button>
        </div>
    </form>
</x-modal>

<script>
    let groupIndex = {{ count($configs) }};

    function addSizeGroup() {
        const container = document.getElementById('sizeGroupsContainer');
        const row = document.createElement('div');
        row.className = 'size-group-row p-4 bg-neutral-50 rounded-xl relative border border-neutral-200 mb-4';
        row.id = `groupRow_${groupIndex}`;
        row.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-3">
                <div class="md:col-span-4">
                    <label class="form-label text-xs font-semibold text-neutral-600 mb-1 block">Tên nhóm</label>
                    <input type="text" name="groups[${groupIndex}][name]" class="form-control rounded-lg w-full border-neutral-200 px-3 py-1.5 text-xs focus:ring-primary-500" placeholder="Vd: Chiều dày 18mm" required>
                </div>
                <div class="md:col-span-4">
                    <label class="form-label text-xs font-semibold text-neutral-600 mb-1 block">Mã nhóm</label>
                    <input type="text" name="groups[${groupIndex}][code]" class="form-control rounded-lg w-full border-neutral-200 px-3 py-1.5 text-xs focus:ring-primary-500" placeholder="Vd: TH18">
                </div>
                <div class="md:col-span-4">
                    <label class="form-label text-xs font-semibold text-neutral-600 mb-1 block">Đơn giá nhóm (nếu có)</label>
                    <input type="number" name="groups[${groupIndex}][price]" class="form-control rounded-lg w-full border-neutral-200 px-3 py-1.5 text-xs focus:ring-primary-500" placeholder="Vd: 1200000" min="0" step="1000">
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label text-xs font-bold text-neutral-700 mb-2 block">Danh sách kích cỡ & Mã / Giá riêng (nếu có)</label>
                <div id="sizesContainer_${groupIndex}" class="space-y-2">
                    <!-- Size rows go here -->
                </div>
                <button type="button" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-2.5 py-1 rounded-md text-[11px] font-semibold mt-2 inline-flex items-center gap-1"
                    onclick="addSizeRow(${groupIndex})">
                    <iconify-icon icon="ic:baseline-plus" class="text-sm"></iconify-icon> Thêm kích cỡ
                </button>
            </div>
            
            <button type="button" class="text-danger-500 hover:text-danger-700 absolute top-0 end-0 mt-2 me-2 p-1" onclick="this.parentElement.remove()">
                <iconify-icon icon="solar:close-circle-outline" class="text-lg"></iconify-icon>
            </button>
        `;
        container.appendChild(row);
        groupIndex++;
    }

    let sizeIndexes = {};

    function addSizeRow(groupIndex, name = '', price = '', code = '') {
        if (sizeIndexes[groupIndex] === undefined) {
            const container = document.getElementById(`sizesContainer_${groupIndex}`);
            sizeIndexes[groupIndex] = container.children.length;
        }
        const idx = sizeIndexes[groupIndex];
        const container = document.getElementById(`sizesContainer_${groupIndex}`);
        const row = document.createElement('div');
        row.className = 'flex items-center gap-3 size-row';
        row.innerHTML = `
            <input type="text" name="groups[${groupIndex}][sizes][${idx}][name]" value="${name}" class="form-control rounded-lg flex-grow border-neutral-200 px-3 py-1 text-xs focus:ring-primary-500" placeholder="Tên kích cỡ (Vd: 1220x2440)" required>
            <input type="text" name="groups[${groupIndex}][sizes][${idx}][code]" value="${code}" class="form-control rounded-lg w-32 border-neutral-200 px-3 py-1 text-xs focus:ring-primary-500" placeholder="Mã size">
            <input type="number" name="groups[${groupIndex}][sizes][${idx}][price]" value="${price}" class="form-control rounded-lg w-32 border-neutral-200 px-3 py-1 text-xs focus:ring-primary-500" placeholder="Đơn giá (Vd: 1500000)" min="0" step="1000">
            <button type="button" class="text-danger-500 hover:text-danger-700 p-1 text-base leading-none" onclick="this.parentElement.remove()">&times;</button>
        `;
        container.appendChild(row);
        sizeIndexes[groupIndex]++;
    }
</script>
