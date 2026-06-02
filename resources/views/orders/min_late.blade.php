{{-- Order Supplies & Items Section Card (Min Late) --}}
<style>
    /* Fix sticky column overlap bug in two-row table headers */
    .table thead tr:nth-child(2) th:last-child {
        position: static !important;
        background-color: inherit !important;
        box-shadow: none !important;
    }
    .table thead tr:nth-child(2) th:last-child::before {
        display: none !important;
    }
</style>
<div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm">
    <div class="flex items-center justify-between border-b border-neutral-100 pb-4 mb-5">
        <div class="flex items-center gap-2">
            <iconify-icon icon="lucide:package-open" class="text-xl text-primary-500"></iconify-icon>
            <h6 class="font-bold text-base text-neutral-800 m-0">Danh sách Vật tư & Sản phẩm (Min Late)</h6>
        </div>
        <button type="button" onclick="addMinLateOrderSupply()" class="btn btn-sm btn-primary rounded-lg flex items-center gap-1">
            <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon> Thêm vật tư
        </button>
    </div>
    <div id="min-late-supplies-container" class="space-y-6">
        @if(isset($acrylicOrder) && $acrylicOrder->type == 'min_late' && $acrylicOrder->supplies->count() > 0)
            @foreach($acrylicOrder->supplies as $supplyIndex => $supply)
            <div class="order-supply-row bg-neutral-50/50 border border-neutral-200 rounded-xl p-5 mb-2 relative transition-all hover:border-neutral-300 shadow-sm" data-supply-id="{{ $supply->id }}">
                
                {{-- Items inside this supply --}}
                <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                            <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                        </div>
                        <input type="text" name="supplies[{{ $supplyIndex }}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)" value="{{ $supply->supply_name }}">
                        <input type="number" name="supplies[{{ $supplyIndex }}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="0.01" value="{{ $supply->quantity ?? 0 }}">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="addMinLateOrderItem(this)" class="btn btn-sm btn-outline-primary rounded-lg flex items-center gap-1">
                            <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon>
                        </button>
                        <button type="button" onclick="this.closest('.order-supply-row').remove()" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa vật tư này">
                            <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto pb-3">
                    <table class="table bordered-table sm-table mb-0 min-w-[2000px] border border-neutral-200">
                        <thead>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" rowspan="2" class="align-middle w-[45px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Stt</th>
                                <th scope="col" rowspan="2" class="align-middle w-[160px] min-w-[160px] max-w-[160px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã hàng</th>
                                <th scope="col" rowspan="2" class="align-middle w-[180px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên hàng hóa, dịch vụ <span class="text-danger-500">*</span></th>
                                <th scope="col" colspan="2" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Kích thước (mm)</th>
                                <th scope="col" rowspan="2" class="align-middle w-[70px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng</th>
                                <th scope="col" colspan="4" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Dán cạnh</th>
                                <th scope="col" rowspan="2" class="align-middle w-[85px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán thẳng</th>
                                <th scope="col" rowspan="2" class="align-middle w-[85px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán vát</th>
                                <th scope="col" rowspan="2" class="align-middle w-[85px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán vát mòi</th>
                                <th scope="col" rowspan="2" class="align-middle w-[105px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 40-59mm</th>
                                <th scope="col" rowspan="2" class="align-middle w-[105px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 17-39mm</th>
                                <th scope="col" rowspan="2" class="align-middle w-[105px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 25-35mm</th>
                                <th scope="col" rowspan="2" class="align-middle w-[85px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số vát tay nắm âm</th>
                                <th scope="col" rowspan="2" class="align-middle w-[75px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số tấm CNC</th>
                                <th scope="col" rowspan="2" class="align-middle w-[85px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều vân</th>
                                <th scope="col" rowspan="2" class="align-middle w-[100px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                                <th scope="col" rowspan="2" class="align-middle w-[105px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                                <th scope="col" rowspan="2" class="align-middle w-[140px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                                <th scope="col" rowspan="2" class="align-middle w-[45px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Xóa</th>
                            </tr>
                            <tr class="bg-neutral-50 text-center">
                                {{-- Kích thước --}}
                                <th scope="col" class="w-[85px] border border-neutral-200 bg-yellow-100/70 font-semibold text-xs text-neutral-700 uppercase">Cao (chiều vân gỗ)</th>
                                <th scope="col" class="w-[80px] border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
                                {{-- Dán cạnh --}}
                                <th scope="col" class="w-[55px] min-w-[55px] max-w-[55px] border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Cao</th>
                                <th scope="col" class="w-[55px] min-w-[55px] max-w-[55px] border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Cao</th>
                                <th scope="col" class="w-[55px] min-w-[55px] max-w-[55px] border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
                                <th scope="col" class="w-[55px] min-w-[55px] max-w-[55px] border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
                            </tr>
                        </thead>
                        <tbody class="supply-items-container" data-supply-index="{{ $supplyIndex }}">
                            @foreach($supply->minLateItems as $itemIndex => $item)
                            @php
                                $size = $item->size ?? [];
                                if (is_string($size)) {
                                    $size = json_decode($size, true) ?? [];
                                }
                                $gluing = $item->edge_gluing ?? [];
                                if (is_string($gluing)) {
                                    $gluing = json_decode($gluing, true) ?? [];
                                }
                            @endphp
                            <tr class="order-item-row" data-item-id="{{ $item->id }}">
                                <td class="text-center align-middle border border-neutral-200">
                                    <span class="row-index font-semibold text-neutral-500">{{ $itemIndex + 1 }}</span>
                                </td>
                                <td class="border border-neutral-200 w-[160px] min-w-[160px] max-w-[160px]">
                                    <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_code]" class="tom-select-product" onchange="fillMinLateProductInfo(this, {{ $supplyIndex }}, {{ $itemIndex }})">
                                        <option value="">-- Chọn --</option>
                                        @foreach(\App\Models\Supply::pluck('product_code')->filter()->unique() as $code)
                                        <option value="{{ $code }}" {{ $item->product_code == $code ? 'selected' : '' }}>{{ $code }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên sản phẩm" required value="{{ $item->product_name ?? $item->name ?? '' }}">
                                </td>
                                <td class="border border-neutral-200 w-[85px] min-w-[85px] max-w-[85px]">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $size['height'] ?? '' }}">
                                </td>
                                <td class="border border-neutral-200 w-[80px] min-w-[80px] max-w-[80px]">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $size['width'] ?? '' }}">
                                </td>
                                <td class="border border-neutral-200 w-[70px] min-w-[70px] max-w-[70px]">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="1" min="1" required value="{{ $item->quantity }}">
                                </td>
                                {{-- Dán cạnh (4 text inputs) --}}
                                <td class="border border-neutral-200 w-[55px] min-w-[55px] max-w-[55px]">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_gluing][height_1]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-1 h-8 text-xs" placeholder="0" value="{{ $gluing['height_1'] ?? '' }}">
                                </td>
                                <td class="border border-neutral-200 w-[55px] min-w-[55px] max-w-[55px]">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_gluing][height_2]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-1 h-8 text-xs" placeholder="0" value="{{ $gluing['height_2'] ?? '' }}">
                                </td>
                                <td class="border border-neutral-200 w-[55px] min-w-[55px] max-w-[55px]">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_gluing][width_1]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-1 h-8 text-xs" placeholder="0" value="{{ $gluing['width_1'] ?? '' }}">
                                </td>
                                <td class="border border-neutral-200 w-[55px] min-w-[55px] max-w-[55px]">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_gluing][width_2]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-1 h-8 text-xs" placeholder="0" value="{{ $gluing['width_2'] ?? '' }}">
                                </td>
                                <td class="border border-neutral-200 w-[85px] min-w-[85px] max-w-[85px]">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][straight_paste_length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->straight_paste_length ?? 0 }}">
                                </td>
                                <td class="border border-neutral-200 w-[85px] min-w-[85px] max-w-[85px]">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][beveled_length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->beveled_length ?? 0 }}">
                                </td>
                                <td class="border border-neutral-200 w-[85px] min-w-[85px] max-w-[85px]">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][vat_moi_length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->vat_moi_length ?? 0 }}">
                                </td>
                                <td class="border border-neutral-200 w-[105px] min-w-[105px] max-w-[105px]">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][ban_rong_40_59]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->ban_rong_40_59 ?? 0 }}">
                                </td>
                                <td class="border border-neutral-200 w-[105px] min-w-[105px] max-w-[105px]">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][ban_rong_17_39]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->ban_rong_17_39 ?? 0 }}">
                                </td>
                                <td class="border border-neutral-200 w-[105px] min-w-[105px] max-w-[105px]">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][ban_rong_25_35]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->ban_rong_25_35 ?? 0 }}">
                                </td>
                                <td class="border border-neutral-200 w-[85px] min-w-[85px] max-w-[85px]">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][beveled_handle]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->beveled_handle ?? 0 }}">
                                </td>
                                <td class="border border-neutral-200 w-[75px] min-w-[75px] max-w-[75px]">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][cnc]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" value="{{ $item->cnc ?? 0 }}">
                                </td>
                                <td class="border border-neutral-200 w-[85px] min-w-[85px] max-w-[85px]">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][direction]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8 text-xs" placeholder="Chiều vân" value="{{ $item->direction ?? '' }}">
                                </td>
                                <td class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][unit_price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" min="0" required value="{{ $item->unit_price ?? 0 }}">
                                </td>
                                <td class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][total_price]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center" placeholder="0" step="0.01" value="{{ $item->total_price ?? 0 }}" readonly>
                                </td>
                                <td class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Ghi chú" value="{{ $item->notes ?? '' }}">
                                </td>
                                <td class="text-center align-middle border border-neutral-200">
                                    <button type="button" onclick="removeMinLateOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
                                        <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                                    </button>
                                </td>
                                <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][id]" value="{{ $item->id }}">
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
            @php $minLateSupplyIndex = $acrylicOrder->supplies->count() @endphp
        @else
            @php $minLateSupplyIndex = 0 @endphp
        @endif
    </div>
</div>

<script>
let minLateSupplyIndex = {{ $minLateSupplyIndex ?? 0 }};

function addMinLateOrderSupply() {
    const container = document.getElementById('min-late-supplies-container');
    const newSupply = document.createElement('div');
    newSupply.className = 'order-supply-row bg-neutral-50/50 border border-neutral-200 rounded-xl p-5 mb-2 relative transition-all hover:border-neutral-300 shadow-sm';
    newSupply.innerHTML = `
        <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-primary-50 rounded-lg text-primary-600 flex items-center justify-center">
                    <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                </div>
                <input type="text" name="supplies[${minLateSupplyIndex}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)">
                <input type="number" name="supplies[${minLateSupplyIndex}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="0.01" value="0">
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="addMinLateOrderItem(this)" class="text-neutral-400 hover:text-primary-600 transition-colors p-1">
                    <iconify-icon icon="lucide:package-plus" class="text-lg"></iconify-icon> Thêm
                </button>
                <button type="button" onclick="this.closest('.order-supply-row').remove()" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa vật tư này">
                    <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon> Xóa
                </button>
            </div>
        </div>
        <div class="overflow-x-auto pb-3">
            <table class="table bordered-table sm-table mb-0 min-w-[2000px] border border-neutral-200">
                <thead>
                    <tr class="bg-neutral-50 text-center">
                        <th scope="col" rowspan="2" class="align-middle w-[45px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Stt</th>
                        <th scope="col" rowspan="2" class="align-middle w-[160px] min-w-[160px] max-w-[160px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã hàng</th>
                        <th scope="col" rowspan="2" class="align-middle w-[180px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên hàng hóa, dịch vụ <span class="text-danger-500">*</span></th>
                        <th scope="col" colspan="2" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Kích thước (mm)</th>
                        <th scope="col" rowspan="2" class="align-middle w-[70px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng</th>
                        <th scope="col" colspan="4" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Dán cạnh</th>
                        <th scope="col" rowspan="2" class="align-middle w-[85px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán thẳng</th>
                        <th scope="col" rowspan="2" class="align-middle w-[85px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán vát</th>
                        <th scope="col" rowspan="2" class="align-middle w-[85px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán vát mòi</th>
                        <th scope="col" rowspan="2" class="align-middle w-[105px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 40-59mm</th>
                        <th scope="col" rowspan="2" class="align-middle w-[105px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 17-39mm</th>
                        <th scope="col" rowspan="2" class="align-middle w-[105px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 25-35mm</th>
                        <th scope="col" rowspan="2" class="align-middle w-[85px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số vát tay nắm âm</th>
                        <th scope="col" rowspan="2" class="align-middle w-[75px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số tấm CNC</th>
                        <th scope="col" rowspan="2" class="align-middle w-[85px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều vân</th>
                        <th scope="col" rowspan="2" class="align-middle w-[100px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                        <th scope="col" rowspan="2" class="align-middle w-[105px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                        <th scope="col" rowspan="2" class="align-middle w-[140px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                        <th scope="col" rowspan="2" class="align-middle w-[45px] border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Xóa</th>
                    </tr>
                    <tr class="bg-neutral-50 text-center">
                        {{-- Kích thước --}}
                        <th scope="col" class="w-[85px] border border-neutral-200 bg-yellow-100/70 font-semibold text-xs text-neutral-700 uppercase">Cao (chiều vân gỗ)</th>
                        <th scope="col" class="w-[80px] border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
                        {{-- Dán cạnh --}}
                        <th scope="col" class="w-[55px] min-w-[55px] max-w-[55px] border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Cao</th>
                        <th scope="col" class="w-[55px] min-w-[55px] max-w-[55px] border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Cao</th>
                        <th scope="col" class="w-[55px] min-w-[55px] max-w-[55px] border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
                        <th scope="col" class="w-[55px] min-w-[55px] max-w-[55px] border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
                    </tr>
                </thead>
                <tbody class="supply-items-container" data-supply-index="${minLateSupplyIndex}">
                </tbody>
            </table>
        </div>
    `;
    container.appendChild(newSupply);
    minLateSupplyIndex++;
}

function addMinLateOrderItem(button) {
    const supplyRow = button.closest('.order-supply-row');
    const supplyIndex = supplyRow.querySelector('.supply-items-container').dataset.supplyIndex;
    const container = supplyRow.querySelector('.supply-items-container');
    const itemIndex = container.querySelectorAll('.order-item-row').length;
    
    const newItem = document.createElement('tr');
    newItem.className = 'order-item-row';
    newItem.innerHTML = `
        <td class="text-center align-middle border border-neutral-200">
            <span class="row-index font-semibold text-neutral-500">${itemIndex + 1}</span>
        </td>
        <td class="border border-neutral-200 w-[160px] min-w-[160px] max-w-[160px]">
            <select name="supplies[${supplyIndex}][items][${itemIndex}][product_code]" class="tom-select-product" onchange="fillMinLateProductInfo(this, ${supplyIndex}, ${itemIndex})">
                <option value="">-- Chọn --</option>
                @foreach(\App\Models\Supply::pluck('product_code')->filter()->unique() as $code)
                <option value="{{ $code }}">{{ $code }}</option>
                @endforeach
            </select>
        </td>
        <td class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên sản phẩm" required>
        </td>
        <td class="border border-neutral-200 w-[85px] min-w-[85px] max-w-[85px]">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01">
        </td>
        <td class="border border-neutral-200 w-[80px] min-w-[80px] max-w-[80px]">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01">
        </td>
        <td class="border border-neutral-200 w-[70px] min-w-[70px] max-w-[70px]">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="1" min="1" required value="1">
        </td>
        {{-- Dán cạnh (4 text inputs) --}}
        <td class="border border-neutral-200 w-[55px] min-w-[55px] max-w-[55px]">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][edge_gluing][height_1]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-1 h-8 text-xs" placeholder="0">
        </td>
        <td class="border border-neutral-200 w-[55px] min-w-[55px] max-w-[55px]">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][edge_gluing][height_2]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-1 h-8 text-xs" placeholder="0">
        </td>
        <td class="border border-neutral-200 w-[55px] min-w-[55px] max-w-[55px]">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][edge_gluing][width_1]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-1 h-8 text-xs" placeholder="0">
        </td>
        <td class="border border-neutral-200 w-[55px] min-w-[55px] max-w-[55px]">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][edge_gluing][width_2]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-1 h-8 text-xs" placeholder="0">
        </td>
        <td class="border border-neutral-200 w-[85px] min-w-[85px] max-w-[85px]">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][straight_paste_length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="0">
        </td>
        <td class="border border-neutral-200 w-[85px] min-w-[85px] max-w-[85px]">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][beveled_length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="0">
        </td>
        <td class="border border-neutral-200 w-[85px] min-w-[85px] max-w-[85px]">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][vat_moi_length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="0">
        </td>
        <td class="border border-neutral-200 w-[105px] min-w-[105px] max-w-[105px]">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][ban_rong_40_59]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="0">
        </td>
        <td class="border border-neutral-200 w-[105px] min-w-[105px] max-w-[105px]">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][ban_rong_17_39]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="0">
        </td>
        <td class="border border-neutral-200 w-[105px] min-w-[105px] max-w-[105px]">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][ban_rong_25_35]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="0">
        </td>
        <td class="border border-neutral-200 w-[85px] min-w-[85px] max-w-[85px]">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][beveled_handle]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="0">
        </td>
        <td class="border border-neutral-200 w-[75px] min-w-[75px] max-w-[75px]">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][cnc]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" value="0">
        </td>
        <td class="border border-neutral-200 w-[85px] min-w-[85px] max-w-[85px]">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][direction]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8 text-xs" placeholder="Chiều vân">
        </td>
        <td class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][unit_price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" min="0" required>
        </td>
        <td class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][total_price]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center" placeholder="0" step="0.01" readonly>
        </td>
        <td class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Ghi chú">
        </td>
        <td class="text-center align-middle border border-neutral-200">
            <button type="button" onclick="removeMinLateOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
                <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
            </button>
        </td>
    `;
    container.appendChild(newItem);
    
    // Initialize tom-select for new product code select
    const newRow = container.lastElementChild;
    const newProductSelect = newRow.querySelector('.tom-select-product');
    if (newProductSelect && typeof TomSelect !== 'undefined') {
        new TomSelect(newProductSelect, {
            allowEmptyOption: true,
            placeholder: '-- Chọn --',
        });
    }
    
    // Attach event listeners to new inputs
    const unitPriceInput = newRow.querySelector('input[name*="[unit_price]"]');
    const quantityInput = newRow.querySelector('input[name*="[quantity]"]');
    if (unitPriceInput) unitPriceInput.addEventListener('input', () => calculateMinLateTotalPrice(newRow));
    if (quantityInput) quantityInput.addEventListener('input', () => calculateMinLateTotalPrice(newRow));
    
    updateOrderSummary();
}

function removeMinLateOrderItem(button) {
    const row = button.closest('.order-item-row');
    const tbody = row.closest('.supply-items-container');
    row.remove();
    updateOrderSummary();
    updateRowIndexes(tbody);
}

function updateRowIndexes(tbody) {
    if (!tbody) return;
    tbody.querySelectorAll('.order-item-row').forEach((row, index) => {
        const indexEl = row.querySelector('.row-index');
        if (indexEl) indexEl.textContent = index + 1;
    });
}

function calculateMinLateTotalPrice(row) {
    const unitPrice = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
    const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
    const totalPrice = unitPrice * quantity;
    row.querySelector('input[name*="[total_price]"]').value = totalPrice.toFixed(2);
    updateOrderSummary();
}

function fillMinLateProductInfo(selectElement, supplyIndex, itemIndex) {
    const productCode = selectElement.value;
    const row = selectElement.closest('.order-item-row');
    
    @if(auth()->check())
    const supplies = @json(\App\Models\Supply::all());
    const supply = supplies.find(s => s.product_code === productCode);
    if (supply) {
        const productNameInput = row.querySelector(`input[name="supplies[${supplyIndex}][items][${itemIndex}][product_name]"]`);
        if (productNameInput) productNameInput.value = supply.name || '';
        
        const unitPriceInput = row.querySelector(`input[name="supplies[${supplyIndex}][items][${itemIndex}][unit_price]"]`);
        if (unitPriceInput) {
            unitPriceInput.value = supply.unit_price || 0;
            calculateMinLateTotalPrice(row);
        }
    }
    @endif
    updateOrderSummary();
}

// Initial setup for Min Late-specific rows if DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.order-item-row').forEach(row => {
        const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
        const quantityInput = row.querySelector('input[name*="[quantity]"]');
        if (unitPriceInput) unitPriceInput.addEventListener('input', () => {
            if (typeof calculateMinLateTotalPrice === 'function') calculateMinLateTotalPrice(row);
            else if (typeof calculateTotalPrice === 'function') calculateTotalPrice(row);
        });
        if (quantityInput) quantityInput.addEventListener('input', () => {
            if (typeof calculateMinLateTotalPrice === 'function') calculateMinLateTotalPrice(row);
            else if (typeof calculateTotalPrice === 'function') calculateTotalPrice(row);
        });
    });
});
</script>
