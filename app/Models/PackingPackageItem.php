<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackingPackageItem extends Model
{
    protected $fillable = [
        'packing_package_id',
        'item_code_type',
        'item_code_id',
    ];

    public function package()
    {
        return $this->belongsTo(PackingPackage::class, 'packing_package_id');
    }

    public function itemCode()
    {
        return $this->morphTo(__FUNCTION__, 'item_code_type', 'item_code_id');
    }
}
