@extends('layout.layout')

@php
    $title = 'Giao hàng';
    $subTitle = 'Quy trình';
    $currentRangeLabel = [
        'all' => 'Tất cả',
        'today' => 'Hôm nay',
        'week' => '7 ngày',
    ][$range ?? 'all'] ?? 'Tất cả';
@endphp

@php
    $initialProductCode = trim(old('product_code', ''));
@endphp

@section('content')
    <style>
        /* Tinh chỉnh riêng cho các khối nhập liệu và thống kê của trang giao hàng. */
        .delivery-page__scan-input,
        .delivery-page__history-search {
            border: 1px solid #e5e7eb !important;
            background: #f8fafc !important;
            color: #111827 !important;
            box-shadow: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        .delivery-page__scan-input-group,
        .delivery-page__history-search-wrap {
            position: relative;
        }

        /* Nút camera được đặt cạnh ô nhập để quét nhanh giống màn đóng gói. */
        .delivery-page__scan-input-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .delivery-page__scan-camera-button {
            width: 52px;
            height: 52px;
            flex-shrink: 0;
            border-radius: 0.75rem;
            background: #059669; /* Đổi sang màu emerald-600 làm chủ đạo */
            color: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
            transition: background-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        .delivery-page__scan-camera-button:hover {
            background: #047857; /* Đổi sang màu emerald-700 */
            box-shadow: 0 4px 10px rgba(5, 150, 105, 0.18);
            transform: translateY(-1px);
        }

        .delivery-page__scan-camera-button:active {
            transform: translateY(0);
        }

        .delivery-page__input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            display: flex;
            width: 1.25rem;
            height: 1.25rem;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            pointer-events: none;
            transform: translateY(-50%);
        }

        .delivery-page__input-icon iconify-icon {
            display: block;
            font-size: 1.25rem;
            line-height: 1;
        }

        .delivery-page__scan-input {
            min-height: 52px;
            padding-left: 3rem !important;
            padding-right: 1rem !important;
            font-size: 0.9375rem;
            line-height: 1.5rem;
        }

        .delivery-page__history-search {
            min-height: 44px;
            padding-left: 3rem !important;
            padding-right: 1rem !important;
            line-height: 1.25rem;
        }

        .delivery-page__scan-input::placeholder,
        .delivery-page__history-search::placeholder {
            color: #94a3b8;
        }

        .delivery-page__history-search-wrap {
            width: min(100%, 320px);
            min-width: min(100%, 280px);
        }

        /* Canh ngang phan hien thi cua bang linh kien de khong roi xuong dong. */
        .delivery-page__package-items-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: nowrap;
        }

        .delivery-page__package-items-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: nowrap;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .delivery-page__package-items-per-page-wrap {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: nowrap;
            white-space: nowrap;
        }

        .delivery-page__filter-button {
            min-height: 44px;
            border-color: #e5e7eb !important;
            box-shadow: none !important;
        }

        .delivery-page__filter-menu {
            z-index: 40;
        }

        /* Ô ghi chú vẫn khóa nhập nhưng nhìn như trạng thái bình thường để chữ dễ đọc hơn. */
        .delivery-page__delivery-note-input:disabled {
            opacity: 1 !important;
            color: #111827 !important;
            background-color: #f8fafc !important;
            -webkit-text-fill-color: #111827;
        }

        /* Cột ghi chú cần có bề ngang cố định để tự xuống dòng sau một đoạn nhất định. */
        #historyTable thead th:nth-child(5),
        #historyTable tbody td:nth-child(5) {
            width: 240px;
            max-width: 240px;
        }

        #historyTable tbody td:nth-child(5) {
            white-space: normal;
            word-break: break-word;
            overflow-wrap: anywhere;
            line-height: 1.45;
            vertical-align: top;
        }

        /* Đẩy cụm nút xác nhận sang phải đúng như layout mong muốn. */
        .delivery-page__result-action-buttons {
            width: 100%;
            display: flex;
            justify-content: flex-end;
        }

        .delivery-page__summary-card {
            min-height: 206px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 24px;
            background: linear-gradient(135deg, #047857 0%, #059669 45%, #0d9488 100%) !important; /* Đổi gradient sang tông màu emerald chủ đạo */
            color: #ffffff !important;
            box-shadow: 0 18px 40px rgba(5, 150, 105, 0.24);
        }

        .delivery-page__summary-body {
            min-height: 206px;
        }

        .delivery-page__summary-icon-wrap {
            border: 1px solid rgba(255, 255, 255, 0.24);
            background: rgba(255, 255, 255, 0.16) !important;
            color: #ffffff !important;
        }

        .delivery-page__summary-tag,
        .delivery-page__summary-count,
        .delivery-page__summary-label,
        .delivery-page__summary-icon {
            color: #ffffff !important;
        }

        .delivery-page__summary-tag {
            opacity: 0.78;
        }

        .delivery-page__summary-label {
            opacity: 0.9;
        }

        @media (max-width: 575.98px) {
            .delivery-page__history-search-wrap {
                min-width: 0;
            }
        }

        @media (min-width: 1024px) {
            .delivery-page__history-card-header {
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: space-between !important;
                flex-wrap: nowrap !important;
            }

            .delivery-page__history-title {
                flex: 0 0 auto;
                white-space: nowrap;
            }

            .delivery-page__history-actions {
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: flex-end !important;
                width: auto !important;
                flex: 1 1 auto;
                min-width: 0;
            }

            .delivery-page__history-per-page-wrap {
                flex: 0 0 auto;
                min-width: 0;
            }

            .delivery-page__history-search-wrap {
                flex: 0 1 320px;
                max-width: 320px;
                min-width: 240px;
            }

            .delivery-page__filter-button {
                flex: 0 0 auto;
            }
        }

        /* Định nghĩa lại các class màu Emerald vì hệ thống chưa biên dịch Tailwind cho màu này */
        .bg-emerald-50 {
            background-color: #ecfdf5 !important;
        }
        .bg-emerald-100 {
            background-color: #d1fae5 !important;
        }
        .bg-emerald-600 {
            background-color: #059669 !important;
        }
        .text-emerald-500 {
            color: #10b981 !important;
        }
        .text-emerald-600 {
            color: #059669 !important;
        }
        .text-emerald-700 {
            color: #047857 !important;
        }
        .border-emerald-100 {
            border-color: #d1fae5 !important;
        }
        .border-emerald-200 {
            border-color: #a7f3d0 !important;
        }
        .border-emerald-600 {
            border-color: #059669 !important;
        }

        /* Nút hành động xem chi tiết (Eye icon) trong bảng lịch sử giao hàng */
        .delivery-page__history-cell--action a {
            transition: all 0.2s ease-in-out !important;
        }
        .delivery-page__history-cell--action a:hover {
            background-color: #059669 !important;
            border-color: #059669 !important;
            color: #ffffff !important;
        }

        /* Nút xác nhận kiểm tra và xác nhận giao hàng */
        .delivery-page__scan-submit {
            transition: background-color 0.2s ease-in-out !important;
        }
        .delivery-page__scan-submit:hover:not(:disabled) {
            background-color: #047857 !important;
        }
        
        #resultConfirmButton {
            transition: background-color 0.2s ease-in-out !important;
        }
        #resultConfirmButton:hover:not(:disabled) {
            background-color: #047857 !important;
        }
    </style>

    <div class="-mt-4 mb-6">
        <p class="text-sm text-neutral-500 dark:text-neutral-400">Quét mã kiện để xác nhận giao hàng và theo dõi lịch sử ngay bên dưới.</p>
    </div>
    <div class="delivery-page w-full space-y-6">

        @if(session('success'))
            <div class="alert alert-success bg-success-100 text-success-700 border border-success-200 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger bg-danger-100 text-danger-700 border border-danger-200 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="delivery-page__grid grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="delivery-page__left lg:col-span-4 space-y-6">
                <div class="delivery-page__scan-card card border border-neutral-200 rounded-2xl shadow-sm bg-white overflow-hidden">
                    <div class="delivery-page__scan-body card-body p-6">
                        <div class="delivery-page__scan-heading flex items-start gap-4 mb-6">
                            <span class="delivery-page__scan-icon-wrap w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                <iconify-icon icon="lucide:qr-code" class="delivery-page__scan-icon text-2xl"></iconify-icon>
                            </span>
                            <div class="delivery-page__scan-heading-text min-w-0">
                                <h6 class="delivery-page__scan-title text-lg font-bold text-neutral-900 mb-1">Quét mã kiện</h6>
                                <p class="delivery-page__scan-description text-sm text-secondary-light mb-0">Nhập hoặc quét mã QR để kiểm tra và xác nhận giao hàng.</p>
                            </div>
                        </div>

                        <form id="deliveryScanForm" method="POST" action="{{ route('processes.delivery.check') }}"
                            class="delivery-page__scan-form flex flex-col gap-4">
                            @csrf
                            <div class="delivery-page__scan-input-group delivery-page__scan-input-row">
                                <span class="delivery-page__input-icon delivery-page__scan-input-icon">
                                    <iconify-icon icon="lucide:search"></iconify-icon>
                                </span>
                                <input type="text" id="product_code" name="product_code" value="{{ old('product_code') }}"
                                    class="delivery-page__scan-input form-control flex-1 min-w-0 rounded-xl pl-11 pr-4 py-3 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    placeholder="Nhập hoặc quét mã QR..." autofocus>
                                <button type="button" onclick="startScanning()"
                                    class="delivery-page__scan-camera-button delivery-page__scan-camera-trigger flex items-center justify-center"
                                    title="Quét Camera">
                                    <iconify-icon icon="lucide:camera" class="delivery-page__scan-camera-icon text-xl"></iconify-icon>
                                </button>
                            </div>
                            <input type="hidden" id="deliveryPackageId" name="package_id" value="">

                            @error('product_code')
                                <p class="delivery-page__scan-error text-danger-600 text-sm mb-0">{{ $message }}</p>
                            @enderror

                            <button type="submit" id="deliverySubmitBtn" @disabled($initialProductCode === '')
                                class="delivery-page__scan-submit btn justify-center w-full py-3 rounded-xl font-semibold bg-emerald-600 hover:bg-emerald-700 text-white inline-flex items-center gap-2 {{ $initialProductCode === '' ? 'opacity-50 cursor-not-allowed' : '' }}">
                                <iconify-icon icon="lucide:check-circle-2" class="delivery-page__scan-submit-icon text-lg"></iconify-icon>
                                <span class="delivery-page__scan-submit-label">{{ $initialProductCode === '' ? 'Nhập mã để kiểm tra' : 'Kiểm tra mã' }}</span>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="delivery-page__summary-card rounded-3xl overflow-hidden shadow-lg bg-gradient-to-br from-emerald-700 via-teal-600 to-emerald-700 text-white">
                    <div class="delivery-page__summary-body p-6">
                        <div class="flex items-center justify-between gap-4 mb-6">
                            <span class="delivery-page__summary-icon-wrap w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center">
                                <iconify-icon icon="lucide:navigation" class="delivery-page__summary-icon text-2xl text-white"></iconify-icon>
                            </span>
                            <span class="delivery-page__summary-tag text-xs font-bold uppercase tracking-wide text-white/80">Hôm nay</span>
                        </div>

                        <div id="todayDeliveryCount" class="delivery-page__summary-count text-5xl font-black leading-none mb-3">
                            {{ $todayDeliveryCount }}
                        </div>
                        <p class="delivery-page__summary-label text-white/90 font-medium mb-0">Kiện đã giao hàng</p>
                    </div>
                </div>
            </div>

            <div class="delivery-page__right lg:col-span-8">
                <div class="delivery-page__result-shell flex min-h-[680px] h-full flex-col overflow-hidden rounded-[24px] border border-neutral-200 bg-white shadow-sm">
                    {{-- Layout rỗng khi chưa kiểm tra mã --}}
                    <div id="deliveryEmptyState" class="delivery-page__empty-state flex flex-1 items-center justify-center px-6 py-10">
                        <div class="delivery-page__empty-content flex w-full max-w-3xl flex-col items-center justify-center text-center">
                            <span class="delivery-page__empty-icon-wrap mb-5 flex h-20 w-20 items-center justify-center rounded-full border border-neutral-200 bg-white text-emerald-500 shadow-sm">
                                <iconify-icon icon="lucide:package-search" class="text-4xl"></iconify-icon>
                            </span>
                            <h6 class="delivery-page__empty-title text-2xl font-bold text-neutral-900 mb-3">Chưa có kiện được quét</h6>
                            <p class="delivery-page__empty-description max-w-xl text-sm leading-7 text-secondary-light mb-0">
                                Vui lòng quét mã QR hoặc nhập mã định danh để bắt đầu kiểm tra giao hàng.
                            </p>
                        </div>
                    </div>

                    <div id="deliveryResultPanel" class="hidden flex h-full flex-col">
                        <div class="delivery-page__result-header-wrap border-b border-neutral-100 bg-neutral-50/80 px-6 py-5">
                            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                                <div class="flex items-start gap-4">
                                    <span class="delivery-page__result-icon-wrap w-20 h-20 rounded-2xl bg-white border border-neutral-200 shadow-sm flex items-center justify-center shrink-0">
                                        <iconify-icon icon="lucide:package" class="delivery-page__result-icon text-4xl text-emerald-600"></iconify-icon>
                                    </span>
                                    <div class="delivery-page__result-title-group min-w-0">
                                        <h6 id="resultPackageName" class="delivery-page__result-title text-2xl font-bold text-neutral-900 mb-1">-</h6>
                                        <p id="resultPackageCode" class="delivery-page__result-subtitle text-sm text-secondary-light mb-0">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="delivery-page__result-body flex-1 px-6 py-6">
                            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
                                <div class="delivery-page__order-card rounded-2xl border border-neutral-200 bg-neutral-50 px-5 py-4">
                                    <div class="flex items-center gap-2 mb-4">
                                        <iconify-icon icon="lucide:receipt" class="text-2xl text-emerald-600"></iconify-icon>
                                        <h6 class="text-base font-bold text-neutral-900 mb-0">THÔNG TIN ĐƠN HÀNG</h6>
                                    </div>
                                    <dl id="resultOrderInfoList" class="space-y-3"></dl>
                                </div>

                                <div class="delivery-page__package-card rounded-2xl border border-neutral-200 bg-neutral-50 px-5 py-4">
                                    <div class="flex items-center gap-2 mb-4">
                                        <iconify-icon icon="lucide:package-open" class="text-2xl text-emerald-600"></iconify-icon>
                                        <h6 class="text-base font-bold text-neutral-900 mb-0">THÔNG TIN KIỆN</h6>
                                    </div>
                                    <dl id="resultPackageInfoList" class="space-y-3"></dl>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-neutral-200 bg-white overflow-hidden">
                                <div class="delivery-page__package-items-header border-b border-neutral-200 bg-neutral-50 px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <iconify-icon icon="lucide:list-checks" class="text-2xl text-emerald-600"></iconify-icon>
                                        <h6 class="text-base font-bold text-neutral-900 mb-0">BẢNG THÔNG TIN LINH KIỆN</h6>
                                    </div>

                                    <div class="delivery-page__package-items-actions">
                                        <span class="text-sm font-medium text-secondary-light whitespace-nowrap">Hiển thị</span>
                                        <select id="packageItemsPerPage"
                                            class="form-select form-select-sm w-auto border-neutral-200 rounded-lg py-2 px-3 text-xs"
                                            onchange="changeDeliveryPackageItemsPerPage(this.value)">
                                            @foreach([15, 25, 50, 100] as $option)
                                                <option value="{{ $option }}" {{ (int) 15 === $option ? 'selected' : '' }}>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table basic-border-table mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="border-r border-neutral-200 last:border-r-0">STT</th>
                                                <th scope="col" class="border-r border-neutral-200 last:border-r-0">Mã linh kiện</th>
                                                <th scope="col" class="border-r border-neutral-200 last:border-r-0">Tên linh kiện</th>
                                                <th scope="col" class="border-r border-neutral-200 last:border-r-0">Loại</th>
                                                <th scope="col" class="border-r border-neutral-200 last:border-r-0">Đơn hàng</th>
                                            </tr>
                                        </thead>
                                        <tbody id="resultPackageItemsBody">
                                            <tr id="resultPackageItemsEmptyRow">
                                                <td colspan="6" class="px-6 py-12 text-center text-neutral-400">Chưa có linh kiện được hiển thị.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div id="resultPackageItemsFooter"
                                    class="delivery-page__package-items-footer hidden flex items-center justify-between flex-wrap gap-2 px-5 py-4 border-t border-neutral-100">
                                    <span id="resultPackageItemsSummary" class="text-secondary-light text-sm">
                                        Hiển thị 0 đến 0 trong tổng 0 linh kiện
                                    </span>
                                    <div id="resultPackageItemsPagination" class="delivery-page__package-items-pagination"></div>
                                </div>
                            </div>
                        </div>

                        <div class="delivery-page__result-actions border-t border-neutral-100 px-6 py-5 flex flex-col gap-4">
                            <div class="delivery-page__delivery-note-wrap">
                                <label for="deliveryNote" class="delivery-page__delivery-note-label block text-base font-bold text-neutral-900 mb-2">
                                    Ghi chú giao hàng
                                </label>
                                <textarea id="deliveryNote" name="delivered_note" rows="3" maxlength="1000"
                                    class="delivery-page__delivery-note-input form-control w-full rounded-xl border border-neutral-200 bg-neutral-50 px-4 py-3 text-sm text-neutral-900 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    placeholder="Nhập ghi chú giao hàng nếu có..."></textarea>
                            </div>

                            <div class="delivery-page__result-action-buttons flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                                <button type="button" id="resultResetButton"
                                    class="btn bg-neutral-100 hover:bg-neutral-200 text-neutral-700 px-4 py-3 rounded-xl font-semibold inline-flex items-center justify-center gap-2">
                                    <iconify-icon icon="lucide:rotate-ccw" class="text-base"></iconify-icon>
                                    Làm mới
                                </button>

                                <button type="button" id="resultConfirmButton"
                                    class="btn bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-xl font-semibold inline-flex items-center justify-center gap-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                    <iconify-icon icon="lucide:navigation" class="text-base"></iconify-icon>
                                    Xác nhận giao hàng
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="delivery-page__history-card card w-full lg:col-span-12 border-0 overflow-hidden shadow-sm relative">
                <div class="delivery-page__history-card-header card-header bg-white border-b border-neutral-200 px-6 py-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between lg:flex-nowrap">
                    <h5 class="delivery-page__history-title text-lg font-bold text-neutral-900 flex items-center gap-2 mb-0 shrink-0">
                        <iconify-icon icon="lucide:history" class="text-2xl text-emerald-600"></iconify-icon>
                        Lịch sử giao hàng
                    </h5>

                    <div class="delivery-page__history-actions flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-end lg:flex-1">
                        <div class="delivery-page__history-per-page-wrap flex items-center gap-2">
                            <span class="text-sm font-medium text-secondary-light whitespace-nowrap">Hiển thị</span>
                            <select id="historyPerPage"
                                class="form-select form-select-sm w-auto border-neutral-200 rounded-lg py-2 px-3 text-xs"
                                onchange="changeDeliveryHistoryPerPage(this.value)">
                                @foreach([15, 25, 50, 100] as $option)
                                    <option value="{{ $option }}" {{ (int) ($perPage ?? 15) === $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="delivery-page__history-search-wrap relative w-full sm:w-80">
                            <span class="delivery-page__input-icon delivery-page__history-search-icon">
                                <iconify-icon icon="lucide:search"></iconify-icon>
                            </span>
                            <input type="text" id="historySearch" value="{{ $search ?? '' }}" oninput="filterHistoryTable()"
                                class="delivery-page__history-search form-control rounded-xl bg-neutral-50 border-neutral-200 pl-11 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                placeholder="Tìm lịch sử...">
                        </div>

                        <div class="relative {{ (($range ?? 'all') === 'all' && empty($search)) ? 'hidden' : '' }}" id="clearFilterWrapper">
                            <a href="{{ route('processes.delivery') }}" id="clearFilterBtn"
                                class="btn bg-danger-50 hover:bg-danger-100 text-danger-600 px-4 py-2.5 rounded-xl font-semibold border border-danger-100 inline-flex items-center gap-2">
                                <iconify-icon icon="lucide:filter-x" class="text-lg"></iconify-icon>
                                <span>Bỏ lọc</span>
                            </a>
                        </div>

                        <div class="relative">
                            <button type="button" id="filterToggleBtn"
                                class="delivery-page__filter-button btn bg-white hover:bg-neutral-50 text-neutral-700 px-4 py-2.5 rounded-xl font-semibold border border-neutral-200 inline-flex items-center gap-2">
                                <iconify-icon icon="lucide:filter" class="text-lg"></iconify-icon>
                                <span>Lọc</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="delivery-page__history-card-body card-body w-full">
                    <div class="delivery-page__history-table-wrap table-responsive">
                        <table class="delivery-page__history-table table w-full basic-border-table mb-0" id="historyTable">
                            <thead class="delivery-page__history-thead">
                                <tr class="delivery-page__history-head-row">
                                    <th scope="col" class="border-r border-neutral-200 last:border-r-0">Mã kiện</th>
                                    <th scope="col" class="border-r border-neutral-200 last:border-r-0">Tên kiện</th>
                                    <th scope="col" class="border-r border-neutral-200 last:border-r-0">Đơn hàng</th>
                                    <th scope="col" class="border-r border-neutral-200 last:border-r-0">Trạng thái</th>
                                    <th scope="col" class="border-r border-neutral-200 last:border-r-0">Ghi chú</th>
                                    <th scope="col" class="border-r border-neutral-200 last:border-r-0">Người thao tác</th>
                                    <th scope="col" class="border-r border-neutral-200 last:border-r-0">Thời gian</th>
                                    <th scope="col" class="border-r border-neutral-200 last:border-r-0 text-end">Hành động</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody" class="delivery-page__history-tbody">
                                @forelse($historyRows as $row)
                                    <tr class="delivery-page__history-row" data-history-row="1">
                                        <td class="delivery-page__history-cell delivery-page__history-cell--code border-r border-neutral-200 last:border-r-0">
                                            <span class="inline-flex items-center rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-1.5 font-mono text-xs font-semibold text-neutral-800">
                                                {{ $row['package_code'] ?? $row['product_code'] }}
                                            </span>
                                        </td>
                                        <td class="delivery-page__history-cell delivery-page__history-cell--name border-r border-neutral-200 last:border-r-0 font-semibold text-neutral-900">
                                            {{ $row['package_name'] ?? $row['product_name'] }}
                                        </td>
                                        <td class="delivery-page__history-cell delivery-page__history-cell--order border-r border-neutral-200 last:border-r-0 text-emerald-600 font-semibold">
                                            {{ $row['order_code'] }}
                                        </td>
                                        <td class="delivery-page__history-cell delivery-page__history-cell--status border-r border-neutral-200 last:border-r-0">
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-success-200 bg-success-100 px-2.5 py-1 text-xs font-semibold text-success-700">
                                                <iconify-icon icon="lucide:check-circle-2" class="text-sm"></iconify-icon>
                                                {{ $row['status_label'] }}
                                            </span>
                                        </td>
                                        <td class="delivery-page__history-cell delivery-page__history-cell--notes border-r border-neutral-200 last:border-r-0 text-secondary-light">
                                            {{ $row['notes'] ?? '—' }}
                                        </td>
                                        <td class="delivery-page__history-cell delivery-page__history-cell--operator border-r border-neutral-200 last:border-r-0">
                                            <div class="flex items-center gap-2">
                                                <span class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs">
                                                    {{ mb_substr($row['operator'] ?? 'H', 0, 1) }}
                                                </span>
                                                <span class="font-medium text-secondary-light">{{ $row['operator'] ?? '—' }}</span>
                                            </div>
                                        </td>
                                        <td class="delivery-page__history-cell delivery-page__history-cell--time border-r border-neutral-200 last:border-r-0 text-secondary-light">
                                            {{ $row['time'] }}
                                        </td>
                                        <td class="delivery-page__history-cell delivery-page__history-cell--action border-r border-neutral-200 last:border-r-0 text-end">
                                            <a href="{{ $row['view_url'] }}"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-emerald-600 shadow-sm transition-colors hover:border-emerald-600 hover:bg-emerald-600 hover:text-white">
                                                <iconify-icon icon="lucide:eye" class="text-base"></iconify-icon>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="noHistoryRow" class="delivery-page__history-empty-row">
                                        <td colspan="8" class="px-6 py-14 text-center text-neutral-400">Chưa có lịch sử giao hàng nào.</td>
                                    </tr>
                                @endforelse
                                @if($historyRows->count() > 0)
                                    <tr id="noFilteredHistoryRow" class="hidden delivery-page__history-empty-row">
                                        <td colspan="8" class="px-6 py-14 text-center text-neutral-400">Không tìm thấy lịch sử phù hợp.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    @if($historyRows instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="delivery-page__history-pagination flex items-center justify-between flex-wrap gap-2 py-5 border-t border-neutral-100">
                            <span class="text-secondary-light text-sm">
                                Hiển thị {{ $historyRows->firstItem() ?? 0 }} đến {{ $historyRows->lastItem() ?? 0 }} trong tổng {{ $historyRows->total() }} lịch sử giao hàng
                            </span>
                            {{ $historyRows->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <x-modal name="delivery-filter-modal" maxWidth="md">
        <div class="border-b border-neutral-200 px-6 py-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <iconify-icon icon="lucide:filter" class="text-lg text-emerald-600"></iconify-icon>
                <h5 class="text-base font-bold text-neutral-900 mb-0">Lọc lịch sử giao hàng</h5>
            </div>
            <button type="button" onclick="closeModal('delivery-filter-modal')"
                class="text-secondary-light hover:text-neutral-900 text-2xl leading-none">&times;</button>
        </div>

        <div class="px-6 py-5">
            <p class="text-sm text-secondary-light mb-4">Chọn nhanh khoảng thời gian hiển thị lịch sử.</p>

            <div class="space-y-3">
                <a href="{{ route('processes.delivery', ['range' => 'all']) }}" data-range-link="all"
                    class="delivery-page__filter-option flex items-center justify-between gap-3 rounded-xl border border-neutral-200 bg-white px-4 py-3 text-sm font-semibold text-neutral-700 hover:bg-neutral-50">
                    <span class="flex items-center gap-2">
                        <iconify-icon icon="lucide:list-filter" class="text-base text-emerald-600"></iconify-icon>
                        Tất cả
                    </span>
                    <span class="text-xs font-medium text-secondary-light">{{ $currentRangeLabel }}</span>
                </a>

                <a href="{{ route('processes.delivery', ['range' => 'today']) }}" data-range-link="today"
                    class="delivery-page__filter-option flex items-center justify-between gap-3 rounded-xl border border-neutral-200 bg-white px-4 py-3 text-sm font-semibold text-neutral-700 hover:bg-neutral-50">
                    <span class="flex items-center gap-2">
                        <iconify-icon icon="lucide:calendar-days" class="text-base text-emerald-600"></iconify-icon>
                        Hôm nay
                    </span>
                    <span class="text-xs font-medium text-secondary-light">Hôm nay</span>
                </a>

                <a href="{{ route('processes.delivery', ['range' => 'week']) }}" data-range-link="week"
                    class="delivery-page__filter-option flex items-center justify-between gap-3 rounded-xl border border-neutral-200 bg-white px-4 py-3 text-sm font-semibold text-neutral-700 hover:bg-neutral-50">
                    <span class="flex items-center gap-2">
                        <iconify-icon icon="lucide:calendar-range" class="text-base text-emerald-600"></iconify-icon>
                        7 ngày
                    </span>
                    <span class="text-xs font-medium text-secondary-light">7 ngày</span>
                </a>
            </div>
        </div>

        <div class="border-t border-neutral-200 px-6 py-4 flex justify-end">
            <button type="button" onclick="closeModal('delivery-filter-modal')"
                class="btn bg-neutral-100 hover:bg-neutral-200 text-neutral-700 px-5 py-2.5 rounded-lg font-semibold">
                Đóng
            </button>
        </div>
    </x-modal>

    <div id="scannerModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-neutral-900/80 backdrop-blur-sm px-4">
        <div class="w-full max-w-md overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-neutral-200 bg-neutral-50 px-5 py-4">
                <div class="flex items-center gap-2 font-bold text-neutral-800">
                    <iconify-icon icon="lucide:camera" class="text-lg text-emerald-600"></iconify-icon>
                    Quét mã QR qua camera
                </div>
                <button type="button" onclick="stopScanning()" class="text-neutral-400 hover:text-neutral-700">
                    <iconify-icon icon="lucide:x" class="text-2xl"></iconify-icon>
                </button>
            </div>
            <div class="flex flex-col items-center gap-4 p-6">
                <div id="reader" class="w-full overflow-hidden rounded-xl border border-neutral-200 bg-neutral-100" style="min-height: 260px;"></div>
                <p class="text-center text-xs text-neutral-400">Đưa mã QR vào giữa khung quét để xác nhận giao hàng nhanh.</p>
            </div>
        </div>
    </div>

    <div id="toastContainer" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2"></div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let html5QrCode = null;
        let isSubmittingDelivery = false;
        const deliveryPreviewUrl = @json(route('processes.delivery.check'));
        const deliveryConfirmUrl = @json(route('processes.delivery.confirm'));
        const csrfToken = @json(csrf_token());
        
        // Trạng thái phân trang riêng cho bảng linh kiện trong kiện đang kiểm tra.
        const deliveryPackageItemsState = {
            rows: [],
            perPage: 15,
            page: 1,
            packageId: null,
            meta: null,
            sizeMeta: null,
        };

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            const isSuccess = type === 'success';

            toast.className = `min-w-[280px] max-w-sm rounded-xl border px-4 py-3 shadow-lg ${isSuccess ? 'border-success-200 bg-success-50 text-success-700' : 'border-danger-200 bg-danger-50 text-danger-700'}`;
            toast.innerHTML = `
                <div class="flex items-start gap-3">
                    <iconify-icon icon="${isSuccess ? 'lucide:check-circle-2' : 'lucide:alert-circle'}" class="mt-0.5 text-lg"></iconify-icon>
                    <div class="text-sm font-medium leading-5">${escapeHtml(message)}</div>
                </div>
            `;

            toastContainer.appendChild(toast);

            window.setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                toast.classList.add('transition', 'duration-300');
            }, 2600);

            window.setTimeout(() => {
                toast.remove();
            }, 3200);
        }

        function toggleSubmitButton() {
            const productCodeInput = document.getElementById('product_code');
            const submitButton = document.getElementById('deliverySubmitBtn');
            const submitLabel = document.querySelector('.delivery-page__scan-submit-label');
            const hasValue = productCodeInput.value.trim().length > 0;
            const shouldDisable = !hasValue || isSubmittingDelivery;

            submitButton.disabled = shouldDisable;
            submitButton.classList.toggle('opacity-50', shouldDisable);
            submitButton.classList.toggle('cursor-not-allowed', shouldDisable);

            if (submitLabel) {
                if (isSubmittingDelivery) {
                    submitLabel.textContent = 'Đang kiểm tra...';
                } else if (hasValue) {
                    submitLabel.textContent = 'Kiểm tra mã';
                } else {
                    submitLabel.textContent = 'Nhập mã để kiểm tra';
                }
            }
        }

        function filterHistoryTable() {
            const query = document.getElementById('historySearch').value.toLowerCase().trim();
            const rows = document.querySelectorAll('#historyTableBody tr[data-history-row="1"]');
            let visibleCount = 0;
            const filteredEmptyRow = document.getElementById('noFilteredHistoryRow');

            rows.forEach((row) => {
                const text = row.textContent.toLowerCase();
                const matches = !query || text.includes(query);
                row.classList.toggle('hidden', !matches);
                if (matches) {
                    visibleCount += 1;
                }
            });

            const emptyRow = document.getElementById('noHistoryRow');
            if (emptyRow) {
                emptyRow.classList.toggle('hidden', visibleCount > 0);
            }
            if (filteredEmptyRow) {
                filteredEmptyRow.classList.toggle('hidden', visibleCount > 0);
            }

            // Hiển thị hoặc ẩn nút bỏ lọc
            const hasUrlParams = @json(($range ?? 'all') !== 'all' || !empty($search));
            const clearFilterWrapper = document.getElementById('clearFilterWrapper');
            if (clearFilterWrapper) {
                if (query !== '' || hasUrlParams) {
                    clearFilterWrapper.classList.remove('hidden');
                } else {
                    clearFilterWrapper.classList.add('hidden');
                }
            }
        }

        // Cập nhật số dòng hiển thị cho bảng linh kiện trong kiện đang kiểm tra.
        function changeDeliveryPackageItemsPerPage(value) {
            const perPage = parseInt(value, 10);
            deliveryPackageItemsState.perPage = Number.isFinite(perPage) && perPage > 0 ? perPage : 15;
            deliveryPackageItemsState.page = 1;
            reloadDeliveryPackageItems(1);
        }

        // Chuyển trang cho bảng linh kiện trong kiện đang kiểm tra.
        function changeDeliveryPackageItemsPage(page) {
            const nextPage = parseInt(page, 10);
            if (!Number.isFinite(nextPage)) {
                return;
            }
            reloadDeliveryPackageItems(nextPage);
        }

        // Tạo dãy nút phân trang gọn cho bảng linh kiện.
        function renderDeliveryPackageItemsPagination(currentPage, totalPages) {
            const buttons = [];
            const addButton = (page, label, disabled = false, isActive = false) => {
                const classes = [
                    'inline-flex',
                    'min-w-8',
                    'h-8',
                    'items-center',
                    'justify-center',
                    'rounded-lg',
                    'border',
                    'px-2',
                    'text-sm',
                    'font-semibold',
                    'transition-colors',
                ];

                if (isActive) {
                    classes.push('border-emerald-600', 'bg-emerald-600', 'text-white');
                } else {
                    classes.push('border-neutral-200', 'bg-white', 'text-neutral-700', 'hover:border-emerald-600', 'hover:text-emerald-600');
                }

                if (disabled) {
                    classes.push('pointer-events-none', 'opacity-50');
                }

                buttons.push(`
                    <button type="button"
                        class="${classes.join(' ')}"
                        ${disabled ? 'disabled' : ''}
                        ${!disabled ? `onclick="changeDeliveryPackageItemsPage(${page})"` : ''}>
                        ${escapeHtml(label)}
                    </button>
                `);
            };

            if (totalPages <= 1) {
                return '';
            }

            addButton(currentPage - 1, '<', currentPage === 1);

            const maxVisible = 5;
            let start = Math.max(currentPage - 2, 1);
            let end = Math.min(start + maxVisible - 1, totalPages);
            start = Math.max(end - maxVisible + 1, 1);

            if (start > 1) {
                addButton(1, '1');
                if (start > 2) {
                    buttons.push('<span class="px-1 text-secondary-light">...</span>');
                }
            }

            for (let page = start; page <= end; page += 1) {
                addButton(page, String(page), false, page === currentPage);
            }

            if (end < totalPages) {
                if (end < totalPages - 1) {
                    buttons.push('<span class="px-1 text-secondary-light">...</span>');
                }
                addButton(totalPages, String(totalPages));
            }

            addButton(currentPage + 1, '>', currentPage === totalPages);

            return buttons.join('');
        }

        // Vẽ lại bảng linh kiện theo trang dữ liệu hiện tại.
        function renderDeliveryPackageItemsTable() {
            const tbody = document.getElementById('resultPackageItemsBody');
            const footer = document.getElementById('resultPackageItemsFooter');
            const summary = document.getElementById('resultPackageItemsSummary');
            const pagination = document.getElementById('resultPackageItemsPagination');
            const perPageSelect = document.getElementById('packageItemsPerPage');
            const table = tbody ? tbody.closest('table') : null;
            const thead = table ? table.querySelector('thead') : null;

            if (!tbody) {
                return;
            }

            const rows = Array.isArray(deliveryPackageItemsState.rows) ? deliveryPackageItemsState.rows : [];
            const meta = deliveryPackageItemsState.meta || {};
            const sizeMeta = deliveryPackageItemsState.sizeMeta || meta.size_meta || {};
            const perPageFromSelect = parseInt(perPageSelect?.value ?? meta.per_page ?? deliveryPackageItemsState.perPage, 10);
            deliveryPackageItemsState.perPage = Number.isFinite(perPageFromSelect) && perPageFromSelect > 0 ? perPageFromSelect : 15;
            if (perPageSelect && perPageSelect.value !== String(deliveryPackageItemsState.perPage)) {
                perPageSelect.value = String(deliveryPackageItemsState.perPage);
            }

            if (thead) {
                thead.innerHTML = `
                    <tr>
                        <th scope="col" rowspan="2" class="align-middle border-r border-neutral-200 last:border-r-0">STT</th>
                        <th scope="col" rowspan="2" class="align-middle border-r border-neutral-200 last:border-r-0">Mã linh kiện</th>
                        <th scope="col" rowspan="2" class="align-middle border-r border-neutral-200 last:border-r-0">Tên linh kiện</th>
                        <th scope="col" rowspan="2" class="align-middle border-r border-neutral-200 last:border-r-0">Loại</th>
                        <th scope="col" colspan="2" class="border-r border-b border-neutral-200 last:border-r-0 text-center py-2 text-xs font-bold uppercase text-neutral-700">
                            ${escapeHtml(sizeMeta.title || 'KÍCH THƯỚC (MM)')}
                        </th>
                    </tr>
                    <tr>
                        <th scope="col" class="w-40 border-r border-neutral-200 last:border-r-0 text-center py-2 text-xs font-semibold text-neutral-700">
                            ${escapeHtml(sizeMeta.label_1 || 'CAO (CHIỀU VÁN)')}
                        </th>
                        <th scope="col" class="w-40 text-center py-2 text-xs font-semibold text-neutral-700">
                            ${escapeHtml(sizeMeta.label_2 || 'RỘNG')}
                        </th>
                    </tr>
                `;
            }

            const total = Number.isFinite(Number(meta.total)) ? Number(meta.total) : rows.length;
            const currentPage = Number.isFinite(Number(meta.current_page)) ? Number(meta.current_page) : deliveryPackageItemsState.page;
            const lastPage = Number.isFinite(Number(meta.last_page))
                ? Number(meta.last_page)
                : Math.max(Math.ceil(total / deliveryPackageItemsState.perPage), 1);
            deliveryPackageItemsState.page = Math.min(Math.max(currentPage, 1), lastPage);

            if (total === 0) {
                tbody.innerHTML = `
                    <tr id="resultPackageItemsEmptyRow">
                        <td colspan="6" class="px-6 py-12 text-center text-neutral-400">Chưa có linh kiện nào được hiển thị.</td>
                    </tr>
                `;
                if (summary) {
                    summary.textContent = 'Hiển thị 0 đến 0 trong tổng 0 linh kiện';
                }
                if (pagination) {
                    pagination.innerHTML = '';
                }
                if (footer) {
                    footer.classList.remove('hidden');
                }
                return;
            }

            tbody.innerHTML = rows.map((item, index) => `
                <tr class="delivery-page__package-item-row">
                    <td class="border-r border-neutral-200 last:border-r-0 text-center font-semibold text-neutral-700">${((deliveryPackageItemsState.page - 1) * deliveryPackageItemsState.perPage) + index + 1}</td>
                    <td class="border-r border-neutral-200 last:border-r-0">
                        <span class="inline-flex items-center rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-1.5 font-mono text-xs font-semibold text-neutral-800">${escapeHtml(item.product_code || '?')}</span>
                    </td>
                    <td class="border-r border-neutral-200 last:border-r-0 font-semibold text-neutral-900">${escapeHtml(item.product_name || '?')}</td>
                    <td class="border-r border-neutral-200 last:border-r-0">
                        <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">${escapeHtml(item.type || '?')}</span>
                    </td>
                    <td class="border-r border-neutral-200 last:border-r-0 text-neutral-900 font-semibold">${escapeHtml(item.size_value_1 || '—')}</td>
                    <td class="border-r border-neutral-200 last:border-r-0 text-neutral-900 font-semibold">${escapeHtml(item.size_value_2 || '—')}</td>
                </tr>
            `).join('');

            if (summary) {
                const from = Number.isFinite(Number(meta.first_item)) ? Number(meta.first_item) : (((deliveryPackageItemsState.page - 1) * deliveryPackageItemsState.perPage) + 1);
                const to = Number.isFinite(Number(meta.last_item)) ? Number(meta.last_item) : (from + rows.length - 1);
                summary.textContent = `Hiển thị ${from} đến ${to} trong tổng ${total} linh kiện`;
            }

            if (pagination) {
                pagination.innerHTML = lastPage > 1
                    ? renderDeliveryPackageItemsPagination(deliveryPackageItemsState.page, lastPage)
                    : '';
            }

            if (footer) {
                footer.classList.remove('hidden');
            }
        }

        function changeDeliveryHistoryPerPage(value) {
            const url = new URL(window.location.href);
            const searchInput = document.getElementById('historySearch');
            const searchValue = searchInput ? searchInput.value.trim() : '';

            url.searchParams.set('per_page', value);
            url.searchParams.delete('page');

            if (searchValue) {
                url.searchParams.set('search', searchValue);
            } else {
                url.searchParams.delete('search');
            }

            window.location.href = url.toString();
        }

        function resetResultPanel() {
            const emptyState = document.getElementById('deliveryEmptyState');
            const resultPanel = document.getElementById('deliveryResultPanel');

            if (emptyState) {
                emptyState.classList.remove('hidden');
            }
            if (resultPanel) {
                resultPanel.classList.add('hidden');
            }
            document.getElementById('product_code').value = '';
            document.getElementById('deliveryPackageId').value = '';
            document.getElementById('resultOrderInfoList').innerHTML = '';
            document.getElementById('resultPackageInfoList').innerHTML = '';
            document.getElementById('resultPackageItemsBody').innerHTML = `
                <tr id="resultPackageItemsEmptyRow">
                    <td colspan="6" class="px-6 py-12 text-center text-neutral-400">Chưa có linh kiện nào được hiển thị.</td>
                </tr>
            `;
            deliveryPackageItemsState.rows = [];
            deliveryPackageItemsState.page = 1;
            deliveryPackageItemsState.packageId = null;
            deliveryPackageItemsState.meta = null;
            const packageItemsFooter = document.getElementById('resultPackageItemsFooter');
            if (packageItemsFooter) {
                packageItemsFooter.classList.add('hidden');
            }
            document.getElementById('resultPackageName').textContent = '-';
            document.getElementById('resultPackageCode').textContent = '-';
            const deliveryNote = document.getElementById('deliveryNote');
            const confirmButton = document.getElementById('resultConfirmButton');

            if (deliveryNote) {
                deliveryNote.value = '';
                deliveryNote.disabled = true;
            }

            confirmButton.disabled = true;
            confirmButton.classList.add('opacity-50', 'cursor-not-allowed');
            confirmButton.innerHTML = `
                <iconify-icon icon="lucide:navigation" class="text-base"></iconify-icon>
                Xác nhận giao hàng
            `;
            toggleSubmitButton();
            document.getElementById('product_code').focus();
        }

        function renderInfoList(containerId, items) {
            const container = document.getElementById(containerId);
            if (!container) {
                return;
            }

            container.innerHTML = (Array.isArray(items) ? items : []).map((item) => `
                <div class="rounded-xl border border-neutral-200 bg-white px-4 py-3">
                    <div class="text-xs font-bold uppercase tracking-wide text-neutral-400 mb-1">${escapeHtml(item.label || '—')}</div>
                    <div class="text-sm font-semibold text-neutral-900 break-words">${escapeHtml(item.value || '—')}</div>
                </div>
            `).join('');
        }

        function renderPackageItems(items, meta = null) {
            deliveryPackageItemsState.rows = Array.isArray(items) ? items : [];
            deliveryPackageItemsState.meta = meta && typeof meta === 'object' ? meta : null;
            deliveryPackageItemsState.sizeMeta = deliveryPackageItemsState.meta?.size_meta || null;

            if (deliveryPackageItemsState.meta) {
                deliveryPackageItemsState.perPage = parseInt(deliveryPackageItemsState.meta.per_page ?? deliveryPackageItemsState.perPage, 10) || deliveryPackageItemsState.perPage;
                deliveryPackageItemsState.page = parseInt(deliveryPackageItemsState.meta.current_page ?? deliveryPackageItemsState.page, 10) || deliveryPackageItemsState.page;
            } else {
                deliveryPackageItemsState.page = 1;
            }

            renderDeliveryPackageItemsTable();
        }

        async function reloadDeliveryPackageItems(page = 1) {
            if (!deliveryPackageItemsState.packageId) {
                return;
            }

            const perPageSelect = document.getElementById('packageItemsPerPage');
            const perPage = parseInt(perPageSelect?.value ?? deliveryPackageItemsState.perPage, 10);
            const nextPage = parseInt(page, 10);

            try {
                const response = await fetch(deliveryPreviewUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        package_id: deliveryPackageItemsState.packageId,
                        per_page: Number.isFinite(perPage) && perPage > 0 ? perPage : 15,
                        page: Number.isFinite(nextPage) && nextPage > 0 ? nextPage : 1,
                    }),
                });

                const contentType = response.headers.get('content-type') || '';
                const payload = contentType.includes('application/json')
                    ? await response.json()
                    : { success: false, message: await response.text() };

                if (!response.ok || !payload.success) {
                    showToast(payload.message || 'Không thể tải lại bảng linh kiện.', 'error');
                    return;
                }

                renderPackageItems(payload.data.package_items || [], payload.data.package_items_meta || null);
            } catch (error) {
                showToast('Không thể tải lại bảng linh kiện.', 'error');
            }
        }

        function renderResultPanel(data) {
            const emptyState = document.getElementById('deliveryEmptyState');
            const resultPanel = document.getElementById('deliveryResultPanel');

            if (emptyState) {
                emptyState.classList.add('hidden');
            }
            if (resultPanel) {
                resultPanel.classList.remove('hidden');
            }

            const canConfirm = Boolean(data.can_confirm);
            const confirmButton = document.getElementById('resultConfirmButton');
            const packageIdInput = document.getElementById('deliveryPackageId');
            const deliveryNote = document.getElementById('deliveryNote');

            document.getElementById('resultPackageName').textContent = data.package_name || '—';
            document.getElementById('resultPackageCode').textContent = data.package_code || '—';
            document.getElementById('todayDeliveryCount').textContent = data.today_count ?? document.getElementById('todayDeliveryCount').textContent;

            if (packageIdInput) {
                packageIdInput.value = data.package_id || '';
            }
            deliveryPackageItemsState.packageId = data.package_id || null;

            renderInfoList('resultOrderInfoList', data.order_info || []);
            renderInfoList('resultPackageInfoList', data.package_info || []);
            renderPackageItems(data.package_items || [], data.package_items_meta || null);

            if (deliveryNote) {
                deliveryNote.value = data.delivered_note || '';
                deliveryNote.disabled = !canConfirm;
            }

            if (confirmButton) {
                confirmButton.disabled = !canConfirm;
                confirmButton.classList.toggle('opacity-50', !canConfirm);
                confirmButton.classList.toggle('cursor-not-allowed', !canConfirm);
            }
        }

        function renderHistoryRow(row) {
            return `
                <tr class="delivery-page__history-row" data-history-row="1">
                    <td class="delivery-page__history-cell delivery-page__history-cell--code border-r border-neutral-200 last:border-r-0">
                        <span class="inline-flex items-center rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-1.5 font-mono text-xs font-semibold text-neutral-800">
                            ${escapeHtml(row.package_code || row.product_code)}
                        </span>
                    </td>
                    <td class="delivery-page__history-cell delivery-page__history-cell--name border-r border-neutral-200 last:border-r-0 font-semibold text-neutral-900">
                        ${escapeHtml(row.package_name || row.product_name)}
                    </td>
                    <td class="delivery-page__history-cell delivery-page__history-cell--order border-r border-neutral-200 last:border-r-0 text-emerald-600 font-semibold">
                        ${escapeHtml(row.order_code)}
                    </td>
                    <td class="delivery-page__history-cell delivery-page__history-cell--status border-r border-neutral-200 last:border-r-0">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-success-200 bg-success-100 px-2.5 py-1 text-xs font-semibold text-success-700">
                            <iconify-icon icon="lucide:check-circle-2" class="text-sm"></iconify-icon>
                            ${escapeHtml(row.status_label || 'Đã giao')}
                        </span>
                    </td>
                    <td class="delivery-page__history-cell delivery-page__history-cell--notes border-r border-neutral-200 last:border-r-0 text-secondary-light">
                        ${escapeHtml(row.notes || '—')}
                    </td>
                    <td class="delivery-page__history-cell delivery-page__history-cell--operator border-r border-neutral-200 last:border-r-0">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs">
                                ${escapeHtml((row.operator || 'H').slice(0, 1))}
                            </span>
                            <span class="font-medium text-secondary-light">${escapeHtml(row.operator || '—')}</span>
                        </div>
                    </td>
                    <td class="delivery-page__history-cell delivery-page__history-cell--time border-r border-neutral-200 last:border-r-0 text-secondary-light">
                        ${escapeHtml(row.time || '—')}
                    </td>
                    <td class="delivery-page__history-cell delivery-page__history-cell--action border-r border-neutral-200 last:border-r-0 text-end">
                        <a href="${escapeHtml(row.view_url || '#')}"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-emerald-600 shadow-sm transition-colors hover:border-emerald-600 hover:bg-emerald-600 hover:text-white">
                            <iconify-icon icon="lucide:eye" class="text-base"></iconify-icon>
                        </a>
                    </td>
                </tr>
            `;
        }

        function prependHistoryRows(rows) {
            const tbody = document.getElementById('historyTableBody');
            const emptyRow = document.getElementById('noHistoryRow');

            if (emptyRow) {
                emptyRow.remove();
            }

            const html = rows.map(renderHistoryRow).join('');
            tbody.insertAdjacentHTML('afterbegin', html);
            filterHistoryTable();
        }

        async function submitDeliveryForm(code) {
            if (isSubmittingDelivery) {
                return;
            }

            const productCode = (code || document.getElementById('product_code').value).trim();
            if (!productCode) {
                toggleSubmitButton();
                return;
            }

            const submitButton = document.getElementById('deliverySubmitBtn');
            const submitLabel = document.querySelector('.delivery-page__scan-submit-label');
            isSubmittingDelivery = true;
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            if (submitLabel) {
                submitLabel.textContent = 'Đang kiểm tra...';
            }

            try {
                const response = await fetch(deliveryPreviewUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        product_code: productCode,
                        per_page: deliveryPackageItemsState.perPage,
                        page: 1,
                    }),
                });

                const contentType = response.headers.get('content-type') || '';
                const payload = contentType.includes('application/json')
                    ? await response.json()
                    : { success: false, message: await response.text() };

                if (!response.ok || !payload.success) {
                    showToast(payload.message || 'Kiểm tra mã thất bại.', 'error');
                    return;
                }

                renderResultPanel(payload.data);
                showToast(payload.message || 'Đã mở thông tin kiện ở bên phải.', 'success');
            } catch (error) {
                showToast('Không thể kiểm tra mã lúc này.', 'error');
            } finally {
                isSubmittingDelivery = false;
                toggleSubmitButton();
            }
        }

        async function confirmDelivery() {
            if (isSubmittingDelivery) {
                return;
            }

            const packageId = document.getElementById('deliveryPackageId').value.trim();
            if (!packageId) {
                showToast('Vui lòng kiểm tra mã kiện trước khi xác nhận giao hàng.', 'error');
                return;
            }

            const confirmButton = document.getElementById('resultConfirmButton');
            const deliveryNoteInput = document.getElementById('deliveryNote');
            const deliveredNote = deliveryNoteInput ? deliveryNoteInput.value.trim() : '';
            isSubmittingDelivery = true;
            confirmButton.disabled = true;
            if (deliveryNoteInput) {
                deliveryNoteInput.disabled = true;
            }

            try {
                const response = await fetch(deliveryConfirmUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        package_id: packageId,
                        delivered_note: deliveredNote,
                        per_page: deliveryPackageItemsState.perPage,
                        page: deliveryPackageItemsState.page,
                    }),
                });

                const contentType = response.headers.get('content-type') || '';
                const payload = contentType.includes('application/json')
                    ? await response.json()
                    : { success: false, message: await response.text() };

                if (!response.ok || !payload.success) {
                    showToast(payload.message || 'Xác nhận giao hàng thất bại.', 'error');
                    confirmButton.disabled = false;
                    confirmButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    if (deliveryNoteInput) {
                        deliveryNoteInput.disabled = false;
                    }
                    return;
                }

                renderResultPanel(payload.data);
                if (Array.isArray(payload.data.rows) && payload.data.rows.length > 0) {
                    prependHistoryRows(payload.data.rows);
                }
                showToast(payload.message || 'Xác nhận giao hàng thành công.', 'success');
            } catch (error) {
                showToast('Không thể xác nhận giao hàng vào lúc này.', 'error');
                confirmButton.disabled = false;
                confirmButton.classList.remove('opacity-50', 'cursor-not-allowed');
                if (deliveryNoteInput) {
                    deliveryNoteInput.disabled = false;
                }
            } finally {
                isSubmittingDelivery = false;
                toggleSubmitButton();
            }
        }

        async function startScanning() {
            const modal = document.getElementById('scannerModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode('reader');
            }

            try {
                await html5QrCode.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    async (decodedText) => {
                        document.getElementById('product_code').value = decodedText;
                        toggleSubmitButton();
                        await stopScanning();
                        await submitDeliveryForm(decodedText);
                    }
                );
            } catch (error) {
                showToast('Không thể mở camera quét mã.', 'error');
                await stopScanning();
            }
        }

        async function stopScanning() {
            const modal = document.getElementById('scannerModal');

            if (html5QrCode) {
                try {
                    await html5QrCode.stop();
                } catch (error) {
                    // Bỏ qua lỗi dừng camera nếu trình duyệt đã tự khóa luồng.
                }
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.addEventListener('DOMContentLoaded', function () {
            const productCodeInput = document.getElementById('product_code');
            const deliveryScanForm = document.getElementById('deliveryScanForm');
            const filterToggleBtn = document.getElementById('filterToggleBtn');
            const confirmButton = document.getElementById('resultConfirmButton');
            const resetButton = document.getElementById('resultResetButton');

            toggleSubmitButton();

            productCodeInput.addEventListener('input', toggleSubmitButton);
            deliveryScanForm.addEventListener('submit', function (event) {
                event.preventDefault();
                submitDeliveryForm();
            });

            if (confirmButton) {
                confirmButton.addEventListener('click', confirmDelivery);
            }

            if (resetButton) {
                resetButton.addEventListener('click', resetResultPanel);
            }

            filterToggleBtn.addEventListener('click', function () {
                openModal('delivery-filter-modal');
            });

            document.querySelectorAll('[data-range-link]').forEach((link) => {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    closeModal('delivery-filter-modal');
                    const url = new URL(this.href, window.location.origin);
                    const searchValue = document.getElementById('historySearch').value.trim();
                    if (searchValue) {
                        url.searchParams.set('search', searchValue);
                    }
                    window.location.href = url.toString();
                });
            });

            filterHistoryTable();
        });
    </script>
@endsection
