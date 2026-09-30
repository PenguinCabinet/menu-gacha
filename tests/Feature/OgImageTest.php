<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OgImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_has_open_graph_metadata_and_generated_image(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('property="og:title" content="メニューガチャ"', false)
            ->assertSee('property="og:image" content="'.route('og.site').'"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false);

        $this->get(route('og.site'))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml; charset=UTF-8')
            ->assertSee('<svg', false)
            ->assertSee('メニューガチャ');
    }

    public function test_menu_page_uses_a_dynamic_og_image_and_hides_unpublished_images(): void
    {
        $user = User::factory()->create();
        $publishedMenu = $user->menuGachas()->create(['name' => 'ランチメニュー', 'is_published' => true]);
        $privateMenu = $user->menuGachas()->create(['name' => '非公開メニュー']);

        $this->get(route('menu-gachas.show', ['id' => $publishedMenu->getKey()]))
            ->assertOk()
            ->assertSee('property="og:image" content="'.route('og.menu', ['id' => $publishedMenu->getKey()]).'"', false);

        $this->get(route('og.menu', ['id' => $publishedMenu->getKey()]))
            ->assertOk()
            ->assertSee('ランチメニュー');

        $this->get(route('og.menu', ['id' => $privateMenu->getKey()]))
            ->assertNotFound();

        $this->actingAs($user)
            ->get(route('og.menu', ['id' => $privateMenu->getKey()]))
            ->assertOk()
            ->assertSee('非公開メニュー');
    }

    public function test_user_page_uses_a_profile_specific_og_image(): void
    {
        $user = User::factory()->create(['name' => 'menu-maker']);

        $this->get(route('users.show', ['name' => $user->name]))
            ->assertOk()
            ->assertSee('property="og:image" content="'.route('og.user', ['name' => $user->name]).'"', false);

        $this->get(route('og.user', ['name' => $user->name]))
            ->assertOk()
            ->assertSee('menu-maker');
    }
}
