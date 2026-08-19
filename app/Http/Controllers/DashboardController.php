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

        $stats = [
            'total_orders' => (clone $orderQuery)->count(),
            'pending_orders' => (clone $orderQuery)->where('status', 'pending')->count(),
            'transferred_orders' => (clone $orderQuery)->where('status', 'transferred')->count(),
            'completed_orders' => (clone $orderQuery)->where('status', 'completed')->count(),
            'cancelled_orders' => (clone $orderQuery)->where('status', 'cancelled')->count(),
            
            'acrylic_orders' => (clone $orderQuery)->where('type', 'acrylic')->count(),
            'glass_orders' => (clone $orderQuery)->where('type', 'glass')->count(),
            'min_late_orders' => (clone $orderQuery)->where('type', 'min_late')->count(),

            'total_customers' => $customerQuery->count(),
            'total_wood_boards' => \App\Models\WoodBoard::count(),
            'total_manufacture_orders' => $manufactureOrderQuery->count(),
            'total_users' => \App\Models\User::count(),
        ];

        $recentOrders = (clone $orderQuery)->with('customer')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard/index', compact('stats', 'recentOrders'));
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
