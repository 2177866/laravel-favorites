<?php
namespace Alyakin\Favorites\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Favorite extends Model {
    use HasUuids;

    protected $fillable = [
        'owner_id',
        'favoritable_type',
        'favoritable_id',
        'favorite_folder_id',
    ];

    public function favoritable() {
        return $this->morphTo();
    }

    public function folder() {
        return $this->belongsTo(FavoriteFolder::class, 'favorite_folder_id');
    }

    /*
    Favorite::forOwner($ownerId)
        ->inFolder('Читать позже', $ownerId)
        ->latest()
        ->get();
    */

    public function scopeForOwner(Builder $query, string $ownerId): Builder {
        return $query->where('owner_id', $ownerId);
    }

    public function scopeInFolder(Builder $query, string $folderName, string $ownerId): Builder {
        return $query->whereHas('folder', function (Builder $q) use ($folderName, $ownerId) {
            $q->where('name', $folderName)
              ->where('owner_id', $ownerId);
        });
    }

    public function scopeForModel(Builder $query, Model $model): Builder {
        return $query->where('favoritable_type', $model::class)
                    ->where('favoritable_id', $model->getKey());
    }
}
