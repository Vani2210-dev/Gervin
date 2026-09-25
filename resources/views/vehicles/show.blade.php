@extends('layout.layout')

@php
    $title = 'Nhật ký Xe ' . $vehicle->plate_number;
    $subTitle = 'Bảng theo dõi các lần đổ dầu, mức tiêu hao nhiên liệu và chu kỳ 5.000km thay dầu máy';
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
    <div class="font-bold text-sm mb-1">Vui lòng kiểm tra lại:</div>
    <ul class="list-disc list-inside text-xs space-y-0.5">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Header Xe & Thống kê --}}
<div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-2xs mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-neutral-100">
        <div class="flex items-center gap-4">
            <a href="{{ route('vehicles.index') }}" class="w-10 h-10 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-600 flex items-center justify-center transition-colors shadow-2xs" title="Quay lại danh sách xe">
                <iconify-icon icon="lucide:arrow-left" class="text-lg"></iconify-icon>
            </a>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="px-3 py-1 bg-amber-100 border border-amber-300 rounded-lg text-amber-950 font-black font-mono text-lg tracking-wider shadow-2xs">
                        {{ $vehicle->plate_number }}
                    </span>
                    <h2 class="text-lg sm:text-xl font-bold text-neutral-800">{{ $vehicle->name }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $vehicle->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-neutral-100 text-neutral-600' }}">
                        {{ $vehicle->status === 'active' ? 'Đang hoạt động' : ($vehicle->status === 'maintenance' ? 'Đang bảo dưỡng' : 'Tạm ngưng') }}
                    </span>
                </div>
                <div class="flex items-center gap-4 text-xs text-neutral-500 mt-1.5 flex-wrap">
                    <span>Lái xe: <strong class="text-neutral-700">{{ $vehicle->driver_name ?: 'Chưa phân công' }}</strong> @if($vehicle->driver_phone)({{ $vehicle->driver_phone }})@endif</span>
                    <span>•</span>
                    <span>Nhiên liệu: <strong class="text-neutral-700 uppercase">{{ match($vehicle->fuel_type) { 'diesel' => 'Dầu Diesel', 'ron95' => 'Xăng RON 95', 'ron92' => 'Xăng RON 92', 'e5' => 'Xăng E5', default => $vehicle->fuel_type } }}</strong></span>
                    <span>•</span>
                    <span>Km ban đầu: <strong class="text-neutral-700 font-mono">{{ number_format($vehicle->initial_km, 0, ',', '.') }} km</strong></span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <button type="button" onclick="openAddFuelModal()" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold transition-all shadow-sm flex items-center gap-1.5">
                <iconify-icon icon="solar:gas-station-bold" class="text-base"></iconify-icon>
                <span>+ Đổ Xăng / Dầu</span>
            </button>

            <button type="button" onclick="openOilChangeModal()" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold transition-all shadow-sm flex items-center gap-1.5">
                <iconify-icon icon="solar:wrench-bold" class="text-base"></iconify-icon>
                <span>+ Ghi nhận Thay Dầu Máy</span>
            </button>
        </div>
    </div>

    {{-- Cards chỉ số nhanh --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 pt-4">
        {{-- Km hiện tại --}}
        <div class="bg-neutral-50 rounded-xl p-3 border border-neutral-100">
            <div class="text-[11px] text-neutral-500 font-medium">Số Km hiện tại</div>
            <div class="text-lg font-bold text-neutral-900 font-mono mt-0.5">
                {{ number_format($vehicle->current_km, 0, ',', '.') }} <span class="text-xs font-normal text-neutral-500">km</span>
            </div>
        </div>

        {{-- Cảnh báo thay dầu 5.000km --}}
        @php
            $oilBoxBorder = 'border-emerald-200 bg-emerald-50/50';
            $oilTextColor = 'text-emerald-700';
            if ($kmSinceOil >= $oilInterval) {
                $oilBoxBorder = 'border-rose-300 bg-rose-50/80 animate-pulse';
                $oilTextColor = 'text-rose-700';
            } elseif ($kmSinceOil >= ($oilInterval - 500)) {
                $oilBoxBorder = 'border-amber-300 bg-amber-50/80';
                $oilTextColor = 'text-amber-700';
            }
        @endphp
        <div class="rounded-xl p-3 border {{ $oilBoxBorder }}">
            <div class="text-[11px] font-semibold {{ $oilTextColor }} flex items-center justify-between">
                <span>Mốc 5.000km thay dầu</span>
                <span>{{ $oilPercent }}%</span>
            </div>
            <div class="text-lg font-bold font-mono mt-0.5 {{ $oilTextColor }}">
                {{ number_format($kmSinceOil, 0, ',', '.') }} <span class="text-xs font-normal">/ {{ number_format($oilInterval, 0, ',', '.') }} km</span>
            </div>
            <div class="text-[10px] mt-0.5 {{ $oilTextColor }}">
                @if($kmSinceOil >= $oilInterval)
                    Đã quá hạn {{ number_format($kmSinceOil - $oilInterval, 0, ',', '.') }} km! Cần thay ngay!
                @else
                    Còn lại {{ number_format($kmUntilNextOil, 0, ',', '.') }} km nữa
                @endif
            </div>
        </div>

        {{-- Lần thay dầu gần nhất --}}
        <div class="bg-neutral-50 rounded-xl p-3 border border-neutral-100">
            <div class="text-[11px] text-neutral-500 font-medium">Lần thay dầu gần nhất</div>
            <div class="text-base font-bold text-neutral-800 font-mono mt-0.5">
                {{ $lastOilKm ? number_format($lastOilKm, 0, ',', '.') . ' km' : 'Chưa có' }}
            </div>
            <div class="text-[10px] text-neutral-400 mt-0.5">
                {{ $lastOilDate ? \Carbon\Carbon::parse($lastOilDate)->format('d/m/Y') : 'Từ đầu' }}
            </div>
        </div>

        {{-- Hiệu suất trung bình km/lít --}}
        <div class="bg-neutral-50 rounded-xl p-3 border border-neutral-100">
            <div class="text-[11px] text-neutral-500 font-medium">1 lít đi được TB</div>
            <div class="text-lg font-bold text-primary-600 mt-0.5">
                {{ $avgKmPerLiter > 0 ? $avgKmPerLiter : '—' }} <span class="text-xs font-normal text-neutral-500">cây / lít</span>
            </div>
        </div>

        {{-- Tổng tiền đã đổ --}}
        <div class="bg-neutral-50 rounded-xl p-3 border border-neutral-100 col-span-2 sm:col-span-1">
            <div class="text-[11px] text-neutral-500 font-medium">Tổng tiền ({{ number_format($totalLiters, 1, ',', '.') }} lít)</div>
            <div class="text-lg font-bold text-primary-600 mt-0.5 truncate">
                {{ number_format($totalExpense, 0, ',', '.') }} <span class="text-xs font-normal text-neutral-500">₫</span>
            </div>
        </div>
    </div>
</div>

{{-- Bộ lọc & Bảng nhật ký --}}
<div class="bg-white border border-neutral-200 rounded-2xl shadow-2xs overflow-hidden mb-8">
    <div class="p-4 border-b border-neutral-200 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-neutral-50/50">
        <form method="GET" action="{{ route('vehicles.show', $vehicle->id) }}" class="flex flex-wrap items-center gap-2.5">
            <div class="flex items-center gap-1.5 text-xs text-neutral-600">
                <span>Từ ngày:</span>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                       class="px-2.5 py-1.5 text-xs border border-neutral-300 rounded-lg bg-white">
            </div>

            <div class="flex items-center gap-1.5 text-xs text-neutral-600">
                <span>Đến ngày:</span>
                <input type="date" name="to_date" value="{{ request('to_date') }}"
                       class="px-2.5 py-1.5 text-xs border border-neutral-300 rounded-lg bg-white">
            </div>

            <select name="log_type" class="px-2.5 py-1.5 text-xs border border-neutral-300 rounded-lg bg-white">
                <option value="">-- Tất cả loại --</option>
                <option value="fuel" {{ request('log_type') == 'fuel' ? 'selected' : '' }}>Chỉ lần đổ dầu</option>
                <option value="oil_change" {{ request('log_type') == 'oil_change' ? 'selected' : '' }}>Chỉ lần thay dầu</option>
            </select>

            <button type="submit" class="px-3.5 py-1.5 bg-neutral-800 hover:bg-neutral-900 text-white rounded-lg text-xs font-medium inline-flex items-center gap-1">
                <iconify-icon icon="lucide:filter"></iconify-icon> Lọc
            </button>

            @if(request()->anyFilled(['from_date', 'to_date', 'log_type']))
            <a href="{{ route('vehicles.show', $vehicle->id) }}" class="px-2.5 py-1.5 text-xs text-neutral-500 hover:text-neutral-700">
                Đặt lại
            </a>
            @endif
        </form>

        <div class="text-xs text-neutral-500">
            Tổng số: <strong class="text-neutral-800 font-mono">{{ $logs->count() }}</strong> lần ghi nhận
        </div>
    </div>

    {{-- BẢNG THEO ĐÚNG MẪU EXCEL CỦA NGƯỜI DÙNG --}}
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left border-collapse" id="fuelTable">
            <thead>
                {{-- Header theo phong cách bảng Excel mẫu với màu vàng và hồng --}}
                <tr class="border-b border-neutral-300 text-neutral-900 font-bold text-center select-none" style="background-color: #fef08a;">
                    <th class="py-3 px-3 border border-neutral-300 whitespace-nowrap min-w-[110px]">Ngày tháng năm</th>
                    <th class="py-3 px-3 border border-neutral-300 whitespace-nowrap min-w-[120px]">Số Km</th>
                    <th class="py-3 px-3 border border-neutral-300 whitespace-nowrap min-w-[120px]">Số lượng ( lít)</th>
                    <th class="py-3 px-3 border border-neutral-300 whitespace-nowrap min-w-[110px]">Đơn giá</th>
                    <th class="py-3 px-3 border border-neutral-300 whitespace-nowrap min-w-[120px]">Số tiền</th>
                    <th class="py-3 px-3 border border-neutral-300 whitespace-nowrap min-w-[140px]">Tổng Số Km đổ dầu đi đc km</th>
                    <th class="py-3 px-3 border border-neutral-300 whitespace-nowrap min-w-[120px]">1 lít đi đc số cây</th>
                    <th class="py-3 px-3 border border-neutral-300 whitespace-nowrap min-w-[140px] text-rose-700" style="background-color: #fbcfe8;">
                        <div class="leading-tight">Ghi chú<br><span class="font-black text-rose-800">5.000km thay dầu</span></div>
                    </th>
                    <th class="py-3 px-3 border border-neutral-300 min-w-[140px]">Ghi chú</th>
                    <th class="py-3 px-2 border border-neutral-300 whitespace-nowrap w-20 bg-neutral-100 text-neutral-600">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 bg-white">
                @forelse($logs as $idx => $log)
                @php
                    $isOilChange = ($log->type === 'oil_change');
                    $isMaintenance = ($log->type === 'maintenance');

                    // Màu cảnh báo mốc 5.000km
                    $oilKmClass = 'font-bold text-neutral-900';
                    if ($log->km_since_oil_change !== null) {
                        if ($log->km_since_oil_change >= 5000) {
                            $oilKmClass = 'font-black text-rose-600 bg-rose-50 px-2 py-0.5 rounded';
                        } elseif ($log->km_since_oil_change >= 4500) {
                            $oilKmClass = 'font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded';
                        }
                    }
                @endphp
                <tr class="hover:bg-neutral-50/80 transition-colors {{ $isOilChange ? 'bg-purple-50/40 font-semibold' : '' }}">
                    {{-- 1. Ngày tháng năm --}}
                    <td class="py-2.5 px-3 border border-neutral-200 text-center font-mono whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($log->date)->format('d/m/Y') }}
                    </td>

                    {{-- 2. Số Km --}}
                    <td class="py-2.5 px-3 border border-neutral-200 text-center font-mono whitespace-nowrap">
                        @if($isOilChange)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-700 border border-purple-300">
                                <iconify-icon icon="solar:wrench-bold" class="text-xs"></iconify-icon>
                                Thay dầu máy
                                @if($log->odometer_km)
                                    ({{ number_format($log->odometer_km, 0, ',', '.') }})
                                @endif
                            </span>
                        @elseif($isMaintenance)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-700 border border-sky-300">
                                <iconify-icon icon="solar:settings-bold" class="text-xs"></iconify-icon>
                                Bảo dưỡng
                            </span>
                        @else
                            <span class="font-bold text-neutral-800">
                                {{ $log->odometer_km !== null ? number_format($log->odometer_km, 0, ',', '.') : '—' }}
                            </span>
                        @endif
                    </td>

                    {{-- 3. Số lượng (lít) --}}
                    <td class="py-2.5 px-3 border border-neutral-200 text-right font-mono whitespace-nowrap">
                        @if($log->liters !== null && $log->liters > 0)
                            {{ number_format($log->liters, 3, ',', '.') }}
                        @else
                            <span class="text-neutral-300">-</span>
                        @endif
                    </td>

                    {{-- 4. Đơn giá --}}
                    <td class="py-2.5 px-3 border border-neutral-200 text-right font-mono whitespace-nowrap">
                        @if($log->unit_price !== null && $log->unit_price > 0)
                            {{ number_format($log->unit_price, 0, ',', '.') }}
                        @else
                            <span class="text-neutral-300">-</span>
                        @endif
                    </td>

                    {{-- 5. Số tiền = Đơn giá * số lít --}}
                    <td class="py-2.5 px-3 border border-neutral-200 text-right font-mono whitespace-nowrap font-bold {{ $log->total_price > 0 ? 'text-neutral-900' : 'text-neutral-400' }}">
                        @if($log->total_price !== null && $log->total_price > 0)
                            {{ number_format($log->total_price, 0, ',', '.') }}
                        @else
                            <span class="text-neutral-300">-</span>
                        @endif
                    </td>

                    {{-- 6. Tổng Số Km đổ dầu đi đc km --}}
                    <td class="py-2.5 px-3 border border-neutral-200 text-right font-mono whitespace-nowrap font-semibold">
                        @if($log->trip_km !== null && $log->trip_km > 0)
                            <span class="text-neutral-800">{{ number_format($log->trip_km, 0, ',', '.') }}</span>
                        @elseif($log->type === 'fuel' && $loop->last)
                            <span class="text-[11px] text-neutral-400 italic">Đang chạy...</span>
                        @else
                            <span class="text-neutral-300">-</span>
                        @endif
                    </td>

                    {{-- 7. 1 lít đi đc số cây = trip_km / liters --}}
                    <td class="py-2.5 px-3 border border-neutral-200 text-right font-mono whitespace-nowrap font-bold">
                        @if($log->km_per_liter !== null && $log->km_per_liter > 0)
                            <span class="text-primary-700 bg-primary-50/60 px-2 py-0.5 rounded">{{ number_format($log->km_per_liter, 2, ',', '.') }}</span>
                        @elseif($log->type === 'fuel' && $loop->last)
                            <span class="text-[11px] text-neutral-400 italic">—</span>
                        @else
                            <span class="text-neutral-300">-</span>
                        @endif
                    </td>

                    {{-- 8. Ghi chú 5.000km thay dầu --}}
                    <td class="py-2.5 px-3 border border-neutral-200 text-right font-mono whitespace-nowrap">
                        @if($isOilChange)
                            <span class="text-purple-600 font-bold text-[11px]">Đã thay dầu (Mốc 0)</span>
                        @elseif($log->km_since_oil_change !== null)
                            <span class="{{ $oilKmClass }}">{{ number_format($log->km_since_oil_change, 0, ',', '.') }}</span>
                        @else
                            <span class="text-neutral-300">-</span>
                        @endif
                    </td>

                    {{-- 9. Ghi chú --}}
                    <td class="py-2.5 px-3 border border-neutral-200 text-neutral-600">
                        {{ $log->notes ?: '' }}
                    </td>

                    {{-- 10. Thao tác --}}
                    <td class="py-2.5 px-2 border border-neutral-200 text-center whitespace-nowrap">
                        <div class="flex items-center justify-center gap-1">
                            <button type="button" onclick="openEditFuelModal({{ json_encode($log) }})" title="Chỉnh sửa dòng"
                                    class="p-1 text-neutral-400 hover:text-primary-600 hover:bg-neutral-100 rounded">
                                <iconify-icon icon="lucide:edit-2" class="text-sm"></iconify-icon>
                            </button>

                            <form action="{{ route('vehicles.fuel-logs.destroy', [$vehicle->id, $log->id]) }}" method="POST" onsubmit="return confirm('Xác nhận xóa bản ghi ngày {{ \Carbon\Carbon::parse($log->date)->format('d/m/Y') }}?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Xóa dòng" class="p-1 text-neutral-400 hover:text-danger-600 hover:bg-danger-50 rounded">
                                    <iconify-icon icon="lucide:trash" class="text-sm"></iconify-icon>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="py-8 text-center text-neutral-400 italic">
                        Chưa có lượt đổ xăng dầu hoặc thay dầu nào. Bấm nút <strong>"+ Đổ Xăng / Dầu"</strong> ở trên để bắt đầu nhập dữ liệu!
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($logs->isNotEmpty())
            @php
                $fuelOnly = $logs->where('type', 'fuel');
                $sumLiters = $fuelOnly->sum('liters');
                $sumMoney = $fuelOnly->sum('total_price');
                $sumTrips = $fuelOnly->whereNotNull('trip_km')->sum('trip_km');
                $avgEfficiency = ($sumLiters > 0 && $sumTrips > 0) ? round($sumTrips / $sumLiters, 2) : 0;
            @endphp
            <tfoot>
                <tr class="bg-neutral-100 font-bold text-neutral-800 border-t-2 border-neutral-400">
                    <td colspan="2" class="py-3 px-3 text-center uppercase tracking-wider text-xs border border-neutral-300">
                        Tổng cộng
                    </td>
                    <td class="py-3 px-3 text-right font-mono border border-neutral-300 text-primary-700">
                        {{ number_format($sumLiters, 3, ',', '.') }}
                    </td>
                    <td class="py-3 px-3 text-center border border-neutral-300 text-neutral-400">—</td>
                    <td class="py-3 px-3 text-right font-mono border border-neutral-300 text-primary-700 text-sm">
                        {{ number_format($sumMoney, 0, ',', '.') }} ₫
                    </td>
                    <td class="py-3 px-3 text-right font-mono border border-neutral-300 text-neutral-900">
                        {{ number_format($sumTrips, 0, ',', '.') }} km
                    </td>
                    <td class="py-3 px-3 text-right font-mono border border-neutral-300 text-primary-700 font-bold">
                        {{ $avgEfficiency > 0 ? $avgEfficiency : '—' }}
                    </td>
                    <td colspan="3" class="py-3 px-3 border border-neutral-300 text-xs text-neutral-500 font-normal">
                        * Mốc 5.000km tự động theo dõi và cảnh báo trước 500km
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- ==================== MODAL THÊM LẦN ĐỔ XĂNG/DẦU ==================== --}}
<div id="addFuelModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl overflow-hidden">
        <div class="p-5 border-b border-neutral-100 flex items-center justify-between bg-primary-50/50">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center text-lg font-bold">
                    <iconify-icon icon="solar:gas-station-bold"></iconify-icon>
                </div>
                <div>
                    <h3 class="font-bold text-neutral-800 text-base leading-tight">Thêm lần đổ xăng / dầu</h3>
                    <p class="text-xs text-neutral-500">Xe: <strong>{{ $vehicle->plate_number }}</strong> (Km gần nhất: {{ number_format($vehicle->current_km, 0, ',', '.') }} km)</p>
                </div>
            </div>
            <button type="button" onclick="closeAddFuelModal()" class="text-neutral-400 hover:text-neutral-600 text-2xl leading-none">&times;</button>
        </div>

        <form action="{{ route('vehicles.fuel-logs.store', $vehicle->id) }}" method="POST" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="type" value="fuel">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Ngày tháng năm <span class="text-danger-500">*</span></label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Số Km công tơ mét <span class="text-danger-500">*</span></label>
                    <input type="number" step="0.1" name="odometer_km" required placeholder="VD: {{ $vehicle->current_km + 500 }}"
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden font-mono font-bold">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Số lượng (lít) <span class="text-danger-500">*</span></label>
                    <input type="number" step="0.001" id="add_liters" name="liters" required placeholder="VD: 70.425" oninput="calcFuelTotal('add')"
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Đơn giá (đ/lít) <span class="text-danger-500">*</span></label>
                    <input type="number" step="10" id="add_unit_price" name="unit_price" required placeholder="VD: 28290" oninput="calcFuelTotal('add')"
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden font-mono">
                </div>
            </div>

            {{-- Tự động tính số tiền = Đơn giá * Số lít --}}
            <div class="bg-amber-50/70 border border-amber-200 rounded-xl p-3 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-amber-900 block">Số tiền (Tự động tính)</span>
                    <span class="text-[11px] text-amber-700">= Đơn giá &times; Số lít</span>
                </div>
                <div class="text-lg font-black font-mono text-amber-900" id="add_total_preview">0 ₫</div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1">Ghi chú</label>
                <input type="text" name="notes" placeholder="VD: Cây xăng Petrolimex QL1A..."
                       class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden">
            </div>

            <div class="pt-3 border-t border-neutral-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeAddFuelModal()" class="px-4 py-2 border border-neutral-300 rounded-xl text-xs font-medium text-neutral-600 hover:bg-neutral-50">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold shadow-2xs">Lưu lần đổ dầu</button>
            </div>
        </form>
    </div>
</div>

{{-- ==================== MODAL GHI NHẬN THAY DẦU MÁY ==================== --}}
<div id="oilChangeModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl overflow-hidden">
        <div class="p-5 border-b border-neutral-100 flex items-center justify-between bg-purple-50/60">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg font-bold">
                    <iconify-icon icon="solar:wrench-bold"></iconify-icon>
                </div>
                <div>
                    <h3 class="font-bold text-neutral-800 text-base leading-tight">Ghi nhận Thay Dầu Máy</h3>
                    <p class="text-xs text-neutral-500">Đánh dấu mốc thay dầu & reset chu kỳ 5.000km</p>
                </div>
            </div>
            <button type="button" onclick="closeOilChangeModal()" class="text-neutral-400 hover:text-neutral-600 text-2xl leading-none">&times;</button>
        </div>

        <form action="{{ route('vehicles.fuel-logs.store', $vehicle->id) }}" method="POST" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="type" value="oil_change">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Ngày thay dầu <span class="text-danger-500">*</span></label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:outline-hidden">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Số Km khi thay dầu <span class="text-danger-500">*</span></label>
                    <input type="number" step="0.1" name="odometer_km" value="{{ $vehicle->current_km }}" required
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:outline-hidden font-mono font-bold">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Số lít dầu nhớt thay (tùy chọn)</label>
                    <input type="number" step="0.1" name="liters" placeholder="VD: 6.5"
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:outline-hidden font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Chi phí thay dầu (VNĐ)</label>
                    <input type="number" step="1000" name="unit_price" placeholder="VD: 850000"
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:outline-hidden font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1">Ghi chú loại nhớt / garage thay</label>
                <input type="text" name="notes" value="Thay dầu máy định kỳ"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:outline-hidden">
            </div>

            <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl text-xs text-purple-800">
                <iconify-icon icon="lucide:info" class="inline text-sm mr-1"></iconify-icon>
                Sau khi ghi nhận, hệ thống sẽ tính lại số km từ mốc này để đếm lên mốc 5.000km tiếp theo.
            </div>

            <div class="pt-3 border-t border-neutral-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeOilChangeModal()" class="px-4 py-2 border border-neutral-300 rounded-xl text-xs font-medium text-neutral-600 hover:bg-neutral-50">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold shadow-2xs">Xác nhận thay dầu</button>
            </div>
        </form>
    </div>
</div>

{{-- ==================== MODAL SỬA BẢN GHI ==================== --}}
<div id="editFuelModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl overflow-hidden">
        <div class="p-5 border-b border-neutral-100 flex items-center justify-between bg-neutral-50/50">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold">
                    <iconify-icon icon="lucide:edit-3"></iconify-icon>
                </div>
                <div>
                    <h3 class="font-bold text-neutral-800 text-base leading-tight">Chỉnh sửa bản ghi</h3>
                    <p class="text-xs text-neutral-400" id="editFuelHeader"></p>
                </div>
            </div>
            <button type="button" onclick="closeEditFuelModal()" class="text-neutral-400 hover:text-neutral-600 text-2xl leading-none">&times;</button>
        </div>

        <form id="editFuelForm" method="POST" class="p-5 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Loại bản ghi</label>
                    <select id="edit_type" name="type" class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden bg-white">
                        <option value="fuel">Đổ xăng / dầu</option>
                        <option value="oil_change">Thay dầu máy</option>
                        <option value="maintenance">Bảo dưỡng khác</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Ngày tháng năm <span class="text-danger-500">*</span></label>
                    <input type="date" id="edit_date" name="date" required
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1">Số Km công tơ mét</label>
                <input type="number" step="0.1" id="edit_odometer_km" name="odometer_km"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden font-mono font-bold">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Số lượng (lít)</label>
                    <input type="number" step="0.001" id="edit_liters" name="liters" oninput="calcFuelTotal('edit')"
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1">Đơn giá</label>
                    <input type="number" step="10" id="edit_unit_price" name="unit_price" oninput="calcFuelTotal('edit')"
                           class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden font-mono">
                </div>
            </div>

            <div class="bg-amber-50/70 border border-amber-200 rounded-xl p-3 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-amber-900 block">Số tiền (Tự động tính)</span>
                    <span class="text-[11px] text-amber-700">= Đơn giá &times; Số lít</span>
                </div>
                <div class="text-lg font-black font-mono text-amber-900" id="edit_total_preview">0 ₫</div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1">Ghi chú</label>
                <input type="text" id="edit_notes" name="notes"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:outline-hidden">
            </div>

            <div class="pt-3 border-t border-neutral-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditFuelModal()" class="px-4 py-2 border border-neutral-300 rounded-xl text-xs font-medium text-neutral-600 hover:bg-neutral-50">Hủy</button>
                <button type="submit" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold shadow-2xs">Cập nhật bản ghi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddFuelModal() {
        document.getElementById('addFuelModal').classList.remove('hidden');
    }
    function closeAddFuelModal() {
        document.getElementById('addFuelModal').classList.add('hidden');
    }

    function openOilChangeModal() {
        document.getElementById('oilChangeModal').classList.remove('hidden');
    }
    function closeOilChangeModal() {
        document.getElementById('oilChangeModal').classList.add('hidden');
    }

    function calcFuelTotal(mode) {
        const liters = parseFloat(document.getElementById(mode + '_liters').value) || 0;
        const price = parseFloat(document.getElementById(mode + '_unit_price').value) || 0;
        const total = Math.round(liters * price);
        document.getElementById(mode + '_total_preview').innerText = total.toLocaleString('vi-VN') + ' ₫';
    }

    function openEditFuelModal(log) {
        document.getElementById('editFuelForm').action = '/vehicles/{{ $vehicle->id }}/fuel-logs/' + log.id;
        document.getElementById('editFuelHeader').innerText = 'Ngày ' + (log.date ? log.date.split('T')[0] : '');
        document.getElementById('edit_type').value = log.type || 'fuel';
        document.getElementById('edit_date').value = log.date ? log.date.split('T')[0] : '';
        document.getElementById('edit_odometer_km').value = log.odometer_km !== null ? log.odometer_km : '';
        document.getElementById('edit_liters').value = log.liters !== null ? log.liters : '';
        document.getElementById('edit_unit_price').value = log.unit_price !== null ? log.unit_price : '';
        document.getElementById('edit_notes').value = log.notes || '';

        calcFuelTotal('edit');
        document.getElementById('editFuelModal').classList.remove('hidden');
    }
    function closeEditFuelModal() {
        document.getElementById('editFuelModal').classList.add('hidden');
    }
</script>

@endsection
