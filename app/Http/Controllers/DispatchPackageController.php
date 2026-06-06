<?php

namespace App\Http\Controllers;

use App\Models\AcrylicOrderItemCode;
use App\Models\GlassOrderItemCode;
use App\Models\MinLateOrderItemCode;
use App\Models\Order;
use App\Models\PackingPackage;
use App\Models\PackingPackageItem;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DispatchPackageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view dispatch')->only(['index', 'preview']);
        $this->middleware('permission:complete dispatch')->only(['confirm']);
    }

    public function index(Request $request)
    {
        $range = $request->input('range', 'all');
        $search = trim($request->input('search', ''));
        $perPage = (int) $request->input('per_page', 15);
        $perPage = in_array($perPage, [15, 25, 50, 100], true) ? $perPage : 15;

        $packages = PackingPackage::with(['packer', 'items.itemCode'])
            ->whereNotNull('dispatched_at')
            ->orderByDesc('dispatched_at')
            ->get();

        $historyRows = $this->buildHistoryRows($packages);
        $historyRows = $this->filterHistoryRows($historyRows, $range, $search);
        $historyRows = $this->paginateHistoryRows($historyRows, $perPage, $request);

        return view('dispatch.index', [
            'historyRows' => $historyRows,
            'todayDispatchCount' => $this->getTodayDispatchCount(),
            'range' => $range,
            'search' => $search,
            'perPage' => $perPage,
            'currentUser' => Auth::user(),
        ]);
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'nullable|integer',
            'product_code' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer',
            'page' => 'nullable|integer|min:1',
        ]);

        $package = null;

        if (! empty($validated['package_id'])) {
            $package = PackingPackage::with(['packer', 'items.itemCode'])->find((int) $validated['package_id']);
        } elseif (! empty(trim((string) ($validated['product_code'] ?? '')))) {
            $resolved = $this->resolveDispatchTarget(trim($validated['product_code']));
            $package = $resolved['package'] ?? null;
        }

        if (! $package) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm hoặc kiện tương ứng.',
            ], 404);
        }

        $package->load(['packer', 'items.itemCode']);
        $this->loadMorphCodeRelations($package->items->map(fn ($item) => $item->itemCode)->filter());

        $perPage = (int) $request->input('per_page', 15);
        $perPage = in_array($perPage, [15, 25, 50, 100], true) ? $perPage : 15;
        $page = max((int) $request->input('page', 1), 1);

        return response()->json([
            'success' => true,
            'message' => $package->dispatched_at
                ? 'Kiện này đã được xuất xưởng trước đó.'
                : 'Đã kiểm tra mã thành công.',
            'data' => $this->buildPreviewData($package, $perPage, $page),
        ]);
    }

    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'nullable|integer',
            'product_code' => 'nullable|string|max:255',
            'dispatched_note' => 'nullable|string|max:1000',
            'per_page' => 'nullable|integer',
            'page' => 'nullable|integer|min:1',
        ]);

        $package = null;
        $resolved = null;

        if (! empty($validated['package_id'])) {
            $package = PackingPackage::with(['packer', 'items.itemCode'])->find((int) $validated['package_id']);
        } elseif (! empty($validated['product_code'])) {
            $resolved = $this->resolveDispatchTarget(trim($validated['product_code']));
            $package = $resolved['package'] ?? null;
        }

        if (! $package) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy kiện tương ứng để xác nhận xuất xưởng.',
            ], 404);
        }

        /** @var \App\Models\PackingPackage $package */
        if ($package->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể xuất xưởng khi kiện đã hoàn tất đóng gói.',
            ], 422);
        }

        if ($package->dispatched_at) {
            return response()->json([
                'success' => false,
                'message' => 'Kiện này đã được xác nhận xuất xưởng trước đó.',
            ], 422);
        }

        $now = now();
        $package->update([
            'dispatched_at' => $now,
            'dispatched_note' => $validated['dispatched_note'] ?? null,
        ]);

        $package->load(['packer', 'items.itemCode']);
        $this->loadMorphCodeRelations($package->items->map(fn ($item) => $item->itemCode)->filter());

        $perPage = (int) ($validated['per_page'] ?? 15);
        $perPage = in_array($perPage, [15, 25, 50, 100], true) ? $perPage : 15;
        $page = max((int) ($validated['page'] ?? 1), 1);
        $historyRows = $this->buildHistoryRows(collect([$package]));

        return response()->json([
            'success' => true,
            'message' => 'Xác nhận xuất xưởng thành công.',
            'data' => [
                ...$this->buildPreviewData($package, $perPage, $page),
                'status_label' => 'Đã xuất',
                'status_detail' => 'Kiện đã được xác nhận xuất xưởng.',
                'can_confirm' => false,
                'confirmed_at' => $now->format('H:i:s d/m/Y'),
                'today_count' => $this->getTodayDispatchCount(),
                'rows' => $historyRows->values()->all(),
            ],
        ]);
    }

    private function resolveDispatchTarget(string $code): ?array
    {
        if ($code === '') {
            return null;
        }

        if (ctype_digit($code)) {
            $package = PackingPackage::with(['packer', 'items.itemCode'])->find((int) $code);

            if (! $package) {
                return null;
            }

            $this->loadMorphCodeRelations($package->items->map(fn ($item) => $item->itemCode)->filter());

            $firstItem = $package->items->where('is_packaged', true)->first() ?? $package->items->first();
            $firstItemData = $firstItem ? $this->formatDispatchItem($package, $firstItem) : null;

            return [
                'package' => $package,
                'row_product_code' => $firstItemData['product_code'] ?? (string) $package->id,
                'row_product_name' => $firstItemData['product_name'] ?? $package->name,
                'row_order_code' => $firstItemData['order_code'] ?? '—',
                'row_notes' => $firstItemData['notes'] ?? '—',
            ];
        }

        foreach ([
            [
                'class' => AcrylicOrderItemCode::class,
                'relation' => 'acrylicOrderItem.orderSupply.order',
            ],
            [
                'class' => GlassOrderItemCode::class,
                'relation' => 'glassOrderItem.orderSupply.order',
            ],
            [
                'class' => MinLateOrderItemCode::class,
                'relation' => 'minLateOrderItem.orderSupply.order',
            ],
        ] as $lookup) {
            $codeModel = $lookup['class']::with($lookup['relation'])
                ->where('product_id', $code)
                ->first();

            if (! $codeModel) {
                continue;
            }

            $packageItem = PackingPackageItem::with(['package.packer', 'itemCode'])
                ->where('item_code_type', $lookup['class'])
                ->where('item_code_id', $code)
                ->first();

            if (! $packageItem || ! $packageItem->package) {
                continue;
            }

            $package = $packageItem->package;
            $this->loadMorphCodeRelations(collect([$packageItem->itemCode])->filter());

            $itemData = $this->formatDispatchItem($package, $packageItem);

            return [
                'package' => $package,
                'row_product_code' => $itemData['product_code'],
                'row_product_name' => $itemData['product_name'],
                'row_order_code' => $itemData['order_code'],
                'row_notes' => $itemData['notes'],
            ];
        }

        return null;
    }

    private function buildHistoryRows(Collection $packages): Collection
    {
        $rows = collect();

        foreach ($packages as $package) {
            $package->loadMissing(['packer', 'items.itemCode']);
            $this->loadMorphCodeRelations($package->items->map(fn ($item) => $item->itemCode)->filter());

            // Lịch sử xuất xưởng hiển thị theo kiện, không tách thành từng linh kiện.
            $rows->push($this->formatDispatchRow($package));
        }

        return $rows->sortByDesc('time_raw')->values();
    }

    private function filterHistoryRows(Collection $rows, string $range, string $search): Collection
    {
        $filtered = $rows;

        if ($range === 'today') {
            $filtered = $filtered->filter(function ($row) {
                return $row['time_raw']?->isToday();
            });
        } elseif ($range === 'week') {
            $filtered = $filtered->filter(function ($row) {
                return $row['time_raw']?->greaterThanOrEqualTo(now()->subDays(7)->startOfDay());
            });
        }

        if ($search !== '') {
            $keyword = mb_strtolower($search);

            $filtered = $filtered->filter(function ($row) use ($keyword) {
                $haystack = mb_strtolower(implode(' ', [
                    $row['package_code'] ?? ($row['product_code'] ?? ''),
                    $row['package_name'] ?? ($row['product_name'] ?? ''),
                    $row['order_code'] ?? '',
                    $row['notes'] ?? '',
                    $row['operator'] ?? '',
                ]));

                return str_contains($haystack, $keyword);
            });
        }

        return $filtered->values();
    }

    private function paginateHistoryRows(Collection $rows, int $perPage, Request $request): LengthAwarePaginator
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $total = $rows->count();
        $lastPage = max((int) ceil($total / $perPage), 1);
        $currentPage = max(1, min($currentPage, $lastPage));
        $items = $rows->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator($items, $total, $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }

    private function formatDispatchRow(PackingPackage $package): array
    {
        $effectiveItems = $package->items->where('is_packaged', true);
        if ($effectiveItems->isEmpty()) {
            $effectiveItems = $package->items;
        }

        $packageOrder = $this->resolvePackageOrder($effectiveItems);
        $packageCode = (string) $package->id;

        return [
            'package_id' => $package->id,
            'package_code' => $packageCode,
            'package_name' => $package->name,
            'product_code' => $packageCode,
            'product_name' => $package->name,
            'order_code' => $packageOrder?->order_code ?? '—',
            'notes' => filled($package->dispatched_note) ? $package->dispatched_note : '—',
            'operator' => $package->packer?->name ?? '—',
            'time_raw' => $package->dispatched_at,
            'time' => $package->dispatched_at?->format('H:i:s d/m/Y') ?? '—',
            'status_label' => 'Đã xuất',
            'view_url' => route('processes.packing.show', $package),
        ];
    }

    private function buildPreviewData(PackingPackage $package, int $perPage = 15, int $page = 1): array
    {
        $package->loadMissing(['packer', 'items.itemCode']);
        $this->loadMorphCodeRelations($package->items->map(fn ($item) => $item->itemCode)->filter());

        $effectiveItems = $package->items->where('is_packaged', true);
        if ($effectiveItems->isEmpty()) {
            $effectiveItems = $package->items;
        }

        $packageOrder = $this->resolvePackageOrder($effectiveItems);
        $packageCode = (string) $package->id;
        $orderCode = $packageOrder?->order_code ?? '—';
        $customerName = $packageOrder?->customer_name ?? '—';
        $customerPhone = $packageOrder?->phone ?? '—';
        $deliveryAddress = $packageOrder?->address ?? '—';
        $totalItems = $effectiveItems->count();
        $canConfirm = $package->status === 'completed' && ! $package->dispatched_at;
        $paginatedItems = $this->paginatePackageItems($effectiveItems, $perPage, $page);
        $itemRows = $paginatedItems->getCollection()
            ->map(fn (PackingPackageItem $packageItem) => $this->formatDispatchPackageItem($packageItem))
            ->values()
            ->all();
        $sizeMeta = $paginatedItems->getCollection()->isNotEmpty()
            ? $this->resolvePackageItemSizeMeta($paginatedItems->getCollection()->first())
            : $this->resolvePackageItemSizeMeta(null);

        return [
            'package_id' => $package->id,
            'package_code' => $packageCode,
            'package_name' => $package->name,
            'dispatched_note' => $package->dispatched_note ?? '',
            'status_label' => $package->dispatched_at
                ? 'Đã xuất'
                : ($package->status === 'completed' ? 'Đã hoàn tất đóng gói' : 'Đang đóng gói'),
            'status_detail' => $package->dispatched_at
                ? 'Kiện này đã được xác nhận xuất xưởng.'
                : ($package->status === 'completed'
                    ? 'Kiện đã đóng gói hoàn tất, sẵn sàng xác nhận xuất xưởng.'
                    : 'Kiện đang đóng gói, chỉ xem thông tin và chưa thể xác nhận xuất xưởng.'),
            'can_confirm' => $canConfirm,
            'order_info' => [
                ['label' => 'Mã đơn', 'value' => $orderCode],
                ['label' => 'Tên khách hàng', 'value' => $customerName],
                ['label' => 'Số điện thoại', 'value' => $customerPhone],
                ['label' => 'Địa chỉ giao hàng', 'value' => $deliveryAddress],
            ],
            'package_info' => [
                ['label' => 'Mã kiện', 'value' => $packageCode],
                ['label' => 'Người đóng gói', 'value' => $package->packer?->name ?? '—'],
                ['label' => 'Tổng SL linh kiện', 'value' => (string) $totalItems],
                ['label' => 'Trạng thái kiện', 'value' => $package->dispatched_at ? 'Đã xuất xưởng' : 'Sẵn sàng xuất xưởng'],
            ],
            'package_items' => $itemRows,
            'package_items_meta' => [
                'current_page' => $paginatedItems->currentPage(),
                'last_page' => $paginatedItems->lastPage(),
                'per_page' => $paginatedItems->perPage(),
                'total' => $paginatedItems->total(),
                'first_item' => $paginatedItems->firstItem() ?? 0,
                'last_item' => $paginatedItems->lastItem() ?? 0,
                'size_meta' => $sizeMeta,
            ],
        ];
    }

    private function paginatePackageItems(Collection $items, int $perPage, int $page): LengthAwarePaginator
    {
        $perPage = in_array($perPage, [15, 25, 50, 100], true) ? $perPage : 15;
        $page = max($page, 1);
        $total = $items->count();
        $lastPage = max((int) ceil($total / $perPage), 1);
        $page = min($page, $lastPage);
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator($slice, $total, $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    private function resolvePackageOrder(Collection $packageItems): ?Order
    {
        foreach ($packageItems as $packageItem) {
            $code = $packageItem->itemCode;

            if ($code instanceof AcrylicOrderItemCode) {
                return $code->acrylicOrderItem?->orderSupply?->order;
            }

            if ($code instanceof GlassOrderItemCode) {
                return $code->glassOrderItem?->orderSupply?->order;
            }

            if ($code instanceof MinLateOrderItemCode) {
                return $code->minLateOrderItem?->orderSupply?->order;
            }
        }

        return null;
    }

    private function formatDispatchItem(PackingPackage $package, PackingPackageItem $packageItem): array
    {
        $code = $packageItem->itemCode;
        $productName = '—';
        $orderCode = '—';
        $notes = '—';

        if ($code instanceof AcrylicOrderItemCode) {
            $item = $code->acrylicOrderItem;
            $productName = $item?->product_name ?? '—';
            $orderCode = $item?->orderSupply?->order?->order_code ?? '—';
            $notes = $item?->notes ?? '—';
        } elseif ($code instanceof GlassOrderItemCode) {
            $item = $code->glassOrderItem;
            $productName = $item?->product_name ?? '—';
            $orderCode = $item?->orderSupply?->order?->order_code ?? '—';
            $notes = $item?->notes ?? '—';
        } elseif ($code instanceof MinLateOrderItemCode) {
            $item = $code->minLateOrderItem;
            $productName = $item?->product_name ?? $item?->name ?? '—';
            $orderCode = $item?->orderSupply?->order?->order_code ?? '—';
            $notes = $item?->notes ?? '—';
        }

        return [
            'package_id' => $package->id,
            'package_name' => $package->name,
            'product_code' => $code?->product_id ?? '—',
            'product_name' => $productName,
            'order_code' => $orderCode,
            'notes' => $notes,
        ];
    }

    private function formatDispatchPackageItem(PackingPackageItem $packageItem): array
    {
        $code = $packageItem->itemCode;
        $productName = '—';
        $type = '—';
        $sizeLabels = $this->resolvePackageItemSizeMeta($packageItem);
        [$sizeValue1, $sizeValue2] = $this->resolvePackageItemSizeValues($packageItem);

        if ($code instanceof AcrylicOrderItemCode) {
            $item = $code->acrylicOrderItem;
            $productName = $item?->product_name ?? '—';
            $type = 'Acrylic';
        } elseif ($code instanceof GlassOrderItemCode) {
            $item = $code->glassOrderItem;
            $productName = $item?->product_name ?? '—';
            $type = 'Glass';
        } elseif ($code instanceof MinLateOrderItemCode) {
            $item = $code->minLateOrderItem;
            $productName = $item?->product_name ?? $item?->name ?? '—';
            $type = 'Min-late';
        }

        return [
            'product_code' => $code?->product_id ?? '—',
            'product_name' => $productName,
            'type' => $type,
            'size_title' => $sizeLabels['title'],
            'size_label_1' => $sizeLabels['label_1'],
            'size_label_2' => $sizeLabels['label_2'],
            'size_value_1' => $sizeValue1,
            'size_value_2' => $sizeValue2,
            'created_at_label' => $packageItem->created_at?->format('H:i:s d/m/Y'),
        ];
    }

    private function resolvePackageItemSizeMeta(?PackingPackageItem $packageItem): array
    {
        $type = $packageItem?->itemCode;

        if ($type instanceof GlassOrderItemCode) {
            return [
                'title' => 'KÍCH THƯỚC CÁNH (MM)',
                'label_1' => 'DÀI (MM)',
                'label_2' => 'RỘNG (MM)',
            ];
        }

        if ($type instanceof MinLateOrderItemCode) {
            return [
                'title' => 'KÍCH THƯỚC (MM)',
                'label_1' => 'CAO (VÁN)',
                'label_2' => 'RỘNG',
            ];
        }

        return [
            'title' => 'KÍCH THƯỚC (MM)',
            'label_1' => 'CAO (CHIỀU VÁN)',
            'label_2' => 'RỘNG',
        ];
    }

    private function resolvePackageItemSizeValues(PackingPackageItem $packageItem): array
    {
        $code = $packageItem->itemCode;
        $height = '—';
        $width = '—';

        if ($code instanceof AcrylicOrderItemCode) {
            $item = $code->acrylicOrderItem;
            $height = filled($item?->height) ? (string) $item->height : '—';
            $width = filled($item?->width) ? (string) $item->width : '—';
        } elseif ($code instanceof GlassOrderItemCode) {
            $item = $code->glassOrderItem;
            $height = filled($item?->height) ? (string) $item->height : '—';
            $width = filled($item?->width) ? (string) $item->width : '—';
        } elseif ($code instanceof MinLateOrderItemCode) {
            $item = $code->minLateOrderItem;
            $size = $item?->size ?? [];

            if (is_string($size)) {
                $size = json_decode($size, true) ?? [];
            }

            $height = filled($size['height'] ?? null) ? (string) $size['height'] : '—';
            $width = filled($size['width'] ?? null) ? (string) $size['width'] : '—';
        }

        return [$height, $width];
    }

    private function getTodayDispatchCount(): int
    {
        $packages = PackingPackage::with(['packer', 'items.itemCode'])
            ->whereNotNull('dispatched_at')
            ->whereDate('dispatched_at', today())
            ->orderByDesc('dispatched_at')
            ->get();

        return $this->buildHistoryRows($packages)->count();
    }

    private function loadMorphCodeRelations(Collection $codes): void
    {
        if ($codes->isEmpty()) {
            return;
        }

        $acrylicCodes = $codes->filter(fn ($code) => $code instanceof AcrylicOrderItemCode);
        if ($acrylicCodes->isNotEmpty()) {
            \Illuminate\Database\Eloquent\Collection::make($acrylicCodes)->load('acrylicOrderItem.orderSupply.order');
        }

        $glassCodes = $codes->filter(fn ($code) => $code instanceof GlassOrderItemCode);
        if ($glassCodes->isNotEmpty()) {
            \Illuminate\Database\Eloquent\Collection::make($glassCodes)->load('glassOrderItem.orderSupply.order');
        }

        $minLateCodes = $codes->filter(fn ($code) => $code instanceof MinLateOrderItemCode);
        if ($minLateCodes->isNotEmpty()) {
            \Illuminate\Database\Eloquent\Collection::make($minLateCodes)->load('minLateOrderItem.orderSupply.order');
        }
    }
}
