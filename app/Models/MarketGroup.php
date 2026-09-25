<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketGroup extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'market_group_user');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'market_group_id');
    }
}
