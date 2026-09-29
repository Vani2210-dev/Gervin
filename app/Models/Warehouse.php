<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'code',
        'address',
        'manager',
        'status',
        'description',
        'item_name',
        'sizes_config',
    ];

    protected $casts = [
        'sizes_config' => 'array',
    ];

    public function records(): HasMany
    {
        return $this->hasMany(WarehouseRecord::class);
    }

    public function woodBoards(): HasMany
    {
        return $this->hasMany(WoodBoard::class);
    }

    public function materials(): HasMany
    {
        return $this->woodBoards();
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(InventoryReceipt::class)->orderBy('date', 'desc')->orderBy('id', 'desc');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(InventoryIssue::class)->orderBy('date', 'desc')->orderBy('id', 'desc');
    }

    public function stocktakes(): HasMany
    {
        return $this->hasMany(InventoryStocktake::class)->orderBy('date', 'desc')->orderBy('id', 'desc');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class)->orderBy('date', 'desc')->orderBy('id', 'desc');
    }
}
