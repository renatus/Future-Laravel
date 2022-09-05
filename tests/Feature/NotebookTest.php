<?php

namespace Tests\Feature;

use Faker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Notebook;
use Illuminate\Support\Str;
use App\Services\FileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotebookTest extends TestCase
{
    // Clear DB before each test
    use RefreshDatabase;

    /**
     * Test Notebook entry addition - without picture
     *
     * @return void
     */
    public function testNotebookCanBeAddedWithoutPicture()
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

        // Check if there is corresponding Notebook entry in DB
        $this->assertDatabaseHas('notebooks', [
            'id' => $response['id'],
            'updated_at' => $response['updated_at'],
            'picture' => null,
        ]);

        // Check if server response matches expected one
        $response->assertJsonFragment(['message' => 'Entry added.']);
    }

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
        // Entry-associated picture will be stored in real Laravel filesystem, NOT in testing.
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
            'picture' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        // Check if entry-associated file is actually in filesystem
        $notebook = Notebook::find($response['id']);
        $this->assertFileExists(FileService::getImgFsPath($notebook['picture']));
        // Delete test file from PROD FS
        unlink(FileService::getImgFsPath($notebook['picture']));

        // Check if server response matches expected one
        $response->assertJsonFragment(['message' => 'Entry added.']);
    }

    /**
     * Test Notebook entry addition - with client-provided UUID
     *
     * @return void
     */
    public function testNotebookCanBeAddedWithUuid()
    {
        // Create and save test user to DB
        $user = User::factory()->create();
        // Create auth token for that user
        $token = $user->createToken('auth_token');
        $faker = Faker\Factory::create();
        // Entry addition pseudo-request
        // Entry-associated picture will be stored in real Laravel filesystem, NOT in testing.
        // Do NOT use $faker->unique()->email, sometimes it'll give e-mail on non-existing domain.
        // Such e-mail will be considered invalid by 'email:rfc,dns' validator.
        // $faker->unique()->freeEmail uses only gmail.com, yahoo.com and hotmail.com domains.
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->post('/api/v1/notebook', [
            'id' => Str::orderedUuid()->toString(),
            'name' => $faker->name,
            'phone' => $faker->phoneNumber,
            'email' => $faker->unique()->freeEmail,
            'picture' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        // Check if entry-associated file is actually in filesystem
        $notebook = Notebook::find($response['id']);
        $this->assertFileExists(FileService::getImgFsPath($notebook['picture']));
        // Delete test file from PROD FS
        unlink(FileService::getImgFsPath($notebook['picture']));

        // Check if server response matches expected one
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
        // Add image file to PROD filesystem and file path - to DB entry
        $image = UploadedFile::fake()->image('avatar.jpg', 500, 500);
        $notebook->update([
            'picture' => FileService::imgSave($image, $notebook['id']),
        ]);
        // Get test user
        $user = User::find($notebook['creator_uuid']);
        // Create auth token for that user
        $token = $user->createToken('auth_token');
        $faker = Faker\Factory::create();
        // Entry-editing pseudo-request
        // Entry-associated picture will be stored in real Laravel filesystem, NOT in testing.
        // Do NOT use $faker->unique()->email, sometimes it'll give e-mail on non-existing domain.
        // Such e-mail will be considered invalid by 'email:rfc,dns' validator.
        // $faker->unique()->freeEmail uses only gmail.com, yahoo.com and hotmail.com domains.
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->post('/api/v1/notebook/' . $notebook['id'], [
            'name' => $faker->name,
            'company' => null,
            'phone' => $faker->phoneNumber,
            'email' => $faker->unique()->freeEmail,
            'picture' => UploadedFile::fake()->image('avatar_new.jpg', 500, 500),
            'updated_at' => $notebook['updated_at'],
        ]);

        // Check if OLD entry-associated file is NOT in filesystem
        $this->assertFileDoesNotExist(FileService::getImgFsPath($notebook['picture']));
        // Check if NEW entry-associated file is actually in filesystem
        $notebookModified = Notebook::find($response['id']);
        $this->assertFileExists(FileService::getImgFsPath($notebookModified['picture']));
        // Delete test file from PROD FS
        unlink(FileService::getImgFsPath($notebookModified['picture']));

        // Check if server response matches expected one
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
        // Add image file to PROD filesystem and file path - to DB entry
        $image = UploadedFile::fake()->image('avatar.jpg', 500, 500);
        $notebook->update([
            'picture' => FileService::imgSave($image, $notebook['id']),
        ]);
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


        // Check if entry-associated file is NOT in filesystem
        $this->assertFileDoesNotExist(FileService::getImgFsPath($notebook['picture']));

        // Check if there is NO corresponding Notebook entries in DB
        $this->assertDatabaseMissing('notebooks', [
            'id' => $notebook['id'],
        ]);

        // Check if server response matches expected one
        $response->assertJsonFragment(['message' => 'Entry deleted.']);
    }

    /**
     * Test Notebook entry deletion - by unauthorized user
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

        // Check if there is corresponding Notebook entry in DB
        $this->assertDatabaseHas('notebooks', [
            'id' => $notebook['id'],
        ]);

        // Check if server response matches expected one
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

        // Check if server response matches expected one
        $response->assertJsonFragment([
            'id' => $notebook['id'],
            'created_at' => $notebook['created_at'],
        ]);
    }

    /**
     * Test all Notebook entries listing, paginated
     *
     * @return void
     */
    public function testNotebooksCanBeListed()
    {
        // Create and save test Notebooks to DB
        Notebook::factory()->count(15)->create();
        // Pseudo-request to get entry
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])->get('/api/v1/notebook');

        // Check if server response matches expected one
        // Max entries number per page cap should be equal to what we've set at .env file
        $response->assertJsonFragment(['per_page' => intval($_ENV['FUTURE_PAGINATION_DEF'])]);
    }
}
