<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Traits\HasDateFilter;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class OrderSequenceController extends Controller
{
    use HasDateFilter;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view sequence', ['only' => ['index', 'export']]);
    }

    /**
     * Lấy dữ liệu tiến độ sản xuất trực tiếp từ Đơn hàng (Order), gom nhóm theo ngày chốt đơn.
     */
    private function getSequenceData(?string $date = null, ?string $search = null, ?string $completionStatus = null, ?string $startDate = null, ?string $endDate = null)
    {
        $user = auth()->user();

        // Lấy tất cả các đơn hàng hợp lệ đã chuyển/đang/hoàn thành sản xuất (loại trừ đơn nháp và đơn hủy)
        $query = Order::with([
            'customer.users',
            'supplies.items.codes',
            'supplies.minLateItems.codes',
            'supplies.glassItems.codes'
        ])->whereNotIn('status', ['draft', 'cancelled'])
          ->whereHas('supplies');

        if ($user && !$user->canViewAllMarketGroups()) {
            $userMarketGroupIds = $user->marketGroups()->pluck('market_groups.id');
            $query->whereHas('customer', function ($uq) use ($user, $userMarketGroupIds) {
                $uq->where('users.id', $user->id)
                   ->orWhereIn('market_group_id', $userMarketGroupIds);
            });
        }

        // Lọc ngày theo ngày chốt đơn (order_date) hoặc created_at nếu order_date rỗng
        if (!empty($startDate) && !empty($endDate)) {
            $query->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('order_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                  ->orWhere(function($sub) use ($startDate, $endDate) {
                      $sub->whereNull('order_date')
                          ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                  });
            });
        } elseif (!empty($startDate)) {
            $query->where(function($q) use ($startDate) {
                $q->where('order_date', '>=', $startDate . ' 00:00:00')
                  ->orWhere(function($sub) use ($startDate) {
                      $sub->whereNull('order_date')
                          ->where('created_at', '>=', $startDate . ' 00:00:00');
                  });
            });
        } elseif (!empty($endDate)) {
            $query->where(function($q) use ($endDate) {
                $q->where('order_date', '<=', $endDate . ' 23:59:59')
                  ->orWhere(function($sub) use ($endDate) {
                      $sub->whereNull('order_date')
                          ->where('created_at', '<=', $endDate . ' 23:59:59');
                  });
            });
        } elseif (!empty($date)) {
            $query->where(function($q) use ($date) {
                $q->whereDate('order_date', $date)
                  ->orWhere(function($sub) use ($date) {
                      $sub->whereNull('order_date')
                          ->whereDate('created_at', $date);
                  });
            });
        }

        $orders = $query->orderByRaw('COALESCE(order_date, created_at) DESC')->get();

        $groupedData = [];

        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');

        foreach ($orders as $order) {
            if ($user && !$user->canViewAllMarketGroups()) {
                if (!$order->customer || !$order->customer->isAccessibleBy($user)) {
                    continue;
                }
            }

            $dateStr = $order->order_date ? $order->order_date->format('Y-m-d') : $order->created_at->format('Y-m-d');
            if (!isset($groupedData[$dateStr])) {
                $groupedData[$dateStr] = [
                    'date' => $dateStr,
                    'supplies' => collect()
                ];
            }

            // N = hôm nay (hoặc ngày lọc), N-1 = hôm qua (hoặc ngày trước ngày lọc)
            $day2Date = !empty($date) ? $date : $today;
            $day1Date = !empty($date) ? date('Y-m-d', strtotime($date . ' -1 day')) : $yesterday;

            $orderSupplyRows = [];
            $orderTotalPlates = 0;
            $orderTotalRemaining = 0;

            foreach ($order->supplies as $supply) {
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
                } elseif (in_array($order->type, ['min_late', 'Flywood'])) {
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

                        if ($completedTime) {
                            $completedTotalCount++;
                            $completedDate = date('Y-m-d', strtotime($completedTime));
                            if ($completedDate === $day2Date || $completedDate === $dateStr) {
                                $day2Count++;
                            } elseif ($completedDate === $day1Date || $completedDate === date('Y-m-d', strtotime($dateStr . ' -1 day'))) {
                                $day1Count++;
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

            // Lọc theo trạng thái hoàn thành đơn hàng
            if ($completionStatus === 'completed') {
                if ($orderTotalRemaining > 0 || $orderTotalPlates === 0) {
                    continue;
                }
            } elseif ($completionStatus === 'uncompleted') {
                if ($orderTotalRemaining === 0 && $orderTotalPlates > 0) {
                    continue;
                }
            }

            // Lọc theo từ khóa tìm kiếm
            foreach ($orderSupplyRows as $row) {
                if (!empty($search)) {
                    $searchLower = mb_strtolower(trim($search));
                    $customerMatch = mb_stripos($order->customer_name ?? '', $searchLower) !== false;
                    $orderCodeMatch = mb_stripos($order->order_code ?? '', $searchLower) !== false;
                    $supplyCodeMatch = mb_stripos($row['supply']->order_supply_code ?? '', $searchLower) !== false;
                    $supplyNameMatch = mb_stripos($row['supply']->supply_name ?? '', $searchLower) !== false;

                    if (!$customerMatch && !$orderCodeMatch && !$supplyCodeMatch && !$supplyNameMatch) {
                        continue;
                    }
                }

                $groupedData[$dateStr]['supplies']->push($row);
            }
        }

        // Sắp xếp các đơn trong từng ngày theo deadline tăng dần
        foreach ($groupedData as $dateStr => $group) {
            $groupedData[$dateStr]['supplies'] = $group['supplies']->sortBy(function($item) {
                return $item['order']->deadline ? $item['order']->deadline->timestamp : PHP_INT_MAX;
            })->values();
        }

        return collect($groupedData)->filter(function($day) {
            return $day['supplies']->isNotEmpty();
        });
    }

    /**
     * Trang chủ Sắp xếp đơn hàng (Theo dõi tiến độ).
     */
    public function index(Request $request)
    {
        $dateFilter = $this->getDateFilterParams($request);
        $date = $request->input('date', '');
        $search = $request->input('search', '');
        $completionStatus = $request->input('completion_status', '');
        $groupedSupplies = $this->getSequenceData($date, $search, $completionStatus, $dateFilter['start_date'], $dateFilter['end_date']);

        return view('orders.sequence', compact('groupedSupplies', 'date', 'search', 'completionStatus'));
    }

    /**
     * Xuất Excel Sắp xếp đơn hàng.
     */
    public function export(Request $request)
    {
        $dateFilter = $this->getDateFilterParams($request);
        $date = $request->input('date', '');
        $search = $request->input('search', '');
        $completionStatus = $request->input('completion_status', '');
        $groupedSupplies = $this->getSequenceData($date, $search, $completionStatus, $dateFilter['start_date'], $dateFilter['end_date']);

        if ($groupedSupplies->isEmpty()) {
            return back()->with('error', 'Không có dữ liệu phù hợp với bộ lọc để xuất Excel.');
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Đơn hàng Lic');

        // Style templates (Màu header cam pastel: #F8B182)
        $headerStyle = [
            'font' => ['bold' => true, 'name' => 'Times New Roman', 'size' => 10, 'color' => ['argb' => 'FF000000']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF8B182']
            ]
        ];

        $dataStyle = [
            'font' => ['name' => 'Times New Roman', 'size' => 10],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]
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
            'H' => 'Số tấm đang sản xuất',
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

        foreach (range('A', 'Q') as $col) {
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
        }

        $sheet->freezePane('A2');

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
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFFFBEB']
            ]
        ];

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

            $sheet->setCellValue('A' . $row, "DANH SÁCH TIẾN ĐỘ SẢN XUẤT NGÀY {$formattedDate}");
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
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]
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

            $row++;
        }

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
}