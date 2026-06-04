<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcrylicOrderItem;
use App\Models\GlassOrderItem;
use App\Models\MinLateOrderItem;
use Illuminate\Support\Facades\Auth;

class ManufactureStepController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function cnc()
    {
        $acrylicItems = AcrylicOrderItem::whereJsonContains('status', ['action' => 'hoàn thành cnc'])->get();
        $glassItems = GlassOrderItem::whereJsonContains('status', ['action' => 'hoàn thành cnc'])->get();
        $minLateItems = MinLateOrderItem::whereJsonContains('status', ['action' => 'hoàn thành cnc'])->get();

        $history = collect();

        foreach ($acrylicItems as $item) {
            $statusLogs = $item->status ?? [];
            foreach ($statusLogs as $log) {
                if (isset($log['action']) && $log['action'] === 'hoàn thành cnc') {
                    $history->push((object)[
                        'product_code' => $item->product_code,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'acrylic',
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $item->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'item_id' => $item->id
                    ]);
                }
            }
        }

        foreach ($glassItems as $item) {
            $statusLogs = $item->status ?? [];
            foreach ($statusLogs as $log) {
                if (isset($log['action']) && $log['action'] === 'hoàn thành cnc') {
                    $history->push((object)[
                        'product_code' => $item->product_code,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'glass',
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $item->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'item_id' => $item->id
                    ]);
                }
            }
        }

        foreach ($minLateItems as $item) {
            $statusLogs = $item->status ?? [];
            foreach ($statusLogs as $log) {
                if (isset($log['action']) && $log['action'] === 'hoàn thành cnc') {
                    $history->push((object)[
                        'product_code' => $item->product_code,
                        'product_name' => $item->product_name ?? $item->name ?? '—',
                        'type' => 'min_late',
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $item->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'item_id' => $item->id
                    ]);
                }
            }
        }

        // Sort history by time descending
        $history = $history->sortByDesc('time')->values();

        return view('processes.cnc', compact('history'));
    }

    public function completeCnc(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $code = trim($request->product_code);
        $notes = $request->notes;

        // Search in Acrylic
        $item = AcrylicOrderItem::where('product_code', $code)->first();
        $type = 'acrylic';
        $nameField = 'product_name';

        if (!$item) {
            // Search in Glass
            $item = GlassOrderItem::where('product_code', $code)->first();
            $type = 'glass';
            $nameField = 'product_name';
        }

        if (!$item) {
            // Search in Min Late
            $item = MinLateOrderItem::where('product_code', $code)->first();
            $type = 'min_late';
            $nameField = 'name';
        }

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm với mã định danh: ' . $code
            ], 404);
        }

        $currentStatus = $item->status ?? [];
        $logTime = now()->toDateTimeString();
        $operatorName = Auth::user()->name ?? 'Hệ thống';

        $newLog = [
            'action' => 'hoàn thành cnc',
            'operator' => $operatorName,
            'operator_id' => Auth::id(),
            'time' => $logTime,
            'notes' => $notes,
        ];

        $currentStatus[] = $newLog;

        // Save
        $item->notes = $notes;
        $item->status = $currentStatus;
        $item->save();

        $productName = $item->$nameField ?? '—';

        return response()->json([
            'success' => true,
            'message' => 'Xác nhận hoàn thành công đoạn cắt CNC thành công.',
            'data' => [
                'product_code' => $item->product_code,
                'product_name' => $productName,
                'type' => $type,
                'operator' => $operatorName,
                'time' => $logTime,
                'notes' => $notes ?? 'Hoàn thành CNC',
            ]
        ]);
    }

    public function pressing()
    {
        return view('processes.pressing');
    }

    public function edgeBanding()
    {
        return view('processes.edge_banding');
    }

    public function finishing()
    {
        return view('processes.finishing');
    }

    public function qc()
    {
        return view('processes.qc');
    }

    public function packing()
    {
        return view('processes.packing');
    }

    public function shipped()
    {
        return view('processes.shipped');
    }
}
