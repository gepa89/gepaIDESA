<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Throwable;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test successful user registration.
     *
     * @return void
     */
    public function test_registers_user_successfully(): void
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
        ]);
    }

    /**
     * Test registration fails with invalid data.
     *
     * @return void
     */
    public function test_registration_fails_with_invalid_data(): void
    {
        $payload = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'message',
            'errors' => ['name', 'email', 'password'],
        ]);
    }

    /**
     * Test successful user login.
     *
     * @return void
     */
    public function test_logs_in_user_successfully(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $payload = [
            'email' => $user->email,
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
        ]);
    }

    /**
     * Test login fails with invalid credentials.
     *
     * @return void
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $payload = [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(401);
        $response->assertJsonFragment([
            'message' => 'Invalid credentials.',
        ]);
    }

    /**
     * Test tokens are revoked on successful login.
     *
     * @return void
     */
    public function test_revokes_existing_tokens_on_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $user->createToken('old_token');

        $payload = [
            'email' => $user->email,
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
        ]);

        $this->assertCount(1, $user->tokens);
    }
}
