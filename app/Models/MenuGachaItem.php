<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['menu_gacha_id', 'item_name', 'price'])]
class MenuGachaItem extends Model
{
    public $timestamps = false;

    public function flags(): BelongsToMany
    {
        return $this->belongsToMany(MenuGachaFlag::class);
    }

    protected function casts(): array
    {
        return [
            'price' => 'integer',
        ];
    }
}
