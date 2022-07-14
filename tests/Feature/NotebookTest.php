<?php

namespace Tests\Feature;

use Faker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Notebook;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotebookTest extends TestCase
{
    // Clear DB before each test
    use RefreshDatabase;

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
        // Entry addition pseudo-request
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

    /**
     * Test Notebook entry addition - with client-provided UUID
     *
     * @return void
     */
    public function testNotebookWithUuidCanBeAdded()
    {
        // Create and save test user to DB
        $user = User::factory()->create();
        // Create auth token for that user
        $token = $user->createToken('auth_token');
        $faker = Faker\Factory::create();
        // Entry addition pseudo-request
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->post('/api/v1/notebook', [
            'id' => Str::orderedUuid()->toString(),
            'name' => $faker->name,
            'phone' => $faker->phoneNumber,
            'email' => $faker->unique()->freeEmail,
        ]);

        $response->assertJsonFragment(['message' => 'Entry added.']);
    }

    /**
     * Test Notebook entry editing
     *
     * @return void
     */
    public function testNotebookCanBeEdited()
    {
        // Create and save test Notebook to DB
        $notebook = Notebook::factory()->create();
        // Get test user
        $user = User::find($notebook['creator_uuid']);
        // Create auth token for that user
        $token = $user->createToken('auth_token');
        $faker = Faker\Factory::create();
        // Editing pseudo-request
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->post('/api/v1/notebook/' . $notebook['id'], [
            'name' => $faker->name,
            'phone' => $faker->phoneNumber,
            'email' => $faker->unique()->freeEmail,
            'company' => null,
            'updated_at' => $notebook['updated_at'],
        ]);

        $response->assertJsonFragment(['message' => 'Entry updated.']);
    }

    /**
     * Test Notebook entry deletion
     *
     * @return void
     */
    public function testNotebookCanBeDeleted()
    {
        // Create and save test Notebook to DB
        $notebook = Notebook::factory()->create();
        // Get test user
        $user = User::find($notebook['creator_uuid']);
        // Create auth token for that user
        $token = $user->createToken('auth_token');
        // Deletion pseudo-request
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->delete('/api/v1/notebook/' . $notebook['id']);

        $response->assertJsonFragment(['message' => 'Entry deleted.']);
    }

    /**
     * Test Notebook entry deletion - by another user
     *
     * @return void
     */
    public function testNotebookCantBeDeletedByAnotherUser()
    {
        // Create and save test Notebook to DB
        $notebook = Notebook::factory()->create();
        // Create and save test user to DB
        $user = User::factory()->create();
        // Create auth token for that user
        $token = $user->createToken('auth_token');
        // Deletion pseudo-request
        // Must fail, that user is not permitted to delete this entry
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->delete('/api/v1/notebook/' . $notebook['id']);

        $response->assertJsonFragment(['message' => 'You are not allowed to delete this entry.']);
    }

    /**
     * Test Notebook entry displaying
     *
     * @return void
     */
    public function testNotebookCanBeDisplayed()
    {
        // Create and save test Notebook to DB
        $notebook = Notebook::factory()->create();
        // Pseudo-request to get entry
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->get('/api/v1/notebook/' . $notebook['id']);

        $response->assertJsonFragment(['created_at' => $notebook['created_at']]);
    }
}
