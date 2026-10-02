<?php

namespace App\Http\Controllers;

use App\Models\DcStock;
use App\Models\WoodBoard;
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
        $perPage   = (int) $request->input('per_page', 25);

        $query = DcStock::with('creator')
            ->orderBy('created_at', 'desc');

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

        $stocks = $query->paginate($perPage)->withQueryString();

        // Lấy danh sách mã màu để gợi ý khi nhập
        $colorCodes = WoodBoard::select('color_code')->distinct()->orderBy('color_code')->pluck('color_code');

        // Thống kê nhanh
        $totalAvailable = DcStock::where('status', 'available')->count();
        $totalUsed      = DcStock::where('status', 'used')->count();
        $totalReserved  = DcStock::where('status', 'reserved')->count();

        return view('dc_stocks.index', compact(
            'stocks', 'colorCodes', 'search', 'status', 'perPage',
            'totalAvailable', 'totalUsed', 'totalReserved'
        ));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('add dc stock'), 403);

        $data = $request->validate([
            'color_code' => 'required|string|max:50',
            'note'       => 'nullable|string|max:100',
            'height'     => 'required|integer|min:1',
            'width'      => 'required|integer|min:1',
            'quantity'   => 'required|integer|min:1',
            'location'   => 'nullable|string|max:50',
            'status'     => 'required|in:available,used,reserved',
        ]);

        $data['created_by'] = Auth::id();

        DcStock::create($data);

        return response()->json(['success' => true, 'message' => 'Đã thêm tấm dư vào Kho DC thành công!']);
    }

    public function update(Request $request, DcStock $dcStock)
    {
        abort_unless(auth()->user()->can('edit dc stock'), 403);

        $data = $request->validate([
            'color_code' => 'required|string|max:50',
            'note'       => 'nullable|string|max:100',
            'height'     => 'required|integer|min:1',
            'width'      => 'required|integer|min:1',
            'quantity'   => 'required|integer|min:1',
            'location'   => 'nullable|string|max:50',
            'status'     => 'required|in:available,used,reserved',
        ]);

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

                $recordsToInsert[] = [
                    'color_code' => mb_strtoupper($colorCode),
                    'note' => $note ?: null,
                    'height' => $height,
                    'width' => $width,
                    'quantity' => $quantity > 0 ? $quantity : 1,
                    'location' => $location ?: null,
                    'status' => in_array($status, ['available', 'used', 'reserved']) ? $status : 'available',
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
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
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FFEA580C');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = [
            'A3' => 'STT',
            'B3' => 'MÃ HÀNG',
            'C3' => 'GHI CHÚ',
            'D3' => 'CAO (mm)',
            'E3' => 'RỘNG (mm)',
            'F3' => 'SỐ LƯỢNG',
            'G3' => 'VỊ TRÍ',
            'H3' => 'TRẠNG THÁI'
        ];
        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }
        $sheet->getStyle('A3:H3')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A3:H3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF97316');
        $sheet->getStyle('A3:H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $r = 4;
        $stt = 1;
        foreach ($stocks as $s) {
            $sheet->setCellValue("A{$r}", $stt++);
            $sheet->setCellValue("B{$r}", $s->color_code);
            $sheet->setCellValue("C{$r}", $s->note);
            $sheet->setCellValue("D{$r}", $s->height);
            $sheet->setCellValue("E{$r}", $s->width);
            $sheet->setCellValue("F{$r}", $s->quantity);
            $sheet->setCellValue("G{$r}", $s->location);
            $sheet->setCellValue("H{$r}", $s->status_label);
            $r++;
        }

        $lastRow = $r - 1;
        if ($lastRow >= 4) {
            $sheet->getStyle("A3:H{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("D4:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        }

        foreach (range('A', 'H') as $col) {
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
}
