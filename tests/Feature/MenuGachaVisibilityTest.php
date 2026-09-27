<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuGachaVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_menu_gacha_is_private_by_default(): void
    {
        $user = User::factory()->create();
        $menuGacha = $user->menuGachas()->create(['name' => '非公開ガチャ']);

        $this->assertDatabaseHas('menu_gachas', ['id' => $menuGacha->getKey(), 'is_published' => false]);
        $this->get(route('menu-gachas.show', ['id' => $menuGacha->getKey()]))->assertNotFound();
    }

    public function test_owner_can_view_private_menu_and_change_its_visibility(): void
    {
        $user = User::factory()->create();
        $menuGacha = $user->menuGachas()->create(['name' => '週末ごはん']);

        $this->actingAs($user)->get(route('menu-gachas.show', ['id' => $menuGacha->getKey()]))
            ->assertSee('公開設定')
            ->assertSee('id="edit-tab"', false);

        $this->patch(route('menu-gachas.update', ['id' => $menuGacha->getKey()]), [
            'name' => '週末ごはん',
            'is_published' => '1',
        ])->assertRedirect(route('menu-gachas.show', ['id' => $menuGacha->getKey(), 'tab' => 'edit']));

        $this->assertDatabaseHas('menu_gachas', ['id' => $menuGacha->getKey(), 'is_published' => true]);

        $this->patch(route('menu-gachas.update', ['id' => $menuGacha->getKey()]), [
            'name' => '週末ごはん',
            'is_published' => '0',
        ])->assertRedirect();

        $this->assertDatabaseHas('menu_gachas', ['id' => $menuGacha->getKey(), 'is_published' => false]);
    }

    public function test_guest_can_only_view_gacha_and_preview_of_published_menu(): void
    {
        $user = User::factory()->create();
        $menuGacha = $user->menuGachas()->create(['name' => '公開ガチャ', 'is_published' => true]);
        $menuGacha->items()->create(['item_name' => 'カレー', 'price' => 850]);

        $this->get(route('menu-gachas.show', ['id' => $menuGacha->getKey(), 'tab' => 'edit']))
            ->assertSee('id="gacha-tab"', false)
            ->assertSee('id="preview-tab"', false)
            ->assertSeeText('カレー')
            ->assertDontSee('id="edit-tab"', false)
            ->assertDontSee('id="edit-panel"', false)
            ->assertDontSee('action="'.route('menu-gachas.update', ['id' => $menuGacha->getKey()]).'"', false)
            ->assertDontSee('ダッシュボードに戻る');
    }

    public function test_non_owner_can_only_view_published_menu_and_cannot_edit_it(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $menuGacha = $owner->menuGachas()->create(['name' => '公開ガチャ', 'is_published' => true]);

        $this->actingAs($otherUser)->get(route('menu-gachas.show', ['id' => $menuGacha->getKey()]))
            ->assertSeeText('公開ガチャ')
            ->assertDontSee('id="edit-tab"', false);

        $this->patch(route('menu-gachas.update', ['id' => $menuGacha->getKey()]), [
            'name' => '改ざん',
            'is_published' => '0',
        ])->assertNotFound();

        $this->assertDatabaseHas('menu_gachas', ['id' => $menuGacha->getKey(), 'name' => '公開ガチャ', 'is_published' => true]);
    }

    public function test_non_owner_cannot_view_private_menu_even_when_authenticated(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $menuGacha = $owner->menuGachas()->create(['name' => '非公開ガチャ']);

        $this->actingAs($otherUser)->get(route('menu-gachas.show', ['id' => $menuGacha->getKey()]))
            ->assertNotFound();
    }

    public function test_unpublishing_revokes_guest_access_to_the_menu(): void
    {
        $user = User::factory()->create();
        $menuGacha = $user->menuGachas()->create(['name' => '公開ガチャ', 'is_published' => true]);

        $this->get(route('menu-gachas.show', ['id' => $menuGacha->getKey()]))->assertSeeText('公開ガチャ');

        $this->actingAs($user)->patch(route('menu-gachas.update', ['id' => $menuGacha->getKey()]), [
            'name' => '公開ガチャ',
            'is_published' => '0',
        ])->assertRedirect();

        $this->app['auth']->guard()->logout();
        $this->get(route('menu-gachas.show', ['id' => $menuGacha->getKey()]))->assertNotFound();
    }

    public function test_guest_cannot_change_published_menu_or_its_items(): void
    {
        $user = User::factory()->create();
        $menuGacha = $user->menuGachas()->create(['name' => '公開ガチャ', 'is_published' => true]);

        $this->patch(route('menu-gachas.update', ['id' => $menuGacha->getKey()]), [
            'name' => '改ざん',
            'is_published' => '0',
        ])->assertRedirect(route('login'));
        $this->post(route('menu-gachas.items.store', ['id' => $menuGacha->getKey()]), [
            'new_item_name' => '不正な食事',
            'new_price' => 100,
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('menu_gachas', ['id' => $menuGacha->getKey(), 'name' => '公開ガチャ', 'is_published' => true]);
        $this->assertDatabaseMissing('menu_gacha_items', ['menu_gacha_id' => $menuGacha->getKey(), 'item_name' => '不正な食事']);
    }

    public function test_invalid_visibility_is_rejected_without_changing_menu(): void
    {
        $user = User::factory()->create();
        $menuGacha = $user->menuGachas()->create(['name' => '非公開ガチャ']);

        $this->actingAs($user)->patch(route('menu-gachas.update', ['id' => $menuGacha->getKey()]), [
            'name' => '変更後',
            'is_published' => 'maybe',
        ])->assertSessionHasErrors('is_published');

        $this->assertDatabaseHas('menu_gachas', ['id' => $menuGacha->getKey(), 'name' => '非公開ガチャ', 'is_published' => false]);
    }
}
