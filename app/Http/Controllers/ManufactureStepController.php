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
        $pressingActions = ['làm lệnh ép', 'xuất kho ván', 'ép đơn', 'ép dự trữ', 'quay lại ép'];

        foreach ($acrylicCodes as $code) {
            $item = $code->acrylicOrderItem;
            if (!$item) continue;
            $statusLogs = $code->status ?? [];
            
            foreach ($statusLogs as $log) {
                if (isset($log['action']) && in_array($log['action'], $pressingActions)) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'acrylic',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
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
                if (isset($log['action']) && in_array($log['action'], $pressingActions)) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'glass',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
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
                if (isset($log['action']) && in_array($log['action'], $pressingActions)) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? $item->name ?? '—',
                        'type' => 'min_late',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'item_id' => $item->id
                    ]);
                }
            }
        }

        // Sort history by time descending
        $history = $history->sortByDesc('time')->values();

        return view('processes.pressing', compact('history'));
    }

    public function completePressing(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string',
            'notes' => 'nullable|string',
            'action_type' => 'required|string|in:làm lệnh ép,xuất kho ván,ép đơn,ép dự trữ,rollback',
        ]);

        $codeStr = trim($request->product_code);
        $notes = $request->notes;
        $actionType = $request->action_type;
        $logTime = now()->toDateTimeString();
        $operatorName = Auth::user()->name ?? 'Hệ thống';

        $pressingActions = ['làm lệnh ép', 'xuất kho ván', 'ép đơn', 'ép dự trữ'];

        // Try to find a ManufactureOrder by this code first (bulk operation)
        $manufacture = \App\Models\ManufactureOrder::with('orders.supplies')->where('code', $codeStr)->first();
        if ($manufacture) {
            $items = $manufacture->getAllItems();
            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lệnh sản xuất này không có sản phẩm nào.'
                ], 400);
            }

            $updatedCount = 0;
            foreach ($items as $itemObj) {
                $codeRecord = null;
                if ($itemObj->type === 'acrylic') {
                    $codeRecord = AcrylicOrderItemCode::find($itemObj->id);
                } elseif ($itemObj->type === 'glass') {
                    $codeRecord = GlassOrderItemCode::find($itemObj->id);
                } elseif ($itemObj->type === 'min_late') {
                    $codeRecord = MinLateOrderItemCode::find($itemObj->id);
                }

                if ($codeRecord) {
                    $currentStatus = $codeRecord->status ?? [];
                    
                    // Find latest pressing log
                    $latestPressingLog = null;
                    $latestPressingIndex = -1;
                    for ($i = count($currentStatus) - 1; $i >= 0; $i--) {
                        $log = $currentStatus[$i];
                        if (isset($log['action']) && in_array($log['action'], $pressingActions)) {
                            $latestPressingLog = $log;
                            $latestPressingIndex = $i;
                            break;
                        }
                    }

                    if ($actionType === 'rollback') {
                        if ($latestPressingLog && $latestPressingIndex !== -1) {
                            $currentStatus = array_slice($currentStatus, 0, $latestPressingIndex);
                            $newLog = [
                                'action' => 'quay lại ép',
                                'operator' => $operatorName,
                                'operator_id' => Auth::id(),
                                'time' => $logTime,
                                'notes' => $notes,
                            ];
                            $currentStatus[] = $newLog;
                            $codeRecord->status = $currentStatus;
                            $codeRecord->save();
                            $updatedCount++;
                        }
                    } else {
                        // Avoid duplicate consecutive action
                        if (!$latestPressingLog || $latestPressingLog['action'] !== $actionType) {
                            $newLog = [
                                'action' => $actionType,
                                'operator' => $operatorName,
                                'operator_id' => Auth::id(),
                                'time' => $logTime,
                                'notes' => $notes,
                            ];
                            $currentStatus[] = $newLog;
                            $codeRecord->status = $currentStatus;
                            $codeRecord->save();
                            $updatedCount++;
                        }
                    }
                }
            }

            if ($updatedCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tất cả sản phẩm trong lệnh này đã ở trạng thái này hoặc không thể hoàn tác.'
                ], 400);
            }

            $msg = $actionType === 'rollback'
                ? "Đã hoàn tác (quay lại) quy trình ép ván cho {$updatedCount} sản phẩm trong Lệnh sản xuất {$codeStr}."
                : "Đã ghi nhận bước \"" . ucwords($actionType) . "\" cho {$updatedCount} sản phẩm trong Lệnh sản xuất {$codeStr}.";

            return response()->json([
                'success' => true,
                'is_bulk' => true,
                'message' => $msg,
                'data' => [
                    'product_code' => $codeStr,
                    'product_name' => "Lệnh sản xuất: {$codeStr}",
                    'type' => 'manufacture_order',
                    'operator' => $operatorName,
                    'time' => $logTime,
                    'notes' => $notes ?? $msg,
                    'action' => $actionType === 'rollback' ? 'quay lại ép' : $actionType,
                ]
            ]);
        }

        // Single plate item code scan
        $codeRecord = AcrylicOrderItemCode::where('product_id', $codeStr)->first();
        $type = 'acrylic';
        $nameField = 'product_name';
        $item = null;

        if ($codeRecord) {
            $item = $codeRecord->acrylicOrderItem;
        } else {
            $codeRecord = GlassOrderItemCode::where('product_id', $codeStr)->first();
            $type = 'glass';
            $nameField = 'product_name';
            if ($codeRecord) {
                $item = $codeRecord->glassOrderItem;
            }
        }

        if (!$codeRecord) {
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
                'message' => 'Không tìm thấy sản phẩm hoặc lệnh sản xuất với mã định danh: ' . $codeStr
            ], 404);
        }

        $currentStatus = $codeRecord->status ?? [];
        
        $latestPressingLog = null;
        $latestPressingIndex = -1;
        for ($i = count($currentStatus) - 1; $i >= 0; $i--) {
            $log = $currentStatus[$i];
            if (isset($log['action']) && in_array($log['action'], $pressingActions)) {
                $latestPressingLog = $log;
                $latestPressingIndex = $i;
                break;
            }
        }

        if ($actionType === 'rollback') {
            if (!$latestPressingLog || $latestPressingIndex === -1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm này chưa ghi nhận quy trình ép ván nên không thể quay lại.'
                ], 400);
            }

            $currentStatus = array_slice($currentStatus, 0, $latestPressingIndex);
            $newLog = [
                'action' => 'quay lại ép',
                'operator' => $operatorName,
                'operator_id' => Auth::id(),
                'time' => $logTime,
                'notes' => $notes,
            ];
            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            $msg = "Đã hoàn tác (quay lại) quy trình ép ván cho sản phẩm {$codeStr}.";
        } else {
            if ($latestPressingLog && $latestPressingLog['action'] === $actionType) {
                return response()->json([
                    'success' => false,
                    'message' => "Sản phẩm này đã được ghi nhận bước \"" . ucwords($actionType) . "\" trước đó."
                ], 400);
            }

            $newLog = [
                'action' => $actionType,
                'operator' => $operatorName,
                'operator_id' => Auth::id(),
                'time' => $logTime,
                'notes' => $notes,
            ];
            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            // Save notes on item if completed
            $item->notes = $notes;
            $item->save();

            $msg = "Đã ghi nhận bước \"" . ucwords($actionType) . "\" cho sản phẩm {$codeStr} thành công.";
        }

        $productName = $item->$nameField ?? '—';

        return response()->json([
            'success' => true,
            'is_bulk' => false,
            'message' => $msg,
            'data' => [
                'product_code' => $codeRecord->product_id,
                'product_name' => $productName,
                'type' => $type,
                'operator' => $operatorName,
                'time' => $logTime,
                'notes' => $notes ?? $msg,
                'action' => $actionType === 'rollback' ? 'quay lại ép' : $actionType,
            ]
        ]);
    }

    public function edgeBanding()
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
                if (isset($log['action']) && in_array($log['action'], ['hoàn thành dán cạnh', 'quay lại dán cạnh'])) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'acrylic',
                        'action' => $log['action'],
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
                if (isset($log['action']) && in_array($log['action'], ['hoàn thành dán cạnh', 'quay lại dán cạnh'])) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'glass',
                        'action' => $log['action'],
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
                if (isset($log['action']) && in_array($log['action'], ['hoàn thành dán cạnh', 'quay lại dán cạnh'])) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? $item->name ?? '—',
                        'type' => 'min_late',
                        'action' => $log['action'],
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
            'action_type' => 'nullable|string|in:complete,rollback',
        ]);

        $codeStr = trim($request->product_code);
        $notes = $request->notes;
        $length = $request->edge_banding_length;
        $actionType = $request->input('action_type', 'complete');

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

        // 1. Find the latest Edge Banding stage log (either 'hoàn thành dán cạnh' or 'quay lại dán cạnh')
        $latestEdgeLog = null;
        for ($i = count($currentStatus) - 1; $i >= 0; $i--) {
            $log = $currentStatus[$i];
            if (isset($log['action']) && in_array($log['action'], ['hoàn thành dán cạnh', 'quay lại dán cạnh'])) {
                $latestEdgeLog = $log;
                break;
            }
        }

        // 2. Find the index of the latest 'hoàn thành dán cạnh' log (for truncation on rollback)
        $lastCompletedEdgeIndex = -1;
        for ($i = count($currentStatus) - 1; $i >= 0; $i--) {
            $log = $currentStatus[$i];
            if (isset($log['action']) && $log['action'] === 'hoàn thành dán cạnh') {
                $lastCompletedEdgeIndex = $i;
                break;
            }
        }

        if ($actionType === 'rollback') {
            if (!$latestEdgeLog || $latestEdgeLog['action'] !== 'hoàn thành dán cạnh' || $lastCompletedEdgeIndex === -1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm này chưa ghi nhận hoàn thành dán cạnh nên không thể quay lại.'
                ], 400);
            }

            // Slice the status array to remove the completed edge banding action and all logs after it
            $currentStatus = array_slice($currentStatus, 0, $lastCompletedEdgeIndex);

            // Record new rollback log
            $newLog = [
                'action' => 'quay lại dán cạnh',
                'operator' => $operatorName,
                'operator_id' => Auth::id(),
                'time' => $logTime,
                'notes' => $notes,
                'edge_banding_length' => $length ?? 0,
            ];

            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            $message = 'Đã hoàn tác (quay lại) công đoạn dán cạnh cho sản phẩm này.';
        } else {
            if ($latestEdgeLog && $latestEdgeLog['action'] === 'hoàn thành dán cạnh') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm này đã được ghi nhận hoàn thành dán cạnh trước đó.'
                ], 400);
            }

            $newLog = [
                'action' => 'hoàn thành dán cạnh',
                'operator' => $operatorName,
                'operator_id' => Auth::id(),
                'time' => $logTime,
                'notes' => $notes,
                'edge_banding_length' => $length ?? 0,
            ];

            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            // Also update notes on the item if complete
            $item->notes = $notes;
            $item->save();

            $message = 'Xác nhận hoàn thành công đoạn dán cạnh thành công.';
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
                'notes' => $notes ?? ($actionType === 'complete' ? 'Hoàn thành dán cạnh' : 'Quay lại dán cạnh'),
                'edge_banding_length' => $length ?? 0,
            ]
        ]);
    }

    public function finishing()
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
                if (isset($log['action']) && in_array($log['action'], ['hoàn thành làm đẹp', 'quay lại làm đẹp'])) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'acrylic',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
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
                if (isset($log['action']) && in_array($log['action'], ['hoàn thành làm đẹp', 'quay lại làm đẹp'])) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'glass',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
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
                if (isset($log['action']) && in_array($log['action'], ['hoàn thành làm đẹp', 'quay lại làm đẹp'])) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? $item->name ?? '—',
                        'type' => 'min_late',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'item_id' => $item->id
                    ]);
                }
            }
        }

        // Sort history by time descending
        $history = $history->sortByDesc('time')->values();

        return view('processes.finishing', compact('history'));
    }

    public function completeFinishing(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string',
            'notes' => 'nullable|string',
            'action_type' => 'nullable|string|in:complete,rollback',
        ]);

        $codeStr = trim($request->product_code);
        $notes = $request->notes;
        $actionType = $request->input('action_type', 'complete');

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

        // 1. Find the latest log for 'làm đẹp'
        $latestFinishingLog = null;
        for ($i = count($currentStatus) - 1; $i >= 0; $i--) {
            $log = $currentStatus[$i];
            if (isset($log['action']) && in_array($log['action'], ['hoàn thành làm đẹp', 'quay lại làm đẹp'])) {
                $latestFinishingLog = $log;
                break;
            }
        }

        // 2. Find the index of the latest 'hoàn thành làm đẹp' log (for truncation on rollback)
        $lastCompletedFinishingIndex = -1;
        for ($i = count($currentStatus) - 1; $i >= 0; $i--) {
            $log = $currentStatus[$i];
            if (isset($log['action']) && $log['action'] === 'hoàn thành làm đẹp') {
                $lastCompletedFinishingIndex = $i;
                break;
            }
        }

        if ($actionType === 'rollback') {
            if (!$latestFinishingLog || $latestFinishingLog['action'] !== 'hoàn thành làm đẹp' || $lastCompletedFinishingIndex === -1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm này chưa ghi nhận hoàn thành làm đẹp nên không thể quay lại.'
                ], 400);
            }

            // Slice status array to remove completed and subsequent logs
            $currentStatus = array_slice($currentStatus, 0, $lastCompletedFinishingIndex);

            // Record rollback log
            $newLog = [
                'action' => 'quay lại làm đẹp',
                'operator' => $operatorName,
                'operator_id' => Auth::id(),
                'time' => $logTime,
                'notes' => $notes,
            ];

            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            $message = 'Đã hoàn tác (quay lại) công đoạn làm đẹp cho sản phẩm này.';
        } else {
            if ($latestFinishingLog && $latestFinishingLog['action'] === 'hoàn thành làm đẹp') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm này đã được ghi nhận hoàn thành làm đẹp trước đó.'
                ], 400);
            }

            $newLog = [
                'action' => 'hoàn thành làm đẹp',
                'operator' => $operatorName,
                'operator_id' => Auth::id(),
                'time' => $logTime,
                'notes' => $notes,
            ];

            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            // Update item notes
            $item->notes = $notes;
            $item->save();

            $message = 'Xác nhận hoàn thành công đoạn làm đẹp thành công.';
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
                'notes' => $notes ?? ($actionType === 'complete' ? 'Hoàn thành làm đẹp' : 'Quay lại làm đẹp'),
            ]
        ]);
    }

    public function qc()
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
                if (isset($log['action']) && in_array($log['action'], ['Hoàn thành QC', 'Ghi nhận lỗi'])) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'acrylic',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
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
                if (isset($log['action']) && in_array($log['action'], ['Hoàn thành QC', 'Ghi nhận lỗi'])) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? '—',
                        'type' => 'glass',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
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
                if (isset($log['action']) && in_array($log['action'], ['Hoàn thành QC', 'Ghi nhận lỗi'])) {
                    $history->push((object)[
                        'product_code' => $code->product_id,
                        'product_name' => $item->product_name ?? $item->name ?? '—',
                        'type' => 'min_late',
                        'action' => $log['action'],
                        'operator' => $log['operator'] ?? 'Hệ thống',
                        'time' => $log['time'] ?? $code->updated_at->toDateTimeString(),
                        'notes' => $log['notes'] ?? $item->notes ?? '',
                        'item_id' => $item->id
                    ]);
                }
            }
        }

        // Sort history by time descending
        $history = $history->sortByDesc('time')->values();

        return view('processes.qc', compact('history'));
    }

    public function completeQc(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string',
            'notes' => 'nullable|string',
            'action_type' => 'nullable|string|in:complete,rollback',
        ]);

        $codeStr = trim($request->product_code);
        $notes = $request->notes;
        $actionType = $request->input('action_type', 'complete');

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

        if ($actionType === 'rollback') {
            // Ghi nhận lỗi là hành động độc lập, ngang hàng với Hoàn thành QC
            $newLog = [
                'action' => 'Ghi nhận lỗi',
                'operator' => $operatorName,
                'operator_id' => Auth::id(),
                'time' => $logTime,
                'notes' => $notes,
            ];

            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            $message = 'Đã ghi nhận lỗi cho sản phẩm này.';
        } else {
            // Check if already completed
            $alreadyCompleted = false;
            for ($i = count($currentStatus) - 1; $i >= 0; $i--) {
                if (isset($currentStatus[$i]['action']) && $currentStatus[$i]['action'] === 'Hoàn thành QC') {
                    $alreadyCompleted = true;
                    break;
                }
            }

            if ($alreadyCompleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm này đã được ghi nhận hoàn thành QC trước đó.'
                ], 400);
            }

            $newLog = [
                'action' => 'Hoàn thành QC',
                'operator' => $operatorName,
                'operator_id' => Auth::id(),
                'time' => $logTime,
                'notes' => $notes,
            ];

            $currentStatus[] = $newLog;
            $codeRecord->status = $currentStatus;
            $codeRecord->save();

            // Also update notes on the item if complete
            $item->notes = $notes;
            $item->save();

            $message = 'Xác nhận hoàn thành công đoạn QC thành công.';
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
                'notes' => $notes ?? ($actionType === 'complete' ? 'Hoàn thành QC' : 'Ghi nhận lỗi'),
            ]
        ]);
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
