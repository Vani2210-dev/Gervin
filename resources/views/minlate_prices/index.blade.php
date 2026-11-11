@extends('layout.layout')
@php
    $title    = 'Bảng giá dịch vụ Min-late & Tổ hợp theo loại đơn';
    $subTitle = 'Quản lý bảng giá gia công và cấu hình tổ hợp chi tiết hóa đơn theo từng loại đơn hàng';
    $activeTab = $activeTab ?? 'prices';
    $orderTypeLabels = [
        'Flywood'  => 'Flywood',
        'min_late' => 'Lệnh sản xuất (Min-late)',
        'acrylic'  => 'Acrylic',
        'glass'    => 'Kính',
        'service'  => 'Dịch vụ & Vật tư',
    ];
@endphp

@section('content')
<style>
    /* Smart Select (TomSelect) styling for Combo Quick Price */
    .ts-wrapper.tom-select-combo-price {
        width: 100% !important;
        min-width: 220px !important;
    }
    .ts-wrapper.tom-select-combo-price .ts-control {
        font-size: 12px !important;
        min-height: 32px !important;
        height: 32px !important;
        padding: 0 8px !important;
        border-radius: 6px !important;
        border: 1px solid #d1d5db !important;
        background-color: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        box-shadow: none !important;
    }
    .ts-wrapper.tom-select-combo-price.focus .ts-control {
        border-color: #4f46e5 !important;
        outline: 2px solid rgba(79, 70, 229, 0.2) !important;
    }
    .ts-wrapper.tom-select-combo-price .ts-control input {
        font-size: 12px !important;
        padding: 0 !important;
        margin: 0 !important;
        height: auto !important;
    }
    .ts-wrapper.tom-select-combo-price .ts-control .item {
        font-size: 12px !important;
        line-height: 1.3 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 200px !important;
    }
    body > .ts-dropdown.tom-select-combo-price-dropdown {
        font-size: 12px !important;
        z-index: 999999 !important;
        min-width: 440px !important;
        max-width: 600px !important;
        border-radius: 8px !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    }
    body > .ts-dropdown.tom-select-combo-price-dropdown .ts-dropdown-content {
        max-height: 320px !important;
    }
    body > .ts-dropdown.tom-select-combo-price-dropdown .optgroup-header {
        font-size: 11px !important;
        font-weight: 800 !important;
        text-transform: uppercase !important;
        background-color: #f1f5f9 !important;
        color: #334155 !important;
        padding: 5px 10px !important;
        letter-spacing: 0.05em !important;
    }
    body > .ts-dropdown.tom-select-combo-price-dropdown .option {
        padding: 6px 10px !important;
        border-bottom: 1px solid #f8fafc !important;
    }
    body > .ts-dropdown.tom-select-combo-price-dropdown .option.active {
        background-color: #f5f3ff !important;
        color: #4f46e5 !important;
    }
</style>
<div class="grid grid-cols-12 gap-4">
    <div class="col-span-12">
        {{-- Flash messages --}}
        @if(session('success'))
        <div class="alert alert-success bg-success-50 text-success-600 border border-success-200 rounded-xl p-4 mb-4 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <iconify-icon icon="lucide:check-circle-2" class="text-xl text-success-600"></iconify-icon>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-success-600 hover:text-success-800">&times;</button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger bg-danger-50 text-danger-600 border border-danger-200 rounded-xl p-4 mb-4 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <iconify-icon icon="lucide:alert-circle" class="text-xl text-danger-600"></iconify-icon>
                <span class="font-medium text-sm">{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-danger-600 hover:text-danger-800">&times;</button>
        </div>
        @endif

        {{-- Main Navigation Tabs --}}
        <div class="bg-white border border-neutral-200 rounded-xl shadow-sm mb-4 px-4 pt-2">
            <div class="flex items-center gap-6 border-b border-neutral-200 flex-wrap">
                <a href="{{ route('minlate_prices.index', ['tab' => 'prices']) }}"
                   class="py-3 px-3 font-bold text-sm border-b-2 flex items-center gap-2 whitespace-nowrap transition-all {{ $activeTab !== 'combos' ? 'border-primary-600 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-800 hover:border-neutral-300' }}">
                    <iconify-icon icon="lucide:list-ordered" class="text-lg"></iconify-icon>
                    <span>1. Bảng giá dịch vụ Min-late</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $activeTab !== 'combos' ? 'bg-primary-100 text-primary-700' : 'bg-neutral-100 text-neutral-600' }}">{{ $minlatePrices->total() }}</span>
                </a>
                <a href="{{ route('minlate_prices.index', ['tab' => 'combos']) }}"
                   class="py-3 px-3 font-bold text-sm border-b-2 flex items-center gap-2 whitespace-nowrap transition-all {{ $activeTab === 'combos' ? 'border-primary-600 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-800 hover:border-neutral-300' }}">
                    <iconify-icon icon="lucide:layers-3" class="text-lg"></iconify-icon>
                    <span>2. Tổ hợp dịch vụ theo loại đơn (Combo)</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $activeTab === 'combos' ? 'bg-primary-100 text-primary-700' : 'bg-neutral-100 text-neutral-600' }}">{{ $combos->count() }}</span>
                </a>
            </div>
        </div>

        @if($activeTab !== 'combos')
        {{-- ======================================================== --}}
        {{-- TAB 1: BẢNG GIÁ DỊCH VỤ MIN-LATE                         --}}
        {{-- ======================================================== --}}
        <div class="card h-full p-0 rounded-xl border-0 overflow-hidden shadow-sm bg-white">
            {{-- Header --}}
            <div class="card-header border-b border-neutral-200 bg-white py-3.5 px-6 flex flex-col gap-3">
                {{-- Tầng 1: Tìm kiếm, Bộ lọc nhóm & Nút hành động chính --}}
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center flex-wrap gap-2.5">
                        {{-- Search --}}
                        <form method="GET" action="{{ route('minlate_prices.index') }}" class="navbar-search">
                            <input type="hidden" name="tab" value="prices">
                            <input type="hidden" name="per_page" value="{{ $perPage }}">
                            <input type="hidden" name="filter_category" value="{{ $filterCategory }}">
                            <div class="relative">
                                <iconify-icon icon="solar:magnifer-linear" class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400 text-base"></iconify-icon>
                                <input type="text" name="search" class="form-control form-control-sm border-neutral-200 rounded-lg pl-9 pr-3 w-60 md:w-72" placeholder="Tìm tên dịch vụ, mã, ghi chú..." value="{{ $search }}">
                            </div>
                        </form>

                        {{-- Category Filter --}}
                        <form method="GET" action="{{ route('minlate_prices.index') }}" id="categoryFilterForm">
                            <input type="hidden" name="tab" value="prices">
                            <input type="hidden" name="per_page" value="{{ $perPage }}">
                            <input type="hidden" name="search" value="{{ $search }}">
                            <select name="filter_category" class="form-select form-select-sm border-neutral-200 rounded-lg max-w-xs"
                                onchange="document.getElementById('categoryFilterForm').submit()">
                                <option value="">-- Lọc theo nhóm dịch vụ --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ $filterCategory == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </form>
                        
                        @if($search || $filterCategory)
                        <a href="{{ route('minlate_prices.index', ['tab' => 'prices']) }}" class="btn bg-light-100 hover:bg-neutral-200 text-xs btn-sm px-2.5 py-1.5 rounded-lg flex items-center gap-1 text-neutral-600 transition">
                            <iconify-icon icon="solar:close-circle-outline" class="text-base"></iconify-icon>
                            Xóa lọc
                        </a>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('minlate_prices.export') }}"
                            class="btn bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200 text-xs btn-sm px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition-all shadow-2xs font-semibold"
                            title="Tải file Excel bảng giá hiện tại để chỉnh sửa">
                            <iconify-icon icon="lucide:download" class="text-base"></iconify-icon>
                            Xuất Excel
                        </a>
                        @can('add supply')
                        <button type="button" onclick="triggerImportExcel()"
                            class="btn bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200 text-xs btn-sm px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition-all shadow-2xs font-semibold">
                            <iconify-icon icon="lucide:file-spreadsheet" class="text-base"></iconify-icon>
                            Nhập Excel
                        </button>
                        <form id="importExcelForm" action="{{ route('minlate_prices.import') }}" method="POST" enctype="multipart/form-data" style="display: none;">
                            @csrf
                            <input type="file" id="excelImportFileInput" name="file" accept=".xlsx,.xls" onchange="submitImportForm()">
                        </form>
                        <button type="button" onclick="openModal('create-price-modal')"
                            class="btn btn-primary text-xs btn-sm px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition-all shadow-sm font-semibold">
                            <iconify-icon icon="ic:baseline-plus" class="text-base"></iconify-icon>
                            Thêm dòng dịch vụ
                        </button>
                        @endcan
                    </div>
                </div>

                {{-- Tầng 2: Bộ lọc thời gian & Hiển thị --}}
                <div class="flex items-center justify-between flex-wrap gap-2.5">
                    <div class="flex items-center flex-wrap gap-2">
                        {{-- Bộ lọc ngày tháng chung --}}
                        <x-date-filter :action="route('minlate_prices.index')" />
                    </div>

                    {{-- Per page --}}
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-secondary-light mb-0">Hiển thị</span>
                        <form method="GET" action="{{ route('minlate_prices.index') }}" id="perPageForm">
                            <input type="hidden" name="tab" value="prices">
                            <input type="hidden" name="search" value="{{ $search }}">
                            <input type="hidden" name="filter_category" value="{{ $filterCategory }}">
                            <select name="per_page" class="form-select form-select-sm w-auto border-neutral-200 rounded-lg py-1 px-2 text-xs"
                                onchange="document.getElementById('perPageForm').submit()">
                                @foreach([15, 25, 50, 100] as $option)
                                <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="card-body">
                <div class="table-responsive scroll-sm overflow-x-auto">
                    <table class="table bordered-table sm-table mb-0 border-collapse border border-neutral-200 text-xs min-w-[1000px]">
                        <thead class="bg-neutral-50 font-semibold text-neutral-700 text-center uppercase tracking-wider">
                            <tr>
                                <th class="border border-neutral-200 w-12 text-center">STT</th>
                                <th class="border border-neutral-200 w-28 text-center">Mã dịch vụ</th>
                                <th class="border border-neutral-200 text-left">Tên sản phẩm / Dịch vụ gia công</th>
                                <th class="border border-neutral-200 w-24 text-center">Đơn vị</th>
                                <th class="border border-neutral-200 w-36 text-end">Đơn giá</th>
                                <th class="border border-neutral-200 w-36 text-center">Công thức mẫu</th>
                                <th class="border border-neutral-200 text-left">Ghi chú</th>
                                <th class="border border-neutral-200 w-28 text-center">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $currentCategoryName = null; @endphp
                            @forelse($minlatePrices as $index => $item)
                                @if($item->category_name !== $currentCategoryName)
                                    @php $currentCategoryName = $item->category_name; @endphp
                                    <tr class="bg-emerald-50/60 font-bold">
                                        <td colspan="8" class="p-3 text-emerald-800 text-sm border border-neutral-200">
                                            <div class="flex items-center gap-1.5">
                                                <iconify-icon icon="solar:folder-open-bold" class="text-emerald-600 text-base"></iconify-icon>
                                                {{ $currentCategoryName ?: 'KHÁC / CHƯA PHÂN NHÓM' }}
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                                
                                <tr class="hover:bg-neutral-50/50 transition-colors">
                                    <td class="text-center border border-neutral-200 text-neutral-500 font-medium">
                                        {{ $item->stt ?: ($minlatePrices->firstItem() + $index) }}
                                    </td>
                                    <td class="text-center border border-neutral-200 font-mono font-semibold text-primary-700">
                                        {{ $item->code ?: '—' }}
                                    </td>
                                    <td class="border border-neutral-200 font-medium text-neutral-800 max-w-[400px] whitespace-normal">
                                        {!! nl2br(e($item->product_name)) !!}
                                    </td>
                                    <td class="text-center border border-neutral-200 font-medium text-neutral-600">
                                        {{ $item->unit ?: '—' }}
                                    </td>
                                    <td class="text-end border border-neutral-200 font-bold text-emerald-700">
                                        {{ number_format($item->price, 0, ',', '.') }}đ
                                    </td>
                                    <td class="text-center border border-neutral-200 font-mono font-bold text-primary-600">
                                        {{ $item->default_formula ?: '—' }}
                                    </td>
                                    <td class="border border-neutral-200 text-neutral-500 max-w-[300px] whitespace-normal">
                                        {!! nl2br(e($item->notes ?: '—')) !!}
                                    </td>
                                    <td class="text-center border border-neutral-200">
                                        <div class="flex items-center gap-2 justify-center">
                                            @can('edit supply')
                                            <button type="button"
                                                onclick="openEditModal({{ json_encode($item) }})"
                                                class="bg-success-100 hover:bg-success-200 text-success-600 font-medium w-8 h-8 flex justify-center items-center rounded-full transition-colors"
                                                title="Sửa">
                                                <iconify-icon icon="lucide:edit" class="text-base"></iconify-icon>
                                            </button>
                                            @endcan
                                            @can('delete supply')
                                            <form method="POST" action="{{ route('minlate_prices.destroy', $item) }}"
                                                onsubmit="return confirm('Xóa dòng bảng giá dịch vụ Min-late này?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="bg-danger-100 hover:bg-danger-200 text-danger-600 font-medium w-8 h-8 flex justify-center items-center rounded-full transition-colors"
                                                    title="Xóa">
                                                    <iconify-icon icon="fluent:delete-24-regular" class="text-base"></iconify-icon>
                                                </button>
                                            </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-8">
                                        <p class="text-neutral-500 text-sm">Chưa có dòng bảng giá dịch vụ Min-late nào</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="flex items-center justify-between flex-wrap gap-2 mt-6">
                    <span class="text-secondary-light text-sm">
                        Hiển thị {{ $minlatePrices->firstItem() ?? 0 }} đến {{ $minlatePrices->lastItem() ?? 0 }}
                        trong tổng {{ $minlatePrices->total() }} dòng bảng giá dịch vụ
                    </span>
                    {{ $minlatePrices->links() }}
                </div>
            </div>
        </div>

        @else
        {{-- ======================================================== --}}
        {{-- TAB 2: CẤU HÌNH TỔ HỢP DỊCH VỤ THEO LOẠI ĐƠN (COMBOS)     --}}
        {{-- ======================================================== --}}
        <div class="space-y-6">
            {{-- Action & Filters Bar --}}
            <div class="bg-white border border-neutral-200 rounded-xl p-4 shadow-sm flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold text-neutral-500 uppercase tracking-wider">Lọc loại đơn:</span>
                    <a href="{{ route('minlate_prices.index', ['tab' => 'combos']) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all {{ empty($comboOrderType) ? 'bg-primary-600 text-white border-primary-600 shadow-sm' : 'bg-neutral-50 text-neutral-700 border-neutral-300 hover:bg-neutral-100' }}">
                       Tất cả ({{ $combos->count() }})
                    </a>
                    @foreach($orderTypeLabels as $kType => $lbl)
                        @php
                            $cnt = $combos->where('order_type', $kType)->count();
                        @endphp
                        <a href="{{ route('minlate_prices.index', ['tab' => 'combos', 'combo_type' => $kType]) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all {{ $comboOrderType === $kType ? 'bg-primary-600 text-white border-primary-600 shadow-sm' : 'bg-neutral-50 text-neutral-700 border-neutral-300 hover:bg-neutral-100' }}">
                           {{ $lbl }} ({{ $cnt }})
                        </a>
                    @endforeach
                </div>

                <div>
                    @can('add supply')
                    <button type="button" onclick="openModal('create-combo-modal')"
                            class="btn btn-primary text-xs btn-sm px-3.5 py-2 rounded-lg flex items-center gap-1.5 font-bold shadow-sm">
                        <iconify-icon icon="lucide:plus-circle" class="text-base"></iconify-icon>
                        Tạo tổ hợp dịch vụ mới
                    </button>
                    @endcan
                </div>
            </div>

            {{-- Guide Box for formulas --}}
            <div class="bg-sky-50/70 border border-sky-200 rounded-xl p-4 text-xs text-sky-900 shadow-sm">
                <div class="font-bold flex items-center gap-1.5 text-sm mb-1.5 text-sky-800">
                    <iconify-icon icon="lucide:info" class="text-base text-sky-600"></iconify-icon>
                    <span>Hướng dẫn cài đặt công thức cho tổ hợp dịch vụ:</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2 mt-2 font-mono text-[11px]">
                    <div class="bg-white p-2 rounded border border-sky-200">
                        <span class="font-bold text-primary-700">(A)</span>: Độ dày tấm<br>
                        <span class="font-bold text-primary-700">(B)</span>: Cao (chiều vân gỗ)<br>
                        <span class="font-bold text-primary-700">(C)</span>: Chiều rộng
                    </div>
                    <div class="bg-white p-2 rounded border border-sky-200">
                        <span class="font-bold text-primary-700">(D)</span>: Số lượng / Tổng số tấm<br>
                        <span class="font-bold text-primary-700">(E)</span>: Số mét dán thẳng<br>
                        <span class="font-bold text-primary-700">(F)</span>: Số mét dán vát
                    </div>
                    <div class="bg-white p-2 rounded border border-sky-200">
                        <span class="font-bold text-primary-700">(G)</span>: Số mét dán vát mòi<br>
                        <span class="font-bold text-primary-700">(H)</span>: Bản rộng 40-59mm<br>
                        <span class="font-bold text-primary-700">(I)</span>: Bản rộng 17-39mm
                    </div>
                    <div class="bg-white p-2 rounded border border-sky-200">
                        <span class="font-bold text-primary-700">(J)</span>: Bản rộng 25-35mm<br>
                        <span class="font-bold text-primary-700">(K)</span>: Số vát tay nắm âm<br>
                        <span class="font-bold text-primary-700">(L)</span>: Số tấm CNC
                    </div>
                </div>
                <div class="mt-2 text-[11px] text-sky-800">
                    <strong>Ví dụ công thức thực tế:</strong> <code>=(E-H-I)*1.05</code> (Dán thẳng trừ bản rộng + 5% hao hụt), <code>=F*1.05</code> (Dán vát), <code>=D</code> (Bằng tổng số tấm ván), <code>=H*1.05</code> (Bản rộng 40-59mm).
                </div>
            </div>

            {{-- List of Combos --}}
            @forelse($combos as $combo)
            <div class="bg-white border border-neutral-200 rounded-xl shadow-sm overflow-hidden" id="combo-card-{{ $combo->id }}">
                <form action="{{ route('minlate_prices.combos.update', $combo) }}" method="POST" class="combo-form">
                    @csrf
                    @method('PUT')
                    
                    {{-- Combo Header --}}
                    <div class="bg-neutral-50/80 border-b border-neutral-200 p-4 flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3 flex-wrap flex-1">
                            <span class="w-8 h-8 rounded-lg bg-primary-100 text-primary-700 font-bold flex items-center justify-center text-sm">
                                <iconify-icon icon="lucide:layers"></iconify-icon>
                            </span>
                            <div class="flex-1 min-w-[240px]">
                                <div class="flex items-center gap-2">
                                    <input type="text" name="name" value="{{ $combo->name }}"
                                           class="form-control form-control-sm font-bold text-neutral-800 border-neutral-300 rounded-lg text-sm w-72 focus:border-primary-500 focus:ring-primary-500"
                                           placeholder="Tên tổ hợp..." required>
                                    <span class="px-2.5 py-1 rounded-md text-xs font-bold border {{ $combo->order_type === 'Flywood' ? 'bg-amber-100 text-amber-800 border-amber-300' : ($combo->order_type === 'min_late' ? 'bg-blue-100 text-blue-800 border-blue-300' : 'bg-neutral-100 text-neutral-800 border-neutral-300') }}">
                                        {{ $orderTypeLabels[$combo->order_type] ?? $combo->order_type }}
                                    </span>
                                    <select name="order_type" class="form-select form-select-sm text-xs rounded-lg border-neutral-300 w-44">
                                        @foreach($orderTypeLabels as $kType => $lbl)
                                            <option value="{{ $kType }}" {{ $combo->order_type === $kType ? 'selected' : '' }}>Áp dụng cho: {{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="text" name="description" value="{{ $combo->description }}"
                                       class="form-control form-control-sm text-xs text-neutral-500 border-transparent hover:border-neutral-200 focus:border-neutral-300 rounded mt-1 w-full bg-transparent"
                                       placeholder="Mô tả ngắn gọn về tổ hợp này (tùy chọn)...">
                            </div>
                        </div>

                        <div class="flex items-center gap-3 flex-wrap">
                            <label class="flex items-center gap-1.5 text-xs font-semibold text-neutral-700 cursor-pointer select-none">
                                <input type="checkbox" name="is_default" value="1" {{ $combo->is_default ? 'checked' : '' }} class="rounded border-neutral-300 text-primary-600 focus:ring-primary-500 h-4 w-4">
                                <span>Đặt làm mặc định cho loại đơn này</span>
                            </label>

                            <button type="submit" class="btn btn-primary text-xs btn-sm px-3.5 py-2 rounded-lg flex items-center gap-1.5 font-bold shadow-sm">
                                <iconify-icon icon="lucide:save" class="text-base"></iconify-icon>
                                <span>Lưu tổ hợp</span>
                            </button>

                            @can('delete supply')
                            <button type="button" onclick="confirmDeleteCombo({{ $combo->id }}, '{{ addslashes($combo->name) }}')"
                                    class="btn bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 text-xs btn-sm px-2.5 py-2 rounded-lg"
                                    title="Xóa tổ hợp này">
                                <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                            </button>
                            @endcan
                        </div>
                    </div>

                    {{-- Combo Items Table --}}
                    <div class="p-4">
                        <div class="overflow-x-auto rounded-lg border border-neutral-200">
                            <table class="table bordered-table sm-table mb-0 w-full text-xs" id="combo-items-table-{{ $combo->id }}">
                                <thead class="bg-neutral-50 text-neutral-700 uppercase font-bold text-center">
                                    <tr>
                                        <th class="border border-neutral-200 w-10 text-center">STT</th>
                                        <th class="border border-neutral-200 text-left" style="width: 240px; min-width: 220px;">Chọn từ bảng giá DV (Smart Select)</th>
                                        <th class="border border-neutral-200 text-left min-w-[260px]">Tên nội dung / Dịch vụ gia công <span class="text-danger-500">*</span></th>
                                        <th class="border border-neutral-200 w-24 text-center">Đơn vị</th>
                                        <th class="border border-neutral-200 w-32 text-end">Đơn giá (đ)</th>
                                        <th class="border border-neutral-200 w-28 text-end">Đơn giá chỉ</th>
                                        <th class="border border-neutral-200 w-44 text-center bg-yellow-50/70 text-yellow-900">Công thức tính SL</th>
                                        <th class="border border-neutral-200 min-w-[140px] text-left">Ghi chú</th>
                                        <th class="border border-neutral-200 w-14 text-center">Xóa</th>
                                    </tr>
                                </thead>
                                <tbody class="combo-items-body">
                                    @php
                                        $items = is_array($combo->items) ? $combo->items : [];
                                    @endphp
                                    @forelse($items as $iIndex => $item)
                                    <tr class="combo-item-row hover:bg-neutral-50/50">
                                        <td class="text-center font-bold text-neutral-400 border border-neutral-200 combo-row-stt">
                                            {{ $iIndex + 1 }}
                                        </td>
                                        <td class="border border-neutral-200 p-1" style="width: 240px; min-width: 220px;">
                                            <select class="combo-quick-price-select w-full" style="display: none;">
                                                <option value="">-- Gõ tìm mã / dịch vụ --</option>
                                                @foreach($allMinLatePrices->groupBy('category_name') as $catName => $catItems)
                                                    <optgroup label="{{ $catName ?: 'Dịch vụ khác' }}">
                                                        @foreach($catItems as $mlp)
                                                            <option value="{{ $mlp->id }}"
                                                                    {{ (!empty($item['code']) && $item['code'] === $mlp->code) ? 'selected' : '' }}>
                                                                {{ $mlp->code ? "[{$mlp->code}] " : '' }}{{ $mlp->product_name }} {{ $mlp->category_name }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="items[{{ $iIndex }}][code]" value="{{ $item['code'] ?? '' }}" class="item-code-input">
                                        </td>
                                        <td class="border border-neutral-200 p-1">
                                            <textarea name="items[{{ $iIndex }}][name]" rows="2" class="form-control form-control-sm text-xs rounded border-neutral-300 font-medium w-full resize-none item-name-input" placeholder="Tên dịch vụ / nội dung..." required>{{ $item['name'] ?? '' }}</textarea>
                                        </td>
                                        <td class="border border-neutral-200 p-1">
                                            <input type="text" name="items[{{ $iIndex }}][unit]" value="{{ $item['unit'] ?? '' }}" class="form-control form-control-sm text-xs text-center rounded border-neutral-300 item-unit-input" placeholder="tấm, m...">
                                        </td>
                                        <td class="border border-neutral-200 p-1">
                                            <input type="number" name="items[{{ $iIndex }}][price]" value="{{ $item['price'] ?? 0 }}" class="form-control form-control-sm text-xs text-end font-bold text-emerald-700 rounded border-neutral-300 item-price-input" min="0" placeholder="0">
                                        </td>
                                        <td class="border border-neutral-200 p-1">
                                            <input type="number" name="items[{{ $iIndex }}][price_only]" value="{{ $item['price_only'] ?? 0 }}" class="form-control form-control-sm text-xs text-end rounded border-neutral-300 item-price-only-input" min="0" placeholder="0">
                                        </td>
                                        <td class="border border-neutral-200 p-1 bg-yellow-50/40">
                                            <input type="text" name="items[{{ $iIndex }}][formula]" value="{{ $item['formula'] ?? ($item['quantity'] ?? '') }}" class="form-control form-control-sm text-xs text-center font-mono font-bold text-primary-700 rounded border-yellow-300 bg-white item-formula-input" placeholder="=(E-H-I)*1.05 hoặc =D" title="Nhập công thức dạng =... hoặc số lượng mặc định">
                                        </td>
                                        <td class="border border-neutral-200 p-1">
                                            <input type="text" name="items[{{ $iIndex }}][notes]" value="{{ $item['notes'] ?? '' }}" class="form-control form-control-sm text-xs rounded border-neutral-300 item-notes-input" placeholder="Ghi chú...">
                                        </td>
                                        <td class="text-center border border-neutral-200 p-1">
                                            <button type="button" onclick="this.closest('tr.combo-item-row').remove(); updateComboRowIndexes({{ $combo->id }});" class="text-neutral-400 hover:text-danger-600 transition-colors p-1" title="Xóa dòng này">
                                                <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr class="empty-combo-row">
                                        <td colspan="9" class="text-center py-4 text-neutral-400">
                                            Chưa có dòng dịch vụ nào trong tổ hợp này. Hãy bấm "+ Thêm dòng dịch vụ".
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <button type="button" onclick="addComboItemRow({{ $combo->id }})" class="btn bg-primary-50 text-primary-700 hover:bg-primary-100 border border-primary-200 text-xs btn-sm px-3 py-1.5 rounded-lg flex items-center gap-1 font-bold">
                                <iconify-icon icon="lucide:plus" class="text-base"></iconify-icon>
                                <span>Thêm dòng dịch vụ vào tổ hợp này</span>
                            </button>
                            <span class="text-xs text-neutral-400 italic">Nhấn "Lưu tổ hợp" ở trên sau khi chỉnh sửa hoặc thêm dòng.</span>
                        </div>
                    </div>
                </form>

                {{-- Hidden Form for Delete --}}
                <form id="delete-combo-form-{{ $combo->id }}" action="{{ route('minlate_prices.combos.destroy', $combo) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            @empty
            <div class="bg-white border border-neutral-200 rounded-xl p-12 text-center shadow-sm">
                <iconify-icon icon="lucide:layers" class="text-5xl text-neutral-300 mb-3"></iconify-icon>
                <h5 class="font-bold text-base text-neutral-700">Chưa có tổ hợp dịch vụ nào</h5>
                <p class="text-xs text-neutral-500 max-w-md mx-auto mt-1 mb-4">Bạn có thể tạo các tổ hợp dịch vụ mẫu (Combo) để khi tạo đơn hàng mới, hệ thống tự động điền sẵn đầy đủ các dòng dịch vụ và công thức tính tiền tương ứng.</p>
                <button type="button" onclick="openModal('create-combo-modal')" class="btn btn-primary text-xs px-4 py-2 rounded-lg font-bold">
                    + Tạo tổ hợp dịch vụ đầu tiên
                </button>
            </div>
            @endforelse
        </div>
        @endif
    </div>
</div>

{{-- Modal Thêm Dòng Bảng Giá (Tab 1) --}}
@can('add supply')
<x-modal name="create-price-modal" maxWidth="2xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50 rounded-t-xl">
        <h5 class="font-bold text-base text-neutral-800">Thêm dòng bảng giá dịch vụ Min-late mới</h5>
        <button type="button" onclick="closeModal('create-price-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('minlate_prices.store') }}" method="POST">
        @csrf
        <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Nhóm dịch vụ (Category)</label>
                <input type="text" name="category_name" class="form-control rounded-lg" placeholder="Ví dụ: ÉP MẶT, DÁN CẠNH..." value="{{ old('category_name') }}">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Mã dịch vụ</label>
                    <input type="text" name="code" class="form-control rounded-lg" placeholder="Ví dụ: ML01, DC01..." value="{{ old('code') }}">
                </div>
                <div class="form-group">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Số thứ tự (STT)</label>
                    <input type="text" name="stt" class="form-control rounded-lg" placeholder="Ví dụ: 1, 2..." value="{{ old('stt') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Tên sản phẩm / Quy cách dịch vụ <span class="text-danger-500">*</span></label>
                <textarea name="product_name" rows="3" class="form-control rounded-lg" placeholder="Nhập tên sản phẩm hoặc quy cách gia công dịch vụ..." required>{{ old('product_name') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Đơn vị tính</label>
                    <input type="text" name="unit" class="form-control rounded-lg text-center" placeholder="Ví dụ: m, cái, tấm..." value="{{ old('unit', 'm') }}">
                </div>
                <div class="form-group">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Đơn giá (VNĐ) <span class="text-danger-500">*</span></label>
                    <input type="number" name="price" class="form-control rounded-lg text-end font-semibold text-emerald-700" placeholder="0" min="0" required value="{{ old('price') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Công thức mẫu (Ví dụ: =(E-H-I)*1.05, =F*1.05, =D)</label>
                <input type="text" name="default_formula" class="form-control rounded-lg font-mono text-xs" placeholder="Ví dụ: =(E-H-I)*1.05, =F*1.05..." value="{{ old('default_formula') }}">
                <span class="text-[11px] text-neutral-500 block mt-1">Khi chọn dịch vụ này trong bảng chi tiết đơn hàng, công thức sẽ được tự động điền vào ô số lượng.</span>
            </div>

            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Ghi chú</label>
                <textarea name="notes" rows="2" class="form-control rounded-lg" placeholder="Nhập ghi chú...">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Lưu</button>
            <button type="button" onclick="closeModal('create-price-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>
@endcan

{{-- Modal Sửa Dòng Bảng Giá (Tab 1) --}}
@can('edit supply')
<x-modal name="edit-price-modal" maxWidth="2xl">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50 rounded-t-xl">
        <h5 class="font-bold text-base text-neutral-800">Chỉnh sửa dòng bảng giá dịch vụ Min-late</h5>
        <button type="button" onclick="closeModal('edit-price-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form id="edit-price-form" action="" method="POST">
        @csrf @method('PUT')
        <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Nhóm dịch vụ (Category)</label>
                <input type="text" id="edit_category_name" name="category_name" class="form-control rounded-lg">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Mã dịch vụ</label>
                    <input type="text" id="edit_code" name="code" class="form-control rounded-lg" placeholder="Ví dụ: ML01, DC01...">
                </div>
                <div class="form-group">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Số thứ tự (STT)</label>
                    <input type="text" id="edit_stt" name="stt" class="form-control rounded-lg">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Tên sản phẩm / Quy cách dịch vụ <span class="text-danger-500">*</span></label>
                <textarea id="edit_product_name" name="product_name" rows="3" class="form-control rounded-lg" required></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Đơn vị tính</label>
                    <input type="text" id="edit_unit" name="unit" class="form-control rounded-lg text-center">
                </div>
                <div class="form-group">
                    <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Đơn giá (VNĐ) <span class="text-danger-500">*</span></label>
                    <input type="number" id="edit_price" name="price" class="form-control rounded-lg text-end font-semibold text-emerald-700" min="0" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Công thức mẫu (Ví dụ: =(E-H-I)*1.05, =F*1.05, =D)</label>
                <input type="text" id="edit_default_formula" name="default_formula" class="form-control rounded-lg font-mono text-xs" placeholder="Ví dụ: =(E-H-I)*1.05, =F*1.05...">
                <span class="text-[11px] text-neutral-500 block mt-1">Khi chọn dịch vụ này trong bảng chi tiết đơn hàng, công thức sẽ được tự động điền vào ô số lượng.</span>
            </div>

            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Ghi chú</label>
                <textarea id="edit_notes" name="notes" rows="2" class="form-control rounded-lg"></textarea>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg">Cập nhật</button>
            <button type="button" onclick="closeModal('edit-price-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg">Hủy</button>
        </div>
    </form>
</x-modal>
@endcan

{{-- Modal Tạo Tổ Hợp Mới (Tab 2) --}}
@can('add supply')
<x-modal name="create-combo-modal" maxWidth="lg">
    <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50 rounded-t-xl">
        <h5 class="font-bold text-base text-neutral-800">Tạo tổ hợp dịch vụ (Combo) mới</h5>
        <button type="button" onclick="closeModal('create-combo-modal')" class="text-secondary-light hover:text-neutral-700 text-xl leading-none">&times;</button>
    </div>
    <form action="{{ route('minlate_prices.combos.store') }}" method="POST">
        @csrf
        <div class="p-6 space-y-4">
            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Loại đơn áp dụng <span class="text-danger-500">*</span></label>
                <select name="order_type" class="form-select rounded-lg text-xs" required>
                    @foreach($orderTypeLabels as $kType => $lbl)
                        <option value="{{ $kType }}" {{ ($comboOrderType === $kType || $kType === 'Flywood') ? 'selected' : '' }}>{{ $lbl }} ({{ $kType }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Tên tổ hợp dịch vụ <span class="text-danger-500">*</span></label>
                <input type="text" name="name" class="form-control rounded-lg text-sm" placeholder="Ví dụ: Tổ hợp Flywood tiêu chuẩn, Combo ép tấm..." required>
            </div>

            <div class="form-group">
                <label class="form-label font-semibold text-xs text-neutral-600 mb-1 block">Mô tả tổ hợp</label>
                <textarea name="description" rows="2" class="form-control rounded-lg text-xs" placeholder="Ghi chú quy trình hoặc đối tượng khách hàng áp dụng..."></textarea>
            </div>

            <div class="form-group">
                <label class="flex items-center gap-2 text-xs font-semibold text-neutral-700 cursor-pointer select-none">
                    <input type="checkbox" name="is_default" value="1" checked class="rounded border-neutral-300 text-primary-600 focus:ring-primary-500 h-4 w-4">
                    <span>Đặt làm tổ hợp mặc định cho loại đơn này</span>
                </label>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-200 flex gap-3">
            <button type="submit" class="btn btn-primary px-5 py-2.5 rounded-lg text-xs font-bold">Tạo tổ hợp</button>
            <button type="button" onclick="closeModal('create-combo-modal')" class="btn btn-neutral px-5 py-2.5 rounded-lg text-xs">Hủy</button>
        </div>
    </form>
</x-modal>
@endcan

<script>
    window.allMinLatePricesData = @json($allMinLatePrices);
    window.allMinLatePricesMap = {};
    (window.allMinLatePricesData || []).forEach(p => {
        window.allMinLatePricesMap[p.id] = p;
    });

    window.escapeHtml = window.escapeHtml || function(text) {
        if (text === undefined || text === null) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    };

    function flashComboInput(el) {
        if (!el) return;
        el.classList.add('bg-amber-100', 'transition-colors', 'duration-500');
        setTimeout(() => {
            el.classList.remove('bg-amber-100');
        }, 1000);
    }

    function initComboPriceTomSelect(selectEl) {
        if (!selectEl || selectEl.tomselect) return;

        const row = selectEl.closest('tr.combo-item-row');

        const ts = new TomSelect(selectEl, {
            wrapperClass: 'ts-wrapper tom-select-combo-price',
            dropdownClass: 'ts-dropdown tom-select-combo-price-dropdown',
            create: false,
            placeholder: '🔍 Gõ tìm mã / dịch vụ...',
            allowEmptyOption: true,
            maxOptions: null,
            dropdownParent: 'body',
            searchField: ['text'],
            render: {
                item: function(data, escape) {
                    const item = window.allMinLatePricesMap ? window.allMinLatePricesMap[data.value] : null;
                    if (!item) return `<div class="truncate text-xs font-semibold text-neutral-700">${escape(data.text || data.value)}</div>`;
                    const code = item.code ? `[${item.code}] ` : '';
                    return `<div class="truncate text-xs font-bold text-neutral-800" title="${escape(code + item.product_name)}">
                        ${code ? `<span class="text-primary-700 font-bold">${escape(code)}</span>` : ''}<span>${escape(item.product_name)}</span>
                    </div>`;
                },
                option: function(data, escape) {
                    const item = window.allMinLatePricesMap ? window.allMinLatePricesMap[data.value] : null;
                    if (!item) return `<div class="text-xs py-1">${escape(data.text || data.value)}</div>`;
                    const priceFmt = item.price ? Number(item.price).toLocaleString('vi-VN') + 'đ' : '0đ';
                    const unitFmt = item.unit ? '/' + item.unit : '';
                    const formulaFmt = item.default_formula ? `<span class="bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded text-[10px] font-mono font-bold">f: ${escape(item.default_formula)}</span>` : '';
                    return `<div class="py-1.5 px-2 hover:bg-purple-50 rounded transition-colors cursor-pointer">
                        <div class="flex items-center justify-between gap-3">
                            <div class="font-bold text-xs text-neutral-800 min-w-0 flex-1">
                                ${item.code ? `<span class="text-primary-700 font-mono bg-primary-50 border border-primary-200 px-1.5 py-0.5 rounded text-[11px] mr-1.5 font-bold">[${escape(item.code)}]</span>` : ''}
                                <span class="font-medium text-neutral-800">${escape(item.product_name)}</span>
                            </div>
                            <div class="text-emerald-700 font-bold text-xs whitespace-nowrap shrink-0 flex items-center gap-2">
                                <span>${priceFmt}${unitFmt}</span>
                                ${formulaFmt}
                            </div>
                        </div>
                        ${item.category_name ? `<div class="text-[10px] text-neutral-400 mt-0.5">${escape(item.category_name)}</div>` : ''}
                    </div>`;
                }
            }
        });

        ts.on('change', function(val) {
            if (!val) return;
            const item = window.allMinLatePricesMap ? window.allMinLatePricesMap[val] : null;
            if (!item) return;

            const targetRow = row || selectEl.closest('tr.combo-item-row');
            if (!targetRow) return;

            const codeInput = targetRow.querySelector('.item-code-input');
            const nameInput = targetRow.querySelector('.item-name-input');
            const unitInput = targetRow.querySelector('.item-unit-input');
            const priceInput = targetRow.querySelector('.item-price-input');
            const formulaInput = targetRow.querySelector('.item-formula-input');

            if (codeInput) codeInput.value = item.code || '';
            if (nameInput) {
                nameInput.value = item.product_name || '';
                flashComboInput(nameInput);
            }
            if (unitInput) {
                unitInput.value = item.unit || '';
                flashComboInput(unitInput);
            }
            if (priceInput) {
                priceInput.value = item.price || 0;
                flashComboInput(priceInput);
            }
            if (formulaInput && item.default_formula) {
                formulaInput.value = item.default_formula;
                flashComboInput(formulaInput);
            }
        });
    }

    function triggerImportExcel() {
        document.getElementById('excelImportFileInput').click();
    }
    
    function submitImportForm() {
        const fileInput = document.getElementById('excelImportFileInput');
        if (fileInput.files.length > 0) {
            if (confirm('Nhập bảng giá dịch vụ Min-late từ Excel sẽ thay thế toàn bộ dữ liệu hiện tại. Tiếp tục?')) {
                document.getElementById('importExcelForm').submit();
            } else {
                fileInput.value = '';
            }
        }
    }

    function openEditModal(item) {
        document.getElementById('edit-price-form').action = '/minlate-prices/' + item.id;
        document.getElementById('edit_category_name').value = item.category_name || '';
        document.getElementById('edit_code').value = item.code || '';
        document.getElementById('edit_stt').value = item.stt || '';
        document.getElementById('edit_product_name').value = item.product_name || '';
        document.getElementById('edit_unit').value = item.unit || '';
        document.getElementById('edit_price').value = item.price || 0;
        document.getElementById('edit_default_formula').value = item.default_formula || '';
        document.getElementById('edit_notes').value = item.notes || '';
        
        openModal('edit-price-modal');
    }

    function confirmDeleteCombo(comboId, comboName) {
        if (confirm(`Bạn có chắc muốn xóa tổ hợp "${comboName}"?`)) {
            document.getElementById('delete-combo-form-' + comboId).submit();
        }
    }

    function updateComboRowIndexes(comboId) {
        const tbody = document.querySelector(`#combo-items-table-${comboId} .combo-items-body`);
        if (!tbody) return;
        const rows = tbody.querySelectorAll('tr.combo-item-row');
        rows.forEach((row, idx) => {
            const sttCell = row.querySelector('.combo-row-stt');
            if (sttCell) sttCell.textContent = idx + 1;

            row.querySelectorAll('input, textarea, select').forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/items\[\d+\]/, `items[${idx}]`);
                }
            });
        });
    }

    function addComboItemRow(comboId) {
        const tbody = document.querySelector(`#combo-items-table-${comboId} .combo-items-body`);
        if (!tbody) return;

        const emptyRow = tbody.querySelector('.empty-combo-row');
        if (emptyRow) emptyRow.remove();

        const rowIndex = tbody.querySelectorAll('tr.combo-item-row').length;

        let optionsHtml = '<option value="">-- Gõ tìm mã / dịch vụ --</option>';
        const grouped = {};
        (window.allMinLatePricesData || []).forEach(mlp => {
            const cat = mlp.category_name || 'Dịch vụ khác';
            if (!grouped[cat]) grouped[cat] = [];
            grouped[cat].push(mlp);
        });

        for (const [cat, items] of Object.entries(grouped)) {
            optionsHtml += `<optgroup label="${escapeHtml(cat)}">`;
            items.forEach(mlp => {
                const codeLbl = mlp.code ? `[${mlp.code}] ` : '';
                optionsHtml += `<option value="${mlp.id}">${codeLbl}${escapeHtml(mlp.product_name || '')} ${escapeHtml(cat)}</option>`;
            });
            optionsHtml += `</optgroup>`;
        }

        const tr = document.createElement('tr');
        tr.className = 'combo-item-row hover:bg-neutral-50/50';
        tr.innerHTML = `
            <td class="text-center font-bold text-neutral-400 border border-neutral-200 combo-row-stt">
                ${rowIndex + 1}
            </td>
            <td class="border border-neutral-200 p-1" style="width: 240px; min-width: 220px;">
                <select class="combo-quick-price-select w-full" style="display: none;">
                    ${optionsHtml}
                </select>
                <input type="hidden" name="items[${rowIndex}][code]" value="" class="item-code-input">
            </td>
            <td class="border border-neutral-200 p-1">
                <textarea name="items[${rowIndex}][name]" rows="2" class="form-control form-control-sm text-xs rounded border-neutral-300 font-medium w-full resize-none item-name-input" placeholder="Tên dịch vụ / nội dung..." required></textarea>
            </td>
            <td class="border border-neutral-200 p-1">
                <input type="text" name="items[${rowIndex}][unit]" value="m" class="form-control form-control-sm text-xs text-center rounded border-neutral-300 item-unit-input" placeholder="tấm, m...">
            </td>
            <td class="border border-neutral-200 p-1">
                <input type="number" name="items[${rowIndex}][price]" value="0" class="form-control form-control-sm text-xs text-end font-bold text-emerald-700 rounded border-neutral-300 item-price-input" min="0" placeholder="0">
            </td>
            <td class="border border-neutral-200 p-1">
                <input type="number" name="items[${rowIndex}][price_only]" value="0" class="form-control form-control-sm text-xs text-end rounded border-neutral-300 item-price-only-input" min="0" placeholder="0">
            </td>
            <td class="border border-neutral-200 p-1 bg-yellow-50/40">
                <input type="text" name="items[${rowIndex}][formula]" value="" class="form-control form-control-sm text-xs text-center font-mono font-bold text-primary-700 rounded border-yellow-300 bg-white item-formula-input" placeholder="=(E-H-I)*1.05 hoặc =D" title="Nhập công thức dạng =... hoặc số lượng mặc định">
            </td>
            <td class="border border-neutral-200 p-1">
                <input type="text" name="items[${rowIndex}][notes]" value="" class="form-control form-control-sm text-xs rounded border-neutral-300 item-notes-input" placeholder="Ghi chú...">
            </td>
            <td class="text-center border border-neutral-200 p-1">
                <button type="button" onclick="this.closest('tr.combo-item-row').remove(); updateComboRowIndexes(${comboId});" class="text-neutral-400 hover:text-danger-600 transition-colors p-1" title="Xóa dòng này">
                    <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        updateComboRowIndexes(comboId);

        const newSel = tr.querySelector('.combo-quick-price-select');
        if (newSel && typeof TomSelect !== 'undefined') {
            initComboPriceTomSelect(newSel);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof TomSelect !== 'undefined') {
            document.querySelectorAll('.combo-quick-price-select').forEach(el => {
                initComboPriceTomSelect(el);
            });
        }
    });
</script>
@endsection
