<?php

namespace App\Http\Controllers;

use App\Models\Supply;
use Illuminate\Http\Request;

class SupplyController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view supply',   ['only' => ['index']]);
        $this->middleware('permission:add supply',    ['only' => ['store']]);
        $this->middleware('permission:edit supply',   ['only' => ['update']]);
        $this->middleware('permission:delete supply', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search', '');

        $supplies = Supply::when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('category', 'like', "%$search%");
            })
            ->when($request->filled('filter_product_code'), function ($q) use ($request) {
                $q->where('product_code', 'like', "%{$request->filter_product_code}%");
            })
            ->when($request->filled('filter_name'), function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->filter_name}%");
            })
            ->when($request->filled('filter_category'), function ($q) use ($request) {
                $q->where('category', 'like', "%{$request->filter_category}%");
            })
            ->when($request->filled('filter_unit'), function ($q) use ($request) {
                $q->where('unit', 'like', "%{$request->filter_unit}%");
            })
            ->when($request->filled('filter_grain_direction'), function ($q) use ($request) {
                $q->where('grain_direction', $request->filter_grain_direction);
            })
            ->when($request->filled('filter_min_width'), function ($q) use ($request) {
                $q->where('width_mm', '>=', $request->filter_min_width);
            })
            ->when($request->filled('filter_max_width'), function ($q) use ($request) {
                $q->where('width_mm', '<=', $request->filter_max_width);
            })
            ->when($request->filled('filter_min_height'), function ($q) use ($request) {
                $q->where('height_mm', '>=', $request->filter_min_height);
            })
            ->when($request->filled('filter_max_height'), function ($q) use ($request) {
                $q->where('height_mm', '<=', $request->filter_max_height);
            })
            ->when($request->filled('filter_min_price'), function ($q) use ($request) {
                $q->where('unit_price', '>=', $request->filter_min_price);
            })
            ->when($request->filled('filter_max_price'), function ($q) use ($request) {
                $q->where('unit_price', '<=', $request->filter_max_price);
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('supplies.index', compact('supplies', 'perPage', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_code'   => 'nullable|string|max:50|unique:supplies,product_code',
            'name'           => 'required|string|max:255',
            'category'       => 'nullable|string|max:255',
            'unit'           => 'nullable|string|max:100',
            'grain_direction'=> 'nullable|in:0,2',
            'width_mm'       => 'nullable|integer|min:0',
            'height_mm'      => 'nullable|integer|min:0',
            'unit_price'     => 'nullable|numeric|min:0',
        ]);

        Supply::create([
            'product_code'   => $request->product_code,
            'name'           => $request->name,
            'category'       => $request->category,
            'unit'           => $request->unit,
            'grain_direction'=> $request->grain_direction ?? 0,
            'width_mm'       => $request->width_mm,
            'height_mm'      => $request->height_mm,
            'unit_price'     => $request->unit_price ?? 0,
        ]);

        return redirect()->route('supplies.index')->with('success', 'Thêm vật tư thành công.');
    }

    public function update(Request $request, Supply $supply)
    {
        $request->validate([
            'product_code'   => 'nullable|string|max:50|unique:supplies,product_code,' . $supply->id,
            'name'           => 'required|string|max:255',
            'category'       => 'nullable|string|max:255',
            'unit'           => 'nullable|string|max:100',
            'grain_direction'=> 'nullable|in:0,2',
            'width_mm'       => 'nullable|integer|min:0',
            'height_mm'      => 'nullable|integer|min:0',
            'unit_price'     => 'nullable|numeric|min:0',
        ]);

        $supply->update([
            'product_code'   => $request->product_code,
            'name'           => $request->name,
            'category'       => $request->category,
            'unit'           => $request->unit,
            'grain_direction'=> $request->grain_direction ?? 0,
            'width_mm'       => $request->width_mm,
            'height_mm'      => $request->height_mm,
            'unit_price'     => $request->unit_price ?? 0,
        ]);

        return redirect()->route('supplies.index')->with('success', 'Cập nhật vật tư thành công.');
    }

    public function destroy(Supply $supply)
    {
        $supply->delete();
        return redirect()->route('supplies.index')->with('success', 'Xóa vật tư thành công.');
    }
}
