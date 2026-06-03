<!-- SweetAlert2 & SheetJS CDNs to ensure they work out of the box -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<!-- Import Excel Modal (Initial Step) -->
<x-modal name="importWarehouseModal" maxWidth="md">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50 rounded-t-xl">
        <h5 class="font-bold text-base text-neutral-800">Nhập Dữ liệu từ Excel</h5>
        <button type="button" onclick="closeModal('importWarehouseModal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <div class="p-6 text-center">
        <div class="w-16 h-16 bg-success-50 rounded-full flex items-center justify-center text-success-600 mx-auto mb-4">
            <iconify-icon icon="solar:file-excel-bold" class="text-3xl"></iconify-icon>
        </div>
        
        <div class="mb-5 bg-primary-50 border border-primary-100 rounded-xl p-4 text-left text-xs text-primary-700 leading-relaxed">
            <iconify-icon icon="solar:magic-stick-bold" class="inline-block align-middle me-1 text-sm"></iconify-icon> 
            <strong>Ánh xạ cột thông minh:</strong> Bạn có thể dùng bất kỳ file Excel nào. Sau khi chọn file, hệ thống sẽ cho phép bạn tự kéo thả các cột vào đúng vị trí.
        </div>
        
        <div class="border-2 border-dashed border-neutral-200 rounded-2xl bg-neutral-50 p-8 cursor-pointer hover:border-primary-500 hover:bg-primary-50 hover:bg-opacity-40 transition-all duration-200" 
             onclick="document.getElementById('warehouseExcelInput').click()">
            <iconify-icon icon="solar:cloud-upload-outline" class="text-4xl text-neutral-400 mb-2"></iconify-icon>
            <p class="mb-1 font-bold text-sm text-neutral-700">Click để chọn file Excel</p>
            <p class="text-xs text-neutral-400">Hỗ trợ các định dạng .xlsx, .xls, .csv</p>
            <input type="file" id="warehouseExcelInput" accept=".xlsx, .xls, .csv" style="display: none;" onchange="handleWarehouseExcel(this)">
        </div>
    </div>
    <div class="px-6 py-4 border-t border-neutral-200 flex justify-center bg-neutral-50 rounded-b-xl">
        <a href="{{ route('warehouses.export-template', $warehouse) }}" class="text-xs text-primary-600 hover:text-primary-800 font-bold flex items-center gap-1">
            <iconify-icon icon="solar:download-outline" class="text-base"></iconify-icon> Tải file mẫu chuẩn (để xem cấu hình)
        </a>
    </div>
</x-modal>

<!-- Advanced Column Mapper Modal (Drag & Drop) -->
<x-modal name="warehouseMapperModal" maxWidth="2xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between text-white rounded-t-xl bg-gradient-to-r from-primary-600 to-primary-800">
        <div>
            <h5 class="font-bold text-base"><iconify-icon icon="solar:checklist-bold" class="inline-block align-middle me-1 text-lg"></iconify-icon> Ánh xạ cột Excel → Kho hàng</h5>
            <p class="text-[10px] opacity-80 mt-0.5">Kéo các cột từ file Excel bên trái thả vào các trường dữ liệu tương ứng bên phải</p>
        </div>
        <button type="button" onclick="closeModal('warehouseMapperModal')" class="text-white hover:text-neutral-200 text-xl leading-none">&times;</button>
    </div>

    <div class="grid grid-cols-12 gap-0 bg-neutral-50" style="height: 65vh;">
        <!-- Excel Source Columns -->
        <div class="col-span-4 bg-white border-r border-neutral-200 p-4 overflow-y-auto h-full">
            <h6 class="font-bold uppercase text-[10px] text-neutral-500 mb-3 border-b border-neutral-100 pb-2 flex items-center gap-1">
                <iconify-icon icon="solar:file-excel-outline" class="text-sm"></iconify-icon> Cột trong file Excel
            </h6>
            <div id="excelColsSource" class="flex flex-col gap-2" ondragover="event.preventDefault()" ondrop="dropBackToSource(event)">
                <!-- Columns render here -->
            </div>
        </div>

        <!-- Target Fields Mapping -->
        <div class="col-span-8 p-6 overflow-y-auto h-full space-y-6">
            <!-- Fixed Fields -->
            <div class="space-y-3">
                <h6 class="font-bold uppercase text-xs text-primary-600 flex items-center gap-2">
                    <span class="w-5 h-5 bg-primary-600 text-white rounded-full flex items-center justify-center font-bold text-[10px]">1</span>
                    Thông tin chung phiếu
                </h6>
                <div class="grid grid-cols-2 gap-3" id="fixedFieldsTarget"></div>
            </div>

            <!-- Dynamic Quantities (In) -->
            <div class="space-y-3 pt-4 border-t border-neutral-100">
                <h6 class="font-bold uppercase text-xs text-success-700 flex items-center gap-2">
                    <span class="w-5 h-5 bg-success-600 text-white rounded-full flex items-center justify-center font-bold text-[10px]">2</span>
                    Số lượng NHẬP (Quantity In)
                </h6>
                <div class="grid grid-cols-2 gap-3" id="inFieldsTarget"></div>
            </div>

            <!-- Dynamic Quantities (Out) -->
            <div class="space-y-3 pt-4 border-t border-neutral-100">
                <h6 class="font-bold uppercase text-xs text-danger-700 flex items-center gap-2">
                    <span class="w-5 h-5 bg-danger-600 text-white rounded-full flex items-center justify-center font-bold text-[10px]">3</span>
                    Số lượng XUẤT (Quantity Out)
                </h6>
                <div class="grid grid-cols-2 gap-3" id="outFieldsTarget"></div>
            </div>

            <!-- Manual Stock Override -->
            <div class="space-y-3 pt-4 border-t border-neutral-100">
                <h6 class="font-bold uppercase text-xs text-warning-700 flex items-center gap-2">
                    <span class="w-5 h-5 bg-warning-500 text-white rounded-full flex items-center justify-center font-bold text-[10px]">4</span>
                    TỒN KHO (Tùy chọn ghi đè)
                </h6>
                <div class="grid grid-cols-2 gap-3" id="stockFieldsTarget"></div>
            </div>
        </div>
    </div>

    <div class="px-6 py-4 border-t border-neutral-200 flex items-center justify-between bg-white rounded-b-xl">
        <div id="importStatus" class="text-xs font-bold text-neutral-500 flex items-center gap-1"></div>
        <div class="flex gap-2">
            <button type="button" class="btn bg-neutral-200 text-neutral-700 px-4 py-2 rounded-lg text-sm font-medium" onclick="closeModal('warehouseMapperModal')">Hủy</button>
            <button type="button" class="btn btn-primary px-5 py-2 rounded-lg text-sm font-semibold shadow-sm flex items-center gap-1.5" id="confirmWarehouseImportBtn" onclick="executeWarehouseImport()">
                <iconify-icon icon="solar:check-circle-outline" class="text-base"></iconify-icon> Xác nhận Nhập dữ liệu
            </button>
        </div>
    </div>
</x-modal>

<script>
    // Configuration from PHP
    const WAREHOUSE_SIZES = @json($allSizes);
    const IMPORT_JSON_URL = "{{ route('warehouses.import-json', $warehouse) }}";

    const FIXED_FIELDS = [
        { key: 'voucher_no', label: 'Số phiếu', aliases: ['so phieu', 'ma phieu', 'voucher', 'id'] },
        { key: 'date', label: 'Ngày', aliases: ['ngay', 'date', 'ngay ghi'] },
        { key: 'content', label: 'Nội dung', aliases: ['noi dung', 'dien giai', 'content', 'description'] },
        { key: 'exporter', label: 'Người xuất', aliases: ['nguoi xuat', 'exporter'] },
        { key: 'receiver', label: 'Người nhận', aliases: ['nguoi nhan', 'receiver'] },
    ];

    let _excelData = [];
    let _excelHeader = [];
    let _mappings = {};

    function handleWarehouseExcel(input) {
        const file = input.files[0];
        if (!file) return;

        Swal.fire({ title: 'Đang đọc tập tin...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        const reader = new FileReader();
        reader.onload = (e) => {
            try {
                const wb = XLSX.read(new Uint8Array(e.target.result), { type: 'array', cellDates: true });
                const ws = wb.Sheets[wb.SheetNames[0]];
                
                // Get raw rows
                let rawRows = XLSX.utils.sheet_to_json(ws, { header: 1, raw: false, dateNF: 'YYYY-MM-DD' });
                
                // --- PART 1: Handle Merged Cells ---
                const merges = ws['!merges'] || [];
                merges.forEach(m => {
                    const val = (rawRows[m.s.r] ? rawRows[m.s.r][m.s.c] : null);
                    for (let r = m.s.r; r <= m.e.r; r++) {
                        if (!rawRows[r]) rawRows[r] = [];
                        for (let c = m.s.c; c <= m.e.c; c++) {
                            if (rawRows[r][c] === undefined || rawRows[r][c] === null || rawRows[r][c] === '') {
                                rawRows[r][c] = val;
                            }
                        }
                    }
                });

                // --- PART 2: Determine Header Context (3-row aware) ---
                let headerRows = 1;
                if (rawRows[0] && rawRows[0].some(c => String(c).toUpperCase().includes('NHẬP') || String(c).toUpperCase().includes('XUẤT'))) {
                    headerRows = 3; 
                }

                // Generate Column Labels
                const maxCols = Math.max(...rawRows.slice(0, headerRows).map(r => r ? r.length : 0));
                _excelHeader = [];
                for (let c = 0; c < maxCols; c++) {
                    let labels = [];
                    for (let r = 0; r < headerRows; r++) {
                        let val = (rawRows[r] ? String(rawRows[r][c] || '').trim() : '');
                        if (val && !labels.includes(val)) labels.push(val);
                    }
                    if (labels.length > 0) {
                        _excelHeader.push({ idx: c, label: labels.join(' > ') });
                    }
                }

                // Data starts after headerRows
                _excelData = rawRows.slice(headerRows).filter(r => r.some(c => c));

                Swal.close();
                closeModal('importWarehouseModal');
                initWarehouseMapper();
                openModal('warehouseMapperModal');
            } catch (err) {
                console.error(err);
                Swal.fire('Lỗi', 'Không thể xử lý file Excel: ' + err.message, 'error');
            }
            input.value = '';
        };
        reader.readAsArrayBuffer(file);
    }

    function initWarehouseMapper() {
        _mappings = {};
        const normalize = s => (s || '').toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-z0-9]/g, "");
        
        const inFields = WAREHOUSE_SIZES.map(s => ({ 
            key: 'in_'+s.key, 
            label: s.label || 'Unknown',
            fullSearch: 'nhap' + normalize(s.label || '') 
        }));

        const outFields = WAREHOUSE_SIZES.map(s => ({ 
            key: 'out_'+s.key, 
            label: s.label || 'Unknown',
            fullSearch: 'xuat' + normalize(s.label || '') 
        }));

        const allFields = [...FIXED_FIELDS, ...inFields, ...outFields];

        allFields.forEach(f => {
            _mappings[f.key] = null;
            const aliases = f.aliases || [normalize(f.label || ''), f.fullSearch].filter(x => x);
            
            // Priority 1: Exact match on aliases
            let match = _excelHeader.find(h => {
                const hNorm = normalize(h.label || '');
                return aliases.some(a => hNorm === a || hNorm.includes(a));
            });

            // Priority 2: Fuzzy match for sizes
            if (!match && (f.key.startsWith('in_') || f.key.startsWith('out_'))) {
                const type = f.key.startsWith('in_') ? 'nhap' : 'xuat';
                match = _excelHeader.find(h => {
                    const hNorm = normalize(h.label);
                    return hNorm.includes(type) && hNorm.includes(normalize(f.label));
                });
            }

            if (match) _mappings[f.key] = match.idx;
        });

        renderWarehouseMapper();
    }

    function renderWarehouseMapper() {
        const mappedIdxs = new Set(Object.values(_mappings).filter(v => v !== null));

        // Source Columns
        const srcContainer = document.getElementById('excelColsSource');
        srcContainer.innerHTML = '';
        _excelHeader.forEach(h => {
            if (mappedIdxs.has(h.idx)) return;
            srcContainer.appendChild(createExcelChip(h));
        });

        // Mapping Targets
        renderTargetFields('fixedFieldsTarget', FIXED_FIELDS, 'col-span-1');
        renderTargetFields('inFieldsTarget', WAREHOUSE_SIZES.map(s => ({ key: 'in_'+s.key, label: s.label, color: '#f0fdf4' })), 'col-span-1');
        renderTargetFields('outFieldsTarget', WAREHOUSE_SIZES.map(s => ({ key: 'out_'+s.key, label: s.label, color: '#fef2f2' })), 'col-span-1');
        renderTargetFields('stockFieldsTarget', WAREHOUSE_SIZES.map(s => ({ key: 'stock_'+s.key, label: s.label, color: '#fffbeb' })), 'col-span-1');

        document.getElementById('importStatus').innerHTML = `<iconify-icon icon="solar:document-text-outline" class="text-base text-neutral-500"></iconify-icon> Sẵn sàng <strong>${_excelData.length}</strong> hàng dữ liệu.`;
    }

    function renderTargetFields(containerId, fields, colClass) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';
        fields.forEach(f => {
            const mappedHeader = _excelHeader.find(h => h.idx === _mappings[f.key]);
            const div = document.createElement('div');
            div.className = colClass;
            div.innerHTML = `
                <div class="relative p-2.5 border rounded-xl transition-all duration-200" 
                     style="background: ${f.color || '#fff'}; min-height: 72px; border-style: ${mappedHeader ? 'solid' : 'dashed'} !important; border-color: ${mappedHeader ? '#4e73df' : '#e5e7eb'}"
                     ondragover="event.preventDefault(); this.style.borderColor='#4e73df'; this.style.backgroundColor='#eff6ff';"
                     ondragleave="this.style.borderColor='${mappedHeader ? '#4e73df' : '#e5e7eb'}'; this.style.backgroundColor='${f.color || '#fff'}';"
                     ondrop="handleDrop(event, '${f.key}')">
                    <div class="text-[10px] font-bold text-neutral-500 mb-1 truncate" title="${f.label}">${f.label}</div>
                    <div class="slot-content">
                        ${mappedHeader ? `
                            <div class="bg-primary-600 text-white rounded-lg px-2.5 py-1 text-center text-xs relative flex items-center justify-center font-medium shadow-sm" style="min-height: 28px;">
                                <span class="truncate pr-3">${mappedHeader.label}</span>
                                <button type="button" class="absolute top-1/2 right-1.5 -translate-y-1/2 text-white hover:text-danger-300 font-bold" 
                                    onclick="unmapField('${f.key}')">&times;</button>
                            </div>
                        ` : `
                            <div class="text-neutral-400 text-[10px] py-1 text-center italic">Kéo thả cột vào</div>
                        `}
                    </div>
                </div>
            `;
            container.appendChild(div);
        });
    }

    function createExcelChip(h) {
        const div = document.createElement('div');
        div.className = 'bg-white hover:bg-neutral-50 text-neutral-800 border border-neutral-200 rounded-lg p-2 text-xs cursor-grab w-full flex items-center shadow-sm select-none';
        div.innerHTML = `<iconify-icon icon="solar:hamburger-menu-outline" class="text-neutral-400 me-2 cursor-grab"></iconify-icon><span class="truncate">${h.label}</span>`;
        div.draggable = true;
        div.ondragstart = (e) => { e.dataTransfer.setData('colIdx', h.idx); };
        return div;
    }

    function handleDrop(e, fieldKey) {
        e.preventDefault();
        const idx = parseInt(e.dataTransfer.getData('colIdx'));
        if (isNaN(idx)) return;
        
        Object.keys(_mappings).forEach(k => { if(_mappings[k] === idx) _mappings[k] = null; });
        _mappings[fieldKey] = idx;
        renderWarehouseMapper();
    }

    function unmapField(k) {
        _mappings[k] = null;
        renderWarehouseMapper();
    }

    function dropBackToSource(e) {
        e.preventDefault();
        const idx = parseInt(e.dataTransfer.getData('colIdx'));
        if (isNaN(idx)) return;
        Object.keys(_mappings).forEach(k => { if(_mappings[k] === idx) _mappings[k] = null; });
        renderWarehouseMapper();
    }

    async function executeWarehouseImport() {
        const records = _excelData.map(row => {
            const obj = {};
            Object.keys(_mappings).forEach(k => {
                if (_mappings[k] !== null) obj[k] = row[_mappings[k]];
            });
            return obj;
        });

        if (records.length === 0) return Swal.fire('Thông báo', 'Không có dữ liệu để nhập.', 'info');

        Swal.fire({ 
            title: 'Hệ thống đang nhập dữ liệu...', 
            html: `Đang xử lý <b>${records.length}</b> bản ghi. Vui lòng chờ trong giây lát.`,
            allowOutsideClick: false, 
            didOpen: () => Swal.showLoading() 
        });

        try {
            const response = await fetch(IMPORT_JSON_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ records })
            });
            const result = await response.json();
            
            if (result.success) {
                Swal.fire('Thành công', result.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Lỗi', result.message, 'error');
            }
        } catch (err) {
            Swal.fire('Lỗi', 'Có lỗi kết nối máy chủ khi nhập dữ liệu.', 'error');
        }
    }
</script>
