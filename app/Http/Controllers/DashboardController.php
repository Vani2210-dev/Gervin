<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function index()
    {
        $stats = [
            'total_orders' => \App\Models\Order::where('status', '!=', 'draft')->count(),
            'pending_orders' => \App\Models\Order::where('status', 'pending')->count(),
            'processing_orders' => \App\Models\Order::where('status', 'processing')->count(),
            'completed_orders' => \App\Models\Order::where('status', 'completed')->count(),
            'cancelled_orders' => \App\Models\Order::where('status', 'cancelled')->count(),
            
            'acrylic_orders' => \App\Models\Order::where('status', '!=', 'draft')->where('type', 'acrylic')->count(),
            'glass_orders' => \App\Models\Order::where('status', '!=', 'draft')->where('type', 'glass')->count(),
            'min_late_orders' => \App\Models\Order::where('status', '!=', 'draft')->where('type', 'min_late')->count(),

            'total_customers' => \App\Models\Customer::count(),
            'total_wood_boards' => \App\Models\WoodBoard::count(),
            'total_manufacture_orders' => \App\Models\ManufactureOrder::count(),
            'total_users' => \App\Models\User::count(),
        ];

        $recentOrders = \App\Models\Order::with('customer')
            ->where('status', '!=', 'draft')
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
