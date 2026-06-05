<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackingPackageItem extends Model
{
    protected $fillable = [
        'packing_package_id',
        'item_code_type',
        'item_code_id',
        'is_packaged',
    ];

    protected $casts = [
        'item_code_id' => 'string',
        'is_packaged' => 'boolean',
    ];

    public function package()
    {
        return $this->belongsTo(PackingPackage::class, 'packing_package_id');
    }

    public function itemCode()
    {
        // item_code_id đang lưu theo product_id nên phải tra qua cột product_id của bảng gốc.
        return $this->morphTo(__FUNCTION__, 'item_code_type', 'item_code_id', 'product_id');
    }
}
