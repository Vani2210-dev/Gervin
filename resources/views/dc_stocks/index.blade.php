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
                {{-- Search (Bên trái ngoài cùng) --}}
                <form method="GET" action="{{ route('dc-stocks.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <div class="relative w-48">
                        <span class="absolute top-1/2 -translate-y-1/2 text-neutral-400 pointer-events-none" style="left: 10px;">
                            <iconify-icon icon="solar:magnifer-linear" class="text-base"></iconify-icon>
                        </span>
                        <input type="text" name="search"
                            class="w-full pr-3 py-1.5 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 text-xs focus:outline-none focus:ring-2 focus:ring-orange-500"
                            style="padding-left: 34px;"
                            placeholder="Tìm mã hàng, vị trí..." value="{{ $search }}">
                    </div>

                    {{-- Status filter --}}
                    <select name="status" onchange="this.form.submit()"
                            class="form-select form-select-sm w-auto border border-neutral-200 dark:border-neutral-700 rounded-lg py-1 px-2 text-xs bg-transparent dark:text-neutral-300">
                        <option value="">-- Tất cả --</option>
                        <option value="available" {{ $status === 'available' ? 'selected' : '' }}>Còn hàng</option>
                        <option value="reserved"  {{ $status === 'reserved'  ? 'selected' : '' }}>Đã đặt</option>
                        <option value="used"      {{ $status === 'used'      ? 'selected' : '' }}>Đã dùng</option>
                    </select>
                </form>

                {{-- Per page --}}
                <form method="GET" action="{{ route('dc-stocks.index') }}" id="perPageForm">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <select name="per_page" class="form-select form-select-sm w-auto border border-neutral-200 dark:border-neutral-700 rounded-lg py-1 px-2 text-xs bg-transparent dark:text-neutral-300"
                        onchange="document.getElementById('perPageForm').submit()">
                        @foreach([15, 25, 50, 100] as $option)
                        <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </form>

                {{-- Add Button --}}
                <button type="button" onclick="openAddModal()"
                    class="px-4 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-bold flex items-center gap-2 transition-colors shadow-sm">
                    <iconify-icon icon="lucide:plus" class="text-base"></iconify-icon>
                    Thêm tấm dư
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="card-body p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm" id="stockTable">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/50 whitespace-nowrap">
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">MÃ HÀNG</th>
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">GHI CHÚ</th>
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400 text-right">CAO (mm)</th>
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400 text-right">RỘNG (mm)</th>
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400 text-right">SỐ LƯỢNG</th>
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">VỊ TRÍ</th>
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400">TRẠNG THÁI</th>
                            <th class="py-3 px-4 font-semibold text-neutral-600 dark:text-neutral-400 text-right">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800" id="stockTableBody">
                        @forelse($stocks as $item)
                        <tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/10 transition-colors" data-id="{{ $item->id }}">
                            <td class="py-3 px-4">
                                <span class="font-mono font-bold text-neutral-800 dark:text-neutral-200 bg-orange-50 dark:bg-orange-950/20 border border-orange-200 dark:border-orange-800/40 px-2.5 py-1 rounded text-xs">
                                    {{ $item->color_code }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-neutral-600 dark:text-neutral-400 text-sm">
                                {{ $item->note ?: '—' }}
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-neutral-800 dark:text-neutral-200">
                                {{ number_format($item->height) }}
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-neutral-800 dark:text-neutral-200">
                                {{ number_format($item->width) }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <span class="font-bold text-lg text-neutral-800 dark:text-neutral-200">{{ $item->quantity }}</span>
                            </td>
                            <td class="py-3 px-4">
                                @if($item->location)
                                    <span class="font-mono text-xs font-bold px-2.5 py-1 bg-sky-50 dark:bg-sky-950/20 border border-sky-200 dark:border-sky-800/40 text-sky-700 dark:text-sky-400 rounded">
                                        {{ $item->location }}
                                    </span>
                                @else
                                    <span class="text-neutral-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($item->status === 'available')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50">
                                        <span class="status-dot status-available"></span> Còn hàng
                                    </span>
                                @elseif($item->status === 'reserved')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/50">
                                        <span class="status-dot status-reserved"></span> Đã đặt
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 border border-neutral-200 dark:border-neutral-700">
                                        <span class="status-dot status-used"></span> Đã dùng
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button"
                                        onclick="openEditModal({{ json_encode($item) }})"
                                        class="p-1.5 text-neutral-400 hover:text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-950/20 rounded-lg transition-colors">
                                        <iconify-icon icon="lucide:edit-2" class="text-base"></iconify-icon>
                                    </button>
                                    <button type="button"
                                        onclick="deleteStock({{ $item->id }})"
                                        class="p-1.5 text-neutral-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-lg transition-colors">
                                        <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr id="emptyRow">
                            <td colspan="8" class="py-12 text-center text-neutral-400 dark:text-neutral-500">
                                <iconify-icon icon="lucide:package-open" class="text-4xl mb-2 block"></iconify-icon>
                                Kho DC chưa có tấm nào.
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
            <div class="p-6 flex flex-col gap-4">
                <input type="hidden" id="stockId" value="">

                {{-- Mã hàng --}}
                <div>
                    <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase mb-1.5">Mã hàng <span class="text-red-500">*</span></label>
                    <input type="text" id="f_color_code" list="colorCodeList" required autocomplete="off"
                           class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-orange-500 font-mono font-bold uppercase"
                           placeholder="VD: PVC20, PVC28...">
                    <datalist id="colorCodeList">
                        @foreach($colorCodes as $code)
                            <option value="{{ $code }}">
                        @endforeach
                    </datalist>
                </div>

                {{-- Ghi chú --}}
                <div>
                    <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase mb-1.5">Ghi chú phụ</label>
                    <input type="text" id="f_note"
                           class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-orange-500"
                           placeholder="VD: 2mat, 1mat...">
                </div>

                {{-- Kích thước --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase mb-1.5">Cao (mm) <span class="text-red-500">*</span></label>
                        <input type="number" id="f_height" min="1" required
                               class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-orange-500"
                               placeholder="1780">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase mb-1.5">Rộng (mm) <span class="text-red-500">*</span></label>
                        <input type="number" id="f_width" min="1" required
                               class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-orange-500"
                               placeholder="420">
                    </div>
                </div>

                {{-- SL và Vị trí --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase mb-1.5">Số lượng <span class="text-red-500">*</span></label>
                        <input type="number" id="f_quantity" min="1" value="1" required
                               class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase mb-1.5">Vị trí kho</label>
                        <input type="text" id="f_location"
                               class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-orange-500 font-mono uppercase"
                               placeholder="VD: D14, SO4, D1...">
                    </div>
                </div>

                {{-- Trạng thái --}}
                <div>
                    <label class="block text-xs font-bold text-neutral-600 dark:text-neutral-400 uppercase mb-1.5">Trạng thái</label>
                    <select id="f_status"
                            class="w-full px-4 py-3 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="available">Còn hàng</option>
                        <option value="reserved">Đã đặt</option>
                        <option value="used">Đã dùng</option>
                    </select>
                </div>
            </div>

            <div class="px-6 py-4 bg-neutral-50 dark:bg-neutral-900/50 border-t border-neutral-100 dark:border-neutral-800 flex justify-end gap-3 rounded-b-xl">
                <button type="button" onclick="closeModal('dcStockModal')"
                        class="px-5 py-2.5 border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 rounded-xl font-bold text-sm hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                    Hủy
                </button>
                <button type="submit" id="saveBtn"
                        class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-bold text-sm shadow-sm transition-colors flex items-center gap-2">
                    <iconify-icon icon="lucide:save" class="text-base"></iconify-icon>
                    <span id="saveBtnText">Lưu vào Kho DC</span>
                </button>
            </div>
        </form>
    </x-modal>

    {{-- Toast --}}
    <div id="toastContainer" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>

@endsection

@php
$script = '
<script>
    const CSRF = "' . csrf_token() . '";

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

    // ─── Open Add Modal ───────────────────────────────────
    function openAddModal() {
        document.getElementById("modalTitle").innerHTML = `<iconify-icon icon="lucide:plus-circle" class="text-orange-500 text-lg"></iconify-icon> Thêm tấm dư vào Kho DC`;
        document.getElementById("saveBtnText").textContent = "Lưu vào Kho DC";
        document.getElementById("stockId").value = "";
        document.getElementById("f_color_code").value = "";
        document.getElementById("f_note").value = "";
        document.getElementById("f_height").value = "";
        document.getElementById("f_width").value = "";
        document.getElementById("f_quantity").value = 1;
        document.getElementById("f_location").value = "";
        document.getElementById("f_status").value = "available";
        openModal("dcStockModal");
        setTimeout(() => document.getElementById("f_color_code").focus(), 200);
    }

    // ─── Open Edit Modal ──────────────────────────────────
    function openEditModal(item) {
        document.getElementById("modalTitle").innerHTML = `<iconify-icon icon="lucide:edit-2" class="text-orange-500 text-lg"></iconify-icon> Chỉnh sửa tấm dư`;
        document.getElementById("saveBtnText").textContent = "Lưu thay đổi";
        document.getElementById("stockId").value = item.id;
        document.getElementById("f_color_code").value = item.color_code;
        document.getElementById("f_note").value = item.note || "";
        document.getElementById("f_height").value = item.height;
        document.getElementById("f_width").value = item.width;
        document.getElementById("f_quantity").value = item.quantity;
        document.getElementById("f_location").value = item.location || "";
        document.getElementById("f_status").value = item.status;
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

        const payload = {
            color_code : document.getElementById("f_color_code").value.trim().toUpperCase(),
            note       : document.getElementById("f_note").value.trim(),
            height     : parseInt(document.getElementById("f_height").value),
            width      : parseInt(document.getElementById("f_width").value),
            quantity   : parseInt(document.getElementById("f_quantity").value),
            location   : document.getElementById("f_location").value.trim().toUpperCase(),
            status     : document.getElementById("f_status").value,
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
</script>
';
@endphp
