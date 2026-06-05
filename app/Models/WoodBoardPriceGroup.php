<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WoodBoardPriceGroup extends Model
{
    protected $table = 'wood_board_price_groups';

    protected $fillable = [
        'name',
    ];

    public function prices()
    {
        return $this->hasMany(WoodBoardPriceGroupPrice::class, 'wood_board_price_group_id');
    }
}
