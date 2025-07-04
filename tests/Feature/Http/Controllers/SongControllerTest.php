<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Artist;
use App\Models\Song;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SongControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $regular_user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->regular_user = User::factory()->create();
    }

    public function test_viewing_song_increments_view_count(): void
    {
        $artist = Artist::factory()->create(['views' => 0]);
        $song = Song::factory()->create([
            'artist_id' => $artist->id,
            'views' => 0,
        ]);

        $this->get(route('artists.songs.show', [$artist, $song]));

        $this->assertEquals(1, $song->fresh()->views);
        $this->assertEquals(1, $artist->fresh()->views);
    }

    public function test_song_show_page_includes_favorite_status(): void
    {
        $song = Song::factory()->create();

        $this->regular_user->addFavoriteSong($song);

        $response = $this->actingAs($this->regular_user)
            ->get(route('artists.songs.show', [$song->artist, $song]));

        $response->assertInertia(fn ($assert) => $assert
            ->where('isFavorited', true)
        );
    }

    public function test_only_authenticated_user_can_access_edit_page(): void
    {
        $song = Song::factory()->create();

        $this->assertGuest();
        $this->get(route('artists.songs.edit', [$song->artist, $song]))
            ->assertRedirect(route('login'));

        $response = $this->actingAs($this->regular_user)
            ->get(route('artists.songs.edit', [$song->artist, $song]));

        $response->assertOk();
    }

    public function test_guests_cannot_favorite_songs(): void
    {
        $song = Song::factory()->create();

        $response = $this->post(route('songs.favorite', [$song->artist, $song]));

        $response->assertRedirect(route('login'));
    }

    public function test_users_can_favorite_songs(): void
    {
        $song = Song::factory()->create();

        $response = $this->actingAs($this->regular_user)
            ->post(route('songs.favorite', [$song->artist, $song]));

        $response->assertOk();
        $this->assertDatabaseHas('favorite_songs', [
            'user_id' => $this->regular_user->id,
            'song_id' => $song->id,
        ]);
    }

    public function test_users_can_unfavorite_songs(): void
    {
        $song = Song::factory()->create();
        $this->regular_user->addFavoriteSong($song);

        $response = $this->actingAs($this->regular_user)
            ->delete(route('songs.favorite', [$song->artist, $song]));

        $response->assertOk();
        $this->assertDatabaseMissing('favorite_songs', [
            'user_id' => $this->regular_user->id,
            'song_id' => $song->id,
        ]);
    }

    public function test_favorite_handles_exceptions(): void
    {
        $song = Song::factory()->create();

        $this->mock(UserService::class, function ($mock): void {
            $mock->shouldReceive('favoriteSong')
                ->once()
                ->andThrows(new \Exception('Database error'));
        });

        $this->actingAs($this->regular_user);

        $this->post(route('songs.favorite', [$song->artist, $song]))
            ->assertServerError();
    }

    public function test_unfavorite_handles_exceptions(): void
    {
        $song = Song::factory()->create();

        $this->mock(UserService::class, function ($mock): void {
            $mock->shouldReceive('unfavoriteSong')
                ->once()
                ->andThrows(new \Exception('Database error'));
        });

        $this->actingAs($this->regular_user);

        $this->delete(route('songs.favorite', [$song->artist, $song]))
            ->assertServerError();
    }
}
