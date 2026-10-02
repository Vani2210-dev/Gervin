@extends('layout.layout')
@php
    $title    = 'Quản lý tồn kho';
    $subTitle = 'Phiếu Kiểm kê kho';
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

        <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
            <div class="p-4 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h5 class="font-bold text-neutral-800 text-base mb-1">Danh sách Phiếu Kiểm kê kho (PKK)</h5>
                    <p class="text-xs text-neutral-500 mb-0">Đối chiếu số tồn trên sổ sách và số kiểm đếm thực tế, tự động cân bằng kho 1-chạm chuẩn KiotViet & MISA.</p>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    {{-- Bộ lọc ngày tháng chung --}}
                    <x-date-filter :action="route('inventory.stocktakes.index')" />

                    <form action="{{ route('inventory.stocktakes.index') }}" method="GET" class="relative">
                        <iconify-icon icon="ion:search-outline" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control rounded-lg pl-9 pr-4 py-2 border-neutral-200 text-xs w-64 focus:border-primary-500" placeholder="Tìm số phiếu, người kiểm...">
                    </form>
                    <a href="{{ route('inventory.stocktakes.create') }}" class="btn bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm">
                        <iconify-icon icon="ic:baseline-plus" class="text-base"></iconify-icon> Bắt đầu kiểm kê mới
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="overflow-x-auto w-full">
                    <table class="w-full border-collapse text-xs text-left">
                        <thead>
                            <tr class="bg-neutral-50 text-neutral-700 uppercase font-bold border-b border-neutral-200">
                                <th class="py-3 px-4 w-12 text-center">STT</th>
                                <th class="py-3 px-4 w-32">Mã kiểm kê</th>
                                <th class="py-3 px-4 w-28">Ngày kiểm</th>
                                <th class="py-3 px-4 w-36">Kho hàng</th>
                                <th class="py-3 px-4 w-36">Người kiểm kê</th>
                                <th class="py-3 px-4 w-24 text-center">Số vật tư</th>
                                <th class="py-3 px-4 w-32 text-right">Tổng sổ sách</th>
                                <th class="py-3 px-4 w-32 text-right">Tổng thực tế</th>
                                <th class="py-3 px-4 w-32 text-right">Chênh lệch</th>
                                <th class="py-3 px-4 w-36 text-center">Trạng thái</th>
                                <th class="py-3 px-4 w-28 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 bg-white">
                            @forelse($stocktakes as $index => $s)
                                @php
                                    $diff = $s->total_actual - $s->total_book;
                                @endphp
                                <tr class="hover:bg-primary-50 hover:bg-opacity-40 transition-colors">
                                    <td class="py-3 px-4 text-center text-neutral-500 font-medium">
                                        {{ $stocktakes->firstItem() + $index }}
                                    </td>
                                    <td class="py-3 px-4 font-bold text-amber-600">
                                        <a href="{{ route('inventory.stocktakes.show', $s) }}" class="hover:underline flex items-center gap-1">
                                            <iconify-icon icon="solar:checklist-bold" class="text-sm"></iconify-icon>
                                            {{ $s->code }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-4 text-neutral-700">
                                        {{ $s->date ? $s->date->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-neutral-700">
                                        {{ $s->warehouse->name ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-neutral-800">
                                        {{ $s->creator_name ?: ($s->creator->name ?? 'Kế toán kho') }}
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-neutral-600">
                                        {{ $s->items_count }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-medium text-neutral-600">
                                        {{ fmod($s->total_book, 1) == 0 ? number_format($s->total_book, 0, ',', '.') : number_format($s->total_book, 2, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-neutral-900">
                                        {{ fmod($s->total_actual, 1) == 0 ? number_format($s->total_actual, 0, ',', '.') : number_format($s->total_actual, 2, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-black">
                                        @if($diff > 0)
                                            <span class="text-emerald-600">+{{ fmod($diff, 1) == 0 ? number_format($diff, 0, ',', '.') : number_format($diff, 2, ',', '.') }} (Thừa)</span>
                                        @elseif($diff < 0)
                                            <span class="text-rose-600">{{ fmod($diff, 1) == 0 ? number_format($diff, 0, ',', '.') : number_format($diff, 2, ',', '.') }} (Thiếu)</span>
                                        @else
                                            <span class="text-neutral-400">0 (Khớp)</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($s->status === 'balanced')
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <iconify-icon icon="solar:check-circle-bold" class="text-xs"></iconify-icon> Đã cân bằng
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                <iconify-icon icon="solar:clock-circle-bold" class="text-xs"></iconify-icon> Đang kiểm kê
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('inventory.stocktakes.show', $s) }}" class="p-2 text-neutral-500 hover:text-primary-600 hover:bg-neutral-100 rounded-lg" title="Xem chi tiết">
                                                <iconify-icon icon="solar:eye-bold" class="text-base"></iconify-icon>
                                            </a>
                                            <form action="{{ route('inventory.stocktakes.destroy', $s) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phiếu kiểm kê {{ $s->code }} không?{{ $s->status === 'balanced' ? ' Tồn kho đã cân bằng sẽ được tự động hoàn tác lại!' : '' }}');" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-neutral-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg" title="Xóa">
                                                    <iconify-icon icon="solar:trash-bin-trash-bold" class="text-base"></iconify-icon>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="py-12 text-center text-neutral-400 font-medium">
                                        <div class="flex flex-col items-center justify-center">
                                            <iconify-icon icon="solar:checklist-minimalistic-outline" class="text-4xl text-neutral-300 mb-2"></iconify-icon>
                                            <span>Chưa có đợt kiểm kê nào được tạo.</span>
                                            <a href="{{ route('inventory.stocktakes.create') }}" class="mt-3 btn btn-sm bg-amber-500 text-white px-4 py-2 rounded-lg text-xs font-bold">
                                                Tạo phiếu kiểm kê đầu tiên
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($stocktakes->hasPages())
                    <div class="p-4 border-t border-neutral-200 flex justify-end">
                        {{ $stocktakes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
