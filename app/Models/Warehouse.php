<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = ['name', 'item_name', 'sizes_config'];

    protected $casts = [
        'sizes_config' => 'array',
    ];

    public function records()
    {
        return $this->hasMany(WarehouseRecord::class);
    }
}
