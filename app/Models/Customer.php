<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_code',
        'name',
        'phone',
        'address',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getTotalDebtAttribute()
    {
        return $this->debt_summary['total_debt'];
    }

    public function getDebtSummaryAttribute()
    {
        return $this->getDebtSummaryExcluding();
    }

    public function getDebtSummaryExcluding($excludeOrderId = null)
    {
        $query = $this->orders()
            ->whereNotIn('status', ['draft', 'cancelled', 'pending']);
            
        if ($excludeOrderId) {
            $query->where('id', '!=', $excludeOrderId);
        }

        $orders = $query->withSum('orderPayments', 'amount')->get();

        $unpaidOrders = $orders->filter(function($order) {
            $paid = $order->order_payments_sum_amount ?? 0;
            return round($order->total_amount, -3) > $paid;
        });

        $totalAmount = $unpaidOrders->sum(function($order) {
            return round($order->total_amount, -3);
        });
        
        $totalPaid = $unpaidOrders->sum('order_payments_sum_amount');

        return [
            'total_amount' => $totalAmount,
            'total_paid' => $totalPaid,
            'total_debt' => max(0, $totalAmount - $totalPaid)
        ];
    }
}
