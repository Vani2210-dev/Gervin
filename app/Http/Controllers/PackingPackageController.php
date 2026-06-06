<?php

namespace App\Http\Controllers;

use App\Models\AcrylicOrderItemCode;
use App\Models\GlassOrderItemCode;
use App\Models\MinLateOrderItemCode;
use App\Models\PackingPackage;
use App\Models\PackingPackageItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PackingPackageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view packing')->only(['index', 'show', 'print']);
        $this->middleware('permission:add packing')->only(['store', 'storeItem']);
        $this->middleware('permission:delete packing')->only(['destroyItem', 'destroy']);
        $this->middleware('permission:complete packing')->only(['complete']);
    }

    public function index()
    {
        // Lấy số lượng hiển thị trên trang của danh sách đang đóng gói (mặc định 15)
        $perPageDraft = intval(request()->input('draft_per_page', 15));
        if ($perPageDraft <= 0) {
            $perPageDraft = 15;
        }

        // Lấy số lượng hiển thị trên trang của danh sách đã hoàn tất (mặc định 15)
        $perPageCompleted = intval(request()->input('completed_per_page', 15));
        if ($perPageCompleted <= 0) {
            $perPageCompleted = 15;
        }

        // Phân trang danh sách đang đóng gói
        $draftPackages = PackingPackage::with('packer')
            ->withCount('packagedItems as items_count')
            ->where('status', 'draft')
            ->latest()
            ->paginate($perPageDraft, ['*'], 'draft_page')
            ->withQueryString();

        // Phân trang danh sách đã hoàn tất
        $completedPackages = PackingPackage::with('packer')
            ->withCount('packagedItems as items_count')
            ->where('status', 'completed')
            ->latest()
            ->paginate($perPageCompleted, ['*'], 'completed_page')
            ->withQueryString();

        return view('packing.index', [
            'draftPackages' => $draftPackages,
            'completedPackages' => $completedPackages,
            'perPageDraft' => $perPageDraft,
            'perPageCompleted' => $perPageCompleted,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $package = PackingPackage::create([
            'name' => $validated['name'],
            'status' => 'draft',
            'packed_by' => Auth::id(),
        ]);

        return redirect()
            ->route('processes.packing.show', $package)
            ->with('success', 'Tạo kiện đóng gói thành công.');
    }

    public function show(PackingPackage $package)
    {
        $package->load(['packer']);

        $perPage = intval(request()->input('per_page', 15));
        if ($perPage <= 0) {
            $perPage = 15;
        }

        $paginatedItems = $package->items()
            ->with('itemCode')
            ->paginate($perPage);

        // Load polymorphic relations for the paginated items
        $codes = $paginatedItems->map(fn($item) => $item->itemCode)->filter();
        $this->loadMorphCodeRelations($codes);

        $packageItems = $paginatedItems->through(function (PackingPackageItem $packageItem) {
            return $this->formatPackageItem($packageItem);
        })->withQueryString();

        $ordersData = $this->getPackageOrdersData($package);

        return view('packing.show', compact('package', 'packageItems', 'ordersData', 'perPage'));
    }

    public function print(PackingPackage $package)
    {
        if ($package->status !== 'completed') {
            return redirect()
                ->route('processes.packing.show', $package)
                ->with('error', 'Chỉ có thể in tem khi kiện đã hoàn tất.');
        }

        $package->load(['packer', 'items.itemCode']);

        $codes = $package->items->map(fn($item) => $item->itemCode)->filter();
        $this->loadMorphCodeRelations($codes);

        $items = $package->items->map(function (PackingPackageItem $packageItem) {
            return $this->formatPackageItem($packageItem);
        });

        // Khi in tem chỉ lấy dữ liệu từ linh kiện đã quét, bỏ qua item nháp/placeholder.
        $scannedItems = $items->where('is_packaged', true);
        $printItems = $scannedItems->isNotEmpty() ? $scannedItems : $items;
        $printPackageItems = $package->items->where('is_packaged', true);
        $printPackageItems = $printPackageItems->isNotEmpty() ? $printPackageItems : $package->items;
        // Lấy thông tin khách hàng theo đơn đầu tiên có linh kiện được in trên tem.
        $packageOrder = $this->resolvePackageOrder($printPackageItems);

        $totalItems = $printItems->count();
        $orderCode = $printItems->first()?->order_code ?? '—';
        $typeSummary = $printItems->pluck('type')->filter()->unique()->values()->implode(', ') ?: '—';
        // Mã kiện lấy thẳng ID của kiện, không thêm tiền tố.
        $packageCode = (string) $package->id;

        // QR dùng trực tiếp mã kiện đang lưu trong database.
        $qrSvg = QrCode::format('svg')
            ->size(180)
            ->margin(1)
            ->encoding('UTF-8')
            ->errorCorrection('H')
            ->generate($packageCode);
        $qrSvg = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $qrSvg);

        return view('packing.print', [
            'package' => $package,
            'totalItems' => $totalItems,
            'orderCode' => $orderCode,
            'typeSummary' => $typeSummary,
            'packageCode' => $packageCode,
            'qrSvg' => $qrSvg,
            'customerName' => $packageOrder?->customer_name ?? '—',
            'customerPhone' => $packageOrder?->phone ?? '—',
            'deliveryAddress' => $packageOrder?->address ?? '—',
        ]);
    }

    public function storeItem(Request $request, PackingPackage $package)
    {
        if ($package->status === 'completed') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kiện đã hoàn tất, không thể thêm linh kiện.',
                ], 400);
            }

            return back()->with('error', 'Kiện đã hoàn tất, không thể thêm linh kiện.');
        }

        $validated = $request->validate([
            'product_code' => 'required|string|max:255',
        ]);

        $found = $this->findItemCode(trim($validated['product_code']));

        if (! $found) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy linh kiện với mã đã quét.',
                ], 404);
            }

            return back()
                ->withInput()
                ->with('error', 'Không tìm thấy linh kiện với mã đã quét.');
        }

        $productId = $found['code']->product_id;

        // Check if already scanned/packaged in another package
        $existing = PackingPackageItem::where('item_code_type', $found['class'])
            ->where('item_code_id', $productId)
            ->where('is_packaged', true)
            ->with('package')
            ->first();

        if ($existing && $existing->packing_package_id !== $package->id) {
            $packageName = $existing->package?->name ?? 'kiện khác';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Linh kiện này đã được đóng gói trong {$packageName}.",
                ], 400);
            }
            return back()->with('error', "Linh kiện này đã được đóng gói trong {$packageName}.");
        }

        // Check if already scanned in the current package
        $existingInCurrent = PackingPackageItem::where('packing_package_id', $package->id)
            ->where('item_code_type', $found['class'])
            ->where('item_code_id', $productId)
            ->first();

        if ($existingInCurrent && $existingInCurrent->is_packaged) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Linh kiện này đã được quét vào kiện hiện tại.',
                ], 400);
            }
            return back()->with('error', 'Linh kiện này đã được quét vào kiện hiện tại.');
        }

        $packageItem = null;

        // Find the Order of this scanned plate to attach all plates of this order
        $order = null;
        $codeObj = $found['code'];
        if ($codeObj instanceof AcrylicOrderItemCode) {
            $order = $codeObj->acrylicOrderItem?->orderSupply?->order;
        } elseif ($codeObj instanceof GlassOrderItemCode) {
            $order = $codeObj->glassOrderItem?->orderSupply?->order;
        } elseif ($codeObj instanceof MinLateOrderItemCode) {
            $order = $codeObj->minLateOrderItem?->orderSupply?->order;
        }

        if ($order) {
            $firstItem = $package->items()->first();
            if ($firstItem) {
                $firstItem->load('itemCode');
                if ($firstItem->itemCode) {
                    $this->loadMorphCodeRelations(collect([$firstItem->itemCode]));
                }
                $firstOrderId = null;
                $firstCode = $firstItem->itemCode;
                if ($firstCode instanceof AcrylicOrderItemCode) {
                    $firstOrderId = $firstCode->acrylicOrderItem?->orderSupply?->order_id;
                } elseif ($firstCode instanceof GlassOrderItemCode) {
                    $firstOrderId = $firstCode->glassOrderItem?->orderSupply?->order_id;
                } elseif ($firstCode instanceof MinLateOrderItemCode) {
                    $firstOrderId = $firstCode->minLateOrderItem?->orderSupply?->order_id;
                }

                if ($firstOrderId && $firstOrderId !== $order->id) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Kiện này đã chứa linh kiện của đơn hàng khác. Mỗi kiện chỉ được chứa tối đa 1 đơn hàng.',
                        ], 400);
                    }
                    return back()->with('error', 'Kiện này đã chứa linh kiện của đơn hàng khác. Mỗi kiện chỉ được chứa tối đa 1 đơn hàng.');
                }
            }

            $order->load([
                'supplies.items.codes',
                'supplies.glassItems.codes',
                'supplies.minLateItems.codes'
            ]);

            $allPlatesInOrder = [];
            foreach ($order->supplies as $supply) {
                foreach ($supply->items as $item) {
                    foreach ($item->codes as $code) {
                        $allPlatesInOrder[] = [
                            'class' => AcrylicOrderItemCode::class,
                            'product_id' => $code->product_id,
                        ];
                    }
                }
                foreach ($supply->glassItems as $item) {
                    foreach ($item->codes as $code) {
                        $allPlatesInOrder[] = [
                            'class' => GlassOrderItemCode::class,
                            'product_id' => $code->product_id,
                        ];
                    }
                }
                foreach ($supply->minLateItems as $item) {
                    foreach ($item->codes as $code) {
                        $allPlatesInOrder[] = [
                            'class' => MinLateOrderItemCode::class,
                            'product_id' => $code->product_id,
                        ];
                    }
                }
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($package, $allPlatesInOrder, $found, $productId, &$packageItem) {
                foreach ($allPlatesInOrder as $plate) {
                    $isTarget = ($plate['class'] === $found['class'] && $plate['product_id'] === $productId);

                    $item = PackingPackageItem::where('packing_package_id', $package->id)
                        ->where('item_code_type', $plate['class'])
                        ->where('item_code_id', $plate['product_id'])
                        ->first();

                    if ($item) {
                        if ($isTarget) {
                            $item->update(['is_packaged' => true]);
                            $packageItem = $item;
                        }
                    } else {
                        // Delete any duplicate rows in other packages with is_packaged = false
                        $existingElsewhere = PackingPackageItem::where('item_code_type', $plate['class'])
                            ->where('item_code_id', $plate['product_id'])
                            ->first();

                        if ($existingElsewhere) {
                            if ($existingElsewhere->is_packaged) {
                                continue;
                            } else {
                                $existingElsewhere->delete();
                            }
                        }

                        $newItem = PackingPackageItem::create([
                            'packing_package_id' => $package->id,
                            'item_code_type' => $plate['class'],
                            'item_code_id' => $plate['product_id'],
                            'is_packaged' => $isTarget,
                        ]);

                        if ($isTarget) {
                            $packageItem = $newItem;
                        }
                    }
                }
            });
        } else {
            // Fallback: create the single item
            $packageItem = PackingPackageItem::updateOrCreate([
                'packing_package_id' => $package->id,
                'item_code_type' => $found['class'],
                'item_code_id' => $productId,
            ], [
                'is_packaged' => true,
            ]);
        }

        if ($request->expectsJson()) {
            $packageItem->load('itemCode');
            $this->loadMorphCodeRelations(collect([$packageItem->itemCode]));

            $package->load('items.itemCode');
            $this->loadMorphCodeRelations($package->items->map(fn($item) => $item->itemCode)->filter());

            return response()->json([
                'success' => true,
                'message' => 'Đã thêm linh kiện vào kiện.',
                'data' => $this->formatPackageItem($packageItem),
                'items_count' => $package->packagedItems()->count() . '/' . $package->items()->count(),
                'orders_data' => $this->getPackageOrdersData($package),
            ]);
        }

        return back()->with('success', 'Đã thêm linh kiện vào kiện.');
    }

    public function destroyItem(Request $request, PackingPackage $package, PackingPackageItem $item)
    {
        if ($package->dispatched_at) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kiện đã xuất xưởng, không thể xóa linh kiện.',
                ], 400);
            }

            return back()->with('error', 'Kiện đã xuất xưởng, không thể xóa linh kiện.');
        }

        if ($package->status === 'completed') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kiện đã hoàn tất, không thể xóa linh kiện.',
                ], 400);
            }

            return back()->with('error', 'Kiện đã hoàn tất, không thể xóa linh kiện.');
        }

        if ($item->packing_package_id !== $package->id) {
            abort(404);
        }

        // Find the order of this plate item
        $code = $item->itemCode;
        $orderId = null;
        if ($code instanceof AcrylicOrderItemCode) {
            $orderId = $code->acrylicOrderItem?->orderSupply?->order_id;
        } elseif ($code instanceof GlassOrderItemCode) {
            $orderId = $code->glassOrderItem?->orderSupply?->order_id;
        } elseif ($code instanceof MinLateOrderItemCode) {
            $orderId = $code->minLateOrderItem?->orderSupply?->order_id;
        }

        // Delete the item
        $item->delete();

        // Check if there are other scanned/packaged items of the same order.
        // If not, clean up the remaining unscanned placeholders of the same order.
        if ($orderId) {
            $package->load('items.itemCode');
            $this->loadMorphCodeRelations($package->items->map(fn($it) => $it->itemCode)->filter());

            $hasOtherScanned = false;
            $unscannedItemsToClean = [];

            foreach ($package->items as $pkgItem) {
                $c = $pkgItem->itemCode;
                $pkgOrderId = null;
                if ($c instanceof AcrylicOrderItemCode) {
                    $pkgOrderId = $c->acrylicOrderItem?->orderSupply?->order_id;
                } elseif ($c instanceof GlassOrderItemCode) {
                    $pkgOrderId = $c->glassOrderItem?->orderSupply?->order_id;
                } elseif ($c instanceof MinLateOrderItemCode) {
                    $pkgOrderId = $c->minLateOrderItem?->orderSupply?->order_id;
                }

                if ($pkgOrderId === $orderId) {
                    if ($pkgItem->is_packaged) {
                        $hasOtherScanned = true;
                    } else {
                        $unscannedItemsToClean[] = $pkgItem;
                    }
                }
            }

            if (!$hasOtherScanned) {
                foreach ($unscannedItemsToClean as $unscannedItem) {
                    $unscannedItem->delete();
                }
            }
        }

        if ($request->expectsJson()) {
            // Reload package items relationship to make sure it excludes the deleted items
            $package->load('items.itemCode');
            $this->loadMorphCodeRelations($package->items->map(fn($it) => $it->itemCode)->filter());

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa linh kiện khỏi kiện.',
                'items_count' => $package->packagedItems()->count() . '/' . $package->items()->count(),
                'orders_data' => $this->getPackageOrdersData($package),
            ]);
        }

        return back()->with('success', 'Đã xóa linh kiện khỏi kiện.');
    }

    public function complete(PackingPackage $package)
    {
        if ($package->status === 'completed') {
            return redirect()
                ->route('processes.packing.show', $package)
                ->with('success', 'Kiện đã hoàn tất trước đó.');
        }

        $package->update(['status' => 'completed']);

        return redirect()
            ->route('processes.packing')
            ->with('success', 'Hoàn tất đóng gói thành công.');
    }

    public function destroy(PackingPackage $package)
    {
        if ($package->dispatched_at) {
            return redirect()
                ->route('processes.packing')
                ->with('error', 'Kiện đã xuất xưởng, không thể xóa.');
        }

        $package->delete();

        return redirect()
            ->route('processes.packing')
            ->with('success', 'Xóa kiện đóng gói thành công.');
    }

    private function findItemCode(string $productCode): ?array
    {
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
            $code = $lookup['class']::with($lookup['relation'])
                ->where('product_id', $productCode)
                ->first();

            if ($code) {
                return [
                    'class' => $lookup['class'],
                    'code' => $code,
                ];
            }
        }

        return null;
    }

    private function formatPackageItem(PackingPackageItem $packageItem): object
    {
        $code = $packageItem->itemCode;
        $productName = '—';
        $orderCode = '—';
        $type = '—';

        if ($code instanceof AcrylicOrderItemCode) {
            $item = $code->acrylicOrderItem;
            $productName = $item?->product_name ?? '—';
            $orderCode = $item?->orderSupply?->order?->order_code ?? '—';
            $type = 'Acrylic';
        } elseif ($code instanceof GlassOrderItemCode) {
            $item = $code->glassOrderItem;
            $productName = $item?->product_name ?? '—';
            $orderCode = $item?->orderSupply?->order?->order_code ?? '—';
            $type = 'Glass';
        } elseif ($code instanceof MinLateOrderItemCode) {
            $item = $code->minLateOrderItem;
            $productName = $item?->name ?? '—';
            $orderCode = $item?->orderSupply?->order?->order_code ?? '—';
            $type = 'Min-late';
        }

        return (object) [
            'id' => $packageItem->id,
            'product_code' => $code?->product_id ?? '—',
            'product_name' => $productName,
            'order_code' => $orderCode,
            'type' => $type,
            'is_packaged' => $packageItem->is_packaged,
            'delete_url' => route('processes.packing.items.destroy', [
                'package' => $packageItem->packing_package_id,
                'item' => $packageItem->id,
            ]),
            'created_at' => $packageItem->created_at,
            'created_at_label' => $packageItem->created_at?->format('H:i:s d/m/Y'),
        ];
    }

    private function resolvePackageOrder($packageItems)
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

    private function getPackageOrdersData(PackingPackage $package): array
    {
        // Eager load the items relation if not loaded to prevent N+1 queries
        if (!$package->relationLoaded('items')) {
            $package->load('items.itemCode');
        }

        $codes = $package->items->map(fn($item) => $item->itemCode)->filter();
        $this->loadMorphCodeRelations($codes);

        $orderIds = [];
        foreach ($package->items as $packageItem) {
            $code = $packageItem->itemCode;
            $orderId = null;
            if ($code instanceof AcrylicOrderItemCode) {
                $orderId = $code->acrylicOrderItem?->orderSupply?->order_id;
            } elseif ($code instanceof GlassOrderItemCode) {
                $orderId = $code->glassOrderItem?->orderSupply?->order_id;
            } elseif ($code instanceof MinLateOrderItemCode) {
                $orderId = $code->minLateOrderItem?->orderSupply?->order_id;
            }
            if ($orderId) {
                $orderIds[$orderId] = true;
            }
        }

        $ordersData = [];
        if (!empty($orderIds)) {
            $orders = \App\Models\Order::with([
                'supplies.items.codes',
                'supplies.glassItems.codes',
                'supplies.minLateItems.codes'
            ])->whereIn('id', array_keys($orderIds))->get();

            // Scanned item product codes in this package
            $scannedProductCodes = $package->items->map(function ($packageItem) {
                return $packageItem->itemCode?->product_id;
            })->filter()->toArray();

            foreach ($orders as $order) {
                $allPlates = [];
                // Acrylic
                foreach ($order->supplies as $supply) {
                    foreach ($supply->items as $item) {
                        foreach ($item->codes as $code) {
                            $isScanned = in_array($code->product_id, $scannedProductCodes);
                            $allPlates[] = [
                                'product_code' => $code->product_id,
                                'product_name' => $item->product_name ?? '—',
                                'type' => 'Acrylic',
                                'is_scanned' => $isScanned,
                            ];
                        }
                    }
                    // Glass
                    foreach ($supply->glassItems as $item) {
                        foreach ($item->codes as $code) {
                            $isScanned = in_array($code->product_id, $scannedProductCodes);
                            $allPlates[] = [
                                'product_code' => $code->product_id,
                                'product_name' => $item->product_name ?? '—',
                                'type' => 'Kính',
                                'is_scanned' => $isScanned,
                            ];
                        }
                    }
                    // Min Late
                    foreach ($supply->minLateItems as $item) {
                        foreach ($item->codes as $code) {
                            $isScanned = in_array($code->product_id, $scannedProductCodes);
                            $allPlates[] = [
                                'product_code' => $code->product_id,
                                'product_name' => $item->product_name ?? $item->name ?? '—',
                                'type' => 'Min-late',
                                'is_scanned' => $isScanned,
                            ];
                        }
                    }
                }

                // Sort: unscanned first, scanned last
                usort($allPlates, function ($a, $b) {
                    return $a['is_scanned'] <=> $b['is_scanned'];
                });

                $ordersData[] = [
                    'order_code' => $order->order_code,
                    'customer_name' => $order->customer_name,
                    'plates' => $allPlates,
                ];
            }
        }

        return $ordersData;
    }

    private function loadMorphCodeRelations($codes)
    {
        if ($codes->isEmpty()) {
            return;
        }

        $acrylicCodes = $codes->filter(fn($code) => $code instanceof AcrylicOrderItemCode);
        if ($acrylicCodes->isNotEmpty()) {
            \Illuminate\Database\Eloquent\Collection::make($acrylicCodes)->load('acrylicOrderItem.orderSupply.order');
        }

        $glassCodes = $codes->filter(fn($code) => $code instanceof GlassOrderItemCode);
        if ($glassCodes->isNotEmpty()) {
            \Illuminate\Database\Eloquent\Collection::make($glassCodes)->load('glassOrderItem.orderSupply.order');
        }

        $minLateCodes = $codes->filter(fn($code) => $code instanceof MinLateOrderItemCode);
        if ($minLateCodes->isNotEmpty()) {
            \Illuminate\Database\Eloquent\Collection::make($minLateCodes)->load('minLateOrderItem.orderSupply.order');
        }
    }

}

