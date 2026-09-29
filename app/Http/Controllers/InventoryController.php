<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Material;
use App\Models\InventoryReceipt;
use App\Models\InventoryIssue;
use App\Models\InventoryStocktake;
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
            'index', 'stockCard', 'exportStock', 
            'receipts', 'showReceipt', 'printReceipt',
            'issues', 'showIssue', 'printIssue',
            'stocktakes', 'showStocktake',
            'reports', 'exportReport', 'exportMatrixReport',
            'warehouseList'
        ]]);

        $this->middleware('permission:add warehouse', ['only' => [
            'storeMaterial', 'importMaterials',
            'createReceipt', 'storeReceipt',
            'createIssue', 'storeIssue',
            'createStocktake', 'storeStocktake',
            'storeWarehouse'
        ]]);

        $this->middleware('permission:edit warehouse', ['only' => [
            'updateMaterial', 'balanceStocktake', 'updateWarehouse'
        ]]);

        $this->middleware('permission:delete warehouse', ['only' => [
            'destroyMaterial', 'destroyReceipt', 'destroyIssue', 'destroyStocktake', 'destroyWarehouse'
        ]]);
    }

    /**
     * 1. Dashboard & Danh sách Hàng hóa - Vật tư tồn kho
     */
    public function index(Request $request)
    {
        // Auto-seed Acrylic data if database has 0 materials
        if (Material::count() === 0) {
            $excelPath = base_path('docs/TỒN KHO TIÊU THỤ  ACRYLIC TQ.xlsx');
            if (file_exists($excelPath)) {
                $this->inventoryService->seedAcrylicFromExcel($excelPath);
            }
        }

        $warehouses = Warehouse::orderBy('name')->get();
        $selectedWarehouseId = $request->input('warehouse_id');
        $selectedCategory = $request->input('category');
        $stockFilter = $request->input('stock_filter', 'all'); // 'all', 'low_stock', 'out_of_stock', 'in_stock'
        $search = trim((string)$request->input('search'));

        // Smart Date Filter handling (tương tự orders và customers)
        $dateMode = $request->input('date_mode', 'all');
        $dateVal = $request->input('date_val', '');
        $filterStartDate = $request->input('filter_start_date');
        $filterEndDate = $request->input('filter_end_date');

        $startDate = null;
        $endDate = null;
        $dateLabel = 'Toàn thời gian';

        if ($dateMode && $dateMode !== 'all') {
            if ($dateMode === 'day' && $dateVal) {
                $startDate = $dateVal;
                $endDate = $dateVal;
                $dateLabel = 'Ngày ' . \Carbon\Carbon::parse($dateVal)->format('d/m/Y');
            } elseif ($dateMode === 'month' && $dateVal) {
                $cDate = \Carbon\Carbon::parse($dateVal . '-01');
                $startDate = $cDate->copy()->startOfMonth()->toDateString();
                $endDate = $cDate->copy()->endOfMonth()->toDateString();
                $dateLabel = 'Tháng ' . $cDate->format('m/Y');
            } elseif ($dateMode === 'year' && $dateVal) {
                $startDate = $dateVal . '-01-01';
                $endDate = $dateVal . '-12-31';
                $dateLabel = 'Năm ' . $dateVal;
            }
        } elseif ($filterStartDate || $filterEndDate) {
            $startDate = $filterStartDate;
            $endDate = $filterEndDate;
            $dateMode = 'custom';
            $dateLabel = ($startDate ? 'Từ ' . \Carbon\Carbon::parse($startDate)->format('d/m/Y') : '') . ($endDate ? ' đến ' . \Carbon\Carbon::parse($endDate)->format('d/m/Y') : '');
        } else {
            $dateMode = 'all';
            $dateVal = '';
            $dateLabel = 'Toàn thời gian';
        }

        $query = Material::with('warehouse');

        if ($selectedWarehouseId) {
            $query->where('warehouse_id', $selectedWarehouseId);
        }

        if ($selectedCategory) {
            $query->where('category', $selectedCategory);
        }

        if ($stockFilter === 'low_stock') {
            $query->whereColumn('current_stock', '<=', 'min_stock')->where('current_stock', '>', 0);
        } elseif ($stockFilter === 'out_of_stock') {
            $query->where('current_stock', '<=', 0);
        } elseif ($stockFilter === 'in_stock') {
            $query->where('current_stock', '>', 0);
        }

        if ($startDate && $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereHas('transactions', function ($t) use ($startDate, $endDate) {
                    $t->whereBetween('date', [$startDate, $endDate]);
                })->orWhereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('origin_code', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $materials = $query->orderBy('category')->orderBy('code')->paginate(50)->withQueryString();

        // Summary KPIs
        $totalItems = Material::count();
        $totalStockQty = Material::sum('current_stock');
        $totalStockValue = Material::selectRaw('SUM(current_stock * cost_price) as val')->value('val') ?? 0;
        $lowStockCount = Material::where(function ($q) {
            $q->whereColumn('current_stock', '<=', 'min_stock')->orWhere('current_stock', '<=', 0);
        })->count();

        $categories = Material::select('category')->whereNotNull('category')->distinct()->pluck('category');

        $activeFilterCount = 0;
        if ($selectedWarehouseId) $activeFilterCount++;
        if ($selectedCategory) $activeFilterCount++;
        if ($stockFilter !== 'all') $activeFilterCount++;
        if ($dateMode !== 'all' && !empty($dateMode)) $activeFilterCount++;
        $isFiltered = ($activeFilterCount > 0) || !empty($search);

        return view('inventory.index', compact(
            'materials',
            'warehouses',
            'selectedWarehouseId',
            'selectedCategory',
            'stockFilter',
            'search',
            'totalItems',
            'totalStockQty',
            'totalStockValue',
            'lowStockCount',
            'categories',
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
     * Store new material
     */
    public function storeMaterial(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:100|unique:materials,code',
            'name' => 'required|string|max:255',
            'origin_code' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'cost_price' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'initial_stock' => 'nullable|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'notes' => 'nullable|string',
        ]);

        $initStock = (float)($request->initial_stock ?? 0);

        $material = Material::create([
            'code' => trim($request->code),
            'name' => trim($request->name),
            'origin_code' => trim($request->origin_code),
            'category' => trim($request->category) ?: 'Vật tư chung',
            'unit' => trim($request->unit) ?: 'Tấm',
            'cost_price' => (float)($request->cost_price ?? 0),
            'min_stock' => (float)($request->min_stock ?? 50),
            'initial_stock' => $initStock,
            'current_stock' => $initStock,
            'warehouse_id' => $request->warehouse_id,
            'status' => 'active',
            'notes' => $request->notes,
        ]);

        if ($initStock > 0) {
            InventoryTransaction::create([
                'warehouse_id' => $material->warehouse_id ?? Warehouse::first()->id,
                'material_id' => $material->id,
                'date' => date('Y-m-d'),
                'type' => 'initial',
                'voucher_code' => 'DK-' . date('Y'),
                'in_qty' => $initStock,
                'out_qty' => 0,
                'stock_after' => $initStock,
                'notes' => 'Số dư tồn kho ban đầu',
                'created_by' => Auth::id(),
            ]);
        }

        return back()->with('success', 'Thêm mới vật tư ' . $material->code . ' thành công!');
    }

    /**
     * Update material
     */
    public function updateMaterial(Request $request, Material $material)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'origin_code' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'cost_price' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $material->update([
            'name' => trim($request->name),
            'origin_code' => trim($request->origin_code),
            'category' => trim($request->category) ?: $material->category,
            'unit' => trim($request->unit) ?: $material->unit,
            'cost_price' => (float)($request->cost_price ?? 0),
            'min_stock' => (float)($request->min_stock ?? 50),
            'warehouse_id' => $request->warehouse_id,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Cập nhật vật tư ' . $material->code . ' thành công!');
    }

    /**
     * Delete material
     */
    public function destroyMaterial(Material $material)
    {
        $code = $material->code;
        $material->delete();
        return back()->with('success', 'Đã xóa vật tư ' . $code . '!');
    }

    /**
     * Thẻ kho (Stock Card - lịch sử biến động của 1 mã vật tư)
     */
    public function stockCard(Request $request, Material $material)
    {
        $transactions = $material->transactions()->with(['warehouse', 'creator'])->paginate(30);

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
        $materials = Material::with('warehouse')->orderBy('category')->orderBy('code')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Danh_Sach_Ton_Kho');

        // Headers
        $sheet->setCellValue('A1', 'BÁO CÁO TỒN KHO VẬT TƯ - ' . date('d/m/Y H:i'));
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);

        $headers = ['STT', 'Mã SKU', 'Tên vật tư', 'Mã xuất xứ', 'Nhóm hàng', 'ĐVT', 'Tồn kho', 'Giá vốn (đ)', 'Giá trị tồn (đ)'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
        foreach ($headers as $idx => $h) {
            $sheet->setCellValue($cols[$idx] . '3', $h);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']]
        ];
        $sheet->getStyle('A3:I3')->applyFromArray($headerStyle);

        $row = 4;
        foreach ($materials as $i => $m) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $m->code);
            $sheet->setCellValue('C' . $row, $m->name);
            $sheet->setCellValue('D' . $row, $m->origin_code ?: '-');
            $sheet->setCellValue('E' . $row, $m->category);
            $sheet->setCellValue('F' . $row, $m->unit);
            $sheet->setCellValue('G' . $row, $m->current_stock);
            $sheet->setCellValue('H' . $row, $m->cost_price);
            $sheet->setCellValue('I' . $row, "=G{$row}*H{$row}");
            $row++;
        }

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

        $fileName = 'Ton_Kho_Vat_Tu_' . date('Ymd_His') . '.xlsx';
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

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $receipts = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(20)->withQueryString();

        return view('inventory.receipts.index', compact('receipts'));
    }

    public function createReceipt(Request $request)
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $materials = Material::where('status', 'active')->orderBy('category')->orderBy('code')->get();
        $nextCode = $this->inventoryService->generateCode('PNK', 'inventory_receipts');

        return view('inventory.receipts.create', compact('warehouses', 'materials', 'nextCode'));
    }

    public function storeReceipt(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
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
        $receipt->load(['warehouse', 'creator', 'items.material']);
        return view('inventory.receipts.show', compact('receipt'));
    }

    public function printReceipt(InventoryReceipt $receipt)
    {
        $receipt->load(['warehouse', 'creator', 'items.material']);
        return view('inventory.receipts.print', compact('receipt'));
    }

    public function destroyReceipt(InventoryReceipt $receipt)
    {
        $code = $receipt->code;
        $receipt->delete();
        return redirect()->route('inventory.receipts.index')->with('success', 'Đã xóa phiếu nhập kho ' . $code . '!');
    }

    /**
     * 3. Phiếu Xuất kho (Goods Issues)
     */
    public function issues(Request $request)
    {
        $query = InventoryIssue::with(['warehouse', 'creator'])->withCount('items');

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
        $materials = Material::where('status', 'active')->orderBy('category')->orderBy('code')->get();
        $nextCode = $this->inventoryService->generateCode('PXK', 'inventory_issues');

        return view('inventory.issues.create', compact('warehouses', 'materials', 'nextCode'));
    }

    public function storeIssue(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
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
        $issue->load(['warehouse', 'creator', 'items.material']);
        return view('inventory.issues.show', compact('issue'));
    }

    public function printIssue(InventoryIssue $issue)
    {
        $issue->load(['warehouse', 'creator', 'items.material']);
        return view('inventory.issues.print', compact('issue'));
    }

    public function destroyIssue(InventoryIssue $issue)
    {
        $code = $issue->code;
        $issue->delete();
        return redirect()->route('inventory.issues.index')->with('success', 'Đã xóa phiếu xuất kho ' . $code . '!');
    }

    /**
     * 4. Phiếu Kiểm kê kho (Stocktake - Cân bằng kho chuẩn MISA / KiotViet)
     */
    public function stocktakes(Request $request)
    {
        $query = InventoryStocktake::with(['warehouse', 'balancedByUser'])->withCount('items');

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
        $warehouse = Warehouse::first();
        if (!$warehouse) {
            $warehouse = Warehouse::create(['name' => 'Kho Chính', 'code' => 'KHO-CHINH']);
        }

        $materials = Material::where('status', 'active')
            ->orderBy('category')
            ->orderBy('code')
            ->get();

        $nextCode = $this->inventoryService->generateCode('PKK', 'inventory_stocktakes');

        return view('inventory.stocktakes.create', compact('warehouse', 'materials', 'nextCode'));
    }

    public function storeStocktake(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.actual_quantity' => 'required|numeric|min:0',
        ]);

        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        $stocktake = $this->inventoryService->createStocktake(
            $warehouse,
            $request->only(['code', 'date', 'creator_name', 'notes']),
            $request->input('items', []),
            Auth::id()
        );

        // If user also checked "auto balance"
        if ($request->boolean('auto_balance')) {
            $this->inventoryService->balanceStocktake($stocktake, Auth::id());
            return redirect()->route('inventory.stocktakes.show', $stocktake)->with('success', 'Đã lưu và Cân bằng kho thành công!');
        }

        return redirect()->route('inventory.stocktakes.show', $stocktake)->with('success', 'Tạo phiếu kiểm kê ' . $stocktake->code . ' thành công!');
    }

    public function showStocktake(InventoryStocktake $stocktake)
    {
        $stocktake->load(['warehouse', 'balancedByUser', 'items.material']);
        return view('inventory.stocktakes.show', compact('stocktake'));
    }

    public function balanceStocktake(InventoryStocktake $stocktake)
    {
        $this->inventoryService->balanceStocktake($stocktake, Auth::id());
        return back()->with('success', 'Cân bằng kho thành công! Số lượng tồn thực tế đã được cập nhật.');
    }

    public function destroyStocktake(InventoryStocktake $stocktake)
    {
        $code = $stocktake->code;
        $stocktake->delete();
        return redirect()->route('inventory.stocktakes.index')->with('success', 'Đã xóa phiếu kiểm kê ' . $code . '!');
    }

    /**
     * 5. Báo cáo Xuất - Nhập - Tồn & Báo cáo Ma trận 12 Tháng Kế toán
     */
    public function reports(Request $request)
    {
        $tab = $request->input('tab', 'summary'); // 'summary' or 'matrix'
        $startDate = $request->input('start_date', date('Y-01-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $year = (int)$request->input('year', date('Y'));

        // Tab 1: Xuất Nhập Tồn tổng hợp
        $materials = Material::with('warehouse')->orderBy('category')->orderBy('code')->get();
        $reportData = [];

        foreach ($materials as $m) {
            // In & Out during period
            $inQty = InventoryTransaction::where('material_id', $m->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('in_qty', '>', 0)
                ->sum('in_qty');

            $outQty = InventoryTransaction::where('material_id', $m->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('out_qty', '>', 0)
                ->sum('out_qty');

            // Start Stock before startDate
            $startStock = InventoryTransaction::where('material_id', $m->id)
                ->where('date', '<', $startDate)
                ->sum(DB::raw('in_qty - out_qty'));

            $endStock = $startStock + $inQty - $outQty;

            $reportData[] = [
                'material' => $m,
                'start_stock' => $startStock,
                'in_qty' => $inQty,
                'out_qty' => $outQty,
                'end_stock' => $endStock,
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
            // Start of year stock
            $initYearStock = InventoryTransaction::where('material_id', $m->id)
                ->where('date', '<', $year . '-01-01')
                ->sum(DB::raw('in_qty - out_qty'));

            $running = $initYearStock;
            $rowMonths = [];
            $totalYearIn = 0;
            $totalYearOut = 0;

            for ($month = 1; $month <= 12; $month++) {
                $mStart = sprintf('%04d-%02d-01', $year, $month);
                $mEnd = date('Y-m-t', strtotime($mStart));

                $mIn = (float)InventoryTransaction::where('material_id', $m->id)
                    ->whereBetween('date', [$mStart, $mEnd])
                    ->sum('in_qty');

                $mOut = (float)InventoryTransaction::where('material_id', $m->id)
                    ->whereBetween('date', [$mStart, $mEnd])
                    ->sum('out_qty');

                $running = $running + $mIn - $mOut;
                $rowMonths[$month] = ['in' => $mIn, 'out' => $mOut, 'stock' => $running];

                $totalYearIn += $mIn;
                $totalYearOut += $mOut;

                $colTotals['months'][$month]['in'] += $mIn;
                $colTotals['months'][$month]['out'] += $mOut;
                $colTotals['months'][$month]['stock'] += $running;
            }

            $matrixRows[] = [
                'stt' => $idx + 1,
                'code' => $m->code,
                'name' => $m->name,
                'origin_code' => $m->origin_code,
                'category' => $m->category,
                'start_stock' => $initYearStock,
                'months' => $rowMonths,
                'total_year_in' => $totalYearIn,
                'total_year_out' => $totalYearOut,
                'final_stock' => $running,
                'stock_status' => $m->stock_status,
            ];

            $colTotals['start_stock'] += $initYearStock;
            $colTotals['total_in'] += $totalYearIn;
            $colTotals['total_out'] += $totalYearOut;
            $colTotals['final_stock'] += $running;
        }

        $availableYears = InventoryTransaction::selectRaw('DISTINCT YEAR(date) as y')
            ->whereNotNull('date')
            ->orderByDesc('y')
            ->pluck('y')
            ->toArray();

        if (empty($availableYears)) $availableYears = [date('Y')];

        return view('inventory.reports.index', compact(
            'tab',
            'startDate',
            'endDate',
            'year',
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
        $startDate = $request->input('start_date', date('Y-01-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        $materials = Material::with('warehouse')->orderBy('category')->orderBy('code')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Xuat_Nhap_Ton');

        $sheet->setCellValue('A1', 'BÁO CÁO TỔNG HỢP XUẤT - NHẬP - TỒN');
        $sheet->setCellValue('A2', 'Từ ngày: ' . date('d/m/Y', strtotime($startDate)) . ' đến ngày: ' . date('d/m/Y', strtotime($endDate)));
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);
        $sheet->getStyle('A2')->getFont()->setSize(11)->setItalic(true);

        $headers = ['STT', 'Mã SKU', 'Mã gốc TQ', 'Tên vật tư', 'ĐVT', 'Tồn đầu kỳ', 'Nhập trong kỳ', 'Xuất trong kỳ', 'Tồn cuối kỳ'];
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
            $inQty = (float)InventoryTransaction::where('material_id', $m->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('in_qty', '>', 0)
                ->sum('in_qty');

            $outQty = (float)InventoryTransaction::where('material_id', $m->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('out_qty', '>', 0)
                ->sum('out_qty');

            $startStock = (float)InventoryTransaction::where('material_id', $m->id)
                ->where('date', '<', $startDate)
                ->sum(DB::raw('in_qty - out_qty'));

            $endStock = $startStock + $inQty - $outQty;

            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $m->code);
            $sheet->setCellValue('C' . $row, $m->origin_code ?: '-');
            $sheet->setCellValue('D' . $row, $m->name);
            $sheet->setCellValue('E' . $row, $m->unit);
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

        $fileName = 'Bao_Cao_XNT_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Xuất Excel Ma trận Tiêu thụ 12 tháng chuẩn kế toán
     */
    public function exportMatrixReport(Request $request)
    {
        $year = (int)$request->input('year', date('Y'));
        $materials = Material::with('warehouse')->orderBy('category')->orderBy('code')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ma_Tran_Tieu_Thu_' . $year);

        $sheet->setCellValue('A1', "BÁO CÁO MA TRẬN NHẬP - XUẤT - TỒN 12 THÁNG NĂM {$year}");
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);

        // Header Rows (Row 3 & 4)
        $sheet->setCellValue('A3', 'STT');
        $sheet->mergeCells('A3:A4');
        $sheet->setCellValue('B3', 'Mã Gervin');
        $sheet->mergeCells('B3:B4');
        $sheet->setCellValue('C3', 'Mã TQ');
        $sheet->mergeCells('C3:C4');
        $sheet->setCellValue('D3', 'Tên hàng / Màu');
        $sheet->mergeCells('D3:D4');
        $sheet->setCellValue('E3', 'Tồn đầu ' . $year);
        $sheet->mergeCells('E3:E4');

        $currentColIdx = 6; // Column F is 6
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
            $sheet->setCellValue('C' . $row, $m->origin_code ?: '-');
            $sheet->setCellValue('D' . $row, $m->name);

            $initYearStock = (float)InventoryTransaction::where('material_id', $m->id)
                ->where('date', '<', $year . '-01-01')
                ->sum(DB::raw('in_qty - out_qty'));
            $sheet->setCellValue('E' . $row, $initYearStock);

            $running = $initYearStock;
            $cIdx = 6;
            $yearIn = 0;
            $yearOut = 0;

            for ($month = 1; $month <= 12; $month++) {
                $mStart = sprintf('%04d-%02d-01', $year, $month);
                $mEnd = date('Y-m-t', strtotime($mStart));

                $mIn = (float)InventoryTransaction::where('material_id', $m->id)
                    ->whereBetween('date', [$mStart, $mEnd])
                    ->sum('in_qty');

                $mOut = (float)InventoryTransaction::where('material_id', $m->id)
                    ->whereBetween('date', [$mStart, $mEnd])
                    ->sum('out_qty');

                $running = $running + $mIn - $mOut;
                $yearIn += $mIn;
                $yearOut += $mOut;

                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx) . $row, $mIn ?: '');
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1) . $row, $mOut ?: '');
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

        $fileName = "Ma_Tran_Tieu_Thu_Acrylic_{$year}_" . date('Ymd_His') . '.xlsx';
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
        $warehouses = Warehouse::withCount('materials')->orderBy('name')->get();
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
}
