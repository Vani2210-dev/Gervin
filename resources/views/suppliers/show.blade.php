@extends('layout.layout')
@php
    $title    = 'Quản lý kho & Nhà cung cấp';
    $subTitle = 'Chi tiết NCC: ' . $supplier->name;
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl px-4 py-3 text-xs font-semibold flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-neutral-400 hover:text-neutral-700">&times;</button>
            </div>
        @endif

        {{-- Top Header Profile Card --}}
        <div class="card p-6 rounded-xl border-0 bg-white shadow-sm mb-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <a href="{{ route('suppliers.index') }}" class="w-10 h-10 rounded-xl flex items-center justify-center bg-neutral-100 text-neutral-600 hover:bg-neutral-200 transition-colors shrink-0">
                        <iconify-icon icon="solar:arrow-left-bold" class="text-lg"></iconify-icon>
                    </a>
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h4 class="text-xl font-bold text-neutral-800 mb-0">{{ $supplier->name }}</h4>
                            <span class="bg-primary-50 text-primary-700 border border-primary-200 px-2.5 py-0.5 rounded-lg text-xs font-bold">
                                {{ $supplier->code }}
                            </span>
                            @if($supplier->status === 'active')
                                <span class="bg-emerald-100 text-emerald-800 px-2.5 py-0.5 rounded-full text-xs font-bold">
                                    Hoạt động
                                </span>
                            @else
                                <span class="bg-neutral-100 text-neutral-600 px-2.5 py-0.5 rounded-full text-xs font-bold">
                                    Ngưng hoạt động
                                </span>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-2 mt-3 text-xs text-neutral-600">
                            <div><span class="text-neutral-400 font-semibold">SĐT:</span> <span class="font-bold text-neutral-800">{{ $supplier->phone ?: 'Chưa cập nhật' }}</span></div>
                            <div><span class="text-neutral-400 font-semibold">Email:</span> <span class="font-medium text-neutral-800">{{ $supplier->email ?: 'Chưa cập nhật' }}</span></div>
                            <div><span class="text-neutral-400 font-semibold">MST:</span> <span class="font-medium text-neutral-800">{{ $supplier->tax_code ?: 'Chưa cập nhật' }}</span></div>
                            <div class="md:col-span-2"><span class="text-neutral-400 font-semibold">Địa chỉ:</span> <span class="text-neutral-800">{{ $supplier->address ?: 'Chưa cập nhật' }}</span></div>
                            <div><span class="text-neutral-400 font-semibold">Người liên hệ:</span> <span class="font-medium text-neutral-800">{{ $supplier->contact_person ?: 'Chưa cập nhật' }}</span></div>
                        </div>
                        @if($supplier->notes)
                            <div class="mt-2 text-xs text-neutral-500 bg-neutral-50 p-2 rounded-lg border border-neutral-100">
                                <span class="font-semibold text-neutral-600">Ghi chú:</span> {{ $supplier->notes }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm">
                        <iconify-icon icon="solar:pen-bold" class="text-base"></iconify-icon> Sửa thông tin
                    </a>
                    <a href="{{ route('inventory.receipts.create') }}?supplier_id={{ $supplier->id }}" class="btn bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm">
                        <iconify-icon icon="solar:import-bold" class="text-base"></iconify-icon> Nhập kho từ NCC này
                    </a>
                    <button type="button" onclick="openPaymentModal()" class="btn bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm">
                        <iconify-icon icon="solar:hand-money-bold" class="text-base"></iconify-icon> Thanh toán / Trả tiền
                    </button>
                </div>
            </div>
        </div>

        {{-- Financial Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="card p-4 rounded-xl border border-neutral-200 bg-white shadow-sm">
                <span class="text-[11px] text-neutral-500 font-semibold uppercase block mb-1">Công nợ ban đầu</span>
                <span class="text-lg font-bold text-neutral-800">{{ number_format($supplier->initial_debt, 0, ',', '.') }}đ</span>
            </div>

            <div class="card p-4 rounded-xl border border-neutral-200 bg-white shadow-sm">
                <span class="text-[11px] text-neutral-500 font-semibold uppercase block mb-1">Tổng tiền mua hàng (PNK)</span>
                <span class="text-lg font-bold text-primary-700">{{ number_format($totalPurchases, 0, ',', '.') }}đ</span>
            </div>

            <div class="card p-4 rounded-xl border border-emerald-200 bg-emerald-50/40 shadow-sm">
                <span class="text-[11px] text-emerald-800 font-semibold uppercase block mb-1">Tổng tiền đã trả</span>
                <span class="text-lg font-bold text-emerald-700">{{ number_format($totalPaid, 0, ',', '.') }}đ</span>
            </div>

            <div class="card p-4 rounded-xl border border-rose-200 bg-rose-50 shadow-sm">
                <span class="text-[11px] text-rose-800 font-semibold uppercase block mb-1">CÔNG NỢ HIỆN TẠI CẦN TRẢ</span>
                <span class="text-2xl font-black text-rose-700">{{ number_format($supplier->debt, 0, ',', '.') }}đ</span>
            </div>
        </div>

        {{-- Tabs & Lists --}}
        <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6" x-data="{ tab: 'receipts' }">
            <div class="px-4 pt-3 border-b border-neutral-200 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-4">
                    <button type="button" @click="tab = 'receipts'" :class="tab === 'receipts' ? 'border-primary-600 text-primary-600 font-bold border-b-2' : 'border-transparent text-neutral-500 hover:text-neutral-800 font-semibold'" class="pb-3 text-xs flex items-center gap-1.5 transition-all">
                        <iconify-icon icon="solar:import-outline" class="text-base"></iconify-icon>
                        Lịch sử Phiếu Nhập kho
                        <span class="bg-neutral-100 text-neutral-700 text-[10px] px-2 py-0.5 rounded-full font-bold">{{ $receipts->total() }}</span>
                    </button>
                    <button type="button" @click="tab = 'payments'" :class="tab === 'payments' ? 'border-primary-600 text-primary-600 font-bold border-b-2' : 'border-transparent text-neutral-500 hover:text-neutral-800 font-semibold'" class="pb-3 text-xs flex items-center gap-1.5 transition-all">
                        <iconify-icon icon="solar:wallet-money-outline" class="text-base"></iconify-icon>
                        Lịch sử Thanh toán / Chi tiền
                        <span class="bg-neutral-100 text-neutral-700 text-[10px] px-2 py-0.5 rounded-full font-bold">{{ $payments->total() }}</span>
                    </button>
                </div>
                <div class="pb-2">
                    <button type="button" x-show="tab === 'payments'" onclick="openPaymentModal()" class="btn btn-sm bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1 shadow-sm">
                        <iconify-icon icon="ic:baseline-plus" class="text-sm"></iconify-icon> Ghi nhận thanh toán
                    </button>
                </div>
            </div>

            {{-- Tab 1: Receipts --}}
            <div x-show="tab === 'receipts'" class="overflow-x-auto w-full">
                <table class="w-full border-collapse text-xs text-left">
                    <thead>
                        <tr class="bg-neutral-50 text-neutral-700 uppercase font-bold border-b border-neutral-200">
                            <th class="py-3 px-4 w-12 text-center">STT</th>
                            <th class="py-3 px-4 w-32">Mã phiếu</th>
                            <th class="py-3 px-4 w-28">Ngày nhập</th>
                            <th class="py-3 px-4 w-36">Kho nhận</th>
                            <th class="py-3 px-4 w-32 text-right">Tổng số lượng</th>
                            <th class="py-3 px-4 w-36 text-right">Thành tiền (đ)</th>
                            <th class="py-3 px-4 w-28 text-center">Trạng thái</th>
                            <th class="py-3 px-4 min-w-[160px]">Ghi chú</th>
                            <th class="py-3 px-4 w-24 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 bg-white">
                        @forelse($receipts as $idx => $rc)
                            <tr class="hover:bg-primary-50/40 transition-colors">
                                <td class="py-3 px-4 text-center text-neutral-400 font-medium">{{ $receipts->firstItem() + $idx }}</td>
                                <td class="py-3 px-4">
                                    <a href="{{ route('inventory.receipts.show', $rc) }}" class="font-bold text-primary-600 hover:text-primary-800 hover:underline">
                                        {{ $rc->code }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-neutral-600">{{ date('d/m/Y', strtotime($rc->date)) }}</td>
                                <td class="py-3 px-4 font-medium text-neutral-800">{{ $rc->warehouse?->name ?? 'Kho chính' }}</td>
                                <td class="py-3 px-4 text-right font-bold text-emerald-700">{{ number_format($rc->total_quantity, 2) }}</td>
                                <td class="py-3 px-4 text-right font-black text-neutral-900">{{ number_format($rc->total_amount, 0, ',', '.') }}đ</td>
                                <td class="py-3 px-4 text-center">
                                    @if($rc->status === 'cancelled')
                                        <span class="bg-rose-100 text-rose-800 px-2 py-0.5 rounded text-[10px] font-bold">Đã hủy</span>
                                    @else
                                        <span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded text-[10px] font-bold">Hoàn tất</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-neutral-500">{{ $rc->notes ?: '-' }}</td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ route('inventory.receipts.show', $rc) }}" class="text-primary-600 hover:text-primary-800 font-bold" title="Xem chi tiết">
                                        Chi tiết &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-neutral-400">
                                    Chưa có phiếu nhập kho nào từ nhà cung cấp này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($receipts->hasPages())
                    <div class="p-4 border-t border-neutral-200">
                        {{ $receipts->links() }}
                    </div>
                @endif
            </div>

            {{-- Tab 2: Payments --}}
            <div x-show="tab === 'payments'" style="display: none;" class="overflow-x-auto w-full">
                <table class="w-full border-collapse text-xs text-left">
                    <thead>
                        <tr class="bg-neutral-50 text-neutral-700 uppercase font-bold border-b border-neutral-200">
                            <th class="py-3 px-4 w-12 text-center">STT</th>
                            <th class="py-3 px-4 w-32">Mã phiếu chi</th>
                            <th class="py-3 px-4 w-28">Ngày chi</th>
                            <th class="py-3 px-4 w-36 text-right">Số tiền chi (đ)</th>
                            <th class="py-3 px-4 w-32">Hình thức</th>
                            <th class="py-3 px-4 w-32">Gắn phiếu PNK</th>
                            <th class="py-3 px-4 min-w-[200px]">Ghi chú</th>
                            <th class="py-3 px-4 w-28">Người tạo</th>
                            <th class="py-3 px-4 w-20 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 bg-white">
                        @forelse($payments as $idx => $pm)
                            <tr class="hover:bg-primary-50/40 transition-colors">
                                <td class="py-3 px-4 text-center text-neutral-400 font-medium">{{ $payments->firstItem() + $idx }}</td>
                                <td class="py-3 px-4 font-bold text-neutral-800">{{ $pm->payment_code }}</td>
                                <td class="py-3 px-4 text-neutral-600">{{ date('d/m/Y', strtotime($pm->payment_date)) }}</td>
                                <td class="py-3 px-4 text-right font-black text-rose-600 text-sm">{{ number_format($pm->amount, 0, ',', '.') }}đ</td>
                                <td class="py-3 px-4 text-neutral-700">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200">
                                        {{ $pm->payment_method ?: 'Tiền mặt' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($pm->receipt)
                                        <a href="{{ route('inventory.receipts.show', $pm->receipt) }}" class="font-bold text-primary-600 hover:underline">
                                            {{ $pm->receipt->code }}
                                        </a>
                                    @else
                                        <span class="text-neutral-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-neutral-600">{{ $pm->note ?: '-' }}</td>
                                <td class="py-3 px-4 text-neutral-500">{{ $pm->creator?->name ?? 'Hệ thống' }}</td>
                                <td class="py-3 px-4 text-center">
                                    <form action="{{ route('suppliers.payments.destroy', [$supplier, $pm]) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc muốn xóa phiếu chi {{ $pm->payment_code }}? Công nợ sẽ được tính lại.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 font-bold p-1" title="Xóa phiếu chi">
                                            <iconify-icon icon="solar:trash-bin-trash-bold" class="text-base"></iconify-icon>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-neutral-400">
                                    Chưa có lịch sử thanh toán nào cho nhà cung cấp này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($payments->hasPages())
                    <div class="p-4 border-t border-neutral-200">
                        {{ $payments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODAL GHI NHẬN THANH TOÁN (PHIẾU CHI CHO NCC) --}}
<div id="paymentModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-neutral-900 bg-opacity-50 transition-opacity" onclick="closePaymentModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="p-5 border-b border-neutral-200 flex items-center justify-between bg-neutral-50">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center">
                        <iconify-icon icon="solar:hand-money-bold" class="text-lg"></iconify-icon>
                    </div>
                    <h5 class="font-bold text-neutral-800 text-base mb-0">Tạo Phiếu Chi / Trả tiền NCC</h5>
                </div>
                <button type="button" onclick="closePaymentModal()" class="text-neutral-400 hover:text-neutral-700 text-xl font-bold leading-none">&times;</button>
            </div>

            <form action="{{ route('suppliers.payments.store', $supplier) }}" method="POST" class="p-6">
                @csrf
                <div class="mb-4 bg-rose-50 border border-rose-200 rounded-xl p-3 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-rose-800 font-semibold block">Nhà cung cấp:</span>
                        <span class="text-sm font-bold text-neutral-800">{{ $supplier->name }} ({{ $supplier->code }})</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-rose-800 font-semibold block">Công nợ hiện tại:</span>
                        <span class="text-base font-black text-rose-700">{{ number_format($supplier->debt, 0, ',', '.') }}đ</span>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Ngày chi tiền <span class="text-danger-500">*</span></label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500 font-semibold" required>
                    </div>

                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Số tiền thanh toán (đ) <span class="text-danger-500">*</span></label>
                        <input type="number" step="1000" min="1" name="amount" value="{{ max(0, (int)$supplier->debt) }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-base font-black text-rose-600 focus:border-primary-500" required>
                    </div>

                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Hình thức thanh toán</label>
                        <select name="payment_method" class="form-select rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500 font-semibold bg-white">
                            <option value="Chuyển khoản">Chuyển khoản ngân hàng</option>
                            <option value="Tiền mặt">Tiền mặt</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Gắn với Phiếu Nhập kho cụ thể (Tùy chọn)</label>
                        <select name="receipt_id" class="form-select rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500 bg-white">
                            <option value="">-- Trừ vào tổng công nợ chung --</option>
                            @foreach($receipts as $r)
                                <option value="{{ $r->id }}">{{ $r->code }} ({{ date('d/m/Y', strtotime($r->date)) }} - {{ number_format($r->total_amount, 0, ',', '.') }}đ)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Ghi chú phiếu chi</label>
                        <input type="text" name="note" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="VD: Chuyển khoản thanh toán đợt 1...">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-5 mt-6 border-t border-neutral-200">
                    <button type="button" onclick="closePaymentModal()" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-4 py-2 rounded-lg text-xs font-semibold">
                        Hủy
                    </button>
                    <button type="submit" class="btn bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm">
                        <iconify-icon icon="solar:check-circle-bold" class="text-base"></iconify-icon> Xác nhận chi tiền
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openPaymentModal() {
        document.getElementById('paymentModal').classList.remove('hidden');
    }
    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
    }
</script>
@endsection

