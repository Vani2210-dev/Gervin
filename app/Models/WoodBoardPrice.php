<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WoodBoardPrice extends Model
{
    protected $table = 'wood_board_prices';

    protected $fillable = [
        'wood_board_id',
        'wood_board_type_id',
        'code',
        'name',
        'foil_name',
        'thickness',
        'price_board',
        'price_m2',
        'stock',
    ];

    protected $casts = [
        'price_board' => 'double',
        'price_m2'    => 'double',
        'stock'       => 'double',
    ];

    public function board()
    {
        return $this->belongsTo(WoodBoard::class, 'wood_board_id');
    }

    public function type()
    {
        return $this->belongsTo(WoodBoardType::class, 'wood_board_type_id');
    }

    public function receiptItems()
    {
        return $this->hasMany(InventoryReceiptItem::class, 'wood_board_price_id');
    }

    public function issueItems()
    {
        return $this->hasMany(InventoryIssueItem::class, 'wood_board_price_id');
    }

    public function stocktakeItems()
    {
        return $this->hasMany(InventoryStocktakeItem::class, 'wood_board_price_id');
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'wood_board_price_id')->orderBy('date', 'desc')->orderBy('id', 'desc');
    }

    public function getDisplayNameAttribute(): string
    {
        $typeName = $this->type?->name ?? '';
        $color = $this->board?->color_code ?? '';
        return "{$this->code} - {$this->name} ({$typeName})" . ($color ? " [Màu {$color}]" : '');
    }
}
