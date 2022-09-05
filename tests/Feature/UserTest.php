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

        // Check if there is corresponding User entry in DB
        $this->assertDatabaseHas('users', [
            'id' => $response['id'],
        ]);

        // Check if there is corresponding Access Token entry in DB
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $response['id'],
        ]);

        // Check if server response matches expected one
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

        // Check if there is corresponding User entry in DB
        $this->assertDatabaseHas('users', [
            'id' => $response['id'],
        ]);

        // Check if there is corresponding Access Token entry in DB
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $response['id'],
        ]);

        // Check if server response matches expected one
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

        // Check if there is corresponding User entry in DB
        $this->assertDatabaseHas('users', [
            'id' => $user['id'],
        ]);

        // Check if there are NO corresponding Access Token entries in DB
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user['id'],
        ]);

        // Check if server response matches expected one
        $response->assertJsonFragment(['message' => 'Successful logout.']);
    }
}
