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

<div class="grid grid-cols-12 gap-y-6" style="row-gap: 24px;">
    <div class="col-span-12 mb-6" style="margin-bottom: 24px;">
        {{-- Statistics Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <!-- Card 1: Tổng đơn hàng -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
                <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center text-primary-600 text-2xl flex-shrink-0">
                    <iconify-icon icon="lucide:shopping-bag"></iconify-icon>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs text-neutral-500 font-medium mb-1 flex items-center gap-1.5 flex-wrap">
                        <span>Tổng đơn hàng</span>
                        <span class="text-[10px] font-normal text-neutral-400 capitalize whitespace-nowrap">({{ $dateLabel }})</span>
                    </div>
                    <div class="text-xl font-bold text-neutral-800">{{ number_format($totalOrdersCount, 0, ',', '.') }} <span class="text-sm font-medium text-neutral-500">đơn</span></div>
                </div>
            </div>

            <!-- Card 2: Tổng số tấm -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
                <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 text-2xl flex-shrink-0">
                    <iconify-icon icon="solar:layers-minimalistic-bold-duotone"></iconify-icon>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs text-neutral-500 font-medium mb-1 flex items-center gap-1.5 flex-wrap">
                        <span>Tổng số tấm</span>
                        <span class="text-[10px] font-normal text-neutral-400 capitalize whitespace-nowrap">({{ $dateLabel }})</span>
                    </div>
                    <div class="text-xl font-bold text-neutral-800">
                        {{ floatval($totalSheetsSum ?? 0) == intval($totalSheetsSum ?? 0) ? number_format($totalSheetsSum ?? 0, 0, ',', '.') : number_format($totalSheetsSum ?? 0, 1, ',', '.') }}
                        <span class="text-sm font-medium text-neutral-500">tấm</span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Tổng số mét -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-2xl flex-shrink-0">
                    <iconify-icon icon="solar:ruler-angular-bold-duotone"></iconify-icon>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs text-neutral-500 font-medium mb-1 flex items-center gap-1.5 flex-wrap">
                        <span>Tổng số mét</span>
                        <span class="text-[10px] font-normal text-neutral-400 capitalize whitespace-nowrap">({{ $dateLabel }})</span>
                    </div>
                    <div class="text-xl font-bold text-neutral-800">
                        {{ floatval($totalMetersSum ?? 0) == intval($totalMetersSum ?? 0) ? number_format($totalMetersSum ?? 0, 0, ',', '.') : rtrim(rtrim(number_format($totalMetersSum ?? 0, 2, ',', '.'), '0'), ',') }}
                        <span class="text-sm font-medium text-neutral-500">m</span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Tổng tiền hàng -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm flex items-center gap-4 transition-all hover:shadow-md">
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-2xl flex-shrink-0">
                    <iconify-icon icon="lucide:circle-dollar-sign"></iconify-icon>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs text-neutral-500 font-medium mb-1 flex items-center gap-1.5 flex-wrap">
                        <span>Tổng tiền hàng</span>
                        <span class="text-[10px] font-normal text-neutral-400 capitalize whitespace-nowrap">({{ $dateLabel }})</span>
                    </div>
                    <div class="text-xl font-bold text-neutral-800">{{ number_format($totalAmountSum, 0, ',', '.') }}₫</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12">
        <div id="orders-list-card" class="card h-full p-0 rounded-xl border-0 overflow-hidden">
            {{-- Header --}}
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                <div class="flex items-center flex-wrap gap-3">
                    {{-- Search --}}
                    <form method="GET" action="{{ route('orders.index') }}" class="navbar-search">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        @if($dateMode && $dateMode !== 'all')
                            <input type="hidden" name="date_mode" value="{{ $dateMode }}">
                            <input type="hidden" name="date_val" value="{{ $dateVal }}">
                        @endif
                        <div class="relative">
                            <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-base"></iconify-icon>
                            <input type="text" name="search" class="form-control form-control-sm border-neutral-200 rounded-lg pl-9 pr-3 w-64 md:w-80" placeholder="Tìm kiếm đơn hàng..." value="{{ $search }}">
                        </div>
                    </form>

                    {{-- Per page --}}
                    <div class="flex items-center gap-2">
                        <span class="text-base font-medium text-secondary-light mb-0">Hiển thị</span>
                        <form method="GET" action="{{ route('orders.index') }}" id="perPageForm">
                            <input type="hidden" name="search" value="{{ $search }}">
                            @if($dateMode && $dateMode !== 'all')
                                <input type="hidden" name="date_mode" value="{{ $dateMode }}">
                                <input type="hidden" name="date_val" value="{{ $dateVal }}">
                            @endif
                            <select name="per_page" class="form-select form-select-sm w-auto border-neutral-200 rounded-lg"
                                onchange="document.getElementById('perPageForm').submit()">
                                @foreach([10, 25, 50, 100] as $option)
                                <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    {{-- Bộ lọc ngày tháng chung --}}
                    <x-date-filter :action="route('orders.index')" />
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @php
                        $isViewingDrafts = request('filter_status') === 'draft';
                    @endphp

                    @if($isViewingDrafts)
                    <a href="{{ route('orders.index') }}"
                        class="btn bg-amber-500 hover:bg-amber-600 text-white text-sm btn-sm px-3 py-2 rounded-lg flex items-center gap-1.5 font-medium shadow-sm transition-all" title="Quay lại danh sách đơn hàng chính thức">
                        <iconify-icon icon="lucide:arrow-left" class="icon text-base"></iconify-icon>
                        Đang xem Đơn nháp (Quay lại)
                    </a>
                    @else
                    <a href="{{ route('orders.index', ['filter_status' => 'draft']) }}"
                        class="btn bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-sm btn-sm px-3 py-2 rounded-lg flex items-center gap-1.5 font-semibold transition-all" title="Xem tất cả đơn nháp đang lưu tạm">
                        <iconify-icon icon="lucide:file-text" class="icon text-base text-amber-600"></iconify-icon>
                        Xem đơn nháp <span class="bg-amber-200/80 text-amber-900 text-xs px-1.5 py-0.5 rounded-full font-bold ml-0.5">{{ $draftOrdersCount ?? 0 }}</span>
                    </a>
                    @endif

                    <button type="button" onclick="openModal('filter-modal')"
                        class="btn bg-light-600 text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="solar:filter-outline" class="icon text-xl line-height-1"></iconify-icon>
                        Lọc
                    </button>
                    @if(request()->filled('filter_customer_id') || request()->filled('filter_status') || request()->filled('filter_type') || request()->filled('filter_start_date') || request()->filled('filter_end_date') || ($dateMode !== 'all' && request()->filled('date_mode')))
                    <a href="{{ route('orders.index') }}" class="btn text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="solar:close-circle-outline" class="icon text-xl line-height-1"></iconify-icon>
                        Xóa lọc
                    </a>
                    @endif
                    {{-- Nút cài đặt cảnh báo deadline --}}
                    <button type="button" onclick="openDeadlineSettings()" class="btn bg-light-600 text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2" title="Cài đặt cảnh báo deadline">
                        <iconify-icon icon="lucide:settings" class="icon text-xl line-height-1"></iconify-icon>
                    </button>
                    {{-- Nút Xuất Excel toàn bộ danh sách đơn theo bộ lọc --}}
                    <a href="{{ route('orders.export', request()->query()) }}"
                        class="btn bg-emerald-600 hover:bg-emerald-700 text-white text-sm btn-sm px-3 py-2 rounded-lg flex items-center gap-1.5 shadow-sm transition"
                        title="Xuất danh sách đơn hàng theo bộ lọc ra file Excel">
                        <iconify-icon icon="solar:file-spreadsheet-bold" class="text-lg"></iconify-icon>
                        <span>Xuất Excel</span>
                    </a>
                    @can('add order')
                    <a href="{{ route('orders.create') }}"
                        class="btn btn-primary text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="ic:baseline-plus" class="icon text-xl line-height-1"></iconify-icon>
                        Tạo đơn hàng
                    </a>
                    @endcan
                    @can('delete order')
                    <button type="button" onclick="toggleBulkDeleteOrders()"
                        class="js-toggle-bulk-delete btn bg-neutral-600 hover:bg-neutral-700 text-white text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2 shadow-sm">
                        <iconify-icon icon="lucide:list-checks" class="icon text-xl line-height-1 text-white"></iconify-icon>
                        <span class="bulk-delete-toggle-label">Chọn nhiều</span>
                    </button>
                    @endcan
                </div>
            </div>

            @if($isViewingDrafts)
            <div class="mx-6 mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between text-xs text-amber-800">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="lucide:info" class="text-base text-amber-600 shrink-0"></iconify-icon>
                    <span>Đang hiển thị danh sách <strong>{{ $totalOrdersCount }} Đơn nháp (lưu tạm)</strong>. Bạn có thể bấm vào Sửa đơn để tiếp tục hoàn thiện hoặc chuyển thành đơn chính thức.</span>
                </div>
                <a href="{{ route('orders.index') }}" class="font-bold underline hover:text-amber-900 shrink-0 ml-2">← Quay lại đơn chính thức</a>
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

                <input type="hidden" name="filter_customer_id" value="{{ request('filter_customer_id') }}">
                <input type="hidden" name="filter_status" value="{{ request('filter_status') }}">
                <input type="hidden" name="filter_type" value="{{ request('filter_type') }}">
                <input type="hidden" name="filter_start_date" value="{{ request('filter_start_date') }}">
                <input type="hidden" name="filter_end_date" value="{{ request('filter_end_date') }}">
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
                        <p class="text-xs text-secondary-light mb-0">Thực hiện thao tác với các đơn hàng được chọn.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="clearBulkOrderSelection()" class="btn btn-sm bg-white border border-neutral-200 text-neutral-700 hover:bg-neutral-100 rounded-lg px-3 py-2 flex items-center gap-2">
                        <iconify-icon icon="lucide:x" class="text-base"></iconify-icon>
                        Bỏ chọn
                    </button>
                    @can('add manufacture')
                    <button type="button" onclick="openBulkManufactureModal()" class="btn btn-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg px-3 py-2 flex items-center gap-1.5 shadow-sm font-semibold cursor-pointer">
                        <iconify-icon icon="solar:box-minimalistic-bold" class="text-base"></iconify-icon>
                        Gom Lệnh SX
                    </button>
                    @endcan
                    <button type="button" id="btnExportBulk" onclick="exportBulkOrders()" class="btn btn-sm bg-success-600 hover:bg-success-700 text-white rounded-lg px-3 py-2 flex items-center gap-2">
                        <iconify-icon icon="lucide:file-spreadsheet" class="text-base"></iconify-icon>
                        Xuất Excel
                    </button>
                    <button type="button" id="btnExportBulkNesting" onclick="exportBulkNestingOrders()" class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg px-3 py-2 flex items-center gap-2">
                        <iconify-icon icon="lucide:table-2" class="text-base"></iconify-icon>
                        Xuất Nesting
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
                {{-- Flash messages --}}
                @if(session('success'))
                <div class="alert alert-success bg-success-50 text-success-600 border border-success-200 rounded-lg p-4 mb-4">
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger bg-danger-50 text-danger-600 border border-danger-200 rounded-lg p-4 mb-4">
                    {{ session('error') }}
                </div>
                @endif

                <div class="table-responsive scroll-sm">
                    <table class="table bordered-table sm-table mb-0 min-w-[1380px]">
                        <thead>
                            <tr>
                                @if($canBulkDeleteOrders)
                                <th scope="col" class="bulk-order-select-col text-center" style="width: 48px; min-width: 48px;">
                                    <input type="checkbox" id="bulkSelectAllOrders" class="form-check-input rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                                </th>
                                @endif
                                <th scope="col" class="text-center px-3" style="width: 50px;">STT</th>
                                <th scope="col" class="px-3" style="min-width: 120px;">Mã đơn</th>
                                <th scope="col" class="px-3" style="min-width: 90px;">Loại đơn</th>
                                <th scope="col" class="px-3" style="min-width: 110px;">Trạng thái</th>
                                <th scope="col" class="px-3" style="min-width: 130px;">Tên công trình</th>
                                <th scope="col" class="px-3" style="min-width: 140px;">Tên khách hàng</th>
                                <th scope="col" class="text-center whitespace-nowrap px-3" style="min-width: 95px;">Số tấm</th>
                                <th scope="col" class="text-center whitespace-nowrap px-3" style="min-width: 115px;">Tổng số mét</th>
                                <th scope="col" class="text-end whitespace-nowrap px-3" style="min-width: 115px;">Tổng tiền</th>
                                <th scope="col" class="px-3" style="min-width: 230px;">Tiến trình duyệt</th>
                                <th scope="col" class="px-3" style="min-width: 110px;">Số điện thoại</th>
                                <th scope="col" class="whitespace-nowrap px-3" style="min-width: 130px;">Ngày tạo đơn</th>
                                <th scope="col" class="whitespace-nowrap px-3" style="min-width: 130px;">Hạn đơn</th>
                                <th scope="col" class="text-center whitespace-nowrap px-3" style="width: 100px; min-width: 100px;">Hành động</th>
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
                                        data-order-code="{{ $order->order_code }}"
                                        data-type="{{ ucfirst($order->type) }}"
                                        data-status="{{ $order->status }}"
                                        form="bulkDeleteForm"
                                        class="bulk-order-checkbox form-check-input rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                                </td>
                                @endif
                                <td class="text-center px-3">{{ $stt }}</td>
                                <td class="px-3">
                                    @php
                                        // Đọc mốc cảnh báo từ cache, mặc định 0 ngày
                                        $deadlineThreshold = \Illuminate\Support\Facades\Cache::get('deadline_warning_days', 0);
                                        $daysLeft = null;
                                        $showWarning = false;
                                        if ($order->deadline && !in_array($order->status, ['completed', 'cancelled', 'draft'])) {
                                            $deadlineDate = \Carbon\Carbon::parse($order->deadline);
                                            $orderDate = \Carbon\Carbon::parse($order->order_date);
                                            $now = \Carbon\Carbon::now();
                                            // Đếm số ngày nguyên để hiển thị ra màn hình (không có số thập phân)
                                            $daysLeft = (int) $now->diffInDays($deadlineDate, false);
                                            // Đếm số giờ để so sánh cảnh báo chuẩn xác 100%
                                            $hoursLeft = $now->diffInHours($deadlineDate, false);
                                            
                                            // Không cảnh báo nếu ngày chốt đơn là ở tương lai (đơn đặt trước)
                                            $isFutureOrder = $orderDate->isFuture();
                                            $showWarning = !$isFutureOrder && $deadlineThreshold > 0 && $hoursLeft <= ($deadlineThreshold * 24);
                                        }
                                    @endphp
                                    <div class="flex flex-col items-start justify-center gap-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-base font-medium {{ $showWarning ? 'text-danger-600' : 'text-secondary-light' }}">{{ $order->order_code }}</span>
                                            @if($order->relation_type === 'rework')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Sửa tấm</span>
                                            @elseif($order->relation_type === 'warranty')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">Bảo hành</span>
                                            @elseif($order->relation_type === 'additional')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-success-100 text-success-800 border border-success-200">Bổ sung</span>
                                                @if($order->board_return_status === 'pending')
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300" title="Khách đang giữ ván chưa trả (Tạm tính công nợ)">Chờ trả ván</span>
                                                @elseif($order->board_return_status === 'returned')
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200" title="Đã xác nhận trả ván (Đã cấn trừ công nợ)">Đã trả ván</span>
                                                @endif
                                            @elseif($order->relation_type === 'reuse')
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">Tận dụng tấm</span>
                                            @endif
                                        </div>
                                        @if($showWarning)
                                            <div class="flex items-center gap-1 text-danger-500">
                                                <iconify-icon icon="lucide:alert-triangle" class="text-xs"></iconify-icon>
                                                <span class="text-xs font-semibold">
                                                    @if($daysLeft < 0)
                                                        Quá hạn {{ abs($daysLeft) }} ngày
                                                    @elseif($daysLeft == 0)
                                                        Hôm nay là hạn cuối
                                                    @else
                                                        Còn {{ $daysLeft }} ngày
                                                    @endif
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3">
                                    @if($order->type)
                                        @php
                                            $typeColors = [
                                                'acrylic' => 'bg-primary-100 text-primary-600 border border-primary-200',
                                                'min_late' => 'bg-warning-100 text-warning-600 border border-warning-200',
                                                'glass' => 'bg-info-100 text-info-600 border border-info-200',
                                                'Flywood' => 'bg-amber-100 text-amber-700 border border-amber-300',
                                                'service' => 'bg-purple-100 text-purple-700 border border-purple-300',
                                            ];
                                            $typeLabels = [
                                                'acrylic' => 'Acrylic',
                                                'min_late' => 'Min Late',
                                                'glass' => 'Glass',
                                                'Flywood' => 'Flywood',
                                                'service' => 'Dịch vụ & Vật tư',
                                            ];
                                        @endphp
                                        <span class="px-2 py-0.5 rounded border text-xs font-semibold {{ $typeColors[$order->type] ?? 'bg-neutral-100 text-neutral-600' }}">
                                            {{ $typeLabels[$order->type] ?? $order->type }}
                                        </span>
                                    @else
                                        <span class="text-neutral-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-3 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-neutral-100 text-neutral-600',
                                            'pending' => 'bg-warning-100 text-warning-700',
                                            'transferred' => 'bg-info-100 text-info-700',
                                            'in_production' => 'bg-indigo-100 text-indigo-700 border border-indigo-200',
                                            'completed' => 'bg-success-100 text-success-700',
                                            'cancelled' => 'bg-danger-100 text-danger-700',
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
                                    <div class="flex flex-col gap-1 items-start">
                                        <span class="px-2.5 py-1 rounded-full font-semibold text-xs {{ $statusColors[$order->status] ?? 'bg-neutral-100 text-neutral-600' }}">
                                            {{ $statusLabels[$order->status] ?? $order->status }}
                                        </span>
                                        @if(!empty($order->edit_reason))
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300 flex items-center gap-1" title="Lý do: {{ $order->edit_reason }}">
                                                <iconify-icon icon="lucide:alert-circle" class="text-xs text-amber-600"></iconify-icon>
                                                <span>Yêu cầu sửa</span>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3">
                                    <span class="text-base font-medium text-secondary-light">{{ $order->customer_name ?: '—' }}</span>
                                </td>
                                <td class="px-3">
                                    @if($order->customer)
                                        <a href="{{ route('customers.index', ['overview_id' => $order->customer->id]) }}" class="font-semibold text-neutral-800 hover:text-primary-600 block leading-tight">
                                            {{ $order->customer->name }}
                                        </a>
                                        <span class="text-xs text-neutral-400 block mt-0.5">{{ $order->customer->customer_code }}</span>
                                    @else
                                        <span class="text-neutral-400">—</span>
                                    @endif
                                </td>
                                <td class="text-center whitespace-nowrap px-3">
                                    @if($order->total_sheets > 0)
                                        <span class="inline-flex items-center justify-center gap-1.5 font-semibold text-neutral-800 bg-neutral-100 px-2.5 py-1 rounded-md text-xs">
                                            <iconify-icon icon="solar:layers-minimalistic-bold" class="text-neutral-500 text-sm"></iconify-icon>
                                            {{ floatval($order->total_sheets) == intval($order->total_sheets) ? number_format($order->total_sheets, 0, ',', '.') : number_format($order->total_sheets, 1, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-neutral-300">—</span>
                                    @endif
                                </td>
                                <td class="text-center whitespace-nowrap px-3">
                                    @if($order->total_meters > 0)
                                        <span class="inline-flex items-center justify-center gap-1.5 font-semibold text-primary-700 bg-primary-50 px-2.5 py-1 rounded-md text-xs border border-primary-100">
                                            <iconify-icon icon="solar:ruler-angular-bold" class="text-primary-500 text-sm"></iconify-icon>
                                            {{ floatval($order->total_meters) == intval($order->total_meters) ? number_format($order->total_meters, 0, ',', '.') : rtrim(rtrim(number_format($order->total_meters, 2, ',', '.'), '0'), ',') }} m
                                        </span>
                                    @else
                                        <span class="text-neutral-300">—</span>
                                    @endif
                                </td>
                                <td class="text-end whitespace-nowrap px-3">
                                    <span class="text-base font-semibold text-neutral-800">{{ number_format(round($order->total_amount, -3), 0, ',', '.') }}₫</span>
                                </td>
                                <td class="px-3 py-2 text-xs" style="min-width: 230px;">
                                    <div class="flex flex-col gap-1.5">
                                        {{-- BƯỚC 1: KẾ TOÁN DUYỆT --}}
                                        <div class="flex items-center justify-between gap-1.5 p-1 rounded bg-neutral-50 border border-neutral-200">
                                            <div class="flex items-center gap-1 min-w-0">
                                                <span class="font-bold text-[11px] text-neutral-600 shrink-0">1. Kế toán:</span>
                                                @if($order->accountant_approved_by)
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 truncate" title="Kế toán duyệt: {{ $order->accountantApprover?->name }} lúc {{ $order->accountant_approved_at?->format('H:i d/m/Y') }}">
                                                        <iconify-icon icon="lucide:check-circle-2" class="text-emerald-600 shrink-0"></iconify-icon>
                                                        <span class="truncate">{{ $order->accountantApprover?->name ?? 'Đã duyệt' }}</span>
                                                        <span class="text-[10px] text-emerald-600 font-normal shrink-0">{{ $order->accountant_approved_at?->format('H:i d/m') }}</span>
                                                    </span>
                                                @else
                                                    <span class="text-[11px] text-neutral-400 italic">Chưa duyệt</span>
                                                @endif
                                            </div>

                                            @can('edit order')
                                                @if(!$order->accountant_approved_by && in_array($order->status, ['draft', 'pending']))
                                                    <div class="flex items-center gap-1 shrink-0">
                                                        <form method="POST" action="{{ route('orders.update-status', $order) }}" class="m-0" onsubmit="return confirm('Kế toán xác nhận duyệt đơn {{ $order->order_code }}?')">
                                                            @csrf
                                                            <input type="hidden" name="status" value="accountant_approve">
                                                            <button type="submit" class="px-1.5 py-0.5 rounded text-[11px] font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors shadow-2xs" title="Kế toán duyệt đơn">
                                                                Duyệt
                                                            </button>
                                                        </form>
                                                        <button type="button" onclick="openRequestEditModal('{{ route('orders.update-status', $order) }}', '{{ $order->order_code }}', 'accountant')" class="px-1 py-0.5 rounded text-[11px] font-semibold bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-300 transition-colors" title="Kế toán yêu cầu sửa đơn">
                                                            Sửa
                                                        </button>
                                                    </div>
                                                @endif
                                            @endcan
                                        </div>

                                        {{-- BƯỚC 2: KỸ THUẬT DUYỆT (CHUYỂN SX) --}}
                                        <div class="flex items-center justify-between gap-1.5 p-1 rounded bg-neutral-50 border border-neutral-200">
                                            <div class="flex items-center gap-1 min-w-0">
                                                <span class="font-bold text-[11px] text-neutral-600 shrink-0">2. Kỹ thuật:</span>
                                                @if($order->technical_approved_by || $order->status === 'transferred')
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200 truncate" title="Kỹ thuật duyệt: {{ $order->technicalApprover?->name }} lúc {{ $order->technical_approved_at?->format('H:i d/m/Y') }}">
                                                        <iconify-icon icon="lucide:check-check" class="text-blue-600 shrink-0"></iconify-icon>
                                                        <span class="truncate">{{ $order->technicalApprover?->name ?? 'Đã duyệt' }}</span>
                                                        <span class="text-[10px] text-blue-600 font-normal shrink-0">{{ $order->technical_approved_at?->format('H:i d/m') }}</span>
                                                    </span>
                                                @else
                                                    <span class="text-[11px] text-neutral-400 italic">Chưa duyệt</span>
                                                @endif
                                            </div>

                                            @can('edit order')
                                                @if(!in_array($order->status, ['transferred', 'in_production', 'completed', 'cancelled']))
                                                    <div class="flex items-center gap-1 shrink-0">
                                                        <form method="POST" action="{{ route('orders.update-status', $order) }}" class="m-0" onsubmit="return confirm('Kỹ thuật xác nhận duyệt & Chuyển sản xuất đơn {{ $order->order_code }}?')">
                                                            @csrf
                                                            <input type="hidden" name="status" value="transferred">
                                                            <button type="submit" class="px-1.5 py-0.5 rounded text-[11px] font-bold bg-violet-600 hover:bg-violet-700 text-white transition-colors shadow-2xs" title="{{ $order->accountant_approved_by ? 'Kỹ thuật duyệt & Chuyển SX' : 'Duyệt & Chuyển SX (Kế toán chưa duyệt)' }}">
                                                                {{ !empty($order->edit_reason) ? 'Chuyển lại' : 'Chuyển SX' }}
                                                            </button>
                                                        </form>
                                                        @if($order->accountant_approved_by)
                                                            <button type="button" onclick="openRequestEditModal('{{ route('orders.update-status', $order) }}', '{{ $order->order_code }}', 'technical')" class="px-1 py-0.5 rounded text-[11px] font-semibold bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-300 transition-colors" title="Kỹ thuật yêu cầu sửa đơn">
                                                                Sửa
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endcan
                                        </div>

                                        {{-- THÔNG TIN TỪ CHỐI / YÊU CẦU SỬA (NẾU CÓ) --}}
                                        @if(!empty($order->edit_reason))
                                            <div class="p-1.5 rounded bg-amber-50/90 border border-amber-300 text-amber-900 text-[11px] leading-tight">
                                                <div class="flex items-center justify-between gap-1 font-semibold text-amber-950">
                                                    <span class="flex items-center gap-1 truncate">
                                                        <iconify-icon icon="lucide:alert-triangle" class="text-amber-600 shrink-0"></iconify-icon>
                                                        <span class="truncate">Sửa: {{ $order->rejecter?->name ?? 'Người kiểm tra' }} ({{ $order->rejected_step === 'technical' ? 'Kỹ thuật' : 'Kế toán' }})</span>
                                                    </span>
                                                    @if($order->rejected_at)
                                                        <span class="text-[10px] text-amber-700 font-normal shrink-0">{{ $order->rejected_at->format('H:i d/m') }}</span>
                                                    @endif
                                                </div>
                                                <div class="mt-0.5 italic text-amber-800 line-clamp-2" title="{{ $order->edit_reason }}">
                                                    "{{ $order->edit_reason }}"
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3">
                                    <span class="text-base text-secondary-light">{{ $order->phone ?? '—' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3">
                                    <span class="text-base text-secondary-light">{{ $order->order_date ? $order->order_date->format('H:i d/m/Y') : '—' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3">
                                    <span class="text-base text-secondary-light">{{ $order->deadline ? $order->deadline->format('H:i d/m/Y') : '—' }}</span>
                                </td>
                                <td class="text-center whitespace-nowrap px-3">
                                    <div class="flex items-center gap-2 justify-center">
                                        @can('view order')
                                        {{-- Xem chi tiết (luôn hiển thị ngoài) --}}
                                        <a href="{{ route('orders.show', $order) }}" class="bg-primary-100 hover:bg-primary-200 text-primary-600 font-medium w-8 h-8 flex justify-center items-center rounded-full" title="Xem chi tiết">
                                            <iconify-icon icon="lucide:eye" class="menu-icon"></iconify-icon>
                                        </a>
                                        @endcan

                                        {{-- Dropdown hành động rút gọn --}}
                                        <div class="relative">
                                            <button id="btn-dropdown-{{ $order->id }}" data-dropdown-toggle="dropdown-actions-{{ $order->id }}" data-dropdown-placement="bottom-end" class="bg-neutral-100 hover:bg-neutral-200 text-neutral-600 font-medium w-8 h-8 flex justify-center items-center rounded-full focus:outline-none" type="button" title="Thêm hành động">
                                                <iconify-icon icon="lucide:more-vertical" class="text-base"></iconify-icon>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ $canBulkDeleteOrders ? 15 : 14 }}" class="text-center py-8">
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
    <form action="{{ route('orders.index') }}" method="GET" id="orderFilterModalForm">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <input type="hidden" name="search" value="{{ $search }}">
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">

            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Khách hàng</label>
                <select name="filter_customer_id" id="filter_customer_id" class="rounded-lg w-full">
                    <option value="">-- Tất cả khách hàng --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request('filter_customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->customer_code }} - {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Trạng thái</label>
                <select name="filter_status" class="form-select rounded-lg">
                    <option value="">Tất cả</option>
                    <option value="draft" {{ request('filter_status') === 'draft' ? 'selected' : '' }}>Nháp</option>
                    <option value="pending" {{ request('filter_status') === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="edit_requested" {{ request('filter_status') === 'edit_requested' ? 'selected' : '' }}>Yêu cầu sửa đơn</option>
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
                    <option value="Flywood" {{ request('filter_type') === 'Flywood' ? 'selected' : '' }}>Flywood</option>
                    <option value="service" {{ request('filter_type') === 'service' ? 'selected' : '' }}>Dịch vụ & Vật tư</option>
                </select>
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600 mb-1.5 block">Thời gian đặt đơn</label>
                <x-flexible-date-filter :dateMode="$dateMode" :dateVal="$dateVal" :standalone="false" formId="orderFilterModalForm" prefix="modal_order_fdf" />
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
            icon.setAttribute('icon', active ? 'lucide:x' : 'lucide:list-checks');
        }

        button.title = active ? 'Thoát chế độ chọn nhiều' : 'Bật chế độ chọn nhiều';
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
        button.className = 'js-toggle-bulk-delete btn btn-sm bg-neutral-50 hover:bg-neutral-100 text-neutral-600 rounded-lg px-2.5 py-1.5 flex items-center gap-1.5';
        button.title = 'Bật chế độ chọn nhiều';
        button.onclick = toggleBulkDeleteOrders;

        const icon = document.createElement('iconify-icon');
        icon.setAttribute('icon', 'lucide:list-checks');
        icon.className = 'text-base';

        const label = document.createElement('span');
        label.className = 'bulk-delete-toggle-label text-xs font-semibold';
        label.textContent = 'Chọn nhiều';

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

<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.4.0/exceljs.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script src="{{ asset('assets/js/order-export.js') }}"></script>
<script src="{{ asset('assets/js/nesting-export.js') }}"></script>
<script>
    async function exportBulkOrders() {
        if (!window.showDirectoryPicker) {
            alert("Trình duyệt của bạn không hỗ trợ chọn thư mục lưu (chỉ hỗ trợ Chrome/Edge mới). Vui lòng cập nhật hoặc dùng trình duyệt khác.");
            return;
        }

        const selectedIds = Array.from(document.querySelectorAll('.bulk-order-checkbox:checked')).map(cb => cb.value);
        if (selectedIds.length === 0) return;

        const btn = document.getElementById('btnExportBulk');
        const originalHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<iconify-icon icon="lucide:loader" class="animate-spin text-base"></iconify-icon> Đang xử lý...';
        }

        try {
            // Yêu cầu chọn thư mục
            const directoryHandle = await window.showDirectoryPicker({ mode: 'readwrite' });

            // Lấy dữ liệu từ server
            const response = await fetch('{{ route("orders.bulk-export-data") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || ''
                },
                body: JSON.stringify({ order_ids: selectedIds })
            });

            if (!response.ok) throw new Error("Không thể lấy dữ liệu xuất Excel");
            const dataList = await response.json();

            let totalFilesCount = 0;
            // Lưu từng file
            for (let data of dataList) {
                if (typeof exportToExcel !== 'function') {
                    throw new Error("Không tìm thấy hàm exportToExcel");
                }
                const blob = await exportToExcel(data, true);
                const fileName = `Bao_Gia_${data.order_code}.xlsx`;
                
                const fileHandle = await directoryHandle.getFileHandle(fileName, { create: true });
                const writable = await fileHandle.createWritable();
                await writable.write(blob);
                await writable.close();
                totalFilesCount++;
            }

            // 2. Xuất các file Nesting (Nesting từng đơn + 1 file Phào gộp chung cho tất cả các đơn)
            if (typeof exportBulkNestingFiles === 'function') {
                const nestingFiles = await exportBulkNestingFiles(dataList, true);
                if (nestingFiles && nestingFiles.length > 0) {
                    for (let nFile of nestingFiles) {
                        const nHandle = await directoryHandle.getFileHandle(nFile.fileName, { create: true });
                        const nWritable = await nHandle.createWritable();
                        await nWritable.write(nFile.blob);
                        await nWritable.close();
                        totalFilesCount++;
                    }
                }
            }
        }

        alert(`Đã xuất thành công ${totalFilesCount} file Excel (gồm Báo giá và Nesting)!`);
        clearBulkOrderSelection();
    } catch (err) {
        console.error(err);
        if (err.name !== 'AbortError') {
            alert("Có lỗi xảy ra: " + err.message);
        }
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }
}

    async function exportBulkNestingOrders() {
        if (!window.showDirectoryPicker) {
            alert("Trình duyệt của bạn không hỗ trợ chọn thư mục lưu (chỉ hỗ trợ Chrome/Edge mới). Vui lòng cập nhật hoặc dùng trình duyệt khác.");
            return;
        }

        const selectedIds = Array.from(document.querySelectorAll('.bulk-order-checkbox:checked')).map(cb => cb.value);
        if (selectedIds.length === 0) return;

        const btn = document.getElementById('btnExportBulkNesting');
        const originalHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<iconify-icon icon="lucide:loader" class="animate-spin text-base"></iconify-icon> Đang xuất Nesting...';
        }

        try {
            // Yêu cầu chọn thư mục
            const directoryHandle = await window.showDirectoryPicker({ mode: 'readwrite' });

            // Lấy dữ liệu từ server
            const response = await fetch('{{ route("orders.bulk-export-data") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || ''
                },
                body: JSON.stringify({ order_ids: selectedIds })
            });

            if (!response.ok) throw new Error("Không thể lấy dữ liệu đơn hàng");
            const dataList = await response.json();

            let exportedCount = 0;
            if (typeof exportBulkNestingFiles === 'function') {
                const nestingFiles = await exportBulkNestingFiles(dataList, true);
                if (nestingFiles && nestingFiles.length > 0) {
                    for (let nFile of nestingFiles) {
                        const nHandle = await directoryHandle.getFileHandle(nFile.fileName, { create: true });
                        const nWritable = await nHandle.createWritable();
                        await nWritable.write(nFile.blob);
                        await nWritable.close();
                        exportedCount++;
                    }
                }
            }

            if (exportedCount === 0) {
                alert("Không có đơn hàng Acrylic nào có tấm cần xả tem Nesting trong các đơn đã chọn.");
            } else {
                alert(`Đã xuất thành công ${exportedCount} file xả tem Nesting!`);
                clearBulkOrderSelection();
            }
        } catch (err) {
            console.error(err);
            if (err.name !== 'AbortError') {
                alert("Có lỗi xảy ra: " + err.message);
            }
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    }
</script>

<!-- Modal Tiến độ sản xuất -->
<div id="productionStatsModal" style="z-index: 99999 !important;" class="fixed inset-0 hidden flex items-center justify-center bg-neutral-900/50 backdrop-blur-sm transition-all duration-300 opacity-0 p-4">
    <div style="width: 95vw; max-width: 95vw; max-height: 95vh;" class="bg-white rounded-xl shadow-2xl transform scale-95 transition-all duration-300 flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200">
            <h5 class="text-lg font-bold text-neutral-800 m-0 flex items-center gap-2">
                <iconify-icon icon="lucide:pie-chart" class="text-info-500"></iconify-icon>
                Tiến độ sản xuất - <span id="statsModalOrderCode" class="text-primary-600"></span>
            </h5>
            <button type="button" onclick="closeProductionStats()" class="text-neutral-400 hover:text-danger-500 transition-colors">
                <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
            </button>
        </div>
        
        <!-- Body -->
        <div class="p-6 overflow-y-auto custom-scrollbar">
            <div id="productionStatsLoading" class="flex flex-col items-center justify-center py-10 hidden">
                <iconify-icon icon="lucide:loader" class="animate-spin text-4xl text-primary-500 mb-3"></iconify-icon>
                <p class="text-neutral-500 font-medium">Đang tải dữ liệu...</p>
            </div>
            
            <div id="productionStatsContent" class="hidden">
                <!-- Thông tin đơn hàng hiển thị phía trên bảng -->
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="bg-info-50 rounded-lg px-4 py-3 border border-info-100">
                        <div class="text-xs text-info-500 font-medium mb-1">Thời gian chốt đơn</div>
                        <div id="statsOrderDate" class="text-sm font-bold text-neutral-800">—</div>
                    </div>
                    <div class="bg-primary-50 rounded-lg px-4 py-3 border border-primary-100">
                        <div class="text-xs text-primary-500 font-medium mb-1">Số ngày hẹn</div>
                        <div id="statsDeliveryDays" class="text-sm font-bold text-neutral-800">—</div>
                    </div>
                    <div class="bg-danger-50 rounded-lg px-4 py-3 border border-danger-100">
                        <div class="text-xs text-danger-500 font-medium mb-1">Thời gian phải hoàn thiện xong</div>
                        <div id="statsDeadline" class="text-sm font-bold text-danger-700">—</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table bordered-table sm-table mb-0 w-full text-sm">
                        <thead class="bg-neutral-50 text-center">
                            <tr>
                                <th class="font-bold text-neutral-600">STT</th>
                                <th class="font-bold text-neutral-600 text-left">Mã màu</th>
                                <th class="font-bold text-neutral-600">Tổng tấm</th>
                                <th class="font-bold text-neutral-600">Kính/CNC</th>
                                <th class="font-bold text-neutral-600">Xong Ngày 1</th>
                                <th class="font-bold text-neutral-600 bg-warning-50 text-warning-700">Đang Sản Xuất</th>
                                <th class="font-bold text-neutral-600">Xong Ngày 2</th>
                                <th class="font-bold text-neutral-600 bg-danger-50 text-danger-700">Còn Lại</th>
                            </tr>
                        </thead>
                        <tbody id="productionStatsTbody">
                            <!-- JS will populate -->
                        </tbody>
                        <tfoot id="productionStatsTfoot" class="bg-neutral-50 font-bold text-center">
                            <!-- JS will populate -->
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div id="productionStatsEmpty" class="hidden text-center py-10 text-neutral-500">
                <iconify-icon icon="lucide:inbox" class="text-5xl mb-2 opacity-50"></iconify-icon>
                <p>Không có dữ liệu vật tư nào.</p>
            </div>
        </div>
    </div>
</div>

<script>
    async function showProductionStats(orderId, orderCode) {
        const modal = document.getElementById('productionStatsModal');
        
        const loading = document.getElementById('productionStatsLoading');
        const content = document.getElementById('productionStatsContent');
        const empty = document.getElementById('productionStatsEmpty');
        const tbody = document.getElementById('productionStatsTbody');
        const tfoot = document.getElementById('productionStatsTfoot');
        
        document.getElementById('statsModalOrderCode').textContent = orderCode;
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.bg-white').classList.remove('scale-95');
            modal.querySelector('.bg-white').classList.add('scale-100');
        }, 10);
        
        loading.classList.remove('hidden');
        content.classList.add('hidden');
        empty.classList.add('hidden');
        
        try {
            const response = await fetch(`/orders/${orderId}/production-stats`);
            if (!response.ok) throw new Error('Network error');
            const result = await response.json();
            
            if (result.success && result.data.length > 0) {
                // Hiển thị thông tin đơn hàng phía trên bảng
                document.getElementById('statsOrderDate').textContent = result.order_info?.order_date || '—';
                document.getElementById('statsDeliveryDays').textContent = result.order_info?.delivery_days || '—';
                document.getElementById('statsDeadline').textContent = result.order_info?.deadline || '—';

                let html = '';
                let totalT = 0, totalC = 0, totalD1 = 0, totalP = 0, totalD2 = 0, totalR = 0;
                
                result.data.forEach((row, index) => {
                    html += `
                        <tr class="text-center border-b border-neutral-100">
                            <td>${index + 1}</td>
                            <td class="text-left font-semibold text-neutral-700">${row.order_supply_code || row.supply_name || '—'}</td>
                            <td>${row.total_count}</td>
                            <td>${row.cnc_count}</td>
                            <td>${row.day1_count}</td>
                            <td class="bg-warning-50/50 text-warning-700">${row.in_production_count}</td>
                            <td>${row.day2_count}</td>
                            <td class="bg-danger-50/50 text-danger-600">${row.remaining_count}</td>
                        </tr>
                    `;
                    totalT += row.total_count;
                    totalC += row.cnc_count;
                    totalD1 += row.day1_count;
                    totalP += row.in_production_count;
                    totalD2 += row.day2_count;
                    totalR += row.remaining_count;
                });
                
                tbody.innerHTML = html;
                
                tfoot.innerHTML = `
                    <tr class="bg-neutral-50 font-semibold">
                        <td colspan="2" class="text-right py-3">Tổng cộng:</td>
                        <td>${totalT}</td>
                        <td>${totalC}</td>
                        <td>${totalD1}</td>
                        <td>${totalP}</td>
                        <td>${totalD2}</td>
                        <td class="text-danger-600" style="background-color: #fef2f2 !important;">${totalR}</td>
                    </tr>
                `;
                
                loading.classList.add('hidden');
                content.classList.remove('hidden');
            } else {
                loading.classList.add('hidden');
                empty.classList.remove('hidden');
            }
        } catch (error) {
            console.error(error);
            loading.classList.add('hidden');
            empty.innerHTML = `<div class="text-danger-500 font-medium">Lỗi tải dữ liệu. Vui lòng thử lại.</div>`;
            empty.classList.remove('hidden');
        }
    }
    
    function closeProductionStats() {
        const modal = document.getElementById('productionStatsModal');
        modal.classList.add('opacity-0');
        modal.querySelector('.bg-white').classList.remove('scale-100');
        modal.querySelector('.bg-white').classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof TomSelect !== 'undefined' && document.getElementById('filter_customer_id')) {
            new TomSelect('#filter_customer_id', {
                allowEmptyOption: true,
                placeholder: '-- Chọn khách hàng --',
            });
        }
    });
</script>

<!-- Modal cài đặt cảnh báo deadline -->
<div id="deadlineSettingsModal" style="z-index: 99999 !important;" class="fixed inset-0 hidden flex items-center justify-center bg-neutral-900/50 backdrop-blur-sm transition-all duration-300 opacity-0 p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm transform scale-95 transition-all duration-300">
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-neutral-200">
            <h5 class="text-base font-bold text-neutral-800 m-0 flex items-center gap-2">
                <iconify-icon icon="lucide:settings" class="text-primary-500"></iconify-icon>
                Cài đặt cảnh báo deadline
            </h5>
            <button type="button" onclick="closeDeadlineSettings()" class="text-neutral-400 hover:text-danger-500 transition-colors">
                <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
            </button>
        </div>
        <!-- Body -->
        <form method="POST" action="{{ route('orders.save-deadline-setting') }}">
            @csrf
            <div class="p-5">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Hiện cảnh báo khi còn bao nhiêu ngày?</label>
                <div class="flex items-center gap-3">
                    <input type="number" name="deadline_warning_days" min="0" max="30" value="{{ \Illuminate\Support\Facades\Cache::get('deadline_warning_days', 0) }}" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 w-32 text-center text-base font-bold">
                    <span class="text-sm text-neutral-500">ngày</span>
                </div>
            </div>
            <!-- Footer -->
            <div class="flex justify-end gap-2 px-5 py-3 border-t border-neutral-100">
                <button type="button" onclick="closeDeadlineSettings()" class="btn btn-sm bg-neutral-100 text-neutral-600 rounded-lg px-4">Hủy</button>
                <button type="submit" class="btn btn-sm btn-primary rounded-lg px-4">Lưu</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Mở/đóng modal cài đặt deadline
    function openDeadlineSettings() {
        const modal = document.getElementById('deadlineSettingsModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.querySelector('.bg-white').classList.remove('scale-95');
            modal.querySelector('.bg-white').classList.add('scale-100');
        }, 10);
    }

    function closeDeadlineSettings() {
        const modal = document.getElementById('deadlineSettingsModal');
        modal.classList.add('opacity-0');
        modal.querySelector('.bg-white').classList.remove('scale-100');
        modal.querySelector('.bg-white').classList.add('scale-95');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
</script>

<!-- Modal Gom Lệnh Sản Xuất Nhanh -->
<div id="bulkManufactureModal" style="z-index: 99999 !important;" class="fixed inset-0 hidden flex items-center justify-center bg-neutral-900/50 backdrop-blur-sm transition-all duration-300 opacity-0 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full transform scale-95 transition-all duration-300 overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50/50">
            <h5 class="text-base font-bold text-neutral-800 m-0 flex items-center gap-2">
                <iconify-icon icon="solar:box-minimalistic-bold" class="text-indigo-600 text-xl"></iconify-icon>
                Gom Đơn Vào Lệnh Sản Xuất
            </h5>
            <button type="button" onclick="closeBulkManufactureModal()" class="text-neutral-400 hover:text-danger-500 transition-colors cursor-pointer">
                <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
            </button>
        </div>

        <!-- Body -->
        <form id="quickManufactureForm" onsubmit="submitQuickManufacture(event)" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-neutral-700 uppercase tracking-wider mb-1.5">Mã Lệnh Sản Xuất</label>
                <input type="text" id="quickMoCode" name="code" value="{{ \App\Models\ManufactureOrder::generateNextCode() }}" required class="form-control rounded-lg border-neutral-300 text-sm font-semibold text-neutral-800 focus:border-indigo-500 focus:ring-indigo-500">
                <p class="text-[11px] text-neutral-400 mt-1 mb-0">Mã tự động sinh theo ngày, bạn có thể chỉnh sửa nếu cần.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-neutral-700 uppercase tracking-wider mb-1.5">Các đơn hàng được gom (<span id="modalSelectedCount">0</span> đơn)</label>
                <div id="modalSelectedOrdersList" class="flex flex-wrap gap-1.5 max-h-36 overflow-y-auto p-2.5 bg-neutral-50 border border-neutral-200 rounded-lg">
                    <!-- Populated by JS -->
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-neutral-700 uppercase tracking-wider mb-1.5">Ghi chú lệnh SX (Tùy chọn)</label>
                <textarea id="quickMoNotes" name="notes" rows="2" class="form-control rounded-lg border-neutral-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ví dụ: Ưu tiên cắt ca sáng, chú ý màu sắc..."></textarea>
            </div>

            <div class="p-3 bg-indigo-50/70 border border-indigo-100 rounded-xl text-xs text-indigo-900 flex items-start gap-2.5">
                <iconify-icon icon="lucide:zap" class="text-indigo-600 text-lg shrink-0 mt-0.5"></iconify-icon>
                <span>Hệ thống sẽ <strong>tự động kích hoạt trạng thái Đang sản xuất</strong> và khởi tạo tem QR cho toàn bộ các tấm ván. Xưởng có thể quét QR gia công ngay!</span>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-neutral-100">
                <button type="button" onclick="closeBulkManufactureModal()" class="px-4 py-2.5 rounded-lg text-xs font-semibold text-neutral-600 hover:bg-neutral-100 transition-colors cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="submit" id="btnSubmitQuickMo" class="px-5 py-2.5 rounded-lg text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm flex items-center gap-1.5 transition-colors cursor-pointer">
                    <iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon>
                    Tạo & Kích hoạt SX ngay
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openBulkManufactureModal() {
    const checkedBoxes = Array.from(document.querySelectorAll('.bulk-order-checkbox:checked'));
    if (checkedBoxes.length === 0) {
        alert('Vui lòng chọn ít nhất 1 đơn hàng để gom vào Lệnh sản xuất.');
        return;
    }

    const listContainer = document.getElementById('modalSelectedOrdersList');
    listContainer.innerHTML = '';
    document.getElementById('modalSelectedCount').textContent = checkedBoxes.length;

    checkedBoxes.forEach(cb => {
        const orderCode = cb.getAttribute('data-order-code') || ('DH #' + cb.value);
        const orderType = cb.getAttribute('data-type') || '';
        const tag = document.createElement('span');
        tag.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-white border border-neutral-200 text-neutral-800 shadow-2xs';
        tag.innerHTML = `<iconify-icon icon="lucide:file-text" class="text-neutral-400"></iconify-icon> <strong>${orderCode}</strong> <span class="text-[10px] text-neutral-400">(${orderType})</span>`;
        listContainer.appendChild(tag);
    });

    const modal = document.getElementById('bulkManufactureModal');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        modal.classList.remove('opacity-0');
        modal.querySelector('.transform').classList.remove('scale-95');
    });
}

function closeBulkManufactureModal() {
    const modal = document.getElementById('bulkManufactureModal');
    modal.classList.add('opacity-0');
    modal.querySelector('.transform').classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

async function submitQuickManufacture(e) {
    e.preventDefault();
    const checkedBoxes = Array.from(document.querySelectorAll('.bulk-order-checkbox:checked'));
    if (checkedBoxes.length === 0) {
        alert('Vui lòng chọn ít nhất 1 đơn hàng.');
        return;
    }

    const orderIds = checkedBoxes.map(cb => cb.value);
    const code = document.getElementById('quickMoCode').value.trim();
    const notes = document.getElementById('quickMoNotes').value.trim();
    const btn = document.getElementById('btnSubmitQuickMo');
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<iconify-icon icon="lucide:loader" class="animate-spin text-base"></iconify-icon> Đang tạo lệnh...';

    try {
        const csrfToken = document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}';
        const response = await fetch('{{ route("manufactures.quick-create") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                order_ids: orderIds,
                code: code,
                notes: notes
            })
        });

        const data = await response.json();
        if (response.ok && data.success) {
            window.location.href = data.redirect_url;
        } else {
            alert(data.message || 'Có lỗi xảy ra khi tạo Lệnh sản xuất. Vui lòng kiểm tra lại.');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (err) {
        console.error(err);
        alert('Có lỗi kết nối đến máy chủ.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}
</script>

{{-- Khung menu dropdown của danh sách đơn hàng (đặt ngoài table-responsive để không bị lỗi UI clip che khuất) --}}
@foreach($orders as $order)
    <div id="dropdown-actions-{{ $order->id }}" class="z-50 hidden bg-white divide-y divide-neutral-100 rounded-xl shadow-lg border border-neutral-200 w-52 text-left">
        <ul class="py-2 text-sm text-neutral-700 space-y-0.5" aria-labelledby="btn-dropdown-{{ $order->id }}">
            
            @can('view order')
            {{-- Xuất Excel --}}
            <li>
                <a href="{{ route('orders.show', $order) }}?export=1" class="flex items-center gap-2 px-4 py-2 hover:bg-neutral-50 text-neutral-700 transition-colors">
                    <iconify-icon icon="lucide:file-spreadsheet" class="text-emerald-500 text-lg"></iconify-icon>
                    <span>Xuất Excel (.xlsx)</span>
                </a>
            </li>

            @if($order->type === 'acrylic')
            {{-- Tải file xả tem cho Kỹ thuật --}}
            <li>
                <a href="{{ route('orders.show', $order) }}?export_nesting=1" class="flex items-center gap-2 px-4 py-2 hover:bg-neutral-50 text-emerald-700 font-medium transition-colors">
                    <iconify-icon icon="lucide:table-2" class="text-emerald-600 text-lg"></iconify-icon>
                    <span>Tải file xả tem (Nesting)</span>
                </a>
            </li>
            @endif

            {{-- Tiến độ sản xuất --}}
            <li>
                @if(!in_array($order->status, ['draft', 'cancelled']))
                    <button type="button" onclick="showProductionStats({{ $order->id }}, '{{ $order->order_code }}')" class="w-full flex items-center gap-2 px-4 py-2 hover:bg-neutral-50 text-neutral-700 text-left transition-colors">
                        <iconify-icon icon="lucide:pie-chart" class="text-info-500 text-lg"></iconify-icon>
                        <span>Tiến độ sản xuất</span>
                    </button>
                @else
                    <button type="button" disabled class="w-full flex items-center gap-2 px-4 py-2 text-neutral-300 text-left cursor-not-allowed">
                        <iconify-icon icon="lucide:pie-chart" class="text-neutral-300 text-lg"></iconify-icon>
                        <span>Tiến độ sản xuất</span>
                    </button>
                @endif
            </li>

            <li>
                <a href="{{ route('orders.show', $order) }}?open_rework=1" class="flex items-center gap-2 px-4 py-2 hover:bg-neutral-50 text-neutral-700 transition-colors">
                    <iconify-icon icon="lucide:rotate-ccw" class="text-amber-500 text-lg"></iconify-icon>
                    <span>Tạo đơn sửa tấm</span>
                </a>
            </li>
            <li>
                <a href="{{ route('orders.show', $order) }}?open_warranty=1" class="flex items-center gap-2 px-4 py-2 hover:bg-neutral-50 text-neutral-700 transition-colors">
                    <iconify-icon icon="lucide:shield-check" class="text-purple-600 text-lg"></iconify-icon>
                    <span>Tạo đơn bảo hành</span>
                </a>
            </li>
            @if($order->type === 'acrylic')
            <li>
                <a href="{{ route('orders.reuse-create', $order) }}" class="flex items-center gap-2 px-4 py-2 hover:bg-neutral-50 text-neutral-700 transition-colors">
                    <iconify-icon icon="lucide:layers" class="text-blue-500 text-lg"></iconify-icon>
                    <span>Tạo đơn tận dụng tấm</span>
                </a>
            </li>
            @endif
            <li>
                <a href="{{ route('orders.show', $order) }}?open_additional=1" class="flex items-center gap-2 px-4 py-2 hover:bg-neutral-50 text-neutral-700 transition-colors">
                    <iconify-icon icon="lucide:plus-circle" class="text-success-500 text-lg"></iconify-icon>
                    <span>Tạo đơn bổ sung</span>
                </a>
            </li>
            @endcan

            @can('edit order')
            {{-- Kế toán duyệt --}}
            @if(!$order->accountant_approved_by && in_array($order->status, ['draft', 'pending']))
            <li class="border-t border-neutral-100 my-1 pt-1">
                <form method="POST" action="{{ route('orders.update-status', $order) }}" onsubmit="return confirm('Kế toán xác nhận duyệt đơn hàng {{ $order->order_code }}?')">
                    @csrf
                    <input type="hidden" name="status" value="accountant_approve">
                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 hover:bg-emerald-50 text-emerald-700 text-left transition-colors font-medium">
                        <iconify-icon icon="lucide:check-circle" class="text-emerald-600 text-lg"></iconify-icon>
                        <span>Kế toán duyệt</span>
                    </button>
                </form>
            </li>
            @endif

            {{-- Chuyển sản xuất (Kỹ thuật duyệt) --}}
            @if(in_array($order->status, ['draft', 'pending']))
            <li class="{{ !$order->accountant_approved_by ? '' : 'border-t border-neutral-100 my-1 pt-1' }}">
                <form method="POST" action="{{ route('orders.update-status', $order) }}" onsubmit="return confirm('Xác nhận chuyển đơn hàng {{ $order->order_code }} sang sản xuất?')">
                    @csrf
                    <input type="hidden" name="status" value="transferred">
                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 hover:bg-violet-50 text-violet-700 text-left transition-colors font-medium">
                        <iconify-icon icon="lucide:factory" class="text-violet-600 text-lg"></iconify-icon>
                        <span>{{ !empty($order->edit_reason) ? 'Chuyển SX lại' : 'Chuyển sản xuất' }}</span>
                    </button>
                </form>
            </li>
            @endif

            {{-- Yêu cầu sửa đơn --}}
            @if(in_array($order->status, ['draft', 'pending', 'transferred']))
            <li class="{{ in_array($order->status, ['draft', 'pending']) ? '' : 'border-t border-neutral-100 my-1 pt-1' }}">
                <button type="button" onclick="openRequestEditModal('{{ route('orders.update-status', $order) }}', '{{ $order->order_code }}', '{{ $order->accountant_approved_by ? 'technical' : 'accountant' }}')" class="w-full flex items-center gap-2 px-4 py-2 hover:bg-amber-50 text-amber-700 text-left transition-colors font-medium">
                    <iconify-icon icon="lucide:alert-circle" class="text-amber-600 text-lg"></iconify-icon>
                    <span>Yêu cầu sửa đơn</span>
                </button>
            </li>
            @endif

            {{-- Chỉnh sửa đơn hàng --}}
            <li class="{{ in_array($order->status, ['draft', 'pending']) ? '' : 'border-t border-neutral-100 my-1 pt-1' }}">
                @if($order->status !== 'cancelled')
                    <a href="{{ route('orders.edit', $order) }}" class="flex items-center gap-2 px-4 py-2 hover:bg-neutral-50 text-neutral-700 transition-colors">
                        <iconify-icon icon="lucide:edit" class="text-primary-500 text-lg"></iconify-icon>
                        <span>Chỉnh sửa đơn</span>
                    </a>
                @else
                    <button type="button" disabled class="w-full flex items-center gap-2 px-4 py-2 text-neutral-300 text-left cursor-not-allowed" title="Đơn hàng đã bị hủy">
                        <iconify-icon icon="lucide:edit" class="text-neutral-300 text-lg"></iconify-icon>
                        <span>Chỉnh sửa đơn</span>
                    </button>
                @endif
            </li>

            {{-- Hủy nhanh đơn (Chỉ cho phép khi chưa hoàn thành và chưa bị hủy) --}}
            @if(!in_array($order->status, ['cancelled', 'completed']))
            <li>
                <form method="POST" action="{{ route('orders.update-status', $order) }}" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')">
                    @csrf
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 hover:bg-danger-50 text-danger-600 text-left transition-colors">
                        <iconify-icon icon="lucide:ban" class="text-danger-500 text-lg"></iconify-icon>
                        <span>Hủy đơn hàng</span>
                    </button>
                </form>
            </li>
            @endif
            @endcan

            @can('delete order')
            {{-- Xóa đơn --}}
            <li class="border-t border-neutral-100 my-1 pt-1">
                <form method="POST" action="{{ route('orders.destroy', $order) }}" onsubmit="return confirm('Xóa đơn hàng này?')">
                    @csrf @method('DELETE')
                    <input type="hidden" name="search" value="{{ $search }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="hidden" name="filter_order_code" value="{{ request('filter_order_code') }}">
                    <input type="hidden" name="filter_customer_name" value="{{ request('filter_customer_name') }}">
                    <input type="hidden" name="filter_status" value="{{ request('filter_status') }}">
                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 hover:bg-danger-50 text-danger-600 text-left transition-colors">
                        <iconify-icon icon="fluent:delete-24-regular" class="text-danger-500 text-lg"></iconify-icon>
                        <span>Xóa đơn hàng</span>
                    </button>
                </form>
            </li>
            @endcan
        </ul>
    </div>
@endforeach
@include('orders._request_edit_modal')
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof TomSelect !== 'undefined' && document.getElementById('filter_customer_id')) {
            new TomSelect('#filter_customer_id', {
                allowEmptyOption: true,
                placeholder: '-- Tất cả khách hàng --',
                maxOptions: 500,
            });
        }
    });
</script>
@endpush
