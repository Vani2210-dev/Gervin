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
        $this->middleware('permission:view sequence',      ['only' => ['sequenceIndex', 'sequenceExport']]);
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

    /**
     * Lấy dữ liệu tiến độ cho chức năng Sắp xếp đơn hàng, gom nhóm theo ngày.
     */
    private function getSequenceData(?string $date = null, ?string $search = null, ?string $completionStatus = null)
    {
        $user = auth()->user();
        // Chỉ lấy các Lệnh sản xuất đã được duyệt hoàn tất quy trình phê duyệt (đã nhận tem, đang sản xuất hoặc đã hoàn thành)
        $query = ManufactureOrder::with([
            'orders.customer.users',
            'orders.supplies.items.codes',
            'orders.supplies.minLateItems.codes',
            'orders.supplies.glassItems.codes'
        ])->whereIn('status', ['stamps_received', 'in_production', 'completed']);
        
        if ($user && !$user->hasRole('Admin')) {
            $query->whereHas('orders.customer.users', function ($uq) use ($user) {
                $uq->where('users.id', $user->id);
            });
        }

        if (!empty($date)) {
            $query->whereDate('created_at', $date);
        }

        $manufactureOrders = $query->orderBy('created_at', 'desc')->get();

        $groupedData = [];

        foreach ($manufactureOrders as $mo) {
            $dateStr = $mo->created_at->format('Y-m-d');
            if (!isset($groupedData[$dateStr])) {
                $groupedData[$dateStr] = [
                    'date' => $dateStr,
                    'supplies' => collect()
                ];
            }

            // Ngày 1 = Hôm qua (ngày liền trước)
            // Ngày 2 = Hôm nay
            $day1Date = date('Y-m-d', strtotime($dateStr . ' -1 day'));
            $day2Date = date('Y-m-d', strtotime($dateStr));

            foreach ($mo->orders as $order) {
                if ($user && !$user->hasRole('Admin')) {
                    if (!$order->customer || !$order->customer->isAccessibleBy($user)) {
                        continue;
                    }
                }

                $orderSupplyRows = [];
                $orderTotalPlates = 0;
                $orderTotalRemaining = 0;

                foreach ($order->supplies as $supply) {
                    // Tránh trùng lặp nếu đơn hàng liên kết nhiều lệnh trong cùng 1 ngày
                    $exists = $groupedData[$dateStr]['supplies']->contains(function($item) use ($order, $supply) {
                        return $item['order']->id === $order->id && $item['supply']->id === $supply->id;
                    });

                    if ($exists) {
                        continue;
                    }

                    $day1Count = 0;
                    $day2Count = 0;
                    $inProductionCount = 0;
                    $totalCount = 0;
                    $cncCount = 0;
                    $completedTotalCount = 0;

                    if ($order->type === 'glass') {
                        $items = $supply->glassItems;
                        $totalCount = $items->sum('wing_quantity') ?: $items->sum('quantity');
                        $cncCount = $totalCount;
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
                                $action = mb_strtolower($log['action'] ?? '');
                                if ($action === 'hoàn thành qc' || $action === 'hoàn thành làm đẹp') {
                                    $completedTime = $log['time'] ?? null;
                                }
                                // Chỉ tính là đang sản xuất khi đã thực sự quét vào các công đoạn gia công
                                if (
                                    str_contains($action, 'ép') ||
                                    str_contains($action, 'cnc') ||
                                    str_contains($action, 'dán cạnh') ||
                                    str_contains($action, 'làm đẹp') ||
                                    str_contains($action, 'lỗi')
                                ) {
                                    $hasStarted = true;
                                }
                            }

                            // Chỉ tính đã xong khi tấm thực tế có log quét hoàn thành QC / làm đẹp
                            if ($completedTime) {
                                $completedTotalCount++;
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

                    $remainingCount = max(0, $totalCount - $completedTotalCount);

                    $orderTotalPlates += $totalCount;
                    $orderTotalRemaining += $remainingCount;

                    $orderSupplyRows[] = [
                        'order' => $order,
                        'supply' => $supply,
                        'totalCount' => $totalCount,
                        'cncCount' => $cncCount,
                        'day1Count' => $day1Count,
                        'day2Count' => $day2Count,
                        'inProductionCount' => $inProductionCount,
                        'remainingCount' => $remainingCount,
                    ];
                }

                // Lọc theo trạng thái hoàn thành của toàn bộ đơn hàng:
                // Đơn tính là đã xong khi TẤT CẢ các mã màu/vật tư trong đơn đã xử lý hoàn thành hết (tổng còn lại của cả đơn = 0)
                if ($completionStatus === 'completed') {
                    if ($orderTotalRemaining > 0 || $orderTotalPlates === 0) {
                        continue;
                    }
                } elseif ($completionStatus === 'uncompleted') {
                    if ($orderTotalRemaining === 0 && $orderTotalPlates > 0) {
                        continue;
                    }
                }

                // Lọc theo từ khóa tìm kiếm (nếu có) và đưa vào mảng dữ liệu
                foreach ($orderSupplyRows as $row) {
                    if (!empty($search)) {
                        $searchLower = mb_strtolower(trim($search));
                        $customerMatch = mb_stripos($order->customer_name ?? '', $searchLower) !== false;
                        $orderCodeMatch = mb_stripos($order->order_code ?? '', $searchLower) !== false;
                        $supplyCodeMatch = mb_stripos($row['supply']->order_supply_code ?? '', $searchLower) !== false;
                        $supplyNameMatch = mb_stripos($row['supply']->supply_name ?? '', $searchLower) !== false;
                        $moCodeMatch = mb_stripos($mo->code ?? '', $searchLower) !== false;

                        if (!$customerMatch && !$orderCodeMatch && !$supplyCodeMatch && !$supplyNameMatch && !$moCodeMatch) {
                            continue;
                        }
                    }

                    $groupedData[$dateStr]['supplies']->push($row);
                }
            }
        }

        // Sắp xếp các đơn trong từng ngày theo hạn hoàn thành (deadline) tăng dần (hạn gần nhất xếp trước)
        foreach ($groupedData as $dateStr => $group) {
            $groupedData[$dateStr]['supplies'] = $group['supplies']->sortBy(function($item) {
                return $item['order']->deadline ? $item['order']->deadline->timestamp : PHP_INT_MAX;
            })->values();
        }

        // Loại bỏ các ngày không có dữ liệu supply
        return collect($groupedData)->filter(function($day) {
            return $day['supplies']->isNotEmpty();
        });
    }

    /**
     * Trang chủ Sắp xếp đơn hàng (Theo dõi tiến độ tổng quan).
     */
    public function sequenceIndex(Request $request)
    {
        $date = $request->input('date', '');
        $search = $request->input('search', '');
        $completionStatus = $request->input('completion_status', '');
        $groupedSupplies = $this->getSequenceData($date, $search, $completionStatus);

        return view('manufactures.sequence', compact('groupedSupplies', 'date', 'search', 'completionStatus'));
    }

    /**
     * Xuất Excel Sắp xếp đơn hàng.
     */
    public function sequenceExport(Request $request)
    {
        $date = $request->input('date', '');
        $search = $request->input('search', '');
        $completionStatus = $request->input('completion_status', '');
        $groupedSupplies = $this->getSequenceData($date, $search, $completionStatus);

        if ($groupedSupplies->isEmpty()) {
            return back()->with('error', 'Không có dữ liệu phù hợp với bộ lọc để xuất Excel.');
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Đơn hàng Lic');

        // Style templates
        $headerStyle = [
            'font' => ['bold' => true, 'name' => 'Times New Roman', 'size' => 10],
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
            'font' => ['name' => 'Times New Roman', 'size' => 10],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ];

        $titleStyle = [
            'font' => ['bold' => true, 'name' => 'Times New Roman', 'size' => 14],
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
            'O' => 'Thời gian chốt đơn',
            'P' => 'Số ngày hẹn',
            'Q' => 'Thời gian phải hoàn thiện xong'
        ];

        foreach ($headers as $col => $text) {
            $sheet->setCellValue($col . '1', $text);
        }
        $sheet->mergeCells('H1:L1');
        
        // Apply header styles to A1:Q1
        foreach (range('A', 'Q') as $col) {
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
        }

        // Calculate grand totals across all groups
        $grandTotalPlates = 0;
        $grandTotalCnc = 0;
        $grandTotalDay1 = 0;
        $grandTotalInProd = 0;
        $grandTotalDay2 = 0;
        $grandTotalRemaining = 0;

        foreach ($groupedSupplies as $dateGroup) {
            foreach ($dateGroup['supplies'] as $item) {
                $grandTotalPlates += $item['totalCount'];
                $grandTotalCnc += $item['cncCount'];
                $grandTotalDay1 += $item['day1Count'];
                $grandTotalInProd += $item['inProductionCount'];
                $grandTotalDay2 += $item['day2Count'];
                $grandTotalRemaining += $item['remainingCount'];
            }
        }

        $grandTotalStyle = [
            'font' => ['bold' => true, 'name' => 'Times New Roman', 'size' => 10],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFFFBEB'] // Màu nền vàng nhạt giống giao diện web
            ]
        ];

        // Dòng Tổng cộng tất cả các nhóm
        $sheet->setCellValue('A2', 'Tổng cộng');
        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('E2', $grandTotalPlates);
        $sheet->setCellValue('F2', $grandTotalCnc ?: null);
        $sheet->setCellValue('G2', $grandTotalDay1);
        $sheet->setCellValue('H2', $grandTotalInProd);
        $sheet->mergeCells('H2:L2');
        $sheet->setCellValue('M2', $grandTotalDay2);
        $sheet->setCellValue('N2', $grandTotalRemaining);
        $sheet->setCellValue('O2', '');
        $sheet->setCellValue('P2', '');
        $sheet->setCellValue('Q2', '');

        foreach (range('A', 'Q') as $col) {
            $cellStyle = $sheet->getStyle($col . '2');
            $cellStyle->applyFromArray($grandTotalStyle);
            if (in_array($col, ['E', 'F', 'G', 'H', 'M', 'N'])) {
                $cellStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            } else {
                $cellStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            }
            $cellStyle->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }

        $row = 3;

        foreach ($groupedSupplies as $dateGroup) {
            $dateStr = $dateGroup['date'];
            $formattedDate = date('d/m/Y', strtotime($dateStr));
            
            // Title Row for the day
            $sheet->setCellValue('A' . $row, "DANH SÁCH LÀM ĐẸP NGÀY {$formattedDate}");
            $sheet->mergeCells("A{$row}:Q{$row}");
            $sheet->getStyle('A' . $row)->applyFromArray($titleStyle);
            $sheet->getRowDimension($row)->setRowHeight(35);
            $sheet->getStyle("A{$row}:Q{$row}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
            $row++;

            $startDataRow = $row;
            $stt = 1;

            foreach ($dateGroup['supplies'] as $itemData) {
                $order = $itemData['order'];
                $supply = $itemData['supply'];

                $sheet->setCellValue('A' . $row, $stt++);
                $sheet->setCellValue('B' . $row, $order->customer_name);
                $sheet->setCellValue('C' . $row, $order->order_code);
                $sheet->setCellValue('D' . $row, $supply->order_supply_code);
                $sheet->setCellValue('E' . $row, $itemData['totalCount']);
                $sheet->setCellValue('F' . $row, $itemData['cncCount'] ?: null);
                $sheet->setCellValue('G' . $row, $itemData['day1Count']);
                
                $sheet->setCellValue('H' . $row, $itemData['inProductionCount']);
                $sheet->mergeCells("H{$row}:L{$row}");

                $sheet->setCellValue('M' . $row, $itemData['day2Count']);
                $sheet->setCellValue('N' . $row, $itemData['remainingCount']);
                $sheet->setCellValue('O' . $row, $order->order_date ? $order->order_date->format('Y-m-d H:i:s') : '—');
                $sheet->setCellValue('P' . $row, $order->delivery_days ?? '—');
                $sheet->setCellValue('Q' . $row, $order->deadline ? $order->deadline->format('Y-m-d H:i:s') : '—');

                foreach (range('A', 'Q') as $col) {
                    $cellStyle = $sheet->getStyle($col . $row);
                    $cellStyle->applyFromArray($dataStyle);
                    
                    if (in_array($col, ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'M', 'N', 'O', 'P', 'Q'])) {
                        $cellStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    } else {
                        $cellStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    }
                    $cellStyle->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                }

                $row++;
            }

            // Summary row for this day
            $endDataRow = $row - 1;
            if ($endDataRow >= $startDataRow) {
                $sheet->setCellValue('A' . $row, "Tổng cộng ngày {$formattedDate}");
                $sheet->mergeCells("A{$row}:D{$row}");
                $sheet->setCellValue('E' . $row, "=SUM(E{$startDataRow}:E{$endDataRow})");
                $sheet->setCellValue('F' . $row, "=SUM(F{$startDataRow}:F{$endDataRow})");
                $sheet->setCellValue('G' . $row, "=SUM(G{$startDataRow}:G{$endDataRow})");
                
                $sheet->setCellValue('H' . $row, "=SUM(H{$startDataRow}:H{$endDataRow})");
                $sheet->mergeCells("H{$row}:L{$row}");

                $sheet->setCellValue('M' . $row, "=SUM(M{$startDataRow}:M{$endDataRow})");
                $sheet->setCellValue('N' . $row, "=SUM(N{$startDataRow}:N{$endDataRow})");

                $totalRowStyle = [
                    'font' => ['bold' => true, 'name' => 'Times New Roman', 'size' => 10],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF1F5F9']
                    ]
                ];

                foreach (range('A', 'Q') as $col) {
                    $cellStyle = $sheet->getStyle($col . $row);
                    $cellStyle->applyFromArray($totalRowStyle);
                    if (in_array($col, ['E', 'F', 'G', 'H', 'M', 'N'])) {
                        $cellStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    } else {
                        $cellStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    }
                    $cellStyle->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                }
                $row++;
            }

            // Add an empty row between groups
            $row++;
        }

        // Auto-sizing columns
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Danh_sach_sap_xep_don_hang' . (empty($date) ? '' : '_ngay_' . str_replace('-', '_', $date)) . '.xlsx';

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
