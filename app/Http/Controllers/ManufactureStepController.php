<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcrylicOrderItem;
use App\Models\GlassOrderItem;
use App\Models\MinLateOrderItem;
use App\Models\AcrylicOrderItemCode;
use App\Models\GlassOrderItemCode;
use App\Models\MinLateOrderItemCode;
use Illuminate\Support\Facades\Auth;

class ManufactureStepController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function cnc()
    {
        $acrylicCodes = AcrylicOrderItemCode::whereNotNull('status')
            ->with('acrylicOrderItem')
            ->get();
        $glassCodes = GlassOrderItemCode::whereNotNull('status')
            ->with('glassOrderItem')
            ->get();
        $minLateCodes = MinLateOrderItemCode::whereNotNull('status')
            ->with('minLateOrderItem')
            ->get();

        $history = collect();

        foreach ($acrylicCodes as $code) {
            $item = $code->acrylicOrderItem;
            if (!$item) continue;
            $statusLogs = $code->status ?? [];
            
            foreach ($statusLogs as $log) {
                if (isset($log['action']) && in_array($log['action'], ['hoàn thành cnc', 'quay lại cnc'])) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'acrylic',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'cnc_machine' => $log['cnc_machine'] ?? '—',
                        'item_id' => $item->id
                    ]);
                }
            }
        }

        foreach ($glassCodes as $code) {
            $item = $code->glassOrderItem;
            if (!$item) continue;
            $statusLogs = $code->status ?? [];
            
            foreach ($statusLogs as $log) {
                if (isset($log['action']) && in_array($log['action'], ['hoàn thành cnc', 'quay lại cnc'])) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'glass',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'cnc_machine' => $log['cnc_machine'] ?? '—',
                        'item_id' => $item->id
                    ]);
                }
            }
        }

        foreach ($minLateCodes as $code) {
            $item = $code->minLateOrderItem;
            if (!$item) continue;
            $statusLogs = $code->status ?? [];
            
            foreach ($statusLogs as $log) {
                if (isset($log['action']) && in_array($log['action'], ['hoàn thành cnc', 'quay lại cnc'])) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? $item->name ?? '—',
                        'type' => 'min_late',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'cnc_machine' => $log['cnc_machine'] ?? '—',
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
            'action_type' => 'nullable|string|in:complete,rollback',
            'cnc_machine' => 'nullable|string',
        ]);

        $codeStr = trim($request->product_code);
        $notes = $request->notes;
        $actionType = $request->input('action_type', 'complete');
        $cncMachine = $request->input('cnc_machine');

        // Search in Acrylic
        $codeRecord = AcrylicOrderItemCode::where('product_id', $codeStr)->first();
        $type = 'acrylic';
        $nameField = 'product_name';
        $item = null;

        if ($codeRecord) {
            $item = $codeRecord->acrylicOrderItem;
        } else {
            // Search in Glass
            $codeRecord = GlassOrderItemCode::where('product_id', $codeStr)->first();
            $type = 'glass';
            $nameField = 'product_name';
            if ($codeRecord) {
                $item = $codeRecord->glassOrderItem;
            }
        }

        if (!$codeRecord) {
            // Search in Min Late
            $codeRecord = MinLateOrderItemCode::where('product_id', $codeStr)->first();
            $type = 'min_late';
            $nameField = 'name';
            if ($codeRecord) {
                $item = $codeRecord->minLateOrderItem;
            }
        }

        if (!$codeRecord || !$item) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm với mã định danh: ' . $codeStr
            ], 404);
        }

        $currentStatus = $codeRecord->status ?? [];
        $logTime = now()->toDateTimeString();
        $operatorName = Auth::user()->name ?? 'Hệ thống';

        // 1. Find the latest CNC stage log (either 'hoàn thành cnc' or 'quay lại cnc')
        $latestCncLog = null;
        for ($i = count($currentStatus) - 1; $i >= 0; $i--) {
            $log = $currentStatus[$i];
            if (isset($log['action']) && in_array($log['action'], ['hoàn thành cnc', 'quay lại cnc'])) {
                $latestCncLog = $log;
                break;
            }
        }

        // 2. Find the index of the latest 'hoàn thành cnc' log (for truncation on rollback)
        $lastCompletedCncIndex = -1;
        for ($i = count($currentStatus) - 1; $i >= 0; $i--) {
            $log = $currentStatus[$i];
            if (isset($log['action']) && $log['action'] === 'hoàn thành cnc') {
                $lastCompletedCncIndex = $i;
                break;
            }
        }

        if ($actionType === 'rollback') {
            if (!$latestCncLog || $latestCncLog['action'] !== 'hoàn thành cnc' || $lastCompletedCncIndex === -1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm này chưa ghi nhận hoàn thành CNC nên không thể quay lại.'
                ], 400);
            }

            // Slice the status array to remove the CNC action and all logs after it
            $currentStatus = array_slice($currentStatus, 0, $lastCompletedCncIndex);

            // Record new rollback log
            $newLog = [
                'action' => 'quay lại cnc',
                'operator' => $operatorName,
                'operator_id' => Auth::id(),
                'time' => $logTime,
                'notes' => $notes,
                'cnc_machine' => $cncMachine,
            ];

            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            $message = 'Đã hoàn tác (quay lại) công đoạn cắt CNC cho sản phẩm này.';
        } else {
            if ($latestCncLog && $latestCncLog['action'] === 'hoàn thành cnc') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm này đã được ghi nhận hoàn thành CNC trước đó.'
                ], 400);
            }

            $newLog = [
                'action' => 'hoàn thành cnc',
                'operator' => $operatorName,
                'operator_id' => Auth::id(),
                'time' => $logTime,
                'notes' => $notes,
                'cnc_machine' => $cncMachine,
            ];

            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            // Also update notes on the item if complete
            $item->notes = $notes;
            $item->save();

            $message = 'Xác nhận hoàn thành công đoạn cắt CNC thành công.';
        }

        $productName = $item->$nameField ?? '—';

        return response()->json([
            'success' => true,
            'action_type' => $actionType,
            'message' => $message,
            'data' => [
                'product_code' => $codeRecord->product_id,
                'product_name' => $productName,
                'type' => $type,
                'operator' => $operatorName,
                'time' => $logTime,
                'notes' => $notes ?? ($actionType === 'complete' ? 'Hoàn thành CNC' : 'Quay lại CNC'),
            ]
        ]);
    }

    public function pressing()
    {
        return view('processes.pressing');
    }

    public function edgeBanding()
    {
        $acrylicCodes = AcrylicOrderItemCode::whereJsonContains('status', ['action' => 'hoàn thành dán cạnh'])
            ->with('acrylicOrderItem')
            ->get();
        $glassCodes = GlassOrderItemCode::whereJsonContains('status', ['action' => 'hoàn thành dán cạnh'])
            ->with('glassOrderItem')
            ->get();
        $minLateCodes = MinLateOrderItemCode::whereJsonContains('status', ['action' => 'hoàn thành dán cạnh'])
            ->with('minLateOrderItem')
            ->get();

        $history = collect();

        foreach ($acrylicCodes as $code) {
            $item = $code->acrylicOrderItem;
            if (!$item) continue;
            $statusLogs = $code->status ?? [];
            foreach ($statusLogs as $log) {
                if (isset($log['action']) && $log['action'] === 'hoàn thành dán cạnh') {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'acrylic',
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'edge_banding_length' => $log['edge_banding_length'] ?? 0,
                        'item_id' => $item->id
                    ]);
                }
            }
        }

        foreach ($glassCodes as $code) {
            $item = $code->glassOrderItem;
            if (!$item) continue;
            $statusLogs = $code->status ?? [];
            foreach ($statusLogs as $log) {
                if (isset($log['action']) && $log['action'] === 'hoàn thành dán cạnh') {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'glass',
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'edge_banding_length' => $log['edge_banding_length'] ?? 0,
                        'item_id' => $item->id
                    ]);
                }
            }
        }

        foreach ($minLateCodes as $code) {
            $item = $code->minLateOrderItem;
            if (!$item) continue;
            $statusLogs = $code->status ?? [];
            foreach ($statusLogs as $log) {
                if (isset($log['action']) && $log['action'] === 'hoàn thành dán cạnh') {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? $item->name ?? '—',
                        'type' => 'min_late',
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'edge_banding_length' => $log['edge_banding_length'] ?? 0,
                        'item_id' => $item->id
                    ]);
                }
            }
        }

        // Sort history by time descending
        $history = $history->sortByDesc('time')->values();

        return view('processes.edge_banding', compact('history'));
    }

    public function completeEdgeBanding(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string',
            'notes' => 'nullable|string',
            'edge_banding_length' => 'nullable|numeric|min:0',
        ]);

        $codeStr = trim($request->product_code);
        $notes = $request->notes;
        $length = $request->edge_banding_length;

        // Search in Acrylic
        $codeRecord = AcrylicOrderItemCode::where('product_id', $codeStr)->first();
        $type = 'acrylic';
        $nameField = 'product_name';
        $item = null;

        if ($codeRecord) {
            $item = $codeRecord->acrylicOrderItem;
        } else {
            // Search in Glass
            $codeRecord = GlassOrderItemCode::where('product_id', $codeStr)->first();
            $type = 'glass';
            $nameField = 'product_name';
            if ($codeRecord) {
                $item = $codeRecord->glassOrderItem;
            }
        }

        if (!$codeRecord) {
            // Search in Min Late
            $codeRecord = MinLateOrderItemCode::where('product_id', $codeStr)->first();
            $type = 'min_late';
            $nameField = 'name';
            if ($codeRecord) {
                $item = $codeRecord->minLateOrderItem;
            }
        }

        if (!$codeRecord || !$item) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm với mã định danh: ' . $codeStr
            ], 404);
        }

        $currentStatus = $codeRecord->status ?? [];
        $logTime = now()->toDateTimeString();
        $operatorName = Auth::user()->name ?? 'Hệ thống';

        $newLog = [
            'action' => 'hoàn thành dán cạnh',
            'operator' => $operatorName,
            'operator_id' => Auth::id(),
            'time' => $logTime,
            'notes' => $notes,
            'edge_banding_length' => $length ?? 0,
        ];

        $currentStatus[] = $newLog;

        // Save status to the code record
        $codeRecord->status = $currentStatus;
        $codeRecord->save();

        // Also update notes on the item
        $item->notes = $notes;
        $item->save();

        $productName = $item->$nameField ?? '—';

        return response()->json([
            'success' => true,
            'message' => 'Xác nhận hoàn thành công đoạn dán cạnh thành công.',
            'data' => [
                'product_code' => $codeRecord->product_id,
                'product_name' => $productName,
                'type' => $type,
                'operator' => $operatorName,
                'time' => $logTime,
                'notes' => $notes ?? 'Hoàn thành dán cạnh',
                'edge_banding_length' => $length ?? 0,
            ]
        ]);
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
