@extends('layout.layout')

@php
    $title = 'Quản lý Xe & Đổ Dầu';
    $subTitle = 'Quản lý danh sách xe, theo dõi số km, chu kỳ thay dầu 5.000km và chi phí nhiên liệu';
@endphp

@section('content')

@if(session('success'))
<div class="mb-4 p-4 rounded-xl border border-success-200 bg-success-50 text-success-700 flex items-center justify-between shadow-2xs">
    <div class="flex items-center gap-2">
        <iconify-icon icon="lucide:check-circle" class="text-xl text-success-600"></iconify-icon>
        <span class="font-medium text-sm">{{ session('success') }}</span>
    </div>
    <button type="button" onclick="this.parentElement.remove()" class="text-neutral-400 hover:text-neutral-600 text-lg">&times;</button>
</div>
@endif

@if(isset($errors) && $errors->any())
<div class="mb-4 p-4 rounded-xl border border-danger-200 bg-danger-50 text-danger-700">
    <div class="font-bold text-sm mb-1">Vui lòng kiểm tra lại thông tin:</div>
    <ul class="list-disc list-inside text-xs space-y-1">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- 4 Thẻ KPI thống kê đầu trang --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-2xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl shrink-0">
            <iconify-icon icon="solar:bus-bold-duotone"></iconify-icon>
        </div>
        <div class="min-w-0">
            <div class="text-xs text-neutral-500 font-medium">Tổng phương tiện</div>
            <div class="text-xl font-bold text-neutral-800 mt-1">
                {{ $totalVehicles }} <span class="text-xs font-normal text-neutral-500">xe ({{ $activeVehicles }} hoạt động)</span>
            </div>
        </div>
    </div>

    <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-2xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl {{ $needOilChangeCount > 0 ? 'bg-rose-50 text-rose-600 animate-pulse' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center text-2xl shrink-0">
            <iconify-icon icon="solar:wrench-bold-duotone"></iconify-icon>
        </div>
        <div class="min-w-0">
            <div class="text-xs text-neutral-500 font-medium">Cần / Sắp thay dầu máy</div>
            <div class="text-xl font-bold {{ $needOilChangeCount > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                {{ $needOilChangeCount }} <span class="text-xs font-normal text-neutral-500">xe cần chú ý (mốc 5.000km)</span>
            </div>
        </div>
    </div>

    <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-2xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl shrink-0">
            <iconify-icon icon="solar:gas-station-bold-duotone"></iconify-icon>
        </div>
        <div class="min-w-0">
            <div class="text-xs text-neutral-500 font-medium">Tổng nhiên liệu tiêu thụ</div>
            <div class="text-xl font-bold text-neutral-800 mt-1">
                {{ number_format($totalAllLiters, 1, ',', '.') }} <span class="text-xs font-normal text-neutral-500">lít</span>
            </div>
        </div>
    </div>

    <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-2xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xl shrink-0">
            <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
        </div>
        <div class="min-w-0">
            <div class="text-xs text-neutral-500 font-medium">Tổng tiền nhiên liệu</div>
            <div class="text-xl font-bold text-primary-600 mt-1">
                {{ number_format($totalAllCost, 0, ',', '.') }} <span class="text-xs font-normal text-neutral-500">₫</span>
            </div>
        </div>
    </div>
</div>

{{-- Thanh tác vụ: Tìm kiếm, lọc & nút Thêm xe --}}
<div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-2xs mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('vehicles.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
            <div class="relative min-w-[240px] flex-1 max-w-md">
                <iconify-icon icon="lucide:search" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-base"></iconify-icon>
                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm biển số, tên xe, lái xe..."
                       class="w-full pl-10 pr-3 py-2 text-sm border border-neutral-300 rounded-xl focus:outline-hidden focus:ring-2 focus:ring-primary-500 bg-neutral-50/50">
            </div>

            <select name="status" class="py-2 px-3 text-sm border border-neutral-300 rounded-xl focus:outline-hidden focus:ring-2 focus:ring-primary-500 bg-white">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Đang bảo dưỡng</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tạm ngưng</option>
            </select>

            <select name="fuel_type" class="py-2 px-3 text-sm border border-neutral-300 rounded-xl focus:outline-hidden focus:ring-2 focus:ring-primary-500 bg-white">
                <option value="">-- Loại nhiên liệu --</option>
                <option value="diesel" {{ request('fuel_type') == 'diesel' ? 'selected' : '' }}>Dầu Diesel</option>
                <option value="ron95" {{ request('fuel_type') == 'ron95' ? 'selected' : '' }}>Xăng RON 95</option>
                <option value="ron92" {{ request('fuel_type') == 'ron92' ? 'selected' : '' }}>Xăng RON 92</option>
                <option value="e5" {{ request('fuel_type') == 'e5' ? 'selected' : '' }}>Xăng E5</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-neutral-800 hover:bg-neutral-900 text-white rounded-xl text-sm font-medium transition-colors inline-flex items-center gap-2 shadow-2xs">
                <iconify-icon icon="lucide:filter" class="text-base"></iconify-icon>
                Lọc
            </button>

            @if(request()->anyFilled(['keyword', 'status', 'fuel_type']))
            <a href="{{ route('vehicles.index') }}" class="px-3 py-2 text-neutral-500 hover:text-neutral-700 text-sm font-medium inline-flex items-center gap-1">
                <iconify-icon icon="lucide:rotate-ccw"></iconify-icon>
                Đặt lại
            </a>
            @endif
        </form>

        <div>
            <button type="button" onclick="openAddVehicleModal()" class="w-full sm:w-auto px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl text-sm transition-all shadow-sm hover:shadow flex items-center justify-center gap-2">
                <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon>
                <span>Thêm phương tiện mới</span>
            </button>
        </div>
    </div>
</div>

{{-- Danh sách xe --}}
@if($vehicles->isEmpty())
<div class="bg-white border border-neutral-200 rounded-2xl p-12 text-center shadow-2xs">
    <div class="w-16 h-16 rounded-full bg-neutral-100 text-neutral-400 mx-auto flex items-center justify-center text-3xl mb-3">
        <iconify-icon icon="solar:bus-outline"></iconify-icon>
    </div>
    <h3 class="text-base font-bold text-neutral-800">Chưa có phương tiện nào</h3>
    <p class="text-sm text-neutral-500 max-w-md mx-auto mt-1 mb-5">Nhập thông tin xe (biển số, tên xe, lái xe) để bắt đầu theo dõi nhật ký đổ dầu và chu kỳ thay nhớt 5.000km.</p>
    <button type="button" onclick="openAddVehicleModal()" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl text-sm inline-flex items-center gap-2">
        <iconify-icon icon="lucide:plus"></iconify-icon> Thêm xe đầu tiên
    </button>
</div>
@else
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($vehicles as $vehicle)
    @php
        $oilAlert = false;
        $oilAlertClass = 'bg-emerald-500';
        $oilTextClass = 'text-emerald-700';
        $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';

        if ($vehicle->current_km_since_oil >= $vehicle->oil_interval) {
            $oilAlert = true;
            $oilAlertClass = 'bg-rose-500';
            $oilTextClass = 'text-rose-700 font-bold';
            $badgeClass = 'bg-rose-50 text-rose-700 border-rose-200 animate-pulse';
        } elseif ($vehicle->current_km_since_oil >= ($vehicle->oil_interval - 500)) {
            $oilAlert = true;
            $oilAlertClass = 'bg-amber-500';
            $oilTextClass = 'text-amber-700 font-bold';
            $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
        }
    @endphp
    <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-2xs hover:shadow-md transition-all flex flex-col justify-between relative group">
        <div>
            {{-- Header card: Biển số & Trạng thái --}}
            <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-50 border border-amber-300 rounded-lg text-amber-900 font-black font-mono text-base tracking-wider shadow-2xs">
                        <iconify-icon icon="solar:shield-check-bold" class="text-amber-600 text-sm"></iconify-icon>
                        {{ $vehicle->plate_number }}
                    </div>
                    <h3 class="font-bold text-neutral-800 text-base mt-2 flex items-center gap-2">
                        {{ $vehicle->name }}
                    </h3>
                </div>

                <div class="flex flex-col items-end gap-2">
                    @if($vehicle->status === 'active')
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span> Hoạt động
                        </span>
                    @elseif($vehicle->status === 'maintenance')
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Bảo dưỡng</span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-neutral-100 text-neutral-600 border border-neutral-200">Tạm ngưng</span>
                    @endif

                    <span class="text-[11px] text-neutral-400 font-medium uppercase tracking-wider">
                        {{ match($vehicle->fuel_type) { 'diesel' => 'Dầu Diesel', 'ron95' => 'Xăng RON 95', 'ron92' => 'Xăng RON 92', 'e5' => 'Xăng E5', default => $vehicle->fuel_type } }}
                    </span>
                </div>
            </div>

            {{-- Thông tin Lái xe & Km hiện tại --}}
            <div class="bg-neutral-50/80 rounded-xl p-3 mb-4 space-y-2 text-xs border border-neutral-100">
                <div class="flex items-center justify-between">
                    <span class="text-neutral-500 flex items-center gap-2">
                        <iconify-icon icon="solar:user-circle-bold" class="text-neutral-400 text-sm"></iconify-icon>
                        Lái xe / Phụ trách:
                    </span>
                    <span class="font-semibold text-neutral-800">
                        {{ $vehicle->driver_name ?: 'Chưa phân công' }}
                        @if($vehicle->driver_phone)
                            <a href="tel:{{ $vehicle->driver_phone }}" class="text-primary-600 hover:underline font-normal ml-1">({{ $vehicle->driver_phone }})</a>
                        @endif
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-neutral-500 flex items-center gap-2">
                        <iconify-icon icon="solar:speedometer-low-bold" class="text-neutral-400 text-sm"></iconify-icon>
                        Số Km hiện tại:
                    </span>
                    <span class="font-bold text-neutral-900 font-mono text-sm">
                        {{ number_format($vehicle->current_km, 0, ',', '.') }} km
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-neutral-500 flex items-center gap-2">
                        <iconify-icon icon="solar:leaf-bold" class="text-neutral-400 text-sm"></iconify-icon>
                        Hiệu suất TB:
                    </span>
                    <span class="font-bold {{ $vehicle->avg_km_per_liter > 0 ? 'text-primary-600' : 'text-neutral-500' }}">
                        {{ $vehicle->avg_km_per_liter > 0 ? $vehicle->avg_km_per_liter . ' km/lít' : '—' }}
                    </span>
                </div>
            </div>

            {{-- Thanh theo dõi chu kỳ 5.000km thay dầu --}}
            <div class="bg-white border rounded-xl p-3 mb-4 {{ $badgeClass }}">
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="font-bold flex items-center gap-1 {{ $oilTextClass }}">
                        <iconify-icon icon="solar:drop-bold" class="text-sm"></iconify-icon>
                        Mốc 5.000km thay dầu
                    </span>
                    <span class="font-mono font-bold {{ $oilTextClass }}">
                        {{ number_format($vehicle->current_km_since_oil, 0, ',', '.') }} / {{ number_format($vehicle->oil_interval, 0, ',', '.') }} km
                    </span>
                </div>

                {{-- Progress bar --}}
                <div class="w-full bg-neutral-200/80 rounded-full h-2 overflow-hidden mb-2">
                    <div class="{{ $oilAlertClass }} h-2 rounded-full transition-all duration-500" style="width: {{ $vehicle->oil_percent }}%"></div>
                </div>

                <div class="flex items-center justify-between text-[11px]">
                    @if($vehicle->current_km_since_oil >= $vehicle->oil_interval)
                        <span class="text-rose-600 font-bold flex items-center gap-1">
                            <iconify-icon icon="lucide:alert-triangle"></iconify-icon>
                            ĐÃ QUÁ HẠN {{ number_format($vehicle->current_km_since_oil - $vehicle->oil_interval, 0, ',', '.') }} KM! CẦN THAY DẦU NGAY
                        </span>
                    @elseif($vehicle->current_km_since_oil >= ($vehicle->oil_interval - 500))
                        <span class="text-amber-700 font-semibold">
                            Sắp đến hạn! Còn {{ number_format($vehicle->oil_remaining, 0, ',', '.') }} km
                        </span>
                    @else
                        <span class="text-neutral-500">
                            Còn lại {{ number_format($vehicle->oil_remaining, 0, ',', '.') }} km
                        </span>
                    @endif
                    <span class="text-neutral-400 font-medium">{{ $vehicle->oil_percent }}%</span>
                </div>
            </div>

            {{-- Chi phí & Nhiên liệu đã đổ --}}
            <div class="grid grid-cols-2 gap-2 text-xs mb-4">
                <div class="bg-neutral-50 p-3 rounded-lg border border-neutral-100">
                    <div class="text-[11px] text-neutral-400">Đã đổ</div>
                    <div class="font-bold text-neutral-800 mt-1">{{ number_format($vehicle->total_liters, 1, ',', '.') }} lít</div>
                </div>
                <div class="bg-neutral-50 p-3 rounded-lg border border-neutral-100">
                    <div class="text-[11px] text-neutral-400">Tổng tiền</div>
                    <div class="font-bold text-primary-600 mt-1">{{ number_format($vehicle->total_fuel_cost, 0, ',', '.') }} ₫</div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="pt-3 border-t border-neutral-100 flex items-center justify-between gap-2">
            <a href="{{ route('vehicles.show', $vehicle->id) }}" class="flex-1 py-2 px-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold text-center transition-colors shadow-2xs flex items-center justify-center gap-2">
                <iconify-icon icon="solar:notes-bold" class="text-sm"></iconify-icon>
                <span>Nhật ký đổ dầu & bảo dưỡng</span>
            </a>

            <button type="button" onclick="openEditVehicleModal({{ json_encode($vehicle) }})" title="Chỉnh sửa xe"
                    class="p-2 text-neutral-500 hover:text-primary-600 hover:bg-neutral-100 rounded-lg transition-colors border border-neutral-200">
                <iconify-icon icon="lucide:edit-3" class="text-base"></iconify-icon>
            </button>

            <form action="{{ route('vehicles.destroy', $vehicle->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phương tiện {{ $vehicle->plate_number }}? Toàn bộ nhật ký đổ dầu của xe này sẽ bị xóa!');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" title="Xóa xe" class="p-2 text-neutral-400 hover:text-danger-600 hover:bg-danger-50 rounded-lg transition-colors border border-neutral-200">
                    <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ==================== MODAL THÊM XE MỚI ==================== --}}
<div id="addVehicleModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl overflow-hidden animate-scale-up">
        <div class="p-5 border-b border-neutral-100 flex items-center justify-between bg-neutral-50/50">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-lg font-bold">
                    <iconify-icon icon="solar:bus-bold-duotone"></iconify-icon>
                </div>
                <div>
                    <h3 class="font-bold text-neutral-800 text-base leading-tight">Thêm phương tiện mới</h3>
                    <p class="text-xs text-neutral-400">Nhập biển số và thông số kỹ thuật theo dõi</p>
                </div>
            </div>
            <button type="button" onclick="closeAddVehicleModal()" class="text-neutral-400 hover:text-neutral-600 text-2xl leading-none">&times;</button>
        </div>

        <form action="{{ route('vehicles.store') }}" method="POST" class="p-5 space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Biển số xe <span class="text-danger-500">*</span></label>
                    <input type="text" name="plate_number" required placeholder="VD: 29H-123.45"
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden uppercase font-mono font-bold">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Tên xe / Dòng xe <span class="text-danger-500">*</span></label>
                    <input type="text" name="name" required placeholder="VD: Xe tải Hino 3.5T"
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Lái xe / Phụ trách</label>
                    <input type="text" name="driver_name" placeholder="Họ và tên lái xe"
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Số điện thoại lái xe</label>
                    <input type="text" name="driver_phone" placeholder="VD: 0988xxxxxx"
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Loại nhiên liệu</label>
                    <select name="fuel_type" class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden bg-white">
                        <option value="diesel">Dầu Diesel</option>
                        <option value="ron95">Xăng RON 95</option>
                        <option value="ron92">Xăng RON 92</option>
                        <option value="e5">Xăng E5</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Số Km ban đầu</label>
                    <input type="number" step="0.1" name="initial_km" value="0"
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Chu kỳ thay dầu (Km)</label>
                    <input type="number" step="100" name="oil_change_interval_km" value="5000"
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden font-mono font-bold text-primary-600">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1">Trạng thái</label>
                <select name="status" class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden bg-white">
                    <option value="active">Đang hoạt động</option>
                    <option value="maintenance">Đang bảo dưỡng</option>
                    <option value="inactive">Tạm ngưng</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1">Ghi chú phương tiện</label>
                <textarea name="notes" rows="2" placeholder="Ghi chú thêm về xe..."
                          class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden"></textarea>
            </div>

            <div class="pt-3 border-t border-neutral-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeAddVehicleModal()" class="px-4 py-2 border border-neutral-300 rounded-xl text-xs font-medium text-neutral-600 hover:bg-neutral-50">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold shadow-2xs">Lưu phương tiện</button>
            </div>
        </form>
    </div>
</div>

{{-- ==================== MODAL SỬA THÔNG TIN XE ==================== --}}
<div id="editVehicleModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl overflow-hidden">
        <div class="p-5 border-b border-neutral-100 flex items-center justify-between bg-neutral-50/50">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold">
                    <iconify-icon icon="lucide:edit-3"></iconify-icon>
                </div>
                <div>
                    <h3 class="font-bold text-neutral-800 text-base leading-tight">Chỉnh sửa phương tiện</h3>
                    <p class="text-xs text-neutral-400" id="editPlateHeader"></p>
                </div>
            </div>
            <button type="button" onclick="closeEditVehicleModal()" class="text-neutral-400 hover:text-neutral-600 text-2xl leading-none">&times;</button>
        </div>

        <form id="editVehicleForm" method="POST" class="p-5 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Biển số xe <span class="text-danger-500">*</span></label>
                    <input type="text" id="edit_plate_number" name="plate_number" required
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden uppercase font-mono font-bold">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Tên xe / Dòng xe <span class="text-danger-500">*</span></label>
                    <input type="text" id="edit_name" name="name" required
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Lái xe / Phụ trách</label>
                    <input type="text" id="edit_driver_name" name="driver_name"
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Số điện thoại</label>
                    <input type="text" id="edit_driver_phone" name="driver_phone"
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Loại nhiên liệu</label>
                    <select id="edit_fuel_type" name="fuel_type" class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden bg-white">
                        <option value="diesel">Dầu Diesel</option>
                        <option value="ron95">Xăng RON 95</option>
                        <option value="ron92">Xăng RON 92</option>
                        <option value="e5">Xăng E5</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Số Km ban đầu</label>
                    <input type="number" step="0.1" id="edit_initial_km" name="initial_km"
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Chu kỳ thay dầu (Km)</label>
                    <input type="number" step="100" id="edit_oil_change_interval_km" name="oil_change_interval_km"
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden font-mono font-bold text-primary-600">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1">Trạng thái</label>
                <select id="edit_status" name="status" class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden bg-white">
                    <option value="active">Đang hoạt động</option>
                    <option value="maintenance">Đang bảo dưỡng</option>
                    <option value="inactive">Tạm ngưng</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1">Ghi chú</label>
                <textarea id="edit_notes" name="notes" rows="2"
                          class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden"></textarea>
            </div>

            <div class="pt-3 border-t border-neutral-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditVehicleModal()" class="px-4 py-2 border border-neutral-300 rounded-xl text-xs font-medium text-neutral-600 hover:bg-neutral-50">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold shadow-2xs">Cập nhật xe</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddVehicleModal() {
        document.getElementById('addVehicleModal').classList.remove('hidden');
    }
    function closeAddVehicleModal() {
        document.getElementById('addVehicleModal').classList.add('hidden');
    }

    function openEditVehicleModal(vehicle) {
        document.getElementById('editVehicleForm').action = '/vehicles/' + vehicle.id;
        document.getElementById('editPlateHeader').innerText = vehicle.plate_number + ' - ' + vehicle.name;
        document.getElementById('edit_plate_number').value = vehicle.plate_number;
        document.getElementById('edit_name').value = vehicle.name;
        document.getElementById('edit_driver_name').value = vehicle.driver_name || '';
        document.getElementById('edit_driver_phone').value = vehicle.driver_phone || '';
        document.getElementById('edit_fuel_type').value = vehicle.fuel_type || 'diesel';
        document.getElementById('edit_initial_km').value = vehicle.initial_km || 0;
        document.getElementById('edit_oil_change_interval_km').value = vehicle.oil_change_interval_km || 5000;
        document.getElementById('edit_status').value = vehicle.status || 'active';
        document.getElementById('edit_notes').value = vehicle.notes || '';

        document.getElementById('editVehicleModal').classList.remove('hidden');
    }
    function closeEditVehicleModal() {
        document.getElementById('editVehicleModal').classList.add('hidden');
    }
</script>

@endsection
