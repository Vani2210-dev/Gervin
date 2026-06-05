<?php

namespace App\Http\Controllers;

use App\Models\AcrylicOrderItemCode;
use App\Models\GlassOrderItemCode;
use App\Models\MinLateOrderItemCode;
use App\Models\PackingPackage;
use App\Models\PackingPackageItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackingPackageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $packages = PackingPackage::with('packer')
            ->withCount('items')
            ->latest()
            ->get();

        return view('packing.index', [
            'draftPackages' => $packages->where('status', 'draft'),
            'completedPackages' => $packages->where('status', 'completed'),
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
        $package->load(['packer', 'items.itemCode']);

        $packageItems = $package->items->map(function (PackingPackageItem $packageItem) {
            return $this->formatPackageItem($packageItem);
        });

        return view('packing.show', compact('package', 'packageItems'));
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

        // item_code_id luu theo product_id de khop voi ma quet tren tem/QR.
        $existing = PackingPackageItem::where('item_code_type', $found['class'])
            ->where('item_code_id', $productId)
            ->with('package')
            ->first();

        if ($existing) {
            if ($request->expectsJson()) {
                $jsonPackageName = $existing->package?->name ?? 'kiện khác';

                return response()->json([
                    'success' => false,
                    'message' => "Linh kiện này đã nằm trong {$jsonPackageName}.",
                ], 400);
            }

            $packageName = $existing->package?->name ?? 'kiện khác';

            return back()
                ->withInput()
                ->with('error', "Linh kiện này đã nằm trong {$packageName}.");
        }

        $packageItem = $package->items()->create([
            'item_code_type' => $found['class'],
            'item_code_id' => $productId,
        ]);

        if ($request->expectsJson()) {
            $packageItem->load('itemCode');

            return response()->json([
                'success' => true,
                'message' => 'Đã thêm linh kiện vào kiện.',
                'data' => $this->formatPackageItem($packageItem),
                'items_count' => $package->items()->count(),
            ]);
        }

        return back()->with('success', 'Đã thêm linh kiện vào kiện.');
    }

    public function destroyItem(Request $request, PackingPackage $package, PackingPackageItem $item)
    {
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

        $item->delete();
        $itemsCount = $package->items()->count();

        if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã xóa linh kiện khỏi kiện.',
                    'items_count' => $itemsCount,
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
            $type = 'Kính';
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
            'delete_url' => route('processes.packing.items.destroy', [
                'package' => $packageItem->packing_package_id,
                'item' => $packageItem->id,
            ]),
            'created_at' => $packageItem->created_at,
            'created_at_label' => $packageItem->created_at?->format('H:i:s d/m/Y'),
        ];
    }
}
