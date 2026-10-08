{{-- Modal Yêu cầu sửa đơn hàng --}}
<div id="requestEditModal" class="fixed inset-0 z-50 hidden bg-neutral-900/60 backdrop-blur-xs overflow-y-auto">
    <div class="min-h-full flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-neutral-200 overflow-hidden transform transition-all">
            <div class="p-5 border-b border-neutral-200 bg-amber-50/70 flex items-center justify-between">
                <div class="flex items-center gap-2.5 text-amber-900">
                    <div class="w-9 h-9 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                        <iconify-icon icon="lucide:alert-triangle" class="text-xl"></iconify-icon>
                    </div>
                    <div>
                        <h5 class="text-base font-bold text-neutral-800 m-0">Yêu cầu sửa đơn hàng</h5>
                        <div class="flex items-center gap-2 mt-0.5">
                            <p class="text-xs text-neutral-500 m-0">Đơn hàng: <span id="requestEditModalOrderCode" class="font-bold text-primary-600"></span></p>
                            <span id="requestEditModalStepBadge" class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-200 text-amber-900">Kế toán yêu cầu</span>
                        </div>
                    </div>
                <button type="button" onclick="closeRequestEditModal()" class="text-neutral-400 hover:text-neutral-600 p-1.5 rounded-lg hover:bg-neutral-100 transition-colors">
                    <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                </button>
            </div>

            <form id="requestEditModalForm" method="POST" action="" class="p-5 space-y-4 m-0">
                @csrf
                <input type="hidden" name="status" value="request_edit">
                <input type="hidden" name="step" id="requestEditModalStep" value="accountant">

                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-1.5">
                        Lý do yêu cầu sửa đơn <span class="text-danger-500">*</span>
                    </label>
                    <textarea name="edit_reason" id="requestEditModalReason" rows="4" required
                        placeholder="Nhập chi tiết lý do yêu cầu sửa (ví dụ: Sai kích thước cánh, thiếu mã ván, cần đổi mã màu...)"
                        class="form-control w-full rounded-xl border border-neutral-300 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-sm p-3"></textarea>
                    <p class="text-xs text-neutral-500 mt-1">Lý do này sẽ hiển thị cho người tạo/sửa đơn để điều chỉnh lại đơn hàng.</p>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeRequestEditModal()"
                        class="px-4 py-2.5 rounded-xl text-sm font-semibold text-neutral-600 hover:bg-neutral-100 border border-neutral-200 transition-colors cursor-pointer">
                        Hủy
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl text-sm font-bold bg-amber-500 hover:bg-amber-600 text-white shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                        <iconify-icon icon="lucide:send" class="text-base"></iconify-icon>
                        Xác nhận yêu cầu sửa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openRequestEditModal(actionUrl, orderCode, step = 'accountant', currentReason = '') {
        const modal = document.getElementById('requestEditModal');
        const form = document.getElementById('requestEditModalForm');
        const codeEl = document.getElementById('requestEditModalOrderCode');
        const reasonEl = document.getElementById('requestEditModalReason');
        const stepEl = document.getElementById('requestEditModalStep');
        const stepBadge = document.getElementById('requestEditModalStepBadge');

        if (modal && form) {
            form.action = actionUrl;
            if (codeEl) codeEl.textContent = orderCode || '';
            if (stepEl) stepEl.value = step || 'accountant';
            if (stepBadge) {
                if (step === 'technical') {
                    stepBadge.textContent = 'Kỹ thuật yêu cầu';
                    stepBadge.className = 'px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200';
                } else {
                    stepBadge.textContent = 'Kế toán yêu cầu';
                    stepBadge.className = 'px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-900 border border-amber-300';
                }
            }
            if (reasonEl) {
                reasonEl.value = currentReason || '';
                setTimeout(() => reasonEl.focus(), 100);
            }
            modal.classList.remove('hidden');
        }
    }

    function closeRequestEditModal() {
        const modal = document.getElementById('requestEditModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeRequestEditModal();
        }
    });
</script>
