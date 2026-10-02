@extends('layout.layout')
@php
    $title    = 'Quản lý tồn kho';
    $subTitle = 'Chi tiết Phiếu Kiểm kê';
    $isBalanced = ($stocktake->status === 'balanced');
    $diff = $stocktake->total_actual - $stocktake->total_book;
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        @include('inventory.partials.navbar')

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-success-100 border border-success-300 text-success-700 rounded-lg px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Cảnh báo nếu chưa cân bằng kho --}}
        @if(!$isBalanced)
            <div class="mb-6 bg-amber-50 border border-amber-300 text-amber-800 rounded-xl p-4 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-200 text-amber-800 flex items-center justify-center text-xl font-bold">
                        <iconify-icon icon="solar:danger-triangle-bold"></iconify-icon>
                    </div>
                    <div>
                        <h6 class="font-bold text-amber-900 text-sm mb-0">Phiếu kiểm kê chưa được cân bằng vào kho</h6>
                        <p class="text-xs text-amber-700 mb-0">Số liệu tồn kho thực tế vẫn chưa được cập nhật. Nhấn nút "Cân bằng kho" bên phải để hệ thống tự động ghi nhận số tồn thực tế và tạo bút toán điều chỉnh.</p>
                    </div>
                </div>

                <form action="{{ route('inventory.stocktakes.balance', $stocktake) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn CÂN BẰNG KHO cho đợt kiểm kê này không? Tồn kho thực tế của tất cả vật tư sẽ được cập nhật!');">
                    @csrf
                    <button type="submit" class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg text-xs font-bold shadow-md flex items-center gap-2">
                        <iconify-icon icon="solar:check-circle-bold" class="text-lg"></iconify-icon> Cân bằng kho 1-chạm ngay
                    </button>
                </form>
            </div>
        @else
            <div class="mb-6 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-200 text-emerald-800 flex items-center justify-center text-xl font-bold">
                        <iconify-icon icon="solar:check-circle-bold"></iconify-icon>
                    </div>
                    <div>
                        <h6 class="font-bold text-emerald-900 text-sm mb-0">Phiếu kiểm kê đã được Cân bằng kho thành công</h6>
                        <p class="text-xs text-emerald-700 mb-0">
                            Cân bằng bởi: <strong>{{ $stocktake->balancedByUser->name ?? 'Kế toán kho' }}</strong> 
                            vào lúc: <strong>{{ $stocktake->balanced_at ? $stocktake->balanced_at->format('d/m/Y H:i') : '-' }}</strong>.
                        </p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    <iconify-icon icon="solar:verified-check-bold" class="text-base"></iconify-icon> Sổ kho đã đồng bộ
                </span>
            </div>
        @endif

        {{-- Phiếu kiểm kê Header --}}
        <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
            <div class="p-4 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('inventory.stocktakes.index') }}" class="p-2 text-neutral-500 hover:text-neutral-800 hover:bg-neutral-100 rounded-lg">
                        <iconify-icon icon="solar:arrow-left-linear" class="text-xl"></iconify-icon>
                    </a>
                    <div>
                        <div class="flex items-center gap-2">
                            <h5 class="font-bold text-neutral-800 text-lg mb-0">{{ $stocktake->code }}</h5>
                            @if($isBalanced)
                                <span class="bg-emerald-100 text-emerald-800 text-xs px-3 py-1 rounded-full font-bold">Đã cân bằng</span>
                            @else
                                <span class="bg-amber-100 text-amber-800 text-xs px-3 py-1 rounded-full font-bold">Đang kiểm kê</span>
                            @endif
                        </div>
                        <p class="text-xs text-neutral-500 mb-0">Ngày kiểm kê: {{ $stocktake->date ? $stocktake->date->format('d/m/Y') : '-' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('inventory.stocktakes.index') }}" class="btn btn-secondary px-4 py-2 rounded-lg text-xs font-bold text-neutral-600 border border-neutral-300">
                        Danh sách phiếu
                    </a>
                    @if(!$isBalanced)
                        <form action="{{ route('inventory.stocktakes.balance', $stocktake) }}" method="POST" onsubmit="return confirm('Xác nhận cân bằng kho ngay?');" class="inline-block">
                            @csrf
                            <button type="submit" class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-sm flex items-center gap-1">
                                <iconify-icon icon="solar:check-circle-bold" class="text-base"></iconify-icon> Cân bằng kho
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('inventory.stocktakes.destroy', $stocktake) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phiếu kiểm kê này không?{{ $isBalanced ? ' Tồn kho đã cân bằng sẽ được tự động hoàn tác lại!' : '' }}');" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn bg-rose-50 text-rose-600 hover:bg-rose-100 px-4 py-2 rounded-lg text-xs font-bold">
                            Xóa phiếu
                        </button>
                    </form>
                </div>
            </div>

            {{-- Thông tin 4 thẻ tóm tắt --}}
            <div class="p-6">
                <div class="grid grid-cols-12 gap-4 mb-6">
                    <div class="col-span-12 sm:col-span-6 md:col-span-3">
                        <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                            <span class="text-xs text-neutral-500 block mb-1">Kho kiểm kê</span>
                            <span class="text-sm font-bold text-neutral-900 block">{{ $stocktake->warehouse->name ?? 'Kho Chính' }}</span>
                            <span class="text-[11px] text-neutral-400">Mã: {{ $stocktake->warehouse->code ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6 md:col-span-3">
                        <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                            <span class="text-xs text-neutral-500 block mb-1">Người kiểm kê</span>
                            <span class="text-sm font-bold text-neutral-900 block">{{ $stocktake->creator_name ?: ($stocktake->creator->name ?? 'Kế toán kho') }}</span>
                            <span class="text-[11px] text-neutral-400">Bộ phận kho & kế toán</span>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6 md:col-span-3">
                        <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                            <span class="text-xs text-neutral-500 block mb-1">Tổng tồn sổ sách</span>
                            <span class="text-base font-bold text-neutral-700 block">
                                {{ fmod($stocktake->total_book, 1) == 0 ? number_format($stocktake->total_book, 0, ',', '.') : number_format($stocktake->total_book, 2, ',', '.') }}
                            </span>
                            <span class="text-[11px] text-neutral-400">Số lượng trước kiểm</span>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6 md:col-span-3">
                        <div class="p-4 rounded-xl {{ $diff > 0 ? 'bg-emerald-50 border-emerald-200' : ($diff < 0 ? 'bg-rose-50 border-rose-200' : 'bg-neutral-50 border-neutral-200') }}">
                            <span class="text-xs {{ $diff > 0 ? 'text-emerald-700' : ($diff < 0 ? 'text-rose-700' : 'text-neutral-500') }} block mb-1">
                                Tổng thực tế / Chênh lệch
                            </span>
                            <div class="flex items-baseline justify-between">
                                <span class="text-base font-black text-neutral-900">
                                    {{ fmod($stocktake->total_actual, 1) == 0 ? number_format($stocktake->total_actual, 0, ',', '.') : number_format($stocktake->total_actual, 2, ',', '.') }}
                                </span>
                                <span class="text-xs font-bold {{ $diff > 0 ? 'text-emerald-700' : ($diff < 0 ? 'text-rose-700' : 'text-neutral-500') }}">
                                    @if($diff > 0)
                                        +{{ fmod($diff, 1) == 0 ? number_format($diff, 0, ',', '.') : number_format($diff, 2, ',', '.') }} (Thừa)
                                    @elseif($diff < 0)
                                        {{ fmod($diff, 1) == 0 ? number_format($diff, 0, ',', '.') : number_format($diff, 2, ',', '.') }} (Thiếu)
                                    @else
                                        0 (Khớp)
                                    @endif
                                </span>
                            </div>
                            <span class="text-[11px] text-neutral-400">Số lượng đếm thực tế</span>
                        </div>
                    </div>
                </div>

                @if($stocktake->notes)
                    <div class="p-3 bg-neutral-50 rounded-lg text-xs text-neutral-600 border border-neutral-200 mb-6">
                        <strong>Ghi chú:</strong> {{ $stocktake->notes }}
                    </div>
                @endif

                {{-- Chi tiết bảng danh mục kiểm kê --}}
                <div class="border border-neutral-200 rounded-xl overflow-hidden">
                    <table class="w-full border-collapse text-xs text-left">
                        <thead>
                            <tr class="bg-neutral-100 text-neutral-700 uppercase font-bold border-b border-neutral-200">
                                <th class="py-3 px-3 w-12 text-center">STT</th>
                                <th class="py-3 px-3 w-28">Mã SKU</th>
                                <th class="py-3 px-3 w-28">Mã gốc TQ</th>
                                <th class="py-3 px-4 min-w-[200px]">Tên vật tư / Quy cách</th>
                                <th class="py-3 px-3 w-20 text-center">ĐVT</th>
                                <th class="py-3 px-3 w-32 text-right">Tồn sổ sách</th>
                                <th class="py-3 px-3 w-32 text-right">Tồn thực tế</th>
                                <th class="py-3 px-3 w-32 text-right">Chênh lệch</th>
                                <th class="py-3 px-4 min-w-[180px]">Ghi chú chênh lệch</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 bg-white">
                            @foreach($stocktake->items as $idx => $item)
                                @php
                                    $itemDiff = (float)$item->difference;
                                @endphp
                                <tr class="hover:bg-neutral-50 transition-colors {{ $itemDiff != 0 ? 'bg-amber-50 bg-opacity-20' : '' }}">
                                    <td class="py-3 px-3 text-center text-neutral-500 font-medium">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="py-3 px-3 font-bold text-neutral-900">
                                        {{ $item->material->code ?? '-' }}
                                    </td>
                                    <td class="py-3 px-3 font-medium text-neutral-600">
                                        {{ $item->material->origin_code ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-neutral-800 font-semibold">
                                        {{ $item->material->name ?? '-' }}
                                        @if($item->material && $item->material->category)
                                            <span class="block text-[10px] text-neutral-400 font-normal">{{ $item->material->category }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center text-neutral-600 font-medium">
                                        {{ $item->material->unit ?? 'Tấm' }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-medium text-neutral-700">
                                        {{ fmod($item->book_quantity, 1) == 0 ? number_format($item->book_quantity, 0, ',', '.') : number_format($item->book_quantity, 2, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-bold text-neutral-900">
                                        {{ fmod($item->actual_quantity, 1) == 0 ? number_format($item->actual_quantity, 0, ',', '.') : number_format($item->actual_quantity, 2, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-black">
                                        @if($itemDiff > 0)
                                            <span class="text-emerald-600">+{{ fmod($itemDiff, 1) == 0 ? number_format($itemDiff, 0, ',', '.') : number_format($itemDiff, 2, ',', '.') }}</span>
                                        @elseif($itemDiff < 0)
                                            <span class="text-rose-600">{{ fmod($itemDiff, 1) == 0 ? number_format($itemDiff, 0, ',', '.') : number_format($itemDiff, 2, ',', '.') }}</span>
                                        @else
                                            <span class="text-neutral-400">0</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-neutral-600">
                                        {{ $item->notes ?: '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
