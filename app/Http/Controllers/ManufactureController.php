<?php

namespace App\Http\Controllers;

use App\Models\ManufactureOrder;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;

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
        $perPage = $request->input('per_page', 10);
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
        })->where('status', '!=', 'cancelled')->get();

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
        $perPage = intval(request()->input('per_page', 10));
        if ($perPage <= 0) {
            $perPage = 10;
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
            $query->whereNotIn('id', function($q) {
                $q->select('order_id')->from('manufacture_order_order');
            })->orWhereIn('id', $linkedOrderIds);
        })->where('status', '!=', 'cancelled')->get();

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
                $manufacture->update([
                    'status' => 'in_production',
                    'production_started_by' => $user->id,
                    'production_started_at' => $now,
                ]);
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
