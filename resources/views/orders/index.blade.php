@extends('layout.layout')
@php
    $title    = 'Đơn hàng';
    $subTitle = 'Danh sách đơn hàng';
@endphp

@section('content')

<style>
    #orders-list-card .bulk-order-select-col {
        display: none;
    }
    #orders-list-card.is-bulk-delete-mode .bulk-order-select-col {
        display: table-cell;
    }
    #orders-list-card.is-bulk-delete-mode thead th:last-child,
    #orders-list-card.is-bulk-delete-mode tbody td:last-child {
        display: none;
    }
</style>

<div class="grid grid-cols-12 gap-y-6">
    <div class="col-span-12">
        {{-- Statistics Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card 1 -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center text-primary-600 text-2xl flex-shrink-0">
                    <iconify-icon icon="lucide:shopping-bag"></iconify-icon>
                </div>
                <div>
                    <div class="text-xs text-neutral-500 font-medium mb-1">Tổng đơn hàng</div>
                    <div class="text-xl font-bold text-neutral-800">{{ $totalOrdersCount }} đơn</div>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-2xl flex-shrink-0">
                    <iconify-icon icon="lucide:circle-dollar-sign"></iconify-icon>
                </div>
                <div>
                    <div class="text-xs text-neutral-500 font-medium mb-1">Tổng tiền hàng</div>
                    <div class="text-xl font-bold text-neutral-800">{{ number_format($totalAmountSum, 0, ',', '.') }}₫</div>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-success-50 flex items-center justify-center text-success-600 text-2xl flex-shrink-0">
                    <iconify-icon icon="lucide:check-circle"></iconify-icon>
                </div>
                <div>
                    <div class="text-xs text-neutral-500 font-medium mb-1">Tổng đã thu</div>
                    <div class="text-xl font-bold text-success-700">{{ number_format($totalPaidSum, 0, ',', '.') }}₫</div>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-danger-50 flex items-center justify-center text-danger-600 text-2xl flex-shrink-0">
                    <iconify-icon icon="lucide:alert-circle"></iconify-icon>
                </div>
                <div>
                    <div class="text-xs text-neutral-500 font-medium mb-1">Tổng còn nợ</div>
                    <div class="text-xl font-bold text-danger-700">{{ number_format($totalDebtSum, 0, ',', '.') }}₫</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12">
        <div id="orders-list-card" class="card h-full p-0 rounded-xl border-0 overflow-hidden">
            {{-- Header --}}
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                <div class="flex items-center flex-wrap gap-3">
                    {{-- Per page --}}
                    <span class="text-base font-medium text-secondary-light mb-0">Hiển thị</span>
                    <form method="GET" action="{{ route('orders.index') }}" id="perPageForm">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <select name="per_page" class="form-select form-select-sm w-auto border-neutral-200 rounded-lg"
                            onchange="document.getElementById('perPageForm').submit()">
                            @foreach([10, 25, 50, 100] as $option)
                            <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </form>

                    {{-- Search --}}
                    <form method="GET" action="{{ route('orders.index') }}" class="navbar-search">
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
                    @if(request()->filled('filter_order_code') || request()->filled('filter_customer_name') || request()->filled('filter_status') || request()->filled('filter_type') || request()->filled('filter_date') || request()->filled('filter_month') || request()->filled('filter_year'))
                    <a href="{{ route('orders.index') }}" class="btn text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="solar:close-circle-outline" class="icon text-xl line-height-1"></iconify-icon>
                        Xóa lọc
                    </a>
                    @endif
                    @can('add order')
                    <a href="{{ route('orders.create') }}"
                        class="btn btn-primary text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                        Tạo đơn hàng
                    </a>
                    @endcan
                    @can('delete order')
                    <button type="button" onclick="toggleBulkDeleteOrders()"
                        class="js-toggle-bulk-delete btn bg-danger-600 hover:bg-danger-700 text-white text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2 shadow-sm">
                        <iconify-icon icon="lucide:trash-2" class="icon text-xl line-height-1 text-white"></iconify-icon>
                        <span class="bulk-delete-toggle-label">Chọn nhiều</span>
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

            @php
                $canBulkDeleteOrders = auth()->user()?->can('delete order');
            @endphp

            @if($canBulkDeleteOrders)
            <form id="bulkDeleteForm" method="POST" action="{{ route('orders.bulk-destroy') }}" onsubmit="return confirmBulkDeleteOrders();">
                @csrf
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <input type="hidden" name="filter_order_code" value="{{ request('filter_order_code') }}">
                <input type="hidden" name="filter_customer_name" value="{{ request('filter_customer_name') }}">
                <input type="hidden" name="filter_status" value="{{ request('filter_status') }}">
                <input type="hidden" name="filter_type" value="{{ request('filter_type') }}">
                <input type="hidden" name="filter_date" value="{{ request('filter_date') }}">
                <input type="hidden" name="filter_month" value="{{ request('filter_month') }}">
                <input type="hidden" name="filter_year" value="{{ request('filter_year') }}">
            </form>
            <div id="bulkOrderActionBar" class="hidden mx-4 mt-4 mb-0 bg-primary-50 border border-primary-200 rounded-xl px-4 py-3 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center">
                        <iconify-icon icon="lucide:check-square" class="text-lg"></iconify-icon>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-neutral-800 mb-0">
                            Đã chọn <span id="bulkSelectedCount" class="text-primary-600">0</span> đơn hàng
                        </p>
                        <p class="text-xs text-secondary-light mb-0">Chỉ xóa những đơn bạn đã chọn trong danh sách hiện tại.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="clearBulkOrderSelection()" class="btn btn-sm bg-white border border-neutral-200 text-neutral-700 hover:bg-neutral-100 rounded-lg px-3 py-2 flex items-center gap-2">
                        <iconify-icon icon="lucide:x" class="text-base"></iconify-icon>
                        Bỏ chọn
                    </button>
                    <button type="submit" form="bulkDeleteForm" class="btn btn-sm bg-danger-600 hover:bg-danger-700 text-white rounded-lg px-3 py-2 flex items-center gap-2">
                        <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                        Xóa đã chọn
                    </button>
                </div>
            </div>
            @endif

            {{-- Table --}}
            <div class="card-body">
                <div class="table-responsive scroll-sm">
                    <table class="table bordered-table sm-table mb-0">
                        <thead>
                            <tr>
                                @if($canBulkDeleteOrders)
                                <th scope="col" class="bulk-order-select-col text-center" style="width: 48px; min-width: 48px;">
                                    <input type="checkbox" id="bulkSelectAllOrders" class="form-check-input rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                                </th>
                                @endif
                                <th scope="col">STT</th>
                                <th scope="col">Mã đơn</th>
                                <th scope="col">Loại đơn</th>
                                <th scope="col">Khách hàng</th>
                                <th scope="col">Số điện thoại</th>
                                <th scope="col">Hạn đơn</th>
                                <th scope="col">Tổng tiền</th>
                                <th scope="col">Trạng thái</th>
                                <th scope="col" class="text-center whitespace-nowrap" style="width: 160px; min-width: 160px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $index => $order)
                            @php $stt = $orders->firstItem() + $loop->index; @endphp
                            <tr>
                                @if($canBulkDeleteOrders)
                                <td class="bulk-order-select-col text-center align-middle">
                                    <input type="checkbox"
                                        name="order_ids[]"
                                        value="{{ $order->id }}"
                                        form="bulkDeleteForm"
                                        class="bulk-order-checkbox form-check-input rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                                </td>
                                @endif
                                <td>{{ $stt }}</td>
                                <td>
                                    <span class="text-base font-medium text-secondary-light">{{ $order->order_code }}</span>
                                </td>
                                <td>
                                    @if($order->type)
                                        @php
                                            $typeColors = [
                                                'acrylic' => 'bg-primary-100 text-primary-600 border border-primary-200',
                                                'min_late' => 'bg-warning-100 text-warning-600 border border-warning-200',
                                                'glass' => 'bg-info-100 text-info-600 border border-info-200',
                                            ];
                                            $typeLabels = [
                                                'acrylic' => 'Acrylic',
                                                'min_late' => 'Min Late',
                                                'glass' => 'Glass',
                                            ];
                                        @endphp
                                        <span class="px-2 py-0.5 rounded border text-xs font-semibold {{ $typeColors[$order->type] ?? 'bg-neutral-100 text-neutral-600' }}">
                                            {{ $typeLabels[$order->type] ?? $order->type }}
                                        </span>
                                    @else
                                        <span class="text-neutral-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-base font-medium text-secondary-light">{{ $order->customer_name }}</span>
                                </td>
                                <td>
                                    <span class="text-base text-secondary-light">{{ $order->phone ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="text-base text-secondary-light">{{ $order->deadline ? $order->deadline->format('H:i d/m/Y') : '—' }}</span>
                                </td>
                                <td>
                                    <span class="text-base font-medium text-secondary-light">{{ number_format(round($order->total_amount, -3), 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-neutral-100 text-neutral-600',
                                            'pending' => 'bg-warning-100 text-warning-600',
                                            'transferred' => 'bg-info-100 text-info-600',

                                            'in_production' => 'bg-indigo-100 text-indigo-600 border border-indigo-200',
                                            'completed' => 'bg-success-100 text-success-600',
                                            'cancelled' => 'bg-danger-100 text-danger-600',
                                        ];
                                        $statusLabels = [
                                            'draft' => 'Nháp',
                                            'pending' => 'Chờ xử lý',
                                            'transferred' => 'Chuyển sản xuất',

                                            'in_production' => 'Đang sản xuất',
                                            'completed' => 'Hoàn thành',
                                            'cancelled' => 'Đã hủy',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded font-medium text-xs {{ $statusColors[$order->status] ?? 'bg-neutral-100 text-neutral-600' }}">
                                        {{ $statusLabels[$order->status] ?? $order->status }}
                                    </span>
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    <div class="flex items-center gap-2 justify-center">
                                        @can('view order')
                                        <a href="{{ route('orders.show', $order) }}" class="bg-primary-100 hover:bg-primary-200 text-primary-600 font-medium w-8 h-8 flex justify-center items-center rounded-full" title="Xem chi tiết">
                                            <iconify-icon icon="lucide:eye" class="menu-icon"></iconify-icon>
                                        </a>
                                        <a href="{{ route('orders.show', $order) }}?export=1" class="bg-success-100 hover:bg-success-200 text-success-600 font-medium w-8 h-8 flex justify-center items-center rounded-full" title="Xuất Excel">
                                            <iconify-icon icon="lucide:file-spreadsheet" class="menu-icon"></iconify-icon>
                                        </a>
                                        @endcan
                                        @can('edit order')
                                            @if(!in_array($order->status, ['in_production', 'cancelled']))
                                                <a href="{{ route('orders.edit', $order) }}"
                                                    class="bg-success-100 hover:bg-success-200 text-success-600 font-medium w-8 h-8 flex justify-center items-center rounded-full" title="Chỉnh sửa">
                                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                                </a>
                                            @else
                                                <span class="bg-neutral-100 text-neutral-400 cursor-not-allowed font-medium w-8 h-8 flex justify-center items-center rounded-full" title="{{ $order->status === 'in_production' ? 'Đơn hàng đang sản xuất, không thể chỉnh sửa' : 'Đơn hàng đã bị hủy, không thể chỉnh sửa' }}">
                                                    <iconify-icon icon="lucide:edit" class="menu-icon"></iconify-icon>
                                                </span>
                                            @endif
                                        @endcan
                                        @can('delete order')
                                        <form method="POST" action="{{ route('orders.destroy', $order) }}"
                                            onsubmit="return confirm('Xóa đơn hàng này?')">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="search" value="{{ $search }}">
                                            <input type="hidden" name="per_page" value="{{ $perPage }}">
                                            <input type="hidden" name="filter_order_code" value="{{ request('filter_order_code') }}">
                                            <input type="hidden" name="filter_customer_name" value="{{ request('filter_customer_name') }}">
                                            <input type="hidden" name="filter_status" value="{{ request('filter_status') }}">
                                            <button type="submit"
                                                class="bg-danger-100 hover:bg-danger-200 text-danger-600 font-medium w-8 h-8 flex justify-center items-center rounded-full">
                                                <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ $canBulkDeleteOrders ? 10 : 9 }}" class="text-center py-8">
                                    <p class="text-neutral-500">Chưa có đơn hàng nào</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="flex items-center justify-between flex-wrap gap-2 mt-6">
                    <span class="text-secondary-light text-sm">
                        Hiển thị {{ $orders->firstItem() ?? 0 }} đến {{ $orders->lastItem() ?? 0 }}
                        trong tổng {{ $orders->total() }} đơn hàng
                    </span>
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Lọc --}}
<x-modal name="filter-modal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between">
        <h5 class="font-semibold text-base">Lọc đơn hàng</h5>
        <button type="button" onclick="closeModal('filter-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('orders.index') }}" method="GET">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <input type="hidden" name="search" value="{{ $search }}">
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Mã đơn</label>
                <input type="text" name="filter_order_code" class="form-control rounded-lg" placeholder="Nhập mã đơn..." value="{{ request('filter_order_code') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Tên khách hàng</label>
                <input type="text" name="filter_customer_name" class="form-control rounded-lg" placeholder="Nhập tên khách..." value="{{ request('filter_customer_name') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Trạng thái</label>
                <select name="filter_status" class="form-select rounded-lg">
                    <option value="">Tất cả</option>
                    <option value="draft" {{ request('filter_status') === 'draft' ? 'selected' : '' }}>Nháp</option>
                    <option value="pending" {{ request('filter_status') === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="transferred" {{ request('filter_status') === 'transferred' ? 'selected' : '' }}>Chuyển sản xuất</option>
                    <option value="in_production" {{ request('filter_status') === 'in_production' ? 'selected' : '' }}>Đang sản xuất</option>
                    <option value="completed" {{ request('filter_status') === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="cancelled" {{ request('filter_status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Loại đơn</label>
                <select name="filter_type" class="form-select rounded-lg">
                    <option value="">Tất cả</option>
                    <option value="acrylic" {{ request('filter_type') === 'acrylic' ? 'selected' : '' }}>Acrylic</option>
                    <option value="glass" {{ request('filter_type') === 'glass' ? 'selected' : '' }}>Kính</option>
                    <option value="min_late" {{ request('filter_type') === 'min_late' ? 'selected' : '' }}>Min Late</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Lọc theo ngày</label>
                <input type="date" name="filter_date" class="form-control rounded-lg" value="{{ request('filter_date') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Lọc theo tháng</label>
                <input type="month" name="filter_month" class="form-control rounded-lg" value="{{ request('filter_month') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Lọc theo năm</label>
                <select name="filter_year" class="form-select rounded-lg">
                    <option value="">Tất cả</option>
                    @php $currentYear = date('Y'); @endphp
                    @for($y = $currentYear; $y >= $currentYear - 5; $y--)
                    <option value="{{ $y }}" {{ request('filter_year') == $y ? 'selected' : '' }}>Năm {{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Áp dụng lọc</button>
            <button type="button" onclick="closeModal('filter-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>

@if($canBulkDeleteOrders)
<script>
    function getOrdersListCard() {
        return document.getElementById('orders-list-card');
    }

    function isBulkDeleteMode() {
        const card = getOrdersListCard();
        return card ? card.classList.contains('is-bulk-delete-mode') : false;
    }

    function updateBulkDeleteToggleButton() {
        const button = document.querySelector('.js-toggle-bulk-delete');
        if (!button) return;

        const label = button.querySelector('.bulk-delete-toggle-label');
        const icon = button.querySelector('iconify-icon');
        const active = isBulkDeleteMode();

        if (label) {
            label.textContent = active ? 'Thoát' : 'Chọn nhiều';
        }

        if (icon) {
            icon.setAttribute('icon', active ? 'lucide:x' : 'lucide:trash-2');
        }

        button.title = active ? 'Thoát chế độ xóa nhiều' : 'Bật chế độ xóa nhiều';
    }

    function setBulkDeleteMode(enabled) {
        const card = getOrdersListCard();
        if (!card) return;

        card.classList.toggle('is-bulk-delete-mode', enabled);
        if (!enabled) {
            clearBulkOrderSelection();
        }

        updateBulkDeleteToggleButton();
        updateBulkOrderSelection();
    }

    function toggleBulkDeleteOrders() {
        setBulkDeleteMode(!isBulkDeleteMode());
    }

    function updateBulkOrderSelection() {
        const selectedCheckboxes = document.querySelectorAll('.bulk-order-checkbox:checked');
        const selectedCount = selectedCheckboxes.length;
        const bulkBar = document.getElementById('bulkOrderActionBar');
        const countEl = document.getElementById('bulkSelectedCount');
        const selectAll = document.getElementById('bulkSelectAllOrders');
        const allCheckboxes = document.querySelectorAll('.bulk-order-checkbox');

        if (countEl) {
            countEl.textContent = selectedCount;
        }

        if (bulkBar) {
            bulkBar.classList.toggle('hidden', !isBulkDeleteMode() || selectedCount === 0);
        }

        if (selectAll) {
            selectAll.checked = allCheckboxes.length > 0 && selectedCount === allCheckboxes.length;
            selectAll.indeterminate = selectedCount > 0 && selectedCount < allCheckboxes.length;
        }
    }

    function clearBulkOrderSelection() {
        document.querySelectorAll('.bulk-order-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateBulkOrderSelection();
    }

    function confirmBulkDeleteOrders() {
        const selectedCount = document.querySelectorAll('.bulk-order-checkbox:checked').length;
        if (selectedCount === 0) {
            return false;
        }

        return confirm(`Xóa ${selectedCount} đơn hàng đã chọn?`);
    }

    function injectBulkDeleteToggleButton() {
        const actionHeader = document.querySelector('#orders-list-card thead tr th:last-child');
        if (!actionHeader || actionHeader.querySelector('.js-toggle-bulk-delete')) return;

        const headerInner = document.createElement('div');
        headerInner.className = 'flex items-center justify-center gap-2';

        const headerLabel = document.createElement('span');
        headerLabel.textContent = 'Hành động';

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'js-toggle-bulk-delete btn btn-sm bg-danger-50 hover:bg-danger-100 text-danger-600 rounded-lg px-2.5 py-1.5 flex items-center gap-1.5';
        button.title = 'Bật chế độ xóa nhiều';
        button.onclick = toggleBulkDeleteOrders;

        const icon = document.createElement('iconify-icon');
        icon.setAttribute('icon', 'lucide:trash-2');
        icon.className = 'text-base';

        const label = document.createElement('span');
        label.className = 'bulk-delete-toggle-label text-xs font-semibold';
        label.textContent = 'Xóa nhiều';

        button.append(icon, label);
        headerInner.append(headerLabel, button);

        actionHeader.textContent = '';
        actionHeader.appendChild(headerInner);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('bulkSelectAllOrders');
        const checkboxes = document.querySelectorAll('.bulk-order-checkbox');

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = selectAll.checked;
                });
                updateBulkOrderSelection();
            });
        }

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkOrderSelection);
        });

        updateBulkDeleteToggleButton();
        updateBulkOrderSelection();
    });
</script>
@endif

@endsection
