<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseRecord extends Model
{
    protected $fillable = [
        'warehouse_id',
        'voucher_no',
        'date',
        'content',
        'exporter',
        'receiver',
        'in_data',
        'out_data',
        'stock_data',
    ];

    protected $casts = [
        'date' => 'date',
        'in_data' => 'array',
        'out_data' => 'array',
        'stock_data' => 'array',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
