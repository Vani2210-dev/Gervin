<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WoodBoardType extends Model
{
    protected $table = 'wood_board_types';

    protected $fillable = [
        'name',
        'prefix',
        'display_order',
    ];

    public function prices()
    {
        return $this->hasMany(WoodBoardPrice::class, 'wood_board_type_id');
    }
}
