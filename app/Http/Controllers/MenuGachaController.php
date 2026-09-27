<?php

namespace App\Http\Controllers;

use App\Models\MenuGacha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
        $menuGacha = MenuGacha::with(['items.flags', 'flags'])->findOrFail($id);
        $isOwner = $request->user() !== null && $menuGacha->user_id === $request->user()->getKey();

        abort_unless($isOwner || $menuGacha->is_published, 404);

        return view('menu-gachas.show', ['menuGacha' => $menuGacha, 'isOwner' => $isOwner]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $menuGacha = $request->user()->menuGachas()->findOrFail($id);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_published' => ['sometimes', 'required', 'boolean'],
            'items' => ['sometimes', 'array'],
            'items.*' => ['array:item_name,price,flag_ids'],
            'items.*.flag_ids' => ['sometimes', 'array'],
            'items.*.flag_ids.*' => ['integer', 'distinct', Rule::exists('menu_gacha_flags', 'id')->where('menu_gacha_id', $menuGacha->getKey())],
        ]);

        DB::transaction(function () use ($menuGacha, $validated): void {
            $menuGacha->update([
                'name' => $validated['name'],
                'is_published' => $validated['is_published'] ?? $menuGacha->is_published,
            ]);

            foreach ($validated['items'] ?? [] as $itemId => $itemData) {
                $item = $menuGacha->items()->findOrFail($itemId);
                $item->update(collect($itemData)->only(['item_name', 'price'])->all());
                $item->flags()->sync($itemData['flag_ids'] ?? []);
            }
        });

        return redirect()
            ->route('menu-gachas.show', ['id' => $menuGacha->getKey(), 'tab' => 'edit'])
            ->with('message', 'メニューガチャを更新しました。');
    }

    public function storeItem(Request $request, int $id): RedirectResponse
    {
        $menuGacha = $request->user()->menuGachas()->findOrFail($id);
        $validated = $request->validate([
            'new_item_name' => ['required', 'string', 'max:255'],
            'new_price' => ['required', 'integer', 'min:0'],
            'new_flag_ids' => ['sometimes', 'array'],
            'new_flag_ids.*' => ['integer', 'distinct', Rule::exists('menu_gacha_flags', 'id')->where('menu_gacha_id', $menuGacha->getKey())],
        ]);

        DB::transaction(function () use ($menuGacha, $validated): void {
            $item = $menuGacha->items()->create([
                'item_name' => $validated['new_item_name'],
                'price' => $validated['new_price'],
            ]);
            $item->flags()->sync($validated['new_flag_ids'] ?? []);
        });

        return redirect()
            ->route('menu-gachas.show', ['id' => $menuGacha->getKey(), 'tab' => 'edit'])
            ->with('message', '食事を追加しました。');
    }

    public function destroyItem(Request $request, int $id, int $itemId): RedirectResponse
    {
        $menuGacha = $request->user()->menuGachas()->findOrFail($id);
        $menuGacha->items()->findOrFail($itemId)->delete();

        return redirect()
            ->route('menu-gachas.show', ['id' => $menuGacha->getKey(), 'tab' => 'edit'])
            ->with('message', '食事を削除しました。');
    }
}
