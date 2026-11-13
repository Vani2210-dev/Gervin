@extends('layout.layout')
@php
    $title    = 'Nhà cung cấp';
    $subTitle = 'Quản lý Nhà cung cấp & Công nợ';
@endphp

@section('content')

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
    {{-- Statistics Grid --}}
    <div class="col-span-12">
        <div class="stats-custom-grid">
            <!-- Card 1: Tổng nhà cung cấp -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center text-primary-600 text-xl flex-shrink-0">
                    <iconify-icon icon="lucide:truck"></iconify-icon>
                </div>
                <div>
                    <div class="text-xs text-neutral-500 font-medium mb-0.5">Tổng nhà cung cấp</div>
                    <div class="text-lg font-bold text-neutral-800">{{ $totalSuppliersCount }} NCC</div>
                </div>
            </div>

            <!-- Card 2: NCC mới -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-xl flex-shrink-0">
                    <iconify-icon icon="lucide:plus-circle"></iconify-icon>
                </div>
                <div>
                    <div class="text-xs text-neutral-500 font-medium mb-0.5">
                        NCC mới tạo
                        <span style="font-size: 9px;" class="font-normal text-neutral-400 capitalize">({{ $dateLabel }})</span>
                    </div>
                    <div class="text-lg font-bold text-neutral-800">{{ $newSuppliersCount }} NCC</div>
                </div>
            </div>

            <!-- Card 3: Đang hoạt động -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 text-xl flex-shrink-0">
                    <iconify-icon icon="lucide:check-circle-2"></iconify-icon>
                </div>
                <div>
                    <div class="text-xs text-neutral-500 font-medium mb-0.5">Đang hoạt động</div>
                    <div class="text-lg font-bold text-neutral-800">{{ $activeSuppliersCount }} NCC</div>
                </div>
            </div>

            <!-- Card 4: Tổng đã thanh toán -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-success-50 flex items-center justify-center text-success-600 text-xl flex-shrink-0">
                    <iconify-icon icon="lucide:wallet"></iconify-icon>
                </div>
                <div class="min-w-0">
                    <div class="text-xs text-neutral-500 font-medium mb-0.5 truncate">
                        Đã thanh toán
                        <span style="font-size: 9px;" class="font-normal text-neutral-400 capitalize">({{ $dateLabel }})</span>
                    </div>
                    <div class="text-lg font-bold text-neutral-800 truncate">{{ number_format($totalPeriodPaidSum, 0, ',', '.') }}₫</div>
                </div>
            </div>

            <!-- Card 5: Tổng công nợ phải trả -->
            <div class="bg-white border border-neutral-200 rounded-2xl p-4 shadow-sm flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-danger-50 flex items-center justify-center text-danger-600 text-xl flex-shrink-0">
                    <iconify-icon icon="lucide:alert-circle"></iconify-icon>
                </div>
                <div class="min-w-0">
                    <div class="text-xs text-neutral-500 font-medium mb-0.5">Tổng nợ phải trả</div>
                    <div class="text-lg font-bold text-rose-600 truncate">{{ number_format($totalDebtSum, 0, ',', '.') }}₫</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="col-span-12">
        <div class="card h-full p-0 rounded-xl border-0 overflow-hidden shadow-xs">
            {{-- Header --}}
            <div class="card-header border-b border-neutral-200 bg-white py-3.5 px-6 flex flex-col gap-3">
                {{-- Tầng 1: Tìm kiếm & Nút hành động chính --}}
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center flex-wrap gap-2.5">
                        {{-- Search --}}
                        <form method="GET" action="{{ route('suppliers.index') }}" class="navbar-search">
                            <input type="hidden" name="per_page" value="{{ $perPage }}">
                            @if($dateMode && $dateMode !== 'all')
                                <input type="hidden" name="date_mode" value="{{ $dateMode }}">
                                <input type="hidden" name="date_val" value="{{ $dateVal }}">
                            @endif
                            <div class="relative">
                                <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-base"></iconify-icon>
                                <input type="text" name="search" class="form-control form-control-sm border-neutral-200 rounded-lg pl-9 pr-3 w-60 md:w-72" placeholder="Tìm kiếm mã, tên, SĐT, MST, liên hệ..." value="{{ $search }}">
                            </div>
                        </form>

                        <button type="button" onclick="openModal('filter-modal')"
                            class="btn bg-light-600 text-xs btn-sm px-2.5 py-1.5 rounded-lg flex items-center gap-1.5 font-medium text-neutral-700 hover:bg-neutral-200 transition">
                            <iconify-icon icon="solar:filter-outline" class="text-base"></iconify-icon>
                            Lọc nâng cao
                        </button>

                        @if(request()->filled('filter_supplier_code') || request()->filled('filter_name') || request()->filled('filter_phone') || request()->filled('filter_status') || request()->filled('filter_debt_level') || ($dateMode !== 'all' && request()->filled('date_mode')))
                        <a href="{{ route('suppliers.index') }}" class="btn text-xs btn-sm px-2.5 py-1.5 rounded-lg flex items-center gap-1 text-danger-600 bg-danger-50 hover:bg-danger-100 transition">
                            <iconify-icon icon="solar:close-circle-outline" class="text-base"></iconify-icon>
                            Xóa lọc
                        </a>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Export Excel --}}
                        <a href="{{ route('suppliers.export', request()->query()) }}"
                            class="btn bg-emerald-600 hover:bg-emerald-700 text-white text-xs btn-sm px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-2xs font-semibold transition"
                            title="Xuất danh sách nhà cung cấp & công nợ ra file Excel">
                            <iconify-icon icon="solar:file-spreadsheet-bold" class="text-base"></iconify-icon>
                            <span>Xuất Excel</span>
                        </a>

                        {{-- Thêm nhà cung cấp --}}
                        <button type="button" onclick="openCreateSupplierModal()"
                            class="btn btn-primary text-xs btn-sm px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-sm font-semibold transition">
                            <iconify-icon icon="ic:baseline-plus" class="text-base"></iconify-icon>
                            <span>Thêm NCC</span>
                        </button>
                    </div>
                </div>

                {{-- Tầng 2: Bộ lọc thời gian & Hiển thị --}}
                <div class="flex items-center justify-between flex-wrap gap-2.5">
                    <div class="flex items-center flex-wrap gap-2">
                        {{-- Bộ lọc ngày tháng chung --}}
                        <x-date-filter :action="route('suppliers.index')" />
                    </div>

                    {{-- Per page --}}
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-secondary-light mb-0">Hiển thị</span>
                        <form method="GET" action="{{ route('suppliers.index') }}" id="perPageForm">
                            <input type="hidden" name="search" value="{{ $search }}">
                            @if($dateMode && $dateMode !== 'all')
                                <input type="hidden" name="date_mode" value="{{ $dateMode }}">
                                <input type="hidden" name="date_val" value="{{ $dateVal }}">
                            @endif
                            <select name="per_page" class="form-select form-select-sm w-auto border-neutral-200 rounded-lg py-1 px-2 text-xs"
                                onchange="document.getElementById('perPageForm').submit()">
                                @foreach([10, 25, 50, 100] as $option)
                                <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="p-1">
                <div class="overflow-x-auto">
                    <table class="table bordered-table sm-table mb-0 w-full text-xs">
                        <thead>
                            <tr class="bg-neutral-50 text-neutral-700">
                                <th scope="col" class="w-12 text-center">STT</th>
                                <th scope="col" class="w-28 text-left">Mã NCC</th>
                                <th scope="col" class="min-w-[200px] text-left">Tên nhà cung cấp</th>
                                <th scope="col" class="w-32 text-left">Người liên hệ</th>
                                <th scope="col" class="w-32 text-left">Số điện thoại</th>
                                <th scope="col" class="min-w-[180px] text-left">Địa chỉ / MST</th>
                                <th scope="col" class="w-32 text-end">Tổng tiền nhập</th>
                                <th scope="col" class="w-32 text-end">Đã thanh toán</th>
                                <th scope="col" class="w-36 text-end">Công nợ hiện tại</th>
                                <th scope="col" class="w-28 text-center">Trạng thái</th>
                                <th scope="col" class="w-36 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliers as $index => $supplier)
                                @php
                                    $purchases = (float)($supplier->period_purchases ?? 0);
                                    $paid = (float)($supplier->period_paid ?? 0);
                                    $debt = (float)($supplier->debt ?? 0);
                                @endphp
                                <tr class="hover:bg-neutral-50 transition-colors">
                                    <td class="text-center font-medium text-neutral-500">
                                        {{ $suppliers->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="font-mono font-bold text-primary-600 hover:underline">
                                            {{ $supplier->code }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="font-bold text-neutral-800 hover:text-primary-600 block text-sm">
                                            {{ $supplier->name }}
                                        </a>
                                        @if($supplier->email)
                                            <span class="text-[11px] text-neutral-400 block truncate">{{ $supplier->email }}</span>
                                        @endif
                                    </td>
                                    <td class="text-neutral-700">
                                        {{ $supplier->contact_person ?: '—' }}
                                    </td>
                                    <td>
                                        @if($supplier->phone)
                                            <a href="tel:{{ $supplier->phone }}" class="text-neutral-700 hover:text-primary-600 font-medium">
                                                {{ $supplier->phone }}
                                            </a>
                                        @else
                                            <span class="text-neutral-400">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-neutral-600 max-w-[220px] truncate" title="{{ $supplier->address }}">
                                            {{ $supplier->address ?: '—' }}
                                        </div>
                                        @if($supplier->tax_code)
                                            <div class="text-[11px] text-neutral-400 font-mono">MST: {{ $supplier->tax_code }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end font-medium text-neutral-700">
                                        {{ number_format($purchases, 0, ',', '.') }}₫
                                    </td>
                                    <td class="text-end font-medium text-emerald-600">
                                        {{ number_format($paid, 0, ',', '.') }}₫
                                    </td>
                                    <td class="text-end font-bold">
                                        @if($debt > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-rose-50 text-rose-700 border border-rose-200">
                                                {{ number_format($debt, 0, ',', '.') }}₫
                                            </span>
                                        @elseif($debt < 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-purple-50 text-purple-700 border border-purple-200" title="Đã trả thừa cho NCC">
                                                {{ number_format($debt, 0, ',', '.') }}₫
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                0₫
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($supplier->status === 'active')
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Hoạt động
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-neutral-100 text-neutral-600 border border-neutral-200">
                                                Ngưng
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            {{-- Xem chi tiết --}}
                                            <a href="{{ route('suppliers.show', $supplier) }}"
                                                class="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 hover:bg-primary-600 hover:text-white flex items-center justify-center transition-colors"
                                                title="Xem chi tiết & công nợ">
                                                <iconify-icon icon="lucide:eye" class="text-sm"></iconify-icon>
                                            </a>

                                            {{-- Lập phiếu chi nhanh --}}
                                            <button type="button"
                                                onclick="openQuickPaymentModal({{ $supplier->id }}, '{{ addslashes($supplier->name) }}', '{{ $supplier->code }}', {{ $debt }})"
                                                class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white flex items-center justify-center transition-colors"
                                                title="Lập phiếu chi / Thanh toán nợ">
                                                <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon>
                                            </button>

                                            {{-- Sửa --}}
                                            <button type="button"
                                                onclick="openEditSupplierModalFromBtn(this)"
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
                                                class="w-7 h-7 rounded-lg bg-neutral-100 text-neutral-600 hover:bg-neutral-800 hover:text-white flex items-center justify-center transition-colors"
                                                title="Sửa thông tin NCC">
                                                <iconify-icon icon="lucide:pencil" class="text-sm"></iconify-icon>
                                            </button>

                                            {{-- Xóa --}}
                                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhà cung cấp này?')"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center transition-colors"
                                                    title="Xóa nhà cung cấp">
                                                    <iconify-icon icon="lucide:trash-2" class="text-sm"></iconify-icon>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-neutral-400 py-8">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <iconify-icon icon="lucide:truck" class="text-3xl text-neutral-300"></iconify-icon>
                                            <span>Chưa có nhà cung cấp nào</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($suppliers->hasPages())
                <div class="p-4 border-t border-neutral-200">
                    {{ $suppliers->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- MODAL THÊM MỚI NHÀ CUNG CẤP --}}
<x-modal name="create-supplier-modal" id="create-supplier-modal" title="Thêm mới nhà cung cấp" size="modal-lg">
    <form method="POST" action="{{ route('suppliers.store') }}" id="createSupplierForm">
        @csrf
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Mã NCC --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Mã nhà cung cấp <span class="text-danger-600">*</span></label>
                    <div class="relative">
                        <input type="text" name="code" id="create_supplier_code" value="{{ $nextCode }}" required
                            class="form-control form-control-sm rounded-lg font-mono uppercase" placeholder="Ví dụ: NCC00001">
                        <span id="create_code_status" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-semibold"></span>
                    </div>
                </div>

                {{-- Tên NCC --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Tên nhà cung cấp <span class="text-danger-600">*</span></label>
                    <input type="text" name="name" required class="form-control form-control-sm rounded-lg" placeholder="Công ty / Tên NCC">
                </div>

                {{-- Người liên hệ --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Người liên hệ</label>
                    <input type="text" name="contact_person" class="form-control form-control-sm rounded-lg" placeholder="Họ tên người liên hệ">
                </div>

                {{-- Số điện thoại --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Số điện thoại</label>
                    <input type="text" name="phone" class="form-control form-control-sm rounded-lg" placeholder="09xxxxxxxxx">
                </div>

                {{-- Email --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Email</label>
                    <input type="email" name="email" class="form-control form-control-sm rounded-lg" placeholder="ncc@example.com">
                </div>

                {{-- Mã số thuế --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Mã số thuế</label>
                    <input type="text" name="tax_code" class="form-control form-control-sm rounded-lg font-mono" placeholder="MST công ty">
                </div>
            </div>

            {{-- Địa chỉ --}}
            <div>
                <label class="form-label text-xs font-bold text-neutral-700">Địa chỉ</label>
                <input type="text" name="address" class="form-control form-control-sm rounded-lg" placeholder="Địa chỉ trụ sở / kho hàng NCC">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Nợ ban đầu --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Nợ đầu kỳ (₫)</label>
                    <input type="number" step="1000" name="initial_debt" value="0" class="form-control form-control-sm rounded-lg" placeholder="Số tiền nợ ban đầu (nếu có)">
                    <span class="text-[11px] text-neutral-400 mt-1 block">Dương: ta nợ NCC. Âm: ta trả thừa trước cho NCC.</span>
                </div>

                {{-- Trạng thái --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Trạng thái</label>
                    <select name="status" class="form-select form-select-sm rounded-lg">
                        <option value="active" selected>Đang hoạt động</option>
                        <option value="inactive">Ngưng hoạt động</option>
                    </select>
                </div>
            </div>

            {{-- Ghi chú --}}
            <div>
                <label class="form-label text-xs font-bold text-neutral-700">Ghi chú</label>
                <textarea name="notes" rows="2" class="form-control form-control-sm rounded-lg" placeholder="Ghi chú về nhà cung cấp, mặt hàng cung cấp, chính sách công nợ..."></textarea>
            </div>
        </div>

        <div class="px-6 py-4 bg-neutral-50 border-t border-neutral-200 flex items-center justify-end gap-3 rounded-b-xl">
            <button type="button" onclick="closeModal('create-supplier-modal')" class="btn bg-neutral-100 hover:bg-neutral-200 text-neutral-700 px-4 py-2 rounded-lg font-semibold text-xs">
                Hủy bỏ
            </button>
            <button type="submit" id="createSubmitBtn" class="btn btn-primary px-5 py-2 rounded-lg font-semibold text-xs flex items-center gap-1.5">
                <iconify-icon icon="lucide:check" class="text-base"></iconify-icon>
                <span>Lưu nhà cung cấp</span>
            </button>
        </div>
    </form>
</x-modal>

{{-- MODAL SỬA NHÀ CUNG CẤP --}}
<x-modal name="edit-supplier-modal" id="edit-supplier-modal" title="Chỉnh sửa thông tin nhà cung cấp" size="modal-lg">
    <form method="POST" action="" id="editSupplierForm">
        @csrf
        @method('PUT')
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Mã NCC --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Mã nhà cung cấp <span class="text-danger-600">*</span></label>
                    <input type="text" name="code" id="edit_supplier_code" required
                        class="form-control form-control-sm rounded-lg font-mono uppercase">
                </div>

                {{-- Tên NCC --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Tên nhà cung cấp <span class="text-danger-600">*</span></label>
                    <input type="text" name="name" id="edit_supplier_name" required class="form-control form-control-sm rounded-lg">
                </div>

                {{-- Người liên hệ --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Người liên hệ</label>
                    <input type="text" name="contact_person" id="edit_supplier_contact_person" class="form-control form-control-sm rounded-lg">
                </div>

                {{-- Số điện thoại --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Số điện thoại</label>
                    <input type="text" name="phone" id="edit_supplier_phone" class="form-control form-control-sm rounded-lg">
                </div>

                {{-- Email --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Email</label>
                    <input type="email" name="email" id="edit_supplier_email" class="form-control form-control-sm rounded-lg">
                </div>

                {{-- Mã số thuế --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Mã số thuế</label>
                    <input type="text" name="tax_code" id="edit_supplier_tax_code" class="form-control form-control-sm rounded-lg font-mono">
                </div>
            </div>

            {{-- Địa chỉ --}}
            <div>
                <label class="form-label text-xs font-bold text-neutral-700">Địa chỉ</label>
                <input type="text" name="address" id="edit_supplier_address" class="form-control form-control-sm rounded-lg">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Nợ ban đầu --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Nợ đầu kỳ (₫)</label>
                    <input type="number" step="1000" name="initial_debt" id="edit_supplier_initial_debt" class="form-control form-control-sm rounded-lg">
                </div>

                {{-- Trạng thái --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Trạng thái</label>
                    <select name="status" id="edit_supplier_status" class="form-select form-select-sm rounded-lg">
                        <option value="active">Đang hoạt động</option>
                        <option value="inactive">Ngưng hoạt động</option>
                    </select>
                </div>
            </div>

            {{-- Ghi chú --}}
            <div>
                <label class="form-label text-xs font-bold text-neutral-700">Ghi chú</label>
                <textarea name="notes" id="edit_supplier_notes" rows="2" class="form-control form-control-sm rounded-lg"></textarea>
            </div>
        </div>

        <div class="px-6 py-4 bg-neutral-50 border-t border-neutral-200 flex items-center justify-end gap-3 rounded-b-xl">
            <button type="button" onclick="closeModal('edit-supplier-modal')" class="btn bg-neutral-100 hover:bg-neutral-200 text-neutral-700 px-4 py-2 rounded-lg font-semibold text-xs">
                Hủy bỏ
            </button>
            <button type="submit" class="btn btn-primary px-5 py-2 rounded-lg font-semibold text-xs flex items-center gap-1.5">
                <iconify-icon icon="lucide:check" class="text-base"></iconify-icon>
                <span>Cập nhật</span>
            </button>
        </div>
    </form>
</x-modal>

{{-- MODAL LẬP PHIẾU CHI NHANH (THANH TOÁN NCC) --}}
<x-modal name="quick-payment-modal" id="quick-payment-modal" title="Lập phiếu chi thanh toán nhà cung cấp" size="modal-md">
    <form method="POST" action="" id="quickPaymentForm">
        @csrf
        <div class="p-6 space-y-4">
            <div class="p-3 bg-neutral-50 rounded-xl border border-neutral-200">
                <div class="text-xs text-neutral-500 font-medium">Nhà cung cấp:</div>
                <div class="font-bold text-neutral-800 text-sm" id="quickPaySupplierName"></div>
                <div class="flex items-center gap-2 mt-1 text-xs">
                    <span class="text-neutral-500">Mã NCC:</span>
                    <span class="font-mono font-bold text-primary-600" id="quickPaySupplierCode"></span>
                    <span class="text-neutral-400">|</span>
                    <span class="text-neutral-500">Đang nợ:</span>
                    <span class="font-bold text-rose-600" id="quickPaySupplierDebt"></span>
                </div>
            </div>

            {{-- Số tiền thanh toán --}}
            <div>
                <label class="form-label text-xs font-bold text-neutral-700">Số tiền chi trả (₫) <span class="text-danger-600">*</span></label>
                <input type="number" step="1000" min="1" name="amount" id="quickPayAmount" required class="form-control form-control-sm rounded-lg font-bold text-emerald-600 text-base" placeholder="Nhập số tiền...">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Ngày chi --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Ngày chi <span class="text-danger-600">*</span></label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="form-control form-control-sm rounded-lg">
                </div>

                {{-- Hình thức --}}
                <div>
                    <label class="form-label text-xs font-bold text-neutral-700">Hình thức</label>
                    <select name="payment_method" class="form-select form-select-sm rounded-lg">
                        <option value="Chuyển khoản" selected>Chuyển khoản</option>
                        <option value="Tiền mặt">Tiền mặt</option>
                        <option value="Khác">Khác</option>
                    </select>
                </div>
            </div>

            {{-- Ghi chú --}}
            <div>
                <label class="form-label text-xs font-bold text-neutral-700">Ghi chú phiếu chi</label>
                <textarea name="note" rows="2" class="form-control form-control-sm rounded-lg" placeholder="Ví dụ: Chuyển khoản thanh toán tiền ván..."></textarea>
            </div>
        </div>

        <div class="px-6 py-4 bg-neutral-50 border-t border-neutral-200 flex items-center justify-end gap-3 rounded-b-xl">
            <button type="button" onclick="closeModal('quick-payment-modal')" class="btn bg-neutral-100 hover:bg-neutral-200 text-neutral-700 px-4 py-2 rounded-lg font-semibold text-xs">
                Hủy bỏ
            </button>
            <button type="submit" class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg font-semibold text-xs flex items-center gap-1.5 shadow-sm">
                <iconify-icon icon="lucide:check-circle" class="text-base"></iconify-icon>
                <span>Xác nhận thanh toán</span>
            </button>
        </div>
    </form>
</x-modal>

{{-- MODAL BỘ LỌC NÂNG CAO --}}
<x-modal name="filter-modal" id="filter-modal" title="Bộ lọc nâng cao" size="modal-md">
    <form method="GET" action="{{ route('suppliers.index') }}">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        @if($dateMode && $dateMode !== 'all')
            <input type="hidden" name="date_mode" value="{{ $dateMode }}">
            <input type="hidden" name="date_val" value="{{ $dateVal }}">
        @endif

        <div class="p-6 space-y-4">
            <div>
                <label class="form-label text-xs font-bold text-neutral-700">Mã nhà cung cấp</label>
                <input type="text" name="filter_supplier_code" value="{{ request('filter_supplier_code') }}" class="form-control form-control-sm rounded-lg" placeholder="Mã NCC...">
            </div>

            <div>
                <label class="form-label text-xs font-bold text-neutral-700">Tên nhà cung cấp</label>
                <input type="text" name="filter_name" value="{{ request('filter_name') }}" class="form-control form-control-sm rounded-lg" placeholder="Tên NCC...">
            </div>

            <div>
                <label class="form-label text-xs font-bold text-neutral-700">Số điện thoại</label>
                <input type="text" name="filter_phone" value="{{ request('filter_phone') }}" class="form-control form-control-sm rounded-lg" placeholder="Số điện thoại...">
            </div>

            <div>
                <label class="form-label text-xs font-bold text-neutral-700">Mức công nợ</label>
                <select name="filter_debt_level" class="form-select form-select-sm rounded-lg">
                    <option value="" {{ request('filter_debt_level') == '' ? 'selected' : '' }}>-- Tất cả mức nợ --</option>
                    <option value="has_debt" {{ request('filter_debt_level') == 'has_debt' ? 'selected' : '' }}>Đang còn nợ (> 0₫)</option>
                    <option value="no_debt" {{ request('filter_debt_level') == 'no_debt' ? 'selected' : '' }}>Hết nợ (<= 0₫)</option>
                    <option value="0-10" {{ request('filter_debt_level') == '0-10' ? 'selected' : '' }}>Dưới 10 triệu</option>
                    <option value="10-30" {{ request('filter_debt_level') == '10-30' ? 'selected' : '' }}>10 - 30 triệu</option>
                    <option value="30-50" {{ request('filter_debt_level') == '30-50' ? 'selected' : '' }}>30 - 50 triệu</option>
                    <option value="50-100" {{ request('filter_debt_level') == '50-100' ? 'selected' : '' }}>50 - 100 triệu</option>
                    <option value="100+" {{ request('filter_debt_level') == '100+' ? 'selected' : '' }}>Trên 100 triệu</option>
                </select>
            </div>

            <div>
                <label class="form-label text-xs font-bold text-neutral-700">Trạng thái</label>
                <select name="filter_status" class="form-select form-select-sm rounded-lg">
                    <option value="" {{ request('filter_status') == '' ? 'selected' : '' }}>-- Tất cả trạng thái --</option>
                    <option value="active" {{ request('filter_status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="inactive" {{ request('filter_status') == 'inactive' ? 'selected' : '' }}>Ngưng hoạt động</option>
                </select>
            </div>
        </div>

        <div class="px-6 py-4 bg-neutral-50 border-t border-neutral-200 flex items-center justify-end gap-3 rounded-b-xl">
            <a href="{{ route('suppliers.index') }}" class="btn bg-neutral-100 hover:bg-neutral-200 text-neutral-700 px-4 py-2 rounded-lg font-semibold text-xs">
                Xóa lọc
            </a>
            <button type="submit" class="btn btn-primary px-5 py-2 rounded-lg font-semibold text-xs flex items-center gap-1.5">
                <iconify-icon icon="solar:filter-bold" class="text-base"></iconify-icon>
                <span>Áp dụng lọc</span>
            </button>
        </div>
    </form>
</x-modal>

@endsection

@php
    $script = '
    <script>
        function openCreateSupplierModal() {
            openModal("create-supplier-modal");
        }

        function openEditSupplierModalFromBtn(btn) {
            const id = btn.getAttribute("data-id");
            const form = document.getElementById("editSupplierForm");
            form.action = "/suppliers/" + id;

            document.getElementById("edit_supplier_code").value = btn.getAttribute("data-code") || "";
            document.getElementById("edit_supplier_name").value = btn.getAttribute("data-name") || "";
            document.getElementById("edit_supplier_phone").value = btn.getAttribute("data-phone") || "";
            document.getElementById("edit_supplier_email").value = btn.getAttribute("data-email") || "";
            document.getElementById("edit_supplier_tax_code").value = btn.getAttribute("data-tax_code") || "";
            document.getElementById("edit_supplier_contact_person").value = btn.getAttribute("data-contact_person") || "";
            document.getElementById("edit_supplier_address").value = btn.getAttribute("data-address") || "";
            document.getElementById("edit_supplier_initial_debt").value = btn.getAttribute("data-initial_debt") || 0;
            document.getElementById("edit_supplier_status").value = btn.getAttribute("data-status") || "active";
            document.getElementById("edit_supplier_notes").value = btn.getAttribute("data-notes") || "";

            openModal("edit-supplier-modal");
        }

        function openQuickPaymentModal(supplierId, name, code, currentDebt) {
            const form = document.getElementById("quickPaymentForm");
            form.action = "/suppliers/" + supplierId + "/payments";

            document.getElementById("quickPaySupplierName").textContent = name;
            document.getElementById("quickPaySupplierCode").textContent = code;
            document.getElementById("quickPaySupplierDebt").textContent = new Intl.NumberFormat("vi-VN").format(currentDebt) + "₫";

            const amountInput = document.getElementById("quickPayAmount");
            amountInput.value = currentDebt > 0 ? currentDebt : "";

            openModal("quick-payment-modal");
        }

        // Kiểm tra mã NCC trùng lặp khi nhập form tạo mới
        const codeInput = document.getElementById("create_supplier_code");
        const codeStatus = document.getElementById("create_code_status");
        const submitBtn = document.getElementById("createSubmitBtn");
        let checkTimeout = null;

        if (codeInput && codeStatus) {
            codeInput.addEventListener("input", function() {
                const code = this.value.trim();
                clearTimeout(checkTimeout);
                if (!code) {
                    codeStatus.textContent = "";
                    return;
                }
                checkTimeout = setTimeout(() => {
                    fetch("/suppliers/check-code?code=" + encodeURIComponent(code))
                        .then(res => res.json())
                        .then(data => {
                            if (data.exists) {
                                codeStatus.textContent = "Đã tồn tại";
                                codeStatus.className = "absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-rose-600";
                                submitBtn.disabled = true;
                            } else {
                                codeStatus.textContent = "Hợp lệ";
                                codeStatus.className = "absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-emerald-600";
                                submitBtn.disabled = false;
                            }
                        })
                        .catch(() => {});
                }, 300);
            });
        }
    </script>
    ';
@endphp
