<?php

namespace App\Http\Controllers;

use App\Models\MinLatePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class MinLatePriceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view supply',   ['only' => ['index', 'export']]);
        $this->middleware('permission:add supply',    ['only' => ['store', 'import']]);
        $this->middleware('permission:edit supply',   ['only' => ['update']]);
        $this->middleware('permission:delete supply', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search  = $request->input('search', '');
        $filterCategory = $request->input('filter_category', '');

        // Fetch categories for filtering dropdown
        $categories = MinLatePrice::select('category_name')
            ->whereNotNull('category_name')
            ->groupBy('category_name')
            ->pluck('category_name');

        $minlatePrices = MinLatePrice::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($subQ) use ($search) {
                    $subQ->where('product_name', 'like', "%$search%")
                         ->orWhere('code', 'like', "%$search%")
                         ->orWhere('notes', 'like', "%$search%");
                });
            })
            ->when($filterCategory, function ($q) use ($filterCategory) {
                $q->where('category_name', $filterCategory);
            })
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return view('minlate_prices.index', compact('minlatePrices', 'categories', 'perPage', 'search', 'filterCategory'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'nullable|string|max:255',
            'stt'           => 'nullable|string|max:50',
            'product_name'  => 'required|string',
            'unit'          => 'nullable|string|max:50',
            'price'         => 'required|numeric|min:0',
            'code'          => 'nullable|string|max:100',
            'notes'         => 'nullable|string',
        ]);

        MinLatePrice::create([
            'category_name' => $request->category_name,
            'stt'           => $request->stt,
            'product_name'  => $request->product_name,
            'unit'          => $request->unit,
            'price'         => floatval($request->price),
            'code'          => $request->code ?: null,
            'notes'         => $request->notes,
        ]);

        return redirect()->route('minlate_prices.index')->with('success', 'Thêm dòng bảng giá dịch vụ Min-late thành công.');
    }

    public function update(Request $request, MinLatePrice $minlatePrice)
    {
        $request->validate([
            'category_name' => 'nullable|string|max:255',
            'stt'           => 'nullable|string|max:50',
            'product_name'  => 'required|string',
            'unit'          => 'nullable|string|max:50',
            'price'         => 'required|numeric|min:0',
            'code'          => 'nullable|string|max:100',
            'notes'         => 'nullable|string',
        ]);

        $minlatePrice->update([
            'category_name' => $request->category_name,
            'stt'           => $request->stt,
            'product_name'  => $request->product_name,
            'unit'          => $request->unit,
            'price'         => floatval($request->price),
            'code'          => $request->code ?: null,
            'notes'          => $request->notes,
        ]);

        return redirect()->route('minlate_prices.index')->with('success', 'Cập nhật dòng bảng giá dịch vụ Min-late thành công.');
    }

    public function destroy(MinLatePrice $minlatePrice)
    {
        $minlatePrice->delete();
        return redirect()->route('minlate_prices.index')->with('success', 'Xóa dòng bảng giá dịch vụ Min-late thành công.');
    }

    public function export()
    {
        $items = MinLatePrice::orderBy('id', 'asc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Bảng giá dịch vụ Min-late');

        // Title row 1
        $sheet->setCellValue('A1', 'BẢNG GIÁ DỊCH VỤ GIA CÔNG MIN-LATE GERVIN');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF166534'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(32);

        // Subtitle row 2
        $sheet->setCellValue('A2', 'Ngày xuất dữ liệu: ' . date('d/m/Y H:i') . ' | Tổng số dòng: ' . $items->count());
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // Header row 3
        $headers = [
            'A3' => 'STT',
            'B3' => 'MÃ DỊCH VỤ',
            'C3' => 'TÊN SẢN PHẨM / DỊCH VỤ GIA CÔNG',
            'D3' => 'ĐƠN VỊ TÍNH',
            'E3' => 'ĐƠN GIÁ (VNĐ)',
            'F3' => 'GHI CHÚ',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '166534'], // Dark green
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ];
        $sheet->getStyle('A3:F3')->applyFromArray($headerStyle);
        $sheet->getRowDimension(3)->setRowHeight(28);

        $currentRow = 4;
        $currentCategory = null;
        $categoryIndex = 0;

        foreach ($items as $item) {
            // Category separator row
            if ($item->category_name !== $currentCategory) {
                $currentCategory = $item->category_name;
                $categoryIndex++;
                $roman = $this->toRomanNumeral($categoryIndex);

                $sheet->setCellValue('A' . $currentRow, $roman);
                $sheet->setCellValue('B' . $currentRow, mb_strtoupper($currentCategory ?: 'CHƯA PHÂN NHÓM', 'UTF-8'));
                $sheet->mergeCells('B' . $currentRow . ':F' . $currentRow);

                $catStyle = [
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '064E3B'],
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D1FAE5'], // Soft emerald green
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC'],
                        ],
                    ],
                ];
                $sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->applyFromArray($catStyle);
                $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension($currentRow)->setRowHeight(24);
                $currentRow++;
            }

            // Data row
            $sheet->setCellValue('A' . $currentRow, $item->stt ?: '');
            $sheet->setCellValueExplicit('B' . $currentRow, (string)($item->code ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $currentRow, $item->product_name);
            $sheet->setCellValue('D' . $currentRow, $item->unit ?: '');
            $sheet->setCellValue('E' . $currentRow, $item->price);
            $sheet->setCellValue('F' . $currentRow, $item->notes ?: '');

            // Formatting
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $currentRow)->getFont()->setBold(true);
            $sheet->getStyle('C' . $currentRow)->getAlignment()->setWrapText(true);
            $sheet->getStyle('D' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $currentRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('E' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('F' . $currentRow)->getAlignment()->setWrapText(true);

            $sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFE2E8F0'));

            $currentRow++;
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(55);
        $sheet->getColumnDimension('D')->setWidth(14);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(30);

        $filename = 'Bang_gia_dich_vu_Min_late_Gervin_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getSheetByName("Đơn giá d.vụ sử dụng VL Gervin") ?: $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (count($rows) < 2) {
                return back()->with('error', 'File Excel không đúng cấu trúc hoặc không có dữ liệu.');
            }

            DB::transaction(function () use ($rows) {
                // Delete existing records
                MinLatePrice::query()->delete();

                // 1. Detect Header row and column indexes accurately
                $headerRowIdx = null;
                $colStt = 0;
                $colCode = -1;
                $colName = 1;
                $colUnit = 2;
                $colPrice = 3;
                $colNotes = 4;

                for ($r = 0; $r < min(10, count($rows)); $r++) {
                    $row = $rows[$r];
                    
                    // Header row must have at least 3 non-empty cells
                    $nonEmptyCells = 0;
                    foreach ($row as $cell) {
                        if (!is_null($cell) && trim((string)$cell) !== '') {
                            $nonEmptyCells++;
                        }
                    }
                    if ($nonEmptyCells < 3) {
                        continue;
                    }

                    $detectedStt = -1;
                    $detectedCode = -1;
                    $detectedName = -1;
                    $detectedUnit = -1;
                    $detectedPrice = -1;
                    $detectedNotes = -1;

                    foreach ($row as $idx => $cell) {
                        $val = mb_strtolower(trim((string)$cell), 'UTF-8');
                        if (empty($val)) continue;

                        if ($val === 'stt' || preg_match('/^(stt|số thứ tự)$/u', $val)) {
                            $detectedStt = $idx;
                        } elseif (preg_match('/(mã dịch vụ|mã sp|mã|code)/u', $val) && !preg_match('/(sản phẩm|quy cách)/u', $val)) {
                            $detectedCode = $idx;
                        } elseif (preg_match('/(sản phẩm|tên sản phẩm|tên dịch vụ|quy cách|nội dung)/u', $val)) {
                            $detectedName = $idx;
                        } elseif (preg_match('/(đơn vị|đvt)/u', $val)) {
                            $detectedUnit = $idx;
                        } elseif (preg_match('/(đơn giá|giá)/u', $val) && !str_contains($val, 'bảng giá')) {
                            $detectedPrice = $idx;
                        } elseif (preg_match('/(ghi chú|note)/u', $val)) {
                            $detectedNotes = $idx;
                        }
                    }

                    if ($detectedName >= 0 && $detectedPrice >= 0 && ($detectedStt >= 0 || $detectedUnit >= 0)) {
                        $headerRowIdx = $r;
                        $colStt   = $detectedStt >= 0 ? $detectedStt : 0;
                        $colCode  = $detectedCode;
                        $colName  = $detectedName;
                        $colUnit  = $detectedUnit >= 0 ? $detectedUnit : ($detectedName + 1);
                        $colPrice = $detectedPrice;
                        $colNotes = $detectedNotes >= 0 ? $detectedNotes : ($detectedPrice + 1);
                        break;
                    }
                }

                $startRow = ($headerRowIdx !== null) ? ($headerRowIdx + 1) : 3;
                $currentCategory = null;

                for ($i = $startRow; $i < count($rows); $i++) {
                    $row = $rows[$i];
                    
                    // Check if row is completely empty
                    $isEmpty = true;
                    foreach ($row as $cell) {
                        if (!is_null($cell) && trim((string)$cell) !== '') {
                            $isEmpty = false;
                            break;
                        }
                    }
                    if ($isEmpty) {
                        continue;
                    }

                    $stt         = isset($row[$colStt]) ? trim((string)$row[$colStt]) : '';
                    $code        = ($colCode >= 0 && isset($row[$colCode])) ? trim((string)$row[$colCode]) : '';
                    $productName = isset($row[$colName]) ? trim((string)$row[$colName]) : '';
                    $unit        = isset($row[$colUnit]) ? trim((string)$row[$colUnit]) : '';
                    $priceVal    = isset($row[$colPrice]) ? $row[$colPrice] : null;
                    $notes       = isset($row[$colNotes]) ? trim((string)$row[$colNotes]) : '';

                    // Check if Category Header Row (e.g. STT is Roman Numeral like I, II, III and Price is empty)
                    $priceStr = trim((string)$priceVal);
                    if ($this->isRomanNumeral($stt) && ($priceStr === '' || is_null($priceVal))) {
                        // Find category name: non-empty cell in row after STT
                        $catName = '';
                        for ($c = 1; $c < count($row); $c++) {
                            $cVal = trim((string)($row[$c] ?? ''));
                            if (!empty($cVal)) {
                                $catName = $cVal;
                                break;
                            }
                        }
                        $currentCategory = !empty($catName) ? $catName : $stt;
                        continue;
                    }

                    // Skip note rows that put long text in the STT column
                    if (mb_strlen($stt) > 10) {
                        continue;
                    }

                    // Ignore non-data bottom note rows
                    if (empty($stt) && empty($unit) && ($priceStr === '' || is_null($priceVal))) {
                        continue;
                    }

                    if (empty($productName)) {
                        continue;
                    }

                    // Save standard data row
                    $cleanNotes = $notes;
                    $price = $this->cleanPrice($priceVal, $cleanNotes);

                    MinLatePrice::create([
                        'category_name' => !empty($currentCategory) ? mb_substr($currentCategory, 0, 255, 'UTF-8') : null,
                        'stt'           => !empty($stt) ? mb_substr($stt, 0, 50, 'UTF-8') : null,
                        'product_name'  => $productName,
                        'unit'          => !empty($unit) ? mb_substr($unit, 0, 50, 'UTF-8') : null,
                        'price'         => $price,
                        'code'          => !empty($code) ? mb_substr($code, 0, 100, 'UTF-8') : null,
                        'notes'         => !empty($cleanNotes) ? $cleanNotes : null,
                    ]);
                }
            });

            return back()->with('success', 'Nhập bảng giá dịch vụ Min-late thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Đã xảy ra lỗi khi nhập file: ' . $e->getMessage());
        }
    }

    private function toRomanNumeral(int $num): string
    {
        $map = [
            1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD',
            100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL',
            10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I'
        ];
        $result = '';
        foreach ($map as $value => $roman) {
            while ($num >= $value) {
                $result .= $roman;
                $num -= $value;
            }
        }
        return $result ?: (string)$num;
    }

    private function isRomanNumeral(?string $val): bool
    {
        if (!$val) {
            return false;
        }
        return (bool) preg_match('/^[IVXLCDM]+$/i', trim($val));
    }

    private function cleanPrice($val, &$notes = null): float
    {
        if (is_null($val) || $val === '') {
            return 0.0;
        }

        // Convert to string and clean spaces/currency
        $valStr = trim((string)$val);
        
        // Remove trailing "đ", "VND", " đồng"
        $valStr = preg_replace('/(đ|vnd|đồng)\s*$/i', '', $valStr);
        $valStr = trim($valStr);

        if (is_numeric($valStr)) {
            $num = floatval($valStr);
            // If the price is entered as a small number (like 35 or 120) meaning thousands
            if ($num < 1000 && $num > 0) {
                return $num * 1000;
            }
            return $num;
        }

        if ($valStr === 'Miễn phí' || $valStr === 'Free') {
            if ($notes !== null && $notes !== '') {
                $notes = trim($notes . ' (Miễn phí)');
            } else {
                $notes = 'Miễn phí';
            }
            return 0.0;
        }

        // Check for format like "35 ngàn" or "35k"
        if (preg_match('/^(\d+)\s*(ngàn|k)/iu', $valStr, $matches)) {
            return floatval($matches[1]) * 1000;
        }

        // Check if it is a number with thousands separators (can be dots or commas)
        // For example: "35,000" or "35.000"
        // Let's remove all dots, commas, spaces and see if it's numeric
        $cleanedNumStr = str_replace([',', '.', ' '], '', $valStr);
        if (is_numeric($cleanedNumStr)) {
            $num = floatval($cleanedNumStr);
            return $num;
        }

        // If it's still not a pure number, it might be a text description with a number inside
        // For example: "35,000 (bao gồm vận chuyển)"
        // Let's try to extract the number
        if (preg_match('/^([\d\.,\s]+)/', $valStr, $matches)) {
            $numPart = trim($matches[1]);
            $cleanedPart = str_replace([',', '.', ' '], '', $numPart);
            if (is_numeric($cleanedPart)) {
                // If there's extra text, append the original text to notes
                if (strlen($valStr) > strlen($numPart)) {
                    if ($notes !== null && $notes !== '') {
                        $notes = trim($notes . ' (' . $valStr . ')');
                    } else {
                        $notes = $valStr;
                    }
                }
                return floatval($cleanedPart);
            }
        }

        // Otherwise, it is a textual note (e.g. "Thỏa thuận")
        if ($notes !== null && $notes !== '') {
            $notes = trim($notes . ' (' . $valStr . ')');
        } else {
            $notes = $valStr;
        }
        return 0.0;
    }
}
