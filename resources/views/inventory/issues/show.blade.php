@extends('layout.layout')
@php
    $title    = 'Chi tiết phiếu xuất';
    $subTitle = $issue->code;
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
            <div class="p-4 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('inventory.issues.index') }}" class="w-8 h-8 rounded-lg bg-neutral-100 hover:bg-neutral-200 flex items-center justify-center text-neutral-600 transition-colors">
                        <iconify-icon icon="solar:arrow-left-outline" class="text-lg"></iconify-icon>
                    </a>
                    <div>
                        <div class="flex items-center gap-2">
                            <h5 class="font-bold text-base text-neutral-800 mb-0">Phiếu Xuất kho: {{ $issue->code }}</h5>
                            <span class="bg-rose-100 text-rose-700 text-xs px-2 py-1 rounded-full font-bold">Đã xuất kho</span>
                        </div>
                        <p class="text-xs text-neutral-500 mb-0">Ngày lập phiếu: {{ $issue->date ? $issue->date->format('d/m/Y') : '-' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('inventory.issues.print', $issue) }}" target="_blank" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-4 py-2 rounded-lg text-xs font-semibold flex items-center gap-2">
                        <iconify-icon icon="solar:printer-outline" class="text-base text-info-600"></iconify-icon> In phiếu A4
                    </a>
                    <a href="{{ route('inventory.issues.create') }}" class="btn bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm">
                        <iconify-icon icon="ic:baseline-plus" class="text-base"></iconify-icon> Tạo phiếu xuất mới
                    </a>
                    <form action="{{ route('inventory.issues.destroy', $issue) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa phiếu xuất {{ $issue->code }}? Số lượng hàng hóa trong phiếu sẽ được tự động hoàn trả (cộng lại) vào tồn kho!')" class="inline-block">
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
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-neutral-50 rounded-xl border border-neutral-200 mb-6 text-xs">
                    <div>
                        <span class="text-neutral-400 block mb-1">Kho xuất:</span>
                        <strong class="text-neutral-800 text-sm">{{ $issue->warehouse->name ?? '-' }}</strong>
                    </div>
                    <div>
                        <span class="text-neutral-400 block mb-1">Người / Bộ phận nhận:</span>
                        <strong class="text-neutral-800 text-sm">{{ $issue->recipient ?: 'Xưởng sản xuất Gervin' }}</strong>
                    </div>
                    <div>
                        <span class="text-neutral-400 block mb-1">Lý do xuất:</span>
                        <span class="bg-rose-50 text-rose-700 font-bold px-2 py-1 rounded text-xs inline-block">{{ $issue->reason ?: 'Xuất sản xuất' }}</span>
                    </div>
                    <div>
                        <span class="text-neutral-400 block mb-1">Người xuất (Thủ kho):</span>
                        <strong class="text-neutral-800 text-sm">{{ $issue->deliverer ?: '-' }}</strong>
                    </div>
                    @if($issue->notes)
                        <div class="md:col-span-4 pt-2 border-t border-neutral-200">
                            <span class="text-neutral-400">Ghi chú:</span>
                            <span class="text-neutral-700 font-medium ml-1">{{ $issue->notes }}</span>
                        </div>
                    @endif
                </div>

                {{-- Items table --}}
                <div class="border border-neutral-200 rounded-xl overflow-hidden mb-6">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead class="bg-neutral-100 text-neutral-700 font-bold border-b border-neutral-200 uppercase">
                            <tr>
                                <th class="py-3 px-4 w-12 text-center">STT</th>
                                <th class="py-3 px-4 w-32">Mã SKU</th>
                                <th class="py-3 px-4 min-w-[200px]">Tên mặt hàng vật tư</th>
                                <th class="py-3 px-4 w-28 text-center">Mã xuất xứ</th>
                                <th class="py-3 px-4 w-36">Nhóm hàng</th>
                                <th class="py-3 px-4 w-20 text-center">ĐVT</th>
                                <th class="py-3 px-4 w-36 text-right">Số lượng xuất</th>
                                <th class="py-3 px-4 min-w-[160px]">Ghi chú dòng</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 bg-white">
                            @foreach($issue->items as $idx => $it)
                                <tr class="hover:bg-neutral-50 transition-colors">
                                    <td class="py-3 px-4 text-center text-neutral-500 font-medium">{{ $idx + 1 }}</td>
                                    <td class="py-3 px-4 font-bold text-neutral-900">{{ $it->material->code ?? '-' }}</td>
                                    <td class="py-3 px-4 font-semibold text-neutral-800">{{ $it->material->name ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center text-neutral-600">{{ $it->material->origin_code ?? '-' }}</td>
                                    <td class="py-3 px-4 text-neutral-600">{{ $it->material->category ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center text-neutral-600">{{ $it->material->unit ?? 'Tấm' }}</td>
                                    <td class="py-3 px-4 text-right font-black text-rose-700 text-sm">
                                        {{ fmod($it->quantity, 1) == 0 ? number_format($it->quantity, 0, ',', '.') : number_format($it->quantity, 2, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-neutral-500">
                                        {{ $it->notes ?: '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-neutral-50 font-bold border-t-2 border-neutral-300 text-xs">
                            <tr>
                                <td colspan="6" class="py-3 px-4 text-right text-neutral-700 uppercase">TỔNG CỘNG XUẤT:</td>
                                <td class="py-3 px-4 text-right text-rose-700 font-black text-sm">
                                    {{ fmod($issue->total_quantity, 1) == 0 ? number_format($issue->total_quantity, 0, ',', '.') : number_format($issue->total_quantity, 2, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
