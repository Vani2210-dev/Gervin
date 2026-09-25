<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerHistory extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'market_group_id',
        'action',
        'summary',
        'changes',
        'photos',
        'latitude',
        'longitude',
        'note',
    ];

    protected $casts = [
        'changes' => 'array',
        'photos'  => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function marketGroup()
    {
        return $this->belongsTo(MarketGroup::class);
    }
}
