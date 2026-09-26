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

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $menuGacha = $request->user()->menuGachas()->findOrFail($id);
        $menuGacha->update($validated);

        return redirect()
            ->route('menu-gachas.show', ['id' => $menuGacha->getKey()])
            ->with('message', 'メニューガチャを更新しました。');
    }
}
