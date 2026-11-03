@extends('layout.layout')
@php
    $title    = 'Quản lý kho & Nhà cung cấp';
    $subTitle = 'Nhà cung cấp & Công nợ';
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        @include('inventory.partials.navbar')

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl px-4 py-3 text-xs font-semibold flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-neutral-400 hover:text-neutral-700">&times;</button>
            </div>
        @endif
        @if(session('warning'))
            <div class="mb-4 bg-amber-50 border border-amber-300 text-amber-800 rounded-xl px-4 py-3 text-xs font-semibold flex items-center justify-between">
                <span>{{ session('warning') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-neutral-400 hover:text-neutral-700">&times;</button>
            </div>
        @endif

        {{-- Metrics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="card p-4 rounded-xl border border-neutral-200 bg-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center shrink-0">
                    <iconify-icon icon="solar:shop-2-bold" class="text-2xl"></iconify-icon>
                </div>
                <div>
                    <span class="text-xs text-neutral-500 font-semibold uppercase block">Tổng Nhà cung cấp</span>
                    <span class="text-xl font-bold text-neutral-800">{{ number_format($totalSuppliers) }}</span>
                </div>
            </div>

            <div class="card p-4 rounded-xl border border-neutral-200 bg-white shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <iconify-icon icon="solar:check-circle-bold" class="text-2xl"></iconify-icon>
                </div>
                <div>
                    <span class="text-xs text-neutral-500 font-semibold uppercase block">Đang hoạt động</span>
                    <span class="text-xl font-bold text-emerald-700">{{ number_format($activeSuppliers) }}</span>
                </div>
            </div>

            <div class="card p-4 rounded-xl border border-rose-200 bg-rose-50/50 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <iconify-icon icon="solar:wallet-money-bold" class="text-2xl"></iconify-icon>
                </div>
                <div>
                    <span class="text-xs text-rose-700 font-semibold uppercase block">Tổng công nợ phải trả</span>
                    <span class="text-xl font-black text-rose-700">{{ number_format($totalDebt, 0, ',', '.') }}đ</span>
                </div>
            </div>
        </div>

        {{-- Main Table Card --}}
        <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
            <div class="p-4 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h5 class="font-bold text-neutral-800 text-base mb-1">Danh sách Nhà cung cấp & Công nợ</h5>
                    <p class="text-xs text-neutral-500 mb-0">Quản lý danh sách NCC, địa chỉ liên hệ và theo dõi công nợ nhập hàng tự động.</p>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <form action="{{ route('suppliers.index') }}" method="GET" class="flex items-center gap-2">
                        <select name="status" onchange="this.form.submit()" class="form-select rounded-lg border-neutral-200 text-xs px-3 py-2 bg-white focus:border-primary-500">
                            <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>Tất cả trạng thái</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Ngưng hoạt động</option>
                        </select>

                        <div class="relative">
                            <iconify-icon icon="ion:search-outline" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400"></iconify-icon>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control rounded-lg pl-9 pr-4 py-2 border-neutral-200 text-xs w-64 focus:border-primary-500" placeholder="Tìm mã NCC, tên, SĐT, địa chỉ...">
                        </div>
                    </form>
                    <a href="{{ route('suppliers.create') }}" class="btn btn-primary px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm">
                        <iconify-icon icon="ic:baseline-plus" class="text-base"></iconify-icon> Thêm Nhà cung cấp
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="overflow-x-auto w-full">
                    <table class="w-full border-collapse text-xs text-left">
                        <thead>
                            <tr class="bg-neutral-50 text-neutral-700 uppercase font-bold border-b border-neutral-200">
                                <th class="py-3 px-4 w-12 text-center">STT</th>
                                <th class="py-3 px-4 w-28">Mã NCC</th>
                                <th class="py-3 px-4 min-w-[200px]">Tên Nhà cung cấp</th>
                                <th class="py-3 px-4 w-32">Số điện thoại</th>
                                <th class="py-3 px-4 min-w-[220px]">Địa chỉ</th>
                                <th class="py-3 px-4 w-32">Mã số thuế</th>
                                <th class="py-3 px-4 w-40 text-right">Công nợ hiện tại</th>
                                <th class="py-3 px-4 w-28 text-center">Trạng thái</th>
                                <th class="py-3 px-4 w-28 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 bg-white">
                            @forelse($suppliers as $index => $sup)
                                <tr class="hover:bg-primary-50/40 transition-colors">
                                    <td class="py-3 px-4 text-center text-neutral-400 font-medium">
                                        {{ $suppliers->firstItem() + $index }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('suppliers.show', $sup) }}" class="font-bold text-primary-600 hover:text-primary-800 hover:underline">
                                            {{ $sup->code }}
                                        </a>
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('suppliers.show', $sup) }}" class="font-bold text-neutral-800 hover:text-primary-600 block">
                                            {{ $sup->name }}
                                        </a>
                                        @if($sup->contact_person)
                                            <span class="text-[11px] text-neutral-400">LH: {{ $sup->contact_person }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-neutral-700 font-medium">
                                        {{ $sup->phone ?: '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-neutral-600">
                                        <div class="line-clamp-2" title="{{ $sup->address }}">{{ $sup->address ?: '-' }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-neutral-600">
                                        {{ $sup->tax_code ?: '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        @if($sup->debt > 0)
                                            <span class="font-black text-rose-600 text-sm">{{ number_format($sup->debt, 0, ',', '.') }}đ</span>
                                        @elseif($sup->debt < 0)
                                            <span class="font-bold text-emerald-600 text-sm">{{ number_format($sup->debt, 0, ',', '.') }}đ</span>
                                        @else
                                            <span class="font-semibold text-neutral-400">0đ</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($sup->status === 'active')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                Hoạt động
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-neutral-100 text-neutral-600">
                                                Ngưng
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('suppliers.show', $sup) }}" class="w-7 h-7 rounded-lg flex items-center justify-center bg-primary-50 text-primary-600 hover:bg-primary-600 hover:text-white transition-colors" title="Xem chi tiết & công nợ">
                                                <iconify-icon icon="solar:eye-bold" class="text-sm"></iconify-icon>
                                            </a>
                                            <a href="{{ route('suppliers.edit', $sup) }}" class="w-7 h-7 rounded-lg flex items-center justify-center bg-neutral-100 text-neutral-700 hover:bg-neutral-200 transition-colors" title="Chỉnh sửa">
                                                <iconify-icon icon="solar:pen-bold" class="text-sm"></iconify-icon>
                                            </a>
                                            <form action="{{ route('suppliers.destroy', $sup) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa/ngưng hoạt động nhà cung cấp {{ $sup->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-7 h-7 rounded-lg flex items-center justify-center bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors" title="Xóa">
                                                    <iconify-icon icon="solar:trash-bin-trash-bold" class="text-sm"></iconify-icon>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-neutral-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <iconify-icon icon="solar:shop-2-outline" class="text-3xl text-neutral-300"></iconify-icon>
                                            <span>Chưa có nhà cung cấp nào phù hợp với bộ lọc.</span>
                                            <a href="{{ route('suppliers.create') }}" class="btn btn-sm btn-primary mt-1 px-3 py-1 rounded-lg text-xs font-semibold">
                                                + Thêm nhà cung cấp đầu tiên
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($suppliers->hasPages())
                    <div class="p-4 border-t border-neutral-200">
                        {{ $suppliers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

