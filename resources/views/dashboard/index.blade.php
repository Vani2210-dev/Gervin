@extends('layout.layout')

@php
    $title = 'Bảng điều khiển';
    $subTitle = 'Tổng quan hệ thống & Vận hành sản xuất';
    $script = '';
@endphp

@section('content')

    {{-- Banner chào mừng & Thao tác nhanh --}}
    <div class="card border border-neutral-200 rounded-2xl mb-6 bg-gradient-to-r from-primary-600 via-indigo-600 to-primary-800 text-white relative overflow-hidden shadow-sm">
        <div class="absolute -right-16 -top-16 w-56 h-56 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
        <div class="absolute right-1/4 -bottom-10 w-40 h-40 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
        <div class="card-body p-6 relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/20 text-white uppercase tracking-wider">Hệ thống điều hành</span>
                    <span class="text-white/70 text-xs">Gervin Wood ERP</span>
                </div>
                <h3 class="text-2xl lg:text-3xl font-bold text-white mb-1.5">Xin chào, {{ auth()->user()->name }}! 👋</h3>
                <p class="text-white/80 text-sm font-medium max-w-xl mb-0">
                    Bảng điều khiển tổng hợp dữ liệu sản xuất, đơn hàng, tiến độ phân xưởng và báo cáo doanh thu thời gian thực.
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <div class="bg-white/15 backdrop-blur-md border border-white/20 text-white px-4 py-2.5 rounded-xl shadow-sm text-center">
                    <span class="block text-[11px] uppercase tracking-wider font-semibold text-white/80">Hôm nay</span>
                    <span class="text-base font-bold text-yellow-300">{{ now()->format('d/m/Y') }}</span>
                </div>
                @can('add order')
                <div class="flex items-center gap-2">
                    <a href="{{ route('orders.create.type', ['type' => 'acrylic']) }}" class="btn bg-white hover:bg-neutral-100 text-primary-700 font-semibold px-3.5 py-2.5 rounded-xl text-xs flex items-center gap-1.5 shadow-sm transition">
                        <iconify-icon icon="lucide:plus" class="text-base"></iconify-icon>
                        Tạo đơn mới
                    </a>
                </div>
                @endcan
            </div>
        </div>
    </div>

    {{-- KHỐI KPI CHỦ ĐẠO (4 THẺ LỚN) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        
        {{-- 1. Doanh thu tháng này --}}
        @if(auth()->user()->can('view revenue report') || auth()->user()->hasRole('Admin') || auth()->user()->can('view order'))
        <div class="card border border-neutral-200 rounded-2xl p-5 bg-white shadow-none hover:shadow-md transition-shadow relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Doanh thu tháng {{ now()->month }}</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <iconify-icon icon="solar:wallet-money-bold-duotone" class="text-2xl"></iconify-icon>
                </div>
            </div>
            <h4 class="text-2xl font-bold text-neutral-900 mb-1">
                {{ number_format($stats['revenue_month'], 0, ',', '.') }} <span class="text-sm font-semibold text-neutral-500">đ</span>
            </h4>
            
            {{-- Thanh tiến độ mục tiêu --}}
            <div class="mt-3">
                <div class="flex justify-between items-center text-xs text-neutral-500 mb-1.5 font-medium">
                    <span>Mục tiêu: {{ number_format($stats['revenue_target'], 0, ',', '.') }}đ</span>
                    <span class="font-bold text-emerald-600">{{ $stats['revenue_progress'] }}%</span>
                </div>
                <div class="w-full bg-neutral-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-full rounded-full transition-all duration-500" style="width: {{ $stats['revenue_progress'] }}%"></div>
                </div>
            </div>
            
            <div class="mt-3 pt-2.5 border-t border-neutral-100 flex items-center justify-between text-xs text-neutral-500">
                <span>Hôm nay: <strong class="text-neutral-800">{{ number_format($stats['revenue_today'], 0, ',', '.') }}đ</strong></span>
                <a href="{{ route('reports.revenue') }}" class="text-primary-600 hover:text-primary-700 font-semibold flex items-center gap-0.5">
                    Chi tiết <iconify-icon icon="lucide:chevron-right" class="text-xs"></iconify-icon>
                </a>
            </div>
        </div>
        @else
        <div class="card border border-neutral-200 rounded-2xl p-5 bg-white shadow-none">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Đơn hàng Acrylic</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <iconify-icon icon="solar:layers-minimalistic-bold-duotone" class="text-2xl"></iconify-icon>
                </div>
            </div>
            <h4 class="text-2xl font-bold text-neutral-900 mb-1">{{ number_format($stats['acrylic_orders']) }}</h4>
            <p class="text-xs text-neutral-500 mb-0">Đơn hàng gia công ván Acrylic</p>
        </div>
        @endif

        {{-- 2. Giá trị đơn hàng đang sản xuất (WIP) --}}
        @can('view order')
        <div class="card border border-neutral-200 rounded-2xl p-5 bg-white shadow-none hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Đang sản xuất & xử lý</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <iconify-icon icon="solar:hourglass-line-bold-duotone" class="text-2xl"></iconify-icon>
                </div>
            </div>
            <h4 class="text-2xl font-bold text-neutral-900 mb-1">
                {{ number_format($stats['total_wip_value'], 0, ',', '.') }} <span class="text-sm font-semibold text-neutral-500">đ</span>
            </h4>
            <div class="mt-3 flex items-center justify-between text-xs text-neutral-500 border-t border-neutral-100 pt-2.5">
                <span>Chờ xử lý: <strong class="text-warning-600 font-semibold">{{ $stats['pending_orders'] }}</strong></span>
                <span>Chuyển SX: <strong class="text-info-600 font-semibold">{{ $stats['transferred_orders'] }}</strong></span>
                <span>Đang SX: <strong class="text-indigo-600 font-semibold">{{ $stats['in_production_orders'] }}</strong></span>
            </div>
        </div>
        @endcan

        {{-- 3. Tổng công nợ khách hàng --}}
        @can('view customer')
        <div class="card border border-neutral-200 rounded-2xl p-5 bg-white shadow-none hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Công nợ khách hàng</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <iconify-icon icon="solar:bill-list-bold-duotone" class="text-2xl"></iconify-icon>
                </div>
            </div>
            <h4 class="text-2xl font-bold text-neutral-900 mb-1">
                {{ number_format($stats['total_customer_debt'], 0, ',', '.') }} <span class="text-sm font-semibold text-neutral-500">đ</span>
            </h4>
            <div class="mt-3 flex items-center justify-between text-xs text-neutral-500 border-t border-neutral-100 pt-2.5">
                <span>Khách hàng có nợ: <strong class="text-amber-600 font-semibold">{{ $stats['customers_with_debt_count'] }} / {{ $stats['total_customers'] }}</strong></span>
                <a href="{{ route('customers.index') }}" class="text-primary-600 hover:text-primary-700 font-semibold">Khách hàng →</a>
            </div>
        </div>
        @endcan

        {{-- 4. Tổng đơn hàng toàn hệ thống --}}
        @can('view order')
        <div class="card border border-neutral-200 rounded-2xl p-5 bg-white shadow-none hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Tổng đơn hàng</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <iconify-icon icon="solar:clipboard-list-bold-duotone" class="text-2xl"></iconify-icon>
                </div>
            </div>
            <div class="flex items-baseline gap-2 mb-1">
                <h4 class="text-2xl font-bold text-neutral-900 mb-0">{{ number_format($stats['total_orders']) }}</h4>
                <span class="text-xs font-semibold text-success-600 bg-success-50 px-2 py-0.5 rounded-md">+{{ $stats['orders_today'] }} hôm nay</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs text-neutral-500 border-t border-neutral-100 pt-2.5">
                <span>Acrylic: <strong>{{ $stats['acrylic_orders'] }}</strong></span>
                <span>Kính: <strong>{{ $stats['glass_orders'] }}</strong></span>
                <span>Min-Late: <strong>{{ $stats['min_late_orders'] }}</strong></span>
            </div>
        </div>
        @endcan

    </div>

    {{-- KHỐI BIỂU ĐỒ PHÂN TÍCH DOANH THU & CƠ CẤU ĐƠN HÀNG --}}
    @if(auth()->user()->can('view revenue report') || auth()->user()->hasRole('Admin') || auth()->user()->can('view order'))
    <div class="grid grid-cols-12 gap-6 mb-6">
        
        {{-- Biểu đồ 7 ngày: Doanh thu & Số đơn --}}
        <div class="col-span-12 lg:col-span-8">
            <div class="card border border-neutral-200 rounded-2xl p-5 bg-white shadow-none h-full flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                    <div>
                        <h5 class="text-base font-bold text-neutral-800 mb-0.5 flex items-center gap-2">
                            <iconify-icon icon="solar:chart-square-bold-duotone" class="text-primary-600 text-xl"></iconify-icon>
                            Xu hướng Doanh thu & Đơn hàng (7 ngày qua)
                        </h5>
                        <p class="text-xs text-neutral-500 mb-0">Thống kê doanh số hoàn thành và số lượng đơn hàng mới mỗi ngày</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-medium">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-primary-600 inline-block"></span>
                            <span class="text-neutral-600">Doanh thu (VNĐ)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                            <span class="text-neutral-600">Số đơn</span>
                        </div>
                    </div>
                </div>

                <div id="weeklyRevenueOrderChart" class="w-full h-[300px]"></div>
            </div>
        </div>

        {{-- Biểu đồ Donut: Cơ cấu doanh thu theo loại sản phẩm --}}
        <div class="col-span-12 lg:col-span-4">
            <div class="card border border-neutral-200 rounded-2xl p-5 bg-white shadow-none h-full flex flex-col justify-between">
                <div class="mb-4">
                    <h5 class="text-base font-bold text-neutral-800 mb-0.5 flex items-center gap-2">
                        <iconify-icon icon="solar:pie-chart-2-bold-duotone" class="text-primary-600 text-xl"></iconify-icon>
                        Cơ cấu Loại đơn hàng
                    </h5>
                    <p class="text-xs text-neutral-500 mb-0">Tỷ trọng số lượng đơn theo ngành sản phẩm</p>
                </div>

                <div id="orderTypeDonutChart" class="w-full flex justify-center py-2"></div>

                <div class="mt-4 pt-3 border-t border-neutral-100 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                            <span class="text-neutral-700 font-medium">Acrylic</span>
                        </div>
                        <span class="font-bold text-neutral-900">{{ number_format($stats['acrylic_orders']) }} đơn ({{ number_format($stats['acrylic_revenue'], 0, ',', '.') }}đ)</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                            <span class="text-neutral-700 font-medium">Cánh kính</span>
                        </div>
                        <span class="font-bold text-neutral-900">{{ number_format($stats['glass_orders']) }} đơn ({{ number_format($stats['glass_revenue'], 0, ',', '.') }}đ)</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <span class="text-neutral-700 font-medium">Min-Late</span>
                        </div>
                        <span class="font-bold text-neutral-900">{{ number_format($stats['min_late_orders']) }} đơn ({{ number_format($stats['min_late_revenue'], 0, ',', '.') }}đ)</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
    @endif

    {{-- KHỐI PHÂN XƯỞNG SẢN XUẤT & QUY TRÌNH (8 CÔNG ĐOẠN) --}}
    @canany(['view pressing', 'view cnc', 'view edge banding', 'view finishing', 'view qc', 'view packing', 'view dispatch', 'view delivery', 'view qr device'])
    <div class="mb-6">
        <div class="flex items-center justify-between mb-3.5">
            <div>
                <h5 class="text-base font-bold text-neutral-800 mb-0 flex items-center gap-2">
                    <iconify-icon icon="solar:box-bold-duotone" class="text-primary-600 text-xl"></iconify-icon>
                    Phân xưởng Sản xuất & Vận hành
                </h5>
                <span class="text-xs text-neutral-500">Truy cập nhanh các công đoạn gia công và đóng gói giao hàng</span>
            </div>
            <div class="flex items-center gap-3 text-xs font-semibold text-neutral-600">
                <span class="flex items-center gap-1 bg-white border border-neutral-200 px-3 py-1.5 rounded-lg">
                    <iconify-icon icon="lucide:qr-code" class="text-primary-600 text-sm"></iconify-icon>
                    {{ $stats['total_qr_devices'] }} Thiết bị QR ({{ $stats['qr_scans_today'] }} lượt quét hôm nay)
                </span>
            </div>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3.5">
            {{-- 1. Ép ván --}}
            @can('view pressing')
            <a href="{{ route('processes.pressing') }}" class="card border border-neutral-200 rounded-2xl hover:border-cyan-500 hover:shadow-md transition-all bg-white group p-3.5 text-center">
                <div class="w-11 h-11 mx-auto bg-cyan-50 text-cyan-600 group-hover:bg-cyan-600 group-hover:text-white rounded-xl flex justify-center items-center mb-2.5 transition-colors">
                    <iconify-icon icon="lucide:monitor" class="text-xl"></iconify-icon>
                </div>
                <h6 class="text-xs font-bold text-neutral-800 mb-0.5">1. Ép ván</h6>
                <span class="text-[11px] text-neutral-400">Tiến độ ép</span>
            </a>
            @endcan

            {{-- 2. CNC --}}
            @can('view cnc')
            <a href="{{ route('processes.cnc') }}" class="card border border-neutral-200 rounded-2xl hover:border-orange-500 hover:shadow-md transition-all bg-white group p-3.5 text-center">
                <div class="w-11 h-11 mx-auto bg-orange-50 text-orange-600 group-hover:bg-orange-600 group-hover:text-white rounded-xl flex justify-center items-center mb-2.5 transition-colors">
                    <iconify-icon icon="lucide:scissors" class="text-xl"></iconify-icon>
                </div>
                <h6 class="text-xs font-bold text-neutral-800 mb-0.5">2. Cắt CNC</h6>
                <span class="text-[11px] text-neutral-400">Quét CNC</span>
            </a>
            @endcan

            {{-- 3. Dán cạnh --}}
            @can('view edge banding')
            <a href="{{ route('processes.edge-banding') }}" class="card border border-neutral-200 rounded-2xl hover:border-blue-500 hover:shadow-md transition-all bg-white group p-3.5 text-center">
                <div class="w-11 h-11 mx-auto bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white rounded-xl flex justify-center items-center mb-2.5 transition-colors">
                    <iconify-icon icon="lucide:layers" class="text-xl"></iconify-icon>
                </div>
                <h6 class="text-xs font-bold text-neutral-800 mb-0.5">3. Dán cạnh</h6>
                <span class="text-[11px] text-neutral-400">Đo mét chỉ</span>
            </a>
            @endcan

            {{-- 4. Làm đẹp --}}
            @can('view finishing')
            <a href="{{ route('processes.finishing') }}" class="card border border-neutral-200 rounded-2xl hover:border-purple-500 hover:shadow-md transition-all bg-white group p-3.5 text-center">
                <div class="w-11 h-11 mx-auto bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white rounded-xl flex justify-center items-center mb-2.5 transition-colors">
                    <iconify-icon icon="lucide:brush" class="text-xl"></iconify-icon>
                </div>
                <h6 class="text-xs font-bold text-neutral-800 mb-0.5">4. Làm đẹp</h6>
                <span class="text-[11px] text-neutral-400">Bề mặt gỗ</span>
            </a>
            @endcan

            {{-- 5. QC --}}
            @can('view qc')
            <a href="{{ route('processes.qc') }}" class="card border border-neutral-200 rounded-2xl hover:border-emerald-500 hover:shadow-md transition-all bg-white group p-3.5 text-center">
                <div class="w-11 h-11 mx-auto bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white rounded-xl flex justify-center items-center mb-2.5 transition-colors">
                    <iconify-icon icon="lucide:check-circle" class="text-xl"></iconify-icon>
                </div>
                <h6 class="text-xs font-bold text-neutral-800 mb-0.5">5. QC Kiểm soát</h6>
                <span class="text-[11px] text-neutral-400">Kiểm tra lỗi</span>
            </a>
            @endcan

            {{-- 6. Đóng gói --}}
            @can('view packing')
            <a href="{{ route('processes.packing') }}" class="card border border-neutral-200 rounded-2xl hover:border-slate-500 hover:shadow-md transition-all bg-white group p-3.5 text-center">
                <div class="w-11 h-11 mx-auto bg-slate-100 text-slate-700 group-hover:bg-slate-700 group-hover:text-white rounded-xl flex justify-center items-center mb-2.5 transition-colors">
                    <iconify-icon icon="lucide:package" class="text-xl"></iconify-icon>
                </div>
                <h6 class="text-xs font-bold text-neutral-800 mb-0.5">6. Đóng gói</h6>
                <span class="text-[11px] font-semibold text-slate-600">{{ $stats['packages_total'] }} kiện</span>
            </a>
            @endcan

            {{-- 7. Xuất xưởng --}}
            @can('view dispatch')
            <a href="{{ route('processes.dispatch') }}" class="card border border-neutral-200 rounded-2xl hover:border-indigo-500 hover:shadow-md transition-all bg-white group p-3.5 text-center">
                <div class="w-11 h-11 mx-auto bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white rounded-xl flex justify-center items-center mb-2.5 transition-colors">
                    <iconify-icon icon="lucide:truck" class="text-xl"></iconify-icon>
                </div>
                <h6 class="text-xs font-bold text-neutral-800 mb-0.5">7. Xuất xưởng</h6>
                <span class="text-[11px] font-semibold text-indigo-600">{{ $stats['packages_ready_dispatch'] }} chờ xuất</span>
            </a>
            @endcan

            {{-- 8. Giao hàng --}}
            @can('view delivery')
            <a href="{{ route('processes.delivery') }}" class="card border border-neutral-200 rounded-2xl hover:border-teal-500 hover:shadow-md transition-all bg-white group p-3.5 text-center">
                <div class="w-11 h-11 mx-auto bg-teal-50 text-teal-600 group-hover:bg-teal-600 group-hover:text-white rounded-xl flex justify-center items-center mb-2.5 transition-colors">
                    <iconify-icon icon="lucide:navigation" class="text-xl"></iconify-icon>
                </div>
                <h6 class="text-xs font-bold text-neutral-800 mb-0.5">8. Giao hàng</h6>
                <span class="text-[11px] font-semibold text-teal-600">{{ $stats['packages_dispatched'] }} đang giao</span>
            </a>
            @endcan
        </div>
    </div>
    @endcanany

    {{-- KHỐI CẢNH BÁO DEADLINE & KHO TẤM DƯ & TOP KHÁCH HÀNG --}}
    <div class="grid grid-cols-12 gap-6 mb-6">
        
        {{-- Cột trái: Đơn hàng khẩn cấp & Cảnh báo hạn giao --}}
        <div class="col-span-12 lg:col-span-6">
            <div class="card border border-neutral-200 rounded-2xl p-5 bg-white shadow-none h-full">
                <div class="flex items-center justify-between mb-4">
                    <h5 class="text-base font-bold text-neutral-800 mb-0 flex items-center gap-2">
                        <iconify-icon icon="solar:danger-triangle-bold-duotone" class="text-danger-600 text-xl"></iconify-icon>
                        Cảnh báo Đơn hàng Gần hạn / Quá hạn
                    </h5>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-danger-50 text-danger-600">
                        {{ $urgentOrders->count() }} đơn ưu tiên
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($urgentOrders as $urgent)
                        @php
                            $isOverdue = $urgent->deadline && $urgent->deadline->isPast();
                        @endphp
                        <div class="p-3.5 rounded-xl border {{ $isOverdue ? 'border-danger-200 bg-danger-50/40' : 'border-warning-200 bg-warning-50/40' }} flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-sm text-neutral-900">{{ $urgent->order_code }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded font-semibold {{ $urgent->type === 'acrylic' ? 'bg-primary-100 text-primary-700' : ($urgent->type === 'glass' ? 'bg-cyan-100 text-cyan-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ strtoupper($urgent->type) }}
                                    </span>
                                </div>
                                <div class="text-xs text-neutral-600 truncate">
                                    Khách: <strong class="text-neutral-800">{{ $urgent->customer_name }}</strong> • {{ number_format(round($urgent->total_amount, -3), 0, ',', '.') }}đ
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="block text-xs font-bold {{ $isOverdue ? 'text-danger-600' : 'text-warning-700' }}">
                                    {{ $isOverdue ? 'Quá hạn' : 'Sắp đến hạn' }}
                                </span>
                                <span class="text-[11px] text-neutral-500">
                                    {{ $urgent->deadline ? $urgent->deadline->format('H:i d/m') : '—' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-neutral-400 text-xs">
                            <iconify-icon icon="lucide:check-circle-2" class="text-3xl text-success-500 mb-1 inline-block"></iconify-icon>
                            <p class="mb-0 font-medium text-neutral-600">Tuyệt vời! Không có đơn hàng nào bị quá hạn hoặc cần gấp trong 48h tới.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Cột phải: Kho DC (Tấm dư) & Top Khách hàng --}}
        <div class="col-span-12 lg:col-span-6 flex flex-col gap-6">
            
            {{-- Kho DC Tấm dư & Vật tư --}}
            <div class="card border border-neutral-200 rounded-2xl p-5 bg-white shadow-none">
                <div class="flex items-center justify-between mb-3.5">
                    <h5 class="text-base font-bold text-neutral-800 mb-0 flex items-center gap-2">
                        <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="text-orange-500 text-xl"></iconify-icon>
                        Kho DC (Tấm dư sau CNC) & Vật tư
                    </h5>
                    @can('view dc stock')
                    <a href="{{ route('dc-stocks.index') }}" class="text-xs text-primary-600 hover:text-primary-700 font-semibold">Xem kho DC →</a>
                    @endcan
                </div>

                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 rounded-xl bg-orange-50/60 border border-orange-100">
                        <span class="text-[11px] font-semibold text-orange-700 uppercase block mb-1">DC Sẵn sàng</span>
                        <h5 class="text-xl font-bold text-orange-900 mb-0">{{ number_format($stats['total_dc_stocks']) }}</h5>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50/60 border border-emerald-100">
                        <span class="text-[11px] font-semibold text-emerald-700 uppercase block mb-1">DC Đã tái dùng</span>
                        <h5 class="text-xl font-bold text-emerald-900 mb-0">{{ number_format($stats['total_dc_used']) }}</h5>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50/60 border border-blue-100">
                        <span class="text-[11px] font-semibold text-blue-700 uppercase block mb-1">Kho vật tư</span>
                        <h5 class="text-xl font-bold text-blue-900 mb-0">{{ number_format($stats['total_warehouses']) }}</h5>
                    </div>
                </div>
            </div>

            {{-- Top Khách hàng --}}
            @can('view customer')
            <div class="card border border-neutral-200 rounded-2xl p-5 bg-white shadow-none flex-grow">
                <div class="flex items-center justify-between mb-3.5">
                    <h5 class="text-base font-bold text-neutral-800 mb-0 flex items-center gap-2">
                        <iconify-icon icon="solar:star-bold-duotone" class="text-yellow-500 text-xl"></iconify-icon>
                        Top Khách hàng tiêu biểu
                    </h5>
                    <a href="{{ route('customers.index') }}" class="text-xs text-primary-600 hover:text-primary-700 font-semibold">Tất cả khách hàng →</a>
                </div>

                <div class="space-y-2.5">
                    @forelse($topCustomers as $index => $customer)
                    <div class="flex items-center justify-between text-xs py-1.5 border-b border-neutral-100 last:border-0">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-5 h-5 rounded-full bg-neutral-100 text-neutral-700 font-bold flex items-center justify-center shrink-0 text-[10px]">
                                {{ $index + 1 }}
                            </span>
                            <span class="font-semibold text-neutral-800 truncate">{{ $customer->name }}</span>
                        </div>
                        <div class="text-right shrink-0">
                            <strong class="text-neutral-900">{{ number_format($customer->orders_sum_total_amount ?? 0, 0, ',', '.') }}đ</strong>
                            <span class="text-neutral-400 text-[11px]">({{ $customer->orders_count }} đơn)</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-neutral-400 text-xs text-center py-2 mb-0">Chưa có dữ liệu đối tác</p>
                    @endforelse
                </div>
            </div>
            @endcan

        </div>

    </div>

    {{-- KHỐI DANH SÁCH ĐƠN HÀNG MỚI NHẤT --}}
    @can('view order')
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12">
            <div class="card p-0 rounded-2xl border border-neutral-200 overflow-hidden bg-white shadow-none">
                <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h6 class="text-base font-bold text-neutral-800 mb-0.5 flex items-center gap-2">
                            <iconify-icon icon="solar:history-bold-duotone" class="text-primary-600 text-xl"></iconify-icon>
                            Đơn hàng mới tiếp nhận
                        </h6>
                        <p class="text-xs text-neutral-500 mb-0">Theo dõi 10 đơn hàng mới nhất và tiến độ xử lý</p>
                    </div>
                    <a href="{{ route('orders.index') }}" class="text-xs text-primary-600 hover:text-primary-700 font-semibold flex items-center gap-1">
                        Xem tất cả đơn hàng
                        <iconify-icon icon="solar:alt-arrow-right-linear" class="text-base"></iconify-icon>
                    </a>
                </div>
                
                <div class="card-body p-0">
                    <div class="overflow-x-auto scroll-sm">
                        <table class="table bordered-table sm-table mb-0 table-auto w-full">
                            <thead>
                                <tr class="bg-neutral-50/60">
                                    <th class="px-6 py-3 text-left text-xs font-bold text-neutral-600 uppercase tracking-wider">Mã đơn</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-neutral-600 uppercase tracking-wider">Loại đơn</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-neutral-600 uppercase tracking-wider">Khách hàng</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-neutral-600 uppercase tracking-wider">Hạn giao (Deadline)</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-neutral-600 uppercase tracking-wider">Tổng tiền</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-neutral-600 uppercase tracking-wider">Trạng thái</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-neutral-600 uppercase tracking-wider">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100">
                                @forelse($recentOrders as $order)
                                <tr class="hover:bg-neutral-50/50 transition-colors">
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <a href="{{ route('orders.show', $order) }}" class="text-sm font-bold text-primary-600 hover:underline">
                                            {{ $order->order_code }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        @php
                                            $typeColors = [
                                                'acrylic' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
                                                'min_late' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                                'glass' => 'bg-cyan-50 text-cyan-700 border border-cyan-200',
                                            ];
                                            $typeLabels = [
                                                'acrylic' => 'Acrylic',
                                                'min_late' => 'Min-Late',
                                                'glass' => 'Cánh kính',
                                            ];
                                        @endphp
                                        <span class="px-2 py-0.5 rounded text-xs font-bold {{ $typeColors[$order->type] ?? 'bg-neutral-100 text-neutral-600' }}">
                                            {{ $typeLabels[$order->type] ?? strtoupper($order->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <span class="text-xs font-medium text-neutral-800">{{ $order->customer_name }}</span>
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <span class="text-xs text-neutral-600">{{ $order->deadline ? $order->deadline->format('H:i d/m/Y') : '—' }}</span>
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <span class="text-xs font-bold text-neutral-900">{{ number_format(round($order->total_amount, -3), 0, ',', '.') }}đ</span>
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'draft' => 'bg-neutral-100 text-neutral-600',
                                                'pending' => 'bg-warning-50 text-warning-700 border border-warning-200',
                                                'transferred' => 'bg-info-50 text-info-700 border border-info-200',
                                                'in_production' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
                                                'completed' => 'bg-success-50 text-success-700 border border-success-200',
                                                'cancelled' => 'bg-danger-50 text-danger-700 border border-danger-200',
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
                                        <span class="px-2.5 py-1 rounded-md text-xs font-semibold {{ $statusColors[$order->status] ?? 'bg-neutral-100 text-neutral-600' }}">
                                            {{ $statusLabels[$order->status] ?? $order->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap text-center">
                                        <div class="flex items-center gap-1.5 justify-center">
                                            @can('view order')
                                            <a href="{{ route('orders.show', $order) }}" class="bg-primary-50 hover:bg-primary-100 text-primary-600 font-medium w-7 h-7 flex justify-center items-center rounded-lg transition" title="Xem chi tiết">
                                                <iconify-icon icon="lucide:eye" class="text-sm"></iconify-icon>
                                            </a>
                                            @endcan
                                            @can('edit order')
                                            @if(!in_array($order->status, ['in_production', 'cancelled']))
                                            <a href="{{ route('orders.edit', $order) }}" class="bg-success-50 hover:bg-success-100 text-success-600 font-medium w-7 h-7 flex justify-center items-center rounded-lg transition" title="Chỉnh sửa">
                                                <iconify-icon icon="lucide:edit" class="text-sm"></iconify-icon>
                                            </a>
                                            @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-neutral-400 text-xs">
                                        Không tìm thấy đơn hàng nào gần đây
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan

@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    // 1. Biểu đồ Weekly Revenue & Orders
    const weeklyChartEl = document.querySelector("#weeklyRevenueOrderChart");
    if (weeklyChartEl && typeof ApexCharts !== 'undefined') {
        const categories = {!! json_encode($chartDays) !!};
        const revenueData = {!! json_encode($chartRevenues) !!};
        const orderData = {!! json_encode($chartOrderCounts) !!};

        const options = {
            series: [
                {
                    name: 'Doanh thu (VNĐ)',
                    type: 'column',
                    data: revenueData
                },
                {
                    name: 'Số đơn hàng',
                    type: 'line',
                    data: orderData
                }
            ],
            chart: {
                height: 300,
                type: 'line',
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            stroke: {
                width: [0, 3],
                curve: 'smooth'
            },
            plotOptions: {
                bar: {
                    columnWidth: '40%',
                    borderRadius: 6
                }
            },
            colors: ['#487fff', '#f59e0b'],
            dataLabels: {
                enabled: false
            },
            labels: categories,
            yaxis: [
                {
                    title: {
                        text: 'Doanh thu (VNĐ)',
                        style: { color: '#64748b', fontSize: '11px' }
                    },
                    labels: {
                        formatter: function (val) {
                            if (val >= 1000000) return (val / 1000000).toFixed(1) + ' tr';
                            if (val >= 1000) return (val / 1000).toFixed(0) + ' k';
                            return val;
                        },
                        style: { colors: '#64748b', fontSize: '11px' }
                    }
                },
                {
                    opposite: true,
                    title: {
                        text: 'Số đơn',
                        style: { color: '#64748b', fontSize: '11px' }
                    },
                    labels: {
                        formatter: function (val) {
                            return Math.round(val);
                        },
                        style: { colors: '#64748b', fontSize: '11px' }
                    }
                }
            ],
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function (y, { seriesIndex }) {
                        if (typeof y !== "undefined") {
                            return seriesIndex === 0
                                ? new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(y)
                                : y + " đơn";
                        }
                        return y;
                    }
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4
            }
        };

        const chart = new ApexCharts(weeklyChartEl, options);
        chart.render();
    }

    // 2. Biểu đồ Donut cơ cấu loại đơn hàng
    const donutChartEl = document.querySelector("#orderTypeDonutChart");
    if (donutChartEl && typeof ApexCharts !== 'undefined') {
        const acrylicCount = {{ (int) $stats['acrylic_orders'] }};
        const glassCount = {{ (int) $stats['glass_orders'] }};
        const minLateCount = {{ (int) $stats['min_late_orders'] }};

        const total = acrylicCount + glassCount + minLateCount;
        const seriesData = total > 0 ? [acrylicCount, glassCount, minLateCount] : [1, 1, 1];

        const donutOptions = {
            series: seriesData,
            chart: {
                type: 'donut',
                height: 220,
                fontFamily: 'Inter, sans-serif'
            },
            labels: ['Acrylic', 'Cánh kính', 'Min-Late'],
            colors: ['#4f46e5', '#06b6d4', '#f59e0b'],
            legend: {
                show: false
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '12px', color: '#64748b' },
                            value: {
                                show: true,
                                fontSize: '18px',
                                fontWeight: 700,
                                color: '#1e293b',
                                formatter: function (val) {
                                    return total > 0 ? val + " đơn" : "0";
                                }
                            },
                            total: {
                                show: true,
                                label: 'Tổng đơn',
                                fontSize: '12px',
                                color: '#64748b',
                                formatter: function () {
                                    return total + " đơn";
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: {
                enabled: false
            }
        };

        const donutChart = new ApexCharts(donutChartEl, donutOptions);
        donutChart.render();
    }
});
</script>
@endpush