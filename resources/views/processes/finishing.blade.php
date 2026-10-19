@extends('layout.layout')

@php
    $title = 'Làm đẹp';
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
    </style>

    <div class="-mt-4 mb-6">
        <p class="text-sm text-neutral-500 dark:text-neutral-400">Quét mã QR sản phẩm để xác nhận hoàn thành công đoạn làm đẹp và vệ sinh.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left column: Beautification Control (col-span-8) -->
        <div class="col-span-12 md:col-span-8 flex flex-col gap-6">
            <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm">
                <div class="card-body p-4 sm:p-6 flex flex-col gap-4 sm:gap-6">
                    <!-- Header of card -->
                    <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800 pb-3 sm:pb-4">
                        <div class="flex items-center gap-2">
                            <iconify-icon icon="lucide:wand-2" class="text-xl text-pink-600"></iconify-icon>
                            <span class="font-bold text-neutral-800 dark:text-neutral-100">Kiểm soát làm đẹp</span>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-pink-50 dark:bg-pink-950/30 text-pink-700 dark:text-pink-400 text-xs font-bold border border-pink-200 dark:border-pink-800/50">
                            <iconify-icon icon="lucide:zap" class="text-sm"></iconify-icon>
                            Quét tự động xong
                        </span>
                    </div>

                    <!-- Toggle Buttons -->
                    <div class="flex gap-2 sm:gap-4">
                        <button type="button" id="btnTabComplete" class="flex-1 py-3 px-3 sm:px-4 border border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 rounded-xl flex items-center justify-center gap-2 font-semibold text-xs sm:text-sm transition-all duration-200">
                            <iconify-icon icon="lucide:check-circle" class="text-base sm:text-lg"></iconify-icon>
                            Quét Hoàn thành
                        </button>
                        <button type="button" id="btnTabRollback" class="flex-1 py-3 px-3 sm:px-4 border border-neutral-200 dark:border-neutral-700 text-neutral-500 dark:text-neutral-400 rounded-xl flex items-center justify-center gap-2 font-semibold text-xs sm:text-sm transition-all duration-200">
                            <iconify-icon icon="lucide:refresh-cw" class="text-base sm:text-lg"></iconify-icon>
                            Quét Quay lại
                        </button>
                    </div>

                    <!-- Input Form -->
                    <form id="finishingForm" class="flex flex-col gap-4 sm:gap-5">
                        @csrf
                        <input type="hidden" name="action_type" id="action_type" value="complete">

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="product_code" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300">Mã sản phẩm (QR)</label>
                                <span class="text-[11px] text-pink-600 dark:text-pink-400 font-semibold flex items-center gap-1">
                                    <iconify-icon icon="lucide:check-circle-2" class="text-xs"></iconify-icon> Quét là lưu ngay
                                </span>
                            </div>
                            <div class="flex items-center gap-2 sm:gap-3">
                                <div class="relative flex-grow">
                                    <input type="text" id="product_code" name="product_code" required autofocus
                                        class="w-full pl-4 pr-10 py-3.5 sm:py-4 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-pink-500 text-base sm:text-lg font-mono"
                                        placeholder="Quét mã QR...">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none">
                                        <iconify-icon icon="lucide:qr-code" class="text-xl"></iconify-icon>
                                    </span>
                                </div>
                                
                                <button type="button" onclick="startScanning()"
                                    class="px-4 sm:px-6 py-3.5 sm:py-4 bg-pink-600 hover:bg-pink-700 active:scale-95 text-white rounded-xl flex items-center justify-center gap-2 font-bold text-sm sm:text-base transition-all whitespace-nowrap shadow-sm flex-shrink-0">
                                    <iconify-icon icon="lucide:camera" class="text-xl"></iconify-icon>
                                    <span class="hidden sm:inline">Quét Camera</span>
                                    <span class="sm:hidden">Camera</span>
                                </button>
                            </div>

                            <!-- Test Codes Badges -->
                            <div class="mt-2.5 flex flex-wrap items-center gap-1.5 text-xs text-neutral-500">
                                <span class="text-[11px]">Mã thử:</span>
                                <div id="completeBadges" class="flex flex-wrap gap-1.5 items-center">
                                    <button type="button" onclick="fillTestCode('LSX01-001')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-001</button>
                                    <button type="button" onclick="fillTestCode('LSX01-002')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-002</button>
                                    <button type="button" onclick="fillTestCode('LSX01-003')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-003</button>
                                    <button type="button" onclick="fillTestCode('LSX01-004')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-004</button>
                                </div>
                                <div id="rollbackBadges" class="flex flex-wrap gap-1.5 items-center hidden">
                                    <button type="button" onclick="fillTestCode('LSX01-001')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-001</button>
                                    <button type="button" onclick="fillTestCode('LSX01-002')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-002</button>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="notes" class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1.5">Ghi chú nhanh (Tùy chọn)</label>
                            <div class="relative flex items-center">
                                <span class="absolute text-neutral-400 flex items-center justify-center pointer-events-none" style="left: 14px;">
                                    <iconify-icon icon="lucide:file-text" class="text-lg"></iconify-icon>
                                </span>
                                <input type="text" id="notes" name="notes"
                                    class="w-full pr-4 py-2.5 sm:py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-pink-500 text-sm"
                                    style="padding-left: 42px;"
                                    placeholder="Ví dụ: Đã vệ sinh sạch keo...">
                            </div>
                        </div>

                        <button type="submit" id="submitBtn" disabled
                            class="w-full py-3.5 sm:py-4 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all transform hover:-translate-y-0.5 opacity-50 cursor-not-allowed pointer-events-none text-sm sm:text-base"
                            style="background-color: rgb(74, 185, 142);">
                            <iconify-icon icon="lucide:check-circle" class="text-xl" id="submitBtnIcon"></iconify-icon>
                            <span id="submitBtnText">Xác nhận HOÀN THÀNH làm đẹp</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right column: Operation Manual (col-span-4) -->
        <div class="col-span-12 md:col-span-4">
            <!-- Mobile compact collapsible guide -->
            <details class="md:hidden bg-pink-50 dark:bg-pink-950/20 border border-pink-200 dark:border-pink-800/50 rounded-xl p-3.5 text-pink-900 dark:text-pink-200 text-xs">
                <summary class="font-bold cursor-pointer flex items-center gap-2 select-none">
                    <iconify-icon icon="lucide:help-circle" class="text-base text-pink-600 dark:text-pink-400"></iconify-icon>
                    <span>Tiêu chuẩn làm đẹp</span>
                </summary>
                <ol class="mt-2.5 space-y-1.5 text-xs text-pink-800 dark:text-pink-300 list-decimal list-inside pl-1">
                    <li>Vệ sinh sạch keo thừa trên các cạnh dán.</li>
                    <li>Kiểm tra bề mặt ván, không để lại vết bẩn.</li>
                    <li>Quét mã QR là hệ thống <strong>tự động ghi nhận hoàn thành ngay</strong>.</li>
                </ol>
            </details>

            <!-- Desktop full guide card -->
            <div class="hidden md:flex text-white rounded-2xl p-6 shadow-md flex-col h-full min-h-[340px]" style="background-color: #d1135c;">
                <div>
                    <h5 class="text-lg font-bold mb-4 flex items-center gap-2 text-white">
                        <iconify-icon icon="lucide:help-circle"></iconify-icon>
                        Tiêu chuẩn làm đẹp
                    </h5>
                    <ol class="space-y-4 text-sm text-pink-100 list-decimal list-inside pl-1">
                        <li>Vệ sinh sạch keo thừa trên các cạnh dán.</li>
                        <li>Kiểm tra bề mặt ván, không để lại vết bẩn, dấu vân tay.</li>
                        <li>Đối với Acrylic, sử dụng dung dịch đánh bóng chuyên dụng.</li>
                        <li>Dùng súng quét hoặc camera quét mã QR tem tấm là xong ngay.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
                <div>
                    <h5 class="text-lg font-bold mb-4 flex items-center gap-2 text-white">
                        <iconify-icon icon="lucide:help-circle"></iconify-icon>
                        Tiêu chuẩn làm đẹp
                    </h5>
                    <ol class="space-y-4 text-sm text-pink-100 list-decimal list-inside pl-1">
                        <li>Vệ sinh sạch keo thừa trên các cạnh dán.</li>
                        <li>Kiểm tra bề mặt ván, không để lại vết bẩn, dấu vân tay.</li>
                        <li>Đối với Acrylic, sử dụng dung dịch đánh bóng chuyên dụng.</li>
                        <li>Quét mã QR để xác nhận sau khi đã kiểm tra đạt yêu cầu.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- History list section -->
    <div class="mt-8 card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">
        <div class="card-header border-b border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900/50 py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
            <h5 class="text-lg font-bold text-neutral-800 dark:text-neutral-100 mb-0 flex items-center gap-2">
                Lịch sử hoàn thành làm đẹp
            </h5>
            <div class="flex items-center flex-wrap gap-3">
                {{-- Bộ lọc ngày tháng chung --}}
                <x-date-filter :action="route('processes.finishing')" />

                {{-- Per page --}}
                <div class="flex items-center gap-1.5">
                    <span class="text-sm font-medium text-secondary-light mb-0">Hiển thị</span>
                    <form method="GET" action="{{ route('processes.finishing') }}" id="perPageForm">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <select name="per_page" class="form-select form-select-sm w-auto border border-neutral-200 dark:border-neutral-700 rounded-lg py-1 px-2 text-xs bg-transparent dark:text-neutral-300"
                            onchange="document.getElementById('perPageForm').submit()">
                            @foreach([15, 25, 50, 100] as $option)
                            <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                {{-- Search bar --}}
                <form method="GET" action="{{ route('processes.finishing') }}" class="relative w-48 sm:w-56">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <span class="absolute top-1/2 -translate-y-1/2 text-neutral-400 flex items-center justify-center pointer-events-none" style="left: 10px;">
                        <iconify-icon icon="lucide:search" class="text-base"></iconify-icon>
                    </span>
                    <input type="text" name="search"
                        class="w-full pr-3 py-1.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 text-xs focus:outline-none focus:ring-2 focus:ring-primary-500"
                        style="padding-left: 34px;"
                        placeholder="Tìm kiếm lịch sử..." value="{{ $search }}">
                </form>
            </div>
        </div>

        <div class="card-body p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm" id="historyTable">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 whitespace-nowrap">
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
                                @if($item->action === 'quay lại làm đẹp')
                                <span class="bg-amber-100 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1">
                                    <iconify-icon icon="lucide:refresh-cw" class="text-xs"></iconify-icon>
                                    QUAY LẠI
                                </span>
                                @else
                                <span class="bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1">
                                    <iconify-icon icon="lucide:check-circle" class="text-xs"></iconify-icon>
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
                                    <div class="w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-950/30 text-primary-700 dark:text-primary-400 flex items-center justify-center font-bold text-xs uppercase">
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
                                    <button type="button" class="p-1 hover:text-primary-600" onclick="alert('Tính năng chỉnh sửa sẽ được phát triển sau.')">
                                        <iconify-icon icon="lucide:scissors" class="text-base"></iconify-icon>
                                    </button>
                                    <button type="button" class="p-1 hover:text-primary-600" onclick="alert('Tính năng chỉnh sửa sẽ được phát triển sau.')">
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
                            <td colspan="6" class="py-8 text-center text-neutral-400 dark:text-neutral-500">
                                Chưa có lịch sử làm đẹp nào trong ca làm việc này.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($history instanceof \Illuminate\Pagination\LengthAwarePaginator && $history->total() > $perPage)
                <div class="flex items-center justify-between flex-wrap gap-2 mt-6">
                    <span class="text-secondary-light text-sm">
                        Hiển thị {{ $history->firstItem() ?? 0 }} đến {{ $history->lastItem() ?? 0 }}
                        trong tổng {{ $history->total() }} bản ghi lịch sử
                    </span>
                    {{ $history->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Scanner Modal Overlay -->
    <div id="scannerModal" class="fixed inset-0 bg-neutral-900/80 backdrop-blur-sm z-50 flex items-center justify-center hidden">
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl w-full max-w-md mx-4 overflow-hidden shadow-2xl">
            <div class="px-5 py-4 border-b border-neutral-100 dark:border-neutral-800 flex justify-between items-center bg-neutral-50 dark:bg-neutral-900/50">
                <span class="font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                    <iconify-icon icon="lucide:camera" class="text-primary-600 text-lg"></iconify-icon>
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

    <!-- Toast Container (Top on mobile, bottom-right on desktop) -->
    <div id="toastContainer" class="fixed top-4 left-4 right-4 sm:left-auto sm:right-5 sm:top-auto sm:bottom-5 z-50 flex flex-col gap-2 pointer-events-none"></div>
@endsection

@php
    $script = '
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let html5QrCode = null;

        function playBeep(success = true) {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                if (success) {
                    osc.frequency.setValueAtTime(880, audioCtx.currentTime);
                    gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.12);
                } else {
                    osc.frequency.setValueAtTime(300, audioCtx.currentTime);
                    gain.gain.setValueAtTime(0.4, audioCtx.currentTime);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.25);
                }
            } catch(e) {}
        }

        function sanitizeProductCode(code) {
            if (!code) return "";
            code = code.trim();
            if (code.includes("_")) {
                code = code.split("_")[0].trim();
            }
            return code;
        }

        function fillTestCode(code) {
            const productCodeInput = document.getElementById("product_code");
            if (productCodeInput) {
                productCodeInput.value = code;
                toggleSubmitButton();
                productCodeInput.focus();
                doSubmitFinishing();
            }
        }

        $(document).ready(function() {
            // Tab complete click handler
            $("#btnTabComplete").on("click", function() {
                $(this).removeClass("border-neutral-200 dark:border-neutral-700 text-neutral-500 dark:text-neutral-400")
                       .addClass("border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400");
                
                $("#btnTabRollback").removeClass("border-amber-500 bg-amber-50/50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400")
                                    .addClass("border-neutral-200 dark:border-neutral-700 text-neutral-500 dark:text-neutral-400");
                
                $("#action_type").val("complete");
                $("#product_code").attr("placeholder", "Quét mã để HOÀN THÀNH...");
                
                $("#completeBadges").removeClass("hidden");
                $("#rollbackBadges").addClass("hidden");
                
                $("#submitBtn").css("background-color", "rgb(74, 185, 142)");
                $("#submitBtnIcon").attr("icon", "lucide:check-circle");
                $("#submitBtnText").text("Xác nhận HOÀN THÀNH làm đẹp");
                
                toggleSubmitButton();
            });

            // Tab rollback click handler
            $("#btnTabRollback").on("click", function() {
                $(this).removeClass("border-neutral-200 dark:border-neutral-700 text-neutral-500 dark:text-neutral-400")
                       .addClass("border-amber-500 bg-amber-50/50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400");
                
                $("#btnTabComplete").removeClass("border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400")
                                     .addClass("border-neutral-200 dark:border-neutral-700 text-neutral-500 dark:text-neutral-400");
                
                $("#action_type").val("rollback");
                $("#product_code").attr("placeholder", "Quét mã để QUAY LẠI...");
                
                $("#rollbackBadges").removeClass("hidden");
                $("#completeBadges").addClass("hidden");
                
                $("#submitBtn").css("background-color", "rgb(217, 119, 6)");
                $("#submitBtnIcon").attr("icon", "lucide:refresh-cw");
                $("#submitBtnText").text("Xác nhận QUAY LẠI làm đẹp");
                
                toggleSubmitButton();
            });

            // Tự động chuẩn hóa mã khi quét bằng súng quét mã vạch hoặc nhập tay
            $("#product_code").on("input change blur", function() {
                const val = $(this).val();
                const sanitized = sanitizeProductCode(val);
                if (val !== sanitized) {
                    $(this).val(sanitized);
                }
            });

            // Súng quét mã vạch gửi phím Enter -> Tự động xác nhận ngay không cần bấm nút
            $("#product_code").on("keydown", function(e) {
                if (e.key === "Enter" || e.keyCode === 13) {
                    e.preventDefault();
                    const val = sanitizeProductCode($(this).val());
                    if (val) {
                        $(this).val(val);
                        toggleSubmitButton();
                        doSubmitFinishing();
                    }
                }
            });
        });

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
                    const cleanCode = sanitizeProductCode(decodedText);
                    document.getElementById("product_code").value = cleanCode;
                    toggleSubmitButton();
                    stopScanning();
                    // Tự động xác nhận ngay lập tức sau khi quét QR qua camera
                    doSubmitFinishing();
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
                toast.style.backgroundColor = isDark ? "rgba(16, 185, 129, 0.2)" : "rgb(240, 253, 250)";
                toast.style.borderColor = isDark ? "rgba(16, 185, 129, 0.4)" : "rgb(167, 243, 208)";
                toast.style.color = isDark ? "rgb(52, 211, 153)" : "rgb(6, 95, 70)";
                icon = "lucide:check-circle";
            } else if (type === "warning") {
                toast.style.backgroundColor = isDark ? "rgba(245, 158, 11, 0.2)" : "rgb(254, 243, 199)";
                toast.style.borderColor = isDark ? "rgba(245, 158, 11, 0.4)" : "rgb(253, 230, 138)";
                toast.style.color = isDark ? "rgb(251, 191, 36)" : "rgb(146, 64, 14)";
                icon = "lucide:alert-triangle";
            } else {
                toast.style.backgroundColor = isDark ? "rgba(239, 68, 68, 0.2)" : "rgb(254, 242, 242)";
                toast.style.borderColor = isDark ? "rgba(239, 68, 68, 0.4)" : "rgb(254, 202, 202)";
                toast.style.color = isDark ? "rgb(248, 113, 113)" : "rgb(153, 27, 27)";
                icon = "lucide:alert-circle";
            }
            toast.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 sm:py-3.5 rounded-xl shadow-xl border text-sm font-bold transition-all transform translate-y-2 opacity-0 duration-300`;
            toast.innerHTML = `
                <iconify-icon icon="${icon}" class="text-xl flex-shrink-0"></iconify-icon>
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

        async function doSubmitFinishing() {
            const submitBtn = document.getElementById("submitBtn");
            const codeInput = document.getElementById("product_code");
            const code = sanitizeProductCode(codeInput.value);
            codeInput.value = code;
            const notesValue = document.getElementById("notes").value.trim();
            const actionType = document.getElementById("action_type").value;
            
            if (!code) {
                showToast("Vui lòng nhập hoặc quét mã QR!", "error");
                return;
            }

            // Nếu đang ở chế độ quay lại, kiểm tra xem có stage "lỗi làm đẹp" không
            if (actionType === "rollback") {
                submitBtn.disabled = true;
                submitBtn.classList.add("opacity-50", "cursor-not-allowed", "pointer-events-none");
                const originalBtnContentCheck = submitBtn.innerHTML;
                submitBtn.innerHTML = `
                    <iconify-icon icon="lucide:loader-2" class="text-xl animate-spin"></iconify-icon>
                    Đang kiểm tra...
                `;

                try {
                    const statusRes = await fetch(`' . route("processes.finishing.product-status") . '?product_code=` + encodeURIComponent(code), {
                        method: "GET",
                        headers: {
                            "X-CSRF-TOKEN": "' . csrf_token() . '",
                            "Accept": "application/json"
                        }
                    });
                    const statusData = await statusRes.json();

                    submitBtn.innerHTML = originalBtnContentCheck;

                    if (!statusData.success) {
                        playBeep(false);
                        showToast(statusData.message || "Không tìm thấy sản phẩm!", "error");
                        toggleSubmitButton();
                        return;
                    }

                    if (!statusData.has_loi_lam_dep) {
                        playBeep(false);
                        showToast("Không có lỗi", "warning");
                        toggleSubmitButton();
                        return;
                    }
                } catch (checkErr) {
                    console.error(checkErr);
                    submitBtn.innerHTML = originalBtnContentCheck;
                    playBeep(false);
                    showToast("Không thể kiểm tra trạng thái sản phẩm!", "error");
                    toggleSubmitButton();
                    return;
                }
            }
            
            // Disable button and show loading state
            submitBtn.disabled = true;
            submitBtn.classList.add("opacity-50", "cursor-not-allowed", "pointer-events-none");
            const originalBtnContent = submitBtn.innerHTML;
            submitBtn.innerHTML = `
                <iconify-icon icon="lucide:loader-2" class="text-xl animate-spin"></iconify-icon>
                Đang xử lý...
            `;
            
            fetch("' . route("processes.finishing.complete") . '", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "' . csrf_token() . '"
                },
                body: JSON.stringify({
                    product_code: code,
                    notes: notesValue,
                    action_type: actionType
                })
            })
            .then(response => response.json())
            .then(res => {
                submitBtn.innerHTML = originalBtnContent;
                
                if (res.success) {
                    playBeep(true);
                    const toastType = res.action_type === "rollback" ? "warning" : "success";
                    showToast(res.message, toastType);
                    
                    // Clear inputs
                    document.getElementById("product_code").value = "";
                    document.getElementById("notes").value = "";
                    toggleSubmitButton();
                    document.getElementById("product_code").focus();
                    
                    // Remove existing row for this product code
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
                    
                    const actionBadge = res.action_type === "rollback" 
                        ? `<span class="bg-amber-100 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1">
                                <iconify-icon icon="lucide:refresh-cw" class="text-xs"></iconify-icon>
                                QUAY LẠI
                           </span>`
                        : `<span class="bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1">
                                <iconify-icon icon="lucide:check-circle" class="text-xs"></iconify-icon>
                                HOÀN THÀNH
                           </span>`;

                    const tr = document.createElement("tr");
                    tr.className = "hover:bg-neutral-50/50 dark:hover:bg-neutral-800/10 transition-colors";
                    tr.innerHTML = `
                        <td class="py-3 px-4 font-mono text-xs">
                            <span class="bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-700 px-2.5 py-1 rounded font-bold">
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
                            ${res.data.notes}
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-primary-100 dark:bg-primary-950/30 text-primary-700 dark:text-primary-400 flex items-center justify-center font-bold text-xs uppercase">
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
                                <button type="button" class="p-1 hover:text-primary-600" onclick="alert(\'Tính năng chỉnh sửa sẽ được phát triển sau.\')">
                                    <iconify-icon icon="lucide:scissors" class="text-base"></iconify-icon>
                                </button>
                                <button type="button" class="p-1 hover:text-primary-600" onclick="alert(\'Tính năng chỉnh sửa sẽ được phát triển sau.\')">
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
                    playBeep(false);
                    showToast(res.message || "Có lỗi xảy ra!", "error");
                    toggleSubmitButton();
                }
            })
            .catch(err => {
                console.error(err);
                playBeep(false);
                showToast("Không thể kết nối đến máy chủ!", "error");
                submitBtn.innerHTML = originalBtnContent;
                toggleSubmitButton();
            });
        }

        document.getElementById("finishingForm").addEventListener("submit", function (e) {
            e.preventDefault();
            doSubmitFinishing();
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
