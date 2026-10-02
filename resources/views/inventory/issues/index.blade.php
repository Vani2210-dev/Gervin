@extends('layout.layout')
@php
    $title    = 'Quản lý tồn kho';
    $subTitle = 'Phiếu Xuất kho';
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
                    <h5 class="font-bold text-neutral-800 text-base mb-1">Danh sách Phiếu Xuất kho (PXK)</h5>
                    <p class="text-xs text-neutral-500 mb-0">Theo dõi chi tiết xuất kho cho xưởng sản xuất, đơn hàng và các mục đích khác.</p>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    {{-- Bộ lọc ngày tháng chung --}}
                    <x-date-filter :action="route('inventory.issues.index')" />

                    <form action="{{ route('inventory.issues.index') }}" method="GET" class="relative">
                        <iconify-icon icon="ion:search-outline" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control rounded-lg pl-9 pr-4 py-2 border-neutral-200 text-xs w-64 focus:border-primary-500" placeholder="Tìm số phiếu, người nhận...">
                    </form>
                    <a href="{{ route('inventory.issues.create') }}" class="btn bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm">
                        <iconify-icon icon="ic:baseline-plus" class="text-base"></iconify-icon> Tạo phiếu xuất mới
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="overflow-x-auto w-full">
                    <table class="w-full border-collapse text-xs text-left">
                        <thead>
                            <tr class="bg-neutral-50 text-neutral-700 uppercase font-bold border-b border-neutral-200">
                                <th class="py-3 px-4 w-12 text-center">STT</th>
                                <th class="py-3 px-4 w-32">Mã phiếu</th>
                                <th class="py-3 px-4 w-28">Ngày xuất</th>
                                <th class="py-3 px-4 min-w-[180px]">Người / Bộ phận nhận</th>
                                <th class="py-3 px-4 w-36">Lý do xuất</th>
                                <th class="py-3 px-4 w-32">Kho xuất</th>
                                <th class="py-3 px-4 w-24 text-center">Số loại VT</th>
                                <th class="py-3 px-4 w-32 text-right">Tổng số lượng</th>
                                <th class="py-3 px-4 min-w-[160px]">Ghi chú</th>
                                <th class="py-3 px-4 w-28 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 bg-white">
                            @forelse($issues as $index => $issue)
                                <tr class="hover:bg-primary-50 hover:bg-opacity-40 transition-colors">
                                    <td class="py-3 px-4 text-center text-neutral-500 font-medium">
                                        {{ $issues->firstItem() + $index }}
                                    </td>
                                    <td class="py-3 px-4 font-bold text-rose-600">
                                        <a href="{{ route('inventory.issues.show', $issue) }}" class="hover:underline">
                                            {{ $issue->code }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-4 text-neutral-700">
                                        {{ $issue->date ? $issue->date->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-neutral-800">
                                        {{ $issue->recipient ?: 'Xưởng sản xuất Gervin' }}
                                    </td>
                                    <td class="py-3 px-4 text-neutral-600">
                                        <span class="bg-rose-50 text-rose-700 px-2 py-1 rounded text-[11px] font-semibold">
                                            {{ $issue->reason ?: 'Xuất sản xuất' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-neutral-700">
                                        {{ $issue->warehouse->name ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-neutral-600">
                                        {{ $issue->items_count }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-black text-rose-700 text-sm">
                                        {{ fmod($issue->total_quantity, 1) == 0 ? number_format($issue->total_quantity, 0, ',', '.') : number_format($issue->total_quantity, 2, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-neutral-500 truncate max-w-xs" title="{{ $issue->notes }}">
                                        {{ $issue->notes ?: '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('inventory.issues.show', $issue) }}" class="p-1 text-primary-600 hover:text-primary-800 text-base" title="Xem chi tiết">
                                                <iconify-icon icon="solar:eye-bold"></iconify-icon>
                                            </a>
                                            <a href="{{ route('inventory.issues.print', $issue) }}" target="_blank" class="p-1 text-info-600 hover:text-info-800 text-base" title="In phiếu">
                                                <iconify-icon icon="solar:printer-outline"></iconify-icon>
                                            </a>
                                            <form action="{{ route('inventory.issues.destroy', $issue) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa phiếu xuất {{ $issue->code }}? Số lượng hàng hóa trong phiếu sẽ được tự động hoàn trả (cộng lại) vào tồn kho!')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 text-danger-600 hover:text-danger-800 text-base" title="Xóa">
                                                    <iconify-icon icon="fluent:delete-24-regular"></iconify-icon>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-12 text-center text-neutral-400 font-medium bg-neutral-50">
                                        <iconify-icon icon="solar:box-broken-outline" class="text-4xl text-neutral-300 mb-2 block"></iconify-icon>
                                        Chưa có phiếu xuất kho nào. Bấm <strong>"Tạo phiếu xuất mới"</strong> để lập phiếu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($issues->hasPages())
                    <div class="p-4 border-t border-neutral-200">
                        {{ $issues->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
