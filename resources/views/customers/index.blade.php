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
                                            onclick="openEditModal({{ $c->id }}, '{{ addslashes($c->customer_code) }}', '{{ addslashes($c->name) }}', '{{ addslashes($c->phone) }}', '{{ addslashes($c->address) }}', {{ $c->initial_debt ?? 0 }})"
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
                <label class="form-label font-semibold text-sm text-neutral-600">Nợ đầu kỳ</label>
                <input type="number" name="initial_debt" class="form-control rounded-lg" placeholder="Ví dụ: 10000000" min="0" value="{{ old('initial_debt') }}">
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Địa chỉ</label>
                <textarea name="address" class="form-control rounded-lg" placeholder="Nhập địa chỉ" rows="3">{{ old('address') }}</textarea>
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
                <label class="form-label font-semibold text-sm text-neutral-600">Nợ đầu kỳ</label>
                <input type="number" id="edit_initial_debt" name="initial_debt" class="form-control rounded-lg" placeholder="Ví dụ: 10000000" min="0">
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Địa chỉ</label>
                <textarea id="edit_address" name="address" class="form-control rounded-lg" placeholder="Nhập địa chỉ" rows="3"></textarea>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Cập nhật</button>
            <button type="button" onclick="closeModal('edit-customer-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>

<script>
function openEditModal(id, customerCode, name, phone, address, initialDebt) {
    document.getElementById('edit-customer-form').action = '/customers/' + id;
    document.getElementById('edit_customer_code').value = customerCode;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_address').value = address;
    document.getElementById('edit_initial_debt').value = initialDebt || 0;
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
    style="display:none;" class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[min(800px,95vw)] max-h-[90vh] overflow-y-auto bg-white rounded-2xl shadow-xl z-[1051]">

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

        {{-- Recent orders --}}
        <div>
            <div style="font-size:13px; font-weight:600; color:#374151; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                <iconify-icon icon="lucide:list" style="color:#8b5cf6;"></iconify-icon>
                10 đơn hàng gần nhất
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:12px;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="padding:8px 10px; text-align:left; color:#64748b; font-weight:600; border-bottom:1px solid #e2e8f0;">Mã đơn</th>
                            <th style="padding:8px 10px; text-align:left; color:#64748b; font-weight:600; border-bottom:1px solid #e2e8f0;">Ngày</th>
                            <th style="padding:8px 10px; text-align:right; color:#64748b; font-weight:600; border-bottom:1px solid #e2e8f0;">Giá trị</th>
                            <th style="padding:8px 10px; text-align:right; color:#64748b; font-weight:600; border-bottom:1px solid #e2e8f0;">Đã thu</th>
                            <th style="padding:8px 10px; text-align:right; color:#64748b; font-weight:600; border-bottom:1px solid #e2e8f0;">Còn nợ</th>
                            <th style="padding:8px 10px; text-align:center; color:#64748b; font-weight:600; border-bottom:1px solid #e2e8f0;">Đợt TT</th>
                            <th style="padding:8px 10px; text-align:center; color:#64748b; font-weight:600; border-bottom:1px solid #e2e8f0;">Trạng thái</th>
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
    </div>
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

function openCustomerOverview(id, code, name) {
    const backdrop = document.getElementById('customer-overview-backdrop');
    const modal    = document.getElementById('customer-overview-modal');
    const loading  = document.getElementById('ov-loading');
    const content  = document.getElementById('ov-content');

    document.getElementById('ov-name').textContent = name;
    document.getElementById('ov-code').textContent = code;

    backdrop.style.display = 'block';
    modal.style.display    = 'block';
    loading.style.display  = 'block';
    content.style.display  = 'none';

    fetch(`/customers/${id}/overview`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            loading.style.display = 'none';
            content.style.display = 'block';
            renderCustomerOverview(data);
        })
        .catch(() => {
            loading.style.display = 'none';
            content.style.display = 'block';
            content.innerHTML = '<div style="text-align:center;color:#ef4444;padding:40px;">Không thể tải dữ liệu.</div>';
        });
}

function closeCustomerOverview() {
    document.getElementById('customer-overview-backdrop').style.display = 'none';
    document.getElementById('customer-overview-modal').style.display    = 'none';
}

function renderCustomerOverview(data) {
    const statusLabels = {
        draft:'Nháp', pending:'Chờ xử lý', transferred:'Chuyển sản xuất',
        in_production:'Đang sản xuất', completed:'Hoàn thành', cancelled:'Đã hủy'
    };

    // Stat cards
    const cards = [
        { label:'Tổng đơn hàng', value: data.total_orders,                  icon:'lucide:shopping-bag',      bg:'#ede9fe', iconColor:'#7c3aed' },
        { label:'Tổng giá trị',  value: ovFmt(data.total_amount) + '₫',     icon:'lucide:circle-dollar-sign', bg:'#dbeafe', iconColor:'#1d4ed8' },
        { label:'Đã thu',        value: ovFmt(data.total_paid) + '₫',        icon:'lucide:check-circle',       bg:'#dcfce7', iconColor:'#15803d' },
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
            const debtStyle = o.debt > 0 ? 'color:#b91c1c;font-weight:600;' : 'color:#15803d;';
            const pmtBadge = o.payments_count > 0
                ? `<span style="background:#ede9fe;color:#6d28d9;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:600;">${o.payments_count} đợt</span>`
                : `<span style="color:#94a3b8;font-size:11px;">—</span>`;
            return `<tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:9px 10px;">
                    <a href="/orders/${o.id}" target="_blank" style="color:#6366f1;font-weight:600;text-decoration:none;">${o.order_code||'—'}</a>
                </td>
                <td style="padding:9px 10px; color:#374151;">${o.order_date ? String(o.order_date).substr(0,10) : '—'}</td>
                <td style="padding:9px 10px; text-align:right; color:#0f172a;">${ovFmt(o.total_amount)}₫</td>
                <td style="padding:9px 10px; text-align:right; color:#15803d;">${ovFmt(o.paid)}₫</td>
                <td style="padding:9px 10px; text-align:right; ${debtStyle}">${ovFmt(o.debt)}₫</td>
                <td style="padding:9px 10px; text-align:center;">${pmtBadge}</td>
                <td style="padding:9px 10px; text-align:center;">
                    <span style="background:${c.bg}; color:${c.text}; padding:3px 10px; border-radius:999px; font-size:10px; font-weight:600; white-space:nowrap;">${o.status_label}</span>
                </td>
            </tr>`;
        }).join('');
    }
}
</script>

@endsection
