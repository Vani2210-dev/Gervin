<x-modal name="importMatrixModal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50 rounded-t-xl">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-success-100 text-success-600 flex items-center justify-center">
                <iconify-icon icon="solar:file-excel-bold" class="text-xl"></iconify-icon>
            </div>
            <div>
                <h5 class="font-bold text-base text-neutral-800">Nhập Báo cáo Ma trận từ Excel</h5>
                <p class="text-xs text-neutral-500">Định dạng bảng tổng hợp Tồn kho & Tiêu thụ theo tháng</p>
            </div>
        </div>
        <button type="button" onclick="closeModal('importMatrixModal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>

    <form action="{{ route('warehouses.import-matrix', $warehouse) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="p-6">
            <div class="mb-5 bg-primary-50 border border-primary-100 rounded-xl p-4 text-xs text-primary-800 leading-relaxed">
                <div class="font-bold flex items-center gap-2 mb-2 text-sm text-primary-900">
                    <iconify-icon icon="solar:info-circle-bold" class="text-base text-primary-600"></iconify-icon>
                    Cấu trúc file Excel kế toán hợp lệ:
                </div>
                <ul class="list-disc list-inside space-y-1 text-neutral-600 pl-1">
                    <li>Dòng 1: Tiêu đề các kỳ (ví dụ: <code class="bg-white px-2 py-1 rounded text-primary-700 font-mono">15 THÁNG 01/2024</code>, <code class="bg-white px-2 py-1 rounded text-primary-700 font-mono">THÁNG 02/2024</code>...)</li>
                    <li>Cột C, D, E: Nhóm màu, Mã xuất xứ, Mã hàng hóa (Mã Gervin)</li>
                    <li>Cột F: Tồn đầu kỳ</li>
                    <li>Từ Cột H trở đi: Bộ 3 cột <strong>Nhận - Tiêu thụ - Tồn</strong> cho từng tháng</li>
                </ul>
            </div>

            <div class="border-2 border-dashed border-neutral-200 rounded-2xl bg-neutral-50 p-6 text-center hover:border-primary-500 hover:bg-primary-50 hover:bg-opacity-40 transition-all cursor-pointer"
                 onclick="document.getElementById('matrixFileInput').click()">
                <iconify-icon icon="solar:cloud-upload-outline" class="text-4xl text-primary-500 mb-2"></iconify-icon>
                <p class="font-bold text-sm text-neutral-800 mb-1" id="matrixFileName">Nhấp để chọn file Excel (.xlsx, .xls)</p>
                <p class="text-xs text-neutral-400">Hỗ trợ các định dạng .xlsx, .xls (Tối đa 10MB)</p>
                <input type="file" id="matrixFileInput" name="matrix_file" accept=".xlsx, .xls" class="hidden" required
                       onchange="if(this.files[0]) document.getElementById('matrixFileName').textContent = this.files[0].name;">
            </div>
        </div>

        <div class="px-6 py-4 border-t border-neutral-200 flex items-center justify-end gap-3 bg-neutral-50 rounded-b-xl">
            <button type="button" onclick="closeModal('importMatrixModal')" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-4 py-2 rounded-lg text-sm font-medium">
                Hủy
            </button>
            <button type="submit" class="btn btn-primary px-5 py-2 rounded-lg text-sm font-bold flex items-center gap-2">
                <iconify-icon icon="solar:upload-track-2-bold" class="text-base"></iconify-icon> Tiến hành Nhập dữ liệu
            </button>
        </div>
    </form>
</x-modal>
