<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrDevice extends Model
{
    protected $table = 'qr_devices';

    // Khai báo id là Device ID nguyên, không tự động tăng
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'name',
        'process_step',
        'action_type',
        'operator_user_id',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_user_id');
    }

    public function logs()
    {
        return $this->hasMany(QrScanLog::class, 'device_id');
    }
}
