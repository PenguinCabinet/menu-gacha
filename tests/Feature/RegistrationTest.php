<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

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
