@extends('layout.layout')

@php
    $title = 'Kho DC';
    $subTitle = 'Quản lý tấm ván dư';
@endphp

@section('content')
<style>
    #stockTable th, #stockTable td { text-align: left !important; }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
    .status-available { background: #10b981; }
    .status-used      { background: #9ca3af; }
    .status-reserved  { background: #f59e0b; }
    datalist option { font-size: 13px; }
</style>

    <div class="-mt-4 mb-6">
        <p class="text-sm text-neutral-500 dark:text-neutral-400">Quản lý tấm ván thừa sau cắt CNC. Tra cứu nhanh theo mã hàng, kích thước và vị trí kho.</p>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-4 p-4 rounded-xl border border-success-200 bg-success-50 text-success-700 flex items-center justify-between text-xs font-semibold">
        <div class="flex items-center gap-2">
            <iconify-icon icon="lucide:check-circle" class="text-lg text-success-600"></iconify-icon>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-neutral-400 hover:text-neutral-600 text-lg">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-4 p-4 rounded-xl border border-danger-200 bg-danger-50 text-danger-700 flex items-center justify-between text-xs font-semibold">
        <div class="flex items-center gap-2">
            <iconify-icon icon="lucide:alert-circle" class="text-lg text-danger-600"></iconify-icon>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-neutral-400 hover:text-neutral-600 text-lg">&times;</button>
    </div>
    @endif

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-950/30 flex items-center justify-center">
                <iconify-icon icon="lucide:package-check" class="text-2xl text-emerald-600"></iconify-icon>
            </div>
            <div>
                <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Còn hàng</p>
                <p class="text-2xl font-bold text-neutral-800 dark:text-neutral-100">{{ $totalAvailable }}</p>
            </div>
        </div>
        <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-950/30 flex items-center justify-center">
                <iconify-icon icon="lucide:bookmark" class="text-2xl text-amber-500"></iconify-icon>
            </div>
            <div>
                <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Đã đặt</p>
                <p class="text-2xl font-bold text-neutral-800 dark:text-neutral-100">{{ $totalReserved }}</p>
            </div>
        </div>
        <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                <iconify-icon icon="lucide:archive" class="text-2xl text-neutral-500"></iconify-icon>
            </div>
            <div>
                <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Đã dùng</p>
                <p class="text-2xl font-bold text-neutral-800 dark:text-neutral-100">{{ $totalUsed }}</p>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">
        {{-- Card Header --}}
        <div class="card-header border-b border-neutral-200 dark:border-neutral-800 py-4 px-6 flex items-center flex-wrap gap-3 justify-between bg-white dark:bg-neutral-900/50">
            <h5 class="text-lg font-bold text-neutral-800 dark:text-neutral-100 mb-0 flex items-center gap-2">
                <iconify-icon icon="lucide:layers" class="text-orange-500 text-xl"></iconify-icon>
                Tấm ván dư — Kho DC
            </h5>

            <div class="flex items-center flex-wrap gap-2">
                {{-- Search & Filters --}}
                <form method="GET" action="{{ route('dc-stocks.index') }}" class="flex items-center flex-wrap gap-2">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <div class="relative w-44">
                        <span class="absolute top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none" style="left: 10px;">
                            <iconify-icon icon="solar:magnifer-linear" class="text-base"></iconify-icon>
                        </span>
                        <input type="text" name="search"
                            class="w-full pr-3 py-1.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500"
                            style="padding-left: 34px;"
                            placeholder="Mã ván, màu, vị trí..." value="{{ $search }}">
                    </div>

                    {{-- Board Type filter --}}
                    <select name="board_type" onchange="this.form.submit()"
                            class="form-select form-select-sm w-auto border border-neutral-200 dark:border-neutral-700 rounded-lg py-1 px-2 text-xs bg-transparent dark:text-neutral-300">
                        <option value="">-- Tất cả loại ván --</option>
                        @foreach($boardTypes as $bt)
                            <option value="{{ $bt->name }}" {{ ($boardType ?? '') === $bt->name ? 'selected' : '' }}>
                                {{ $bt->name }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Status filter --}}
                    <select name="status" onchange="this.form.submit()"
                            class="form-select form-select-sm w-auto border border-neutral-200 dark:border-neutral-700 rounded-lg py-1 px-2 text-xs bg-transparent dark:text-neutral-300">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="available" {{ $status === 'available' ? 'selected' : '' }}>Còn hàng</option>
                        <option value="reserved"  {{ $status === 'reserved'  ? 'selected' : '' }}>Đã đặt</option>
                        <option value="used"      {{ $status === 'used'      ? 'selected' : '' }}>Đã dùng</option>
                    </select>
                </form>

                {{-- Bộ lọc ngày tháng chung --}}
                <x-date-filter :action="route('dc-stocks.index')" />

                {{-- Per page --}}
                <form method="GET" action="{{ route('dc-stocks.index') }}" id="perPageForm">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="hidden" name="board_type" value="{{ $boardType ?? '' }}">
                    <select name="per_page" class="form-select form-select-sm w-auto border border-neutral-200 dark:border-neutral-700 rounded-lg py-1 px-2 text-xs bg-transparent dark:text-neutral-300"
                        onchange="document.getElementById('perPageForm').submit()">
                        @foreach([15, 25, 50, 100] as $option)
                        <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </form>

                {{-- Export Button --}}
                <a href="{{ route('dc-stocks.export', request()->query()) }}"
                    class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors shadow-sm" title="Tải file Excel danh sách tấm dư">
                    <iconify-icon icon="solar:document-medicine-bold" class="text-base"></iconify-icon>
                    Xuất Excel
                </a>

                @can('add dc stock')
                {{-- Import Button --}}
                <button type="button" onclick="openModal('importDcModal')"
                    class="px-3.5 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors shadow-sm" title="Nhập danh sách từ file Excel">
                    <iconify-icon icon="solar:upload-track-2-bold" class="text-base"></iconify-icon>
                    Nhập Excel
                </button>

                {{-- Nút chuyển đổi chuẩn hóa đúng form theo ghi chú --}}
                <button type="button" onclick="confirmNormalizeTypes()"
                    id="btnNormalizeTypes"
                    class="px-3.5 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors shadow-sm cursor-pointer"
                    title="Tự động chuẩn hóa Loại ván và Mã SKU theo Ghi chú (VD: PVC 1 mặt ➔ Cốt nhựa 1 mặt, MDF 2 mặt ➔ MDF 2 mặt)">
                    <iconify-icon icon="solar:magic-stick-3-bold" class="text-base"></iconify-icon>
                    <span>Chuyển đổi đúng form</span>
                </button>
                @endcan

                {{-- Add Button with shortcut tooltip --}}
                <button type="button" onclick="openAddModal()"
                    class="px-4 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-bold flex items-center gap-2 transition-all shadow-sm active:scale-95"
                    title="Phím tắt: Alt + N">
                    <iconify-icon icon="lucide:plus" class="text-base"></iconify-icon>
                    <span>Thêm tấm dư</span>
                    <kbd class="hidden sm:inline-block px-1 py-0.2 bg-orange-700/60 text-white/90 rounded text-[10px] font-mono font-normal">Alt+N</kbd>
                </button>
            </div>
        </div>

        {{-- Quick Filter Shortcut Pills Bar --}}
        <div class="px-6 py-2.5 bg-neutral-50/80 dark:bg-neutral-800/40 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between flex-wrap gap-2 text-xs">
            <div class="flex items-center flex-wrap gap-1.5">
                <span class="text-neutral-500 dark:text-neutral-400 font-bold uppercase text-[11px] mr-1 flex items-center gap-1">
                    <iconify-icon icon="solar:filter-bold" class="text-orange-500"></iconify-icon> Lọc nhanh:
                </span>

                {{-- Tất cả --}}
                <a href="{{ route('dc-stocks.index', array_merge(request()->except(['board_type', 'status', 'page']))) }}"
                   class="px-2.5 py-1 rounded-lg font-bold transition-all text-xs flex items-center gap-1 {{ empty($boardType) && empty($status) ? 'bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 shadow-sm' : 'bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 border border-neutral-200 dark:border-neutral-700 hover:border-neutral-400' }}">
                   Tất cả
                </a>

                {{-- MDF 1 Mặt --}}
                <a href="{{ route('dc-stocks.index', array_merge(request()->except(['board_type', 'page']), ['board_type' => 'MDF 1 MẶT ACRYLIC'])) }}"
                   class="px-2.5 py-1 rounded-lg font-bold transition-all text-xs flex items-center gap-1 {{ ($boardType ?? '') === 'MDF 1 MẶT ACRYLIC' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white dark:bg-neutral-800 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 hover:bg-emerald-50' }}">
                   🪵 MDF 1 Mặt
                </a>

                {{-- MDF 2 Mặt --}}
                <a href="{{ route('dc-stocks.index', array_merge(request()->except(['board_type', 'page']), ['board_type' => 'MDF 2 MẶT ACRYLIC'])) }}"
                   class="px-2.5 py-1 rounded-lg font-bold transition-all text-xs flex items-center gap-1 {{ ($boardType ?? '') === 'MDF 2 MẶT ACRYLIC' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white dark:bg-neutral-800 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800/60 hover:bg-purple-50' }}">
                   🪵 MDF 2 Mặt
                </a>

                {{-- Cốt Nhựa PVC --}}
                <a href="{{ route('dc-stocks.index', array_merge(request()->except(['board_type', 'page']), ['board_type' => 'CỐT NHỰA PVC'])) }}"
                   class="px-2.5 py-1 rounded-lg font-bold transition-all text-xs flex items-center gap-1 {{ in_array(($boardType ?? ''), ['CỐT NHỰA PVC', 'CỐT NHỰA 1 MẶT ACRYLIC', 'CỐT NHỰA 2 MẶT ACRYLIC', 'PVC']) ? 'bg-amber-600 text-white shadow-sm' : 'bg-white dark:bg-neutral-800 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60 hover:bg-amber-50' }}">
                   🧱 Cốt Nhựa PVC
                </a>

                <span class="text-neutral-300 dark:text-neutral-700 mx-1">|</span>

                {{-- Còn hàng --}}
                <a href="{{ route('dc-stocks.index', array_merge(request()->except(['status', 'page']), ['status' => 'available'])) }}"
                   class="px-2.5 py-1 rounded-lg font-bold transition-all text-xs flex items-center gap-1.5 {{ $status === 'available' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white dark:bg-neutral-800 text-emerald-700 dark:text-emerald-400 border border-neutral-200 dark:border-neutral-700 hover:bg-emerald-50' }}">
                   <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Còn hàng
                </a>

                {{-- Đã đặt --}}
                <a href="{{ route('dc-stocks.index', array_merge(request()->except(['status', 'page']), ['status' => 'reserved'])) }}"
                   class="px-2.5 py-1 rounded-lg font-bold transition-all text-xs flex items-center gap-1.5 {{ $status === 'reserved' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white dark:bg-neutral-800 text-amber-700 dark:text-amber-400 border border-neutral-200 dark:border-neutral-700 hover:bg-amber-50' }}">
                   <span class="w-2 h-2 rounded-full bg-amber-500"></span> Đã đặt
                </a>

                {{-- Đã dùng --}}
                <a href="{{ route('dc-stocks.index', array_merge(request()->except(['status', 'page']), ['status' => 'used'])) }}"
                   class="px-2.5 py-1 rounded-lg font-bold transition-all text-xs flex items-center gap-1.5 {{ $status === 'used' ? 'bg-neutral-700 text-white shadow-sm' : 'bg-white dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-100' }}">
                   <span class="w-2 h-2 rounded-full bg-neutral-400"></span> Đã dùng
                </a>
            </div>

            <div class="text-neutral-400 text-xs hidden lg:flex items-center gap-1.5">
                <iconify-icon icon="solar:keyboard-linear" class="text-sm"></iconify-icon>
                <span>Mẹo: Phím <kbd class="px-1.5 py-0.5 bg-neutral-200 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300 rounded font-mono text-[10px] font-bold">Alt + N</kbd> mở form thêm, click badge trạng thái để đổi nhanh</span>
            </div>
        </div>

        {{-- Table --}}
        <div class="card-body p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm" id="stockTable">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 whitespace-nowrap">
                            <th class="py-3 px-3 font-semibold text-neutral-600 dark:text-neutral-400 w-10 text-center">STT</th>
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">MÃ VÁN / SKU</th>
                            <th class="py-3 px-3 font-semibold text-neutral-600 dark:text-neutral-400">MÃ MÀU</th>
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">LOẠI VÁN (CỐT VÁN)</th>
                            <th class="py-3 px-3 font-semibold text-neutral-600 dark:text-neutral-400 text-center">ĐỘ DÀY</th>
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400 text-right">CAO (mm)</th>
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400 text-right">RỘNG (mm)</th>
                            <th class="py-3 px-3 font-semibold text-neutral-600 dark:text-neutral-400 text-right">DT (m²)</th>
                            <th class="py-3 px-3 font-semibold text-neutral-600 dark:text-neutral-400 text-center">SL</th>
                            <th class="py-3 px-3 font-semibold text-neutral-600 dark:text-neutral-400">VỊ TRÍ</th>
                            <th class="py-3 px-3 font-semibold text-neutral-600 dark:text-neutral-400">TRẠNG THÁI</th>
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">GHI CHÚ</th>
                            <th class="py-3 px-3 font-semibold text-neutral-600 dark:text-neutral-400 text-right">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800" id="stockTableBody">
                        @forelse($stocks as $index => $item)
                        @php
                            $stt = $stocks->firstItem() + $index;
                            $bType = $item->board_type ?: ($item->boardTypeRel?->name ?: 'MDF 1 MẶT ACRYLIC');
                            $typeBadgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/40 dark:text-emerald-400';
                            if (stripos($bType, 'nhựa') !== false || stripos($bType, 'pvc') !== false) {
                                $typeBadgeClass = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/20 dark:border-amber-800/40 dark:text-amber-400';
                            } elseif (stripos($bType, '2 mặt') !== false || stripos($bType, '2M') !== false) {
                                $typeBadgeClass = 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/20 dark:border-purple-800/40 dark:text-purple-400';
                            }
                            $areaM2 = round(($item->height * $item->width) / 1000000, 3);
                        @endphp
                        <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/10 transition-colors" data-id="{{ $item->id }}">
                            <td class="py-3 px-3 text-center text-neutral-400 text-xs">
                                {{ $stt }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-mono font-bold text-neutral-900 dark:text-neutral-100 bg-orange-50 dark:bg-orange-950/30 border border-orange-200 dark:border-orange-800/50 px-2 py-0.5 rounded text-xs block truncate max-w-[140px]" title="{{ $item->board_code ?: $item->color_code }}">
                                    {{ $item->board_code ?: $item->color_code }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-mono font-semibold text-neutral-700 dark:text-neutral-300 text-xs">
                                    {{ $item->color_code }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center gap-1 font-semibold border px-2 py-0.5 rounded text-[11px] {{ $typeBadgeClass }}">
                                    <iconify-icon icon="lucide:layers" class="text-xs"></iconify-icon>
                                    {{ $bType }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center text-xs text-neutral-600 dark:text-neutral-400 font-medium">
                                {{ $item->thickness ?: '17mm' }}
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-neutral-800 dark:text-neutral-200">
                                {{ number_format($item->height) }}
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-neutral-800 dark:text-neutral-200">
                                {{ number_format($item->width) }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-xs text-neutral-500 dark:text-neutral-400">
                                {{ number_format($areaM2, 3, '.', '') }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span class="font-bold text-sm text-neutral-800 dark:text-neutral-200 bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5 rounded">{{ $item->quantity }}</span>
                            </td>
                            <td class="py-3 px-3">
                                @if($item->location)
                                    <span class="font-mono text-xs font-bold px-2 py-0.5 bg-sky-50 dark:bg-sky-950/20 border border-sky-200 dark:border-sky-800/40 text-sky-700 dark:text-sky-400 rounded">
                                        {{ $item->location }}
                                    </span>
                                @else
                                    <span class="text-neutral-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-3" id="statusCell_{{ $item->id }}">
                                <button type="button"
                                        onclick="cycleStockStatus({{ $item->id }}, '{{ $item->status }}')"
                                        class="group inline-flex items-center text-left focus:outline-none"
                                        title="Click để đổi nhanh trạng thái: Còn hàng ➜ Đã đặt ➜ Đã dùng">
                                    @if($item->status === 'available')
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50 group-hover:ring-2 group-hover:ring-emerald-400 transition-all cursor-pointer">
                                            <span class="status-dot status-available"></span> Còn hàng
                                            <iconify-icon icon="lucide:refresh-cw" class="text-[10px] opacity-40 group-hover:opacity-100 transition-opacity"></iconify-icon>
                                        </span>
                                    @elseif($item->status === 'reserved')
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50 group-hover:ring-2 group-hover:ring-amber-400 transition-all cursor-pointer">
                                            <span class="status-dot status-reserved"></span> Đã đặt
                                            <iconify-icon icon="lucide:refresh-cw" class="text-[10px] opacity-40 group-hover:opacity-100 transition-opacity"></iconify-icon>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2 py-0.5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-700 group-hover:ring-2 group-hover:ring-neutral-400 transition-all cursor-pointer">
                                            <span class="status-dot status-used"></span> Đã dùng
                                            <iconify-icon icon="lucide:refresh-cw" class="text-[10px] opacity-40 group-hover:opacity-100 transition-opacity"></iconify-icon>
                                        </span>
                                    @endif
                                </button>
                            </td>
                            <td class="py-3 px-4 text-neutral-500 dark:text-neutral-400 text-xs truncate max-w-xs">
                                {{ $item->note ?: '—' }}
                            </td>
                            <td class="py-3 px-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button"
                                        onclick="duplicateStock({{ json_encode($item) }})"
                                        class="p-1.5 text-neutral-400 hover:text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-950/20 rounded-lg transition-colors"
                                        title="Nhân bản (Tạo tấm tương tự với kích thước mới)">
                                        <iconify-icon icon="solar:copy-bold" class="text-base"></iconify-icon>
                                    </button>
                                    <button type="button"
                                        onclick="openEditModal({{ json_encode($item) }})"
                                        class="p-1.5 text-neutral-400 hover:text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-950/20 rounded-lg transition-colors" title="Chỉnh sửa">
                                        <iconify-icon icon="lucide:edit-2" class="text-base"></iconify-icon>
                                    </button>
                                    <button type="button"
                                        onclick="deleteStock({{ $item->id }})"
                                        class="p-1.5 text-neutral-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-lg transition-colors" title="Xóa">
                                        <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr id="emptyRow">
                            <td colspan="13" class="py-12 text-center text-neutral-400 dark:text-neutral-500">
                                <iconify-icon icon="lucide:package-open" class="text-4xl mb-2 block"></iconify-icon>
                                Không tìm thấy tấm ván dư nào phù hợp.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($stocks instanceof \Illuminate\Pagination\LengthAwarePaginator && $stocks->total() > $perPage)
                <div class="flex items-center justify-between flex-wrap gap-2 mt-6">
                    <span class="text-secondary-light text-sm">
                        Hiển thị {{ $stocks->firstItem() ?? 0 }} đến {{ $stocks->lastItem() ?? 0 }}
                        trong tổng {{ $stocks->total() }} tấm
                    </span>
                    {{ $stocks->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Add / Edit Modal --}}
    <x-modal name="dcStockModal" maxWidth="md">
        <div class="px-5 py-4 border-b border-neutral-100 dark:border-neutral-800 flex justify-between items-center bg-neutral-50 dark:bg-neutral-900/50 rounded-t-xl">
            <span id="modalTitle" class="font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                <iconify-icon icon="lucide:plus-circle" class="text-orange-500 text-lg"></iconify-icon>
                Thêm tấm dư vào Kho DC
            </span>
            <button type="button" onclick="closeModal('dcStockModal')" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 p-1 rounded-lg transition-colors">
                <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form id="dcStockForm">
            <div class="p-6 flex flex-col gap-4 max-h-[80vh] overflow-y-auto">
                <input type="hidden" id="stockId" value="">

                {{-- Thông báo khớp Bảng giá tấm --}}
                <div id="boardMatchAlert" class="hidden p-3 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/40 rounded-xl text-xs text-emerald-800 dark:text-emerald-300 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <iconify-icon icon="solar:check-circle-bold" class="text-base text-emerald-600"></iconify-icon>
                        <span id="boardMatchText">Khớp với Bảng giá tấm</span>
                    </div>
                    <span id="boardMatchPrice" class="font-bold"></span>
                </div>

                {{-- Hàng 1: Mã màu & Nút bấm nhanh Loại ván --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase mb-1.5">
                            Mã màu <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="f_color_code" list="colorCodeList" required autocomplete="off"
                               oninput="onColorCodeOrTypeChange()"
                               class="w-full px-4 py-2.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-orange-500 font-mono font-bold uppercase text-sm"
                               placeholder="VD: GV01, PARC 01...">
                        <datalist id="colorCodeList">
                            @foreach($colorCodes as $code)
                                <option value="{{ $code }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase">
                                Loại ván (Cốt ván) <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[10px] text-neutral-400 font-semibold">Chọn nhanh:</span>
                        </div>
                        @php
                            $typeMdf1 = $boardTypes->first(function($t) {
                                $n = mb_strtolower($t->name);
                                return (str_contains($n, '1') || str_contains($n, '1m')) && !str_contains($n, 'nhựa') && !str_contains($n, 'pvc') && !str_contains($n, 'foil');
                            }) ?? $boardTypes->first();

                            $typeMdf2 = $boardTypes->first(function($t) {
                                $n = mb_strtolower($t->name);
                                return (str_contains($n, '2') || str_contains($n, '2m')) && !str_contains($n, 'nhựa') && !str_contains($n, 'pvc');
                            });

                            $typePvc = $boardTypes->first(function($t) {
                                $n = mb_strtolower($t->name);
                                return str_contains($n, 'nhựa') || str_contains($n, 'pvc');
                            });
                            $defaultTypeId = $typeMdf1?->id ?? ($boardTypes->first()?->id ?? 1);
                        @endphp
                        <div class="grid grid-cols-3 gap-1 mb-1.5">
                            @if($typeMdf1)
                            <button type="button" onclick="selectTypeShortcut({{ $typeMdf1->id }})"
                                    id="btnType_{{ $typeMdf1->id }}"
                                    data-type-id="{{ $typeMdf1->id }}"
                                    class="type-shortcut-btn px-1.5 py-1.5 rounded-lg border text-[11px] font-bold text-center transition-all bg-emerald-600 text-white border-emerald-600 shadow-xs">
                                🪵 MDF 1M
                            </button>
                            @endif
                            @if($typeMdf2)
                            <button type="button" onclick="selectTypeShortcut({{ $typeMdf2->id }})"
                                    id="btnType_{{ $typeMdf2->id }}"
                                    data-type-id="{{ $typeMdf2->id }}"
                                    class="type-shortcut-btn px-1.5 py-1.5 rounded-lg border text-[11px] font-bold text-center transition-all bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border-neutral-300 dark:border-neutral-700 hover:bg-neutral-50">
                                🪵 MDF 2M
                            </button>
                            @endif
                            @if($typePvc)
                            <button type="button" onclick="selectTypeShortcut({{ $typePvc->id }})"
                                    id="btnType_{{ $typePvc->id }}"
                                    data-type-id="{{ $typePvc->id }}"
                                    class="type-shortcut-btn px-1.5 py-1.5 rounded-lg border text-[11px] font-bold text-center transition-all bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border-neutral-300 dark:border-neutral-700 hover:bg-neutral-50">
                                🧱 Nhựa PVC
                            </button>
                            @endif
                        </div>
                        <select id="f_wood_board_type_id" onchange="onTypeSelectChange()"
                                class="w-full px-3 py-1.5 border border-neutral-300 dark:border-neutral-700 rounded-lg bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-orange-500 text-xs font-semibold">
                            @foreach($boardTypes as $bt)
                                <option value="{{ $bt->id }}" data-prefix="{{ $bt->prefix }}" data-name="{{ $bt->name }}" {{ $bt->id == $defaultTypeId ? 'selected' : '' }}>
                                    {{ $bt->name }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" id="f_board_type" value="{{ $typeMdf1?->name ?? 'MDF 1 MẶT ACRYLIC' }}">
                    </div>
                </div>

                {{-- Hàng 2: Mã ván đầy đủ & Độ dày (Tự động từ Bảng giá) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-orange-50/50 dark:bg-neutral-800/40 p-3 rounded-xl border border-orange-200/80 dark:border-neutral-700">
                    <div>
                        <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-300 uppercase mb-1">
                            Mã ván / SKU (tự sinh)
                        </label>
                        <input type="text" id="f_board_code"
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-xs font-mono font-bold uppercase"
                               placeholder="VD: GV01 hoặc GV01.TP.2M">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-300 uppercase mb-1">
                            Độ dày
                        </label>
                        <input type="text" id="f_thickness" value="17mm"
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-xs font-medium"
                               placeholder="17mm">
                    </div>
                </div>

                {{-- Hàng 3: Kích thước Cao & Rộng với các nút mẫu nhanh + Đảo chiều --}}
                <div class="p-3 bg-neutral-50 dark:bg-neutral-800/30 rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-neutral-700 dark:text-neutral-300 uppercase">
                            Kích thước (mm) <span class="text-red-500">*</span>
                        </label>
                        <button type="button" onclick="swapDimensions()"
                                class="px-2 py-0.5 bg-orange-100 hover:bg-orange-200 dark:bg-orange-950/40 dark:hover:bg-orange-900/50 text-orange-800 dark:text-orange-300 rounded text-[11px] font-bold flex items-center gap-1 transition-colors"
                                title="Đổi chỗ Cao và Rộng">
                            <iconify-icon icon="solar:refresh-linear" class="text-xs"></iconify-icon>
                            <span>Đảo Cao ⇄ Rộng</span>
                        </button>
                    </div>

                    {{-- Dải nút kích thước thường gặp --}}
                    <div class="flex items-center flex-wrap gap-1.5 mb-2.5">
                        <span class="text-[10px] text-neutral-400 font-semibold mr-0.5">Mẫu hay dùng:</span>
                        <button type="button" onclick="setSizePreset(2440, 1220)" class="px-2 py-0.5 bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 hover:border-orange-500 hover:text-orange-600 text-neutral-700 dark:text-neutral-200 rounded text-xs font-mono font-medium transition-colors">2440×1220</button>
                        <button type="button" onclick="setSizePreset(2400, 600)" class="px-2 py-0.5 bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 hover:border-orange-500 hover:text-orange-600 text-neutral-700 dark:text-neutral-200 rounded text-xs font-mono font-medium transition-colors">2400×600</button>
                        <button type="button" onclick="setSizePreset(2400, 400)" class="px-2 py-0.5 bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 hover:border-orange-500 hover:text-orange-600 text-neutral-700 dark:text-neutral-200 rounded text-xs font-mono font-medium transition-colors">2400×400</button>
                        <button type="button" onclick="setSizePreset(1200, 600)" class="px-2 py-0.5 bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 hover:border-orange-500 hover:text-orange-600 text-neutral-700 dark:text-neutral-200 rounded text-xs font-mono font-medium transition-colors">1200×600</button>
                        <button type="button" onclick="setSizePreset(800, 400)" class="px-2 py-0.5 bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 hover:border-orange-500 hover:text-orange-600 text-neutral-700 dark:text-neutral-200 rounded text-xs font-mono font-medium transition-colors">800×400</button>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="relative">
                                <input type="number" id="f_height" min="1" required
                                       class="w-full px-4 py-2 pr-12 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-white dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm font-semibold"
                                       placeholder="VD: 2400">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-neutral-400 font-bold pointer-events-none">Cao</span>
                            </div>
                        </div>
                        <div>
                            <div class="relative">
                                <input type="number" id="f_width" min="1" required
                                       class="w-full px-4 py-2 pr-12 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-white dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm font-semibold"
                                       placeholder="VD: 400">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-neutral-400 font-bold pointer-events-none">Rộng</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Hàng 4: SL (với nút nhanh) và Vị trí kho (với gợi ý nhanh) --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase">
                                Số lượng <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-1">
                                <button type="button" onclick="setQty(1)" class="px-1.5 py-0.5 bg-neutral-100 dark:bg-neutral-800 text-[10px] font-bold rounded hover:bg-orange-100">1</button>
                                <button type="button" onclick="setQty(2)" class="px-1.5 py-0.5 bg-neutral-100 dark:bg-neutral-800 text-[10px] font-bold rounded hover:bg-orange-100">2</button>
                                <button type="button" onclick="setQty(5)" class="px-1.5 py-0.5 bg-neutral-100 dark:bg-neutral-800 text-[10px] font-bold rounded hover:bg-orange-100">5</button>
                                <button type="button" onclick="adjustQty(1)" class="px-1.5 py-0.5 bg-orange-100 text-orange-700 text-[10px] font-bold rounded hover:bg-orange-200">+1</button>
                            </div>
                        </div>
                        <input type="number" id="f_quantity" min="1" value="1" required
                               class="w-full px-4 py-2.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm font-bold">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase">
                                Vị trí kho
                            </label>
                            <div class="flex items-center gap-1">
                                <button type="button" onclick="setLocation('D1')" class="px-1.5 py-0.5 bg-sky-50 dark:bg-sky-950/30 text-sky-700 dark:text-sky-300 text-[10px] font-bold rounded hover:bg-sky-100">D1</button>
                                <button type="button" onclick="setLocation('D2')" class="px-1.5 py-0.5 bg-sky-50 dark:bg-sky-950/30 text-sky-700 dark:text-sky-300 text-[10px] font-bold rounded hover:bg-sky-100">D2</button>
                                <button type="button" onclick="setLocation('SO4')" class="px-1.5 py-0.5 bg-sky-50 dark:bg-sky-950/30 text-sky-700 dark:text-sky-300 text-[10px] font-bold rounded hover:bg-sky-100">SO4</button>
                            </div>
                        </div>
                        <input type="text" id="f_location"
                               class="w-full px-4 py-2.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-orange-500 font-mono uppercase text-sm"
                               placeholder="VD: D1.45, SO4...">
                    </div>
                </div>

                {{-- Hàng 5: Trạng thái (Nút segmented 3 màu) & Ghi chú --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase mb-1.5">
                            Trạng thái
                        </label>
                        <div class="grid grid-cols-3 gap-1.5">
                            <button type="button" onclick="setStatusPreset('available')" id="btnStatus_available"
                                    class="px-2 py-2 rounded-xl border text-xs font-bold text-center transition-all bg-emerald-600 text-white border-emerald-600 shadow-xs flex items-center justify-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-white"></span> Còn hàng
                            </button>
                            <button type="button" onclick="setStatusPreset('reserved')" id="btnStatus_reserved"
                                    class="px-2 py-2 rounded-xl border text-xs font-bold text-center transition-all bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 border-neutral-300 dark:border-neutral-700 flex items-center justify-center gap-1 hover:bg-neutral-50">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Đã đặt
                            </button>
                            <button type="button" onclick="setStatusPreset('used')" id="btnStatus_used"
                                    class="px-2 py-2 rounded-xl border text-xs font-bold text-center transition-all bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 border-neutral-300 dark:border-neutral-700 flex items-center justify-center gap-1 hover:bg-neutral-50">
                                <span class="w-1.5 h-1.5 rounded-full bg-neutral-400"></span> Đã dùng
                            </button>
                        </div>
                        <input type="hidden" id="f_status" value="available">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase mb-1.5">Ghi chú phụ</label>
                        <input type="text" id="f_note"
                               class="w-full px-4 py-2 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-orange-500 text-xs"
                               placeholder="VD: Mặt xước nhẹ, còn mới...">
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-neutral-50 dark:bg-neutral-900/50 border-t border-neutral-100 dark:border-neutral-800 flex justify-end gap-3 rounded-b-xl">
                <button type="button" onclick="closeModal('dcStockModal')"
                        class="px-5 py-2.5 border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 rounded-xl font-bold text-sm hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                    Hủy (Esc)
                </button>
                <button type="submit" id="saveBtn"
                        class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold text-sm shadow-sm transition-all flex items-center gap-2 active:scale-95">
                    <iconify-icon icon="lucide:save" class="text-base"></iconify-icon>
                    <span id="saveBtnText">Lưu vào Kho DC</span>
                </button>
            </div>
        </form>
    </x-modal>

    {{-- Import Excel Modal --}}
    <x-modal name="importDcModal" maxWidth="md">
        <div class="px-5 py-4 border-b border-neutral-100 dark:border-neutral-800 flex justify-between items-center bg-neutral-50 dark:bg-neutral-900/50 rounded-t-xl">
            <span class="font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                <iconify-icon icon="solar:upload-track-2-bold" class="text-sky-600 text-lg"></iconify-icon>
                Nhập danh sách tấm dư từ file Excel
            </span>
            <button type="button" onclick="closeModal('importDcModal')" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 p-1 rounded-lg transition-colors">
                <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
            </button>
        </div>

        <form action="{{ route('dc-stocks.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="p-6 flex flex-col gap-4">
                <div class="p-3.5 bg-sky-50 dark:bg-sky-950/20 border border-sky-200 dark:border-sky-800/40 rounded-xl text-xs text-sky-800 dark:text-sky-300 space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <iconify-icon icon="lucide:info" class="text-sm"></iconify-icon> Hướng dẫn file Excel
                    </div>
                    <div>Hệ thống hỗ trợ file Excel (.xlsx, .xls) hoặc CSV. Cột nhận diện: <strong>Mã màu / Mã hàng, Loại ván, Mã ván, Độ dày, Cao, Rộng, Số lượng, Vị trí</strong>.</div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase mb-1.5">Chọn file Excel / CSV <span class="text-red-500">*</span></label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                           class="w-full text-xs text-neutral-600 border border-neutral-300 dark:border-neutral-700 rounded-xl file:mr-4 file:py-2.5 file:px-4 file:rounded-l-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 bg-neutral-50 dark:bg-neutral-800">
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="replace_existing" id="replace_existing" value="1" class="rounded border-neutral-300 text-orange-500 focus:ring-orange-500">
                    <label for="replace_existing" class="text-xs text-neutral-700 dark:text-neutral-300 cursor-pointer select-none">
                        Xóa toàn bộ dữ liệu cũ trong Kho DC trước khi nhập mới
                    </label>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-neutral-100 dark:border-neutral-800 flex justify-end gap-3 bg-neutral-50/50 dark:bg-neutral-900/30 rounded-b-xl">
                <button type="button" onclick="closeModal('importDcModal')" class="px-4 py-2 border border-neutral-300 dark:border-neutral-700 text-neutral-600 dark:text-neutral-400 text-xs font-semibold rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800">
                    Hủy
                </button>
                <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow-sm flex items-center gap-1.5">
                    <iconify-icon icon="solar:upload-track-2-bold" class="text-base"></iconify-icon>
                    Bắt đầu nhập
                </button>
            </div>
        </form>
    </x-modal>

    {{-- Toast Container --}}
    <div id="toastContainer" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>
@endsection

@push('scripts')
<script>
    const CSRF = "{{ csrf_token() }}";

    // ─── Toast ───────────────────────────────────────────
    function showToast(message, type = "success") {
        const toast = document.createElement("div");
        const isDark = document.documentElement.classList.contains("dark");
        if (type === "success") {
            toast.style.cssText = "background:" + (isDark ? "rgba(16,185,129,0.15)" : "#f0fdf4") + ";border-color:" + (isDark ? "rgba(16,185,129,0.3)" : "#bbf7d0") + ";color:" + (isDark ? "#34d399" : "#065f46");
        } else {
            toast.style.cssText = "background:" + (isDark ? "rgba(239,68,68,0.15)" : "#fef2f2") + ";border-color:" + (isDark ? "rgba(239,68,68,0.3)" : "#fecaca") + ";color:" + (isDark ? "#f87171" : "#991b1b");
        }
        toast.className = "flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-semibold transition-all transform translate-y-2 opacity-0 duration-300";
        const icon = type === "success" ? "lucide:check-circle" : "lucide:alert-circle";
        toast.innerHTML = `<iconify-icon icon="${icon}" class="text-lg"></iconify-icon><span>${message}</span>`;
        document.getElementById("toastContainer").appendChild(toast);
        setTimeout(() => toast.classList.remove("translate-y-2","opacity-0"), 10);
        setTimeout(() => { toast.classList.add("opacity-0","translate-y-2"); setTimeout(() => toast.remove(), 300); }, 3500);
    }

    // ─── Auto Lookup Board Info ───────────────────────────
    let lookupTimeout = null;
    function onColorCodeOrTypeChange() {
        clearTimeout(lookupTimeout);
        lookupTimeout = setTimeout(fetchBoardInfo, 250);
    }

    async function fetchBoardInfo() {
        const color = document.getElementById("f_color_code").value.trim().toUpperCase();
        const typeSelect = document.getElementById("f_wood_board_type_id");
        const typeId = typeSelect.value;
        const selectedOpt = typeSelect.selectedOptions[0];
        const prefix = selectedOpt ? (selectedOpt.getAttribute("data-prefix") || "") : "";
        const typeName = selectedOpt ? (selectedOpt.getAttribute("data-name") || "") : "";

        document.getElementById("f_board_type").value = typeName;

        // Cập nhật ngay mã ván dự kiến
        if (color) {
            document.getElementById("f_board_code").value = color + prefix;
        }

        if (!color) {
            document.getElementById("boardMatchAlert").classList.add("hidden");
            return;
        }

        try {
            const url = `/wood-boards/board-info?color_code=${encodeURIComponent(color)}&wood_board_type_id=${typeId || ""}`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.success && (data.board || data.price)) {
                document.getElementById("boardMatchAlert").classList.remove("hidden");
                let matchText = `✓ Tìm thấy trong Bảng giá: ${data.computed_name || color}`;
                if (data.price_group) {
                    matchText += ` (Nhóm: ${data.price_group})`;
                }
                document.getElementById("boardMatchText").textContent = matchText;

                if (data.price && data.price.price_board > 0) {
                    document.getElementById("boardMatchPrice").textContent = new Intl.NumberFormat("vi-VN").format(data.price.price_board) + "đ";
                } else {
                    document.getElementById("boardMatchPrice").textContent = "";
                }

                if (data.computed_code) {
                    document.getElementById("f_board_code").value = data.computed_code;
                }
                if (data.computed_thickness) {
                    document.getElementById("f_thickness").value = data.computed_thickness;
                }
            } else {
                document.getElementById("boardMatchAlert").classList.add("hidden");
            }
        } catch (e) {
            console.error("Lookup error:", e);
        }
    }

    const DEFAULT_TYPE_ID = {{ $defaultTypeId }};

    // ─── Type Shortcuts ──────────────────────────────────
    function selectTypeShortcut(id) {
        const typeSelect = document.getElementById("f_wood_board_type_id");
        if (typeSelect) {
            typeSelect.value = id;
            const opt = typeSelect.selectedOptions[0];
            if (opt) {
                document.getElementById("f_board_type").value = opt.getAttribute("data-name") || "";
            }
        }
        updateTypeButtonsUI(id);
        onColorCodeOrTypeChange();
    }

    function onTypeSelectChange() {
        const typeSelect = document.getElementById("f_wood_board_type_id");
        const val = typeSelect ? typeSelect.value : "";
        const opt = typeSelect ? typeSelect.selectedOptions[0] : null;
        if (opt) {
            document.getElementById("f_board_type").value = opt.getAttribute("data-name") || "";
        }
        updateTypeButtonsUI(val);
        onColorCodeOrTypeChange();
    }

    function updateTypeButtonsUI(activeId) {
        document.querySelectorAll(".type-shortcut-btn").forEach(btn => {
            const tid = btn.getAttribute("data-type-id");
            if (tid && String(tid) === String(activeId)) {
                btn.className = "type-shortcut-btn px-1.5 py-1.5 rounded-lg border text-[11px] font-bold text-center transition-all bg-emerald-600 text-white border-emerald-600 shadow-xs";
            } else {
                btn.className = "type-shortcut-btn px-1.5 py-1.5 rounded-lg border text-[11px] font-bold text-center transition-all bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 border-neutral-300 dark:border-neutral-700 hover:bg-neutral-50";
            }
        });
    }

    // ─── Size Presets & Swap ──────────────────────────────
    function setSizePreset(h, w) {
        const hInput = document.getElementById("f_height");
        const wInput = document.getElementById("f_width");
        hInput.value = h;
        wInput.value = w;
        [hInput, wInput].forEach(el => {
            el.classList.add("ring-2", "ring-orange-400");
            setTimeout(() => el.classList.remove("ring-2", "ring-orange-400"), 300);
        });
    }

    function swapDimensions() {
        const hInput = document.getElementById("f_height");
        const wInput = document.getElementById("f_width");
        const temp = hInput.value;
        hInput.value = wInput.value;
        wInput.value = temp;
        [hInput, wInput].forEach(el => {
            el.classList.add("ring-2", "ring-orange-400");
            setTimeout(() => el.classList.remove("ring-2", "ring-orange-400"), 300);
        });
        showToast("Đã đảo chiều Cao ⇄ Rộng!");
    }

    // ─── Quantity & Location Shortcuts ─────────────────────
    function setQty(val) {
        document.getElementById("f_quantity").value = val;
    }

    function adjustQty(delta) {
        const q = document.getElementById("f_quantity");
        q.value = Math.max(1, (parseInt(q.value) || 0) + delta);
    }

    function setLocation(loc) {
        document.getElementById("f_location").value = loc;
    }

    // ─── Status Presets ───────────────────────────────────
    function setStatusPreset(status) {
        document.getElementById("f_status").value = status;
        ["available", "reserved", "used"].forEach(s => {
            const btn = document.getElementById("btnStatus_" + s);
            if (!btn) return;
            if (s === status) {
                if (s === "available") btn.className = "px-2 py-2 rounded-xl border text-xs font-bold text-center transition-all bg-emerald-600 text-white border-emerald-600 shadow-xs flex items-center justify-center gap-1";
                else if (s === "reserved") btn.className = "px-2 py-2 rounded-xl border text-xs font-bold text-center transition-all bg-amber-600 text-white border-amber-600 shadow-xs flex items-center justify-center gap-1";
                else btn.className = "px-2 py-2 rounded-xl border text-xs font-bold text-center transition-all bg-neutral-700 text-white border-neutral-700 shadow-xs flex items-center justify-center gap-1";
            } else {
                btn.className = "px-2 py-2 rounded-xl border text-xs font-bold text-center transition-all bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 border-neutral-300 dark:border-neutral-700 flex items-center justify-center gap-1 hover:bg-neutral-50";
            }
        });
    }

    // ─── Quick 1-Click Status Cycle on Table ──────────────
    async function cycleStockStatus(id, currentStatus) {
        const order = ["available", "reserved", "used"];
        const currentIndex = order.indexOf(currentStatus);
        const nextStatus = order[(currentIndex + 1) % order.length];

        const cell = document.getElementById("statusCell_" + id);
        if (cell) cell.style.opacity = "0.5";

        try {
            const res = await fetch(`/dc-stocks/${id}/status`, {
                method: "PATCH",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF, "Accept": "application/json" },
                body: JSON.stringify({ status: nextStatus })
            });
            const data = await res.json();
            if (data.success) {
                let badgeHtml = "";
                let label = "";
                if (nextStatus === "available") {
                    label = "Còn hàng";
                    badgeHtml = `
                        <button type="button" onclick="cycleStockStatus(${id}, '${nextStatus}')" class="group inline-flex items-center text-left focus:outline-none" title="Click để đổi nhanh trạng thái: Còn hàng ➜ Đã đặt ➜ Đã dùng">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50 group-hover:ring-2 group-hover:ring-emerald-400 transition-all cursor-pointer">
                                <span class="status-dot status-available"></span> Còn hàng
                                <iconify-icon icon="lucide:refresh-cw" class="text-[10px] opacity-40 group-hover:opacity-100 transition-opacity"></iconify-icon>
                            </span>
                        </button>`;
                } else if (nextStatus === "reserved") {
                    label = "Đã đặt";
                    badgeHtml = `
                        <button type="button" onclick="cycleStockStatus(${id}, '${nextStatus}')" class="group inline-flex items-center text-left focus:outline-none" title="Click để đổi nhanh trạng thái: Còn hàng ➜ Đã đặt ➜ Đã dùng">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50 group-hover:ring-2 group-hover:ring-amber-400 transition-all cursor-pointer">
                                <span class="status-dot status-reserved"></span> Đã đặt
                                <iconify-icon icon="lucide:refresh-cw" class="text-[10px] opacity-40 group-hover:opacity-100 transition-opacity"></iconify-icon>
                            </span>
                        </button>`;
                } else {
                    label = "Đã dùng";
                    badgeHtml = `
                        <button type="button" onclick="cycleStockStatus(${id}, '${nextStatus}')" class="group inline-flex items-center text-left focus:outline-none" title="Click để đổi nhanh trạng thái: Còn hàng ➜ Đã đặt ➜ Đã dùng">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2 py-0.5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-700 group-hover:ring-2 group-hover:ring-neutral-400 transition-all cursor-pointer">
                                <span class="status-dot status-used"></span> Đã dùng
                                <iconify-icon icon="lucide:refresh-cw" class="text-[10px] opacity-40 group-hover:opacity-100 transition-opacity"></iconify-icon>
                            </span>
                        </button>`;
                }
                if (cell) {
                    cell.innerHTML = badgeHtml;
                    cell.style.opacity = "1";
                }
                showToast(`Đã chuyển trạng thái sang: "${label}"`);
            } else {
                if (cell) cell.style.opacity = "1";
                showToast(data.message || "Không thể cập nhật trạng thái!", "error");
            }
        } catch (e) {
            if (cell) cell.style.opacity = "1";
            showToast("Lỗi kết nối máy chủ!", "error");
        }
    }

    // ─── Open Add Modal ───────────────────────────────────
    function openAddModal() {
        document.getElementById("modalTitle").innerHTML = `<iconify-icon icon="lucide:plus-circle" class="text-orange-500 text-lg"></iconify-icon> Thêm tấm dư vào Kho DC`;
        document.getElementById("saveBtnText").textContent = "Lưu vào Kho DC";
        document.getElementById("stockId").value = "";
        document.getElementById("f_color_code").value = "";
        selectTypeShortcut(DEFAULT_TYPE_ID);
        document.getElementById("f_board_code").value = "";
        document.getElementById("f_thickness").value = "17mm";
        document.getElementById("f_note").value = "";
        document.getElementById("f_height").value = "";
        document.getElementById("f_width").value = "";
        document.getElementById("f_quantity").value = 1;
        document.getElementById("f_location").value = "";
        setStatusPreset("available");
        document.getElementById("boardMatchAlert").classList.add("hidden");
        openModal("dcStockModal");
        setTimeout(() => document.getElementById("f_color_code").focus(), 200);
    }

    // ─── Duplicate Stock (Tạo tấm tương tự) ───────────────
    function duplicateStock(item) {
        openAddModal();
        document.getElementById("modalTitle").innerHTML = `<iconify-icon icon="solar:copy-bold" class="text-sky-500 text-lg"></iconify-icon> Nhân bản tấm dư (Tạo tương tự)`;
        document.getElementById("f_color_code").value = item.color_code || "";

        let targetTypeId = item.wood_board_type_id;
        const typeSelect = document.getElementById("f_wood_board_type_id");
        if (!targetTypeId && item.board_type && typeSelect) {
            for (let i = 0; i < typeSelect.options.length; i++) {
                const optName = typeSelect.options[i].getAttribute("data-name") || "";
                if (optName.toLowerCase() === item.board_type.toLowerCase() || optName.includes(item.board_type) || item.board_type.includes(optName)) {
                    targetTypeId = typeSelect.options[i].value;
                    break;
                }
            }
        }
        selectTypeShortcut(targetTypeId || DEFAULT_TYPE_ID);

        document.getElementById("f_board_code").value = item.board_code || item.color_code;
        document.getElementById("f_thickness").value = item.thickness || "17mm";
        document.getElementById("f_height").value = item.height || "";
        document.getElementById("f_width").value = item.width || "";
        document.getElementById("f_quantity").value = 1;
        document.getElementById("f_location").value = item.location || "";
        document.getElementById("f_note").value = item.note || "";
        setStatusPreset("available");

        onColorCodeOrTypeChange();
        showToast("Đã nhân bản thông tin tấm ván, hãy chỉnh sửa kích thước hoặc số lượng rồi lưu!");
    }

    // ─── Open Edit Modal ──────────────────────────────────
    function openEditModal(item) {
        document.getElementById("modalTitle").innerHTML = `<iconify-icon icon="lucide:edit-2" class="text-orange-500 text-lg"></iconify-icon> Chỉnh sửa tấm dư`;
        document.getElementById("saveBtnText").textContent = "Lưu thay đổi";
        document.getElementById("stockId").value = item.id;
        document.getElementById("f_color_code").value = item.color_code;

        let targetTypeId = item.wood_board_type_id;
        const typeSelect = document.getElementById("f_wood_board_type_id");
        if (!targetTypeId && item.board_type && typeSelect) {
            for (let i = 0; i < typeSelect.options.length; i++) {
                const optName = typeSelect.options[i].getAttribute("data-name") || "";
                if (optName.toLowerCase() === item.board_type.toLowerCase() || optName.includes(item.board_type) || item.board_type.includes(optName)) {
                    targetTypeId = typeSelect.options[i].value;
                    break;
                }
            }
        }
        selectTypeShortcut(targetTypeId || DEFAULT_TYPE_ID);

        if (item.board_type) {
            document.getElementById("f_board_type").value = item.board_type;
        }
        document.getElementById("f_board_code").value = item.board_code || item.color_code;
        document.getElementById("f_thickness").value = item.thickness || "17mm";

        document.getElementById("f_note").value = item.note || "";
        document.getElementById("f_height").value = item.height;
        document.getElementById("f_width").value = item.width;
        document.getElementById("f_quantity").value = item.quantity;
        document.getElementById("f_location").value = item.location || "";
        setStatusPreset(item.status || "available");
        document.getElementById("boardMatchAlert").classList.add("hidden");
        openModal("dcStockModal");
    }

    // ─── Submit Form ──────────────────────────────────────
    document.getElementById("dcStockForm").addEventListener("submit", async function(e) {
        e.preventDefault();
        const id = document.getElementById("stockId").value;
        const url = id ? `/dc-stocks/${id}` : "/dc-stocks";
        const method = id ? "PUT" : "POST";
        const btn = document.getElementById("saveBtn");
        const btnText = document.getElementById("saveBtnText");

        const typeSelect = document.getElementById("f_wood_board_type_id");
        const selectedOpt = typeSelect ? typeSelect.selectedOptions[0] : null;
        const typeName = selectedOpt ? (selectedOpt.getAttribute("data-name") || "") : "";

        const payload = {
            color_code         : document.getElementById("f_color_code").value.trim().toUpperCase(),
            wood_board_type_id : typeSelect && typeSelect.value ? parseInt(typeSelect.value) : null,
            board_type         : typeName || document.getElementById("f_board_type").value.trim(),
            board_code         : document.getElementById("f_board_code").value.trim().toUpperCase(),
            thickness          : document.getElementById("f_thickness").value.trim(),
            note               : document.getElementById("f_note").value.trim(),
            height             : parseInt(document.getElementById("f_height").value),
            width              : parseInt(document.getElementById("f_width").value),
            quantity           : parseInt(document.getElementById("f_quantity").value),
            location           : document.getElementById("f_location").value.trim().toUpperCase(),
            status             : document.getElementById("f_status").value,
        };

        if (!payload.color_code || !payload.height || !payload.width || !payload.quantity) {
            showToast("Vui lòng điền đầy đủ thông tin bắt buộc!", "error"); return;
        }

        btn.disabled = true;
        btnText.textContent = "Đang lưu...";

        try {
            const res = await fetch(url, {
                method,
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF, "Accept": "application/json" },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message);
                closeModal("dcStockModal");
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(data.message || "Có lỗi xảy ra!", "error");
            }
        } catch (err) {
            showToast("Không thể kết nối đến máy chủ!", "error");
        } finally {
            btn.disabled = false;
            btnText.textContent = id ? "Lưu thay đổi" : "Lưu vào Kho DC";
        }
    });

    // ─── Delete ───────────────────────────────────────────
    async function deleteStock(id) {
        if (!confirm("Xóa tấm này khỏi Kho DC?")) return;
        try {
            const res = await fetch(`/dc-stocks/${id}`, {
                method: "DELETE",
                headers: { "X-CSRF-TOKEN": CSRF, "Accept": "application/json" }
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message);
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) { row.style.opacity = "0"; row.style.transition = "opacity 0.3s"; setTimeout(() => row.remove(), 300); }
            } else {
                showToast("Xóa thất bại!", "error");
            }
        } catch (err) {
            showToast("Không thể kết nối đến máy chủ!", "error");
        }
    }

    // ─── Keyboard Shortcuts ───────────────────────────────
    document.addEventListener("keydown", function(e) {
        // Alt + N: Mở form thêm tấm dư
        if (e.altKey && (e.key === "n" || e.key === "N")) {
            e.preventDefault();
            openAddModal();
            return;
        }
        // Phím N khi không đang focus ô gõ phím
        if ((e.key === "n" || e.key === "N") && !["INPUT", "TEXTAREA", "SELECT"].includes(document.activeElement.tagName)) {
            const modal = document.querySelector("[name='dcStockModal']");
            if (!modal || modal.classList.contains("hidden")) {
                e.preventDefault();
                openAddModal();
            }
        }
    });

    // ─── Chuẩn hóa loại ván và mã SKU theo ghi chú ──────────
    async function confirmNormalizeTypes() {
        if (!confirm("Hệ thống sẽ quét toàn bộ kho DC và tự động cập nhật LOẠI VÁN cùng MÃ VÁN / SKU chuẩn form dựa trên GHI CHÚ (ví dụ: 'PVC 1 mặt' ➔ Cốt nhựa 1 mặt, 'MDF 2 mặt' ➔ MDF 2 mặt).\n\nBạn có chắc chắn muốn thực hiện?")) {
            return;
        }

        const btn = document.getElementById('btnNormalizeTypes');
        const originalHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<iconify-icon icon="lucide:loader" class="animate-spin text-base"></iconify-icon> <span>Đang xử lý...</span>';
        }

        try {
            const response = await fetch('{{ route("dc-stocks.normalize-types") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json'
                }
            });

            const res = await response.json();
            if (res.success) {
                alert(res.message);
                window.location.reload();
            } else {
                alert('Có lỗi xảy ra: ' + (res.message || 'Không xác định'));
            }
        } catch (e) {
            console.error(e);
            alert('Lỗi kết nối máy chủ: ' + e.message);
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    }
</script>
@endpush
