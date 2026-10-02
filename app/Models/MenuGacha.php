<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'is_published'])]
class MenuGacha extends Model
{
    public const UPDATED_AT = null;

    /**
     * @param  Builder<MenuGacha>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * @param  Builder<MenuGacha>  $query
     */
    #[Scope]
    protected function forTimeline(Builder $query): void
    {
        $query->published()->latest('created_at')->latest('id');
    }

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuGachaItem::class);
    }

    public function flags(): HasMany
    {
        return $this->hasMany(MenuGachaFlag::class);
    }
}
