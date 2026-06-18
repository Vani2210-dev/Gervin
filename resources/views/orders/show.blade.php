@extends('layout.layout')
@php
    $title    = 'Chi tiết đơn hàng';
    $subTitle = 'Đơn hàng: ' . $acrylicOrder->order_code;
@endphp

@section('content')

<style>
    /* Bỏ logic sticky cột cuối cùng riêng cho trang này */
    .table th:last-child {
        position: static !important;
        background-color: rgb(243 244 246) !important;
        z-index: auto !important;
    }
    .table td:last-child {
        position: static !important;
        background-color: inherit !important;
        z-index: auto !important;
    }
    .table th:last-child::before,
    .table td:last-child::before {
        display: none !important;
    }
    .dark .table th:last-child {
        background-color: rgb(31 41 55) !important;
    }
    .dark .table td:last-child {
        background-color: inherit !important;
    }

    /* Vẽ lại các đường cắt dọc cho bảng */
    .table th,
    .table td {
        border-right: 1px solid rgb(235, 236, 239) !important;
    }
    .dark .table th,
    .dark .table td {
        border-right: 1px solid rgb(75, 85, 99) !important;
    }
</style>

<div class="grid grid-cols-12 gap-6">
    {{-- Main Order Info --}}
    <div class="col-span-12 md:col-span-8 space-y-6">
        <div class="card p-0 rounded-xl border-0 overflow-hidden shadow-sm bg-white mb-2">
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary-50 rounded-lg text-primary-500">
                        <iconify-icon icon="lucide:receipt" class="text-xl"></iconify-icon>
                    </div>
                    <div>
                        <h5 class="font-bold text-lg text-neutral-800 m-0">{{ $acrylicOrder->order_code }}</h5>
                        <p class="text-xs text-neutral-400 m-0">Tạo ngày: {{ $acrylicOrder->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @php
                        $typeColors = [
                            'acrylic' => 'bg-primary-100 text-primary-600 border border-primary-200',
                            'min_late' => 'bg-warning-100 text-warning-600 border border-warning-200',
                            'glass' => 'bg-info-100 text-info-600 border border-info-200',
                        ];
                        $typeLabels = [
                            'acrylic' => 'Acrylic',
                            'min_late' => 'Min Late',
                            'glass' => 'Glass',
                        ];
                        $statusColors = [
                            'pending' => 'bg-warning-100 text-warning-600 border border-warning-200',
                            'processing' => 'bg-info-100 text-info-600 border border-info-200',
                            'completed' => 'bg-success-100 text-success-600 border border-success-200',
                            'cancelled' => 'bg-danger-100 text-danger-600 border border-danger-200',
                        ];
                        $statusLabels = [
                            'pending' => 'Chờ xử lý',
                            'processing' => 'Đang xử lý',
                            'completed' => 'Hoàn thành',
                            'cancelled' => 'Đã hủy',
                        ];
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $typeColors[$acrylicOrder->type] ?? 'bg-neutral-100 text-neutral-600' }}">
                        {{ $typeLabels[$acrylicOrder->type] ?? $acrylicOrder->type }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$acrylicOrder->status] ?? 'bg-neutral-100 text-neutral-600' }}">
                        {{ $statusLabels[$acrylicOrder->status] ?? $acrylicOrder->status }}
                    </span>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Khách hàng</span>
                        <span class="font-medium text-neutral-800">{{ $acrylicOrder->customer_name }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Số điện thoại</span>
                        <span class="font-medium text-neutral-800">{{ $acrylicOrder->phone ?? '—' }}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Ngày chốt đơn</span>
                        <span class="font-medium text-neutral-800">
                            {{ $acrylicOrder->order_date ? \Carbon\Carbon::parse($acrylicOrder->order_date)->format('d/m/Y H:i') : '—' }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Hạn giao hàng (Deadline)</span>
                        <span class="font-medium text-danger-600">
                            {{ $acrylicOrder->deadline ? \Carbon\Carbon::parse($acrylicOrder->deadline)->format('d/m/Y') : '—' }}
                            @if($acrylicOrder->delivery_days)
                                <span class="text-xs text-neutral-400 font-normal">({{ $acrylicOrder->delivery_days }} ngày phải giao)</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex flex-col gap-1 md:col-span-2">
                        <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Địa chỉ giao hàng</span>
                        <span class="font-medium text-neutral-800">{{ $acrylicOrder->address ?? '—' }}</span>
                    </div>
                    <div class="flex flex-col gap-1 md:col-span-2">
                        <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Ghi chú đơn hàng</span>
                        <span class="text-neutral-600 italic bg-neutral-50 p-3 rounded-lg border border-neutral-100">{{ $acrylicOrder->notes ?? 'Không có ghi chú' }}</span>
                    </div>
                    <div class="flex flex-col gap-1 md:col-span-2">
                        <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Chính sách KH</span>
                        <span class="text-neutral-600 italic bg-neutral-50 p-3 rounded-lg border border-neutral-100">{{ $acrylicOrder->customer_policy ?? 'Không có chính sách' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Supplies & Items list --}}
        @foreach($acrylicOrder->supplies as $supply)
        <div class="card p-0 rounded-xl border-0 overflow-hidden shadow-sm bg-white border-l-4 border-l-primary-500 mb-2">
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                        <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                    </div>
                    <h6 class="font-bold text-base text-neutral-800 m-0">Vật tư: {{ $supply->order_supply_code ? '[' . $supply->order_supply_code . '] ' : '' }}{{ $supply->supply_name }}</h6>
                </div>
                @if($supply->quantity)
                <span class="text-sm font-semibold bg-neutral-100 px-3 py-1 rounded-lg text-neutral-700">
                    SL: {{ $supply->quantity }}
                </span>
                @endif
            </div>

            <div class="p-6">
                <div class="overflow-x-auto">
                    @if($acrylicOrder->type === 'min_late')
                        {{-- Min Late items --}}
                        <table class="table bordered-table sm-table mb-0 min-w-[1700px]">
                            <thead>
                                <tr>
                                    <th scope="col" class="w-10 text-center">STT</th>
                                    <th scope="col" class="w-32">Mã SP</th>
                                    <th scope="col" class="w-64">Tên SP</th>
                                    <th scope="col" class="w-20">Độ dày</th>
                                    <th scope="col" class="w-20">SL</th>
                                    <th scope="col" class="w-20">Cao</th>
                                    <th scope="col" class="w-20">Rộng</th>
                                    <th scope="col" class="w-28">Dán cạnh</th>
                                    <th scope="col" class="w-24">Dán thẳng</th>
                                    <th scope="col" class="w-24">Dán vát</th>
                                    <th scope="col" class="w-24">Vát mòi</th>
                                    <th scope="col" class="w-28">Bản rộng 40-59</th>
                                    <th scope="col" class="w-28">Bản rộng 17-39</th>
                                    <th scope="col" class="w-28">Bản rộng 25-35</th>
                                    <th scope="col" class="w-28">Tay nắm vát</th>
                                    <th scope="col" class="w-20 text-center">CNC</th>
                                    <th scope="col" class="w-24">Chiều vân</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supply->minLateItems as $itemIndex => $item)
                                @php
                                    $sizes = $item->size ?? [];
                                    if (is_string($sizes)) {
                                        $sizes = json_decode($sizes, true) ?? [];
                                    }
                                    $edgeGluing = $item->edge_gluing ?? [];
                                    if (is_string($edgeGluing)) {
                                        $edgeGluing = json_decode($edgeGluing, true) ?? [];
                                    }
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $itemIndex + 1 }}</td>
                                    <td><span class="text-neutral-500 text-xs">{{ $item->product_code ?? '—' }}</span></td>
                                    <td><span class="font-medium text-neutral-800">{{ $item->name }}</span></td>
                                    <td>{{ $item->thickness ?? '—' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $sizes['height'] ?? '—' }}</td>
                                    <td>{{ $sizes['width'] ?? '—' }}</td>
                                    <td>
                                        @if(!empty($edgeGluing))
                                            <span class="text-xs bg-neutral-100 px-2 py-0.5 rounded text-neutral-600">{{ implode(', ', $edgeGluing) }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $item->straight_paste_length ?? '—' }}</td>
                                    <td>{{ $item->beveled_length ?? '—' }}</td>
                                    <td>{{ $item->vat_moi_length ?? '—' }}</td>
                                    <td>{{ $item->ban_rong_40_59 ?? '—' }}</td>
                                    <td>{{ $item->call_rong_17_39 ?? $item->ban_rong_17_39 ?? '—' }}</td>
                                    <td>{{ $item->ban_rong_25_35 ?? '—' }}</td>
                                    <td>{{ $item->beveled_handle ?? '—' }}</td>
                                    <td class="text-center">
                                        @if($item->cnc)
                                            <iconify-icon icon="lucide:check-circle" class="text-success-500 text-lg"></iconify-icon>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $item->direction ?? '—' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="17" class="text-center text-neutral-400 py-4">Chưa có sản phẩm nào</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @elseif($acrylicOrder->type === 'glass')
                        {{-- Glass items --}}
                        <table class="table bordered-table sm-table mb-0 min-w-[1800px]">
                            <thead>
                                <tr>
                                    <th scope="col" class="w-10 text-center">STT</th>
                                    <th scope="col" class="w-32">Mã SP</th>
                                    <th scope="col" class="w-64">Tên SP</th>
                                    <th scope="col" class="w-20">Độ dày</th>
                                    <th scope="col" class="w-28">Chiều mở cánh</th>
                                    <th scope="col" class="w-28">Màu nhôm</th>
                                    <th scope="col" class="w-28">Màu kính</th>
                                    <th scope="col" class="w-20">Dài</th>
                                    <th scope="col" class="w-20">Rộng</th>
                                    <th scope="col" class="w-20">Đơn vị</th>
                                    <th scope="col" class="w-24">SL cánh</th>
                                    <th scope="col" class="w-28">Khối lượng (m2)</th>
                                    <th scope="col" class="w-28 text-end">Đơn giá</th>
                                    <th scope="col" class="w-28 text-end">Thành tiền</th>
                                    <th scope="col" class="w-44">Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supply->glassItems as $itemIndex => $item)
                                <tr>
                                    <td class="text-center">{{ $itemIndex + 1 }}</td>
                                    <td><span class="text-neutral-500 text-xs">{{ $item->product_code ?? '—' }}</span></td>
                                    <td><span class="font-medium text-neutral-800">{{ $item->product_name }}</span></td>
                                    <td>{{ $item->thickness ?? '—' }}</td>
                                    <td>{{ $item->wing_opening_direction ?? '—' }}</td>
                                    <td>{{ $item->aluminum_color ?? '—' }}</td>
                                    <td>{{ $item->glass_color ?? '—' }}</td>
                                    <td>{{ $item->height ?? '—' }}</td>
                                    <td>{{ $item->width ?? '—' }}</td>
                                    <td>{{ $item->unit ?? 'Bộ' }}</td>
                                    <td>{{ $item->wing_quantity }}</td>
                                    <td>{{ $item->area_m2 ?? '—' }}</td>
                                    <td class="text-end font-medium text-neutral-600">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-end font-semibold text-neutral-800">{{ number_format($item->total_price, 0, ',', '.') }}</td>
                                    <td><span class="text-neutral-500 text-xs">{{ $item->notes ?? '—' }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="15" class="text-center text-neutral-400 py-4">Chưa có sản phẩm nào</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @else
                        {{-- Acrylic items - expand to individual sheets with status --}}
                        <table class="table bordered-table sm-table mb-0 min-w-[1700px]">
                            <thead>
                                <tr>
                                    <th scope="col" class="w-10 text-center">STT</th>
                                    <th scope="col" class="w-32">Mã tấm</th>
                                    <th scope="col" class="w-64">Tên SP</th>
                                    <th scope="col" class="w-20">Độ dày</th>
                                    <th scope="col" class="w-20">Cao</th>
                                    <th scope="col" class="w-20">Rộng</th>
                                    <th scope="col" class="w-24">Vát</th>
                                    <th scope="col" class="w-24">Chiều vân</th>
                                    <th scope="col" class="w-24">Cánh (m2)</th>
                                    <th scope="col" class="w-24">Phào (m)</th>
                                    <th scope="col" class="w-24">Cạnh Vát</th>
                                    <th scope="col" class="w-28">Vân dọc CNC</th>
                                    <th scope="col" class="w-28 text-end">Đơn giá</th>
                                    <th scope="col" class="w-28 text-end">Thành tiền</th>
                                    <th scope="col" class="w-36">Giai đoạn</th>
                                    <th scope="col" class="w-28">Người thực hiện</th>
                                    <th scope="col" class="w-44">Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $globalSheetIndex = 0; @endphp
                                @forelse($supply->items as $item)
                                @php
                                    $codes = $item->codes;
                                    $hasAnyCode = $codes->count() > 0;
                                    $rowCount = $hasAnyCode ? $codes->count() : 1;
                                @endphp
                                @for($sheetIdx = 0; $sheetIdx < $rowCount; $sheetIdx++)
                                @php
                                    $globalSheetIndex++;
                                    $code = $hasAnyCode ? $codes[$sheetIdx] : null;
                                    $productId = $code ? $code->product_id : ($item->product_code ?? '—');
                                    $statusLog = $code ? ($code->status ?? []) : [];
                                    $lastEntry = !empty($statusLog) ? end($statusLog) : null;
                                    $currentAction = $lastEntry ? ($lastEntry['action'] ?? '—') : '—';
                                    $currentOperator = $lastEntry ? ($lastEntry['operator'] ?? '—') : '—';
                                    $actionColors = [
                                        'chờ xử lý' => 'bg-neutral-100 text-neutral-500',
                                        'đang xử lý' => 'bg-info-100 text-info-600',
                                        'đã nhận tem' => 'bg-warning-100 text-warning-600',
                                        'hoàn thành' => 'bg-success-100 text-success-600',
                                        'đã hủy' => 'bg-danger-100 text-danger-600',
                                    ];
                                    $actionColor = $actionColors[mb_strtolower($currentAction)] ?? 'bg-primary-50 text-primary-600';
                                @endphp
                                <tr class="{{ $sheetIdx === 0 ? 'border-t-2 border-neutral-200' : '' }}">
                                    <td class="text-center font-semibold text-neutral-500">{{ $globalSheetIndex }}</td>
                                    <td><span class="text-neutral-500 text-xs font-mono">{{ $productId }}</span></td>
                                    @if($sheetIdx === 0)
                                    <td rowspan="{{ $rowCount }}" class="font-medium text-neutral-800 align-top pt-3">{{ $item->product_name }}</td>
                                    <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->thickness ?? '—' }}</td>
                                    <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->height ?? '—' }}</td>
                                    <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->width ?? '—' }}</td>
                                    <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->bevel ?? '—' }}</td>
                                    <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->grain_direction ?? '0' }}</td>
                                    <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->wing_area ?? '—' }}</td>
                                    <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->molding_length ?? '—' }}</td>
                                    <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->edge_bevel ?? '—' }}</td>
                                    <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->vertical_grain_cnc ?? '—' }}</td>
                                    <td rowspan="{{ $rowCount }}" class="text-end font-medium text-neutral-600 align-top pt-3">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td rowspan="{{ $rowCount }}" class="text-end font-semibold text-neutral-800 align-top pt-3">{{ number_format($item->total_price, 0, ',', '.') }}</td>
                                    @endif
                                    <td>
                                        @if($currentAction !== '—')
                                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $actionColor }}">
                                            {{ $currentAction }}
                                        </span>
                                        @else
                                        <span class="text-neutral-300 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="text-xs text-neutral-500">{{ $currentOperator !== '—' ? $currentOperator : '' }}</td>
                                    @if($sheetIdx === 0)
                                    <td rowspan="{{ $rowCount }}" class="align-top pt-3"><span class="text-neutral-500 text-xs">{{ $item->notes ?? '—' }}</span></td>
                                    @endif
                                </tr>
                                @endfor
                                @empty
                                <tr>
                                    <td colspan="17" class="text-center text-neutral-400 py-4">Chưa có sản phẩm nào</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

        @if($acrylicOrder->type === 'min_late' && isset($acrylicOrder->paymentDetails) && $acrylicOrder->paymentDetails->count() > 0)
        <div class="card p-0 rounded-xl border-0 overflow-hidden shadow-sm bg-white border-l-4 border-l-primary-500">
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                        <iconify-icon icon="lucide:receipt" class="text-base"></iconify-icon>
                    </div>
                    <h6 class="font-bold text-base text-neutral-800 m-0">Chi tiết hóa đơn (Min Late)</h6>
                </div>
            </div>

            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="table bordered-table sm-table mb-0 min-w-[800px]">
                        <thead>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" class="w-10 text-center">STT</th>
                                <th scope="col">Tên nội dung</th>
                                <th scope="col" class="w-28 text-center">Đơn vị</th>
                                <th scope="col" class="w-24 text-center">Số lượng</th>
                                <th scope="col" class="w-32 text-end">Đơn giá</th>
                                <th scope="col" class="w-32 text-end">Đơn giá chỉ gỗ</th>
                                <th scope="col" class="w-32 text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($acrylicOrder->paymentDetails as $detailIndex => $detail)
                            <tr>
                                <td class="text-center font-semibold text-neutral-500">{{ $detailIndex + 1 }}</td>
                                <td><span class="font-semibold text-neutral-800">{{ $detail->name }}</span></td>
                                <td class="text-center">{{ $detail->unit ?? '—' }}</td>
                                <td class="text-center font-medium">{{ number_format($detail->quantity, 2, ',', '.') }}</td>
                                <td class="text-end font-medium text-neutral-600">{{ number_format($detail->price, 0, ',', '.') }}</td>
                                <td class="text-end font-medium text-neutral-600">{{ number_format($detail->price_only, 0, ',', '.') }}</td>
                                <td class="text-end font-bold text-primary-600">{{ number_format($detail->total, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Order Summary --}}
    <div class="col-span-12 md:col-span-4 space-y-6">
        <div class="card p-0 rounded-xl border-0 overflow-hidden shadow-sm bg-white mb-2">
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center gap-2">
                <iconify-icon icon="lucide:receipt-text" class="text-xl text-primary-500"></iconify-icon>
                <h6 class="font-bold text-base text-neutral-800 m-0">Tóm tắt đơn hàng</h6>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-neutral-500">Tổng số sản phẩm:</span>
                    <span class="font-semibold text-neutral-800" id="summary-total-items">
                        {{ $acrylicOrder->supplies->flatMap(function($s) use ($acrylicOrder) {
                            if ($acrylicOrder->type === 'min_late') return $s->minLateItems;
                            if ($acrylicOrder->type === 'glass') return $s->glassItems;
                            return $s->items;
                        })->sum(fn($i) => $i->quantity ?? $i->wing_quantity ?? 0) }}
                    </span>
                </div>
                <div class="border-t border-neutral-100 pt-3 flex justify-between items-center">
                    <span class="text-base font-bold text-neutral-800">Tổng thanh toán:</span>
                    <span class="text-lg font-black text-primary-600" id="summary-total-amount">
                        {{ number_format(round($acrylicOrder->total_amount, -3), 0, ',', '.') }} VNĐ
                    </span>
                </div>
            </div>
        </div>

        {{-- ===== THANH TOÁN ===== --}}
        @php
            $payments    = $acrylicOrder->orderPayments ?? collect();
            $totalPaid   = $payments->sum('amount');
            $totalAmount = $acrylicOrder->total_amount ?? 0;
            $totalDebt   = max(0, $totalAmount - $totalPaid);
            $paidPct     = $totalAmount > 0 ? min(100, round($totalPaid / $totalAmount * 100)) : 0;
        @endphp
        <div class="card p-0 rounded-xl border-0 overflow-hidden shadow-sm bg-white">
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center gap-2">
                <iconify-icon icon="lucide:wallet" class="text-xl text-violet-500"></iconify-icon>
                <h6 class="font-bold text-base text-neutral-800 m-0 flex-1">Thanh toán</h6>
                @if($paidPct >= 100)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-success-100 text-success-600 font-semibold">Đã thanh toán đủ</span>
                @elseif($totalPaid > 0)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-warning-100 text-warning-600 font-semibold">{{ $paidPct }}%</span>
                @endif
            </div>
            <div class="p-5 space-y-4">
                {{-- Stat row --}}
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="bg-neutral-50 rounded-lg p-3">
                        <div class="text-xs text-neutral-400 font-medium mb-1">Tổng đơn</div>
                        <div class="text-sm font-bold text-neutral-800">{{ number_format(round($totalAmount,-3),0,',','.') }}₫</div>
                    </div>
                    <div class="bg-success-50 rounded-lg p-3">
                        <div class="text-xs text-success-500 font-medium mb-1">Đã thu</div>
                        <div class="text-sm font-bold text-success-600">{{ number_format($totalPaid,0,',','.') }}₫</div>
                    </div>
                    <div class="rounded-lg p-3 {{ $totalDebt > 0 ? 'bg-danger-50' : 'bg-success-50' }}">
                        <div class="text-xs font-medium mb-1 {{ $totalDebt > 0 ? 'text-danger-500' : 'text-success-500' }}">Còn nợ</div>
                        <div class="text-sm font-bold {{ $totalDebt > 0 ? 'text-danger-600' : 'text-success-600' }}">{{ number_format($totalDebt,0,',','.') }}₫</div>
                    </div>
                </div>

                {{-- Progress bar --}}
                <div>
                    <div class="flex justify-between text-xs text-neutral-400 mb-1">
                        <span>Tiến độ thanh toán</span>
                        <span>{{ $paidPct }}%</span>
                    </div>
                    <div class="w-full bg-neutral-100 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all {{ $paidPct >= 100 ? 'bg-success-500' : ($paidPct > 0 ? 'bg-warning-400' : 'bg-neutral-200') }}"
                            style="width:{{ $paidPct }}%"></div>
                    </div>
                </div>

                {{-- Payment history --}}
                @if($payments->count() > 0)
                <div>
                    <div class="text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-2">Lịch sử thanh toán</div>
                    <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                        @foreach($payments as $pmt)
                        <div class="flex items-start gap-2 bg-neutral-50 rounded-lg px-3 py-2 group">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-semibold text-neutral-700">{{ $pmt->payment_date->format('d/m/Y') }}</span>
                                    <span class="text-xs px-1.5 py-0.5 rounded font-medium
                                        {{ $pmt->payment_method === 'cash' ? 'bg-amber-100 text-amber-700' : ($pmt->payment_method === 'transfer' ? 'bg-blue-100 text-blue-700' : 'bg-neutral-200 text-neutral-600') }}">
                                        {{ \App\Models\OrderPayment::methodLabel($pmt->payment_method) }}
                                    </span>
                                </div>
                                <div class="text-sm font-bold text-success-600 mt-0.5">+{{ number_format($pmt->amount,0,',','.') }}₫</div>
                                @if($pmt->note)
                                    <div class="text-xs text-neutral-400 mt-0.5 truncate">{{ $pmt->note }}</div>
                                @endif
                            </div>
                            <div class="flex-shrink-0 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button"
                                    onclick="openEditPayment({{ $pmt->id }}, '{{ $pmt->payment_date->format('Y-m-d') }}', {{ $pmt->amount }}, '{{ $pmt->payment_method }}', '{{ addslashes($pmt->note ?? '') }}')"
                                    class="text-neutral-400 hover:text-primary-500 p-1 rounded">
                                    <iconify-icon icon="lucide:edit-2" style="font-size:13px;"></iconify-icon>
                                </button>
                                <form method="POST" action="{{ route('orders.payments.destroy', [$acrylicOrder, $pmt]) }}"
                                    onsubmit="return confirm('Xóa đợt thanh toán này?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-neutral-400 hover:text-danger-500 p-1 rounded">
                                        <iconify-icon icon="lucide:trash-2" style="font-size:13px;"></iconify-icon>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="text-center py-4 text-neutral-300">
                    <iconify-icon icon="lucide:coins" style="font-size:28px;"></iconify-icon>
                    <div class="text-xs mt-1">Chưa có đợt thanh toán nào</div>
                </div>
                @endif

                {{-- Add payment form --}}
                <div>
                    <button type="button" id="toggle-add-payment"
                        onclick="document.getElementById('add-payment-form').classList.toggle('hidden'); this.classList.toggle('hidden')"
                        class="w-full flex items-center justify-center gap-2 py-2 rounded-lg border border-dashed border-violet-300 text-violet-600 hover:bg-violet-50 text-sm font-semibold transition-colors">
                        <iconify-icon icon="lucide:plus-circle" style="font-size:16px;"></iconify-icon>
                        Thêm đợt thanh toán
                    </button>
                    <div id="add-payment-form" class="hidden">
                        <form method="POST" action="{{ route('orders.payments.store', $acrylicOrder) }}" class="space-y-3">
                            @csrf
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-xs font-semibold text-neutral-500 block mb-1">Ngày <span class="text-danger-500">*</span></label>
                                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                                        class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-violet-400 focus:ring-violet-400 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-neutral-500 block mb-1">Số tiền (₫) <span class="text-danger-500">*</span></label>
                                    <input type="number" name="amount" min="1" step="1000" placeholder="0"
                                        class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-violet-400 focus:ring-violet-400 text-sm" required>
                                </div>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-neutral-500 block mb-1">Hình thức</label>
                                <select name="payment_method" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-violet-400 focus:ring-violet-400 text-sm">
                                    <option value="cash">💵 Tiền mặt</option>
                                    <option value="transfer">🏦 Chuyển khoản</option>
                                    <option value="other">📋 Khác</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-neutral-500 block mb-1">Ghi chú</label>
                                <input type="text" name="note" placeholder="Ghi chú (nếu có)"
                                    class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-violet-400 focus:ring-violet-400 text-sm">
                            </div>
                            <div class="flex gap-2">
                                <button type="submit"
                                    class="flex-1 py-2 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold transition-colors">
                                    Lưu thanh toán
                                </button>
                                <button type="button"
                                    onclick="document.getElementById('add-payment-form').classList.add('hidden'); document.getElementById('toggle-add-payment').classList.remove('hidden')"
                                    class="px-3 py-2 rounded-lg border border-neutral-200 text-neutral-600 text-sm font-semibold hover:bg-neutral-50 transition-colors">
                                    Hủy
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Order Attachments --}}
        @if($acrylicOrder->attachments)
        <div class="card p-0 rounded-xl border-0 overflow-hidden shadow-sm bg-white">
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center gap-2">
                <iconify-icon icon="lucide:paperclip" class="text-xl text-primary-500"></iconify-icon>
                <h6 class="font-bold text-base text-neutral-800 m-0">File đính kèm</h6>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-3">
                    @foreach(json_decode($acrylicOrder->attachments, true) ?? [] as $image)
                    <div class="relative group border border-neutral-100 rounded-lg overflow-hidden shadow-xs hover:shadow-md transition-all">
                        <a href="{{ route('orders.image', ['filename' => basename($image)]) }}" target="_blank" title="Xem ảnh gốc">
                            <img src="{{ route('orders.image', ['filename' => basename($image)]) }}" class="w-full h-24 object-cover transition-transform group-hover:scale-105">
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Navigation Actions --}}
        <div class="flex flex-col gap-3">
            <a href="{{ route('orders.index') }}" class="w-full justify-center flex items-center gap-2 py-3 rounded-xl font-semibold border border-neutral-200 text-neutral-700 hover:bg-neutral-50 transition-colors shadow-sm text-sm bg-white cursor-pointer">
                <iconify-icon icon="lucide:arrow-left" class="text-base"></iconify-icon> Quay lại danh sách
            </a>
            @can('edit order')
            <a href="{{ route('orders.edit', $acrylicOrder) }}" class="w-full justify-center flex items-center gap-2 py-3 rounded-xl font-semibold bg-neutral-900 hover:bg-black text-white shadow-sm text-sm transition-colors cursor-pointer">
                <iconify-icon icon="lucide:edit-3" class="text-base"></iconify-icon> Chỉnh sửa đơn hàng
            </a>
            @endcan
            <button type="button" onclick="exportToExcel()" class="w-full justify-center flex items-center gap-2 py-3 rounded-xl font-semibold border border-neutral-200 text-neutral-700 hover:bg-neutral-50 transition-colors shadow-sm text-sm bg-white cursor-pointer">
                <iconify-icon icon="lucide:file-spreadsheet" class="text-base"></iconify-icon> Xuất Excel (.xlsx)
            </button>
            @if($acrylicOrder->type === 'acrylic')
            <button type="button" onclick="exportNestingFiles()" class="w-full justify-center flex items-center gap-2 py-3 rounded-xl font-semibold border border-emerald-200 text-emerald-700 hover:bg-emerald-50 transition-colors shadow-sm text-sm bg-white cursor-pointer">
                <iconify-icon icon="lucide:table-2" class="text-base"></iconify-icon> Xuất Nesting (.xlsx)
            </button>
            @endif
        </div>
    </div>
</div>

@php
    $exportData = [
        'order_code' => $acrylicOrder->order_code,
        'created_at' => $acrylicOrder->created_at->format('d/m/Y H:i'),
        'type' => $acrylicOrder->type,
        'customer_name' => $acrylicOrder->customer_name,
        'phone' => $acrylicOrder->phone,
        'order_date' => $acrylicOrder->order_date ? \Carbon\Carbon::parse($acrylicOrder->order_date)->format('Y-m-d H:i:s') : null,
        'deadline' => $acrylicOrder->deadline ? \Carbon\Carbon::parse($acrylicOrder->deadline)->format('Y-m-d') : null,
        'address' => $acrylicOrder->address,
        'notes' => $acrylicOrder->notes,
        'customer_policy' => $acrylicOrder->customer_policy,
        'total_amount' => round($acrylicOrder->total_amount, -3),
        'delivery_days' => $acrylicOrder->delivery_days ?? ($acrylicOrder->type === 'glass' ? 5 : 2),
        'supplies' => $acrylicOrder->supplies->map(function($supply) use ($acrylicOrder) {
            $items = [];
            if ($acrylicOrder->type === 'min_late') {
                $items = $supply->minLateItems->map(function($item) {
                    $sizes = $item->size ?? [];
                    if (is_string($sizes)) {
                        $sizes = json_decode($sizes, true) ?? [];
                    }
                    $edgeGluing = $item->edge_gluing ?? [];
                    if (is_string($edgeGluing)) {
                        $edgeGluing = json_decode($edgeGluing, true) ?? [];
                    }
                    return [
                        'product_code' => $item->product_code,
                        'name' => $item->product_name ?? $item->name,
                        'thickness' => $item->thickness,
                        'quantity' => $item->quantity,
                        'height' => $sizes['height'] ?? null,
                        'width' => $sizes['width'] ?? null,
                        'edge_gluing' => $edgeGluing,
                        'straight_paste_length' => $item->straight_paste_length,
                        'beveled_length' => $item->beveled_length,
                        'vat_moi_length' => $item->vat_moi_length,
                        'ban_rong_40_59' => $item->ban_rong_40_59,
                        'ban_rong_17_39' => $item->call_rong_17_39 ?? $item->ban_rong_17_39,
                        'ban_rong_25_35' => $item->ban_rong_25_35,
                        'beveled_handle' => $item->beveled_handle,
                        'cnc' => $item->cnc,
                        'direction' => $item->direction,
                        'notes' => $item->notes,
                    ];
                });
            } elseif ($acrylicOrder->type === 'glass') {
                $items = $supply->glassItems->map(function($item) {
                    return [
                        'product_code' => $item->product_code,
                        'product_name' => $item->product_name,
                        'thickness' => $item->thickness,
                        'wing_opening_direction' => $item->wing_opening_direction,
                        'aluminum_color' => $item->aluminum_color,
                        'glass_color' => $item->glass_color,
                        'height' => $item->height,
                        'width' => $item->width,
                        'unit' => $item->unit ?? 'cánh',
                        'wing_quantity' => $item->wing_quantity,
                        'area_m2' => $item->area_m2,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'notes' => $item->notes,
                    ];
                });
            } else {
                $items = $supply->items->map(function($item) {
                    return [
                        'product_code' => $item->product_code,
                        'product_codes' => $item->codes->pluck('product_id')->toArray(),
                        'product_name' => $item->product_name,
                        'thickness' => $item->thickness,
                        'quantity' => $item->quantity,
                        'height' => $item->height,
                        'width' => $item->width,
                        'edge_bevel' => $item->edge_bevel,
                        'grain_direction' => $item->grain_direction,
                        'wing_area' => $item->wing_area,
                        'molding_length' => $item->molding_length,
                        'bevel' => $item->bevel,
                        'vertical_grain_cnc' => $item->vertical_grain_cnc,
                        'offset_left'   => $item->offset_left,
                        'offset_right'  => $item->offset_right,
                        'offset_top'    => $item->offset_top,
                        'offset_bottom' => $item->offset_bottom,
                        'mill_left'     => $item->mill_left,
                        'mill_right'    => $item->mill_right,
                        'mill_top'      => $item->mill_top,
                        'mill_bottom'   => $item->mill_bottom,
                        'mill_width'    => $item->mill_width,
                        'mill_depth'    => $item->mill_depth,
                        'mill_left_2'   => $item->mill_left_2,
                        'mill_right_2'  => $item->mill_right_2,
                        'mill_top_2'    => $item->mill_top_2,
                        'mill_bottom_2' => $item->mill_bottom_2,
                        'mill_width_2'  => $item->mill_width_2,
                        'mill_depth_2'  => $item->mill_depth_2,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'notes' => $item->notes,
                    ];
                });
            }
            return [
                'order_supply_code' => $supply->order_supply_code,
                'supply_name' => $supply->supply_name,
                'quantity' => $supply->quantity,
                'items' => $items
            ];
        }),
        'payment_details' => ($acrylicOrder->type === 'min_late' && isset($acrylicOrder->paymentDetails)) ? $acrylicOrder->paymentDetails->map(function($detail) {
            return [
                'name' => $detail->name,
                'unit' => $detail->unit,
                'quantity' => $detail->quantity,
                'price' => $detail->price,
                'price_only' => $detail->price_only,
                'total' => $detail->total,
            ];
        }) : []
    ];
@endphp

<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script>
    window.orderExportData = @json($exportData);
</script>
<script src="{{ asset('assets/js/order-export.js') }}"></script>
@if($acrylicOrder->type === 'acrylic')
<script src="{{ asset('assets/js/nesting-export.js') }}"></script>
@endif
<script>
    // Auto-export when accessed with ?export=1 (from orders index list button)
    (function() {
        const params = new URLSearchParams(window.location.search);
        if (params.get('export') === '1') {
            window.addEventListener('load', function() {
                // Small delay to ensure exceljs + FileSaver are ready
                setTimeout(function() {
                    if (typeof exportToExcel === 'function') {
                        exportToExcel();
                    }
                    // Navigate back to orders list after triggering download
                    setTimeout(function() {
                        window.history.back();
                    }, 800);
                }, 400);
            });
        }
    })();
</script>

{{-- ===== EDIT PAYMENT MODAL ===== --}}
<div id="edit-payment-backdrop"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:2000;"
    onclick="closeEditPayment()"></div>
<div id="edit-payment-modal"
    style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);
           width:min(420px,95vw); background:#fff; border-radius:16px;
           box-shadow:0 20px 60px rgba(0,0,0,0.3); z-index:2001; overflow:hidden;">
    <div style="background:linear-gradient(135deg,#7c3aed,#6d28d9); padding:18px 24px; display:flex; align-items:center; justify-content:space-between;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div style="background:rgba(255,255,255,0.2); border-radius:8px; padding:8px; display:flex;">
                <iconify-icon icon="lucide:edit-3" style="font-size:18px; color:#fff;"></iconify-icon>
            </div>
            <div style="font-size:15px; font-weight:700; color:#fff;">Sửa đợt thanh toán</div>
        </div>
        <button onclick="closeEditPayment()" style="background:rgba(255,255,255,0.15); border:none; border-radius:8px; width:30px; height:30px; cursor:pointer; display:flex; align-items:center; justify-content:center;">
            <iconify-icon icon="lucide:x" style="font-size:15px; color:#fff;"></iconify-icon>
        </button>
    </div>
    <form id="edit-payment-form" method="POST" style="padding:20px 24px; display:flex; flex-direction:column; gap:14px;">
        @csrf
        @method('PUT')
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Ngày <span style="color:#ef4444;">*</span></label>
                <input id="ep-date" type="date" name="payment_date" required
                    style="width:100%; padding:9px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Số tiền (₫) <span style="color:#ef4444;">*</span></label>
                <input id="ep-amount" type="number" name="amount" min="1" step="1000" required
                    style="width:100%; padding:9px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
            </div>
        </div>
        <div>
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Hình thức</label>
            <select id="ep-method" name="payment_method"
                style="width:100%; padding:9px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; background:#fff;">
                <option value="cash">💵 Tiền mặt</option>
                <option value="transfer">🏦 Chuyển khoản</option>
                <option value="other">📋 Khác</option>
            </select>
        </div>
        <div>
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Ghi chú</label>
            <input id="ep-note" type="text" name="note" placeholder="Ghi chú (nếu có)"
                style="width:100%; padding:9px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
        </div>
        <div style="display:flex; gap:10px; padding-top:4px;">
            <button type="submit"
                style="flex:1; padding:10px; border:none; border-radius:8px; background:linear-gradient(135deg,#7c3aed,#6d28d9); color:#fff; font-size:13px; font-weight:600; cursor:pointer;">
                Lưu thay đổi
            </button>
            <button type="button" onclick="closeEditPayment()"
                style="padding:10px 18px; border:1.5px solid #d1d5db; border-radius:8px; background:#fff; color:#374151; font-size:13px; font-weight:600; cursor:pointer;">
                Hủy
            </button>
        </div>
    </form>
</div>

<script>
function openEditPayment(id, date, amount, method, note) {
    const baseUrl = '{{ route('orders.payments.update', [$acrylicOrder, '__ID__']) }}'.replace('__ID__', id);
    document.getElementById('edit-payment-form').action = baseUrl;
    document.getElementById('ep-date').value   = date;
    document.getElementById('ep-amount').value = amount;
    document.getElementById('ep-method').value = method;
    document.getElementById('ep-note').value   = note;
    document.getElementById('edit-payment-backdrop').style.display = 'block';
    document.getElementById('edit-payment-modal').style.display    = 'block';
}
function closeEditPayment() {
    document.getElementById('edit-payment-backdrop').style.display = 'none';
    document.getElementById('edit-payment-modal').style.display    = 'none';
}

// Auto-open add form on validation error flash
@if(session('success') && str_contains(session('success'), 'thanh toán'))
document.addEventListener('DOMContentLoaded', function() {
    // Briefly highlight the payment card on success
    const card = document.querySelector('[data-payment-card]');
});
@endif
</script>

@endsection
