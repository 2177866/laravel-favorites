<?php
namespace Alyakin\Favorites\Providers;

use Illuminate\Support\ServiceProvider;

class FavoritesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        // $this->publishes([
        //     __DIR__ . '/../../config/favorites.php' => config_path('favorites.php'),
        // ], 'config');
    }

    public function register(): void
    {
        // $this->mergeConfigFrom(
        //     __DIR__ . '/../../config/favorites.php',
        //     'favorites'
        // );
    }
}
