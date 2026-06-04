@extends('layout.layout')

@php
    $title = 'Ép ván';
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
        <p class="text-sm text-neutral-500 dark:text-neutral-400">Kiểm soát các công đoạn làm lệnh, xuất kho và ép ván gỗ.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left column: Pressing Control (col-span-8) -->
        <div class="col-span-12 md:col-span-8 flex flex-col gap-6">
            <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm">
                <div class="card-body p-6 flex flex-col gap-6">
                    <!-- Header of card -->
                    <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800 pb-4">
                        <div class="flex items-center gap-2">
                            <iconify-icon icon="lucide:monitor" class="text-xl text-indigo-600"></iconify-icon>
                            <span class="font-bold text-neutral-800 dark:text-neutral-100">Kiểm soát quy trình ép ván</span>
                        </div>
                    </div>

                    <!-- Step Stepper (Tabs) -->
                    <div class="grid bg-neutral-50 dark:bg-neutral-800/50 p-1.5 rounded-2xl border border-neutral-100 dark:border-neutral-800/80 gap-1" style="grid-template-columns: repeat(5, minmax(0, 1fr));">
                        <button type="button" id="tabLamLenh" data-action="làm lệnh ép" data-label="LÀM LỆNH ÉP"
                            class="py-2.5 rounded-xl flex flex-col items-center justify-center gap-1 font-semibold text-[10px] md:text-xs transition-all duration-200 bg-white dark:bg-neutral-900 border border-emerald-500 text-emerald-700 dark:text-emerald-400 shadow-sm">
                            <iconify-icon icon="lucide:clipboard-list" class="text-base md:text-lg"></iconify-icon>
                            Làm lệnh
                        </button>
                        
                        <button type="button" id="tabXuatKho" data-action="xuất kho ván" data-label="XUẤT KHO VÁN"
                            class="py-2.5 rounded-xl flex flex-col items-center justify-center gap-1 font-semibold text-[10px] md:text-xs transition-all duration-200 text-neutral-500 dark:text-neutral-400 hover:bg-neutral-100/50 dark:hover:bg-neutral-800/30">
                            <iconify-icon icon="lucide:package-open" class="text-base md:text-lg"></iconify-icon>
                            Xuất kho
                        </button>

                        <button type="button" id="tabEpDon" data-action="ép đơn" data-label="ÉP ĐƠN"
                            class="py-2.5 rounded-xl flex flex-col items-center justify-center gap-1 font-semibold text-[10px] md:text-xs transition-all duration-200 text-neutral-500 dark:text-neutral-400 hover:bg-neutral-100/50 dark:hover:bg-neutral-800/30">
                            <iconify-icon icon="lucide:check-circle" class="text-base md:text-lg"></iconify-icon>
                            Ép đơn
                        </button>

                        <button type="button" id="tabEpDuTru" data-action="ép dự trữ" data-label="ÉP DỰ TRỮ"
                            class="py-2.5 rounded-xl flex flex-col items-center justify-center gap-1 font-semibold text-[10px] md:text-xs transition-all duration-200 text-neutral-500 dark:text-neutral-400 hover:bg-neutral-100/50 dark:hover:bg-neutral-800/30">
                            <iconify-icon icon="lucide:package" class="text-base md:text-lg"></iconify-icon>
                            Ép dự trữ
                        </button>

                        <button type="button" id="tabRollback" data-action="rollback" data-label="QUAY LẠI"
                            class="py-2.5 rounded-xl flex flex-col items-center justify-center gap-1 font-semibold text-[10px] md:text-xs transition-all duration-200 text-neutral-500 dark:text-neutral-400 hover:bg-neutral-100/50 dark:hover:bg-neutral-800/30">
                            <iconify-icon icon="lucide:undo" class="text-base md:text-lg"></iconify-icon>
                            Quay lại
                        </button>
                    </div>

                    <!-- Input Form -->
                    <form id="pressingForm" class="flex flex-col gap-5">
                        @csrf
                        <input type="hidden" name="action_type" id="action_type" value="làm lệnh ép">

                        <div>
                            <label for="product_code" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Mã sản phẩm / Lệnh (QR)</label>
                            <div class="flex items-center gap-3">
                                <input type="text" id="product_code" name="product_code" required autofocus
                                    class="flex-grow pl-4 pr-4 py-3.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-primary-500 text-base font-mono"
                                    placeholder="quét để thực hiện: LÀM LỆNH ÉP...">
                                
                                <button type="button" onclick="startScanning()"
                                    class="px-5 py-3.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/20 dark:hover:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50 rounded-xl flex items-center gap-2 font-semibold text-sm transition-colors whitespace-nowrap shadow-sm">
                                    <iconify-icon icon="lucide:camera" class="text-base"></iconify-icon>
                                    Quét Camera
                                </button>
                            </div>

                            <!-- Test Codes Badges -->
                            <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs text-neutral-500">
                                <span>Mã nhập thử:</span>
                                <div id="testBadges" class="flex flex-wrap gap-1.5 items-center">
                                    <button type="button" onclick="fillTestCode('LSX01-001')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-001</button>
                                    <button type="button" onclick="fillTestCode('LSX01-002')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-002</button>
                                    <button type="button" onclick="fillTestCode('LSX01-003')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-003</button>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Ghi chú nhanh</label>
                            <div class="relative flex items-center">
                                <span class="absolute text-neutral-400 flex items-center justify-center pointer-events-none" style="left: 14px;">
                                    <iconify-icon icon="lucide:file-text" class="text-lg"></iconify-icon>
                                </span>
                                <input type="text" id="notes" name="notes"
                                    class="w-full pr-4 py-3.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-primary-500 text-base"
                                    style="padding-left: 42px;"
                                    placeholder="Ví dụ: Ván bị xước nhẹ...">
                            </div>
                        </div>

                        <button type="submit" id="submitBtn" disabled
                            class="w-full py-4 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all transform hover:-translate-y-0.5 opacity-50 cursor-not-allowed pointer-events-none"
                            style="background-color: rgb(74, 185, 142);">
                            <iconify-icon icon="lucide:check-circle" class="text-xl" id="submitBtnIcon"></iconify-icon>
                            <span id="submitBtnText">Xác nhận: XÁC NHẬN LÀM LỆNH ÉP</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right column: Operation Manual (col-span-4) -->
        <div class="col-span-12 md:col-span-4">
            <div class="text-white rounded-2xl p-6 shadow-md flex flex-col justify-between h-full min-h-[380px]" style="background-color: rgb(79, 70, 229);">
                <div>
                    <h5 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <iconify-icon icon="lucide:help-circle"></iconify-icon>
                        Hướng dẫn thao tác
                    </h5>
                    <ol class="space-y-4 text-sm text-indigo-100 list-decimal list-inside pl-1">
                        <li>Chọn bước quy trình tương ứng phía trên.</li>
                        <li>Quét mã QR trên lệnh sản xuất hoặc trên tem sản phẩm.</li>
                        <li>Hệ thống sẽ tự động cập nhật trạng thái và người thực hiện.</li>
                    </ol>
                    
                    <div class="mt-6 pt-5 border-t border-indigo-500/50">
                        <span class="text-xs text-indigo-200 block font-bold uppercase tracking-wider mb-2">Quy trình chuẩn:</span>
                        <ul class="space-y-2 text-sm text-indigo-100 list-disc list-inside pl-1">
                            <li>Làm lệnh ép</li>
                            <li>Xuất kho ván</li>
                            <li>Ép đơn / Ép dự trữ</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Operator Info -->
                <div class="mt-6 pt-4 border-t border-indigo-500/50 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-600 border border-indigo-400 flex items-center justify-center text-white text-base">
                        <iconify-icon icon="lucide:user"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-[10px] text-indigo-200 block font-bold leading-none mb-1">NGƯỜI VẬN HÀNH</span>
                        <span class="text-sm font-bold block leading-none text-white">{{ Auth::user()->name ?? 'Hệ thống' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- History list section -->
    <div class="mt-8 card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm">
        <div class="card-body p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <h5 class="text-lg font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                    Lịch sử quy trình ép ván
                </h5>
                
                <!-- Search bar -->
                <div class="relative w-48 sm:w-56">
                    <span class="absolute top-1/2 -translate-y-1/2 text-neutral-400 flex items-center justify-center pointer-events-none" style="left: 10px;">
                        <iconify-icon icon="lucide:search" class="text-base"></iconify-icon>
                    </span>
                    <input type="text" id="historySearch" oninput="filterHistoryTable()"
                        class="w-full pr-3 py-1.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 text-xs focus:outline-none focus:ring-2 focus:ring-primary-500"
                        style="padding-left: 34px;"
                        placeholder="Tìm kiếm lịch sử...">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm" id="historyTable">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50">
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">SẢN PHẨM / LỆNH</th>
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">BƯỚC QUY TRÌNH</th>
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
                                @if($item->action === 'làm lệnh ép')
                                <span class="bg-purple-100 dark:bg-purple-950/30 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800/50 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1">
                                    <iconify-icon icon="lucide:clipboard-list" class="text-xs"></iconify-icon>
                                    LÀM LỆNH ÉP
                                </span>
                                @elseif($item->action === 'xuất kho ván')
                                <span class="bg-blue-100 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/50 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1">
                                    <iconify-icon icon="lucide:package-open" class="text-xs"></iconify-icon>
                                    XUẤT KHO VÁN
                                </span>
                                @elseif($item->action === 'ép đơn')
                                <span class="bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1">
                                    <iconify-icon icon="lucide:check-circle" class="text-xs"></iconify-icon>
                                    ÉP ĐƠN
                                </span>
                                @elseif($item->action === 'ép dự trữ')
                                <span class="bg-cyan-100 dark:bg-cyan-950/30 text-cyan-700 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800/50 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1">
                                    <iconify-icon icon="lucide:package" class="text-xs"></iconify-icon>
                                    ÉP DỰ TRỮ
                                </span>
                                @elseif($item->action === 'quay lại ép')
                                <span class="bg-amber-100 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1">
                                    <iconify-icon icon="lucide:undo" class="text-xs"></iconify-icon>
                                    QUAY LẠI ÉP
                                </span>
                                @else
                                <span class="bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-neutral-200 dark:border-neutral-700 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1 animate-pulse">
                                    {{ strtoupper($item->action) }}
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
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-400 flex items-center justify-center font-bold text-xs uppercase">
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
                                Chưa có lịch sử thao tác nào.
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
                    <iconify-icon icon="lucide:camera" class="text-indigo-600 text-lg"></iconify-icon>
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
            }
        }

        $(document).ready(function() {
            // Function to handle switching tabs
            function switchTab(targetButton) {
                // Clear all active styling
                $("#tabLamLenh, #tabXuatKho, #tabEpDon, #tabEpDuTru, #tabRollback").removeClass(
                    "bg-white dark:bg-neutral-900 border border-emerald-500 text-emerald-700 dark:text-emerald-400 shadow-sm border-amber-500 text-amber-700 dark:text-amber-400"
                ).addClass("text-neutral-500 dark:text-neutral-400").removeClass("hover:bg-neutral-100/50 dark:hover:bg-neutral-800/30");

                // Add hover style to other buttons
                $("#tabLamLenh, #tabXuatKho, #tabEpDon, #tabEpDuTru, #tabRollback").not(targetButton).addClass("hover:bg-neutral-100/50 dark:hover:bg-neutral-800/30");

                const action = targetButton.data("action");
                const label = targetButton.data("label");
                
                $("#action_type").val(action);
                
                if (action === "rollback") {
                    targetButton.removeClass("text-neutral-500 dark:text-neutral-400")
                                 .addClass("bg-white dark:bg-neutral-900 border border-amber-500 text-amber-700 dark:text-amber-400 shadow-sm");
                    $("#product_code").attr("placeholder", "quét để thực hiện: QUAY LẠI...");
                    $("#submitBtn").css("background-color", "rgb(217, 119, 6)");
                    $("#submitBtnIcon").attr("icon", "lucide:undo");
                } else {
                    targetButton.removeClass("text-neutral-500 dark:text-neutral-400")
                                 .addClass("bg-white dark:bg-neutral-900 border border-emerald-500 text-emerald-700 dark:text-emerald-400 shadow-sm");
                    $("#product_code").attr("placeholder", "quét để thực hiện: " + label + "...");
                    $("#submitBtn").css("background-color", "rgb(74, 185, 142)");
                    $("#submitBtnIcon").attr("icon", "lucide:check-circle");
                }
                
                $("#submitBtnText").text("Xác nhận: XÁC NHẬN " + label);
                toggleSubmitButton();
            }

            // Click handlers for each tab
            $("#tabLamLenh").on("click", function() { switchTab($(this)); });
            $("#tabXuatKho").on("click", function() { switchTab($(this)); });
            $("#tabEpDon").on("click", function() { switchTab($(this)); });
            $("#tabEpDuTru").on("click", function() { switchTab($(this)); });
            $("#tabRollback").on("click", function() { switchTab($(this)); });
            
            // Default select "Làm lệnh" or active state
            switchTab($("#tabLamLenh"));
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
                    document.getElementById("product_code").value = decodedText;
                    toggleSubmitButton();
                    stopScanning();
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

        document.getElementById("pressingForm").addEventListener("submit", function (e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById("submitBtn");
            const code = document.getElementById("product_code").value.trim();
            const notesValue = document.getElementById("notes").value.trim();
            const actionType = document.getElementById("action_type").value;
            
            if (!code) {
                showToast("Vui lòng nhập hoặc quét mã QR!", "error");
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.classList.add("opacity-50", "cursor-not-allowed", "pointer-events-none");
            const originalBtnContent = submitBtn.innerHTML;
            submitBtn.innerHTML = `
                <iconify-icon icon="lucide:loader-2" class="text-xl animate-spin"></iconify-icon>
                Đang xử lý...
            `;
            
            fetch("' . route("processes.pressing.complete") . '", {
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
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(res => {
                submitBtn.innerHTML = originalBtnContent;
                
                if (res.success) {
                    showToast(res.message, "success");
                    
                    document.getElementById("product_code").value = "";
                    document.getElementById("notes").value = "";
                    toggleSubmitButton();
                    document.getElementById("product_code").focus();
                    
                    // Remove existing row(s) matching this code if single and not bulk
                    if (!res.is_bulk) {
                        const rows = document.querySelectorAll("#historyTableBody tr");
                        rows.forEach(row => {
                            const codeCell = row.querySelector("td.font-mono");
                            if (codeCell && codeCell.textContent.trim() === res.data.product_code) {
                                row.remove();
                            }
                        });
                    }

                    // Prepend new row to table
                    const tbody = document.getElementById("historyTableBody");
                    const noHistoryRow = document.getElementById("noHistoryRow");
                    if (noHistoryRow) noHistoryRow.remove();
                    
                    let actionBadge = "";
                    if (res.data.action === "làm lệnh ép") {
                        actionBadge = `<span class="bg-purple-100 dark:bg-purple-950/30 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800/50 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1">
                                            <iconify-icon icon="lucide:clipboard-list" class="text-xs"></iconify-icon>
                                            LÀM LỆNH ÉP
                                       </span>`;
                    } else if (res.data.action === "xuất kho ván") {
                        actionBadge = `<span class="bg-blue-100 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/50 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1">
                                            <iconify-icon icon="lucide:package-open" class="text-xs"></iconify-icon>
                                            XUẤT KHO VÁN
                                       </span>`;
                    } else if (res.data.action === "ép đơn") {
                        actionBadge = `<span class="bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1">
                                            <iconify-icon icon="lucide:check-circle" class="text-xs"></iconify-icon>
                                            ÉP ĐƠN
                                       </span>`;
                    } else if (res.data.action === "ép dự trữ") {
                        actionBadge = `<span class="bg-cyan-100 dark:bg-cyan-950/30 text-cyan-700 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800/50 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1">
                                            <iconify-icon icon="lucide:package" class="text-xs"></iconify-icon>
                                            ÉP DỰ TRỮ
                                       </span>`;
                    } else {
                        actionBadge = `<span class="bg-amber-100 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50 text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1">
                                            <iconify-icon icon="lucide:undo" class="text-xs"></iconify-icon>
                                            QUAY LẠI ÉP
                                       </span>`;
                    }

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
                            ${res.data.notes}
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-400 flex items-center justify-center font-bold text-xs uppercase">
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
                    showToast(res.message || "Có lỗi xảy ra!", "error");
                    toggleSubmitButton();
                }
            })
            .catch(err => {
                console.error(err);
                showToast(err.message || "Không thể kết nối đến máy chủ!", "error");
                submitBtn.innerHTML = originalBtnContent;
                toggleSubmitButton();
            });
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
