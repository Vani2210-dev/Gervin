<?php

namespace App\Http\Controllers;

use App\Models\DcStock;
use App\Models\WoodBoard;
use App\Models\WoodBoardType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DcStockController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && ($user->can('view dc stock') || $user->hasRole('Admin')), 403);

        $search    = $request->input('search', '');
        $status    = $request->input('status', '');
        $boardType = $request->input('board_type', '');
        $perPage   = (int) $request->input('per_page', 25);

        $query = DcStock::with(['creator', 'boardTypeRel', 'woodBoard'])
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('color_code', 'like', "%{$search}%")
                  ->orWhere('board_code', 'like', "%{$search}%")
                  ->orWhere('board_type', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($boardType) {
            if ($boardType === 'CỐT NHỰA PVC' || $boardType === 'PVC') {
                $query->where(function ($q) {
                    $q->where('board_type', 'like', '%NHỰA%')
                      ->orWhere('board_type', 'like', '%PVC%');
                });
            } else {
                $query->where('board_type', $boardType);
            }
        }

        $this->applyDateFilter($query, $request, 'created_at');

        $stocks = $query->paginate($perPage)->withQueryString();

        // Lấy danh sách Loại ván
        $boardTypes = WoodBoardType::orderBy('display_order')->get();

        // Lấy danh sách mã màu để gợi ý khi nhập
        $colorCodes = WoodBoard::select('color_code')->distinct()->orderBy('color_code')->pluck('color_code')
            ->merge(DcStock::select('color_code')->distinct()->pluck('color_code'))
            ->filter()->unique()->sort()->values();

        // Thống kê nhanh
        $totalAvailable = DcStock::where('status', 'available')->count();
        $totalUsed      = DcStock::where('status', 'used')->count();
        $totalReserved  = DcStock::where('status', 'reserved')->count();

        return view('dc_stocks.index', compact(
            'stocks', 'colorCodes', 'boardTypes', 'search', 'status', 'boardType', 'perPage',
            'totalAvailable', 'totalUsed', 'totalReserved'
        ));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('add dc stock'), 403);

        $data = $request->validate([
            'color_code'         => 'required|string|max:50',
            'board_type'         => 'nullable|string|max:100',
            'board_code'         => 'nullable|string|max:100',
            'thickness'          => 'nullable|string|max:50',
            'wood_board_type_id' => 'nullable|integer|exists:wood_board_types,id',
            'note'               => 'nullable|string|max:100',
            'height'             => 'required|integer|min:1',
            'width'              => 'required|integer|min:1',
            'quantity'           => 'required|integer|min:1',
            'location'           => 'nullable|string|max:50',
            'status'             => 'required|in:available,used,reserved',
        ]);

        if (empty($data['board_code'])) {
            $prefix = '';
            if (!empty($data['wood_board_type_id'])) {
                $bt = WoodBoardType::find($data['wood_board_type_id']);
                $prefix = $bt?->prefix ?? '';
                if (empty($data['board_type'])) {
                    $data['board_type'] = $bt?->name;
                }
            }
            $data['board_code'] = $data['color_code'] . $prefix;
        }

        if (empty($data['thickness'])) {
            $data['thickness'] = '17mm';
        }

        $data['created_by'] = Auth::id();

        DcStock::create($data);

        return response()->json(['success' => true, 'message' => 'Đã thêm tấm dư vào Kho DC thành công!']);
    }

    public function update(Request $request, DcStock $dcStock)
    {
        abort_unless(auth()->user()->can('edit dc stock'), 403);

        $data = $request->validate([
            'color_code'         => 'required|string|max:50',
            'board_type'         => 'nullable|string|max:100',
            'board_code'         => 'nullable|string|max:100',
            'thickness'          => 'nullable|string|max:50',
            'wood_board_type_id' => 'nullable|integer|exists:wood_board_types,id',
            'note'               => 'nullable|string|max:100',
            'height'             => 'required|integer|min:1',
            'width'              => 'required|integer|min:1',
            'quantity'           => 'required|integer|min:1',
            'location'           => 'nullable|string|max:50',
            'status'             => 'required|in:available,used,reserved',
        ]);

        if (empty($data['board_code'])) {
            $prefix = '';
            if (!empty($data['wood_board_type_id'])) {
                $bt = WoodBoardType::find($data['wood_board_type_id']);
                $prefix = $bt?->prefix ?? '';
                if (empty($data['board_type'])) {
                    $data['board_type'] = $bt?->name;
                }
            }
            $data['board_code'] = $data['color_code'] . $prefix;
        }

        if (empty($data['thickness'])) {
            $data['thickness'] = '17mm';
        }

        $dcStock->update($data);

        return response()->json(['success' => true, 'message' => 'Đã cập nhật thông tin tấm dư!']);
    }

    public function destroy(DcStock $dcStock)
    {
        abort_unless(auth()->user()->can('delete dc stock'), 403);

        $dcStock->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa khỏi Kho DC!']);
    }

    public function updateStatus(Request $request, DcStock $dcStock)
    {
        abort_unless(auth()->user()->can('edit dc stock'), 403);

        $request->validate(['status' => 'required|in:available,used,reserved']);
        $dcStock->update(['status' => $request->status]);

        return response()->json(['success' => true]);
    }

    /**
     * Nhập dữ liệu tấm dư từ file Excel / CSV
     */
    public function import(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && ($user->can('add dc stock') || $user->hasRole('Admin')), 403);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        $file = $request->file('file');
        $replaceExisting = $request->boolean('replace_existing', false);

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            // Tự động nhận diện dòng tiêu đề
            $headerRow = 1;
            $colMap = [];

            for ($r = 1; $r <= min($highestRow, 10); $r++) {
                $rowVals = [];
                for ($c = 1; $c <= 15; $c++) {
                    $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                    $val = mb_strtolower(trim((string)$sheet->getCell($letter . $r)->getValue()));
                    if ($val) $rowVals[$c] = $val;
                }
                $matched = 0;
                foreach ($rowVals as $c => $val) {
                    if (str_contains($val, 'mã hàng') || str_contains($val, 'color_code') || str_contains($val, 'mã màu')) $matched++;
                    if (str_contains($val, 'cao') || str_contains($val, 'height') || str_contains($val, 'dài')) $matched++;
                    if (str_contains($val, 'rộng') || str_contains($val, 'width')) $matched++;
                    if (str_contains($val, 'số lượng') || str_contains($val, 'quantity') || $val === 'sl') $matched++;
                }
                if ($matched >= 3) {
                    $headerRow = $r;
                    foreach ($rowVals as $c => $val) {
                        if (str_contains($val, 'mã hàng') || str_contains($val, 'color_code')) $colMap['color_code'] = $c;
                        elseif (str_contains($val, 'mã màu') && !isset($colMap['color_code'])) $colMap['color_code'] = $c;
                        elseif (str_contains($val, 'cao') || str_contains($val, 'height') || str_contains($val, 'dài')) $colMap['height'] = $c;
                        elseif (str_contains($val, 'rộng') || str_contains($val, 'width')) $colMap['width'] = $c;
                        elseif (str_contains($val, 'số lượng') || str_contains($val, 'quantity') || $val === 'sl') $colMap['quantity'] = $c;
                        elseif (str_contains($val, 'vị trí') || str_contains($val, 'location')) $colMap['location'] = $c;
                        elseif (str_contains($val, 'ghi chú') || str_contains($val, 'note')) $colMap['note'] = $c;
                        elseif (str_contains($val, 'trạng thái') || str_contains($val, 'status')) $colMap['status'] = $c;
                    }
                    break;
                }
            }

            if (empty($colMap)) {
                $colMap = [
                    'color_code' => 2,
                    'height' => 6,
                    'width' => 7,
                    'quantity' => 8,
                    'location' => 9,
                    'note' => 10,
                ];
                $headerRow = 4;
            }

            $userId = Auth::id();
            $importedCount = 0;

            DB::beginTransaction();

            if ($replaceExisting) {
                DcStock::truncate();
            }

            $recordsToInsert = [];

            for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
                $getVal = function ($key) use ($sheet, $row, $colMap) {
                    if (!isset($colMap[$key])) return null;
                    $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colMap[$key]);
                    return trim((string)$sheet->getCell($letter . $row)->getValue());
                };

                $colorCode = $getVal('color_code');
                $height = (int)$getVal('height');
                $width = (int)$getVal('width');
                $quantity = (int)($getVal('quantity') ?: 1);
                $location = $getVal('location');
                $note = $getVal('note');
                $status = $getVal('status') ?: 'available';

                if (empty($colorCode) || $height <= 0 || $width <= 0) {
                    continue;
                }

                if ($status === 'Còn hàng' || $status === 'con_hang') $status = 'available';
                elseif ($status === 'Đã dùng' || $status === 'da_dung') $status = 'used';
                elseif ($status === 'Đã đặt' || $status === 'da_dat') $status = 'reserved';

                $normalized = $this->resolveNormalizedStockInfo($note, $colorCode);

                $recordsToInsert[] = [
                    'color_code'         => $normalized['color_code'],
                    'board_code'         => $normalized['board_code'],
                    'board_type'         => $normalized['board_type'],
                    'wood_board_type_id' => $normalized['wood_board_type_id'],
                    'thickness'          => '17mm',
                    'note'               => $note ?: null,
                    'height'             => $height,
                    'width'              => $width,
                    'quantity'           => $quantity > 0 ? $quantity : 1,
                    'location'           => $location ?: null,
                    'status'             => in_array($status, ['available', 'used', 'reserved']) ? $status : 'available',
                    'created_by'         => $userId,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];

                if (count($recordsToInsert) >= 200) {
                    DcStock::insert($recordsToInsert);
                    $importedCount += count($recordsToInsert);
                    $recordsToInsert = [];
                }
            }

            if (!empty($recordsToInsert)) {
                DcStock::insert($recordsToInsert);
                $importedCount += count($recordsToInsert);
            }

            DB::commit();

            return back()->with('success', "Đã nhập thành công {$importedCount} tấm ván dư vào Kho DC!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi khi nhập file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Xuất danh sách tấm dư Kho DC ra file Excel
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && ($user->can('view dc stock') || $user->hasRole('Admin')), 403);

        $search = $request->input('search', '');
        $status = $request->input('status', '');

        $query = DcStock::with('creator')->orderBy('color_code')->orderBy('height', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('color_code', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $this->applyDateFilter($query, $request, 'created_at');

        $stocks = $query->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kho_DC');

        $sheet->setCellValue('A1', 'DANH SÁCH TẤM VÁN DƯ KHO DC');
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FFEA580C');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = [
            'A3' => 'STT',
            'B3' => 'MÃ VÁN / SKU',
            'C3' => 'MÃ MÀU',
            'D3' => 'LOẠI VÁN',
            'E3' => 'ĐỘ DÀY',
            'F3' => 'CAO (mm)',
            'G3' => 'RỘNG (mm)',
            'H3' => 'DIỆN TÍCH (m²)',
            'I3' => 'SỐ LƯỢNG',
            'J3' => 'VỊ TRÍ',
            'K3' => 'TRẠNG THÁI',
            'L3' => 'GHI CHÚ'
        ];
        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }
        $sheet->getStyle('A3:L3')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A3:L3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF97316');
        $sheet->getStyle('A3:L3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $r = 4;
        $stt = 1;
        foreach ($stocks as $s) {
            $areaM2 = round(($s->height * $s->width) / 1000000, 3);
            $sheet->setCellValue("A{$r}", $stt++);
            $sheet->setCellValue("B{$r}", $s->board_code ?: $s->color_code);
            $sheet->setCellValue("C{$r}", $s->color_code);
            $sheet->setCellValue("D{$r}", $s->board_type ?: ($s->boardTypeRel?->name ?: '—'));
            $sheet->setCellValue("E{$r}", $s->thickness ?: '17mm');
            $sheet->setCellValue("F{$r}", $s->height);
            $sheet->setCellValue("G{$r}", $s->width);
            $sheet->setCellValue("H{$r}", $areaM2);
            $sheet->setCellValue("I{$r}", $s->quantity);
            $sheet->setCellValue("J{$r}", $s->location);
            $sheet->setCellValue("K{$r}", $s->status_label);
            $sheet->setCellValue("L{$r}", $s->note);
            $r++;
        }

        $lastRow = $r - 1;
        if ($lastRow >= 4) {
            $sheet->getStyle("A3:L{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("F4:G{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("H4:H{$lastRow}")->getNumberFormat()->setFormatCode('0.000');
            $sheet->getStyle("I4:I{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Kho_DC_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Xác định thông tin Loại ván, Mã SKU chuẩn form dựa theo Ghi chú và Mã màu
     */
    private function resolveNormalizedStockInfo(?string $note, ?string $rawColor, ?string $rawBoardCode = null): array
    {
        $note = mb_strtolower(trim((string)$note));
        $raw = $rawColor ?: $rawBoardCode ?: '';
        $c = trim($raw);
        $c = preg_replace('/\.TP\.(PVC|2M)(\.2M)?$/i', '', $c);
        $c = preg_replace('/\s*[-_]?\s*(2M|1M|2\s*mặt|1\s*mặt|PVC)$/iu', '', $c);
        $cleanColor = mb_strtoupper(trim($c));
        if (empty($cleanColor)) {
            $cleanColor = mb_strtoupper(trim($raw));
        }

        $typeMdf1 = WoodBoardType::where('name', 'like', '%MDF 1 MẶT%')->first() ?? WoodBoardType::find(1);
        $typeMdf2 = WoodBoardType::where('name', 'like', '%MDF 2 MẶT%')->first() ?? WoodBoardType::find(2);
        $typePvc1 = WoodBoardType::where('name', 'like', '%NHỰA 1 MẶT%')->orWhere('name', 'like', '%PVC 1%')->first() ?? WoodBoardType::find(3);
        $typePvc2 = WoodBoardType::where('name', 'like', '%NHỰA 2 MẶT%')->orWhere('name', 'like', '%PVC 2%')->first();
        if (!$typePvc2) {
            $typePvc2 = WoodBoardType::firstOrCreate([
                'name' => 'CỐT NHỰA 2 MẶT ACRYLIC'
            ], [
                'prefix' => '.TP.PVC.2M',
                'display_order' => 4,
            ]);
        }

        if ((str_contains($note, 'pvc') || str_contains($note, 'nhựa')) && (str_contains($note, '2 mặt') || str_contains($note, '2m'))) {
            $boardType = 'CỐT NHỰA 2 MẶT ACRYLIC';
            $typeId = $typePvc2 ? $typePvc2->id : 4;
            $boardCode = $cleanColor . '.TP.PVC.2M';
        } elseif (str_contains($note, 'pvc') || str_contains($note, 'nhựa')) {
            $boardType = 'CỐT NHỰA 1 MẶT ACRYLIC';
            $typeId = $typePvc1 ? $typePvc1->id : 3;
            $boardCode = $cleanColor . '.TP.PVC';
        } elseif (str_contains($note, '2 mặt') || str_contains($note, '2m') || str_contains($note, '2 măt') || str_contains($note, '2 mătt')) {
            $boardType = 'MDF 2 MẶT ACRYLIC';
            $typeId = $typeMdf2 ? $typeMdf2->id : 2;
            $boardCode = $cleanColor . '.TP.2M';
        } elseif (str_contains($note, '1 mặt') || str_contains($note, '1m') || str_contains($note, 'mdf')) {
            $boardType = 'MDF 1 MẶT ACRYLIC';
            $typeId = $typeMdf1 ? $typeMdf1->id : 1;
            $boardCode = $cleanColor;
        } else {
            if (str_contains($cleanColor, 'PVC') || str_contains((string)$rawBoardCode, 'PVC')) {
                $boardType = 'CỐT NHỰA 1 MẶT ACRYLIC';
                $typeId = $typePvc1 ? $typePvc1->id : 3;
                $boardCode = $cleanColor . '.TP.PVC';
            } else {
                $boardType = 'MDF 1 MẶT ACRYLIC';
                $typeId = $typeMdf1 ? $typeMdf1->id : 1;
                $boardCode = $cleanColor;
            }
        }

        return [
            'color_code'         => $cleanColor,
            'board_code'         => $boardCode,
            'board_type'         => $boardType,
            'wood_board_type_id' => $typeId,
        ];
    }

    /**
     * Tự động chuẩn hóa Loại ván và Mã SKU theo Ghi chú
     */
    public function normalizeTypes(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && ($user->can('add dc stock') || $user->hasRole('Admin')), 403);

        $stocks = DcStock::all();
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($stocks as $stock) {
                $normalized = $this->resolveNormalizedStockInfo($stock->note, $stock->color_code, $stock->board_code);

                $stock->color_code         = $normalized['color_code'];
                $stock->board_code         = $normalized['board_code'];
                $stock->board_type         = $normalized['board_type'];
                $stock->wood_board_type_id = $normalized['wood_board_type_id'];
                if (empty($stock->thickness)) {
                    $stock->thickness = '17mm';
                }
                $stock->save();
                $updatedCount++;
            }

            DB::commit();

            return response()->json([
                'success'       => true,
                'message'       => "Đã chuyển đổi và chuẩn hóa thành công {$updatedCount} tấm ván trong Kho DC!",
                'updated_count' => $updatedCount
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi chuẩn hóa: ' . $e->getMessage()
            ], 500);
        }
    }
}
