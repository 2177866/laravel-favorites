<?php
namespace Alyakin\Favorites\Services;

use Alyakin\Favorites\Models\Favorite;
use Alyakin\Favorites\Models\FavoriteFolder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FavoriteFolderService {

    public function getFolderOrFail(string $ownerId, string $folderId): FavoriteFolder {
        return FavoriteFolder::forOwner($ownerId)->findOrFail($folderId);
    }

    /**
     * Найти или создать папку с заданным именем.
     *
     * @param string $ownerId
     * @param string $name
     * @return FavoriteFolder
     */
    public function createOrFindFolder(string $ownerId, string $name): FavoriteFolder {
        return FavoriteFolder::firstOrCreate([
            'owner_id' => $ownerId,
            'name'    => $name,
        ]);
    }

    /**
     * Переименовать папку. Имя должно быть уникальным для пользователя.
     *
     * @param string $ownerId
     * @param string $folderId
     * @param string $newName
     *
     * @throws ValidationException если имя уже занято
     */
    public function updateFolderName(string $ownerId, string $folderId, string $newName): void {
        $folder = FavoriteFolder::forOwner($ownerId)->findOrFail($folderId);

        $duplicate = FavoriteFolder::forOwner($ownerId)
            ->where('name', $newName)
            ->where('id', '!=', $folderId)
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'name' => ['Папка с таким именем уже существует.'],
            ]);
        }

        $folder->name = $newName;
        $folder->save();
    }

    /**
     * Удалить папку и убрать ссылки на нее из избранного.
     *
     * @param string $ownerId
     * @param string $folderId
     */
    public function deleteFolder(string $ownerId, string $folderId): void {
        DB::transaction(function () use ($ownerId, $folderId) {
            $folder = FavoriteFolder::forOwner($ownerId)->findOrFail($folderId);
            $folder->favorites()->delete();
            $folder->delete();
        });
    }

    /**
     * Получить все папки пользователя, отсортированные по имени.
     *
     * @param string $ownerId
     * @return Collection<FavoriteFolder>
     */
    public function getAllFoldersForOwner(string $ownerId, $orderBy = 'name'): Collection {
        return FavoriteFolder::forOwner($ownerId)->orderBy($orderBy)->get();
    }
}
