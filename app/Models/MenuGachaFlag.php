<?php

namespace App\Models;

use Database\Factories\MenuGachaFlagFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name'])]
class MenuGachaFlag extends Model
{
    /** @use HasFactory<MenuGachaFlagFactory> */
    use HasFactory;

    public $timestamps = false;

    public function menuGacha(): BelongsTo
    {
        return $this->belongsTo(MenuGacha::class);
    }
}
