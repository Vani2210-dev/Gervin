{{-- Stock Card Modal Content Partial --}}
<div class="space-y-4">
    {{-- Top info bar --}}
    <div class="bg-neutral-50 border border-neutral-200 rounded-xl p-4 flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-sm font-black text-neutral-900">{{ $material->code }}</span>
                <span class="text-xs text-neutral-500 font-medium">({{ $material->origin_code ?: 'Không có mã NCC' }})</span>
                <span class="bg-primary-100 text-primary-800 text-[10px] px-2 py-1 rounded font-bold">{{ $material->category }}</span>
            </div>
            <div class="text-xs text-neutral-600">{{ $material->name }}</div>
        </div>

        <div class="flex items-center gap-6">
            <div class="text-right">
                <div class="text-[10px] uppercase font-bold text-neutral-400">Tồn kho hiện tại</div>
                <div class="text-xl font-black text-primary-600">
                    {{ fmod($material->current_stock, 1) == 0 ? number_format($material->current_stock, 0, ',', '.') : number_format($material->current_stock, 2, ',', '.') }}
                    <span class="text-xs font-normal text-neutral-500">{{ $material->unit }}</span>
                </div>
            </div>
            <div class="text-right">
                <div class="text-[10px] uppercase font-bold text-neutral-400">Định mức tồn tối thiểu</div>
                <div class="text-sm font-bold text-neutral-700">
                    {{ number_format($material->min_stock, 0) }} {{ $material->unit }}
                </div>
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="border border-neutral-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto max-h-[50vh]">
            <table class="w-full text-xs text-left border-collapse">
                <thead class="bg-neutral-100 text-neutral-700 font-bold sticky top-0 z-10 border-b border-neutral-200">
                    <tr>
                        <th class="py-2 px-3 w-24">Ngày</th>
                        <th class="py-2 px-3 w-28">Số chứng từ</th>
                        <th class="py-2 px-3 w-28">Loại phát sinh</th>
                        <th class="py-2 px-3 min-w-[180px]">Diễn giải</th>
                        <th class="py-2 px-3 w-24 text-right bg-emerald-50 text-emerald-800">Nhập</th>
                        <th class="py-2 px-3 w-24 text-right bg-rose-50 text-rose-800">Xuất</th>
                        <th class="py-2 px-3 w-28 text-right bg-primary-50 text-primary-900">Tồn sau GD</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 bg-white">
                    @forelse($transactions as $t)
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="py-2 px-3 text-neutral-600">{{ $t->date ? $t->date->format('d/m/Y') : '-' }}</td>
                            <td class="py-2 px-3 font-bold text-neutral-800">{{ $t->voucher_code }}</td>
                            <td class="py-2 px-3">
                                @if($t->type === 'receipt')
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] px-2 py-1 rounded font-bold">Nhập kho</span>
                                @elseif($t->type === 'issue')
                                    <span class="bg-rose-100 text-rose-800 text-[10px] px-2 py-1 rounded font-bold">Xuất kho</span>
                                @elseif($t->type === 'stocktake')
                                    <span class="bg-amber-100 text-amber-900 text-[10px] px-2 py-1 rounded font-bold">Cân bằng KK</span>
                                @else
                                    <span class="bg-neutral-100 text-neutral-700 text-[10px] px-2 py-1 rounded font-bold">Đầu kỳ</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 text-neutral-700">{{ $t->notes ?: '-' }}</td>
                            <td class="py-2 px-3 text-right font-semibold text-emerald-700 bg-emerald-50 bg-opacity-20">
                                {{ $t->in_qty > 0 ? (fmod($t->in_qty, 1) == 0 ? number_format($t->in_qty, 0, ',', '.') : number_format($t->in_qty, 2, ',', '.')) : '-' }}
                            </td>
                            <td class="py-2 px-3 text-right font-semibold text-rose-700 bg-rose-50 bg-opacity-20">
                                {{ $t->out_qty > 0 ? (fmod($t->out_qty, 1) == 0 ? number_format($t->out_qty, 0, ',', '.') : number_format($t->out_qty, 2, ',', '.')) : '-' }}
                            </td>
                            <td class="py-2 px-3 text-right font-black text-neutral-900 bg-primary-50 bg-opacity-20">
                                {{ fmod($t->stock_after, 1) == 0 ? number_format($t->stock_after, 0, ',', '.') : number_format($t->stock_after, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-neutral-400 font-medium">
                                Chưa có phát sinh giao dịch nào cho mặt hàng này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
