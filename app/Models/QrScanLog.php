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

    /**
     * Encode attribute to JSON safely, handling non-UTF-8 bytes from barcode scanners.
     */
    protected function asJson($value)
    {
        if (is_array($value)) {
            array_walk_recursive($value, function (&$item) {
                if (is_string($item)) {
                    $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
                }
            });
        } elseif (is_string($value)) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        $encoded = json_encode($value, JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
        return $encoded !== false ? $encoded : '[]';
    }
}
