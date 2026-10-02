@props([
    'startDate' => null,
    'endDate' => null,
    'preset' => null,
    'action' => null,
    'formId' => null,
    'standalone' => true,
    'class' => '',
    'compact' => false,
])

@php
    $uid = 'df_' . substr(md5(uniqid(rand(), true)), 0, 8);
    $actualFormId = $formId ?? ($uid . '_form');
    $actionUrl = $action ?? url()->current();

    // Auto-detect from request if not explicitly provided
    $reqPreset = request('date_preset');
    $reqStartDate = request('start_date') ?? request('from_date') ?? request('filter_start_date');
    $reqEndDate = request('end_date') ?? request('to_date') ?? request('filter_end_date');

    // Handle legacy date_mode & date_val
    $dateMode = request('date_mode');
    $dateVal = request('date_val');
    if (!$reqPreset && $dateMode) {
        if ($dateMode === 'day' && $dateVal) {
            $reqStartDate = $dateVal;
            $reqEndDate = $dateVal;
            $reqPreset = ($dateVal === now()->toDateString()) ? 'today' : 'custom';
        } elseif ($dateMode === 'month' && $dateVal) {
            $c = \Carbon\Carbon::parse($dateVal . '-01');
            $reqStartDate = $c->copy()->startOfMonth()->toDateString();
            $reqEndDate = $c->copy()->endOfMonth()->toDateString();
            $reqPreset = ($dateVal === now()->format('Y-m')) ? 'this_month' : 'custom';
        } elseif ($dateMode === 'year' && $dateVal) {
            $reqStartDate = $dateVal . '-01-01';
            $reqEndDate = $dateVal . '-12-31';
            $reqPreset = ($dateVal === now()->format('Y')) ? 'this_year' : 'custom';
        } elseif ($dateMode === 'all') {
            $reqPreset = 'all';
            $reqStartDate = null;
            $reqEndDate = null;
        }
    }

    $currentPreset = $preset ?? $reqPreset ?? ($reqStartDate || $reqEndDate ? 'custom' : 'all');
    $currentStart = $startDate ?? $reqStartDate ?? '';
    $currentEnd = $endDate ?? $reqEndDate ?? '';

    $isFiltered = ($currentPreset !== 'all' && ($currentStart || $currentEnd || $currentPreset));
@endphp

@if($standalone)
<form method="GET" action="{{ $actionUrl }}" id="{{ $actualFormId }}" class="inline-block {{ $class }}">
    {{-- Tự động giữ lại các tham số query params khác (search, status, per_page, tab, v.v.) --}}
    @foreach(request()->except(['date_preset', 'start_date', 'end_date', 'from_date', 'to_date', 'filter_start_date', 'filter_end_date', 'date_mode', 'date_val', 'page']) as $k => $v)
        @if(is_array($v))
            @foreach($v as $arrV)
                <input type="hidden" name="{{ $k }}[]" value="{{ $arrV }}">
            @endforeach
        @elseif($v !== null && $v !== '')
            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endif
    @endforeach
@endif

    <div class="inline-flex flex-wrap items-center gap-2 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-xl p-1.5 shadow-xs text-xs">
        <input type="hidden" name="date_preset" id="{{ $uid }}_preset" value="{{ $currentPreset }}">

        {{-- Preset Buttons --}}
        <div class="inline-flex items-center bg-neutral-100 dark:bg-neutral-900 rounded-lg p-0.5 font-medium">
            <button type="button" onclick="{{ $uid }}_setPreset('today')" id="{{ $uid }}_btn_today"
                class="px-2.5 py-1 rounded-md transition-all {{ $currentPreset === 'today' ? 'bg-white dark:bg-neutral-800 text-primary-600 shadow-2xs font-semibold' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}"
                title="Hôm nay">
                Hôm nay
            </button>
            <button type="button" onclick="{{ $uid }}_setPreset('yesterday')" id="{{ $uid }}_btn_yesterday"
                class="px-2.5 py-1 rounded-md transition-all {{ $currentPreset === 'yesterday' ? 'bg-white dark:bg-neutral-800 text-primary-600 shadow-2xs font-semibold' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}"
                title="Hôm qua">
                Hôm qua
            </button>
            <button type="button" onclick="{{ $uid }}_setPreset('7_days')" id="{{ $uid }}_btn_7_days"
                class="px-2.5 py-1 rounded-md transition-all {{ $currentPreset === '7_days' ? 'bg-white dark:bg-neutral-800 text-primary-600 shadow-2xs font-semibold' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}"
                title="7 ngày gần nhất">
                7 ngày
            </button>
            <button type="button" onclick="{{ $uid }}_setPreset('this_month')" id="{{ $uid }}_btn_this_month"
                class="px-2.5 py-1 rounded-md transition-all {{ $currentPreset === 'this_month' ? 'bg-white dark:bg-neutral-800 text-primary-600 shadow-2xs font-semibold' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}"
                title="Tháng này">
                Tháng này
            </button>
            <button type="button" onclick="{{ $uid }}_setPreset('this_year')" id="{{ $uid }}_btn_this_year"
                class="px-2.5 py-1 rounded-md transition-all {{ $currentPreset === 'this_year' ? 'bg-white dark:bg-neutral-800 text-primary-600 shadow-2xs font-semibold' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}"
                title="Năm nay">
                Năm nay
            </button>
            <button type="button" onclick="{{ $uid }}_setPreset('all')" id="{{ $uid }}_btn_all"
                class="px-2.5 py-1 rounded-md transition-all {{ $currentPreset === 'all' ? 'bg-white dark:bg-neutral-800 text-primary-600 shadow-2xs font-semibold' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200' }}"
                title="Tất cả thời gian">
                Tất cả
            </button>
        </div>

        {{-- Divider --}}
        <div class="h-4 w-px bg-neutral-200 dark:bg-neutral-700 hidden sm:block"></div>

        {{-- Date range inputs --}}
        <div class="inline-flex items-center gap-1.5">
            <div class="relative flex items-center">
                <input type="date" name="start_date" id="{{ $uid }}_start" value="{{ $currentStart }}"
                    onchange="{{ $uid }}_onCustomDateChange()"
                    class="form-control form-control-xs py-1 px-2 text-xs border border-neutral-200 dark:border-neutral-700 rounded-lg bg-neutral-50 dark:bg-neutral-900 text-neutral-800 dark:text-neutral-200 focus:bg-white cursor-pointer w-32"
                    title="Từ ngày">
            </div>
            <span class="text-neutral-400 font-semibold text-xs">→</span>
            <div class="relative flex items-center">
                <input type="date" name="end_date" id="{{ $uid }}_end" value="{{ $currentEnd }}"
                    onchange="{{ $uid }}_onCustomDateChange()"
                    class="form-control form-control-xs py-1 px-2 text-xs border border-neutral-200 dark:border-neutral-700 rounded-lg bg-neutral-50 dark:bg-neutral-900 text-neutral-800 dark:text-neutral-200 focus:bg-white cursor-pointer w-32"
                    title="Đến ngày">
            </div>

            {{-- Submit button --}}
            <button type="submit"
                class="btn btn-xs bg-primary-600 hover:bg-primary-700 text-white rounded-lg px-2.5 py-1 text-xs font-semibold flex items-center gap-1 shadow-2xs transition"
                title="Áp dụng bộ lọc">
                <iconify-icon icon="lucide:filter" class="text-xs"></iconify-icon>
                <span>Lọc</span>
            </button>

            {{-- Reset button if filtered --}}
            @if($isFiltered)
                <button type="button" onclick="{{ $uid }}_setPreset('all')"
                    class="btn btn-xs bg-neutral-100 hover:bg-neutral-200 text-neutral-600 dark:bg-neutral-700 dark:text-neutral-300 rounded-lg px-2 py-1 text-xs font-medium flex items-center gap-1 transition"
                    title="Xóa lọc ngày">
                    <iconify-icon icon="lucide:x" class="text-xs"></iconify-icon>
                    <span>Xóa</span>
                </button>
            @endif
        </div>
    </div>

@if($standalone)
</form>
@endif

<script>
(function() {
    const pad = n => String(n).padStart(2, '0');
    const formatDate = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;

    window.{{ $uid }}_setPreset = function(preset) {
        const form = document.getElementById('{{ $actualFormId }}');
        const presetInput = document.getElementById('{{ $uid }}_preset');
        const startInput = document.getElementById('{{ $uid }}_start');
        const endInput = document.getElementById('{{ $uid }}_end');

        if (presetInput) presetInput.value = preset;

        const now = new Date();
        let startStr = '';
        let endStr = '';

        if (preset === 'today') {
            startStr = formatDate(now);
            endStr = formatDate(now);
        } else if (preset === 'yesterday') {
            const y = new Date(now);
            y.setDate(y.getDate() - 1);
            startStr = formatDate(y);
            endStr = formatDate(y);
        } else if (preset === '7_days') {
            const d7 = new Date(now);
            d7.setDate(d7.getDate() - 6);
            startStr = formatDate(d7);
            endStr = formatDate(now);
        } else if (preset === 'this_month') {
            const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
            const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            startStr = formatDate(firstDay);
            endStr = formatDate(lastDay);
        } else if (preset === 'this_year') {
            startStr = `${now.getFullYear()}-01-01`;
            endStr = `${now.getFullYear()}-12-31`;
        } else if (preset === 'all') {
            startStr = '';
            endStr = '';
        }

        if (startInput) startInput.value = startStr;
        if (endInput) endInput.value = endStr;

        if (form) {
            form.submit();
        }
    };

    window.{{ $uid }}_onCustomDateChange = function() {
        const presetInput = document.getElementById('{{ $uid }}_preset');
        if (presetInput) {
            presetInput.value = 'custom';
        }
        ['today', 'yesterday', '7_days', 'this_month', 'this_year', 'all'].forEach(p => {
            const btn = document.getElementById('{{ $uid }}_btn_' + p);
            if (btn) {
                btn.className = 'px-2.5 py-1 rounded-md transition-all text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200';
            }
        });
    };
})();
</script>
