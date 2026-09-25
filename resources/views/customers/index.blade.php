@extends('layout.layout')
@php
    $title    = 'Khách hàng';
    $subTitle = 'Danh sách khách hàng';
@endphp

@section('content')

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .stats-custom-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 16px;
    }
    @media (min-width: 768px) {
        .stats-custom-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
    }
</style>

<div class="grid grid-cols-12 gap-y-6">
    <div class="col-span-12">
        {{-- Statistics Grid --}}
        <div class="stats-custom-grid">
            <!-- Card 1: Tổng khách hàng -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center text-primary-600 text-xl flex-shrink-0">
                    <iconify-icon icon="lucide:users"></iconify-icon>
                </div>
                <div>
                    <div class="text-xs text-neutral-500 font-medium mb-0.5">Tổng khách hàng</div>
                    <div class="text-lg font-bold text-neutral-800">{{ $totalCustomersCount }} KH</div>
                </div>
            </div>
            <!-- Card 2: Khách hàng mới -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-xl flex-shrink-0">
                    <iconify-icon icon="lucide:user-plus"></iconify-icon>
                </div>
                <div>
                    <div class="text-xs text-neutral-500 font-medium mb-0.5">
                        Khách hàng mới
                        @if($startDate || $endDate)
                            <span style="font-size: 9px;" class="font-normal text-neutral-400 capitalize">
                                @if($startDate && $endDate)
                                    ({{ \Carbon\Carbon::parse($startDate)->format('d/m') }})
                                @endif
                            </span>
                        @else
                            <span style="font-size: 9px;" class="font-normal text-neutral-400 capitalize">(Tháng này)</span>
                        @endif
                    </div>
                    <div class="text-lg font-bold text-neutral-800">{{ $newCustomersCount }} KH</div>
                </div>
            </div>
            <!-- Card 3: Khách mất đi -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 text-xl flex-shrink-0">
                    <iconify-icon icon="lucide:user-minus"></iconify-icon>
                </div>
                <div>
                    <div class="text-xs text-neutral-500 font-medium mb-0.5" title="Khách hàng không phát sinh đơn hàng trong 2 tháng gần nhất">Khách mất đi <iconify-icon icon="lucide:info" style="font-size:10px; vertical-align: middle;"></iconify-icon></div>
                    <div class="text-lg font-bold text-neutral-800">{{ $lostCustomersCount }} KH</div>
                </div>
            </div>
            <!-- Card 4: Tổng đã thanh toán -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-success-50 flex items-center justify-center text-success-600 text-xl flex-shrink-0">
                    <iconify-icon icon="lucide:check-circle"></iconify-icon>
                </div>
                <div class="min-w-0">
                    <div class="text-xs text-neutral-500 font-medium mb-0.5 truncate">
                        Đã thanh toán
                        @if($startDate || $endDate)
                            <span style="font-size: 9px;" class="font-normal text-neutral-400 capitalize">
                                @if($startDate && $endDate)
                                    ({{ \Carbon\Carbon::parse($startDate)->format('d/m') }})
                                @endif
                            </span>
                        @else
                            <span style="font-size: 9px;" class="font-normal text-neutral-400 capitalize">(Tháng)</span>
                        @endif
                    </div>
                    <div class="text-lg font-bold text-neutral-800 truncate">{{ number_format($totalPeriodPaidSum, 0, ',', '.') }}₫</div>
                </div>
            </div>
            <!-- Card 5: Tổng công nợ -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-danger-50 flex items-center justify-center text-danger-600 text-xl flex-shrink-0">
                    <iconify-icon icon="lucide:alert-circle"></iconify-icon>
                </div>
                <div class="min-w-0">
                    <div class="text-xs text-neutral-500 font-medium mb-0.5">Tổng công nợ</div>
                    <div class="text-lg font-bold text-neutral-800 truncate">{{ number_format($totalDebtSum, 0, ',', '.') }}₫</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12">
        <div class="card h-full p-0 rounded-xl border-0 overflow-hidden">
            {{-- Header --}}
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                <div class="flex items-center flex-wrap gap-3">
                    {{-- Search (Bên trái ngoài cùng) --}}
                    <form method="GET" action="{{ route('customers.index') }}" class="navbar-search">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <div class="relative">
                            <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-base"></iconify-icon>
                            <input type="text" name="search" class="form-control form-control-sm border-neutral-200 rounded-lg pl-9 pr-3 w-64 md:w-80" placeholder="Tìm kiếm..." value="{{ $search }}">
                        </div>
                    </form>

                    {{-- Per page --}}
                    <div class="flex items-center gap-2">
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
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="openModal('filter-modal')"
                        class="btn bg-light-600 text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="solar:filter-outline" class="icon text-xl line-height-1"></iconify-icon>
                        Lọc
                    </button>
                    @if(request()->filled('filter_customer_code') || request()->filled('filter_name') || request()->filled('filter_phone') || request()->filled('filter_start_date') || request()->filled('filter_end_date') || request()->filled('filter_customer_id') || request()->filled('filter_debt_level') || request()->filled('filter_market_group_id'))
                    <a href="{{ route('customers.index') }}" class="btn text-sm btn-sm px-2 py-2 rounded-lg flex items-center gap-2">
                        <iconify-icon icon="solar:close-circle-outline" class="icon text-xl line-height-1"></iconify-icon>
                        Xóa lọc
                    </a>
                    @endif
                    @can('add customer')
                    <button type="button" onclick="openCreateCustomerModal()"
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
                                <th scope="col">Nhóm thị trường</th>
                                <th scope="col" class="text-right">
                                    Đã thanh toán
                                    @if($startDate || $endDate)
                                        <div style="font-size: 10px;" class="font-normal text-neutral-400 capitalize">
                                            @if($startDate && $endDate)
                                                ({{ \Carbon\Carbon::parse($startDate)->format('d/m') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m') }})
                                            @elseif($startDate)
                                                (Từ {{ \Carbon\Carbon::parse($startDate)->format('d/m') }})
                                            @else
                                                (Đến {{ \Carbon\Carbon::parse($endDate)->format('d/m') }})
                                            @endif
                                        </div>
                                    @else
                                        <div style="font-size: 10px;" class="font-normal text-neutral-400 capitalize">(Tháng này)</div>
                                    @endif
                                </th>
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
                                    <span class="font-semibold text-neutral-800">{{ $c->customer_code ?? '—' }}</span>
                                </td>
                                <td>
                                    <div class="font-bold text-neutral-800">{{ $c->name }}</div>
                                    <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                        @if($c->status)
                                            @php
                                                $statusColor = match($c->status) {
                                                    'Đang đặt hàng' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                    'Không đặt GERVIN' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                    'Khách hàng mới tiềm năng' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                    'Tạm dừng hợp tác' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                    default => 'bg-neutral-100 text-neutral-600 border-neutral-200'
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold border {{ $statusColor }}">
                                                {{ $c->status }}
                                            </span>
                                        @endif
                                        @if($c->partner_competitors)
                                            <span class="text-[10px] text-neutral-500 bg-neutral-100 px-1.5 py-0.5 rounded" title="Đối tác: {{ $c->partner_competitors }}">
                                                <iconify-icon icon="lucide:handshake" class="inline text-xs mr-0.5"></iconify-icon>{{ Str::limit($c->partner_competitors, 20) }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $c->phone ?: '—' }}</td>
                                <td>
                                    @php
                                        $fullAddress = implode(', ', array_filter([$c->address, $c->ward, $c->province]));
                                    @endphp
                                    <div class="flex items-start gap-1">
                                        <div class="max-w-[200px] truncate text-neutral-700" title="{{ $fullAddress ?: ($c->address ?: '—') }}">
                                            {{ $fullAddress ?: ($c->address ?: '—') }}
                                        </div>
                                        @if($c->latitude && $c->longitude)
                                            <a href="https://www.google.com/maps?q={{ $c->latitude }},{{ $c->longitude }}" target="_blank" class="text-primary-600 hover:text-primary-800 shrink-0" title="Mở bản đồ toạ độ: {{ $c->latitude }}, {{ $c->longitude }}">
                                                <iconify-icon icon="solar:map-point-bold" class="text-sm text-red-500"></iconify-icon>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="max-w-[200px] truncate text-neutral-600" title="{{ $c->policy }}">{{ $c->policy ?: '—' }}</div>
                                </td>
                                <td>
                                    @if($c->marketGroup)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-200">
                                            <iconify-icon icon="solar:users-group-two-rounded-bold" class="mr-1 text-sm"></iconify-icon>
                                            {{ $c->marketGroup->name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-neutral-400 italic">Chưa phân nhóm</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <span class="text-base font-bold text-success-600">
                                        {{ number_format($c->period_paid, 0, ',', '.') }} đ
                                    </span>
                                </td>
                                <td class="text-right">
                                    <span class="text-base font-bold {{ $c->total_debt > 0 ? 'text-danger-600' : 'text-success-600' }}">
                                        {{ number_format($c->total_debt, 0, ',', '.') }} đ
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center gap-3 justify-center">
                                        <button type="button"
                                            class="bg-info-100 hover:bg-info-200 text-info-600 font-medium w-9 h-9 flex justify-center items-center rounded-full transition-colors"
                                            title="Xem tổng quan"
                                            data-customer-id="{{ $c->id }}"
                                            onclick="openCustomerOverview({{ $c->id }})">
                                            <iconify-icon icon="majesticons:eye-line" class="text-lg"></iconify-icon>
                                        </button>
                                        @can('edit customer')
                                        <button type="button"
                                            class="bg-success-100 hover:bg-success-200 text-success-600 font-medium w-9 h-9 flex justify-center items-center rounded-full transition-colors customer-edit-btn"
                                            title="Sửa"
                                            data-id="{{ $c->id }}"
                                            data-code="{{ $c->customer_code ?? '' }}"
                                            data-name="{{ $c->name }}"
                                            data-phone="{{ $c->phone ?? '' }}"
                                            data-address="{{ $c->address ?? '' }}"
                                            data-latitude="{{ $c->latitude ?? '' }}"
                                            data-longitude="{{ $c->longitude ?? '' }}"
                                            data-province="{{ $c->province ?? '' }}"
                                            data-ward="{{ $c->ward ?? '' }}"
                                            data-status="{{ $c->status ?? 'Đang đặt hàng' }}"
                                            data-partner-competitors="{{ $c->partner_competitors ?? '' }}"
                                            data-feedback="{{ $c->feedback ?? '' }}"
                                            data-personality="{{ $c->personality ?? '' }}"
                                            data-workshop-scale="{{ $c->workshop_scale ?? '' }}"
                                            data-customer-proposal="{{ $c->customer_proposal ?? '' }}"
                                            data-sale-proposal="{{ $c->sale_proposal ?? '' }}"
                                            data-debt="{{ $c->debt ?? 0 }}"
                                            data-debt-limit="{{ $c->debt_limit ?? 0 }}"
                                            data-policy="{{ $c->policy ?? '' }}"
                                            data-market-group-id="{{ $c->market_group_id ?? '' }}"
                                            onclick="openEditCustomerModalFromBtn(this)">
                                            <iconify-icon icon="lucide:edit" class="text-lg"></iconify-icon>
                                        </button>
                                        @endcan
                                        @can('delete customer')
                                        <form action="{{ route('customers.destroy', $c->id) }}" method="POST"
                                            onsubmit="return confirm('Bạn có chắc muốn xóa khách hàng này?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="bg-danger-100 hover:bg-danger-200 text-danger-600 font-medium w-9 h-9 flex justify-center items-center rounded-full transition-colors"
                                                title="Xóa">
                                                <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center text-neutral-400 py-8">
                                    Không tìm thấy khách hàng nào.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-neutral-200">
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Thêm khách hàng --}}
{{-- Modal Thêm khách hàng --}}
@can('add customer')
<x-modal name="create-customer-modal" maxWidth="5xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between shrink-0 bg-white">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center text-lg">
                <iconify-icon icon="solar:user-plus-bold-duotone"></iconify-icon>
            </div>
            <div>
                <h5 class="font-bold text-base text-neutral-800 m-0">Thêm mới khách hàng</h5>
                <p class="text-[11px] text-neutral-400 m-0">Nhập đầy đủ thông tin, định vị bản đồ và hồ sơ thị trường của khách hàng</p>
            </div>
        </div>
        <button type="button" onclick="closeModal('create-customer-modal')" class="text-neutral-400 hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('customers.store') }}" method="POST" class="flex flex-col overflow-hidden max-h-[calc(95vh-65px)]">
        @csrf
        <div class="p-6 overflow-y-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Cột 1: Thông tin cơ bản, Địa chỉ & Bản đồ Leaflet --}}
                <div class="lg:col-span-6 space-y-3.5">
                    <div class="border-b border-neutral-200 pb-1.5 flex items-center gap-2">
                        <iconify-icon icon="solar:user-id-bold-duotone" class="text-primary-600 text-lg"></iconify-icon>
                        <h6 class="font-bold text-xs uppercase tracking-wider text-neutral-700 m-0">Thông tin cơ bản & Vị trí</h6>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Mã khách hàng</label>
                            <input type="text" name="customer_code" class="form-control rounded-lg text-xs" placeholder="Tự sinh nếu để trống" value="{{ old('customer_code') }}">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Nhóm thị trường</label>
                            <select name="market_group_id" class="form-select rounded-lg text-xs w-full">
                                <option value="">-- Chọn nhóm --</option>
                                @foreach($marketGroups as $mg)
                                    <option value="{{ $mg->id }}" {{ old('market_group_id') == $mg->id ? 'selected' : '' }}>
                                        {{ $mg->name }} ({{ $mg->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Tên khách hàng <span class="text-danger-500">*</span></label>
                            <input type="text" name="name" class="form-control rounded-lg text-xs" placeholder="Tên khách hàng / Xưởng" value="{{ old('name') }}" required>
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control rounded-lg text-xs" placeholder="Nhập số điện thoại" value="{{ old('phone') }}">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Tỉnh / Thành phố</label>
                            <input type="text" name="province" id="create_province" class="form-control rounded-lg text-xs" placeholder="Ví dụ: Hà Nội, Bắc Ninh..." value="{{ old('province') }}">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Quận / Huyện / Xã</label>
                            <input type="text" name="ward" id="create_ward" class="form-control rounded-lg text-xs" placeholder="Ví dụ: Thạch Thất, Hữu Bằng..." value="{{ old('ward') }}">
                        </div>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Địa chỉ chi tiết</label>
                        <input type="text" name="address" id="create_address" class="form-control rounded-lg text-xs" placeholder="Ví dụ: Số 12 Đội 5, Làng nghề..." value="{{ old('address') }}">
                    </div>

                    {{-- Leaflet Map & GPS Location Picker --}}
                    <div class="bg-neutral-50 rounded-xl p-3 border border-neutral-200 space-y-2">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-1.5">
                                <iconify-icon icon="solar:map-point-wave-bold" class="text-primary-600 text-base"></iconify-icon>
                                <span class="font-bold text-xs text-neutral-800">Định vị bản đồ (Leaflet)</span>
                            </div>
                            <button type="button" onclick="getCreateCurrentLocation()" id="btn-create-gps" class="btn btn-xs bg-primary-50 text-primary-700 hover:bg-primary-100 border border-primary-200 rounded-lg px-2.5 py-1 text-[11px] font-semibold flex items-center gap-1">
                                <iconify-icon icon="lucide:crosshair" class="text-xs"></iconify-icon> Lấy vị trí hiện tại
                            </button>
                        </div>
                        
                        <input type="hidden" name="latitude" id="create_latitude" value="{{ old('latitude') }}">
                        <input type="hidden" name="longitude" id="create_longitude" value="{{ old('longitude') }}">

                        <div id="create-customer-map" style="height: 180px; width: 100%; border-radius: 8px;" class="border border-neutral-300"></div>

                        <div class="flex items-center justify-between text-[11px] text-neutral-500 pt-0.5">
                            <span>Toạ độ: <strong id="create_coords_display" class="text-neutral-700 font-mono">Chưa ghim vị trí</strong></span>
                            <span class="text-neutral-400 italic">Click hoặc kéo ghim để chỉnh toạ độ</span>
                        </div>
                    </div>
                </div>

                {{-- Cột 2: Hồ sơ thị trường, đối tác & CSKH --}}
                <div class="lg:col-span-6 space-y-3.5">
                    <div class="border-b border-neutral-200 pb-1.5 flex items-center gap-2">
                        <iconify-icon icon="solar:chart-square-bold-duotone" class="text-primary-600 text-lg"></iconify-icon>
                        <h6 class="font-bold text-xs uppercase tracking-wider text-neutral-700 m-0">Hồ sơ thị trường & CSKH</h6>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Tình trạng hợp tác</label>
                        <select name="status" class="form-select rounded-lg text-xs w-full">
                            <option value="Đang đặt hàng" {{ old('status') == 'Đang đặt hàng' ? 'selected' : '' }}>Đang đặt hàng</option>
                            <option value="Không đặt GERVIN" {{ old('status') == 'Không đặt GERVIN' ? 'selected' : '' }}>Không đặt GERVIN</option>
                            <option value="Khách hàng mới tiềm năng" {{ old('status') == 'Khách hàng mới tiềm năng' ? 'selected' : '' }}>Khách hàng mới tiềm năng</option>
                            <option value="Tạm dừng hợp tác" {{ old('status') == 'Tạm dừng hợp tác' ? 'selected' : '' }}>Tạm dừng hợp tác</option>
                            <option value="Khác" {{ old('status') == 'Khác' ? 'selected' : '' }}>Khác</option>
                        </select>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-0">Đối tác KH đang hợp tác</label>
                            <span class="text-[10px] text-neutral-400">Chọn nhanh hoặc nhập thêm:</span>
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap mb-1.5" id="create-partner-pills">
                            <button type="button" onclick="togglePartnerTag('create-partner-pills', 'create_partner_competitors', 'ALD')" data-partner="ALD" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200 hover:border-primary-400 transition-all">+ ALD</button>
                            <button type="button" onclick="togglePartnerTag('create-partner-pills', 'create_partner_competitors', 'LIVAS')" data-partner="LIVAS" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200 hover:border-primary-400 transition-all">+ LIVAS</button>
                            <button type="button" onclick="togglePartnerTag('create-partner-pills', 'create_partner_competitors', 'GERVIN')" data-partner="GERVIN" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200 hover:border-primary-400 transition-all">+ GERVIN</button>
                            <button type="button" onclick="togglePartnerTag('create-partner-pills', 'create_partner_competitors', 'ĐỖ THÀNH ĐẠT')" data-partner="ĐỖ THÀNH ĐẠT" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200 hover:border-primary-400 transition-all">+ ĐỖ THÀNH ĐẠT</button>
                        </div>
                        <input type="text" name="partner_competitors" id="create_partner_competitors" class="form-control rounded-lg text-xs" placeholder="Ví dụ: ALD, LIVAS, GERVIN..." value="{{ old('partner_competitors') }}">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Quy mô xưởng</label>
                            <input type="text" name="workshop_scale" class="form-control rounded-lg text-xs" placeholder="Ví dụ: 300m2, 5 thợ, 2 máy dán..." value="{{ old('workshop_scale') }}">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Tính cách khách hàng</label>
                            <input type="text" name="personality" class="form-control rounded-lg text-xs" placeholder="Ví dụ: Kỹ tính, cẩn thận đường keo..." value="{{ old('personality') }}">
                        </div>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Phản ánh khách hàng về Gervin</label>
                        <textarea name="feedback" rows="2" class="form-control rounded-lg text-xs" placeholder="Ghi nhận phản hồi về chất lượng nẹp, keo PUR, tiến độ giao hàng...">{{ old('feedback') }}</textarea>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Đề xuất Khách hàng (nếu có)</label>
                        <textarea name="customer_proposal" rows="2" class="form-control rounded-lg text-xs" placeholder="Mong muốn, yêu cầu hoặc đề xuất từ phía khách hàng...">{{ old('customer_proposal') }}</textarea>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Đề xuất Sale (CSKH / kéo khách / mở mới)</label>
                        <textarea name="sale_proposal" rows="2" class="form-control rounded-lg text-xs" placeholder="Kế hoạch chăm sóc, kéo khách quay lại hoặc mở mới...">{{ old('sale_proposal') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Công nợ ban đầu (₫)</label>
                            <input type="number" name="initial_debt" min="0" class="form-control rounded-lg text-xs" placeholder="Ví dụ: 10000000" value="{{ old('initial_debt') }}">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Định mức công nợ (₫)</label>
                            <input type="number" name="debt_limit" min="0" class="form-control rounded-lg text-xs" placeholder="Ví dụ: 50000000" value="{{ old('debt_limit') }}">
                        </div>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Chính sách khách hàng</label>
                        <input type="text" name="policy" class="form-control rounded-lg text-xs" placeholder="Chính sách giá, chiết khấu, điều khoản thanh toán..." value="{{ old('policy') }}">
                    </div>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex justify-end gap-3 shrink-0 bg-white">
            <button type="button" onclick="closeModal('create-customer-modal')" class="btn btn-neutral px-5 py-2 text-xs rounded-lg font-semibold">Hủy</button>
            <button type="submit" class="btn btn-primary px-6 py-2 text-xs rounded-lg font-semibold flex items-center gap-1.5">
                <iconify-icon icon="lucide:check" class="text-sm"></iconify-icon> Lưu khách hàng
            </button>
        </div>
    </form>
</x-modal>
@endcan

{{-- Modal Sửa khách hàng --}}
@can('edit customer')
<x-modal name="edit-customer-modal" maxWidth="5xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between shrink-0 bg-white">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                <iconify-icon icon="solar:pen-new-square-bold-duotone"></iconify-icon>
            </div>
            <div>
                <h5 class="font-bold text-base text-neutral-800 m-0">Chỉnh sửa khách hàng</h5>
                <p class="text-[11px] text-neutral-400 m-0">Cập nhật thông tin chi tiết, bản đồ toạ độ và hồ sơ thị trường</p>
            </div>
        </div>
        <button type="button" onclick="closeModal('edit-customer-modal')" class="text-neutral-400 hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form id="edit-customer-form" action="" method="POST" class="flex flex-col overflow-hidden max-h-[calc(95vh-65px)]">
        @csrf @method('PUT')
        <div class="p-6 overflow-y-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Cột 1: Thông tin cơ bản, Địa chỉ & Bản đồ Leaflet --}}
                <div class="lg:col-span-6 space-y-3.5">
                    <div class="border-b border-neutral-200 pb-1.5 flex items-center gap-2">
                        <iconify-icon icon="solar:user-id-bold-duotone" class="text-primary-600 text-lg"></iconify-icon>
                        <h6 class="font-bold text-xs uppercase tracking-wider text-neutral-700 m-0">Thông tin cơ bản & Vị trí</h6>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Mã khách hàng</label>
                            <input type="text" id="edit_customer_code" name="customer_code" class="form-control rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Nhóm thị trường</label>
                            <select id="edit_market_group_id" name="market_group_id" class="form-select rounded-lg text-xs w-full">
                                <option value="">-- Chưa chọn nhóm --</option>
                                @foreach($marketGroups as $mg)
                                    <option value="{{ $mg->id }}">
                                        {{ $mg->name }} ({{ $mg->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Tên khách hàng <span class="text-danger-500">*</span></label>
                            <input type="text" id="edit_name" name="name" class="form-control rounded-lg text-xs" placeholder="Nhập tên khách hàng" required>
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Số điện thoại</label>
                            <input type="text" id="edit_phone" name="phone" class="form-control rounded-lg text-xs" placeholder="Nhập số điện thoại">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Tỉnh / Thành phố</label>
                            <input type="text" id="edit_province" name="province" class="form-control rounded-lg text-xs" placeholder="Ví dụ: Hà Nội, Bắc Ninh...">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Quận / Huyện / Xã</label>
                            <input type="text" id="edit_ward" name="ward" class="form-control rounded-lg text-xs" placeholder="Ví dụ: Thạch Thất, Hữu Bằng...">
                        </div>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Địa chỉ chi tiết</label>
                        <input type="text" id="edit_address" name="address" class="form-control rounded-lg text-xs" placeholder="Nhập địa chỉ chi tiết">
                    </div>

                    {{-- Leaflet Map & GPS Location Picker --}}
                    <div class="bg-neutral-50 rounded-xl p-3 border border-neutral-200 space-y-2">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-1.5">
                                <iconify-icon icon="solar:map-point-wave-bold" class="text-primary-600 text-base"></iconify-icon>
                                <span class="font-bold text-xs text-neutral-800">Định vị bản đồ (Leaflet)</span>
                            </div>
                            <button type="button" onclick="getEditCurrentLocation()" id="btn-edit-gps" class="btn btn-xs bg-primary-50 text-primary-700 hover:bg-primary-100 border border-primary-200 rounded-lg px-2.5 py-1 text-[11px] font-semibold flex items-center gap-1">
                                <iconify-icon icon="lucide:crosshair" class="text-xs"></iconify-icon> Lấy vị trí hiện tại
                            </button>
                        </div>
                        
                        <input type="hidden" name="latitude" id="edit_latitude">
                        <input type="hidden" name="longitude" id="edit_longitude">

                        <div id="edit-customer-map" style="height: 180px; width: 100%; border-radius: 8px;" class="border border-neutral-300"></div>

                        <div class="flex items-center justify-between text-[11px] text-neutral-500 pt-0.5">
                            <span>Toạ độ: <strong id="edit_coords_display" class="text-neutral-700 font-mono">Chưa ghim vị trí</strong></span>
                            <span class="text-neutral-400 italic">Click hoặc kéo ghim để chỉnh toạ độ</span>
                        </div>
                    </div>
                </div>

                {{-- Cột 2: Hồ sơ thị trường, đối tác & CSKH --}}
                <div class="lg:col-span-6 space-y-3.5">
                    <div class="border-b border-neutral-200 pb-1.5 flex items-center gap-2">
                        <iconify-icon icon="solar:chart-square-bold-duotone" class="text-primary-600 text-lg"></iconify-icon>
                        <h6 class="font-bold text-xs uppercase tracking-wider text-neutral-700 m-0">Hồ sơ thị trường & CSKH</h6>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Tình trạng hợp tác</label>
                        <select id="edit_status" name="status" class="form-select rounded-lg text-xs w-full">
                            <option value="Đang đặt hàng">Đang đặt hàng</option>
                            <option value="Không đặt GERVIN">Không đặt GERVIN</option>
                            <option value="Khách hàng mới tiềm năng">Khách hàng mới tiềm năng</option>
                            <option value="Tạm dừng hợp tác">Tạm dừng hợp tác</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-0">Đối tác KH đang hợp tác</label>
                            <span class="text-[10px] text-neutral-400">Chọn nhanh hoặc nhập thêm:</span>
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap mb-1.5" id="edit-partner-pills">
                            <button type="button" onclick="togglePartnerTag('edit-partner-pills', 'edit_partner_competitors', 'ALD')" data-partner="ALD" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200 hover:border-primary-400 transition-all">+ ALD</button>
                            <button type="button" onclick="togglePartnerTag('edit-partner-pills', 'edit_partner_competitors', 'LIVAS')" data-partner="LIVAS" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200 hover:border-primary-400 transition-all">+ LIVAS</button>
                            <button type="button" onclick="togglePartnerTag('edit-partner-pills', 'edit_partner_competitors', 'GERVIN')" data-partner="GERVIN" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200 hover:border-primary-400 transition-all">+ GERVIN</button>
                            <button type="button" onclick="togglePartnerTag('edit-partner-pills', 'edit_partner_competitors', 'ĐỖ THÀNH ĐẠT')" data-partner="ĐỖ THÀNH ĐẠT" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200 hover:border-primary-400 transition-all">+ ĐỖ THÀNH ĐẠT</button>
                        </div>
                        <input type="text" id="edit_partner_competitors" name="partner_competitors" class="form-control rounded-lg text-xs" placeholder="Ví dụ: ALD, LIVAS, GERVIN...">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Quy mô xưởng</label>
                            <input type="text" id="edit_workshop_scale" name="workshop_scale" class="form-control rounded-lg text-xs" placeholder="Ví dụ: 300m2, 5 thợ, máy dán...">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Tính cách khách hàng</label>
                            <input type="text" id="edit_personality" name="personality" class="form-control rounded-lg text-xs" placeholder="Ví dụ: Kỹ tính, cẩn thận đường keo...">
                        </div>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Phản ánh khách hàng về Gervin</label>
                        <textarea id="edit_feedback" name="feedback" rows="2" class="form-control rounded-lg text-xs" placeholder="Ghi nhận phản hồi về chất lượng nẹp, keo PUR, tiến độ giao hàng..."></textarea>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Đề xuất Khách hàng (nếu có)</label>
                        <textarea id="edit_customer_proposal" name="customer_proposal" rows="2" class="form-control rounded-lg text-xs" placeholder="Mong muốn, yêu cầu hoặc đề xuất từ phía khách hàng..."></textarea>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Đề xuất Sale (CSKH / kéo khách / mở mới)</label>
                        <textarea id="edit_sale_proposal" name="sale_proposal" rows="2" class="form-control rounded-lg text-xs" placeholder="Kế hoạch chăm sóc, kéo khách quay lại hoặc mở mới..."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Công nợ ban đầu (₫)</label>
                            <input type="number" id="edit_initial_debt" name="initial_debt" min="0" class="form-control rounded-lg text-xs" placeholder="Ví dụ: 10000000">
                        </div>
                        <div>
                            <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Định mức công nợ (₫)</label>
                            <input type="number" id="edit_debt_limit" name="debt_limit" min="0" class="form-control rounded-lg text-xs" placeholder="Ví dụ: 50000000">
                        </div>
                    </div>

                    <div>
                        <label class="form-label font-semibold text-xs text-neutral-600 mb-1">Chính sách khách hàng</label>
                        <input type="text" id="edit_policy" name="policy" class="form-control rounded-lg text-xs" placeholder="Chính sách giá, chiết khấu, điều khoản thanh toán...">
                    </div>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex justify-end gap-3 shrink-0 bg-white">
            <button type="button" onclick="closeModal('edit-customer-modal')" class="btn btn-neutral px-5 py-2 text-xs rounded-lg font-semibold">Hủy</button>
            <button type="submit" class="btn btn-primary px-6 py-2 text-xs rounded-lg font-semibold flex items-center gap-1.5">
                <iconify-icon icon="lucide:check" class="text-sm"></iconify-icon> Cập nhật khách hàng
            </button>
        </div>
    </form>
</x-modal>

<script>
let createMap = null;
let createMarker = null;
let editMap = null;
let editMarker = null;

function setCreateCoords(lat, lng) {
    const latFixed = Number(lat).toFixed(7);
    const lngFixed = Number(lng).toFixed(7);
    document.getElementById('create_latitude').value = latFixed;
    document.getElementById('create_longitude').value = lngFixed;
    const textEl = document.getElementById('create_coords_display');
    if (textEl) {
        textEl.textContent = `${latFixed}, ${lngFixed}`;
    }
}

function initCreateMap(defaultLat, defaultLng) {
    const lat = Number(defaultLat) || 20.9800;
    const lng = Number(defaultLng) || 105.7800;
    const hasExistingCoords = !!(defaultLat && defaultLng);

    setTimeout(() => {
        if (!createMap) {
            createMap = L.map('create-customer-map').setView([lat, lng], 14);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(createMap);

            createMarker = L.marker([lat, lng], { draggable: true }).addTo(createMap);

            createMap.on('click', function(e) {
                createMarker.setLatLng(e.latlng);
                setCreateCoords(e.latlng.lat, e.latlng.lng);
            });

            createMarker.on('dragend', function(e) {
                const pos = createMarker.getLatLng();
                setCreateCoords(pos.lat, pos.lng);
            });
        } else {
            createMap.setView([lat, lng], 14);
            createMarker.setLatLng([lat, lng]);
            createMap.invalidateSize();
        }

        if (hasExistingCoords) {
            setCreateCoords(lat, lng);
        } else {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    const myLat = pos.coords.latitude;
                    const myLng = pos.coords.longitude;
                    if (createMap) {
                        createMap.setView([myLat, myLng], 15);
                        createMarker.setLatLng([myLat, myLng]);
                    }
                    setCreateCoords(myLat, myLng);
                }, function() {}, { enableHighAccuracy: true, timeout: 5000 });
            }
        }
    }, 200);
}

function getCreateCurrentLocation() {
    if (!navigator.geolocation) {
        alert('Trình duyệt không hỗ trợ Geolocation.');
        return;
    }
    const btn = document.getElementById('btn-create-gps');
    if (btn) btn.innerHTML = '<iconify-icon icon="lucide:loader-2" class="animate-spin"></iconify-icon> Đang tìm...';
    navigator.geolocation.getCurrentPosition(function(pos) {
        if (btn) btn.innerHTML = '<iconify-icon icon="lucide:crosshair"></iconify-icon> Lấy vị trí hiện tại';
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        if (createMap) {
            createMap.setView([lat, lng], 16);
            createMarker.setLatLng([lat, lng]);
        }
        setCreateCoords(lat, lng);
    }, function(err) {
        if (btn) btn.innerHTML = '<iconify-icon icon="lucide:crosshair"></iconify-icon> Lấy vị trí hiện tại';
        alert('Không thể xác định vị trí: ' + err.message);
    }, { enableHighAccuracy: true, timeout: 8000 });
}

function setEditCoords(lat, lng) {
    const latFixed = Number(lat).toFixed(7);
    const lngFixed = Number(lng).toFixed(7);
    document.getElementById('edit_latitude').value = latFixed;
    document.getElementById('edit_longitude').value = lngFixed;
    const textEl = document.getElementById('edit_coords_display');
    if (textEl) {
        textEl.textContent = `${latFixed}, ${lngFixed}`;
    }
}

function initEditMap(defaultLat, defaultLng) {
    const lat = Number(defaultLat) || 20.9800;
    const lng = Number(defaultLng) || 105.7800;
    const hasExistingCoords = !!(defaultLat && defaultLng);

    setTimeout(() => {
        if (!editMap) {
            editMap = L.map('edit-customer-map').setView([lat, lng], 14);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(editMap);

            editMarker = L.marker([lat, lng], { draggable: true }).addTo(editMap);

            editMap.on('click', function(e) {
                editMarker.setLatLng(e.latlng);
                setEditCoords(e.latlng.lat, e.latlng.lng);
            });

            editMarker.on('dragend', function(e) {
                const pos = editMarker.getLatLng();
                setEditCoords(pos.lat, pos.lng);
            });
        } else {
            editMap.setView([lat, lng], 14);
            editMarker.setLatLng([lat, lng]);
            editMap.invalidateSize();
        }

        if (hasExistingCoords) {
            setEditCoords(lat, lng);
        } else {
            document.getElementById('edit_latitude').value = '';
            document.getElementById('edit_longitude').value = '';
            const textEl = document.getElementById('edit_coords_display');
            if (textEl) textEl.textContent = 'Chưa ghim vị trí';
        }
    }, 200);
}

function getEditCurrentLocation() {
    if (!navigator.geolocation) {
        alert('Trình duyệt không hỗ trợ Geolocation.');
        return;
    }
    const btn = document.getElementById('btn-edit-gps');
    if (btn) btn.innerHTML = '<iconify-icon icon="lucide:loader-2" class="animate-spin"></iconify-icon> Đang tìm...';
    navigator.geolocation.getCurrentPosition(function(pos) {
        if (btn) btn.innerHTML = '<iconify-icon icon="lucide:crosshair"></iconify-icon> Lấy vị trí hiện tại';
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        if (editMap) {
            editMap.setView([lat, lng], 16);
            editMarker.setLatLng([lat, lng]);
        }
        setEditCoords(lat, lng);
    }, function(err) {
        if (btn) btn.innerHTML = '<iconify-icon icon="lucide:crosshair"></iconify-icon> Lấy vị trí hiện tại';
        alert('Không thể xác định vị trí: ' + err.message);
    }, { enableHighAccuracy: true, timeout: 8000 });
}

function togglePartnerTag(containerId, inputId, partnerName) {
    const input = document.getElementById(inputId);
    if (!input) return;
    let list = input.value.split(',').map(s => s.trim()).filter(Boolean);
    const idx = list.indexOf(partnerName);
    if (idx > -1) {
        list.splice(idx, 1);
    } else {
        list.push(partnerName);
    }
    input.value = list.join(', ');
    updatePartnerTagButtons(containerId, inputId);
}

function updatePartnerTagButtons(containerId, inputId) {
    const input = document.getElementById(inputId);
    const container = document.getElementById(containerId);
    if (!input || !container) return;
    const list = input.value.split(',').map(s => s.trim().toUpperCase()).filter(Boolean);
    container.querySelectorAll('[data-partner]').forEach(btn => {
        const p = btn.getAttribute('data-partner').toUpperCase();
        if (list.includes(p)) {
            btn.classList.add('bg-primary-600', 'text-white', 'border-primary-600');
            btn.classList.remove('bg-neutral-100', 'text-neutral-700', 'border-neutral-200');
        } else {
            btn.classList.remove('bg-primary-600', 'text-white', 'border-primary-600');
            btn.classList.add('bg-neutral-100', 'text-neutral-700', 'border-neutral-200');
        }
    });
}

function openCreateCustomerModal() {
    updatePartnerTagButtons('create-partner-pills', 'create_partner_competitors');
    openModal('create-customer-modal');
    initCreateMap(
        document.getElementById('create_latitude').value,
        document.getElementById('create_longitude').value
    );
}

function openEditCustomerModalFromBtn(btn) {
    const id = btn.getAttribute('data-id');
    const code = btn.getAttribute('data-code') || '';
    const name = btn.getAttribute('data-name') || '';
    const phone = btn.getAttribute('data-phone') || '';
    const province = btn.getAttribute('data-province') || '';
    const ward = btn.getAttribute('data-ward') || '';
    const address = btn.getAttribute('data-address') || '';
    const lat = btn.getAttribute('data-latitude') || '';
    const lng = btn.getAttribute('data-longitude') || '';
    const status = btn.getAttribute('data-status') || 'Đang đặt hàng';
    const partner = btn.getAttribute('data-partner-competitors') || '';
    const scale = btn.getAttribute('data-workshop-scale') || '';
    const personality = btn.getAttribute('data-personality') || '';
    const feedback = btn.getAttribute('data-feedback') || '';
    const custProposal = btn.getAttribute('data-customer-proposal') || '';
    const saleProposal = btn.getAttribute('data-sale-proposal') || '';
    const debt = btn.getAttribute('data-debt') || 0;
    const debtLimit = btn.getAttribute('data-debt-limit') || 0;
    const policy = btn.getAttribute('data-policy') || '';
    const marketGroupId = btn.getAttribute('data-market-group-id') || '';

    document.getElementById('edit-customer-form').action = '/customers/' + id;
    document.getElementById('edit_customer_code').value = code;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_province').value = province;
    document.getElementById('edit_ward').value = ward;
    document.getElementById('edit_address').value = address;
    document.getElementById('edit_latitude').value = lat;
    document.getElementById('edit_longitude').value = lng;
    document.getElementById('edit_status').value = status;
    document.getElementById('edit_partner_competitors').value = partner;
    document.getElementById('edit_workshop_scale').value = scale;
    document.getElementById('edit_personality').value = personality;
    document.getElementById('edit_feedback').value = feedback;
    document.getElementById('edit_customer_proposal').value = custProposal;
    document.getElementById('edit_sale_proposal').value = saleProposal;
    document.getElementById('edit_initial_debt').value = debt;
    document.getElementById('edit_debt_limit').value = debtLimit;
    document.getElementById('edit_policy').value = policy;
    
    const mgSelect = document.getElementById('edit_market_group_id');
    if (mgSelect) {
        mgSelect.value = marketGroupId;
    }

    updatePartnerTagButtons('edit-partner-pills', 'edit_partner_competitors');

    openModal('edit-customer-modal');
    initEditMap(lat, lng);
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
            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Chọn nhanh khách hàng</label>
                <select name="filter_customer_id" id="filter_customer_id" class="rounded-lg w-full">
                    <option value="">Tất cả</option>
                    @foreach($filterCustomers as $fc)
                        <option value="{{ $fc->id }}" {{ request('filter_customer_id') == $fc->id ? 'selected' : '' }}>
                            {{ $fc->customer_code }} - {{ $fc->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group md:col-span-2">
                <label class="form-label font-semibold text-sm text-neutral-600">Nhóm thị trường</label>
                <select name="filter_market_group_id" class="form-select rounded-lg w-full">
                    <option value="">Tất cả nhóm thị trường</option>
                    @foreach($marketGroups as $mg)
                        <option value="{{ $mg->id }}" {{ request('filter_market_group_id') == $mg->id ? 'selected' : '' }}>
                            {{ $mg->name }} ({{ $mg->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Tên khách hàng</label>
                <input type="text" name="filter_name" class="form-control rounded-lg" placeholder="Nhập tên khách hàng..." value="{{ request('filter_name') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Mã khách hàng</label>
                <input type="text" name="filter_customer_code" class="form-control rounded-lg" placeholder="Nhập mã khách hàng..." value="{{ request('filter_customer_code') }}">
            </div>
            
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Số điện thoại</label>
                <input type="text" name="filter_phone" class="form-control rounded-lg" placeholder="Nhập số điện thoại..." value="{{ request('filter_phone') }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Mức công nợ</label>
                <select name="filter_debt_level" class="form-select rounded-lg">
                    <option value="">Tất cả mức công nợ</option>
                    <option value="0-10" {{ request('filter_debt_level') === '0-10' ? 'selected' : '' }}>0 - 10 triệu</option>
                    <option value="10-30" {{ request('filter_debt_level') === '10-30' ? 'selected' : '' }}>10 - 30 triệu</option>
                    <option value="30-50" {{ request('filter_debt_level') === '30-50' ? 'selected' : '' }}>30 - 50 triệu</option>
                    <option value="50-100" {{ request('filter_debt_level') === '50-100' ? 'selected' : '' }}>50 - 100 triệu</option>
                    <option value="100-150" {{ request('filter_debt_level') === '100-150' ? 'selected' : '' }}>100 - 150 triệu</option>
                    <option value="150+" {{ request('filter_debt_level') === '150+' ? 'selected' : '' }}>Trên 150 triệu</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Từ ngày</label>
                <input type="date" name="filter_start_date" class="form-control rounded-lg" value="{{ $startDate }}">
            </div>
            <div class="form-group">
                <label class="form-label font-semibold text-sm text-neutral-600">Đến ngày</label>
                <input type="date" name="filter_end_date" class="form-control rounded-lg" value="{{ $endDate }}">
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
        {{-- Customer Market Profile & Details --}}
        <div id="ov-customer-profile" class="mb-5 bg-neutral-50 rounded-xl p-4 border border-neutral-200">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-neutral-200 flex-wrap gap-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <span id="ov-status-badge" class="px-2.5 py-1 rounded text-xs font-bold border"></span>
                    <span id="ov-market-group-badge" class="px-2.5 py-1 rounded text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-200 flex items-center gap-1"></span>
                </div>
                <div id="ov-map-link" class="text-xs"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div>
                    <span class="text-neutral-400 block mb-0.5">Số điện thoại:</span>
                    <span id="ov-phone" class="font-medium text-neutral-800"></span>
                </div>
                <div class="lg:col-span-2">
                    <span class="text-neutral-400 block mb-0.5">Địa chỉ:</span>
                    <span id="ov-address" class="font-medium text-neutral-800"></span>
                </div>
                <div>
                    <span class="text-neutral-400 block mb-0.5">Đối tác đang hợp tác:</span>
                    <span id="ov-partner" class="font-semibold text-neutral-800"></span>
                </div>
                <div>
                    <span class="text-neutral-400 block mb-0.5">Quy mô xưởng:</span>
                    <span id="ov-workshop" class="text-neutral-800"></span>
                </div>
                <div>
                    <span class="text-neutral-400 block mb-0.5">Tính cách khách hàng:</span>
                    <span id="ov-personality" class="text-neutral-800"></span>
                </div>
                <div>
                    <span class="text-neutral-400 block mb-0.5">Định mức công nợ:</span>
                    <span id="ov-debt-limit" class="font-semibold text-neutral-800"></span>
                </div>
                <div>
                    <span class="text-neutral-400 block mb-0.5">Chính sách:</span>
                    <span id="ov-policy" class="text-neutral-800 truncate block"></span>
                </div>
            </div>

            <div id="ov-proposals-grid" class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3 pt-3 border-t border-neutral-200 text-xs">
                <div class="bg-white p-3 rounded-lg border border-neutral-200">
                    <div class="flex items-center gap-1.5 text-primary-700 font-bold mb-1">
                        <iconify-icon icon="solar:chat-round-line-bold" class="text-sm"></iconify-icon> Phản ánh về Gervin:
                    </div>
                    <p id="ov-feedback" class="text-neutral-700 m-0 whitespace-pre-line text-[11px]"></p>
                </div>
                <div class="bg-white p-3 rounded-lg border border-neutral-200">
                    <div class="flex items-center gap-1.5 text-emerald-700 font-bold mb-1">
                        <iconify-icon icon="solar:lightbulb-bold" class="text-sm"></iconify-icon> Đề xuất của KH:
                    </div>
                    <p id="ov-cust-proposal" class="text-neutral-700 m-0 whitespace-pre-line text-[11px]"></p>
                </div>
                <div class="bg-white p-3 rounded-lg border border-neutral-200">
                    <div class="flex items-center gap-1.5 text-amber-700 font-bold mb-1">
                        <iconify-icon icon="solar:user-speak-rounded-bold" class="text-sm"></iconify-icon> Đề xuất Sale:
                    </div>
                    <p id="ov-sale-proposal" class="text-neutral-700 m-0 whitespace-pre-line text-[11px]"></p>
                </div>
            </div>
        </div>

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
                                <th style="padding:8px 10px; text-align:left; color:#4a5568; font-weight:600; border-bottom:1px solid #cbd5e0;">Tên công trình</th>
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
                <div style="display:none; background:#f8fafc; border-radius:12px; padding:12px; border:1px solid #f1f5f9; flex-direction:column; gap:10px; max-height:300px; overflow-y:auto;" id="ov-payments-list"></div>
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

    document.getElementById('ov-name').textContent = currentOverviewName || 'Đang tải...';
    document.getElementById('ov-code').textContent = currentOverviewCode || '...';

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
            if (data.customer) {
                document.getElementById('ov-name').textContent = data.customer.name || '—';
                document.getElementById('ov-code').textContent = data.customer.customer_code || '—';
            }
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
    const cust = data.customer || {};

    // Customer status & market group
    const statusEl = document.getElementById('ov-status-badge');
    if (statusEl) {
        const st = cust.status || 'Đang đặt hàng';
        statusEl.textContent = st;
        let stClass = 'bg-neutral-100 text-neutral-600 border-neutral-200';
        if (st === 'Đang đặt hàng') stClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
        else if (st === 'Không đặt GERVIN') stClass = 'bg-rose-50 text-rose-700 border-rose-200';
        else if (st === 'Khách hàng mới tiềm năng') stClass = 'bg-blue-50 text-blue-700 border-blue-200';
        else if (st === 'Tạm dừng hợp tác') stClass = 'bg-amber-50 text-amber-700 border-amber-200';
        statusEl.className = `px-2.5 py-1 rounded text-xs font-bold border ${stClass}`;
    }

    const mgEl = document.getElementById('ov-market-group-badge');
    if (mgEl) {
        if (cust.market_group && cust.market_group.name) {
            mgEl.innerHTML = `<iconify-icon icon="solar:users-group-two-rounded-bold"></iconify-icon> ${cust.market_group.name}`;
            mgEl.style.display = 'inline-flex';
        } else {
            mgEl.style.display = 'none';
        }
    }

    // Google Maps link
    const mapLinkEl = document.getElementById('ov-map-link');
    if (mapLinkEl) {
        if (cust.latitude && cust.longitude) {
            mapLinkEl.innerHTML = `<a href="https://www.google.com/maps?q=${cust.latitude},${cust.longitude}" target="_blank" class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-800 font-semibold bg-white px-2.5 py-1 rounded-md border border-neutral-200 shadow-sm"><iconify-icon icon="solar:map-point-wave-bold" class="text-red-500 text-sm"></iconify-icon> Xem vị trí Google Maps (${Number(cust.latitude).toFixed(4)}, ${Number(cust.longitude).toFixed(4)})</a>`;
        } else {
            mapLinkEl.innerHTML = '<span class="text-neutral-400 italic">Chưa có toạ độ GPS</span>';
        }
    }

    // Basic & Market fields
    const fullAddr = [cust.address, cust.ward, cust.province].filter(Boolean).join(', ');
    document.getElementById('ov-phone').textContent = cust.phone || '—';
    document.getElementById('ov-address').textContent = fullAddr || (cust.address || '—');
    document.getElementById('ov-partner').textContent = cust.partner_competitors || '—';
    document.getElementById('ov-workshop').textContent = cust.workshop_scale || '—';
    document.getElementById('ov-personality').textContent = cust.personality || '—';
    document.getElementById('ov-debt-limit').textContent = cust.debt_limit ? ovFmt(cust.debt_limit) + '₫' : '—';
    document.getElementById('ov-policy').textContent = cust.policy || '—';

    // Proposals & Feedback
    document.getElementById('ov-feedback').textContent = cust.feedback || 'Chưa có ghi nhận phản ánh';
    document.getElementById('ov-cust-proposal').textContent = cust.customer_proposal || 'Chưa có đề xuất từ khách hàng';
    document.getElementById('ov-sale-proposal').textContent = cust.sale_proposal || 'Chưa có kế hoạch đề xuất';

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
                <td style="padding:9px 10px; font-weight:500; color:#334155;">${o.customer_name || '—'}</td>
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
        pList.style.display = 'none';
        noPayments.style.display = 'block';
    } else {
        noPayments.style.display = 'none';
        pList.style.display = 'flex';
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

function toggleCustomerStaff(customerId) {
    const moreDiv = document.getElementById('customer-staff-more-' + customerId);
    const expandBtn = document.getElementById('staff-expand-btn-' + customerId);
    if (!moreDiv) return;
    
    const isHidden = moreDiv.classList.contains('hidden');
    if (isHidden) {
        moreDiv.classList.remove('hidden');
        if (expandBtn) expandBtn.classList.add('hidden');
    } else {
        moreDiv.classList.add('hidden');
        if (expandBtn) expandBtn.classList.remove('hidden');
    }
}

// Auto open modal on redirect with overview_id param
document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const overviewId = urlParams.get('overview_id');
    if (overviewId) {
        const btn = document.querySelector(`button[data-customer-id="${overviewId}"]`);
        if (btn) {
            btn.click();
        }
    }

    if (typeof TomSelect !== 'undefined' && document.getElementById('filter_customer_id')) {
        new TomSelect('#filter_customer_id', {
            allowEmptyOption: true,
            placeholder: '-- Chọn khách hàng --',
        });
    }
});
</script>

<style>
.ts-dropdown {
    z-index: 999999 !important;
    max-height: 200px !important;
    overflow-y: auto !important;
    border-radius: 0.5rem !important;
}
#edit-customer-modal [data-modal-content],
#create-customer-modal [data-modal-content] {
    max-height: 95vh !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
}
</style>

@endsection
