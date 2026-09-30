<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_guests_see_the_service_introduction_and_registration_links(): void
    {
        $this->get(route('home'))
            ->assertSee('飲食店や学食のメニューを登録')
            ->assertSee('予算内で選ぶ')
            ->assertSee('「学割」などのフラグ')
            ->assertSee('href="'.route('register').'"', false)
            ->assertSee('href="'.route('login').'"', false);
    }

    public function test_signed_in_users_see_a_dashboard_link_instead_of_account_links(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('home'))
            ->assertSee('href="'.route('dashboard').'"', false)
            ->assertDontSee('href="'.route('login').'"', false);
    }
}
