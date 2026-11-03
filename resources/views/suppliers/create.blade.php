@extends('layout.layout')
@php
    $title    = 'Quản lý kho & Nhà cung cấp';
    $subTitle = 'Thêm mới Nhà cung cấp';
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12 max-w-4xl mx-auto w-full">
        <div class="card border-0 rounded-xl bg-white shadow-sm overflow-hidden">
            <div class="p-4 border-b border-neutral-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('suppliers.index') }}" class="w-8 h-8 rounded-lg flex items-center justify-center bg-neutral-100 text-neutral-600 hover:bg-neutral-200 transition-colors">
                        <iconify-icon icon="solar:arrow-left-bold" class="text-base"></iconify-icon>
                    </a>
                    <div>
                        <h5 class="font-bold text-neutral-800 text-base mb-0">Thêm mới Nhà cung cấp</h5>
                        <p class="text-xs text-neutral-500 mb-0">Tạo hồ sơ nhà cung cấp để theo dõi phiếu nhập kho và quản lý công nợ.</p>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="p-4 bg-rose-50 border-b border-rose-200 text-rose-700 text-xs">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('suppliers.store') }}" method="POST" class="p-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Mã Nhà cung cấp</label>
                        <input type="text" name="code" value="{{ old('code', $nextCode) }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs font-bold text-primary-700 focus:border-primary-500" placeholder="VD: NCC0001 (để trống tự sinh)">
                        <span class="text-[11px] text-neutral-400 mt-1 block">Mã định danh duy nhất của NCC trong hệ thống</span>
                    </div>

                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Tên Nhà cung cấp <span class="text-danger-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500 font-semibold" placeholder="VD: Công ty TNHH Gỗ An Cường" required>
                    </div>

                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Số điện thoại</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="VD: 0912 345 678">
                    </div>

                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="VD: sales@supplier.vn">
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Địa chỉ kho / trụ sở</label>
                        <input type="text" name="address" value="{{ old('address') }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="VD: Lô A2, KCN Bình Dương, Phường An Phú, TP. Thuận An">
                    </div>

                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Mã số thuế (MST)</label>
                        <input type="text" name="tax_code" value="{{ old('tax_code') }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="VD: 0312345678">
                    </div>

                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Người liên hệ đại diện</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person') }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="VD: Nguyễn Văn A (Kinh doanh)">
                    </div>

                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Công nợ ban đầu (đ)</label>
                        <input type="number" step="1000" name="initial_debt" value="{{ old('initial_debt', 0) }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500 font-bold text-rose-600">
                        <span class="text-[11px] text-neutral-400 mt-1 block">Số tiền còn nợ NCC trước khi đưa vào phần mềm</span>
                    </div>

                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Trạng thái hoạt động</label>
                        <select name="status" class="form-select rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500 font-semibold bg-white">
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Ngưng hoạt động</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Ghi chú thêm</label>
                        <textarea name="notes" rows="3" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-primary-500" placeholder="Thông tin số tài khoản ngân hàng, chính sách công nợ (30 ngày, 45 ngày)...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200">
                    <a href="{{ route('suppliers.index') }}" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-5 py-2 rounded-lg text-xs font-semibold">
                        Hủy bỏ
                    </a>
                    <button type="submit" class="btn btn-primary px-6 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm">
                        <iconify-icon icon="solar:diskette-bold" class="text-base"></iconify-icon> Lưu Nhà cung cấp
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

