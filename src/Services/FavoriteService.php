<?php
namespace Alyakin\Favorites\Services;

use Illuminate\Support\Facades\DB;
use Alyakin\Favorites\Models\Favorite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Alyakin\Favorites\Services\FavoriteFolderService;

class FavoriteService {

    public function __construct(
        protected FavoriteFolderService $folderService
    ) {}

    /**
     * Добавить объект в избранное.
     *
     * @param string $ownerId
     * @param Model $model — любой favoritable (Post, Comment, Profile, и т.п.)
     * @param string|null $folderName — имя папки (если нужно создать/поместить)
     * @return Favorite
     *
     * @throws ValidationException если объект уже в избранном
     */
    public function addToFavorites(string $ownerId, Model $model, ?string $folderName = null): Favorite {
        return DB::transaction(function () use ($ownerId, $model, $folderName) {
            $folderId = null;

            if ($folderName) {
                $folder = $this->folderService->createOrFindFolder($ownerId, $folderName);
                $folderId = $folder->id;
            }

            $existing = Favorite::forOwner($ownerId)->forModel($model)->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'favoritable' => ['Объект уже находится в избранном.'],
                ]);
            }

            return Favorite::create([
                'owner_id'           => $ownerId,
                'favoritable_type'  => $model::class,
                'favoritable_id'    => $model->getKey(),
                'favorite_folder_id'=> $folderId,
            ]);
        });
    }

    /**
     * Удалить объект из избранного.
     *
     * @param string $ownerId
     * @param Model $model
     */
    public function removeFromFavorites(string $ownerId, Model $model): void {
        Favorite::forOwner($ownerId)->forModel($model)->delete();
    }

    /**
     * Переместить избранный объект в другую папку (или убрать из папки).
     *
     * @param string $favoriteId — ID записи в таблице favorites
     * @param string $folderId — ID папки (или null, чтобы убрать из папки)
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException если запись не найдена
     */
    public function moveToFolder(string $favoriteId, ?string $folderId): void {
        $favorite = Favorite::findOrFail($favoriteId);

        if ($folderId) {
            $folder = $this->folderService->getFolderOrFail($favorite->owner_id, $folderId);
            $folderId = $folder->id;
        }

        $favorite->update([
            'favorite_folder_id' => $folderId
        ]);
    }
}
