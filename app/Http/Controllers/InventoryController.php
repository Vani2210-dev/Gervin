<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WoodBoard;
use App\Models\WoodBoardType;
use App\Models\DcStock;
use App\Models\InventoryReceipt;
use App\Models\InventoryReceiptItem;
use App\Models\InventoryIssue;
use App\Models\InventoryIssueItem;
use App\Models\InventoryStocktake;
use App\Models\InventoryStocktakeItem;
use App\Models\InventoryTransaction;
use App\Services\InventoryService;
use Illuminate\Http\Request;
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
            'updateMaterial', 'balanceStocktake', 'updateWarehouse'
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

        $warehouses = Warehouse::orderBy('name')->get();
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

        if ($isEdgeBandWarehouse) {
            // Khi xem Kho Dán Chỉ (KHO-CHI): Hiển thị từng cuộn / mã chỉ dán viền tương ứng
            $query = WoodBoard::with(['warehouse']);

            if ($selectedCategory) {
                $query->where('price_group', $selectedCategory);
            }

            if ($stockFilter === 'low_stock') {
                $query->whereColumn('edge_band_current_stock', '<=', 'edge_band_min_stock')->where('edge_band_current_stock', '>', 0);
            } elseif ($stockFilter === 'out_of_stock') {
                $query->where('edge_band_current_stock', '<=', 0);
            } elseif ($stockFilter === 'in_stock') {
                $query->where('edge_band_current_stock', '>', 0);
            }

            if ($startDate && $endDate && $dateMode !== 'all') {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereHas('transactions', function ($t) use ($startDate, $endDate) {
                        $t->whereBetween('date', [$startDate, $endDate])->where('item_type', 'edge_band');
                    })->orWhereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                });
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('color_code', 'like', "%{$search}%")
                      ->orWhere('origin_code', 'like', "%{$search}%")
                      ->orWhere('price_group', 'like', "%{$search}%");
                });
            }

            $materials = $query->orderBy('color_code')->paginate(50)->withQueryString();

            // Summary KPIs Kho Chỉ
            $totalItems = WoodBoard::count();
            $totalStockQty = (float)WoodBoard::sum('edge_band_current_stock');
            $totalStockValue = (float)(WoodBoard::selectRaw('SUM(edge_band_current_stock * edge_band_cost_price) as val')->value('val') ?? 0);
            $lowStockCount = WoodBoard::where(function ($q) {
                $q->whereColumn('edge_band_current_stock', '<=', 'edge_band_min_stock')->orWhere('edge_band_current_stock', '<=', 0);
            })->count();
        } else {
            // Khi xem Kho Tấm Ván hoặc Tất cả kho: Mỗi tên hàng hóa / loại ván (WoodBoardPrice) là 1 dòng độc lập
            $hasPrices = \App\Models\WoodBoardPrice::exists();

            if ($hasPrices) {
                $query = \App\Models\WoodBoardPrice::with(['board.warehouse', 'type'])
                    ->whereHas('board', function ($b) {
                        $b->where('status', 'active');
                    });

                if ($selectedCategory) {
                    $query->whereHas('board', function ($b) use ($selectedCategory) {
                        $b->where('price_group', $selectedCategory);
                    });
                }

                if ($selectedWarehouseId) {
                    $query->whereHas('board', function ($b) use ($selectedWarehouseId, $selectedWarehouse) {
                        $b->where(function ($w) use ($selectedWarehouseId, $selectedWarehouse) {
                            $w->where('warehouse_id', $selectedWarehouseId);
                            if (($selectedWarehouse && $selectedWarehouse->code === 'KHO-VAN') || $selectedWarehouseId == 1) {
                                $w->orWhereNull('warehouse_id');
                            }
                        });
                    });
                }

                if ($stockFilter === 'low_stock') {
                    $query->where('wood_board_prices.stock', '<=', 5)->where('wood_board_prices.stock', '>', 0);
                } elseif ($stockFilter === 'out_of_stock') {
                    $query->where('wood_board_prices.stock', '<=', 0);
                } elseif ($stockFilter === 'in_stock') {
                    $query->where('wood_board_prices.stock', '>', 0);
                }

                if ($startDate && $endDate && $dateMode !== 'all') {
                    $query->where(function ($q) use ($startDate, $endDate, $selectedWarehouseId) {
                        $q->whereHas('transactions', function ($t) use ($startDate, $endDate, $selectedWarehouseId) {
                            $t->whereBetween('date', [$startDate, $endDate]);
                            if ($selectedWarehouseId) {
                                $t->where('warehouse_id', $selectedWarehouseId);
                            }
                        })->orWhereBetween('wood_board_prices.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                    });
                }

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('wood_board_prices.code', 'like', "%{$search}%")
                          ->orWhere('wood_board_prices.name', 'like', "%{$search}%")
                          ->orWhere('wood_board_prices.foil_name', 'like', "%{$search}%")
                          ->orWhereHas('board', function ($b) use ($search) {
                              $b->where('color_code', 'like', "%{$search}%")
                                ->orWhere('origin_code', 'like', "%{$search}%")
                                ->orWhere('foil_name', 'like', "%{$search}%")
                                ->orWhere('price_group', 'like', "%{$search}%");
                          })
                          ->orWhereHas('type', function ($t) use ($search) {
                              $t->where('wood_board_types.name', 'like', "%{$search}%")
                                ->orWhere('wood_board_types.prefix', 'like', "%{$search}%");
                          });
                    });
                }

                $materials = $query->join('wood_boards', 'wood_board_prices.wood_board_id', '=', 'wood_boards.id')
                    ->leftJoin('wood_board_types', 'wood_board_prices.wood_board_type_id', '=', 'wood_board_types.id')
                    ->select('wood_board_prices.*')
                    ->orderBy('wood_boards.color_code', 'asc')
                    ->orderBy('wood_board_types.display_order', 'asc')
                    ->paginate(50)
                    ->withQueryString();

                // Summary KPIs
                $totalItems = \App\Models\WoodBoardPrice::count();
                $totalStockQty = (float)\App\Models\WoodBoardPrice::sum('stock');
                $totalStockValue = (float)(\App\Models\WoodBoardPrice::selectRaw('SUM(stock * price_board) as val')->value('val') ?? 0);
                $lowStockCount = \App\Models\WoodBoardPrice::where('stock', '<=', 5)->count();
            } else {
                $query = WoodBoard::with(['warehouse']);
                if ($selectedCategory) $query->where('price_group', $selectedCategory);
                if ($stockFilter === 'low_stock') $query->whereColumn('current_stock', '<=', 'min_stock')->where('current_stock', '>', 0);
                elseif ($stockFilter === 'out_of_stock') $query->where('current_stock', '<=', 0);
                elseif ($stockFilter === 'in_stock') $query->where('current_stock', '>', 0);
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('color_code', 'like', "%{$search}%")
                          ->orWhere('origin_code', 'like', "%{$search}%")
                          ->orWhere('price_group', 'like', "%{$search}%");
                    });
                }
                $materials = $query->orderBy('color_code')->paginate(50)->withQueryString();
                $totalItems = WoodBoard::count();
                $totalStockQty = (float)WoodBoard::sum('current_stock');
                $totalStockValue = (float)(WoodBoard::selectRaw('SUM(current_stock * cost_price) as val')->value('val') ?? 0);
                $lowStockCount = WoodBoard::where(function ($q) {
                    $q->whereColumn('current_stock', '<=', 'min_stock')->orWhere('current_stock', '<=', 0);
                })->count();
            }
        }

        $categories = WoodBoard::select('price_group as category')->whereNotNull('price_group')->distinct()->pluck('category');

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
        $code = trim($request->color_code ?: $request->code);
        $category = trim($request->price_group ?: $request->category);

        $request->validate([
            'color_code' => 'nullable|string|max:100',
            'code' => 'nullable|string|max:100',
            'origin_code' => 'nullable|string|max:100',
            'foil_name' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'cost_price' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'initial_stock' => 'nullable|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'notes' => 'nullable|string',
            'edge_band_unit' => 'nullable|string|max:50',
            'edge_band_current_stock' => 'nullable|numeric|min:0',
            'edge_band_initial_stock' => 'nullable|numeric|min:0',
            'edge_band_cost_price' => 'nullable|numeric|min:0',
            'edge_band_min_stock' => 'nullable|numeric|min:0',
        ]);

        if (!$code) {
            return back()->withErrors(['code' => 'Vui lòng nhập mã màu ván!']);
        }

        if (WoodBoard::where('color_code', $code)->exists()) {
            return back()->withErrors(['code' => 'Mã màu ván này đã tồn tại trong hệ thống!']);
        }

        $initStock = (float)($request->initial_stock ?? 0);
        $edgeInitStock = (float)($request->edge_band_initial_stock ?? $request->edge_band_current_stock ?? 0);

        $board = WoodBoard::create([
            'color_code' => $code,
            'origin_code' => trim($request->origin_code),
            'foil_name' => trim($request->foil_name) ?: null,
            'price_group' => $category ?: 'Tấm cốt gỗ / Acrylic',
            'unit' => trim($request->unit) ?: 'Tấm',
            'cost_price' => (float)($request->cost_price ?? 0),
            'min_stock' => (float)($request->min_stock ?? 50),
            'initial_stock' => $initStock,
            'current_stock' => $initStock,
            'warehouse_id' => $request->warehouse_id,
            'status' => 'active',
            'notes' => $request->notes,
            'edge_band_unit' => trim($request->edge_band_unit) ?: 'm',
            'edge_band_cost_price' => (float)($request->edge_band_cost_price ?? 0),
            'edge_band_min_stock' => (float)($request->edge_band_min_stock ?? 100),
            'edge_band_initial_stock' => $edgeInitStock,
            'edge_band_current_stock' => $edgeInitStock,
        ]);

        if ($initStock > 0) {
            $vanWarehouse = Warehouse::where('code', 'KHO-VAN')->first() ?? Warehouse::first();
            InventoryTransaction::create([
                'warehouse_id' => $board->warehouse_id ?? $vanWarehouse->id,
                'item_type' => 'board',
                'wood_board_id' => $board->id,
                'date' => date('Y-m-d'),
                'type' => 'initial',
                'voucher_code' => 'DK-' . date('Y'),
                'in_qty' => $initStock,
                'out_qty' => 0,
                'stock_after' => $initStock,
                'notes' => 'Số dư tồn kho tấm ban đầu',
                'created_by' => Auth::id(),
            ]);
        }

        if ($edgeInitStock > 0) {
            $chiWarehouse = Warehouse::where('code', 'KHO-CHI')->first() ?? $board->warehouse_id;
            InventoryTransaction::create([
                'warehouse_id' => $chiWarehouse->id,
                'item_type' => 'edge_band',
                'wood_board_id' => $board->id,
                'date' => date('Y-m-d'),
                'type' => 'initial',
                'voucher_code' => 'DK-CHI-' . date('Y'),
                'in_qty' => $edgeInitStock,
                'out_qty' => 0,
                'stock_after' => $edgeInitStock,
                'notes' => 'Số dư tồn kho chỉ ban đầu',
                'created_by' => Auth::id(),
            ]);
        }

        return back()->with('success', 'Thêm mới tấm ván & chỉ dán ' . $board->color_code . ' thành công!');
    }

    /**
     * Update wood board or individual commodity price
     */
    public function updateMaterial(Request $request, $id)
    {
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
                $price->stock = (float)$request->stock;
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

        // 2. Cập nhật WoodBoard (tấm ván hoặc chỉ dán viền)
        $material = WoodBoard::findOrFail($id);

        $request->validate([
            'origin_code' => 'nullable|string|max:100',
            'foil_name' => 'nullable|string|max:255',
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
     * Delete wood board or price
     */
    public function destroyMaterial($id)
    {
        $price = \App\Models\WoodBoardPrice::find($id);
        if ($price) {
            $code = $price->code ?: $price->name;
            $price->transactions()->delete();
            $price->delete();
            return back()->with('success', 'Đã xóa hàng hóa ' . $code . ' khỏi kho thành công!');
        }

        $material = WoodBoard::findOrFail($id);
        $code = $material->color_code;
        $material->transactions()->delete();
        $material->prices()->delete();
        $material->delete();
        return back()->with('success', 'Đã xóa tấm ' . $code . ' khỏi kho thành công!');
    }

    /**
     * Thẻ kho (Stock Card - lịch sử biến động của 1 mã hàng hóa / tấm / chỉ)
     */
    public function stockCard(Request $request, $id)
    {
        $type = $request->input('type');

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
     * Export Stock List to Excel
     */
    public function exportStock(Request $request)
    {
        $selectedWarehouseId = $request->input('warehouse_id');
        $selectedWarehouse = $selectedWarehouseId ? Warehouse::find($selectedWarehouseId) : null;
        $isEdgeBandWarehouse = $selectedWarehouse && ($selectedWarehouse->code === 'KHO-CHI' || str_contains(mb_strtolower($selectedWarehouse->name), 'chỉ'));

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Danh_Sach_Ton_Kho');

        if ($isEdgeBandWarehouse) {
            $materials = WoodBoard::orderBy('color_code')->get();
            $sheet->setCellValue('A1', 'BÁO CÁO TỒN KHO CHỈ DÁN VIỀN - ' . date('d/m/Y H:i'));
            $sheet->mergeCells('A1:I1');
            $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);

            $headers = ['STT', 'Mã SKU Chỉ', 'Tên mặt hàng', 'Mã xuất xứ', 'Nhóm hàng', 'ĐVT', 'Tồn kho thực tế', 'Giá vốn (đ)', 'Giá trị tồn (đ)'];
            $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
            foreach ($headers as $idx => $h) {
                $sheet->setCellValue($cols[$idx] . '3', $h);
            }

            $row = 4;
            foreach ($materials as $i => $m) {
                $sheet->setCellValue('A' . $row, $i + 1);
                $sheet->setCellValue('B' . $row, $m->edge_band_code);
                $sheet->setCellValue('C' . $row, $m->edge_band_name);
                $sheet->setCellValue('D' . $row, $m->origin_code ?: '-');
                $sheet->setCellValue('E' . $row, 'Chỉ dán viền');
                $sheet->setCellValue('F' . $row, $m->edge_band_unit ?: 'm');
                $sheet->setCellValue('G' . $row, (float)$m->edge_band_current_stock);
                $sheet->setCellValue('H' . $row, (float)$m->edge_band_cost_price);
                $sheet->setCellValue('I' . $row, "=G{$row}*H{$row}");
                $row++;
            }
        } else {
            $prices = \App\Models\WoodBoardPrice::with(['board', 'type'])
                ->join('wood_boards', 'wood_board_prices.wood_board_id', '=', 'wood_boards.id')
                ->leftJoin('wood_board_types', 'wood_board_prices.wood_board_type_id', '=', 'wood_board_types.id')
                ->select('wood_board_prices.*')
                ->orderBy('wood_boards.color_code', 'asc')
                ->orderBy('wood_board_types.display_order', 'asc')
                ->get();

            $sheet->setCellValue('A1', 'BÁO CÁO TỒN KHO VẬT TƯ / HÀNG HÓA - ' . date('d/m/Y H:i'));
            $sheet->mergeCells('A1:I1');
            $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);

            $headers = ['STT', 'Mã SKU', 'Tên hàng hóa / vật tư', 'Mã xuất xứ', 'Nhóm hàng', 'ĐVT', 'Tồn kho thực tế', 'Giá vốn (đ)', 'Giá trị tồn (đ)'];
            $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
            foreach ($headers as $idx => $h) {
                $sheet->setCellValue($cols[$idx] . '3', $h);
            }

            $row = 4;
            foreach ($prices as $i => $p) {
                $sku = $p->code ?: ($p->board?->color_code . ($p->type?->prefix ?? ''));
                $name = 'Tấm ' . ($p->board?->color_code ?? '') . ' - ' . ($p->name ?: $p->type?->name);
                $origin = $p->board?->origin_code ?: '-';
                $category = $p->type?->name ?? $p->board?->price_group ?? 'Tấm cốt gỗ';
                $unit = $p->board?->unit ?: 'Tấm';
                $stock = (float)$p->stock;
                $cost = (float)$p->price_board;

                $sheet->setCellValue('A' . $row, $i + 1);
                $sheet->setCellValue('B' . $row, $sku);
                $sheet->setCellValue('C' . $row, $name);
                $sheet->setCellValue('D' . $row, $origin);
                $sheet->setCellValue('E' . $row, $category);
                $sheet->setCellValue('F' . $row, $unit);
                $sheet->setCellValue('G' . $row, $stock);
                $sheet->setCellValue('H' . $row, $cost);
                $sheet->setCellValue('I' . $row, "=G{$row}*H{$row}");
                $row++;
            }
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']]
        ];
        $sheet->getStyle('A3:I3')->applyFromArray($headerStyle);

        // Summary row
        $sheet->setCellValue('A' . $row, 'TỔNG CỘNG:');
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue('G' . $row, "=SUM(G4:G" . ($row - 1) . ")");
        $sheet->setCellValue('I' . $row, "=SUM(I4:I" . ($row - 1) . ")");
        $sheet->getStyle("A{$row}:I{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        $sheet->getStyle("A3:I{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("G4:G{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("H4:I{$row}")->getNumberFormat()->setFormatCode('#,##0');

        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Ton_Kho_Tam_Van_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * 2. Phiếu Nhập kho (Goods Receipts)
     */
    public function receipts(Request $request)
    {
        $query = InventoryReceipt::with(['warehouse', 'creator'])->withCount('items');
        $this->applyDateFilter($query, $request, 'date');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhere('deliverer', 'like', "%{$search}%")
                  ->orWhere('receiver', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $receipts = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(20)->withQueryString();

        return view('inventory.receipts.index', compact('receipts'));
    }

    public function createReceipt(Request $request)
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $materials = $this->getSelectableMaterials();
        $nextCode = $this->inventoryService->generateCode('PNK', 'inventory_receipts');

        return view('inventory.receipts.create', compact('warehouses', 'materials', 'nextCode'));
    }

    public function storeReceipt(Request $request)
    {
        $request->validate([
            'warehouse_id'        => 'required|exists:warehouses,id',
            'date'                => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.material_id' => 'required',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'nullable|numeric|min:0',
        ]);

        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        $receipt = $this->inventoryService->createReceipt(
            $warehouse,
            $request->only(['code', 'date', 'supplier_name', 'deliverer', 'receiver', 'notes']),
            $request->input('items', []),
            Auth::id()
        );

        return redirect()->route('inventory.receipts.show', $receipt)->with('success', 'Tạo phiếu nhập kho ' . $receipt->code . ' thành công!');
    }

    public function showReceipt(InventoryReceipt $receipt)
    {
        $receipt->load(['warehouse', 'creator', 'items.woodBoard', 'items.boardPrice.type']);
        return view('inventory.receipts.show', compact('receipt'));
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
        $query = InventoryIssue::with(['warehouse', 'creator'])->withCount('items');
        $this->applyDateFilter($query, $request, 'date');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('recipient', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $issues = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(20)->withQueryString();

        return view('inventory.issues.index', compact('issues'));
    }

    public function createIssue(Request $request)
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $materials = $this->getSelectableMaterials();
        $nextCode = $this->inventoryService->generateCode('PXK', 'inventory_issues');

        return view('inventory.issues.create', compact('warehouses', 'materials', 'nextCode'));
    }

    public function storeIssue(Request $request)
    {
        $request->validate([
            'warehouse_id'        => 'required|exists:warehouses,id',
            'date'                => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.material_id' => 'required',
            'items.*.quantity'    => 'required|numeric|min:0.01',
        ]);

        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        $issue = $this->inventoryService->createIssue(
            $warehouse,
            $request->only(['code', 'date', 'recipient', 'deliverer', 'reason', 'notes']),
            $request->input('items', []),
            Auth::id()
        );

        return redirect()->route('inventory.issues.show', $issue)->with('success', 'Tạo phiếu xuất kho ' . $issue->code . ' thành công!');
    }

    public function showIssue(InventoryIssue $issue)
    {
        $issue->load(['warehouse', 'creator', 'items.woodBoard', 'items.boardPrice.type']);
        return view('inventory.issues.show', compact('issue'));
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

        $warehouses = Warehouse::orderBy('name')->get();
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
     * Lấy danh sách mặt hàng có thể chọn (WoodBoardPrice kết hợp WoodBoard & Chỉ dán viền)
     */
    private function getSelectableMaterials($warehouseId = null)
    {
        $selectedWh = $warehouseId ? Warehouse::find($warehouseId) : null;
        $isEdgeBand = $selectedWh && ($selectedWh->code === 'KHO-CHI' || str_contains(mb_strtolower($selectedWh->name), 'chỉ'));
        $isBoardOnly = $selectedWh && ($selectedWh->code === 'KHO-VAN' || str_contains(mb_strtolower($selectedWh->name), 'ván'));

        $result = collect();

        // 1. Wood Board items (cho Kho Tấm Ván hoặc tất cả)
        if (!$isEdgeBand) {
            $prices = \App\Models\WoodBoardPrice::with(['board', 'type'])
                ->whereHas('board', function($q) { $q->where('status', 'active'); })
                ->orderBy('code')
                ->get();

            if ($prices->isNotEmpty()) {
                $boardItems = $prices->map(function ($p) {
                    $typeName = $p->type?->name ?? '';
                    $color = $p->board?->color_code ?? '';
                    $group = $p->board?->price_group ?? '';
                    return (object)[
                        'id'                  => 'board_' . $p->id,
                        'raw_id'              => $p->id,
                        'wood_board_id'       => $p->wood_board_id,
                        'wood_board_price_id' => $p->id,
                        'item_type'           => 'board',
                        'warehouse_code'      => 'KHO-VAN',
                        'code'                => $p->code,
                        'name'                => ($p->name ?: $typeName) . ($color ? " (Màu {$color})" : ''),
                        'origin_code'         => $p->board?->origin_code ?? '',
                        'category'            => $group ?: $typeName,
                        'unit'                => 'Tấm',
                        'cost_price'          => (float)($p->price_board ?: 0),
                        'current_stock'       => (float)($p->stock ?: 0),
                        'thickness'           => $p->thickness ?: '17mm',
                    ];
                });
                $result = $result->merge($boardItems);
            } else {
                $boards = WoodBoard::where('status', 'active')->orderBy('price_group')->orderBy('color_code')->get();
                $boardItems = $boards->map(function ($b) {
                    return (object)[
                        'id'                  => 'board_' . $b->id,
                        'raw_id'              => $b->id,
                        'wood_board_id'       => $b->id,
                        'wood_board_price_id' => null,
                        'item_type'           => 'board',
                        'warehouse_code'      => 'KHO-VAN',
                        'code'                => $b->color_code,
                        'name'                => 'Tấm ' . $b->color_code,
                        'origin_code'         => $b->origin_code ?? '',
                        'category'            => $b->price_group ?: 'Tấm cốt gỗ',
                        'unit'                => $b->unit ?: 'Tấm',
                        'cost_price'          => (float)($b->cost_price ?: 0),
                        'current_stock'       => (float)($b->current_stock ?: 0),
                        'thickness'           => '17mm',
                    ];
                });
                $result = $result->merge($boardItems);
            }
        }

        // 2. Edge Band items (Mỗi tấm ván trong hệ thống đều có chỉ dán viền tương ứng trong Kho Dán Chỉ!)
        if (!$isBoardOnly) {
            $boards = WoodBoard::where('status', 'active')->orderBy('color_code')->get();
            $edgeItems = $boards->map(function ($b) {
                return (object)[
                    'id'                  => 'edge_' . $b->id,
                    'raw_id'              => $b->id,
                    'wood_board_id'       => $b->id,
                    'wood_board_price_id' => null,
                    'item_type'           => 'edge_band',
                    'warehouse_code'      => 'KHO-CHI',
                    'code'                => $b->edge_band_code,
                    'name'                => $b->edge_band_name,
                    'origin_code'         => $b->origin_code ?? '',
                    'category'            => 'Chỉ dán viền (' . ($b->price_group ?: 'Tiêu chuẩn') . ')',
                    'unit'                => $b->edge_band_unit ?: 'm',
                    'cost_price'          => (float)($b->edge_band_cost_price ?: 0),
                    'current_stock'       => (float)($b->edge_band_current_stock ?: 0),
                    'thickness'           => '',
                ];
            });
            $result = $result->merge($edgeItems);
        }

        return $result;
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
        $tab = $request->input('tab', 'summary'); // 'summary' or 'matrix'
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

        $warehouses = Warehouse::orderBy('name')->get();
        $selectedWarehouse = $warehouseId ? Warehouse::find($warehouseId) : null;
        $isEdgeBand = $selectedWarehouse && ($selectedWarehouse->code === 'KHO-CHI' || str_contains(mb_strtolower($selectedWarehouse->name), 'chỉ'));

        // Query wood boards (nguồn chuẩn: wood_boards)
        $materials = WoodBoard::with('warehouse')->orderBy('price_group')->orderBy('color_code')->get();

        // Tab 1: Xuất Nhập Tồn tổng hợp
        $reportData = [];

        foreach ($materials as $m) {
            $sku = $isEdgeBand ? $m->edge_band_code : $m->color_code;
            $name = $isEdgeBand ? $m->edge_band_name : ('Tấm ' . $m->color_code);
            $unit = $isEdgeBand ? ($m->edge_band_unit ?: 'm') : $m->unit;

            $inQuery = InventoryTransaction::where('wood_board_id', $m->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('in_qty', '>', 0);
            if ($warehouseId) {
                $inQuery->where('warehouse_id', $warehouseId);
            }
            if ($isEdgeBand) {
                $inQuery->where('item_type', 'edge_band');
            } else {
                $inQuery->where(function($q) {
                    $q->where('item_type', 'board')->orWhereNull('item_type');
                });
            }
            $inQty = (float)$inQuery->sum('in_qty');

            $outQuery = InventoryTransaction::where('wood_board_id', $m->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('out_qty', '>', 0);
            if ($warehouseId) {
                $outQuery->where('warehouse_id', $warehouseId);
            }
            if ($isEdgeBand) {
                $outQuery->where('item_type', 'edge_band');
            } else {
                $outQuery->where(function($q) {
                    $q->where('item_type', 'board')->orWhereNull('item_type');
                });
            }
            $outQty = (float)$outQuery->sum('out_qty');

            $startQuery = InventoryTransaction::where('wood_board_id', $m->id)
                ->where('date', '<', $startDate);
            if ($warehouseId) {
                $startQuery->where('warehouse_id', $warehouseId);
            }
            if ($isEdgeBand) {
                $startQuery->where('item_type', 'edge_band');
            } else {
                $startQuery->where(function($q) {
                    $q->where('item_type', 'board')->orWhereNull('item_type');
                });
            }
            $startStock = (float)$startQuery->sum(DB::raw('in_qty - out_qty'));

            $endStock = $startStock + $inQty - $outQty;

            $reportData[] = [
                'material'    => $m,
                'sku'         => $sku,
                'name'        => $name,
                'unit'        => $unit,
                'is_edge_band'=> $isEdgeBand,
                'start_stock' => $startStock,
                'in_qty'      => $inQty,
                'out_qty'     => $outQty,
                'end_stock'   => $endStock,
            ];
        }

        // Tab 2: Monthly Matrix Report (12 months for $year)
        $matrixRows = [];
        $colTotals = [
            'start_stock' => 0,
            'total_in' => 0,
            'total_out' => 0,
            'final_stock' => 0,
            'months' => []
        ];
        for ($m = 1; $m <= 12; $m++) {
            $colTotals['months'][$m] = ['in' => 0, 'out' => 0, 'stock' => 0];
        }

        foreach ($materials as $idx => $m) {
            $sku = $isEdgeBand ? $m->edge_band_code : $m->color_code;
            $name = $isEdgeBand ? $m->edge_band_name : ('Tấm ' . $m->color_code);
            $unit = $isEdgeBand ? ($m->edge_band_unit ?: 'm') : $m->unit;

            $initYearQuery = InventoryTransaction::where('wood_board_id', $m->id)
                ->where('date', '<', $year . '-01-01');
            if ($warehouseId) {
                $initYearQuery->where('warehouse_id', $warehouseId);
            }
            if ($isEdgeBand) {
                $initYearQuery->where('item_type', 'edge_band');
            } else {
                $initYearQuery->where(function($q) {
                    $q->where('item_type', 'board')->orWhereNull('item_type');
                });
            }
            $initYearStock = (float)$initYearQuery->sum(DB::raw('in_qty - out_qty'));

            $running = $initYearStock;
            $rowMonths = [];
            $totalYearIn = 0;
            $totalYearOut = 0;

            for ($month = 1; $month <= 12; $month++) {
                $mStart = sprintf('%04d-%02d-01', $year, $month);
                $mEnd = date('Y-m-t', strtotime($mStart));

                $mInQuery = InventoryTransaction::where('wood_board_id', $m->id)
                    ->whereBetween('date', [$mStart, $mEnd]);
                if ($warehouseId) {
                    $mInQuery->where('warehouse_id', $warehouseId);
                }
                if ($isEdgeBand) {
                    $mInQuery->where('item_type', 'edge_band');
                } else {
                    $mInQuery->where(function($q) {
                        $q->where('item_type', 'board')->orWhereNull('item_type');
                    });
                }
                $mIn = (float)$mInQuery->sum('in_qty');

                $mOutQuery = InventoryTransaction::where('wood_board_id', $m->id)
                    ->whereBetween('date', [$mStart, $mEnd]);
                if ($warehouseId) {
                    $mOutQuery->where('warehouse_id', $warehouseId);
                }
                if ($isEdgeBand) {
                    $mOutQuery->where('item_type', 'edge_band');
                } else {
                    $mOutQuery->where(function($q) {
                        $q->where('item_type', 'board')->orWhereNull('item_type');
                    });
                }
                $mOut = (float)$mOutQuery->sum('out_qty');

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
                'code'          => $sku,
                'name'          => $name,
                'unit'          => $unit,
                'is_edge_band'  => $isEdgeBand,
                'origin_code'   => $m->origin_code,
                'category'      => $m->price_group,
                'start_stock'   => $initYearStock,
                'months'        => $rowMonths,
                'total_year_in' => $totalYearIn,
                'total_year_out'=> $totalYearOut,
                'final_stock'   => $running,
                'stock_status'  => $m->stock_status,
            ];

            $colTotals['start_stock'] += $initYearStock;
            $colTotals['total_in'] += $totalYearIn;
            $colTotals['total_out'] += $totalYearOut;
            $colTotals['final_stock'] += $running;
        }

        $yearsQuery = InventoryTransaction::selectRaw('DISTINCT YEAR(date) as y')
            ->whereNotNull('date');
        if ($warehouseId) {
            $yearsQuery->where('warehouse_id', $warehouseId);
        }
        $availableYears = $yearsQuery->orderByDesc('y')->pluck('y')->toArray();

        if (empty($availableYears)) $availableYears = [date('Y')];

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
            'availableYears'
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
        $isEdgeBand = $selectedWarehouse && ($selectedWarehouse->code === 'KHO-CHI' || str_contains(mb_strtolower($selectedWarehouse->name), 'chỉ'));

        $materials = WoodBoard::with('warehouse')->orderBy('price_group')->orderBy('color_code')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Xuat_Nhap_Ton');

        $sheetTitle = 'BÁO CÁO TỔNG HỢP XUẤT - NHẬP - TỒN' . ($isEdgeBand ? ' (KHO DÁN CHỈ)' : ($warehouseId ? ' (KHO TẤM VÁN)' : ''));
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

        $headers = ['STT', $isEdgeBand ? 'Mã chỉ dán' : 'Mã màu ván', 'Mã gốc TQ', 'Tên mặt hàng', 'ĐVT', 'Tồn đầu kỳ', 'Nhập trong kỳ', 'Xuất trong kỳ', 'Tồn cuối kỳ'];
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
            $sku = $isEdgeBand ? $m->edge_band_code : $m->color_code;
            $name = $isEdgeBand ? $m->edge_band_name : ('Tấm ' . $m->color_code);
            $unit = $isEdgeBand ? ($m->edge_band_unit ?: 'm') : $m->unit;

            $inQuery = InventoryTransaction::where('wood_board_id', $m->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('in_qty', '>', 0);
            if ($warehouseId) {
                $inQuery->where('warehouse_id', $warehouseId);
            }
            if ($isEdgeBand) {
                $inQuery->where('item_type', 'edge_band');
            } else {
                $inQuery->where(function($q) {
                    $q->where('item_type', 'board')->orWhereNull('item_type');
                });
            }
            $inQty = (float)$inQuery->sum('in_qty');

            $outQuery = InventoryTransaction::where('wood_board_id', $m->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('out_qty', '>', 0);
            if ($warehouseId) {
                $outQuery->where('warehouse_id', $warehouseId);
            }
            if ($isEdgeBand) {
                $outQuery->where('item_type', 'edge_band');
            } else {
                $outQuery->where(function($q) {
                    $q->where('item_type', 'board')->orWhereNull('item_type');
                });
            }
            $outQty = (float)$outQuery->sum('out_qty');

            $startQuery = InventoryTransaction::where('wood_board_id', $m->id)
                ->where('date', '<', $startDate);
            if ($warehouseId) {
                $startQuery->where('warehouse_id', $warehouseId);
            }
            if ($isEdgeBand) {
                $startQuery->where('item_type', 'edge_band');
            } else {
                $startQuery->where(function($q) {
                    $q->where('item_type', 'board')->orWhereNull('item_type');
                });
            }
            $startStock = (float)$startQuery->sum(DB::raw('in_qty - out_qty'));

            $endStock = $startStock + $inQty - $outQty;

            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $sku);
            $sheet->setCellValue('C' . $row, $m->origin_code ?: '-');
            $sheet->setCellValue('D' . $row, $name);
            $sheet->setCellValue('E' . $row, $unit);
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

        $fileName = 'Bao_Cao_XNT_' . ($isEdgeBand ? 'Kho_Chi_' : 'Kho_Van_') . date('Ymd_His') . '.xlsx';
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
        $isEdgeBand = $selectedWarehouse && ($selectedWarehouse->code === 'KHO-CHI' || str_contains(mb_strtolower($selectedWarehouse->name), 'chỉ'));

        $materials = WoodBoard::with('warehouse')->orderBy('price_group')->orderBy('color_code')->get();

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
        $sheet->setCellValue('B3', $isEdgeBand ? 'Mã chỉ dán' : 'Mã màu ván');
        $sheet->mergeCells('B3:B4');
        $sheet->setCellValue('C3', 'Mã TQ');
        $sheet->mergeCells('C3:C4');
        $sheet->setCellValue('D3', 'Tên hàng / Màu');
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
            $sku = $isEdgeBand ? $m->edge_band_code : $m->color_code;
            $name = $isEdgeBand ? $m->edge_band_name : ('Tấm ' . $m->color_code);

            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $sku);
            $sheet->setCellValue('C' . $row, $m->origin_code ?: '-');
            $sheet->setCellValue('D' . $row, $name);

            $initYearQuery = InventoryTransaction::where('wood_board_id', $m->id)
                ->where('date', '<', $year . '-01-01');
            if ($warehouseId) {
                $initYearQuery->where('warehouse_id', $warehouseId);
            }
            if ($isEdgeBand) {
                $initYearQuery->where('item_type', 'edge_band');
            } else {
                $initYearQuery->where(function($q) {
                    $q->where('item_type', 'board')->orWhereNull('item_type');
                });
            }
            $initYearStock = (float)$initYearQuery->sum(DB::raw('in_qty - out_qty'));
            $sheet->setCellValue('E' . $row, $initYearStock);

            $running = $initYearStock;
            $cIdx = 6;
            $yearIn = 0;
            $yearOut = 0;

            for ($month = 1; $month <= 12; $month++) {
                $mStart = sprintf('%04d-%02d-01', $year, $month);
                $mEnd = date('Y-m-t', strtotime($mStart));

                $mInQuery = InventoryTransaction::where('wood_board_id', $m->id)
                    ->whereBetween('date', [$mStart, $mEnd]);
                if ($warehouseId) {
                    $mInQuery->where('warehouse_id', $warehouseId);
                }
                if ($isEdgeBand) {
                    $mInQuery->where('item_type', 'edge_band');
                } else {
                    $mInQuery->where(function($q) {
                        $q->where('item_type', 'board')->orWhereNull('item_type');
                    });
                }
                $mIn = (float)$mInQuery->sum('in_qty');

                $mOutQuery = InventoryTransaction::where('wood_board_id', $m->id)
                    ->whereBetween('date', [$mStart, $mEnd]);
                if ($warehouseId) {
                    $mOutQuery->where('warehouse_id', $warehouseId);
                }
                if ($isEdgeBand) {
                    $mOutQuery->where('item_type', 'edge_band');
                } else {
                    $mOutQuery->where(function($q) {
                        $q->where('item_type', 'board')->orWhereNull('item_type');
                    });
                }
                $mOut = (float)$mOutQuery->sum('out_qty');

                $running = $running + $mIn - $mOut;
                $yearIn += $mIn;
                $yearOut += $mOut;

                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx) . $row, $mIn > 0 ? $mIn : '');
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1) . $row, $mOut > 0 ? $mOut : '');
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 2) . $row, $running);
                $cIdx += 3;
            }

            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx) . $row, $yearIn);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1) . $row, $yearOut);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 2) . $row, $running);

            $row++;
        }

        // TỔNG CỘNG row
        $sheet->setCellValue('A' . $row, 'TỔNG CỘNG:');
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue('E' . $row, "=SUM(E5:E" . ($row - 1) . ")");

        for ($c = 6; $c <= $currentColIdx + 2; $c++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $sheet->setCellValue($colLetter . $row, "=SUM({$colLetter}5:{$colLetter}" . ($row - 1) . ")");
        }

        $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        $sheet->getStyle("A3:{$lastColLetter}{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("E5:{$lastColLetter}{$row}")->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(26);
        $sheet->getColumnDimension('E')->setWidth(14);
        for ($c = 6; $c <= $currentColIdx + 2; $c++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c))->setWidth(10);
        }

        $fileName = "Ma_Tran_Tieu_Thu_" . ($isEdgeBand ? 'Kho_Chi_' : 'Kho_Van_') . "{$year}_" . date('Ymd_His') . '.xlsx';
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
        $warehouse->delete();
        return back()->with('success', 'Đã xóa kho ' . $name . '!');
    }

    /**
     * Nhập tồn kho từ file Excel Ton_Kho_Tam_Van_
     */
    public function importStock(Request $request)
    {
        if ($request->boolean('use_server_file')) {
            $filePath = base_path('Ton_Kho_Tam_Van_.xlsx');
            if (!file_exists($filePath)) {
                $filePath = 'C:/Code/Gervin/Ton_Kho_Tam_Van_.xlsx';
            }
            if (!file_exists($filePath)) {
                return back()->withErrors(['excel_file' => 'Không tìm thấy file Ton_Kho_Tam_Van_.xlsx trên máy chủ!']);
            }
        } else {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls|max:20480',
            ], [
                'excel_file.required' => 'Vui lòng chọn file Excel để nhập dữ liệu!',
                'excel_file.mimes'    => 'File phải có định dạng .xlsx hoặc .xls!',
                'excel_file.max'      => 'Dung lượng file tối đa là 20MB!',
            ]);
            $filePath = $request->file('excel_file')->getRealPath();
        }

        try {
            $result = $this->inventoryService->importStockFromExcel($filePath);

            return back()->with('success', "Đã nhập thành công {$result['total_items']} dòng vật tư (Cập nhật: {$result['updated_count']}, Thêm mới: {$result['created_count']}). Tổng tồn kho: " . number_format($result['total_stock'], 2, ',', '.') . " tấm!");
        } catch (\Exception $e) {
            return back()->withErrors(['excel_file' => 'Lỗi khi nhập file Excel: ' . $e->getMessage()]);
        }
    }
}
