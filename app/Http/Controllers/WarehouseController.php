<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseRecord;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view supply',   ['only' => ['index', 'show', 'exportTemplate', 'exportData', 'printRecordVoucher', 'exportMatrix']]);
        $this->middleware('permission:add supply',    ['only' => ['store', 'storeRecord', 'import', 'importJson', 'importMatrix']]);
        $this->middleware('permission:edit supply',   ['only' => ['updateConfig', 'updateRecord']]);
        $this->middleware('permission:delete supply', ['only' => ['destroy', 'destroyRecord']]);
    }

    public function index()
    {
        $warehouses = Warehouse::withCount('records')->latest()->get();
        return view('warehouses.index', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Warehouse::create([
            'name' => $request->name,
            'sizes_config' => [] // Initial empty config
        ]);

        return redirect()->route('warehouses.index')->with('success', 'Tạo kho mới thành công!');
    }

    public function show(Warehouse $warehouse, Request $request)
    {
        $records = $warehouse->records()->orderBy('date', 'desc')->orderBy('id', 'desc')->get();
        $allSizes = $this->getWarehouseSizes($warehouse);

        $lastVoucher = $warehouse->records()->orderBy('id', 'desc')->value('voucher_no');
        $nextVoucher = $this->generateNextVoucher($lastVoucher);

        // Matrix Data Calculation
        $selectedYear = $request->input('year');
        $matrixData = $this->getMonthlyMatrixData($warehouse, $selectedYear);
        $selectedYear = $matrixData['year'];
        $availableYears = $matrixData['years'];
        $currentTab = $request->input('tab', !empty($warehouse->sizes_config) ? 'matrix' : 'ledger'); // 'ledger' or 'matrix'

        return view('warehouses.show', compact(
            'warehouse', 
            'records', 
            'allSizes', 
            'nextVoucher', 
            'matrixData', 
            'selectedYear', 
            'availableYears', 
            'currentTab'
        ));
    }

    private function generateNextVoucher($lastVoucher)
    {
        if (!$lastVoucher) return 'P001';
        
        // Extract prefix and numeric part (e.g. PN005 -> PN and 005)
        preg_match('/^([^\d]*)(\d+)/', $lastVoucher, $matches);
        
        if (count($matches) == 3) {
            $prefix = $matches[1];
            $number = (int)$matches[2] + 1;
            $width = strlen($matches[2]);
            return $prefix . str_pad($number, $width, '0', STR_PAD_LEFT);
        }
        
        return $lastVoucher . '-1'; // Fallback
    }

    public function updateConfig(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'groups' => 'required|array',
            'groups.*.name' => 'required|string',
            'groups.*.code' => 'nullable|string|max:255',
            'groups.*.price' => 'nullable|numeric|min:0',
            'groups.*.sizes' => 'nullable|array',
            'groups.*.sizes.*.name' => 'required_with:groups.*.sizes|string',
            'groups.*.sizes.*.code' => 'nullable|string|max:255',
            'groups.*.sizes.*.price' => 'nullable|numeric|min:0',
        ]);

        $config = [];
        foreach ($request->groups as $group) {
            $sizes = [];
            if (!empty($group['sizes']) && is_array($group['sizes'])) {
                foreach ($group['sizes'] as $size) {
                    if (!empty($size['name'])) {
                        $sizes[] = [
                            'name' => trim($size['name']),
                            'code' => !empty($size['code']) ? trim($size['code']) : null,
                            'price' => !empty($size['price']) ? (float)$size['price'] : null
                        ];
                    }
                }
            }
            $config[] = [
                'name' => trim($group['name']),
                'code' => !empty($group['code']) ? trim($group['code']) : null,
                'price' => !empty($group['price']) ? (float)$group['price'] : null,
                'sizes' => $sizes
            ];
        }

        $warehouse->update([
            'item_name' => $request->item_name,
            'sizes_config' => $config
        ]);

        return back()->with('success', 'Cấu hình kho đã được cập nhật!');
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->records()->delete();
        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('success', 'Đã xóa kho và toàn bộ lịch sử phiếu!');
    }

    public function storeRecord(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'voucher_no' => 'nullable|string',
            'date' => 'nullable|date',
            'content' => 'nullable|string',
            'exporter' => 'nullable|string',
            'receiver' => 'nullable|string',
        ]);

        $inData = $request->input('in_data', []);
        $outData = $request->input('out_data', []);

        $warehouse->records()->create([
            'voucher_no' => $request->voucher_no,
            'date' => $request->date ?: now(),
            'content' => $request->content,
            'exporter' => $request->exporter,
            'receiver' => $request->receiver,
            'in_data' => $inData,
            'out_data' => $outData,
            'stock_data' => [], // Will be filled by recalculateStock
        ]);

        $this->recalculateStock($warehouse);

        return back()->with('success', 'Thêm phiếu thành công!');
    }

    public function updateRecord(Request $request, Warehouse $warehouse, WarehouseRecord $record)
    {
        $request->validate([
            'voucher_no' => 'nullable|string',
            'date' => 'nullable|date',
            'content' => 'nullable|string',
            'exporter' => 'nullable|string',
            'receiver' => 'nullable|string',
        ]);

        $inData = $request->input('in_data', []);
        $outData = $request->input('out_data', []);

        $record->update([
            'voucher_no' => $request->voucher_no,
            'date' => $request->date ?: now(),
            'content' => $request->content,
            'exporter' => $request->exporter,
            'receiver' => $request->receiver,
            'in_data' => $inData,
            'out_data' => $outData,
        ]);

        $this->recalculateStock($warehouse);

        return back()->with('success', 'Cập nhật phiếu thành công!');
    }

    public function destroyRecord(Warehouse $warehouse, WarehouseRecord $record)
    {
        $record->delete();
        $this->recalculateStock($warehouse);
        return back()->with('success', 'Đã xóa phiếu và tính toán lại tồn kho!');
    }

    private function recalculateStock(Warehouse $warehouse)
    {
        $records = $warehouse->records()->orderBy('date', 'asc')->orderBy('id', 'asc')->get();
        $prevStock = [];
        $allSizes = $this->getWarehouseSizes($warehouse);

        foreach ($records as $record) {
            $stockData = [];
            foreach ($allSizes as $s) {
                $inVal = (int)($record->in_data[$s['key']] ?? 0);
                $outVal = (int)($record->out_data[$s['key']] ?? 0);
                $prevVal = (int)($prevStock[$s['key']] ?? 0);
                $stockData[$s['key']] = $prevVal + $inVal - $outVal;
            }
            $record->update(['stock_data' => $stockData]);
            $prevStock = $stockData;
        }
    }

    public function exportTemplate(Warehouse $warehouse)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $allSizes = $this->getWarehouseSizes($warehouse);
        $dynamicStartCol = 5; // Column E (1-indexed index 5)
        $totalDynamicCols = count($allSizes);

        // Styling utils
        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ];

        // Row 1 & Fixed Headers
        $fixedFields = [
            'A' => 'STT',
            'B' => 'SỐ PHIẾU',
            'C' => 'NGÀY',
            'D' => 'NỘI DUNG'
        ];
        foreach ($fixedFields as $col => $title) {
            $sheet->setCellValue($col . '1', $title);
            $sheet->mergeCells($col . '1:' . $col . '3');
            $sheet->getStyle($col . '1:' . $col . '3')->applyFromArray($headerStyle);
        }

        // Row 1: NHẬP, XUẤT, TỒN Large Headers
        $types = [
            ['title' => 'NHẬP ' . strtoupper($warehouse->item_name), 'color' => 'E2F3F1', 'key' => 'in'],
            ['title' => 'XUẤT ' . strtoupper($warehouse->item_name), 'color' => 'E9ECEF', 'key' => 'out'],
            ['title' => 'TỒN ' . strtoupper($warehouse->item_name), 'color' => 'FFF3CD', 'key' => 'stock']
        ];

        $currentColIndex = $dynamicStartCol;
        foreach ($types as $tIdx => $type) {
            $startColStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIndex);
            $endColStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIndex + $totalDynamicCols - 1);
            
            $sheet->setCellValue($startColStr . '1', $type['title']);
            $sheet->mergeCells($startColStr . '1:' . $endColStr . '1');
            $sheet->getStyle($startColStr . '1:' . $endColStr . '1')->applyFromArray($headerStyle);
            $sheet->getStyle($startColStr . '1:' . $endColStr . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($type['color']);
            
            // Row 2: Group Names
            $groupColIdx = $currentColIndex;
            foreach ($warehouse->sizes_config as $group) {
                $groupSizeCount = count($group['sizes']) ?: 1;
                $gStartCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($groupColIdx);
                $gEndCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($groupColIdx + $groupSizeCount - 1);
                
                $label = $group['name'];
                if ($type['key'] === 'stock' && !empty($group['price'])) {
                    $label .= ' (' . number_format($group['price'], 0, ',', '.') . 'đ)';
                }
                $sheet->setCellValue($gStartCol . '2', $label);
                $sheet->mergeCells($gStartCol . '2:' . $gEndCol . (count($group['sizes']) ? '2' : '3'));
                $sheet->getStyle($gStartCol . '2:' . $gEndCol . (count($group['sizes']) ? '2' : '3'))->applyFromArray($headerStyle);
                
                // Row 3: Sizes
                if (count($group['sizes'])) {
                    $sizeColIdx = $groupColIdx;
                    foreach ($group['sizes'] as $size) {
                        $sColStr = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($sizeColIdx);
                        $label = $size['name'];
                        if ($type['key'] === 'stock') {
                            $price = !empty($size['price']) ? $size['price'] : (!empty($group['price']) ? $group['price'] : null);
                            if ($price) {
                                $label .= ' (' . number_format($price, 0, ',', '.') . 'đ)';
                            }
                        }
                        $sheet->setCellValue($sColStr . '3', $label);
                        $sheet->getStyle($sColStr . '3')->applyFromArray($headerStyle);
                        $sizeColIdx++;
                    }
                }
                $groupColIdx += $groupSizeCount;
            }
            $currentColIndex += $totalDynamicCols;
        }

        // Footer columns (Exporter, Receiver)
        $exporterCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIndex);
        $sheet->setCellValue($exporterCol . '1', 'NGƯỜI XUẤT');
        $sheet->mergeCells($exporterCol . '1:' . $exporterCol . '3');
        $sheet->getStyle($exporterCol . '1:' . $exporterCol . '3')->applyFromArray($headerStyle);

        $receiverCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentColIndex + 1);
        $sheet->setCellValue($receiverCol . '1', 'NGƯỜI NHẬN');
        $sheet->mergeCells($receiverCol . '1:' . $receiverCol . '3');
        $sheet->getStyle($receiverCol . '1:' . $receiverCol . '3')->applyFromArray($headerStyle);

        // Auto-sizing
        foreach (range('A', $receiverCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Mau_Kho_' . str_replace(' ', '_', $warehouse->name) . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $writer->save('php://output');
        exit;
    }

    public function import(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $file = $request->file('file');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        
        // Skip 3 rows of headers
        array_shift($rows); 
        array_shift($rows); 
        array_shift($rows); 
        
        $allSizes = $this->getWarehouseSizes($warehouse);
        $importCount = 0;
        $rowIdx = 4; // Data starts at Row 4
        foreach ($rows as $row) {
            if (empty($row[1]) && empty($row[2])) { $rowIdx++; continue; }

            // Robust Date Parsing
            $date = null;
            try {
                if (is_numeric($row[2])) {
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2])->format('Y-m-d');
                } else if ($row[2]) {
                    $date = Carbon::parse($row[2])->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $date = null;
            }

            if (!$date) {
                $date = now()->format('Y-m-d');
            }

            $data = [
                'voucher_no' => $row[1],
                'date' => $date,
                'content' => $row[3],
            ];

            $inData = [];
            $outData = [];
            $manualStockData = [];
            $colIdx = 4; // Start of NHẬP columns (index 4 is Col E)
            
            // Map In Data
            foreach ($allSizes as $s) {
                $inData[$s['key']] = (int)($row[$colIdx] ?? 0);
                $colIdx++;
            }
            // Map Out Data
            foreach ($allSizes as $s) {
                $outData[$s['key']] = (int)($row[$colIdx] ?? 0);
                $colIdx++;
            }
            // Map Manual Stock Data (Optional)
            foreach ($allSizes as $s) {
                $manualStockData[$s['key']] = ($row[$colIdx] !== null && $row[$colIdx] !== '') ? (int)$row[$colIdx] : null;
                $colIdx++;
            }

            $data['exporter'] = $row[$colIdx] ?? '';
            $data['receiver'] = $row[$colIdx+1] ?? '';

            // Calculate Stock
            $lastRecord = $warehouse->records()->orderBy('date', 'desc')->orderBy('id', 'desc')->first();
            $prevStock = $lastRecord ? $lastRecord->stock_data : [];
            $stockData = [];
            
            foreach ($allSizes as $s) {
                $manualVal = $manualStockData[$s['key']];
                
                if ($manualVal !== null) {
                    $stockData[$s['key']] = $manualVal;
                } else {
                    $inVal = (int)($inData[$s['key']] ?? 0);
                    $outVal = (int)($outData[$s['key']] ?? 0);
                    $prevVal = (int)($prevStock[$s['key']] ?? 0);
                    $stockData[$s['key']] = $prevVal + $inVal - $outVal;
                }
            }

            $warehouse->records()->create(array_merge($data, [
                'in_data' => $inData,
                'out_data' => $outData,
                'stock_data' => $stockData
            ]));
            
            $importCount++;
            $rowIdx++;
        }

        return back()->with('success', "Đã nhập thành công $importCount phiếu!");
    }

    public function importJson(Request $request, Warehouse $warehouse)
    {
        try {
            $records = $request->input('records', []);
            if (empty($records)) {
                return response()->json(['success' => false, 'message' => 'Không có dữ liệu hợp lệ.']);
            }

            $allSizes = $this->getWarehouseSizes($warehouse);
            $count = 0;

            foreach ($records as $row) {
                $date = null;
                $rawDate = $row['date'] ?? null;
                if (!empty($rawDate)) {
                    try {
                        if (is_numeric($rawDate) && (float)$rawDate > 10000) {
                            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate)->format('Y-m-d');
                        } else {
                            $date = Carbon::parse(str_replace('/', '-', $rawDate))->format('Y-m-d');
                        }
                    } catch (\Exception $e) { $date = null; }
                }
                if (!$date) $date = now()->format('Y-m-d');

                $data = [
                    'voucher_no'    => $row['voucher_no'] ?? '',
                    'date'          => $date,
                    'content'       => $row['content'] ?? '',
                    'exporter'      => $row['exporter'] ?? '',
                    'receiver'      => $row['receiver'] ?? '',
                ];

                $inData = [];
                $outData = [];
                $manualStockData = [];

                foreach ($allSizes as $s) {
                    $inData[$s['key']] = (int)($row['in_' . $s['key']] ?? 0);
                    $outData[$s['key']] = (int)($row['out_' . $s['key']] ?? 0);
                    
                    $manualVal = isset($row['stock_' . $s['key']]) ? trim($row['stock_' . $s['key']]) : '';
                    $manualStockData[$s['key']] = ($manualVal !== '') ? (int)$manualVal : null;
                }

                // Calculate Running Stock
                $lastRecord = $warehouse->records()->orderBy('date', 'desc')->orderBy('id', 'desc')->first();
                $prevStock = $lastRecord ? $lastRecord->stock_data : [];
                $stockData = [];

                foreach ($allSizes as $s) {
                    if ($manualStockData[$s['key']] !== null) {
                        $stockData[$s['key']] = $manualStockData[$s['key']];
                    } else {
                        $inVal = (int)($inData[$s['key']] ?? 0);
                        $outVal = (int)($outData[$s['key']] ?? 0);
                        $prevVal = (int)($prevStock[$s['key']] ?? 0);
                        $stockData[$s['key']] = $prevVal + $inVal - $outVal;
                    }
                }

                $warehouse->records()->create(array_merge($data, [
                    'in_data'    => $inData,
                    'out_data'   => $outData,
                    'stock_data' => $stockData
                ]));

                $count++;
            }

            return response()->json(['success' => true, 'message' => "Đã nhập thành công $count phiếu!"]);

        } catch (\Exception $e) {
            \Log::error('Warehouse Import Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    private function getWarehouseSizes(Warehouse $warehouse)
    {
        $allSizes = [];
        foreach ($warehouse->sizes_config ?? [] as $group) {
            $groupPrice = !empty($group['price']) ? (float)$group['price'] : 0;
            $groupCode = $group['code'] ?? '';
            if (empty($group['sizes'])) {
                $label = $group['name'];
                if ($groupCode) {
                    $label .= ' (' . $groupCode . ')';
                }
                $allSizes[] = [
                    'group' => $group['name'],
                    'group_code' => $groupCode,
                    'size' => '',
                    'size_code' => '',
                    'key' => $group['name'] . '_default',
                    'price' => $groupPrice,
                    'label' => $label
                ];
            } else {
                foreach ($group['sizes'] as $size) {
                    $sizePrice = !empty($size['price']) ? (float)$size['price'] : $groupPrice;
                    $sizeCode = $size['code'] ?? '';
                    $label = $group['name'] . ' (' . $size['name'] . ')';
                    if ($sizeCode) {
                        $label .= ' [' . $sizeCode . ']';
                    }
                    $allSizes[] = [
                        'group' => $group['name'],
                        'group_code' => $groupCode,
                        'size' => $size['name'],
                        'size_code' => $sizeCode,
                        'key' => $group['name'] . '_' . $size['name'],
                        'price' => $sizePrice,
                        'label' => $label
                    ];
                }
            }
        }
        return $allSizes;
    }

    public function exportData(Warehouse $warehouse)
    {
        $records = $warehouse->records()->orderBy('date', 'desc')->orderBy('id', 'desc')->get();
        $allSizes = $this->getWarehouseSizes($warehouse);
        $sizeCount = count($allSizes);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('BC_Kho');

        // Headers - Row 1 (Main Groups)
        $sheet->setCellValue('A1', 'STT');
        $sheet->setCellValue('B1', 'Số phiếu');
        $sheet->setCellValue('C1', 'Ngày');
        $sheet->setCellValue('D1', 'Nội dung');

        // Merge Row 1 Fixed headers
        foreach(range('A','D') as $col) {
            $sheet->mergeCells("{$col}1:{$col}3");
        }

        $colIdx = 5; // Column 1-indexed (5 = E)
        $inColStart = $this->getColLetter($colIdx);
        $inColEnd = $this->getColLetter($colIdx + $sizeCount - 1);
        $sheet->setCellValue($inColStart.'1', 'NHẬP (' . $warehouse->item_name . ')');
        $sheet->mergeCells("{$inColStart}1:{$inColEnd}1");
        
        $colIdx += $sizeCount;
        $outColStart = $this->getColLetter($colIdx);
        $outColEnd = $this->getColLetter($colIdx + $sizeCount - 1);
        $sheet->setCellValue($outColStart.'1', 'XUẤT (' . $warehouse->item_name . ')');
        $sheet->mergeCells("{$outColStart}1:{$outColEnd}1");

        $colIdx += $sizeCount;
        $stockColStart = $this->getColLetter($colIdx);
        $stockColEnd = $this->getColLetter($colIdx + $sizeCount - 1);
        $sheet->setCellValue($stockColStart.'1', 'TỒN (' . $warehouse->item_name . ')');
        $sheet->mergeCells("{$stockColStart}1:{$stockColEnd}1");

        $colIdx += $sizeCount;
        $sheet->setCellValue($this->getColLetter($colIdx).'1', 'Người xuất');
        $sheet->mergeCells($this->getColLetter($colIdx).'1:'.$this->getColLetter($colIdx).'3');
        $colIdx++;
        $sheet->setCellValue($this->getColLetter($colIdx).'1', 'Người nhận');
        $sheet->mergeCells($this->getColLetter($colIdx).'1:'.$this->getColLetter($colIdx).'3');

        // Row 2: Groups
        $colIdx = 5;
        foreach(['in', 'out', 'stock'] as $type) {
            foreach($warehouse->sizes_config ?? [] as $group) {
                $count = count($group['sizes']) ?: 1;
                $label = $group['name'];
                if (!empty($group['price']) && $type === 'stock') {
                    $label .= ' (' . number_format($group['price'], 0, ',', '.') . 'đ)';
                }
                $sheet->setCellValue($this->getColLetter($colIdx).'2', $label);
                if($count > 1) {
                    $sheet->mergeCells($this->getColLetter($colIdx).'2:'.$this->getColLetter($colIdx + $count - 1).'2');
                }
                $colIdx += $count;
            }
        }

        // Row 3: Sizes
        $colIdx = 5;
        foreach(['in', 'out', 'stock'] as $type) {
            foreach($allSizes as $s) {
                $label = $s['size'] ?: '-';
                if (!empty($s['price']) && $type === 'stock') {
                    $label .= ' (' . number_format($s['price'], 0, ',', '.') . 'đ)';
                }
                $sheet->setCellValue($this->getColLetter($colIdx).'3', $label);
                $colIdx++;
            }
        }

        // Row 4: Summary Row (Totals)
        $currentRow = 4;
        $sheet->setCellValue('A'.$currentRow, 'TỔNG CỘNG');
        $sheet->mergeCells('A'.$currentRow.':D'.$currentRow);
        
        $colIdx = 5;
        // Total In
        foreach($allSizes as $s) {
            $sheet->setCellValue($this->getColLetter($colIdx).$currentRow, $records->sum(fn($r) => $r->in_data[$s['key']] ?? 0));
            $colIdx++;
        }
        // Total Out
        foreach($allSizes as $s) {
            $sheet->setCellValue($this->getColLetter($colIdx).$currentRow, $records->sum(fn($r) => $r->out_data[$s['key']] ?? 0));
            $colIdx++;
        }
        // Current Stock
        $latest = $records->first();
        foreach($allSizes as $s) {
            $sheet->setCellValue($this->getColLetter($colIdx).$currentRow, $latest ? ($latest->stock_data[$s['key']] ?? 0) : 0);
            $colIdx++;
        }

        // Row 5+: Records
        $currentRow = 5;
        foreach($records as $idx => $record) {
            $sheet->setCellValue('A'.$currentRow, $records->count() - $idx);
            $sheet->setCellValue('B'.$currentRow, $record->voucher_no);
            $sheet->setCellValue('C'.$currentRow, $record->date ? $record->date->format('d/m/Y') : '-');
            $sheet->setCellValue('D'.$currentRow, $record->content);

            $colIdx = 5;
            foreach($allSizes as $s) { $sheet->setCellValue($this->getColLetter($colIdx).$currentRow, $record->in_data[$s['key']] ?? 0); $colIdx++; }
            foreach($allSizes as $s) { $sheet->setCellValue($this->getColLetter($colIdx).$currentRow, $record->out_data[$s['key']] ?? 0); $colIdx++; }
            foreach($allSizes as $s) { $sheet->setCellValue($this->getColLetter($colIdx).$currentRow, $record->stock_data[$s['key']] ?? 0); $colIdx++; }

            $sheet->setCellValue($this->getColLetter($colIdx).$currentRow, $record->exporter);
            $sheet->setCellValue($this->getColLetter($colIdx+1).$currentRow, $record->receiver);
            $currentRow++;
        }

        // Styling
        $totalCols = $colIdx + 1;
        $maxCol = $this->getColLetter($totalCols);
        $headerRange = "A1:{$maxCol}3";
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        
        // Summary row styling
        $sumRange = "A4:{$maxCol}4";
        $sheet->getStyle($sumRange)->getFont()->setBold(true);
        $sheet->getStyle($sumRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F0F0');

        // Main Headers coloring
        $sheet->getStyle($inColStart.'1:'.$inColEnd.'1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAD3');
        $sheet->getStyle($outColStart.'1:'.$outColEnd.'1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF4CCCC');
        $sheet->getStyle($stockColStart.'1:'.$stockColEnd.'1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF2CC');

        // Borders
        $fullRange = "A1:{$maxCol}".($currentRow-1);
        $sheet->getStyle($fullRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Auto-size columns
        for($i=1; $i<=$totalCols; $i++) {
            $sheet->getColumnDimension($this->getColLetter($i))->setAutoSize(true);
        }

        $filename = 'Export_Kho_' . $warehouse->name . '_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. $filename .'"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    public function printRecordVoucher(Warehouse $warehouse, WarehouseRecord $record)
    {
        if ((int)$record->warehouse_id !== (int)$warehouse->id) {
            abort(404);
        }

        // Prepare items for display
        $items = [];
        $itemName = $warehouse->item_name ?? '';
        $allSizes = $this->getWarehouseSizes($warehouse);
        foreach ($allSizes as $s) {
            $qty = $record->out_data[$s['key']] ?? 0;
            if ($qty == 0) {
                $qty = $record->in_data[$s['key']] ?? 0;
            }
            if ($qty > 0) {
                $items[] = [
                    'item_name' => $itemName,
                    'group'     => $s['group'],
                    'group_code'=> $s['group_code'] ?? '',
                    'size'      => $s['size'],
                    'size_code' => $s['size_code'] ?? '',
                    'label'     => $s['label'],
                    'qty'       => $qty,
                    'price'     => $s['price'] ?? 0, // Dynamic price from config
                ];
            }
        }

        return view('warehouses.print-voucher', compact('warehouse', 'record', 'items'));
    }

    private function getColLetter($colIdx)
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
    }

    public function getMonthlyMatrixData(Warehouse $warehouse, $year = null)
    {
        $allSizes = $this->getWarehouseSizes($warehouse);

        // Find all available years from records
        $years = $warehouse->records()
            ->selectRaw('DISTINCT YEAR(date) as y')
            ->whereNotNull('date')
            ->orderByDesc('y')
            ->pluck('y')
            ->map(fn($v) => (int)$v)
            ->filter(fn($v) => $v > 2000)
            ->values()
            ->toArray();

        if (empty($years)) {
            $years = [(int)date('Y')];
        }

        $selectedYear = $year ? (int)$year : (int)($years[0] ?? date('Y'));

        // All records
        $allRecords = $warehouse->records()->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

        // Previous year ending stock = Start of selected year stock
        $lastRecordPrevYear = $allRecords->filter(fn($r) => date('Y', strtotime($r->date)) < $selectedYear)->last();
        $startOfYearStock = $lastRecordPrevYear ? ($lastRecordPrevYear->stock_data ?? []) : [];

        // Records for selected year
        $yearRecords = $allRecords->filter(fn($r) => date('Y', strtotime($r->date)) == $selectedYear);

        // Determine max month with data in this year
        $maxMonth = 0;
        foreach ($yearRecords as $r) {
            $m = (int)date('n', strtotime($r->date));
            if ($m > $maxMonth) $maxMonth = $m;
        }

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

        $lowStockCount = 0;
        $outOfStockCount = 0;

        foreach ($allSizes as $idx => $s) {
            $key = $s['key'];
            $initStock = (float)($startOfYearStock[$key] ?? 0);
            $runningStock = $initStock;

            $rowMonths = [];
            $totalYearIn = 0;
            $totalYearOut = 0;

            for ($m = 1; $m <= 12; $m++) {
                $monthRecs = $yearRecords->filter(fn($r) => (int)date('n', strtotime($r->date)) == $m);
                $mIn = (float)$monthRecs->sum(fn($r) => (float)($r->in_data[$key] ?? 0));
                $mOut = (float)$monthRecs->sum(fn($r) => (float)($r->out_data[$key] ?? 0));

                $totalYearIn += $mIn;
                $totalYearOut += $mOut;

                $hasData = ($m <= $maxMonth && $maxMonth > 0);
                if ($hasData) {
                    $runningStock = round($runningStock + $mIn - $mOut, 2);
                }

                $rowMonths[$m] = [
                    'in' => $mIn,
                    'out' => $mOut,
                    'stock' => $runningStock,
                    'has_data' => $hasData,
                ];

                $colTotals['months'][$m]['in'] += $mIn;
                $colTotals['months'][$m]['out'] += $mOut;
                if ($hasData) {
                    $colTotals['months'][$m]['stock'] += $runningStock;
                }
            }

            if ($runningStock <= 0 && $totalYearIn == 0 && $initStock == 0) {
                // Not in stock
                $stockStatus = 'empty';
            } elseif ($runningStock <= 0) {
                $outOfStockCount++;
                $stockStatus = 'out_of_stock';
            } elseif ($runningStock <= 50) {
                $lowStockCount++;
                $stockStatus = 'danger_low';
            } elseif ($runningStock <= 150) {
                $lowStockCount++;
                $stockStatus = 'warning_low';
            } else {
                $stockStatus = 'normal';
            }

            $matrixRows[] = [
                'stt' => $idx + 1,
                'item' => $s,
                'code' => $s['size'] ?: $s['group'],
                'origin_code' => $s['size_code'] ?? '',
                'group' => $s['group'],
                'price' => $s['price'] ?? 0,
                'start_stock' => $initStock,
                'months' => $rowMonths,
                'total_year_in' => $totalYearIn,
                'total_year_out' => $totalYearOut,
                'final_stock' => $runningStock,
                'stock_status' => $stockStatus,
            ];

            $colTotals['start_stock'] += $initStock;
            $colTotals['total_in'] += $totalYearIn;
            $colTotals['total_out'] += $totalYearOut;
            $colTotals['final_stock'] += $runningStock;
        }

        return [
            'years' => $years,
            'year' => $selectedYear,
            'max_month' => $maxMonth,
            'rows' => $matrixRows,
            'col_totals' => $colTotals,
            'kpis' => [
                'total_items' => count($matrixRows),
                'total_in' => $colTotals['total_in'],
                'total_out' => $colTotals['total_out'],
                'final_stock' => $colTotals['final_stock'],
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
            ]
        ];
    }

    public function exportMatrix(Request $request, Warehouse $warehouse)
    {
        $year = (int)$request->input('year', date('Y'));
        $data = $this->getMonthlyMatrixData($warehouse, $year);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('TỒN_TIÊU_THỤ_' . $year);

        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];

        // Title
        $sheet->setCellValue('A1', 'BÁO CÁO TỔNG HỢP TỒN KHO & TIÊU THỤ - NĂM ' . $year . ' - ' . strtoupper($warehouse->name));
        $sheet->mergeCells('A1:AP1');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Fixed Headers
        $fixedHeaders = [
            'A' => 'STT',
            'B' => 'MÃ GERVIN',
            'C' => 'MÃ XUẤT XỨ',
            'D' => 'NHÓM MÀU / PHÂN LOẠI',
            'E' => 'TỒN ĐẦU NĂM'
        ];
        foreach ($fixedHeaders as $col => $title) {
            $sheet->setCellValue($col . '2', $title);
            $sheet->mergeCells($col . '2:' . $col . '3');
            $sheet->getStyle($col . '2:' . $col . '3')->applyFromArray($headerStyle);
        }

        // Months 1..12
        $currCol = 6;
        for ($m = 1; $m <= 12; $m++) {
            $cIn = $this->getColLetter($currCol);
            $cOut = $this->getColLetter($currCol + 1);
            $cStock = $this->getColLetter($currCol + 2);

            $mTitle = sprintf('THÁNG %02d.%04d', $m, $year);
            $sheet->setCellValue($cIn . '2', $mTitle);
            $sheet->mergeCells($cIn . '2:' . $cStock . '2');
            $sheet->getStyle($cIn . '2:' . $cStock . '2')->applyFromArray($headerStyle);

            $sheet->setCellValue($cIn . '3', 'NHẬN');
            $sheet->setCellValue($cOut . '3', 'TIÊU THỤ');
            $sheet->setCellValue($cStock . '3', 'TỒN CUỐI');

            $sheet->getStyle($cIn . '3')->applyFromArray($headerStyle)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E2F3F1');
            $sheet->getStyle($cOut . '3')->applyFromArray($headerStyle)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE4E6');
            $sheet->getStyle($cStock . '3')->applyFromArray($headerStyle)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FEF3C7');

            $currCol += 3;
        }

        // Annual Totals
        $cTotIn = $this->getColLetter($currCol);
        $cTotOut = $this->getColLetter($currCol + 1);
        $cTotStock = $this->getColLetter($currCol + 2);

        $sheet->setCellValue($cTotIn . '2', 'TỔNG KẾT NĂM ' . $year);
        $sheet->mergeCells($cTotIn . '2:' . $cTotStock . '2');
        $sheet->getStyle($cTotIn . '2:' . $cTotStock . '2')->applyFromArray($headerStyle);

        $sheet->setCellValue($cTotIn . '3', 'TỔNG NHẬN');
        $sheet->setCellValue($cTotOut . '3', 'TỔNG TIÊU THỤ');
        $sheet->setCellValue($cTotStock . '3', 'TỒN HIỆN TẠI');
        $sheet->getStyle($cTotIn . '3:' . $cTotStock . '3')->applyFromArray($headerStyle);

        // Data rows
        $rowIdx = 4;
        foreach ($data['rows'] as $r) {
            $sheet->setCellValue('A' . $rowIdx, $r['stt']);
            $sheet->setCellValue('B' . $rowIdx, $r['code']);
            $sheet->setCellValue('C' . $rowIdx, $r['origin_code']);
            $sheet->setCellValue('D' . $rowIdx, $r['group']);
            $sheet->setCellValue('E' . $rowIdx, $r['start_stock']);

            $col = 6;
            for ($m = 1; $m <= 12; $m++) {
                $cIn = $this->getColLetter($col);
                $cOut = $this->getColLetter($col + 1);
                $cStock = $this->getColLetter($col + 2);

                $sheet->setCellValue($cIn . $rowIdx, $r['months'][$m]['in']);
                $sheet->setCellValue($cOut . $rowIdx, $r['months'][$m]['out']);
                $sheet->setCellValue($cStock . $rowIdx, $r['months'][$m]['has_data'] ? $r['months'][$m]['stock'] : 0);

                $col += 3;
            }

            $cTotIn = $this->getColLetter($col);
            $cTotOut = $this->getColLetter($col + 1);
            $cTotStock = $this->getColLetter($col + 2);

            $sheet->setCellValue($cTotIn . $rowIdx, $r['total_year_in']);
            $sheet->setCellValue($cTotOut . $rowIdx, $r['total_year_out']);
            $sheet->setCellValue($cTotStock . $rowIdx, $r['final_stock']);

            $sheet->getStyle('A' . $rowIdx . ':' . $cTotStock . $rowIdx)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('E' . $rowIdx . ':' . $cTotStock . $rowIdx)->getNumberFormat()->setFormatCode('#,##0.00');

            $rowIdx++;
        }

        // Summary row (Total)
        $sheet->setCellValue('A' . $rowIdx, 'TỔNG CỘNG');
        $sheet->mergeCells('A' . $rowIdx . ':D' . $rowIdx);
        $sheet->setCellValue('E' . $rowIdx, $data['col_totals']['start_stock']);

        $col = 6;
        for ($m = 1; $m <= 12; $m++) {
            $cIn = $this->getColLetter($col);
            $cOut = $this->getColLetter($col + 1);
            $cStock = $this->getColLetter($col + 2);

            $sheet->setCellValue($cIn . $rowIdx, $data['col_totals']['months'][$m]['in']);
            $sheet->setCellValue($cOut . $rowIdx, $data['col_totals']['months'][$m]['out']);
            $sheet->setCellValue($cStock . $rowIdx, $data['col_totals']['months'][$m]['stock']);

            $col += 3;
        }

        $cTotIn = $this->getColLetter($col);
        $cTotOut = $this->getColLetter($col + 1);
        $cTotStock = $this->getColLetter($col + 2);

        $sheet->setCellValue($cTotIn . $rowIdx, $data['col_totals']['total_in']);
        $sheet->setCellValue($cTotOut . $rowIdx, $data['col_totals']['total_out']);
        $sheet->setCellValue($cTotStock . $rowIdx, $data['col_totals']['final_stock']);

        $sheet->getStyle('A' . $rowIdx . ':' . $cTotStock . $rowIdx)->applyFromArray([
            'font' => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $sheet->getStyle('E' . $rowIdx . ':' . $cTotStock . $rowIdx)->getNumberFormat()->setFormatCode('#,##0.00');

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(26);
        $sheet->getColumnDimension('E')->setWidth(14);
        for ($c = 6; $c <= $currCol + 2; $c++) {
            $sheet->getColumnDimension($this->getColLetter($c))->setWidth(12);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Bao_Cao_Tieu_Thu_' . $year . '_' . str_replace(' ', '_', $warehouse->name) . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $writer->save('php://output');
        exit;
    }

    public function importMatrix(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'matrix_file' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            $file = $request->file('matrix_file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();

            $highestRow = $sheet->getHighestRow();
            $highestCol = $sheet->getHighestColumn();
            $highestColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

            // 1. Parse items (row 5 onwards)
            $groupedItems = [];
            $itemsMap = [];
            for ($r = 5; $r <= $highestRow; $r++) {
                $codeGV = trim((string)$sheet->getCell("E$r")->getValue());
                if (!$codeGV) continue;

                $groupName = trim((string)$sheet->getCell("F$r")->getValue()) ?: 'Phôi Acrylic';
                $originCode = trim((string)$sheet->getCell("C$r")->getValue());
                $nameEn = trim((string)$sheet->getCell("D$r")->getValue());
                $initStock = (float)$sheet->getCell("G$r")->getCalculatedValue();

                if (!isset($groupedItems[$groupName])) {
                    $groupedItems[$groupName] = [];
                }

                $groupedItems[$groupName][] = [
                    'name' => $codeGV,
                    'code' => $originCode,
                    'price' => 0,
                    'description' => $nameEn
                ];

                $itemsMap[$codeGV] = [
                    'row' => $r,
                    'group' => $groupName,
                    'code' => $codeGV,
                    'origin_code' => $originCode,
                    'key' => $groupName . '_' . $codeGV,
                    'initial_stock' => $initStock,
                ];
            }

            if (empty($itemsMap)) {
                return back()->withErrors(['matrix_file' => 'Không tìm thấy dữ liệu mặt hàng hợp lệ trong file Excel (Cột E từ dòng 5 trở đi).']);
            }

            // Update warehouse config if empty
            if (empty($warehouse->sizes_config)) {
                $sizesConfig = [];
                foreach ($groupedItems as $gName => $sizes) {
                    $sizesConfig[] = [
                        'name' => $gName,
                        'code' => '',
                        'price' => 0,
                        'sizes' => $sizes
                    ];
                }
                $warehouse->update([
                    'item_name' => $warehouse->item_name ?: 'Tấm Acrylic',
                    'sizes_config' => $sizesConfig
                ]);
            }

            // 2. Initial Stock Record
            $initInData = [];
            foreach ($itemsMap as $codeGV => $info) {
                if ($info['initial_stock'] > 0) {
                    $initInData[$info['key']] = $info['initial_stock'];
                }
            }

            if (!empty($initInData) && !$warehouse->records()->where('voucher_no', 'DK-2023')->exists()) {
                $warehouse->records()->create([
                    'voucher_no' => 'DK-2023',
                    'date' => '2023-12-31',
                    'content' => 'Số dư tồn kho đầu kỳ từ file kế toán',
                    'exporter' => 'Kế toán',
                    'receiver' => $warehouse->name,
                    'in_data' => $initInData,
                    'out_data' => [],
                    'stock_data' => []
                ]);
            }

            // 3. Monthly records
            $importedCount = 0;
            for ($col = 8; $col <= $highestColIdx; $col += 3) {
                $colLetterIn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $colLetterOut = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);

                $title1 = trim((string)$sheet->getCell($colLetterIn . '1')->getValue());
                if (!$title1 && $col <= $highestColIdx) {
                    $prevCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col - 1);
                    $title1 = trim((string)$sheet->getCell($prevCol . '1')->getValue());
                }
                if (!$title1) continue;

                $year = null;
                $month = null;
                if (preg_match('/(\d{4})/', $title1, $yMatches)) {
                    $year = (int)$yMatches[1];
                }
                if (preg_match('/(?:THÁNG\s*)(\d+)/i', $title1, $mMatches)) {
                    $month = (int)$mMatches[1];
                } elseif (preg_match('/(\d+)\s*THÁNG/i', $title1, $mMatches)) {
                    $month = (int)$mMatches[1];
                }

                if (!$year || !$month) continue;

                $day = 28;
                if (preg_match('/^(\d+)\s*THÁNG/i', $title1, $dMatches)) {
                    $day = (int)$dMatches[1];
                }

                $recordDate = sprintf('%04d-%02d-%02d', $year, $month, min($day, 28));
                $isKiemKe = stripos($title1, 'kiểm kê') !== false;
                $voucherPrefix = $isKiemKe ? 'KK' : 'TH';
                $voucherNo = sprintf('%s-%04d%02d%s', $voucherPrefix, $year, $month, $isKiemKe ? '-K' : '');

                $inData = [];
                $outData = [];

                foreach ($itemsMap as $codeGV => $info) {
                    $r = $info['row'];
                    $inVal = (float)$sheet->getCell($colLetterIn . $r)->getCalculatedValue();
                    $outVal = (float)$sheet->getCell($colLetterOut . $r)->getCalculatedValue();

                    if ($inVal > 0) $inData[$info['key']] = $inVal;
                    if ($outVal > 0) $outData[$info['key']] = $outVal;
                }

                if (!empty($inData) || !empty($outData)) {
                    $warehouse->records()->updateOrCreate(
                        ['voucher_no' => $voucherNo],
                        [
                            'date' => $recordDate,
                            'content' => "Tổng hợp {$title1}",
                            'exporter' => 'Nhà máy / Kho',
                            'receiver' => 'Xưởng sản xuất Gervin',
                            'in_data' => $inData,
                            'out_data' => $outData,
                        ]
                    );
                    $importedCount++;
                }
            }

            // Recalculate
            $this->recalculateStock($warehouse);

            return back()->with('success', "Đã nhập thành công dữ liệu ma trận từ file Excel ($importedCount kỳ)!");
        } catch (\Exception $e) {
            \Log::error('Import Matrix Error: ' . $e->getMessage());
            return back()->withErrors(['matrix_file' => 'Lỗi khi đọc file Excel: ' . $e->getMessage()]);
        }
    }
}
