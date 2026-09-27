<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuGachaPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_gacha_page_has_preview_and_edit_tabs(): void
    {
        $user = User::factory()->create();
        $menuGacha = $user->menuGachas()->create(['name' => '週末ごはん']);

        $response = $this->actingAs($user)
            ->get(route('menu-gachas.show', ['id' => $menuGacha->getKey()]));

        $response->assertOk()
            ->assertSee('ガチャ')
            ->assertSee('ガチャを回す')
            ->assertSee('プレビュー')
            ->assertSee('編集')
            ->assertSee('週末ごはん');
    }

    public function test_menu_gacha_name_can_be_updated_from_the_edit_tab(): void
    {
        $user = User::factory()->create();
        $menuGacha = $user->menuGachas()->create(['name' => '週末ごはん']);

        $response = $this->actingAs($user)
            ->patch(route('menu-gachas.update', ['id' => $menuGacha->getKey()]), [
                'name' => '平日ごはん',
            ]);

        $response->assertRedirect(route('menu-gachas.show', [
            'id' => $menuGacha->getKey(),
            'tab' => 'edit',
        ]));
        $this->assertDatabaseHas('menu_gachas', [
            'id' => $menuGacha->getKey(),
            'name' => '平日ごはん',
        ]);
    }

    public function test_meal_can_be_added_to_a_menu_gacha(): void
    {
        $user = User::factory()->create();
        $menuGacha = $user->menuGachas()->create(['name' => '週末ごはん']);

        $response = $this->actingAs($user)
            ->post(route('menu-gachas.items.store', ['id' => $menuGacha->getKey()]), [
                'new_item_name' => 'カレー',
                'new_price' => 850,
            ]);

        $response->assertRedirect(route('menu-gachas.show', [
            'id' => $menuGacha->getKey(),
            'tab' => 'edit',
        ]));
        $this->assertDatabaseHas('menu_gacha_items', [
            'menu_gacha_id' => $menuGacha->getKey(),
            'item_name' => 'カレー',
            'price' => 850,
        ]);
    }

    public function test_meal_name_and_price_can_be_updated_with_the_page_save_action(): void
    {
        $user = User::factory()->create();
        $menuGacha = $user->menuGachas()->create(['name' => '週末ごはん']);
        $item = $menuGacha->items()->create([
            'item_name' => 'カレー',
            'price' => 850,
        ]);

        $response = $this->actingAs($user)
            ->patch(route('menu-gachas.update', ['id' => $menuGacha->getKey()]), [
                'name' => '週末ごはん',
                'items' => [
                    $item->getKey() => [
                        'item_name' => 'チキンカレー',
                        'price' => 950,
                    ],
                ],
            ]);

        $response->assertRedirect(route('menu-gachas.show', [
            'id' => $menuGacha->getKey(),
            'tab' => 'edit',
        ]));
        $this->assertDatabaseHas('menu_gacha_items', [
            'id' => $item->getKey(),
            'menu_gacha_id' => $menuGacha->getKey(),
            'item_name' => 'チキンカレー',
            'price' => 950,
        ]);
    }

    public function test_meal_can_be_deleted_from_its_menu_gacha(): void
    {
        $user = User::factory()->create();
        $menuGacha = $user->menuGachas()->create(['name' => '週末ごはん']);
        $item = $menuGacha->items()->create([
            'item_name' => 'カレー',
            'price' => 850,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('menu-gachas.items.destroy', [
                'id' => $menuGacha->getKey(),
                'itemId' => $item->getKey(),
            ]));

        $response->assertRedirect(route('menu-gachas.show', [
            'id' => $menuGacha->getKey(),
            'tab' => 'edit',
        ]));
        $this->assertDatabaseMissing('menu_gacha_items', ['id' => $item->getKey()]);
    }
}
