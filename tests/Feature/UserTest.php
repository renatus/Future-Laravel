<?php

namespace Tests\Feature;

use Faker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    // Clear DB before each test
    use RefreshDatabase;

    /**
     * Test site's user registration
     *
     * @return void
     */
    public function testUserCanBeAdded()
    {
        $faker = Faker\Factory::create();
        // User registration pseudo-request
        // Do NOT use $faker->unique()->email, sometimes it'll give e-mail on non-existing domain.
        // Such e-mail will be considered invalid by 'email:rfc,dns' validator.
        // $faker->unique()->freeEmail uses only gmail.com, yahoo.com and hotmail.com domains.
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->post('/api/v1/register', [
            'name' => $faker->name,
            'email' => $faker->unique()->freeEmail,
            'password' => 'DfBBBnMMl23DwerT',
        ]);

        // If account was created successfully, token of type "Bearer" is being returned
        $response->assertJsonFragment(['token_type' => 'Bearer']);
    }

    /**
     * Test logging in as a site's user
     *
     * @return void
     */
    public function testUserCanLogin()
    {
        // Create and save test user to DB
        $user = User::factory()->create();
        // Login pseudo-request
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->post('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // If user logged in successfully, token of type "Bearer" is being returned
        $response->assertJsonFragment(['token_type' => 'Bearer']);
    }

    /**
     * Test logging out as a site's user.
     *
     * @return void
     */
    public function testUserCanLogout()
    {
        // Create and save test user to DB
        $user = User::factory()->create();
        // Create auth token for that user
        $token = $user->createToken('auth_token');
        // Logout pseudo-request
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->get('/api/v1/logout');

        $response->assertJsonFragment(['message' => 'Successful logout.']);
    }
}
