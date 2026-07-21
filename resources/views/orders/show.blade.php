@extends('layout.layout')
@php
    $title = 'Chi tiết đơn hàng';
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
                <div
                    class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-primary-50 rounded-lg text-primary-500">
                            <iconify-icon icon="lucide:receipt" class="text-xl"></iconify-icon>
                        </div>
                        <div>
                            <h5 class="font-bold text-lg text-neutral-800 m-0">{{ $acrylicOrder->order_code }}</h5>
                            <p class="text-xs text-neutral-400 m-0">Tạo ngày:
                                {{ $acrylicOrder->created_at->format('d/m/Y H:i') }}</p>
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
                                'transferred' => 'bg-info-100 text-info-600 border border-info-200',

                                'completed' => 'bg-success-100 text-success-600 border border-success-200',
                                'cancelled' => 'bg-danger-100 text-danger-600 border border-danger-200',
                            ];
                            $statusLabels = [
                                'pending' => 'Chờ xử lý',
                                'transferred' => 'Chuyển sản xuất',

                                'completed' => 'Hoàn thành',
                                'cancelled' => 'Đã hủy',
                            ];
                        @endphp
                        <span
                            class="px-3 py-1 rounded-full text-xs font-semibold {{ $typeColors[$acrylicOrder->type] ?? 'bg-neutral-100 text-neutral-600' }}">
                            {{ $typeLabels[$acrylicOrder->type] ?? $acrylicOrder->type }}
                        </span>
                        <span
                            class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$acrylicOrder->status] ?? 'bg-neutral-100 text-neutral-600' }}">
                            {{ $statusLabels[$acrylicOrder->status] ?? $acrylicOrder->status }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Khách hàng</span>
                            <span class="font-medium text-neutral-800">
                                {{ $acrylicOrder->customer_name }}
                            </span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Số điện
                                thoại</span>
                            <span class="font-medium text-neutral-800">{{ $acrylicOrder->phone ?? '—' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Ngày chốt
                                đơn</span>
                            <span class="font-medium text-neutral-800">
                                {{ $acrylicOrder->order_date ? \Carbon\Carbon::parse($acrylicOrder->order_date)->format('d/m/Y H:i') : '—' }}
                            </span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Hạn giao hàng
                                (Deadline)</span>
                            <span class="font-medium text-danger-600">
                                {{ $acrylicOrder->deadline ? \Carbon\Carbon::parse($acrylicOrder->deadline)->format('H:i d/m/Y') : '—' }}
                                @if($acrylicOrder->delivery_days)
                                    <span class="text-xs text-neutral-400 font-normal">({{ $acrylicOrder->delivery_days }} ngày
                                        phải giao)</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex flex-col gap-1 md:col-span-2">
                            <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Địa chỉ giao
                                hàng</span>
                            <span class="font-medium text-neutral-800">{{ $acrylicOrder->address ?? '—' }}</span>
                        </div>
                        <div class="flex flex-col gap-1 md:col-span-2">
                            <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Ghi chú đơn
                                hàng</span>
                            <span
                                class="text-neutral-600 italic bg-neutral-50 p-3 rounded-lg border border-neutral-100">{{ $acrylicOrder->notes ?? 'Không có ghi chú' }}</span>
                        </div>
                        <div class="flex flex-col gap-1 md:col-span-2">
                            <span class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Chính sách
                                KH</span>
                            <span
                                class="text-neutral-600 italic bg-neutral-50 p-3 rounded-lg border border-neutral-100">{{ $acrylicOrder->customer_policy ?? 'Không có chính sách' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Supplies & Items list --}}
            @foreach($acrylicOrder->supplies as $supply)
                <div
                    class="card p-0 rounded-xl border-0 overflow-hidden shadow-sm bg-white border-l-4 border-l-primary-500 mb-2">
                    <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                                <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                            </div>
                            <h6 class="font-bold text-base text-neutral-800 m-0">Vật tư:
                                {{ $supply->order_supply_code ? '[' . $supply->order_supply_code . '] ' : '' }}{{ $supply->supply_name }}
                            </h6>
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
                                            <th scope="col" class="w-20">Cao</th>
                                            <th scope="col" class="w-20">Rộng</th>
                                            <th scope="col" class="w-20">SL</th>
                                            <th scope="col" class="w-24">Vát</th>
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
                                                <td>{{ $sizes['height'] ?? '—' }}</td>
                                                <td>{{ $sizes['width'] ?? '—' }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>{{ $item->bevel ?? '—' }}</td>
                                                <td>
                                                    @if(!empty($edgeGluing))
                                                        <span
                                                            class="text-xs bg-neutral-100 px-2 py-0.5 rounded text-neutral-600">{{ implode(', ', $edgeGluing) }}</span>
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
                                                        <iconify-icon icon="lucide:check-circle"
                                                            class="text-success-500 text-lg"></iconify-icon>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>{{ $item->direction ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="18" class="text-center text-neutral-400 py-4">Chưa có sản phẩm nào</td>
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
                                                <td class="text-end font-medium text-neutral-600">
                                                    {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                                <td class="text-end font-semibold text-neutral-800">
                                                    {{ number_format($item->total_price, 0, ',', '.') }}</td>
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
                                                        <td rowspan="{{ $rowCount }}" class="font-medium text-neutral-800 align-top pt-3">
                                                            {{ $item->product_name }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->thickness ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->height ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->width ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->bevel ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->grain_direction ?? '0' }}
                                                        </td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->wing_area ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->molding_length ?? '—' }}
                                                        </td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->edge_bevel ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">
                                                            {{ $item->vertical_grain_cnc ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}"
                                                            class="text-end font-medium text-neutral-600 align-top pt-3">
                                                            {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                                        <td rowspan="{{ $rowCount }}"
                                                            class="text-end font-semibold text-neutral-800 align-top pt-3">
                                                            {{ number_format($item->total_price, 0, ',', '.') }}</td>
                                                    @endif
                                                    <td>
                                                        @if($currentAction !== '—')
                                                            <span
                                                                class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $actionColor }}">
                                                                {{ $currentAction }}
                                                            </span>
                                                        @else
                                                            <span class="text-neutral-300 text-xs">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-xs text-neutral-500">
                                                        {{ $currentOperator !== '—' ? $currentOperator : '' }}</td>
                                                    @if($sheetIdx === 0)
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3"><span
                                                                class="text-neutral-500 text-xs">{{ $item->notes ?? '—' }}</span></td>
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

            @if(in_array($acrylicOrder->type, ['min_late', 'acrylic']) && isset($acrylicOrder->paymentDetails) && $acrylicOrder->paymentDetails->count() > 0)
                <div class="card p-0 rounded-xl border-0 overflow-hidden shadow-sm bg-white border-l-4 border-l-primary-500 mt-6">
                    <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                                <iconify-icon icon="lucide:receipt" class="text-base"></iconify-icon>
                            </div>
                            <h6 class="font-bold text-base text-neutral-800 m-0">Chi tiết hóa đơn dịch vụ</h6>
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
                                        @if($acrylicOrder->type === 'min_late')
                                            <th scope="col" class="w-32 text-end">Đơn giá chỉ</th>
                                        @endif
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
                                            <td class="text-end font-medium text-neutral-600">
                                                {{ number_format($detail->price, 0, ',', '.') }}</td>
                                            @if($acrylicOrder->type === 'min_late')
                                                <td class="text-end font-medium text-neutral-600">
                                                    {{ number_format($detail->price_only, 0, ',', '.') }}</td>
                                            @endif
                                            <td class="text-end font-bold text-primary-600">
                                                {{ number_format($detail->total, 0, ',', '.') }}</td>
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
                            {{ $acrylicOrder->supplies->flatMap(function ($s) use ($acrylicOrder) {
        if ($acrylicOrder->type === 'min_late')
            return $s->minLateItems;
        if ($acrylicOrder->type === 'glass')
            return $s->glassItems;
        return $s->items;
    })->sum(fn($i) => $i->quantity ?? $i->wing_quantity ?? 0) }}
                        </span>
                    </div>
                    
                    @php
                        $discountAmt = $acrylicOrder->discount_amount ?? 0;
                        $vatAmt = $acrylicOrder->vat_amount ?? 0;
                        $subTotal = $acrylicOrder->total_amount + $discountAmt - $vatAmt;
                    @endphp
                    
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-neutral-500">Tổng tiền hàng:</span>
                        <span class="font-semibold text-neutral-800">
                            {{ number_format(round($subTotal, -3), 0, ',', '.') }} VNĐ
                        </span>
                    </div>

                    @if($acrylicOrder->discount_percent > 0)
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-neutral-500">Chiết khấu ({{ (float)$acrylicOrder->discount_percent }}%):</span>
                        <span class="font-semibold text-danger-600">
                            -{{ number_format(round($discountAmt, -3), 0, ',', '.') }} VNĐ
                        </span>
                    </div>
                    @endif

                    @if($acrylicOrder->vat_percent > 0)
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-neutral-500">VAT ({{ (float)$acrylicOrder->vat_percent }}%):</span>
                        <span class="font-semibold text-neutral-800">
                            +{{ number_format(round($vatAmt, -3), 0, ',', '.') }} VNĐ
                        </span>
                    </div>
                    @endif

                    <div class="border-t border-neutral-100 pt-3 flex justify-between items-center">
                        <span class="text-base font-bold text-neutral-800">Tổng thanh toán:</span>
                        <span class="text-lg font-black text-primary-600" id="summary-total-amount">
                            {{ number_format(round($acrylicOrder->total_amount, -3), 0, ',', '.') }} VNĐ
                        </span>
                    </div>
                </div>
            </div>

            {{-- ===== CÔNG NỢ KHÁCH HÀNG ===== --}}
            @if($acrylicOrder->customer && !in_array($acrylicOrder->status, ['draft', 'pending', 'cancelled']))
            @php
                $customer = $acrylicOrder->customer;
                $debtSummary = $customer->debt_summary;
                $custTotalAmount = $debtSummary['total_amount'];
                $custTotalPaid = $debtSummary['total_paid'];
                $custTotalDebt = $debtSummary['total_debt'];
                $custInitialDebt = $customer->debt ?? 0;
                $total_combined_debt = $custTotalDebt + $custTotalPaid;
            @endphp
            <div class="card p-0 rounded-xl border-0 overflow-hidden shadow-sm bg-white">
                <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center gap-2">
                    <iconify-icon icon="lucide:wallet" class="text-xl text-violet-500"></iconify-icon>
                    <h6 class="font-bold text-base text-neutral-800 m-0 flex-1">Công nợ khách hàng</h6>
                </div>
                <div class="p-5 space-y-4">
                    <div class="text-xs text-neutral-500 font-medium">
                        Khách hàng: 
                        <a href="{{ route('customers.index', ['overview_id' => $customer->id]) }}" 
                           class="text-primary-600 hover:underline font-bold">
                            {{ $customer->name }} ({{ $customer->customer_code }})
                        </a>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-neutral-500 font-medium">Công nợ:</span>
                            <span class="font-semibold text-neutral-800">{{ number_format($total_combined_debt, 0, ',', '.') }} đ</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-neutral-500 font-medium">Đã thanh toán:</span>
                            <span class="font-semibold text-success-600">{{ number_format($custTotalPaid, 0, ',', '.') }} đ</span>
                        </div>
                        <hr class="border-neutral-100">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-neutral-800 font-bold">Tổng nợ hiện tại:</span>
                            <span class="text-lg font-extrabold text-danger-600">{{ number_format($custTotalDebt, 0, ',', '.') }} đ</span>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-neutral-100 text-center">
                        <a href="{{ route('customers.index', ['overview_id' => $customer->id]) }}" 
                           class="inline-flex items-center justify-center gap-2 py-2 px-4 w-full rounded-lg bg-violet-50 hover:bg-violet-100 text-violet-700 text-sm font-semibold transition-colors">
                            <iconify-icon icon="lucide:external-link" style="font-size:14px;"></iconify-icon>
                            Quản lý thanh toán khách hàng
                        </a>
                    </div>
                </div>
            </div>
            @endif

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
                                <div
                                    class="relative group border border-neutral-100 rounded-lg overflow-hidden shadow-xs hover:shadow-md transition-all">
                                    <a href="{{ route('orders.image', ['filename' => basename($image)]) }}" target="_blank"
                                        title="Xem ảnh gốc">
                                        <img src="{{ route('orders.image', ['filename' => basename($image)]) }}"
                                            class="w-full h-24 object-cover transition-transform group-hover:scale-105">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Navigation Actions --}}
            <div class="flex flex-col gap-3">
                <a href="{{ route('orders.index') }}"
                    class="w-full justify-center flex items-center gap-2 py-3 rounded-xl font-semibold border border-neutral-200 text-neutral-700 hover:bg-neutral-50 transition-colors shadow-sm text-sm bg-white cursor-pointer">
                    <iconify-icon icon="lucide:arrow-left" class="text-base"></iconify-icon> Quay lại danh sách
                </a>
                @can('edit order')
                    @if(!in_array($acrylicOrder->status, ['in_production', 'cancelled']))
                        <a href="{{ route('orders.edit', $acrylicOrder) }}"
                            class="w-full justify-center flex items-center gap-2 py-3 rounded-xl font-semibold bg-neutral-900 hover:bg-black text-white shadow-sm text-sm transition-colors cursor-pointer">
                            <iconify-icon icon="lucide:edit-3" class="text-base"></iconify-icon> Chỉnh sửa đơn hàng
                        </a>
                    @else
                        <button type="button" disabled
                            class="w-full justify-center flex items-center gap-2 py-3 rounded-xl font-semibold bg-neutral-300 text-neutral-500 shadow-sm text-sm cursor-not-allowed"
                            title="{{ $acrylicOrder->status === 'in_production' ? 'Đơn hàng đang sản xuất, không thể chỉnh sửa' : 'Đơn hàng đã bị hủy, không thể chỉnh sửa' }}">
                            <iconify-icon icon="lucide:edit-3" class="text-base"></iconify-icon> Chỉnh sửa đơn hàng
                        </button>
                    @endif
                @endcan
                <button type="button" onclick="exportToExcel()"
                    class="w-full justify-center flex items-center gap-2 py-3 rounded-xl font-semibold border border-neutral-200 text-neutral-700 hover:bg-neutral-50 transition-colors shadow-sm text-sm bg-white cursor-pointer">
                    <iconify-icon icon="lucide:file-spreadsheet" class="text-base"></iconify-icon> Xuất Excel (.xlsx)
                </button>
                @if($acrylicOrder->type === 'acrylic')
                    <button type="button" onclick="exportNestingFiles()"
                        class="w-full justify-center flex items-center gap-2 py-3 rounded-xl font-semibold border border-emerald-200 text-emerald-700 hover:bg-emerald-50 transition-colors shadow-sm text-sm bg-white cursor-pointer">
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
            'deadline' => $acrylicOrder->deadline ? \Carbon\Carbon::parse($acrylicOrder->deadline)->format('Y-m-d H:i:s') : null,
            'address' => $acrylicOrder->address,
            'notes' => $acrylicOrder->notes,
            'customer_policy' => $acrylicOrder->customer_policy,
            'discount_percent' => $acrylicOrder->discount_percent ?? 0,
            'discount_amount' => $acrylicOrder->discount_amount ?? 0,
            'vat_percent' => $acrylicOrder->vat_percent ?? 0,
            'vat_amount' => $acrylicOrder->vat_amount ?? 0,
            'total_amount' => round($acrylicOrder->total_amount, -3),
            'delivery_days' => $acrylicOrder->delivery_days ?? ($acrylicOrder->type === 'glass' ? 5 : 2),
            'customer_debt_info' => call_user_func(function() use ($acrylicOrder) {
                if (!$acrylicOrder->customer) return null;
                $thisOrderTotal = round($acrylicOrder->total_amount, -3);
                $thisOrderPaid = $acrylicOrder->orderPayments->sum('amount');
                $thisOrderUnpaid = 0;
                if (!in_array($acrylicOrder->status, ['draft', 'cancelled', 'pending'])) {
                    $thisOrderUnpaid = max(0, $thisOrderTotal - $thisOrderPaid);
                }
                $currentTotalDebt = $acrylicOrder->customer->total_debt;
                $oldDebt = max(0, $currentTotalDebt - $thisOrderUnpaid);
                $totalCombinedDebt = $oldDebt + $thisOrderTotal - $thisOrderPaid;
                
                $allOrdersPaid = $acrylicOrder->customer->debt_summary['total_paid'] ?? 0;
                
                if ($oldDebt == 0 && $allOrdersPaid == 0 && $totalCombinedDebt == 0) return null;
                
                return [
                    'old_debt' => $oldDebt,
                    'this_order_paid' => $allOrdersPaid, // still named this_order_paid in JS, but sends allOrdersPaid
                    'total_combined_debt' => max(0, $totalCombinedDebt),
                ];
            }),
            'supplies' => $acrylicOrder->supplies->map(function ($supply) use ($acrylicOrder) {
                $items = [];
                if ($acrylicOrder->type === 'min_late') {
                    $items = $supply->minLateItems->map(function ($item) {
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
                            'bevel' => $item->bevel,
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
                    $items = $supply->glassItems->map(function ($item) {
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
                    $items = $supply->items->map(function ($item) {
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
                            'offset_left' => $item->offset_left,
                            'offset_right' => $item->offset_right,
                            'offset_top' => $item->offset_top,
                            'offset_bottom' => $item->offset_bottom,
                            'mill_left' => $item->mill_left,
                            'mill_right' => $item->mill_right,
                            'mill_top' => $item->mill_top,
                            'mill_bottom' => $item->mill_bottom,
                            'mill_width' => $item->mill_width,
                            'mill_depth' => $item->mill_depth,
                            'mill_left_2' => $item->mill_left_2,
                            'mill_right_2' => $item->mill_right_2,
                            'mill_top_2' => $item->mill_top_2,
                            'mill_bottom_2' => $item->mill_bottom_2,
                            'mill_width_2' => $item->mill_width_2,
                            'mill_depth_2' => $item->mill_depth_2,
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
            'payment_details' => isset($acrylicOrder->paymentDetails) ? $acrylicOrder->paymentDetails->map(function ($detail) {
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
        (function () {
            const params = new URLSearchParams(window.location.search);
            if (params.get('export') === '1') {
                window.addEventListener('load', function () {
                    // Small delay to ensure exceljs + FileSaver are ready
                    setTimeout(function () {
                        if (typeof exportToExcel === 'function') {
                            exportToExcel();
                        }
                        // Navigate back to orders list after triggering download
                        setTimeout(function () {
                            window.history.back();
                        }, 800);
                    }, 400);
                });
            }
        })();
    </script>



@endsection