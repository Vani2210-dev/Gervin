<?php

namespace App\Http\Controllers;

use App\Models\PressingOrder;
use App\Models\PressingOrderItem;
use App\Models\WoodBoard;
use App\Models\WoodBoardPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PressingOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Danh sách lệnh ép ván
     */
    public function index(Request $request)
    {
        $query = PressingOrder::with(['items', 'creator'])->latest('id');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('code', 'like', "%{$s}%")
                  ->orWhere('material_name', 'like', "%{$s}%")
                  ->orWhere('notes', 'like', "%{$s}%");
            });
        }

        if ($request->filled('purpose')) {
            $query->where('purpose', $request->purpose);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(15);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        }

        return response()->json($orders);
    }

    /**
     * Tạo lệnh ép ván mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_name' => 'required|string|max:255',
            'core_material' => 'nullable|string|max:255',
            'thickness'     => 'nullable|string|max:50',
            'dimensions'    => 'nullable|string|max:100',
            'qty_needed'    => 'required|integer|min:1|max:500',
            'purpose'       => 'nullable|string|in:stock,order',
            'notes'         => 'nullable|string',
            'wood_board_id' => 'nullable|integer|exists:wood_boards,id',
        ]);

        $purpose = $validated['purpose'] ?? 'stock';
        $dimensions = $validated['dimensions'] ?? '1220 x 2440';
        $qty = intval($validated['qty_needed']);

        $pressingOrder = DB::transaction(function () use ($validated, $purpose, $dimensions, $qty) {
            $code = PressingOrder::generateCode();

            $order = PressingOrder::create([
                'code'          => $code,
                'purpose'       => $purpose,
                'material_name' => trim($validated['material_name']),
                'core_material' => $validated['core_material'] ?? null,
                'thickness'     => $validated['thickness'] ?? null,
                'dimensions'    => $dimensions,
                'qty_needed'    => $qty,
                'qty_done'      => 0,
                'status'        => 'processing',
                'notes'         => $validated['notes'] ?? null,
                'wood_board_id' => $validated['wood_board_id'] ?? null,
                'created_by'    => Auth::id(),
            ]);

            // Sinh từng tấm con kèm mã định danh QR
            $operatorName = Auth::user()->name ?? 'Hệ thống';
            $nowStr = now()->toDateTimeString();

            for ($i = 1; $i <= $qty; $i++) {
                $pieceSeq = str_pad($i, 2, '0', STR_PAD_LEFT);
                $pieceCode = "{$code}-{$pieceSeq}";

                PressingOrderItem::create([
                    'pressing_order_id' => $order->id,
                    'piece_code'        => $pieceCode,
                    'item_index'        => $i,
                    'status'            => 'pending',
                    'status_logs'       => [
                        [
                            'action'      => 'làm lệnh ép',
                            'operator'    => $operatorName,
                            'operator_id' => Auth::id(),
                            'time'        => $nowStr,
                            'notes'       => $order->notes ?? 'Thiết lập lệnh ép',
                        ]
                    ],
                ]);
            }

            return $order->load('items');
        });

        return response()->json([
            'success' => true,
            'message' => "Đã tạo thành công lệnh ép {$pressingOrder->code} với {$qty} tấm ván!",
            'data'    => $pressingOrder
        ]);
    }

    /**
     * Chi tiết một lệnh ép
     */
    public function show(PressingOrder $pressingOrder)
    {
        $pressingOrder->load(['items', 'creator']);

        return response()->json([
            'success' => true,
            'data'    => $pressingOrder
        ]);
    }

    /**
     * Giao diện in tem QR dán lên tấm ván
     */
    public function printLabels(PressingOrder $pressingOrder, Request $request)
    {
        $pressingOrder->load('items');

        $singleItemId = $request->query('item_id');
        $items = $pressingOrder->items;

        if ($singleItemId) {
            $items = $items->where('id', $singleItemId);
        }

        // Sinh SVG QR cho từng tấm ván
        $labels = [];
        foreach ($items as $item) {
            $qrSvg = QrCode::format('svg')
                ->size(130)
                ->margin(1)
                ->errorCorrection('M')
                ->generate($item->piece_code);

            // Bỏ header XML để nhúng trực tiếp SVG
            $qrSvg = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $qrSvg);

            $labels[] = [
                'item'          => $item,
                'piece_code'    => $item->piece_code,
                'order_code'    => $pressingOrder->code,
                'material_name' => $pressingOrder->material_name,
                'core_material' => $pressingOrder->core_material ?? 'MDF Thái xanh chống ẩm',
                'thickness'     => $pressingOrder->thickness ?? '17mm',
                'dimensions'    => $pressingOrder->dimensions ?: '1220 x 2440 mm',
                'purpose_label' => $pressingOrder->purpose === 'stock' ? 'ÉP DỰ TRỮ KHO' : 'ÉP THEO ĐƠN HÀNG',
                'notes'         => $pressingOrder->notes ?: 'Dán keo PUR nhiệt cao',
                'date'          => $pressingOrder->created_at ? $pressingOrder->created_at->format('d/m/Y') : date('d/m/Y'),
                'qr_svg'        => $qrSvg,
            ];
        }

        return view('processes.pressing_labels', compact('pressingOrder', 'labels'));
    }

    /**
     * Hủy/Xóa lệnh ép
     */
    public function destroy(PressingOrder $pressingOrder)
    {
        $code = $pressingOrder->code;
        $pressingOrder->delete();

        return response()->json([
            'success' => true,
            'message' => "Đã xóa lệnh ép {$code}."
        ]);
    }
}
