@extends('layout.layout')

@php
    $title = 'Dán cạnh';
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
        <p class="text-sm text-neutral-500 dark:text-neutral-400">Quét mã QR sản phẩm để xác nhận hoàn thành công đoạn dán cạnh.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left column: Production Control (col-span-8) -->
        <div class="lg:col-span-8 flex flex-col gap-6">
            <div class="card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm">
                <div class="card-body p-6 flex flex-col gap-6">
                    <!-- Header of card -->
                    <div class="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-800 pb-4">
                        <div class="flex items-center gap-2">
                            <iconify-icon icon="fluent:production-24-regular" class="text-xl text-emerald-600"></iconify-icon>
                            <span class="font-bold text-neutral-800 dark:text-neutral-100">Kiểm soát dán cạnh</span>
                        </div>
                    </div>

                    <!-- Toggle Buttons -->
                    <div class="flex gap-4">
                        <button type="button" id="btnTabComplete" class="flex-1 py-3 px-4 border border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 rounded-xl flex items-center justify-center gap-2 font-semibold text-sm transition-all duration-200">
                            <iconify-icon icon="lucide:check-circle" class="text-lg"></iconify-icon>
                            Quét QR Hoàn thành
                        </button>
                        <button type="button" id="btnTabRollback" class="flex-1 py-3 px-4 border border-neutral-200 dark:border-neutral-700 text-neutral-500 dark:text-neutral-400 rounded-xl flex items-center justify-center gap-2 font-semibold text-sm transition-all duration-200">
                            <iconify-icon icon="lucide:refresh-cw" class="text-lg"></iconify-icon>
                            Quét QR Quay lại
                        </button>
                    </div>

                    <!-- Input Form -->
                    <form id="edgeBandingForm" class="flex flex-col gap-5">
                        @csrf
                        <input type="hidden" name="action_type" id="action_type" value="complete">

                        <div>
                            <label for="product_code" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Mã định danh sản phẩm (QR)</label>
                            <div class="flex items-center gap-3">
                                <div class="flex-grow">
                                    <input type="text" id="product_code" name="product_code" required autofocus
                                        class="w-full pl-4 pr-4 py-3.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-base"
                                        placeholder="Quét mã để HOÀN THÀNH...">
                                </div>
                                
                                <button type="button" onclick="startScanning()"
                                    class="px-5 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl flex items-center gap-2 font-semibold text-sm transition-colors whitespace-nowrap shadow-sm">
                                    <iconify-icon icon="lucide:camera" class="text-base"></iconify-icon>
                                </button>
                            </div>
                            
                            <!-- Test Codes Badges -->
                            <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs text-neutral-500">
                                <span>Mã nhập thử:</span>
                                <div id="completeBadges" class="flex flex-wrap gap-1.5 items-center">
                                    <button type="button" onclick="fillTestCode('LSX01-001')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-001</button>
                                    <button type="button" onclick="fillTestCode('LSX01-002')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-002</button>
                                    <button type="button" onclick="fillTestCode('LSX01-003')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-003</button>
                                    <button type="button" onclick="fillTestCode('LSX01-004')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-004</button>
                                    <button type="button" onclick="fillTestCode('LSX01-005')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-005</button>
                                    <button type="button" onclick="fillTestCode('QR-PBS-003')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">QR-PBS-003</button>
                                </div>
                                <div id="rollbackBadges" class="flex flex-wrap gap-1.5 items-center hidden">
                                    <button type="button" onclick="fillTestCode('LSX01-001')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-001</button>
                                    <button type="button" onclick="fillTestCode('LSX01-002')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-002</button>
                                    <button type="button" onclick="fillTestCode('LSX01-003')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-003</button>
                                    <button type="button" onclick="fillTestCode('LSX01-004')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-004</button>
                                    <button type="button" onclick="fillTestCode('LSX01-005')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">LSX01-005</button>
                                    <button type="button" onclick="fillTestCode('QR-PRD-001')" class="px-2 py-0.5 bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 rounded border border-neutral-200 dark:border-neutral-700 font-mono text-[10px]">QR-PRD-001</button>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="notes" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-2">Ghi chú nhanh</label>
                                <div class="relative flex items-center">
                                    <span class="absolute text-neutral-400 flex items-center justify-center pointer-events-none" style="left: 14px;">
                                        <iconify-icon icon="lucide:file-text" class="text-lg"></iconify-icon>
                                    </span>
                                    <input type="text" id="notes" name="notes"
                                        class="w-full pr-4 py-3.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-base"
                                        style="padding-left: 42px;"
                                        placeholder="Ví dụ: Dán đẹp, chỉ 1mm...">
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="submitBtn" disabled
                            class="w-full py-4 text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-sm transition-all transform hover:-translate-y-0.5 opacity-50 cursor-not-allowed pointer-events-none"
                            style="background-color: rgb(0, 140, 100);">
                            <iconify-icon icon="lucide:check-circle" class="text-xl" id="submitBtnIcon"></iconify-icon>
                            <span id="submitBtnText">Xác nhận HOÀN THÀNH dán cạnh</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right column: Operation Manual (col-span-4) -->
        <div class="lg:col-span-4">
            <div class="text-white rounded-2xl p-6 shadow-md flex flex-col justify-between h-full min-h-[380px]" style="background-color: rgb(0, 140, 100);">
                <div>
                    <h5 class="text-lg font-bold mb-4 flex items-center gap-2">
                        <iconify-icon icon="lucide:help-circle"></iconify-icon>
                        Hướng dẫn thao tác
                    </h5>
                    <ol class="space-y-4 text-sm text-emerald-100 list-decimal list-inside pl-1">
                        <li>Quét mã QR trên tem sản phẩm.</li>
                        <li>Nhập số lượng chỉ đã dán (nếu hệ thống chưa tự động tính).</li>
                        <li>Hệ thống sẽ ghi nhận thời gian và người thực hiện.</li>
                        <li>Kiểm tra lịch sử bên dưới nếu cần chỉnh sửa.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- History list section -->
    <div class="mt-8 card bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">
        <div class="card-header border-b border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900/50 py-4 px-6 flex items-center flex-wrap gap-3 justify-between">
            <h5 class="text-lg font-bold text-neutral-800 dark:text-neutral-100 mb-0 flex items-center gap-2">
                Lịch sử dán cạnh
            </h5>
            <div class="flex items-center flex-wrap gap-3">
                {{-- Per page --}}
                <span class="text-sm font-medium text-secondary-light mb-0">Hiển thị</span>
                <form method="GET" action="{{ route('processes.edge-banding') }}" id="perPageForm">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <select name="per_page" class="form-select form-select-sm w-auto border border-neutral-200 dark:border-neutral-700 rounded-lg py-1 px-2 text-xs bg-transparent dark:text-neutral-300"
                        onchange="document.getElementById('perPageForm').submit()">
                        @foreach([15, 25, 50, 100] as $option)
                        <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </form>

                {{-- Search bar --}}
                <form method="GET" action="{{ route('processes.edge-banding') }}" class="relative w-48 sm:w-56">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <span class="absolute top-1/2 -translate-y-1/2 text-neutral-400 flex items-center justify-center pointer-events-none" style="left: 10px;">
                        <iconify-icon icon="lucide:search" class="text-base"></iconify-icon>
                    </span>
                    <input type="text" name="search"
                        class="w-full pr-3 py-1.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500"
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
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">SỐ LƯỢNG CHỈ (M)</th>
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
                                @if($item->action === 'quay lại dán cạnh')
                                <span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1" style="background-color: rgba(249, 115, 22, 0.1); color: rgb(234, 88, 12); border: 1px solid rgba(249, 115, 22, 0.3);">
                                    <iconify-icon icon="lucide:refresh-cw" class="text-xs"></iconify-icon>
                                    QUAY LẠI
                                </span>
                                @else
                                <span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1" style="background-color: rgba(16, 185, 129, 0.1); color: rgb(5, 150, 105); border: 1px solid rgba(16, 185, 129, 0.3);">
                                    <iconify-icon icon="lucide:check-circle" class="text-xs"></iconify-icon>
                                    HOÀN THÀNH
                                </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-neutral-800 dark:text-neutral-200 font-medium">
                                {{ $item->product_name }}
                            </td>
                            <td class="py-3 px-4 text-neutral-800 dark:text-neutral-200 font-semibold font-mono">
                                {{ $item->edge_banding_length }} m
                            </td>
                            <td class="py-3 px-4 text-neutral-600 dark:text-neutral-400">
                                {{ $item->notes ?: '—' }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 flex items-center justify-center font-bold text-xs uppercase">
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
                                    <button type="button" class="p-1 hover:text-emerald-600" onclick="alert('Tính năng chỉnh sửa sẽ được phát triển sau.')">
                                        <iconify-icon icon="lucide:scissors" class="text-base"></iconify-icon>
                                    </button>
                                    <button type="button" class="p-1 hover:text-emerald-600" onclick="alert('Tính năng chỉnh sửa sẽ được phát triển sau.')">
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
                                Chưa có lịch sử dán cạnh nào trong ca làm việc này.
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
                    <iconify-icon icon="lucide:camera" class="text-emerald-600 text-lg"></iconify-icon>
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
                
                $("#submitBtn").css("background-color", "rgb(0, 140, 100)");
                $("#submitBtnIcon").attr("icon", "lucide:check-circle");
                $("#submitBtnText").text("Xác nhận HOÀN THÀNH dán cạnh");
                
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
                $("#submitBtnText").text("Xác nhận QUAY LẠI dán cạnh");
                
                toggleSubmitButton();
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
                    document.getElementById("product_code").value = decodedText;
                    toggleSubmitButton();
                    stopScanning();
                    // Play a beep sound on success
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

        document.getElementById("edgeBandingForm").addEventListener("submit", async function (e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById("submitBtn");
            const code = document.getElementById("product_code").value.trim();
            const notesValue = document.getElementById("notes").value.trim();
            const actionType = document.getElementById("action_type").value;
            
            if (!code) {
                showToast("Vui lòng nhập hoặc quét mã QR!", "error");
                return;
            }

            // Nếu đang ở chế độ quay lại, kiểm tra xem có stage "lỗi dán cạnh" không
            if (actionType === "rollback") {
                submitBtn.disabled = true;
                submitBtn.classList.add("opacity-50", "cursor-not-allowed", "pointer-events-none");
                const originalBtnContentCheck = submitBtn.innerHTML;
                submitBtn.innerHTML = `
                    <iconify-icon icon="lucide:loader-2" class="text-xl animate-spin"></iconify-icon>
                    Đang kiểm tra...
                `;

                try {
                    const statusRes = await fetch(`' . route("processes.edge-banding.product-status") . '?product_code=` + encodeURIComponent(code), {
                        method: "GET",
                        headers: {
                            "X-CSRF-TOKEN": "' . csrf_token() . '",
                            "Accept": "application/json"
                        }
                    });
                    const statusData = await statusRes.json();

                    submitBtn.innerHTML = originalBtnContentCheck;

                    if (!statusData.success) {
                        showToast(statusData.message || "Không tìm thấy sản phẩm!", "error");
                        toggleSubmitButton();
                        return;
                    }

                    if (!statusData.has_loi_dan_canh) {
                        showToast("Không có lỗi", "warning");
                        toggleSubmitButton();
                        return;
                    }
                } catch (checkErr) {
                    console.error(checkErr);
                    submitBtn.innerHTML = originalBtnContentCheck;
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
            
            fetch("' . route("processes.edge-banding.complete") . '", {
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
                // Restore button content
                submitBtn.innerHTML = originalBtnContent;
                
                if (res.success) {
                    const toastType = res.action_type === "rollback" ? "error" : "success";
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
                        ? `<span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1" style="background-color: rgba(249, 115, 22, 0.1); color: rgb(234, 88, 12); border: 1px solid rgba(249, 115, 22, 0.3);">
                                <iconify-icon icon="lucide:refresh-cw" class="text-xs"></iconify-icon>
                                QUAY LẠI
                           </span>`
                        : `<span class="text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1" style="background-color: rgba(16, 185, 129, 0.1); color: rgb(5, 150, 105); border: 1px solid rgba(16, 185, 129, 0.3);">
                                <iconify-icon icon="lucide:check-circle" class="text-xs"></iconify-icon>
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
                        <td class="py-3 px-4 text-neutral-800 dark:text-neutral-200 font-semibold font-mono">
                            ${res.data.edge_banding_length} m
                        </td>
                        <td class="py-3 px-4 text-neutral-600 dark:text-neutral-400">
                            ${res.data.notes}
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 flex items-center justify-center font-bold text-xs uppercase">
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
                                <button type="button" class="p-1 hover:text-emerald-600" onclick="alert(\'Tính năng chỉnh sửa sẽ được phát triển sau.\')">
                                    <iconify-icon icon="lucide:scissors" class="text-base"></iconify-icon>
                                </button>
                                <button type="button" class="p-1 hover:text-emerald-600" onclick="alert(\'Tính năng chỉnh sửa sẽ được phát triển sau.\')">
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
                    // Restore active state since request failed and input is not empty
                    toggleSubmitButton();
                }
            })
            .catch(err => {
                console.error(err);
                showToast("Không thể kết nối đến máy chủ!", "error");
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