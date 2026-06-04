@extends('layout.layout')
@php
    $title    = 'Quản lý kho';
    $subTitle = 'Danh sách kho vật tư';
@endphp

@section('content')

<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        {{-- Header & Summary --}}
        <div class="card p-0 rounded-xl border-0 mb-6 bg-white shadow-sm">
            <div class="card-body p-5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h4 class="text-xl font-bold text-neutral-800 mb-1">Danh sách kho hàng</h4>
                        <p class="text-secondary-light text-sm">Quản lý nhập xuất tồn theo kích cỡ cho từng loại vật tư</p>
                    </div>
                    <button type="button" onclick="openModal('create-warehouse-modal')"
                        class="btn btn-primary text-sm btn-sm px-4 py-2.5 rounded-lg flex items-center gap-2 font-medium shadow-sm transition-all hover:opacity-90">
                        <iconify-icon icon="ic:baseline-plus" class="text-xl"></iconify-icon>
                        Thêm kho mới
                    </button>
                </div>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div class="mb-6 bg-success-100 border border-success-300 text-success-700 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
        @endif
        @if($errors->any())
        <div class="mb-6 bg-danger-100 border border-danger-300 text-danger-700 rounded-lg px-4 py-3 text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Warehouses Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($warehouses as $warehouse)
                <div class="bg-white rounded-2xl border border-neutral-100 p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between h-full">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center text-primary-600">
                                <iconify-icon icon="solar:warehouse-outline" class="text-2xl"></iconify-icon>
                            </div>
                            <div>
                                <h5 class="text-base font-bold text-neutral-800 mb-0.5">{{ $warehouse->name }}</h5>
                                <span class="bg-neutral-100 text-neutral-600 text-xs font-semibold px-2 py-0.5 rounded-full">
                                    {{ $warehouse->records_count }} phiếu
                                </span>
                            </div>
                        </div>
                        <p class="text-secondary-light text-sm mb-6 min-h-[40px]">
                            {{ $warehouse->item_name ?: 'Chưa cấu hình loại hàng hóa' }}
                        </p>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <a href="{{ route('warehouses.show', $warehouse) }}" 
                            class="flex-grow text-center bg-primary-50 text-primary-600 hover:bg-primary-600 hover:text-white font-semibold py-2 px-4 rounded-xl text-sm transition-all duration-200">
                            Xem chi tiết &rarr;
                        </a>
                        <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" 
                            onsubmit="return confirm('Xóa kho này sẽ mất vĩnh viễn toàn bộ lịch sử nhập xuất? Bạn có chắc chắn?')">
                            @csrf 
                            @method('DELETE')
                            <button type="submit" 
                                class="bg-danger-50 hover:bg-danger-600 hover:text-white text-danger-600 w-9 h-9 flex justify-center items-center rounded-xl transition-all duration-200"
                                title="Xóa kho">
                                <iconify-icon icon="fluent:delete-24-regular" class="text-lg"></iconify-icon>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-12">
                    <div class="text-center py-12 bg-white rounded-2xl border border-neutral-100 shadow-sm">
                        <iconify-icon icon="solar:box-broken-outline" class="text-5xl text-neutral-300 mb-3"></iconify-icon>
                        <h5 class="text-neutral-500 font-semibold mb-1">Chưa có kho nào được tạo</h5>
                        <p class="text-secondary-light text-sm mb-4">Bắt đầu bằng cách thêm một kho mới để quản lý hàng hóa.</p>
                        <button type="button" onclick="openModal('create-warehouse-modal')"
                            class="btn btn-primary text-sm btn-sm px-4 py-2 rounded-lg inline-flex items-center gap-2">
                            <iconify-icon icon="ic:baseline-plus" class="text-xl"></iconify-icon>
                            Tạo kho đầu tiên
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Modal Thêm kho mới --}}
<x-modal name="create-warehouse-modal" maxWidth="md">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
        <h5 class="font-bold text-base text-neutral-800">Thông tin kho mới</h5>
        <button type="button" onclick="closeModal('create-warehouse-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('warehouses.store') }}" method="POST">
        @csrf
        <div class="p-6">
            <div class="mb-4">
                <label class="form-label font-semibold text-sm text-neutral-600 mb-1 block">Tên kho <span class="text-danger-500">*</span></label>
                <input type="text" name="name" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500" placeholder="Vd: Kho Đồng Phục" required>
            </div>
            <p class="text-secondary-light text-xs leading-relaxed">Lưu ý: Sau khi tạo, bạn có thể vào trang chi tiết để cấu hình loại hàng hóa và các kích thước (size) tương ứng.</p>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex justify-end gap-3">
            <button type="button" onclick="closeModal('create-warehouse-modal')" class="btn bg-neutral-200 text-neutral-700 px-4 py-2 rounded-lg text-sm font-medium">Hủy</button>
            <button type="submit" class="btn btn-primary px-4 py-2 rounded-lg text-sm font-medium">Tạo kho</button>
        </div>
    </form>
</x-modal>

@endsection
