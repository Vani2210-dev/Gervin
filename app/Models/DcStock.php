<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DcStock extends Model
{
    use SoftDeletes;

    protected $table = 'dc_stocks';

    protected $fillable = [
        'color_code',
        'note',
        'height',
        'width',
        'quantity',
        'location',
        'status',
        'created_by',
    ];

    protected $casts = [
        'height'   => 'integer',
        'width'    => 'integer',
        'quantity' => 'integer',
    ];

    // Quan hệ người tạo
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Diện tích tấm (mm²)
    public function getAreaAttribute(): int
    {
        return $this->height * $this->width;
    }

    // Label trạng thái
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'available' => 'Còn hàng',
            'used'      => 'Đã dùng',
            'reserved'  => 'Đã đặt',
            default     => $this->status,
        };
    }
}
