@extends('layout.layout')
@php
    $title    = 'Chi tiết Lệnh sản xuất';
    $subTitle = $manufacture->code;
    
    // Status mapping for active states
    $statusSteps = [
        'initialized' => 1,
        'tech_approved' => 2,
        'manager_approved' => 3,
        'stamps_received' => 4,
        'in_production' => 5,
        'completed' => 6
    ];
    
    $currentStepNum = $statusSteps[$manufacture->status] ?? 1;
@endphp

@section('content')

{{-- Stepper Progress Tracker --}}
<div class="card border-0 rounded-xl mb-6 shadow-sm overflow-hidden bg-white">
    <div class="px-6 py-6 border-b border-neutral-100">
        <div class="relative flex items-center justify-between w-full">
            {{-- Background line --}}
            <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-1 bg-neutral-200 z-0"></div>
            {{-- Active background line fill --}}
            <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-primary-600 transition-all duration-500 z-0" style="width: {{ (($currentStepNum - 1) / 5) * 100 }}%"></div>

            {{-- Step 1: KHỞI TẠO --}}
            <div class="flex flex-col items-center relative z-10">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 {{ $currentStepNum >= 1 ? 'bg-primary-600 text-white ring-4 ring-primary-100' : 'bg-neutral-200 text-neutral-500' }}">
                    <iconify-icon icon="lucide:clock" class="text-lg"></iconify-icon>
                </div>
                <span class="text-xs font-bold mt-2 uppercase tracking-wide {{ $currentStepNum >= 1 ? 'text-primary-600' : 'text-neutral-500' }}">Khởi tạo</span>
            </div>

            {{-- Step 2: KT DUYỆT --}}
            <div class="flex flex-col items-center relative z-10">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 {{ $currentStepNum >= 2 ? 'bg-primary-600 text-white ring-4 ring-primary-100' : 'bg-neutral-200 text-neutral-500' }}">
                    <iconify-icon icon="lucide:shield-check" class="text-lg"></iconify-icon>
                </div>
                <span class="text-xs font-bold mt-2 uppercase tracking-wide {{ $currentStepNum >= 2 ? 'text-primary-600' : 'text-neutral-500' }}">KT Duyệt</span>
            </div>

            {{-- Step 3: QĐ DUYỆT --}}
            <div class="flex flex-col items-center relative z-10">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 {{ $currentStepNum >= 3 ? 'bg-primary-600 text-white ring-4 ring-primary-100' : 'bg-neutral-200 text-neutral-500' }}">
                    <iconify-icon icon="lucide:user-check" class="text-lg"></iconify-icon>
                </div>
                <span class="text-xs font-bold mt-2 uppercase tracking-wide {{ $currentStepNum >= 3 ? 'text-primary-600' : 'text-neutral-500' }}">QĐ Duyệt</span>
            </div>

            {{-- Step 4: NHẬN TEM --}}
            <div class="flex flex-col items-center relative z-10">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 {{ $currentStepNum >= 4 ? 'bg-primary-600 text-white ring-4 ring-primary-100' : 'bg-neutral-200 text-neutral-500' }}">
                    <iconify-icon icon="lucide:tag" class="text-lg"></iconify-icon>
                </div>
                <span class="text-xs font-bold mt-2 uppercase tracking-wide {{ $currentStepNum >= 4 ? 'text-primary-600' : 'text-neutral-500' }}">Nhận tem</span>
            </div>

            {{-- Step 5: SẢN XUẤT --}}
            <div class="flex flex-col items-center relative z-10">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 {{ $currentStepNum >= 5 ? 'bg-primary-600 text-white ring-4 ring-primary-100' : 'bg-neutral-200 text-neutral-500' }}">
                    <iconify-icon icon="lucide:cog" class="text-lg"></iconify-icon>
                </div>
                <span class="text-xs font-bold mt-2 uppercase tracking-wide {{ $currentStepNum >= 5 ? 'text-primary-600' : 'text-neutral-500' }}">Sản xuất</span>
            </div>

            {{-- Step 6: HOÀN THÀNH --}}
            <div class="flex flex-col items-center relative z-10">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 {{ $currentStepNum >= 6 ? 'bg-success-600 text-white ring-4 ring-success-100' : 'bg-neutral-200 text-neutral-500' }}">
                    <iconify-icon icon="lucide:check-circle" class="text-lg"></iconify-icon>
                </div>
                <span class="text-xs font-bold mt-2 uppercase tracking-wide {{ $currentStepNum >= 6 ? 'text-success-600' : 'text-neutral-500' }}">Hoàn thành</span>
            </div>
        </div>
    </div>
</div>

{{-- Main Grid --}}
<div class="grid grid-cols-12 gap-6">
    {{-- Left column: Danh sách các tấm --}}
    <div class="col-span-12 lg:col-span-8 space-y-6">
        <div class="card p-0 rounded-xl border-0 overflow-hidden shadow-sm bg-white">
            <div class="card-header border-b border-neutral-100 bg-white py-4 px-6 flex items-center justify-between">
                <h6 class="font-bold text-base text-neutral-800 m-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:layers" class="text-xl text-primary-500"></iconify-icon>
                    Danh sách các tấm ({{ $items->count() }})
                </h6>
                <div class="flex items-center gap-2">
                    <a href="{{ route('manufactures.print-stamps', $manufacture) }}" target="_blank"
                       class="btn btn-outline-primary text-sm btn-sm px-3 py-2 rounded-lg flex items-center gap-1.5 font-semibold">
                        <iconify-icon icon="lucide:printer" class="text-lg"></iconify-icon> In tem QR
                    </a>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive border-0 overflow-x-auto scroll-sm">
                    <table class="table bordered-table sm-table mb-0">
                        <thead class="bg-neutral-50">
                            <tr>
                                <th scope="col" style="width: 50px;" class="text-center">Stt</th>
                                <th scope="col" style="width: 80px;" class="text-center">QR Code</th>
                                <th scope="col">Mã SP</th>
                                <th scope="col">Sản phẩm</th>
                                <th scope="col">Loại gỗ / Vật tư</th>
                                <th scope="col" class="text-center">Kích thước (mm)</th>
                                <th scope="col" class="text-center">SL</th>
                                <th scope="col">Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $index => $item)
                            <tr>
                                <td class="text-center align-middle">{{ $index + 1 }}</td>
                                <td class="text-center align-middle py-2">
                                    @if($item->product_code)
                                    <div class="relative group w-12 h-12 mx-auto border border-neutral-200 rounded p-0.5 bg-white shadow-sm flex items-center justify-center cursor-zoom-in" onclick="showQrModal('{{ $item->product_code }}')">
                                        <img src="{{ route('manufactures.qr', $item->product_code) }}" class="w-full h-full object-contain">
                                    </div>
                                    @else
                                    <span class="text-neutral-400">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-semibold text-secondary-light">{{ $item->product_code ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="text-base text-secondary-light font-medium">{{ $item->product_name }}</span>
                                    <span class="block text-xs text-neutral-400">Đơn hàng: {{ $item->order_code }}</span>
                                </td>
                                <td>
                                    <span class="text-base text-secondary-light">{{ $item->supply_name ?? '—' }}</span>
                                </td>
                                <td class="text-center align-middle font-medium text-secondary-light">
                                    {{ $item->dimensions ?: '—' }}
                                </td>
                                <td class="text-center align-middle font-semibold text-secondary-light">
                                    {{ $item->quantity }}
                                </td>
                                <td>
                                    <span class="text-xs text-neutral-500 block max-w-xs truncate" title="{{ $item->notes }}">{{ $item->notes ?? '—' }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-8">
                                    <p class="text-neutral-500 mb-0">Không có tấm gỗ/sản phẩm nào trong lệnh sản xuất này.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Right column: Thông tin lệnh & Lịch sử duyệt --}}
    <div class="col-span-12 lg:col-span-4 space-y-6">
        
        {{-- Flash message inside right col --}}
        @if(session('success') || session('error'))
        <div class="card p-0 rounded-xl border-0 overflow-hidden shadow-sm">
            <div class="p-4">
                @if(session('success'))
                <div class="alert alert-success bg-success-50 text-success-600 border border-success-200 rounded-lg p-3 m-0 text-sm">
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger bg-danger-50 text-danger-600 border border-danger-200 rounded-lg p-3 m-0 text-sm">
                    {{ session('error') }}
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Action Card based on current step & permissions --}}
        @can('approve manufacture')
        @if($manufacture->status !== 'completed')
        <div class="card border-0 rounded-xl p-5 shadow-sm bg-white">
            <h6 class="font-bold text-sm text-neutral-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                <iconify-icon icon="lucide:user-check" class="text-lg text-primary-500"></iconify-icon>
                Thao tác phê duyệt
            </h6>
            
            @if($manufacture->status === 'initialized')
                <form action="{{ route('manufactures.approve', [$manufacture, 'approve_tech']) }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-primary w-full py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 shadow-sm">
                        <iconify-icon icon="lucide:shield-check" class="text-lg"></iconify-icon>
                        Duyệt kỹ thuật (KT DUYỆT)
                    </button>
                </form>
            @elseif($manufacture->status === 'tech_approved')
                <form action="{{ route('manufactures.approve', [$manufacture, 'approve_manager']) }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-warning w-full py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 shadow-sm text-white">
                        <iconify-icon icon="lucide:user-check" class="text-lg"></iconify-icon>
                        Quản đốc duyệt (QĐ DUYỆT)
                    </button>
                </form>
            @elseif($manufacture->status === 'manager_approved')
                <form action="{{ route('manufactures.approve', [$manufacture, 'receive_stamps']) }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-info w-full py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 shadow-sm">
                        <iconify-icon icon="lucide:tag" class="text-lg"></iconify-icon>
                        Nhận tem sản xuất (NHẬN TEM)
                    </button>
                </form>
            @elseif($manufacture->status === 'stamps_received')
                <form action="{{ route('manufactures.approve', [$manufacture, 'start_production']) }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-success w-full py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 shadow-sm">
                        <iconify-icon icon="lucide:cog" class="text-lg animate-spin" style="animation-duration: 3s;"></iconify-icon>
                        Bắt đầu sản xuất (SẢN XUẤT)
                    </button>
                </form>
            @elseif($manufacture->status === 'in_production')
                <form action="{{ route('manufactures.approve', [$manufacture, 'complete']) }}" method="POST" class="m-0" onsubmit="return confirm('Bạn có chắc muốn hoàn thành lệnh sản xuất này?')">
                    @csrf
                    <button type="submit" class="btn bg-success-600 hover:bg-success-700 text-white w-full py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 shadow-sm">
                        <iconify-icon icon="lucide:check-circle" class="text-lg"></iconify-icon>
                        Hoàn thành lệnh (HOÀN THÀNH)
                    </button>
                </form>
            @endif
        </div>
        @endif
        @endcan

        {{-- Thông tin lệnh --}}
        <div class="card border-0 rounded-xl p-5 shadow-sm bg-white">
            <h6 class="font-bold text-sm text-neutral-800 uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-neutral-100 pb-3">
                <iconify-icon icon="lucide:info" class="text-lg text-primary-500"></iconify-icon>
                Thông tin lệnh
            </h6>
            
            <div class="space-y-4 text-sm">
                <div class="flex justify-between items-center py-2 border-b border-neutral-50">
                    <span class="text-neutral-500 font-medium">Đơn hàng ghép:</span>
                    <span class="font-semibold text-neutral-800 flex flex-wrap justify-end gap-1 max-w-[200px]">
                        @foreach($manufacture->orders as $o)
                        <a href="{{ route('orders.show', $o) }}" class="badge bg-neutral-100 hover:bg-neutral-200 text-neutral-700 text-xs px-2 py-0.5 rounded font-bold transition-all">
                            {{ $o->order_code }}
                        </a>
                        @endforeach
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-neutral-50">
                    <span class="text-neutral-500 font-medium">Tổng số tem:</span>
                    <span class="font-bold text-neutral-800 text-base">{{ $items->sum('quantity') }}</span>
                </div>
                <div class="py-2">
                    <span class="text-neutral-500 font-medium block mb-1">Ghi chú lệnh:</span>
                    <p class="text-neutral-700 bg-neutral-50 rounded-lg p-3 text-xs leading-relaxed italic m-0 border border-neutral-100">
                        {{ $manufacture->notes ?: 'Không có ghi chú' }}
                    </p>
                </div>
                
                @can('edit manufacture')
                <div class="pt-2">
                    <a href="{{ route('manufactures.edit', $manufacture) }}" class="btn btn-outline-neutral w-full py-2 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5">
                        <iconify-icon icon="lucide:edit" class="text-sm"></iconify-icon>
                        Chỉnh sửa lệnh ghép
                    </a>
                </div>
                @endcan
            </div>
        </div>

        {{-- Lịch sử duyệt --}}
        <div class="card border-0 rounded-xl p-5 shadow-sm bg-white">
            <h6 class="font-bold text-sm text-neutral-800 uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-neutral-100 pb-3">
                <iconify-icon icon="lucide:history" class="text-lg text-primary-500"></iconify-icon>
                Lịch sử duyệt
            </h6>
            
            <div class="space-y-4">
                {{-- 1. Kỹ thuật duyệt --}}
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $manufacture->techApprover ? 'bg-success-50 text-success-600' : 'bg-neutral-100 text-neutral-400' }}">
                        <iconify-icon icon="lucide:shield" class="text-base"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-sm font-semibold block text-neutral-800">Kỹ thuật duyệt</span>
                        @if($manufacture->techApprover)
                            <span class="text-xs text-neutral-600 block">{{ $manufacture->techApprover->name }}</span>
                            <span class="text-[10px] text-neutral-400 block">{{ $manufacture->tech_approved_at->format('d/m/Y H:i') }}</span>
                        @else
                            <span class="text-xs text-neutral-400 block italic">Chưa duyệt</span>
                        @endif
                    </div>
                </div>

                {{-- 2. Quản đốc duyệt --}}
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $manufacture->managerApprover ? 'bg-success-50 text-success-600' : 'bg-neutral-100 text-neutral-400' }}">
                        <iconify-icon icon="lucide:user" class="text-base"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-sm font-semibold block text-neutral-800">Quản đốc duyệt</span>
                        @if($manufacture->managerApprover)
                            <span class="text-xs text-neutral-600 block">{{ $manufacture->managerApprover->name }}</span>
                            <span class="text-[10px] text-neutral-400 block">{{ $manufacture->manager_approved_at->format('d/m/Y H:i') }}</span>
                        @else
                            <span class="text-xs text-neutral-400 block italic">Chưa duyệt</span>
                        @endif
                    </div>
                </div>

                {{-- 3. Nhận tem sản xuất --}}
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $manufacture->stampsReceiver ? 'bg-success-50 text-success-600' : 'bg-neutral-100 text-neutral-400' }}">
                        <iconify-icon icon="lucide:tag" class="text-base"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-sm font-semibold block text-neutral-800">Nhận tem sản xuất</span>
                        @if($manufacture->stampsReceiver)
                            <span class="text-xs text-neutral-600 block">{{ $manufacture->stampsReceiver->name }}</span>
                            <span class="text-[10px] text-neutral-400 block">{{ $manufacture->stamps_received_at->format('d/m/Y H:i') }}</span>
                        @else
                            <span class="text-xs text-neutral-400 block italic">Chưa nhận</span>
                        @endif
                    </div>
                </div>

                {{-- 4. Bắt đầu sản xuất --}}
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $manufacture->productionStarter ? 'bg-success-50 text-success-600' : 'bg-neutral-100 text-neutral-400' }}">
                        <iconify-icon icon="lucide:cog" class="text-base"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-sm font-semibold block text-neutral-800">Bắt đầu sản xuất</span>
                        @if($manufacture->productionStarter)
                            <span class="text-xs text-neutral-600 block">{{ $manufacture->productionStarter->name }}</span>
                            <span class="text-[10px] text-neutral-400 block">{{ $manufacture->production_started_at->format('d/m/Y H:i') }}</span>
                        @else
                            <span class="text-xs text-neutral-400 block italic">Chưa bắt đầu</span>
                        @endif
                    </div>
                </div>

                {{-- 5. Hoàn thành --}}
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $manufacture->completer ? 'bg-success-50 text-success-600' : 'bg-neutral-100 text-neutral-400' }}">
                        <iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-sm font-semibold block text-neutral-800">Hoàn thành lệnh</span>
                        @if($manufacture->completer)
                            <span class="text-xs text-neutral-600 block">{{ $manufacture->completer->name }}</span>
                            <span class="text-[10px] text-neutral-400 block">{{ $manufacture->completed_at->format('d/m/Y H:i') }}</span>
                        @else
                            <span class="text-xs text-neutral-400 block italic">Chưa hoàn thành</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- QR Code Zoom Modal --}}
<div class="modal fade hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center" id="qrZoomModal" tabindex="-1">
    <div class="relative bg-white rounded-xl shadow-lg max-w-sm w-full mx-4 overflow-hidden border border-neutral-100">
        <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50">
            <h6 class="font-bold text-sm text-neutral-800 m-0 uppercase tracking-wide">Mã QR Sản phẩm</h6>
            <button type="button" onclick="closeQrModal()" class="text-neutral-500 hover:text-neutral-800 text-2xl leading-none">&times;</button>
        </div>
        <div class="p-8 flex flex-col items-center justify-center bg-white">
            <div class="w-64 h-64 border border-neutral-200 rounded-lg p-2 bg-neutral-50 flex items-center justify-center shadow-sm">
                <img id="qrZoomImage" src="" class="w-full h-full object-contain">
            </div>
            <span class="mt-4 font-bold text-neutral-700 text-base" id="qrZoomCode"></span>
        </div>
        <div class="px-6 py-3 border-t border-neutral-100 bg-neutral-50 flex justify-end">
            <button type="button" onclick="closeQrModal()" class="btn btn-sm btn-neutral px-4 py-2 rounded-lg text-xs font-semibold">Đóng</button>
        </div>
    </div>
</div>

<script>
function showQrModal(code) {
    const modal = document.getElementById('qrZoomModal');
    const img = document.getElementById('qrZoomImage');
    const label = document.getElementById('qrZoomCode');
    if (modal && img && label) {
        img.src = '/manufactures/qr/' + code;
        label.textContent = code;
        modal.classList.remove('hidden');
    }
}

function closeQrModal() {
    const modal = document.getElementById('qrZoomModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('qrZoomModal');
    if (event.target === modal) {
        closeQrModal();
    }
});
</script>

@endsection
