<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuGachaSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_owner_can_join_a_menu_sync_room(): void
    {
        $owner = User::factory()->create();
        $menu = $owner->menuGachas()->create(['name' => 'ランチ', 'is_published' => true]);

        $this->get(route('menu-gachas.sync.authorize', ['id' => $menu->id]))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('menu-gachas.sync.authorize', ['id' => $menu->id]))->assertNotFound();
        $this->actingAs($owner)->get(route('menu-gachas.sync.authorize', ['id' => $menu->id]))
            ->assertOk()->assertExactJson(['authorized' => true]);
    }

    public function test_only_the_internal_server_can_load_or_save_a_document(): void
    {
        $user = User::factory()->create();
        $menu = $user->menuGachas()->create(['name' => 'ランチ']);
        $path = "/internal/menu/{$menu->id}/sync";

        $this->actingAs($user)->getJson($path)->assertForbidden();
        $this->postJson($path, [])->assertForbidden();
        $this->withHeader('X-Menu-Sync-Key', config('app.key'))->getJson($path)
            ->assertOk()->assertJsonPath('menu.name', 'ランチ')->assertJsonPath('state', null);
    }

    public function test_sync_saves_the_yjs_state_and_projects_valid_menu_changes(): void
    {
        $user = User::factory()->create();
        $menu = $user->menuGachas()->create(['name' => 'ランチ']);
        $item = $menu->items()->create(['item_name' => 'カレー', 'price' => 800]);
        $flag = $menu->flags()->create(['name' => '学割']);
        $payload = [
            'state' => base64_encode('yjs-update'),
            'menu' => [
                'name' => '夕食', 'isPublished' => true,
                'flags' => [['id' => $flag->id, 'name' => '限定']],
                'items' => [['id' => $item->id, 'name' => 'チキンカレー', 'price' => 900, 'flagIds' => [$flag->id]]],
            ],
        ];

        $this->withHeader('X-Menu-Sync-Key', config('app.key'))
            ->postJson("/internal/menu/{$menu->id}/sync", $payload)
            ->assertOk()->assertExactJson(['saved' => true]);

        $this->assertDatabaseHas('menu_gachas', ['id' => $menu->id, 'name' => '夕食', 'is_published' => true]);
        $this->assertDatabaseHas('menu_gacha_items', ['id' => $item->id, 'item_name' => 'チキンカレー', 'price' => 900]);
        $this->assertDatabaseHas('menu_gacha_flags', ['id' => $flag->id, 'name' => '限定']);
        $this->assertDatabaseHas('menu_gacha_flag_menu_gacha_item', ['menu_gacha_item_id' => $item->id, 'menu_gacha_flag_id' => $flag->id]);
        $this->assertDatabaseHas('menu_gacha_sync_documents', ['menu_gacha_id' => $menu->id, 'state' => $payload['state']]);
    }

    public function test_sync_rejects_foreign_item_ids_without_saving_any_changes(): void
    {
        $owner = User::factory()->create();
        $menu = $owner->menuGachas()->create(['name' => 'ランチ']);
        $otherMenu = $owner->menuGachas()->create(['name' => '夕食']);
        $foreignItem = $otherMenu->items()->create(['item_name' => 'カレー', 'price' => 800]);

        $this->withHeader('X-Menu-Sync-Key', config('app.key'))->postJson("/internal/menu/{$menu->id}/sync", [
            'state' => base64_encode('yjs-update'),
            'menu' => [
                'name' => '変更後', 'isPublished' => false, 'flags' => [],
                'items' => [['id' => $foreignItem->id, 'name' => '侵入', 'price' => 0, 'flagIds' => []]],
            ],
        ])->assertUnprocessable()->assertJsonValidationErrors('menu.items.0.id');

        $this->assertDatabaseHas('menu_gachas', ['id' => $menu->id, 'name' => 'ランチ']);
        $this->assertDatabaseMissing('menu_gacha_sync_documents', ['menu_gacha_id' => $menu->id]);
        $this->assertDatabaseHas('menu_gacha_items', ['id' => $foreignItem->id, 'item_name' => 'カレー']);
    }
}
