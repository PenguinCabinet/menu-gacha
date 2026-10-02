<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_timeline_shows_published_menu_gachas_in_latest_order(): void
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

    public function test_dashboard_timeline_shows_other_users_published_menu_gachas(): void
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

    public function test_home_timeline_paginates_beyond_twenty_items(): void
    {
        $user = User::factory()->create();
        for ($i = 1; $i <= 21; $i++) {
            $user->menuGachas()->create([
                'name' => sprintf('published-menu-%02d', $i),
                'is_published' => true,
            ]);
        }

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('published-menu-21')
            ->assertSeeText('published-menu-02')
            ->assertDontSeeText('published-menu-01');

        $this->get(route('home', ['timeline_page' => 2]))
            ->assertOk()
            ->assertSeeText('published-menu-01')
            ->assertDontSeeText('published-menu-21');
    }

    public function test_home_timeline_shows_pagination_buttons_and_navigates_across_pages(): void
    {
        $user = User::factory()->create();
        for ($i = 1; $i <= 45; $i++) {
            $user->menuGachas()->create([
                'name' => sprintf('paged-menu-%02d', $i),
                'is_published' => true,
            ]);
        }

        $firstPage = $this->get(route('home'));
        $firstPage->assertOk()
            ->assertSeeText('paged-menu-45')
            ->assertSeeText('paged-menu-26')
            ->assertDontSeeText('paged-menu-25')
            ->assertDontSeeText('paged-menu-01')
            ->assertSee('timeline_page=2', false)
            ->assertSee('timeline_page=3', false)
            ->assertDontSee('?page=2', false);

        $secondPage = $this->get(route('home', ['timeline_page' => 2]));
        $secondPage->assertOk()
            ->assertSeeText('paged-menu-25')
            ->assertSeeText('paged-menu-06')
            ->assertDontSeeText('paged-menu-26')
            ->assertDontSeeText('paged-menu-05')
            ->assertSee('timeline_page=1', false)
            ->assertSee('timeline_page=3', false);

        $thirdPage = $this->get(route('home', ['timeline_page' => 3]));
        $thirdPage->assertOk()
            ->assertSeeText('paged-menu-05')
            ->assertSeeText('paged-menu-01')
            ->assertDontSeeText('paged-menu-06')
            ->assertDontSeeText('paged-menu-45')
            ->assertSee('timeline_page=1', false)
            ->assertSee('timeline_page=2', false);
    }

    public function test_home_timeline_hides_pagination_buttons_at_or_below_twenty_items(): void
    {
        $user = User::factory()->create();
        for ($i = 1; $i <= 20; $i++) {
            $user->menuGachas()->create([
                'name' => sprintf('exact-menu-%02d', $i),
                'is_published' => true,
            ]);
        }

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('exact-menu-20')
            ->assertSeeText('exact-menu-01')
            ->assertDontSee('timeline_page=', false);
    }

    public function test_dashboard_timeline_shows_pagination_buttons_and_navigates(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        for ($i = 1; $i <= 21; $i++) {
            $otherUser->menuGachas()->create([
                'name' => sprintf('dashboard-menu-%02d', $i),
                'is_published' => true,
            ]);
        }

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('dashboard-menu-21')
            ->assertDontSeeText('dashboard-menu-01')
            ->assertSee('timeline_page=2', false);

        $this->actingAs($user)->get(route('dashboard', ['timeline_page' => 2]))
            ->assertOk()
            ->assertSeeText('dashboard-menu-01')
            ->assertDontSeeText('dashboard-menu-21')
            ->assertSee('timeline_page=1', false);
    }

    public function test_home_timeline_eager_loads_users_to_avoid_n_plus_one(): void
    {
        $user = User::factory()->create(['name' => 'timeline-user']);
        $user->menuGachas()->create(['name' => '公開ガチャ', 'is_published' => true]);

        $menuGachas = $this->get(route('home'))->viewData('timelineMenuGachas');

        $this->assertTrue($menuGachas->getCollection()->first()->relationLoaded('user'));
    }

    public function test_profile_timeline_does_not_eager_load_unneeded_users(): void
    {
        $user = User::factory()->create(['name' => 'profile-user']);
        $user->menuGachas()->create(['name' => '本人の公開ガチャ', 'is_published' => true]);

        $menuGachas = $this->get(route('users.show', ['name' => $user->name]))->viewData('menuGachas');

        $this->assertFalse($menuGachas->first()->relationLoaded('user'));
    }
}
