<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Material;
use App\Models\WoodBoard;
use App\Models\WoodBoardType;
use App\Models\WoodBoardPrice;
use App\Models\DcStock;
use App\Models\InventoryReceipt;
use App\Models\InventoryReceiptItem;
use App\Models\InventoryIssue;
use App\Models\InventoryIssueItem;
use App\Models\InventoryStocktake;
use App\Models\InventoryStocktakeItem;
use App\Models\InventoryTransaction;
use App\Models\Supplier;
use App\Models\Order;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;

        $this->middleware('permission:view warehouse', ['only' => [
            'index', 'stockCard', 'exportStock', 'receipts', 'showReceipt', 'printReceipt',
            'issues', 'showIssue', 'printIssue', 'stocktakes', 'showStocktake', 'reports',
            'exportReport', 'exportMatrixReport', 'warehouseList'
        ]]);

        $this->middleware('permission:add warehouse', ['only' => [
            'storeMaterial', 'importStock', 'createReceipt', 'storeReceipt', 'createIssue', 'storeIssue',
            'createStocktake', 'storeStocktake', 'storeWarehouse'
        ]]);

        $this->middleware('permission:edit warehouse', ['only' => [
            'updateMaterial', 'balanceStocktake', 'updateWarehouse', 'editReceipt', 'updateReceipt'
        ]]);

        $this->middleware('permission:delete warehouse', ['only' => [
            'destroyMaterial', 'destroyReceipt', 'destroyIssue', 'destroyStocktake', 'destroyWarehouse'
        ]]);
    }

    /**
     * 1. Dashboard & Danh sách Tấm ván tồn kho
     */
    public function index(Request $request)
    {
        // Auto-seed Acrylic data if database has 0 boards
        if (WoodBoard::count() === 0) {
            $excelPath = base_path('docs/TỒN KHO TIÊU THỤ  ACRYLIC TQ.xlsx');
            if (file_exists($excelPath)) {
                $this->inventoryService->seedAcrylicFromExcel($excelPath);
            }
        }

        $warehouses = $this->getOrderedWarehouses();
        $selectedWarehouseId = $request->input('warehouse_id');
        $selectedWarehouse = $selectedWarehouseId ? Warehouse::find($selectedWarehouseId) : null;
        $isEdgeBandWarehouse = $selectedWarehouse && ($selectedWarehouse->code === 'KHO-CHI' || str_contains(mb_strtolower($selectedWarehouse->name), 'chỉ'));
        $selectedCategory = $request->input('category');
        $stockFilter = $request->input('stock_filter', 'all'); // 'all', 'low_stock', 'out_of_stock', 'in_stock'
        $search = trim((string)$request->input('search'));

        // Smart Date Filter handling
        $dateFilterData = $this->getDateFilterParams($request);
        $startDate = $dateFilterData['start_date'];
        $endDate = $dateFilterData['end_date'];
        $dateLabel = $dateFilterData['label'];
        $dateMode = $request->input('date_mode', $dateFilterData['preset']);
        $dateVal = $request->input('date_val', '');

        // BẢNG VẬT TƯ TỒN KHO: Truy vấn trực tiếp từ bảng materials
        $query = Material::with(['warehouse']);

        if ($selectedWarehouseId) {
            $query->where('warehouse_id', $selectedWarehouseId);
        }

        if ($selectedCategory) {
            $query->where('category', $selectedCategory);
        }

        if ($stockFilter === 'in_stock') {
            $query->where('current_stock', '>', 0);
        } elseif ($stockFilter === 'out_of_stock') {
            $query->where('current_stock', '<=', 0);
        } elseif ($stockFilter === 'low_stock') {
            $query->whereColumn('current_stock', '<=', 'min_stock')->where('current_stock', '>', 0);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('origin', 'like', "%{$search}%");
            });
        }

        $statsQuery = (clone $query)->reorder();
        $totalItems = (clone $statsQuery)->count();
        $totalStockQty = (float)(clone $statsQuery)->sum('current_stock');
        $totalStockValue = (float)((clone $statsQuery)->selectRaw('SUM(current_stock * cost_price) as val')->value('val') ?? 0);
        $lowStockCount = (clone $statsQuery)->where(function ($q) {
            $q->whereColumn('current_stock', '<=', 'min_stock')->orWhere('current_stock', '<=', 0);
        })->count();

        $materials = $query->orderBy('code')->paginate(50)->withQueryString();

        $categories = Material::when($selectedWarehouseId, fn($q) => $q->where('warehouse_id', $selectedWarehouseId))
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        $dimStocks = collect();
        $matIds = $materials->getCollection()->pluck('id')->filter()->toArray();
        if (!empty($matIds)) {
            $dimStocks = InventoryTransaction::whereIn('material_id', $matIds)
                ->when($selectedWarehouseId, fn($q) => $q->where('warehouse_id', $selectedWarehouseId))
                ->whereNotNull('height')
                ->whereNotNull('width')
                ->groupBy('material_id', 'height', 'width')
                ->selectRaw('material_id, height, width, SUM(in_qty - out_qty) as dim_stock')
                ->havingRaw('dim_stock > 0')
                ->orderByDesc('dim_stock')
                ->get()
                ->groupBy('material_id');
        }

        // Tính Xuất - Nhập - Tồn theo bộ lọc ngày tháng
        $periodStats = collect();
        $startStats = collect();
        if (!empty($matIds)) {
            $periodQuery = InventoryTransaction::whereIn('material_id', $matIds);
            if ($selectedWarehouseId) {
                $periodQuery->where('warehouse_id', $selectedWarehouseId);
            }
            if ($startDate && $endDate) {
                $periodQuery->whereBetween('date', [$startDate, $endDate]);
            } elseif ($startDate) {
                $periodQuery->where('date', '>=', $startDate);
            } elseif ($endDate) {
                $periodQuery->where('date', '<=', $endDate);
            }

            $periodStats = $periodQuery->groupBy('material_id')
                ->selectRaw('material_id, SUM(in_qty) as total_in, SUM(out_qty) as total_out')
                ->get()
                ->keyBy('material_id');

            if ($startDate) {
                $startQuery = InventoryTransaction::whereIn('material_id', $matIds)
                    ->where('date', '<', $startDate);
                if ($selectedWarehouseId) {
                    $startQuery->where('warehouse_id', $selectedWarehouseId);
                }
                $startStats = $startQuery->groupBy('material_id')
                    ->selectRaw('material_id, SUM(in_qty - out_qty) as net_before')
                    ->get()
                    ->keyBy('material_id');
            }
        }

        foreach ($materials as $m) {
            $mInQty = (float)($periodStats[$m->id]->total_in ?? 0);
            $mOutQty = (float)($periodStats[$m->id]->total_out ?? 0);
            if ($startDate) {
                $mStartStock = (float)($m->initial_stock ?? 0) + (float)($startStats[$m->id]->net_before ?? 0);
                $mEndStock = $mStartStock + $mInQty - $mOutQty;
            } else {
                $mStartStock = (float)($m->initial_stock ?? 0);
                $mEndStock = (float)$m->current_stock;
            }

            $m->period_start_stock = $mStartStock;
            $m->period_in_qty = $mInQty;
            $m->period_out_qty = $mOutQty;
            $m->period_end_stock = $mEndStock;
        }

        $pageSumStartStock = $materials->getCollection()->sum('period_start_stock');
        $pageSumInQty = $materials->getCollection()->sum('period_in_qty');
        $pageSumOutQty = $materials->getCollection()->sum('period_out_qty');
        $pageSumEndStock = $materials->getCollection()->sum('period_end_stock');
        $pageSumTotalValue = $materials->getCollection()->sum(fn($m) => $m->period_end_stock * (float)$m->cost_price);

        $activeFilterCount = 0;
        if ($selectedWarehouseId) $activeFilterCount++;
        if ($selectedCategory) $activeFilterCount++;
        if ($stockFilter !== 'all') $activeFilterCount++;
        if ($dateMode !== 'all' && !empty($dateMode)) $activeFilterCount++;
        $isFiltered = ($activeFilterCount > 0) || !empty($search);

        $boardTypes = WoodBoardType::orderBy('display_order')->get();
        $colorCodes = WoodBoard::select('color_code')->distinct()->orderBy('color_code')->pluck('color_code')
            ->merge(DcStock::select('color_code')->distinct()->pluck('color_code'))
            ->filter()->unique()->sort()->values();

        return view('inventory.index', compact(
            'materials',
            'dimStocks',
            'warehouses',
            'selectedWarehouseId',
            'selectedWarehouse',
            'isEdgeBandWarehouse',
            'selectedCategory',
            'stockFilter',
            'search',
            'totalItems',
            'totalStockQty',
            'totalStockValue',
            'lowStockCount',
            'pageSumStartStock',
            'pageSumInQty',
            'pageSumOutQty',
            'pageSumEndStock',
            'pageSumTotalValue',
            'categories',
            'boardTypes',
            'colorCodes',
            'dateMode',
            'dateVal',
            'startDate',
            'endDate',
            'dateLabel',
            'activeFilterCount',
            'isFiltered'
        ));
    }

    /**
     * Store new wood board in inventory
     */
    public function storeMaterial(Request $request)
    {
        $code = trim((string)($request->code ?: $request->color_code));
        $name = trim((string)$request->name);
        if (!$name) {
            $name = $code;
        }

        $request->validate([
            'code'          => 'nullable|string|max:100',
            'color_code'    => 'nullable|string|max:100',
            'name'          => 'nullable|string|max:255',
            'origin_code'   => 'nullable|string|max:100',
            'warehouse_id'  => 'nullable|exists:warehouses,id',
            'category'      => 'nullable|string|max:100',
            'unit'          => 'nullable|string|max:50',
            'cost_price'    => 'nullable|numeric|min:0',
            'initial_stock' => 'nullable|numeric|min:0',
            'min_stock'     => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string',
        ]);

        if (!$code) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Vui lòng nhập mã vật tư / SKU!'], 422);
            }
            return back()->withErrors(['code' => 'Vui lòng nhập mã vật tư!']);
        }

        $targetWarehouseId = $request->input('warehouse_id');
        $warehouse = $targetWarehouseId ? Warehouse::find($targetWarehouseId) : Warehouse::first();
        if (!$warehouse) {
            $warehouse = Warehouse::create([
                'name' => 'Kho Vật Tư Chung (152)',
                'code' => '152_PHU',
                'status' => 'active'
            ]);
        }

        $initStock = (float)($request->initial_stock ?? 0);
        $costPrice = (float)($request->cost_price ?? 0);
        $minStock = (float)($request->min_stock ?? 5);
        $unit = trim((string)$request->unit) ?: 'Cái';
        $category = trim((string)($request->category ?: $request->price_group)) ?: ($warehouse->name ?? 'Vật tư');
        $origin = trim((string)$request->origin_code);

        // Kiểm tra xem mã này đã tồn tại trong kho chưa
        $existing = Material::where('code', $code)
            ->where(function ($q) use ($warehouse) {
                $q->where('warehouse_id', $warehouse->id)
                  ->orWhereNull('warehouse_id');
            })->first();

        if ($existing) {
            if ($request->ajax() || $request->wantsJson()) {
                $itemObj = [
                    'id'                  => 'mat_' . $existing->id,
                    'raw_id'              => $existing->id,
                    'material_id'         => $existing->id,
                    'wood_board_id'       => $existing->wood_board_id,
                    'wood_board_price_id' => $existing->wood_board_price_id,
                    'warehouse_id'        => $existing->warehouse_id,
                    'warehouse_code'      => $existing->warehouse_code,
                    'warehouse_name'      => $existing->warehouse?->name ?? 'Kho tổng',
                    'item_type'           => $existing->item_type,
                    'code'                => $existing->code,
                    'name'                => $existing->name,
                    'origin_code'         => $existing->origin ?? '',
                    'category'            => $existing->category,
                    'unit'                => $existing->unit ?: 'Cái',
                    'cost_price'          => (float)($existing->cost_price ?: 0),
                    'current_stock'       => (float)($existing->current_stock ?: 0),
                    'thickness'           => '',
                ];

                return response()->json([
                    'success'        => true,
                    'message'        => 'Mã vật tư ' . $code . ' đã có sẵn trong kho!',
                    'already_exists' => true,
                    'material'       => $existing,
                    'created_items'  => [$itemObj],
                    'primary_item'   => $itemObj,
                ]);
            }
            return back()->withErrors(['code' => "Mã vật tư {$code} đã tồn tại trong kho!"]);
        }

        // Tự động phân loại item_type
        $itemType = 'hardware';
        $upperCode = strtoupper($code);
        $upperWh = strtoupper($warehouse->code ?? '');
        if ($upperWh === '152_CHI' || str_starts_with($upperCode, 'CHI') || str_starts_with($upperCode, 'CML.') || str_starts_with($upperCode, 'ASA.CHI')) {
            $itemType = 'edge_band';
        } elseif ($upperWh === '152 - KÍNH' || $upperWh === '155_CÁNH KÍNH') {
            $itemType = 'glass';
        } elseif ($upperWh === '152 - NHÔM') {
            $itemType = 'aluminum';
        } elseif (in_array($upperWh, ['152_HÓA CHẤT', '152_PHU', '153_PHU'])) {
            $itemType = 'chemical';
        } elseif (mb_strtolower($unit) === 'tấm' || str_contains($upperWh, 'ACRYLIC') || str_contains($upperWh, 'COT') || str_contains($upperWh, 'MELAMIN') || str_contains($upperWh, 'LAMINATE')) {
            $itemType = 'board';
        }

        // Tạo mới vào bảng materials
        $material = Material::create([
            'code'           => $code,
            'name'           => $name,
            'warehouse_id'   => $warehouse->id,
            'warehouse_code' => $warehouse->code,
            'category'       => $category,
            'unit'           => $unit,
            'item_type'      => $itemType,
            'origin'         => $origin ?: null,
            'cost_price'     => $costPrice,
            'initial_stock'  => $initStock,
            'current_stock'  => $initStock,
            'min_stock'      => $minStock,
            'status'         => 'active',
            'notes'          => $request->notes,
        ]);

        if ($initStock > 0) {
            InventoryTransaction::create([
                'warehouse_id'   => $warehouse->id,
                'material_id'    => $material->id,
                'item_type'      => $itemType,
                'date'           => date('Y-m-d'),
                'type'           => 'initial',
                'voucher_code'   => 'DK-' . date('Y'),
                'in_qty'         => $initStock,
                'out_qty'        => 0,
                'stock_after'    => $initStock,
                'notes'          => 'Số dư tồn kho vật tư ban đầu',
                'created_by'     => Auth::id(),
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            $itemObj = [
                'id'                  => 'mat_' . $material->id,
                'raw_id'              => $material->id,
                'material_id'         => $material->id,
                'wood_board_id'       => $material->wood_board_id,
                'wood_board_price_id' => $material->wood_board_price_id,
                'warehouse_id'        => $material->warehouse_id,
                'warehouse_code'      => $material->warehouse_code,
                'warehouse_name'      => $warehouse->name ?? 'Kho tổng',
                'item_type'           => $material->item_type,
                'code'                => $material->code,
                'name'                => $material->name,
                'origin_code'         => $material->origin ?? '',
                'category'            => $material->category,
                'unit'                => $material->unit ?: 'Cái',
                'cost_price'          => (float)($material->cost_price ?: 0),
                'current_stock'       => (float)($material->current_stock ?: 0),
                'thickness'           => '',
            ];

            $createdItems = [$itemObj];

            // Nếu người dùng có nhập thông tin chỉ dán kèm theo
            if ($request->filled('edge_band_cost_price') || $request->boolean('is_edge_band')) {
                $edgeWarehouse = Warehouse::where('code', '152_CHI')->orWhere('code', 'KHO-CHI')->first();
                if ($edgeWarehouse) {
                    $edgeCode = str_starts_with(strtoupper($code), 'CHI-') ? $code : ('CHI-' . $code);
                    $edgeCost = (float)($request->input('edge_band_cost_price') ?: 0);
                    $edgeMat = Material::firstOrCreate(
                        ['code' => $edgeCode, 'warehouse_id' => $edgeWarehouse->id],
                        [
                            'name'           => 'Chỉ dán viền ' . $name,
                            'warehouse_code' => $edgeWarehouse->code,
                            'category'       => 'Chỉ dán viền',
                            'unit'           => 'm',
                            'item_type'      => 'edge_band',
                            'cost_price'     => $edgeCost,
                            'current_stock'  => 0,
                            'min_stock'      => 50,
                            'status'         => 'active',
                        ]
                    );

                    $createdItems[] = [
                        'id'                  => 'mat_' . $edgeMat->id,
                        'raw_id'              => $edgeMat->id,
                        'material_id'         => $edgeMat->id,
                        'wood_board_id'       => $edgeMat->wood_board_id,
                        'wood_board_price_id' => $edgeMat->wood_board_price_id,
                        'warehouse_id'        => $edgeWarehouse->id,
                        'warehouse_code'      => $edgeWarehouse->code,
                        'warehouse_name'      => $edgeWarehouse->name,
                        'item_type'           => 'edge_band',
                        'code'                => $edgeMat->code,
                        'name'                => $edgeMat->name,
                        'origin_code'         => $edgeMat->origin ?? '',
                        'category'            => $edgeMat->category,
                        'unit'                => 'm',
                        'cost_price'          => (float)($edgeMat->cost_price ?: 0),
                        'current_stock'       => (float)($edgeMat->current_stock ?: 0),
                        'thickness'           => '',
                    ];
                }
            }

            return response()->json([
                'success'       => true,
                'message'       => 'Đã thêm vật tư mới ' . $material->code . ' thành công!',
                'material'      => $material,
                'created_items' => $createdItems,
                'primary_item'  => $itemObj,
            ]);
        }

        return redirect()->route('inventory.index', ['warehouse_id' => $warehouse->id])
            ->with('success', 'Đã thêm mới vật tư ' . $material->code . ' vào kho ' . $warehouse->name . ' thành công!');
    }

    public function updateMaterial(Request $request, $id)
    {
        // 0. Ưu tiên cập nhật Material nếu có cờ is_material hoặc id thuộc bảng materials
        $isMaterial = $request->input('is_material') === '1' || $request->boolean('is_material');
        $matModel = \App\Models\Material::find($id);

        if ($matModel && ($isMaterial || (!$request->has('color_code') && !$request->has('is_price')))) {
            $matModel->update([
                'name'          => $request->name ?: $matModel->name,
                'origin'        => $request->origin_code,
                'category'      => $request->category ?: ($request->price_group ?: $matModel->category),
                'unit'          => $request->unit ?: $matModel->unit,
                'cost_price'    => (float)($request->cost_price ?? $matModel->cost_price),
                'min_stock'     => (float)($request->min_stock ?? $matModel->min_stock),
                'current_stock' => (float)($request->stock ?? $matModel->current_stock),
                'warehouse_id'  => $request->warehouse_id ?: $matModel->warehouse_id,
                'status'        => $request->status ?: $matModel->status,
                'notes'         => $request->notes,
            ]);

            if ($matModel->wood_board_price_id) {
                $p = \App\Models\WoodBoardPrice::find($matModel->wood_board_price_id);
                if ($p) {
                    if ($request->has('cost_price')) $p->price_board = (float)$request->cost_price;
                    if ($request->has('stock')) $p->stock = (float)$request->stock;
                    if ($request->filled('name')) $p->name = trim($request->name);
                    $p->save();
                }
            }
            if ($matModel->wood_board_id) {
                $b = \App\Models\WoodBoard::find($matModel->wood_board_id);
                if ($b) {
                    if ($matModel->item_type === 'edge_band') {
                        if ($request->has('stock')) $b->edge_band_current_stock = (float)$request->stock;
                        if ($request->has('cost_price')) $b->edge_band_cost_price = (float)$request->cost_price;
                    }
                    $b->save();
                }
            }

            return back()->with('success', 'Cập nhật thông tin vật tư ' . $matModel->code . ' thành công!');
        }

        // 1. Kiểm tra nếu là cập nhật dòng hàng hóa con (WoodBoardPrice)
        $isPrice = $request->input('is_price') === '1' || $request->boolean('is_price');
        $price = $isPrice ? \App\Models\WoodBoardPrice::with('board')->find($id) : null;
        if (!$price && !$request->has('color_code') && !$request->has('edge_band_current_stock')) {
            $price = \App\Models\WoodBoardPrice::with('board')->find($id);
        }

        if ($price) {
            if ($request->has('cost_price')) {
                $price->price_board = (float)$request->cost_price;
            }
            if ($request->has('stock')) {
                $newStock = (float)$request->stock;
                $targetWarehouseId = $request->input('warehouse_id');
                $isSubWarehouse = $targetWarehouseId && $targetWarehouseId != 1 && ($wh = Warehouse::find($targetWarehouseId)) && $wh->code !== 'KHO-VAN';
                if ($isSubWarehouse) {
                    $currentWhStock = (float)\App\Models\InventoryTransaction::where('warehouse_id', $targetWarehouseId)
                        ->where('wood_board_price_id', $price->id)
                        ->sum(DB::raw('in_qty - out_qty'));
                    $diff = $newStock - $currentWhStock;
                    if ($diff != 0) {
                        \App\Models\InventoryTransaction::create([
                            'warehouse_id'        => $targetWarehouseId,
                            'wood_board_id'       => $price->wood_board_id,
                            'wood_board_price_id' => $price->id,
                            'item_type'           => 'board',
                            'date'                => now()->toDateString(),
                            'type'                => 'initial',
                            'voucher_code'        => 'CBK-' . strtoupper(\Illuminate\Support\Str::random(6)),
                            'in_qty'              => $diff > 0 ? $diff : 0,
                            'out_qty'             => $diff < 0 ? abs($diff) : 0,
                            'stock_after'         => $newStock,
                            'unit_price'          => (float)$price->price_board,
                            'notes'               => 'Cập nhật số dư kho ' . $wh->name,
                            'created_by'          => \Illuminate\Support\Facades\Auth::id(),
                        ]);
                        $price->stock = max(0, (float)$price->stock + $diff);
                    }
                } else {
                    $price->stock = $newStock;
                }
            }
            if ($request->filled('name')) {
                $price->name = trim($request->name);
            }
            if ($request->has('foil_name')) {
                $price->foil_name = trim($request->foil_name) ?: null;
            }
            $price->save();

            if ($price->board) {
                $board = $price->board;
                $board->current_stock = $board->prices()->sum('stock');
                if ($request->filled('origin_code')) $board->origin_code = trim($request->origin_code);
                if ($request->has('foil_name')) $board->foil_name = trim($request->foil_name) ?: null;
                if ($request->filled('nature')) $board->nature = trim($request->nature);
                if ($request->filled('category')) $board->price_group = trim($request->category);
                if ($request->filled('warehouse_id')) {
                    $whExists = \App\Models\Warehouse::where('id', $request->warehouse_id)->exists();
                    if ($whExists) {
                        $board->warehouse_id = $request->warehouse_id;
                    }
                }
                if ($request->filled('status')) $board->status = $request->status;
                if ($request->has('notes')) $board->notes = $request->notes;
                $board->save();
            }

            return back()->with('success', 'Cập nhật thông tin hàng hóa ' . ($price->code ?: $price->name) . ' thành công!');
        }

        // 0. Cập nhật Material (vật tư chung, phụ kiện, hóa chất, nhôm...)
        $matModel = \App\Models\Material::find($id);
        if ($matModel && ($request->input('is_material') || !\App\Models\WoodBoardPrice::find($id) && !WoodBoard::find($id))) {
            $matModel->update([
                'name'          => $request->name ?: $matModel->name,
                'origin'        => $request->origin_code,
                'category'      => $request->category ?: ($request->price_group ?: $matModel->category),
                'unit'          => $request->unit ?: $matModel->unit,
                'cost_price'    => (float)($request->cost_price ?? $matModel->cost_price),
                'min_stock'     => (float)($request->min_stock ?? $matModel->min_stock),
                'current_stock' => (float)($request->stock ?? $matModel->current_stock),
                'status'        => $request->status ?: $matModel->status,
                'notes'         => $request->notes,
            ]);
            return back()->with('success', 'Cập nhật thông tin vật tư ' . $matModel->code . ' thành công!');
        }

        // 2. Cập nhật WoodBoard (tấm ván hoặc chỉ dán viền)
        $material = WoodBoard::findOrFail($id);

        $request->validate([
            'origin_code' => 'nullable|string|max:100',
            'foil_name' => 'nullable|string|max:255',
            'nature' => 'nullable|string|max:100',
            'price_group' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'cost_price' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'status' => 'nullable|in:active,inactive',
            'notes' => 'nullable|string',
            'edge_band_unit' => 'nullable|string|max:50',
            'edge_band_current_stock' => 'nullable|numeric|min:0',
            'edge_band_cost_price' => 'nullable|numeric|min:0',
            'edge_band_min_stock' => 'nullable|numeric|min:0',
        ]);

        $group = trim($request->price_group ?: $request->category);

        $targetWarehouseId = $material->warehouse_id;
        if ($request->filled('warehouse_id') && \App\Models\Warehouse::where('id', $request->warehouse_id)->exists()) {
            $targetWarehouseId = $request->warehouse_id;
        }

        $updateData = [
            'origin_code' => trim($request->origin_code),
            'foil_name' => trim($request->foil_name) ?: null,
            'nature' => trim($request->nature) ?: ($material->nature ?: 'Vật tư hàng hóa'),
            'price_group' => $group ?: $material->price_group,
            'unit' => trim($request->unit) ?: $material->unit,
            'cost_price' => (float)($request->cost_price ?? $material->cost_price),
            'min_stock' => (float)($request->min_stock ?? $material->min_stock),
            'warehouse_id' => $targetWarehouseId,
            'status' => $request->status ?: $material->status,
            'notes' => $request->notes,
        ];

        if ($request->has('edge_band_unit') && $request->filled('edge_band_unit')) {
            $updateData['edge_band_unit'] = trim($request->edge_band_unit);
        }
        if ($request->has('edge_band_current_stock') && $request->filled('edge_band_current_stock')) {
            $updateData['edge_band_current_stock'] = (float)$request->edge_band_current_stock;
        }
        if ($request->has('edge_band_cost_price') && $request->filled('edge_band_cost_price')) {
            $updateData['edge_band_cost_price'] = (float)$request->edge_band_cost_price;
        }
        if ($request->has('edge_band_min_stock') && $request->filled('edge_band_min_stock')) {
            $updateData['edge_band_min_stock'] = (float)$request->edge_band_min_stock;
        }

        $material->update($updateData);

        return back()->with('success', 'Cập nhật thông tin tấm & chỉ dán ' . $material->color_code . ' thành công!');
    }

    /**
     * Delete wood board, price, or material
     */
    public function destroyMaterial($id)
    {
        $mat = \App\Models\Material::find($id);
        if ($mat && !WoodBoard::where('id', $id)->exists() && !\App\Models\WoodBoardPrice::where('id', $id)->exists()) {
            $code = $mat->code ?: $mat->name;
            $mat->transactions()->delete();
            $mat->delete();
            return back()->with('success', 'Đã xóa vật tư ' . $code . ' khỏi kho thành công!');
        }

        $price = \App\Models\WoodBoardPrice::find($id);
        if ($price) {
            $code = $price->code ?: $price->name;
            $price->transactions()->delete();
            $price->delete();
            return back()->with('success', 'Đã xóa hàng hóa ' . $code . ' khỏi kho thành công!');
        }

        $material = WoodBoard::find($id);
        if ($material) {
            $code = $material->color_code;
            $material->transactions()->delete();
            $material->prices()->delete();
            $material->delete();
            return back()->with('success', 'Đã xóa tấm ' . $code . ' khỏi kho thành công!');
        }

        if ($mat) {
            $code = $mat->code ?: $mat->name;
            $mat->transactions()->delete();
            $mat->delete();
            return back()->with('success', 'Đã xóa vật tư ' . $code . ' khỏi kho thành công!');
        }

        return back()->with('error', 'Không tìm thấy vật tư cần xóa!');
    }

    /**
     * Thẻ kho (Stock Card - lịch sử biến động của 1 mã hàng hóa / tấm / chỉ / vật tư)
     */
    public function stockCard(Request $request, $id)
    {
        $type = $request->input('type');

        // Kiểm tra nếu là Material
        if ($type === 'material' || !$type) {
            $mat = \App\Models\Material::with('warehouse')->find($id);
            if ($mat) {
                $material = (object)[
                    'id'            => $mat->id,
                    'code'          => $mat->code,
                    'origin_code'   => $mat->origin,
                    'category'      => $mat->category,
                    'name'          => $mat->name,
                    'current_stock' => (float)$mat->current_stock,
                    'unit'          => $mat->unit ?: 'Cái',
                    'min_stock'     => (float)$mat->min_stock,
                ];

                $txQuery = \App\Models\InventoryTransaction::where(function($q) use ($mat) {
                        $q->where('material_id', $mat->id);
                        if ($mat->wood_board_id) {
                            $q->orWhere(function($sub) use ($mat) {
                                $sub->whereNull('material_id')->where('wood_board_id', $mat->wood_board_id);
                            });
                        }
                    })
                    ->with(['warehouse', 'creator'])
                    ->orderBy('date', 'desc')
                    ->orderBy('id', 'desc');

                if ($request->filled('warehouse_id')) {
                    $txQuery->where('warehouse_id', $request->input('warehouse_id'));
                }
                $transactions = $txQuery->paginate(50);

                if ($request->ajax()) {
                    return view('inventory.partials.stock_card_modal_content', compact('material', 'transactions'));
                }

                return view('inventory.stock_card', compact('material', 'transactions'));
            }
        }

        // Kiểm tra nếu là WoodBoardPrice
        $price = \App\Models\WoodBoardPrice::with(['board.warehouse', 'type'])->find($id);
        if ($price && ($type === 'price' || !$type)) {
            $material = (object)[
                'id'            => $price->id,
                'code'          => $price->code ?: ($price->board?->color_code . ($price->type?->prefix ?? '')),
                'origin_code'   => $price->board?->origin_code,
                'category'      => $price->type?->name ?? $price->board?->price_group ?? 'Tấm cốt gỗ',
                'name'          => 'Tấm ' . ($price->board?->color_code ?? '') . ' - ' . ($price->name ?: $price->type?->name),
                'current_stock' => (float)$price->stock,
                'unit'          => $price->board?->unit ?: 'Tấm',
                'min_stock'     => 5,
            ];

            $txQuery = \App\Models\InventoryTransaction::where('wood_board_price_id', $price->id)
                ->with(['warehouse', 'creator'])
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc');

            if ($request->filled('warehouse_id')) {
                $txQuery->where('warehouse_id', $request->input('warehouse_id'));
            }
            $transactions = $txQuery->paginate(30);

            if ($request->ajax()) {
                return view('inventory.partials.stock_card_modal_content', compact('material', 'transactions'));
            }

            return view('inventory.stock_card', compact('material', 'transactions'));
        }

        $materialModel = WoodBoard::findOrFail($id);
        if ($type === 'edge_band') {
            $material = (object)[
                'id'            => $materialModel->id,
                'code'          => $materialModel->edge_band_code,
                'origin_code'   => $materialModel->origin_code,
                'category'      => 'Chỉ dán viền',
                'name'          => $materialModel->edge_band_name,
                'current_stock' => (float)$materialModel->edge_band_current_stock,
                'unit'          => $materialModel->edge_band_unit ?: 'm',
                'min_stock'     => (float)$materialModel->edge_band_min_stock,
            ];
            $txQuery = $materialModel->transactions()->where('item_type', 'edge_band')->with(['warehouse', 'creator']);
        } else {
            $material = (object)[
                'id'            => $materialModel->id,
                'code'          => $materialModel->color_code,
                'origin_code'   => $materialModel->origin_code,
                'category'      => $materialModel->price_group ?: 'Tấm cốt gỗ',
                'name'          => 'Tấm ' . $materialModel->color_code,
                'current_stock' => (float)$materialModel->current_stock,
                'unit'          => $materialModel->unit ?: 'Tấm',
                'min_stock'     => (float)$materialModel->min_stock,
            ];
            $txQuery = $materialModel->transactions()->where('item_type', 'board')->with(['warehouse', 'creator']);
        }

        if ($request->filled('warehouse_id')) {
            $txQuery->where('warehouse_id', $request->input('warehouse_id'));
        }
        $transactions = $txQuery->paginate(30);

        if ($request->ajax()) {
            return view('inventory.partials.stock_card_modal_content', compact('material', 'transactions'));
        }

        return view('inventory.stock_card', compact('material', 'transactions'));
    }

        /**
     * Export Stock List to Excel (Xuất - Nhập - Tồn đầy đủ)
     */
    public function exportStock(Request $request)
    {
        $selectedWarehouseId = $request->input('warehouse_id');
        $selectedWarehouse = $selectedWarehouseId ? Warehouse::find($selectedWarehouseId) : null;
        $selectedCategory = $request->input('category');
        $stockFilter = $request->input('stock_filter', 'all');
        $search = trim((string)$request->input('search'));

        $dateFilterData = $this->getDateFilterParams($request);
        $startDate = $dateFilterData['start_date'];
        $endDate = $dateFilterData['end_date'];
        $dateLabel = $dateFilterData['label'];

        $query = Material::with(['warehouse']);

        if ($selectedWarehouseId) {
            $query->where('warehouse_id', $selectedWarehouseId);
        }
        if ($selectedCategory) {
            $query->where('category', $selectedCategory);
        }
        if ($stockFilter === 'in_stock') {
            $query->where('current_stock', '>', 0);
        } elseif ($stockFilter === 'out_of_stock') {
            $query->where('current_stock', '<=', 0);
        } elseif ($stockFilter === 'low_stock') {
            $query->whereColumn('current_stock', '<=', 'min_stock')->where('current_stock', '>', 0);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('origin', 'like', "%{$search}%");
            });
        }

        $materials = $query->orderBy('code')->get();
        $matIds = $materials->pluck('id')->filter()->toArray();

        $periodStats = collect();
        $startStats = collect();
        if (!empty($matIds)) {
            $periodQuery = InventoryTransaction::whereIn('material_id', $matIds);
            if ($selectedWarehouseId) {
                $periodQuery->where('warehouse_id', $selectedWarehouseId);
            }
            if ($startDate && $endDate) {
                $periodQuery->whereBetween('date', [$startDate, $endDate]);
            } elseif ($startDate) {
                $periodQuery->where('date', '>=', $startDate);
            } elseif ($endDate) {
                $periodQuery->where('date', '<=', $endDate);
            }

            $periodStats = $periodQuery->groupBy('material_id')
                ->selectRaw('material_id, SUM(in_qty) as total_in, SUM(out_qty) as total_out')
                ->get()
                ->keyBy('material_id');

            if ($startDate) {
                $startQuery = InventoryTransaction::whereIn('material_id', $matIds)
                    ->where('date', '<', $startDate);
                if ($selectedWarehouseId) {
                    $startQuery->where('warehouse_id', $selectedWarehouseId);
                }
                $startStats = $startQuery->groupBy('material_id')
                    ->selectRaw('material_id, SUM(in_qty - out_qty) as net_before')
                    ->get()
                    ->keyBy('material_id');
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Xuat_Nhap_Ton');

        $reportTitle = 'BÁO CÁO XUẤT - NHẬP - TỒN KHO & VẬT TƯ';
        if ($selectedWarehouse) {
            $reportTitle .= ' - ' . mb_strtoupper($selectedWarehouse->name);
        }
        $reportSub = 'Kỳ báo cáo: ' . $dateLabel . ' | Ngày xuất: ' . date('d/m/Y H:i');

        $sheet->setCellValue('A1', $reportTitle);
        $sheet->setCellValue('A2', $reportSub);
        $sheet->mergeCells('A1:M1');
        $sheet->mergeCells('A2:M2');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);
        $sheet->getStyle('A2')->getFont()->setSize(11)->setItalic(true);

        $headers = [
            'STT', 'Mã SKU', 'Mã xuất xứ', 'Tên hàng hóa / vật tư', 'Kho lưu trữ',
            'Nhóm hàng', 'ĐVT', 'Tồn đầu kỳ', 'Nhận trong kỳ', 'Tiêu thụ trong kỳ',
            'Tồn cuối kỳ', 'Giá mua (đ)', 'Giá trị tồn (đ)'
        ];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'];
        foreach ($headers as $idx => $h) {
            $sheet->setCellValue($cols[$idx] . '3', $h);
        }

        $row = 4;
        foreach ($materials as $i => $m) {
            $inQty = (float)($periodStats[$m->id]->total_in ?? 0);
            $outQty = (float)($periodStats[$m->id]->total_out ?? 0);
            if ($startDate) {
                $startStock = (float)($m->initial_stock ?? 0) + (float)($startStats[$m->id]->net_before ?? 0);
                $endStock = $startStock + $inQty - $outQty;
            } else {
                $startStock = (float)($m->initial_stock ?? 0);
                $endStock = (float)$m->current_stock;
            }
            $costPrice = (float)$m->cost_price;

            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $m->code);
            $sheet->setCellValue('C' . $row, $m->origin ?: '—');
            $sheet->setCellValue('D' . $row, $m->name);
            $sheet->setCellValue('E' . $row, $m->warehouse?->name ?? 'Kho tổng');
            $sheet->setCellValue('F' . $row, $m->category ?: '—');
            $sheet->setCellValue('G' . $row, $m->unit ?: 'Cái');
            $sheet->setCellValue('H' . $row, $startStock);
            $sheet->setCellValue('I' . $row, $inQty);
            $sheet->setCellValue('J' . $row, $outQty);
            $sheet->setCellValue('K' . $row, $endStock);
            $sheet->setCellValue('L' . $row, $costPrice);
            $sheet->setCellValue('M' . $row, "=K{$row}*L{$row}");
            $row++;
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']]
        ];
        $sheet->getStyle('A3:M3')->applyFromArray($headerStyle);

        // Summary row
        $sheet->setCellValue('A' . $row, 'TỔNG CỘNG:');
        $sheet->mergeCells("A{$row}:G{$row}");
        $sheet->setCellValue('H' . $row, "=SUM(H4:H" . ($row - 1) . ")");
        $sheet->setCellValue('I' . $row, "=SUM(I4:I" . ($row - 1) . ")");
        $sheet->setCellValue('J' . $row, "=SUM(J4:J" . ($row - 1) . ")");
        $sheet->setCellValue('K' . $row, "=SUM(K4:K" . ($row - 1) . ")");
        $sheet->setCellValue('M' . $row, "=SUM(M4:M" . ($row - 1) . ")");
        $sheet->getStyle("A{$row}:M{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:M{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        $sheet->getStyle("A3:M{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("H4:K{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("L4:M{$row}")->getNumberFormat()->setFormatCode('#,##0');

        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Bao_Cao_Xuat_Nhap_Ton_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * 2. Sổ tổng hợp Phiếu Nhập / Xuất kho (Master-Detail theo phong cách MISA SME)
     */
    public function receipts(Request $request)
    {
        $type = $request->input('type', 'all'); // 'all', 'receipt', 'issue'
        $warehouseId = $request->input('warehouse_id');
        $search = $request->input('search');
        $warehouses = $this->getOrderedWarehouses();

        // 1. Query Receipts (PNK)
        $receipts = collect();
        if ($type === 'all' || $type === 'receipt') {
            $rQuery = InventoryReceipt::with([
                'warehouse',
                'creator',
                'supplier',
                'order',
                'items.woodBoard',
                'items.boardPrice'
            ])->withCount('items');

            $this->applyDateFilter($rQuery, $request, 'date');

            if ($warehouseId) {
                $rQuery->where('warehouse_id', $warehouseId);
            }

            if ($search) {
                $rQuery->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('supplier_name', 'like', "%{$search}%")
                      ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                      ->orWhere('deliverer', 'like', "%{$search}%")
                      ->orWhere('receiver', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhereHas('order', fn($oq) => $oq->where('order_code', 'like', "%{$search}%"));
                });
            }

            $receipts = $rQuery->get()->map(function ($r) {
                $partnerText = $r->supplier
                    ? "[{$r->supplier->code}] {$r->supplier->name}"
                    : ($r->supplier_name ?: 'NCC Nhà máy');

                return (object)[
                    'unique_id'      => 'receipt_' . $r->id,
                    'id'             => $r->id,
                    'voucher_type'   => 'receipt',
                    'type_label'     => 'Nhập kho',
                    'badge_class'    => 'bg-emerald-50 text-emerald-700 border border-emerald-300',
                    'icon'           => 'solar:import-bold',
                    'code'           => $r->code,
                    'date'           => $r->date,
                    'warehouse'      => $r->warehouse,
                    'warehouse_name' => $r->warehouse->name ?? '—',
                    'order'          => $r->order,
                    'partner'        => $partnerText,
                    'partner_title'  => 'Nhà cung cấp',
                    'deliverer'      => $r->deliverer ?: '—',
                    'receiver'       => $r->receiver ?: '—',
                    'reason'         => $r->notes ?: 'Nhập kho vật tư',
                    'notes'          => $r->notes ?: '',
                    'items_count'    => $r->items_count ?? $r->items->count(),
                    'total_quantity' => (float)$r->total_quantity,
                    'total_amount'   => (float)$r->total_amount,
                    'creator'        => $r->creator,
                    'created_at'     => $r->created_at,
                    'show_url'       => route('inventory.receipts.show', $r),
                    'edit_url'       => route('inventory.receipts.edit', $r),
                    'print_url'      => route('inventory.receipts.print', $r),
                    'destroy_url'    => route('inventory.receipts.destroy', $r),
                    'items'          => $r->items->map(function ($it, $idx) use ($r) {
                        return [
                            'stt'         => $idx + 1,
                            'sku'         => $it->item_sku,
                            'name'        => $it->item_name,
                            'dimensions'  => $it->dimensions,
                            'unit'        => $it->item_unit,
                            'warehouse'   => $r->warehouse->name ?? '—',
                            'quantity'    => (float)$it->quantity,
                            'unit_price'  => (float)$it->unit_price,
                            'total_price' => (float)$it->total_price,
                            'notes'       => $it->notes ?: '',
                        ];
                    })->values()->all(),
                ];
            });
        }

        // 2. Query Issues (PXK)
        $issues = collect();
        if ($type === 'all' || $type === 'issue') {
            $iQuery = InventoryIssue::with([
                'warehouse',
                'creator',
                'order',
                'items.woodBoard',
                'items.boardPrice'
            ])->withCount('items');

            $this->applyDateFilter($iQuery, $request, 'date');

            if ($warehouseId) {
                $iQuery->where('warehouse_id', $warehouseId);
            }

            if ($search) {
                $iQuery->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('recipient', 'like', "%{$search}%")
                      ->orWhere('deliverer', 'like', "%{$search}%")
                      ->orWhere('reason', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhereHas('order', fn($oq) => $oq->where('order_code', 'like', "%{$search}%"));
                });
            }

            $issues = $iQuery->get()->map(function ($i) {
                $partnerText = $i->recipient
                    ?: ($i->order ? ($i->order->customer_name ?: 'Đơn ' . $i->order->code) : 'Xưởng sản xuất');

                return (object)[
                    'unique_id'      => 'issue_' . $i->id,
                    'id'             => $i->id,
                    'voucher_type'   => 'issue',
                    'type_label'     => 'Xuất kho',
                    'badge_class'    => 'bg-rose-50 text-rose-700 border border-rose-300',
                    'icon'           => 'solar:export-bold',
                    'code'           => $i->code,
                    'date'           => $i->date,
                    'warehouse'      => $i->warehouse,
                    'warehouse_name' => $i->warehouse->name ?? '—',
                    'order'          => $i->order,
                    'partner'        => $partnerText,
                    'partner_title'  => 'Người / Bộ phận nhận',
                    'deliverer'      => $i->deliverer ?: '—',
                    'receiver'       => $i->recipient ?: '—',
                    'reason'         => $i->reason ?: ($i->notes ?: 'Xuất kho vật tư'),
                    'notes'          => $i->notes ?: '',
                    'items_count'    => $i->items_count ?? $i->items->count(),
                    'total_quantity' => (float)$i->total_quantity,
                    'total_amount'   => (float)$i->total_amount,
                    'creator'        => $i->creator,
                    'created_at'     => $i->created_at,
                    'show_url'       => route('inventory.issues.show', $i),
                    'edit_url'       => route('inventory.issues.edit', $i),
                    'print_url'      => route('inventory.issues.print', $i),
                    'destroy_url'    => route('inventory.issues.destroy', $i),
                    'items'          => $i->items->map(function ($it, $idx) use ($i) {
                        return [
                            'stt'         => $idx + 1,
                            'sku'         => $it->item_sku,
                            'name'        => $it->item_name,
                            'dimensions'  => $it->dimensions,
                            'unit'        => $it->item_unit,
                            'warehouse'   => $i->warehouse->name ?? '—',
                            'quantity'    => (float)$it->quantity,
                            'unit_price'  => (float)$it->unit_price,
                            'total_price' => (float)$it->total_price,
                            'notes'       => $it->notes ?: '',
                        ];
                    })->values()->all(),
                ];
            });
        }

        // 3. Merge & Sort by date desc, then created_at / id desc
        $combined = $receipts->concat($issues)->sortByDesc(function ($item) {
            $dateStr = $item->date ? $item->date->format('Y-m-d') : '1970-01-01';
            $ts = $item->created_at ? $item->created_at->timestamp : 0;
            return $dateStr . '_' . str_pad((string)$ts, 12, '0', STR_PAD_LEFT) . '_' . str_pad((string)$item->id, 8, '0', STR_PAD_LEFT);
        })->values();

        // 4. Calculate overall statistics
        $stats = [
            'total_vouchers' => $combined->count(),
            'total_receipts' => $receipts->count(),
            'total_issues'   => $issues->count(),
            'total_quantity' => $combined->sum('total_quantity'),
            'total_amount'   => $combined->sum('total_amount'),
        ];

        // 5. Paginate with LengthAwarePaginator
        $perPage = 25;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $total = $combined->count();
        $pagedData = $combined->slice(($page - 1) * $perPage, $perPage)->values();

        $vouchers = new LengthAwarePaginator($pagedData, $total, $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        $voucherItemsMap = $pagedData->keyBy('unique_id')->map->items;
        $voucherMetaMap = $pagedData->keyBy('unique_id')->map(function ($v) {
            return [
                'code'           => $v->code,
                'type_label'     => $v->type_label,
                'badge_class'    => $v->badge_class,
                'date'           => $v->date ? $v->date->format('d/m/Y') : '—',
                'partner'        => $v->partner,
                'warehouse_name' => $v->warehouse_name,
                'reason'         => $v->reason,
                'total_quantity' => $v->total_quantity,
                'total_amount'   => $v->total_amount,
            ];
        });

        return view('inventory.receipts.index', compact('vouchers', 'warehouses', 'type', 'warehouseId', 'search', 'stats', 'voucherItemsMap', 'voucherMetaMap'));
    }

    public function createReceipt(Request $request)
    {
        $warehouses = $this->getOrderedWarehouses();
        $materials = $this->getSelectableMaterials();
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $nextCode = $this->inventoryService->generateCode('PNK', 'inventory_receipts');

        $selectedOrderId = $request->input('order_id');
        $selectedWarehouseId = $request->input('warehouse_id') ?: ($warehouses->first()?->id ?? null);
        $selectedOrder = $selectedOrderId ? Order::with(['customer', 'supplies.items', 'supplies.glassItems', 'supplies.minLateItems'])->find($selectedOrderId) : null;
        $orderItems = $selectedOrder ? $this->inventoryService->getOrderMaterialItems($selectedOrder, 'receipt', $selectedWarehouseId ? (int)$selectedWarehouseId : null) : [];

        $orders = Order::select('id', 'order_code', 'customer_name', 'order_date')
            ->whereNotIn('status', ['cancelled'])
            ->orderByDesc('id')
            ->take(100)
            ->get();

        $categories = WoodBoard::select('price_group')->whereNotNull('price_group')->distinct()->pluck('price_group')->filter()->values();
        $colorCodes = WoodBoard::select('color_code')->whereNotNull('color_code')->distinct()->pluck('color_code')->filter()->values();
        $boardTypes = WoodBoardType::orderBy('id')->get();

        return view('inventory.receipts.create', compact('warehouses', 'materials', 'nextCode', 'suppliers', 'orders', 'selectedOrder', 'orderItems', 'categories', 'colorCodes', 'boardTypes', 'selectedWarehouseId'));
    }

    public function storeReceipt(Request $request)
    {
        $request->validate([
            'warehouse_id'        => 'required|exists:warehouses,id',
            'date'                => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.material_id' => 'required',
            'items.*.height'      => 'nullable|numeric|min:0',
            'items.*.width'       => 'nullable|numeric|min:0',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'nullable|numeric|min:0',
        ]);

        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        $receipt = $this->inventoryService->createReceipt(
            $warehouse,
            $request->only(['order_id', 'code', 'date', 'supplier_id', 'supplier_name', 'deliverer', 'receiver', 'notes']),
            $request->input('items', []),
            Auth::id()
        );

        return redirect()->route('inventory.receipts.show', $receipt)->with('success', 'Tạo phiếu nhập kho ' . $receipt->code . ' thành công!');
    }

    public function showReceipt(InventoryReceipt $receipt)
    {
        $receipt->load(['order', 'warehouse', 'creator', 'supplier', 'items.woodBoard', 'items.boardPrice.type']);
        return view('inventory.receipts.show', compact('receipt'));
    }

    public function editReceipt(InventoryReceipt $receipt)
    {
        $receipt->load(['order', 'supplier', 'items.woodBoard', 'items.boardPrice.type']);
        $warehouses = $this->getOrderedWarehouses();
        $materials = $this->getSelectableMaterials();
        $suppliers = Supplier::orderBy('name')->get();
        $orders = Order::select('id', 'order_code', 'customer_name', 'order_date')
            ->whereNotIn('status', ['cancelled'])
            ->orderByDesc('id')
            ->take(100)
            ->get();

        $categories = WoodBoard::select('price_group')->whereNotNull('price_group')->distinct()->pluck('price_group')->filter()->values();
        $colorCodes = WoodBoard::select('color_code')->whereNotNull('color_code')->distinct()->pluck('color_code')->filter()->values();
        $boardTypes = WoodBoardType::orderBy('id')->get();

        return view('inventory.receipts.edit', compact('receipt', 'warehouses', 'materials', 'suppliers', 'orders', 'categories', 'colorCodes', 'boardTypes'));
    }

    public function updateReceipt(Request $request, InventoryReceipt $receipt)
    {
        $request->validate([
            'warehouse_id'        => 'required|exists:warehouses,id',
            'date'                => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.material_id' => 'required',
            'items.*.height'      => 'nullable|numeric|min:0',
            'items.*.width'       => 'nullable|numeric|min:0',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'nullable|numeric|min:0',
        ]);

        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        $receipt = $this->inventoryService->updateReceipt(
            $receipt,
            $warehouse,
            $request->only(['order_id', 'code', 'date', 'supplier_id', 'supplier_name', 'deliverer', 'receiver', 'notes']),
            $request->input('items', []),
            Auth::id()
        );

        return redirect()->route('inventory.receipts.show', $receipt)->with('success', 'Cập nhật phiếu nhập kho ' . $receipt->code . ' thành công!');
    }

    public function printReceipt(InventoryReceipt $receipt)
    {
        $receipt->load(['warehouse', 'creator', 'items.woodBoard', 'items.boardPrice.type']);
        return view('inventory.receipts.print', compact('receipt'));
    }

    public function destroyReceipt(InventoryReceipt $receipt)
    {
        $code = $receipt->code;
        $this->inventoryService->deleteReceipt($receipt);
        return redirect()->route('inventory.receipts.index')->with('success', 'Đã xóa phiếu nhập kho ' . $code . ' và hoàn trả (trừ lại) số lượng tồn kho thành công!');
    }

    /**
     * 3. Phiếu Xuất kho (Goods Issues)
     */
    public function issues(Request $request)
    {
        if (!$request->has('type')) {
            $request->merge(['type' => 'issue']);
        }
        return $this->receipts($request);
    }

    public function createIssue(Request $request)
    {
        $warehouses = $this->getOrderedWarehouses();
        $materials = $this->getSelectableMaterials();
        $nextCode = $this->inventoryService->generateCode('PXK', 'inventory_issues');

        $selectedOrderId = $request->input('order_id');
        $selectedWarehouseId = $request->input('warehouse_id') ?: ($warehouses->first()?->id ?? null);
        $selectedOrder = $selectedOrderId ? Order::with(['customer', 'supplies.items', 'supplies.glassItems', 'supplies.minLateItems'])->find($selectedOrderId) : null;
        $orderItems = $selectedOrder ? $this->inventoryService->getOrderMaterialItems($selectedOrder, 'issue', $selectedWarehouseId ? (int)$selectedWarehouseId : null) : [];

        $orders = Order::select('id', 'order_code', 'customer_name', 'order_date')
            ->whereNotIn('status', ['cancelled'])
            ->orderByDesc('id')
            ->take(100)
            ->get();

        $categories = WoodBoard::select('price_group')->whereNotNull('price_group')->distinct()->pluck('price_group')->filter()->values();
        $colorCodes = WoodBoard::select('color_code')->whereNotNull('color_code')->distinct()->pluck('color_code')->filter()->values();
        $boardTypes = WoodBoardType::orderBy('id')->get();

        return view('inventory.issues.create', compact('warehouses', 'materials', 'nextCode', 'orders', 'selectedOrder', 'orderItems', 'categories', 'colorCodes', 'boardTypes', 'selectedWarehouseId'));
    }

    public function storeIssue(Request $request)
    {
        $request->validate([
            'warehouse_id'        => 'required|exists:warehouses,id',
            'date'                => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.material_id' => 'required',
            'items.*.height'      => 'nullable|numeric|min:0',
            'items.*.width'       => 'nullable|numeric|min:0',
            'items.*.quantity'    => 'required|numeric|min:0.01',
        ]);

        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        $issue = $this->inventoryService->createIssue(
            $warehouse,
            $request->only(['order_id', 'code', 'date', 'recipient', 'deliverer', 'reason', 'notes']),
            $request->input('items', []),
            Auth::id()
        );

        return redirect()->route('inventory.issues.show', $issue)->with('success', 'Tạo phiếu xuất kho ' . $issue->code . ' thành công!');
    }

    public function showIssue(InventoryIssue $issue)
    {
        $issue->load(['order', 'warehouse', 'creator', 'items.woodBoard', 'items.boardPrice.type']);
        return view('inventory.issues.show', compact('issue'));
    }

    public function editIssue(InventoryIssue $issue)
    {
        $issue->load(['order', 'items.woodBoard', 'items.boardPrice.type']);
        $warehouses = $this->getOrderedWarehouses();
        $materials = $this->getSelectableMaterials();
        $orders = Order::select('id', 'order_code', 'customer_name', 'order_date')
            ->whereNotIn('status', ['cancelled'])
            ->orderByDesc('id')
            ->take(100)
            ->get();

        return view('inventory.issues.edit', compact('issue', 'warehouses', 'materials', 'orders'));
    }

    public function updateIssue(Request $request, InventoryIssue $issue)
    {
        $request->validate([
            'warehouse_id'        => 'required|exists:warehouses,id',
            'date'                => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.material_id' => 'required',
            'items.*.height'      => 'nullable|numeric|min:0',
            'items.*.width'       => 'nullable|numeric|min:0',
            'items.*.quantity'    => 'required|numeric|min:0.01',
        ]);

        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        $issue = $this->inventoryService->updateIssue(
            $issue,
            $warehouse,
            $request->only(['order_id', 'code', 'date', 'recipient', 'deliverer', 'reason', 'notes']),
            $request->input('items', []),
            Auth::id()
        );

        return redirect()->route('inventory.issues.show', $issue)->with('success', 'Cập nhật phiếu xuất kho ' . $issue->code . ' thành công!');
    }

    public function orderSupplies(Request $request, Order $order)
    {
        $type = $request->query('type');
        $warehouseId = $request->query('warehouse_id');
        $items = $this->inventoryService->getOrderMaterialItems($order, $type, $warehouseId ? (int)$warehouseId : null);
        return response()->json([
            'success'       => true,
            'order_id'      => $order->id,
            'order_code'    => $order->order_code,
            'customer_name' => $order->customer_name ?: ($order->customer?->name ?? ''),
            'items'         => $items,
        ]);
    }

    public function printIssue(InventoryIssue $issue)
    {
        $issue->load(['warehouse', 'creator', 'items.woodBoard', 'items.boardPrice.type']);
        return view('inventory.issues.print', compact('issue'));
    }

    public function destroyIssue(InventoryIssue $issue)
    {
        $code = $issue->code;
        $this->inventoryService->deleteIssue($issue);
        return redirect()->route('inventory.issues.index')->with('success', 'Đã xóa phiếu xuất kho ' . $code . ' và hoàn trả (cộng lại) số lượng tồn kho thành công!');
    }

    /**
     * 4. Phiếu Kiểm kê kho (Stocktake - Cân bằng kho chuẩn MISA / KiotViet)
     */
    public function stocktakes(Request $request)
    {
        $query = InventoryStocktake::with(['warehouse', 'balancedByUser'])->withCount('items');
        $this->applyDateFilter($query, $request, 'date');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('creator_name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $stocktakes = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(20)->withQueryString();

        return view('inventory.stocktakes.index', compact('stocktakes'));
    }

    public function createStocktake(Request $request)
    {
        $warehouseId = $request->input('warehouse_id');
        $warehouse = $warehouseId ? Warehouse::find($warehouseId) : Warehouse::first();
        if (!$warehouse) {
            $warehouse = Warehouse::create(['name' => 'Kho Tấm Ván', 'code' => 'KHO-VAN']);
        }

        $warehouses = $this->getOrderedWarehouses();
        $materials = $this->getSelectableMaterials($warehouse->id);
        $nextCode = $this->inventoryService->generateCode('PKK', 'inventory_stocktakes');

        return view('inventory.stocktakes.create', compact('warehouse', 'warehouses', 'materials', 'nextCode'));
    }

    public function storeStocktake(Request $request)
    {
        $request->validate([
            'warehouse_id'            => 'required|exists:warehouses,id',
            'date'                    => 'required|date',
            'items'                   => 'nullable|array',
            'items.*.material_id'     => 'nullable',
            'items.*.book_quantity'   => 'nullable|numeric',
            'items.*.actual_quantity' => 'nullable|numeric',
        ]);

        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        $rawItems = $request->input('items', []);
        $items = [];
        foreach ($rawItems as $it) {
            if (empty($it['material_id'])) continue;
            $items[] = [
                'material_id'     => $it['material_id'],
                'book_quantity'   => isset($it['book_quantity']) && $it['book_quantity'] !== '' ? (float)$it['book_quantity'] : null,
                'actual_quantity' => isset($it['actual_quantity']) && $it['actual_quantity'] !== '' ? (float)$it['actual_quantity'] : 0,
                'notes'           => $it['notes'] ?? ($it['reason'] ?? null),
            ];
        }

        $stocktake = $this->inventoryService->createStocktake(
            $warehouse,
            $request->only(['code', 'date', 'creator_name', 'notes']),
            $items,
            Auth::id()
        );

        if ($request->boolean('auto_balance')) {
            $this->inventoryService->balanceStocktake($stocktake, Auth::id());
            return redirect()->route('inventory.stocktakes.show', $stocktake)->with('success', 'Tạo và Cân bằng kho cho phiếu ' . $stocktake->code . ' thành công!');
        }

        return redirect()->route('inventory.stocktakes.show', $stocktake)->with('success', 'Lưu phiếu kiểm kê ' . $stocktake->code . ' thành công!');
    }

    public function showStocktake(InventoryStocktake $stocktake)
    {
        $stocktake->load(['warehouse', 'balancedByUser', 'items.woodBoard', 'items.boardPrice.type']);
        return view('inventory.stocktakes.show', compact('stocktake'));
    }

    /**
     * Lấy danh sách kho hàng ưu tiên: Kho Tấm Ván lên đầu tiên, Kho Dán Chỉ xuống cuối
     */
    private function getOrderedWarehouses()
    {
        return Warehouse::orderByRaw("
            CASE 
                WHEN code = 'KHO-VAN' OR LOWER(name) LIKE '%ván%' OR LOWER(name) LIKE '%tấm%' OR LOWER(name) LIKE '%gỗ%' THEN 0 
                WHEN code = 'KHO-CHI' OR LOWER(name) LIKE '%chỉ%' THEN 2 
                ELSE 1 
            END, name ASC
        ")->get();
    }

    /**
     * Lấy danh sách mặt hàng có thể chọn (WoodBoardPrice kết hợp WoodBoard & Chỉ dán viền)
     */
        private function getSelectableMaterials($warehouseId = null)
    {
        $materials = \App\Models\Material::with(['warehouse', 'boardPrice'])
            ->where('status', 'active')
            ->orderBy('code')
            ->get();

        return $materials->map(function ($m) {
            $costPrice = (float)($m->cost_price ?: 0);
            if ($costPrice <= 0 && $m->boardPrice && (float)$m->boardPrice->price_board > 0) {
                $costPrice = (float)$m->boardPrice->price_board;
            }

            return (object)[
                'id'                  => 'mat_' . $m->id,
                'raw_id'              => $m->id,
                'material_id'         => $m->id,
                'wood_board_id'       => $m->wood_board_id,
                'wood_board_price_id' => $m->wood_board_price_id,
                'warehouse_id'        => $m->warehouse_id,
                'warehouse_code'      => $m->warehouse_code,
                'warehouse_name'      => $m->warehouse?->name ?? 'Kho tổng',
                'item_type'           => $m->item_type,
                'code'                => $m->code,
                'name'                => $m->name,
                'origin_code'         => $m->origin ?? '',
                'category'            => $m->category,
                'unit'                => $m->unit ?: 'Cái',
                'cost_price'          => $costPrice,
                'current_stock'       => (float)($m->current_stock ?: 0),
                'thickness'           => '',
            ];
        });
    }

    public function balanceStocktake(InventoryStocktake $stocktake)
    {
        $this->inventoryService->balanceStocktake($stocktake, Auth::id());
        return redirect()->route('inventory.stocktakes.show', $stocktake)->with('success', 'Cân bằng tồn kho cho phiếu ' . $stocktake->code . ' thành công!');
    }

    public function destroyStocktake(InventoryStocktake $stocktake)
    {
        $code = $stocktake->code;
        $this->inventoryService->deleteStocktake($stocktake);
        return redirect()->route('inventory.stocktakes.index')->with('success', 'Đã xóa phiếu kiểm kê ' . $code . ' thành công!');
    }

    /**
     * 5. Báo cáo Xuất - Nhập - Tồn & Báo cáo Ma trận 12 Tháng Kế toán theo từng kho
     */
    public function reports(Request $request)
    {
        $tab = $request->input('tab', 'summary'); // 'summary', 'detail', or 'matrix'
        $dateFilter = $this->getDateFilterParams($request);
        if ($request->has('date_preset') || $request->has('start_date') || $request->has('end_date') || $request->has('date_mode')) {
            $startDate = $dateFilter['start_date'] ?? '2000-01-01';
            $endDate = $dateFilter['end_date'] ?? date('Y-m-d');
        } else {
            $startDate = date('Y-01-01');
            $endDate = date('Y-m-d');
        }
        $year = (int)$request->input('year', date('Y'));
        $warehouseId = $request->input('warehouse_id');
        $search = trim((string)$request->input('search', ''));
        $filterStock = $request->input('filter_stock', 'all');

        $warehouses = $this->getOrderedWarehouses();
        $selectedWarehouse = $warehouseId ? Warehouse::find($warehouseId) : null;
        $isEdgeBand = $selectedWarehouse && (str_contains(strtoupper($selectedWarehouse->code), 'CHI') || str_contains(mb_strtolower($selectedWarehouse->name), 'chỉ'));

        // Query materials
        $materialsQuery = Material::with('warehouse');
        if ($warehouseId) {
            $materialsQuery->where('warehouse_id', $warehouseId);
        }
        if ($search) {
            $materialsQuery->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('origin', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }
        $materials = $materialsQuery->orderBy('code')->get();

        // -------------------------------------------------------------
        // TAB 1: Xuất Nhập Tồn tổng hợp
        // -------------------------------------------------------------
        $reportData = [];

        // Aggregate start stock before $startDate
        $startQuery = InventoryTransaction::where('date', '<', $startDate);
        if ($warehouseId) {
            $startQuery->where('warehouse_id', $warehouseId);
        }
        $startStockMap = $startQuery->selectRaw('material_id, SUM(in_qty - out_qty) as total')
            ->whereNotNull('material_id')
            ->groupBy('material_id')
            ->pluck('total', 'material_id')
            ->toArray();

        // Aggregate in/out in period [$startDate, $endDate]
        $periodQuery = InventoryTransaction::whereBetween('date', [$startDate, $endDate]);
        if ($warehouseId) {
            $periodQuery->where('warehouse_id', $warehouseId);
        }
        $periodTx = $periodQuery->selectRaw('material_id, SUM(in_qty) as total_in, SUM(out_qty) as total_out')
            ->whereNotNull('material_id')
            ->groupBy('material_id')
            ->get()
            ->keyBy('material_id');

        foreach ($materials as $m) {
            $startStock = (float)($startStockMap[$m->id] ?? 0);
            $inQty = isset($periodTx[$m->id]) ? (float)$periodTx[$m->id]->total_in : 0.0;
            $outQty = isset($periodTx[$m->id]) ? (float)$periodTx[$m->id]->total_out : 0.0;
            $endStock = $startStock + $inQty - $outQty;

            if ($startStock == 0 && $inQty == 0 && $outQty == 0 && (float)$m->current_stock > 0) {
                $endStock = (float)$m->current_stock;
                $startStock = (float)$m->current_stock;
            }

            if ($filterStock === 'has_stock' && $endStock <= 0) {
                continue;
            }
            if ($filterStock === 'has_movement' && $inQty == 0 && $outQty == 0) {
                continue;
            }

            $reportData[] = [
                'material'    => $m,
                'sku'         => $m->code,
                'name'        => $m->name,
                'unit'        => $m->unit ?: ($isEdgeBand ? 'm' : 'Tấm'),
                'is_edge_band'=> $isEdgeBand || $m->isEdgeBand(),
                'start_stock' => $startStock,
                'in_qty'      => $inQty,
                'out_qty'     => $outQty,
                'end_stock'   => $endStock,
            ];
        }

        // -------------------------------------------------------------
        // TAB 2: Báo cáo Chi tiết phát sinh (Sổ chi tiết / Thẻ kho)
        // -------------------------------------------------------------
        $detailTransactions = collect();
        $selectedDetailMaterial = null;
        if ($tab === 'detail') {
            $detailMatId = $request->input('material_id');
            $detailTxQuery = InventoryTransaction::with(['material.warehouse', 'creator', 'warehouse'])
                ->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc');

            if ($warehouseId) {
                $detailTxQuery->where('warehouse_id', $warehouseId);
            }
            if ($detailMatId) {
                $selectedDetailMaterial = Material::find($detailMatId);
                $detailTxQuery->where('material_id', $detailMatId);
            }
            if ($search) {
                $detailTxQuery->where(function($q) use ($search) {
                    $q->where('voucher_code', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%")
                      ->orWhereHas('material', function($mQ) use ($search) {
                          $mQ->where('code', 'like', "%{$search}%")
                             ->orWhere('name', 'like', "%{$search}%");
                      });
                });
            }
            $detailTransactions = $detailTxQuery->paginate(50)->withQueryString();
        }

        // -------------------------------------------------------------
        // TAB 3: Ma trận Tiêu thụ 12 Tháng Kế toán
        // -------------------------------------------------------------
        $matrixRows = [];
        $colTotals = [
            'start_stock' => 0,
            'total_in'    => 0,
            'total_out'   => 0,
            'final_stock' => 0,
            'months'      => []
        ];
        for ($m = 1; $m <= 12; $m++) {
            $colTotals['months'][$m] = ['in' => 0, 'out' => 0, 'stock' => 0];
        }

        if ($tab === 'matrix') {
            // Aggregate initial stock before $year-01-01
            $initYearQuery = InventoryTransaction::where('date', '<', $year . '-01-01');
            if ($warehouseId) {
                $initYearQuery->where('warehouse_id', $warehouseId);
            }
            $initYearStockMap = $initYearQuery->selectRaw('material_id, SUM(in_qty - out_qty) as total')
                ->whereNotNull('material_id')
                ->groupBy('material_id')
                ->pluck('total', 'material_id')
                ->toArray();

            // Aggregate monthly in/out for $year
            $monthTxQuery = InventoryTransaction::whereYear('date', $year);
            if ($warehouseId) {
                $monthTxQuery->where('warehouse_id', $warehouseId);
            }
            $monthTxData = $monthTxQuery->selectRaw('material_id, MONTH(date) as m, SUM(in_qty) as total_in, SUM(out_qty) as total_out')
                ->whereNotNull('material_id')
                ->groupBy('material_id', DB::raw('MONTH(date)'))
                ->get();

            // Group monthly tx by material_id -> month
            $matMonthlyMap = [];
            foreach ($monthTxData as $item) {
                $matMonthlyMap[$item->material_id][$item->m] = [
                    'in'  => (float)$item->total_in,
                    'out' => (float)$item->total_out,
                ];
            }

            foreach ($materials as $idx => $m) {
                $initYearStock = (float)($initYearStockMap[$m->id] ?? 0);
                if ($initYearStock == 0 && empty($matMonthlyMap[$m->id]) && (float)$m->current_stock > 0 && $year == (int)date('Y')) {
                    $initYearStock = (float)$m->current_stock;
                }

                $running = $initYearStock;
                $rowMonths = [];
                $totalYearIn = 0;
                $totalYearOut = 0;

                for ($month = 1; $month <= 12; $month++) {
                    $mIn = (float)($matMonthlyMap[$m->id][$month]['in'] ?? 0);
                    $mOut = (float)($matMonthlyMap[$m->id][$month]['out'] ?? 0);
                    $running = $running + $mIn - $mOut;

                    $rowMonths[$month] = ['in' => $mIn, 'out' => $mOut, 'stock' => $running];
                    $totalYearIn += $mIn;
                    $totalYearOut += $mOut;

                    $colTotals['months'][$month]['in'] += $mIn;
                    $colTotals['months'][$month]['out'] += $mOut;
                    $colTotals['months'][$month]['stock'] += $running;
                }

                $matrixRows[] = [
                    'stt'           => $idx + 1,
                    'material_id'   => $m->id,
                    'code'          => $m->code,
                    'name'          => $m->name,
                    'unit'          => $m->unit ?: ($isEdgeBand ? 'm' : 'Tấm'),
                    'is_edge_band'  => $isEdgeBand || $m->isEdgeBand(),
                    'origin_code'   => $m->origin ?: '',
                    'category'      => $m->category ?: '',
                    'start_stock'   => $initYearStock,
                    'months'        => $rowMonths,
                    'total_year_in' => $totalYearIn,
                    'total_year_out'=> $totalYearOut,
                    'final_stock'   => $running,
                ];

                $colTotals['start_stock'] += $initYearStock;
                $colTotals['total_in'] += $totalYearIn;
                $colTotals['total_out'] += $totalYearOut;
                $colTotals['final_stock'] += $running;
            }
        }

        $yearsQuery = InventoryTransaction::selectRaw('DISTINCT YEAR(date) as y')->whereNotNull('date');
        if ($warehouseId) {
            $yearsQuery->where('warehouse_id', $warehouseId);
        }
        $availableYears = $yearsQuery->orderByDesc('y')->pluck('y')->toArray();
        if (!in_array((int)date('Y'), $availableYears)) {
            $availableYears[] = (int)date('Y');
        }
        rsort($availableYears);

        return view('inventory.reports.index', compact(
            'tab',
            'startDate',
            'endDate',
            'year',
            'warehouseId',
            'selectedWarehouse',
            'isEdgeBand',
            'warehouses',
            'reportData',
            'matrixRows',
            'colTotals',
            'availableYears',
            'search',
            'filterStock',
            'detailTransactions',
            'selectedDetailMaterial'
        ));
    }

    /**
     * Xuất Excel Báo cáo Xuất - Nhập - Tồn tổng hợp
     */
    public function exportReport(Request $request)
    {
        $dateFilter = $this->getDateFilterParams($request);
        if ($request->has('date_preset') || $request->has('start_date') || $request->has('end_date') || $request->has('date_mode')) {
            $startDate = $dateFilter['start_date'] ?? '2000-01-01';
            $endDate = $dateFilter['end_date'] ?? date('Y-m-d');
        } else {
            $startDate = date('Y-01-01');
            $endDate = date('Y-m-d');
        }
        $warehouseId = $request->input('warehouse_id');
        $selectedWarehouse = $warehouseId ? Warehouse::find($warehouseId) : null;
        $isEdgeBand = $selectedWarehouse && (str_contains(strtoupper($selectedWarehouse->code), 'CHI') || str_contains(mb_strtolower($selectedWarehouse->name), 'chỉ'));

        $materialsQuery = Material::with('warehouse');
        if ($warehouseId) {
            $materialsQuery->where('warehouse_id', $warehouseId);
        }
        $materials = $materialsQuery->orderBy('code')->get();

        $startStockMap = InventoryTransaction::where('date', '<', $startDate)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->whereNotNull('material_id')
            ->groupBy('material_id')
            ->selectRaw('material_id, SUM(in_qty - out_qty) as total')
            ->pluck('total', 'material_id')
            ->toArray();

        $periodTx = InventoryTransaction::whereBetween('date', [$startDate, $endDate])
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->whereNotNull('material_id')
            ->groupBy('material_id')
            ->selectRaw('material_id, SUM(in_qty) as total_in, SUM(out_qty) as total_out')
            ->get()
            ->keyBy('material_id');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Xuat_Nhap_Ton');

        $sheetTitle = 'BÁO CÁO TỔNG HỢP XUẤT - NHẬP - TỒN' . ($selectedWarehouse ? ' (' . mb_strtoupper($selectedWarehouse->name) . ')' : '');
        $sheet->setCellValue('A1', $sheetTitle);
        $subTitle = 'Từ ngày: ' . date('d/m/Y', strtotime($startDate)) . ' đến ngày: ' . date('d/m/Y', strtotime($endDate));
        if ($selectedWarehouse) {
            $subTitle .= ' | Kho: ' . $selectedWarehouse->name;
        }
        $sheet->setCellValue('A2', $subTitle);
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);
        $sheet->getStyle('A2')->getFont()->setSize(11)->setItalic(true);

        $headers = ['STT', 'Mã vật tư (SKU)', 'Mã gốc NCC/TQ', 'Tên mặt hàng / Quy cách', 'ĐVT', 'Tồn đầu kỳ', 'Nhập trong kỳ', 'Xuất trong kỳ', 'Tồn cuối kỳ'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
        foreach ($headers as $idx => $h) {
            $sheet->setCellValue($cols[$idx] . '4', $h);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']]
        ];
        $sheet->getStyle('A4:I4')->applyFromArray($headerStyle);

        $row = 5;
        foreach ($materials as $idx => $m) {
            $startStock = (float)($startStockMap[$m->id] ?? 0);
            $inQty = isset($periodTx[$m->id]) ? (float)$periodTx[$m->id]->total_in : 0.0;
            $outQty = isset($periodTx[$m->id]) ? (float)$periodTx[$m->id]->total_out : 0.0;
            $endStock = $startStock + $inQty - $outQty;

            if ($startStock == 0 && $inQty == 0 && $outQty == 0 && (float)$m->current_stock > 0) {
                $endStock = (float)$m->current_stock;
                $startStock = (float)$m->current_stock;
            }

            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $m->code);
            $sheet->setCellValue('C' . $row, $m->origin ?: '-');
            $sheet->setCellValue('D' . $row, $m->name);
            $sheet->setCellValue('E' . $row, $m->unit ?: ($isEdgeBand ? 'm' : 'Tấm'));
            $sheet->setCellValue('F' . $row, $startStock);
            $sheet->setCellValue('G' . $row, $inQty);
            $sheet->setCellValue('H' . $row, $outQty);
            $sheet->setCellValue('I' . $row, $endStock);
            $row++;
        }

        // Summary row
        $sheet->setCellValue('A' . $row, 'TỔNG CỘNG:');
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->setCellValue('F' . $row, "=SUM(F5:F" . ($row - 1) . ")");
        $sheet->setCellValue('G' . $row, "=SUM(G5:G" . ($row - 1) . ")");
        $sheet->setCellValue('H' . $row, "=SUM(H5:H" . ($row - 1) . ")");
        $sheet->setCellValue('I' . $row, "=SUM(I5:I" . ($row - 1) . ")");
        $sheet->getStyle("A{$row}:I{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        $sheet->getStyle("A4:I{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("F5:I{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Bao_Cao_XNT_' . ($selectedWarehouse ? \Illuminate\Support\Str::slug($selectedWarehouse->name) . '_' : '') . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Xuất Excel Ma trận Tiêu thụ 12 tháng chuẩn kế toán theo từng kho
     */
    public function exportMatrixReport(Request $request)
    {
        $year = (int)$request->input('year', date('Y'));
        $warehouseId = $request->input('warehouse_id');
        $selectedWarehouse = $warehouseId ? Warehouse::find($warehouseId) : null;
        $isEdgeBand = $selectedWarehouse && (str_contains(strtoupper($selectedWarehouse->code), 'CHI') || str_contains(mb_strtolower($selectedWarehouse->name), 'chỉ'));

        $materialsQuery = Material::with('warehouse');
        if ($warehouseId) {
            $materialsQuery->where('warehouse_id', $warehouseId);
        }
        $materials = $materialsQuery->orderBy('code')->get();

        $initYearStockMap = InventoryTransaction::where('date', '<', $year . '-01-01')
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->whereNotNull('material_id')
            ->groupBy('material_id')
            ->selectRaw('material_id, SUM(in_qty - out_qty) as total')
            ->pluck('total', 'material_id')
            ->toArray();

        $monthTxData = InventoryTransaction::whereYear('date', $year)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->whereNotNull('material_id')
            ->groupBy('material_id', DB::raw('MONTH(date)'))
            ->selectRaw('material_id, MONTH(date) as m, SUM(in_qty) as total_in, SUM(out_qty) as total_out')
            ->get();

        $matMonthlyMap = [];
        foreach ($monthTxData as $item) {
            $matMonthlyMap[$item->material_id][$item->m] = [
                'in'  => (float)$item->total_in,
                'out' => (float)$item->total_out,
            ];
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ma_Tran_Tieu_Thu_' . $year);

        $title = "BÁO CÁO MA TRẬN NHẬP - XUẤT - TỒN 12 THÁNG NĂM {$year}";
        if ($selectedWarehouse) {
            $title .= ' - KHO: ' . mb_strtoupper($selectedWarehouse->name);
        }
        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);

        // Header Rows (Row 3 & 4)
        $sheet->setCellValue('A3', 'STT');
        $sheet->mergeCells('A3:A4');
        $sheet->setCellValue('B3', 'Mã vật tư');
        $sheet->mergeCells('B3:B4');
        $sheet->setCellValue('C3', 'Mã NCC/TQ');
        $sheet->mergeCells('C3:C4');
        $sheet->setCellValue('D3', 'Tên hàng / Quy cách');
        $sheet->mergeCells('D3:D4');
        $sheet->setCellValue('E3', 'Tồn đầu ' . $year);
        $sheet->mergeCells('E3:E4');

        $currentColIdx = 6;
        for ($m = 1; $m <= 12; $m++) {
            $colLetter1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIdx);
            $colLetter3 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIdx + 2);
            $sheet->mergeCells("{$colLetter1}3:{$colLetter3}3");
            $sheet->setCellValue("{$colLetter1}3", 'Tháng ' . $m);

            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIdx) . '4', 'Nhận');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIdx + 1) . '4', 'Tiêu thụ');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIdx + 2) . '4', 'Tồn kho');
            $currentColIdx += 3;
        }

        // CẢ NĂM
        $yearCol1 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIdx);
        $yearCol3 = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIdx + 2);
        $sheet->mergeCells("{$yearCol1}3:{$yearCol3}3");
        $sheet->setCellValue("{$yearCol1}3", 'CẢ NĂM ' . $year);
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIdx) . '4', 'Tổng nhận');
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIdx + 1) . '4', 'Tổng xuất');
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIdx + 2) . '4', 'Tồn cuối');

        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIdx + 2);
        $sheet->mergeCells("A1:{$lastColLetter}1");

        // Styling headers
        $sheet->getStyle("A3:{$lastColLetter}4")->getFont()->setBold(true);
        $sheet->getStyle("A3:{$lastColLetter}4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A3:{$lastColLetter}4")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B');
        $sheet->getStyle("A3:{$lastColLetter}4")->getFont()->getColor()->setARGB('FFFFFFFF');

        $row = 5;
        foreach ($materials as $idx => $m) {
            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $m->code);
            $sheet->setCellValue('C' . $row, $m->origin ?: '-');
            $sheet->setCellValue('D' . $row, $m->name);

            $initYearStock = (float)($initYearStockMap[$m->id] ?? 0);
            if ($initYearStock == 0 && empty($matMonthlyMap[$m->id]) && (float)$m->current_stock > 0 && $year == (int)date('Y')) {
                $initYearStock = (float)$m->current_stock;
            }
            $sheet->setCellValue('E' . $row, $initYearStock);

            $running = $initYearStock;
            $cIdx = 6;
            $yearIn = 0;
            $yearOut = 0;

            for ($month = 1; $month <= 12; $month++) {
                $mIn = (float)($matMonthlyMap[$m->id][$month]['in'] ?? 0);
                $mOut = (float)($matMonthlyMap[$m->id][$month]['out'] ?? 0);
                $running = $running + $mIn - $mOut;

                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx) . $row, $mIn > 0 ? $mIn : '-');
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1) . $row, $mOut > 0 ? $mOut : '-');
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 2) . $row, $running);

                $yearIn += $mIn;
                $yearOut += $mOut;
                $cIdx += 3;
            }

            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx) . $row, $yearIn > 0 ? $yearIn : '-');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1) . $row, $yearOut > 0 ? $yearOut : '-');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 2) . $row, $running);

            $row++;
        }

        $sheet->getStyle("A3:{$lastColLetter}" . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("E5:{$lastColLetter}" . ($row - 1))->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(26);
        $sheet->getColumnDimension('E')->setWidth(14);
        for ($c = 6; $c <= $currentColIdx + 2; $c++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c))->setWidth(10);
        }

        $fileName = "Ma_Tran_Tieu_Thu_{$year}_" . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    

    

    /**
     * 6. Danh sách Kho hàng
     */
    public function warehouseList(Request $request)
    {
        $warehouses = Warehouse::withCount(['woodBoards as materials_count'])->orderBy('name')->get();
        return view('inventory.warehouses.index', compact('warehouses'));
    }

    public function storeWarehouse(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'manager' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        Warehouse::create([
            'name' => trim($request->name),
            'code' => trim($request->code) ?: ('KHO-' . strtoupper(uniqid())),
            'address' => $request->address,
            'manager' => $request->manager,
            'description' => $request->description,
            'status' => 'active',
        ]);

        return back()->with('success', 'Thêm mới kho hàng thành công!');
    }

    public function updateWarehouse(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'manager' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string',
        ]);

        $warehouse->update([
            'name' => trim($request->name),
            'code' => trim($request->code) ?: $warehouse->code,
            'address' => $request->address,
            'manager' => $request->manager,
            'status' => $request->status,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Cập nhật kho hàng thành công!');
    }

    public function destroyWarehouse(Warehouse $warehouse)
    {
        $name = $warehouse->name;
        
        // Tìm kho chuẩn để chuyển dữ liệu an toàn nếu kho này đang có vật tư/chứng từ
        $targetWh = Warehouse::where('id', '!=', $warehouse->id)->where('code', '155_EP_TAM')->first()
            ?? Warehouse::where('id', '!=', $warehouse->id)->where('code', '152_COT')->first()
            ?? Warehouse::where('id', '!=', $warehouse->id)->first();

        \Illuminate\Support\Facades\DB::transaction(function () use ($warehouse, $targetWh) {
            if ($targetWh) {
                \App\Models\Material::where('warehouse_id', $warehouse->id)->update([
                    'warehouse_id'   => $targetWh->id,
                    'warehouse_code' => $targetWh->code,
                ]);
                \App\Models\InventoryTransaction::where('warehouse_id', $warehouse->id)->update(['warehouse_id' => $targetWh->id]);
                \App\Models\InventoryReceipt::where('warehouse_id', $warehouse->id)->update(['warehouse_id' => $targetWh->id]);
                \App\Models\InventoryIssue::where('warehouse_id', $warehouse->id)->update(['warehouse_id' => $targetWh->id]);
                \App\Models\InventoryStocktake::where('warehouse_id', $warehouse->id)->update(['warehouse_id' => $targetWh->id]);
                \App\Models\WoodBoard::where('warehouse_id', $warehouse->id)->update(['warehouse_id' => $targetWh->id]);
            }

            $warehouse->delete();
        });

        $msg = "Đã xóa kho {$name} thành công!";
        if ($targetWh) {
            $msg .= " (Dữ liệu vật tư/chứng từ liên quan đã được chuyển an toàn sang {$targetWh->name})";
        }

        return back()->with('success', $msg);
    }

    /**
     * Nhập tồn kho từ file Excel Ton_Kho_Tam_Van_
     */
    /**
     * Nhập tồn kho / danh mục vật tư từ file Excel (máy chủ hoặc tải lên từ máy tính)
     */
    public function importStock(Request $request)
    {
        $useServerFile = $request->boolean('use_server_file');
        $targetWarehouseId = $request->input('warehouse_id');
        $serverFile = $request->input('server_file', 'all_p_code_excels');

        if ($useServerFile) {
            // Chỉ khi nạp từ file máy chủ: Xóa bỏ các kho cũ tạm thời (KHO-VAN, KHO-CHI) và chuẩn hóa 24 kho hàng
            $deletedOldWhs = $this->inventoryService->cleanupOldWarehouses();
            $whSync = $this->inventoryService->ensureStandardWarehouses();

            try {
                // Trường hợp 1: Chọn nạp toàn bộ 24 kho từ thư mục p_code_excels
                if ($serverFile === 'all_p_code_excels' || ($serverFile === 'all' && empty($targetWarehouseId))) {
                    $result = $this->inventoryService->importAllWarehousesFromFolder();
                    $msg = "Đã đồng bộ 24 kho chuẩn và nạp thành công {$result['files_count']} file kho từ máy chủ! Tổng xử lý: {$result['total_items']} dòng (Thêm mới: {$result['created_count']}, Cập nhật: {$result['updated_count']}).";
                    return redirect()->route('inventory.index', $targetWarehouseId ? ['warehouse_id' => $targetWarehouseId] : [])
                        ->with('success', $msg);
                }

                // Trường hợp 2: Chọn file tổng mã.xlsx
                if ($serverFile === 'ma.xlsx') {
                    $filePath = base_path('mã.xlsx');
                    if (!file_exists($filePath)) {
                        $filePath = 'C:/Code/Gervin/mã.xlsx';
                    }
                    if (!file_exists($filePath)) {
                        return back()->withErrors(['excel_file' => 'Không tìm thấy file mã.xlsx trên máy chủ!']);
                    }
                    $result = $this->inventoryService->importMaterialsFromExcel($filePath, $targetWarehouseId ? (int)$targetWarehouseId : null);
                    $msg = "Đã nạp danh mục vật tư từ file tổng mã.xlsx thành công! Tổng xử lý: {$result['total_items']} dòng (Thêm mới: {$result['created_count']}, Cập nhật: {$result['updated_count']}).";
                    return redirect()->route('inventory.index', $targetWarehouseId ? ['warehouse_id' => $targetWarehouseId] : [])
                        ->with('success', $msg);
                }

                // Trường hợp 3: Chọn 1 file kho cụ thể trong thư mục p_code_excels
                $folder = \App\Services\InventoryService::getServerExcelFolderPath();
                $filePath = $folder ? ($folder . DIRECTORY_SEPARATOR . $serverFile) : null;
                if (!$filePath || !file_exists($filePath)) {
                    // Thử tìm theo mã kho nếu người dùng chọn kho đích
                    if ($targetWarehouseId) {
                        $targetWh = \App\Models\Warehouse::find($targetWarehouseId);
                        if ($targetWh) {
                            $safeWhName = preg_replace('/[\\\\\\/*?:"<>|]/', '_', $targetWh->code);
                            $candidatePath = $folder ? ($folder . DIRECTORY_SEPARATOR . $safeWhName . '.xlsx') : null;
                            if ($candidatePath && file_exists($candidatePath)) {
                                $filePath = $candidatePath;
                                $serverFile = $safeWhName . '.xlsx';
                            }
                        }
                    }
                }

                if (!$filePath || !file_exists($filePath)) {
                    return back()->withErrors(['excel_file' => "Không tìm thấy file kho {$serverFile} trên máy chủ!"]);
                }

                $result = $this->inventoryService->importMaterialsFromExcel($filePath, $targetWarehouseId ? (int)$targetWarehouseId : null);
                $msg = "Đã nạp thành công file {$serverFile}! Tổng xử lý: {$result['total_items']} dòng (Thêm mới: {$result['created_count']}, Cập nhật: {$result['updated_count']}).";

                return redirect()->route('inventory.index', $targetWarehouseId ? ['warehouse_id' => $targetWarehouseId] : [])
                    ->with('success', $msg);
            } catch (\Exception $e) {
                return back()->withErrors(['excel_file' => 'Lỗi khi nạp file từ máy chủ: ' . $e->getMessage()]);
            }
        } else {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls|max:30720',
            ], [
                'excel_file.required' => 'Vui lòng chọn file Excel để nhập danh mục vật tư!',
                'excel_file.mimes'    => 'File phải có định dạng .xlsx hoặc .xls!',
                'excel_file.max'      => 'Dung lượng file tối đa là 30MB!',
            ]);
            $filePath = $request->file('excel_file')->getRealPath();

            try {
                $result = $this->inventoryService->importMaterialsFromExcel($filePath, $targetWarehouseId ? (int)$targetWarehouseId : null);

                return redirect()->route('inventory.index', $targetWarehouseId ? ['warehouse_id' => $targetWarehouseId] : [])
                    ->with('success', "Nhập danh mục vật tư thành công! Tổng xử lý: {$result['total_items']} dòng (Thêm mới: {$result['created_count']}, Cập nhật: {$result['updated_count']}).");
            } catch (\Exception $e) {
                return back()->withErrors(['excel_file' => 'Lỗi khi nhập file Excel: ' . $e->getMessage()]);
            }
        }
    }
}
