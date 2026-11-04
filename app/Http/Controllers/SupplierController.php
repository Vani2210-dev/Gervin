<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\InventoryReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Danh sách nhà cung cấp & công nợ
     */
    public function index(Request $request)
    {
        $dateFilter = $this->getDateFilterParams($request);
        $startDate = $dateFilter['start_date'];
        $endDate = $dateFilter['end_date'];
        $dateLabel = $dateFilter['label'];
        $dateMode = $dateFilter['preset'];
        $dateVal = $request->input('date_val', '');

        $search = trim($request->input('search', ''));
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $query = Supplier::query();

        // Tìm kiếm tổng quát
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('tax_code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        // Lọc chi tiết theo modal
        if ($request->filled('filter_supplier_code')) {
            $query->where('code', 'like', '%' . trim($request->filter_supplier_code) . '%');
        }
        if ($request->filled('filter_name')) {
            $query->where('name', 'like', '%' . trim($request->filter_name) . '%');
        }
        if ($request->filled('filter_phone')) {
            $query->where('phone', 'like', '%' . trim($request->filter_phone) . '%');
        }
        if ($request->filled('filter_supplier_id')) {
            $query->where('id', $request->filter_supplier_id);
        }
        if ($request->filled('filter_status') && in_array($request->filter_status, ['active', 'inactive'])) {
            $query->where('status', $request->filter_status);
        }

        // Lọc mức công nợ
        $debtLevel = $request->input('filter_debt_level');
        if ($debtLevel) {
            switch ($debtLevel) {
                case 'has_debt':
                    $query->where('debt', '>', 0);
                    break;
                case 'no_debt':
                    $query->where('debt', '<=', 0);
                    break;
                case '0-10':
                    $query->whereBetween('debt', [0, 10000000]);
                    break;
                case '10-30':
                    $query->whereBetween('debt', [10000000, 30000000]);
                    break;
                case '30-50':
                    $query->whereBetween('debt', [30000000, 50000000]);
                    break;
                case '50-100':
                    $query->whereBetween('debt', [50000000, 100000000]);
                    break;
                case '100+':
                    $query->where('debt', '>', 100000000);
                    break;
            }
        }

        // Lọc theo thời gian phát sinh giao dịch nhập kho hoặc thanh toán
        if ($startDate || $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereHas('receipts', function ($r) use ($startDate, $endDate) {
                    if ($startDate) $r->where('date', '>=', $startDate);
                    if ($endDate) $r->where('date', '<=', $endDate);
                })->orWhereHas('payments', function ($p) use ($startDate, $endDate) {
                    if ($startDate) $p->where('payment_date', '>=', $startDate);
                    if ($endDate) $p->where('payment_date', '<=', $endDate);
                });
            });
        }

        // Thống kê tổng quan dựa trên danh sách lọc
        $matchingQuery = clone $query;
        $totalSuppliersCount = $matchingQuery->count();
        $totalDebtSum = (float) $matchingQuery->sum('debt');
        $activeSuppliersCount = (clone $matchingQuery)->where('status', 'active')->count();

        // Nhà cung cấp mới tạo trong kỳ
        $newSuppliersQuery = Supplier::query();
        if ($startDate) $newSuppliersQuery->where('created_at', '>=', $startDate . ' 00:00:00');
        if ($endDate) $newSuppliersQuery->where('created_at', '<=', $endDate . ' 23:59:59');
        $newSuppliersCount = ($startDate || $endDate) ? $newSuppliersQuery->count() : Supplier::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

        // Doanh số mua hàng & đã thanh toán trong kỳ (hoặc toàn bộ)
        $receiptsQuery = InventoryReceipt::query()->whereNotIn('status', ['cancelled']);
        $paymentsQuery = SupplierPayment::query();
        if ($startDate) {
            $receiptsQuery->where('date', '>=', $startDate);
            $paymentsQuery->where('payment_date', '>=', $startDate);
        }
        if ($endDate) {
            $receiptsQuery->where('date', '<=', $endDate);
            $paymentsQuery->where('payment_date', '<=', $endDate);
        }
        $totalPeriodPurchasesSum = (float) $receiptsQuery->sum('total_amount');
        $totalPeriodPaidSum = (float) $paymentsQuery->sum('amount');

        // Phân trang danh sách NCC kèm tính toán tổng nhập & đã trả
        $suppliers = $query->withSum(['receipts as period_purchases' => function ($q) use ($startDate, $endDate) {
                $q->whereNotIn('status', ['cancelled']);
                if ($startDate) $q->where('date', '>=', $startDate);
                if ($endDate) $q->where('date', '<=', $endDate);
            }], 'total_amount')
            ->withSum(['payments as period_paid' => function ($q) use ($startDate, $endDate) {
                if ($startDate) $q->where('payment_date', '>=', $startDate);
                if ($endDate) $q->where('payment_date', '<=', $endDate);
            }], 'amount')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $nextCode = Supplier::generateNextCode();

        return view('suppliers.index', compact(
            'suppliers',
            'search',
            'perPage',
            'dateFilter',
            'startDate',
            'endDate',
            'dateLabel',
            'dateMode',
            'dateVal',
            'totalSuppliersCount',
            'activeSuppliersCount',
            'newSuppliersCount',
            'totalPeriodPurchasesSum',
            'totalPeriodPaidSum',
            'totalDebtSum',
            'nextCode'
        ));
    }

    /**
     * Chi tiết nhà cung cấp & công nợ
     */
    public function show(Supplier $supplier, Request $request)
    {
        $supplier->recalculateDebt();

        // Bộ lọc ngày
        $dateFilter = $this->getDateFilterParams($request);
        $startDate = $dateFilter['start_date'];
        $endDate = $dateFilter['end_date'];
        $dateLabel = $dateFilter['label'];
        $dateMode = $dateFilter['preset'];
        $dateVal = $request->input('date_val', '');

        $currTab = $request->input('tab', 'receipts');
        $perPage = (int) $request->input('per_page', 10);
        $search = trim($request->input('search', ''));

        // Query Phiếu nhập kho (tương ứng Orders bên Khách hàng)
        $receiptsQuery = $supplier->receipts()
            ->with(['warehouse', 'creator'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('code', 'like', "%{$search}%")
                        ->orWhere('deliverer', 'like', "%{$search}%")
                        ->orWhere('receiver', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status') && $request->status !== 'all', function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($startDate, function ($q) use ($startDate) {
                $q->where('date', '>=', $startDate);
            })
            ->when($endDate, function ($q) use ($endDate) {
                $q->where('date', '<=', $endDate);
            });

        $receipts = $receiptsQuery->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'receipts_page')
            ->withQueryString();

        // Query Phiếu chi (tương ứng Payments bên Khách hàng)
        $paymentsQuery = $supplier->payments()
            ->with(['creator', 'receipt'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('payment_code', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%")
                        ->orWhere('payment_method', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q) use ($startDate) {
                $q->where('payment_date', '>=', $startDate);
            })
            ->when($endDate, function ($q) use ($endDate) {
                $q->where('payment_date', '<=', $endDate);
            });

        $payments = $paymentsQuery->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'payments_page')
            ->withQueryString();

        // Thống kê tổng số
        $totalPurchases = (float) $supplier->receipts()->whereNotIn('status', ['cancelled'])->sum('total_amount');
        $totalPaid = (float) $supplier->payments()->sum('amount');
        $totalReceiptsCount = $supplier->receipts()->whereNotIn('status', ['cancelled'])->count();
        $totalPaymentsCount = $supplier->payments()->count();

        return view('suppliers.show', compact(
            'supplier',
            'receipts',
            'payments',
            'currTab',
            'perPage',
            'search',
            'dateFilter',
            'startDate',
            'endDate',
            'dateLabel',
            'dateMode',
            'dateVal',
            'totalPurchases',
            'totalPaid',
            'totalReceiptsCount',
            'totalPaymentsCount'
        ));
    }

    public function create()
    {
        $nextCode = Supplier::generateNextCode();
        return view('suppliers.create', compact('nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'           => 'nullable|string|max:50|unique:suppliers,code',
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:100',
            'address'        => 'nullable|string|max:255',
            'tax_code'       => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:100',
            'initial_debt'   => 'nullable|numeric',
            'notes'          => 'nullable|string|max:1000',
            'status'         => 'nullable|in:active,inactive',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Supplier::generateNextCode();
        }

        $validated['initial_debt'] = (float)($validated['initial_debt'] ?? 0);
        $validated['debt'] = $validated['initial_debt'];
        $validated['status'] = $validated['status'] ?? 'active';

        $supplier = Supplier::create($validated);
        $supplier->recalculateDebt();

        return redirect()->route('suppliers.show', $supplier)->with('success', "Thêm nhà cung cấp {$supplier->name} ({$supplier->code}) thành công!");
    }

    public function quickCreate(Request $request)
    {
        $validated = $request->validate([
            'code'           => 'nullable|string|max:50|unique:suppliers,code',
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:100',
            'address'        => 'nullable|string|max:255',
            'tax_code'       => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:100',
            'initial_debt'   => 'nullable|numeric',
            'notes'          => 'nullable|string|max:1000',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Supplier::generateNextCode();
        }

        $validated['initial_debt'] = (float)($validated['initial_debt'] ?? 0);
        $validated['debt'] = $validated['initial_debt'];
        $validated['status'] = 'active';

        $supplier = Supplier::create($validated);

        return response()->json([
            'success'  => true,
            'supplier' => [
                'id'             => $supplier->id,
                'code'           => $supplier->code,
                'name'           => $supplier->name,
                'phone'          => $supplier->phone,
                'email'          => $supplier->email,
                'address'        => $supplier->address,
                'tax_code'       => $supplier->tax_code,
                'contact_person' => $supplier->contact_person,
                'debt'           => (float)$supplier->debt,
            ],
            'message'  => "Đã tạo nhanh nhà cung cấp {$supplier->name} ({$supplier->code})!",
        ]);
    }

    public function quickStore(Request $request)
    {
        return $this->quickCreate($request);
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'code'           => 'required|string|max:50|unique:suppliers,code,' . $supplier->id,
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:100',
            'address'        => 'nullable|string|max:255',
            'tax_code'       => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:100',
            'initial_debt'   => 'nullable|numeric',
            'notes'          => 'nullable|string|max:1000',
            'status'         => 'required|in:active,inactive',
        ]);

        $validated['initial_debt'] = (float)($validated['initial_debt'] ?? 0);
        $supplier->update($validated);
        $supplier->recalculateDebt();

        return redirect()->route('suppliers.show', $supplier)->with('success', "Cập nhật nhà cung cấp {$supplier->name} thành công!");
    }

    public function destroy(Supplier $supplier)
    {
        $receiptCount = $supplier->receipts()->count();
        $paymentCount = $supplier->payments()->count();

        if ($receiptCount > 0 || $paymentCount > 0) {
            $supplier->status = 'inactive';
            $supplier->save();
            return redirect()->route('suppliers.index')->with('warning', "Nhà cung cấp {$supplier->name} đã có {$receiptCount} phiếu nhập kho và {$paymentCount} giao dịch thanh toán nên không thể xóa, đã chuyển trạng thái sang Ngưng hoạt động.");
        }

        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', "Đã xóa nhà cung cấp {$supplier->name} thành công!");
    }

    public function checkCode(Request $request)
    {
        $code = trim($request->input('code', ''));
        $excludeId = $request->input('exclude_id');

        if (!$code) {
            return response()->json(['exists' => false]);
        }

        $exists = Supplier::where('code', $code)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    public function storePayment(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'payment_date'   => 'required|date',
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'nullable|string|max:50',
            'receipt_id'     => 'nullable|exists:inventory_receipts,id',
            'note'           => 'nullable|string|max:500',
        ]);

        $todayCode = 'PC-' . date('ymd') . '-';
        $count = SupplierPayment::where('payment_code', 'like', "{$todayCode}%")->count() + 1;
        $paymentCode = $todayCode . str_pad($count, 3, '0', STR_PAD_LEFT);

        SupplierPayment::create([
            'supplier_id'    => $supplier->id,
            'receipt_id'     => $validated['receipt_id'] ?? null,
            'payment_code'   => $paymentCode,
            'payment_date'   => $validated['payment_date'],
            'amount'         => (float)$validated['amount'],
            'payment_method' => $validated['payment_method'] ?? 'Tiền mặt',
            'note'           => $validated['note'] ?? null,
            'created_by'     => Auth::id(),
        ]);

        $supplier->recalculateDebt();

        return redirect()->back()->with('success', 'Ghi nhận thanh toán ' . number_format($validated['amount'], 0, ',', '.') . '₫ cho nhà cung cấp thành công!');
    }

    public function updatePayment(Request $request, Supplier $supplier, SupplierPayment $payment)
    {
        if ($payment->supplier_id !== $supplier->id) {
            abort(404);
        }

        $validated = $request->validate([
            'payment_date'   => 'required|date',
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'nullable|string|max:50',
            'receipt_id'     => 'nullable|exists:inventory_receipts,id',
            'note'           => 'nullable|string|max:500',
        ]);

        $payment->update([
            'receipt_id'     => $validated['receipt_id'] ?? null,
            'payment_date'   => $validated['payment_date'],
            'amount'         => (float)$validated['amount'],
            'payment_method' => $validated['payment_method'] ?? $payment->payment_method,
            'note'           => $validated['note'] ?? null,
        ]);

        $supplier->recalculateDebt();

        return redirect()->back()->with('success', 'Đã cập nhật phiếu chi ' . $payment->payment_code . ' và điều chỉnh công nợ!');
    }

    public function destroyPayment(Supplier $supplier, SupplierPayment $payment)
    {
        if ($payment->supplier_id !== $supplier->id) {
            abort(404);
        }

        $code = $payment->payment_code;
        $amount = $payment->amount;
        $payment->delete();
        $supplier->recalculateDebt();

        return redirect()->back()->with('success', "Đã xóa phiếu chi {$code} (" . number_format($amount, 0, ',', '.') . '₫) và cập nhật lại công nợ!');
    }

    /**
     * Xuất danh sách nhà cung cấp & công nợ ra file Excel
     */
    public function exportExcel(Request $request)
    {
        $search = trim($request->input('search', ''));
        $dateFilter = $this->getDateFilterParams($request);
        $startDate = $dateFilter['start_date'];
        $endDate = $dateFilter['end_date'];
        $dateLabel = $dateFilter['label'];

        $query = Supplier::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('tax_code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        if ($request->filled('filter_supplier_code')) {
            $query->where('code', 'like', '%' . trim($request->filter_supplier_code) . '%');
        }
        if ($request->filled('filter_name')) {
            $query->where('name', 'like', '%' . trim($request->filter_name) . '%');
        }
        if ($request->filled('filter_phone')) {
            $query->where('phone', 'like', '%' . trim($request->filter_phone) . '%');
        }
        if ($request->filled('filter_status') && in_array($request->filter_status, ['active', 'inactive'])) {
            $query->where('status', $request->filter_status);
        }

        $debtLevel = $request->input('filter_debt_level');
        if ($debtLevel) {
            switch ($debtLevel) {
                case 'has_debt':
                    $query->where('debt', '>', 0);
                    break;
                case 'no_debt':
                    $query->where('debt', '<=', 0);
                    break;
                case '0-10':
                    $query->whereBetween('debt', [0, 10000000]);
                    break;
                case '10-30':
                    $query->whereBetween('debt', [10000000, 30000000]);
                    break;
                case '30-50':
                    $query->whereBetween('debt', [30000000, 50000000]);
                    break;
                case '50-100':
                    $query->whereBetween('debt', [50000000, 100000000]);
                    break;
                case '100+':
                    $query->where('debt', '>', 100000000);
                    break;
            }
        }

        $suppliers = $query->withSum(['receipts as total_purchases' => function ($q) {
                $q->whereNotIn('status', ['cancelled']);
            }], 'total_amount')
            ->withSum('payments as total_paid', 'amount')
            ->orderBy('name')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Nhà cung cấp & Công nợ');

        // Tiêu đề báo cáo
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'BÁO CÁO DANH SÁCH NHÀ CUNG CẤP & CÔNG NỢ');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15)->getColor()->setRGB('1E3A8A');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:L2');
        $sheet->setCellValue('A2', 'Kỳ báo cáo: ' . $dateLabel . ' | Ngày xuất: ' . date('d/m/Y H:i'));
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('6B7280');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header bảng
        $headers = [
            'A4' => 'STT',
            'B4' => 'Mã NCC',
            'C4' => 'Tên nhà cung cấp',
            'D4' => 'Người liên hệ',
            'E4' => 'Số điện thoại',
            'F4' => 'Mã số thuế',
            'G4' => 'Địa chỉ',
            'H4' => 'Nợ ban đầu',
            'I4' => 'Tổng mua hàng',
            'J4' => 'Đã thanh toán',
            'K4' => 'Còn nợ (Công nợ)',
            'L4' => 'Trạng thái',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col, $title);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
        ];
        $sheet->getStyle('A4:L4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(26);

        $row = 5;
        $totalInitialDebt = 0;
        $totalPurchasesSum = 0;
        $totalPaidSum = 0;
        $totalDebtSum = 0;

        foreach ($suppliers as $idx => $s) {
            $initDebt = (float)($s->initial_debt ?? 0);
            $purchases = (float)($s->total_purchases ?? 0);
            $paid = (float)($s->total_paid ?? 0);
            $debt = (float)($s->debt ?? 0);

            $totalInitialDebt += $initDebt;
            $totalPurchasesSum += $purchases;
            $totalPaidSum += $paid;
            $totalDebtSum += $debt;

            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $s->code);
            $sheet->setCellValue('C' . $row, $s->name);
            $sheet->setCellValue('D' . $row, $s->contact_person ?? '—');
            $sheet->setCellValueExplicit('E' . $row, (string)($s->phone ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('F' . $row, (string)($s->tax_code ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('G' . $row, $s->address ?? '—');
            $sheet->setCellValue('H' . $row, $initDebt);
            $sheet->setCellValue('I' . $row, $purchases);
            $sheet->setCellValue('J' . $row, $paid);
            $sheet->setCellValue('K' . $row, $debt);
            $sheet->setCellValue('L' . $row, $s->status === 'active' ? 'Đang hoạt động' : 'Ngưng');

            $row++;
        }

        // Dòng tổng cộng
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->setCellValue('A' . $row, 'TỔNG CỘNG');
        $sheet->setCellValue('H' . $row, $totalInitialDebt);
        $sheet->setCellValue('I' . $row, $totalPurchasesSum);
        $sheet->setCellValue('J' . $row, $totalPaidSum);
        $sheet->setCellValue('K' . $row, $totalDebtSum);
        $sheet->setCellValue('L' . $row, '');

        $sheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '1E3A8A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFF6FF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Định dạng số tiền
        $sheet->getStyle('H5:K' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('A5:A' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B5:B' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('L5:L' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $borderStyle = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']],
            ],
        ];
        $sheet->getStyle('A5:L' . ($row - 1))->applyFromArray($borderStyle);

        // Auto width
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Bao_Cao_Nha_Cung_Cap_Cong_No_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Xuất phiếu nhập kho của nhà cung cấp ra Excel
     */
    public function exportReceiptsExcel(Request $request, Supplier $supplier)
    {
        $dateFilter = $this->getDateFilterParams($request);
        $startDate = $dateFilter['start_date'];
        $endDate = $dateFilter['end_date'];

        $receipts = $supplier->receipts()
            ->with(['warehouse', 'creator'])
            ->when($startDate, fn($q) => $q->where('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('date', '<=', $endDate))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Lịch sử nhập kho');

        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'LỊCH SỬ NHẬP KHO - NHÀ CUNG CẤP: ' . mb_strtoupper($supplier->name) . " ({$supplier->code})");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('1E3A8A');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = [
            'A3' => 'STT',
            'B3' => 'Mã phiếu',
            'C3' => 'Ngày nhập',
            'D3' => 'Kho hàng',
            'E3' => 'Người giao',
            'F3' => 'Tổng tiền',
            'G3' => 'Trạng thái',
            'H3' => 'Ghi chú',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col, $title);
        }

        $sheet->getStyle('A3:H3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $row = 4;
        $totalSum = 0;
        foreach ($receipts as $idx => $r) {
            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $r->code);
            $sheet->setCellValue('C' . $row, $r->date ? $r->date->format('d/m/Y') : '');
            $sheet->setCellValue('D' . $row, $r->warehouse?->name ?? '—');
            $sheet->setCellValue('E' . $row, $r->deliverer ?? '—');
            $sheet->setCellValue('F' . $row, (float)$r->total_amount);
            $sheet->setCellValue('G' . $row, $r->status === 'cancelled' ? 'Đã hủy' : 'Hoàn thành');
            $sheet->setCellValue('H' . $row, $r->notes ?? '');

            if ($r->status !== 'cancelled') {
                $totalSum += (float)$r->total_amount;
            }
            $row++;
        }

        $sheet->mergeCells('A' . $row . ':E' . $row);
        $sheet->setCellValue('A' . $row, 'TỔNG CỘNG');
        $sheet->setCellValue('F' . $row, $totalSum);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        $sheet->getStyle('F4:F' . $row)->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Nhap_Kho_' . $supplier->code . '_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Xuất phiếu chi của nhà cung cấp ra Excel
     */
    public function exportPaymentsExcel(Request $request, Supplier $supplier)
    {
        $dateFilter = $this->getDateFilterParams($request);
        $startDate = $dateFilter['start_date'];
        $endDate = $dateFilter['end_date'];

        $payments = $supplier->payments()
            ->with(['creator', 'receipt'])
            ->when($startDate, fn($q) => $q->where('payment_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('payment_date', '<=', $endDate))
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Lịch sử thanh toán');

        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'LỊCH SỬ THANH TOÁN (PHIẾU CHI) - NHÀ CUNG CẤP: ' . mb_strtoupper($supplier->name) . " ({$supplier->code})");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('1E3A8A');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = [
            'A3' => 'STT',
            'B3' => 'Mã phiếu chi',
            'C3' => 'Ngày chi',
            'D3' => 'Số tiền',
            'E3' => 'Hình thức',
            'F3' => 'Người lập',
            'G3' => 'Ghi chú',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col, $title);
        }

        $sheet->getStyle('A3:G3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $row = 4;
        $totalSum = 0;
        foreach ($payments as $idx => $p) {
            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $p->payment_code);
            $sheet->setCellValue('C' . $row, $p->payment_date ? $p->payment_date->format('d/m/Y') : '');
            $sheet->setCellValue('D' . $row, (float)$p->amount);
            $sheet->setCellValue('E' . $row, $p->payment_method ?? 'Tiền mặt');
            $sheet->setCellValue('F' . $row, $p->creator?->name ?? '—');
            $sheet->setCellValue('G' . $row, $p->note ?? '');

            $totalSum += (float)$p->amount;
            $row++;
        }

        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->setCellValue('A' . $row, 'TỔNG CỘNG');
        $sheet->setCellValue('D' . $row, $totalSum);
        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
        $sheet->getStyle('D4:D' . $row)->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Phieu_Chi_' . $supplier->code . '_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function() use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
