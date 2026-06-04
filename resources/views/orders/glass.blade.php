{{-- Order Supplies & Items Section Card (Glass) --}}
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
    /* === COMPACT TABLE: 75% font scale === */
    #glass-supplies-container .order-supply-row table {
        font-size: 75% !important;
    }
    #glass-supplies-container .order-supply-row table input,
    #glass-supplies-container .order-supply-row table select,
    #glass-supplies-container .order-supply-row table textarea {
        font-size: 75% !important;
        height: 24px !important;
        min-height: 24px !important;
        padding-top: 2px !important;
        padding-bottom: 2px !important;
        line-height: 1.2 !important;
    }
    #glass-supplies-container .order-supply-row table th,
    #glass-supplies-container .order-supply-row table td {
        padding: 3px 4px !important;
    }
    /* Override td widths to ~65% of original */
    #glass-supplies-container .order-supply-row table td[style*="width: 45px"],
    #glass-supplies-container .order-supply-row table td[style*="width:45px"] { width: 30px !important; min-width: 30px !important; max-width: 30px !important; }
    #glass-supplies-container .order-supply-row table td[style*="width: 160px"],
    #glass-supplies-container .order-supply-row table td[style*="width:160px"] { width: 110px !important; min-width: 110px !important; max-width: 110px !important; }
    #glass-supplies-container .order-supply-row table td[style*="width: 200px"],
    #glass-supplies-container .order-supply-row table td[style*="width:200px"] { width: 130px !important; min-width: 130px !important; max-width: 130px !important; }
    #glass-supplies-container .order-supply-row table td[style*="width: 110px"],
    #glass-supplies-container .order-supply-row table td[style*="width:110px"] { width: 80px !important; min-width: 80px !important; max-width: 80px !important; }
    #glass-supplies-container .order-supply-row table td[style*="width: 100px"],
    #glass-supplies-container .order-supply-row table td[style*="width:100px"] { width: 70px !important; min-width: 70px !important; max-width: 70px !important; }
    #glass-supplies-container .order-supply-row table td[style*="width: 120px"],
    #glass-supplies-container .order-supply-row table td[style*="width:120px"] { width: 90px !important; min-width: 90px !important; max-width: 90px !important; }
    #glass-supplies-container .order-supply-row table td[style*="width: 70px"],
    #glass-supplies-container .order-supply-row table td[style*="width:70px"] { width: 50px !important; min-width: 50px !important; max-width: 50px !important; }
    #glass-supplies-container .order-supply-row table td[style*="width: 80px"],
    #glass-supplies-container .order-supply-row table td[style*="width:80px"] { width: 60px !important; min-width: 60px !important; max-width: 60px !important; }
    #glass-supplies-container .order-supply-row table td[style*="min-width: 160px"],
    #glass-supplies-container .order-supply-row table td[style*="min-width:160px"] { min-width: 120px !important; }
    /* Style TomSelect inside table rows to match compact inputs */
    .table .ts-wrapper {
        padding: 0 !important;
        border: none !important;
        background: transparent !important;
        min-height: auto !important;
        height: 24px !important;
    }
    .table .ts-control {
        padding: 0 5px !important;
        height: 24px !important;
        font-size: 9px !important;
        line-height: 22px !important;
        border-radius: 6px !important;
        border: 1px solid #d1d5db !important;
        background-color: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        box-shadow: none !important;
    }
    .table .ts-control input {
        font-size: 9px !important;
        height: auto !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .table .ts-control .item {
        font-size: 9px !important;
        line-height: 22px !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .table .ts-wrapper.single .ts-control:after {
        top: 50% !important;
        margin-top: -3px !important;
    }
    .table .ts-wrapper.focus .ts-control {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 1px #3b82f6 !important;
    }

    .order-entry-card:not(.is-table-view) .overflow-x-auto {
        overflow: visible !important;
    }
    .order-entry-card:not(.is-table-view) table {
        display: block;
        min-width: 0 !important;
        border: 0 !important;
    }
    .order-entry-card:not(.is-table-view) thead {
        display: none;
    }
    .order-entry-card:not(.is-table-view) tbody.supply-items-container {
        display: grid;
        gap: 1rem;
    }
    .order-entry-card:not(.is-table-view) tr.order-item-row {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem 1.25rem;
        padding: 1.25rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .order-entry-card:not(.is-table-view) tr.order-item-row td {
        display: block;
        width: auto !important;
        min-width: 0 !important;
        max-width: none !important;
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        position: static !important;
    }
    .order-entry-card:not(.is-table-view) tr.order-item-row td::before {
        display: block;
        margin-bottom: 0.5rem;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .order-entry-card:not(.is-table-view) tr.order-item-row input,
    .order-entry-card:not(.is-table-view) tr.order-item-row select,
    .order-entry-card:not(.is-table-view) tr.order-item-row textarea {
        width: 100% !important;
        height: 42px !important;
        min-height: 42px !important;
        padding: 0.5rem 0.75rem !important;
        font-size: 0.875rem !important;
        line-height: 1.25rem !important;
        text-align: left !important;
    }
    .order-entry-card:not(.is-table-view) tr.order-item-row textarea {
        height: auto !important;
    }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:first-child,
    .order-entry-card:not(.is-table-view) tr.order-item-row td:last-child {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }
    .order-entry-card:not(.is-table-view) .order-supply-row > .flex.items-center.justify-between {
        align-items: flex-start;
        flex-wrap: wrap;
    }
    .order-entry-card:not(.is-table-view) .order-supply-row > .flex.items-center.justify-between > .flex.items-center.gap-3 {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        flex: 1 1 720px;
        align-items: end;
    }
    .order-entry-card:not(.is-table-view) .order-supply-row > .flex.items-center.justify-between > .flex.items-center.gap-3::before {
        content: "Thông tin vật tư";
        grid-column: 1 / -1;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .order-entry-card:not(.is-table-view) .order-supply-row > .flex.items-center.justify-between > .flex.items-center.gap-3 > .p-1\.5 {
        display: none;
    }
    .order-entry-card:not(.is-table-view) .order-supply-row > .flex.items-center.justify-between > .flex.items-center.gap-2 {
        align-self: flex-end;
    }
    .order-entry-card:not(.is-table-view) .order-supply-row > .flex.items-center.justify-between input {
        width: 100% !important;
        height: 42px !important;
        font-size: 0.875rem !important;
    }
    .order-entry-card:not(.is-table-view) .order-input-layout {
        display: grid !important;
        gap: 1rem;
        overflow: visible !important;
    }
    .order-entry-card:not(.is-table-view) .order-input-layout table {
        display: none !important;
    }
    .order-entry-card:not(.is-table-view) .supply-items-container.order-input-rows {
        display: grid;
        gap: 1rem;
    }
    .order-entry-card:not(.is-table-view) .order-item-row.order-form-row {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem 1.25rem;
        padding: 1.25rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .order-entry-card:not(.is-table-view) .order-form-field {
        display: block;
        min-width: 0;
    }
    .order-entry-card:not(.is-table-view) .order-form-section {
        grid-column: 1 / -1;
        display: grid;
        gap: 1rem 1.25rem;
    }
    .order-entry-card:not(.is-table-view) .order-form-section--1 {
        grid-template-columns: minmax(0, 1fr);
    }
    .order-entry-card:not(.is-table-view) .order-form-section--2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .order-entry-card:not(.is-table-view) .order-form-section--3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .order-entry-card:not(.is-table-view) .order-form-section--4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    .order-entry-card:not(.is-table-view) .order-form-field__label {
        display: block;
        margin-bottom: 0.5rem;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .order-entry-card:not(.is-table-view) .order-form-field input,
    .order-entry-card:not(.is-table-view) .order-form-field select,
    .order-entry-card:not(.is-table-view) .order-form-field textarea {
        width: 100% !important;
        height: 42px !important;
        min-height: 42px !important;
        padding: 0.5rem 0.75rem !important;
        font-size: 0.875rem !important;
        line-height: 1.25rem !important;
        text-align: left !important;
    }
    .order-entry-card:not(.is-table-view) .order-form-field--action {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }
    .order-entry-card:not(.is-table-view) .order-form-row__header {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .order-entry-card:not(.is-table-view) .order-form-row__badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 999px;
        background: #eff6ff;
        color: #2563eb;
        padding: 0.375rem 0.75rem;
        font-size: 0.8125rem;
        font-weight: 700;
    }
    .order-entry-card:not(.is-table-view) .order-form-row__actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    .order-entry-card:not(.is-table-view) .order-form-row__actions > button,
    .order-entry-card:not(.is-table-view) .order-form-row__quick-actions button {
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        background: #f8fafc;
    }
    .order-entry-card:not(.is-table-view) .order-form-row__quick-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(1)::before { content: "STT"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(2)::before { content: "Mã SP"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(3)::before { content: "Tên sản phẩm"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(4)::before { content: "Chiều mở cánh"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(5)::before { content: "Màu nhôm"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(6)::before { content: "Màu kính"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(7)::before { content: "Dài"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(8)::before { content: "Rộng"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(9)::before { content: "Đơn vị"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(10)::before { content: "Số lượng cánh"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(11)::before { content: "Khối lượng (m2)"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(12)::before { content: "Đơn giá"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(13)::before { content: "Thành tiền"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(14)::before { content: "Ghi chú"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(15)::before { content: "Hành động"; }
    .order-entry-card .order-preview-table-wrap {
        display: none;
    }
    .order-entry-card.is-table-view .order-input-layout {
        display: none !important;
    }
    .order-entry-card.is-table-view .order-preview-table-wrap {
        display: block;
    }
    .order-entry-card.is-table-view .order-table-form-action {
        display: none !important;
    }
    .order-preview-table td {
        vertical-align: middle;
        white-space: nowrap;
    }
    .order-entry-card:not(.is-table-view) .order-form-row--focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .16), 0 12px 24px rgba(15, 23, 42, .08);
    }
    @media (max-width: 1199px) {
        .order-entry-card:not(.is-table-view) tr.order-item-row,
        .order-entry-card:not(.is-table-view) .order-item-row.order-form-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 767px) {
        .order-entry-card:not(.is-table-view) tr.order-item-row,
        .order-entry-card:not(.is-table-view) .order-item-row.order-form-row,
        .order-entry-card:not(.is-table-view) .order-supply-row > .flex.items-center.justify-between > .flex.items-center.gap-3,
        .order-entry-card:not(.is-table-view) .order-form-section {
            grid-template-columns: 1fr;
        }
    }
</style>
<div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm order-entry-card">
    <div class="flex items-center justify-between border-b border-neutral-100 pb-4 mb-5">
        <div class="flex items-center gap-2">
            <iconify-icon icon="lucide:package-open" class="text-xl text-primary-500"></iconify-icon>
            <h6 class="font-bold text-base text-neutral-800 m-0">Danh sách Vật tư & Sản phẩm (Kính)</h6>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleOrderTableView(this)" class="btn btn-sm bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:table-2" class="text-lg"></iconify-icon>
                <span class="order-table-view-label">Xem dạng bảng</span>
            </button>
            <button type="button" onclick="addGlassOrderSupply()" class="order-table-form-action btn btn-sm btn-primary rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon> Thêm vật tư
            </button>
        </div>
    </div>
    <div id="glass-supplies-container" class="space-y-6">
        @if(isset($acrylicOrder) && $acrylicOrder->type == 'glass' && $acrylicOrder->supplies->count() > 0)
            @foreach($acrylicOrder->supplies as $supplyIndex => $supply)
            <div class="order-supply-row bg-neutral-50/50 border border-neutral-200 rounded-xl p-5 mb-2 relative transition-all hover:border-neutral-300 shadow-sm" data-supply-id="{{ $supply->id }}">
                
                {{-- Items inside this supply --}}
                <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                            <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                        </div>
                        <input type="text" name="supplies[{{ $supplyIndex }}][order_supply_code]" class="order-supply-code-input form-control form-control-sm rounded-lg w-40 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Mã vật tư" value="{{ $supply->order_supply_code ?? '' }}">
                        <input type="text" name="supplies[{{ $supplyIndex }}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)" value="{{ $supply->supply_name }}">
                        <input type="number" name="supplies[{{ $supplyIndex }}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="0.01" value="{{ $supply->quantity ?? 0 }}">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="addGlassOrderItem(this)" class="order-table-form-action btn btn-sm btn-outline-primary rounded-lg flex items-center gap-1">
                            <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon> Thêm sản phẩm
                        </button>
                        <button type="button" onclick="this.closest('.order-supply-row').remove()" class="order-table-form-action text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                            <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto pb-3">
                    <table class="table bordered-table sm-table mb-0 min-w-[1800px] border border-neutral-200">
                        <thead>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" rowspan="2" style="width: 45px; min-width: 45px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Stt</th>
                                <th scope="col" rowspan="2" style="width: 160px; min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã SP</th>
                                <th scope="col" rowspan="2" style="min-width: 220px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên sản phẩm <span class="text-danger-500">*</span></th>
                                <th scope="col" rowspan="2" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều mở cánh</th>
                                <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Màu nhôm</th>
                                <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Màu kính</th>
                                <th scope="col" colspan="2" style="white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Kích thước cánh (mm)</th>
                                <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn vị</th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng cánh <span class="text-danger-500">*</span></th>
                                <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Khối lượng (m2)</th>
                                <th scope="col" rowspan="2" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                                <th scope="col" rowspan="2" style="width: 120px; min-width: 120px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                                <th scope="col" rowspan="2" style="min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                                <th scope="col" rowspan="2" style="width: 80px; min-width: 80px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Hành động</th>
                            </tr>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="border border-neutral-200 bg-yellow-100/70 font-semibold text-xs text-neutral-700 uppercase">Dài (mm)</th>
                                <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng (mm)</th>
                            </tr>
                        </thead>
                        <tbody class="supply-items-container" data-supply-index="{{ $supplyIndex }}">
                            @foreach($supply->glassItems as $itemIndex => $item)
                            <tr class="order-item-row" data-item-id="{{ $item->id }}">
                                <td style="width: 45px; min-width: 45px; " class="text-center align-middle border border-neutral-200">
                                    <span class="row-index font-semibold text-neutral-500">{{ $itemIndex + 1 }}</span>
                                </td>
                                <td style="width: 160px; min-width: 160px; " class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_code]" class="product-code-input form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs font-semibold text-neutral-600" readonly value="{{ $item->product_code ?? '' }}">
                                </td>
                                <td style="min-width: 220px;" class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Tên sản phẩm" value="{{ $item->product_name }}">
                                </td>
                                <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][wing_opening_direction]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Chiều mở cánh" value="{{ $item->wing_opening_direction }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][aluminum_color]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Màu nhôm" value="{{ $item->aluminum_color }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][glass_color]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Màu kính" value="{{ $item->glass_color }}">
                                </td>
                                <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->height }}">
                                </td>
                                <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->width }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][unit]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs px-1" placeholder="Bộ" value="{{ $item->unit ?? 'Bộ' }}">
                                </td>
                                <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][wing_quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="1" min="1" required value="{{ $item->wing_quantity }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][area_m2]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->area_m2 }}">
                                </td>
                                <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][unit_price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" min="0" required value="{{ $item->unit_price ? round($item->unit_price) : '' }}">
                                </td>
                                <td style="width: 120px; min-width: 120px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][total_price]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center px-1 py-1 h-8 text-xs" placeholder="0" value="{{ $item->total_price ? round($item->total_price) : '' }}" readonly>
                                </td>
                                <td style="min-width: 160px;" class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Ghi chú" value="{{ $item->notes }}">
                                </td>
                                <td style="width: 80px; min-width: 80px; " class="text-center align-middle border border-neutral-200">
                                    <div class="flex items-center gap-1 justify-center">
                                        <button type="button" onclick="duplicateGlassRow(this)" class="text-neutral-400 hover:text-primary-500 transition-colors p-1" title="Nhân bản sản phẩm">
                                            <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                                        </button>
                                        <button type="button" onclick="removeGlassOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
                                            <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                                        </button>
                                    </div>
                                    <input type="hidden" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][id]" value="{{ $item->id }}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
            @php $glassSupplyIndex = $acrylicOrder->supplies->count() @endphp
        @else
            @php $glassSupplyIndex = 0 @endphp
        @endif
    </div>
</div>

<script>
let glassSupplyIndex = {{ $glassSupplyIndex ?? 0 }};

function addGlassOrderSupply() {
    const container = document.getElementById('glass-supplies-container');
    const newSupply = document.createElement('div');
    newSupply.className = 'order-supply-row bg-neutral-50/50 border border-neutral-200 rounded-xl p-5 mb-2 relative transition-all hover:border-neutral-300 shadow-sm';
    newSupply.innerHTML = `
        <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                    <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                </div>
                <input type="text" name="supplies[${glassSupplyIndex}][order_supply_code]" class="order-supply-code-input form-control form-control-sm rounded-lg w-40 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Mã vật tư">
                <input type="text" name="supplies[${glassSupplyIndex}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)">
                <input type="number" name="supplies[${glassSupplyIndex}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="0.01" value="0">
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="addGlassOrderItem(this)" class="order-table-form-action btn btn-sm btn-outline-primary rounded-lg flex items-center gap-1">
                    <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon> Thêm sản phẩm
                </button>
                <button type="button" onclick="this.closest('.order-supply-row').remove()" class="order-table-form-action text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                    <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto pb-3">
            <table class="table bordered-table sm-table mb-0 min-w-[1800px] border border-neutral-200">
                <thead>
                    <tr class="bg-neutral-50 text-center">
                        <th scope="col" rowspan="2" style="width: 45px; min-width: 45px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Stt</th>
                        <th scope="col" rowspan="2" style="width: 160px; min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã SP</th>
                        <th scope="col" rowspan="2" style="min-width: 220px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên sản phẩm <span class="text-danger-500">*</span></th>
                        <th scope="col" rowspan="2" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều mở cánh</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Màu nhôm</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Màu kính</th>
                        <th scope="col" colspan="2" style="white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Kích thước cánh (mm)</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn vị</th>
                        <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng cánh <span class="text-danger-500">*</span></th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Khối lượng (m2)</th>
                        <th scope="col" rowspan="2" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                        <th scope="col" rowspan="2" style="width: 120px; min-width: 120px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                        <th scope="col" rowspan="2" style="min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                        <th scope="col" rowspan="2" style="width: 80px; min-width: 80px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Hành động</th>
                    </tr>
                    <tr class="bg-neutral-50 text-center">
                        <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="border border-neutral-200 bg-yellow-100/70 font-semibold text-xs text-neutral-700 uppercase">Dài (mm)</th>
                        <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng (mm)</th>
                    </tr>
                </thead>
                <tbody class="supply-items-container" data-supply-index="${glassSupplyIndex}">
                </tbody>
            </table>
        </div>
    `;
    newSupply.querySelector('.order-supply-code-input').addEventListener('input', function() {
        updateGlassRowIndexes(newSupply.querySelector('.supply-items-container'));
    });
    container.appendChild(newSupply);

    // Add one product row by default
    const addProductBtn = newSupply.querySelector('button[onclick^="addGlassOrderItem"]');
    if (addProductBtn) {
        addGlassOrderItem(addProductBtn);
    }

    ensureGlassFormLayout(newSupply);
    glassSupplyIndex++;
}

function addGlassOrderItem(button) {
    const supplyRow = button.closest('.order-supply-row');
    const supplyIndex = supplyRow.querySelector('.supply-items-container').dataset.supplyIndex;
    const container = supplyRow.querySelector('.supply-items-container');
    const itemIndex = container.querySelectorAll('.order-item-row').length;
    
    // Check if there is an existing last row in the container to copy from
    const lastRow = container.querySelector('.order-item-row:last-of-type');
    let lastData = null;
    if (lastRow) {
        lastData = {
            product_name: lastRow.querySelector('input[name*="[product_name]"]') ? lastRow.querySelector('input[name*="[product_name]"]').value : '',
            wing_opening_direction: lastRow.querySelector('input[name*="[wing_opening_direction]"]') ? lastRow.querySelector('input[name*="[wing_opening_direction]"]').value : '',
            aluminum_color: lastRow.querySelector('input[name*="[aluminum_color]"]') ? lastRow.querySelector('input[name*="[aluminum_color]"]').value : '',
            glass_color: lastRow.querySelector('input[name*="[glass_color]"]') ? lastRow.querySelector('input[name*="[glass_color]"]').value : '',
            height: lastRow.querySelector('input[name*="[height]"]') ? lastRow.querySelector('input[name*="[height]"]').value : '',
            width: lastRow.querySelector('input[name*="[width]"]') ? lastRow.querySelector('input[name*="[width]"]').value : '',
            unit: lastRow.querySelector('input[name*="[unit]"]') ? lastRow.querySelector('input[name*="[unit]"]').value : 'Bộ',
            wing_quantity: lastRow.querySelector('input[name*="[wing_quantity]"]') ? lastRow.querySelector('input[name*="[wing_quantity]"]').value : '1',
            area_m2: lastRow.querySelector('input[name*="[area_m2]"]') ? lastRow.querySelector('input[name*="[area_m2]"]').value : '',
            unit_price: lastRow.querySelector('input[name*="[unit_price]"]') ? lastRow.querySelector('input[name*="[unit_price]"]').value : '',
            total_price: lastRow.querySelector('input[name*="[total_price]"]') ? lastRow.querySelector('input[name*="[total_price]"]').value : '',
            notes: lastRow.querySelector('input[name*="[notes]"]') ? lastRow.querySelector('input[name*="[notes]"]').value : '',
        };
    }
    
    const newItem = document.createElement('tr');
    newItem.className = 'order-item-row';
    newItem.innerHTML = `
        <td style="width: 45px; min-width: 45px; " class="text-center align-middle border border-neutral-200">
            <span class="row-index font-semibold text-neutral-500">${itemIndex + 1}</span>
        </td>
        <td style="width: 160px; min-width: 160px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][product_code]" class="product-code-input form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs font-semibold text-neutral-600" readonly>
        </td>
        <td style="min-width: 220px;" class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Tên sản phẩm" value="${lastData ? lastData.product_name : ''}">
        </td>
        <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][wing_opening_direction]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Chiều mở cánh" value="${lastData ? lastData.wing_opening_direction : ''}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][aluminum_color]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Màu nhôm" value="${lastData ? lastData.aluminum_color : ''}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][glass_color]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs text-center px-1" placeholder="Màu kính" value="${lastData ? lastData.glass_color : ''}">
        </td>
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.height : ''}">
        </td>
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.width : ''}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][unit]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs px-1" placeholder="Bộ" value="${lastData ? lastData.unit : 'Bộ'}">
        </td>
        <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][wing_quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="1" min="1" required value="${lastData ? lastData.wing_quantity : '1'}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][area_m2]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.area_m2 : ''}">
        </td>
        <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][unit_price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" min="0" required value="${lastData ? lastData.unit_price : ''}">
        </td>
        <td style="width: 120px; min-width: 120px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][total_price]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center px-1 py-1 h-8 text-xs" placeholder="0" readonly value="${lastData ? lastData.total_price : ''}">
        </td>
        <td style="min-width: 160px;" class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Ghi chú" value="${lastData ? lastData.notes : ''}">
        </td>
        <td style="width: 80px; min-width: 80px; " class="text-center align-middle border border-neutral-200">
            <div class="flex items-center gap-1 justify-center">
                <button type="button" onclick="duplicateGlassRow(this)" class="text-neutral-400 hover:text-primary-500 transition-colors p-1" title="Nhân bản sản phẩm">
                    <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                </button>
                <button type="button" onclick="removeGlassOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
                    <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                </button>
            </div>
        </td>
    `;
    const rowToAppend = container.classList.contains('order-input-rows') ? buildGlassFormRow(newItem) : newItem;
    container.appendChild(rowToAppend);
    
    const newRow = container.lastElementChild;
    bindGlassRowEvents(newRow);
    updateOrderSummary();
    updateGlassRowIndexes(container);
}

function removeGlassOrderItem(button) {
    const row = button.closest('.order-item-row');
    const tbody = row.closest('.supply-items-container');
    row.remove();
    updateOrderSummary();
    updateGlassRowIndexes();
}

function updateGlassRowIndexes() {
    const orderCode = document.getElementById('order-code-input')?.value || '';
    let globalPieceIndex = 1;
    let globalItemIndex = 1;

    document.querySelectorAll('#glass-supplies-container .order-supply-row').forEach((supplyRow, supplyIndex) => {
        const supplyCode = supplyRow.querySelector('.order-supply-code-input')?.value || '';
        const tbody = supplyRow.querySelector('.supply-items-container');
        if (!tbody) return;

        tbody.querySelectorAll('.order-item-row').forEach((row, itemIndex) => {
            // Update row STT
            const indexEl = row.querySelector('.row-index');
            if (indexEl) indexEl.textContent = globalItemIndex;

            // Get quantity (Glass uses wing_quantity)
            const quantityInput = row.querySelector('input[name*="[wing_quantity]"]');
            const qty = parseInt(quantityInput?.value) || 1;

            // Base code for the item (first piece index)
            const baseCode = `${orderCode}.${supplyCode}.${globalPieceIndex}`;
            const productCodeInput = row.querySelector('.product-code-input');
            if (productCodeInput) {
                productCodeInput.value = baseCode;
            }

            // Remove product_ids container if it exists
            const idsContainer = row.querySelector('.product-ids-container');
            if (idsContainer) {
                idsContainer.remove();
            }

            // Update inputs name indexes
            row.querySelectorAll('input, select, textarea').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    let newName = name.replace(/supplies\[\d+\]/, `supplies[${supplyIndex}]`);
                    newName = newName.replace(/\[items\]\[\d+\]/, `[items][${itemIndex}]`);
                    input.setAttribute('name', newName);
                }
            });

            globalPieceIndex += qty;
            globalItemIndex++;
        });
    });
}

function bindGlassRowEvents(row) {
    const heightInput = row.querySelector('input[name*="[height]"]');
    const widthInput = row.querySelector('input[name*="[width]"]');
    const wingQtyInput = row.querySelector('input[name*="[wing_quantity]"]');
    const areaInput = row.querySelector('input[name*="[area_m2]"]');
    const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
    
    if (heightInput) heightInput.addEventListener('input', () => calculateGlassTotalPrice(row, 'height'));
    if (widthInput) widthInput.addEventListener('input', () => calculateGlassTotalPrice(row, 'width'));
    if (wingQtyInput) {
        wingQtyInput.addEventListener('input', () => {
            calculateGlassTotalPrice(row, 'wing_quantity');
            updateGlassRowIndexes();
        });
    }
    if (areaInput) areaInput.addEventListener('input', () => calculateGlassTotalPrice(row, 'area'));
    if (unitPriceInput) unitPriceInput.addEventListener('input', () => calculateGlassTotalPrice(row, 'unit_price'));
}

function duplicateGlassRow(button) {
    const row = button.closest('.order-item-row');
    const tbody = row.closest('.supply-items-container');

    // Collect all input/select/textarea values BEFORE cloning
    const valuesToCopy = [];
    row.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(el => {
        valuesToCopy.push({ name: el.name, value: el.value });
    });

    const newRow = row.cloneNode(true);

    // Remove database ID so it creates a new entry
    const idInput = newRow.querySelector('input[name*="[id]"]');
    if (idInput) idInput.remove();

    // Restore field values by name
    valuesToCopy.forEach(({ name, value }) => {
        if (!name || name.indexOf('[id]') !== -1) return;
        const el = newRow.querySelector(`[name="${name}"]`);
        if (el) el.value = value;
    });

    // Insert after current row
    row.parentNode.insertBefore(newRow, row.nextSibling);

    bindGlassRowEvents(newRow);
    updateGlassRowIndexes();
    updateOrderSummary();
}

function calculateGlassTotalPrice(row, sourceEvent) {
    const heightInput = row.querySelector('input[name*="[height]"]');
    const widthInput = row.querySelector('input[name*="[width]"]');
    const wingQtyInput = row.querySelector('input[name*="[wing_quantity]"]');
    const areaInput = row.querySelector('input[name*="[area_m2]"]');
    const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
    
    if (!heightInput || !widthInput || !wingQtyInput || !areaInput) return;
    
    if (!heightInput || !widthInput || !wingQtyInput || !areaInput || !unitPriceInput) return;

    const height = parseFloat(heightInput.value) || 0;
    const width = parseFloat(widthInput.value) || 0;
    const wingQty = parseFloat(wingQtyInput.value) || 0;
    const unitPrice = parseFloat(unitPriceInput.value) || 0;
    
    if (sourceEvent !== 'area') {
        let area = 0;
        if (height > 0 && width > 0 && wingQty > 0) {
            area = (height * width * wingQty) / 1000000;
        }
        areaInput.value = area > 0 ? area.toFixed(4) : '';
    }
    
    const currentArea = parseFloat(areaInput.value) || 0;
    const totalPrice = currentArea * unitPrice;
    
    const totalPriceInput = row.querySelector('input[name*="[total_price]"]');
    if (totalPriceInput) {
        totalPriceInput.value = Math.round(totalPrice);
    }
    updateOrderSummary();
}

const glassPreviewLabels = [
    'STT',
    'Mã SP',
    'Tên sản phẩm',
    'Chiều mở cánh',
    'Màu nhôm',
    'Màu kính',
    'Dài',
    'Rộng',
    'Đơn vị',
    'Số lượng cánh',
    'Khối lượng (m2)',
    'Đơn giá',
    'Thành tiền',
    'Ghi chú',
    'Hành động',
];

function getOrderPreviewFieldValue(fieldWrap) {
    const field = fieldWrap.querySelector('input, select, textarea');
    if (!field) {
        return fieldWrap.textContent.trim() || '-';
    }

    if (field.tagName === 'SELECT') {
        return field.options[field.selectedIndex]?.text || field.value || '-';
    }

    return field.value || '-';
}

function getGlassFieldLabel(index) {
    return glassPreviewLabels[index] || 'Thông tin';
}

function getGlassFormLabel(index) {
    switch (index) {
        case 0: return 'STT';
        case 1: return 'Mã SP';
        case 2: return 'Tên sản phẩm';
        case 3: return 'Chiều mở cánh';
        case 4: return 'Màu nhôm';
        case 5: return 'Màu kính';
        case 6: return 'Dài (mm)';
        case 7: return 'Rộng (mm)';
        case 8: return 'Đơn vị';
        case 9: return 'Số lượng cánh';
        case 10: return 'Khối lượng (m2)';
        case 11: return 'Đơn giá';
        case 12: return 'Thành tiền';
        case 13: return 'Ghi chú';
        case 14: return 'Hành động';
        default: return 'Thông tin';
    }
}

function updateGlassFormSectionClass(section) {
    section.classList.remove('order-form-section--1', 'order-form-section--2', 'order-form-section--3', 'order-form-section--4');
    section.classList.add(`order-form-section--${section.children.length}`);
}

function getOrCreateGlassFormSection(formRow) {
    let section = formRow._orderFormCurrentSection;
    if (!section || section.children.length >= 4 || formRow.lastElementChild !== section) {
        section = document.createElement('div');
        section.className = 'order-form-section';
        formRow.appendChild(section);
        formRow._orderFormCurrentSection = section;
    }

    return section;
}

function appendGlassFieldToSection(formRow, fieldWrap) {
    const section = getOrCreateGlassFormSection(formRow);
    section.appendChild(fieldWrap);
    updateGlassFormSectionClass(section);
}

function appendGlassFormField(formRow, fieldWrap, index) {
    appendGlassFieldToSection(formRow, fieldWrap);
}

function buildGlassFormRow(sourceRow) {
    if (sourceRow.classList.contains('order-form-row')) {
        return sourceRow;
    }

    const formRow = document.createElement('div');
    formRow.className = 'order-item-row order-form-row';

    if (sourceRow.dataset.itemId) {
        formRow.dataset.itemId = sourceRow.dataset.itemId;
    }

    const header = document.createElement('div');
    header.className = 'order-form-row__header';
    const badge = document.createElement('div');
    badge.className = 'order-form-row__badge';
    const actions = document.createElement('div');
    actions.className = 'order-form-row__actions';
    actions.appendChild(buildGlassFormQuickActions());
    header.appendChild(badge);
    header.appendChild(actions);
    formRow.appendChild(header);

    const cells = Array.from(sourceRow.querySelectorAll('td'));
    cells.forEach((cell, index) => {
        if (index === 0) {
            badge.appendChild(document.createTextNode('Sản phẩm '));
            while (cell.firstChild) {
                badge.appendChild(cell.firstChild);
            }
            return;
        }

        if (index === cells.length - 1) {
            while (cell.firstChild) {
                actions.appendChild(cell.firstChild);
            }
            return;
        }

        const fieldWrap = document.createElement('div');
        fieldWrap.className = 'order-form-field';
        fieldWrap.dataset.previewLabel = getGlassFormLabel(index);

        const label = document.createElement('label');
        label.className = 'order-form-field__label';
        label.textContent = getGlassFormLabel(index);
        fieldWrap.appendChild(label);

        while (cell.firstChild) {
            fieldWrap.appendChild(cell.firstChild);
        }

        appendGlassFormField(formRow, fieldWrap, index);
    });

    return formRow;
}

function buildGlassFormQuickActions() {
    const actionWrap = document.createElement('div');
    actionWrap.className = 'order-form-row__quick-actions';

    const tableButton = document.createElement('button');
    tableButton.type = 'button';
    tableButton.className = 'js-order-table-view text-neutral-400 hover:text-primary-500 transition-colors p-1';
    tableButton.title = 'Xem dạng bảng';

    const tableIcon = document.createElement('iconify-icon');
    tableIcon.setAttribute('icon', 'lucide:table-2');
    tableIcon.className = 'text-base';

    tableButton.appendChild(tableIcon);

    const addButton = document.createElement('button');
    addButton.type = 'button';
    addButton.className = 'js-order-add-item text-neutral-400 hover:text-primary-500 transition-colors p-1';
    addButton.title = 'Thêm sản phẩm';

    const addIcon = document.createElement('iconify-icon');
    addIcon.setAttribute('icon', 'lucide:plus');
    addIcon.className = 'text-base';

    addButton.appendChild(addIcon);

    actionWrap.append(tableButton, addButton);
    return actionWrap;
}

document.addEventListener('click', function(e) {
    const tableButton = e.target.closest('.js-order-table-view');
    if (tableButton) {
        e.preventDefault();
        toggleOrderTableView(tableButton);
        return;
    }

    const addButton = e.target.closest('.js-order-add-item');
    if (addButton) {
        e.preventDefault();
        addGlassOrderItem(addButton);
    }
});

function ensureGlassFormLayout(supplyRow) {
    const inputWrap = supplyRow.querySelector('.overflow-x-auto:not(.order-preview-table-wrap), .order-input-layout');
    if (!inputWrap) return null;

    inputWrap.classList.add('order-input-layout');

    const currentContainer = inputWrap.querySelector('.supply-items-container');
    if (!currentContainer) return null;

    if (currentContainer.classList.contains('order-input-rows')) {
        return currentContainer;
    }

    const formContainer = document.createElement('div');
    formContainer.className = 'supply-items-container order-input-rows';
    formContainer.dataset.supplyIndex = currentContainer.dataset.supplyIndex || '0';

    currentContainer.querySelectorAll('.order-item-row').forEach(row => {
        formContainer.appendChild(buildGlassFormRow(row));
    });

    currentContainer.classList.remove('supply-items-container');
    currentContainer.dataset.sourceTableBody = 'true';
    currentContainer.parentElement.insertAdjacentElement('afterend', formContainer);

    return formContainer;
}

function ensureAllGlassFormLayouts() {
    document.querySelectorAll('#glass-supplies-container .order-supply-row').forEach(supplyRow => {
        ensureGlassFormLayout(supplyRow);
    });
}

function refreshOrderPreviewTables(card) {
    card.querySelectorAll('.order-supply-row').forEach(supplyRow => {
        const inputWrap = supplyRow.querySelector('.order-input-layout, .overflow-x-auto:not(.order-preview-table-wrap)');
        if (!inputWrap) return;

        const formContainer = ensureGlassFormLayout(supplyRow);

        let previewWrap = supplyRow.querySelector('.order-preview-table-wrap');
        if (!previewWrap) {
            previewWrap = document.createElement('div');
            previewWrap.className = 'order-preview-table-wrap overflow-x-auto pb-3';
            inputWrap.insertAdjacentElement('afterend', previewWrap);
        }

        const sourceTable = inputWrap.querySelector('table');
        const sourceHead = sourceTable?.querySelector('thead');
        const sourceRows = formContainer ? formContainer.querySelectorAll('.order-item-row') : [];
        if (!sourceTable || !sourceHead) return;

        const previewTable = document.createElement('table');
        previewTable.className = `${sourceTable.className} order-preview-table`;
        const previewHead = sourceHead.cloneNode(true);
        const actionHeader = previewHead.querySelector('tr:first-child th:last-child');
        if (actionHeader) {
            actionHeader.textContent = 'Hành động';
        }
        previewTable.appendChild(previewHead);

        const previewBody = document.createElement('tbody');
        sourceRows.forEach(row => {
            const targetId = row.dataset.previewTargetId || `order-form-row-${Date.now()}-${Math.random().toString(36).slice(2)}`;
            row.dataset.previewTargetId = targetId;
            const previewRow = document.createElement('tr');
            previewRow.className = 'order-preview-row';

            const indexCell = document.createElement('td');
            indexCell.className = 'border border-neutral-200 text-center';
            indexCell.textContent = row.querySelector('.row-index')?.textContent?.trim() || '-';
            previewRow.appendChild(indexCell);

            row.querySelectorAll('.order-form-field').forEach(field => {
                const previewCell = document.createElement('td');
                previewCell.className = 'border border-neutral-200';
                previewCell.textContent = getOrderPreviewFieldValue(field);
                previewRow.appendChild(previewCell);
            });

            const actionCell = document.createElement('td');
            actionCell.className = 'border border-neutral-200 text-center';
            const actionButton = buildPreviewJumpButton(targetId);
            actionCell.appendChild(actionButton);
            previewRow.appendChild(actionCell);

            previewBody.appendChild(previewRow);
        });

        previewTable.appendChild(previewBody);
        previewWrap.replaceChildren(previewTable);
    });
}

function buildPreviewJumpButton(targetId) {
    const actionButton = document.createElement('button');
    actionButton.type = 'button';
    actionButton.className = 'btn border border-primary-600 text-primary-600 hover:bg-primary-600 hover:text-white rounded-lg w-9 h-9 p-0 inline-flex items-center justify-center';
    actionButton.dataset.previewTargetId = targetId;
    actionButton.title = 'Đi tới dòng nhập liệu';
    actionButton.onclick = () => jumpToOrderFormRow(actionButton);

    const icon = document.createElement('iconify-icon');
    icon.setAttribute('icon', 'lucide:edit-3');
    icon.className = 'text-base';

    actionButton.appendChild(icon);
    return actionButton;
}

function jumpToOrderFormRow(button) {
    const card = button.closest('.order-entry-card');
    if (!card) return;

    const targetId = button.dataset.previewTargetId;
    const targetRow = targetId ? card.querySelector(`[data-preview-target-id="${targetId}"]`) : null;
    if (!targetRow) return;

    card.classList.remove('is-table-view');

    card.querySelectorAll('.order-table-view-label').forEach(label => {
        label.textContent = 'Xem dạng bảng';
    });

    targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
    targetRow.classList.add('order-form-row--focus');
    window.setTimeout(() => targetRow.classList.remove('order-form-row--focus'), 1600);
}

function toggleOrderTableView(button) {
    const card = button.closest('.order-entry-card');
    if (!card) return;

    const nextTableView = !card.classList.contains('is-table-view');
    if (nextTableView) {
        refreshOrderPreviewTables(card);
    }

    card.classList.toggle('is-table-view', nextTableView);
    card.querySelectorAll('.order-table-view-label').forEach(label => {
        label.textContent = nextTableView ? 'Ẩn dạng bảng' : 'Xem dạng bảng';
    });
}

// Initial setup for Glass-specific rows if DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('glass-supplies-container');
    if (container && container.querySelectorAll('.order-supply-row').length === 0) {
        addGlassOrderSupply();
    }

    ensureAllGlassFormLayouts();

    document.querySelectorAll('#glass-supplies-container .order-item-row').forEach(row => {
        bindGlassRowEvents(row);
        calculateGlassTotalPrice(row);
    });

    // Recalculate codes on load
    updateGlassRowIndexes();

    const orderCodeInput = document.getElementById('order-code-input');
    if (orderCodeInput) {
        orderCodeInput.addEventListener('input', () => updateGlassRowIndexes());
    }
});

// Live listener for supply code input changes
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('order-supply-code-input')) {
        updateGlassRowIndexes();
    }
});
</script>
