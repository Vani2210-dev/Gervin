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
            ->when($request->filled('filter_name'), function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->filter_name}%");
            })
            ->when($request->filled('filter_category'), function ($q) use ($request) {
                $q->where('category', 'like', "%{$request->filter_category}%");
            })
            ->when($request->filled('filter_unit'), function ($q) use ($request) {
                $q->where('unit', 'like', "%{$request->filter_unit}%");
            })
            ->when($request->filled('filter_min_stock'), function ($q) use ($request) {
                $q->where('stock_quantity', '>=', $request->filter_min_stock);
            })
            ->when($request->filled('filter_max_stock'), function ($q) use ($request) {
                $q->where('stock_quantity', '<=', $request->filter_max_stock);
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
            'name'           => 'required|string|max:255',
            'category'       => 'nullable|string|max:255',
            'unit'           => 'nullable|string|max:100',
            'stock_quantity' => 'nullable|numeric|min:0',
            'min_stock'      => 'nullable|numeric|min:0',
            'unit_price'     => 'nullable|numeric|min:0',
        ]);

        Supply::create([
            'name'           => $request->name,
            'category'       => $request->category,
            'unit'           => $request->unit,
            'stock_quantity' => $request->stock_quantity ?? 0,
            'min_stock'      => $request->min_stock ?? 0,
            'unit_price'     => $request->unit_price ?? 0,
        ]);

        return redirect()->route('supplies.index')->with('success', 'Thêm vật tư thành công.');
    }

    public function update(Request $request, Supply $supply)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'category'       => 'nullable|string|max:255',
            'unit'           => 'nullable|string|max:100',
            'stock_quantity' => 'nullable|numeric|min:0',
            'min_stock'      => 'nullable|numeric|min:0',
            'unit_price'     => 'nullable|numeric|min:0',
        ]);

        $supply->update([
            'name'           => $request->name,
            'category'       => $request->category,
            'unit'           => $request->unit,
            'stock_quantity' => $request->stock_quantity ?? 0,
            'min_stock'      => $request->min_stock ?? 0,
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
