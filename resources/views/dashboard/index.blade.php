@extends('layout.layout')

@php
    $title = 'Bảng điều khiển';
    $subTitle = 'Tổng quan hệ thống';
    $script = '';
@endphp

@section('content')

    {{-- Banner chào mừng --}}
    <div class="card border border-neutral-200 rounded-xl mb-6 bg-gradient-to-r from-primary-600 to-indigo-700 text-white relative overflow-hidden">
        <div class="absolute -right-16 -top-16 w-48 h-48 rounded-full bg-white/10 blur-xl"></div>
        <div class="absolute -right-4 -bottom-4 w-32 h-32 rounded-full bg-white/10 blur-lg"></div>
        <div class="card-body p-6 flex items-center justify-between relative z-10 flex-wrap gap-4">
            <div>
                <h4 class="text-xl md:text-2xl font-bold mb-1">Xin chào, {{ auth()->user()->name }}!</h4>
                <p class="text-white/80 text-sm md:text-base font-medium">Chào mừng bạn quay trở lại với Hệ thống điều hành sản xuất gỗ Gervin.</p>
            </div>
            <div class="bg-yellow-400 text-neutral-900 px-4 py-2 rounded-xl shadow-sm text-center shrink-0">
                <span class="block text-xs uppercase tracking-wider font-bold text-neutral-800">Hôm nay</span>
                <span class="text-base font-bold">{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Khối thống kê tổng quan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        {{-- Tổng đơn hàng --}}
        @can('view order')
        <div class="card shadow-none border border-neutral-200 rounded-xl h-full bg-gradient-to-r from-blue-600/10 to-white">
            <div class="card-body p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-medium text-neutral-600 mb-1 text-sm">Tổng đơn hàng</p>
                        <h4 class="mb-0 font-bold text-neutral-900">{{ number_format($stats['total_orders']) }}</h4>
                    </div>
                    <div class="w-[50px] h-[50px] bg-blue-600 rounded-full flex justify-center items-center text-white shrink-0">
                        <iconify-icon icon="solar:clipboard-list-bold-duotone" class="text-2xl"></iconify-icon>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between text-xs text-neutral-500 border-t border-neutral-100 pt-2 flex-wrap gap-2">
                    <span>Chờ xử lý: <strong class="text-warning-600 font-semibold">{{ $stats['pending_orders'] }}</strong></span>
                    <span>Đang chạy: <strong class="text-info-600 font-semibold">{{ $stats['processing_orders'] }}</strong></span>
                    <span>Xong: <strong class="text-success-600 font-semibold">{{ $stats['completed_orders'] }}</strong></span>
                </div>
            </div>
        </div>
        @endcan

        {{-- Acrylic Orders --}}
        @can('view order')
        <div class="card shadow-none border border-neutral-200 rounded-xl h-full bg-gradient-to-r from-indigo-600/10 to-white">
            <div class="card-body p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-medium text-neutral-600 mb-1 text-sm">Đơn hàng Acrylic</p>
                        <h4 class="mb-0 font-bold text-neutral-900">{{ number_format($stats['acrylic_orders']) }}</h4>
                    </div>
                    <div class="w-[50px] h-[50px] bg-indigo-600 rounded-full flex justify-center items-center text-white shrink-0">
                        <iconify-icon icon="solar:layers-minimalistic-bold-duotone" class="text-2xl"></iconify-icon>
                    </div>
                </div>
                <p class="text-xs text-neutral-500 mt-3 border-t border-neutral-100 pt-2 mb-0">
                    Đơn hàng gỗ Acrylic của Gervin
                </p>
            </div>
        </div>
        @endcan

        {{-- Glass Orders --}}
        @can('view order')
        <div class="card shadow-none border border-neutral-200 rounded-xl h-full bg-gradient-to-r from-cyan-600/10 to-white">
            <div class="card-body p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-medium text-neutral-600 mb-1 text-sm">Đơn hàng Kính</p>
                        <h4 class="mb-0 font-bold text-neutral-900">{{ number_format($stats['glass_orders']) }}</h4>
                    </div>
                    <div class="w-[50px] h-[50px] bg-cyan-600 rounded-full flex justify-center items-center text-white shrink-0">
                        <iconify-icon icon="solar:mirror-bold-duotone" class="text-2xl"></iconify-icon>
                    </div>
                </div>
                <p class="text-xs text-neutral-500 mt-3 border-t border-neutral-100 pt-2 mb-0">
                    Đơn hàng gia công cánh kính tủ áo
                </p>
            </div>
        </div>
        @endcan

        {{-- Min Late Orders --}}
        @can('view order')
        <div class="card shadow-none border border-neutral-200 rounded-xl h-full bg-gradient-to-r from-amber-600/10 to-white">
            <div class="card-body p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-medium text-neutral-600 mb-1 text-sm">Đơn hàng Min Late</p>
                        <h4 class="mb-0 font-bold text-neutral-900">{{ number_format($stats['min_late_orders']) }}</h4>
                    </div>
                    <div class="w-[50px] h-[50px] bg-amber-500 rounded-full flex justify-center items-center text-white shrink-0">
                        <iconify-icon icon="solar:clock-square-bold-duotone" class="text-2xl"></iconify-icon>
                    </div>
                </div>
                <p class="text-xs text-neutral-500 mt-3 border-t border-neutral-100 pt-2 mb-0">
                    Đơn gỗ công nghiệp Min Late
                </p>
            </div>
        </div>
        @endcan

        {{-- Khách hàng --}}
        @can('view customer')
        <div class="card shadow-none border border-neutral-200 rounded-xl h-full bg-gradient-to-r from-emerald-600/10 to-white">
            <div class="card-body p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-medium text-neutral-600 mb-1 text-sm">Khách hàng</p>
                        <h4 class="mb-0 font-bold text-neutral-900">{{ number_format($stats['total_customers']) }}</h4>
                    </div>
                    <div class="w-[50px] h-[50px] bg-emerald-600 rounded-full flex justify-center items-center text-white shrink-0">
                        <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="text-2xl"></iconify-icon>
                    </div>
                </div>
                <p class="text-xs text-neutral-500 mt-3 border-t border-neutral-100 pt-2 mb-0">
                    Đối tác & Khách hàng trong hệ thống
                </p>
            </div>
        </div>
        @endcan

        {{-- Bảng giá tấm --}}
        @can('view supply')
        <div class="card shadow-none border border-neutral-200 rounded-xl h-full bg-gradient-to-r from-rose-600/10 to-white">
            <div class="card-body p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-medium text-neutral-600 mb-1 text-sm">Bảng giá tấm</p>
                        <h4 class="mb-0 font-bold text-neutral-900">{{ number_format($stats['total_wood_boards']) }}</h4>
                    </div>
                    <div class="w-[50px] h-[50px] bg-rose-600 rounded-full flex justify-center items-center text-white shrink-0">
                        <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="text-2xl"></iconify-icon>
                    </div>
                </div>
                <p class="text-xs text-neutral-500 mt-3 border-t border-neutral-100 pt-2 mb-0">
                    Quản lý bảng giá tấm gỗ m2
                </p>
            </div>
        </div>
        @endcan

        {{-- Lệnh sản xuất --}}
        @can('view manufacture')
        <div class="card shadow-none border border-neutral-200 rounded-xl h-full bg-gradient-to-r from-violet-600/10 to-white">
            <div class="card-body p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-medium text-neutral-600 mb-1 text-sm">Lệnh sản xuất</p>
                        <h4 class="mb-0 font-bold text-neutral-900">{{ number_format($stats['total_manufacture_orders']) }}</h4>
                    </div>
                    <div class="w-[50px] h-[50px] bg-violet-600 rounded-full flex justify-center items-center text-white shrink-0">
                        <iconify-icon icon="solar:settings-bold-duotone" class="text-2xl"></iconify-icon>
                    </div>
                </div>
                <p class="text-xs text-neutral-500 mt-3 border-t border-neutral-100 pt-2 mb-0">
                    Theo dõi lệnh sản xuất gia công gỗ
                </p>
            </div>
        </div>
        @endcan

        {{-- Người dùng --}}
        @can('view user')
        <div class="card shadow-none border border-neutral-200 rounded-xl h-full bg-gradient-to-r from-slate-600/10 to-white">
            <div class="card-body p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-medium text-neutral-600 mb-1 text-sm">Tài khoản nhân sự</p>
                        <h4 class="mb-0 font-bold text-neutral-900">{{ number_format($stats['total_users']) }}</h4>
                    </div>
                    <div class="w-[50px] h-[50px] bg-slate-600 rounded-full flex justify-center items-center text-white shrink-0">
                        <iconify-icon icon="solar:user-rounded-bold-duotone" class="text-2xl"></iconify-icon>
                    </div>
                </div>
                <p class="text-xs text-neutral-500 mt-3 border-t border-neutral-100 pt-2 mb-0">
                    Tài khoản nhân sự vận hành hệ thống
                </p>
            </div>
        </div>
        @endcan
    </div>

    {{-- Phân xưởng sản xuất --}}
    @canany(['view pressing', 'view cnc', 'view edge banding', 'view finishing', 'view qc', 'view packing', 'view shipped'])
    <div class="mt-8">
        <h5 class="text-lg font-semibold mb-4 text-neutral-800 flex items-center gap-2">
            <iconify-icon icon="solar:box-bold-duotone" class="text-primary-600 text-2xl"></iconify-icon>
            Phân xưởng Sản xuất & Quy trình
        </h5>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-4">
            {{-- Pressing --}}
            @can('view pressing')
            <a href="{{ route('processes.pressing') }}" class="card border border-neutral-200 rounded-xl hover:border-cyan-500 hover:shadow-md transition-all duration-300 bg-white">
                <div class="card-body p-4 flex flex-col items-center justify-center text-center h-full">
                    <div class="w-12 h-12 bg-cyan-100 text-cyan-600 rounded-xl flex justify-center items-center mb-3">
                        <iconify-icon icon="lucide:monitor" class="text-2xl"></iconify-icon>
                    </div>
                    <h6 class="text-sm font-semibold text-neutral-800 mb-1">Ép ván</h6>
                    <span class="text-xs text-neutral-400">Xem tiến độ ép ván</span>
                </div>
            </a>
            @endcan

            {{-- CNC --}}
            @can('view cnc')
            <a href="{{ route('processes.cnc') }}" class="card border border-neutral-200 rounded-xl hover:border-orange-500 hover:shadow-md transition-all duration-300 bg-white">
                <div class="card-body p-4 flex flex-col items-center justify-center text-center h-full">
                    <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-xl flex justify-center items-center mb-3">
                        <iconify-icon icon="lucide:scissors" class="text-2xl"></iconify-icon>
                    </div>
                    <h6 class="text-sm font-semibold text-neutral-800 mb-1">Cắt CNC</h6>
                    <span class="text-xs text-neutral-400">Quét mã CNC & Hoàn thành</span>
                </div>
            </a>
            @endcan

            {{-- Edge Banding --}}
            @can('view edge banding')
            <a href="{{ route('processes.edge-banding') }}" class="card border border-neutral-200 rounded-xl hover:border-blue-500 hover:shadow-md transition-all duration-300 bg-white">
                <div class="card-body p-4 flex flex-col items-center justify-center text-center h-full">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex justify-center items-center mb-3">
                        <iconify-icon icon="lucide:layers" class="text-2xl"></iconify-icon>
                    </div>
                    <h6 class="text-sm font-semibold text-neutral-800 mb-1">Dán cạnh</h6>
                    <span class="text-xs text-neutral-400">Quét dán cạnh & đo mét dài</span>
                </div>
            </a>
            @endcan

            {{-- Finishing --}}
            @can('view finishing')
            <a href="{{ route('processes.finishing') }}" class="card border border-neutral-200 rounded-xl hover:border-purple-500 hover:shadow-md transition-all duration-300 bg-white">
                <div class="card-body p-4 flex flex-col items-center justify-center text-center h-full">
                    <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex justify-center items-center mb-3">
                        <iconify-icon icon="lucide:brush" class="text-2xl"></iconify-icon>
                    </div>
                    <h6 class="text-sm font-semibold text-neutral-800 mb-1">Làm đẹp</h6>
                    <span class="text-xs text-neutral-400">Hoàn thiện làm đẹp gỗ</span>
                </div>
            </a>
            @endcan

            {{-- QC --}}
            @can('view qc')
            <a href="{{ route('processes.qc') }}" class="card border border-neutral-200 rounded-xl hover:border-emerald-500 hover:shadow-md transition-all duration-300 bg-white">
                <div class="card-body p-4 flex flex-col items-center justify-center text-center h-full">
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex justify-center items-center mb-3">
                        <iconify-icon icon="lucide:check-circle" class="text-2xl"></iconify-icon>
                    </div>
                    <h6 class="text-sm font-semibold text-neutral-800 mb-1">Kiểm soát QC</h6>
                    <span class="text-xs text-neutral-400">Kiểm tra chất lượng & báo lỗi</span>
                </div>
            </a>
            @endcan

            {{-- Packing --}}
            @can('view packing')
            <a href="{{ route('processes.packing') }}" class="card border border-neutral-200 rounded-xl hover:border-slate-500 hover:shadow-md transition-all duration-300 bg-white">
                <div class="card-body p-4 flex flex-col items-center justify-center text-center h-full">
                    <div class="w-12 h-12 bg-slate-100 text-slate-600 rounded-xl flex justify-center items-center mb-3">
                        <iconify-icon icon="lucide:package" class="text-2xl"></iconify-icon>
                    </div>
                    <h6 class="text-sm font-semibold text-neutral-800 mb-1">Đóng gói</h6>
                    <span class="text-xs text-neutral-400">Xác nhận đóng gói</span>
                </div>
            </a>
            @endcan

            {{-- Shipped --}}
            @can('view shipped')
            <a href="{{ route('processes.shipped') }}" class="card border border-neutral-200 rounded-xl hover:border-indigo-500 hover:shadow-md transition-all duration-300 bg-white">
                <div class="card-body p-4 flex flex-col items-center justify-center text-center h-full">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-xl flex justify-center items-center mb-3">
                        <iconify-icon icon="lucide:truck" class="text-2xl"></iconify-icon>
                    </div>
                    <h6 class="text-sm font-semibold text-neutral-800 mb-1">Xuất xưởng</h6>
                    <span class="text-xs text-neutral-400">Giao hàng & Xuất xưởng</span>
                </div>
            </a>
            @endcan
        </div>
    </div>
    @endcanany

    {{-- Danh sách đơn hàng gần đây --}}
    @can('view order')
    <div class="grid grid-cols-12 gap-6 mt-8">
        <div class="col-span-12">
            <div class="card h-full p-0 rounded-xl border border-neutral-200 overflow-hidden bg-white">
                <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center justify-between">
                    <h6 class="text-lg font-semibold text-neutral-800 mb-0 flex items-center gap-2">
                        <iconify-icon icon="solar:history-bold-duotone" class="text-primary-600 text-2xl"></iconify-icon>
                        Đơn hàng mới nhận
                    </h6>
                    <a href="{{ route('orders.index') }}" class="text-sm text-primary-600 hover:text-primary-700 font-semibold flex items-center gap-1">
                        Xem tất cả đơn hàng
                        <iconify-icon icon="solar:alt-arrow-right-linear" class="text-base"></iconify-icon>
                    </a>
                </div>
                
                <div class="card-body p-0">
                    <div class="overflow-x-auto scroll-sm">
                        <table class="table bordered-table sm-table mb-0 table-auto w-full">
                            <thead>
                                <tr class="bg-neutral-50/50">
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Mã đơn</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Loại đơn</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Khách hàng</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Hạn đơn</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Tổng tiền</th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Trạng thái</th>
                                    <th class="px-6 py-3.5 text-center text-xs font-semibold text-neutral-600 uppercase tracking-wider">Hành động</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100">
                                @forelse($recentOrders as $order)
                                <tr class="hover:bg-neutral-50/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-neutral-800">{{ $order->order_code }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
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
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-neutral-700">{{ $order->customer_name }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-neutral-500">{{ $order->deadline ? $order->deadline->format('d/m/Y') : '—' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-neutral-800">{{ number_format(round($order->total_amount, -3), 0, ',', '.') }}đ</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'draft' => 'bg-neutral-100 text-neutral-600',
                                                'pending' => 'bg-warning-100 text-warning-600',
                                                'processing' => 'bg-info-100 text-info-600',
                                                'completed' => 'bg-success-100 text-success-600',
                                                'cancelled' => 'bg-danger-100 text-danger-600',
                                            ];
                                            $statusLabels = [
                                                'draft' => 'Nháp',
                                                'pending' => 'Chờ xử lý',
                                                'processing' => 'Đang xử lý',
                                                'completed' => 'Hoàn thành',
                                                'cancelled' => 'Đã hủy',
                                            ];
                                        @endphp
                                        <span class="px-2.5 py-1 rounded-md text-xs font-semibold {{ $statusColors[$order->status] ?? 'bg-neutral-100 text-neutral-600' }}">
                                            {{ $statusLabels[$order->status] ?? $order->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center gap-2 justify-center">
                                            @can('view order')
                                            <a href="{{ route('orders.show', $order) }}" class="bg-primary-100 hover:bg-primary-200 text-primary-600 font-medium w-8 h-8 flex justify-center items-center rounded-full" title="Xem chi tiết">
                                                <iconify-icon icon="lucide:eye" class="text-base"></iconify-icon>
                                            </a>
                                            @endcan
                                            @can('edit order')
                                            <a href="{{ route('orders.edit', $order) }}" class="bg-success-100 hover:bg-success-200 text-success-600 font-medium w-8 h-8 flex justify-center items-center rounded-full" title="Chỉnh sửa">
                                                <iconify-icon icon="lucide:edit" class="text-base"></iconify-icon>
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-neutral-500 text-sm">
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