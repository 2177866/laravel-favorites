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
     * @param FavoriteFolder|string $folder
     * @param string $newName
     *
     * @throws ValidationException если имя уже занято
     */
    public function updateFolderName(string $ownerId, FavoriteFolder | string $folder, string $newName): FavoriteFolder {
        $folder = \is_string($folder)
            ? FavoriteFolder::forOwner($ownerId)->findOrFail($folder)
            : $folder;

        $duplicate = FavoriteFolder::forOwner($ownerId)
            ->where('name', $newName)
            ->where('id', '!=', $folder->id)
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'name' => ['Папка с таким именем уже существует.'],
            ]);
        }

        $folder->name = $newName;
        $folder->save();
        return $folder;
    }

    /**
     * Удалить папку и убрать ссылки на нее из избранного.
     *
     * @param string $ownerId
     * @param FavoriteFolder|string $folder
     */
    public function deleteFolder(string $ownerId, FavoriteFolder | string $folder): bool {
        $result = DB::transaction(function () use ($ownerId, $folder) {
            $folder = \is_string($folder)
                ? FavoriteFolder::forOwner($ownerId)->findOrFail($folder)
                : $folder;

            $folder->favorites()->delete();
            $folder->delete();
            return true;
        });

        return (bool) $result;
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
