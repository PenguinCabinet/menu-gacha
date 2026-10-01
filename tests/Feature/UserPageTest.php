<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_page_is_public_and_displays_the_two_tabs(): void
    {
        $user = User::factory()->create(['name' => 'test-user']);
        $publishedMenuGacha = $user->menuGachas()->create([
            'name' => '公開中のガチャ',
            'is_published' => true,
        ]);
        $user->menuGachas()->create(['name' => '非公開のガチャ']);

        $response = $this->get(route('users.show', ['name' => $user->name]));

        $response->assertOk()
            ->assertSeeText('test-user')
            ->assertSeeText('メニューガチャ')
            ->assertSeeText('タイムライン')
            ->assertSeeText('公開中のガチャ')
            ->assertSee(route('menu-gachas.show', ['id' => $publishedMenuGacha->getKey()]))
            ->assertDontSeeText('非公開のガチャ');
    }

    public function test_user_page_shows_an_empty_state_when_no_menu_gachas_are_published(): void
    {
        $user = User::factory()->create(['name' => 'test-user']);
        $user->menuGachas()->create(['name' => '非公開のガチャ']);

        $this->get(route('users.show', ['name' => $user->name]))
            ->assertOk()
            ->assertSeeText('公開中のメニューガチャはありません。')
            ->assertDontSeeText('非公開のガチャ');
    }

    public function test_user_page_returns_not_found_for_an_unknown_name(): void
    {
        $this->get(route('users.show', ['name' => 'unknown-user']))
            ->assertNotFound();
    }
}
