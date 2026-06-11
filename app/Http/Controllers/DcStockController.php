<?php

namespace App\Http\Controllers;

use App\Models\DcStock;
use App\Models\WoodBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DcStockController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('view dc stock'), 403);

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
}
