<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrScanLog extends Model
{
    protected $table = 'qr_scan_logs';

    protected $fillable = [
        'device_id',
        'barcode',
        'scanned_at',
        'scanned_at_raw',
        'status',
        'message',
        'payload',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'payload' => 'array',
    ];

    public function device()
    {
        return $this->belongsTo(QrDevice::class, 'device_id');
    }
}
