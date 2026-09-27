<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMenuGachasTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_lists_only_the_authenticated_users_menu_gachas(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownMenuGacha = $user->menuGachas()->create(['name' => '自分のガチャ']);
        $otherUser->menuGachas()->create(['name' => '他の人のガチャ']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSeeText('自分のガチャ')
            ->assertDontSeeText('他の人のガチャ')
            ->assertSee(route('menu-gachas.show', ['id' => $ownMenuGacha->getKey()]))
            ->assertSee(route('users.show', ['name' => $user->name]));
    }

    public function test_dashboard_shows_an_empty_state_when_the_user_has_no_menu_gachas(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

        $response->assertOk()
            ->assertSeeText('メニューガチャを作成してみましょう。');
    }
}
