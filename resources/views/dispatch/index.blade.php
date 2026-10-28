@extends('layout.layout')

@php
    $title = 'Điều vận & Giao nhận';
    $subTitle = 'Quy trình';
@endphp

@section('content')
<style>
    /* ========================================================
       LOGISTICS & DELIVERY PAGE STYLES (THEO STYLE PRESSING)
       ======================================================== */
    .mode-tab-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 14px 18px;
        border-radius: 14px;
        font-weight: 700;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s ease;
        flex: 1;
        text-align: center;
    }
    .mode-tab-btn.active-dispatch {
        background: #eef2ff;
        border-color: #6366f1;
        color: #3730a3;
        box-shadow: 0 4px 12px -2px rgba(99, 102, 241, 0.15);
    }
    .mode-tab-btn.active-delivery {
        background: #ecfdf5;
        border-color: #10b981;
        color: #065f46;
        box-shadow: 0 4px 12px -2px rgba(16, 185, 129, 0.15);
    }
    .dark .mode-tab-btn.active-dispatch {
        background: rgba(99, 102, 241, 0.15);
        border-color: #818cf8;
        color: #c7d2fe;
    }
    .dark .mode-tab-btn.active-delivery {
        background: rgba(16, 185, 129, 0.15);
        border-color: #34d399;
        color: #a7f3d0;
    }
    .mode-tab-btn:not(.active-dispatch):not(.active-delivery) {
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #64748b;
    }
    .dark .mode-tab-btn:not(.active-dispatch):not(.active-delivery) {
        background: #1e293b;
        border-color: #334155;
        color: #94a3b8;
    }
    .mode-tab-btn:hover:not(.active-dispatch):not(.active-delivery) {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }
    .dark .mode-tab-btn:hover:not(.active-dispatch):not(.active-delivery) {
        background: #243044;
        border-color: #475569;
    }

    .logistics-subtab-btn {
        padding: 10px 18px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .logistics-subtab-btn.active {
        background: #1e293b;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    }
    .logistics-subtab-btn:not(.active) {
        background: #f1f5f9;
        color: #64748b;
    }
    .logistics-subtab-btn:not(.active):hover {
        background: #e2e8f0;
        color: #1e293b;
    }
</style>

<div class="-mt-4 mb-6">
    <p class="text-sm text-neutral-500 dark:text-neutral-400">
        Kiểm soát quy trình Điều vận & Giao nhận: <strong>Giao hàng</strong> (Xuất xưởng lên xe) và <strong>Nhận hàng</strong> (Khách nhận đủ, tự động hoàn tất đơn).
    </p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    {{-- Left column: Control Card (col-span-8) --}}
    <div class="lg:col-span-8 flex flex-col gap-6">
        <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm">
            <div class="card-body p-4 sm:p-6 flex flex-col gap-6">

                {{-- Header line --}}
                <div class="flex items-center justify-between pb-4 border-b border-neutral-100 dark:border-neutral-800">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shrink-0">
                            <iconify-icon icon="lucide:arrow-left-right"></iconify-icon>
                        </span>
                        <div>
                            <h4 class="text-lg font-bold text-neutral-900 dark:text-neutral-100 mb-0">Điều Vận & Giao Nhận</h4>
                            <p class="text-xs text-neutral-500 mb-0">Quét mã QR / Barcode tem kiện hoặc linh kiện</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        <iconify-icon icon="lucide:zap" class="text-emerald-600 dark:text-emerald-400"></iconify-icon>
                        Tự động đổi trạng thái đơn
                    </span>
                </div>

                {{-- Mode Selection Toggle (2 Nút To chuẩn Pressing Style) --}}
                <div>
                    <label class="block text-xs font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider mb-3">
                        Chọn chế độ thao tác
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Mode 1: GIAO HÀNG (Xuất xưởng lên xe) --}}
                        <button type="button" id="btnModeDispatch" onclick="switchMainMode('dispatch')"
                            class="mode-tab-btn {{ $activeMode === 'dispatch' ? 'active-dispatch' : '' }}">
                            <div class="flex items-center gap-2 text-base">
                                <iconify-icon icon="lucide:truck" class="text-2xl"></iconify-icon>
                                <span>1. GIAO HÀNG (Xuất xưởng)</span>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300">
                                Bốc kiện lên xe vận chuyển
                            </span>
                        </button>

                        {{-- Mode 2: NHẬN HÀNG (Bàn giao khách) --}}
                        <button type="button" id="btnModeDelivery" onclick="switchMainMode('delivery')"
                            class="mode-tab-btn {{ $activeMode === 'delivery' ? 'active-delivery' : '' }}">
                            <div class="flex items-center gap-2 text-base">
                                <iconify-icon icon="lucide:check-circle-2" class="text-2xl"></iconify-icon>
                                <span>2. NHẬN HÀNG (Bàn giao khách)</span>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300">
                                Khách nhận đủ → Hoàn thành đơn
                            </span>
                        </button>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- SECTION 1: GIAO HÀNG (XUẤT XƯỞNG LÊN XE)                 --}}
                {{-- ======================================================== --}}
                <div id="sectionDispatchMode" class="{{ $activeMode === 'dispatch' ? 'flex' : 'hidden' }} flex-col gap-5">
                    <div class="p-4 bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-800/50 rounded-xl text-xs text-indigo-900 dark:text-indigo-200 flex items-start gap-3">
                        <iconify-icon icon="lucide:info" class="text-lg text-indigo-600 dark:text-indigo-400 mt-0.5 flex-shrink-0"></iconify-icon>
                        <div>
                            <strong>Chế độ GIAO HÀNG (Xuất xưởng):</strong> Quét mã tem kiện đóng gói (hoặc mã 1 tấm bất kỳ). Chọn xe vận chuyển và xác nhận xuất xưởng để kiện rời xưởng lên đường giao.
                        </div>
                    </div>

                    {{-- QR scan input --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="dispatch_qr_input" class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                                Mã kiện / Mã tem tấm (QR / Barcode)
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-neutral-600">
                                <input type="checkbox" id="dispatch_auto_confirm" class="rounded border-neutral-300 text-indigo-600 focus:ring-indigo-500" checked>
                                <span>Quét tự động xuất ngay</span>
                            </label>
                        </div>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <div class="relative flex-grow">
                                <input type="text" id="dispatch_qr_input" autofocus
                                    class="w-full pl-4 pr-10 py-3.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-base font-mono font-bold"
                                    placeholder="Quét hoặc nhập mã kiện (VD: 261028001, PK1)...">
                                <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none">
                                    <iconify-icon icon="lucide:qr-code" class="text-xl"></iconify-icon>
                                </span>
                            </div>
                            <button type="button" onclick="startCameraScanner('dispatch')"
                                class="px-5 py-3.5 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-xl flex items-center justify-center gap-2 font-bold text-sm transition-all whitespace-nowrap shadow-md flex-shrink-0">
                                <iconify-icon icon="lucide:camera" class="text-xl"></iconify-icon>
                                <span>Quét Camera</span>
                            </button>
                        </div>
                    </div>

                    {{-- Vehicle & Notes --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="dispatch_vehicle_id" class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">
                                Xe vận chuyển / Tài xế
                            </label>
                            <select id="dispatch_vehicle_id" class="form-select w-full rounded-xl border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-sm py-2.5">
                                <option value="">-- Chọn xe giao hàng (Tùy chọn) --</option>
                                @foreach($vehicles as $veh)
                                    <option value="{{ $veh->id }}">{{ $veh->plate_number }} - {{ $veh->name }} ({{ $veh->driver_name ?: 'Chưa có lái xe' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="dispatch_notes" class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">
                                Ghi chú xuất hàng
                            </label>
                            <input type="text" id="dispatch_notes"
                                class="w-full px-4 py-2.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-sm"
                                placeholder="Ghi chú người bốc hàng, số lượng...">
                        </div>
                    </div>

                    {{-- Preview Box for Dispatch --}}
                    <div id="dispatchPreviewBox" class="hidden p-4 rounded-xl border border-indigo-200 bg-indigo-50/50 dark:bg-indigo-950/20 flex-col gap-4">
                        <div class="flex items-center justify-between border-b border-indigo-100 pb-3">
                            <div>
                                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Thông tin kiện tìm thấy</span>
                                <h5 id="dispatchPreviewTitle" class="text-base font-bold text-neutral-900 mb-0">Mã kiện: -</h5>
                            </div>
                            <span id="dispatchPreviewStatus" class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Chờ xuất</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                            <div><strong>Mã đơn:</strong> <span id="dispatchPreviewOrder">-</span></div>
                            <div><strong>Khách hàng:</strong> <span id="dispatchPreviewCustomer">-</span></div>
                            <div><strong>Tổng số tấm:</strong> <span id="dispatchPreviewTotalItems">-</span></div>
                            <div class="col-span-2 sm:col-span-3"><strong>Địa chỉ:</strong> <span id="dispatchPreviewAddress">-</span></div>
                        </div>
                        <button type="button" id="btnConfirmDispatch" onclick="confirmDispatchAction()"
                            class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all text-sm">
                            <iconify-icon icon="lucide:check-circle-2" class="text-lg"></iconify-icon>
                            <span>Xác Nhận Xuất Xưởng Lên Xe</span>
                        </button>
                    </div>
                </div>

                {{-- ======================================================== --}}
                {{-- SECTION 2: NHẬN HÀNG (BÀN GIAO KHÁCH - HOÀN TẤT ĐƠN)      --}}
                {{-- ======================================================== --}}
                <div id="sectionDeliveryMode" class="{{ $activeMode === 'delivery' ? 'flex' : 'hidden' }} flex-col gap-5">
                    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/50 rounded-xl text-xs text-emerald-900 dark:text-emerald-200 flex items-start gap-3">
                        <iconify-icon icon="lucide:info" class="text-lg text-emerald-600 dark:text-emerald-400 mt-0.5 flex-shrink-0"></iconify-icon>
                        <div>
                            <strong>Chế độ NHẬN HÀNG (Bàn giao khách):</strong> Quét mã kiện đã xuất xưởng để xác nhận giao hàng thành công đến tay khách/công trình. Khi <strong>tất cả các kiện trong đơn đã giao đủ</strong>, hệ thống sẽ <strong>tự động đổi trạng thái đơn hàng sang Hoàn thành</strong>!
                        </div>
                    </div>

                    {{-- QR scan input --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="delivery_qr_input" class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">
                                Mã kiện cần xác nhận giao (QR / Barcode)
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-neutral-600">
                                <input type="checkbox" id="delivery_auto_confirm" class="rounded border-neutral-300 text-emerald-600 focus:ring-emerald-500" checked>
                                <span>Quét tự động xác nhận giao ngay</span>
                            </label>
                        </div>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <div class="relative flex-grow">
                                <input type="text" id="delivery_qr_input"
                                    class="w-full pl-4 pr-10 py-3.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-base font-mono font-bold"
                                    placeholder="Quét hoặc nhập mã kiện đã xuất...">
                                <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none">
                                    <iconify-icon icon="lucide:qr-code" class="text-xl"></iconify-icon>
                                </span>
                            </div>
                            <button type="button" onclick="startCameraScanner('delivery')"
                                class="px-5 py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl flex items-center justify-center gap-2 font-bold text-sm transition-all whitespace-nowrap shadow-md flex-shrink-0">
                                <iconify-icon icon="lucide:camera" class="text-xl"></iconify-icon>
                                <span>Quét Camera</span>
                            </button>
                        </div>
                    </div>

                    {{-- Delivery Notes --}}
                    <div>
                        <label for="delivery_notes" class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1.5">
                            Ghi chú giao nhận (Người nhận ký, tình trạng hàng...)
                        </label>
                        <input type="text" id="delivery_notes"
                            class="w-full px-4 py-2.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-sm"
                            placeholder="Ví dụ: Khách đã ký nhận nguyên vẹn, đủ hàng...">
                    </div>

                    {{-- Preview Box for Delivery --}}
                    <div id="deliveryPreviewBox" class="hidden p-4 rounded-xl border border-emerald-200 bg-emerald-50/50 dark:bg-emerald-950/20 flex-col gap-4">
                        <div class="flex items-center justify-between border-b border-emerald-100 pb-3">
                            <div>
                                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Thông tin kiện giao nhận</span>
                                <h5 id="deliveryPreviewTitle" class="text-base font-bold text-neutral-900 mb-0">Mã kiện: -</h5>
                            </div>
                            <span id="deliveryPreviewStatus" class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">Đang đi giao</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                            <div><strong>Mã đơn:</strong> <span id="deliveryPreviewOrder">-</span></div>
                            <div><strong>Khách hàng:</strong> <span id="deliveryPreviewCustomer">-</span></div>
                            <div><strong>Số điện thoại:</strong> <span id="deliveryPreviewPhone">-</span></div>
                            <div class="col-span-2 sm:col-span-3"><strong>Địa chỉ giao:</strong> <span id="deliveryPreviewAddress">-</span></div>
                        </div>
                        <button type="button" id="btnConfirmDelivery" onclick="confirmDeliveryAction()"
                            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all text-sm">
                            <iconify-icon icon="lucide:check-circle-2" class="text-lg"></iconify-icon>
                            <span>Xác Nhận Khách Đã Nhận Hàng Thành Công</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Right column: Stats & Guide Cards (col-span-4) --}}
    <div class="lg:col-span-4 flex flex-col gap-4">
        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="p-4 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-lg">
                        <iconify-icon icon="lucide:truck"></iconify-icon>
                    </span>
                    <span class="text-[10px] uppercase font-bold tracking-wider opacity-80">Hôm nay</span>
                </div>
                <div>
                    <div id="statTodayDispatch" class="text-2xl font-black">{{ $todayDispatchCount }}</div>
                    <div class="text-xs text-indigo-100 font-semibold">Kiện đã xuất xưởng</div>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-lg">
                        <iconify-icon icon="lucide:check-circle-2"></iconify-icon>
                    </span>
                    <span class="text-[10px] uppercase font-bold tracking-wider opacity-80">Hôm nay</span>
                </div>
                <div>
                    <div id="statTodayDelivery" class="text-2xl font-black">{{ $todayDeliveryCount }}</div>
                    <div class="text-xs text-emerald-100 font-semibold">Kiện đã giao xong</div>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-amber-500 text-white shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-lg">
                        <iconify-icon icon="lucide:package"></iconify-icon>
                    </span>
                    <span class="text-[10px] uppercase font-bold tracking-wider opacity-80">Chờ xuất</span>
                </div>
                <div>
                    <div id="statPendingDispatch" class="text-2xl font-black">{{ $pendingDispatchCount }}</div>
                    <div class="text-xs text-amber-100 font-semibold">Kiện chờ lên xe</div>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-blue-600 text-white shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2">
                    <span class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-lg">
                        <iconify-icon icon="lucide:navigation"></iconify-icon>
                    </span>
                    <span class="text-[10px] uppercase font-bold tracking-wider opacity-80">Đang giao</span>
                </div>
                <div>
                    <div id="statPendingDelivery" class="text-2xl font-black">{{ $pendingDeliveryCount }}</div>
                    <div class="text-xs text-blue-100 font-semibold">Kiện trên đường</div>
                </div>
            </div>
        </div>

        {{-- Guide Card (Màu tím Indigo chuẩn Pressing) --}}
        <div class="text-white rounded-2xl p-6 shadow-md flex flex-col justify-between flex-grow" style="background-color: rgb(79, 70, 229);">
            <div>
                <h5 class="text-base font-bold mb-4 flex items-center gap-2 text-white">
                    <iconify-icon icon="lucide:help-circle" class="text-lg"></iconify-icon>
                    Quy trình Giao & Nhận
                </h5>
                <ol class="space-y-3 text-xs text-indigo-100 list-decimal list-inside pl-1 leading-relaxed">
                    <li>
                        <strong>Bước 1 - Giao hàng:</strong><br>
                        <span class="text-[11px] text-indigo-200">Quét mã kiện khi bốc hàng lên xe tải $\rightarrow$ Xác nhận xuất xưởng rời kho.</span>
                    </li>
                    <li>
                        <strong>Bước 2 - Nhận hàng:</strong><br>
                        <span class="text-[11px] text-indigo-200">Quét mã kiện khi bàn giao tại công trình/nhà khách $\rightarrow$ Xác nhận khách đã nhận.</span>
                    </li>
                    <li>
                        <strong>Tự động hoàn tất:</strong><br>
                        <span class="text-[11px] text-indigo-200">Khi toàn bộ các kiện của đơn hàng đã được giao đủ $\rightarrow$ <strong>Hệ thống tự động đổi đơn sang Hoàn thành!</strong></span>
                    </li>
                </ol>
            </div>
            <div class="pt-4 mt-4 border-t border-indigo-400/30 text-[11px] text-indigo-200 flex items-center gap-2">
                <iconify-icon icon="lucide:shield-check" class="text-sm"></iconify-icon>
                <span>Đồng bộ tức thời với dữ liệu kho & đơn hàng</span>
            </div>
        </div>
    </div>
</div>

{{-- ======================================================== --}}
{{-- BOTTOM SECTION: 3 SUB-TABS (DANH SÁCH & LỊCH SỬ)         --}}
{{-- ======================================================== --}}
<div class="mt-8 card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">
    {{-- Sub-tabs header --}}
    <div class="p-4 sm:px-6 sm:py-4 border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800/50 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <button type="button" onclick="switchListTab('pending-dispatch')" id="btnSubTabPendingDispatch"
                class="logistics-subtab-btn {{ $activeMode === 'dispatch' ? 'active' : '' }}">
                <iconify-icon icon="lucide:package"></iconify-icon>
                <span>Kiện chờ xuất xưởng ({{ $pendingDispatchCount }})</span>
            </button>
            <button type="button" onclick="switchListTab('pending-delivery')" id="btnSubTabPendingDelivery"
                class="logistics-subtab-btn {{ $activeMode === 'delivery' ? 'active' : '' }}">
                <iconify-icon icon="lucide:navigation"></iconify-icon>
                <span>Kiện đang đi giao ({{ $pendingDeliveryCount }})</span>
            </button>
            <button type="button" onclick="switchListTab('history')" id="btnSubTabHistory"
                class="logistics-subtab-btn">
                <iconify-icon icon="lucide:history"></iconify-icon>
                <span>Lịch sử thao tác</span>
            </button>
        </div>

        {{-- Bulk actions --}}
        <div id="bulkDispatchActions" class="{{ $activeMode === 'dispatch' ? 'flex' : 'hidden' }} items-center gap-2">
            <button type="button" onclick="confirmBulkDispatch()" class="btn btn-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg px-3 py-1.5 text-xs font-bold flex items-center gap-1.5">
                <iconify-icon icon="lucide:truck"></iconify-icon>
                <span>Xuất xưởng đã chọn (<span id="selectedDispatchCount">0</span>)</span>
            </button>
        </div>
        <div id="bulkDeliveryActions" class="{{ $activeMode === 'delivery' ? 'flex' : 'hidden' }} items-center gap-2">
            <button type="button" onclick="confirmBulkDelivery()" class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg px-3 py-1.5 text-xs font-bold flex items-center gap-1.5">
                <iconify-icon icon="lucide:check-circle-2"></iconify-icon>
                <span>Xác nhận giao đã chọn (<span id="selectedDeliveryCount">0</span>)</span>
            </button>
        </div>
    </div>

    {{-- TAB 1: Danh sách kiện chờ xuất xưởng --}}
    <div id="listTabPendingDispatch" class="{{ $activeMode === 'dispatch' ? 'block' : 'hidden' }}">
        <div class="overflow-x-auto">
            <table class="table w-full text-xs text-left">
                <thead class="bg-neutral-100 text-neutral-600 uppercase font-bold text-[11px]">
                    <tr>
                        <th class="p-3 w-10 text-center"><input type="checkbox" id="checkAllDispatch" onchange="toggleSelectAllDispatch(this)" class="rounded"></th>
                        <th class="p-3">Mã kiện</th>
                        <th class="p-3">Mã đơn</th>
                        <th class="p-3">Khách hàng</th>
                        <th class="p-3">Địa chỉ giao</th>
                        <th class="p-3 text-center">Số tấm</th>
                        <th class="p-3">Người đóng</th>
                        <th class="p-3">Giờ đóng</th>
                        <th class="p-3 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($pendingDispatchRows as $row)
                    <tr class="hover:bg-neutral-50">
                        <td class="p-3 text-center">
                            <input type="checkbox" class="dispatch-checkbox rounded" value="{{ $row['package_id'] }}" onchange="updateDispatchSelectedCount()">
                        </td>
                        <td class="p-3 font-mono font-bold text-indigo-600">{{ $row['package_code'] }}</td>
                        <td class="p-3 font-semibold">{{ $row['order_code'] }}</td>
                        <td class="p-3 font-semibold">{{ $row['customer_name'] }}</td>
                        <td class="p-3 max-w-[220px] truncate" title="{{ $row['delivery_address'] }}">{{ $row['delivery_address'] }}</td>
                        <td class="p-3 text-center"><span class="px-2 py-0.5 rounded bg-neutral-100 font-bold">{{ $row['total_items'] }}</span></td>
                        <td class="p-3">{{ $row['operator'] }}</td>
                        <td class="p-3 text-neutral-500">{{ $row['packed_time'] }}</td>
                        <td class="p-3 text-center">
                            <button type="button" onclick="quickSelectPackage('dispatch', '{{ $row['package_code'] }}')" class="btn btn-xs bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded px-2.5 py-1 font-bold">
                                Xuất xưởng
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center text-neutral-400">Không có kiện nào đang chờ xuất xưởng.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TAB 2: Danh sách kiện đang đi giao --}}
    <div id="listTabPendingDelivery" class="{{ $activeMode === 'delivery' ? 'block' : 'hidden' }}">
        <div class="overflow-x-auto">
            <table class="table w-full text-xs text-left">
                <thead class="bg-neutral-100 text-neutral-600 uppercase font-bold text-[11px]">
                    <tr>
                        <th class="p-3 w-10 text-center"><input type="checkbox" id="checkAllDelivery" onchange="toggleSelectAllDelivery(this)" class="rounded"></th>
                        <th class="p-3">Mã kiện</th>
                        <th class="p-3">Mã đơn</th>
                        <th class="p-3">Khách hàng</th>
                        <th class="p-3">SĐT người nhận</th>
                        <th class="p-3">Địa chỉ công trình</th>
                        <th class="p-3">Xe vận chuyển</th>
                        <th class="p-3">Giờ xuất xưởng</th>
                        <th class="p-3 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($pendingDeliveryRows as $row)
                    <tr class="hover:bg-neutral-50">
                        <td class="p-3 text-center">
                            <input type="checkbox" class="delivery-checkbox rounded" value="{{ $row['package_id'] }}" onchange="updateDeliverySelectedCount()">
                        </td>
                        <td class="p-3 font-mono font-bold text-emerald-600">{{ $row['package_code'] }}</td>
                        <td class="p-3 font-semibold">{{ $row['order_code'] }}</td>
                        <td class="p-3 font-semibold">{{ $row['customer_name'] }}</td>
                        <td class="p-3">{{ $row['customer_phone'] }}</td>
                        <td class="p-3 max-w-[220px] truncate" title="{{ $row['delivery_address'] }}">{{ $row['delivery_address'] }}</td>
                        <td class="p-3 text-neutral-600">{{ $row['vehicle_info'] }}</td>
                        <td class="p-3 text-neutral-500">{{ $row['dispatched_time'] }}</td>
                        <td class="p-3 text-center">
                            <button type="button" onclick="quickSelectPackage('delivery', '{{ $row['package_code'] }}')" class="btn btn-xs bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded px-2.5 py-1 font-bold">
                                Xác nhận giao
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center text-neutral-400">Không có kiện nào đang trên đường đi giao.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TAB 3: Lịch sử thao tác --}}
    <div id="listTabHistory" class="hidden">
        <div class="p-4 border-b border-neutral-100 flex flex-wrap items-center justify-between gap-3">
            <form method="GET" action="{{ route('processes.dispatch') }}" class="flex items-center gap-2">
                <input type="hidden" name="mode" value="{{ $activeMode }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Tìm theo mã kiện, mã đơn, khách..." class="form-control text-xs py-1.5 px-3 rounded-lg w-64">
                <button type="submit" class="btn btn-sm bg-neutral-800 text-white rounded-lg px-3 py-1.5 text-xs font-semibold">Tìm kiếm</button>
            </form>
            <x-date-filter :action="route('processes.dispatch')" />
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full text-xs text-left">
                <thead class="bg-neutral-100 text-neutral-600 uppercase font-bold text-[11px]">
                    <tr>
                        <th class="p-3">Mã kiện</th>
                        <th class="p-3">Mã đơn</th>
                        <th class="p-3">Khách hàng</th>
                        <th class="p-3 text-center">Số tấm</th>
                        <th class="p-3">Xe chở</th>
                        <th class="p-3">Giờ xuất</th>
                        <th class="p-3">Giờ giao xong</th>
                        <th class="p-3">Ghi chú</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($historyRows as $row)
                    <tr class="hover:bg-neutral-50">
                        <td class="p-3 font-mono font-bold text-neutral-800">{{ $row['package_code'] }}</td>
                        <td class="p-3 font-semibold">{{ $row['order_code'] }}</td>
                        <td class="p-3">{{ $row['customer_name'] }}</td>
                        <td class="p-3 text-center font-bold">{{ $row['total_items'] }}</td>
                        <td class="p-3">{{ $row['vehicle_plate'] }}</td>
                        <td class="p-3 text-neutral-500">{{ $row['dispatched_time'] }}</td>
                        <td class="p-3 text-neutral-500">{{ $row['delivered_time'] }}</td>
                        <td class="p-3 text-neutral-600 max-w-[200px] truncate" title="{{ $row['notes'] }}">{{ $row['notes'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-neutral-400">Chưa có lịch sử trong khoảng thời gian này.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($historyRows->hasPages())
        <div class="p-4 border-t border-neutral-100">
            {{ $historyRows->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ======================================================== --}}
{{-- CAMERA SCANNER MODAL (HTML5QRCODE)                       --}}
{{-- ======================================================== --}}
<div id="scannerModal" class="fixed inset-0 z-50 bg-black/70 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-neutral-900 rounded-2xl max-w-md w-full overflow-hidden shadow-2xl flex flex-col" style="display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0; margin: 0;">
        <div class="p-4 border-b border-neutral-200 flex items-center justify-between">
            <h5 class="text-base font-bold text-neutral-900 dark:text-neutral-100 mb-0 flex items-center gap-2">
                <iconify-icon icon="lucide:camera" class="text-indigo-600"></iconify-icon>
                <span>Quét mã camera</span>
            </h5>
            <button type="button" onclick="stopCameraScanner()" class="text-neutral-400 hover:text-neutral-700 text-xl leading-none">
                <iconify-icon icon="lucide:x"></iconify-icon>
            </button>
        </div>
        <div class="p-4 flex-grow flex items-center justify-center bg-black/5" style="flex: 1 1 auto; min-height: 0; overflow-y: auto;">
            <div id="reader" style="width: 100%; min-height: 280px;"></div>
        </div>
        <div class="p-4 border-t border-neutral-200 bg-neutral-50 flex justify-end" style="flex-shrink: 0;">
            <button type="button" onclick="stopCameraScanner()" class="btn btn-sm bg-neutral-200 hover:bg-neutral-300 text-neutral-700 font-bold px-4 py-2 rounded-lg">
                Đóng
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    let currentMainMode = @json($activeMode);
    let cameraMode = 'dispatch';
    let html5QrScanner = null;
    let currentScannedPackageId = null;

    // API URLs
    const dispatchCheckUrl = @json(route('processes.dispatch.check'));
    const dispatchConfirmUrl = @json(route('processes.dispatch.confirm'));
    const dispatchBulkConfirmUrl = @json(route('processes.dispatch.bulk-confirm'));

    const deliveryCheckUrl = @json(route('processes.delivery.check'));
    const deliveryConfirmUrl = @json(route('processes.delivery.confirm'));
    const deliveryBulkConfirmUrl = @json(route('processes.delivery.bulk-confirm'));

    function playBeepSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 880;
            gain.gain.value = 0.15;
            osc.start();
            osc.stop(ctx.currentTime + 0.15);
        } catch (e) {}
    }

    // 1. Chuyển đổi giữa 2 chế độ chính: Giao & Nhận
    function switchMainMode(mode) {
        currentMainMode = mode;
        const btnDispatch = document.getElementById('btnModeDispatch');
        const btnDelivery = document.getElementById('btnModeDelivery');
        const secDispatch = document.getElementById('sectionDispatchMode');
        const secDelivery = document.getElementById('sectionDeliveryMode');

        if (mode === 'dispatch') {
            btnDispatch.classList.add('active-dispatch');
            btnDelivery.classList.remove('active-delivery');
            secDispatch.classList.remove('hidden');
            secDispatch.classList.add('flex');
            secDelivery.classList.add('hidden');
            secDelivery.classList.remove('flex');

            switchListTab('pending-dispatch');
            document.getElementById('dispatch_qr_input').focus();
        } else {
            btnDelivery.classList.add('active-delivery');
            btnDispatch.classList.remove('active-dispatch');
            secDelivery.classList.remove('hidden');
            secDelivery.classList.add('flex');
            secDispatch.classList.add('hidden');
            secDispatch.classList.remove('flex');

            switchListTab('pending-delivery');
            document.getElementById('delivery_qr_input').focus();
        }
    }

    // 2. Chuyển đổi sub-tab danh sách bên dưới
    function switchListTab(tabName) {
        const tabs = ['pending-dispatch', 'pending-delivery', 'history'];
        tabs.forEach(t => {
            const el = document.getElementById(t === 'pending-dispatch' ? 'listTabPendingDispatch' : (t === 'pending-delivery' ? 'listTabPendingDelivery' : 'listTabHistory'));
            const btn = document.getElementById(t === 'pending-dispatch' ? 'btnSubTabPendingDispatch' : (t === 'pending-delivery' ? 'btnSubTabPendingDelivery' : 'btnSubTabHistory'));
            if (t === tabName) {
                el.classList.remove('hidden');
                btn.classList.add('active');
            } else {
                el.classList.add('hidden');
                btn.classList.remove('active');
            }
        });

        // Toggle bulk action buttons
        const bulkDispatch = document.getElementById('bulkDispatchActions');
        const bulkDelivery = document.getElementById('bulkDeliveryActions');
        if (tabName === 'pending-dispatch') {
            bulkDispatch.classList.remove('hidden');
            bulkDispatch.classList.add('flex');
            bulkDelivery.classList.add('hidden');
        } else if (tabName === 'pending-delivery') {
            bulkDelivery.classList.remove('hidden');
            bulkDelivery.classList.add('flex');
            bulkDispatch.classList.add('hidden');
        } else {
            bulkDispatch.classList.add('hidden');
            bulkDelivery.classList.add('hidden');
        }
    }

    // 3. Xử lý quét mã kiểm tra (Preview)
    document.addEventListener('DOMContentLoaded', () => {
        const inpDispatch = document.getElementById('dispatch_qr_input');
        const inpDelivery = document.getElementById('delivery_qr_input');

        if (inpDispatch) {
            inpDispatch.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    triggerScanCheck('dispatch', inpDispatch.value.trim());
                }
            });
        }

        if (inpDelivery) {
            inpDelivery.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    triggerScanCheck('delivery', inpDelivery.value.trim());
                }
            });
        }
    });

    async function triggerScanCheck(mode, code) {
        if (!code) return;
        playBeepSound();

        const isAutoConfirm = mode === 'dispatch' 
            ? document.getElementById('dispatch_auto_confirm').checked 
            : document.getElementById('delivery_auto_confirm').checked;

        const url = mode === 'dispatch' ? dispatchCheckUrl : deliveryCheckUrl;
        const payload = {
            product_code: code,
            auto_confirm: isAutoConfirm ? 1 : 0,
            vehicle_id: mode === 'dispatch' ? document.getElementById('dispatch_vehicle_id').value : null,
            dispatched_note: mode === 'dispatch' ? document.getElementById('dispatch_notes').value : null,
            delivered_note: mode === 'delivery' ? document.getElementById('delivery_notes').value : null,
        };

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (!res.ok) {
                alert(data.message || 'Lỗi kiểm tra mã kiện.');
                return;
            }

            if (isAutoConfirm && data.data && data.data.can_confirm === false) {
                // Auto confirmed!
                alert(data.message || 'Thao tác thành công!');
                window.location.reload();
                return;
            }

            // Hiển thị preview card
            renderPreviewBox(mode, data.data);

        } catch (err) {
            alert('Lỗi kết nối máy chủ.');
        }
    }

    function renderPreviewBox(mode, pData) {
        currentScannedPackageId = pData.package_id;

        if (mode === 'dispatch') {
            const box = document.getElementById('dispatchPreviewBox');
            document.getElementById('dispatchPreviewTitle').innerText = 'Kiện: ' + (pData.package_code || pData.package_name);
            document.getElementById('dispatchPreviewOrder').innerText = pData.order_code || '-';
            document.getElementById('dispatchPreviewCustomer').innerText = pData.customer_name || '-';
            document.getElementById('dispatchPreviewTotalItems').innerText = pData.total_items + ' tấm';
            document.getElementById('dispatchPreviewAddress').innerText = pData.delivery_address || '-';
            document.getElementById('dispatchPreviewStatus').innerText = pData.status_label || 'Chờ xuất';
            box.classList.remove('hidden');
            box.classList.add('flex');
        } else {
            const box = document.getElementById('deliveryPreviewBox');
            document.getElementById('deliveryPreviewTitle').innerText = 'Kiện: ' + (pData.package_code || pData.package_name);
            document.getElementById('deliveryPreviewOrder').innerText = pData.order_code || '-';
            document.getElementById('deliveryPreviewCustomer').innerText = pData.customer_name || '-';
            document.getElementById('deliveryPreviewPhone').innerText = pData.customer_phone || '-';
            document.getElementById('deliveryPreviewAddress').innerText = pData.delivery_address || '-';
            document.getElementById('deliveryPreviewStatus').innerText = pData.status_label || 'Đang đi giao';
            box.classList.remove('hidden');
            box.classList.add('flex');
        }
    }

    // 4. Xác nhận thủ công từ preview box
    async function confirmDispatchAction() {
        if (!currentScannedPackageId) return;
        const res = await fetch(dispatchConfirmUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                package_id: currentScannedPackageId,
                vehicle_id: document.getElementById('dispatch_vehicle_id').value,
                dispatched_note: document.getElementById('dispatch_notes').value
            })
        });
        const data = await res.json();
        if (res.ok) {
            alert(data.message || 'Xác nhận xuất xưởng thành công!');
            window.location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra.');
        }
    }

    async function confirmDeliveryAction() {
        if (!currentScannedPackageId) return;
        const res = await fetch(deliveryConfirmUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                package_id: currentScannedPackageId,
                delivered_note: document.getElementById('delivery_notes').value
            })
        });
        const data = await res.json();
        if (res.ok) {
            alert(data.message || 'Xác nhận giao hàng thành công!');
            window.location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra.');
        }
    }

    function quickSelectPackage(mode, code) {
        if (mode === 'dispatch') {
            document.getElementById('dispatch_qr_input').value = code;
            triggerScanCheck('dispatch', code);
        } else {
            document.getElementById('delivery_qr_input').value = code;
            triggerScanCheck('delivery', code);
        }
    }

    // 5. Thao tác hàng loạt (Bulk Confirm)
    function toggleSelectAllDispatch(master) {
        document.querySelectorAll('.dispatch-checkbox').forEach(cb => cb.checked = master.checked);
        updateDispatchSelectedCount();
    }
    function updateDispatchSelectedCount() {
        const cnt = document.querySelectorAll('.dispatch-checkbox:checked').length;
        document.getElementById('selectedDispatchCount').innerText = cnt;
    }
    async function confirmBulkDispatch() {
        const ids = Array.from(document.querySelectorAll('.dispatch-checkbox:checked')).map(cb => parseInt(cb.value));
        if (ids.length === 0) {
            alert('Vui lòng chọn ít nhất 1 kiện để xuất xưởng.');
            return;
        }
        if (!confirm(`Xác nhận xuất xưởng cho ${ids.length} kiện hàng đã chọn?`)) return;

        const res = await fetch(dispatchBulkConfirmUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                package_ids: ids,
                vehicle_id: document.getElementById('dispatch_vehicle_id').value,
                dispatched_note: document.getElementById('dispatch_notes').value
            })
        });
        const data = await res.json();
        if (res.ok) {
            alert(data.message || 'Xuất xưởng hàng loạt thành công!');
            window.location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra.');
        }
    }

    function toggleSelectAllDelivery(master) {
        document.querySelectorAll('.delivery-checkbox').forEach(cb => cb.checked = master.checked);
        updateDeliverySelectedCount();
    }
    function updateDeliverySelectedCount() {
        const cnt = document.querySelectorAll('.delivery-checkbox:checked').length;
        document.getElementById('selectedDeliveryCount').innerText = cnt;
    }
    async function confirmBulkDelivery() {
        const ids = Array.from(document.querySelectorAll('.delivery-checkbox:checked')).map(cb => parseInt(cb.value));
        if (ids.length === 0) {
            alert('Vui lòng chọn ít nhất 1 kiện để xác nhận giao hàng.');
            return;
        }
        if (!confirm(`Xác nhận giao hàng thành công cho ${ids.length} kiện hàng đã chọn?`)) return;

        const res = await fetch(deliveryBulkConfirmUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                package_ids: ids,
                delivered_note: document.getElementById('delivery_notes').value
            })
        });
        const data = await res.json();
        if (res.ok) {
            alert(data.message || 'Xác nhận giao hàng thành công!');
            window.location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra.');
        }
    }

    // 6. Camera QR Scanning
    function startCameraScanner(mode) {
        cameraMode = mode;
        const modal = document.getElementById('scannerModal');
        modal.classList.remove('hidden');

        html5QrScanner = new Html5Qrcode("reader");
        html5QrScanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            (decodedText) => {
                stopCameraScanner();
                if (cameraMode === 'dispatch') {
                    document.getElementById('dispatch_qr_input').value = decodedText;
                    triggerScanCheck('dispatch', decodedText);
                } else {
                    document.getElementById('delivery_qr_input').value = decodedText;
                    triggerScanCheck('delivery', decodedText);
                }
            }
        ).catch(err => {
            alert('Không thể mở camera: ' + err);
            stopCameraScanner();
        });
    }

    function stopCameraScanner() {
        if (html5QrScanner) {
            html5QrScanner.stop().then(() => {
                html5QrScanner.clear();
                document.getElementById('scannerModal').classList.add('hidden');
            }).catch(() => {
                document.getElementById('scannerModal').classList.add('hidden');
            });
        } else {
            document.getElementById('scannerModal').classList.add('hidden');
        }
    }
</script>
@endpush
@endsection