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
        $this->middleware('permission:view supply',   ['only' => ['index', 'show', 'exportTemplate', 'exportData', 'printRecordVoucher']]);
        $this->middleware('permission:add supply',    ['only' => ['store', 'storeRecord', 'import', 'importJson']]);
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

    public function show(Warehouse $warehouse)
    {
        $records = $warehouse->records()->orderBy('date', 'desc')->orderBy('id', 'desc')->get();
        $allSizes = $this->getWarehouseSizes($warehouse);

        $lastVoucher = $warehouse->records()->orderBy('id', 'desc')->value('voucher_no');
        $nextVoucher = $this->generateNextVoucher($lastVoucher);

        return view('warehouses.show', compact('warehouse', 'records', 'allSizes', 'nextVoucher'));
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
}
