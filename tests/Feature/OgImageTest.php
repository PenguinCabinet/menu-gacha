<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\OgImageRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OgImageTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p5sAAAAASUVORK5CYII=';

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(OgImageRenderer::class, new class extends OgImageRenderer
        {
            public function render(string $title, string $description, string $eyebrow): string
            {
                return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p5sAAAAASUVORK5CYII=');
            }
        });
    }

    public function test_homepage_has_open_graph_metadata_and_generated_image(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('property="og:title" content="メニューガチャ"', false)
            ->assertSee('property="og:image" content="'.route('og.site').'"', false)
            ->assertSee('property="og:image:type" content="image/png"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false);

        $this->get(route('og.site'))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertContent(base64_decode(self::TEST_PNG));
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
            ->assertHeader('Content-Type', 'image/png')
            ->assertContent(base64_decode(self::TEST_PNG));

        $this->get(route('og.menu', ['id' => $privateMenu->getKey()]))
            ->assertNotFound();

        $this->actingAs($user)
            ->get(route('og.menu', ['id' => $privateMenu->getKey()]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertContent(base64_decode(self::TEST_PNG));
    }

    public function test_user_page_uses_a_profile_specific_og_image(): void
    {
        $user = User::factory()->create(['name' => 'menu-maker']);

        $this->get(route('users.show', ['name' => $user->name]))
            ->assertOk()
            ->assertSee('property="og:image" content="'.route('og.user', ['name' => $user->name]).'"', false);

        $this->get(route('og.user', ['name' => $user->name]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertContent(base64_decode(self::TEST_PNG));
    }
}
