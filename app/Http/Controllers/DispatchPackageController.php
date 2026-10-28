<?php

namespace App\Http\Controllers;

use App\Models\AcrylicOrderItemCode;
use App\Models\GlassOrderItemCode;
use App\Models\MinLateOrderItemCode;
use App\Models\Order;
use App\Models\PackingPackage;
use App\Models\PackingPackageItem;
use App\Models\Vehicle;
use App\Traits\HasDateFilter;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DispatchPackageController extends Controller
{
    use HasDateFilter;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->can('view dispatch') && !auth()->user()->can('view delivery')) {
                abort(403, 'Bạn không có quyền truy cập chức năng này.');
            }
            return $next($request);
        })->only(['index', 'preview']);

        $this->middleware('permission:complete dispatch')->only(['confirm', 'bulkConfirm']);
    }

    public function index(Request $request)
    {
        $range = $request->input('range', 'all');
        $search = trim($request->input('search', ''));
        $perPage = (int) $request->input('per_page', 15);
        $perPage = in_array($perPage, [15, 25, 50, 100], true) ? $perPage : 15;
        $activeMode = $request->input('mode', $request->is('*delivery*') ? 'delivery' : 'dispatch');

        // 1. Lấy danh sách lịch sử theo chế độ
        if ($activeMode === 'delivery') {
            $query = PackingPackage::with(['packer', 'vehicle', 'items.itemCode'])
                ->whereNotNull('delivered_at')
                ->orderByDesc('delivered_at');
            $this->applyDateFilter($query, $request, 'delivered_at');
        } else {
            $query = PackingPackage::with(['packer', 'vehicle', 'items.itemCode'])
                ->whereNotNull('dispatched_at')
                ->orderByDesc('dispatched_at');
            $this->applyDateFilter($query, $request, 'dispatched_at');
        }

        $packages = $query->get();
        $historyRows = $this->buildHistoryRows($packages);
        $historyRows = $this->filterHistoryRows($historyRows, $range, $search);
        $historyRows = $this->paginateHistoryRows($historyRows, $perPage, $request);

        // 2. Kiện ĐANG CHỜ XUẤT XƯỞNG (Đã đóng gói xong, chưa xuất)
        $pendingDispatchPackages = PackingPackage::with(['packer', 'items.itemCode'])
            ->where('status', 'completed')
            ->whereNull('dispatched_at')
            ->orderByDesc('id')
            ->get();
        $pendingDispatchRows = $this->buildPendingRows($pendingDispatchPackages);

        // 3. Kiện ĐANG ĐI GIAO (Đã xuất xưởng, chưa giao xong)
        $pendingDeliveryPackages = PackingPackage::with(['packer', 'vehicle', 'items.itemCode'])
            ->where('status', 'completed')
            ->whereNotNull('dispatched_at')
            ->whereNull('delivered_at')
            ->orderByDesc('dispatched_at')
            ->get();
        $pendingDeliveryRows = $this->buildDeliveryPendingRows($pendingDeliveryPackages);

        // 4. Danh sách xe giao hàng khả dụng
        $vehicles = Vehicle::where('status', 'active')->orderBy('name')->get();

        // 5. Thống kê hôm nay
        $todayDispatchCount = $this->getTodayDispatchCount();
        $todayDeliveryCount = PackingPackage::whereNotNull('delivered_at')->whereDate('delivered_at', today())->count();

        return view('dispatch.index', [
            'historyRows'            => $historyRows,
            'pendingDispatchRows'    => $pendingDispatchRows,
            'pendingDispatchCount'   => $pendingDispatchPackages->count(),
            'pendingDeliveryRows'    => $pendingDeliveryRows,
            'pendingDeliveryCount'   => $pendingDeliveryPackages->count(),
            'vehicles'               => $vehicles,
            'todayDispatchCount'     => $todayDispatchCount,
            'todayDeliveryCount'     => $todayDeliveryCount,
            'activeMode'             => $activeMode,
            'range'                  => $range,
            'search'                 => $search,
            'perPage'                => $perPage,
            'currentUser'            => Auth::user(),
        ]);
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'package_id'      => 'nullable|integer',
            'product_code'    => 'nullable|string|max:255',
            'per_page'        => 'nullable|integer',
            'page'            => 'nullable|integer|min:1',
            'auto_confirm'    => 'nullable|boolean',
            'dispatched_note' => 'nullable|string|max:1000',
            'vehicle_id'      => 'nullable|integer|exists:vehicles,id',
        ]);

        $package = null;

        if (! empty($validated['package_id'])) {
            $package = PackingPackage::with(['packer', 'vehicle', 'items.itemCode'])->find((int) $validated['package_id']);
        } elseif (! empty(trim((string) ($validated['product_code'] ?? '')))) {
            $resolved = $this->resolveDispatchTarget(trim($validated['product_code']));
            $package = $resolved['package'] ?? null;
        }

        if (! $package) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy kiện hoặc sản phẩm tương ứng với mã vừa quét.',
            ], 404);
        }

        $package->load(['packer', 'vehicle', 'items.itemCode']);
        $this->loadMorphCodeRelations($package->items->map(fn ($item) => $item->itemCode)->filter());

        $perPage = (int) $request->input('per_page', 15);
        $perPage = in_array($perPage, [15, 25, 50, 100], true) ? $perPage : 15;
        $page = max((int) $request->input('page', 1), 1);

        $previewData = $this->buildPreviewData($package, $perPage, $page);

        $canConfirm = $package->status === 'completed' && ! $package->dispatched_at;

        if (! empty($validated['auto_confirm']) && $canConfirm) {
            return $this->confirm(new Request([
                'package_id'      => $package->id,
                'dispatched_note' => $validated['dispatched_note'] ?? null,
                'vehicle_id'      => $validated['vehicle_id'] ?? null,
                'per_page'        => $perPage,
                'page'            => $page,
            ]));
        }

        $statusLabel = 'Chưa xuất';
        $statusDetail = 'Kiện đang chờ xuất xưởng.';

        if ($package->status !== 'completed') {
            $statusLabel = 'Chưa hoàn tất';
            $statusDetail = 'Kiện chưa hoàn tất đóng gói, không thể xuất xưởng.';
        } elseif ($package->dispatched_at) {
            $statusLabel = 'Đã xuất';
            $statusDetail = 'Kiện đã được xác nhận xuất xưởng trước đó.';
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã tìm thấy thông tin kiện.',
            'data' => [
                ...$previewData,
                'status_label' => $statusLabel,
                'status_detail' => $statusDetail,
                'can_confirm' => $canConfirm,
                'confirmed_at' => $package->dispatched_at?->format('H:i:s d/m/Y'),
                'today_count' => $this->getTodayDispatchCount(),
            ],
        ]);
    }

    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'package_id'      => 'nullable|integer',
            'product_code'    => 'nullable|string|max:255',
            'dispatched_note' => 'nullable|string|max:1000',
            'vehicle_id'      => 'nullable|integer|exists:vehicles,id',
            'per_page'        => 'nullable|integer',
            'page'            => 'nullable|integer|min:1',
        ]);

        $package = null;
        $resolved = null;

        if (! empty($validated['package_id'])) {
            $package = PackingPackage::with(['packer', 'vehicle', 'items.itemCode'])->find((int) $validated['package_id']);
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
            'dispatched_at'   => $now,
            'dispatched_note' => $validated['dispatched_note'] ?? null,
            'vehicle_id'      => $validated['vehicle_id'] ?? null,
        ]);

        $package->load(['packer', 'vehicle', 'items.itemCode']);
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

    public function bulkConfirm(Request $request)
    {
        $validated = $request->validate([
            'package_ids'     => 'required|array',
            'package_ids.*'   => 'integer|exists:packing_packages,id',
            'vehicle_id'      => 'nullable|integer|exists:vehicles,id',
            'dispatched_note' => 'nullable|string|max:1000',
        ]);

        $packages = PackingPackage::whereIn('id', $validated['package_ids'])
            ->where('status', 'completed')
            ->whereNull('dispatched_at')
            ->get();

        if ($packages->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có kiện hàng hợp lệ nào để xuất xưởng.',
            ], 422);
        }

        $now = now();
        $count = 0;
        foreach ($packages as $pkg) {
            $pkg->update([
                'dispatched_at'   => $now,
                'dispatched_note' => $validated['dispatched_note'] ?? null,
                'vehicle_id'      => $validated['vehicle_id'] ?? null,
            ]);
            $count++;
        }

        return response()->json([
            'success'          => true,
            'message'          => "Đã xác nhận xuất xưởng thành công cho {$count} kiện hàng!",
            'dispatched_count' => $count,
            'today_count'      => $this->getTodayDispatchCount(),
        ]);
    }

    private function resolveDispatchTarget(string $code): ?array
    {
        $code = trim($code);
        if ($code === '') {
            return null;
        }

        if (str_contains($code, '_') && ! preg_match('/^PK[-_]?\d+$/i', $code)) {
            $code = explode('_', $code)[0];
        }

        // 1. Kiểm tra ID hoặc mã dạng PK1, PK001
        $idCandidate = $code;
        if (preg_match('/^PK[-_]?(\d+)$/i', $code, $m)) {
            $idCandidate = $m[1];
        }

        if (ctype_digit((string) $idCandidate)) {
            $package = PackingPackage::with(['packer', 'vehicle', 'items.itemCode'])->find((int) $idCandidate);
            if ($package) {
                return $this->formatResolvedPackage($package);
            }
        }

        // 2. Tìm theo tên kiện (name)
        $package = PackingPackage::with(['packer', 'vehicle', 'items.itemCode'])->where('name', $code)->first();
        if ($package) {
            return $this->formatResolvedPackage($package);
        }

        // 3. Tìm theo mã sản phẩm
        $matchedItem = $this->findItemCode($code);
        if (! $matchedItem) {
            return null;
        }

        $packageItem = PackingPackageItem::with(['package.packer', 'package.vehicle', 'package.items.itemCode'])
            ->where('item_code_type', $matchedItem['class'])
            ->where('item_code_id', $matchedItem['code']->id)
            ->where('is_packaged', true)
            ->first();

        if (! $packageItem || ! $packageItem->package) {
            return null;
        }

        return $this->formatResolvedPackage($packageItem->package);
    }

    private function formatResolvedPackage(PackingPackage $package): array
    {
        return [
            'package' => $package,
        ];
    }

    private function findItemCode(string $code): ?array
    {
        foreach ([
            ['class' => AcrylicOrderItemCode::class, 'relation' => 'acrylicOrderItem.orderSupply.order'],
            ['class' => GlassOrderItemCode::class, 'relation' => 'glassOrderItem.orderSupply.order'],
            ['class' => MinLateOrderItemCode::class, 'relation' => 'minLateOrderItem.orderSupply.order'],
        ] as $lookup) {
            $record = $lookup['class']::with($lookup['relation'])
                ->where('product_id', $code)
                ->first();

            if ($record) {
                return [
                    'class' => $lookup['class'],
                    'code'  => $record,
                ];
            }
        }

        return null;
    }

    private function buildPendingRows(Collection $packages): Collection
    {
        return $packages->map(function (PackingPackage $package) {
            $effectiveItems = $package->items->where('is_packaged', true);
            if ($effectiveItems->isEmpty()) {
                $effectiveItems = $package->items;
            }
            $packageOrder = $this->resolvePackageOrder($effectiveItems);

            return [
                'package_id'       => $package->id,
                'package_code'     => $package->name ?: (string) $package->id,
                'package_name'     => $package->name,
                'order_code'       => $packageOrder?->order_code ?? '-',
                'customer_name'    => $packageOrder?->customer_name ?? '-',
                'customer_phone'   => $packageOrder?->phone ?? '-',
                'delivery_address' => $packageOrder?->address ?? '-',
                'total_items'      => $effectiveItems->count(),
                'operator'         => $package->packer?->name ?? '-',
                'packed_time'      => $package->updated_at?->format('H:i d/m/Y') ?? '-',
                'view_url'         => route('processes.packing.show', $package),
            ];
        });
    }

    private function buildDeliveryPendingRows(Collection $packages): Collection
    {
        return $packages->map(function (PackingPackage $package) {
            $effectiveItems = $package->items->where('is_packaged', true);
            if ($effectiveItems->isEmpty()) {
                $effectiveItems = $package->items;
            }
            $packageOrder = $this->resolvePackageOrder($effectiveItems);

            return [
                'package_id'       => $package->id,
                'package_code'     => $package->name ?: (string) $package->id,
                'package_name'     => $package->name,
                'order_code'       => $packageOrder?->order_code ?? '-',
                'customer_name'    => $packageOrder?->customer_name ?? '-',
                'customer_phone'   => $packageOrder?->phone ?? '-',
                'delivery_address' => $packageOrder?->address ?? '-',
                'total_items'      => $effectiveItems->count(),
                'operator'         => $package->packer?->name ?? '-',
                'vehicle_info'     => $package->vehicle ? ($package->vehicle->plate_number . ' - ' . $package->vehicle->name) : 'Chưa gán xe',
                'dispatched_time'  => $package->dispatched_at?->format('H:i d/m/Y') ?? '-',
                'view_url'         => route('processes.packing.show', $package),
            ];
        });
    }

    private function buildHistoryRows(Collection $packages): Collection
    {
        return $packages->map(function (PackingPackage $package) {
            return $this->formatDispatchRow($package);
        });
    }

    private function filterHistoryRows(Collection $rows, string $range, string $search): Collection
    {
        if ($search === '') {
            return $rows;
        }

        $needle = mb_strtolower($search);

        return $rows->filter(function (array $row) use ($needle) {
            $haystack = mb_strtolower(implode(' ', [
                $row['package_code'] ?? ($row['product_code'] ?? ''),
                $row['package_name'] ?? ($row['product_name'] ?? ''),
                $row['order_code'] ?? '',
                $row['vehicle_plate'] ?? '',
                $row['notes'] ?? '',
                $row['customer_name'] ?? '',
            ]));

            return str_contains($haystack, $needle);
        });
    }

    private function paginateHistoryRows(Collection $rows, int $perPage, Request $request): LengthAwarePaginator
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $total = $rows->count();
        $lastPage = max((int) ceil($total / $perPage), 1);
        $currentPage = max(1, min($currentPage, $lastPage));

        $items = $rows->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    private function formatDispatchRow(PackingPackage $package): array
    {
        $effectiveItems = $package->items->where('is_packaged', true);
        if ($effectiveItems->isEmpty()) {
            $effectiveItems = $package->items;
        }

        $packageOrder = $this->resolvePackageOrder($effectiveItems);

        return [
            'package_id'       => $package->id,
            'package_code'     => $package->name ?: (string) $package->id,
            'package_name'     => $package->name,
            'order_code'       => $packageOrder?->order_code ?? '-',
            'customer_name'    => $packageOrder?->customer_name ?? '-',
            'delivery_address' => $packageOrder?->address ?? '-',
            'total_items'      => $effectiveItems->count(),
            'vehicle_plate'    => $package->vehicle?->plate_number ?? '-',
            'vehicle_driver'   => $package->vehicle?->driver_name ?? '-',
            'operator'         => $package->packer?->name ?? '-',
            'dispatched_time'  => $package->dispatched_at?->format('H:i:s d/m/Y') ?? '-',
            'delivered_time'   => $package->delivered_at?->format('H:i:s d/m/Y') ?? '-',
            'notes'            => $package->dispatched_note ?? ($package->delivered_note ?? '-'),
            'view_url'         => route('processes.packing.show', $package),
        ];
    }

    private function buildPreviewData(PackingPackage $package, int $perPage = 15, int $page = 1): array
    {
        $package->loadMissing(['packer', 'vehicle', 'items.itemCode']);
        $this->loadMorphCodeRelations($package->items->map(fn ($item) => $item->itemCode)->filter());

        $effectiveItems = $package->items->where('is_packaged', true);
        if ($effectiveItems->isEmpty()) {
            $effectiveItems = $package->items;
        }

        $packageOrder = $this->resolvePackageOrder($effectiveItems);

        $formattedItems = $effectiveItems->values()->map(function (PackingPackageItem $item) {
            return $this->formatDispatchPackageItem($item);
        });

        $paginator = $this->paginatePackageItems($formattedItems, $perPage, $page);

        return [
            'package_id'       => $package->id,
            'package_code'     => $package->name ?: (string) $package->id,
            'package_name'     => $package->name,
            'order_code'       => $packageOrder?->order_code ?? '-',
            'customer_name'    => $packageOrder?->customer_name ?? '-',
            'customer_phone'   => $packageOrder?->phone ?? '-',
            'delivery_address' => $packageOrder?->address ?? '-',
            'total_items'      => $effectiveItems->count(),
            'operator'         => $package->packer?->name ?? '-',
            'packed_time'      => $package->updated_at?->format('H:i d/m/Y') ?? '-',
            'dispatched_time'  => $package->dispatched_at?->format('H:i d/m/Y'),
            'delivered_time'   => $package->delivered_at?->format('H:i d/m/Y'),
            'vehicle_id'       => $package->vehicle_id,
            'vehicle_plate'    => $package->vehicle?->plate_number,
            'vehicle_name'     => $package->vehicle?->name,
            'vehicle_driver'   => $package->vehicle?->driver_name,
            'notes'            => $package->dispatched_note,
            'delivered_note'   => $package->delivered_note,
            'items'            => $paginator->items(),
            'pagination'       => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ];
    }

    private function paginatePackageItems(Collection $items, int $perPage, int $page): LengthAwarePaginator
    {
        $perPage = in_array($perPage, [15, 25, 50, 100], true) ? $perPage : 15;
        $page = max($page, 1);
        $total = $items->count();
        $lastPage = max((int) ceil($total / $perPage), 1);
        $currentPage = min($page, $lastPage);

        $slice = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator($slice, $total, $perPage, $currentPage);
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

    private function formatDispatchPackageItem(PackingPackageItem $packageItem): array
    {
        $code = $packageItem->itemCode;
        $productName = '-';
        $type = '-';
        $sizeLabels = $this->resolvePackageItemSizeMeta($packageItem);
        $sizeValues = $this->resolvePackageItemSizeValues($packageItem);

        if ($code instanceof AcrylicOrderItemCode) {
            $item = $code->acrylicOrderItem;
            $productName = $item?->product_name ?? '-';
            $type = 'Acrylic';
        } elseif ($code instanceof GlassOrderItemCode) {
            $item = $code->glassOrderItem;
            $productName = $item?->product_name ?? '-';
            $type = 'Glass';
        } elseif ($code instanceof MinLateOrderItemCode) {
            $item = $code->minLateOrderItem;
            $productName = $item?->name ?? '-';
            $type = 'Min-late';
        }

        return [
            'id'             => $packageItem->id,
            'product_code'   => $code?->product_id ?? '-',
            'product_name'   => $productName,
            'type'           => $type,
            'height_label'   => $sizeLabels['height_label'],
            'width_label'    => $sizeLabels['width_label'],
            'height'         => $sizeValues['height'],
            'width'          => $sizeValues['width'],
            'is_packaged'    => $packageItem->is_packaged,
            'packed_at'      => $packageItem->updated_at?->format('H:i d/m/Y') ?? '-',
        ];
    }

    private function resolvePackageItemSizeMeta(?PackingPackageItem $packageItem): array
    {
        $type = $packageItem?->itemCode;

        if ($type instanceof GlassOrderItemCode) {
            return [
                'height_label' => 'Cao cánh',
                'width_label'  => 'Rộng cánh',
            ];
        }

        if ($type instanceof MinLateOrderItemCode) {
            return [
                'height_label' => 'Dài',
                'width_label'  => 'Rộng',
            ];
        }

        return [
            'height_label' => 'Chiều cao',
            'width_label'  => 'Chiều rộng',
        ];
    }

    private function resolvePackageItemSizeValues(PackingPackageItem $packageItem): array
    {
        $code = $packageItem->itemCode;
        $height = '-';
        $width = '-';

        if ($code instanceof AcrylicOrderItemCode) {
            $item = $code->acrylicOrderItem;
            $height = $item?->height ?? '-';
            $width = $item?->width ?? '-';
        } elseif ($code instanceof GlassOrderItemCode) {
            $item = $code->glassOrderItem;
            $height = $item?->wing_height ?? '-';
            $width = $item?->wing_width ?? '-';
        } elseif ($code instanceof MinLateOrderItemCode) {
            $item = $code->minLateOrderItem;
            $height = $item?->length ?? '-';
            $width = $item?->width ?? '-';
        }

        return [
            'height' => $height,
            'width'  => $width,
        ];
    }

    private function getTodayDispatchCount(): int
    {
        return PackingPackage::whereNotNull('dispatched_at')
            ->whereDate('dispatched_at', today())
            ->count();
    }

    private function loadMorphCodeRelations(Collection $codes): void
    {
        if ($codes->isEmpty()) {
            return;
        }

        $codes->loadMorph('acrylicOrderItem', [
            AcrylicOrderItemCode::class => ['acrylicOrderItem.orderSupply.order'],
        ]);
        $codes->loadMorph('glassOrderItem', [
            GlassOrderItemCode::class => ['glassOrderItem.orderSupply.order'],
        ]);
        $codes->loadMorph('minLateOrderItem', [
            MinLateOrderItemCode::class => ['minLateOrderItem.orderSupply.order'],
        ]);
    }
}