<?php
namespace Alyakin\Favorites\Tests;

use Alyakin\Favorites\Models\Favorite;
use Alyakin\Favorites\Models\FavoriteFolder;
use Alyakin\Favorites\Services\FavoriteFolderService;
use Alyakin\Favorites\Tests\Stubs\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FavoriteFolderServiceTest extends TestCase
{
    protected FavoriteFolderService $service;
    protected string $ownerId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(FavoriteFolderService::class);
        $this->ownerId = (string) Str::uuid();
    }

    public function test_create_or_find_creates_new_folder_when_not_exists(): void
    {
        $folder = $this->service->createOrFindFolder($this->ownerId, 'Read later');

        $this->assertInstanceOf(FavoriteFolder::class, $folder);
        $this->assertEquals('Read later', $folder->name);
        $this->assertEquals($this->ownerId, $folder->owner_id);
    }

    public function test_create_or_find_returns_existing_folder(): void
    {
        $existing = FavoriteFolder::create([
            'owner_id' => $this->ownerId,
            'name' => 'Saved',
        ]);

        $folder = $this->service->createOrFindFolder($this->ownerId, 'Saved');

        $this->assertTrue($existing->is($folder));
    }

    public function test_can_rename_folder_successfully(): void
    {
        $folder = FavoriteFolder::create([
            'owner_id' => $this->ownerId,
            'name' => 'Old Name',
        ]);

        $this->service->updateFolderName($this->ownerId, $folder->id, 'New Name');

        $this->assertEquals('New Name', $folder->fresh()->name);
    }

    public function test_rename_fails_if_name_already_exists(): void
    {
        FavoriteFolder::create([
            'owner_id' => $this->ownerId,
            'name' => 'Work',
        ]);

        $folder = FavoriteFolder::create([
            'owner_id' => $this->ownerId,
            'name' => 'Personal',
        ]);

        $this->expectException(ValidationException::class);

        $this->service->updateFolderName($this->ownerId, $folder->id, 'Work');
    }

    public function test_can_delete_folder_and_its_favorites(): void
    {
        $folder = FavoriteFolder::create([
            'owner_id' => $this->ownerId,
            'name' => 'Trash',
        ]);

        $post = Post::create(['title' => 'Throwaway']);
        Favorite::create([
            'owner_id' => $this->ownerId,
            'favoritable_type' => $post::class,
            'favoritable_id' => $post->id,
            'favorite_folder_id' => $folder->id,
        ]);

        $this->service->deleteFolder($this->ownerId, $folder->id);

        $this->assertDatabaseMissing('favorite_folders', [
            'id' => $folder->id,
        ]);

        $this->assertDatabaseMissing('favorites', [
            'favorite_folder_id' => $folder->id,
        ]);
    }

    public function test_delete_fails_if_folder_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->deleteFolder($this->ownerId, Str::uuid()->toString());
    }

    public function test_get_all_folders_for_owner_returns_sorted_list(): void
    {
        FavoriteFolder::create(['owner_id' => $this->ownerId, 'name' => 'Zebra']);
        FavoriteFolder::create(['owner_id' => $this->ownerId, 'name' => 'Alpha']);
        FavoriteFolder::create(['owner_id' => $this->ownerId, 'name' => 'Middle']);

        /** @var Collection<FavoriteFolder> $folders */
        $folders = $this->service->getAllFoldersForOwner($this->ownerId);

        $this->assertCount(3, $folders);
        $this->assertEquals(['Alpha', 'Middle', 'Zebra'], $folders->pluck('name')->toArray());
    }
}
