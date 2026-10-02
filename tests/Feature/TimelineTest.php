<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_timeline_shows_all_published_menu_gachas_in_latest_order(): void
    {
        $oldUser = User::factory()->create(['name' => 'old-user']);
        $newUser = User::factory()->create(['name' => 'new-user']);
        $oldMenu = $oldUser->menuGachas()->create([
            'name' => '古い公開ガチャ',
            'is_published' => true,
        ]);
        $newMenu = $newUser->menuGachas()->create([
            'name' => '新しい公開ガチャ',
            'is_published' => true,
        ]);
        $oldUser->menuGachas()->create(['name' => '非公開ガチャ']);

        $oldMenu->forceFill(['created_at' => now()->subDay()])->save();
        $newMenu->forceFill(['created_at' => now()])->save();

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSeeText('新着のメニューガチャ')
            ->assertSeeText('古い公開ガチャ')
            ->assertSeeText('新しい公開ガチャ')
            ->assertDontSeeText('非公開ガチャ')
            ->assertSee(route('menu-gachas.show', ['id' => $oldMenu->getKey()]))
            ->assertSee(route('menu-gachas.show', ['id' => $newMenu->getKey()]))
            ->assertSee(route('users.show', ['name' => 'old-user']))
            ->assertSee(route('users.show', ['name' => 'new-user']))
            ->assertSeeInOrder(['新しい公開ガチャ', '古い公開ガチャ']);
    }

    public function test_home_timeline_shows_empty_state_when_no_published_menu_exists(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('まだ公開されたメニューガチャがありません。');
    }

    public function test_dashboard_timeline_shows_all_users_published_menu_gachas(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create(['name' => 'other-user']);
        $ownMenu = $user->menuGachas()->create(['name' => '自分の公開ガチャ', 'is_published' => true]);
        $otherMenu = $otherUser->menuGachas()->create(['name' => '他人の公開ガチャ', 'is_published' => true]);
        $otherUser->menuGachas()->create(['name' => '他人の非公開ガチャ']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee(route('menu-gachas.show', ['id' => $ownMenu->getKey()]))
            ->assertSee(route('menu-gachas.show', ['id' => $otherMenu->getKey()]))
            ->assertSeeText('他人の公開ガチャ')
            ->assertSee(route('users.show', ['name' => 'other-user']))
            ->assertDontSeeText('他人の非公開ガチャ');
    }

    public function test_profile_timeline_shows_only_the_users_published_menu_gachas(): void
    {
        $user = User::factory()->create(['name' => 'profile-user']);
        $otherUser = User::factory()->create(['name' => 'other-user']);
        $ownMenu = $user->menuGachas()->create(['name' => '本人の公開ガチャ', 'is_published' => true]);
        $user->menuGachas()->create(['name' => '本人の非公開ガチャ']);
        $otherMenu = $otherUser->menuGachas()->create(['name' => '他人の公開ガチャ', 'is_published' => true]);

        $response = $this->get(route('users.show', ['name' => $user->name]));

        $response->assertOk()
            ->assertSee(route('menu-gachas.show', ['id' => $ownMenu->getKey()]))
            ->assertSeeText('本人の公開ガチャ')
            ->assertDontSeeText('本人の非公開ガチャ')
            ->assertDontSeeText('他人の公開ガチャ')
            ->assertDontSee(route('menu-gachas.show', ['id' => $otherMenu->getKey()]));
    }
}
