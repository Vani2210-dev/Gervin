@extends('layout.layout')
@php
    $title    = 'Khách hàng';
    $subTitle = 'Danh sách khách hàng';
@endphp

@section('content')

<div class="grid grid-cols-12">
    <div class="col-span-12">
        <div class="card h-full p-0 rounded-xl border-0 overflow-hidden">
            {{-- Header --}}
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                <div class="flex items-center flex-wrap gap-3">
                    {{-- Per page --}}
                    <span class="text-base font-medium text-secondary-light mb-0">Hiển thị</span>
                    <form method="GET" action="{{ route('customers.index') }}" id="perPageForm">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <select name="per_page" class="form-select form-select-sm w-auto border-neutral-200 rounded-lg"
                            onchange="document.getElementById('perPageForm').submit()">
                            @foreach([10, 25, 50, 100] as $option)
                            <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </form>

                    {{-- Search --}}
                    <form method="GET" action="{{ route('customers.index') }}" class="navbar-search">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <input type="text" name="search" class="form-control form-control-sm border-neutral-200 rounded-lg" placeholder="Tìm kiếm..." value="{{ $search }}">
                    </form>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="openModal('filter-modal')"
                        class="btn bg-light-600 text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="solar:filter-outline" class="icon text-xl line-height-1"></iconify-icon>
                        Lọc
                    </button>
                    @if(request()->filled('filter_customer_code') || request()->filled('filter_name') || request()->filled('filter_phone'))
                    <a href="{{ route('customers.index') }}" class="btn text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="solar:close-circle-outline" class="icon text-xl line-height-1"></iconify-icon>
                        Xóa lọc
                    </a>
                    @endif
                    @can('add customer')
                    <button type="button" onclick="openModal('create-customer-modal')"
                        class="btn btn-primary text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                        Thêm khách hàng
                    </button>
                    @endcan
                </div>
            </div>

            {{-- Flash messages --}}
            @if(session('success'))
            <div class="alert alert-success bg-success-50 text-success-600 border border-success-200 rounded-lg p-4 mx-6 mt-4">
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger bg-danger-50 text-danger-600 border border-danger-200 rounded-lg p-4 mx-6 mt-4">
                {{ session('error') }}
            </div>
            @endif

            {{-- Table --}}
            <div class="card-body">
                <div class="table-responsive scroll-sm">
                    <table class="table bordered-table sm-table mb-0">
                        <thead>
                            <tr>
                                <th scope="col">STT</th>
                                <th scope="col">Mã khách hàng</th>
                                <th scope="col">Tên khách hàng</th>
                                <th scope="col">Số điện thoại</th>
                                <th scope="col">Địa chỉ</th>
                                <th scope="col">Chính sách KH</th>
                                <th scope="col" class="text-right">Công nợ</th>
                                <th scope="col" class="text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $index => $c)
                            @php $stt = $customers->firstItem() + $loop->index; @endphp
                            <tr>
                                <td>{{ $stt }}</td>
                                <td>
                                    <span class="text-base font-medium text-secondary-light">{{ $c->customer_code }}</span>
                                </td>
                                <td>
                                    <span class="text-base font-medium text-secondary-light">{{ $c->name }}</span>
                                </td>
                                <td>
                                    <span class="text-base text-secondary-light">{{ $c->phone ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="text-base text-secondary-light">{{ $c->address ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="text-base text-secondary-light">{{ $c->policy ?? '—' }}</span>
                                </td>
                                <td class="text-right">
                                    <span class="text-base font-bold {{ $c->total_debt > 0 ? 'text-danger-600' : 'text-success-600' }}">
                                        {{ number_format($c->total_debt, 0, ',', '.') }} đ
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center gap-3 justify-center">
                                        {{-- Nút xem tổng quan --}}
                                        <button type="button"
                                            onclick="openCustomerOverview({{ $c->id }}, '{{ addslashes($c->customer_code) }}', '{{ addslashes($c->name) }}')"
                                            class="bg-primary-100 hover:bg-primary-200 text-primary-600 font-medium w-10 h-10 flex justify-center items-center rounded-full"
                                            title="Xem tổng quan">
                                            <iconify-icon icon="lucide:bar-chart-2" class="menu-icon"></iconify-icon>
                                        </button>
                                        @can('edit customer')
                                        <button type="button"
                                            onclick="openEditModal({{ $c->id }}, '{{ addslashes($c->customer_code) }}', '{{ addslashes($c->name) }}', '{{ addslashes($c->phone) }}', '{{ addslashes($c->address) }}', {{ $c->debt ?? 0 }}, '{{ addslashes($c->policy ?? '') }}')"
                                            class="bg-success-100 hover:bg-success-200 text-success-600 font-medium w-10 h-10 flex justify-center items-center rounded-full">
                                            <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                        </button>
                                        @endcan
                                        @can('delete customer')
                                        <form method="POST" action="{{ route('customers.destroy', $c) }}"
                                            onsubmit="return confirm('Xóa khách hàng này?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="bg-danger-100 hover:bg-danger-200 text-danger-600 font-medium w-10 h-10 flex justify-center items-center rounded-full">
                                                <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-8">
                                    <p class="text-neutral-500">Chưa có khách hàng nào</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="flex items-center justify-between flex-wrap gap-2 mt-6">
                    <span class="text-secondary-light text-sm">
                        Hiển thị {{ $customers->firstItem() ?? 0 }} đến {{ $customers->lastItem() ?? 0 }}
                        trong tổng {{ $customers->total() }} khách hàng
                    </span>
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Thêm khách hàng --}}
@can('add customer')
<x-modal name="create-customer-modal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
        <h5 class="font-semibold text-base">Thêm khách hàng mới</h5>
        <button type="button" onclick="closeModal('create-customer-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('customers.store') }}" method="POST">
        @csrf
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Mã khách hàng
                    <span class="text-xs font-normal text-neutral-400 ml-1">(để trống sẽ tự tạo tự động)</span>
                </label>
                <input type="text" name="customer_code" class="form-control rounded-lg" placeholder="VD: KH00001 — hoặc để trống" value="{{ old('customer_code') }}">
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Tên khách hàng <span class="text-danger-500">*</span></label>
                <input type="text" name="name" class="form-control rounded-lg" placeholder="Nhập tên khách hàng" required value="{{ old('name') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Số điện thoại</label>
                <input type="text" name="phone" class="form-control rounded-lg" placeholder="Nhập số điện thoại" value="{{ old('phone') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Công nợ</label>
                <input type="number" name="initial_debt" class="form-control rounded-lg" placeholder="Ví dụ: 10000000" min="0" value="{{ old('initial_debt') }}">
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Địa chỉ</label>
                <textarea name="address" class="form-control rounded-lg" placeholder="Nhập địa chỉ" rows="3">{{ old('address') }}</textarea>
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Chính sách KH</label>
                <textarea name="policy" class="form-control rounded-lg" placeholder="Nhập chính sách khách hàng" rows="3">{{ old('policy') }}</textarea>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Lưu</button>
            <button type="button" onclick="closeModal('create-customer-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>
@endcan

{{-- Modal Sửa khách hàng --}}
@can('edit customer')
<x-modal name="edit-customer-modal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
        <h5 class="font-semibold text-base">Chỉnh sửa khách hàng</h5>
        <button type="button" onclick="closeModal('edit-customer-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form id="edit-customer-form" action="" method="POST">
        @csrf @method('PUT')
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Mã khách hàng</label>
                <input type="text" id="edit_customer_code" name="customer_code" class="form-control rounded-lg">
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Tên khách hàng <span class="text-danger-500">*</span></label>
                <input type="text" id="edit_name" name="name" class="form-control rounded-lg" placeholder="Nhập tên khách hàng" required>
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Số điện thoại</label>
                <input type="text" id="edit_phone" name="phone" class="form-control rounded-lg" placeholder="Nhập số điện thoại">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Công nợ</label>
                <input type="number" id="edit_initial_debt" name="initial_debt" class="form-control rounded-lg" placeholder="Ví dụ: 10000000" min="0">
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Địa chỉ</label>
                <textarea id="edit_address" name="address" class="form-control rounded-lg" placeholder="Nhập địa chỉ" rows="3"></textarea>
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Chính sách KH</label>
                <textarea id="edit_policy" name="policy" class="form-control rounded-lg" placeholder="Nhập chính sách khách hàng" rows="3"></textarea>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Cập nhật</button>
            <button type="button" onclick="closeModal('edit-customer-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>

<script>
function openEditModal(id, customerCode, name, phone, address, initialDebt, policy) {
    document.getElementById('edit-customer-form').action = '/customers/' + id;
    document.getElementById('edit_customer_code').value = customerCode;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_address').value = address;
    document.getElementById('edit_initial_debt').value = initialDebt || 0;
    document.getElementById('edit_policy').value = policy || '';
    openModal('edit-customer-modal');
}
</script>
@endcan

{{-- Modal Lọc --}}
<x-modal name="filter-modal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
        <h5 class="font-semibold text-base">Lọc khách hàng</h5>
        <button type="button" onclick="closeModal('filter-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('customers.index') }}" method="GET">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <input type="hidden" name="search" value="{{ $search }}">
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Mã khách hàng</label>
                <input type="text" name="filter_customer_code" class="form-control rounded-lg" placeholder="Nhập mã khách hàng..." value="{{ request('filter_customer_code') }}">
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Tên khách hàng</label>
                <input type="text" name="filter_name" class="form-control rounded-lg" placeholder="Nhập tên khách hàng..." value="{{ request('filter_name') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Số điện thoại</label>
                <input type="text" name="filter_phone" class="form-control rounded-lg" placeholder="Nhập số điện thoại..." value="{{ request('filter_phone') }}">
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Áp dụng lọc</button>
            <button type="button" onclick="closeModal('filter-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>

{{-- Modal Tổng quan khách hàng --}}
<div id="customer-overview-backdrop"
    style="display:none;" class="fixed inset-0 bg-neutral-900/50 z-[1050] backdrop-blur-sm transition-opacity"
    onclick="closeCustomerOverview()">
</div>
<div id="customer-overview-modal"
    style="display:none;" class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[min(1200px,95vw)] max-h-[90vh] overflow-y-auto bg-white rounded-2xl shadow-xl z-[1051]">

    {{-- Header --}}
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between sticky top-0 bg-white z-10">
        <h5 class="font-semibold text-base m-0 flex items-baseline gap-2">
            <span id="ov-name" class="text-neutral-800">—</span>
            <span id="ov-code" class="text-xs font-normal text-neutral-500 bg-neutral-100 px-2 py-0.5 rounded-md">—</span>
        </h5>
        <button type="button" onclick="closeCustomerOverview()" class="text-neutral-400 hover:text-neutral-700 text-2xl leading-none bg-transparent border-none cursor-pointer p-0 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-neutral-100 transition-colors">&times;</button>
    </div>

    {{-- Loading --}}
    <div id="ov-loading" style="padding:60px; text-align:center; display:none;">
        <iconify-icon icon="lucide:loader-2" style="font-size:36px; color:#8b5cf6; animation:ov-spin 1s linear infinite;"></iconify-icon>
        <div style="margin-top:12px; color:#6b7280;">Đang tải...</div>
    </div>

    {{-- Content --}}
    <div id="ov-content" style="padding:24px 28px; display:none;">
        {{-- Stat cards --}}
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px;" id="ov-stats-grid"></div>

        {{-- Status breakdown --}}
        <div style="margin-bottom:22px;">
            <div style="font-size:13px; font-weight:600; color:#374151; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                <iconify-icon icon="lucide:pie-chart" style="color:#8b5cf6;"></iconify-icon>
                Phân bổ trạng thái đơn hàng
            </div>
            <div id="ov-status-grid" style="display:flex; flex-wrap:wrap; gap:8px;"></div>
        </div>

        {{-- Side-by-side grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Left: Orders --}}
            <div class="lg:col-span-7">
                <div style="font-size:13px; font-weight:600; color:#374151; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                    <iconify-icon icon="lucide:list" style="color:#8b5cf6;"></iconify-icon>
                    10 đơn hàng gần nhất
                </div>
                <div style="overflow-x:auto; background:#f8fafc; border-radius:12px; padding:12px; border:1px solid #f1f5f9;">
                    <table style="width:100%; border-collapse:collapse; font-size:12px;">
                        <thead>
                            <tr style="background:#edf2f7;">
                                <th style="padding:8px 10px; text-align:left; color:#4a5568; font-weight:600; border-bottom:1px solid #cbd5e0;">Mã đơn</th>
                                <th style="padding:8px 10px; text-align:left; color:#4a5568; font-weight:600; border-bottom:1px solid #cbd5e0;">Ngày</th>
                                <th style="padding:8px 10px; text-align:right; color:#4a5568; font-weight:600; border-bottom:1px solid #cbd5e0;">Giá trị</th>
                                <th style="padding:8px 10px; text-align:center; color:#4a5568; font-weight:600; border-bottom:1px solid #cbd5e0;">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody id="ov-orders-tbody"></tbody>
                    </table>
                    <div id="ov-no-orders" style="display:none; text-align:center; padding:30px; color:#94a3b8; font-size:13px;">
                        <iconify-icon icon="lucide:package-open" style="font-size:28px;"></iconify-icon>
                        <div style="margin-top:8px;">Chưa có đơn hàng nào</div>
                    </div>
                </div>
            </div>

            {{-- Right: Payments --}}
            <div class="lg:col-span-5 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div style="font-size:13px; font-weight:600; color:#374151; display:flex; align-items:center; gap:6px;">
                        <iconify-icon icon="lucide:wallet" style="color:#8b5cf6;"></iconify-icon>
                        Lịch sử thanh toán
                    </div>
                    <button type="button" id="ov-toggle-add-payment"
                        onclick="document.getElementById('ov-add-payment-block').classList.toggle('hidden'); this.classList.toggle('hidden')"
                        style="background:#f3f4f6; color:#4f46e5; border:1px dashed #c7d2fe; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:4px; transition:all 0.2s;">
                        <iconify-icon icon="lucide:plus" style="font-size:12px;"></iconify-icon>
                        Thêm thanh toán
                    </button>
                </div>

                {{-- Add payment form block inside modal --}}
                <div id="ov-add-payment-block" class="hidden" style="background:#f5f3ff; border:1px solid #ddd6fe; border-radius:12px; padding:12px;">
                    <form id="ov-add-payment-form" method="POST" style="display:flex; flex-direction:column; gap:10px;">
                        @csrf
                        <div style="display:grid; grid-template-columns:1fr 1.2fr; gap:8px;">
                            <div>
                                <label style="font-size:11px; font-weight:600; color:#4b5563; display:block; margin-bottom:3px;">Ngày</label>
                                <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                                    style="width:100%; padding:6px 10px; border:1px solid #cbd5e0; border-radius:6px; font-size:12px; box-sizing:border-box;">
                            </div>
                            <div>
                                <label style="font-size:11px; font-weight:600; color:#4b5563; display:block; margin-bottom:3px;">Số tiền (₫)</label>
                                <input type="number" name="amount" min="1" required placeholder="Nhập số tiền"
                                    style="width:100%; padding:6px 10px; border:1px solid #cbd5e0; border-radius:6px; font-size:12px; box-sizing:border-box;">
                            </div>
                        </div>
                        <div>
                            <label style="font-size:11px; font-weight:600; color:#4b5563; display:block; margin-bottom:3px;">Hình thức</label>
                            <select name="payment_method" style="width:100%; padding:6px 10px; border:1px solid #cbd5e0; border-radius:6px; font-size:12px; background:white;">
                                <option value="cash">Tiền mặt</option>
                                <option value="transfer">Chuyển khoản</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:11px; font-weight:600; color:#4b5563; display:block; margin-bottom:3px;">Ghi chú</label>
                            <input type="text" name="note" placeholder="Ghi chú (nếu có)"
                                style="width:100%; padding:6px 10px; border:1px solid #cbd5e0; border-radius:6px; font-size:12px; box-sizing:border-box;">
                        </div>
                        <div style="display:flex; gap:6px; margin-top:4px;">
                            <button type="submit" style="flex:1; padding:6px; border:none; border-radius:6px; background:#7c3aed; color:#fff; font-size:11px; font-weight:600; cursor:pointer;">
                                Lưu thanh toán
                            </button>
                            <button type="button" onclick="document.getElementById('ov-add-payment-block').classList.add('hidden'); document.getElementById('ov-toggle-add-payment').classList.remove('hidden')"
                                style="padding:6px 12px; border:1px solid #d1d5db; border-radius:6px; background:#fff; color:#4b5563; font-size:11px; font-weight:600; cursor:pointer;">
                                Hủy
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Filter payments --}}
                <div style="display:flex; gap:6px; align-items:center; margin-bottom:8px; margin-top:8px;">
                    <div style="font-size:11px; font-weight:600; color:#4b5563;">Lọc ngày:</div>
                    <input type="date" id="ov-payment-filter-date" style="flex:1; padding:5px 8px; border:1px solid #cbd5e0; border-radius:6px; font-size:11px; outline:none; height:28px; box-sizing:border-box;">
                    <button type="button" onclick="filterOvPayments()" style="background:#4f46e5; color:#fff; border:none; padding:5px 12px; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer; height:28px; display:flex; align-items:center; justify-content:center;">Lọc</button>
                    <button type="button" onclick="clearOvPaymentsFilter()" style="background:#f3f4f6; color:#4b5563; border:1px solid #cbd5e0; padding:5px 12px; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer; height:28px; display:flex; align-items:center; justify-content:center;">Xóa</button>
                </div>

                {{-- Payments list --}}
                <div style="background:#f8fafc; border-radius:12px; padding:12px; border:1px solid #f1f5f9; display:flex; flex-direction:column; gap:10px; max-h-[300px]; overflow-y:auto;" id="ov-payments-list"></div>
                <div id="ov-payments-pagination" style="display:flex; justify-content:center; gap:4px; margin-top:8px;"></div>
                <div id="ov-no-payments" style="display:none; text-align:center; padding:30px; color:#94a3b8; font-size:12px; background:#f8fafc; border-radius:12px; border:1px solid #f1f5f9;">
                    <iconify-icon icon="lucide:coins" style="font-size:24px;"></iconify-icon>
                    <div style="margin-top:6px;">Khách hàng chưa có đợt thanh toán nào hoặc không khớp bộ lọc</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== EDIT CUSTOMER PAYMENT MODAL ===== --}}
<div id="edit-cust-payment-backdrop" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:2000;"
    onclick="closeEditCustPayment()"></div>
<div id="edit-cust-payment-modal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);
           width:min(420px,95vw); background:#fff; border-radius:16px;
           box-shadow:0 20px 60px rgba(0,0,0,0.3); z-index:2001; overflow:hidden;">
    <div style="background:linear-gradient(135deg,#7c3aed,#6d28d9); padding:18px 24px; display:flex; align-items:center; justify-content:space-between;">
        <div style="display:flex; align-items:center; gap:10px;">
            <div style="background:rgba(255,255,255,0.2); border-radius:8px; padding:8px; display:flex;">
                <iconify-icon icon="lucide:edit-3" style="font-size:18px; color:#fff;"></iconify-icon>
            </div>
            <div style="font-size:15px; font-weight:700; color:#fff;">Sửa đợt thanh toán</div>
        </div>
        <button onclick="closeEditCustPayment()"
            style="background:rgba(255,255,255,0.15); border:none; border-radius:8px; width:30px; height:30px; cursor:pointer; display:flex; align-items:center; justify-content:center;">
            <iconify-icon icon="lucide:x" style="font-size:15px; color:#fff;"></iconify-icon>
        </button>
    </div>
    <form id="edit-cust-payment-form" method="POST"
        style="padding:20px 24px; display:flex; flex-direction:column; gap:14px;">
        @csrf
        @method('PUT')
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Ngày <span style="color:#ef4444;">*</span></label>
                <input id="ecp-date" type="date" name="payment_date" required
                    style="width:100%; padding:9px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Số tiền (₫) <span style="color:#ef4444;">*</span></label>
                <input id="ecp-amount" type="number" name="amount" min="1" required
                    style="width:100%; padding:9px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
            </div>
        </div>
        <div>
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Hình thức</label>
            <select id="ecp-method" name="payment_method"
                style="width:100%; padding:9px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; background:white;">
                <option value="cash">💵 Tiền mặt</option>
                <option value="transfer">🏦 Chuyển khoản</option>
                <option value="other">📋 Khác</option>
            </select>
        </div>

        <div>
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Ghi chú</label>
            <input id="ecp-note" type="text" name="note" placeholder="Ghi chú (nếu có)"
                style="width:100%; padding:9px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
        </div>
        <div style="display:flex; gap:10px; padding-top:4px;">
            <button type="submit"
                style="flex:1; padding:10px; border:none; border-radius:8px; background:linear-gradient(135deg,#7c3aed,#6d28d9); color:#fff; font-size:13px; font-weight:600; cursor:pointer;">
                Lưu thay đổi
            </button>
            <button type="button" onclick="closeEditCustPayment()"
                style="padding:10px 18px; border:1.5px solid #d1d5db; border-radius:8px; background:#fff; color:#374151; font-size:13px; font-weight:600; cursor:pointer;">
                Hủy
            </button>
        </div>
    </form>
</div>

<style>
@keyframes ov-spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
@media(max-width:600px){
    #ov-stats-grid { grid-template-columns: repeat(2,1fr) !important; }
}
</style>

<script>
const ovStatusColors = {
    draft:         { bg:'#f1f5f9', text:'#64748b' },
    pending:       { bg:'#fef9c3', text:'#854d0e' },
    transferred:   { bg:'#dbeafe', text:'#1d4ed8' },
    in_production: { bg:'#ede9fe', text:'#6d28d9' },
    completed:     { bg:'#dcfce7', text:'#15803d' },
    cancelled:     { bg:'#fee2e2', text:'#b91c1c' },
};

function ovFmt(n) {
    if (!n && n !== 0) return '0';
    return Number(n).toLocaleString('vi-VN');
}

let currentOverviewId = null;
let currentOverviewCode = null;
let currentOverviewName = null;
let currentOverviewPage = 1;
let currentOverviewFilterDate = '';

function openCustomerOverview(id, code, name) {
    currentOverviewId = id;
    currentOverviewCode = code;
    currentOverviewName = name;
    currentOverviewPage = 1;
    currentOverviewFilterDate = '';
    
    // Reset date input value if it exists
    const dateInput = document.getElementById('ov-payment-filter-date');
    if (dateInput) dateInput.value = '';

    loadCustomerOverviewData(id, 1, '');
}

function filterOvPayments() {
    const dateVal = document.getElementById('ov-payment-filter-date').value;
    currentOverviewFilterDate = dateVal;
    currentOverviewPage = 1;
    loadCustomerOverviewData(currentOverviewId, 1, dateVal);
}

function clearOvPaymentsFilter() {
    const dateInput = document.getElementById('ov-payment-filter-date');
    if (dateInput) dateInput.value = '';
    currentOverviewFilterDate = '';
    currentOverviewPage = 1;
    loadCustomerOverviewData(currentOverviewId, 1, '');
}

function loadCustomerOverviewData(id, page, filterDate) {
    const backdrop = document.getElementById('customer-overview-backdrop');
    const modal    = document.getElementById('customer-overview-modal');
    const loading  = document.getElementById('ov-loading');
    const content  = document.getElementById('ov-content');

    document.getElementById('ov-name').textContent = currentOverviewName;
    document.getElementById('ov-code').textContent = currentOverviewCode;

    backdrop.style.display = 'block';
    modal.style.display    = 'block';
    loading.style.display  = 'block';
    content.style.display  = 'none';

    // Hide add payment block on opening
    document.getElementById('ov-add-payment-block').classList.add('hidden');
    document.getElementById('ov-toggle-add-payment').classList.remove('hidden');

    let url = `/customers/${id}/overview?payment_page=${page}`;
    if (filterDate) {
        url += `&payment_date=${filterDate}`;
    }

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            loading.style.display = 'none';
            content.style.display = 'block';
            renderCustomerOverview(data);
            renderPaymentsPagination(data.payments_pagination);
        })
        .catch(() => {
            loading.style.display = 'none';
            content.style.display = 'block';
            content.innerHTML = '<div style="text-align:center;color:#ef4444;padding:40px;">Không thể tải dữ liệu.</div>';
        });
}

function renderPaymentsPagination(pagination) {
    const container = document.getElementById('ov-payments-pagination');
    if (!pagination || pagination.last_page <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '';
    for (let i = 1; i <= pagination.last_page; i++) {
        const activeStyle = i === pagination.current_page 
            ? 'background:#4f46e5;color:#fff;font-weight:bold;' 
            : 'background:#fff;color:#4b5563;border:1px solid #cbd5e0;';
            
        html += `<button type="button" onclick="changeOverviewPage(${i})" style="width:24px;height:24px;border-radius:4px;font-size:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;${activeStyle}">${i}</button>`;
    }
    container.innerHTML = html;
}

function changeOverviewPage(page) {
    currentOverviewPage = page;
    loadCustomerOverviewData(currentOverviewId, page, currentOverviewFilterDate);
}

function closeCustomerOverview() {
    document.getElementById('customer-overview-backdrop').style.display = 'none';
    document.getElementById('customer-overview-modal').style.display    = 'none';
}

function openEditCustPayment(customerId, pmt) {
    const actionUrl = `/customers/${customerId}/payments/${pmt.id}`;
    document.getElementById('edit-cust-payment-form').action = actionUrl;
    document.getElementById('ecp-date').value = pmt.payment_date;
    document.getElementById('ecp-amount').value = pmt.amount;
    document.getElementById('ecp-method').value = pmt.payment_method;
    document.getElementById('ecp-note').value = pmt.note || '';



    document.getElementById('edit-cust-payment-backdrop').style.display = 'block';
    document.getElementById('edit-cust-payment-modal').style.display = 'block';
}

function closeEditCustPayment() {
    document.getElementById('edit-cust-payment-backdrop').style.display = 'none';
    document.getElementById('edit-cust-payment-modal').style.display = 'none';
}

function renderCustomerOverview(data) {
    const statusLabels = {
        draft:'Nháp', pending:'Chờ xử lý', transferred:'Chuyển sản xuất',
        in_production:'Đang sản xuất', completed:'Hoàn thành', cancelled:'Đã hủy'
    };

    // Store customer orders in window scope for edit payment modal
    window.lastCustomerOrders = data.customer_orders || [];

    // Stat cards
    const cards = [
        { label:'Tổng đơn hàng', value: data.total_orders,                  icon:'lucide:shopping-bag',      bg:'#ede9fe', iconColor:'#7c3aed' },
        { label:'Tổng giá trị',  value: ovFmt(data.total_amount) + '₫',     icon:'lucide:circle-dollar-sign', bg:'#dbeafe', iconColor:'#1d4ed8' },
        { label:'Đã thanh toán', value: ovFmt(data.total_paid) + '₫',        icon:'lucide:check-circle',       bg:'#dcfce7', iconColor:'#15803d' },
        { label:'Còn nợ',        value: ovFmt(data.total_debt) + '₫',        icon:'lucide:alert-circle',
          bg: data.total_debt > 0 ? '#fee2e2' : '#f0fdf4',
          iconColor: data.total_debt > 0 ? '#b91c1c' : '#15803d' },
    ];
    document.getElementById('ov-stats-grid').innerHTML = cards.map(s => `
        <div style="background:#f8fafc; border-radius:12px; padding:16px; display:flex; flex-direction:column; gap:8px;">
            <div style="width:36px; height:36px; background:${s.bg}; border-radius:8px; display:flex; align-items:center; justify-content:center;">
                <iconify-icon icon="${s.icon}" style="color:${s.iconColor}; font-size:18px;"></iconify-icon>
            </div>
            <div style="font-size:11px; color:#64748b; font-weight:500;">${s.label}</div>
            <div style="font-size:16px; font-weight:700; color:#0f172a;">${s.value}</div>
        </div>
    `).join('');

    // Status tags
    const statusHtml = Object.entries(data.status_counts || {}).map(([s, cnt]) => {
        const c = ovStatusColors[s] || { bg:'#f1f5f9', text:'#64748b' };
        return `<span style="background:${c.bg}; color:${c.text}; padding:4px 12px; border-radius:999px; font-size:11px; font-weight:600;">${statusLabels[s]||s}: ${cnt}</span>`;
    }).join('');
    document.getElementById('ov-status-grid').innerHTML = statusHtml || '<span style="color:#94a3b8;font-size:12px;">Chưa có đơn hàng</span>';

    // Orders table
    const tbody = document.getElementById('ov-orders-tbody');
    const noOv  = document.getElementById('ov-no-orders');
    if (!data.recent_orders || data.recent_orders.length === 0) {
        tbody.innerHTML = '';
        noOv.style.display = 'block';
    } else {
        noOv.style.display = 'none';
        tbody.innerHTML = data.recent_orders.map(o => {
            const c = ovStatusColors[o.status] || { bg:'#f1f5f9', text:'#64748b' };
            return `<tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:9px 10px;">
                    <a href="/orders/${o.id}" target="_blank" style="color:#6366f1;font-weight:600;text-decoration:none;">${o.order_code||'—'}</a>
                </td>
                <td style="padding:9px 10px; color:#374151;">${o.order_date ? String(o.order_date).substr(0,10) : '—'}</td>
                <td style="padding:9px 10px; text-align:right; color:#0f172a;">${ovFmt(o.total_amount)}₫</td>
                <td style="padding:9px 10px; text-align:center;">
                    <span style="background:${c.bg}; color:${c.text}; padding:3px 10px; border-radius:999px; font-size:10px; font-weight:600; white-space:nowrap;">${o.status_label}</span>
                </td>
            </tr>`;
        }).join('');
    }

    // Set Action URL for Add Payment Form
    document.getElementById('ov-add-payment-form').action = '/customers/' + data.customer.id + '/payments';



    // Populate Payments list
    const pList = document.getElementById('ov-payments-list');
    const noPayments = document.getElementById('ov-no-payments');
    if (!data.payments || data.payments.length === 0) {
        pList.innerHTML = '';
        noPayments.style.display = 'block';
    } else {
        noPayments.style.display = 'none';
        pList.innerHTML = data.payments.map(p => {
            const methodColor = p.payment_method === 'cash' 
                ? 'background:#fef3c7;color:#d97706;' 
                : (p.payment_method === 'transfer' ? 'background:#dbeafe;color:#2563eb;' : 'background:#f3f4f6;color:#4b5563;');
            
            const orderLink = p.order_code 
                ? `<div style="font-size:10px;color:#4f46e5;margin-top:2px;">Liên kết: <strong>${p.order_code}</strong></div>` 
                : '';
                
            const noteStr = p.note ? `<div style="font-size:10px;color:#6b7280;margin-top:2px;">${p.note}</div>` : '';
            
            const pDataJson = JSON.stringify(p).replace(/"/g, '&quot;');
            
            return `
                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:10px; display:flex; align-items:start; justify-content:space-between; gap:10px;">
                    <div style="flex:1; min-w-0;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <span style="font-size:11px; font-weight:600; color:#374151;">${p.payment_date_formatted}</span>
                            <span style="font-size:9px; padding:1px 5px; border-radius:4px; font-weight:600; ${methodColor}">${p.payment_method_label}</span>
                        </div>
                        <div style="font-size:13px; font-weight:700; color:#059669; margin-top:2px;">+${ovFmt(p.amount)}₫</div>
                        ${orderLink}
                        ${noteStr}
                        <div style="font-size:9px; color:#9ca3af; margin-top:2px;">Người tạo: ${p.creator_name}</div>
                    </div>
                    <div style="display:flex; gap:4px;">
                        <button type="button" onclick="openEditCustPayment(${data.customer.id}, ${pDataJson})" style="border:none; background:none; color:#9ca3af; cursor:pointer; padding:2px; font-size:14px;" class="hover:text-primary-600">
                            <iconify-icon icon="lucide:edit-2"></iconify-icon>
                        </button>
                        <form method="POST" action="/customers/${data.customer.id}/payments/${p.id}" onsubmit="return confirm('Xóa đợt thanh toán này?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="border:none; background:none; color:#9ca3af; cursor:pointer; padding:2px; font-size:14px;" class="hover:text-danger-600">
                                <iconify-icon icon="lucide:trash-2"></iconify-icon>
                            </button>
                        </form>
                    </div>
                </div>
            `;
        }).join('');
    }
}

// Auto open modal on redirect with overview_id param
document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const overviewId = urlParams.get('overview_id');
    if (overviewId) {
        const btn = document.querySelector(`button[onclick*="openCustomerOverview(${overviewId},"]`);
        if (btn) {
            btn.click();
        }
    }
});
</script>

@endsection
