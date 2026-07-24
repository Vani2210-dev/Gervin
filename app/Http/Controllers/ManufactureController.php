<?php

namespace App\Http\Controllers;

use App\Models\ManufactureOrder;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ManufactureController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view manufacture',   ['only' => ['index', 'show', 'printStamps']]);
        $this->middleware('permission:add manufacture',    ['only' => ['create', 'store']]);
        $this->middleware('permission:edit manufacture',   ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete manufacture', ['only' => ['destroy']]);
        $this->middleware('permission:approve manufacture',['only' => ['approveStep']]);
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search  = $request->input('search', '');
        $status  = $request->input('status', '');

        $manufactures = ManufactureOrder::with(['orders', 'creator'])
            ->when($search, function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('manufactures.index', compact('manufactures', 'perPage', 'search', 'status'));
    }

    public function exportExcel(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));

        // Query 1: Find all orders linked to ManufactureOrders created on this date
        $manufactureOrders = ManufactureOrder::whereDate('created_at', $date)
            ->with(['orders.supplies.items.codes', 'orders.supplies.minLateItems.codes', 'orders.supplies.glassItems.codes'])
            ->get();

        $orders = collect();
        foreach ($manufactureOrders as $mo) {
            foreach ($mo->orders as $order) {
                $orders->push($order);
            }
        }

        if ($orders->isEmpty()) {
            return back()->with('error', 'Không có lệnh sản xuất nào trong ngày này để xuất Excel.');
        }

        // Extract and group supplies
        $suppliesList = collect();
        foreach ($orders as $order) {
            foreach ($order->supplies as $supply) {
                $suppliesList->push([
                    'order' => $order,
                    'supply' => $supply
                ]);
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Đơn hàng Lic');

        // Style templates
        $headerStyle = [
            'font' => ['bold' => true, 'name' => 'Arial', 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE2E8F0']
            ]
        ];

        $dataStyle = [
            'font' => ['name' => 'Arial', 'size' => 10],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ];

        $titleStyle = [
            'font' => ['bold' => true, 'name' => 'Arial', 'size' => 14],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];

        // Row 1: Headers
        $headers = [
            'A' => 'STT',
            'B' => 'Đơn hàng',
            'C' => 'Số phiếu',
            'D' => 'Mã màu',
            'E' => 'Tổng tấm',
            'F' => 'Kính/ CNC',
            'G' => 'Số tấm đã xong ngày 1',
            'H' => 'Số tấm đang sản xuất', // Will merge H1:L1
            'I' => '',
            'J' => '',
            'K' => '',
            'L' => '',
            'M' => 'Số tấm đã xong ngày 2',
            'N' => 'Còn lại phải làm',
            'O' => 'Địa chỉ',
            'P' => 'Thời gian chốt đơn',
            'Q' => 'Số ngày hẹn',
            'R' => 'Thời gian phải hoàn thiện xong'
        ];

        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col . '1', $text);
        }
        $sheet->mergeCells('H1:L1');
        
        // Apply header styles to A1:R1
        foreach (range('A', 'R') as $col) {
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
        }

        // Row 2: Title
        $formattedDate = date('d/m/Y', strtotime($date));
        $sheet->setCellValue('A2', "DANH SÁCH LÀM ĐẸP NGÀY {$formattedDate}");
        $sheet->mergeCells('A2:R2');
        $sheet->getStyle('A2')->applyFromArray($titleStyle);
        $sheet->getRowDimension('2')->setRowHeight(35);
        $sheet->getStyle('A2:R2')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

        // Row 3+: Data
        $row = 3;
        $stt = 1;
        $day1Date = date('Y-m-d', strtotime($date));
        $day2Date = date('Y-m-d', strtotime($date . ' +1 day'));

        foreach ($suppliesList as $itemData) {
            $order = $itemData['order'];
            $supply = $itemData['supply'];

            $day1Count = 0;
            $day2Count = 0;
            $inProductionCount = 0;
            $totalCount = 0;
            $cncCount = 0;

            if ($order->type === 'glass') {
                $items = $supply->glassItems;
                $totalCount = $items->sum('quantity');
                $cncCount = $totalCount; // count of glass plates
            } elseif ($order->type === 'min_late') {
                $items = $supply->minLateItems;
                $totalCount = $items->sum('quantity');
                $cncCount = $items->where('cnc', 1)->sum('quantity');
            } else {
                $items = $supply->items;
                $totalCount = $items->sum('quantity');
                $cncCount = $items->filter(fn($i) => !empty($i->vertical_grain_cnc))->sum('quantity');
            }

            foreach ($items as $item) {
                foreach ($item->codes as $code) {
                    $statusLogs = $code->status ?? [];
                    if (is_string($statusLogs)) {
                        $statusLogs = json_decode($statusLogs, true) ?? [];
                    }

                    $completedTime = null;
                    $hasStarted = false;
                    foreach ($statusLogs as $log) {
                        $action = strtolower($log['action'] ?? '');
                        if (in_array($action, ['hoàn thành làm đẹp', 'hoàn thành qc'])) {
                            $completedTime = $log['time'] ?? null;
                        }
                        if (in_array($action, ['đã nhận tem', 'hoàn thành cnc', 'ép ván', 'hoàn thành ép', 'hoàn thành dán cạnh'])) {
                            $hasStarted = true;
                        }
                    }

                    if ($completedTime) {
                        $completedDate = date('Y-m-d', strtotime($completedTime));
                        if ($completedDate === $day1Date) {
                            $day1Count++;
                        } elseif ($completedDate === $day2Date) {
                            $day2Count++;
                        }
                    } elseif ($hasStarted) {
                        $inProductionCount++;
                    }
                }
            }

            $remainingCount = max(0, $totalCount - $day1Count - $day2Count);

            $sheet->setCellValue('A' . $row, $stt++);
            $sheet->setCellValue('B' . $row, $order->customer_name);
            $sheet->setCellValue('C' . $row, $order->order_code);
            $sheet->setCellValue('D' . $row, $supply->supply_name);
            $sheet->setCellValue('E' . $row, $totalCount);
            $sheet->setCellValue('F' . $row, $cncCount ?: null);
            $sheet->setCellValue('G' . $row, $day1Count);
            
            // Set value in H and merge H:L
            $sheet->setCellValue('H' . $row, $inProductionCount);
            $sheet->mergeCells("H{$row}:L{$row}");

            $sheet->setCellValue('M' . $row, $day2Count);
            $sheet->setCellValue('N' . $row, $remainingCount);
            $sheet->setCellValue('O' . $row, $order->address);
            $sheet->setCellValue('P' . $row, $order->order_date ? $order->order_date->format('Y-m-d H:i:s') : '—');
            $sheet->setCellValue('Q' . $row, $order->delivery_days ?? '—');
            $sheet->setCellValue('R' . $row, $order->deadline ? $order->deadline->format('Y-m-d H:i:s') : '—');

            // Apply data row styling and alignments
            foreach (range('A', 'R') as $col) {
                $cellStyle = $sheet->getStyle($col . $row);
                $cellStyle->applyFromArray($dataStyle);
                
                // Alignments
                if (in_array($col, ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'M', 'N', 'P', 'Q', 'R'])) {
                    $cellStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                } else {
                    $cellStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }
                $cellStyle->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            }

            $row++;
        }

        // Summary row
        if ($row > 3) {
            $sheet->setCellValue('A' . $row, 'Tổng cộng');
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue('E' . $row, "=SUM(E3:E" . ($row - 1) . ")");
            $sheet->setCellValue('F' . $row, "=SUM(F3:F" . ($row - 1) . ")");
            $sheet->setCellValue('G' . $row, "=SUM(G3:G" . ($row - 1) . ")");
            
            // Sum H and merge H:L
            $sheet->setCellValue('H' . $row, "=SUM(H3:H" . ($row - 1) . ")");
            $sheet->mergeCells("H{$row}:L{$row}");

            $sheet->setCellValue('M' . $row, "=SUM(M3:M" . ($row - 1) . ")");
            $sheet->setCellValue('N' . $row, "=SUM(N3:N" . ($row - 1) . ")");

            // Styling for summary row
            $totalRowStyle = [
                'font' => ['bold' => true, 'name' => 'Arial', 'size' => 10],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF1F5F9'] // light gray background
                ]
            ];

            foreach (range('A', 'R') as $col) {
                $cellStyle = $sheet->getStyle($col . $row);
                $cellStyle->applyFromArray($totalRowStyle);
                if (in_array($col, ['E', 'F', 'G', 'H', 'M', 'N'])) {
                    $cellStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                } else {
                    $cellStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }
                $cellStyle->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            }
        }

        // Auto-sizing columns
        foreach (range('A', 'R') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Danh_sach_sap_xep_don_hang_ngay_' . str_replace('-', '_', $date) . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        $writer->save('php://output');
        exit;
    }

    public function create()
    {
        $today = date('Ymd');
        $lastMO = ManufactureOrder::where('code', 'like', "LSX-{$today}-%")->orderBy('id', 'desc')->first();
        $nextNumber = 1;
        if ($lastMO) {
            $parts = explode('-', $lastMO->code);
            $nextNumber = intval(end($parts)) + 1;
        }
        $nextCode = "LSX-{$today}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Get unlinked active orders (not cancelled)
        $orders = Order::whereNotIn('id', function($q) {
            $q->select('order_id')->from('manufacture_order_order');
        })->where('status', 'transferred')->get();

        return view('manufactures.create', compact('nextCode', 'orders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'      => 'required|string|unique:manufacture_orders,code',
            'notes'     => 'nullable|string',
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
        ]);

        $manufacture = ManufactureOrder::create([
            'code'       => $request->code,
            'notes'      => $request->notes,
            'status'     => 'initialized',
            'created_by' => Auth::id(),
        ]);

        $manufacture->orders()->attach($request->order_ids);

        return redirect()->route('manufactures.index')->with('success', 'Tạo lệnh sản xuất thành công.');
    }

    public function show(ManufactureOrder $manufacture)
    {
        $manufacture->load([
            'orders.supplies',
            'creator',
            'techApprover',
            'managerApprover',
            'stampsReceiver',
            'productionStarter',
            'completer',
            'stampDistributions.worker'
        ]);

        $allItems = $manufacture->getAllItems();
        $totalItemsCount = $allItems->count();

        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = intval(request()->input('per_page', 15));
        if ($perPage <= 0) {
            $perPage = 15;
        }

        $currentItems = $allItems->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $items = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $totalItemsCount,
            $perPage,
            $currentPage,
            [
                'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );

        $workers = \App\Models\User::orderBy('name')->get();

        return view('manufactures.show', compact('manufacture', 'items', 'workers', 'totalItemsCount'));
    }

    public function edit(ManufactureOrder $manufacture)
    {
        $manufacture->load('orders');
        $linkedOrderIds = $manufacture->orders->pluck('id')->toArray();

        // Get unlinked active orders OR orders already linked to this manufacture order
        $orders = Order::where(function($query) use ($linkedOrderIds) {
            $query->where(function($q) {
                $q->whereNotIn('id', function($subQ) {
                    $subQ->select('order_id')->from('manufacture_order_order');
                })->where('status', 'transferred');
            })->orWhereIn('id', $linkedOrderIds);
        })->get();

        return view('manufactures.edit', compact('manufacture', 'orders', 'linkedOrderIds'));
    }

    public function update(Request $request, ManufactureOrder $manufacture)
    {
        $request->validate([
            'notes'     => 'nullable|string',
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
        ]);

        $manufacture->update([
            'notes' => $request->notes,
        ]);

        $manufacture->orders()->sync($request->order_ids);

        return redirect()->route('manufactures.show', $manufacture)->with('success', 'Cập nhật lệnh sản xuất thành công.');
    }

    public function destroy(ManufactureOrder $manufacture)
    {
        $manufacture->orders()->detach();
        $manufacture->delete();
        return redirect()->route('manufactures.index')->with('success', 'Xóa lệnh sản xuất thành công.');
    }

    public function approveStep(ManufactureOrder $manufacture, string $step)
    {
        $user = Auth::user();
        $now = now();

        switch ($step) {
            case 'approve_tech':
                if ($manufacture->status !== 'initialized') {
                    return back()->with('error', 'Trạng thái không hợp lệ để duyệt kỹ thuật.');
                }
                $manufacture->update([
                    'status' => 'tech_approved',
                    'tech_approved_by' => $user->id,
                    'tech_approved_at' => $now,
                ]);
                $msg = 'Duyệt kỹ thuật thành công.';
                break;

            case 'approve_manager':
                if ($manufacture->status !== 'tech_approved') {
                    return back()->with('error', 'Trạng thái không hợp lệ để quản đốc duyệt.');
                }
                $manufacture->update([
                    'status' => 'manager_approved',
                    'manager_approved_by' => $user->id,
                    'manager_approved_at' => $now,
                ]);
                $msg = 'Quản đốc duyệt thành công.';
                break;

            case 'receive_stamps':
                if ($manufacture->status !== 'manager_approved') {
                    return back()->with('error', 'Trạng thái không hợp lệ để nhận tem.');
                }
                
                $manufacture->update([
                    'status' => 'stamps_received',
                    'stamps_received_by' => $user->id,
                    'stamps_received_at' => $now,
                ]);

                // Update status log for all associated plates to "đã nhận tem"
                $orderIds = $manufacture->orders->pluck('id')->toArray();
                $supplyIds = \App\Models\OrderSupply::whereIn('order_id', $orderIds)->pluck('id')->toArray();
                
                $newStatus = [
                    [
                        'time' => $now->format('Y-m-d H:i:s'),
                        'notes' => null,
                        'action' => 'đã nhận tem',
                        'operator' => $user->name ?? 'Hệ thống',
                        'operator_id' => $user->id,
                    ]
                ];
                $newStatusJson = json_encode($newStatus);

                // Acrylic
                $acrylicItemIds = \App\Models\AcrylicOrderItem::whereIn('order_supply_id', $supplyIds)->pluck('id')->toArray();
                \App\Models\AcrylicOrderItemCode::whereIn('acrylic_order_item_id', $acrylicItemIds)
                    ->update(['status' => $newStatusJson]);

                // Glass
                $glassItemIds = \App\Models\GlassOrderItem::whereIn('order_supply_id', $supplyIds)->pluck('id')->toArray();
                \App\Models\GlassOrderItemCode::whereIn('glass_order_item_id', $glassItemIds)
                    ->update(['status' => $newStatusJson]);

                // Min Late
                $minLateItemIds = \App\Models\MinLateOrderItem::whereIn('order_supply_id', $supplyIds)->pluck('id')->toArray();
                \App\Models\MinLateOrderItemCode::whereIn('min_late_order_item_id', $minLateItemIds)
                    ->update(['status' => $newStatusJson]);

                $msg = 'Xác nhận nhận tem thành công.';
                break;

            case 'start_production':
                if ($manufacture->status !== 'stamps_received') {
                    return back()->with('error', 'Trạng thái không hợp lệ để bắt đầu sản xuất.');
                }
                \Illuminate\Support\Facades\DB::transaction(function () use ($manufacture, $user, $now) {
                    $manufacture->update([
                        'status' => 'in_production',
                        'production_started_by' => $user->id,
                        'production_started_at' => $now,
                    ]);
                    $manufacture->orders()->update(['status' => 'in_production']);
                });
                $msg = 'Bắt đầu sản xuất thành công.';
                break;

            case 'complete':
                if ($manufacture->status !== 'in_production') {
                    return back()->with('error', 'Trạng thái không hợp lệ để hoàn thành.');
                }
                $manufacture->update([
                    'status' => 'completed',
                    'completed_by' => $user->id,
                    'completed_at' => $now,
                ]);
                $msg = 'Hoàn thành lệnh sản xuất.';
                break;

            default:
                return back()->with('error', 'Thao tác không hợp lệ.');
        }

        return back()->with('success', $msg);
    }

    public function printStamps(ManufactureOrder $manufacture)
    {
        $manufacture->load([
            'orders.supplies',
            'stampDistributions.worker'
        ]);
        $items = $manufacture->getAllItems();
        $workers = \App\Models\User::orderBy('name')->get();

        return view('manufactures.print_stamps', compact('manufacture', 'items', 'workers'));
    }

    public function assignStamps(Request $request, ManufactureOrder $manufacture)
    {
        // 1. Quick distribution action
        if ($request->input('action') === 'quick_distribute') {
            $request->validate([
                'worker_id' => 'required|exists:users,id',
                'quantity'  => 'required|integer|min:1',
            ]);

            $workerId = $request->worker_id;
            $quantity = intval($request->quantity);

            $totalStampsCount = $manufacture->getAllItems()->count();
            $assignedStampsCount = \App\Models\ManufactureStampDistribution::where('manufacture_order_id', $manufacture->id)->sum('quantity');
            $unassignedStampsCount = $totalStampsCount - $assignedStampsCount;

            if ($quantity > $unassignedStampsCount) {
                return back()->with('error', "Không thể phân phát quá số tem chưa phân phối ({$unassignedStampsCount} tem).");
            }

            // Create or update the distribution row
            $distribution = \App\Models\ManufactureStampDistribution::where('manufacture_order_id', $manufacture->id)
                ->where('worker_id', $workerId)
                ->first();

            if ($distribution) {
                $distribution->increment('quantity', $quantity);
            } else {
                \App\Models\ManufactureStampDistribution::create([
                    'manufacture_order_id' => $manufacture->id,
                    'worker_id' => $workerId,
                    'quantity' => $quantity,
                ]);
            }

            return back()->with('success', "Đã phân phát thành công {$quantity} tem cho nhân viên.");
        }

        // 2. Reset all assignments action
        if ($request->input('action') === 'reset_all') {
            \App\Models\ManufactureStampDistribution::where('manufacture_order_id', $manufacture->id)->delete();
            return back()->with('success', 'Đã thu hồi toàn bộ phân phát tem.');
        }

        return back()->with('error', 'Thao tác không hợp lệ.');
    }

}
