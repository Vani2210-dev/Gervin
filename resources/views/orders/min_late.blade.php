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
    /* Hide dropdown arrow in very narrow select boxes for a clean, centered look */
    .hide-arrow {
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background-image: none !important;
        padding-right: 4px !important;
        padding-left: 4px !important;
        text-align: center !important;
        text-align-last: center !important;
        cursor: pointer;
    }
    .hide-arrow option {
        text-align: center;
    }
    /* === COMPACT TABLE: 75% font scale === */
    #min-late-supplies-container .order-supply-row table {
        font-size: 75% !important;
    }
    #min-late-supplies-container .order-supply-row table input,
    #min-late-supplies-container .order-supply-row table select,
    #min-late-supplies-container .order-supply-row table textarea {
        font-size: 75% !important;
        height: 24px !important;
        min-height: 24px !important;
        padding-top: 2px !important;
        padding-bottom: 2px !important;
        line-height: 1.2 !important;
    }
    #min-late-supplies-container .order-supply-row table th,
    #min-late-supplies-container .order-supply-row table td {
        padding: 3px 4px !important;
    }
    /* Override td widths to ~65% of original */
    #min-late-supplies-container .order-supply-row table td[style*="width: 45px"],
    #min-late-supplies-container .order-supply-row table td[style*="width:45px"] { width: 30px !important; min-width: 30px !important; max-width: 30px !important; }
    #min-late-supplies-container .order-supply-row table td[style*="width: 160px"],
    #min-late-supplies-container .order-supply-row table td[style*="width:160px"] { width: 110px !important; min-width: 110px !important; max-width: 110px !important; }
    #min-late-supplies-container .order-supply-row table td[style*="width: 200px"],
    #min-late-supplies-container .order-supply-row table td[style*="width:200px"] { width: 130px !important; min-width: 130px !important; max-width: 130px !important; }
    #min-late-supplies-container .order-supply-row table td[style*="width: 70px"],
    #min-late-supplies-container .order-supply-row table td[style*="width:70px"] { width: 50px !important; min-width: 50px !important; max-width: 50px !important; }
    #min-late-supplies-container .order-supply-row table td[style*="width: 100px"],
    #min-late-supplies-container .order-supply-row table td[style*="width:100px"] { width: 65px !important; min-width: 65px !important; max-width: 65px !important; }
    #min-late-supplies-container .order-supply-row table td[style*="width: 105px"],
    #min-late-supplies-container .order-supply-row table td[style*="width:105px"] { width: 70px !important; min-width: 70px !important; max-width: 70px !important; }
    #min-late-supplies-container .order-supply-row table td[style*="width: 110px"],
    #min-late-supplies-container .order-supply-row table td[style*="width:110px"] { width: 75px !important; min-width: 75px !important; max-width: 75px !important; }
    #min-late-supplies-container .order-supply-row table td[style*="width: 80px"],
    #min-late-supplies-container .order-supply-row table td[style*="width:80px"] { width: 55px !important; min-width: 55px !important; max-width: 55px !important; }
    #min-late-supplies-container .order-supply-row table td[style*="min-width: 140px"],
    #min-late-supplies-container .order-supply-row table td[style*="min-width:140px"] { min-width: 90px !important; }
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

    .order-entry-card:not(.is-table-view) .overflow-x-auto,
    .payment-entry-card:not(.is-table-view) .overflow-x-auto {
        overflow: visible !important;
    }
    .order-entry-card:not(.is-table-view) table,
    .payment-entry-card:not(.is-table-view) table {
        display: block;
        min-width: 0 !important;
        border: 0 !important;
    }
    .order-entry-card:not(.is-table-view) thead,
    .payment-entry-card:not(.is-table-view) thead {
        display: none;
    }
    .order-entry-card:not(.is-table-view) tbody.supply-items-container,
    .payment-entry-card:not(.is-table-view) tbody {
        display: grid;
        gap: 1rem;
    }
    .order-entry-card:not(.is-table-view) tr.order-item-row,
    .payment-entry-card:not(.is-table-view) tr.payment-detail-row {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem 1.25rem;
        padding: 1.25rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .order-entry-card:not(.is-table-view) tr.order-item-row td,
    .payment-entry-card:not(.is-table-view) tr.payment-detail-row td {
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
    .order-entry-card:not(.is-table-view) tr.order-item-row td::before,
    .payment-entry-card:not(.is-table-view) tr.payment-detail-row td::before {
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
    .order-entry-card:not(.is-table-view) tr.order-item-row textarea,
    .payment-entry-card:not(.is-table-view) tr.payment-detail-row input,
    .payment-entry-card:not(.is-table-view) tr.payment-detail-row select,
    .payment-entry-card:not(.is-table-view) tr.payment-detail-row textarea {
        width: 100% !important;
        height: 42px !important;
        min-height: 42px !important;
        padding: 0.5rem 0.75rem !important;
        font-size: 0.875rem !important;
        line-height: 1.25rem !important;
        text-align: left !important;
    }
    .order-entry-card:not(.is-table-view) tr.order-item-row textarea,
    .payment-entry-card:not(.is-table-view) tr.payment-detail-row textarea {
        height: auto !important;
    }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:first-child,
    .order-entry-card:not(.is-table-view) tr.order-item-row td:last-child,
    .payment-entry-card:not(.is-table-view) tr.payment-detail-row td:first-child,
    .payment-entry-card:not(.is-table-view) tr.payment-detail-row td:last-child {
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
    .order-entry-card:not(.is-table-view) .order-input-layout,
    .payment-entry-card:not(.is-table-view) .order-input-layout {
        display: grid !important;
        gap: 1rem;
        overflow: visible !important;
    }
    .order-entry-card:not(.is-table-view) .order-input-layout table,
    .payment-entry-card:not(.is-table-view) .order-input-layout table {
        display: none !important;
    }
    .order-entry-card:not(.is-table-view) .supply-items-container.order-input-rows,
    .payment-entry-card:not(.is-table-view) .payment-detail-rows {
        display: grid;
        gap: 1rem;
    }
    .order-entry-card:not(.is-table-view) .order-item-row.order-form-row,
    .payment-entry-card:not(.is-table-view) .payment-detail-row.order-form-row {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem 1.25rem;
        padding: 1.25rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .order-entry-card:not(.is-table-view) .order-form-field,
    .payment-entry-card:not(.is-table-view) .order-form-field {
        display: block;
        min-width: 0;
    }
    .order-entry-card:not(.is-table-view) .order-form-section,
    .payment-entry-card:not(.is-table-view) .order-form-section {
        grid-column: 1 / -1;
        display: grid;
        gap: 1rem 1.25rem;
    }
    .order-entry-card:not(.is-table-view) .order-form-section--1,
    .payment-entry-card:not(.is-table-view) .order-form-section--1 {
        grid-template-columns: minmax(0, 1fr);
    }
    .order-entry-card:not(.is-table-view) .order-form-section--2,
    .payment-entry-card:not(.is-table-view) .order-form-section--2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .order-entry-card:not(.is-table-view) .order-form-section--3,
    .payment-entry-card:not(.is-table-view) .order-form-section--3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .order-entry-card:not(.is-table-view) .order-form-section--4,
    .payment-entry-card:not(.is-table-view) .order-form-section--4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
    .order-entry-card:not(.is-table-view) .order-form-field__label,
    .payment-entry-card:not(.is-table-view) .order-form-field__label {
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
    .order-entry-card:not(.is-table-view) .order-form-field textarea,
    .payment-entry-card:not(.is-table-view) .order-form-field input,
    .payment-entry-card:not(.is-table-view) .order-form-field select,
    .payment-entry-card:not(.is-table-view) .order-form-field textarea {
        width: 100% !important;
        height: 42px !important;
        min-height: 42px !important;
        padding: 0.5rem 0.75rem !important;
        font-size: 0.875rem !important;
        line-height: 1.25rem !important;
        text-align: left !important;
    }
    .order-entry-card:not(.is-table-view) .order-form-field--action,
    .payment-entry-card:not(.is-table-view) .order-form-field--action {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }
    .order-entry-card:not(.is-table-view) .order-form-row__header,
    .payment-entry-card:not(.is-table-view) .order-form-row__header {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .order-entry-card:not(.is-table-view) .order-form-row__badge,
    .payment-entry-card:not(.is-table-view) .order-form-row__badge {
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
    .order-entry-card:not(.is-table-view) .order-form-row__actions,
    .payment-entry-card:not(.is-table-view) .order-form-row__actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    .order-entry-card:not(.is-table-view) .order-form-row__actions > button,
    .payment-entry-card:not(.is-table-view) .order-form-row__actions > button,
    .order-entry-card:not(.is-table-view) .order-form-row__quick-actions button,
    .payment-entry-card:not(.is-table-view) .order-form-row__quick-actions button {
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        background: #f8fafc;
    }
    .order-entry-card:not(.is-table-view) .order-form-row__quick-actions,
    .payment-entry-card:not(.is-table-view) .order-form-row__quick-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(1)::before { content: "STT"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(2)::before { content: "Mã hàng"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(3)::before { content: "Tên hàng hóa, dịch vụ"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(4)::before { content: "Cao"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(5)::before { content: "Rộng"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(6)::before { content: "Số lượng"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(7)::before { content: "Dán cạnh cao 1"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(8)::before { content: "Dán cạnh cao 2"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(9)::before { content: "Dán cạnh rộng 1"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(10)::before { content: "Dán cạnh rộng 2"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(11)::before { content: "Mét dán thẳng"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(12)::before { content: "Mét dán vát"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(13)::before { content: "Mét vát mòi"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(14)::before { content: "Bản rộng 40-59"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(15)::before { content: "Bản rộng 17-39"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(16)::before { content: "Bản rộng 25-35"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(17)::before { content: "Vát tay nắm âm"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(18)::before { content: "Tấm CNC"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(19)::before { content: "Chiều vân"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(20)::before { content: "Ghi chú"; }
    .order-entry-card:not(.is-table-view) tr.order-item-row td:nth-child(21)::before { content: "Hành động"; }
    .payment-entry-card:not(.is-table-view) tr.payment-detail-row td:nth-child(1)::before { content: "STT"; }
    .payment-entry-card:not(.is-table-view) tr.payment-detail-row td:nth-child(2)::before { content: "Tên chi phí/dịch vụ"; }
    .payment-entry-card:not(.is-table-view) tr.payment-detail-row td:nth-child(3)::before { content: "Đơn vị"; }
    .payment-entry-card:not(.is-table-view) tr.payment-detail-row td:nth-child(4)::before { content: "Số lượng"; }
    .payment-entry-card:not(.is-table-view) tr.payment-detail-row td:nth-child(5)::before { content: "Đơn giá"; }
    .payment-entry-card:not(.is-table-view) tr.payment-detail-row td:nth-child(6)::before { content: "Giá riêng"; }
    .payment-entry-card:not(.is-table-view) tr.payment-detail-row td:nth-child(7)::before { content: "Thành tiền"; }
    .payment-entry-card:not(.is-table-view) tr.payment-detail-row td:nth-child(8)::before { content: "Hành động"; }
    .order-entry-card .order-preview-table-wrap,
    .payment-entry-card .order-preview-table-wrap {
        display: none;
    }
    .order-entry-card.is-table-view .order-input-layout,
    .payment-entry-card.is-table-view .order-input-layout {
        display: none !important;
    }
    .order-entry-card.is-table-view .order-preview-table-wrap,
    .payment-entry-card.is-table-view .order-preview-table-wrap {
        display: block;
    }
    .order-entry-card.is-table-view .order-table-form-action,
    .payment-entry-card.is-table-view .order-table-form-action {
        display: none !important;
    }
    .order-preview-table td {
        vertical-align: middle;
        white-space: nowrap;
    }
    .order-entry-card:not(.is-table-view) .order-form-row--focus,
    .payment-entry-card:not(.is-table-view) .order-form-row--focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .16), 0 12px 24px rgba(15, 23, 42, .08);
    }
    @media (max-width: 1199px) {
        .order-entry-card:not(.is-table-view) tr.order-item-row,
        .payment-entry-card:not(.is-table-view) tr.payment-detail-row,
        .order-entry-card:not(.is-table-view) .order-item-row.order-form-row,
        .payment-entry-card:not(.is-table-view) .payment-detail-row.order-form-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 767px) {
        .order-entry-card:not(.is-table-view) tr.order-item-row,
        .payment-entry-card:not(.is-table-view) tr.payment-detail-row,
        .order-entry-card:not(.is-table-view) .order-item-row.order-form-row,
        .payment-entry-card:not(.is-table-view) .payment-detail-row.order-form-row,
        .order-entry-card:not(.is-table-view) .order-supply-row > .flex.items-center.justify-between > .flex.items-center.gap-3,
        .order-entry-card:not(.is-table-view) .order-form-section,
        .payment-entry-card:not(.is-table-view) .order-form-section {
            grid-template-columns: 1fr;
        }
    }
</style>
<div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm order-entry-card">
    <div class="flex items-center justify-between border-b border-neutral-100 pb-4 mb-5">
        <div class="flex items-center gap-2">
            <iconify-icon icon="lucide:package-open" class="text-xl text-primary-500"></iconify-icon>
            <h6 class="font-bold text-base text-neutral-800 m-0">Danh sách Vật tư & Sản phẩm (Min Late)</h6>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleOrderTableView(this)" class="btn btn-sm bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:table-2" class="text-lg"></iconify-icon>
                <span class="order-table-view-label">Xem dạng bảng</span>
            </button>
            <button type="button" onclick="addMinLateOrderSupply()" class="order-table-form-action btn btn-sm btn-primary rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon> Thêm vật tư
            </button>
        </div>
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
                        <input type="text" name="supplies[{{ $supplyIndex }}][order_supply_code]" class="order-supply-code-input form-control form-control-sm rounded-lg w-40 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Mã vật tư" value="{{ $supply->order_supply_code ?? '' }}">
                        <input type="text" name="supplies[{{ $supplyIndex }}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)" value="{{ $supply->supply_name }}">
                        <input type="number" name="supplies[{{ $supplyIndex }}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="0.01" value="{{ $supply->quantity ?? 0 }}">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="addMinLateOrderItem(this)" class="order-table-form-action btn btn-sm btn-outline-primary rounded-lg flex items-center gap-1">
                            <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon> Thêm sản phẩm
                        </button>
                        <button type="button" onclick="this.closest('.order-supply-row').remove()" class="order-table-form-action text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                            <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto pb-3">
                    <table class="table bordered-table sm-table mb-0 min-w-[2000px] border border-neutral-200">
                        <thead>
                            <tr class="bg-neutral-50 text-center">
                                <th scope="col" rowspan="2" style="width: 45px; min-width: 45px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Stt</th>
                                <th scope="col" rowspan="2" style="width: 160px; min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã hàng</th>
                                <th scope="col" rowspan="2" style="min-width: 180px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên hàng hóa, dịch vụ <span class="text-danger-500">*</span></th>
                                <th scope="col" colspan="2" style="white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Kích thước (mm)</th>
                                <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng</th>
                                <th scope="col" colspan="4" style="white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Dán cạnh</th>
                                <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán thẳng</th>
                                <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán vát</th>
                                <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán vát mòi</th>
                                <th scope="col" rowspan="2" style="width: 105px; min-width: 105px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 40-59mm</th>
                                <th scope="col" rowspan="2" style="width: 105px; min-width: 105px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 17-39mm</th>
                                <th scope="col" rowspan="2" style="width: 105px; min-width: 105px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 25-35mm</th>
                                <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số vát tay nắm âm</th>
                                <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số tấm CNC</th>
                                <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều vân</th>
                                <th scope="col" rowspan="2" style="min-width: 140px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                                <th scope="col" rowspan="2" style="width: 80px; min-width: 80px; white-space: nowrap; position: sticky; right: 0; z-index: 3; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase bg-neutral-50">Hành động</th>
                            </tr>
                            <tr class="bg-neutral-50 text-center">
                                {{-- Kích thước --}}
                                <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="border border-neutral-200 bg-yellow-100/70 font-semibold text-xs text-neutral-700 uppercase">Cao (vân)</th>
                                <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
                                {{-- Dán cạnh --}}
                                <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Cao</th>
                                <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Cao</th>
                                <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
                                <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
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
                                <td style="width: 45px; min-width: 45px; " class="text-center align-middle border border-neutral-200">
                                    <span class="row-index font-semibold text-neutral-500">{{ $itemIndex + 1 }}</span>
                                </td>
                                <td style="width: 160px; min-width: 160px; " class="border border-neutral-200">
                                     <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_code]" class="product-code-input form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs font-semibold text-neutral-600" readonly value="{{ $item->product_code ?? '' }}">
                                 </td>
                                 <td style="min-width: 180px;" class="border border-neutral-200">
                                     <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][product_name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Tên sản phẩm" value="{{ $item->product_name ?? $item->name ?? '' }}">
                                 </td>
                                <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][height]" class="form-control form-control-sm rounded-lg border-yellow-300 focus:border-yellow-500 focus:ring-yellow-500 bg-yellow-50/30 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $size['height'] ?? '' }}">
                                </td>
                                <td style="width: 200px; min-width: 200px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][width]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $size['width'] ?? '' }}">
                                </td>
                                <td style="width: 70px; min-width: 70px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center px-1 py-1 h-8 text-xs" placeholder="1" min="1" required value="{{ $item->quantity }}">
                                </td>
                                {{-- Dán cạnh (4 text inputs) --}}
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_gluing][height_1]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-0.5 h-8 text-xs hide-arrow">
                                        <option value=""></option>
                                        <option value="V" {{ ($gluing['height_1'] ?? '') == 'V' ? 'selected' : '' }}>V</option>
                                        <option value="T" {{ ($gluing['height_1'] ?? '') == 'T' ? 'selected' : '' }}>T</option>
                                        <option value="VAT MOI" {{ ($gluing['height_1'] ?? '') == 'VAT MOI' ? 'selected' : '' }}>VAT MOI</option>
                                        <option value="DS" {{ ($gluing['height_1'] ?? '') == 'DS' ? 'selected' : '' }}>DS</option>
                                        <option value="VAT TNA" {{ ($gluing['height_1'] ?? '') == 'VAT TNA' ? 'selected' : '' }}>VAT TNA</option>
                                        <option value="XEM BAN VE CT" {{ ($gluing['height_1'] ?? '') == 'XEM BAN VE CT' ? 'selected' : '' }}>XEM BAN VE CT</option>
                                    </select>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_gluing][height_2]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-0.5 h-8 text-xs hide-arrow">
                                        <option value=""></option>
                                        <option value="V" {{ ($gluing['height_2'] ?? '') == 'V' ? 'selected' : '' }}>V</option>
                                        <option value="T" {{ ($gluing['height_2'] ?? '') == 'T' ? 'selected' : '' }}>T</option>
                                        <option value="VAT MOI" {{ ($gluing['height_2'] ?? '') == 'VAT MOI' ? 'selected' : '' }}>VAT MOI</option>
                                        <option value="DS" {{ ($gluing['height_2'] ?? '') == 'DS' ? 'selected' : '' }}>DS</option>
                                        <option value="VAT TNA" {{ ($gluing['height_2'] ?? '') == 'VAT TNA' ? 'selected' : '' }}>VAT TNA</option>
                                        <option value="XEM BAN VE CT" {{ ($gluing['height_2'] ?? '') == 'XEM BAN VE CT' ? 'selected' : '' }}>XEM BAN VE CT</option>
                                    </select>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_gluing][width_1]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-0.5 h-8 text-xs hide-arrow">
                                        <option value=""></option>
                                        <option value="V" {{ ($gluing['width_1'] ?? '') == 'V' ? 'selected' : '' }}>V</option>
                                        <option value="T" {{ ($gluing['width_1'] ?? '') == 'T' ? 'selected' : '' }}>T</option>
                                        <option value="VAT MOI" {{ ($gluing['width_1'] ?? '') == 'VAT MOI' ? 'selected' : '' }}>VAT MOI</option>
                                        <option value="DS" {{ ($gluing['width_1'] ?? '') == 'DS' ? 'selected' : '' }}>DS</option>
                                        <option value="VAT TNA" {{ ($gluing['width_1'] ?? '') == 'VAT TNA' ? 'selected' : '' }}>VAT TNA</option>
                                        <option value="XEM BAN VE CT" {{ ($gluing['width_1'] ?? '') == 'XEM BAN VE CT' ? 'selected' : '' }}>XEM BAN VE CT</option>
                                    </select>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <select name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][edge_gluing][width_2]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-0.5 h-8 text-xs hide-arrow">
                                        <option value=""></option>
                                        <option value="V" {{ ($gluing['width_2'] ?? '') == 'V' ? 'selected' : '' }}>V</option>
                                        <option value="T" {{ ($gluing['width_2'] ?? '') == 'T' ? 'selected' : '' }}>T</option>
                                        <option value="VAT MOI" {{ ($gluing['width_2'] ?? '') == 'VAT MOI' ? 'selected' : '' }}>VAT MOI</option>
                                        <option value="DS" {{ ($gluing['width_2'] ?? '') == 'DS' ? 'selected' : '' }}>DS</option>
                                        <option value="VAT TNA" {{ ($gluing['width_2'] ?? '') == 'VAT TNA' ? 'selected' : '' }}>VAT TNA</option>
                                        <option value="XEM BAN VE CT" {{ ($gluing['width_2'] ?? '') == 'XEM BAN VE CT' ? 'selected' : '' }}>XEM BAN VE CT</option>
                                    </select>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][straight_paste_length]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->straight_paste_length ?? 0 }}" readonly>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][beveled_length]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->beveled_length ?? 0 }}" readonly>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][vat_moi_length]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->vat_moi_length ?? 0 }}" readonly>
                                </td>
                                <td style="width: 105px; min-width: 105px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][ban_rong_40_59]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->ban_rong_40_59 ?? 0 }}" readonly>
                                </td>
                                <td style="width: 105px; min-width: 105px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][ban_rong_17_39]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->ban_rong_17_39 ?? 0 }}" readonly>
                                </td>
                                <td style="width: 105px; min-width: 105px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][ban_rong_25_35]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->ban_rong_25_35 ?? 0 }}" readonly>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][beveled_handle]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="{{ $item->beveled_handle ?? 0 }}" readonly>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="number" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][cnc]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="0" value="{{ $item->cnc ?? 0 }}" readonly>
                                </td>
                                <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][direction]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8 text-xs" placeholder="Chiều vân" value="{{ $item->direction ?? '' }}">
                                </td>

                                <td style="min-width: 140px;" class="border border-neutral-200">
                                    <input type="text" name="supplies[{{ $supplyIndex }}][items][{{ $itemIndex }}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Ghi chú" value="{{ $item->notes ?? '' }}">
                                </td>
                                <td style="width: 80px; min-width: 80px; position: sticky; right: 0; z-index: 1; background-color: #fff; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="text-center align-middle border border-neutral-200">
                                    <div class="flex items-center gap-1 justify-center">
                                        <button type="button" onclick="duplicateMinLateRow(this)" class="text-neutral-400 hover:text-primary-500 transition-colors p-1" title="Nhân bản sản phẩm">
                                            <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                                        </button>
                                        <button type="button" onclick="removeMinLateOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
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
            @php $minLateSupplyIndex = $acrylicOrder->supplies->count() @endphp
        @else
            @php $minLateSupplyIndex = 0 @endphp
        @endif
    </div>
</div>

{{-- Invoice Details Card (Min Late only) --}}
<div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm mt-6 payment-entry-card">
    <div class="flex items-center justify-between border-b border-neutral-100 pb-4 mb-5">
        <div class="flex items-center gap-2">
            <iconify-icon icon="lucide:receipt" class="text-xl text-primary-500"></iconify-icon>
            <h6 class="font-bold text-base text-neutral-800 m-0">Chi tiết hóa đơn</h6>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="toggleOrderTableView(this)" class="btn btn-sm bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:table-2" class="text-lg"></iconify-icon>
                <span class="order-table-view-label">Xem dạng bảng</span>
            </button>
            <button type="button" onclick="addPaymentDetail()" class="order-table-form-action btn btn-sm btn-primary rounded-lg flex items-center gap-1">
                <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon> Thêm nội dung
            </button>
        </div>
    </div>
    <div class="overflow-x-auto pb-3">
        <table class="table bordered-table sm-table mb-0 min-w-[900px] border border-neutral-200">
            <thead>
                <tr class="bg-neutral-50 text-center">
                    <th scope="col" style="width: 50px; min-width: 50px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Stt</th>
                    <th scope="col" style="white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên nội dung <span class="text-danger-500">*</span></th>
                    <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn vị</th>
                    <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng</th>
                    <th scope="col" style="width: 150px; min-width: 150px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá <span class="text-danger-500">*</span></th>
                    <th scope="col" style="width: 150px; min-width: 150px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Đơn giá chỉ gỗ</th>
                    <th scope="col" style="width: 160px; min-width: 160px; white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Thành tiền</th>
                    <th scope="col" style="width: 50px; min-width: 50px; white-space: nowrap; position: sticky; right: 0; z-index: 2; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase bg-neutral-50">Xóa</th>
                </tr>
            </thead>
            <tbody id="payment-details-container">
                @if(isset($acrylicOrder) && $acrylicOrder->type == 'min_late' && isset($acrylicOrder->paymentDetails) && $acrylicOrder->paymentDetails->count() > 0)
                    @foreach($acrylicOrder->paymentDetails as $detailIndex => $detail)
                    <tr class="payment-detail-row">
                        <td style="width: 50px; min-width: 50px; " class="text-center align-middle border border-neutral-200">
                            <span class="detail-index font-semibold text-neutral-500">{{ $detailIndex + 1 }}</span>
                        </td>
                        <td class="border border-neutral-200">
                            <input type="text" name="payment_details[{{ $detailIndex }}][name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Nhập tên chi phí/dịch vụ" required value="{{ $detail->name }}">
                        </td>
                        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                            <input type="text" name="payment_details[{{ $detailIndex }}][unit]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="m, tấm..." value="{{ $detail->unit }}">
                        </td>
                        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
                            <input type="number" name="payment_details[{{ $detailIndex }}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="1" step="0.01" value="{{ $detail->quantity }}">
                        </td>
                        <td style="width: 150px; min-width: 150px; " class="border border-neutral-200">
                            <input type="number" name="payment_details[{{ $detailIndex }}][price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="0" min="0" required value="{{ $detail->price ? round($detail->price) : '0' }}">
                        </td>
                        <td style="width: 150px; min-width: 150px; " class="border border-neutral-200">
                            <input type="number" name="payment_details[{{ $detailIndex }}][price_only]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="0" min="0" value="{{ $detail->price_only ? round($detail->price_only) : '0' }}">
                        </td>
                        <td style="width: 160px; min-width: 160px; " class="border border-neutral-200">
                            <input type="number" name="payment_details[{{ $detailIndex }}][total]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center h-8 text-xs" placeholder="0" readonly value="{{ $detail->total ? round($detail->total) : '0' }}">
                        </td>
                        <td style="width: 50px; min-width: 50px; position: sticky; right: 0; z-index: 1; background-color: #fff; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="text-center align-middle border border-neutral-200">
                            <button type="button" onclick="removePaymentDetail(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa nội dung này">
                                <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                            </button>
                            <input type="hidden" name="payment_details[{{ $detailIndex }}][id]" value="{{ $detail->id }}">
                        </td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
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
                <div class="p-1.5 bg-primary-50 rounded-lg text-primary-500 flex items-center justify-center">
                    <iconify-icon icon="lucide:clipboard-list" class="text-base"></iconify-icon>
                </div>
                <input type="text" name="supplies[${minLateSupplyIndex}][order_supply_code]" class="order-supply-code-input form-control form-control-sm rounded-lg w-40 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Mã vật tư">
                <input type="text" name="supplies[${minLateSupplyIndex}][supply_name]" class="form-control form-control-sm rounded-lg w-64 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="Tên vật tư (ví dụ: Acrylic, Melamine...)">
                <input type="number" name="supplies[${minLateSupplyIndex}][quantity]" class="form-control form-control-sm rounded-lg w-24 border-neutral-300 focus:border-primary-500 focus:ring-primary-500" placeholder="SL" min="0" step="0.01" value="0">
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="addMinLateOrderItem(this)" class="order-table-form-action btn btn-sm btn-outline-primary rounded-lg flex items-center gap-1">
                    <iconify-icon icon="lucide:plus" class="text-sm"></iconify-icon> Thêm sản phẩm
                </button>
                <button type="button" onclick="this.closest('.order-supply-row').remove()" class="order-table-form-action text-neutral-400 hover:text-danger-500 transition-colors p-1 flex items-center justify-center" title="Xóa vật tư này">
                    <iconify-icon icon="lucide:trash-2" class="text-lg"></iconify-icon>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto pb-3">
            <table class="table bordered-table sm-table mb-0 min-w-[2000px] border border-neutral-200">
                <thead>
                    <tr class="bg-neutral-50 text-center">
                        <th scope="col" rowspan="2" style="width: 45px; min-width: 45px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Stt</th>
                        <th scope="col" rowspan="2" style="width: 160px; min-width: 160px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Mã hàng</th>
                        <th scope="col" rowspan="2" style="min-width: 180px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Tên hàng hóa, dịch vụ <span class="text-danger-500">*</span></th>
                        <th scope="col" colspan="2" style="white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Kích thước (mm)</th>
                        <th scope="col" rowspan="2" style="width: 70px; min-width: 70px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số lượng</th>
                        <th scope="col" colspan="4" style="white-space: nowrap;" class="border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Dán cạnh</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán thẳng</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán vát</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán vát mòi</th>
                        <th scope="col" rowspan="2" style="width: 105px; min-width: 105px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 40-59mm</th>
                        <th scope="col" rowspan="2" style="width: 105px; min-width: 105px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 17-39mm</th>
                        <th scope="col" rowspan="2" style="width: 105px; min-width: 105px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số mét dán bản rộng 25-35mm</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số vát tay nắm âm</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Số tấm CNC</th>
                        <th scope="col" rowspan="2" style="width: 100px; min-width: 100px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Chiều vân</th>
                        <th scope="col" rowspan="2" style="min-width: 140px; white-space: nowrap;" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase">Ghi chú</th>
                        <th scope="col" rowspan="2" style="width: 80px; min-width: 80px; white-space: nowrap; position: sticky; right: 0; z-index: 3; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="align-middle border border-neutral-200 font-bold text-xs text-neutral-600 uppercase bg-neutral-50">Hành động</th>
                    </tr>
                    <tr class="bg-neutral-50 text-center">
                        {{-- Kích thước --}}
                        <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="border border-neutral-200 bg-yellow-100/70 font-semibold text-xs text-neutral-700 uppercase">Cao (vân)</th>
                        <th scope="col" style="width: 200px; min-width: 200px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
                        {{-- Dán cạnh --}}
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Cao</th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Cao</th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
                        <th scope="col" style="width: 100px; min-width: 100px; white-space: nowrap;" class="border border-neutral-200 font-semibold text-xs text-neutral-700 uppercase">Rộng</th>
                    </tr>
                </thead>
                <tbody class="supply-items-container" data-supply-index="${minLateSupplyIndex}">
                </tbody>
            </table>
        </div>
    `;
    container.appendChild(newSupply);

    // Add one product row by default
    const addProductBtn = newSupply.querySelector('button[onclick^="addMinLateOrderItem"]');
    if (addProductBtn) {
        addMinLateOrderItem(addProductBtn);
    }

    ensureMinLateFormLayout(newSupply);
    minLateSupplyIndex++;
}

function addMinLateOrderItem(button) {
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
            edge_gluing_height_1: lastRow.querySelector('select[name*="[edge_gluing][height_1]"]') ? lastRow.querySelector('select[name*="[edge_gluing][height_1]"]').value : '',
            edge_gluing_height_2: lastRow.querySelector('select[name*="[edge_gluing][height_2]"]') ? lastRow.querySelector('select[name*="[edge_gluing][height_2]"]').value : '',
            edge_gluing_width_1: lastRow.querySelector('select[name*="[edge_gluing][width_1]"]') ? lastRow.querySelector('select[name*="[edge_gluing][width_1]"]').value : '',
            edge_gluing_width_2: lastRow.querySelector('select[name*="[edge_gluing][width_2]"]') ? lastRow.querySelector('select[name*="[edge_gluing][width_2]"]').value : '',
            straight_paste_length: lastRow.querySelector('input[name*="[straight_paste_length]"]') ? lastRow.querySelector('input[name*="[straight_paste_length]"]').value : '0',
            beveled_length: lastRow.querySelector('input[name*="[beveled_length]"]') ? lastRow.querySelector('input[name*="[beveled_length]"]').value : '0',
            vat_moi_length: lastRow.querySelector('input[name*="[vat_moi_length]"]') ? lastRow.querySelector('input[name*="[vat_moi_length]"]').value : '0',
            ban_rong_40_59: lastRow.querySelector('input[name*="[ban_rong_40_59]"]') ? lastRow.querySelector('input[name*="[ban_rong_40_59]"]').value : '0',
            ban_rong_17_39: lastRow.querySelector('input[name*="[ban_rong_17_39]"]') ? lastRow.querySelector('input[name*="[ban_rong_17_39]"]').value : '0',
            ban_rong_25_35: lastRow.querySelector('input[name*="[ban_rong_25_35]"]') ? lastRow.querySelector('input[name*="[ban_rong_25_35]"]').value : '0',
            beveled_handle: lastRow.querySelector('input[name*="[beveled_handle]"]') ? lastRow.querySelector('input[name*="[beveled_handle]"]').value : '0',
            cnc: lastRow.querySelector('input[name*="[cnc]"]') ? lastRow.querySelector('input[name*="[cnc]"]').value : '0',
            direction: lastRow.querySelector('input[name*="[direction]"]') ? lastRow.querySelector('input[name*="[direction]"]').value : '',
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
        <td style="min-width: 180px;" class="border border-neutral-200">
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
        {{-- Dán cạnh (4 select options) --}}
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <select name="supplies[${supplyIndex}][items][${itemIndex}][edge_gluing][height_1]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-0.5 h-8 text-xs hide-arrow">
                <option value=""></option>
                <option value="V" \${lastData && lastData.edge_gluing_height_1 === 'V' ? 'selected' : ''}>V</option>
                <option value="T" \${lastData && lastData.edge_gluing_height_1 === 'T' ? 'selected' : ''}>T</option>
                <option value="VAT MOI" \${lastData && lastData.edge_gluing_height_1 === 'VAT MOI' ? 'selected' : ''}>VAT MOI</option>
                <option value="DS" \${lastData && lastData.edge_gluing_height_1 === 'DS' ? 'selected' : ''}>DS</option>
                <option value="VAT TNA" \${lastData && lastData.edge_gluing_height_1 === 'VAT TNA' ? 'selected' : ''}>VAT TNA</option>
                <option value="XEM BAN VE CT" \${lastData && lastData.edge_gluing_height_1 === 'XEM BAN VE CT' ? 'selected' : ''}>XEM BAN VE CT</option>
            </select>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <select name="supplies[${supplyIndex}][items][${itemIndex}][edge_gluing][height_2]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-0.5 h-8 text-xs hide-arrow">
                <option value=""></option>
                <option value="V" \${lastData && lastData.edge_gluing_height_2 === 'V' ? 'selected' : ''}>V</option>
                <option value="T" \${lastData && lastData.edge_gluing_height_2 === 'T' ? 'selected' : ''}>T</option>
                <option value="VAT MOI" \${lastData && lastData.edge_gluing_height_2 === 'VAT MOI' ? 'selected' : ''}>VAT MOI</option>
                <option value="DS" \${lastData && lastData.edge_gluing_height_2 === 'DS' ? 'selected' : ''}>DS</option>
                <option value="VAT TNA" \${lastData && lastData.edge_gluing_height_2 === 'VAT TNA' ? 'selected' : ''}>VAT TNA</option>
                <option value="XEM BAN VE CT" \${lastData && lastData.edge_gluing_height_2 === 'XEM BAN VE CT' ? 'selected' : ''}>XEM BAN VE CT</option>
            </select>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <select name="supplies[${supplyIndex}][items][${itemIndex}][edge_gluing][width_1]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-0.5 h-8 text-xs hide-arrow">
                <option value=""></option>
                <option value="V" \${lastData && lastData.edge_gluing_width_1 === 'V' ? 'selected' : ''}>V</option>
                <option value="T" \${lastData && lastData.edge_gluing_width_1 === 'T' ? 'selected' : ''}>T</option>
                <option value="VAT MOI" \${lastData && lastData.edge_gluing_width_1 === 'VAT MOI' ? 'selected' : ''}>VAT MOI</option>
                <option value="DS" \${lastData && lastData.edge_gluing_width_1 === 'DS' ? 'selected' : ''}>DS</option>
                <option value="VAT TNA" \${lastData && lastData.edge_gluing_width_1 === 'VAT TNA' ? 'selected' : ''}>VAT TNA</option>
                <option value="XEM BAN VE CT" \${lastData && lastData.edge_gluing_width_1 === 'XEM BAN VE CT' ? 'selected' : ''}>XEM BAN VE CT</option>
            </select>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <select name="supplies[${supplyIndex}][items][${itemIndex}][edge_gluing][width_2]" class="form-select form-select-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center w-full px-1 py-0.5 h-8 text-xs hide-arrow">
                <option value=""></option>
                <option value="V" \${lastData && lastData.edge_gluing_width_2 === 'V' ? 'selected' : ''}>V</option>
                <option value="T" \${lastData && lastData.edge_gluing_width_2 === 'T' ? 'selected' : ''}>T</option>
                <option value="VAT MOI" \${lastData && lastData.edge_gluing_width_2 === 'VAT MOI' ? 'selected' : ''}>VAT MOI</option>
                <option value="DS" \${lastData && lastData.edge_gluing_width_2 === 'DS' ? 'selected' : ''}>DS</option>
                <option value="VAT TNA" \${lastData && lastData.edge_gluing_width_2 === 'VAT TNA' ? 'selected' : ''}>VAT TNA</option>
                <option value="XEM BAN VE CT" \${lastData && lastData.edge_gluing_width_2 === 'XEM BAN VE CT' ? 'selected' : ''}>XEM BAN VE CT</option>
            </select>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][straight_paste_length]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.straight_paste_length : '0'}" readonly>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][beveled_length]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.beveled_length : '0'}" readonly>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][vat_moi_length]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.vat_moi_length : '0'}" readonly>
        </td>
        <td style="width: 105px; min-width: 105px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][ban_rong_40_59]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.ban_rong_40_59 : '0'}" readonly>
        </td>
        <td style="width: 105px; min-width: 105px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][ban_rong_17_39]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.ban_rong_17_39 : '0'}" readonly>
        </td>
        <td style="width: 105px; min-width: 105px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][ban_rong_25_35]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.ban_rong_25_35 : '0'}" readonly>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][beveled_handle]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="0" step="0.01" value="${lastData ? lastData.beveled_handle : '0'}" readonly>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="supplies[${supplyIndex}][items][${itemIndex}][cnc]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed text-center px-1 py-1 h-8 text-xs" placeholder="0" value="${lastData ? lastData.cnc : '0'}" readonly>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][direction]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 px-1 py-1 h-8 text-xs" placeholder="Chiều vân" value="${lastData ? lastData.direction : ''}">
        </td>
        <td style="min-width: 140px;" class="border border-neutral-200">
            <input type="text" name="supplies[${supplyIndex}][items][${itemIndex}][notes]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Ghi chú" value="${lastData ? lastData.notes : ''}">
        </td>
        <td style="width: 80px; min-width: 80px; position: sticky; right: 0; z-index: 1; background-color: #fff; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="text-center align-middle border border-neutral-200">
            <div class="flex items-center gap-1 justify-center">
                <button type="button" onclick="duplicateMinLateRow(this)" class="text-neutral-400 hover:text-primary-500 transition-colors p-1" title="Nhân bản sản phẩm">
                    <iconify-icon icon="lucide:copy" class="text-base"></iconify-icon>
                </button>
                <button type="button" onclick="removeMinLateOrderItem(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa sản phẩm">
                    <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
                </button>
            </div>
        </td>
    `;
    const rowToAppend = container.classList.contains('order-input-rows') ? buildFormRowFromTableRow(newItem, minLatePreviewLabels) : newItem;
    container.appendChild(rowToAppend);
    
    const newRow = container.lastElementChild;
    bindMinLateRowEvents(newRow);
    calculateMinLateRowStats(newRow);
    
    updateOrderSummary();
    updateMinLateRowIndexes();
}

function removeMinLateOrderItem(button) {
    const row = button.closest('.order-item-row');
    row.remove();
    updateOrderSummary();
    updateMinLateRowIndexes();
}

function updateMinLateRowIndexes() {
    const orderCode = document.getElementById('order-code-input')?.value || '';
    let globalPieceIndex = 1;
    let globalItemIndex = 1;

    document.querySelectorAll('#min-late-supplies-container .order-supply-row').forEach((supplyRow, supplyIndex) => {
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

function duplicateMinLateRow(button) {
    const row = button.closest('.order-item-row');

    // Collect input/select/textarea values BEFORE cloning
    const valuesToCopy = [];
    row.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(el => {
        valuesToCopy.push({ name: el.name, value: el.value });
    });
    
    const newRow = row.cloneNode(true);
    
    // Remove database ID so it creates a new entry
    const idInput = newRow.querySelector('input[name*="[id]"]');
    if (idInput) idInput.remove();
    
    // Restore other field values by name
    valuesToCopy.forEach(({ name, value }) => {
        if (!name || name.indexOf('[id]') !== -1) return;
        const el = newRow.querySelector(`[name="${name}"]`);
        if (el) el.value = value;
    });

    // Insert after current row
    row.parentNode.insertBefore(newRow, row.nextSibling);
    
    bindMinLateRowEvents(newRow);
    calculateMinLateRowStats(newRow);
    updateMinLateRowIndexes();
    updateOrderSummary();
}

function calculateMinLateRowStats(row) {
    if (!row) return;
    
    const heightInput = row.querySelector('input[name*="[height]"]');
    const widthInput = row.querySelector('input[name*="[width]"]');
    const quantityInput = row.querySelector('input[name*="[quantity]"]');
    
    if (!heightInput || !widthInput || !quantityInput) return;
    
    const height = parseFloat(heightInput.value) || 0;
    const width = parseFloat(widthInput.value) || 0;
    const quantity = parseFloat(quantityInput.value) || 0;
    
    const height1Select = row.querySelector('select[name*="[edge_gluing][height_1]"]');
    const height2Select = row.querySelector('select[name*="[edge_gluing][height_2]"]');
    const width1Select = row.querySelector('select[name*="[edge_gluing][width_1]"]');
    const width2Select = row.querySelector('select[name*="[edge_gluing][width_2]"]');
    
    const h1 = height1Select ? height1Select.value : '';
    const h2 = height2Select ? height2Select.value : '';
    const w1 = width1Select ? width1Select.value : '';
    const w2 = width2Select ? width2Select.value : '';
    
    // 1. Straight paste length ("T")
    let sumT = 0;
    if (h1 === 'T') sumT += height;
    if (h2 === 'T') sumT += height;
    if (w1 === 'T') sumT += width;
    if (w2 === 'T') sumT += width;
    const straightLength = Math.ceil(((sumT * quantity) / 1000) * 100) / 100;
    
    // 2. Beveled length ("V")
    let sumV = 0;
    if (h1 === 'V') sumV += height;
    if (h2 === 'V') sumV += height;
    if (w1 === 'V') sumV += width;
    if (w2 === 'V') sumV += width;
    const beveledLength = Math.ceil(((sumV * quantity) / 1000) * 100) / 100;
    
    // 3. Vát mòi length ("VAT MOI")
    let sumVatMoi = 0;
    if (h1 === 'VAT MOI') sumVatMoi += height;
    if (h2 === 'VAT MOI') sumVatMoi += height;
    if (w1 === 'VAT MOI') sumVatMoi += width;
    if (w2 === 'VAT MOI') sumVatMoi += width;
    const vatMoiLength = Math.ceil(((sumVatMoi * quantity) / 1000) * 100) / 100;
    
    // 3.5. Dán bản rộng 25-35mm ("DS")
    let sumDS = 0;
    if (h1 === 'DS') sumDS += height;
    if (h2 === 'DS') sumDS += height;
    if (w1 === 'DS') sumDS += width;
    if (w2 === 'DS') sumDS += width;
    const banRong2535Length = Math.ceil(((sumDS * quantity) / 1000) * 100) / 100;
    
    // Set outputs
    const straightInput = row.querySelector('input[name*="[straight_paste_length]"]');
    if (straightInput) {
        straightInput.value = straightLength.toFixed(2);
    }
    
    const beveledInput = row.querySelector('input[name*="[beveled_length]"]');
    if (beveledInput) {
        beveledInput.value = beveledLength.toFixed(2);
    }
    
    const vatMoiInput = row.querySelector('input[name*="[vat_moi_length]"]');
    if (vatMoiInput) {
        vatMoiInput.value = vatMoiLength.toFixed(2);
    }
    
    const banRong2535Input = row.querySelector('input[name*="[ban_rong_25_35]"]');
    if (banRong2535Input) {
        banRong2535Input.value = banRong2535Length.toFixed(2);
    }
    
    // Dán bản rộng 40-59mm and 17-39mm logic based on dimension checks
    let banRong40_59Length = 0;
    let banRong17_39Length = 0;
    
    const hasDim40_59 = (height >= 40 && height <= 59) || (width >= 40 && width <= 59);
    const hasDimUnder39 = (height > 0 && height <= 39) || (width > 0 && width <= 39);
    
    if (hasDim40_59) {
        banRong40_59Length = straightLength;
    }
    if (hasDimUnder39) {
        banRong17_39Length = straightLength;
    }
    
    const banRong40_59Input = row.querySelector('input[name*="[ban_rong_40_59]"]');
    if (banRong40_59Input) {
        banRong40_59Input.value = banRong40_59Length.toFixed(2);
    }
    
    const banRong17_39Input = row.querySelector('input[name*="[ban_rong_17_39]"]');
    if (banRong17_39Input) {
        banRong17_39Input.value = banRong17_39Length.toFixed(2);
    }
    
    // 4. CNC count: if any of the 4 contains "BAN VE CT" or "XEM BAN VE CT"
    const hasBanVe = [h1, h2, w1, w2].some(val => val === 'XEM BAN VE CT' || val === 'BAN VE CT');
    const cncInput = row.querySelector('input[name*="[cnc]"]');
    if (cncInput) {
        cncInput.value = hasBanVe ? quantity : 0;
    }
    
    // 5. Số vát tay nắm âm: if any of the 4 contains "VAT TNA"
    const hasVatTna = [h1, h2, w1, w2].some(val => val === 'VAT TNA');
    const beveledHandleInput = row.querySelector('input[name*="[beveled_handle]"]');
    if (beveledHandleInput) {
        beveledHandleInput.value = hasVatTna ? quantity : 0;
    }
}

function bindMinLateRowEvents(row) {
    if (!row) return;
    
    const inputs = row.querySelectorAll('input[name*="[height]"], input[name*="[width]"], input[name*="[quantity]"]');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            calculateMinLateRowStats(row);
            if (typeof calculateMinLateTotalPrice === 'function') calculateMinLateTotalPrice(row);
            if (input.name.indexOf('[quantity]') !== -1) {
                updateMinLateRowIndexes();
            }
        });
    });
    
    const selects = row.querySelectorAll('select[name*="[edge_gluing]"]');
    selects.forEach(select => {
        select.addEventListener('change', () => {
            calculateMinLateRowStats(row);
        });
    });
}

function calculateMinLateTotalPrice(row) {
    const unitPriceEl = row.querySelector('input[name*="[unit_price]"]');
    const totalPriceEl = row.querySelector('input[name*="[total_price]"]');
    if (!unitPriceEl || !totalPriceEl) return;
    const unitPrice = parseFloat(unitPriceEl.value) || 0;
    const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
    const totalPrice = unitPrice * quantity;
    totalPriceEl.value = Math.round(totalPrice);
    updateOrderSummary();
}

const minLatePreviewLabels = [
    'STT',
    'Mã hàng',
    'Tên hàng hóa, dịch vụ',
    'Cao',
    'Rộng',
    'Số lượng',
    'Dán cạnh cao 1',
    'Dán cạnh cao 2',
    'Dán cạnh rộng 1',
    'Dán cạnh rộng 2',
    'Mét dán thẳng',
    'Mét dán vát',
    'Mét vát mòi',
    'Bản rộng 40-59',
    'Bản rộng 17-39',
    'Bản rộng 25-35',
    'Vát tay nắm âm',
    'Tấm CNC',
    'Chiều vân',
    'Ghi chú',
    'Hành động',
];

const paymentPreviewLabels = [
    'STT',
    'Tên chi phí/dịch vụ',
    'Đơn vị',
    'Số lượng',
    'Đơn giá',
    'Giá riêng',
    'Thành tiền',
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

function getMinLateFormLabel(index, isPaymentRow) {
    if (isPaymentRow) {
        switch (index) {
            case 0: return 'STT';
            case 1: return 'Tên chi phí/dịch vụ';
            case 2: return 'Đơn vị';
            case 3: return 'Số lượng';
            case 4: return 'Đơn giá';
            case 5: return 'Giá riêng';
            case 6: return 'Thành tiền';
            case 7: return 'Hành động';
            default: return 'Thông tin';
        }
    }

    switch (index) {
        case 0: return 'STT';
        case 1: return 'Mã hàng';
        case 2: return 'Tên hàng hóa, dịch vụ';
        case 3: return 'Cao (vân)';
        case 4: return 'Rộng';
        case 5: return 'Số lượng';
        case 6: return 'Cao';
        case 7: return 'Cao';
        case 8: return 'Rộng';
        case 9: return 'Rộng';
        case 10: return 'Mét dán thẳng';
        case 11: return 'Mét dán vát';
        case 12: return 'Mét vát mòi';
        case 13: return 'Bản rộng 40-59mm';
        case 14: return 'Bản rộng 17-39mm';
        case 15: return 'Bản rộng 25-35mm';
        case 16: return 'Vát tay nắm âm';
        case 17: return 'Tấm CNC';
        case 18: return 'Chiều vân';
        case 19: return 'Ghi chú';
        case 20: return 'Hành động';
        default: return 'Thông tin';
    }
}

function updateMinLateFormSectionClass(section) {
    section.classList.remove('order-form-section--1', 'order-form-section--2', 'order-form-section--3', 'order-form-section--4');
    section.classList.add(`order-form-section--${section.children.length}`);
}

function getOrCreateMinLateFormSection(formRow) {
    let section = formRow._orderFormCurrentSection;
    if (!section || section.children.length >= 4 || formRow.lastElementChild !== section) {
        section = document.createElement('div');
        section.className = 'order-form-section';
        formRow.appendChild(section);
        formRow._orderFormCurrentSection = section;
    }

    return section;
}

function appendMinLateFieldToSection(formRow, fieldWrap) {
    const section = getOrCreateMinLateFormSection(formRow);
    section.appendChild(fieldWrap);
    updateMinLateFormSectionClass(section);
}

function appendMinLateFormField(formRow, fieldWrap, index, isPaymentRow) {
    if (isPaymentRow) {
        appendMinLateFieldToSection(formRow, fieldWrap);
        return;
    }

    appendMinLateFieldToSection(formRow, fieldWrap);
}

function buildFormRowFromTableRow(sourceRow, labels) {
    if (sourceRow.classList.contains('order-form-row')) {
        return sourceRow;
    }

    const isPaymentRow = sourceRow.classList.contains('payment-detail-row');
    const formRow = document.createElement('div');
    formRow.className = `${isPaymentRow ? 'payment-detail-row' : 'order-item-row'} order-form-row`;

    if (sourceRow.dataset.itemId) {
        formRow.dataset.itemId = sourceRow.dataset.itemId;
    }

    const header = document.createElement('div');
    header.className = 'order-form-row__header';
    const badge = document.createElement('div');
    badge.className = 'order-form-row__badge';
    const actions = document.createElement('div');
    actions.className = 'order-form-row__actions';
    actions.appendChild(buildMinLateFormQuickActions(isPaymentRow));
    header.appendChild(badge);
    header.appendChild(actions);
    formRow.appendChild(header);

    const cells = Array.from(sourceRow.querySelectorAll('td'));
    cells.forEach((cell, index) => {
        if (index === 0) {
            badge.appendChild(document.createTextNode(isPaymentRow ? 'Chi phí ' : 'Sản phẩm '));
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
        fieldWrap.dataset.previewLabel = getMinLateFormLabel(index, isPaymentRow);

        const label = document.createElement('label');
        label.className = 'order-form-field__label';
        label.textContent = getMinLateFormLabel(index, isPaymentRow);
        fieldWrap.appendChild(label);

        while (cell.firstChild) {
            fieldWrap.appendChild(cell.firstChild);
        }

        appendMinLateFormField(formRow, fieldWrap, index, isPaymentRow);
    });

    return formRow;
}

function buildMinLateFormQuickActions(isPaymentRow) {
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
    addButton.className = `${isPaymentRow ? 'js-payment-add-detail' : 'js-order-add-item'} text-neutral-400 hover:text-primary-500 transition-colors p-1`;
    addButton.title = isPaymentRow ? 'Thêm nội dung' : 'Thêm sản phẩm';

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
        addMinLateOrderItem(addButton);
        return;
    }

    const paymentButton = e.target.closest('.js-payment-add-detail');
    if (paymentButton) {
        e.preventDefault();
        addPaymentDetail();
    }
});

function ensureMinLateFormLayout(supplyRow) {
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
        formContainer.appendChild(buildFormRowFromTableRow(row, minLatePreviewLabels));
    });

    currentContainer.classList.remove('supply-items-container');
    currentContainer.dataset.sourceTableBody = 'true';
    currentContainer.parentElement.insertAdjacentElement('afterend', formContainer);

    return formContainer;
}

function ensurePaymentFormLayout(card) {
    const inputWrap = card.querySelector('.overflow-x-auto:not(.order-preview-table-wrap), .order-input-layout');
    if (!inputWrap) return null;

    inputWrap.classList.add('order-input-layout');

    const currentContainer = inputWrap.querySelector('#payment-details-container, .payment-detail-rows');
    if (!currentContainer) return null;

    if (currentContainer.classList.contains('payment-detail-rows')) {
        return currentContainer;
    }

    const formContainer = document.createElement('div');
    formContainer.id = 'payment-details-container';
    formContainer.className = 'payment-detail-rows';

    currentContainer.querySelectorAll('.payment-detail-row').forEach(row => {
        formContainer.appendChild(buildFormRowFromTableRow(row, paymentPreviewLabels));
    });

    currentContainer.removeAttribute('id');
    currentContainer.dataset.sourceTableBody = 'true';
    currentContainer.parentElement.insertAdjacentElement('afterend', formContainer);

    return formContainer;
}

function ensureAllMinLateFormLayouts() {
    document.querySelectorAll('#min-late-supplies-container .order-supply-row').forEach(supplyRow => {
        ensureMinLateFormLayout(supplyRow);
    });

    document.querySelectorAll('.payment-entry-card').forEach(card => {
        ensurePaymentFormLayout(card);
    });
}

function refreshEntryPreviewTable(inputWrap, rowSelector, labels, formContainer = null) {
    inputWrap.classList.add('order-input-layout');

    let previewWrap = inputWrap.parentElement.querySelector('.order-preview-table-wrap');
    if (!previewWrap) {
        previewWrap = document.createElement('div');
        previewWrap.className = 'order-preview-table-wrap overflow-x-auto pb-3';
        inputWrap.insertAdjacentElement('afterend', previewWrap);
    }

    const sourceTable = inputWrap.querySelector('table');
    const sourceHead = sourceTable?.querySelector('thead');
    const sourceRows = formContainer ? formContainer.querySelectorAll(rowSelector) : inputWrap.querySelectorAll(rowSelector);
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
        indexCell.textContent = row.querySelector('.row-index, .detail-index')?.textContent?.trim() || '-';
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
    const card = button.closest('.order-entry-card, .payment-entry-card');
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

function refreshOrderPreviewTables(card) {
    if (card.classList.contains('order-entry-card')) {
        card.querySelectorAll('.order-supply-row').forEach(supplyRow => {
            const inputWrap = supplyRow.querySelector('.order-input-layout, .overflow-x-auto:not(.order-preview-table-wrap)');
            if (inputWrap) {
                const formContainer = ensureMinLateFormLayout(supplyRow);
                refreshEntryPreviewTable(inputWrap, '.order-item-row', minLatePreviewLabels, formContainer);
            }
        });
        return;
    }

    const inputWrap = card.querySelector('.order-input-layout, .overflow-x-auto:not(.order-preview-table-wrap)');
    if (inputWrap) {
        const formContainer = ensurePaymentFormLayout(card);
        refreshEntryPreviewTable(inputWrap, '.payment-detail-row', paymentPreviewLabels, formContainer);
    }
}

function toggleOrderTableView(button) {
    const card = button.closest('.order-entry-card, .payment-entry-card');
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

function fillMinLateProductInfo(selectElement, supplyIndex, itemIndex) {
    const productName = selectElement.value;
    const row = selectElement.closest('.order-item-row');
    
    @if(auth()->check())
    const supplies = @json(\App\Models\Supply::all());
    const supply = supplies.find(s => s.name === productName);
    if (supply) {
        const unitPriceInput = row.querySelector(`input[name="supplies[${supplyIndex}][items][${itemIndex}][unit_price]"]`);
        if (unitPriceInput) {
            unitPriceInput.value = supply.unit_price ? Math.round(supply.unit_price) : 0;
            calculateMinLateTotalPrice(row);
        }
    }
    @endif
    updateOrderSummary();
}

// Initial setup for Min Late-specific rows if DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('min-late-supplies-container');
    if (container && container.querySelectorAll('.order-supply-row').length === 0) {
        addMinLateOrderSupply();
    }

    ensureAllMinLateFormLayouts();

    document.querySelectorAll('.order-item-row').forEach(row => {
        bindMinLateRowEvents(row);
        calculateMinLateRowStats(row);
    });

    // Recalculate codes on load
    updateMinLateRowIndexes();

    const orderCodeInput = document.getElementById('order-code-input');
    if (orderCodeInput) {
        orderCodeInput.addEventListener('input', () => updateMinLateRowIndexes());
    }
});

// Live listener for supply code input changes
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('order-supply-code-input')) {
        updateMinLateRowIndexes();
    }
});

let paymentDetailIndex = {{ (isset($acrylicOrder) && $acrylicOrder->type == 'min_late' && isset($acrylicOrder->paymentDetails)) ? $acrylicOrder->paymentDetails->count() : 0 }};

function addPaymentDetail() {
    const container = document.getElementById('payment-details-container');
    if (!container) return;
    
    const newRow = document.createElement('tr');
    newRow.className = 'payment-detail-row';
    newRow.innerHTML = `
        <td style="width: 50px; min-width: 50px; " class="text-center align-middle border border-neutral-200">
            <span class="detail-index font-semibold text-neutral-500">${paymentDetailIndex + 1}</span>
        </td>
        <td class="border border-neutral-200">
            <input type="text" name="payment_details[${paymentDetailIndex}][name]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 h-8 text-xs" placeholder="Nhập tên chi phí/dịch vụ" required>
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="text" name="payment_details[${paymentDetailIndex}][unit]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="m, tấm...">
        </td>
        <td style="width: 100px; min-width: 100px; " class="border border-neutral-200">
            <input type="number" name="payment_details[${paymentDetailIndex}][quantity]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="1" step="0.01" value="1">
        </td>
        <td style="width: 150px; min-width: 150px; " class="border border-neutral-200">
            <input type="number" name="payment_details[${paymentDetailIndex}][price]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="0" min="0" required value="0">
        </td>
        <td style="width: 150px; min-width: 150px; " class="border border-neutral-200">
            <input type="number" name="payment_details[${paymentDetailIndex}][price_only]" class="form-control form-control-sm rounded-lg border-neutral-300 focus:border-primary-500 focus:ring-primary-500 text-center h-8 text-xs" placeholder="0" min="0" value="0">
        </td>
        <td style="width: 160px; min-width: 160px; " class="border border-neutral-200">
            <input type="number" name="payment_details[${paymentDetailIndex}][total]" class="form-control form-control-sm rounded-lg bg-neutral-50 border-neutral-200 cursor-not-allowed font-semibold text-neutral-700 text-center h-8 text-xs" placeholder="0" readonly value="0">
        </td>
        <td style="width: 50px; min-width: 50px; position: sticky; right: 0; z-index: 1; background-color: #fff; box-shadow: -2px 0 4px rgba(0,0,0,0.06);" class="text-center align-middle border border-neutral-200">
            <button type="button" onclick="removePaymentDetail(this)" class="text-neutral-400 hover:text-danger-500 transition-colors p-1" title="Xóa nội dung này">
                <iconify-icon icon="lucide:trash-2" class="text-base"></iconify-icon>
            </button>
        </td>
    `;
    const rowToAppend = container.classList.contains('payment-detail-rows') ? buildFormRowFromTableRow(newRow, paymentPreviewLabels) : newRow;
    container.appendChild(rowToAppend);
    
    // Bind change/input events for auto-calculating row total
    bindPaymentDetailEvents(rowToAppend);
    
    paymentDetailIndex++;
    updatePaymentDetailIndexes();
    updateOrderSummary();
}

function removePaymentDetail(button) {
    const row = button.closest('.payment-detail-row');
    row.remove();
    updatePaymentDetailIndexes();
    updateOrderSummary();
}

function calculatePaymentDetailRowTotal(row) {
    const qtyInput = row.querySelector('input[name*="[quantity]"]');
    const priceInput = row.querySelector('input[name*="[price]"]');
    const totalInput = row.querySelector('input[name*="[total]"]');
    
    if (!qtyInput || !priceInput || !totalInput) return;
    
    const quantity = parseFloat(qtyInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    const total = quantity * price;
    
    totalInput.value = Math.round(total);
    updateOrderSummary();
}

function updatePaymentDetailIndexes() {
    const rows = document.querySelectorAll('.payment-detail-row');
    rows.forEach((row, index) => {
        const indexSpan = row.querySelector('.detail-index');
        if (indexSpan) {
            indexSpan.textContent = index + 1;
        }
    });
}

function bindPaymentDetailEvents(row) {
    const inputs = row.querySelectorAll('input[name*="[quantity]"], input[name*="[price]"]');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            calculatePaymentDetailRowTotal(row);
        });
    });
}

// Initial setup for Min Late-specific rows and payment details
document.addEventListener('DOMContentLoaded', function() {
    ensureAllMinLateFormLayouts();

    document.querySelectorAll('.order-item-row').forEach(row => {
        bindMinLateRowEvents(row);
        calculateMinLateRowStats(row);
    });

    // Bind events for existing payment details
    document.querySelectorAll('.payment-detail-row').forEach(row => {
        bindPaymentDetailEvents(row);
        calculatePaymentDetailRowTotal(row);
    });

    // Ensure at least one payment detail row exists on page load if none loaded
    const container = document.getElementById('payment-details-container');
    if (container && container.querySelectorAll('.payment-detail-row').length === 0) {
        addPaymentDetail();
    }
});
</script>
