<?php

namespace App\Http\Controllers;

use App\Models\MenuGacha;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MenuGachaSyncController extends Controller
{
    public function authorizeConnection(Request $request, int $id): JsonResponse
    {
        $request->user()->menuGachas()->findOrFail($id);

        return response()->json(['authorized' => true]);
    }

    public function load(Request $request, int $id): JsonResponse
    {
        $this->authorizeServer($request);
        $menu = MenuGacha::with(['items.flags', 'flags'])->findOrFail($id);

        return response()->json([
            'state' => DB::table('menu_gacha_sync_documents')->where('menu_gacha_id', $id)->value('state'),
            'menu' => [
                'name' => $menu->name,
                'isPublished' => $menu->is_published,
                'items' => $menu->items->map(fn ($item) => [
                    'id' => $item->getKey(),
                    'name' => $item->item_name,
                    'price' => $item->price,
                    'flagIds' => $item->flags->modelKeys(),
                ])->values(),
                'flags' => $menu->flags->map(fn ($flag) => [
                    'id' => $flag->getKey(),
                    'name' => $flag->name,
                ])->values(),
            ],
        ]);
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $this->authorizeServer($request);
        $menu = MenuGacha::findOrFail($id);
        $validated = Validator::make($request->all(), [
            'state' => ['required', 'string', 'max:4000000'],
            'menu' => ['required', 'array:name,isPublished,items,flags'],
            'menu.name' => ['required', 'string', 'max:255'],
            'menu.isPublished' => ['required', 'boolean'],
            'menu.items' => ['required', 'array'],
            'menu.items.*' => ['required', 'array:id,name,price,flagIds'],
            'menu.items.*.id' => ['required', 'integer', 'distinct', Rule::exists('menu_gacha_items', 'id')->where('menu_gacha_id', $id)],
            'menu.items.*.name' => ['required', 'string', 'max:255'],
            'menu.items.*.price' => ['required', 'integer', 'min:0'],
            'menu.items.*.flagIds' => ['required', 'array'],
            'menu.items.*.flagIds.*' => ['integer', 'distinct', Rule::exists('menu_gacha_flags', 'id')->where('menu_gacha_id', $id)],
            'menu.flags' => ['required', 'array'],
            'menu.flags.*' => ['required', 'array:id,name'],
            'menu.flags.*.id' => ['required', 'integer', 'distinct', Rule::exists('menu_gacha_flags', 'id')->where('menu_gacha_id', $id)],
            'menu.flags.*.name' => ['required', 'string', 'max:255', 'distinct'],
        ])->validate();

        abort_unless(base64_decode($validated['state'], true) !== false, 422);

        DB::transaction(function () use ($menu, $validated): void {
            $data = $validated['menu'];
            $menu->update(['name' => $data['name'], 'is_published' => $data['isPublished']]);

            foreach ($data['flags'] as $flagData) {
                $menu->flags()->findOrFail($flagData['id'])->update(['name' => $flagData['name']]);
            }

            foreach ($data['items'] as $itemData) {
                $item = $menu->items()->findOrFail($itemData['id']);
                $item->update(['item_name' => $itemData['name'], 'price' => $itemData['price']]);
                $item->flags()->sync($itemData['flagIds']);
            }

            DB::table('menu_gacha_sync_documents')->updateOrInsert(
                ['menu_gacha_id' => $menu->getKey()],
                ['state' => $validated['state']]
            );
        });

        return response()->json(['saved' => true]);
    }

    private function authorizeServer(Request $request): void
    {
        abort_unless(
            is_string(config('app.key')) && config('app.key') !== ''
            && hash_equals(config('app.key'), (string) $request->header('X-Menu-Sync-Key')),
            403
        );
    }
}
