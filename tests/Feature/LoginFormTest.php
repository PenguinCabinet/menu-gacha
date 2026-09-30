<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_uses_username_without_email_field(): void
    {
        $this->get(route('login'))
            ->assertSee('ユーザー名')
            ->assertDontSee('name="email"', false);
    }

    public function test_user_without_email_can_log_in_with_username(): void
    {
        $user = User::factory()->create(['name' => 'username-only', 'email' => null]);

        $this->post(route('login.submit'), [
            'name' => 'username-only',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_email_cannot_be_used_instead_of_username(): void
    {
        User::factory()->create(['name' => 'existing-user', 'email' => 'existing@example.com']);

        $this->post(route('login.submit'), [
            'name' => 'existing@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('name');

        $this->assertGuest();
    }

    public function test_login_form_submits_over_https_behind_a_proxy(): void
    {
        $this->withServerVariables([
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ])->get('http://menu-gacha.penguincabinet.com/login')
            ->assertSee('action="https://menu-gacha.penguincabinet.com/login"', false);
    }
}
