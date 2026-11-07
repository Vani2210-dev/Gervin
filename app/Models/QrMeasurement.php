<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrMeasurement extends Model
{
    protected $table = 'qr_measurements';

    protected $fillable = [
        'qr_code',
        'order_id',
        'order_code',
        'customer_name',
        'item_type',
        'item_id',
        'product_name',
        'required_height',
        'required_width',
        'actual_height',
        'actual_width',
        'diff_height',
        'diff_width',
        'tolerance',
        'tolerance_height',
        'tolerance_width',
        'is_passed',
        'status',
        'device_id',
        'operator_name',
        'notes',
        'measured_at',
        'raw_payload',
    ];

    protected $casts = [
        'required_height'  => 'float',
        'required_width'   => 'float',
        'actual_height'    => 'float',
        'actual_width'     => 'float',
        'diff_height'      => 'float',
        'diff_width'       => 'float',
        'tolerance'        => 'float',
        'tolerance_height' => 'float',
        'tolerance_width'  => 'float',
        'is_passed'        => 'boolean',
        'measured_at'      => 'datetime',
        'raw_payload'      => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}

