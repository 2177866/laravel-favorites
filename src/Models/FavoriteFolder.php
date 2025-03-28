<?php
namespace Alyakin\Favorites\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FavoriteFolder extends Model {
    use HasUuids;

    protected $fillable = ['owner_id', 'name'];

    public function favorites() {
        return $this->hasMany(Favorite::class)->orderBy('created_at', 'desc');
    }

    public function scopeForOwner(Builder $query, string $ownerId): Builder {
        return $query->where('owner_id', $ownerId);
    }
}
