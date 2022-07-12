<?php

namespace Tests\Feature;

use Faker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Notebook;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotebookTest extends TestCase
{
    // Clear DB before each test
    //use RefreshDatabase;

    /**
     * Test Notebook entry addition
     *
     * @return void
     */
    public function testNotebookCanBeAdded()
    {
        // Create and save test user to DB
        $user = User::factory()->create();
        // Create auth token for that user
        $token = $user->createToken('auth_token');
        $faker = Faker\Factory::create();
        // User registration pseudo-request
        // Do NOT use $faker->unique()->email, sometimes it'll give e-mail on non-existing domain.
        // Such e-mail will be considered invalid by 'email:rfc,dns' validator.
        // $faker->unique()->freeEmail uses only gmail.com, yahoo.com and hotmail.com domains.
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->post('/api/v1/notebook', [
            'name' => $faker->name,
            'phone' => $faker->phoneNumber,
            'email' => $faker->unique()->freeEmail,
        ]);

        $response->assertJsonFragment(['message' => 'Entry added.']);
    }
}
