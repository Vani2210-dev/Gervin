@extends('layout.layout')
@php
    $title    = 'Chi tiết đơn hàng';
    $subTitle = 'Đơn hàng: ' . $acrylicOrder->order_code;
@endphp

@section('content')

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
                                    <td colspan="16" class="text-center text-neutral-400 py-4">Chưa có sản phẩm nào</td>
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
                                    <td colspan="14" class="text-center text-neutral-400 py-4">Chưa có sản phẩm nào</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @else
                        {{-- Acrylic items --}}
                        <table class="table bordered-table sm-table mb-0 min-w-[1700px]">
                            <thead>
                                <tr>
                                    <th scope="col" class="w-10 text-center">STT</th>
                                    <th scope="col" class="w-32">Mã SP</th>
                                    <th scope="col" class="w-64">Tên SP</th>
                                    <th scope="col" class="w-20">SL</th>
                                    <th scope="col" class="w-20">Cao</th>
                                    <th scope="col" class="w-20">Rộng</th>
                                    <th scope="col" class="w-24">Cạnh Vát</th>
                                    <th scope="col" class="w-24">Chiều vân</th>
                                    <th scope="col" class="w-24">Cánh (m2)</th>
                                    <th scope="col" class="w-24">Phào (m)</th>
                                    <th scope="col" class="w-24">Vát</th>
                                    <th scope="col" class="w-28">Vân dọc CNC</th>
                                    <th scope="col" class="w-28 text-end">Đơn giá</th>
                                    <th scope="col" class="w-28 text-end">Thành tiền</th>
                                    <th scope="col" class="w-44">Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supply->items as $itemIndex => $item)
                                <tr>
                                    <td class="text-center">{{ $itemIndex + 1 }}</td>
                                    <td><span class="text-neutral-500 text-xs">{{ $item->product_code ?? '—' }}</span></td>
                                    <td><span class="font-medium text-neutral-800">{{ $item->product_name }}</span></td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->height ?? '—' }}</td>
                                    <td>{{ $item->width ?? '—' }}</td>
                                    <td>{{ $item->edge_bevel ?? '—' }}</td>
                                    <td>{{ $item->grain_direction ?? '0' }}</td>
                                    <td>{{ $item->wing_area ?? '—' }}</td>
                                    <td>{{ $item->molding_length ?? '—' }}</td>
                                    <td>{{ $item->bevel ?? '—' }}</td>
                                    <td>{{ $item->vertical_grain_cnc ?? '—' }}</td>
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
                        {{ number_format($acrylicOrder->total_amount, 0, ',', '.') }} VNĐ
                    </span>
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
            @can('edit acrylic order')
            <a href="{{ route('orders.edit', $acrylicOrder) }}" class="w-full justify-center flex items-center gap-2 py-3 rounded-xl font-semibold bg-neutral-900 hover:bg-black text-white shadow-sm text-sm transition-colors cursor-pointer">
                <iconify-icon icon="lucide:edit-3" class="text-base"></iconify-icon> Chỉnh sửa đơn hàng
            </a>
            @endcan
            <button type="button" onclick="exportToExcel()" class="w-full justify-center flex items-center gap-2 py-3 rounded-xl font-semibold border border-neutral-200 text-neutral-700 hover:bg-neutral-50 transition-colors shadow-sm text-sm bg-white cursor-pointer">
                <iconify-icon icon="lucide:file-spreadsheet" class="text-base"></iconify-icon> Xuất Excel (.xlsx)
            </button>
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
        'total_amount' => $acrylicOrder->total_amount,
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
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'height' => $item->height,
                        'width' => $item->width,
                        'edge_bevel' => $item->edge_bevel,
                        'grain_direction' => $item->grain_direction,
                        'wing_area' => $item->wing_area,
                        'molding_length' => $item->molding_length,
                        'bevel' => $item->bevel,
                        'vertical_grain_cnc' => $item->vertical_grain_cnc,
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
@endsection
