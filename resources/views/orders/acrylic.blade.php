{{-- Order Supplies & Items Section Card --}}
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
    #order-supplies-container .order-supply-row table {
        font-size: 75% !important;
    }
    #order-supplies-container .order-supply-row table input,
    #order-supplies-container .order-supply-row table select,
    #order-supplies-container .order-supply-row table textarea {
        font-size: 75% !important;
        height: 24px !important;
        min-height: 24px !important;
        padding-top: 2px !important;
        padding-bottom: 2px !important;
        line-height: 1.2 !important;
    }
    #order-supplies-container .order-supply-row table th,
    #order-supplies-container .order-supply-row table td {
        padding: 3px 4px !important;
    }
    /* Override all td max-widths to 65% of original */
    #order-supplies-container .order-supply-row table td[style*="width: 45px"],
    #order-supplies-container .order-supply-row table td[style*="width:45px"] { width: 30px !important; min-width: 30px !important; max-width: 30px !important; }
    #order-supplies-container .order-supply-row table td[style*="width: 160px"],
    #order-supplies-container .order-supply-row table td[style*="width:160px"] { width: 110px !important; min-width: 110px !important; max-width: 110px !important; }
    #order-supplies-container .order-supply-row table td[style*="min-width: 220px"],
    #order-supplies-container .order-supply-row table td[style*="min-width:220px"] { min-width: 150px !important; }
    #order-supplies-container .order-supply-row table td[style*="width: 200px"],
    #order-supplies-container .order-supply-row table td[style*="width:200px"] { width: 130px !important; min-width: 130px !important; max-width: 130px !important; }
    #order-supplies-container .order-supply-row table td[style*="width: 70px"],
    #order-supplies-container .order-supply-row table td[style*="width:70px"] { width: 50px !important; min-width: 50px !important; max-width: 50px !important; }
    #order-supplies-container .order-supply-row table td[style*="width: 100px"],
    #order-supplies-container .order-supply-row table td[style*="width:100px"] { width: 70px !important; min-width: 70px !important; max-width: 70px !important; }
    #order-supplies-container .order-supply-row table td[style*="width: 110px"],
    #order-supplies-container .order-supply-row table td[style*="width:110px"] { width: 80px !important; min-width: 80px !important; max-width: 80px !important; }
    #order-supplies-container .order-supply-row table td[style*="width: 120px"],
    #order-supplies-container .order-supply-row table td[style*="width:120px"] { width: 90px !important; min-width: 90px !important; max-width: 90px !important; }
    #order-supplies-container .order-supply-row table td[style*="min-width: 160px"],
    #order-supplies-container .order-supply-row table td[style*="min-width:160px"],
    #order-supplies-container .order-supply-row table td[style*="width: 160px"],
    #order-supplies-container .order-supply-row table td[style*="width:160px"] { min-width: 120px !important; }
    #order-supplies-container .order-supply-row table td[style*="width: 80px"],
    #order-supplies-container .order-supply-row table td[style*="width:80px"] { width: 60px !important; min-width: 60px !important; max-width: 60px !important; }
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
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(4)::before { content: "Cao"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(5)::before { content: "Rộng"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(6)::before { content: "Số lượng"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(7)::before { content: "Cạnh vát"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(8)::before { content: "Chiều vân"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(9)::before { content: "Cánh (m2)"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(10)::before { content: "Phào (m)"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(11)::before { content: "Vát"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(12)::before { content: "Vân dọc CNC"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(13)::before { content: "Đơn giá"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(14)::before { content: "Thành tiền"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(15)::before { content: "Ghi chú"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(16)::before { content: "Hành động"; }
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
            <h6 class="font-bold text-base text-neutral-800 m-0">Danh sách Vật tư & Sản phẩm (Acrylic)</h6>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleOrderTableView(this)" class="btn btn-sm bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:table-2" class="text-lg"></iconify-icon>
                <span class="order-table-view-label">Xem dạng bảng</span>
            </button>
            <button type="button" onclick="addOrderSupply()" class="order-table-form-action btn btn-sm btn-primary rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon> Thêm vật tư
            </button>
        </div>
    </div>
    <div id="order-supplies-container" class="space-y-6">
        @if(isset($acrylicOrder) && in_array($acrylicOrder->type, ['acrylic', 'glass']) && $acrylicOrder->supplies->count() > 0)
            @foreach($acrylicOrder->supplies as $supplyIndex => $supply)
            <div class="order-supply-row bg-neutral-50/50 border border-primary-600 rounded-xl p-5 mb-2 relative shadow-sm" data-supply-id="{{ $supply->id }}">
                
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
                        <button type="button" onclick="addOrderItem(this)" class="order-table-form-action btn btn-sm btn-outline-primary rounded-lg flex items-center gap-1">
                            <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon> Thêm sản phẩm
                        </button>
                        <button type="button" onclick="this.closest('.order-supply-row').remove()" class="order-table-form-action text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                            <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto pb-3">
                    <table class="table bordered-table sm-table mb-0 min-w-[1100px] border border-neutral-200">
                        <thead>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" rowspan="2" style="width: 30px; min-width: 30px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Stt</th>
                                <th scope="col" rowspan="2" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã SP</th>
                                <th scope="col" rowspan="2" style="width: 150px; min-width: 150px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên sản phẩm <span class="text-danger-500">*</span></th>
                                <th scope="col" colspan="2" style="white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Kích thước (mm)</th>
                                <th scope="col" rowspan="2" style="width: 50px; min-width: 50px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng <span class="text-danger-500">*</span></th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Cạnh Vát</th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều vân</th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Cánh (m2)</th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Phào (m)</th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Vát</th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Vân dọc CNC</th>
                                <th scope="col" rowspan="2" style="width: 80px; min-width: 80px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                                <th scope="col" rowspan="2" style="width: 90px; min-width: 90px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                                <th scope="col" rowspan="2" style="min-width: 120px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                                <th scope="col" rowspan="2" style="width: 60px; min-width: 60px; white-space: nowrap; position: sticky; right: 0; z-index: 3; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase bg-neutral-50">Hành động</th>
                            </tr>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" style="width: 130px; min-width: 130px; white-space: nowrap;" class="border border-neutral-200 bg-yellow-100/70 font-semibold text-xs text-neutral-700 uppercase">Cao (chiều vân)</th>
                                <th scope="col" style="width: 130px; min-width: 130px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
                            </tr>
                        </thead>
                        <tbody class="supply-items-container" data-supply-index="{{ $supplyIndex }}">
                            @foreach($supply->items as $itemIndex => $item)
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
                                <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->height }}">
                                </td>
                                <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->width }}">
                                </td>
                                <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="1" min="1" required value="{{ $item->quantity }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Cạnh vát" value="{{ $item->edge_bevel }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][grain_direction]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8 text-xs">
                                        <option value="0" {{ $item->grain_direction == 0 ? 'selected' : '' }}>0</option>
                                        <option value="2" {{ $item->grain_direction == 2 ? 'selected' : '' }}>2</option>
                                    </select>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][wing_area]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->wing_area }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][molding_length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->molding_length }}">
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Vát" value="{{ $item->bevel }}">
                                </td>
                                <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][vertical_grain_cnc]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Vân dọc CNC" value="{{ $item->vertical_grain_cnc }}">
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
                                        <button type="button" onclick="duplicateAcrylicRow(this)" class="text-neutral-400 hover:text-primary-500 transition-colors p-1" title="Nhân bản sản phẩm">
                                            <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                                        </button>
                                        <button type="button" onclick="removeOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
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
            @php $supplyIndex = $acrylicOrder->supplies->count() @endphp
        @else
            @php $supplyIndex = 0 @endphp
        @endif
    </div>
</div>

<script>
let supplyIndex = {{ $supplyIndex ?? 0 }};

function addOrderSupply() {
    const container = document.getElementById('order-supplies-container');
    const newSupply = document.createElement('div');
    newSupply.className = 'order-supply-row bg-neutral-50/50 border border-primary-600 rounded-xl p-5 mb-2 relative shadow-sm';
    newSupply.innerHTML = `
        <div class="flex items-center justify-between gap-4 border-b border-neutral-200 pb-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                    <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                </div>
                <input type="text" name="supplies[${supplyIndex}][order_supply_code]" class="order-supply-code-input form-control form-control-sm rounded-lg w-40 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Mã vật tư">
                <input type="text" name="supplies[${supplyIndex}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)">
                <input type="number" name="supplies[${supplyIndex}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="0.01" value="0">
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="addOrderItem(this)" class="order-table-form-action btn btn-sm btn-outline-primary rounded-lg flex items-center gap-1">
                    <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon> Thêm sản phẩm
                </button>
                <button type="button" onclick="this.closest('.order-supply-row').remove()" class="order-table-form-action text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                    <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto pb-3">
            <table class="table bordered-table sm-table mb-0 min-w-[1700px] border border-neutral-200">
                <thead>
                    <tr class="bg-neutral-50 text-center">
                        <th scope="col" rowspan="2" style="width: 45px; min-width: 45px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Stt</th>
                        <th scope="col" rowspan="2" style="width: 160px; min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã SP</th>
                        <th scope="col" rowspan="2" style="min-width: 220px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên sản phẩm <span class="text-danger-500">*</span></th>
                        <th scope="col" colspan="2" style="white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Kích thước (mm)</th>
                        <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng <span class="text-danger-500">*</span></th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Cạnh Vát</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều vân</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Cánh (m2)</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Phào (m)</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Vát</th>
                        <th scope="col" rowspan="2" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Vân dọc CNC</th>
                        <th scope="col" rowspan="2" style="width: 110px; min-width: 110px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                        <th scope="col" rowspan="2" style="width: 120px; min-width: 120px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                        <th scope="col" rowspan="2" style="min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                        <th scope="col" rowspan="2" style="width: 80px; min-width: 80px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Hành động</th>
                    </tr>
                    <tr class="bg-neutral-50 text-center">
                        <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="border border-neutral-200 bg-yellow-100/70 font-semibold text-xs text-neutral-700 uppercase">Cao (chiều vân)</th>
                        <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
                    </tr>
                </thead>
                <tbody class="supply-items-container" data-supply-index="${supplyIndex}">
                </tbody>
            </table>
        </div>
    `;
    container.appendChild(newSupply);

    // Add one product row by default
    const addProductBtn = newSupply.querySelector('button[onclick^="addOrderItem"]');
    if (addProductBtn) {
        addOrderItem(addProductBtn);
    }

    ensureAcrylicFormLayout(newSupply);
    supplyIndex++;
}

function addOrderItem(button) {
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
            height: lastRow.querySelector('input[name*="[height]"]') ? lastRow.querySelector('input[name*="[height]"]').value : '',
            width: lastRow.querySelector('input[name*="[width]"]') ? lastRow.querySelector('input[name*="[width]"]').value : '',
            quantity: lastRow.querySelector('input[name*="[quantity]"]') ? lastRow.querySelector('input[name*="[quantity]"]').value : '1',
            edge_bevel: lastRow.querySelector('input[name*="[edge_bevel]"]') ? lastRow.querySelector('input[name*="[edge_bevel]"]').value : '',
            grain_direction: lastRow.querySelector('select[name*="[grain_direction]"]') ? lastRow.querySelector('select[name*="[grain_direction]"]').value : '0',
            wing_area: lastRow.querySelector('input[name*="[wing_area]"]') ? lastRow.querySelector('input[name*="[wing_area]"]').value : '',
            molding_length: lastRow.querySelector('input[name*="[molding_length]"]') ? lastRow.querySelector('input[name*="[molding_length]"]').value : '',
            bevel: lastRow.querySelector('input[name*="[bevel]"]') ? lastRow.querySelector('input[name*="[bevel]"]').value : '',
            vertical_grain_cnc: lastRow.querySelector('input[name*="[vertical_grain_cnc]"]') ? lastRow.querySelector('input[name*="[vertical_grain_cnc]"]').value : '',
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
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.height : ''}">
        </td>
        <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.width : ''}">
        </td>
        <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="1" min="1" required value="${lastData ? lastData.quantity : '1'}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][edge_bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Cạnh vát" value="${lastData ? lastData.edge_bevel : ''}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <select name="supplies[${supplyIndex}][items][${itemIndex}][grain_direction]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8 text-xs">
                <option value="0" ${lastData && lastData.grain_direction === '0' ? 'selected' : ''}>0</option>
                <option value="2" ${lastData && lastData.grain_direction === '2' ? 'selected' : ''}>2</option>
            </select>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][wing_area]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.wing_area : ''}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][molding_length]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.molding_length : ''}">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][bevel]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Vát" value="${lastData ? lastData.bevel : ''}">
        </td>
        <td style="width: 110px; min-width: 110px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][vertical_grain_cnc]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="Vân dọc CNC" value="${lastData ? lastData.vertical_grain_cnc : ''}">
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
                <button type="button" onclick="duplicateAcrylicRow(this)" class="text-neutral-400 hover:text-primary-500 transition-colors p-1" title="Nhân bản sản phẩm">
                    <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                </button>
                <button type="button" onclick="removeOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
                    <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                </button>
            </div>
        </td>
    `;
    const rowToAppend = container.classList.contains('order-input-rows') ? buildAcrylicFormRow(newItem) : newItem;
    container.appendChild(rowToAppend);
    
    const newRow = container.lastElementChild;
    bindAcrylicRowEvents(newRow);
    
    updateOrderSummary();
    updateAcrylicRowIndexes(container);
}

function removeOrderItem(button) {
    const row = button.closest('.order-item-row');
    const tbody = row.closest('.supply-items-container');
    row.remove();
    updateOrderSummary();
    updateAcrylicRowIndexes(tbody);
}

function updateAcrylicRowIndexes() {
    const orderCode = document.getElementById('order-code-input')?.value || '';
    let globalPieceIndex = 1;
    let globalItemIndex = 1;

    document.querySelectorAll('#order-supplies-container .order-supply-row').forEach((supplyRow, supplyIndex) => {
        const supplyCode = supplyRow.querySelector('.order-supply-code-input')?.value || '';
        const tbody = supplyRow.querySelector('.supply-items-container');
        if (!tbody) return;

        tbody.querySelectorAll('.order-item-row').forEach((row, itemIndex) => {
            // Update row STT
            const indexEl = row.querySelector('.row-index');
            if (indexEl) indexEl.textContent = globalItemIndex;

            // Get quantity
            const quantityInput = row.querySelector('input[name*="[quantity]"]');
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

function bindAcrylicRowEvents(row) {
    const heightInput = row.querySelector('input[name*="[height]"]');
    const widthInput = row.querySelector('input[name*="[width]"]');
    const quantityInput = row.querySelector('input[name*="[quantity]"]');
    const wingAreaInput = row.querySelector('input[name*="[wing_area]"]');
    const moldingLengthInput = row.querySelector('input[name*="[molding_length]"]');
    const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
    const bevelInput = row.querySelector('input[name*="[bevel]"]');
    
    if (heightInput) heightInput.addEventListener('input', () => calculateTotalPrice(row, 'height'));
    if (widthInput) widthInput.addEventListener('input', () => calculateTotalPrice(row, 'width'));
    if (quantityInput) {
        quantityInput.addEventListener('input', () => {
            calculateTotalPrice(row, 'quantity');
            updateAcrylicRowIndexes();
        });
    }
    if (wingAreaInput) wingAreaInput.addEventListener('input', () => calculateTotalPrice(row, 'wing_area'));
    if (moldingLengthInput) moldingLengthInput.addEventListener('input', () => calculateTotalPrice(row, 'molding_length'));
    if (unitPriceInput) unitPriceInput.addEventListener('input', () => calculateTotalPrice(row, 'unit_price'));
    if (bevelInput) bevelInput.addEventListener('input', () => updateEdgeBevel(row));
}

function duplicateAcrylicRow(button) {
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

    bindAcrylicRowEvents(newRow);
    updateAcrylicRowIndexes();
    updateOrderSummary();
}

function calculateTotalPrice(row, sourceEvent) {
    const heightInput = row.querySelector('input[name*="[height]"]');
    const widthInput = row.querySelector('input[name*="[width]"]');
    const quantityInput = row.querySelector('input[name*="[quantity]"]');
    const wingAreaInput = row.querySelector('input[name*="[wing_area]"]');
    const moldingLengthInput = row.querySelector('input[name*="[molding_length]"]');
    const unitPriceInput = row.querySelector('input[name*="[unit_price]"]');
    
    if (!heightInput || !widthInput || !quantityInput || !wingAreaInput || !moldingLengthInput) return;

    const height = parseFloat(heightInput.value) || 0;
    const width = parseFloat(widthInput.value) || 0;
    const quantity = parseFloat(quantityInput.value) || 0;
    const unitPrice = parseFloat(unitPriceInput ? unitPriceInput.value : 0) || 0;
    
    if (sourceEvent !== 'wing_area') {
        let wingArea = 0;
        if (height > 0 && width > 0 && quantity > 0) {
            wingArea = (height * width * quantity) / 1000000;
        }
        wingAreaInput.value = wingArea > 0 ? wingArea.toFixed(2) : '';
    }
    
    const currentWingArea = parseFloat(wingAreaInput.value) || 0;
    const moldingLength = parseFloat(moldingLengthInput.value) || 0;
    
    const totalPrice = Math.round((currentWingArea + moldingLength) * unitPrice);
    const totalPriceInput = row.querySelector('input[name*="[total_price]"]');
    if (totalPriceInput) {
        totalPriceInput.value = totalPrice > 0 ? totalPrice : 0;
    }
    updateOrderSummary();
}

const acrylicPreviewLabels = [
    'STT',
    'Mã SP',
    'Tên sản phẩm',
    'Cao',
    'Rộng',
    'Số lượng',
    'Cạnh vát',
    'Chiều vân',
    'Cánh (m2)',
    'Phào (m)',
    'Vát',
    'Vân dọc CNC',
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

function getAcrylicFieldLabel(index) {
    return acrylicPreviewLabels[index] || 'Thông tin';
}

function getAcrylicFormLabel(index) {
    switch (index) {
        case 0: return 'STT';
        case 1: return 'Mã SP';
        case 2: return 'Tên sản phẩm';
        case 3: return 'Cao (chiều vân)';
        case 4: return 'Rộng';
        case 5: return 'Số lượng';
        case 6: return 'Cạnh vát';
        case 7: return 'Chiều vân';
        case 8: return 'Cánh (m2)';
        case 9: return 'Phào (m)';
        case 10: return 'Vát';
        case 11: return 'Vân dọc CNC';
        case 12: return 'Đơn giá';
        case 13: return 'Thành tiền';
        case 14: return 'Ghi chú';
        case 15: return 'Hành động';
        default: return 'Thông tin';
    }
}

function updateAcrylicFormSectionClass(section) {
    section.classList.remove('order-form-section--1', 'order-form-section--2', 'order-form-section--3', 'order-form-section--4');
    section.classList.add(`order-form-section--${section.children.length}`);
}

function getOrCreateAcrylicFormSection(formRow) {
    let section = formRow._orderFormCurrentSection;
    if (!section || section.children.length >= 4 || formRow.lastElementChild !== section) {
        section = document.createElement('div');
        section.className = 'order-form-section';
        formRow.appendChild(section);
        formRow._orderFormCurrentSection = section;
    }

    return section;
}

function appendAcrylicFieldToSection(formRow, fieldWrap) {
    const section = getOrCreateAcrylicFormSection(formRow);
    section.appendChild(fieldWrap);
    updateAcrylicFormSectionClass(section);
}

function appendAcrylicFormField(formRow, fieldWrap, index) {
    appendAcrylicFieldToSection(formRow, fieldWrap);
}

function buildAcrylicFormRow(sourceRow) {
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
    actions.appendChild(buildAcrylicFormQuickActions());
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
        fieldWrap.dataset.previewLabel = getAcrylicFormLabel(index);

        const label = document.createElement('label');
        label.className = 'order-form-field__label';
        label.textContent = getAcrylicFormLabel(index);
        fieldWrap.appendChild(label);

        while (cell.firstChild) {
            fieldWrap.appendChild(cell.firstChild);
        }

        appendAcrylicFormField(formRow, fieldWrap, index);
    });

    return formRow;
}

function buildAcrylicFormQuickActions() {
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
        addOrderItem(addButton);
    }
});

function ensureAcrylicFormLayout(supplyRow) {
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
        formContainer.appendChild(buildAcrylicFormRow(row));
    });

    currentContainer.classList.remove('supply-items-container');
    currentContainer.dataset.sourceTableBody = 'true';
    currentContainer.parentElement.insertAdjacentElement('afterend', formContainer);

    return formContainer;
}

function ensureAllAcrylicFormLayouts() {
    document.querySelectorAll('#order-supplies-container .order-supply-row').forEach(supplyRow => {
        ensureAcrylicFormLayout(supplyRow);
    });
}

function refreshOrderPreviewTables(card) {
    card.querySelectorAll('.order-supply-row').forEach(supplyRow => {
        const inputWrap = supplyRow.querySelector('.order-input-layout, .overflow-x-auto:not(.order-preview-table-wrap)');
        if (!inputWrap) return;

        const formContainer = ensureAcrylicFormLayout(supplyRow);

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

function updateEdgeBevel(row) {
    const bevelInput = row.querySelector('input[name*="[bevel]"]');
    const edgeBevelInput = row.querySelector('input[name*="[edge_bevel]"]');
    if (!bevelInput || !edgeBevelInput) return;
    
    const bevelValue = bevelInput.value.trim();
    if (bevelValue) {
        if (/^vát/i.test(bevelValue)) {
            edgeBevelInput.value = bevelValue;
        } else {
            edgeBevelInput.value = 'Vát ' + bevelValue;
        }
    } else {
        edgeBevelInput.value = '';
    }
}

// Initial attachment setup for Acrylic-specific rows if DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('order-supplies-container');
    if (container && container.querySelectorAll('.order-supply-row').length === 0) {
        addOrderSupply();
    }

    ensureAllAcrylicFormLayouts();

    document.querySelectorAll('#order-supplies-container .order-item-row').forEach(row => {
        bindAcrylicRowEvents(row);
        calculateTotalPrice(row);
        updateEdgeBevel(row);
    });

    // Recalculate codes on load
    updateAcrylicRowIndexes();

    const orderCodeInput = document.getElementById('order-code-input');
    if (orderCodeInput) {
        orderCodeInput.addEventListener('input', () => updateAcrylicRowIndexes());
    }
});

// Live listener for supply code input changes
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('order-supply-code-input')) {
        updateAcrylicRowIndexes();
    }
});
</script>
