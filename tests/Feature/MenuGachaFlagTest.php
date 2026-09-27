<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuGachaFlagTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_and_see_a_flag(): void
    {
        $user = User::factory()->create();
        $menu = $user->menuGachas()->create(['name' => 'ランチ']);

        $this->actingAs($user)->post(route('menu-gachas.flags.store', ['id' => $menu->id]), [
            'new_flag_name' => '学割',
        ])->assertRedirect(route('menu-gachas.show', ['id' => $menu->id, 'tab' => 'edit']));

        $this->assertDatabaseHas('menu_gacha_flags', ['menu_gacha_id' => $menu->id, 'name' => '学割']);
        $this->get(route('menu-gachas.show', ['id' => $menu->id]))->assertSee('学割');
    }

    public function test_owner_can_rename_and_delete_a_flag(): void
    {
        $user = User::factory()->create();
        $menu = $user->menuGachas()->create(['name' => 'ランチ']);
        $flag = $menu->flags()->create(['name' => '学割']);

        $this->actingAs($user)->patch(route('menu-gachas.flags.update', ['id' => $menu->id, 'flagId' => $flag->id]), [
            'flag_name' => '平日限定',
        ])->assertRedirect(route('menu-gachas.show', ['id' => $menu->id, 'tab' => 'edit']));
        $this->assertDatabaseHas('menu_gacha_flags', ['id' => $flag->id, 'name' => '平日限定']);

        $this->delete(route('menu-gachas.flags.destroy', ['id' => $menu->id, 'flagId' => $flag->id]))
            ->assertRedirect(route('menu-gachas.show', ['id' => $menu->id, 'tab' => 'edit']));
        $this->assertDatabaseMissing('menu_gacha_flags', ['id' => $flag->id]);
    }

    public function test_names_must_be_present_and_unique_within_each_menu(): void
    {
        $user = User::factory()->create();
        $menu = $user->menuGachas()->create(['name' => 'ランチ']);
        $otherMenu = $user->menuGachas()->create(['name' => 'ディナー']);
        $flag = $menu->flags()->create(['name' => '学割']);
        $menu->flags()->create(['name' => '平日限定']);

        $this->actingAs($user)->post(route('menu-gachas.flags.store', ['id' => $menu->id]), ['new_flag_name' => ''])
            ->assertSessionHasErrors('new_flag_name');
        $this->post(route('menu-gachas.flags.store', ['id' => $menu->id]), ['new_flag_name' => '学割'])
            ->assertSessionHasErrors('new_flag_name');
        $this->patch(route('menu-gachas.flags.update', ['id' => $menu->id, 'flagId' => $flag->id]), ['flag_name' => '平日限定'])
            ->assertSessionHasErrors('flag_name');
        $this->assertDatabaseHas('menu_gacha_flags', ['id' => $flag->id, 'name' => '学割']);

        $this->post(route('menu-gachas.flags.store', ['id' => $otherMenu->id]), ['new_flag_name' => '学割'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseCount('menu_gacha_flags', 3);
    }

    public function test_other_users_and_other_menus_cannot_change_a_flag(): void
    {
        $owner = User::factory()->create();
        $menu = $owner->menuGachas()->create(['name' => 'ランチ']);
        $flag = $menu->flags()->create(['name' => '学割']);
        $otherMenu = $owner->menuGachas()->create(['name' => 'ディナー']);
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)->get(route('menu-gachas.show', ['id' => $menu->id]))->assertNotFound();
        $this->post(route('menu-gachas.flags.store', ['id' => $menu->id]), ['new_flag_name' => '夜限定'])->assertNotFound();
        $this->patch(route('menu-gachas.flags.update', ['id' => $menu->id, 'flagId' => $flag->id]), ['flag_name' => '夜限定'])->assertNotFound();
        $this->delete(route('menu-gachas.flags.destroy', ['id' => $menu->id, 'flagId' => $flag->id]))->assertNotFound();

        $this->actingAs($owner)->patch(route('menu-gachas.flags.update', ['id' => $otherMenu->id, 'flagId' => $flag->id]), ['flag_name' => '夜限定'])->assertNotFound();
        $this->delete(route('menu-gachas.flags.destroy', ['id' => $otherMenu->id, 'flagId' => $flag->id]))->assertNotFound();
        $this->assertDatabaseHas('menu_gacha_flags', ['id' => $flag->id, 'name' => '学割']);
    }

    public function test_guests_cannot_manage_flags(): void
    {
        $user = User::factory()->create();
        $menu = $user->menuGachas()->create(['name' => 'ランチ']);
        $flag = $menu->flags()->create(['name' => '学割']);

        $this->post(route('menu-gachas.flags.store', ['id' => $menu->id]), ['new_flag_name' => '夜限定'])->assertRedirect(route('login'));
        $this->patch(route('menu-gachas.flags.update', ['id' => $menu->id, 'flagId' => $flag->id]), ['flag_name' => '夜限定'])->assertRedirect(route('login'));
        $this->delete(route('menu-gachas.flags.destroy', ['id' => $menu->id, 'flagId' => $flag->id]))->assertRedirect(route('login'));
    }

    public function test_flag_names_are_escaped_in_the_edit_screen(): void
    {
        $user = User::factory()->create();
        $menu = $user->menuGachas()->create(['name' => 'ランチ']);
        $menu->flags()->create(['name' => '<script>alert(1)</script>']);

        $this->actingAs($user)->get(route('menu-gachas.show', ['id' => $menu->id]))
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
