<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackingPackage extends Model
{
    protected $fillable = [
        'name',
        'status',
        'packed_by',
    ];

    public function packer()
    {
        return $this->belongsTo(User::class, 'packed_by');
    }

    public function items()
    {
        return $this->hasMany(PackingPackageItem::class);
    }

    public function packagedItems()
    {
        return $this->hasMany(PackingPackageItem::class)->where('is_packaged', true);
    }
}
