{{-- View cho loại đơn: Dịch vụ & Vật tư bổ sung --}}
<div class="space-y-4">
    <div class="p-4 bg-purple-50/70 border border-purple-200 rounded-xl flex items-center justify-between gap-3 flex-wrap">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0">
                <iconify-icon icon="solar:bill-list-bold" class="text-xl"></iconify-icon>
            </div>
            <div>
                <h6 class="text-sm font-bold text-neutral-800 mb-0.5">Đơn Hàng Dịch Vụ & Vật Tư Bổ Sung</h6>
                <p class="text-xs text-neutral-500 mb-0">Thêm các hạng mục ván mộc, phụ kiện, vật tư hoặc dịch vụ gia công. Đơn hàng không yêu cầu bảng kích thước phôi tấm.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="addPaymentDetail()" class="btn btn-sm bg-purple-600 hover:bg-purple-700 text-white rounded-lg flex items-center gap-1 font-semibold text-xs py-2 px-3">
                <iconify-icon icon="lucide:plus" class="text-base"></iconify-icon>
                <span>Thêm hạng mục mới</span>
            </button>
        </div>
    </div>

    {{-- Nhúng trực tiếp Bảng chi tiết hóa đơn dịch vụ & vật tư --}}
    @include('orders._payment_details')
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Đảm bảo bảng chi tiết hóa đơn luôn mở sẵn cho loại đơn service
        const paymentSec = document.getElementById('payment-details-section');
        if (paymentSec) {
            paymentSec.style.display = 'block';
            paymentSec.classList.remove('hidden');
        }

        // Nếu chưa có dòng nào, tự động thêm 1 dòng trống đầu tiên
        setTimeout(() => {
            const container = document.getElementById('payment-details-container');
            if (container && container.querySelectorAll('tr.payment-detail-row').length === 0) {
                if (typeof addPaymentDetail === 'function') {
                    addPaymentDetail();
                }
            }
        }, 150);
    });
</script>
