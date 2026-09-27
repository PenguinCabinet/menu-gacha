<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuGachaFlagController extends Controller
{
    public function store(Request $request, int $id): RedirectResponse
    {
        $menuGacha = $request->user()->menuGachas()->findOrFail($id);
        $validated = $request->validate([
            'new_flag_name' => [
                'required', 'string', 'max:255',
                Rule::unique('menu_gacha_flags', 'name')->where('menu_gacha_id', $menuGacha->getKey()),
            ],
        ]);

        $menuGacha->flags()->create(['name' => $validated['new_flag_name']]);

        return redirect()
            ->route('menu-gachas.show', ['id' => $menuGacha->getKey(), 'tab' => 'edit'])
            ->with('message', 'フラグを追加しました。');
    }

    public function update(Request $request, int $id, int $flagId): RedirectResponse
    {
        $menuGacha = $request->user()->menuGachas()->findOrFail($id);
        $flag = $menuGacha->flags()->findOrFail($flagId);
        $validated = $request->validate([
            'flag_name' => [
                'required', 'string', 'max:255',
                Rule::unique('menu_gacha_flags', 'name')->where('menu_gacha_id', $menuGacha->getKey())->ignore($flag->getKey()),
            ],
        ]);

        $flag->update(['name' => $validated['flag_name']]);

        return redirect()
            ->route('menu-gachas.show', ['id' => $menuGacha->getKey(), 'tab' => 'edit'])
            ->with('message', 'フラグを更新しました。');
    }

    public function destroy(Request $request, int $id, int $flagId): RedirectResponse
    {
        $menuGacha = $request->user()->menuGachas()->findOrFail($id);
        $menuGacha->flags()->findOrFail($flagId)->delete();

        return redirect()
            ->route('menu-gachas.show', ['id' => $menuGacha->getKey(), 'tab' => 'edit'])
            ->with('message', 'フラグを削除しました。');
    }
}
