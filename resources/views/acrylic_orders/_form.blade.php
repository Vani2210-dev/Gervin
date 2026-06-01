<div class="card p-0 rounded-xl border-0 overflow-hidden">
    <div class="card-header border-b border-neutral-200 bg-white py-4 px-6">
        <h5 class="font-semibold text-base">{{ $title ?? 'Đơn hàng Acrylic' }}</h5>
    </div>
    <form action="{{ $action }}" method="POST" id="order-form" enctype="multipart/form-data">
        @if(isset($acrylicOrder))
            @method('PUT')
        @endif
        @csrf
        <div class="p-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Main Form - col-lg-8 --}}
                <div class="lg:col-span-8 space-y-6">
                    {{-- Customer Info Section Card --}}
                    <div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm mb-2">
                        <div class="flex items-center gap-2 border-b border-neutral-100 pb-4 mb-5">
                            <iconify-icon icon="lucide:user" class="text-xl text-primary-500"></iconify-icon>
                            <h6 class="font-bold text-base text-neutral-800 m-0">Thông tin khách hàng & Đơn hàng</h6>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Khách hàng</label>
                                <select name="customer_id" class="form-select rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" onchange="fillCustomerInfo(this.value)">
                                    <option value="">-- Chọn khách hàng --</option>
                                    @foreach(\App\Models\Customer::all() as $customer)
                                    <option value="{{ $customer->id }}" {{ isset($acrylicOrder) && $acrylicOrder?->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->customer_code }} - {{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group md:col-span-2">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Tên khách hàng <span class="text-danger-500">*</span></label>
                                <input type="text" name="customer_name" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập tên khách hàng" required value="{{ old('customer_name', $acrylicOrder?->customer_name ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Loại đơn</label>
                                <select name="type" class="form-select rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500">
                                    <option value="">-- Chọn --</option>
                                    <option value="acrylic" {{ isset($acrylicOrder) && $acrylicOrder->type == 'acrylic' ? 'selected' : '' }}>Acrylic</option>
                                    <option value="min_late" {{ isset($acrylicOrder) && $acrylicOrder->type == 'min_late' ? 'selected' : '' }}>Min Late</option>
                                    <option value="glass" {{ isset($acrylicOrder) && $acrylicOrder->type == 'glass' ? 'selected' : '' }}>Glass</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập số điện thoại" value="{{ old('phone', $acrylicOrder?->phone ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Ngày giờ chốt đơn</label>
                                <input type="datetime-local" id="order_date" name="order_date" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" value="{{ old('order_date', (isset($acrylicOrder) && $acrylicOrder->order_date) ? ($acrylicOrder->order_date instanceof \Carbon\Carbon ? $acrylicOrder->order_date->format('Y-m-d\TH:i') : date('Y-m-d\TH:i', strtotime($acrylicOrder->order_date))) : '') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Số ngày phải giao</label>
                                <input type="number" id="delivery_days" name="delivery_days" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập số ngày" min="0" value="{{ old('delivery_days', $acrylicOrder?->delivery_days ?? '') }}">
                            </div>
                            <div class="form-group md:col-span-2">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Hạn đơn (tự động tính)</label>
                                <input type="date" id="deadline" name="deadline" class="form-control rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-medium text-neutral-700" readonly value="{{ old('deadline', (isset($acrylicOrder) && $acrylicOrder->deadline) ? ($acrylicOrder->deadline instanceof \Carbon\Carbon ? $acrylicOrder->deadline->format('Y-m-d') : date('Y-m-d', strtotime($acrylicOrder->deadline))) : '') }}">
                            </div>
                            <div class="form-group md:col-span-2">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Địa chỉ</label>
                                <textarea name="address" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập địa chỉ" rows="2">{{ old('address', $acrylicOrder?->address ?? '') }}</textarea>
                            </div>
                            <div class="form-group md:col-span-2">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Ghi chú đơn hàng</label>
                                <textarea name="notes" class="form-control rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Nhập ghi chú" rows="2">{{ old('notes', $acrylicOrder?->notes ?? '') }}</textarea>
                            </div>
                            @if(isset($acrylicOrder))
                            <div class="form-group md:col-span-2">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Trạng thái</label>
                                <select name="status" class="form-select rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500">
                                    <option value="pending" {{ $acrylicOrder->status === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                                    <option value="processing" {{ $acrylicOrder->status === 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                                    <option value="completed" {{ $acrylicOrder->status === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                    <option value="cancelled" {{ $acrylicOrder->status === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                                </select>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Order Supplies & Items Section Card --}}
                    <div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm">
                        <div class="flex items-center justify-between border-b border-neutral-100 pb-4 mb-5">
                            <div class="flex items-center gap-2">
                                <iconify-icon icon="lucide:package-open" class="text-xl text-primary-500"></iconify-icon>
                                <h6 class="font-bold text-base text-neutral-800 m-0">Danh sách Vật tư & Sản phẩm</h6>
                            </div>
                            <button type="button" onclick="addOrderSupply()" class="btn btn-sm btn-primary rounded-lg flex items-center gap-1">
                                <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon> Thêm vật tư
                            </button>
                        </div>
                        <div id="order-supplies-container" class="space-y-6">
                            @if(isset($acrylicOrder) && $acrylicOrder->supplies->count() > 0)
                                @foreach($acrylicOrder->supplies as $supplyIndex => $supply)
                                <div class="order-supply-row bg-neutral-50/50 border border-neutral-200 rounded-xl p-5 mb-2 relative transition-all hover:border-neutral-300 shadow-sm" data-supply-id="{{ $supply->id }}">
                                    
                                    {{-- Items inside this supply --}}
                                    <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3 mb-4">
                                        <div class="flex items-center gap-3">
                                            <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                                                <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                                            </div>
                                            <input type="text" name="supplies[{{ $supplyIndex }}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)" value="{{ $supply->supply_name }}">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" onclick="addOrderItem(this)" class="btn btn-sm btn-outline-primary rounded-lg flex items-center gap-1">
                                                <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon>
                                            </button>
                                            <button type="button" onclick="this.closest('.order-supply-row').remove()" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa vật tư này">
                                                <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto pb-3">
                                        <table class="table bordered-table sm-table mb-0 min-w-[1700px]">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="w-32">Mã SP</th>
                                                    <th scope="col" class="w-64">Tên SP <span class="text-danger-500">*</span></th>
                                                    <th scope="col" class="w-20">SL <span class="text-danger-500">*</span></th>
                                                    <th scope="col" class="w-20">Cao</th>
                                                    <th scope="col" class="w-20">Rộng</th>
                                                    <th scope="col" class="w-24">Cạnh Vát</th>
                                                    <th scope="col" class="w-24">Chiều vân</th>
                                                    <th scope="col" class="w-24">Cánh (m2)</th>
                                                    <th scope="col" class="w-24">Phào (m)</th>
                                                    <th scope="col" class="w-24">Vát</th>
                                                    <th scope="col" class="w-28">Vân dọc CNC</th>
                                                    <th scope="col" class="w-28">Đơn giá <span class="text-danger-500">*</span></th>
                                                    <th scope="col" class="w-28">Thành tiền</th>
                                                    <th scope="col" class="w-44">Ghi chú</th>
                                                    <th scope="col" class="text-center w-12">Xóa</th>
                                                </tr>
                                            </thead>
                                            <tbody class="supply-items-container" data-supply-index="{{ $supplyIndex }}">
                                                @foreach($supply->items as $itemIndex => $item)
                                                <tr class="order-item-row" data-item-id="{{ $item->id }}">
                                                    <td>
                                                        <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_code]" class="tom-select-product" onchange="fillProductInfo(this, {{ $supplyIndex }}, {{ $itemIndex }})">
                                                            <option value="">-- Chọn --</option>
                                                            @foreach(\App\Models\Supply::pluck('product_code')->filter()->unique() as $code)
                                                            <option value="{{ $code }}" {{ $item->product_code == $code ? 'selected' : '' }}>{{ $code }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên sản phẩm" required value="{{ $item->product_name }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="1" min="1" required value="{{ $item->quantity }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][height]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" step="0.01" value="{{ $item->height }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" step="0.01" value="{{ $item->width }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Cạnh vát" value="{{ $item->edge_bevel }}">
                                                    </td>
                                                    <td>
                                                        <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][grain_direction]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500">
                                                            <option value="0" {{ $item->grain_direction == 0 ? 'selected' : '' }}>0</option>
                                                            <option value="2" {{ $item->grain_direction == 2 ? 'selected' : '' }}>2</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][wing_area]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" step="0.01" value="{{ $item->wing_area }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][molding_length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" step="0.01" value="{{ $item->molding_length }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Vát" value="{{ $item->bevel }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][vertical_grain_cnc]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Vân dọc CNC" value="{{ $item->vertical_grain_cnc }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][unit_price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" min="0" required value="{{ $item->unit_price }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][total_price]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700" placeholder="0" step="0.01" value="{{ $item->total_price }}" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Ghi chú" value="{{ $item->notes }}">
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" onclick="this.closest('.order-item-row').remove(); updateOrderSummary();" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
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
                                @php $supplyIndex = $acrylicOrder->supplies->count() @endphp
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Order Summary & Attachments - col-lg-4 --}}
                <div class="lg:col-span-4">
                    <div class="sticky top-6 space-y-6">
                        {{-- Summary Card --}}
                        <div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm mb-2">
                            <div class="flex items-center gap-2 border-b border-neutral-100 pb-4 mb-4">
                                <iconify-icon icon="lucide:receipt-text" class="text-xl text-primary-500"></iconify-icon>
                                <h6 class="font-bold text-base text-neutral-800 m-0">Tóm tắt đơn hàng</h6>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-neutral-500 font-medium">Số lượng sản phẩm:</span>
                                    <span class="font-semibold text-neutral-800" id="total-items">0</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-neutral-500 font-medium">Tổng tiền hàng:</span>
                                    <span class="font-semibold text-neutral-800" id="total-amount">0 VNĐ</span>
                                </div>
                                <hr class="border-neutral-100">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-bold text-neutral-800">Tổng thanh toán:</span>
                                    <span class="text-lg font-extrabold text-primary-600" id="grand-total">0 VNĐ</span>
                                </div>
                            </div>
                        </div>

                        {{-- Attachments Card --}}
                        <div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm">
                            <div class="flex items-center gap-2 border-b border-neutral-100 pb-4 mb-4">
                                <iconify-icon icon="lucide:paperclip" class="text-xl text-primary-500"></iconify-icon>
                                <h6 class="font-bold text-base text-neutral-800 m-0">Tệp tin đính kèm</h6>
                            </div>
                            <div class="form-group mb-4">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Tải lên hình ảnh</label>
                                <div class="relative flex items-center justify-center border-2 border-dashed border-neutral-300 rounded-xl p-4 hover:bg-neutral-50 hover:border-primary-400 transition-colors cursor-pointer group">
                                    <input type="file" id="attachments-input" name="attachments[]" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" multiple accept="image/*">
                                    <div class="text-center pointer-events-none">
                                        <iconify-icon icon="lucide:image-plus" class="text-3xl text-neutral-400 group-hover:text-primary-500 transition-colors mb-2"></iconify-icon>
                                        <p class="text-xs font-semibold text-neutral-600">Chọn hoặc thả ảnh tại đây</p>
                                        <p class="text-[10px] text-neutral-400 mt-1">Hỗ trợ JPG, PNG — tối đa 2MB/ảnh</p>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Preview Container for newly selected images --}}
                            <div class="mt-4 hidden" id="new-attachments-preview-container">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Hình ảnh mới chọn</label>
                                <div class="flex flex-wrap gap-2" id="new-attachments-preview"></div>
                            </div>

                            @if(isset($acrylicOrder) && $acrylicOrder->attachments)
                            <div class="mt-4">
                                <label class="form-label font-semibold text-xs text-neutral-500 uppercase tracking-wider mb-2 block">Hình ảnh đã tải</label>
                                <div class="flex flex-wrap gap-2" id="existing-attachments">
                                    @foreach(json_decode($acrylicOrder->attachments, true) ?? [] as $index => $image)
                                    <div class="relative group">
                                        <img src="{{ route('acrylic_orders.image', ['filename' => basename($image)]) }}" class="w-16 h-16 object-cover rounded-lg border border-neutral-200 shadow-sm transition-transform group-hover:scale-105">
                                        <button type="button" onclick="deleteAttachment('{{ $index }}', '{{ $image }}')" class="absolute -top-1.5 -right-1.5 bg-danger-100 hover:bg-danger-200 text-danger-600 transition-colors w-6 h-6 flex justify-center items-center rounded-full shadow-sm" title="Xóa ảnh">
                                            <iconify-icon icon="lucide:trash-2" class="text-xs"></iconify-icon>
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                                <input type="hidden" name="delete_attachments" id="delete-attachments" value="">
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-neutral-100 bg-neutral-50/50 flex items-center justify-end gap-3 rounded-b-xl">
            <a href="{{ route('acrylic_orders.index') }}" class="btn btn-outline-neutral px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">Quay lại</a>
            <button type="submit" class="btn btn-primary px-6 py-2.5 rounded-lg text-sm font-semibold shadow-sm transition-all">{{ isset($acrylicOrder) ? 'Cập nhật đơn hàng' : 'Lưu đơn hàng' }}</button>
        </div>
    </form>
</div>

<script>
let supplyIndex = {{ $supplyIndex ?? 0 }};

function addOrderSupply() {
    const container = document.getElementById('order-supplies-container');
    const newSupply = document.createElement('div');
    newSupply.className = 'order-supply-row bg-neutral-50/50 border border-neutral-200 rounded-xl p-5 mb-2 relative transition-all hover:border-neutral-300 shadow-sm';
    newSupply.innerHTML = `
        <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-primary-50 rounded-lg text-primary-600 flex items-center justify-center">
                    <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                </div>
                <input type="text" name="supplies[${supplyIndex}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)">
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="addOrderItem(this)" class="text-neutral-400 hover:text-primary-600 transition-colors p-1">
                    <iconify-icon icon="lucide:package-plus" class="text-lg"></iconify-icon> Thêm
                </button>
                <button type="button" onclick="this.closest('.order-supply-row').remove()" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa vật tư này">
                    <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon> Xóa
                </button>
            </div>
        </div>
        <div class="overflow-x-auto pb-3">
            <table class="table bordered-table sm-table mb-0 min-w-[1700px]">
                <thead>
                    <tr>
                        <th scope="col" class="w-32">Mã SP</th>
                        <th scope="col" class="w-64">Tên SP <span class="text-danger-500">*</span></th>
                        <th scope="col" class="w-20">SL <span class="text-danger-500">*</span></th>
                        <th scope="col" class="w-20">Cao</th>
                        <th scope="col" class="w-20">Rộng</th>
                        <th scope="col" class="w-24">Cạnh Vát</th>
                        <th scope="col" class="w-24">Chiều vân</th>
                        <th scope="col" class="w-24">Cánh (m2)</th>
                        <th scope="col" class="w-24">Phào (m)</th>
                        <th scope="col" class="w-24">Vát</th>
                        <th scope="col" class="w-28">Vân dọc CNC</th>
                        <th scope="col" class="w-28">Đơn giá <span class="text-danger-500">*</span></th>
                        <th scope="col" class="w-28">Thành tiền</th>
                        <th scope="col" class="w-44">Ghi chú</th>
                        <th scope="col" class="text-center w-12">Xóa</th>
                    </tr>
                </thead>
                <tbody class="supply-items-container" data-supply-index="${supplyIndex}">
                </tbody>
            </table>
        </div>
    `;
    container.appendChild(newSupply);
    supplyIndex++;
}

function addOrderItem(button) {
    const supplyRow = button.closest('.order-supply-row');
    const supplyIndex = supplyRow.querySelector('.supply-items-container').dataset.supplyIndex;
    const container = supplyRow.querySelector('.supply-items-container');
    const itemIndex = container.querySelectorAll('.order-item-row').length;
    
    const newItem = document.createElement('tr');
    newItem.className = 'order-item-row';
    newItem.innerHTML = `
        <td>
            <select name="supplies[${supplyIndex}][items][${itemIndex}][product_code]" class="tom-select-product" onchange="fillProductInfo(this, ${supplyIndex}, ${itemIndex})">
                <option value="">-- Chọn --</option>
                @foreach(\App\Models\Supply::pluck('product_code')->filter()->unique() as $code)
                <option value="{{ $code }}">{{ $code }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên sản phẩm" required>
        </td>
        <td>
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="1" min="1" required value="1">
        </td>
        <td>
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][height]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" step="0.01">
        </td>
        <td>
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" step="0.01">
        </td>
        <td>
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][edge_bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Cạnh vát">
        </td>
        <td>
            <select name="supplies[${supplyIndex}][items][${itemIndex}][grain_direction]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500">
                <option value="0">0</option>
                <option value="2">2</option>
            </select>
        </td>
        <td>
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][wing_area]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" step="0.01">
        </td>
        <td>
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][molding_length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" step="0.01">
        </td>
        <td>
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Vát">
        </td>
        <td>
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][vertical_grain_cnc]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Vân dọc CNC">
        </td>
        <td>
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][unit_price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="0" min="0" required>
        </td>
        <td>
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][total_price]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700" placeholder="0" step="0.01" readonly>
        </td>
        <td>
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Ghi chú">
        </td>
        <td class="text-center">
            <button type="button" onclick="this.closest('.order-item-row').remove(); updateOrderSummary();" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
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
    if (unitPriceInput) unitPriceInput.addEventListener('input', () => calculateTotalPrice(newRow));
    if (quantityInput) quantityInput.addEventListener('input', () => calculateTotalPrice(newRow));
    
    updateOrderSummary();
}

function fillCustomerInfo(customerId) {
    @if(auth()->check())
    const customers = @json(\App\Models\Customer::all());
    const customer = customers.find(c => c.id == customerId);
    if (customer) {
        document.querySelector('input[name="customer_name"]').value = customer.name;
        document.querySelector('input[name="phone"]').value = customer.phone || '';
        document.querySelector('textarea[name="address"]').value = customer.address || '';
    }
    @endif
}

function calculateTotalPrice(row) {
    const unitPrice = parseFloat(row.querySelector('input[name*="[unit_price]"]').value) || 0;
    const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
    const totalPrice = unitPrice * quantity;
    row.querySelector('input[name*="[total_price]"]').value = totalPrice.toFixed(2);
    updateOrderSummary();
}

// Attach event listeners to existing inputs
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tom-select for existing product code selects
    if (typeof TomSelect !== 'undefined') {
        document.querySelectorAll('.tom-select-product').forEach(function(element) {
            new TomSelect(element, {
                allowEmptyOption: true,
                placeholder: '-- Chọn --',
            });
        });
    }
    
    document.querySelectorAll('.order-item-row').forEach(row => {
        const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
        const quantityInput = row.querySelector('input[name*="[quantity]"]');
        if (unitPriceInput) unitPriceInput.addEventListener('input', () => calculateTotalPrice(row));
        if (quantityInput) quantityInput.addEventListener('input', () => calculateTotalPrice(row));
    });
    
    // Initialize order summary
    updateOrderSummary();

    // Image preview handler
    const attachmentsInput = document.getElementById('attachments-input');
    if (attachmentsInput) {
        attachmentsInput.addEventListener('change', function() {
            const previewContainer = document.getElementById('new-attachments-preview-container');
            const previewDiv = document.getElementById('new-attachments-preview');
            previewDiv.innerHTML = ''; // Clear previous previews

            if (this.files && this.files.length > 0) {
                previewContainer.classList.remove('hidden');
                Array.from(this.files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imgWrapper = document.createElement('div');
                            imgWrapper.className = 'relative';
                            imgWrapper.innerHTML = `
                                <img src="${e.target.result}" class="w-20 h-20 object-cover rounded-lg border border-neutral-200">
                            `;
                            previewDiv.appendChild(imgWrapper);
                        }
                        reader.readAsDataURL(file);
                    }
                });
            } else {
                previewContainer.classList.add('hidden');
            }
        });
    }

    // Auto-calculate deadline from order_date + delivery_days
    const orderDateInput = document.getElementById('order_date');
    const deliveryDaysInput = document.getElementById('delivery_days');
    const deadlineInput = document.getElementById('deadline');

    function calculateDeadline() {
        if (orderDateInput.value && deliveryDaysInput.value) {
            const orderDate = new Date(orderDateInput.value);
            const deliveryDays = parseInt(deliveryDaysInput.value) || 0;
            const deadline = new Date(orderDate);
            deadline.setDate(deadline.getDate() + deliveryDays);
            deadlineInput.value = deadline.toISOString().split('T')[0];
        }
    }

    if (orderDateInput && deliveryDaysInput && deadlineInput) {
        orderDateInput.addEventListener('change', calculateDeadline);
        deliveryDaysInput.addEventListener('input', calculateDeadline);
    }
});

function deleteAttachment(index, imagePath) {
    if (confirm('Bạn có chắc muốn xóa hình ảnh này?')) {
        // Remove from DOM
        const container = document.getElementById('existing-attachments');
        const imageDivs = container.querySelectorAll('.relative');
        if (imageDivs[index]) {
            imageDivs[index].remove();
        }
        
        // Track deleted attachments
        const deleteInput = document.getElementById('delete-attachments');
        let deleted = deleteInput.value ? JSON.parse(deleteInput.value) : [];
        deleted.push(imagePath);
        deleteInput.value = JSON.stringify(deleted);
    }
}

function fillProductInfo(selectElement, supplyIndex, itemIndex) {
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
            calculateTotalPrice(row);
        }
    }
    @endif
    updateOrderSummary();
}

function updateOrderSummary() {
    const rows = document.querySelectorAll('.order-item-row');
    let totalItems = 0;
    let totalAmount = 0;
    
    rows.forEach(row => {
        const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
        const totalPrice = parseFloat(row.querySelector('input[name*="[total_price]"]').value) || 0;
        totalItems += quantity;
        totalAmount += totalPrice;
    });
    
    document.getElementById('total-items').textContent = totalItems;
    document.getElementById('total-amount').textContent = totalAmount.toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('grand-total').textContent = totalAmount.toLocaleString('vi-VN') + ' VNĐ';
}
</script>
