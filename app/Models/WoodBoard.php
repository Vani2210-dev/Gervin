<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WoodBoard extends Model
{
    protected $table = 'wood_boards';

    protected $fillable = [
        'price_group',
        'color_code',
    ];

    public function prices()
    {
        return $this->hasMany(WoodBoardPrice::class, 'wood_board_id');
    }
}
