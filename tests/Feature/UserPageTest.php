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

        $response = $this->get(route('users.show', ['name' => $user->name]));

        $response->assertOk()
            ->assertSeeText('test-user')
            ->assertSeeText('メニューガチャ')
            ->assertSeeText('タイムライン')
            ->assertSeeText('このタブは準備中です。');
    }

    public function test_user_page_returns_not_found_for_an_unknown_name(): void
    {
        $this->get(route('users.show', ['name' => 'unknown-user']))
            ->assertNotFound();
    }
}
