<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WoodBoardPriceGroupPrice extends Model
{
    protected $table = 'wood_board_price_group_prices';

    protected $fillable = [
        'wood_board_price_group_id',
        'wood_board_type_id',
        'name',
        'thickness',
        'price_board',
        'price_m2',
    ];

    public function group()
    {
        return $this->belongsTo(WoodBoardPriceGroup::class, 'wood_board_price_group_id');
    }

    public function type()
    {
        return $this->belongsTo(WoodBoardType::class, 'wood_board_type_id');
    }
}
