<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_endpoint_requires_credentials()
    {
        $response = $this->postJson('/api/auth/login', []);
        $response->assertStatus(422);
    }

    // Further tests would require configuring Passport and inserting oauth client id/secret
}
