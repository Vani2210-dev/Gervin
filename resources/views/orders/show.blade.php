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
                                'draft' => 'bg-neutral-100 text-neutral-600 border border-neutral-200',
                                'pending' => 'bg-warning-100 text-warning-600 border border-warning-200',
                                'transferred' => 'bg-info-100 text-info-600 border border-info-200',
                                'in_production' => 'bg-indigo-100 text-indigo-600 border border-indigo-200',
                                'completed' => 'bg-success-100 text-success-600 border border-success-200',
                                'cancelled' => 'bg-danger-100 text-danger-600 border border-danger-200',
                            ];
                            $statusLabels = [
                                'draft' => 'Nháp',
                                'pending' => 'Chờ xử lý',
                                'transferred' => 'Chuyển sản xuất',
                                'in_production' => 'Đang sản xuất',
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
                    {{-- Liên kết đơn cha - con cho đơn sửa tấm/bổ sung/bảo hành --}}
                    @if($acrylicOrder->parent)
                        <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-2 text-sm text-amber-800">
                            <iconify-icon icon="lucide:link" class="text-base"></iconify-icon>
                            <span>Đơn hàng {{ $acrylicOrder->relation_type === 'rework' ? 'sửa tấm' : ($acrylicOrder->relation_type === 'additional' ? 'bổ sung' : ($acrylicOrder->relation_type === 'reuse' ? 'tận dụng tấm' : ($acrylicOrder->relation_type === 'warranty' ? 'bảo hành' : 'liên kết'))) }} từ đơn: 
                                <a href="{{ route('orders.show', $acrylicOrder->parent_id) }}" class="font-bold underline hover:text-amber-950">{{ $acrylicOrder->parent->order_code }}</a>
                            </span>
                        </div>
                    @endif

                    @if($acrylicOrder->children->filter(fn($c) => $c->status !== 'draft')->count() > 0)
                        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800 space-y-1">
                            <div class="flex items-center gap-2 font-semibold">
                                <iconify-icon icon="lucide:link-2" class="text-base"></iconify-icon>
                                <span>Đơn hàng này có các đơn liên kết:</span>
                            </div>
                            <ul class="list-disc pl-5">
                                @foreach($acrylicOrder->children->filter(fn($c) => $c->status !== 'draft') as $child)
                                    <li>
                                        <a href="{{ route('orders.show', $child->id) }}" class="font-bold underline hover:text-blue-950">{{ $child->order_code }}</a> 
                                        - Phân loại: <strong class="text-primary-700">{{ $child->relation_type === 'rework' ? 'Sửa tấm' : ($child->relation_type === 'additional' ? 'Bổ sung' : ($child->relation_type === 'reuse' ? 'Tận dụng tấm' : ($child->relation_type === 'warranty' ? 'Bảo hành' : 'Khác'))) }}</strong> 
                                        ({{ $child->status === 'pending' ? 'Chờ xử lý' : ($child->status === 'transferred' ? 'Chuyển sản xuất' : $child->status) }})
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Trạng thái trả ván cho đơn bổ sung --}}
                    @if($acrylicOrder->relation_type === 'additional')
                        @if($acrylicOrder->board_return_status === 'pending')
                            <div class="mb-4 p-4 bg-amber-50 border border-amber-300 rounded-xl flex items-center justify-between gap-4 text-amber-900">
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <iconify-icon icon="lucide:alert-circle" class="text-2xl text-amber-600 shrink-0"></iconify-icon>
                                    <div>
                                        <div class="font-bold text-sm">Khách chưa hoàn trả ván cũ</div>
                                        <div class="text-xs text-amber-700">Đơn bổ sung này đang được tạm tính công nợ ({{ number_format(round($acrylicOrder->total_amount, -3), 0, ',', '.') }} VNĐ). Khi nhận lại ván cũ, vui lòng bấm xác nhận để trừ công nợ.</div>
                                    </div>
                                </div>
                                <form action="{{ route('orders.confirm-board-return', $acrylicOrder) }}" method="POST" class="shrink-0" onsubmit="return confirm('Bạn có chắc chắn khách hàng đã hoàn trả ván cũ? Hệ thống sẽ cấn trừ công nợ của đơn hàng này.');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm bg-success-600 hover:bg-success-700 text-white rounded-lg flex items-center gap-1.5 shadow-sm font-semibold text-sm px-3.5 py-2 transition-colors whitespace-nowrap">
                                        <iconify-icon icon="lucide:check-circle-2" class="text-base"></iconify-icon>
                                        <span>Xác nhận đã trả ván</span>
                                    </button>
                                </form>
                            </div>
                        @elseif($acrylicOrder->board_return_status === 'returned')
                            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center gap-2 text-sm text-emerald-800">
                                <iconify-icon icon="lucide:check-circle" class="text-base text-emerald-600"></iconify-icon>
                                <span><strong>Đã xác nhận trả ván:</strong> Đơn bổ sung này đã được cấn trừ công nợ.</span>
                            </div>
                        @endif
                    @endif

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
                                SL: {{ floatval($supply->quantity) == intval($supply->quantity) ? number_format($supply->quantity, 0, ',', '.') : number_format($supply->quantity, 2, ',', '.') }}
                            </span>
                        @endif
                    </div>

                    <div class="p-6">
                        <div class="overflow-x-auto">
                            @if($acrylicOrder->type === 'min_late')
                                {{-- Min Late items - expand to individual sheets with status --}}
                                <table class="table bordered-table sm-table mb-0 min-w-[1900px]">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="w-10 text-center">STT</th>
                                            <th scope="col" class="w-32">Mã tấm</th>
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
                                            <th scope="col" class="w-36">Giai đoạn</th>
                                            <th scope="col" class="w-28">Người thực hiện</th>
                                            <th scope="col" class="w-44">Ghi chú</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $globalMinLateIndex = 0; @endphp
                                        @forelse($supply->minLateItems as $item)
                                            @php
                                                $sizes = $item->size ?? [];
                                                if (is_string($sizes)) {
                                                    $sizes = json_decode($sizes, true) ?? [];
                                                }
                                                $edgeGluing = $item->edge_gluing ?? [];
                                                if (is_string($edgeGluing)) {
                                                    $edgeGluing = json_decode($edgeGluing, true) ?? [];
                                                }
                                                $codes = $item->codes;
                                                $hasAnyCode = $codes->count() > 0;
                                                $rowCount = $hasAnyCode ? $codes->count() : 1;
                                            @endphp
                                            @for($sheetIdx = 0; $sheetIdx < $rowCount; $sheetIdx++)
                                                @php
                                                    $globalMinLateIndex++;
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
                                                    <td class="text-center font-semibold text-neutral-500">{{ $globalMinLateIndex }}</td>
                                                    <td><span class="text-neutral-500 text-xs font-mono">{{ $productId }}</span></td>
                                                    @if($sheetIdx === 0)
                                                        <td rowspan="{{ $rowCount }}" class="font-medium text-neutral-800 align-top pt-3">{{ $item->name }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->thickness ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $sizes['height'] ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $sizes['width'] ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3 font-medium">{{ $item->quantity }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->bevel ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">
                                                            @if(!empty($edgeGluing))
                                                                <span class="text-xs bg-neutral-100 px-2 py-0.5 rounded text-neutral-600">{{ implode(', ', $edgeGluing) }}</span>
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->straight_paste_length ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->beveled_length ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->vat_moi_length ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->ban_rong_40_59 ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->call_rong_17_39 ?? $item->ban_rong_17_39 ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->ban_rong_25_35 ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->beveled_handle ?? '—' }}</td>
                                                        <td rowspan="{{ $rowCount }}" class="text-center align-top pt-3">
                                                            @if($item->cnc)
                                                                <iconify-icon icon="lucide:check-circle" class="text-success-500 text-lg"></iconify-icon>
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->direction ?? '—' }}</td>
                                                    @endif
                                                    <td>
                                                        @if($currentAction !== '—')
                                                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $actionColor }}">
                                                                {{ $currentAction }}
                                                            </span>
                                                        @else
                                                            <span class="text-neutral-400 text-xs">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="text-neutral-600 text-xs">{{ $currentOperator }}</span>
                                                    </td>
                                                    @if($sheetIdx === 0)
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3"><span class="text-neutral-500 text-xs">{{ $item->notes ?? '—' }}</span></td>
                                                    @endif
                                                </tr>
                                            @endfor
                                        @empty
                                            <tr>
                                                <td colspan="21" class="text-center text-neutral-400 py-4">Chưa có sản phẩm nào</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            @elseif($acrylicOrder->type === 'glass')
                                @php
                                    $isAccessory = ($supply->supply_name === 'Phụ kiện');
                                @endphp
                                @if($isAccessory)
                                    {{-- Bảng chi tiết Phụ kiện --}}
                                    <table class="table bordered-table sm-table mb-0 w-full text-xs">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="w-12 text-center">STT</th>
                                                <th scope="col" class="w-32">Mã SP</th>
                                                <th scope="col" class="w-64">Tên SP</th>
                                                <th scope="col" class="w-24 text-center">Số lượng</th>
                                                <th scope="col" class="w-28 text-end">Đơn giá</th>
                                                <th scope="col" class="w-28 text-end">Thành tiền</th>
                                                <th scope="col" class="w-44">Ghi chú</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($supply->glassItems as $itemIndex => $item)
                                                <tr>
                                                    <td class="text-center">{{ $itemIndex + 1 }}</td>
                                                    <td><span class="text-neutral-500 text-xs font-semibold">{{ $item->product_code ?? '—' }}</span></td>
                                                    <td><span class="font-medium text-neutral-800">{{ $item->product_name }}</span></td>
                                                    <td class="text-center font-medium">{{ floatval($item->wing_quantity) == intval($item->wing_quantity) ? number_format($item->wing_quantity, 0, ',', '.') : number_format($item->wing_quantity, 2, ',', '.') }}</td>
                                                    <td class="text-end font-medium text-neutral-600">
                                                        {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                                    <td class="text-end font-semibold text-neutral-800">
                                                        {{ number_format($item->total_price, 0, ',', '.') }}</td>
                                                    <td><span class="text-neutral-500 text-xs">{{ $item->notes ?? '—' }}</span></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-neutral-400 py-4">Chưa có sản phẩm nào</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                @else
                                    {{-- Glass items - expand to individual sheets with status --}}
                                    <table class="table bordered-table sm-table mb-0 min-w-[1800px]">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="w-10 text-center">STT</th>
                                                <th scope="col" class="w-32">Mã cánh</th>
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
                                                <th scope="col" class="w-36">Giai đoạn</th>
                                                <th scope="col" class="w-28">Người thực hiện</th>
                                                <th scope="col" class="w-44">Ghi chú</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $globalGlassIndex = 0; @endphp
                                            @forelse($supply->glassItems as $item)
                                                @php
                                                    $codes = $item->codes;
                                                    $hasAnyCode = $codes->count() > 0;
                                                    $rowCount = $hasAnyCode ? $codes->count() : 1;
                                                @endphp
                                                @for($sheetIdx = 0; $sheetIdx < $rowCount; $sheetIdx++)
                                                    @php
                                                        $globalGlassIndex++;
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
                                                        <td class="text-center font-semibold text-neutral-500">{{ $globalGlassIndex }}</td>
                                                        <td><span class="text-neutral-500 text-xs font-mono">{{ $productId }}</span></td>
                                                        @if($sheetIdx === 0)
                                                            <td rowspan="{{ $rowCount }}" class="font-medium text-neutral-800 align-top pt-3">{{ $item->product_name }}</td>
                                                            <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->thickness ?? '—' }}</td>
                                                            <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->wing_opening_direction ?? '—' }}</td>
                                                            <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->aluminum_color ?? '—' }}</td>
                                                            <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->glass_color ?? '—' }}</td>
                                                            <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->height ?? '—' }}</td>
                                                            <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->width ?? '—' }}</td>
                                                            <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->unit ?? 'Bộ' }}</td>
                                                            <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->wing_quantity }}</td>
                                                            <td rowspan="{{ $rowCount }}" class="align-top pt-3">{{ $item->area_m2 ?? '—' }}</td>
                                                            <td rowspan="{{ $rowCount }}" class="text-end font-medium text-neutral-600 align-top pt-3">
                                                                {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                                            <td rowspan="{{ $rowCount }}" class="text-end font-semibold text-neutral-800 align-top pt-3">
                                                                {{ number_format($item->total_price, 0, ',', '.') }}</td>
                                                        @endif
                                                        <td>
                                                            @if($currentAction !== '—')
                                                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $actionColor }}">
                                                                    {{ $currentAction }}
                                                                </span>
                                                            @else
                                                                <span class="text-neutral-400 text-xs">—</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="text-neutral-600 text-xs">{{ $currentOperator }}</span>
                                                        </td>
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
                                            <th scope="col" class="w-20">SL</th>
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
                                                        <td rowspan="{{ $rowCount }}" class="align-top pt-3 font-medium">{{ floatval($item->quantity) == intval($item->quantity) ? number_format($item->quantity, 0, ',', '.') : number_format($item->quantity, 2, ',', '.') }}</td>
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

            @if(in_array($acrylicOrder->type, ['min_late', 'acrylic', 'glass']) && isset($acrylicOrder->paymentDetails) && $acrylicOrder->paymentDetails->count() > 0)
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
                                            <td class="text-center font-medium">{{ floatval($detail->quantity) == intval($detail->quantity) ? number_format($detail->quantity, 0, ',', '.') : number_format($detail->quantity, 2, ',', '.') }}</td>
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
                @can('add order')
                    <div class="flex flex-col gap-2 w-full">
                        <button type="button" onclick="openReworkModal()"
                            class="w-full justify-center flex items-center gap-2 py-3 rounded-xl font-semibold bg-amber-500 hover:bg-amber-600 text-white shadow-sm text-sm transition-colors cursor-pointer">
                            <iconify-icon icon="lucide:rotate-ccw" class="text-base"></iconify-icon> Tạo đơn sửa tấm
                        </button>
                        <button type="button" onclick="openWarrantyModal()"
                            class="w-full justify-center flex items-center gap-2 py-3 rounded-xl font-semibold bg-purple-600 hover:bg-purple-700 text-white shadow-sm text-sm transition-colors cursor-pointer">
                            <iconify-icon icon="lucide:shield-check" class="text-base"></iconify-icon> Tạo đơn bảo hành
                        </button>
                        @if($acrylicOrder->type === 'acrylic')
                            <a href="{{ route('orders.reuse-create', $acrylicOrder->id) }}"
                                class="w-full justify-center flex items-center gap-2 py-3 rounded-xl font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm text-sm transition-colors cursor-pointer">
                                <iconify-icon icon="lucide:layers" class="text-base"></iconify-icon> Tạo đơn tận dụng tấm
                            </a>
                        @endif
                        <button type="button" onclick="openChooseAdditionalModal()"
                            class="w-full justify-center flex items-center gap-2 py-3 rounded-xl font-semibold bg-success-600 hover:bg-success-700 text-white shadow-sm text-sm transition-colors cursor-pointer">
                            <iconify-icon icon="lucide:plus-circle" class="text-base"></iconify-icon> Tạo đơn bổ sung
                        </button>
                        @if($acrylicOrder->relation_type === 'rework')
                            <a href="{{ route('orders.print-handwritten', $acrylicOrder->id) }}" target="_blank"
                                class="w-full justify-center flex items-center gap-2 py-3 rounded-xl font-semibold bg-amber-600 hover:bg-amber-700 text-white shadow-sm text-sm transition-colors cursor-pointer">
                                <iconify-icon icon="lucide:printer" class="text-base"></iconify-icon> In lệnh viết tay
                            </a>
                        @endif
                    </div>
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
        // $exportData is now passed from OrderController
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

        // Tự động mở modal sửa tấm / bảo hành / bổ sung nếu url có ?open_rework=1 hoặc ?open_warranty=1 hoặc ?open_additional=1
        (function () {
            const params = new URLSearchParams(window.location.search);
            if (params.get('open_rework') === '1') {
                window.addEventListener('load', function () {
                    setTimeout(function () {
                        if (typeof openReworkModal === 'function') {
                            openReworkModal();
                        }
                    }, 200);
                });
            } else if (params.get('open_warranty') === '1') {
                window.addEventListener('load', function () {
                    setTimeout(function () {
                        if (typeof openWarrantyModal === 'function') {
                            openWarrantyModal();
                        }
                    }, 200);
                });
            } else if (params.get('open_additional') === '1') {
                window.addEventListener('load', function () {
                    setTimeout(function () {
                        if (typeof openChooseAdditionalModal === 'function') {
                            openChooseAdditionalModal();
                        }
                    }, 200);
                });
            }
        })();
    </script>

    {{-- Modal chọn hình thức tạo đơn bổ sung (2 Options) --}}
    <x-modal name="modal-choose-additional-type" maxWidth="md" :hasBackdrop="true">
        <style>
            .choose-additional-options {
                display: flex;
                flex-direction: column;
                gap: 16px;
            }
            .choose-additional-card {
                padding: 16px 18px;
            }
        </style>
        <div class="p-6">
            <div class="flex items-center justify-between pb-3 border-b border-neutral-100 mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-success-50 rounded-lg text-success-600 flex items-center justify-center">
                        <iconify-icon icon="lucide:plus-circle" class="text-xl"></iconify-icon>
                    </div>
                    <h5 class="font-bold text-lg text-neutral-800 m-0">Tạo đơn bổ sung</h5>
                </div>
                <button type="button" onclick="closeModal('modal-choose-additional-type')" class="text-neutral-400 hover:text-neutral-600 p-1.5 rounded-lg transition-colors">
                    <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                </button>
            </div>

            <p class="text-xs text-neutral-500 mb-4 font-medium">Vui lòng chọn hình thức tạo đơn bổ sung:</p>

            <div class="choose-additional-options">
                {{-- Option 1: Đơn bổ sung thông thường (form trống) --}}
                <a href="{{ route('orders.additional-create', $acrylicOrder->id) }}" class="choose-additional-card flex items-center gap-4 rounded-xl border border-neutral-200 hover:border-success-500 hover:bg-success-50/40 transition-all group cursor-pointer block text-left shadow-sm hover:shadow">
                    <div class="w-11 h-11 bg-neutral-100 group-hover:bg-success-100 text-neutral-600 group-hover:text-success-700 rounded-xl transition-colors shrink-0 flex items-center justify-center">
                        <iconify-icon icon="lucide:file-plus" class="text-xl"></iconify-icon>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-sm text-neutral-800 group-hover:text-success-700 transition-colors">
                            Đơn bổ sung thông thường
                        </div>
                        <p class="text-xs text-neutral-500 mt-1 leading-normal mb-0">
                            Nhập quy cách, kích thước mới phát sinh cho khách hàng (form tạo đơn trống).
                        </p>
                    </div>
                    <div class="shrink-0 text-neutral-300 group-hover:text-success-600 group-hover:translate-x-0.5 transition-all">
                        <iconify-icon icon="lucide:chevron-right" class="text-lg"></iconify-icon>
                    </div>
                </a>

                {{-- Option 2: Đơn bổ sung đổi tấm / Khách giữ ván (chọn từ đơn gốc) --}}
                <button type="button" onclick="chooseAdditionalFromExisting()" class="choose-additional-card w-full flex items-center gap-4 rounded-xl border border-neutral-200 hover:border-amber-500 hover:bg-amber-50/40 transition-all group cursor-pointer text-left bg-white shadow-sm hover:shadow">
                    <div class="w-11 h-11 bg-neutral-100 group-hover:bg-amber-100 text-neutral-600 group-hover:text-amber-700 rounded-xl transition-colors shrink-0 flex items-center justify-center">
                        <iconify-icon icon="lucide:package" class="text-xl"></iconify-icon>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-sm text-neutral-800 group-hover:text-amber-700 transition-colors">
                            Đổi tấm / Khách đang giữ ván cũ
                        </div>
                        <p class="text-xs text-neutral-500 mt-1 leading-normal mb-0">
                            Chọn tấm từ đơn gốc để sản xuất lại (tạm tính công nợ khi khách chưa mang trả ván cũ).
                        </p>
                    </div>
                    <div class="shrink-0 text-neutral-300 group-hover:text-amber-600 group-hover:translate-x-0.5 transition-all">
                        <iconify-icon icon="lucide:chevron-right" class="text-lg"></iconify-icon>
                    </div>
                </button>
            </div>
        </div>
    </x-modal>

    {{-- Modal chọn sản phẩm để tạo đơn sửa tấm / đơn bảo hành / đơn bổ sung --}}
    <x-modal name="modal-rework-order" maxWidth="3xl" :hasBackdrop="true">
        <style>
            #modal-rework-order [data-modal-content] {
                width: 95% !important;
                max-width: 95% !important;
                max-height: 95vh !important;
            }
        </style>
        <div class="p-6">
            <div class="flex items-center justify-between pb-3 border-b border-neutral-100">
                <h5 id="modal-select-items-title" class="font-bold text-lg text-neutral-800 m-0">Tạo đơn sửa tấm</h5>
                <button type="button" onclick="closeModal('modal-rework-order')" class="text-neutral-400 hover:text-neutral-600 p-1.5 rounded-lg transition-colors">
                    <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                </button>
            </div>

            <form id="rework-order-form" onsubmit="submitItemSelectionOrder(event)" class="mt-4 space-y-4">
                @csrf
                <div id="modal-select-items-desc" class="text-sm text-neutral-500">
                    Tích chọn những tấm bị lỗi từ đơn gốc để sản xuất lại.
                </div>

                <div class="overflow-y-auto overflow-x-auto border border-neutral-200 rounded-lg" style="max-height: calc(95vh - 220px);">
                    @if($acrylicOrder->type === 'min_late')
                        {{-- Bảng cho đơn Melamine/Laminate --}}
                        <table class="table bordered-table sm-table mb-0 w-full text-xs min-w-[1400px]">
                            <thead class="bg-neutral-50 sticky top-0 z-10">
                                <tr>
                                    <th scope="col" class="w-10 text-center py-2 px-3">
                                        <input type="checkbox" onchange="toggleSelectAllReworkItems(this)" class="rounded text-primary-600 focus:ring-primary-500">
                                    </th>
                                    <th scope="col" class="w-12 text-center py-2 px-3">STT</th>
                                    <th scope="col" class="w-32 text-left py-2 px-3">Mã tấm</th>
                                    <th scope="col" class="text-left py-2 px-3">Tên sản phẩm</th>
                                    <th scope="col" class="w-20 text-center py-2 px-3">Độ dày</th>
                                    <th scope="col" class="w-20 text-center py-2 px-3">Cao</th>
                                    <th scope="col" class="w-20 text-center py-2 px-3">Rộng</th>
                                    <th scope="col" class="w-20 text-center py-2 px-3">SL</th>
                                    <th scope="col" class="w-20 text-center py-2 px-3">Vát</th>
                                    <th scope="col" class="w-28 text-center py-2 px-3">Dán cạnh</th>
                                    <th scope="col" class="w-24 text-center py-2 px-3">Dán thẳng</th>
                                    <th scope="col" class="w-24 text-center py-2 px-3">Dán vát</th>
                                    <th scope="col" class="w-24 text-center py-2 px-3">Vát mòi</th>
                                    <th scope="col" class="w-28 text-center py-2 px-3">Bản rộng 40-59</th>
                                    <th scope="col" class="w-28 text-center py-2 px-3">Bản rộng 17-39</th>
                                    <th scope="col" class="w-28 text-center py-2 px-3">Bản rộng 25-35</th>
                                    <th scope="col" class="w-28 text-center py-2 px-3">Tay nắm vát</th>
                                    <th scope="col" class="w-20 text-center py-2 px-3">CNC</th>
                                    <th scope="col" class="w-24 text-center py-2 px-3">Chiều vân</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($acrylicOrder->supplies as $supply)
                                    @php
                                        $filteredItems = $supply->minLateItems->filter(function($item) {
                                            return $item->product_name !== 'Công giả dày';
                                        });
                                    @endphp
                                    @if($filteredItems->count() > 0)
                                        <tr class="bg-neutral-100 font-semibold border-b border-neutral-200">
                                            <td colspan="19" class="py-2 px-4 text-neutral-700 bg-neutral-100/80 font-bold text-xs">
                                                <iconify-icon icon="lucide:package" class="align-middle mr-1 text-primary-500"></iconify-icon>
                                                Vật tư: {{ $supply->order_supply_code ? '[' . $supply->order_supply_code . '] ' : '' }}{{ $supply->supply_name }}
                                                @if($supply->quantity)
                                                    <span class="text-[10px] font-normal text-neutral-500 ml-2">(SL: {{ floatval($supply->quantity) }})</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @foreach($filteredItems as $itemIndex => $item)
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
                                            <tr class="border-b border-neutral-100 hover:bg-neutral-50/50">
                                                <td class="text-center py-2.5 px-3">
                                                    <input type="checkbox" data-item-id="{{ $item->id }}" class="rework-item-checkbox rounded text-primary-600 focus:ring-primary-500">
                                                </td>
                                                <td class="text-center text-neutral-500 py-2.5 px-3">{{ $itemIndex + 1 }}</td>
                                                <td class="py-2.5 px-3 font-semibold text-neutral-600 text-left font-mono">
                                                    @if($item->codes->count() > 0)
                                                        {{ $item->codes->pluck('product_id')->implode(', ') }}
                                                    @else
                                                        {{ $item->product_code ?? '—' }}
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 font-medium text-neutral-800 text-left">{{ $item->name }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->thickness ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $sizes['height'] ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $sizes['width'] ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3 font-medium">{{ $item->quantity }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->bevel ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">
                                                    @if(!empty($edgeGluing))
                                                        <span class="text-xs bg-neutral-100 px-2 py-0.5 rounded text-neutral-600">{{ implode(', ', $edgeGluing) }}</span>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->straight_paste_length ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->beveled_length ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->vat_moi_length ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->ban_rong_40_59 ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->ban_rong_17_39 ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->ban_rong_25_35 ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->beveled_handle ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">
                                                    @if($item->cnc)
                                                        <span class="text-success-600 font-bold"><iconify-icon icon="lucide:check"></iconify-icon></span>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->direction ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @elseif($acrylicOrder->type === 'glass')
                        {{-- Bảng cho đơn Kính --}}
                        <table class="table bordered-table sm-table mb-0 w-full text-xs min-w-[1300px]">
                            <thead class="bg-neutral-50 sticky top-0 z-10">
                                <tr>
                                    <th scope="col" class="w-10 text-center py-2 px-3">
                                        <input type="checkbox" onchange="toggleSelectAllReworkItems(this)" class="rounded text-primary-600 focus:ring-primary-500">
                                    </th>
                                    <th scope="col" class="w-12 text-center py-2 px-3">STT</th>
                                    <th scope="col" class="w-32 text-left py-2 px-3">Mã cánh</th>
                                    <th scope="col" class="text-left py-2 px-3">Tên sản phẩm</th>
                                    <th scope="col" class="w-20 text-center py-2 px-3">Độ dày</th>
                                    <th scope="col" class="w-28 text-center py-2 px-3">Chiều mở cánh</th>
                                    <th scope="col" class="w-28 text-center py-2 px-3">Màu nhôm</th>
                                    <th scope="col" class="w-28 text-center py-2 px-3">Màu kính</th>
                                    <th scope="col" class="w-20 text-center py-2 px-3">Dài</th>
                                    <th scope="col" class="w-20 text-center py-2 px-3">Rộng</th>
                                    <th scope="col" class="w-20 text-center py-2 px-3">Đơn vị</th>
                                    <th scope="col" class="w-24 text-center py-2 px-3">SL cánh</th>
                                    <th scope="col" class="w-28 text-center py-2 px-3">Khối lượng (m2)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($acrylicOrder->supplies as $supply)
                                    @php
                                        $filteredItems = $supply->glassItems;
                                    @endphp
                                    @if($filteredItems->count() > 0)
                                        <tr class="bg-neutral-100 font-semibold border-b border-neutral-200">
                                            <td colspan="13" class="py-2 px-4 text-neutral-700 bg-neutral-100/80 font-bold text-xs">
                                                <iconify-icon icon="lucide:package" class="align-middle mr-1 text-primary-500"></iconify-icon>
                                                Vật tư: {{ $supply->order_supply_code ? '[' . $supply->order_supply_code . '] ' : '' }}{{ $supply->supply_name }}
                                                @if($supply->quantity)
                                                    <span class="text-[10px] font-normal text-neutral-500 ml-2">(SL: {{ floatval($supply->quantity) }})</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @foreach($filteredItems as $itemIndex => $item)
                                            <tr class="border-b border-neutral-100 hover:bg-neutral-50/50">
                                                <td class="text-center py-2.5 px-3">
                                                    <input type="checkbox" data-item-id="{{ $item->id }}" class="rework-item-checkbox rounded text-primary-600 focus:ring-primary-500">
                                                </td>
                                                <td class="text-center text-neutral-500 py-2.5 px-3">{{ $itemIndex + 1 }}</td>
                                                <td class="py-2.5 px-3 font-semibold text-neutral-600 text-left font-mono">
                                                    @if($item->codes->count() > 0)
                                                        {{ $item->codes->pluck('product_id')->implode(', ') }}
                                                    @else
                                                        {{ $item->product_code ?? '—' }}
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 font-medium text-neutral-800 text-left">{{ $item->product_name }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->thickness ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->wing_opening_direction ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->aluminum_color ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->glass_color ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->height ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->width ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->unit ?? 'Bộ' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3 font-medium">{{ $item->wing_quantity }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->area_m2 ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        {{-- Bảng cho đơn Acrylic (mặc định) --}}
                        <table class="table bordered-table sm-table mb-0 w-full text-xs min-w-[1300px]">
                            <thead class="bg-neutral-50 sticky top-0 z-10">
                                <tr>
                                    <th scope="col" class="w-10 text-center py-2 px-3">
                                        <input type="checkbox" onchange="toggleSelectAllReworkItems(this)" class="rounded text-primary-600 focus:ring-primary-500">
                                    </th>
                                    <th scope="col" class="w-12 text-center py-2 px-3">STT</th>
                                    <th scope="col" class="w-32 text-left py-2 px-3">Mã tấm</th>
                                    <th scope="col" class="text-left py-2 px-3">Tên sản phẩm</th>
                                    <th scope="col" class="w-20 text-center py-2 px-3">Độ dày</th>
                                    <th scope="col" class="w-20 text-center py-2 px-3">Cao</th>
                                    <th scope="col" class="w-20 text-center py-2 px-3">Rộng</th>
                                    <th scope="col" class="w-20 text-center py-2 px-3">SL</th>
                                    <th scope="col" class="w-24 text-center py-2 px-3">Vát</th>
                                    <th scope="col" class="w-24 text-center py-2 px-3">Chiều vân</th>
                                    <th scope="col" class="w-24 text-center py-2 px-3">Cánh (m2)</th>
                                    <th scope="col" class="w-24 text-center py-2 px-3">Phào (m)</th>
                                    <th scope="col" class="w-24 text-center py-2 px-3">Cạnh Vát</th>
                                    <th scope="col" class="w-28 text-center py-2 px-3">Vân dọc CNC</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($acrylicOrder->supplies as $supply)
                                    @php
                                        $filteredItems = $supply->items->filter(function($item) {
                                            return $item->product_name !== 'Công giả dày';
                                        });
                                    @endphp
                                    @if($filteredItems->count() > 0)
                                        <tr class="bg-neutral-100 font-semibold border-b border-neutral-200">
                                            <td colspan="14" class="py-2 px-4 text-neutral-700 bg-neutral-100/80 font-bold text-xs">
                                                <iconify-icon icon="lucide:package" class="align-middle mr-1 text-primary-500"></iconify-icon>
                                                Vật tư: {{ $supply->order_supply_code ? '[' . $supply->order_supply_code . '] ' : '' }}{{ $supply->supply_name }}
                                                @if($supply->quantity)
                                                    <span class="text-[10px] font-normal text-neutral-500 ml-2">(SL: {{ floatval($supply->quantity) }})</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @foreach($filteredItems as $itemIndex => $item)
                                            <tr class="border-b border-neutral-100 hover:bg-neutral-50/50">
                                                <td class="text-center py-2.5 px-3">
                                                    <input type="checkbox" data-item-id="{{ $item->id }}" class="rework-item-checkbox rounded text-primary-600 focus:ring-primary-500">
                                                </td>
                                                <td class="text-center text-neutral-500 py-2.5 px-3">{{ $itemIndex + 1 }}</td>
                                                <td class="py-2.5 px-3 font-semibold text-neutral-600 text-left font-mono">
                                                    @if($item->codes->count() > 0)
                                                        {{ $item->codes->pluck('product_id')->implode(', ') }}
                                                    @else
                                                        {{ $item->product_code ?? '—' }}
                                                    @endif
                                                </td>
                                                <td class="py-2.5 px-3 font-medium text-neutral-800 text-left">{{ $item->product_name }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->thickness ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->height ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->width ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3 font-medium">{{ floatval($item->quantity) == intval($item->quantity) ? number_format($item->quantity, 0, ',', '.') : number_format($item->quantity, 2, ',', '.') }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->bevel ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->grain_direction ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->wing_area ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->molding_length ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">{{ $item->edge_bevel ?? '—' }}</td>
                                                <td class="text-center text-neutral-600 py-2.5 px-3">
                                                    @if($item->vertical_grain_cnc)
                                                        <span class="text-success-600 font-bold"><iconify-icon icon="lucide:check"></iconify-icon></span>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <div class="pt-4 border-t border-neutral-100 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModal('modal-rework-order')" class="btn border border-neutral-200 text-neutral-700 font-semibold px-5 py-2.5 rounded-lg text-sm transition-colors hover:bg-neutral-50">Hủy</button>
                    <button type="submit" id="submit-rework-btn" class="btn btn-primary font-semibold px-5 py-2.5 rounded-lg text-sm flex items-center gap-2">
                        Tạo đơn sửa
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <script>
        let currentItemSelectionMode = 'rework'; // 'rework', 'warranty', hoặc 'additional'

        function toggleSelectAllReworkItems(headerCheckbox) {
            const checkboxes = document.querySelectorAll('.rework-item-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = headerCheckbox.checked;
            });
        }

        function openChooseAdditionalModal() {
            openModal('modal-choose-additional-type');
        }

        function chooseAdditionalFromExisting() {
            closeModal('modal-choose-additional-type');
            setTimeout(function() {
                openAdditionalModal();
            }, 150);
        }

        function openReworkModal() {
            currentItemSelectionMode = 'rework';
            const titleEl = document.getElementById('modal-select-items-title');
            const descEl = document.getElementById('modal-select-items-desc');
            const btnEl = document.getElementById('submit-rework-btn');
            
            if (titleEl) titleEl.textContent = 'Tạo đơn sửa tấm';
            if (descEl) descEl.textContent = 'Tích chọn những tấm bị lỗi từ đơn gốc để sản xuất lại.';
            if (btnEl) {
                btnEl.className = 'btn btn-primary font-semibold px-5 py-2.5 rounded-lg text-sm flex items-center gap-2';
                btnEl.textContent = 'Tạo đơn sửa';
            }
            openModal('modal-rework-order');
        }

        function openWarrantyModal() {
            currentItemSelectionMode = 'warranty';
            const titleEl = document.getElementById('modal-select-items-title');
            const descEl = document.getElementById('modal-select-items-desc');
            const btnEl = document.getElementById('submit-rework-btn');
            
            if (titleEl) titleEl.textContent = 'Tạo đơn bảo hành';
            if (descEl) descEl.textContent = 'Tích chọn những tấm cần bảo hành từ đơn gốc.';
            if (btnEl) {
                btnEl.className = 'btn bg-purple-600 hover:bg-purple-700 text-white font-semibold px-5 py-2.5 rounded-lg text-sm flex items-center gap-2 shadow-sm';
                btnEl.textContent = 'Tạo đơn bảo hành';
            }
            openModal('modal-rework-order');
        }

        function openAdditionalModal() {
            currentItemSelectionMode = 'additional';
            const titleEl = document.getElementById('modal-select-items-title');
            const descEl = document.getElementById('modal-select-items-desc');
            const btnEl = document.getElementById('submit-rework-btn');
            
            if (titleEl) titleEl.textContent = 'Tạo đơn bổ sung (Đổi tấm / Giữ ván)';
            if (descEl) descEl.textContent = 'Tích chọn những tấm cần sản xuất lại từ đơn gốc để đưa vào đơn bổ sung.';
            if (btnEl) {
                btnEl.className = 'btn bg-success-600 hover:bg-success-700 text-white font-semibold px-5 py-2.5 rounded-lg text-sm flex items-center gap-2 shadow-sm';
                btnEl.textContent = 'Tạo đơn bổ sung';
            }
            openModal('modal-rework-order');
        }

        function submitItemSelectionOrder(e) {
            e.preventDefault();
            const checkedBoxes = document.querySelectorAll('.rework-item-checkbox:checked');
            const mode = currentItemSelectionMode;
            const label = mode === 'warranty' ? 'bảo hành' : (mode === 'additional' ? 'bổ sung' : 'sửa');

            if (checkedBoxes.length === 0) {
                alert(`Vui lòng chọn ít nhất một tấm cần ${label}.`);
                return;
            }

            const submitBtn = document.getElementById('submit-rework-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<iconify-icon icon="lucide:loader" class="animate-spin text-base"></iconify-icon> Đang tạo...';

            const itemIds = [];
            checkedBoxes.forEach(cb => {
                itemIds.push(parseInt(cb.dataset.itemId));
            });

            let targetUrl = '{{ route("orders.create-rework", $acrylicOrder->id) }}';
            let payload = { item_ids: itemIds };

            if (mode === 'warranty') {
                targetUrl = '{{ route("orders.create-warranty", $acrylicOrder->id) }}';
            } else if (mode === 'additional') {
                targetUrl = '{{ route("orders.create-additional", $acrylicOrder->id) }}';
                payload.board_return_status = 'pending';
            }

            fetch(targetUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.ok && data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    alert(data.error || `Có lỗi xảy ra khi tạo đơn ${label}.`);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = mode === 'warranty' ? 'Tạo đơn bảo hành' : (mode === 'additional' ? 'Tạo đơn bổ sung' : 'Tạo đơn sửa');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Không thể kết nối tới hệ thống. Vui lòng thử lại sau.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = mode === 'warranty' ? 'Tạo đơn bảo hành' : (mode === 'additional' ? 'Tạo đơn bổ sung' : 'Tạo đơn sửa');
            });
        }
    </script>
@endsection