<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['menu_gacha_id', 'item_name', 'price'])]
class MenuGachaItem extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'price' => 'integer',
        ];
    }
}
