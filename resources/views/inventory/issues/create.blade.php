@extends('layout.layout')
@php
    $title    = 'Xuất kho';
    $subTitle = 'Tạo phiếu xuất mới';
@endphp

@section('content')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12">
        <div class="card p-0 rounded-xl border-0 bg-white shadow-sm mb-6">
            <div class="p-4 border-b border-neutral-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('inventory.issues.index') }}" class="w-8 h-8 rounded-lg bg-neutral-100 hover:bg-neutral-200 flex items-center justify-center text-neutral-600 transition-colors">
                        <iconify-icon icon="solar:arrow-left-outline" class="text-lg"></iconify-icon>
                    </a>
                    <div>
                        <h5 class="font-bold text-base text-neutral-800 mb-0">Tạo Phiếu Xuất kho (PXK)</h5>
                        <p class="text-xs text-neutral-500 mb-0">Xuất nguyên vật liệu cho xưởng sản xuất hoặc giao hàng.</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('inventory.issues.store') }}" method="POST">
                @csrf
                <div class="p-6">
                    {{-- Master Info Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 bg-neutral-50 p-4 rounded-xl border border-neutral-200">
                        <div>
                            <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Mã phiếu xuất <span class="text-danger-500">*</span></label>
                            <input type="text" name="code" value="{{ $nextCode }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs font-bold text-rose-700 focus:border-rose-500" required>
                        </div>
                        <div>
                            <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Ngày xuất <span class="text-danger-500">*</span></label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-rose-500" required>
                        </div>
                        <div>
                            <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Kho xuất <span class="text-danger-500">*</span></label>
                            <select name="warehouse_id" id="select_warehouse" class="form-select rounded-lg w-full border border-neutral-300 px-3 py-2 text-xs font-semibold text-neutral-800 bg-white focus:border-rose-500" required>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ (string)old('warehouse_id', request('warehouse_id', $selectedWarehouseId ?? '')) === (string)$wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Người / Bộ phận nhận <span class="text-danger-500">*</span></label>
                            <input type="text" name="recipient" value="Xưởng sản xuất Gervin" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-rose-500" required>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="form-label text-xs font-bold text-neutral-700 block mb-0">Đơn hàng liên quan <span class="text-neutral-400 font-normal">(Tùy chọn)</span></label>
                                <button type="button" id="btnLoadOrderItems" onclick="loadSelectedOrderSupplies()" class="text-[11px] text-rose-600 hover:text-rose-800 font-bold flex items-center gap-0.5 {{ empty($selectedOrder) ? 'hidden' : '' }}" title="Nạp danh sách vật tư từ đơn hàng này vào bảng bên dưới">
                                    <iconify-icon icon="solar:download-square-bold"></iconify-icon> Nạp vật tư đơn
                                </button>
                            </div>
                            <select name="order_id" id="select_order" class="form-select rounded-lg w-full border border-neutral-300 px-3 py-2 text-xs font-semibold text-neutral-800 bg-white focus:border-rose-500" onchange="onOrderChanged(this)">
                                <option value="">-- Không gắn đơn hàng --</option>
                                @if(!empty($selectedOrder) && !$orders->contains('id', $selectedOrder->id))
                                    <option value="{{ $selectedOrder->id }}" selected>
                                        [{{ $selectedOrder->order_code }}] {{ $selectedOrder->customer_name ?: 'Khách hàng' }}
                                    </option>
                                @endif
                                @foreach($orders ?? [] as $ord)
                                    <option value="{{ $ord->id }}" {{ (string)old('order_id', request('order_id')) === (string)$ord->id ? 'selected' : '' }}>
                                        [{{ $ord->order_code }}] {{ $ord->customer_name ?: 'Khách hàng' }} ({{ date('d/m/Y', strtotime($ord->order_date)) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Lý do xuất</label>
                            <select name="reason" class="form-select rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-rose-500">
                                <option value="Xuất sản xuất">Xuất sản xuất xưởng</option>
                                <option value="Xuất bán hàng">Xuất bán vật tư</option>
                                <option value="Xuất chuyển kho">Xuất chuyển kho</option>
                                <option value="Xuất bảo hành">Xuất bảo hành / sửa chữa</option>
                                <option value="Xuất hủy">Xuất hủy hỏng</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Người xuất (Thủ kho)</label>
                            <input type="text" name="deliverer" value="{{ auth()->user()->name ?? 'Thủ kho' }}" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-rose-500">
                        </div>
                        <div>
                            <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Ghi chú phiếu xuất</label>
                            <input type="text" name="notes" class="form-control rounded-lg w-full border-neutral-200 px-3 py-2 text-xs focus:border-rose-500" placeholder="Xuất theo lệnh sản xuất số, đơn hàng..." value="{{ !empty($selectedOrder) ? 'Xuất kho cho đơn hàng #' . $selectedOrder->order_code : '' }}">
                        </div>
                    </div>

                    {{-- Items Details Table --}}
                    <div class="border border-neutral-200 rounded-xl overflow-hidden mb-6">
                        <div class="p-3 bg-neutral-100 border-b border-neutral-200 flex items-center justify-between">
                            <h6 class="font-bold text-neutral-800 text-xs uppercase mb-0">Danh sách vật tư xuất kho</h6>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="openQuickMaterialModal()" class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded-lg text-xs font-bold flex items-center gap-1 shadow-sm transition-all" title="Thêm mới mã nguyên vật liệu hoặc thành phẩm">
                                    <iconify-icon icon="solar:box-minimalistic-bold" class="text-sm"></iconify-icon> Thêm mới mã NVL, TP
                                </button>
                                <button type="button" onclick="addItemRow()" class="btn btn-sm btn-primary px-3 py-1 rounded-lg text-xs font-bold flex items-center gap-1 shadow-sm">
                                    <iconify-icon icon="ic:baseline-plus" class="text-sm"></iconify-icon> Thêm dòng
                                </button>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left border-collapse" id="itemsTable">
                                <thead class="bg-neutral-50 text-neutral-700 font-bold border-b border-neutral-200">
                                    <tr>
                                        <th class="py-2 px-3 w-12 text-center">STT</th>
                                        <th class="py-2 px-3 min-w-[280px]" id="thMaterialCol">
                                            <div class="flex items-center justify-between gap-2">
                                                <span>Mặt hàng vật tư (Mã ván / Chỉ dán) <span class="text-danger-500">*</span></span>
                                                <button type="button" onclick="openQuickMaterialModal()" class="text-[11px] text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2 py-0.5 rounded font-bold flex items-center gap-0.5 transition-all">
                                                    <iconify-icon icon="ic:baseline-plus"></iconify-icon> Thêm mới NVL / TP
                                                </button>
                                            </div>
                                        </th>
                                        <th class="py-2 px-3 w-20 text-center">ĐVT</th>
                                        <th class="py-2 px-3 w-24 text-center">Cao (mm)</th>
                                        <th class="py-2 px-3 w-24 text-center">Rộng (mm)</th>
                                        <th class="py-2 px-3 w-28 text-right bg-amber-50 text-amber-900">Tồn hiện tại</th>
                                        <th class="py-2 px-3 w-32 text-right">Số lượng xuất <span class="text-danger-500">*</span></th>
                                        <th class="py-2 px-3 min-w-[160px]">Ghi chú dòng</th>
                                        <th class="py-2 px-3 w-12 text-center"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody" class="divide-y divide-neutral-100 bg-white">
                                    {{-- Row template populated by JS --}}
                                </tbody>
                                <tfoot class="bg-neutral-50 font-bold border-t-2 border-neutral-300 text-xs">
                                    <tr>
                                        <td colspan="6" class="py-3 px-3 text-right text-neutral-700 uppercase">TỔNG CỘNG XUẤT:</td>
                                        <td class="py-3 px-3 text-right text-rose-700 font-black text-sm" id="lblTotalQty">0</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Actions Bar --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200">
                        <a href="{{ route('inventory.issues.index') }}" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-5 py-2 rounded-lg text-xs font-semibold">
                            Hủy bỏ
                        </a>
                        <button type="submit" class="btn btn-primary px-6 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm">
                            <iconify-icon icon="solar:diskette-bold" class="text-base"></iconify-icon> Xác nhận Xuất kho
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL THÊM NHANH MÃ VẬT TƯ / THÀNH PHẨM (NVL, TP) --}}
<div id="quickMaterialModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-neutral-900 bg-opacity-50 transition-opacity" onclick="closeQuickMaterialModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="p-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="solar:box-minimalistic-bold" class="text-xl text-emerald-600"></iconify-icon>
                    <div>
                        <h5 class="font-bold text-neutral-800 text-sm mb-0">Thêm nhanh Hàng hóa / Vật tư (NVL, Thành phẩm)</h5>
                        <p class="text-[11px] text-neutral-500 mb-0">Tạo mã mới và tự động nạp thẳng vào dòng phiếu xuất kho hiện tại</p>
                    </div>
                </div>
                <button type="button" onclick="closeQuickMaterialModal()" class="text-neutral-400 hover:text-neutral-700 text-xl font-bold leading-none">&times;</button>
            </div>

            <form id="quickMaterialForm" onsubmit="handleQuickMaterialSubmit(event)" class="p-5 space-y-4 max-h-[78vh] overflow-y-auto">
                <input type="hidden" id="quick_mat_target_row" value="">
                <input type="hidden" id="quick_selected_type_id" value="">

                {{-- CHỌN NHANH TỪ BẢNG GIÁ TẤM --}}
                <div class="p-3 bg-orange-50/80 border border-orange-200 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-orange-900 flex items-center gap-1.5">
                            <iconify-icon icon="solar:magic-stick-3-bold" class="text-orange-600 text-sm"></iconify-icon>
                            Lấy nhanh data từ Bảng giá tấm (Nhập mã màu & loại ván)
                        </span>
                        <span id="quickBoardMatchPrice" class="text-xs font-bold text-emerald-700"></span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label text-[11px] font-bold text-neutral-600 mb-1 block">Mã màu ván</label>
                            <input type="text" id="quick_lookup_color" list="quickColorList" oninput="onQuickBoardLookup()"
                                   class="form-control rounded-lg w-full border-neutral-300 px-3 py-1.5 text-xs font-mono font-bold uppercase focus:border-orange-500"
                                   placeholder="Vd: GV01, PARC 01, GV26**...">
                            <datalist id="quickColorList">
                                @foreach($colorCodes ?? [] as $code)
                                    <option value="{{ $code }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="form-label text-[11px] font-bold text-neutral-600 block mb-0">Loại ván (Cốt ván)</label>
                                <span class="text-[10px] text-neutral-400 font-semibold">Chọn nhanh:</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1 mb-1.5">
                                <button type="button" onclick="selectQuickTypeShortcut(2)" id="btnQuickType_2"
                                        class="quick-type-btn px-1.5 py-1 rounded text-[11px] font-bold border transition-all bg-emerald-600 text-white border-emerald-600 shadow-xs">
                                    MDF 1M
                                </button>
                                <button type="button" onclick="selectQuickTypeShortcut(3)" id="btnQuickType_3"
                                        class="quick-type-btn px-1.5 py-1 rounded text-[11px] font-bold border transition-all bg-white text-neutral-700 border-neutral-300 hover:bg-neutral-50">
                                    MDF 2M
                                </button>
                                <button type="button" onclick="selectQuickTypeShortcut(5)" id="btnQuickType_5"
                                        class="quick-type-btn px-1.5 py-1 rounded text-[11px] font-bold border transition-all bg-white text-neutral-700 border-neutral-300 hover:bg-neutral-50">
                                    Nhựa PVC
                                </button>
                            </div>
                            <select id="quick_lookup_type" onchange="onQuickTypeSelectChange()"
                                    class="form-select rounded-lg w-full border-neutral-300 px-3 py-1 text-xs focus:border-orange-500 font-semibold">
                                <option value="">-- Chọn loại ván --</option>
                                @foreach($boardTypes ?? [] as $bt)
                                    <option value="{{ $bt->id }}" data-prefix="{{ $bt->prefix }}" data-name="{{ $bt->name }}" {{ $bt->id == 2 ? 'selected' : '' }}>
                                        {{ $bt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div id="quickBoardMatchNote" class="text-[11px] text-emerald-800 font-medium hidden"></div>
                </div>

                {{-- Thông tin chi tiết --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Mã SKU / Mã vật tư <span class="text-danger-500">*</span></label>
                        <input type="text" id="quick_mat_code" class="form-control rounded-lg w-full border-neutral-300 px-3 py-2 text-xs font-mono font-bold uppercase focus:border-primary-500" placeholder="Vd: GV120.TP" required>
                    </div>
                    <div>
                        <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Mã xuất xứ (NCC)</label>
                        <input type="text" id="quick_mat_origin_code" class="form-control rounded-lg w-full border-neutral-300 px-3 py-2 text-xs focus:border-primary-500" placeholder="Vd: 2201">
                    </div>
                </div>

                <div>
                    <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Tên Foil thay thế</label>
                    <input type="text" id="quick_mat_foil_name" class="form-control rounded-lg w-full border-neutral-300 px-3 py-2 text-xs focus:border-primary-500" placeholder="Tên hàng Foil thay thế tên cũ (nếu có)">
                </div>

                <div>
                    <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Tên vật tư / Quy cách <span class="text-danger-500">*</span></label>
                    <input type="text" id="quick_mat_name" class="form-control rounded-lg w-full border-neutral-300 px-3 py-2 text-xs focus:border-primary-500 font-medium" placeholder="Vd: Tấm GV120 - MDF 1 mặt Acrylic 1 mặt Bakerlic" required>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Tính chất <span class="text-danger-500">*</span></label>
                        <select id="quick_mat_nature" class="form-select rounded-lg w-full border-neutral-300 px-3 py-2 text-xs focus:border-primary-500 font-semibold text-neutral-800">
                            <option value="Vật tư hàng hóa" selected>Vật tư hàng hóa</option>
                            <option value="Thành phẩm">Thành phẩm</option>
                            <option value="Bán thành phẩm">Bán thành phẩm</option>
                            <option value="Dịch vụ">Dịch vụ</option>
                            <option value="Công cụ dụng cụ">Công cụ dụng cụ</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Kho ngầm định <span class="text-danger-500">*</span></label>
                        <select id="quick_mat_warehouse_id" class="form-select rounded-lg w-full border-neutral-300 px-3 py-2 text-xs focus:border-primary-500 font-semibold text-neutral-800">
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}" {{ (string)old('warehouse_id', request('warehouse_id', $selectedWarehouseId ?? '')) === (string)$wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }} ({{ $wh->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Nhóm hàng / Phân loại</label>
                        <input type="text" id="quick_mat_category" list="quickCategoryOptions" class="form-control rounded-lg w-full border-neutral-300 px-3 py-2 text-xs focus:border-primary-500" placeholder="Vd: Acrylic TQ, MDF...">
                        <datalist id="quickCategoryOptions">
                            @foreach($categories ?? [] as $cat)
                                <option value="{{ $cat }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div>
                        <label class="form-label text-xs font-bold text-neutral-700 mb-1 block">Đơn vị tính <span class="text-danger-500">*</span></label>
                        <input type="text" id="quick_mat_unit" value="Tấm" class="form-control rounded-lg w-full border-neutral-300 px-3 py-2 text-xs focus:border-primary-500 font-bold" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Giá mua gần nhất (đ)</label>
                        <input type="number" id="quick_mat_cost_price" value="0" min="0" step="1000" class="form-control rounded-lg w-full border-neutral-300 px-3 py-2 text-xs focus:border-primary-500 font-semibold">
                    </div>
                    <div>
                        <label class="form-label text-xs font-semibold text-neutral-700 mb-1 block">Định mức tồn min</label>
                        <input type="number" id="quick_mat_min_stock" value="50" min="0" class="form-control rounded-lg w-full border-neutral-300 px-3 py-2 text-xs focus:border-primary-500">
                    </div>
                </div>

                {{-- Thông số chỉ dán viền đi kèm --}}
                <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-purple-900 flex items-center gap-1.5">
                            <iconify-icon icon="solar:ruler-angular-bold" class="text-base text-purple-600"></iconify-icon>
                            Chỉ dán viền tương ứng (Tự động tạo ở Kho Dán Chỉ)
                        </span>
                        <span class="text-[11px] text-purple-600 font-semibold">SKU: CHI-[Mã tấm]</span>
                    </div>
                    <div>
                        <label class="form-label text-[11px] font-semibold text-neutral-700 mb-1 block">Giá mua gần nhất chỉ (đ/m)</label>
                        <input type="number" id="quick_edge_band_cost_price" value="0" min="0" step="100" class="form-control rounded-lg w-full border-purple-200 px-3 py-1.5 text-xs focus:border-purple-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-neutral-200">
                    <button type="button" onclick="closeQuickMaterialModal()" class="btn border border-neutral-300 text-neutral-700 hover:bg-neutral-100 px-4 py-2 rounded-lg text-xs font-semibold">
                        Hủy
                    </button>
                    <button type="submit" id="btnSubmitQuickMaterial" class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm transition-all">
                        <iconify-icon icon="solar:check-circle-bold" class="text-sm"></iconify-icon> Lưu & Chọn vật tư
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .issue-ts-wrapper {
        width: 100% !important;
    }
    .issue-ts-wrapper .ts-control {
        border-radius: 0.5rem !important;
        border: 1px solid #e5e7eb !important;
        padding: 5px 10px !important;
        font-size: 12px !important;
        min-height: 36px !important;
        line-height: 1.25rem !important;
        background-color: #ffffff !important;
        box-shadow: none !important;
        display: flex !important;
        align-items: center !important;
        color: #1f2937 !important;
    }
    .issue-ts-wrapper.focus .ts-control {
        border-color: #e11d48 !important;
        box-shadow: 0 0 0 2px rgba(225, 29, 72, 0.15) !important;
    }
    .issue-ts-dropdown {
        font-size: 12px !important;
        border-radius: 0.5rem !important;
        border: 1px solid #e5e7eb !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08) !important;
        z-index: 99999 !important;
        max-height: 320px !important;
        overflow-y: auto !important;
        background-color: #ffffff !important;
    }
    .issue-ts-dropdown .ts-dropdown-content {
        max-height: 320px !important;
    }
    .issue-ts-dropdown .active {
        background-color: #fff1f2 !important;
        color: #be123c !important;
    }
    select.tomselected,
    select.material-tom-select.tomselected {
        display: none !important;
    }
</style>
@endsection

@push('scripts')
<script>
    const WAREHOUSES = @json($warehouses);
    const MATERIALS = @json($materials);
    const PREFILLED_ORDER_ITEMS = @json($orderItems ?? []);

    function getCurrentWarehouse() {
        const whSelect = document.getElementById('select_warehouse');
        const whId = whSelect ? whSelect.value : '';
        return WAREHOUSES.find(w => String(w.id) === String(whId)) || null;
    }

    function isEdgeBandWarehouse() {
        const wh = getCurrentWarehouse();
        if (!wh) return false;
        const code = (wh.code || '').toUpperCase();
        return code === 'KHO-CHI' || code.includes('CHI') || wh.name.toLowerCase().includes('chỉ');
    }

    function getFilteredMaterialOptions() {
        return MATERIALS.map(m => ({
            value: String(m.id),
            code: m.code || '',
            name: m.name || '',
            origin_code: m.origin_code || '',
            category: m.category || '',
            unit: m.unit || (m.item_type === 'edge_band' ? 'm' : 'Tấm'),
            stock: m.current_stock || 0,
            item_type: m.item_type
        }));
    }

    function onWarehouseChange() {
        const isEdge = isEdgeBandWarehouse();
        const thCol = document.getElementById('thMaterialCol');
        if (thCol) {
            thCol.innerHTML = isEdge 
                ? `<div class="flex items-center justify-between gap-2"><span>Mặt hàng Chỉ Dán Viền (Mã CHI-...) <span class="text-danger-500">*</span></span><button type="button" onclick="openQuickMaterialModal()" class="text-[11px] text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2 py-0.5 rounded font-bold flex items-center gap-0.5 transition-all"><iconify-icon icon="ic:baseline-plus"></iconify-icon> Thêm mới NVL / TP</button></div>`
                : `<div class="flex items-center justify-between gap-2"><span>Mặt hàng vật tư (Mã ván / Chỉ dán) <span class="text-danger-500">*</span></span><button type="button" onclick="openQuickMaterialModal()" class="text-[11px] text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2 py-0.5 rounded font-bold flex items-center gap-0.5 transition-all"><iconify-icon icon="ic:baseline-plus"></iconify-icon> Thêm mới NVL / TP</button></div>`;
        }

        const filtered = getFilteredMaterialOptions();
        const rows = document.querySelectorAll('#itemsTableBody tr');
        rows.forEach(r => {
            const selectEl = r.querySelector('.material-tom-select');
            if (selectEl && selectEl._tomSelect) {
                const ts = selectEl._tomSelect;
                const curVal = ts.getValue();
                const curOpt = curVal && ts.options ? ts.options[curVal] : null;
                ts.clear();
                ts.clearOptions();
                ts.addOptions(filtered);
                if (curVal && curOpt && !filtered.some(opt => opt.value === curVal)) {
                    ts.addOption(curOpt);
                }
                ts.refreshOptions(false);
                if (curVal) {
                    ts.setValue(curVal, true);
                    onMaterialSelected(r.id.replace('row_', ''), curVal);
                } else {
                    r.querySelector('.unit-cell').textContent = isEdge ? 'm' : 'Tấm';
                    r.querySelector('.stock-cell').textContent = '-';
                    r.removeAttribute('data-max-stock');
                }
            }
        });
        calcTotals();
    }

    let rowCount = 0;

    function addItemRow(selectedMaterialId = '', qty = '', notes = '', height = '', width = '') {
        const tbody = document.getElementById('itemsTableBody');
        const tr = document.createElement('tr');
        const thisRowIdx = rowCount;
        const isEdge = isEdgeBandWarehouse();
        tr.id = `row_${thisRowIdx}`;
        tr.className = 'hover:bg-neutral-50 transition-colors';

        tr.innerHTML = `
            <td class="py-2 px-3 text-center text-neutral-400 font-medium row-stt">${tbody.children.length + 1}</td>
            <td class="py-2 px-3">
                <select id="select_mat_${thisRowIdx}" name="items[${thisRowIdx}][material_id]" class="material-tom-select w-full" required>
                    <option value="">-- Chọn / tìm ${isEdge ? 'mã chỉ dán xuất kho...' : 'mã ván xuất kho...'} --</option>
                </select>
            </td>
            <td class="py-2 px-3 text-center text-neutral-600 unit-cell">${isEdge ? 'm' : 'Tấm'}</td>
            <td class="py-2 px-3 text-center">
                <input type="number" name="items[${thisRowIdx}][height]" value="${height !== '' && height !== null ? height : (isEdge ? '' : '2440')}" step="any" min="0" class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-center text-xs focus:border-rose-500 height-input" placeholder="${isEdge ? '—' : '2440'}">
            </td>
            <td class="py-2 px-3 text-center">
                <input type="number" name="items[${thisRowIdx}][width]" value="${width !== '' && width !== null ? width : (isEdge ? '' : '1220')}" step="any" min="0" class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-center text-xs focus:border-rose-500 width-input" placeholder="${isEdge ? '—' : '1220'}">
            </td>
            <td class="py-2 px-3 text-right font-bold text-amber-900 bg-amber-50 bg-opacity-40 stock-cell">-</td>
            <td class="py-2 px-3 text-right">
                <input type="number" name="items[${thisRowIdx}][quantity]" value="${qty}" step="any" min="0.001" class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-right text-xs font-bold text-rose-700 focus:border-rose-500 qty-input" required oninput="calcTotals()">
                <div class="text-[10px] text-danger-600 stock-warn hidden mt-1 font-semibold">Vượt số lượng tồn!</div>
            </td>
            <td class="py-2 px-3">
                <input type="text" name="items[${thisRowIdx}][notes]" value="${notes}" class="form-control rounded-lg w-full border-neutral-200 px-2 py-1 text-xs" placeholder="Ghi chú...">
            </td>
            <td class="py-2 px-3 text-center">
                <button type="button" onclick="removeRow(${thisRowIdx})" class="text-danger-500 hover:text-danger-700 p-1 text-base leading-none" title="Xóa dòng">&times;</button>
            </td>
        `;

        tbody.appendChild(tr);
        rowCount++;

        initTomSelectForRow(thisRowIdx, selectedMaterialId);
        updateStt();
        calcTotals();
    }

    function initTomSelectForRow(rowIdx, selectedMaterialId) {
        const selectEl = document.getElementById(`select_mat_${rowIdx}`);
        if (!selectEl) return;

        const isEdge = isEdgeBandWarehouse();
        const options = getFilteredMaterialOptions();

        if (typeof TomSelect !== 'undefined') {
            const ts = new TomSelect(selectEl, {
                options: options,
                valueField: 'value',
                labelField: 'code',
                searchField: ['code', 'origin_code', 'name', 'category'],
                create: false,
                maxItems: 1,
                placeholder: isEdge ? '-- Tìm kiếm mã chỉ dán CHI-... --' : '-- Tìm kiếm mã ván, tên màu... --',
                allowEmptyOption: true,
                dropdownParent: 'body',
                wrapperClass: 'ts-wrapper issue-ts-wrapper',
                dropdownClass: 'ts-dropdown issue-ts-dropdown',
                render: {
                    option: function(data, escape) {
                        const originHtml = data.origin_code 
                            ? `<span class="inline-block bg-amber-50 text-amber-700 border border-amber-200 rounded px-1 text-[10px] font-bold ml-1">${escape(data.origin_code)}</span>`
                            : '';
                        const catHtml = data.category
                            ? `<span class="text-neutral-400 text-[11px]">&bull; ${escape(data.category)}</span>`
                            : '';
                        const stockFormatted = Number(data.stock || 0).toLocaleString('vi-VN');
                        const isOutOfStock = Number(data.stock || 0) <= 0;
                        const stockBadge = isOutOfStock
                            ? `<span class="text-[11px] font-bold text-rose-700 bg-rose-50 border border-rose-200 rounded px-2 py-1">Hết hàng (0 ${escape(data.unit)})</span>`
                            : `<span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded px-2 py-1">Tồn: ${stockFormatted} ${escape(data.unit)}</span>`;

                        return `
                            <div class="py-2 px-3 flex items-center justify-between border-b border-neutral-100 hover:bg-rose-50 transition-colors">
                                <div class="flex-1 min-w-0 pr-2">
                                    <div class="flex items-center">
                                        <span class="font-bold text-neutral-800 text-xs">${escape(data.code)}</span>
                                        ${originHtml}
                                    </div>
                                    <div class="text-[11px] text-neutral-600 truncate mt-1">
                                        ${escape(data.name)} ${catHtml}
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    ${stockBadge}
                                </div>
                            </div>
                        `;
                    },
                    item: function(data, escape) {
                        const originText = data.origin_code ? ` [${escape(data.origin_code)}]` : '';
                        return `<div class="truncate text-xs font-semibold text-neutral-800">
                            <span class="font-bold text-rose-700">${escape(data.code)}</span>${originText} - ${escape(data.name)}
                        </div>`;
                    },
                    no_results: function(data, escape) {
                        return `
                            <div class="p-3 text-center text-xs text-neutral-600 bg-neutral-50 border-t border-neutral-100">
                                <div class="mb-2 font-medium">Không tìm thấy mã "<strong>${escape(data.input)}</strong>"</div>
                                <button type="button" onmousedown="event.preventDefault(); openQuickMaterialModal(${rowIdx}, '${escape(data.input)}');" 
                                        class="btn btn-xs bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1.5 rounded-lg text-xs shadow-sm inline-flex items-center gap-1 transition-all">
                                    <iconify-icon icon="ic:baseline-plus"></iconify-icon> Thêm mới mã "${escape(data.input)}" ngay
                                </button>
                            </div>
                        `;
                    }
                },
                onDropdownOpen: function() {
                    let header = this.dropdown.querySelector('.ts-custom-quick-add-header');
                    if (!header) {
                        header = document.createElement('div');
                        header.className = 'ts-custom-quick-add-header p-2 bg-emerald-50 border-b border-emerald-200 flex items-center justify-between sticky top-0 z-20 shadow-xs';
                        header.innerHTML = `
                            <span class="text-[11px] font-bold text-emerald-900 flex items-center gap-1">
                                <iconify-icon icon="solar:box-minimalistic-bold" class="text-emerald-600 text-sm"></iconify-icon>
                                Danh sách NVL, Thành phẩm
                            </span>
                            <button type="button" onmousedown="event.preventDefault(); openQuickMaterialModal(${rowIdx});" 
                                    class="btn btn-xs bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-2 py-1 rounded text-[11px] flex items-center gap-1 shadow-xs transition-all">
                                <iconify-icon icon="ic:baseline-plus"></iconify-icon> + Thêm mới mã NVL, TP
                            </button>
                        `;
                        this.dropdown.insertBefore(header, this.dropdown.firstChild);
                    }
                },
                onChange: function(val) {
                    onMaterialSelected(rowIdx, val);
                }
            });

            selectEl._tomSelect = ts;

            if (selectedMaterialId) {
                const strId = String(selectedMaterialId);
                if (!ts.options[strId]) {
                    const foundMat = MATERIALS.find(m => String(m.id) === strId);
                    if (foundMat) {
                        ts.addOption({
                            value: String(foundMat.id),
                            code: foundMat.code || '',
                            name: foundMat.name || '',
                            origin_code: foundMat.origin_code || '',
                            category: foundMat.category || '',
                            unit: foundMat.unit || (foundMat.item_type === 'edge_band' ? 'm' : 'Tấm'),
                            stock: foundMat.current_stock || 0,
                            item_type: foundMat.item_type
                        });
                    }
                }
                ts.setValue(strId, true);
                onMaterialSelected(rowIdx, strId);
            }
        }
    }

    function onMaterialSelected(rowIdx, materialId) {
        const tr = document.getElementById(`row_${rowIdx}`);
        if (!tr) return;

        const mat = MATERIALS.find(m => String(m.id) === String(materialId));
        if (!mat) {
            tr.querySelector('.unit-cell').textContent = 'Tấm';
            tr.querySelector('.stock-cell').textContent = '-';
            tr.removeAttribute('data-max-stock');
            return;
        }

        const unit = mat.unit || 'Tấm';
        const stock = parseFloat(mat.current_stock || 0);

        tr.querySelector('.unit-cell').textContent = unit;
        tr.querySelector('.stock-cell').textContent = stock.toLocaleString('vi-VN');
        tr.setAttribute('data-max-stock', stock);

        const heightInput = tr.querySelector('.height-input');
        const widthInput = tr.querySelector('.width-input');
        const isMatEdge = mat.item_type === 'edge_band' || (mat.code && String(mat.code).startsWith('CHI-'));
        if (heightInput) {
            heightInput.disabled = false;
            if (isMatEdge) {
                heightInput.placeholder = '—';
            } else {
                heightInput.placeholder = '2440';
                if (!heightInput.value) heightInput.value = '2440';
            }
        }
        if (widthInput) {
            widthInput.disabled = false;
            if (isMatEdge) {
                widthInput.placeholder = '—';
            } else {
                widthInput.placeholder = '1220';
                if (!widthInput.value) widthInput.value = '1220';
            }
        }

        calcTotals();
    }

    function removeRow(rowIdx) {
        const tr = document.getElementById(`row_${rowIdx}`);
        if (tr) {
            const selectEl = document.getElementById(`select_mat_${rowIdx}`);
            if (selectEl && selectEl._tomSelect) {
                selectEl._tomSelect.destroy();
            }
            tr.remove();
        }
        updateStt();
        calcTotals();
    }

    function updateStt() {
        const rows = document.querySelectorAll('#itemsTableBody tr');
        rows.forEach((r, idx) => {
            const sttCell = r.querySelector('.row-stt');
            if (sttCell) sttCell.textContent = idx + 1;
        });
    }

    function calcTotals() {
        let totalQty = 0;
        const rows = document.querySelectorAll('#itemsTableBody tr');

        rows.forEach(r => {
            const qtyInput = r.querySelector('.qty-input');
            const qty = parseFloat(qtyInput?.value || 0);
            const maxStock = parseFloat(r.getAttribute('data-max-stock') || 999999);
            const warnElem = r.querySelector('.stock-warn');

            if (warnElem) {
                if (qty > maxStock && maxStock >= 0) {
                    warnElem.classList.remove('hidden');
                } else {
                    warnElem.classList.add('hidden');
                }
            }

            totalQty += qty;
        });

        document.getElementById('lblTotalQty').textContent = totalQty.toLocaleString('vi-VN');
    }

    // Initialize rows
    function initDefaultRows() {
        const whEl = document.getElementById("select_warehouse");
        if (whEl) {
            whEl.addEventListener('change', onWarehouseChange);
        }
        onWarehouseChange();

        if (PREFILLED_ORDER_ITEMS && PREFILLED_ORDER_ITEMS.length > 0) {
            PREFILLED_ORDER_ITEMS.forEach(it => {
                addItemRow(it.material_id, it.quantity, it.notes || '', it.height || '', it.width || '');
            });
        } else {
            addItemRow();
            addItemRow();
            addItemRow();
        }
    }

    // Order items loader
    function onOrderChanged(selectEl) {
        const btn = document.getElementById('btnLoadOrderItems');
        if (selectEl.value) {
            if (btn) btn.classList.remove('hidden');
            loadSelectedOrderSupplies(true);

            // Gợi ý ghi chú xuất kho theo đơn hàng
            const notesInput = document.querySelector('input[name="notes"]');
            if (notesInput && (!notesInput.value || notesInput.value.startsWith('Xuất kho cho đơn hàng'))) {
                const optText = selectEl.options[selectEl.selectedIndex]?.text || '';
                const match = optText.match(/\[(.*?)\]/);
                const orderCode = match ? match[1] : '';
                if (orderCode) {
                    notesInput.value = 'Xuất kho cho đơn hàng #' + orderCode;
                }
            }
        } else {
            if (btn) btn.classList.add('hidden');
        }
    }

    function loadSelectedOrderSupplies(forceClean = false) {
        const orderId = document.getElementById('select_order')?.value;
        if (!orderId) {
            if (!forceClean) alert('Vui lòng chọn đơn hàng trước.');
            return;
        }

        const btn = document.getElementById('btnLoadOrderItems');
        if (btn) btn.innerHTML = 'Đang tải...';

        const whId = document.getElementById('select_warehouse')?.value || '';
        fetch(`/warehouses/orders/${orderId}/supplies?type=issue&warehouse_id=${whId}`)
            .then(res => res.json())
            .then(data => {
                if (btn) btn.innerHTML = '<iconify-icon icon="solar:download-square-bold"></iconify-icon> Nạp vật tư đơn';
                if (data.success && data.items && data.items.length > 0) {
                    const tbody = document.getElementById('itemsTableBody');
                    if (!forceClean && tbody && tbody.children.length > 0) {
                        const hasFilled = Array.from(tbody.querySelectorAll('.qty-input')).some(i => parseFloat(i.value) > 0);
                        if (hasFilled && !confirm('Bạn có muốn xóa các dòng hiện tại để nạp lại vật tư từ đơn hàng ' + data.order_code + '?')) {
                            data.items.forEach(it => {
                                addItemRow(it.material_id, it.quantity, it.notes || '', it.height || '', it.width || '');
                            });
                            return;
                        }
                    }

                    if (tbody) tbody.innerHTML = '';
                    data.items.forEach(it => {
                        addItemRow(it.material_id, it.quantity, it.notes || '', it.height || '', it.width || '');
                    });
                } else {
                    alert('Đơn hàng ' + (data.order_code || '') + ' không có vật tư phù hợp để xuất kho.');
                }
            })
            .catch(err => {
                if (btn) btn.innerHTML = '<iconify-icon icon="solar:download-square-bold"></iconify-icon> Nạp vật tư đơn';
                if (!forceClean) alert('Không thể tải vật tư từ đơn hàng.');
            });
    }

    // ===== QUICK MATERIAL / NVL, TP CREATION =====
    function openQuickMaterialModal(targetRowIdx = null, prefillCode = '') {
        const modal = document.getElementById('quickMaterialModal');
        if (!modal) return;

        document.getElementById('quick_mat_target_row').value = targetRowIdx !== null ? targetRowIdx : '';
        
        // Gán kho ngầm định theo kho xuất hiện tại trên phiếu xuất
        const currentWh = document.getElementById('select_warehouse')?.value;
        if (currentWh) {
            const whSelect = document.getElementById('quick_mat_warehouse_id');
            if (whSelect) whSelect.value = currentWh;
        }

        if (prefillCode) {
            const cleanCode = prefillCode.trim().toUpperCase();
            const codeInput = document.getElementById('quick_mat_code');
            const colorInput = document.getElementById('quick_lookup_color');
            if (codeInput) codeInput.value = cleanCode;
            if (colorInput) {
                colorInput.value = cleanCode.replace(/\.TP.*$/i, '');
            }
            onQuickBoardLookup();
        }

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        setTimeout(() => {
            const targetFocus = prefillCode ? document.getElementById('quick_mat_name') : document.getElementById('quick_lookup_color');
            targetFocus?.focus();
        }, 100);
    }

    function closeQuickMaterialModal() {
        const modal = document.getElementById('quickMaterialModal');
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    let isSubmittingMaterial = false;
    async function handleQuickMaterialSubmit(e) {
        e.preventDefault();
        if (isSubmittingMaterial) return;

        const code = document.getElementById('quick_mat_code')?.value.trim();
        if (!code) {
            alert('Vui lòng nhập Mã SKU / Mã vật tư!');
            return;
        }

        const submitBtn = document.getElementById('btnSubmitQuickMaterial');
        const originalText = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<iconify-icon icon="line-md:loading-loop" class="text-sm animate-spin"></iconify-icon> Đang lưu...';
        }
        isSubmittingMaterial = true;

        const payload = {
            code: code,
            color_code: code,
            name: document.getElementById('quick_mat_name')?.value.trim() || '',
            origin_code: document.getElementById('quick_mat_origin_code')?.value.trim() || '',
            foil_name: document.getElementById('quick_mat_foil_name')?.value.trim() || '',
            nature: document.getElementById('quick_mat_nature')?.value || 'Vật tư hàng hóa',
            warehouse_id: document.getElementById('quick_mat_warehouse_id')?.value || '',
            category: document.getElementById('quick_mat_category')?.value.trim() || '',
            unit: document.getElementById('quick_mat_unit')?.value.trim() || 'Tấm',
            cost_price: document.getElementById('quick_mat_cost_price')?.value || 0,
            min_stock: document.getElementById('quick_mat_min_stock')?.value || 50,
            edge_band_cost_price: document.getElementById('quick_edge_band_cost_price')?.value || 0,
            selected_type_id: document.getElementById('quick_selected_type_id')?.value || '',
        };

        try {
            const res = await fetch('{{ route("inventory.materials.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                throw new Error(data.message || 'Không thể tạo vật tư mới');
            }

            const createdItems = data.created_items || (data.material ? [data.material] : []);
            const primaryItem = data.primary_item || createdItems[0];

            // 1. Gộp vào danh sách MATERIALS toàn cục
            createdItems.forEach(item => {
                const strId = String(item.id);
                if (!MATERIALS.some(m => String(m.id) === strId)) {
                    MATERIALS.push(item);
                }
            });

            // 2. Thêm option vào tất cả các TomSelect trên trang
            const allRows = document.querySelectorAll('#itemsTableBody tr');
            allRows.forEach(r => {
                const sel = r.querySelector('.material-tom-select');
                if (sel && sel._tomSelect) {
                    createdItems.forEach(item => {
                        const opt = {
                            value: String(item.id),
                            code: item.code || '',
                            name: item.name || '',
                            origin_code: item.origin_code || '',
                            category: item.category || '',
                            unit: item.unit || (item.item_type === 'edge_band' ? 'm' : 'Tấm'),
                            stock: item.current_stock || 0,
                            item_type: item.item_type
                        };
                        if (!sel._tomSelect.options[opt.value]) {
                            sel._tomSelect.addOption(opt);
                        }
                    });
                    sel._tomSelect.refreshOptions(false);
                }
            });

            // 3. Tự động chọn vào dòng hiện tại
            const targetRowIdx = document.getElementById('quick_mat_target_row')?.value;
            let selectedRowEl = null;
            if (targetRowIdx !== '' && targetRowIdx !== null) {
                selectedRowEl = document.getElementById(`select_mat_${targetRowIdx}`);
            } else {
                allRows.forEach(r => {
                    const s = r.querySelector('.material-tom-select');
                    if (s && s._tomSelect && !s._tomSelect.getValue() && !selectedRowEl) {
                        selectedRowEl = s;
                    }
                });
                if (!selectedRowEl && allRows.length > 0) {
                    const lastRow = allRows[allRows.length - 1];
                    selectedRowEl = lastRow.querySelector('.material-tom-select');
                }
            }

            if (selectedRowEl && selectedRowEl._tomSelect && primaryItem) {
                selectedRowEl._tomSelect.setValue(String(primaryItem.id));
            }

            closeQuickMaterialModal();
            document.getElementById('quickMaterialForm').reset();
            alert('Đã thêm thành công mã "' + (primaryItem?.code || code) + '" và nạp vào phiếu!');

        } catch (err) {
            alert('Lỗi: ' + (err.message || 'Không thể lưu vật tư mới'));
        } finally {
            isSubmittingMaterial = false;
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    }

    let quickLookupTimeout = null;
    function onQuickBoardLookup() {
        clearTimeout(quickLookupTimeout);
        quickLookupTimeout = setTimeout(async () => {
            const color = (document.getElementById("quick_lookup_color")?.value || "").trim().toUpperCase();
            const typeSelect = document.getElementById("quick_lookup_type");
            const typeId = typeSelect ? typeSelect.value : "";
            const selectedOpt = typeSelect ? typeSelect.selectedOptions[0] : null;
            const prefix = selectedOpt ? (selectedOpt.getAttribute("data-prefix") || "") : "";
            const typeName = selectedOpt ? (selectedOpt.getAttribute("data-name") || "") : "";

            if (!color && !typeId) return;

            const codeInput = document.getElementById("quick_mat_code");
            const nameInput = document.getElementById("quick_mat_name");
            const catInput = document.getElementById("quick_mat_category");
            const priceInput = document.getElementById("quick_mat_cost_price");

            if (color && codeInput) {
                codeInput.value = color + prefix;
                if (nameInput) {
                    nameInput.value = typeName ? `Tấm ${typeName} ${color}` : `Tấm Acrylic ${color}`;
                }
            }

            try {
                const res = await fetch(`/wood-boards/board-info?color_code=${encodeURIComponent(color)}&wood_board_type_id=${typeId || ""}`);
                const data = await res.json();
                if (data.success) {
                    if (data.computed_code && codeInput) codeInput.value = data.computed_code;
                    if (data.computed_name && nameInput) nameInput.value = data.computed_name;
                    if (data.price_group && catInput) catInput.value = data.price_group;
                    if (data.cost_price > 0 && priceInput) {
                        priceInput.value = data.cost_price;
                        const matchPriceEl = document.getElementById("quickBoardMatchPrice");
                        if (matchPriceEl) {
                            matchPriceEl.textContent = "Giá bảng: " + new Intl.NumberFormat("vi-VN").format(data.cost_price) + "đ";
                        }
                    }
                    const noteEl = document.getElementById("quickBoardMatchNote");
                    if (noteEl) {
                        if (data.board) {
                            noteEl.textContent = `✓ Đã khớp Bảng giá tấm: Nhóm ${data.price_group || 'chuẩn'}, độ dày ${data.computed_thickness || '17mm'}`;
                            noteEl.classList.remove("hidden");
                        } else {
                            noteEl.classList.add("hidden");
                        }
                    }
                }
            } catch (e) {
                console.error("Quick lookup board info error:", e);
            }
        }, 200);
    }

    function selectQuickTypeShortcut(id) {
        const sel = document.getElementById("quick_lookup_type");
        if (sel) sel.value = id;
        document.getElementById("quick_selected_type_id").value = id;
        updateQuickTypeButtonsUI(id);
        onQuickBoardLookup();
    }

    function onQuickTypeSelectChange() {
        const sel = document.getElementById("quick_lookup_type");
        if (sel) {
            document.getElementById("quick_selected_type_id").value = sel.value;
            updateQuickTypeButtonsUI(sel.value);
        }
        onQuickBoardLookup();
    }

    function updateQuickTypeButtonsUI(activeId) {
        document.querySelectorAll(".quick-type-btn").forEach(btn => {
            btn.className = "quick-type-btn px-1.5 py-1 rounded text-[11px] font-bold border transition-all bg-white text-neutral-700 border-neutral-300 hover:bg-neutral-50";
        });
        const btn = document.getElementById("btnQuickType_" + activeId);
        if (btn) {
            if (activeId == 1) btn.className = "quick-type-btn px-1.5 py-1 rounded text-[11px] font-bold border transition-all bg-emerald-600 text-white border-emerald-600 shadow-xs";
            else if (activeId == 2) btn.className = "quick-type-btn px-1.5 py-1 rounded text-[11px] font-bold border transition-all bg-purple-600 text-white border-purple-600 shadow-xs";
            else btn.className = "quick-type-btn px-1.5 py-1 rounded text-[11px] font-bold border transition-all bg-amber-600 text-white border-amber-600 shadow-xs";
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDefaultRows);
    } else {
        initDefaultRows();
    }
</script>
@endpush
