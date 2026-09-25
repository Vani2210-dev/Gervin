@props([
    'dateMode' => 'all',
    'dateVal' => '',
    'formId' => null,
    'standalone' => true,
    'action' => null,
    'showAll' => true,
    'showYear' => true,
    'showMonth' => true,
    'showDay' => true,
    'prefix' => null,
    'compact' => false,
    'class' => '',
])

@php
    $uid = $prefix ?? ('fdf_' . substr(md5(uniqid(rand(), true)), 0, 8));
    $actualFormId = $formId ?? ($uid . '_form');
    $currentMode = in_array($dateMode, ['day', 'month', 'year', 'all']) ? $dateMode : ($dateMode ? 'day' : 'all');
    $currentVal = $dateVal ?? '';
    $actionUrl = $action ?? url()->current();
@endphp

@if($standalone)
<form method="GET" action="{{ $actionUrl }}" id="{{ $actualFormId }}" class="inline-flex items-center {{ $class }}">
    {{-- Tự động giữ lại các tham số truy vấn khác trên URL (search, per_page, filter_*, v.v.) --}}
    @foreach(request()->except(['date_mode', 'date_val', 'filter_start_date', 'filter_end_date', 'page']) as $k => $v)
        @if(is_array($v))
            @foreach($v as $arrV)
                <input type="hidden" name="{{ $k }}[]" value="{{ $arrV }}">
            @endforeach
        @elseif($v !== null && $v !== '')
            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endif
    @endforeach
@endif

    <div class="inline-flex items-center bg-white border border-neutral-300 rounded-xl p-1 shadow-2xs {{ !$standalone ? $class : '' }}">
        {{-- Selector chọn chế độ --}}
        <div class="flex items-center bg-neutral-100 rounded-lg p-0.5 text-xs font-semibold mr-1.5">
            @if($showDay)
            <button type="button" onclick="{{ $uid }}_setMode('day')" id="{{ $uid }}_btn_day"
                class="px-2.5 py-1 rounded-md transition-all {{ $currentMode === 'day' ? 'bg-white text-primary-700 shadow-2xs font-bold' : 'text-neutral-500 hover:text-neutral-800' }}">
                Ngày
            </button>
            @endif

            @if($showMonth)
            <button type="button" onclick="{{ $uid }}_setMode('month')" id="{{ $uid }}_btn_month"
                class="px-2.5 py-1 rounded-md transition-all {{ $currentMode === 'month' ? 'bg-white text-primary-700 shadow-2xs font-bold' : 'text-neutral-500 hover:text-neutral-800' }}">
                Tháng
            </button>
            @endif

            @if($showYear)
            <button type="button" onclick="{{ $uid }}_setMode('year')" id="{{ $uid }}_btn_year"
                class="px-2.5 py-1 rounded-md transition-all {{ $currentMode === 'year' ? 'bg-white text-primary-700 shadow-2xs font-bold' : 'text-neutral-500 hover:text-neutral-800' }}">
                Năm
            </button>
            @endif

            @if($showAll)
            <button type="button" onclick="{{ $uid }}_setMode('all')" id="{{ $uid }}_btn_all"
                class="px-2.5 py-1 rounded-md transition-all {{ $currentMode === 'all' ? 'bg-white text-primary-700 shadow-2xs font-bold' : 'text-neutral-500 hover:text-neutral-800' }}">
                Tất cả
            </button>
            @endif
        </div>

        <input type="hidden" name="date_mode" id="{{ $uid }}_mode" value="{{ $currentMode }}">

        {{-- Ô nhập date duy nhất linh hoạt --}}
        <div id="{{ $uid }}_input_container" class="flex items-center">
            @if($currentMode === 'all')
                <input type="text" readonly value="Toàn thời gian" class="border-0 bg-transparent text-xs py-1 px-2 font-semibold text-neutral-500 w-28 cursor-default outline-none">
            @elseif($currentMode === 'day')
                <input type="date" name="date_val" id="{{ $uid }}_val" value="{{ $currentVal }}"
                    class="border-0 bg-transparent text-xs py-1 px-1.5 font-medium text-neutral-800 outline-none cursor-pointer"
                    onchange="document.getElementById('{{ $actualFormId }}').submit()">
            @elseif($currentMode === 'month')
                <input type="month" name="date_val" id="{{ $uid }}_val" value="{{ $currentVal }}"
                    class="border-0 bg-transparent text-xs py-1 px-1.5 font-medium text-neutral-800 outline-none cursor-pointer"
                    onchange="document.getElementById('{{ $actualFormId }}').submit()">
            @elseif($currentMode === 'year')
                <input type="number" min="2000" max="2099" name="date_val" id="{{ $uid }}_val" value="{{ $currentVal }}"
                    class="border-0 bg-transparent text-xs py-1 px-1.5 font-medium text-neutral-800 outline-none w-20"
                    placeholder="Năm..." onchange="document.getElementById('{{ $actualFormId }}').submit()">
            @endif
        </div>

        <button type="submit" class="btn btn-xs btn-primary rounded-lg px-2.5 py-1.5 text-xs font-semibold flex items-center gap-1 ml-1" title="Áp dụng lọc">
            <iconify-icon icon="lucide:arrow-right" class="text-xs"></iconify-icon>
        </button>
    </div>

@if($standalone)
</form>
@endif

<script>
function {{ $uid }}_setMode(mode) {
    const hiddenMode = document.getElementById('{{ $uid }}_mode');
    const container = document.getElementById('{{ $uid }}_input_container');
    const oldInput = document.getElementById('{{ $uid }}_val');
    const oldVal = oldInput ? oldInput.value : '';
    const form = document.getElementById('{{ $actualFormId }}');

    if (hiddenMode) hiddenMode.value = mode;

    ['day', 'month', 'year', 'all'].forEach(m => {
        const btn = document.getElementById('{{ $uid }}_btn_' + m);
        if (!btn) return;
        if (m === mode) {
            btn.className = 'px-2.5 py-1 rounded-md transition-all bg-white text-primary-700 shadow-2xs font-bold';
        } else {
            btn.className = 'px-2.5 py-1 rounded-md transition-all text-neutral-500 hover:text-neutral-800';
        }
    });

    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    const todayStr = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}`;
    const thisMonthStr = `${now.getFullYear()}-${pad(now.getMonth()+1)}`;
    const thisYearStr = `${now.getFullYear()}`;

    if (mode === 'day') {
        const val = (oldVal && oldVal.length === 10) ? oldVal : todayStr;
        container.innerHTML = `<input type="date" name="date_val" id="{{ $uid }}_val" value="${val}" class="border-0 bg-transparent text-xs py-1 px-1.5 font-medium text-neutral-800 outline-none cursor-pointer" onchange="document.getElementById('{{ $actualFormId }}').submit()">`;
    } else if (mode === 'month') {
        const val = (oldVal && oldVal.length >= 7) ? oldVal.substring(0, 7) : thisMonthStr;
        container.innerHTML = `<input type="month" name="date_val" id="{{ $uid }}_val" value="${val}" class="border-0 bg-transparent text-xs py-1 px-1.5 font-medium text-neutral-800 outline-none cursor-pointer" onchange="document.getElementById('{{ $actualFormId }}').submit()">`;
    } else if (mode === 'year') {
        const val = (oldVal && oldVal.length >= 4) ? oldVal.substring(0, 4) : thisYearStr;
        container.innerHTML = `<input type="number" min="2000" max="2099" name="date_val" id="{{ $uid }}_val" value="${val}" class="border-0 bg-transparent text-xs py-1 px-1.5 font-medium text-neutral-800 outline-none w-20" placeholder="Năm..." onchange="document.getElementById('{{ $actualFormId }}').submit()">`;
    } else {
        container.innerHTML = `<input type="text" readonly value="Toàn thời gian" class="border-0 bg-transparent text-xs py-1 px-2 font-semibold text-neutral-500 w-28 cursor-default outline-none">`;
    }

    if (form) form.submit();
}
</script>
