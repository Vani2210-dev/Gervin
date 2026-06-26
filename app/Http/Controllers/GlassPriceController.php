<?php

namespace App\Http\Controllers;

use App\Models\GlassPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlassPriceController extends Controller
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
        $categories = GlassPrice::select('category_name')
            ->whereNotNull('category_name')
            ->groupBy('category_name')
            ->pluck('category_name');

        $glassPrices = GlassPrice::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($subQ) use ($search) {
                    $subQ->where('product_name', 'like', "%$search%")
                         ->orWhere('code', 'like', "%$search%")
                         ->orWhere('aluminum_color', 'like', "%$search%")
                         ->orWhere('glass_color', 'like', "%$search%");
                });
            })
            ->when($filterCategory, function ($q) use ($filterCategory) {
                $q->where('category_name', $filterCategory);
            })
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return view('glass_prices.index', compact('glassPrices', 'categories', 'perPage', 'search', 'filterCategory'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_name'  => 'nullable|string|max:255',
            'stt'            => 'nullable|string|max:50',
            'product_name'   => 'required|string',
            'aluminum_color' => 'nullable|string|max:255',
            'glass_color'    => 'nullable|string|max:255',
            'unit'           => 'nullable|string|max:50',
            'price'          => 'required|numeric|min:0',
            'code'           => 'nullable|string|max:100|unique:glass_prices,code',
            'notes'          => 'nullable|string',
        ]);

        GlassPrice::create([
            'category_name'  => $request->category_name,
            'stt'            => $request->stt,
            'product_name'   => $request->product_name,
            'aluminum_color' => $request->aluminum_color,
            'glass_color'    => $request->glass_color,
            'unit'           => $request->unit,
            'price'          => floatval($request->price),
            'code'           => $request->code ?: null,
            'notes'          => $request->notes,
        ]);

        return redirect()->route('glass_prices.index')->with('success', 'Thêm dòng bảng giá kính thành công.');
    }

    public function update(Request $request, GlassPrice $glassPrice)
    {
        $request->validate([
            'category_name'  => 'nullable|string|max:255',
            'stt'            => 'nullable|string|max:50',
            'product_name'   => 'required|string',
            'aluminum_color' => 'nullable|string|max:255',
            'glass_color'    => 'nullable|string|max:255',
            'unit'           => 'nullable|string|max:50',
            'price'          => 'required|numeric|min:0',
            'code'           => 'nullable|string|max:100|unique:glass_prices,code,' . $glassPrice->id,
            'notes'          => 'nullable|string',
        ]);

        $glassPrice->update([
            'category_name'  => $request->category_name,
            'stt'            => $request->stt,
            'product_name'   => $request->product_name,
            'aluminum_color' => $request->aluminum_color,
            'glass_color'    => $request->glass_color,
            'unit'           => $request->unit,
            'price'          => floatval($request->price),
            'code'           => $request->code ?: null,
            'notes'          => $request->notes,
        ]);

        return redirect()->route('glass_prices.index')->with('success', 'Cập nhật dòng bảng giá kính thành công.');
    }

    public function destroy(GlassPrice $glassPrice)
    {
        $glassPrice->delete();
        return redirect()->route('glass_prices.index')->with('success', 'Xóa dòng bảng giá kính thành công.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (count($rows) < 2) {
                return back()->with('error', 'File Excel không đúng cấu trúc hoặc không có dữ liệu.');
            }

            DB::transaction(function () use ($rows) {
                // Delete existing glass prices
                GlassPrice::query()->delete();

                $currentCategory = null;
                $currentProductName = null;
                $currentAluminumColor = null;
                $currentUnit = null;
                $currentNotes = null;

                for ($i = 1; $i < count($rows); $i++) {
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

                    $stt            = isset($row[0]) ? trim((string)$row[0]) : '';
                    $productName    = isset($row[1]) ? trim((string)$row[1]) : '';
                    $aluminumColor  = isset($row[2]) ? trim((string)$row[2]) : '';
                    $glassColor     = isset($row[3]) ? trim((string)$row[3]) : '';
                    $unit           = isset($row[4]) ? trim((string)$row[4]) : '';
                    $priceVal       = isset($row[5]) ? $row[5] : null;
                    $code           = isset($row[6]) ? trim((string)$row[6]) : '';
                    $notes          = isset($row[7]) ? trim((string)$row[7]) : '';

                    // 1. Category Header Row
                    if ($this->isRomanNumeral($stt) && (is_null($priceVal) || $priceVal === '')) {
                        $currentCategory = !empty($productName) ? $productName : $stt;
                        $currentProductName = null;
                        $currentAluminumColor = null;
                        $currentUnit = null;
                        $currentNotes = null;
                        continue;
                    }

                    // Reset product-specific fields on a new product group row
                    if (preg_match('/^\d+$/', $stt)) {
                        $currentProductName = null;
                        $currentAluminumColor = null;
                        $currentUnit = null;
                        $currentNotes = null;

                        if (intval($stt) >= 8) {
                            $currentCategory = 'PHỤ KIỆN & PHỤ PHÍ';
                        }
                    }

                    // 2. Data Row
                    if (!empty($productName)) {
                        $currentProductName = $productName;
                    }
                    if (!empty($aluminumColor)) {
                        $currentAluminumColor = $aluminumColor;
                    }
                    if (!empty($unit)) {
                        $currentUnit = $unit;
                    }
                    if (!empty($notes)) {
                        $currentNotes = $notes;
                    }

                    $price = $this->cleanPrice($priceVal);

                    if (!empty($code) || !is_null($priceVal)) {
                        if ($code === 'Mã TP') {
                            continue;
                        }

                        GlassPrice::create([
                            'category_name'  => $currentCategory,
                            'stt'            => $stt,
                            'product_name'   => $currentProductName,
                            'aluminum_color' => $currentAluminumColor,
                            'glass_color'    => $glassColor,
                            'unit'           => $currentUnit,
                            'price'          => $price,
                            'code'           => !empty($code) ? $code : null,
                            'notes'          => !empty($notes) ? $notes : null,
                        ]);
                    }
                }
            });

            return back()->with('success', 'Nhập bảng giá cánh kính thành công!');
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

    private function cleanPrice($val): float
    {
        if (is_null($val) || $val === '') {
            return 0.0;
        }
        if (is_numeric($val)) {
            return floatval($val);
        }
        $cleaned = preg_replace('/[^0-9]/', '', $val);
        return floatval($cleaned ?: 0);
    }
}
