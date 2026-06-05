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
<div class="bg-white border border-neutral-200 rounded-xl mb-6 shadow-sm overflow-hidden">
    <div class="px-6 py-8">
        <div class="relative flex flex-col md:flex-row justify-between w-full gap-6 md:gap-0">
            
            {{-- Desktop Line --}}
            <div class="hidden md:block absolute left-[5%] right-[5%] top-[24px] h-[3px] bg-neutral-200 z-0 rounded-full">
                <div class="h-full bg-primary-600 transition-all duration-500 rounded-full" style="width: {{ (($currentStepNum - 1) / 5) * 100 }}%"></div>
            </div>

            {{-- Mobile Line --}}
            <div class="absolute left-[24px] top-[24px] bottom-[24px] w-[3px] bg-neutral-200 z-0 md:hidden rounded-full">
                <div class="w-full bg-primary-600 transition-all duration-500 rounded-full" style="height: {{ (($currentStepNum - 1) / 5) * 100 }}%"></div>
            </div>

            {{-- Step 1: Khởi tạo --}}
            @php
                $isCompleted = $currentStepNum > 1;
                $isActive = $currentStepNum == 1;
            @endphp
            <div class="flex flex-row md:flex-col items-center md:text-center relative z-10 md:w-[15%] group">
                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold transition-all duration-300 flex-shrink-0
                    {{ $isCompleted ? 'bg-success-500 text-white shadow-sm border border-success-500' : '' }}
                    {{ $isActive ? 'bg-primary-600 text-white ring-4 ring-primary-50 shadow-sm border border-primary-600 animate-pulse' : '' }}
                    {{ !$isCompleted && !$isActive ? 'bg-neutral-100 text-neutral-400 border border-neutral-200' : '' }}">
                    @if($isCompleted)
                        <iconify-icon icon="lucide:check" class="text-xl"></iconify-icon>
                    @else
                        <iconify-icon icon="lucide:clock" class="text-xl"></iconify-icon>
                    @endif
                </div>
                <div class="ml-4 md:ml-0 md:mt-3 flex-1 text-left md:text-center">
                    <span class="text-xs font-extrabold uppercase tracking-wider block
                        {{ $isCompleted ? 'text-success-600' : '' }}
                        {{ $isActive ? 'text-primary-600 font-bold' : '' }}
                        {{ !$isCompleted && !$isActive ? 'text-neutral-400' : '' }}">
                        Khởi tạo
                    </span>
                    <span class="block text-[10px] text-neutral-500 font-medium mt-0.5">{{ $manufacture->creator->name ?? 'Hệ thống' }}</span>
                    <span class="block text-[9px] text-neutral-400">{{ $manufacture->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            {{-- Step 2: KT DUYỆT --}}
            @php
                $isCompleted = $currentStepNum > 2;
                $isActive = $currentStepNum == 2;
            @endphp
            <div class="flex flex-row md:flex-col items-center md:text-center relative z-10 md:w-[15%] group">
                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold transition-all duration-300 flex-shrink-0
                    {{ $isCompleted ? 'bg-success-500 text-white shadow-sm border border-success-500' : '' }}
                    {{ $isActive ? 'bg-primary-600 text-white ring-4 ring-primary-50 shadow-sm border border-primary-600 animate-pulse' : '' }}
                    {{ !$isCompleted && !$isActive ? 'bg-neutral-100 text-neutral-400 border border-neutral-200' : '' }}">
                    @if($isCompleted)
                        <iconify-icon icon="lucide:check" class="text-xl"></iconify-icon>
                    @else
                        <iconify-icon icon="lucide:shield-check" class="text-xl"></iconify-icon>
                    @endif
                </div>
                <div class="ml-4 md:ml-0 md:mt-3 flex-1 text-left md:text-center">
                    <span class="text-xs font-extrabold uppercase tracking-wider block
                        {{ $isCompleted ? 'text-success-600' : '' }}
                        {{ $isActive ? 'text-primary-600 font-bold' : '' }}
                        {{ !$isCompleted && !$isActive ? 'text-neutral-400' : '' }}">
                        KT Duyệt
                    </span>
                    @if($manufacture->techApprover)
                        <span class="block text-[10px] text-neutral-500 font-medium mt-0.5">{{ $manufacture->techApprover->name }}</span>
                        <span class="block text-[9px] text-neutral-400">{{ $manufacture->tech_approved_at->format('d/m/Y H:i') }}</span>
                    @else
                        <span class="block text-[10px] text-neutral-400 italic mt-0.5">Chờ duyệt</span>
                    @endif
                </div>
            </div>

            {{-- Step 3: QĐ DUYỆT --}}
            @php
                $isCompleted = $currentStepNum > 3;
                $isActive = $currentStepNum == 3;
            @endphp
            <div class="flex flex-row md:flex-col items-center md:text-center relative z-10 md:w-[15%] group">
                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold transition-all duration-300 flex-shrink-0
                    {{ $isCompleted ? 'bg-success-500 text-white shadow-sm border border-success-500' : '' }}
                    {{ $isActive ? 'bg-primary-600 text-white ring-4 ring-primary-50 shadow-sm border border-primary-600 animate-pulse' : '' }}
                    {{ !$isCompleted && !$isActive ? 'bg-neutral-100 text-neutral-400 border border-neutral-200' : '' }}">
                    @if($isCompleted)
                        <iconify-icon icon="lucide:check" class="text-xl"></iconify-icon>
                    @else
                        <iconify-icon icon="lucide:user-check" class="text-xl"></iconify-icon>
                    @endif
                </div>
                <div class="ml-4 md:ml-0 md:mt-3 flex-1 text-left md:text-center">
                    <span class="text-xs font-extrabold uppercase tracking-wider block
                        {{ $isCompleted ? 'text-success-600' : '' }}
                        {{ $isActive ? 'text-primary-600 font-bold' : '' }}
                        {{ !$isCompleted && !$isActive ? 'text-neutral-400' : '' }}">
                        QĐ Duyệt
                    </span>
                    @if($manufacture->managerApprover)
                        <span class="block text-[10px] text-neutral-500 font-medium mt-0.5">{{ $manufacture->managerApprover->name }}</span>
                        <span class="block text-[9px] text-neutral-400">{{ $manufacture->manager_approved_at->format('d/m/Y H:i') }}</span>
                    @else
                        <span class="block text-[10px] text-neutral-400 italic mt-0.5">Chờ duyệt</span>
                    @endif
                </div>
            </div>

            {{-- Step 4: NHẬN TEM --}}
            @php
                $isCompleted = $currentStepNum > 4;
                $isActive = $currentStepNum == 4;
            @endphp
            <div class="flex flex-row md:flex-col items-center md:text-center relative z-10 md:w-[15%] group">
                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold transition-all duration-300 flex-shrink-0
                    {{ $isCompleted ? 'bg-success-500 text-white shadow-sm border border-success-500' : '' }}
                    {{ $isActive ? 'bg-primary-600 text-white ring-4 ring-primary-50 shadow-sm border border-primary-600 animate-pulse' : '' }}
                    {{ !$isCompleted && !$isActive ? 'bg-neutral-100 text-neutral-400 border border-neutral-200' : '' }}">
                    @if($isCompleted)
                        <iconify-icon icon="lucide:check" class="text-xl"></iconify-icon>
                    @else
                        <iconify-icon icon="lucide:tag" class="text-xl"></iconify-icon>
                    @endif
                </div>
                <div class="ml-4 md:ml-0 md:mt-3 flex-1 text-left md:text-center">
                    <span class="text-xs font-extrabold uppercase tracking-wider block
                        {{ $isCompleted ? 'text-success-600' : '' }}
                        {{ $isActive ? 'text-primary-600 font-bold' : '' }}
                        {{ !$isCompleted && !$isActive ? 'text-neutral-400' : '' }}">
                        Nhận tem
                    </span>
                    @if($manufacture->stampsReceiver)
                        <span class="block text-[10px] text-neutral-500 font-medium mt-0.5">{{ $manufacture->stampsReceiver->name }}</span>
                        <span class="block text-[9px] text-neutral-400">{{ $manufacture->stamps_received_at->format('d/m/Y H:i') }}</span>
                    @else
                        <span class="block text-[10px] text-neutral-400 italic mt-0.5">Chưa nhận</span>
                    @endif
                </div>
            </div>

            {{-- Step 5: SẢN XUẤT --}}
            @php
                $isCompleted = $currentStepNum > 5;
                $isActive = $currentStepNum == 5;
            @endphp
            <div class="flex flex-row md:flex-col items-center md:text-center relative z-10 md:w-[15%] group">
                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold transition-all duration-300 flex-shrink-0
                    {{ $isCompleted ? 'bg-success-500 text-white shadow-sm border border-success-500' : '' }}
                    {{ $isActive ? 'bg-primary-600 text-white ring-4 ring-primary-50 shadow-sm border border-primary-600 animate-pulse' : '' }}
                    {{ !$isCompleted && !$isActive ? 'bg-neutral-100 text-neutral-400 border border-neutral-200' : '' }}">
                    @if($isCompleted)
                        <iconify-icon icon="lucide:check" class="text-xl"></iconify-icon>
                    @else
                        <iconify-icon icon="lucide:cog" class="text-xl"></iconify-icon>
                    @endif
                </div>
                <div class="ml-4 md:ml-0 md:mt-3 flex-1 text-left md:text-center">
                    <span class="text-xs font-extrabold uppercase tracking-wider block
                        {{ $isCompleted ? 'text-success-600' : '' }}
                        {{ $isActive ? 'text-primary-600 font-bold' : '' }}
                        {{ !$isCompleted && !$isActive ? 'text-neutral-400' : '' }}">
                        Sản xuất
                    </span>
                    @if($manufacture->productionStarter)
                        <span class="block text-[10px] text-neutral-500 font-medium mt-0.5">{{ $manufacture->productionStarter->name }}</span>
                        <span class="block text-[9px] text-neutral-400">{{ $manufacture->production_started_at->format('d/m/Y H:i') }}</span>
                    @else
                        <span class="block text-[10px] text-neutral-400 italic mt-0.5">Chưa bắt đầu</span>
                    @endif
                </div>
            </div>

            {{-- Step 6: HOÀN THÀNH --}}
            @php
                $isCompleted = $currentStepNum > 6;
                $isActive = $currentStepNum == 6;
            @endphp
            <div class="flex flex-row md:flex-col items-center md:text-center relative z-10 md:w-[15%] group">
                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold transition-all duration-300 flex-shrink-0
                    {{ $isCompleted ? 'bg-success-500 text-white shadow-sm border border-success-500' : '' }}
                    {{ $isActive ? 'bg-primary-600 text-white ring-4 ring-primary-50 shadow-sm border border-primary-600 animate-pulse' : '' }}
                    {{ !$isCompleted && !$isActive ? 'bg-neutral-100 text-neutral-400 border border-neutral-200' : '' }}">
                    @if($currentStepNum == 6)
                        <iconify-icon icon="lucide:check-circle" class="text-xl"></iconify-icon>
                    @else
                        <iconify-icon icon="lucide:flag" class="text-xl"></iconify-icon>
                    @endif
                </div>
                <div class="ml-4 md:ml-0 md:mt-3 flex-1 text-left md:text-center">
                    <span class="text-xs font-extrabold uppercase tracking-wider block
                        {{ $currentStepNum == 6 ? 'text-success-600 font-bold' : 'text-neutral-400' }}">
                        Hoàn thành
                    </span>
                    @if($manufacture->completer)
                        <span class="block text-[10px] text-neutral-500 font-medium mt-0.5">{{ $manufacture->completer->name }}</span>
                        <span class="block text-[9px] text-neutral-400">{{ $manufacture->completed_at->format('d/m/Y H:i') }}</span>
                    @else
                        <span class="block text-[10px] text-neutral-400 italic mt-0.5">Chưa hoàn thành</span>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Main Grid --}}
<div class="grid grid-cols-12 gap-6">
    {{-- Left column: Danh sách các tấm --}}
    <div class="col-span-12 md:col-span-8 space-y-6">
        <div class="bg-white border border-neutral-200 rounded-xl overflow-hidden shadow-sm">
            <div class="border-b border-neutral-100 bg-white py-4 px-6 flex items-center justify-between">
                <h6 class="font-bold text-base text-neutral-800 m-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:layers" class="text-xl text-neutral-600"></iconify-icon>
                    Danh sách các tấm ({{ $totalItemsCount }})
                </h6>
                <div class="flex items-center gap-3">
                    {{-- Per page --}}
                    <div class="flex items-center gap-1.5 text-xs text-neutral-500">
                        <span class="font-medium text-secondary-light">Hiển thị</span>
                        <select onchange="window.location.href = this.value" class="form-select rounded-lg border-neutral-300 py-1 px-2.5 text-xs focus:border-primary-500 focus:ring-primary-500 bg-white cursor-pointer font-medium text-neutral-700 w-auto">
                            @foreach([10, 25, 50, 100] as $size)
                                <option value="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}" {{ $items->perPage() == $size ? 'selected' : '' }}>
                                    {{ $size }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <a href="{{ route('manufactures.print-stamps', $manufacture) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold text-neutral-700 hover:text-neutral-800 hover:bg-neutral-50 border border-neutral-200 hover:border-neutral-300 rounded-lg transition-colors duration-200">
                        <iconify-icon icon="lucide:printer" class="text-lg"></iconify-icon> In phiếu dán tem
                    </a>
                </div>
            </div>
            
            <div class="p-1">
                <div class="table-responsive border-0 overflow-x-auto scroll-sm">
                    <table class="table bordered-table sm-table mb-0">
                        <thead class="bg-neutral-50">
                            <tr>
                                <th scope="col" style="width: 50px;" class="text-center">Stt</th>
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
                                <td class="text-center align-middle">{{ ($items->currentPage() - 1) * $items->perPage() + $index + 1 }}</td>
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
                                <td colspan="7" class="text-center py-8">
                                    <p class="text-neutral-500 mb-0">Không có tấm gỗ/sản phẩm nào trong lệnh sản xuất này.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($items->hasPages() || $totalItemsCount > 10)
            <div class="px-6 py-4 border-t border-neutral-100 bg-neutral-50/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                <span class="text-neutral-500 text-xs font-medium">
                    Hiển thị từ {{ $items->firstItem() ?? 0 }} đến {{ $items->lastItem() ?? 0 }} trong tổng số {{ $items->total() }} tấm
                </span>
                {{ $items->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Right column: Thông tin lệnh & Lịch sử duyệt --}}
    <div class="col-span-12 md:col-span-4 space-y-6">
        
        {{-- Flash message inside right col --}}
        @if(session('success') || session('error'))
        <div class="bg-white border border-neutral-200 rounded-xl overflow-hidden shadow-sm">
            <div class="p-4">
                @if(session('success'))
                <div class="bg-success-50 text-success-600 border border-success-200 rounded-lg p-3 m-0 text-sm">
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="bg-danger-50 text-danger-600 border border-danger-200 rounded-lg p-3 m-0 text-sm">
                    {{ session('error') }}
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Action Card based on current step & permissions --}}
        @can('approve manufacture')
        @if($manufacture->status !== 'completed')
        <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
            <h6 class="font-bold text-sm text-neutral-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                <iconify-icon icon="lucide:user-check" class="text-lg text-primary-600"></iconify-icon>
                Thao tác phê duyệt
            </h6>
            
            @if($manufacture->status === 'initialized')
                <form action="{{ route('manufactures.approve', [$manufacture, 'approve_tech']) }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 bg-neutral-900 hover:bg-black text-white shadow-sm border border-transparent transition-colors duration-200 cursor-pointer">
                        <iconify-icon icon="lucide:shield-check" class="text-lg"></iconify-icon>
                        Duyệt kỹ thuật (KT DUYỆT)
                    </button>
                </form>
            @elseif($manufacture->status === 'tech_approved')
                <form action="{{ route('manufactures.approve', [$manufacture, 'approve_manager']) }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 bg-neutral-900 hover:bg-black text-white shadow-sm border border-transparent transition-colors duration-200 cursor-pointer">
                        <iconify-icon icon="lucide:user-check" class="text-lg"></iconify-icon>
                        Quản đốc duyệt (QĐ DUYỆT)
                    </button>
                </form>
            @elseif($manufacture->status === 'manager_approved')
                <form action="{{ route('manufactures.approve', [$manufacture, 'receive_stamps']) }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 bg-neutral-900 hover:bg-black text-white shadow-sm border border-transparent transition-colors duration-200 cursor-pointer">
                        <iconify-icon icon="lucide:tag" class="text-lg"></iconify-icon>
                        Nhận tem sản xuất (NHẬN TEM)
                    </button>
                </form>
            @elseif($manufacture->status === 'stamps_received')
                <form action="{{ route('manufactures.approve', [$manufacture, 'start_production']) }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 bg-neutral-900 hover:bg-black text-white shadow-sm border border-transparent transition-colors duration-200 cursor-pointer">
                        <iconify-icon icon="lucide:cog" class="text-lg animate-spin" style="animation-duration: 3s;"></iconify-icon>
                        Bắt đầu sản xuất (SẢN XUẤT)
                    </button>
                </form>
            @elseif($manufacture->status === 'in_production')
                <form action="{{ route('manufactures.approve', [$manufacture, 'complete']) }}" method="POST" class="m-0" onsubmit="return confirm('Bạn có chắc muốn hoàn thành lệnh sản xuất này?')">
                    @csrf
                    <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 bg-neutral-900 hover:bg-black text-white shadow-sm border border-transparent transition-colors duration-200 cursor-pointer">
                        <iconify-icon icon="lucide:check-circle" class="text-lg"></iconify-icon>
                        Hoàn thành lệnh (HOÀN THÀNH)
                    </button>
                </form>
            @endif
        </div>
        @endif
        @endcan

        {{-- Phân phát tem nhanh --}}
        @if(in_array($manufacture->status, ['manager_approved', 'stamps_received', 'in_production']))
        <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
            <h6 class="font-bold text-sm text-neutral-800 uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-neutral-100 pb-3">
                <iconify-icon icon="solar:tag-bold-duotone" class="text-lg text-primary-600"></iconify-icon>
                Phân phát tem nhanh
            </h6>
            
            <div class="mb-4 text-xs text-neutral-500">
                @php
                    $totalStampsCount = $totalItemsCount;
                    $assignedStampsCount = $manufacture->stampDistributions->sum('quantity');
                    $unassignedStampsCount = max(0, $totalStampsCount - $assignedStampsCount);
                @endphp
                <div class="flex justify-between mb-1.5">
                    <span>Đã phân phát:</span>
                    <span class="font-semibold text-neutral-800">{{ $assignedStampsCount }} / {{ $totalStampsCount }} tem</span>
                </div>
                <div class="flex justify-between">
                    <span>Chưa phân phát:</span>
                    <span class="font-bold text-primary-600">{{ $unassignedStampsCount }} tem</span>
                </div>
            </div>

            <form action="{{ route('manufactures.assign-stamps', $manufacture) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="action" value="quick_distribute">
                
                <div class="form-group">
                    <label class="form-label text-xs font-semibold text-neutral-500 mb-1.5 block">Nhân viên nhận tem</label>
                    <select name="worker_id" class="form-select rounded-lg border-neutral-300 text-sm py-2 px-3 focus:border-primary-500 focus:ring-primary-500 w-full" required>
                        <option value="">-- Chọn nhân viên --</option>
                        @foreach($workers as $worker)
                            <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label text-xs font-semibold text-neutral-500 mb-1.5 block">Số lượng tem cần phát</label>
                    <input type="number" name="quantity" min="1" max="{{ $unassignedStampsCount }}" class="form-control rounded-lg border-neutral-300 text-sm focus:border-primary-500 focus:ring-primary-500 w-full" placeholder="Ví dụ: 100" required>
                </div>

                <button type="submit" {{ $unassignedStampsCount == 0 ? 'disabled' : '' }} class="w-full py-2 px-4 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 bg-neutral-900 hover:bg-black text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 cursor-pointer">
                    <iconify-icon icon="lucide:check-circle" class="text-sm"></iconify-icon>
                    Phân phát tem
                </button>
            </form>

            @if($assignedStampsCount > 0)
            <div class="mt-4 pt-4 border-t border-neutral-100">
                <form action="{{ route('manufactures.assign-stamps', $manufacture) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn thu hồi toàn bộ tem đã phân phát cho nhân viên?')">
                    @csrf
                    <input type="hidden" name="action" value="reset_all">
                    <button type="submit" class="w-full py-2 px-4 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 border border-danger-200 text-danger-600 hover:bg-danger-50 transition-colors duration-200 cursor-pointer">
                        <iconify-icon icon="lucide:rotate-ccw" class="text-sm"></iconify-icon>
                        Thu hồi toàn bộ tem đã phát
                    </button>
                </form>
            </div>
            @endif
        </div>
        @endif

        {{-- Thông tin lệnh --}}
        <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
            <h6 class="font-bold text-sm text-neutral-800 uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-neutral-100 pb-3">
                <iconify-icon icon="lucide:info" class="text-lg text-primary-600"></iconify-icon>
                Thông tin lệnh
            </h6>
            
            <div class="space-y-4 text-sm">
                <div class="flex justify-between items-center py-2 border-b border-neutral-50">
                    <span class="text-neutral-500 font-medium">Đơn hàng ghép:</span>
                    <span class="font-semibold text-neutral-800 flex flex-wrap justify-end gap-1 max-w-[200px]">
                        @foreach($manufacture->orders as $o)
                        <a href="{{ route('orders.show', $o) }}" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-neutral-100 hover:bg-neutral-200 text-neutral-700 transition-colors">
                            {{ $o->order_code }}
                        </a>
                        @endforeach
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-neutral-50">
                    <span class="text-neutral-500 font-medium">Tổng số tem:</span>
                    <span class="font-bold text-neutral-800 text-base">{{ $totalItemsCount }}</span>
                </div>
                <div class="py-2">
                    <span class="text-neutral-500 font-medium block mb-1">Ghi chú lệnh:</span>
                    <p class="text-neutral-700 bg-neutral-50 rounded-lg p-3 text-xs leading-relaxed italic m-0 border border-neutral-100">
                        {{ $manufacture->notes ?: 'Không có ghi chú' }}
                    </p>
                </div>
                
                @can('edit manufacture')
                <div class="pt-2">
                    <a href="{{ route('manufactures.edit', $manufacture) }}" class="w-full py-2 px-4 rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 border border-neutral-200 text-neutral-700 hover:bg-neutral-50 transition-colors">
                        <iconify-icon icon="lucide:edit" class="text-sm"></iconify-icon>
                        Chỉnh sửa lệnh ghép
                    </a>
                </div>
                @endcan
            </div>
        </div>

        {{-- Lịch sử duyệt --}}
        <div class="bg-white border border-neutral-200 rounded-xl p-5 shadow-sm">
            <h6 class="font-bold text-sm text-neutral-800 uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-neutral-100 pb-3">
                <iconify-icon icon="lucide:history" class="text-lg text-primary-600"></iconify-icon>
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
@endsection
