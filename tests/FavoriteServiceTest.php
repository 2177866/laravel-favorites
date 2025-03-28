<?php
namespace Alyakin\Favorites\Tests;

use Alyakin\Favorites\Models\Favorite;
use Alyakin\Favorites\Services\FavoriteService;
use Alyakin\Favorites\Tests\Stubs\Post;
use Alyakin\Favorites\Tests\Stubs\Profile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FavoriteServiceTest extends TestCase
{
    protected FavoriteService $service;
    protected string $ownerId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(FavoriteService::class);
        $this->ownerId = (string) Str::uuid();
    }

    public function test_can_add_favorite_to_root_folder(): void
    {
        $post = Post::create(['title' => 'Test post']);

        $favorite = $this->service->addToFavorites($this->ownerId, $post);

        $this->assertDatabaseHas('favorites', [
            'id' => $favorite->id,
            'owner_id' => $this->ownerId,
            'favoritable_type' => $post::class,
            'favoritable_id' => $post->getKey(),
            'favorite_folder_id' => null,
        ]);
    }

    public function test_can_add_favorite_to_named_folder(): void
    {
        $post = Post::create(['title' => 'Post in folder']);

        $favorite = $this->service->addToFavorites($this->ownerId, $post, 'Read later');

        $this->assertNotNull($favorite->favorite_folder_id);
        $this->assertEquals('Read later', $favorite->folder->name);
    }

    public function test_cannot_add_duplicate_favorite(): void
    {
        $post = Post::create(['title' => 'Dup']);

        $this->service->addToFavorites($this->ownerId, $post);

        $this->expectException(ValidationException::class);

        $this->service->addToFavorites($this->ownerId, $post);
    }

    public function test_can_remove_existing_favorite(): void
    {
        $post = Post::create();

        $this->service->addToFavorites($this->ownerId, $post);
        $this->service->removeFromFavorites($this->ownerId, $post);

        $this->assertDatabaseMissing('favorites', [
            'owner_id' => $this->ownerId,
            'favoritable_type' => $post::class,
            'favoritable_id' => $post->getKey(),
        ]);
    }

    public function test_remove_is_silent_when_favorite_does_not_exist(): void
    {
        $post = Post::create();

        $this->service->removeFromFavorites($this->ownerId, $post);

        $this->assertTrue(true); // просто проверка, что не упало
    }

    public function test_can_move_favorite_to_another_folder(): void
    {
        $profile = Profile::create(['nickname' => 'scout']);

        $favorite = $this->service->addToFavorites($this->ownerId, $profile);

        $folder = \Alyakin\Favorites\Models\FavoriteFolder::create([
            'owner_id' => $this->ownerId,
            'name' => 'People',
        ]);

        $this->service->moveToFolder($favorite->id, $folder->id);

        $favorite->refresh();
        $this->assertEquals('People', $favorite->folder->name);
    }

    public function test_can_move_favorite_to_root(): void
    {
        $profile = Profile::create(['nickname' => 'desert fox']);

        $favorite = $this->service->addToFavorites($this->ownerId, $profile, 'Heroes');

        $this->service->moveToFolder($favorite->id, null);

        $favorite->refresh();
        $this->assertNull($favorite->favorite_folder_id);
    }

    public function test_move_fails_if_favorite_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->moveToFolder(Str::uuid()->toString(), null);
    }

    public function test_favoritable_relation_works(): void
    {
        $post = Post::create(['title' => 'Relation test']);

        $favorite = $this->service->addToFavorites($this->ownerId, $post);

        $this->assertTrue($favorite->favoritable->is($post));
    }

    public function test_folder_relation_works(): void
    {
        $profile = Profile::create(['nickname' => 'test']);

        $favorite = $this->service->addToFavorites($this->ownerId, $profile, 'People');

        $this->assertEquals('People', $favorite->folder->name);
    }
}
