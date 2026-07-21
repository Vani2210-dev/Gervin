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
        'debt',
        'policy',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function isAccessibleBy($user)
    {
        if (!$user) {
            return false;
        }
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $this->users()->where('users.id', $user->id)->exists();
    }

    public function customerPayments()
    {
        return $this->hasMany(CustomerPayment::class, 'customer_id')->orderBy('payment_date');
    }

    public function getTotalDebtAttribute()
    {
        return $this->debt;
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

        $orders = $query->get();

        $totalAmount = $orders->sum(function($order) {
            return round($order->total_amount, -3);
        });
        
        $totalPaid = $this->customerPayments()->sum('amount');

        $totalDebt = $this->debt;
        if ($excludeOrderId) {
            $excludedOrder = Order::find($excludeOrderId);
            if ($excludedOrder && !in_array($excludedOrder->status, ['draft', 'cancelled', 'pending'])) {
                $totalDebt -= round($excludedOrder->total_amount, -3);
            }
        }

        return [
            'total_amount' => $totalAmount,
            'total_paid' => $totalPaid,
            'total_debt' => max(0, $totalDebt)
        ];
    }
}
