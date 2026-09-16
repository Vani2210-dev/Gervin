<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user && $user->hasRole('Admin');

        $orderQuery = \App\Models\Order::where('status', '!=', 'draft');
        $customerQuery = \App\Models\Customer::query();
        $manufactureOrderQuery = \App\Models\ManufactureOrder::query();

        if (!$isAdmin && $user) {
            $orderQuery->whereHas('customer.users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });

            $customerQuery->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });

            $manufactureOrderQuery->whereHas('orders.customer.users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        $now = \Carbon\Carbon::now();
        $today = \Carbon\Carbon::today();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Target doanh thu tháng hiện tại
        $targetRecord = \App\Models\RevenueTarget::where('year', $now->year)->where('month', $now->month)->first();
        $revenueTarget = $targetRecord ? (float) $targetRecord->target_amount : 500000000;

        // Doanh thu tháng
        $revenueMonth = (clone $orderQuery)->where('status', 'completed')
            ->whereBetween('order_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->sum('total_amount');
        if ($revenueMonth <= 0) {
            $revenueMonth = (clone $orderQuery)->where('status', 'completed')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('total_amount');
            if ($revenueMonth <= 0) {
                $revenueMonth = (clone $orderQuery)->where('status', 'completed')->sum('total_amount');
            }
        }

        $revenueToday = (clone $orderQuery)->where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        $totalWipValue = (clone $orderQuery)->whereIn('status', ['pending', 'transferred', 'in_production'])
            ->sum('total_amount');

        $totalCustomerDebt = (clone $customerQuery)->sum('debt');
        $customersWithDebtCount = (clone $customerQuery)->where('debt', '>', 0)->count();

        $stats = [
            // Đơn hàng
            'total_orders' => (clone $orderQuery)->count(),
            'orders_today' => (clone $orderQuery)->whereDate('created_at', $today)->count(),
            'orders_this_month' => (clone $orderQuery)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            'pending_orders' => (clone $orderQuery)->where('status', 'pending')->count(),
            'transferred_orders' => (clone $orderQuery)->where('status', 'transferred')->count(),
            'in_production_orders' => (clone $orderQuery)->where('status', 'in_production')->count(),
            'completed_orders' => (clone $orderQuery)->where('status', 'completed')->count(),
            'cancelled_orders' => (clone $orderQuery)->where('status', 'cancelled')->count(),
            
            // Loại đơn & doanh thu từng loại
            'acrylic_orders' => (clone $orderQuery)->where('type', 'acrylic')->count(),
            'acrylic_revenue' => (clone $orderQuery)->where('type', 'acrylic')->sum('total_amount'),
            'glass_orders' => (clone $orderQuery)->where('type', 'glass')->count(),
            'glass_revenue' => (clone $orderQuery)->where('type', 'glass')->sum('total_amount'),
            'min_late_orders' => (clone $orderQuery)->where('type', 'min_late')->count(),
            'min_late_revenue' => (clone $orderQuery)->where('type', 'min_late')->sum('total_amount'),

            // Tài chính & Doanh thu
            'revenue_month' => $revenueMonth,
            'revenue_today' => $revenueToday,
            'revenue_target' => $revenueTarget,
            'revenue_progress' => $revenueTarget > 0 ? min(round(($revenueMonth / $revenueTarget) * 100, 1), 100) : 0,
            'total_wip_value' => $totalWipValue,
            'total_customer_debt' => $totalCustomerDebt,
            'customers_with_debt_count' => $customersWithDebtCount,

            // Đối tượng kho & bảng giá
            'total_customers' => (clone $customerQuery)->count(),
            'total_wood_boards' => \App\Models\WoodBoard::count(),
            'total_glass_prices' => \App\Models\GlassPrice::count(),
            'total_minlate_prices' => \App\Models\MinLatePrice::count(),
            'total_warehouses' => \App\Models\Warehouse::count(),
            'total_dc_stocks' => \App\Models\DcStock::where('status', 'available')->count(),
            'total_dc_used' => \App\Models\DcStock::where('status', 'used')->count(),
            'total_manufacture_orders' => (clone $manufactureOrderQuery)->count(),
            'total_qr_devices' => \App\Models\QrDevice::count(),
            'qr_scans_today' => \App\Models\QrScanLog::whereDate('created_at', $today)->count(),
            'total_users' => \App\Models\User::count(),

            // Đóng gói & Giao nhận
            'packages_total' => \App\Models\PackingPackage::count(),
            'packages_ready_dispatch' => \App\Models\PackingPackage::where('status', 'completed')->whereNull('dispatched_at')->count(),
            'packages_dispatched' => \App\Models\PackingPackage::whereNotNull('dispatched_at')->whereNull('delivered_at')->count(),
            'packages_delivered' => \App\Models\PackingPackage::whereNotNull('delivered_at')->count(),
        ];

        // Đơn hàng khẩn cấp (Quá hạn hoặc sắp tới hạn trong 48h)
        $urgentOrders = (clone $orderQuery)
            ->with('customer')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('deadline')
            ->where('deadline', '<=', $now->copy()->addHours(48))
            ->orderBy('deadline', 'asc')
            ->limit(5)
            ->get();

        // Top khách hàng doanh số / đơn hàng
        $topCustomers = (clone $customerQuery)
            ->withCount(['orders' => function ($q) {
                $q->where('status', '!=', 'draft');
            }])
            ->withSum(['orders' => function ($q) {
                $q->where('status', '!=', 'draft');
            }], 'total_amount')
            ->orderByDesc('orders_sum_total_amount')
            ->limit(5)
            ->get();

        // Dữ liệu biểu đồ 7 ngày gần nhất
        $chartDays = [];
        $chartOrderCounts = [];
        $chartRevenues = [];

        for ($i = 6; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i);
            $dayStr = $d->format('d/m');
            $dateStr = $d->toDateString();

            $chartDays[] = $dayStr;
            $chartOrderCounts[] = (clone $orderQuery)->whereDate('created_at', $dateStr)->count();
            $chartRevenues[] = (float) (clone $orderQuery)->where('status', 'completed')->whereDate('created_at', $dateStr)->sum('total_amount');
        }

        // Đơn hàng gần đây
        $recentOrders = (clone $orderQuery)->with('customer')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard/index', compact(
            'stats',
            'recentOrders',
            'urgentOrders',
            'topCustomers',
            'chartDays',
            'chartOrderCounts',
            'chartRevenues'
        ));
    }

    public function index2()
    {
        return view('dashboard/index2');
    }

    public function index3()
    {
        return view('dashboard/index3');
    }

    public function index4()
    {
        return view('dashboard/index4');
    }

    public function index5()
    {
        return view('dashboard/index5');
    }

    public function index6()
    {
        return view('dashboard/index6');
    }

    public function index7()
    {
        return view('dashboard/index7');
    }

    public function index8()
    {
        return view('dashboard/index8');
    }

    public function index9()
    {
        return view('dashboard/index9');
    }

}
