@extends('layout.layout')

@php
    $title = "Chi tiết đóng gói";
    $subTitle = 'Đóng gói';
    $isCompleted = $package->status === 'completed';
@endphp

@section('content')
    <style>
        #packingSubmitBtn:disabled {
            opacity: 0.45 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
            transform: none !important;
            box-shadow: none !important;
        }
    </style>

    <div class="-mt-4 mb-6">
        <p class="text-sm text-neutral-500 dark:text-neutral-400">
            {{ $isCompleted ? 'Kiện này đã hoàn tất, bạn chỉ có thể xem danh sách linh kiện bên dưới.' : 'Quét mã QR hoặc barcode linh kiện để thêm nhanh vào kiện đang đóng gói.' }}
        </p>
    </div>

    <div class="packing-package-page__header mb-6 flex w-full flex-col justify-between gap-4 sm:flex-row">
        <div class="packing-package-page__heading packing-package-page__header-left flex flex-1 min-w-0 items-start gap-4">
            <a href="{{ route('processes.packing') }}" class="packing-package-page__back-link packing-package-page__header-back-link w-10 h-10 rounded-full hover:bg-neutral-100 text-secondary-light hover:text-neutral-900 flex items-center justify-center mt-1">
                <iconify-icon icon="lucide:arrow-left" class="packing-package-page__back-icon packing-package-page__header-back-icon text-2xl"></iconify-icon>
            </a>
            <div class="packing-package-page__heading-text packing-package-page__header-text">
                <h4 class="packing-package-page__name packing-package-page__header-name text-2xl font-bold text-neutral-900 mb-1">{{ $package->name }}</h4>
                <p class="packing-package-page__status packing-package-page__header-status text-sm text-secondary-light mb-0">{{ $isCompleted ? 'Đã hoàn tất' : 'Đang đóng gói (Nháp)' }}</p>
            </div>
        </div>

        @if(!$isCompleted)
            <form method="POST" action="{{ route('processes.packing.complete', $package) }}" onsubmit="return confirm('Hoàn tất đóng gói kiện này?')" class="packing-package-page__complete-form packing-package-page__header-complete-form shrink-0">
                @csrf
                <button type="submit" class="packing-package-page__complete-button packing-package-page__header-complete-button btn bg-primary-600 hover:bg-primary-700 text-white px-5 py-3 rounded-lg font-semibold">
                    Hoàn tất đóng gói
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="packing-package-page__alert packing-package-page__alert--success alert alert-success bg-success-100 text-success-700 border border-success-200 px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="packing-package-page__alert packing-package-page__alert--error alert alert-danger bg-danger-100 text-danger-700 border border-danger-200 px-4 py-3 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="packing-package-page__layout grid grid-cols-1 lg:grid-cols-12 gap-6">
        @unless($isCompleted)
            <div class="packing-package-page__scan-column lg:col-span-4">
                <div class="packing-package-page__scan-card packing-package-page__scan-card--form card h-full border border-neutral-200 rounded-xl shadow-sm">
                    <div class="packing-package-page__scan-body card-body p-6">
                        <div class="packing-package-page__scan-heading flex items-start gap-4 mb-6">
                            <span class="packing-package-page__scan-icon-wrap w-12 h-12 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center">
                                <iconify-icon icon="lucide:qr-code" class="packing-package-page__scan-icon text-2xl"></iconify-icon>
                            </span>
                            <div class="packing-package-page__scan-heading-text">
                                <h6 class="packing-package-page__scan-title text-lg font-bold text-neutral-900 mb-1">Quét mã linh kiện</h6>
                                <p class="packing-package-page__scan-description text-sm text-secondary-light mb-0">Quét mã vạch để thêm vào kiện</p>
                            </div>
                        </div>

                        <form id="packingScanForm" method="POST" action="{{ route('processes.packing.items.store', $package) }}" class="packing-package-page__scan-form flex flex-col gap-4">
                            @csrf
                            <div class="packing-package-page__scan-input-group packing-package-page__scan-input-row flex items-center gap-3">
                                <input type="text" id="product_code" name="product_code" value="{{ old('product_code') }}"
                                    class="packing-package-page__scan-input packing-package-page__scan-input--code form-control rounded-lg text-center font-mono py-3"
                                    placeholder="Quét mã QR / Barcode..."
                                    autofocus
                                    required>

                                <button type="button" onclick="startScanning()"
                                    class="packing-package-page__scan-camera-button packing-package-page__scan-camera-trigger w-12 h-12 shrink-0 rounded-lg bg-primary-600 hover:bg-primary-700 text-white flex items-center justify-center shadow-sm"
                                    title="Quét Camera">
                                    <iconify-icon icon="lucide:camera" class="packing-package-page__scan-camera-icon text-xl"></iconify-icon>
                                </button>
                            </div>

                            @error('product_code')
                                <p class="packing-package-page__scan-error text-danger-600 text-sm mb-0">{{ $message }}</p>
                            @enderror

                            <button type="submit" id="packingSubmitBtn"
                                class="packing-package-page__scan-submit packing-package-page__scan-submit-button btn justify-center w-full py-3 rounded-lg font-semibold bg-primary-600 hover:bg-primary-700 text-white opacity-50 cursor-not-allowed pointer-events-none"
                                disabled>
                                Thêm vào kiện
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endunless

        <div class="packing-package-page__items-column {{ $isCompleted ? 'lg:col-span-12' : 'lg:col-span-8' }}">
            <div class="packing-package-page__items-card packing-package-page__items-card--list card border-0 overflow-hidden shadow-sm">
                <div class="packing-package-page__items-header card-header bg-white border-b border-neutral-200 px-6 py-4 flex items-center justify-between gap-3">
                    <h6 id="packageItemsTitle" class="packing-package-page__items-title text-lg font-bold text-neutral-900 mb-0">Danh sách linh kiện trong kiện ({{ $packageItems->count() }})</h6>
                </div>

                <div class="packing-package-page__items-card-body card-body">
                    <div id="emptyPackageItemsState" class="packing-package-page__empty-state min-h-[260px] flex flex-col items-center justify-center text-center px-6 py-12 {{ $packageItems->isEmpty() ? '' : 'hidden' }}">
                        <iconify-icon icon="lucide:package-open" class="packing-package-page__empty-icon text-6xl text-neutral-300 mb-4"></iconify-icon>
                        <h6 class="packing-package-page__empty-title text-lg font-bold text-secondary-light mb-2">Chưa có linh kiện nào</h6>
                        <p class="packing-package-page__empty-description text-sm text-neutral-400 mb-0">Dùng máy quét barcode bên trái để thêm linh kiện vào kiện hàng này.</p>
                    </div>

                    <div id="packageItemsTableWrap" class="packing-package-page__items-table-wrap table-responsive {{ $packageItems->isEmpty() ? 'hidden' : '' }}">
                        <table class="packing-package-page__items-table table basic-border-table mb-0">
                            <thead class="packing-package-page__items-thead bg-neutral-50">
                                <tr class="packing-package-page__items-head-row">
                                    <th scope="col" class="packing-package-page__items-head-cell packing-package-page__items-head-cell--code border-r border-neutral-200 last:border-r-0 px-5 py-3 text-secondary-light font-semibold">Mã linh kiện</th>
                                    <th scope="col" class="packing-package-page__items-head-cell packing-package-page__items-head-cell--name border-r border-neutral-200 last:border-r-0 px-5 py-3 text-secondary-light font-semibold">Tên linh kiện</th>
                                    <th scope="col" class="packing-package-page__items-head-cell packing-package-page__items-head-cell--type border-r border-neutral-200 last:border-r-0 px-5 py-3 text-secondary-light font-semibold">Loại</th>
                                    <th scope="col" class="packing-package-page__items-head-cell packing-package-page__items-head-cell--order-code border-r border-neutral-200 last:border-r-0 px-5 py-3 text-secondary-light font-semibold">Mã đơn</th>
                                    <th scope="col" class="packing-package-page__items-head-cell packing-package-page__items-head-cell--time border-r border-neutral-200 last:border-r-0 px-5 py-3 text-secondary-light font-semibold">Thời gian thêm</th>
                                    @unless($isCompleted)
                                        <th scope="col" class="packing-package-page__items-head-cell packing-package-page__items-head-cell--action border-r border-neutral-200 last:border-r-0 px-5 py-3 text-secondary-light font-semibold text-end">Hành động</th>
                                    @endunless
                                </tr>
                            </thead>
                            <tbody id="packageItemsTableBody" class="packing-package-page__items-tbody">
                                @foreach($packageItems as $item)
                                    <tr class="packing-package-page__items-row packing-package-page__items-row--static" data-package-item-row-id="{{ $item->id }}">
                                        <td class="packing-package-page__items-cell packing-package-page__items-cell--code border-r border-neutral-200 last:border-r-0 px-5 py-3">
                                            <span class="packing-package-page__code-pill px-2.5 py-1 rounded bg-neutral-100 text-neutral-800 font-mono text-xs border border-neutral-200">
                                                {{ $item->product_code }}
                                            </span>
                                        </td>
                                        <td class="packing-package-page__items-cell packing-package-page__items-cell--name border-r border-neutral-200 last:border-r-0 px-5 py-3 font-semibold text-neutral-900">{{ $item->product_name }}</td>
                                        <td class="packing-package-page__items-cell packing-package-page__items-cell--type border-r border-neutral-200 last:border-r-0 px-5 py-3 text-secondary-light">{{ $item->type }}</td>
                                        <td class="packing-package-page__items-cell packing-package-page__items-cell--order-code border-r border-neutral-200 last:border-r-0 px-5 py-3 text-secondary-light">{{ $item->order_code }}</td>
                                        <td class="packing-package-page__items-cell packing-package-page__items-cell--time border-r border-neutral-200 last:border-r-0 px-5 py-3 text-secondary-light">{{ $item->created_at_label }}</td>
                                        @unless($isCompleted)
                                            <td class="packing-package-page__items-cell packing-package-page__items-cell--action border-r border-neutral-200 last:border-r-0 px-5 py-3 text-end">
                                                <form method="POST" action="{{ $item->delete_url }}" class="packing-package-page__item-delete-form inline-flex items-center" data-package-item-delete-form>
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="packing-package-page__item-delete-button w-8 h-8 rounded-lg bg-danger-50 text-danger-600 border border-danger-100 hover:bg-danger-600 hover:text-white hover:border-danger-600 transition-colors inline-flex items-center justify-center shadow-sm">
                                                        <iconify-icon icon="lucide:trash-2" class="packing-package-page__item-delete-icon text-base"></iconify-icon>
                                                    </button>
                                                </form>
                                            </td>
                                        @endunless
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @unless($isCompleted)
        <div id="scannerModal" class="packing-package-page__scanner-modal fixed inset-0 bg-neutral-900/80 backdrop-blur-sm z-50 flex items-center justify-center hidden">
            <div class="packing-package-page__scanner-panel packing-package-page__scanner-panel--modal bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl w-full max-w-md mx-4 overflow-hidden shadow-2xl">
                <div class="packing-package-page__scanner-header px-5 py-4 border-b border-neutral-100 dark:border-neutral-800 flex justify-between items-center bg-neutral-50 dark:bg-neutral-900/50">
                    <span class="packing-package-page__scanner-title font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                        <iconify-icon icon="lucide:camera" class="packing-package-page__scanner-title-icon text-primary-600 text-lg"></iconify-icon>
                        Quét mã QR qua Camera
                    </span>
                    <button type="button" onclick="stopScanning()" class="packing-package-page__scanner-close text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 p-1 rounded-lg">
                        <iconify-icon icon="lucide:x" class="packing-package-page__scanner-close-icon text-xl"></iconify-icon>
                    </button>
                </div>
                <div class="packing-package-page__scanner-body p-6 flex flex-col items-center justify-center gap-4">
                    <div id="reader" class="packing-package-page__scanner-reader w-full bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700 rounded-xl overflow-hidden shadow-inner" style="min-height: 250px;"></div>
                    <p class="packing-package-page__scanner-hint text-xs text-neutral-400 dark:text-neutral-500 text-center">Di chuyển camera để mã QR lọt vào ô quét.</p>
                </div>
            </div>
        </div>

        <div id="toastContainer" class="packing-package-page__toast-container fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>
    @endunless
@endsection

@php
    $script = '
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let html5QrCode = null;
        const canDeletePackageItems = ' . json_encode(!$isCompleted) . ';
        const packageItemDeleteToken = ' . json_encode(csrf_token()) . ';

        function toggleSubmitButton() {
            const productCodeInput = document.getElementById("product_code");
            const submitBtn = document.getElementById("packingSubmitBtn");
            if (!productCodeInput || !submitBtn) return;

            if (productCodeInput.value.trim() === "") {
                submitBtn.disabled = true;
                submitBtn.classList.add("opacity-50", "cursor-not-allowed", "pointer-events-none");
            } else {
                submitBtn.disabled = false;
                submitBtn.classList.remove("opacity-50", "cursor-not-allowed", "pointer-events-none");
            }
        }

        function startScanning() {
            document.getElementById("scannerModal").classList.remove("hidden");
            html5QrCode = new Html5Qrcode("reader");
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => {
                    document.getElementById("product_code").value = decodedText;
                    toggleSubmitButton();
                    stopScanning();
                    playScanBeep();
                },
                () => {}
            ).catch((err) => {
                showToast("Không thể khởi động camera: " + err, "error");
                stopScanning();
            });
        }

        function stopScanning() {
            const modal = document.getElementById("scannerModal");
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    modal.classList.add("hidden");
                }).catch(() => {
                    modal.classList.add("hidden");
                });
            } else {
                modal.classList.add("hidden");
            }
        }

        function playScanBeep() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                osc.type = "sine";
                osc.frequency.setValueAtTime(800, audioCtx.currentTime);
                osc.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.1);
            } catch (e) {}
        }

        function showToast(message, type = "success") {
            const toast = document.createElement("div");
            toast.className = `packing-package-page__toast flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border text-sm font-semibold transition-all transform translate-y-2 opacity-0 duration-300 ${
                type === "success"
                    ? "bg-emerald-50 border-emerald-200 text-emerald-800"
                    : "bg-red-50 border-red-200 text-red-800"
            }`;
            const icon = type === "success" ? "lucide:check-circle" : "lucide:alert-circle";
            toast.innerHTML = `<iconify-icon icon="${icon}" class="packing-package-page__toast-icon text-lg"></iconify-icon><span class="packing-package-page__toast-message">${message}</span>`;
            document.getElementById("toastContainer").appendChild(toast);
            setTimeout(() => toast.classList.remove("translate-y-2", "opacity-0"), 10);
            setTimeout(() => {
                toast.classList.add("opacity-0", "translate-y-2");
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        function updatePackageItemsTitle(count) {
            const title = document.getElementById("packageItemsTitle");
            if (title) {
                title.textContent = `Danh sách linh kiện trong kiện (${count})`;
            }
        }

        function syncPackageItemsState() {
            const tbody = document.getElementById("packageItemsTableBody");
            const emptyState = document.getElementById("emptyPackageItemsState");
            const tableWrap = document.getElementById("packageItemsTableWrap");
            if (!tbody || !emptyState || !tableWrap) return;

            const hasRows = tbody.querySelectorAll("tr").length > 0;
            emptyState.classList.toggle("hidden", hasRows);
            tableWrap.classList.toggle("hidden", !hasRows);
        }

        function appendPackageItemRow(item) {
            const tbody = document.getElementById("packageItemsTableBody");
            const emptyState = document.getElementById("emptyPackageItemsState");
            const tableWrap = document.getElementById("packageItemsTableWrap");
            if (!tbody || !emptyState || !tableWrap) return;

            emptyState.classList.add("hidden");
            tableWrap.classList.remove("hidden");

            const tr = document.createElement("tr");
            tr.className = "packing-package-page__items-row packing-package-page__items-row--dynamic";
            tr.innerHTML = `
                <td class="packing-package-page__items-cell packing-package-page__items-cell--code border-r border-neutral-200 last:border-r-0 px-5 py-3">
                    <span class="packing-package-page__code-pill px-2.5 py-1 rounded bg-neutral-100 text-neutral-800 font-mono text-xs border border-neutral-200">
                        ${item.product_code}
                    </span>
                </td>
                <td class="packing-package-page__items-cell packing-package-page__items-cell--name border-r border-neutral-200 last:border-r-0 px-5 py-3 font-semibold text-neutral-900">${item.product_name}</td>
                <td class="packing-package-page__items-cell packing-package-page__items-cell--type border-r border-neutral-200 last:border-r-0 px-5 py-3 text-secondary-light">${item.type}</td>
                <td class="packing-package-page__items-cell packing-package-page__items-cell--order-code border-r border-neutral-200 last:border-r-0 px-5 py-3 text-secondary-light">${item.order_code}</td>
                <td class="packing-package-page__items-cell packing-package-page__items-cell--time border-r border-neutral-200 last:border-r-0 px-5 py-3 text-secondary-light">${item.created_at_label}</td>
                ${canDeletePackageItems ? `
                    <td class="packing-package-page__items-cell packing-package-page__items-cell--action border-r border-neutral-200 last:border-r-0 px-5 py-3 text-end">
                        <form method="POST" action="${item.delete_url}" class="packing-package-page__item-delete-form inline-flex items-center" data-package-item-delete-form>
                            <input type="hidden" name="_token" value="${packageItemDeleteToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="packing-package-page__item-delete-button w-8 h-8 rounded-lg bg-danger-50 text-danger-600 border border-danger-100 hover:bg-danger-600 hover:text-white hover:border-danger-600 transition-colors inline-flex items-center justify-center shadow-sm">
                                <iconify-icon icon="lucide:trash-2" class="packing-package-page__item-delete-icon text-base"></iconify-icon>
                            </button>
                        </form>
                    </td>
                ` : ""}
            `;
            tbody.insertBefore(tr, tbody.firstChild);
        }

        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("packingScanForm");
            const productCodeInput = document.getElementById("product_code");
            const submitBtn = document.getElementById("packingSubmitBtn");

            if (productCodeInput) {
                productCodeInput.addEventListener("input", toggleSubmitButton);
                toggleSubmitButton();
            }

            if (!form || !submitBtn || !productCodeInput) return;

            form.addEventListener("submit", function (e) {
                e.preventDefault();

                const code = productCodeInput.value.trim();
                if (!code) {
                    showToast("Vui lòng nhập hoặc quét mã QR!", "error");
                    return;
                }

                const originalBtnContent = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.classList.add("opacity-50", "cursor-not-allowed", "pointer-events-none");
                submitBtn.innerHTML = `<iconify-icon icon="lucide:loader-2" class="text-xl animate-spin"></iconify-icon> Đang xử lý...`;

                fetch("' . route('processes.packing.items.store', $package) . '", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": "' . csrf_token() . '"
                    },
                    body: JSON.stringify({ product_code: code })
                })
                .then(async (response) => {
                    const data = await response.json();
                    if (!response.ok) throw data;
                    return data;
                })
                .then((res) => {
                    showToast(res.message, "success");
                    appendPackageItemRow(res.data);
                    updatePackageItemsTitle(res.items_count);
                    productCodeInput.value = "";
                    productCodeInput.focus();
                    submitBtn.innerHTML = originalBtnContent;
                    toggleSubmitButton();
                })
                .catch((err) => {
                    showToast(err.message || "Không thể thêm linh kiện vào kiện.", "error");
                    submitBtn.innerHTML = originalBtnContent;
                    toggleSubmitButton();
                });
            });

            if (canDeletePackageItems) {
                document.addEventListener("submit", function (e) {
                    const deleteForm = e.target;
                    if (!deleteForm.matches("[data-package-item-delete-form]")) return;

                    e.preventDefault();

                    if (!confirm("Xóa linh kiện khỏi kiện này?")) {
                        return;
                    }

                    const deleteButton = deleteForm.querySelector("button[type=\"submit\"]");
                    const originalDeleteButtonContent = deleteButton ? deleteButton.innerHTML : "";
                    if (deleteButton) {
                        deleteButton.disabled = true;
                        deleteButton.classList.add("opacity-70", "cursor-not-allowed");
                        deleteButton.innerHTML = `<iconify-icon icon="lucide:loader-2" class="text-base animate-spin"></iconify-icon>`;
                    }

                    fetch(deleteForm.action, {
                        method: "POST",
                        headers: {
                            "Accept": "application/json",
                            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
                            "X-CSRF-TOKEN": packageItemDeleteToken,
                            "X-Requested-With": "XMLHttpRequest"
                        },
                        body: new URLSearchParams(new FormData(deleteForm)).toString()
                    })
                    .then(async (response) => {
                        const data = await response.json();
                        if (!response.ok) throw data;
                        return data;
                    })
                    .then((res) => {
                        const row = deleteForm.closest("tr");
                        if (row) row.remove();
                        updatePackageItemsTitle(res.items_count);
                        syncPackageItemsState();
                        showToast(res.message, "success");
                    })
                    .catch((err) => {
                        showToast(err.message || "Không thể xóa linh kiện khỏi kiện.", "error");
                    })
                    .finally(() => {
                        if (deleteButton && document.body.contains(deleteButton)) {
                            deleteButton.disabled = false;
                            deleteButton.classList.remove("opacity-70", "cursor-not-allowed");
                            deleteButton.innerHTML = originalDeleteButtonContent;
                        }
                    });
                });
            }
        });
    </script>';
@endphp
