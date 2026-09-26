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
            ->assertSee('プレビュー')
            ->assertSee('編集')
            ->assertSee('週末ごはん');
    }

    public function test_menu_gacha_name_can_be_updated_from_the_edit_tab(): void
    {
        $user = User::factory()->create();
        $menuGacha = $user->menuGachas()->create(['name' => '週末ごはん']);

        $response = $this->actingAs($user)
            ->patch(route('menu-gachas.update', ['id' => $menuGacha->getKey()]), [
                'name' => '平日ごはん',
            ]);

        $response->assertRedirect(route('menu-gachas.show', ['id' => $menuGacha->getKey()]));
        $this->assertDatabaseHas('menu_gachas', [
            'id' => $menuGacha->getKey(),
            'name' => '平日ごはん',
        ]);
    }
}
