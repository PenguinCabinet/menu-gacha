<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuGachaController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $menuGacha = $request->user()->menuGachas()->create([
            'name' => '新しいメニューガチャ',
        ]);

        return redirect()->route('menu-gachas.show', ['id' => $menuGacha->getKey()]);
    }

    public function show(Request $request, int $id): View
    {
        $menuGacha = $request->user()->menuGachas()->findOrFail($id);

        return view('menu-gachas.show', ['menuGacha' => $menuGacha]);
    }
}
