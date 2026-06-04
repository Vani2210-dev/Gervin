@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $onEachSide = 1;

        $links = [];

        if ($lastPage <= 6) {
            for ($i = 1; $i <= $lastPage; $i++) {
                $links[] = ['label' => $i, 'url' => $paginator->url($i), 'active' => ($i == $currentPage)];
            }
        } else {
            // Page 1
            $links[] = ['label' => 1, 'url' => $paginator->url(1), 'active' => ($currentPage == 1)];

            // First Ellipsis
            if ($currentPage - $onEachSide > 2) {
                $links[] = ['label' => '...', 'url' => null, 'active' => false];
            }

            // Middle buttons
            $start = max(2, $currentPage - $onEachSide);
            $end = min($lastPage - 1, $currentPage + $onEachSide);

            if ($currentPage <= 3) {
                $end = min($lastPage - 1, 4);
            }
            if ($currentPage >= $lastPage - 2) {
                $start = max(2, $lastPage - 3);
            }

            for ($i = $start; $i <= $end; $i++) {
                $links[] = ['label' => $i, 'url' => $paginator->url($i), 'active' => ($i == $currentPage)];
            }

            // Second Ellipsis
            if ($currentPage + $onEachSide < $lastPage - 1) {
                if ($currentPage < $lastPage - 2) {
                    $links[] = ['label' => '...', 'url' => null, 'active' => false];
                }
            }

            // Last page
            $links[] = ['label' => $lastPage, 'url' => $paginator->url($lastPage), 'active' => ($currentPage == $lastPage)];
        }
    @endphp

    <ul class="pagination flex flex-wrap items-center gap-1.5 justify-center m-0 p-0 list-none">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled opacity-50 pointer-events-none" aria-disabled="true">
                <span class="page-link bg-neutral-200 text-neutral-400 font-semibold rounded-lg flex items-center justify-center h-8 w-8 text-sm">
                    <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                </span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link bg-neutral-300 text-secondary-light font-semibold rounded-lg flex items-center justify-center h-8 w-8 text-sm transition-colors hover:bg-neutral-400 hover:text-white" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    <iconify-icon icon="ep:d-arrow-left"></iconify-icon>
                </a>
            </li>
        @endif

        {{-- Render generated links --}}
        @foreach ($links as $link)
            @if ($link['label'] === '...')
                <li class="page-item disabled px-1" aria-disabled="true">
                    <span class="text-neutral-400 font-bold text-sm">...</span>
                </li>
            @elseif ($link['active'])
                <li class="page-item active" aria-current="page">
                    <span class="page-link font-semibold rounded-lg flex items-center justify-center h-8 w-8 text-sm bg-primary-600 text-white select-none">{{ $link['label'] }}</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link font-semibold rounded-lg flex items-center justify-center h-8 w-8 text-sm bg-neutral-300 text-secondary-light transition-colors hover:bg-neutral-400 hover:text-white" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                </li>
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link bg-neutral-300 text-secondary-light font-semibold rounded-lg flex items-center justify-center h-8 w-8 text-sm transition-colors hover:bg-neutral-400 hover:text-white" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    <iconify-icon icon="ep:d-arrow-right"></iconify-icon>
                </a>
            </li>
        @else
            <li class="page-item disabled opacity-50 pointer-events-none" aria-disabled="true">
                <span class="page-link bg-neutral-200 text-neutral-400 font-semibold rounded-lg flex items-center justify-center h-8 w-8 text-sm">
                    <iconify-icon icon="ep:d-arrow-right"></iconify-icon>
                </span>
            </li>
        @endif
    </ul>
@endif
