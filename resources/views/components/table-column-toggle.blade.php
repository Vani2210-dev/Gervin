@props([
    'tableId' => '',
    'label'   => 'Cột',
    'title'   => 'Tùy chỉnh hiển thị cột'
])

<div class="relative inline-block text-left table-column-toggle-wrapper {{ $attributes->get('class') }}" data-table-id="{{ $tableId }}">
    <button type="button"
        class="table-col-toggle-btn btn border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-200 text-xs btn-sm px-2.5 py-1.5 rounded-lg flex items-center gap-1.5 shadow-2xs font-semibold transition cursor-pointer"
        title="{{ $title }}"
        aria-haspopup="true">
        <iconify-icon icon="lucide:columns-3" class="text-base text-neutral-500 dark:text-neutral-400"></iconify-icon>
        @if($label)
            <span class="hidden sm:inline text-xs font-semibold">{{ $label }}</span>
        @endif
        <span class="col-toggle-badge hidden w-2 h-2 rounded-full bg-primary-600" title="Có cột đang được ẩn"></span>
    </button>

    {{-- Dropdown Menu / Popover (Sẽ được JS chuyển ra document.body để không bị overflow:hidden của card cắt xén) --}}
    <div class="table-col-dropdown hidden w-64 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-2xl overflow-hidden text-neutral-800 dark:text-neutral-200 max-h-[85vh] flex flex-col" style="z-index: 99999;">
        {{-- Header: Tiêu đề & Nút Mặc định --}}
        <div class="p-3 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between bg-neutral-50/80 dark:bg-neutral-900/50 shrink-0">
            <span class="text-xs font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-1.5">
                <iconify-icon icon="lucide:columns-3" class="text-sm text-primary-600"></iconify-icon>
                Hiển thị cột
            </span>
            <button type="button" class="btn-reset-default text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline cursor-pointer">
                Mặc định
            </button>
        </div>

        {{-- Thanh tìm kiếm nhanh tên cột --}}
        <div class="p-2 border-b border-neutral-100 dark:border-neutral-700 shrink-0">
            <div class="relative">
                <iconify-icon icon="solar:magnifer-linear" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-neutral-400 text-sm pointer-events-none"></iconify-icon>
                <input type="text"
                    class="col-search-input w-full text-xs pl-8 pr-2.5 py-1.5 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-900 focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 text-neutral-800 dark:text-neutral-200 placeholder-neutral-400"
                    placeholder="Tìm kiếm cột...">
            </div>
        </div>

        {{-- Danh sách Checkbox các cột --}}
        <div class="col-list overflow-y-auto py-1 scroll-sm divide-y divide-neutral-50 dark:divide-neutral-700/30 flex-1 max-h-64">
            {{-- Được sinh tự động bằng JavaScript từ thẻ <thead> của bảng --}}
        </div>

        {{-- Footer --}}
        <div class="px-3 py-2 bg-neutral-50/80 dark:bg-neutral-900/80 border-t border-neutral-100 dark:border-neutral-700 flex items-center justify-between text-[11px] text-neutral-500 shrink-0">
            <span class="col-count-info">Đang hiện 0/0 cột</span>
            <button type="button" class="btn-show-all text-primary-600 hover:underline font-medium cursor-pointer">Hiện tất cả</button>
        </div>
    </div>
</div>
