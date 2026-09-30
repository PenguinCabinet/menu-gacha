<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_form_uses_username_without_email_field(): void
    {
        $this->get(route('register'))
            ->assertSee('ユーザー名')
            ->assertDontSee('name="email"', false);
    }

    public function test_users_can_register_without_email(): void
    {
        foreach (['first-user', 'second-user'] as $name) {
            $this->post(route('register.submit'), [
                'name' => $name,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])->assertRedirect(route('dashboard'));

            $this->assertDatabaseHas('users', ['name' => $name, 'email' => null]);
        }

        $this->assertDatabaseCount('users', 2);
    }

    public function test_registration_ignores_email_sent_directly(): void
    {
        $this->post(route('register.submit'), [
            'name' => 'email-user',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', ['name' => 'email-user', 'email' => null]);
    }

    public function test_registration_rejects_an_existing_user_name(): void
    {
        User::factory()->create(['name' => 'existing-user']);

        $this->post(route('register.submit'), [
            'name' => 'existing-user',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('users', 1);
    }
}
