<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManufactureStampDistribution extends Model
{
    protected $fillable = [
        'manufacture_order_id',
        'worker_id',
        'quantity',
    ];

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function manufactureOrder()
    {
        return $this->belongsTo(ManufactureOrder::class, 'manufacture_order_id');
    }
}
