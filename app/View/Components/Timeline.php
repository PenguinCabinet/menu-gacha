<?php

namespace App\View\Components;

use App\Models\MenuGacha;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Timeline extends Component
{
    /**
     * @param  iterable<int, MenuGacha>  $menuGachas
     */
    public function __construct(
        public readonly iterable $menuGachas,
        public readonly bool $showUser = true,
        public readonly string $emptyMessage = 'まだ投稿がありません。',
    ) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.timeline');
    }
}
