@extends('layout.layout')
@php
    $title    = 'Chi tiết phiếu chuyển kho';
    $subTitle = $transfer->code;
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
            {{-- Header --}}
            <div class="p-4 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('inventory.transfers.index') }}" class="w-8 h-8 rounded-lg bg-neutral-100 hover:bg-neutral-200 flex items-center justify-center text-neutral-600 transition-colors">
                        <iconify-icon icon="solar:arrow-left-outline" class="text-lg"></iconify-icon>
                    </a>
                    <div>
                        <div class="flex items-center gap-2">
                            <h5 class="font-bold text-base text-neutral-800 mb-0">Phiếu Chuyển Kho: {{ $transfer->code }}</h5>
                            <span class="bg-purple-100 text-purple-700 text-xs px-2.5 py-0.5 rounded-full font-bold">Đã hoàn thành</span>
                        </div>
                        <p class="text-xs text-neutral-500 mb-0">Ngày lập phiếu: {{ $transfer->date ? $transfer->date->format('d/m/Y') : '-' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('inventory.transfers.print', $transfer) }}" target="_blank" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-4 py-2 rounded-lg text-xs font-semibold flex items-center gap-2">
                        <iconify-icon icon="solar:printer-outline" class="text-base text-info-600"></iconify-icon> In phiếu A4
                    </a>
                    <a href="{{ route('inventory.transfers.create') }}" class="btn bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm">
                        <iconify-icon icon="ic:baseline-plus" class="text-base"></iconify-icon> Tạo phiếu chuyển mới
                    </a>
                    <form action="{{ route('inventory.transfers.destroy', $transfer) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa phiếu chuyển kho {{ $transfer->code }}? Số lượng hàng hóa trong phiếu sẽ được tự động hoàn trả (cộng lại kho xuất và trừ khỏi kho nhập)!')" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn border border-danger-300 text-danger-600 hover:bg-danger-50 px-4 py-2 rounded-lg text-xs font-semibold flex items-center gap-2">
                            <iconify-icon icon="fluent:delete-24-regular" class="text-base"></iconify-icon> Xóa phiếu
                        </button>
                    </form>
                </div>
            </div>

            <div class="p-6">
                {{-- Master details card --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-purple-50/40 rounded-xl border border-purple-100 mb-6 text-xs">
                    <div>
                        <span class="text-neutral-400 block mb-1">Kho xuất (Nguồn):</span>
                        <div class="flex items-center gap-1.5 font-bold text-rose-700 text-sm">
                            <iconify-icon icon="solar:export-bold" class="text-base"></iconify-icon>
                            <span>{{ $transfer->fromWarehouse->name ?? '-' }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-neutral-400 block mb-1">Kho nhập (Đích):</span>
                        <div class="flex items-center gap-1.5 font-bold text-emerald-700 text-sm">
                            <iconify-icon icon="solar:import-bold" class="text-base"></iconify-icon>
                            <span>{{ $transfer->toWarehouse->name ?? '-' }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="text-neutral-400 block mb-1">Người lập / giao hàng:</span>
                        <strong class="text-neutral-800 text-sm">{{ $transfer->deliverer ?: ($transfer->creator->name ?? '-') }}</strong>
                    </div>
                    <div>
                        <span class="text-neutral-400 block mb-1">Người nhận tại kho đích:</span>
                        <strong class="text-neutral-800 text-sm">{{ $transfer->receiver ?: 'Thủ kho' }}</strong>
                    </div>
                    <div class="md:col-span-3">
                        <span class="text-neutral-400 block mb-1">Lý do điều chuyển / Ghi chú:</span>
                        <span class="text-neutral-800 font-semibold">{{ $transfer->notes ?: 'Chuyển kho nội bộ Gervin' }}</span>
                    </div>
                    <div>
                        <span class="text-neutral-400 block mb-1">Tổng số lượng chuyển:</span>
                        <strong class="text-purple-700 text-base font-bold">{{ number_format($transfer->total_quantity, 1, ',', '.') }}</strong>
                    </div>
                </div>

                {{-- Table Items --}}
                <div class="card p-0 rounded-xl border border-neutral-200 overflow-hidden mb-6">
                    <div class="p-3 bg-neutral-50 border-b border-neutral-200">
                        <span class="font-bold text-xs uppercase text-neutral-700">Chi tiết danh sách mặt hàng đã điều chuyển</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="bg-neutral-100 text-neutral-700 font-bold border-b border-neutral-200">
                                    <th class="py-2.5 px-3 w-12 text-center">STT</th>
                                    <th class="py-2.5 px-3 w-40">Mã SKU</th>
                                    <th class="py-2.5 px-3">Tên nhãn hiệu, quy cách vật tư</th>
                                    <th class="py-2.5 px-3 w-28 text-center">Kích thước (mm)</th>
                                    <th class="py-2.5 px-3 w-20 text-center">ĐVT</th>
                                    <th class="py-2.5 px-3 w-32 text-right">Số lượng chuyển</th>
                                    <th class="py-2.5 px-3">Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 bg-white">
                                @forelse($transfer->items as $index => $item)
                                    <tr class="hover:bg-neutral-50 transition-colors">
                                        <td class="py-2.5 px-3 text-center text-neutral-400 font-medium">{{ $index + 1 }}</td>
                                        <td class="py-2.5 px-3 font-bold text-purple-700">{{ $item->code ?: ($item->fromMaterial->code ?? '—') }}</td>
                                        <td class="py-2.5 px-3 font-semibold text-neutral-800">{{ $item->name ?: ($item->fromMaterial->name ?? '—') }}</td>
                                        <td class="py-2.5 px-3 text-center text-neutral-600">{{ $item->dimensions }}</td>
                                        <td class="py-2.5 px-3 text-center font-medium text-neutral-600">{{ $item->unit ?: ($item->fromMaterial->unit ?? 'Tấm') }}</td>
                                        <td class="py-2.5 px-3 text-right font-bold text-neutral-900 text-sm">
                                            {{ floatval($item->quantity) == intval($item->quantity) ? number_format($item->quantity, 0, ',', '.') : number_format($item->quantity, 2, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 px-3 text-neutral-500">{{ $item->notes ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-6 text-center text-neutral-400">Không có dòng vật tư nào trong phiếu này.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-purple-50 font-bold text-purple-900 border-t border-purple-200">
                                    <td colspan="5" class="py-3 px-4 text-right uppercase">Tổng cộng:</td>
                                    <td class="py-3 px-3 text-right text-sm text-purple-700">
                                        {{ floatval($transfer->total_quantity) == intval($transfer->total_quantity) ? number_format($transfer->total_quantity, 0, ',', '.') : number_format($transfer->total_quantity, 2, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Status notice --}}
                <div class="p-3 bg-neutral-50 rounded-lg border border-neutral-200 text-xs text-neutral-500 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="solar:info-circle-bold" class="text-base text-purple-600"></iconify-icon>
                        <span>Hệ thống đã tự động ghi nhận 2 giao dịch kho: Trừ tồn kho tại <strong>{{ $transfer->fromWarehouse->name ?? 'Kho xuất' }}</strong> và Cộng tồn kho tại <strong>{{ $transfer->toWarehouse->name ?? 'Kho nhận' }}</strong>.</span>
                    </div>
                    <span class="text-neutral-400">Người tạo: {{ $transfer->creator->name ?? 'Hệ thống' }} ({{ $transfer->created_at->format('H:i d/m/Y') }})</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
