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
            'completer'
        ]);

        $items = $manufacture->getAllItems();
        $workers = \App\Models\User::orderBy('name')->get();

        return view('manufactures.show', compact('manufacture', 'items', 'workers'));
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
        $manufacture->load('orders.supplies');
        $items = $manufacture->getAllItems();
        $workers = \App\Models\User::orderBy('name')->get();

        return view('manufactures.print_stamps', compact('manufacture', 'items', 'workers'));
    }

    public function assignStamps(Request $request, ManufactureOrder $manufacture)
    {
        $request->validate([
            'worker_id' => 'nullable|exists:users,id',
            'items'     => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.type' => 'required|string|in:acrylic,glass,min_late',
        ]);

        foreach ($request->items as $item) {
            if (isset($item['checked']) && $item['checked'] == '1') {
                if ($item['type'] === 'acrylic') {
                    \App\Models\AcrylicOrderItemCode::where('id', $item['id'])
                        ->update(['assigned_worker_id' => $request->worker_id]);
                } elseif ($item['type'] === 'glass') {
                    \App\Models\GlassOrderItemCode::where('id', $item['id'])
                        ->update(['assigned_worker_id' => $request->worker_id]);
                } elseif ($item['type'] === 'min_late') {
                    \App\Models\MinLateOrderItemCode::where('id', $item['id'])
                        ->update(['assigned_worker_id' => $request->worker_id]);
                }
            }
        }

        return back()->with('success', 'Phân công dán tem thành công.');
    }

}
