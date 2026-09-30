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
            ->assertSee('週末ごはん')
            ->assertSee('作成者：')
            ->assertSee(route('users.show', ['name' => $user->name]), false)
            ->assertSee($user->name);
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

    public function test_page_save_action_updates_menu_items_and_flags_together(): void
    {
        $user = User::factory()->create();
        $menuGacha = $user->menuGachas()->create(['name' => '週末ごはん']);
        $item = $menuGacha->items()->create(['item_name' => 'カレー', 'price' => 850]);
        $flag = $menuGacha->flags()->create(['name' => '学割']);

        $response = $this->actingAs($user)
            ->patch(route('menu-gachas.update', ['id' => $menuGacha->getKey()]), [
                'name' => '平日ごはん',
                'is_published' => '1',
                'items' => [
                    $item->getKey() => ['item_name' => 'チキンカレー', 'price' => 950],
                ],
                'flags' => [
                    $flag->getKey() => ['name' => '平日限定'],
                ],
            ]);

        $response->assertRedirect(route('menu-gachas.show', [
            'id' => $menuGacha->getKey(),
            'tab' => 'edit',
        ]));
        $this->assertDatabaseHas('menu_gachas', [
            'id' => $menuGacha->getKey(),
            'name' => '平日ごはん',
            'is_published' => true,
        ]);
        $this->assertDatabaseHas('menu_gacha_items', [
            'id' => $item->getKey(),
            'item_name' => 'チキンカレー',
            'price' => 950,
        ]);
        $this->assertDatabaseHas('menu_gacha_flags', [
            'id' => $flag->getKey(),
            'name' => '平日限定',
        ]);
    }

    public function test_edit_tab_has_one_fixed_save_button_for_the_edit_form(): void
    {
        $user = User::factory()->create();
        $menuGacha = $user->menuGachas()->create(['name' => '週末ごはん']);
        $menuGacha->flags()->create(['name' => '学割']);

        $response = $this->actingAs($user)->get(route('menu-gachas.show', [
            'id' => $menuGacha->getKey(),
            'tab' => 'edit',
        ]));

        $response->assertOk()
            ->assertSee('position-fixed bottom-0 end-0', false)
            ->assertSee('name="flags['.$menuGacha->flags()->first()->getKey().'][name]" form="menu-gacha-edit-form"', false);
        $this->assertSame(1, substr_count($response->getContent(), '>保存</button>'));
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

    public function test_item_edit_screen_shows_flag_checkboxes_and_selected_values(): void
    {
        $user = User::factory()->create();
        $menu = $user->menuGachas()->create(['name' => 'ランチ']);
        $item = $menu->items()->create(['item_name' => 'カレー', 'price' => 800]);
        $discount = $menu->flags()->create(['name' => '学割']);
        $limited = $menu->flags()->create(['name' => '期間限定']);
        $item->flags()->attach($discount);

        $response = $this->actingAs($user)->get(route('menu-gachas.show', ['id' => $menu->id, 'tab' => 'edit']));

        $response
            ->assertSee('items['.$item->id.'][flag_ids][]', false)
            ->assertSee('item-'.$item->id.'-flag-'.$discount->id, false)
            ->assertSee('item-'.$item->id.'-flag-'.$limited->id, false);
        $this->assertMatchesRegularExpression('/id="item-'.$item->id.'-flag-'.$discount->id.'"[^>]*checked/s', $response->getContent());
        $this->assertDoesNotMatchRegularExpression('/id="item-'.$item->id.'-flag-'.$limited->id.'"[^>]*checked/s', $response->getContent());
    }

    public function test_preview_shows_only_flags_attached_to_each_item(): void
    {
        $user = User::factory()->create();
        $menu = $user->menuGachas()->create(['name' => 'ランチ', 'is_published' => true]);
        $curry = $menu->items()->create(['item_name' => 'カレー', 'price' => 800]);
        $udon = $menu->items()->create(['item_name' => 'うどん', 'price' => 500]);
        $menu->items()->create(['item_name' => 'サラダ', 'price' => 300]);
        $discount = $menu->flags()->create(['name' => '学割']);
        $limited = $menu->flags()->create(['name' => '期間限定']);
        $menu->flags()->create(['name' => '未使用']);
        $curry->flags()->attach([$discount->id, $limited->id]);
        $udon->flags()->attach($limited);

        $response = $this->get(route('menu-gachas.show', ['id' => $menu->id]));

        $response->assertOk()
            ->assertSeeInOrder(['カレー', '学割', '期間限定', '800円', 'うどん', '期間限定', '500円', 'サラダ', '300円']);
        $this->assertSame(1, substr_count($response->getContent(), '>学割</span>'));
        $this->assertSame(2, substr_count($response->getContent(), '>期間限定</span>'));
        $this->assertSame(0, substr_count($response->getContent(), '>未使用</span>'));
    }

    public function test_preview_escapes_flag_names(): void
    {
        $user = User::factory()->create();
        $menu = $user->menuGachas()->create(['name' => 'ランチ', 'is_published' => true]);
        $item = $menu->items()->create(['item_name' => 'カレー', 'price' => 800]);
        $flag = $menu->flags()->create(['name' => '<script>alert(1)</script>']);
        $item->flags()->attach($flag);

        $this->get(route('menu-gachas.show', ['id' => $menu->id]))
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_gacha_displays_menu_flags_and_serializes_item_assignments_for_filtering(): void
    {
        $user = User::factory()->create();
        $menu = $user->menuGachas()->create(['name' => 'ランチ', 'is_published' => true]);
        $item = $menu->items()->create(['item_name' => 'カレー', 'price' => 800]);
        $flag = $menu->flags()->create(['name' => '学割']);
        $item->flags()->attach($flag);

        $this->get(route('menu-gachas.show', ['id' => $menu->id]))
            ->assertSee('id="gacha-flags"', false)
            ->assertSee('id="gacha-flag-'.$flag->id.'" value="'.$flag->id.'" checked', false)
            ->assertSee('学割')
            ->assertSee('"flagIds":['.$flag->id.']', false);
    }

    public function test_gacha_has_no_flag_checkboxes_when_menu_has_no_flags(): void
    {
        $user = User::factory()->create();
        $menu = $user->menuGachas()->create(['name' => 'ランチ', 'is_published' => true]);

        $this->get(route('menu-gachas.show', ['id' => $menu->id]))
            ->assertDontSee('id="gacha-flags"', false)
            ->assertSee('ガチャを回す');
    }

    public function test_item_flags_can_be_selected_and_all_cleared_with_the_edit_form(): void
    {
        $user = User::factory()->create();
        $menu = $user->menuGachas()->create(['name' => 'ランチ']);
        $item = $menu->items()->create(['item_name' => 'カレー', 'price' => 800]);
        $discount = $menu->flags()->create(['name' => '学割']);
        $limited = $menu->flags()->create(['name' => '期間限定']);

        $this->actingAs($user)->patch(route('menu-gachas.update', ['id' => $menu->id]), [
            'name' => 'ランチ',
            'items' => [$item->id => ['item_name' => 'カレー', 'price' => 800, 'flag_ids' => [$discount->id, $limited->id]]],
        ])->assertRedirect(route('menu-gachas.show', ['id' => $menu->id, 'tab' => 'edit']));

        $this->assertDatabaseHas('menu_gacha_flag_menu_gacha_item', ['menu_gacha_item_id' => $item->id, 'menu_gacha_flag_id' => $discount->id]);
        $this->assertDatabaseHas('menu_gacha_flag_menu_gacha_item', ['menu_gacha_item_id' => $item->id, 'menu_gacha_flag_id' => $limited->id]);

        $this->patch(route('menu-gachas.update', ['id' => $menu->id]), [
            'name' => 'ランチ',
            'items' => [$item->id => ['item_name' => 'カレー', 'price' => 800]],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('menu_gacha_flag_menu_gacha_item', 0);
    }

    public function test_new_item_can_be_created_with_multiple_flags(): void
    {
        $user = User::factory()->create();
        $menu = $user->menuGachas()->create(['name' => 'ランチ']);
        $discount = $menu->flags()->create(['name' => '学割']);
        $limited = $menu->flags()->create(['name' => '期間限定']);

        $this->actingAs($user)->post(route('menu-gachas.items.store', ['id' => $menu->id]), [
            'new_item_name' => 'カレー', 'new_price' => 800,
            'new_flag_ids' => [$discount->id, $limited->id],
        ])->assertRedirect(route('menu-gachas.show', ['id' => $menu->id, 'tab' => 'edit']));

        $item = $menu->items()->firstOrFail();
        $this->assertDatabaseHas('menu_gacha_flag_menu_gacha_item', ['menu_gacha_item_id' => $item->id, 'menu_gacha_flag_id' => $discount->id]);
        $this->assertDatabaseHas('menu_gacha_flag_menu_gacha_item', ['menu_gacha_item_id' => $item->id, 'menu_gacha_flag_id' => $limited->id]);
    }

    public function test_flags_from_another_menu_are_rejected_without_updating_the_item(): void
    {
        $user = User::factory()->create();
        $menu = $user->menuGachas()->create(['name' => 'ランチ']);
        $item = $menu->items()->create(['item_name' => 'カレー', 'price' => 800]);
        $otherMenu = $user->menuGachas()->create(['name' => 'ディナー']);
        $foreignFlag = $otherMenu->flags()->create(['name' => '学割']);

        $this->actingAs($user)->patch(route('menu-gachas.update', ['id' => $menu->id]), [
            'name' => '変更後',
            'items' => [$item->id => ['item_name' => '変更後', 'price' => 900, 'flag_ids' => [$foreignFlag->id]]],
        ])->assertSessionHasErrors('items.'.$item->id.'.flag_ids.0');

        $this->post(route('menu-gachas.items.store', ['id' => $menu->id]), [
            'new_item_name' => 'うどん', 'new_price' => 500, 'new_flag_ids' => [$foreignFlag->id],
        ])->assertSessionHasErrors('new_flag_ids.0');

        $this->assertDatabaseHas('menu_gacha_items', ['id' => $item->id, 'item_name' => 'カレー', 'price' => 800]);
        $this->assertDatabaseCount('menu_gacha_items', 1);
        $this->assertDatabaseCount('menu_gacha_flag_menu_gacha_item', 0);
    }

    public function test_deleting_an_item_or_flag_removes_its_assignments(): void
    {
        $user = User::factory()->create();
        $menu = $user->menuGachas()->create(['name' => 'ランチ']);
        $firstItem = $menu->items()->create(['item_name' => 'カレー', 'price' => 800]);
        $secondItem = $menu->items()->create(['item_name' => 'うどん', 'price' => 500]);
        $flag = $menu->flags()->create(['name' => '学割']);
        $firstItem->flags()->attach($flag);
        $secondItem->flags()->attach($flag);

        $this->actingAs($user)->delete(route('menu-gachas.items.destroy', ['id' => $menu->id, 'itemId' => $firstItem->id]))
            ->assertRedirect();
        $this->assertDatabaseCount('menu_gacha_flag_menu_gacha_item', 1);

        $this->delete(route('menu-gachas.flags.destroy', ['id' => $menu->id, 'flagId' => $flag->id]))
            ->assertRedirect();
        $this->assertDatabaseCount('menu_gacha_flag_menu_gacha_item', 0);
    }
}
