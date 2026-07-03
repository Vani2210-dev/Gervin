<?php

namespace App\Http\Controllers;

use App\Models\MinLatePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MinLatePriceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view supply',   ['only' => ['index']]);
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

                $currentCategory = null;

                // Data starts at Row 4 (index 3)
                for ($i = 3; $i < count($rows); $i++) {
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

                    $stt         = isset($row[0]) ? trim((string)$row[0]) : '';
                    $productName = isset($row[1]) ? trim((string)$row[1]) : '';
                    $unit        = isset($row[2]) ? trim((string)$row[2]) : '';
                    $priceVal    = isset($row[3]) ? $row[3] : null;
                    $notes       = isset($row[4]) ? trim((string)$row[4]) : '';

                    // 1. Check if Category Header Row
                    if ($this->isRomanNumeral($stt) && (is_null($priceVal) || trim((string)$priceVal) === '')) {
                        $currentCategory = !empty($productName) ? $productName : $stt;
                        continue;
                    }

                    // Skip note rows that put long text in the STT column
                    if (mb_strlen($stt) > 10) {
                        continue;
                    }

                    // 2. Ignore non-data bottom note rows
                    if (empty($stt) && empty($unit) && (is_null($priceVal) || trim((string)$priceVal) === '')) {
                        continue;
                    }

                    // 3. Save standard data row
                    $cleanNotes = $notes;
                    $price = $this->cleanPrice($priceVal, $cleanNotes);

                    MinLatePrice::create([
                        'category_name' => $currentCategory,
                        'stt'           => $stt,
                        'product_name'  => $productName,
                        'unit'          => $unit,
                        'price'         => $price,
                        'code'          => null,
                        'notes'         => !empty($cleanNotes) ? $cleanNotes : null,
                    ]);
                }
            });

            return back()->with('success', 'Nhập bảng giá dịch vụ Min-late thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Đã xảy ra lỗi khi nhập file: ' . $e->getMessage());
        }
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
