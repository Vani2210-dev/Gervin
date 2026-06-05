<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackingPackage extends Model
{
    protected $fillable = [
        'name',
        'status',
        'packed_by',
        'dispatched_at',
        'dispatched_note',
        'delivered_at', // Thời điểm giao hàng thành công
        'delivered_note', // Ghi chú giao hàng
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'delivered_at' => 'datetime', // Cast trường thời gian giao hàng sang kiểu Carbon datetime
    ];

    public function packer()
    {
        return $this->belongsTo(User::class, 'packed_by');
    }

    public function items()
    {
        // Giữ thứ tự ổn định theo bản ghi được tạo trước.
        return $this->hasMany(PackingPackageItem::class)->orderBy('id', 'asc');
    }

    public function packagedItems()
    {
        // Nhóm item đã quét cũng theo thứ tự tăng dần của bản ghi.
        return $this->hasMany(PackingPackageItem::class)
            ->where('is_packaged', true)
            ->orderBy('id', 'asc');
    }
}
