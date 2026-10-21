@extends('layout.layout')

@php
    $title = 'Kiểm soát (QC)';
    $subTitle = 'Quy trình';
@endphp

@section('content')
    <style>
        #submitBtn:disabled {
            opacity: 0.45 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
            transform: none !important;
            box-shadow: none !important;
        }
        #historyTable th, #historyTable td {
            text-align: left !important;
        }
        #historyTable th.text-right, #historyTable td.text-right {
            text-align: right !important;
        }

        /* Responsive grid columns and layout for product details section */
        @media (min-width: 1024px) {
            #productDetailsSection .lg\:col-span-7 {
                grid-column: span 7 / span 7 !important;
            }
            #productDetailsSection .lg\:col-span-5 {
                grid-column: span 5 / span 5 !important;
            }
            #productDetailsSection .lg\:border-t-0 {
                border-top-width: 0px !important;
            }
            #productDetailsSection .lg\:border-l {
                border-left-width: 1px !important;
            }
            #productDetailsSection .lg\:pt-0 {
                padding-top: 0px !important;
            }
            #productDetailsSection .lg\:pl-8 {
                padding-left: 2rem !important;
            }
        }
        @media (min-width: 768px) {
            #productDetailsSection .md\:col-span-2 {
                grid-column: span 2 / span 2 !important;
            }
        }

        /* Indigo theme colors for QC outcomes and labels */
        .border-indigo-500 {
            border-color: rgb(99, 102, 241) !important;
        }
        .bg-indigo-50\/30 {
            background-color: rgba(238, 242, 255, 0.3) !important;
        }
        .bg-indigo-100 {
            background-color: rgb(224, 231, 255) !important;
        }
        .text-indigo-600 {
            color: rgb(79, 70, 229) !important;
        }
        .text-indigo-650 {
            color: rgb(79, 70, 229) !important;
        }

        /* Neutral colors overrides */
        .text-neutral-850 {
            color: rgb(38, 38, 38) !important;
        }
        .text-neutral-455, .text-neutral-450 {
            color: rgb(115, 115, 115) !important;
        }
        .bg-neutral-50 {
            background-color: rgb(250, 250, 250) !important;
        }
        .border-neutral-100 {
            border-color: rgb(245, 245, 245) !important;
        }

        /* Dark mode overrides */
        .dark #productDetailsSection {
            background-color: rgb(23, 23, 23) !important;
            border-color: rgb(64, 64, 64) !important;
        }
        .dark .dark\:bg-neutral-900\/50 {
            background-color: rgba(23, 23, 23, 0.5) !important;
        }
        .dark .dark\:bg-neutral-800\/40 {
            background-color: rgba(38, 38, 38, 0.4) !important;
        }
        .dark .dark\:border-neutral-800\/60 {
            border-color: rgba(38, 38, 38, 0.6) !important;
        }
        .dark .dark\:bg-indigo-950\/10 {
            background-color: rgba(30, 27, 75, 0.1) !important;
        }
        .dark .dark\:bg-indigo-900\/50 {
            background-color: rgba(49, 46, 129, 0.5) !important;
        }
        .dark .dark\:text-indigo-400 {
            color: rgb(129, 140, 248) !important;
        }
    </style>

    <div class="-mt-4 mb-6">
        <p class="text-sm text-neutral-500 dark:text-neutral-400">Quét mã QR sản phẩm để xác nhận hoàn thành công đoạn kiểm soát chất lượng (QC).</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left column: Production Control (col-span-8) -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm">
                <div class="card-body p-4 sm:p-6 flex flex-col gap-5 sm:gap-6">
                    <!-- Header of card -->
                    <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800 pb-3 sm:pb-4">
                        <div class="flex items-center gap-2">
                            <iconify-icon icon="lucide:shield-check" class="text-xl text-sky-600"></iconify-icon>
                            <span class="font-bold text-neutral-800 dark:text-neutral-100 text-base sm:text-lg">Kiểm soát chất lượng (QC)</span>
                        </div>
                        <span id="badgeCurrentMode" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            <iconify-icon icon="lucide:zap" class="text-emerald-600 dark:text-emerald-400"></iconify-icon>
                            Quét là tự động xong
                        </span>
                    </div>

                    <!-- Mode Selector Tab -->
                    <div class="flex p-1 bg-neutral-100 dark:bg-neutral-800 rounded-xl gap-1">
                        <button type="button" id="tabModePass" onclick="setQcMode('pass')" 
                            class="flex-1 py-2 px-3 rounded-lg font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 transition-all bg-emerald-600 text-white shadow-sm">
                            <iconify-icon icon="lucide:zap" class="text-base"></iconify-icon>
                            <span>Quét Tự Động ĐẠT (Mặc định)</span>
                        </button>
                        <button type="button" id="tabModeDefect" onclick="setQcMode('defect')" 
                            class="flex-1 py-2 px-3 rounded-lg font-semibold text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 flex items-center justify-center gap-1.5 transition-all hover:text-neutral-900 dark:hover:text-neutral-100">
                            <iconify-icon icon="lucide:alert-triangle" class="text-base text-amber-500"></iconify-icon>
                            <span>Quét Báo LỖI</span>
                        </button>
                    </div>

                    <!-- Input Form -->
                    <form id="qcForm" class="flex flex-col gap-4 sm:gap-5">
                        @csrf
                        <div>
                            <label for="product_code" id="labelProductCode" class="block text-xs sm:text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Mã định danh sản phẩm (QR)</label>
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">
                                <input type="text" id="product_code" name="product_code" required autofocus
                                    class="w-full pl-4 pr-4 py-3.5 sm:py-4 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 text-base sm:text-lg font-mono font-bold"
                                    placeholder="Quét mã để HOÀN THÀNH QC...">

                                <button type="button" onclick="startScanning()"
                                    class="w-full sm:w-auto px-5 py-3.5 sm:py-4 text-white rounded-xl flex items-center justify-center gap-2 font-bold text-sm sm:text-base transition-all duration-200 hover:opacity-90 active:scale-95 whitespace-nowrap shadow-md"
                                    style="background-color: rgb(2, 100, 155);">
                                    <iconify-icon icon="lucide:camera" class="text-xl"></iconify-icon>
                                    <span>Quét Camera</span>
                                </button>
                            </div>

                            <!-- Test Codes Badges -->
                            <div class="mt-2.5 flex flex-wrap items-center gap-1.5 text-xs text-neutral-500">
                                <span>Mã nhập thử (bấm là tự nhận):</span>
                                <div class="flex flex-wrap gap-1.5 items-center">
                                    <button type="button" onclick="fillTestCode('LSX01-001')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[11px]">LSX01-001</button>
                                    <button type="button" onclick="fillTestCode('LSX01-002')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[11px]">LSX01-002</button>
                                    <button type="button" onclick="fillTestCode('LSX01-003')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[11px]">LSX01-003</button>
                                    <button type="button" onclick="fillTestCode('LSX01-004')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[11px]">LSX01-004</button>
                                    <button type="button" onclick="fillTestCode('LSX01-005')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[11px]">LSX01-005</button>
                                    <button type="button" onclick="fillTestCode('QR-PBS-003')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[11px]">QR-PBS-003</button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="submitBtn" disabled
                            class="w-full py-3.5 sm:py-4 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all transform hover:-translate-y-0.5 opacity-50 cursor-not-allowed pointer-events-none text-sm sm:text-base"
                            style="background-color: rgb(2, 100, 155);">
                            <iconify-icon icon="lucide:check-circle" class="text-xl" id="submitBtnIcon"></iconify-icon>
                            <span id="submitBtnText">Xác nhận QC ĐẠT (hoặc quét mã)</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right column: Operation Manual (col-span-4) -->
        <div class="lg:col-span-4">
            {{-- Collapsible on mobile --}}
            <details class="lg:hidden bg-sky-700 text-white rounded-2xl p-4 shadow-sm mb-2">
                <summary class="font-bold flex items-center justify-between cursor-pointer list-none">
                    <span class="flex items-center gap-2 text-sm"><iconify-icon icon="lucide:help-circle" class="text-lg"></iconify-icon> Hướng dẫn quy trình QC</span>
                    <iconify-icon icon="lucide:chevron-down" class="text-lg transition-transform duration-200"></iconify-icon>
                </summary>
                <div class="mt-3 pt-3 border-t border-sky-600/50 text-xs text-sky-100 space-y-2">
                    <p>• <strong>Quét Đạt (Mặc định)</strong>: Đưa mã QR vào camera hoặc bấm máy quét — hệ thống tự động hoàn thành QC ngay.</p>
                    <p>• <strong>Báo Lỗi</strong>: Bấm tab "Quét Báo LỖI" để kiểm tra thông tin và chọn công đoạn bị lỗi (Cắt CNC, Dán cạnh...).</p>
                </div>
            </details>

            {{-- Desktop card --}}
            <div class="hidden lg:flex text-white rounded-2xl p-6 shadow-md flex-col justify-between h-full min-h-[380px]" style="background-color: rgb(2, 100, 155);">
                <div>
                    <h5 class="text-lg font-bold mb-4 flex items-center gap-2 text-white">
                        <iconify-icon icon="lucide:clipboard-list"></iconify-icon>
                        Quy trình QC
                    </h5>
                    <ol class="space-y-4 text-sm text-sky-100 list-decimal list-inside pl-1">
                        <li><strong>Chế độ Quét Đạt (Mặc định)</strong>: Quét mã QR là tự động ghi nhận Hoàn thành QC.</li>
                        <li><strong>Chế độ Báo Lỗi</strong>: Chuyển tab khi phát hiện tấm lỗi để chọn nguyên nhân và hoàn trả công đoạn.</li>
                        <li>Đối chiếu kích thước thực tế với tem và thông số kỹ thuật.</li>
                    </ol>
                </div>
                <div class="mt-6 pt-4 border-t border-sky-600/50 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-sky-800 border border-sky-400 flex items-center justify-center text-white text-base font-bold">
                        {{ strtoupper(substr(Auth::user()?->name ?? 'Q', 0, 2)) }}
                    </div>
                    <div>
                        <span class="text-[10px] text-sky-200 block font-bold leading-none mb-1">NGƯỜI KIỂM SOÁT</span>
                        <span class="text-sm font-bold block leading-none text-white">{{ Auth::user()?->name ?? 'Hệ thống' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Section (Shown when product is scanned/loaded) -->
    <div id="productDetailsSection" class="hidden mt-6 card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">
        <!-- Section Header -->
        <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-800 flex justify-between items-center bg-neutral-50 dark:bg-neutral-900/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <iconify-icon icon="lucide:info" class="text-lg"></iconify-icon>
                </div>
                <div>
                    <h6 class="font-bold text-neutral-800 dark:text-neutral-100 m-0 text-sm">Thông tin sản phẩm</h6>
                    <span id="infoProductCode" class="text-[10px] text-neutral-400 font-mono">Mã: —</span>
                </div>
            </div>
            <button type="button" onclick="closeProductDetails()" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 p-1.5 rounded-lg transition-colors">
                <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
            </button>
        </div>

        <!-- Section Body -->
        <div class="card-body p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left Column: Product Info -->
                <div class="lg:col-span-7 flex flex-col gap-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Product Name Card -->
                        <div class="bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800/60 p-4 rounded-xl">
                            <span class="block text-[10px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider mb-1">Tên sản phẩm</span>
                            <span id="infoProductName" class="text-sm font-bold text-neutral-850 dark:text-neutral-100">Sản phẩm từ Bazix #002</span>
                        </div>
                        
                        <!-- Order Code Card -->
                        <div class="bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800/60 p-4 rounded-xl">
                            <span class="block text-[10px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider mb-1">Đơn hàng</span>
                            <span id="infoOrderCode" class="text-sm font-bold text-indigo-650 dark:text-indigo-400">ORD-024</span>
                        </div>

                        <!-- Dimensions Card (Spans full width on md+) -->
                        <div class="md:col-span-2 bg-neutral-50 dark:bg-neutral-800/40 border border-neutral-100 dark:border-neutral-800/60 p-4 rounded-xl">
                            <span class="block text-[10px] font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider mb-1">Kích thước</span>
                            <span id="infoDimensions" class="text-sm font-bold text-neutral-800 dark:text-neutral-100">1200 × 600 × 17 mm</span>
                        </div>
                    </div>

                    <!-- Manufacturing Steps -->
                    <div>
                        <span class="block text-xs font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider mb-3">Hạng mục gia công</span>
                        <div id="infoCompletedSteps" class="flex flex-wrap gap-2.5">
                            <!-- Badges will be generated here -->
                        </div>
                    </div>
                </div>

                <!-- Right Column: QC Result Form -->
                <div class="lg:col-span-5 border-t lg:border-t-0 lg:border-l border-neutral-100 dark:border-neutral-800 pt-6 lg:pt-0 lg:pl-8 flex flex-col gap-5">
                    <span class="block text-xs font-bold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">Kết quả kiểm soát chất lượng</span>
                    
                    <!-- Outcome Grid -->
                    <div class="grid grid-cols-2 gap-3" id="outcomeGrid">
                        <!-- Hoàn thành -->
                        <div data-value="complete" class="outcome-card cursor-pointer border border-indigo-500 bg-indigo-50/30 dark:bg-indigo-950/10 p-4 rounded-xl flex flex-col items-center justify-center text-center gap-1.5 transition-all duration-200 hover:shadow-sm">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <iconify-icon icon="lucide:check-circle" class="text-lg"></iconify-icon>
                            </div>
                            <span class="block font-bold text-xs text-neutral-800 dark:text-neutral-250">Hoàn thành</span>
                            <span class="block text-[9px] text-neutral-400 dark:text-neutral-500">Chuyển qua đóng gói</span>
                        </div>

                        <!-- Cắt hụt -->
                        <div data-value="cắt hụt" class="outcome-card cursor-pointer border border-neutral-200 dark:border-neutral-700 p-4 rounded-xl flex flex-col items-center justify-center text-center gap-1.5 transition-all duration-200 hover:shadow-sm">
                            <div class="w-8 h-8 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 flex items-center justify-center">
                                <iconify-icon icon="lucide:x-circle" class="text-lg"></iconify-icon>
                            </div>
                            <span class="block font-bold text-xs text-neutral-800 dark:text-neutral-250">Cắt hụt</span>
                            <span class="block text-[9px] text-neutral-400 dark:text-neutral-500">Cắt lại từ đầu</span>
                        </div>

                        <!-- Cắt thừa -->
                        <div data-value="cắt thừa" class="outcome-card cursor-pointer border border-neutral-200 dark:border-neutral-700 p-4 rounded-xl flex flex-col items-center justify-center text-center gap-1.5 transition-all duration-200 hover:shadow-sm">
                            <div class="w-8 h-8 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 flex items-center justify-center">
                                <iconify-icon icon="lucide:scissors" class="text-lg"></iconify-icon>
                            </div>
                            <span class="block font-bold text-xs text-neutral-800 dark:text-neutral-250">Cắt thừa</span>
                            <span class="block text-[9px] text-neutral-400 dark:text-neutral-500">Cắt lại CNC & Đánh bóng</span>
                        </div>

                        <!-- Bị Xước -->
                        <div data-value="bị xước" class="outcome-card cursor-pointer border border-neutral-200 dark:border-neutral-700 p-4 rounded-xl flex flex-col items-center justify-center text-center gap-1.5 transition-all duration-200 hover:shadow-sm">
                            <div class="w-8 h-8 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 flex items-center justify-center">
                                <iconify-icon icon="lucide:alert-circle" class="text-lg"></iconify-icon>
                            </div>
                            <span class="block font-bold text-xs text-neutral-800 dark:text-neutral-250">Bị Xước</span>
                            <span class="block text-[9px] text-neutral-400 dark:text-neutral-500">Cắt lấy tận dụng DC</span>
                        </div>
                    </div>

                    <!-- Hidden Input for selected outcome -->
                    <input type="hidden" id="selectedOutcome" value="complete">

                    <!-- QC Notes -->
                    <div class="flex flex-col gap-2" id="qcNotesSection">
                        <label for="qcNotes" class="block text-xs font-bold text-neutral-450 dark:text-neutral-400 uppercase tracking-wider">Ghi chú QC</label>
                        <textarea id="qcNotes" rows="3" 
                            class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                            placeholder="Nhập lý do không đạt hoặc ghi chú thêm..."></textarea>
                    </div>

                    <!-- Save Button -->
                    <button type="button" onclick="submitQCResult()" id="saveQCBtn"
                        class="w-full py-3.5 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all duration-200 hover:opacity-90 active:scale-95 text-sm"
                        style="background-color: rgb(139, 92, 246);">
                        <iconify-icon icon="lucide:save" class="text-lg"></iconify-icon>
                        Lưu kết quả kiểm soát
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- History list section -->
    <div class="mt-8 card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm">
        <div class="card-body p-6">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-6">
                <h5 class="text-lg font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-2 mb-0">
                    Lịch sử kiểm soát chất lượng
                </h5>

                <div class="flex items-center flex-wrap gap-3">
                    {{-- Bộ lọc ngày tháng chung --}}
                    <x-date-filter :action="route('processes.qc')" />

                    <!-- Search bar -->
                    <div class="relative w-48 sm:w-56">
                        <span class="absolute top-1/2 -translate-y-1/2 text-neutral-400 flex items-center justify-center pointer-events-none" style="left: 10px;">
                            <iconify-icon icon="lucide:search" class="text-base"></iconify-icon>
                        </span>
                        <input type="text" id="historySearch" oninput="filterHistoryTable()"
                            class="w-full pr-3 py-1.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 text-xs focus:outline-none focus:ring-2 focus:ring-sky-500"
                            style="padding-left: 34px;"
                            placeholder="Tìm kiếm lịch sử...">
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm" id="historyTable">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50">
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">MÃ ĐỊNH DANH</th>
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">HÀNH ĐỘNG</th>
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">TÊN SẢN PHẨM</th>

                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">GHI CHÚ</th>
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">NGƯỜI THAO TÁC</th>
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400 text-right">HÀNH ĐỘNG</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800" id="historyTableBody">
                        @forelse($history as $item)
                        <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/10 transition-colors">
                            <td class="py-3 px-4 font-mono text-xs">
                                <span class="bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-700 px-2.5 py-1 rounded">
                                    {{ $item->product_code }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if(stripos($item->action, 'lỗi') === 0 || $item->action === 'Ghi nhận lỗi')
                                <span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1" style="background-color: rgba(239, 68, 68, 0.1); color: rgb(185, 28, 28); border: 1px solid rgba(239, 68, 68, 0.4);">
                                    <iconify-icon icon="lucide:alert-circle" class="text-xs"></iconify-icon>
                                    {{ mb_strtoupper($item->action) }}
                                </span>
                                @else
                                <span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1" style="background-color: rgba(16, 185, 129, 0.1); color: rgb(5, 150, 105); border: 1px solid rgba(16, 185, 129, 0.3);">
                                    <iconify-icon icon="lucide:shield-check" class="text-xs"></iconify-icon>
                                    HOÀN THÀNH
                                </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-neutral-800 dark:text-neutral-200 font-medium">
                                {{ $item->product_name }}
                            </td>

                            <td class="py-3 px-4 text-neutral-600 dark:text-neutral-400">
                                {{ $item->notes ?: '—' }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-sky-100 dark:bg-sky-950/30 text-sky-700 dark:text-sky-400 flex items-center justify-center font-bold text-xs uppercase">
                                        {{ substr($item->operator, 0, 1) }}
                                    </div>
                                    <div>
                                        <span class="block font-medium text-neutral-800 dark:text-neutral-200 leading-none mb-1">{{ $item->operator }}</span>
                                        <span class="block text-[10px] text-neutral-400 leading-none">{{ $item->time }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-2 text-neutral-400">
                                    <button type="button" class="p-1 hover:text-sky-600" onclick="alert('Tính năng chỉnh sửa sẽ được phát triển sau.')">
                                        <iconify-icon icon="lucide:edit-2" class="text-base"></iconify-icon>
                                    </button>
                                    <button type="button" class="p-1 hover:text-danger-600" onclick="alert('Tính năng xóa lịch sử sẽ được phát triển sau.')">
                                        <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr id="noHistoryRow">
                            <td colspan="7" class="py-8 text-center text-neutral-400 dark:text-neutral-500">
                                Chưa có lịch sử kiểm soát nào.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Scanner Modal Overlay -->
    <div id="scannerModal" class="fixed inset-0 bg-neutral-900/80 backdrop-blur-sm z-50 flex items-center justify-center hidden">
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl w-full max-w-md mx-4 overflow-hidden shadow-2xl">
            <div class="px-5 py-4 border-b border-neutral-100 dark:border-neutral-800 flex justify-between items-center bg-neutral-50 dark:bg-neutral-900/50">
                <span class="font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                    <iconify-icon icon="lucide:camera" class="text-sky-600 text-lg"></iconify-icon>
                    Quét mã QR qua Camera
                </span>
                <button type="button" onclick="stopScanning()" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 p-1 rounded-lg">
                    <iconify-icon icon="lucide:x" class="text-xl"></iconify-icon>
                </button>
            </div>
            <div class="p-6 flex flex-col items-center justify-center gap-4">
                <div id="reader" class="w-full bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-xl overflow-hidden shadow-inner" style="min-height: 250px;"></div>
                <p class="text-xs text-neutral-400 dark:text-neutral-500 text-center">Di chuyển camera để mã QR lọt vào ô quét.</p>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-4 left-1/2 -translate-x-1/2 sm:left-auto sm:right-5 sm:translate-x-0 sm:bottom-5 sm:top-auto z-50 flex flex-col gap-2 w-[92vw] sm:w-auto max-w-md pointer-events-none"></div>
@endsection

@php
    $script = '';
@endphp

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let html5QrCode = null;
    let currentQcMode = 'pass'; // 'pass' | 'defect'

        function sanitizeProductCode(code) {
            if (!code) return "";
            code = code.trim();
            if (code.includes("_")) {
                code = code.split("_")[0].trim();
            }
            return code;
        }

        function setQcMode(mode) {
            currentQcMode = mode;
            const tabPass = document.getElementById("tabModePass");
            const tabDefect = document.getElementById("tabModeDefect");
            const badge = document.getElementById("badgeCurrentMode");
            const submitBtnText = document.getElementById("submitBtnText");
            const submitBtnIcon = document.getElementById("submitBtnIcon");
            const input = document.getElementById("product_code");

            if (mode === 'pass') {
                tabPass.className = "flex-1 py-2 px-3 rounded-lg font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 transition-all bg-emerald-600 text-white shadow-sm";
                tabDefect.className = "flex-1 py-2 px-3 rounded-lg font-semibold text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 flex items-center justify-center gap-1.5 transition-all hover:text-neutral-900 dark:hover:text-neutral-100";
                if (badge) {
                    badge.innerHTML = '<iconify-icon icon="lucide:zap" class="text-emerald-600 dark:text-emerald-400"></iconify-icon> Quét là tự động xong';
                    badge.className = "inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800";
                }
                if (input) input.placeholder = "Quét mã để HOÀN THÀNH QC...";
                if (submitBtnText) submitBtnText.textContent = "Xác nhận QC ĐẠT (hoặc quét mã)";
                if (submitBtnIcon) submitBtnIcon.setAttribute("icon", "lucide:check-circle");
            } else {
                tabPass.className = "flex-1 py-2 px-3 rounded-lg font-semibold text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 flex items-center justify-center gap-1.5 transition-all hover:text-neutral-900 dark:hover:text-neutral-100";
                tabDefect.className = "flex-1 py-2 px-3 rounded-lg font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 transition-all bg-amber-500 text-white shadow-sm";
                if (badge) {
                    badge.innerHTML = '<iconify-icon icon="lucide:alert-triangle" class="text-amber-500"></iconify-icon> Chế độ Báo Lỗi';
                    badge.className = "inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800";
                }
                if (input) input.placeholder = "Quét mã để BÁO LỖI...";
                if (submitBtnText) submitBtnText.textContent = "Kiểm tra thông tin & Chọn lỗi";
                if (submitBtnIcon) submitBtnIcon.setAttribute("icon", "lucide:search");
            }
            if (input) input.focus();
        }

        // Audio feedback
        function playBeep(isSuccess = true) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                if (isSuccess) {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = "sine";
                    osc.frequency.setValueAtTime(880, ctx.currentTime);
                    gain.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.12);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.12);
                } else {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = "sawtooth";
                    osc.frequency.setValueAtTime(300, ctx.currentTime);
                    osc.frequency.setValueAtTime(200, ctx.currentTime + 0.1);
                    gain.gain.setValueAtTime(0.3, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.25);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.25);
                }
            } catch(e) {}
        }

        $(document).ready(function() {
            $("#product_code").on("input change blur", function() {
                const val = $(this).val();
                const sanitized = sanitizeProductCode(val);
                if (val !== sanitized) {
                    $(this).val(sanitized);
                }
            });
        });

        function fillTestCode(code) {
            const productCodeInput = document.getElementById("product_code");
            if (productCodeInput) {
                productCodeInput.value = code;
                toggleSubmitButton();
                if (currentQcMode === 'pass') {
                    submitDirectQC(code, 'complete');
                } else {
                    fetchProductInfo(code);
                }
            }
        }

        function startScanning() {
            document.getElementById("scannerModal").classList.remove("hidden");
            html5QrCode = new Html5Qrcode("reader");
            html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                (decodedText, decodedResult) => {
                    const cleaned = sanitizeProductCode(decodedText);
                    document.getElementById("product_code").value = cleaned;
                    toggleSubmitButton();
                    stopScanning();
                    if (currentQcMode === 'pass') {
                        submitDirectQC(cleaned, 'complete');
                    } else {
                        fetchProductInfo(cleaned);
                    }
                },
                (errorMessage) => {
                    // Ignore parse errors
                }
            ).catch((err) => {
                alert("Không thể khởi động camera: " + err);
                stopScanning();
            });
        }

        function stopScanning() {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    document.getElementById("scannerModal").classList.add("hidden");
                }).catch(err => {
                    console.error(err);
                    document.getElementById("scannerModal").classList.add("hidden");
                });
            } else {
                document.getElementById("scannerModal").classList.add("hidden");
            }
        }

        function showToast(message, type = "success") {
            const toast = document.createElement("div");
            let icon = "";
            const isDark = document.documentElement.classList.contains("dark");
            if (type === "success") {
                toast.style.backgroundColor = isDark ? "rgba(16, 185, 129, 0.15)" : "rgb(240, 253, 250)";
                toast.style.borderColor = isDark ? "rgba(16, 185, 129, 0.3)" : "rgb(204, 251, 241)";
                toast.style.color = isDark ? "rgb(52, 211, 153)" : "rgb(6, 95, 70)";
                icon = "lucide:check-circle";
            } else if (type === "warning") {
                toast.style.backgroundColor = isDark ? "rgba(245, 158, 11, 0.15)" : "rgb(254, 243, 199)";
                toast.style.borderColor = isDark ? "rgba(245, 158, 11, 0.3)" : "rgb(253, 230, 138)";
                toast.style.color = isDark ? "rgb(251, 191, 36)" : "rgb(146, 64, 14)";
                icon = "lucide:alert-triangle";
            } else {
                toast.style.backgroundColor = isDark ? "rgba(239, 68, 68, 0.15)" : "rgb(254, 242, 242)";
                toast.style.borderColor = isDark ? "rgba(239, 68, 68, 0.3)" : "rgb(254, 226, 226)";
                toast.style.color = isDark ? "rgb(248, 113, 113)" : "rgb(153, 27, 27)";
                icon = "lucide:alert-circle";
            }
            toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-semibold transition-all transform translate-y-2 opacity-0 duration-300`;
            toast.innerHTML = `
                <iconify-icon icon="${icon}" class="text-lg"></iconify-icon>
                <span>${message}</span>
            `;

            document.getElementById("toastContainer").appendChild(toast);

            setTimeout(() => {
                toast.classList.remove("translate-y-2", "opacity-0");
            }, 10);

            setTimeout(() => {
                toast.classList.add("opacity-0", "translate-y-2");
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        // Direct Submit for Mode Pass (Auto-Confirm)
        function submitDirectQC(code, outcome = 'complete', notes = '') {
            code = sanitizeProductCode(code);
            if (!code) {
                playBeep(false);
                showToast("Mã sản phẩm trống!", "error");
                return;
            }

            const submitBtn = document.getElementById("submitBtn");
            const originalBtnContent = submitBtn ? submitBtn.innerHTML : "";
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <iconify-icon icon="lucide:loader-2" class="text-xl animate-spin"></iconify-icon>
                    Đang ghi nhận QC...
                `;
            }

            let actionType = "complete";
            if (outcome !== "complete") {
                actionType = "lỗi " + outcome;
            }

            fetch("{{ route('processes.qc.complete') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    product_code: code,
                    notes: notes,
                    action_type: actionType,
                    error_type: outcome !== "complete" ? outcome : null
                })
            })
            .then(response => response.json())
            .then(res => {
                if (submitBtn) {
                    submitBtn.innerHTML = originalBtnContent;
                    submitBtn.disabled = false;
                }

                if (res.success) {
                    playBeep(true);
                    const toastType = res.action_type === "rollback" ? "error" : "success";
                    showToast(res.message, toastType);

                    // Clear inputs and hide details section
                    document.getElementById("product_code").value = "";
                    toggleSubmitButton();
                    document.getElementById("productDetailsSection").classList.add("hidden");
                    document.getElementById("product_code").focus();

                    // Prepend or update history table
                    prependHistoryTable(res);
                } else {
                    playBeep(false);
                    showToast(res.message || "Có lỗi xảy ra!", "error");
                }
            })
            .catch(err => {
                console.error(err);
                playBeep(false);
                showToast("Không thể kết nối đến máy chủ!", "error");
                if (submitBtn) {
                    submitBtn.innerHTML = originalBtnContent;
                    submitBtn.disabled = false;
                }
            });
        }

        // Fetch product information by code (for Defect mode)
        function fetchProductInfo(code) {
            code = sanitizeProductCode(code);
            if (!code) return;
            const productCodeInput = document.getElementById("product_code");
            if (productCodeInput) productCodeInput.value = code;
            
            const submitBtn = document.getElementById("submitBtn");
            const originalBtnContent = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <iconify-icon icon="lucide:loader-2" class="text-xl animate-spin"></iconify-icon>
                Đang tìm kiếm...
            `;
            
            fetch("{{ route('processes.qc.product-info') }}?product_code=" + encodeURIComponent(code))
                .then(response => response.json())
                .then(res => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnContent;
                    toggleSubmitButton();
                    
                    if (res.success) {
                        playBeep(true);
                        // Populate details
                        document.getElementById("infoProductCode").textContent = "Mã: " + res.data.product_code;
                        document.getElementById("infoProductName").textContent = res.data.product_name;
                        document.getElementById("infoOrderCode").textContent = res.data.order_code;
                        document.getElementById("infoDimensions").textContent = res.data.dimensions;
                        
                        // Populate completed steps
                        const stepsContainer = document.getElementById("infoCompletedSteps");
                        stepsContainer.innerHTML = "";
                        res.data.completed_steps.forEach(step => {
                            const badge = document.createElement("div");
                            badge.className = "flex items-center gap-1.5 px-3 py-1.5 border border-indigo-200 dark:border-indigo-800 bg-indigo-50/55 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400 rounded-lg text-xs font-semibold";
                            badge.innerHTML = `
                                <iconify-icon icon="lucide:chevron-right" class="text-xs"></iconify-icon>
                                ${step}
                            `;
                            stepsContainer.appendChild(badge);
                        });

                        // Populate outcome grid based on completed steps
                        const outcomeGrid = document.getElementById("outcomeGrid");
                        outcomeGrid.innerHTML = `
                            <!-- Hoàn thành -->
                            <div data-value="complete" class="outcome-card cursor-pointer border border-indigo-500 bg-indigo-50/30 dark:bg-indigo-950/10 p-4 rounded-xl flex flex-col items-center justify-center text-center gap-1.5 transition-all duration-200 hover:shadow-sm">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                    <iconify-icon icon="lucide:check-circle" class="text-lg"></iconify-icon>
                                </div>
                                <span class="block font-bold text-xs text-neutral-800 dark:text-neutral-250">Hoàn thành</span>
                                <span class="block text-[9px] text-neutral-400 dark:text-neutral-500">Chuyển qua đóng gói</span>
                            </div>
                        `;

                        const stepConfigs = {
                            "ép ván": {
                                title: "Lỗi Ép ván",
                                icon: "lucide:layers",
                                subtext: "Yêu cầu ép lại ván"
                            },
                            "cắt cnc": {
                                title: "Lỗi Cắt CNC",
                                icon: "lucide:scissors",
                                subtext: "Yêu cầu cắt lại CNC"
                            },
                            "dán cạnh": {
                                title: "Lỗi Dán cạnh",
                                icon: "lucide:columns",
                                subtext: "Yêu cầu dán lại cạnh"
                            },
                            "làm đẹp": {
                                title: "Lỗi Làm đẹp",
                                icon: "lucide:sparkles",
                                subtext: "Yêu cầu sửa lỗi/vệ sinh"
                            }
                        };

                        res.data.completed_steps.forEach(step => {
                            const lowercaseStep = step.toLowerCase().trim();
                            const config = stepConfigs[lowercaseStep];
                            if (config) {
                                const card = document.createElement("div");
                                card.dataset.value = lowercaseStep;
                                card.className = "outcome-card cursor-pointer border border-neutral-200 dark:border-neutral-700 p-4 rounded-xl flex flex-col items-center justify-center text-center gap-1.5 transition-all duration-200 hover:shadow-sm";
                                card.innerHTML = `
                                    <div class="w-8 h-8 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 flex items-center justify-center">
                                        <iconify-icon icon="${config.icon}" class="text-lg"></iconify-icon>
                                    </div>
                                    <span class="block font-bold text-xs text-neutral-800 dark:text-neutral-250">${config.title}</span>
                                    <span class="block text-[9px] text-neutral-400 dark:text-neutral-500">${config.subtext}</span>
                                `;
                                outcomeGrid.appendChild(card);
                            }
                        });

                        // Show section
                        document.getElementById("productDetailsSection").classList.remove("hidden");
                        
                        // Scroll to details section smoothly
                        document.getElementById("productDetailsSection").scrollIntoView({ behavior: "smooth", block: "nearest" });
                        
                        if (res.data.is_qc_completed) {
                            // Ẩn các nút lựa chọn, notes, save button
                            document.getElementById("outcomeGrid").innerHTML = `
                                <div class="col-span-2 flex flex-col items-center justify-center gap-3 py-6">
                                    <div class="w-14 h-14 rounded-full flex items-center justify-center" style="background-color: rgba(16,185,129,0.12);">
                                        <iconify-icon icon="lucide:check-circle-2" class="text-3xl" style="color: rgb(5,150,105);"></iconify-icon>
                                    </div>
                                    <div class="text-center">
                                        <p class="font-bold text-base" style="color: rgb(5,150,105);">Đã hoàn thành QC</p>
                                        <p class="text-xs text-neutral-400 mt-0.5">Sản phẩm này đã được kiểm soát chất lượng.</p>
                                    </div>
                                </div>
                            `;
                            // Ẩn ghi chú và nút lưu
                            const qcNotesSection = document.getElementById("qcNotesSection");
                            if (qcNotesSection) qcNotesSection.classList.add("hidden");
                            const saveQCBtn = document.getElementById("saveQCBtn");
                            if (saveQCBtn) saveQCBtn.classList.add("hidden");
                        } else {
                            // Reset form elements in details section
                            resetQCFormDetails();
                            const qcNotesSection = document.getElementById("qcNotesSection");
                            if (qcNotesSection) qcNotesSection.classList.remove("hidden");
                            const saveQCBtn = document.getElementById("saveQCBtn");
                            if (saveQCBtn) saveQCBtn.classList.remove("hidden");
                        }
                    } else {
                        playBeep(false);
                        showToast(res.message || "Không tìm thấy thông tin sản phẩm!", "error");
                        document.getElementById("productDetailsSection").classList.add("hidden");
                    }
                })
                .catch(err => {
                    console.error(err);
                    playBeep(false);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnContent;
                    toggleSubmitButton();
                    showToast("Không thể tải thông tin sản phẩm!", "error");
                    document.getElementById("productDetailsSection").classList.add("hidden");
                });
        }

        // Submit QC Result from Details Panel
        function submitQCResult() {
            const codeInput = document.getElementById("product_code");
            const code = sanitizeProductCode(codeInput.value);
            codeInput.value = code;
            const outcome = document.getElementById("selectedOutcome").value;
            const notesInput = document.getElementById("qcNotes").value.trim();
            const saveBtn = document.getElementById("saveQCBtn");

            if (!code) {
                playBeep(false);
                showToast("Mã sản phẩm trống!", "error");
                return;
            }

            // Disable button and show loading state
            saveBtn.disabled = true;
            saveBtn.classList.add("opacity-50", "cursor-not-allowed");
            const originalBtnContent = saveBtn.innerHTML;
            saveBtn.innerHTML = `
                <iconify-icon icon="lucide:loader-2" class="text-xl animate-spin"></iconify-icon>
                Đang lưu...
            `;

            // Prepare action_type and notes based on selected card
            let actionType = "complete";
            let notesValue = notesInput;

            if (outcome !== "complete") {
                actionType = "lỗi " + outcome;
            }

            fetch("{{ route('processes.qc.complete') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    product_code: code,
                    notes: notesValue,
                    action_type: actionType,
                    error_type: outcome !== "complete" ? outcome : null
                })
            })
            .then(response => response.json())
            .then(res => {
                saveBtn.innerHTML = originalBtnContent;
                saveBtn.disabled = false;
                saveBtn.classList.remove("opacity-50", "cursor-not-allowed");

                if (res.success) {
                    playBeep(res.action_type !== "rollback");
                    const toastType = res.action_type === "rollback" ? "error" : "success";
                    showToast(res.message, toastType);
                    
                    // Clear inputs and hide details section
                    document.getElementById("product_code").value = "";
                    toggleSubmitButton();
                    document.getElementById("productDetailsSection").classList.add("hidden");
                    document.getElementById("product_code").focus();

                    // Prepend or update history table
                    prependHistoryTable(res);
                } else {
                    playBeep(false);
                    showToast(res.message || "Có lỗi xảy ra!", "error");
                }
            })
            .catch(err => {
                console.error(err);
                playBeep(false);
                showToast("Không thể kết nối đến máy chủ!", "error");
                saveBtn.innerHTML = originalBtnContent;
                saveBtn.disabled = false;
                saveBtn.classList.remove("opacity-50", "cursor-not-allowed");
            });
        }

        function prependHistoryTable(res) {
            if (!res || !res.data) return;

            // Remove existing row for this product code from the history table
            const rows = document.querySelectorAll("#historyTableBody tr");
            rows.forEach(row => {
                const codeCell = row.querySelector("td.font-mono");
                if (codeCell && codeCell.textContent.trim() === res.data.product_code) {
                    row.remove();
                }
            });

            // Add new row to top of table
            const tbody = document.getElementById("historyTableBody");
            const noHistoryRow = document.getElementById("noHistoryRow");
            if (noHistoryRow) noHistoryRow.remove();

            const errorLabel = (res.data && res.data.action) ? res.data.action.toUpperCase() : "LỖI";
            const actionBadge = res.action_type === "rollback"
                ? `<span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1" style="background-color: rgba(239, 68, 68, 0.1); color: rgb(185, 28, 28); border: 1px solid rgba(239, 68, 68, 0.4);">
                        <iconify-icon icon="lucide:alert-circle" class="text-xs"></iconify-icon>
                        ${errorLabel}
                   </span>`
                : `<span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1" style="background-color: rgba(16, 185, 129, 0.1); color: rgb(5, 150, 105); border: 1px solid rgba(16, 185, 129, 0.3);">
                        <iconify-icon icon="lucide:shield-check" class="text-xs"></iconify-icon>
                        HOÀN THÀNH
                   </span>`;

            const tr = document.createElement("tr");
            tr.className = "hover:bg-neutral-50/50 dark:hover:bg-neutral-800/10 transition-colors";
            tr.innerHTML = `
                <td class="py-3 px-4 font-mono text-xs">
                    <span class="bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-700 px-2.5 py-1 rounded">
                        ${res.data.product_code}
                    </span>
                </td>
                <td class="py-3 px-4">
                    ${actionBadge}
                </td>
                <td class="py-3 px-4 text-neutral-800 dark:text-neutral-200 font-medium">
                    ${res.data.product_name}
                </td>
                <td class="py-3 px-4 text-neutral-600 dark:text-neutral-400">
                    ${res.data.notes || "—"}
                </td>
                <td class="py-3 px-4">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-sky-100 dark:bg-sky-950/30 text-sky-700 dark:text-sky-400 flex items-center justify-center font-bold text-xs uppercase">
                            ${(res.data.operator || "Q").substring(0, 1)}
                        </div>
                        <div>
                            <span class="block font-medium text-neutral-800 dark:text-neutral-200 leading-none mb-1">${res.data.operator}</span>
                            <span class="block text-[10px] text-neutral-400 leading-none">${res.data.time}</span>
                        </div>
                    </div>
                </td>
                <td class="py-3 px-4 text-right">
                    <div class="flex items-center justify-end gap-2 text-neutral-400">
                        <button type="button" class="p-1 hover:text-sky-600" onclick="alert('Tính năng chỉnh sửa sẽ được phát triển sau.')">
                            <iconify-icon icon="lucide:edit-2" class="text-base"></iconify-icon>
                        </button>
                        <button type="button" class="p-1 hover:text-danger-600" onclick="alert('Tính năng xóa lịch sử sẽ được phát triển sau.')">
                            <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                        </button>
                    </div>
                </td>
            `;
            tbody.insertBefore(tr, tbody.firstChild);
        }

        function resetQCFormDetails() {
            document.getElementById("qcNotes").value = "";
            document.getElementById("selectedOutcome").value = "complete";
            
            // Set first outcome card as active, others inactive
            const cards = document.querySelectorAll(".outcome-card");
            cards.forEach((card, idx) => {
                if (idx === 0) {
                    card.classList.add("border-indigo-500", "bg-indigo-50/30", "dark:bg-indigo-950/10");
                    card.classList.remove("border-neutral-200", "dark:border-neutral-700");
                    const iconContainer = card.querySelector("div");
                    if (iconContainer) {
                        iconContainer.classList.add("bg-indigo-100", "dark:bg-indigo-900/50", "text-indigo-600", "dark:text-indigo-400");
                        iconContainer.classList.remove("bg-neutral-100", "dark:bg-neutral-800", "text-neutral-500", "dark:text-neutral-400");
                    }
                } else {
                    card.classList.remove("border-indigo-500", "bg-indigo-50/30", "dark:bg-indigo-950/10");
                    card.classList.add("border-neutral-200", "dark:border-neutral-700");
                    const iconContainer = card.querySelector("div");
                    if (iconContainer) {
                        iconContainer.classList.remove("bg-indigo-100", "dark:bg-indigo-900/50", "text-indigo-600", "dark:text-indigo-400");
                        iconContainer.classList.add("bg-neutral-100", "dark:bg-neutral-800", "text-neutral-500", "dark:text-neutral-400");
                    }
                }
            });
        }

        function closeProductDetails() {
            document.getElementById("productDetailsSection").classList.add("hidden");
            document.getElementById("product_code").value = "";
            toggleSubmitButton();
        }

        document.getElementById("qcForm").addEventListener("submit", function (e) {
            e.preventDefault();
            const codeInput = document.getElementById("product_code");
            const code = sanitizeProductCode(codeInput.value);
            codeInput.value = code;
            if (code) {
                if (currentQcMode === 'pass') {
                    submitDirectQC(code, 'complete');
                } else {
                    fetchProductInfo(code);
                }
            }
        });

        // Outcome Grid Card click selection (Event Delegation)
        document.getElementById("outcomeGrid").addEventListener("click", function(e) {
            const card = e.target.closest(".outcome-card");
            if (!card) return;

            document.querySelectorAll(".outcome-card").forEach(c => {
                c.classList.remove("border-indigo-500", "bg-indigo-50/30", "dark:bg-indigo-950/10");
                c.classList.add("border-neutral-200", "dark:border-neutral-700");
                
                const iconContainer = c.querySelector("div");
                if (iconContainer) {
                    iconContainer.classList.remove("bg-indigo-100", "dark:bg-indigo-900/50", "text-indigo-600", "dark:text-indigo-400");
                    iconContainer.classList.add("bg-neutral-100", "dark:bg-neutral-800", "text-neutral-500", "dark:text-neutral-400");
                }
            });

            card.classList.add("border-indigo-500", "bg-indigo-50/30", "dark:bg-indigo-950/10");
            card.classList.remove("border-neutral-200", "dark:border-neutral-700");
            
            const iconContainer = card.querySelector("div");
            if (iconContainer) {
                iconContainer.classList.add("bg-indigo-100", "dark:bg-indigo-900/50", "text-indigo-600", "dark:text-indigo-400");
                iconContainer.classList.remove("bg-neutral-100", "dark:bg-neutral-800", "text-neutral-500", "dark:text-neutral-400");
            }

            document.getElementById("selectedOutcome").value = card.dataset.value;
        });

        function filterHistoryTable() {
            const query = document.getElementById("historySearch").value.toLowerCase();
            const rows = document.querySelectorAll("#historyTableBody tr");

            rows.forEach(row => {
                if (row.id === "noHistoryRow") return;
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        function toggleSubmitButton() {
            const productCodeInput = document.getElementById("product_code");
            const submitBtn = document.getElementById("submitBtn");
            if (productCodeInput && submitBtn) {
                if (productCodeInput.value.trim() === "") {
                    submitBtn.disabled = true;
                    submitBtn.classList.add("opacity-50", "cursor-not-allowed", "pointer-events-none");
                } else {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove("opacity-50", "cursor-not-allowed", "pointer-events-none");
                }
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            const productCodeInput = document.getElementById("product_code");
            if (productCodeInput) {
                productCodeInput.addEventListener("input", toggleSubmitButton);
                productCodeInput.addEventListener("change", toggleSubmitButton);
                productCodeInput.addEventListener("keyup", toggleSubmitButton);
            }
            toggleSubmitButton();
        });
    </script>
@endpush