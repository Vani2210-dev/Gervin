<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\InventoryReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $status = $request->input('status', 'all');

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

        if ($status !== 'all' && in_array($status, ['active', 'inactive'])) {
            $query->where('status', $status);
        }

        // Summary stats
        $totalSuppliers = Supplier::count();
        $totalDebt = (float)Supplier::sum('debt');
        $activeSuppliers = Supplier::where('status', 'active')->count();

        $suppliers = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('suppliers.index', compact('suppliers', 'search', 'status', 'totalSuppliers', 'totalDebt', 'activeSuppliers'));
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

        return redirect()->route('suppliers.show', $supplier)->with('success', "Thêm nhà cung cấp {$supplier->name} ({$supplier->code}) thành công!");
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'code'           => 'nullable|string|max:50|unique:suppliers,code',
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'address'        => 'nullable|string|max:255',
            'initial_debt'   => 'nullable|numeric',
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
                'id'      => $supplier->id,
                'code'    => $supplier->code,
                'name'    => $supplier->name,
                'phone'   => $supplier->phone,
                'address' => $supplier->address,
                'debt'    => (float)$supplier->debt,
            ],
            'message'  => "Đã tạo nhanh nhà cung cấp {$supplier->name} ({$supplier->code})!",
        ]);
    }

    public function show(Supplier $supplier, Request $request)
    {
        $supplier->recalculateDebt();

        $receipts = $supplier->receipts()
            ->with(['warehouse', 'creator'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'receipts_page');

        $payments = $supplier->payments()
            ->with(['creator', 'receipt'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'payments_page');

        $totalPurchases = (float)$supplier->receipts()->where('status', '!=', 'cancelled')->sum('total_amount');
        $totalPaid = (float)$supplier->payments()->sum('amount');

        return view('suppliers.show', compact('supplier', 'receipts', 'payments', 'totalPurchases', 'totalPaid'));
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

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Ghi nhận thanh toán ' . number_format($validated['amount'], 0, ',', '.') . 'đ cho nhà cung cấp thành công!');
    }

    public function destroyPayment(Supplier $supplier, SupplierPayment $payment)
    {
        if ($payment->supplier_id !== $supplier->id) {
            abort(404);
        }

        $amount = $payment->amount;
        $payment->delete();
        $supplier->recalculateDebt();

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Đã xóa phiếu chi ' . number_format($amount, 0, ',', '.') . 'đ và cập nhật lại công nợ!');
    }
}

