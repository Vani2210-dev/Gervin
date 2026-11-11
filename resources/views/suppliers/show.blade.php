@extends('layout.layout')
@php
    $title    = 'Chi tiết nhà cung cấp';
    $subTitle = '[' . $supplier->code . '] ' . $supplier->name;

    $statusColor = match($supplier->status) {
        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'inactive' => 'bg-rose-50 text-rose-700 border-rose-200',
        default => 'bg-neutral-100 text-neutral-600 border-neutral-200'
    };
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
    @if(session('warning'))
    <div class="alert alert-warning bg-amber-50 text-amber-800 border border-amber-200 rounded-xl p-4 flex items-center justify-between shadow-2xs">
        <div class="flex items-center gap-2">
            <iconify-icon icon="solar:danger-triangle-bold" class="text-xl text-amber-600"></iconify-icon>
            <span class="text-sm font-medium">{{ session('warning') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-amber-500 hover:text-amber-800 text-lg leading-none">&times;</button>
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

    {{-- Top Action / Header Profile Card --}}
    <div class="bg-white rounded-2xl p-6 border border-neutral-200/80 shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <a href="{{ route('suppliers.index') }}"
                   class="w-10 h-10 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-600 flex items-center justify-center transition-colors shrink-0 mt-0.5"
                   title="Quay lại danh sách nhà cung cấp">
                    <iconify-icon icon="lucide:arrow-left" class="text-xl"></iconify-icon>
                </a>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl md:text-2xl font-bold text-neutral-800 m-0">{{ $supplier->name }}</h1>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-neutral-100 text-neutral-700 border border-neutral-200 font-mono">
                            {{ $supplier->code }}
                        </span>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold border {{ $statusColor }}">
                            {{ $supplier->status === 'active' ? 'Đang hoạt động' : 'Ngưng hoạt động' }}
                        </span>
                        @if($supplier->tax_code)
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 font-mono">
                                MST: {{ $supplier->tax_code }}
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-4 mt-2 text-xs text-neutral-500 flex-wrap">
                        @if($supplier->contact_person)
                            <span class="flex items-center gap-1 text-neutral-700 font-medium">
                                <iconify-icon icon="solar:user-bold" class="text-primary-600"></iconify-icon>
                                {{ $supplier->contact_person }}
                            </span>
                        @endif
                        @if($supplier->phone)
                            <a href="tel:{{ $supplier->phone }}" class="flex items-center gap-1 text-neutral-700 hover:text-primary-600 font-medium">
                                <iconify-icon icon="solar:phone-bold" class="text-primary-600"></iconify-icon>
                                {{ $supplier->phone }}
                            </a>
                        @endif
                        @if($supplier->email)
                            <a href="mailto:{{ $supplier->email }}" class="flex items-center gap-1 text-neutral-700 hover:text-primary-600">
                                <iconify-icon icon="solar:letter-bold" class="text-indigo-600"></iconify-icon>
                                {{ $supplier->email }}
                            </a>
                        @endif
                        @if($supplier->address)
                            <span class="flex items-center gap-1 max-w-md truncate" title="{{ $supplier->address }}">
                                <iconify-icon icon="solar:map-point-bold" class="text-rose-500 shrink-0"></iconify-icon>
                                {{ $supplier->address }}
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
                    Thêm phiếu chi
                </button>
                <a href="{{ route('inventory.receipts.create') }}?supplier_id={{ $supplier->id }}"
                    class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-all">
                    <iconify-icon icon="lucide:package-plus" class="text-base"></iconify-icon>
                    Tạo phiếu nhập
                </a>
                <button type="button" onclick="openEditSupplierModalFromBtn(this)"
                    data-id="{{ $supplier->id }}"
                    data-code="{{ $supplier->code }}"
                    data-name="{{ $supplier->name }}"
                    data-phone="{{ $supplier->phone ?? '' }}"
                    data-email="{{ $supplier->email ?? '' }}"
                    data-tax_code="{{ $supplier->tax_code ?? '' }}"
                    data-contact_person="{{ $supplier->contact_person ?? '' }}"
                    data-address="{{ $supplier->address ?? '' }}"
                    data-initial_debt="{{ $supplier->initial_debt ?? 0 }}"
                    data-status="{{ $supplier->status ?? 'active' }}"
                    data-notes="{{ $supplier->notes ?? '' }}"
                    class="btn bg-white hover:bg-neutral-50 text-neutral-700 border border-neutral-300 px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all">
                    <iconify-icon icon="lucide:edit" class="text-sm text-neutral-600"></iconify-icon>
                    Sửa thông tin
                </button>
            </div>
        </div>
    </div>

    {{-- Stats Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Tổng tiền nhập hàng -->
        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-neutral-500">Tổng tiền nhập hàng</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                    <iconify-icon icon="lucide:shopping-bag"></iconify-icon>
                </div>
            </div>
            <div class="text-2xl font-bold text-neutral-800">{{ number_format($totalPurchases, 0, ',', '.') }}₫</div>
            <div class="text-[11px] text-neutral-400 mt-1">{{ $totalReceiptsCount }} phiếu nhập hợp lệ</div>
        </div>

        <!-- 2. Đã thanh toán -->
        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-neutral-500">Đã thanh toán (Phiếu chi)</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <iconify-icon icon="lucide:check-circle"></iconify-icon>
                </div>
            </div>
            <div class="text-2xl font-bold text-emerald-600">{{ number_format($totalPaid, 0, ',', '.') }}₫</div>
            <div class="text-[11px] text-neutral-400 mt-1">{{ $totalPaymentsCount }} đợt chi trả</div>
        </div>

        <!-- 3. Nợ đầu kỳ -->
        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-neutral-500">Nợ ban đầu (Đầu kỳ)</span>
                <div class="w-8 h-8 rounded-lg bg-neutral-100 text-neutral-600 flex items-center justify-center text-lg">
                    <iconify-icon icon="lucide:clock-3"></iconify-icon>
                </div>
            </div>
            <div class="text-2xl font-bold text-neutral-700">{{ number_format((float)($supplier->initial_debt ?? 0), 0, ',', '.') }}₫</div>
            <div class="text-[11px] text-neutral-400 mt-1">Dư nợ chuyển tiếp</div>
        </div>

        <!-- 4. Công nợ hiện tại -->
        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-neutral-700">CÔNG NỢ HIỆN TẠI</span>
                <div class="w-8 h-8 rounded-lg {{ $supplier->debt > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center text-lg">
                    <iconify-icon icon="lucide:alert-circle"></iconify-icon>
                </div>
            </div>
            <div class="text-2xl font-extrabold {{ $supplier->debt > 0 ? 'text-rose-600' : ($supplier->debt < 0 ? 'text-purple-600' : 'text-emerald-600') }}">
                {{ number_format((float)$supplier->debt, 0, ',', '.') }}₫
            </div>
            <div class="text-[11px] mt-1 {{ $supplier->debt > 0 ? 'text-rose-500 font-semibold' : ($supplier->debt < 0 ? 'text-purple-500' : 'text-emerald-500') }}">
                @if($supplier->debt > 0)
                    Ta đang nợ nhà cung cấp
                @elseif($supplier->debt < 0)
                    Ta đã trả thừa cho nhà cung cấp
                @else
                    Đã thanh toán hết công nợ
                @endif
            </div>
        </div>
    </div>

    {{-- Tabs Navigation --}}
    <div class="flex items-center gap-2 border-b border-neutral-200 pb-2">
        <button type="button" onclick="switchSupplierTab('receipts')" id="tab-btn-receipts"
            class="px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition-all {{ $currTab === 'receipts' ? 'bg-primary-600 text-white shadow-xs' : 'bg-white text-neutral-600 hover:bg-neutral-100 border border-neutral-200' }}">
            <iconify-icon icon="lucide:package-check" class="text-base"></iconify-icon>
            <span>Phiếu nhập kho</span>
            <span id="tab-badge-receipts" class="px-2 py-0.5 rounded-full text-xs font-extrabold {{ $currTab === 'receipts' ? 'bg-white/20 text-white' : 'bg-neutral-200 text-neutral-700' }}">
                {{ $totalReceiptsCount }}
            </span>
        </button>

        <button type="button" onclick="switchSupplierTab('payments')" id="tab-btn-payments"
            class="px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition-all {{ $currTab === 'payments' ? 'bg-primary-600 text-white shadow-xs' : 'bg-white text-neutral-600 hover:bg-neutral-100 border border-neutral-200' }}">
            <iconify-icon icon="lucide:receipt" class="text-base"></iconify-icon>
            <span>Lịch sử thanh toán</span>
            <span id="tab-badge-payments" class="px-2 py-0.5 rounded-full text-xs font-extrabold {{ $currTab === 'payments' ? 'bg-white/20 text-white' : 'bg-neutral-200 text-neutral-700' }}">
                {{ $totalPaymentsCount }}
            </span>
        </button>

        <button type="button" onclick="switchSupplierTab('info')" id="tab-btn-info"
            class="px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition-all {{ $currTab === 'info' ? 'bg-primary-600 text-white shadow-xs' : 'bg-white text-neutral-600 hover:bg-neutral-100 border border-neutral-200' }}">
            <iconify-icon icon="lucide:info" class="text-base"></iconify-icon>
            <span>Thông tin chi tiết</span>
        </button>
    </div>

    {{-- TAB 1: PHIẾU NHẬP KHO --}}
    <div id="tab-pane-receipts" class="space-y-6 {{ $currTab === 'receipts' ? '' : 'hidden' }}">
        <div class="card h-full p-0 rounded-xl border-0 overflow-hidden shadow-xs">
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                <div class="flex items-center flex-wrap gap-3">
                    <form method="GET" action="{{ route('suppliers.show', $supplier) }}" class="navbar-search">
                        <input type="hidden" name="tab" value="receipts">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        @if($dateMode && $dateMode !== 'all')
                            <input type="hidden" name="date_mode" value="{{ $dateMode }}">
                            <input type="hidden" name="date_val" value="{{ $dateVal }}">
                        @endif
                        <div class="relative">
                            <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-base"></iconify-icon>
                            <input type="text" name="search" class="form-control form-control-sm border-neutral-200 rounded-lg pl-9 pr-3 w-64 md:w-80" placeholder="Tìm kiếm mã phiếu, người giao, nhận..." value="{{ $search }}">
                        </div>
                    </form>

                    <x-date-filter :action="route('suppliers.show', $supplier)" />
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('suppliers.receipts.export', array_merge(['supplier' => $supplier->id], request()->query())) }}"
                        class="btn bg-emerald-600 hover:bg-emerald-700 text-white text-sm btn-sm px-3 py-2 rounded-lg flex items-center gap-1.5 shadow-sm transition"
                        title="Xuất phiếu nhập ra Excel">
                        <iconify-icon icon="solar:file-spreadsheet-bold" class="text-lg"></iconify-icon>
                        <span>Xuất Excel</span>
                    </a>
                    <a href="{{ route('inventory.receipts.create') }}?supplier_id={{ $supplier->id }}"
                        class="btn btn-primary text-sm btn-sm px-3 py-2 rounded-lg flex items-center gap-1.5 shadow-sm">
                        <iconify-icon icon="ic:baseline-plus" class="text-lg"></iconify-icon>
                        <span>Tạo phiếu nhập</span>
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table bordered-table sm-table mb-0 w-full text-xs">
                        <thead>
                            <tr class="bg-neutral-50 text-neutral-700">
                                <th scope="col" class="w-12 text-center">STT</th>
                                <th scope="col" class="w-32 text-left">Mã phiếu nhập</th>
                                <th scope="col" class="w-28 text-center">Ngày nhập</th>
                                <th scope="col" class="w-36 text-left">Kho nhập</th>
                                <th scope="col" class="w-32 text-left">Người giao</th>
                                <th scope="col" class="w-32 text-left">Người nhận</th>
                                <th scope="col" class="w-36 text-end">Tổng tiền</th>
                                <th scope="col" class="w-28 text-center">Trạng thái</th>
                                <th scope="col" class="min-w-[150px] text-left">Ghi chú</th>
                                <th scope="col" class="w-28 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($receipts as $idx => $r)
                                <tr class="hover:bg-neutral-50 transition-colors">
                                    <td class="text-center font-medium text-neutral-500">
                                        {{ $receipts->firstItem() + $idx }}
                                    </td>
                                    <td>
                                        <a href="{{ route('inventory.receipts.show', $r) }}" class="font-mono font-bold text-primary-600 hover:underline">
                                            {{ $r->code }}
                                        </a>
                                    </td>
                                    <td class="text-center text-neutral-600 font-mono">
                                        {{ $r->date ? $r->date->format('d/m/Y') : '—' }}
                                    </td>
                                    <td class="text-neutral-700 font-medium">
                                        {{ $r->warehouse?->name ?? '—' }}
                                    </td>
                                    <td class="text-neutral-600">{{ $r->deliverer ?: '—' }}</td>
                                    <td class="text-neutral-600">{{ $r->receiver ?: '—' }}</td>
                                    <td class="text-end font-bold text-neutral-800">
                                        {{ number_format($r->total_amount, 0, ',', '.') }}₫
                                    </td>
                                    <td class="text-center">
                                        @if($r->status === 'cancelled')
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                Đã hủy
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Hoàn tất
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-neutral-500 max-w-[200px] truncate" title="{{ $r->notes }}">
                                        {{ $r->notes ?: '—' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('inventory.receipts.show', $r) }}"
                                                class="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 hover:bg-primary-600 hover:text-white flex items-center justify-center transition-colors"
                                                title="Xem chi tiết phiếu nhập">
                                                <iconify-icon icon="lucide:eye" class="text-sm"></iconify-icon>
                                            </a>
                                            <a href="{{ route('inventory.receipts.print', $r) }}" target="_blank"
                                                class="w-7 h-7 rounded-lg bg-neutral-100 text-neutral-600 hover:bg-neutral-800 hover:text-white flex items-center justify-center transition-colors"
                                                title="In phiếu nhập">
                                                <iconify-icon icon="lucide:printer" class="text-sm"></iconify-icon>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-neutral-400 py-8">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <iconify-icon icon="lucide:package-open" class="text-3xl text-neutral-300"></iconify-icon>
                                            <span>Chưa có phiếu nhập kho nào</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($receipts->hasPages())
                <div class="p-4 border-t border-neutral-200">
                    {{ $receipts->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- TAB 2: LỊCH SỬ THANH TOÁN (PHIẾU CHI) --}}
    <div id="tab-pane-payments" class="space-y-6 {{ $currTab === 'payments' ? '' : 'hidden' }}">
        <div class="card h-full p-0 rounded-xl border-0 overflow-hidden shadow-xs">
            <div class="card-header border-b border-neutral-200 bg-white py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
                <div class="flex items-center flex-wrap gap-3">
                    <form method="GET" action="{{ route('suppliers.show', $supplier) }}" class="navbar-search">
                        <input type="hidden" name="tab" value="payments">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        @if($dateMode && $dateMode !== 'all')
                            <input type="hidden" name="date_mode" value="{{ $dateMode }}">
                            <input type="hidden" name="date_val" value="{{ $dateVal }}">
                        @endif
                        <div class="relative">
                            <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-base"></iconify-icon>
                            <input type="text" name="search" class="form-control form-control-sm border-neutral-200 rounded-lg pl-9 pr-3 w-64 md:w-80" placeholder="Tìm kiếm mã phiếu chi, ghi chú..." value="{{ $search }}">
                        </div>
                    </form>

                    <x-date-filter :action="route('suppliers.show', $supplier)" />
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('suppliers.payments.export', array_merge(['supplier' => $supplier->id], request()->query())) }}"
                        class="btn bg-emerald-600 hover:bg-emerald-700 text-white text-sm btn-sm px-3 py-2 rounded-lg flex items-center gap-1.5 shadow-sm transition"
                        title="Xuất phiếu chi ra Excel">
                        <iconify-icon icon="solar:file-spreadsheet-bold" class="text-lg"></iconify-icon>
                        <span>Xuất Excel</span>
                    </a>
                    <button type="button" onclick="openModal('add-payment-modal')"
                        class="btn btn-primary text-sm btn-sm px-3 py-2 rounded-lg flex items-center gap-1.5 shadow-sm">
                        <iconify-icon icon="ic:baseline-plus" class="text-lg"></iconify-icon>
                        <span>Thêm phiếu chi</span>
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table bordered-table sm-table mb-0 w-full text-xs">
                        <thead>
                            <tr class="bg-neutral-50 text-neutral-700">
                                <th scope="col" class="w-12 text-center">STT</th>
                                <th scope="col" class="w-36 text-left">Mã phiếu chi</th>
                                <th scope="col" class="w-28 text-center">Ngày chi</th>
                                <th scope="col" class="w-36 text-end">Số tiền</th>
                                <th scope="col" class="w-32 text-left">Hình thức</th>
                                <th scope="col" class="w-36 text-left">Người lập</th>
                                <th scope="col" class="min-w-[200px] text-left">Ghi chú</th>
                                <th scope="col" class="w-24 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $idx => $p)
                                <tr class="hover:bg-neutral-50 transition-colors">
                                    <td class="text-center font-medium text-neutral-500">
                                        {{ $payments->firstItem() + $idx }}
                                    </td>
                                    <td>
                                        <span class="font-mono font-bold text-primary-600">{{ $p->payment_code }}</span>
                                    </td>
                                    <td class="text-center text-neutral-600 font-mono">
                                        {{ $p->payment_date ? $p->payment_date->format('d/m/Y') : '—' }}
                                    </td>
                                    <td class="text-end font-bold text-emerald-600">
                                        {{ number_format($p->amount, 0, ',', '.') }}₫
                                    </td>
                                    <td>
                                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-neutral-100 text-neutral-700 border border-neutral-200">
                                            {{ $p->payment_method ?: 'Tiền mặt' }}
                                        </span>
                                    </td>
                                    <td class="text-neutral-700 font-medium">
                                        {{ $p->creator?->name ?? '—' }}
                                    </td>
                                    <td class="text-neutral-600">
                                        {{ $p->note ?: '—' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            {{-- Sửa --}}
                                            <button type="button"
                                                onclick="openEditPaymentModal(this)"
                                                data-id="{{ $p->id }}"
                                                data-code="{{ $p->payment_code }}"
                                                data-amount="{{ $p->amount }}"
                                                data-date="{{ $p->payment_date ? $p->payment_date->format('Y-m-d') : '' }}"
                                                data-method="{{ $p->payment_method }}"
                                                data-note="{{ $p->note }}"
                                                class="w-7 h-7 rounded-lg bg-neutral-100 text-neutral-600 hover:bg-neutral-800 hover:text-white flex items-center justify-center transition-colors"
                                                title="Sửa phiếu chi">
                                                <iconify-icon icon="lucide:pencil" class="text-sm"></iconify-icon>
                                            </button>

                                            {{-- Xóa --}}
                                            <form method="POST" action="{{ route('suppliers.payments.destroy', [$supplier, $p]) }}"
                                                onsubmit="return confirm('Bạn có chắc muốn xóa phiếu chi {{ $p->payment_code }} này? Công nợ sẽ được tính toán lại.')"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center transition-colors"
                                                    title="Xóa phiếu chi">
                                                    <iconify-icon icon="lucide:trash-2" class="text-sm"></iconify-icon>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-neutral-400 py-8">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <iconify-icon icon="lucide:receipt" class="text-3xl text-neutral-300"></iconify-icon>
                                            <span>Chưa có đợt thanh toán nào</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($payments->hasPages())
                <div class="p-4 border-t border-neutral-200">
                    {{ $payments->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- TAB 3: THÔNG TIN CHI TIẾT --}}
    <div id="tab-pane-info" class="space-y-6 {{ $currTab === 'info' ? '' : 'hidden' }}">
        <div class="bg-white rounded-2xl border border-neutral-200 p-6 shadow-xs">
            <h3 class="text-base font-bold text-neutral-800 mb-4 pb-2 border-b border-neutral-100 flex items-center gap-2">
                <iconify-icon icon="lucide:building" class="text-primary-600"></iconify-icon>
                <span>Hồ sơ nhà cung cấp</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-2">
                        <span class="text-neutral-500">Mã nhà cung cấp:</span>
                        <span class="font-mono font-bold text-neutral-800">{{ $supplier->code }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-2">
                        <span class="text-neutral-500">Tên đơn vị:</span>
                        <span class="font-bold text-neutral-800">{{ $supplier->name }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-2">
                        <span class="text-neutral-500">Người đại diện / Liên hệ:</span>
                        <span class="font-medium text-neutral-800">{{ $supplier->contact_person ?: '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-2">
                        <span class="text-neutral-500">Số điện thoại:</span>
                        <span class="font-medium text-neutral-800">{{ $supplier->phone ?: '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-2">
                        <span class="text-neutral-500">Email:</span>
                        <span class="font-medium text-neutral-800">{{ $supplier->email ?: '—' }}</span>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-2">
                        <span class="text-neutral-500">Mã số thuế:</span>
                        <span class="font-mono font-bold text-neutral-800">{{ $supplier->tax_code ?: '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-2">
                        <span class="text-neutral-500">Địa chỉ:</span>
                        <span class="font-medium text-neutral-800">{{ $supplier->address ?: '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-2">
                        <span class="text-neutral-500">Nợ ban đầu:</span>
                        <span class="font-bold text-neutral-800">{{ number_format((float)$supplier->initial_debt, 0, ',', '.') }}₫</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-2">
                        <span class="text-neutral-500">Trạng thái:</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusColor }}">
                            {{ $supplier->status === 'active' ? 'Đang hoạt động' : 'Ngưng hoạt động' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between border-b border-neutral-100 pb-2">
                        <span class="text-neutral-500">Ngày tạo hồ sơ:</span>
                        <span class="font-medium text-neutral-700">{{ $supplier->created_at ? $supplier->created_at->format('d/m/Y H:i') : '—' }}</span>
                    </div>
                </div>
            </div>

            @if($supplier->notes)
            <div class="mt-6 pt-4 border-t border-neutral-100">
                <div class="text-xs font-bold text-neutral-500 mb-1">GHI CHÚ:</div>
                <div class="p-3 bg-neutral-50 rounded-xl text-neutral-700 text-sm whitespace-pre-line">{{ $supplier->notes }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- MODAL THÊM PHIẾU CHI --}}
<x-modal name="add-payment-modal" id="add-payment-modal" title="Lập phiếu chi thanh toán cho {{ $supplier->name }}" size="modal-md">
    <form method="POST" action="{{ route('suppliers.payments.store', $supplier) }}">
        @csrf
        <div class="p-6 space-y-4">
            <div class="p-3 bg-neutral-50 rounded-xl border border-neutral-200 flex items-center justify-between">
                <div>
                    <span class="text-xs text-neutral-500">Đang nợ NCC:</span>
                    <div class="text-lg font-bold text-rose-600">{{ number_format((float)$supplier->debt, 0, ',', '.') }}₫</div>
                </div>
                <span class="text-xs font-mono px-2 py-1 rounded bg-neutral-200 text-neutral-700">{{ $supplier->code }}</span>
            </div>

            <div>
                <label class="form-label text-xs font-bold text-neutral-700">Số tiền chi trả (₫) <span class="text-danger-600">*</span></label>
                <input type="number" step="1000" min="1" name="amount" required
                    value="{{ $supplier->debt > 0 ? (float)$supplier->debt : '' }}"
                    class="form-control form-control-sm rounded-lg font-bold text-emerald-600 text-base" placeholder="Nhập số tiền...">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Ngày chi <span class="text-danger-600">*</span></label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="form-control form-control-sm rounded-lg">
                </div>

                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Hình thức thanh toán</label>
                    <select name="payment_method" class="form-select form-select-sm rounded-lg">
                        <option value="Chuyển khoản" selected>Chuyển khoản</option>
                        <option value="Tiền mặt">Tiền mặt</option>
                        <option value="Khác">Khác</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="form-label text-xs font-bold text-neutral-700">Ghi chú</label>
                <textarea name="note" rows="2" class="form-control form-control-sm rounded-lg" placeholder="Ví dụ: Chuyển khoản thanh toán tiền ván đợt 1..."></textarea>
            </div>
        </div>

        <div class="px-6 py-4 bg-neutral-50 border-t border-neutral-200 flex items-center justify-end gap-3 rounded-b-xl">
            <button type="button" onclick="closeModal('add-payment-modal')" class="btn bg-neutral-100 hover:bg-neutral-200 text-neutral-700 px-4 py-2 rounded-lg font-semibold text-xs">
                Hủy bỏ
            </button>
            <button type="submit" class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg font-semibold text-xs flex items-center gap-1.5 shadow-sm">
                <iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon>
                <span>Xác nhận thanh toán</span>
            </button>
        </div>
    </form>
</x-modal>

{{-- MODAL SỬA PHIẾU CHI --}}
<x-modal name="edit-payment-modal" id="edit-payment-modal" title="Chỉnh sửa phiếu chi" size="modal-md">
    <form method="POST" action="" id="editPaymentForm">
        @csrf
        @method('PUT')
        <div class="p-6 space-y-4">
            <div class="text-xs font-mono text-neutral-500">Mã phiếu chi: <span id="edit_pay_code" class="font-bold text-neutral-800"></span></div>

            <div>
                <label class="form-label text-xs font-bold text-neutral-700">Số tiền chi trả (₫) <span class="text-danger-600">*</span></label>
                <input type="number" step="1000" min="1" name="amount" id="edit_pay_amount" required class="form-control form-control-sm rounded-lg font-bold text-emerald-600 text-base">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Ngày chi <span class="text-danger-600">*</span></label>
                    <input type="date" name="payment_date" id="edit_pay_date" required class="form-control form-control-sm rounded-lg">
                </div>

                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Hình thức thanh toán</label>
                    <select name="payment_method" id="edit_pay_method" class="form-select form-select-sm rounded-lg">
                        <option value="Chuyển khoản">Chuyển khoản</option>
                        <option value="Tiền mặt">Tiền mặt</option>
                        <option value="Khác">Khác</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="form-label text-xs font-bold text-neutral-700">Ghi chú</label>
                <textarea name="note" id="edit_pay_note" rows="2" class="form-control form-control-sm rounded-lg"></textarea>
            </div>
        </div>

        <div class="px-6 py-4 bg-neutral-50 border-t border-neutral-200 flex items-center justify-end gap-3 rounded-b-xl">
            <button type="button" onclick="closeModal('edit-payment-modal')" class="btn bg-neutral-100 hover:bg-neutral-200 text-neutral-700 px-4 py-2 rounded-lg font-semibold text-xs">
                Hủy bỏ
            </button>
            <button type="submit" class="btn btn-primary px-5 py-2 rounded-lg font-semibold text-xs flex items-center gap-1.5 shadow-sm">
                <iconify-icon icon="lucide:check" class="text-base"></iconify-icon>
                <span>Cập nhật</span>
            </button>
        </div>
    </form>
</x-modal>

{{-- MODAL SỬA THÔNG TIN NHÀ CUNG CẤP --}}
<x-modal name="edit-supplier-modal" id="edit-supplier-modal" title="Chỉnh sửa thông tin nhà cung cấp" size="modal-lg">
    <form method="POST" action="{{ route('suppliers.update', $supplier) }}" id="editSupplierForm">
        @csrf
        @method('PUT')
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Mã nhà cung cấp <span class="text-danger-600">*</span></label>
                    <input type="text" name="code" value="{{ $supplier->code }}" required class="form-control form-control-sm rounded-lg font-mono uppercase">
                </div>

                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Tên nhà cung cấp <span class="text-danger-600">*</span></label>
                    <input type="text" name="name" value="{{ $supplier->name }}" required class="form-control form-control-sm rounded-lg">
                </div>

                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Người liên hệ</label>
                    <input type="text" name="contact_person" value="{{ $supplier->contact_person }}" class="form-control form-control-sm rounded-lg">
                </div>

                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Số điện thoại</label>
                    <input type="text" name="phone" value="{{ $supplier->phone }}" class="form-control form-control-sm rounded-lg">
                </div>

                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Email</label>
                    <input type="email" name="email" value="{{ $supplier->email }}" class="form-control form-control-sm rounded-lg">
                </div>

                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Mã số thuế</label>
                    <input type="text" name="tax_code" value="{{ $supplier->tax_code }}" class="form-control form-control-sm rounded-lg font-mono">
                </div>
            </div>

            <div>
                <label class="form-label text-xs font-bold text-neutral-700">Địa chỉ</label>
                <input type="text" name="address" value="{{ $supplier->address }}" class="form-control form-control-sm rounded-lg">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Nợ đầu kỳ (₫)</label>
                    <input type="number" step="1000" name="initial_debt" value="{{ (float)$supplier->initial_debt }}" class="form-control form-control-sm rounded-lg">
                </div>

                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Trạng thái</label>
                    <select name="status" class="form-select form-select-sm rounded-lg">
                        <option value="active" {{ $supplier->status === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="inactive" {{ $supplier->status === 'inactive' ? 'selected' : '' }}>Ngưng hoạt động</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="form-label text-xs font-bold text-neutral-700">Ghi chú</label>
                <textarea name="notes" rows="2" class="form-control form-control-sm rounded-lg">{{ $supplier->notes }}</textarea>
            </div>
        </div>

        <div class="px-6 py-4 bg-neutral-50 border-t border-neutral-200 flex items-center justify-end gap-3 rounded-b-xl">
            <button type="button" onclick="closeModal('edit-supplier-modal')" class="btn bg-neutral-100 hover:bg-neutral-200 text-neutral-700 px-4 py-2 rounded-lg font-semibold text-xs">
                Hủy bỏ
            </button>
            <button type="submit" class="btn btn-primary px-5 py-2 rounded-lg font-semibold text-xs flex items-center gap-1.5">
                <iconify-icon icon="lucide:check" class="text-base"></iconify-icon>
                <span>Lưu thay đổi</span>
            </button>
        </div>
    </form>
</x-modal>

@endsection

@php
    $script = '
    <script>
        function switchSupplierTab(tab) {
            ["receipts", "payments", "info"].forEach(t => {
                const pane = document.getElementById("tab-pane-" + t);
                const btn = document.getElementById("tab-btn-" + t);
                const badge = document.getElementById("tab-badge-" + t);

                if (pane && btn) {
                    if (t === tab) {
                        pane.classList.remove("hidden");
                        btn.className = "px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition-all bg-primary-600 text-white shadow-xs";
                        if (badge) badge.className = "px-2 py-0.5 rounded-full text-xs font-extrabold bg-white/20 text-white";
                    } else {
                        pane.classList.add("hidden");
                        btn.className = "px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition-all bg-white text-neutral-600 hover:bg-neutral-100 border border-neutral-200";
                        if (badge) badge.className = "px-2 py-0.5 rounded-full text-xs font-extrabold bg-neutral-200 text-neutral-700";
                    }
                }
            });

            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set("tab", tab);
            window.history.replaceState({}, "", url);
        }

        function openEditSupplierModalFromBtn(btn) {
            openModal("edit-supplier-modal");
        }

        function openEditPaymentModal(btn) {
            const id = btn.getAttribute("data-id");
            const code = btn.getAttribute("data-code");
            const amount = btn.getAttribute("data-amount");
            const date = btn.getAttribute("data-date");
            const method = btn.getAttribute("data-method");
            const note = btn.getAttribute("data-note");

            const form = document.getElementById("editPaymentForm");
            form.action = "/suppliers/' . $supplier->id . '/payments/" + id;

            document.getElementById("edit_pay_code").textContent = code;
            document.getElementById("edit_pay_amount").value = amount;
            document.getElementById("edit_pay_date").value = date;
            document.getElementById("edit_pay_method").value = method;
            document.getElementById("edit_pay_note").value = note || "";

            openModal("edit-payment-modal");
        }
    </script>
    ';
@endphp
