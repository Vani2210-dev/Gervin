<?php

namespace App\Http\Controllers;

use App\Models\AcrylicOrderItemCode;
use App\Models\GlassOrderItemCode;
use App\Models\MinLateOrderItemCode;
use App\Models\Order;
use App\Models\PackingPackage;
use App\Models\PackingPackageItem;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DeliveryPackageController extends Controller
{
    public function __construct()
    {
        // Kiểm tra phân quyền truy cập cho trang giao hàng
        $this->middleware('permission:view delivery')->only(['index', 'preview']);
        $this->middleware('permission:complete delivery')->only(['confirm', 'bulkConfirm']);
    }

    /**
     * Hiển thị danh sách lịch sử giao hàng và kiện chờ giao
     */
    public function index(Request $request)
    {
        $range = $request->input('range', 'all');
        $search = trim($request->input('search', ''));
        $perPage = (int) $request->input('per_page', 15);
        $perPage = in_array($perPage, [15, 25, 50, 100], true) ? $perPage : 15;

        // 1. Lấy danh sách lịch sử giao hàng thành công
        $query = PackingPackage::with(['packer', 'vehicle', 'items.itemCode'])
            ->whereNotNull('delivered_at')
            ->orderByDesc('delivered_at');

        $this->applyDateFilter($query, $request, 'delivered_at');

        $packages = $query->get();

        $historyRows = $this->buildHistoryRows($packages);
        $historyRows = $this->filterHistoryRows($historyRows, $range, $search);
        $historyRows = $this->paginateHistoryRows($historyRows, $perPage, $request);

        // 2. Danh sách kiện ĐANG CHỜ GIAO HÀNG (đã xuất xưởng nhưng chưa giao xong)
        $pendingPackages = PackingPackage::with(['packer', 'vehicle', 'items.itemCode'])
            ->where('status', 'completed')
            ->whereNotNull('dispatched_at')
            ->whereNull('delivered_at')
            ->orderByDesc('dispatched_at')
            ->get();
        $pendingRows = $this->buildPendingRows($pendingPackages);

        // 3. Danh sách xe giao hàng
        $vehicles = Vehicle::where('status', 'active')->orderBy('name')->get();

        return view('delivery.index', [
            'historyRows'        => $historyRows,
            'pendingRows'        => $pendingRows,
            'pendingCount'       => $pendingPackages->count(),
            'vehicles'           => $vehicles,
            'todayDeliveryCount' => $this->getTodayDeliveryCount(),
            'range'              => $range,
            'search'             => $search,
            'perPage'            => $perPage,
            'currentUser'        => Auth::user(),
        ]);
    }

    /**
     * Xem thông tin kiểm tra trước khi xác nhận giao hàng
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'package_id'     => 'nullable|integer',
            'product_code'   => 'nullable|string|max:255',
            'per_page'       => 'nullable|integer',
            'page'           => 'nullable|integer|min:1',
            'auto_confirm'   => 'nullable|boolean',
            'delivered_note' => 'nullable|string|max:1000',
        ]);

        $package = null;

        if (! empty($validated['package_id'])) {
            $package = PackingPackage::with(['packer', 'vehicle', 'items.itemCode'])->find((int) $validated['package_id']);
        } elseif (! empty(trim((string) ($validated['product_code'] ?? '')))) {
            $resolved = $this->resolveDeliveryTarget(trim($validated['product_code']));
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

        // Tự động xác nhận giao hàng nếu chế độ Fast Scan (auto_confirm) đang bật
        $autoConfirm = $request->boolean('auto_confirm');
        if ($autoConfirm && $package->status === 'completed' && $package->dispatched_at && ! $package->delivered_at) {
            $now = now();
            $package->update([
                'delivered_at'   => $now,
                'delivered_note' => $validated['delivered_note'] ?? null,
            ]);

            // Tự động kiểm tra và hoàn tất đơn hàng nếu tất cả các kiện đã giao
            $this->checkAndCompleteOrder($package);

            $package->load(['packer', 'vehicle', 'items.itemCode']);
            $this->loadMorphCodeRelations($package->items->map(fn ($item) => $item->itemCode)->filter());
            $historyRows = $this->buildHistoryRows(collect([$package]));

            return response()->json([
                'success'        => true,
                'auto_confirmed' => true,
                'message'        => "Đã tự động xác nhận giao hàng kiện {$package->name} thành công!",
                'data'           => [
                    ...$this->buildPreviewData($package, $perPage, $page),
                    'status_label'   => 'Đã giao',
                    'status_detail'  => 'Kiện đã được tự động xác nhận giao hàng.',
                    'can_confirm'    => false,
                    'confirmed_at'   => $now->format('H:i:s d/m/Y'),
                    'today_count'    => $this->getTodayDeliveryCount(),
                    'rows'           => $historyRows->values()->all(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $package->delivered_at
                ? 'Kiện này đã được giao hàng trước đó.'
                : 'Đã tìm thấy thông tin kiện.',
            'data'    => $this->buildPreviewData($package, $perPage, $page),
        ]);
    }

    /**
     * Xác nhận giao hàng thành công
     */
    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'package_id'     => 'nullable|integer',
            'product_code'   => 'nullable|string|max:255',
            'delivered_note' => 'nullable|string|max:1000',
            'per_page'       => 'nullable|integer',
            'page'           => 'nullable|integer|min:1',
        ]);

        $package = null;
        $resolved = null;

        if (! empty($validated['package_id'])) {
            $package = PackingPackage::with(['packer', 'vehicle', 'items.itemCode'])->find((int) $validated['package_id']);
        } elseif (! empty($validated['product_code'])) {
            $resolved = $this->resolveDeliveryTarget(trim($validated['product_code']));
            $package = $resolved['package'] ?? null;
        }

        if (! $package) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy kiện tương ứng để xác nhận giao hàng.',
            ], 404);
        }

        /** @var PackingPackage $package */
        if ($package->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể giao khi kiện đã hoàn tất đóng gói và xuất xưởng.',
            ], 422);
        }

        if (! $package->dispatched_at) {
            return response()->json([
                'success' => false,
                'message' => 'Kiện hàng này chưa được xuất xưởng. Không thể thực hiện giao hàng.',
            ], 422);
        }

        if ($package->delivered_at) {
            return response()->json([
                'success' => false,
                'message' => 'Kiện này đã được xác nhận giao hàng trước đó.',
            ], 422);
        }

        $now = now();
        $package->update([
            'delivered_at'   => $now,
            'delivered_note' => $validated['delivered_note'] ?? null,
        ]);

        // Tự động kiểm tra và hoàn thành đơn hàng nếu đã giao đủ các kiện
        $this->checkAndCompleteOrder($package);

        $package->load(['packer', 'vehicle', 'items.itemCode']);
        $this->loadMorphCodeRelations($package->items->map(fn ($item) => $item->itemCode)->filter());

        $perPage = (int) ($validated['per_page'] ?? 15);
        $perPage = in_array($perPage, [15, 25, 50, 100], true) ? $perPage : 15;
        $page = max((int) ($validated['page'] ?? 1), 1);
        $historyRows = $this->buildHistoryRows(collect([$package]));

        return response()->json([
            'success' => true,
            'message' => 'Xác nhận giao hàng thành công.',
            'data' => [
                ...$this->buildPreviewData($package, $perPage, $page),
                'status_label'   => 'Đã giao',
                'status_detail'  => 'Kiện đã được xác nhận giao hàng.',
                'can_confirm'    => false,
                'confirmed_at'   => $now->format('H:i:s d/m/Y'),
                'today_count'    => $this->getTodayDeliveryCount(),
                'rows'           => $historyRows->values()->all(),
            ],
        ]);
    }

    /**
     * Xác nhận giao hàng hàng loạt cho nhiều kiện cùng lúc
     */
    public function bulkConfirm(Request $request)
    {
        $validated = $request->validate([
            'package_ids'    => 'required|array|min:1',
            'package_ids.*'  => 'integer|exists:packing_packages,id',
            'delivered_note' => 'nullable|string|max:1000',
        ]);

        $now = now();
        $packages = PackingPackage::whereIn('id', $validated['package_ids'])
            ->where('status', 'completed')
            ->whereNotNull('dispatched_at')
            ->whereNull('delivered_at')
            ->get();

        if ($packages->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có kiện nào đủ điều kiện để xác nhận giao hàng.',
            ], 422);
        }

        $count = 0;
        foreach ($packages as $pkg) {
            $pkg->update([
                'delivered_at'   => $now,
                'delivered_note' => $validated['delivered_note'] ?? null,
            ]);
            $count++;

            $this->checkAndCompleteOrder($pkg);
        }

        return response()->json([
            'success'         => true,
            'message'         => "Đã xác nhận giao hàng thành công cho {$count} kiện hàng!",
            'delivered_count' => $count,
            'today_count'     => $this->getTodayDeliveryCount(),
        ]);
    }

    /**
     * Tìm kiện từ mã sản phẩm quét được
     */
    private function resolveDeliveryTarget(string $code): ?array
    {
        $code = trim($code);
        if ($code === '') {
            return null;
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

        // 2. Tìm theo tên kiện (VD: 'Kiện 1', 'Kiện cánh tủ')
        $package = PackingPackage::with(['packer', 'vehicle', 'items.itemCode'])
            ->where('name', $code)
            ->first();
        if ($package) {
            return $this->formatResolvedPackage($package);
        }

        // 3. Tìm theo Mã Đơn Hàng (VD: DA00003, MIN00001, PLY00001)
        $order = Order::where('order_code', $code)->first();
        if ($order) {
            $orderPackageIds = PackingPackageItem::whereHasMorph('itemCode', [
                AcrylicOrderItemCode::class,
                GlassOrderItemCode::class,
                MinLateOrderItemCode::class,
            ], function ($q, $type) use ($order) {
                if ($type === AcrylicOrderItemCode::class) {
                    $q->whereHas('acrylicOrderItem.orderSupply', fn($sq) => $sq->where('order_id', $order->id));
                } elseif ($type === GlassOrderItemCode::class) {
                    $q->whereHas('glassOrderItem.orderSupply', fn($sq) => $sq->where('order_id', $order->id));
                } elseif ($type === MinLateOrderItemCode::class) {
                    $q->whereHas('minLateOrderItem.orderSupply', fn($sq) => $sq->where('order_id', $order->id));
                }
            })->pluck('packing_package_id')->unique();

            if ($orderPackageIds->isNotEmpty()) {
                // Ưu tiên kiện đã xuất xưởng nhưng chưa giao
                $pkg = PackingPackage::with(['packer', 'vehicle', 'items.itemCode'])
                    ->whereIn('id', $orderPackageIds)
                    ->where('status', 'completed')
                    ->whereNotNull('dispatched_at')
                    ->whereNull('delivered_at')
                    ->first()
                    ?? PackingPackage::with(['packer', 'vehicle', 'items.itemCode'])
                    ->whereIn('id', $orderPackageIds)
                    ->first();

                if ($pkg) {
                    return $this->formatResolvedPackage($pkg);
                }
            }
        }

        // 4. Tìm theo mã linh kiện con bên trong kiện
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

            return $this->formatResolvedPackage($packageItem->package, $packageItem);
        }

        return null;
    }

    private function formatResolvedPackage(PackingPackage $package, ?PackingPackageItem $packageItem = null): array
    {
        $this->loadMorphCodeRelations($package->items->map(fn ($item) => $item->itemCode)->filter());

        if ($packageItem) {
            $itemData = $this->formatDeliveryItem($package, $packageItem);
        } else {
            $firstItem = $package->items->where('is_packaged', true)->first() ?? $package->items->first();
            $itemData = $firstItem ? $this->formatDeliveryItem($package, $firstItem) : null;
        }

        return [
            'package'          => $package,
            'row_product_code' => $itemData['product_code'] ?? (string) $package->id,
            'row_product_name' => $itemData['product_name'] ?? $package->name,
            'row_order_code'   => $itemData['order_code'] ?? '—',
            'row_notes'        => $itemData['notes'] ?? '—',
        ];
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
                'package_code'     => (string) $package->id,
                'package_name'     => $package->name,
                'order_code'       => $packageOrder?->order_code ?? '—',
                'customer_name'    => $packageOrder?->customer_name ?? '—',
                'customer_phone'   => $packageOrder?->phone ?? '—',
                'delivery_address' => $packageOrder?->address ?? '—',
                'total_items'      => $effectiveItems->count(),
                'operator'         => $package->packer?->name ?? '—',
                'dispatched_time'  => $package->dispatched_at?->format('H:i d/m/Y') ?? '—',
                'view_url'         => route('processes.packing.show', $package),
            ];
        });
    }

    /**
     * Tự động kiểm tra và cập nhật trạng thái Đơn hàng sang Hoàn thành nếu tất cả các kiện đã giao
     */
    private function checkAndCompleteOrder(PackingPackage $package): void
    {
        $effectiveItems = $package->items->where('is_packaged', true);
        if ($effectiveItems->isEmpty()) {
            $effectiveItems = $package->items;
        }

        $packageOrder = $this->resolvePackageOrder($effectiveItems);
        if (! $packageOrder) {
            return;
        }

        $orderPackageIds = PackingPackageItem::whereHasMorph('itemCode', [
            AcrylicOrderItemCode::class,
            GlassOrderItemCode::class,
            MinLateOrderItemCode::class,
        ], function ($q, $type) use ($packageOrder) {
            if ($type === AcrylicOrderItemCode::class) {
                $q->whereHas('acrylicOrderItem.orderSupply', fn($sq) => $sq->where('order_id', $packageOrder->id));
            } elseif ($type === GlassOrderItemCode::class) {
                $q->whereHas('glassOrderItem.orderSupply', fn($sq) => $sq->where('order_id', $packageOrder->id));
            } elseif ($type === MinLateOrderItemCode::class) {
                $q->whereHas('minLateOrderItem.orderSupply', fn($sq) => $sq->where('order_id', $packageOrder->id));
            }
        })->pluck('packing_package_id')->unique();

        if ($orderPackageIds->isNotEmpty()) {
            $undeliveredCount = PackingPackage::whereIn('id', $orderPackageIds)
                ->whereNull('delivered_at')
                ->count();

            if ($undeliveredCount === 0) {
                $packageOrder->update(['status' => 'completed']);
            }
        }
    }

    /**
     * Dựng hàng lịch sử hiển thị
     */
    private function buildHistoryRows(Collection $packages): Collection
    {
        $rows = collect();

        foreach ($packages as $package) {
            $package->loadMissing(['packer', 'items.itemCode']);
            $this->loadMorphCodeRelations($package->items->map(fn ($item) => $item->itemCode)->filter());

            $rows->push($this->formatDeliveryRow($package));
        }

        return $rows->sortByDesc('time_raw')->values();
    }

    /**
     * Bộ lọc lịch sử hiển thị
     */
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

    /**
     * Phân trang cho danh sách lịch sử
     */
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

    /**
     * Định dạng dữ liệu hàng trong lịch sử
     */
    private function formatDeliveryRow(PackingPackage $package): array
    {
        $effectiveItems = $package->items->where('is_packaged', true);
        if ($effectiveItems->isEmpty()) {
            $effectiveItems = $package->items;
        }

        $packageOrder = $this->resolvePackageOrder($effectiveItems);
        $packageCode = (string) $package->id;

        return [
            'package_id'   => $package->id,
            'package_code' => $packageCode,
            'package_name' => $package->name,
            'product_code' => $packageCode,
            'product_name' => $package->name,
            'order_code'   => $packageOrder?->order_code ?? '—',
            'notes'        => filled($package->delivered_note) ? $package->delivered_note : '—',
            'operator'     => $package->packer?->name ?? '—',
            'time_raw'     => $package->delivered_at,
            'time'         => $package->delivered_at?->format('H:i:s d/m/Y') ?? '—',
            'status_label' => 'Đã giao',
            'view_url'     => route('processes.packing.show', $package),
        ];
    }

    /**
     * Xây dựng dữ liệu xem trước kiện cho Ajax quét mã
     */
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
        $canConfirm = $package->status === 'completed' && $package->dispatched_at && ! $package->delivered_at;
        $paginatedItems = $this->paginatePackageItems($effectiveItems, $perPage, $page);
        $itemRows = $paginatedItems->getCollection()
            ->map(fn (PackingPackageItem $packageItem) => $this->formatDeliveryPackageItem($packageItem))
            ->values()
            ->all();
        $sizeMeta = $paginatedItems->getCollection()->isNotEmpty()
            ? $this->resolvePackageItemSizeMeta($paginatedItems->getCollection()->first())
            : $this->resolvePackageItemSizeMeta(null);

        return [
            'package_id'     => $package->id,
            'package_code'   => $packageCode,
            'package_name'   => $package->name,
            'delivered_note' => $package->delivered_note ?? '',
            'status_label'   => $package->delivered_at
                ? 'Đã giao'
                : ($package->dispatched_at
                    ? 'Đã xuất xưởng (Chưa giao)'
                    : ($package->status === 'completed'
                        ? 'Đã hoàn tất đóng gói (Chưa xuất xưởng)'
                        : 'Đang đóng gói (Chưa xuất xưởng)')),
            'status_detail'  => $package->delivered_at
                ? 'Kiện này đã được xác nhận giao hàng thành công.'
                : ($package->dispatched_at
                    ? 'Kiện đã xuất xưởng, sẵn sàng xác nhận giao hàng.'
                    : ($package->status === 'completed'
                        ? 'Kiện đã hoàn tất đóng gói nhưng chưa được xuất xưởng. Cần xuất xưởng trước khi giao hàng.'
                        : 'Kiện chưa hoàn tất đóng gói và chưa xuất xưởng. Chỉ xem thông tin, chưa thể giao hàng.')),
            'can_confirm'    => $canConfirm,
            'order_info'     => [
                ['label' => 'Mã đơn', 'value' => $orderCode],
                ['label' => 'Tên khách hàng', 'value' => $customerName],
                ['label' => 'Số điện thoại', 'value' => $customerPhone],
                ['label' => 'Địa chỉ giao hàng', 'value' => $deliveryAddress],
            ],
            'package_info'   => [
                ['label' => 'Mã kiện', 'value' => $packageCode],
                ['label' => 'Người đóng gói', 'value' => $package->packer?->name ?? '—'],
                ['label' => 'Tổng SL linh kiện', 'value' => (string) $totalItems],
                ['label' => 'Trạng thái kiện', 'value' => $package->delivered_at
                    ? 'Đã giao hàng'
                    : ($package->dispatched_at
                        ? 'Đã xuất xưởng'
                        : ($package->status === 'completed' ? 'Đã đóng gói hoàn tất' : 'Đang đóng gói'))],
            ],
            'package_items'  => $itemRows,
            'package_items_meta' => [
                'current_page' => $paginatedItems->currentPage(),
                'last_page'    => $paginatedItems->lastPage(),
                'per_page'     => $paginatedItems->perPage(),
                'total'        => $paginatedItems->total(),
                'first_item'   => $paginatedItems->firstItem() ?? 0,
                'last_item'    => $paginatedItems->lastItem() ?? 0,
                'size_meta'    => $sizeMeta,
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

    private function formatDeliveryItem(PackingPackage $package, PackingPackageItem $packageItem): array
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
            'package_id'   => $package->id,
            'package_name' => $package->name,
            'product_code' => $code?->product_id ?? '—',
            'product_name' => $productName,
            'order_code'   => $orderCode,
            'notes'        => $notes,
        ];
    }

    private function formatDeliveryPackageItem(PackingPackageItem $packageItem): array
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
            'type'         => $type,
            'size_title'   => $sizeLabels['title'],
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
                'title'   => 'KÍCH THƯỚC CÁNH (MM)',
                'label_1' => 'DÀI (MM)',
                'label_2' => 'RỘNG (MM)',
            ];
        }

        if ($type instanceof MinLateOrderItemCode) {
            return [
                'title'   => 'KÍCH THƯỚC (MM)',
                'label_1' => 'CAO (VÁN)',
                'label_2' => 'RỘNG',
            ];
        }

        return [
            'title'   => 'KÍCH THƯỚC (MM)',
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

    /**
     * Lấy số lượng kiện đã giao trong ngày hôm nay
     */
    private function getTodayDeliveryCount(): int
    {
        $packages = PackingPackage::with(['packer', 'items.itemCode'])
            ->whereNotNull('delivered_at')
            ->whereDate('delivered_at', today())
            ->orderByDesc('delivered_at')
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
