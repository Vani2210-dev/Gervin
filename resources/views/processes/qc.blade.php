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
                <div class="card-body p-6 flex flex-col gap-6">
                    <!-- Header of card -->
                    <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800 pb-4">
                        <div class="flex items-center gap-2">
                            <iconify-icon icon="lucide:shield-check" class="text-xl text-sky-600"></iconify-icon>
                            <span class="font-bold text-neutral-800 dark:text-neutral-100">Quét sản phẩm kiểm tra</span>
                        </div>
                    </div>


                    <!-- Input Form -->
                    <form id="qcForm" class="flex flex-col gap-5">
                        @csrf
                        <div>
                            <label for="product_code" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Mã định danh sản phẩm (QR)</label>
                            <div class="flex items-center gap-3">
                                <input type="text" id="product_code" name="product_code" required autofocus
                                    class="flex-grow pl-4 pr-4 py-3.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 text-base"
                                    placeholder="Quét mã để KIỂM TRA...">

                                <button type="button" onclick="startScanning()"
                                    class="px-5 py-3.5 text-white rounded-xl flex items-center gap-2 font-semibold text-sm transition-all duration-200 hover:opacity-90 active:scale-95 whitespace-nowrap shadow-sm"
                                    style="background-color: rgb(2, 100, 155);">
                                    <iconify-icon icon="lucide:camera" class="text-base"></iconify-icon>
                                    Quét Camera
                                </button>
                            </div>

                            <!-- Test Codes Badges -->
                            <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs text-neutral-500">
                                <span>Mã nhập thử:</span>
                                <div class="flex flex-wrap gap-1.5 items-center">
                                    <button type="button" onclick="fillTestCode('LSX01-001')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-001</button>
                                    <button type="button" onclick="fillTestCode('LSX01-002')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-002</button>
                                    <button type="button" onclick="fillTestCode('LSX01-003')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-003</button>
                                    <button type="button" onclick="fillTestCode('LSX01-004')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-004</button>
                                    <button type="button" onclick="fillTestCode('LSX01-005')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-005</button>
                                    <button type="button" onclick="fillTestCode('QR-PBS-003')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">QR-PBS-003</button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="submitBtn" disabled
                            class="w-full py-4 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all transform hover:-translate-y-0.5 opacity-50 cursor-not-allowed pointer-events-none"
                            style="background-color: rgb(2, 100, 155);">
                            <iconify-icon icon="lucide:search" class="text-xl" id="submitBtnIcon"></iconify-icon>
                            <span id="submitBtnText">Kiểm tra thông tin mã</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right column: Operation Manual (col-span-4) -->
        <div class="lg:col-span-4">
            <div class="text-white rounded-2xl p-6 shadow-md flex flex-col justify-between h-full min-h-[380px]" style="background-color: rgb(2, 100, 155);">
                <div>
                    <h5 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <iconify-icon icon="lucide:clipboard-list"></iconify-icon>
                        Quy trình QC
                    </h5>
                    <ol class="space-y-4 text-sm text-sky-100 list-decimal list-inside pl-1">
                        <li>Quét mã QR định danh từ tem Bazix.</li>
                        <li>Đối chiếu kích thước thực tế với thông số trên hệ thống.</li>
                        <li>Kiểm tra đầy đủ các hạng mục gia công (Dán cạnh, Khoan...).</li>
                    </ol>
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
                    <div class="flex flex-col gap-2">
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
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <h5 class="text-lg font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                    Lịch sử kiểm soát chất lượng
                </h5>

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
    <div id="toastContainer" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>
@endsection

@php
    $script = '
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let html5QrCode = null;

        function fillTestCode(code) {
            const productCodeInput = document.getElementById("product_code");
            if (productCodeInput) {
                productCodeInput.value = code;
                toggleSubmitButton();
                productCodeInput.focus();
                fetchProductInfo(code);
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
                    document.getElementById("product_code").value = decodedText;
                    toggleSubmitButton();
                    stopScanning();
                    fetchProductInfo(decodedText);
                    try {
                        let audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        let osc = audioCtx.createOscillator();
                        osc.type = "sine";
                        osc.frequency.setValueAtTime(800, audioCtx.currentTime);
                        osc.connect(audioCtx.destination);
                        osc.start();
                        osc.stop(audioCtx.currentTime + 0.1);
                    } catch(e) {}
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
            toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-semibold transition-all transform translate-y-2 opacity-0 duration-300 ${
                type === "success"
                    ? "bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-400"
                    : "bg-red-50 dark:bg-red-950/20 border-red-200 dark:border-red-800 text-red-800 dark:text-red-400"
            }`;

            const icon = type === "success" ? "lucide:check-circle" : "lucide:alert-circle";
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

        // Fetch product information by code
        function fetchProductInfo(code) {
            if (!code) return;
            
            const submitBtn = document.getElementById("submitBtn");
            const originalBtnContent = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <iconify-icon icon="lucide:loader-2" class="text-xl animate-spin"></iconify-icon>
                Đang tìm kiếm...
            `;
            
            fetch("' . route("processes.qc.product-info") . '?product_code=" + encodeURIComponent(code))
                .then(response => response.json())
                .then(res => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnContent;
                    toggleSubmitButton();
                    
                    if (res.success) {
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
                        
                        // Reset form elements in details section
                        resetQCFormDetails();
                    } else {
                        showToast(res.message || "Không tìm thấy thông tin sản phẩm!", "error");
                        document.getElementById("productDetailsSection").classList.add("hidden");
                    }
                })
                .catch(err => {
                    console.error(err);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnContent;
                    toggleSubmitButton();
                    showToast("Không thể tải thông tin sản phẩm!", "error");
                    document.getElementById("productDetailsSection").classList.add("hidden");
                });
        }

        // Submit QC Result
        function submitQCResult() {
            const code = document.getElementById("product_code").value.trim();
            const outcome = document.getElementById("selectedOutcome").value;
            const notesInput = document.getElementById("qcNotes").value.trim();
            const saveBtn = document.getElementById("saveQCBtn");

            if (!code) {
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

            fetch("' . route("processes.qc.complete") . '", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "' . csrf_token() . '"
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
                    showToast(res.message, "success");
                    
                    // Clear inputs and hide details section
                    document.getElementById("product_code").value = "";
                    toggleSubmitButton();
                    document.getElementById("productDetailsSection").classList.add("hidden");
                    document.getElementById("product_code").focus();

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

                    const errorLabel = (res.data && res.data.action) ? res.data.action.toUpperCase() : "LOI";
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
                                    ${res.data.operator.substring(0, 1)}
                                </div>
                                <div>
                                    <span class="block font-medium text-neutral-800 dark:text-neutral-200 leading-none mb-1">${res.data.operator}</span>
                                    <span class="block text-[10px] text-neutral-400 leading-none">${res.data.time}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-2 text-neutral-400">
                                <button type="button" class="p-1 hover:text-sky-600" onclick="alert(\'Tính năng chỉnh sửa sẽ được phát triển sau.\')">
                                    <iconify-icon icon="lucide:edit-2" class="text-base"></iconify-icon>
                                </button>
                                <button type="button" class="p-1 hover:text-danger-600" onclick="alert(\'Tính năng xóa lịch sử sẽ được phát triển sau.\')">
                                    <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                                </button>
                            </div>
                        </td>
                    `;
                    tbody.insertBefore(tr, tbody.firstChild);
                } else {
                    showToast(res.message || "Có lỗi xảy ra!", "error");
                }
            })
            .catch(err => {
                console.error(err);
                showToast("Không thể kết nối đến máy chủ!", "error");
                saveBtn.innerHTML = originalBtnContent;
                saveBtn.disabled = false;
                saveBtn.classList.remove("opacity-50", "cursor-not-allowed");
            });
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
            const code = document.getElementById("product_code").value.trim();
            if (code) {
                fetchProductInfo(code);
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
    ';
@endphp
