<?php

namespace App\Http\Controllers;

use App\Models\MenuGacha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
<<<<<<< HEAD
        $menuGacha = MenuGacha::with('items')->findOrFail($id);
        $isOwner = $request->user() !== null && $menuGacha->user_id === $request->user()->getKey();
=======
        $menuGacha = $request->user()->menuGachas()->with(['items', 'flags'])->findOrFail($id);
>>>>>>> ad32dd0 (テーブルスキーマを作成し、メニュー設定画面に項目フラグの作成・編集・消去をできるようにする)

        abort_unless($isOwner || $menuGacha->is_published, 404);

        return view('menu-gachas.show', ['menuGacha' => $menuGacha, 'isOwner' => $isOwner]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
<<<<<<< HEAD
            'is_published' => ['sometimes', 'required', 'boolean'],
=======
>>>>>>> ad32dd0 (テーブルスキーマを作成し、メニュー設定画面に項目フラグの作成・編集・消去をできるようにする)
            'items' => ['sometimes', 'array'],
            'items.*' => ['array:item_name,price'],
        ]);

        $menuGacha = $request->user()->menuGachas()->findOrFail($id);

        DB::transaction(function () use ($menuGacha, $validated): void {
            $menuGacha->update([
                'name' => $validated['name'],
                'is_published' => $validated['is_published'] ?? $menuGacha->is_published,
            ]);

            foreach ($validated['items'] ?? [] as $itemId => $itemData) {
                $menuGacha->items()->findOrFail($itemId)->update($itemData);
            }
        });

        return redirect()
            ->route('menu-gachas.show', ['id' => $menuGacha->getKey(), 'tab' => 'edit'])
            ->with('message', 'メニューガチャを更新しました。');
    }

    public function storeItem(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'new_item_name' => ['required', 'string', 'max:255'],
            'new_price' => ['required', 'integer', 'min:0'],
        ]);

        $menuGacha = $request->user()->menuGachas()->findOrFail($id);
        $menuGacha->items()->create([
            'item_name' => $validated['new_item_name'],
            'price' => $validated['new_price'],
        ]);

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
