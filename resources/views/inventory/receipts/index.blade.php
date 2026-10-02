@extends('layout.layout')
@php
    $title    = 'Quản lý tồn kho';
    $subTitle = 'Phiếu Nhập kho';
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
                    <h5 class="font-bold text-neutral-800 text-base mb-1">Danh sách Phiếu Nhập kho (PNK)</h5>
                    <p class="text-xs text-neutral-500 mb-0">Quản lý toàn bộ lịch sử nhập vật tư từ nhà cung cấp và nhập điều chỉnh.</p>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    {{-- Bộ lọc ngày tháng chung --}}
                    <x-date-filter :action="route('inventory.receipts.index')" />

                    <form action="{{ route('inventory.receipts.index') }}" method="GET" class="relative">
                        <iconify-icon icon="ion:search-outline" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control rounded-lg pl-9 pr-4 py-2 border-neutral-200 text-xs w-64 focus:border-primary-500" placeholder="Tìm số phiếu, NCC...">
                    </form>
                    <a href="{{ route('inventory.receipts.create') }}" class="btn btn-primary px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm">
                        <iconify-icon icon="ic:baseline-plus" class="text-base"></iconify-icon> Tạo phiếu nhập mới
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
                                <th class="py-3 px-4 w-28">Ngày nhập</th>
                                <th class="py-3 px-4 min-w-[180px]">Nhà cung cấp / Nguồn</th>
                                <th class="py-3 px-4 w-36">Kho nhận</th>
                                <th class="py-3 px-4 w-24 text-center">Số loại VT</th>
                                <th class="py-3 px-4 w-32 text-right">Tổng số lượng</th>
                                <th class="py-3 px-4 w-36 text-right">Tổng tiền</th>
                                <th class="py-3 px-4 min-w-[160px]">Ghi chú</th>
                                <th class="py-3 px-4 w-28 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 bg-white">
                            @forelse($receipts as $index => $r)
                                <tr class="hover:bg-primary-50 hover:bg-opacity-40 transition-colors">
                                    <td class="py-3 px-4 text-center text-neutral-500 font-medium">
                                        {{ $receipts->firstItem() + $index }}
                                    </td>
                                    <td class="py-3 px-4 font-bold text-primary-600">
                                        <a href="{{ route('inventory.receipts.show', $r) }}" class="hover:underline">
                                            {{ $r->code }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-4 text-neutral-700">
                                        {{ $r->date ? $r->date->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-neutral-800">
                                        {{ $r->supplier_name ?: 'NCC Nhà máy TQ' }}
                                    </td>
                                    <td class="py-3 px-4 text-neutral-700">
                                        {{ $r->warehouse->name ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-neutral-600">
                                        {{ $r->items_count }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-black text-emerald-700 text-sm">
                                        {{ fmod($r->total_quantity, 1) == 0 ? number_format($r->total_quantity, 0, ',', '.') : number_format($r->total_quantity, 2, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-semibold text-neutral-800">
                                        {{ $r->total_amount > 0 ? number_format($r->total_amount, 0, ',', '.') . 'đ' : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-neutral-500 truncate max-w-xs" title="{{ $r->notes }}">
                                        {{ $r->notes ?: '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('inventory.receipts.show', $r) }}" class="p-1 text-primary-600 hover:text-primary-800 text-base" title="Xem chi tiết">
                                                <iconify-icon icon="solar:eye-bold"></iconify-icon>
                                            </a>
                                            <a href="{{ route('inventory.receipts.print', $r) }}" target="_blank" class="p-1 text-info-600 hover:text-info-800 text-base" title="In phiếu">
                                                <iconify-icon icon="solar:printer-outline"></iconify-icon>
                                            </a>
                                            <form action="{{ route('inventory.receipts.destroy', $r) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa phiếu nhập {{ $r->code }}? Số lượng hàng hóa trong phiếu sẽ được tự động hoàn trả (trừ bớt) khỏi tồn kho!')">
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
                                        Chưa có phiếu nhập kho nào. Bấm <strong>"Tạo phiếu nhập mới"</strong> để lập phiếu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($receipts->hasPages())
                    <div class="p-4 border-t border-neutral-200">
                        {{ $receipts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
