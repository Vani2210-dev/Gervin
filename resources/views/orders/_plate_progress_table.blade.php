{{-- resources/views/orders/_plate_progress_table.blade.php --}}
@php
    $items = $items ?? collect();
    $codeLabel = $codeLabel ?? 'Mã tấm';
    $packageItemsMap = $packageItemsMap ?? [];
@endphp

<div class="overflow-x-auto border border-neutral-200 rounded-xl shadow-xs">
    <table class="table bordered-table sm-table mb-0 w-full min-w-[1380px] text-xs">
        <thead>
            <tr class="bg-neutral-100 text-neutral-700">
                <th scope="col" class="w-[52px] min-w-[52px] max-w-[52px] text-center sticky left-0 z-20 bg-neutral-100 shadow-[1px_0_0_0_#e5e7eb]">
                    STT
                </th>
                <th scope="col" class="w-64 min-w-[240px] max-w-[260px] text-left sticky left-[52px] z-20 bg-neutral-100 border-r border-neutral-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.12)]">
                    {{ $codeLabel }}
                </th>
                <th scope="col" class="w-40 min-w-[150px] text-center bg-neutral-100">
                    1. Ép ván
                </th>
                <th scope="col" class="w-40 min-w-[150px] text-center bg-neutral-100">
                    2. Cắt CNC
                </th>
                <th scope="col" class="w-44 min-w-[160px] text-center bg-neutral-100">
                    3. Dán cạnh
                </th>
                <th scope="col" class="w-40 min-w-[150px] text-center bg-neutral-100">
                    4. Làm đẹp
                </th>
                <th scope="col" class="w-40 min-w-[150px] text-center bg-neutral-100">
                    5. KCS (QC)
                </th>
                <th scope="col" class="w-44 min-w-[160px] text-center bg-neutral-100">
                    6. Đóng gói
                </th>
                <th scope="col" class="min-w-[180px] text-left bg-neutral-100">
                    Ghi chú
                </th>
            </tr>
        </thead>
        <tbody>
            @php $rowSeq = 0; @endphp
            @forelse($items as $item)
                @php
                    $codes = $item->codes;
                    $hasAnyCode = $codes->count() > 0;
                    $rowCount = $hasAnyCode ? $codes->count() : 1;
                @endphp
                @for($sheetIdx = 0; $sheetIdx < $rowCount; $sheetIdx++)
                    @php
                        $rowSeq++;
                        $code = $hasAnyCode ? $codes[$sheetIdx] : null;
                        $productId = $code ? $code->product_id : ($item->product_code ?? '—');
                        $rawStatusLog = $code ? ($code->status ?? []) : [];
                        if (is_string($rawStatusLog)) {
                            $rawStatusLog = json_decode($rawStatusLog, true) ?? [];
                        }

                        $stagePressing = [];
                        $stageCnc = [];
                        $stageEdge = [];
                        $stageFinishing = [];
                        $stageQc = [];
                        $stagePacking = [];
                        $otherLogs = [];

                        foreach ($rawStatusLog as $log) {
                            $actLower = mb_strtolower($log['action'] ?? '');
                            if (str_contains($actLower, 'ép')) {
                                $stagePressing[] = $log;
                            } elseif (str_contains($actLower, 'cnc') || str_contains($actLower, 'cắt')) {
                                $stageCnc[] = $log;
                            } elseif (str_contains($actLower, 'dán') || str_contains($actLower, 'cạnh')) {
                                $stageEdge[] = $log;
                            } elseif (str_contains($actLower, 'làm đẹp') || str_contains($actLower, 'hoàn thiện')) {
                                $stageFinishing[] = $log;
                            } elseif (str_contains($actLower, 'qc') || str_contains($actLower, 'kcs')) {
                                $stageQc[] = $log;
                            } elseif (str_contains($actLower, 'đóng gói')) {
                                $stagePacking[] = $log;
                            } else {
                                $otherLogs[] = $log;
                            }
                        }

                        $pkgItem = $packageItemsMap[$productId] ?? null;
                    @endphp
                    <tr class="group hover:bg-neutral-50 transition-colors">
                        {{-- STT sticky --}}
                        <td class="w-[52px] min-w-[52px] max-w-[52px] text-center font-semibold text-neutral-500 sticky left-0 z-10 bg-white group-hover:bg-neutral-50 shadow-[1px_0_0_0_#e5e7eb] align-middle">
                            {{ $rowSeq }}
                        </td>

                        {{-- Mã tấm sticky --}}
                        <td class="w-64 min-w-[240px] max-w-[260px] text-left sticky left-[52px] z-10 bg-white group-hover:bg-neutral-50 border-r border-neutral-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.12)] align-middle py-2 px-3">
                            <span class="text-neutral-900 text-xs font-mono font-semibold block break-all">{{ $productId }}</span>
                            @if(!empty($item->product_name) && $item->product_name !== $productId)
                                <span class="text-[11px] text-neutral-500 block truncate" title="{{ $item->product_name }}">{{ $item->product_name }}</span>
                            @endif
                        </td>

                        {{-- 1. Ép ván --}}
                        <td class="text-center py-2 px-2.5 align-middle bg-white group-hover:bg-neutral-50">
                            @if(!empty($stagePressing))
                                <div class="flex flex-col items-center justify-center gap-1.5">
                                    @foreach($stagePressing as $log)
                                        @php
                                            $act = $log['action'] ?? 'Ép ván';
                                            $actLower = mb_strtolower($act);
                                            $badgeClass = 'bg-amber-50 text-amber-800 border border-amber-200';
                                            if (str_contains($actLower, 'lỗi') || str_contains($actLower, 'hỏng')) {
                                                $badgeClass = 'bg-rose-50 text-rose-700 border border-rose-200';
                                            } elseif (str_contains($actLower, 'quay lại')) {
                                                $badgeClass = 'bg-orange-50 text-orange-800 border border-orange-200';
                                            }
                                            $op = $log['operator'] ?? '';
                                            $time = !empty($log['time']) ? date('H:i d/m', strtotime($log['time'])) : '';
                                            $logNote = $log['notes'] ?? '';
                                        @endphp
                                        <div class="flex flex-col items-center text-center gap-0.5 w-full">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $badgeClass }}">
                                                {{ $act }}
                                            </span>
                                            @if($op || $time)
                                                <div class="text-[11px] text-neutral-600 flex items-center justify-center gap-1 flex-wrap leading-tight mt-0.5">
                                                    @if($op)
                                                        <span class="font-medium text-neutral-800">{{ $op }}</span>
                                                    @endif
                                                    @if($time)
                                                        <span class="text-[10px] text-neutral-400 font-mono">({{ $time }})</span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if($logNote)
                                                <div class="text-[10px] text-neutral-500 italic max-w-[150px] truncate mt-0.5" title="{{ $logNote }}">
                                                    {{ $logNote }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-neutral-300 font-mono text-xs">—</span>
                            @endif
                        </td>

                        {{-- 2. Cắt CNC --}}
                        <td class="text-center py-2 px-2.5 align-middle bg-white group-hover:bg-neutral-50">
                            @if(!empty($stageCnc))
                                <div class="flex flex-col items-center justify-center gap-1.5">
                                    @foreach($stageCnc as $log)
                                        @php
                                            $act = $log['action'] ?? 'Cắt CNC';
                                            $actLower = mb_strtolower($act);
                                            $badgeClass = 'bg-cyan-50 text-cyan-700 border border-cyan-200';
                                            if (str_contains($actLower, 'lỗi') || str_contains($actLower, 'hỏng')) {
                                                $badgeClass = 'bg-rose-50 text-rose-700 border border-rose-200';
                                            } elseif (str_contains($actLower, 'quay lại')) {
                                                $badgeClass = 'bg-amber-50 text-amber-800 border border-amber-200';
                                            } elseif (str_contains($actLower, 'hoàn thành')) {
                                                $badgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                            }
                                            $op = $log['operator'] ?? '';
                                            $time = !empty($log['time']) ? date('H:i d/m', strtotime($log['time'])) : '';
                                            $logNote = $log['notes'] ?? '';
                                        @endphp
                                        <div class="flex flex-col items-center text-center gap-0.5 w-full">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $badgeClass }}">
                                                {{ $act }}
                                            </span>
                                            @if($op || $time)
                                                <div class="text-[11px] text-neutral-600 flex items-center justify-center gap-1 flex-wrap leading-tight mt-0.5">
                                                    @if($op)
                                                        <span class="font-medium text-neutral-800">{{ $op }}</span>
                                                    @endif
                                                    @if($time)
                                                        <span class="text-[10px] text-neutral-400 font-mono">({{ $time }})</span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if($logNote)
                                                <div class="text-[10px] text-neutral-500 italic max-w-[150px] truncate mt-0.5" title="{{ $logNote }}">
                                                    {{ $logNote }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-neutral-300 font-mono text-xs">—</span>
                            @endif
                        </td>

                        {{-- 3. Dán cạnh --}}
                        <td class="text-center py-2 px-2.5 align-middle bg-white group-hover:bg-neutral-50">
                            @if(!empty($stageEdge))
                                <div class="flex flex-col items-center justify-center gap-1.5">
                                    @foreach($stageEdge as $log)
                                        @php
                                            $act = $log['action'] ?? 'Dán cạnh';
                                            $actLower = mb_strtolower($act);
                                            $badgeClass = 'bg-indigo-50 text-indigo-700 border border-indigo-200';
                                            if (str_contains($actLower, 'lỗi') || str_contains($actLower, 'hỏng')) {
                                                $badgeClass = 'bg-rose-50 text-rose-700 border border-rose-200';
                                            } elseif (str_contains($actLower, 'quay lại')) {
                                                $badgeClass = 'bg-amber-50 text-amber-800 border border-amber-200';
                                            } elseif (str_contains($actLower, 'hoàn thành')) {
                                                $badgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                            }
                                            $op = $log['operator'] ?? '';
                                            $time = !empty($log['time']) ? date('H:i d/m', strtotime($log['time'])) : '';
                                            $logNote = $log['notes'] ?? '';
                                        @endphp
                                        <div class="flex flex-col items-center text-center gap-0.5 w-full">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $badgeClass }}">
                                                {{ $act }}
                                            </span>
                                            @if($op || $time)
                                                <div class="text-[11px] text-neutral-600 flex items-center justify-center gap-1 flex-wrap leading-tight mt-0.5">
                                                    @if($op)
                                                        <span class="font-medium text-neutral-800">{{ $op }}</span>
                                                    @endif
                                                    @if($time)
                                                        <span class="text-[10px] text-neutral-400 font-mono">({{ $time }})</span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if($logNote)
                                                <div class="text-[10px] text-neutral-500 italic max-w-[150px] truncate mt-0.5" title="{{ $logNote }}">
                                                    {{ $logNote }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-neutral-300 font-mono text-xs">—</span>
                            @endif
                        </td>

                        {{-- 4. Làm đẹp --}}
                        <td class="text-center py-2 px-2.5 align-middle bg-white group-hover:bg-neutral-50">
                            @if(!empty($stageFinishing))
                                <div class="flex flex-col items-center justify-center gap-1.5">
                                    @foreach($stageFinishing as $log)
                                        @php
                                            $act = $log['action'] ?? 'Làm đẹp';
                                            $actLower = mb_strtolower($act);
                                            $badgeClass = 'bg-purple-50 text-purple-700 border border-purple-200';
                                            if (str_contains($actLower, 'lỗi') || str_contains($actLower, 'hỏng')) {
                                                $badgeClass = 'bg-rose-50 text-rose-700 border border-rose-200';
                                            } elseif (str_contains($actLower, 'quay lại')) {
                                                $badgeClass = 'bg-amber-50 text-amber-800 border border-amber-200';
                                            } elseif (str_contains($actLower, 'hoàn thành')) {
                                                $badgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                            }
                                            $op = $log['operator'] ?? '';
                                            $time = !empty($log['time']) ? date('H:i d/m', strtotime($log['time'])) : '';
                                            $logNote = $log['notes'] ?? '';
                                        @endphp
                                        <div class="flex flex-col items-center text-center gap-0.5 w-full">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $badgeClass }}">
                                                {{ $act }}
                                            </span>
                                            @if($op || $time)
                                                <div class="text-[11px] text-neutral-600 flex items-center justify-center gap-1 flex-wrap leading-tight mt-0.5">
                                                    @if($op)
                                                        <span class="font-medium text-neutral-800">{{ $op }}</span>
                                                    @endif
                                                    @if($time)
                                                        <span class="text-[10px] text-neutral-400 font-mono">({{ $time }})</span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if($logNote)
                                                <div class="text-[10px] text-neutral-500 italic max-w-[150px] truncate mt-0.5" title="{{ $logNote }}">
                                                    {{ $logNote }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-neutral-300 font-mono text-xs">—</span>
                            @endif
                        </td>

                        {{-- 5. KCS (QC) --}}
                        <td class="text-center py-2 px-2.5 align-middle bg-white group-hover:bg-neutral-50">
                            @if(!empty($stageQc))
                                <div class="flex flex-col items-center justify-center gap-1.5">
                                    @foreach($stageQc as $log)
                                        @php
                                            $act = $log['action'] ?? 'KCS (QC)';
                                            $actLower = mb_strtolower($act);
                                            $badgeClass = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                                            if (str_contains($actLower, 'lỗi') || str_contains($actLower, 'hỏng')) {
                                                $badgeClass = 'bg-rose-50 text-rose-700 border border-rose-200';
                                            } elseif (str_contains($actLower, 'quay lại') || str_contains($actLower, 'chờ')) {
                                                $badgeClass = 'bg-amber-50 text-amber-800 border border-amber-200';
                                            }
                                            $op = $log['operator'] ?? '';
                                            $time = !empty($log['time']) ? date('H:i d/m', strtotime($log['time'])) : '';
                                            $logNote = $log['notes'] ?? '';
                                        @endphp
                                        <div class="flex flex-col items-center text-center gap-0.5 w-full">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $badgeClass }}">
                                                {{ $act }}
                                            </span>
                                            @if($op || $time)
                                                <div class="text-[11px] text-neutral-600 flex items-center justify-center gap-1 flex-wrap leading-tight mt-0.5">
                                                    @if($op)
                                                        <span class="font-medium text-neutral-800">{{ $op }}</span>
                                                    @endif
                                                    @if($time)
                                                        <span class="text-[10px] text-neutral-400 font-mono">({{ $time }})</span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if($logNote)
                                                <div class="text-[10px] text-neutral-500 italic max-w-[150px] truncate mt-0.5" title="{{ $logNote }}">
                                                    {{ $logNote }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-neutral-300 font-mono text-xs">—</span>
                            @endif
                        </td>

                        {{-- 6. Đóng gói --}}
                        <td class="text-center py-2 px-2.5 align-middle bg-white group-hover:bg-neutral-50">
                            @if(!empty($stagePacking) || $pkgItem)
                                <div class="flex flex-col items-center justify-center gap-1.5">
                                    @foreach($stagePacking as $log)
                                        @php
                                            $act = $log['action'] ?? 'Đóng gói';
                                            $actLower = mb_strtolower($act);
                                            $badgeClass = 'bg-teal-50 text-teal-700 border border-teal-200';
                                            $op = $log['operator'] ?? '';
                                            $time = !empty($log['time']) ? date('H:i d/m', strtotime($log['time'])) : '';
                                            $logNote = $log['notes'] ?? '';
                                        @endphp
                                        <div class="flex flex-col items-center text-center gap-0.5 w-full">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $badgeClass }}">
                                                {{ $act }}
                                            </span>
                                            @if($op || $time)
                                                <div class="text-[11px] text-neutral-600 flex items-center justify-center gap-1 flex-wrap leading-tight mt-0.5">
                                                    @if($op)
                                                        <span class="font-medium text-neutral-800">{{ $op }}</span>
                                                    @endif
                                                    @if($time)
                                                        <span class="text-[10px] text-neutral-400 font-mono">({{ $time }})</span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if($logNote)
                                                <div class="text-[10px] text-neutral-500 italic max-w-[150px] truncate mt-0.5" title="{{ $logNote }}">
                                                    {{ $logNote }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach

                                    @if($pkgItem)
                                        @php
                                            $pkg = $pkgItem->package;
                                            $pkgTime = $pkgItem->updated_at ? $pkgItem->updated_at->format('H:i d/m') : '';
                                        @endphp
                                        <div class="flex flex-col items-center text-center gap-0.5 w-full">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-teal-50 text-teal-700 border border-teal-200">
                                                <iconify-icon icon="lucide:package-check" class="text-xs"></iconify-icon>
                                                <span>Đã đóng gói</span>
                                            </span>
                                            @if($pkg)
                                                <a href="{{ route('processes.packing.show', $pkg) }}" class="text-[11px] font-semibold text-primary-600 hover:underline mt-0.5">
                                                    {{ $pkg->name }}
                                                </a>
                                            @endif
                                            @if($pkgTime)
                                                <span class="text-[10px] text-neutral-400 font-mono leading-tight">({{ $pkgTime }})</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-neutral-300 font-mono text-xs">—</span>
                            @endif
                        </td>

                        {{-- Ghi chú --}}
                        <td class="text-left py-2 px-3 align-middle bg-white group-hover:bg-neutral-50 text-xs text-neutral-600">
                            @php
                                $itemNote = $item->notes ?? '';
                            @endphp
                            @if($itemNote)
                                <div class="font-medium text-neutral-800">{{ $itemNote }}</div>
                            @endif
                            @if(!empty($otherLogs))
                                @foreach($otherLogs as $olog)
                                    <div class="text-[11px] text-neutral-500 mt-0.5">
                                        <span class="font-medium text-neutral-700">{{ $olog['action'] ?? '' }}:</span> {{ $olog['notes'] ?? ($olog['operator'] ?? '') }}
                                    </div>
                                @endforeach
                            @endif
                            @if(!$itemNote && empty($otherLogs))
                                <span class="text-neutral-300 font-mono">—</span>
                            @endif
                        </td>
                    </tr>
                @endfor
            @empty
                <tr>
                    <td colspan="9" class="text-center text-neutral-400 py-6">Chưa có sản phẩm nào</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

