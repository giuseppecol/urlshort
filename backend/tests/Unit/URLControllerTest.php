<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\URL;
use Illuminate\Foundation\Testing\RefreshDatabase;

class URLControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test URL shortening.
     *
     * @return void
     */
    public function testShortenUrl()
    {
        // Arrange: Create a user and authenticate.
        $user = User::factory()->create();
        $this->actingAs($user);

        // Act: Call the URL shortening endpoint.
        $response = $this->postJson('/api/shorten', [
            'url' => 'https://www.example.com',
        ]);

        // Assert: Check if the response is correct.
        $response->assertStatus(201);
        $response->assertJsonStructure(['short_url']);

        // Verify the URL exists in the database.
        $this->assertDatabaseHas('urls', [
            'original_url' => 'https://www.example.com',
        ]);
    }

    /**
     * Test URL shortening with invalid URL.
     *
     * @return void
     */
    public function testShortenUrlValidationError()
    {
        // Arrange: Create a user and authenticate.
        $user = User::factory()->create();
        $this->actingAs($user);

        // Act: Call the URL shortening endpoint with invalid data.
        $response = $this->postJson('/api/shorten', [
            'url' => 'invalid-url',
        ]);

        // Assert: Check if the response is correct.
        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The url must be a valid.',
        ]);
    }

    /**
     * Test retrieving URLs for the authenticated user.
     *
     * @return void
     */
    public function testRetrieveUserUrls()
    {
        // Arrange: Create a user and authenticate.
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create some URLs for the user
        $url1 = URL::factory()->create(['user_id' => $user->id]);
        $url2 = URL::factory()->create(['user_id' => $user->id]);

        // Act: Retrieve URLs for the authenticated user
        $response = $this->getJson('/api/urls');

        // Assert: Verify the response structure and check for the URLs.
        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonFragment(['original_url' => $url1->original_url]);
        $response->assertJsonFragment(['original_url' => $url2->original_url]);
    }

    /**
     * Test retrieving URLs when not authenticated.
     *
     * @return void
     */
    public function testRetrieveUrlsUnauthorized()
    {
        // Act: Try to retrieve URLs without authentication
        $response = $this->getJson('/api/urls');

        // Assert: Verify that the request returns a 401 Unauthorized error.
        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthorized',
        ]);
    }

    /**
     * Test the redirect to the original URL.
     *
     * @return void
     */
    public function testRedirectToOriginalUrl()
    {
        // Arrange: Create a user and authenticate.
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a URL
        $url = URL::factory()->create(['user_id' => $user->id]);

        // Act: Try to access the shortened URL.
        $response = $this->get('/api/redirect/' . $url->short_code);

        // Assert: Check if the user is redirected to the original URL.
        $response->assertRedirect($url->original_url);
    }

    /**
     * Test redirect with invalid short code.
     *
     * @return void
     */
    public function testRedirectUrlNotFound()
    {
        // Act: Try to access a non-existent shortened URL.
        $response = $this->get('/api/redirect/invalidcode');

        // Assert: Check if the response is a 404 error.
        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Short URL not found',
        ]);
    }

    /**
     * Test deleting a URL.
     *
     * @return void
     */
    public function testDeleteUrl()
    {
        // Arrange: Create a user and authenticate.
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a URL for the authenticated user.
        $url = URL::factory()->create(['user_id' => $user->id]);

        // Act: Call the delete endpoint.
        $response = $this->deleteJson("/api/urls/{$url->id}");

        // Assert: Check if the response is correct.
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'URL deleted successfully',
        ]);

        // Verify the URL was deleted from the database.
        $this->assertDatabaseMissing('urls', ['id' => $url->id]);
    }

    /**
     * Test trying to delete a URL by an unauthorized user.
     *
     * @return void
     */
    public function testDeleteUrlUnauthorized()
    {
        // Arrange: Create two users.
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $this->actingAs($user1);

        // Create a URL for user1.
        $url = URL::factory()->create(['user_id' => $user1->id]);

        // Act: Try to delete the URL created by user1 but from user2.
        $response = $this->deleteJson("/api/urls/{$url->id}");

        // Assert: Check if the response is a 403 error (Unauthorized).
        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Unauthorized',
        ]);
    }

    /**
     * Test trying to delete a non-existent URL.
     *
     * @return void
     */
    public function testDeleteUrlNotFound()
    {
        // Arrange: Create a user and authenticate.
        $user = User::factory()->create();
        $this->actingAs($user);

        // Act: Try to delete a URL that doesn't exist.
        $response = $this->deleteJson("/api/urls/9999");

        // Assert: Check if the response is a 404 error (URL not found).
        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'URL not found',
        ]);
    }
}
