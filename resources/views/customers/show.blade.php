@extends('layout.layout')
@php
    $title    = 'Chi tiết khách hàng';
    $subTitle = ($customer->customer_code ? '[' . $customer->customer_code . '] ' : '') . $customer->name;

    $fullAddress = implode(', ', array_filter([$customer->address, $customer->ward, $customer->province]));

    $statusColor = match($customer->status) {
        'Đang đặt hàng' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'Không đặt GERVIN' => 'bg-rose-50 text-rose-700 border-rose-200',
        'Khách hàng mới tiềm năng' => 'bg-blue-50 text-blue-700 border-blue-200',
        'Tạm dừng hợp tác' => 'bg-amber-50 text-amber-700 border-amber-200',
        default => 'bg-neutral-100 text-neutral-600 border-neutral-200'
    };

    $orderStatusColors = [
        'draft'         => 'bg-neutral-100 text-neutral-700 border-neutral-200',
        'pending'       => 'bg-amber-50 text-amber-800 border-amber-200',
        'transferred'   => 'bg-blue-50 text-blue-700 border-blue-200',
        'in_production' => 'bg-purple-50 text-purple-700 border-purple-200',
        'completed'     => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'cancelled'     => 'bg-rose-50 text-rose-700 border-rose-200',
    ];
@endphp

@section('content')
<div class="space-y-6">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="alert alert-success bg-success-50 text-success-700 border border-success-200 rounded-xl p-4 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2">
            <iconify-icon icon="solar:check-circle-bold" class="text-xl text-success-600"></iconify-icon>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-success-500 hover:text-success-800 text-lg leading-none">&times;</button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger bg-danger-50 text-danger-700 border border-danger-200 rounded-xl p-4 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2">
            <iconify-icon icon="solar:danger-triangle-bold" class="text-xl text-danger-600"></iconify-icon>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-danger-500 hover:text-danger-800 text-lg leading-none">&times;</button>
    </div>
    @endif

    {{-- Top Action / Header Card --}}
    <div class="bg-white rounded-2xl p-6 border border-neutral-200/80 shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <a href="{{ route('customers.index') }}"
                   class="w-10 h-10 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-600 flex items-center justify-center transition-colors shrink-0 mt-0.5"
                   title="Quay lại danh sách khách hàng">
                    <iconify-icon icon="lucide:arrow-left" class="text-xl"></iconify-icon>
                </a>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl md:text-2xl font-bold text-neutral-800 m-0">{{ $customer->name }}</h1>
                        @if($customer->customer_code)
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-neutral-100 text-neutral-700 border border-neutral-200">
                                {{ $customer->customer_code }}
                            </span>
                        @endif
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold border {{ $statusColor }}">
                            {{ $customer->status ?: 'Đang đặt hàng' }}
                        </span>
                        @if($customer->marketGroup)
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-200 flex items-center gap-1">
                                <iconify-icon icon="solar:users-group-two-rounded-bold" class="text-sm"></iconify-icon>
                                {{ $customer->marketGroup->name }}
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-4 mt-2 text-xs text-neutral-500 flex-wrap">
                        @if($customer->phone)
                            <a href="tel:{{ $customer->phone }}" class="flex items-center gap-1 text-neutral-700 hover:text-primary-600 font-medium">
                                <iconify-icon icon="solar:phone-bold" class="text-primary-600"></iconify-icon>
                                {{ $customer->phone }}
                            </a>
                        @endif
                        @if($fullAddress)
                            <span class="flex items-center gap-1 max-w-md truncate" title="{{ $fullAddress }}">
                                <iconify-icon icon="solar:map-point-bold" class="text-rose-500 shrink-0"></iconify-icon>
                                {{ $fullAddress }}
                            </span>
                        @endif
                        @if($customer->latitude && $customer->longitude)
                            <a href="https://www.google.com/maps?q={{ $customer->latitude }},{{ $customer->longitude }}" target="_blank"
                               class="inline-flex items-center gap-1 font-semibold text-primary-600 hover:text-primary-800 bg-primary-50 px-2 py-0.5 rounded border border-primary-200">
                                <iconify-icon icon="solar:map-point-wave-bold" class="text-rose-500"></iconify-icon>
                                GPS ({{ number_format($customer->latitude, 4) }}, {{ number_format($customer->longitude, 4) }})
                            </a>
                        @else
                            <span class="text-neutral-400 italic flex items-center gap-1">
                                <iconify-icon icon="solar:map-point-linear"></iconify-icon> Chưa có tọa độ GPS
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action buttons --}}
            <div class="flex items-center gap-2.5 flex-wrap">
                <button type="button" onclick="openModal('add-payment-modal')"
                    class="btn bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-all">
                    <iconify-icon icon="lucide:plus-circle" class="text-base"></iconify-icon>
                    Thêm thanh toán
                </button>
                <a href="{{ route('orders.create') }}?customer_id={{ $customer->id }}"
                    class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-all">
                    <iconify-icon icon="solar:bag-3-bold" class="text-base"></iconify-icon>
                    Tạo đơn hàng
                </a>
                @can('edit customer')
                <button type="button" onclick="openEditCustomerModalFromBtn(this)"
                    data-id="{{ $customer->id }}"
                    data-code="{{ $customer->customer_code ?? '' }}"
                    data-name="{{ $customer->name }}"
                    data-phone="{{ $customer->phone ?? '' }}"
                    data-address="{{ $customer->address ?? '' }}"
                    data-latitude="{{ $customer->latitude ?? '' }}"
                    data-longitude="{{ $customer->longitude ?? '' }}"
                    data-province="{{ $customer->province ?? '' }}"
                    data-ward="{{ $customer->ward ?? '' }}"
                    data-status="{{ $customer->status ?? 'Đang đặt hàng' }}"
                    data-partner-competitors="{{ $customer->partner_competitors ?? '' }}"
                    data-feedback="{{ $customer->feedback ?? '' }}"
                    data-personality="{{ $customer->personality ?? '' }}"
                    data-workshop-scale="{{ $customer->workshop_scale ?? '' }}"
                    data-customer-proposal="{{ $customer->customer_proposal ?? '' }}"
                    data-sale-proposal="{{ $customer->sale_proposal ?? '' }}"
                    data-debt="{{ $customer->debt ?? 0 }}"
                    data-debt-limit="{{ $customer->debt_limit ?? 0 }}"
                    data-policy="{{ $customer->policy ?? '' }}"
                    data-photos="{{ json_encode($customer->photos ?? []) }}"
                    data-market-group-id="{{ $customer->market_group_id ?? '' }}"
                    class="btn bg-white hover:bg-neutral-50 text-neutral-700 border border-neutral-300 px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all">
                    <iconify-icon icon="lucide:edit" class="text-sm text-neutral-600"></iconify-icon>
                    Sửa thông tin
                </button>
                <button type="button" onclick="openCustomerHistoryModal({{ $customer->id }}, '{{ addslashes($customer->name) }}', '{{ $customer->customer_code ?? '' }}')"
                    class="btn bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all"
                    title="Lịch sử cập nhật thông tin khách hàng">
                    <iconify-icon icon="solar:history-bold-duotone" class="text-base"></iconify-icon>
                    Lịch sử sửa
                </button>
                @endcan
            </div>
        </div>
    </div>

    {{-- Customer Market Profile & Details Card --}}
    <div class="bg-white rounded-2xl p-6 border border-neutral-200/80 shadow-xs space-y-5">
        <div class="flex items-center justify-between border-b border-neutral-100 pb-3">
            <div class="flex items-center gap-2">
                <iconify-icon icon="solar:user-id-bold-duotone" class="text-primary-600 text-lg"></iconify-icon>
                <h6 class="font-bold text-xs uppercase tracking-wider text-neutral-700 m-0">Hồ sơ thị trường & Thông tin chi tiết</h6>
            </div>
            <button type="button" onclick="toggleDetailsSection()" id="toggle-details-btn" class="text-xs text-primary-600 hover:text-primary-800 font-semibold flex items-center gap-1">
                <span id="toggle-details-text">Thu gọn</span>
                <iconify-icon icon="lucide:chevron-up" id="toggle-details-icon" class="text-sm"></iconify-icon>
            </button>
        </div>

        <div id="details-section-body" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div class="bg-neutral-50/70 p-3 rounded-xl border border-neutral-200/70">
                    <span class="text-neutral-400 block mb-1">Số điện thoại:</span>
                    <span class="font-semibold text-neutral-800">{{ $customer->phone ?: '—' }}</span>
                </div>
                <div class="bg-neutral-50/70 p-3 rounded-xl border border-neutral-200/70 lg:col-span-2">
                    <span class="text-neutral-400 block mb-1">Địa chỉ:</span>
                    <span class="font-semibold text-neutral-800">{{ $fullAddress ?: ($customer->address ?: '—') }}</span>
                </div>
                <div class="bg-neutral-50/70 p-3 rounded-xl border border-neutral-200/70">
                    <span class="text-neutral-400 block mb-1">Đối tác đang hợp tác:</span>
                    <span class="font-bold text-primary-700">{{ $customer->partner_competitors ?: '—' }}</span>
                </div>
                <div class="bg-neutral-50/70 p-3 rounded-xl border border-neutral-200/70">
                    <span class="text-neutral-400 block mb-1">Quy mô xưởng:</span>
                    <span class="font-medium text-neutral-800">{{ $customer->workshop_scale ?: '—' }}</span>
                </div>
                <div class="bg-neutral-50/70 p-3 rounded-xl border border-neutral-200/70">
                    <span class="text-neutral-400 block mb-1">Tính cách khách hàng:</span>
                    <span class="font-medium text-neutral-800">{{ $customer->personality ?: '—' }}</span>
                </div>
                <div class="bg-neutral-50/70 p-3 rounded-xl border border-neutral-200/70">
                    <span class="text-neutral-400 block mb-1">Định mức công nợ:</span>
                    <span class="font-bold text-neutral-800">{{ $customer->debt_limit ? number_format($customer->debt_limit, 0, ',', '.') . '₫' : '—' }}</span>
                </div>
                <div class="bg-neutral-50/70 p-3 rounded-xl border border-neutral-200/70">
                    <span class="text-neutral-400 block mb-1">Chính sách:</span>
                    <span class="font-medium text-neutral-800" title="{{ $customer->policy }}">{{ $customer->policy ?: '—' }}</span>
                </div>
            </div>

            {{-- Proposals & Feedback --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs pt-1">
                <div class="bg-purple-50/50 p-3.5 rounded-xl border border-purple-200/80">
                    <div class="flex items-center gap-1.5 text-primary-700 font-bold mb-1.5">
                        <iconify-icon icon="solar:chat-round-line-bold" class="text-base"></iconify-icon> Phản ánh về Gervin:
                    </div>
                    <p class="text-neutral-700 m-0 whitespace-pre-line text-xs leading-relaxed">{{ $customer->feedback ?: 'Chưa có ghi nhận phản ánh' }}</p>
                </div>
                <div class="bg-emerald-50/50 p-3.5 rounded-xl border border-emerald-200/80">
                    <div class="flex items-center gap-1.5 text-emerald-700 font-bold mb-1.5">
                        <iconify-icon icon="solar:lightbulb-bold" class="text-base"></iconify-icon> Đề xuất của KH:
                    </div>
                    <p class="text-neutral-700 m-0 whitespace-pre-line text-xs leading-relaxed">{{ $customer->customer_proposal ?: 'Chưa có đề xuất từ khách hàng' }}</p>
                </div>
                <div class="bg-amber-50/50 p-3.5 rounded-xl border border-amber-200/80">
                    <div class="flex items-center gap-1.5 text-amber-700 font-bold mb-1.5">
                        <iconify-icon icon="solar:user-speak-rounded-bold" class="text-base"></iconify-icon> Đề xuất Sale:
                    </div>
                    <p class="text-neutral-700 m-0 whitespace-pre-line text-xs leading-relaxed">{{ $customer->sale_proposal ?: 'Chưa có kế hoạch đề xuất' }}</p>
                </div>
            </div>

            {{-- Photos Gallery --}}
            @if(!empty($customer->photos) && is_array($customer->photos) && count($customer->photos) > 0)
            <div class="mt-4 pt-3 border-t border-neutral-100">
                <div class="text-xs font-bold text-neutral-800 mb-2 flex items-center justify-between">
                    <span class="flex items-center gap-1.5 text-primary-700">
                        <iconify-icon icon="solar:camera-bold" class="text-base"></iconify-icon> Hình ảnh xưởng & bảng biểu chụp thực tế:
                    </span>
                    <span class="text-[11px] font-normal text-neutral-500 bg-neutral-100 px-2 py-0.5 rounded-full">{{ count($customer->photos) }} hình ảnh</span>
                </div>
                <div class="flex flex-wrap gap-3">
                    @foreach($customer->photos as $idx => $photo)
                        @php
                            $photoUrl = (str_starts_with($photo, 'http') || str_starts_with($photo, '/')) ? $photo : '/' . $photo;
                        @endphp
                        <div class="relative group w-20 h-20 rounded-xl overflow-hidden border border-neutral-300 shadow-xs shrink-0 bg-neutral-100 cursor-pointer"
                             onclick="openImageLightbox('{{ $photoUrl }}', '{{ addslashes($customer->name) }} - Ảnh {{ $idx + 1 }}')">
                            <img src="{{ $photoUrl }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                            <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition-colors"></div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Tổng đơn --}}
        <div class="bg-white rounded-2xl p-5 border border-neutral-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <iconify-icon icon="solar:bag-3-bold" class="text-2xl"></iconify-icon>
            </div>
            <div>
                <span class="text-xs text-neutral-400 font-medium block">Tổng đơn hàng hợp lệ</span>
                <h3 class="text-xl font-bold text-neutral-800 m-0 mt-0.5">{{ number_format($totalOrdersCount) }}</h3>
            </div>
        </div>

        {{-- Card 2: Tổng giá trị --}}
        <div class="bg-white rounded-2xl p-5 border border-neutral-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <iconify-icon icon="solar:dollar-minimalistic-bold" class="text-2xl"></iconify-icon>
            </div>
            <div>
                <span class="text-xs text-neutral-400 font-medium block">Tổng giá trị đơn hàng</span>
                <h3 class="text-xl font-bold text-neutral-800 m-0 mt-0.5">{{ number_format($totalOrdersAmount, 0, ',', '.') }}₫</h3>
            </div>
        </div>

        {{-- Card 3: Đã thanh toán --}}
        <div class="bg-white rounded-2xl p-5 border border-neutral-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <iconify-icon icon="solar:check-circle-bold" class="text-2xl"></iconify-icon>
            </div>
            <div>
                <span class="text-xs text-neutral-400 font-medium block">Đã thanh toán</span>
                <h3 class="text-xl font-bold text-emerald-600 m-0 mt-0.5">{{ number_format($totalPaid, 0, ',', '.') }}₫</h3>
            </div>
        </div>

        {{-- Card 4: Còn nợ --}}
        <div class="bg-white rounded-2xl p-5 border border-neutral-200/80 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl {{ $totalDebt > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center shrink-0">
                <iconify-icon icon="{{ $totalDebt > 0 ? 'solar:danger-triangle-bold' : 'solar:verified-check-bold' }}" class="text-2xl"></iconify-icon>
            </div>
            <div>
                <span class="text-xs text-neutral-400 font-medium block">Còn nợ hiện tại</span>
                <h3 class="text-xl font-bold {{ $totalDebt > 0 ? 'text-rose-600' : 'text-emerald-600' }} m-0 mt-0.5">
                    {{ number_format($totalDebt, 0, ',', '.') }}₫
                </h3>
            </div>
        </div>
    </div>

    {{-- Order Status Distribution breakdown --}}
    <div class="bg-white rounded-2xl p-4 border border-neutral-200/80 shadow-xs">
        <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
            <div class="flex items-center gap-2 text-xs font-bold text-neutral-700 uppercase tracking-wide">
                <iconify-icon icon="solar:pie-chart-2-bold" class="text-primary-600 text-base"></iconify-icon>
                Phân bổ trạng thái đơn hàng (Bấm để lọc nhanh)
            </div>
            @if(request('status') && request('status') !== 'all')
                <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}" class="text-xs text-primary-600 hover:underline font-semibold flex items-center gap-1">
                    <iconify-icon icon="lucide:x"></iconify-icon> Bỏ lọc trạng thái
                </a>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach($statusLabels as $stKey => $stLabel)
                @php
                    $cnt = $statusCounts[$stKey] ?? 0;
                    $isActive = request('status') === $stKey;
                    $stBadgeColor = $orderStatusColors[$stKey] ?? 'bg-neutral-100 text-neutral-600 border-neutral-200';
                @endphp
                <a href="{{ request()->fullUrlWithQuery(['status' => $isActive ? 'all' : $stKey, 'page' => 1]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border transition-all duration-150 {{ $stBadgeColor }} {{ $isActive ? 'ring-2 ring-primary-500 ring-offset-1 font-bold shadow-xs' : 'hover:opacity-85' }}">
                    <span>{{ $stLabel }}</span>
                    <span class="bg-white/80 px-1.5 py-0.2 rounded-full text-[11px] font-bold">{{ $cnt }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Side-by-side Layout: Left (Orders List with filters & pagination) | Right (Payments History) --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">

        {{-- LEFT COLUMN: Orders List --}}
        <div class="xl:col-span-8 space-y-4">
            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs overflow-hidden">
                {{-- Header with title and count --}}
                <div class="p-5 border-b border-neutral-200 flex flex-wrap items-center justify-between gap-3 bg-neutral-50/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg">
                            <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-neutral-800 m-0">Danh sách đơn hàng</h3>
                            <span class="text-xs text-neutral-400">Tất cả đơn hàng của khách hàng này có bộ lọc & phân trang</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-primary-50 text-primary-700 border border-primary-200">
                            {{ $orders->total() }} đơn hàng
                        </span>
                    </div>
                </div>

                {{-- Filter Toolbar --}}
                <div class="p-4 bg-white border-b border-neutral-200">
                    <form action="{{ route('customers.show', $customer) }}" method="GET" class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                            {{-- Search input --}}
                            <div class="col-span-1 sm:col-span-2 md:col-span-1">
                                <label class="text-[11px] font-semibold text-neutral-600 mb-1 block">Tìm kiếm</label>
                                <div class="relative">
                                    <input type="text" name="search" value="{{ request('search') }}"
                                           placeholder="Mã đơn, tên CT..."
                                           class="form-control rounded-xl text-xs py-2 pl-8 pr-2 w-full border border-neutral-200">
                                    <iconify-icon icon="lucide:search" class="absolute left-2.5 top-2.5 text-neutral-400 text-sm"></iconify-icon>
                                </div>
                            </div>

                            {{-- Status select --}}
                            <div>
                                <label class="text-[11px] font-semibold text-neutral-600 mb-1 block">Trạng thái</label>
                                <select name="status" class="form-select rounded-xl text-xs py-2 px-3 w-full border border-neutral-200">
                                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>-- Tất cả trạng thái --</option>
                                    @foreach($statusLabels as $sKey => $sVal)
                                        <option value="{{ $sKey }}" {{ request('status') === $sKey ? 'selected' : '' }}>{{ $sVal }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Date range: From date --}}
                            <div>
                                <label class="text-[11px] font-semibold text-neutral-600 mb-1 block">Từ ngày</label>
                                <input type="date" name="start_date" value="{{ request('start_date') }}"
                                       class="form-control rounded-xl text-xs py-2 px-3 w-full border border-neutral-200">
                            </div>

                            {{-- Date range: To date --}}
                            <div>
                                <label class="text-[11px] font-semibold text-neutral-600 mb-1 block">Đến ngày</label>
                                <input type="date" name="end_date" value="{{ request('end_date') }}"
                                       class="form-control rounded-xl text-xs py-2 px-3 w-full border border-neutral-200">
                            </div>
                        </div>

                        {{-- Lower Filter row: per_page & action buttons --}}
                        <div class="flex items-center justify-between flex-wrap gap-2 pt-1 border-t border-neutral-100">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-neutral-500 font-medium">Hiển thị:</span>
                                <select name="per_page" onchange="this.form.submit()" class="form-select rounded-lg text-xs py-1 px-2 border border-neutral-200 bg-white">
                                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 đơn / trang</option>
                                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 đơn / trang</option>
                                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 đơn / trang</option>
                                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 đơn / trang</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="submit" class="btn bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-semibold px-4 py-2 flex items-center gap-1.5 shadow-2xs">
                                    <iconify-icon icon="lucide:filter" class="text-sm"></iconify-icon> Áp dụng lọc
                                </button>
                                <a href="{{ route('customers.show', $customer) }}" class="btn bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-xl text-xs font-semibold px-3 py-2">
                                    Xóa lọc
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Orders Table --}}
                <div class="overflow-x-auto">
                    <table class="table bordered-table sm-table mb-0 w-full text-xs">
                        <thead>
                            <tr class="bg-neutral-50 text-neutral-700 border-b border-neutral-200">
                                <th class="text-center w-12 py-3">STT</th>
                                <th class="text-left py-3">Mã đơn</th>
                                <th class="text-left py-3">Tên công trình</th>
                                <th class="text-left py-3">Ngày đặt</th>
                                <th class="text-center py-3 whitespace-nowrap">Số tấm</th>
                                <th class="text-center py-3 whitespace-nowrap">Tổng số mét</th>
                                <th class="text-right py-3">Giá trị đơn</th>
                                <th class="text-right py-3">Đã TT</th>
                                <th class="text-right py-3">Còn nợ</th>
                                <th class="text-center py-3">Trạng thái</th>
                                <th class="text-center py-3 w-16">Xem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @forelse($orders as $o)
                            @php
                                $stt = $orders->firstItem() + $loop->index;
                                $badgeColor = $orderStatusColors[$o->status] ?? 'bg-neutral-100 text-neutral-600 border-neutral-200';
                            @endphp
                            <tr class="hover:bg-neutral-50/70 transition-colors">
                                <td class="text-center text-neutral-400 font-mono">{{ $stt }}</td>
                                <td>
                                    <a href="{{ route('orders.show', $o->id) }}" target="_blank"
                                       class="font-bold text-primary-600 hover:text-primary-800 hover:underline flex items-center gap-1">
                                        {{ $o->order_code ?: '—' }}
                                        <iconify-icon icon="lucide:external-link" class="text-[10px] text-neutral-400"></iconify-icon>
                                    </a>
                                </td>
                                <td>
                                    <div class="font-semibold text-neutral-800 max-w-[180px] truncate" title="{{ $o->customer_name }}">
                                        {{ $o->customer_name ?: '—' }}
                                    </div>
                                </td>
                                <td class="text-neutral-600 whitespace-nowrap">
                                    {{ $o->order_date ? \Carbon\Carbon::parse($o->order_date)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    @if($o->total_sheets > 0)
                                        <span class="inline-flex items-center gap-1 font-semibold text-neutral-800 bg-neutral-100 px-2 py-0.5 rounded-md text-[11px]">
                                            <iconify-icon icon="solar:layers-minimalistic-bold" class="text-neutral-500 text-xs"></iconify-icon>
                                            {{ floatval($o->total_sheets) == intval($o->total_sheets) ? number_format($o->total_sheets, 0, ',', '.') : number_format($o->total_sheets, 1, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-neutral-300">—</span>
                                    @endif
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    @if($o->total_meters > 0)
                                        <span class="inline-flex items-center gap-1 font-semibold text-primary-700 bg-primary-50 px-2 py-0.5 rounded-md text-[11px] border border-primary-100">
                                            <iconify-icon icon="solar:ruler-angular-bold" class="text-primary-500 text-xs"></iconify-icon>
                                            {{ floatval($o->total_meters) == intval($o->total_meters) ? number_format($o->total_meters, 0, ',', '.') : rtrim(rtrim(number_format($o->total_meters, 2, ',', '.'), '0'), ',') }} m
                                        </span>
                                    @else
                                        <span class="text-neutral-300">—</span>
                                    @endif
                                </td>
                                <td class="text-right font-bold text-neutral-900 whitespace-nowrap">
                                    {{ number_format(round($o->total_amount, -3), 0, ',', '.') }}₫
                                </td>
                                <td class="text-right font-semibold text-emerald-600 whitespace-nowrap">
                                    {{ number_format($o->paid, 0, ',', '.') }}₫
                                </td>
                                <td class="text-right font-bold {{ $o->debt > 0 ? 'text-rose-600' : 'text-neutral-400' }} whitespace-nowrap">
                                    {{ number_format($o->debt, 0, ',', '.') }}₫
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeColor }}">
                                        {{ $statusLabels[$o->status] ?? $o->status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('orders.show', $o->id) }}" target="_blank"
                                       class="w-7 h-7 inline-flex items-center justify-center rounded-lg bg-neutral-100 hover:bg-primary-50 text-neutral-600 hover:text-primary-700 transition"
                                       title="Xem chi tiết đơn hàng">
                                        <iconify-icon icon="solar:eye-bold" class="text-sm"></iconify-icon>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center py-12 text-neutral-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <iconify-icon icon="solar:box-minimalistic-linear" class="text-4xl text-neutral-300"></iconify-icon>
                                        <span class="text-xs">Không tìm thấy đơn hàng nào phù hợp với bộ lọc</span>
                                        @if(request('search') || request('status') !== 'all' || request('start_date') || request('end_date'))
                                            <a href="{{ route('customers.show', $customer) }}" class="text-primary-600 font-semibold text-xs hover:underline mt-1">
                                                Đặt lại bộ lọc
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination summary & links --}}
                @if($orders->hasPages() || $orders->total() > 0)
                <div class="px-5 py-4 border-t border-neutral-200 bg-neutral-50/50 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <span class="text-neutral-500 font-medium">
                        Hiển thị <strong>{{ $orders->firstItem() ?? 0 }}</strong> đến <strong>{{ $orders->lastItem() ?? 0 }}</strong> trong tổng số <strong>{{ $orders->total() }}</strong> đơn hàng
                    </span>
                    <div>
                        {{ $orders->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN: Payments History --}}
        <div class="xl:col-span-4 space-y-4">
            <div class="bg-white rounded-2xl border border-neutral-200/80 shadow-xs overflow-hidden">
                {{-- Payments Header --}}
                <div class="p-5 border-b border-neutral-200 flex items-center justify-between bg-neutral-50/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                            <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-neutral-800 m-0">Lịch sử thanh toán</h3>
                            <span class="text-xs text-neutral-400">Các đợt thu tiền từ khách</span>
                        </div>
                    </div>
                    <button type="button" onclick="openModal('add-payment-modal')"
                        class="btn bg-primary-50 text-primary-700 hover:bg-primary-100 border border-primary-200 px-2.5 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1 transition">
                        <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon>
                        Thêm
                    </button>
                </div>

                {{-- Date filter for payments --}}
                <div class="p-3 bg-white border-b border-neutral-200">
                    <form action="{{ route('customers.show', $customer) }}" method="GET" class="flex items-center gap-2">
                        @foreach(request()->except(['payment_date', 'payment_page']) as $k => $v)
                            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                        @endforeach
                        <input type="date" name="payment_date" value="{{ request('payment_date') }}"
                               class="form-control rounded-lg text-xs py-1.5 px-2.5 border border-neutral-200 flex-1">
                        <button type="submit" class="btn bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-semibold px-3 py-1.5">
                            Lọc
                        </button>
                        @if(request('payment_date'))
                            <a href="{{ request()->fullUrlWithQuery(['payment_date' => null, 'payment_page' => 1]) }}"
                               class="btn bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-lg text-xs font-semibold px-2.5 py-1.5">
                                Xóa
                            </a>
                        @endif
                    </form>
                </div>

                {{-- Payments List --}}
                <div class="p-4 space-y-3 max-h-[560px] overflow-y-auto">
                    @forelse($payments as $p)
                        @php
                            $methodBadge = match($p->payment_method) {
                                'cash'     => 'bg-amber-50 text-amber-700 border-amber-200',
                                'transfer' => 'bg-blue-50 text-blue-700 border-blue-200',
                                default    => 'bg-neutral-100 text-neutral-700 border-neutral-200'
                            };
                            $methodLabel = \App\Models\CustomerPayment::methodLabel($p->payment_method);
                        @endphp
                        <div class="bg-neutral-50/70 border border-neutral-200/80 rounded-xl p-3 hover:border-neutral-300 transition">
                            <div class="flex items-start justify-between gap-2">
                                <div class="space-y-1 min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs font-bold text-neutral-800">
                                            {{ $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->format('d/m/Y') : '—' }}
                                        </span>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $methodBadge }}">
                                            {{ $methodLabel }}
                                        </span>
                                    </div>
                                    <div class="text-base font-bold text-emerald-600">
                                        +{{ number_format($p->amount, 0, ',', '.') }}₫
                                    </div>
                                    @if($p->order)
                                        <div class="text-[11px] text-primary-600 flex items-center gap-1">
                                            <iconify-icon icon="solar:link-circle-bold"></iconify-icon>
                                            Liên kết đơn: <a href="{{ route('orders.show', $p->order->id) }}" target="_blank" class="font-bold underline">{{ $p->order->order_code }}</a>
                                        </div>
                                    @endif
                                    @if($p->note)
                                        <p class="text-[11px] text-neutral-500 m-0 italic">{{ $p->note }}</p>
                                    @endif
                                    <div class="text-[10px] text-neutral-400">
                                        Tạo bởi: {{ $p->creator->name ?? 'Hệ thống' }}
                                    </div>
                                </div>

                                {{-- Action buttons: Edit & Delete --}}
                                <div class="flex items-center gap-1 shrink-0">
                                    <button type="button"
                                        onclick="openEditCustPaymentModal({{ $p->id }}, '{{ $p->payment_date ? $p->payment_date->format('Y-m-d') : '' }}', {{ $p->amount }}, '{{ $p->payment_method }}', '{{ addslashes($p->note ?? '') }}', '{{ $p->order_id ?? '' }}')"
                                        class="w-7 h-7 rounded-lg hover:bg-neutral-200 text-neutral-400 hover:text-primary-600 flex items-center justify-center transition"
                                        title="Sửa đợt thanh toán">
                                        <iconify-icon icon="lucide:edit-2" class="text-sm"></iconify-icon>
                                    </button>
                                    <form method="POST" action="{{ route('customers.payments.destroy', [$customer->id, $p->id]) }}"
                                          onsubmit="return confirm('Bạn có chắc muốn xóa đợt thanh toán {{ number_format($p->amount, 0, ',', '.') }}₫ này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-7 h-7 rounded-lg hover:bg-rose-50 text-neutral-400 hover:text-rose-600 flex items-center justify-center transition"
                                            title="Xóa đợt thanh toán">
                                            <iconify-icon icon="lucide:trash-2" class="text-sm"></iconify-icon>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 text-neutral-400">
                            <iconify-icon icon="solar:wallet-money-linear" class="text-3xl text-neutral-300"></iconify-icon>
                            <p class="text-xs m-0 mt-2">Chưa có giao dịch thanh toán nào</p>
                        </div>
                    @endforelse
                </div>

                {{-- Payments Pagination --}}
                @if($payments->hasPages())
                <div class="p-3 border-t border-neutral-200 bg-neutral-50/50 flex justify-center">
                    {{ $payments->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ===== MODAL: THÊM THANH TOÁN ===== --}}
<x-modal name="add-payment-modal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-white">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center text-lg">
                <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
            </div>
            <h5 class="font-bold text-sm text-neutral-800 m-0">Ghi nhận thanh toán</h5>
        </div>
        <button type="button" onclick="closeModal('add-payment-modal')" class="text-neutral-400 hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('customers.payments.store', $customer->id) }}" method="POST" class="p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-semibold text-neutral-700 mb-1 block">Ngày thanh toán <span class="text-rose-500">*</span></label>
                <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                       class="form-control rounded-xl text-xs py-2 px-3 w-full border border-neutral-300">
            </div>
            <div>
                <label class="text-xs font-semibold text-neutral-700 mb-1 block">Số tiền (₫) <span class="text-rose-500">*</span></label>
                <input type="number" name="amount" min="1" required placeholder="Nhập số tiền"
                       class="form-control rounded-xl text-xs py-2 px-3 w-full border border-neutral-300 font-bold">
            </div>
        </div>
        <div>
            <label class="text-xs font-semibold text-neutral-700 mb-1 block">Hình thức thanh toán</label>
            <select name="payment_method" class="form-select rounded-xl text-xs py-2 px-3 w-full border border-neutral-300 bg-white">
                <option value="cash">Tiền mặt</option>
                <option value="transfer" selected>Chuyển khoản</option>
                <option value="other">Khác</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold text-neutral-700 mb-1 block">Liên kết đơn hàng (Tùy chọn)</label>
            <select name="order_id" class="form-select rounded-xl text-xs py-2 px-3 w-full border border-neutral-300 bg-white">
                <option value="">-- Không liên kết đơn cụ thể (Cấn trừ chung) --</option>
                @foreach($customerOrders as $co)
                    <option value="{{ $co->id }}">{{ $co->order_code }} ({{ number_format(round($co->total_amount, -3), 0, ',', '.') }}₫)</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold text-neutral-700 mb-1 block">Ghi chú</label>
            <textarea name="note" rows="2" placeholder="Ghi chú đợt thanh toán (nếu có)..."
                      class="form-control rounded-xl text-xs py-2 px-3 w-full border border-neutral-300"></textarea>
        </div>
        <div class="pt-2 flex justify-end gap-2 border-t border-neutral-100">
            <button type="button" onclick="closeModal('add-payment-modal')" class="btn btn-neutral px-4 py-2 text-xs rounded-xl font-semibold">Hủy</button>
            <button type="submit" class="btn btn-primary px-5 py-2 text-xs rounded-xl font-semibold flex items-center gap-1.5 shadow-xs">
                <iconify-icon icon="lucide:check" class="text-sm"></iconify-icon> Lưu thanh toán
            </button>
        </div>
    </form>
</x-modal>

{{-- ===== MODAL: SỬA THANH TOÁN ===== --}}
<x-modal name="edit-payment-modal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-white">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <iconify-icon icon="solar:pen-new-square-bold-duotone"></iconify-icon>
            </div>
            <h5 class="font-bold text-sm text-neutral-800 m-0">Sửa đợt thanh toán</h5>
        </div>
        <button type="button" onclick="closeModal('edit-payment-modal')" class="text-neutral-400 hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form id="edit-payment-form" action="" method="POST" class="p-6 space-y-4">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-semibold text-neutral-700 mb-1 block">Ngày thanh toán <span class="text-rose-500">*</span></label>
                <input type="date" id="epm-date" name="payment_date" required
                       class="form-control rounded-xl text-xs py-2 px-3 w-full border border-neutral-300">
            </div>
            <div>
                <label class="text-xs font-semibold text-neutral-700 mb-1 block">Số tiền (₫) <span class="text-rose-500">*</span></label>
                <input type="number" id="epm-amount" name="amount" min="1" required
                       class="form-control rounded-xl text-xs py-2 px-3 w-full border border-neutral-300 font-bold">
            </div>
        </div>
        <div>
            <label class="text-xs font-semibold text-neutral-700 mb-1 block">Hình thức thanh toán</label>
            <select id="epm-method" name="payment_method" class="form-select rounded-xl text-xs py-2 px-3 w-full border border-neutral-300 bg-white">
                <option value="cash">Tiền mặt</option>
                <option value="transfer">Chuyển khoản</option>
                <option value="other">Khác</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold text-neutral-700 mb-1 block">Liên kết đơn hàng (Tùy chọn)</label>
            <select id="epm-order" name="order_id" class="form-select rounded-xl text-xs py-2 px-3 w-full border border-neutral-300 bg-white">
                <option value="">-- Không liên kết đơn cụ thể --</option>
                @foreach($customerOrders as $co)
                    <option value="{{ $co->id }}">{{ $co->order_code }} ({{ number_format(round($co->total_amount, -3), 0, ',', '.') }}₫)</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold text-neutral-700 mb-1 block">Ghi chú</label>
            <textarea id="epm-note" name="note" rows="2"
                      class="form-control rounded-xl text-xs py-2 px-3 w-full border border-neutral-300"></textarea>
        </div>
        <div class="pt-2 flex justify-end gap-2 border-t border-neutral-100">
            <button type="button" onclick="closeModal('edit-payment-modal')" class="btn btn-neutral px-4 py-2 text-xs rounded-xl font-semibold">Hủy</button>
            <button type="submit" class="btn btn-primary px-5 py-2 text-xs rounded-xl font-semibold flex items-center gap-1.5 shadow-xs">
                <iconify-icon icon="lucide:check" class="text-sm"></iconify-icon> Cập nhật
            </button>
        </div>
    </form>
</x-modal>

{{-- ===== MODAL: SỬA KHÁCH HÀNG ===== --}}
@can('edit customer')
<x-modal name="edit-customer-modal" maxWidth="7xl">
    <div class="px-8 py-5 border-b border-neutral-200 flex items-center justify-between shrink-0 bg-white">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-xs">
                <iconify-icon icon="solar:pen-new-square-bold-duotone"></iconify-icon>
            </div>
            <div>
                <h5 class="font-bold text-lg text-neutral-800 m-0">Chỉnh sửa khách hàng</h5>
                <p class="text-xs text-neutral-400 m-0 mt-0.5">Cập nhật thông tin chi tiết và hồ sơ thị trường</p>
            </div>
        </div>
        <button type="button" onclick="closeModal('edit-customer-modal')" class="text-neutral-400 hover:text-neutral-700 text-2xl leading-none w-8 h-8 flex items-center justify-center rounded-lg hover:bg-neutral-100 transition-colors">&times;</button>
    </div>
    <form id="edit-customer-form" action="{{ route('customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-col overflow-hidden max-h-[calc(94vh-80px)]">
        @csrf @method('PUT')
        <div class="p-8 overflow-y-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
                {{-- Cột 1: Thông tin cơ bản --}}
                <div class="lg:col-span-6 space-y-4">
                    <div class="border-b border-neutral-200 pb-2 flex items-center gap-2.5">
                        <iconify-icon icon="solar:user-id-bold-duotone" class="text-primary-600 text-xl"></iconify-icon>
                        <h6 class="font-bold text-sm uppercase tracking-wider text-neutral-800 m-0">Thông tin cơ bản & Vị trí</h6>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Mã khách hàng</label>
                            <input type="text" id="edit_customer_code" name="customer_code" value="{{ $customer->customer_code }}" class="form-control rounded-xl text-sm py-2.5 px-3.5">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Nhóm thị trường</label>
                            <select id="edit_market_group_id" name="market_group_id" class="form-select rounded-xl text-sm py-2.5 px-3.5 w-full">
                                <option value="">-- Chưa chọn nhóm --</option>
                                @foreach($marketGroups as $mg)
                                    <option value="{{ $mg->id }}" {{ $customer->market_group_id == $mg->id ? 'selected' : '' }}>
                                        {{ $mg->name }} ({{ $mg->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Tên khách hàng <span class="text-danger-500">*</span></label>
                            <input type="text" id="edit_name" name="name" value="{{ $customer->name }}" class="form-control rounded-xl text-sm py-2.5 px-3.5" required>
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Số điện thoại</label>
                            <input type="text" id="edit_phone" name="phone" value="{{ $customer->phone }}" class="form-control rounded-xl text-sm py-2.5 px-3.5">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Tỉnh / Thành phố</label>
                            <input type="text" id="edit_province" name="province" value="{{ $customer->province }}" class="form-control rounded-xl text-sm py-2.5 px-3.5">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Quận / Huyện / Xã</label>
                            <input type="text" id="edit_ward" name="ward" value="{{ $customer->ward }}" class="form-control rounded-xl text-sm py-2.5 px-3.5">
                        </div>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Địa chỉ chi tiết</label>
                        <input type="text" id="edit_address" name="address" value="{{ $customer->address }}" class="form-control rounded-xl text-sm py-2.5 px-3.5">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Vĩ độ (Latitude)</label>
                            <input type="text" id="edit_latitude" name="latitude" value="{{ $customer->latitude }}" class="form-control rounded-xl text-sm py-2.5 px-3.5">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Kinh độ (Longitude)</label>
                            <input type="text" id="edit_longitude" name="longitude" value="{{ $customer->longitude }}" class="form-control rounded-xl text-sm py-2.5 px-3.5">
                        </div>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Ghi chú cập nhật</label>
                        <input type="text" name="edit_note" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Ví dụ: Cập nhật địa chỉ xưởng, số điện thoại...">
                    </div>
                </div>

                {{-- Cột 2: Hồ sơ thị trường --}}
                <div class="lg:col-span-6 space-y-4">
                    <div class="border-b border-neutral-200 pb-2 flex items-center gap-2.5">
                        <iconify-icon icon="solar:chart-square-bold-duotone" class="text-primary-600 text-xl"></iconify-icon>
                        <h6 class="font-bold text-sm uppercase tracking-wider text-neutral-800 m-0">Hồ sơ thị trường & CSKH</h6>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Tình trạng hợp tác</label>
                        <select id="edit_status" name="status" class="form-select rounded-xl text-sm py-2.5 px-3.5 w-full">
                            <option value="Đang đặt hàng" {{ $customer->status == 'Đang đặt hàng' ? 'selected' : '' }}>Đang đặt hàng</option>
                            <option value="Không đặt GERVIN" {{ $customer->status == 'Không đặt GERVIN' ? 'selected' : '' }}>Không đặt GERVIN</option>
                            <option value="Khách hàng mới tiềm năng" {{ $customer->status == 'Khách hàng mới tiềm năng' ? 'selected' : '' }}>Khách hàng mới tiềm năng</option>
                            <option value="Tạm dừng hợp tác" {{ $customer->status == 'Tạm dừng hợp tác' ? 'selected' : '' }}>Tạm dừng hợp tác</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Đối tác KH đang hợp tác</label>
                        <input type="text" id="edit_partner_competitors" name="partner_competitors" value="{{ $customer->partner_competitors }}" class="form-control rounded-xl text-sm py-2.5 px-3.5" placeholder="Ví dụ: ALD, LIVAS, GERVIN...">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Quy mô xưởng</label>
                            <input type="text" id="edit_workshop_scale" name="workshop_scale" value="{{ $customer->workshop_scale }}" class="form-control rounded-xl text-sm py-2.5 px-3.5">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Tính cách khách hàng</label>
                            <input type="text" id="edit_personality" name="personality" value="{{ $customer->personality }}" class="form-control rounded-xl text-sm py-2.5 px-3.5">
                        </div>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Phản ánh khách hàng về Gervin</label>
                        <textarea id="edit_feedback" name="feedback" rows="2" class="form-control rounded-xl text-sm py-2 px-3.5">{{ $customer->feedback }}</textarea>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Đề xuất Khách hàng (nếu có)</label>
                        <textarea id="edit_customer_proposal" name="customer_proposal" rows="2" class="form-control rounded-xl text-sm py-2 px-3.5">{{ $customer->customer_proposal }}</textarea>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Đề xuất Sale (CSKH / kéo khách / mở mới)</label>
                        <textarea id="edit_sale_proposal" name="sale_proposal" rows="2" class="form-control rounded-xl text-sm py-2 px-3.5">{{ $customer->sale_proposal }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Định mức công nợ (₫)</label>
                            <input type="number" id="edit_debt_limit" name="debt_limit" value="{{ $customer->debt_limit }}" min="0" class="form-control rounded-xl text-sm py-2.5 px-3.5">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-700 mb-1.5 block">Chính sách khách hàng</label>
                            <input type="text" id="edit_policy" name="policy" value="{{ $customer->policy }}" class="form-control rounded-xl text-sm py-2.5 px-3.5">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-8 py-4 border-t border-neutral-200 flex justify-end gap-3.5 shrink-0 bg-white">
            <button type="button" onclick="closeModal('edit-customer-modal')" class="btn btn-neutral px-6 py-2.5 text-xs rounded-xl font-semibold">Hủy</button>
            <button type="submit" class="btn btn-primary px-7 py-2.5 text-xs rounded-xl font-semibold flex items-center gap-2 shadow-sm">
                <iconify-icon icon="lucide:check" class="text-base"></iconify-icon> Cập nhật thông tin
            </button>
        </div>
    </form>
</x-modal>
@endcan

{{-- ===== MODAL: LỊCH SỬ CHỈNH SỬA ===== --}}
<x-modal name="customer-history-modal" maxWidth="4xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between shrink-0 bg-white">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-lg">
                <iconify-icon icon="solar:history-bold-duotone"></iconify-icon>
            </div>
            <div>
                <h5 class="font-bold text-sm text-neutral-800 m-0">Lịch sử chỉnh sửa: <span id="history_modal_customer_title"></span></h5>
                <span class="text-xs text-neutral-400 font-normal" id="customer-history-total-count">Đang tải...</span>
            </div>
        </div>
        <button type="button" onclick="closeModal('customer-history-modal')" class="text-neutral-400 hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <div class="p-6 max-h-[75vh] overflow-y-auto">
        <div id="customer-history-loading" class="text-center py-10">
            <iconify-icon icon="lucide:loader-2" class="text-3xl text-amber-600 animate-spin"></iconify-icon>
            <div class="text-xs text-neutral-500 mt-2 font-medium">Đang tải lịch sử chỉnh sửa...</div>
        </div>
        <div id="customer-history-empty" class="hidden text-center py-10 text-neutral-400">
            <iconify-icon icon="solar:history-linear" class="text-4xl text-neutral-300"></iconify-icon>
            <div class="text-xs mt-2 font-medium">Khách hàng chưa có lịch sử chỉnh sửa nào</div>
        </div>
        <div id="customer-history-timeline" class="hidden space-y-3"></div>
    </div>
</x-modal>

{{-- ===== IMAGE LIGHTBOX MODAL ===== --}}
<div id="image-lightbox-modal" class="hidden fixed inset-0 z-[3000] bg-black/85 backdrop-blur-sm flex flex-col items-center justify-center p-4" onclick="closeImageLightbox()">
    <div class="relative max-w-4xl max-h-[92vh] flex flex-col items-center" onclick="event.stopPropagation()">
        <button type="button" onclick="closeImageLightbox()"
                class="absolute -top-12 right-0 text-white/80 hover:text-white bg-neutral-900/70 rounded-full p-2 hover:bg-neutral-900 transition flex items-center justify-center shadow-lg">
            <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
        </button>
        <img id="image-lightbox-img" src="" alt="Customer Photo" class="max-h-[82vh] max-w-full rounded-xl shadow-2xl border border-white/20 object-contain bg-black">
        <div id="image-lightbox-caption" class="mt-3 text-white/90 text-xs md:text-sm font-medium bg-neutral-900/80 px-4 py-1.5 rounded-full border border-white/10 max-w-xl text-center truncate"></div>
    </div>
</div>

@push('scripts')
<script>
function toggleDetailsSection() {
    const body = document.getElementById('details-section-body');
    const text = document.getElementById('toggle-details-text');
    const icon = document.getElementById('toggle-details-icon');
    if (!body) return;
    if (body.classList.contains('hidden')) {
        body.classList.remove('hidden');
        text.textContent = 'Thu gọn';
        icon.setAttribute('icon', 'lucide:chevron-up');
    } else {
        body.classList.add('hidden');
        text.textContent = 'Mở rộng';
        icon.setAttribute('icon', 'lucide:chevron-down');
    }
}

function openEditCustPaymentModal(id, date, amount, method, note, orderId) {
    const form = document.getElementById('edit-payment-form');
    form.action = '/customers/{{ $customer->id }}/payments/' + id;
    document.getElementById('epm-date').value = date;
    document.getElementById('epm-amount').value = amount;
    document.getElementById('epm-method').value = method;
    document.getElementById('epm-note').value = note || '';
    document.getElementById('epm-order').value = orderId || '';
    openModal('edit-payment-modal');
}

function openEditCustomerModalFromBtn(btn) {
    openModal('edit-customer-modal');
}

function openImageLightbox(src, caption) {
    const modal = document.getElementById('image-lightbox-modal');
    const img   = document.getElementById('image-lightbox-img');
    const cap   = document.getElementById('image-lightbox-caption');
    if (!modal || !img) return;
    img.src = src;
    if (cap) cap.textContent = caption || '';
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageLightbox() {
    const modal = document.getElementById('image-lightbox-modal');
    const img   = document.getElementById('image-lightbox-img');
    if (!modal) return;
    modal.classList.add('hidden');
    if (img) img.src = '';
    document.body.style.overflow = '';
}

function openCustomerHistoryModal(customerId, customerName, customerCode) {
    document.getElementById('history_modal_customer_title').textContent = (customerCode ? `[${customerCode}] ` : '') + customerName;
    const loading = document.getElementById('customer-history-loading');
    const empty = document.getElementById('customer-history-empty');
    const timeline = document.getElementById('customer-history-timeline');
    const countEl = document.getElementById('customer-history-total-count');

    loading.classList.remove('hidden');
    empty.classList.add('hidden');
    timeline.classList.add('hidden');
    timeline.innerHTML = '';

    openModal('customer-history-modal');

    fetch(`/customers/${customerId}/histories`)
        .then(res => res.json())
        .then(data => {
            loading.classList.add('hidden');
            if (!data.histories || data.histories.length === 0) {
                empty.classList.remove('hidden');
                countEl.textContent = '0 lần chỉnh sửa';
                return;
            }

            countEl.textContent = `${data.histories.length} lần chỉnh sửa`;
            timeline.classList.remove('hidden');

            data.histories.forEach((h, idx) => {
                const item = document.createElement('div');
                item.className = 'p-4 rounded-xl border border-neutral-200 bg-white shadow-2xs hover:border-neutral-300 transition';

                const photosHtml = (h.photos && h.photos.length > 0)
                    ? `<div class="mt-2.5 flex items-center gap-2 flex-wrap">` +
                        h.photos.map(p => {
                            const src = (p.startsWith('http') || p.startsWith('/')) ? p : '/' + p;
                            return `<img src="${src}" class="w-14 h-14 rounded-lg border border-neutral-200 object-cover cursor-pointer hover:scale-105 transition-transform" onclick="openImageLightbox('${src}', 'Ảnh ngày ${h.created_at}')">`;
                        }).join('') +
                      `</div>`
                    : '';

                const mapsHtml = (h.latitude && h.longitude)
                    ? `<a href="https://www.google.com/maps?q=${h.latitude},${h.longitude}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-primary-600 hover:text-primary-800 bg-primary-50 px-2 py-0.5 rounded border border-primary-200">
                        <iconify-icon icon="solar:map-point-wave-bold" class="text-rose-500"></iconify-icon> GPS (${Number(h.latitude).toFixed(3)}, ${Number(h.longitude).toFixed(3)})
                       </a>`
                    : '';

                let changesHtml = '';
                if (h.changes && Array.isArray(h.changes) && h.changes.length > 0) {
                    changesHtml = `<div class="bg-neutral-50 rounded-lg p-2.5 my-2 border border-neutral-100 text-xs space-y-1">` +
                        h.changes.map(ch => `
                            <div>
                                <span class="font-bold text-neutral-600">${ch.label || ch.field}:</span>
                                ${ch.old ? `<span class="line-through text-neutral-400 mx-1">${ch.old}</span> →` : ''}
                                <span class="font-semibold text-emerald-700 ml-1">${ch.new || '(Trống)'}</span>
                            </div>
                        `).join('') +
                    `</div>`;
                }

                item.innerHTML = `
                    <div class="flex items-start justify-between gap-3 border-b border-neutral-100 pb-2.5 mb-2">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-xs shrink-0">
                                #${data.histories.length - idx}
                            </span>
                            <div>
                                <div class="text-xs font-bold text-neutral-800 flex items-center gap-2">
                                    <span>${h.created_at}</span>
                                    <span class="text-[11px] text-neutral-400 font-normal">(${h.diff})</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold ${h.action === 'created' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'}">
                                        ${h.action === 'created' ? 'Tạo mới' : 'Cập nhật'}
                                    </span>
                                </div>
                                <div class="text-[11px] text-neutral-500 mt-0.5">
                                    Thực hiện bởi: <strong class="text-neutral-700">${h.user_name}</strong>
                                </div>
                            </div>
                        </div>
                        <div>${mapsHtml}</div>
                    </div>

                    ${h.note ? `<div class="bg-amber-50/80 border border-amber-200 text-amber-900 rounded-lg p-2 text-xs mb-2"><strong>Ghi chú:</strong> ${h.note}</div>` : ''}
                    ${changesHtml}
                    ${h.summary && !changesHtml ? `<div class="text-xs text-neutral-600 italic mb-2">${h.summary}</div>` : ''}
                    ${photosHtml}
                `;
                timeline.appendChild(item);
            });
        })
        .catch(err => {
            loading.classList.add('hidden');
            empty.classList.remove('hidden');
            empty.innerHTML = `<div class="text-danger-500 text-xs font-semibold">Lỗi khi tải lịch sử: ${err.message}</div>`;
        });
}
</script>
@endpush

@endsection
