<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginFormTest extends TestCase
{
    public function test_login_form_submits_over_https_behind_a_proxy(): void
    {
        $this->withServerVariables([
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ])->get('http://menu-gacha.penguincabinet.com/login')
            ->assertSee('action="https://menu-gacha.penguincabinet.com/login"', false);
    }
}
